# Scan KK field-only

## Perbaikan tabel bergaris

Jenis kelamin dinormalisasi dari teks yang benar-benar terbaca: `PEREMPUAN` atau kata yang terpisah spasi/baris (`PEREM PUAN`) menjadi `P`; `LAKI-LAKI` dengan variasi pemisah menjadi `L`. Tidak memakai nama, tanggal lahir/NIK, atau hubungan keluarga untuk menyimpulkan jenis kelamin. Teks campuran dan karakter huruf ambigu tetap perlu diperiksa. Formulir menampilkan pilihan Laki-laki/Perempuan dengan nilai backend L/P, termasuk pada anggota manual. Tes HTTP dapat menambahkan `--second-female` pada mode input privat untuk memeriksa pilihan Perempuan pada anggota kedua tanpa mencetak identitas.

OCR mencoba mode blok dan teks terpisah, lalu memilih hasil berdasarkan jumlah anggota dan field valid. Gambar kecil diperbesar sebelum OCR. Pada layout KK dengan sembilan kolom data, proyeksi garis mendeteksi batas sel; field yang gagal dibaca diperiksa ulang per sel. Jika header hilang pada pembacaan satu halaman, header NIK diverifikasi dari sel sebelum membaca baris. Tidak menebak digit NIK atau nomor KK. Field ambigu tetap kosong atau bertanda Periksa.

Regresi `php -d extension=gd tests/kk_ruled_table_test.php` memakai KK sintetis bertabel dan lima baris kosong: lima anggota, sembilan field tiap anggota, NIK sintetis tepat. Dokumen pengguna hanya diuji lewat parameter path saat runtime; nilai pribadi tidak disalin ke fixture/log. Pengujian privat foto dan PDF turunan foto melalui login berhasil menampilkan lima anggota. Hubungan keluarga tidak terlihat pada potongan dokumen, sehingga tetap perlu koreksi manual.

## Nomor KK dengan cetakan OCR-A

Header KK lama dapat menggunakan angka bersudut OCR-A yang gagal dikenali model bahasa umum. Fallback `Kk_header_digits` mendeteksi komponen gambar di atas tabel dan mencocokkan bentuk terhadap sepuluh glyph sintetis 0–9 (`application/data/kk_ocra_digits.json`). Hasil hanya diterima untuk satu baris unik berisi tepat 16 glyph yang jelas dan berjarak konsisten. Tidak mengganti huruf menjadi angka, tidak mengambil NIK anggota sebagai nomor KK, dan tidak menambahkan digit yang hilang. Bentuk ambigu atau jumlah digit berbeda tetap kosong.

Template dibuat dari font OCR-A lokal dengan `tests/kk_build_digit_templates.php`, tanpa data KK pengguna. Template JSON dibawa bersama source; tidak memerlukan font atau model tambahan di perangkat warga. Pengujian `tests/kk_header_digits_test.php` mencakup seluruh digit, nol awal, 15/17 digit dan bentuk ambigu. Tes upload privat foto dan PDF turunan kini memastikan field nomor KK terisi 16 digit; nilainya tidak dicetak pada output pengujian. Pengguna tetap harus memeriksa hasil OCR sebelum mengajukan.

## Konfigurasi lokal yang aktif

Pembaruan dukungan format: Poppler aktif pada `tools/ocr-bin/poppler/bin/pdftoppm.exe`, ImageMagick aktif pada `C:/Program Files/ImageMagick-7.1.2-Q16-HDRI/magick.exe`. Folder binary menolak akses HTTP (403) dan tidak dimasukkan ke Git. Pada mesin lain, pasang ulang dependensi dan atur `KK_PDFTOPPM` / `KK_MAGICK` bila path berbeda.

Format unggahan: PDF, JPG/JPEG, PNG, WEBP, HEIC/HEIF, AVIF, BMP, TIFF, GIF, JP2, JXL. MIME diperiksa dari isi file. Foto dikonversi ke PNG, orientasi kamera diperbaiki dan transparansi diratakan sebelum OCR. PDF/TIFF membaca seluruh halaman (maksimal 5) dan menggabungkan anggota tanpa menggandakan NIK yang sama; data antarhalaman yang berkonflik ditolak. Satu dokumen hanya boleh memuat satu KK. Gambar animasi dan HEIC dengan beberapa frame memakai frame pertama. Batas upload tetap 10 MB dan foto 40 megapiksel. PDF rusak/terenkripsi atau halaman berlebihan mendapat pesan khusus.

`php -d extension=gd tests/kk_formats_test.php` menguji OCR gambar KK sintetis pada sepuluh format termasuk PDF, pemrosesan multipage, batas halaman dan MIME palsu. Sampel publik HEIC dari `https://github.com/strukturag/libheif/blob/master/examples/example.heic` dipakai hanya untuk menguji decoder dan memastikan foto non-KK tidak mengarang anggota; bukan pengukuran akurasi KK HEIC. Tes HTTP PDF, WEBP, AVIF dan TIFF juga mengisi enam anggota pada formulir. Keberhasilan format tidak menjamin tulisan buram/terpotong bisa dikenali.

Tesseract telah dipasang di `C:/Program Files/Tesseract-OCR/tesseract.exe`. Bila `KK_OCR_ENDPOINT` kosong, aplikasi memakai executable lokal tersebut secara langsung lewat `Kk_local_engine`, sehingga worker terpisah dan restart Apache tidak diperlukan. `KK_TESSERACT` dapat mengganti path. Bahasa `ind+eng` digunakan bila model Indonesia tersedia; jika tidak, menggunakan model `eng` untuk teks Latin. Worker pada panduan di bawah adalah alternatif deployment, bukan persyaratan untuk mode lokal langsung.

## Produksi Linux (CentOS 9 Stream + httpd/PHP-FPM)

`Kk_local_engine` mencari `magick`/`convert` pada path yang dikonfigurasi, daftar path umum (`/usr/bin`, `/usr/local/bin`, `/bin`, `/snap/bin`), folder pada `$PATH`, dan pola `/opt/*/bin`, `/usr/local/ImageMagick*/bin`. Binary harus dapat dieksekusi oleh user web; `is_file()` saja tidak cukup. Jika binary berada di lokasi tidak lazim, set `KK_MAGICK` ke path absolut.

1. Pasang dependensi: `sudo dnf install -y ImageMagick poppler-utils tesseract tesseract-langpack-ind` (Tesseract ada di repo EPEL pada CentOS Stream 9; aktifkan EPEL lebih dulu).
2. Verifikasi sebagai user web, bukan hanya root:
   `sudo -u apache /usr/bin/magick -version`, `sudo -u apache /usr/bin/tesseract --version`, `sudo -u apache /usr/bin/pdftoppm -v`.
3. Jalankan diagnosa aplikasi: `sudo -u apache php tools/kk-check.php`. Output JSON menampilkan path yang terdeteksi, versi, `proc_open`, `open_basedir`, folder temporary, dan ketersediaan model `ind`.
4. Konfigurasi environment. Untuk PHP-FPM tambahkan ke `/etc/php-fpm.d/www.conf` lalu restart `php-fpm`:
   `env[KK_MAGICK] = /usr/bin/magick`, `env[KK_TESSERACT] = /usr/bin/tesseract`, `env[KK_PDFTOPPM] = /usr/bin/pdftoppm`. Untuk `mod_php`, gunakan `SetEnv KK_MAGICK /usr/bin/magick` pada konfigurasi VirtualHost.
5. Jika `open_basedir` aktif, sertakan `/usr/bin` dan folder temporary PHP; bila `proc_open` ada di `disable_functions`, aktifkan kembali untuk VirtualHost aplikasi.
6. SELinux enforcing biasanya mengizinkan eksekusi dari `/usr/bin`. Bila masih gagal, periksa `sudo ausearch -m avc -ts recent` dan jangan mematikan SELinux secara permanen.
7. Pesan "ImageMagick tidak ditemukan" berarti `converter()` tidak menemukan binary executable. Jalankan `command -v magick convert` di shell user web dan cocokkan hasilnya dengan `tools/kk-check.php`.

Pengujian nyata mesin OCR pada PNG sintetis 3 dan 6 anggota berhasil, termasuk NIK setiap anggota. Pengujian HTTP melalui login dan upload PNG juga berhasil mengisi enam anggota pada formulir. Jalankan dengan `php -d extension=gd tests/kk_ocr_image_test.php` dan `php -d extension=gd tests/kk_http_smoke.php` pada instalasi XAMPP ini. GD hanya diperlukan untuk menghasilkan gambar uji, bukan untuk OCR aplikasi.

Poppler sudah tersedia pada instalasi ini. Foto nyata yang buram, terpotong atau memiliki layout berbeda tetap perlu review manual.

Modul baru mempertahankan `citizens` sebagai akun login. Tidak membuat akun/password untuk setiap anggota KK. Tabel tambahan: `kk_families` (KK dan pemilik akun), `kk_members` (anggota), `kk_scan_requests` (snapshot pengajuan, status dan catatan review). Field pribadi tersimpan di database; batasi akses database dan backup. Enkripsi pada lapisan aplikasi belum tersedia.

## Aktivasi

1. Backup database dan uji restore terlebih dahulu. Jalankan `kk_scan_upgrade.sql` pada database yang sama dengan aplikasi. Migrasi tidak dijalankan otomatis pada request web.
2. Sediakan Tesseract lokal dengan bahasa `ind` dan `eng`. Set `KK_TESSERACT` ke path executable absolut. Untuk PDF, sediakan Poppler dan set `KK_PDFTOPPM`. PDF dibaca halaman pertama; gunakan satu KK per file. Tidak ada instalasi/dependency yang diunduh otomatis.
3. Set `KK_OCR_TOKEN` ke token acak yang sama pada Apache/PHP aplikasi dan worker. Set `KK_OCR_ENDPOINT=http://127.0.0.1:8097/` pada environment Apache, lalu restart Apache.
4. Jalankan worker dengan environment tersebut, dari root proyek:

   `C:\xampp\php\php.exe -d upload_max_filesize=10M -d post_max_size=12M -S 127.0.0.1:8097 tools/kk-worker/router.php`

   Worker harus terikat ke loopback dan hanya digunakan untuk pengembangan / satu pengguna. Untuk produksi, gunakan worker terisolasi dengan batas resource, antrean, timeout dan cleanup proses yang dikelola supervisor. Jangan mengekspos server pengembangan PHP ke jaringan.

5. Atur `upload_max_filesize=10M` dan `post_max_size=12M` pada PHP Apache. Folder upload sementara PHP harus di luar webroot. File tidak dipindahkan ke folder `uploads`.
6. Buka `/warga/keluarga` dan `/admin/kk/pengajuan`. Role review mengikuti existing: admin, operator, ketua. Admin memeriksa kewenangan pemohon di luar foto KK sebelum approve. Penolakan/revisi tidak mengubah master; warga bisa mengajukan snapshot baru setelah koreksi. Tidak ada notifikasi eksternal; antrean dan riwayat menjadi pemberitahuan di aplikasi.

## Batas akurasi yang wajib diperiksa

Parser menggunakan posisi kolom dari TSV Tesseract. Memisahkan nomor KK di header dari NIK anggota; membaca jumlah dinamis berdasarkan baris NIK, mengabaikan identifier ambigu dan memberi tanda pemeriksaan untuk confidence rendah. Hubungan keluarga pada tabel bawah dicocokkan lewat nomor urut tercetak. Tidak menyimpulkan hubungan dari jenis kelamin, umur atau urutan anggota.

Foto miring, silau, header pecah, layout berbeda, atau NIK yang tidak terbaca dapat menyebabkan baris/kolom terlewat. Parser ini belum memiliki deskew, deteksi blur atau koreksi perspektif. Kolom yang tidak dikenali tetap kosong. Hasil tidak menjamin semua anggota terbaca, meskipun nomor berformat 16 digit. Pengguna harus mencocokkan semua field dengan KK dan menambah baris yang terlewat sebelum mengajukan. Semua field dapat diedit dan diisi manual bila OCR belum dikonfigurasi.

Tidak ada gambar, path, URL, base64, thumbnail atau raw OCR di hasil maupun halaman admin. Worker menampung TSV hanya dalam memori. Original upload dan PDF raster sementara dihapus pada jalur sukses/error; PHP menghapus upload yang ditolak sebelum proses. Penghentian paksa OS membutuhkan cleanup folder temporary oleh pengelola. Jangan aktifkan tracing request body, logging TSV, atau database query logging di produksi.

## Verifikasi

`C:\xampp\php\php.exe tests/kk_fields_test.php`

Tes sintetis mencakup jumlah 3/6 anggota, pemetaan kolom spasial, pemisahan nomor KK/NIK, tanggal invalid, identifier ambigu, duplikat dan whitelist field. Ini tes parser/validator, bukan bukti akurasi OCR pada foto nyata.

UAT sebelum produksi: KK sintetis JPG/PNG/PDF dengan 3 dan 6 anggota; foto rotasi/silau/blur; tabel bawah; baris kosong; koreksi dan submit; review tanpa dokumen; approve bersamaan; konflik NIK/KK; rollback; akun warga berbeda; mobile 320px. Tesseract/Poppler dan migrasi diperlukan untuk pengujian penuh. Approval mempertahankan anggota lama yang tidak ada pada pengajuan baru, dan perubahan nomor KK memerlukan proses mutasi terpisah.
