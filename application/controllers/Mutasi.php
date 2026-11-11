<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Modul 02 — Mutasi Warga.
 * Warga mengajukan perubahan atas keluarga terverifikasi miliknya; pengurus memeriksa
 * dan menyetujui. Data master (kk_members / kk_families) hanya berubah saat disetujui,
 * di dalam satu transaksi, dan snapshot sebelum/sesudah disimpan pada baris mutasi.
 * Riwayat tidak pernah dihapus: anggota pindah/meninggal hanya berubah status.
 */
class Mutasi extends Ops_Controller
{
    public static $types = array(
        'masuk' => 'Anggota Masuk / Lahir',
        'pindah' => 'Anggota Pindah Keluar',
        'meninggal' => 'Anggota Meninggal',
        'ubah_data' => 'Koreksi Data Anggota',
        'ubah_alamat' => 'Perubahan Alamat Keluarga',
    );
    public static $labels = array('submitted' => 'Diajukan', 'in_review' => 'Diperiksa', 'revision_requested' => 'Perlu Perbaikan',
        'approved' => 'Disetujui', 'rejected' => 'Ditolak', 'cancelled' => 'Dibatalkan');
    public static $tones = array('submitted' => 'pending', 'in_review' => 'info', 'revision_requested' => 'purple', 'approved' => 'ok', 'rejected' => 'bad', 'cancelled' => 'muted');
    private $member_edit = array('nama_lengkap', 'jenis_kelamin', 'tempat_lahir', 'tanggal_lahir', 'agama', 'pendidikan', 'pekerjaan', 'golongan_darah', 'hubungan_keluarga');
    private $family_edit = array('alamat', 'rt', 'rw', 'kelurahan', 'kecamatan', 'kota', 'provinsi', 'kode_pos');
    private $domicile = array('tetap' => 'Penduduk Tetap', 'kontrak' => 'Kontrak / Sewa', 'sementara' => 'Domisili Sementara');

    public function __construct()
    {
        parent::__construct();
        $this->load->library('Kk_fields');
    }

    /* ================= WARGA ================= */

    public function index()
    {
        $this->guard_citizen();
        $family = $this->Ops_model->family_by_owner($this->citizen_id);
        $items = $this->db->order_by('id', 'DESC')->get_where('resident_mutations', array('citizen_id' => $this->citizen_id))->result_array();
        $this->render_citizen('mutasi_list', 'Mutasi Data Keluarga', array('family' => $family, 'items' => $items, 'types' => self::$types,
            'labels' => self::$labels, 'tones' => self::$tones, 'member_names' => $this->member_names($family)));
    }

    public function ajukan()
    {
        $this->guard_citizen();
        $family = $this->Ops_model->family_by_owner($this->citizen_id);
        if (!$family) $this->back('warga/mutasi', '', 'Data KK Anda belum terverifikasi. Ajukan data KK terlebih dahulu melalui menu Data Keluarga.');
        $existing = NULL;
        $edit_id = (int) $this->input->get('ubah');
        if ($edit_id) {
            $existing = $this->db->get_where('resident_mutations', array('id' => $edit_id, 'citizen_id' => $this->citizen_id, 'status' => 'revision_requested'))->row_array();
            if (!$existing) show_404();
        }
        $type = $existing ? $existing['mutation_type'] : $this->choice('jenis', self::$types, '', 'get');
        $error = '';
        $values = $existing ? (json_decode($existing['proposed_values_json'], TRUE) ?: array()) + array('member_id' => $existing['member_id'], 'effective_date' => $existing['effective_date'], 'reason' => $existing['reason']) : array();
        if ($type && $this->input->method(TRUE) === 'POST') {
            try {
                $input = $this->collect($type, $family);
                $values = $input['proposed'] + array('member_id' => $input['member_id'], 'effective_date' => $input['effective_date'], 'reason' => $input['reason']);
                $id = $this->atomic(function () use ($input, $type, $family, $existing) {
                    $this->db->query('SELECT id FROM citizens WHERE id=? FOR UPDATE', array($this->citizen_id));
                    $this->assert_no_pending($family['id'], $input['member_id'], $existing ? (int) $existing['id'] : 0, $type);
                    $row = array('mutation_type' => $type, 'family_id' => (int) $family['id'], 'member_id' => $input['member_id'] ?: NULL,
                        'citizen_id' => $this->citizen_id, 'effective_date' => $input['effective_date'], 'reason' => $input['reason'],
                        'proposed_values_json' => json_encode($input['proposed'], JSON_UNESCAPED_UNICODE), 'status' => 'submitted', 'updated_at' => date('Y-m-d H:i:s'));
                    if ($existing) {
                        $this->db->where('id', (int) $existing['id'])->where('status', 'revision_requested')->update('resident_mutations', $row);
                        return (int) $existing['id'];
                    }
                    $this->db->insert('resident_mutations', $row);
                    return (int) $this->db->insert_id();
                });
                $this->audit_citizen($existing ? 'resubmit' : 'submit', 'mutation:' . $id, self::$types[$type]);
                $this->back('warga/mutasi/' . $id, 'Pengajuan mutasi ' . $this->code($id) . ' terkirim dan menunggu pemeriksaan pengurus.');
            } catch (RuntimeException $e) {
                $error = $e->getMessage();
            }
        }
        $this->render_citizen('mutasi_form', 'Ajukan Mutasi', array('family' => $family, 'type' => $type, 'types' => self::$types, 'values' => $values,
            'error' => $error, 'existing' => $existing, 'relationships' => array_combine($this->kk_fields->relationships, $this->kk_fields->relationships),
            'domicile' => $this->domicile, 'member_edit' => $this->member_edit, 'family_edit' => $this->family_edit, 'action' => site_url('warga/mutasi/ajukan') . ($existing ? '?ubah=' . (int) $existing['id'] : '?jenis=' . $type)));
    }

    public function detail($id = 0)
    {
        $this->guard_citizen();
        $item = $this->db->get_where('resident_mutations', array('id' => (int) $id, 'citizen_id' => $this->citizen_id))->row_array();
        if (!$item) show_404();
        $family = $this->Ops_model->family_by_id($item['family_id']);
        $this->render_citizen('mutasi_detail', 'Detail Mutasi ' . $this->code($item['id']), array('item' => $item, 'family' => $family, 'types' => self::$types,
            'labels' => self::$labels, 'tones' => self::$tones, 'code' => $this->code($item['id']), 'member_names' => $this->member_names($family)));
    }

    public function batal($id = 0)
    {
        $this->guard_citizen();
        $this->post_only();
        $done = $this->db->where('id', (int) $id)->where('citizen_id', $this->citizen_id)->where_in('status', array('submitted', 'revision_requested'))
            ->update('resident_mutations', array('status' => 'cancelled', 'updated_at' => date('Y-m-d H:i:s')));
        if (!$done || !$this->db->affected_rows()) $this->back('warga/mutasi/' . (int) $id, '', 'Pengajuan tidak dapat dibatalkan karena sudah diproses.');
        $this->audit_citizen('cancel', 'mutation:' . (int) $id);
        $this->back('warga/mutasi', 'Pengajuan mutasi dibatalkan.');
    }

    /* ================= ADMIN ================= */

    public function admin_index()
    {
        $this->guard_admin('mutasi.read');
        $status = $this->choice('status', self::$labels + array('open' => 'Perlu Tindakan', 'all' => 'Semua'), 'open', 'get');
        // 'perubahan' = tab gabungan koreksi data anggota + perubahan alamat keluarga.
        $type = $this->choice('jenis', self::$types + array('perubahan' => 'Perubahan Data'), '', 'get');
        $q = $this->str('q', 80, 'get');
        $period = (string) $this->input->get('periode', TRUE);
        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $period)) $period = '';
        $counts = array();
        foreach ($this->db->select('status,COUNT(*) n')->group_by('status')->get('resident_mutations')->result_array() as $r) $counts[$r['status']] = (int) $r['n'];
        $periods = array_column($this->db->query("SELECT DISTINCT DATE_FORMAT(created_at,'%Y-%m') p FROM resident_mutations WHERE created_at IS NOT NULL ORDER BY p DESC LIMIT 24")->result_array(), 'p');
        $apply = function () use ($status, $type, $q, $period) {
            $this->db->from('resident_mutations m')->join('kk_families f', 'f.id=m.family_id', 'left')->join('citizens c', 'c.id=m.citizen_id', 'left');
            if ($status === 'open') $this->db->where_in('m.status', array('submitted', 'in_review'));
            elseif ($status !== 'all') $this->db->where('m.status', $status);
            if ($type === 'perubahan') $this->db->where_in('m.mutation_type', array('ubah_data', 'ubah_alamat'));
            elseif ($type) $this->db->where('m.mutation_type', $type);
            if ($period !== '') $this->db->where('m.created_at >=', $period . '-01')->where('m.created_at <', date('Y-m-d', strtotime($period . '-01 +1 month')));
            if ($q !== '') $this->db->group_start()->like('c.display_name', $q)->or_like('m.proposed_values_json', $q)->or_like('m.reason', $q)->group_end();
        };
        $apply(); $matched = (int) $this->db->count_all_results();
        $per_page = 15; $pages = max(1, (int) ceil($matched / $per_page)); $page = min($pages, max(1, (int) $this->input->get('hal')));
        $apply();
        $items = $this->db->select('m.*,f.nomor_kk,f.fields_json family_json,c.display_name')
            ->order_by("m.status IN ('submitted','in_review')", 'DESC', FALSE)->order_by('m.id', 'DESC')->limit($per_page, ($page - 1) * $per_page)->get()->result_array();
        $family_size = array(); $family_head = array();
        $fids = array_unique(array_filter(array_column($items, 'family_id')));
        if ($fids && $this->db->table_exists('kk_members')) foreach ($this->db->select('family_id,fields_json,status')->where_in('family_id', $fids)->get('kk_members')->result_array() as $r) {
            if ($r['status'] !== 'aktif') continue;
            $fid = (int) $r['family_id']; $family_size[$fid] = ($family_size[$fid] ?? 0) + 1;
            $f = json_decode($r['fields_json'], TRUE) ?: array();
            if (($f['hubungan_keluarga'] ?? '') === 'Kepala Keluarga') $family_head[$fid] = (string) ($f['nama_lengkap'] ?? '');
        }
        $members = array();
        $ids = array_filter(array_column($items, 'member_id'));
        if ($ids) foreach ($this->db->select('id,fields_json')->where_in('id', $ids)->get('kk_members')->result_array() as $m) $members[(int) $m['id']] = (json_decode($m['fields_json'], TRUE)['nama_lengkap'] ?? '');
        $this->render('mutasi_index', array('items' => $items, 'counts' => $counts, 'status' => $status, 'type' => $type, 'q' => $q, 'members' => $members,
            'period' => $period, 'periods' => $periods, 'matched' => $matched, 'page' => $page, 'pages' => $pages, 'family_size' => $family_size, 'family_head' => $family_head,
            'types' => self::$types, 'labels' => self::$labels, 'tones' => self::$tones));
    }

    public function admin_create()
    {
        $this->guard_admin('mutasi.review');
        $family_id = (int) ($this->input->get('keluarga') ?: $this->input->post('family_id'));
        $type = $this->choice('jenis', self::$types, '', 'get');
        $family = $family_id ? $this->Ops_model->family_by_id($family_id) : NULL;
        $error = '';
        $values = array();
        // Pra-pilih anggota dari tautan Data Penduduk (?anggota=ID); tetap divalidasi ulang saat disimpan.
        $preset = (int) $this->input->get('anggota');
        if ($preset && $family) foreach ((array) $family['members'] as $m) if ((int) $m['id'] === $preset && $m['status'] === 'aktif') $values['member_id'] = $preset;
        if ($family && $type && $this->input->method(TRUE) === 'POST') {
            try {
                $input = $this->collect($type, $family);
                $values = $input['proposed'] + array('member_id' => $input['member_id'], 'effective_date' => $input['effective_date'], 'reason' => $input['reason']);
                $id = $this->atomic(function () use ($input, $type, $family) {
                    $this->db->query('SELECT id FROM kk_families WHERE id=? FOR UPDATE', array((int) $family['id']));
                    $this->assert_no_pending($family['id'], $input['member_id'], 0, $type);
                    $this->db->insert('resident_mutations', array('mutation_type' => $type, 'family_id' => (int) $family['id'], 'member_id' => $input['member_id'] ?: NULL,
                        'created_by_admin' => $this->admin_id, 'effective_date' => $input['effective_date'], 'reason' => $input['reason'],
                        'proposed_values_json' => json_encode($input['proposed'], JSON_UNESCAPED_UNICODE), 'status' => 'in_review', 'updated_at' => date('Y-m-d H:i:s')));
                    return (int) $this->db->insert_id();
                });
                $this->audit('create', 'mutation:' . $id, self::$types[$type]);
                $this->back('admin/mutasi/' . $id, 'Mutasi ' . $this->code($id) . ' dicatat dan menunggu persetujuan.');
            } catch (RuntimeException $e) {
                $error = $e->getMessage();
            }
        }
        $families = array();
        if (!$family) {
            foreach ($this->db->select('f.id,f.nomor_kk,f.fields_json,c.display_name')->from('kk_families f')->join('citizens c', 'c.id=f.owner_citizen_id', 'left')->order_by('c.display_name')->get()->result_array() as $f) {
                $fields = json_decode($f['fields_json'], TRUE) ?: array();
                $families[(int) $f['id']] = ($f['display_name'] ?: 'Tanpa akun') . ' — KK ' . rt_mask_id($f['nomor_kk']) . ' — ' . mb_substr($fields['alamat'] ?? '', 0, 50);
            }
        }
        $this->render('mutasi_form', array('family' => $family, 'families' => $families, 'type' => $type, 'types' => self::$types, 'values' => $values, 'error' => $error,
            'relationships' => array_combine($this->kk_fields->relationships, $this->kk_fields->relationships), 'domicile' => $this->domicile,
            'member_edit' => $this->member_edit, 'family_edit' => $this->family_edit));
    }

    public function admin_detail($id = 0)
    {
        $this->guard_admin('mutasi.read');
        $id = (int) $id;
        if ($this->input->method(TRUE) === 'POST') {
            $decision = $this->choice('decision', array('in_review' => 1, 'revision_requested' => 1, 'rejected' => 1, 'approved' => 1));
            $note = $this->str('review_note', 1000);
            try {
                if (!$decision) throw new RuntimeException('Pilih keputusan.');
                $this->need($decision === 'approved' ? 'mutasi.approve' : 'mutasi.review');
                if (in_array($decision, array('revision_requested', 'rejected'), TRUE) && $note === '') throw new RuntimeException('Alasan wajib diisi agar pemohon tahu yang perlu diperbaiki.');
                $this->atomic(function () use ($id, $decision, $note) {
                    $m = $this->lock('resident_mutations', $id);
                    if (!$m || !in_array($m['status'], array('submitted', 'in_review'), TRUE)) throw new RuntimeException('Mutasi sudah diproses atau tidak ditemukan.');
                    $update = array('status' => $decision, 'review_note' => $note ?: NULL, 'reviewed_by' => $this->admin_id, 'reviewed_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'));
                    if ($decision === 'approved') {
                        $update['old_values_json'] = json_encode($this->apply($m), JSON_UNESCAPED_UNICODE);
                        $update['applied_at'] = date('Y-m-d H:i:s');
                    }
                    $this->db->where('id', $id)->update('resident_mutations', $update);
                });
                $this->audit($decision, 'mutation:' . $id, $note);
                $owner = (int) ($this->db->select('citizen_id')->get_where('resident_mutations', array('id' => $id))->row_array()['citizen_id'] ?? 0);
                if ($owner && $decision !== 'in_review') {
                    $this->load->library('Notifier');
                    $this->notifier->enqueue_citizen($owner, 'Mutasi ' . $this->code($id) . ': ' . self::$labels[$decision],
                        'Pengajuan mutasi ' . $this->code($id) . ' sekarang berstatus: ' . self::$labels[$decision] . '.' . ($note !== '' ? "\nCatatan pengurus: " . $note : ''),
                        'mutation:' . $id . ':' . $decision . ':' . date('YmdHis'));
                }
                $this->back('admin/mutasi/' . $id, 'Mutasi ' . $this->code($id) . ' diperbarui: ' . self::$labels[$decision] . '.');
            } catch (RuntimeException $e) {
                $this->back('admin/mutasi/' . $id, '', $e->getMessage());
            }
        }
        $item = $this->db->select('m.*,c.display_name,c.phone,a.username reviewer,b.username creator')->from('resident_mutations m')
            ->join('citizens c', 'c.id=m.citizen_id', 'left')->join('admins a', 'a.id=m.reviewed_by', 'left')->join('admins b', 'b.id=m.created_by_admin', 'left')
            ->where('m.id', $id)->get()->row_array();
        if (!$item) show_404();
        $family = $this->Ops_model->family_by_id($item['family_id']);
        $this->audit('view', 'mutation:' . $id);
        $this->render('mutasi_detail', array('item' => $item, 'family' => $family, 'types' => self::$types, 'labels' => self::$labels, 'tones' => self::$tones,
            'code' => $this->code($id), 'member_names' => $this->member_names($family), 'domicile' => $this->domicile));
    }

    /* ================= ATURAN BISNIS ================= */

    private function code($id)
    {
        return 'MUT-' . str_pad((int) $id, 6, '0', STR_PAD_LEFT);
    }

    private function member_names($family)
    {
        $out = array();
        if ($family) foreach ($family['members'] as $m) $out[(int) $m['id']] = ($m['fields']['nama_lengkap'] ?? '-') . ' (' . ($m['fields']['hubungan_keluarga'] ?? '-') . ')' . ($m['status'] !== 'aktif' ? ' — ' . ucfirst($m['status']) : '');
        return $out;
    }

    /** Validasi input formulir sesuai jenis mutasi. */
    private function collect($type, $family)
    {
        $member_id = (int) $this->input->post('member_id');
        $effective = $this->date_or_null('effective_date');
        $reason = $this->str('reason', 1000);
        $proposed = array();
        $active = array();
        foreach ($family['members'] as $m) if ($m['status'] === 'aktif') $active[(int) $m['id']] = $m;
        if (!$effective) throw new RuntimeException('Tanggal efektif wajib diisi dengan format yang benar.');
        if ($effective > date('Y-m-d', strtotime('+1 year'))) throw new RuntimeException('Tanggal efektif terlalu jauh di masa depan.');
        if (mb_strlen($reason) < 5) throw new RuntimeException('Alasan / keterangan wajib diisi (minimal 5 karakter).');

        if (in_array($type, array('pindah', 'meninggal', 'ubah_data'), TRUE)) {
            if (!isset($active[$member_id])) throw new RuntimeException('Pilih anggota keluarga aktif yang terdaftar pada KK ini.');
        } else {
            $member_id = 0;
        }
        if ($type === 'masuk') {
            $raw = array();
            foreach ($this->kk_fields->member_fields as $f) $raw[$f] = $this->str('m_' . $f, 255);
            $norm = $this->kk_fields->normalize(array('keluarga' => array(), 'anggota' => array($raw)));
            $m = $norm['anggota'][0] ?? NULL;
            if (!$m || $m['nama_lengkap'] === '' || !preg_match('/^\d{16}$/', $m['nik']) || !in_array($m['jenis_kelamin'], array('L', 'P'), TRUE) || $m['hubungan_keluarga'] === '') {
                throw new RuntimeException('Lengkapi nama, NIK 16 digit, jenis kelamin, dan hubungan keluarga anggota baru.');
            }
            unset($m['fields_need_review']);
            if ($m['hubungan_keluarga'] === 'Kepala Keluarga') foreach ($active as $a) if (($a['fields']['hubungan_keluarga'] ?? '') === 'Kepala Keluarga') throw new RuntimeException('Keluarga ini sudah memiliki Kepala Keluarga aktif.');
            $proposed = $m;
            $proposed['domicile_status'] = $this->choice('domicile_status', $this->domicile, 'tetap');
        } elseif ($type === 'pindah') {
            $proposed['alamat_tujuan'] = $this->str('alamat_tujuan', 255);
            if ($proposed['alamat_tujuan'] === '') throw new RuntimeException('Alamat tujuan pindah wajib diisi.');
        } elseif ($type === 'meninggal') {
            $proposed['tempat_meninggal'] = $this->str('tempat_meninggal', 160);
        } elseif ($type === 'ubah_data') {
            $current = $active[$member_id]['fields'];
            foreach ($this->member_edit as $f) {
                $v = $this->str('m_' . $f, 255);
                if ($f === 'jenis_kelamin' && $v !== '') $v = $this->kk_fields->gender($v);
                if ($f === 'hubungan_keluarga' && $v !== '') $v = $this->kk_fields->relationship($v);
                if ($f === 'tanggal_lahir' && $v !== '' && !ops_valid_date($v)) throw new RuntimeException('Format tanggal lahir tidak valid.');
                if ($v !== '' && $v !== (string) ($current[$f] ?? '')) $proposed[$f] = $v;
            }
            $dom = $this->choice('domicile_status', $this->domicile, '');
            if ($dom !== '' && $dom !== (string) ($active[$member_id]['domicile_status'] ?? '')) $proposed['domicile_status'] = $dom;
            if (!$proposed) throw new RuntimeException('Tidak ada perubahan data. Ubah minimal satu field.');
            if (($proposed['hubungan_keluarga'] ?? '') === 'Kepala Keluarga') foreach ($active as $id => $a) if ($id !== $member_id && ($a['fields']['hubungan_keluarga'] ?? '') === 'Kepala Keluarga') throw new RuntimeException('Sudah ada Kepala Keluarga aktif lain pada KK ini.');
        } elseif ($type === 'ubah_alamat') {
            foreach ($this->family_edit as $f) {
                $v = $this->str('f_' . $f, 255);
                if (in_array($f, array('rt', 'rw'), TRUE) && $v !== '' && !preg_match('/^\d{1,3}$/', $v)) throw new RuntimeException('RT/RW harus berupa angka.');
                if ($v !== '' && $v !== (string) ($family['fields'][$f] ?? '')) $proposed[$f] = $v;
            }
            if (!$proposed) throw new RuntimeException('Tidak ada perubahan alamat. Ubah minimal satu field.');
        }
        return array('member_id' => $member_id, 'effective_date' => $effective, 'reason' => $reason, 'proposed' => $proposed);
    }

    private function assert_no_pending($family_id, $member_id, $except_id, $type)
    {
        $this->db->where('family_id', (int) $family_id)->where_in('status', array('submitted', 'in_review', 'revision_requested'))->where('id !=', (int) $except_id);
        if ($member_id) $this->db->where('member_id', (int) $member_id);
        else $this->db->where('member_id IS NULL', NULL, FALSE)->where('mutation_type', $type);
        if ($this->db->count_all_results('resident_mutations') > 0) throw new RuntimeException('Masih ada pengajuan mutasi lain yang belum selesai untuk data ini. Selesaikan atau batalkan dulu.');
    }

    /**
     * Terapkan mutasi yang disetujui ke data master. Dipanggil di dalam transaksi
     * setelah baris mutasi dikunci, sehingga hanya terjadi sekali.
     * @return array snapshot data sebelum perubahan
     */
    private function apply($m)
    {
        $proposed = json_decode($m['proposed_values_json'], TRUE) ?: array();
        $family = $this->db->query('SELECT * FROM kk_families WHERE id=? FOR UPDATE', array((int) $m['family_id']))->row_array();
        if (!$family) throw new RuntimeException('Data keluarga tidak ditemukan.');
        $now = date('Y-m-d H:i:s');
        $effective = $m['effective_date'] ? $m['effective_date'] . ' 00:00:00' : $now;

        if ($m['mutation_type'] === 'ubah_alamat') {
            $fields = json_decode($family['fields_json'], TRUE) ?: array();
            $before = array_intersect_key($fields, array_flip($this->family_edit));
            foreach ($proposed as $k => $v) if (in_array($k, $this->family_edit, TRUE)) $fields[$k] = $v;
            $this->db->where('id', (int) $family['id'])->update('kk_families', array('fields_json' => json_encode($fields), 'updated_at' => $now));
            return array('keluarga' => $before);
        }

        if ($m['mutation_type'] === 'masuk') {
            $nik = (string) ($proposed['nik'] ?? '');
            $existing = $this->db->query('SELECT * FROM kk_members WHERE nik=? FOR UPDATE', array($nik))->row_array();
            if ($existing && ($existing['status'] ?? 'aktif') === 'aktif') throw new RuntimeException('NIK sudah terdaftar aktif pada keluarga lain. Lakukan mutasi pindah dari keluarga asal terlebih dahulu.');
            if (($proposed['hubungan_keluarga'] ?? '') === 'Kepala Keluarga') {
                foreach ($this->db->get_where('kk_members', array('family_id' => (int) $family['id'], 'status' => 'aktif'))->result_array() as $a) {
                    if ((json_decode($a['fields_json'], TRUE)['hubungan_keluarga'] ?? '') === 'Kepala Keluarga') throw new RuntimeException('Keluarga sudah memiliki Kepala Keluarga aktif.');
                }
            }
            $fields = array_intersect_key($proposed, array_flip($this->kk_fields->member_fields));
            $record = array('family_id' => (int) $family['id'], 'nik' => $nik, 'fields_json' => json_encode($fields), 'status' => 'aktif',
                'status_changed_at' => $effective, 'domicile_status' => $proposed['domicile_status'] ?? 'tetap', 'resident_since' => $m['effective_date']);
            if ($existing) {
                // Warga yang pernah pindah/keluar dipindahkan ke keluarga baru; histori lama tetap di tabel mutasi.
                $this->db->where('id', (int) $existing['id'])->update('kk_members', $record);
                $member_id = (int) $existing['id'];
                $before = array('member_id' => $member_id, 'family_id' => (int) $existing['family_id'], 'status' => $existing['status'], 'fields' => json_decode($existing['fields_json'], TRUE));
            } else {
                $this->db->insert('kk_members', $record);
                $member_id = (int) $this->db->insert_id();
                $before = array('member_id' => NULL);
            }
            $this->db->where('id', (int) $m['id'])->update('resident_mutations', array('member_id' => $member_id));
            return $before;
        }

        $member = $this->db->query('SELECT * FROM kk_members WHERE id=? AND family_id=? FOR UPDATE', array((int) $m['member_id'], (int) $family['id']))->row_array();
        if (!$member) throw new RuntimeException('Anggota keluarga tidak ditemukan pada KK ini.');
        if (($member['status'] ?? 'aktif') !== 'aktif') throw new RuntimeException('Anggota ini sudah tidak berstatus aktif.');
        $fields = json_decode($member['fields_json'], TRUE) ?: array();
        $before = array('status' => $member['status'] ?? 'aktif', 'domicile_status' => $member['domicile_status'] ?? NULL, 'fields' => array_intersect_key($fields, array_flip($this->member_edit)));

        if ($m['mutation_type'] === 'pindah' || $m['mutation_type'] === 'meninggal') {
            $this->db->where('id', (int) $member['id'])->update('kk_members', array('status' => $m['mutation_type'], 'status_changed_at' => $effective));
        } elseif ($m['mutation_type'] === 'ubah_data') {
            foreach ($proposed as $k => $v) if (in_array($k, $this->member_edit, TRUE)) $fields[$k] = $v;
            $update = array('fields_json' => json_encode($fields));
            if (isset($proposed['domicile_status'])) $update['domicile_status'] = $proposed['domicile_status'];
            $this->db->where('id', (int) $member['id'])->update('kk_members', $update);
        }
        return $before;
    }
}
