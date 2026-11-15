-- Master KK, Rumah & Domisili terintegrasi (non-destruktif).
-- Jalankan setelah backup. Tidak ada DROP/DELETE atau perubahan data existing.
ALTER TABLE `kk_families` MODIFY `owner_citizen_id` INT UNSIGNED NULL;
ALTER TABLE `kk_families` ADD COLUMN IF NOT EXISTS `source` VARCHAR(20) NOT NULL DEFAULT 'pengajuan_warga';
ALTER TABLE `kk_families` ADD COLUMN IF NOT EXISTS `verified_by` INT UNSIGNED NULL;
ALTER TABLE `kk_families` ADD COLUMN IF NOT EXISTS `verified_at` DATETIME NULL;
ALTER TABLE `kk_scan_requests` MODIFY `citizen_id` INT UNSIGNED NULL;
ALTER TABLE `kk_scan_requests` MODIFY `status` ENUM('pending','approved','revision_requested','rejected','draft') NOT NULL DEFAULT 'pending';
ALTER TABLE `kk_scan_requests` ADD COLUMN IF NOT EXISTS `source` VARCHAR(20) NOT NULL DEFAULT 'warga';
ALTER TABLE `kk_scan_requests` ADD COLUMN IF NOT EXISTS `created_by_admin` INT UNSIGNED NULL;
ALTER TABLE `kk_scan_requests` ADD COLUMN IF NOT EXISTS `updated_at` DATETIME NULL;

CREATE TABLE IF NOT EXISTS `account_invitations` (
 `id` INT UNSIGNED NOT NULL AUTO_INCREMENT, `family_id` INT UNSIGNED NOT NULL, `token_hash` CHAR(64) NOT NULL,
 `created_by` INT UNSIGNED NULL, `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, `expires_at` DATETIME NOT NULL,
 `attempts` TINYINT UNSIGNED NOT NULL DEFAULT 0, `used_at` DATETIME NULL, `revoked_at` DATETIME NULL,
 `citizen_id` INT UNSIGNED NULL, `member_id` INT UNSIGNED NULL, PRIMARY KEY (`id`), UNIQUE KEY `uq_invite_token` (`token_hash`),
 KEY `idx_invite_family` (`family_id`,`used_at`,`revoked_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `rt_houses` (
 `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1, `house_code` VARCHAR(40) NOT NULL,
 `house_number` VARCHAR(30) NULL, `block_name` VARCHAR(100) NOT NULL DEFAULT '', `address_text` VARCHAR(500) NOT NULL,
 `landmark` VARCHAR(190) NOT NULL DEFAULT '', `building_type` VARCHAR(30) NOT NULL DEFAULT 'rumah',
 `occupancy_status` VARCHAR(20) NOT NULL DEFAULT 'dihuni', `notes` VARCHAR(500) NOT NULL DEFAULT '', `created_by` INT UNSIGNED NULL,
 `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, `updated_at` DATETIME NULL, PRIMARY KEY (`id`),
 UNIQUE KEY `uq_house_code` (`tenant_id`,`house_code`), KEY `idx_house_address` (`tenant_id`,`house_number`,`block_name`),
 KEY `idx_house_status` (`tenant_id`,`occupancy_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `rt_family_residences` (
 `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1, `family_id` INT UNSIGNED NOT NULL,
 `house_id` BIGINT UNSIGNED NOT NULL, `occupancy_relation` VARCHAR(20) NOT NULL DEFAULT 'lainnya', `start_date` DATE NOT NULL,
 `end_date` DATE NULL, `verification_status` VARCHAR(20) NOT NULL DEFAULT 'pending', `verified_by` INT UNSIGNED NULL,
 `verified_at` DATETIME NULL, `created_by` INT UNSIGNED NULL, `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_at` DATETIME NULL, PRIMARY KEY (`id`), KEY `idx_res_family_active` (`tenant_id`,`family_id`,`end_date`),
 KEY `idx_res_house_active` (`tenant_id`,`house_id`,`end_date`), KEY `idx_res_verify` (`tenant_id`,`verification_status`),
 CONSTRAINT `fk_residence_house` FOREIGN KEY (`house_id`) REFERENCES `rt_houses` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
