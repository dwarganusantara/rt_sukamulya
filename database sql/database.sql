-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: sukamulya
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `sukamulya`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `sukamulya` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;

USE `sukamulya`;

--
-- Table structure for table `activity_logs`
--

DROP TABLE IF EXISTS `activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activity_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `admin_id` int(10) unsigned NOT NULL,
  `action` varchar(80) NOT NULL,
  `subject` varchar(100) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_logs`
--

LOCK TABLES `activity_logs` WRITE;
/*!40000 ALTER TABLE `activity_logs` DISABLE KEYS */;
INSERT INTO `activity_logs` VALUES (5,1,'update','quick_link:7','2026-10-17 12:58:08'),(6,1,'update','quick_link:7','2026-10-17 12:58:08'),(7,1,'create','galeri:1','2026-10-17 13:07:30'),(8,1,'create','citizen:2','2026-10-17 13:29:51'),(9,7,'review','request:2','2026-10-17 16:38:50'),(10,1,'update','letter_settings','2026-10-17 16:52:14'),(11,1,'update','letter_settings','2026-10-17 16:52:40'),(12,1,'update','letter_settings','2026-10-17 16:54:01'),(13,1,'update','letter_settings','2026-10-17 16:55:59'),(14,1,'update','letter_settings','2026-10-17 16:59:21'),(15,1,'update','letter_settings','2026-10-17 17:03:28'),(16,1,'update','letter_settings','2026-10-17 17:03:32'),(17,1,'update','letter_settings','2026-10-17 17:04:00'),(18,1,'create','pengumuman:2','2026-10-17 17:21:08'),(19,1,'review','request:41','2026-10-17 19:32:29'),(20,1,'update','letter_settings','2026-10-17 19:45:42'),(21,1,'update','letter_settings','2026-10-17 19:53:13'),(22,1,'create','due:2','2026-10-17 19:59:53'),(23,1,'update','letter_settings','2026-10-17 20:04:59'),(24,1,'verified','due_payment:1','2026-10-17 20:29:09'),(25,1,'review','request:54','2026-10-18 09:41:58');
/*!40000 ALTER TABLE `activity_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admins` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(80) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','operator','bendahara','ketua') NOT NULL DEFAULT 'admin',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admins`
--

LOCK TABLES `admins` WRITE;
/*!40000 ALTER TABLE `admins` DISABLE KEYS */;
INSERT INTO `admins` VALUES (1,'admin','$2y$10$2DhqArcPy1qvhK4Gy9kOPecrGwWUKCoyAGBlAgPPoB86CThNLPY92','admin');
/*!40000 ALTER TABLE `admins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `billing_campaigns`
--

DROP TABLE IF EXISTS `billing_campaigns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `billing_campaigns` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(140) NOT NULL,
  `due_type` enum('rutin','non_rutin','sosial') NOT NULL,
  `category` varchar(100) NOT NULL,
  `period` char(7) NOT NULL,
  `amount` decimal(14,2) NOT NULL,
  `note` varchar(255) NOT NULL DEFAULT '',
  `created_by` int(10) unsigned NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `period_type` (`period`,`due_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `billing_campaigns`
--

LOCK TABLES `billing_campaigns` WRITE;
/*!40000 ALTER TABLE `billing_campaigns` DISABLE KEYS */;
/*!40000 ALTER TABLE `billing_campaigns` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `citizens`
--

DROP TABLE IF EXISTS `citizens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `citizens` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(80) NOT NULL,
  `display_name` varchar(140) NOT NULL,
  `full_name` varchar(140) DEFAULT NULL,
  `gender` varchar(10) NOT NULL DEFAULT 'female',
  `address` varchar(255) DEFAULT NULL,
  `phone` varchar(25) DEFAULT NULL,
  `email` varchar(190) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `citizens`
--

LOCK TABLES `citizens` WRITE;
/*!40000 ALTER TABLE `citizens` DISABLE KEYS */;
INSERT INTO `citizens` (`id`,`username`,`display_name`,`full_name`,`gender`,`address`,`phone`,`email`,`notes`,`password_hash`,`is_active`,`created_at`) VALUES (2,'chendy','Chendy','Chendy','male',NULL,NULL,NULL,NULL,'$2y$10$mhH3DHgOTZdPv.jYmhPxK.JoIsxjmsIySP5cBDabN.QGA93kOI3Ne',1,'2026-10-17 13:29:51');
/*!40000 ALTER TABLE `citizens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `due_payments`
--

DROP TABLE IF EXISTS `due_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `due_payments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `due_id` int(10) unsigned NOT NULL,
  `citizen_id` int(10) unsigned NOT NULL,
  `amount` decimal(14,2) NOT NULL,
  `method` enum('transfer','tunai') NOT NULL,
  `proof` varchar(255) DEFAULT NULL,
  `status` enum('pending','verified','returned') NOT NULL DEFAULT 'pending',
  `review_note` varchar(255) NOT NULL DEFAULT '',
  `transaction_id` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `reviewed_at` datetime DEFAULT NULL,
  `reviewed_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `due_status` (`due_id`,`status`),
  KEY `citizen_id` (`citizen_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `due_payments`
--

LOCK TABLES `due_payments` WRITE;
/*!40000 ALTER TABLE `due_payments` DISABLE KEYS */;
INSERT INTO `due_payments` VALUES (1,2,2,10000.00,'tunai',NULL,'verified','',2,'2026-10-17 20:28:48','2026-10-17 15:29:09',1);
/*!40000 ALTER TABLE `due_payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dues`
--

DROP TABLE IF EXISTS `dues`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dues` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `citizen_id` int(10) unsigned NOT NULL,
  `period` char(7) NOT NULL,
  `category` varchar(100) NOT NULL,
  `due_type` enum('rutin','non_rutin','sosial') NOT NULL DEFAULT 'rutin',
  `campaign_id` int(10) unsigned DEFAULT NULL,
  `amount_due` decimal(14,2) NOT NULL,
  `amount_paid` decimal(14,2) NOT NULL DEFAULT 0.00,
  `note` varchar(255) NOT NULL DEFAULT '',
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `citizen_period_category` (`citizen_id`,`period`,`category`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dues`
--

LOCK TABLES `dues` WRITE;
/*!40000 ALTER TABLE `dues` DISABLE KEYS */;
INSERT INTO `dues` VALUES (2,2,'2026-10','Iuran lingkungan','rutin',NULL,50000.00,50000.00,'','2026-10-17 20:29:09');
/*!40000 ALTER TABLE `dues` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `letter_types`
--

DROP TABLE IF EXISTS `letter_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `letter_types` (
  `type_key` varchar(40) NOT NULL,
  `title` varchar(160) NOT NULL,
  `category` varchar(40) NOT NULL,
  `description` varchar(255) NOT NULL,
  `body_template` text DEFAULT NULL,
  `icon_path` varchar(255) NOT NULL DEFAULT '',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`type_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `letter_types`
--

LOCK TABLES `letter_types` WRITE;
/*!40000 ALTER TABLE `letter_types` DISABLE KEYS */;
INSERT INTO `letter_types` VALUES ('aduan','Aduan / Saran','Lainnya','Sampaikan keluhan atau saran untuk pengurus RT.','Yang bertanda tangan di bawah ini:\nNama : {{nama_warga}}\nNIK : {{nik_warga}}\nAlamat : {{alamat_domisili}}\n\nMenyampaikan aduan kategori {{issue_category}} mengenai kejadian di {{incident_location}} pada {{incident_date}}. Uraian: {{keterangan}}. Tindak lanjut yang diharapkan: {{expected_followup}}.\nDemikian aduan ini disampaikan kepada pengurus RT untuk ditindaklanjuti.','',16,1),('ahli_waris','Surat Pengantar Ahli Waris','Keterangan','Pengantar proses administrasi ahli waris.','Yang bertanda tangan di bawah ini, {{jabatan_ketua_rt}} {{wilayah_rt}}, menerangkan bahwa menurut laporan warga, {{heir_deceased_name}} yang terakhir beralamat di {{heir_deceased_address}} meninggal dunia pada {{heir_death_date}}. Nama pihak yang dilaporkan sebagai ahli waris: {{heir_names}}. Hubungan para pihak: {{heir_relationships}}.\nPelapor: {{nama_warga}}, NIK {{nik_warga}}. Keterangan ini disampaikan untuk keperluan {{keperluan_surat}} dan tetap memerlukan pemeriksaan dokumen oleh instansi berwenang.\n\nDemikian surat ini dibuat berdasarkan keterangan yang disampaikan dan data yang tersedia, untuk dipergunakan sebagaimana mestinya.','',12,1),('domisili','Surat Keterangan Domisili','Kependudukan','Keterangan tempat tinggal warga RT 009/008.','Yang bertanda tangan di bawah ini, {{jabatan_ketua_rt}} {{wilayah_rt}}, menerangkan bahwa:\nNama : {{nama_warga}}\nNIK : {{nik_warga}}\nTempat, Tanggal Lahir : {{tempat_tanggal_lahir}}\nJenis Kelamin : {{jenis_kelamin}}\nPekerjaan : {{pekerjaan}}\nAlamat Domisili : {{alamat_domisili}}\n\nBerdasarkan keterangan yang tersedia pada pengurus RT, yang bersangkutan berdomisili di alamat tersebut sejak {{domicile_since}}. Status tempat tinggal: {{residence_status}}. Kepala keluarga: {{household_head}}.\nSurat keterangan domisili ini dimohon untuk {{keperluan_surat}}.\n\nDemikian surat ini dibuat berdasarkan keterangan yang disampaikan dan data yang tersedia, untuk dipergunakan sebagaimana mestinya.','',2,1),('kegiatan','Surat Pengantar Kegiatan Warga','Kegiatan','Pemberitahuan kegiatan atau acara lingkungan.','Yang bertanda tangan di bawah ini, {{jabatan_ketua_rt}} {{wilayah_rt}}, menerangkan bahwa warga merencanakan kegiatan {{event_name}} pada {{event_date}} pukul {{event_time}} di {{event_location}}. Penanggung jawab kegiatan: {{event_organizer}}. Pemohon: {{nama_warga}}, NIK {{nik_warga}}. Kegiatan ini disampaikan kepada pengurus RT untuk keperluan {{keperluan_surat}}.\n\nDemikian surat ini dibuat berdasarkan keterangan yang disampaikan dan data yang tersedia, untuk dipergunakan sebagaimana mestinya.','',14,1),('kehilangan','Surat Pengantar Kehilangan','Keterangan','Pengantar untuk pengurusan dokumen yang hilang.','Yang bertanda tangan di bawah ini, {{jabatan_ketua_rt}} {{wilayah_rt}}, menerangkan bahwa:\nNama : {{nama_warga}}\nNIK : {{nik_warga}}\nTempat, Tanggal Lahir : {{tempat_tanggal_lahir}}\nJenis Kelamin : {{jenis_kelamin}}\nPekerjaan : {{pekerjaan}}\nAlamat Domisili : {{alamat_domisili}}\n\nYang bersangkutan melaporkan kehilangan {{lost_item}}, nomor atau identitas {{lost_number}}, pada {{loss_date}} di {{loss_place}}. Kronologi menurut pelapor: {{loss_chronology}}. Surat ini dimohon sebagai pengantar pelaporan kepada pihak berwenang.\n\nDemikian surat ini dibuat berdasarkan keterangan yang disampaikan dan data yang tersedia, untuk dipergunakan sebagaimana mestinya.','',13,1),('kelahiran','Surat Pengantar Kelahiran','Keterangan','Laporan kelahiran bayi di lingkungan RT.','Yang bertanda tangan di bawah ini, {{jabatan_ketua_rt}} {{wilayah_rt}}, menerangkan bahwa berdasarkan laporan warga telah terjadi kelahiran dengan data:\nNama Bayi : {{baby_name}}\nJenis Kelamin : {{baby_gender}}\nTempat, Tanggal Lahir : {{baby_birth_place}}, {{baby_birth_date}}\nWaktu Lahir : {{baby_birth_time}}\nAnak Ke : {{child_number}}\nNama Ayah / NIK : {{father_name}} / {{father_nik}}\nNama Ibu / NIK : {{mother_name}} / {{mother_nik}}\nPenolong Kelahiran : {{birth_assistant}}\n\nPelapor: {{nama_warga}}, NIK {{nik_warga}}, alamat {{alamat_domisili}}. Surat ini dimohon untuk {{keperluan_surat}}.\n\nDemikian surat ini dibuat berdasarkan keterangan yang disampaikan dan data yang tersedia, untuk dipergunakan sebagaimana mestinya.','',4,1),('kematian','Surat Keterangan Kematian','Keterangan','Laporan warga yang meninggal dunia.','Yang bertanda tangan di bawah ini, {{jabatan_ketua_rt}} {{wilayah_rt}}, menerangkan bahwa:\nNama Almarhum/Almarhumah : {{deceased_name}}\nNIK : {{deceased_nik}}\nTempat, Tanggal Lahir : {{deceased_birth_place}}, {{deceased_birth_date}}\nJenis Kelamin : {{deceased_gender}}\nAgama : {{deceased_religion}}\nAlamat Terakhir : {{last_address}}\n\nMenurut laporan dari {{nama_warga}}, hubungan dengan almarhum/almarhumah: {{relationship}}, yang bersangkutan meninggal dunia pada {{death_date}} pukul {{death_time}} di {{death_place}}. Keterangan sebab meninggal: {{death_cause}}.\nSurat ini dimohon untuk {{keperluan_surat}}.\n\nDemikian surat ini dibuat berdasarkan keterangan yang disampaikan dan data yang tersedia, untuk dipergunakan sebagaimana mestinya.','',3,1),('ktp_kk','Surat Pengantar KTP / KK','Kependudukan','Pengurusan atau perubahan dokumen kependudukan.','Yang bertanda tangan di bawah ini, {{jabatan_ketua_rt}} {{wilayah_rt}}, menerangkan bahwa:\nNama : {{nama_warga}}\nNIK : {{nik_warga}}\nTempat, Tanggal Lahir : {{tempat_tanggal_lahir}}\nJenis Kelamin : {{jenis_kelamin}}\nPekerjaan : {{pekerjaan}}\nAlamat Domisili : {{alamat_domisili}}\n\nYang bersangkutan memohon pengantar layanan kependudukan: {{document_service}}. Nomor KK saat ini: {{current_kk_number}}. Data yang dibuat atau diubah: {{data_change}}. Keperluan: {{keperluan_surat}}.\n\nDemikian surat ini dibuat berdasarkan keterangan yang disampaikan dan data yang tersedia, untuk dipergunakan sebagaimana mestinya.','',7,1),('lainnya','Keperluan Lainnya','Lainnya','Ajukan keperluan administrasi lainnya.','Yang bertanda tangan di bawah ini, {{jabatan_ketua_rt}} {{wilayah_rt}}, menerangkan bahwa:\nNama : {{nama_warga}}\nNIK : {{nik_warga}}\nTempat, Tanggal Lahir : {{tempat_tanggal_lahir}}\nJenis Kelamin : {{jenis_kelamin}}\nPekerjaan : {{pekerjaan}}\nAlamat Domisili : {{alamat_domisili}}\n\nYang bersangkutan mengajukan {{other_subject}} kepada {{other_destination}} dengan rincian: {{other_details}}. Keperluan surat: {{keperluan_surat}}.\n\nDemikian surat ini dibuat berdasarkan keterangan yang disampaikan dan data yang tersedia, untuk dipergunakan sebagaimana mestinya.','',17,1),('nikah','Surat Pengantar Nikah','Keterangan','Pengurusan administrasi pernikahan.','Yang bertanda tangan di bawah ini, {{jabatan_ketua_rt}} {{wilayah_rt}}, menerangkan bahwa:\nNama : {{nama_warga}}\nNIK : {{nik_warga}}\nTempat, Tanggal Lahir : {{tempat_tanggal_lahir}}\nJenis Kelamin : {{jenis_kelamin}}\nPekerjaan : {{pekerjaan}}\nAlamat Domisili : {{alamat_domisili}}\n\nYang bersangkutan menyampaikan rencana pernikahan dengan {{partner_name}}, NIK {{partner_nik}}, pada {{marriage_date}} di {{marriage_place}}. Surat pengantar ini dimohon untuk melengkapi administrasi pernikahan.\n\nDemikian surat ini dibuat berdasarkan keterangan yang disampaikan dan data yang tersedia, untuk dipergunakan sebagaimana mestinya.','',9,1),('pindah','Surat Pengantar Pindah Domisili','Kependudukan','Pengurusan perpindahan alamat domisili.','Yang bertanda tangan di bawah ini, {{jabatan_ketua_rt}} {{wilayah_rt}}, menerangkan bahwa:\nNama : {{nama_warga}}\nNIK : {{nik_warga}}\nTempat, Tanggal Lahir : {{tempat_tanggal_lahir}}\nJenis Kelamin : {{jenis_kelamin}}\nPekerjaan : {{pekerjaan}}\nAlamat Domisili : {{alamat_domisili}}\n\nYang bersangkutan menyampaikan rencana pindah dari {{move_origin}} ke {{move_destination}} pada {{move_date}}. Alasan pindah: {{move_reason}}. Anggota keluarga yang ikut pindah: {{moving_people}}.\nSurat pengantar ini dimohon untuk {{keperluan_surat}}.\n\nDemikian surat ini dibuat berdasarkan keterangan yang disampaikan dan data yang tersedia, untuk dipergunakan sebagaimana mestinya.','',5,1),('renovasi','Surat Pengantar Renovasi Rumah','Kegiatan','Pemberitahuan kegiatan renovasi rumah.','Yang bertanda tangan di bawah ini, {{jabatan_ketua_rt}} {{wilayah_rt}}, menerangkan bahwa:\nNama : {{nama_warga}}\nNIK : {{nik_warga}}\nTempat, Tanggal Lahir : {{tempat_tanggal_lahir}}\nJenis Kelamin : {{jenis_kelamin}}\nPekerjaan : {{pekerjaan}}\nAlamat Domisili : {{alamat_domisili}}\n\nYang bersangkutan menyampaikan rencana renovasi rumah di {{property_address}} berupa {{renovation_work}}, mulai {{renovation_start}} sampai {{renovation_end}}. Surat pengantar ini dibuat untuk pemberitahuan kepada lingkungan setempat.\n\nDemikian surat ini dibuat berdasarkan keterangan yang disampaikan dan data yang tersedia, untuk dipergunakan sebagaimana mestinya.','',15,1),('skck','Surat Pengantar SKCK','Keterangan','Pengantar untuk keperluan pengurusan SKCK.','Yang bertanda tangan di bawah ini, {{jabatan_ketua_rt}} {{wilayah_rt}}, menerangkan bahwa:\nNama : {{nama_warga}}\nNIK : {{nik_warga}}\nTempat, Tanggal Lahir : {{tempat_tanggal_lahir}}\nJenis Kelamin : {{jenis_kelamin}}\nPekerjaan : {{pekerjaan}}\nAlamat Domisili : {{alamat_domisili}}\n\nYang bersangkutan memohon pengantar untuk pengurusan Surat Keterangan Catatan Kepolisian pada {{police_destination}} dengan keperluan {{skck_reason}}. Surat ini merupakan pengantar dari pengurus RT setempat.\n\nDemikian surat ini dibuat berdasarkan keterangan yang disampaikan dan data yang tersedia, untuk dipergunakan sebagaimana mestinya.','',8,1),('surat_pengantar','Surat Pengantar','Kependudukan','Untuk berbagai keperluan administrasi ke kelurahan atau instansi lainnya.','Yang bertanda tangan di bawah ini, {{jabatan_ketua_rt}} {{wilayah_rt}}, menerangkan bahwa:\nNama : {{nama_warga}}\nNIK : {{nik_warga}}\nTempat, Tanggal Lahir : {{tempat_tanggal_lahir}}\nJenis Kelamin : {{jenis_kelamin}}\nPekerjaan : {{pekerjaan}}\nAlamat Domisili : {{alamat_domisili}}\n\nYang bersangkutan memohon surat pengantar kepada {{tujuan_instansi}} untuk urusan {{jenis_urusan}}. Nomor Kartu Keluarga: {{nomor_kk}}.\nKeperluan: {{keperluan_surat}}.\n\nDemikian surat ini dibuat berdasarkan keterangan yang disampaikan dan data yang tersedia, untuk dipergunakan sebagaimana mestinya.','',1,1),('tidak_mampu','Surat Pengantar Tidak Mampu','Keterangan','Pengantar untuk pengajuan bantuan sosial atau pendidikan.','Yang bertanda tangan di bawah ini, {{jabatan_ketua_rt}} {{wilayah_rt}}, menerangkan bahwa:\nNama : {{nama_warga}}\nNIK : {{nik_warga}}\nTempat, Tanggal Lahir : {{tempat_tanggal_lahir}}\nJenis Kelamin : {{jenis_kelamin}}\nPekerjaan : {{pekerjaan}}\nAlamat Domisili : {{alamat_domisili}}\n\nBerdasarkan keterangan yang disampaikan, penghasilan keluarga per bulan adalah {{income_range}} dengan {{dependents}} tanggungan. Yang bersangkutan memohon keterangan untuk {{assistance_purpose}}. Surat ini diberikan untuk pertimbangan pihak yang berwenang.\n\nDemikian surat ini dibuat berdasarkan keterangan yang disampaikan dan data yang tersedia, untuk dipergunakan sebagaimana mestinya.','',11,1),('usaha','Surat Keterangan Usaha','Keterangan','Pengantar untuk keperluan administrasi usaha warga.','Yang bertanda tangan di bawah ini, {{jabatan_ketua_rt}} {{wilayah_rt}}, menerangkan bahwa:\nNama : {{nama_warga}}\nNIK : {{nik_warga}}\nTempat, Tanggal Lahir : {{tempat_tanggal_lahir}}\nJenis Kelamin : {{jenis_kelamin}}\nPekerjaan : {{pekerjaan}}\nAlamat Domisili : {{alamat_domisili}}\n\nBerdasarkan keterangan yang disampaikan, yang bersangkutan menjalankan usaha {{business_name}} di bidang {{business_type}}, beralamat di {{business_address}}, sejak {{business_since}}. Surat ini dimohon untuk {{keperluan_surat}}.\n\nDemikian surat ini dibuat berdasarkan keterangan yang disampaikan dan data yang tersedia, untuk dipergunakan sebagaimana mestinya.','',10,1),('warga_baru','Surat Pengantar Warga Baru','Kependudukan','Laporan warga yang baru menetap di lingkungan RT.','Yang bertanda tangan di bawah ini, {{jabatan_ketua_rt}} {{wilayah_rt}}, menerangkan bahwa:\nNama : {{nama_warga}}\nNIK : {{nik_warga}}\nTempat, Tanggal Lahir : {{tempat_tanggal_lahir}}\nJenis Kelamin : {{jenis_kelamin}}\nPekerjaan : {{pekerjaan}}\nAlamat Domisili : {{alamat_domisili}}\n\nYang bersangkutan melaporkan telah mulai menetap di lingkungan ini sejak {{arrival_date}}, sebelumnya beralamat di {{previous_address}}. Kepala keluarga: {{new_household_head}}. Anggota keluarga yang ikut menetap: {{arriving_people}}.\n\nDemikian surat ini dibuat berdasarkan keterangan yang disampaikan dan data yang tersedia, untuk dipergunakan sebagaimana mestinya.','',6,1);
/*!40000 ALTER TABLE `letter_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `officials`
--

DROP TABLE IF EXISTS `officials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `officials` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(140) NOT NULL,
  `position` varchar(140) NOT NULL,
  `description` varchar(500) NOT NULL DEFAULT '',
  `icon` varchar(20) NOT NULL DEFAULT '👤',
  `photo` varchar(255) DEFAULT NULL,
  `parent_id` int(10) unsigned DEFAULT NULL,
  `contact` varchar(30) NOT NULL DEFAULT '',
  `term_start` year(4) DEFAULT NULL,
  `term_end` year(4) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_official_parent` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `officials`
--

LOCK TABLES `officials` WRITE;
/*!40000 ALTER TABLE `officials` DISABLE KEYS */;
/*!40000 ALTER TABLE `officials` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pages`
--

DROP TABLE IF EXISTS `pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(160) NOT NULL,
  `slug` varchar(180) NOT NULL,
  `body` longtext NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `show_in_menu` tinyint(1) NOT NULL DEFAULT 1,
  `is_published` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pages`
--

LOCK TABLES `pages` WRITE;
/*!40000 ALTER TABLE `pages` DISABLE KEYS */;
INSERT INTO `pages` VALUES (1,'Profil Lingkungan','profil','Informasi sejarah, batas wilayah, dan fasilitas RT 09 / RW 08 akan dilengkapi oleh pengurus.',NULL,1,1,1);
/*!40000 ALTER TABLE `pages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `posts`
--

DROP TABLE IF EXISTS `posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `posts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` enum('pengumuman','agenda','galeri') NOT NULL,
  `title` varchar(180) NOT NULL,
  `body` text NOT NULL,
  `event_date` date DEFAULT NULL,
  `event_time` time DEFAULT NULL,
  `event_location` varchar(160) NOT NULL DEFAULT '',
  `image` varchar(255) DEFAULT NULL,
  `status` enum('draft','pending','published') NOT NULL DEFAULT 'draft',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `type_status_date` (`type`,`status`,`event_date`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `posts`
--

LOCK TABLES `posts` WRITE;
/*!40000 ALTER TABLE `posts` DISABLE KEYS */;
INSERT INTO `posts` VALUES (2,'pengumuman','LOMBA 17 AGUSTUS – SEMARAK HUT KEMERDEKAAN RI DI SUKA MULYA IV','🇮🇩 **PENGUMUMAN LOMBA 17 AGUSTUS** 🇮🇩\r\n\r\nDalam rangka memeriahkan Hari Ulang Tahun Kemerdekaan Republik Indonesia, pengurus RT 009/RW 008 mengundang seluruh warga Suka Mulya IV untuk berpartisipasi dalam kegiatan **Lomba 17 Agustus**.\r\n\r\n**Lokasi Kegiatan:**\r\nLingkungan Suka Mulya IV, RT 009/RW 008, Kelurahan Serua Indah, Kota Tangerang Selatan.\r\n\r\n**Waktu Kegiatan:**\r\nTanggal: 17 Agustus (tahun menyesuaikan)\r\nPukul: 08.00 WIB – selesai (rencana)\r\n\r\n**Jenis Perlombaan:**\r\n1. Lomba makan kerupuk\r\n2. Lomba balap karung\r\n3. Lomba memasukkan paku ke botol\r\n4. Lomba membawa kelereng dengan sendok\r\n5. Lomba tarik tambang\r\n6. Lomba joget balon\r\n7. Lomba mewarnai untuk anak-anak\r\n8. Lomba keluarga dan pembagian hadiah\r\n\r\nKegiatan ini terbuka untuk anak-anak, remaja, dan orang tua di lingkungan RT 009/RW 008.\r\n\r\nMari kita meriahkan Hari Kemerdekaan dengan semangat kebersamaan, kekompakan, dan gotong royong.\r\n\r\n**Merdeka! Merdeka! Merdeka! 🇮🇩**\r\n\r\nHormat kami,\r\nPanitia HUT RI\r\nPengurus RT 009/RW 008 – Suka Mulya IV\r\n\r\n*Catatan: Jadwal dan daftar perlombaan merupakan contoh dan dapat disesuaikan oleh panitia.*',NULL,NULL,'','uploads/3561b6b57e566b88d5645e3d66db3626.png','published','2026-10-17 17:21:08');
/*!40000 ALTER TABLE `posts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `quick_links`
--

DROP TABLE IF EXISTS `quick_links`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quick_links` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `subtitle` varchar(160) NOT NULL DEFAULT '',
  `icon_image` varchar(255) NOT NULL DEFAULT '',
  `url_path` varchar(180) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `quick_links`
--

LOCK TABLES `quick_links` WRITE;
/*!40000 ALTER TABLE `quick_links` DISABLE KEYS */;
INSERT INTO `quick_links` VALUES (1,'Pengumuman','Info terbaru untuk warga','assets/mockup/icon-pengumuman.png','pengumuman',1,1),(2,'Agenda','Jadwal kegiatan RT','assets/mockup/icon-agenda.png','agenda',2,1),(3,'Profil RT','Struktur & informasi','assets/mockup/icon-profil.png','profil',3,1),(4,'Layanan Warga','Surat & pengajuan','assets/mockup/icon-layanan.png','warga',4,1),(5,'Iuran Warga','Informasi iuran lingkungan','assets/mockup/icon-iuran.png','warga/iuran',5,1),(6,'Galeri Kegiatan','Dokumentasi warga','assets/mockup/icon-galeri.png','galeri',6,1),(7,'Kontak RT','Hubungi pengurus','assets/mockup/icon-kontak.png','kontak',7,1);
/*!40000 ALTER TABLE `quick_links` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `service_request_details`
--

DROP TABLE IF EXISTS `service_request_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `service_request_details` (
  `request_id` int(10) unsigned NOT NULL,
  `applicant_name` varchar(140) NOT NULL,
  `address` varchar(255) NOT NULL,
  `purpose` varchar(255) NOT NULL,
  `extra_data` text DEFAULT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `letter_number` varchar(100) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `approved_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`request_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `service_request_details`
--

LOCK TABLES `service_request_details` WRITE;
/*!40000 ALTER TABLE `service_request_details` DISABLE KEYS */;
INSERT INTO `service_request_details` VALUES (12,'muhammad chendy setiadi','jln sukamulya IV RT 09 RW 08 No 17','Surat Keterangan','{\"applicant_nik\":\"108093000064\",\"applicant_phone\":\"085697578958\",\"applicant_birth_place\":\"tangerang\",\"applicant_birth_date\":\"1989-09-26\",\"applicant_gender\":\"Laki-laki\",\"applicant_religion\":\"islam\",\"applicant_occupation\":\"karyawan swasta\",\"applicant_ktp_address\":\"jln sukamulya IV RT 09 RW 08 No 17\"}',NULL,NULL,NULL,NULL),(41,'muhammad chendy setiadi','jln sukamulya IV RT 09 RW 08 No 17','Surat Keterangan','{\"applicant_nik\":\"1234567891011111\",\"applicant_phone\":\"085697578958\",\"applicant_birth_place\":\"tangerang\",\"applicant_birth_date\":\"\",\"applicant_gender\":\"Laki-laki\",\"applicant_religion\":\"islam\",\"applicant_occupation\":\"karyawan swasta\",\"applicant_ktp_address\":\"jln sukamulya IV RT 09 RW 08 No 17\"}',NULL,'001/RT/Tangsel/2026','2026-10-17 14:32:29',1),(54,'muhammad chendy setiadi','jln sukamulya IV RT 09 RW 08 No 17','untuk sekolah','{\"applicant_nik\":\"1234567891011111\",\"applicant_phone\":\"085697578958\",\"applicant_birth_place\":\"tangerang\",\"applicant_birth_date\":\"1989-10-06\",\"applicant_gender\":\"Laki-laki\",\"applicant_religion\":\"islam\",\"applicant_occupation\":\"karyawan swasta\",\"applicant_ktp_address\":\"jln sukamulya IV RT 09 RW 08 No 17\",\"domicile_since\":\"2026-10-12\",\"residence_status\":\"tetap\",\"household_head\":\"chendy\"}',NULL,'111/rt/0/2026','2026-10-18 04:41:58',1);
/*!40000 ALTER TABLE `service_request_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `service_request_documents`
--

DROP TABLE IF EXISTS `service_request_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `service_request_documents` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `request_id` int(10) unsigned NOT NULL,
  `label` varchar(100) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `request_id` (`request_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `service_request_documents`
--

LOCK TABLES `service_request_documents` WRITE;
/*!40000 ALTER TABLE `service_request_documents` DISABLE KEYS */;
/*!40000 ALTER TABLE `service_request_documents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `service_requests`
--

DROP TABLE IF EXISTS `service_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `service_requests` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `citizen_id` int(10) unsigned NOT NULL,
  `type` varchar(40) NOT NULL,
  `subject` varchar(180) NOT NULL,
  `description` text NOT NULL,
  `status` enum('draft','diajukan','diproses','disetujui','ditolak','selesai') NOT NULL DEFAULT 'diajukan',
  `admin_note` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `citizen_status` (`citizen_id`,`status`)
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `service_requests`
--

LOCK TABLES `service_requests` WRITE;
/*!40000 ALTER TABLE `service_requests` DISABLE KEYS */;
INSERT INTO `service_requests` VALUES (12,2,'domisili','Surat Keterangan Domisili','Surat Keterangan','draft',NULL,'2026-10-17 19:03:04',NULL),(41,2,'domisili','Surat Keterangan Domisili','','disetujui','','2026-10-17 19:15:24','2026-10-17 14:32:29'),(54,2,'domisili','Surat Keterangan Domisili','','disetujui','','2026-10-18 09:41:07','2026-10-18 04:41:58');
/*!40000 ALTER TABLE `service_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `setting_key` varchar(80) NOT NULL,
  `setting_value` text NOT NULL,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES ('about_image','assets/mockup/about-neighborhood.png'),('about_text','RT 009/008 Suka Mulya IV adalah bagian dari lingkungan masyarakat yang menjunjung tinggi nilai gotong royong, kebersamaan, dan kepedulian antarwarga.'),('about_title','Tentang RT 009/008'),('address','Suka Mulya IV'),('agenda_icon','assets/mockup/icon-calendar-small.png'),('announcement_icon','assets/mockup/icon-announcement.png'),('brand_label','RT 009/008'),('brand_subtitle','Suka Mulya IV'),('email',''),('emergency_contact',''),('female_residents','0'),('footer_text','┬® RT 09 / RW 08 Sukamulya.'),('footer_wave','assets/mockup/footer-wave.png'),('hero_accent','Suka Mulya IV'),('hero_image','assets/mockup/hero-neighborhood.png'),('hero_kicker','SELAMAT DATANG DI WEBSITE'),('hero_text','Bersama Membangun Lingkungan yang Aman, Nyaman, Rukun dan Sejahtera.'),('hero_title','RT 009/008'),('households','0'),('letter_address','Kel. Suka Mulya, Kec. Serpong, Kota Tangerang Selatan'),('letter_district','Ciputat'),('letter_environment','Suka Mulya IV'),('letter_logo','11e4f869912d7a009a8dda94307fae74.webp'),('letter_postal','15414'),('letter_region','Tangerang Selatan'),('letter_rt_name','RUKUN TETANGGA 009 / RUKUN WARGA 008'),('letter_signature','1f6a72d73a092d1c04aea8213df7f398.png'),('letter_signer_name','rohmat'),('letter_signer_title','Ketua RT 009 / RW 008'),('letter_stamp','7edc5e9f78b15ffc9770d8137bcfc274.png'),('letter_village','Suka Mulya IV'),('logo','assets/mockup/logo.png'),('male_residents','0'),('map_url',''),('phone',''),('residents','0'),('site_name','RT 009/008 Suka Mulya IV'),('slogan','Satu Warga, Satu Keluarga'),('tagline','Website lingkungan RT 009/008 Suka Mulya IV'),('value1_icon','assets/mockup/icon-rukun.png'),('value1_text','Menjaga silaturahmi'),('value1_title','Rukun'),('value2_icon','assets/mockup/icon-bersih.png'),('value2_text','Lingkungan yang sehat'),('value2_title','Bersih'),('value3_icon','assets/mockup/icon-aman.png'),('value3_text','Nyaman untuk semua warga'),('value3_title','Aman'),('whatsapp','');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transactions`
--

DROP TABLE IF EXISTS `transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transactions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
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
  `created_by` int(10) unsigned NOT NULL,
  `verified_by` int(10) unsigned DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `reverses_id` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `period_status` (`period`,`status`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transactions`
--

LOCK TABLES `transactions` WRITE;
/*!40000 ALTER TABLE `transactions` DISABLE KEYS */;
INSERT INTO `transactions` VALUES (2,'2026-10-17','masuk','Iuran lingkungan','Iuran rutin Iuran lingkungan 2026-10',10000.00,'2026-10','','','verified',NULL,1,1,'2026-10-17 15:29:09',NULL,'2026-10-17 20:29:09');
/*!40000 ALTER TABLE `transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping events for database 'sukamulya'
--

--
-- Dumping routines for database 'sukamulya'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-22  8:52:13
