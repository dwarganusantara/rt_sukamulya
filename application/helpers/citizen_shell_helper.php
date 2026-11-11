<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Data bersama untuk "shell" portal warga (header merah dua tingkat + footer banner),
 * dipakai Dashboard Warga dan halaman warga lain (mis. Data Kartu Keluarga).
 * Semua identitas diambil dari Pengaturan admin; nilai yang sudah dihitung controller dipakai ulang.
 */
if (!function_exists('citizen_shell_vars')) {
    function citizen_shell_vars(array $settings, array $given = array())
    {
        static $cache = NULL;
        if ($cache !== NULL) return $cache;
        $CI =& get_instance();
        $CI->load->helper('dashboard_ui');
        $CI->config->load('dashboard', TRUE);
        if (!isset($CI->feature_gate)) $CI->load->library('Feature_gate');
        $id = (int) $CI->session->userdata('citizen_id');

        $app = dui_title_case($settings['site_name'] ?? 'RT Digital Warga Nusantara');
        $words = preg_split('/\s+/', $app);
        $slogan = trim((string) ($settings['slogan'] ?? '')) ?: (string) $CI->config->item('dashboard_slogan_default', 'dashboard');
        $logo = trim((string) ($settings['logo'] ?? '')); if ($logo === '' || !is_file(FCPATH . $logo)) $logo = 'assets/mockup/logo.png';
        $footer_default = (string) $CI->config->item('dashboard_footer_default', 'dashboard');
        $footer_art = trim((string) ($settings['citizen_footer_image'] ?? ''));
        $custom_footer = $footer_art !== '' && is_file(FCPATH . $footer_art) && !in_array($footer_art, array($footer_default, 'assets/img/dashboard/footer-kampung.svg'), TRUE);

        $citizen = $given['citizen'] ?? ($id ? $CI->db->select('display_name,gender')->get_where('citizens', array('id' => $id))->row_array() : array());
        $avatar = $given['profile_photo'] ?? NULL;
        if (!$avatar) {
            $photos = glob(FCPATH . 'uploads/profiles/citizen_' . $id . '_*') ?: array();
            usort($photos, function ($a, $b) { return filemtime($b) <=> filemtime($a); });
            $avatar = $photos ? 'uploads/profiles/' . basename($photos[0]) : (($citizen['gender'] ?? 'female') === 'male' ? 'assets/mockup/citizen-avatar-male.png' : 'assets/mockup/citizen-avatar-female.png');
        }

        $notify = $given['notify'] ?? NULL;
        if (!$notify) {
            $chat = 0;
            if ($id && $CI->feature_gate->link_ok('warga/chat', 'warga') && $CI->db->table_exists('chat_messages')) {
                $conv = $CI->db->select('id')->get_where('chat_conversations', array('citizen_id' => $id))->row_array();
                if ($conv) $chat = $CI->db->where('conversation_id', (int) $conv['id'])->where('sender_type', 'admin')->where('is_read', 0)->count_all_results('chat_messages');
            }
            $dues = ($id && $CI->feature_gate->link_ok('warga/iuran', 'warga')) ? $CI->db->where('citizen_id', $id)->where('amount_due > amount_paid', NULL, FALSE)->count_all_results('dues') : 0;
            $notify = array('chat' => (int) $chat, 'dues' => (int) $dues, 'total' => (int) $chat + (int) $dues,
                'target' => $chat ? 'warga/chat' : ($dues ? 'warga/iuran' : ($CI->feature_gate->link_ok('pengumuman', 'warga') ? 'pengumuman' : 'warga')));
        }
        $nav = $given['dashboard_header'] ?? NULL;
        if (!$nav) { $CI->load->model('Dashboard_menu_model'); $nav = $CI->Dashboard_menu_model->header_nav(); }

        return $cache = array(
            'app' => $app, 'app_l1' => implode(' ', array_slice($words, 0, 2)), 'app_l2' => implode(' ', array_slice($words, 2)),
            'rt_label' => trim((string) ($settings['brand_label'] ?? 'RT 009/008')), 'rt_area' => trim((string) ($settings['brand_subtitle'] ?? 'Suka Mulya IV')),
            'slogan' => $slogan, 'slogan_parts' => preg_split('/,\s*(?=[^,]*$)/', $slogan, 2),
            'logo' => $logo, 'footer_art' => $custom_footer ? $footer_art : $footer_default, 'custom_footer' => $custom_footer,
            'name' => trim((string) ($citizen['display_name'] ?? $CI->session->userdata('citizen_name'))) ?: 'Warga',
            'avatar' => $avatar, 'notify' => $notify, 'nav' => $nav,
        );
    }
}
