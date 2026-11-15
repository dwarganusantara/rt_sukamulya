<?php
/*
 * Smoke test: semua menu sidebar melalui proses langganan + Super Admin bebas mengubah status akses.
 * HANYA untuk sukamulya_ui_test. Server uji butuh identitas Admin Pusat uji (CI_ENV=testing,
 * application/config/testing/central_admin.php) dengan username/password di env PUSAT_USER/PUSAT_PASS.
 */
if (PHP_SAPI !== 'cli') {http_response_code(404); exit;}
$dbn = getenv('DB_NAME') ?: 'sukamulya_ui_test';
if ($dbn !== 'sukamulya_ui_test') throw new RuntimeException('Only isolated sukamulya_ui_test allowed');
$db = new mysqli('localhost', 'root', '', $dbn);
$base = getenv('TEST_BASE') ?: 'http://127.0.0.1:8098/';
$tag = 'sam' . bin2hex(random_bytes(4)); $pw = bin2hex(random_bytes(20)); $hash = password_hash($pw, PASSWORD_DEFAULT);
foreach (array('admin', 'operator') as $role) {$name = $tag . '_' . $role; $s = $db->prepare('INSERT INTO admins(username,password_hash,role,is_active) VALUES(?,?,?,1)'); $s->bind_param('sss', $name, $hash, $role); $s->execute();}
$fail = 0;
function check($ok, $label) {global $fail; echo ($ok ? 'PASS ' : 'FAIL ') . $label . PHP_EOL; if (!$ok) $fail++;}
class Http {
    public $c, $html, $code, $location;
    function __construct() {$this->c = curl_init(); curl_setopt_array($this->c, array(CURLOPT_RETURNTRANSFER => TRUE, CURLOPT_COOKIEFILE => '', CURLOPT_TIMEOUT => 30, CURLOPT_PROXY => '', CURLOPT_HEADER => TRUE));}
    function token() {foreach (curl_getinfo($this->c, CURLINFO_COOKIELIST) as $cookie) {$p = explode("\t", $cookie); if (($p[5] ?? '') === 'csrf_sukamulya_cookie') return $p[6];} return '';}
    function go($path, $fields = NULL, $headers = array()) {
        global $base;
        if ($fields !== NULL) {$fields['csrf_sukamulya'] = $this->token(); curl_setopt_array($this->c, array(CURLOPT_POST => TRUE, CURLOPT_POSTFIELDS => http_build_query($fields)));}
        else curl_setopt($this->c, CURLOPT_HTTPGET, TRUE);
        curl_setopt_array($this->c, array(CURLOPT_URL => $base . $path, CURLOPT_HTTPHEADER => $headers));
        $raw = (string) curl_exec($this->c); $size = curl_getinfo($this->c, CURLINFO_HEADER_SIZE);
        $this->location = preg_match('/^Location:\s*(\S+)/mi', substr($raw, 0, $size), $m) ? $m[1] : '';
        $this->html = substr($raw, $size); $this->code = curl_getinfo($this->c, CURLINFO_HTTP_CODE); return $this;
    }
    function login($user, $pass, $path = 'admin/login') {$this->go($path); $this->go($path, array('username' => $user, 'password' => $pass)); return $this;}
    function clean() {return !preg_match('/Fatal error|A PHP Error|A Database Error|Severity:/', $this->html);}
}
$type = function ($code) use ($db) {$r = $db->query("SELECT commercial_type FROM subscription_catalog WHERE feature_code='" . $db->real_escape_string($code) . "'")->fetch_assoc(); return $r['commercial_type'] ?? 'FREE';};
$backup = $db->query('SELECT * FROM subscription_catalog')->fetch_all(MYSQLI_ASSOC);
try {
    $a = (new Http())->login($tag . '_admin', $pw);
    $a->go('admin/langganan?tab=fitur');
    preg_match_all('/<tr id="sp-row-[^"]+" data-sp-row/', $a->html, $rows);
    check($a->code === 200 && $a->clean() && count($rows[0]) >= 35, 'Aktivasi Fitur menampilkan semua menu sidebar (' . count($rows[0]) . ' baris)');
    foreach (array('Data Keluarga / KK', 'Profil RT', 'Backup Data', 'Gambar Halaman', 'Jenis Surat &amp; Ikon') as $n) check(strpos($a->html, '<strong>' . $n . '</strong>') !== FALSE, "Menu $n ada di Aktivasi Fitur");
    check(strpos($a->html, 'sp-type-form') === FALSE, 'Admin RT tidak mendapat kontrol ubah status (baca saja)');
    $a->go('admin/langganan/fitur', array('feature_code' => 'menu:admin/citizens', 'commercial_type' => 'FREE', 'return_tab' => 'fitur'));
    check($a->code !== 200 || strpos($a->html, 'disimpan') === FALSE, 'Admin RT tidak bisa mengubah status lewat POST palsu');

    $su = (new Http())->login(getenv('PUSAT_USER'), getenv('PUSAT_PASS'), 'platform/login');
    $su->go('admin/langganan?tab=fitur');
    check($su->code === 200 && $su->clean() && strpos($su->html, 'Mode Super Admin') !== FALSE && substr_count($su->html, 'sp-type-form') >= 30, 'Super Admin melihat kontrol status di setiap fitur');

    // Toggle OFF fitur Gratis → Berlangganan.
    $su->go('admin/langganan/fitur', array('feature_code' => 'menu:admin/citizens', 'commercial_type' => 'PREMIUM', 'return_tab' => 'fitur'));
    check($type('menu:admin/citizens') === 'PREMIUM' && strpos($su->location, 'admin/langganan?tab=fitur') !== FALSE, 'Super Admin: Data Warga → Berlangganan, kembali ke Aktivasi Fitur');
    check((int) $db->query("SELECT COUNT(*) n FROM subscription_audit_logs WHERE action='catalog' AND after_json LIKE '%menu:admin%citizens%PREMIUM%'")->fetch_assoc()['n'] > 0, 'Perubahan tercatat di subscription_audit_logs');

    $a->go('admin'); check(preg_match('#data-sub-feature="admin/citizens"#', $a->html) === 1, 'Sidebar RT: Data Warga berlabel Premium (terkunci)');
    $a->go('admin/citizens'); check($a->code === 403 && strpos($a->html, 'Fitur Berlangganan') !== FALSE, 'URL langsung Data Warga ditolak 403 (Berlangganan)');
    $a->go('admin/citizen/1'); check($a->code === 403, 'Sub-halaman/form Data Warga ditolak');
    $a->go('admin/citizens', NULL, array('Accept: application/json', 'X-Requested-With: XMLHttpRequest'));
    $j = json_decode($a->html, TRUE); check(($j['error'] ?? '') === 'SUBSCRIPTION_REQUIRED', 'AJAX Data Warga → SUBSCRIPTION_REQUIRED');
    $a->go('admin/langganan/status?feature=admin%2Fcitizens', NULL, array('Accept: application/json', 'X-Requested-With: XMLHttpRequest'));
    $j = json_decode($a->html, TRUE); check(($j['status'] ?? '') === 'SUBSCRIPTION_REQUIRED' && ($j['name'] ?? '') === 'Data Keluarga / KK', 'Endpoint popup mengenali menu Data Keluarga / KK');
    $a->go('admin/langganan?tab=fitur'); check(strpos($a->html, 'data-sub-feature="admin/citizens"') !== FALSE, 'Aktivasi Fitur: Data Warga Nonaktif + tombol Berlangganan');

    // Override GRANT (lisensi) membuka akses lagi tanpa mengubah kategori.
    $db->query("INSERT INTO subscription_overrides(tenant_id,feature_code,access_mode,starts_at,ends_at,reason,created_by) VALUES(1,'menu:admin/citizens','GRANT',NOW()-INTERVAL 1 HOUR,NOW()+INTERVAL 1 DAY,'uji $tag',0)");
    $a->go('admin/citizens'); check($a->code === 200 && $a->clean(), 'Akses khusus/trial (override GRANT) membuka Data Warga');
    $db->query("DELETE FROM subscription_overrides WHERE reason='uji $tag'");

    // Nonaktif → disembunyikan dari sidebar dan ditolak.
    $su->go('admin/langganan/fitur', array('feature_code' => 'menu:admin/page-images', 'commercial_type' => 'DISABLED', 'return_tab' => 'fitur'));
    $a->go('admin'); check(strpos($a->html, 'data-key="admin/page-images"') === FALSE, 'Nonaktif: Gambar Halaman hilang dari sidebar');
    $a->go('admin/page-images'); check($a->code === 403 && strpos($a->html, 'Tidak Tersedia') !== FALSE, 'Nonaktif: URL Gambar Halaman ditolak');

    // Halaman turunan fitur katalog (Jenis Surat) bisa diatur sendiri tanpa mengunci Pengajuan Warga.
    $su->go('admin/langganan/fitur', array('feature_code' => 'menu:admin/letter_types', 'commercial_type' => 'PREMIUM', 'return_tab' => 'fitur'));
    $a->go('admin/letter_types'); check($a->code === 403, 'Jenis Surat & Ikon terkunci sendiri');
    $a->go('admin/requests'); check($a->code === 200, 'Pengajuan Warga tetap terbuka');

    // Fitur katalog lama tetap bisa diatur (Bantuan Sosial).
    $su->go('admin/langganan/fitur', array('feature_code' => 'social_aid', 'commercial_type' => 'PREMIUM', 'return_tab' => 'paket'));
    $a->go('admin/bansos'); check($a->code === 403, 'Fitur katalog Bantuan Sosial → Berlangganan terkunci');

    // Menu inti tidak bisa diubah.
    $su->go('admin/langganan/fitur', array('feature_code' => 'menu:admin', 'commercial_type' => 'PREMIUM', 'return_tab' => 'fitur'));
    check($su->code === 422 && $type('menu:admin') === 'FREE', 'Menu inti (Dashboard) ditolak diubah');

    // Toggle ON → Gratis kembali.
    $su->go('admin/langganan/fitur', array('feature_code' => 'menu:admin/citizens', 'commercial_type' => 'FREE', 'return_tab' => 'fitur'));
    $a->go('admin/citizens'); check($a->code === 200 && $type('menu:admin/citizens') === 'FREE', 'Toggle ON: Data Warga Gratis dan terbuka lagi');

    // Operator tanpa izin bansos: popup tidak ditawarkan.
    $o = (new Http())->login($tag . '_operator', $pw);
    $o->go('admin/langganan/status?feature=admin%2Fbansos', NULL, array('Accept: application/json', 'X-Requested-With: XMLHttpRequest'));
    $j = json_decode($o->html, TRUE); check($o->code === 403 && ($j['status'] ?? '') === 'PERMISSION_DENIED', 'Peran tanpa izin → PERMISSION_DENIED, bukan popup');
    $su->go('platform/paket'); check($su->code === 200 && strpos($su->html, 'Data Keluarga / KK') !== FALSE, 'Editor paket memuat semua menu sidebar sebagai pilihan fitur');
} finally {
    $db->query("DELETE FROM admins WHERE username LIKE '" . $db->real_escape_string($tag) . "\\_%'");
    $db->query('DELETE FROM subscription_catalog');
    foreach ($backup as $r) {$s = $db->prepare('INSERT INTO subscription_catalog(feature_code,commercial_type,description) VALUES(?,?,?)'); $s->bind_param('sss', $r['feature_code'], $r['commercial_type'], $r['description']); $s->execute();}
}
echo $fail ? "$fail FAILED" . PHP_EOL : 'ALL PASS (hanya database uji yang diubah)' . PHP_EOL;
exit($fail ? 1 : 0);
