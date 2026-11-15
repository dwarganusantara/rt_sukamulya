<?php
/*
 * Smoke test Hak Akses Menu per peran + popup langganan (PERMISSION_DENIED).
 * HANYA untuk database salinan sukamulya_ui_test. Jalankan server uji:
 *   set DB_NAME=sukamulya_ui_test && C:\xampp\php\php.exe -S 127.0.0.1:8098 index.php
 *   set TEST_BASE=http://127.0.0.1:8098/ && C:\xampp\php\php.exe tests\role_menu_access_smoke.php
 */
if (PHP_SAPI !== 'cli') {http_response_code(404); exit;}
$dbn = getenv('DB_NAME') ?: 'sukamulya_ui_test';
if ($dbn !== 'sukamulya_ui_test') throw new RuntimeException('Only isolated sukamulya_ui_test allowed');
$db = new mysqli('localhost', 'root', '', $dbn);
$base = getenv('TEST_BASE') ?: 'http://127.0.0.1:8097/';
$tag = 'rma' . bin2hex(random_bytes(4)); $pw = bin2hex(random_bytes(20)); $hash = password_hash($pw, PASSWORD_DEFAULT);
foreach (array('admin', 'operator', 'auditor') as $role) {$name = $tag . '_' . $role; $s = $db->prepare('INSERT INTO admins(username,password_hash,role,is_active) VALUES(?,?,?,1)'); $s->bind_param('sss', $name, $hash, $role); $s->execute();}
$fail = 0;
function check($ok, $label) {global $fail; echo ($ok ? 'PASS ' : 'FAIL ') . $label . PHP_EOL; if (!$ok) $fail++;}
class Http {
    public $c, $html, $code;
    function __construct() {$this->c = curl_init(); curl_setopt_array($this->c, array(CURLOPT_RETURNTRANSFER => TRUE, CURLOPT_COOKIEFILE => '', CURLOPT_TIMEOUT => 30, CURLOPT_PROXY => ''));}
    function token() {foreach (curl_getinfo($this->c, CURLINFO_COOKIELIST) as $cookie) {$p = explode("\t", $cookie); if (($p[5] ?? '') === 'csrf_sukamulya_cookie') return $p[6];} return '';}
    function go($path, $fields = NULL, $headers = array()) {
        global $base;
        if ($fields !== NULL) {$fields['csrf_sukamulya'] = $this->token(); curl_setopt_array($this->c, array(CURLOPT_POST => TRUE, CURLOPT_POSTFIELDS => http_build_query($fields)));}
        else curl_setopt($this->c, CURLOPT_HTTPGET, TRUE);
        curl_setopt_array($this->c, array(CURLOPT_URL => $base . $path, CURLOPT_HTTPHEADER => $headers));
        $this->html = (string) curl_exec($this->c); $this->code = curl_getinfo($this->c, CURLINFO_HTTP_CODE); return $this;
    }
    function login($user) {global $pw; $this->go('admin/login'); $this->go('admin/login', array('username' => $user, 'password' => $pw)); return $this;}
    function clean() {return !preg_match('/Fatal error|A PHP Error|A Database Error|Severity:/', $this->html);}
}
try {
    $a = (new Http())->login($tag . '_admin');
    foreach (array('akun', 'peran', 'menu') as $t) {$a->go('admin/akun?tab=' . $t . '&role=operator'); check($a->code === 200 && $a->clean(), "Admin GET tab $t tanpa error");}
    $a->go('admin/akun?tab=peran&role=operator');
    check(strpos($a->html, 'Daftar Peran (Role)') !== FALSE && strpos($a->html, 'Hak Akses Peran') !== FALSE && strpos($a->html, 'Ringkasan Akses') !== FALSE && strpos($a->html, 'Tambah Peran') !== FALSE, 'Tab Hak Akses Peran: daftar peran, ringkasan, Tambah Peran');
    preg_match_all('/name="menus\[\]" value="([^"]+)" data-akx-item checked/', $a->html, $m);
    $operator_menus = $m[1];
    check(in_array('admin/kontak', $operator_menus, TRUE), 'Operator punya menu Kontak Penting menurut RBAC');

    // Matikan Kontak Penting untuk operator.
    $a->go('admin/akun/hak-akses', array('role' => 'operator', 'tab' => 'akun', 'menus' => array_values(array_diff($operator_menus, array('admin/kontak')))));
    $row = $db->query("SELECT allowed FROM role_menu_access WHERE role='operator' AND menu_path='admin/kontak'")->fetch_assoc();
    check($row && (int) $row['allowed'] === 0, 'Simpan: admin/kontak dimatikan untuk operator');
    check((int) $db->query("SELECT COUNT(*) n FROM activity_logs WHERE subject='role_menu:operator' AND created_at >= NOW() - INTERVAL 5 MINUTE")->fetch_assoc()['n'] > 0, 'Perubahan hak akses menu tercatat di activity_logs');

    $o = (new Http())->login($tag . '_operator');
    $o->go('admin'); check($o->code === 200 && strpos($o->html, 'href="' . $base . 'admin/kontak"') === FALSE && strpos($o->html, '/admin/kontak"') === FALSE, 'Sidebar operator menyembunyikan Kontak Penting');
    $o->go('admin/kontak'); check($o->code === 403 && strpos($o->html, 'tidak diaktifkan untuk peran') !== FALSE, 'URL langsung admin/kontak ditolak 403');
    $o->go('admin/kontak/1'); check($o->code === 403, 'Sub-halaman admin/kontak/1 ditolak 403');
    $o->go('operasional/kontak'); check($o->code === 403, 'Alias controller operasional/kontak ditolak 403');
    $o->go('admin/kontak', array('name' => 'Bypass ' . $tag, 'phone' => '0215550000', 'category' => 'umum', 'is_active' => 1));
    check($o->code === 403 && !$db->query("SELECT id FROM important_contacts WHERE name='Bypass $tag'")->num_rows, 'POST admin/kontak ditolak dan tidak menyimpan data');
    $o->go('admin/kontak', NULL, array('Accept: application/json', 'X-Requested-With: XMLHttpRequest'));
    $j = json_decode($o->html, TRUE); check($o->code === 403 && ($j['error'] ?? '') === 'PERMISSION_DENIED', 'AJAX menerima JSON PERMISSION_DENIED');
    $o->go('admin/program'); check($o->code === 200 && $o->clean(), 'Menu lain operator (Program Kerja) tetap terbuka');
    $o->go('admin/akun/hak-akses', array('role' => 'operator', 'menus' => array('admin/kontak')));
    check($o->code === 403 && (int) $db->query("SELECT allowed FROM role_menu_access WHERE role='operator' AND menu_path='admin/kontak'")->fetch_assoc()['allowed'] === 0, 'Operator tidak bisa mengubah hak akses menu');
    $o->go('admin/langganan/status?feature=social_aid', NULL, array('Accept: application/json', 'X-Requested-With: XMLHttpRequest'));
    $j = json_decode($o->html, TRUE); check($o->code === 403 && ($j['status'] ?? '') === 'PERMISSION_DENIED', 'Status langganan: peran tanpa izin → PERMISSION_DENIED (bukan popup langganan)');

    // Tidak bisa menambah izin di luar RBAC.
    $a->go('admin/akun/hak-akses', array('role' => 'auditor', 'menus' => array('admin/akun', 'admin/backup')));
    check(!$db->query("SELECT 1 FROM role_menu_access WHERE role='auditor' AND menu_path IN ('admin/akun','admin/backup')")->num_rows, 'Menu di luar RBAC tidak tersimpan untuk auditor');
    $au = (new Http())->login($tag . '_auditor'); $au->go('admin/akun'); check($au->code === 403, 'Auditor tetap ditolak di Akun & Hak Akses');

    // Menu terkunci: admin tidak bisa mengunci dirinya sendiri.
    $a->go('admin/akun/hak-akses', array('role' => 'admin', 'menus' => array()));
    $a->go('admin/akun'); check($a->code === 200, 'Akun & Hak Akses tetap terbuka untuk Administrator (terkunci)');
    $a->go('admin'); check($a->code === 200, 'Dashboard tetap terbuka (wajib)');
    $a->go('admin/akun/hak-akses', array('role' => 'admin', 'action' => 'default'));
    check(!$db->query("SELECT 1 FROM role_menu_access WHERE role='admin'")->num_rows, 'Pulihkan bawaan Administrator menghapus pengaturan');

    // Tambah Peran (turunan Operator), batasi menunya, pakai di akun, lalu ubah & hapus.
    $a->go('admin/akun/peran', array('action' => 'create', 'label' => 'Koordinator ' . $tag, 'description' => 'Uji peran', 'base_role' => 'operator'));
    $cr = $db->query("SELECT * FROM custom_roles WHERE label='Koordinator $tag'")->fetch_assoc();
    check($cr && $cr['base_role'] === 'operator', 'Tambah Peran tersimpan sebagai turunan Operator');
    $a->go('admin/akun?tab=peran&role=' . $cr['role_key']); check($a->code === 200 && strpos($a->html, 'Koordinator ' . $tag) !== FALSE, 'Peran baru tampil di daftar & detail');
    $a->go('admin/akun/hak-akses', array('role' => $cr['role_key'], 'tab' => 'peran', 'menus' => array('admin/program')));
    $a->go('admin/akun', array('username' => $tag . '_koor', 'role' => $cr['role_key'], 'password' => $pw, 'full_name' => 'Koor', 'is_active' => 1));
    $acc = $db->query("SELECT role,custom_role FROM admins WHERE username='{$tag}_koor'")->fetch_assoc();
    check($acc && $acc['role'] === 'operator' && $acc['custom_role'] === $cr['role_key'], 'Akun dengan peran tambahan: role dasar operator + custom_role');
    $k = (new Http())->login($tag . '_koor');
    $k->go('admin/program'); check($k->code === 200, 'Peran tambahan: menu yang diaktifkan terbuka');
    $k->go('admin/kontak'); check($k->code === 403, 'Peran tambahan: menu yang dimatikan ditolak (walau Operator boleh)');
    $k->go('admin/akun'); check($k->code === 403, 'Peran tambahan tidak melebihi izin peran dasar');
    $a->go('admin/akun/peran', array('action' => 'delete', 'role' => $cr['role_key']));
    check((bool) $db->query("SELECT 1 FROM custom_roles WHERE role_key='{$cr['role_key']}'")->num_rows, 'Peran yang masih dipakai akun tidak bisa dihapus');
    $a->go('admin/akun/peran', array('action' => 'update', 'role' => $cr['role_key'], 'label' => 'Koor Baru ' . $tag, 'description' => 'x'));
    check((bool) $db->query("SELECT 1 FROM custom_roles WHERE label='Koor Baru $tag'")->num_rows, 'Ubah nama peran tambahan');
    $db->query("DELETE FROM admins WHERE username='{$tag}_koor'");
    $a->go('admin/akun/peran', array('action' => 'delete', 'role' => $cr['role_key']));
    check(!$db->query("SELECT 1 FROM custom_roles WHERE role_key='{$cr['role_key']}'")->num_rows && !$db->query("SELECT 1 FROM role_menu_access WHERE role='{$cr['role_key']}'")->num_rows, 'Hapus peran tambahan (beserta hak akses menunya)');
    $o2 = (new Http())->login($tag . '_operator'); $o2->go('admin/akun/peran', array('action' => 'create', 'label' => 'Hack ' . $tag, 'base_role' => 'admin'));
    check(!$db->query("SELECT 1 FROM custom_roles WHERE label='Hack $tag'")->num_rows, 'Operator tidak bisa menambah peran');

    // Pulihkan operator → akses kembali.
    $a->go('admin/akun/hak-akses', array('role' => 'operator', 'action' => 'default'));
    $o->go('admin/kontak'); check($o->code === 200 && $o->clean(), 'Setelah dipulihkan, operator dapat membuka Kontak Penting lagi');
} finally {
    $db->query("DELETE FROM admins WHERE username LIKE '" . $db->real_escape_string($tag) . "\\_%'");
    $db->query("DELETE FROM role_menu_access WHERE role IN ('operator','auditor','admin')");
    if ($db->query("SHOW TABLES LIKE 'custom_roles'")->num_rows) {$db->query("DELETE FROM role_menu_access WHERE role IN (SELECT role_key FROM custom_roles WHERE label LIKE '%" . $db->real_escape_string($tag) . "%')"); $db->query("DELETE FROM custom_roles WHERE label LIKE '%" . $db->real_escape_string($tag) . "%'");}
}
echo $fail ? "$fail FAILED" . PHP_EOL : 'ALL PASS (hanya database uji yang diubah)' . PHP_EOL;
exit($fail ? 1 : 0);
