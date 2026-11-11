<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Pengaturan → Manajemen Fitur & Menu.
 * Mengaktifkan/menonaktifkan fitur per tenant, visibilitas warga/pengurus, audit, dan pratinjau menu warga.
 * Penegakan akses ada di libraries/Feature_gate.php (hook), bukan hanya menyembunyikan menu.
 */
class Fitur extends Ops_Controller
{
    private $fields = array('enabled' => 'Status fitur', 'visible_to_residents' => 'Tampil di portal warga', 'visible_to_staff' => 'Tampil untuk pengurus');

    public function __construct()
    {
        parent::__construct();
        $this->load->library('Feature_gate');
    }

    public function index()
    {
        $this->guard_admin('fitur.manage');
        $tab = $this->choice('tab', array('daftar' => 1, 'akses' => 1, 'dashboard' => 1, 'riwayat' => 1), 'daftar', 'get');
        $q = $this->str('q', 60, 'get');
        $catalog = $this->feature_gate->catalog();
        $categories = array_values(array_unique(array_column($catalog, 'category')));
        $category = $this->choice('kategori', array_fill_keys($categories, 1), '', 'get');
        $state = $this->feature_gate->state(TRUE) ?: array();
        $rows = array();
        foreach ($catalog as $code => $f) {
            $s = $state[$code] ?? array();
            $rows[$code] = $f + array(
                'code' => $code, 'id' => (int) ($s['id'] ?? 0),
                'enabled' => (int) ($s['enabled'] ?? 0), 'visible_to_residents' => (int) ($s['visible_to_residents'] ?? 0), 'visible_to_staff' => (int) ($s['visible_to_staff'] ?? 0),
                'effective' => $this->feature_gate->effective($code), 'residents' => $this->feature_gate->for_residents($code),
                'has_resident' => !empty($f['warga']), 'version' => $this->version($s), 'updated_at' => $s['updated_at'] ?? NULL,
                'parent_name' => !empty($f['parent']) ? ($catalog[$f['parent']]['name'] ?? $f['parent']) : '',
                'children' => array_keys(array_filter($catalog, function ($x) use ($code) {return ($x['parent'] ?? '') === $code;})),
            );
        }
        $active = count(array_filter($rows, function ($r) {return $r['effective'];}));
        $filtered = array_filter($rows, function ($r) use ($q, $category) {
            if ($category !== '' && $r['category'] !== $category) return FALSE;
            return $q === '' || mb_stripos($r['name'] . ' ' . $r['desc'] . ' ' . $r['category'], $q) !== FALSE;
        });
        foreach ($filtered as $code => &$r) $r['open'] = $this->feature_gate->open_count($code);
        unset($r);
        $data = array('tab' => $tab, 'q' => $q, 'category' => $category, 'categories' => $categories, 'rows' => $rows, 'filtered' => $filtered,
            'active' => $active, 'total' => count($rows), 'fields' => $this->fields);
        if ($tab === 'akses') {
            $this->config->load('rt_access', TRUE);
            $data['perm'] = (array) $this->config->item('rt_permissions', 'rt_access');
            $data['roles'] = (array) ($this->config->item('rt_roles', 'rt_access') ?: array());
        }
        if ($tab === 'riwayat') {
            $data['audit'] = $this->db->select('a.*,f.code,f.name,ad.username')->from('tenant_feature_audit a')->join('app_features f', 'f.id=a.feature_id', 'left')
                ->join('admins ad', 'ad.id=a.actor_user_id', 'left')->where('a.tenant_id', $this->feature_gate->tenant())->order_by('a.id', 'DESC')->limit(100)->get()->result_array();
        }
        if ($tab === 'dashboard') {
            $this->load->model('Dashboard_menu_model');
            $data['dashboard_settings']=$this->Dashboard_menu_model->all();
            $data['dashboard_items']=$this->Dashboard_menu_model->visible();
            $data['dashboard_version']=$this->Dashboard_menu_model->version();
            foreach(array('icons','colors','categories','images') as $option)$data['dashboard_'.$option]=$this->Dashboard_menu_model->options($option);
        }
        $this->render('fitur_index', $data);
    }

    /** POST: ubah satu nilai (status/visibilitas) satu fitur, atomik + audit + cek versi (UAT-13). */
    public function update()
    {
        $this->guard_admin('fitur.manage');
        $this->post_only();
        $catalog = $this->feature_gate->catalog();
        $code = $this->str('code', 80);
        $field = $this->str('field', 30);
        $value = (int) $this->input->post('value') === 1 ? 1 : 0;
        $reason = $this->str('reason', 500);
        $return = 'admin/fitur' . (($r = $this->str('return', 200)) !== '' && preg_match('/^\?[a-z0-9_=&%+.\-]*$/i', $r) ? $r : '');
        if (!isset($catalog[$code]) || !isset($this->fields[$field])) $this->back($return, '', 'Fitur atau pengaturan tidak dikenal.');
        if ($field === 'visible_to_residents' && empty($catalog[$code]['warga'])) $this->back($return, '', 'Fitur ini tidak memiliki tampilan di portal warga.');
        $open = $field === 'enabled' && !$value ? $this->feature_gate->open_count($code) : NULL;
        if ($open && mb_strlen($reason) < 5) $this->back($return, '', 'Masih ada ' . $open . ' transaksi belum selesai pada ' . $catalog[$code]['name'] . '. Isi alasan (min. 5 karakter) untuk menonaktifkan.');
        $tenant = $this->feature_gate->tenant();
        $version = $this->str('version', 40);
        try {
            $result = $this->atomic(function () use ($code, $field, $value, $reason, $tenant, $version) {
                $f = $this->db->get_where('app_features', array('code' => $code))->row_array();
                if (!$f) throw new RuntimeException('Katalog fitur belum terpasang.');
                $row = $this->db->query('SELECT * FROM tenant_features WHERE tenant_id=? AND feature_id=? FOR UPDATE', array($tenant, (int) $f['id']))->row_array();
                if (!$row) {
                    $this->db->insert('tenant_features', array('tenant_id' => $tenant, 'feature_id' => (int) $f['id'], 'enabled' => 0, 'visible_to_residents' => 1, 'visible_to_staff' => 1));
                    $row = $this->db->query('SELECT * FROM tenant_features WHERE tenant_id=? AND feature_id=? FOR UPDATE', array($tenant, (int) $f['id']))->row_array();
                } elseif ($version !== '' && $version !== $this->version($row)) {
                    throw new RuntimeException('Pengaturan fitur ini baru saja diubah oleh admin lain. Muat ulang halaman, periksa status terbaru, lalu ulangi.');
                }
                if ((int) $row[$field] === $value) return array('changed' => FALSE);
                $old = array('enabled' => (int) $row['enabled'], 'visible_to_residents' => (int) $row['visible_to_residents'], 'visible_to_staff' => (int) $row['visible_to_staff']);
                $new = array_merge($old, array($field => $value));
                $this->db->where(array('tenant_id' => $tenant, 'feature_id' => (int) $f['id']))->update('tenant_features', array($field => $value, 'updated_by' => $this->admin_id, 'updated_at' => date('Y-m-d H:i:s')));
                $this->db->insert('tenant_feature_audit', array('tenant_id' => $tenant, 'feature_id' => (int) $f['id'], 'actor_user_id' => $this->admin_id,
                    'old_value' => json_encode($old), 'new_value' => json_encode($new), 'reason' => $reason !== '' ? $reason : NULL));
                return array('changed' => TRUE, 'old' => $old, 'new' => $new);
            });
        } catch (RuntimeException $e) {
            $this->back($return, '', $e->getMessage());
        }
        if (empty($result['changed'])) $this->back($return, 'Tidak ada perubahan.');
        $name = $catalog[$code]['name'];
        $this->audit('feature_update', 'feature:' . $code, $this->fields[$field] . ' → ' . ($value ? 'aktif' : 'nonaktif') . ($reason !== '' ? ' · ' . $reason : ''));
        $msg = $field === 'enabled'
            ? $name . ($value ? ' diaktifkan. Menu kembali tampil setelah halaman dimuat ulang; data lama tetap tersedia.' : ' dinonaktifkan. Menu disembunyikan dan akses URL ditolak; data tidak dihapus.')
            : $name . ': ' . mb_strtolower($this->fields[$field]) . ' ' . ($value ? 'diaktifkan.' : 'dimatikan.');
        $children = array_keys(array_filter($catalog, function ($x) use ($code) {return ($x['parent'] ?? '') === $code;}));
        if ($field === 'enabled' && !$value && $children) $msg .= ' Fitur turunan ikut tidak aktif: ' . implode(', ', array_map(function ($c) use ($catalog) {return $catalog[$c]['name'];}, $children)) . '.';
        $parent = $catalog[$code]['parent'] ?? NULL;
        if ($field === 'enabled' && $value && $parent && !$this->feature_gate->effective($parent)) $msg .= ' Catatan: belum efektif karena fitur induk "' . $catalog[$parent]['name'] . '" masih nonaktif.';
        if ($open) $msg .= ' ' . $open . ' transaksi belum selesai tetap tersimpan; Administrator masih dapat membukanya untuk diselesaikan.';
        $this->back($return, $msg);
    }

    public function dashboard_save()
    {
        $this->guard_admin('fitur.manage');
        $this->post_only();
        $this->load->model('Dashboard_menu_model');
        try {
            $reset=$this->input->post('action')==='reset';
            $values=$reset?$this->Dashboard_menu_model->defaults():$this->Dashboard_menu_model->validate($this->input->post('items'));
            $version=$this->str('version',40);
            $this->atomic(function()use($values,$version,$reset){$this->Dashboard_menu_model->save($values,$version,$this->admin_id,$reset);});
        } catch(RuntimeException $e){$this->back('admin/fitur?tab=dashboard','',$e->getMessage());}
        $this->audit('dashboard_menu_update','tenant:'.$this->feature_gate->tenant(),$reset?'Reset menu dashboard warga':'Perbarui tampilan menu dashboard warga');
        $this->back('admin/fitur?tab=dashboard','Pengaturan dashboard disimpan. Perubahan berlaku saat warga memuat ulang halaman.');
    }
    private function version($row)
    {
        if (!$row || !isset($row['enabled'])) return '';
        return substr(sha1($row['enabled'] . '|' . $row['visible_to_residents'] . '|' . $row['visible_to_staff'] . '|' . $row['updated_at']), 0, 16);
    }
}
