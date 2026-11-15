<?php
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
// Live integration smoke: a short-lived account, no letters, payments, or identity records created.
$db=new mysqli('localhost','root','','sukamulya');$name='dashboard_test_'.bin2hex(random_bytes(8));$pass=bin2hex(random_bytes(24));$display='Warga Contoh';$hash=password_hash($pass,PASSWORD_DEFAULT);
$stmt=$db->prepare('INSERT INTO citizens(username,display_name,password_hash,is_active) VALUES(?,?,?,1)');$stmt->bind_param('sss',$name,$display,$hash);$stmt->execute();$id=$db->insert_id;
$c=curl_init();curl_setopt_array($c,array(CURLOPT_RETURNTRANSFER=>TRUE,CURLOPT_COOKIEFILE=>'',CURLOPT_TIMEOUT=>20,CURLOPT_PROXY=>''));
function check_dashboard($ok,$message){if(!$ok)throw new RuntimeException($message);echo 'PASS '.$message.PHP_EOL;}
try{
 curl_setopt($c,CURLOPT_URL,'http://localhost/sukamulya/warga');curl_exec($c);check_dashboard(in_array(curl_getinfo($c,CURLINFO_HTTP_CODE),array(302,303,307),TRUE),'Guest dashboard redirects to login');
 curl_setopt($c,CURLOPT_URL,'http://localhost/sukamulya/warga/login');$html=curl_exec($c);preg_match('/name="(csrf_[^"]+)"\s+value="([^"]+)"/',$html,$csrf);
 curl_setopt_array($c,array(CURLOPT_POST=>TRUE,CURLOPT_POSTFIELDS=>http_build_query(array('username'=>$name,'password'=>$pass,$csrf[1]=>$csrf[2]))));curl_exec($c);
 curl_setopt($c,CURLOPT_HTTPGET,TRUE);
 $routes=array('warga','warga/profil','warga/keluarga','warga/layanan','warga/riwayat','warga/iuran','keuangan','pengumuman','agenda','galeri','warga/ronda','warga/pengaduan','warga/password');
 foreach($routes as $route){curl_setopt($c,CURLOPT_URL,'http://localhost/sukamulya/'.$route);$html=curl_exec($c);$status=curl_getinfo($c,CURLINFO_HTTP_CODE);check_dashboard($status===200&&!preg_match('/A PHP Error|Fatal error|Severity:|Database Error/i',$html),'Authenticated route '.$route);if($route==='warga')$dashboard=$html;}
 check_dashboard(substr_count($dashboard,'data-mobile-menu=')===12,'All 12 existing dashboard services rendered');
 check_dashboard(strpos($dashboard,'dm-more')!==FALSE&&strpos($dashboard,'dm-account')!==FALSE,'Account and more-menu controls rendered');
 check_dashboard(preg_match('/id="dm-main"/',$dashboard)&&strpos($dashboard,'Warga Contoh')!==FALSE,'Logged-in identity rendered');
 curl_setopt($c,CURLOPT_URL,'http://localhost/sukamulya/warga/chat');curl_exec($c);check_dashboard(in_array(curl_getinfo($c,CURLINFO_HTTP_CODE),array(302,303,307),TRUE),'Chat redirects to existing dashboard widget');
 // Guard an inactive session as well as a missing session.
 $db->query('UPDATE citizens SET is_active=0 WHERE id='.(int)$id);curl_setopt($c,CURLOPT_URL,'http://localhost/sukamulya/warga');curl_exec($c);check_dashboard(in_array(curl_getinfo($c,CURLINFO_HTTP_CODE),array(302,303,307),TRUE),'Inactive account cannot open dashboard');
}catch(Throwable $e){fwrite(STDERR,$e->getMessage().PHP_EOL); $failed=TRUE;}finally{$stmt=$db->prepare('DELETE FROM citizens WHERE id=?');$stmt->bind_param('i',$id);$stmt->execute();curl_close($c);}

if(!empty($failed))exit(1);
