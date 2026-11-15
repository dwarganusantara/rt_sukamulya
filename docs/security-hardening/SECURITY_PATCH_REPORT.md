# SECURITY_PATCH_REPORT

All patches are on the development copy only. Nothing was deployed. Evidence: `TEST_RESULTS.md`.

### SEC-01 — Web exposure of repository, dumps and dev artifacts
| Informasi | Isi |
|---|---|
| Modul | Web root / deployment |
| File | `.htaccess`; new `tests/.htaccess`, `tools/.htaccess`, `tmp/.htaccess`, `docs/.htaccess`, `database sql/.htaccess`; `uploads/.htaccess` |
| Kerentanan | Sensitive data exposure (`.git/config` 200, `database.sql` 200 with admin hash + PII, `tmp/*.json`, `*.md`) |
| Severity | Critical |
| Root cause | Root `.htaccess` only had the CI rewrite; dev artifacts live inside the web root |
| Patch | Rewrite rules return 403 for dotfiles (except `.well-known`), `database sql/ tests/ tools/ tmp/ docs/ application/ system/`, `*.sql *.md *.log *.bak …`, root `*.php` except `index.php`, root `*.png` QA screenshots; `FilesMatch` fallback without mod_rewrite; deny-all `.htaccess` in each dev directory; baseline headers for static files; `nosniff` on `uploads/` |
| Business impact | None — routes, assets, public uploads unchanged |
| Compatibility | Apache 2.4 (`authz_core`) + 2.2 fallback; `mod_headers` optional |
| Test | 18/18 sensitive paths → 403; 9/9 public pages/assets → 200/307 (Apache probes) + 12 checks in `tests/security_hardening_smoke.php` |
| Status | **PASS** |
| Rollback | Restore `.htaccess` and `uploads/.htaccess` from backup `source.zip`; delete the five new `.htaccess` files |

### SEC-02 — Login brute force
| Informasi | Isi |
|---|---|
| Modul | Authentication (admin, resident) |
| File | new `application/libraries/Login_throttle.php`; `application/controllers/Admin.php` (`login`); `application/controllers/Citizen.php` (`login`) |
| Kerentanan | Unlimited password guessing; user enumeration by response time |
| Severity | High |
| Root cause | Only the platform-owner login had a limiter |
| Patch | Failed attempts recorded in existing `platform_login_attempts` (namespaced SHA-256 keys, no schema change). Block at 8 failures per account or 30 per IP in 15 min. Success clears only that account's counter. Dummy bcrypt verify for unknown usernames. Same message style as the platform limiter |
| Business impact | Legitimate users unaffected unless they fail 8 times in 15 minutes; error text for wrong password unchanged |
| Compatibility | Skips silently if the table is absent (fresh install) |
| Test | 7 checks PASS (lock after 8, lock follows account, unlock after window, success clears, resident lock) |
| Status | **PASS** |
| Rollback | Revert the three login blocks in Admin.php/Citizen.php (marked `SEC-02`); delete `Login_throttle.php`. Optional cleanup: `DELETE FROM platform_login_attempts WHERE created_at < NOW() - INTERVAL 1 DAY` |

### SEC-03 — Encryption key in source
| Informasi | Isi |
|---|---|
| Modul | Configuration / secrets |
| File | `application/config/config.php` |
| Kerentanan | Hard-coded secret, present in git history |
| Severity | High |
| Root cause | Key committed with config |
| Patch | `getenv('APP_ENCRYPTION_KEY') ?: <existing key>` — production can inject the key; existing ciphertext stays readable |
| Business impact | None |
| Compatibility | Encryption round trip and cross-version decrypt PASS on 8.2.12 / 8.2.35 / 8.4.27 |
| Test | `tests/encryption_compat_test.php` 6/6 PASS |
| Status | **PARTIAL** — rotation BLOCKED pending approval (see SECURITY_FINDINGS.md) |
| Rollback | Restore the original single line |

### SEC-04 — Security headers
| Informasi | Isi |
|---|---|
| Modul | All CI responses |
| File | new `application/hooks/Security_headers.php`; `application/config/hooks.php` |
| Kerentanan | Clickjacking, MIME sniffing, referrer leakage, version disclosure |
| Severity | Medium |
| Root cause | No global headers |
| Patch | `pre_controller` hook: `X-Frame-Options: SAMEORIGIN`, `CSP: frame-ancestors 'self'`, `nosniff`, `Referrer-Policy`, remove `X-Powered-By`, HSTS only on HTTPS. No script-src CSP (would break inline scripts) |
| Business impact | None — same-origin preview iframes still allowed; `admin/theme-css` already sends `text/css` |
| Compatibility | CLI skipped (`is_cli()`) |
| Test | 6 header checks PASS; full regression unchanged |
| Status | **PASS** |
| Rollback | Remove the hook entry from `hooks.php` |

### SEC-05 — Session handling
| Informasi | Isi |
|---|---|
| Modul | Session / cookies |
| File | `application/controllers/Citizen.php` (`logout`); `application/config/config.php` |
| Kerentanan | Session ID reused after logout; cookies not `Secure` on HTTPS |
| Severity | Medium |
| Root cause | `logout` only unset keys; `cookie_secure` fixed FALSE |
| Patch | `sess_regenerate(TRUE)` after resident logout (other session keys preserved, as before); `cookie_secure = is_https()`; explicit `sess_samesite = 'Lax'` (CI default) |
| Business impact | None |
| Compatibility | HTTP on XAMPP keeps working (Secure only when HTTPS) |
| Test | 4 checks PASS (new ID on logout, old ID rejected, HttpOnly+SameSite=Lax). Secure flag on HTTPS **UNVERIFIED** (no local HTTPS) |
| Status | **PASS** (HTTPS part UNVERIFIED) |
| Rollback | Remove the added `sess_regenerate` line; set `cookie_secure = FALSE`; delete `sess_samesite` line |

### SEC-06 — Registration password minimum
| Informasi | Isi |
|---|---|
| Modul | Resident registration |
| File | `application/controllers/Citizen.php` (`register`); `application/views/citizen/register.php` |
| Kerentanan | Weak passwords on self-registration |
| Severity | Medium |
| Root cause | Rule existed for password change (min 8) but not for registration |
| Patch | Same min-8 rule + `minlength="8"` and placeholder "Minimal 8 karakter" |
| Business impact | New registrations need ≥ 8 characters (aligned with the existing password-change rule). Existing accounts unaffected |
| Compatibility | — |
| Test | 3 checks PASS |
| Status | **PASS** |
| Rollback | Remove the `elseif (strlen($password) < 8)` branch; restore input attributes |

## Round 2 (2026-11-13, owner instruction "perbaiki semua")
All retested by `tests/security_hardening_smoke.php` (63/63 PASS on PHP 8.2.12; ALL PASS on 8.4.27).

| ID | Files | Patch | Business impact | Test | Status | Rollback |
|---|---|---|---|---|---|---|
| SEC-03 (rotation) | `application/config/config.php`, new `config/secrets.php` (git-ignored), `config/secrets.example.php`, `controllers/Sistem.php` (`backup_key`), `.gitignore` | Key read from env → `secrets.php`; new random 32-byte key; old key removed from source. Backups refuse an empty/short key | None (SMTP password never set, no backups existed) | encryption round trip on new key PASS; backup run PASS in isolated test | PASS | Restore old line in config.php; delete secrets.php |
| SEC-12 (code) | `application/config/database.php` | Credentials: env `DB_USER/DB_PASS` → `secrets.php` → root fallback | None | All suites connect normally | PARTIAL (account creation → OWNER_ACTIONS) | Restore database.php |
| SEC-16 | `database.sql`, `database sql/database.sql` | Seeded admin hash and personal resident row replaced by comments (install already uses `create_admin.php`) | New installs create the admin with `create_admin.php` (README step 4) | 0 `$2y$` hashes left in any `.sql` | PASS (password change → owner) | Restore files from source.zip |
| SEC-07 | `controllers/Citizen.php` (`letter_asset`, `letter`, `is_staff_session`), `views/citizen/letter.php`, `views/citizen/request.php` | Stamp/signature: staff (admin or platform owner) always; residents only with `?id=` of own request in `disetujui` with letter number. Logo unchanged for residents. Request preview shows "Stempel & tanda tangan dibubuhkan setelah surat disetujui" | Residents no longer see the real stamp/signature in the draft preview / pending-letter preview; approved letters print exactly as before | 15 checks PASS | PASS | Revert the `SEC-07` blocks |
| SEC-08 | new `helpers/logout_helper.php`, `config/autoload.php`, `Admin::logout`, `Citizen::logout`, 5 views (admin header, shell header, account menu, mobile nav, dashboard mobile) | Same logout links + `?t=<per-session token>`. POST or valid token logs out; otherwise a confirmation page with a POST button. Not logged in → login page | No visible change for normal use | 9 checks PASS | PASS | Revert links to `site_url(...)`, remove the two `logout_request_ok` lines |
| SEC-09 | `Admin::login` | "Akun ini sudah dinonaktifkan" only after the correct password; otherwise the generic error (and counted by SEC-02) | Deactivated users who know their password still see the explanation | 3 checks PASS | PASS | Move the block back before `password_verify` |
| SEC-10 | `config/config.php`, new `application/cache/sessions/` (+ deny `.htaccess`), `tests/dashboard_icons_smoke.php` | `sess_save_path` = `application/cache/sessions` (override `SESSION_SAVE_PATH`) | Everyone logged in had to log in once again after the switch | 1 check PASS | PASS | Restore `sys_get_temp_dir()` |
| SEC-11 | `hooks/Security_headers.php` | Enforced `object-src 'none'; base-uri 'self'; form-action 'self'` + `Content-Security-Policy-Report-Only` script/style policy | None (verified no `<object>/<embed>/<base>`/external forms) | 2 checks PASS + full regression | PARTIAL by design | Remove the added directives |
| TEST-01 | `tests/admin_system_final_smoke.php` | Stale input: handover form now takes official ids (`'o'.id`) like `handover_smoke.php`; assertion unchanged | — | 10 → 25 PASS (34/34 with `tmp/adminqa` fixture) | PASS | Restore file |

### PHP-01 — PHP 8.4 compatibility patches (framework)
| Informasi | Isi |
|---|---|
| Modul | CodeIgniter core |
| File | `index.php`, `system/core/Exceptions.php`, `system/libraries/Encryption.php`, `system/libraries/Xmlrpcs.php` |
| Kerentanan | n/a (compatibility) — PHP 8.4 deprecations: `E_STRICT` constant, implicit nullable `array $params = NULL`, `"${var}"` interpolation |
| Severity | Low |
| Patch | `E_STRICT` → literal `2048` (same value on every PHP version); `?array $params = NULL`; `{$err}` |
| Business impact | None |
| Compatibility | Valid since PHP 7.1; lint clean on 8.2.12 and 8.4.27 with `error_reporting=-1` |
| Test | Full regression on 8.2.12, 8.2.35, 8.4.27 identical to baseline; encryption cross-version PASS |
| Status | **PASS** |
| Rollback | Restore the four files from `source.zip` |
