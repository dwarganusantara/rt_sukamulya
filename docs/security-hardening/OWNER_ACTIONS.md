# OWNER_ACTIONS — steps that must be run by the owner

These were not done automatically: creating DB accounts was refused by the agent's permission policy, and the
PHP/MariaDB upgrades change the shared XAMPP used by ~25 other applications on this machine.

## 1. SEC-12 — least-privilege database account (≈2 minutes)
Run in phpMyAdmin (SQL tab) or `C:\xampp\mysql\bin\mysql.exe -u root`. Choose your own strong password:
```sql
CREATE USER 'sukamulya_app'@'localhost' IDENTIFIED BY 'GANTI_DENGAN_PASSWORD_KUAT';
CREATE USER 'sukamulya_app'@'127.0.0.1' IDENTIFIED BY 'GANTI_DENGAN_PASSWORD_KUAT';
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX, REFERENCES ON `sukamulya`.*   TO 'sukamulya_app'@'localhost', 'sukamulya_app'@'127.0.0.1';
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX, REFERENCES ON `sukamulya\_%`.* TO 'sukamulya_app'@'localhost', 'sukamulya_app'@'127.0.0.1';
FLUSH PRIVILEGES;
```
(CREATE/ALTER are needed by the app's built-in self-migrations; DROP, GRANT, FILE and other databases are not granted. The second GRANT covers the test copies `sukamulya_ui_test` / `sukamulya_e2e`.)
Then edit `application/config/secrets.php`: set `'db_user' => 'sukamulya_app'` and `'db_pass' => '...'`. Open `/admin/login` to confirm.
Undo: clear `db_user` in secrets.php (falls back to root) and `DROP USER 'sukamulya_app'@'localhost', 'sukamulya_app'@'127.0.0.1';`.
On hosting: create the same account in the control panel and set it in `secrets.php` or env `DB_USER`/`DB_PASS`.

## 2. Admin password (hash was public in database.sql)
The live `admin` account still uses the password whose hash was downloadable. Log in → menu **Password** and set a new one (min. 10 characters), or run:
`C:\xampp\php\php.exe create_admin.php admin <password_baru_min_10> admin`
The agent did not choose a password for you.

## 3. PHP upgrade for XAMPP (affects every site in C:\xampp\htdocs)
Verified for this app on 8.2.35 and 8.4.27 (see PHP_COMPATIBILITY.md). Other apps on this XAMPP were not tested — 8.2.35 is the low-risk step.
1. Stop Apache in the XAMPP Control Panel.
2. Copy `C:\xampp\php` → `C:\xampp\php_8.2.12_backup`.
3. Download **Thread Safe** x64 build `php-8.2.35-Win32-vs16-x64.zip` from windows.php.net (TS is required for Apache `php8apache2_4.dll`).
4. Extract over `C:\xampp\php`, overwriting files but **keeping** `php.ini`.
5. Optional: in `C:\xampp\php\php.ini` enable `extension=gd` (needed by the KK image tests/OCR preprocessing).
6. Start Apache; check `C:\xampp\php\php.exe -v` and open the app.
Rollback: stop Apache, rename the folders back, start Apache.
For 8.4: use `php-8.4.x-Win32-vs17-x64.zip` (TS) after testing your other apps; requires the VC++ 2015-2022 redistributable.

## 4. MariaDB 10.4 (end-of-life)
Upgrade XAMPP's MariaDB to a supported LTS (10.11 or 11.4) in a planned window: full `mysqldump --all-databases` first, then install, import, run `mysql_upgrade`. Affects all databases on this server.

## 5. Production
Upload `application/config/secrets.php` separately (never through git) or use env variables. Do not deploy `.git/`, `tests/`, `tmp/`, `docs/`, `database sql/`, root `*.sql`/`*.png`/`*.md`.
