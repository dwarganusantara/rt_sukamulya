# Master KK, Rumah & Domisili Terintegrasi

Implementasi ini memperluas Master KK existing tanpa membuat master keluarga atau penduduk baru.

## Sumber data

- `kk_families` dan `kk_members`: identitas keluarga/penduduk serta alamat pada dokumen KK.
- `rt_houses`: bangunan/lokasi fisik. Kode rumah tidak berubah saat penghuni berganti.
- `rt_family_residences`: hubungan historis KK–rumah. Relasi aktif memiliki `end_date = NULL`.
- `citizens`: akun login opsional; KK tetap dapat dikelola tanpa akun.

Alamat dokumen KK dan alamat domisili tidak saling menimpa. Modul Bansos mengutamakan domisili aktif terverifikasi dan memakai alamat KK sebagai fallback.

## Route

- `GET /admin/keluarga` — dashboard gabungan KK, rumah, akun, dan layanan.
- `GET /admin/rumah` — daftar rumah.
- `GET|POST /admin/rumah/baru` dan `/admin/rumah/{id}/ubah` — tambah/ubah rumah.
- `GET /admin/rumah/{id}` — penghuni dan histori rumah.
- `GET|POST /admin/rumah/tautkan` — tautkan atau pindahkan KK.
- `POST /admin/domisili/{id}/verifikasi` — verifikasi domisili.

## Konsistensi transaksi

Penautan mengunci baris keluarga. Saat keluarga pindah, seluruh relasi aktif lama ditutup pada satu hari sebelum `start_date`, kemudian satu relasi baru dibuat dalam transaksi yang sama. Ini mencegah dua domisili aktif dari request bersamaan.

## Hak akses

- `rumah.read`: admin, ketua, sekretaris, operator, auditor.
- `rumah.write` dan `domisili.link`: admin, ketua, sekretaris, operator.
- `domisili.verify`: admin, ketua, sekretaris.

Semua perubahan rumah, penautan, perpindahan, dan verifikasi masuk Audit Aktivitas.

## Migrasi dan rollback

- Upgrade: `integrated_residence_upgrade.sql` (aditif/non-destruktif).
- Rollback: `integrated_residence_rollback.sql`. Perintah `DROP` sengaja dikomentari dan hanya boleh dijalankan setelah kedua tabel dipastikan kosong.
- Cadangan lokal sebelum aktivasi tersimpan di `tmp/db-backups/before-integrated-residence-20261114.sql`.

## Pengujian

`php tests/house_master_smoke.php` memeriksa pembuatan rumah 12A, tautan/verifikasi KK, perpindahan ke rumah 13, histori lama, satu domisili aktif, halaman gabungan, serta penolakan perubahan untuk auditor.
