# APPLICATION_MAP

Source of truth for routes: `application/config/routes.php` (150 lines). Feature → route mapping: `application/config/features.php` and `docs/ROUTE_FEATURE_MATRIX.md`. Menu definition: `application/config/admin_menu.php`, `application/config/dashboard.php`.

## Controllers (159 public actions)
| Controller | Actions | Area | Guard | Purpose |
|---|---|---|---|---|
| Site | 12 | public | none (public by design); `proof` = verified tx only, unverified only for admin/bendahara/ketua | Home, pages, announcements, agenda, gallery, structure, finance transparency, search, contact |
| Citizen | 23 | `warga/*` | `guard()` (active citizen) or inline owner check on downloads | Login/register, dashboard, letters (request, draft, detail, delete, print), attachments, dues & payment proof, profile, password, chat |
| Admin | 46 | `admin/*` | `guard($roles)` + hooks | Dashboard, posts/pages/quick links, officials, citizens, letters & settings, dues/billing, transactions, chat, notifications, settings |
| Keluarga | 4 | `warga/keluarga`, `admin/kk/pengajuan` | `boot()` citizen / admin roles admin,operator,ketua | Family card (KK) OCR scan, draft, submission, admin verification |
| Keputusan | 2 | `warga/keputusan` | citizen session (JSON) | Decision pop-up polling / mark seen |
| Penduduk | 4 | `admin/penduduk` | `guard_admin(perm)` | Population list, recap, export, family |
| Mutasi | 7 | `warga/mutasi`, `admin/mutasi` | citizen/admin guard | Resident mutation requests |
| Pengaduan | 7 | `warga/pengaduan`, `admin/pengaduan` | citizen/admin guard | Complaints & aspirations, private attachments |
| Kegiatan | 9 | `admin/program`, `notulen`, `ronda`, `warga/ronda` | guard | Work programs, meeting notes (versioned), patrol |
| Operasional | 10 | `admin/inventaris`, `kontak`, `belanja` | guard | Inventory & loans, important contacts, purchasing |
| Sosial | 5 | `admin/bansos` | guard | Social aid programs, recipients, export, proof |
| Keuangan | 3 | `admin/tunggakan`, `rekonsiliasi` | guard | Arrears aging, reminders, cash reconciliation |
| Sistem | 15 (+2 CLI) | `admin/arsip`, `serah-terima`, `akun`, `audit`, `backup`, `pengaturan/riwayat`, `field-tambahan`, `antrean-email` | guard / `is_cli()` | Archive, handover, accounts & role access, audit log, backup, settings history, custom fields, e-mail queue |
| Fitur | 3 | `admin/fitur` | guard | Feature & menu management |
| Langganan | 7 | `admin/langganan`, `platform/*` | `platform_guard()` (server-owned identity) | Subscription plans, platform owner console |
| Tampilan | 2 | `admin/tampilan`, `admin/theme-css` | guard_admin | Theme & appearance |

## Roles (application/config/rt_access.php)
Staff: `admin`, `ketua`, `sekretaris`, `bendahara`, `operator`, `keamanan`, `inventaris`, `sosial`, `auditor`. Plus resident (citizens table), platform owner & central admin (server config files, not in DB).

## Feature keys (features.php)
family_card, admin, resident_profile, resident_mutation, social_aid, letters, warga, complaints, live_chat, dues, dues_arrears, cash_report, cash_reconciliation, announcements, events, gallery, contacts, patrol, work_program, activity_notes, inventory, purchasing, documents, …

## Data stores
- MariaDB `sukamulya`: 70 tables (citizens, admins, kk_families/kk_members [NIK/No KK], service_requests*, dues/due_payments, transactions, complaints, resident_mutations, aid_*, assets, purchase_*, documents*, settings, subscription_*, audit/activity logs …). Several modules self-migrate (`Ops_model::ensure`, `Subscription_model::ensure`, `ensure_*_schema`) with `CREATE TABLE IF NOT EXISTS` / `ADD COLUMN` only.
- Files: public `uploads/` (images, random names, PHP blocked); private `uploads/letters`, `uploads/proofs` (deny all, served via controllers); `application/storage` (Private_storage, deny all).

## Integrations
SMTP via `Notifier` (password encrypted with CI Encryption), local OCR binaries in `tools/ocr-bin` (Poppler 25.07, proc_open with bypass_shell), CLI cron `php index.php sistem cli_backup|cli_notify`.
