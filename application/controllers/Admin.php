<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends CI_Controller
{
    private function guard($roles = array())
    {
        $this->load->library('Platform_owner');
        if($this->platform_owner->valid_session()){
            $this->session->set_userdata('admin_role','admin');
            $this->output->set_header('Cache-Control: no-store, private');
            return;
        }
        if (!$this->session->userdata('admin_id')) redirect('admin/login');
        // Akun yang dicabut (is_active=0) langsung kehilangan akses walau sesinya masih ada.
        if ($this->db->field_exists('is_active', 'admins')) {
            $account = $this->db->select('role,is_active')->get_where('admins', array('id' => (int) $this->session->userdata('admin_id')))->row_array();
            if (!$account || !(int) $account['is_active']) {$this->session->sess_destroy(); redirect('admin/login');}
            if ($account['role'] !== $this->session->userdata('admin_role')) $this->session->set_userdata('admin_role', $account['role']);
        }
        if ($roles && !in_array($this->session->userdata('admin_role'), $roles, TRUE)) show_error('Akses tidak diizinkan.', 403);
        if ($this->db->table_exists('chat_presence')) {$this->load->model('Chat_model');$this->Chat_model->touch('admin',(int)$this->session->userdata('admin_id'));}
    }

    private function log_action($action, $subject)
    {
        $this->load->helper('rt_audit');
        rt_audit($action, $subject);
    }

    private function valid_date($value)
    {
        $date = DateTime::createFromFormat('!Y-m-d', (string) $value);
        return $date && $date->format('Y-m-d') === $value;
    }

    private function ensure_official_hierarchy_schema()
    {
        if ($this->db->table_exists('officials') && !$this->db->field_exists('parent_id', 'officials')) {
            $this->db->query('ALTER TABLE `officials` ADD COLUMN `parent_id` INT UNSIGNED NULL AFTER `photo`, ADD KEY `idx_official_parent` (`parent_id`)');
        }
        if ($this->db->table_exists('officials') && !$this->db->field_exists('icon', 'officials')) {
            $this->db->query("ALTER TABLE `officials` ADD COLUMN `icon` VARCHAR(20) NOT NULL DEFAULT '👤' AFTER `description`");
        }
    }

    private function ensure_citizen_profile_schema()
    {
        $missing = array();
        foreach (array(
            'full_name'=>array('type'=>'VARCHAR','constraint'=>140,'null'=>TRUE),
            'gender'=>array('type'=>'VARCHAR','constraint'=>10,'default'=>'female'),
            'address'=>array('type'=>'VARCHAR','constraint'=>255,'null'=>TRUE),
            'phone'=>array('type'=>'VARCHAR','constraint'=>25,'null'=>TRUE),
            'email'=>array('type'=>'VARCHAR','constraint'=>190,'null'=>TRUE),
            'notes'=>array('type'=>'TEXT','null'=>TRUE)
        ) as $field=>$definition) if (!$this->db->field_exists($field,'citizens')) $missing[$field]=$definition;
        if ($missing) {$this->load->dbforge();$this->dbforge->add_column('citizens',$missing);}
    }

    private function render($view, $data = array())
    {
        $this->load->view('admin/header', $data);
        $this->load->view('admin/' . $view, $data);
        $this->load->view('admin/footer');
    }

    public function login()
    {
        if ($this->session->userdata('admin_id') && $this->input->method(TRUE)!=='POST') redirect('admin');
        $data = array('error' => '');
        if ($this->input->method(TRUE) === 'POST') {
            $username = trim((string) $this->input->post('username', TRUE));
            $password = (string) $this->input->post('password');
            $this->load->library('Platform_owner');
            if($this->platform_owner->recognizes($username)){
                $this->load->model('Subscription_model');$this->Subscription_model->ensure();
                $data['error']=$this->platform_owner->sign_in($username,$password);
                if($data['error']==='')redirect('admin');
                $this->load->view('admin/login',$data);return;
            }
            // SEC-02: brute-force protection (failed attempts only; platform owner has its own limiter above).
            $this->load->library('Login_throttle');
            if ($this->login_throttle->blocked('admin', $username)) {
                $data['error'] = Login_throttle::MESSAGE;
                $this->load->view('admin/login', $data);
                return;
            }
            $user = $this->db->get_where('admins', array('username' => $username))->row_array();
            if (!$user) Login_throttle::dummy_verify($password);
            if ($user && password_verify($password, $user['password_hash'])) {
                // SEC-09: the "deactivated" notice is only shown to someone who knows the password,
                // so it no longer reveals which usernames exist.
                if (isset($user['is_active']) && !(int) $user['is_active']) {
                    $data['error'] = 'Akun ini sudah dinonaktifkan. Hubungi Administrator.';
                    $this->load->view('admin/login', $data);
                    return;
                }
                $this->login_throttle->succeed('admin', $username);
                $this->session->sess_regenerate(TRUE);
                $this->session->unset_userdata(array('platform_owner_name','platform_owner_fingerprint','platform_authenticated_at','platform_admin_id'));
                $this->session->set_userdata('admin_id', $user['id']);
                $this->session->set_userdata('admin_role', $user['role']);
                redirect('admin');
            }
            $this->login_throttle->fail('admin', $username);
            $data['error'] = 'Username atau password salah.';
        }
        $this->load->view('admin/login', $data);
    }

    public function logout()
    {
        // SEC-08: only a POST (CSRF-checked) or the per-session logout link may end the session.
        if (!logout_request_ok()) {logout_confirm_page('admin/logout', 'admin'); return;}
        $this->session->sess_destroy();
        redirect('admin/login');
    }

    public function index()
    {
        $this->guard();
        if ($this->session->userdata('admin_role') === 'bendahara') redirect('admin/transactions');
        $this->load->helper(array('official_icons', 'content_form'));
        $month = date('Y-m');
        $dues = $this->db->select('COUNT(*) AS total, SUM(CASE WHEN amount_due > 0 AND amount_paid >= amount_due THEN 1 ELSE 0 END) AS paid_count, SUM(amount_due) AS due_total, SUM(amount_paid) AS paid_total', FALSE)
            ->where('period', $month)->get('dues')->row_array();
        $data = array(
            'settings' => $this->Content_model->settings(),
            'admin_user' => $this->db->select('username,role')->get_where('admins', array('id' => $this->session->userdata('admin_id')))->row_array(),
            'citizen_count' => $this->db->where('is_active', 1)->count_all_results('citizens'),
            'citizen_new' => $this->db->where('is_active', 1)->where('created_at >=', $month . '-01')->count_all_results('citizens'),
            'announcement_new' => $this->db->where('type', 'pengumuman')->where('status', 'published')->where('created_at >=', $month . '-01')->count_all_results('posts'),
            'agenda_count' => $this->db->where('type', 'agenda')->where('status', 'published')->where('event_date >=', $month . '-01')->where('event_date <', date('Y-m-d', strtotime($month . '-01 +1 month')))->count_all_results('posts'),
            'announcement_count' => $this->db->where('type', 'pengumuman')->where('status', 'published')->count_all_results('posts'),
            'announcements' => $this->db->where('type', 'pengumuman')->order_by('created_at', 'DESC')->limit(3)->get('posts')->result_array(),
            'agendas' => $this->db->where('type', 'agenda')->where('event_date >=', date('Y-m-d'))->order_by('event_date', 'ASC')->limit(4)->get('posts')->result_array(),
            'gallery' => $this->db->where('type', 'galeri')->where('image IS NOT NULL', NULL, FALSE)->order_by('created_at', 'DESC')->limit(4)->get('posts')->result_array(),
            'waiting_requests' => $this->db->where('status', 'diajukan')->count_all_results('service_requests'),
            // Pembayaran iuran dari warga yang menunggu verifikasi (banner di beranda; hanya untuk peran yang boleh memverifikasi).
            'waiting_payments' => in_array($this->session->userdata('admin_role'), array('admin', 'bendahara', 'ketua'), TRUE)
                ? $this->db->select('COUNT(*) n, COALESCE(SUM(amount),0) total, MAX(created_at) latest', FALSE)->where('status', 'pending')->get('due_payments')->row_array()
                : array('n' => 0, 'total' => 0, 'latest' => NULL),
            'dues_summary' => $dues,
            'pending_count' => $this->db->where('status', 'diajukan')->count_all_results('service_requests') + $this->db->where('status', 'pending')->count_all_results('posts') + $this->db->where('status', 'pending')->count_all_results('due_payments'),
        );
        // Modul 01: ringkasan monitoring lintas modul (semua angka dari data tersimpan).
        $this->load->model('Ops_model');
        $data['ops'] = $this->Ops_model->monitoring();
        $this->config->load('rt_access', TRUE);
        $data['rt_perm'] = (array) $this->config->item('rt_permissions', 'rt_access');
        $role = (string) $this->session->userdata('admin_role');
        $can = function ($perm) use ($data, $role) {return in_array($role, $data['rt_perm'][$perm] ?? array('admin'), TRUE);};
        // Manajemen Fitur & Menu: kartu fitur nonaktif tidak ditampilkan di dashboard (BR-10).
        if (!isset($this->feature_gate)) $this->load->library('Feature_gate');
        $on = function ($code) {return $this->feature_gate->for_staff($code);};
        $data['feature_on'] = array_map($on, array_combine(array_keys($this->feature_gate->catalog()), array_keys($this->feature_gate->catalog())));
        // Semua menu sidebar melalui proses langganan: data menu yang belum berlangganan tidak dikirim ke tampilan.
        if (!isset($this->menu_access)) $this->load->library('Menu_access');
        $menu_on = function ($path) {return $this->menu_access->commercial($path) === 'ALLOW';};
        $data['citizens_locked'] = !$menu_on('admin/citizens');
        if ($data['citizens_locked']) {$data['citizen_count'] = NULL; $data['citizen_new'] = NULL;}
        // Statistik warga per akhir bulan (agregat, tanpa data pribadi). Riwayat KK memakai tanggal persetujuan
        // verifikasi KK; anggota memakai resident_since dan status_changed_at. Data tanpa tanggal dihitung di semua bulan.
        $data['stat_span'] = (int) $this->input->get('statistik') === 12 ? 12 : 6;
        $data['resident_stats'] = NULL;
        if ($can('penduduk.read') && $on('resident_profile') && $menu_on('admin/penduduk') && !$data['citizens_locked'] && $this->db->table_exists('kk_families')) {
            $families = $this->db->table_exists('kk_scan_requests')
                ? $this->db->query("SELECT (SELECT MIN(r.reviewed_at) FROM kk_scan_requests r WHERE r.citizen_id=f.owner_citizen_id AND r.status='approved') AS since FROM kk_families f")->result_array()
                : $this->db->select('NULL AS since', FALSE)->get('kk_families')->result_array();
            $members = $this->db->table_exists('kk_members') ? $this->db->select('status,status_changed_at,resident_since')->get('kk_members')->result_array() : array();
            $short = array(1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des');
            $points = array();
            for ($i = $data['stat_span'] - 1; $i >= 0; $i--) {
                $first = strtotime(date('Y-m-01') . " -$i month");
                $end = date('Y-m-t 23:59:59', $first); $end_date = substr($end, 0, 10);
                $fam = 0; foreach ($families as $f) if (!$f['since'] || $f['since'] <= $end) $fam++;
                $mem = 0;
                foreach ($members as $m) {
                    if ($m['resident_since'] && $m['resident_since'] > $end_date) continue;
                    if ($m['status'] === 'aktif' || ($m['status_changed_at'] && $m['status_changed_at'] > $end)) $mem++;
                }
                $points[] = array('label' => $short[(int) date('n', $first)], 'families' => $fam, 'members' => $mem);
            }
            $start = $points[0]['members']; $last = end($points)['members'];
            $change = $start > 0 ? (($last - $start >= 0 ? '+' : '') . (int) round(100 * ($last - $start) / $start) . '%') : ($last > 0 ? '+' . $last . ' warga' : '0%');
            $data['resident_stats'] = array('points' => $points, 'families' => end($points)['families'], 'members' => $last, 'change' => $change);
        }
        $data['complaints_latest'] = ($can('pengaduan.read') && $on('complaints') && $this->db->table_exists('complaints'))
            ? $this->db->select('id,ticket_no,category,title,location,status,created_at')->order_by('id', 'DESC')->limit(3)->get('complaints')->result_array() : NULL;
        $data['letters_latest'] = in_array($role, array('admin', 'operator', 'ketua'), TRUE) && $on('letters')
            ? $this->db->select('service_requests.id,service_requests.subject,service_requests.status,service_requests.created_at,citizens.display_name,letter_types.title AS letter_title')
                ->join('citizens', 'citizens.id=service_requests.citizen_id', 'left')->join('letter_types', 'letter_types.type_key=service_requests.type', 'left')
                ->where('service_requests.status !=', 'draft')->order_by('service_requests.id', 'DESC')->limit(3)->get('service_requests')->result_array() : NULL;
        $data['show_dues'] = in_array($role, array('admin', 'bendahara', 'ketua'), TRUE) && $on('dues');
        $data['show_cash'] = $can('kas.report') && $on('cash_report');

        // Data ringkas dashboard baru. Seluruh nilai berasal dari data operasional,
        // bukan angka contoh pada desain referensi.
        $data['cash_month'] = array('income' => 0, 'expense' => 0, 'balance' => !empty($data['ops']) ? (float) $data['ops']['cash_balance'] : 0, 'trend' => array());
        $data['cash_span'] = (int) $this->input->get('kas') === 12 ? 12 : 6;
        if ($data['show_cash'] && $this->db->table_exists('transactions')) {
            $cash_now = $this->db->select("COALESCE(SUM(CASE WHEN type='masuk' THEN amount ELSE 0 END),0) income, COALESCE(SUM(CASE WHEN type='keluar' THEN amount ELSE 0 END),0) expense", FALSE)
                ->where('status', 'verified')->where('period', $month)->get('transactions')->row_array();
            $data['cash_month']['income'] = (float) ($cash_now['income'] ?? 0);
            $data['cash_month']['expense'] = (float) ($cash_now['expense'] ?? 0);
            // Satu query GROUP BY untuk seluruh rentang (hanya transaksi terverifikasi, sama dengan modul Kas RT).
            $first_period = date('Y-m', strtotime(date('Y-m-01') . ' -' . ($data['cash_span'] - 1) . ' month'));
            $by_period = array();
            foreach ($this->db->select("period, COALESCE(SUM(CASE WHEN type='masuk' THEN amount ELSE 0 END),0) income, COALESCE(SUM(CASE WHEN type='keluar' THEN amount ELSE 0 END),0) expense", FALSE)
                ->where('status', 'verified')->where('period >=', $first_period)->where('period <=', $month)->group_by('period')->get('transactions')->result_array() as $row) $by_period[$row['period']] = $row;
            for ($i = $data['cash_span'] - 1; $i >= 0; $i--) {
                $period = date('Y-m', strtotime(date('Y-m-01') . " -$i month"));
                $data['cash_month']['trend'][] = array('period' => $period, 'income' => (float) ($by_period[$period]['income'] ?? 0), 'expense' => (float) ($by_period[$period]['expense'] ?? 0));
            }
        }
        $data['gender_stats'] = array('male' => 0, 'female' => 0);
        if (!$data['citizens_locked'] && $this->db->field_exists('gender', 'citizens')) {
            foreach ($this->db->select('gender,COUNT(*) total', FALSE)->where('is_active', 1)->group_by('gender')->get('citizens')->result_array() as $g) {
                if (isset($data['gender_stats'][$g['gender']])) $data['gender_stats'][$g['gender']] = (int) $g['total'];
            }
        }
        $data['letters_processing'] = $this->db->where_in('status', array('diajukan', 'diproses'))->count_all_results('service_requests');
        $data['complaints_high'] = ($on('complaints') && $this->db->table_exists('complaints'))
            ? $this->db->where('priority', 'high')->where_in('status', array('submitted', 'triaged', 'assigned', 'in_progress', 'waiting_resident', 'reopened'))->count_all_results('complaints') : 0;
        $data['complaints_closed'] = ($on('complaints') && $this->db->table_exists('complaints'))
            ? $this->db->where_in('status', array('resolved', 'closed'))->count_all_results('complaints') : 0;

        // ---- Dashboard v7 (redesign desktop). Semua blok di bawah hanya MEMBACA data. ----
        // Kartu/tautan hanya tampil bila menu tersebut diizinkan untuk peran ini DAN aktif secara langganan.
        $menu_ok = function ($path) use ($role) {return $this->menu_access->allowed($role, $path) && $this->menu_access->commercial($path) === 'ALLOW';};
        $data['menu_ok'] = array();
        foreach (array('admin/keluarga', 'admin/penduduk', 'admin/rumah', 'admin/kk/pengajuan', 'admin/requests', 'admin/pengaduan', 'admin/transactions', 'admin/dues', 'admin/tunggakan', 'admin/posts/agenda', 'admin/mutasi') as $p) $data['menu_ok'][$p] = $menu_ok($p);
        $data['house_count'] = ($data['menu_ok']['admin/rumah'] && $this->db->table_exists('rt_houses')) ? (int) $this->db->where('tenant_id', 1)->count_all_results('rt_houses') : NULL;
        $data['kk_pending'] = ($data['menu_ok']['admin/kk/pengajuan'] && $this->db->table_exists('kk_scan_requests')) ? (int) $this->db->where('status', 'pending')->count_all_results('kk_scan_requests') : NULL;
        $data['complaints_new'] = ($on('complaints') && $data['menu_ok']['admin/pengaduan'] && $this->db->table_exists('complaints')) ? (int) $this->db->where('status', 'submitted')->count_all_results('complaints') : 0;
        $data['mutations_pending'] = ($data['menu_ok']['admin/mutasi'] && $can('mutasi.review')) ? (int) ($data['ops']['mutations_pending'] ?? 0) : 0;

        // Rekap iuran: tagihan dibuat per AKUN WARGA (dues.citizen_id), bisa lebih dari satu kategori per periode.
        // Lunas = total dibayar >= total tagihan warga tersebut pada periode itu.
        $data['iuran'] = NULL;
        if ($data['show_dues']) {
            $periods = array_column($this->db->query('SELECT DISTINCT period FROM dues WHERE period <= ? ORDER BY period DESC LIMIT 12', array($month))->result_array(), 'period');
            if (!in_array($month, $periods, TRUE)) array_unshift($periods, $month);
            $pick = (string) $this->input->get('iuran', TRUE);
            $period = in_array($pick, $periods, TRUE) ? $pick : $month;
            $row = $this->db->query('SELECT COUNT(*) billed, COALESCE(SUM(paid >= due),0) lunas FROM (SELECT citizen_id, SUM(amount_due) due, SUM(amount_paid) paid FROM dues WHERE period=? GROUP BY citizen_id HAVING SUM(amount_due) > 0) x', array($period))->row_array();
            $data['iuran'] = array('period' => $period, 'periods' => $periods, 'billed' => (int) $row['billed'], 'lunas' => (int) $row['lunas']);
        }

        // Agenda terdekat: hanya yang dipublikasikan dan belum lewat.
        $data['agenda_next'] = $data['menu_ok']['admin/posts/agenda']
            ? $this->db->select('id,title,event_date,event_time,event_location')->where(array('type' => 'agenda', 'status' => 'published'))->where('event_date >=', date('Y-m-d'))
                ->order_by('event_date', 'ASC')->order_by('event_time', 'ASC')->limit(3)->get('posts')->result_array() : array();

        // Aktivitas terbaru lintas modul (nama tampilan saja; tanpa NIK/isi dokumen).
        $feed = array();
        foreach ((array) $data['letters_latest'] as $r) $feed[] = array('kind' => 'letter', 'at' => $r['created_at'], 'title' => 'Pengajuan ' . ($r['letter_title'] ?: 'surat') . ' oleh ' . ($r['display_name'] ?: 'warga'), 'url' => 'admin/request_detail/' . (int) $r['id']);
        if ($data['menu_ok']['admin/pengaduan']) foreach ((array) $data['complaints_latest'] as $r) $feed[] = array('kind' => 'complaint', 'at' => $r['created_at'], 'title' => 'Pengaduan ' . ($r['ticket_no'] ?: '#' . (int) $r['id']) . ' · ' . ($r['category'] ?: 'umum'), 'url' => 'admin/pengaduan/' . (int) $r['id']);
        if ($data['show_dues'] && $data['menu_ok']['admin/dues']) foreach ($this->db->select('p.created_at, p.status, c.display_name')->from('due_payments p')->join('citizens c', 'c.id=p.citizen_id', 'left')->order_by('p.id', 'DESC')->limit(3)->get()->result_array() as $r)
            $feed[] = array('kind' => 'payment', 'at' => $r['created_at'], 'title' => 'Pembayaran iuran oleh ' . ($r['display_name'] ?: 'warga') . ($r['status'] === 'pending' ? ' (menunggu verifikasi)' : ''), 'url' => 'admin/dues#pembayaran');
        if ($data['kk_pending'] !== NULL) foreach ($this->db->select('r.id, r.created_at, r.citizen_id, c.display_name')->from('kk_scan_requests r')->join('citizens c', 'c.id=r.citizen_id', 'left')->where('r.status !=', 'draft')->order_by('r.id', 'DESC')->limit(3)->get()->result_array() as $r)
            $feed[] = array('kind' => 'kk', 'at' => $r['created_at'], 'title' => 'Pengajuan data KK ' . ($r['citizen_id'] ? 'oleh ' . ($r['display_name'] ?: 'warga') : 'input pengurus'), 'url' => 'admin/kk/pengajuan/' . (int) $r['id']);
        usort($feed, function ($a, $b) {return strcmp((string) $b['at'], (string) $a['at']);});
        $data['activity'] = array_slice($feed, 0, 4);
        $this->render('dashboard', $data);
    }

    /** JSON for the admin "pengajuan baru" popup; polled by assets/admin-alerts.js. */
    public function alerts()
    {
        $this->guard();
        $this->load->helper('admin_alert');
        $this->output->set_header('Cache-Control: no-store, private')->set_content_type('application/json')
            ->set_output(json_encode(admin_alert_data() ?: array('total' => 0), JSON_UNESCAPED_UNICODE));
    }

    public function pages()
    {
        $this->guard(array('admin', 'operator', 'ketua'));
        $q=mb_substr(trim((string)$this->input->get('q',TRUE)),0,100);
        $status=(string)$this->input->get('status',TRUE);
        if(!in_array($status,array('','published','draft','menu'),TRUE))$status='';
        $sort=(string)$this->input->get('sort',TRUE);
        if(!in_array($sort,array('order','title','newest'),TRUE))$sort='order';
        if($q!=='')$this->db->group_start()->like('title',$q)->or_like('slug',$q)->group_end();
        if($status==='published')$this->db->where('is_published',1);elseif($status==='draft')$this->db->where('is_published',0);elseif($status==='menu')$this->db->where('show_in_menu',1);
        if($sort==='title')$this->db->order_by('title','ASC');elseif($sort==='newest')$this->db->order_by('id','DESC');else $this->db->order_by('sort_order','ASC')->order_by('id','ASC');
        $pages=$this->db->get('pages')->result_array();
        $total=$this->db->count_all('pages');
        $this->render('pages',array('pages'=>$pages,'total'=>$total,'q'=>$q,'status'=>$status,'sort'=>$sort));
    }

    public function search()
    {
        $this->guard();
        $q = trim((string) $this->input->get('q', TRUE));
        $data = array('q' => $q, 'posts' => array(), 'citizens' => array());
        if ($q !== '') {
            $allowed_types=array();
            foreach(array('announcements'=>'pengumuman','events'=>'agenda','gallery'=>'galeri') as $feature=>$type) if($this->feature_gate->for_staff($feature)) $allowed_types[]=$type;
            if($allowed_types) $data['posts'] = $this->db->where_in('type',$allowed_types)->group_start()->like('title', $q)->or_like('body', $q)->group_end()->order_by('created_at', 'DESC')->limit(30)->get('posts')->result_array();
            if (!isset($this->menu_access)) $this->load->library('Menu_access');
            // Data Warga belum berlangganan/nonaktif: hasil pencarian warga tidak ditampilkan.
            if (in_array($this->session->userdata('admin_role'), array('admin','operator','ketua'), TRUE) && $this->menu_access->commercial('admin/citizens') === 'ALLOW') {
                $data['citizens'] = $this->db->group_start()->like('display_name', $q)->or_like('username', $q)->group_end()->limit(30)->get('citizens')->result_array();
            }
        }
        $this->render('search', $data);
    }

    public function chat()
    {
        // Halaman Live Chat diganti widget chat melayang (admin/chat_widget) di semua halaman admin.
        // URL lama tetap berlaku: diarahkan ke dashboard dan widget membuka percakapan yang diminta.
        $this->guard(array('admin','operator','ketua'));
        $conversation=(int)$this->input->get('conversation');
        redirect('admin'.($conversation?'?chat='.$conversation:'#chat'));
    }

    /** Daftar percakapan untuk widget chat melayang admin (multi-chat). */
    public function chat_inbox()
    {
        $this->guard(array('admin','operator','ketua'));$this->load->model('Chat_model');$this->Chat_model->ensure_schema();$this->Chat_model->touch('admin',(int)$this->session->userdata('admin_id'));
        $list=array();$online=0;$unread=0;
        foreach($this->Chat_model->admin_conversations() as $c){
            $photos=glob(FCPATH.'uploads/profiles/citizen_'.(int)$c['citizen_id'].'_*')?:array();usort($photos,function($a,$b){return filemtime($b)<=>filemtime($a);});
            $avatar=$photos?'uploads/profiles/'.basename($photos[0]):'assets/mockup/chat-avatar-'.(($c['gender']??'female')==='male'?'male':'female').'.jpg';
            $online+=(int)$c['is_online'];$unread+=(int)$c['unread'];
            $list[]=array('id'=>(int)$c['id'],'name'=>(string)($c['display_name']?:$c['username']),'avatar'=>base_url($avatar),'online'=>(int)$c['is_online'],'unread'=>(int)$c['unread'],'last'=>(string)$c['last_message'],'time'=>(string)$c['last_message_time'],'poll'=>site_url('admin/chat/pesan/'.(int)$c['id']),'send'=>site_url('admin/chat/kirim/'.(int)$c['id']));
        }
        $this->output->set_content_type('application/json')->set_output(json_encode(array('conversations'=>$list,'online'=>$online,'unread'=>$unread,'csrf'=>$this->security->get_csrf_hash()),JSON_UNESCAPED_UNICODE));
    }

    public function chat_messages($conversation_id=0)
    {
        $this->guard(array('admin','operator','ketua'));$this->load->model('Chat_model');$this->Chat_model->ensure_schema();$conversation=$this->db->get_where('chat_conversations',array('id'=>(int)$conversation_id))->row_array();if(!$conversation)show_404();
        $this->Chat_model->touch('admin',(int)$this->session->userdata('admin_id'));$this->Chat_model->mark_read($conversation_id,'admin');
        $this->output->set_content_type('application/json')->set_output(json_encode(array('messages'=>$this->Chat_model->messages($conversation_id,(int)$this->input->get('after')),'conversations'=>$this->Chat_model->admin_conversations(),'read_up_to'=>$this->Chat_model->read_up_to($conversation_id),'csrf'=>$this->security->get_csrf_hash())));
    }

    public function chat_note($conversation_id=0)
    {
        $this->guard(array('admin','operator','ketua'));if($this->input->method(TRUE)!=='POST')show_error('Metode tidak diizinkan.',405);
        $conversation=$this->db->get_where('chat_conversations',array('id'=>(int)$conversation_id))->row_array();if(!$conversation)show_404();
        $note=mb_substr(trim((string)$this->input->post('note',TRUE)),0,1000);
        $this->db->where('id',(int)$conversation['citizen_id'])->update('citizens',array('notes'=>$note===''?NULL:$note));
        $this->log_action('update','citizen_note:'.(int)$conversation['citizen_id']);
        $this->output->set_content_type('application/json')->set_output(json_encode(array('ok'=>TRUE,'note'=>$note,'csrf'=>$this->security->get_csrf_hash())));
    }

    public function chat_send($conversation_id=0)
    {
        $this->guard(array('admin','operator','ketua'));if($this->input->method(TRUE)!=='POST')show_error('Metode tidak diizinkan.',405);$this->load->model('Chat_model');$this->Chat_model->ensure_schema();$conversation=$this->db->get_where('chat_conversations',array('id'=>(int)$conversation_id))->row_array();if(!$conversation)show_404();
        $message=trim((string)$this->input->post('message',FALSE));if($message===''||mb_strlen($message)>2000)show_error('Pesan wajib diisi dan maksimal 2000 karakter.',422);$admin_id=(int)$this->session->userdata('admin_id');$this->Chat_model->touch('admin',$admin_id);$message_id=$this->Chat_model->send($conversation_id,'admin',$admin_id,$message);
        $this->output->set_content_type('application/json')->set_output(json_encode(array('ok'=>TRUE,'id'=>$message_id,'csrf'=>$this->security->get_csrf_hash())));
    }

    public function notifications()
    {
        $this->guard();
        $data = array(
            'requests' => $this->db->select('service_requests.id,service_requests.subject,citizens.display_name')->join('citizens', 'citizens.id=service_requests.citizen_id')->where('service_requests.status', 'diajukan')->order_by('service_requests.created_at', 'DESC')->limit(20)->get('service_requests')->result_array(),
            'posts' => $this->db->where('status', 'pending')->order_by('created_at', 'DESC')->limit(20)->get('posts')->result_array(),
            'due_payments' => $this->db->select('due_payments.id,due_payments.amount,citizens.display_name')
                ->join('citizens','citizens.id=due_payments.citizen_id')->where('due_payments.status','pending')
                ->order_by('due_payments.created_at','DESC')->limit(20)->get('due_payments')->result_array(),
        );
        if (!$this->feature_gate->for_staff('letters') || !in_array($this->session->userdata('admin_role'),array('admin','operator','ketua'),TRUE)) $data['requests']=array();
        if (!$this->feature_gate->for_staff('dues') || !in_array($this->session->userdata('admin_role'),array('admin','bendahara','ketua'),TRUE)) $data['due_payments']=array();
        $data['posts']=array_values(array_filter($data['posts'],function($post){$code=array('pengumuman'=>'announcements','agenda'=>'events','galeri'=>'gallery')[$post['type']]??NULL;return $code && $this->feature_gate->for_staff($code);}));
        $this->render('notifications', $data);
    }

    public function settings()
    {
        $this->guard(array('admin', 'ketua'));
        $settings = $this->Content_model->settings();
        $error = '';
        if ($this->input->method(TRUE) === 'POST') {
            $values = array();
            foreach (array('site_name', 'brand_label', 'brand_subtitle', 'tagline', 'hero_kicker', 'hero_title', 'hero_accent', 'hero_text', 'about_title', 'about_text', 'value1_title', 'value1_text', 'value2_title', 'value2_text', 'value3_title', 'value3_text', 'slogan', 'citizen_hero_quote', 'address', 'email', 'phone', 'footer_text', 'map_url', 'emergency_contact', 'whatsapp', 'instagram_url', 'youtube_url', 'facebook_url') as $key) {
                $values[$key] = trim((string) $this->input->post($key, TRUE));
            }
            // Modul 06: tema warna (hanya #rrggbb + cek kontras) dan sakelar fitur opsional.
            $this->load->helper('theme');
            if ($this->input->post('theme_reset')) {
                $values['theme_accent'] = THEME_DEFAULT_ACCENT;
                $values['theme_navy'] = THEME_DEFAULT_NAVY;
            } else {
                $values['theme_accent'] = theme_hex($this->input->post('theme_accent', TRUE)) ?: THEME_DEFAULT_ACCENT;
                $values['theme_navy'] = theme_hex($this->input->post('theme_navy', TRUE)) ?: THEME_DEFAULT_NAVY;
                if ($theme_error = theme_validate($values['theme_accent'], $values['theme_navy'])) $error = $theme_error;
            }
            $values['complaint_anonymous'] = $this->input->post('complaint_anonymous') ? '1' : '0';
            $values['notify_email'] = $this->input->post('notify_email') ? '1' : '0';
            // Admin cukup mengisi nama pengguna/handle. URL publik dibentuk saat ditampilkan.
            $social_name = static function ($value) {
                $value = trim((string) $value);
                if (preg_match('#^https?://#i', $value)) {
                    $path = trim((string) parse_url($value, PHP_URL_PATH), '/');
                    $parts = $path === '' ? array() : explode('/', $path);
                    $value = $parts ? end($parts) : '';
                }
                return ltrim(trim($value, "/ \t\n\r\0\x0B"), '@');
            };
            $values['instagram_url'] = $social_name($values['instagram_url']);
            $values['youtube_url'] = $social_name($values['youtube_url']);
            if ($values['map_url'] && (!filter_var($values['map_url'], FILTER_VALIDATE_URL) || !in_array(strtolower((string) parse_url($values['map_url'], PHP_URL_SCHEME)), array('http','https'), TRUE))) $error = 'URL peta tidak valid.';
            if ($values['instagram_url'] && !preg_match('/^[A-Za-z0-9._]{1,30}$/', $values['instagram_url'])) $error = 'Nama Instagram hanya boleh berisi huruf, angka, titik, atau garis bawah.';
            if ($values['youtube_url'] && !preg_match('/^[\p{L}\p{N}._-]{1,100}$/u', $values['youtube_url'])) $error = 'Nama YouTube hanya boleh berisi huruf, angka, titik, garis bawah, atau tanda hubung.';
            if ($values['facebook_url'] && (!filter_var($values['facebook_url'], FILTER_VALIDATE_URL) || !in_array(strtolower((string) parse_url($values['facebook_url'], PHP_URL_SCHEME)), array('http','https'), TRUE))) $error = 'URL Facebook tidak valid.';
            if (!empty($_FILES['home_banner_jpg']['name'])) {
                $path = $this->upload_image('home_banner_jpg', $error);
                if ($path) $values['home_banner_jpg'] = $path;
            }
            if (!empty($_FILES['footer_banner_jpg']['name'])) {
                $path = $this->upload_image('footer_banner_jpg', $error);
                if ($path) $values['footer_banner_jpg'] = $path;
            }
            if (!empty($_FILES['logo']['name'])) {
                $path = $this->upload_image('logo', $error);
                if ($path) $values['logo'] = $path;
            }
            // Ikon logo kecil untuk header portal warga (opsional; kosong = pakai Logo RT).
            if (!empty($_FILES['logo_icon']['name'])) {
                $path = $this->upload_image('logo_icon', $error);
                if ($path) $values['logo_icon'] = $path;
            } elseif ($this->input->post('logo_icon_reset')) {
                $values['logo_icon'] = '';
            }
            if (!empty($_FILES['about_image']['name'])) {
                $path = $this->upload_image('about_image', $error);
                if ($path) $values['about_image'] = $path;
            }
            foreach (array('value1_icon','value2_icon','value3_icon','announcement_icon','agenda_icon','footer_wave') as $field) {
                if (!empty($_FILES[$field]['name'])) {
                    $path = $this->upload_image($field, $error);
                    if ($path) $values[$field] = $path;
                }
            }
            if (!$error) {
                $this->Content_model->save_settings($values);
                $this->log_action('update', 'settings');
                $this->session->set_flashdata('notice', 'Pengaturan disimpan.');
                redirect('admin/settings');
            }
            $settings = array_merge($settings, $values);
        }
        $this->render('settings', array('settings' => $settings, 'error' => $error));
    }

    public function page_images()
    {
        $this->guard(array('admin', 'ketua'));
        $images = array(
            'dashboard_page_image' => array('Dashboard Admin', 'Banner sambutan pada halaman utama admin.', 'assets/mockup/home-hero-v4.png'),
            'citizen_dashboard_page_image' => array('Dashboard Warga', 'Ilustrasi banner selamat datang warga. Gunakan gambar tanpa tulisan; nama warga ditampilkan otomatis. Bawaan: gapura & kartu kutipan yang isinya diambil dari Pengaturan → Identitas RT.', 'assets/img/dashboard/portal-hero-v2.jpg'),
            'citizen_footer_image' => array('Footer Portal Warga', 'Ilustrasi kampung & taman di footer dashboard warga. Gunakan gambar lebar tanpa tulisan (mis. 1600 × 200 px); identitas RT dan slogan ditampilkan otomatis di atasnya.', 'assets/img/dashboard/footer-banner-v2.jpg'),
            'home_banner_jpg' => array('Beranda', 'Halaman utama yang dilihat semua pengunjung.', 'assets/mockup/home-banner.jpg'),
            'profile_page_image' => array('Profil', 'Halaman profil dan informasi RT.', 'assets/mockup/information-hero-v2.png'),
            'announcement_page_image' => array('Pengumuman', 'Halaman daftar pengumuman untuk warga.', 'assets/mockup/announcement-hero-v2.png'),
            'agenda_page_image' => array('Agenda', 'Halaman jadwal kegiatan RT.', 'assets/mockup/agenda-hero-v2.png'),
            'information_page_image' => array('Informasi', 'Halaman informasi dan layanan warga.', 'assets/mockup/info-hero-art.png'),
            'gallery_page_image' => array('Galeri', 'Halaman galeri foto kegiatan RT.', 'assets/mockup/gallery-hero-v2.png'),
            'contact_page_image' => array('Kontak', 'Halaman kontak dan informasi lokasi.', 'assets/mockup/contact-hero-v3.png'),
            'citizen_login_page_image' => array('Login Warga', 'Latar halaman masuk akun warga.', 'assets/mockup/citizen-login-bg.jpg'),
            'structure_page_image' => array('Struktur Organisasi', 'Gambar utama halaman struktur organisasi.', 'assets/mockup/home-hero-v4.png'),
            'footer_banner_jpg' => array('Footer Website', 'Gambar penutup yang tampil di halaman publik.', 'assets/mockup/footer-banner.jpg'),
        );
        $error = '';
        if ($this->input->method(TRUE) === 'POST') {
            $key = (string) $this->input->post('image_key', TRUE);
            if (!isset($images[$key])) $error = 'Pilihan gambar halaman tidak valid.';
            elseif (empty($_FILES['page_image']['name'])) $error = 'Pilih gambar baru terlebih dahulu.';
            else {
                $path = $this->upload_image('page_image', $error);
                if ($path && !$error) {
                    $this->Content_model->save_settings(array($key => $path));
                    $this->log_action('update', 'page_image:' . $key);
                    $this->session->set_flashdata('notice', 'Gambar halaman '.$images[$key][0].' berhasil diterapkan.');
                    redirect('admin/page-images');
                }
            }
        }
        $this->load->helper('official_icons');
        $this->render('page_images', array('settings' => $this->Content_model->settings(), 'images' => $images, 'error' => $error));
    }

    public function page($id = 0)
    {
        $this->guard(array('admin', 'operator', 'ketua'));
        $id = (int) $id;
        $page = $id ? $this->Content_model->page($id) : NULL;
        if ($id && !$page) show_404();
        $error = '';
        if ($this->input->method(TRUE) === 'POST') {
            $title = trim((string) $this->input->post('title', TRUE));
            if ($title === '') $error = 'Judul wajib diisi.';
            $data = array(
                'title' => $title,
                'slug' => $this->Content_model->unique_slug($title, $id),
                'body' => trim((string) $this->input->post('body', TRUE)),
                'sort_order' => (int) $this->input->post('sort_order'),
                'show_in_menu' => $this->input->post('show_in_menu') ? 1 : 0,
                'is_published' => $this->input->post('is_published') && in_array($this->session->userdata('admin_role'), array('admin','ketua'), TRUE) ? 1 : 0,
            );
            if (!empty($_FILES['image']['name'])) {
                $path = $this->upload_image('image', $error);
                if ($path) $data['image'] = $path;
            }
            if (!$error) {
                if ($id) $this->db->where('id', $id)->update('pages', $data);
                else $this->db->insert('pages', $data);
                $this->log_action($id ? 'update' : 'create', 'page:' . ($id ?: $this->db->insert_id()));
                $this->session->set_flashdata('notice', 'Halaman disimpan.');
                redirect('admin/pages');
            }
            $page = array_merge($page ?: array(), $data);
        }
        $this->load->helper(array('official_icons', 'content_form'));
        $this->render('page_form', array('page' => $page, 'error' => $error));
    }

    public function delete_page($id = 0)
    {
        $this->guard(array('admin', 'operator', 'ketua'));
        if ($this->input->method(TRUE) !== 'POST') show_404();
        $this->db->where('id', (int) $id)->delete('pages');
        $this->log_action('delete', 'page:' . (int) $id);
        $this->session->set_flashdata('notice', 'Halaman dihapus.');
        redirect('admin/pages');
    }

    public function password()
    {
        $this->guard();
        if($this->platform_owner->valid_session())show_error('Password Admin Pusat dikelola melalui konfigurasi server, bukan akun pengurus.',403);
        $error = '';
        if ($this->input->method(TRUE) === 'POST') {
            $old = (string) $this->input->post('old_password');
            $new = (string) $this->input->post('new_password');
            $user = $this->db->get_where('admins', array('id' => $this->session->userdata('admin_id')))->row_array();
            if (!password_verify($old, $user['password_hash'])) $error = 'Password lama salah.';
            elseif (strlen($new) < 10) $error = 'Password baru minimal 10 karakter.';
            else {
                $this->db->where('id', $user['id'])->update('admins', array('password_hash' => password_hash($new, PASSWORD_DEFAULT)));
                $this->session->set_flashdata('notice', 'Password diganti.');
                redirect('admin/password');
            }
        }
        $this->render('password', array('error' => $error));
    }

    public function posts($type = 'pengumuman')
    {
        $this->guard(array('admin', 'operator', 'ketua'));
        if (!in_array($type, array('pengumuman', 'agenda', 'galeri'), TRUE)) show_404();
        $q = mb_substr(trim((string)$this->input->get('q', TRUE)), 0, 100);
        $status = (string)$this->input->get('status', TRUE);
        if (!in_array($status, array('','draft','pending','published'), TRUE)) $status = '';
        $sort = (string)$this->input->get('sort', TRUE);
        if (!in_array($sort, array('newest','oldest','title'), TRUE)) $sort = 'newest';
        $date_scope = (string)$this->input->get('date_scope', TRUE);
        if (!in_array($date_scope, array('','upcoming','past','month','year'), TRUE)) $date_scope = '';
        $location = mb_substr(trim((string)$this->input->get('location', TRUE)), 0, 160);
        $this->db->where('type', $type);
        if ($status !== '') $this->db->where('status', $status);
        if ($type === 'agenda' && $date_scope === 'upcoming') $this->db->where('event_date >=', date('Y-m-d'));
        if ($type === 'agenda' && $date_scope === 'past') $this->db->where('event_date <', date('Y-m-d'));
        if ($type === 'galeri' && $date_scope === 'month') $this->db->where('created_at >=', date('Y-m-01'))->where('created_at <', date('Y-m-d',strtotime('first day of next month')));
        if ($type === 'galeri' && $date_scope === 'year') $this->db->where('created_at >=', date('Y-01-01'))->where('created_at <', (date('Y')+1).'-01-01');
        if ($type === 'agenda' && $location !== '') $this->db->where('event_location', $location);
        if ($q !== '') $this->db->group_start()->like('title', $q)->or_like('body', $q)->group_end();
        if ($sort === 'oldest') $this->db->order_by('created_at', 'ASC');
        elseif ($sort === 'title') $this->db->order_by('title', 'ASC');
        else $this->db->order_by('created_at', 'DESC');
        $posts = $this->db->get('posts')->result_array();
        $total = $this->db->where('type', $type)->count_all_results('posts');
        $locations = $type === 'agenda' ? $this->db->select('event_location')->distinct()->where('type','agenda')->where("event_location !=",'')->order_by('event_location')->get('posts')->result_array() : array();
        $status_counts = array('published' => 0, 'pending' => 0, 'draft' => 0);
        foreach ($this->db->select('status,COUNT(*) n')->where('type', $type)->group_by('status')->get('posts')->result_array() as $r) $status_counts[$r['status']] = (int) $r['n'];
        $this->render('posts', array('status_counts'=>$status_counts,'type'=>$type,'posts'=>$posts,'total'=>$total,'q'=>$q,'status'=>$status,'sort'=>$sort,'date_scope'=>$date_scope,'location'=>$location,'locations'=>$locations));
    }

    public function post($type = 'pengumuman', $id = 0)
    {
        $this->guard(array('admin', 'operator', 'ketua'));
        if (!in_array($type, array('pengumuman', 'agenda', 'galeri'), TRUE)) show_404();
        $id = (int) $id;
        $post = $id ? $this->db->get_where('posts', array('id' => $id, 'type' => $type))->row_array() : NULL;
        if ($id && !$post) show_404();
        $error = '';
        if ($this->input->method(TRUE) === 'POST') {
            $title = trim((string) $this->input->post('title', TRUE));
            if (!$title) $error = 'Judul wajib diisi.';
            $status = $this->input->post('status', TRUE);
            if (!in_array($status, array('draft', 'pending', 'published'), TRUE)) $status = 'draft';
            if ($status === 'published' && !in_array($this->session->userdata('admin_role'), array('admin', 'ketua'), TRUE)) $status = 'pending';
            $became_published = $id && $status === 'published' && ($post['status'] ?? '') !== 'published';
            $data = array('type' => $type, 'title' => $title,
                'body' => trim((string) $this->input->post('body', TRUE)),
                'event_date' => $this->input->post('event_date', TRUE) ?: NULL,
                'event_time' => $type === 'agenda' && $this->input->post('event_time', TRUE) ? $this->input->post('event_time', TRUE) : NULL,
                'event_location' => $type === 'agenda' ? trim((string) $this->input->post('event_location', TRUE)) : '',
                'status' => $status);
            if ($became_published) $data['created_at'] = date('Y-m-d H:i:s');
            if ($type === 'agenda' && !$this->valid_date($data['event_date'])) $error = 'Tanggal acara wajib diisi.';
            if ($type === 'galeri' && !$id && empty($_FILES['image']['name'])) $error = 'Pilih foto kegiatan yang akan diunggah.';
            if ($type === 'galeri' && !empty($_FILES['image']['name']) && !$this->input->post('photo_consent')) $error = 'Pastikan izin publikasi foto sebelum mengunggah.';
            if (!$error && !empty($_FILES['image']['name'])) {
                $path = $this->upload_image('image', $error);
                if ($path) $data['image'] = $path;
            }
            if (!$error) {
                if ($id) $this->db->where('id', $id)->update('posts', $data);
                else $this->db->insert('posts', $data);
                if ($became_published && $this->db->table_exists('citizen_notice_reads')) {
                    $notice_kind = $type === 'pengumuman' ? 'an' : ($type === 'agenda' ? 'ag' : '');
                    if ($notice_kind !== '') $this->db->where(array('kind' => $notice_kind, 'ref_id' => $id, 'status' => 'pub'))->delete('citizen_notice_reads');
                }
                $this->log_action($id ? 'update' : 'create', $type . ':' . ($id ?: $this->db->insert_id()));
                $this->session->set_flashdata('notice', 'Konten disimpan.');
                redirect('admin/posts/' . $type);
            }
            $post = array_merge($post ?: array(), $data);
        }
        $this->load->helper(array('official_icons', 'content_form'));
        $this->render('post_form', array('type' => $type, 'post' => $post, 'error' => $error));
    }

    public function post_delete($type = 'pengumuman', $id = 0)
    {
        $this->guard(array('admin', 'ketua'));
        if ($this->input->method(TRUE) !== 'POST' || !in_array($type, array('pengumuman', 'agenda', 'galeri'), TRUE)) show_404();

        $id = (int) $id;
        $post = $id ? $this->db->get_where('posts', array('id' => $id, 'type' => $type))->row_array() : NULL;
        if (!$post) show_404();

        $this->db->where(array('id' => $id, 'type' => $type))->delete('posts');
        $label = $type === 'galeri' ? 'Foto galeri' : ($type === 'agenda' ? 'Agenda kegiatan' : 'Pengumuman');
        if ($this->db->affected_rows() !== 1) show_error($label . ' gagal dihapus. Silakan coba kembali.', 500);
        // Agenda bisa tertaut ke Program Kerja dan notifikasi warga; jangan tinggalkan rujukan ke agenda yang sudah dihapus.
        if ($type === 'agenda') {
            if ($this->db->table_exists('program_agendas')) $this->db->where('post_id', $id)->delete('program_agendas');
            if ($this->db->table_exists('citizen_notice_reads')) $this->db->where(array('kind' => 'ag', 'ref_id' => $id))->delete('citizen_notice_reads');
        }

        if (!empty($post['image'])) $this->remove_post_image($post['image']);
        $this->log_action('delete', $type . ':' . $id);
        $this->session->set_flashdata('notice', $label . ' berhasil dihapus.');
        redirect('admin/posts/' . $type);
    }

    public function officials()
    {
        $this->guard(array('admin', 'operator', 'ketua'));
        $this->ensure_official_hierarchy_schema();
        $this->load->helper('official_icons');
        $items = $this->db->order_by('sort_order')->order_by('id')->get('officials')->result_array();
        $this->render('officials', array('items' => $items));
    }

    public function official($id = 0)
    {
        $this->guard(array('admin', 'operator', 'ketua'));
        $this->ensure_official_hierarchy_schema();
        $this->load->helper('official_icons');
        $id = (int) $id;
        $item = $id ? $this->db->get_where('officials', array('id' => $id))->row_array() : NULL;
        if ($id && !$item) show_404();
        $parents = $this->db->where('id !=', $id)->order_by('sort_order')->order_by('id')->get('officials')->result_array();
        $error = '';
        if ($this->input->method(TRUE) === 'POST') {
            $parent_id = (int) $this->input->post('parent_id');
            if ($parent_id && !$this->db->get_where('officials', array('id' => $parent_id))->row_array()) $parent_id = 0;
            $icon = (string) $this->input->post('icon', TRUE);
            if (!isset(official_icon_catalog()[$icon])) $icon = '👤';
            $data = array('name' => trim((string) $this->input->post('name', TRUE)),
                'position' => trim((string) $this->input->post('position', TRUE)),
                'description' => trim((string) $this->input->post('description', TRUE)),
                'icon' => $icon,
                'parent_id' => $parent_id ?: NULL,
                'contact' => preg_replace('/[^0-9+]/', '', (string) $this->input->post('contact', TRUE)),
                'term_start' => $this->input->post('term_start', TRUE) ?: NULL,
                'term_end' => $this->input->post('term_end', TRUE) ?: NULL,
                'sort_order' => (int) $this->input->post('sort_order'),
                'is_active' => $this->input->post('is_active') && in_array($this->session->userdata('admin_role'), array('admin','ketua'), TRUE) ? 1 : 0);
            if (!$data['name'] || !$data['position']) $error = 'Nama dan jabatan wajib diisi.';
            if ($parent_id === $id && $id) $error = 'Posisi tidak dapat menjadi atasan bagi dirinya sendiri.';
            if (!$error && !empty($_FILES['photo']['name'])) {
                $path = $this->upload_official_photo('photo', $error);
                if ($path) $data['photo'] = $path;
            }
            if (!$error) {
                if ($id) $this->db->where('id', $id)->update('officials', $data);
                else $this->db->insert('officials', $data);
                if ($id && isset($data['photo']) && !empty($item['photo']) && $item['photo'] !== $data['photo']) $this->remove_uploaded_file($item['photo']);
                $this->log_action($id ? 'update' : 'create', 'official:' . ($id ?: $this->db->insert_id()));
                $this->session->set_flashdata('notice', 'Pengurus disimpan.');
                redirect('admin/officials');
            }
            $item = array_merge($item ?: array(), $data);
        }
        $this->render('official_form', array('item' => $item, 'parents' => $parents, 'error' => $error));
    }

    public function official_photo_delete($id)
    {
        $this->guard(array('admin', 'ketua'));
        if ($this->input->method(TRUE) !== 'POST') show_404();
        $item = $this->db->get_where('officials', array('id' => (int) $id))->row_array();
        if (!$item) show_404();
        $this->db->where('id', (int) $id)->update('officials', array('photo' => NULL));
        if (!empty($item['photo'])) $this->remove_uploaded_file($item['photo']);
        $this->log_action('delete_photo', 'official:' . (int) $id);
        $this->session->set_flashdata('notice', 'Foto personel dihapus.');
        redirect('admin/official/' . (int) $id);
    }

    public function official_delete($id)
    {
        $this->guard(array('admin', 'ketua'));
        if ($this->input->method(TRUE) !== 'POST') show_404();
        $id = (int) $id;
        $item = $this->db->get_where('officials', array('id' => $id))->row_array();
        if (!$item) show_404();
        $this->db->where('parent_id', $id)->update('officials', array('parent_id' => $item['parent_id'] ?: NULL));
        $this->db->where('id', $id)->delete('officials');
        if (!empty($item['photo'])) $this->remove_uploaded_file($item['photo']);
        $this->log_action('delete', 'official:' . $id);
        $this->session->set_flashdata('notice', 'Posisi organisasi dihapus.');
        redirect('admin/officials');
    }

    public function transactions()
    {
        $this->guard(array('admin', 'bendahara', 'ketua'));
        $period = trim((string)$this->input->get('period', TRUE));
        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $period)) $period = date('Y-m');
        $type = (string)$this->input->get('type', TRUE);
        if (!in_array($type, array('','masuk','keluar'), TRUE)) $type = '';
        $q = mb_substr(trim((string)$this->input->get('q', TRUE)), 0, 100);

        $this->db->where('period', $period)->where('status', 'verified');
        if ($type !== '') $this->db->where('type', $type);
        if ($q !== '') $this->db->group_start()->like('description', $q)->or_like('category', $q)->or_like('counterparty', $q)->group_end();
        // Urutan kolom tabel (Tanggal / Jenis / Jumlah), default tanggal terbaru.
        $sorts = array('tanggal' => 'tx_date', 'jenis' => 'type', 'jumlah' => 'amount');
        $sort = (string) $this->input->get('urut', TRUE);
        if (!isset($sorts[$sort])) $sort = 'tanggal';
        $dir = strtolower((string) $this->input->get('arah', TRUE)) === 'asc' ? 'ASC' : 'DESC';
        $this->db->order_by($sorts[$sort], $dir);
        if ($sort !== 'tanggal') $this->db->order_by('tx_date', 'DESC');
        $items = $this->db->order_by('id', 'DESC')->get('transactions')->result_array();

        // Data grafik: total harian pemasukan & pengeluaran pada periode (transaksi terverifikasi).
        $days_in_month = (int) date('t', strtotime($period . '-01'));
        $daily = array('masuk' => array_fill(1, $days_in_month, 0.0), 'keluar' => array_fill(1, $days_in_month, 0.0));
        foreach ($this->db->select('DAY(tx_date) d, type, SUM(amount) total', FALSE)->where('period', $period)->where('status', 'verified')
            ->group_by(array('DAY(tx_date)', 'type'))->get('transactions')->result_array() as $r) {
            if (isset($daily[$r['type']][(int) $r['d']])) $daily[$r['type']][(int) $r['d']] = (float) $r['total'];
        }

        $summary =$this->db->select("COALESCE(SUM(CASE WHEN type='masuk' THEN amount ELSE 0 END),0) AS income, COALESCE(SUM(CASE WHEN type='keluar' THEN amount ELSE 0 END),0) AS expense, SUM(type='masuk') AS income_count, SUM(type='keluar') AS expense_count", FALSE)
            ->where('period', $period)->where('status', 'verified')->get('transactions')->row_array();
        $balance = $this->db->select("COALESCE(SUM(CASE WHEN type='masuk' THEN amount ELSE -amount END),0) AS total", FALSE)
            ->where('status', 'verified')->where('period <=', $period)->get('transactions')->row_array();
        $this->render('transactions', array('items'=>$items,'period'=>$period,'type'=>$type,'q'=>$q,'summary'=>$summary,'balance'=>(float)$balance['total'],
            'daily'=>$daily,'sort'=>$sort,'dir'=>$dir));
    }

    public function transaction($type = 'masuk')
    {
        $this->guard(array('admin', 'bendahara'));
        if (!in_array($type, array('masuk', 'keluar'), TRUE)) show_404();
        $reverse_id = (int) $this->input->get('reverse');
        $reversal = $reverse_id ? $this->db->get_where('transactions', array('id' => $reverse_id, 'status' => 'verified'))->row_array() : NULL;
        if ($reverse_id && (!$reversal || $type === $reversal['type'])) show_404();
        $error = '';
        if ($this->input->method(TRUE) === 'POST') {
            $date = (string) $this->input->post('tx_date', TRUE);
            $amount = (int) $this->input->post('amount');
            $category = trim((string) $this->input->post('category', TRUE));
            $description = trim((string) $this->input->post('description', TRUE));
            if (!$this->valid_date($date) || !$category || !$description || $amount <= 0 || $amount >= 1000000000000) $error = 'Lengkapi tanggal, kategori, uraian, dan jumlah yang valid.';
            if ($reversal && $amount != (int) $reversal['amount']) $error = 'Jumlah pembalik harus sama dengan transaksi asal.';
            $proof = '';
            if (!$error && !empty($_FILES['proof']['name'])) $proof = $this->upload_proof($error) ?: '';
            if (!$error) {
                $data = array('tx_date' => $date, 'type' => $type,
                    'category' => $category, 'description' => $description, 'amount' => $amount,
                    'period' => substr($date, 0, 7),
                    'counterparty' => trim((string) $this->input->post('counterparty', TRUE)),
                    'proof' => $proof, 'status' => 'verified',
                    // Sesi Superadmin (pengelola platform) tidak punya admin_id; 0 = dicatat Superadmin,
                    // sama dengan verifikasi pembayaran iuran & belanja RT. Sebelumnya NULL ditolak DB (created_by NOT NULL).
                    'created_by' => (int) $this->session->userdata('admin_id'),
                    'verified_by' => (int) $this->session->userdata('admin_id'),
                    'verified_at' => date('Y-m-d H:i:s'),
                    'reverses_id' => $reversal ? $reversal['id'] : NULL);
                $this->db->insert('transactions', $data);
                $this->log_action('create', 'transaction:' . $this->db->insert_id());
                $this->session->set_flashdata('notice', 'Transaksi disimpan.');
                redirect('admin/transactions');
            }
        }
        $this->render('transaction_form', array('type' => $type, 'error' => $error, 'reversal' => $reversal));
    }

    public function edit_transaction($id = 0)
    {
        $this->guard(array('admin', 'bendahara'));
        $id = (int) $id;
        $item = $this->db->get_where('transactions', array('id' => $id))->row_array();
        if (!$item || !in_array($item['status'], array('draft','returned'), TRUE)) show_404();
        $error = '';
        if ($this->input->method(TRUE) === 'POST') {
            $date = (string) $this->input->post('tx_date', TRUE);
            $amount = (int) $this->input->post('amount');
            $category = trim((string) $this->input->post('category', TRUE));
            $description = trim((string) $this->input->post('description', TRUE));
            if (!$this->valid_date($date) || !$category || !$description || $amount <= 0 || $amount >= 1000000000000) $error = 'Lengkapi tanggal, kategori, uraian, dan jumlah yang valid.';
            if ($item['reverses_id']) {
                $original = $this->db->get_where('transactions', array('id' => $item['reverses_id']))->row_array();
                if (!$original || $amount != (int) $original['amount']) $error = 'Jumlah pembalik harus sama dengan transaksi asal.';
            }
            $data = array('tx_date' => $date, 'category' => $category, 'description' => $description,
                'amount' => $amount, 'period' => substr($date, 0, 7),
                'counterparty' => trim((string) $this->input->post('counterparty', TRUE)),
                'status' => $this->input->post('submit_status') === 'pending' ? 'pending' : 'draft',
                'review_note' => NULL);
            if (!empty($_FILES['proof']['name'])) {
                $proof = $this->upload_proof($error);
                if ($proof) $data['proof'] = $proof;
            }
            if (!$error) {
                $this->db->where('id', $id)->update('transactions', $data);
                $this->log_action('update', 'transaction:' . $id);
                $this->session->set_flashdata('notice', 'Transaksi diperbarui.');
                redirect('admin/transactions');
            }
            $item = array_merge($item, $data);
        }
        $this->render('transaction_form', array('type' => $item['type'], 'item' => $item, 'error' => $error));
    }

    public function quick_links()
    {
        $this->guard(array('admin', 'ketua'));
        $this->load->helper('official_icons');
        $items = $this->db->order_by('sort_order')->order_by('id')->get('quick_links')->result_array();
        $this->render('quick_links', array('items' => $items));
    }

    public function quick_link_move($id, $direction = '')
    {
        $this->guard(array('admin', 'ketua'));
        if ($this->input->method(TRUE) !== 'POST') show_404();
        $ids = array_map('intval', array_column($this->db->select('id')->order_by('sort_order')->order_by('id')->get('quick_links')->result_array(), 'id'));
        $from = array_search((int) $id, $ids, TRUE);
        if ($from === FALSE) show_404();
        $to = $direction === 'up' ? $from - 1 : ($direction === 'down' ? $from + 1 : -1);
        if (isset($ids[$to])) {
            list($ids[$from], $ids[$to]) = array($ids[$to], $ids[$from]);
            // Urutan lama bisa kembar, jadi seluruh urutan ditulis ulang.
            foreach ($ids as $index => $link_id) $this->db->where('id', $link_id)->update('quick_links', array('sort_order' => $index + 1));
            $this->log_action('reorder', 'quick_link:' . (int) $id);
        }
        redirect('admin/quick_links');
    }

    public function quick_link_delete($id)
    {
        $this->guard(array('admin', 'ketua'));
        if ($this->input->method(TRUE) !== 'POST') show_404();
        $id = (int) $id;
        $item = $this->db->get_where('quick_links', array('id' => $id))->row_array();
        if (!$item) show_404();
        $this->db->where('id', $id)->delete('quick_links');
        if (!empty($item['icon_image'])) $this->remove_uploaded_file($item['icon_image']);
        $this->log_action('delete', 'quick_link:' . $id);
        $this->session->set_flashdata('notice', 'Menu cepat dihapus.');
        redirect('admin/quick_links');
    }

    public function quick_link($id = 0)
    {
        $this->guard(array('admin', 'ketua'));
        $id = (int) $id;
        $item = $id ? $this->db->get_where('quick_links', array('id' => $id))->row_array() : NULL;
        if ($id && !$item) show_404();
        $error = '';
        if ($this->input->method(TRUE) === 'POST') {
            $data = array('title' => trim((string) $this->input->post('title', TRUE)),
                'subtitle' => trim((string) $this->input->post('subtitle', TRUE)),
                'url_path' => trim((string) $this->input->post('url_path', TRUE), '/'),
                'sort_order' => (int) $this->input->post('sort_order'),
                'is_active' => $this->input->post('is_active') ? 1 : 0);
            if (!$data['title'] || !preg_match('/^[a-z0-9_\/-]+$/', $data['url_path'])) $error = 'Judul dan tujuan menu wajib diisi. Gunakan path lokal seperti agenda atau warga/iuran.';
            if (!$error && !empty($_FILES['icon_image']['name'])) {
                $path = $this->upload_image('icon_image', $error);
                if ($path) $data['icon_image'] = $path;
            }
            if (!$error) {
                if ($id) $this->db->where('id', $id)->update('quick_links', $data);
                else $this->db->insert('quick_links', $data);
                $this->log_action($id ? 'update' : 'create', 'quick_link:' . ($id ?: $this->db->insert_id()));
                $this->session->set_flashdata('notice', 'Menu cepat disimpan.');
                redirect('admin/quick_links');
            }
            $item = array_merge($item ?: array(), $data);
        }
        $this->load->helper(array('official_icons', 'content_form'));
        $this->render('quick_link_form', array('item' => $item, 'error' => $error));
    }

    public function citizens()
    {
        $this->guard(array('admin', 'operator', 'ketua'));
        $q=mb_substr(trim((string)$this->input->get('q',TRUE)),0,100);
        $status=(string)$this->input->get('status',TRUE);
        if(!in_array($status,array('','active','inactive'),TRUE))$status='';
        $sort=(string)$this->input->get('sort',TRUE);
        if(!in_array($sort,array('newest','oldest','name'),TRUE))$sort='newest';
        $has_kk=$this->db->table_exists('kk_families');
        $kk=(string)$this->input->get('kk',TRUE);
        if(!$has_kk||!in_array($kk,array('','belum','terhubung'),TRUE))$kk='';
        $owned='(SELECT owner_citizen_id FROM kk_families WHERE owner_citizen_id IS NOT NULL)';
        $filter=function()use($q,$status,$kk,$owned){
            if($q!=='')$this->db->group_start()->like('display_name',$q)->or_like('username',$q)->group_end();
            if($status==='active')$this->db->where('is_active',1);elseif($status==='inactive')$this->db->where('is_active',0);
            if($kk==='belum')$this->db->where('id NOT IN '.$owned,NULL,FALSE);elseif($kk==='terhubung')$this->db->where('id IN '.$owned,NULL,FALSE);
        };
        $filter();$matched=$this->db->count_all_results('citizens');
        $per_page=20;$pages=max(1,(int)ceil($matched/$per_page));$page=min($pages,max(1,(int)$this->input->get('hal')));
        $filter();
        if($sort==='oldest')$this->db->order_by('created_at','ASC');elseif($sort==='name')$this->db->order_by('display_name','ASC');else $this->db->order_by('created_at','DESC');
        $items=$this->db->select('id,username,display_name,is_active,created_at,gender')->order_by('id','DESC')->limit($per_page,($page-1)*$per_page)->get('citizens')->result_array();
        $counts=array('total'=>$this->db->count_all('citizens'),'active'=>$this->db->where('is_active',1)->count_all_results('citizens'),'inactive'=>$this->db->where('is_active',0)->count_all_results('citizens'));
        $family_of=array();$pending_of=array();$member_of=array();
        $ids=array_column($items,'id');
        if($items&&$has_kk){
            foreach($this->db->select('id,owner_citizen_id')->where_in('owner_citizen_id',$ids)->get('kk_families')->result_array() as $f)$family_of[(int)$f['owner_citizen_id']]=(int)$f['id'];
            if($this->db->table_exists('kk_scan_requests'))foreach($this->db->select('citizen_id')->where_in('citizen_id',$ids)->where_in('status',array('pending','revision_requested'))->get('kk_scan_requests')->result_array() as $r)$pending_of[(int)$r['citizen_id']]=1;
            if($this->db->table_exists('citizen_resident_links'))foreach($this->db->select('citizen_id,member_id')->where_in('citizen_id',$ids)->get('citizen_resident_links')->result_array() as $r)$member_of[(int)$r['citizen_id']]=(int)$r['member_id'];
        }
        $counts['no_kk']=$has_kk?(int)$this->db->query('SELECT COUNT(*) n FROM citizens WHERE is_active=1 AND id NOT IN '.$owned)->row()->n:0;
        $duplicates=$this->citizen_duplicate_groups($has_kk);
        $show_dupes=$this->input->get('tampil',TRUE)==='duplikat';
        $this->config->load('rt_access',TRUE);$perm=(array)$this->config->item('rt_permissions','rt_access');
        $can_family=in_array($this->session->userdata('admin_role'),$perm['penduduk.read']??array(),TRUE);
        $this->render('citizens',array('family_of'=>$family_of,'pending_of'=>$pending_of,'member_of'=>$member_of,'has_kk'=>$has_kk,'kk'=>$kk,'duplicates'=>$duplicates,'show_dupes'=>$show_dupes,'can_family'=>$can_family,'items'=>$items,'counts'=>$counts,'q'=>$q,'status'=>$status,'sort'=>$sort,'matched'=>$matched,'page'=>$page,'pages'=>$pages,'per_page'=>$per_page));
    }

    /**
     * Deteksi kemungkinan akun ganda: nama sama, nomor telepon sama, atau email sama.
     * Hanya MENDETEKSI. Kesamaan nama tidak cukup untuk menyatakan duplikat, jadi tidak ada penggabungan/penghapusan
     * otomatis; pengurus memverifikasi username, telepon, dan identitas pemilik lalu menonaktifkan akun yang salah.
     */
    private function citizen_duplicate_groups($has_kk)
    {
        $rows=$this->db->select('id,username,display_name,full_name,phone,email,is_active,created_at')->get('citizens')->result_array();
        $owners=array();
        if($has_kk)foreach($this->db->select('owner_citizen_id')->where('owner_citizen_id IS NOT NULL',NULL,FALSE)->get('kk_families')->result_array() as $f)$owners[(int)$f['owner_citizen_id']]=1;
        $parent=array();
        $find=function($x)use(&$parent,&$find){return $parent[$x]===$x?$x:($parent[$x]=$find($parent[$x]));};
        $keys=array();
        foreach($rows as $r){
            $id=(int)$r['id'];$parent[$id]=$id;
            foreach(array_unique(array_filter(array($r['display_name'],$r['full_name']))) as $n){$n=trim(preg_replace('/\s+/u',' ',mb_strtolower((string)$n,'UTF-8')));if(mb_strlen($n,'UTF-8')>=3)$keys['n:'.$n][$id]='nama';}
            $p=preg_replace('/\D+/','',(string)$r['phone']);if(strpos($p,'62')===0)$p='0'.substr($p,2);if(strlen($p)>=8)$keys['p:'.$p][$id]='telepon';
            $e=mb_strtolower(trim((string)$r['email']),'UTF-8');if($e!=='')$keys['e:'.$e][$id]='email';
        }
        $reasons=array();
        foreach($keys as $k=>$ids){
            if(count($ids)<2)continue;
            $list=array_keys($ids);
            foreach($list as $id){$reasons[$id][$ids[$id]]=1;$a=$find($list[0]);$b=$find($id);if($a!==$b)$parent[$b]=$a;}
        }
        $groups=array();
        foreach($rows as $r){$id=(int)$r['id'];if(!isset($reasons[$id]))continue;
            $mask=function($v,$keep){$v=(string)$v;return $v===''?'':(mb_strlen($v)>$keep+2?mb_substr($v,0,$keep).str_repeat('•',max(3,mb_strlen($v)-$keep-2)).mb_substr($v,-2):$v);};
            $groups[$find($id)][]=array('id'=>$id,'username'=>$r['username'],'name'=>$r['display_name'],'phone'=>$mask($r['phone'],4),'email'=>$mask($r['email'],3),
                'active'=>(int)$r['is_active']===1,'has_kk'=>isset($owners[$id]),'created_at'=>$r['created_at'],'reasons'=>array_keys($reasons[$id]));}
        return array_values(array_filter($groups,function($g){return count($g)>1;}));
    }

    public function citizen($id = 0)
    {
        $this->guard(array('admin', 'operator', 'ketua'));
        $this->ensure_citizen_profile_schema();
        $id = (int) $id;
        $item = $id ? $this->db->get_where('citizens', array('id' => $id))->row_array() : NULL;
        if ($id && !$item) show_404();
        $error = '';
        $this->load->library('Custom_fields');
        $custom_values = $id ? $this->custom_fields->values($id) : array();
        $custom_post = array();
        if ($this->input->method(TRUE) === 'POST') {
            list($custom_post, $custom_error) = $this->custom_fields->collect(FALSE);
            $username = trim((string) $this->input->post('username', TRUE));
            $name = trim((string) $this->input->post('display_name', TRUE));
            $full_name = trim((string) $this->input->post('full_name', TRUE));
            $phone = trim((string) $this->input->post('phone', TRUE));
            $email = trim((string) $this->input->post('email', TRUE));
            $address = trim((string) $this->input->post('address', TRUE));
            $notes = trim((string) $this->input->post('notes', TRUE));
            $gender = (string) $this->input->post('gender', TRUE);
            $password = (string) $this->input->post('password');
            if (!preg_match('/^[a-zA-Z0-9._-]{3,80}$/', $username) || !$name || !$full_name) $error = 'Nama lengkap, nama tampilan, dan username wajib diisi.';
            if (!$id && strlen($password) < 8) $error = 'Password awal minimal 8 karakter.';
            if ($password !== '' && strlen($password) < 8) $error = 'Password minimal 8 karakter.';
            if ($phone !== '' && !preg_match('/^[0-9+() .-]{7,25}$/', $phone)) $error = 'Format nomor WhatsApp tidak valid.';
            if ($email !== '' && !filter_var($email,FILTER_VALIDATE_EMAIL)) $error = 'Format email tidak valid.';
            if (!in_array($gender,array('male','female'),TRUE)) $error = 'Jenis kelamin wajib dipilih.';
            $duplicate = $this->db->get_where('citizens', array('username' => $username))->row_array();
            if ($duplicate && (int) $duplicate['id'] !== $id) $error = 'Username sudah dipakai.';
            if (!$error && $custom_error) $error = $custom_error;
            $photo = $_FILES['profile_photo'] ?? array();
            $photo_ext = '';
            if (!$error && $photo && (int)($photo['error']??UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                if ((int)$photo['error'] !== UPLOAD_ERR_OK || (int)$photo['size'] > 2*1024*1024) $error='Foto profil harus berukuran maksimal 2 MB.';
                else {$mime=(new finfo(FILEINFO_MIME_TYPE))->file($photo['tmp_name']);$allowed=image_mime_map();if(!@getimagesize($photo['tmp_name'])||!isset($allowed[$mime]))$error='Foto profil harus berformat '.image_formats_label().'.';else $photo_ext=$allowed[$mime];}
            }
            if (!$error) {
                $data = array('username' => $username, 'display_name' => $name, 'full_name'=>$full_name,
                    'gender'=>$gender,
                    'phone'=>$phone?:NULL,'email'=>$email?:NULL,'address'=>$address?:NULL,'notes'=>$notes?:NULL,
                    'is_active' => $this->input->post('is_active') ? 1 : 0);
                if ($password !== '') $data['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
                if ($id) $this->db->where('id', $id)->update('citizens', $data);
                else $this->db->insert('citizens', $data);
                $saved_id=$id ?: (int)$this->db->insert_id();
                $this->custom_fields->save($saved_id, $custom_post);
                if ($photo_ext) {$dir=FCPATH.'uploads/profiles/';if(!is_dir($dir))mkdir($dir,0755,TRUE);$filename='citizen_'.$saved_id.'_'.bin2hex(random_bytes(8)).'.'.$photo_ext;if(move_uploaded_file($photo['tmp_name'],$dir.$filename))foreach(glob($dir.'citizen_'.$saved_id.'_*')?:array()as$old)if(basename($old)!==$filename&&is_file($old))@unlink($old);}
                $this->log_action($id ? 'update' : 'create', 'citizen:' . $saved_id);
                $this->session->set_flashdata('notice', 'Akun warga disimpan.');
                redirect('admin/citizens');
            }
            $item = array_merge($item ?: array(), array('username' => $username, 'display_name' => $name,
                'full_name'=>$full_name,'gender'=>$gender,'phone'=>$phone,'email'=>$email,'address'=>$address,'notes'=>$notes,
                'is_active' => $this->input->post('is_active') ? 1 : 0));
        }
        $profile_photos=$id?(glob(FCPATH.'uploads/profiles/citizen_'.$id.'_*')?:array()):array();
        usort($profile_photos,function($a,$b){return filemtime($b)<=>filemtime($a);});
        if ($custom_post) $custom_values = $custom_post + $custom_values;
        $this->render('citizen_form', array('item' => $item, 'error' => $error,'editing'=>(bool)$id,'custom_fields_html'=>$this->custom_fields->render($custom_values, 'acf', FALSE),'profile_photo'=>$profile_photos?'uploads/profiles/'.basename($profile_photos[0]):''));
    }

    public function requests()
    {
        $this->guard(array('admin', 'operator', 'ketua'));
        $this->load->model('Letter_model');
        // Tab default = "Perlu Ditinjau": pengajuan yang sudah di-ACC/ditolak/selesai pindah ke tabnya sendiri.
        $statuses=array('diajukan','diproses','disetujui','selesai','ditolak');
        $filter=(string)$this->input->get('status',TRUE);
        if ($filter!=='semua' && !in_array($filter,$statuses,TRUE)) $filter='diajukan';
        $q=mb_substr(trim((string)$this->input->get('q',TRUE)),0,80);
        $counts=array('semua'=>$this->db->where('status !=','draft')->count_all_results('service_requests'));
        foreach($statuses as $status) $counts[$status]=$this->db->where('status',$status)->count_all_results('service_requests');
        $this->db->select('service_requests.*,citizens.display_name,service_request_details.purpose,service_request_details.extra_data')
            ->join('citizens', 'citizens.id=service_requests.citizen_id')
            ->join('service_request_details','service_request_details.request_id=service_requests.id','left')->where('service_requests.status !=','draft');
        if ($filter!=='semua') $this->db->where('service_requests.status',$filter);
        if ($q) $this->db->group_start()->like('citizens.display_name',$q)->or_like('service_requests.subject',$q)->or_like('service_request_details.extra_data',$q)->group_end();
        $items=$this->db->order_by('service_requests.created_at','DESC')->limit(100)->get('service_requests')->result_array();
        $this->render('requests', array('items'=>$items,'catalog'=>$this->Letter_model->catalog(),'counts'=>$counts,'filter'=>$filter,'q'=>$q));
    }

    public function letter_types()
    {
        $this->guard(array('admin','ketua'));
        $this->load->model('Letter_model');
        $q=mb_substr(trim((string)$this->input->get('q',TRUE)),0,100);
        $category=(string)$this->input->get('category',TRUE);
        if(!in_array($category,array('','Kependudukan','Keterangan','Kegiatan','Lainnya'),TRUE))$category='';
        $active=(string)$this->input->get('active',TRUE);
        if(!in_array($active,array('','1','0'),TRUE))$active='';
        $sort=(string)$this->input->get('sort',TRUE);
        if(!in_array($sort,array('order','title','newest'),TRUE))$sort='order';
        if($q!=='')$this->db->group_start()->like('title',$q)->or_like('description',$q)->group_end();
        if($category!=='')$this->db->where('category',$category);
        if($active!=='')$this->db->where('is_active',(int)$active);
        if($sort==='title')$this->db->order_by('title','ASC');elseif($sort==='newest')$this->db->order_by('id','DESC');else $this->db->order_by('sort_order')->order_by('title');
        $items=$this->db->get('letter_types')->result_array();
        $total=$this->db->count_all('letter_types');
        $this->render('letter_types',array('items'=>$items,'total'=>$total,'q'=>$q,'category'=>$category,'active'=>$active,'sort'=>$sort));
    }

    public function letter_type($key = '')
    {
        $this->guard(array('admin','ketua'));
        $this->load->model('Letter_model');
        $key = (string)$key;
        $item = $key ? $this->db->get_where('letter_types',array('type_key'=>$key))->row_array() : NULL;
        if ($key && !$item) show_404();
        $error = '';
        if ($this->input->method(TRUE) === 'POST') {
            $type_key = $key ?: strtolower(trim((string)$this->input->post('type_key',TRUE)));
            $title = trim((string)$this->input->post('title',TRUE));
            $category = (string)$this->input->post('category',TRUE);
            $description = trim((string)$this->input->post('description',TRUE));
            $body_template = trim((string)$this->input->post('body_template',TRUE));
            if (!preg_match('/^[a-z0-9_]{3,40}$/',$type_key) || mb_strlen($title)<3 || mb_strlen($title)>160 || !in_array($category,array('Kependudukan','Keterangan','Kegiatan','Lainnya'),TRUE) || mb_strlen($description)>255) $error='Lengkapi data jenis surat dengan benar.';
            elseif (!$key && $this->db->get_where('letter_types',array('type_key'=>$type_key))->row_array()) $error='Kode jenis surat sudah dipakai.';
            $path = $item['icon_path'] ?? '';
            if (!$error && !empty($_FILES['icon_image']['name'])) {
                $uploaded = $this->upload_image('icon_image',$error);
                if ($uploaded) $path=$uploaded;
            }
            if (!$error && !$key && !$path) $error='Ikon wajib diunggah untuk jenis surat baru.';
            if (!$error) {
                $values = array('title'=>$title,'category'=>$category,'description'=>$description,'body_template'=>mb_substr($body_template,0,5000),'icon_path'=>$path,'sort_order'=>(int)$this->input->post('sort_order'),'is_active'=>$this->input->post('is_active')?1:0);
                if ($key) $this->db->where('type_key',$key)->update('letter_types',$values);
                else $this->db->insert('letter_types',array('type_key'=>$type_key)+$values);
                $this->log_action($key?'update':'create','letter_type:'.$type_key);
                $this->session->set_flashdata('notice','Jenis surat disimpan.');
                redirect('admin/letter_types');
            }
            $item = array_merge($item ?: array(),array('type_key'=>$type_key,'title'=>$title,'category'=>$category,'description'=>$description,'body_template'=>$body_template));
        }
        $this->render('letter_type_form',array('item'=>$item,'error'=>$error));
    }

    public function letter_settings()
    {
        $this->guard(array('admin','ketua'));
        $this->load->helper('official_icons');
        $settings = $this->Content_model->settings();
        $error = '';
        if ($this->input->method(TRUE) === 'POST') {
            $values = array();
            foreach (array('letter_region','letter_district','letter_village','letter_rt_name','letter_environment','letter_postal','letter_address','letter_signer_name','letter_signer_title') as $field) {
                $values[$field] = mb_substr(trim((string)$this->input->post($field,TRUE)),0,180);
            }
            if (!$values['letter_rt_name'] || !$values['letter_signer_name']) $error='Nama RT dan nama penanda tangan wajib diisi.';
            foreach (array('letter_logo','letter_signature','letter_stamp') as $field) {
                if (!empty($_FILES[$field]['name'])) {
                    $path = $this->upload_letter_asset($field,$error);
                    if ($path) $values[$field]=$path;
                } elseif ($this->input->post('remove_'.$field)) $values[$field]='';
            }
            if (!$error) {
                $this->Content_model->save_settings($values);
                $this->log_action('update','letter_settings');
                $this->session->set_flashdata('notice','Pengaturan surat disimpan.');
                redirect('admin/letter_settings');
            }
            $settings=array_merge($settings,$values);
        }
        $this->render('letter_settings',array('settings'=>$settings,'error'=>$error));
    }

    private function upload_letter_asset($field, &$error)
    {
        $file = $_FILES[$field];
        $mime = is_uploaded_file($file['tmp_name']) ? (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']) : '';
        $types = array('image/png'=>'png','image/jpeg'=>'jpg','image/webp'=>'webp');
        if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] > 2*1024*1024 || !isset($types[$mime])) { $error='Gambar harus PNG, JPG, atau WebP maksimal 2 MB.'; return NULL; }
        $dir=FCPATH.'uploads/letters/';
        if (!is_dir($dir)) mkdir($dir,0750,TRUE);
        $name=bin2hex(random_bytes(16)).'.'.$types[$mime];
        if (!move_uploaded_file($file['tmp_name'],$dir.$name)) { $error='Gambar gagal disimpan.'; return NULL; }
        return $name;
    }

    public function request_detail($id = 0)
    {
        $this->guard(array('admin', 'operator', 'ketua'));
        $id = (int) $id;
        $item = $this->db->select('service_requests.*,citizens.display_name,service_request_details.applicant_name,service_request_details.address,service_request_details.purpose,service_request_details.extra_data,service_request_details.attachment,service_request_details.letter_number,service_request_details.approved_at')
            ->join('citizens', 'citizens.id=service_requests.citizen_id')
            ->join('service_request_details', 'service_request_details.request_id=service_requests.id', 'left')
            ->where('service_requests.id', $id)->get('service_requests')->row_array();
        if (!$item || $item['status']==='draft') show_404();
        if ($this->input->method(TRUE) === 'POST') {
            $status = (string) $this->input->post('status', TRUE);
            if (!in_array($status, array('diajukan','diproses','disetujui','ditolak','selesai'), TRUE)) show_404();
            if (in_array($status, array('disetujui','ditolak','selesai'), TRUE) && !in_array($this->session->userdata('admin_role'), array('admin','ketua'), TRUE)) show_error('Persetujuan hanya untuk Ketua RT.', 403);
            $number = trim((string)$this->input->post('letter_number', TRUE));
            if ($status === 'disetujui' && $item['type'] !== 'aduan' && !$number) show_error('Nomor surat wajib diisi sebelum persetujuan.', 400);
            if (mb_strlen($number) > 100) show_error('Nomor surat terlalu panjang.', 400);
            $status_changed = $status !== $item['status'];
            $this->db->trans_start();
            $this->db->where('id', $id)->update('service_requests', array('status' => $status,
                'admin_note' => trim((string) $this->input->post('admin_note', TRUE)),
                'updated_at' => date('Y-m-d H:i:s')));
            $detail_update = array('letter_number'=>$number ?: NULL,'approved_at'=>$status === 'disetujui' ? ($item['approved_at'] ?: date('Y-m-d H:i:s')) : NULL,'approved_by'=>$status === 'disetujui' ? (int)$this->session->userdata('admin_id') : NULL);
            if ($item['applicant_name'] !== NULL) $this->db->where('request_id',$id)->update('service_request_details', $detail_update);
            else $this->db->insert('service_request_details', $detail_update + array('request_id'=>$id,'applicant_name'=>$item['display_name'],'address'=>'-','purpose'=>$item['subject']));
            // A status may return to one the resident has seen before. Clear only
            // that exact acknowledgement so every real admin status change is shown.
            if ($status_changed && $this->db->table_exists('citizen_notice_reads')) {
                $this->db->where(array('citizen_id' => (int) $item['citizen_id'], 'kind' => 'sr', 'ref_id' => $id, 'status' => $status))->delete('citizen_notice_reads');
            }
            $this->db->trans_complete();
            $this->log_action('review', 'request:' . $id);
            $this->session->set_flashdata('notice', $status_changed
                ? 'Status pengajuan diperbarui. Warga akan menerima popup pemberitahuan saat membuka portal.'
                : 'Catatan pengajuan diperbarui.');
            redirect('admin/requests');
        }
        $this->load->model('Letter_model');
        $documents=$this->db->get_where('service_request_documents',array('request_id'=>$id))->result_array();
        $this->render('request_detail', array('item'=>$item,'catalog'=>$this->Letter_model->catalog(),'documents'=>$documents,'type_fields'=>$this->Letter_model->fields($item['type'])));
    }

    public function dues()
    {
        $this->guard(array('admin', 'bendahara', 'ketua'));
        $this->load->helper(array('official_icons', 'content_form'));
        // Periode terbaru dulu; dalam satu periode tagihan yang terakhir dibuat tampil paling atas.
        $items = $this->db->select('dues.*,citizens.display_name')->join('citizens', 'citizens.id=dues.citizen_id')
            ->order_by('dues.period', 'DESC')->order_by('dues.id', 'DESC')->get('dues')->result_array();
        // Pembayaran yang perlu ditinjau selalu tampil paling atas.
        $payments = $this->db->select('due_payments.*,dues.category,dues.period,citizens.display_name')
            ->join('dues','dues.id=due_payments.due_id')->join('citizens','citizens.id=due_payments.citizen_id')
            ->order_by("due_payments.status='pending'",'DESC',FALSE)->order_by('due_payments.created_at','DESC')->get('due_payments')->result_array();
        $periods = array_values(array_unique(array_column($items, 'period')));
        $period = (string) $this->input->get('period', TRUE);
        if ($period !== 'all' && !in_array($period, $periods, TRUE)) {
            $period = in_array(date('Y-m'), $periods, TRUE) ? date('Y-m') : ($periods ? $periods[0] : 'all');
        }
        $pending_due_ids = array();
        $summary = array('total'=>0,'paid'=>0,'bills'=>0,'lunas'=>0,'waiting'=>0,'unpaid'=>0,'pending_payments'=>0,'pending_amount'=>0);
        $unpaid_citizens = array();
        $paid_recorded = 0;$paid_verified = 0;
        foreach ($payments as $payment) {
            if ($payment['status'] === 'verified') $paid_verified += (float) $payment['amount'];
            if ($payment['status'] === 'pending' && ($period === 'all' || $payment['period'] === $period)) {
                $pending_due_ids[(int) $payment['due_id']] = TRUE;
                $summary['pending_payments']++;
                $summary['pending_amount'] += (float) $payment['amount'];
            }
        }
        foreach ($items as $due) {
            $paid_recorded += (float) $due['amount_paid'];
            if ($period !== 'all' && $due['period'] !== $period) continue;
            $summary['total'] += (float) $due['amount_due'];
            $summary['paid'] += (float) $due['amount_paid'];
            $summary['bills']++;
            if ((float) $due['amount_paid'] >= (float) $due['amount_due']) $summary['lunas']++;
            elseif (isset($pending_due_ids[(int) $due['id']])) $summary['waiting']++;
            else $summary['unpaid']++;
            if ((float) $due['amount_paid'] < (float) $due['amount_due']) $unpaid_citizens[(int) $due['citizen_id']] = TRUE;
        }
        $summary['unpaid_citizens'] = count($unpaid_citizens);
        $paid_due_ids = array_flip(array_map('intval', array_column($payments, 'due_id')));

        // Daftar Tagihan: filter kategori (tab), periode & nama warga di server + halaman. Ringkasan di atas tetap dari seluruh data.
        $tperiod = (string) $this->input->get('tperiod', TRUE);
        if (!in_array($tperiod, $periods, TRUE)) $tperiod = '';
        $q = mb_substr(trim((string) $this->input->get('q', TRUE)), 0, 60);
        $base = array_values(array_filter($items, function ($d) use ($tperiod, $q) {
            return ($tperiod === '' || $d['period'] === $tperiod) && ($q === '' || mb_stripos($d['display_name'], $q) !== FALSE);
        }));
        $cats = array();
        foreach ($base as $d) $cats[$d['category']] = ($cats[$d['category']] ?? 0) + 1;
        ksort($cats, SORT_NATURAL | SORT_FLAG_CASE);
        $kat = (string) $this->input->get('kat', TRUE);
        if ($kat !== '' && !isset($cats[$kat])) $kat = '';
        $due_rows = $kat === '' ? $base : array_values(array_filter($base, function ($d) use ($kat) {return $d['category'] === $kat;}));
        // Filter status pembayaran: lunas / sebagian / belum.
        $status_opts = array('lunas' => 'Lunas', 'sebagian' => 'Sebagian', 'belum' => 'Belum dibayar');
        $tstatus = (string) $this->input->get('status', TRUE);
        if (!isset($status_opts[$tstatus])) $tstatus = '';
        if ($tstatus !== '') $due_rows = array_values(array_filter($due_rows, function ($d) use ($tstatus) {
            $due = (float) $d['amount_due']; $paid = (float) $d['amount_paid'];
            return $tstatus === 'lunas' ? $paid >= $due : ($tstatus === 'sebagian' ? $paid > 0 && $paid < $due : $paid <= 0 && $due > 0);
        }));
        // Urutan daftar.
        $sort_opts = array('terbaru' => 'Tanggal Terbaru', 'terlama' => 'Tanggal Terlama', 'nama' => 'Nama Warga (A–Z)', 'nominal' => 'Nominal Tertinggi', 'sisa' => 'Sisa Terbesar');
        $sort = (string) $this->input->get('urut', TRUE);
        if (!isset($sort_opts[$sort])) $sort = 'terbaru';
        if ($sort !== 'terbaru') usort($due_rows, function ($a, $b) use ($sort) {
            if ($sort === 'terlama') return strcmp($a['period'], $b['period']) ?: ((int) $a['id'] <=> (int) $b['id']);
            if ($sort === 'nama') return strcasecmp($a['display_name'], $b['display_name']) ?: strcmp($b['period'], $a['period']);
            if ($sort === 'nominal') return ((float) $b['amount_due'] <=> (float) $a['amount_due']) ?: strcmp($b['period'], $a['period']);
            return (((float) $b['amount_due'] - (float) $b['amount_paid']) <=> ((float) $a['amount_due'] - (float) $a['amount_paid'])) ?: strcmp($b['period'], $a['period']);
        });
        $per_opts = array(10, 15, 25, 50);
        $due_per = (int) $this->input->get('per');
        if (!in_array($due_per, $per_opts, TRUE)) $due_per = 10;
        $due_total = count($due_rows);
        $due_pages = max(1, (int) ceil($due_total / $due_per));
        $due_page = min($due_pages, max(1, (int) $this->input->get('hal')));
        $due_rows = array_slice($due_rows, ($due_page - 1) * $due_per, $due_per);

        // Pembayaran Masuk: tab kategori + halaman; yang menunggu verifikasi tetap paling atas.
        $pcats = array();
        foreach ($payments as $p) $pcats[$p['category']] = ($pcats[$p['category']] ?? 0) + 1;
        ksort($pcats, SORT_NATURAL | SORT_FLAG_CASE);
        $pkat = (string) $this->input->get('pkat', TRUE);
        if ($pkat !== '' && !isset($pcats[$pkat])) $pkat = '';
        $pay_rows = $pkat === '' ? $payments : array_values(array_filter($payments, function ($p) use ($pkat) {return $p['category'] === $pkat;}));
        $pay_per = 10;
        $pay_total = count($pay_rows);
        $pay_pages = max(1, (int) ceil($pay_total / $pay_per));
        $pay_page = min($pay_pages, max(1, (int) $this->input->get('phal')));
        $pay_rows = array_slice($pay_rows, ($pay_page - 1) * $pay_per, $pay_per);

        $this->render('dues', array('items' => $items, 'payments'=>$payments, 'periods'=>$periods, 'period'=>$period,
            'summary'=>$summary, 'legacy_paid'=>max(0, $paid_recorded - $paid_verified), 'paid_due_ids'=>$paid_due_ids,
            'due_rows'=>$due_rows, 'due_total'=>$due_total, 'due_page'=>$due_page, 'due_pages'=>$due_pages, 'due_per'=>$due_per,
            'cats'=>$cats, 'kat'=>$kat, 'tperiod'=>$tperiod, 'q'=>$q, 'cats_total'=>count($base), 'baru'=>(int)$this->input->get('baru'),
            'tstatus'=>$tstatus, 'status_opts'=>$status_opts, 'sort'=>$sort, 'sort_opts'=>$sort_opts, 'per_opts'=>$per_opts,
            'pay_rows'=>$pay_rows, 'pay_total'=>$pay_total, 'pay_page'=>$pay_page, 'pay_pages'=>$pay_pages, 'pay_per'=>$pay_per,
            'pcats'=>$pcats, 'pkat'=>$pkat));
    }

    public function dues_export()
    {
        $this->guard(array('admin', 'bendahara', 'ketua'));
        $period = (string) $this->input->get('period', TRUE);
        $format = strtolower((string) $this->input->get('format', TRUE));
        if (!in_array($format, array('xls', 'xlsx'), TRUE)) $format = 'xlsx';
        $this->db->select('dues.*,citizens.display_name')->join('citizens', 'citizens.id=dues.citizen_id');
        if (preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $period)) $this->db->where('dues.period', $period);
        else $period = 'semua';
        $items = $this->db->order_by('dues.period', 'DESC')->order_by('citizens.display_name')->get('dues')->result_array();
        $this->log_action('export', 'dues:' . $period . ':' . $format);
        $headers = array('Periode', 'Warga', 'Jenis', 'Kategori', 'Tagihan', 'Dibayar', 'Sisa', 'Status', 'Catatan');
        $rows = array();
        $types = array('rutin'=>'Rutin','non_rutin'=>'Nonrutin','sosial'=>'Sosial');
        foreach ($items as $item) {
            $due = (float) $item['amount_due'];$paid = (float) $item['amount_paid'];
            $rows[] = array($item['period'], (string) $item['display_name'], $types[$item['due_type']] ?? 'Rutin', (string) $item['category'],
                (int) $due, (int) $paid, (int) max(0, $due - $paid), $paid >= $due ? 'Lunas' : ($paid > 0 ? 'Sebagian' : 'Belum dibayar'), (string) $item['note']);
        }
        $this->load->library('spreadsheet_export');
        $this->spreadsheet_export->send($format, 'iuran-warga-' . $period, 'Iuran Warga', $headers, $rows);
    }

    public function due_payment_record($due_id = 0)
    {
        $this->guard(array('admin', 'bendahara', 'ketua'));
        $this->load->helper(array('official_icons', 'content_form'));
        $error = '';
        $form = array('due_id' => (int) $due_id, 'amount' => '', 'method' => 'tunai', 'note' => '');
        if ($this->input->method(TRUE) === 'POST') {
            $form = array('due_id' => (int) $this->input->post('due_id'), 'amount' => (int) $this->input->post('amount'),
                'method' => (string) $this->input->post('method', TRUE), 'note' => mb_substr(trim((string) $this->input->post('note', TRUE)), 0, 255));
            $has_file = !empty($_FILES['proof']['name']);
            if (!in_array($form['method'], array('tunai', 'transfer'), TRUE)) $error = 'Pilih metode pembayaran.';
            elseif ($form['method'] === 'transfer' && !$has_file) $error = 'Pembayaran transfer wajib menyertakan bukti.';
            $proof = NULL;
            if (!$error && $has_file) $proof = $this->upload_proof($error);
            if (!$error) {
                $this->db->trans_begin();
                $due = $this->db->query('SELECT * FROM dues WHERE id=? FOR UPDATE', array($form['due_id']))->row_array();
                $remaining = $due ? (float) $due['amount_due'] - (float) $due['amount_paid'] : 0;
                if (!$due) $error = 'Pilih tagihan yang akan dibayar.';
                elseif ($form['amount'] < 1 || $form['amount'] > $remaining) $error = 'Jumlah pembayaran harus antara Rp 1 dan sisa tagihan.';
                elseif ($this->db->get_where('due_payments', array('due_id' => (int) $due['id'], 'status' => 'pending'))->row_array()) $error = 'Tagihan ini masih punya pembayaran yang menunggu verifikasi. Tinjau dulu pembayaran tersebut.';
                if ($error) {
                    $this->db->trans_rollback();
                    if ($proof) @unlink(FCPATH . 'uploads/proofs/' . basename($proof));
                } else {
                    $admin_id = (int) $this->session->userdata('admin_id');
                    $payment = array('due_id' => (int) $due['id'], 'citizen_id' => (int) $due['citizen_id'], 'amount' => $form['amount'], 'method' => $form['method'], 'proof' => $proof);
                    $transaction_id = $this->apply_due_payment($payment, $due);
                    $this->db->insert('due_payments', $payment + array('status' => 'verified', 'review_note' => $form['note'], 'transaction_id' => $transaction_id, 'reviewed_at' => date('Y-m-d H:i:s'), 'reviewed_by' => $admin_id));
                    $payment_id = (int) $this->db->insert_id();
                    if ($this->db->trans_status() === FALSE) {
                        $this->db->trans_rollback();
                        if ($proof) @unlink(FCPATH . 'uploads/proofs/' . basename($proof));
                        $error = 'Pembayaran gagal disimpan.';
                    } else {
                        $this->db->trans_commit();
                        $this->log_action('record', 'due_payment:' . $payment_id);
                        $this->session->set_flashdata('notice', 'Pembayaran dicatat dan masuk laporan kas.');
                        redirect('admin/dues');
                    }
                }
            }
        }
        $dues = $this->db->select('dues.id,dues.period,dues.category,dues.amount_due,dues.amount_paid,citizens.display_name')
            ->join('citizens', 'citizens.id=dues.citizen_id')->where('dues.amount_paid < dues.amount_due', NULL, FALSE)
            ->order_by('citizens.display_name')->order_by('dues.period', 'DESC')->get('dues')->result_array();
        $this->render('due_payment_form', array('dues' => $dues, 'form' => $form, 'error' => $error));
    }

    public function due_delete($id = 0)
    {
        $this->guard(array('admin', 'bendahara', 'ketua'));
        if ($this->input->method(TRUE) !== 'POST') show_404();
        $due = $this->db->get_where('dues', array('id' => (int) $id))->row_array();
        if (!$due) show_404();
        if ((float) $due['amount_paid'] > 0 || $this->db->where('due_id', (int) $id)->count_all_results('due_payments') > 0) {
            $this->session->set_flashdata('notice', 'Tagihan yang sudah punya pembayaran tidak dapat dihapus.');
            redirect('admin/dues');
        }
        $this->db->where('id', (int) $id)->delete('dues');
        $this->log_action('delete', 'due:' . (int) $id);
        $this->session->set_flashdata('notice', 'Tagihan dihapus.');
        redirect('admin/dues');
    }

    // Menambah pembayaran ke tagihan dan laporan kas; dipanggil di dalam transaksi database.
    private function apply_due_payment($payment, $due)
    {
        $admin_id = (int) $this->session->userdata('admin_id');
        $this->db->set('amount_paid', 'amount_paid+' . $this->db->escape((float) $payment['amount']), FALSE)->where('id', (int) $due['id'])->update('dues');
        $this->db->insert('transactions', array('tx_date' => date('Y-m-d'), 'type' => 'masuk', 'category' => $due['category'],
            'description' => 'Iuran ' . str_replace('_', ' ', $due['due_type']) . ' ' . $due['category'] . ' ' . $due['period'],
            'amount' => $payment['amount'], 'period' => date('Y-m'), 'counterparty' => '', 'proof' => $payment['proof'] ?: '',
            'status' => 'verified', 'created_by' => $admin_id, 'verified_by' => $admin_id, 'verified_at' => date('Y-m-d H:i:s')));
        return (int) $this->db->insert_id();
    }

    public function billing_campaign()
    {
        $this->guard(array('admin','bendahara','ketua'));
        $error = '';
        if ($this->input->method(TRUE) === 'POST') {
            $title = trim((string)$this->input->post('title', TRUE));
            $type = (string)$this->input->post('due_type', TRUE);
            $category = trim((string)$this->input->post('category', TRUE));
            // REVISI: kategori "Lainnya (tulis sendiri)" — nama kategori diketik admin (maks. 100 karakter, sesuai kolom dues.category).
            if ($category === '__custom') $category = mb_substr(trim(preg_replace('/\s+/u', ' ', (string)$this->input->post('category_custom', TRUE))), 0, 100);
            $period = (string)$this->input->post('period', TRUE);
            $amount = (int)$this->input->post('amount');
            $target = (string)$this->input->post('target', TRUE);
            $citizenSelection = trim((string)$this->input->post('citizen_id', TRUE));
            // Dropdown menjadi sumber kebenaran agar pilihan semua warga tetap
            // bekerja meskipun JavaScript pada browser tidak berjalan.
            if ($citizenSelection === 'all') {
                $target = 'semua';
                $citizenId = 0;
            } elseif (ctype_digit($citizenSelection) && (int)$citizenSelection > 0) {
                $target = 'warga';
                $citizenId = (int)$citizenSelection;
            } else {
                $citizenId = 0;
            }
            if (!$title || !$category || !in_array($type,array('rutin','non_rutin','sosial'),TRUE) ||
                !preg_match('/^\d{4}-(0[1-9]|1[0-2])$/',$period) || $amount < 1 || $amount >= 1000000000000 ||
                !in_array($target,array('semua','warga'),TRUE) || ($target === 'warga' && $citizenId < 1)) {
                $error = 'Lengkapi jenis, kategori, periode, nominal, dan sasaran tagihan.';
            }
            $citizens = $target === 'semua'
                ? $this->db->select('id')->where('is_active',1)->get('citizens')->result_array()
                : $this->db->select('id')->where('id',$citizenId)->where('is_active',1)->get('citizens')->result_array();
            if (!$citizens) $error = 'Tidak ada warga aktif yang dipilih.';
            if (!$error) {
                $this->db->trans_begin();
                $this->db->insert('billing_campaigns',array('title'=>$title,'due_type'=>$type,'category'=>$category,
                    'period'=>$period,'amount'=>$amount,'note'=>trim((string)$this->input->post('note',TRUE)),
                    'created_by'=>(int)$this->session->userdata('admin_id')));
                $campaignId = (int)$this->db->insert_id();
                $created = 0;
                $skipped = array(); // REVISI: catat warga yang dilewati karena sudah punya tagihan kategori & periode yang sama
                foreach ($citizens as $citizen) {
                    $exists = $this->db->get_where('dues',array('citizen_id'=>(int)$citizen['id'],'period'=>$period,'category'=>$category))->row_array();
                    if ($exists) { $skipped[] = $exists; continue; }
                    $this->db->insert('dues',array('citizen_id'=>(int)$citizen['id'],'period'=>$period,'category'=>$category,
                        'due_type'=>$type,'campaign_id'=>$campaignId,'amount_due'=>$amount,'amount_paid'=>0,
                        'note'=>trim((string)$this->input->post('note',TRUE))));
                    $created++;
                }
                if ($created === 0) {
                    $this->db->trans_rollback();
                    // REVISI: pesan jelas — sebut warga, kategori, periode, dan nama tagihan yang sudah ada.
                    $bulan = array(1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember');
                    $periodLabel = $bulan[(int)substr($period,5,2)].' '.substr($period,0,4);
                    if ($target === 'warga' && $skipped) {
                        $who = $this->db->select('display_name')->get_where('citizens',array('id'=>$citizenId))->row_array();
                        $old = !empty($skipped[0]['campaign_id']) ? $this->db->select('title')->get_where('billing_campaigns',array('id'=>(int)$skipped[0]['campaign_id']))->row_array() : NULL;
                        $error = ($who['display_name'] ?? 'Warga ini').' sudah punya tagihan kategori "'.$category.'" untuk periode '.$periodLabel
                            .($old ? ' (tagihan "'.$old['title'].'")' : '').'. Satu warga hanya boleh punya satu tagihan per kategori per periode. '
                            .'Pilih kategori lain, pilih "Lainnya (tulis sendiri)" untuk kategori baru, atau ubah tagihan yang sudah ada lewat tombol Edit di Daftar Tagihan.';
                    } else {
                        $error = 'Semua warga yang dipilih sudah punya tagihan kategori "'.$category.'" untuk periode '.$periodLabel.'. Pilih kategori lain atau "Lainnya (tulis sendiri)".';
                    }
                } elseif ($this->db->trans_status() === FALSE) {
                    $this->db->trans_rollback();
                    $error = 'Tagihan gagal dibuat.';
                } else {
                    $this->db->trans_commit();
                    $this->log_action('create','billing_campaign:'.$campaignId);
                    $this->session->set_flashdata('notice',$created.' tagihan warga berhasil dibuat.'.($skipped ? ' '.count($skipped).' warga dilewati karena sudah punya tagihan kategori "'.$category.'" di periode ini.' : ''));
                    // Langsung tampilkan tab Daftar Tagihan, tersaring ke periode & kategori tagihan baru, dengan baris baru ditandai.
                    redirect('admin/dues?'.http_build_query(array('tperiod'=>$period,'kat'=>$category,'baru'=>$campaignId)).'#tagihan');
                }
            }
        }
        $citizens = $this->db->select('id,display_name')->where('is_active',1)->order_by('display_name')->get('citizens')->result_array();
        // REVISI: kategori yang sudah dipakai per warga per periode (12 bulan terakhir s.d. 12 bulan ke depan) untuk menandai "(sudah ada)" di form.
        $used = array();
        $from = date('Y-m', strtotime('-12 months')); $to = date('Y-m', strtotime('+12 months'));
        foreach ($this->db->select('citizen_id,period,category')->where('period >=',$from)->where('period <=',$to)->get('dues')->result_array() as $d) {
            $used[(int)$d['citizen_id']][$d['period']][] = $d['category'];
        }
        $this->render('billing_campaign',array('citizens'=>$citizens,'error'=>$error,'used'=>$used));
    }

    public function review_due_payment($id = 0)
    {
        $this->guard(array('admin','bendahara','ketua'));
        if ($this->input->method(TRUE) !== 'POST') show_404();
        $decision = (string)$this->input->post('decision',TRUE);
        if (!in_array($decision,array('verified','returned'),TRUE)) show_404();
        $this->db->trans_begin();
        $payment = $this->db->query('SELECT * FROM due_payments WHERE id=? FOR UPDATE',array((int)$id))->row_array();
        if (!$payment || $payment['status'] !== 'pending') { $this->db->trans_rollback(); show_404(); }
        $due = $this->db->query('SELECT * FROM dues WHERE id=? FOR UPDATE',array((int)$payment['due_id']))->row_array();
        if (!$due) { $this->db->trans_rollback(); show_404(); }
        if ($decision === 'verified' && (float)$payment['amount'] > (float)$due['amount_due']-(float)$due['amount_paid']) {
            $this->db->trans_rollback();
            $this->session->set_flashdata('notice','Pembayaran tidak dapat diverifikasi karena melebihi sisa tagihan. Kembalikan ke warga dengan catatan.');
            redirect('admin/dues');
        }
        $update = array('status'=>$decision,'review_note'=>trim((string)$this->input->post('review_note',TRUE)),
            'reviewed_at'=>date('Y-m-d H:i:s'),'reviewed_by'=>(int)$this->session->userdata('admin_id'));
        if ($decision === 'verified') $update['transaction_id'] = $this->apply_due_payment($payment,$due);
        $this->db->where('id',(int)$id)->update('due_payments',$update);
        if ($this->db->trans_status() === FALSE) { $this->db->trans_rollback(); show_error('Verifikasi gagal disimpan.',500); }
        $this->db->trans_commit();
        $this->log_action($decision,'due_payment:'.(int)$id);
        $this->session->set_flashdata('notice',$decision==='verified'?'Pembayaran diverifikasi dan masuk laporan kas.':'Pembayaran dikembalikan ke warga.');
        redirect('admin/dues');
    }

    public function due($id = 0)
    {
        $this->guard(array('admin', 'bendahara', 'ketua'));
        $id = (int) $id;
        $item = $id ? $this->db->get_where('dues', array('id' => $id))->row_array() : NULL;
        if ($id && !$item) show_404();
        $citizens = $this->db->select('id,display_name')->where('is_active', 1)->order_by('display_name')->get('citizens')->result_array();
        $error = '';
        if ($this->input->method(TRUE) === 'POST') {
            $citizen_id = (int) $this->input->post('citizen_id');
            $period = (string) $this->input->post('period', TRUE);
            $category = trim((string) $this->input->post('category', TRUE));
            $amount_due = (int) $this->input->post('amount_due');
            $amount_paid = $item ? (int)$item['amount_paid'] : 0;
            if (!$this->db->get_where('citizens', array('id' => $citizen_id))->row_array() ||
                !preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $period) || !$category ||
                $amount_due < 0 || $amount_paid < 0 || $amount_due >= 1000000000000 || $amount_paid >= 1000000000000) $error = 'Lengkapi data iuran dengan benar.';
            $duplicate = $this->db->get_where('dues', array('citizen_id' => $citizen_id, 'period' => $period, 'category' => $category))->row_array();
            if ($duplicate && (int) $duplicate['id'] !== $id) $error = 'Iuran untuk warga, periode, dan kategori ini sudah ada.';
            if ($amount_due < $amount_paid) $error = 'Tagihan tidak boleh lebih kecil dari jumlah yang sudah dibayar.';
            $data = array('citizen_id' => $citizen_id, 'period' => $period, 'category' => $category,
                'amount_due' => $amount_due, 'amount_paid' => $amount_paid,
                'note' => trim((string) $this->input->post('note', TRUE)));
            if (!$error) {
                if ($id) $this->db->where('id', $id)->update('dues', $data);
                else $this->db->insert('dues', $data);
                $this->log_action($id ? 'update' : 'create', 'due:' . ($id ?: $this->db->insert_id()));
                $this->session->set_flashdata('notice', 'Iuran warga disimpan.');
                redirect('admin/dues');
            }
            $item = array_merge($item ?: array(), $data);
        }
        $this->render('due_form', array('item' => $item, 'citizens' => $citizens, 'error' => $error));
    }

    public function review_transaction($id = 0)
    {
        $this->guard(array('admin', 'ketua'));
        if ($this->input->method(TRUE) !== 'POST') show_404();
        $item = $this->db->get_where('transactions', array('id' => (int) $id))->row_array();
        if (!$item || $item['status'] !== 'pending') show_404();
        $decision = $this->input->post('decision', TRUE);
        if (!in_array($decision, array('verified', 'returned'), TRUE)) show_404();
        $data = array('status' => $decision, 'review_note' => trim((string) $this->input->post('review_note', TRUE)),
            'verified_by' => $decision === 'verified' ? $this->session->userdata('admin_id') : NULL,
            'verified_at' => $decision === 'verified' ? date('Y-m-d H:i:s') : NULL);
        $this->db->where('id', $id)->update('transactions', $data);
        $this->log_action($decision, 'transaction:' . $id);
        $this->session->set_flashdata('notice', 'Status transaksi diperbarui.');
        redirect('admin/transactions');
    }

    public function submit_transaction($id = 0)
    {
        $this->guard(array('admin', 'bendahara'));
        if ($this->input->method(TRUE) !== 'POST') show_404();
        $item = $this->db->get_where('transactions', array('id' => (int) $id))->row_array();
        if (!$item || !in_array($item['status'], array('draft', 'returned'), TRUE)) show_404();
        $this->db->where('id', $id)->update('transactions', array('status' => 'pending'));
        $this->log_action('submit', 'transaction:' . $id);
        redirect('admin/transactions');
    }

    private function upload_proof(&$error)
    {
        $config = array('upload_path' => FCPATH . 'uploads/proofs/', 'allowed_types' => 'jpg|jpeg|png|webp|pdf',
            'max_size' => 5120, 'encrypt_name' => TRUE, 'file_ext_tolower' => TRUE,
            'detect_mime' => TRUE, 'mod_mime_fix' => TRUE);
        $this->load->library('upload', $config);
        $this->upload->initialize($config);
        if (!$this->upload->do_upload('proof')) {
            // REVISI: pesan unggah bukti dalam Bahasa Indonesia + petunjuk (sebelumnya pesan bawaan CodeIgniter berbahasa Inggris).
            $raw = strip_tags($this->upload->display_errors('', ''));
            $code = (int) ($_FILES['proof']['error'] ?? 0);
            if (stripos($raw, 'filetype') !== FALSE) {
                $error = 'Transaksi belum disimpan: format file bukti tidak didukung. Gunakan JPG, PNG, WebP, atau PDF. Foto HEIC dari iPhone perlu diubah ke JPG terlebih dahulu.';
            } elseif (stripos($raw, 'larger') !== FALSE || in_array($code, array(UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE), TRUE)) {
                $error = 'Transaksi belum disimpan: ukuran file bukti lebih dari 5 MB. Kecilkan/kompres foto atau pilih file lain.';
            } elseif (stripos($raw, 'upload path') !== FALSE || stripos($raw, 'writable') !== FALSE) {
                $error = 'Transaksi belum disimpan: folder penyimpanan bukti tidak bisa ditulis. Hubungi pengelola sistem.';
            } else {
                $error = 'Transaksi belum disimpan: bukti gagal diunggah. Coba pilih file lain atau simpan tanpa bukti.';
            }
            log_message('error', 'upload_proof: ' . $raw);
            return NULL;
        }
        return $this->upload->data('file_name');
    }

    private function upload_image($field, &$error, $allowed_types = NULL)
    {
        $config = array('upload_path' => FCPATH . 'uploads/', 'allowed_types' => $allowed_types ?: image_upload_types(),
            'max_size' => 4096, 'encrypt_name' => TRUE, 'file_ext_tolower' => TRUE,
            'detect_mime' => TRUE, 'mod_mime_fix' => TRUE);
        $this->load->library('upload', $config);
        $this->upload->initialize($config);
        if (!$this->upload->do_upload($field)) {
            $error = image_upload_error($this->upload->display_errors('', ''), $_FILES[$field]['name'] ?? '');
            return NULL;
        }
        // Pastikan isinya benar-benar gambar (bukan file lain yang diganti ekstensinya).
        $file = $this->upload->data('full_path');
        if (@getimagesize($file) === FALSE) {
            @unlink($file);
            $error = 'File yang diunggah bukan gambar yang valid. Gunakan ' . image_formats_label() . '.';
            return NULL;
        }
        return 'uploads/' . $this->upload->data('file_name');
    }

    private function upload_official_photo($field, &$error)
    {
        $directory = FCPATH . 'uploads/pengurus/';
        $config = array('upload_path' => $directory, 'allowed_types' => image_upload_types(),
            'max_size' => 2048, 'encrypt_name' => TRUE, 'file_ext_tolower' => TRUE,
            'detect_mime' => TRUE, 'mod_mime_fix' => TRUE);
        $this->load->library('upload', $config);
        $this->upload->initialize($config);
        if (!$this->upload->do_upload($field)) {
            $error = strip_tags($this->upload->display_errors('', ''));
            return NULL;
        }
        $file = $this->upload->data('full_path');
        if (@getimagesize($file) === FALSE) {
            @unlink($file);
            $error = 'File yang diunggah bukan gambar yang valid.';
            return NULL;
        }
        return 'uploads/pengurus/' . $this->upload->data('file_name');
    }

    private function remove_uploaded_file($relative_path)
    {
        $prefix = 'uploads/pengurus/';
        if (strpos((string) $relative_path, $prefix) !== 0) return;
        $path = realpath(FCPATH . $relative_path);
        $directory = realpath(FCPATH . $prefix);
        if ($path && $directory && strpos($path, $directory . DIRECTORY_SEPARATOR) === 0 && is_file($path)) @unlink($path);
    }

    private function remove_post_image($relative_path)
    {
        $relative_path = str_replace('\\', '/', (string) $relative_path);
        if (strpos($relative_path, 'uploads/') !== 0 || substr($relative_path, 8) !== basename($relative_path)) return;

        $path = realpath(FCPATH . $relative_path);
        $directory = realpath(FCPATH . 'uploads/');
        if ($path && $directory && dirname($path) === $directory && is_file($path)) @unlink($path);
    }
}
