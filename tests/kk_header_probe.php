<?php
if(PHP_SAPI!=='cli')exit;
$magick= FCPATH.'plugin/ImageMagick-7.1.2-Q16-HDRI/magick.exe';$exe= FCPATH.'plugin/Tesseract-OCR/tesseract.exe';
function probe_run($args){$p=proc_open($args,array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('file','NUL','w')),$pipes);fclose($pipes[0]);$s=stream_get_contents($pipes[1]);fclose($pipes[1]);proc_close($p);return $s;}
$f=tempnam(sys_get_temp_dir(),'kk_header_');$seen=array();
try{
foreach(array('normal','threshold','erode')as $variant){$args=array($magick,$argv[1],'-crop',$argv[2],'+repage','-background','white','-alpha','remove','-alpha','off','-resize','300%','-colorspace','Gray');if($variant==='threshold')array_push($args,'-threshold','60%');if($variant==='erode')array_push($args,'-negate','-morphology','Dilate','Disk:1','-negate');array_push($args,'-bordercolor','white','-border','30','PNG:'.$f);probe_run($args);
foreach(array(7,13,8)as $mode){foreach(array(false,true)as $digits){$args=array($exe,$f,'stdout','--psm',(string)$mode);if(isset($argv[3]))array_push($args,'--tessdata-dir',dirname(__DIR__).'/tools/ocr-bin','-l',$argv[3]);if(isset($argv[4]))array_push($args,'--oem',$argv[4]);if($digits)array_push($args,'-c','tessedit_char_whitelist=0123456789');array_push($args,'-c','tessedit_create_tsv=1');$tsv=probe_run($args);$parts=array();$c=100;foreach(explode("\n",$tsv)as $l){$v=explode("\t",$l,12);if(count($v)===12&&$v[0]==='5'&&trim($v[11])!==''){$parts[]=trim($v[11]);$c=min($c,(float)$v[10]);}}$text=implode('',$parts);$key=array_search($text,$seen,TRUE);if($key===false){$seen[]=$text;$key=count($seen)-1;}echo json_encode(array('variant'=>$variant,'psm'=>$mode,'digits_only'=>$digits,'length'=>strlen($text),'valid'=>preg_match('/^\d{16}$/D',$text)===1,'confidence'=>$c,'agreement_group'=>$key)),PHP_EOL;}}
}
}finally{unlink($f);}



