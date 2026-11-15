<?php
if(PHP_SAPI!=='cli')exit;
require __DIR__.'/kk_ocr_image_test.php';
define('FCPATH',dirname(__DIR__).'/');require FCPATH.'application/config/kk_scan.php';
$path=tempnam(sys_get_temp_dir(),'kk_ruled_');
try {
    $im=imagecreatetruecolor(1550,600);$white=imagecolorallocate($im,255,255,255);$black=imagecolorallocate($im,0,0,0);imagefill($im,0,0,$white);
    $font='C:/Windows/Fonts/arial.ttf';
    $text=function($x,$y,$s,$size=12)use($im,$black,$font){imagettftext($im,$size,0,$x,$y,$black,$font,$s);};
    $text(540,80,'KARTU KELUARGA',30);$text(480,140,'No. 9900000000009999',28);$text(240,200,'Alamat : JALAN SINTETIS');
    $x=array(40,70,410,560,640,850,940,1020,1290,1460,1540);
    $y=array(260,312,342);for($i=1;$i<=10;$i++)$y[]=342+$i*23;
    imagesetthickness($im,2);foreach($x as $v)imageline($im,$v,260,$v,572,$black);foreach($y as $v)imageline($im,40,$v,1540,$v,$black);
    foreach(array('No','Nama Lengkap','NIK','Jenis','Tempat Lahir','Tanggal','Agama','Pendidikan','Jenis Pekerjaan','Golongan') as $i=>$v)$text($x[$i]+5,281,$v,11);
    $text(565,302,'Kelamin',11);$text(855,302,'Lahir',11);$text(1465,302,'Darah',11);
    for($i=1;$i<=10;$i++) {
        $values=$i<=5?array((string)$i,'CONTOH WARGA SINTETIS '.$i,'990000000000'.str_pad($i,4,'0',STR_PAD_LEFT),$i%2?'LAKI-LAKI':'PEREMPUAN','KOTA CONTOH','01-01-2000','ISLAM','SLTA','KARYAWAN','O'):array((string)$i,'-','-','-','-','-','-','-','-','-');
        foreach($values as $j=>$value) {
            // Keep synthetic text inside its printed cell, as on a real KK.
            $fontSize=10;
            while($fontSize>6) {$box=imagettfbbox($fontSize,0,$font,$value);if($box[2]-$box[0]<$x[$j+1]-$x[$j]-8)break;$fontSize--;}
            $text($x[$j]+4,358+($i-1)*23,$value,$fontSize);
        }
    }
    imagepng($im,$path);imagedestroy($im);
    $data=(new Kk_fields())->normalize((new Kk_local_engine())->scan($path,'image/png',$config['kk_tesseract'],$config['kk_pdftoppm'],$config['kk_magick']));
    if($data['jumlah_anggota']!==5)throw new RuntimeException('Ruled table expected five members, got '.$data['jumlah_anggota']);
    foreach($data['anggota'] as $i=>$m) {
        foreach(array('nama_lengkap','nik','jenis_kelamin','tempat_lahir','tanggal_lahir','agama','pendidikan','pekerjaan','golongan_darah')as $field)if($m[$field]==='')throw new RuntimeException('Ruled table missing '.$field);
        if($m['nik']!=='990000000000'.str_pad($i+1,4,'0',STR_PAD_LEFT))throw new RuntimeException('Ruled table NIK mismatch');
        if($m['jenis_kelamin']!==($i%2?'P':'L'))throw new RuntimeException('Ruled table female/male mapping mismatch');
    }
    echo 'PASS realistic ruled table: five members, nine fields per member, blank rows ignored, exact synthetic NIK'.PHP_EOL;
} finally {if(is_file($path))unlink($path);}
