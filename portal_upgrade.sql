USE `sukamulya`;

ALTER TABLE `posts` ADD COLUMN `event_time` time DEFAULT NULL AFTER `event_date`;
ALTER TABLE `posts` ADD COLUMN `event_location` varchar(160) NOT NULL DEFAULT '' AFTER `event_time`;

CREATE TABLE IF NOT EXISTS `quick_links` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `subtitle` varchar(160) NOT NULL DEFAULT '',
  `icon_image` varchar(255) NOT NULL DEFAULT '',
  `url_path` varchar(180) NOT NULL,
  `sort_order` int NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `citizens` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(80) NOT NULL,
  `display_name` varchar(140) NOT NULL,
  `full_name` varchar(140) DEFAULT NULL,
  `gender` varchar(10) NOT NULL DEFAULT 'female',
  `address` varchar(255) DEFAULT NULL,
  `phone` varchar(25) DEFAULT NULL,
  `email` varchar(190) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`), UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `service_requests` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `citizen_id` int unsigned NOT NULL,
  `type` enum('surat_pengantar','warga_baru','pindah','kelahiran','kematian','aduan','lainnya') NOT NULL,
  `subject` varchar(180) NOT NULL,
  `description` text NOT NULL,
  `status` enum('diajukan','diproses','disetujui','ditolak','selesai') NOT NULL DEFAULT 'diajukan',
  `admin_note` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`), KEY `citizen_status` (`citizen_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `dues` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `citizen_id` int unsigned NOT NULL,
  `period` char(7) NOT NULL,
  `category` varchar(100) NOT NULL,
  `amount_due` decimal(14,2) NOT NULL,
  `amount_paid` decimal(14,2) NOT NULL DEFAULT 0,
  `note` varchar(255) NOT NULL DEFAULT '',
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`), UNIQUE KEY `citizen_period_category` (`citizen_id`,`period`,`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `quick_links` (`title`,`subtitle`,`icon_image`,`url_path`,`sort_order`) VALUES
('Pengumuman','Info terbaru untuk warga','assets/mockup/icon-pengumuman.png','pengumuman',1),
('Agenda','Jadwal kegiatan RT','assets/mockup/icon-agenda.png','agenda',2),
('Profil RT','Struktur & informasi','assets/mockup/icon-profil.png','profil',3),
('Layanan Warga','Surat & pengajuan','assets/mockup/icon-layanan.png','warga',4),
('Iuran Warga','Informasi iuran lingkungan','assets/mockup/icon-iuran.png','warga/iuran',5),
('Galeri Kegiatan','Dokumentasi warga','assets/mockup/icon-galeri.png','galeri',6),
('Kontak RT','Hubungi pengurus','assets/mockup/icon-kontak.png','kontak',7);

INSERT IGNORE INTO `settings` (`setting_key`,`setting_value`) VALUES
('brand_label','RT 009/008'),('brand_subtitle','Suka Mulya IV'),
('hero_image','assets/mockup/hero-neighborhood.png'),
('logo','assets/mockup/logo.png'),
('about_image','assets/mockup/about-neighborhood.png'),
('hero_accent','Suka Mulya IV'),('hero_kicker','SELAMAT DATANG DI WEBSITE'),
('value1_title','Rukun'),('value1_text','Menjaga silaturahmi'),('value1_icon','assets/mockup/icon-rukun.png'),
('value2_title','Bersih'),('value2_text','Lingkungan yang sehat'),('value2_icon','assets/mockup/icon-bersih.png'),
('value3_title','Aman'),('value3_text','Nyaman untuk semua warga'),('value3_icon','assets/mockup/icon-aman.png'),
('announcement_icon','assets/mockup/icon-announcement.png'),('agenda_icon','assets/mockup/icon-calendar-small.png'),
('footer_wave','assets/mockup/footer-wave.png'),('slogan','Satu Warga, Satu Keluarga');

UPDATE `settings` SET `setting_value`='assets/mockup/hero-neighborhood.png' WHERE `setting_key`='hero_image' AND `setting_value`='';
UPDATE `settings` SET `setting_value`='assets/mockup/logo.png' WHERE `setting_key`='logo' AND `setting_value`='';
UPDATE `settings` SET `setting_value`='RT 009/008' WHERE `setting_key`='brand_label' AND `setting_value`='RT 09 / RW 08';
UPDATE `settings` SET `setting_value`='Suka Mulya IV' WHERE `setting_key`='brand_subtitle' AND `setting_value`='SUKAMULYA';
