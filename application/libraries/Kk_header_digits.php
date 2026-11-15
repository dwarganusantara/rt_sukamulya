<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Optical matching for the angular OCR-A digits used on older KK headers.
 * Templates contain only synthetic glyphs 0..9, never resident identifiers.
 */
class Kk_header_digits
{
    public function read($components,$headerHeight,$readGlyph)
    {
        $templates=json_decode(file_get_contents(__DIR__.'/../data/kk_ocra_digits.json'),TRUE);
        foreach($templates as &$v)$v=base64_decode($v);unset($v);
        $groups=array();
        foreach(explode("\n",$components)as $line) {
            if(!preg_match('/^\s*\d+:\s+(\d+)x(\d+)\+(\d+)\+(\d+).*gray\(255\)/',$line,$m))continue;
            $box=array('w'=>(int)$m[1],'h'=>(int)$m[2],'x'=>(int)$m[3],'y'=>(int)$m[4]);
            if($box['h']<max(15,$headerHeight*.09) || $box['w']<$box['h']*.15 || $box['w']>$box['h']*1.1)continue;
            $cy=$box['y']+$box['h']/2;$group=NULL;
            foreach($groups as $i=>$g)if(abs($g['cy']-$cy)<$box['h']*.3 && abs($g['h']-$box['h'])<$box['h']*.35){$group=$i;break;}
            if($group===NULL){$groups[]=array('cy'=>$cy,'h'=>$box['h'],'boxes'=>array());$group=count($groups)-1;}
            $groups[$group]['boxes'][]=$box;
        }
        $found=array();
        foreach($groups as $g) {
            if(count($g['boxes'])<16 || count($g['boxes'])>24)continue;
            usort($g['boxes'],function($a,$b){return $a['x']<=>$b['x'];});$digits=array();
            foreach($g['boxes'] as $box) {
                $pixels=$readGlyph($box);if(strlen($pixels)!==960)continue;$scores=array();
                foreach($templates as $digit=>$template){$distance=0;for($p=0;$p<960;$p++)$distance+=abs(ord($pixels[$p])-ord($template[$p]));$scores[(string)$digit]=$distance/(960*255);}
                asort($scores);$keys=array_keys($scores);$values=array_values($scores);
                if($values[0]>.18 || $values[1]-$values[0]<.035)continue;
                $box['digit']=(string)$keys[0];$digits[]=$box;
            }
            if(count($digits)!==16)continue;
            $gaps=array();for($i=1;$i<16;$i++)$gaps[]=$digits[$i]['x']-$digits[$i-1]['x'];
            $sorted=$gaps;sort($sorted);$pitch=$sorted[7];
            foreach($gaps as $gap)if($gap<$pitch*.65 || $gap>$pitch*1.35)continue 2;
            $found[]=implode('',array_column($digits,'digit'));
        }
        $found=array_values(array_unique($found));
        return count($found)===1?$found[0]:'';
    }
}
