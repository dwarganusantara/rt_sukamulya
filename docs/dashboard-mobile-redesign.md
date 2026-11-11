# Redesign dashboard portal warga mobile

Dashboard setelah login: `warga` -> `Citizen::index` -> `citizen/dashboard`.
Breakpoint desain baru: maksimal 1023px. Desktop 1024px ke atas mempertahankan header, hero, favorit, heksagon, footer, dan widget existing.

## File implementasi

- `application/controllers/Citizen.php`: menambahkan pemanggilan model agregat baca pada index setelah guard login existing.
- `application/models/Dashboard_summary_model.php`: warga aktif dari master anggota KK terverifikasi; kepala keluarga aktif dihitung per keluarga; surat diajukan/diproses dibatasi citizen_id sesi.
- `application/views/citizen/dashboard.php`: memasang stylesheet dan partial mobile; markup desktop dipertahankan.
- `application/views/citizen/dashboard_mobile.php`: identitas dinamis, notifikasi pesan, akun, banner, statistik, menu registry, navigasi bawah dan dialog Lainnya.
- `assets/dashboard-mobile.css`: aturan layout hanya di media query mobile/tablet; komponen mobile disembunyikan di desktop.
- `assets/dashboard-mobile.js`: dialog, penutupan akun, skip link responsif, dan integrasi pembuka chat existing.
- `assets/img/dashboard/mobile-gateway.webp`: ilustrasi banner baru, 1100 x 458, sekitar 174 KB. Papan nama diisi HTML dari pengaturan. Gambar kustom admin tetap dihormati.
- `tests/dashboard_mobile_smoke.php`: pengujian HTTP akun sementara, dibersihkan setelah pengujian.
- `tests/dashboard_summary_test.php`: pengujian batas kepemilikan dan status surat tanpa mengubah database.

Tidak ada migrasi database, endpoint baru, perubahan status transaksi, atau perubahan autentikasi. Tidak ada angka contoh disimpan untuk statistik. Jika modul tidak tersedia atau fitur dinonaktifkan, statistik terkait ditampilkan sebagai tanda tidak tersedia, bukan nol rekaan.

## Mapping layanan existing

| Menu | Route | Controller / sumber |
| --- | --- | --- |
| Profil Saya | warga/profil | Citizen::profile, citizens |
| Kartu Keluarga | warga/keluarga | Keluarga::index, Kk_model |
| Pengajuan Surat | warga/layanan | Citizen::services, Letter_model |
| Riwayat Surat | warga/riwayat | Citizen::history, Letter_model |
| Iuran Warga | warga/iuran | Citizen::dues, dues/due_payments |
| Laporan Kas | keuangan | Site::finance, transaksi verified |
| Pengumuman | pengumuman | Site::listing, posts published |
| Agenda Kegiatan | agenda | Site::listing, posts published |
| Galeri Kegiatan | galeri | Site::listing, posts published |
| Jadwal Ronda | warga/ronda | Kegiatan::ronda_warga, Ops_model/patrol_schedules |
| Pengaduan & Aspirasi | warga/pengaduan | Pengaduan::index, Ops_model/complaints |
| Chat Pengurus | warga/chat | Citizen::chat -> warga#live-chat, Chat_model/widget existing |

Label, ikon, urutan dan visibilitas kartu tetap berasal dari Dashboard_menu_model. Feature_gate hook existing tetap menolak fitur nonaktif di server. Menu akun memakai warga/profil, warga/password, warga/logout. Lainnya menampilkan route registry yang diizinkan. Badge lonceng menghitung pesan pengurus yang belum dibaca milik akun; jumlah tagihan tidak dilabeli sebagai pesan belum dibaca.

## Validasi

- PHP lint: controller, model dan kedua view lulus.
- `php tests/dashboard_mobile_smoke.php`: login akun aktif, penolakan guest/nonaktif, 12 layanan dan pengaturan, redirect chat, markup identitas dan semua menu lulus. Tidak mengirim surat, pengaduan, pesan, atau pembayaran selama tes.
- `php tests/dashboard_summary_test.php`: surat akun lain, draft dan surat disetujui tidak masuk hitungan; feature disabled dan guest ditangani.
- Browser Chrome otomatis: 320, 390, 600, 768, 1023, 1440px; tanpa overflow horizontal dan error JavaScript. Papan nama tidak terpotong. Panel Lainnya, Escape, menu akun dan jarak aman navigasi bawah diuji.
- Desktop 1440px: perbandingan screenshot komponen mobile aktif versus komponen mobile/CSS baru dilepas menghasilkan gambar identik setelah semua gambar selesai dimuat.
- Statistik pada pratinjau adalah angka master aktual; nama Warga Contoh adalah akun sementara untuk pengambilan gambar, bukan data statistik tambahan.

Batas validasi: browser desktop dengan viewport responsif, belum pengujian langsung di perangkat iOS/Android. Guard kepemilikan fitur existing dipertahankan; tes ini tidak menggantikan audit keamanan menyeluruh seluruh modul.

## Sumber banner

Dibuat dengan built-in ImageGen. Prompt: ilustrasi 3D landscape gerbang lingkungan RT Indonesia, marun dan emas, rumah beratap genteng, bendera merah putih, taman tropis, tanpa orang dan tanpa tulisan, papan gerbang kosong untuk identitas dinamis. File sumber tetap disimpan non-destruktif di direktori generated_images; aset proyek menggunakan WebP terkompresi.
