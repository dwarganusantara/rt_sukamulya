<?php
if(PHP_SAPI!=='cli') exit;
require __DIR__.'/kk_ocr_image_test.php';
require __DIR__.'/../application/libraries/Kk_file_types.php';
define('FCPATH',dirname(__DIR__).'/');
require __DIR__.'/../application/config/kk_scan.php';
$base=tempnam(sys_get_temp_dir(),'kk_formats_');$files=array($base);$formats=array();
$convert=function($args)use($config){
    $p=proc_open(array_merge(array($config['kk_magick']),$args),array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('pipe','w')),$pipes,NULL,NULL,array('bypass_shell'=>TRUE));
    fclose($pipes[0]);$out=stream_get_contents($pipes[1]);$error=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);
    if(proc_close($p)!==0) throw new RuntimeException('Fixture conversion failed: '.$error);return $out;
};
try {
    kk_synthetic_image($base,6);
    foreach(array('png','jpg','webp','avif','bmp','tiff','gif','jp2','jxl','pdf') as $format) {
        $file=$base.'.'.$format;$files[]=$file;
        $convert(array($base,$file));$mime=Kk_file_types::detect($file);
        $data=(new Kk_fields())->normalize((new Kk_local_engine())->scan($file,$mime,$config['kk_tesseract'],$config['kk_pdftoppm'],$config['kk_magick']));
        if($data['jumlah_anggota']!==6) throw new RuntimeException($format.': expected 6 members, got '.$data['jumlah_anggota']);
        foreach($data['anggota'] as $i=>$m) if($m['nik']!=='990000000000'.str_pad($i+1,4,'0',STR_PAD_LEFT)) throw new RuntimeException($format.': NIK mismatch');
        echo 'PASS actual OCR '.strtoupper($format).': 6 members and exact NIK'.PHP_EOL;
        $formats[$format]=$mime;
    }
    $firstPage=$base.'-three.png';$files[]=$firstPage;kk_synthetic_image($firstPage,3);
    foreach(array('pdf','tiff') as $format) {
        $file=$base.'-multi.'.$format;$files[]=$file;$convert(array($firstPage,$base,$file));
        $data=(new Kk_fields())->normalize((new Kk_local_engine())->scan($file,Kk_file_types::detect($file),$config['kk_tesseract'],$config['kk_pdftoppm'],$config['kk_magick']));
        if($data['jumlah_anggota']!==6) throw new RuntimeException('Multi-page duplicate detection failed '.$format);
        echo 'PASS two-page '.strtoupper($format).': all pages read, repeated members deduplicated'.PHP_EOL;
    }
    $tooMany=$base.'-six-pages.pdf';$files[]=$tooMany;
    $convert(array($firstPage,$firstPage,$firstPage,$firstPage,$firstPage,$firstPage,$tooMany));
    try {(new Kk_local_engine())->scan($tooMany,'application/pdf',$config['kk_tesseract'],$config['kk_pdftoppm'],$config['kk_magick']);throw new LogicException('Page limit was not enforced');}
    catch(RuntimeException $e) {if(strpos($e->getMessage(),'maksimal 5 halaman')===FALSE)throw $e;echo 'PASS PDF over five pages rejected explicitly'.PHP_EOL;}
    $bad=$base.'-fake.jpg';$files[]=$bad;file_put_contents($bad,'<?php echo "not a photo";');
    try {Kk_file_types::detect($bad);throw new LogicException('Invalid file accepted');} catch(RuntimeException $e) {echo 'PASS fake image rejected by actual MIME'.PHP_EOL;}
    $heic=FCPATH.'tools/ocr-bin/heic-read-test.heic';
    if(is_file($heic)) {
        $data=(new Kk_fields())->normalize((new Kk_local_engine())->scan($heic,Kk_file_types::detect($heic),$config['kk_tesseract'],$config['kk_pdftoppm'],$config['kk_magick']));
        if($data['jumlah_anggota']!==0)throw new RuntimeException('Non-KK HEIC must not fabricate family members');
        echo 'PASS public libheif HEIC: decoded, processed, no fabricated members'.PHP_EOL;
    }
} finally {foreach($files as $f)if(is_file($f))unlink($f);}
