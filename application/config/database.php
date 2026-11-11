<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$active_group = 'default';
$query_builder = TRUE;
$db['default'] = array(
    'dsn' => '', 'hostname' => getenv('DB_HOST') ?: 'localhost',
    'username' => getenv('DB_USER') ?: 'sukr9483_usr_rt009', 'password' => getenv('DB_PASS') ?: 'rt009@sukamulya',
    'database' => getenv('DB_NAME') ?: 'sukr9483_db_rt009', 'dbdriver' => 'mysqli',
    'dbprefix' => '', 'pconnect' => FALSE, 'db_debug' => (ENVIRONMENT !== 'production'),
    'cache_on' => FALSE, 'cachedir' => '', 'char_set' => 'utf8mb4',
    'dbcollat' => 'utf8mb4_unicode_ci', 'swap_pre' => '', 'encrypt' => FALSE,
    'compress' => FALSE, 'stricton' => FALSE, 'failover' => array(), 'save_queries' => TRUE
);