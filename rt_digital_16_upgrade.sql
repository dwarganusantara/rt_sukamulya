-- RT/RW Digital: migrasi modul tambahan (skema rt16-2).
-- AMAN dijalankan berulang: hanya CREATE TABLE IF NOT EXISTS dan ADD COLUMN IF NOT EXISTS (MariaDB 10.0.2+ / MySQL 8.0.29+).
-- Tidak ada DROP/DELETE/UPDATE terhadap data existing. WAJIB backup database sebelum menjalankan.
-- Aplikasi juga membuat skema ini otomatis (Ops_model::ensure) bila user DB punya izin CREATE/ALTER.

CREATE TABLE IF NOT EXISTS `resident_mutations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `mutation_type` varchar(30) NOT NULL,
  `family_id` int(10) unsigned DEFAULT NULL,
  `member_id` int(10) unsigned DEFAULT NULL,
  `citizen_id` int(10) unsigned DEFAULT NULL,
  `created_by_admin` int(10) unsigned DEFAULT NULL,
  `effective_date` date DEFAULT NULL,
  `old_values_json` text DEFAULT NULL,
  `proposed_values_json` text NOT NULL,
  `reason` varchar(1000) NOT NULL DEFAULT '',
  `status` varchar(30) NOT NULL DEFAULT 'submitted',
  `reviewed_by` int(10) unsigned DEFAULT NULL,
  `review_note` varchar(1000) DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `applied_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_mut_status` (`status`),
  KEY `idx_mut_family` (`family_id`),
  KEY `idx_mut_citizen` (`citizen_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `complaints` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ticket_no` varchar(30) DEFAULT NULL,
  `citizen_id` int(10) unsigned DEFAULT NULL,
  `category` varchar(40) NOT NULL,
  `title` varchar(160) NOT NULL,
  `location` varchar(190) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `attachment` varchar(120) DEFAULT NULL,
  `priority` varchar(10) NOT NULL DEFAULT 'normal',
  `status` varchar(30) NOT NULL DEFAULT 'submitted',
  `assigned_to` int(10) unsigned DEFAULT NULL,
  `resolution_note` text DEFAULT NULL,
  `resolved_at` datetime DEFAULT NULL,
  `closed_at` datetime DEFAULT NULL,
  `rating` tinyint(3) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_ticket` (`ticket_no`),
  KEY `idx_cmp_status` (`status`),
  KEY `idx_cmp_citizen` (`citizen_id`),
  KEY `idx_cmp_assigned` (`assigned_to`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `complaint_updates` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `complaint_id` int(10) unsigned NOT NULL,
  `actor_type` varchar(10) NOT NULL,
  `actor_id` int(10) unsigned NOT NULL,
  `status_from` varchar(30) DEFAULT NULL,
  `status_to` varchar(30) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `is_internal` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_cu_complaint` (`complaint_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `work_programs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(160) NOT NULL,
  `category` varchar(60) NOT NULL DEFAULT '',
  `description` text DEFAULT NULL,
  `period_start` date DEFAULT NULL,
  `period_end` date DEFAULT NULL,
  `target` varchar(255) NOT NULL DEFAULT '',
  `indicator` varchar(255) NOT NULL DEFAULT '',
  `pic` varchar(120) NOT NULL DEFAULT '',
  `budget` decimal(15,2) NOT NULL DEFAULT 0.00,
  `realization` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status` varchar(20) NOT NULL DEFAULT 'draft',
  `progress` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `obstacles` text DEFAULT NULL,
  `evaluation` text DEFAULT NULL,
  `approved_by` int(10) unsigned DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_wp_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `program_milestones` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `program_id` int(10) unsigned NOT NULL,
  `title` varchar(160) NOT NULL,
  `due_date` date DEFAULT NULL,
  `weight` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `is_done` tinyint(1) NOT NULL DEFAULT 0,
  `done_at` datetime DEFAULT NULL,
  `note` varchar(255) NOT NULL DEFAULT '',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_pm_program` (`program_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `program_agendas` (
  `program_id` int(10) unsigned NOT NULL,
  `post_id` int(10) unsigned NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`program_id`,`post_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `activity_notes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(160) NOT NULL,
  `note_date` date NOT NULL,
  `agenda_id` int(10) unsigned DEFAULT NULL,
  `program_id` int(10) unsigned DEFAULT NULL,
  `location` varchar(160) NOT NULL DEFAULT '',
  `participants` varchar(1000) NOT NULL DEFAULT '',
  `participant_count` int(10) unsigned NOT NULL DEFAULT 0,
  `summary` text DEFAULT NULL,
  `decisions` text DEFAULT NULL,
  `obstacles` text DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'draft',
  `version` int(10) unsigned NOT NULL DEFAULT 1,
  `approved_by` int(10) unsigned DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_an_agenda` (`agenda_id`),
  KEY `idx_an_program` (`program_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `activity_note_versions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `note_id` int(10) unsigned NOT NULL,
  `version` int(10) unsigned NOT NULL,
  `snapshot_json` mediumtext NOT NULL,
  `changed_by` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_note_version` (`note_id`,`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `action_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `note_id` int(10) unsigned NOT NULL,
  `title` varchar(190) NOT NULL,
  `pic` varchar(120) NOT NULL DEFAULT '',
  `due_date` date DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'open',
  `progress_note` varchar(255) NOT NULL DEFAULT '',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_ai_note` (`note_id`),
  KEY `idx_ai_status_due` (`status`,`due_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `patrol_schedules` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `patrol_date` date NOT NULL,
  `shift` varchar(20) NOT NULL,
  `post_location` varchar(120) NOT NULL DEFAULT 'Pos Ronda',
  `officer_name` varchar(140) NOT NULL,
  `citizen_id` int(10) unsigned DEFAULT NULL,
  `attendance` varchar(20) NOT NULL DEFAULT 'scheduled',
  `check_in` datetime DEFAULT NULL,
  `check_out` datetime DEFAULT NULL,
  `substitute_name` varchar(140) NOT NULL DEFAULT '',
  `attendance_note` varchar(255) NOT NULL DEFAULT '',
  `marked_by_admin` int(10) unsigned DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_patrol_officer` (`patrol_date`,`shift`,`officer_name`),
  KEY `idx_patrol_citizen` (`citizen_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `incident_reports` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `schedule_id` int(10) unsigned DEFAULT NULL,
  `incident_at` datetime NOT NULL,
  `location` varchar(160) NOT NULL DEFAULT '',
  `category` varchar(40) NOT NULL DEFAULT 'lainnya',
  `description` text NOT NULL,
  `action_taken` text DEFAULT NULL,
  `reported_by_admin` int(10) unsigned DEFAULT NULL,
  `reported_by_citizen` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_ir_date` (`incident_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `assets` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(40) NOT NULL,
  `name` varchar(160) NOT NULL,
  `category` varchar(60) NOT NULL DEFAULT '',
  `kind` varchar(20) NOT NULL DEFAULT 'aset',
  `quantity` int(11) NOT NULL DEFAULT 0,
  `qty_available` int(11) NOT NULL DEFAULT 0,
  `unit` varchar(30) NOT NULL DEFAULT 'unit',
  `condition_status` varchar(20) NOT NULL DEFAULT 'baik',
  `location` varchar(160) NOT NULL DEFAULT '',
  `source` varchar(120) NOT NULL DEFAULT '',
  `pic` varchar(120) NOT NULL DEFAULT '',
  `value` decimal(15,2) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'aktif',
  `note` varchar(500) NOT NULL DEFAULT '',
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_asset_code` (`code`),
  KEY `idx_asset_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `stock_movements` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `asset_id` int(10) unsigned NOT NULL,
  `movement` varchar(20) NOT NULL,
  `qty` int(11) NOT NULL,
  `ref_type` varchar(30) NOT NULL DEFAULT '',
  `ref_id` int(10) unsigned DEFAULT NULL,
  `note` varchar(255) NOT NULL DEFAULT '',
  `actor_id` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_sm_asset` (`asset_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `asset_loans` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `asset_id` int(10) unsigned NOT NULL,
  `borrower_name` varchar(140) NOT NULL,
  `citizen_id` int(10) unsigned DEFAULT NULL,
  `qty` int(11) NOT NULL DEFAULT 1,
  `purpose` varchar(255) NOT NULL DEFAULT '',
  `loan_date` date NOT NULL,
  `due_date` date NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'requested',
  `approved_by` int(10) unsigned DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `borrowed_at` datetime DEFAULT NULL,
  `returned_at` datetime DEFAULT NULL,
  `qty_returned_good` int(11) NOT NULL DEFAULT 0,
  `qty_damaged` int(11) NOT NULL DEFAULT 0,
  `qty_lost` int(11) NOT NULL DEFAULT 0,
  `note` varchar(500) NOT NULL DEFAULT '',
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_loan_asset` (`asset_id`),
  KEY `idx_loan_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `important_contacts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(140) NOT NULL,
  `category` varchar(40) NOT NULL DEFAULT 'umum',
  `phone` varchar(30) NOT NULL,
  `address` varchar(255) NOT NULL DEFAULT '',
  `service_hours` varchar(100) NOT NULL DEFAULT '',
  `note` varchar(255) NOT NULL DEFAULT '',
  `is_public` tinyint(1) NOT NULL DEFAULT 1,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_emergency` tinyint(1) NOT NULL DEFAULT 0,
  `verified_at` date DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_contact_public` (`is_public`,`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `purchase_requests` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(160) NOT NULL,
  `reason` text DEFAULT NULL,
  `vendor` varchar(140) NOT NULL DEFAULT '',
  `program_id` int(10) unsigned DEFAULT NULL,
  `estimated_total` decimal(15,2) NOT NULL DEFAULT 0.00,
  `actual_total` decimal(15,2) DEFAULT NULL,
  `actual_note` varchar(255) NOT NULL DEFAULT '',
  `is_donation` tinyint(1) NOT NULL DEFAULT 0,
  `status` varchar(20) NOT NULL DEFAULT 'draft',
  `requested_by` int(10) unsigned DEFAULT NULL,
  `approved_by` int(10) unsigned DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `review_note` varchar(500) DEFAULT NULL,
  `proof` varchar(120) DEFAULT NULL,
  `transaction_id` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_purchase_tx` (`transaction_id`),
  KEY `idx_pr_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `purchase_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `purchase_id` int(10) unsigned NOT NULL,
  `name` varchar(160) NOT NULL,
  `qty` int(11) NOT NULL DEFAULT 1,
  `unit` varchar(30) NOT NULL DEFAULT 'unit',
  `est_price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `actual_price` decimal(15,2) DEFAULT NULL,
  `qty_received` int(11) NOT NULL DEFAULT 0,
  `to_inventory` tinyint(1) NOT NULL DEFAULT 0,
  `asset_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_pi_purchase` (`purchase_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `goods_receipts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `purchase_id` int(10) unsigned NOT NULL,
  `receipt_date` date NOT NULL,
  `items_json` text NOT NULL,
  `condition_note` varchar(500) NOT NULL DEFAULT '',
  `received_by` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_gr_purchase` (`purchase_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `aid_programs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(160) NOT NULL,
  `period` varchar(40) NOT NULL DEFAULT '',
  `aid_type` varchar(60) NOT NULL DEFAULT '',
  `unit_value` decimal(15,2) NOT NULL DEFAULT 0.00,
  `source` varchar(120) NOT NULL DEFAULT '',
  `criteria` text DEFAULT NULL,
  `quota` int(10) unsigned NOT NULL DEFAULT 0,
  `status` varchar(20) NOT NULL DEFAULT 'draft',
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `aid_recipients` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `program_id` int(10) unsigned NOT NULL,
  `recipient_key` varchar(60) NOT NULL,
  `citizen_id` int(10) unsigned DEFAULT NULL,
  `family_id` int(10) unsigned DEFAULT NULL,
  `recipient_name` varchar(140) NOT NULL,
  `address` varchar(255) NOT NULL DEFAULT '',
  `proposal_note` varchar(500) NOT NULL DEFAULT '',
  `status` varchar(20) NOT NULL DEFAULT 'usulan',
  `verified_by` int(10) unsigned DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `approved_by` int(10) unsigned DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `decision_note` varchar(500) NOT NULL DEFAULT '',
  `amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `distributed_at` datetime DEFAULT NULL,
  `distributed_by` int(10) unsigned DEFAULT NULL,
  `proof` varchar(120) DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_aid_recipient` (`program_id`,`recipient_key`),
  KEY `idx_aid_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `documents` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `category` varchar(60) NOT NULL DEFAULT 'umum',
  `doc_number` varchar(100) NOT NULL DEFAULT '',
  `title` varchar(190) NOT NULL,
  `period` varchar(40) NOT NULL DEFAULT '',
  `doc_date` date DEFAULT NULL,
  `module` varchar(30) NOT NULL DEFAULT 'umum',
  `confidentiality` varchar(20) NOT NULL DEFAULT 'internal',
  `retention_until` date DEFAULT NULL,
  `current_version` int(10) unsigned NOT NULL DEFAULT 1,
  `uploaded_by` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `deleted_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_doc_cat` (`category`),
  KEY `idx_doc_deleted` (`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `document_versions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `document_id` int(10) unsigned NOT NULL,
  `version` int(10) unsigned NOT NULL,
  `stored_name` varchar(120) NOT NULL,
  `original_name` varchar(190) NOT NULL,
  `mime` varchar(100) NOT NULL,
  `size_bytes` int(10) unsigned NOT NULL,
  `checksum` char(64) NOT NULL,
  `note` varchar(255) NOT NULL DEFAULT '',
  `uploaded_by` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_doc_version` (`document_id`,`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `handover_sessions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(160) NOT NULL,
  `period_label` varchar(60) NOT NULL DEFAULT '',
  `outgoing` varchar(255) NOT NULL DEFAULT '',
  `incoming` varchar(255) NOT NULL DEFAULT '',
  `snapshot_json` mediumtext DEFAULT NULL,
  `snapshot_hash` char(64) DEFAULT NULL,
  `checklist_json` mediumtext DEFAULT NULL,
  `exceptions` text DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'draft',
  `finalized_by` int(10) unsigned DEFAULT NULL,
  `finalized_at` datetime DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `handover_signoffs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `session_id` int(10) unsigned NOT NULL,
  `admin_id` int(10) unsigned NOT NULL,
  `side` varchar(10) NOT NULL,
  `role_label` varchar(60) NOT NULL DEFAULT '',
  `note` varchar(255) NOT NULL DEFAULT '',
  `signed_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_signoff` (`session_id`,`admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `backup_runs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `kind` varchar(20) NOT NULL DEFAULT 'database',
  `status` varchar(20) NOT NULL,
  `file_name` varchar(160) DEFAULT NULL,
  `size_bytes` bigint(20) unsigned DEFAULT NULL,
  `checksum` char(64) DEFAULT NULL,
  `message` varchar(500) NOT NULL DEFAULT '',
  `triggered_by` int(10) unsigned DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_backup_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `settings_history` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `snapshot_json` mediumtext NOT NULL,
  `note` varchar(255) NOT NULL DEFAULT '',
  `changed_by` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cash_reconciliations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `recon_date` date NOT NULL,
  `bank_balance` decimal(15,2) NOT NULL,
  `system_balance` decimal(15,2) NOT NULL,
  `difference` decimal(15,2) NOT NULL,
  `note` varchar(500) NOT NULL DEFAULT '',
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `due_reminders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `due_id` int(10) unsigned NOT NULL,
  `citizen_id` int(10) unsigned NOT NULL,
  `message` varchar(500) NOT NULL,
  `sent_by` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_rem_citizen` (`citizen_id`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Kolom tambahan pada tabel existing (nullable / berdefault, tidak mengubah data lama)
ALTER TABLE `activity_logs` ADD COLUMN IF NOT EXISTS `ip_address` VARCHAR(45) NULL, ADD COLUMN IF NOT EXISTS `details` VARCHAR(500) NULL;
ALTER TABLE `admins` ADD COLUMN IF NOT EXISTS `is_active` TINYINT(1) NOT NULL DEFAULT 1, ADD COLUMN IF NOT EXISTS `full_name` VARCHAR(140) NULL, ADD COLUMN IF NOT EXISTS `created_at` DATETIME NULL;
ALTER TABLE `kk_members` ADD COLUMN IF NOT EXISTS `status` VARCHAR(20) NOT NULL DEFAULT 'aktif', ADD COLUMN IF NOT EXISTS `status_changed_at` DATETIME NULL, ADD COLUMN IF NOT EXISTS `domicile_status` VARCHAR(20) NULL, ADD COLUMN IF NOT EXISTS `resident_since` DATE NULL;
-- Role baru (superset dari role lama; role existing tetap valid)
ALTER TABLE `admins` MODIFY `role` ENUM('admin','operator','bendahara','ketua','sekretaris','keamanan','inventaris','sosial','auditor') NOT NULL DEFAULT 'admin';
REPLACE INTO `settings` (`setting_key`,`setting_value`) VALUES ('ops_schema_version','rt16-1');

-- ===== rt16-2: field tambahan warga, antrean notifikasi email, pengaduan identitas disembunyikan =====
CREATE TABLE IF NOT EXISTS `custom_field_definitions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `label` varchar(80) NOT NULL,
  `field_type` varchar(20) NOT NULL DEFAULT 'text',
  `options` text DEFAULT NULL,
  `help_text` varchar(160) NOT NULL DEFAULT '',
  `is_required` tinyint(1) NOT NULL DEFAULT 0,
  `citizen_editable` tinyint(1) NOT NULL DEFAULT 1,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `citizen_field_values` (
  `citizen_id` int(10) unsigned NOT NULL,
  `field_id` int(10) unsigned NOT NULL,
  `value` varchar(255) NOT NULL DEFAULT '',
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`citizen_id`,`field_id`),
  KEY `idx_cfv_field` (`field_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `notification_outbox` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `citizen_id` int(10) unsigned DEFAULT NULL,
  `channel` varchar(20) NOT NULL DEFAULT 'email',
  `recipient` varchar(190) NOT NULL,
  `subject` varchar(190) NOT NULL,
  `body` text NOT NULL,
  `dedupe_key` varchar(120) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `attempts` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `last_error` varchar(300) DEFAULT NULL,
  `next_attempt_at` datetime DEFAULT NULL,
  `sent_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_outbox_dedupe` (`dedupe_key`),
  KEY `idx_outbox_status` (`status`,`next_attempt_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `citizens` ADD COLUMN IF NOT EXISTS `notify_email` TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE `complaints` ADD COLUMN IF NOT EXISTS `is_anonymous` TINYINT(1) NOT NULL DEFAULT 0;
REPLACE INTO `settings` (`setting_key`,`setting_value`) VALUES ('ops_schema_version','rt16-2');
-- ROLLBACK (hanya bila benar-benar perlu, SETELAH backup): tabel baru dapat di-DROP tanpa memengaruhi fitur lama.
-- Kolom tambahan boleh dibiarkan; aplikasi lama mengabaikannya.
