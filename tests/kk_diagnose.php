<?php
if(PHP_SAPI!=='cli')exit;
if(empty($argv[1])||!is_file($argv[1]))exit('Pakai: php tests/kk_diagnose.php /path/foto-kk.jpg'.PHP_EOL);
define('BASEPATH',__DIR__);require __DIR__.'/../application/libraries/Kk_tsv_parser.php';require __DIR__.'/../application/libraries/Kk_fields.php';
require __DIR__.'/../application/libraries/Kk_local_engine.php';
$magick='/usr/bin/convert';$exe='/usr/bin/tesseract';
// Samakan dengan pipeline: perbaiki orientasi, ratakan transparansi, koreksi pencahayaan (flat-field),
// lalu perbesar bila citra lebih kecil dari 3500 px.
$page=tempnam(sys_get_temp_dir(),'kkdiag_').'.png';
register_shutdown_function(function()use($page){if(is_file($page))@unlink($page);});
$p=proc_open(array($magick,$argv[1],'-auto-orient','-resize','3500x3500','-background','white','-alpha','remove','-alpha','off','-colorspace','sRGB','-colorspace','Gray','(','+clone','-blur','0x60',')','-compose','Divide','-composite','-normalize','-colorspace','sRGB','-strip','PNG:'.$page),array(0=>array('pipe','r'),1=>array('file','/dev/null','w'),2=>array('file','/dev/null','w')),$pipes);
fclose($pipes[0]);if(proc_close($p)!==0||!is_file($page))exit('Gagal menyiapkan gambar dengan ImageMagick.'.PHP_EOL);
$size=getimagesize($page);
$engine=new Kk_local_engine();$started=new ReflectionProperty($engine,'started');$started->setAccessible(TRUE);$started->setValue($engine,microtime(TRUE));
$call=function($name)use($engine){$args=func_get_args();$m=new ReflectionMethod($engine,array_shift($args));$m->setAccessible(TRUE);return $m->invokeArgs($engine,$args);};
$rows=$call('projection',$page,$magick,'1x'.$size[1].'!',TRUE);
$tables=$call('tables',$page,$magick,$size,$rows);
$summary=array();
foreach($tables as $i=>$table)$summary[]=array('table'=>$i,'columns'=>count($table['x']),'rows'=>count($table['y']),'x'=>$table['x']);
$grid=$call('grid',$page,$magick,$size);
echo json_encode(array('file'=>basename($argv[1]),'size'=>array($size[0],$size[1]),'horizontal_lines'=>count($rows),'y'=>$rows)),PHP_EOL;
echo json_encode(array('tables'=>$summary)),PHP_EOL;
echo json_encode(array('grid_columns'=>count($grid['x']??array()),'grid_rows'=>count($grid['y']??array()),'grid'=>$grid)),PHP_EOL;
$run=function($args){$p=proc_open($args,array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('file','/dev/null','w')),$pipes);fclose($pipes[0]);$out=stream_get_contents($pipes[1]);fclose($pipes[1]);proc_close($p);return $out;};
$darkest=function($text){$v=array();foreach(explode("\n",$text)as $l){if(preg_match('/^(\d+),(\d+):\s*\((\d+)/',$l,$m))$v[(int)$m[2]]=(int)$m[3];}asort($v);$r=array();foreach(array_slice($v,0,15,TRUE)as $y=>$val)$r[]=array('y'=>$y,'value'=>$val);return $r;};
echo json_encode(array('content_box'=>trim($run(array($magick,$page,'-colorspace','Gray','-threshold','55%','-format','%@','info:'))))),PHP_EOL;
echo json_encode(array('darkest_rows_raw'=>$darkest($run(array($magick,$page,'-colorspace','Gray','-scale','1x'.$size[1].'!','-depth','8','txt:-'))))),PHP_EOL;
echo json_encode(array('darkest_rows_thresholded'=>$darkest($run(array($magick,$page,'-colorspace','Gray','-threshold','55%','-scale','1x'.$size[1].'!','-depth','8','txt:-'))))),PHP_EOL;
foreach(array(6,11) as $mode){
$p=proc_open(array($exe,$page,'stdout','-l','eng','--psm',(string)$mode,'tsv'),array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('file','/dev/null','w')),$pipes);fclose($pipes[0]);$tsv=stream_get_contents($pipes[1]);fclose($pipes[1]);proc_close($p);
$headers=0;$ids=0;$digits=array();$words=0;$labels=array();
foreach(explode("\n",$tsv)as $line){$v=explode("\t",$line,12);if(count($v)!==12||$v[0]!=='5')continue;$t=trim($v[11]);$words++;if(in_array(strtoupper($t),array('NO','NO.','NIK'),TRUE))$labels[]=array('label'=>strtoupper($t),'x'=>(int)$v[6],'y'=>(int)$v[7],'h'=>(int)$v[9]);if(strtoupper($t)==='NIK')$headers++;if(preg_match('/^\d{16}$/D',$t))$ids++;if(preg_match('/^\d{10,}$/D',$t))$digits[]=strlen($t);}
echo json_encode(array('psm'=>$mode,'words'=>$words,'nik_headers'=>$headers,'sixteen_digit_tokens'=>$ids,'long_token_lengths'=>$digits,'labels'=>$labels)),PHP_EOL;
$data=(new Kk_fields())->normalize((new Kk_tsv_parser())->parse($tsv,$grid));echo json_encode(array('members'=>$data['jumlah_anggota'],'populated_fields'=>array_map(function($m){return array_keys(array_filter($m,function($v){return is_string($v)&&$v!=='';}));},$data['anggota']))),PHP_EOL;
}
