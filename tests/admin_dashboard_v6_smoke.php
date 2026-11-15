<?php
// Read-only dashboard render check. Temporary session files are always removed.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$db = new mysqli('localhost', 'root', '', 'sukamulya');
$admin = $db->query("SELECT id,username,role FROM admins WHERE is_active=1 AND role='admin' ORDER BY id LIMIT 1")->fetch_assoc();
if (!$admin) throw new RuntimeException('Active admin required.');
$sid = bin2hex(random_bytes(16));
$values = array('admin_id' => (int) $admin['id'], 'admin_role' => $admin['role'], 'admin_username' => $admin['username'], '__ci_last_regenerate' => time());
$content = '';
foreach ($values as $key => $value) $content .= $key . '|' . serialize($value);
$files = array();
foreach (array_unique(array(__DIR__ . '/../application/cache/sessions', sys_get_temp_dir(), 'C:/Windows/Temp', 'C:/xampp/tmp')) as $dir) {
    if (!is_dir($dir) || !is_writable($dir)) continue;
    $file = $dir . DIRECTORY_SEPARATOR . 'sukamulya_session' . $sid;
    file_put_contents($file, $content); $files[] = $file;
}
try {
    $ch = curl_init('http://localhost/sukamulya/admin');
    curl_setopt_array($ch, array(CURLOPT_RETURNTRANSFER => TRUE, CURLOPT_FOLLOWLOCATION => TRUE, CURLOPT_COOKIE => 'sukamulya_session=' . $sid, CURLOPT_TIMEOUT => 20));
    $html = curl_exec($ch); $status = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
    $checks = array(
        'HTTP dashboard' => $status === 200,
        'layout v6' => strpos($html, 'class="db6"') !== FALSE,
        'sidebar lama' => strpos($html, 'class="ad-sidebar"') !== FALSE && strpos($html, 'id="sbx-nav"') !== FALSE,
        'ringkasan data' => strpos($html, 'Surat (Proses)') !== FALSE && strpos($html, 'Pengaduan') !== FALSE,
        'panel keuangan' => strpos($html, 'Tren Keuangan RT') !== FALSE,
        'akses cepat' => strpos($html, 'Akses Cepat') !== FALSE,
        'tanpa PHP error' => strpos($html, 'A PHP Error was encountered') === FALSE && strpos($html, 'An uncaught Exception') === FALSE,
    );
    foreach ($checks as $label => $ok) { if (!$ok) throw new RuntimeException('FAIL ' . $label); echo 'PASS ' . $label . PHP_EOL; }
} finally { foreach ($files as $file) if (is_file($file)) unlink($file); }
