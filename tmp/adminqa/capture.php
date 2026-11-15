<?php
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
$dbn=getenv('DB_NAME')?:'sukamulya_ui_test';if($dbn!=='sukamulya_ui_test')throw new RuntimeException('Only isolated sukamulya_ui_test allowed');
$db=new mysqli('localhost','root','',$dbn);$base='http://127.0.0.1:8097/';$tag='uit'.bin2hex(random_bytes(4));$pw=bin2hex(random_bytes(20));$hash=password_hash($pw,PASSWORD_DEFAULT);
foreach(array('admin') as $role){$name=$tag.'_'.$role;$s=$db->prepare('INSERT INTO admins(username,password_hash,role,is_active) VALUES(?,?,?,1)');$s->bind_param('sss',$name,$hash,$role);$s->execute();}
function verify_ui($ok,$label){if(!$ok)throw new RuntimeException($label);echo 'PASS '.$label.PHP_EOL;}
class OpsHttp{
 public $c,$html,$code,$location;public function __construct(){$this->c=curl_init();curl_setopt_array($this->c,array(CURLOPT_RETURNTRANSFER=>TRUE,CURLOPT_COOKIEFILE=>'',CURLOPT_TIMEOUT=>20,CURLOPT_PROXY=>'',CURLOPT_HEADER=>TRUE));}
 function go($path,$fields=NULL){global $base;if($fields!==NULL){$token='';foreach(curl_getinfo($this->c,CURLINFO_COOKIELIST) as $cookie){$p=explode("\t",$cookie);if(($p[5]??'')==='csrf_sukamulya_cookie')$token=$p[6];}$fields['csrf_sukamulya']=$token;curl_setopt_array($this->c,array(CURLOPT_POST=>TRUE,CURLOPT_POSTFIELDS=>http_build_query($fields)));}else curl_setopt($this->c,CURLOPT_HTTPGET,TRUE);curl_setopt($this->c,CURLOPT_URL,$base.$path);$raw=curl_exec($this->c);$size=curl_getinfo($this->c,CURLINFO_HEADER_SIZE);$head=substr($raw,0,$size);$this->html=substr($raw,$size);$this->code=curl_getinfo($this->c,CURLINFO_HTTP_CODE);$this->location=preg_match('/^Location:\s*(\S+)/mi',$head,$m)?$m[1]:'';return $this;}
 function login($role){global $tag,$pw;$this->go('admin/login');$this->go('admin/login',array('username'=>$tag.'_'.$role,'password'=>$pw));verify_ui(in_array($this->code,array(302,303,307),TRUE),'Login '.$role);return $this;}
 function post($path,$data){$this->go($path);$this->go($path,$data);verify_ui(!preg_match('/Fatal error|A PHP Error|A Database Error|Severity:/',$this->html),'POST no PHP errors '.$path);return $this;}
}
$a=(new OpsHttp())->login('admin');
$pages=array('akun'=>'admin/akun','fields'=>'admin/field-tambahan','outbox'=>'admin/antrean-email','handover'=>'admin/serah-terima','audit'=>'admin/audit','backup'=>'admin/backup','settings-history'=>'admin/pengaturan/riwayat','reconciliation'=>'admin/rekonsiliasi');
foreach($pages as $name=>$path){$a->go($path);verify_ui($a->code===200&&!preg_match('/A PHP Error|A Database Error|Fatal error|Severity:/',$a->html),'GET '.$path);$html=str_replace(array($tag.'_admin',ucwords(str_replace('_',' ',$tag.'_admin'))),'Admin',$a->html);file_put_contents(__DIR__.'/../family-preview/admin-'.$name.'.html',$html);}
