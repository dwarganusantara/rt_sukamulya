# Data contoh paket berlangganan

Atas permintaan pemilik, tiga penawaran contoh berikut telah diisi pada database instalasi lokal. Data dapat diedit dari panel pemilik platform.

| Paket | Harga | Durasi | Cakupan |
|---|---:|---|---|
| Gratis (Basic) | Rp0 | Selamanya | Dashboard dan Data Warga sebagai fitur inti; Pengajuan Surat, Pengumuman, dan Agenda dari modul existing |
| Pro | Rp99.000 | 365 hari | Basic + KK, Data Penduduk, Mutasi, Bantuan Sosial, Kas/Laporan & Ekspor sesuai hak akses |
| Enterprise | Rp199.000 | 365 hari | Pro + modul operasional, pengaduan, chat, iuran, rekonsiliasi, galeri, kontak, ronda, program, notulen, inventaris, belanja, dan arsip |

Manfaat tambahan Enterprise mencantumkan Manajemen Pengurus, Audit Aktivitas, Backup Data **manual**, dan prioritas dukungan sebagai contoh manfaat penawaran. Seeder tidak membuat scheduler backup, gateway pembayaran, atau fitur yang belum tersedia. Fitur inti dan izin peran tetap mengikuti sistem existing.

## Batas data contoh

- Hanya penawaran paket, relasi fitur, metadata tampilan, marker seeder, dan audit pengisian yang ditambahkan.
- Tidak mengubah katalog Gratis/Premium/Nonaktif, sakelar operasional, lisensi tenant existing, override, pembayaran, akun, atau data warga.
- Tidak menciptakan 12 tenant/5 pelanggan seperti angka ilustrasi gambar; aplikasi saat ini memakai satu instalasi tenant existing.
- Basic disimpan sebagai harga nol dan durasi nol, ditampilkan “Selamanya”, dan tersedia sebagai fallback Gratis. Paket ini tidak menghasilkan lisensi berbayar dengan tanggal kedaluwarsa rekaan.
- Halaman tenant/editor menampilkan satu kartu Basic, bukan kartu fallback ditambah duplikat data Basic.

## Pengisian ulang dan pengeditan

```powershell
C:\xampp\php\php.exe tools/seed-subscription-demo.php --dry-run
C:\xampp\php\php.exe tools/seed-subscription-demo.php
```

Seeder CLI menggunakan manifest `application/config/subscription_demo.php`, transaksi, kode fitur yang telah terdaftar, dan marker `subscription_demo_offers_v1`. Pengisian ulang tidak menggandakan paket atau menimpa nama/harga/manfaat yang telah diedit. Penawaran aktif yang cocok dapat digunakan ulang. Paket dipilih melalui editor di `/platform/paket`; status lisensi tenant tetap diaktifkan secara terpisah oleh pemilik platform setelah proses yang sesuai.

Durasi nol hanya diizinkan untuk harga nol. Validasi server menolak paket berbayar berdurasi nol. UI menampilkan Selamanya untuk Basic dan mempertahankan formulir editnya. Penawaran Basic dikeluarkan dari pilihan aktivasi lisensi berjangka.

## File dan validasi

Perubahan: manifest dan seeder baru, `Subscription_model` untuk penanda Basic, `Langganan` untuk validasi/penyajian Basic, view `mine`, `platform`, `plan_editor`, dan pratinjau editor.

`tests/subscription_demo_smoke.php` memverifikasi harga, durasi, manfaat, pemetaan modul, pengisian ulang tanpa duplikat, pemeliharaan edit, serta tidak berubahnya lisensi/override/katalog. Suite langganan, editor, dan halaman paket juga lolos. Browser memverifikasi tiga paket nyata di panel XAMPP, harga, Basic Selamanya, tiga kartu tanpa duplikat, dan editor mobile. Mutasi pengujian dilakukan pada database/storage disposable; pengisian tiga penawaran contoh pada instalasi lokal adalah aksi yang diminta pemilik.

Pratinjau data lokal: `tmp/family-preview/platform-demo-offers.png` dan `demo-package-cards.png`.
