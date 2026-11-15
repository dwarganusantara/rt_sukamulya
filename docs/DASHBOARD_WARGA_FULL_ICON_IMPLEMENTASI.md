# Dashboard Warga Full Icon

Dashboard warga sekarang memakai satu banner dan grid layanan berbasis konfigurasi. Identitas RT, autentikasi, profil, notifikasi, logout, URL layanan, pengajuan surat tanpa sidebar, dan footer yang hanya tampil di beranda publik dipertahankan.

## Penggunaan

- Warga: buka `/warga`. Kartu hanya tampil jika fitur aktif, akses warga diizinkan oleh Feature_gate, dan `show_on_dashboard` aktif.
- Admin: Pengaturan → Manajemen Fitur & Menu → **Dashboard Warga** (`admin/fitur?tab=dashboard`). Ubah label, ikon, warna, kategori, visibilitas, dan urutan. Urutan dapat diseret atau memakai tombol naik/turun. Simpan untuk menerapkan; Batalkan memuat ulang nilai tersimpan.
- Status aktif dan akses warga/pengurus tetap dikelola pada tab Daftar Fitur. Menyembunyikan kartu tidak menonaktifkan layanan.
- Pratinjau desktop/ponsel memakai komponen kartu yang sama, berubah langsung sebelum disimpan, dan tidak menampilkan data pribadi warga.
- Banner: Admin → Gambar Halaman → Dashboard Warga. Gunakan ilustrasi tanpa tulisan. Nama login serta identitas RT dirender sebagai teks HTML.
- Reset Default memerlukan konfirmasi. Perubahan dicatat di Riwayat Perubahan bersama pengaturan fitur existing.

## Penyimpanan dan kompatibilitas

Tidak ada migrasi DDL: audit menemukan tabel `settings`, `app_features`, `tenant_features`, serta `tenant_feature_audit` sudah tersedia. Konfigurasi presentasi disimpan sebagai JSON pada `settings.setting_key = dashboard_menu_tenant_<id>`. Banner memakai `citizen_dashboard_page_image`. Model memakai tenant dari Feature_gate/server, bukan dari POST. Aplikasi saat ini satu RT dengan tenant default sesuai `config/features.php`; pengembangan multi-tenant penuh tetap membutuhkan pemetaan akun ke tenant di seluruh aplikasi.

Defaults berasal dari registry route yang dikelola server. Route tidak dapat diubah melalui formulir. Ikon, warna, kategori, label, urutan dan kelengkapan registry divalidasi server. Penyimpanan konfigurasi dan audit berlangsung dalam transaksi; versi konfigurasi diperiksa dengan penguncian untuk menolak konflik penyimpanan. Izin admin `fitur.manage`, token CSRF, serta hook penegakan akses URL/AJAX/unduhan existing tetap digunakan.

Tidak ada perubahan data warga, surat, pembayaran, atau struktur tabel. Tidak ada dependensi frontend baru. Kompatibel dengan pola CodeIgniter 3 dan PHP existing (`array_key_first`/operator modern sudah dipakai aplikasi).

## Pemetaan layanan

| Kartu | Route existing |
|---|---|
| Profil Saya | warga/profil |
| Data Keluarga | warga/keluarga |
| Pengajuan Surat | warga/layanan |
| Riwayat Pengajuan | warga/riwayat |
| Iuran Warga | warga/iuran |
| Laporan Kas RT | keuangan |
| Pengumuman | pengumuman |
| Agenda Kegiatan | agenda |
| Galeri Kegiatan | galeri |
| Jadwal Ronda | warga/ronda |
| Pengaduan & Aspirasi | warga/pengaduan |
| Chat Pengurus | warga/chat |
| Mutasi Data | warga/mutasi |
| Kontak Penting | warga/kontak-penting |
| Pengaturan Akun | warga/password |
| Beranda (default tersembunyi) | warga |

Registry: `application/config/dashboard.php`; model: `application/models/Dashboard_menu_model.php`. Dashboard dan kartu: `application/views/citizen/dashboard.php`, `dashboard_tiles.php`. Integrasi: controller Citizen, Fitur, Admin, config routes, header site, halaman fitur admin, dan partial `admin/ops/dashboard_settings.php`. CSS/JS: `assets/dashboard-icons.css`, `dashboard-settings.css`, `dashboard-settings.js`.

## Hasil QA

- PHP lint lolos untuk seluruh PHP yang ditambah/diubah.
- `tests/dashboard_menu_model_test.php`: validasi label/ikon/warna/kategori/urutan, penolakan key tidak dikenal, filtering akses, penyimpanan dan reload, visibilitas terpisah dari akses, urutan, konflik versi, isolasi key tenant, audit, reset, dan empty state lolos. Semua penulisan uji berada dalam transaksi yang di-rollback; konfigurasi dan jumlah audit awal diperiksa kembali.
- `tests/dashboard_icons_smoke.php`: dashboard warga dan editor admin dengan sesi QA sementara berhasil; sidebar dan widget lama tidak dirender; header/logout tetap ada; pengajuan surat tetap memakai layout fokus; CSRF tanpa token ditolak HTTP 403. Sesi uji dibersihkan. Snapshot disimpan di direktori temporer dengan nama warga disamarkan.
- `tests/dashboard_visual_qa.ps1`: Chrome headless dengan emulasi viewport 320, 375, 390, 768, 1440 px. Tidak ada scroll horizontal. Grid 2 kolom di ponsel, 4 pada 768 px, dan 6 pada desktop.
- Interaksi editor diuji pada 1440 dan 390 px: label preview, sembunyikan kartu, naik/turun urutan, preview ponsel, serta empty state lolos.
- Screenshot dashboard dan admin tersedia di `docs/dashboard-qa/`. File `admin-top-*` menampilkan viewport atas; `admin-preview-*` menampilkan pratinjau.
- Uji ini tidak mengirim surat, mengunggah dokumen warga, atau melakukan pembayaran. Pengujian transaksi bisnis penuh dan deployment hosting belum dilakukan. Pembatasan akses fitur menggunakan hook existing; pemetaan role bisnis existing tidak diubah.

Jalankan tes PHP dari root proyek dengan `C:\xampp\php\php.exe tests\dashboard_menu_model_test.php` dan `C:\xampp\php\php.exe tests\dashboard_icons_smoke.php`. Smoke test mengasumsikan DB lokal `sukamulya`, akun DB lokal root, dan minimal satu akun admin/warga aktif. Jangan jalankan skrip QA lokal pada hosting. Jalankan visual QA hanya setelah smoke menghasilkan snapshot.

## Deployment dan rollback

1. Backup source serta database hosting. Terapkan file PHP/CSS/JS yang berubah; tidak ada SQL migrasi wajib. Pastikan tabel fitur existing sudah terpasang sesuai versi aplikasi.
2. Buka Manajemen Fitur & Menu, periksa status layanan, pratinjau, lalu simpan konfigurasi. Uji akun warga dan refresh browser.
3. Untuk membatalkan konfigurasi gunakan Reset ke Default. Untuk rollback fitur sepenuhnya, pulihkan source dari backup sebelum perubahan; baris JSON dan audit dapat tetap tersimpan karena tidak dibaca source lama. Jangan menghapus tabel atau data layanan. Pulihkan banner sebelumnya melalui Gambar Halaman bila diperlukan.