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
        $header=NULL;
        foreach($words as $w) if(strtoupper($w['t'])==='NIK') {$header=$w;break;}
        if(!$header) return $out;
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
        $aliases=array('nama'=>'nama_lengkap','nik'=>'nik','kelamin'=>'jenis_kelamin','tempat'=>'tempat_lahir','tanggal'=>'tanggal_lahir','agama'=>'agama','pendidikan'=>'pendidikan','pekerjaan'=>'pekerjaan','darah'=>'golongan_darah');
        foreach($words as $w) {
            $key=strtolower(preg_replace('/[^a-z]/i','',$w['t']));
            if(abs($w['y']-$header['y'])<max(4*$header['h'],40) && isset($aliases[$key])) {
                $field=$aliases[$key];
                if(!isset($columns[$field])) $columns[$field]=$w['x']+$w['w']/2;
            }
        }
        asort($columns);
        $keys=array_keys($columns); $centers=array_values($columns);
        $bounds=array();
        if(count($grid['x'] ?? array())===11) {
            $fields=array('nama_lengkap','nik','jenis_kelamin','tempat_lahir','tanggal_lahir','agama','pendidikan','pekerjaan','golongan_darah');
            // Use printed cell borders only after confirming the NIK column.
            if($header['x']>$grid['x'][2] && $header['x']<$grid['x'][3]) {
                foreach($fields as $i=>$field) $bounds[$field]=array($grid['x'][$i+1],$grid['x'][$i+2]);
                $keys=$fields;
            }
        }
        $anchors=array();
        foreach($words as $w) if($w['y']>$header['y']+1.5*$header['h'] && preg_match('/^\d{16}$/D',$w['t'])) {
            // Never use an identifier from an unrelated column as a member row.
            if(abs(($w['x']+$w['w']/2)-($columns['nik'] ?? -10000)) < $w['w']) $anchors[]=$w;
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
            $cy=$anchor['y']+$anchor['h']/2;$top=NULL;$bottom=NULL;
            foreach($grid['y'] ?? array() as $line) {if($line<$cy)$top=$line;elseif($bottom===NULL)$bottom=$line;}
            $geometry[$i]=array('bounds'=>$bounds,'top'=>$top,'bottom'=>$bottom);
            $out['anggota'][]=$row;
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
}
