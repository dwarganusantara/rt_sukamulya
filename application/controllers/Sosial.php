<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Modul 13 — Bantuan Sosial.
 * Program → calon penerima → verifikasi kelayakan → persetujuan → penyaluran (sekali) → rekap agregat.
 * Data penerima hanya untuk petugas berwenang (bansos.*). Jangan mencatat data kesehatan/ekonomi rinci.
 */
class Sosial extends Ops_Controller
{
    public static $program_labels = array('draft' => 'Draf', 'aktif' => 'Aktif', 'selesai' => 'Selesai');
    public static $program_tones = array('draft' => 'muted', 'aktif' => 'info', 'selesai' => 'ok');
    public static $labels = array('usulan' => 'Diusulkan', 'layak' => 'Layak (menunggu persetujuan)', 'tidak_layak' => 'Tidak Layak', 'disetujui' => 'Disetujui', 'ditolak' => 'Ditolak', 'disalurkan' => 'Sudah Disalurkan');
    public static $tones = array('usulan' => 'pending', 'layak' => 'info', 'tidak_layak' => 'bad', 'disetujui' => 'purple', 'ditolak' => 'bad', 'disalurkan' => 'ok');

    public function index()
    {
        $this->guard_admin('bansos.read');
        $tab = $this->choice('tab', array('program' => 1, 'calon' => 1, 'salur' => 1, 'laporan' => 1), 'program', 'get');
        $q = $this->str('q', 80, 'get');
        $period = $this->str('periode', 40, 'get');
        $periods = array_column($this->db->select('DISTINCT period', FALSE)->where("period <> ''", NULL, FALSE)->order_by('period', 'DESC')->get('aid_programs')->result_array(), 'period');
        if ($period !== '' && !in_array($period, $periods, TRUE)) $period = '';
        $stats = $this->db->query("SELECT (SELECT COUNT(*) FROM aid_programs) programs, (SELECT COUNT(*) FROM aid_programs WHERE status='aktif') active,
            (SELECT COUNT(*) FROM aid_recipients WHERE status IN ('usulan','layak')) candidates, (SELECT COUNT(*) FROM aid_recipients WHERE status='disetujui') approved,
            (SELECT COUNT(*) FROM aid_recipients WHERE status='disalurkan') distributed")->row_array();
        $per_page = 10; $data = array('tab' => $tab, 'q' => $q, 'period' => $period, 'periods' => $periods, 'stats' => $stats,
            'labels' => self::$program_labels, 'tones' => self::$program_tones, 'r_labels' => self::$labels, 'r_tones' => self::$tones);

        if ($tab === 'calon' || $tab === 'salur') {
            // Daftar penerima lintas program: hanya nama & alamat ringkas (tanpa catatan usulan/keputusan).
            $status = $this->choice('status', $tab === 'salur' ? array('disetujui' => 1, 'disalurkan' => 1) : array('usulan' => 1, 'layak' => 1, 'tidak_layak' => 1, 'ditolak' => 1), '', 'get');
            $apply = function () use ($tab, $status, $q, $period) {
                $this->db->from('aid_recipients r')->join('aid_programs p', 'p.id=r.program_id');
                if ($status !== '') $this->db->where('r.status', $status);
                else $this->db->where_in('r.status', $tab === 'salur' ? array('disetujui', 'disalurkan') : array('usulan', 'layak', 'tidak_layak', 'ditolak'));
                if ($period !== '') $this->db->where('p.period', $period);
                if ($q !== '') $this->db->group_start()->like('r.recipient_name', $q)->or_like('p.name', $q)->group_end();
            };
            $apply(); $matched = (int) $this->db->count_all_results();
            $pages = max(1, (int) ceil($matched / $per_page)); $page = min($pages, max(1, (int) $this->input->get('hal')));
            $apply();
            $data['rows'] = $this->db->select('r.id,r.program_id,r.recipient_name,r.address,r.status,r.amount,r.created_at,r.distributed_at,p.name program_name,p.period,p.aid_type')
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
                (SELECT COALESCE(SUM(amount),0) FROM aid_recipients r WHERE r.program_id=p.id AND r.status='disalurkan') distributed_value", FALSE)
                ->order_by("FIELD(p.status,'aktif','draft','selesai')", '', FALSE)->order_by('p.id', 'DESC')->limit($limit, ($page - 1) * $limit)->get()->result_array();
            $data += array('status' => $status, 'matched' => $matched, 'page' => $page, 'pages' => $pages);
        }
        $this->render('bansos_index', $data);
    }

    /** Ekspor rekap agregat per program (tanpa identitas penerima). */
    public function ekspor()
    {
        $this->guard_admin('bansos.read');
        $rows = $this->db->select("p.name,p.period,p.aid_type,p.source,p.quota,p.status, (SELECT COUNT(*) FROM aid_recipients r WHERE r.program_id=p.id) total,
            (SELECT COUNT(*) FROM aid_recipients r WHERE r.program_id=p.id AND r.status IN ('usulan','layak')) pending,
            (SELECT COUNT(*) FROM aid_recipients r WHERE r.program_id=p.id AND r.status='disetujui') approved,
            (SELECT COUNT(*) FROM aid_recipients r WHERE r.program_id=p.id AND r.status='disalurkan') distributed,
            (SELECT COALESCE(SUM(amount),0) FROM aid_recipients r WHERE r.program_id=p.id AND r.status='disalurkan') distributed_value", FALSE)
            ->from('aid_programs p')->order_by('p.id', 'DESC')->get()->result_array();
        $this->audit('export', 'aid_programs', count($rows) . ' program (agregat)');
        $out = fopen('php://temp', 'w+');
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, array('Program', 'Periode', 'Jenis', 'Sumber', 'Kuota', 'Status', 'Calon/penerima tercatat', 'Menunggu verifikasi', 'Disetujui belum disalurkan', 'Sudah disalurkan', 'Nilai tersalur (Rp)'));
        foreach ($rows as $r) {
            $cells = array($r['name'], $r['period'], $r['aid_type'], $r['source'], (int) $r['quota'], self::$program_labels[$r['status']] ?? $r['status'], (int) $r['total'], (int) $r['pending'], (int) $r['approved'], (int) $r['distributed'], (float) $r['distributed_value']);
            // Cegah formula injection di Excel.
            foreach ($cells as &$c) if (is_string($c) && $c !== '' && strpos('=+-@', $c[0]) !== FALSE) $c = "'" . $c;
            unset($c);
            fputcsv($out, $cells);
        }
        rewind($out); $csv = stream_get_contents($out); fclose($out);
        $this->output->set_header('Cache-Control: no-store, private')->set_content_type('text/csv', 'utf-8')
            ->set_header('Content-Disposition: attachment; filename="rekap-bansos-' . date('Ymd') . '.csv"')->set_output($csv);
    }

    public function form($id = 0)
    {
        $this->guard_admin('bansos.write');
        $id = (int) $id;
        $item = $id ? $this->db->get_where('aid_programs', array('id' => $id))->row_array() : NULL;
        if ($id && !$item) show_404();
        $error = '';
        if ($this->input->method(TRUE) === 'POST') {
            $data = array('name' => $this->str('name', 160), 'period' => $this->str('period', 40), 'aid_type' => $this->str('aid_type', 60), 'unit_value' => $this->money('unit_value'),
                'source' => $this->str('source', 120), 'criteria' => $this->text('criteria', 3000), 'quota' => max(0, (int) $this->input->post('quota')), 'updated_at' => date('Y-m-d H:i:s'));
            if (mb_strlen($data['name']) < 3 || $data['aid_type'] === '' || $data['period'] === '') $error = 'Nama program, jenis bantuan, dan periode wajib diisi.';
            if (!$error) {
                if ($id) $this->db->where('id', $id)->update('aid_programs', $data);
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
                    if ($act === 'add') {
                        $this->need('bansos.write');
                        $family_id = (int) $this->input->post('family_id');
                        $name = $this->str('recipient_name', 140);
                        $address = $this->str('address', 255);
                        $citizen_id = NULL;
                        if ($family_id) {
                            $f = $this->Ops_model->family_by_id($family_id);
                            if (!$f) throw new RuntimeException('Keluarga tidak ditemukan.');
                            foreach ($f['members'] as $m) if ($m['status'] === 'aktif' && ($m['fields']['hubungan_keluarga'] ?? '') === 'Kepala Keluarga') $name = $name ?: ($m['fields']['nama_lengkap'] ?? '');
                            $address = $address ?: ($f['fields']['alamat'] ?? '');
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
                $this->back('admin/bansos/' . $id, 'Data bantuan diperbarui.');
            } catch (RuntimeException $e) {
                if ($upload) $this->private_storage->delete('aid', $upload['stored_name']);
                $this->back('admin/bansos/' . $id, '', $e->getMessage());
            }
        }
        $recipients = $this->db->order_by("FIELD(status,'usulan','layak','disetujui','disalurkan','tidak_layak','ditolak')", '', FALSE)->order_by('recipient_name')->get_where('aid_recipients', array('program_id' => $id))->result_array();
        $summary = array_fill_keys(array_keys(self::$labels), 0);
        $value = 0;
        foreach ($recipients as $r) {$summary[$r['status']]++; if ($r['status'] === 'disalurkan') $value += (float) $r['amount'];}
        $families = array();
        foreach ($this->db->select('f.id,f.nomor_kk,f.fields_json,c.display_name')->from('kk_families f')->join('citizens c', 'c.id=f.owner_citizen_id', 'left')->order_by('c.display_name')->get()->result_array() as $f) {
            $fields = json_decode($f['fields_json'], TRUE) ?: array();
            $families[(int) $f['id']] = ($f['display_name'] ?: '-') . ' — KK ' . rt_mask_id($f['nomor_kk']) . ' — ' . mb_substr($fields['alamat'] ?? '', 0, 40);
        }
        $this->audit('view', 'aid_program:' . $id);
        $this->render('bansos_detail', array('program' => $program, 'recipients' => $recipients, 'summary' => $summary, 'value' => $value, 'families' => $families,
            'labels' => self::$labels, 'tones' => self::$tones, 'program_labels' => self::$program_labels, 'program_tones' => self::$program_tones));
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
