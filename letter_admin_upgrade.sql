CREATE TABLE IF NOT EXISTS `letter_types` (
  `type_key` varchar(40) NOT NULL,
  `title` varchar(160) NOT NULL,
  `category` varchar(40) NOT NULL,
  `description` varchar(255) NOT NULL,
  `body_template` text DEFAULT NULL,
  `icon_path` varchar(255) NOT NULL DEFAULT '',
  `sort_order` int NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`type_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT IGNORE INTO `letter_types` (`type_key`,`title`,`category`,`description`,`sort_order`) VALUES
('surat_pengantar','Surat Pengantar','Kependudukan','Untuk berbagai keperluan administrasi ke kelurahan atau instansi lainnya.',1),
('domisili','Surat Keterangan Domisili','Kependudukan','Keterangan tempat tinggal warga RT 009/008.',2),
('kematian','Surat Keterangan Kematian','Keterangan','Laporan warga yang meninggal dunia.',3),
('kelahiran','Surat Pengantar Kelahiran','Keterangan','Laporan kelahiran bayi di lingkungan RT.',4),
('pindah','Surat Pengantar Pindah Domisili','Kependudukan','Pengurusan perpindahan alamat domisili.',5),
('warga_baru','Surat Pengantar Warga Baru','Kependudukan','Laporan warga yang baru menetap di lingkungan RT.',6),
('ktp_kk','Surat Pengantar KTP / KK','Kependudukan','Pengurusan atau perubahan dokumen kependudukan.',7),
('skck','Surat Pengantar SKCK','Keterangan','Pengantar untuk keperluan pengurusan SKCK.',8),
('nikah','Surat Pengantar Nikah','Keterangan','Pengurusan administrasi pernikahan.',9),
('usaha','Surat Keterangan Usaha','Keterangan','Pengantar untuk keperluan administrasi usaha warga.',10),
('tidak_mampu','Surat Pengantar Tidak Mampu','Keterangan','Pengantar untuk pengajuan bantuan sosial atau pendidikan.',11),
('ahli_waris','Surat Pengantar Ahli Waris','Keterangan','Pengantar proses administrasi ahli waris.',12),
('kehilangan','Surat Pengantar Kehilangan','Keterangan','Pengantar untuk pengurusan dokumen yang hilang.',13),
('kegiatan','Surat Pengantar Kegiatan Warga','Kegiatan','Pemberitahuan kegiatan atau acara lingkungan.',14),
('renovasi','Surat Pengantar Renovasi Rumah','Kegiatan','Pemberitahuan kegiatan renovasi rumah.',15),
('aduan','Aduan / Saran','Lainnya','Sampaikan keluhan atau saran untuk pengurus RT.',16),
('lainnya','Keperluan Lainnya','Lainnya','Ajukan keperluan administrasi lainnya.',17);
ALTER TABLE `service_request_details` ADD COLUMN `extra_data` text DEFAULT NULL AFTER `purpose`;
CREATE TABLE IF NOT EXISTS `service_request_documents` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `request_id` int unsigned NOT NULL,
  `label` varchar(100) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`), KEY `request_id` (`request_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
ALTER TABLE `service_requests` MODIFY `status` enum('draft','diajukan','diproses','disetujui','ditolak','selesai') NOT NULL DEFAULT 'diajukan';
