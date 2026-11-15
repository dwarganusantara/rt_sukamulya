<?php
// Jalankan dari CLI setelah impor database.sql.
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('CLI only'); }
if (($argc !== 3 && $argc !== 4) || strlen($argv[2]) < 10 || ($argc === 4 && !in_array($argv[3], array('admin','operator','bendahara','ketua'), TRUE))) {
    fwrite(STDERR, "Usage: php create_admin.php username password_min_10_karakter [admin|operator|bendahara|ketua]\n");
    exit(1);
}
$db = new mysqli(getenv('DB_HOST') ?: 'localhost', getenv('DB_USER') ?: 'root', getenv('DB_PASS') ?: '', getenv('DB_NAME') ?: 'sukamulya');
if ($db->connect_errno) { fwrite(STDERR, "Database gagal terhubung.\n"); exit(1); }
$stmt = $db->prepare('INSERT INTO admins (username,password_hash,role) VALUES (?,?,?) ON DUPLICATE KEY UPDATE password_hash=VALUES(password_hash),role=VALUES(role)');
$hash = password_hash($argv[2], PASSWORD_DEFAULT);
$role = $argv[3] ?? 'admin';
$stmt->bind_param('sss', $argv[1], $hash, $role);
$stmt->execute();
echo "Akun siap: {$argv[1]} ($role)\n";
