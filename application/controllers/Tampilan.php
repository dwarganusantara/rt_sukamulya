<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Pengaturan → Tema & Tampilan. Hanya mengubah tampilan; data, route, endpoint, dan hak akses tidak berubah.
 * Alur: draft kerja per admin → (pratinjau di sesi sendiri) → Simpan & Terapkan (versi baru, atomik, diaudit).
 */
class Tampilan extends Ops_Controller
{
    private $tabs = array('tema' => 'Tema & Warna', 'dashboard' => 'Konten Dashboard', 'formulir' => 'Formulir & Field', 'tata-letak' => 'Tata Letak', 'logo' => 'Ikon & Logo', 'riwayat' => 'Riwayat Perubahan');

    public function __construct()
    {
        parent::__construct();
        $this->load->library('Ui_theme');
        $this->ui_theme->ensure();
    }

    private function actor_name()
    {
        if ($this->admin_id) return (string) ($this->db->select('username')->get_where('admins', array('id' => $this->admin_id))->row_array()['username'] ?? 'admin');
        return 'Admin Pusat (' . $this->session->userdata('platform_owner_name') . ')';
    }

    public function index()
    {
        $this->guard_admin('appearance.manage');
        $tab = $this->choice('tab', $this->tabs, 'tema', $this->input->method(TRUE) === 'POST' ? 'post' : 'get');
        $key = $this->ui_theme->owner_key();
        $active = $this->ui_theme->active();
        $draft = $this->ui_theme->draft($key);
        $work = $draft ?: $active['config'];
        if ($this->input->method(TRUE) === 'POST') {
            $go = $this->str('go', 20);
            $back = 'admin/tampilan?tab=' . $tab;
            try {
                if ($go === 'cancel') {$this->ui_theme->discard_draft($key); $this->back($back, 'Perubahan dibatalkan. Konfigurasi aktif dimuat ulang.');}
                if ($go === 'preview_exit') {$this->session->unset_userdata('ui_preview'); $this->back($back, 'Mode pratinjau ditutup. Draft tetap tersimpan.');}
                if ($go === 'reset_default') {
                    if ($this->input->post('confirm') !== 'RESET') throw new RuntimeException('Konfirmasi reset belum diberikan.');
                    $id = $this->ui_theme->publish($this->ui_theme->default_config(), 'reset', 'Reset ke Default (Oranye Harmoni)', $this->admin_id, $this->actor_name());
                    $this->Content_model->save_settings(array('logo' => 'assets/mockup/logo.png'), 'Reset tampilan: logo bawaan');
                    $this->ui_theme->discard_draft($key);
                    $this->audit('ui_reset', 'ui_config:' . $id, 'Reset ke Default');
                    $this->back($back, 'Semua pengaturan tampilan dipulihkan ke desain asli Oranye Harmoni. Data tidak berubah.');
                }
                if ($go === 'rollback') {
                    $vid = (int) $this->input->post('version_id');
                    $cfg = $this->ui_theme->version($vid);
                    if (!$cfg) throw new RuntimeException('Versi tidak ditemukan.');
                    $id = $this->ui_theme->publish($cfg, 'rollback', 'Kembalikan versi #' . $vid, $this->admin_id, $this->actor_name());
                    if ($cfg['brand']['logo'] !== '') $this->Content_model->save_settings(array('logo' => $cfg['brand']['logo']), 'Rollback tampilan: logo');
                    $this->ui_theme->discard_draft($key);
                    $this->audit('ui_rollback', 'ui_config:' . $id, 'dari versi #' . $vid);
                    $this->back('admin/tampilan?tab=riwayat', 'Tampilan dikembalikan ke versi #' . $vid . ' (tercatat sebagai versi baru #' . $id . ').');
                }
                // Gabungkan isian tab ini ke draft kerja.
                $work = $this->merge($work, $tab);
                if ($go === 'reset_section') {
                    $work = $this->reset_section($work, $tab);
                    $this->ui_theme->save_draft($key, $work);
                    $this->back($back, 'Bagian "' . $this->tabs[$tab] . '" dipulihkan ke bawaan pada draft. Klik Simpan & Terapkan untuk menerbitkan.');
                }
                $this->ui_theme->save_draft($key, $work);
                if ($go === 'preview') {$this->session->set_userdata('ui_preview', 1); redirect('admin');}
                if ($go === 'apply') {
                    $work = $this->ui_theme->normalize($work);
                    $id = $this->ui_theme->publish($work, 'apply', 'Tema ' . $this->ui_theme->presets()[$work['themeId']]['name'], $this->admin_id, $this->actor_name());
                    if ($work['brand']['logo'] !== '') $this->Content_model->save_settings(array('logo' => $work['brand']['logo']), 'Logo dari Tema & Tampilan');
                    $this->ui_theme->discard_draft($key);
                    $this->audit('ui_apply', 'ui_config:' . $id, $this->ui_theme->presets()[$work['themeId']]['name']);
                    $this->back($back, 'Tampilan diterapkan untuk semua pengguna (versi #' . $id . ').');
                }
                $this->back($back, 'Draft disimpan. Belum terlihat oleh pengguna lain sampai Simpan & Terapkan.');
            } catch (RuntimeException $e) {
                if (isset($work)) $this->ui_theme->save_draft($key, $work);
                $this->back($back, '', $e->getMessage());
            }
        }
        $this->load->helper('theme');
        $versions = $this->ui_theme->versions();
        $this->render('tampilan', array('tab' => $tab, 'tabs' => $this->tabs, 'work' => $work, 'has_draft' => (bool) $draft, 'active' => $active,
            'presets' => $this->ui_theme->presets(), 'widgets' => $this->ui_theme->widgets(), 'stat_cards' => $this->ui_theme->stat_cards(),
            'versions' => $versions, 'previewing' => $this->ui_theme->previewing(), 'errors' => $this->ui_theme->validate($work),
            'field_count' => $this->db->table_exists('custom_field_definitions') ? (int) $this->db->count_all('custom_field_definitions') : 0));
    }

    /** Ambil isian dari tab aktif ke konfigurasi kerja (semua divalidasi ulang oleh Ui_theme::normalize). */
    private function merge($w, $tab)
    {
        $presets = $this->ui_theme->presets();
        if ($tab === 'tema') {
            $theme = $this->str('themeId', 40);
            if (isset($presets[$theme]) && $theme !== $w['themeId']) {$w['themeId'] = $theme; $w['colors'] = $presets[$theme]['colors'];}
            elseif ($this->input->post('custom_colors')) foreach (array_keys($w['colors']) as $k) {$v = strtoupper($this->str('color_' . $k, 7)); if (preg_match('/^#[0-9A-F]{6}$/', $v)) $w['colors'][$k] = $v;}
        } elseif ($tab === 'dashboard') {
            $order = $this->input->post('order'); $order = is_array($order) ? array_map('strval', $order) : array();
            asort($order); $w['dashboard']['order'] = array_keys($order);
            $show = (array) $this->input->post('show');
            $w['dashboard']['hidden'] = array_values(array_diff(array_keys($this->ui_theme->widgets()), $show));
            $stats = (array) $this->input->post('stats');
            $w['dashboard']['stats_hidden'] = array_values(array_diff(array_keys($this->ui_theme->stat_cards()), $stats));
        } elseif ($tab === 'formulir') {
            $w['components']['inputHeight'] = $this->str('inputHeight', 20);
        } elseif ($tab === 'tata-letak') {
            $w['sidebar']['variant'] = $this->str('sidebar', 20);
            foreach (array('radius', 'fontSize', 'density') as $k) $w['components'][$k] = $this->str($k, 20);
            foreach (array('search', 'notifications', 'banner') as $k) $w['header'][$k] = (bool) $this->input->post('header_' . $k);
        } elseif ($tab === 'logo') {
            if (!empty($_FILES['logo']['name'])) $w['brand']['logo'] = $this->upload_logo();
            if ($this->input->post('logo_default')) $w['brand']['logo'] = '';
        }
        return $this->ui_theme->normalize($w);
    }

    private function reset_section($w, $tab)
    {
        $d = $this->ui_theme->default_config();
        if ($tab === 'tema') {$w['themeId'] = $d['themeId']; $w['colors'] = $d['colors'];}
        elseif ($tab === 'dashboard') $w['dashboard'] = $d['dashboard'];
        elseif ($tab === 'formulir') $w['components']['inputHeight'] = $d['components']['inputHeight'] ?? 'standard';
        elseif ($tab === 'tata-letak') {$w['sidebar'] = $d['sidebar']; $w['header'] = $d['header']; foreach (array('radius', 'fontSize', 'density') as $k) $w['components'][$k] = $d['components'][$k];}
        elseif ($tab === 'logo') $w['brand'] = $d['brand'];
        return $this->ui_theme->normalize($w);
    }

    /** Logo: hanya PNG/WebP/JPG (SVG ditolak), maks 1 MB, isi diverifikasi sebagai gambar. */
    private function upload_logo()
    {
        $f = $_FILES['logo'];
        if ($f['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($f['tmp_name'])) throw new RuntimeException('Logo gagal diunggah.');
        if ($f['size'] > 1048576) throw new RuntimeException('Ukuran logo maksimal 1 MB.');
        $info = @getimagesize($f['tmp_name']);
        $types = array(IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp', IMAGETYPE_JPEG => 'jpg');
        if (!$info || !isset($types[$info[2]])) throw new RuntimeException('Logo harus berupa gambar PNG, WebP, atau JPG.');
        if ($info[0] < 32 || $info[1] < 32 || $info[0] > 2000 || $info[1] > 2000) throw new RuntimeException('Dimensi logo 32–2000 piksel.');
        $name = 'uploads/logo_' . bin2hex(random_bytes(8)) . '.' . $types[$info[2]];
        if (!move_uploaded_file($f['tmp_name'], FCPATH . $name)) throw new RuntimeException('Logo tidak dapat disimpan.');
        return $name;
    }

    /** CSS lapisan tema untuk halaman admin (sesuai versi aktif, atau draft bila pratinjau di sesi ini). */
    public function css()
    {
        $this->guard_admin();
        $css = $this->ui_theme->css($this->ui_theme->current());
        $this->output->set_content_type('text/css', 'utf-8')->set_header('Cache-Control: private, max-age=31536000, immutable')->set_output($css);
    }
}
