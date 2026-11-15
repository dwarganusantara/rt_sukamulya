<?php
define('BASEPATH',__DIR__);
require __DIR__.'/../application/libraries/Kk_fields.php';
require __DIR__.'/../application/libraries/Kk_tsv_parser.php';
function check($condition,$message) {if(!$condition) throw new RuntimeException($message);}
$fields=new Kk_fields();
foreach(array('PEREMPUAN','PEREM PUAN',"PEREM\nPUAN",' PEREMPUAN. ','| PEREMPUAN |','P')as $value)check($fields->gender($value)==='P','Female OCR separators normalized');
foreach(array('LAKI-LAKI','LAKI - LAKI','LAKI LAKI','L')as $value)check($fields->gender($value)==='L','Male OCR separators normalized');
foreach(array('BUKAN PEREMPUAN','PEREMPUAN ISLAM','PRIA','PEREM?UAN','')as $value)check($fields->gender($value)==='','Unknown or mixed gender text is not inferred');
foreach(array(3,6) as $n) {
    $rows=array();
    for($i=0;$i<$n;$i++) $rows[]=array('nama_lengkap'=>'WARGA SINTETIS '.($i+1),'nik'=>'990000000000'.str_pad($i+1,4,'0',STR_PAD_LEFT),'jenis_kelamin'=>'L','hubungan_keluarga'=>$i?'Anak':'Kepala Keluarga');
    $rows[]=array('nama_lengkap'=>'-','nik'=>'-');
    $data=$fields->normalize(array('keluarga'=>array('nomor_kk'=>'9900000000009999','alamat'=>'ALAMAT SINTETIS'),'anggota'=>$rows,'foto'=>'must disappear','raw_ocr_text'=>'must disappear'));
    check($data['jumlah_anggota']===$n,'Dynamic member count');
    check(!$fields->validate($data),'Valid synthetic family');
    check(!isset($data['foto'],$data['raw_ocr_text']),'Whitelist strips document data');
    $data['anggota'][1]['nik']=$data['anggota'][0]['nik'];
    check((bool)$fields->validate($data),'Duplicate NIK blocked');
}
$data=$fields->normalize(array('keluarga'=>array('nomor_kk'=>'99O0000000009999'),'anggota'=>array(array('nama_lengkap'=>'SINTETIS','nik'=>'99O0000000000001','jenis_kelamin'=>'unknown','tanggal_lahir'=>'31-02-2024','foto'=>'no'))));
check($data['keluarga']['nomor_kk']==='' && $data['anggota'][0]['nik']==='','Ambiguous identifiers never repaired');
check($data['anggota'][0]['tanggal_lahir']==='' && $data['anggota'][0]['jenis_kelamin']==='','Invalid dates and gender remain empty');
check(!isset($data['anggota'][0]['foto']),'Member whitelist');
$tsv="level\tpage_num\tblock_num\tpar_num\tline_num\tword_num\tleft\ttop\twidth\theight\tconf\ttext\n";
$word=function($x,$y,$w,$text,$confidence=95)use(&$tsv){$tsv.="5\t1\t1\t1\t1\t1\t$x\t$y\t$w\t10\t$confidence\t$text\n";};
$word(30,10,20,'No.'); $word(100,10,160,'9900000000009999');
$word(40,100,40,'Nama'); $word(220,100,30,'NIK'); $word(380,100,60,'Kelamin');
for($i=0;$i<6;$i++) {$word(40,140+$i*30,90,'SINTETIS');$word(160,140+$i*30,160,'990000000000'.str_pad($i+1,4,'0',STR_PAD_LEFT));$word(400,140+$i*30,10,'L');}
$parsed=(new Kk_tsv_parser())->parse($tsv);
check(count($parsed['anggota'])===6,'Spatial OCR reads six rows');
check($parsed['keluarga']['nomor_kk']==='9900000000009999','Header number distinct from member NIK');
check($parsed['anggota'][0]['nama_lengkap']==='SINTETIS','Name mapped by column');
check($parsed['anggota'][0]['jenis_kelamin']==='L','Gender mapped by column');
check(!(new Kk_tsv_parser())->parse('unreadable')['anggota'],'Unreadable input has no fabricated members');
echo "PASS: field normalization, privacy whitelist, dates, duplicates, dynamic 3/6 members, spatial mapping\n";
