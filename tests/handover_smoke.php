<?php
/* Smoke test Serah Terima Pengurus (buat sesi, validasi, filter, hapus draf, izin). HANYA sukamulya_ui_test. */
if (PHP_SAPI !== 'cli') {http_response_code(404); exit;}
$dbn = getenv('DB_NAME') ?: 'sukamulya_ui_test';
if ($dbn !== 'sukamulya_ui_test') throw new RuntimeException('Only isolated sukamulya_ui_test allowed');
$db = new mysqli('localhost', 'root', '', $dbn);
$base = getenv('TEST_BASE') ?: 'http://127.0.0.1:8097/';
$tag = 'hvs' . bin2hex(random_bytes(4)); $pw = bin2hex(random_bytes(20)); $hash = password_hash($pw, PASSWORD_DEFAULT);
foreach (array('admin', 'auditor', 'operator') as $role) {$n = $tag . '_' . $role; $s = $db->prepare('INSERT INTO admins(username,password_hash,role,is_active) VALUES(?,?,?,1)'); $s->bind_param('sss', $n, $hash, $role); $s->execute();}
$fail = 0;
function check($ok, $l) {global $fail; echo ($ok ? 'PASS ' : 'FAIL ') . $l . PHP_EOL; if (!$ok) $fail++;}
function http() {$c = curl_init(); curl_setopt_array($c, array(CURLOPT_RETURNTRANSFER => 1, CURLOPT_COOKIEFILE => '', CURLOPT_PROXY => '', CURLOPT_TIMEOUT => 30, CURLOPT_HEADER => 1)); return $c;}
function go($c, $p, $f = NULL) {global $base; if ($f !== NULL) {$t = ''; foreach (curl_getinfo($c, CURLINFO_COOKIELIST) as $k) {$x = explode("\t", $k); if ($x[5] === 'csrf_sukamulya_cookie') $t = $x[6];} $f['csrf_sukamulya'] = $t; curl_setopt_array($c, array(CURLOPT_POST => 1, CURLOPT_POSTFIELDS => http_build_query($f)));} else curl_setopt($c, CURLOPT_HTTPGET, 1); curl_setopt($c, CURLOPT_URL, $base . $p); $raw = (string) curl_exec($c); $h = curl_getinfo($c, CURLINFO_HEADER_SIZE); preg_match('/^Location:\s*(\S+)/mi', substr($raw, 0, $h), $m); return array(curl_getinfo($c, CURLINFO_HTTP_CODE), substr($raw, $h), $m[1] ?? '');}
$db->query("INSERT INTO officials(name,position,description,icon,sort_order,is_active) VALUES('Lama $tag','Ketua RT','', 'x', 0, 0),('Baru $tag','Ketua RT','', 'x', 0, 1)");
$o1 = $db->insert_id; $o2 = $o1 + 1;
try {
    $a = http(); go($a, 'admin/login'); go($a, 'admin/login', array('username' => $tag . '_admin', 'password' => $pw));
    list($code, $html) = go($a, 'admin/serah-terima');
    check($code === 200 && !preg_match('/A PHP Error|Fatal error|Database Error/', $html), 'Halaman Serah Terima tampil tanpa error');
    foreach (array('Buat Sesi', 'Lengkapi Data', 'Finalisasi', 'Daftar Sesi Serah Terima', 'Semua Periode', 'Buat Sesi Baru', 'Judul Sesi', 'Periode Kepengurusan', 'Pilih pengurus lama', 'Pilih pengurus baru', 'Lanjutkan') as $t) check(strpos($html, $t) !== FALSE, "Elemen mockup: $t");
    check(strpos($html, 'value="o' . $o1 . '"') !== FALSE, 'Dropdown pengurus diisi dari Manajemen Pengurus');
    // Validasi.
    list(, , $loc) = go($a, 'admin/serah-terima', array('title' => 'Uji ' . $tag, 'period_label' => '2026 - 2029', 'outgoing' => 'o' . $o1, 'incoming' => 'o' . $o1));
    list(, $html) = go($a, 'admin/serah-terima');
    check(strpos($html, 'tidak boleh sama') !== FALSE && !$db->query("SELECT 1 FROM handover_sessions WHERE title='Uji $tag'")->num_rows, 'Validasi: pengurus lama ≠ baru (data form dipertahankan)');
    check(strpos($html, 'value="Uji ' . $tag . '"') !== FALSE, 'Isian form dipertahankan setelah error');
    go($a, 'admin/serah-terima', array('title' => 'Uji ' . $tag, 'period_label' => '2029-2026', 'outgoing' => 'o' . $o1, 'incoming' => 'o' . $o2));
    check(!$db->query("SELECT 1 FROM handover_sessions WHERE title='Uji $tag'")->num_rows, 'Validasi: tahun akhir harus > tahun awal');
    // Buat sesi (dropdown + manual).
    list(, , $loc) = go($a, 'admin/serah-terima', array('title' => 'Uji ' . $tag, 'period_label' => '2026-2029', 'outgoing' => 'o' . $o1, 'incoming' => '__other', 'incoming_other' => 'Manual ' . $tag));
    $s = $db->query("SELECT * FROM handover_sessions WHERE title='Uji $tag'")->fetch_assoc();
    check($s && $s['period_label'] === '2026 – 2029' && $s['outgoing'] === 'Lama ' . $tag . ' — Ketua RT' && $s['incoming'] === 'Manual ' . $tag && $s['status'] === 'draft', 'Sesi dibuat: periode dinormalkan, pengurus dari dropdown & manual');
    check($s && strpos($loc, 'admin/serah-terima/' . $s['id']) !== FALSE, 'Lanjutkan membuka langkah 2 (detail sesi)');
    check($s && $s['snapshot_hash'] === hash('sha256', $s['snapshot_json']), 'Snapshot berita acara tersimpan utuh');
    list($code) = go($a, 'admin/serah-terima/' . $s['id']); check($code === 200, 'Detail sesi terbuka');
    // Filter periode.
    $db->query("INSERT INTO handover_sessions(title,period_label,outgoing,incoming,status) VALUES('Final $tag','2020 – 2022','A','B','final')"); $fid = $db->insert_id;
    list(, $html) = go($a, 'admin/serah-terima?period=' . urlencode('2020 – 2022'));
    check(strpos($html, 'Final ' . $tag) !== FALSE && strpos($html, 'Uji ' . $tag) === FALSE, 'Filter periode di server');
    check(strpos($html, 'Selesai') !== FALSE && strpos($html, 'Hapus sesi draf') === FALSE, 'Sesi final: status Selesai, tanpa opsi hapus');
    // Hapus.
    go($a, 'admin/serah-terima', array('act' => 'delete', 'id' => $fid));
    check((bool) $db->query("SELECT 1 FROM handover_sessions WHERE id=$fid")->num_rows, 'Sesi final tidak bisa dihapus');
    go($a, 'admin/serah-terima', array('act' => 'delete', 'id' => $s['id']));
    check(!$db->query("SELECT 1 FROM handover_sessions WHERE id=" . (int) $s['id'])->num_rows, 'Sesi draf dapat dihapus');
    // Izin.
    $au = http(); go($au, 'admin/login'); go($au, 'admin/login', array('username' => $tag . '_auditor', 'password' => $pw));
    list($code, $html) = go($au, 'admin/serah-terima'); check($code === 200 && strpos($html, 'Buat Sesi Baru') === FALSE, 'Auditor hanya melihat (tanpa form Buat Sesi)');
    list($code) = go($au, 'admin/serah-terima', array('title' => 'Hack ' . $tag, 'period_label' => '2026-2029', 'outgoing' => '__other', 'outgoing_other' => 'X', 'incoming' => '__other', 'incoming_other' => 'Y'));
    check($code === 403 && !$db->query("SELECT 1 FROM handover_sessions WHERE title='Hack $tag'")->num_rows, 'Auditor tidak bisa membuat sesi');
    $op = http(); go($op, 'admin/login'); go($op, 'admin/login', array('username' => $tag . '_operator', 'password' => $pw));
    list($code) = go($op, 'admin/serah-terima'); check($code === 403, 'Operator tidak bisa membuka Serah Terima');
} finally {
    $db->query("DELETE FROM handover_sessions WHERE title LIKE '%$tag%'");
    $db->query("DELETE FROM officials WHERE name LIKE '%$tag%'");
    $db->query("DELETE FROM admins WHERE username LIKE '" . $tag . "\\_%'");
}
echo $fail ? "$fail FAILED\n" : "ALL PASS (hanya database uji yang diubah)\n";
exit($fail ? 1 : 0);
