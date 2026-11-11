<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Modul 04 (lanjutan) — Data Penduduk terverifikasi.
 * Satu tampilan baca untuk KK & anggota hasil verifikasi KK / mutasi:
 * daftar anggota & keluarga, detail keluarga + riwayat mutasi, rekap agregat, ekspor CSV.
 *
 * Aturan:
 * - Modul ini read-only. Perubahan data master tetap lewat Verifikasi KK atau Mutasi (no silent overwrite).
 * - NIK / nomor KK selalu dimasking di layar. Ekspor NIK lengkap hanya untuk penduduk.export_full,
 *   wajib alasan, dan tercatat di audit.
 * - Penyaringan dilakukan di PHP karena field anggota tersimpan sebagai JSON; skala satu RT (ratusan baris) aman.
 */
class Penduduk extends Ops_Controller
{
    public static $status_labels = array('aktif' => 'Aktif', 'pindah' => 'Pindah', 'meninggal' => 'Meninggal');
    public static $status_tones = array('aktif' => 'ok', 'pindah' => 'muted', 'meninggal' => 'muted');
    /** Sama dengan pilihan di Mutasi::$domicile. */
    public static $domicile = array('tetap' => 'Penduduk Tetap', 'kontrak' => 'Kontrak / Sewa', 'sementara' => 'Domisili Sementara');
    public static $age_groups = array('balita' => 'Balita (0–5)', 'anak' => 'Anak & Remaja (6–17)', 'dewasa' => 'Dewasa (18–59)', 'lansia' => 'Lansia (60+)', 'tidak_diketahui' => 'Tgl lahir kosong');
    private $per_page = 50;

    /* ---------- Halaman ---------- */

    public function index()
    {
        $this->guard_admin('penduduk.read');
        $view = $this->choice('tampil', array('anggota' => 1, 'keluarga' => 1), 'anggota', 'get');
        $filters = $this->filters();
        $all = $this->members();
        $stats = $this->summary($all);

        if ($view === 'keluarga') {
            $rows = $this->families($all, $filters['q']);
        } else {
            $rows = array_values(array_filter($all, function ($m) use ($filters) {return $this->match($m, $filters);}));
        }
        $total = count($rows);
        $pages = max(1, (int) ceil($total / $this->per_page));
        $page = min($pages, max(1, (int) $this->input->get('hal')));
        $rows = array_slice($rows, ($page - 1) * $this->per_page, $this->per_page);

        $this->render('penduduk_index', array(
            'view' => $view, 'rows' => $rows, 'filters' => $filters, 'stats' => $stats,
            'total' => $total, 'page' => $page, 'pages' => $pages,
            'query' => $this->query_string($filters + array('tampil' => $view)),
            'status_labels' => self::$status_labels, 'status_tones' => self::$status_tones,
            'domicile' => self::$domicile, 'age_groups' => self::$age_groups,
        ));
    }

    public function keluarga($id = 0)
    {
        $this->guard_admin('penduduk.read');
        $family = $this->Ops_model->family_by_id((int) $id);
        if (!$family) show_404();
        $owner = $this->db->select('id,display_name,username,is_active')->get_where('citizens', array('id' => (int) $family['owner_citizen_id']))->row_array();
        $review = array();
        foreach ($family['members'] as &$m) {
            $m['age'] = $this->age($m['fields']['tanggal_lahir'] ?? '');
            $missing = array_values(array_diff((array) ($m['fields']['fields_need_review'] ?? array()), array('agama')));
            if ($m['status'] === 'aktif' && empty($m['domicile_status'])) $missing[] = 'domisili';
            if ($missing && $m['status'] === 'aktif') $review[] = array('name' => $m['fields']['nama_lengkap'] ?? '-', 'fields' => $missing);
        }
        unset($m);
        $mutations = array();
        if ($this->db->table_exists('resident_mutations')) {
            require_once APPPATH . 'controllers/Mutasi.php';
            $mutations = $this->db->select('id,mutation_type,member_id,status,effective_date,created_at,applied_at')
                ->order_by('id', 'DESC')->get_where('resident_mutations', array('family_id' => (int) $family['id']))->result_array();
        }
        $this->audit('view', 'family:' . (int) $family['id']);
        $this->render('penduduk_keluarga', array(
            'family' => $family, 'owner' => $owner, 'review' => $review, 'mutations' => $mutations,
            'mut_types' => class_exists('Mutasi', FALSE) ? Mutasi::$types : array(),
            'mut_labels' => class_exists('Mutasi', FALSE) ? Mutasi::$labels : array(),
            'mut_tones' => class_exists('Mutasi', FALSE) ? Mutasi::$tones : array(),
            'status_labels' => self::$status_labels, 'status_tones' => self::$status_tones, 'domicile' => self::$domicile,
            'can_view_account' => in_array($this->role, array('admin', 'operator', 'ketua'), TRUE),
        ));
    }

    public function rekap()
    {
        $this->guard_admin('penduduk.read');
        $all = $this->members();
        $active = array_filter($all, function ($m) {return $m['status'] === 'aktif';});
        $age = array();
        foreach (self::$age_groups as $k => $label) $age[$k] = array('L' => 0, 'P' => 0, '?' => 0);
        $domicile = array_fill_keys(array_merge(array_keys(self::$domicile), array('belum')), 0);
        $jobs = array();
        $education = array();
        foreach ($active as $m) {
            $age[$m['age_group']][in_array($m['jk'], array('L', 'P'), TRUE) ? $m['jk'] : '?']++;
            $domicile[$m['domicile_status'] && isset(self::$domicile[$m['domicile_status']]) ? $m['domicile_status'] : 'belum']++;
            $job = $m['pekerjaan'] !== '' ? mb_convert_case($m['pekerjaan'], MB_CASE_TITLE, 'UTF-8') : 'Belum diisi';
            $jobs[$job] = ($jobs[$job] ?? 0) + 1;
            $edu = $m['pendidikan'] !== '' ? mb_strtoupper($m['pendidikan'], 'UTF-8') : 'BELUM DIISI';
            $education[$edu] = ($education[$edu] ?? 0) + 1;
        }
        arsort($jobs);
        arsort($education);
        $jobs = $this->top($jobs, 8);
        $education = $this->top($education, 8);

        // Mutasi yang sudah diterapkan, 12 bulan terakhir.
        $months = array();
        for ($i = 11; $i >= 0; $i--) $months[date('Y-m', strtotime(date('Y-m-01') . " -$i month"))] = array();
        $mut_types = array();
        if ($this->db->table_exists('resident_mutations')) {
            require_once APPPATH . 'controllers/Mutasi.php';
            $mut_types = Mutasi::$types;
            $rows = $this->db->query("SELECT DATE_FORMAT(applied_at,'%Y-%m') ym, mutation_type, COUNT(*) n FROM resident_mutations
                WHERE status='approved' AND applied_at >= ? GROUP BY ym, mutation_type", array(array_key_first($months) . '-01'))->result_array();
            foreach ($rows as $r) if (isset($months[$r['ym']])) $months[$r['ym']][$r['mutation_type']] = (int) $r['n'];
        }
        $this->audit('view', 'penduduk:rekap');
        $this->render('penduduk_rekap', array(
            'stats' => $this->summary($all), 'age' => $age, 'domicile' => $domicile, 'jobs' => $jobs, 'education' => $education,
            'months' => $months, 'mut_types' => $mut_types,
            'age_groups' => self::$age_groups, 'domicile_labels' => self::$domicile + array('belum' => 'Belum diisi'),
        ));
    }

    /**
     * Ekspor CSV anggota sesuai filter.
     * GET  = NIK & nomor KK dimasking (penduduk.export).
     * POST full=1 = NIK lengkap (penduduk.export_full) + alasan wajib, dicatat di audit.
     */
    public function ekspor()
    {
        $this->guard_admin('penduduk.export');
        $full = $this->input->method(TRUE) === 'POST' && $this->input->post('full') === '1';
        $source = $full ? 'post' : 'get';
        $reason = '';
        if ($full) {
            $this->need('penduduk.export_full');
            $reason = $this->str('reason', 200);
            if (mb_strlen($reason) < 10) $this->back('admin/penduduk?' . $this->query_string($this->filters('post')), '', 'Tuliskan alasan ekspor NIK lengkap (minimal 10 karakter).');
        }
        $filters = $this->filters($source);
        $rows = array_values(array_filter($this->members(), function ($m) use ($filters) {return $this->match($m, $filters);}));
        $this->audit($full ? 'export_full' : 'export', 'penduduk', trim('baris=' . count($rows) . ' filter=' . $this->query_string($filters) . ($full ? ' alasan=' . $reason : '')));

        $safe = function ($value) {$value = (string) $value; return preg_match('/^[=+\-@\t\r]/', $value) ? "'" . $value : $value;};
        // Tab di depan angka 16 digit menjaga Excel membaca sebagai teks (tidak jadi notasi ilmiah / 0 di belakang).
        $id = function ($value) use ($full) {return $full ? "\t" . $value : rt_mask_id($value);};
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="data-penduduk-' . ($full ? 'lengkap-' : '') . date('Ymd') . '.csv"');
        header('X-Content-Type-Options: nosniff');
        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, array('No KK', 'Alamat', 'RT', 'RW', 'NIK', 'Nama', 'L/P', 'Tempat Lahir', 'Tanggal Lahir', 'Umur', 'Hubungan', 'Pendidikan', 'Pekerjaan', 'Status', 'Domisili', 'Menetap Sejak'), ';');
        foreach ($rows as $m) {
            fputcsv($out, array($id($m['nomor_kk']), $safe($m['alamat']), $safe($m['rt']), $safe($m['rw']), $id($m['nik']), $safe($m['nama']), $m['jk'],
                $safe($m['tempat_lahir']), $m['tanggal_lahir'], $m['age'] === NULL ? '' : $m['age'], $safe($m['hubungan']), $safe($m['pendidikan']), $safe($m['pekerjaan']),
                self::$status_labels[$m['status']] ?? $m['status'], self::$domicile[$m['domicile_status']] ?? '', $m['resident_since'] ?: ''), ';');
        }
        fclose($out);
        exit;
    }

    /* ---------- Data ---------- */

    /** Semua anggota KK terverifikasi (semua status), sudah didekode & dihitung umur/kelompoknya. */
    private function members()
    {
        if (!$this->db->table_exists('kk_members')) return array();
        $rows = $this->db->select('m.id, m.family_id, m.nik, m.fields_json, m.status, m.status_changed_at, m.domicile_status, m.resident_since, f.nomor_kk, f.fields_json family_json, f.owner_citizen_id')
            ->from('kk_members m')->join('kk_families f', 'f.id=m.family_id')->order_by('f.id')->order_by('m.id')->get()->result_array();
        $out = array();
        foreach ($rows as $r) {
            $f = json_decode($r['fields_json'], TRUE) ?: array();
            $fam = json_decode($r['family_json'], TRUE) ?: array();
            $age = $this->age($f['tanggal_lahir'] ?? '');
            $out[] = array(
                'id' => (int) $r['id'], 'family_id' => (int) $r['family_id'], 'nik' => (string) $r['nik'], 'nomor_kk' => (string) $r['nomor_kk'],
                'nama' => (string) ($f['nama_lengkap'] ?? ''), 'jk' => (string) ($f['jenis_kelamin'] ?? ''),
                'tempat_lahir' => (string) ($f['tempat_lahir'] ?? ''), 'tanggal_lahir' => (string) ($f['tanggal_lahir'] ?? ''),
                'age' => $age, 'age_group' => $this->age_group($age),
                'hubungan' => (string) ($f['hubungan_keluarga'] ?? ''), 'pendidikan' => (string) ($f['pendidikan'] ?? ''), 'pekerjaan' => (string) ($f['pekerjaan'] ?? ''),
                'status' => (string) ($r['status'] ?: 'aktif'), 'status_changed_at' => $r['status_changed_at'],
                'domicile_status' => (string) $r['domicile_status'], 'resident_since' => $r['resident_since'],
                'alamat' => (string) ($fam['alamat'] ?? ''), 'rt' => (string) ($fam['rt'] ?? ''), 'rw' => (string) ($fam['rw'] ?? ''),
            );
        }
        return $out;
    }

    /** Ringkasan keluarga dari daftar anggota (termasuk KK tanpa anggota). */
    private function families($members, $q = '')
    {
        $by = array();
        foreach ($members as $m) $by[$m['family_id']][] = $m;
        $rows = $this->db->select('f.id, f.nomor_kk, f.fields_json, f.updated_at, c.display_name')->from('kk_families f')
            ->join('citizens c', 'c.id=f.owner_citizen_id', 'left')->order_by('f.id')->get()->result_array();
        $out = array();
        $needle = mb_strtolower($q, 'UTF-8');
        foreach ($rows as $r) {
            $fam = json_decode($r['fields_json'], TRUE) ?: array();
            $list = $by[(int) $r['id']] ?? array();
            $head = '';
            $active = 0;
            foreach ($list as $m) {
                if ($m['status'] === 'aktif') $active++;
                if ($m['hubungan'] === 'Kepala Keluarga' && $m['status'] === 'aktif') $head = $m['nama'];
            }
            $row = array('id' => (int) $r['id'], 'nomor_kk' => (string) $r['nomor_kk'], 'alamat' => (string) ($fam['alamat'] ?? ''),
                'rt' => (string) ($fam['rt'] ?? ''), 'rw' => (string) ($fam['rw'] ?? ''), 'head' => $head, 'active' => $active, 'total' => count($list),
                'owner' => (string) $r['display_name'], 'updated_at' => $r['updated_at']);
            if ($needle !== '') {
                $hay = mb_strtolower($head . ' ' . $row['alamat'] . ' ' . $row['owner'] . ' ' . implode(' ', array_column($list, 'nama')), 'UTF-8');
                if (mb_strpos($hay, $needle) === FALSE) continue;
            }
            $out[] = $row;
        }
        return $out;
    }

    private function summary($members)
    {
        $s = array('families' => $this->db->table_exists('kk_families') ? (int) $this->db->count_all('kk_families') : 0,
            'aktif' => 0, 'L' => 0, 'P' => 0, 'pindah' => 0, 'meninggal' => 0, 'no_domicile' => 0, 'lansia' => 0);
        foreach ($members as $m) {
            if (isset($s[$m['status']]) && $m['status'] !== 'aktif') $s[$m['status']]++;
            if ($m['status'] !== 'aktif') continue;
            $s['aktif']++;
            if ($m['jk'] === 'L' || $m['jk'] === 'P') $s[$m['jk']]++;
            if ($m['domicile_status'] === '') $s['no_domicile']++;
            if ($m['age_group'] === 'lansia') $s['lansia']++;
        }
        return $s;
    }

    private function filters($source = 'get')
    {
        return array(
            // Deretan angka panjang dibuang agar NIK/KK yang terlanjur diketik tidak ikut ke URL paginasi/audit.
            'q' => trim(preg_replace('/\d{8,}/', '', $this->str('q', 80, $source))),
            'status' => $this->choice('status', self::$status_labels + array('semua' => 1), 'aktif', $source),
            'domisili' => $this->choice('domisili', self::$domicile + array('belum' => 1), '', $source),
            'jk' => $this->choice('jk', array('L' => 1, 'P' => 1), '', $source),
            'usia' => $this->choice('usia', self::$age_groups, '', $source),
        );
    }

    private function match($m, $f)
    {
        if ($f['status'] !== 'semua' && $m['status'] !== $f['status']) return FALSE;
        if ($f['domisili'] === 'belum' && $m['domicile_status'] !== '') return FALSE;
        if ($f['domisili'] !== '' && $f['domisili'] !== 'belum' && $m['domicile_status'] !== $f['domisili']) return FALSE;
        if ($f['jk'] !== '' && $m['jk'] !== $f['jk']) return FALSE;
        if ($f['usia'] !== '' && $m['age_group'] !== $f['usia']) return FALSE;
        if ($f['q'] !== '') {
            // Sengaja tidak mencari berdasarkan NIK/KK: filter memakai GET, sehingga nomor utuh akan
            // tersimpan di log akses web server & riwayat browser.
            $hay = mb_strtolower($m['nama'] . ' ' . $m['alamat'] . ' ' . $m['pekerjaan'], 'UTF-8');
            if (mb_strpos($hay, mb_strtolower($f['q'], 'UTF-8')) === FALSE) return FALSE;
        }
        return TRUE;
    }

    private function query_string($params)
    {
        return http_build_query(array_filter($params, function ($v) {return $v !== '' && $v !== NULL;}));
    }

    private function age($birth)
    {
        if (!ops_valid_date((string) $birth)) return NULL;
        $age = (new DateTime($birth))->diff(new DateTime('today'))->y;
        return $age >= 0 && $age < 130 ? $age : NULL;
    }

    private function age_group($age)
    {
        if ($age === NULL) return 'tidak_diketahui';
        if ($age <= 5) return 'balita';
        if ($age <= 17) return 'anak';
        if ($age <= 59) return 'dewasa';
        return 'lansia';
    }

    private function top($counts, $limit)
    {
        if (count($counts) <= $limit) return $counts;
        $head = array_slice($counts, 0, $limit - 1, TRUE);
        $head['Lainnya'] = array_sum(array_slice($counts, $limit - 1));
        return $head;
    }
}
