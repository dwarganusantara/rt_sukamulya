<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Conservative spatial parser: unrecognized columns stay blank for review. */
class Kk_tsv_parser
{
    public function parse($tsv,$grid=array(),&$geometry=NULL)
    {
        $words=array();
        foreach(explode("\n",trim($tsv)) as $line) {
            $v=explode("\t",$line,12);
            if(count($v)!==12 || $v[0]!=='5' || trim($v[11])==='') continue;
            $words[]=array('x'=>(int)$v[6],'y'=>(int)$v[7],'w'=>(int)$v[8],'h'=>(int)$v[9],'c'=>(float)$v[10],'t'=>trim($v[11]));
        }
        $out=array('keluarga'=>array(),'anggota'=>array());
        if(!$words) return $out;
        $gridX=isset($grid['x'])&&is_array($grid['x'])?array_values($grid['x']):array();
        $gridY=isset($grid['y'])&&is_array($grid['y'])?array_values($grid['y']):array();
        $synthetic=FALSE;
        $header=$this->header($words);
        // Header "NIK" can be misread (NlK/N1K) or lost entirely on photos. The printed
        // border is still usable: NIK is always the third column of the identity table.
        if(!$header && count($gridX)===11 && count($gridY)>=3) {
            $header=array('x'=>(int)$gridX[2],'y'=>(int)$gridY[0],'w'=>(int)max(1,$gridX[3]-$gridX[2]),'h'=>(int)max(1,$gridY[1]-$gridY[0]),'c'=>0,'t'=>'NIK');
            $synthetic=TRUE;
        }
        if(!$header) return $out;
        $top=$synthetic?($gridY[1]+4):($header['y']+1.5*$header['h']);
        $headerCenter=$header['x']+$header['w']/2;
        foreach($words as $label) {
            if($label['y']<$header['y'] && $label['h']>$header['h'] && preg_match('/^(NO\.?|NOMOR)$/i',$label['t'])) {
                $geometry['kk_box']=array('x'=>$label['x']+$label['w']+4,'y'=>max(0,$label['y']-4),'h'=>$label['h']+8);
            }
        }
        $metadata=array('alamat'=>'alamat','desa/kelurahan'=>'kelurahan','kelurahan'=>'kelurahan','kecamatan'=>'kecamatan','kabupaten/kota'=>'kota','provinsi'=>'provinsi','pos'=>'kode_pos');
        foreach($words as $label) {
            if($label['y'] >= $header['y']) continue;
            $labelText=strtolower(rtrim($label['t'],':'));
            if(!isset($metadata[$labelText]) && !in_array($labelText,array('rt/rw','rt/rw.'),TRUE)) continue;
            $line=array();
            foreach($words as $w) if(abs($w['y']-$label['y'])<max($label['h'],$w['h'])*0.65 && $w['x']>$label['x']+$label['w'] && $w['c']>=80) $line[]=$w;
            usort($line,function($a,$b){return $a['x']<=>$b['x'];});
            $value=''; $previous=$label['x']+$label['w'];
            foreach($line as $w) {
                if($value!=='' && $w['x']-$previous>8*$label['h']) break;
                $value.=' '.$w['t']; $previous=$w['x']+$w['w'];
            }
            $value=trim(ltrim(trim($value),':'));
            if(strpos($labelText,'rt/rw')===0) {
                if(preg_match('/^(\d{1,3})\s*\/\s*(\d{1,3})$/D',$value,$matches)) {$out['keluarga']['rt']=$matches[1];$out['keluarga']['rw']=$matches[2];}
            } else $out['keluarga'][$metadata[$labelText]]=$value;
        }
        // A KK identifier is accepted only next to a No/Nomor label above the table.
        foreach($words as $w) {
            if($w['y'] >= $header['y'] || !preg_match('/^\d{16}$/D',$w['t']) || $w['c']<80) continue;
            foreach($words as $label) if(preg_match('/^(NO\.?|NOMOR)$/i',$label['t']) && abs($label['y']-$w['y'])<max($w['h'],$label['h'])*1.2 && $label['x']<$w['x']) $out['keluarga']['nomor_kk']=$w['t'];
        }
        $columns=array();
        foreach($words as $w) {
            $field=$this->labelField($w['t']);
            if($field!=='' && abs($w['y']-$header['y'])<max(4*$header['h'],40) && !isset($columns[$field])) {
                $columns[$field]=$w['x']+$w['w']/2;
            }
        }
        asort($columns);
        $keys=array_keys($columns); $centers=array_values($columns);
        $fields=array('nama_lengkap','nik','jenis_kelamin','tempat_lahir','tanggal_lahir','agama','pendidikan','pekerjaan','golongan_darah');
        $bounds=array();
        if(count($gridX)===11) {
            // Use printed cell borders only after confirming the NIK column.
            if($headerCenter>$gridX[2] && $headerCenter<$gridX[3]) {
                foreach($fields as $i=>$field) $bounds[$field]=array($gridX[$i+1],$gridX[$i+2]);
                $keys=$fields;
                $centers=array();
                foreach($fields as $i=>$field) $centers[]=($bounds[$field][0]+$bounds[$field][1])/2;
            }
        }
        $nikLeft=isset($bounds['nik'])?$bounds['nik'][0]:NULL;
        $nikRight=isset($bounds['nik'])?$bounds['nik'][1]:NULL;
        $nikKnown=isset($columns['nik']);
        $nikCenter=$nikKnown?$columns['nik']:$headerCenter;
        $nikTolerance=max($header['w'],80)*1.6;
        $anchors=array();
        foreach($words as $w) {
            if($w['y']<=$top || !preg_match('/^\d{16}$/D',$w['t'])) continue;
            $cx=$w['x']+$w['w']/2;
            // Never use an identifier from an unrelated column as a member row. When no
            // column is known, every 16-digit token below the table header is a member NIK
            // (the KK number itself is printed above the table).
            if($nikLeft!==NULL) {if($cx<$nikLeft || $cx>$nikRight) continue;}
            elseif($nikKnown && abs($cx-$nikCenter)>$nikTolerance) continue;
            $anchors[]=$w;
        }
        // Phone photos often split printed NIK digits into groups. Rejoin digit-only words
        // inside the NIK column per row instead of dropping the whole member.
        if($nikLeft!==NULL || $nikKnown) {
            $digits=array();
            foreach($words as $w) {
                if($w['y']<=$top || !preg_match('/^\d{4,8}$/D',$w['t'])) continue;
                $cx=$w['x']+$w['w']/2;
                if($nikLeft!==NULL) {if($cx<$nikLeft || $cx>$nikRight) continue;}
                elseif(abs($cx-$nikCenter)>$nikTolerance) continue;
                $digits[]=$w;
            }
            usort($digits,function($a,$b){return $a['y']===$b['y']?$a['x']<=>$b['x']:$a['y']<=>$b['y'];});
            $groups=array();
            foreach($digits as $w) {
                $last=count($groups)-1;
                if($last>=0 && abs($w['y']-$groups[$last]['y'])<max($w['h'],$groups[$last]['h'])*0.8) {
                    $groups[$last]['parts'][]=$w;
                    $groups[$last]['h']=max($groups[$last]['h'],$w['h']);
                    continue;
                }
                $groups[]=array('y'=>$w['y'],'h'=>$w['h'],'parts'=>array($w));
            }
            foreach($groups as $group) {
                $parts=$group['parts'];usort($parts,function($a,$b){return $a['x']<=>$b['x'];});
                $value=implode('',array_column($parts,'t'));$confidence=100;
                foreach($parts as $part) $confidence=min($confidence,$part['c']);
                if(strlen($value)!==16) continue;
                $duplicate=FALSE;
                foreach($anchors as $anchor) if(abs($anchor['y']-$group['y'])<max($anchor['h'],$group['h'])) {$duplicate=TRUE;break;}
                if($duplicate) continue;
                $anchors[]=array('x'=>$parts[0]['x'],'y'=>$group['y'],'w'=>end($parts)['x']+end($parts)['w']-$parts[0]['x'],'h'=>$group['h'],'c'=>$confidence,'t'=>$value);
            }
        }
        usort($anchors,function($a,$b){return $a['y']<=>$b['y'];});
        foreach($anchors as $i=>$anchor) {
            $row=array(); $flags=array();
            $gap=isset($anchors[$i+1])?($anchors[$i+1]['y']-$anchor['y'])/2:$anchor['h']*1.7;
            $tolerance=min(max($anchor['h']*0.8,$gap),$anchor['h']*1.7);
            foreach($words as $w) {
                if(abs(($w['y']+$w['h']/2)-($anchor['y']+$anchor['h']/2))>$tolerance) continue;
                $x=$w['x']+$w['w']/2;
                foreach($keys as $j=>$field) {
                    $left=$bounds[$field][0] ?? ($j?($centers[$j-1]+$centers[$j])/2:$centers[$j]-($centers[$j+1] ?? $centers[$j]+100)+$centers[$j]);
                    $right=$bounds[$field][1] ?? (isset($centers[$j+1])?($centers[$j]+$centers[$j+1])/2:INF);
                    if($x >= $left && $x<$right) {
                        $row[$field]=trim(($row[$field] ?? '').' '.$w['t']);
                        if($w['c']<80) $flags[]=$field;
                        break;
                    }
                }
            }
            // Ambiguous identifier is not repaired (O/0, I/1, missing digits).
            $row['nik']=$anchor['c']>=80?$anchor['t']:'';
            $row['fields_need_review']=array_values(array_unique($flags));
            $cy=$anchor['y']+$anchor['h']/2;$rowTop=NULL;$rowBottom=NULL;
            foreach($gridY as $line) {if($line<$cy)$rowTop=$line;elseif($rowBottom===NULL)$rowBottom=$line;}
            $geometry[$i]=array('bounds'=>$bounds,'top'=>$rowTop,'bottom'=>$rowBottom);
            $out['anggota'][]=$row;
        }
        // A single misread digit used to discard the whole member. Keep named rows even when
        // the NIK is not exactly 16 digits; the user must review/fill the identifier manually.
        if(!$out['anggota'] && $top<$this->pageBottom($words)) {
            foreach($this->nameRows($words,$top,$columns,$bounds,$keys,$centers,$gridY,$grid) as $i=>$fallback) {
                $out['anggota'][]=$fallback['member'];
                $geometry[$i]=$fallback['geometry'];
            }
        }
        // The lower KK table restarts row numbers; join by printed row ordinal,
        // never by proximity to the upper table or assumed gender/age.
        $relationship=NULL;
        foreach($words as $w) if(strtolower($w['t'])==='hubungan' && (!$relationship || $w['y']>$relationship['y'])) $relationship=$w;
        if($relationship && $anchors && $relationship['y']>$anchors[count($anchors)-1]['y']) {
            $rx=$relationship['x']+$relationship['w']/2;
            foreach($words as $ordinal) {
                if($ordinal['y']<$relationship['y']+2*$relationship['h'] || !preg_match('/^([1-9][0-9]?)\.?$/D',$ordinal['t'],$match) || $ordinal['x']>($centers[0] ?? 0)-10) continue;
                $index=(int)$match[1]-1;
                if(!isset($out['anggota'][$index])) continue;
                $parts=array();
                foreach($words as $w) if(abs($w['y']-$ordinal['y'])<$ordinal['h']*1.1 && abs($w['x']+$w['w']/2-$rx)<$relationship['w']*2.1 && $w['c']>=80) $parts[]=$w;
                usort($parts,function($a,$b){return $a['x']<=>$b['x'];});
                $text=strtoupper(implode(' ',array_column($parts,'t')));
                if(preg_match('/\b(KEPALA KELUARGA|SUAMI|ISTRI|ANAK|MENANTU|CUCU|ORANG TUA|MERTUA|FAMILI LAIN|PEMBANTU|LAINNYA)\b/',$text,$match)) $out['anggota'][$index]['hubungan_keluarga']=$match[1];
            }
        }
        return $out;
    }

    /** Locate the column-header row even when "NIK" is misread or missing. */
    private function header($words)
    {
        $sorted=$words; usort($sorted,function($a,$b){return $a['y']<=>$b['y'];});
        $bands=array();$current=NULL;
        foreach($sorted as $w) {
            if($current!==NULL) {
                $last=end($current);
                if(abs($w['y']-$last['y'])<=max($w['h'],$last['h'])*0.8) {$current[]=$w;continue;}
                $bands[]=$current;
            }
            $current=array($w);
        }
        if($current) $bands[]=$current;
        $best=NULL;$bestScore=0;
        foreach($bands as $band) {
            $labels=array();$nik=NULL;$minY=NULL;$height=0;$left=NULL;
            foreach($band as $w) {
                $field=$this->labelField($w['t']);
                if($field!=='' ) $labels[$field]=TRUE;
                if($nik===NULL && $this->isNik($w['t'])) $nik=$w;
                if($minY===NULL || $w['y']<$minY['y']) $minY=$w;
                if($w['h']>$height) $height=$w['h'];
                if($left===NULL || $w['x']<$left['x']) $left=$w;
            }
            $score=count($labels)+($nik?3:0);
            if($score<$bestScore) continue;
            $point=$nik?:$left;
            $bestScore=$score;
            $best=array('x'=>(int)$point['x'],'y'=>(int)$minY['y'],'w'=>(int)$point['w'],'h'=>(int)max(1,$height),'c'=>0,'t'=>$nik?'NIK':'');
        }
        // Without a literal NIK label require several distinct column labels so a line of
        // metadata (e.g. "Nama Kepala Keluarga") is never mistaken for the table header.
        if(!$best || ($best['t']!=='NIK' && $bestScore<3)) return NULL;
        return $best;
    }

    private function labelField($text)
    {
        if($this->isNik($text)) return 'nik';
        $aliases=array('nama'=>'nama_lengkap','nik'=>'nik','kelamin'=>'jenis_kelamin','tempat'=>'tempat_lahir','tanggal'=>'tanggal_lahir','agama'=>'agama','pendidikan'=>'pendidikan','pekerjaan'=>'pekerjaan','darah'=>'golongan_darah');
        $key=strtolower(preg_replace('/[^a-z]/i','',$text));
        return isset($aliases[$key])?$aliases[$key]:'';
    }

    /** "NIK" is frequently read as NlK/N1K/N|K on phone photos. */
    private function isNik($text)
    {
        $value=strtoupper(preg_replace('/[^A-Za-z0-9|!]/','',$text));
        $value=strtr($value,array('1'=>'I','L'=>'I','|'=>'I','!'=>'I'));
        return $value==='NIK';
    }

    private function pageBottom($words)
    {
        $bottom=0;
        foreach($words as $w) if($w['y']+$w['h']>$bottom) $bottom=$w['y']+$w['h'];
        return $bottom;
    }

    /**
     * Last-resort rows from the Nama column. A member is retained even when its NIK is
     * misread, matching the review workflow (the identifier stays blank for manual entry).
     */
    private function nameRows($words,$top,$columns,$bounds,$keys,$centers,$gridY,$grid)
    {
        $nameLeft=isset($bounds['nama_lengkap'])?$bounds['nama_lengkap'][0]:NULL;
        $nameRight=isset($bounds['nama_lengkap'])?$bounds['nama_lengkap'][1]:NULL;
        $nameCenter=isset($columns['nama_lengkap'])?$columns['nama_lengkap']:NULL;
        if($nameLeft===NULL && $nameCenter===NULL) return array();
        $nameTolerance=200;
        if($nameCenter!==NULL && isset($columns['nik'])) $nameTolerance=max(120,abs($columns['nik']-$nameCenter)/2+40);
        $lowerTop=!empty($grid['lower']['y'][0])?(int)$grid['lower']['y'][0]:NULL;
        // The bottom table also contains names (parents), but no "Nama" identity column.
        // Stop at its header so parent names are never turned into extra members.
        if($lowerTop===NULL) foreach($words as $w) {
            if($w['y']>$top && preg_match('/^(HUBUNGAN|KEWARGANEGARAAN)$/iu',trim($w['t']))) {$lowerTop=$w['y']-40;break;}
        }
        $stop='/^(NAMA(?:\s+LENGKAP)?|NIK|NO\.?|NOMOR|JENIS(?:\s+KELAMIN)?|KELAMIN|TEMPAT(?:\s+LAHIR)?|TANGGAL(?:\s+LAHIR)?|AGAMA|PENDIDIKAN|PEKERJAAN|JENIS\s+PEKERJAAN|GOLONGAN(?:\s+DARAH)?|DARAH|LAHIR|LENGKAP|STATUS(?:\s+PERKAWINAN)?|HUBUNGAN(?:\s+DALAM\s+KELUARGA)?|KEWARGANEGARAAN|KAWIN|BELUM\s+KAWIN|CERAI.*)$/iu';
        $candidates=array();
        foreach($words as $w) {
            if($w['y']<=$top || ($lowerTop!==NULL && $w['y']>=$lowerTop)) continue;
            if($w['c']<50 || trim($w['t'])==='') continue;
            if(preg_match($stop,trim($w['t']))) continue;
            if(!preg_match('/[A-Za-z]{2}/u',$w['t'])) continue;
            if(preg_match('/^\W*[\d.,\/-]+\W*$/u',$w['t'])) continue;
            $cx=$w['x']+$w['w']/2;
            if($nameLeft!==NULL) {if($cx<$nameLeft || $cx>$nameRight) continue;}
            elseif(abs($cx-$nameCenter)>$nameTolerance) continue;
            $candidates[]=$w;
        }
        usort($candidates,function($a,$b){return $a['y']<=>$b['y'];});
        $groups=array();
        foreach($candidates as $w) {
            $last=count($groups)-1;
            if($last>=0) {
                $group=$groups[$last];$cy=$w['y']+$w['h']/2;
                if(abs($cy-$group['cy'])<=max($w['h'],$group['h'])*0.8) {
                    $groups[$last]['words'][]=$w;
                    $groups[$last]['cy']=($group['cy']*count($group['words'])+$cy)/(count($group['words'])+1);
                    $groups[$last]['h']=max($group['h'],$w['h']);
                    continue;
                }
            }
            $groups[]=array('cy'=>$w['y']+$w['h']/2,'h'=>$w['h'],'words'=>array($w));
        }
        $nikLeft=isset($bounds['nik'])?$bounds['nik'][0]:NULL;
        $nikRight=isset($bounds['nik'])?$bounds['nik'][1]:NULL;
        $rows=array();
        foreach($groups as $group) {
            $parts=$group['words'];usort($parts,function($a,$b){return $a['x']<=>$b['x'];});
            $name=trim(preg_replace('/\s+/u',' ',implode(' ',array_column($parts,'t'))));
            if(!preg_match('/^.{3,}$/us',$name) || preg_match($stop,$name)) continue;
            $member=array('nama_lengkap'=>$name,'nik'=>'','fields_need_review'=>array());
            $digits=array();$confidence=100;
            foreach($words as $w) {
                if(abs(($w['y']+$w['h']/2)-$group['cy'])>max($w['h'],$group['h'])*0.9) continue;
                if(!preg_match('/^\d{1,8}$/D',$w['t'])) continue;
                $cx=$w['x']+$w['w']/2;
                if($nikLeft!==NULL) {if($cx<$nikLeft || $cx>$nikRight) continue;}
                elseif(isset($columns['nik']) && abs($cx-$columns['nik'])>max($w['w'],80)*1.6) continue;
                elseif(strlen($w['t'])!==16) continue;
                $digits[]=$w;$confidence=min($confidence,$w['c']);
            }
            if($digits) {
                usort($digits,function($a,$b){return $a['x']<=>$b['x'];});
                $nik=implode('',array_column($digits,'t'));
                if(strlen($nik)===16 && $confidence>=50) $member['nik']=$nik;
            }
            $flags=array();
            foreach($words as $w) {
                if(abs(($w['y']+$w['h']/2)-$group['cy'])>max($w['h'],$group['h'])*0.9) continue;
                $x=$w['x']+$w['w']/2;
                foreach($keys as $j=>$field) {
                    if($field==='nama_lengkap' || $field==='nik') continue;
                    $left=$bounds[$field][0] ?? ($j?($centers[$j-1]+$centers[$j])/2:$centers[$j]-($centers[$j+1] ?? $centers[$j]+100)+$centers[$j]);
                    $right=$bounds[$field][1] ?? (isset($centers[$j+1])?($centers[$j]+$centers[$j+1])/2:INF);
                    if($x>=$left && $x<$right) {
                        $member[$field]=trim(($member[$field] ?? '').' '.$w['t']);
                        if($w['c']<80) $flags[]=$field;
                        break;
                    }
                }
            }
            $member['fields_need_review']=array_values(array_unique($flags));
            $rowTop=NULL;$rowBottom=NULL;
            foreach($gridY as $line) {if($line<$group['cy'])$rowTop=$line;elseif($rowBottom===NULL)$rowBottom=$line;}
            $rows[]=array('member'=>$member,'geometry'=>array('bounds'=>$bounds,'top'=>$rowTop,'bottom'=>$rowBottom));
            if(count($rows)>=20) break;
        }
        return $rows;
    }
}