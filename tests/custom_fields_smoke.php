<?php
/* Smoke test Field Tambahan Warga (tambah, validasi, filter status, ubah, nonaktif, hapus, izin). HANYA sukamulya_ui_test. */
if (PHP_SAPI !== 'cli') {http_response_code(404); exit;}
$dbn = getenv('DB_NAME') ?: 'sukamulya_ui_test';
if ($dbn !== 'sukamulya_ui_test') throw new RuntimeException('Only isolated sukamulya_ui_test allowed');
$db = new mysqli('localhost', 'root', '', $dbn);
$base = getenv('TEST_BASE') ?: 'http://127.0.0.1:8097/';
$tag = 'cfs' . bin2hex(random_bytes(4)); $pw = bin2hex(random_bytes(20)); $hash = password_hash($pw, PASSWORD_DEFAULT);
foreach (array('admin', 'operator') as $role) {$n = $tag . '_' . $role; $s = $db->prepare('INSERT INTO admins(username,password_hash,role,is_active) VALUES(?,?,?,1)'); $s->bind_param('sss', $n, $hash, $role); $s->execute();}
$fail = 0;
function check($ok, $l) {global $fail; echo ($ok ? 'PASS ' : 'FAIL ') . $l . PHP_EOL; if (!$ok) $fail++;}
function http() {$c = curl_init(); curl_setopt_array($c, array(CURLOPT_RETURNTRANSFER => 1, CURLOPT_COOKIEFILE => '', CURLOPT_PROXY => '', CURLOPT_TIMEOUT => 30)); return $c;}
function go($c, $p, $f = NULL) {global $base; if ($f !== NULL) {$t = ''; foreach (curl_getinfo($c, CURLINFO_COOKIELIST) as $k) {$x = explode("\t", $k); if ($x[5] === 'csrf_sukamulya_cookie') $t = $x[6];} $f['csrf_sukamulya'] = $t; curl_setopt_array($c, array(CURLOPT_POST => 1, CURLOPT_POSTFIELDS => http_build_query($f)));} else curl_setopt($c, CURLOPT_HTTPGET, 1); curl_setopt($c, CURLOPT_URL, $base . $p); $b = (string) curl_exec($c); return array(curl_getinfo($c, CURLINFO_HTTP_CODE), $b);}
$get = function ($label) use ($db) {return $db->query("SELECT * FROM custom_field_definitions WHERE label='" . $db->real_escape_string($label) . "'")->fetch_assoc();};
try {
    $a = http(); go($a, 'admin/login'); go($a, 'admin/login', array('username' => $tag . '_admin', 'password' => $pw));
    list($code, $html) = go($a, 'admin/field-tambahan');
    check($code === 200 && !preg_match('/A PHP Error|Fatal error|Database Error/', $html), 'Halaman Field Tambahan tampil tanpa error');
    foreach (array('Semua Field', 'Aktif', 'Nonaktif', 'Tambah Field', 'Label Field', 'Tipe Isian', 'Wajib', 'Urutan', 'Status', 'Aksi') as $t) check(strpos($html, $t) !== FALSE, "Elemen mockup: $t");
    list(, $html) = go($a, 'admin/field-tambahan?baru=1'); check(strpos($html, 'data-cf-autoopen') !== FALSE, 'Tambah Field membuka dialog form');
    // Validasi.
    list(, $html) = go($a, 'admin/field-tambahan', array('label' => 'NIK ' . $tag, 'field_type' => 'text', 'sort_order' => 1));
    check(!$get('NIK ' . $tag) && strpos($html, 'data-cf-autoopen') !== FALSE, 'Label data sensitif (NIK) ditolak, dialog tetap terbuka dengan pesan error');
    go($a, 'admin/field-tambahan', array('label' => 'Status Rumah ' . $tag, 'field_type' => 'select', 'options' => 'Milik sendiri', 'sort_order' => 2));
    check(!$get('Status Rumah ' . $tag), 'Tipe Pilihan butuh minimal 2 opsi');
    // Tambah.
    go($a, 'admin/field-tambahan', array('label' => 'Status Rumah ' . $tag, 'field_type' => 'select', 'options' => "Milik sendiri\nSewa", 'sort_order' => 2, 'is_required' => 1, 'citizen_editable' => 1));
    $f = $get('Status Rumah ' . $tag);
    check($f && $f['options'] === "Milik sendiri\nSewa" && (int) $f['is_required'] === 1 && (int) $f['is_active'] === 1, 'Field Pilihan tersimpan (opsi, wajib, aktif)');
    go($a, 'admin/field-tambahan', array('label' => 'Nomor Rumah ' . $tag, 'field_type' => 'text', 'sort_order' => 1, 'citizen_editable' => 1));
    $g = $get('Nomor Rumah ' . $tag);
    // Ubah.
    list(, $html) = go($a, 'admin/field-tambahan/' . $g['id']); check(strpos($html, 'Ubah Field') !== FALSE && strpos($html, 'data-cf-autoopen') !== FALSE, 'Ubah field membuka dialog berisi data');
    go($a, 'admin/field-tambahan/' . $g['id'], array('label' => 'Nomor Rumah ' . $tag, 'field_type' => 'text', 'sort_order' => 5, 'help_text' => 'Contoh A-12'));
    check((int) $get('Nomor Rumah ' . $tag)['sort_order'] === 5, 'Perubahan urutan & keterangan tersimpan');
    // Toggle & filter.
    go($a, 'admin/field-tambahan', array('act' => 'toggle', 'field_id' => $g['id']));
    check((int) $get('Nomor Rumah ' . $tag)['is_active'] === 0, 'Nonaktifkan field');
    list(, $html) = go($a, 'admin/field-tambahan?status=inactive'); check(strpos($html, 'Nomor Rumah ' . $tag) !== FALSE && strpos($html, 'Status Rumah ' . $tag) === FALSE, 'Tab Nonaktif memfilter di server');
    list(, $html) = go($a, 'admin/field-tambahan?status=active'); check(strpos($html, 'Status Rumah ' . $tag) !== FALSE && strpos($html, 'Nomor Rumah ' . $tag) === FALSE, 'Tab Aktif memfilter di server');
    // Hapus: ditolak bila sudah terisi.
    $cid = (int) ($db->query('SELECT id FROM citizens LIMIT 1')->fetch_assoc()['id'] ?? 0);
    if ($cid) $db->query("INSERT INTO citizen_field_values(citizen_id,field_id,value) VALUES($cid,{$f['id']},'Sewa')");
    go($a, 'admin/field-tambahan', array('act' => 'delete', 'field_id' => $f['id']));
    check(!$cid || (bool) $get('Status Rumah ' . $tag), 'Field yang sudah diisi warga tidak bisa dihapus');
    go($a, 'admin/field-tambahan', array('act' => 'delete', 'field_id' => $g['id']));
    check(!$get('Nomor Rumah ' . $tag), 'Field yang belum diisi dapat dihapus');
    // Izin.
    $o = http(); go($o, 'admin/login'); go($o, 'admin/login', array('username' => $tag . '_operator', 'password' => $pw));
    list($code) = go($o, 'admin/field-tambahan'); check($code === 403, 'Operator tidak bisa membuka Field Tambahan');
} finally {
    foreach ($db->query("SELECT id FROM custom_field_definitions WHERE label LIKE '%$tag%'")->fetch_all(MYSQLI_ASSOC) as $r) {$db->query('DELETE FROM citizen_field_values WHERE field_id=' . (int) $r['id']); $db->query('DELETE FROM custom_field_definitions WHERE id=' . (int) $r['id']);}
    $db->query("DELETE FROM admins WHERE username LIKE '{$tag}\\_%'");
}
echo $fail ? "$fail FAILED\n" : "ALL PASS (hanya database uji yang diubah)\n";
exit($fail ? 1 : 0);
