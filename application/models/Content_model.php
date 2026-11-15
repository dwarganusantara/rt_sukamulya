<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Content_model extends CI_Model
{
    public function settings()
    {
        $rows = $this->db->get('settings')->result_array();
        $result = array();
        foreach ($rows as $row) $result[$row['setting_key']] = $row['setting_value'];
        // Statistik publik selalu mengikuti master KK yang sudah terverifikasi.
        foreach ($this->population_stats() as $key => $value) $result[$key] = (string) $value;
        return $result;
    }

    public function population_stats()
    {
        $stats = array('households' => 0, 'residents' => 0, 'female_residents' => 0, 'male_residents' => 0);
        if (!$this->db->table_exists('kk_families') || !$this->db->table_exists('kk_members')) return $stats;

        // kk_families/kk_members merupakan master hasil verifikasi. Penduduk
        // pindah atau meninggal tetap menjadi histori, tetapi tidak dihitung.
        $stats['households'] = (int) $this->db->count_all('kk_families');
        $has_status = $this->db->field_exists('status', 'kk_members');
        $this->db->select('fields_json')->from('kk_members');
        if ($has_status) $this->db->where('status', 'aktif');
        foreach ($this->db->get()->result_array() as $row) {
            $stats['residents']++;
            $fields = json_decode((string) $row['fields_json'], TRUE);
            $gender = is_array($fields) ? strtoupper(trim((string) ($fields['jenis_kelamin'] ?? ''))) : '';
            if ($gender === 'L') $stats['male_residents']++;
            if ($gender === 'P') $stats['female_residents']++;
        }
        return $stats;
    }

    public function save_settings($values, $note = '')
    {
        // Simpan nilai lama yang akan berubah agar pengaturan dapat dikembalikan (Modul 06).
        if ($this->db->table_exists('settings_history')) {
            $current = $this->settings();
            $old = array();
            foreach ($values as $key => $value) {
                if ($key === 'ops_schema_version') continue;
                if ((string) ($current[$key] ?? '') !== (string) $value) $old[$key] = $current[$key] ?? '';
            }
            if ($old) {
                $row = array('snapshot_json' => json_encode($old, JSON_UNESCAPED_UNICODE),
                    'note' => mb_substr($note ?: 'Perubahan: ' . implode(', ', array_keys($old)), 0, 255), 'changed_by' => (int) $this->session->userdata('admin_id') ?: NULL);
                // Nama pelaku juga dicatat agar perubahan oleh Admin Pusat (tanpa admin_id) tetap terlihat di Riwayat Pengaturan.
                if ($this->db->field_exists('changed_by_name', 'settings_history')) {
                    $name = $this->session->userdata('platform_owner_name');
                    if (!$row['changed_by'] && $name) $row['changed_by_name'] = mb_substr('Admin Pusat (' . $name . ')', 0, 80);
                }
                $this->db->insert('settings_history', $row);
            }
        }
        foreach ($values as $key => $value) {
            $this->db->replace('settings', array('setting_key' => $key, 'setting_value' => $value));
        }
    }

    public function pages($menu_only = FALSE)
    {
        if ($menu_only) $this->db->where('show_in_menu', 1)->where('is_published', 1);
        return $this->db->order_by('sort_order', 'ASC')->order_by('id', 'ASC')->get('pages')->result_array();
    }

    public function page($id)
    {
        return $this->db->get_where('pages', array('id' => (int) $id))->row_array();
    }

    public function published_page($slug)
    {
        return $this->db->get_where('pages', array('slug' => $slug, 'is_published' => 1))->row_array();
    }

    public function unique_slug($title, $except_id = 0)
    {
        $slug = url_title($title, '-', TRUE) ?: 'halaman';
        $base = $slug;
        $n = 2;
        while (TRUE) {
            $query = $this->db->get_where('pages', array('slug' => $slug))->row_array();
            if (!$query || (int) $query['id'] === (int) $except_id) return $slug;
            $slug = $base . '-' . $n++;
        }
    }
}
