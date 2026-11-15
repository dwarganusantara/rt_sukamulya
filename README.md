# Website RT 09 / RW 08 Sukamulya

Website berbasis CodeIgniter 3.1.13, PHP 8.2, dan MySQL/MariaDB. Tampilan portal mengikuti mockup `RT 009_008 Suka Mulya Community Portal.png` yang diberikan pengguna. Foto lingkungan, logo, ikon menu, dan dekorasi bawah dipotong dari file itu dan tersimpan di `assets/mockup/`.

## Instalasi XAMPP

1. Jalankan Apache dan MySQL.
2. Impor `database.sql`, lalu `upgrade.sql`, `portal_upgrade.sql`, `letter_services_upgrade.sql`, dan `letter_admin_upgrade.sql` melalui phpMyAdmin sesuai urutan. Di terminal: `C:\xampp\mysql\bin\mysql.exe -u root -e "source C:/xampp/htdocs/sukamulya/database.sql"`, kemudian jalankan perintah yang sama untuk setiap file berikutnya. Jalankan file upgrade sekali saja.
3. Bila kredensial MySQL berbeda, atur `DB_HOST`, `DB_USER`, `DB_PASS`, dan `DB_NAME` sebagai environment variable atau ubah `application/config/database.php`.
4. Buat akun lewat terminal: `C:\xampp\php\php.exe create_admin.php username password_minimal_10_karakter peran`. Peran: `admin`, `operator`, `bendahara`, `ketua`.
5. Buka `http://localhost/sukamulya/`; admin di `http://localhost/sukamulya/admin`.

URL dasar dan rewrite sudah portabel: instalasi dapat berada di root domain maupun subfolder tanpa mengubah kode. Bila server/proxy memerlukan URL absolut, isi environment variable `APP_BASE_URL` (contoh `https://rt009.sukamulya.my.id/`). Gunakan HTTPS ketika dipublikasikan. Lihat `UPDATE-HOSTING.md` untuk penjelasan perubahan dan checklist deployment.

## Hak akses

| Peran | Tugas di aplikasi |
|---|---|
| Admin | Semua konten dan pengaturan teknis |
| Operator | Halaman, struktur, pengumuman, agenda, galeri, akun warga, proses awal pengajuan |
| Bendahara | Input/pengajuan transaksi dan kelola iuran warga |
| Ketua | Terbitkan konten, putuskan pengajuan, verifikasi/kembalikan transaksi |

Admin awal sudah dibuat pada instalasi lokal. Segera ganti password awal lewat menu **Password**. Buat akun per orang sesuai tugas; jangan berbagi akun.

## Alur keuangan

1. Bendahara mengisi pemasukan/pengeluaran dan mengunggah foto/PDF bukti, maksimal 5 MB.
2. Simpan **draf** atau **ajukan verifikasi**.
3. Ketua RT meninjau bukti dan memilih **Verifikasi** atau **Kembalikan** disertai catatan.
4. Hanya transaksi terverifikasi yang tampil di laporan publik. Bukti draf tidak dapat diakses pengunjung. Draf dan transaksi yang dikembalikan bisa diperbaiki; transaksi terverifikasi terkunci. Gunakan tombol **Buat pembalik** untuk koreksi, lalu catat transaksi yang benar.
5. Pengunjung dapat memilih bulan dan menggunakan **Cetak / PDF** dari browser.

Data iuran pada laporan publik dicatat per periode tanpa nama warga. Sensor nomor rekening, nomor HP pribadi, dan data sensitif lain pada bukti sebelum unggah.

## Konten

- **Beranda & Kontak**: identitas, logo, hero, foto tentang, nilai lingkungan, ikon, dekorasi, statistik agregat, kontak, peta, WhatsApp RT.
- **Menu & Ikon**: tujuh kartu menu cepat pada beranda dapat diubah gambar, judul, subjudul, tujuan, urutan, dan status tampilnya.
- **Halaman**: profil, fasilitas, atau informasi lain; halaman dapat dimasukkan ke menu.
- **Struktur**: nama, jabatan, foto, masa jabatan, kontak publik; pengurus lama dapat diarsipkan.
- **Pengumuman, agenda, galeri**: draf, menunggu persetujuan, terbit. Agenda memiliki tanggal, jam, dan lokasi.
- **Keuangan**: transaksi dengan bukti wajib dan verifikasi.
- **Akun Warga**: warga dapat mendaftar mandiri di `/warga/daftar` atau akun dapat dibuat pengurus. Data akun tidak menyimpan NIK. Setelah mendaftar, warga langsung masuk untuk melihat iuran, mengajukan layanan, dan memantau status.
- **Layanan Warga**: jenis surat, teks katalog, ikon, urutan, status tampil, dan template isi surat dapat diubah di admin. Warga dapat menyimpan draf lalu melanjutkannya; lampiran dipilih saat pengiriman akhir. Formulir kematian meminta data almarhum dan tiga dokumen pendukung. Operator memproses; Ketua RT menyetujui atau menolak dengan nomor surat.
- **Pengaturan Surat RT**: kop surat, logo, penanda tangan, tanda tangan, dan stempel dapat diubah di admin. Surat yang disetujui dapat dicetak atau disimpan sebagai PDF dari browser.
- **Iuran Warga**: tagihan dan pembayaran per akun warga hanya terlihat oleh pemilik akun dan pengurus. Laporan kas publik tetap menampilkan agregat tanpa nama.

Semua foto profil dan acara harus memperoleh izin sebelum diterbitkan. NIK serta dokumen pendukung yang dimasukkan dalam pengajuan hanya ditampilkan kepada pemilik akun dan pengurus. Surat yang belum memiliki tanda tangan atau stempel unggahan perlu disahkan secara manual sebelum dipakai sebagai dokumen resmi.
