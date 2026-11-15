<?php
if(PHP_SAPI!=='cli') exit;
define('BASEPATH',__DIR__);
require __DIR__.'/../application/libraries/Kk_local_engine.php';
require __DIR__.'/../application/libraries/Kk_fields.php';
function kk_synthetic_image($path,$count) {
    $im=imagecreatetruecolor(2100,300+$count*75);
    $white=imagecolorallocate($im,255,255,255);$black=imagecolorallocate($im,0,0,0);
    imagefill($im,0,0,$white);$font='C:/Windows/Fonts/arial.ttf';
    $write=function($x,$y,$s)use($im,$black,$font){imagettftext($im,24,0,$x,$y,$black,$font,$s);};
    $write(80,60,'KARTU KELUARGA');$write(80,115,'No. 9900000000009999');
    $write(80,170,'Alamat : JALAN SINTETIS');
    $write(80,240,'Nama');$write(650,240,'NIK');$write(1090,240,'Kelamin');$write(1400,240,'Tanggal');
    for($i=0;$i<$count;$i++) {
        $y=310+$i*75;
        $write(80,$y,'WARGA SINTETIS '.($i+1));$write(600,$y,'990000000000'.str_pad($i+1,4,'0',STR_PAD_LEFT));$write(1130,$y,'L');$write(1400,$y,'01-01-2000');
    }
    imagepng($im,$path);imagedestroy($im);
}
if(realpath($_SERVER['SCRIPT_FILENAME'])===__FILE__) {
    $path=tempnam(sys_get_temp_dir(),'kk_test_');
    try {
        foreach(array(3,6) as $n) {
            kk_synthetic_image($path,$n);
            $raw=(new Kk_local_engine())->scan($path,'image/png','C:/Program Files/Tesseract-OCR/tesseract.exe');
            $data=(new Kk_fields())->normalize($raw);
            if($data['jumlah_anggota']!==$n) throw new RuntimeException('Actual OCR member count failed: expected '.$n.', got '.$data['jumlah_anggota']);
            foreach($data['anggota'] as $i=>$m) if($m['nik']!=='990000000000'.str_pad($i+1,4,'0',STR_PAD_LEFT)) throw new RuntimeException('Actual OCR NIK mismatch');
            echo 'PASS actual local OCR PNG: '.$n.' synthetic members, NIK mapped correctly'.PHP_EOL;
        }
    } finally {if(is_file($path))unlink($path);}
}
