<?php
if(PHP_SAPI!=='cli'||getenv('DB_NAME')!=='sukamulya_ui_test')exit(1);
$db=new mysqli('localhost','root','','sukamulya_ui_test');
// Explicitly disposable design fixtures, never operational packages/prices.
$db->query('DELETE FROM tenant_subscriptions');$db->query('DELETE FROM subscription_plan_details');$db->query('DELETE FROM subscription_plan_features');$db->query('DELETE FROM subscription_plans');
foreach(array(array('Pro',99000,array('family_card','resident_mutation','social_aid','cash_report')),array('Enterprise',199000,array('family_card','resident_mutation','social_aid','cash_report','inventory','purchasing','documents','activity_notes'))) as $plan){
 $s=$db->prepare('INSERT INTO subscription_plans(name,price,duration_days,is_active)VALUES(?,?,365,1)');$s->bind_param('sd',$plan[0],$plan[1]);$s->execute();$id=$db->insert_id;
 foreach($plan[2] as $code){$s=$db->prepare('INSERT INTO subscription_plan_features(plan_id,feature_code)VALUES(?,?)');$s->bind_param('is',$id,$code);$s->execute();}
}
echo "Disposable UI packages ready. Production unchanged.\n";
