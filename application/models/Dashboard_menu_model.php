<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Dashboard_menu_model extends CI_Model
{
 public function __construct(){parent::__construct();$this->config->load('dashboard',TRUE);$this->load->library('Feature_gate');}
 public function options($key){return (array)$this->config->item('dashboard_'.$key,'dashboard');}
 public function storage_key(){return 'dashboard_menu_tenant_'.$this->feature_gate->tenant();}
 public function stored(){return (string)($this->db->get_where('settings',array('setting_key'=>$this->storage_key()))->row('setting_value')??'');}
 public function version(){return sha1($this->stored());}
 public function defaults(){
  $out=array();$order=0;foreach($this->options('registry') as $key=>$r){$out[$key]=array('label'=>$r[1],'icon'=>$r[2],'color'=>$r[3],'category'=>$r[4],'show_on_dashboard'=>$r[5],'sort_order'=>++$order*10,'is_default_favorite'=>(int)($r[6]??0),'show_in_header'=>(int)($r[7]??0));}
  return $out;
 }
 public function all(){
  $saved=json_decode($this->stored(),TRUE);$saved=is_array($saved)?$saved:array();$out=$this->defaults();
  foreach($out as $key=>&$item){$s=$saved[$key]??array();if(!is_array($s))continue;
   if(isset($s['label'])&&is_string($s['label'])&&mb_strlen($s['label'])<=60&&trim($s['label'])!=='')$item['label']=$s['label'];
   foreach(array('icon'=>'icons','color'=>'colors') as $field=>$option)if(isset($s[$field])&&is_string($s[$field])&&isset($this->options($option)[$s[$field]]))$item[$field]=$s[$field];
   if(isset($s['category'])&&in_array($s['category'],$this->options('categories'),TRUE))$item['category']=$s['category'];
   if(isset($s['sort_order'])&&is_numeric($s['sort_order']))$item['sort_order']=max(0,min(9999,(int)$s['sort_order']));
   foreach(array('show_on_dashboard','is_default_favorite','show_in_header') as $flag)if(isset($s[$flag]))$item[$flag]=(int)(bool)$s[$flag];
  }unset($item);
  foreach($out as $key=>&$item){$item['key']=$key;$item['route']=$this->options('registry')[$key][0];$item['allowed']=$this->feature_gate->link_ok($item['route'],'warga');}unset($item);
  uasort($out,function($a,$b){return ($a['sort_order']<=>$b['sort_order'])?:strcmp($a['key'],$b['key']);});return $out;
 }
 public function visible(){return array_filter($this->all(),function($item){return $item['show_on_dashboard']&&$item['allowed'];});}
 public function validate($input){
  if(!is_array($input)||array_diff(array_keys($input),array_keys($this->defaults()))||array_diff(array_keys($this->defaults()),array_keys($input)))throw new RuntimeException('Daftar menu tidak valid. Muat ulang halaman.');
  $out=array();foreach($this->defaults() as $key=>$default){$r=$input[$key];if(!is_array($r))throw new RuntimeException('Format menu tidak valid.');
   foreach(array('label','icon','color','category','sort_order') as $field)if(!isset($r[$field])||!is_scalar($r[$field]))throw new RuntimeException('Lengkapi pengaturan menu.');
   $label=trim((string)$r['label']);if($label===''||mb_strlen($label)>60||preg_match('/[<>\x00-\x1F]/u',$label))throw new RuntimeException('Nama menu wajib diisi, maksimal 60 karakter tanpa HTML.');
   if(!isset($this->options('icons')[$r['icon']])||!isset($this->options('colors')[$r['color']])||!in_array($r['category'],$this->options('categories'),TRUE))throw new RuntimeException('Ikon, warna, atau kategori tidak dikenal.');
   if(filter_var($r['sort_order'],FILTER_VALIDATE_INT)===FALSE||(int)$r['sort_order']<0||(int)$r['sort_order']>9999)throw new RuntimeException('Urutan harus angka 0 sampai 9999.');
   foreach(array('show_on_dashboard','is_default_favorite','show_in_header') as $flag)if(isset($r[$flag])&&$r[$flag]!=='1')throw new RuntimeException('Status menu tidak valid.');
   $out[$key]=array('label'=>$label,'icon'=>$r['icon'],'color'=>$r['color'],'category'=>$r['category'],'show_on_dashboard'=>isset($r['show_on_dashboard'])?1:0,'sort_order'=>(int)$r['sort_order'],'is_default_favorite'=>isset($r['is_default_favorite'])?1:0,'show_in_header'=>isset($r['show_in_header'])?1:0);
  }return $out;
 }
 // Caller holds a database transaction; all settings and audit rows commit together.
 public function save($values,$version,$actor,$reset=FALSE){
  $key=$this->storage_key();$this->db->query('INSERT IGNORE INTO settings (setting_key,setting_value) VALUES (?,?)',array($key,''));
  $row=$this->db->query('SELECT setting_value FROM settings WHERE setting_key=? FOR UPDATE',array($key))->row_array();
  if(!hash_equals(sha1((string)$row['setting_value']),(string)$version))throw new RuntimeException('Pengaturan baru diubah admin lain. Muat ulang sebelum menyimpan.');
  $old=json_decode((string)$row['setting_value'],TRUE);$old=is_array($old)?$old:$this->defaults();
  $this->db->where('setting_key',$key)->update('settings',array('setting_value'=>json_encode($values,JSON_UNESCAPED_UNICODE)));
  foreach($values as $code=>$value){if(($old[$code]??NULL)===$value)continue;
   $match=$this->feature_gate->match($this->options('registry')[$code][0]);$feature=$match?$this->db->get_where('app_features',array('code'=>$match[0]))->row_array():NULL;
   $this->db->insert('tenant_feature_audit',array('tenant_id'=>$this->feature_gate->tenant(),'feature_id'=>(int)($feature['id']??0),'actor_user_id'=>$actor,'old_value'=>json_encode($old[$code]??array(),JSON_UNESCAPED_UNICODE),'new_value'=>json_encode($value,JSON_UNESCAPED_UNICODE),'reason'=>($reset?'Reset dashboard: ':'Dashboard: ').$code));
  }
 }
 public function header_items(){return array_filter($this->all(),function($item){return $item['show_in_header']&&$item['allowed'];});}
 /** Navigasi header dashboard: urutan & label pendek dari config, hanya fitur aktif yang diizinkan. */
 public function header_nav(){
  $all=$this->all();$out=array();
  foreach((array)$this->config->item('dashboard_header','dashboard') as $key=>$label){if(isset($all[$key])&&$all[$key]['allowed']&&($key==='home'||$all[$key]['show_in_header']))$out[$key]=array('label'=>$label,'route'=>$all[$key]['route']);}
  return $out;
 }
 private function favorites_key($citizen){if((int)$citizen<1)throw new RuntimeException('Akun warga tidak valid.');return 'dashboard_favorites_t'.$this->feature_gate->tenant().'_c'.(int)$citizen;}
 public function favorites($citizen){
  $raw=$this->db->get_where('settings',array('setting_key'=>$this->favorites_key($citizen)))->row('setting_value');$saved=$raw!==NULL?json_decode($raw,TRUE):NULL;$visible=$this->visible();
  $keys=is_array($saved)?$saved:array_keys(array_filter($visible,function($item){return $item['is_default_favorite'];}));
  $out=array();foreach($keys as $key){if(is_string($key)&&isset($visible[$key])&&!isset($out[$key]))$out[$key]=$visible[$key];if(count($out)===4)break;}return $out;
 }
 public function validate_favorites($keys){
  if(!is_array($keys)||count($keys)>4)throw new RuntimeException('Pilih maksimal 4 menu favorit.');$visible=$this->visible();$out=array();
  foreach($keys as $key){if(!is_string($key)||!isset($visible[$key])||in_array($key,$out,TRUE))throw new RuntimeException('Pilihan favorit tidak tersedia atau berulang.');$out[]=$key;}return $out;
 }
 public function save_favorites($citizen,$keys){
  $keys=$this->validate_favorites($keys);$this->db->replace('settings',array('setting_key'=>$this->favorites_key($citizen),'setting_value'=>json_encode($keys)));return $keys;
 }}