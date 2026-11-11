# RT/RW Digital — Implementasi 16 Area Fitur

Tanggal: 7 November 2026 · Skema: `rt16-2` · Acuan: `RT_RW_Digital_Bisnis_Proses_16_Fitur.md`

Prinsip yang dipakai: **existing-first & additive**. Tidak ada tabel/kolom lama yang dihapus atau diubah tipenya, tidak ada route aktif yang diganti, dan tampilan halaman lama tidak didesain ulang. Modul baru memakai shell admin/portal warga yang sama (sidebar, header, kartu, palet navy–oranye).

## 1. Matriks gap & status

| # | Modul | Sebelum | Sesudah | Lokasi |
|---|---|---|---|---|
| 01 | Dashboard monitoring | Statistik dasar | + kartu Monitoring Operasional per peran (KK, mutasi, pengaduan, tunggakan, saldo kas, tindak lanjut & peminjaman terlambat, belanja, ronda) + peringatan backup | `views/admin/dashboard.php`, `Ops_model::monitoring()` |
| 02 | Mutasi warga | Belum ada | Lengkap: masuk/lahir, pindah, meninggal, koreksi data, ubah alamat; review → setuju/revisi/tolak; penerapan atomik ke KK master; snapshot sebelum/sesudah | `controllers/Mutasi.php` |
| 03 | Pengaduan & aspirasi | Hanya jenis surat "aduan" | Lengkap: tiket bernomor, prioritas, PIC, alur status tervalidasi, catatan internal, lampiran privat, konfirmasi/buka kembali, rating, batas 5/hari | `controllers/Pengaduan.php` |
| 04 | Data KK / OCR | Sudah ada | Integrasi: anggota pindah/meninggal tidak ikut auto-fill surat; status anggota tampil di Data Keluarga. **+ Data Penduduk** (admin): daftar anggota/keluarga terverifikasi dengan filter status, domisili, L/P, kelompok usia; detail KK + riwayat mutasi + peringatan "data perlu dilengkapi"; rekap agregat (usia×L/P, domisili, pekerjaan, pendidikan, mutasi 12 bulan); ekspor CSV ber-masking, ekspor NIK lengkap khusus admin/ketua dengan alasan + audit. Read-only; perubahan tetap lewat Verifikasi KK/Mutasi | `Kk_model::family()`, `Citizen::request`, `controllers/Penduduk.php` |
| 05 | Iuran & kas | Sudah ada (verifikasi terkunci, posting kas) | + Aging tunggakan (4 kelompok umur), ekspor CSV, pengingat in-app (maks 1×/3 hari), rekonsiliasi saldo bank/fisik, pemeriksaan integritas iuran↔kas | `controllers/Keuangan.php` |
| 06 | Pengaturan dinamis | Identitas, gambar, menu cepat | + Riwayat & pemulihan pengaturan, + manajemen akun & role | `Content_model::save_settings`, `Sistem::pengaturan_riwayat`, `Sistem::akun` |
| 07 | Program kerja | Belum ada | Lengkap: draf → persetujuan → aktif/tunda → selesai (evaluasi wajib); milestone berbobot; tautan agenda, notulen, belanja | `Kegiatan::program_*` |
| 08 | Catatan & notulen | Belum ada | Lengkap: versi tersimpan di setiap revisi, pengesahan & kunci, tindak lanjut ber-PIC/tenggat, daftar terlambat | `Kegiatan::notulen_*` |
| 09 | Ronda & absensi | Belum ada | Lengkap: jadwal 2 minggu, deteksi bentrok, absensi manual wajib alasan (diaudit), check-in/out mandiri warga tervalidasi server, laporan kejadian | `Kegiatan::ronda_*` |
| 10 | Aset & inventaris | Belum ada | Lengkap: aset vs habis pakai, mutasi stok, peminjaman (stok dikunci saat disetujui), pengembalian baik/rusak/hilang, usulan & persetujuan hapus | `Operasional::aset_*`, `pinjam` |
| 11 | Kontak penting | Belum ada | Lengkap: publik vs internal, darurat, tanggal verifikasi, tautan `tel:` | `Operasional::kontak*` |
| 12 | Belanja & penerimaan | Belum ada | Lengkap: permintaan multi-item, pemohon tidak boleh menyetujui sendiri, penerimaan parsial, barang inventaris masuk stok setelah diterima, posting kas sekali, donasi tanpa kas | `Operasional::belanja_*` |
| 13 | Bantuan sosial | Belum ada | Lengkap: program & kuota, usulan dari KK terverifikasi, verifikasi → persetujuan → penyaluran sekali, bukti privat, rekap agregat | `controllers/Sosial.php` |
| 14 | Arsip digital | Belum ada | Lengkap: penyimpanan privat, SHA-256, versi, kerahasiaan (rahasia hanya ketua/admin), unduh via controller + audit, soft delete, retensi | `Sistem::arsip*` |
| 15 | Serah terima | Belum ada | Lengkap: snapshot agregat ber-hash, checklist, pengecualian, persetujuan pihak lama & baru, final terkunci; pencabutan akun | `Sistem::serah_*` |
| 16 | Backup & audit | `activity_logs` minimal | Audit: IP + detail tersanitasi (NIK dimasking), viewer + filter + ekspor; Backup: dump DB terenkripsi AES-256-GCM, checksum, verifikasi, retensi 14, CLI terjadwal | `Sistem::audit_log`, `Sistem::backup` |

## 2. Rute baru

Admin: `admin/penduduk` (+`/rekap`, `/ekspor`, `/keluarga/{id}`), `admin/mutasi`, `admin/pengaduan`, `admin/bansos`, `admin/tunggakan`, `admin/rekonsiliasi`, `admin/program`, `admin/notulen`, `admin/ronda`, `admin/inventaris` (+`/pinjam`), `admin/belanja`, `admin/kontak`, `admin/arsip`, `admin/akun`, `admin/serah-terima`, `admin/audit`, `admin/backup`, `admin/pengaturan/riwayat`.

Warga: `warga/mutasi`, `warga/pengaduan`, `warga/ronda`, `warga/kontak-penting`.

Daftar lengkap di `application/config/routes.php` (blok "RT/RW Digital").

## 3. Hak akses

Matriks `module.action` ada di `application/config/rt_access.php` dan diperiksa di backend setiap request (`Ops_Controller::need()`); sidebar memakai matriks yang sama. Role baru: `sekretaris`, `keamanan`, `inventaris`, `sosial`, `auditor` (kelola di **Akun & Hak Akses**). Menu lama tetap memakai role lama (admin/ketua/operator/bendahara). Scope tambahan: petugas keamanan hanya melihat tiket keamanan/darurat atau yang ditugaskan kepadanya; dokumen rahasia hanya admin/ketua; warga hanya data miliknya (dicek di query).

Akun yang dinonaktifkan tidak bisa login dan sesinya berakhir pada request berikutnya (`Admin::guard`, `Ops_Controller::guard_admin`, `Keluarga::boot`).

## 4. Database

- Migrasi: `rt_digital_16_upgrade.sql` (idempoten, hanya `CREATE TABLE IF NOT EXISTS` / `ADD COLUMN IF NOT EXISTS`), atau otomatis oleh `Ops_model::ensure()` saat modul baru pertama dibuka.
- 28 tabel baru; kolom tambahan: `activity_logs.ip_address/details`, `admins.is_active/full_name/created_at`, `kk_members.status/status_changed_at/domicile_status/resident_since`; enum `admins.role` diperluas (superset).
- Uang `DECIMAL(15,2)`, NIK/KK `VARCHAR`, InnoDB `utf8mb4`.
- Pencegahan duplikasi: `uq_purchase_tx`, `uq_aid_recipient`, `uq_patrol_officer`, `uq_doc_version`, `uq_note_version`, plus `SELECT … FOR UPDATE` pada setiap transisi status.

## 5. Berkas privat

`application/storage/{complaints,purchases,aid,documents,backups}` — diblokir web server (`application/.htaccess` + `.htaccess` sendiri; uji URL langsung = 403). Nama acak, MIME diperiksa dari isi berkas. Pastikan folder ini **ikut dibackup** dan dapat ditulis PHP di hosting.

## 6. Backup & restore

Backup otomatis (Windows Task Scheduler / cron), harian 02.00:

```
php /path/ke/sukamulya/index.php sistem cli_backup
php /path/ke/sukamulya/index.php sistem cli_backup files   # butuh ekstensi PHP zip
```

Kunci enkripsi diturunkan dari `encryption_key` di `config.php` — **simpan salinan kunci tersebut di tempat aman terpisah**; tanpa kunci, backup tidak bisa dibuka.

Prosedur restore (wajib persetujuan Ketua RT):
1. Jalankan backup terbaru dari kondisi saat ini (untuk rollback).
2. Dekripsi berkas `db-*.sql.gz.enc` (format: `RTBK1` + IV 12 byte + tag 16 byte + ciphertext AES-256-GCM dari gzip SQL; kunci = `sha256(encryption_key . '|rt-backup')`).
3. Impor ke **database baru/staging** lebih dulu dan periksa jumlah data.
4. Setelah valid, aktifkan mode pemeliharaan, impor ke produksi, uji login & menu utama, catat di audit.

Restore sengaja tidak tersedia sebagai tombol di aplikasi.

## 7. Hasil pengujian (lokal, XAMPP PHP 8.2 / MariaDB 10.4)

| UAT | Hasil |
|---|---|
| 02/03 Surat dari KK; anggota meninggal tidak ikut auto-fill, master tidak berubah | Lulus |
| 03 Mutasi disetujui → master berubah sekali; persetujuan ulang ditolak | Lulus |
| 04 Pengaduan: assign, resolve, konfirmasi warga; transisi ilegal ditolak | Lulus |
| 06 Posting kas belanja dua kali → ditolak (1 jurnal) | Lulus |
| 07 Program ↔ notulen ↔ agenda tertaut; notulen terkunci setelah disahkan, versi tersimpan | Lulus |
| 08 Dua persetujuan pinjam bersaing pada stok 3 (2+2) → kedua ditolak, stok tidak minus | Lulus |
| 09 Belanja: pemohon tidak bisa menyetujui sendiri; penerimaan parsial; aset muncul setelah diterima | Lulus |
| 10 Bansos disalurkan dua kali → ditolak; kuota; duplikasi calon ditolak | Lulus |
| 11 Dokumen rahasia: sekretaris 403/404, URL langsung ke berkas 403 | Lulus |
| 12 Akun dicabut → sesi aktif langsung keluar; login ditolak | Lulus |
| 13 Backup terenkripsi → dekripsi → impor ke DB terpisah: 51 tabel & jumlah baris identik | Lulus |
| 14 Warga membuka mutasi/pengaduan/lampiran milik warga lain → 404; check-in jadwal orang lain ditolak | Lulus |
| Data Penduduk (`tests/penduduk_http_smoke.php`, 38 cek): filter, masking NIK/KK di daftar/detail/ekspor, NIK yang diketik di pencarian dibuang dari filter, ekspor lengkap wajib alasan + tercatat di audit, sekretaris/auditor/bendahara/keamanan dibatasi di backend (403), regresi halaman kependudukan lama | Lulus |
| 16 Seluruh halaman lama (publik 10, warga 9, admin 21) tetap 200 tanpa error PHP/DB | Lulus |
| 15 Tampilan 360 px & 1280 px (`tests/responsive_360.php`, Chrome headless) | 46 halaman × 2 viewport tanpa scroll horizontal halaman & tanpa error PHP; screenshot 360 px tersimpan | Lulus |
| E2E seluruh alur (`tests/e2e_rt16.php`): 133 cek + crawl ±970 permintaan oleh 9 peran admin, 2 warga, dan anonim | Lulus |

## 7a. Putaran revisi & perbaikan bug (7 Nov 2026, skema `rt16-2`)

**Bug yang ditemukan dan diperbaiki**

| Bug | Dampak | Perbaikan |
|---|---|---|
| Folder `application/views/errors/` tidak ada | Semua `show_404()`/`show_error()` (termasuk 403 "Akses Ditolak") dan error DB tampil sebagai **halaman kosong** | Template error CI3 (html + cli) dengan tampilan rapi berbahasa Indonesia, tanpa detail teknis di produksi |
| Ikon `key` & `gear` tidak terdefinisi | Warning PHP di halaman *Akun & Hak Akses* dan *Riwayat Pengaturan* | Ikon ditambahkan; `official_svg()` kini aman untuk nama ikon tak dikenal |
| Teks rusak `â€”` di Mutasi | Tampil di daftar pilihan keluarga ("Tanpa akun â€” KK…") | Encoding diperbaiki |
| Keputusan Verifikasi KK tidak diaudit | Melanggar Bagian 10.7 (approve/reject wajib tercatat) | `rt_audit('kk_approved'…)` di setiap keputusan |
| Audit pengaduan mencatat status yang tidak berubah | Jejak audit menyesatkan | Status aktual dikembalikan dari transaksi |
| Penolakan peminjaman cukup hak tulis | Petugas inventaris bisa menolak (keputusan persetujuan) | Tolak = `aset.approve`; batal tetap hak tulis |
| `citizen_id` peminjam tidak divalidasi; kode aset dari belanja bisa bentrok | Data yatim / error unik `uq_asset_code` | Validasi akun warga; kode aset diberi akhiran unik |
| Backup berkas gagal ikut menghapus backup DB yang sudah jadi; ZIP besar bisa habis memori tanpa pesan | Kehilangan backup DB | Bagian berkas dicatat gagal terpisah, DB tetap tersimpan; batas memori dinaikkan khusus proses ini; batas 200 MB dengan pesan jelas |
| Overflow horizontal: tabel izin di *Akun* (962 px di layar 360), Iuran warga & popup kabar warga, input file tersembunyi di *Pengaturan*/*Form Warga* (desktop) | Halaman bergeser horizontal | `.ops-table-wrap{position:relative}`, grid kartu tabel mobile `minmax(0,1fr)`, teks keadaan kosong boleh membungkus, input file dikurung di labelnya |
| Mode development tidak bisa dipakai di PHP 8.2 | Deprecation core CI 3.1.13 tercetak sebelum header → redirect gagal | Cabang `development` di `index.php` menyembunyikan E_DEPRECATED (produksi tidak berubah) |

**Fitur yang sebelumnya tertunda, kini dibangun (bawaan nonaktif / tampilan identik sampai diubah pengurus)**

| Fitur | Ringkas | Lokasi |
|---|---|---|
| Tema warna dinamis (Modul 06) | 168 warna brand di 22 CSS menjadi `var(--theme-accent,#f0501a)` / `var(--theme-navy,#082654)` — fallback = warna asli. Pengaturan → *Tema & Fitur*: input warna, pratinjau, cek kontras (aksen ≥ 3:1, utama ≥ 4.5:1), kembalikan ke bawaan, tercatat di riwayat | `helpers/theme_helper.php`, `Admin::settings` |
| Field tambahan warga (Modul 06) | Teks/angka/tanggal/pilihan/centang; wajib/opsional; bisa khusus pengurus; label data sensitif (NIK, agama, kesehatan, penghasilan, dll.) ditolak; nilai tetap tersimpan saat field dinonaktifkan | `libraries/Custom_fields.php`, `admin/field-tambahan`, Edit Profil warga, Form Akun Warga |
| Notifikasi email (Bagian 11) | Outbox `notification_outbox` dengan `dedupe_key` unik (tidak ganda saat retry), klaim baris sebelum kirim, retry bertahap maks. 5×; hanya ke warga yang mengisi email **dan** menyetujui; isi tanpa NIK/KK. Event: keputusan KK, mutasi, status pengaduan, pengingat iuran | `libraries/Notifier.php`, `config/notifications.php`, `admin/antrean-email`, `php index.php sistem cli_notify` |
| Pengaduan identitas disembunyikan (Modul 03) | Warga tetap login (batas 5/hari), nama/telepon disembunyikan dari petugas di daftar, detail, timeline, dan pencarian nama. Ketua/Admin dapat membuka dengan alasan → tercatat `reveal_identity`, tidak disimpan di sesi | `Pengaduan.php`, izin `pengaduan.reveal` |
| Ekstensi `zip` | Diaktifkan di `php.ini` XAMPP (cadangan: `php.ini.bak-sebelum-zip`); backup berkas terenkripsi terverifikasi dapat didekripsi & dibuka | — |

Migrasi `rt16-2` (otomatis via `Ops_model::ensure()` atau `rt_digital_16_upgrade.sql`, diuji dua kali pada salinan data asli tanpa error dan tanpa kehilangan data): tabel `custom_field_definitions`, `citizen_field_values`, `notification_outbox`; kolom `citizens.notify_email`, `complaints.is_anonymous`. Izin baru: `pengaturan.fields`, `notifikasi.manage`, `pengaduan.reveal`. Rute baru: `admin/field-tambahan`, `admin/antrean-email`.

**Cara menjalankan pengujian E2E** (tidak menyentuh data asli): salin DB ke `sukamulya_e2e`, tambahkan sementara `SetEnv CI_ENV development` dan `SetEnv DB_NAME sukamulya_e2e` di `.htaccess`, jalankan `php tests/e2e_rt16.php` lalu `php tests/responsive_360.php <folder-screenshot>`, kemudian hapus baris `SetEnv`, berkas uji di `application/storage/*`, dan database salinan.

## 8. Isu terbuka / keputusan pengurus

- **Restart Apache** agar ekstensi `zip` juga aktif untuk tombol *Backup* di web (CLI terjadwal sudah aktif).
- **SMTP** belum dikonfigurasi: isi environment `SMTP_HOST`, `SMTP_USER`, `SMTP_PASS`, `MAIL_FROM` di server, lalu aktifkan *Kirim salinan notifikasi lewat email* di Pengaturan → Tema & Fitur. Jadwalkan `php index.php sistem cli_notify` (mis. tiap 10 menit).
- **WhatsApp** tidak diimplementasikan: membutuhkan integrasi resmi (WhatsApp Business API) dan persetujuan pengguna.
- **Pengaduan identitas disembunyikan** dan **email** bawaannya nonaktif — aktifkan setelah disepakati rapat pengurus.
- **Offsite**: backup tersimpan di server yang sama; unduh berkala ke media terpisah. Backup berkas dari aplikasi dibatasi 200 MB.
- Data Penduduk sengaja **tidak** mendukung pencarian berdasarkan NIK/nomor KK, karena filter memakai GET dan nomor utuh akan masuk log akses server. Bila diperlukan, buat pencarian NIK via POST dengan audit.
- Kotak pencarian global di header admin (existing) memantulkan parameter `q` dari URL ke semua halaman; tidak berbahaya (ter-escape), tetapi perlu diketahui.
- Kelurahan/RW multi-tenant tidak diterapkan (aplikasi saat ini satu RT).
- Kepatuhan UU PDP perlu ditinjau pengurus sebelum produksi (pemberitahuan pemrosesan data, retensi).

## 9. Rollback

Semua perubahan additive. Untuk menonaktifkan modul baru tanpa menyentuh data: hapus blok route "RT/RW Digital" di `routes.php` dan item menu baru di `views/admin/header.php` & `views/citizen/sidebar.php`. Perubahan backend kecil pada berkas lama (dapat dikembalikan satu per satu): `Admin.php` (cek akun aktif, audit via `rt_audit`, data monitoring), `Keluarga.php` (cek akun aktif, KK manual hanya anggota aktif), `Citizen.php` (auto-fill surat hanya anggota aktif), `Kk_model.php` (parameter `$active_only`), `Content_model.php` (riwayat pengaturan), `citizen_notice_helper.php` (notifikasi mutasi/pengaduan/pengingat), `views/citizen/family.php` (teks badge status), `views/admin/dashboard.php` (kartu monitoring; kartu "KK Terverifikasi" kini menaut ke `admin/penduduk`). Modul Data Penduduk dapat dilepas dengan menghapus 4 rute `admin/penduduk*`, item menu "Data Penduduk" & ikon `idcard` di `header.php`, 3 izin `penduduk.*` di `rt_access.php`, serta `controllers/Penduduk.php` dan `views/admin/ops/penduduk_*.php` — tanpa perubahan database.

Rollback putaran `rt16-2`: tema → centang *Kembalikan ke warna bawaan* (atau pulihkan dari Riwayat Pengaturan); CSS dengan `var(--theme-*, #warna-asli)` tetap tampil identik tanpa pengaturan. Email & pengaduan anonim → matikan sakelarnya di Pengaturan. Field tambahan → nonaktifkan per field. Tabel/kolom baru boleh dibiarkan (aplikasi lama mengabaikannya). `php.ini` dapat dikembalikan dari `php.ini.bak-sebelum-zip`.
