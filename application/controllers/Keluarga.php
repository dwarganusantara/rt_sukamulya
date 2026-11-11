<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Keluarga extends CI_Controller
{
    private function boot($admin=FALSE)
    {
        if($admin) {
            $this->load->library('Platform_owner');
            if($this->platform_owner->valid_session()) {
                $this->session->set_userdata('admin_role','admin');
            } else {
            if(!$this->session->userdata('admin_id')) redirect('admin/login');
            $account=$this->db->get_where('admins',array('id'=>(int)$this->session->userdata('admin_id')))->row_array();
            if(!$account || (isset($account['is_active']) && !(int)$account['is_active'])) {$this->session->sess_destroy(); redirect('admin/login');}
            if(!in_array($account['role'],array('admin','operator','ketua'),TRUE)) show_error('Akses tidak diizinkan.',403);
            }
        } else {
            if(!$this->session->userdata('citizen_id')) redirect('warga/login');
            if(!$this->db->get_where('citizens',array('id'=>(int)$this->session->userdata('citizen_id'),'is_active'=>1))->row_array()) show_error('Akun tidak aktif.',403);
        }
        $this->output->set_header('Cache-Control: no-store, private');
        // Never render SQL values containing identity fields on a database error.
        $this->db->db_debug=FALSE;
        $this->db->save_queries=FALSE;
        $this->load->model('Kk_model'); $this->load->library('Kk_fields');
        if(!$this->Kk_model->ready()) show_error('Modul keluarga belum diaktifkan. Pengelola perlu menjalankan migrasi kk_scan_upgrade.sql setelah backup.',503);
    }

    public function index()
    {
        $this->boot(); $id=(int)$this->session->userdata('citizen_id');
        $first=$this->db->select_min('id','id')->get_where('kk_scan_requests',array('citizen_id'=>$id))->row_array();
        $data=array('settings'=>$this->Content_model->settings(),'menu'=>$this->Content_model->pages(TRUE),'family'=>$this->Kk_model->family($id),'requests'=>$this->db->order_by('id','DESC')->limit(30)->get_where('kk_scan_requests',array('citizen_id'=>$id))->result_array(),'first_request_id'=>(int)($first['id']??0),'member_fields'=>$this->kk_fields->member_fields,'family_fields'=>$this->kk_fields->family_fields);
        $record=$this->db->select('updated_at')->get_where('kk_families',array('owner_citizen_id'=>$id))->row_array();
        $data['family_updated_at']=$record['updated_at']??NULL;
        $this->load->view('citizen/family',$data);
    }

    public function scan()
    {
        $this->boot(); $error=''; $notice='';
        $data=$this->kk_fields->normalize(array());
        if($this->input->method(TRUE)==='GET') {
            $draft=$this->session->userdata('kk_form_draft');
            if(is_array($draft)) $data=$this->kk_fields->normalize($draft);
            elseif($this->input->get('manual')) {
                $existing=$this->Kk_model->family((int)$this->session->userdata('citizen_id'),TRUE);
                if($existing) $data=$this->kk_fields->normalize($existing);
            }
        }
        if($this->input->method(TRUE)==='POST') {
            try {
                if($this->input->post('action')==='scan') {
                    $last=(int)$this->session->userdata('kk_scan_at');
                    if(time()-$last<15) throw new RuntimeException('Tunggu 15 detik sebelum scan ulang.');
                    $this->session->set_userdata('kk_scan_at',time());
                    $this->load->library('Kk_ocr');
                    $data=$this->kk_ocr->scan($_FILES['document'] ?? NULL);
                    $notice='Hasil pembacaan awal tersedia. Periksa setiap field dan jumlah anggota; kelengkapan belum dijamin.';
                } elseif($this->input->post('action')==='draft') {
                    $data=$this->kk_fields->normalize(array('keluarga'=>$this->input->post('keluarga'),'anggota'=>$this->input->post('anggota')));
                    $this->session->set_userdata('kk_form_draft',$data);
                    $notice='Draft berhasil disimpan untuk sesi ini. Anda dapat melanjutkan sebelum keluar dari akun.';
                } else {
                    $data=$this->kk_fields->normalize(array('keluarga'=>$this->input->post('keluarga'),'anggota'=>$this->input->post('anggota')));
                    $errors=$this->kk_fields->validate($data);
                    if(!$this->input->post('consent')) $errors[]='Konfirmasi kewenangan dan pemeriksaan data wajib dicentang.';
                    if($errors) throw new RuntimeException(implode(' ',$errors));
                    $id=$this->Kk_model->submit((int)$this->session->userdata('citizen_id'),$data);
                    $this->session->unset_userdata('kk_form_draft');
                    $this->session->set_flashdata('notice','Pengajuan KK-'.str_pad($id,6,'0',STR_PAD_LEFT).' berhasil dikirim. Menunggu verifikasi admin.');
                    redirect('warga/keluarga');
                }
            } catch(Throwable $e) {$error=$e->getMessage();}
        }
        $this->load->view('citizen/family_scan',array('settings'=>$this->Content_model->settings(),'menu'=>$this->Content_model->pages(TRUE),'fields'=>$data,'error'=>$error,'notice'=>$notice,'family_fields'=>$this->kk_fields->family_fields,'member_fields'=>$this->kk_fields->member_fields,'relationships'=>$this->kk_fields->relationships));
    }

    /**
     * Current CSRF token for the form about to be submitted.
     * Background GETs (chat polling) that overlap a slow POST such as OCR can put an
     * older token back into the cookie; the page re-reads it here right before submitting.
     */
    public function token()
    {
        if(!$this->session->userdata('citizen_id')) {$this->output->set_status_header(401); return;}
        $this->output->set_header('Cache-Control: no-store, private')->set_content_type('application/json')
            ->set_output(json_encode(array('name'=>$this->security->get_csrf_token_name(),'hash'=>$this->security->get_csrf_hash())));
    }

    public function admin($id=0)
    {
        $this->boot(TRUE);
        $labels=array('approved'=>'disetujui','revision_requested'=>'dikembalikan untuk perbaikan','rejected'=>'ditolak');
        if($this->input->method(TRUE)==='POST') {
            $status=$this->input->post('status',TRUE); $notes=trim((string)$this->input->post('notes',TRUE));
            // Only return to this module's own pages (keeps list filters; no open redirect).
            $return=(string)$this->input->post('return',TRUE);
            if(!preg_match('#^admin/kk/pengajuan(/\d+)?(\?[\w=&%+.\-]*)?$#',$return)) $return='admin/kk/pengajuan';
            try {
                if(!$id) throw new RuntimeException('Pengajuan tidak ditemukan.');
                if(!in_array($status,array('approved','revision_requested','rejected'),TRUE)) throw new RuntimeException('Status tidak valid.');
                if($status==='approved' && !$this->input->post('ownership_checked')) throw new RuntimeException('Centang konfirmasi pemeriksaan kewenangan pemohon sebelum menyetujui.');
                if($status!=='approved' && $notes==='') throw new RuntimeException('Catatan/alasan wajib diisi agar warga tahu yang perlu diperbaiki.');
                $this->Kk_model->review((int)$id,(int)$this->session->userdata('admin_id'),$status,mb_substr($notes,0,2000));
                // Audit keputusan (Bagian 10.7) + salinan email opsional. Catatan tidak memuat NIK (disanitasi rt_audit).
                $this->load->helper('rt_audit');
                rt_audit('kk_'.$status,'kk_request:'.(int)$id,$notes);
                $owner=$this->db->select('citizen_id')->get_where('kk_scan_requests',array('id'=>(int)$id))->row_array();
                if($owner) {
                    $this->load->library('Notifier');
                    $this->notifier->enqueue_citizen((int)$owner['citizen_id'],'Pengajuan data KK '.$labels[$status],
                        'Pengajuan data Kartu Keluarga Anda (KK-'.str_pad((int)$id,6,'0',STR_PAD_LEFT).') telah '.$labels[$status].'.'.($status!=='approved'&&$notes!==''?"\nCatatan pengurus: ".$notes:''),
                        'kk:'.(int)$id.':'.$status.':'.date('YmdHis'));
                }
                $this->session->set_flashdata('kk_notice','Pengajuan KK-'.str_pad((int)$id,6,'0',STR_PAD_LEFT).' berhasil '.$labels[$status].'.');
            } catch(Throwable $e) {
                $this->session->set_flashdata('kk_error',$e->getMessage());
            }
            redirect($return);
        }
        if($id) {$this->admin_detail((int)$id); return;}

        $status=(string)$this->input->get('status',TRUE);
        if(!in_array($status,array('pending','approved','rejected','revision_requested','all'),TRUE)) $status='pending';
        $period=(string)$this->input->get('periode',TRUE);
        $periods=array('all'=>'Semua Waktu','today'=>'Hari Ini','7'=>'7 Hari Terakhir','30'=>'30 Hari Terakhir');
        if(!isset($periods[$period])) $period='all';
        $q=mb_substr(trim((string)$this->input->get('q',TRUE)),0,60);
        $per=(int)$this->input->get('tampil'); if(!in_array($per,array(10,25,50),TRUE)) $per=10;
        $page=max(1,(int)$this->input->get('hal'));

        $counts=array('pending'=>0,'approved'=>0,'rejected'=>0,'revision_requested'=>0);
        foreach($this->db->select('status,COUNT(*) n')->group_by('status')->get('kk_scan_requests')->result_array() as $row) $counts[$row['status']]=(int)$row['n'];
        $counts['all']=array_sum($counts);

        $filter=function() use($status,$period,$q) {
            $this->db->from('kk_scan_requests r')->join('citizens c','c.id=r.citizen_id');
            if($status!=='all') $this->db->where('r.status',$status);
            if($period==='today') $this->db->where('r.created_at >=',date('Y-m-d 00:00:00'));
            elseif($period!=='all') $this->db->where('r.created_at >=',date('Y-m-d H:i:s',strtotime('-'.$period.' days')));
            // proposed_data_json holds nama, NIK and nomor KK, so one LIKE covers all three.
            if($q!=='') $this->db->group_start()->like('c.display_name',$q)->or_like('c.full_name',$q)->or_like('r.proposed_data_json',$q)->group_end();
        };
        $filter(); $total=(int)$this->db->count_all_results();
        $pages=max(1,(int)ceil($total/$per)); $page=min($page,$pages);
        $filter();
        $rows=$this->db->select('r.id,r.citizen_id,r.status,r.created_at,r.proposed_data_json,r.review_notes,r.reviewed_at,c.display_name,c.full_name')
            ->order_by("r.status='pending'",'DESC',FALSE)->order_by('r.id','DESC')->limit($per,($page-1)*$per)->get()->result_array();
        $items=array();
        foreach($rows as $row) {
            $data=json_decode($row['proposed_data_json'],TRUE)?:array();
            $members=array();
            foreach(($data['anggota']??array()) as $m) $members[]=array('nama'=>$m['nama_lengkap']??'','nik'=>$m['nik']??'','hubungan'=>$m['hubungan_keluarga']??'','jk'=>$m['jenis_kelamin']??'');
            $head=''; foreach($members as $m) if(strcasecmp($m['hubungan'],'Kepala Keluarga')===0) {$head=$m['nama'];break;}
            $items[]=array('id'=>(int)$row['id'],'code'=>'KK-'.str_pad($row['id'],6,'0',STR_PAD_LEFT),'status'=>$row['status'],
                'name'=>($row['full_name']?:$row['display_name']),'nomor_kk'=>$data['keluarga']['nomor_kk']??'','alamat'=>$data['keluarga']['alamat']??'',
                'rt_rw'=>trim(($data['keluarga']['rt']??'').' / '.($data['keluarga']['rw']??''),' /'),'kepala'=>$head,'members'=>$members,
                'created_at'=>$row['created_at'],'review_notes'=>(string)$row['review_notes'],'reviewed_at'=>$row['reviewed_at']);
        }
        $query=array_filter(array('status'=>$status,'periode'=>$period,'q'=>$q,'tampil'=>$per,'hal'=>$page),function($v){return $v!=='' && $v!==NULL;});
        $this->load->view('admin/header');
        $this->load->view('admin/kk_requests',compact('items','counts','status','period','periods','q','per','page','pages','total','query'));
        $this->load->view('admin/footer');
    }

    private function admin_detail($id)
    {
        $request=$this->db->select('r.*,c.display_name,c.full_name,c.phone,c.username')->from('kk_scan_requests r')->join('citizens c','c.id=r.citizen_id')->where('r.id',$id)->get()->row_array();
        if(!$request) show_404();
        $fields=$this->kk_fields->normalize(json_decode($request['proposed_data_json'],TRUE)?:array());
        $existing=$this->Kk_model->family($request['citizen_id']);
        $reviewer=$request['reviewed_by']?$this->db->select('username')->get_where('admins',array('id'=>(int)$request['reviewed_by']))->row_array():NULL;
        $this->load->view('admin/header');
        $this->load->view('admin/kk_request_detail',array('request'=>$request,'fields'=>$fields,'existing'=>$existing,'reviewer'=>$reviewer['username']??'',
            'family_fields'=>$this->kk_fields->family_fields,'member_fields'=>$this->kk_fields->member_fields));
        $this->load->view('admin/footer');
    }
}
