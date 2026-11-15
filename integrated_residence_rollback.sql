-- Rollback aman untuk modul rumah. Menolak penghapusan implisit data: tinjau/ekspor isi tabel lebih dulu.
-- Jalankan DROP berikut hanya bila kedua SELECT menghasilkan 0 baris.
SELECT COUNT(*) AS residence_rows_must_be_zero FROM rt_family_residences;
SELECT COUNT(*) AS house_rows_must_be_zero FROM rt_houses;
-- DROP TABLE rt_family_residences;
-- DROP TABLE rt_houses;
-- Kolom/tabel Master KK dan undangan tidak dihapus karena mungkin sudah dipakai data operasional.
