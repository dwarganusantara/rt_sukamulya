<?php
if(PHP_SAPI!=='cli')exit;
define('BASEPATH',__DIR__);require __DIR__.'/../application/libraries/Kk_tsv_parser.php';require __DIR__.'/../application/libraries/Kk_fields.php';
require __DIR__.'/../application/libraries/Kk_local_engine.php';
$engine=new Kk_local_engine();$started=new ReflectionProperty($engine,'started');$started->setValue($engine,microtime(TRUE));$method=new ReflectionMethod($engine,'grid');$grid=$method->invoke($engine,$argv[1],'C:/Program Files/ImageMagick-7.1.2-Q16-HDRI/magick.exe',getimagesize($argv[1]));echo json_encode(array('grid'=>$grid)),PHP_EOL;
foreach(array(6,11,3) as $mode){
$p=proc_open(array('C:/Program Files/Tesseract-OCR/tesseract.exe',$argv[1],'stdout','--psm',(string)$mode,'tsv'),array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('file','NUL','w')),$pipes);fclose($pipes[0]);$tsv=stream_get_contents($pipes[1]);fclose($pipes[1]);proc_close($p);
$headers=0;$ids=0;$digits=array();$words=0;$labels=array();
foreach(explode("\n",$tsv)as $line){$v=explode("\t",$line,12);if(count($v)!==12||$v[0]!=='5')continue;$t=trim($v[11]);$words++;if(in_array(strtoupper($t),array('NO','NO.','NIK'),TRUE))$labels[]=array('label'=>strtoupper($t),'x'=>$v[6],'y'=>$v[7],'h'=>$v[9]);if(strtoupper($t)==='NIK')$headers++;if(preg_match('/^\d{16}$/D',$t))$ids++;if(preg_match('/^\d{10,}$/D',$t))$digits[]=strlen($t);}
echo json_encode(array('psm'=>$mode,'words'=>$words,'nik_headers'=>$headers,'sixteen_digit_tokens'=>$ids,'long_token_lengths'=>$digits,'labels'=>$labels)),PHP_EOL;
$data=(new Kk_fields())->normalize((new Kk_tsv_parser())->parse($tsv,$grid));echo json_encode(array('members'=>$data['jumlah_anggota'],'populated_fields'=>array_map(function($m){return array_keys(array_filter($m,function($v){return is_string($v)&&$v!=='';}));},$data['anggota']))),PHP_EOL;
}
