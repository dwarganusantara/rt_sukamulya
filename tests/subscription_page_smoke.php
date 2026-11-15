<?php
if(PHP_SAPI!=='cli'||getenv('DB_NAME')!=='sukamulya_ui_test')exit(1);
date_default_timezone_set('Asia/Jakarta');
$db=new mysqli('localhost','root','','sukamulya_ui_test');
$credentials=json_decode(file_get_contents(__DIR__.'/../tmp/adminqa/sub-browser.json'),TRUE);
$curl=curl_init();curl_setopt_array($curl,array(CURLOPT_RETURNTRANSFER=>TRUE,CURLOPT_COOKIEFILE=>'',CURLOPT_PROXY=>'',CURLOPT_TIMEOUT=>20));
function sub_page_get($path,$fields=NULL){global $curl;if($fields!==NULL){foreach(curl_getinfo($curl,CURLINFO_COOKIELIST) as $cookie){$parts=explode("\t",$cookie);if(($parts[5]??'')==='csrf_sukamulya_cookie')$fields['csrf_sukamulya']=$parts[6];}curl_setopt_array($curl,array(CURLOPT_POST=>TRUE,CURLOPT_POSTFIELDS=>http_build_query($fields)));}else curl_setopt($curl,CURLOPT_HTTPGET,TRUE);curl_setopt($curl,CURLOPT_URL,'http://127.0.0.1:8097/'.$path);$html=curl_exec($curl);if(preg_match('/Fatal error|A PHP Error|A Database Error|Severity:/',$html))throw new RuntimeException('PHP error '.$path);return $html;}
function sub_page_check($ok,$label){if(!$ok)throw new RuntimeException($label);echo 'PASS '.$label.PHP_EOL;}
sub_page_get('admin/login');sub_page_get('admin/login',array('username'=>$credentials['username'],'password'=>$credentials['password']));
foreach(array('paket','fitur','riwayat') as $tab){$html=sub_page_get('admin/langganan?tab='.$tab);sub_page_check(curl_getinfo($curl,CURLINFO_HTTP_CODE)===200,'Tab '.$tab.' renders without PHP errors');}
$plan=$db->query('SELECT * FROM subscription_plans WHERE is_active=1 ORDER BY price,id LIMIT 1')->fetch_assoc();
if(!$plan)throw new RuntimeException('Run disposable subscription page fixtures first.');
$actor=0;
$start=date('Y-m-d H:i:s',time()-60);$end=date('Y-m-d H:i:s',time()+5*86400);$reason='UI history '.bin2hex(random_bytes(4));$id=(int)$plan['id'];
$s=$db->prepare("INSERT INTO tenant_subscriptions(tenant_id,plan_id,status,starts_at,ends_at,reason,created_by)VALUES(1,?,'ACTIVE',?,?,?,?)");$s->bind_param('isssi',$id,$start,$end,$reason,$actor);$s->execute();$sid=$db->insert_id;
try{
 $html=sub_page_get('admin/langganan');preg_match('/<section class="sp-summary".*?<\/section>/s',$html,$summary);
 sub_page_check(strpos($summary[0],htmlspecialchars($plan['name'],ENT_QUOTES,'UTF-8'))!==FALSE&&strpos($summary[0],'Selamanya')===FALSE,'Active package summary uses persisted name and expiry');
 $html=sub_page_get('admin/langganan?tab=riwayat');sub_page_check(strpos($html,$reason)!==FALSE&&strpos($html,'Belum ada riwayat langganan')===FALSE,'History displays actual tenant licence and reason');
 $db->query("UPDATE tenant_subscriptions SET ends_at=DATE_SUB(NOW(),INTERVAL 1 DAY) WHERE id=$sid");$html=sub_page_get('admin/langganan');
 sub_page_check(strpos($html,'Langganan terakhir:')!==FALSE&&strpos($html,'Kedaluwarsa')!==FALSE,'Expired licence shows safe Basic fallback with explanation');
}finally{$db->query('DELETE FROM tenant_subscriptions WHERE id='.(int)$sid);}
$active=array_column($db->query('SELECT id FROM subscription_plans WHERE is_active=1')->fetch_all(MYSQLI_ASSOC),'id');
try{$db->query('UPDATE subscription_plans SET is_active=0');$html=sub_page_get('admin/langganan');sub_page_check(strpos($html,'Informasi paket dan harga belum tersedia')!==FALSE&&strpos($html,'Pilih Paket Pro')===FALSE,'No configured paid offers shows honest empty state');}finally{foreach($active as $id)$db->query('UPDATE subscription_plans SET is_active=1 WHERE id='.(int)$id);}
echo "Only disposable DB changed; no messages sent.\n";
