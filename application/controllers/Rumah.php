<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Rumah extends Ops_Controller
{
    private $relations = array('milik' => 'Milik Sendiri', 'kontrak' => 'Kontrakan / Sewa', 'menumpang' => 'Menumpang', 'dinas' => 'Rumah Dinas', 'lainnya' => 'Lainnya');
    private $types = array('rumah' => 'Rumah Tinggal', 'kontrakan' => 'Kontrakan', 'kos' => 'Kos', 'rusun' => 'Rumah Susun', 'lainnya' => 'Lainnya');
    private $statuses = array('dihuni' => 'Dihuni', 'kosong' => 'Kosong', 'nonaktif' => 'Tidak Aktif');

    public function __construct()
    {
        parent::__construct();
        $this->load->model('House_model');
    }

    public function index()
    {
        $this->guard_admin('rumah.read');
        $q = mb_strtolower($this->str('q', 80, 'get'));
        $status = $this->choice('status', $this->statuses, '', 'get');
        $rows = array_values(array_filter($this->House_model->houses(), function ($r) use ($q, $status) {
            if ($status !== '' && $r['occupancy_status'] !== $status) return FALSE;
            return $q === '' || mb_strpos(mb_strtolower($r['house_code'] . ' ' . $r['house_number'] . ' ' . $r['block_name'] . ' ' . $r['address_text']), $q) !== FALSE;
        }));
        $all = $this->House_model->houses();
        $stats = array('total' => count($all), 'dihuni' => 0, 'kosong' => 0, 'families' => 0, 'residents' => 0, 'pending' => 0);
        foreach ($all as $r) {
            if ($r['occupancy_status'] === 'dihuni' && (int) $r['family_count'] > 0) $stats['dihuni']++;
            if ($r['occupancy_status'] === 'kosong' || (int) $r['family_count'] === 0) $stats['kosong']++;
            $stats['families'] += (int) $r['family_count']; $stats['residents'] += (int) $r['resident_count']; $stats['pending'] += (int) $r['pending_count'];
        }
        $this->render('house_index', array('rows' => $rows, 'stats' => $stats, 'q' => $q, 'status' => $status,
            'statuses' => $this->statuses, 'can_write' => $this->can('rumah.write')));
    }

    public function form($id = 0)
    {
        $this->guard_admin('rumah.write');
        $id = (int) $id; $item = $id ? $this->House_model->one($id) : NULL;
        $return_family = (int) ($this->input->method(TRUE) === 'POST' ? $this->input->post('return_family') : $this->input->get('keluarga'));
        if ($id && !$item) show_404();
        $error = '';
        if ($this->input->method(TRUE) === 'POST') {
            $item = array('house_code' => strtoupper($this->str('house_code', 40)), 'house_number' => strtoupper($this->str('house_number', 30)) ?: NULL,
                'block_name' => $this->str('block_name', 100), 'address_text' => $this->str('address_text', 500), 'landmark' => $this->str('landmark', 190),
                'building_type' => $this->choice('building_type', $this->types, 'rumah'), 'occupancy_status' => $this->choice('occupancy_status', $this->statuses, 'dihuni'),
                'notes' => $this->str('notes', 500));
            try {
                if ($item['house_code'] === '' || $item['address_text'] === '') throw new RuntimeException('Kode rumah dan alamat domisili wajib diisi.');
                $saved = $this->House_model->save_house($id, $item, $this->admin_id);
                $this->audit($id ? 'update' : 'create', 'house:' . $saved, 'kode=' . $item['house_code']);
                if (!$id && $return_family > 0) {
                    $this->back('admin/rumah/tautkan?keluarga=' . $return_family . '&rumah=' . $saved, 'Rumah berhasil ditambahkan. Silakan periksa lalu hubungkan KK ke rumah.');
                }
                $this->back('admin/rumah/' . $saved, $id ? 'Data rumah diperbarui.' : 'Rumah berhasil ditambahkan. Selanjutnya hubungkan KK penghuni.');
            } catch (RuntimeException $e) {$error = $e->getMessage();}
        }
        $this->render('house_form', array('item' => $item ?: array(), 'id' => $id, 'error' => $error, 'types' => $this->types,
            'statuses' => $this->statuses, 'return_family' => $return_family));
    }

    public function detail($id = 0)
    {
        $this->guard_admin('rumah.read');
        $house = $this->House_model->one($id); if (!$house) show_404();
        $history = $this->House_model->history_for_house($id);
        foreach ($history as &$r) {
            $f = json_decode($r['fields_json'], TRUE) ?: array(); $r['address_kk'] = $f['alamat'] ?? ''; $r['head'] = '';
            foreach ($this->db->get_where('kk_members', array('family_id' => $r['family_id'], 'status' => 'aktif'))->result_array() as $m) {
                $mf = json_decode($m['fields_json'], TRUE) ?: array();
                if (($mf['hubungan_keluarga'] ?? '') === 'Kepala Keluarga') $r['head'] = $mf['nama_lengkap'] ?? '';
            }
        }
        unset($r);
        $this->render('house_detail', array('house' => $house, 'history' => $history, 'relations' => $this->relations,
            'can_write' => $this->can('rumah.write'), 'can_verify' => $this->can('domisili.verify')));
    }

    public function link()
    {
        $this->guard_admin('domisili.link');
        $family_id = (int) ($this->input->method(TRUE) === 'POST' ? $this->input->post('family_id') : $this->input->get('keluarga'));
        $house_id = (int) ($this->input->method(TRUE) === 'POST' ? $this->input->post('house_id') : $this->input->get('rumah'));
        $error = ''; $relation = 'milik'; $start = date('Y-m-d'); $verify = FALSE;
        if ($this->input->method(TRUE) === 'POST') {
            try {
                $relation = $this->choice('occupancy_relation', $this->relations, 'lainnya');
                $start = $this->date_or_null('start_date'); if (!$family_id || !$house_id || !$start) throw new RuntimeException('KK, rumah, dan tanggal mulai tinggal wajib dipilih.');
                $verify = $this->input->post('verify') === '1' && $this->can('domisili.verify');
                $rid = $this->House_model->link_family($family_id, $house_id, $relation, $start, $this->admin_id, $verify);
                $this->audit($verify ? 'verify' : 'create', 'residence:' . $rid, 'family=' . $family_id . ' house=' . $house_id);
                $this->back('admin/rumah/' . $house_id, $verify ? 'KK berhasil dihubungkan dan domisili terverifikasi.' : 'KK berhasil dihubungkan. Domisili menunggu verifikasi.');
            } catch (RuntimeException $e) {$error = $e->getMessage();}
        }
        $families = array();
        foreach ($this->db->select('id,nomor_kk,fields_json')->order_by('id', 'DESC')->get('kk_families')->result_array() as $f) {
            $head = ''; foreach ($this->db->get_where('kk_members', array('family_id' => $f['id'], 'status' => 'aktif'))->result_array() as $m) {
                $mf = json_decode($m['fields_json'], TRUE) ?: array(); if (($mf['hubungan_keluarga'] ?? '') === 'Kepala Keluarga') $head = $mf['nama_lengkap'] ?? '';
            }
            $families[(int) $f['id']] = ($head ?: 'Tanpa nama kepala keluarga') . ' — ' . rt_mask_id($f['nomor_kk']);
        }
        $houses = array(); foreach ($this->House_model->houses() as $h) $houses[(int) $h['id']] = ($h['house_number'] ?: $h['house_code']) . ' — ' . $h['address_text'];
        $this->render('house_link', array('families' => $families, 'houses' => $houses, 'family_id' => $family_id, 'house_id' => $house_id,
            'relations' => $this->relations, 'relation' => $relation, 'start' => $start, 'verify' => $verify, 'error' => $error,
            'can_verify' => $this->can('domisili.verify'), 'can_create_house' => $this->can('rumah.write')));
    }

    public function verify($id = 0)
    {
        $this->guard_admin('domisili.verify'); $this->post_only();
        try {$this->House_model->verify($id, $this->admin_id); $this->audit('verify', 'residence:' . (int) $id);}
        catch (RuntimeException $e) {$this->back('admin/rumah', '', $e->getMessage());}
        $back = $this->str('return', 80); $this->back(preg_match('#^admin/rumah/\d+$#', $back) ? $back : 'admin/rumah', 'Domisili berhasil diverifikasi.');
    }
}
