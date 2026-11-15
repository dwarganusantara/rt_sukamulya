# Bisnis Proses Fitur Berlangganan RT/RW Digital

Revisi: 8 November 2026. Rujukan: dokumen bisnis proses yang dilampirkan dan mockup popup **Fitur Berlangganan**.

Dokumen ini merupakan spesifikasi proses yang disesuaikan dengan aplikasi existing. Status implementasi belum dinyatakan selesai oleh dokumen ini. Contoh angka, nama warga, dan statistik pada mockup bukan sumber data aplikasi.

## 1. Tujuan dan ruang lingkup

Tambahkan pengelolaan langganan pada fitur yang sudah tersedia tanpa membangun ulang modul, mengubah data historis, atau memberi tambahan hak akses kepada pengguna. Pertahankan tema navy, putih/krem, dan oranye serta tata letak admin existing.

Fitur Premium yang belum berhak digunakan tetap terlihat bagi pengurus yang memiliki izin modul, dengan badge mahkota/gembok **Premium**. Klik membuka popup sesuai mockup dan tidak menjalankan modul terkunci. Fitur yang dinonaktifkan tidak ditawarkan sebagai langganan.

Dashboard, autentikasi, profil akun, penggantian password, informasi langganan, dan fungsi administrasi inti tetap tersedia sesuai hak akses. Katalog existing memang mengecualikan fungsi inti dari fitur yang dapat dimatikan.

## 2. Hasil audit aplikasi existing

| Bagian | Kondisi saat ini | Penyesuaian yang diperlukan |
|---|---|---|
| Katalog | `application/config/features.php`, kode fitur stabil dan awalan route | Gunakan kode existing sebagai identitas fitur; kategori bisnis modul jangan ditimpa kategori komersial |
| Pengaturan tenant | `app_features`, `tenant_features`, tenant konfigurasi default 1 | Pisahkan aktivasi operasional, visibilitas, dan entitlement berlangganan |
| Gerbang server | `application/libraries/Feature_gate.php`, hook `post_controller_constructor` | Tambahkan keputusan lisensi untuk halaman, POST, AJAX, ekspor, dan unduhan |
| Pengecualian admin | Admin saat ini dapat membuka fitur operasional nonaktif untuk menyelesaikan transaksi lama | Jangan gunakan pengecualian ini untuk melewati lisensi Premium |
| Sidebar | `application/views/admin/header.php` menyaring role dan `link_ok()` | Tambahkan status terlihat-terkunci; jangan mengubah `link_ok()` menjadi izin membuka modul terkunci |
| Dashboard | `application/views/admin/dashboard.php` menyaring sebagian kartu dengan gerbang fitur | Terapkan status terkunci yang sama pada akses cepat; ringkasan tidak boleh membocorkan data fitur terkunci |
| Izin modul | Matriks `application/config/rt_access.php` dan pemeriksaan controller existing | Lisensi merupakan syarat tambahan, bukan pengganti RBAC |
| Identitas tenant | Instalasi satu RT menggunakan `feature_tenant_id` | Jangan mengklaim isolasi data multi-tenant sebelum seluruh query dan relasi pengguna diaudit |

Ketiadaan tabel fitur pada gerbang lama saat ini membuat gerbang terbuka untuk kompatibilitas. Pemeriksaan entitlement Premium yang baru harus menolak akses jika lisensi tidak dapat dibuktikan valid. Jangan otomatis mengklasifikasikan semua modul lama menjadi Premium ketika migrasi.

## 3. Aktor dan kewenangan

| Aktor | Kewenangan |
|---|---|
| Super Admin platform | Mengatur katalog komersial, paket, harga, lisensi, trial, override, dan riwayat aktivasi |
| Admin RT/RW | Melihat paket sendiri, membuka popup, meminta aktivasi/perpanjangan, dan mengelola fitur sesuai lisensi serta role |
| Pengurus lainnya | Mengakses modul sesuai izin role dan entitlement RT/RW |
| Warga | Mengakses fitur warga sesuai kepemilikan data, role, dan kebijakan layanan; tidak mengelola lisensi |
| Pengembang | Menerima permintaan dan konfirmasi pembayaran; aktivasi dilakukan melalui kewenangan platform yang sah |

Super Admin harus memiliki autentikasi dan otorisasi platform terpisah. Username `admin`, role admin RT, atau parameter tenant dari browser tidak memberikan kewenangan platform.

## 4. Status dan urutan keputusan

Pisahkan tiga lapisan berikut:

1. **Katalog komersial:** `FREE`, `PREMIUM`, `DISABLED`.
2. **Operasional:** aktivasi modul, induk/dependensi, serta visibilitas pengurus/warga existing.
3. **Entitlement tenant:** `ACTIVE`, `TRIAL`, `EXPIRED`, `SUSPENDED`, `NONE`, dengan tanggal mulai/akhir.

Urutan pemeriksaan server: autentikasi → tenant dan relasi pengguna yang sah → izin role/action → katalog/aktivasi/visibilitas/dependensi → entitlement Premium → eksekusi controller.

| Keadaan | Menu | Respons akses |
|---|---|---|
| Gratis, operasional aktif, role sesuai | Normal | Jalankan fungsi existing |
| Premium dengan lisensi aktif/trial yang masih berlaku | Normal | Jalankan fungsi existing sesuai action yang diizinkan |
| Premium tanpa lisensi, belum mulai, kedaluwarsa, atau ditangguhkan | Badge Premium, dapat membuka penawaran | `SUBSCRIPTION_REQUIRED`, popup; modul tidak dijalankan |
| Platform menonaktifkan fitur atau modul tidak tersedia | Disembunyikan | `FEATURE_DISABLED`; tanpa popup penawaran |
| Role tidak memiliki izin | Disembunyikan | `PERMISSION_DENIED`; tanpa popup penawaran |
| Belum login | Tidak tersedia | Login atau respons autentikasi JSON sesuai endpoint |

Hak akses anak tidak mengaktifkan induknya. Bila induk terkunci karena langganan, tampilkan penjelasan dependensi yang benar; bila induk nonaktif secara operasional, jangan menawarkan pembelian seolah pembelian pasti menyelesaikannya.

## 5. Alur pengurus membuka Premium

1. Pengurus login; server menentukan tenant dan izin menggunakan identitas yang tervalidasi.
2. Server membentuk menu normal, terkunci, atau tersembunyi berdasarkan keputusan yang sama dengan gerbang akses.
3. Pengurus mengklik menu/kartu Premium. Frontend memeriksa status terbaru dari server tanpa menjalankan route modul.
4. Bila berhak, navigasikan ke route existing. Bila memerlukan langganan, buka satu komponen popup reusable.
5. Jika status tidak dapat diperiksa karena jaringan, tampilkan pesan coba lagi. Jangan memberikan akses atau menyatakan lisensi kedaluwarsa tanpa bukti.
6. Tutup popup mengembalikan pengguna ke halaman semula dan fokus ke tombol pemicu.
7. Setelah aktivasi sah, permintaan berikutnya memeriksa ulang lisensi; tautan kembali membuka modul tanpa perubahan kode modul.

URL langsung tetap diperiksa di server. GET terkunci menampilkan penjelasan dan kontak yang aman; POST/AJAX/ekspor/unduhan ditolak sebelum aksi dan tidak diubah menjadi navigasi popup yang mengeksekusi ulang aksi.

## 6. Isi popup sesuai mockup

**Judul:** Fitur Berlangganan

**Ilustrasi:** mahkota emas/oranye dengan gembok, tanpa menyalin gambar seluruh dashboard sebagai UI.

**Isi standar:**

> Fitur **{Nama Fitur}** belum dapat digunakan untuk RT/RW Anda. Untuk menggunakan fitur ini, silakan berlangganan / subscribe dengan menghubungi Pengembang.

Untuk kedaluwarsa gunakan kalimat “Masa aktif fitur **{Nama Fitur}** telah berakhir. Silakan hubungi Pengembang untuk memperpanjang langganan.” Untuk penangguhan tampilkan status dan arahan menghubungi pengembang, tanpa menjanjikan pembelian otomatis mengaktifkan kembali akses.

| Elemen | Perilaku |
|---|---|
| X kanan atas | Menutup popup |
| Hubungi via WhatsApp | Membuka `https://wa.me/6285697578958?text={pesan}` di tab baru; tidak mengirim pesan otomatis |
| Nomor kontak | **085697578958 (Chendy)** |
| Kunjungi Website | Membuka `https://www.warganusa.com` di tab baru |
| Lihat Paket Berlangganan | Membuka halaman internal **Paket & Langganan Saya**, berisi penawaran paket aktual dan status tenant |

Jangan mengarahkan tombol paket ke URL rekaan atau menampilkan harga yang belum dikonfigurasi. Jika belum ada paket penawaran aktif, halaman paket menampilkan keadaan kosong dengan tombol hubungi pengembang.

Pesan WhatsApp dibuat dengan URL encoding:

> Halo, saya pengurus {Nama RT/RW}. Saya ingin informasi paket dan aktivasi fitur {Nama Fitur} pada aplikasi RT/RW Digital Warga Nusantara. Terima kasih.

Nama RT/RW berasal dari pengaturan, nama fitur dari katalog. Jangan menyertakan NIK, nomor KK, identitas warga lain, token sesi, atau password. Gunakan `noopener noreferrer` untuk tautan eksternal.

Popup memakai backdrop gelap, panel putih membulat, teks navy, kartu kontak krem, dan CTA oranye gradasi. Lebar desktop sekitar 600px mengikuti komposisi gambar, maksimum lebar layar dikurangi margin. Pada mobile gunakan lebar responsif, batas tinggi dan scroll internal jika diperlukan. Dukung Escape, fokus keyboard terkurung di dialog, label aksesibel, dan tombol tutup yang mudah disentuh.

## 7. Paket dan aktivasi manual

1. Super Admin mengonfigurasi paket aktif, harga, durasi, fitur, dan kebijakan masa berlaku.
2. Admin RT membuka paket, lalu menghubungi pengembang dengan paket/fitur yang diminati.
3. Pengembang memverifikasi pembayaran melalui proses manual. Klik WhatsApp, website, atau tombol paket tidak mengaktifkan lisensi.
4. Super Admin yang sah memilih tenant, paket, tanggal mulai/akhir, referensi konfirmasi, dan alasan aktivasi.
5. Server memvalidasi tanggal, paket aktif, dan kewenangan; menyimpan lisensi serta audit dalam transaksi.
6. Invalidasi cache entitlement tenant. Pengurus dapat memuat ulang status tanpa logout.
7. Perpanjangan, penangguhan, pencabutan, trial, dan CSR mengikuti mekanisme berizin dan tercatat yang sama.

Tanggal akhir harus memiliki semantik eksplisit: akses valid ketika `starts_at <= now < ends_at`, memakai waktu server yang konsisten. Paket berbayar tidak otomatis berlaku tanpa batas saat tanggal akhir kosong; akses permanen memerlukan mode eksplisit.

## 8. Kedaluwarsa dan data historis

Data tetap tersimpan. Default akses Premium terkunci ketika entitlement habis. Kebijakan baca historis harus dikonfigurasi per fitur/action dan tetap memeriksa kepemilikan data serta RBAC.

Ekspor/unduh bukan otomatis aman hanya karena memakai GET. Bila tidak ada kebijakan historis yang eksplisit, tolak akses. Pengecualian administrator lama untuk modul nonaktif tidak boleh menjadi bypass lisensi.

Proses latar belakang juga memeriksa entitlement saat eksekusi. Antrean yang ditunda tidak dihapus; catat alasan penundaan tanpa menganggap pekerjaan berhasil. Tetapkan kebijakan khusus untuk pekerjaan administratif wajib atau keselamatan sebelum mengkomersialkan fitur terkait.

## 9. Pemetaan fitur awal

| Kode existing | Menu/route admin | Sisi warga terkait |
|---|---|---|
| `family_card` | Verifikasi KK — `admin/kk/pengajuan` dan awalan `admin/kk` | `warga/keluarga` |
| `resident_profile` | Data Penduduk — `admin/penduduk` | Tidak ada route warga pada katalog ini |
| `resident_mutation` | Mutasi Warga — `admin/mutasi` | `warga/mutasi` |
| `social_aid` | Bantuan Sosial — `admin/bansos` | Tidak ada route warga pada katalog ini |
| `letters` | Pengajuan Warga, jenis/pengaturan surat — awalan katalog existing | Layanan dan riwayat surat existing |
| Fitur lainnya | Gunakan seluruh awalan route di `features.php` | Pertahankan pemetaan existing |

Mockup memberi contoh Verifikasi KK, Mutasi Warga, dan Bantuan Sosial berbadge Premium; ini usulan katalog yang perlu diatur oleh platform, bukan alasan mematikan akses pelanggan existing secara otomatis. Satu kode `family_card` saat ini mencakup admin dan warga; jika lisensi harus khusus verifikasi admin, pisahkan kebijakan audience/action terlebih dahulu agar akses KK warga tidak ikut terkunci tanpa sengaja.

## 10. Data dan panel pengelolaan

Gunakan tabel fitur existing sebagai identitas; jangan membuat katalog duplikat yang memiliki route berbeda. Tambahkan metadata komersial yang terpisah dari `category` modul existing, paket, relasi fitur paket, langganan tenant, override bertanggal, dan audit lisensi melalui migrasi non-destruktif.

Panel platform mencakup Katalog Komersial, Paket, Langganan RT/RW, Trial/Override, dan Riwayat Aktivasi. Admin RT hanya mendapat **Paket & Langganan Saya** yang bersifat baca-saja. Endpoint admin RT tidak boleh mengubah harga, tenant, kategori komersial, atau lisensi meskipun request dimanipulasi.

Instalasi saat ini satu RT. Pengelolaan banyak tenant pada lisensi tidak membuktikan data operasional telah terisolasi. Rollout multi-tenant menunggu audit identitas tenant, query, penyimpanan berkas, cache, pekerjaan latar belakang, serta pengujian akses silang.

## 11. Pengujian penerimaan

- Fitur gratis tetap berfungsi; pengguna tanpa izin tidak mendapat akses maupun popup penawaran.
- Premium tanpa lisensi terlihat terkunci; klik sidebar, favorit, pencarian, dan akses cepat menghasilkan keputusan konsisten.
- Popup menampilkan fitur yang benar, kontak benar, dapat ditutup, serta tidak overflow pada layar 320px dan desktop.
- WhatsApp membawa nama RT/fitur tanpa data pribadi; tombol paket menuju halaman paket yang nyata.
- Lisensi aktif, trial, belum mulai, kedaluwarsa, suspended, dan override memiliki hasil sesuai tanggal server.
- URL langsung, POST, AJAX, ekspor, unduhan, dan pekerjaan latar belakang tidak dapat melewati gerbang.
- Admin RT tidak dapat mengaktifkan lisensi sendiri; role admin existing tidak melewati Premium.
- Aktivasi/perpanjangan/pencabutan diaudit, cache terinvalidasi, dan data historis tidak dihapus.
- Migrasi tidak otomatis mengunci modul pelanggan existing; rollback tidak menghapus data modul atau riwayat lisensi.

## 12. Tahapan pelaksanaan

1. Finalisasi klasifikasi komersial dan kebijakan historis berdasarkan pemetaan nyata.
2. Bangun migrasi, service entitlement, serta identitas platform terpisah; pertahankan default akses existing saat transisi.
3. Integrasikan gerbang server dan audit seluruh route/action termasuk proses background.
4. Bangun panel platform, halaman paket tenant, badge, dan popup reusable sesuai gambar.
5. Uji pada database serta storage terisolasi; lakukan pilot sebelum mengaktifkan pembatasan pada instalasi operasional.

Dokumen sumber berisi contoh prompt coding dan permintaan persetujuan sebelum implementasi. Bagian tersebut diperlakukan sebagai materi referensi, bukan instruksi operasional tambahan untuk revisi dokumen ini. Revisi ini tidak mengubah source code, database, harga, atau lisensi pelanggan.
