<?php
/*
 * PHP compatibility check for system/libraries/Encryption.php (used for settings.smtp_pass_enc).
 *   php tests/encryption_compat_test.php encrypt            -> prints a ciphertext made with the app key
 *   php tests/encryption_compat_test.php decrypt <cipher>   -> decrypts it (run with another PHP version)
 *   php tests/encryption_compat_test.php                    -> round trip on the current PHP
 * Reads only application/config/config.php; touches no database.
 */
if (PHP_SAPI !== 'cli') {http_response_code(404); exit;}
error_reporting(E_ALL);
set_error_handler(function ($no, $msg, $file, $line) {fwrite(STDERR, "PHP notice/deprecation: $msg ($file:$line)\n"); exit(2);});
define('BASEPATH', __DIR__ . '/../system/');
define('APPPATH', __DIR__ . '/../application/');
define('ENVIRONMENT', 'testing');
function log_message() {}
function is_php($v) {return version_compare(PHP_VERSION, $v, '>=');}
function show_error($m) {throw new RuntimeException($m);}
function config_item($k) {static $c; if ($c === NULL) {$config = array(); require __DIR__ . '/../application/config/config.php'; $c = $config;} return $c[$k] ?? NULL;}
function get_instance() {return NULL;}
require BASEPATH . 'libraries/Encryption.php';
$enc = new CI_Encryption();
$secret = 'smtp-pass-Ujı-' . PHP_MAJOR_VERSION;
$mode = $argv[1] ?? 'roundtrip';
if ($mode === 'encrypt') {echo $enc->encrypt('cross-version-secret'), PHP_EOL; exit(0);}
if ($mode === 'decrypt') {
    $plain = $enc->decrypt($argv[2] ?? '');
    echo ($plain === 'cross-version-secret' ? 'PASS' : 'FAIL') . ' decrypt on PHP ' . PHP_VERSION . PHP_EOL;
    exit($plain === 'cross-version-secret' ? 0 : 1);
}
$ok = $enc->decrypt($enc->encrypt($secret)) === $secret;
echo ($ok ? 'PASS' : 'FAIL') . ' encrypt/decrypt round trip on PHP ' . PHP_VERSION . PHP_EOL;
exit($ok ? 0 : 1);
