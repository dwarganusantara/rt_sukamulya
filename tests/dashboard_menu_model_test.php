<?php
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
define('BASEPATH',__DIR__);define('QA_APP',dirname(__DIR__).'/application/');
class QaConfig{public $data=array();function load($name,$group){$config=array();require QA_APP.'config/'.$name.'.php';$this->data[$name]=$config;}function item($key,$group){return $this->data[$group][$key]??NULL;}}
class QaLoad{function library($name){}}
class QaResult{private $r;function __construct($r){$this->r=$r;}function row($key=NULL){$r=$this->row_array();return $key===NULL?(object)$r:($r[$key]??NULL);}function row_array(){return $this->r instanceof mysqli_result?($this->r->fetch_assoc()?:NULL):NULL;}}
class QaDb{public $conn;private $where;function __construct($conn){$this->conn=$conn;}function quote($x){return "'".$this->conn->real_escape_string((string)$x)."'";}function condition($where){$p=array();foreach($where as $k=>$v)$p[]='`'.$k.'`='.$this->quote($v);return implode(' AND ',$p);}function query($sql,$params=array()){foreach($params as $value){$pos=strpos($sql,'?');$sql=substr_replace($sql,$this->quote($value),$pos,1);}return new QaResult($this->conn->query($sql));}function get_where($table,$where){return $this->query('SELECT * FROM `'.$table.'` WHERE '.$this->condition($where));}function where($k,$v){$this->where=array($k=>$v);return $this;}function update($table,$values){return $this->conn->query('UPDATE `'.$table.'` SET '.str_replace(' AND ',',',$this->condition($values)).' WHERE '.$this->condition($this->where));}function replace($table,$values){$this->conn->query('DELETE FROM '.$table.' WHERE '.$this->condition(array('setting_key'=>$values['setting_key'])));return $this->insert($table,$values);}function insert($table,$values){$cols=array();$vals=array();foreach($values as $k=>$v){$cols[]='`'.$k.'`';$vals[]=$this->quote($v);}return $this->conn->query('INSERT INTO `'.$table.'` ('.implode(',',$cols).') VALUES ('.implode(',',$vals).')');}}
class QaGate{public $tenantId=1,$denied=array(),$catalog;function __construct(){$config=array();require QA_APP.'config/features.php';$this->catalog=$config['feature_catalog'];}function tenant(){return $this->tenantId;}function link_ok($route,$audience){foreach($this->denied as $prefix)if($route===$prefix||strpos($route,$prefix.'/')===0)return FALSE;return TRUE;}function match($route){$best=NULL;$length=-1;foreach($this->catalog as $code=>$f)foreach($f['warga'] as $prefix)if(($route===$prefix||strpos($route,$prefix.'/')===0)&&strlen($prefix)>$length){$best=array($code,'warga');$length=strlen($prefix);}return $best;}}
class CI_Model{public $config,$load,$db,$feature_gate;function __construct(){global $qaConn;$this->config=new QaConfig;$this->load=new QaLoad;$this->db=new QaDb($qaConn);$this->feature_gate=new QaGate;}}
require QA_APP.'models/Dashboard_menu_model.php';
function ok($v,$name){if(!$v)throw new RuntimeException('FAIL '.$name);echo 'PASS '.$name,PHP_EOL;}
function rejects($fn,$name){try{$fn();}catch(RuntimeException $e){ok(TRUE,$name);return;}throw new RuntimeException('FAIL accepted '.$name);}
function form_values($items){foreach($items as &$item){$item['sort_order']=(string)$item['sort_order'];foreach(array('show_on_dashboard','is_default_favorite','show_in_header') as $flag){if($item[$flag])$item[$flag]='1';else unset($item[$flag]);}}return $items;}
$qaConn=new mysqli('localhost','root','','sukamulya');$qaConn->set_charset('utf8mb4');$model=new Dashboard_menu_model;
$defaults=$model->defaults();ok(count($defaults)===18,'registry contains 18 real destinations');ok($defaults['home']['show_on_dashboard']===0,'home tile hidden by default');
ok(array_slice(array_keys($defaults),0,5)===array('letters','ocr','family','history','dues'),'priority starts letters OCR family history dues');
$sidebar=file_get_contents(QA_APP.'views/citizen/sidebar.php');preg_match_all("/array\('([^']+)','[^']+','[^']+',/",$sidebar,$sidebarRoutes);$registryRoutes=array_column($model->options('registry'),0);foreach($sidebarRoutes[1] as $route)ok(in_array($route,$registryRoutes,TRUE),'sidebar destination covered: '.$route);
ok($model->options('registry')['ocr'][0]==='warga/keluarga/scan','OCR uses existing upload route');
$valid=form_values($defaults);ok($model->validate($valid)===$defaults,'defaults round trip through validation');
foreach(array('label'=>'<script>alert(1)</script>','icon'=>'raw-svg','color'=>'url(javascript:test)','category'=>'unknown','sort_order'=>'-1') as $field=>$bad){$input=$valid;$input['profile'][$field]=$bad;rejects(function()use($model,$input){$model->validate($input);},'reject invalid '.$field);}
$input=$valid;$input['profile']['label']=str_repeat('a',61);rejects(function()use($model,$input){$model->validate($input);},'label maximum 60');$input=$valid;$input['evil']=array();rejects(function()use($model,$input){$model->validate($input);},'unknown registry key denied');
$model->feature_gate->denied=array('warga/keluarga','warga/layanan','warga/riwayat');$visible=$model->visible();ok(!isset($visible['family'])&&!isset($visible['letters'])&&!isset($visible['history']),'inaccessible services filtered server-side');$model->feature_gate->denied=array();
$original=$model->stored();$key=$model->storage_key();$before=$qaConn->query("SELECT COUNT(*) n FROM tenant_feature_audit")->fetch_assoc()['n'];
$qaConn->begin_transaction();
try{
 $actor=(int)$qaConn->query("SELECT id FROM admins WHERE role='admin' ORDER BY id LIMIT 1")->fetch_assoc()['id'];
 $values=$defaults;$values['profile']['label']='Profil Uji';$values['profile']['sort_order']=999;$values['family']['show_on_dashboard']=0;
 $model->save($values,$model->version(),$actor);$all=$model->all();$visible=$model->visible();ok($all['profile']['label']==='Profil Uji','label saved and reloaded');ok(!isset($visible['family']),'show_on_dashboard is independent of access');ok($all['family']['allowed'],'hidden tile retains service access');ok(array_key_last($visible)==='profile','saved sort order applied');
 rejects(function()use($model,$values,$actor){$model->save($values,str_repeat('0',40),$actor);},'stale admin version rejected');
 $model->feature_gate->tenantId=2;ok($model->all()['profile']['label']!=='Profil Uji','tenant configuration isolation');$model->feature_gate->tenantId=1;
 ok(count($model->favorites(2147483001))<=4,'default favorites capped at four');
 rejects(function()use($model){$model->validate_favorites(array('letters','ocr','history','dues','chat'));},'five favorites rejected server-side');
 rejects(function()use($model){$model->validate_favorites(array('letters','letters'));},'duplicate favorites rejected');
 rejects(function()use($model){$model->validate_favorites(array('unknown'));},'unknown favorite rejected');
 $model->save_favorites(2147483001,array('ocr','letters','dues'));ok(array_keys($model->favorites(2147483001))===array('ocr','letters','dues'),'favorite selection and order persisted');
 ok(array_keys($model->favorites(2147483002))!==array('ocr','letters','dues'),'favorites isolated by citizen');
 $model->feature_gate->tenantId=2;ok(array_keys($model->favorites(2147483001))!==array('ocr','letters','dues'),'favorites isolated by tenant');$model->feature_gate->tenantId=1;
 $model->feature_gate->denied=array('warga/keluarga');ok(!isset($model->favorites(2147483001)['ocr']),'disabled KK also hides OCR favorite');rejects(function()use($model){$model->validate_favorites(array('ocr'));},'disabled favorite rejected');$model->feature_gate->denied=array();
 $model->save_favorites(2147483001,array());ok(!$model->favorites(2147483001),'explicit empty favorites preserved');
 $audit=$qaConn->query("SELECT COUNT(*) n FROM tenant_feature_audit")->fetch_assoc()['n'];ok($audit>$before,'changes audited in transaction');
 $model->save($defaults,$model->version(),$actor,TRUE);ok($model->all()['profile']['label']===$defaults['profile']['label'],'reset restores defaults');
 $none=$defaults;foreach($none as &$item)$item['show_on_dashboard']=0;unset($item);$model->save($none,$model->version(),$actor);ok(!$model->visible(),'all hidden produces empty menu');
}finally{$qaConn->rollback();}
ok($model->stored()===$original,'transaction rollback leaves original settings intact');ok($qaConn->query('SELECT COUNT(*) n FROM tenant_feature_audit')->fetch_assoc()['n']===$before,'rollback leaves no test audit records');