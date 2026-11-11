<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Citizen extends CI_Controller
{
    private function guard()
    {
        if (!$this->session->userdata('citizen_id')) redirect('warga/login');
        $active = $this->db->get_where('citizens', array('id' => (int) $this->session->userdata('citizen_id'), 'is_active' => 1))->row_array();
        if (!$active) {
            $this->session->unset_userdata(array('citizen_id','citizen_name'));
            redirect('warga/login');
        }
        if ($this->db->table_exists('chat_presence')) {$this->load->model('Chat_model');$this->Chat_model->touch('citizen',(int)$this->session->userdata('citizen_id'));}
    }

    private function shared()
    {
        return array('settings' => $this->Content_model->settings(),
            'menu' => $this->Content_model->pages(TRUE));
    }

    private function ensure_profile_fields()
    {
        $missing = array();
        if (!$this->db->field_exists('full_name', 'citizens')) $missing['full_name'] = array('type'=>'VARCHAR','constraint'=>140,'null'=>TRUE);
        if (!$this->db->field_exists('gender', 'citizens')) $missing['gender'] = array('type'=>'VARCHAR','constraint'=>10,'default'=>'female');
        if (!$this->db->field_exists('address', 'citizens')) $missing['address'] = array('type'=>'VARCHAR','constraint'=>255,'null'=>TRUE);
        if (!$this->db->field_exists('phone', 'citizens')) $missing['phone'] = array('type'=>'VARCHAR','constraint'=>25,'null'=>TRUE);
        if (!$this->db->field_exists('email', 'citizens')) $missing['email'] = array('type'=>'VARCHAR','constraint'=>190,'null'=>TRUE);
        if ($missing) {
            $this->load->dbforge();
            $this->dbforge->add_column('citizens', $missing);
        }
    }

    public function login()
    {
        if ($this->session->userdata('citizen_id')) redirect('warga');
        $data = $this->shared();
        $data['error'] = '';
        if ($this->input->method(TRUE) === 'POST') {
            $username = trim((string) $this->input->post('username', TRUE));
            $user = $this->db->get_where('citizens', array('username' => $username, 'is_active' => 1))->row_array();
            if ($user && password_verify((string) $this->input->post('password'), $user['password_hash'])) {
                $this->session->sess_regenerate(TRUE);
                $this->session->set_userdata(array('citizen_id' => $user['id'], 'citizen_name' => $user['display_name']));
                redirect('warga');
            }
            $data['error'] = 'Username atau password salah.';
        }
        $this->load->view('citizen/login', $data);
    }

    public function register()
    {
        if ($this->session->userdata('citizen_id')) redirect('warga');
        $this->ensure_profile_fields();

        $data = $this->shared();
        $data['error'] = '';
        if ($this->input->method(TRUE) === 'POST') {
            $display_name = preg_replace('/\s+/u', ' ', trim((string) $this->input->post('display_name', TRUE)));
            $username = trim((string) $this->input->post('username', TRUE));
            $password = (string) $this->input->post('password');
            $password_confirmation = (string) $this->input->post('password_confirmation');
            $gender = (string) $this->input->post('gender', TRUE);

            if (mb_strlen($display_name) < 3 || mb_strlen($display_name) > 140) {
                $data['error'] = 'Nama lengkap harus terdiri dari 3 sampai 140 karakter.';
            } elseif (!in_array($gender,array('male','female'),TRUE)) {
                $data['error'] = 'Jenis kelamin wajib dipilih.';
            } elseif ($username === '') {
                $data['error'] = 'Username wajib diisi.';
            } elseif ($password === '') {
                $data['error'] = 'Password wajib diisi.';
            } elseif ($password !== $password_confirmation) {
                $data['error'] = 'Konfirmasi password tidak sama.';
            } elseif ($this->db->get_where('citizens', array('username' => $username))->row_array()) {
                $data['error'] = 'Username sudah digunakan. Silakan pilih username lain.';
            } else {
                $created = $this->db->insert('citizens', array(
                    'username' => $username,
                    'display_name' => $display_name,
                    'full_name' => $display_name,
                    'gender' => $gender,
                    'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                    'is_active' => 1
                ));
                if (!$created) {
                    $data['error'] = 'Akun belum berhasil dibuat. Silakan coba lagi.';
                } else {
                    $citizen_id = (int) $this->db->insert_id();
                    $this->session->sess_regenerate(TRUE);
                    $this->session->set_userdata(array('citizen_id' => $citizen_id, 'citizen_name' => $display_name));
                    $this->session->set_flashdata('notice', 'Pendaftaran berhasil. Selamat datang di Portal Warga.');
                    redirect('warga');
                }
            }
        }
        $this->load->view('citizen/register', $data);
    }

    public function logout()
    {
        $this->session->unset_userdata(array('citizen_id', 'citizen_name'));
        redirect('warga/login');
    }

    public function index()
    {
        $this->guard();
        $this->ensure_profile_fields();
        $id = (int) $this->session->userdata('citizen_id');
        $data = $this->shared();
        $data['citizen'] = $this->db->get_where('citizens', array('id' => $id))->row_array();
        $profile_photos=glob(FCPATH.'uploads/profiles/citizen_'.$id.'_*')?:array();usort($profile_photos,function($a,$b){return filemtime($b)<=>filemtime($a);});$data['profile_photo']=$profile_photos?'uploads/profiles/'.basename($profile_photos[0]):(($data['citizen']['gender']??'female')==='male'?'assets/mockup/citizen-avatar-male.png':'assets/mockup/citizen-avatar-female.png');
        $data['requests'] = $this->db->where('citizen_id', $id)->order_by('created_at', 'DESC')->limit(5)->get('service_requests')->result_array();
        $data['dues'] = $this->db->where('citizen_id', $id)->order_by('period', 'DESC')->limit(5)->get('dues')->result_array();
        $data['unpaid_count'] = $this->db->where('citizen_id',$id)->where('amount_due > amount_paid',NULL,FALSE)->count_all_results('dues');
        $this->load->model('Dashboard_menu_model');
        $data['dashboard_items'] = $this->Dashboard_menu_model->visible();
        $data['dashboard_images'] = $this->Dashboard_menu_model->options('images');
        $data['dashboard_favorites'] = $this->Dashboard_menu_model->favorites($id);
        $data['dashboard_header'] = $this->Dashboard_menu_model->header_nav();
        // Badge notifikasi: pesan pengurus belum dibaca + tagihan belum lunas (hanya untuk fitur yang aktif).
        $chat_unread = 0;
        if ($this->feature_gate->link_ok('warga/chat', 'warga') && $this->db->table_exists('chat_messages')) {
            $conv = $this->db->select('id')->get_where('chat_conversations', array('citizen_id' => $id))->row_array();
            if ($conv) $chat_unread = $this->db->where('conversation_id', (int) $conv['id'])->where('sender_type', 'admin')->where('is_read', 0)->count_all_results('chat_messages');
        }
        $dues_open = $this->feature_gate->link_ok('warga/iuran', 'warga') ? (int) $data['unpaid_count'] : 0;
        $data['notify'] = array('chat' => (int) $chat_unread, 'dues' => $dues_open, 'total' => (int) $chat_unread + $dues_open,
            'target' => $chat_unread ? 'warga/chat' : ($dues_open ? 'warga/iuran' : ($this->feature_gate->link_ok('pengumuman', 'warga') ? 'pengumuman' : 'warga')));
        $this->load->model('Dashboard_summary_model');
        $data['mobile_summary'] = $this->Dashboard_summary_model->for_citizen($id);
        $this->load->view('citizen/dashboard', $data);
    }

    public function favorites_save()
    {
        $this->guard();
        if($this->input->method(TRUE)!=='POST')show_error('Metode tidak diizinkan.',405);
        $this->load->model('Dashboard_menu_model');
        try {
            $this->db->trans_start();
            $this->Dashboard_menu_model->save_favorites((int)$this->session->userdata('citizen_id'),$this->input->post('favorites')??array());
            $this->db->trans_complete();
            if($this->db->trans_status()===FALSE)throw new RuntimeException('Favorit belum berhasil disimpan.');
            $this->session->set_flashdata('notice','Menu favorit disimpan.');
        } catch(RuntimeException $e){$this->db->trans_rollback();$this->session->set_flashdata('notice',$e->getMessage());}
        redirect('warga');
    }
    public function services()
    {
        $this->guard();
        $data = $this->shared();
        $this->load->model('Letter_model');
        $data['catalog'] = $this->Letter_model->catalog();
        $this->load->view('citizen/services', $data);
    }

    public function history()
    {
        $this->guard();
        $data = $this->shared();
        $this->load->model('Letter_model');
        $data['catalog'] = $this->Letter_model->catalog();
        $data['requests'] = $this->db->select('service_requests.*,service_request_details.purpose,service_request_details.letter_number')
            ->join('service_request_details', 'service_request_details.request_id=service_requests.id', 'left')
            ->where('service_requests.citizen_id', (int) $this->session->userdata('citizen_id'))
            ->order_by('service_requests.created_at', 'DESC')->get('service_requests')->result_array();
        $this->load->view('citizen/history', $data);
    }

    public function profile()
    {
        $this->guard();
        $this->ensure_profile_fields();
        $data = $this->shared();
        $id = (int) $this->session->userdata('citizen_id');
        if ($this->input->method(TRUE) === 'POST') {
            if ((string) $this->input->post('profile_action', TRUE) === 'delete') {
                $deleted = FALSE;
                foreach (glob(FCPATH . 'uploads/profiles/citizen_' . $id . '_*') ?: array() as $photo) {
                    if (is_file($photo) && @unlink($photo)) $deleted = TRUE;
                }
                $this->session->set_flashdata(
                    $deleted ? 'profile_notice' : 'profile_error',
                    $deleted ? 'Foto profil berhasil dihapus.' : 'Belum ada foto profil yang dapat dihapus.'
                );
                redirect('warga/profil');
            }
            $file = isset($_FILES['profile_photo']) ? $_FILES['profile_photo'] : array();
            if (!$file || (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                $this->session->set_flashdata('profile_error', 'Pilih foto yang ingin digunakan.');
            } elseif ((int) $file['error'] !== UPLOAD_ERR_OK) {
                $this->session->set_flashdata('profile_error', 'Foto belum berhasil diunggah. Silakan coba lagi.');
            } elseif ((int) $file['size'] > 2 * 1024 * 1024) {
                $this->session->set_flashdata('profile_error', 'Ukuran foto maksimal 2 MB.');
            } else {
                $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
                $allowed = image_mime_map();
                $image_info = @getimagesize($file['tmp_name']);
                if (!$image_info || !isset($allowed[$mime])) {
                    $this->session->set_flashdata('profile_error', 'Format foto harus '.image_formats_label().'.');
                } else {
                    $dir = FCPATH . 'uploads/profiles/';
                    if (!is_dir($dir) && !mkdir($dir, 0755, TRUE)) {
                        $this->session->set_flashdata('profile_error', 'Folder penyimpanan foto tidak dapat dibuat.');
                    } else {
                        $filename = 'citizen_' . $id . '_' . bin2hex(random_bytes(8)) . '.' . $allowed[$mime];
                        if (!move_uploaded_file($file['tmp_name'], $dir . $filename)) {
                            $this->session->set_flashdata('profile_error', 'Foto gagal disimpan. Periksa izin folder upload.');
                        } else {
                            foreach (glob($dir . 'citizen_' . $id . '_*') ?: array() as $old) {
                                if (basename($old) !== $filename && is_file($old)) @unlink($old);
                            }
                            $this->session->set_flashdata('profile_notice', 'Foto profil berhasil diperbarui.');
                        }
                    }
                }
            }
            redirect('warga/profil');
        }
        $data['citizen'] = $this->db->get_where('citizens', array('id' => $id))->row_array();
        $profile_photos = glob(FCPATH . 'uploads/profiles/citizen_' . $id . '_*') ?: array();
        usort($profile_photos, function ($a, $b) { return filemtime($b) <=> filemtime($a); });
        $data['has_profile_photo'] = !empty($profile_photos);
        $data['profile_photo'] = $profile_photos ? 'uploads/profiles/' . basename($profile_photos[0]) : (($data['citizen']['gender']??'female')==='male'?'assets/mockup/citizen-avatar-male.png':'assets/mockup/citizen-avatar-female.png');
        $data['profile_counts'] = array(
            'requests' => $this->db->where('citizen_id', $id)->where('status !=', 'draft')->count_all_results('service_requests'),
            'paid_dues' => $this->db->where('citizen_id', $id)->where('amount_paid >= amount_due', NULL, FALSE)->count_all_results('dues'),
            'agendas' => $this->db->where('type', 'agenda')->where('status', 'published')->where('event_date >=', date('Y-m-d'))->count_all_results('posts'),
            'gallery' => $this->db->where('type', 'galeri')->where('status', 'published')->count_all_results('posts')
        );
        $data['profile_gallery'] = $this->db->where('type', 'galeri')->where('status', 'published')->where("image != ''", NULL, FALSE)
            ->order_by('created_at', 'DESC')->limit(5)->get('posts')->result_array();
        $data['profile_requests'] = $this->db->where('citizen_id', $id)->where('status !=', 'draft')
            ->order_by('created_at', 'DESC')->limit(4)->get('service_requests')->result_array();
        $data['profile_dues'] = $this->db->where('citizen_id', $id)->order_by('updated_at', 'DESC')->limit(2)->get('dues')->result_array();
        $this->load->view('citizen/profile', $data);
    }

    public function profile_edit()
    {
        $this->guard();
        $this->ensure_profile_fields();
        $id = (int) $this->session->userdata('citizen_id');
        $data = $this->shared();
        $data['error'] = '';
        $data['citizen'] = $this->db->get_where('citizens', array('id'=>$id))->row_array();
        // Modul 06 & Bagian 11: field tambahan non-sensitif dan persetujuan notifikasi email.
        $this->load->library('Custom_fields');
        $data['custom_values'] = $this->custom_fields->values($id);
        $custom_values = array();

        if ($this->input->method(TRUE) === 'POST') {
            list($custom_values, $custom_error) = $this->custom_fields->collect(TRUE);
            if ($custom_error) $data['error'] = $custom_error;
            $full_name = preg_replace('/\s+/u', ' ', trim((string)$this->input->post('full_name', TRUE)));
            $name = preg_replace('/\s+/u', ' ', trim((string)$this->input->post('display_name', TRUE)));
            $address = trim((string)$this->input->post('address', TRUE));
            $phone = trim((string)$this->input->post('phone', TRUE));
            $email = trim((string)$this->input->post('email', TRUE));
            $gender = (string)$this->input->post('gender', TRUE);

            if ($data['error']) {}
            elseif (mb_strlen($full_name) < 3 || mb_strlen($full_name) > 140) $data['error'] = 'Nama lengkap harus terdiri dari 3 sampai 140 karakter.';
            elseif (mb_strlen($name) < 2 || mb_strlen($name) > 140) $data['error'] = 'Nama tampilan harus terdiri dari 2 sampai 140 karakter.';
            elseif (mb_strlen($address) > 255) $data['error'] = 'Alamat maksimal 255 karakter.';
            elseif ($phone !== '' && !preg_match('/^[0-9+() .-]{7,25}$/', $phone)) $data['error'] = 'Format nomor HP tidak valid.';
            elseif ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) $data['error'] = 'Format email tidak valid.';
            elseif (!in_array($gender,array('male','female'),TRUE)) $data['error'] = 'Jenis kelamin wajib dipilih.';
            $photo = $_FILES['profile_photo'] ?? array();
            $photo_ext = '';
            if (!$data['error'] && $photo && (int)($photo['error']??UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                if ((int)$photo['error'] !== UPLOAD_ERR_OK || (int)$photo['size'] > 2*1024*1024) $data['error']='Foto profil harus berukuran maksimal 2 MB.';
                else {$mime=(new finfo(FILEINFO_MIME_TYPE))->file($photo['tmp_name']);$allowed=image_mime_map();if(!@getimagesize($photo['tmp_name'])||!isset($allowed[$mime]))$data['error']='Foto profil harus berformat '.image_formats_label().'.';else $photo_ext=$allowed[$mime];}
            }
            if (!$data['error']) {
                $update = array(
                    'full_name'=>$full_name,
                    'display_name'=>$name,
                    'address'=>$address ?: NULL,
                    'phone'=>$phone ?: NULL,
                    'email'=>$email ?: NULL
                    ,'gender'=>$gender
                );
                // Persetujuan email hanya bermakna bila alamat email diisi.
                if ($this->db->field_exists('notify_email', 'citizens')) $update['notify_email'] = ($email !== '' && $this->input->post('notify_email')) ? 1 : 0;
                $this->db->where('id', $id)->update('citizens', $update);
                $this->custom_fields->save($id, $custom_values);
                if ($photo_ext) {$dir=FCPATH.'uploads/profiles/';if(!is_dir($dir))mkdir($dir,0755,TRUE);$filename='citizen_'.$id.'_'.bin2hex(random_bytes(8)).'.'.$photo_ext;if(move_uploaded_file($photo['tmp_name'],$dir.$filename))foreach(glob($dir.'citizen_'.$id.'_*')?:array()as$old)if(basename($old)!==$filename&&is_file($old))@unlink($old);}
                $this->session->set_userdata('citizen_name', $name);
                $this->session->set_flashdata('profile_notice', 'Informasi pribadi berhasil diperbarui.');
                redirect('warga/profil');
            }
            $data['citizen'] = array_merge($data['citizen'], array('full_name'=>$full_name,'display_name'=>$name,'address'=>$address,'phone'=>$phone,'email'=>$email,'gender'=>$gender,'notify_email'=>$this->input->post('notify_email') ? 1 : 0));
            $posted = $this->input->post('cf', TRUE);
            if (is_array($posted)) foreach ($posted as $k => $v) if (is_scalar($v)) $data['custom_values'][(int) $k] = (string) $v;
        }
        $data['custom_fields_html'] = $this->custom_fields->render($data['custom_values'], 'pe', TRUE);
        $data['email_feature'] = $this->db->field_exists('notify_email', 'citizens');
        $profile_photos=glob(FCPATH.'uploads/profiles/citizen_'.$id.'_*')?:array();
        usort($profile_photos,function($a,$b){return filemtime($b)<=>filemtime($a);});
        $data['profile_photo']=$profile_photos?'uploads/profiles/'.basename($profile_photos[0]):(($data['citizen']['gender']??'female')==='male'?'assets/mockup/citizen-avatar-male.png':'assets/mockup/citizen-avatar-female.png');
        $this->load->view('citizen/profile_edit', $data);
    }

    public function request($id = 0)
    {
        $this->guard();
        $this->load->model('Letter_model');
        $data = $this->shared();
        $data['error'] = '';
        $draft = $id ? $this->db->select('service_requests.*,service_request_details.applicant_name,service_request_details.address,service_request_details.purpose,service_request_details.extra_data')->join('service_request_details','service_request_details.request_id=service_requests.id','left')->where('service_requests.id',(int)$id)->where('service_requests.citizen_id',(int)$this->session->userdata('citizen_id'))->where('service_requests.status','draft')->get('service_requests')->row_array() : NULL;
        if ($id && !$draft) show_404();
        $data['draft'] = $draft;
        $data['draft_values'] = $draft ? array_merge($draft,json_decode($draft['extra_data']??'',TRUE)?:array()) : array();
        $data['catalog'] = $this->Letter_model->catalog();
        $data['type_fields'] = $this->Letter_model->fields();
        // Verified (admin-approved) KK only: kk_families rows exist only after approval.
        $data['family_members'] = array(); $data['family_address'] = ''; $data['family_self'] = ''; $data['family_kk'] = '';
        $this->load->model('Kk_model');
        if ($this->Kk_model->ready() && ($family = $this->Kk_model->family((int)$this->session->userdata('citizen_id'), TRUE))) {
            $k = $family['keluarga'];
            $parts = array(trim((string)($k['alamat']??'')));
            if (($k['rt']??'')!=='' || ($k['rw']??'')!=='') $parts[] = 'RT '.($k['rt']??'-').' / RW '.($k['rw']??'-');
            if (!empty($k['kelurahan'])) $parts[] = 'Kel. '.$k['kelurahan'];
            if (!empty($k['kecamatan'])) $parts[] = 'Kec. '.$k['kecamatan'];
            if (!empty($k['kota'])) $parts[] = $k['kota'];
            $data['family_address'] = implode(', ', array_filter($parts));
            $data['family_kk'] = (string)($k['nomor_kk']??'');
            foreach ($family['anggota'] as $m) {
                $data['family_members'][] = array('nama'=>(string)($m['nama_lengkap']??''),'nik'=>(string)($m['nik']??''),'jk'=>(string)($m['jenis_kelamin']??''),
                    'tempat_lahir'=>(string)($m['tempat_lahir']??''),'tanggal_lahir'=>(string)($m['tanggal_lahir']??''),'agama'=>(string)($m['agama']??''),
                    'pekerjaan'=>(string)($m['pekerjaan']??''),'hubungan'=>(string)($m['hubungan_keluarga']??''));
            }
            // Pre-select the member whose name matches the account, if any.
            $me = $this->db->select('display_name,full_name')->get_where('citizens',array('id'=>(int)$this->session->userdata('citizen_id')))->row_array();
            foreach ($data['family_members'] as $m) foreach (array($me['full_name']??'',$me['display_name']??'') as $n) if ($n!=='' && strcasecmp(trim($n),trim($m['nama']))===0) { $data['family_self']=$m['nik']; break 2; }
        }
        $selected = (string) ($this->input->post('type', TRUE) ?: $this->input->get('jenis', TRUE) ?: ($draft['type']??''));
        $data['selected'] = isset($data['catalog'][$selected]) ? $selected : '';
        if ($this->input->method(TRUE) === 'POST') {
            $type = (string) $this->input->post('type', TRUE);
            $subject = isset($data['catalog'][$type]) ? $data['catalog'][$type][0] : '';
            $applicant = trim((string) $this->input->post('applicant_name', TRUE));
            $address = trim((string) $this->input->post('address', TRUE));
            $purpose = trim((string) $this->input->post('purpose', TRUE));
            $description = trim((string) $this->input->post('description', TRUE));
            $extra = array();
            foreach (array('applicant_nik','applicant_phone','applicant_birth_place','applicant_birth_date','applicant_gender','applicant_religion','applicant_occupation','applicant_ktp_address') as $field) $extra[$field]=trim((string)$this->input->post($field,TRUE));
            foreach ($this->Letter_model->fields($type) as $definition) $extra[$definition[0]]=trim((string)$this->input->post($definition[0],TRUE));
            foreach ($extra as $field=>$value) $extra[$field]=mb_substr($value,0,255);
            if ((string)$this->input->post('action',TRUE)==='draft') {
                if (!isset($data['catalog'][$type])) $data['error']='Pilih jenis surat sebelum menyimpan draf.';
                else {
                    $requestData=array('citizen_id'=>(int)$this->session->userdata('citizen_id'),'type'=>$type,'subject'=>$subject,'description'=>mb_substr($description,0,5000),'status'=>'draft');
                    $detailData=array('applicant_name'=>mb_substr($applicant,0,140),'address'=>mb_substr($address,0,255),'purpose'=>mb_substr($purpose,0,255),'extra_data'=>json_encode($extra,JSON_UNESCAPED_UNICODE));
                    $this->db->trans_start();
                    if ($draft) {
                        $this->db->where('id',(int)$id)->update('service_requests',$requestData);
                        $this->db->where('request_id',(int)$id)->update('service_request_details',$detailData);
                    } else {
                        $this->db->insert('service_requests',$requestData);
                        $id=$this->db->insert_id();
                        $this->db->insert('service_request_details',array('request_id'=>$id)+$detailData);
                    }
                    $this->db->trans_complete();
                    $this->session->set_flashdata('notice','Draf tersimpan. Pilih kembali dokumen pendukung saat mengirim pengajuan.');
                    redirect('warga/riwayat');
                }
            }
            $validDate = function($value) { $date=DateTime::createFromFormat('!Y-m-d',$value); return $date && $date->format('Y-m-d')===$value; };
            $fieldError='';
            foreach ($this->Letter_model->fields($type) as $definition) {
                $value=$extra[$definition[0]]??'';
                if ($value !== '' && $definition[2]==='date' && !$validDate($value)) $fieldError='Tanggal pada data khusus surat tidak valid.';
                if ($value !== '' && preg_match('/_nik$/',$definition[0]) && !preg_match('/^\d{16}$/',$value)) $fieldError='NIK pada data khusus surat harus 16 digit jika diisi.';
            }
            if (!$data['error'] && !isset($data['catalog'][$type])) {
                $data['error'] = 'Pilih jenis surat yang tersedia.';
            } elseif ($fieldError) {
                $data['error'] = $fieldError;
            } elseif (!in_array($extra['applicant_religion'], array('Islam','Kristen (Protestan)','Katolik','Hindu','Buddha','Khonghucu'), TRUE)) {
                $data['error'] = 'Pilih agama warga dari daftar yang tersedia.';
            } elseif ($extra['applicant_nik'] && !preg_match('/^\d{16}$/',$extra['applicant_nik'])) {
                $data['error'] = 'NIK warga harus 16 digit jika diisi.';
            } elseif ($extra['applicant_birth_date'] && (!$validDate($extra['applicant_birth_date']) || strtotime($extra['applicant_birth_date']) > time())) {
                $data['error'] = 'Tanggal lahir warga tidak valid.';
            } elseif ($type === 'kematian' && $extra['deceased_nik'] && !preg_match('/^\d{16}$/',$extra['deceased_nik'])) {
                $data['error'] = 'NIK almarhum harus 16 digit jika diisi.';
            } elseif ($type === 'kematian' && (($extra['deceased_birth_date'] && !$validDate($extra['deceased_birth_date'])) || ($extra['death_date'] && (!$validDate($extra['death_date']) || strtotime($extra['death_date']) > time())) || ($extra['deceased_birth_date'] && $extra['death_date'] && strtotime($extra['death_date']) < strtotime($extra['deceased_birth_date'])))) {
                $data['error'] = 'Tanggal lahir atau wafat tidak valid.';
            } else {
                $attachment = NULL;
                if (!empty($_FILES['attachment']['name'])) {
                    $file = $_FILES['attachment'];
                    $mime = is_uploaded_file($file['tmp_name']) ? (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']) : '';
                    $ext = array('application/pdf'=>'pdf','image/jpeg'=>'jpg','image/png'=>'png');
                    if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] > 5*1024*1024 || !isset($ext[$mime])) {
                        $data['error'] = 'Lampiran harus PDF, JPG, atau PNG dengan ukuran maksimal 5 MB.';
                    } else {
                        $dir = FCPATH.'uploads/letters/';
                        if (!is_dir($dir)) mkdir($dir, 0750, TRUE);
                        $attachment = bin2hex(random_bytes(16)).'.'.$ext[$mime];
                        if (!move_uploaded_file($file['tmp_name'], $dir.$attachment)) $data['error'] = 'Lampiran gagal disimpan.';
                    }
                }
            }
            if (!$data['error']) {
                $savedFiles = $attachment ? array($attachment) : array();
                $this->db->trans_start();
                $requestData=array('citizen_id'=>(int)$this->session->userdata('citizen_id'),'type'=>$type,'subject'=>mb_substr($subject,0,180),'description'=>mb_substr($description,0,5000),'status'=>'diajukan');
                $detailData=array('applicant_name'=>mb_substr($applicant,0,140),'address'=>mb_substr($address,0,255),'purpose'=>mb_substr($purpose,0,255),'extra_data'=>json_encode($extra,JSON_UNESCAPED_UNICODE),'attachment'=>$attachment);
                if ($draft) {
                    $request_id=(int)$id;
                    $this->db->where('id',$request_id)->update('service_requests',$requestData);
                    $this->db->where('request_id',$request_id)->update('service_request_details',$detailData);
                } else {
                    $this->db->insert('service_requests',$requestData);
                    $request_id=$this->db->insert_id();
                    $this->db->insert('service_request_details',array('request_id'=>$request_id)+$detailData);
                }
                $document_fields = $type === 'kematian' ? array('doc_ktp'=>'KTP Almarhum','doc_kk'=>'Kartu Keluarga','doc_death'=>'Surat Keterangan Wafat') : array();
                foreach ($document_fields as $field=>$label) {
                    if (empty($_FILES[$field]['name'])) continue;
                    $file=$_FILES[$field];
                    $mime=is_uploaded_file($file['tmp_name']) ? (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']) : '';
                    $allowed=array('application/pdf'=>'pdf','image/jpeg'=>'jpg','image/png'=>'png');
                    if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] > 2*1024*1024 || !isset($allowed[$mime])) { $data['error']=$label.' harus PDF, JPG, atau PNG maksimal 2 MB.'; break; }
                    $filename=bin2hex(random_bytes(16)).'.'.$allowed[$mime];
                    if (!move_uploaded_file($file['tmp_name'],FCPATH.'uploads/letters/'.$filename)) { $data['error']='Gagal menyimpan '.$label.'.'; break; }
                    $savedFiles[]=$filename;
                    $this->db->insert('service_request_documents',array('request_id'=>$request_id,'label'=>$label,'file_path'=>$filename));
                }
                if ($data['error']) { $this->db->trans_rollback(); foreach($savedFiles as $filename) @unlink(FCPATH.'uploads/letters/'.$filename); }
                else {
                $this->db->trans_complete();
                $this->session->set_flashdata('notice', 'Pengajuan berhasil dikirim.');
                redirect('warga/riwayat');
                }
            }
        }
        $this->load->view('citizen/request', $data);
    }

    public function request_detail($id = 0)
    {
        $this->guard();
        $this->load->model('Letter_model');
        $data = $this->shared();
        $data['item'] = $this->db->select('service_requests.*,service_request_details.applicant_name,service_request_details.address,service_request_details.purpose,service_request_details.extra_data,service_request_details.attachment,service_request_details.letter_number,service_request_details.approved_at')->join('service_request_details','service_request_details.request_id=service_requests.id','left')->where('service_requests.id',(int)$id)->where('service_requests.citizen_id',(int)$this->session->userdata('citizen_id'))->get('service_requests')->row_array();
        if (!$data['item']) show_404();
        $data['documents']=$this->db->get_where('service_request_documents',array('request_id'=>(int)$id))->result_array();
        $data['type_fields']=$this->Letter_model->fields($data['item']['type']);
        $this->load->view('citizen/request_detail', $data);
    }

    public function delete_request($id = 0)
    {
        $this->guard();
        if ($this->input->method(TRUE) !== 'POST') show_404();
        $id = (int) $id;
        $item = $this->db->select('service_requests.id,service_request_details.attachment')
            ->join('service_request_details', 'service_request_details.request_id=service_requests.id', 'left')
            ->where('service_requests.id', $id)
            ->where('service_requests.citizen_id', (int) $this->session->userdata('citizen_id'))
            ->get('service_requests')->row_array();
        if (!$item) show_404();

        $documents = $this->db->select('file_path')->get_where('service_request_documents', array('request_id'=>$id))->result_array();
        $files = array($item['attachment']);
        foreach ($documents as $document) $files[] = $document['file_path'];

        $this->db->trans_begin();
        $this->db->where('request_id', $id)->delete('service_request_documents');
        $this->db->where('request_id', $id)->delete('service_request_details');
        $this->db->where('id', $id)->where('citizen_id', (int) $this->session->userdata('citizen_id'))->delete('service_requests');
        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            show_error('Pengajuan gagal dihapus. Silakan coba lagi.', 500);
        }
        $this->db->trans_commit();
        foreach ($files as $filename) {
            if (!$filename) continue;
            $path = FCPATH . 'uploads/letters/' . basename($filename);
            if (is_file($path)) @unlink($path);
        }
        $this->session->set_flashdata('notice', 'Pengajuan berhasil dihapus.');
        redirect('warga/riwayat');
    }

    public function attachment($id = 0)
    {
        $item = $this->db->select('service_requests.citizen_id,service_request_details.attachment')->join('service_request_details','service_request_details.request_id=service_requests.id')->where('service_requests.id',(int)$id)->get('service_requests')->row_array();
        if (!$item || (!$this->session->userdata('admin_id') && (int)$this->session->userdata('citizen_id') !== (int)$item['citizen_id'])) show_404();
        $path = FCPATH.'uploads/letters/'.basename((string)$item['attachment']);
        if (!$item['attachment'] || !is_file($path)) show_404();
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($path);
        if (!in_array($mime,array('application/pdf','image/jpeg','image/png'),TRUE)) show_404();
        header('Content-Type: '.$mime); header('Content-Disposition: inline; filename="lampiran-'.(int)$id.'.'.pathinfo($path,PATHINFO_EXTENSION).'"'); header('X-Content-Type-Options: nosniff'); readfile($path);
    }

    public function document($id = 0)
    {
        $document=$this->db->select('service_request_documents.*,service_requests.citizen_id')->join('service_requests','service_requests.id=service_request_documents.request_id')->where('service_request_documents.id',(int)$id)->get('service_request_documents')->row_array();
        if (!$document || (!$this->session->userdata('admin_id') && (int)$this->session->userdata('citizen_id') !== (int)$document['citizen_id'])) show_404();
        $path=FCPATH.'uploads/letters/'.basename($document['file_path']);
        if (!is_file($path)) show_404();
        $mime=(new finfo(FILEINFO_MIME_TYPE))->file($path);
        if (!in_array($mime,array('application/pdf','image/jpeg','image/png'),TRUE)) show_404();
        header('Content-Type: '.$mime);header('Content-Disposition: inline; filename="dokumen-'.(int)$id.'.'.pathinfo($path,PATHINFO_EXTENSION).'"');header('X-Content-Type-Options: nosniff');readfile($path);
    }

    public function letter($id = 0)
    {
        $this->load->model('Letter_model');
        $preview = $this->input->get('preview', TRUE) === '1';
        $item = $this->db->select('service_requests.*,citizens.display_name,service_request_details.applicant_name,service_request_details.address,service_request_details.purpose,service_request_details.extra_data,service_request_details.letter_number,service_request_details.approved_at')->join('citizens','citizens.id=service_requests.citizen_id')->join('service_request_details','service_request_details.request_id=service_requests.id')->where('service_requests.id',(int)$id)->get('service_requests')->row_array();
        if (!$item || (!$this->session->userdata('admin_id') && (int)$this->session->userdata('citizen_id') !== (int)$item['citizen_id']) || (!$preview && ($item['status'] !== 'disetujui' || !$item['letter_number']))) show_404();
        $this->load->view('citizen/letter', array('item'=>$item,'settings'=>$this->Content_model->settings(),'catalog'=>$this->Letter_model->catalog(),'preview'=>$preview));
    }

    public function letter_asset($kind = '')
    {
        $fields = array('logo'=>'letter_logo','signature'=>'letter_signature','stamp'=>'letter_stamp');
        if (!isset($fields[$kind])) show_404();
        if (!$this->session->userdata('admin_id') && !$this->session->userdata('citizen_id')) {
            $id=(int)$this->input->get('id');
            $owned=$this->db->get_where('service_requests',array('id'=>$id,'citizen_id'=>(int)$this->session->userdata('citizen_id'),'status'=>'disetujui'))->row_array();
            if (!$owned) show_404();
        }
        $settings=$this->Content_model->settings();
        $filename=$settings[$fields[$kind]] ?? '';
        $path=FCPATH.'uploads/letters/'.basename($filename);
        if (!$filename || !is_file($path)) show_404();
        $mime=(new finfo(FILEINFO_MIME_TYPE))->file($path);
        if (!in_array($mime,array('image/png','image/jpeg','image/webp'),TRUE)) show_404();
        header('Content-Type: '.$mime); header('Cache-Control: private, max-age=300'); header('X-Content-Type-Options: nosniff'); readfile($path);
    }

    public function dues()
    {
        $this->guard();
        $this->load->helper(array('official_icons', 'content_form'));
        $data = $this->shared();
        $data['citizen'] = $this->db->select('display_name,gender')->get_where('citizens', array('id' => (int) $this->session->userdata('citizen_id')))->row_array();
        $data['dues'] = $this->db->where('citizen_id', (int) $this->session->userdata('citizen_id'))
            ->order_by('period', 'DESC')->get('dues')->result_array();
        $data['payments'] = $this->db->select('due_payments.*,dues.category,dues.period')
            ->join('dues', 'dues.id=due_payments.due_id')
            ->where('due_payments.citizen_id', (int) $this->session->userdata('citizen_id'))
            ->order_by('due_payments.created_at', 'DESC')->get('due_payments')->result_array();
        $this->load->view('citizen/dues', $data);
    }

    public function pay_due($id = 0)
    {
        $this->guard();
        if ($this->input->method(TRUE) !== 'POST') show_404();
        $due = $this->db->get_where('dues', array('id'=>(int)$id, 'citizen_id'=>(int)$this->session->userdata('citizen_id')))->row_array();
        if (!$due) show_404();
        $amount = (int) $this->input->post('amount');
        $method = (string) $this->input->post('method', TRUE);
        $remaining = (int)$due['amount_due'] - (int)$due['amount_paid'];
        $reject = function ($message) { $this->session->set_flashdata('due_error', $message); redirect('warga/iuran'); };
        if ($amount < 1 || $amount > $remaining || !in_array($method, array('transfer','tunai'), TRUE)) $reject('Jumlah pembayaran harus antara Rp 1 dan sisa tagihan.');
        if ($this->db->get_where('due_payments', array('due_id'=>(int)$id,'status'=>'pending'))->row_array()) $reject('Pembayaran sebelumnya masih menunggu verifikasi pengurus.');
        $proof = NULL;
        if ($method === 'transfer') {
            $file = $_FILES['proof'] ?? NULL;
            $allowed = array('image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','application/pdf'=>'pdf');
            $mime = $file && is_uploaded_file($file['tmp_name']) ? (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']) : '';
            if (!$file || $file['error'] !== UPLOAD_ERR_OK || $file['size'] > 5*1024*1024 || !isset($allowed[$mime])) $reject('Unggah bukti JPG, PNG, WebP, atau PDF maksimal 5 MB.');
            $proof = bin2hex(random_bytes(16)).'.'.$allowed[$mime];
            if (!move_uploaded_file($file['tmp_name'], FCPATH.'uploads/proofs/'.$proof)) $reject('Bukti pembayaran gagal disimpan. Silakan coba lagi.');
        }
        $this->db->insert('due_payments', array('due_id'=>(int)$id,'citizen_id'=>(int)$this->session->userdata('citizen_id'),
            'amount'=>$amount,'method'=>$method,'proof'=>$proof,'status'=>'pending'));
        if ($this->db->affected_rows() !== 1) {
            if ($proof) @unlink(FCPATH.'uploads/proofs/'.$proof);
            show_error('Pembayaran gagal dicatat.', 500);
        }
        $this->session->set_flashdata('notice', 'Pembayaran dikirim dan menunggu verifikasi pengurus.');
        redirect('warga/iuran');
    }

    public function due_proof($id = 0)
    {
        $payment = $this->db->get_where('due_payments', array('id'=>(int)$id))->row_array();
        if (!$payment || (!$this->session->userdata('admin_id') && (int)$this->session->userdata('citizen_id') !== (int)$payment['citizen_id'])) show_404();
        $path = FCPATH.'uploads/proofs/'.basename((string)$payment['proof']);
        if (!$payment['proof'] || !is_file($path)) show_404();
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($path);
        if (!in_array($mime, array('image/jpeg','image/png','image/webp','application/pdf'), TRUE)) show_404();
        header('Content-Type: '.$mime);header('Content-Disposition: inline; filename="bukti-iuran-'.(int)$id.'.'.pathinfo($path,PATHINFO_EXTENSION).'"');header('X-Content-Type-Options: nosniff');readfile($path);
    }

    public function chat()
    {
        $this->guard();redirect('warga#live-chat');
    }

    public function chat_messages()
    {
        $this->guard();$this->load->model('Chat_model');$this->Chat_model->ensure_schema();$id=(int)$this->session->userdata('citizen_id');
        $this->Chat_model->touch('citizen',$id);$conversation=$this->Chat_model->conversation_for_citizen($id);$this->Chat_model->mark_read($conversation['id'],'citizen');
        $this->output->set_content_type('application/json')->set_output(json_encode(array('messages'=>$this->Chat_model->messages($conversation['id'],(int)$this->input->get('after')),'admins'=>$this->Chat_model->online_admins(),'csrf'=>$this->security->get_csrf_hash())));
    }

    public function chat_send()
    {
        $this->guard();if($this->input->method(TRUE)!=='POST')show_error('Metode tidak diizinkan.',405);$this->load->model('Chat_model');$this->Chat_model->ensure_schema();$id=(int)$this->session->userdata('citizen_id');
        $message=trim((string)$this->input->post('message',FALSE));if($message===''||mb_strlen($message)>2000)show_error('Pesan wajib diisi dan maksimal 2000 karakter.',422);
        $conversation=$this->Chat_model->conversation_for_citizen($id,(int)$this->input->post('admin_id'));$this->Chat_model->touch('citizen',$id);$message_id=$this->Chat_model->send($conversation['id'],'citizen',$id,$message);
        $this->output->set_content_type('application/json')->set_output(json_encode(array('ok'=>TRUE,'id'=>$message_id,'csrf'=>$this->security->get_csrf_hash())));
    }

    public function password()
    {
        $this->guard();
        $data = $this->shared();
        $data['error'] = '';
        if ($this->input->method(TRUE) === 'POST') {
            $id = (int) $this->session->userdata('citizen_id');
            $user = $this->db->get_where('citizens', array('id' => $id))->row_array();
            $old = (string) $this->input->post('old_password');
            $new = (string) $this->input->post('new_password');
            $confirmation = (string) $this->input->post('password_confirmation');
            if (!password_verify($old, $user['password_hash'])) $data['error'] = 'Password lama salah.';
            elseif (strlen($new) < 8) $data['error'] = 'Password baru minimal 8 karakter.';
            elseif ($new !== $confirmation) $data['error'] = 'Konfirmasi password baru tidak sama.';
            else {
                $this->db->where('id', $id)->update('citizens', array('password_hash' => password_hash($new, PASSWORD_DEFAULT)));
                $this->session->set_flashdata('notice', 'Password berhasil diganti.');
                redirect('warga/password');
            }
        }
        $this->load->view('citizen/password', $data);
    }
}
