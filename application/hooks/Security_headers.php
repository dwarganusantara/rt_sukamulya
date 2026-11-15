<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * SECURITY (SEC-04): baseline response headers for every page rendered by CodeIgniter.
 *
 * Deliberately conservative so no existing page changes behaviour:
 *  - no script-src CSP (views use inline scripts); only frame-ancestors to stop clickjacking,
 *  - SAMEORIGIN keeps same-site preview iframes (Tema & Tampilan, live preview) working,
 *  - HSTS only when the request is already HTTPS.
 */
function security_headers_hook()
{
    if (is_cli() || headers_sent()) return;
    header_remove('X-Powered-By');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    // Enforced: directives verified not to affect any existing view (no <object>/<embed>/<base>, all forms same-origin).
    header("Content-Security-Policy: frame-ancestors 'self'; object-src 'none'; base-uri 'self'; form-action 'self'");
    // SEC-11: report-only inventory of script/style sources; never blocks. Tighten once the browser
    // console shows no violations (inline scripts must first move to files or get nonces).
    header("Content-Security-Policy-Report-Only: default-src 'self'; script-src 'self' 'unsafe-inline'; "
        . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' data: https://fonts.gstatic.com; "
        . "img-src 'self' data: blob:; connect-src 'self'; frame-src 'self'; worker-src 'self' blob:");
    header('Referrer-Policy: strict-origin-when-cross-origin');
    if (is_https()) header('Strict-Transport-Security: max-age=31536000');
}
