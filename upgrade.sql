USE `sukamulya`;
ALTER TABLE `admins` ADD COLUMN `role` enum('admin','operator','bendahara','ketua') NOT NULL DEFAULT 'admin';

CREATE TABLE IF NOT EXISTS `posts` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `type` enum('pengumuman','agenda','galeri') NOT NULL,
  `title` varchar(180) NOT NULL,
  `body` text NOT NULL,
  `event_date` date DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('draft','pending','published') NOT NULL DEFAULT 'draft',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`), KEY `type_status_date` (`type`,`status`,`event_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `officials` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(140) NOT NULL,
  `position` varchar(140) NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `contact` varchar(30) NOT NULL DEFAULT '',
  `term_start` year DEFAULT NULL,
  `term_end` year DEFAULT NULL,
  `sort_order` int NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `transactions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `tx_date` date NOT NULL,
  `type` enum('masuk','keluar') NOT NULL,
  `category` varchar(100) NOT NULL,
  `description` varchar(255) NOT NULL,
  `amount` decimal(14,2) NOT NULL,
  `period` char(7) NOT NULL,
  `counterparty` varchar(140) NOT NULL DEFAULT '',
  `proof` varchar(255) NOT NULL DEFAULT '',
  `status` enum('draft','pending','verified','returned') NOT NULL DEFAULT 'verified',
  `review_note` text DEFAULT NULL,
  `created_by` int unsigned NOT NULL,
  `verified_by` int unsigned DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `reverses_id` int unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`), KEY `period_status` (`period`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `admin_id` int unsigned NOT NULL,
  `action` varchar(80) NOT NULL,
  `subject` varchar(100) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `settings` (`setting_key`,`setting_value`) VALUES
('brand_label','RT 09 / RW 08'),('brand_subtitle','SUKAMULYA'),('map_url',''),('emergency_contact',''),('households','0'),('residents','0'),
('female_residents','0'),('male_residents','0'),('whatsapp','')
ON DUPLICATE KEY UPDATE `setting_value`=`setting_value`;
