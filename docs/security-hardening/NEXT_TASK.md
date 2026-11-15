# NEXT_TASK

1. Owner: run OWNER_ACTIONS.md §1 (DB account) and §2 (change admin password).
2. Owner: PHP 8.2.12 → 8.2.35 on XAMPP (OWNER_ACTIONS §3), then 8.4 before 2026-12-31 after checking the other XAMPP apps. Enable GD.
3. Owner/dev: fix pre-existing OCR issue (`kk_ruled_table_test`: 6 vs 5 members) and the subscription test failures (identical on original code).
4. Optional: generate `tmp/adminqa/sub-browser.json` with the Playwright scripts in `tmp/adminqa/*.cjs` so subscription_editor/page and platform_owner smoke tests can run.
5. Watch the browser console for CSP Report-Only violations; then move inline scripts to files/nonces and enforce script-src.
6. Restore drill of the backup into a scratch schema (DATABASE_INTEGRITY_REPORT).
7. Owner UAT (letters with stamp/signature after approval, logout from every menu, login lock message), then deploy per DEPLOYMENT_ROLLBACK_PLAN.md with `secrets.php` uploaded separately.
