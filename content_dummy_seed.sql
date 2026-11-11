-- Data contoh Pengumuman dan Agenda. Aman dijalankan berulang karena setiap INSERT memeriksa judul.
INSERT INTO posts(type,title,body,event_location,image,status,created_at)
SELECT 'pengumuman','Rapat Koordinasi Warga Bulanan','Pengurus mengundang seluruh warga mengikuti rapat koordinasi bulanan.','Balai Warga RT 009/008','uploads/content-dummy/rapat-warga.png','published','2026-10-28 18:00:00'
WHERE NOT EXISTS(SELECT 1 FROM posts WHERE type='pengumuman' AND title='Rapat Koordinasi Warga Bulanan');
INSERT INTO posts(type,title,body,event_location,image,status,created_at)
SELECT 'pengumuman','Jadwal Fogging Lingkungan','Kegiatan fogging dilaksanakan sebagai langkah pencegahan demam berdarah.','Lingkungan RT 009/008','uploads/content-dummy/fogging-lingkungan.png','published','2026-10-27 09:00:00'
WHERE NOT EXISTS(SELECT 1 FROM posts WHERE type='pengumuman' AND title='Jadwal Fogging Lingkungan');
INSERT INTO posts(type,title,body,event_location,image,status,created_at)
SELECT 'pengumuman','Senam Sehat Bersama Warga','Senam sehat bersama untuk menjaga kebugaran dan kebersamaan warga.','Lapangan Warga RT 009/008','uploads/content-dummy/senam-warga.png','published','2026-10-25 07:00:00'
WHERE NOT EXISTS(SELECT 1 FROM posts WHERE type='pengumuman' AND title='Senam Sehat Bersama Warga');

INSERT INTO posts(type,title,body,event_date,event_time,event_location,image,status,created_at)
SELECT 'agenda','Senam Pagi Bersama','Kegiatan senam pagi rutin untuk seluruh warga.','2026-11-01','07:00:00','Lapangan Warga RT 009/008','uploads/content-dummy/senam-warga.png','published','2026-10-28 10:00:00'
WHERE NOT EXISTS(SELECT 1 FROM posts WHERE type='agenda' AND title='Senam Pagi Bersama');
INSERT INTO posts(type,title,body,event_date,event_time,event_location,image,status,created_at)
SELECT 'agenda','Rapat Koordinasi November','Pertemuan warga membahas program kerja lingkungan.','2026-11-07','19:30:00','Balai Warga RT 009/008','uploads/content-dummy/rapat-warga.png','published','2026-10-28 10:10:00'
WHERE NOT EXISTS(SELECT 1 FROM posts WHERE type='agenda' AND title='Rapat Koordinasi November');
INSERT INTO posts(type,title,body,event_date,event_time,event_location,image,status,created_at)
SELECT 'agenda','Bazar UMKM Warga','Bazar produk makanan, minuman, dan kerajinan hasil usaha warga.','2026-11-15','08:00:00','Jalan Utama RT 009/008','uploads/content-dummy/bazar-warga.png','published','2026-10-28 10:20:00'
WHERE NOT EXISTS(SELECT 1 FROM posts WHERE type='agenda' AND title='Bazar UMKM Warga');
INSERT INTO posts(type,title,body,event_date,event_time,event_location,image,status,created_at)
SELECT 'agenda','Kerja Bakti Akhir Bulan','Gotong royong membersihkan saluran air, taman, dan jalan lingkungan.','2026-11-29','07:00:00','Lingkungan RT 009/008','uploads/gallery-dummy/kerja-bakti-warga.png','published','2026-10-28 10:30:00'
WHERE NOT EXISTS(SELECT 1 FROM posts WHERE type='agenda' AND title='Kerja Bakti Akhir Bulan');
