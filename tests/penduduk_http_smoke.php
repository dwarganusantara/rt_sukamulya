<?php
if (PHP_SAPI !== 'cli') exit;
$base = 'http://localhost/sukamulya/';
$db = new mysqli('localhost', 'root', '', 'sukamulya');
$db->set_charset('utf8mb4');
$tag = 'pdtest_' . bin2hex(random_bytes(4));
$pw = bin2hex(random_bytes(12));
$hash = password_hash($pw, PASSWORD_DEFAULT);
$admins = array();
$results = array();
$fail = 0;
$check = function ($ok, $msg) use (&$results, &$fail) {$results[] = ($ok ? 'PASS ' : 'FAIL ') . $msg; if (!$ok) $fail++;};
function xlsx_sheet_xml($bin) {$tmp=tempnam(sys_get_temp_dir(),'xlsx-test-');file_put_contents($tmp,$bin);$z=new ZipArchive();if($z->open($tmp)!==TRUE){@unlink($tmp);return '';}$xml=(string)$z->getFromName('xl/worksheets/sheet1.xml');$z->close();@unlink($tmp);return $xml;}

// Data sintetis: 1 warga, 1 KK, 3 anggota (aktif, pindah, aktif tanpa domisili)
$db->query("INSERT INTO citizens (username,display_name,password_hash,is_active) VALUES ('$tag','Uji Penduduk','$hash',1)");
$cid = $db->insert_id;
$kk = '99' . str_pad((string) random_int(0, 99999999999999), 14, '0', STR_PAD_LEFT);
$fam = json_encode(array('nomor_kk' => $kk, 'alamat' => 'Jl. Uji Coba No. 1', 'rt' => '009', 'rw' => '008', 'kelurahan' => 'Uji'));
$db->query("INSERT INTO kk_families (nomor_kk,owner_citizen_id,fields_json) VALUES ('$kk',$cid,'" . $db->real_escape_string($fam) . "')");
$fid = $db->insert_id;
$niks = array();
$members = array(
    array('ZZUJI KEPALA', 'L', '1980-01-01', 'Kepala Keluarga', 'aktif', 'tetap'),
    array('ZZUJI PINDAH', 'P', '2001-05-05', 'Anak', 'pindah', 'tetap'),
    array('ZZUJI BALITA', 'P', date('Y-m-d', strtotime('-3 years')), 'Anak', 'aktif', NULL),
);
foreach ($members as $i => $m) {
    $nik = '99' . str_pad((string) random_int(0, 99999999999999), 14, '0', STR_PAD_LEFT);
    $niks[] = $nik;
    $f = $db->real_escape_string(json_encode(array('nama_lengkap' => $m[0], 'nik' => $nik, 'jenis_kelamin' => $m[1], 'tanggal_lahir' => $m[2], 'hubungan_keluarga' => $m[3], 'pekerjaan' => 'PELAJAR', 'fields_need_review' => array())));
    $dom = $m[5] ? "'$m[5]'" : 'NULL';
    $db->query("INSERT INTO kk_members (family_id,nik,fields_json,status,domicile_status) VALUES ($fid,'$nik','$f','$m[4]',$dom)");
}

function login($role, $tag, $hash, $pw, $db, &$admins)
{
    global $base;
    $u = $tag . '_' . $role;
    $db->query("INSERT INTO admins (username,password_hash,role,is_active) VALUES ('$u','$hash','$role',1)");
    $admins[] = $db->insert_id;
    $c = curl_init();
    curl_setopt_array($c, array(CURLOPT_RETURNTRANSFER => TRUE, CURLOPT_COOKIEFILE => '', CURLOPT_TIMEOUT => 20, CURLOPT_PROXY => '', CURLOPT_URL => $base . 'admin/login'));
    $html = curl_exec($c);
    preg_match('/name="(csrf_[^"]+)"\s+value="([^"]+)"/', $html, $t);
    curl_setopt_array($c, array(CURLOPT_POST => TRUE, CURLOPT_POSTFIELDS => http_build_query(array('username' => $u, 'password' => $pw, $t[1] => $t[2]))));
    curl_exec($c);
    curl_setopt($c, CURLOPT_HTTPGET, TRUE);
    return $c;
}
function get($c, $path, &$code, &$ctype = NULL)
{
    global $base;
    curl_setopt($c, CURLOPT_HTTPGET, TRUE);
    curl_setopt($c, CURLOPT_URL, $base . $path);
    curl_setopt($c, CURLOPT_FOLLOWLOCATION, FALSE);
    $body = curl_exec($c);
    $code = curl_getinfo($c, CURLINFO_HTTP_CODE);
    $ctype = curl_getinfo($c, CURLINFO_CONTENT_TYPE);
    return $body;
}
function post($c, $path, $fields, &$code)
{
    global $base;
    // Token CSRF diambil dari cookie, jadi 403 yang terjadi pasti dari cek izin, bukan CSRF.
    $token = '';
    foreach (curl_getinfo($c, CURLINFO_COOKIELIST) as $line) {$p = explode("\t", $line); if (($p[5] ?? '') === 'csrf_sukamulya_cookie') $token = $p[6];}
    if ($token === '') throw new RuntimeException('cookie CSRF tidak ada');
    curl_setopt_array($c, array(CURLOPT_URL => $base . $path, CURLOPT_POST => TRUE, CURLOPT_POSTFIELDS => http_build_query($fields + array('csrf_sukamulya' => $token))));
    $body = curl_exec($c);
    $code = curl_getinfo($c, CURLINFO_HTTP_CODE);
    return $body;
}
$err = '/A PHP Error|Fatal error|Severity:|Database Error|Exception/i';

try {
    // ---- admin
    $a = login('admin', $tag, $hash, $pw, $db, $admins);
    $body = get($a, 'admin/penduduk?q=ZZUJI', $code);
    $check($code === 200 && !preg_match($err, $body), "admin: daftar anggota HTTP $code tanpa error PHP");
    $check(substr_count($body, '<strong>ZZUJI') === 2, 'filter default status=aktif: 2 anggota aktif tampil, yang pindah tidak');
    $leak = 0; foreach (array_merge($niks, array($kk)) as $n) if (strpos($body, $n) !== FALSE) $leak++;
    $check($leak === 0, 'NIK & nomor KK tidak muncul utuh di daftar (masking)');
    $body = get($a, 'admin/penduduk?q=ZZUJI&status=semua', $code);
    $check(substr_count($body, '<strong>ZZUJI') === 3, 'status=semua menampilkan anggota pindah sebagai histori');
    $body = get($a, 'admin/penduduk?q=ZZUJI&domisili=belum', $code);
    $check(strpos($body, 'ZZUJI BALITA') !== FALSE && strpos($body, 'ZZUJI KEPALA') === FALSE, 'filter domisili belum diisi');
    $body = get($a, 'admin/penduduk?q=ZZUJI&usia=balita', $code);
    $check(strpos($body, 'ZZUJI BALITA') !== FALSE && strpos($body, 'ZZUJI KEPALA') === FALSE, 'filter kelompok usia balita');
    $body = get($a, 'admin/penduduk?q=' . $niks[0], $code);
    $body = preg_replace('/<input id="ad-search-input"[^>]*>/', '', $body); // kotak cari global header (perilaku lama)
    $check($code === 200 && strpos($body, $niks[0]) === FALSE, 'NIK yang diketik di pencarian tidak dipantulkan ke halaman/tautan paginasi');
    $body = get($a, 'admin/penduduk?tampil=keluarga&q=ZZUJI', $code);
    $check($code === 200 && strpos($body, 'ZZUJI KEPALA') !== FALSE && strpos($body, $kk) === FALSE && !preg_match($err, $body), 'tampilan per keluarga: kepala keluarga tampil, KK dimasking');
    $body = get($a, 'admin/penduduk/keluarga/' . $fid, $code);
    $check($code === 200 && !preg_match($err, $body) && strpos($body, 'ZZUJI PINDAH') !== FALSE && strpos($body, 'Data perlu dilengkapi') !== FALSE, 'detail keluarga: histori anggota + peringatan data perlu dilengkapi');
    $leak = 0; foreach (array_merge($niks, array($kk)) as $n) if (strpos($body, $n) !== FALSE) $leak++;
    $check($leak === 0, 'detail keluarga tidak menampilkan NIK/KK utuh');
    $check(strpos($body, 'admin/mutasi/baru?keluarga=' . $fid) !== FALSE, 'tombol Catat Mutasi tertaut ke KK ini');
    get($a, 'admin/penduduk/keluarga/99999999', $code);
    $check($code === 404, "detail KK tidak ada -> HTTP $code");
    $body = get($a, 'admin/penduduk/rekap', $code);
    $check($code === 200 && !preg_match($err, $body) && strpos($body, 'Kelompok usia') !== FALSE && strpos($body, 'ZZUJI') === FALSE, 'rekap: HTTP 200, agregat tanpa nama perorangan');
    $body = get($a, 'admin', $code);
    $check($code === 200 && strpos($body, 'admin/penduduk') !== FALSE && !preg_match($err, $body), 'dashboard: kartu KK & menu menaut ke Data Penduduk');

    foreach (array('admin/citizens', 'admin/kk/pengajuan', 'admin/mutasi', 'admin/mutasi/baru?keluarga=' . $fid, 'admin/bansos', 'admin/serah-terima') as $p) {
        $body = get($a, $p, $code);
        $check($code === 200 && !preg_match($err, $body), "regresi halaman lama $p HTTP $code");
    }
    $xlsx = get($a, 'admin/penduduk/ekspor?q=ZZUJI&status=semua&format=xlsx', $code, $ct); $xml=xlsx_sheet_xml($xlsx);
    $check($code === 200 && strpos((string) $ct, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') === 0 && substr_count($xml, 'ZZUJI') === 3, 'ekspor XLSX (masking): 3 baris sesuai filter');
    $leak = 0; foreach ($niks as $n) if (strpos($xml, $n) !== FALSE) $leak++;
    $check($leak === 0, 'ekspor default tidak memuat NIK utuh');
    $body = post($a, 'admin/penduduk/ekspor', array('full' => '1', 'reason' => 'x', 'q' => 'ZZUJI', 'status' => 'semua'), $code);
    $check($code === 303 || $code === 302, "ekspor lengkap tanpa alasan memadai ditolak (redirect $code)");
    $before = (int) $db->query("SELECT COUNT(*) n FROM activity_logs WHERE action='export_full'")->fetch_assoc()['n'];
    $xlsx = post($a, 'admin/penduduk/ekspor', array('full' => '1', 'format' => 'xlsx', 'reason' => 'Uji otomatis ekspor lengkap', 'q' => 'ZZUJI', 'status' => 'semua'), $code); $xml=xlsx_sheet_xml($xlsx);
    $found = 0; foreach ($niks as $n) if (strpos($xml, $n) !== FALSE) $found++;
    $check($code === 200 && $found === 3, 'ekspor lengkap dengan alasan: 3 NIK utuh sebagai teks Excel');
    $log = $db->query("SELECT details FROM activity_logs WHERE action='export_full' ORDER BY id DESC LIMIT 1")->fetch_assoc();
    $after = (int) $db->query("SELECT COUNT(*) n FROM activity_logs WHERE action='export_full'")->fetch_assoc()['n'];
    $check($after === $before + 1 && strpos((string) $log['details'], 'alasan=Uji otomatis') !== FALSE && strpos((string) $log['details'], 'baris=3') !== FALSE, 'ekspor lengkap tercatat di audit (alasan + jumlah baris)');

    // ---- sekretaris
    $s = login('sekretaris', $tag, $hash, $pw, $db, $admins);
    get($s, 'admin/penduduk', $code); $check($code === 200, "sekretaris: baca HTTP $code");
    $body = get($s, 'admin/penduduk', $code); $check(strpos($body, 'Unduh Excel Lengkap') === FALSE, 'sekretaris: form ekspor lengkap tidak tampil');
    get($s, 'admin/penduduk/ekspor?q=ZZUJI&format=xlsx', $code, $ct); $check($code === 200 && strpos((string) $ct, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') === 0, 'sekretaris: ekspor masking diizinkan');
    post($s, 'admin/penduduk/ekspor', array('full' => '1', 'reason' => 'Mencoba ekspor lengkap', 'q' => 'ZZUJI'), $code); $check($code === 403, "sekretaris: ekspor lengkap ditolak backend (HTTP $code)");

    // ---- auditor
    $u = login('auditor', $tag, $hash, $pw, $db, $admins);
    get($u, 'admin/penduduk/rekap', $code); $check($code === 200, "auditor: rekap HTTP $code");
    get($u, 'admin/penduduk/ekspor', $code); $check($code === 403, "auditor: ekspor ditolak (HTTP $code)");

    // ---- bendahara & keamanan (tidak berhak)
    foreach (array('bendahara', 'keamanan') as $r) {
        $x = login($r, $tag, $hash, $pw, $db, $admins);
        get($x, 'admin/penduduk', $code); $check($code === 403, "$r: daftar penduduk ditolak (HTTP $code)");
        get($x, 'admin/penduduk/keluarga/' . $fid, $code); $check($code === 403, "$r: detail keluarga ditolak (HTTP $code)");
        $body = get($x, 'admin/notifications', $c2);
        $check(strpos((string) $body, 'admin/penduduk"') === FALSE, "$r: menu Data Penduduk tidak tampil");
    }

    // ---- tanpa login
    $anon = curl_init(); curl_setopt_array($anon, array(CURLOPT_RETURNTRANSFER => TRUE, CURLOPT_PROXY => ''));
    get($anon, 'admin/penduduk/ekspor', $code); $check(in_array($code, array(302, 303, 307), TRUE), "tanpa login: ekspor diarahkan ke login (HTTP $code)");
} finally {
    $ids = implode(',', array_map('intval', $admins)) ?: '0';
    $db->query("DELETE FROM activity_logs WHERE admin_id IN ($ids)");
    $db->query("DELETE FROM chat_presence WHERE user_type='admin' AND user_id IN ($ids)");
    $db->query("DELETE FROM admins WHERE id IN ($ids)");
    $db->query("DELETE FROM kk_members WHERE family_id=$fid");
    $db->query("DELETE FROM kk_families WHERE id=$fid");
    $db->query("DELETE FROM chat_presence WHERE user_type='citizen' AND user_id=$cid");
    $db->query("DELETE FROM citizens WHERE id=$cid");
}
echo implode(PHP_EOL, $results) . PHP_EOL . ($fail ? "$fail GAGAL" : 'SEMUA LULUS') . PHP_EOL;
