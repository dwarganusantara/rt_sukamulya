<?php
$src=file_get_contents(__DIR__.'/../../tests/admin_system_final_smoke.php');$end=strpos($src,'// Handover workflow');$capture=substr($src,0,$end);$capture=str_replace("array('admin','operator','auditor')","array('admin')",$capture);file_put_contents(__DIR__.'/capture.php',$capture);
