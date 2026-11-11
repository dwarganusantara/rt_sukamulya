<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller
{
}

/**
 * Basis controller untuk modul RT/RW Digital tambahan.
 * Menyediakan: guard admin/warga, pemeriksaan izin module.action, audit,
 * render dengan shell UI existing, dan helper validasi input.
 */
class Ops_Controller extends MY_Controller
{
    protected $role = '';
    protected $admin_id = 0;
    protected $citizen_id = 0;

    public function __construct()
    {
        parent::__construct();
        $this->config->load('rt_access', TRUE);
        $this->load->model('Ops_model');
        $this->load->helper(array('rt_audit', 'ops_ui', 'official_icons', 'content_form'));
        $this->Ops_model->ensure();
    }

    /* ---------- Admin ---------- */

    protected function guard_admin($perm = NULL)
    {
        $this->load->library('Platform_owner');
        if($this->platform_owner->valid_session()){
            $this->role='admin';
            $this->admin_id=0;
            $this->session->set_userdata('admin_role','admin');
            $this->output->set_header('Cache-Control: no-store, private');
            return;
        }
        if (!$this->session->userdata('admin_id')) redirect('admin/login');
        $user = $this->db->get_where('admins', array('id' => (int) $this->session->userdata('admin_id')))->row_array();
        // Akun dicabut/nonaktif langsung kehilangan akses walau sesi lama masih ada.
        if (!$user || (isset($user['is_active']) && !(int) $user['is_active'])) {
            $this->session->sess_destroy();
            redirect('admin/login');
        }
        if ($user['role'] !== $this->session->userdata('admin_role')) $this->session->set_userdata('admin_role', $user['role']);
        $this->role = $user['role'];
        if(!array_key_exists($this->role,(array)$this->config->item('rt_roles','rt_access'))){$this->session->sess_destroy();redirect('admin/login');}
        $this->admin_id = (int) $user['id'];
        $this->output->set_header('Cache-Control: no-store, private');
        if ($this->db->table_exists('chat_presence')) {$this->load->model('Chat_model'); $this->Chat_model->touch('admin', $this->admin_id);}
        if ($perm) $this->need($perm);
    }

    protected function can($perm)
    {
        $map = $this->config->item('rt_permissions', 'rt_access');
        return isset($map[$perm]) && in_array($this->role, $map[$perm], TRUE);
    }

    protected function need($perm)
    {
        if (!$this->can($perm)) show_error('Akses tidak diizinkan untuk peran Anda.', 403, 'Akses Ditolak');
    }

    protected function render($view, $data = array())
    {
        $data['settings'] = $data['settings'] ?? $this->Content_model->settings();
        $data['can'] = function ($perm) {return $this->can($perm);};
        $data['me_id'] = $this->admin_id;
        $this->load->view('admin/header', $data);
        $this->output->append_output('<link rel="stylesheet" href="' . base_url('assets/admin-ops.css') . '?v=' . filemtime(FCPATH . 'assets/admin-ops.css') . '">');
        $this->load->view('admin/ops/' . $view, $data);
        $this->load->view('admin/footer');
    }

    protected function audit($action, $subject, $details = '')
    {
        rt_audit($action, $subject, $details, $this->admin_id ?: NULL);
    }

    /* ---------- Warga ---------- */

    protected function guard_citizen()
    {
        if (!$this->session->userdata('citizen_id')) redirect('warga/login');
        $citizen = $this->db->get_where('citizens', array('id' => (int) $this->session->userdata('citizen_id'), 'is_active' => 1))->row_array();
        if (!$citizen) {
            $this->session->unset_userdata(array('citizen_id', 'citizen_name'));
            redirect('warga/login');
        }
        $this->citizen_id = (int) $citizen['id'];
        $this->output->set_header('Cache-Control: no-store, private');
        if ($this->db->table_exists('chat_presence')) {$this->load->model('Chat_model'); $this->Chat_model->touch('citizen', $this->citizen_id);}
        return $citizen;
    }

    /** Audit aksi warga: admin_id 0, identitas warga dicatat di detail. */
    protected function audit_citizen($action, $subject, $details = '')
    {
        rt_audit($action, $subject, trim('warga#' . $this->citizen_id . ' ' . $details), 0);
    }

    protected function render_citizen($view, $title, $data = array())
    {
        $data['settings'] = $this->Content_model->settings();
        $data['menu'] = $this->Content_model->pages(TRUE);
        $data['title'] = $title . ' | ' . ($data['settings']['site_name'] ?? 'Sukamulya');
        $data['citizen_letter_layout'] = TRUE;
        // Header merah portal warga + tema merah (assets/citizen-red.css) untuk semua halaman operasional warga.
        $data['citizen_shell'] = TRUE;
        $data['shell_active'] = $data['shell_active'] ?? '';
        $this->load->view('site/header', $data);
        $this->load->view('citizen/sidebar', $data);
        $css = '';
        foreach (array('citizen-dues-v3.css', 'citizen-pages-v3.css', 'admin-ops.css', 'citizen-ops.css', 'citizen-red.css') as $file) {
            $css .= '<link rel="stylesheet" href="' . base_url('assets/' . $file) . '?v=' . filemtime(FCPATH . 'assets/' . $file) . '">';
        }
        $this->output->append_output($css);
        $this->load->view('citizen/ops/' . $view, $data);
        $this->load->view('site/footer', array_merge($data, array('citizen_shell' => FALSE)));
    }

    /* ---------- Input ---------- */

    protected function post_only()
    {
        if ($this->input->method(TRUE) !== 'POST') show_error('Metode tidak diizinkan.', 405);
    }

    protected function str($name, $max = 255, $source = 'post')
    {
        $value = $source === 'get' ? $this->input->get($name, TRUE) : $this->input->post($name, TRUE);
        return mb_substr(trim((string) (is_scalar($value) ? $value : '')), 0, $max);
    }

    protected function text($name, $max = 5000)
    {
        $value = $this->input->post($name, TRUE);
        return mb_substr(trim(str_replace("\r\n", "\n", (string) (is_scalar($value) ? $value : ''))), 0, $max);
    }

    protected function choice($name, $options, $default = '', $source = 'post')
    {
        $value = $this->str($name, 60, $source);
        return array_key_exists($value, $options) ? $value : $default;
    }

    protected function date_or_null($name)
    {
        $value = $this->str($name, 10);
        return ops_valid_date($value) ? $value : NULL;
    }

    protected function money($name)
    {
        $value = preg_replace('/[^0-9]/', '', (string) $this->input->post($name, TRUE));
        $value = $value === '' ? 0 : (float) $value;
        return min($value, 999999999999);
    }

    protected function back($url, $notice = '', $error = '')
    {
        if ($notice !== '') $this->session->set_flashdata('notice', $notice);
        if ($error !== '') $this->session->set_flashdata('ops_error', $error);
        redirect($url);
    }

    /** Ambil satu baris dengan kunci FOR UPDATE; dipanggil di dalam transaksi. */
    protected function lock($table, $id)
    {
        return $this->db->query('SELECT * FROM `' . $table . '` WHERE id=? FOR UPDATE', array((int) $id))->row_array();
    }

    /** Jalankan $fn di dalam transaksi. $fn boleh melempar RuntimeException berisi pesan untuk pengguna. */
    protected function atomic($fn)
    {
        $this->db->trans_begin();
        try {
            $result = $fn();
            if ($this->db->trans_status() === FALSE) throw new RuntimeException('Penyimpanan gagal. Tidak ada data yang diubah.');
            $this->db->trans_commit();
            return $result;
        } catch (Throwable $e) {
            $this->db->trans_rollback();
            throw $e;
        }
    }
}
