<?php
// Reuse the isolated owner HTTP harness and verify the permanent-free boundary.
require __DIR__.'/platform_owner_smoke.php';
date_default_timezone_set('Asia/Jakarta');
$marker=json_decode($db->query("SELECT setting_value FROM settings WHERE setting_key='subscription_demo_offers_v1'")->fetch_assoc()['setting_value'],TRUE);$basic_id=(int)$marker['basic'];
$basic=$db->query('SELECT * FROM subscription_plans WHERE id='.$basic_id)->fetch_assoc();$details=$db->query('SELECT * FROM subscription_plan_details WHERE plan_id='.$basic_id)->fetch_assoc();
$owner=(new OwnerHttp)->login($c['platform'],$c['password'],TRUE);
$owner->go('platform/paket',array('action'=>'save','plan_id'=>$basic_id,'version'=>$details['version'],'name'=>$basic['name'],'price'=>'0','duration_days'=>0,'description'=>$details['description'],'benefits'=>implode("\n",json_decode($details['benefits_json'],TRUE)),'theme'=>'green','sort_order'=>0,'features'=>array('letters','announcements','events'),'is_active'=>1));
owner_check(in_array($owner->code,array(302,303,307),TRUE),'Basic without expiry can be edited and saved');
$normal=(new OwnerHttp)->login($c['username'],$c['password']);$normal->go('admin/langganan');owner_check(substr_count($normal->html,'class="sp-plan sp-basic"')===1,'Tenant page shows Basic only once');
$licences=$db->query('SELECT COUNT(*) n FROM tenant_subscriptions')->fetch_assoc()['n'];
$owner->go('platform/langganan',array('action'=>'subscription','tenant_id'=>1,'plan_id'=>$basic_id,'status'=>'ACTIVE','starts_at'=>date('Y-m-d\TH:i',time()-60),'ends_at'=>date('Y-m-d\TH:i',time()+86400),'reason'=>'Free tier validation'));
owner_check($owner->code===200&&strpos($owner->html,'Paket Gratis tanpa batas waktu tersedia otomatis')!==FALSE&&$db->query('SELECT COUNT(*) n FROM tenant_subscriptions')->fetch_assoc()['n']===$licences,'Basic cannot become an artificial paid licence');
