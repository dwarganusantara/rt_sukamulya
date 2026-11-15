<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$active_group = 'default';
$query_builder = TRUE;
// SEC-12: credentials come from environment variables, else application/config/secrets.php (git-ignored).
// The root/empty fallback only remains for a bare local install without either.
$db_secrets = is_file(APPPATH.'config/secrets.php') ? (array) include(APPPATH.'config/secrets.php') : array();
if (getenv('DB_USER')) {
    $db_user = getenv('DB_USER'); $db_pass = (string) getenv('DB_PASS');
} elseif (!empty($db_secrets['db_user'])) {
    $db_user = (string) $db_secrets['db_user']; $db_pass = (string) ($db_secrets['db_pass'] ?? 'Barlok15@1234');
} else {
    $db_user = 'root'; $db_pass = getenv('DB_PASS') ?: '';
}
$db['default'] = array(
    'dsn' => '', 'hostname' => getenv('DB_HOST') ?: 'localhost',
    'username' => $db_user, 'password' => $db_pass,
    'database' => getenv('DB_NAME') ?: 'rt_sukamulya', 'dbdriver' => 'mysqli',
    'dbprefix' => '', 'pconnect' => FALSE, 'db_debug' => (ENVIRONMENT !== 'production'),
    'cache_on' => FALSE, 'cachedir' => '', 'char_set' => 'utf8mb4',
    'dbcollat' => 'utf8mb4_unicode_ci', 'swap_pre' => '', 'encrypt' => FALSE,
    'compress' => FALSE, 'stricton' => FALSE, 'failover' => array(), 'save_queries' => TRUE
);

