INSERT INTO posts(type,title,body,event_location,image,status,created_at)
SELECT 'galeri','Kerja Bakti Lingkungan','Warga bergotong royong membersihkan jalan, taman, dan area sekitar lingkungan RT.','Lingkungan RT 009/008','uploads/gallery-dummy/kerja-bakti-warga.png','published','2026-10-26 08:15:00'
WHERE NOT EXISTS(SELECT 1 FROM posts WHERE type='galeri' AND title='Kerja Bakti Lingkungan');

INSERT INTO posts(type,title,body,event_location,image,status,created_at)
SELECT 'galeri','Semarak Lomba Kemerdekaan','Anak-anak dan keluarga warga mengikuti perlombaan kemerdekaan dengan penuh semangat.','Lapangan Warga RT 009/008','uploads/gallery-dummy/lomba-kemerdekaan.png','published','2026-08-17 10:30:00'
WHERE NOT EXISTS(SELECT 1 FROM posts WHERE type='galeri' AND title='Semarak Lomba Kemerdekaan');

INSERT INTO posts(type,title,body,event_location,image,status,created_at)
SELECT 'galeri','Kegiatan Posyandu Balita','Pelayanan kesehatan rutin bagi ibu dan balita bersama kader lingkungan.','Balai Warga RT 009/008','uploads/gallery-dummy/posyandu-balita.png','published','2026-10-18 09:00:00'
WHERE NOT EXISTS(SELECT 1 FROM posts WHERE type='galeri' AND title='Kegiatan Posyandu Balita');

INSERT INTO posts(type,title,body,event_location,image,status,created_at)
SELECT 'galeri','Pengajian dan Silaturahmi Warga','Kegiatan pengajian rutin sekaligus mempererat silaturahmi antarwarga.','Balai Warga RT 009/008','uploads/gallery-dummy/pengajian-warga.png','published','2026-10-12 19:30:00'
WHERE NOT EXISTS(SELECT 1 FROM posts WHERE type='galeri' AND title='Pengajian dan Silaturahmi Warga');
