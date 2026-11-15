-- Rollback data_integrity_upgrade.sql. Hanya melepas constraint; tidak menghapus data operasional.
-- Tabel citizen_resident_links TIDAK dihapus otomatis karena berisi hasil verifikasi akun.
-- Bila memang ingin dihapus, ekspor dulu isinya lalu jalankan baris DROP yang dikomentari di bawah.
ALTER TABLE `kk_families` DROP FOREIGN KEY IF EXISTS `fk_family_owner_citizen`;
ALTER TABLE `rt_family_residences` DROP FOREIGN KEY IF EXISTS `fk_residence_family`;
ALTER TABLE `kk_scan_requests` DROP FOREIGN KEY IF EXISTS `fk_kkreq_citizen`;
ALTER TABLE `resident_mutations` DROP FOREIGN KEY IF EXISTS `fk_mutation_family`, DROP FOREIGN KEY IF EXISTS `fk_mutation_member`, DROP FOREIGN KEY IF EXISTS `fk_mutation_citizen`;
ALTER TABLE `aid_recipients` DROP FOREIGN KEY IF EXISTS `fk_aid_program`, DROP FOREIGN KEY IF EXISTS `fk_aid_family`, DROP FOREIGN KEY IF EXISTS `fk_aid_citizen`;

ALTER TABLE `rt_family_residences` DROP INDEX IF EXISTS `uq_res_one_active`, DROP COLUMN IF EXISTS `active_family_key`;
ALTER TABLE `kk_scan_requests` DROP INDEX IF EXISTS `uq_kkreq_one_pending`, DROP COLUMN IF EXISTS `active_citizen_key`;

SELECT COUNT(*) AS link_rows_review_before_drop FROM citizen_resident_links;
-- DROP TABLE citizen_resident_links;
