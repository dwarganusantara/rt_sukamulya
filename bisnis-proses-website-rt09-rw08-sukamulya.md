# Bisnis Proses & Panduan Website RT 09 / RW 08 Kelurahan Sukamulya

> Dokumen perencanaan website lingkungan: data lingkungan, acara, struktur organisasi, dan transparansi keuangan RT.
> Versi 1.0 | Oktober 2026

---

## 1. Tujuan Website

Website sebagai pusat informasi lingkungan yang:

- **Transparan**: laporan pemasukan dan pengeluaran terbuka untuk warga.
- **Mudah diakses**: nyaman dibuka lewat HP, termasuk oleh warga lansia.
- **Mudah dikelola**: bisa dioperasikan pengurus yang bukan ahli IT.
- **Aman**: data pribadi warga terlindungi.

---

## 2. Pengguna dan Hak Akses

| Peran | Hak akses |
|---|---|
| Pengunjung / warga umum | Melihat info publik: profil, struktur, acara, ringkasan keuangan, pengumuman |
| Warga terdaftar | Akses publik + data KK sendiri, pengajuan surat pengantar, status iuran sendiri |
| Operator (Sekretaris) | Kelola data warga, acara, pengumuman, surat |
| Bendahara | Input pemasukan/pengeluaran, unggah bukti nota |
| Ketua RT | Verifikasi/approve laporan keuangan dan surat, akses penuh |
| Admin teknis | Pengaturan sistem, backup, manajemen akun |

---

## 3. Modul Utama

1. **Beranda**: pengumuman terbaru, acara terdekat, ringkasan saldo kas.
2. **Profil Lingkungan**: sejarah, peta wilayah, batas RT, fasilitas (pos ronda, musala, posyandu), kontak darurat.
3. **Struktur Organisasi**: Ketua RW, Ketua RT, sekretaris, bendahara, seksi-seksi, lengkap dengan foto dan kontak.
4. **Data Lingkungan**: jumlah KK, jumlah jiwa, komposisi usia, jenis kelamin, pekerjaan (statistik saja).
5. **Agenda & Acara**: kalender kegiatan, dokumentasi foto, notulen rapat.
6. **Keuangan Transparan**: kas masuk, kas keluar, saldo, bukti nota.
7. **Layanan Warga**: surat pengantar, lapor warga baru/pindah/kelahiran/kematian.
8. **Galeri & Berita**.
9. **Kontak & Aduan**.

---

## 4. Alur Proses per Modul

### A. Data Warga

1. Warga baru, pindah, lahir, atau meninggal melapor ke RT (online atau langsung).
2. Sekretaris memverifikasi dokumen (KK, KTP, surat pindah).
3. Sekretaris menginput atau memperbarui data di sistem.
4. Ketua RT menyetujui perubahan.
5. Statistik di website diperbarui.
6. **NIK, nomor KK, alamat lengkap, dan nomor HP tidak ditampilkan publik.** Yang tampil hanya statistik agregat.

### B. Acara Lingkungan

1. Pengurus/seksi mengusulkan acara (kerja bakti, 17 Agustusan, pengajian, posyandu, rapat warga).
2. Ketua RT menyetujui, operator memublikasikan di kalender.
3. Pengumuman disebar lewat website dan grup WhatsApp RT.
4. Pelaksanaan: absensi atau RSVP bila perlu.
5. Setelah acara: unggah dokumentasi dan notulen.
6. Jika ada biaya, dicatat di modul keuangan.

### C. Struktur Organisasi

1. Dibuat berdasarkan hasil musyawarah pemilihan pengurus (berita acara/SK).
2. Admin menginput nama, jabatan, kontak, masa jabatan.
3. Saat pergantian pengurus, data lama diarsipkan sebagai riwayat kepengurusan.

### D. Transparansi Keuangan (inti website)

**Pemasukan**

1. Warga membayar iuran (keamanan, kebersihan, sosial/kas), tunai atau transfer.
2. Bendahara mencatat: tanggal, sumber, kategori, jumlah.
3. Bukti (foto kwitansi / screenshot transfer) diunggah.
4. Transaksi muncul di laporan dengan status "tercatat".

**Pengeluaran**

1. Pengajuan kebutuhan (honor petugas sampah, perbaikan lampu jalan, konsumsi acara, dll.).
2. Ketua RT menyetujui pengeluaran di atas batas tertentu (usulan: Rp500.000).
3. Bendahara membayar, mencatat, dan **mengunggah nota/foto bukti**.
4. Transaksi muncul di laporan.

**Pelaporan**

1. Pemasukan atau pengeluaran langsung tercatat saat bendahara menyimpan transaksi.
2. Akhir bulan: bendahara menutup buku dan menghitung saldo.
3. Laporan bulanan dipublikasikan (tabel, grafik, unduh PDF).
4. Laporan tahunan dibahas di rapat warga dan diarsipkan.

**Aturan emas transparansi**

- Bukti transaksi dapat dilampirkan jika tersedia, tetapi tidak diwajibkan.
- Transaksi yang sudah dipublikasikan **tidak dihapus**. Koreksi dilakukan dengan transaksi pembalik, disertai log perubahan.
- Iuran ditampilkan **per periode, bukan per nama**. Status tunggakan hanya terlihat oleh warga yang bersangkutan agar tidak mempermalukan.

### E. Layanan Surat Pengantar

1. Warga mengajukan online.
2. Sekretaris memverifikasi.
3. Ketua RT menyetujui.
4. Surat diunduh sebagai PDF dengan nomor surat otomatis.

---

## 5. Standar yang Sebaiknya Ada

### Konten dan Fungsi

- Informasi selalu terbaru, dengan tanggal terbit di setiap pengumuman.
- Kontak jelas: Ketua RT, Ketua RW, ronda/keamanan, nomor darurat (Puskesmas, pemadam kebakaran, polisi).
- Peta lokasi wilayah RT (Google Maps).
- Menu sederhana, informasi penting maksimal 3 klik.
- Fitur pencarian.

### Keamanan dan Privasi

- Patuhi **UU No. 27 Tahun 2022 tentang Pelindungan Data Pribadi**: jangan tampilkan NIK, no. KK, atau alamat lengkap. Minta persetujuan untuk foto warga, terutama anak-anak.
- Gunakan HTTPS, password kuat, dan akun dengan peran berbeda.
- Backup otomatis mingguan.
- Log aktivitas (siapa mengubah apa).

### Tata Kelola

- Tunjuk **admin website dan penggantinya**.
- Buat SOP singkat: siapa menginput apa dan batas waktunya (contoh: laporan keuangan paling lambat tanggal 5 bulan berikutnya).
- Rujukan regulasi: Permendagri No. 18 Tahun 2018 tentang Lembaga Kemasyarakatan Desa dan Lembaga Adat Desa. Konsultasikan dengan kelurahan soal kewenangan pengelolaan dana iuran.
- Sahkan lewat **musyawarah warga**, termasuk persetujuan publikasi data dan laporan keuangan.

---

## 6. Panduan Tampilan (Sederhana & Soft)

### Prinsip Desain

| Prinsip | Penjelasan |
|---|---|
| Sederhana | Satu kolom di HP, banyak ruang kosong, tidak ramai |
| Soft | Warna lembut, sudut membulat, bayangan tipis |
| Terbaca | Huruf besar, kontras cukup, ramah lansia |
| Konsisten | Warna, tombol, dan kartu seragam di semua halaman |
| Mobile-first | Dirancang untuk HP dulu, baru layar besar |

### Palet Warna (Soft)

Pilih satu warna utama, jangan lebih dari tiga warna.

| Peran | Warna | Kode |
|---|---|---|
| Utama (hijau sage) | ![](https://placehold.co/16x16/7FB5A0/7FB5A0.png) | `#7FB5A0` |
| Utama gelap (teks judul, tombol) | ![](https://placehold.co/16x16/3F6F5E/3F6F5E.png) | `#3F6F5E` |
| Latar halaman (krem lembut) | ![](https://placehold.co/16x16/FAF8F3/FAF8F3.png) | `#FAF8F3` |
| Latar kartu | ![](https://placehold.co/16x16/FFFFFF/FFFFFF.png) | `#FFFFFF` |
| Aksen hangat (peach) | ![](https://placehold.co/16x16/F2C6A0/F2C6A0.png) | `#F2C6A0` |
| Teks utama | ![](https://placehold.co/16x16/2F3A36/2F3A36.png) | `#2F3A36` |
| Teks sekunder | ![](https://placehold.co/16x16/6B7873/6B7873.png) | `#6B7873` |
| Pemasukan (hijau) | ![](https://placehold.co/16x16/5FA77F/5FA77F.png) | `#5FA77F` |
| Pengeluaran (merah lembut) | ![](https://placehold.co/16x16/D98C86/D98C86.png) | `#D98C86` |

Alternatif palet jika ingin nuansa lain: biru langit lembut (`#8FB8D8`) atau lavender (`#B7A9D6`) dengan latar yang sama.

### Huruf (Tipografi)

- Gunakan **satu keluarga huruf**: *Poppins*, *Nunito*, atau *Inter* (semua gratis di Google Fonts).
- Ukuran teks isi **16–18 px**, judul 24–32 px.
- Jarak antarbaris longgar (sekitar 1,6).
- Hindari huruf miring/dekoratif panjang dan teks abu-abu terlalu muda.

### Gaya Komponen

- **Kartu**: latar putih, sudut membulat 12–16 px, bayangan sangat tipis.
- **Tombol**: sudut membulat penuh, warna utama gelap dengan teks putih, ukuran besar agar mudah ditekan jempol.
- **Ikon**: satu set ikon garis tipis yang seragam (misalnya Lucide atau Feather).
- **Foto**: pengurus dalam bingkai bulat, dokumentasi acara dalam kotak bersudut membulat.
- **Animasi**: minimal; cukup efek halus saat disentuh.

### Struktur Halaman (Wireframe)

**Beranda (tampilan HP)**

```
+--------------------------------+
|  [logo]  RT 09 / RW 08    [≡]  |
+--------------------------------+
|  Selamat datang, Warga         |
|  RT 09 RW 08 Sukamulya         |
|  [ Lihat Laporan Kas ]         |
+--------------------------------+
|  Saldo Kas Bulan Ini           |
|  Rp 4.250.000                  |
|  Masuk  Rp 2.100.000           |
|  Keluar Rp 1.350.000           |
+--------------------------------+
|  Pengumuman Terbaru            |
|  - Kerja bakti Minggu 25 Okt   |
|  - Jadwal posyandu November    |
+--------------------------------+
|  Acara Terdekat                |
|  [25 OKT] Kerja Bakti          |
+--------------------------------+
|  Menu Cepat                    |
|  (Profil) (Struktur) (Agenda)  |
|  (Keuangan) (Surat) (Kontak)   |
+--------------------------------+
|  Kontak darurat | Footer       |
+--------------------------------+
```

**Halaman Keuangan**

```
+--------------------------------+
|  Laporan Keuangan              |
|  [Okt 2026 v]  [Unduh PDF]     |
+--------------------------------+
|  Saldo Awal     Rp 3.500.000   |
|  Pemasukan      Rp 2.100.000   |
|  Pengeluaran    Rp 1.350.000   |
|  Saldo Akhir    Rp 4.250.000   |
|  Status: Terverifikasi ✓       |
+--------------------------------+
|  [grafik batang sederhana]     |
+--------------------------------+
|  Tanggal | Uraian | Jumlah | 📎 |
|  05 Okt  | Iuran  | +...   | 📎 |
|  08 Okt  | Honor  | -...   | 📎 |
+--------------------------------+
```

**Struktur Organisasi**

```
        [ Ketua RW 08 ]
              |
        [ Ketua RT 09 ]
       /      |       \
 [Sekretaris][Bendahara][Seksi-seksi]
```

Setiap kotak berisi foto bulat, nama, jabatan, dan tombol "Hubungi" (WhatsApp).

### Menu Navigasi yang Disarankan

`Beranda · Profil · Struktur · Data Lingkungan · Agenda · Keuangan · Layanan · Galeri · Kontak`

Di HP cukup menu hamburger, ditambah **tombol WhatsApp melayang** di pojok kanan bawah.

### Template Siap Pakai

Pilih tema yang ringan dan sudah berdesain bersih. Opsi gratis untuk WordPress:

| Tema | Kelebihan |
|---|---|
| Astra | Sangat ringan, banyak template, mudah diubah |
| Kadence | Pengaturan warna dan huruf mudah, tampilan modern |
| GeneratePress | Paling cepat dan stabil |

Cari template bertema *community / non-profit / organization*, lalu ganti warna sesuai palet di atas. Plugin pendukung yang berguna: kalender acara, tabel data, formulir kontak, dan galeri foto.

Alternatif tanpa coding: **Google Sites** untuk halaman informasi, dengan **Google Sheets** yang ditanam untuk laporan keuangan.

---

## 7. Panduan Admin: Upload Bon, Foto Acara, dan Foto Lainnya

Bagian ini berisi langkah kerja untuk admin/operator/bendahara dalam memasukkan bukti dan foto ke website.

### 7.1 Siapa Boleh Upload Apa

| Jenis file | Yang meng-upload | Yang menyetujui |
|---|---|---|
| Bon / nota / kwitansi pengeluaran | Bendahara | Ketua RT |
| Bukti transfer / kwitansi pemasukan | Bendahara | Ketua RT |
| Foto kegiatan / acara | Operator (Sekretaris) atau seksi acara | Ketua RT |
| Foto profil pengurus | Operator | Ketua RT |
| Foto fasilitas, peta, banner, logo | Admin | Ketua RT |
| Dokumen (notulen, surat, PDF laporan) | Operator | Ketua RT |

### 7.2 Perintah Kerja: Upload Bon Pengeluaran

Lakukan setiap kali ada pengeluaran, **paling lambat 2 hari setelah transaksi**.

1. Masuk ke halaman admin, pilih menu **Keuangan > Tambah Pengeluaran**.
2. Isi data transaksi:
   - Tanggal transaksi
   - Kategori (kebersihan, keamanan, acara, perbaikan, sosial, administrasi, lainnya)
   - Uraian singkat dan jelas (contoh: "Honor petugas sampah Oktober")
   - Jumlah (angka saja, tanpa titik/koma)
   - Penerima / nama toko
   - Terkait acara (pilih bila pengeluaran untuk acara tertentu)
3. Foto bon/nota yang asli:
   - Letakkan nota di permukaan datar dengan cahaya cukup.
   - Pastikan **tanggal, nama toko, rincian barang, dan total** terbaca jelas.
   - Foto dari atas, tidak miring, tidak buram, tidak terpotong.
   - Bila nota panjang, foto per bagian atau gunakan fitur pindai (scan) di HP.
4. Klik **Unggah Bukti** dan pilih foto. Pratinjau akan tampil, periksa keterbacaannya.
5. Bila **tidak ada nota** (contoh: honor harian, parkir), buat **kwitansi sederhana** bermaterai bila perlu, ditandatangani penerima, lalu foto kwitansinya. Jangan mengosongkan bukti.
6. Klik **Simpan sebagai Draf**. Status menjadi "Menunggu Verifikasi".
7. Ketua RT memeriksa dan menyetujui. Setelah disetujui, status menjadi "Terverifikasi" dan transaksi tampil di laporan publik.
8. Simpan **nota fisik** dalam map arsip dan beri nomor sesuai nomor transaksi di website.

**Aturan bon:**

- Satu transaksi = minimal satu foto bukti.
- Bukti tidak boleh diganti setelah terverifikasi. Jika salah unggah, buat koreksi dengan catatan, jangan hapus.
- Tutup (sensor) data sensitif di bukti, seperti nomor rekening lengkap, nomor HP pribadi, dan saldo rekening, sebelum diunggah.
- Bukti transfer: tampilkan tanggal, nominal, dan nama pengirim/penerima (bila boleh dipublikasikan).

**Contoh tampilan formulir upload bon:**

```
+------------------------------------+
|  Tambah Pengeluaran                |
+------------------------------------+
|  Tanggal      [ 17-10-2026 ]       |
|  Kategori     [ Kebersihan  v ]    |
|  Uraian       [ Honor petugas ... ]|
|  Jumlah (Rp)  [ 350000 ]           |
|  Penerima     [ Pak ... ]          |
|  Terkait acara[ - pilih -   v ]    |
|                                    |
|  Bukti / Bon (wajib)               |
|  +------------------------------+  |
|  |   [ + Pilih foto / Ambil ]   |  |
|  |   JPG / PNG / PDF, maks 5 MB |  |
|  +------------------------------+  |
|                                    |
|  [ Simpan Draf ]  [ Ajukan ]       |
+------------------------------------+
```

### 7.3 Perintah Kerja: Upload Bukti Pemasukan

1. Pilih menu **Keuangan > Tambah Pemasukan**.
2. Isi tanggal, kategori (iuran keamanan, kebersihan, sosial, donasi, lainnya), periode (bulan), dan jumlah.
3. Isi sumber: untuk iuran cukup "Iuran warga periode Oktober (jumlah KK: 30)". **Jangan menampilkan nama warga di laporan publik.** Nama dicatat di data internal admin.
4. Unggah foto kwitansi atau tangkapan layar transfer.
5. Simpan dan ajukan ke Ketua RT.

### 7.4 Perintah Kerja: Upload Foto Kegiatan / Acara

Lakukan **paling lambat 3 hari setelah acara selesai**.

1. Pilih menu **Agenda > pilih acara > Tambah Dokumentasi** (atau **Galeri > Album Baru**).
2. Buat album dengan format judul: `[Nama Acara] - [Tanggal]` (contoh: "Kerja Bakti Lingkungan - 25 Okt 2026").
3. Pilih **6 sampai 20 foto terbaik**. Jangan unggah semua foto.
4. Foto yang dianjurkan:
   - Suasana umum dan kegiatan berlangsung
   - Foto bersama peserta
   - Hasil kegiatan (sebelum dan sesudah)
   - Spanduk/papan acara
5. Foto yang **tidak boleh** diunggah:
   - Foto buram, gelap, atau tidak pantas
   - Foto anak-anak yang jelas wajahnya tanpa izin orang tua
   - Foto yang memperlihatkan nomor rumah, plat kendaraan, atau dokumen pribadi secara jelas
   - Foto warga yang meminta tidak dipublikasikan
6. Isi **keterangan** tiap foto secara singkat, dan pilih satu foto sebagai **foto sampul**.
7. Unggah **notulen/laporan singkat** acara (opsional, format PDF).
8. Bila acara memakai dana RT, **tautkan ke transaksi pengeluaran** terkait.
9. Klik **Simpan sebagai Draf**, lalu Ketua RT menyetujui sebelum album terbit.

**Contoh tampilan formulir album:**

```
+------------------------------------+
|  Album Dokumentasi Acara           |
+------------------------------------+
|  Acara        [ Kerja Bakti  v ]   |
|  Judul album  [ Kerja Bakti - ...] |
|  Tanggal      [ 25-10-2026 ]       |
|                                    |
|  Foto (maks 20)                    |
|  [+][img][img][img][img]           |
|  Keterangan tiap foto: [ ... ]     |
|  Foto sampul: ( ) pilih            |
|                                    |
|  [x] Saya sudah memeriksa izin     |
|      foto warga/anak-anak          |
|  [ Simpan Draf ]  [ Terbitkan ]    |
+------------------------------------+
```

### 7.5 Perintah Kerja: Upload Foto Lain yang Dibutuhkan

| Kebutuhan | Cara | Ukuran/format anjuran |
|---|---|---|
| Foto profil pengurus | Menu **Struktur Organisasi > Edit**, unggah foto wajah, minta izin pengurus | Persegi, minimal 400x400 px |
| Logo / banner beranda | Menu **Pengaturan Tampilan > Banner** | Banner 1600x600 px, logo PNG transparan |
| Foto fasilitas (pos ronda, musala, posyandu, taman) | Menu **Profil > Fasilitas** | Landscape 1200x800 px |
| Peta wilayah / batas RT | Menu **Profil > Peta**, unggah gambar atau tanam peta | JPG/PNG, teks peta harus terbaca |
| Dokumen (notulen, SK, laporan PDF) | Menu **Dokumen > Unggah** | PDF, maks 10 MB |
| Foto pengumuman / poster acara | Menu **Pengumuman > Tambah** | Portrait atau persegi, teks terbaca di HP |

### 7.6 Standar Teknis File

- **Format:** JPG atau PNG untuk foto, PDF untuk dokumen.
- **Ukuran:** foto idealnya di bawah 2 MB (kecilkan dahulu jika perlu), bukti dan dokumen maksimal 5-10 MB.
- **Resolusi foto:** sisi terpanjang sekitar 1600 px sudah cukup. Resolusi lebih besar membuat website lambat.
- **Orientasi:** pastikan foto tidak terbalik atau miring.
- **Cadangan:** simpan foto asli di Google Drive/penyimpanan RT, jangan hanya di HP.

### 7.7 Penamaan File (Rapi dan Mudah Dicari)

Format: `jenis_tanggal_uraian.ekstensi` (huruf kecil, tanpa spasi, gunakan garis bawah)

| Jenis | Contoh nama file |
|---|---|
| Bon pengeluaran | `bon_2026-10-17_honor-sampah.jpg` |
| Bukti pemasukan | `masuk_2026-10-05_iuran-oktober.jpg` |
| Foto acara | `acara_2026-10-25_kerja-bakti_01.jpg` |
| Notulen | `notulen_2026-10-30_rapat-warga.pdf` |
| Foto pengurus | `pengurus_ketua-rt.jpg` |

### 7.8 Alur Persetujuan Upload

```
Admin/Bendahara unggah  ->  Status: DRAF
        |
        v
Ajukan ke Ketua RT      ->  Status: MENUNGGU VERIFIKASI
        |
        v
Ketua RT cek bukti      ->  Disetujui: TERVERIFIKASI (tampil publik)
        |                   Ditolak: DIKEMBALIKAN + catatan perbaikan
        v
Arsip fisik disimpan dan diberi nomor
```

### 7.9 Checklist Admin Setiap Upload

- [ ] Data transaksi/acara sudah lengkap dan benar
- [ ] Foto jelas, terbaca, tidak miring, ukuran sudah dikecilkan
- [ ] Data sensitif sudah disensor (rekening, HP pribadi, NIK)
- [ ] Izin foto warga/anak-anak sudah dipastikan
- [ ] Nama file sudah sesuai format
- [ ] Sudah diajukan ke Ketua RT dan status diperiksa
- [ ] Nota fisik/foto asli sudah diarsipkan
- [ ] Batas waktu upload terpenuhi (bon 2 hari, foto acara 3 hari, laporan bulanan tanggal 5)

### 7.10 Jika Terjadi Masalah

| Masalah | Solusi |
|---|---|
| Foto gagal diunggah | Kecilkan ukuran (di bawah 2 MB), cek sinyal, coba lagi |
| Nota hilang / tidak ada | Buat kwitansi pengganti ditandatangani penerima dan diketahui Ketua RT, beri catatan |
| Salah unggah bukti | Jangan hapus, ajukan koreksi dengan catatan, unggah bukti yang benar |
| Warga keberatan fotonya tampil | Segera hapus/blur foto tersebut dan catat permintaan |
| Admin berhalangan | Admin pengganti mengambil alih sesuai SOP |

---

## 8. Masukan Tambahan

1. **Mulai kecil.** Rilis tiga fitur dulu: profil dan struktur, agenda, laporan keuangan. Tambahkan layanan surat setelah warga terbiasa.
2. **Integrasikan dengan WhatsApp.** Kirim tautan laporan bulanan ke grup RT.
3. **Sediakan versi cetak.** Tempel ringkasan keuangan di pos ronda.
4. **Kotak saran/aduan online**, dengan jawaban pengurus yang bisa dilihat bersama.
5. **Pilih platform yang mudah dirawat.** WordPress cocok untuk pengurus awam. Google Sheets yang ditampilkan di website adalah solusi murah untuk keuangan. Aplikasi buatan sendiri butuh pengembang tetap.
6. **Biaya:** hosting dan domain sekitar Rp300 ribu–Rp1 juta per tahun (perkiraan). Catat pula biaya ini di laporan keuangan.
7. **Domain mudah diingat**, misalnya `rt09rw08sukamulya.id`.

---

## 9. Tahapan Implementasi

| Tahap | Kegiatan | Perkiraan waktu |
|---|---|---|
| 1 | Rapat pengurus dan warga, persetujuan, penunjukan admin | 1–2 minggu |
| 2 | Kumpulkan data (struktur, profil, data warga, template keuangan) | 2 minggu |
| 3 | Pilih template, atur warna dan huruf, bangun modul inti | 2–4 minggu |
| 4 | Uji coba dengan beberapa warga (termasuk lansia), perbaiki | 1–2 minggu |
| 5 | Peluncuran dan sosialisasi lewat WhatsApp dan pengumuman | 1 minggu |
| 6 | Evaluasi tiap 3 bulan, tambah fitur | Berkelanjutan |

---

## 10. Checklist Sebelum Peluncuran

- [ ] Persetujuan warga dalam musyawarah sudah ada.
- [ ] Admin dan pengganti admin sudah ditunjuk.
- [ ] Tidak ada NIK/no. KK/alamat lengkap yang tampil.
- [ ] Semua transaksi awal punya bukti.
- [ ] Tampilan sudah dicek di HP (layar kecil).
- [ ] HTTPS aktif dan backup berjalan.
- [ ] Kontak darurat dan peta sudah benar.
- [ ] SOP input data dan jadwal laporan sudah disepakati.
- [ ] Admin dan bendahara sudah dilatih cara upload bon dan foto (Bagian 7).
- [ ] Formulir upload sudah diuji dengan satu bon dan satu album contoh.
