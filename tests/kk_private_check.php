<?php
if(PHP_SAPI!=='cli')exit;
define('BASEPATH',__DIR__);define('FCPATH',dirname(__DIR__).'/');
require FCPATH.'application/config/kk_scan.php';require FCPATH.'application/libraries/Kk_local_engine.php';require FCPATH.'application/libraries/Kk_fields.php';require FCPATH.'application/libraries/Kk_file_types.php';
$raw=(new Kk_local_engine())->scan($argv[1],Kk_file_types::detect($argv[1]),$config['kk_tesseract'],$config['kk_pdftoppm'],$config['kk_magick']);
$data=(new Kk_fields())->normalize($raw);$counts=array();foreach($data['anggota']as $m)foreach($m as $k=>$v)if(is_string($v)&&$v!=='')$counts[$k]=($counts[$k]??0)+1;
echo json_encode(array('gender_values'=>array_column($data['anggota'],'jenis_kelamin'),'raw_gender_lengths'=>array_map(function($m){return strlen($m['jenis_kelamin']??'');},$raw['anggota']))),PHP_EOL;
echo json_encode(array('members'=>$data['jumlah_anggota'],'populated_field_counts'=>$counts,'family_populated'=>array_keys(array_filter($data['keluarga'],function($v){return is_string($v)&&$v!=='';})))),PHP_EOL;
