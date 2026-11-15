# REGRESSION_TEST_RESULTS

Full tables and commands: `TEST_RESULTS.md` §4.

**Result:** 13 runnable suites, 235 assertions — identical before and after the patches, on PHP 8.2.12, 8.2.35 and 8.4.27. 0 regressions.

**Pre-existing failures (identical before/after, not caused by this work):** 2 KK image tests (no GD), `admin_system_final_smoke` (handover snapshot assertion), 6 subscription/platform suites (missing test fixtures). These areas are **UNVERIFIED** by automation.

Re-run:
```
set DB_NAME=sukamulya_ui_test & set CI_ENV=development
C:\xampp\php\php.exe -S 127.0.0.1:8097 index.php
:: in another window, each test, e.g.
set DB_NAME=sukamulya_ui_test & set TEST_BASE=http://127.0.0.1:8097/ & C:\xampp\php\php.exe tests\role_menu_access_smoke.php
```
