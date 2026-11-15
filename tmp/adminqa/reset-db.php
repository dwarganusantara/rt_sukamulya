<?php
if(PHP_SAPI!=='cli')exit;
$db=new mysqli('localhost','root','');$db->query('DROP DATABASE IF EXISTS sukamulya_ui_test');echo 'Reset only disposable test database'.PHP_EOL;
