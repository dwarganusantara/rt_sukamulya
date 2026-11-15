<?php
if(PHP_SAPI!=='cli' || getenv('DB_NAME')!=='sukamulya_ui_test')exit(1);
$db=new mysqli('localhost','root','','sukamulya_ui_test');
$db->query("UPDATE subscription_catalog SET commercial_type='FREE'");
echo "Disposable catalog reset to FREE for regression tests.\n";
