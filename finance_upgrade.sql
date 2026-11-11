USE `sukamulya`;

CREATE TABLE IF NOT EXISTS `billing_campaigns` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(140) NOT NULL,
  `due_type` enum('rutin','non_rutin','sosial') NOT NULL,
  `category` varchar(100) NOT NULL,
  `period` char(7) NOT NULL,
  `amount` decimal(14,2) NOT NULL,
  `note` varchar(255) NOT NULL DEFAULT '',
  `created_by` int unsigned NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`), KEY `period_type` (`period`,`due_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `dues` ADD COLUMN IF NOT EXISTS `due_type` enum('rutin','non_rutin','sosial') NOT NULL DEFAULT 'rutin' AFTER `category`;
ALTER TABLE `dues` ADD COLUMN IF NOT EXISTS `campaign_id` int unsigned DEFAULT NULL AFTER `due_type`;

CREATE TABLE IF NOT EXISTS `due_payments` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `due_id` int unsigned NOT NULL,
  `citizen_id` int unsigned NOT NULL,
  `amount` decimal(14,2) NOT NULL,
  `method` enum('transfer','tunai') NOT NULL,
  `proof` varchar(255) DEFAULT NULL,
  `status` enum('pending','verified','returned') NOT NULL DEFAULT 'pending',
  `review_note` varchar(255) NOT NULL DEFAULT '',
  `transaction_id` int unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reviewed_at` datetime DEFAULT NULL,
  `reviewed_by` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id`), KEY `due_status` (`due_id`,`status`), KEY `citizen_id` (`citizen_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
