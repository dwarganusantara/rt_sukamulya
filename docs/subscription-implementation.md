# Implementasi Fitur Berlangganan

**Data contoh terbaru:** Gratis (Basic) Rp0/Selamanya, Pro Rp99.000/365 hari, dan Enterprise Rp199.000/365 hari sudah diisi pada instalasi lokal sesuai permintaan pemilik. Pengisian tidak mengubah lisensi atau kategori akses tenant. [Manifest, seeder, dan hasil validasi](subscription-demo.md) menggantikan keterangan revisi historis bahwa paket berbayar hanya tersedia di database uji.

**Aturan terbaru:** Super Admin adalah **pemilik platform di luar akun pengurus**, login melalui `/platform/login`. Identitas server `chendy` menggunakan hash password pada konfigurasi privat, bukan tabel akun. Role/penambahan/bootstrap Super Admin di Akun & Hak Akses telah dihapus. Admin RT biasa tidak dapat mengontrol paket atau mengubah identitas pemilik. [Aturan terbaru dan hasil uji](super-admin-role.md) menggantikan seluruh uraian role lokal pada revisi historis di bawah.

## Editor detail paket

Tombol **Edit Paket** pada panel Detail Paket membuka `/platform/paket`. Bila belum memiliki sesi platform, pengguna diarahkan ke login Super Admin dan kembali ke editor setelah berhasil login. Akun Admin RT tidak mendapat kewenangan mengubah penawaran atau lisensi.

Editor menyediakan nama, harga, durasi, deskripsi, manfaat tambahan, pilihan modul existing, warna kartu hijau/oranye/ungu, urutan tampilan, dan status penawaran aktif. Ada pratinjau langsung sebelum menyimpan. Nama paket Gratis serta judul/deskripsi halaman dapat diubah pada formulir Tampilan; daftar fitur Gratis tetap mengikuti klasifikasi komersial existing, dan harga Gratis tetap nol. Teks manfaat tidak menggantikan entitlement modul.

Metadata tersimpan pada tabel additive `subscription_plan_details` (FK paket, deskripsi, manfaat JSON, warna, urutan, versi). Paket lama tetap kompatibel tanpa metadata. Metadata dipakai langsung oleh halaman RT, bukan hanya oleh editor. Perubahan tampilan halaman disimpan pada tiga key pengaturan khusus dengan audit platform.

Paket yang belum pernah dipakai lisensi dapat diedit pada ID yang sama. Jika sudah ada referensi lisensi, penyimpanan membuat revisi paket baru dan menghentikan penawaran revisi lama; catatan lisensi, harga lama, dan komposisi fitur lama tidak ditimpa. Semua edit mencatat before/after beserta ID hasil. Versi edit diperiksa dalam transaksi dengan row lock untuk menolak formulir kedaluwarsa.

File revisi: `application/controllers/Langganan.php`, `application/models/Subscription_model.php`, `application/config/routes.php`, `subscription_upgrade.sql`, `application/views/subscription/mine.php`, `platform.php`, `plan_editor.php`, `assets/subscription-page.css`, `subscription-editor.css`, dan `subscription-editor.js`. Tidak ada paket/harga produksi yang dibuat dari data mockup.

Validasi: `tests/subscription_editor_smoke.php` lolos untuk pembuatan/edit paket, metadata tersimpan, penolakan nilai tidak valid dan versi lama, pembatasan Admin RT, pembaruan tampilan RT, penyimpanan revisi berlisensi, pemeliharaan entitlement lama, audit, serta pengeditan judul/nama Gratis. `tests/subscription_smoke.php` dan `tests/subscription_page_smoke.php` tetap lolos. Browser memverifikasi alur Edit Paket → login platform → pratinjau → simpan → perubahan tampil di halaman RT, serta editor pada layar 390px tanpa overflow. Semua mutasi pengujian hanya pada database/storage disposable.

Pratinjau database uji: `tmp/family-preview/subscription-editable-main.png`, `subscription-package-editor.png`, dan `subscription-editor-mobile.png`.

## Revisi halaman sesuai mockup Paket & Langganan Saya

Halaman tenant kini memakai banner ilustrasi laptop/mahkota, tiga tab navigasi, empat kartu ringkasan, perbandingan paket dengan tema hijau/oranye/ungu, serta tabel status fitur dengan indikator hijau/abu-abu dan mahkota Premium. Layout desktop membagi detail paket dan status fitur menjadi dua panel; layar kecil menyesuaikan tanpa scroll horizontal halaman.

Perubahan revisi: `application/controllers/Langganan.php` menyiapkan paket aktif, tanggal, fitur sesuai role, jumlah aktif aktual, serta riwayat tenant; `application/views/subscription/mine.php` menggunakan komposisi baru; `assets/subscription-page.css`, `subscription-page.js`, dan `assets/img/admin-ops/subscription.svg` menyediakan tampilan, filter, dan dialog panduan. Label/sidebar langganan di `application/views/admin/header.php` menggunakan ikon mahkota.

Tab Paket menampilkan delapan fitur pertama dengan tautan Lihat Semua. Tab Aktivasi Fitur menampilkan seluruh fitur sesuai hak akses dengan pencarian dan filter status. Indikator status bukan sakelar lisensi yang bisa diubah Admin RT: klik fitur Premium membuka pemeriksaan dan popup langganan existing; pengurus berizin dapat membuka Kelola Fitur untuk pengaturan operasional. Tab Riwayat mengambil catatan lisensi tenant, termasuk tanggal dan alasan; keadaan kosong dijelaskan dengan jujur. Panduan dapat dibuka/ditutup dengan keyboard. Pemilihan paket mengarah ke WhatsApp dengan nama RT dan paket yang dipilih, tanpa mengirim pesan otomatis.

Paket diurutkan menurut harga aktual. Nama, harga, durasi, fitur paket, masa berlaku, dan ringkasan berasal dari database. Fitur inti yang memang selalu tersedia sesuai role ikut dihitung, bukan angka contoh 5/12. Saat belum ada lisensi aktif, fallback Gratis ditampilkan; saat belum ada penawaran berbayar, kartu kontak pengembang menggantikan penawaran rekaan. Harga 99.000/199.000 dan paket Pro/Enterprise pada screenshot revisi adalah fixture database disposable untuk memverifikasi komposisi tiga kartu, bukan konfigurasi produksi.

Validasi revisi: `tests/subscription_page_smoke.php` lolos untuk ketiga tab, ringkasan paket aktif/tanggal aktual, riwayat tersimpan, lisensi kedaluwarsa, serta penawaran kosong. Browser menguji lebar 1672, 1024, 768, 390, dan 320 piksel: tiga kartu paket dari fixture, kontak dinamis, dialog panduan, popup fitur Premium, pencarian/filter, dan navigasi riwayat lolos tanpa error JavaScript/overflow halaman. Suite `tests/subscription_smoke.php` juga lolos pada awal revisi. Semua mutasi dibatasi ke database/storage uji.

Pratinjau revisi: `tmp/family-preview/subscription-page-1672.png` dan `subscription-page-390.png`.

8 November 2026 — CodeIgniter 3, instalasi satu RT/RW existing.

## Membuka aplikasi

- Admin RT: `/admin/langganan` atau menu **Paket & Langganan Saya**.
- Platform: `/platform/login`, lalu `/platform/langganan`.
- Tidak ada akun/password platform default dan akun Admin RT tidak dipromosikan otomatis.
- Buat akun platform pertama dari PowerShell pada komputer server:

```powershell
Set-Location C:\xampp\htdocs\sukamulya
.\tools\setup-platform.ps1
```

Script meminta username terpisah dan password tersembunyi minimal 14 karakter. Password di-hash; variabel proses dibersihkan setelah provisioning. Tidak perlu memberikan password lewat chat atau memasukkannya ke source code.

Alternatif otomasi server: set `PLATFORM_ADMIN_USER` dan `PLATFORM_ADMIN_PASSWORD` pada environment proses lalu jalankan `C:\xampp\php\php.exe tools/create-platform-admin.php`. Jangan menyimpan password dalam file `.ps1`/`.php`, log, atau riwayat perintah.

## Alur operasional

1. Super Admin mengubah kategori komersial fitur existing menjadi Gratis, Premium, atau Nonaktif platform.
2. Super Admin membuat paket berisi nama, harga, durasi, dan pilihan fitur aktual. Perubahan komposisi/harga memakai versi paket baru; penawaran lama dapat dinonaktifkan tanpa menghapus lisensi existing.
3. Fitur Premium aktif secara operasional namun tanpa entitlement muncul berbadge Premium di sidebar/favorit dan akses cepat. Klik memeriksa status server terbaru dan membuka popup sesuai mockup.
4. Tombol WhatsApp membuka pesan berisi RT/RW serta nama fitur. Membuka tautan tidak mengirim pesan atau mengaktifkan lisensi. Website menuju `https://www.warganusa.com`; tombol paket menuju halaman paket tenant yang nyata.
5. Setelah konfirmasi pembayaran manual, Super Admin menyimpan lisensi dengan tanggal mulai/akhir serta alasan/referensi. Permintaan berikutnya langsung memakai status baru tanpa cache lintas request.
6. Perpanjangan, penangguhan, dan kedaluwarsa menggunakan catatan lisensi terbaru. Trial/CSR dapat diberikan melalui lisensi TRIAL atau override fitur bertanggal; override berlaku paling baru didahulukan. Override DENY dapat mencabut akses sampai tanggal akhir.
7. Semua perubahan komersial dicatat dengan operator platform, waktu, tenant, dan nilai sebelum/sesudah. Akun RT tidak memiliki endpoint aktivasi.

Status `ACTIVE`/`TRIAL` berlaku bila `starts_at <= waktu server < ends_at` dan paket memuat kode fitur tersebut. Masa berlaku tidak diisi otomatis tanpa batas. Modul Gratis tetap tunduk pada login, role, pengaturan operasional, dan kepemilikan data existing.

## Integrasi dan file berubah

| File | Perubahan |
|---|---|
| `subscription_upgrade.sql` | Delapan tabel tambahan: identitas platform, throttle login, katalog komersial, paket, relasi fitur paket, lisensi tenant, override, audit |
| `application/models/Subscription_model.php` | Instalasi skema idempoten, keputusan entitlement, data paket, transaksi perubahan dan audit |
| `application/controllers/Langganan.php` | Login platform terpisah dengan throttle/masa sesi satu jam, panel platform, halaman tenant baca-saja, endpoint status fitur |
| `application/libraries/Feature_gate.php` | Gerbang komersial di server, dependensi induk, status menu terlihat-terkunci, pemeriksaan alias controller CI3 termasuk URL langsung |
| `application/config/routes.php` | Route panel platform, paket tenant, dan status |
| `application/config/features.php` | Bukti keuangan publik ikut dipetakan ke gerbang fitur kas |
| `application/views/subscription/*.php` | Popup reusable, daftar paket tenant, login dan panel platform |
| `application/views/errors/subscription.php` | Penjelasan aman untuk akses URL langsung yang ditolak |
| `application/views/admin/header.php`, `footer.php`, `dashboard.php` | Menu langganan, badge Premium, dialog, dan kartu penawaran berdasarkan status aktual |
| `application/controllers/Admin.php`, `Site.php` | Penyaringan data fitur terkunci pada pencarian serta notifikasi agregat |
| `application/helpers/admin_alert_helper.php` | Antrean KK/surat terkunci tidak bocor lewat popup notifikasi |
| `application/libraries/Notifier.php`, `application/controllers/Sistem.php` | Antrean email modul existing memeriksa ulang lisensi, menunda pekerjaan tanpa menaikkan attempts; UI/CLI melaporkan penundaan |
| `assets/subscription.css`, `subscription.js` | Tema popup/navy/oranye, SVG lokal, responsif, pemeriksaan status terbaru, fokus dan penanganan kegagalan jaringan |
| `tools/create-platform-admin.php`, `setup-platform.ps1` | Provisioning lokal akun platform tanpa password default |
| `tests/subscription_smoke.php` | Uji integrasi pada database disposable saja |

Tidak ada tabel operasional lama yang dihapus atau diubah strukturnya. Tidak ada harga paket, statistik pelanggan, pembayaran, atau masa langganan produksi rekaan. Skema tambahan sudah dipasang lokal. Katalog produksi tetap Gratis sampai Super Admin memilih klasifikasi yang sesuai; contoh Premium dalam screenshot hanya menggunakan database uji.

## Kebijakan kompatibilitas dan keamanan

- Entitlement merupakan syarat tambahan. Izin controller existing tetap berlaku, lisensi tidak memberikan role baru.
- Pengecualian admin lama untuk fitur operasional nonaktif tidak melewati lisensi Premium atau fitur yang dinonaktifkan platform.
- Entitlement Premium tidak valid/tidak ditemukan/kedaluwarsa ditolak. Kegagalan server tidak memberikan akses.
- Tidak ada endpoint penghapusan data historis akibat kedaluwarsa. Kebijakan awal untuk Premium terkunci adalah tolak akses; mode baca historis khusus belum dibuka.
- Identitas tenant ditentukan server dari konfigurasi existing. Panel menolak parameter tenant asing. Ini bukan konversi database operasional menjadi multi-tenant: isolasi banyak RT membutuhkan proyek migrasi terpisah.
- Account platform disimpan terpisah dari `admins`, dicek aktif setiap permintaan, login dibatasi delapan kegagalan per IP dalam 15 menit, dan seluruh perubahan memakai CSRF CI3.
- Proses email mengenali asal fitur dari prefix dedupe existing: `kk`, `mutation`, `complaint`, dan `reminder`. Pekerjaan tertunda tetap pending, dijadwalkan ulang 15 menit, dan tidak diklaim terkirim. Notifikasi generik serta backup inti tetap memakai proses existing.
- Dokumen/berkas melalui endpoint aplikasi tunduk pada gerbang. Berkas gambar yang memang sudah dipublikasikan secara statis bukan mekanisme DRM; jangan mempublikasikan berkas privat ke direktori publik.
- MVP memakai konfirmasi pembayaran manual. Tidak ada payment gateway, penagihan otomatis, atau email/WhatsApp yang dikirim oleh pengujian ini.

## Pengujian

Semua mutasi uji dijalankan pada `sukamulya_ui_test` dan storage aplikasi salinan di `tmp/adminqa/application`. Tidak ada lisensi/paket berbayar atau data pelanggan uji yang dimasukkan ke database operasional.

- `tests/subscription_smoke.php`: Gratis, klasifikasi Premium, lisensi aktif/trial/belum mulai/kedaluwarsa/suspended, override GRANT/DENY/kedaluwarsa, role tanpa izin, admin RT tidak bisa aktivasi/platform, CSRF, manipulasi tenant, URL langsung/alias/default controller, POST, AJAX, ekspor, pekerjaan email tertunda, menu Premium dan nonaktif, serta audit.
- Browser Chromium: popup pada lebar 1672, 390, dan 320 piksel; tidak ada scroll horizontal atau error JavaScript; nama fitur dan pesan WhatsApp benar; Escape menutup dialog; tombol paket dan login platform bekerja.
- Lima suite regresi existing: `admin_ops_ui_smoke.php`, `admin_settings_ui_smoke.php`, `admin_audit_ui_smoke.php`, `admin_reconciliation_smoke.php`, `admin_system_final_smoke.php`. Lolos untuk formulir, unduhan arsip, penyimpanan akun, notulen, inventaris, belanja, field tambahan, antrean email tanpa SMTP, audit/CSV, rekonsiliasi, serah terima, rollback pengaturan, serta backup terenkripsi di storage uji.

Pratinjau: `tmp/family-preview/subscription-1672.png`, `subscription-390.png`, `subscription-320.png`, dan `subscription-platform.png`.

## Deployment dan rollback

Migrasi manual idempoten:

```powershell
C:\xampp\php\php.exe tools/create-platform-admin.php --migrate-only
```

Model juga menyiapkan skema tambahan sebelum dipakai. Pertahankan waktu server aplikasi konsisten dengan konfigurasi Asia/Jakarta. Simpan backup database sebelum deployment pada server lain.

Untuk membatalkan rollout, kembalikan source code versi sebelum implementasi menggunakan backup/version control. Pertahankan delapan tabel tambahan beserta audit untuk pemulihan; jangan DROP tabel atau menghapus data operasional. Pengembalian kode gerbang lama menghilangkan pembatasan komersial, sehingga hanya operator platform/server yang boleh melakukannya. Tidak ada tombol rollback lisensi oleh Admin RT.
