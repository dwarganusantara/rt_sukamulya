<?php
/*
 * Smoke test Antrean Email: Atur SMTP (password terenkripsi), Kirim Email Uji, Buat Notifikasi Email,
 * Proses Antrean (pengiriman nyata ke server SMTP uji), aksi massal, izin.
 * HANYA sukamulya_ui_test. Butuh server SMTP uji di SMTP_SINK_PORT (default 2525) yang menulis ke SMTP_SINK_LOG.
 */
if (PHP_SAPI !== 'cli') {http_response_code(404); exit;}
$dbn = getenv('DB_NAME') ?: 'sukamulya_ui_test';
if ($dbn !== 'sukamulya_ui_test') throw new RuntimeException('Only isolated sukamulya_ui_test allowed');
$db = new mysqli('localhost', 'root', '', $dbn);
$base = getenv('TEST_BASE') ?: 'http://127.0.0.1:8097/';
$port = (int) (getenv('SMTP_SINK_PORT') ?: 2525); $sink = getenv('SMTP_SINK_LOG');
$tag = 'eqs' . bin2hex(random_bytes(4)); $pw = bin2hex(random_bytes(20)); $hash = password_hash($pw, PASSWORD_DEFAULT);
foreach (array('admin', 'operator') as $role) {$n = $tag . '_' . $role; $s = $db->prepare('INSERT INTO admins(username,password_hash,role,is_active) VALUES(?,?,?,1)'); $s->bind_param('sss', $n, $hash, $role); $s->execute();}
$fail = 0;
function check($ok, $l) {global $fail; echo ($ok ? 'PASS ' : 'FAIL ') . $l . PHP_EOL; if (!$ok) $fail++;}
function http() {$c = curl_init(); curl_setopt_array($c, array(CURLOPT_RETURNTRANSFER => 1, CURLOPT_COOKIEFILE => '', CURLOPT_PROXY => '', CURLOPT_TIMEOUT => 60)); return $c;}
function go($c, $p, $f = NULL) {global $base; if ($f !== NULL) {$t = ''; foreach (curl_getinfo($c, CURLINFO_COOKIELIST) as $k) {$x = explode("\t", $k); if ($x[5] === 'csrf_sukamulya_cookie') $t = $x[6];} $f['csrf_sukamulya'] = $t; curl_setopt_array($c, array(CURLOPT_POST => 1, CURLOPT_POSTFIELDS => http_build_query($f)));} else curl_setopt($c, CURLOPT_HTTPGET, 1); curl_setopt($c, CURLOPT_URL, $base . $p); $b = (string) curl_exec($c); return array(curl_getinfo($c, CURLINFO_HTTP_CODE), $b);}
function flash($c) {list(, $h) = go($c, 'admin/antrean-email'); preg_match('/class="(?:notice|error ops-flash)"[^>]*>(.*?)</s', $h, $m); return html_entity_decode(trim($m[1] ?? ''));}
$settings_backup = $db->query("SELECT * FROM settings WHERE setting_key LIKE 'smtp\\_%' OR setting_key='notify_email'")->fetch_all(MYSQLI_ASSOC);
$db->query("INSERT INTO citizens(username,display_name,password_hash,is_active,email,notify_email) VALUES('{$tag}_w1','Warga Uji','x',1,'warga1.$tag@example.test',1),('{$tag}_w2','Warga Dua','x',1,'warga2.$tag@example.test',0)");
try {
    $a = http(); go($a, 'admin/login'); go($a, 'admin/login', array('username' => $tag . '_admin', 'password' => $pw));
    $db->query("DELETE FROM settings WHERE setting_key LIKE 'smtp\\_%'"); $db->query("REPLACE INTO settings VALUES('notify_email','0')");
    list($code, $html) = go($a, 'admin/antrean-email');
    check($code === 200 && !preg_match('/A PHP Error|Fatal error|Database Error/', $html), 'Halaman Antrean Email tampil tanpa error');
    foreach (array('SMTP email belum dikonfigurasi', 'Atur SMTP', 'Menunggu dikirim', 'Terkirim', 'Gagal', 'Daftar Antrean Email', 'Proses Antrean Sekarang', 'Waktu Dibuat', 'Percobaan') as $t) check(strpos($html, $t) !== FALSE, "Elemen mockup: $t");
    // Validasi SMTP.
    go($a, 'admin/antrean-email', array('act' => 'smtp_save', 'smtp_host' => '127.0.0.1', 'smtp_port' => 2525, 'smtp_crypto' => 'none', 'from_email' => 'bukan-email'));
    check(strpos(flash($a), 'Email pengirim') !== FALSE || !$db->query("SELECT 1 FROM settings WHERE setting_key='smtp_host'")->num_rows, 'Validasi: email pengirim wajib valid');
    // Simpan SMTP + aktifkan notifikasi; password terenkripsi.
    go($a, 'admin/antrean-email', array('act' => 'smtp_save', 'smtp_host' => '127.0.0.1', 'smtp_port' => $port, 'smtp_crypto' => 'none', 'smtp_user' => '', 'smtp_pass' => 'RahasiaUji123', 'from_email' => 'noreply@example.test', 'from_name' => 'RT Uji', 'notify_email' => 1));
    $enc = $db->query("SELECT setting_value FROM settings WHERE setting_key='smtp_pass_enc'")->fetch_assoc()['setting_value'] ?? '';
    check($enc !== '' && strpos($enc, 'RahasiaUji123') === FALSE, 'Password SMTP tersimpan terenkripsi (bukan teks biasa)');
    check($db->query("SELECT setting_value FROM settings WHERE setting_key='notify_email'")->fetch_assoc()['setting_value'] === '1', 'Toggle notifikasi email tersimpan');
    list(, $html) = go($a, 'admin/antrean-email');
    check(strpos($html, 'SMTP email belum dikonfigurasi') === FALSE && strpos($html, 'RahasiaUji123') === FALSE && strpos($html, '(tersimpan') !== FALSE, 'Peringatan hilang; password tidak pernah ditampilkan');
    // Kirim email uji (pengiriman nyata ke server SMTP uji).
    $before = $sink && is_file($sink) ? filesize($sink) : 0;
    go($a, 'admin/antrean-email', array('act' => 'smtp_test', 'test_email' => 'admin.' . $tag . '@example.test'));
    clearstatcache(); $got = $sink && is_file($sink) ? (string) file_get_contents($sink, FALSE, NULL, $before) : '';
    check(strpos($got, 'admin.' . $tag . '@example.test') !== FALSE && preg_match('/Uji(=20| )SMTP/', $got), 'Kirim Email Uji benar-benar terkirim lewat SMTP');
    // Buat notifikasi → hanya warga yang menyetujui.
    go($a, 'admin/antrean-email', array('act' => 'broadcast', 'subject' => 'Kerja Bakti ' . $tag, 'body' => 'Minggu pagi kerja bakti di lingkungan RT.'));
    $q = $db->query("SELECT * FROM notification_outbox WHERE subject='Kerja Bakti $tag'")->fetch_all(MYSQLI_ASSOC);
    check(count($q) === 1 && $q[0]['recipient'] === "warga1.$tag@example.test" && $q[0]['status'] === 'pending', 'Buat Notifikasi: hanya warga yang menyetujui masuk antrean');
    go($a, 'admin/antrean-email', array('act' => 'broadcast', 'subject' => 'NIK ' . $tag, 'body' => 'NIK 3201234567890123'));
    check(!$db->query("SELECT 1 FROM notification_outbox WHERE subject='NIK $tag'")->num_rows, 'Pesan berisi NIK (16 digit) ditolak');
    // Proses antrean → terkirim.
    $before = is_file($sink) ? filesize($sink) : 0;
    go($a, 'admin/antrean-email', array('act' => 'process'));
    $st = $db->query("SELECT status,attempts FROM notification_outbox WHERE subject='Kerja Bakti $tag'")->fetch_assoc();
    clearstatcache(); $got = is_file($sink) ? (string) file_get_contents($sink, FALSE, NULL, $before) : '';
    check($st['status'] === 'sent' && strpos($got, "warga1.$tag@example.test") !== FALSE, 'Proses Antrean Sekarang mengirim email (status Terkirim)');
    // Aksi massal: coba lagi & hapus.
    $db->query("INSERT INTO notification_outbox(citizen_id,channel,recipient,subject,body,dedupe_key,status,attempts,last_error) VALUES(NULL,'email','x@example.test','Gagal $tag','b','t:$tag:1','failed',5,'err'),(NULL,'email','y@example.test','Antre $tag','b','t:$tag:2','pending',0,NULL)");
    $f1 = $db->query("SELECT id FROM notification_outbox WHERE dedupe_key='t:$tag:1'")->fetch_assoc()['id']; $p1 = $db->query("SELECT id FROM notification_outbox WHERE dedupe_key='t:$tag:2'")->fetch_assoc()['id'];
    $sent = $db->query("SELECT id FROM notification_outbox WHERE subject='Kerja Bakti $tag'")->fetch_assoc()['id'];
    go($a, 'admin/antrean-email', array('act' => 'bulk', 'bulk_action' => 'retry', 'ids' => array($f1, $p1)));
    $r = $db->query("SELECT status,attempts FROM notification_outbox WHERE id=$f1")->fetch_assoc();
    check($r['status'] === 'pending' && (int) $r['attempts'] === 0, 'Aksi massal Coba lagi: gagal → antre');
    go($a, 'admin/antrean-email', array('act' => 'bulk', 'bulk_action' => 'delete', 'ids' => array($f1, $p1, $sent)));
    check(!$db->query("SELECT 1 FROM notification_outbox WHERE id IN ($f1,$p1)")->num_rows && $db->query("SELECT 1 FROM notification_outbox WHERE id=$sent")->num_rows, 'Aksi massal Hapus: antre/gagal terhapus, terkirim tetap tersimpan');
    list(, $html) = go($a, 'admin/antrean-email');
    check(strpos($html, 'data-eq-all') !== FALSE && strpos($html, 'name="ids[]"') !== FALSE, 'Checkbox pilih semua & per baris tersedia');
    // Izin.
    $o = http(); go($o, 'admin/login'); go($o, 'admin/login', array('username' => $tag . '_operator', 'password' => $pw));
    list($code) = go($o, 'admin/antrean-email'); check($code === 403, 'Operator tidak bisa membuka Antrean Email');
    list($code) = go($o, 'admin/antrean-email', array('act' => 'smtp_save', 'smtp_host' => 'evil.example', 'smtp_port' => 25, 'from_email' => 'a@b.c'));
    check($code === 403 && $db->query("SELECT setting_value FROM settings WHERE setting_key='smtp_host'")->fetch_assoc()['setting_value'] === '127.0.0.1', 'Operator tidak bisa mengubah SMTP');
} finally {
    $db->query("DELETE FROM notification_outbox WHERE subject LIKE '%$tag%' OR dedupe_key LIKE '%$tag%'");
    $db->query("DELETE FROM citizens WHERE username LIKE '{$tag}\\_%'");
    $db->query("DELETE FROM admins WHERE username LIKE '{$tag}\\_%'");
    $db->query("DELETE FROM settings WHERE setting_key LIKE 'smtp\\_%' OR setting_key='notify_email'");
    foreach ($settings_backup as $r) {$s = $db->prepare('REPLACE INTO settings(setting_key,setting_value) VALUES(?,?)'); $s->bind_param('ss', $r['setting_key'], $r['setting_value']); $s->execute();}
}
echo $fail ? "$fail FAILED\n" : "ALL PASS (hanya database uji yang diubah)\n";
exit($fail ? 1 : 0);
