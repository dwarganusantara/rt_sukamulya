<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
 * SECURITY (SEC-08): logout CSRF protection without changing the existing logout links.
 * Links carry a random per-session token (?t=...). A logout request is accepted when it is a POST
 * (already CSRF-checked by CodeIgniter) or a GET with the matching token. A forged GET from another
 * site (e.g. <img src=".../warga/logout">) has no token and gets a confirmation page instead.
 */

if (!function_exists('logout_token')) {
    function logout_token()
    {
        $CI =& get_instance();
        $token = $CI->session->userdata('logout_token');
        if (!is_string($token) || strlen($token) !== 32) {
            $token = bin2hex(random_bytes(16));
            $CI->session->set_userdata('logout_token', $token);
        }
        return $token;
    }
}

if (!function_exists('logout_url')) {
    /** site_url() of a logout route plus the per-session token. */
    function logout_url($path)
    {
        return site_url($path) . '?t=' . logout_token();
    }
}

if (!function_exists('logout_request_ok')) {
    function logout_request_ok()
    {
        $CI =& get_instance();
        if ($CI->input->method(TRUE) === 'POST') return TRUE;
        $stored = $CI->session->userdata('logout_token');
        $given = $CI->input->get('t');
        return is_string($stored) && $stored !== '' && is_string($given) && hash_equals($stored, $given);
    }
}

if (!function_exists('logout_confirm_page')) {
    /** Shown when a logout GET has no valid token. Not logged in → nothing to do, go to the login page. */
    function logout_confirm_page($path, $area)
    {
        $CI =& get_instance();
        $logged_in = $area === 'admin'
            ? ($CI->session->userdata('admin_id') || $CI->session->userdata('platform_owner_name'))
            : $CI->session->userdata('citizen_id');
        if (!$logged_in) redirect($area === 'admin' ? 'admin/login' : 'warga/login');
        $CI->output->set_header('Cache-Control: no-store, private');
        $CI->output->set_output('<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Keluar dari Akun</title><style>'
            . 'body{margin:0;min-height:100vh;display:grid;place-items:center;padding:16px;box-sizing:border-box;font-family:system-ui,-apple-system,"Segoe UI",sans-serif;background:#f3faf5;color:#0b1744}'
            . '.card{max-width:420px;width:100%;background:#fff;border-radius:22px;padding:28px 24px;text-align:center;box-shadow:0 18px 50px #12322422}'
            . 'h1{font-size:21px;margin:0 0 8px}p{margin:0 0 20px;color:#4b5d73;line-height:1.55}.actions{display:flex;gap:10px;justify-content:center;flex-wrap:wrap}'
            . 'button,a{font:inherit;font-weight:800;padding:11px 20px;border-radius:12px;cursor:pointer;text-decoration:none}button{border:0;color:#fff;background:linear-gradient(#06b437,#007821)}a{border:1px solid #168539;color:#075a19;background:#fff}'
            . '</style></head><body><main class="card"><h1>Keluar dari akun?</h1><p>Tekan <strong>Keluar</strong> untuk mengakhiri sesi Anda.</p>'
            . form_open($path) . '<div class="actions"><button type="submit">Keluar</button><a href="' . html_escape(site_url($area === 'admin' ? 'admin' : 'warga')) . '">Batal</a></div>' . form_close()
            . '</main></body></html>');
    }
}
