# DEPLOYMENT_ROLLBACK_PLAN

**Status: NOT DEPLOYED.** Production deployment needs owner approval and UAT sign-off.

## Changed / new files
Modified: `.htaccess`, `uploads/.htaccess`, `index.php`, `application/config/config.php`, `application/config/hooks.php`, `application/controllers/Admin.php`, `application/controllers/Citizen.php`, `application/views/citizen/register.php`, `system/core/Exceptions.php`, `system/libraries/Encryption.php`, `system/libraries/Xmlrpcs.php`.
New: `application/libraries/Login_throttle.php`, `application/hooks/Security_headers.php`, `tests/.htaccess`, `tools/.htaccess`, `tmp/.htaccess`, `docs/.htaccess`, `database sql/.htaccess`, `tests/security_hardening_smoke.php`, `tests/encryption_compat_test.php`, `docs/security-hardening/*.md`.
Round 2 modified: `application/config/database.php`, `application/config/autoload.php`, `application/controllers/Sistem.php`, `application/views/citizen/letter.php`, `application/views/citizen/request.php`, `application/views/admin/header.php`, `application/views/citizen/shell_header.php`, `application/views/citizen/dashboard_mobile.php`, `application/views/site/account_menu.php`, `application/views/site/mobile_nav.php`, `database.sql`, `database sql/database.sql`, `.gitignore`, `tests/admin_system_final_smoke.php`, `tests/dashboard_icons_smoke.php`.
Round 2 new: `application/helpers/logout_helper.php`, `application/config/secrets.example.php`, `application/config/secrets.php` (**secret — deploy out-of-band, never via git**), `application/cache/sessions/` (+ `index.html`, `.htaccess`), `docs/security-hardening/OWNER_ACTIONS.md`.
No database migration. Switching the session directory logs every user out once.

## Pre-deployment checklist
1. Backup production files and DB (mysqldump with routines/triggers/events). Run a restore drill into a scratch schema.
2. Host has `mod_rewrite` (required already) and preferably `mod_headers`.
3. Set environment: `CI_ENV=production`, `DB_USER`/`DB_PASS` (least-privilege, SEC-12), optionally `APP_ENCRYPTION_KEY` **equal to the current key** (rotation is a separate approved change, SEC-03).
4. Do not upload dev artifacts: `.git/`, `tests/`, `tmp/`, `docs/`, `database sql/`, root `*.sql`, `*.png`, `*.md`, `create_admin.php` (blocked anyway by SEC-01, but better absent).
5. If the site runs on HTTPS, confirm the session cookie shows `Secure` and HSTS is present (UNVERIFIED locally).
6. UAT: login (admin, resident, platform owner), registration, letter request + print, dues payment with proof, staff verification, finance page, KK scan, SMTP e-mail, theme preview iframe.

## Deployment steps
1. Maintenance window; upload the changed/new files listed above.
2. Smoke: `/`, `admin/login`, `warga/login`, a public upload image → 200; `/.git/config`, `/database.sql` → 403.
3. Log in as each role once; check `application/logs` for new errors.

## Rollback
| Scope | Action | Time |
|---|---|---|
| Single patch | Revert the block marked `SEC-xx` / `PHP 8.4 compat` (see SECURITY_PATCH_REPORT "Rollback" rows) | minutes |
| Full | Restore the listed files from `C:\xampp\backup_sukamulya\20261113_185057\source.zip` (or the production backup); delete the new files | minutes |
| Data | Not required — no schema or data change. Optional: purge namespaced rows from `platform_login_attempts` | — |
Rollback is safe in any order; no patch depends on data written by another.

## PHP upgrade (separate change)
8.2.12 → 8.2.35 first, then 8.4.27 before 2026-12-31 (see PHP_COMPATIBILITY.md). Keep the old PHP folder to switch back by restoring the Apache `LoadModule`/`PHPIniDir` lines.
