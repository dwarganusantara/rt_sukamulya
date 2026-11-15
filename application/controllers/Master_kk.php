<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Data Warga = Master Kartu Keluarga & Penduduk (Blueprint Master KK & Layanan Warga).
 *
 * - Satu sumber data keluarga untuk Bansos, Mutasi, Iuran, Surat; warga berakun maupun tidak.
 * - Dua jalur input: pengajuan warga (Verifikasi KK) dan input langsung admin (manual / OCR).
 * - Input admin disimpan sebagai pengajuan (draft → menunggu → terverifikasi). Tabel master
 *   (kk_families / kk_members) hanya berisi KK terverifikasi, sehingga modul lain tidak berubah.
 * - Status verifikasi KK terpisah dari status akun (belum ada / diundang / aktif / nonaktif).
 * - Akun tidak pernah dibuat otomatis: admin mengirim undangan aktivasi, warga membuat password sendiri.
 * - Nomor KK selalu dimasking di layar & ekspor; pencarian tidak menerima deretan angka panjang (NIK/KK
 *   tidak boleh masuk URL / log akses).
 */
class Master_kk extends Ops_Controller
{
    const INVITE_DAYS = 7;
    private $per_page = 20;

    public static $kk_labels = array('terverifikasi' => 'Terverifikasi', 'menunggu' => 'Menunggu', 'perlu_perbaikan' => 'Perlu Perbaikan', 'draft' => 'Draft');
    public static $account_labels = array('belum' => 'Belum Punya Akun', 'diundang' => 'Sudah Diundang', 'aktif' => 'Akun Aktif', 'nonaktif' => 'Akun Nonaktif');

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Kk_model');
        $this->load->model('House_model');
        $this->load->library('Kk_fields');
    }

    /* ---------- Halaman daftar ---------- */

    public function index()
    {
        $this->guard_admin('kk.read');
        $filters = $this->filters();
        $all = $this->rows();
        $stats = array('total' => count($all), 'verified' => 0, 'no_account' => 0, 'members' => 0, 'houses' => 0, 'unlinked' => 0,
            'pending' => $this->Kk_model->ready() ? (int) $this->db->where('status', 'pending')->count_all_results('kk_scan_requests') : 0,
            'citizen_pending' => 0);
        foreach ($all as $r) {
            if ($r['kk_status'] === 'terverifikasi') $stats['verified']++;
            if ($r['kk_status'] === 'terverifikasi' && in_array($r['account'], array('belum', 'diundang'), TRUE)) $stats['no_account']++;
            if ($r['kk_status'] === 'terverifikasi') $stats['members'] += (int) $r['members'];
            if ($r['kk_status'] === 'terverifikasi' && empty($r['house_id'])) $stats['unlinked']++;
        }
        if ($this->House_model->ready()) $stats['houses'] = count($this->House_model->houses());
        if ($this->Kk_model->ready()) {
            $this->db->where('status', 'pending');
            if ($this->master_ready()) $this->db->where('citizen_id IS NOT NULL', NULL, FALSE);
            $stats['citizen_pending'] = (int) $this->db->count_all_results('kk_scan_requests');
        }

        $rows = array_values(array_filter($all, function ($r) use ($filters) {return $this->match($r, $filters);}));
        $total = count($rows);
        $pages = max(1, (int) ceil($total / $this->per_page));
        $page = min($pages, max(1, (int) $this->input->get('hal')));
        $selected = NULL;
        $detail_id = (int) $this->input->get('detail');
        foreach ($all as $r) if ($r['type'] === 'family' && (!$selected || $detail_id === (int) $r['id'])) {$selected = $r; if ($detail_id === (int) $r['id']) break;}
        $this->render('master_kk_index', array(
            'rows' => array_slice($rows, ($page - 1) * $this->per_page, $this->per_page), 'offset' => ($page - 1) * $this->per_page,
            'filters' => $filters, 'stats' => $stats, 'total' => $total, 'page' => $page, 'pages' => $pages,
            'kk_labels' => self::$kk_labels, 'account_labels' => self::$account_labels,
            'master_ready' => $this->master_ready(),
            'houses_ready' => $this->House_model->ready(), 'selected' => $selected,
            'invite' => $this->session->flashdata('kk_invite'),
            // Halaman Akun Warga & Verifikasi KK memakai guard peran lama (admin/operator/ketua).
            'can_accounts' => in_array($this->role, array('admin', 'operator', 'ketua'), TRUE),
            'can_review' => in_array($this->role, array('admin', 'operator', 'ketua'), TRUE),
        ));
    }

    /** Ekspor daftar sesuai filter (nomor KK dimasking; tanpa NIK). */
    public function ekspor()
    {
        $this->guard_admin('kk.read');
        $this->need('penduduk.export');
        $filters = $this->filters();
        $format = $this->choice('format', array('xlsx' => 1, 'xls' => 1), 'xlsx', 'get');
        $rows = array_values(array_filter($this->rows(), function ($r) use ($filters) {return $this->match($r, $filters);}));
        $this->audit('export', 'master_kk', 'baris=' . count($rows) . ' format=' . $format);
        $out = array();
        foreach ($rows as $i => $r) {
            $out[] = array($i + 1, rt_mask_id($r['nomor_kk']), (string) $r['head'], (string) ($r['house_number'] ?: 'Belum terhubung'), (string) $r['domicile_address'], (string) $r['alamat'],
                (int) $r['members'], (string) $r['occupancy_label'], self::$kk_labels[$r['kk_status']], self::$account_labels[$r['account']], $r['updated_at'] ? date('Y-m-d', strtotime($r['updated_at'])) : '');
        }
        $this->load->library('spreadsheet_export');
        $this->spreadsheet_export->send($format, 'master-kk-' . date('Ymd'), 'Master KK',
            array('No', 'No. KK (disamarkan)', 'Kepala Keluarga', 'Nomor Rumah', 'Alamat Domisili', 'Alamat pada KK', 'Jumlah Anggota', 'Status Hunian', 'Status Verifikasi', 'Status Akun', 'Diperbarui'), $out);
    }

    /* ---------- Tambah KK (manual / OCR) ---------- */

    public function baru()
    {
        $this->guard_admin('kk.create');
        if (!$this->master_ready()) $this->back('admin/keluarga', '', 'Master data belum siap. Jalankan integrated_residence_upgrade.sql setelah backup database.');
        $mode = $this->choice('mode', array('ocr' => 1, 'manual' => 1), 'manual', $this->input->method(TRUE) === 'POST' ? 'post' : 'get');
        $request_id = (int) ($this->input->method(TRUE) === 'POST' ? $this->input->post('draft') : $this->input->get('draft'));
        $fields = $this->kk_fields->normalize(array());
        $error = ''; $notice = ''; $dupes = NULL; $scanned = FALSE;

        if ($request_id) {
            $req = $this->db->get_where('kk_scan_requests', array('id' => $request_id))->row_array();
            if (!$req || $req['citizen_id'] !== NULL || !in_array($req['status'], array('draft', 'revision_requested'), TRUE)) show_404();
            if ($this->input->method(TRUE) === 'GET') {
                $fields = $this->kk_fields->normalize(json_decode($req['proposed_data_json'], TRUE) ?: array());
                $mode = $req['source'] === 'admin_ocr' ? 'ocr' : 'manual';
                if ($req['status'] === 'revision_requested' && $req['review_notes']) $notice = 'Catatan verifikator: ' . $req['review_notes'];
            }
        }

        if ($this->input->method(TRUE) === 'POST') {
            $action = $this->choice('action', array('scan' => 1, 'draft' => 1, 'submit' => 1, 'verify' => 1), 'submit');
            try {
                if ($action === 'scan') {
                    if (time() - (int) $this->session->userdata('kk_admin_scan_at') < 15) throw new RuntimeException('Tunggu 15 detik sebelum scan ulang.');
                    $this->session->set_userdata('kk_admin_scan_at', time());
                    $this->load->library('Kk_ocr');
                    $fields = $this->kk_ocr->scan($_FILES['document'] ?? NULL);
                    $scanned = TRUE; $mode = 'ocr';
                    $notice = 'Hasil OCR hanya membantu pengisian. Periksa setiap field (terutama yang ditandai kuning) dengan dokumen KK sebelum menyimpan.';
                    $this->audit('ocr_preview', 'master_kk', 'anggota=' . (int) $fields['jumlah_anggota']);
                } else {
                    $fields = $this->kk_fields->normalize(array('keluarga' => $this->input->post('keluarga'), 'anggota' => $this->input->post('anggota')));
                    $source = $mode === 'ocr' ? 'admin_ocr' : 'admin_manual';
                    if ($action === 'draft') {
                        $id = $this->Kk_model->admin_save($this->admin_id, $fields, 'draft', $source, $request_id);
                        $this->audit('kk_draft', 'kk_request:' . $id);
                        $this->back('admin/keluarga?status_kk=draft', 'Draft KK-' . str_pad($id, 6, '0', STR_PAD_LEFT) . ' disimpan. Lanjutkan kapan saja dari daftar Master KK.');
                    }
                    if ($action === 'verify') $this->need('kk.verify');
                    $errors = $this->kk_fields->validate($fields);
                    if (!$this->input->post('checked')) $errors[] = 'Centang konfirmasi bahwa data sudah dicocokkan dengan dokumen KK.';
                    $dupes = $this->Kk_model->duplicates($fields, $request_id);
                    if ($dupes['nik_conflicts']) $errors[] = count($dupes['nik_conflicts']) . ' NIK sudah terdaftar di KK lain. Pindahkan anggota tersebut lewat Mutasi Warga, bukan input ulang.';
                    if ($dupes['pending']) $errors[] = 'Nomor KK ini masih punya pengajuan yang menunggu verifikasi (KK-' . str_pad($dupes['pending']['id'], 6, '0', STR_PAD_LEFT) . '). Selesaikan pengajuan itu dulu.';
                    if ($dupes['family'] && !$this->input->post('update_existing')) $errors[] = 'Nomor KK sudah ada di Master KK. Centang "Perbarui data KK yang sudah ada" bila memang ingin memperbarui, atau periksa kembali nomornya.';
                    if ($errors) throw new RuntimeException(implode(' ', $errors));

                    $id = $this->Kk_model->admin_save($this->admin_id, $fields, 'pending', $source, $request_id);
                    if ($action === 'submit') {
                        $this->audit('kk_submit_admin', 'kk_request:' . $id, $dupes['family'] ? 'perbarui KK terdaftar' : 'KK baru');
                        $this->back('admin/keluarga?status_kk=menunggu', 'KK-' . str_pad($id, 6, '0', STR_PAD_LEFT) . ' dikirim untuk verifikasi.');
                    }
                    try {
                        $family_id = $this->Kk_model->review($id, $this->admin_id, 'approved', 'Diinput dan diverifikasi langsung oleh admin RT (' . ($source === 'admin_ocr' ? 'OCR dicek manual' : 'input manual') . ').');
                    } catch (Throwable $e) {
                        $this->back('admin/keluarga?status_kk=menunggu', '', 'Data tersimpan sebagai Menunggu Verifikasi, tetapi belum bisa diverifikasi: ' . $e->getMessage());
                    }
                    $this->audit('kk_approved', 'kk_request:' . $id, 'input admin, ' . ($dupes['family'] ? 'perbarui KK terdaftar' : 'KK baru'));
                    $this->back('admin/rumah/tautkan?keluarga=' . (int) $family_id, 'KK berhasil disimpan dan terverifikasi. Hubungkan KK ke rumah domisili untuk menyelesaikan proses.');
                }
            } catch (InvalidArgumentException $e) {
                $error = $e->getMessage();
            } catch (RuntimeException $e) {
                $error = $e->getMessage();
            }
        }

        $this->render('master_kk_form', array(
            'fields' => $fields, 'mode' => $mode, 'request_id' => $request_id, 'error' => $error, 'notice' => $notice,
            'dupes' => $dupes, 'scanned' => $scanned,
            'show_form' => $mode === 'manual' || $scanned || $request_id || $this->input->method(TRUE) === 'POST' && $this->input->post('action') !== 'scan',
            'family_fields' => $this->kk_fields->family_fields, 'member_fields' => $this->kk_fields->member_fields,
            'relationships' => $this->kk_fields->relationships,
            'can_verify' => $this->can('kk.verify'),
        ));
    }

    /* ---------- Undangan aktivasi akun ---------- */

    public function undang($id = 0)
    {
        $this->guard_admin('akun.invite');
        $this->post_only();
        if (!$this->master_ready()) $this->back('admin/keluarga', '', 'Undangan akun belum siap. Jalankan integrated_residence_upgrade.sql setelah backup database.');
        $id = (int) $id;
        $token = bin2hex(random_bytes(32));
        try {
            $this->atomic(function () use ($id, $token) {
                $family = $this->lock('kk_families', $id);
                if (!$family) throw new RuntimeException('KK tidak ditemukan.');
                if ($family['owner_citizen_id'] !== NULL) throw new RuntimeException('KK ini sudah tertaut ke akun warga.');
                $now = date('Y-m-d H:i:s');
                // Satu undangan aktif per KK: undangan lama dicabut (kirim ulang = token baru).
                $this->db->where(array('family_id' => $id, 'used_at' => NULL, 'revoked_at' => NULL))->update('account_invitations', array('revoked_at' => $now));
                $this->db->insert('account_invitations', array('family_id' => $id, 'token_hash' => hash('sha256', $token), 'created_by' => $this->admin_id ?: NULL,
                    'created_at' => $now, 'expires_at' => date('Y-m-d H:i:s', strtotime('+' . self::INVITE_DAYS . ' days'))));
            });
        } catch (RuntimeException $e) {
            $this->back('admin/keluarga', '', $e->getMessage());
        }
        $this->audit('account_invite', 'family:' . $id, 'berlaku ' . self::INVITE_DAYS . ' hari');
        // Tautan hanya ditampilkan sekali; yang tersimpan di database hanya hash-nya.
        $this->session->set_flashdata('kk_invite', array('family_id' => $id, 'url' => site_url('warga/aktivasi/' . $token), 'days' => self::INVITE_DAYS));
        $back = (string) $this->input->post('return', TRUE);
        redirect(preg_match('#^admin/(keluarga|penduduk/keluarga/\d+)(\?[\w=&%+.\-]*)?$#', $back) ? $back : 'admin/keluarga');
    }

    /* ---------- Data ---------- */

    private function master_ready()
    {
        return $this->Kk_model->master_ready();
    }

    /**
     * Baris Master KK: KK terverifikasi + input admin yang belum terverifikasi (draft/menunggu/perbaikan).
     * Pengajuan warga yang belum diputus tetap di tab Pengajuan Warga (Verifikasi KK).
     */
    private function rows()
    {
        if (!$this->Kk_model->ready()) return array();
        $master = $this->master_ready();
        $residences = $this->House_model->ready() ? $this->House_model->active_residences() : array();
        $members = array();
        foreach ($this->db->select('family_id, fields_json, status')->get('kk_members')->result_array() as $m) $members[(int) $m['family_id']][] = $m;
        $invites = array();
        if ($master) foreach ($this->db->select('family_id, MAX(expires_at) exp', FALSE)->where('used_at IS NULL AND revoked_at IS NULL', NULL, FALSE)->where('expires_at >', date('Y-m-d H:i:s'))->group_by('family_id')->get('account_invitations')->result_array() as $i) $invites[(int) $i['family_id']] = $i['exp'];

        $out = array();
        $select = 'f.id, f.nomor_kk, f.fields_json, f.owner_citizen_id, f.updated_at, c.display_name, c.is_active' . ($master ? ', f.source' : '');
        foreach ($this->db->select($select)->from('kk_families f')->join('citizens c', 'c.id=f.owner_citizen_id', 'left')->order_by('f.id', 'DESC')->get()->result_array() as $f) {
            $fam = json_decode($f['fields_json'], TRUE) ?: array();
            $head = ''; $active = 0; $names = array();
            foreach ($members[(int) $f['id']] ?? array() as $m) {
                $mf = json_decode($m['fields_json'], TRUE) ?: array();
                if (($m['status'] ?: 'aktif') !== 'aktif') continue;
                $active++; $names[] = (string) ($mf['nama_lengkap'] ?? '');
                if (($mf['hubungan_keluarga'] ?? '') === 'Kepala Keluarga') $head = (string) ($mf['nama_lengkap'] ?? '');
            }
            if ($f['owner_citizen_id'] !== NULL) $account = (int) $f['is_active'] ? 'aktif' : 'nonaktif';
            else $account = isset($invites[(int) $f['id']]) ? 'diundang' : 'belum';
            $res = $residences[(int) $f['id']] ?? array();
            $relation_labels = array('milik' => 'Milik Sendiri', 'kontrak' => 'Kontrakan', 'menumpang' => 'Menumpang', 'dinas' => 'Rumah Dinas', 'lainnya' => 'Lainnya');
            $out[] = array('type' => 'family', 'id' => (int) $f['id'], 'nomor_kk' => (string) $f['nomor_kk'], 'head' => $head, 'names' => $names,
                'alamat' => (string) ($fam['alamat'] ?? ''), 'rt' => (string) ($fam['rt'] ?? ''), 'rw' => (string) ($fam['rw'] ?? ''),
                'members' => $active, 'kk_status' => 'terverifikasi', 'account' => $account, 'invite_expires' => $invites[(int) $f['id']] ?? NULL,
                'owner' => (string) $f['display_name'], 'source' => (string) ($f['source'] ?? 'pengajuan_warga'), 'updated_at' => $f['updated_at'],
                'house_id' => (int) ($res['house_id'] ?? 0), 'house_number' => (string) ($res['house_number'] ?? ''), 'house_code' => (string) ($res['house_code'] ?? ''),
                'domicile_address' => (string) ($res['address_text'] ?? ''), 'residence_id' => (int) ($res['id'] ?? 0),
                'residence_status' => (string) ($res['verification_status'] ?? 'unlinked'), 'occupancy_relation' => (string) ($res['occupancy_relation'] ?? ''),
                'occupancy_label' => isset($res['occupancy_relation']) ? ($relation_labels[$res['occupancy_relation']] ?? 'Lainnya') : 'Belum diatur');
        }
        if ($master) {
            $map = array('draft' => 'draft', 'pending' => 'menunggu', 'revision_requested' => 'perlu_perbaikan');
            $pending = $this->db->select('id, status, source, proposed_data_json, created_at, updated_at')->where('citizen_id IS NULL', NULL, FALSE)
                ->where_in('status', array_keys($map))->order_by('id', 'DESC')->get('kk_scan_requests')->result_array();
            $head_rows = array();
            foreach ($pending as $r) {
                $d = json_decode($r['proposed_data_json'], TRUE) ?: array();
                $head = ''; $names = array();
                foreach ((array) ($d['anggota'] ?? array()) as $m) {
                    $names[] = (string) ($m['nama_lengkap'] ?? '');
                    if (($m['hubungan_keluarga'] ?? '') === 'Kepala Keluarga') $head = (string) ($m['nama_lengkap'] ?? '');
                }
                $head_rows[] = array('type' => 'request', 'id' => (int) $r['id'], 'nomor_kk' => (string) ($d['keluarga']['nomor_kk'] ?? ''), 'head' => $head, 'names' => $names,
                    'alamat' => (string) ($d['keluarga']['alamat'] ?? ''), 'rt' => (string) ($d['keluarga']['rt'] ?? ''), 'rw' => (string) ($d['keluarga']['rw'] ?? ''),
                    'members' => count($names), 'kk_status' => $map[$r['status']], 'account' => 'belum', 'invite_expires' => NULL,
                    'owner' => '', 'source' => (string) $r['source'], 'updated_at' => $r['updated_at'] ?: $r['created_at'],
                    'house_id' => 0, 'house_number' => '', 'house_code' => '', 'domicile_address' => '', 'residence_id' => 0,
                    'residence_status' => 'unlinked', 'occupancy_relation' => '', 'occupancy_label' => 'Belum diatur');
            }
            // Input admin yang belum selesai tampil paling atas agar tidak terlupa.
            $out = array_merge($head_rows, $out);
        }
        return $out;
    }

    private function filters()
    {
        return array(
            'q' => trim(preg_replace('/\d{8,}/', '', $this->str('q', 80, 'get'))),
            'status_kk' => $this->choice('status_kk', self::$kk_labels, '', 'get'),
            'status_akun' => $this->choice('status_akun', self::$account_labels, '', 'get'),
            'status_rumah' => $this->choice('status_rumah', array('terhubung' => 1, 'belum' => 1, 'pending' => 1), '', 'get'),
        );
    }

    private function match($r, $f)
    {
        if ($f['status_kk'] !== '' && $r['kk_status'] !== $f['status_kk']) return FALSE;
        if ($f['status_akun'] !== '' && $r['account'] !== $f['status_akun']) return FALSE;
        if ($f['status_rumah'] === 'terhubung' && empty($r['house_id'])) return FALSE;
        if ($f['status_rumah'] === 'belum' && !empty($r['house_id'])) return FALSE;
        if ($f['status_rumah'] === 'pending' && $r['residence_status'] !== 'pending') return FALSE;
        if ($f['q'] !== '') {
            $hay = mb_strtolower($r['head'] . ' ' . implode(' ', $r['names']) . ' ' . $r['alamat'] . ' ' . $r['owner'] . ' ' . $r['house_number'] . ' ' . $r['house_code'] . ' ' . $r['domicile_address'], 'UTF-8');
            if (mb_strpos($hay, mb_strtolower($f['q'], 'UTF-8')) === FALSE) return FALSE;
        }
        return TRUE;
    }
}
