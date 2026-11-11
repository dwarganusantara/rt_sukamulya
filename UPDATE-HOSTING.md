# Dokumentasi perbaikan tampilan di hosting

## Masalah

Halaman di hosting tampil sebagai HTML polos karena URL aset dibuat dari nilai tetap `http://localhost/sukamulya/`. Akibatnya browser pengunjung meminta file CSS ke `localhost`, bukan ke domain hosting. Aturan rewrite juga dikunci ke subfolder `/sukamulya/` sehingga tidak portabel bila website dipasang di root domain atau folder lain.

## File yang diperbarui

1. `application/config/config.php`
   - Menghapus `base_url` localhost yang bersifat tetap.
   - Secara otomatis memakai path instalasi saat ini, misalnya `/` untuk root domain atau `/sukamulya/` untuk subfolder.
   - Menambahkan dukungan environment variable `APP_BASE_URL` bila hosting memerlukan URL absolut.
2. `.htaccess`
   - Menghapus `RewriteBase /sukamulya/`.
   - Rewrite sekarang mengikuti lokasi folder aplikasi secara otomatis.
3. `README.md`
   - Memperbarui petunjuk deployment agar tidak lagi meminta perubahan kode untuk setiap domain/path.
4. `UPDATE-HOSTING.md`
   - Dokumen ini, berisi penyebab, lokasi perubahan, dan langkah upload/verifikasi.

## Cara memasang update

Upload dan timpa tiga file berikut dengan versi terbaru:

- `application/config/config.php`
- `.htaccess` (pastikan file tersembunyi ikut terunggah)
- `README.md` (dokumentasi; tidak memengaruhi runtime)

Tidak ada perubahan database dan tidak perlu menjalankan file SQL.

## Update registrasi mandiri warga

Warga sekarang dapat membuat akun sendiri melalui `/warga/daftar`. Halaman login mempunyai tautan **Daftar sekarang**. Registrasi memvalidasi nama, format dan keunikan username, password minimal 10 karakter yang berisi huruf dan angka, konfirmasi password, serta menggunakan CSRF bawaan aplikasi. Password disimpan sebagai hash dan warga langsung masuk setelah pendaftaran berhasil.

File terkait registrasi:

- `application/controllers/Citizen.php`
- `application/config/routes.php`
- `application/views/citizen/login.php`
- `application/views/citizen/register.php`
- `assets/portal.css`

## Update fungsi dan desain Pengumuman

Halaman `/pengumuman` kini memiliki hero khusus, pencarian judul/isi, filter kategori otomatis (Kegiatan, Informasi, Iuran, Lainnya), kartu responsif, kondisi hasil kosong, dan tautan detail. Setiap pengumuman terbit dapat dibuka melalui `/pengumuman/{id}` dan menampilkan isi lengkap serta pengumuman terkait. Kategori ditentukan otomatis dari kata kunci konten sehingga tidak memerlukan perubahan database.

File terkait:

- `application/controllers/Site.php`
- `application/config/routes.php`
- `application/views/site/announcements.php`
- `application/views/site/announcement_detail.php`
- `assets/portal.css`
- `assets/mockup/announcement-hero-v2.png`

## Update fungsi dan desain Agenda

Halaman `/agenda` kini memiliki hero kalender, pencarian berdasarkan judul/isi/lokasi, filter kategori otomatis (Kegiatan, Kerja Bakti, Lomba, Sosial, Lainnya), kondisi hasil kosong, dan kartu dengan tanggal, waktu, serta lokasi. Agenda terbit dapat dibuka melalui `/agenda/{id}` untuk melihat detail lengkap dan agenda terkait. Seluruh data tetap dikelola melalui menu Agenda di Admin dan tidak memerlukan perubahan database.

File terkait:

- `application/controllers/Site.php`
- `application/config/routes.php`
- `application/views/site/agendas.php`
- `application/views/site/agenda_detail.php`
- `assets/portal.css`
- `assets/mockup/agenda-hero-v2.png`

## Update fungsi dan desain Galeri

Halaman `/galeri` kini memiliki hero dokumentasi, pencarian judul/isi, filter kategori otomatis (Kerja Bakti, Lomba, Sosial, Keagamaan, Kegiatan Rutin, Lainnya), kartu foto responsif, serta lightbox. Tombol **Lihat Foto** membuka gambar berukuran besar dan dapat ditutup melalui tombol tutup, area luar, atau tombol Escape. Hanya galeri terbit yang mempunyai gambar yang ditampilkan; data tetap dikelola melalui Galeri Admin.

File terkait:

- `application/controllers/Site.php`
- `application/views/site/gallery.php`
- `assets/portal.css`
- `assets/mockup/gallery-hero-v2.png`

## Update fungsi Kontak dan footer

Halaman `/kontak` kini memiliki hero komunikasi dan kartu kontak dinamis. Alamat membuka URL peta, telepon serta kontak darurat memakai tautan `tel:`, email memakai `mailto:`, dan WhatsApp membuka `wa.me` jika nilainya tersedia di Pengaturan Admin. Nilai kosong ditampilkan sebagai **Belum tersedia** tanpa membuat tautan palsu. Footer global diperbarui dengan identitas, navigasi, data kontak, tahun otomatis, dan tautan Admin.

File terkait:

- `application/views/site/contact.php`
- `application/views/site/footer.php`
- `assets/portal.css`
- `assets/mockup/contact-hero-v2.png`

### Penyempurnaan footer global

Footer seluruh halaman publik kini mengikuti desain referensi: panel utama membulat, tiga kolom informasi, ilustrasi lanskap, papan identitas RT, serta bar copyright terpisah. Teks identitas, alamat, email, telepon, WhatsApp, dan tahun tetap dinamis. Ikon yang sebelumnya berpotensi tampil sebagai karakter rusak juga telah diganti dengan entitas HTML yang aman untuk hosting.

File tambahan/perubahan:

- `application/views/site/footer.php`
- `assets/portal.css`
- `assets/mockup/footer-landscape-v2.png`

Fitur ini menggunakan tabel `citizens` yang sudah ada, sehingga tidak memerlukan perubahan database atau file SQL.

## Update desain halaman login

Halaman `/warga/login` disesuaikan dengan desain portal warga: header mengambang, latar lingkungan penuh, identitas RT dan pintasan layanan di sisi kiri, kartu login serta tombol registrasi di sisi kanan, dan informasi wilayah di bagian bawah. Tampilan memiliki susunan khusus tablet dan seluler.

File yang diperbarui untuk tampilan ini:

- `application/views/citizen/login.php`
- `application/views/site/header.php`
- `assets/portal.css`

CSS `portal.css` menggunakan parameter versi otomatis sehingga browser mengambil desain terbaru setelah file diunggah.

## Update halaman Informasi Warga

Halaman `/informasi` kini menggunakan hero ilustrasi lingkungan dan keluarga, judul identitas RT, serta direktori kartu untuk laporan kas, iuran, agenda, dan profil lingkungan. Susunan kartu berubah menjadi dua kolom pada tablet dan satu kolom pada ponsel.

File terkait:

- `application/views/site/information.php`
- `assets/portal.css`
- `assets/mockup/information-hero-v2.png`

## Update fungsi dan desain halaman Profil

Halaman `/profil` kini memiliki hero konsisten, breadcrumb aktif, konten tentang lingkungan dari pengaturan admin, empat statistik dinamis (KK, jiwa, laki-laki, perempuan), tombol menuju struktur pengurus, serta navigasi cepat ke beranda, informasi, dan kontak. Menu utama sekarang menandai halaman aktif berdasarkan URL, bukan selalu menandai Beranda.

File terkait:

- `application/views/site/profile.php`
- `application/views/site/header.php`
- `assets/portal.css`

## Update desain dan fungsi Beranda

Beranda kini memakai hero lingkungan baru, teks dan tombol yang tetap dikelola melalui Pengaturan Admin, menu cepat dari database, pengumuman terbaru, agenda mendatang, serta bagian profil dan nilai lingkungan. Kartu pengumuman dan agenda sekarang membuka halaman detail masing-masing. Hero bawaan menggunakan aset baru, sementara gambar hero hasil unggahan Admin tetap diprioritaskan.

File terkait:

- `application/views/site/home.php`
- `assets/portal.css`
- `assets/mockup/home-hero-v2.png`

Penyempurnaan berikutnya menggunakan `assets/mockup/home-hero-v3.png`: posisi keluarga diberi ruang aman agar tidak terpotong, teks dekoratif pada papan dihapus, ketajaman teks HTML diperbaiki, dan tinggi footer dipadatkan.

Label papan gerbang, papan hijau, dan papan kayu footer kemudian dipasang sebagai teks HTML dinamis agar tetap tajam. Identitas RT mengikuti `brand_label` dan `brand_subtitle` dari Pengaturan Admin; slogan papan hijau ditempatkan sesuai komposisi desain.

Jika hosting berada di belakang reverse proxy atau memang membutuhkan URL absolut, set environment variable berikut melalui panel hosting:

```text
APP_BASE_URL=https://rt009.sukamulya.my.id/
```

Pada hosting biasa, variabel tersebut tidak perlu diisi.

## Verifikasi setelah upload

1. Buka website dengan mode incognito atau lakukan hard refresh (`Ctrl+F5`).
2. Pastikan tampilan, menu, warna, dan tata letak kembali normal.
3. Buka langsung `/assets/portal.css` pada domain website dan pastikan responsnya bukan 404.
4. Uji halaman Beranda, login warga, dan Admin.
5. Bila halaman selain Beranda menghasilkan 404, pastikan Apache mengizinkan `.htaccess`/`mod_rewrite`. Pada LiteSpeed/cPanel aturan ini umumnya langsung didukung.

## Update footer global v3 (24 Oktober 2026)

- Footer seluruh menu publik memakai ilustrasi lingkungan baru yang lebih ringkas dan responsif.
- Keluarga dan papan kayu ditempatkan di sisi kanan; informasi situs, menu, dan kontak tetap berupa teks HTML yang tajam.
- Tombol Admin di footer publik dihapus agar sesuai desain. Halaman Admin tetap dapat dibuka melalui `/admin`.
- Menu Informasi, email, WhatsApp, dan tautan kontak tetap dapat diklik.
- Admin > Pengaturan Beranda kini memiliki URL Instagram, YouTube, dan Facebook. Ikon sosial hanya muncul jika URL terkait telah diisi.
- Aset baru: `assets/mockup/footer-community-v3.png`.
- File terkait: `application/views/site/footer.php`, `assets/portal.css`, `application/controllers/Admin.php`, dan `application/views/admin/settings.php`.

### Penyelarasan tulisan papan

Tulisan pada papan gerbang hero, papan hijau hero, dan papan kayu footer dipusatkan ulang mengikuti bidang papan. Tipografi menggunakan susunan font rounded yang tersedia di perangkat, jarak huruf dipadatkan, dan ukuran setiap baris dibuat berbeda agar tetap tajam serta proporsional.
