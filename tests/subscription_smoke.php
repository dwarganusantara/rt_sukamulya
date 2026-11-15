<?php
if(PHP_SAPI!=='cli')exit;
if((getenv('DB_NAME')?:'')!=='sukamulya_ui_test')throw new RuntimeException('Disposable database required.');
date_default_timezone_set('Asia/Jakarta');
$db=new mysqli('localhost','root','','sukamulya_ui_test');
foreach(explode(';',file_get_contents(__DIR__.'/../subscription_upgrade.sql')) as $sql)if(trim($sql))$db->query($sql);
foreach(array('subscription_audit_logs','subscription_overrides','tenant_subscriptions','subscription_plan_details','subscription_plan_features','subscription_plans','subscription_catalog') as $t)$db->query('DELETE FROM '.$t);
$tag='sub'.bin2hex(random_bytes(4));$password=bin2hex(random_bytes(20));$hash=password_hash($password,PASSWORD_DEFAULT);
foreach(array('admin','operator','auditor') as $role){$s=$db->prepare('INSERT INTO admins(username,password_hash,role,is_active) VALUES(?,?,?,1)');$name=$tag.'_'.$role;$s->bind_param('sss',$name,$hash,$role);$s->execute();}
$owner_config=realpath(__DIR__.'/../tmp/adminqa/application/config');
if(!$owner_config||strpos(str_replace('\\','/',$owner_config),'tmp/adminqa/application/config')===FALSE)throw new RuntimeException('Isolated application configuration required.');
file_put_contents($owner_config.'/platform_owner.php',"<?php\ndefined('BASEPATH') OR exit('No direct script access allowed');\n\$config['identity']=".var_export(array('enabled'=>TRUE,'username'=>$tag,'password_hash'=>$hash),TRUE).";\n");
function check_sub($ok,$label){if(!$ok)throw new RuntimeException($label);echo 'PASS '.$label.PHP_EOL;}
class SubHttp {
 public $c,$code,$html; public function __construct(){$this->c=curl_init();curl_setopt_array($this->c,array(CURLOPT_RETURNTRANSFER=>TRUE,CURLOPT_COOKIEFILE=>'',CURLOPT_TIMEOUT=>25,CURLOPT_PROXY=>''));}
 function go($path,$fields=NULL,$json=FALSE){$url='http://127.0.0.1:8097/'.$path;curl_setopt($this->c,CURLOPT_HTTPHEADER,$json?array('Accept: application/json','X-Requested-With: XMLHttpRequest'):array());if($fields!==NULL){$token='';foreach(curl_getinfo($this->c,CURLINFO_COOKIELIST) as $cookie){$parts=explode("\t",$cookie);if(($parts[5]??'')==='csrf_sukamulya_cookie')$token=$parts[6];}$fields['csrf_sukamulya']=$token;curl_setopt_array($this->c,array(CURLOPT_POST=>TRUE,CURLOPT_POSTFIELDS=>http_build_query($fields)));}else curl_setopt($this->c,CURLOPT_HTTPGET,TRUE);curl_setopt($this->c,CURLOPT_URL,$url);$this->html=curl_exec($this->c);$this->code=curl_getinfo($this->c,CURLINFO_HTTP_CODE);if(preg_match('/Fatal error|A PHP Error|A Database Error|Severity:/',$this->html)){file_put_contents(__DIR__.'/../tmp/adminqa/sub-error.html',$this->html);throw new RuntimeException('PHP error '.$path);}return $this;}
 function login($name,$password,$platform=FALSE){$path=$platform?'platform/login':'admin/login';$this->go($path);$this->go($path,array('username'=>$name,'password'=>$password));check_sub(in_array($this->code,array(302,303,307),TRUE),'Login '.$name);return $this;}
 function post($data){$this->go('platform/langganan');return $this->go('platform/langganan',$data);}
}
$a=(new SubHttp)->login($tag.'_admin',$password);$p=(new SubHttp)->login($tag,$password,TRUE);
$a->go('platform/langganan');check_sub(in_array($a->code,array(302,303,307),TRUE),'RT admin cannot use platform panel');
$a->go('admin/langganan',array('action'=>'subscription','tenant_id'=>1));check_sub($a->code===405&&(int)$db->query('SELECT COUNT(*) n FROM tenant_subscriptions')->fetch_assoc()['n']===0,'Tenant page cannot activate itself');
$a->go('admin');check_sub($a->code===200,'Existing free dashboard works');
$p->post(array('action'=>'catalog','feature_code'=>'social_aid','commercial_type'=>'PREMIUM','description'=>'Bantuan sosial'));check_sub(in_array($p->code,array(302,303,307),TRUE),'Platform classifies existing feature');
$db->query("UPDATE tenant_features t JOIN app_features f ON f.id=t.feature_id SET t.enabled=1,t.visible_to_staff=1 WHERE f.code='social_aid' AND t.tenant_id=1");
foreach(array('admin/bansos','sosial/index','sosial','admin/bansos/ekspor','sosial/ekspor','sosial/detail/1','sosial/form/1','sosial/bukti/1') as $path){$a->go($path);check_sub($a->code===403,'Direct URL denied '.$path);}
$a->go('admin/bansos',array('title'=>'blocked'));check_sub($a->code===403,'Locked POST denied before action');
$a->go('admin/bansos',NULL,TRUE);check_sub($a->code===403&&(json_decode($a->html,TRUE)['error']??'')==='SUBSCRIPTION_REQUIRED','AJAX returns subscription code');
$a->go('admin');check_sub(strpos($a->html,'data-sub-feature="social_aid"')!==FALSE,'Locked menu remains visible with premium trigger');
$a->go('admin/langganan/status?feature=social_aid',NULL,TRUE);check_sub((json_decode($a->html,TRUE)['status']??'')==='SUBSCRIPTION_REQUIRED','Status endpoint is server authoritative');
$o=(new SubHttp)->login($tag.'_auditor',$password);$o->go('admin/langganan/status?feature=social_aid');check_sub($o->code===403,'Unauthorized role gets no subscription offer');
$p->post(array('action'=>'plan','name'=>'Paket uji','price'=>'125000','duration_days'=>'30','features'=>array('social_aid'),'is_active'=>1));$plan=(int)$db->insert_id;$plan=(int)$db->query('SELECT MAX(id) n FROM subscription_plans')->fetch_assoc()['n'];check_sub($plan>0,'Plan stored with actual price and features');
$license=array('action'=>'subscription','tenant_id'=>1,'plan_id'=>$plan,'status'=>'ACTIVE','starts_at'=>date('Y-m-d\TH:i',time()-3600),'ends_at'=>date('Y-m-d\TH:i',time()+86400),'reason'=>'Konfirmasi uji');
$p->post($license);$a->go('admin/bansos');check_sub($a->code===200,'Active entitlement unlocks existing module');
$a->go('sosial/index');check_sub($a->code===200,'Controller URL keeps same entitlement');
$p->post(array_merge($license,array('status'=>'TRIAL')));$a->go('admin/bansos');check_sub($a->code===200,'Valid trial unlocks premium');
$p->post(array_merge($license,array('starts_at'=>date('Y-m-d\TH:i',time()+3600))));$a->go('admin/bansos');check_sub($a->code===403,'Future licence does not grant early access');
$db->query("UPDATE tenant_subscriptions SET ends_at=DATE_SUB(NOW(),INTERVAL 1 SECOND)");$a->go('admin/bansos');check_sub($a->code===403,'Expiry automatically relocks premium');
$p->post(array_merge($license,array('status'=>'SUSPENDED')));$a->go('admin/bansos');check_sub($a->code===403,'Suspended entitlement denied');
$override=array('action'=>'override','tenant_id'=>1,'feature_code'=>'social_aid','access_mode'=>'GRANT','starts_at'=>date('Y-m-d\TH:i',time()-60),'ends_at'=>date('Y-m-d\TH:i',time()+3600),'reason'=>'Trial CSR uji');
$p->post($override);$a->go('admin/bansos');check_sub($a->code===200,'Valid timed override grants access');
$p->post(array_merge($override,array('access_mode'=>'DENY')));$a->go('admin/bansos');check_sub($a->code===403,'Latest timed deny overrides grant');
$db->query("UPDATE subscription_overrides SET ends_at=DATE_SUB(NOW(),INTERVAL 1 SECOND)");$a->go('admin/bansos');check_sub($a->code===403,'Expired override does not grant access');
$before=(int)$db->query('SELECT COUNT(*) n FROM tenant_subscriptions')->fetch_assoc()['n'];$p->post(array_merge($license,array('tenant_id'=>2)));check_sub($p->code===200&&strpos($p->html,'Tenant tidak sesuai')!==FALSE&&(int)$db->query('SELECT COUNT(*) n FROM tenant_subscriptions')->fetch_assoc()['n']===$before,'Forged tenant scope rejected');
$p->post(array('action'=>'catalog','feature_code'=>'social_aid','commercial_type'=>'DISABLED'));$a->go('admin/bansos',NULL,TRUE);check_sub((json_decode($a->html,TRUE)['error']??'')==='FEATURE_DISABLED','Disabled feature is not a sales popup');
$a->go('admin');check_sub(strpos($a->html,'data-sub-feature="social_aid"')===FALSE,'Disabled menu hidden');
check_sub((int)$db->query('SELECT COUNT(*) n FROM subscription_audit_logs')->fetch_assoc()['n']>=7,'Commercial changes audited');
$p->post(array('action'=>'catalog','feature_code'=>'family_card','commercial_type'=>'PREMIUM'));
$db->query("INSERT INTO notification_outbox(channel,recipient,subject,body,dedupe_key,status,attempts) VALUES('email','test@example.invalid','Uji lisensi','Uji','kk:subscription:smoke','pending',0) ON DUPLICATE KEY UPDATE status='pending',next_attempt_at=NULL");
$a->go('admin/antrean-email');$a->go('admin/antrean-email',array('act'=>'process'));$r=$db->query("SELECT * FROM notification_outbox WHERE dedupe_key='kk:subscription:smoke'")->fetch_assoc();check_sub($r['status']==='pending'&&$r['attempts']==0&&strpos($r['last_error'],'lisensi')!==FALSE,'Background queue rechecks licence and retains blocked work');
$p->post(array('action'=>'catalog','feature_code'=>'announcements','commercial_type'=>'PREMIUM'));
foreach(array('admin/posts','admin/post','site/listing/pengumuman','site/announcement/1') as $path){$a->go($path);check_sub($a->code===403,'Default/alias route cannot bypass '.$path);}
$db->query("UPDATE subscription_catalog SET commercial_type='FREE' WHERE feature_code='announcements'");
$before=(int)$db->query('SELECT COUNT(*) n FROM subscription_plans')->fetch_assoc()['n'];
$raw=curl_init('http://127.0.0.1:8097/platform/langganan');curl_setopt_array($raw,array(CURLOPT_RETURNTRANSFER=>TRUE,CURLOPT_POST=>TRUE,CURLOPT_POSTFIELDS=>'action=plan&name=forged'));
curl_exec($raw);check_sub(curl_getinfo($raw,CURLINFO_HTTP_CODE)===403&&(int)$db->query('SELECT COUNT(*) n FROM subscription_plans')->fetch_assoc()['n']===$before,'CSRF-less platform mutation rejected');curl_close($raw);
// Leave only disposable state for browser QA. No operational tenant is modified.
$db->query('DELETE FROM subscription_overrides');$db->query('DELETE FROM tenant_subscriptions');
foreach(array('social_aid','family_card','resident_mutation') as $code){$db->query("REPLACE INTO subscription_catalog(feature_code,commercial_type) VALUES('$code','PREMIUM')");$db->query("UPDATE tenant_features t JOIN app_features f ON f.id=t.feature_id SET t.enabled=1,t.visible_to_staff=1 WHERE f.code='$code' AND t.tenant_id=1");}
file_put_contents(__DIR__.'/../tmp/adminqa/sub-browser.json',json_encode(array('username'=>$tag.'_admin','password'=>$password,'platform'=>$tag)));
echo "All subscription checks passed on disposable DB.\n";
