-- Penguatan integritas data kependudukan (Nov 2026). Non-destruktif: tidak ada DROP/DELETE data.
-- Prasyarat: backup dulu, lalu jalankan bagian "PRA-PEMERIKSAAN" — semua angka harus 0.
-- Target: MariaDB 10.4+ (memakai ADD ... IF NOT EXISTS dan kolom PERSISTENT).
-- Rollback: data_integrity_rollback.sql

-- ===================== PRA-PEMERIKSAAN (wajib 0 semua) =====================
SELECT 'fam.owner yatim' cek, COUNT(*) n FROM kk_families f LEFT JOIN citizens c ON c.id=f.owner_citizen_id WHERE f.owner_citizen_id IS NOT NULL AND c.id IS NULL
UNION ALL SELECT 'domisili.family yatim', COUNT(*) FROM rt_family_residences r LEFT JOIN kk_families f ON f.id=r.family_id WHERE f.id IS NULL
UNION ALL SELECT 'pengajuan.citizen yatim', COUNT(*) FROM kk_scan_requests s LEFT JOIN citizens c ON c.id=s.citizen_id WHERE s.citizen_id IS NOT NULL AND c.id IS NULL
UNION ALL SELECT 'mutasi.family yatim', COUNT(*) FROM resident_mutations m LEFT JOIN kk_families f ON f.id=m.family_id WHERE m.family_id IS NOT NULL AND f.id IS NULL
UNION ALL SELECT 'mutasi.member yatim', COUNT(*) FROM resident_mutations m LEFT JOIN kk_members x ON x.id=m.member_id WHERE m.member_id IS NOT NULL AND x.id IS NULL
UNION ALL SELECT 'mutasi.citizen yatim', COUNT(*) FROM resident_mutations m LEFT JOIN citizens c ON c.id=m.citizen_id WHERE m.citizen_id IS NOT NULL AND c.id IS NULL
UNION ALL SELECT 'bansos.program yatim', COUNT(*) FROM aid_recipients a LEFT JOIN aid_programs p ON p.id=a.program_id WHERE p.id IS NULL
UNION ALL SELECT 'bansos.family yatim', COUNT(*) FROM aid_recipients a LEFT JOIN kk_families f ON f.id=a.family_id WHERE a.family_id IS NOT NULL AND f.id IS NULL
UNION ALL SELECT 'bansos.citizen yatim', COUNT(*) FROM aid_recipients a LEFT JOIN citizens c ON c.id=a.citizen_id WHERE a.citizen_id IS NOT NULL AND c.id IS NULL
UNION ALL SELECT 'domisili aktif ganda', COUNT(*) FROM (SELECT 1 FROM rt_family_residences WHERE end_date IS NULL GROUP BY tenant_id,family_id HAVING COUNT(*)>1) x
UNION ALL SELECT 'pengajuan pending ganda', COUNT(*) FROM (SELECT 1 FROM kk_scan_requests WHERE status='pending' AND citizen_id IS NOT NULL GROUP BY citizen_id HAVING COUNT(*)>1) x;

-- ===================== 1. FOREIGN KEY YANG HILANG =====================
-- ON DELETE RESTRICT (bawaan): data induk tidak bisa dihapus selama masih dirujuk.
-- Aplikasi tidak pernah menghapus citizens/kk_*; hapus program bansos sudah menghapus penerimanya lebih dulu.
ALTER TABLE `kk_families`
  ADD CONSTRAINT `fk_family_owner_citizen` FOREIGN KEY IF NOT EXISTS (`owner_citizen_id`) REFERENCES `citizens` (`id`);
ALTER TABLE `rt_family_residences`
  ADD CONSTRAINT `fk_residence_family` FOREIGN KEY IF NOT EXISTS (`family_id`) REFERENCES `kk_families` (`id`);
ALTER TABLE `kk_scan_requests`
  ADD CONSTRAINT `fk_kkreq_citizen` FOREIGN KEY IF NOT EXISTS (`citizen_id`) REFERENCES `citizens` (`id`);
ALTER TABLE `resident_mutations`
  ADD CONSTRAINT `fk_mutation_family` FOREIGN KEY IF NOT EXISTS (`family_id`) REFERENCES `kk_families` (`id`),
  ADD CONSTRAINT `fk_mutation_member` FOREIGN KEY IF NOT EXISTS (`member_id`) REFERENCES `kk_members` (`id`),
  ADD CONSTRAINT `fk_mutation_citizen` FOREIGN KEY IF NOT EXISTS (`citizen_id`) REFERENCES `citizens` (`id`);
ALTER TABLE `aid_recipients`
  ADD CONSTRAINT `fk_aid_program` FOREIGN KEY IF NOT EXISTS (`program_id`) REFERENCES `aid_programs` (`id`),
  ADD CONSTRAINT `fk_aid_family` FOREIGN KEY IF NOT EXISTS (`family_id`) REFERENCES `kk_families` (`id`),
  ADD CONSTRAINT `fk_aid_citizen` FOREIGN KEY IF NOT EXISTS (`citizen_id`) REFERENCES `citizens` (`id`);

-- ===================== 2. SATU DOMISILI AKTIF PER KK =====================
-- active_family_key = family_id selama end_date NULL, selain itu NULL (NULL boleh berulang di UNIQUE).
ALTER TABLE `rt_family_residences`
  ADD COLUMN IF NOT EXISTS `active_family_key` INT UNSIGNED AS (IF(`end_date` IS NULL, `family_id`, NULL)) PERSISTENT;
ALTER TABLE `rt_family_residences`
  ADD UNIQUE KEY IF NOT EXISTS `uq_res_one_active` (`tenant_id`, `active_family_key`);

-- ===================== 3. SATU PENGAJUAN PENDING PER AKUN =====================
-- Sama dengan aturan Kk_model::submit(); input admin (citizen_id NULL) tidak terkena.
ALTER TABLE `kk_scan_requests`
  ADD COLUMN IF NOT EXISTS `active_citizen_key` INT UNSIGNED AS (IF(`status` = 'pending', `citizen_id`, NULL)) PERSISTENT;
ALTER TABLE `kk_scan_requests`
  ADD UNIQUE KEY IF NOT EXISTS `uq_kkreq_one_pending` (`active_citizen_key`);

-- ===================== 4. TAUTAN AKUN -> ANGGOTA PENDUDUK =====================
-- Satu akun = satu penduduk, satu penduduk = satu akun.
CREATE TABLE IF NOT EXISTS `citizen_resident_links` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `citizen_id` INT UNSIGNED NOT NULL,
  `member_id` INT UNSIGNED NOT NULL,
  `verified_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `verification_source` VARCHAR(30) NOT NULL DEFAULT 'admin',
  `verified_by` INT UNSIGNED NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_link_citizen` (`citizen_id`),
  UNIQUE KEY `uq_link_member` (`member_id`),
  CONSTRAINT `fk_link_citizen` FOREIGN KEY (`citizen_id`) REFERENCES `citizens` (`id`),
  CONSTRAINT `fk_link_member` FOREIGN KEY (`member_id`) REFERENCES `kk_members` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Isi dari aktivasi undangan yang sudah terjadi (bukti NIK + tanggal lahir sudah dicek saat itu).
INSERT IGNORE INTO `citizen_resident_links` (`citizen_id`, `member_id`, `verified_at`, `verification_source`)
SELECT i.citizen_id, i.member_id, i.used_at, 'aktivasi_undangan'
FROM account_invitations i
JOIN citizens c ON c.id = i.citizen_id
JOIN kk_members m ON m.id = i.member_id
WHERE i.used_at IS NOT NULL AND i.citizen_id IS NOT NULL AND i.member_id IS NOT NULL;
