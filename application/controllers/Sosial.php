<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Modul 13 — Bantuan Sosial.
 * Program → calon penerima → verifikasi kelayakan → persetujuan → penyaluran (sekali) → rekap agregat.
 * Data penerima hanya untuk petugas berwenang (bansos.*). Jangan mencatat data kesehatan/ekonomi rinci.
 */
class Sosial extends Ops_Controller
{
    /** Pesan sukses khusus dari dalam transaksi (mis. jumlah KK yang diusulkan massal). */
    private $flash_msg = '';
    public static $program_labels = array('draft' => 'Draf', 'aktif' => 'Aktif', 'selesai' => 'Selesai');
    public static $program_tones = array('draft' => 'muted', 'aktif' => 'info', 'selesai' => 'ok');
    public static $labels = array('usulan' => 'Diusulkan', 'layak' => 'Layak (menunggu persetujuan)', 'tidak_layak' => 'Tidak Layak', 'disetujui' => 'Disetujui', 'ditolak' => 'Ditolak', 'disalurkan' => 'Sudah Disalurkan');
    public static $tones = array('usulan' => 'pending', 'layak' => 'info', 'tidak_layak' => 'bad', 'disetujui' => 'purple', 'ditolak' => 'bad', 'disalurkan' => 'ok');
    public static $aid_types = array('Sembako', 'Uang Tunai', 'Kesehatan', 'Pendidikan', 'Pembangunan / Bedah Rumah', 'Santunan', 'Lainnya');
    public static $months = array('Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember');

    /** "desember 2026" → array('Desember', '2026'); tidak dikenali → array('', ''). */
    public static function parse_period($period)
    {
        if (preg_match('/^\s*([a-z]+)\s+(\d{4})\s*$/i', (string) $period, $m)) {
            foreach (self::$months as $name) if (strcasecmp($name, $m[1]) === 0) return array($name, $m[2]);
        }
        return array('', '');
    }

    /** Alamat lengkap dari data master KK (kk_families.fields_json). */
    public static function family_address(array $f)
    {
        $parts = array(trim((string) ($f['alamat'] ?? '')));
        $rt = trim((string) ($f['rt'] ?? '')); $rw = trim((string) ($f['rw'] ?? ''));
        if ($rt !== '' || $rw !== '') $parts[] = trim(($rt !== '' ? 'RT ' . $rt : '') . ($rw !== '' ? '/RW ' . $rw : ''), '/');
        foreach (array('kelurahan' => 'Kel. ', 'kecamatan' => 'Kec. ', 'kota' => '', 'kode_pos' => '') as $k => $prefix) {
            $val = trim((string) ($f[$k] ?? '')); if ($val !== '') $parts[] = $prefix . $val;
        }
        return mb_substr(implode(', ', array_filter($parts, 'strlen')), 0, 255);
    }

    public function index()
    {
        $this->guard_admin('bansos.read');
        $tab = $this->choice('tab', array('program' => 1, 'calon' => 1, 'salur' => 1, 'laporan' => 1), 'program', 'get');
        $q = $this->str('q', 80, 'get');
        $period = $this->str('periode', 40, 'get');
        $periods = array_column($this->db->select('DISTINCT period', FALSE)->where("period <> ''", NULL, FALSE)->order_by('period', 'DESC')->get('aid_programs')->result_array(), 'period');
        if ($period !== '' && !in_array($period, $periods, TRUE)) $period = '';
        // Calon & antrean penyaluran hanya dari program yang masih berjalan (Draf/Aktif); program Selesai = arsip.
        $stats = $this->db->query("SELECT (SELECT COUNT(*) FROM aid_programs) programs, (SELECT COUNT(*) FROM aid_programs WHERE status='aktif') active,
            (SELECT COUNT(*) FROM aid_recipients r JOIN aid_programs p ON p.id=r.program_id WHERE r.status IN ('usulan','layak') AND p.status<>'selesai') candidates,
            (SELECT COUNT(*) FROM aid_recipients r JOIN aid_programs p ON p.id=r.program_id WHERE r.status='disetujui' AND p.status<>'selesai') approved,
            (SELECT COUNT(*) FROM aid_recipients WHERE status='disalurkan') distributed")->row_array();
        $archive = $this->input->get('arsip') === '1';
        $per_page = 10; $data = array('tab' => $tab, 'q' => $q, 'period' => $period, 'periods' => $periods, 'stats' => $stats, 'archive' => $archive,
            'labels' => self::$program_labels, 'tones' => self::$program_tones, 'r_labels' => self::$labels, 'r_tones' => self::$tones);

        if ($tab === 'calon' || $tab === 'salur') {
            // Daftar penerima lintas program: hanya nama & alamat ringkas (tanpa catatan usulan/keputusan).
            $status = $this->choice('status', $tab === 'salur' ? array('disetujui' => 1, 'disalurkan' => 1) : array('usulan' => 1, 'layak' => 1, 'tidak_layak' => 1, 'ditolak' => 1), '', 'get');
            $apply = function () use ($tab, $status, $q, $period, $archive) {
                $this->db->from('aid_recipients r')->join('aid_programs p', 'p.id=r.program_id');
                if ($status !== '') $this->db->where('r.status', $status);
                else $this->db->where_in('r.status', $tab === 'salur' ? array('disetujui', 'disalurkan') : array('usulan', 'layak', 'tidak_layak', 'ditolak'));
                // Tanpa ?arsip=1: sembunyikan program Selesai. Riwayat "sudah disalurkan" tetap tampil di tab Penyaluran.
                if (!$archive) {
                    if ($tab === 'salur') $this->db->group_start()->where('r.status', 'disalurkan')->or_where('p.status <>', 'selesai')->group_end();
                    else $this->db->where('p.status <>', 'selesai');
                }
                if ($period !== '') $this->db->where('p.period', $period);
                if ($q !== '') $this->db->group_start()->like('r.recipient_name', $q)->or_like('p.name', $q)->group_end();
            };
            $apply(); $matched = (int) $this->db->count_all_results();
            $pages = max(1, (int) ceil($matched / $per_page)); $page = min($pages, max(1, (int) $this->input->get('hal')));
            $apply();
            $data['rows'] = $this->db->select('r.id,r.program_id,r.recipient_name,r.address,r.status,r.amount,r.created_at,r.distributed_at,p.name program_name,p.period,p.aid_type,p.status program_status')
                ->order_by($tab === 'salur' ? "r.status='disetujui'" : "r.status='usulan'", 'DESC', FALSE)->order_by('r.id', 'DESC')->limit($per_page, ($page - 1) * $per_page)->get()->result_array();
            $data += array('status' => $status, 'matched' => $matched, 'page' => $page, 'pages' => $pages);
        } else {
            $status = $this->choice('status', self::$program_labels, '', 'get');
            $apply = function () use ($status, $q, $period) {
                $this->db->from('aid_programs p');
                if ($status !== '') $this->db->where('p.status', $status);
                if ($period !== '') $this->db->where('p.period', $period);
                if ($q !== '') $this->db->group_start()->like('p.name', $q)->or_like('p.aid_type', $q)->or_like('p.source', $q)->or_like('p.criteria', $q)->group_end();
            };
            $apply(); $matched = (int) $this->db->count_all_results();
            $limit = $tab === 'laporan' ? 500 : $per_page;
            $pages = max(1, (int) ceil($matched / $limit)); $page = min($pages, max(1, (int) $this->input->get('hal')));
            $apply();
            $data['rows'] = $this->db->select("p.*, (SELECT COUNT(*) FROM aid_recipients r WHERE r.program_id=p.id) total,
                (SELECT COUNT(*) FROM aid_recipients r WHERE r.program_id=p.id AND r.status IN ('usulan','layak')) pending,
                (SELECT COUNT(*) FROM aid_recipients r WHERE r.program_id=p.id AND r.status='disetujui') approved,
                (SELECT COUNT(*) FROM aid_recipients r WHERE r.program_id=p.id AND r.status='disalurkan') distributed,
                (SELECT COUNT(*) FROM aid_recipients r WHERE r.program_id=p.id AND r.status <> 'usulan') processed,
                (SELECT COALESCE(SUM(amount),0) FROM aid_recipients r WHERE r.program_id=p.id AND r.status='disalurkan') distributed_value", FALSE)
                ->order_by("FIELD(p.status,'aktif','draft','selesai')", '', FALSE)->order_by('p.id', 'DESC')->limit($limit, ($page - 1) * $limit)->get()->result_array();
            $data += array('status' => $status, 'matched' => $matched, 'page' => $page, 'pages' => $pages);
        }
        $this->render('bansos_index', $data);
    }

    /** Ekspor rekap agregat per program (tanpa identitas penerima). ?format=xlsx|xls */
    public function ekspor()
    {
        $this->guard_admin('bansos.read');
        $format = $this->choice('format', array('xlsx' => 1, 'xls' => 1), 'xlsx', 'get');
        $rows = $this->db->select("p.name,p.period,p.aid_type,p.source,p.quota,p.status, (SELECT COUNT(*) FROM aid_recipients r WHERE r.program_id=p.id) total,
            (SELECT COUNT(*) FROM aid_recipients r WHERE r.program_id=p.id AND r.status IN ('usulan','layak')) pending,
            (SELECT COUNT(*) FROM aid_recipients r WHERE r.program_id=p.id AND r.status='disetujui') approved,
            (SELECT COUNT(*) FROM aid_recipients r WHERE r.program_id=p.id AND r.status='disalurkan') distributed,
            (SELECT COALESCE(SUM(amount),0) FROM aid_recipients r WHERE r.program_id=p.id AND r.status='disalurkan') distributed_value", FALSE)
            ->from('aid_programs p')->order_by('p.id', 'DESC')->get()->result_array();
        $this->audit('export', 'aid_programs', count($rows) . ' program (agregat, ' . $format . ')');
        $headers = array('Program', 'Periode', 'Jenis', 'Sumber', 'Kuota', 'Status', 'Calon/penerima tercatat', 'Menunggu verifikasi', 'Disetujui belum disalurkan', 'Sudah disalurkan', 'Nilai tersalur (Rp)');
        $cells = array();
        foreach ($rows as $r) {
            $cells[] = array((string) $r['name'], (string) $r['period'], (string) $r['aid_type'], (string) $r['source'], (int) $r['quota'], self::$program_labels[$r['status']] ?? (string) $r['status'], (int) $r['total'], (int) $r['pending'], (int) $r['approved'], (int) $r['distributed'], (float) $r['distributed_value']);
        }
        $this->load->library('spreadsheet_export');
        $this->spreadsheet_export->send($format, 'rekap-bansos-' . date('Ymd'), 'Rekap Bansos', $headers, $cells);
    }

    public function form($id = 0)
    {
        $this->guard_admin('bansos.write');
        $id = (int) $id;
        $item = $id ? $this->db->get_where('aid_programs', array('id' => $id))->row_array() : NULL;
        if ($id && !$item) show_404();
        $error = '';
        if ($this->input->method(TRUE) === 'POST') {
            // Jenis bantuan dari dropdown; "Lainnya" memakai isian bebas bila diisi.
            $type = $this->str('aid_type', 60);
            if (!in_array($type, self::$aid_types, TRUE)) $type = '';
            if ($type === 'Lainnya' && ($other = $this->str('aid_type_other', 60)) !== '') $type = $other;
            // Periode dikirim dari satu dropdown sebagai "Bulan|Tahun" dan disimpan sebagai "Desember 2026".
            $period_value = $this->str('period_value', 30);
            $period_parts = explode('|', $period_value, 2);
            $month = $period_parts[0] ?? $this->str('period_month', 20);
            $year = (int) ($period_parts[1] ?? $this->input->post('period_year'));
            $period = in_array($month, self::$months, TRUE) && $year >= 2000 && $year <= 2100 ? $month . ' ' . $year : '';
            $source = $this->str('source', 120);
            if ($source === 'Lainnya') $source = $this->str('source_other', 120);
            $data = array('name' => $this->str('name', 160), 'period' => $period, 'aid_type' => $type, 'unit_value' => $this->money('unit_value'),
                'source' => $source, 'criteria' => $this->text('criteria', 1000), 'quota' => max(0, (int) $this->input->post('quota')), 'updated_at' => date('Y-m-d H:i:s'));
            if (mb_strlen($data['name']) < 3 || $data['aid_type'] === '' || $data['period'] === '' || $data['source'] === '') $error = 'Nama program, jenis bantuan, periode, dan sumber dana/barang wajib diisi.';
            if (!$error) {
                if ($id) {
                    $this->db->where('id', $id)->update('aid_programs', $data);
                    // Nominal calon yang belum disalurkan ikut nilai per penerima terbaru (yang sudah disalurkan = catatan keuangan, tidak diubah).
                    $this->db->where('program_id', $id)->where('status <>', 'disalurkan')->update('aid_recipients', array('amount' => $data['unit_value']));
                }
                else {$this->db->insert('aid_programs', $data + array('status' => 'draft', 'created_by' => $this->admin_id)); $id = (int) $this->db->insert_id();}
                $this->audit($item ? 'update' : 'create', 'aid_program:' . $id);
                $this->back('admin/bansos/' . $id, 'Program bantuan disimpan.');
            }
            $item = array_merge($item ?: array(), $data);
        }
        $this->render('bansos_form', array('item' => $item, 'error' => $error));
    }

    public function detail($id = 0)
    {
        $this->guard_admin('bansos.read');
        $id = (int) $id;
        $program = $this->db->get_where('aid_programs', array('id' => $id))->row_array();
        if (!$program) show_404();
        if ($this->input->method(TRUE) === 'POST') {
            $act = $this->str('act', 20);
            $this->load->library('Private_storage');
            $upload = NULL;
            try {
                if ($act === 'distribute') {
                    $err = '';
                    $upload = $this->private_storage->store('proof', 'aid', $err, array('jpg', 'png', 'webp', 'pdf'));
                    if ($err) throw new RuntimeException($err);
                }
                $this->atomic(function () use ($id, $act, $upload) {
                    $p = $this->lock('aid_programs', $id);
                    if (!$p) throw new RuntimeException('Program bantuan tidak ditemukan.');
                    if ($act === 'delete') {
                        $this->need('bansos.approve');
                        // Program yang ditutup atau sudah memasuki proses verifikasi/persetujuan/penyaluran
                        // merupakan riwayat layanan dan tidak boleh dihapus, termasuk lewat POST langsung.
                        $processed = (int) $this->db->where('program_id', $id)->where('status <>', 'usulan')->count_all_results('aid_recipients');
                        if ($p['status'] === 'selesai' || $processed > 0) throw new RuntimeException('Program tidak dapat dihapus karena sudah selesai atau memiliki penerima yang telah diproses.');
                        $rids = array_map('intval', array_column($this->db->select('id')->where('program_id', $id)->get('aid_recipients')->result_array(), 'id'));
                        if ($rids) {
                            if ($this->db->table_exists('citizen_notice_reads')) $this->db->where('kind', 'bs')->where_in('ref_id', $rids)->delete('citizen_notice_reads');
                            $this->db->where('program_id', $id)->delete('aid_recipients');
                        }
                        $this->db->where('id', $id)->delete('aid_programs');
                        if (!$this->db->affected_rows()) throw new RuntimeException('Program bantuan gagal dihapus.');
                        $this->audit('delete', 'aid_program:' . $id, $p['name'] . ($rids ? ' (+' . count($rids) . ' calon penerima)' : ''));
                        return;
                    }
                    if ($act === 'program_status') {
                        $this->need('bansos.approve');
                        $to = $this->choice('to', self::$program_labels);
                        $flow = array('draft' => array('aktif'), 'aktif' => array('selesai'));
                        if (!in_array($to, $flow[$p['status']] ?? array(), TRUE)) throw new RuntimeException('Perubahan status program tidak diizinkan.');
                        $this->db->where('id', $id)->update('aid_programs', array('status' => $to, 'updated_at' => date('Y-m-d H:i:s')));
                        $this->audit('status', 'aid_program:' . $id, $to);
                        return;
                    }
                    if ($p['status'] === 'selesai') throw new RuntimeException('Program sudah selesai.');
                    if ($act === 'add' && $this->input->post('family_id') === 'all') {
                        // Usulkan seluruh warga sekaligus: satu calon per KK di data master + akun warga aktif yang belum punya data KK.
                        $this->need('bansos.write');
                        $existing = $this->db->select('recipient_key,citizen_id')->where('program_id', $id)->get('aid_recipients')->result_array();
                        $taken = array_flip(array_column($existing, 'recipient_key'));
                        $taken_cit = array_flip(array_filter(array_map('intval', array_column($existing, 'citizen_id'))));
                        $note = $this->str('proposal_note', 500);
                        $added_kk = 0; $added_acc = 0;
                        foreach ($this->family_master() as $fid => $f) {
                            $key = 'f:' . $fid;
                            if (isset($taken[$key]) || ($f['citizen_id'] && isset($taken_cit[$f['citizen_id']])) || mb_strlen($f['name']) < 2) continue;
                            $this->db->insert('aid_recipients', array('program_id' => $id, 'recipient_key' => $key, 'citizen_id' => $f['citizen_id'], 'family_id' => $fid,
                                'recipient_name' => $f['name'], 'address' => $f['address'], 'proposal_note' => $note, 'amount' => $p['unit_value'], 'created_by' => $this->admin_id));
                            if ($f['citizen_id']) $taken_cit[$f['citizen_id']] = 1;
                            $added_kk++;
                        }
                        foreach ($this->citizen_master() as $cid => $c) {
                            $key = 'c:' . $cid;
                            if (isset($taken[$key]) || isset($taken_cit[$cid]) || mb_strlen($c['name']) < 2) continue;
                            $this->db->insert('aid_recipients', array('program_id' => $id, 'recipient_key' => $key, 'citizen_id' => $cid, 'family_id' => NULL,
                                'recipient_name' => $c['name'], 'address' => $c['address'], 'proposal_note' => $note, 'amount' => $p['unit_value'], 'created_by' => $this->admin_id));
                            $added_acc++;
                        }
                        $added = $added_kk + $added_acc;
                        if (!$added) throw new RuntimeException('Semua warga (data KK & akun warga aktif) sudah terdaftar pada program ini.');
                        $this->audit('add_all', 'aid_program:' . $id, $added_kk . ' KK + ' . $added_acc . ' akun warga diusulkan');
                        $this->flash_msg = $added . ' warga berhasil diusulkan sebagai calon penerima (' . $added_kk . ' dari data KK, ' . $added_acc . ' akun warga tanpa data KK).';
                        return;
                    }
                    if ($act === 'add') {
                        $this->need('bansos.write');
                        $pick = (string) $this->input->post('family_id');
                        $family_id = ctype_digit($pick) ? (int) $pick : 0;
                        $name = $this->str('recipient_name', 140);
                        $address = $this->str('address', 255);
                        $citizen_id = NULL;
                        if (preg_match('/^c:(\d+)$/', $pick, $mm)) {
                            // Akun warga aktif yang belum punya data KK: nama & alamat dari profil akun.
                            $acc = $this->citizen_master();
                            $c = $acc[(int) $mm[1]] ?? NULL;
                            if (!$c) throw new RuntimeException('Akun warga tidak ditemukan atau sudah memiliki data KK.');
                            $citizen_id = (int) $mm[1]; $name = $c['name']; $address = $c['address'] ?: $address;
                            $key = 'c:' . $citizen_id;
                            if ($this->db->where('program_id', $id)->where('citizen_id', $citizen_id)->count_all_results('aid_recipients')) throw new RuntimeException('Warga ini sudah terdaftar pada program.');
                        } elseif ($family_id) {
                            $f = $this->Ops_model->family_by_id($family_id);
                            if (!$f) throw new RuntimeException('Keluarga tidak ditemukan.');
                            foreach ($f['members'] as $m) if ($m['status'] === 'aktif' && ($m['fields']['hubungan_keluarga'] ?? '') === 'Kepala Keluarga') $name = $name ?: ($m['fields']['nama_lengkap'] ?? '');
                            // Alamat selalu dari data master KK agar konsisten (bukan ketikan bebas).
                            $address = $this->family_domicile($family_id, self::family_address($f['fields'] ?? array())) ?: $address;
                            $citizen_id = (int) $f['owner_citizen_id'] ?: NULL;
                            $key = 'f:' . $family_id;
                        } else {
                            $key = 'n:' . md5(mb_strtolower(trim($name)) . '|' . mb_strtolower(trim($address)));
                        }
                        if (mb_strlen($name) < 2) throw new RuntimeException('Pilih keluarga atau isi nama calon penerima.');
                        if ($this->db->get_where('aid_recipients', array('program_id' => $id, 'recipient_key' => $key))->row_array()) throw new RuntimeException('Calon penerima ini sudah terdaftar pada program.');
                        $this->db->insert('aid_recipients', array('program_id' => $id, 'recipient_key' => $key, 'citizen_id' => $citizen_id, 'family_id' => $family_id ?: NULL,
                            'recipient_name' => $name, 'address' => $address, 'proposal_note' => $this->str('proposal_note', 500), 'amount' => $p['unit_value'], 'created_by' => $this->admin_id));
                        $this->audit('add', 'aid_recipient:' . $this->db->insert_id(), 'program ' . $id);
                        return;
                    }
                    $rid = (int) $this->input->post('recipient_id');
                    $r = $this->db->query('SELECT * FROM aid_recipients WHERE id=? AND program_id=? FOR UPDATE', array($rid, $id))->row_array();
                    if (!$r) throw new RuntimeException('Calon penerima tidak ditemukan.');
                    $note = $this->str('note', 500);
                    $now = date('Y-m-d H:i:s');
                    if ($act === 'verify') {
                        $this->need('bansos.write');
                        if ($r['status'] !== 'usulan') throw new RuntimeException('Verifikasi hanya untuk calon berstatus diusulkan.');
                        $to = $this->choice('result', array('layak' => 1, 'tidak_layak' => 1));
                        if (!$to) throw new RuntimeException('Pilih hasil verifikasi.');
                        if ($to === 'tidak_layak' && mb_strlen($note) < 3) throw new RuntimeException('Tuliskan alasan tidak layak.');
                        $set = array('status' => $to, 'verified_by' => $this->admin_id, 'verified_at' => $now, 'decision_note' => $note);
                    } elseif ($act === 'approve' || $act === 'reject') {
                        $this->need('bansos.approve');
                        if ($r['status'] !== 'layak') throw new RuntimeException('Persetujuan hanya untuk calon yang sudah diverifikasi layak.');
                        if ($p['status'] !== 'aktif') throw new RuntimeException('Aktifkan program sebelum menyetujui penerima.');
                        if ($act === 'reject' && mb_strlen($note) < 3) throw new RuntimeException('Penolakan wajib menyimpan alasan.');
                        if ($act === 'approve' && (int) $p['quota'] > 0) {
                            $used = (int) $this->db->where('program_id', $id)->where_in('status', array('disetujui', 'disalurkan'))->count_all_results('aid_recipients');
                            if ($used >= (int) $p['quota']) throw new RuntimeException('Kuota program (' . (int) $p['quota'] . ') sudah terpenuhi.');
                        }
                        $set = array('status' => $act === 'approve' ? 'disetujui' : 'ditolak', 'approved_by' => $this->admin_id, 'approved_at' => $now, 'decision_note' => $note ?: $r['decision_note']);
                    } elseif ($act === 'distribute') {
                        $this->need('bansos.write');
                        // Status dicek di bawah kunci baris: satu alokasi tidak bisa disalurkan dua kali.
                        if ($r['status'] !== 'disetujui') throw new RuntimeException($r['status'] === 'disalurkan' ? 'Bantuan untuk penerima ini sudah tercatat disalurkan.' : 'Penyaluran hanya untuk penerima yang disetujui.');
                        $set = array('status' => 'disalurkan', 'distributed_at' => $now, 'distributed_by' => $this->admin_id, 'decision_note' => $note ?: $r['decision_note']);
                        if ($upload) $set['proof'] = $upload['stored_name'];
                    } else {
                        throw new RuntimeException('Aksi tidak dikenal.');
                    }
                    $this->db->where('id', $rid)->update('aid_recipients', $set);
                    $this->audit($act, 'aid_recipient:' . $rid, $set['status']);
                });
                if ($act === 'delete') $this->back('admin/bansos', 'Program bantuan berhasil dihapus.');
                $this->back('admin/bansos/' . $id, $this->flash_msg ?: 'Data bantuan diperbarui.');
            } catch (RuntimeException $e) {
                if ($upload) $this->private_storage->delete('aid', $upload['stored_name']);
                $this->back($act === 'delete' && $this->input->post('from') === 'index' ? 'admin/bansos' : 'admin/bansos/' . $id, '', $e->getMessage());
            }
        }
        $recipients = $this->db->order_by("FIELD(status,'usulan','layak','disetujui','disalurkan','tidak_layak','ditolak')", '', FALSE)->order_by('recipient_name')->get_where('aid_recipients', array('program_id' => $id))->result_array();
        $summary = array_fill_keys(array_keys(self::$labels), 0);
        $value = 0;
        foreach ($recipients as $r) {$summary[$r['status']]++; if ($r['status'] === 'disalurkan') $value += (float) $r['amount'];}
        $families = $this->family_master();
        // Tandai keluarga yang sudah terdaftar di program ini (tidak bisa diusulkan dua kali).
        foreach ($recipients as $r) if ($r['family_id'] && isset($families[(int) $r['family_id']])) $families[(int) $r['family_id']]['registered'] = TRUE;
        $accounts = $this->citizen_master();
        foreach ($recipients as $r) if ($r['citizen_id'] && isset($accounts[(int) $r['citizen_id']])) $accounts[(int) $r['citizen_id']]['registered'] = TRUE;
        $news = $this->db->table_exists('posts') ? $this->db->select('id,type,title,event_date,event_time,created_at')->where_in('type', array('pengumuman', 'agenda'))
            ->where('status', 'published')->order_by('created_at', 'DESC')->limit(3)->get('posts')->result_array() : array();
        $this->audit('view', 'aid_program:' . $id);
        $this->render('bansos_detail', array('program' => $program, 'recipients' => $recipients, 'summary' => $summary, 'value' => $value, 'families' => $families, 'accounts' => $accounts, 'news' => $news,
            'labels' => self::$labels, 'tones' => self::$tones, 'program_labels' => self::$program_labels, 'program_tones' => self::$program_tones));
    }

    /** Data master keluarga: nama Kepala Keluarga, alamat lengkap, dan akun warga pemilik KK. */
    private function family_master()
    {
        $heads = array();
        foreach ($this->db->select('family_id,fields_json')->where('status', 'aktif')->get('kk_members')->result_array() as $m) {
            $mf = json_decode($m['fields_json'], TRUE) ?: array();
            $fid = (int) $m['family_id'];
            // Kepala Keluarga diutamakan; anggota pertama hanya sebagai cadangan.
            if (($mf['hubungan_keluarga'] ?? '') === 'Kepala Keluarga' || !isset($heads[$fid])) $heads[$fid] = (string) ($mf['nama_lengkap'] ?? '');
        }
        $domiciles = array();
        if ($this->db->table_exists('rt_family_residences') && $this->db->table_exists('rt_houses'))
            foreach ($this->db->select('r.family_id,h.house_number,h.address_text')->from('rt_family_residences r')->join('rt_houses h','h.id=r.house_id')
                ->where('r.end_date IS NULL',NULL,FALSE)->where('r.verification_status','verified')->get()->result_array() as $d)
                $domiciles[(int)$d['family_id']] = trim(($d['house_number'] ? 'No. '.$d['house_number'].', ' : '').$d['address_text']);
        $families = array();
        foreach ($this->db->select('f.id,f.nomor_kk,f.fields_json,f.owner_citizen_id,c.display_name')->from('kk_families f')->join('citizens c', 'c.id=f.owner_citizen_id', 'left')->order_by('c.display_name')->get()->result_array() as $f) {
            $fields = json_decode($f['fields_json'], TRUE) ?: array();
            $head = $heads[(int) $f['id']] ?? '';
            $families[(int) $f['id']] = array('label' => ($head ?: ($f['display_name'] ?: '-')) . ' — KK ' . rt_mask_id($f['nomor_kk']),
                'name' => $head ?: (string) $f['display_name'], 'address' => $domiciles[(int)$f['id']] ?? self::family_address($fields), 'citizen_id' => (int) $f['owner_citizen_id'] ?: NULL, 'registered' => FALSE);
        }
        return $families;
    }

    private function family_domicile($family_id, $fallback = '')
    {
        if (!$this->db->table_exists('rt_family_residences') || !$this->db->table_exists('rt_houses')) return $fallback;
        $r = $this->db->select('h.house_number,h.address_text')->from('rt_family_residences r')->join('rt_houses h','h.id=r.house_id')
            ->where('r.family_id',(int)$family_id)->where('r.end_date IS NULL',NULL,FALSE)->where('r.verification_status','verified')->order_by('r.id','DESC')->get()->row_array();
        return $r ? trim(($r['house_number'] ? 'No. '.$r['house_number'].', ' : '').$r['address_text']) : $fallback;
    }

    /** Akun warga aktif yang BELUM punya data KK di master (agar "Seluruh Warga" benar-benar mencakup semua warga). */
    private function citizen_master()
    {
        $rows = $this->db->query("SELECT c.id,c.display_name,c.full_name,c.address FROM citizens c
            WHERE c.is_active=1 AND NOT EXISTS (SELECT 1 FROM kk_families f WHERE f.owner_citizen_id=c.id) ORDER BY COALESCE(NULLIF(c.full_name,''),c.display_name)")->result_array();
        $out = array();
        foreach ($rows as $r) {
            $name = trim((string) ($r['full_name'] ?: $r['display_name']));
            $out[(int) $r['id']] = array('name' => $name, 'address' => mb_substr(trim((string) $r['address']), 0, 255), 'registered' => FALSE);
        }
        return $out;
    }

    public function bukti($rid = 0)
    {
        $this->guard_admin('bansos.read');
        $r = $this->db->get_where('aid_recipients', array('id' => (int) $rid))->row_array();
        if (!$r || !$r['proof']) show_404();
        $this->audit('download', 'aid_proof:' . (int) $rid);
        $this->load->library('Private_storage');
        $this->private_storage->send('aid', $r['proof'], 'bukti-bansos-' . (int) $rid);
    }
}
