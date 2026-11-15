# TEST_RESULTS (also serves as REGRESSION_TEST_RESULTS evidence)

Date 2026-11-13. Test server: `php -S 127.0.0.1:8097 index.php`, `DB_NAME=sukamulya_ui_test`, `CI_ENV=development`. Test clients: XAMPP PHP 8.2.12. Same suite, same DB, same data in every run.

## 1. Syntax checking
| Run | Files | Result |
|---|---|---|
| `php -l` PHP 8.2.12, application + system (before) | 408 | 0 errors |
| `php -l` PHP 8.4.27, `error_reporting=-1` (before compat patch) | 410 | 2 deprecations (Encryption.php implicit nullable, Xmlrpcs.php `${}`) |
| Same after PHP-01 patch, changed files | 4 + 7 | 0 errors, 0 deprecations on 8.2.12 and 8.4.27 |

## 2. Security retest — `tests/security_hardening_smoke.php`
33/33 PASS on server PHP 8.2.12 (with Apache probes), and ALL PASS on 8.2.35 and 8.4.27 (CI checks only).
Covers SEC-01 (8 blocked + 4 still public), SEC-02 (7), SEC-04 (6), SEC-05 (4), SEC-06 (3), plus "login page still renders".

## 3. Apache exposure probe (read-only, main install)
Before: `.git/config`, `database.sql`, `database sql/database.sql`, `chat_upgrade.sql`, `tmp/super-admin-role-files.json`, `README.md`, `docs/KK_SCAN.md`, `bisnis-proses-….md` → **200**.
After: those + `.git/HEAD`, `.gitignore`, `.htaccess`, `create_admin.php`, `tests/*.php`, `tools/*.php`, root `*.png`, `application/…`, `system/…`, `uploads/letters/…` → **403** (18/18).
Still working: `/`, `pengumuman`, `admin/login`, `warga/login`, `kontak`, `keuangan`, `assets/admin-ops.css`, public upload image → 200; `admin` → 307 to login.

## 4. Regression — BEFORE → PATCH → AFTER
Columns: exit code / PASS / FAIL. Identical in all four runs.

| Test | Before (8.2.12) | After (8.2.12) | After (8.2.35) | After (8.4.27) |
|---|---|---|---|---|
| kk_fields_test | 0/1/0 | 0/1/0 | 0/1/0 | 0/1/0 |
| kk_header_digits_test | 0/1/0 | 0/1/0 | 0/1/0 | 0/1/0 |
| kk_two_table_test | 0/6/0 | 0/6/0 | 0/6/0 | 0/6/0 |
| dashboard_summary_test | 0/5/0 | 0/5/0 | 0/5/0 | 0/5/0 |
| admin_ops_ui_smoke | 0/31/0 | 0/31/0 | 0/31/0 | 0/31/0 |
| admin_audit_ui_smoke | 0/11/0 | 0/11/0 | 0/11/0 | 0/11/0 |
| admin_reconciliation_smoke (financial) | 0/17/0 | 0/17/0 | 0/17/0 | 0/17/0 |
| admin_settings_ui_smoke | 0/22/0 | 0/22/0 | 0/22/0 | 0/22/0 |
| custom_fields_smoke | 0/23/0 | 0/23/0 | 0/23/0 | 0/23/0 |
| handover_smoke | 0/27/0 | 0/27/0 | 0/27/0 | 0/27/0 |
| settings_history_smoke | 0/22/0 | 0/22/0 | 0/22/0 | 0/22/0 |
| theme_settings_smoke | 0/37/0 | 0/37/0 | 0/37/0 | 0/37/0 |
| role_menu_access_smoke (authorization) | 0/32/0 | 0/32/0 | 0/32/0 | 0/32/0 |
| **Sum of PASS** | **235** | **235** | **235** | **235** |

### Pre-existing failures (same before and after — not caused by this work) → BLOCKED / UNVERIFIED
| Test | Before = After | Cause |
|---|---|---|
| kk_formats_test, kk_ruled_table_test | fatal | XAMPP PHP has no GD (`imagecreatetruecolor`) — environment |
| admin_system_final_smoke | 10 PASS then exception "Handover creates immutable aggregate snapshot" | Pre-existing assertion failure in test data / feature; not touched |
| subscription_smoke | exception "Isolated application configuration required" | Needs test-only platform config |
| subscription_editor/page/demo_smoke, platform_owner_smoke | warning on line 3–8 | Need `tmp/adminqa/*` credential files that are absent |
| subscription_all_menus_smoke | 12 PASS / 15 FAIL | Needs Super Admin test identity (`CI_ENV=testing`) |
These modules (subscription / platform owner, KK image OCR) are therefore **UNVERIFIED** by automation in this run. The code paths changed by this work do not touch them except the shared login form (platform-owner branch runs before SEC-02 and is unchanged).

## 5. PHP compatibility
`tests/encryption_compat_test.php`: round trip PASS on 8.2.12, 8.2.35, 8.4.27; ciphertext from 8.2.12 decrypts on 8.2.35 and 8.4.27; ciphertext from 8.4.27 decrypts on 8.2.12. No deprecation raised (handler exits on any notice).
Server stderr for the 8.4.27 run: no warnings. `application/logs` for the day: only 404 entries.

## 6. Database integrity
Main DB `sukamulya` re-dumped after all tests and compared with the pre-work backup dump: **0 differing lines**. All test writes went to `sukamulya_ui_test`; the security test removes its own rows.

## 7. Round 2 (after "perbaiki semua")
| Run | Result |
|---|---|
| Lint of all changed files (8.2.12) | 0 errors |
| `security_hardening_smoke.php` server 8.2.12 (+ Apache probes) | **63/63 PASS** |
| same, server 8.4.27 | ALL PASS |
| Main regression suite, 8.2.12 and 8.4.27 | identical to baseline (235 PASS) except `admin_system_final_smoke` 10 → 25 PASS after TEST-01 |
| Isolated `tmp/adminqa` fixture (`setup.php` + `router.php`), **original code from backup vs patched code** | identical: subscription_smoke 16, subscription_all_menus 13/14 FAIL, editor 1, page 0, demo 1, platform_owner 0, admin_system_final **34/34** |
| KK image tests with GD (portable 8.2.35 / 8.4.27) | kk_formats PASS, kk_ocr_image PASS, kk_ruled_table FAIL (6 vs 5 members, pre-existing OCR issue) |
| `encryption_compat_test.php` with new key | PASS on 8.2.12 / 8.4.27 |
| Main DB `sukamulya` vs pre-work dump | 0 differing lines |
Test artifacts removed afterwards: encrypted test backups in `application/storage/backups`, `tmp/adminqa/application` fixture copy.

## Not executed (UNVERIFIED)
- HTTPS-only behaviour (Secure cookie, HSTS) — no TLS on local XAMPP.
- Browser/visual UAT — not run; needs owner UAT before production.
- Performance testing — changes add ≤ 2 indexed queries on login only; not measured.
- Malware scanning of uploads — no scanner available.
