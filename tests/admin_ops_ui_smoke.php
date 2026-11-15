<?php
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
$dbn=getenv('DB_NAME')?:'sukamulya_ui_test';if($dbn!=='sukamulya_ui_test')throw new RuntimeException('Only isolated sukamulya_ui_test allowed');
$db=new mysqli('localhost','root','',$dbn);$base='http://127.0.0.1:8097/';$tag='uit'.bin2hex(random_bytes(4));$pw=bin2hex(random_bytes(20));$hash=password_hash($pw,PASSWORD_DEFAULT);
foreach(array('admin','operator','auditor') as $role){$name=$tag.'_'.$role;$s=$db->prepare('INSERT INTO admins(username,password_hash,role,is_active) VALUES(?,?,?,1)');$s->bind_param('sss',$name,$hash,$role);$s->execute();}
function verify_ui($ok,$label){if(!$ok)throw new RuntimeException($label);echo 'PASS '.$label.PHP_EOL;}
class OpsHttp{
 public $c,$html,$code,$location;public function __construct(){$this->c=curl_init();curl_setopt_array($this->c,array(CURLOPT_RETURNTRANSFER=>TRUE,CURLOPT_COOKIEFILE=>'',CURLOPT_TIMEOUT=>20,CURLOPT_PROXY=>'',CURLOPT_HEADER=>TRUE));}
 function go($path,$fields=NULL){global $base;if($fields!==NULL){$token='';foreach(curl_getinfo($this->c,CURLINFO_COOKIELIST) as $cookie){$p=explode("\t",$cookie);if(($p[5]??'')==='csrf_sukamulya_cookie')$token=$p[6];}$fields['csrf_sukamulya']=$token;curl_setopt_array($this->c,array(CURLOPT_POST=>TRUE,CURLOPT_POSTFIELDS=>http_build_query($fields)));}else curl_setopt($this->c,CURLOPT_HTTPGET,TRUE);curl_setopt($this->c,CURLOPT_URL,$base.$path);$raw=curl_exec($this->c);$size=curl_getinfo($this->c,CURLINFO_HEADER_SIZE);$head=substr($raw,0,$size);$this->html=substr($raw,$size);$this->code=curl_getinfo($this->c,CURLINFO_HTTP_CODE);$this->location=preg_match('/^Location:\s*(\S+)/mi',$head,$m)?$m[1]:'';return $this;}
 function login($role){global $tag,$pw;$this->go('admin/login');$this->go('admin/login',array('username'=>$tag.'_'.$role,'password'=>$pw));verify_ui(in_array($this->code,array(302,303,307),TRUE),'Login '.$role);return $this;}
 function post($path,$data){$this->go($path);$this->go($path,$data);verify_ui(!preg_match('/Fatal error|A PHP Error|A Database Error|Severity:/',$this->html),'POST no PHP errors '.$path);return $this;}
}
$a=(new OpsHttp())->login('admin');
$pages=array('notulen'=>'admin/notulen/baru','inventaris'=>'admin/inventaris','belanja'=>'admin/belanja','kontak'=>'admin/kontak','arsip'=>'admin/arsip','akun'=>'admin/akun');
foreach($pages as $name=>$path){$a->go($path);verify_ui($a->code===200&&!preg_match('/Fatal error|A PHP Error|A Database Error|Severity:/',$a->html),'GET '.$path);$out=str_replace(array($tag.'_admin',ucwords(str_replace('_',' ',$tag.'_admin'))),'Admin',$a->html);$out=preg_replace('#<head>#','<head><base href="'.$base.'">',$out,1);file_put_contents(__DIR__.'/../tmp/family-preview/admin-'.$name.'.html',$out);}
if(getenv('OPS_CAPTURE_ONLY'))exit(0);
$a->post('admin/notulen/baru',array('title'=>'Uji UI '.$tag,'note_date'=>'2026-11-08','summary'=>'Ringkasan hasil rapat uji','participant_count'=>2));$r=$db->query("SELECT * FROM activity_notes WHERE title='Uji UI $tag'")->fetch_assoc();verify_ui($r&&$r['status']==='draft'&&$r['summary']==='Ringkasan hasil rapat uji','Notulen saves draft from existing form');
$a->post('admin/notulen/'.$r['id'].'/ubah',array('title'=>'Uji UI '.$tag,'note_date'=>'2026-11-08','summary'=>'Revisi tersimpan','participant_count'=>3));verify_ui((int)$db->query('SELECT COUNT(*) n FROM activity_note_versions WHERE note_id='.(int)$r['id'])->fetch_assoc()['n']>=1,'Editing notulen preserves audited previous version');
$a->post('admin/inventaris/baru',array('code'=>strtoupper($tag),'name'=>'Barang Uji '.$tag,'kind'=>'aset','quantity'=>3,'unit'=>'unit','condition_status'=>'baik'));$r=$db->query("SELECT * FROM assets WHERE code='".strtoupper($tag)."'")->fetch_assoc();verify_ui($r&&(int)$r['qty_available']===3,'Inventory save keeps stock function');
$a->post('admin/kontak',array('name'=>'Kontak Uji '.$tag,'phone'=>'0215551234','category'=>'umum','is_public'=>1,'is_active'=>1));$r=$db->query("SELECT * FROM important_contacts WHERE name='Kontak Uji $tag'")->fetch_assoc();verify_ui($r&&(int)$r['is_public']===1,'Contact saves public flag and phone');
$a->post('admin/kontak/'.$r['id'],array('name'=>'Kontak Uji '.$tag,'phone'=>'0215555678','category'=>'umum','is_active'=>1));$new=$db->query('SELECT * FROM important_contacts WHERE id='.(int)$r['id'])->fetch_assoc();verify_ui($new['phone']==='0215555678'&&(int)$new['is_public']===0,'Contact edit and private flag persist');
$a->post('admin/belanja/baru',array('title'=>'Belanja Uji '.$tag,'item_name'=>array('Kertas'),'item_qty'=>array(2),'item_price'=>array(10000),'item_unit'=>array('rim')));$r=$db->query("SELECT * FROM purchase_requests WHERE title='Belanja Uji $tag'")->fetch_assoc();verify_ui($r&&(int)$r['estimated_total']===20000&&$r['status']==='draft','Purchase item totals and draft save work');
$a->post('admin/akun',array('username'=>$tag.'_created','role'=>'operator','password'=>$pw,'full_name'=>'Akun Uji','is_active'=>1));$r=$db->query("SELECT * FROM admins WHERE username='{$tag}_created'")->fetch_assoc();verify_ui($r&&password_verify($pw,$r['password_hash']),'Account creation stores hashed password');
$o=(new OpsHttp())->login('operator');$o->go('admin/akun');verify_ui($o->code===403,'Operator denied account management');
$aud=(new OpsHttp())->login('auditor');$aud->go('admin/inventaris/baru');verify_ui($aud->code===403,'Auditor denied inventory write route');
$guest=new OpsHttp();$guest->go('admin/inventaris');verify_ui(in_array($guest->code,array(302,303,307),TRUE),'Guest denied inventory page');
$a->post('admin/arsip',array('title'=>'Validasi berkas '.$tag,'category'=>'umum','confidentiality'=>'internal'));verify_ui($a->code===200&&strpos($a->html,'Pilih berkas dokumen')!==FALSE,'Archive validates required upload on server');
// Positive file upload and download on the isolated database, then remove only this test's file.
$upload=tempnam(sys_get_temp_dir(),'ops_ui_');file_put_contents($upload,"Dokumen uji UI arsip $tag\n");$stored_path=NULL;
try{
 $a->go('admin/arsip');$token='';foreach(curl_getinfo($a->c,CURLINFO_COOKIELIST) as $cookie){$parts=explode("\t",$cookie);if(($parts[5]??'')==='csrf_sukamulya_cookie')$token=$parts[6];}
 curl_setopt_array($a->c,array(CURLOPT_URL=>$base.'admin/arsip',CURLOPT_POST=>TRUE,CURLOPT_POSTFIELDS=>array('csrf_sukamulya'=>$token,'title'=>'Arsip Uji '.$tag,'category'=>'umum','confidentiality'=>'internal','document'=>new CURLFile($upload,'text/plain','uji-arsip.txt'))));$result=curl_exec($a->c);
 $doc=$db->query("SELECT d.id,v.stored_name FROM documents d JOIN document_versions v ON v.document_id=d.id WHERE d.title='Arsip Uji $tag'")->fetch_assoc();verify_ui((bool)$doc,'Valid archive file uploaded through existing form');
 $stored_path=dirname(__DIR__).'/application/storage/documents/'.$doc['stored_name'];
 $a->go('admin/arsip/'.$doc['id'].'/unduh');verify_ui($a->code===200&&strpos($a->html,'Dokumen uji UI arsip')!==FALSE,'Authorized archive download works');
 $guest->go('admin/arsip/'.$doc['id'].'/unduh');verify_ui(in_array($guest->code,array(302,303,307),TRUE),'Guest denied private archive download');
}finally{if(is_file($upload))unlink($upload);if($stored_path&&is_file($stored_path))unlink($stored_path);}
// Recapture UI with the final templates; all mutations above remain confined to the test DB.
foreach($pages as $name=>$path){$a->go($path);$out=preg_replace('/'.preg_quote(ucwords(str_replace('_',' ',$tag.'_admin')),'/').'/', 'Admin', $a->html);$out=preg_replace('#<head>#','<head><base href="'.$base.'">',$out,1);file_put_contents(__DIR__.'/../tmp/family-preview/admin-'.$name.'.html',$out);}
file_put_contents(__DIR__.'/../tmp/family-preview/admin-ui-test-tag.txt',$tag);
echo 'Only the isolated test database was mutated.'.PHP_EOL;
