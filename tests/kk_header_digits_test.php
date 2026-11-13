<?php
if(PHP_SAPI!=='cli')exit;
define('BASEPATH',__DIR__);require __DIR__.'/../application/libraries/Kk_header_digits.php';
$magick='/usr/bin/convert';
$run=function($args){$p=proc_open($args,array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('file','NUL','w')),$pipes);fclose($pipes[0]);$s=stream_get_contents($pipes[1]);fclose($pipes[1]);if(proc_close($p)!==0)throw new RuntimeException('Image conversion failed');return $s;};
$file=tempnam(sys_get_temp_dir(),'kk_digit_test_');
try {
    foreach(array('9901023456789012','9876543210012345','0123456789012345','123456789012345','12345678901234567') as $number) {
        $run(array($magick,'-size','2000x300','xc:white','-fill','black','-font','C:/Windows/Fonts/OCRAEXT.TTF','-pointsize','60','-annotate','+200+150',$number,'PNG:'.$file));
        $components=$run(array($magick,$file,'-colorspace','Gray','-threshold','60%','-negate','-define','connected-components:verbose=true','-connected-components','8','null:'));
        $found=(new Kk_header_digits())->read($components,300,function($box)use($run,$magick,$file){return $run(array($magick,$file,'-crop',sprintf('%dx%d+%d+%d',$box['w'],$box['h'],$box['x'],$box['y']),'+repage','-resize','24x40!','-colorspace','Gray','-alpha','off','-depth','8','gray:-'));});
        $expected=strlen($number)===16?$number:'';
        if($found!==$expected)throw new RuntimeException('Synthetic header glyph recognition mismatch at length '.strlen($number));
    }
    $templates=json_decode(file_get_contents(__DIR__.'/../application/data/kk_ocra_digits.json'),TRUE);
    $components='';for($i=0;$i<16;$i++)$components.=' '.($i+1).': 24x40+'.($i*35).'+50 0,0 100 gray(255)'.PHP_EOL;
    $ambiguous=(new Kk_header_digits())->read($components,200,function($box){return str_repeat(chr(128),960);});
    if($ambiguous!=='')throw new RuntimeException('Ambiguous glyph accepted');
    echo 'PASS synthetic OCR-A headers: all ten digits, leading zero, exact 16 digits, truncated/extra/ambiguous rejected'.PHP_EOL;
} finally {if(is_file($file))unlink($file);}
