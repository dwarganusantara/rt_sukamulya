<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Kk_model extends CI_Model
{
    public function ready()
    {
        foreach(array('kk_families','kk_members','kk_scan_requests') as $t) if(!$this->db->table_exists($t)) return FALSE;
        return TRUE;
    }

    /**
     * @param bool $active_only TRUE = hanya anggota berstatus aktif (dipakai auto-fill surat);
     *        anggota pindah/meninggal tetap tersimpan sebagai histori mutasi.
     */
    public function family($citizen,$active_only=FALSE)
    {
        $f=$this->db->get_where('kk_families',array('owner_citizen_id'=>$citizen))->row_array();
        if(!$f) return NULL;
        $data=json_decode($f['fields_json'],TRUE);
        if($active_only && $this->db->field_exists('status','kk_members')) $this->db->where('status','aktif');
        $rows=$this->db->get_where('kk_members',array('family_id'=>$f['id']))->result_array();
        return array('keluarga'=>$data,'anggota'=>array_map(function($r){$m=json_decode($r['fields_json'],TRUE);if(isset($r['status'])&&$r['status']!=='aktif')$m['status_anggota']=$r['status'];return $m;},$rows));
    }

    public function submit($citizen,$data)
    {
        $this->db->trans_begin();
        // Serialize requests for an account, including concurrent browser tabs.
        $this->db->query('SELECT id FROM citizens WHERE id=? FOR UPDATE',array($citizen));
        $pending=$this->db->get_where('kk_scan_requests',array('citizen_id'=>$citizen,'status'=>'pending'))->row_array();
        if($pending) {$this->db->trans_rollback();throw new RuntimeException('Masih ada pengajuan menunggu verifikasi.');}
        $this->db->insert('kk_scan_requests',array('citizen_id'=>$citizen,'proposed_data_json'=>json_encode($data,JSON_UNESCAPED_UNICODE)));
        $id=$this->db->insert_id();
        if(!$this->db->trans_status()) {$this->db->trans_rollback();throw new RuntimeException('Pengajuan belum tersimpan.');}
        $this->db->trans_commit(); return $id;
    }

    public function review($id,$actor,$status,$notes)
    {
        $this->load->library('Kk_fields');
        $this->db->trans_begin();
        try {
            $r=$this->db->query('SELECT * FROM kk_scan_requests WHERE id=? FOR UPDATE',array($id))->row_array();
            if(!$r || $r['status']!=='pending') throw new RuntimeException('Pengajuan sudah diproses atau tidak ditemukan.');
            if($status==='approved') {
                $data=$this->kk_fields->normalize(json_decode($r['proposed_data_json'],TRUE));
                $errors=$this->kk_fields->validate($data);
                if($errors) throw new RuntimeException(implode(' ',$errors));
                $this->db->query('SELECT id FROM citizens WHERE id=? FOR UPDATE',array($r['citizen_id']));
                $family=$this->db->get_where('kk_families',array('owner_citizen_id'=>$r['citizen_id']))->row_array();
                $other=$this->db->get_where('kk_families',array('nomor_kk'=>$data['keluarga']['nomor_kk']))->row_array();
                if($other && (!$family || $other['id']!==$family['id'])) throw new RuntimeException('Nomor KK berkonflik dengan keluarga terdaftar. Minta perbaikan.');
                if($family && $family['nomor_kk']!==$data['keluarga']['nomor_kk']) throw new RuntimeException('Perubahan nomor KK memerlukan proses mutasi terpisah.');
                if(!$family) {
                    $this->db->insert('kk_families',array('nomor_kk'=>$data['keluarga']['nomor_kk'],'owner_citizen_id'=>$r['citizen_id'],'fields_json'=>json_encode($data['keluarga'])));
                    $fid=$this->db->insert_id();
                } else {
                    $fid=$family['id'];
                    $this->db->where('id',$fid)->update('kk_families',array('fields_json'=>json_encode($data['keluarga']),'updated_at'=>date('Y-m-d H:i:s')));
                }
                foreach($data['anggota'] as $m) {
                    $existing=$this->db->get_where('kk_members',array('nik'=>$m['nik']))->row_array();
                    if($existing && (int)$existing['family_id']!==(int)$fid) throw new RuntimeException('NIK berkonflik dengan keluarga lain. Minta perbaikan.');
                    $record=array('family_id'=>$fid,'nik'=>$m['nik'],'fields_json'=>json_encode($m));
                    if($existing) $this->db->where('id',$existing['id'])->update('kk_members',$record);
                    else $this->db->insert('kk_members',$record);
                }
            }
            $this->db->where('id',$id)->update('kk_scan_requests',array('status'=>$status,'review_notes'=>$notes,'reviewed_by'=>$actor,'reviewed_at'=>date('Y-m-d H:i:s')));
            if(!$this->db->trans_status()) throw new RuntimeException('Penyimpanan gagal. Tidak ada data yang diubah.');
            $this->db->trans_commit();
        } catch(Throwable $e) {$this->db->trans_rollback();throw $e;}
    }
}
