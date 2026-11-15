# BUSINESS_PROCESS_BASELINE

Authoritative business descriptions remain in `bisnis-proses-website-rt09-rw08-sukamulya.md`, `docs/BISNIS_PROSES_FEATURE_SUBSCRIPTION_REVISI.md`, `docs/RT_RW_DIGITAL_IMPLEMENTASI.md`, `docs/ROUTE_FEATURE_MATRIX.md`. This file lists the flows that must keep working and how they are guarded by tests.

| Flow | Key rules preserved | Locked by |
|---|---|---|
| Resident registration & login | Self-registration; login only for active accounts; session regenerated on login | security_hardening_smoke (login, register ≥ 8) |
| Letter requests | Draft → submitted → approved (`disetujui`) with letter number; print only when approved (preview allowed) | Code unchanged; Citizen request flow not modified |
| Dues & payments | Payment between Rp 1 and remaining amount; one pending payment per due; transfer requires proof (JPG/PNG/WebP/PDF ≤ 5 MB); verification by staff | Code unchanged; admin_reconciliation_smoke |
| Cash book / reconciliation | Verified transactions only on public finance page; reconciliation figures | admin_reconciliation_smoke 17/17 (identical before/after) |
| Staff roles & menu access | 9 roles, `rt_permissions`, per-role menu toggles, feature/subscription gates enforced server-side | role_menu_access_smoke 32/32, admin_ops_ui_smoke 31/31 |
| Notulen, inventory, contacts, purchasing, accounts | Versioned notes, stock counts, purchase totals, hashed passwords, auditor read-only | admin_ops_ui_smoke 31/31 |
| Settings history, theme, custom fields, handover, audit log | Restore, filters, validation, immutability | settings_history 22, theme 37, custom_fields 23, handover 27, audit 11 |
| KK (family card) | OCR draft, validation, privacy whitelist, admin verification | kk_fields / kk_header_digits / kk_two_table (image tests need GD) |
| Subscription / platform owner | Server-owned identity, packages, feature status | UNVERIFIED in this run (test credentials absent) — code not modified |

## Intentional behaviour changes (security, approved scope)
1. Login is refused for 15 minutes after 8 failed attempts on one account or 30 from one IP (message: "Terlalu banyak percobaan login…").
2. New resident registrations need a password of at least 8 characters (the same rule already used by *Ganti Password*).
3. Responses carry anti-clickjacking headers: the site can no longer be embedded in iframes on other domains.
4. Files under `.git/`, `docs/`, `tests/`, `tools/`, `tmp/`, `database sql/` and root `*.sql/*.md/*.png` are no longer downloadable.
5. (Round 2) The resident's request-form preview and the preview of a not-yet-approved letter show "Stempel & tanda tangan dibubuhkan setelah surat disetujui" instead of the real stamp/signature. Approved letters and all staff views are unchanged.
6. (Round 2) A logout link opened without its session token (e.g. a forged link) shows a "Keluar dari akun?" confirmation instead of logging out immediately. Normal menu clicks log out exactly as before.
7. (Round 2) A deactivated admin sees "Akun ini sudah dinonaktifkan" only after typing the correct password; otherwise "Username atau password salah."
8. (Round 2) Fresh installs no longer contain a pre-made `admin` / resident account; create the admin with `create_admin.php` (README step 4).
No route, menu, role, calculation, status flow or report output was changed.
