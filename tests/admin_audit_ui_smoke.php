<?php
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
$dbn=getenv('DB_NAME')?:'sukamulya_ui_test';if($dbn!=='sukamulya_ui_test')throw new RuntimeException('Only isolated sukamulya_ui_test allowed');
$db=new mysqli('localhost','root','',$dbn);$base='http://127.0.0.1:8097/';$tag='uit'.bin2hex(random_bytes(4));$pw=bin2hex(random_bytes(20));$hash=password_hash($pw,PASSWORD_DEFAULT);
foreach(array('admin','operator','auditor') as $role){$name=$tag.'_'.$role;$s=$db->prepare('INSERT INTO admins(username,password_hash,role,is_active) VALUES(?,?,?,1)');$s->bind_param('sss',$name,$hash,$role);$s->execute();}
function verify_ui($ok,$label){if(!$ok)throw new RuntimeException($label);echo 'PASS '.$label.PHP_EOL;}
function xlsx_xml($bin){$tmp=tempnam(sys_get_temp_dir(),'xlsx-test-');file_put_contents($tmp,$bin);$z=new ZipArchive();if($z->open($tmp)!==TRUE){@unlink($tmp);return '';}$xml=(string)$z->getFromName('xl/worksheets/sheet1.xml');$z->close();@unlink($tmp);return $xml;}
class OpsHttp{
 public $c,$html,$code,$location;public function __construct(){$this->c=curl_init();curl_setopt_array($this->c,array(CURLOPT_RETURNTRANSFER=>TRUE,CURLOPT_COOKIEFILE=>'',CURLOPT_TIMEOUT=>20,CURLOPT_PROXY=>'',CURLOPT_HEADER=>TRUE));}
 function go($path,$fields=NULL){global $base;if($fields!==NULL){$token='';foreach(curl_getinfo($this->c,CURLINFO_COOKIELIST) as $cookie){$p=explode("\t",$cookie);if(($p[5]??'')==='csrf_sukamulya_cookie')$token=$p[6];}$fields['csrf_sukamulya']=$token;curl_setopt_array($this->c,array(CURLOPT_POST=>TRUE,CURLOPT_POSTFIELDS=>http_build_query($fields)));}else curl_setopt($this->c,CURLOPT_HTTPGET,TRUE);curl_setopt($this->c,CURLOPT_URL,$base.$path);$raw=curl_exec($this->c);$size=curl_getinfo($this->c,CURLINFO_HEADER_SIZE);$head=substr($raw,0,$size);$this->html=substr($raw,$size);$this->code=curl_getinfo($this->c,CURLINFO_HTTP_CODE);$this->location=preg_match('/^Location:\s*(\S+)/mi',$head,$m)?$m[1]:'';return $this;}
 function login($role){global $tag,$pw;$this->go('admin/login');$this->go('admin/login',array('username'=>$tag.'_'.$role,'password'=>$pw));verify_ui(in_array($this->code,array(302,303,307),TRUE),'Login '.$role);return $this;}
 function post($path,$data){$this->go($path);$this->go($path,$data);verify_ui(!preg_match('/Fatal error|A PHP Error|A Database Error|Severity:/',$this->html),'POST no PHP errors '.$path);return $this;}
}
$a=(new OpsHttp())->login('admin');
$aid=(int)$db->query("SELECT id FROM admins WHERE username='{$tag}_admin'")->fetch_assoc()['id'];
$opid=(int)$db->query("SELECT id FROM admins WHERE username='{$tag}_operator'")->fetch_assoc()['id'];
$subject='audit-ui-'.$tag;$s=$db->prepare('INSERT INTO activity_logs(admin_id,action,subject,details,ip_address,created_at) VALUES(?,?,?,?,?,?)');
$action='update';$details='Data tersimpan';$ip='127.0.0.1';$date='2026-11-07 12:00:00';$s->bind_param('isssss',$aid,$action,$subject,$details,$ip,$date);$s->execute();
$date='2026-10-01 12:00:00';$action='view';$s->bind_param('isssss',$opid,$action,$subject,$details,$ip,$date);$s->execute();
$a->go('admin/audit');verify_ui($a->code===200&&!preg_match('/A PHP Error|A Database Error|Fatal error/',$a->html),'Audit page loads without errors');
$url='admin/audit?'.http_build_query(array('q'=>$subject,'admin'=>$aid,'dari'=>'2026-11-01','sampai'=>'2026-11-08'));
$a->go($url);verify_ui(substr_count($a->html,'data-label="Waktu"')===1&&strpos($a->html,'Data tersimpan')!==FALSE,'Keyword + user + date filters return matching log only');
verify_ui(strpos($a->html,'name="q" value="'.$subject.'"')!==FALSE&&strpos($a->html,'name="dari" value="2026-11-01"')!==FALSE,'Filter values persist in form');
verify_ui(preg_match('/href="[^\"]+export(?:=|%3D)xlsx/',$a->html)===1,'Excel export link preserves active filter query');
$a->go($url.'&export=xlsx');$xml=xlsx_xml($a->html);verify_ui($a->code===200&&strpos($xml,$subject)!==FALSE&&strpos($xml,'2026-11-07')!==FALSE&&strpos($xml,'2026-10-01')===FALSE,'XLSX exports only the filtered record');
$a->go('admin/audit?q=nonexistent-'.$tag);verify_ui(strpos($a->html,'Tidak ada aktivitas')!==FALSE,'Empty filtered result displays correct state');
$o=(new OpsHttp())->login('operator');$o->go('admin/audit');verify_ui($o->code===403,'Operator denied audit');$o->go('admin/audit?export=xlsx');verify_ui($o->code===403,'Operator denied Excel export');
$g=new OpsHttp();$g->go('admin/audit');verify_ui(in_array($g->code,array(302,303,307),TRUE),'Guest redirected to login');
$a->go('admin/audit');$html=str_replace(array($tag.'_admin',ucwords(str_replace('_',' ',$tag.'_admin'))),'Admin',$a->html);file_put_contents(__DIR__.'/../tmp/family-preview/admin-audit.html',$html);
echo 'Only isolated test database changed.'.PHP_EOL;
