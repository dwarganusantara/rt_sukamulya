<?php
// Legacy migration entry point only. Owner credentials never become database accounts.
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
if(!in_array('--migrate-only',$argv,TRUE)){fwrite(STDERR,"Super Admin is a private server identity. Use tools/setup-platform.ps1 or tools/set-platform-owner.php.\n");exit(1);}
$db=new mysqli(getenv('DB_HOST')?:'localhost',getenv('DB_USER')?:'root',getenv('DB_PASS')?:'',getenv('DB_NAME')?:'sukamulya');$db->set_charset('utf8mb4');
foreach(explode(';',file_get_contents(dirname(__DIR__).'/subscription_upgrade.sql')) as $sql)if(trim($sql))$db->query($sql);
echo "Subscription schema installed; no owner account created in the database.\n";
