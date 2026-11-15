# PHP_COMPATIBILITY

## Versions found
| Component | Version | Support status (2026-11-13) |
|---|---|---|
| PHP (XAMPP) | 8.2.12 (Oct 2023), ZTS | 8.2 branch: security fixes only until **2026-12-31**. 8.2.12 is 23 patch releases behind 8.2.35 |
| CodeIgniter | 3.1.13 | Final CI3 release line; maintenance only. Not officially certified for 8.2+ |
| MariaDB | 10.4.32 | 10.4 series is end-of-life — no more security fixes |
| Apache | 2.4.58 | Older than current 2.4.x; update with XAMPP/host |
| OpenSSL / curl in PHP 8.2.12 | 3.0.11 / 8.4.0 | Outdated builds bundled with this PHP |
| Poppler (OCR) | 25.07.0 | Current |
| Composer packages | none | — |
| Extensions used | mysqli, mbstring, fileinfo, openssl, intl, curl, session, json; GD optional (absent) | All available on 8.2.35 and 8.4.27 Windows builds |

## Tests performed (not assumed)
| Runtime | Lint (`-d error_reporting=-1`) | Regression suite | Security retest | Encryption |
|---|---|---|---|---|
| 8.2.12 (XAMPP) | 0 errors | identical to baseline | 33/33 | PASS |
| 8.2.35 (latest 8.2, portable) | 0 errors | identical to baseline | ALL PASS | PASS + cross-version |
| 8.4.27 (latest 8.4, portable) | 2 deprecations → fixed by PHP-01 → 0 | identical to baseline | ALL PASS | PASS + cross-version |
Portable runtimes were placed in the session scratchpad; XAMPP configuration was not changed.

## Compatibility issues and handling
| Issue | PHP version | Where | Handling |
|---|---|---|---|
| Dynamic properties deprecated | 8.2+ | CI3 core (`$this->load`, `$this->db` …) — no `#[AllowDynamicProperties]` in 3.1.13 | Masked by `index.php` (`~E_DEPRECATED`). Still works on 8.4; removal planned only for PHP 9. No action now; re-evaluate before any PHP 9 move |
| `E_STRICT` constant deprecated | 8.4 | `index.php`, `system/core/Exceptions.php` | Replaced by literal 2048 (identical value) |
| Implicit nullable parameter | 8.4 | `CI_Encryption::encrypt/decrypt` | `?array $params = NULL` |
| `"${var}"` interpolation | 8.2 deprecated | `system/libraries/Xmlrpcs.php` (unused by app) | `{$var}` |
| GD missing | all | KK OCR image tests | Environment only; enable `extension=gd` when upgrading to run those tests |

## Recommendation
1. **Now (low risk):** upgrade XAMPP/host PHP 8.2.12 → **8.2.35** (same minor, verified identical behaviour).
2. **Before 2026-12-31 (target):** move to **PHP 8.4.27** — verified with the full available suite after PHP-01. Keep the PHP-01 patches.
3. Also plan MariaDB 10.4 → a supported LTS (10.11 or 11.4) in a separate, DB-tested change (needs approval; out of scope here).
4. Run owner UAT on the chosen runtime, including SMTP sending, KK scan with GD enabled, PDF/print letters.
PHP 8.5 was not tested.
