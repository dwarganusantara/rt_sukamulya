<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Kk_model extends CI_Model
{
    public function ready()
    {
        foreach(array('kk_families','kk_members','kk_scan_requests') as $t) if(!$this->db->table_exists($t)) return FALSE;
        return TRUE;
    }

    /** Migrasi master_kk_upgrade.sql sudah dijalankan (KK tanpa akun, input admin, undangan akun). */
    public function master_ready()
    {
        return $this->ready() && $this->db->field_exists('source','kk_scan_requests') && $this->db->table_exists('account_invitations');
    }

    /** Sama dengan family() tetapi dicari dari nomor KK (untuk input admin / keluarga tanpa akun). */
    public function family_by_kk($nomor_kk)
    {
        $f=$this->db->get_where('kk_families',array('nomor_kk'=>(string)$nomor_kk))->row_array();
        return $f ? $this->family_record($f) : NULL;
    }

    private function family_record($f)
    {
        $rows=$this->db->get_where('kk_members',array('family_id'=>$f['id']))->result_array();
        return array('keluarga'=>json_decode($f['fields_json'],TRUE),'anggota'=>array_map(function($r){$m=json_decode($r['fields_json'],TRUE);if(isset($r['status'])&&$r['status']!=='aktif')$m['status_anggota']=$r['status'];return $m;},$rows));
    }

    /**
     * Deteksi duplikasi sebelum menyimpan (tidak pernah membuat data ganda diam-diam).
     * family      = KK terverifikasi dengan nomor sama (boleh diperbarui bila admin mengonfirmasi)
     * nik_conflicts = NIK yang sudah terdaftar di KK LAIN (harus lewat mutasi)
     * pending     = pengajuan lain dengan nomor KK sama yang masih menunggu
     */
    public function duplicates($data,$ignore_request=0)
    {
        $kk=(string)($data['keluarga']['nomor_kk']??'');
        $family=$kk!=='' ? $this->db->get_where('kk_families',array('nomor_kk'=>$kk))->row_array() : NULL;
        $niks=array_values(array_filter(array_column($data['anggota']??array(),'nik')));
        $conflicts=array();
        if($niks) foreach($this->db->select('m.nik,m.family_id')->from('kk_members m')->where_in('m.nik',$niks)->get()->result_array() as $m) {
            if(!$family || (int)$m['family_id']!==(int)$family['id']) $conflicts[]=$m['nik'];
        }
        $pending=NULL;
        if($kk!=='') foreach($this->db->select('id,proposed_data_json')->where('status','pending')->where('id <>',(int)$ignore_request)->get('kk_scan_requests')->result_array() as $r) {
            $d=json_decode($r['proposed_data_json'],TRUE);
            if(($d['keluarga']['nomor_kk']??'')===$kk) {$pending=$r;break;}
        }
        return array('family'=>$family,'nik_conflicts'=>$conflicts,'pending'=>$pending);
    }

    /**
     * Simpan input admin sebagai pengajuan (draft / pending) tanpa akun warga.
     * $request_id > 0 melanjutkan draft atau pengajuan admin yang dikembalikan untuk perbaikan.
     */
    public function admin_save($admin_id,$data,$status,$source,$request_id=0)
    {
        if(!in_array($status,array('draft','pending'),TRUE)) throw new InvalidArgumentException('Status tidak valid.');
        $row=array('proposed_data_json'=>json_encode($data,JSON_UNESCAPED_UNICODE),'status'=>$status,'source'=>$source,'updated_at'=>date('Y-m-d H:i:s'));
        $this->db->trans_begin();
        try {
            if($request_id) {
                $r=$this->db->query('SELECT id,citizen_id,status FROM kk_scan_requests WHERE id=? FOR UPDATE',array((int)$request_id))->row_array();
                if(!$r || $r['citizen_id']!==NULL || !in_array($r['status'],array('draft','revision_requested'),TRUE)) throw new RuntimeException('Draft tidak ditemukan atau sudah diproses.');
                // Pengajuan yang dikirim ulang dianggap pengajuan baru: catatan keputusan lama dikosongkan.
                $this->db->where('id',(int)$request_id)->update('kk_scan_requests',$row+array('review_notes'=>NULL,'reviewed_by'=>NULL,'reviewed_at'=>NULL));
                $id=(int)$request_id;
            } else {
                $this->db->insert('kk_scan_requests',$row+array('citizen_id'=>NULL,'created_by_admin'=>(int)$admin_id?:NULL));
                $id=(int)$this->db->insert_id();
            }
            if(!$this->db->trans_status()) throw new RuntimeException('Data KK belum tersimpan.');
            $this->db->trans_commit();
            return $id;
        } catch(Throwable $e) {$this->db->trans_rollback();throw $e;}
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
        // Unique key uq_kkreq_one_pending (data_integrity_upgrade.sql) menjadi pengaman terakhir bila dua permintaan lolos bersamaan.
        $debug=$this->db->db_debug; $this->db->db_debug=FALSE;
        $ok=$this->db->insert('kk_scan_requests',array('citizen_id'=>$citizen,'proposed_data_json'=>json_encode($data,JSON_UNESCAPED_UNICODE)));
        $err=$this->db->error(); $this->db->db_debug=$debug;
        if(!$ok) {$this->db->trans_rollback();throw new RuntimeException((int)$err['code']===1062?'Masih ada pengajuan menunggu verifikasi.':'Pengajuan belum tersimpan.');}
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
                $master=$this->db->field_exists('source','kk_families');
                $now=date('Y-m-d H:i:s');
                $other=$this->db->query('SELECT * FROM kk_families WHERE nomor_kk=? FOR UPDATE',array($data['keluarga']['nomor_kk']))->row_array();
                $claim=FALSE;
                if($r['citizen_id']!==NULL) {
                    $this->db->query('SELECT id FROM citizens WHERE id=? FOR UPDATE',array($r['citizen_id']));
                    $family=$this->db->get_where('kk_families',array('owner_citizen_id'=>$r['citizen_id']))->row_array();
                    // KK yang diinput admin (belum punya akun) boleh diklaim pemohon: verifikator sudah
                    // mencentang pemeriksaan kewenangan pemohon sebelum menyetujui.
                    if(!$family && $other && $other['owner_citizen_id']===NULL) {$family=$other;$claim=TRUE;}
                    if($other && (!$family || $other['id']!==$family['id'])) throw new RuntimeException('Nomor KK berkonflik dengan keluarga terdaftar. Minta perbaikan.');
                    if($family && $family['nomor_kk']!==$data['keluarga']['nomor_kk']) throw new RuntimeException('Perubahan nomor KK memerlukan proses mutasi terpisah.');
                } else {
                    // Input admin: KK dengan nomor sama diperbarui (admin sudah dikonfirmasi saat input), bukan diduplikasi.
                    $family=$other;
                }
                $stamp=$master?array('verified_by'=>(int)$actor?:NULL,'verified_at'=>$now):array();
                if(!$family) {
                    $row=array('nomor_kk'=>$data['keluarga']['nomor_kk'],'owner_citizen_id'=>$r['citizen_id'],'fields_json'=>json_encode($data['keluarga']));
                    if($master) $row+=$stamp+array('source'=>$r['citizen_id']!==NULL?'pengajuan_warga':(string)($r['source']??'admin_manual'));
                    $this->db->insert('kk_families',$row);
                    $fid=$this->db->insert_id();
                } else {
                    $fid=$family['id'];
                    $row=array('fields_json'=>json_encode($data['keluarga']),'updated_at'=>$now)+$stamp;
                    if($claim) $row['owner_citizen_id']=$r['citizen_id'];
                    $this->db->where('id',$fid)->update('kk_families',$row);
                    // KK sudah tertaut ke akun: undangan aktivasi yang masih terbuka tidak boleh dipakai lagi.
                    if($claim && $this->db->table_exists('account_invitations'))
                        $this->db->where(array('family_id'=>$fid,'used_at'=>NULL,'revoked_at'=>NULL))->update('account_invitations',array('revoked_at'=>$now));
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
            return isset($fid) ? (int) $fid : 0;
        } catch(Throwable $e) {$this->db->trans_rollback();throw $e;}
    }
}
