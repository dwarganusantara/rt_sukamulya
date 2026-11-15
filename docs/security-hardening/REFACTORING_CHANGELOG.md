# REFACTORING_CHANGELOG

No refactoring beyond the security and compatibility patches was done. The audit found no vulnerable SQL, no unsafe error handling and no broken transaction code that needed restructuring. Large controllers (Admin.php ≈ 93 KB, Sistem.php ≈ 60 KB) are hard to maintain, but splitting them brings no security benefit and adds regression risk, so it was deliberately deferred.

| Date | Change | Reason | Files |
|---|---|---|---|
| 2026-11-13 | Extracted login limiter into reusable `Login_throttle` library instead of duplicating code in two controllers | SEC-02; one tested implementation | `application/libraries/Login_throttle.php` |
| 2026-11-13 | Global security headers as a hook instead of per-controller headers | SEC-04; single point | `application/hooks/Security_headers.php`, `application/config/hooks.php` |
| 2026-11-13 | PHP 8.4 syntax modernisation in 4 framework spots | PHP-01 | `index.php`, `system/core/Exceptions.php`, `system/libraries/Encryption.php`, `system/libraries/Xmlrpcs.php` |
| 2026-11-13 | Added automated tests | Retest evidence | `tests/security_hardening_smoke.php`, `tests/encryption_compat_test.php` |

Not removed (looks dead, not proven dead): the anonymous branch in `Citizen::letter_asset` — part of SEC-07 decision.
