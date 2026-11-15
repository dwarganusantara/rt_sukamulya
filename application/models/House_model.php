<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class House_model extends CI_Model
{
    const TENANT = 1;

    public function ready()
    {
        return $this->db->table_exists('rt_houses') && $this->db->table_exists('rt_family_residences');
    }

    public function houses()
    {
        if (!$this->ready()) return array();
        return $this->db->select("h.*,
                COUNT(DISTINCT CASE WHEN r.end_date IS NULL AND r.verification_status='verified' THEN r.family_id END) family_count,
                COUNT(DISTINCT CASE WHEN r.end_date IS NULL AND r.verification_status='pending' THEN r.family_id END) pending_count,
                COUNT(DISTINCT CASE WHEN r.end_date IS NULL AND r.verification_status='verified' AND m.status='aktif' THEN m.id END) resident_count", FALSE)
            ->from('rt_houses h')->join('rt_family_residences r', 'r.house_id=h.id AND r.tenant_id=h.tenant_id', 'left')
            ->join('kk_members m', 'm.family_id=r.family_id', 'left')->where('h.tenant_id', self::TENANT)
            ->group_by('h.id')->order_by('h.house_number IS NULL, h.house_number', '', FALSE)->order_by('h.house_code')->get()->result_array();
    }

    public function one($id)
    {
        if (!$this->ready()) return NULL;
        return $this->db->get_where('rt_houses', array('id' => (int) $id, 'tenant_id' => self::TENANT))->row_array();
    }

    public function save_house($id, array $data, $actor)
    {
        $id = (int) $id;
        $data['tenant_id'] = self::TENANT;
        $dup = $this->db->group_start()->where('house_code', $data['house_code']);
        if ($data['house_number'] !== NULL && $data['house_number'] !== '')
            $this->db->or_group_start()->where('house_number', $data['house_number'])->where('block_name', $data['block_name'])->group_end();
        $this->db->group_end()->where('tenant_id', self::TENANT);
        if ($id) $this->db->where('id <>', $id);
        if ($this->db->count_all_results('rt_houses')) throw new RuntimeException('Kode atau kombinasi nomor rumah dan blok sudah terdaftar. Tinjau rumah existing agar tidak membuat duplikat.');
        $data['updated_at'] = date('Y-m-d H:i:s');
        if ($id) {
            if (!$this->one($id)) throw new RuntimeException('Rumah tidak ditemukan.');
            $this->db->where(array('id' => $id, 'tenant_id' => self::TENANT))->update('rt_houses', $data);
            return $id;
        }
        $data['created_by'] = (int) $actor ?: NULL;
        $this->db->insert('rt_houses', $data);
        return (int) $this->db->insert_id();
    }

    public function active_residences()
    {
        $out = array();
        if (!$this->ready()) return $out;
        $rows = $this->db->select('r.*,h.house_code,h.house_number,h.block_name,h.address_text,h.occupancy_status')
            ->from('rt_family_residences r')->join('rt_houses h', 'h.id=r.house_id')
            ->where('r.tenant_id', self::TENANT)->where('r.end_date IS NULL', NULL, FALSE)->order_by('r.id', 'DESC')->get()->result_array();
        foreach ($rows as $r) if (!isset($out[(int) $r['family_id']]) || $r['verification_status'] === 'verified') $out[(int) $r['family_id']] = $r;
        return $out;
    }

    public function residence($id)
    {
        return $this->db->select('r.*,h.house_code,h.house_number,h.address_text')->from('rt_family_residences r')
            ->join('rt_houses h', 'h.id=r.house_id')->where(array('r.id' => (int) $id, 'r.tenant_id' => self::TENANT))->get()->row_array();
    }

    public function link_family($family_id, $house_id, $relation, $start_date, $actor, $verify)
    {
        $family_id = (int) $family_id; $house_id = (int) $house_id;
        $this->db->trans_begin();
        try {
            $family = $this->db->query('SELECT id FROM kk_families WHERE id=? FOR UPDATE', array($family_id))->row_array();
            $house = $this->db->query('SELECT id FROM rt_houses WHERE id=? AND tenant_id=? FOR UPDATE', array($house_id, self::TENANT))->row_array();
            if (!$family || !$house) throw new RuntimeException('KK atau rumah tidak ditemukan.');
            $active = $this->db->query('SELECT * FROM rt_family_residences WHERE tenant_id=? AND family_id=? AND end_date IS NULL ORDER BY id DESC FOR UPDATE', array(self::TENANT, $family_id))->result_array();
            foreach ($active as $old) {
                if ((int) $old['house_id'] === $house_id) {
                    $this->db->where('id', $old['id'])->update('rt_family_residences', array('occupancy_relation' => $relation, 'start_date' => $start_date,
                        'verification_status' => $verify ? 'verified' : 'pending', 'verified_by' => $verify ? (int) $actor : NULL,
                        'verified_at' => $verify ? date('Y-m-d H:i:s') : NULL, 'updated_at' => date('Y-m-d H:i:s')));
                    $id = (int) $old['id'];
                    if (!$this->db->trans_status()) throw new RuntimeException('Domisili belum tersimpan.');
                    $this->db->trans_commit(); return $id;
                }
                $end = date('Y-m-d', strtotime($start_date . ' -1 day'));
                $this->db->where('id', $old['id'])->update('rt_family_residences', array('end_date' => $end, 'updated_at' => date('Y-m-d H:i:s')));
            }
            // uq_res_one_active (data_integrity_upgrade.sql) menjamin satu domisili aktif per KK walau ada permintaan bersamaan.
            $debug = $this->db->db_debug; $this->db->db_debug = FALSE;
            $ok = $this->db->insert('rt_family_residences', array('tenant_id' => self::TENANT, 'family_id' => $family_id, 'house_id' => $house_id,
                'occupancy_relation' => $relation, 'start_date' => $start_date, 'verification_status' => $verify ? 'verified' : 'pending',
                'verified_by' => $verify ? (int) $actor : NULL, 'verified_at' => $verify ? date('Y-m-d H:i:s') : NULL, 'created_by' => (int) $actor ?: NULL));
            $err = $this->db->error(); $this->db->db_debug = $debug;
            if (!$ok) throw new RuntimeException((int) $err['code'] === 1062 ? 'KK ini sudah memiliki domisili aktif. Muat ulang halaman lalu coba lagi.' : 'Domisili belum tersimpan.');
            $id = (int) $this->db->insert_id();
            if (!$this->db->trans_status()) throw new RuntimeException('Domisili belum tersimpan.');
            $this->db->trans_commit(); return $id;
        } catch (Throwable $e) {$this->db->trans_rollback(); throw $e;}
    }

    public function verify($id, $actor)
    {
        $row = $this->residence($id);
        if (!$row || $row['end_date'] !== NULL) throw new RuntimeException('Relasi domisili aktif tidak ditemukan.');
        $this->db->where(array('id' => (int) $id, 'tenant_id' => self::TENANT))->update('rt_family_residences', array(
            'verification_status' => 'verified', 'verified_by' => (int) $actor ?: NULL, 'verified_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')));
    }

    public function history_for_house($house_id)
    {
        return $this->db->select('r.*,f.nomor_kk,f.fields_json')->from('rt_family_residences r')->join('kk_families f', 'f.id=r.family_id')
            ->where(array('r.house_id' => (int) $house_id, 'r.tenant_id' => self::TENANT))->order_by('r.start_date', 'DESC')->get()->result_array();
    }
}
