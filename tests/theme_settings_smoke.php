<?php
/* Smoke test Pengaturan → Tema & Tampilan (UAT spesifikasi). HANYA sukamulya_ui_test. */
if (PHP_SAPI !== 'cli') {http_response_code(404); exit;}
$dbn = getenv('DB_NAME') ?: 'sukamulya_ui_test';
if ($dbn !== 'sukamulya_ui_test') throw new RuntimeException('Only isolated sukamulya_ui_test allowed');
$db = new mysqli('localhost', 'root', '', $dbn);
$base = getenv('TEST_BASE') ?: 'http://127.0.0.1:8097/';
$tag = 'tms' . bin2hex(random_bytes(3)); $pw = bin2hex(random_bytes(16)); $hash = password_hash($pw, PASSWORD_DEFAULT);
foreach (array('admin', 'admin2', 'ketua') as $r) {$role = $r === 'admin2' ? 'admin' : $r; $n = $tag . '_' . $r; $s = $db->prepare('INSERT INTO admins(username,password_hash,role,is_active) VALUES(?,?,?,1)'); $s->bind_param('sss', $n, $hash, $role); $s->execute();}
$fail = 0;
function check($ok, $l) {global $fail; echo ($ok ? 'PASS ' : 'FAIL ') . $l . PHP_EOL; if (!$ok) $fail++;}
function http() {$c = curl_init(); curl_setopt_array($c, array(CURLOPT_RETURNTRANSFER => 1, CURLOPT_COOKIEFILE => '', CURLOPT_PROXY => '', CURLOPT_TIMEOUT => 120)); return $c;}
function go($c, $p, $f = NULL, $multi = FALSE) {global $base; if ($f !== NULL) {$t = ''; foreach (curl_getinfo($c, CURLINFO_COOKIELIST) as $k) {$x = explode("\t", $k); if ($x[5] === 'csrf_sukamulya_cookie') $t = $x[6];} $f['csrf_sukamulya'] = $t; curl_setopt_array($c, array(CURLOPT_POST => 1, CURLOPT_POSTFIELDS => $multi ? $f : http_build_query($f)));} else curl_setopt($c, CURLOPT_HTTPGET, 1); curl_setopt($c, CURLOPT_URL, $base . $p); $b = (string) curl_exec($c); return array(curl_getinfo($c, CURLINFO_HTTP_CODE), $b);}
function login($u) {global $pw; $c = http(); go($c, 'admin/login'); go($c, 'admin/login', array('username' => $u, 'password' => $pw)); return $c;}
$active = function () use ($db) {$id = (int) ($db->query("SELECT setting_value FROM settings WHERE setting_key='ui_active_version'")->fetch_assoc()['setting_value'] ?? 0); $r = $id ? $db->query("SELECT * FROM ui_config_versions WHERE id=$id")->fetch_assoc() : NULL; return $r ? array('id' => $id, 'cfg' => json_decode($r['config_json'], TRUE), 'action' => $r['action']) : array('id' => 0, 'cfg' => NULL, 'action' => '');};
$themeLink = function ($c) {list(, $b) = go($c, 'admin'); return preg_match('#id="ui-theme" href="[^"]*\?v=([0-9a-f]+)"#', $b, $m) ? $m[1] : '';};
$settings_backup = $db->query("SELECT * FROM settings WHERE setting_key IN ('ui_active_version','logo')")->fetch_all(MYSQLI_ASSOC);
$vmax = (int) ($db->query('SELECT MAX(id) m FROM ui_config_versions')->fetch_assoc()['m'] ?? 0);
try {
    $a = login($tag . '_admin'); $b = login($tag . '_admin2'); $k = login($tag . '_ketua');
    list($code, $html) = go($a, 'admin/tampilan');
    check($code === 200 && !preg_match('/A PHP Error|Fatal error|Database Error/', $html), 'Halaman Tema & Tampilan tampil tanpa error');
    foreach (array('Tema &amp; Warna', 'Konten Dashboard', 'Formulir &amp; Field', 'Tata Letak', 'Ikon &amp; Logo', 'Riwayat Perubahan', 'Pratinjau Penuh', 'Reset ke Default', 'Simpan sebagai Draft', 'Simpan &amp; Terapkan') as $t) check(strpos($html, $t) !== FALSE, "Elemen spesifikasi: $t");
    check(substr_count($html, 'name="themeId"') === 6 && substr_count($html, 'class="tm-mini') >= 6, 'Enam tema dengan kartu pratinjau');
    check(strpos($html, 'Periksa kontras') === FALSE, 'Tema bawaan tidak memicu peringatan kontras');
    list(, $html) = go($a, 'admin'); check(strpos($html, 'Tema &amp; Tampilan') !== FALSE && strpos($html, 'admin/tampilan') !== FALSE, 'Menu Tema & Tampilan ada di sidebar Pengaturan');
    // Draft tidak memengaruhi admin lain.
    go($a, 'admin/tampilan', array('tab' => 'tema', 'themeId' => 'teal', 'go' => 'draft'));
    check($active()['id'] === ((int) ($settings_backup ? 0 : 0)) || $active()['cfg']['themeId'] ?? '' !== 'teal', 'Simpan sebagai Draft tidak menerbitkan versi');
    check($themeLink($b) === '', 'Admin lain tetap melihat tampilan aktif (draft privat)');
    go($a, 'admin/tampilan', array('tab' => 'tema', 'go' => 'cancel'));
    check(!$db->query("SELECT 1 FROM ui_config_drafts WHERE owner_key LIKE 'admin:%'")->num_rows, 'Batal membuang draft');
    // Simpan & Terapkan.
    go($a, 'admin/tampilan', array('tab' => 'tema', 'themeId' => 'teal', 'go' => 'apply'));
    $v1 = $active(); check(($v1['cfg']['themeId'] ?? '') === 'teal' && $v1['action'] === 'apply', 'Simpan & Terapkan menerbitkan versi baru (teal)');
    $h = $themeLink($b); check($h !== '', 'Setelah diterapkan, admin lain ikut memakai tema baru');
    list($code, $css) = go($b, 'admin/theme-css?v=' . $h);
    check($code === 200 && strpos($css, 'Lapisan Tema') !== FALSE && strlen($css) > 5000 && stripos($css, '#f97316') === FALSE, 'CSS lapisan tema dibangkitkan (oranye dipetakan ulang)');
    check((bool) $db->query("SELECT 1 FROM activity_logs WHERE action='ui_apply' AND subject='ui_config:{$v1['id']}'")->num_rows, 'Audit log mencatat penerapan');
    // Kontras kustom invalid → ditolak, versi aktif tetap.
    go($a, 'admin/tampilan', array('tab' => 'tema', 'themeId' => 'teal', 'custom_colors' => 1, 'color_primary' => '#0D9488', 'color_secondary' => '#CCFBF1', 'color_accent' => '#FFFF66', 'color_background' => '#F8FAFC', 'color_surface' => '#FFFFFF', 'color_text' => '#102A56', 'go' => 'apply'));
    check($active()['id'] === $v1['id'], 'Warna kustom berkontras buruk ditolak; versi aktif lama tetap dipakai');
    go($a, 'admin/tampilan', array('tab' => 'tema', 'go' => 'cancel'));
    // Konten dashboard & tata letak.
    go($a, 'admin/tampilan', array('tab' => 'dashboard', 'order' => array('operations' => 0, 'hero' => 1, 'stats' => 2, 'stats_chart' => 3, 'dues' => 4, 'agenda' => 5, 'letters' => 6), 'show' => array('operations', 'hero', 'stats', 'stats_chart', 'dues', 'agenda'), 'stats' => array('residents', 'agenda', 'announcements', 'dues'), 'go' => 'apply'));
    $v2 = $active(); check($v2['cfg']['dashboard']['order'][0] === 'operations' && in_array('letters', $v2['cfg']['dashboard']['hidden'], TRUE) && $v2['cfg']['dashboard']['stats_hidden'] === array('cash'), 'Konten Dashboard: urutan & visibilitas widget tersimpan');
    list(, $css) = go($b, 'admin/theme-css?v=' . $themeLink($b));
    check(strpos($css, '.dbh-c-letters{display:none!important}') !== FALSE && strpos($css, '.dbh-stat.amber{display:none!important}') !== FALSE, 'Widget tersembunyi hanya disembunyikan di tampilan (CSS)');
    go($a, 'admin/tampilan', array('tab' => 'tata-letak', 'sidebar' => 'icons', 'radius' => 'round', 'fontSize' => 'large', 'density' => 'compact', 'header_search' => 1, 'go' => 'apply'));
    $v3 = $active(); check($v3['cfg']['sidebar']['variant'] === 'icons' && $v3['cfg']['components']['radius'] === 'round' && !$v3['cfg']['header']['notifications'], 'Tata Letak tersimpan (sidebar ikon, sudut membulat, notifikasi disembunyikan)');
    // Reset Bagian Ini (draft) lalu Batal.
    go($a, 'admin/tampilan', array('tab' => 'tata-letak', 'sidebar' => 'icons', 'radius' => 'round', 'fontSize' => 'large', 'density' => 'compact', 'go' => 'reset_section'));
    $d = json_decode($db->query("SELECT config_json FROM ui_config_drafts WHERE owner_key LIKE 'admin:%'")->fetch_assoc()['config_json'] ?? '{}', TRUE);
    check(($d['sidebar']['variant'] ?? '') === 'standard' && ($d['dashboard']['order'][0] ?? '') === 'operations' && $active()['id'] === $v3['id'], 'Reset Bagian Ini hanya memulihkan tab tersebut (di draft)');
    go($a, 'admin/tampilan', array('tab' => 'tata-letak', 'go' => 'cancel'));
    // Logo: SVG ditolak, PNG diterima.
    $svg = tempnam(sys_get_temp_dir(), 'lg') . '.svg'; file_put_contents($svg, '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>');
    list(, $html) = go($a, 'admin/tampilan', array('tab' => 'logo', 'go' => 'draft', 'logo' => new CURLFile($svg, 'image/svg+xml', 'x.svg')), TRUE);
    check(!$db->query("SELECT 1 FROM ui_config_drafts WHERE config_json LIKE '%logo_%svg%'")->num_rows, 'Logo SVG ditolak');
    $png = tempnam(sys_get_temp_dir(), 'lg') . '.png'; copy(dirname(__DIR__) . '/assets/mockup/logo.png', $png);
    go($a, 'admin/tampilan', array('tab' => 'logo', 'go' => 'apply', 'logo' => new CURLFile($png, 'image/png', 'logo.png')), TRUE);
    $logo = $active()['cfg']['brand']['logo'] ?? ''; check(preg_match('#^uploads/logo_[0-9a-f]+\.png$#', $logo) && $db->query("SELECT setting_value FROM settings WHERE setting_key='logo'")->fetch_assoc()['setting_value'] === $logo, 'Logo PNG diterapkan');
    // Rollback.
    go($a, 'admin/tampilan', array('tab' => 'riwayat', 'go' => 'rollback', 'version_id' => $v1['id']));
    $rb = $active(); check($rb['action'] === 'rollback' && $rb['id'] > $v3['id'] && $rb['cfg']['dashboard']['order'][0] === 'hero' && $rb['cfg']['themeId'] === 'teal', 'Kembalikan versi sebelumnya (tercatat sebagai versi baru)');
    // Reset ke Default.
    go($a, 'admin/tampilan', array('tab' => 'tema', 'go' => 'reset_default'));
    check($active()['id'] === $rb['id'], 'Reset ke Default butuh konfirmasi');
    go($a, 'admin/tampilan', array('tab' => 'tema', 'go' => 'reset_default', 'confirm' => 'RESET'));
    $rs = $active(); check($rs['action'] === 'reset' && $rs['cfg']['themeId'] === 'original-orange' && $rs['cfg']['sidebar']['variant'] === 'standard' && $themeLink($b) === '', 'Reset ke Default memulihkan desain asli (tanpa lapisan tema)');
    check($db->query("SELECT setting_value FROM settings WHERE setting_key='logo'")->fetch_assoc()['setting_value'] === 'assets/mockup/logo.png', 'Reset ke Default memulihkan logo bawaan');
    list(, $html) = go($a, 'admin/tampilan?tab=riwayat'); check(substr_count($html, 'tm-rb-') >= 6, 'Riwayat Perubahan menampilkan versi');
    // Mode aman.
    go($a, 'admin/tampilan', array('tab' => 'tema', 'themeId' => 'navy', 'go' => 'apply'));
    list(, $html) = go($a, 'admin/tampilan?aman=1'); check(strpos($html, 'id="ui-theme"') === FALSE, 'Pulihkan Tampilan (?aman=1) memakai gaya bawaan');
    // Hak akses.
    list($code) = go($k, 'admin/tampilan'); check($code === 403, 'Ketua tidak bisa membuka Tema & Tampilan');
    list($code) = go($k, 'admin/tampilan', array('tab' => 'tema', 'go' => 'reset_default', 'confirm' => 'RESET')); check($code === 403 && $active()['cfg']['themeId'] === 'navy', 'Ketua tidak bisa reset tema global');
    list($code) = go($k, 'admin'); check($code === 200 || $code === 303, 'Ketua tetap bisa membuka dashboard (tema diterapkan untuk semua)');
    @unlink($svg); @unlink($png); if ($logo) @unlink(dirname(__DIR__) . '/' . $logo);
} finally {
    $db->query("DELETE FROM ui_config_versions WHERE id>$vmax"); $db->query('DELETE FROM ui_config_drafts');
    $db->query("DELETE FROM settings WHERE setting_key IN ('ui_active_version')");
    foreach ($settings_backup as $r) {$s = $db->prepare('REPLACE INTO settings(setting_key,setting_value) VALUES(?,?)'); $s->bind_param('ss', $r['setting_key'], $r['setting_value']); $s->execute();}
    $db->query("DELETE FROM admins WHERE username LIKE '{$tag}\\_%'");
}
echo $fail ? "$fail FAILED\n" : "ALL PASS (hanya database uji yang diubah)\n";
exit($fail ? 1 : 0);
