# Redesign menu admin putih-oranye

Referensi diterapkan pada komponen admin modul operasional: Notulen, Aset & Inventaris, Belanja & Penerimaan, Kontak Penting, Arsip Dokumen, dan Akun & Hak Akses.

## Perubahan

- Sidebar putih dengan menu aktif oranye, judul kelompok lebih jelas, topbar pencarian dan akun tetap memakai fungsi existing.
- Banner ilustrasi SVG lokal untuk enam modul, kartu statistik, tabel dengan header krem, formulir dua kolom dan ikon di input.
- Toolbar inventaris/belanja memisahkan aksi dari banner. Aksi tambah/pinjam tetap memakai pemeriksaan izin dan route existing.
- Empty state inventaris/belanja/arsip memiliki tombol menuju form nyata atau fokus ke form unggah.
- Pencarian kontak dan kategori bekerja pada daftar kontak yang sudah diizinkan backend. Pencarian belanja berlaku dalam daftar hasil yang sedang ditampilkan; filter status tetap dikirim ke backend.
- Tombol tampil/sembunyi password di form akun. Input file tetap native, dengan validasi MIME/ukuran existing di server.
- Semua field penting existing, termasuk urutan/keaktifan kontak, retensi arsip, serta status pengesahan notulen, dipertahankan.
- Tidak menambahkan tombol hapus karena proses hapus tidak tersedia pada halaman existing. Matriks hak akses tetap baca dari konfigurasi server.

## File implementasi

- application/helpers/ops_ui_helper.php: dekorasi form admin, banner dan statistik reusable, empty state. Field name, value, required, CSRF dan nama aksi tidak diubah.
- application/views/admin/header.php: memuat CSS/JS presentasi baru.
- application/views/admin/ops/aset_index.php: toolbar, empty state.
- application/views/admin/ops/belanja_index.php: toolbar, pencarian daftar saat ini, empty state.
- application/views/admin/ops/kontak.php: pencarian/kategori, jumlah hasil, tombol Batal.
- application/views/admin/ops/arsip_index.php: empty state unggah dan tombol Batal.
- application/views/admin/ops/akun.php: judul daftar, tautan tambah akun, tombol Batal.
- assets/admin-ops-redesign.css dan assets/admin-ops-redesign.js.
- assets/img/admin-ops/{minutes,inventory,purchase,contacts,archive,accounts}.svg: ilustrasi vektor lokal tanpa CDN.
- tests/admin_ops_ui_smoke.php: uji integrasi pada database terisolasi saja.

Tidak ada perubahan controller, model transaksi, skema database, peran atau aturan persetujuan untuk pekerjaan admin ini. Data situs warga tidak diubah. CSS tampilan baru dibatasi pada halaman admin yang memiliki komponen .ops; dekorasi helper form/statistik juga dibatasi route admin.

## Mapping route

| Halaman | Route | Handler existing |
| --- | --- | --- |
| Notulen baru | admin/notulen/baru | Kegiatan::notulen_form |
| Inventaris | admin/inventaris | Operasional::aset_index |
| Tambah barang | admin/inventaris/baru | Operasional::aset_form |
| Peminjaman | admin/inventaris/pinjam | Operasional::pinjam |
| Belanja | admin/belanja | Operasional::belanja_index |
| Permintaan baru | admin/belanja/baru | Operasional::belanja_form |
| Kontak | admin/kontak | Operasional::kontak |
| Arsip | admin/arsip | Sistem::arsip |
| Unduh arsip | admin/arsip/{id}/unduh | Sistem::arsip_unduh |
| Akun | admin/akun | Sistem::akun |

## Hasil verifikasi

Dilakukan melalui server PHP lokal khusus pengujian dengan DB_NAME=sukamulya_ui_test, salinan database. Data uji transaksi tidak dimasukkan ke database operasional. Berkas unggah uji telah dibersihkan.

- PHP lint seluruh file PHP berubah: lulus.
- GET enam halaman target: HTTP 200 tanpa error PHP/database.
- Login admin/operator/auditor: lulus.
- Simpan draf notulen serta revisi: lulus, versi sebelumnya tetap tercatat.
- Simpan inventaris dan stok tersedia: lulus.
- Buat/edit kontak dan ubah publik menjadi privat: lulus.
- Buat belanja draf dan kalkulasi jumlah x harga: lulus.
- Buat akun: password tersimpan sebagai hash, lulus.
- Unggah arsip valid dan unduh dengan akun admin: lulus.
- Unggah tanpa berkas: validasi server menolak, lulus.
- Guest tidak boleh mengunduh arsip; operator tidak boleh mengelola akun; auditor tidak boleh menambah inventaris: lulus.
- Browser Chrome: keenam halaman pada 390/1024/1600px tidak memiliki overflow horizontal atau error JavaScript.
- Pencarian lokal, toggle password, sidebar collapse dan shortcut Ctrl+K: lulus.

Pratinjau gambar diambil dari lingkungan uji, sehingga baris berlabel Barang/Kontak Uji dan akun pengujian pada gambar bukan penambahan ke data operasional. Pengujian tidak mencakup pengiriman pesan chat atau penyelesaian transaksi keuangan nyata.

## Lanjutan: Field Tambahan Warga dan Antrean Email

- View `application/views/admin/ops/fields.php`: judul/tombol Tambah Field, empty state dengan fokus ke form, placeholder opsi dan keterangan, tombol Batal. Field name dan action POST existing dipertahankan.
- View `application/views/admin/ops/outbox.php`: banner email, kartu statistik status dari database, empty state antrean, tombol proses existing, pemberitahuan konfigurasi yang dapat ditutup sementara.
- Helper `application/helpers/ops_ui_helper.php`: mapping banner dua halaman serta opsi ikon statistik; tetap kompatibel dengan pemanggil lain.
- `assets/admin-ops-redesign.css`, `assets/admin-ops-redesign.js`: layout tiga statistik antrean, gaya pemberitahuan, tombol tutup tanpa mengubah setting backend.
- Aset SVG `assets/img/admin-ops/fields.svg` dan `email.svg`.
- Test `tests/admin_settings_ui_smoke.php`, hanya boleh berjalan dengan database `sukamulya_ui_test`.

Validasi pada salinan database: login/GET kedua halaman; create/edit/nonaktif field; aturan minimal dua opsi; penolakan label sensitif; masking email; retry failed menjadi pending; process tanpa SMTP tetap pending; operator dan guest ditolak. Tidak ada email dikirim. Browser 390/1024/1600px: tanpa horizontal overflow/error JavaScript; tombol tambah field memfokuskan input; pilihan tipe dan tutup pemberitahuan berfungsi.

Status nonaktif dan peringatan SMTP ditampilkan berdasarkan konfigurasi aktual. Tidak menambahkan klaim fitur dalam pengembangan secara statis. Proses pengiriman ke SMTP sungguhan tidak diuji karena tidak termasuk izin mengirim email.

## Lanjutan: Audit Aktivitas

- `application/views/admin/ops/audit.php`: panel Filter & Pencarian collapsible, pencarian/pengguna/tanggal existing, lima tautan rentang cepat, Reset, Terapkan Filter, badge aksi, empty state. Ekspor dan pagination mempertahankan parameter filter.
- Helper mapping banner diperluas untuk `admin/audit`; aset `assets/img/admin-ops/audit.svg` berupa ilustrasi laptop/log/server/jam.
- `assets/admin-ops-redesign.css`: layout filter responsif, indikator mode filter, chip rentang tanggal.
- `tests/admin_audit_ui_smoke.php`: uji database terisolasi untuk kata kunci + pengguna + tanggal, CSV hasil filter, empty state, penolakan operator/guest.
- Tidak ada perubahan controller, query audit, catatan audit existing, skema, masking ataupun hak ekspor. Ekspor tetap mencatat aktivitas export sesuai proses lama.

Hasil: seluruh tes HTTP lulus; browser 390/1024/1600px tanpa overflow/error JavaScript; panel dapat dibuka/ditutup; lima rentang tanggal menghasilkan parameter GET yang valid. Periode cepat mempertahankan filter pengguna/kata kunci dan memulai pagination dari awal. Pratinjau berasal dari salinan database beserta log uji; data audit operasional tidak diubah.

## Lanjutan: Rekonsiliasi Kas

- View `application/views/admin/ops/rekonsiliasi.php`: layout ringkasan/form/integritas/riwayat sesuai referensi; form tetap memakai recon_date, bank_balance, note, dan CSRF existing. Form hanya tampil bagi kas.reconcile.
- `assets/admin-reconciliation.js`: konfirmasi sebelum submit, pembatalan tanpa POST, cegah klik ganda pada konfirmasi, ringkas/lihat semua maksimal 30 riwayat. Tanpa JavaScript, form tetap dapat disimpan menggunakan backend existing.
- CSS `assets/admin-ops-redesign.css`: tiga statistik, form horizontal responsif, indikator tahapan, panduan, dialog, dua kolom integritas/riwayat.
- Aset SVG `assets/img/admin-ops/reconciliation.svg`; mapping banner helper ditambahkan. Ikon check/close/layers ditambahkan pada helper ikon tanpa mengganti ikon existing.
- Test `tests/admin_reconciliation_smoke.php` dibatasi DB terisolasi.

Hasil: server menghitung saldo jurnal verified sampai tanggal pilihan dan selisih bank dikurangi sistem; tanggal lampau memakai saldo historis; tanggal masa depan/nonangka ditolak; rekonsiliasi tidak mengubah jumlah maupun total jurnal transaksi. Auditor dapat membaca tetapi tidak menyimpan; operator dan guest ditolak. Browser 390/1024/1600px lulus layout/panduan/konfirmasi/batal dan tepat satu POST pada penyimpanan. Konfirmasi tidak menampilkan saldo sistem hari ini sebagai saldo historis; perhitungan otoritatif tetap di server. Tidak ada perubahan controller keuangan, skema database, atau saldo operasional.

## Penyelarasan akhir delapan menu sistem

- Akun: avatar inisial dinamis, badge peran/status, menu aksi Ubah/Reset menuju form existing. Matriks hak akses tetap tersedia sebagai bagian yang dapat dibuka.
- Field: tab Semua/Aktif/Nonaktif menyaring daftar aktual; sakelar wajib isi tetap input checkbox asli. Tipe isian tetap mengikuti pilihan yang didukung backend, tidak menambahkan tipe semu dari mockup.
- Serah terima: indikator tiga tahap, filter periode dari sesi aktual, empty state menuju form sesi, tombol Batal; pengelolaan akun hanya tampil bagi akun.manage. Auditor tidak ditawari tombol persetujuan yang akan ditolak backend.
- Backup: kartu riwayat, tombol backup existing, panduan jadwal dan pemulihan yang dapat dibuka. Tidak menambahkan toggle jadwal atau tombol upload restore tanpa endpoint pendukung.
- Riwayat pengaturan: pencarian/periode dan Reset dalam maksimal 50 entri terbaru, jumlah hasil dinamis, tombol Kembalikan tetap memakai konfirmasi dan POST existing; nilai sebelumnya tetap dapat diperiksa.
- Audit: judul Hasil Aktivitas dan jumlah entri aktual. Rekonsiliasi menggunakan alur konfirmasi yang telah diuji sebelumnya.
- Banner SVG baru: backup.svg, handover.svg, settings-history.svg; mapping helper diperluas.

File tambahan/berubah pada tahap ini: ops_ui_helper.php, akun.php, fields.php, serah_index.php, serah_detail.php, backup.php, pengaturan_riwayat.php, audit.php, admin-ops-redesign.css, admin-ops-redesign.js, tiga aset SVG tersebut, tests/admin_system_final_smoke.php.

### Pengujian akhir

Uji dijalankan pada database sukamulya_ui_test dan SALINAN application dengan storage tersendiri, sehingga enkripsi, rotasi, verifikasi dan unduh backup tidak menyentuh storage produksi.

- Delapan halaman GET lulus tanpa error PHP/database.
- Serah terima: snapshot hash sesuai, finalisasi prematur ditolak, checklist tersimpan, dua pihak menyetujui, finalisasi berhasil, perubahan setelah final ditolak.
- Riwayat: Kembalikan memulihkan nilai konfigurasi pada database uji.
- Backup: run berhasil, header terenkripsi RTBK1, checksum dan dekripsi diverifikasi, unduh berizin berhasil. Uji database backup saja; backup dengan seluruh unggahan tidak dijalankan.
- Operator/guest ditolak pada halaman terlindungi.
- Seluruh suite regresi admin_ops_ui_smoke, admin_settings_ui_smoke, admin_audit_ui_smoke, admin_reconciliation_smoke lulus.
- Browser delapan halaman x empat ukuran (320/390/1024/1600px): 32 pemeriksaan layout tanpa overflow horizontal/error JavaScript; filter status, aksi akun, filter periode, filter/reset riwayat dan panduan backup berfungsi.
- PHP lint file yang berubah lulus.

Batas pemeriksaan: pengujian otomatis dan review visual, bukan jaminan matematis tidak adanya bug. SMTP eksternal, penjadwal OS, dan restore database produksi tidak dijalankan. Fitur tersebut tidak ditampilkan sebagai aksi otomatis baru. Data uji dan backup uji dibersihkan setelah selesai; data operasional tidak diubah.

## Koreksi halaman Antrean Email langsung di localhost

Keluhan tampilan lama ditindaklanjuti dengan mengganti struktur halaman secara khusus, bukan hanya menambahkan gaya komponen umum.

- `application/views/admin/ops/outbox.php`: judul/deskripsi baru, satu kartu peringatan gabungan berdasarkan status aktual, tombol panduan SMTP, statistik tanpa keterangan berulang, Daftar Antrean Email dan empty state ilustratif.
- `assets/admin-email-queue.css`: dimuat pada view setelah CSS modul; ukuran banner, teks, ilustrasi, statistik, tabel dan jarak disesuaikan ulang dengan mockup. Menggunakan cache busting filemtime.
- `assets/admin-email-queue.js`: panduan SMTP dan penutupan pemberitahuan.
- `assets/img/admin-ops/email-empty.svg`: ilustrasi empty state.
- `application/views/admin/settings.php`: parameter tab=theme membuka panel Tema & Fitur yang benar; allowlist tetap berdasarkan meta tab existing.
- Tombol Atur SMTP membuka petunjuk konfigurasi yang nyata; tidak mengaku menyimpan SMTP dari form yang belum tersedia. Tombol Atur Notifikasi Email menuju pengaturan existing. Tombol Proses Antrean tetap POST existing dan tidak diaktifkan otomatis selama verifikasi.

Verifikasi: browser membuka langsung http://localhost/sukamulya/admin/antrean-email dengan sesi admin sementara, bukan halaman HTML snapshot. HTTP 200, layout email-queue-v2 dan stylesheet baru aktif, tanpa horizontal overflow/error JavaScript pada 390/1600px. Panduan SMTP, tutup pemberitahuan, dan tautan langsung panel theme diuji. Akun sementara dihapus otomatis. Tidak ada email dikirim atau konfigurasi operasional diubah. Screenshot: tmp/family-preview/admin-email-live-fixed.png. Header identitas akun pada gambar diberi label Admin untuk pratinjau.

## Pemeriksaan ulang kesesuaian mockup — delapan halaman admin

Masalah yang diperbaiki: tipografi desktop terlalu kecil, proporsi banner dan ilustrasi, jarak antar kartu/form, ketidaksejajaran tinggi panel kiri-kanan, tab status field, komposisi tabel serah terima, serta pop-up aksi akun yang bisa terpotong wadah tabel. BOM pada view rekonsiliasi dihapus karena menghasilkan baris kosong sebelum konten.

File utama baru: `assets/admin-ops-mockup.css`, dimuat melalui `application/views/admin/header.php`. Semua aturan dibatasi admin .ops, bukan portal warga. Penyesuaian view: fields.php, serah_index.php, backup.php, pengaturan_riwayat.php, rekonsiliasi.php. JS aksi baris di admin-ops-redesign.js menempatkan menu di viewport dan menutupnya saat Escape/klik luar/scroll.

Field Tambahan: kolom Wajib dan Urutan mengikuti referensi; checkbox wajib tetap kontrol asli; bantuan/kewenangan warga dipindahkan ke Pengaturan Lanjutan. Nilai tetap ikut dikirim walaupun detail ditutup. Pilihan tipe tetap yang didukung backend, bukan menambahkan jenis semu dari gambar.

Backup: jadwal dan pemulihan menggunakan panduan yang dapat dibuka, sebab penjadwalan dilakukan di server dan tidak tersedia endpoint restore web. Tidak menambahkan toggle atau unggah restore yang berpura-pura aktif. Warning backup tetap ditampilkan di panel backup. Isi/angka tabel tetap dinamis; baris contoh mockup tidak dimasukkan ke database.

Verifikasi ulang:
- `admin_system_final_smoke.php`: delapan GET, snapshot serah terima, penolakan finalisasi belum lengkap, persetujuan dua pihak, final terkunci, restore pengaturan, backup terenkripsi, checksum/dekripsi, unduh, dan pembatasan peran — lulus.
- `admin_settings_ui_smoke.php`: CRUD/status field, validasi, masking antrean dan retry/process tanpa SMTP — lulus; tidak ada email dikirim.
- `admin_audit_ui_smoke.php`: filter dan CSV serta hak akses — lulus.
- `admin_reconciliation_smoke.php`: saldo historis, selisih, validasi dan jurnal tidak berubah — lulus.
- Browser seluruh delapan halaman pada desktop 1742px dan HP 390px: tidak ada horizontal overflow/error JavaScript.
- Pemeriksaan interaksi: menu aksi di dalam viewport, tab status, field advanced tetap masuk FormData, pengungkapan panduan backup, filter/reset riwayat — lulus.

Semua pengujian tulis memakai `sukamulya_ui_test` dan salinan application/storage di tmp/adminqa. Database, saldo, backup dan konfigurasi operasional tidak diubah. Pratinjau adalah tampilan pengujian dengan isi database salinan; kondisi data kosong mengikuti sumber, bukan jumlah contoh pada mockup.
