<?php
/*
 * Security retest for the hardening patches SEC-02, SEC-04, SEC-05, SEC-06.
 * HANYA untuk database salinan sukamulya_ui_test. Jalankan server uji:
 *   set DB_NAME=sukamulya_ui_test && C:\xampp\php\php.exe -S 127.0.0.1:8097 index.php
 *   set DB_NAME=sukamulya_ui_test && C:\xampp\php\php.exe tests\security_hardening_smoke.php
 * Optional: set APACHE_BASE=http://localhost/sukamulya/ to also check SEC-01 (read-only HEAD/GET probes).
 */
if (PHP_SAPI !== 'cli') {http_response_code(404); exit;}
$dbn = getenv('DB_NAME') ?: 'sukamulya_ui_test';
if ($dbn !== 'sukamulya_ui_test') throw new RuntimeException('Only isolated sukamulya_ui_test allowed');
$db = new mysqli('localhost', 'root', '', $dbn);
$db->set_charset('utf8mb4');
$base = getenv('TEST_BASE') ?: 'http://127.0.0.1:8097/';
$tag = 'sec' . bin2hex(random_bytes(4));
$pw = bin2hex(random_bytes(12));
$hash = password_hash($pw, PASSWORD_DEFAULT);
$fail = 0;
function check($ok, $label) {global $fail; echo ($ok ? 'PASS ' : 'FAIL ') . $label . PHP_EOL; if (!$ok) $fail++;}

class Http {
    public $c, $html, $code, $headers;
    function __construct() {$this->c = curl_init(); curl_setopt_array($this->c, array(CURLOPT_RETURNTRANSFER => TRUE, CURLOPT_COOKIEFILE => '', CURLOPT_TIMEOUT => 30, CURLOPT_PROXY => '', CURLOPT_HEADER => TRUE));}
    function cookie($name) {foreach (curl_getinfo($this->c, CURLINFO_COOKIELIST) as $cookie) {$p = explode("\t", $cookie); if (($p[5] ?? '') === $name) return $p[6];} return '';}
    function go($path, $fields = NULL) {
        global $base;
        if ($fields !== NULL) {$fields['csrf_sukamulya'] = $this->cookie('csrf_sukamulya_cookie'); curl_setopt_array($this->c, array(CURLOPT_POST => TRUE, CURLOPT_POSTFIELDS => http_build_query($fields)));}
        else curl_setopt($this->c, CURLOPT_HTTPGET, TRUE);
        curl_setopt($this->c, CURLOPT_URL, $base . $path);
        $raw = (string) curl_exec($this->c);
        $size = curl_getinfo($this->c, CURLINFO_HEADER_SIZE);
        $this->headers = substr($raw, 0, $size); $this->html = substr($raw, $size);
        $this->code = curl_getinfo($this->c, CURLINFO_HTTP_CODE);
        return $this;
    }
    function post($path, $fields) {$this->go($path); return $this->go($path, $fields);}
}

$db->query("INSERT INTO admins(username,password_hash,role,is_active) VALUES('{$tag}_admin','$hash','admin',1)");
$db->query("INSERT INTO citizens(username,display_name,full_name,gender,password_hash,is_active) VALUES('{$tag}_warga','Warga Uji','Warga Uji','male','$hash',1)");

try {
    /* SEC-04 security headers on CI-rendered pages */
    $h = (new Http())->go('admin/login');
    check($h->code === 200, 'Admin login page still renders');
    check(stripos($h->headers, 'X-Frame-Options: SAMEORIGIN') !== FALSE, 'X-Frame-Options SAMEORIGIN present');
    check(stripos($h->headers, "frame-ancestors 'self'") !== FALSE, 'CSP frame-ancestors present');
    check(stripos($h->headers, 'X-Content-Type-Options: nosniff') !== FALSE, 'nosniff present');
    check(stripos($h->headers, 'Referrer-Policy: strict-origin-when-cross-origin') !== FALSE, 'Referrer-Policy present');
    check(stripos($h->headers, 'X-Powered-By') === FALSE, 'X-Powered-By removed');
    check(preg_match('/Set-Cookie: sukamulya_session=[^\r\n]*HttpOnly[^\r\n]*SameSite=Lax/i', $h->headers) === 1, 'Session cookie HttpOnly + SameSite=Lax');

    /* SEC-02 admin brute force: 8 failures lock the account, correct password then refused */
    $a = new Http();
    for ($i = 1; $i <= 8; $i++) $a->post('admin/login', array('username' => $tag . '_admin', 'password' => 'salah' . $i));
    check(strpos($a->html, 'Username atau password salah.') !== FALSE, 'Wrong password keeps the existing error message');
    $a->post('admin/login', array('username' => $tag . '_admin', 'password' => $pw));
    check($a->code === 200 && strpos($a->html, 'Terlalu banyak percobaan login') !== FALSE, 'Admin account locked after 8 failures (correct password refused)');
    $other = (new Http())->post('admin/login', array('username' => $tag . '_admin', 'password' => $pw));
    check(strpos($other->html, 'Terlalu banyak percobaan login') !== FALSE, 'Lock follows the account, not the browser session');
    $db->query("DELETE FROM platform_login_attempts WHERE created_at > NOW() - INTERVAL 1 HOUR");
    $ok = (new Http())->post('admin/login', array('username' => $tag . '_admin', 'password' => $pw));
    check(in_array($ok->code, array(302, 303, 307), TRUE), 'Admin login works again after the window (counter cleared)');

    /* Success clears only that account's counter; few failures never block */
    $b = new Http();
    for ($i = 1; $i <= 3; $i++) $b->post('admin/login', array('username' => $tag . '_admin', 'password' => 'x' . $i));
    $b->post('admin/login', array('username' => $tag . '_admin', 'password' => $pw));
    check(in_array($b->code, array(302, 303, 307), TRUE), '3 failures then correct password still logs in');
    $left = (int) $db->query("SELECT COUNT(*) n FROM platform_login_attempts WHERE ip_hash='" . hash('sha256', 'login|admin|user|' . $tag . '_admin') . "'")->fetch_assoc()['n'];
    check($left === 0, 'Successful login clears that account counter');

    /* SEC-02 citizen brute force */
    $w = new Http();
    for ($i = 1; $i <= 8; $i++) $w->post('warga/login', array('username' => $tag . '_warga', 'password' => 'salah' . $i));
    $w->post('warga/login', array('username' => $tag . '_warga', 'password' => $pw));
    check(strpos($w->html, 'Terlalu banyak percobaan login') !== FALSE, 'Citizen account locked after 8 failures');
    $db->query("DELETE FROM platform_login_attempts WHERE created_at > NOW() - INTERVAL 1 HOUR");

    /* SEC-05 citizen logout rotates the session ID */
    $w = new Http();
    $w->post('warga/login', array('username' => $tag . '_warga', 'password' => $pw));
    check(in_array($w->code, array(302, 303, 307), TRUE), 'Citizen login works');
    $before = $w->cookie('sukamulya_session');
    $w->go('warga');
    preg_match('#warga/logout\?t=([a-f0-9]{32})#', $w->html, $m);
    check(!empty($m[1]), 'Resident pages render the tokenised logout link');
    $w->go('warga/logout?t=' . ($m[1] ?? ''));
    $after = $w->cookie('sukamulya_session');
    check($before !== '' && $after !== '' && $before !== $after, 'Citizen logout issues a new session ID');
    $w->go('warga');
    check(in_array($w->code, array(302, 303, 307), TRUE), 'After logout the resident area redirects to login');
    $replay = new Http();
    curl_setopt($replay->c, CURLOPT_COOKIE, 'sukamulya_session=' . $before);
    $replay->go('warga');
    check(in_array($replay->code, array(302, 303, 307), TRUE), 'Old pre-logout session ID cannot open the resident area');

    /* SEC-06 registration password minimum (same as ganti password) */
    $r = (new Http())->post('warga/daftar', array('display_name' => 'Pendaftar Uji', 'gender' => 'male', 'username' => $tag . '_short', 'password' => 'abc', 'password_confirmation' => 'abc'));
    check(strpos($r->html, 'Password minimal 8 karakter.') !== FALSE, 'Registration rejects password shorter than 8');
    check(!$db->query("SELECT id FROM citizens WHERE username='{$tag}_short'")->fetch_assoc(), 'No account created for short password');
    $r = (new Http())->post('warga/daftar', array('display_name' => 'Pendaftar Uji', 'gender' => 'male', 'username' => $tag . '_long', 'password' => 'abcdefgh', 'password_confirmation' => 'abcdefgh'));
    check(in_array($r->code, array(302, 303, 307), TRUE) && $db->query("SELECT id FROM citizens WHERE username='{$tag}_long'")->fetch_assoc(), 'Registration with 8+ characters still works');

    /* SEC-08 logout CSRF: forged GET without token does not log out; POST and tokenised link do */
    $w = new Http();
    $w->post('warga/login', array('username' => $tag . '_warga', 'password' => $pw));
    $w->go('warga/logout');
    check($w->code === 200 && strpos($w->html, 'Keluar dari akun?') !== FALSE, 'Logout GET without token shows confirmation');
    $w->go('warga');
    check($w->code === 200, 'Forged logout GET did not end the resident session');
    $w->go('warga/logout?t=' . str_repeat('0', 32));
    $w->go('warga');
    check($w->code === 200, 'Wrong logout token did not end the session');
    $w->go('warga/logout', array());
    $w->go('warga');
    check(in_array($w->code, array(302, 303, 307), TRUE), 'Logout by POST (confirmation form) ends the session');
    $ad = (new Http())->post('admin/login', array('username' => $tag . '_admin', 'password' => $pw));
    $ad->go('admin');
    preg_match('#admin/logout\?t=([a-f0-9]{32})#', $ad->html, $m);
    check(!empty($m[1]), 'Admin header renders the tokenised logout link');
    $ad->go('admin/logout');
    $ad->go('admin');
    check($ad->code === 200, 'Admin forged logout GET did not end the session');
    $ad->go('admin/logout?t=' . ($m[1] ?? ''));
    $ad->go('admin');
    check(in_array($ad->code, array(302, 303, 307), TRUE), 'Admin tokenised logout link still works');
    $anon = (new Http())->go('warga/logout');
    check(in_array($anon->code, array(302, 303, 307), TRUE), 'Logout while not logged in just redirects to login');

    /* SEC-09 inactive account notice only with the right password */
    $db->query("INSERT INTO admins(username,password_hash,role,is_active) VALUES('{$tag}_off','$hash','operator',0)");
    $x = (new Http())->post('admin/login', array('username' => $tag . '_off', 'password' => 'salah-sekali'));
    check(strpos($x->html, 'Username atau password salah.') !== FALSE && strpos($x->html, 'dinonaktifkan') === FALSE, 'Inactive account + wrong password gives the generic error');
    $x = (new Http())->post('admin/login', array('username' => $tag . '_off', 'password' => $pw));
    check(strpos($x->html, 'dinonaktifkan') !== FALSE, 'Inactive account + right password still explains it is deactivated');
    $x->go('admin');
    check(in_array($x->code, array(302, 303, 307), TRUE), 'Inactive account still cannot enter');
    $db->query("DELETE FROM platform_login_attempts WHERE created_at > NOW() - INTERVAL 1 HOUR");

    /* SEC-07 letter stamp/signature */
    $cid = (int) $db->query("SELECT id FROM citizens WHERE username='{$tag}_warga'")->fetch_assoc()['id'];
    $db->query("INSERT INTO service_requests(citizen_id,type,subject,description,status,created_at) VALUES($cid,'domisili','Uji $tag','Uji','diajukan',NOW())");
    $pending = $db->insert_id;
    $db->query("INSERT INTO service_request_details(request_id,applicant_name,address,purpose) VALUES($pending,'Warga Uji','Alamat','Uji')");
    $db->query("INSERT INTO service_requests(citizen_id,type,subject,description,status,created_at) VALUES($cid,'domisili','Uji $tag','Uji','disetujui',NOW())");
    $approved = $db->insert_id;
    $db->query("INSERT INTO service_request_details(request_id,applicant_name,address,purpose,letter_number,approved_at) VALUES($approved,'Warga Uji','Alamat','Uji','001/UJI/$tag',NOW())");
    $anon = new Http();
    foreach (array('logo', 'stamp', 'signature') as $k) {$anon->go('warga/aset-surat/' . $k . '?id=' . $approved); check($anon->code === 404, "Anonymous cannot fetch $k");}
    $w = (new Http())->post('warga/login', array('username' => $tag . '_warga', 'password' => $pw));
    $w->go('warga/aset-surat/logo');
    check($w->code === 200, 'Resident still gets the letter-head logo');
    foreach (array('stamp', 'signature') as $k) {
        $w->go('warga/aset-surat/' . $k); check($w->code === 404, "Resident without approved letter cannot fetch $k");
        $w->go('warga/aset-surat/' . $k . '?id=' . $pending); check($w->code === 404, "Resident cannot fetch $k via a pending request");
        $w->go('warga/aset-surat/' . $k . '?id=' . $approved); check($w->code === 200, "Resident fetches $k for own approved letter");
    }
    $w->go('warga/surat/' . $approved);
    check($w->code === 200 && strpos($w->html, 'aset-surat/signature?id=' . $approved) !== FALSE, 'Approved letter still prints signature and stamp');
    $w->go('warga/surat/' . $pending . '?preview=1');
    check($w->code === 200 && strpos($w->html, 'aset-surat/signature') === FALSE, 'Preview of a pending letter has no signature');
    $w->go('warga/ajukan');
    check(strpos($w->html, 'aset-surat/signature') === FALSE && strpos($w->html, 'dibubuhkan setelah surat disetujui') !== FALSE, 'Request form preview shows the placeholder instead of the signature');
    $other = (new Http())->post('admin/login', array('username' => $tag . '_admin', 'password' => $pw));
    $other->go('warga/aset-surat/signature');
    check($other->code === 200, 'Staff still see the signature (letter settings preview)');
    $other->go('warga/surat/' . $pending . '?preview=1');
    check(strpos($other->html, 'aset-surat/signature') !== FALSE, 'Staff preview of a pending letter keeps the signature');

    /* SEC-10 session storage */
    $sid = $w->cookie('sukamulya_session');
    check($sid !== '' && is_file(__DIR__ . '/../application/cache/sessions/sukamulya_session' . $sid), 'Session file stored in application/cache/sessions');

    /* SEC-11 CSP */
    $h = (new Http())->go('warga/login');
    check(stripos($h->headers, "object-src 'none'; base-uri 'self'; form-action 'self'") !== FALSE, 'Enforced CSP has object-src/base-uri/form-action');
    check(stripos($h->headers, 'Content-Security-Policy-Report-Only:') !== FALSE, 'Report-only CSP present');

    /* SEC-01 (optional, Apache only) */
    $apache = getenv('APACHE_BASE');
    if ($apache) {
        foreach (array('.git/config', 'database.sql', 'database%20sql/database.sql', 'tmp/super-admin-role-files.json', 'README.md', 'docs/KK_SCAN.md', 'tests/kk_fields_test.php', 'create_admin.php') as $p) {
            $c = curl_init($apache . $p); curl_setopt_array($c, array(CURLOPT_RETURNTRANSFER => TRUE, CURLOPT_NOBODY => TRUE, CURLOPT_PROXY => '')); curl_exec($c);
            check(in_array(curl_getinfo($c, CURLINFO_HTTP_CODE), array(403, 404), TRUE), 'Blocked from web: ' . $p);
        }
        foreach (array('', 'admin/login', 'warga/login', 'assets/admin-ops.css') as $p) {
            $c = curl_init($apache . $p); curl_setopt_array($c, array(CURLOPT_RETURNTRANSFER => TRUE, CURLOPT_PROXY => '')); curl_exec($c);
            check(curl_getinfo($c, CURLINFO_HTTP_CODE) === 200, 'Still public: /' . $p);
        }
    }
} finally {
    $db->query("DELETE d FROM service_request_details d JOIN service_requests r ON r.id=d.request_id WHERE r.subject='Uji $tag'");
    $db->query("DELETE FROM service_requests WHERE subject='Uji $tag'");
    $db->query("DELETE FROM admins WHERE username LIKE '{$tag}\\_%'");
    $db->query("DELETE FROM citizens WHERE username LIKE '{$tag}\\_%'");
    $db->query("DELETE FROM platform_login_attempts WHERE created_at > NOW() - INTERVAL 1 HOUR");
}
echo $fail ? "$fail FAILED" . PHP_EOL : 'ALL PASS (hanya database uji yang diubah)' . PHP_EOL;
exit($fail ? 1 : 0);
