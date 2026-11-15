<?php
/* Smoke test Riwayat Pengaturan (filter, pagination, detail, Kembalikan). HANYA sukamulya_ui_test. */
if (PHP_SAPI !== 'cli') {http_response_code(404); exit;}
$dbn = getenv('DB_NAME') ?: 'sukamulya_ui_test';
if ($dbn !== 'sukamulya_ui_test') throw new RuntimeException('Only isolated sukamulya_ui_test allowed');
$db = new mysqli('localhost', 'root', '', $dbn);
$base = getenv('TEST_BASE') ?: 'http://127.0.0.1:8097/';
$tag = 'shs' . bin2hex(random_bytes(4)); $pw = bin2hex(random_bytes(20)); $hash = password_hash($pw, PASSWORD_DEFAULT);
foreach (array('admin', 'operator') as $role) {$n = $tag . '_' . $role; $s = $db->prepare('INSERT INTO admins(username,password_hash,role,is_active) VALUES(?,?,?,1)'); $s->bind_param('sss', $n, $hash, $role); $s->execute();}
$fail = 0;
function check($ok, $l) {global $fail; echo ($ok ? 'PASS ' : 'FAIL ') . $l . PHP_EOL; if (!$ok) $fail++;}
function http() {$c = curl_init(); curl_setopt_array($c, array(CURLOPT_RETURNTRANSFER => 1, CURLOPT_COOKIEFILE => '', CURLOPT_PROXY => '', CURLOPT_TIMEOUT => 30)); return $c;}
function go($c, $p, $f = NULL) {global $base; if ($f !== NULL) {$t = ''; foreach (curl_getinfo($c, CURLINFO_COOKIELIST) as $k) {$x = explode("\t", $k); if ($x[5] === 'csrf_sukamulya_cookie') $t = $x[6];} $f['csrf_sukamulya'] = $t; curl_setopt_array($c, array(CURLOPT_POST => 1, CURLOPT_POSTFIELDS => http_build_query($f)));} else curl_setopt($c, CURLOPT_HTTPGET, 1); curl_setopt($c, CURLOPT_URL, $base . $p); $b = (string) curl_exec($c); return array(curl_getinfo($c, CURLINFO_HTTP_CODE), $b);}
$orig = $db->query("SELECT setting_value FROM settings WHERE setting_key='tagline'")->fetch_assoc()['setting_value'] ?? '';
$ids = array();
try {
    // Data uji: 12 perubahan (untuk pagination) + 1 perubahan tagline yang akan dikembalikan.
    for ($i = 0; $i < 12; $i++) {$db->query("INSERT INTO settings_history(snapshot_json,note,changed_by,created_at) VALUES('{\"home_banner_jpg\":\"\"}','Perubahan: home_banner_jpg $tag',NULL,NOW()-INTERVAL $i HOUR)"); $ids[] = $db->insert_id;}
    $db->query("REPLACE INTO settings(setting_key,setting_value) VALUES('tagline','Tagline baru $tag')");
    $db->query("INSERT INTO settings_history(snapshot_json,note,changed_by,created_at) VALUES('" . $db->real_escape_string(json_encode(array('tagline' => 'Tagline lama ' . $tag))) . "','Perubahan: tagline $tag',NULL,NOW())"); $target = $db->insert_id; $ids[] = $target;
    $a = http(); go($a, 'admin/login'); go($a, 'admin/login', array('username' => $tag . '_admin', 'password' => $pw));
    list($code, $html) = go($a, 'admin/pengaturan/riwayat');
    check($code === 200 && !preg_match('/A PHP Error|Fatal error|Database Error/', $html), 'Halaman Riwayat Pengaturan tampil tanpa error');
    foreach (array('Total Perubahan', 'Perubahan Hari Ini', 'Dilakukan Oleh', 'Fitur Terbanyak', 'Terapkan Filter', 'Daftar Perubahan', '10 / halaman') as $t) check(strpos($html, $t) !== FALSE, "Elemen mockup: $t");
    check(substr_count($html, 'class="sh-restore"') === 10, 'Pagination: 10 baris per halaman');
    check(strpos($html, 'Tampilan</span>') !== FALSE, 'Kategori banner → Tampilan');
    list(, $html) = go($a, 'admin/pengaturan/riwayat?page=2');
    check(substr_count($html, 'class="sh-restore"') >= 3 && strpos($html, 'aria-current="page">2<') !== FALSE, 'Halaman 2 berisi sisa data');
    list(, $html) = go($a, 'admin/pengaturan/riwayat?per=25');
    check(substr_count($html, 'class="sh-restore"') >= 13, 'Pilihan 25 / halaman');
    list(, $html) = go($a, 'admin/pengaturan/riwayat?q=' . urlencode('tagline ' . $tag));
    check(substr_count($html, 'class="sh-restore"') === 1 && strpos($html, 'Ditemukan 1 perubahan') !== FALSE, 'Pencarian kata kunci memfilter di server');
    list(, $html) = go($a, 'admin/pengaturan/riwayat?q=' . urlencode('Tagline lama ' . $tag));
    check(substr_count($html, 'class="sh-restore"') === 1, 'Pencarian juga mencari nilai sebelumnya');
    list(, $html) = go($a, 'admin/pengaturan/riwayat?period=today&q=' . $tag);
    check(substr_count($html, 'class="sh-restore"') >= 1, 'Filter periode Hari ini');
    list(, $html) = go($a, 'admin/pengaturan/riwayat?period=1999-01');
    check(strpos($html, 'Tidak ada perubahan yang sesuai filter') !== FALSE, 'Filter periode tanpa hasil menampilkan pesan kosong');
    list(, $html) = go($a, 'admin/pengaturan/riwayat?q=' . urlencode('tagline ' . $tag));
    check(strpos($html, 'id="sh-d-' . $target . '"') !== FALSE && strpos($html, 'Tagline lama ' . $tag) !== FALSE && strpos($html, 'Tagline baru ' . $tag) !== FALSE, 'Detail: nilai sebelumnya vs sekarang');
    list($code) = go($a, 'admin/pengaturan/riwayat?q=' . urlencode('tagline ' . $tag), array('history_id' => $target));
    $now = $db->query("SELECT setting_value FROM settings WHERE setting_key='tagline'")->fetch_assoc()['setting_value'];
    check($now === 'Tagline lama ' . $tag, 'Kembalikan memulihkan nilai tagline');
    $new = $db->query("SELECT * FROM settings_history WHERE note LIKE 'Kembalikan ke nilai sebelum perubahan #$target%' ORDER BY id DESC LIMIT 1")->fetch_assoc();
    check($new && strpos($new['snapshot_json'], 'Tagline baru ' . $tag) !== FALSE, 'Pemulihan tercatat sebagai riwayat baru (bisa dibatalkan)');
    if ($new) $ids[] = $new['id'];
    list(, $html) = go($a, 'admin/pengaturan/riwayat?q=' . $target);
    check(strpos($html, 'Pemulihan</span>') !== FALSE, 'Riwayat pemulihan berkategori Pemulihan');
    $o = http(); go($o, 'admin/login'); go($o, 'admin/login', array('username' => $tag . '_operator', 'password' => $pw));
    list($code) = go($o, 'admin/pengaturan/riwayat'); check($code === 403, 'Operator tidak bisa membuka Riwayat Pengaturan');
    list($code) = go($o, 'admin/pengaturan/riwayat', array('history_id' => $target)); check($code === 403, 'Operator tidak bisa Kembalikan');
} finally {
    if ($ids) $db->query('DELETE FROM settings_history WHERE id IN (' . implode(',', array_map('intval', $ids)) . ')');
    $s = $db->prepare("REPLACE INTO settings(setting_key,setting_value) VALUES('tagline',?)"); $s->bind_param('s', $orig); $s->execute();
    $db->query("DELETE FROM admins WHERE username LIKE '" . $tag . "\\_%'");
}
echo $fail ? "$fail FAILED\n" : "ALL PASS (hanya database uji yang diubah)\n";
exit($fail ? 1 : 0);
