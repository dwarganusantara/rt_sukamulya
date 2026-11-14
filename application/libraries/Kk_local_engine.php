<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Kk_local_engine
{
    public function scan($source,$mime,$exe,$pdf='',$magick='')
    {
        $this->started=microtime(TRUE);
        if(!$this->executable($exe) || !is_callable('proc_open')) throw new RuntimeException('Mesin OCR lokal belum tersedia. Hubungi pengelola.');
        $magick=$this->converter($magick);$identify=$this->imageInfo($magick);
        if($magick==='' && function_exists('log_message')) log_message('error','KK OCR: ImageMagick tidak ditemukan. KK_MAGICK='.getenv('KK_MAGICK').' PATH='.getenv('PATH'));
        $temp=array(); $pages=array();
        try {
            if($mime==='application/pdf') {
                if(!$pdf || !$this->executable($pdf)) throw new RuntimeException('Pembaca PDF belum tersedia. Hubungi pengelola.');
                try {$info=$this->run(array($this->companion($pdf,'pdfinfo'),$source));}
                catch(RuntimeException $e) {throw new RuntimeException('PDF rusak atau terkunci. Unggah PDF yang dapat dibuka tanpa password.');}
                if(preg_match('/^Encrypted:\s*yes/mi',$info)) throw new RuntimeException('PDF terkunci. Unggah salinan tanpa password atau enkripsi.');
                if(!preg_match('/^Pages:\s*(\d+)/m',$info,$match)) throw new RuntimeException('Jumlah halaman PDF tidak dapat diperiksa.');
                $count=(int)$match[1];
                if($count<1 || $count>5) throw new RuntimeException('PDF maksimal 5 halaman untuk satu KK. Pisahkan dokumen sebelum upload.');
                for($i=1;$i<=$count;$i++) {
                    $base=tempnam(sys_get_temp_dir(),'kk_'); $temp[]=$base; $temp[]=$base.'.png';
                    $this->run(array($pdf,'-f',(string)$i,'-l',(string)$i,'-singlefile','-scale-to','3500','-png',$source,$base));
                    $pages[]=$base.'.png';
                }
            } elseif($magick && is_file($magick)) {
                require_once __DIR__.'/Kk_file_types.php';
                $coder=Kk_file_types::coders()[$mime] ?? NULL;
                if(!$coder) throw new RuntimeException('Format foto tidak didukung.');
                $input=$coder.':'.$source;
                $limits=array('-limit','memory','256MiB','-limit','map','256MiB','-limit','disk','512MiB','-limit','thread','2');
                $info=$this->run(array_merge($identify,$limits,array('-ping','-format',"%w %h\n",$input)));
                $frames=preg_split('/\r?\n/',trim($info));
                // Animated images use their first frame; multi-page TIFF reads all pages.
                $count=$coder==='TIFF'?count($frames):1;
                if($count>5) throw new RuntimeException('TIFF maksimal 5 halaman untuk satu KK.');
                for($i=0;$i<$count;$i++) {
                    $dimensions=explode(' ',trim($frames[$i]));
                    if(count($dimensions)!==2 || (float)$dimensions[0]*(float)$dimensions[1]>40000000) throw new RuntimeException('Foto melebihi 40 megapiksel. Perkecil foto terlebih dahulu.');
                    $base=tempnam(sys_get_temp_dir(),'kk_');$temp[]=$base;$temp[]=$base.'.png';
                    // Foto ponsel sering tidak rata terangnya (vignette/bayangan). Membagi citra terhadap versi
                    // blur-nya (koreksi pencahayaan) membuat goresan huruf dan garis tabel kontras merata
                    // sehingga OCR dan deteksi garis bekerja jauh lebih andal.
                    $this->run(array_merge(array($magick),$limits,array($input.'['.$i.']','-auto-orient','-resize','3500x3500','-background','white','-alpha','remove','-alpha','off','-colorspace','sRGB','-colorspace','Gray','(','+clone','-blur','0x60',')','-compose','Divide','-composite','-normalize','-colorspace','sRGB','-strip','PNG:'.$base.'.png')));
                    $pages[]=$base.'.png';
                }
            } else {
                if(!in_array($mime,array('image/jpeg','image/png'),TRUE)) throw new RuntimeException('Konverter foto belum tersedia. Hubungi pengelola.');
                $pages[]=$source;
            }
            // Foto/scan dari ponsel kadang sedikit miring. Namun memutar gambar menambah interpolasi yang
            // menghaluskan goresan huruf tipis, sehingga OCR justru memburuk: pada uji langsung, NIK 16 digit
            // hilang TOTAL setelah deskew meski kemiringannya hanya ~0,6 derajat. Tesseract masih sanggup membaca
            // teks dengan kemiringan kecil, jadi gambar hanya diputar bila kemiringannya benar-benar besar.
            if($magick && is_file($magick)) {
                foreach($pages as $k=>$page) {
                    $angle=(float)trim($this->run(array($magick,$page,'-deskew','40%','-format','%[deskew:angle]','info:')));
                    if(abs($angle)<1.2)continue;
                    $straight=tempnam(sys_get_temp_dir(),'kk_dsk_');$temp[]=$straight;$temp[]=$straight.'.png';
                    $this->run(array($magick,$page,'-background','white','-filter','Lanczos','-deskew','40%','+repage','-unsharp','0x1.5+1.0+0.02','-colorspace','sRGB','PNG:'.$straight.'.png'));
                    if(is_file($straight.'.png') && filesize($straight.'.png')>0)$pages[$k]=$straight.'.png';
                }
            }
            $indPaths=array(
                '/usr/share/tesseract/tessdata/ind.traineddata',
                '/usr/share/tesseract-ocr/4.00/tessdata/ind.traineddata',
                '/usr/share/tesseract-ocr/5/tessdata/ind.traineddata'
            );
            $hasInd=FALSE;
            foreach($indPaths as $indPath)if(is_file($indPath)){$hasInd=TRUE;break;}
            $lang=$hasInd?'ind+eng':'eng';
            require_once __DIR__.'/Kk_tsv_parser.php';
            $result=array('keluarga'=>array(),'anggota'=>array());$seen=array();
            foreach($pages as $page) {
                $size=@getimagesize($page);
                if(!$size || $size[0]*$size[1]>40000000) throw new RuntimeException('Foto tidak valid atau terlalu besar.');
                require_once __DIR__.'/Kk_fields.php';
                $grid=$magick && is_file($magick)?$this->grid($page,$magick,$size):array();
                $data=array('keluarga'=>array(),'anggota'=>array());$best=-1;$geometry=array();
                // Sparse text mode recognizes ruled tables that block mode skips.
                foreach(array(6,11) as $mode) {
                    $tsv=$this->run(array($exe,$page,'stdout','-l',$lang,'--psm',(string)$mode,'tsv'));
                    $boxes=array();$candidate=(new Kk_tsv_parser())->parse($tsv,$grid,$boxes);
                    $normalized=(new Kk_fields())->normalize($candidate);$score=0;
                    foreach($normalized['anggota'] as $m) {$score+=100;foreach($m as $v)if(is_string($v)&&$v!=='')$score++;}
                    if($score>$best) {$best=$score;$data=$candidate;$geometry=$boxes;}
                }
                // If page OCR loses the header, verify and read the printed cells.
                // Do not require a successfully read NIK before retaining a named row.
                if(!$data['anggota'] && count($grid['x'] ?? array())===11 && count($grid['y'] ?? array())>3) {
                    $columns=array('nama_lengkap','nik','jenis_kelamin','tempat_lahir','tanggal_lahir','agama','pendidikan','pekerjaan','golongan_darah');$bounds=array();
                    foreach($columns as $i=>$field)$bounds[$field]=array($grid['x'][$i+1],$grid['x'][$i+2]);
                    // Jangan menggantungkan pembacaan anggota pada hasil OCR header "NIK".
                    // Header sering terbaca keliru (mis. NlK), padahal sel nama dan baris data masih terbaca.
                    for($r=1;$r<count($grid['y'])-1;$r++) {
                        $name=$this->cell($page,$bounds['nama_lengkap'],$grid['y'][$r],$grid['y'][$r+1],$magick,$exe,$lang,$temp);
                        $nameValue=trim(preg_replace('/\s+/u',' ',$name['value']));
                        // Abaikan sel kosong, teks header, dan teks yang terlalu pendek.
                        if(!preg_match('/^.{3,}$/us',$nameValue))continue;
                        if(preg_match('/^(NAMA(?:\s+LENGKAP)?|NIK|NO\.?|NOMOR)$/iu',$nameValue))continue;
                        if(!preg_match('/[a-z]{2}/i',$nameValue))continue;

                        $nik=$this->cell($page,$bounds['nik'],$grid['y'][$r],$grid['y'][$r+1],$magick,$exe,$lang,$temp);
                        $i=count($data['anggota']);
                        $m=array('nama_lengkap'=>$nameValue,'nik'=>'','fields_need_review'=>array());
                        if($nik['confidence']>=80 && preg_match('/^\d{16}$/D',$nik['value']))$m['nik']=$nik['value'];
                        if($name['confidence']<80)$m['fields_need_review'][]='nama_lengkap';
                        $data['anggota'][]=$m;
                        $geometry[$i]=array('bounds'=>$bounds,'top'=>$grid['y'][$r],'bottom'=>$grid['y'][$r+1]);
                    }
                }
                foreach($data['anggota'] as $i=>&$m) {
                    $normalized=(new Kk_fields())->normalize(array('anggota'=>array($m)));
                    foreach(array('nama_lengkap','nik','jenis_kelamin','tempat_lahir','tanggal_lahir','agama','pendidikan','pekerjaan','golongan_darah') as $field) {
                        if(!empty($normalized['anggota'][0][$field]) || empty($geometry[$i]['bounds'][$field]) || $geometry[$i]['top']===NULL || $geometry[$i]['bottom']===NULL)continue;
                        $box=$geometry[$i];$read=$this->cell($page,$box['bounds'][$field],$box['top'],$box['bottom'],$magick,$exe,$lang,$temp);
                        $value=$read['value'];$confidence=$read['confidence'];
                        if($field==='nik' && ($confidence<80 || !preg_match('/^\d{16}$/D',$value)))continue;
                        $m[$field]=$value;
                        if($confidence<80)$m['fields_need_review'][]=$field;
                    }
                }
                unset($m);
                if($magick && is_file($magick)) $this->relationships($data['anggota'],$geometry,$grid,$page,$magick,$exe,$lang,$temp);
                if(empty($data['keluarga']['nomor_kk']) && isset($grid['y'][0]) && $magick && is_file($magick)) {
                    require_once __DIR__.'/Kk_header_digits.php';
                    $height=$grid['y'][0];
                    $components=$this->run(array($magick,$page,'-crop',$size[0].'x'.$height.'+0+0','+repage','-colorspace','Gray','-threshold','60%','-negate','-define','connected-components:verbose=true','-connected-components','8','null:'));
                    $number=(new Kk_header_digits())->read($components,$height,function($box)use($magick,$page){
                        return $this->run(array($magick,$page,'-crop',sprintf('%dx%d+%d+%d',$box['w'],$box['h'],$box['x'],$box['y']),'+repage','-resize','24x40!','-colorspace','Gray','-alpha','off','-depth','8','gray:-'));
                    });
                    if($number!=='')$data['keluarga']['nomor_kk']=$number;
                }
                if(empty($data['keluarga']['nomor_kk']) && isset($geometry['kk_box']) && $magick && is_file($magick)) {
                    $box=$geometry['kk_box'];$cell=tempnam(sys_get_temp_dir(),'kk_number_');$temp[]=$cell;$temp[]=$cell.'.png';
                    $this->run(array($magick,$page,'-crop',sprintf('%dx%d+%d+%d',$size[0]-$box['x'],$box['h'],$box['x'],$box['y']),'+repage','-bordercolor','white','-border','12','PNG:'.$cell.'.png'));
                    $numberTsv=$this->run(array($exe,$cell.'.png','stdout','-l',$lang,'--psm','7','tsv'));
                    $parts=array();$confidence=100;
                    foreach(explode("\n",$numberTsv) as $line) {$v=explode("\t",$line,12);if(count($v)===12 && $v[0]==='5' && trim($v[11])!==''){$parts[]=trim($v[11]);$confidence=min($confidence,(float)$v[10]);}}
                    $number=implode('',$parts);
                    if($confidence>=80 && preg_match('/^\d{16}$/D',$number))$data['keluarga']['nomor_kk']=$number;
                }
                foreach($data['keluarga'] as $key=>$value) {
                    if($key==='nomor_kk' && !empty($result['keluarga'][$key]) && $result['keluarga'][$key]!==$value) throw new RuntimeException('Dokumen berisi nomor KK berbeda. Unggah satu keluarga per dokumen.');
                    if($value!=='' && empty($result['keluarga'][$key])) $result['keluarga'][$key]=$value;
                }
                foreach($data['anggota'] as $member) {
                    $nik=$member['nik'] ?? '';
                    if($nik!=='' && isset($seen[$nik])) {
                        if($seen[$nik]!==$member) throw new RuntimeException('Data anggota antarhalaman berbeda. Pisahkan halaman dan periksa ulang.');
                        continue;
                    }
                    if($nik!=='') $seen[$nik]=$member;
                    $result['anggota'][]=$member;
                }
            }
            // Tanpa ImageMagick, foto tidak diperjelas/deskew dan garis tabel tidak terbaca:
            // beri pesan penyebab yang sebenarnya, bukan seolah foto pengguna yang salah.
            if(!$result['anggota'] && $magick==='') throw new RuntimeException('Belum ada anggota terbaca. Konverter gambar (ImageMagick) tidak ditemukan di server sehingga foto tidak dapat diperjelas sebelum dibaca. Pasang ImageMagick atau set variabel KK_MAGICK ke path executable yang benar (mis. /usr/bin/magick), lalu hubungi pengelola.');
            return $result;
        } finally {foreach($temp as $path) if(is_file($path)) @unlink($path);}
    }

    private $started;

    /**
     * Paket ImageMagick menyediakan `convert` (IM6) atau `magick` (IM7). Lokasi tidak selalu
     * /usr/bin: distro, snap, conda, atau build source bisa menaruhnya di path lain. Selain
     * daftar path umum, cari juga melalui $PATH (dan open_basedir tetap dihormati PHP).
     */
    private function converter($configured)
    {
        $candidates=array();
        if(is_string($configured) && $configured!=='') $candidates[]=$configured;
        $dirs=array('/usr/bin','/usr/local/bin','/bin','/snap/bin');
        foreach(array(getenv('PATH'),$_SERVER['PATH'] ?? '') as $envPath) {
            if(!is_string($envPath) || $envPath==='') continue;
            foreach(explode(PATH_SEPARATOR,$envPath) as $dir) if($dir!=='') $dirs[]=$dir;
        }
        foreach($dirs as $dir) {
            $dir=rtrim(str_replace('\\','/',$dir),'/');
            $candidates[]=$dir.'/magick';
            $candidates[]=$dir.'/convert';
        }
        foreach(array('/opt/*/bin/magick','/opt/*/bin/convert','/usr/local/ImageMagick*/bin/magick','/usr/local/ImageMagick*/bin/convert') as $pattern)
            foreach((array)@glob($pattern) as $match) $candidates[]=$match;
        foreach($candidates as $path) {
            $resolved=$this->resolve($path);
            if($resolved!=='') return $resolved;
        }
        return '';
    }

    /** IM7 memakai `magick identify` bila executable `identify` terpisah tidak ada; IM6 `convert` bisa langsung info:. */
    private function imageInfo($magick)
    {
        if($magick==='') return array('/usr/bin/identify');
        $dir=str_replace('\\','/',dirname($magick));
        foreach(array($dir.'/identify','/usr/bin/identify','/usr/local/bin/identify') as $path) {
            $resolved=$this->resolve($path);
            if($resolved!=='') return array($resolved);
        }
        return strtolower(pathinfo($magick,PATHINFO_FILENAME))==='magick'?array($magick,'identify'):array($magick);
    }

    /** Binary harus ada dan executable; di Windows is_executable kurang konsisten, ekstensi .exe dicoba otomatis. */
    private function executable($path)
    {
        return $this->resolve($path)!=='';
    }

    private function resolve($path)
    {
        if(!is_string($path) || $path==='') return '';
        $candidates=array($path);
        if(DIRECTORY_SEPARATOR==='\\' && strtolower(pathinfo($path,PATHINFO_EXTENSION))!=='exe') $candidates[]=$path.'.exe';
        foreach($candidates as $candidate) {
            if(!is_file($candidate)) continue;
            if(DIRECTORY_SEPARATOR==='\\' || is_executable($candidate)) return $candidate;
        }
        return '';
    }

    /** Cari executable pendamping (mis. pdfinfo di folder yang sama dengan pdftoppm). */
    private function companion($path,$name)
    {
        $dir=str_replace('\\','/',dirname($path));
        foreach(array($dir.'/'.$name,'/usr/bin/'.$name,'/usr/local/bin/'.$name) as $candidate) {
            $resolved=$this->resolve($candidate);
            if($resolved!=='') return $resolved;
        }
        return $name;
    }

    private function cell($page,$bounds,$top,$bottom,$magick,$exe,$lang,&$temp,$psm=7)
    {
        $x=$bounds[0]+4;$y=$top+4;$w=$bounds[1]-$x-4;$h=$bottom-$y-4;
        if($w<10 || $h<10)return array('value'=>'','confidence'=>0);
        $cell=tempnam(sys_get_temp_dir(),'kk_cell_');$temp[]=$cell;$temp[]=$cell.'.png';
        // Sel diperbesar 2x agar huruf kecil di tabel (mis. LAKI-LAKI, ISTRI) terbaca lebih andal.
        $this->run(array($magick,$page,'-crop',sprintf('%dx%d+%d+%d',$w,$h,$x,$y),'+repage','-resize','200%','-bordercolor','white','-border','16','PNG:'.$cell.'.png'));
        $tsv=$this->run(array($exe,$cell.'.png','stdout','-l',$lang,'--psm',(string)$psm,'tsv'));
        $parts=array();$confidence=100;
        foreach(explode("\n",$tsv) as $line) {$v=explode("\t",$line,12);if(count($v)===12 && $v[0]==='5' && trim($v[11])!==''){$parts[]=trim($v[11]);$confidence=min($confidence,(float)$v[10]);}}
        return array('value'=>implode(' ',$parts),'confidence'=>$confidence);
    }

    private function grid($page,$magick,$size)
    {
        $rows=$this->projection($page,$magick,'1x'.$size[1].'!',TRUE);
        if(count($rows)<4)return array();
        // KK memiliki dua tabel dengan garis kolom berbeda (atas: identitas; bawah: status/hubungan).
        // Garis kolom dihitung per pita baris lalu dikelompokkan menjadi tabel, agar keduanya tidak tercampur.
        $tables=$this->tables($page,$magick,$size,$rows);
        foreach($tables as $i=>$table) {
            if(count($table['x'])!==11)continue;
            $grid=array('x'=>$table['x'],'y'=>$table['y']);
            for($j=$i+1;$j<count($tables);$j++) if(count($tables[$j]['x'])>=9) {$grid['lower']=$tables[$j];break;}
            return $grid;
        }
        // Cadangan lama: satu proyeksi untuk seluruh area tabel.
        $top=$rows[0];$bottom=end($rows);$height=$bottom-$top;
        $text=$this->run(array($magick,$page,'-crop',$size[0].'x'.$height.'+0+'.$top,'+repage','-colorspace','Gray','-threshold','55%','-scale',$size[0].'x1!','-depth','8','txt:-'));
        $cols=$this->darkLines($text,FALSE);
        return array('x'=>$cols,'y'=>$rows);
    }

    /** Kelompokkan pita antar-garis horizontal yang memiliki garis kolom sama menjadi satu tabel. */
    private function tables($page,$magick,$size,$rows)
    {
        $tables=array();$current=NULL;
        $close=function()use(&$tables,&$current){if($current && count($current['y'])>=3)$tables[]=$current;$current=NULL;};
        for($i=0;$i<count($rows)-1;$i++) {
            $y1=$rows[$i]+4;$h=$rows[$i+1]-4-$y1;
            if($h<8)continue;
            $text=$this->run(array($magick,$page,'-crop',$size[0].'x'.$h.'+0+'.$y1,'+repage','-colorspace','Gray','-threshold','55%','-scale',$size[0].'x1!','-depth','8','txt:-'));
            $cols=$this->darkLines($text,FALSE);
            if(count($cols)<4){$close();continue;}
            $same=$current && count($current['x'])===count($cols);
            if($same) foreach($cols as $k=>$x) if(abs($x-$current['x'][$k])>12){$same=FALSE;break;}
            if($same){$current['y'][]=$rows[$i+1];continue;}
            $close();
            $current=array('x'=>$cols,'y'=>array($rows[$i],$rows[$i+1]));
        }
        $close();
        return $tables;
    }

    /**
     * Status Hubungan Dalam Keluarga ada di tabel bawah KK. Baris ke-r tabel bawah adalah anggota yang sama dengan
     * baris ke-r tabel atas (struktur resmi KK), sehingga dibaca per sel tanpa bergantung pada angka nomor urut
     * yang sering tidak terbaca OCR. Kolom diverifikasi dari teks header.
     */
    private function relationships(&$members,&$geometry,$grid,$page,$magick,$exe,$lang,&$temp)
    {
        $lower=$grid['lower'] ?? NULL;
        if(!$lower || count($lower['y'])<3)return;
        $fields=new Kk_fields();
        // Urutan baris DATA anggota di tabel atas (0 = baris data pertama). Baris judul / nomor kolom "(1)..(9)"
        // tidak dihitung, karena jumlahnya berbeda dengan tabel bawah.
        $rows=array();
        foreach($members as $i=>$m) {
            $top=$geometry[$i]['top'] ?? NULL;
            $r=$top===NULL?FALSE:array_search($top,$grid['y'],TRUE);
            if($r!==FALSE && $r>=1)$rows[$i]=$r;
        }
        if(!$rows)return;
        $upperStart=min($rows);
        $targets=array();
        foreach($rows as $i=>$r) if($fields->relationship($members[$i]['hubungan_keluarga'] ?? '')==='')$targets[$i]=$r-$upperStart;
        if(!$targets)return;
        $candidates=count($lower['x'])===10?array(3):array();
        for($k=0;$k<count($lower['x'])-1;$k++) if(!in_array($k,$candidates,TRUE))$candidates[]=$k;
        // Header KK asli dua baris dengan sel gabungan ("Status Hubungan Dalam Keluarga" membentang dua baris,
        // "Dokumen Imigrasi"/"Nama Orang Tua" di atas sub-kolom). Tabel bawah terdeteksi mulai baris header kedua,
        // jadi area judul dibaca dari atas header gabungan sampai garis bawah header.
        $headerHeight=$lower['y'][1]-$lower['y'][0];
        $headerTop=max((int)end($grid['y'])+4,(int)round($lower['y'][0]-1.4*$headerHeight));
        $col=NULL;
        foreach($candidates as $k) {
            $head=$this->cell($page,array($lower['x'][$k],$lower['x'][$k+1]),$headerTop,$lower['y'][1],$magick,$exe,$lang,$temp,6);
            if(preg_match('/HUBUNG|DALAM\s*KELUAR|KELUARGA/i',$head['value'])){$col=$k;break;}
        }
        // Cari BARIS DATA PERTAMA tabel bawah pada kolom SHDK: baris pertama yang isinya nilai SHDK resmi.
        // Baris judul, baris nomor kolom "(10) (11) (12)", dan header bertingkat dilewati otomatis.
        // Bila judul kolom tidak terbaca, kolom juga dipilih dari isi sel (kolom lain seperti KAWIN/WNI tertolak).
        $columns=$col===NULL?$candidates:array($col);
        $last=count($lower['y'])-2;
        foreach($columns as $k) {
            $bounds=array($lower['x'][$k],$lower['x'][$k+1]);
            $start=NULL;$first=NULL;$lastHeader=NULL;$reads=array();
            for($row=0;$row<=min($last,5);$row++) {
                $read=$this->cell($page,$bounds,$lower['y'][$row],$lower['y'][$row+1],$magick,$exe,$lang,$temp);
                $reads[$row]=$read;
                // Baris judul ("Status Hubungan Dalam Keluarga") atau baris nomor kolom "(12)".
                if(preg_match('/HUBUNG|DALAM|^\W*\(?\s*\d{1,2}\s*\)?\W*$/i',$read['value'])){$lastHeader=$row;continue;}
                if($fields->relationship($read['value'])!==''){$first=$row;break;}
            }
            if($first===NULL)continue;
            // Awal data = tepat setelah baris judul/nomor kolom terakhir. Bila nilai baris data pertama gagal terbaca,
            // baris itu tetap dihitung sebagai data ke-1 sehingga urutan anggota tidak bergeser.
            $start=$lastHeader!==NULL?$lastHeader+1:$first;
            $first=$reads[$start] ?? NULL;
            // Anggota ke-n tabel atas = baris data ke-n tabel bawah (nomor urut sama pada KK).
            foreach($targets as $i=>$n) {
                $row=$start+$n;
                if($row>$last)continue;
                $read=$reads[$row] ?? $this->cell($page,$bounds,$lower['y'][$row],$lower['y'][$row+1],$magick,$exe,$lang,$temp);
                if($fields->relationship($read['value'])==='')continue;
                $members[$i]['hubungan_keluarga']=$read['value'];
                if($read['confidence']<80)$members[$i]['fields_need_review'][]='hubungan_keluarga';
            }
            return;
        }
    }
    private function projection($page,$magick,$scale,$vertical)
    {
        return $this->darkLines($this->run(array($magick,$page,'-colorspace','Gray','-threshold','55%','-scale',$scale,'-depth','8','txt:-')),$vertical);
    }
    private function darkLines($text,$vertical)
    {
        $groups=array();$last=-100;
        foreach(explode("\n",$text)as $line) {
            if(!preg_match('/^(\d+),(\d+):\s*\((\d+)/',$line,$m) || (int)$m[3]>70)continue;
            $pos=(int)$m[$vertical?2:1];
            if($pos>$last+2)$groups[]=array();
            $groups[count($groups)-1][]=$pos;$last=$pos;
        }
        return array_map(function($g){return (int)round(array_sum($g)/count($g));},$groups);
    }

    
    private function run($args)
    {
        $pipes = array();

        $process = proc_open(
            $args,
            array(
                0 => array('pipe', 'r'),
                1 => array('pipe', 'w'),
                2 => array('pipe', 'w')
            ),
            $pipes,
            NULL,
            NULL,
            array('bypass_shell' => TRUE)
        );

        if (!is_resource($process)) {
            throw new RuntimeException('Mesin OCR tidak dapat dijalankan.');
        }

        fclose($pipes[0]);

        stream_set_blocking($pipes[1], FALSE);
        stream_set_blocking($pipes[2], FALSE);

        $stdout = '';
        $stderr = '';
        $start = microtime(TRUE);
        $exitCode = -1;

        try {
            do {
                $stdout .= stream_get_contents($pipes[1]);
                $stderr .= stream_get_contents($pipes[2]);

                if (
                    strlen($stdout) > 4194304 ||
                    strlen($stderr) > 1048576 ||
                    microtime(TRUE) - $start > 40 ||
                    microtime(TRUE) - $this->started > 150
                ) {
                    proc_terminate($process);
                    throw new RuntimeException(
                        'Pembacaan terlalu lama. Gunakan foto yang lebih jelas atau pisahkan halaman.'
                    );
                }

                $status = proc_get_status($process);

                if (!$status['running']) {
                    $exitCode = $status['exitcode'];
                    break;
                }

                usleep(20000);

            } while (TRUE);

            $stdout .= stream_get_contents($pipes[1]);
            $stderr .= stream_get_contents($pipes[2]);

            if ($exitCode !== 0) {
                throw new RuntimeException(
                'OCR ERROR | ExitCode: ' . $exitCode .
                ' | Command: ' . implode(' ', $args) .
                ' | STDERR: ' . trim($stderr) .
                ' | STDOUT: ' . trim($stdout)
            );
        }

        return $stdout;

    } finally {
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($process);
    }
}

}
