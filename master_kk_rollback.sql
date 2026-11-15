-- Rollback master_kk_upgrade.sql
-- PERHATIAN: langkah 1 & 2 akan GAGAL bila sudah ada KK tanpa akun / pengajuan input admin / status draft.
-- Itu disengaja agar rollback tidak menghapus data. Tinjau dulu datanya:
--   SELECT id FROM kk_families WHERE owner_citizen_id IS NULL;
--   SELECT id,status FROM kk_scan_requests WHERE citizen_id IS NULL OR status='draft';
-- Pindahkan/arsipkan baris tersebut secara manual (setelah backup), baru jalankan skrip ini.

DROP TABLE IF EXISTS `account_invitations`;

ALTER TABLE `kk_scan_requests`
  DROP KEY `idx_kk_req_source`,
  DROP COLUMN `updated_at`,
  DROP COLUMN `created_by_admin`,
  DROP COLUMN `source`,
  MODIFY `status` ENUM('pending','approved','revision_requested','rejected') NOT NULL DEFAULT 'pending',
  MODIFY `citizen_id` INT(10) UNSIGNED NOT NULL;

ALTER TABLE `kk_families`
  DROP COLUMN `verified_at`,
  DROP COLUMN `verified_by`,
  DROP COLUMN `source`,
  MODIFY `owner_citizen_id` INT(10) UNSIGNED NOT NULL;
