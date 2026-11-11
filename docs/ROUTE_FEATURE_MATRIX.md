# Route & Feature Matrix

Baseline audit: 29 October 2026 (local XAMPP). Runtime: PHP 8.2.12, CodeIgniter 3.1.13, MySQL through CI3 database layer. Authentication uses CI session; write forms use the configured CSRF token.

Status legend: `HTTP OK` means the route rendered or redirected correctly in an unauthenticated HTTP check. It does not mean its authenticated workflow passed. `AUTH REQUIRED` means the guard redirected to the relevant login page. Dynamic record routes were inspected in code but not invoked against real records.

## Public routes

| Route | Controller action | Main feature | Status | Risk / note |
|---|---|---|---|---|
| `/` | `Site::index` | Homepage, quick links, announcements, agenda | HTTP OK; visual 360/1440 | Quick links intentionally scroll inside their card below 1200px. |
| `/profil` | `Site::profile` | RT profile and statistics | HTTP OK | Dynamic content. |
| `/struktur` | `Site::structure` | Organization structure | HTTP OK | Verify large hierarchy with production-sized data. |
| `/pengumuman` | `Site::listing` | Announcement list, search and category filters | HTTP OK; visual 360 | Filters use local horizontal scrolling on small screens. |
| `/pengumuman/{id}` | `Site::announcement` | Announcement detail | Code inspected | Needs a valid record ID. |
| `/agenda` | `Site::listing` | Agenda list | HTTP OK | Dynamic content. |
| `/agenda/{id}` | `Site::agenda` | Agenda detail | Code inspected | Needs a valid record ID. |
| `/galeri` | `Site::listing` | Responsive gallery | HTTP OK | Lightbox/touch needs interactive test. |
| `/informasi` | `Site::information` | Environmental information | HTTP OK | Dynamic settings. |
| `/kontak` | `Site::contact` | Contact and configured channels | HTTP OK | External links not activated during audit. |
| `/keuangan` | `Site::finance` | Public cash report | HTTP OK | Data visibility depends on configured content. |
| `/bukti/{id}` | `Site::proof` | Transaction proof | Code inspected | Authorization/data exposure requires record fixture. |
| `/cari?q=` | `Site::search` | Site search | HTTP OK | Empty query tested only. |
| `/halaman/{slug}` | `Site::page` | CMS page | Code inspected | Needs an existing slug. |

## Citizen routes

| Route | Controller action | Main feature | Role | Status |
|---|---|---|---|---|
| `/warga/login` | `Citizen::login` | Login | Public | HTTP OK; visual 360 |
| `/warga/daftar` | `Citizen::register` | Registration | Public | HTTP OK; no form submission |
| `/warga/logout` | `Citizen::logout` | Logout | Citizen | Not invoked |
| `/warga` | `Citizen::index` | Citizen dashboard | Citizen | AUTH REQUIRED |
| `/warga/layanan` | `Citizen::services` | Letter-service catalog | Citizen | AUTH REQUIRED |
| `/warga/riwayat` | `Citizen::history` | Request history | Citizen | AUTH REQUIRED |
| `/warga/ajukan`, `/warga/draft/{id}` | `Citizen::request` | Create/edit draft request and upload | Citizen | Code inspected; write not invoked |
| `/warga/pengajuan/{id}` | `Citizen::request_detail` | Request detail | Citizen | Code inspected |
| `/warga/pengajuan/{id}/hapus` | `Citizen::delete_request` | Delete owned draft | Citizen | Destructive; not invoked |
| `/warga/lampiran/{id}` | `Citizen::attachment` | Owned attachment | Citizen | Code inspected |
| `/warga/dokumen/{id}` | `Citizen::document` | Approved document | Citizen | Code inspected |
| `/warga/surat/{id}` | `Citizen::letter` | Printable letter | Citizen | Code inspected |
| `/warga/aset-surat/{path}` | `Citizen::letter_asset` | Controlled letter asset | Citizen | Code inspected |
| `/warga/iuran` | `Citizen::dues` | Dues and payment history | Citizen | AUTH REQUIRED |
| `/warga/iuran/{id}/bayar` | `Citizen::pay_due` | Upload payment proof | Citizen | Write not invoked |
| `/warga/bukti-iuran/{id}` | `Citizen::due_proof` | Owned proof | Citizen | Code inspected |
| `/warga/profil` | `Citizen::profile` | Profile | Citizen | AUTH REQUIRED |
| `/warga/password` | `Citizen::password` | Change password | Citizen | AUTH REQUIRED; submission not invoked |
| `/warga/chat` | `Citizen::chat` | Chat UI | Citizen | AUTH REQUIRED |
| `/warga/chat/pesan`, `/warga/chat/kirim` | chat endpoints | Poll/send messages | Citizen | Code inspected; send not invoked |

## Admin routes

All controller methods below use `Admin::guard()` except login/logout. Roles found in code/navigation: `admin`, `operator`, `ketua`, and `bendahara`; each action's controller guard remains authoritative.

| Route / action group | Feature | Status |
|---|---|---|
| `/admin/login`, `/admin/logout` | Admin authentication | Login HTTP OK + visual 360; logout not invoked |
| `/admin` | Dashboard | AUTH REQUIRED |
| `/admin/pages`, `/admin/page[...]`, `/admin/delete_page[...]` | CMS pages CRUD | Guard/code inspected; writes not invoked |
| `/admin/posts/{type}`, `/admin/post/{type}[/{id}]` | Announcements, agenda, gallery | AUTH REQUIRED; writes not invoked |
| `/admin/citizens`, `/admin/citizen[/{id}]` | Citizen master data | Guard/code inspected |
| `/admin/officials`, `/admin/official[/{id}]` | Officials | Guard/code inspected |
| `/admin/requests`, `/admin/request_detail/{id}` | Citizen letter review | AUTH REQUIRED |
| `/admin/letter_types`, `/admin/letter_type[/{id}]`, `/admin/letter_settings` | Letter configuration | Guard/code inspected |
| `/admin/dues`, `/admin/due[...]`, `/admin/billing_campaign`, `/admin/review_due_payment[...]` | Dues and payment review | AUTH REQUIRED |
| `/admin/transactions`, transaction actions | Cash transactions and review | AUTH REQUIRED |
| `/admin/settings`, `/admin/quick_links`, `/admin/quick_link[...]` | Site/menu settings | Guard/code inspected |
| `/admin/chat` (redirect ke dashboard), `/admin/chat/inbox`, message endpoints | Admin live chat — widget melayang multi-chat di semua halaman admin | AUTH REQUIRED; send not invoked |
| `/admin/notifications`, `/admin/search`, `/admin/password` | Notifications, search, account | Guard/code inspected |

## Shared responsive components

- Public/citizen header and footer: `application/views/site/header.php`, `footer.php`.
- Citizen service sidebar: `application/views/citizen/sidebar.php`.
- Admin shell/sidebar/header: `application/views/admin/header.php`.
- Shared responsive override: `assets/responsive-app.css`.
- Viewport metadata exists in the public/citizen header, admin header/login, and standalone printable letter.

