<?php
/*
 * Regresi scan KK: Jenis Kelamin (tabel atas) dan Status Hubungan Dalam Keluarga (tabel bawah) harus terbaca
 * walau nomor urut baris tidak terbaca OCR, header tabel bawah bertingkat (sel gabungan) dengan baris nomor kolom,
 * baris kosong '-' di bawah data, dan walau foto sedikit miring/buram (JPEG). Urutan anggota tidak boleh bergeser.
 * Memakai ImageMagick + Tesseract lokal (tanpa ekstensi GD). Data KK sepenuhnya fiktif.
 */
if (PHP_SAPI !== 'cli') {http_response_code(404); exit;}
define('BASEPATH', __DIR__);
$lib = __DIR__ . '/../application/libraries/';
require $lib . 'Kk_local_engine.php'; require_once $lib . 'Kk_fields.php';
function kk_two_table_image($out) {
// Format seperti KK asli: baris nomor kolom (1)..(10) & (11)..(18), header bertingkat dengan sel gabungan,
// baris kosong '-' setelah data. Data fiktif.
// Membuat gambar KK sintetis (data fiktif) mirip format Dukcapil untuk menguji OCR.
$magick = 'C:/Program Files/ImageMagick-7.1.2-Q16-HDRI/magick.exe';
$W = 3300; $H = 2340; $m = array();
$m[] = "font 'Arial'"; $m[] = 'fill black'; $m[] = 'stroke none';
$text = function ($x, $y, $s, $size = 30, $bold = FALSE) use (&$m) {$m[] = 'font-size ' . $size; $m[] = 'font ' . ($bold ? "'Arial-Bold'" : "'Arial'"); $m[] = sprintf("text %d,%d '%s'", $x, $y, str_replace("'", "\\'", $s));};
$line = function ($x1, $y1, $x2, $y2) use (&$m) {$m[] = 'stroke black'; $m[] = 'stroke-width 3'; $m[] = sprintf('line %d,%d %d,%d', $x1, $y1, $x2, $y2); $m[] = 'stroke none';};
$text(1180, 150, 'KARTU KELUARGA', 64, TRUE);
$text(1300, 230, 'No. 3201234567890001', 44, TRUE);
foreach (array(array('Nama Kepala Keluarga', 'BUDI SANTOSO'), array('Alamat', 'JL MELATI NO 12'), array('RT/RW', '009/008')) as $i => $r) {$text(120, 330 + $i * 50, $r[0], 30); $text(560, 330 + $i * 50, ': ' . $r[1], 30);}
foreach (array(array('Desa/Kelurahan', 'SUKAMULYA'), array('Kecamatan', 'CIKUPA'), array('Kabupaten/Kota', 'TANGERANG')) as $i => $r) {$text(1900, 330 + $i * 50, $r[0], 30); $text(2300, 330 + $i * 50, ': ' . $r[1], 30);}
// Tabel atas: No | Nama | NIK | JK | Tempat | Tgl | Agama | Pendidikan | Pekerjaan | Gol. Darah
$xs = array(100, 190, 720, 1130, 1420, 1720, 1980, 2200, 2560, 3000, 3200);
$top = 520; $hh = 110; $rh = 62; $rows = array(array('(2)','(3)','(4)','(5)','(6)','(7)','(8)','(9)','(10)'),
 array('BUDI SANTOSO', '3201010101800001', 'LAKI-LAKI', 'TANGERANG', '01-01-1980', 'ISLAM', 'SLTA/SEDERAJAT', 'KARYAWAN SWASTA', 'O'),
 array('SITI AMINAH', '3201014202850002', 'PEREMPUAN', 'BOGOR', '02-02-1985', 'ISLAM', 'DIPLOMA III', 'MENGURUS RUMAH TANGGA', 'A'),
 array('RIZKY PRATAMA', '3201010303100003', 'LAKI-LAKI', 'TANGERANG', '03-03-2010', 'ISLAM', 'SLTP/SEDERAJAT', 'PELAJAR/MAHASISWA', 'B'), array('-','-','-','-','-','-','-','-','-'), array('-','-','-','-','-','-','-','-','-'));
$bottom = $top + $hh + $rh * count($rows);
foreach ($xs as $x) $line($x, $top, $x, $bottom);
$line($xs[0], $top, end($xs), $top); $line($xs[0], $top + $hh, end($xs), $top + $hh); $line($xs[0], $bottom, end($xs), $bottom);
for ($r = 1; $r < count($rows); $r++) $line($xs[0], $top + $hh + $r * $rh, end($xs), $top + $hh + $r * $rh);
$heads = array(array('No'), array('Nama Lengkap'), array('NIK'), array('Jenis', 'Kelamin'), array('Tempat Lahir'), array('Tanggal', 'Lahir'), array('Agama'), array('Pendidikan'), array('Jenis Pekerjaan'), array('Golongan', 'Darah'));
foreach ($heads as $c => $h) foreach ($h as $k => $t) $text($xs[$c] + 14, $top + 46 + $k * 38, $t, 28, TRUE);
foreach ($rows as $r => $row) {$y = $top + $hh + $r * $rh + 46; $text($xs[0] + 20, $y, $r ? ($r <= 3 ? (string) $r : (string) $r) : '(1)', 30); foreach ($row as $c => $v) $text($xs[$c + 1] + 14, $y, $v, 28);}
// Tabel bawah: No | Status Perkawinan | Tgl Perkawinan | Status Hubungan Dalam Keluarga | Kewarganegaraan | No Paspor | No KITAP | Nama Ayah | Nama Ibu
$xs2 = array(100, 190, 560, 880, 1300, 1640, 1900, 2160, 2680, 3200);
$top2 = $bottom + 90; $rows2 = array(array('(11)','(12)','(13)','(14)','(15)','(16)','(17)','(18)'),
 array('KAWIN', '10-10-2008', 'KEPALA KELUARGA', 'WNI', '-', '-', 'SLAMET', 'SUMIATI'),
 array('KAWIN', '10-10-2008', 'ISTRI', 'WNI', '-', '-', 'AHMAD', 'KARTINI'),
 array('BELUM KAWIN', '-', 'ANAK', 'WNI', '-', '-', 'BUDI SANTOSO', 'SITI AMINAH'), array('-','-','-','-','-','-','-','-'), array('-','-','-','-','-','-','-','-'));
$bottom2 = $top2 + $hh + $rh * count($rows2);
// Header dua baris dengan sel gabungan seperti KK asli: "Dokumen Imigrasi" (Paspor|KITAP) & "Nama Orang Tua" (Ayah|Ibu).
$mid = $top2 + (int) ($hh / 2);
foreach ($xs2 as $k => $x) $line($x, in_array($k, array(6, 8), TRUE) ? $mid : $top2, $x, $bottom2);
$line($xs2[0], $top2, end($xs2), $top2); $line($xs2[5], $mid, end($xs2), $mid); $line($xs2[0], $top2 + $hh, end($xs2), $top2 + $hh); $line($xs2[0], $bottom2, end($xs2), $bottom2);
for ($r = 1; $r < count($rows2); $r++) $line($xs2[0], $top2 + $hh + $r * $rh, end($xs2), $top2 + $hh + $r * $rh);
$heads2 = array(array('No'), array('Status', 'Perkawinan'), array('Tanggal', 'Perkawinan'), array('Status Hubungan', 'Dalam Keluarga'), array('Kewarganegaraan'));
foreach ($heads2 as $c => $h) foreach ($h as $k => $t) $text($xs2[$c] + 14, $top2 + 46 + $k * 38, $t, 28, TRUE);
$text($xs2[5] + 60, $top2 + 40, 'Dokumen Imigrasi', 26, TRUE); $text($xs2[7] + 160, $top2 + 40, 'Nama Orang Tua', 26, TRUE);
foreach (array(5 => 'No. Paspor', 6 => 'No. KITAP', 7 => 'Ayah', 8 => 'Ibu') as $c => $t) $text($xs2[$c] + 14, $mid + 40, $t, 26, TRUE);
foreach ($rows2 as $r => $row) {$y = $top2 + $hh + $r * $rh + 46; $text($xs2[0] + 20, $y, $r ? (string) $r : '', 30); foreach ($row as $c => $v) $text($xs2[$c + 1] + 14, $y, $v, 28);}
$mvg = $out . '.mvg'; file_put_contents($mvg, implode("\n", $m));
$cmd = array($magick, '-size', $W . 'x' . $H, 'xc:white', '-draw', '@' . $mvg, $out);
$p = proc_open($cmd, array(1 => array('pipe', 'w'), 2 => array('pipe', 'w')), $pipes, NULL, NULL, array('bypass_shell' => TRUE));
echo stream_get_contents($pipes[1]), stream_get_contents($pipes[2]); proc_close($p);
}
$magick = 'C:/Program Files/ImageMagick-7.1.2-Q16-HDRI/magick.exe'; $tess = 'C:/Program Files/Tesseract-OCR/tesseract.exe';
if (!is_file($magick) || !is_file($tess)) {echo "SKIP: ImageMagick/Tesseract tidak tersedia\n"; exit(0);}
$dir = sys_get_temp_dir(); $png = $dir . '/kk_two_table_' . getmypid() . '.png'; $jpg = $dir . '/kk_two_table_' . getmypid() . '.jpg';
ob_start(); kk_two_table_image($png); ob_end_clean();
$p = proc_open(array($magick, $png, '-background', 'white', '-rotate', '0.8', '-resize', '75%', '-blur', '0x0.8', '-quality', '70', $jpg), array(), $pipes, NULL, NULL, array('bypass_shell' => TRUE)); proc_close($p);
$expect = array(array('L', 'Kepala Keluarga', '3201010101800001'), array('P', 'Istri', '3201014202850002'), array('L', 'Anak', '3201010303100003'));
$fail = 0;
foreach (array($png => 'image/png', $jpg => 'image/jpeg') as $file => $mime) {
    $n = (new Kk_fields())->normalize((new Kk_local_engine())->scan($file, $mime, $tess, '', $magick));
    $label = basename($file);
    if (count($n['anggota']) !== 3) {echo "FAIL $label: anggota " . count($n['anggota']) . "\n"; $fail++; continue;}
    foreach ($expect as $i => $e) {
        $m = $n['anggota'][$i]; $ok = $m['jenis_kelamin'] === $e[0] && $m['hubungan_keluarga'] === $e[1] && $m['nik'] === $e[2];
        echo ($ok ? 'PASS' : 'FAIL') . " $label #" . ($i + 1) . ": jk=$m[jenis_kelamin] hubungan=$m[hubungan_keluarga] nik=$m[nik]\n";
        if (!$ok) $fail++;
    }
}
@unlink($png); @unlink($png . '.mvg'); @unlink($jpg);
echo $fail ? "$fail FAILED\n" : "ALL PASS: jenis kelamin & hubungan keluarga terbaca (lurus dan foto miring)\n";
exit($fail ? 1 : 0);