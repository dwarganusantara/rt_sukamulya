<?php
if(PHP_SAPI!=='cli-server') {http_response_code(404);exit;}
header('Content-Type: application/json');header('Cache-Control: no-store');ini_set('display_errors','0');
$token=getenv('KK_OCR_TOKEN');
if(!$token || !hash_equals('Bearer '.$token,$_SERVER['HTTP_AUTHORIZATION'] ?? '')) {http_response_code(403);exit('{}');}
if($_SERVER['REQUEST_METHOD']!=='POST') {http_response_code(405);exit('{}');}
$f=$_FILES['document'] ?? NULL;
try {
 if(!$f || $f['error']!==UPLOAD_ERR_OK || $f['size']>10485760 || !is_uploaded_file($f['tmp_name'])) throw new RuntimeException();
 define('BASEPATH',__DIR__);define('FCPATH',dirname(__DIR__,2).'/');
 require FCPATH.'application/config/kk_scan.php';
 require FCPATH.'application/libraries/Kk_file_types.php';
 require FCPATH.'application/libraries/Kk_local_engine.php';
 require FCPATH.'application/libraries/Kk_fields.php';
 set_time_limit(180);
 $raw=(new Kk_local_engine())->scan($f['tmp_name'],Kk_file_types::detect($f['tmp_name']),$config['kk_tesseract'],$config['kk_pdftoppm'],$config['kk_magick']);
 echo json_encode((new Kk_fields())->normalize($raw));
} catch(Throwable $e) {http_response_code(422);echo '{"error":"unreadable"}';}
finally {if($f && is_uploaded_file($f['tmp_name'])) @unlink($f['tmp_name']);}
