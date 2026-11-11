-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 11, 2026 at 08:18 PM
-- Server version: 10.11.19-MariaDB-cll-lve
-- PHP Version: 8.4.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sukr9483_db_rt009`
--

-- --------------------------------------------------------

--
-- Table structure for table `action_items`
--

CREATE TABLE `action_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `note_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(190) NOT NULL,
  `pic` varchar(120) NOT NULL DEFAULT '',
  `due_date` date DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'open',
  `progress_note` varchar(255) NOT NULL DEFAULT '',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `admin_id` int(10) UNSIGNED NOT NULL,
  `action` varchar(80) NOT NULL,
  `subject` varchar(100) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL,
  `details` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `admin_id`, `action`, `subject`, `created_at`, `ip_address`, `details`) VALUES
(5, 1, 'update', 'quick_link:7', '2026-10-17 12:58:08', NULL, NULL),
(6, 1, 'update', 'quick_link:7', '2026-10-17 12:58:08', NULL, NULL),
(7, 1, 'create', 'galeri:1', '2026-10-17 13:07:30', NULL, NULL),
(8, 1, 'create', 'citizen:2', '2026-10-17 13:29:51', NULL, NULL),
(9, 7, 'review', 'request:2', '2026-10-17 16:38:50', NULL, NULL),
(10, 1, 'update', 'letter_settings', '2026-10-17 16:52:14', NULL, NULL),
(11, 1, 'update', 'letter_settings', '2026-10-17 16:52:40', NULL, NULL),
(12, 1, 'update', 'letter_settings', '2026-10-17 16:54:01', NULL, NULL),
(13, 1, 'update', 'letter_settings', '2026-10-17 16:55:59', NULL, NULL),
(14, 1, 'update', 'letter_settings', '2026-10-17 16:59:21', NULL, NULL),
(15, 1, 'update', 'letter_settings', '2026-10-17 17:03:28', NULL, NULL),
(16, 1, 'update', 'letter_settings', '2026-10-17 17:03:32', NULL, NULL),
(17, 1, 'update', 'letter_settings', '2026-10-17 17:04:00', NULL, NULL),
(18, 1, 'create', 'pengumuman:2', '2026-10-17 17:21:08', NULL, NULL),
(19, 1, 'review', 'request:41', '2026-10-17 19:32:29', NULL, NULL),
(20, 1, 'update', 'letter_settings', '2026-10-17 19:45:42', NULL, NULL),
(21, 1, 'update', 'letter_settings', '2026-10-17 19:53:13', NULL, NULL),
(22, 1, 'create', 'due:2', '2026-10-17 19:59:53', NULL, NULL),
(23, 1, 'update', 'letter_settings', '2026-10-17 20:04:59', NULL, NULL),
(24, 1, 'verified', 'due_payment:1', '2026-10-17 20:29:09', NULL, NULL),
(25, 1, 'review', 'request:54', '2026-10-18 09:41:58', NULL, NULL),
(26, 1, 'create', 'official:1', '2026-10-22 14:55:13', NULL, NULL),
(27, 1, 'review', 'request:55', '2026-10-24 20:32:02', NULL, NULL),
(28, 1, 'update', 'official:3', '2026-10-29 18:35:56', NULL, NULL),
(29, 1, 'update', 'official:1', '2026-10-29 18:36:59', NULL, NULL),
(30, 1, 'update', 'official:1', '2026-10-29 18:37:14', NULL, NULL),
(31, 1, 'create', 'transaction:3', '2026-10-31 15:01:41', NULL, NULL),
(32, 1, 'create', 'transaction:4', '2026-10-31 15:06:42', NULL, NULL),
(74, 1, 'view', 'family:10', '2026-11-07 21:08:09', '127.0.0.1', NULL),
(75, 1, 'update', 'settings', '2026-11-07 21:30:52', '127.0.0.1', NULL),
(76, 1, 'update', 'settings', '2026-11-07 22:20:41', '127.0.0.1', NULL),
(83, 1, 'update', 'settings', '2026-11-07 22:44:31', '127.0.0.1', NULL),
(84, 1, 'update', 'settings', '2026-11-07 23:01:22', '127.0.0.1', NULL),
(85, 1, 'kk_rejected', 'kk_request:30', '2026-11-08 08:32:23', '127.0.0.1', 'double'),
(86, 0, 'ui_apply', 'ui_config:1', '2026-11-08 14:33:59', '127.0.0.1', 'Dark Navy'),
(87, 0, 'ui_reset', 'ui_config:2', '2026-11-08 14:34:14', '127.0.0.1', 'Reset ke Default'),
(88, 0, 'ui_apply', 'ui_config:3', '2026-11-08 14:35:07', '127.0.0.1', 'Teal Fresh'),
(89, 0, 'ui_reset', 'ui_config:4', '2026-11-08 14:35:34', '127.0.0.1', 'Reset ke Default');

-- --------------------------------------------------------

--
-- Table structure for table `activity_notes`
--

CREATE TABLE `activity_notes` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(160) NOT NULL,
  `note_date` date NOT NULL,
  `agenda_id` int(10) UNSIGNED DEFAULT NULL,
  `program_id` int(10) UNSIGNED DEFAULT NULL,
  `location` varchar(160) NOT NULL DEFAULT '',
  `participants` varchar(1000) NOT NULL DEFAULT '',
  `participant_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `summary` text DEFAULT NULL,
  `decisions` text DEFAULT NULL,
  `obstacles` text DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'draft',
  `version` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `approved_by` int(10) UNSIGNED DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `activity_note_versions`
--

CREATE TABLE `activity_note_versions` (
  `id` int(10) UNSIGNED NOT NULL,
  `note_id` int(10) UNSIGNED NOT NULL,
  `version` int(10) UNSIGNED NOT NULL,
  `snapshot_json` mediumtext NOT NULL,
  `changed_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(10) UNSIGNED NOT NULL,
  `username` varchar(80) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','operator','bendahara','ketua','sekretaris','keamanan','inventaris','sosial','auditor') NOT NULL DEFAULT 'admin',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `full_name` varchar(140) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `custom_role` varchar(40) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password_hash`, `role`, `is_active`, `full_name`, `created_at`, `custom_role`) VALUES
(1, 'admin', '$2y$10$2DhqArcPy1qvhK4Gy9kOPecrGwWUKCoyAGBlAgPPoB86CThNLPY92', 'admin', 1, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `aid_programs`
--

CREATE TABLE `aid_programs` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(160) NOT NULL,
  `period` varchar(40) NOT NULL DEFAULT '',
  `aid_type` varchar(60) NOT NULL DEFAULT '',
  `unit_value` decimal(15,2) NOT NULL DEFAULT 0.00,
  `source` varchar(120) NOT NULL DEFAULT '',
  `criteria` text DEFAULT NULL,
  `quota` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `status` varchar(20) NOT NULL DEFAULT 'draft',
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `aid_recipients`
--

CREATE TABLE `aid_recipients` (
  `id` int(10) UNSIGNED NOT NULL,
  `program_id` int(10) UNSIGNED NOT NULL,
  `recipient_key` varchar(60) NOT NULL,
  `citizen_id` int(10) UNSIGNED DEFAULT NULL,
  `family_id` int(10) UNSIGNED DEFAULT NULL,
  `recipient_name` varchar(140) NOT NULL,
  `address` varchar(255) NOT NULL DEFAULT '',
  `proposal_note` varchar(500) NOT NULL DEFAULT '',
  `status` varchar(20) NOT NULL DEFAULT 'usulan',
  `verified_by` int(10) UNSIGNED DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `approved_by` int(10) UNSIGNED DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `decision_note` varchar(500) NOT NULL DEFAULT '',
  `amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `distributed_at` datetime DEFAULT NULL,
  `distributed_by` int(10) UNSIGNED DEFAULT NULL,
  `proof` varchar(120) DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `app_features`
--

CREATE TABLE `app_features` (
  `id` int(10) UNSIGNED NOT NULL,
  `code` varchar(80) NOT NULL,
  `name` varchar(150) NOT NULL,
  `category` varchar(80) NOT NULL,
  `parent_id` int(10) UNSIGNED DEFAULT NULL,
  `route_key` varchar(150) DEFAULT NULL,
  `icon` varchar(80) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_core` tinyint(1) NOT NULL DEFAULT 0,
  `is_implemented` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `app_features`
--

INSERT INTO `app_features` (`id`, `code`, `name`, `category`, `parent_id`, `route_key`, `icon`, `sort_order`, `is_core`, `is_implemented`, `created_at`) VALUES
(1, 'family_card', 'Kartu Keluarga', 'Kependudukan', NULL, 'admin/kk', 'people', 10, 0, 1, '2026-11-07 22:18:48'),
(2, 'resident_profile', 'Data Penduduk', 'Kependudukan', NULL, 'admin/penduduk', 'list', 20, 0, 1, '2026-11-07 22:18:48'),
(3, 'resident_mutation', 'Mutasi Warga', 'Kependudukan', 1, 'admin/mutasi', 'swap', 30, 0, 1, '2026-11-07 22:18:48'),
(4, 'social_aid', 'Bantuan Sosial', 'Kependudukan', NULL, 'admin/bansos', 'heart', 40, 0, 1, '2026-11-07 22:18:48'),
(5, 'letters', 'Pengajuan Surat', 'Layanan Warga', NULL, 'admin/requests', 'doc', 50, 0, 1, '2026-11-07 22:18:48'),
(6, 'complaints', 'Pengaduan & Aspirasi', 'Layanan Warga', NULL, 'admin/pengaduan', 'megaphone', 60, 0, 1, '2026-11-07 22:18:48'),
(7, 'live_chat', 'Live Chat Warga', 'Layanan Warga', NULL, 'admin/chat', 'chat', 70, 0, 1, '2026-11-07 22:18:48'),
(8, 'dues', 'Iuran Warga', 'Keuangan', NULL, 'admin/dues', 'database', 80, 0, 1, '2026-11-07 22:18:48'),
(9, 'dues_arrears', 'Tunggakan Iuran Warga', 'Keuangan', 8, 'admin/tunggakan', 'clock', 90, 0, 1, '2026-11-07 22:18:48'),
(10, 'cash_report', 'Kas RT', 'Keuangan', NULL, 'admin/transactions', 'chart', 100, 0, 1, '2026-11-07 22:18:48'),
(11, 'cash_reconciliation', 'Rekonsiliasi Kas', 'Keuangan', 10, 'admin/rekonsiliasi', 'list', 110, 0, 1, '2026-11-07 22:18:48'),
(12, 'announcements', 'Pengumuman', 'Informasi', NULL, 'admin/posts/pengumuman', 'megaphone', 120, 0, 1, '2026-11-07 22:18:48'),
(13, 'events', 'Agenda Kegiatan', 'Informasi', NULL, 'admin/posts/agenda', 'calendar', 130, 0, 1, '2026-11-07 22:18:48'),
(14, 'gallery', 'Galeri Kegiatan', 'Informasi', NULL, 'admin/posts/galeri', 'image', 140, 0, 1, '2026-11-07 22:18:48'),
(15, 'contacts', 'Kontak Penting', 'Informasi', NULL, 'admin/kontak', 'phone', 150, 0, 1, '2026-11-07 22:18:48'),
(16, 'patrol', 'Jadwal Ronda', 'Operasional', NULL, 'admin/ronda', 'shield', 160, 0, 1, '2026-11-07 22:18:48'),
(17, 'work_program', 'Program Kerja', 'Operasional', NULL, 'admin/program', 'briefcase', 170, 0, 1, '2026-11-07 22:18:48'),
(18, 'activity_notes', 'Catatan & Notulen', 'Operasional', NULL, 'admin/notulen', 'lines', 180, 0, 1, '2026-11-07 22:18:48'),
(19, 'inventory', 'Aset & Inventaris', 'Operasional', NULL, 'admin/inventaris', 'box', 190, 0, 1, '2026-11-07 22:18:48'),
(20, 'purchasing', 'Belanja & Penerimaan', 'Operasional', NULL, 'admin/belanja', 'cart', 200, 0, 1, '2026-11-07 22:18:48'),
(21, 'documents', 'Arsip Dokumen', 'Operasional', NULL, 'admin/arsip', 'database', 210, 0, 1, '2026-11-07 22:18:48');

-- --------------------------------------------------------

--
-- Table structure for table `assets`
--

CREATE TABLE `assets` (
  `id` int(10) UNSIGNED NOT NULL,
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
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `asset_loans`
--

CREATE TABLE `asset_loans` (
  `id` int(10) UNSIGNED NOT NULL,
  `asset_id` int(10) UNSIGNED NOT NULL,
  `borrower_name` varchar(140) NOT NULL,
  `citizen_id` int(10) UNSIGNED DEFAULT NULL,
  `qty` int(11) NOT NULL DEFAULT 1,
  `purpose` varchar(255) NOT NULL DEFAULT '',
  `loan_date` date NOT NULL,
  `due_date` date NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'requested',
  `approved_by` int(10) UNSIGNED DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `borrowed_at` datetime DEFAULT NULL,
  `returned_at` datetime DEFAULT NULL,
  `qty_returned_good` int(11) NOT NULL DEFAULT 0,
  `qty_damaged` int(11) NOT NULL DEFAULT 0,
  `qty_lost` int(11) NOT NULL DEFAULT 0,
  `note` varchar(500) NOT NULL DEFAULT '',
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `backup_runs`
--

CREATE TABLE `backup_runs` (
  `id` int(10) UNSIGNED NOT NULL,
  `kind` varchar(20) NOT NULL DEFAULT 'database',
  `status` varchar(20) NOT NULL,
  `file_name` varchar(160) DEFAULT NULL,
  `size_bytes` bigint(20) UNSIGNED DEFAULT NULL,
  `checksum` char(64) DEFAULT NULL,
  `message` varchar(500) NOT NULL DEFAULT '',
  `triggered_by` int(10) UNSIGNED DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `billing_campaigns`
--

CREATE TABLE `billing_campaigns` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(140) NOT NULL,
  `due_type` enum('rutin','non_rutin','sosial') NOT NULL,
  `category` varchar(100) NOT NULL,
  `period` char(7) NOT NULL,
  `amount` decimal(14,2) NOT NULL,
  `note` varchar(255) NOT NULL DEFAULT '',
  `created_by` int(10) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cash_reconciliations`
--

CREATE TABLE `cash_reconciliations` (
  `id` int(10) UNSIGNED NOT NULL,
  `recon_date` date NOT NULL,
  `bank_balance` decimal(15,2) NOT NULL,
  `system_balance` decimal(15,2) NOT NULL,
  `difference` decimal(15,2) NOT NULL,
  `note` varchar(500) NOT NULL DEFAULT '',
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chat_conversations`
--

CREATE TABLE `chat_conversations` (
  `id` int(10) UNSIGNED NOT NULL,
  `citizen_id` int(10) UNSIGNED NOT NULL,
  `assigned_admin_id` int(10) UNSIGNED DEFAULT NULL,
  `last_message_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `chat_conversations`
--

INSERT INTO `chat_conversations` (`id`, `citizen_id`, `assigned_admin_id`, `last_message_at`, `created_at`) VALUES
(2, 23, 1, '2026-11-08 10:01:46', '2026-10-28 21:47:00'),
(3, 2, NULL, NULL, '2026-10-31 18:19:20'),
(4, 100, NULL, NULL, '2026-11-08 00:27:42'),
(5, 22, NULL, NULL, '2026-11-08 08:20:02'),
(6, 128, NULL, NULL, '2026-11-08 12:54:30');

-- --------------------------------------------------------

--
-- Table structure for table `chat_messages`
--

CREATE TABLE `chat_messages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `conversation_id` int(10) UNSIGNED NOT NULL,
  `sender_type` enum('citizen','admin') NOT NULL,
  `sender_id` int(10) UNSIGNED NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `chat_messages`
--

INSERT INTO `chat_messages` (`id`, `conversation_id`, `sender_type`, `sender_id`, `message`, `is_read`, `created_at`) VALUES
(3, 2, 'citizen', 23, 'tes', 1, '2026-10-28 21:57:15'),
(4, 2, 'citizen', 23, 'tes', 1, '2026-11-08 00:00:59'),
(5, 2, 'citizen', 23, 'hahaha', 1, '2026-11-08 00:01:14'),
(6, 2, 'citizen', 23, 'blee', 1, '2026-11-08 04:58:00'),
(7, 2, 'admin', 1, 'hola', 1, '2026-11-08 10:01:46');

-- --------------------------------------------------------

--
-- Table structure for table `chat_presence`
--

CREATE TABLE `chat_presence` (
  `user_type` enum('citizen','admin') NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `last_seen` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `chat_presence`
--

INSERT INTO `chat_presence` (`user_type`, `user_id`, `last_seen`) VALUES
('admin', 12, '2026-11-05 10:56:57'),
('admin', 14, '2026-11-05 11:05:13'),
('admin', 15, '2026-11-05 11:05:22'),
('admin', 16, '2026-11-05 11:07:41'),
('admin', 17, '2026-11-05 11:30:51'),
('citizen', 77, '2026-11-05 11:37:12'),
('citizen', 79, '2026-11-05 11:37:35'),
('citizen', 80, '2026-11-05 11:37:44'),
('citizen', 81, '2026-11-05 11:38:12'),
('citizen', 82, '2026-11-05 11:38:42'),
('citizen', 85, '2026-11-05 11:45:01'),
('citizen', 86, '2026-11-05 11:48:10'),
('citizen', 87, '2026-11-05 11:48:10'),
('citizen', 88, '2026-11-05 11:55:28'),
('citizen', 89, '2026-11-05 11:55:28'),
('citizen', 90, '2026-11-05 12:05:16'),
('citizen', 91, '2026-11-05 12:05:16'),
('citizen', 2, '2026-11-08 00:10:50'),
('citizen', 114, '2026-11-08 07:19:00'),
('citizen', 115, '2026-11-08 07:45:55'),
('citizen', 116, '2026-11-08 07:57:13'),
('citizen', 118, '2026-11-08 07:58:09'),
('citizen', 119, '2026-11-08 07:58:51'),
('citizen', 120, '2026-11-08 08:01:09'),
('citizen', 121, '2026-11-08 08:01:23'),
('citizen', 122, '2026-11-08 08:04:01'),
('citizen', 126, '2026-11-08 08:16:38'),
('citizen', 22, '2026-11-08 08:23:42'),
('admin', 72, '2026-11-08 09:03:52'),
('admin', 73, '2026-11-08 11:50:11'),
('admin', 74, '2026-11-08 11:52:23'),
('admin', 75, '2026-11-08 11:53:48'),
('admin', 76, '2026-11-08 11:53:50'),
('admin', 77, '2026-11-08 12:06:55'),
('admin', 78, '2026-11-08 12:29:16'),
('admin', 79, '2026-11-08 13:26:14'),
('citizen', 128, '2026-11-08 14:38:45'),
('citizen', 23, '2026-11-10 15:23:49'),
('admin', 1, '2026-11-10 16:56:06');

-- --------------------------------------------------------

--
-- Table structure for table `citizens`
--

CREATE TABLE `citizens` (
  `id` int(10) UNSIGNED NOT NULL,
  `username` varchar(80) NOT NULL,
  `display_name` varchar(140) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `address` varchar(255) DEFAULT NULL,
  `phone` varchar(25) DEFAULT NULL,
  `email` varchar(190) DEFAULT NULL,
  `full_name` varchar(140) DEFAULT NULL,
  `gender` varchar(10) DEFAULT 'female',
  `notes` text DEFAULT NULL,
  `notify_email` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `citizens`
--

INSERT INTO `citizens` (`id`, `username`, `display_name`, `password_hash`, `is_active`, `created_at`, `address`, `phone`, `email`, `full_name`, `gender`, `notes`, `notify_email`) VALUES
(2, 'chendy', 'Chendy', '$2y$10$mhH3DHgOTZdPv.jYmhPxK.JoIsxjmsIySP5cBDabN.QGA93kOI3Ne', 1, '2026-10-17 13:29:51', NULL, NULL, NULL, NULL, 'female', NULL, 0),
(22, 'chendy182', 'chendy', '$2y$10$2FscB0L1QbS43GjyQoAEIOHV2SqbbsERCVZWt3CYj2Evc2BytTll2', 1, '2026-10-24 20:29:24', NULL, NULL, NULL, NULL, 'female', NULL, 0),
(23, 'dewi', 'dewi sinta', '$2y$10$vXqidTEG3LNTWfuRjaKFbOUdgly2mvduUG.hT4GOieAcU7WXfe6ha', 1, '2026-10-28 21:03:51', NULL, NULL, NULL, NULL, 'female', NULL, 0),
(128, 'vita', 'vita', '$2y$10$K0mJQl1xyGJcTYqQ4yPKBeecRdp62V4/pGSW5GcL9LbMi7hdGRQYm', 1, '2026-11-08 12:54:30', NULL, NULL, NULL, 'vita', 'female', NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `citizen_field_values`
--

CREATE TABLE `citizen_field_values` (
  `citizen_id` int(10) UNSIGNED NOT NULL,
  `field_id` int(10) UNSIGNED NOT NULL,
  `value` varchar(255) NOT NULL DEFAULT '',
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `citizen_notice_reads`
--

CREATE TABLE `citizen_notice_reads` (
  `citizen_id` int(10) UNSIGNED NOT NULL,
  `kind` varchar(10) NOT NULL,
  `ref_id` int(10) UNSIGNED NOT NULL,
  `status` varchar(30) NOT NULL,
  `seen_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `citizen_notice_reads`
--

INSERT INTO `citizen_notice_reads` (`citizen_id`, `kind`, `ref_id`, `status`, `seen_at`) VALUES
(23, 'kk', 10, 'approved', '2026-11-05 11:42:58');

-- --------------------------------------------------------

--
-- Table structure for table `complaints`
--

CREATE TABLE `complaints` (
  `id` int(10) UNSIGNED NOT NULL,
  `ticket_no` varchar(30) DEFAULT NULL,
  `citizen_id` int(10) UNSIGNED DEFAULT NULL,
  `category` varchar(40) NOT NULL,
  `title` varchar(160) NOT NULL,
  `location` varchar(190) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `attachment` varchar(120) DEFAULT NULL,
  `priority` varchar(10) NOT NULL DEFAULT 'normal',
  `status` varchar(30) NOT NULL DEFAULT 'submitted',
  `assigned_to` int(10) UNSIGNED DEFAULT NULL,
  `resolution_note` text DEFAULT NULL,
  `resolved_at` datetime DEFAULT NULL,
  `closed_at` datetime DEFAULT NULL,
  `rating` tinyint(3) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  `is_anonymous` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `complaint_updates`
--

CREATE TABLE `complaint_updates` (
  `id` int(10) UNSIGNED NOT NULL,
  `complaint_id` int(10) UNSIGNED NOT NULL,
  `actor_type` varchar(10) NOT NULL,
  `actor_id` int(10) UNSIGNED NOT NULL,
  `status_from` varchar(30) DEFAULT NULL,
  `status_to` varchar(30) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `is_internal` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `custom_field_definitions`
--

CREATE TABLE `custom_field_definitions` (
  `id` int(10) UNSIGNED NOT NULL,
  `label` varchar(80) NOT NULL,
  `field_type` varchar(20) NOT NULL DEFAULT 'text',
  `options` text DEFAULT NULL,
  `help_text` varchar(160) NOT NULL DEFAULT '',
  `is_required` tinyint(1) NOT NULL DEFAULT 0,
  `citizen_editable` tinyint(1) NOT NULL DEFAULT 1,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `custom_roles`
--

CREATE TABLE `custom_roles` (
  `role_key` varchar(40) NOT NULL,
  `tenant_id` int(10) UNSIGNED NOT NULL,
  `label` varchar(80) NOT NULL,
  `description` varchar(255) NOT NULL DEFAULT '',
  `base_role` varchar(40) NOT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `id` int(10) UNSIGNED NOT NULL,
  `category` varchar(60) NOT NULL DEFAULT 'umum',
  `doc_number` varchar(100) NOT NULL DEFAULT '',
  `title` varchar(190) NOT NULL,
  `period` varchar(40) NOT NULL DEFAULT '',
  `doc_date` date DEFAULT NULL,
  `module` varchar(30) NOT NULL DEFAULT 'umum',
  `confidentiality` varchar(20) NOT NULL DEFAULT 'internal',
  `retention_until` date DEFAULT NULL,
  `current_version` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `uploaded_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `deleted_by` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `document_versions`
--

CREATE TABLE `document_versions` (
  `id` int(10) UNSIGNED NOT NULL,
  `document_id` int(10) UNSIGNED NOT NULL,
  `version` int(10) UNSIGNED NOT NULL,
  `stored_name` varchar(120) NOT NULL,
  `original_name` varchar(190) NOT NULL,
  `mime` varchar(100) NOT NULL,
  `size_bytes` int(10) UNSIGNED NOT NULL,
  `checksum` char(64) NOT NULL,
  `note` varchar(255) NOT NULL DEFAULT '',
  `uploaded_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dues`
--

CREATE TABLE `dues` (
  `id` int(10) UNSIGNED NOT NULL,
  `citizen_id` int(10) UNSIGNED NOT NULL,
  `period` char(7) NOT NULL,
  `category` varchar(100) NOT NULL,
  `due_type` enum('rutin','non_rutin','sosial') NOT NULL DEFAULT 'rutin',
  `campaign_id` int(10) UNSIGNED DEFAULT NULL,
  `amount_due` decimal(14,2) NOT NULL,
  `amount_paid` decimal(14,2) NOT NULL DEFAULT 0.00,
  `note` varchar(255) NOT NULL DEFAULT '',
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `dues`
--

INSERT INTO `dues` (`id`, `citizen_id`, `period`, `category`, `due_type`, `campaign_id`, `amount_due`, `amount_paid`, `note`, `updated_at`) VALUES
(2, 2, '2026-10', 'Iuran lingkungan', 'rutin', NULL, 50000.00, 50000.00, '', '2026-10-17 20:29:09');

-- --------------------------------------------------------

--
-- Table structure for table `due_payments`
--

CREATE TABLE `due_payments` (
  `id` int(10) UNSIGNED NOT NULL,
  `due_id` int(10) UNSIGNED NOT NULL,
  `citizen_id` int(10) UNSIGNED NOT NULL,
  `amount` decimal(14,2) NOT NULL,
  `method` enum('transfer','tunai') NOT NULL,
  `proof` varchar(255) DEFAULT NULL,
  `status` enum('pending','verified','returned') NOT NULL DEFAULT 'pending',
  `review_note` varchar(255) NOT NULL DEFAULT '',
  `transaction_id` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `reviewed_at` datetime DEFAULT NULL,
  `reviewed_by` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `due_payments`
--

INSERT INTO `due_payments` (`id`, `due_id`, `citizen_id`, `amount`, `method`, `proof`, `status`, `review_note`, `transaction_id`, `created_at`, `reviewed_at`, `reviewed_by`) VALUES
(1, 2, 2, 10000.00, 'tunai', NULL, 'verified', '', 2, '2026-10-17 20:28:48', '2026-10-17 15:29:09', 1);

-- --------------------------------------------------------

--
-- Table structure for table `due_reminders`
--

CREATE TABLE `due_reminders` (
  `id` int(10) UNSIGNED NOT NULL,
  `due_id` int(10) UNSIGNED NOT NULL,
  `citizen_id` int(10) UNSIGNED NOT NULL,
  `message` varchar(500) NOT NULL,
  `sent_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `goods_receipts`
--

CREATE TABLE `goods_receipts` (
  `id` int(10) UNSIGNED NOT NULL,
  `purchase_id` int(10) UNSIGNED NOT NULL,
  `receipt_date` date NOT NULL,
  `items_json` text NOT NULL,
  `condition_note` varchar(500) NOT NULL DEFAULT '',
  `received_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `handover_sessions`
--

CREATE TABLE `handover_sessions` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(160) NOT NULL,
  `period_label` varchar(60) NOT NULL DEFAULT '',
  `outgoing` varchar(255) NOT NULL DEFAULT '',
  `incoming` varchar(255) NOT NULL DEFAULT '',
  `snapshot_json` mediumtext DEFAULT NULL,
  `snapshot_hash` char(64) DEFAULT NULL,
  `checklist_json` mediumtext DEFAULT NULL,
  `exceptions` text DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'draft',
  `finalized_by` int(10) UNSIGNED DEFAULT NULL,
  `finalized_at` datetime DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `handover_signoffs`
--

CREATE TABLE `handover_signoffs` (
  `id` int(10) UNSIGNED NOT NULL,
  `session_id` int(10) UNSIGNED NOT NULL,
  `admin_id` int(10) UNSIGNED NOT NULL,
  `side` varchar(10) NOT NULL,
  `role_label` varchar(60) NOT NULL DEFAULT '',
  `note` varchar(255) NOT NULL DEFAULT '',
  `signed_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `important_contacts`
--

CREATE TABLE `important_contacts` (
  `id` int(10) UNSIGNED NOT NULL,
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
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `incident_reports`
--

CREATE TABLE `incident_reports` (
  `id` int(10) UNSIGNED NOT NULL,
  `schedule_id` int(10) UNSIGNED DEFAULT NULL,
  `incident_at` datetime NOT NULL,
  `location` varchar(160) NOT NULL DEFAULT '',
  `category` varchar(40) NOT NULL DEFAULT 'lainnya',
  `description` text NOT NULL,
  `action_taken` text DEFAULT NULL,
  `reported_by_admin` int(10) UNSIGNED DEFAULT NULL,
  `reported_by_citizen` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kk_families`
--

CREATE TABLE `kk_families` (
  `id` int(10) UNSIGNED NOT NULL,
  `nomor_kk` varchar(16) NOT NULL,
  `owner_citizen_id` int(10) UNSIGNED NOT NULL,
  `fields_json` text NOT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kk_families`
--

INSERT INTO `kk_families` (`id`, `nomor_kk`, `owner_citizen_id`, `fields_json`, `updated_at`) VALUES
(10, '3674041612200024', 23, '{\"nomor_kk\":\"3674041612200024\",\"alamat\":\"JL SUKA MULYA IV NO. 17\",\"rt\":\"009\",\"rw\":\"008\",\"kelurahan\":\"SERUA INDAH\",\"kecamatan\":\"CIPUTAT\",\"kota\":\"KOTA TANGERANG SELATAN\",\"provinsi\":\"BANTEN\",\"kode_pos\":\"15414\",\"fields_need_review\":[]}', '2026-11-05 11:33:10');

-- --------------------------------------------------------

--
-- Table structure for table `kk_members`
--

CREATE TABLE `kk_members` (
  `id` int(10) UNSIGNED NOT NULL,
  `family_id` int(10) UNSIGNED NOT NULL,
  `nik` varchar(16) NOT NULL,
  `fields_json` text NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'aktif',
  `status_changed_at` datetime DEFAULT NULL,
  `domicile_status` varchar(20) DEFAULT NULL,
  `resident_since` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kk_members`
--

INSERT INTO `kk_members` (`id`, `family_id`, `nik`, `fields_json`, `status`, `status_changed_at`, `domicile_status`, `resident_since`) VALUES
(25, 10, '3674062609890006', '{\"nama_lengkap\":\"MUHAMMAD CHENDY SETIADI\",\"nik\":\"3674062609890006\",\"jenis_kelamin\":\"L\",\"tempat_lahir\":\"TANGERANG\",\"tanggal_lahir\":\"1989-09-26\",\"agama\":\"ISLAM DIPLOMA\",\"pendidikan\":\"IV\\/STRATA |\",\"pekerjaan\":\"KARYAWAN SWASTA\",\"golongan_darah\":\"TIDAK TAHU\",\"hubungan_keluarga\":\"Kepala Keluarga\",\"fields_need_review\":[]}', 'aktif', NULL, NULL, NULL),
(26, 10, '3674046502950009', '{\"nama_lengkap\":\"DEWI SINTA SARI\",\"nik\":\"3674046502950009\",\"jenis_kelamin\":\"P\",\"tempat_lahir\":\"TANGERANG\",\"tanggal_lahir\":\"1995-02-25\",\"agama\":\"ISLAM\",\"pendidikan\":\"AKADEMI\\/DIPLOMA III\\/SARJANA MUDA\",\"pekerjaan\":\"KARYAWAN SWASTA\",\"golongan_darah\":\"TIDAK TAHU\",\"hubungan_keluarga\":\"Istri\",\"fields_need_review\":[]}', 'aktif', NULL, NULL, NULL),
(27, 10, '3674060410170002', '{\"nama_lengkap\":\"EMMERIL KHAIRAN HAMIZAN\",\"nik\":\"3674060410170002\",\"jenis_kelamin\":\"L\",\"tempat_lahir\":\"TANGERANG SELATAN\",\"tanggal_lahir\":\"2017-10-04\",\"agama\":\"ISLAM TIDAK\\/BELUM\",\"pendidikan\":\"SEKOLAH\",\"pekerjaan\":\"BELUM\\/TIDAK BEKERJA\",\"golongan_darah\":\"TIDAK TAHU\",\"hubungan_keluarga\":\"Anak\",\"fields_need_review\":[]}', 'aktif', NULL, NULL, NULL),
(28, 10, '3674044611210002', '{\"nama_lengkap\":\"SHADLEEN HAZZEMA SHAHIA\",\"nik\":\"3674044611210002\",\"jenis_kelamin\":\"P\",\"tempat_lahir\":\"TANGERANG SELATAN\",\"tanggal_lahir\":\"2021-11-06\",\"agama\":\"ISLAM TIDAK\\/BELUM\",\"pendidikan\":\"SEKOLAH\",\"pekerjaan\":\"BELUM\\/TIDAK BEKERJA\",\"golongan_darah\":\"TIDAK TAHU\",\"hubungan_keluarga\":\"Anak\",\"fields_need_review\":[]}', 'aktif', NULL, NULL, NULL),
(29, 10, '3674040407240004', '{\"nama_lengkap\":\"MUHAMMAD ARVA KAUTSARRAZKY\",\"nik\":\"3674040407240004\",\"jenis_kelamin\":\"L\",\"tempat_lahir\":\"TANGERANG SELATAN\",\"tanggal_lahir\":\"2024-07-04\",\"agama\":\"ISLAM TIDAK\\/BELUM\",\"pendidikan\":\"SEKOLAH\",\"pekerjaan\":\"BELUM\\/TIDAK BEKERJA\",\"golongan_darah\":\"TIDAK TAHU\",\"hubungan_keluarga\":\"Anak\",\"fields_need_review\":[]}', 'aktif', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `kk_scan_requests`
--

CREATE TABLE `kk_scan_requests` (
  `id` int(10) UNSIGNED NOT NULL,
  `citizen_id` int(10) UNSIGNED NOT NULL,
  `status` enum('pending','approved','revision_requested','rejected') NOT NULL DEFAULT 'pending',
  `proposed_data_json` mediumtext NOT NULL,
  `review_notes` text DEFAULT NULL,
  `reviewed_by` int(10) UNSIGNED DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kk_scan_requests`
--

INSERT INTO `kk_scan_requests` (`id`, `citizen_id`, `status`, `proposed_data_json`, `review_notes`, `reviewed_by`, `reviewed_at`, `created_at`) VALUES
(10, 23, 'approved', '{\"keluarga\":{\"nomor_kk\":\"3674041612200024\",\"alamat\":\"JL SUKA MULYA IV NO. 17\",\"rt\":\"009\",\"rw\":\"008\",\"kelurahan\":\"SERUA INDAH\",\"kecamatan\":\"CIPUTAT\",\"kota\":\"KOTA TANGERANG SELATAN\",\"provinsi\":\"BANTEN\",\"kode_pos\":\"15414\",\"fields_need_review\":[]},\"anggota\":[{\"nama_lengkap\":\"MUHAMMAD CHENDY SETIADI\",\"nik\":\"3674062609890006\",\"jenis_kelamin\":\"L\",\"tempat_lahir\":\"TANGERANG\",\"tanggal_lahir\":\"1989-09-26\",\"agama\":\"ISLAM DIPLOMA\",\"pendidikan\":\"IV\\/STRATA |\",\"pekerjaan\":\"KARYAWAN SWASTA\",\"golongan_darah\":\"TIDAK TAHU\",\"hubungan_keluarga\":\"Kepala Keluarga\",\"fields_need_review\":[]},{\"nama_lengkap\":\"DEWI SINTA SARI\",\"nik\":\"3674046502950009\",\"jenis_kelamin\":\"P\",\"tempat_lahir\":\"TANGERANG\",\"tanggal_lahir\":\"1995-02-25\",\"agama\":\"ISLAM\",\"pendidikan\":\"AKADEMI\\/DIPLOMA III\\/SARJANA MUDA\",\"pekerjaan\":\"KARYAWAN SWASTA\",\"golongan_darah\":\"TIDAK TAHU\",\"hubungan_keluarga\":\"Istri\",\"fields_need_review\":[]},{\"nama_lengkap\":\"EMMERIL KHAIRAN HAMIZAN\",\"nik\":\"3674060410170002\",\"jenis_kelamin\":\"L\",\"tempat_lahir\":\"TANGERANG SELATAN\",\"tanggal_lahir\":\"2017-10-04\",\"agama\":\"ISLAM TIDAK\\/BELUM\",\"pendidikan\":\"SEKOLAH\",\"pekerjaan\":\"BELUM\\/TIDAK BEKERJA\",\"golongan_darah\":\"TIDAK TAHU\",\"hubungan_keluarga\":\"Anak\",\"fields_need_review\":[]},{\"nama_lengkap\":\"SHADLEEN HAZZEMA SHAHIA\",\"nik\":\"3674044611210002\",\"jenis_kelamin\":\"P\",\"tempat_lahir\":\"TANGERANG SELATAN\",\"tanggal_lahir\":\"2021-11-06\",\"agama\":\"ISLAM TIDAK\\/BELUM\",\"pendidikan\":\"SEKOLAH\",\"pekerjaan\":\"BELUM\\/TIDAK BEKERJA\",\"golongan_darah\":\"TIDAK TAHU\",\"hubungan_keluarga\":\"Anak\",\"fields_need_review\":[]},{\"nama_lengkap\":\"MUHAMMAD ARVA KAUTSARRAZKY\",\"nik\":\"3674040407240004\",\"jenis_kelamin\":\"L\",\"tempat_lahir\":\"TANGERANG SELATAN\",\"tanggal_lahir\":\"2024-07-04\",\"agama\":\"ISLAM TIDAK\\/BELUM\",\"pendidikan\":\"SEKOLAH\",\"pekerjaan\":\"BELUM\\/TIDAK BEKERJA\",\"golongan_darah\":\"TIDAK TAHU\",\"hubungan_keluarga\":\"Anak\",\"fields_need_review\":[]}],\"jumlah_anggota\":5}', 'lanjutkan', 1, '2026-11-05 11:33:10', '2026-11-05 10:37:42'),
(30, 22, 'rejected', '{\"keluarga\":{\"nomor_kk\":\"3674041612200024\",\"alamat\":\"JL SUKA MULYA IV NO. 17\",\"rt\":\"009\",\"rw\":\"008\",\"kelurahan\":\"SERUA INDAH\",\"kecamatan\":\"CIPUTAT\",\"kota\":\"KOTA TANGERANG SELATAN\",\"provinsi\":\"BANTEN\",\"kode_pos\":\"15414\",\"fields_need_review\":[]},\"anggota\":[{\"nama_lengkap\":\"MUHAMMAD CHENDY SETIADI\",\"nik\":\"3674062609890006\",\"jenis_kelamin\":\"L\",\"tempat_lahir\":\"TANGERANG\",\"tanggal_lahir\":\"1989-09-26\",\"agama\":\"ISLAM DIPLOMA\",\"pendidikan\":\"IV\\/STRATA |\",\"pekerjaan\":\"KARYAWAN SWASTA\",\"golongan_darah\":\"TIDAK TAHU\",\"hubungan_keluarga\":\"Suami\",\"fields_need_review\":[]},{\"nama_lengkap\":\"DEWI SINTA SARI\",\"nik\":\"3674046502950009\",\"jenis_kelamin\":\"P\",\"tempat_lahir\":\"TANGERANG\",\"tanggal_lahir\":\"1995-02-25\",\"agama\":\"ISLAM\",\"pendidikan\":\"AKADEMI\\/DIPLOMA III\\/SARJANA MUDA\",\"pekerjaan\":\"KARYAWAN SWASTA\",\"golongan_darah\":\"TIDAK TAHU\",\"hubungan_keluarga\":\"Kepala Keluarga\",\"fields_need_review\":[]},{\"nama_lengkap\":\"EMMERIL KHAIRAN HAMIZAN\",\"nik\":\"3674060410170002\",\"jenis_kelamin\":\"L\",\"tempat_lahir\":\"TANGERANG SELATAN\",\"tanggal_lahir\":\"2017-10-04\",\"agama\":\"ISLAM TIDAK\\/BELUM\",\"pendidikan\":\"SEKOLAH\",\"pekerjaan\":\"BELUM\\/TIDAK BEKERJA\",\"golongan_darah\":\"TIDAK TAHU\",\"hubungan_keluarga\":\"Anak\",\"fields_need_review\":[]},{\"nama_lengkap\":\"SHADLEEN HAZZEMA SHAHIA\",\"nik\":\"3674044611210002\",\"jenis_kelamin\":\"P\",\"tempat_lahir\":\"TANGERANG SELATAN\",\"tanggal_lahir\":\"2021-11-06\",\"agama\":\"ISLAM TIDAK\\/BELUM\",\"pendidikan\":\"SEKOLAH\",\"pekerjaan\":\"BELUM\\/TIDAK BEKERJA\",\"golongan_darah\":\"TIDAK TAHU\",\"hubungan_keluarga\":\"Anak\",\"fields_need_review\":[]},{\"nama_lengkap\":\"MUHAMMAD ARVA KAUTSARRAZKY\",\"nik\":\"3674040407240004\",\"jenis_kelamin\":\"L\",\"tempat_lahir\":\"TANGERANG SELATAN\",\"tanggal_lahir\":\"2024-07-04\",\"agama\":\"ISLAM TIDAK\\/BELUM\",\"pendidikan\":\"SEKOLAH\",\"pekerjaan\":\"BELUM\\/TIDAK BEKERJA\",\"golongan_darah\":\"TIDAK TAHU\",\"hubungan_keluarga\":\"Anak\",\"fields_need_review\":[]}],\"jumlah_anggota\":5}', 'double', 1, '2026-11-08 08:32:23', '2026-11-08 08:22:19');

-- --------------------------------------------------------

--
-- Table structure for table `letter_types`
--

CREATE TABLE `letter_types` (
  `type_key` varchar(40) NOT NULL,
  `title` varchar(160) NOT NULL,
  `category` varchar(40) NOT NULL,
  `description` varchar(255) NOT NULL,
  `body_template` text DEFAULT NULL,
  `icon_path` varchar(255) NOT NULL DEFAULT '',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `letter_types`
--

INSERT INTO `letter_types` (`type_key`, `title`, `category`, `description`, `body_template`, `icon_path`, `sort_order`, `is_active`) VALUES
('aduan', 'Aduan / Saran', 'Lainnya', 'Sampaikan keluhan atau saran untuk pengurus RT.', 'Yang bertanda tangan di bawah ini:\nNama : {{nama_warga}}\nNIK : {{nik_warga}}\nAlamat : {{alamat_domisili}}\n\nMenyampaikan aduan kategori {{issue_category}} mengenai kejadian di {{incident_location}} pada {{incident_date}}. Uraian: {{keterangan}}. Tindak lanjut yang diharapkan: {{expected_followup}}.\nDemikian aduan ini disampaikan kepada pengurus RT untuk ditindaklanjuti.', '', 16, 1),
('ahli_waris', 'Surat Pengantar Ahli Waris', 'Keterangan', 'Pengantar proses administrasi ahli waris.', 'Yang bertanda tangan di bawah ini, {{jabatan_ketua_rt}} {{wilayah_rt}}, menerangkan bahwa menurut laporan warga, {{heir_deceased_name}} yang terakhir beralamat di {{heir_deceased_address}} meninggal dunia pada {{heir_death_date}}. Nama pihak yang dilaporkan sebagai ahli waris: {{heir_names}}. Hubungan para pihak: {{heir_relationships}}.\nPelapor: {{nama_warga}}, NIK {{nik_warga}}. Keterangan ini disampaikan untuk keperluan {{keperluan_surat}} dan tetap memerlukan pemeriksaan dokumen oleh instansi berwenang.\n\nDemikian surat ini dibuat berdasarkan keterangan yang disampaikan dan data yang tersedia, untuk dipergunakan sebagaimana mestinya.', '', 12, 1),
('domisili', 'Surat Keterangan Domisili', 'Kependudukan', 'Keterangan tempat tinggal warga RT 009/008.', 'Yang bertanda tangan di bawah ini, {{jabatan_ketua_rt}} {{wilayah_rt}}, menerangkan bahwa:\nNama : {{nama_warga}}\nNIK : {{nik_warga}}\nTempat, Tanggal Lahir : {{tempat_tanggal_lahir}}\nJenis Kelamin : {{jenis_kelamin}}\nPekerjaan : {{pekerjaan}}\nAlamat Domisili : {{alamat_domisili}}\n\nBerdasarkan keterangan yang tersedia pada pengurus RT, yang bersangkutan berdomisili di alamat tersebut sejak {{domicile_since}}. Status tempat tinggal: {{residence_status}}. Kepala keluarga: {{household_head}}.\nSurat keterangan domisili ini dimohon untuk {{keperluan_surat}}.\n\nDemikian surat ini dibuat berdasarkan keterangan yang disampaikan dan data yang tersedia, untuk dipergunakan sebagaimana mestinya.', '', 2, 1),
('kegiatan', 'Surat Pengantar Kegiatan Warga', 'Kegiatan', 'Pemberitahuan kegiatan atau acara lingkungan.', 'Yang bertanda tangan di bawah ini, {{jabatan_ketua_rt}} {{wilayah_rt}}, menerangkan bahwa warga merencanakan kegiatan {{event_name}} pada {{event_date}} pukul {{event_time}} di {{event_location}}. Penanggung jawab kegiatan: {{event_organizer}}. Pemohon: {{nama_warga}}, NIK {{nik_warga}}. Kegiatan ini disampaikan kepada pengurus RT untuk keperluan {{keperluan_surat}}.\n\nDemikian surat ini dibuat berdasarkan keterangan yang disampaikan dan data yang tersedia, untuk dipergunakan sebagaimana mestinya.', '', 14, 1),
('kehilangan', 'Surat Pengantar Kehilangan', 'Keterangan', 'Pengantar untuk pengurusan dokumen yang hilang.', 'Yang bertanda tangan di bawah ini, {{jabatan_ketua_rt}} {{wilayah_rt}}, menerangkan bahwa:\nNama : {{nama_warga}}\nNIK : {{nik_warga}}\nTempat, Tanggal Lahir : {{tempat_tanggal_lahir}}\nJenis Kelamin : {{jenis_kelamin}}\nPekerjaan : {{pekerjaan}}\nAlamat Domisili : {{alamat_domisili}}\n\nYang bersangkutan melaporkan kehilangan {{lost_item}}, nomor atau identitas {{lost_number}}, pada {{loss_date}} di {{loss_place}}. Kronologi menurut pelapor: {{loss_chronology}}. Surat ini dimohon sebagai pengantar pelaporan kepada pihak berwenang.\n\nDemikian surat ini dibuat berdasarkan keterangan yang disampaikan dan data yang tersedia, untuk dipergunakan sebagaimana mestinya.', '', 13, 1),
('kelahiran', 'Surat Pengantar Kelahiran', 'Keterangan', 'Laporan kelahiran bayi di lingkungan RT.', 'Yang bertanda tangan di bawah ini, {{jabatan_ketua_rt}} {{wilayah_rt}}, menerangkan bahwa berdasarkan laporan warga telah terjadi kelahiran dengan data:\nNama Bayi : {{baby_name}}\nJenis Kelamin : {{baby_gender}}\nTempat, Tanggal Lahir : {{baby_birth_place}}, {{baby_birth_date}}\nWaktu Lahir : {{baby_birth_time}}\nAnak Ke : {{child_number}}\nNama Ayah / NIK : {{father_name}} / {{father_nik}}\nNama Ibu / NIK : {{mother_name}} / {{mother_nik}}\nPenolong Kelahiran : {{birth_assistant}}\n\nPelapor: {{nama_warga}}, NIK {{nik_warga}}, alamat {{alamat_domisili}}. Surat ini dimohon untuk {{keperluan_surat}}.\n\nDemikian surat ini dibuat berdasarkan keterangan yang disampaikan dan data yang tersedia, untuk dipergunakan sebagaimana mestinya.', '', 4, 1),
('kematian', 'Surat Keterangan Kematian', 'Keterangan', 'Laporan warga yang meninggal dunia.', 'Yang bertanda tangan di bawah ini, {{jabatan_ketua_rt}} {{wilayah_rt}}, menerangkan bahwa:\nNama Almarhum/Almarhumah : {{deceased_name}}\nNIK : {{deceased_nik}}\nTempat, Tanggal Lahir : {{deceased_birth_place}}, {{deceased_birth_date}}\nJenis Kelamin : {{deceased_gender}}\nAgama : {{deceased_religion}}\nAlamat Terakhir : {{last_address}}\n\nMenurut laporan dari {{nama_warga}}, hubungan dengan almarhum/almarhumah: {{relationship}}, yang bersangkutan meninggal dunia pada {{death_date}} pukul {{death_time}} di {{death_place}}. Keterangan sebab meninggal: {{death_cause}}.\nSurat ini dimohon untuk {{keperluan_surat}}.\n\nDemikian surat ini dibuat berdasarkan keterangan yang disampaikan dan data yang tersedia, untuk dipergunakan sebagaimana mestinya.', '', 3, 1),
('ktp_kk', 'Surat Pengantar KTP / KK', 'Kependudukan', 'Pengurusan atau perubahan dokumen kependudukan.', 'Yang bertanda tangan di bawah ini, {{jabatan_ketua_rt}} {{wilayah_rt}}, menerangkan bahwa:\nNama : {{nama_warga}}\nNIK : {{nik_warga}}\nTempat, Tanggal Lahir : {{tempat_tanggal_lahir}}\nJenis Kelamin : {{jenis_kelamin}}\nPekerjaan : {{pekerjaan}}\nAlamat Domisili : {{alamat_domisili}}\n\nYang bersangkutan memohon pengantar layanan kependudukan: {{document_service}}. Nomor KK saat ini: {{current_kk_number}}. Data yang dibuat atau diubah: {{data_change}}. Keperluan: {{keperluan_surat}}.\n\nDemikian surat ini dibuat berdasarkan keterangan yang disampaikan dan data yang tersedia, untuk dipergunakan sebagaimana mestinya.', '', 7, 1),
('lainnya', 'Keperluan Lainnya', 'Lainnya', 'Ajukan keperluan administrasi lainnya.', 'Yang bertanda tangan di bawah ini, {{jabatan_ketua_rt}} {{wilayah_rt}}, menerangkan bahwa:\nNama : {{nama_warga}}\nNIK : {{nik_warga}}\nTempat, Tanggal Lahir : {{tempat_tanggal_lahir}}\nJenis Kelamin : {{jenis_kelamin}}\nPekerjaan : {{pekerjaan}}\nAlamat Domisili : {{alamat_domisili}}\n\nYang bersangkutan mengajukan {{other_subject}} kepada {{other_destination}} dengan rincian: {{other_details}}. Keperluan surat: {{keperluan_surat}}.\n\nDemikian surat ini dibuat berdasarkan keterangan yang disampaikan dan data yang tersedia, untuk dipergunakan sebagaimana mestinya.', '', 17, 1),
('nikah', 'Surat Pengantar Nikah', 'Keterangan', 'Pengurusan administrasi pernikahan.', 'Yang bertanda tangan di bawah ini, {{jabatan_ketua_rt}} {{wilayah_rt}}, menerangkan bahwa:\nNama : {{nama_warga}}\nNIK : {{nik_warga}}\nTempat, Tanggal Lahir : {{tempat_tanggal_lahir}}\nJenis Kelamin : {{jenis_kelamin}}\nPekerjaan : {{pekerjaan}}\nAlamat Domisili : {{alamat_domisili}}\n\nYang bersangkutan menyampaikan rencana pernikahan dengan {{partner_name}}, NIK {{partner_nik}}, pada {{marriage_date}} di {{marriage_place}}. Surat pengantar ini dimohon untuk melengkapi administrasi pernikahan.\n\nDemikian surat ini dibuat berdasarkan keterangan yang disampaikan dan data yang tersedia, untuk dipergunakan sebagaimana mestinya.', '', 9, 1),
('pindah', 'Surat Pengantar Pindah Domisili', 'Kependudukan', 'Pengurusan perpindahan alamat domisili.', 'Yang bertanda tangan di bawah ini, {{jabatan_ketua_rt}} {{wilayah_rt}}, menerangkan bahwa:\nNama : {{nama_warga}}\nNIK : {{nik_warga}}\nTempat, Tanggal Lahir : {{tempat_tanggal_lahir}}\nJenis Kelamin : {{jenis_kelamin}}\nPekerjaan : {{pekerjaan}}\nAlamat Domisili : {{alamat_domisili}}\n\nYang bersangkutan menyampaikan rencana pindah dari {{move_origin}} ke {{move_destination}} pada {{move_date}}. Alasan pindah: {{move_reason}}. Anggota keluarga yang ikut pindah: {{moving_people}}.\nSurat pengantar ini dimohon untuk {{keperluan_surat}}.\n\nDemikian surat ini dibuat berdasarkan keterangan yang disampaikan dan data yang tersedia, untuk dipergunakan sebagaimana mestinya.', '', 5, 1),
('renovasi', 'Surat Pengantar Renovasi Rumah', 'Kegiatan', 'Pemberitahuan kegiatan renovasi rumah.', 'Yang bertanda tangan di bawah ini, {{jabatan_ketua_rt}} {{wilayah_rt}}, menerangkan bahwa:\nNama : {{nama_warga}}\nNIK : {{nik_warga}}\nTempat, Tanggal Lahir : {{tempat_tanggal_lahir}}\nJenis Kelamin : {{jenis_kelamin}}\nPekerjaan : {{pekerjaan}}\nAlamat Domisili : {{alamat_domisili}}\n\nYang bersangkutan menyampaikan rencana renovasi rumah di {{property_address}} berupa {{renovation_work}}, mulai {{renovation_start}} sampai {{renovation_end}}. Surat pengantar ini dibuat untuk pemberitahuan kepada lingkungan setempat.\n\nDemikian surat ini dibuat berdasarkan keterangan yang disampaikan dan data yang tersedia, untuk dipergunakan sebagaimana mestinya.', '', 15, 1),
('skck', 'Surat Pengantar SKCK', 'Keterangan', 'Pengantar untuk keperluan pengurusan SKCK.', 'Yang bertanda tangan di bawah ini, {{jabatan_ketua_rt}} {{wilayah_rt}}, menerangkan bahwa:\nNama : {{nama_warga}}\nNIK : {{nik_warga}}\nTempat, Tanggal Lahir : {{tempat_tanggal_lahir}}\nJenis Kelamin : {{jenis_kelamin}}\nPekerjaan : {{pekerjaan}}\nAlamat Domisili : {{alamat_domisili}}\n\nYang bersangkutan memohon pengantar untuk pengurusan Surat Keterangan Catatan Kepolisian pada {{police_destination}} dengan keperluan {{skck_reason}}. Surat ini merupakan pengantar dari pengurus RT setempat.\n\nDemikian surat ini dibuat berdasarkan keterangan yang disampaikan dan data yang tersedia, untuk dipergunakan sebagaimana mestinya.', '', 8, 1),
('surat_pengantar', 'Surat Pengantar', 'Kependudukan', 'Untuk berbagai keperluan administrasi ke kelurahan atau instansi lainnya.', 'Yang bertanda tangan di bawah ini, {{jabatan_ketua_rt}} {{wilayah_rt}}, menerangkan bahwa:\nNama : {{nama_warga}}\nNIK : {{nik_warga}}\nTempat, Tanggal Lahir : {{tempat_tanggal_lahir}}\nJenis Kelamin : {{jenis_kelamin}}\nPekerjaan : {{pekerjaan}}\nAlamat Domisili : {{alamat_domisili}}\n\nYang bersangkutan memohon surat pengantar kepada {{tujuan_instansi}} untuk urusan {{jenis_urusan}}. Nomor Kartu Keluarga: {{nomor_kk}}.\nKeperluan: {{keperluan_surat}}.\n\nDemikian surat ini dibuat berdasarkan keterangan yang disampaikan dan data yang tersedia, untuk dipergunakan sebagaimana mestinya.', '', 1, 1),
('tidak_mampu', 'Surat Pengantar Tidak Mampu', 'Keterangan', 'Pengantar untuk pengajuan bantuan sosial atau pendidikan.', 'Yang bertanda tangan di bawah ini, {{jabatan_ketua_rt}} {{wilayah_rt}}, menerangkan bahwa:\nNama : {{nama_warga}}\nNIK : {{nik_warga}}\nTempat, Tanggal Lahir : {{tempat_tanggal_lahir}}\nJenis Kelamin : {{jenis_kelamin}}\nPekerjaan : {{pekerjaan}}\nAlamat Domisili : {{alamat_domisili}}\n\nBerdasarkan keterangan yang disampaikan, penghasilan keluarga per bulan adalah {{income_range}} dengan {{dependents}} tanggungan. Yang bersangkutan memohon keterangan untuk {{assistance_purpose}}. Surat ini diberikan untuk pertimbangan pihak yang berwenang.\n\nDemikian surat ini dibuat berdasarkan keterangan yang disampaikan dan data yang tersedia, untuk dipergunakan sebagaimana mestinya.', '', 11, 1),
('usaha', 'Surat Keterangan Usaha', 'Keterangan', 'Pengantar untuk keperluan administrasi usaha warga.', 'Yang bertanda tangan di bawah ini, {{jabatan_ketua_rt}} {{wilayah_rt}}, menerangkan bahwa:\nNama : {{nama_warga}}\nNIK : {{nik_warga}}\nTempat, Tanggal Lahir : {{tempat_tanggal_lahir}}\nJenis Kelamin : {{jenis_kelamin}}\nPekerjaan : {{pekerjaan}}\nAlamat Domisili : {{alamat_domisili}}\n\nBerdasarkan keterangan yang disampaikan, yang bersangkutan menjalankan usaha {{business_name}} di bidang {{business_type}}, beralamat di {{business_address}}, sejak {{business_since}}. Surat ini dimohon untuk {{keperluan_surat}}.\n\nDemikian surat ini dibuat berdasarkan keterangan yang disampaikan dan data yang tersedia, untuk dipergunakan sebagaimana mestinya.', '', 10, 1),
('warga_baru', 'Surat Pengantar Warga Baru', 'Kependudukan', 'Laporan warga yang baru menetap di lingkungan RT.', 'Yang bertanda tangan di bawah ini, {{jabatan_ketua_rt}} {{wilayah_rt}}, menerangkan bahwa:\nNama : {{nama_warga}}\nNIK : {{nik_warga}}\nTempat, Tanggal Lahir : {{tempat_tanggal_lahir}}\nJenis Kelamin : {{jenis_kelamin}}\nPekerjaan : {{pekerjaan}}\nAlamat Domisili : {{alamat_domisili}}\n\nYang bersangkutan melaporkan telah mulai menetap di lingkungan ini sejak {{arrival_date}}, sebelumnya beralamat di {{previous_address}}. Kepala keluarga: {{new_household_head}}. Anggota keluarga yang ikut menetap: {{arriving_people}}.\n\nDemikian surat ini dibuat berdasarkan keterangan yang disampaikan dan data yang tersedia, untuk dipergunakan sebagaimana mestinya.', '', 6, 1);

-- --------------------------------------------------------

--
-- Table structure for table `notification_outbox`
--

CREATE TABLE `notification_outbox` (
  `id` int(10) UNSIGNED NOT NULL,
  `citizen_id` int(10) UNSIGNED DEFAULT NULL,
  `channel` varchar(20) NOT NULL DEFAULT 'email',
  `recipient` varchar(190) NOT NULL,
  `subject` varchar(190) NOT NULL,
  `body` text NOT NULL,
  `dedupe_key` varchar(120) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `attempts` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `last_error` varchar(300) DEFAULT NULL,
  `next_attempt_at` datetime DEFAULT NULL,
  `sent_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `officials`
--

CREATE TABLE `officials` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(140) NOT NULL,
  `position` varchar(140) NOT NULL,
  `description` varchar(500) NOT NULL DEFAULT '',
  `icon` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '??',
  `photo` varchar(255) DEFAULT NULL,
  `parent_id` int(10) UNSIGNED DEFAULT NULL,
  `contact` varchar(30) NOT NULL DEFAULT '',
  `term_start` year(4) DEFAULT NULL,
  `term_end` year(4) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `officials`
--

INSERT INTO `officials` (`id`, `name`, `position`, `description`, `icon`, `photo`, `parent_id`, `contact`, `term_start`, `term_end`, `sort_order`, `is_active`) VALUES
(1, 'Rohmat', 'Ketua RT', 'Bertanggung jawab atas pengelolaan dan pelayanan RT secara keseluruhan.', '👑', 'uploads/pengurus/fc3bc310c6ad40695b3934f1017b2edd.png', NULL, '', '2020', '2026', 1, 1),
(2, 'Andi', 'Admin Layanan', 'Mengelola pengajuan layanan warga dan memberikan informasi.', '🪪', NULL, 1, '', NULL, NULL, 2, 1),
(3, 'Sari', 'Admin Keuangan', 'Mengelola administrasi pembayaran dan keuangan.', '💰', NULL, 1, '', NULL, NULL, 3, 1),
(4, 'Budi', 'Admin Data / Mapping', 'Mengelola data warga dan pemetaan lokasi.', '🗺️', NULL, 1, '', NULL, NULL, 4, 1),
(5, 'Agus', 'Petugas Lapangan', 'Melaksanakan layanan di area RT, termasuk penggalian dan perawatan.', '🛠️', NULL, 1, '', NULL, NULL, 5, 1),
(6, 'Dani', 'Support / IT', 'Memberikan dukungan sistem, data, dan infrastruktur.', '💻', NULL, 1, '', NULL, NULL, 6, 1);

-- --------------------------------------------------------

--
-- Table structure for table `pages`
--

CREATE TABLE `pages` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(160) NOT NULL,
  `slug` varchar(180) NOT NULL,
  `body` longtext NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `show_in_menu` tinyint(1) NOT NULL DEFAULT 1,
  `is_published` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pages`
--

INSERT INTO `pages` (`id`, `title`, `slug`, `body`, `image`, `sort_order`, `show_in_menu`, `is_published`) VALUES
(1, 'Profil Lingkungan', 'profil', 'Informasi sejarah, batas wilayah, dan fasilitas RT 09 / RW 08 akan dilengkapi oleh pengurus.', NULL, 1, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `patrol_schedules`
--

CREATE TABLE `patrol_schedules` (
  `id` int(10) UNSIGNED NOT NULL,
  `patrol_date` date NOT NULL,
  `shift` varchar(20) NOT NULL,
  `post_location` varchar(120) NOT NULL DEFAULT 'Pos Ronda',
  `officer_name` varchar(140) NOT NULL,
  `citizen_id` int(10) UNSIGNED DEFAULT NULL,
  `attendance` varchar(20) NOT NULL DEFAULT 'scheduled',
  `check_in` datetime DEFAULT NULL,
  `check_out` datetime DEFAULT NULL,
  `substitute_name` varchar(140) NOT NULL DEFAULT '',
  `attendance_note` varchar(255) NOT NULL DEFAULT '',
  `marked_by_admin` int(10) UNSIGNED DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `platform_admins`
--

CREATE TABLE `platform_admins` (
  `id` int(10) UNSIGNED NOT NULL,
  `username` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `is_active` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `platform_login_attempts`
--

CREATE TABLE `platform_login_attempts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ip_hash` char(64) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `id` int(10) UNSIGNED NOT NULL,
  `type` enum('pengumuman','agenda','galeri') NOT NULL,
  `title` varchar(180) NOT NULL,
  `body` text NOT NULL,
  `event_date` date DEFAULT NULL,
  `event_time` time DEFAULT NULL,
  `event_location` varchar(160) NOT NULL DEFAULT '',
  `image` varchar(255) DEFAULT NULL,
  `status` enum('draft','pending','published') NOT NULL DEFAULT 'draft',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`id`, `type`, `title`, `body`, `event_date`, `event_time`, `event_location`, `image`, `status`, `created_at`) VALUES
(2, 'pengumuman', 'LOMBA 17 AGUSTUS – SEMARAK HUT KEMERDEKAAN RI DI SUKA MULYA IV', '🇮🇩 **PENGUMUMAN LOMBA 17 AGUSTUS** 🇮🇩\r\n\r\nDalam rangka memeriahkan Hari Ulang Tahun Kemerdekaan Republik Indonesia, pengurus RT 009/RW 008 mengundang seluruh warga Suka Mulya IV untuk berpartisipasi dalam kegiatan **Lomba 17 Agustus**.\r\n\r\n**Lokasi Kegiatan:**\r\nLingkungan Suka Mulya IV, RT 009/RW 008, Kelurahan Serua Indah, Kota Tangerang Selatan.\r\n\r\n**Waktu Kegiatan:**\r\nTanggal: 17 Agustus (tahun menyesuaikan)\r\nPukul: 08.00 WIB – selesai (rencana)\r\n\r\n**Jenis Perlombaan:**\r\n1. Lomba makan kerupuk\r\n2. Lomba balap karung\r\n3. Lomba memasukkan paku ke botol\r\n4. Lomba membawa kelereng dengan sendok\r\n5. Lomba tarik tambang\r\n6. Lomba joget balon\r\n7. Lomba mewarnai untuk anak-anak\r\n8. Lomba keluarga dan pembagian hadiah\r\n\r\nKegiatan ini terbuka untuk anak-anak, remaja, dan orang tua di lingkungan RT 009/RW 008.\r\n\r\nMari kita meriahkan Hari Kemerdekaan dengan semangat kebersamaan, kekompakan, dan gotong royong.\r\n\r\n**Merdeka! Merdeka! Merdeka! 🇮🇩**\r\n\r\nHormat kami,\r\nPanitia HUT RI\r\nPengurus RT 009/RW 008 – Suka Mulya IV\r\n\r\n*Catatan: Jadwal dan daftar perlombaan merupakan contoh dan dapat disesuaikan oleh panitia.*', NULL, NULL, '', 'uploads/3561b6b57e566b88d5645e3d66db3626.png', 'published', '2026-10-17 17:21:08'),
(7, 'galeri', 'Kerja Bakti Lingkungan', 'Warga bergotong royong membersihkan jalan, taman, dan area sekitar lingkungan RT. Kegiatan rutin ini menjaga lingkungan tetap bersih dan nyaman.', NULL, NULL, 'Lingkungan RT 009/008', 'uploads/gallery-dummy/kerja-bakti-warga.png', 'published', '2026-10-26 08:15:00'),
(8, 'galeri', 'Semarak Lomba Kemerdekaan', 'Anak-anak dan keluarga warga mengikuti perlombaan kemerdekaan dengan penuh semangat dan kebersamaan.', NULL, NULL, 'Lapangan Warga RT 009/008', 'uploads/gallery-dummy/lomba-kemerdekaan.png', 'published', '2026-08-17 10:30:00'),
(9, 'galeri', 'Kegiatan Posyandu Balita', 'Pelayanan kesehatan rutin bagi ibu dan balita bersama kader lingkungan untuk memantau tumbuh kembang anak.', NULL, NULL, 'Balai Warga RT 009/008', 'uploads/gallery-dummy/posyandu-balita.png', 'published', '2026-10-18 09:00:00'),
(10, 'galeri', 'Pengajian dan Silaturahmi Warga', 'Kegiatan pengajian rutin sekaligus mempererat silaturahmi dan kebersamaan antarwarga.', NULL, NULL, 'Balai Warga RT 009/008', 'uploads/gallery-dummy/pengajian-warga.png', 'published', '2026-10-12 19:30:00'),
(11, 'pengumuman', 'Rapat Koordinasi Warga Bulanan', 'Pengurus mengundang seluruh warga untuk mengikuti rapat koordinasi bulanan membahas keamanan, kebersihan, dan program lingkungan.', NULL, NULL, 'Balai Warga RT 009/008', 'uploads/content-dummy/rapat-warga.png', 'published', '2026-10-28 18:00:00'),
(12, 'pengumuman', 'Jadwal Fogging Lingkungan', 'Kegiatan fogging akan dilaksanakan sebagai langkah pencegahan demam berdarah. Warga diminta menjaga keamanan makanan dan hewan peliharaan.', NULL, NULL, 'Lingkungan RT 009/008', 'uploads/content-dummy/fogging-lingkungan.png', 'published', '2026-10-27 09:00:00'),
(13, 'pengumuman', 'Senam Sehat Bersama Warga', 'Ayo ikuti senam sehat bersama untuk menjaga kebugaran sekaligus mempererat kebersamaan warga.', NULL, NULL, 'Lapangan Warga RT 009/008', 'uploads/content-dummy/senam-warga.png', 'published', '2026-10-25 07:00:00'),
(14, 'agenda', 'Senam Pagi Bersama', 'Kegiatan senam pagi rutin untuk seluruh warga.', '2026-11-01', '07:00:00', 'Lapangan Warga RT 009/008', 'uploads/content-dummy/senam-warga.png', 'published', '2026-10-28 10:00:00'),
(15, 'agenda', 'Rapat Koordinasi November', 'Pertemuan warga membahas program kerja, keamanan, kebersihan, dan pelayanan lingkungan.', '2026-11-07', '19:30:00', 'Balai Warga RT 009/008', 'uploads/content-dummy/rapat-warga.png', 'published', '2026-10-28 10:10:00'),
(16, 'agenda', 'Bazar UMKM Warga', 'Bazar produk makanan, minuman, dan kerajinan hasil usaha warga lingkungan.', '2026-11-15', '08:00:00', 'Jalan Utama RT 009/008', 'uploads/content-dummy/bazar-warga.png', 'published', '2026-10-28 10:20:00'),
(17, 'agenda', 'Kerja Bakti Akhir Bulan', 'Gotong royong membersihkan saluran air, taman, dan jalan lingkungan.', '2026-11-29', '07:00:00', 'Lingkungan RT 009/008', 'uploads/gallery-dummy/kerja-bakti-warga.png', 'published', '2026-10-28 10:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `program_agendas`
--

CREATE TABLE `program_agendas` (
  `program_id` int(10) UNSIGNED NOT NULL,
  `post_id` int(10) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `program_milestones`
--

CREATE TABLE `program_milestones` (
  `id` int(10) UNSIGNED NOT NULL,
  `program_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(160) NOT NULL,
  `due_date` date DEFAULT NULL,
  `weight` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  `is_done` tinyint(1) NOT NULL DEFAULT 0,
  `done_at` datetime DEFAULT NULL,
  `note` varchar(255) NOT NULL DEFAULT '',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_items`
--

CREATE TABLE `purchase_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `purchase_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(160) NOT NULL,
  `qty` int(11) NOT NULL DEFAULT 1,
  `unit` varchar(30) NOT NULL DEFAULT 'unit',
  `est_price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `actual_price` decimal(15,2) DEFAULT NULL,
  `qty_received` int(11) NOT NULL DEFAULT 0,
  `to_inventory` tinyint(1) NOT NULL DEFAULT 0,
  `asset_id` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_requests`
--

CREATE TABLE `purchase_requests` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(160) NOT NULL,
  `reason` text DEFAULT NULL,
  `vendor` varchar(140) NOT NULL DEFAULT '',
  `program_id` int(10) UNSIGNED DEFAULT NULL,
  `estimated_total` decimal(15,2) NOT NULL DEFAULT 0.00,
  `actual_total` decimal(15,2) DEFAULT NULL,
  `actual_note` varchar(255) NOT NULL DEFAULT '',
  `is_donation` tinyint(1) NOT NULL DEFAULT 0,
  `status` varchar(20) NOT NULL DEFAULT 'draft',
  `requested_by` int(10) UNSIGNED DEFAULT NULL,
  `approved_by` int(10) UNSIGNED DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `review_note` varchar(500) DEFAULT NULL,
  `proof` varchar(120) DEFAULT NULL,
  `transaction_id` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quick_links`
--

CREATE TABLE `quick_links` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(100) NOT NULL,
  `subtitle` varchar(160) NOT NULL DEFAULT '',
  `icon_image` varchar(255) NOT NULL DEFAULT '',
  `url_path` varchar(180) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `quick_links`
--

INSERT INTO `quick_links` (`id`, `title`, `subtitle`, `icon_image`, `url_path`, `sort_order`, `is_active`) VALUES
(1, 'Pengumuman', 'Info terbaru untuk warga', 'assets/mockup/icon-pengumuman.png', 'pengumuman', 1, 1),
(2, 'Agenda', 'Jadwal kegiatan RT', 'assets/mockup/icon-agenda.png', 'agenda', 2, 1),
(3, 'Profil RT', 'Struktur & informasi', 'assets/mockup/icon-profil.png', 'profil', 3, 1),
(4, 'Layanan Warga', 'Surat & pengajuan', 'assets/mockup/icon-layanan.png', 'warga', 4, 1),
(5, 'Iuran Warga', 'Informasi iuran lingkungan', 'assets/mockup/icon-iuran.png', 'warga/iuran', 5, 1),
(6, 'Galeri Kegiatan', 'Dokumentasi warga', 'assets/mockup/icon-galeri.png', 'galeri', 6, 1),
(7, 'Kontak RT', 'Hubungi pengurus', 'assets/mockup/icon-kontak.png', 'kontak', 7, 1);

-- --------------------------------------------------------

--
-- Table structure for table `resident_mutations`
--

CREATE TABLE `resident_mutations` (
  `id` int(10) UNSIGNED NOT NULL,
  `mutation_type` varchar(30) NOT NULL,
  `family_id` int(10) UNSIGNED DEFAULT NULL,
  `member_id` int(10) UNSIGNED DEFAULT NULL,
  `citizen_id` int(10) UNSIGNED DEFAULT NULL,
  `created_by_admin` int(10) UNSIGNED DEFAULT NULL,
  `effective_date` date DEFAULT NULL,
  `old_values_json` text DEFAULT NULL,
  `proposed_values_json` text NOT NULL,
  `reason` varchar(1000) NOT NULL DEFAULT '',
  `status` varchar(30) NOT NULL DEFAULT 'submitted',
  `reviewed_by` int(10) UNSIGNED DEFAULT NULL,
  `review_note` varchar(1000) DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `applied_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `role_menu_access`
--

CREATE TABLE `role_menu_access` (
  `tenant_id` int(10) UNSIGNED NOT NULL,
  `role` varchar(40) NOT NULL,
  `menu_path` varchar(120) NOT NULL,
  `allowed` tinyint(1) NOT NULL DEFAULT 1,
  `updated_by` int(10) UNSIGNED DEFAULT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `service_requests`
--

CREATE TABLE `service_requests` (
  `id` int(10) UNSIGNED NOT NULL,
  `citizen_id` int(10) UNSIGNED NOT NULL,
  `type` varchar(40) NOT NULL,
  `subject` varchar(180) NOT NULL,
  `description` text NOT NULL,
  `status` enum('draft','diajukan','diproses','disetujui','ditolak','selesai') NOT NULL DEFAULT 'diajukan',
  `admin_note` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `service_requests`
--

INSERT INTO `service_requests` (`id`, `citizen_id`, `type`, `subject`, `description`, `status`, `admin_note`, `created_at`, `updated_at`) VALUES
(12, 2, 'domisili', 'Surat Keterangan Domisili', 'Surat Keterangan', 'draft', NULL, '2026-10-17 19:03:04', NULL),
(41, 2, 'domisili', 'Surat Keterangan Domisili', '', 'disetujui', '', '2026-10-17 19:15:24', '2026-10-17 14:32:29'),
(54, 2, 'domisili', 'Surat Keterangan Domisili', '', 'disetujui', '', '2026-10-18 09:41:07', '2026-10-18 04:41:58'),
(55, 22, 'surat_pengantar', 'Surat Pengantar', '', 'disetujui', '', '2026-10-24 20:31:35', '2026-10-24 15:32:02');

-- --------------------------------------------------------

--
-- Table structure for table `service_request_details`
--

CREATE TABLE `service_request_details` (
  `request_id` int(10) UNSIGNED NOT NULL,
  `applicant_name` varchar(140) NOT NULL,
  `address` varchar(255) NOT NULL,
  `purpose` varchar(255) NOT NULL,
  `extra_data` text DEFAULT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `letter_number` varchar(100) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `approved_by` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `service_request_details`
--

INSERT INTO `service_request_details` (`request_id`, `applicant_name`, `address`, `purpose`, `extra_data`, `attachment`, `letter_number`, `approved_at`, `approved_by`) VALUES
(12, 'muhammad chendy setiadi', 'jln sukamulya IV RT 09 RW 08 No 17', 'Surat Keterangan', '{\"applicant_nik\":\"108093000064\",\"applicant_phone\":\"085697578958\",\"applicant_birth_place\":\"tangerang\",\"applicant_birth_date\":\"1989-09-26\",\"applicant_gender\":\"Laki-laki\",\"applicant_religion\":\"islam\",\"applicant_occupation\":\"karyawan swasta\",\"applicant_ktp_address\":\"jln sukamulya IV RT 09 RW 08 No 17\"}', NULL, NULL, NULL, NULL),
(41, 'muhammad chendy setiadi', 'jln sukamulya IV RT 09 RW 08 No 17', 'Surat Keterangan', '{\"applicant_nik\":\"1234567891011111\",\"applicant_phone\":\"085697578958\",\"applicant_birth_place\":\"tangerang\",\"applicant_birth_date\":\"\",\"applicant_gender\":\"Laki-laki\",\"applicant_religion\":\"islam\",\"applicant_occupation\":\"karyawan swasta\",\"applicant_ktp_address\":\"jln sukamulya IV RT 09 RW 08 No 17\"}', NULL, '001/RT/Tangsel/2026', '2026-10-17 14:32:29', 1),
(54, 'muhammad chendy setiadi', 'jln sukamulya IV RT 09 RW 08 No 17', 'untuk sekolah', '{\"applicant_nik\":\"1234567891011111\",\"applicant_phone\":\"085697578958\",\"applicant_birth_place\":\"tangerang\",\"applicant_birth_date\":\"1989-10-06\",\"applicant_gender\":\"Laki-laki\",\"applicant_religion\":\"islam\",\"applicant_occupation\":\"karyawan swasta\",\"applicant_ktp_address\":\"jln sukamulya IV RT 09 RW 08 No 17\",\"domicile_since\":\"2026-10-12\",\"residence_status\":\"tetap\",\"household_head\":\"chendy\"}', NULL, '111/rt/0/2026', '2026-10-18 04:41:58', 1),
(55, 'muhammad chendy setiadi', 'bumi pelita', '', '{\"applicant_nik\":\"1234567891234567\",\"applicant_phone\":\"\",\"applicant_birth_place\":\"tangerang selatan\",\"applicant_birth_date\":\"2026-10-24\",\"applicant_gender\":\"Laki-laki\",\"applicant_religion\":\"Islam\",\"applicant_occupation\":\"karyawan swasta\",\"applicant_ktp_address\":\"bumi pelita\",\"tujuan_instansi\":\"\",\"jenis_urusan\":\"\",\"nomor_kk\":\"\"}', NULL, '111/rt/0/2026', '2026-10-24 15:32:02', 1);

-- --------------------------------------------------------

--
-- Table structure for table `service_request_documents`
--

CREATE TABLE `service_request_documents` (
  `id` int(10) UNSIGNED NOT NULL,
  `request_id` int(10) UNSIGNED NOT NULL,
  `label` varchar(100) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `setting_key` varchar(80) NOT NULL,
  `setting_value` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('about_image', 'assets/mockup/hero-neighborhood.png'),
('about_text', 'RT 009/008 Suka Mulya IV adalah bagian dari lingkungan masyarakat yang menjunjung tinggi nilai gotong royong, kebersamaan, dan kepedulian antarwarga.'),
('about_title', 'Tentang RT 009/008'),
('address', 'Suka Mulya IV'),
('agenda_icon', 'assets/mockup/icon-calendar-small.png'),
('announcement_icon', 'assets/mockup/icon-announcement.png'),
('brand_label', 'RT 009/008'),
('brand_subtitle', 'Suka Mulya IV'),
('complaint_anonymous', '0'),
('dashboard_favorites_t1_c128', '[]'),
('dashboard_favorites_t1_c23', '[]'),
('email', ''),
('emergency_contact', ''),
('facebook_url', ''),
('female_residents', '0'),
('footer_banner_jpg', 'uploads/ff997dd0eb1fb7c6a92ce1677c6da5df.png'),
('footer_text', '┬® RT 09 / RW 08 Sukamulya.'),
('footer_wave', 'assets/mockup/footer-wave.png'),
('hero_accent', 'Suka Mulya IV'),
('hero_image', 'assets/mockup/hero-neighborhood.png'),
('hero_kicker', 'SELAMAT DATANG DI WEBSITE'),
('hero_text', 'Bersama Membangun Lingkungan yang Aman, Nyaman, Rukun dan Sejahtera.'),
('hero_title', 'RT 009/008'),
('home_banner_jpg', 'uploads/fcfb10544bfccb7d153892cee6691ace.png'),
('households', '0'),
('instagram_url', ''),
('letter_address', 'Kel. Suka Mulya, Kec. Serpong, Kota Tangerang Selatan'),
('letter_district', 'Ciputat'),
('letter_environment', 'Suka Mulya IV'),
('letter_logo', '11e4f869912d7a009a8dda94307fae74.webp'),
('letter_postal', '15414'),
('letter_region', 'Tangerang Selatan'),
('letter_rt_name', 'RUKUN TETANGGA 009 / RUKUN WARGA 008'),
('letter_signature', '1f6a72d73a092d1c04aea8213df7f398.png'),
('letter_signer_name', 'rohmat'),
('letter_signer_title', 'Ketua RT 009 / RW 008'),
('letter_stamp', '7edc5e9f78b15ffc9770d8137bcfc274.png'),
('letter_village', 'Suka Mulya IV'),
('logo', 'assets/mockup/logo.png'),
('male_residents', '0'),
('map_url', ''),
('notify_email', '0'),
('ops_schema_version', 'rt16-5-platform-owner'),
('phone', ''),
('residents', '0'),
('site_name', 'RT DIGITAL WARGA NUSANTARA'),
('slogan', 'Satu Warga, Satu Keluarga'),
('subscription_demo_offers_v1', '{\"basic\":1,\"pro\":2,\"enterprise\":3}'),
('tagline', 'Website lingkungan RT Digital Warga Nusantara'),
('theme_accent', '#f0501a'),
('theme_navy', '#082654'),
('ui_active_version', '4'),
('value1_icon', 'assets/mockup/icon-rukun.png'),
('value1_text', 'Menjaga silaturahmi'),
('value1_title', 'Rukun'),
('value2_icon', 'assets/mockup/icon-bersih.png'),
('value2_text', 'Lingkungan yang sehat'),
('value2_title', 'Bersih'),
('value3_icon', 'assets/mockup/icon-aman.png'),
('value3_text', 'Nyaman untuk semua warga'),
('value3_title', 'Aman'),
('whatsapp', ''),
('youtube_url', '');

-- --------------------------------------------------------

--
-- Table structure for table `settings_history`
--

CREATE TABLE `settings_history` (
  `id` int(10) UNSIGNED NOT NULL,
  `snapshot_json` mediumtext NOT NULL,
  `note` varchar(255) NOT NULL DEFAULT '',
  `changed_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `changed_by_name` varchar(80) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings_history`
--

INSERT INTO `settings_history` (`id`, `snapshot_json`, `note`, `changed_by`, `created_at`, `changed_by_name`) VALUES
(1, '{\"site_name\":\"RT 009\\/008 Suka Mulya IV\",\"tagline\":\"Website lingkungan RT 009\\/008 Suka Mulya IV\",\"theme_accent\":\"\",\"theme_navy\":\"\",\"complaint_anonymous\":\"\",\"notify_email\":\"\"}', 'Perubahan: site_name, tagline, theme_accent, theme_navy, complaint_anonymous, notify_email', 1, '2026-11-07 21:30:52', NULL),
(2, '{\"home_banner_jpg\":\"\",\"footer_banner_jpg\":\"\"}', 'Perubahan: home_banner_jpg, footer_banner_jpg', 1, '2026-11-07 22:44:31', NULL),
(3, '{\"footer_banner_jpg\":\"uploads\\/8593ddff16d872afa2b69354bfc1f2f7.png\"}', 'Perubahan: footer_banner_jpg', 1, '2026-11-07 23:01:22', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `stock_movements`
--

CREATE TABLE `stock_movements` (
  `id` int(10) UNSIGNED NOT NULL,
  `asset_id` int(10) UNSIGNED NOT NULL,
  `movement` varchar(20) NOT NULL,
  `qty` int(11) NOT NULL,
  `ref_type` varchar(30) NOT NULL DEFAULT '',
  `ref_id` int(10) UNSIGNED DEFAULT NULL,
  `note` varchar(255) NOT NULL DEFAULT '',
  `actor_id` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subscription_audit_logs`
--

CREATE TABLE `subscription_audit_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `actor_id` int(10) UNSIGNED NOT NULL,
  `tenant_id` int(10) UNSIGNED DEFAULT NULL,
  `action` varchar(60) NOT NULL,
  `before_json` mediumtext DEFAULT NULL,
  `after_json` mediumtext DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subscription_audit_logs`
--

INSERT INTO `subscription_audit_logs` (`id`, `actor_id`, `tenant_id`, `action`, `before_json`, `after_json`, `created_at`) VALUES
(1, 0, NULL, 'demo_offers_seed', NULL, '{\"_actor_scope\":\"platform_owner\",\"demo\":true,\"plan_ids\":{\"basic\":1,\"pro\":2,\"enterprise\":3},\"note\":\"Demo package offers only; tenant licences, catalog classifications and data unchanged.\"}', '2026-11-08 11:07:02'),
(2, 0, NULL, 'plan_toggle', '{\"id\":\"3\",\"name\":\"Enterprise\",\"price\":\"199000.00\",\"duration_days\":\"365\",\"is_active\":\"1\"}', '{\"is_active\":0,\"_actor_scope\":\"platform_owner\"}', '2026-11-08 11:16:33'),
(3, 0, 1, 'catalog', 'null', '{\"feature_code\":\"menu:admin\\/citizens\",\"commercial_type\":\"PREMIUM\",\"description\":\"\",\"_actor_scope\":\"platform_owner\"}', '2026-11-08 11:44:43'),
(4, 0, 1, 'catalog', '{\"feature_code\":\"menu:admin\\/citizens\",\"commercial_type\":\"PREMIUM\",\"description\":\"\"}', '{\"feature_code\":\"menu:admin\\/citizens\",\"commercial_type\":\"FREE\",\"description\":\"\",\"_actor_scope\":\"platform_owner\"}', '2026-11-08 11:44:46'),
(5, 0, 1, 'catalog', '{\"feature_code\":\"menu:admin\\/citizens\",\"commercial_type\":\"FREE\",\"description\":\"\"}', '{\"feature_code\":\"menu:admin\\/citizens\",\"commercial_type\":\"PREMIUM\",\"description\":\"\",\"_actor_scope\":\"platform_owner\"}', '2026-11-08 11:47:58'),
(6, 0, 1, 'catalog', '{\"feature_code\":\"menu:admin\\/citizens\",\"commercial_type\":\"PREMIUM\",\"description\":\"\"}', '{\"feature_code\":\"menu:admin\\/citizens\",\"commercial_type\":\"FREE\",\"description\":\"\",\"_actor_scope\":\"platform_owner\"}', '2026-11-08 12:00:46'),
(7, 0, 1, 'catalog', 'null', '{\"feature_code\":\"menu:admin\\/pengaturan\\/riwayat\",\"commercial_type\":\"PREMIUM\",\"description\":\"\",\"_actor_scope\":\"platform_owner\"}', '2026-11-08 12:01:04'),
(8, 0, 1, 'catalog', 'null', '{\"feature_code\":\"menu:admin\\/backup\",\"commercial_type\":\"PREMIUM\",\"description\":\"\",\"_actor_scope\":\"platform_owner\"}', '2026-11-08 12:01:06'),
(9, 0, 1, 'catalog', 'null', '{\"feature_code\":\"menu:admin\\/audit\",\"commercial_type\":\"PREMIUM\",\"description\":\"\",\"_actor_scope\":\"platform_owner\"}', '2026-11-08 12:01:08'),
(10, 0, 1, 'catalog', 'null', '{\"feature_code\":\"menu:admin\\/antrean-email\",\"commercial_type\":\"PREMIUM\",\"description\":\"\",\"_actor_scope\":\"platform_owner\"}', '2026-11-08 12:01:13'),
(11, 0, 1, 'catalog', 'null', '{\"feature_code\":\"menu:admin\\/field-tambahan\",\"commercial_type\":\"PREMIUM\",\"description\":\"\",\"_actor_scope\":\"platform_owner\"}', '2026-11-08 12:01:17'),
(12, 0, 1, 'catalog', 'null', '{\"feature_code\":\"menu:admin\\/quick_links\",\"commercial_type\":\"PREMIUM\",\"description\":\"\",\"_actor_scope\":\"platform_owner\"}', '2026-11-08 12:01:22');

-- --------------------------------------------------------

--
-- Table structure for table `subscription_catalog`
--

CREATE TABLE `subscription_catalog` (
  `feature_code` varchar(80) NOT NULL,
  `commercial_type` enum('FREE','PREMIUM','DISABLED') NOT NULL DEFAULT 'FREE',
  `description` varchar(500) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subscription_catalog`
--

INSERT INTO `subscription_catalog` (`feature_code`, `commercial_type`, `description`) VALUES
('menu:admin/antrean-email', 'PREMIUM', ''),
('menu:admin/audit', 'PREMIUM', ''),
('menu:admin/backup', 'PREMIUM', ''),
('menu:admin/citizens', 'FREE', ''),
('menu:admin/field-tambahan', 'PREMIUM', ''),
('menu:admin/pengaturan/riwayat', 'PREMIUM', ''),
('menu:admin/quick_links', 'PREMIUM', '');

-- --------------------------------------------------------

--
-- Table structure for table `subscription_overrides`
--

CREATE TABLE `subscription_overrides` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` int(10) UNSIGNED NOT NULL,
  `feature_code` varchar(80) NOT NULL,
  `access_mode` enum('GRANT','DENY') NOT NULL,
  `starts_at` datetime NOT NULL,
  `ends_at` datetime NOT NULL,
  `reason` varchar(500) NOT NULL,
  `created_by` int(10) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subscription_plans`
--

CREATE TABLE `subscription_plans` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(120) NOT NULL,
  `price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `duration_days` int(10) UNSIGNED NOT NULL,
  `is_active` tinyint(4) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subscription_plans`
--

INSERT INTO `subscription_plans` (`id`, `name`, `price`, `duration_days`, `is_active`) VALUES
(1, 'Gratis (Basic)', 0.00, 0, 1),
(2, 'Pro', 99000.00, 365, 1),
(3, 'Enterprise', 199000.00, 365, 0);

-- --------------------------------------------------------

--
-- Table structure for table `subscription_plan_details`
--

CREATE TABLE `subscription_plan_details` (
  `plan_id` int(10) UNSIGNED NOT NULL,
  `description` varchar(300) NOT NULL DEFAULT '',
  `benefits_json` text NOT NULL,
  `theme` varchar(12) NOT NULL DEFAULT 'orange',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `version` int(10) UNSIGNED NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subscription_plan_details`
--

INSERT INTO `subscription_plan_details` (`plan_id`, `description`, `benefits_json`, `theme`, `sort_order`, `version`) VALUES
(1, 'Fitur dasar portal warga. Data contoh paket; akses mengikuti pengaturan dan hak pengguna.', '[\"Dashboard\",\"Data Warga\"]', 'green', 0, 1),
(2, 'Semua fitur Basic, ditambah:', '[\"Laporan & Ekspor sesuai hak akses\"]', 'orange', 1, 1),
(3, 'Semua fitur Pro, ditambah fitur operasional lingkungan:', '[\"Manajemen Pengurus sesuai hak akses\",\"Audit Aktivitas\",\"Backup Data manual\",\"Prioritas Dukungan — contoh manfaat paket\"]', 'violet', 2, 1);

-- --------------------------------------------------------

--
-- Table structure for table `subscription_plan_features`
--

CREATE TABLE `subscription_plan_features` (
  `plan_id` int(10) UNSIGNED NOT NULL,
  `feature_code` varchar(80) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subscription_plan_features`
--

INSERT INTO `subscription_plan_features` (`plan_id`, `feature_code`) VALUES
(1, 'announcements'),
(1, 'events'),
(1, 'letters'),
(2, 'announcements'),
(2, 'cash_report'),
(2, 'events'),
(2, 'family_card'),
(2, 'letters'),
(2, 'resident_mutation'),
(2, 'resident_profile'),
(2, 'social_aid'),
(3, 'activity_notes'),
(3, 'announcements'),
(3, 'cash_reconciliation'),
(3, 'cash_report'),
(3, 'complaints'),
(3, 'contacts'),
(3, 'documents'),
(3, 'dues'),
(3, 'dues_arrears'),
(3, 'events'),
(3, 'family_card'),
(3, 'gallery'),
(3, 'inventory'),
(3, 'letters'),
(3, 'live_chat'),
(3, 'patrol'),
(3, 'purchasing'),
(3, 'resident_mutation'),
(3, 'resident_profile'),
(3, 'social_aid'),
(3, 'work_program');

-- --------------------------------------------------------

--
-- Table structure for table `tenant_features`
--

CREATE TABLE `tenant_features` (
  `tenant_id` int(10) UNSIGNED NOT NULL,
  `feature_id` int(10) UNSIGNED NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT 0,
  `visible_to_residents` tinyint(1) NOT NULL DEFAULT 1,
  `visible_to_staff` tinyint(1) NOT NULL DEFAULT 1,
  `updated_by` int(10) UNSIGNED DEFAULT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tenant_features`
--

INSERT INTO `tenant_features` (`tenant_id`, `feature_id`, `enabled`, `visible_to_residents`, `visible_to_staff`, `updated_by`, `updated_at`) VALUES
(1, 1, 1, 1, 1, NULL, '2026-11-07 22:18:48'),
(1, 2, 1, 0, 1, NULL, '2026-11-07 22:18:48'),
(1, 3, 1, 1, 1, NULL, '2026-11-07 22:18:48'),
(1, 4, 1, 0, 1, NULL, '2026-11-07 22:18:48'),
(1, 5, 1, 1, 1, NULL, '2026-11-07 22:18:48'),
(1, 6, 1, 1, 1, NULL, '2026-11-07 22:18:48'),
(1, 7, 1, 1, 1, NULL, '2026-11-07 22:18:48'),
(1, 8, 1, 1, 1, NULL, '2026-11-07 22:18:48'),
(1, 9, 1, 0, 1, NULL, '2026-11-07 22:18:48'),
(1, 10, 1, 1, 1, NULL, '2026-11-07 22:18:48'),
(1, 11, 1, 0, 1, NULL, '2026-11-07 22:18:48'),
(1, 12, 1, 1, 1, NULL, '2026-11-07 22:18:48'),
(1, 13, 1, 1, 1, NULL, '2026-11-07 22:18:48'),
(1, 14, 1, 1, 1, NULL, '2026-11-07 22:18:48'),
(1, 15, 1, 1, 1, NULL, '2026-11-07 22:18:48'),
(1, 16, 1, 1, 1, NULL, '2026-11-07 22:18:48'),
(1, 17, 1, 0, 1, NULL, '2026-11-07 22:18:48'),
(1, 18, 1, 0, 1, NULL, '2026-11-07 22:18:48'),
(1, 19, 1, 0, 1, NULL, '2026-11-07 22:18:48'),
(1, 20, 1, 0, 1, NULL, '2026-11-07 22:18:48'),
(1, 21, 1, 0, 1, NULL, '2026-11-07 22:18:48');

-- --------------------------------------------------------

--
-- Table structure for table `tenant_feature_audit`
--

CREATE TABLE `tenant_feature_audit` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` int(10) UNSIGNED NOT NULL,
  `feature_id` int(10) UNSIGNED NOT NULL,
  `actor_user_id` int(10) UNSIGNED NOT NULL,
  `old_value` longtext DEFAULT NULL,
  `new_value` longtext NOT NULL,
  `reason` varchar(500) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tenant_subscriptions`
--

CREATE TABLE `tenant_subscriptions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` int(10) UNSIGNED NOT NULL,
  `plan_id` int(10) UNSIGNED NOT NULL,
  `status` enum('ACTIVE','TRIAL','EXPIRED','SUSPENDED') NOT NULL,
  `starts_at` datetime NOT NULL,
  `ends_at` datetime NOT NULL,
  `reason` varchar(500) NOT NULL,
  `created_by` int(10) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(10) UNSIGNED NOT NULL,
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
  `created_by` int(10) UNSIGNED NOT NULL,
  `verified_by` int(10) UNSIGNED DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `reverses_id` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `tx_date`, `type`, `category`, `description`, `amount`, `period`, `counterparty`, `proof`, `status`, `review_note`, `created_by`, `verified_by`, `verified_at`, `reverses_id`, `created_at`) VALUES
(2, '2026-10-17', 'masuk', 'Iuran lingkungan', 'Iuran rutin Iuran lingkungan 2026-10', 10000.00, '2026-10', '', '', 'verified', NULL, 1, 1, '2026-10-17 15:29:09', NULL, '2026-10-17 20:29:09'),
(3, '2026-10-31', 'keluar', 'Kebersihan', 'sampah', 10000.00, '2026-10', '', '19966c24877af515ab70a0f1051e0c1d.png', 'verified', NULL, 1, 1, '2026-10-31 15:01:41', NULL, '2026-10-31 15:01:41'),
(4, '2026-10-31', 'masuk', 'Iuran keamanan', 'fdfd', 1.00, '2026-10', '', '', 'verified', NULL, 1, 1, '2026-10-31 09:06:42', NULL, '2026-10-31 15:06:42');

-- --------------------------------------------------------

--
-- Table structure for table `ui_config_drafts`
--

CREATE TABLE `ui_config_drafts` (
  `owner_key` varchar(100) NOT NULL,
  `config_json` mediumtext NOT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ui_config_versions`
--

CREATE TABLE `ui_config_versions` (
  `id` int(10) UNSIGNED NOT NULL,
  `config_json` mediumtext NOT NULL,
  `action` varchar(30) NOT NULL DEFAULT 'apply',
  `note` varchar(255) NOT NULL DEFAULT '',
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_by_name` varchar(80) NOT NULL DEFAULT '',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ui_config_versions`
--

INSERT INTO `ui_config_versions` (`id`, `config_json`, `action`, `note`, `created_by`, `created_by_name`, `created_at`) VALUES
(1, '{\"schemaVersion\":1,\"themeId\":\"navy\",\"sidebar\":{\"variant\":\"standard\"},\"components\":{\"radius\":\"medium\",\"fontSize\":\"medium\",\"density\":\"comfortable\",\"inputHeight\":\"standard\"},\"header\":{\"search\":true,\"notifications\":true,\"banner\":true},\"dashboard\":{\"order\":[\"hero\",\"stats\",\"stats_chart\",\"dues\",\"agenda\",\"letters\",\"operations\"],\"hidden\":[],\"stats_hidden\":[]},\"colors\":{\"primary\":\"#172554\",\"secondary\":\"#334155\",\"accent\":\"#3B82F6\",\"background\":\"#0B1220\",\"surface\":\"#111C33\",\"text\":\"#F8FAFC\"},\"brand\":{\"logo\":\"\"}}', 'apply', 'Tema Dark Navy', NULL, 'Admin Pusat (superadmin)', '2026-11-08 14:33:59'),
(2, '{\"schemaVersion\":1,\"themeId\":\"original-orange\",\"sidebar\":{\"variant\":\"standard\"},\"components\":{\"radius\":\"medium\",\"fontSize\":\"medium\",\"density\":\"comfortable\",\"inputHeight\":\"standard\"},\"header\":{\"search\":true,\"notifications\":true,\"banner\":true},\"dashboard\":{\"order\":[\"hero\",\"stats\",\"stats_chart\",\"dues\",\"agenda\",\"letters\",\"operations\"],\"hidden\":[],\"stats_hidden\":[]},\"colors\":{\"primary\":\"#F97316\",\"secondary\":\"#FFEDD5\",\"accent\":\"#F97316\",\"background\":\"#F8FAFC\",\"surface\":\"#FFFFFF\",\"text\":\"#102A56\"},\"brand\":{\"logo\":\"\"}}', 'reset', 'Reset ke Default (Oranye Harmoni)', NULL, 'Admin Pusat (superadmin)', '2026-11-08 14:34:14'),
(3, '{\"schemaVersion\":1,\"themeId\":\"teal\",\"sidebar\":{\"variant\":\"standard\"},\"components\":{\"radius\":\"medium\",\"fontSize\":\"medium\",\"density\":\"comfortable\",\"inputHeight\":\"standard\"},\"header\":{\"search\":true,\"notifications\":true,\"banner\":true},\"dashboard\":{\"order\":[\"hero\",\"stats\",\"stats_chart\",\"dues\",\"agenda\",\"letters\",\"operations\"],\"hidden\":[],\"stats_hidden\":[]},\"colors\":{\"primary\":\"#0D9488\",\"secondary\":\"#CCFBF1\",\"accent\":\"#0D9488\",\"background\":\"#F8FAFC\",\"surface\":\"#FFFFFF\",\"text\":\"#102A56\"},\"brand\":{\"logo\":\"\"}}', 'apply', 'Tema Teal Fresh', NULL, 'Admin Pusat (superadmin)', '2026-11-08 14:35:07'),
(4, '{\"schemaVersion\":1,\"themeId\":\"original-orange\",\"sidebar\":{\"variant\":\"standard\"},\"components\":{\"radius\":\"medium\",\"fontSize\":\"medium\",\"density\":\"comfortable\",\"inputHeight\":\"standard\"},\"header\":{\"search\":true,\"notifications\":true,\"banner\":true},\"dashboard\":{\"order\":[\"hero\",\"stats\",\"stats_chart\",\"dues\",\"agenda\",\"letters\",\"operations\"],\"hidden\":[],\"stats_hidden\":[]},\"colors\":{\"primary\":\"#F97316\",\"secondary\":\"#FFEDD5\",\"accent\":\"#F97316\",\"background\":\"#F8FAFC\",\"surface\":\"#FFFFFF\",\"text\":\"#102A56\"},\"brand\":{\"logo\":\"\"}}', 'reset', 'Reset ke Default (Oranye Harmoni)', NULL, 'Admin Pusat (superadmin)', '2026-11-08 14:35:34');

-- --------------------------------------------------------

--
-- Table structure for table `work_programs`
--

CREATE TABLE `work_programs` (
  `id` int(10) UNSIGNED NOT NULL,
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
  `progress` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `obstacles` text DEFAULT NULL,
  `evaluation` text DEFAULT NULL,
  `approved_by` int(10) UNSIGNED DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `action_items`
--
ALTER TABLE `action_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ai_note` (`note_id`),
  ADD KEY `idx_ai_status_due` (`status`,`due_date`);

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `activity_notes`
--
ALTER TABLE `activity_notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_an_agenda` (`agenda_id`),
  ADD KEY `idx_an_program` (`program_id`);

--
-- Indexes for table `activity_note_versions`
--
ALTER TABLE `activity_note_versions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_note_version` (`note_id`,`version`);

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `aid_programs`
--
ALTER TABLE `aid_programs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `aid_recipients`
--
ALTER TABLE `aid_recipients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_aid_recipient` (`program_id`,`recipient_key`),
  ADD KEY `idx_aid_status` (`status`);

--
-- Indexes for table `app_features`
--
ALTER TABLE `app_features`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_feature_code` (`code`),
  ADD KEY `idx_feature_parent` (`parent_id`);

--
-- Indexes for table `assets`
--
ALTER TABLE `assets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_asset_code` (`code`),
  ADD KEY `idx_asset_status` (`status`);

--
-- Indexes for table `asset_loans`
--
ALTER TABLE `asset_loans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_loan_asset` (`asset_id`),
  ADD KEY `idx_loan_status` (`status`);

--
-- Indexes for table `backup_runs`
--
ALTER TABLE `backup_runs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_backup_created` (`created_at`);

--
-- Indexes for table `billing_campaigns`
--
ALTER TABLE `billing_campaigns`
  ADD PRIMARY KEY (`id`),
  ADD KEY `period_type` (`period`,`due_type`);

--
-- Indexes for table `cash_reconciliations`
--
ALTER TABLE `cash_reconciliations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `chat_conversations`
--
ALTER TABLE `chat_conversations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `citizen_id` (`citizen_id`),
  ADD KEY `assigned_admin_id` (`assigned_admin_id`),
  ADD KEY `last_message_at` (`last_message_at`);

--
-- Indexes for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `conversation_messages` (`conversation_id`,`id`),
  ADD KEY `unread_lookup` (`conversation_id`,`sender_type`,`is_read`);

--
-- Indexes for table `chat_presence`
--
ALTER TABLE `chat_presence`
  ADD PRIMARY KEY (`user_type`,`user_id`),
  ADD KEY `last_seen` (`last_seen`);

--
-- Indexes for table `citizens`
--
ALTER TABLE `citizens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `citizen_field_values`
--
ALTER TABLE `citizen_field_values`
  ADD PRIMARY KEY (`citizen_id`,`field_id`),
  ADD KEY `idx_cfv_field` (`field_id`);

--
-- Indexes for table `citizen_notice_reads`
--
ALTER TABLE `citizen_notice_reads`
  ADD PRIMARY KEY (`citizen_id`,`kind`,`ref_id`,`status`);

--
-- Indexes for table `complaints`
--
ALTER TABLE `complaints`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_ticket` (`ticket_no`),
  ADD KEY `idx_cmp_status` (`status`),
  ADD KEY `idx_cmp_citizen` (`citizen_id`),
  ADD KEY `idx_cmp_assigned` (`assigned_to`);

--
-- Indexes for table `complaint_updates`
--
ALTER TABLE `complaint_updates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_cu_complaint` (`complaint_id`);

--
-- Indexes for table `custom_field_definitions`
--
ALTER TABLE `custom_field_definitions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `custom_roles`
--
ALTER TABLE `custom_roles`
  ADD PRIMARY KEY (`role_key`),
  ADD KEY `idx_custom_roles_tenant` (`tenant_id`);

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_doc_cat` (`category`),
  ADD KEY `idx_doc_deleted` (`deleted_at`);

--
-- Indexes for table `document_versions`
--
ALTER TABLE `document_versions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_doc_version` (`document_id`,`version`);

--
-- Indexes for table `dues`
--
ALTER TABLE `dues`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `citizen_period_category` (`citizen_id`,`period`,`category`);

--
-- Indexes for table `due_payments`
--
ALTER TABLE `due_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `due_status` (`due_id`,`status`),
  ADD KEY `citizen_id` (`citizen_id`);

--
-- Indexes for table `due_reminders`
--
ALTER TABLE `due_reminders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_rem_citizen` (`citizen_id`,`created_at`);

--
-- Indexes for table `goods_receipts`
--
ALTER TABLE `goods_receipts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_gr_purchase` (`purchase_id`);

--
-- Indexes for table `handover_sessions`
--
ALTER TABLE `handover_sessions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `handover_signoffs`
--
ALTER TABLE `handover_signoffs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_signoff` (`session_id`,`admin_id`);

--
-- Indexes for table `important_contacts`
--
ALTER TABLE `important_contacts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_contact_public` (`is_public`,`is_active`);

--
-- Indexes for table `incident_reports`
--
ALTER TABLE `incident_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ir_date` (`incident_at`);

--
-- Indexes for table `kk_families`
--
ALTER TABLE `kk_families`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nomor_kk` (`nomor_kk`),
  ADD UNIQUE KEY `owner_citizen_id` (`owner_citizen_id`);

--
-- Indexes for table `kk_members`
--
ALTER TABLE `kk_members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nik` (`nik`),
  ADD KEY `family_id` (`family_id`);

--
-- Indexes for table `kk_scan_requests`
--
ALTER TABLE `kk_scan_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `citizen_id` (`citizen_id`,`status`);

--
-- Indexes for table `letter_types`
--
ALTER TABLE `letter_types`
  ADD PRIMARY KEY (`type_key`);

--
-- Indexes for table `notification_outbox`
--
ALTER TABLE `notification_outbox`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_outbox_dedupe` (`dedupe_key`),
  ADD KEY `idx_outbox_status` (`status`,`next_attempt_at`);

--
-- Indexes for table `officials`
--
ALTER TABLE `officials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_official_parent` (`parent_id`);

--
-- Indexes for table `pages`
--
ALTER TABLE `pages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `patrol_schedules`
--
ALTER TABLE `patrol_schedules`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_patrol_officer` (`patrol_date`,`shift`,`officer_name`),
  ADD KEY `idx_patrol_citizen` (`citizen_id`);

--
-- Indexes for table `platform_admins`
--
ALTER TABLE `platform_admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `platform_login_attempts`
--
ALTER TABLE `platform_login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_attempt` (`ip_hash`,`created_at`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `type_status_date` (`type`,`status`,`event_date`);

--
-- Indexes for table `program_agendas`
--
ALTER TABLE `program_agendas`
  ADD PRIMARY KEY (`program_id`,`post_id`);

--
-- Indexes for table `program_milestones`
--
ALTER TABLE `program_milestones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pm_program` (`program_id`);

--
-- Indexes for table `purchase_items`
--
ALTER TABLE `purchase_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pi_purchase` (`purchase_id`);

--
-- Indexes for table `purchase_requests`
--
ALTER TABLE `purchase_requests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_purchase_tx` (`transaction_id`),
  ADD KEY `idx_pr_status` (`status`);

--
-- Indexes for table `quick_links`
--
ALTER TABLE `quick_links`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `resident_mutations`
--
ALTER TABLE `resident_mutations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_mut_status` (`status`),
  ADD KEY `idx_mut_family` (`family_id`),
  ADD KEY `idx_mut_citizen` (`citizen_id`);

--
-- Indexes for table `role_menu_access`
--
ALTER TABLE `role_menu_access`
  ADD PRIMARY KEY (`tenant_id`,`role`,`menu_path`),
  ADD KEY `idx_role_menu_role` (`tenant_id`,`role`);

--
-- Indexes for table `service_requests`
--
ALTER TABLE `service_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `citizen_status` (`citizen_id`,`status`);

--
-- Indexes for table `service_request_details`
--
ALTER TABLE `service_request_details`
  ADD PRIMARY KEY (`request_id`);

--
-- Indexes for table `service_request_documents`
--
ALTER TABLE `service_request_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `request_id` (`request_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`setting_key`);

--
-- Indexes for table `settings_history`
--
ALTER TABLE `settings_history`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sm_asset` (`asset_id`);

--
-- Indexes for table `subscription_audit_logs`
--
ALTER TABLE `subscription_audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_audit_tenant` (`tenant_id`,`created_at`);

--
-- Indexes for table `subscription_catalog`
--
ALTER TABLE `subscription_catalog`
  ADD PRIMARY KEY (`feature_code`);

--
-- Indexes for table `subscription_overrides`
--
ALTER TABLE `subscription_overrides`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_override` (`tenant_id`,`feature_code`,`starts_at`,`ends_at`);

--
-- Indexes for table `subscription_plans`
--
ALTER TABLE `subscription_plans`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `subscription_plan_details`
--
ALTER TABLE `subscription_plan_details`
  ADD PRIMARY KEY (`plan_id`);

--
-- Indexes for table `subscription_plan_features`
--
ALTER TABLE `subscription_plan_features`
  ADD PRIMARY KEY (`plan_id`,`feature_code`);

--
-- Indexes for table `tenant_features`
--
ALTER TABLE `tenant_features`
  ADD PRIMARY KEY (`tenant_id`,`feature_id`),
  ADD KEY `idx_tf_feature` (`feature_id`);

--
-- Indexes for table `tenant_feature_audit`
--
ALTER TABLE `tenant_feature_audit`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tenant_time` (`tenant_id`,`created_at`),
  ADD KEY `idx_tfa_feature` (`feature_id`);

--
-- Indexes for table `tenant_subscriptions`
--
ALTER TABLE `tenant_subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tenant` (`tenant_id`,`id`),
  ADD KEY `plan_id` (`plan_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `period_status` (`period`,`status`);

--
-- Indexes for table `ui_config_drafts`
--
ALTER TABLE `ui_config_drafts`
  ADD PRIMARY KEY (`owner_key`);

--
-- Indexes for table `ui_config_versions`
--
ALTER TABLE `ui_config_versions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `work_programs`
--
ALTER TABLE `work_programs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_wp_status` (`status`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `action_items`
--
ALTER TABLE `action_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=90;

--
-- AUTO_INCREMENT for table `activity_notes`
--
ALTER TABLE `activity_notes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `activity_note_versions`
--
ALTER TABLE `activity_note_versions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=80;

--
-- AUTO_INCREMENT for table `aid_programs`
--
ALTER TABLE `aid_programs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `aid_recipients`
--
ALTER TABLE `aid_recipients`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `app_features`
--
ALTER TABLE `app_features`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `assets`
--
ALTER TABLE `assets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `asset_loans`
--
ALTER TABLE `asset_loans`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `backup_runs`
--
ALTER TABLE `backup_runs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `billing_campaigns`
--
ALTER TABLE `billing_campaigns`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cash_reconciliations`
--
ALTER TABLE `cash_reconciliations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chat_conversations`
--
ALTER TABLE `chat_conversations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `citizens`
--
ALTER TABLE `citizens`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=129;

--
-- AUTO_INCREMENT for table `complaints`
--
ALTER TABLE `complaints`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `complaint_updates`
--
ALTER TABLE `complaint_updates`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `custom_field_definitions`
--
ALTER TABLE `custom_field_definitions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `document_versions`
--
ALTER TABLE `document_versions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `dues`
--
ALTER TABLE `dues`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `due_payments`
--
ALTER TABLE `due_payments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `due_reminders`
--
ALTER TABLE `due_reminders`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `goods_receipts`
--
ALTER TABLE `goods_receipts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `handover_sessions`
--
ALTER TABLE `handover_sessions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `handover_signoffs`
--
ALTER TABLE `handover_signoffs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `important_contacts`
--
ALTER TABLE `important_contacts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `incident_reports`
--
ALTER TABLE `incident_reports`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kk_families`
--
ALTER TABLE `kk_families`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `kk_members`
--
ALTER TABLE `kk_members`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `kk_scan_requests`
--
ALTER TABLE `kk_scan_requests`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `notification_outbox`
--
ALTER TABLE `notification_outbox`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `officials`
--
ALTER TABLE `officials`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `pages`
--
ALTER TABLE `pages`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `patrol_schedules`
--
ALTER TABLE `patrol_schedules`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `platform_admins`
--
ALTER TABLE `platform_admins`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `platform_login_attempts`
--
ALTER TABLE `platform_login_attempts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `program_milestones`
--
ALTER TABLE `program_milestones`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchase_items`
--
ALTER TABLE `purchase_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchase_requests`
--
ALTER TABLE `purchase_requests`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quick_links`
--
ALTER TABLE `quick_links`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `resident_mutations`
--
ALTER TABLE `resident_mutations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `service_requests`
--
ALTER TABLE `service_requests`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT for table `service_request_documents`
--
ALTER TABLE `service_request_documents`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `settings_history`
--
ALTER TABLE `settings_history`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `stock_movements`
--
ALTER TABLE `stock_movements`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subscription_audit_logs`
--
ALTER TABLE `subscription_audit_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `subscription_overrides`
--
ALTER TABLE `subscription_overrides`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subscription_plans`
--
ALTER TABLE `subscription_plans`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tenant_feature_audit`
--
ALTER TABLE `tenant_feature_audit`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `tenant_subscriptions`
--
ALTER TABLE `tenant_subscriptions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `ui_config_versions`
--
ALTER TABLE `ui_config_versions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `work_programs`
--
ALTER TABLE `work_programs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `app_features`
--
ALTER TABLE `app_features`
  ADD CONSTRAINT `fk_feature_parent` FOREIGN KEY (`parent_id`) REFERENCES `app_features` (`id`);

--
-- Constraints for table `kk_members`
--
ALTER TABLE `kk_members`
  ADD CONSTRAINT `kk_members_ibfk_1` FOREIGN KEY (`family_id`) REFERENCES `kk_families` (`id`);

--
-- Constraints for table `subscription_plan_details`
--
ALTER TABLE `subscription_plan_details`
  ADD CONSTRAINT `subscription_plan_details_ibfk_1` FOREIGN KEY (`plan_id`) REFERENCES `subscription_plans` (`id`);

--
-- Constraints for table `subscription_plan_features`
--
ALTER TABLE `subscription_plan_features`
  ADD CONSTRAINT `subscription_plan_features_ibfk_1` FOREIGN KEY (`plan_id`) REFERENCES `subscription_plans` (`id`);

--
-- Constraints for table `tenant_features`
--
ALTER TABLE `tenant_features`
  ADD CONSTRAINT `fk_tenant_feature` FOREIGN KEY (`feature_id`) REFERENCES `app_features` (`id`);

--
-- Constraints for table `tenant_subscriptions`
--
ALTER TABLE `tenant_subscriptions`
  ADD CONSTRAINT `tenant_subscriptions_ibfk_1` FOREIGN KEY (`plan_id`) REFERENCES `subscription_plans` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
