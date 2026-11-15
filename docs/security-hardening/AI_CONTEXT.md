# AI_CONTEXT — Sukamulya RT/RW Digital (CodeIgniter 3) Security Hardening

Read this first in every new session, then `NEXT_TASK.md`. Do not re-audit modules marked done in `PATCH_PROGRESS.md`.

## Environment (verified 2026-11-13)
| Item | Value |
|---|---|
| App path | `C:\xampp\htdocs\sukamulya` (XAMPP, URL `http://localhost/sukamulya/`) |
| Framework | CodeIgniter **3.1.13** (`system/core/CodeIgniter.php`), no Composer |
| PHP (XAMPP) | 8.2.12 ZTS — **no GD extension** |
| PHP tested extra | 8.2.35 NTS, 8.4.27 NTS (portable, outside XAMPP, session scratchpad) |
| DB | MariaDB 10.4.32, main DB `sukamulya`, isolated test DB `sukamulya_ui_test` |
| Web server | Apache 2.4.58 |
| Git | not on PATH — use `C:\Users\chendy\AppData\Local\GitHubDesktop\app-3.6.3\resources\app\git\cmd\git.exe`. Branch `main`, 1 commit, ~360 uncommitted owner changes (do not commit/discard them without the owner). |
| Backup (pre-change) | `C:\xampp\backup_sukamulya\20261113_185057\` → `source.zip`, `tools.zip`, `sukamulya.sql` |

## How to test (never against `sukamulya`)
```
set DB_NAME=sukamulya_ui_test & set CI_ENV=development
C:\xampp\php\php.exe -S 127.0.0.1:8097 index.php          (separate window)
set DB_NAME=sukamulya_ui_test & C:\xampp\php\php.exe tests\security_hardening_smoke.php
```
Existing smoke tests refuse any DB other than `sukamulya_ui_test`. `.htaccess` rules are only exercised through Apache (`APACHE_BASE=http://localhost/sukamulya/`, read-only probes).

## Architecture in one paragraph
Public site (`Site`), resident portal (`Citizen` + Ops modules under `warga/...`), staff admin (`Admin` + Ops modules under `admin/...`), platform owner / subscription (`Langganan`, `platform/...`). `Ops_Controller` (application/core/MY_Controller.php) supplies `guard_admin($perm)`, `guard_citizen()`, `need()`, `atomic()`, `lock()`. Permissions: `application/config/rt_access.php` (9 staff roles). Two `post_controller_constructor` hooks enforce feature/subscription gates (`Feature_gate`) and per-role menu access (`Menu_access`) server-side. CSRF is global (`csrf_regenerate` on). Private files are served through controllers with ownership checks.

## Rules that bind future work
- Business process, routes, roles, calculations and report output must not change.
- No schema change without a migration + owner approval. `SEC-02` reuses the existing `platform_login_attempts` table on purpose.
- `encryption_key` must NOT be rotated without re-encrypting `settings.smtp_pass_enc`.
- Keep patches minimal; every patch carries a `SEC-xx` comment.
