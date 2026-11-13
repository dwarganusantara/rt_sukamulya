<?php
if(PHP_SAPI!=='cli')exit;
$templates=array();for($i=0;$i<=9;$i++){
$args=array('/usr/bin/convert','-background','white','-fill','black','-font',FCPATH . 'assets/fonts/arial.ttf','-pointsize','60','label:'.$i,'-alpha','off','-trim','+repage','-resize','24x40!','-colorspace','Gray','-depth','8','gray:-');
$p=proc_open($args,array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('file','NUL','w')),$pipes);fclose($pipes[0]);$s=stream_get_contents($pipes[1]);fclose($pipes[1]);proc_close($p);if(strlen($s)!==960)throw new RuntimeException('Invalid glyph');$templates[(string)$i]=base64_encode($s);
}file_put_contents(__DIR__.'/../application/data/kk_ocra_digits.json',json_encode($templates));echo 'Generated ten synthetic OCR-A glyph templates',PHP_EOL;
