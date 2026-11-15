<?php
/**
 * E2E alur bisnis modul RT/RW Digital + crawl seluruh halaman per peran.
 *
 * WAJIB dijalankan terhadap SALINAN database, bukan produksi:
 *   1. Salin DB:  mysqldump sukamulya | mysql sukamulya_e2e
 *   2. .htaccess sementara:  SetEnv CI_ENV development  &  SetEnv DB_NAME sukamulya_e2e
 *   3. php tests/e2e_rt16.php
 *   4. Hapus baris SetEnv dari .htaccess, hapus berkas uji di application/storage.
 * Pengaman: akun uji hanya dibuat di sukamulya_e2e; bila aplikasi masih memakai DB lain, login gagal dan tes berhenti.
 */
if (PHP_SAPI !== 'cli') {http_response_code(404); exit;}
const BASE = 'http://localhost/sukamulya/';
const DBN = 'sukamulya_e2e';
$db = new mysqli('localhost', 'root', '', DBN);
$db->set_charset('utf8mb4');

$results = array(); $fail = 0; $finished = FALSE;
register_shutdown_function(function () {global $results, $finished; if (!$finished) echo implode(PHP_EOL, $results), PHP_EOL, '*** BERHENTI SEBELUM SELESAI ***', PHP_EOL;});
function ok($cond, $msg) {global $results, $fail; $results[] = ($cond ? 'PASS ' : 'FAIL ') . $msg; if (!$cond) $fail++; return $cond;}
function q($sql) {global $db; $r = $db->query($sql); if ($r === FALSE) throw new RuntimeException($db->error . ' :: ' . $sql); return $r;}
function one($sql) {$r = q($sql); $row = $r->fetch_assoc(); return $row ? reset($row) : NULL;}
function row($sql) {return q($sql)->fetch_assoc();}
function xlsx_xml($bin) {$tmp=tempnam(sys_get_temp_dir(),'xlsx-test-');file_put_contents($tmp,$bin);$z=new ZipArchive();if($z->open($tmp)!==TRUE){@unlink($tmp);return '';}$xml=(string)$z->getFromName('xl/worksheets/sheet1.xml');$z->close();@unlink($tmp);return $xml;}
const ERR = '/A PHP Error was encountered|An uncaught Exception|A Database Error Occurred|Fatal error|Parse error/i';

class Http
{
    public $c; public $code; public $loc; public $body; public $ctype;
    function __construct() {$this->c = curl_init(); curl_setopt_array($this->c, array(CURLOPT_RETURNTRANSFER => TRUE, CURLOPT_COOKIEFILE => '', CURLOPT_TIMEOUT => 60, CURLOPT_PROXY => '', CURLOPT_FOLLOWLOCATION => FALSE, CURLOPT_HEADER => TRUE));}
    private function exec() {
        $raw = curl_exec($this->c);
        $hs = curl_getinfo($this->c, CURLINFO_HEADER_SIZE);
        $head = substr($raw, 0, $hs); $this->body = substr($raw, $hs);
        $this->code = curl_getinfo($this->c, CURLINFO_HTTP_CODE);
        $this->ctype = (string) curl_getinfo($this->c, CURLINFO_CONTENT_TYPE);
        $this->loc = preg_match('/^Location:\s*(\S+)/mi', $head, $m) ? $m[1] : '';
        return $this->body;
    }
    function get($path) {curl_setopt($this->c, CURLOPT_HTTPGET, TRUE); curl_setopt($this->c, CURLOPT_URL, BASE . ltrim($path, '/')); return $this->exec();}
    function token() {foreach (curl_getinfo($this->c, CURLINFO_COOKIELIST) as $l) {$p = explode("\t", $l); if (($p[5] ?? '') === 'csrf_sukamulya_cookie') return $p[6];} return '';}
    /** POST lalu ikuti redirect sekali; mengembalikan teks pesan flash (notice/error). */
    function post($path, $fields, $files = array()) {
        if (!$this->token()) $this->get('warga/login');
        $fields['csrf_sukamulya'] = $this->token();
        $data = $files ? $this->flat($fields) + $files : http_build_query($fields);
        curl_setopt_array($this->c, array(CURLOPT_URL => BASE . ltrim($path, '/'), CURLOPT_POST => TRUE, CURLOPT_POSTFIELDS => $data));
        $this->exec();
        $this->post_code = $this->code;
        $this->post_body = $this->body;
        if ($this->loc) $this->get(preg_replace('#^(https?://[^/]+)?/sukamulya/#', '', $this->loc));
        return $this->flash();
    }
    private function flat($a, $prefix = '') {$out = array(); foreach ($a as $k => $v) {$key = $prefix === '' ? $k : $prefix . '[' . $k . ']'; if (is_array($v)) $out += $this->flat($v, $key); else $out[$key] = (string) $v;} return $out;}
    function flash() {
        $b = $this->body; $msg = array();
        if (preg_match_all('/class="error[^"]*"[^>]*>(.*?)<\/div>/s', $b, $m)) foreach ($m[1] as $x) $msg[] = 'ERR:' . trim(strip_tags($x));
        if (preg_match_all('/class="(?:notice|success|ops-ok|flash-ok)[^"]*"[^>]*>(.*?)<\/div>/s', $b, $m)) foreach ($m[1] as $x) $msg[] = 'OK:' . trim(strip_tags($x));
        return implode(' | ', $msg);
    }
    public $post_code; public $post_body;
    /** Ambil semua isian form pertama yang method=post (input, textarea, select); checkbox hanya bila checked. */
    function form_fields($html) {
        if (!preg_match('/<form[^>]*method="post"[^>]*>(.*?)<\/form>/si', $html, $m)) return array();
        $f = $m[1]; $out = array();
        preg_match_all('/<input\b([^>]*)>/i', $f, $ins);
        foreach ($ins[1] as $a) {
            if (!preg_match('/name="([^"]+)"/', $a, $n)) continue;
            $type = preg_match('/type="([^"]+)"/i', $a, $t) ? strtolower($t[1]) : 'text';
            if (in_array($type, array('file', 'submit', 'button'), TRUE)) continue;
            if (in_array($type, array('checkbox', 'radio'), TRUE) && stripos($a, 'checked') === FALSE) continue;
            $out[html_entity_decode($n[1])] = preg_match('/value="([^"]*)"/', $a, $v) ? html_entity_decode($v[1], ENT_QUOTES) : '';
        }
        preg_match_all('/<textarea[^>]*name="([^"]+)"[^>]*>(.*?)<\/textarea>/si', $f, $tas, PREG_SET_ORDER);
        foreach ($tas as $ta) $out[$ta[1]] = html_entity_decode($ta[2], ENT_QUOTES);
        preg_match_all('/<select[^>]*name="([^"]+)"[^>]*>(.*?)<\/select>/si', $f, $ss, PREG_SET_ORDER);
        foreach ($ss as $s) $out[$s[1]] = preg_match('/<option value="([^"]*)"[^>]*selected/i', $s[2], $o) ? html_entity_decode($o[1], ENT_QUOTES) : '';
        unset($out['csrf_sukamulya']);
        return $out;
    }
}

function login_admin($user, $pw) {$h = new Http(); $h->get('admin/login'); $h->post('admin/login', array('username' => $user, 'password' => $pw)); return $h;}
function login_warga($user, $pw) {$h = new Http(); $h->get('warga/login'); $h->post('warga/login', array('username' => $user, 'password' => $pw)); return $h;}
/** PNG 2x2 valid tanpa ekstensi GD. */
function png($path) {
    $chunk = function ($type, $data) {return pack('N', strlen($data)) . $type . $data . pack('N', crc32($type . $data));};
    $raw = str_repeat("\x00" . str_repeat("\xF0\x50\x1A", 2), 2);
    file_put_contents($path, "\x89PNG\r\n\x1a\n" . $chunk('IHDR', pack('NNCCCCC', 2, 2, 8, 2, 0, 0, 0)) . $chunk('IDAT', gzcompress($raw)) . $chunk('IEND', ''));
    return $path;
}
function noerr($h, $what) {return ok(!preg_match(ERR, $h->body) && $h->code < 500, "$what (HTTP {$h->code})" . (preg_match(ERR, $h->body, $m) ? ' :: ' . mb_substr(trim(strip_tags(substr($h->body, max(0, strpos($h->body, $m[0]) - 0), 600))), 0, 400) : ''));}

$tag = 'e2e' . bin2hex(random_bytes(3));
$pw = 'Uji-' . bin2hex(random_bytes(8));
$hash = password_hash($pw, PASSWORD_DEFAULT);
$roles = array('admin', 'ketua', 'sekretaris', 'bendahara', 'operator', 'keamanan', 'inventaris', 'sosial', 'auditor');
$aid = array();
foreach (array_merge($roles, array('ketua2')) as $r) {
    $role = $r === 'ketua2' ? 'ketua' : $r;
    q("INSERT INTO admins (username,password_hash,role,is_active,full_name) VALUES ('{$tag}_$r','$hash','$role',1,'Uji $r')");
    $aid[$r] = (int) $db->insert_id;
}
foreach (array('w1', 'w2') as $w) {q("INSERT INTO citizens (username,display_name,full_name,password_hash,is_active,gender) VALUES ('{$tag}_$w','Warga $w $tag','Warga Uji $w $tag','$hash',1,'male')"); $cid[$w] = (int) $db->insert_id;}
$nik = function () {return '99' . str_pad((string) random_int(0, 99999999999999), 14, '0', STR_PAD_LEFT);};
$kk = $nik();
q("INSERT INTO kk_families (nomor_kk,owner_citizen_id,fields_json) VALUES ('$kk',{$cid['w1']},'" . $db->real_escape_string(json_encode(array('nomor_kk' => $kk, 'alamat' => 'Jl. E2E 9', 'rt' => '009', 'rw' => '008'))) . "')");
$fid = (int) $db->insert_id;
$mem = array();
foreach (array(array('E2E KEPALA', 'L', 'Kepala Keluarga'), array('E2E ISTRI', 'P', 'Istri'), array('E2E ANAK', 'L', 'Anak')) as $m) {
    $n = $nik();
    q("INSERT INTO kk_members (family_id,nik,fields_json,status,domicile_status) VALUES ($fid,'$n','" . $db->real_escape_string(json_encode(array('nama_lengkap' => $m[0], 'nik' => $n, 'jenis_kelamin' => $m[1], 'tanggal_lahir' => '1990-01-01', 'hubungan_keluarga' => $m[2]))) . "','aktif','tetap')");
    $mem[$m[0]] = (int) $db->insert_id;
}
$img = png(sys_get_temp_dir() . DIRECTORY_SEPARATOR . $tag . '.png');
$file = function () use ($img) {return new CURLFile($img, 'image/png', 'bukti.png');};
$A = array();
foreach (array_merge($roles, array('ketua2')) as $r) $A[$r] = login_admin("{$tag}_$r", $pw);
ok(strpos($A['admin']->body, 'admin/login') === FALSE || $A['admin']->code === 200, 'login admin uji');
$A['admin']->get('admin'); if (!ok($A['admin']->code === 200, 'aplikasi memakai DB e2e (login akun uji berhasil)')) {echo implode(PHP_EOL, $results), PHP_EOL; exit(1);}
$W1 = login_warga("{$tag}_w1", $pw); $W2 = login_warga("{$tag}_w2", $pw);
$W1->get('warga'); ok($W1->code === 200, 'login warga uji');

/* ---------- 02 MUTASI ---------- */
$W1->get('warga/mutasi/ajukan?jenis=pindah'); noerr($W1, 'warga: form mutasi pindah');
$f = $W1->post('warga/mutasi/ajukan?jenis=pindah', array('member_id' => $mem['E2E ANAK'], 'effective_date' => date('Y-m-d'), 'reason' => 'Pindah kuliah ke luar kota', 'alamat_tujuan' => 'Bandung'));
$mid = (int) one("SELECT MAX(id) FROM resident_mutations WHERE citizen_id={$cid['w1']}");
ok($mid && one("SELECT status FROM resident_mutations WHERE id=$mid") === 'submitted', 'warga mengajukan mutasi pindah → submitted :: ' . $f);
$W1->post('warga/mutasi/ajukan?jenis=pindah', array('member_id' => $mem['E2E ANAK'], 'effective_date' => date('Y-m-d'), 'reason' => 'Duplikat pengajuan', 'alamat_tujuan' => 'Bandung'));
ok((int) one("SELECT COUNT(*) FROM resident_mutations WHERE citizen_id={$cid['w1']}") === 1, 'pengajuan ganda untuk anggota yang sama ditolak');
$W2->get("warga/mutasi/$mid"); ok($W2->code === 404, 'warga lain membuka mutasi → 404');
$A['admin']->get("admin/mutasi/$mid"); noerr($A['admin'], 'admin: detail mutasi');
$f = $A['operator']->post("admin/mutasi/$mid", array('decision' => 'approved', 'review_note' => ''));
ok($A['operator']->code === 403 || one("SELECT status FROM resident_mutations WHERE id=$mid") !== 'approved', 'operator tidak boleh menyetujui mutasi');
$f = $A['sekretaris']->post("admin/mutasi/$mid", array('decision' => 'approved', 'review_note' => 'OK'));
ok(one("SELECT status FROM kk_members WHERE id={$mem['E2E ANAK']}") === 'pindah', 'sekretaris setujui → anggota berstatus pindah :: ' . $f);
$f = $A['sekretaris']->post("admin/mutasi/$mid", array('decision' => 'approved', 'review_note' => 'lagi'));
ok(strpos($f, 'sudah diproses') !== FALSE, 'persetujuan ulang ditolak :: ' . $f . ' [HTTP ' . $A['sekretaris']->post_code . ' -> ' . $A['sekretaris']->loc . ']');
$newnik = $nik();
$A['sekretaris']->get("admin/mutasi/baru?keluarga=$fid&jenis=masuk"); noerr($A['sekretaris'], 'admin: form mutasi masuk');
$f = $A['sekretaris']->post("admin/mutasi/baru?keluarga=$fid&jenis=masuk", array('family_id' => $fid, 'effective_date' => date('Y-m-d'), 'reason' => 'Kelahiran anak kedua', 'm_nama_lengkap' => 'E2E BAYI', 'm_nik' => $newnik, 'm_jenis_kelamin' => 'P', 'm_hubungan_keluarga' => 'Anak', 'm_tanggal_lahir' => date('Y-m-d'), 'domicile_status' => 'tetap'));
$m2 = (int) one("SELECT MAX(id) FROM resident_mutations WHERE family_id=$fid");
ok($m2 > $mid, 'pengurus mencatat mutasi masuk :: ' . $f);
$A['ketua']->post("admin/mutasi/$m2", array('decision' => 'approved'));
ok((int) one("SELECT COUNT(*) FROM kk_members WHERE nik='$newnik' AND family_id=$fid AND status='aktif'") === 1, 'mutasi masuk disetujui → anggota baru aktif');
$W1->post('warga/mutasi/ajukan?jenis=ubah_data', array('member_id' => $mem['E2E ISTRI'], 'effective_date' => date('Y-m-d'), 'reason' => 'Ganti pekerjaan', 'm_pekerjaan' => 'GURU', 'domicile_status' => 'kontrak'));
$m3 = (int) one("SELECT MAX(id) FROM resident_mutations WHERE citizen_id={$cid['w1']}");
$A['sekretaris']->post("admin/mutasi/$m3", array('decision' => 'revision_requested', 'review_note' => ''));
ok(one("SELECT status FROM resident_mutations WHERE id=$m3") === 'submitted', 'minta perbaikan tanpa alasan ditolak');
$A['sekretaris']->post("admin/mutasi/$m3", array('decision' => 'revision_requested', 'review_note' => 'Lampirkan keterangan'));
ok(one("SELECT status FROM resident_mutations WHERE id=$m3") === 'revision_requested', 'minta perbaikan dengan alasan');
$W1->get("warga/mutasi/ajukan?ubah=$m3"); noerr($W1, 'warga: form revisi mutasi');
$W1->post("warga/mutasi/ajukan?ubah=$m3", array('member_id' => $mem['E2E ISTRI'], 'effective_date' => date('Y-m-d'), 'reason' => 'Ganti pekerjaan (revisi)', 'm_pekerjaan' => 'GURU', 'domicile_status' => 'kontrak'));
ok(one("SELECT status FROM resident_mutations WHERE id=$m3") === 'submitted', 'warga mengirim ulang revisi');
$A['ketua']->post("admin/mutasi/$m3", array('decision' => 'approved'));
$r = row("SELECT fields_json,domicile_status FROM kk_members WHERE id={$mem['E2E ISTRI']}");
ok(json_decode($r['fields_json'], TRUE)['pekerjaan'] === 'GURU' && $r['domicile_status'] === 'kontrak', 'koreksi data + domisili diterapkan ke master');

/* ---------- 03 PENGADUAN ---------- */
$W1->get('warga/pengaduan/buat'); noerr($W1, 'warga: form pengaduan');
$f = $W1->post('warga/pengaduan/buat', array('category' => 'kebersihan', 'title' => 'Sampah menumpuk di gang', 'location' => 'Gang 3', 'description' => 'Sudah tiga hari tidak diangkut petugas.'), array('attachment' => $file()));
$cpid = (int) one("SELECT MAX(id) FROM complaints WHERE citizen_id={$cid['w1']}");
ok($cpid && one("SELECT attachment FROM complaints WHERE id=$cpid"), 'pengaduan + lampiran tersimpan :: ' . $f);
$W1->get("warga/pengaduan/$cpid/lampiran"); ok($W1->code === 200 && strpos($W1->ctype, 'image/png') === 0, 'pemilik dapat membuka lampiran');
$W2->get("warga/pengaduan/$cpid/lampiran"); ok($W2->code === 404, 'warga lain membuka lampiran → 404');
$W2->get("warga/pengaduan/$cpid"); ok($W2->code === 404, 'warga lain membuka tiket → 404');
$A['keamanan']->get("admin/pengaduan/$cpid"); ok($A['keamanan']->code === 404, 'petugas keamanan tidak melihat tiket kebersihan yang bukan tugasnya');
$A['ketua']->post("admin/pengaduan/$cpid", array('assigned_to' => $aid['keamanan'], 'priority' => 'tinggi', 'message' => 'Mohon dicek', 'status' => ''));
ok(one("SELECT status FROM complaints WHERE id=$cpid") === 'assigned' && (int) one("SELECT assigned_to FROM complaints WHERE id=$cpid") === $aid['keamanan'], 'ketua menugaskan PIC → status ditugaskan');
$A['keamanan']->get("admin/pengaduan/$cpid"); ok($A['keamanan']->code === 200, 'PIC keamanan kini dapat membuka tiket');
$A['keamanan']->post("admin/pengaduan/$cpid", array('status' => 'closed', 'message' => 'x'));
ok(one("SELECT status FROM complaints WHERE id=$cpid") === 'assigned', 'transisi ilegal (ditugaskan→ditutup) ditolak');
$A['keamanan']->post("admin/pengaduan/$cpid", array('status' => 'resolved', 'message' => 'Sudah diangkut pagi ini.'));
ok(one("SELECT status FROM complaints WHERE id=$cpid") === 'resolved', 'PIC menyelesaikan tiket');
$W1->post("warga/pengaduan/$cpid", array('action' => 'confirm', 'rating' => 5, 'message' => ''));
ok(one("SELECT status FROM complaints WHERE id=$cpid") === 'closed' && (int) one("SELECT rating FROM complaints WHERE id=$cpid") === 5, 'warga mengonfirmasi + rating → ditutup');

/* ---------- 07 PROGRAM ---------- */
$A['operator']->get('admin/program/baru'); noerr($A['operator'], 'form program');
$A['operator']->post('admin/program/baru', array('title' => 'Program E2E Kebersihan', 'category' => 'lingkungan', 'period_start' => date('Y-m-01'), 'period_end' => date('Y-m-t'), 'budget' => '500000', 'pic' => 'Pak Uji'));
$pg = (int) one("SELECT MAX(id) FROM work_programs WHERE title='Program E2E Kebersihan'");
ok($pg > 0, 'program dibuat (draf)');
$A['operator']->post("admin/program/$pg", array('act' => 'status', 'to' => 'diajukan'));
$A['operator']->post("admin/program/$pg", array('act' => 'status', 'to' => 'aktif'));
ok(one("SELECT status FROM work_programs WHERE id=$pg") === 'diajukan', 'operator mengajukan; tidak dapat mengaktifkan sendiri');
$A['ketua']->post("admin/program/$pg", array('act' => 'status', 'to' => 'aktif'));
ok(one("SELECT status FROM work_programs WHERE id=$pg") === 'aktif', 'ketua menyetujui → aktif');
$A['operator']->post("admin/program/$pg", array('act' => 'milestone_add', 'm_title' => 'Kerja bakti 1', 'm_weight' => 40, 'm_due' => date('Y-m-d')));
$A['operator']->post("admin/program/$pg", array('act' => 'milestone_add', 'm_title' => 'Kerja bakti 2', 'm_weight' => 60));
$ms = (int) one("SELECT id FROM program_milestones WHERE program_id=$pg AND title='Kerja bakti 1'");
$A['operator']->post("admin/program/$pg", array('act' => 'milestone_toggle', 'milestone_id' => $ms));
ok((int) one("SELECT progress FROM work_programs WHERE id=$pg") === 40, 'progres berbobot 40% setelah milestone 1 selesai');
$agenda = (int) one("SELECT id FROM posts WHERE type='agenda' ORDER BY id LIMIT 1");
if ($agenda) {$A['operator']->post("admin/program/$pg", array('act' => 'link_agenda', 'post_id' => $agenda)); ok((int) one("SELECT COUNT(*) FROM program_agendas WHERE program_id=$pg") === 1, 'agenda ditautkan ke program');}

/* ---------- 08 NOTULEN ---------- */
$A['operator']->get('admin/notulen/baru' . ($agenda ? "?agenda=$agenda" : '')); noerr($A['operator'], 'form notulen');
$A['operator']->post('admin/notulen/baru', array('title' => 'Rapat E2E', 'note_date' => date('Y-m-d'), 'program_id' => $pg, 'agenda_id' => $agenda ?: '', 'summary' => 'Ringkasan', 'decisions' => 'Keputusan', 'finalize' => 1));
$nt = (int) one("SELECT MAX(id) FROM activity_notes WHERE title='Rapat E2E'");
ok(one("SELECT status FROM activity_notes WHERE id=$nt") === 'final', 'notulen diajukan untuk pengesahan');
$A['operator']->post("admin/notulen/$nt/ubah", array('title' => 'Rapat E2E (rev)', 'note_date' => date('Y-m-d'), 'program_id' => $pg, 'summary' => 'Ringkasan rev', 'finalize' => 1));
ok((int) one("SELECT version FROM activity_notes WHERE id=$nt") === 2 && (int) one("SELECT COUNT(*) FROM activity_note_versions WHERE note_id=$nt") === 1, 'revisi menyimpan versi lama');
$A['operator']->post("admin/notulen/$nt", array('act' => 'approve'));
ok(one("SELECT status FROM activity_notes WHERE id=$nt") === 'final', 'operator tidak dapat mengesahkan');
$A['ketua']->post("admin/notulen/$nt", array('act' => 'approve'));
ok(one("SELECT status FROM activity_notes WHERE id=$nt") === 'disahkan', 'ketua mengesahkan');
$A['operator']->get("admin/notulen/$nt/ubah"); ok($A['operator']->code >= 300 && $A['operator']->code < 400 || strpos($A['operator']->body, 'dikunci') !== FALSE, 'notulen disahkan terkunci dari edit');
$A['operator']->post("admin/notulen/$nt", array('act' => 'action_add', 'a_title' => 'Beli tong sampah', 'a_pic' => 'Pak Uji', 'a_due' => date('Y-m-d', strtotime('-2 days'))));
ok((int) one("SELECT COUNT(*) FROM action_items WHERE note_id=$nt") === 1, 'tindak lanjut ditambahkan');
$A['admin']->get('admin'); noerr($A['admin'], 'dashboard dengan data monitoring');

/* ---------- 09 RONDA ---------- */
$today = date('Y-m-d');
$A['keamanan']->post('admin/ronda', array('act' => 'add', 'patrol_date' => $today, 'shift' => 'shift_1', 'citizen_id' => $cid['w1'], 'officer_name' => ''));
$ps = (int) one("SELECT MAX(id) FROM patrol_schedules WHERE citizen_id={$cid['w1']}");
ok($ps > 0, 'jadwal ronda ditambahkan');
$A['keamanan']->post('admin/ronda', array('act' => 'add', 'patrol_date' => $today, 'shift' => 'penuh', 'citizen_id' => $cid['w1']));
ok((int) one("SELECT COUNT(*) FROM patrol_schedules WHERE citizen_id={$cid['w1']}") === 1 && strpos($A['keamanan']->post_body, 'bentrok') !== FALSE, 'jadwal bentrok terdeteksi');
$A['keamanan']->post('admin/ronda', array('act' => 'attendance', 'schedule_id' => $ps, 'attendance' => 'izin', 'attendance_note' => ''));
ok(one("SELECT attendance FROM patrol_schedules WHERE id=$ps") === 'scheduled', 'absensi manual tanpa alasan ditolak');
$W2->post('warga/ronda', array('schedule_id' => $ps, 'act' => 'checkin'));
ok(one("SELECT check_in FROM patrol_schedules WHERE id=$ps") === NULL, 'warga lain tidak dapat check-in jadwal orang lain');
$W1->get('warga/ronda'); noerr($W1, 'warga: halaman ronda');
$A['keamanan']->post('admin/ronda/kejadian', array('incident_at' => $today . 'T23:00', 'category' => 'mencurigakan', 'location' => 'Pos', 'description' => 'Motor tanpa plat berputar-putar', 'schedule_id' => $ps));
ok((int) one("SELECT COUNT(*) FROM incident_reports WHERE schedule_id=$ps") === 1, 'laporan kejadian dicatat');

/* ---------- 10 INVENTARIS ---------- */
$code = strtoupper($tag) . '-TENDA';
$A['inventaris']->post('admin/inventaris/baru', array('code' => $code, 'name' => 'Tenda Uji', 'kind' => 'aset', 'quantity' => 3, 'unit' => 'buah', 'category' => 'acara'));
$as = (int) one("SELECT id FROM assets WHERE code='$code'");
ok($as && (int) one("SELECT qty_available FROM assets WHERE id=$as") === 3, 'aset dibuat dengan stok 3');
foreach (array(1, 2) as $i) $A['inventaris']->post('admin/inventaris/pinjam', array('act' => 'create', 'asset_id' => $as, 'qty' => 2, 'loan_date' => $today, 'due_date' => date('Y-m-d', strtotime('+2 days')), 'borrower_name' => "Peminjam $i", 'citizen_id' => $cid['w1']));
$loans = array_map('intval', array_column(q("SELECT id FROM asset_loans WHERE asset_id=$as ORDER BY id")->fetch_all(MYSQLI_ASSOC), 'id'));
ok(count($loans) === 2, 'dua permohonan pinjam (2+2) dicatat');
$A['inventaris']->post('admin/inventaris/pinjam', array('act' => 'approve', 'loan_id' => $loans[0]));
ok(one("SELECT status FROM asset_loans WHERE id={$loans[0]}") === 'requested', 'petugas inventaris tidak berwenang menyetujui');
$A['ketua']->post('admin/inventaris/pinjam', array('act' => 'approve', 'loan_id' => $loans[0]));
$A['ketua']->post('admin/inventaris/pinjam', array('act' => 'approve', 'loan_id' => $loans[1]));
ok(one("SELECT status FROM asset_loans WHERE id={$loans[1]}") === 'requested' && (int) one("SELECT qty_available FROM assets WHERE id=$as") === 1, 'persetujuan kedua ditolak, stok tidak minus (tersisa 1)');
$A['inventaris']->post('admin/inventaris/pinjam', array('act' => 'handover', 'loan_id' => $loans[0]));
$A['inventaris']->post('admin/inventaris/pinjam', array('act' => 'return', 'loan_id' => $loans[0], 'qty_good' => 1, 'qty_damaged' => 1, 'qty_lost' => 0));
$a = row("SELECT quantity,qty_available FROM assets WHERE id=$as");
ok(one("SELECT status FROM asset_loans WHERE id={$loans[0]}") === 'returned' && (int) $a['qty_available'] === 2 && (int) $a['quantity'] === 3, 'pengembalian 1 baik + 1 rusak: tersedia 2, total 3');
$A['inventaris']->post('admin/inventaris/pinjam', array('act' => 'reject', 'loan_id' => $loans[1]));
$A['ketua']->post('admin/inventaris/pinjam', array('act' => 'reject', 'loan_id' => $loans[1]));
ok(one("SELECT status FROM asset_loans WHERE id={$loans[1]}") === 'rejected', 'permohonan kedua ditolak oleh ketua');
$A['inventaris']->get("admin/inventaris/$as"); noerr($A['inventaris'], 'detail aset');

/* ---------- 11 KONTAK ---------- */
$A['sekretaris']->post('admin/kontak', array('name' => 'Puskesmas E2E', 'category' => 'kesehatan', 'phone' => '021-555', 'is_public' => 1, 'is_active' => 1, 'is_emergency' => 1));
$A['sekretaris']->post('admin/kontak', array('name' => 'Internal E2E', 'category' => 'pengurus', 'phone' => '0812345', 'is_active' => 1));
$W1->get('warga/kontak-penting');
ok(strpos($W1->body, 'Puskesmas E2E') !== FALSE && strpos($W1->body, 'Internal E2E') === FALSE && strpos($W1->body, 'tel:') !== FALSE, 'warga hanya melihat kontak publik + tautan tel:');
$W1->get('warga/pengaduan/buat'); ok(strpos($W1->body, 'Puskesmas E2E') !== FALSE, 'kontak darurat tampil di form pengaduan');

/* ---------- 12 BELANJA ---------- */
$A['operator']->post('admin/belanja/baru', array('title' => 'Belanja E2E', 'reason' => 'Tong sampah', 'vendor' => 'Toko Uji', 'program_id' => $pg, 'item_name' => array('Tong sampah', 'Sapu'), 'item_qty' => array(2, 3), 'item_price' => array('100000', '20000'), 'item_unit' => array('buah', 'buah'), 'item_inventory' => array(0 => 1), 'submit_now' => 1));
$pr = (int) one("SELECT MAX(id) FROM purchase_requests WHERE title='Belanja E2E'");
ok(one("SELECT status FROM purchase_requests WHERE id=$pr") === 'submitted' && (float) one("SELECT estimated_total FROM purchase_requests WHERE id=$pr") == 260000, 'permintaan belanja diajukan, estimasi Rp260.000');
$A['ketua']->post('admin/belanja/baru', array('title' => 'Belanja Ketua', 'item_name' => array('Spidol'), 'item_qty' => array(1), 'item_price' => array('5000'), 'submit_now' => 1));
$pr2 = (int) one("SELECT MAX(id) FROM purchase_requests WHERE title='Belanja Ketua'");
$f = $A['ketua']->post("admin/belanja/$pr2", array('act' => 'approve'));
ok(one("SELECT status FROM purchase_requests WHERE id=$pr2") === 'submitted', 'pemohon tidak dapat menyetujui sendiri :: ' . $f);
$A['ketua']->post("admin/belanja/$pr", array('act' => 'approve', 'note' => ''));
$A['operator']->post("admin/belanja/$pr", array('act' => 'order'));
ok(one("SELECT status FROM purchase_requests WHERE id=$pr") === 'ordered', 'disetujui lalu dipesan');
$items = q("SELECT id,name FROM purchase_items WHERE purchase_id=$pr ORDER BY id")->fetch_all(MYSQLI_ASSOC);
$A['operator']->post("admin/belanja/$pr", array('act' => 'receive', 'recv' => array($items[0]['id'] => 1, $items[1]['id'] => 3), 'note' => 'Sebagian'));
ok(one("SELECT status FROM purchase_requests WHERE id=$pr") === 'partially_received', 'penerimaan parsial');
$asset_from_purchase = (int) one("SELECT asset_id FROM purchase_items WHERE id={$items[0]['id']}");
ok($asset_from_purchase && (int) one("SELECT quantity FROM assets WHERE id=$asset_from_purchase") === 1, 'barang inventaris masuk stok setelah diterima (1)');
$cash_before = (int) one("SELECT COUNT(*) FROM transactions");
$f = $A['bendahara']->post("admin/belanja/$pr", array('act' => 'post', 'actual_total' => '250000', 'actual_note' => 'Diskon toko'), array('proof' => $file()));
ok((int) one("SELECT COUNT(*) FROM transactions") === $cash_before + 1 && one("SELECT transaction_id FROM purchase_requests WHERE id=$pr"), 'bendahara memposting pengeluaran kas sekali :: ' . $f);
$A['bendahara']->post("admin/belanja/$pr", array('act' => 'post', 'actual_total' => '250000', 'actual_note' => 'lagi'), array('proof' => $file()));
ok((int) one("SELECT COUNT(*) FROM transactions") === $cash_before + 1, 'posting kedua ditolak (tidak ada jurnal ganda)');
$A['operator']->post("admin/belanja/$pr", array('act' => 'receive', 'recv' => array($items[0]['id'] => 1)));
$A['operator']->post("admin/belanja/$pr", array('act' => 'close'));
ok(one("SELECT status FROM purchase_requests WHERE id=$pr") === 'closed' && (int) one("SELECT quantity FROM assets WHERE id=$asset_from_purchase") === 2, 'sisa diterima, permintaan ditutup, stok 2');
$A['auditor']->get("admin/belanja/$pr/bukti"); ok($A['auditor']->code === 200, 'auditor dapat melihat bukti belanja');
$A['keamanan']->get("admin/belanja/$pr/bukti"); ok($A['keamanan']->code === 403, 'petugas keamanan tidak dapat melihat bukti belanja');

/* ---------- 13 BANSOS ---------- */
$A['sosial']->post('admin/bansos/baru', array('name' => 'Sembako E2E', 'aid_type' => 'Sembako', 'unit_value' => '150000', 'quota' => 1, 'period' => date('Y')));
$bp = (int) one("SELECT MAX(id) FROM aid_programs WHERE name='Sembako E2E'");
$A['ketua']->post("admin/bansos/$bp", array('act' => 'program_status', 'to' => 'aktif'));
$A['sosial']->post("admin/bansos/$bp", array('act' => 'add', 'family_id' => $fid, 'proposal_note' => 'Kepala keluarga terdampak PHK'));
$A['sosial']->post("admin/bansos/$bp", array('act' => 'add', 'family_id' => $fid));
ok((int) one("SELECT COUNT(*) FROM aid_recipients WHERE program_id=$bp") === 1, 'calon dari KK ditambahkan; duplikasi ditolak');
$rc = (int) one("SELECT id FROM aid_recipients WHERE program_id=$bp");
ok(one("SELECT recipient_name FROM aid_recipients WHERE id=$rc") === 'E2E KEPALA', 'nama penerima diambil dari kepala keluarga aktif');
$A['sosial']->post("admin/bansos/$bp", array('act' => 'verify', 'recipient_id' => $rc, 'result' => 'layak'));
$A['sosial']->post("admin/bansos/$bp", array('act' => 'approve', 'recipient_id' => $rc));
ok(one("SELECT status FROM aid_recipients WHERE id=$rc") === 'layak', 'petugas sosial tidak dapat menyetujui sendiri');
$A['ketua']->post("admin/bansos/$bp", array('act' => 'approve', 'recipient_id' => $rc));
$A['sosial']->post("admin/bansos/$bp", array('act' => 'distribute', 'recipient_id' => $rc), array('proof' => $file()));
ok(one("SELECT status FROM aid_recipients WHERE id=$rc") === 'disalurkan' && one("SELECT proof FROM aid_recipients WHERE id=$rc"), 'bantuan disalurkan + bukti');
$f = $A['sosial']->post("admin/bansos/$bp", array('act' => 'distribute', 'recipient_id' => $rc), array('proof' => $file()));
ok(strpos($f, 'sudah tercatat disalurkan') !== FALSE && (int) one("SELECT COUNT(*) FROM activity_logs WHERE subject='aid_recipient:$rc' AND action='distribute'") === 1, 'penyaluran kedua ditolak :: ' . $f . ' [HTTP ' . $A['sosial']->post_code . ']');
$A['bendahara']->get("admin/bansos/$bp"); ok($A['bendahara']->code === 403, 'bendahara tidak dapat melihat daftar penerima bansos');

/* ---------- 14 ARSIP ---------- */
$A['sekretaris']->post('admin/arsip', array('title' => 'SK E2E', 'category' => 'sk', 'confidentiality' => 'internal'), array('document' => $file()));
$doc = (int) one("SELECT MAX(id) FROM documents WHERE title='SK E2E'");
ok($doc > 0, 'dokumen diarsipkan');
$A['sekretaris']->get("admin/arsip/$doc/unduh"); ok($A['sekretaris']->code === 200 && strpos($A['sekretaris']->ctype, 'image/png') === 0, 'unduh dokumen via controller');
$A['admin']->post('admin/arsip', array('title' => 'Rahasia E2E', 'category' => 'umum', 'confidentiality' => 'rahasia'), array('document' => $file()));
$sec = (int) one("SELECT MAX(id) FROM documents WHERE title='Rahasia E2E'");
$A['sekretaris']->get("admin/arsip/$sec"); ok($A['sekretaris']->code === 404, 'sekretaris tidak melihat dokumen rahasia');
$A['sekretaris']->get("admin/arsip/$sec/unduh"); ok($A['sekretaris']->code === 403, 'sekretaris tidak dapat mengunduh dokumen rahasia');
$stored = one("SELECT stored_name FROM document_versions WHERE document_id=$doc");
$anon = new Http(); $anon->get("application/storage/documents/$stored"); ok($anon->code === 403 || $anon->code === 404, "URL langsung ke berkas privat ditolak ({$anon->code})");
$A['sekretaris']->post("admin/arsip/$doc", array('act' => 'version', 'note' => 'Revisi'), array('document' => $file()));
ok((int) one("SELECT current_version FROM documents WHERE id=$doc") === 2, 'versi baru dokumen');
$A['sekretaris']->post("admin/arsip/$doc", array('act' => 'delete', 'reason' => 'Salah unggah'));
ok(one("SELECT deleted_at FROM documents WHERE id=$doc") !== NULL, 'soft delete dokumen');

/* ---------- 15 SERAH TERIMA ---------- */
$A['sekretaris']->post('admin/serah-terima', array('title' => 'Serah Terima E2E', 'period_label' => '2026', 'outgoing' => 'Lama', 'incoming' => 'Baru'));
$hs = (int) one("SELECT MAX(id) FROM handover_sessions WHERE title='Serah Terima E2E'");
$snap = json_decode(one("SELECT snapshot_json FROM handover_sessions WHERE id=$hs"), TRUE);
ok($hs && isset($snap['saldo_kas']) && strpos(json_encode($snap), $kk) === FALSE, 'snapshot agregat dibuat tanpa identitas');
$st = array(); foreach (array('warga', 'kas', 'tunggakan', 'aset', 'program', 'dokumen', 'akun') as $k) $st[$k] = 'sesuai';
$A['ketua']->post("admin/serah-terima/$hs", array('act' => 'finalize'));
ok(one("SELECT status FROM handover_sessions WHERE id=$hs") !== 'final', 'final ditolak sebelum checklist & tanda tangan');
$A['sekretaris']->post("admin/serah-terima/$hs", array('act' => 'checklist', 'status' => $st, 'note' => array()));
$A['sekretaris']->post("admin/serah-terima/$hs", array('act' => 'sign', 'side' => 'lama'));
$A['ketua']->post("admin/serah-terima/$hs", array('act' => 'sign', 'side' => 'baru'));
$A['ketua']->post("admin/serah-terima/$hs", array('act' => 'finalize'));
ok(one("SELECT status FROM handover_sessions WHERE id=$hs") === 'final', 'serah terima final setelah checklist + 2 pihak');
$A['sekretaris']->post("admin/serah-terima/$hs", array('act' => 'checklist', 'status' => $st));
ok(one("SELECT status FROM handover_sessions WHERE id=$hs") === 'final', 'berita acara final tidak dapat diubah');

/* ---------- AKUN, AUDIT, BACKUP, KEUANGAN, PENGATURAN ---------- */
$A['admin']->post('admin/akun', array('username' => $tag . '_baru', 'role' => 'operator', 'password' => 'PasswordUji123', 'is_active' => 1, 'full_name' => 'Akun Baru'));
$nb = (int) one("SELECT id FROM admins WHERE username='{$tag}_baru'"); $aid['baru'] = $nb;
$NB = login_admin($tag . '_baru', 'PasswordUji123'); $NB->get('admin'); ok($NB->code === 200, 'akun baru dapat login');
$A['admin']->post("admin/akun/$nb", array('role' => 'operator', 'is_active' => 0));
$NB->get('admin'); ok($NB->code >= 300 && $NB->code < 400, 'akun dicabut → sesi aktif langsung keluar');
$A['admin']->post("admin/akun/{$aid['admin']}", array('role' => 'operator', 'is_active' => 1));
ok(one("SELECT role FROM admins WHERE id={$aid['admin']}") === 'admin', 'admin tidak dapat menurunkan peran sendiri');
$A['auditor']->get('admin/audit?q=mutation'); noerr($A['auditor'], 'auditor membuka audit log');
ok(strpos($A['auditor']->body, $kk) === FALSE, 'audit log tidak memuat nomor KK utuh');
$A['auditor']->get('admin/audit?export=xlsx'); ok(strpos($A['auditor']->ctype, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') === 0, 'ekspor audit XLSX');
$f = $A['admin']->post('admin/backup', array('act' => 'run'));
$br = row("SELECT * FROM backup_runs WHERE kind='database' ORDER BY id DESC LIMIT 1");
ok($br && $br['status'] === 'success', 'backup database terenkripsi berhasil :: ' . mb_substr($f, 0, 120));
if ($br && $br['status'] === 'success') {$A['admin']->post('admin/backup', array('act' => 'verify', 'run_id' => $br['id'])); ok(one("SELECT verified_at FROM backup_runs WHERE id={$br['id']}") !== NULL, 'verifikasi checksum + dekripsi backup');}
$A['bendahara']->get('admin/tunggakan'); noerr($A['bendahara'], 'aging tunggakan');
$A['bendahara']->get('admin/tunggakan?export=xlsx'); ok(strpos($A['bendahara']->ctype, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') === 0, 'ekspor tunggakan XLSX');
// Tunggakan v4: pengingat per baris (name="one") dan batas 1x/3 hari.
$past = date('Y-m', strtotime('first day of -2 month'));
q("INSERT INTO dues (citizen_id,period,category,due_type,amount_due,amount_paid,note,updated_at) VALUES ({$cid['w1']},'$past','Iuran E2E Tunggak','rutin',50000,0,'e2e',NOW())"); $tdue = (int) $db->insert_id;
$A['bendahara']->get('admin/tunggakan'); ok(strpos($A['bendahara']->body, 'Tunggakan Iuran Warga') !== FALSE && strpos($A['bendahara']->body, 'name="one" value="' . $tdue . '"') !== FALSE && strpos($A['bendahara']->body, 'Distribusi Tunggakan') !== FALSE, 'tunggakan v4: daftar, tombol pengingat per baris, distribusi');
$A['bendahara']->post('admin/tunggakan/ingatkan', array('one' => $tdue));
ok((int) one("SELECT COUNT(*) FROM due_reminders WHERE due_id=$tdue") === 1, 'kirim pengingat per baris membuat 1 pengingat');
$A['bendahara']->post('admin/tunggakan/ingatkan', array('due_ids' => array($tdue)));
ok((int) one("SELECT COUNT(*) FROM due_reminders WHERE due_id=$tdue") === 1, 'pengingat ulang < 3 hari dilewati');
$A['bendahara']->get('admin/tunggakan?periode=' . $past); ok(strpos($A['bendahara']->body, 'Iuran E2E Tunggak') !== FALSE, 'filter periode tunggakan');
$A['auditor']->get('admin/tunggakan'); ok($A['auditor']->code === 200 && strpos($A['auditor']->body, 'name="one"') === FALSE && strpos($A['auditor']->body, 'href="/sukamulya/admin/dues"') === FALSE, 'auditor: lihat tunggakan tanpa tombol pengingat & tab Iuran');
$A['bendahara']->post('admin/rekonsiliasi', array('recon_date' => $today, 'bank_balance' => '1000000', 'note' => "Uji $tag"));
ok((int) one("SELECT COUNT(*) FROM cash_reconciliations WHERE note='Uji $tag'") === 1, 'rekonsiliasi dicatat');
$A['admin']->get('admin/settings'); noerr($A['admin'], 'pengaturan');
$A['admin']->get('admin/pengaturan/riwayat'); noerr($A['admin'], 'riwayat pengaturan');

/* ---------- 06 TEMA WARNA + SAKELAR FITUR ---------- */
$A['admin']->get('admin/settings'); $form = $A['admin']->form_fields($A['admin']->body);
ok(isset($form['site_name'], $form['theme_accent']) && $form['theme_accent'] !== '', 'form pengaturan memuat isian tema');
$site_before = one("SELECT setting_value FROM settings WHERE setting_key='site_name'");
$A['admin']->post('admin/settings', array_merge($form, array('theme_accent' => '#ffee00', 'theme_navy' => '#082654')));
ok(one("SELECT setting_value FROM settings WHERE setting_key='theme_accent'") !== '#ffee00', 'warna aksen terlalu terang (kontras < 3:1) ditolak');
$A['admin']->post('admin/settings', array_merge($form, array('theme_accent' => '#1d4ed8', 'theme_navy' => '#14213d', 'complaint_anonymous' => '1', 'notify_email' => '1')));
// Unggah banner: semua format foto umum (PNG/GIF) diterima; file palsu ditolak.
$banner0 = (string) one("SELECT setting_value FROM settings WHERE setting_key='home_banner_jpg'");
$gif = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $tag . '.gif'; file_put_contents($gif, base64_decode('R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw=='));
$fake = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $tag . '-palsu.png'; file_put_contents($fake, 'bukan gambar sama sekali');
$A['admin']->get('admin/settings'); $form = $A['admin']->form_fields($A['admin']->body);
$A['admin']->post('admin/settings', $form, array('home_banner_jpg' => new CURLFile($img, 'image/png', 'banner.png')));
$b_png = (string) one("SELECT setting_value FROM settings WHERE setting_key='home_banner_jpg'");
ok(substr($b_png, -4) === '.png' && is_file('C:/xampp/htdocs/sukamulya/' . $b_png), 'banner beranda format PNG berhasil diunggah');
$A['admin']->get('admin/settings'); $form = $A['admin']->form_fields($A['admin']->body);
$A['admin']->post('admin/settings', $form, array('footer_banner_jpg' => new CURLFile($gif, 'image/gif', 'footer.gif')));
$b_gif = (string) one("SELECT setting_value FROM settings WHERE setting_key='footer_banner_jpg'");
ok(substr($b_gif, -4) === '.gif', 'banner footer format GIF berhasil diunggah');
$A['admin']->get('admin/settings'); $form = $A['admin']->form_fields($A['admin']->body);
$A['admin']->post('admin/settings', $form, array('home_banner_jpg' => new CURLFile($fake, 'image/png', 'palsu.png')));
ok((string) one("SELECT setting_value FROM settings WHERE setting_key='home_banner_jpg'") === $b_png, 'file bukan gambar (ekstensi .png palsu) ditolak');
foreach (array($b_png, $b_gif) as $u) if (strpos($u, 'uploads/') === 0) @unlink('C:/xampp/htdocs/sukamulya/' . $u);
q("UPDATE settings SET setting_value=" . "'" . $db->real_escape_string($banner0) . "' WHERE setting_key='home_banner_jpg'");
@unlink($gif); @unlink($fake);
ok(one("SELECT setting_value FROM settings WHERE setting_key='theme_accent'") === '#1d4ed8' && one("SELECT setting_value FROM settings WHERE setting_key='site_name'") === $site_before, 'tema disimpan; isian lain tidak berubah');
$W1->get('warga'); ok(strpos($W1->body, '<style id="rt-theme">:root{--theme-accent:#1d4ed8;--theme-navy:#14213d;}</style>') !== FALSE, 'variabel tema tampil di portal warga');
$anon0 = new Http(); $anon0->get(''); ok(strpos($anon0->body, '--theme-accent:#1d4ed8') !== FALSE, 'variabel tema tampil di halaman publik');
$A['admin']->get('admin/login'); $A['admin']->get('admin/penduduk'); ok(strpos($A['admin']->body, '--theme-accent:#1d4ed8') !== FALSE, 'variabel tema tampil di dashboard admin');
ok((int) one("SELECT COUNT(*) FROM settings_history WHERE snapshot_json LIKE '%theme_accent%'") >= 1, 'perubahan tema tercatat di riwayat pengaturan');
$css = file_get_contents(__DIR__ . '/../assets/admin-ops.css');
ok(strpos($css, 'var(--theme-accent,#f0501a)') !== FALSE && !preg_match('/(?<!,)#f0501a(?![0-9a-f])/i', $css), 'CSS memakai var(--theme-accent) dengan fallback warna asli');

/* ---------- 06 FIELD TAMBAHAN WARGA ---------- */
// Isolasi dari putaran tes sebelumnya di DB e2e yang sama (field wajib lama akan menolak warga baru, sesuai aturan).
q("UPDATE custom_field_definitions SET is_active=0");
$A['admin']->post('admin/field-tambahan', array('label' => 'Agama', 'field_type' => 'text', 'citizen_editable' => 1));
ok((int) one("SELECT COUNT(*) FROM custom_field_definitions WHERE label='Agama'") === 0, 'field bertema data sensitif (agama) ditolak');
$A['admin']->post('admin/field-tambahan', array('label' => "Nomor rumah $tag", 'field_type' => 'text', 'is_required' => 1, 'citizen_editable' => 1, 'help_text' => 'Contoh: 12A'));
$A['admin']->post('admin/field-tambahan', array('label' => "Status rumah $tag", 'field_type' => 'select', 'options' => "Milik sendiri\nSewa/Kontrak", 'citizen_editable' => 1));
$A['admin']->post('admin/field-tambahan', array('label' => "Catatan pengurus $tag", 'field_type' => 'text'));
$cf1 = (int) one("SELECT id FROM custom_field_definitions WHERE label='Nomor rumah $tag'"); $cf2 = (int) one("SELECT id FROM custom_field_definitions WHERE label='Status rumah $tag'"); $cf3 = (int) one("SELECT id FROM custom_field_definitions WHERE label='Catatan pengurus $tag'");
ok($cf1 && $cf2 && $cf3, 'tiga field tambahan dibuat (teks wajib, pilihan, khusus pengurus)');
$W1->get('warga/profil/edit'); noerr($W1, 'edit profil warga dengan field tambahan');
ok(strpos($W1->body, "cf[$cf1]") !== FALSE && strpos($W1->body, "cf[$cf3]") === FALSE, 'warga melihat field miliknya, bukan field khusus pengurus');
$pf = $W1->form_fields($W1->body);
$W1->post('warga/profil/edit', array_merge($pf, array("cf[$cf1]" => '', "cf[$cf2]" => 'Sewa/Kontrak')));
ok((int) one("SELECT COUNT(*) FROM citizen_field_values WHERE citizen_id={$cid['w1']}") === 0, 'field wajib kosong ditolak, tidak ada nilai tersimpan');
$W1->post('warga/profil/edit', array_merge($pf, array("cf[$cf1]" => '12A', "cf[$cf2]" => 'Bukan opsi')));
ok((int) one("SELECT COUNT(*) FROM citizen_field_values WHERE citizen_id={$cid['w1']}") === 0, 'opsi pilihan di luar daftar ditolak');
$W1->post('warga/profil/edit', array_merge($pf, array("cf[$cf1]" => '12A', "cf[$cf2]" => 'Sewa/Kontrak', 'email' => "w1.$tag@example.test", 'notify_email' => '1')));
ok(one("SELECT value FROM citizen_field_values WHERE citizen_id={$cid['w1']} AND field_id=$cf1") === '12A' && (int) one("SELECT notify_email FROM citizens WHERE id={$cid['w1']}") === 1, 'nilai field + persetujuan email warga tersimpan');
$A['admin']->get("admin/citizen/{$cid['w1']}"); ok(strpos($A['admin']->body, "cf[$cf3]") !== FALSE && strpos($A['admin']->body, 'value="12A"') !== FALSE, 'pengurus melihat semua field + nilai warga');
$cform = $A['admin']->form_fields($A['admin']->body);
$A['admin']->post("admin/citizen/{$cid['w1']}", array_merge($cform, array("cf[$cf3]" => 'Rumah pojok')));
ok(one("SELECT value FROM citizen_field_values WHERE citizen_id={$cid['w1']} AND field_id=$cf3") === 'Rumah pojok', 'pengurus mengisi field khusus pengurus');

/* ---------- 11 NOTIFIKASI EMAIL (OUTBOX) ---------- */
$W1->post('warga/mutasi/ajukan?jenis=meninggal', array('member_id' => $mem['E2E KEPALA'], 'effective_date' => date('Y-m-d'), 'reason' => 'Uji notifikasi email', 'tempat_meninggal' => 'Rumah'));
$m4 = (int) one("SELECT MAX(id) FROM resident_mutations WHERE citizen_id={$cid['w1']}");
$A['sekretaris']->post("admin/mutasi/$m4", array('decision' => 'rejected', 'review_note' => 'Data uji, bukan kejadian nyata'));
ok((int) one("SELECT COUNT(*) FROM notification_outbox WHERE citizen_id={$cid['w1']} AND dedupe_key LIKE 'mutation:$m4:rejected:%'") === 1, 'keputusan mutasi masuk antrean email');
$body = (string) one("SELECT body FROM notification_outbox WHERE citizen_id={$cid['w1']} ORDER BY id DESC LIMIT 1");
$leak = 0; foreach (q("SELECT nik FROM kk_members WHERE family_id=$fid")->fetch_all(MYSQLI_ASSOC) as $r) if (strpos($body, $r['nik']) !== FALSE) $leak++;
ok($leak === 0 && strpos($body, $kk) === FALSE, 'isi email tidak memuat NIK/nomor KK');
ok((int) one("SELECT COUNT(*) FROM notification_outbox WHERE citizen_id={$cid['w2']}") === 0, 'warga tanpa persetujuan email tidak dikirimi email');
$A['admin']->get('admin/antrean-email'); noerr($A['admin'], 'halaman antrean email');
$A['admin']->post('admin/antrean-email', array('act' => 'process'));
ok(one("SELECT status FROM notification_outbox WHERE citizen_id={$cid['w1']} ORDER BY id DESC LIMIT 1") === 'pending', 'tanpa SMTP email tetap antre (tidak hilang/gagal)');
$A['sekretaris']->get('admin/antrean-email'); ok($A['sekretaris']->code === 403, 'sekretaris tidak dapat membuka antrean email');

/* ---------- 03 PENGADUAN IDENTITAS DISEMBUNYIKAN ---------- */
$W1->get('warga/pengaduan/buat'); ok(strpos($W1->body, 'name="is_anonymous"') !== FALSE, 'opsi sembunyikan identitas tampil saat fitur aktif');
$W1->post('warga/pengaduan/buat', array('category' => 'ketertiban', 'title' => "Parkir liar $tag", 'location' => 'Gang 1', 'description' => 'Mobil parkir menutup jalan setiap malam.', 'is_anonymous' => '1'));
$ap = (int) one("SELECT id FROM complaints WHERE title='Parkir liar $tag'");
ok($ap && (int) one("SELECT is_anonymous FROM complaints WHERE id=$ap") === 1, 'pengaduan anonim tersimpan (tetap terhubung ke akun)');
$wname = "Warga w1 $tag";
$A['sekretaris']->get("admin/pengaduan/$ap"); ok(strpos($A['sekretaris']->body, $wname) === FALSE && strpos($A['sekretaris']->body, 'identitas disembunyikan') !== FALSE, 'petugas tidak melihat nama pelapor anonim');
$A['sekretaris']->get('admin/pengaduan?status=all&q=' . urlencode($wname)); ok(strpos($A['sekretaris']->body, "Parkir liar $tag") === FALSE, 'cari nama pelapor tidak menemukan tiket anonim');
$A['sekretaris']->get('admin/pengaduan?status=all');
$rowhtml = preg_match('/<tr>(?:(?!<\/tr>).)*Parkir liar ' . preg_quote($tag, '/') . '.*?<\/tr>/s', $A['sekretaris']->body, $mm) ? $mm[0] : '';
ok($rowhtml !== '' && strpos($rowhtml, $wname) === FALSE && strpos($rowhtml, 'identitas disembunyikan') !== FALSE, 'daftar tiket menyamarkan nama pelapor anonim');
$A['sekretaris']->post("admin/pengaduan/$ap", array('act' => 'reveal', 'reason' => 'Mencoba membuka identitas'));
ok($A['sekretaris']->post_code === 403, 'sekretaris tidak berwenang membuka identitas');
$A['ketua']->post("admin/pengaduan/$ap", array('act' => 'reveal', 'reason' => 'x'));
ok(strpos($A['ketua']->body, $wname) === FALSE, 'buka identitas tanpa alasan memadai ditolak');
$A['ketua']->post("admin/pengaduan/$ap", array('act' => 'reveal', 'reason' => 'Laporan berulang diduga iseng'));
ok($A['ketua']->post_code === 200 && strpos($A['ketua']->post_body, $wname) !== FALSE && (int) one("SELECT COUNT(*) FROM activity_logs WHERE action='reveal_identity' AND subject='complaint:$ap'") === 1, 'ketua membuka identitas dengan alasan; tercatat di audit');
$A['ketua']->get("admin/pengaduan/$ap"); ok(strpos($A['ketua']->body, $wname) === FALSE, 'identitas kembali tersembunyi pada kunjungan berikutnya');
$A['ketua']->post("admin/pengaduan/$ap", array('status' => 'rejected', 'message' => 'Bukan wewenang RT, diteruskan ke kelurahan.'));
ok((int) one("SELECT COUNT(*) FROM notification_outbox WHERE dedupe_key LIKE 'complaint:$ap:rejected:%'") === 1, 'perubahan status pengaduan masuk antrean email');

/* ---------- CRAWL ---------- */
$ids = array(
    'mut' => $mid, 'cp' => $cpid, 'pg' => $pg, 'nt' => $nt, 'as' => $as, 'pr' => $pr, 'bp' => $bp, 'doc' => (int) one("SELECT MAX(id) FROM documents WHERE deleted_at IS NULL"), 'hs' => $hs,
    'kontak' => (int) one('SELECT MAX(id) FROM important_contacts'), 'akun' => $aid['operator'], 'kkreq' => (int) one('SELECT MAX(id) FROM kk_scan_requests'),
    'req' => (int) one('SELECT MAX(id) FROM service_requests'), 'post' => (int) one("SELECT MAX(id) FROM posts WHERE type='pengumuman'"), 'agenda' => $agenda,
    'tx' => (int) one('SELECT MAX(id) FROM transactions'), 'due' => (int) one('SELECT MAX(id) FROM dues'), 'off' => (int) one('SELECT MAX(id) FROM officials'), 'page' => (int) one('SELECT MAX(id) FROM pages'),
    'cit' => $cid['w1'], 'ql' => (int) one('SELECT MAX(id) FROM quick_links'), 'lt' => (string) one('SELECT type_key FROM letter_types LIMIT 1'),
);
$admin_pages = array('admin', 'admin/notifications', 'admin/search?q=uji', 'admin/chat/inbox', 'admin/settings', 'admin/page-images', 'admin/pages', 'admin/password',
    'admin/posts/pengumuman', 'admin/posts/agenda', 'admin/posts/galeri', 'admin/post/pengumuman', "admin/post/pengumuman/{$ids['post']}", 'admin/officials', 'admin/official', "admin/official/{$ids['off']}",
    'admin/transactions', 'admin/transaction/masuk', 'admin/transaction/keluar', "admin/edit_transaction/{$ids['tx']}", 'admin/quick_links', 'admin/quick_link', "admin/quick_link/{$ids['ql']}",
    'admin/citizens', 'admin/citizen', "admin/citizen/{$ids['cit']}", 'admin/requests', "admin/request_detail/{$ids['req']}", 'admin/letter_types', "admin/letter_type/{$ids['lt']}", 'admin/letter_settings',
    'admin/dues', "admin/due/{$ids['due']}", 'admin/billing_campaign', "admin/page/{$ids['page']}", 'admin/kk/pengajuan', "admin/kk/pengajuan/{$ids['kkreq']}",
    'admin/penduduk', 'admin/penduduk?tampil=keluarga', 'admin/penduduk/rekap', "admin/penduduk/keluarga/$fid",
    'admin/mutasi', 'admin/mutasi?status=all', 'admin/mutasi/baru', "admin/mutasi/baru?keluarga=$fid&jenis=ubah_alamat", "admin/mutasi/baru?keluarga=$fid&jenis=meninggal", "admin/mutasi/{$ids['mut']}",
    'admin/pengaduan', 'admin/pengaduan?status=all', "admin/pengaduan/{$ids['cp']}", 'admin/program', 'admin/program/baru', "admin/program/{$ids['pg']}", "admin/program/{$ids['pg']}/ubah",
    'admin/notulen', 'admin/notulen/baru', "admin/notulen/{$ids['nt']}", 'admin/ronda', 'admin/ronda?dari=' . date('Y-m-d', strtotime('-14 days')),
    'admin/inventaris', 'admin/inventaris/baru', 'admin/inventaris/pinjam', 'admin/inventaris/pinjam?status=all', "admin/inventaris/{$ids['as']}", "admin/inventaris/{$ids['as']}/ubah",
    'admin/kontak', "admin/kontak/{$ids['kontak']}", 'admin/belanja', 'admin/belanja/baru', "admin/belanja/{$ids['pr']}", 'admin/bansos', 'admin/bansos/baru', "admin/bansos/{$ids['bp']}", "admin/bansos/{$ids['bp']}/ubah",
    'admin/arsip', "admin/arsip/{$ids['doc']}", 'admin/serah-terima', "admin/serah-terima/{$ids['hs']}", 'admin/akun', "admin/akun/{$ids['akun']}", 'admin/audit', 'admin/backup', 'admin/pengaturan/riwayat',
    'admin/tunggakan', 'admin/rekonsiliasi', 'admin/field-tambahan', "admin/field-tambahan/$cf2", 'admin/antrean-email', "admin/pengaduan/$ap",
    // Desain ulang v4: tab, filter, paginasi, ekspor agregat.
    'admin?statistik=12', 'admin/citizens?status=active&sort=name&hal=2', 'admin/penduduk?jk=L&usia=dewasa', 'admin/mutasi?jenis=perubahan&status=all', 'admin/mutasi?periode=' . date('Y-m') . '&status=all&hal=9',
    "admin/mutasi/baru?keluarga=$fid&jenis=ubah_data&anggota=999999", 'admin/pengaduan?status=all&kategori=keamanan&hal=3',
    'admin/bansos?tab=calon', 'admin/bansos?tab=salur&status=disalurkan', 'admin/bansos?tab=laporan', 'admin/bansos?periode=x&status=aktif', 'admin/bansos/ekspor', 'admin/fitur', 'admin/fitur?tab=akses', 'admin/fitur?tab=riwayat');
$warga_pages = array('warga', 'warga/layanan', 'warga/riwayat', 'warga/ajukan', 'warga/iuran', 'warga/profil', 'warga/profil/edit', 'warga/password', 'warga/chat', 'warga/keluarga', 'warga/keluarga/scan', 'warga/keputusan',
    'warga/mutasi', 'warga/mutasi/ajukan', 'warga/mutasi/ajukan?jenis=masuk', 'warga/mutasi/ajukan?jenis=meninggal', 'warga/mutasi/ajukan?jenis=ubah_alamat', "warga/mutasi/{$ids['mut']}",
    'warga/pengaduan', 'warga/pengaduan/buat', "warga/pengaduan/{$ids['cp']}", 'warga/ronda', 'warga/kontak-penting');
$public_pages = array('', 'pengumuman', 'agenda', 'galeri', 'struktur', 'keuangan', 'cari?q=rt', 'profil', 'informasi', 'kontak', 'warga/login', 'warga/daftar', 'admin/login', "pengumuman/{$ids['post']}");
if ($ids['agenda']) $public_pages[] = "agenda/{$ids['agenda']}";
$crawl_errors = array(); $crawled = 0; $denied = array();
$check = function ($h, $who, $p) use (&$crawl_errors, &$crawled) {
    $h->get($p); $crawled++;
    if ($h->code >= 500 || preg_match(ERR, $h->body)) {
        $pos = preg_match(ERR, $h->body, $m, PREG_OFFSET_CAPTURE) ? $m[0][1] : 0;
        $crawl_errors[] = "$who $p HTTP {$h->code} :: " . preg_replace('/\s+/', ' ', mb_substr(strip_tags(substr($h->body, $pos, 900)), 0, 350));
    }
    return $h->code;
};
foreach ($A as $role => $h) { if ($role === 'ketua2') continue; foreach ($admin_pages as $p) {$code = $check($h, $role, $p); if ($code === 403) $denied[$role] = ($denied[$role] ?? 0) + 1;} }
foreach (array('W1' => $W1, 'W2' => $W2) as $who => $h) foreach ($warga_pages as $p) $check($h, $who, $p);
$anon = new Http(); foreach ($public_pages as $p) $check($anon, 'anon', $p);
foreach (array_merge($admin_pages, $warga_pages) as $p) { if (strpos($p, 'login') !== FALSE) continue; $code = $check($anon, 'anon', $p); if ($code === 200 && strpos($p, 'admin') === 0 && $p !== 'admin/login') $crawl_errors[] = "anon dapat membuka $p tanpa login"; }
ok(!$crawl_errors, "crawl $crawled permintaan (9 peran admin, 2 warga, anonim) tanpa error PHP/DB/500" . ($crawl_errors ? ":\n   - " . implode("\n   - ", array_slice(array_unique($crawl_errors), 0, 40)) : ''));
$results[] = 'INFO halaman ditolak (403) per peran: ' . json_encode($denied);

/* ---- Desain ulang v4: fungsi baru ---- */
$A['admin']->get('admin'); ok(strpos($A['admin']->body, 'Statistik Warga') !== FALSE && strpos($A['admin']->body, 'Pengajuan Surat Terbaru') !== FALSE && strpos($A['admin']->body, 'Pengaduan Terbaru') !== FALSE && strpos($A['admin']->body, 'Monitoring Operasional') !== FALSE, 'dashboard v4: statistik, pengaduan, surat, monitoring tampil');
$A['operator']->get('admin'); ok(strpos($A['operator']->body, 'Iuran Warga') === FALSE && strpos($A['operator']->body, 'Saldo Kas RT') === FALSE, 'dashboard v4: operator tidak melihat kartu iuran/kas');
$A['sosial']->get('admin/bansos/ekspor?format=xlsx'); $bansos_xml=xlsx_xml($A['sosial']->body); ok(strpos($A['sosial']->ctype, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') === 0 && strpos($bansos_xml, 'Sembako E2E') !== FALSE && strpos($bansos_xml, 'Nilai tersalur') !== FALSE, 'ekspor bansos: XLSX agregat per program');
ok((int) one("SELECT COUNT(*) FROM activity_logs WHERE action='export' AND subject='aid_programs'") > 0, 'ekspor bansos tercatat di audit');
$A['sosial']->get('admin/bansos?tab=laporan'); ok(strpos($A['sosial']->body, 'Sembako E2E') !== FALSE && strpos($A['sosial']->body, 'pg-total') !== FALSE, 'tab laporan bansos menampilkan rekap + total');
$mid = (int) one("SELECT id FROM kk_members WHERE family_id=$fid AND status='aktif' ORDER BY id LIMIT 1");
$A['admin']->get("admin/mutasi/baru?keluarga=$fid&jenis=ubah_data&anggota=$mid"); ok((bool) preg_match('/value="' . $mid . '"[^>]*selected/', $A['admin']->body), 'form mutasi: anggota terpilih otomatis dari Data Penduduk');
$A['admin']->get('admin/penduduk'); ok(strpos($A['admin']->body, "anggota=$mid&amp;jenis=ubah_data") !== FALSE || strpos($A['admin']->body, "anggota=$mid&jenis=ubah_data") !== FALSE, 'data penduduk: tombol koreksi menaut ke mutasi anggota');
$A['admin']->get('admin/citizens'); ok(strpos($A['admin']->body, 'class="pg-pager"') !== FALSE && strpos($A['admin']->body, 'Menampilkan') !== FALSE, 'akun warga: pager & ringkasan tampil');
$aticket = (string) one("SELECT ticket_no FROM complaints WHERE id=$ap");
$A['sekretaris']->get('admin/pengaduan?status=all&q=' . rawurlencode($aticket));
$arow = preg_match('#<tr>(?:(?!</tr>).)*' . preg_quote($aticket, '#') . '(?:(?!</tr>).)*</tr>#s', $A['sekretaris']->body, $mm) ? $mm[0] : '';
ok($arow !== '' && strpos($arow, $wname) === FALSE && strpos($arow, 'identitas disembunyikan') !== FALSE, 'daftar pengaduan v4 tetap menyembunyikan nama pelapor anonim');

/* ---------- Dashboard warga (header dua tingkat, favorit, heksagon) ---------- */
$W1->get('warga');
ok($W1->code === 200 && strpos($W1->body, 'class="dwi-grid dwi-honey"') !== FALSE && substr_count($W1->body, 'class="dwi-tile ') === 12, 'dashboard warga: 12 menu heksagon tampil');
ok(strpos($W1->body, 'Warga w1 ' . $tag) !== FALSE && strpos($W1->body, 'Ahmad Fauzi') === FALSE, 'dashboard: nama warga dari akun login, tidak di-hardcode');
foreach (array('Beranda', 'Profil Saya', 'Layanan Surat', 'Iuran Warga', 'Galeri', 'Agenda', 'Kontak Pengurus') as $menu) if (!ok(strpos($W1->body, '<span>' . $menu . '</span>') !== FALSE, "header warga memuat menu $menu")) break;
$favkey = function ($h) {preg_match_all('#<a class="dwf-item"[^>]*data-menu-key="([a-z_]+)"#', $h->body, $m); return $m[1];};
ok($favkey($W1) === array('profile', 'letters', 'dues', 'chat'), 'favorit default: Profil Saya, Pengajuan Surat, Iuran Warga, Chat Pengurus');
$W1->post('warga/favorit/simpan', array('favorites' => array('gallery', 'events', 'complaints', 'dues')));
ok($favkey($W1) === array('gallery', 'events', 'complaints', 'dues'), 'Atur Menu: 4 favorit tersimpan sesuai urutan');
$W1->post('warga/favorit/simpan', array('favorites' => array('gallery', 'events', 'complaints', 'dues', 'chat')));
ok($favkey($W1) === array('gallery', 'events', 'complaints', 'dues'), 'favorit lebih dari 4 ditolak (tidak mengubah pilihan)');
$W1->post('warga/favorit/simpan', array('favorites' => array('tidak_ada')));
ok($favkey($W1) === array('gallery', 'events', 'complaints', 'dues'), 'favorit dengan kunci tidak dikenal ditolak');
$W2->get('warga'); ok($favkey($W2) === array('profile', 'letters', 'dues', 'chat'), 'favorit tersimpan per akun (warga lain tetap default)');

/* ---------- Manajemen Fitur & Menu (UAT-01..14) ---------- */
$fver = function ($code) {$r = row("SELECT t.* FROM tenant_features t JOIN app_features f ON f.id=t.feature_id WHERE f.code='$code' AND t.tenant_id=1"); return substr(sha1($r['enabled'] . '|' . $r['visible_to_residents'] . '|' . $r['visible_to_staff'] . '|' . $r['updated_at']), 0, 16);};
$fval = function ($code, $field = 'enabled') {return (int) one("SELECT t.$field FROM tenant_features t JOIN app_features f ON f.id=t.feature_id WHERE f.code='$code' AND t.tenant_id=1");};
$fset = function ($who, $code, $field, $value, $reason = 'Uji E2E fitur') use ($A, $fver) {return $A[$who]->post('admin/fitur/update', array('code' => $code, 'field' => $field, 'value' => $value, 'reason' => $reason, 'version' => $fver($code)));};
ok((int) one("SELECT COUNT(*) FROM app_features") >= 20 && (int) one("SELECT COUNT(*) FROM tenant_features WHERE tenant_id=1 AND enabled=1") >= 20, 'katalog fitur terpasang; fitur lama aktif setelah migrasi');
$A['admin']->get('admin/fitur'); ok($A['admin']->code === 200 && strpos($A['admin']->body, 'Manajemen Fitur') !== FALSE && strpos($A['admin']->body, 'Pratinjau Menu Portal Warga') !== FALSE, 'admin membuka Manajemen Fitur & Menu');
foreach (array('admin/fitur?tab=akses', 'admin/fitur?tab=riwayat', 'admin/fitur?kategori=Keuangan&q=iuran') as $p) {$A['admin']->get($p); noerr($A['admin'], "halaman $p");}
$A['operator']->get('admin/fitur'); ok($A['operator']->code === 403, 'UAT-05 operator tidak dapat membuka Manajemen Fitur');
$A['ketua']->post('admin/fitur/update', array('code' => 'complaints', 'field' => 'enabled', 'value' => 0)); ok($A['ketua']->code === 403 && $fval('complaints') === 1, 'UAT-05 ketua/operator tidak dapat mengubah fitur (403)');
$cmp_before = (int) one("SELECT COUNT(*) FROM complaints");
$open_cmp = (int) one("SELECT COUNT(*) FROM complaints WHERE status IN ('submitted','triaged','assigned','in_progress','waiting_resident','reopened')");
if ($open_cmp) {$fset('admin', 'complaints', 'enabled', 0, ''); ok($fval('complaints') === 1, 'UAT-10 menonaktifkan fitur dengan transaksi terbuka wajib alasan');}
$audit0 = (int) one("SELECT COUNT(*) FROM tenant_feature_audit");
$fset('admin', 'complaints', 'enabled', 0, 'Uji UAT-01 nonaktif'); ok($fval('complaints') === 0, 'UAT-01 admin menonaktifkan Pengaduan');
ok((int) one("SELECT COUNT(*) FROM tenant_feature_audit") === $audit0 + 1 && strpos((string) one("SELECT new_value FROM tenant_feature_audit ORDER BY id DESC LIMIT 1"), '"enabled":0') !== FALSE, 'UAT-08 audit mencatat nilai lama/baru + alasan');
$W1->get('warga'); ok(strpos($W1->body, 'warga/pengaduan') === FALSE, 'UAT-01 menu Pengaduan hilang dari portal warga');
ok(!in_array('complaints', $favkey($W1), TRUE) && substr_count($W1->body, 'class="dwi-tile ') === 11, 'fitur nonaktif hilang dari favorit & grid heksagon warga');
$W1->get('warga/pengaduan'); ok($W1->code === 403 && strpos($W1->body, 'Layanan sedang tidak tersedia') !== FALSE, 'UAT-02 URL langsung warga ditolak server');
$W1->get("warga/pengaduan/{$ids['cp']}"); ok($W1->code === 403 && strpos($W1->body, $wname) === FALSE, 'UAT-02 detail tiket tidak bocor');
curl_setopt($W1->c, CURLOPT_HTTPHEADER, array('X-Requested-With: XMLHttpRequest', 'Accept: application/json')); $W1->get('warga/pengaduan/buat'); curl_setopt($W1->c, CURLOPT_HTTPHEADER, array());
ok($W1->code === 403 && strpos($W1->body, 'feature_disabled') !== FALSE, 'UAT-09 permintaan AJAX ditolak gate yang sama (JSON 403)');
$A['sekretaris']->get('admin/pengaduan'); ok($A['sekretaris']->code === 403, 'fitur nonaktif ditolak untuk pengurus non-admin');
$A['sekretaris']->get('admin'); ok(strpos($A['sekretaris']->body, 'href="/sukamulya/admin/pengaduan"') === FALSE, 'menu Pengaduan hilang dari sidebar pengurus');
$A['admin']->get('admin/pengaduan?status=all'); ok($A['admin']->code === 200 && strpos($A['admin']->body, 'sedang <b>nonaktif</b>') !== FALSE, 'Administrator tetap dapat menyelesaikan tiket lama (dengan banner)');
$fset('admin', 'complaints', 'enabled', 1, 'Uji UAT-03 aktif'); $W1->get('warga/pengaduan');
ok($fval('complaints') === 1 && $W1->code === 200 && (int) one("SELECT COUNT(*) FROM complaints") === $cmp_before, 'UAT-03 diaktifkan kembali: menu & riwayat pengaduan tetap ada');
// UAT-06: induk Iuran nonaktif → anak Tunggakan ikut tertutup tanpa mengubah nilai anak.
$fset('admin', 'dues', 'enabled', 0, 'Uji UAT-06 induk');
$A['bendahara']->get('admin/tunggakan'); ok($A['bendahara']->code === 403 && $fval('dues_arrears') === 1, 'UAT-06 induk OFF: Tunggakan tidak dapat diakses, nilai anak tetap tersimpan');
$W1->get('warga/iuran'); ok($W1->code === 403, 'UAT-06 iuran warga ikut tertutup');
$fset('admin', 'dues', 'enabled', 1, 'Uji UAT-06 pulih'); $A['bendahara']->get('admin/tunggakan'); ok($A['bendahara']->code === 200, 'induk aktif kembali: Tunggakan dapat diakses');
// Visibilitas warga saja: pengumuman disembunyikan dari publik/warga, pengurus tetap bisa mengelola.
$fset('admin', 'announcements', 'visible_to_residents', 0);
$anonF = new Http(); $anonF->get('pengumuman'); $A['operator']->get('admin/posts/pengumuman');
ok($anonF->code === 403 && $A['operator']->code === 200, 'visibilitas warga OFF: halaman publik ditolak, pengurus tetap mengelola');
$anonF->get(''); ok(strpos($anonF->body, 'href="/sukamulya/pengumuman"') === FALSE, 'BR-10 tautan Pengumuman hilang dari navigasi publik');
$fset('admin', 'announcements', 'visible_to_residents', 1);
$anonF->get(''); ok(strpos($anonF->body, 'href="/sukamulya/pengumuman"') !== FALSE, 'tautan Pengumuman kembali tampil setelah diaktifkan');
$A['sekretaris']->get('admin'); ok(strpos($A['sekretaris']->body, 'href="/sukamulya/admin/pengaduan"') !== FALSE, 'menu Pengaduan kembali tampil di sidebar pengurus');
// UAT-13: versi kadaluarsa ditolak, tidak menimpa diam-diam.
$stale = $fver('gallery'); $fset('admin', 'gallery', 'visible_to_staff', 0); $A['admin']->post('admin/fitur/update', array('code' => 'gallery', 'field' => 'enabled', 'value' => 0, 'version' => $stale));
ok($fval('gallery') === 1 && $fval('gallery', 'visible_to_staff') === 0, 'UAT-13 simpan dengan versi lama ditolak (konflik versi)');
$fset('admin', 'gallery', 'visible_to_staff', 1);
// UAT-14: kode fitur baru default OFF.
q("DELETE t FROM tenant_features t JOIN app_features f ON f.id=t.feature_id WHERE f.code='documents' AND t.tenant_id=1");
$A['inventaris']->get('admin/arsip'); $docs_off = $fval('documents') === 0;
q("UPDATE tenant_features t JOIN app_features f ON f.id=t.feature_id SET t.enabled=1 WHERE f.code='documents' AND t.tenant_id=1");
ok($docs_off, 'UAT-14 fitur yang baru terdaftar default OFF');
ok((int) one("SELECT COUNT(*) FROM tenant_features WHERE tenant_id=1 AND enabled=0") === 0, 'semua fitur dipulihkan aktif setelah uji');

/* ---------- BERSIHKAN (hanya di DB e2e; DB dibuang setelahnya) ---------- */
@unlink($img);
$finished = TRUE;
echo implode(PHP_EOL, $results), PHP_EOL, ($fail ? "$fail GAGAL" : 'SEMUA LULUS'), PHP_EOL;
exit($fail ? 1 : 0);
