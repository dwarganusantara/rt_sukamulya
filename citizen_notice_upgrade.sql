-- Popup "pengajuan Anda sudah diproses admin" di portal warga.
-- Aplikasi membuat tabel ini otomatis; jalankan manual hanya jika user database
-- di hosting tidak punya izin CREATE TABLE.
CREATE TABLE IF NOT EXISTS `citizen_notice_reads` (
  `citizen_id` INT UNSIGNED NOT NULL,
  `kind` VARCHAR(10) NOT NULL,          -- 'kk' = verifikasi KK, 'sr' = pengajuan surat
  `ref_id` INT UNSIGNED NOT NULL,
  `status` VARCHAR(30) NOT NULL,        -- status yang sudah dilihat warga
  `seen_at` DATETIME NOT NULL,
  PRIMARY KEY (`citizen_id`,`kind`,`ref_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
