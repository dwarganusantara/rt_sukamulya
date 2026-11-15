<?php
/**
 * UAT-15: cek overflow horizontal halaman pada viewport 360 px (dan 1280 px) memakai Chrome headless (DevTools Protocol).
 * Jalankan terhadap DB salinan (lihat tests/e2e_rt16.php), setelah e2e agar halaman detail berisi data.
 *   php tests/responsive_360.php <folder-screenshot>
 * Elemen di dalam wadah ber-overflow-x auto/scroll/hidden (mis. tabel dengan scroll lokal) tidak dihitung sebagai overflow halaman.
 */
if (PHP_SAPI !== 'cli') exit;
const BASE = 'http://localhost/sukamulya/';
$shots = rtrim($argv[1] ?? sys_get_temp_dir() . '/rt360', '/\\');
@mkdir($shots, 0777, TRUE);
$db = new mysqli('localhost', 'root', '', 'sukamulya_e2e');

/* ---- login lewat HTTP, ambil cookie sesi ---- */
function session_cookie($path, $user, $pw)
{
    $c = curl_init();
    curl_setopt_array($c, array(CURLOPT_RETURNTRANSFER => 1, CURLOPT_COOKIEFILE => '', CURLOPT_PROXY => '', CURLOPT_URL => BASE . $path));
    curl_exec($c);
    $tok = ''; foreach (curl_getinfo($c, CURLINFO_COOKIELIST) as $l) {$p = explode("\t", $l); if ($p[5] === 'csrf_sukamulya_cookie') $tok = $p[6];}
    curl_setopt_array($c, array(CURLOPT_POST => 1, CURLOPT_POSTFIELDS => http_build_query(array('username' => $user, 'password' => $pw, 'csrf_sukamulya' => $tok))));
    curl_exec($c);
    foreach (curl_getinfo($c, CURLINFO_COOKIELIST) as $l) {$p = explode("\t", $l); if ($p[5] === 'sukamulya_session') return $p[6];}
    return '';
}

/* ---- klien websocket minimal untuk CDP ---- */
class Cdp
{
    private $s; private $id = 0;
    function __construct($url)
    {
        $u = parse_url($url);
        $this->s = stream_socket_client('tcp://' . $u['host'] . ':' . $u['port'], $en, $es, 10);
        $key = base64_encode(random_bytes(16));
        fwrite($this->s, "GET {$u['path']} HTTP/1.1\r\nHost: {$u['host']}:{$u['port']}\r\nUpgrade: websocket\r\nConnection: Upgrade\r\nSec-WebSocket-Key: $key\r\nSec-WebSocket-Version: 13\r\n\r\n");
        $h = ''; while (strpos($h, "\r\n\r\n") === FALSE) $h .= fread($this->s, 1);
        if (strpos($h, ' 101 ') === FALSE) throw new RuntimeException('WS handshake gagal');
        stream_set_timeout($this->s, 60);
    }
    private function send_frame($data)
    {
        $len = strlen($data); $head = chr(0x81);
        if ($len < 126) $head .= chr(0x80 | $len); elseif ($len < 65536) $head .= chr(0x80 | 126) . pack('n', $len); else $head .= chr(0x80 | 127) . pack('J', $len);
        $mask = random_bytes(4); $out = '';
        for ($i = 0; $i < $len; $i++) $out .= $data[$i] ^ $mask[$i % 4];
        fwrite($this->s, $head . $mask . $out);
    }
    private function read_exact($n) {$b = ''; while (strlen($b) < $n) {$x = fread($this->s, $n - strlen($b)); if ($x === '' || $x === FALSE) throw new RuntimeException('WS terputus'); $b .= $x;} return $b;}
    private function recv()
    {
        $msg = '';
        do {
            $h = $this->read_exact(2); $fin = ord($h[0]) & 0x80; $len = ord($h[1]) & 0x7f;
            if ($len === 126) $len = unpack('n', $this->read_exact(2))[1]; elseif ($len === 127) $len = unpack('J', $this->read_exact(8))[1];
            $msg .= $len ? $this->read_exact($len) : '';
        } while (!$fin);
        return json_decode($msg, TRUE);
    }
    function call($method, $params = array())
    {
        $id = ++$this->id;
        $this->send_frame(json_encode(array('id' => $id, 'method' => $method, 'params' => (object) $params)));
        while (TRUE) {$m = $this->recv(); if (($m['id'] ?? 0) === $id) return $m['result'] ?? $m;}
    }
    function wait_event($name, $timeout = 30)
    {
        $end = time() + $timeout;
        while (time() < $end) {$m = $this->recv(); if (($m['method'] ?? '') === $name) return TRUE;}
        return FALSE;
    }
}

/* ---- akun uji sementara di DB e2e ---- */
$tag = 'r360' . bin2hex(random_bytes(2)); $pw = 'Pw-' . bin2hex(random_bytes(6)); $hash = password_hash($pw, PASSWORD_DEFAULT);
$db->query("INSERT INTO admins (username,password_hash,role,is_active) VALUES ('{$tag}a','$hash','admin',1)"); $aid = $db->insert_id;
$owner = (int) $db->query("SELECT owner_citizen_id FROM kk_families ORDER BY id DESC LIMIT 1")->fetch_row()[0];
$db->query("UPDATE citizens SET password_hash='$hash' WHERE id=$owner"); // warga e2e terakhir (data uji)
$wuser = $db->query("SELECT username FROM citizens WHERE id=$owner")->fetch_row()[0];
$admin_cookie = session_cookie('admin/login', "{$tag}a", $pw);
$warga_cookie = session_cookie('warga/login', $wuser, $pw);
$id = function ($sql) use ($db) {return (int) ($db->query($sql)->fetch_row()[0] ?? 0);};
$pages = array(
    array('anon', ''), array('anon', 'pengumuman'), array('anon', 'warga/login'), array('anon', 'admin/login'),
    array('admin', 'admin'), array('admin', 'admin/keluarga'), array('admin', 'admin/rumah'), array('admin', 'admin/rumah/baru'), array('admin', 'admin/rumah/tautkan'), array('admin', 'admin/penduduk'), array('admin', 'admin/penduduk?tampil=keluarga'), array('admin', 'admin/penduduk/rekap'),
    array('admin', 'admin/penduduk/keluarga/' . $id('SELECT MAX(id) FROM kk_families')), array('admin', 'admin/mutasi?status=all'), array('admin', 'admin/mutasi/' . $id('SELECT MAX(id) FROM resident_mutations')),
    array('admin', 'admin/mutasi/baru?keluarga=' . $id('SELECT MAX(id) FROM kk_families') . '&jenis=masuk'),
    array('admin', 'admin/pengaduan?status=all'), array('admin', 'admin/pengaduan/' . $id('SELECT MAX(id) FROM complaints')), array('admin', 'admin/program/' . $id('SELECT MAX(id) FROM work_programs')),
    array('admin', 'admin/notulen/' . $id('SELECT MAX(id) FROM activity_notes')), array('admin', 'admin/ronda'), array('admin', 'admin/inventaris/pinjam?status=all'),
    array('admin', 'admin/belanja/' . $id('SELECT MAX(id) FROM purchase_requests')), array('admin', 'admin/belanja/baru'), array('admin', 'admin/bansos/' . $id('SELECT MAX(id) FROM aid_programs')),
    array('admin', 'admin/arsip'), array('admin', 'admin/serah-terima/' . $id('SELECT MAX(id) FROM handover_sessions')), array('admin', 'admin/akun'), array('admin', 'admin/audit'),
    array('admin', 'admin/backup'), array('admin', 'admin/tunggakan'), array('admin', 'admin/rekonsiliasi'), array('admin', 'admin/kontak'), array('admin', 'admin/settings'),
    array('admin', 'admin/posts/pengumuman'), array('admin', 'admin/citizens'), array('admin', 'admin/mutasi/baru'), array('admin', 'admin/mutasi/baru?keluarga=' . $id('SELECT MAX(id) FROM kk_families') . '&jenis=ubah_data'),
    array('admin', 'admin/bansos'), array('admin', 'admin/bansos?tab=calon'), array('admin', 'admin/bansos?tab=salur'), array('admin', 'admin/bansos?tab=laporan'), array('admin', 'admin/bansos/baru'),
    array('admin', 'admin/field-tambahan'), array('admin', 'admin/antrean-email'), array('admin', 'admin/citizen/' . $owner), array('admin', 'admin/kk/pengajuan'),
    array('warga', 'warga'), array('warga', 'warga/keluarga'), array('warga', 'warga/mutasi'), array('warga', 'warga/mutasi/ajukan?jenis=ubah_data'), array('warga', 'warga/pengaduan'),
    array('warga', 'warga/pengaduan/buat'), array('warga', 'warga/pengaduan/' . $id("SELECT MAX(id) FROM complaints WHERE citizen_id=$owner")), array('warga', 'warga/ronda'),
    array('warga', 'warga/kontak-penting'), array('warga', 'warga/profil/edit'), array('warga', 'warga/iuran'),
);

/* ---- jalankan Chrome headless ---- */
$port = 9333;
$profile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'rt360-profile-' . $tag;
$chrome = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
$proc = proc_open(array($chrome, '--headless=new', '--disable-gpu', '--no-first-run', '--no-default-browser-check', '--hide-scrollbars', "--remote-debugging-port=$port", "--user-data-dir=$profile", 'about:blank'),
    array(0 => array('pipe', 'r'), 1 => array('file', 'NUL', 'w'), 2 => array('file', 'NUL', 'w')), $pipes, NULL, NULL, array('bypass_shell' => TRUE));
$ws = '';
for ($i = 0; $i < 50 && !$ws; $i++) {usleep(200000); $j = @file_get_contents("http://127.0.0.1:$port/json/list"); foreach ((array) json_decode((string) $j, TRUE) as $t) if (($t['type'] ?? '') === 'page') $ws = $t['webSocketDebuggerUrl'];}
$report = array(); $fail = 0;
try {
    if (!$ws) throw new RuntimeException('Chrome DevTools tidak tersedia');
    $cdp = new Cdp($ws);
    $cdp->call('Page.enable'); $cdp->call('Network.enable');
    $js = <<<'JS'
(() => { const w = document.documentElement.clientWidth, sw = document.documentElement.scrollWidth, bad = [];
 if (sw > w + 1) document.querySelectorAll('body *').forEach(e => { const r = e.getBoundingClientRect(); if (r.width <= 0 || r.right <= w + 1) return;
   const cs = getComputedStyle(e); if (cs.position === 'fixed') return;
   let p = e.parentElement, scoped = false; while (p && p !== document.body) { if (/(auto|scroll|hidden|clip)/.test(getComputedStyle(p).overflowX)) { scoped = true; break; } p = p.parentElement; }
   if (!scoped) bad.push(e.tagName.toLowerCase() + (e.id ? '#' + e.id : '') + (typeof e.className === 'string' && e.className ? '.' + e.className.trim().split(/\s+/).slice(0, 2).join('.') : '') + ' (right=' + Math.round(r.right) + ')'); });
 return JSON.stringify({ w, sw, title: document.title, bad: bad.slice(0, 6), err: /A PHP Error|Database Error|uncaught Exception/i.test(document.body.innerText) }); })()
JS;
    foreach (array(array(360, 800, TRUE), array(1280, 900, FALSE)) as $vp) {
        list($w, $h, $mobile) = $vp;
        $cdp->call('Emulation.setDeviceMetricsOverride', array('width' => $w, 'height' => $h, 'deviceScaleFactor' => 1, 'mobile' => $mobile));
        foreach ($pages as $pg) {
            list($who, $path) = $pg;
            $cdp->call('Network.clearBrowserCookies');
            if ($who !== 'anon') $cdp->call('Network.setCookie', array('name' => 'sukamulya_session', 'value' => $who === 'admin' ? $admin_cookie : $warga_cookie, 'domain' => 'localhost', 'path' => '/', 'httpOnly' => TRUE));
            $cdp->call('Page.navigate', array('url' => BASE . $path));
            $cdp->wait_event('Page.loadEventFired', 30);
            usleep(250000);
            $r = json_decode($cdp->call('Runtime.evaluate', array('expression' => $js, 'returnByValue' => TRUE))['result']['value'] ?? '{}', TRUE);
            $ok = $r && $r['sw'] <= $r['w'] + 1 && !$r['err'];
            if (!$ok) $fail++;
            $name = $w . '-' . $who . '-' . trim(preg_replace('/[^a-z0-9]+/i', '-', $path ?: 'beranda'), '-');
            if ($w === 360) {$img = $cdp->call('Page.captureScreenshot', array('format' => 'png', 'captureBeyondViewport' => FALSE)); if (!empty($img['data'])) file_put_contents("$shots/$name.png", base64_decode($img['data']));}
            $report[] = ($ok ? 'PASS ' : 'FAIL ') . "{$w}px $who /$path" . ($ok ? '' : ' :: lebar dokumen ' . ($r['sw'] ?? '?') . ' > ' . ($r['w'] ?? '?') . ($r['err'] ?? FALSE ? ' [error PHP]' : '') . ' :: ' . implode(', ', $r['bad'] ?? array()));
        }
    }
} finally {
    if (is_resource($proc)) {proc_terminate($proc); proc_close($proc);}
    $db->query("DELETE FROM activity_logs WHERE admin_id=$aid"); $db->query("DELETE FROM chat_presence WHERE user_type='admin' AND user_id=$aid"); $db->query("DELETE FROM admins WHERE id=$aid");
}
echo implode(PHP_EOL, $report), PHP_EOL, $fail ? "$fail GAGAL" : 'SEMUA LULUS', PHP_EOL, "Screenshot 360px: $shots", PHP_EOL;
exit($fail ? 1 : 0);
