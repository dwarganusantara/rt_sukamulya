<?php
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
$username=getenv('PLATFORM_OWNER_USER');$password=getenv('PLATFORM_OWNER_PASSWORD');
if(!$username||!preg_match('/^[A-Za-z0-9_.-]{3,100}$/',$username)||strlen((string)$password)<10){fwrite(STDERR,"Set owner username and password (minimum 10 characters) in the process environment.\n");exit(1);}
$identity=array('enabled'=>TRUE,'username'=>$username,'password_hash'=>password_hash($password,PASSWORD_DEFAULT));
$content="<?php\ndefined('BASEPATH') OR exit('No direct script access allowed');\n// Server-owned identity; unavailable to account-management endpoints.\n\$config['identity']=".var_export($identity,TRUE).";\n";
$target=dirname(__DIR__).'/application/config/platform_owner.php';
if(file_put_contents($target,$content,LOCK_EX)===FALSE)throw new RuntimeException('Owner configuration could not be written.');
echo "Platform owner configured with a password hash. No pengurus account created.\n";
