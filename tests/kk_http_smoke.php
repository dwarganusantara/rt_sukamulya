<?php
if(PHP_SAPI!=='cli') {http_response_code(404);exit;}
$db=new mysqli('localhost','root','','sukamulya');
$name='kk_smoke_'.bin2hex(random_bytes(8)); $password=bin2hex(random_bytes(24));
$stmt=$db->prepare('INSERT INTO citizens (username,display_name,password_hash,is_active) VALUES (?,?,?,1)');
$hash=password_hash($password,PASSWORD_DEFAULT); $stmt->bind_param('sss',$name,$name,$hash); $stmt->execute(); $id=$db->insert_id;
$curl=curl_init();
curl_setopt_array($curl,array(CURLOPT_RETURNTRANSFER=>TRUE,CURLOPT_COOKIEFILE=>'',CURLOPT_TIMEOUT=>15,CURLOPT_PROXY=>''));
$results=array();
try {
 curl_setopt($curl,CURLOPT_URL,'http://localhost/sukamulya/warga/login'); $login=curl_exec($curl);
 if(!preg_match('/name="(csrf_[^"]+)"\s+value="([^"]+)"/',$login,$csrf)) throw new RuntimeException('CSRF token not found.');
 curl_setopt_array($curl,array(CURLOPT_POST=>TRUE,CURLOPT_POSTFIELDS=>http_build_query(array('username'=>$name,'password'=>$password,$csrf[1]=>$csrf[2])))); curl_exec($curl);
 curl_setopt($curl,CURLOPT_HTTPGET,TRUE);
 foreach(array('warga/keluarga'=>'Data Kartu Keluarga Saya','warga/keluarga/scan'=>'Scan Kartu Keluarga (KK)') as $path=>$expected) {
  curl_setopt($curl,CURLOPT_URL,'http://localhost/sukamulya/'.$path); $body=curl_exec($curl); $status=curl_getinfo($curl,CURLINFO_HTTP_CODE);
  if($status!==200 || strpos($body,$expected)===FALSE || preg_match('/A PHP Error|Fatal error|Severity:|Database Error/i',$body)) throw new RuntimeException('Failed '.$path.' HTTP '.$status);
  $results[]='PASS '.$path.' HTTP 200, expected content, no PHP errors';
 }
 if(extension_loaded('gd')) {
  require __DIR__.'/kk_ocr_image_test.php';
  $image=tempnam(sys_get_temp_dir(),'kk_http_');
  try {
   $private=($argv[1] ?? '')==='--input';
   $expected=$private?(int)($argv[3] ?? 0):6;
   if($private) {if(!is_file($argv[2]))throw new RuntimeException('Input missing');copy($argv[2],$image);}else kk_synthetic_image($image,6);
   $format=$private?($argv[4] ?? 'png'):($argv[1] ?? 'png');
   $upload=$image;
   if($format!=='png') {
    $upload=$image.'.'.$format;
    $p=proc_open(array('C:/Program Files/ImageMagick-7.1.2-Q16-HDRI/magick.exe',$image,$upload),array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('file','NUL','w')),$pipes,NULL,NULL,array('bypass_shell'=>TRUE));
    fclose($pipes[0]);stream_get_contents($pipes[1]);fclose($pipes[1]);if(proc_close($p)!==0)throw new RuntimeException('Format fixture failed');
   }
   require_once __DIR__.'/../application/libraries/Kk_file_types.php';
   $mime=Kk_file_types::detect($upload);
   if(!preg_match('/name="(csrf_[^"]+)"\s+value="([^"]+)"/',$body,$csrf)) throw new RuntimeException('Scan CSRF missing');
   curl_setopt_array($curl,array(CURLOPT_POST=>TRUE,CURLOPT_TIMEOUT=>180,CURLOPT_POSTFIELDS=>array('action'=>'scan',$csrf[1]=>$csrf[2],'document'=>new CURLFile($upload,$mime,'synthetic.'.$format))));
   $result=curl_exec($curl);$status=curl_getinfo($curl,CURLINFO_HTTP_CODE);
   if($status!==200 || strpos($result,'Mesin OCR lokal belum')!==FALSE || (!$private && strpos($result,'value="9900000000000006"')===FALSE) || strpos($result,'id="kk-count">'.$expected.'</span>')===FALSE) throw new RuntimeException('HTTP OCR upload did not populate expected members. HTTP '.$status);
   if(in_array('--require-kk',$argv,TRUE)) {
    if(!preg_match('/name="keluarga\[nomor_kk\]" value="[0-9]{16}"/',$result))throw new RuntimeException('KK number was not populated');
    $results[]='PASS KK number populated with 16 observed digits (value not logged)';
   }
   if(in_array('--second-female',$argv,TRUE)) {
    if(!preg_match('/<select name="anggota\[1\]\[jenis_kelamin\]"[^>]*>(.*?)<\/select>/s',$result,$genderSelect) || !preg_match('/<option value="P" selected>Perempuan<\/option>/',$genderSelect[1]))throw new RuntimeException('Second member did not display Perempuan');
    $results[]='PASS second member displays selected Perempuan in form';
   }
   $results[]='PASS authenticated '.strtoupper($format).' upload: '.$expected.' member fields populated by actual OCR (no document values logged)';
  } finally {if(isset($upload) && $upload!==$image && is_file($upload))unlink($upload);if(is_file($image))unlink($image);curl_setopt($curl,CURLOPT_HTTPGET,TRUE);}
 }
} finally {
 curl_setopt($curl,CURLOPT_URL,'http://localhost/sukamulya/warga/logout');curl_exec($curl);curl_close($curl);
 if($db->query("SHOW TABLES LIKE 'chat_presence'")->num_rows) $db->query('DELETE FROM chat_presence WHERE user_type="citizen" AND user_id='.(int)$id);
 $db->query('DELETE FROM citizens WHERE id='.(int)$id);
}
echo implode(PHP_EOL,$results).PHP_EOL;
