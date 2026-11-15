<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/*
 * SECURITY (SEC-03 / SEC-12): copy to application/config/secrets.php (git-ignored, web-denied)
 * and fill in real values. Environment variables, when set, take precedence:
 *   APP_ENCRYPTION_KEY, DB_HOST, DB_USER, DB_PASS, DB_NAME
 *
 * encryption_key protects settings.smtp_pass_enc and encrypted backups (application/storage/backups).
 * Changing it makes existing encrypted values unreadable - re-save the SMTP password and make a new
 * backup after a rotation. Generate: php -r "echo base64_encode(random_bytes(32));"
 */
return array(
    'encryption_key' => '',
    'db_user'        => '',   // least-privilege account, not root
    'db_pass'        => '',
);
