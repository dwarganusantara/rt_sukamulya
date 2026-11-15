-- =====================================================================
-- Master KK terintegrasi (Blueprint Master KK & Layanan Warga, Tahap 1 + undangan akun)
-- NON-DESTRUKTIF: hanya melonggarkan NOT NULL, menambah kolom, nilai ENUM, dan tabel baru.
-- Tidak ada data yang dihapus/diubah. Backup dulu sebelum menjalankan:
--   C:\xampp\mysql\bin\mysqldump -uroot sukamulya > backup_sebelum_master_kk.sql
-- Rollback: master_kk_rollback.sql
-- =====================================================================

-- 1) KK boleh ada tanpa akun warga (prinsip: data penduduk != akun login).
--    UNIQUE(owner_citizen_id) tetap: satu akun maksimal satu KK; banyak NULL diperbolehkan MySQL.
ALTER TABLE `kk_families`
  MODIFY `owner_citizen_id` INT(10) UNSIGNED NULL,
  ADD COLUMN `source` VARCHAR(20) NOT NULL DEFAULT 'pengajuan_warga' AFTER `fields_json`,
  ADD COLUMN `verified_by` INT(10) UNSIGNED NULL AFTER `source`,
  ADD COLUMN `verified_at` DATETIME NULL AFTER `verified_by`;

-- 2) Pengajuan KK bisa dibuat admin (tanpa akun warga) dan disimpan sebagai draft.
ALTER TABLE `kk_scan_requests`
  MODIFY `citizen_id` INT(10) UNSIGNED NULL,
  MODIFY `status` ENUM('pending','approved','revision_requested','rejected','draft') NOT NULL DEFAULT 'pending',
  ADD COLUMN `source` VARCHAR(20) NOT NULL DEFAULT 'warga' AFTER `citizen_id`,
  ADD COLUMN `created_by_admin` INT(10) UNSIGNED NULL AFTER `source`,
  ADD COLUMN `updated_at` DATETIME NULL AFTER `created_at`,
  ADD KEY `idx_kk_req_source` (`source`, `status`);

-- 3) Undangan aktivasi akun warga (token sekali pakai, hanya hash yang disimpan).
CREATE TABLE IF NOT EXISTS `account_invitations` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `family_id` INT(10) UNSIGNED NOT NULL,
  `token_hash` CHAR(64) NOT NULL,
  `created_by` INT(10) UNSIGNED NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` DATETIME NOT NULL,
  `attempts` TINYINT(3) UNSIGNED NOT NULL DEFAULT 0,
  `used_at` DATETIME NULL,
  `revoked_at` DATETIME NULL,
  `citizen_id` INT(10) UNSIGNED NULL,
  `member_id` INT(10) UNSIGNED NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_invite_token` (`token_hash`),
  KEY `idx_invite_family` (`family_id`, `used_at`, `revoked_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
