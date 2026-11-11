<?php
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
$dry=in_array('--dry-run',$argv,TRUE);$root=dirname(__DIR__);$offers=require $root.'/application/config/subscription_demo.php';
$db=new mysqli(getenv('DB_HOST')?:'localhost',getenv('DB_USER')?:'root',getenv('DB_PASS')?:'',getenv('DB_NAME')?:'sukamulya');$db->set_charset('utf8mb4');
$marker='subscription_demo_offers_v1';$s=$db->prepare('SELECT setting_value FROM settings WHERE setting_key=?');$s->bind_param('s',$marker);$s->execute();$saved=$s->get_result()->fetch_assoc();$ids=$saved?(json_decode($saved['setting_value'],TRUE)?:array()):array();
$available=array_column($db->query('SELECT code FROM app_features')->fetch_all(MYSQLI_ASSOC),'code');
foreach($offers as $offer)if(array_diff($offer['features'],$available))throw new RuntimeException('Demo references an unavailable feature.');
if(!$dry)$db->begin_transaction();
try{
 foreach($offers as $key=>$offer){
  $id=(int)($ids[$key]??0);if($id&&$db->query('SELECT id FROM subscription_plans WHERE id='.$id)->num_rows){echo 'Preserved existing demo offer: '.$key.PHP_EOL;continue;}
  $s=$db->prepare('SELECT id FROM subscription_plans WHERE name=? AND price=? AND duration_days=? AND is_active=1 ORDER BY id DESC LIMIT 1');$s->bind_param('ssi',$offer['name'],$offer['price'],$offer['duration_days']);$s->execute();$existing=$s->get_result()->fetch_assoc();
  if($existing){$ids[$key]=(int)$existing['id'];echo 'Reused configured offer: '.$offer['name'].PHP_EOL;continue;}
  echo ($dry?'Would create: ':'Creating: ').$offer['name'].' | Rp '.$offer['price'].' | '.($offer['duration_days']?$offer['duration_days'].' days':'unlimited').PHP_EOL;if($dry)continue;
  $s=$db->prepare('INSERT INTO subscription_plans(name,price,duration_days,is_active)VALUES(?,?,?,1)');$s->bind_param('ssi',$offer['name'],$offer['price'],$offer['duration_days']);$s->execute();$id=$db->insert_id;$ids[$key]=$id;
  foreach($offer['features'] as $code){$s=$db->prepare('INSERT INTO subscription_plan_features(plan_id,feature_code)VALUES(?,?)');$s->bind_param('is',$id,$code);$s->execute();}
  $benefits=json_encode($offer['benefits'],JSON_UNESCAPED_UNICODE);$s=$db->prepare('INSERT INTO subscription_plan_details(plan_id,description,benefits_json,theme,sort_order,version)VALUES(?,?,?,?,?,1)');$s->bind_param('isssi',$id,$offer['description'],$benefits,$offer['theme'],$offer['sort_order']);$s->execute();
 }
 if(!$dry){$json=json_encode($ids);$s=$db->prepare('REPLACE INTO settings(setting_key,setting_value)VALUES(?,?)');$s->bind_param('ss',$marker,$json);$s->execute();
  $before=$saved['setting_value']??NULL;$after=json_encode(array('_actor_scope'=>'platform_owner','demo'=>TRUE,'plan_ids'=>$ids,'note'=>'Demo package offers only; tenant licences, catalog classifications and data unchanged.'));$action='demo_offers_seed';$s=$db->prepare('INSERT INTO subscription_audit_logs(actor_id,action,before_json,after_json)VALUES(0,?,?,?)');$s->bind_param('sss',$action,$before,$after);$s->execute();$db->commit();
 }
}catch(Throwable $e){if(!$dry)$db->rollback();throw $e;}
echo $dry?"Dry run complete.\n":"Demo offers ready. Existing licences, overrides and operational feature categories unchanged.\n";
