# DATABASE_INTEGRITY_REPORT

| Check | Result |
|---|---|
| Schema changes by this work | **None.** No migration needed. SEC-02 reuses existing table `platform_login_attempts` (id, ip_hash CHAR(64), created_at, index `idx_attempt(ip_hash, created_at)`) |
| Data changes in main DB `sukamulya` | **None.** Post-test dump vs pre-work dump: 0 differing lines (`--skip-dump-date`) |
| Backup | `C:\xampp\backup_sukamulya\20261113_185057\sukamulya.sql` (mysqldump, routines/triggers/events, single-transaction). Dump completeness checked (re-dump comparison). **Restore not yet tested — UNVERIFIED**; a restore drill into a scratch DB is required before production |
| Test writes | Only `sukamulya_ui_test`; security test deletes its own admins/citizens/attempt rows |
| Transactions & locking | Reviewed: financial and stock writes use `atomic()` + `SELECT … FOR UPDATE` (Admin dues payment, Operasional purchasing, Sosial recipients, Kk_model, Mutasi). No change |
| Rounding / nominal | Not touched. `money()` strips non-digits and caps at 999,999,999,999 (existing) |
| Report output | Not touched; reconciliation smoke identical before/after |
| Role-based data access | Resident queries filtered by `citizen_id`; verified (see SECURITY_FINDINGS) |
| Self-migrations (existing) | `Ops_model::ensure`, `Subscription_model::ensure`, `ensure_*_schema` run `CREATE TABLE IF NOT EXISTS` / `ADD COLUMN` at runtime. Not destructive, but require CREATE/ALTER privilege — note for SEC-12 least-privilege account |
| Growth of `platform_login_attempts` | Rows older than 1 day are purged probabilistically (1/50 failed logins) |

## Recommendations (need approval, not done)
- Run a restore drill of the backup into a new scratch schema and compare row counts.
- Move self-migrations to explicit, versioned migration scripts so production can run with a DB user without ALTER rights.
- Plan MariaDB 10.4 (EOL) upgrade.
