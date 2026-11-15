<?php
if(PHP_SAPI!=='cli')exit;
$db=new mysqli('localhost','root','');$db->query('DROP DATABASE IF EXISTS sukamulya_ui_test');echo 'Disposable DB removed'.PHP_EOL;
