<?php
if(PHP_SAPI!=='cli')exit;
$root=dirname(__DIR__,2);$dest=__DIR__.'/application';
function cpcode($src,$dst){if(!is_dir($dst))mkdir($dst,0750,TRUE);foreach(scandir($src) as $f){if(in_array($f,array('.','..','storage','cache','logs'),TRUE))continue;$a=$src.'/'.$f;$b=$dst.'/'.$f;if(is_dir($a))cpcode($a,$b);else copy($a,$b);}}
cpcode($root.'/application',$dest);foreach(array('storage','logs','cache') as $f)@mkdir($dest.'/'.$f,0750,TRUE);
$index=file_get_contents($root.'/index.php');$index=str_replace("\$application_folder = 'application';","\$application_folder = ".var_export($dest,TRUE).';',$index);$index=str_replace("define('FCPATH', dirname(__FILE__).DIRECTORY_SEPARATOR);","define('FCPATH', ".var_export($root.'/',TRUE).");",$index);file_put_contents(__DIR__.'/index.php',$index);
$db=new mysqli('localhost','root','');$db->query('CREATE DATABASE IF NOT EXISTS sukamulya_ui_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');$db->query('SET FOREIGN_KEY_CHECKS=0');foreach($db->query('SHOW FULL TABLES FROM sukamulya WHERE Table_type="BASE TABLE"')->fetch_all() as $r){$t=$r[0];$db->query("CREATE TABLE IF NOT EXISTS sukamulya_ui_test.`$t` LIKE sukamulya.`$t`");$db->query("INSERT IGNORE INTO sukamulya_ui_test.`$t` SELECT * FROM sukamulya.`$t`");}echo 'Isolated code/storage/database ready'.PHP_EOL;
