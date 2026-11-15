<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Replaces CodeIgniter's CSRF failure response. The stock one renders an error
 * template that this project does not ship, so browsers showed a blank 403.
 * The page stays a 403 and accepts nothing; it only explains the problem and
 * offers "Kembali" (history.back keeps what the user typed in most browsers).
 */
class MY_Security extends CI_Security
{
    /**
     * CodeIgniter re-sends the CSRF cookie on every GET with the value that request
     * carried. A background poll (chat, admin alerts) that started before a POST and
     * finished after it would therefore put the old token back and break the next
     * form submit with a 403. Background requests never render a form, so they
     * skip the cookie write; their token is still read and nothing is relaxed.
     */
    public function csrf_verify()
    {
        if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' && $this->is_background_request()) {
            return $this;
        }
        return parent::csrf_verify();
    }

    private function is_background_request()
    {
        if (strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest') return TRUE;
        // fetch()/XHR in modern browsers; page navigations send "document".
        $dest = strtolower($_SERVER['HTTP_SEC_FETCH_DEST'] ?? '');
        return $dest === 'empty';
    }

    public function csrf_show_error()
    {
        if (!headers_sent()) {
            set_status_header(403);
            header('Content-Type: text/html; charset=UTF-8');
            header('Cache-Control: no-store, private');
        }
        $ajax = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
        if ($ajax) {
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode(array('error' => 'Sesi formulir sudah kedaluwarsa. Muat ulang halaman lalu coba lagi.'));
            exit;
        }
        echo '<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Sesi Formulir Kedaluwarsa</title><style>'
            .'body{margin:0;min-height:100vh;display:grid;place-items:center;padding:16px;box-sizing:border-box;font-family:system-ui,-apple-system,"Segoe UI",sans-serif;background:#f3faf5;color:#0b1744}'
            .'.card{max-width:460px;width:100%;background:#fff;border-radius:22px;padding:30px 26px;text-align:center;box-shadow:0 18px 50px #12322422}'
            .'.icon{width:64px;height:64px;margin:0 auto 14px;border-radius:50%;display:grid;place-items:center;background:#fff4d6;color:#b77900;font-size:32px;font-weight:900}'
            .'h1{font-size:21px;margin:0 0 8px}p{margin:0 0 20px;color:#4b5d73;line-height:1.55;font-size:15px}'
            .'.actions{display:flex;gap:10px;justify-content:center;flex-wrap:wrap}'
            .'button,a{font:inherit;font-weight:800;font-size:15px;padding:11px 20px;border-radius:12px;cursor:pointer;text-decoration:none}'
            .'button{border:0;color:#fff;background:linear-gradient(#06b437,#007821)}a{border:1px solid #168539;color:#075a19;background:#fff}'
            .'</style></head><body><main class="card" role="alert"><div class="icon" aria-hidden="true">!</div>'
            .'<h1>Sesi formulir sudah kedaluwarsa</h1>'
            .'<p>Demi keamanan, data belum dikirim. Tekan <strong>Kembali ke Formulir</strong>, lalu kirim ulang. Isian Anda biasanya masih tersimpan di halaman sebelumnya.</p>'
            .'<div class="actions"><button type="button" onclick="history.back()">Kembali ke Formulir</button><a href="'.htmlspecialchars(config_item('base_url'), ENT_QUOTES, 'UTF-8').'">Ke Beranda</a></div>'
            .'</main></body></html>';
        exit;
    }
}
