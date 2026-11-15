# SECURITY_AUDIT — summary

Application: Sukamulya RT/RW Digital (CodeIgniter 3.1.13). Audit date 2026-11-13. Scope and method: manual review of all controllers, models, libraries, helpers, hooks, config and routes; a token-based scan of every output statement in 155 views; a guard scan of 159 public controller actions; probes of the web root over Apache; and automated retests.

## Posture
The code base already applies most controls correctly: parameterised SQL, consistent output escaping, global CSRF, ownership checks on resident data, server-side feature/menu authorization, and safe uploads. The most serious problems were in **deployment hygiene and authentication hardening**, not in the business code:
- Critical: the git repository and SQL dumps could be downloaded (fixed).
- High: no login rate limit (fixed); encryption key committed (mitigated, rotation pending approval); root/empty DB default (deployment); unsupported PHP/MariaDB versions approaching or past end of life (upgrade path tested).

Totals: 1 Critical, 4 High, 4 Medium, 3 Low, 3 Info. Fixed and retested: SEC-01, 02, 04, 05, 06 (+ PHP-01). Partial: SEC-03. Open, waiting on a decision: SEC-07, 08, 09, 10, 12, 14.

Details: `SECURITY_FINDINGS.md` · patches: `SECURITY_PATCH_REPORT.md` · evidence: `TEST_RESULTS.md`.
Limits: no HTTPS environment, no browser UAT, and subscription/platform tests could not run (fixtures absent). This report does not claim the application is free of vulnerabilities.
