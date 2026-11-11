<?php
// Semua tanggal aplikasi memakai WIB, sama dengan waktu database.
date_default_timezone_set('Asia/Jakarta');
defined('BASEPATH') OR exit('No direct script access allowed');
/*
 * Keep generated asset and navigation URLs on the current installation path.
 * A hard-coded localhost URL makes CSS requests point to the visitor's own
 * computer after the application is uploaded to hosting.
 *
 * APP_BASE_URL remains available for deployments that need an explicit
 * absolute URL. Otherwise use a same-origin, root-relative URL that works
 * both in /sukamulya/ on XAMPP and in the document root/subfolder on hosting.
 */
$app_base_url = getenv('APP_BASE_URL');
if ($app_base_url !== FALSE && trim($app_base_url) !== '') {
    $config['base_url'] = rtrim(trim($app_base_url), '/').'/';
} else {
    $script_name = isset($_SERVER['SCRIPT_NAME']) ? str_replace('\\', '/', $_SERVER['SCRIPT_NAME']) : '/index.php';
    $base_path = str_replace('\\', '/', dirname($script_name));
    $config['base_url'] = ($base_path === '/' || $base_path === '.') ? '/' : '/'.trim($base_path, '/').'/';
}
$config['index_page'] = '';
$config['uri_protocol'] = 'REQUEST_URI';
$config['url_suffix'] = '';
$config['language'] = 'english';
$config['charset'] = 'UTF-8';
$config['enable_hooks'] = TRUE;
$config['subclass_prefix'] = 'MY_';
$config['composer_autoload'] = FALSE;
$config['permitted_uri_chars'] = 'a-z 0-9~%.:_\-';
$config['enable_query_strings'] = FALSE;
$config['allow_get_array'] = TRUE;
$config['controller_trigger'] = 'c';
$config['function_trigger'] = 'm';
$config['directory_trigger'] = 'd';
$config['log_threshold'] = 1;
$config['log_path'] = '';
$config['log_file_extension'] = '';
$config['log_file_permissions'] = 0644;
$config['log_date_format'] = 'Y-m-d H:i:s';
$config['error_views_path'] = '';
$config['cache_path'] = '';
$config['cache_query_string'] = FALSE;
$config['encryption_key'] = 'rlS+FaBm9JT2otE/RkH+SsDrMakUHZ4dBylTM9qLlWc=';
$config['sess_driver'] = 'files';
$config['sess_cookie_name'] = 'sukamulya_session';
$config['sess_expiration'] = 7200;
$config['sess_save_path'] = sys_get_temp_dir();
$config['sess_match_ip'] = FALSE;
$config['sess_time_to_update'] = 300;
$config['sess_regenerate_destroy'] = FALSE;
$config['cookie_prefix'] = '';
$config['cookie_domain'] = '';
$config['cookie_path'] = '/';
$config['cookie_secure'] = FALSE;
$config['cookie_httponly'] = TRUE;
$config['standardize_newlines'] = FALSE;
$config['global_xss_filtering'] = FALSE;
$config['csrf_protection'] = TRUE;
$config['csrf_token_name'] = 'csrf_sukamulya';
$config['csrf_cookie_name'] = 'csrf_sukamulya_cookie';
$config['csrf_expire'] = 7200;
$config['csrf_regenerate'] = TRUE;
// Background "notice seen" ping; excluded so it does not rotate the token of forms on the page.
$config['csrf_exclude_uris'] = array('warga/keputusan/baca');
$config['compress_output'] = FALSE;
$config['time_reference'] = 'local';
$config['rewrite_short_tags'] = FALSE;
$config['proxy_ips'] = '';
