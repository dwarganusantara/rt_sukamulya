# SECURITY_FINDINGS

Scope: all of `application/` (16 controllers, 8 models, 14 libraries, 13 helpers, 155 views, config, hooks), web-root files, `system/` compatibility. Reference: OWASP Top 10 2021 / ASVS 5.0. Audit date 2026-11-13.
This is the posture found by this audit and its tests. It is **not** a claim that the application has no vulnerabilities.

## Findings
| ID | Severity | Title | OWASP | Status |
|---|---|---|---|---|
| SEC-01 | **Critical** | Source repo (`.git/`), SQL dumps (admin bcrypt hash + resident PII), `tmp/`, `docs/`, `tests/`, `*.md` downloadable from the web | A05, A01 | **FIXED – PASS** |
| SEC-02 | **High** | No brute-force / rate limit on admin and resident login | A07 | **FIXED – PASS** |
| SEC-03 | **High** | `encryption_key` hard-coded and committed; was downloadable via `.git` (SEC-01) | A02, A05 | **FIXED – PASS** – key rotated into git-ignored `secrets.php` (nothing to re-encrypt: SMTP password unset, no backups); empty key blocks backups |
| SEC-12 | **High (deploy)** | DB defaults to `root` with empty password when env vars are absent | A05 | **PARTIAL** – code reads `secrets.php`/env; creating the DB account was refused by the agent permission policy → OWNER_ACTIONS §1 |
| SEC-14 | **High (platform)** | PHP 8.2 security support ends 2026-12-31; XAMPP PHP 8.2.12 lacks ~23 security patch releases; MariaDB 10.4 is end-of-life | A06 | **OPEN – owner action** (shared XAMPP; app verified on 8.2.35/8.4.27) → OWNER_ACTIONS §3–4 |
| SEC-16 | **High** | Live `admin` password hash identical to the one published in `database.sql`; seed also held a resident's personal account | A07 | **PARTIAL** – both rows removed from the seed files; password change → OWNER_ACTIONS §2 |
| SEC-04 | Medium | No anti-clickjacking / nosniff / referrer headers | A05 | **FIXED – PASS** |
| SEC-05 | Medium | Resident logout keeps the same session ID; cookies never `Secure` even on HTTPS | A07 | **FIXED – PASS** (Secure-on-HTTPS: UNVERIFIED, no HTTPS locally) |
| SEC-06 | Medium | Resident self-registration accepts any password length (password change already requires 8) | A07 | **FIXED – PASS** |
| SEC-07 | Medium | `warga/aset-surat/{signature,stamp}`: any logged-in resident (registration is open) can download the RT signature and stamp images; the anonymous branch of the check is dead code | A01 | **FIXED – PASS** – staff always; residents only for their own approved letter; request preview shows a placeholder |
| SEC-10 | Low | Session files in shared system temp dir (`sys_get_temp_dir()`) | A05 | **FIXED – PASS** – `application/cache/sessions` (deny-all) |
| SEC-08 | Low | Admin/resident logout accepts GET (logout CSRF) | A01 | **FIXED – PASS** – per-session token on the same links; no token → confirmation form (POST) |
| SEC-09 | Low | Admin login says "Akun ini sudah dinonaktifkan" → reveals inactive usernames | A07 | **FIXED – PASS** – notice only after the correct password |
| SEC-11 | Info | No script-src CSP (views rely on inline scripts) | A03 | **PARTIAL** – enforced `object-src 'none'; base-uri 'self'; form-action 'self'`; script/style policy in Report-Only |
| SEC-13 | Info | `warga/keputusan/baca` excluded from CSRF | A01 | Accepted – can only flag the caller's own notices |
| SEC-15 | Info | Dev artifacts (root `*.sql`, `*.png`, `*.md`, `tests/`, `tmp/`) in the deploy tree | A05 | Mitigated by SEC-01; recommend excluding from production upload |

## Verified as sound (no change needed)
- **SQL injection**: every query uses Query Builder or `?` bindings. Only identifier interpolation is from internal constants (`Ops_Controller::lock`, `Ops_model` migrations). Sorting/filters use allowlists (`choice()`, regex-validated periods).
- **XSS**: token-based scan of all `echo`/`<?=` in 155 views (1,511 non-trivial outputs) → 103 direct outputs reviewed by hand; all are static arrays, integers or server-generated codes (`KK-000123`). User/DB text is consistently passed through `html_escape`. JSON in attributes is escaped.
- **Access control / IDOR**: resident endpoints filter by `citizen_id` (Mutasi, Pengaduan, Citizen request/attachment/document/due_proof, Keluarga). Admin endpoints use role guards plus the server-side `Feature_gate` and `Menu_access` hooks. Platform console uses a server-owned identity with its own limiter and fingerprint.
- **CSRF**: global, token regeneration, AJAX token refresh endpoints.
- **Uploads**: random names, `finfo` MIME allowlist, `getimagesize` content check, size limits, PHP execution blocked in `uploads/`, private dirs deny-all and served with `nosniff`. Path handling uses `basename()` / `realpath()` prefix checks.
- **Passwords**: `password_hash` / `password_verify`; session ID regenerated on every login.
- **Command execution**: OCR uses `proc_open` with an argument array and `bypass_shell`.
- **Errors**: `db_debug` off in production; KK module disables query logging for identity fields.

## Pre-existing functional issues found by tests (not security, not changed)
- `kk_ruled_table_test` (with GD): OCR reads 6 members instead of 5 on a synthetic ruled KK table — same on PHP 8.2.35 and 8.4.27; KK engine untouched. Needs an OCR-logic fix by the owner.
- `subscription_smoke` "Locked menu remains visible with premium trigger", `subscription_demo_smoke` "Configured demo price and duration basic", `subscription_all_menus_smoke` 14 FAIL — identical on the original code from backup; belong to the in-progress subscription work.

## Details (historical — decisions taken 2026-11-13, "perbaiki semua")
**SEC-03 key rotation.** The current key is in git history. If the repository or a deployment with `.git/` was ever reachable, treat it as compromised. Procedure (needs approval + maintenance window): decrypt `settings.smtp_pass_enc` with the old key → set `APP_ENCRYPTION_KEY` to a new 32-byte key → re-save SMTP password from *Antrean Email* panel (or re-encrypt) → verify e-mail sending. Also rotate the admin password whose hash was in `database.sql`.

**SEC-07 options.** (a) Keep as is (accepted risk). (b) Serve signature/stamp in the request preview as a watermarked/low-resolution image and the clean image only on approved letters (`?id=` owned + `disetujui`). (c) Hide signature/stamp from the preview. Options b/c change what residents see → owner approval required.

**SEC-12.** Production must set `DB_USER`/`DB_PASS` (least-privilege account: SELECT/INSERT/UPDATE/DELETE + the CREATE/ALTER used by self-migrations, no GRANT/FILE/SUPER).
