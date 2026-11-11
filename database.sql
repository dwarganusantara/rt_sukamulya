CREATE DATABASE IF NOT EXISTS `sukamulya` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `sukamulya`;

CREATE TABLE IF NOT EXISTS `settings` (
  `setting_key` varchar(80) NOT NULL,
  `setting_value` text NOT NULL,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `pages` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(160) NOT NULL,
  `slug` varchar(180) NOT NULL,
  `body` longtext NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `sort_order` int NOT NULL DEFAULT 0,
  `show_in_menu` tinyint(1) NOT NULL DEFAULT 1,
  `is_published` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`), UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `admins` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(80) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  PRIMARY KEY (`id`), UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `settings` (`setting_key`,`setting_value`) VALUES
('site_name','RT 009/008 Suka Mulya IV'),('brand_label','RT 009/008'),('brand_subtitle','Suka Mulya IV'),('tagline','Website lingkungan RT 009/008 Suka Mulya IV'),
('hero_title','RT 009/008'),('hero_accent','Suka Mulya IV'),
('hero_text','Bersama Membangun Lingkungan yang Aman, Nyaman, Rukun dan Sejahtera.'),
('about_title','Tentang RT 009/008'),
('about_text','RT 009/008 Suka Mulya IV adalah bagian dari lingkungan masyarakat yang menjunjung tinggi nilai gotong royong, kebersamaan, dan kepedulian antarwarga.'),
('address','Suka Mulya IV'),('phone',''),('email',''),
('footer_text','© RT 009/008 Suka Mulya IV.'),('hero_image','assets/mockup/hero-neighborhood.png'),('logo','assets/mockup/logo.png')
ON DUPLICATE KEY UPDATE `setting_value`=VALUES(`setting_value`);
