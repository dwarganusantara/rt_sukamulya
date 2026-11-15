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
-- Table structure for table `account_invitations`
--

DROP TABLE IF EXISTS `account_invitations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `account_invitations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `family_id` int(10) unsigned NOT NULL,
  `token_hash` char(64) NOT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `used_at` datetime DEFAULT NULL,
  `revoked_at` datetime DEFAULT NULL,
  `citizen_id` int(10) unsigned DEFAULT NULL,
  `member_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_invite_token` (`token_hash`),
  KEY `idx_invite_family` (`family_id`,`used_at`,`revoked_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `account_invitations`
--

LOCK TABLES `account_invitations` WRITE;
/*!40000 ALTER TABLE `account_invitations` DISABLE KEYS */;
/*!40000 ALTER TABLE `account_invitations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `action_items`
--

DROP TABLE IF EXISTS `action_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `action_items` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `action_items`
--

LOCK TABLES `action_items` WRITE;
/*!40000 ALTER TABLE `action_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `action_items` ENABLE KEYS */;
UNLOCK TABLES;

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
  `ip_address` varchar(45) DEFAULT NULL,
  `details` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=372 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_logs`
--

LOCK TABLES `activity_logs` WRITE;
/*!40000 ALTER TABLE `activity_logs` DISABLE KEYS */;
INSERT INTO `activity_logs` VALUES (5,1,'update','quick_link:7','2026-10-17 12:58:08',NULL,NULL),(6,1,'update','quick_link:7','2026-10-17 12:58:08',NULL,NULL),(7,1,'create','galeri:1','2026-10-17 13:07:30',NULL,NULL),(8,1,'create','citizen:2','2026-10-17 13:29:51',NULL,NULL),(9,7,'review','request:2','2026-10-17 16:38:50',NULL,NULL),(10,1,'update','letter_settings','2026-10-17 16:52:14',NULL,NULL),(11,1,'update','letter_settings','2026-10-17 16:52:40',NULL,NULL),(12,1,'update','letter_settings','2026-10-17 16:54:01',NULL,NULL),(13,1,'update','letter_settings','2026-10-17 16:55:59',NULL,NULL),(14,1,'update','letter_settings','2026-10-17 16:59:21',NULL,NULL),(15,1,'update','letter_settings','2026-10-17 17:03:28',NULL,NULL),(16,1,'update','letter_settings','2026-10-17 17:03:32',NULL,NULL),(17,1,'update','letter_settings','2026-10-17 17:04:00',NULL,NULL),(18,1,'create','pengumuman:2','2026-10-17 17:21:08',NULL,NULL),(19,1,'review','request:41','2026-10-17 19:32:29',NULL,NULL),(20,1,'update','letter_settings','2026-10-17 19:45:42',NULL,NULL),(21,1,'update','letter_settings','2026-10-17 19:53:13',NULL,NULL),(22,1,'create','due:2','2026-10-17 19:59:53',NULL,NULL),(23,1,'update','letter_settings','2026-10-17 20:04:59',NULL,NULL),(24,1,'verified','due_payment:1','2026-10-17 20:29:09',NULL,NULL),(25,1,'review','request:54','2026-10-18 09:41:58',NULL,NULL),(26,1,'create','official:1','2026-10-22 14:55:13',NULL,NULL),(27,1,'review','request:55','2026-10-24 20:32:02',NULL,NULL),(28,1,'update','official:3','2026-10-29 18:35:56',NULL,NULL),(29,1,'update','official:1','2026-10-29 18:36:59',NULL,NULL),(30,1,'update','official:1','2026-10-29 18:37:14',NULL,NULL),(31,1,'create','transaction:3','2026-10-31 15:01:41',NULL,NULL),(32,1,'create','transaction:4','2026-10-31 15:06:42',NULL,NULL),(74,1,'view','family:10','2026-11-07 21:08:09','127.0.0.1',NULL),(75,1,'update','settings','2026-11-07 21:30:52','127.0.0.1',NULL),(76,1,'update','settings','2026-11-07 22:20:41','127.0.0.1',NULL),(83,1,'update','settings','2026-11-07 22:44:31','127.0.0.1',NULL),(84,1,'update','settings','2026-11-07 23:01:22','127.0.0.1',NULL),(85,1,'kk_rejected','kk_request:30','2026-11-08 08:32:23','127.0.0.1','double'),(86,0,'ui_apply','ui_config:1','2026-11-08 14:33:59','127.0.0.1','Dark Navy'),(87,0,'ui_reset','ui_config:2','2026-11-08 14:34:14','127.0.0.1','Reset ke Default'),(88,0,'ui_apply','ui_config:3','2026-11-08 14:35:07','127.0.0.1','Teal Fresh'),(89,0,'ui_reset','ui_config:4','2026-11-08 14:35:34','127.0.0.1','Reset ke Default'),(90,1,'create','citizen:129','2026-11-13 20:35:04','127.0.0.1',NULL),(91,0,'view','family:10','2026-11-13 20:40:48','127.0.0.1',NULL),(92,0,'create','mutation:1','2026-11-13 20:42:11','127.0.0.1','Perubahan Alamat Keluarga'),(93,0,'view','mutation:1','2026-11-13 20:42:11','127.0.0.1',NULL),(94,0,'approved','mutation:1','2026-11-13 20:43:04','127.0.0.1',NULL),(95,0,'view','mutation:1','2026-11-13 20:43:04','127.0.0.1',NULL),(96,0,'create','aid_program:1','2026-11-13 20:48:38','127.0.0.1',NULL),(97,0,'view','aid_program:1','2026-11-13 20:48:38','127.0.0.1',NULL),(98,0,'create','aid_program:2','2026-11-13 20:51:59','127.0.0.1',NULL),(99,0,'view','aid_program:2','2026-11-13 20:51:59','127.0.0.1',NULL),(100,0,'add','aid_recipient:1','2026-11-13 20:53:04','127.0.0.1','program 2'),(101,0,'view','aid_program:2','2026-11-13 20:53:04','127.0.0.1',NULL),(102,0,'status','aid_program:2','2026-11-13 20:53:06','127.0.0.1','aktif'),(103,0,'view','aid_program:2','2026-11-13 20:53:06','127.0.0.1',NULL),(104,0,'verify','aid_recipient:1','2026-11-13 20:53:16','127.0.0.1','layak'),(105,0,'view','aid_program:2','2026-11-13 20:53:16','127.0.0.1',NULL),(106,0,'approve','aid_recipient:1','2026-11-13 20:53:19','127.0.0.1','disetujui'),(107,0,'view','aid_program:2','2026-11-13 20:53:20','127.0.0.1',NULL),(108,0,'distribute','aid_recipient:1','2026-11-13 20:54:29','127.0.0.1','disalurkan'),(109,0,'view','aid_program:2','2026-11-13 20:54:29','127.0.0.1',NULL),(110,0,'review','request:55','2026-11-13 20:57:00','127.0.0.1',NULL),(111,0,'create','complaint:1','2026-11-13 21:01:22','127.0.0.1','warga#23 Keamanan'),(112,0,'update','complaint:1','2026-11-13 21:03:40','127.0.0.1','status=in_progress'),(113,0,'update','complaint:1','2026-11-13 21:05:09','127.0.0.1','tanggapan'),(114,0,'create','pengumuman:18','2026-11-13 21:06:27','127.0.0.1',NULL),(115,0,'create','agenda:19','2026-11-13 21:11:24','127.0.0.1',NULL),(116,0,'update','pengumuman:18','2026-11-13 21:11:46','127.0.0.1',NULL),(117,0,'create','galeri:20','2026-11-13 21:13:13','127.0.0.1',NULL),(118,0,'update','galeri:20','2026-11-13 21:13:42','127.0.0.1',NULL),(119,0,'create','billing_campaign:1','2026-11-13 21:15:31','127.0.0.1',NULL),(120,0,'create','billing_campaign:2','2026-11-13 21:16:09','127.0.0.1',NULL),(121,0,'create','billing_campaign:3','2026-11-13 21:17:07','127.0.0.1',NULL),(122,0,'export','dues:2026-11','2026-11-13 21:17:25','127.0.0.1',NULL),(123,0,'verified','due_payment:2','2026-11-13 21:20:10','127.0.0.1',NULL),(124,0,'record','due_payment:3','2026-11-13 21:22:03','127.0.0.1',NULL),(125,0,'record','due_payment:4','2026-11-13 21:22:14','127.0.0.1',NULL),(126,0,'record','due_payment:5','2026-11-13 21:22:30','127.0.0.1',NULL),(127,0,'create','transaction:0','2026-11-13 21:26:10','127.0.0.1',NULL),(128,0,'create','transaction:0','2026-11-13 21:26:51','127.0.0.1',NULL),(129,0,'create','transaction:0','2026-11-13 21:27:30','127.0.0.1',NULL),(130,0,'reconcile','cash:2026-11-13','2026-11-13 21:30:51','127.0.0.1','selisih -150001'),(131,0,'create','program:1','2026-11-13 21:33:43','127.0.0.1',NULL),(132,0,'status','program:1','2026-11-13 21:34:10','127.0.0.1','draft->diajukan'),(133,0,'status','program:1','2026-11-13 21:34:17','127.0.0.1','diajukan->aktif'),(134,0,'create','notulen:1','2026-11-13 21:36:32','127.0.0.1','final'),(135,0,'create','patrol:1','2026-11-13 21:39:09','127.0.0.1','2026-11-13 penuh'),(136,0,'create','incident:1','2026-11-13 21:39:22','127.0.0.1',NULL),(137,0,'create','patrol:2','2026-11-13 21:40:06','127.0.0.1','2026-11-13 shift_1'),(138,0,'checkin','patrol:2','2026-11-13 21:40:15','::1','warga#23'),(139,0,'checkout','patrol:2','2026-11-13 21:40:16','::1','warga#23'),(140,1,'create','asset:1','2026-11-13 21:41:41','127.0.0.1','DFDFDF'),(141,1,'create','purchase:1','2026-11-13 21:43:25','127.0.0.1','draft'),(142,1,'purchase_submit','purchase:1','2026-11-13 21:43:27','127.0.0.1','draft->submitted'),(143,1,'update','settings','2026-11-13 21:54:44','127.0.0.1',NULL),(144,1,'update','settings','2026-11-13 21:56:57','127.0.0.1',NULL),(145,1,'update','settings','2026-11-13 21:57:25','127.0.0.1',NULL),(146,1,'update','settings','2026-11-13 21:57:39','127.0.0.1',NULL),(147,1,'update','settings','2026-11-13 21:58:10','127.0.0.1',NULL),(148,1,'update','settings','2026-11-13 21:58:31','127.0.0.1',NULL),(149,1,'update','settings','2026-11-13 21:58:50','127.0.0.1',NULL),(150,1,'update','settings','2026-11-13 22:00:22','127.0.0.1',NULL),(151,1,'update','settings','2026-11-13 22:01:49','127.0.0.1',NULL),(152,1,'update','settings','2026-11-13 22:03:00','127.0.0.1',NULL),(153,1,'ui_apply','ui_config:5','2026-11-13 22:11:06','127.0.0.1','Dark Navy'),(154,1,'ui_apply','ui_config:6','2026-11-13 22:11:16','127.0.0.1','Hijau Emerald'),(155,1,'ui_apply','ui_config:7','2026-11-13 22:11:37','127.0.0.1','Oranye Harmoni'),(156,1,'delete','official:5','2026-11-13 22:13:06','127.0.0.1',NULL),(157,1,'update','official:2','2026-11-13 22:13:19','127.0.0.1',NULL),(158,1,'update','official:2','2026-11-13 22:13:31','127.0.0.1',NULL),(159,0,'backup','backup_run','2026-11-13 22:21:44','127.0.0.1','db-20261113-222144.sql.gz.enc'),(160,0,'verify','backup:1','2026-11-13 22:21:48','127.0.0.1','ok'),(161,0,'download','backup:1','2026-11-13 22:21:50','127.0.0.1',NULL),(162,81,'create','mutation:2','2026-11-13 22:39:58','::1','Anggota Masuk / Lahir'),(163,81,'create','mutation:3','2026-11-13 22:39:58','::1','Anggota Pindah Keluar'),(164,81,'create','mutation:4','2026-11-13 22:39:59','::1','Anggota Meninggal'),(165,81,'create','mutation:5','2026-11-13 22:39:59','::1','Koreksi Data Anggota'),(166,0,'create','aid_program:3','2026-11-13 22:47:09','127.0.0.1',NULL),(167,0,'view','aid_program:3','2026-11-13 22:47:10','127.0.0.1',NULL),(168,0,'view','aid_program:2','2026-11-13 22:53:22','127.0.0.1',NULL),(169,0,'view','aid_program:2','2026-11-13 22:53:27','127.0.0.1',NULL),(170,0,'view','aid_program:2','2026-11-13 22:54:08','127.0.0.1',NULL),(174,0,'create','aid_program:10','2026-11-13 22:55:11','127.0.0.1',NULL),(175,0,'view','aid_program:10','2026-11-13 22:55:11','127.0.0.1',NULL),(176,0,'add','aid_recipient:5','2026-11-13 22:55:24','127.0.0.1','program 10'),(177,0,'view','aid_program:10','2026-11-13 22:55:24','127.0.0.1',NULL),(178,0,'view','aid_program:10','2026-11-13 22:56:14','127.0.0.1',NULL),(179,0,'view','aid_program:10','2026-11-13 22:56:17','127.0.0.1',NULL),(180,0,'view','aid_program:10','2026-11-13 22:56:19','127.0.0.1',NULL),(181,0,'view','aid_program:10','2026-11-13 22:58:22','127.0.0.1',NULL),(186,0,'create','mutation:6','2026-11-13 22:59:26','127.0.0.1','Koreksi Data Anggota'),(187,0,'view','mutation:6','2026-11-13 22:59:27','127.0.0.1',NULL),(188,0,'approved','mutation:6','2026-11-13 23:00:07','127.0.0.1',NULL),(189,0,'view','mutation:6','2026-11-13 23:00:07','127.0.0.1',NULL),(190,0,'view','aid_program:2','2026-11-13 23:00:41','127.0.0.1',NULL),(191,0,'update','aid_program:10','2026-11-13 23:01:04','127.0.0.1',NULL),(192,0,'view','aid_program:10','2026-11-13 23:01:04','127.0.0.1',NULL),(193,0,'status','aid_program:10','2026-11-13 23:01:13','127.0.0.1','aktif'),(194,0,'view','aid_program:10','2026-11-13 23:01:13','127.0.0.1',NULL),(195,0,'verify','aid_recipient:5','2026-11-13 23:01:17','127.0.0.1','layak'),(196,0,'view','aid_program:10','2026-11-13 23:01:17','127.0.0.1',NULL),(197,0,'approve','aid_recipient:5','2026-11-13 23:01:21','127.0.0.1','disetujui'),(198,0,'view','aid_program:10','2026-11-13 23:01:22','127.0.0.1',NULL),(199,0,'view','aid_program:3','2026-11-13 23:07:10','127.0.0.1',NULL),(202,0,'view','aid_program:10','2026-11-13 23:09:40','127.0.0.1',NULL),(204,1,'view','aid_program:10','2026-11-13 23:17:24','127.0.0.1',NULL),(205,1,'view','aid_program:3','2026-11-13 23:17:46','127.0.0.1',NULL),(206,1,'status','aid_program:3','2026-11-13 23:17:48','127.0.0.1','aktif'),(207,1,'view','aid_program:3','2026-11-13 23:17:48','127.0.0.1',NULL),(208,1,'add','aid_recipient:9','2026-11-13 23:17:57','127.0.0.1','program 3'),(209,1,'view','aid_program:3','2026-11-13 23:17:57','127.0.0.1',NULL),(210,1,'view','aid_program:3','2026-11-13 23:18:01','127.0.0.1',NULL),(211,1,'view','aid_program:3','2026-11-13 23:18:37','127.0.0.1',NULL),(212,1,'view','aid_program:3','2026-11-13 23:21:10','127.0.0.1',NULL),(213,1,'view','aid_program:3','2026-11-13 23:21:17','127.0.0.1',NULL),(214,1,'view','aid_program:3','2026-11-13 23:22:34','127.0.0.1',NULL),(215,1,'view','aid_program:3','2026-11-13 23:22:35','127.0.0.1',NULL),(216,1,'view','aid_program:3','2026-11-13 23:22:51','127.0.0.1',NULL),(217,1,'view','aid_program:3','2026-11-13 23:22:54','127.0.0.1',NULL),(218,1,'view','aid_program:3','2026-11-13 23:23:15','127.0.0.1',NULL),(219,1,'view','aid_program:3','2026-11-13 23:23:17','127.0.0.1',NULL),(220,1,'view','aid_program:3','2026-11-13 23:23:49','127.0.0.1',NULL),(221,1,'verify','aid_recipient:9','2026-11-13 23:23:59','127.0.0.1','tidak_layak'),(222,1,'view','aid_program:3','2026-11-13 23:24:00','127.0.0.1',NULL),(223,1,'view','aid_program:1','2026-11-13 23:24:45','127.0.0.1',NULL),(224,1,'add','aid_recipient:10','2026-11-13 23:24:53','127.0.0.1','program 1'),(225,1,'view','aid_program:1','2026-11-13 23:24:53','127.0.0.1',NULL),(232,1,'view','aid_program:10','2026-11-13 23:27:45','127.0.0.1',NULL),(233,1,'view','aid_program:10','2026-11-13 23:28:06','127.0.0.1',NULL),(234,1,'view','aid_program:10','2026-11-13 23:28:15','127.0.0.1',NULL),(235,1,'view','aid_program:2','2026-11-13 23:32:11','127.0.0.1',NULL),(236,1,'view','aid_program:2','2026-11-13 23:32:15','127.0.0.1',NULL),(237,1,'view','aid_program:2','2026-11-13 23:32:47','127.0.0.1',NULL),(238,1,'view','aid_program:10','2026-11-13 23:33:17','127.0.0.1',NULL),(239,1,'view','aid_program:10','2026-11-13 23:33:22','127.0.0.1',NULL),(240,1,'delete','aid_program:3','2026-11-13 23:34:14','127.0.0.1','rrrr (+1 calon penerima)'),(241,1,'delete','aid_program:1','2026-11-13 23:34:17','127.0.0.1','bansos (+1 calon penerima)'),(242,1,'view','aid_program:10','2026-11-13 23:34:32','127.0.0.1',NULL),(243,1,'status','aid_program:10','2026-11-13 23:34:41','127.0.0.1','selesai'),(244,1,'view','aid_program:10','2026-11-13 23:34:41','127.0.0.1',NULL),(245,1,'view','aid_program:10','2026-11-13 23:34:52','127.0.0.1',NULL),(246,1,'view','aid_program:10','2026-11-13 23:35:22','127.0.0.1',NULL),(249,1,'update','aid_program:2','2026-11-13 23:36:22','127.0.0.1',NULL),(250,1,'view','aid_program:2','2026-11-13 23:36:22','127.0.0.1',NULL),(251,1,'status','aid_program:2','2026-11-13 23:36:33','127.0.0.1','selesai'),(252,1,'view','aid_program:2','2026-11-13 23:36:33','127.0.0.1',NULL),(253,1,'view','aid_program:10','2026-11-13 23:37:42','127.0.0.1',NULL),(254,1,'view','aid_program:10','2026-11-13 23:38:07','127.0.0.1',NULL),(255,1,'view','aid_program:10','2026-11-13 23:38:33','127.0.0.1',NULL),(256,1,'view','aid_program:2','2026-11-13 23:38:37','127.0.0.1',NULL),(257,1,'view','aid_program:10','2026-11-13 23:39:45','127.0.0.1',NULL),(258,1,'view','aid_program:2','2026-11-13 23:39:49','127.0.0.1',NULL),(259,1,'delete','aid_program:10','2026-11-13 23:42:49','127.0.0.1','dfdfdf (+1 calon penerima)'),(260,1,'create','aid_program:16','2026-11-13 23:43:10','127.0.0.1',NULL),(261,1,'view','aid_program:16','2026-11-13 23:43:10','127.0.0.1',NULL),(262,1,'add','aid_recipient:12','2026-11-13 23:44:13','127.0.0.1','program 16'),(263,1,'view','aid_program:16','2026-11-13 23:44:13','127.0.0.1',NULL),(264,1,'verify','aid_recipient:12','2026-11-13 23:44:19','127.0.0.1','layak'),(265,1,'view','aid_program:16','2026-11-13 23:44:19','127.0.0.1',NULL),(266,1,'view','aid_program:16','2026-11-13 23:44:29','127.0.0.1',NULL),(267,1,'view','aid_program:16','2026-11-13 23:44:32','127.0.0.1',NULL),(268,1,'status','aid_program:16','2026-11-13 23:45:20','127.0.0.1','aktif'),(269,1,'view','aid_program:16','2026-11-13 23:45:21','127.0.0.1',NULL),(270,1,'approve','aid_recipient:12','2026-11-13 23:45:30','127.0.0.1','disetujui'),(271,1,'view','aid_program:16','2026-11-13 23:45:30','127.0.0.1',NULL),(272,1,'status','aid_program:16','2026-11-13 23:45:49','127.0.0.1','selesai'),(273,1,'view','aid_program:16','2026-11-13 23:45:49','127.0.0.1',NULL),(274,1,'view','aid_program:16','2026-11-13 23:46:08','127.0.0.1',NULL),(275,1,'create','aid_program:17','2026-11-13 23:48:57','127.0.0.1',NULL),(276,1,'view','aid_program:17','2026-11-13 23:48:58','127.0.0.1',NULL),(277,1,'status','aid_program:17','2026-11-13 23:49:01','127.0.0.1','aktif'),(278,1,'view','aid_program:17','2026-11-13 23:49:01','127.0.0.1',NULL),(279,1,'view','aid_program:17','2026-11-13 23:49:58','127.0.0.1',NULL),(280,1,'add','aid_recipient:13','2026-11-13 23:50:13','127.0.0.1','program 17'),(281,1,'view','aid_program:17','2026-11-13 23:50:13','127.0.0.1',NULL),(282,1,'view','aid_program:17','2026-11-13 23:50:25','127.0.0.1',NULL),(283,1,'view','aid_program:17','2026-11-13 23:50:33','127.0.0.1',NULL),(284,1,'verify','aid_recipient:13','2026-11-13 23:50:46','127.0.0.1','layak'),(285,1,'view','aid_program:17','2026-11-13 23:50:47','127.0.0.1',NULL),(286,1,'view','aid_program:17','2026-11-13 23:51:08','127.0.0.1',NULL),(287,1,'status','aid_program:17','2026-11-13 23:51:23','127.0.0.1','selesai'),(288,1,'view','aid_program:17','2026-11-13 23:51:23','127.0.0.1',NULL),(289,1,'create','aid_program:18','2026-11-13 23:51:58','127.0.0.1',NULL),(290,1,'view','aid_program:18','2026-11-13 23:51:58','127.0.0.1',NULL),(291,1,'status','aid_program:18','2026-11-13 23:52:02','127.0.0.1','aktif'),(292,1,'view','aid_program:18','2026-11-13 23:52:02','127.0.0.1',NULL),(293,1,'view','aid_program:18','2026-11-13 23:52:15','127.0.0.1',NULL),(294,1,'view','aid_program:18','2026-11-13 23:52:32','127.0.0.1',NULL),(295,1,'view','aid_program:18','2026-11-13 23:54:38','127.0.0.1',NULL),(296,1,'add_all','aid_program:18','2026-11-13 23:55:04','127.0.0.1','1 KK diusulkan'),(297,1,'view','aid_program:18','2026-11-13 23:55:04','127.0.0.1',NULL),(298,1,'verify','aid_recipient:14','2026-11-13 23:55:20','127.0.0.1','layak'),(299,1,'view','aid_program:18','2026-11-13 23:55:20','127.0.0.1',NULL),(300,1,'view','aid_program:18','2026-11-13 23:55:44','127.0.0.1',NULL),(301,1,'view','aid_program:18','2026-11-13 23:58:30','127.0.0.1',NULL),(302,1,'view','aid_program:18','2026-11-13 23:58:32','127.0.0.1',NULL),(303,1,'view','aid_program:18','2026-11-13 23:58:46','127.0.0.1',NULL),(304,1,'view','aid_program:18','2026-11-13 23:59:49','127.0.0.1',NULL),(305,1,'update','aid_program:18','2026-11-14 00:04:04','127.0.0.1',NULL),(306,1,'view','aid_program:18','2026-11-14 00:04:04','127.0.0.1',NULL),(307,1,'status','aid_program:18','2026-11-14 00:04:09','127.0.0.1','selesai'),(308,1,'view','aid_program:18','2026-11-14 00:04:10','127.0.0.1',NULL),(309,1,'export','aid_programs','2026-11-14 06:36:36','::1','4 program (agregat)'),(310,1,'export','penduduk','2026-11-14 06:53:27','::1','baris=5 filter=status=aktif'),(311,1,'view','family:10','2026-11-14 06:54:59','::1',NULL),(312,1,'export','aid_programs','2026-11-14 06:59:20','127.0.0.1','4 program (agregat, xlsx)'),(313,1,'export_full','penduduk','2026-11-14 07:06:45','127.0.0.1','baris=5 filter=status=aktif alasan=dfdfdfdfdfdfdfdfdfdfdfdfdf'),(314,1,'view','family:10','2026-11-14 07:08:40','127.0.0.1',NULL),(315,1,'view','family:10','2026-11-14 07:11:01','127.0.0.1',NULL),(316,1,'view','family:10','2026-11-14 07:12:11','127.0.0.1',NULL),(317,1,'view','family:10','2026-11-14 07:16:18','127.0.0.1',NULL),(318,103,'create','house:1','2026-11-14 07:17:53','::1','kode=UJI-BB55CD'),(319,103,'verify','residence:1','2026-11-14 07:17:53','::1','family=24 house=1'),(320,103,'create','house:2','2026-11-14 07:17:53','::1','kode=UJI-BB55CDB'),(321,103,'verify','residence:2','2026-11-14 07:17:53','::1','family=24 house=2'),(322,104,'create','house:3','2026-11-14 07:19:45','::1','kode=UJI-566FFE'),(323,104,'verify','residence:3','2026-11-14 07:19:45','::1','family=25 house=3'),(324,104,'create','house:4','2026-11-14 07:19:46','::1','kode=UJI-566FFEB'),(325,104,'verify','residence:4','2026-11-14 07:19:46','::1','family=25 house=4'),(326,1,'ocr_preview','master_kk','2026-11-14 07:24:47','127.0.0.1','anggota=5'),(327,1,'ocr_preview','master_kk','2026-11-14 08:47:39','127.0.0.1','anggota=5'),(328,106,'create','house:5','2026-11-14 08:51:44','::1','kode=UJI-C2FFEF'),(329,106,'verify','residence:5','2026-11-14 08:51:45','::1','family=26 house=5'),(330,106,'create','house:6','2026-11-14 08:51:45','::1','kode=UJI-C2FFEFB'),(331,106,'verify','residence:6','2026-11-14 08:51:45','::1','family=26 house=6'),(332,1,'view','family:10','2026-11-14 08:54:10','127.0.0.1',NULL),(333,1,'view','family:10','2026-11-14 08:54:17','127.0.0.1',NULL),(334,1,'view','family:10','2026-11-14 08:56:11','127.0.0.1',NULL),(335,1,'view','penduduk:rekap','2026-11-14 08:57:06','127.0.0.1',NULL),(336,108,'create','house:7','2026-11-14 08:59:45','::1','kode=UJI-9BD765'),(337,108,'verify','residence:7','2026-11-14 08:59:45','::1','family=27 house=7'),(338,108,'create','house:8','2026-11-14 08:59:45','::1','kode=UJI-9BD765B'),(339,108,'verify','residence:8','2026-11-14 08:59:46','::1','family=27 house=8'),(340,1,'create','house:9','2026-11-14 09:00:43','127.0.0.1','kode=SUKAMULYA IV'),(341,1,'verify','residence:9','2026-11-14 09:00:46','127.0.0.1','family=10 house=9'),(342,1,'view','family:10','2026-11-14 09:00:50','127.0.0.1',NULL),(343,1,'ocr_preview','master_kk','2026-11-14 09:02:04','127.0.0.1','anggota=5'),(344,1,'ocr_preview','master_kk','2026-11-14 09:11:26','127.0.0.1','anggota=5'),(345,1,'ocr_preview','master_kk','2026-11-14 09:14:16','127.0.0.1','anggota=5'),(346,1,'view','family:10','2026-11-14 09:42:21','127.0.0.1',NULL),(347,1,'view','family:10','2026-11-14 09:45:04','127.0.0.1',NULL),(348,1,'view','family:10','2026-11-14 09:45:05','127.0.0.1',NULL),(349,1,'view','family:10','2026-11-14 09:45:20','127.0.0.1',NULL),(350,1,'view','family:10','2026-11-14 09:46:35','127.0.0.1',NULL),(351,1,'delete','pengumuman:18','2026-11-14 10:28:45','127.0.0.1',NULL),(352,1,'export','dues:2026-11:xlsx','2026-11-14 10:29:24','127.0.0.1',NULL),(353,1,'create','billing_campaign:8','2026-11-14 11:21:54','127.0.0.1',NULL),(354,1,'create','billing_campaign:9','2026-11-14 11:22:27','127.0.0.1',NULL),(355,1,'remind','dues','2026-11-14 11:34:25','127.0.0.1','1 terkirim, 0 dilewati'),(356,1,'create','transaction:9','2026-11-14 11:46:58','127.0.0.1',NULL),(357,1,'approve','notulen:1','2026-11-14 11:58:11','127.0.0.1',NULL),(358,1,'feature_update','feature:inventory','2026-11-14 12:16:43','127.0.0.1','Aktif → nonaktif · Menu disembunyikan atas permintaan pengurus (via Claude Code). Data tidak dihapus.'),(359,1,'feature_update','feature:purchasing','2026-11-14 12:16:43','127.0.0.1','Aktif → nonaktif · Menu disembunyikan atas permintaan pengurus (via Claude Code). Data tidak dihapus.'),(360,1,'view','family:10','2026-11-14 15:23:06','::1',NULL),(361,1,'view','family:10','2026-11-14 15:23:35','::1',NULL),(362,1,'status','program:1','2026-11-14 19:48:16','127.0.0.1','aktif->ditunda'),(363,1,'status','program:1','2026-11-14 19:48:33','127.0.0.1','ditunda->aktif'),(364,1,'status','program:1','2026-11-14 19:48:46','127.0.0.1','aktif->selesai'),(365,1,'view','family:10','2026-11-14 20:25:54','127.0.0.1',NULL),(366,0,'ocr_preview','master_kk','2026-11-14 21:15:36','127.0.0.1','anggota=5'),(367,0,'delete','agenda:19','2026-11-14 21:19:22','127.0.0.1',NULL),(368,0,'create','transaction:0','2026-11-14 21:37:39','127.0.0.1',NULL),(369,0,'create','transaction:10','2026-11-14 21:39:00','127.0.0.1',NULL),(370,0,'create','transaction:11','2026-11-14 21:39:32','127.0.0.1',NULL),(371,0,'view','family:10','2026-11-14 21:40:30','127.0.0.1',NULL);
/*!40000 ALTER TABLE `activity_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `activity_note_versions`
--

DROP TABLE IF EXISTS `activity_note_versions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activity_note_versions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `note_id` int(10) unsigned NOT NULL,
  `version` int(10) unsigned NOT NULL,
  `snapshot_json` mediumtext NOT NULL,
  `changed_by` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_note_version` (`note_id`,`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_note_versions`
--

LOCK TABLES `activity_note_versions` WRITE;
/*!40000 ALTER TABLE `activity_note_versions` DISABLE KEYS */;
/*!40000 ALTER TABLE `activity_note_versions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `activity_notes`
--

DROP TABLE IF EXISTS `activity_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activity_notes` (
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_notes`
--

LOCK TABLES `activity_notes` WRITE;
/*!40000 ALTER TABLE `activity_notes` DISABLE KEYS */;
INSERT INTO `activity_notes` VALUES (1,'rapat maling','2026-11-13',NULL,NULL,'serua','',1,'gbgb','gbg','gbgb','disahkan',1,1,'2026-11-14 11:58:11',0,'2026-11-13 21:36:32','2026-11-13 21:36:32');
/*!40000 ALTER TABLE `activity_notes` ENABLE KEYS */;
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
  `role` enum('admin','operator','bendahara','ketua','sekretaris','keamanan','inventaris','sosial','auditor') NOT NULL DEFAULT 'admin',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `full_name` varchar(140) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `custom_role` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=110 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admins`
--

LOCK TABLES `admins` WRITE;
/*!40000 ALTER TABLE `admins` DISABLE KEYS */;
INSERT INTO `admins` VALUES (1,'admin','$2y$10$2DhqArcPy1qvhK4Gy9kOPecrGwWUKCoyAGBlAgPPoB86CThNLPY92','admin',1,NULL,NULL,NULL);
/*!40000 ALTER TABLE `admins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `aid_programs`
--

DROP TABLE IF EXISTS `aid_programs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aid_programs` (
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
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `aid_programs`
--

LOCK TABLES `aid_programs` WRITE;
/*!40000 ALTER TABLE `aid_programs` DISABLE KEYS */;
INSERT INTO `aid_programs` VALUES (2,'promo hut bca','Desember 2026','Uang Tunai',1000000.00,'csr','layak',1,'selesai',0,'2026-11-13 20:51:59','2026-11-13 23:36:33'),(16,'santunan','November 2026','Sembako',1000000.00,'Donasi Warga','layak',1,'selesai',1,'2026-11-13 23:43:10','2026-11-13 23:45:49'),(17,'testing','November 2026','Uang Tunai',100000.00,'Kas RT','testing',1,'selesai',1,'2026-11-13 23:48:57','2026-11-13 23:51:23'),(18,'sendi','November 2026','Sembako',1234.00,'Kas RT','fafsdf',100,'selesai',1,'2026-11-13 23:51:58','2026-11-14 00:04:09');
/*!40000 ALTER TABLE `aid_programs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `aid_recipients`
--

DROP TABLE IF EXISTS `aid_recipients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `aid_recipients` (
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
  KEY `idx_aid_status` (`status`),
  KEY `fk_aid_family` (`family_id`),
  KEY `fk_aid_citizen` (`citizen_id`),
  CONSTRAINT `fk_aid_citizen` FOREIGN KEY (`citizen_id`) REFERENCES `citizens` (`id`),
  CONSTRAINT `fk_aid_family` FOREIGN KEY (`family_id`) REFERENCES `kk_families` (`id`),
  CONSTRAINT `fk_aid_program` FOREIGN KEY (`program_id`) REFERENCES `aid_programs` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `aid_recipients`
--

LOCK TABLES `aid_recipients` WRITE;
/*!40000 ALTER TABLE `aid_recipients` DISABLE KEYS */;
INSERT INTO `aid_recipients` VALUES (1,2,'f:10',23,10,'dewi','serua','sdsds','disalurkan',0,'2026-11-13 20:53:16',0,'2026-11-13 20:53:19','sdsds',1000000.00,'2026-11-13 20:54:29',0,NULL,0,'2026-11-13 20:53:04'),(12,16,'f:10',23,10,'MUHAMMAD CHENDY SETIADI','xccxcx, RT 444/RW 009, Kel. serua indah, Kec. ade, jakarta, 15412','ttt','disetujui',1,'2026-11-13 23:44:19',1,'2026-11-13 23:45:29','layak',1000000.00,NULL,NULL,NULL,1,'2026-11-13 23:44:13'),(13,17,'f:10',23,10,'MUHAMMAD CHENDY SETIADI','xccxcx, RT 444/RW 009, Kel. serua indah, Kec. ade, jakarta, 15412','bagus','layak',1,'2026-11-13 23:50:46',NULL,NULL,'layak',100000.00,NULL,NULL,NULL,1,'2026-11-13 23:50:13'),(14,18,'f:10',23,10,'MUHAMMAD CHENDY SETIADI','xccxcx, RT 444/RW 009, Kel. serua indah, Kec. ade, jakarta, 15412','rrr','layak',1,'2026-11-13 23:55:20',NULL,NULL,'layak',1234.00,NULL,NULL,NULL,1,'2026-11-13 23:55:04');
/*!40000 ALTER TABLE `aid_recipients` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_features`
--

DROP TABLE IF EXISTS `app_features`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_features` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(80) NOT NULL,
  `name` varchar(150) NOT NULL,
  `category` varchar(80) NOT NULL,
  `parent_id` int(10) unsigned DEFAULT NULL,
  `route_key` varchar(150) DEFAULT NULL,
  `icon` varchar(80) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_core` tinyint(1) NOT NULL DEFAULT 0,
  `is_implemented` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_feature_code` (`code`),
  KEY `idx_feature_parent` (`parent_id`),
  CONSTRAINT `fk_feature_parent` FOREIGN KEY (`parent_id`) REFERENCES `app_features` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_features`
--

LOCK TABLES `app_features` WRITE;
/*!40000 ALTER TABLE `app_features` DISABLE KEYS */;
INSERT INTO `app_features` VALUES (1,'family_card','Kartu Keluarga','Kependudukan',NULL,'admin/kk','people',10,0,1,'2026-11-07 22:18:48'),(2,'resident_profile','Data Penduduk','Kependudukan',NULL,'admin/penduduk','list',20,0,1,'2026-11-07 22:18:48'),(3,'resident_mutation','Mutasi Warga','Kependudukan',1,'admin/mutasi','swap',30,0,1,'2026-11-07 22:18:48'),(4,'social_aid','Bantuan Sosial','Kependudukan',NULL,'admin/bansos','heart',40,0,1,'2026-11-07 22:18:48'),(5,'letters','Pengajuan Surat','Layanan Warga',NULL,'admin/requests','doc',50,0,1,'2026-11-07 22:18:48'),(6,'complaints','Pengaduan & Aspirasi','Layanan Warga',NULL,'admin/pengaduan','megaphone',60,0,1,'2026-11-07 22:18:48'),(7,'live_chat','Live Chat Warga','Layanan Warga',NULL,'admin/chat','chat',70,0,1,'2026-11-07 22:18:48'),(8,'dues','Iuran Warga','Keuangan',NULL,'admin/dues','database',80,0,1,'2026-11-07 22:18:48'),(9,'dues_arrears','Tunggakan Iuran Warga','Keuangan',8,'admin/tunggakan','clock',90,0,1,'2026-11-07 22:18:48'),(10,'cash_report','Kas RT','Keuangan',NULL,'admin/transactions','chart',100,0,1,'2026-11-07 22:18:48'),(11,'cash_reconciliation','Rekonsiliasi Kas','Keuangan',10,'admin/rekonsiliasi','list',110,0,1,'2026-11-07 22:18:48'),(12,'announcements','Pengumuman','Informasi',NULL,'admin/posts/pengumuman','megaphone',120,0,1,'2026-11-07 22:18:48'),(13,'events','Agenda Kegiatan','Informasi',NULL,'admin/posts/agenda','calendar',130,0,1,'2026-11-07 22:18:48'),(14,'gallery','Galeri Kegiatan','Informasi',NULL,'admin/posts/galeri','image',140,0,1,'2026-11-07 22:18:48'),(15,'contacts','Kontak Penting','Informasi',NULL,'admin/kontak','phone',150,0,1,'2026-11-07 22:18:48'),(16,'patrol','Jadwal Ronda','Operasional',NULL,'admin/ronda','shield',160,0,1,'2026-11-07 22:18:48'),(17,'work_program','Program Kerja','Operasional',NULL,'admin/program','briefcase',170,0,1,'2026-11-07 22:18:48'),(18,'activity_notes','Catatan & Notulen','Operasional',NULL,'admin/notulen','lines',180,0,1,'2026-11-07 22:18:48'),(19,'inventory','Aset & Inventaris','Operasional',NULL,'admin/inventaris','box',190,0,1,'2026-11-07 22:18:48'),(20,'purchasing','Belanja & Penerimaan','Operasional',NULL,'admin/belanja','cart',200,0,1,'2026-11-07 22:18:48'),(21,'documents','Arsip Dokumen','Operasional',NULL,'admin/arsip','database',210,0,1,'2026-11-07 22:18:48');
/*!40000 ALTER TABLE `app_features` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `asset_loans`
--

DROP TABLE IF EXISTS `asset_loans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `asset_loans` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asset_loans`
--

LOCK TABLES `asset_loans` WRITE;
/*!40000 ALTER TABLE `asset_loans` DISABLE KEYS */;
/*!40000 ALTER TABLE `asset_loans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `assets`
--

DROP TABLE IF EXISTS `assets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `assets` (
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `assets`
--

LOCK TABLES `assets` WRITE;
/*!40000 ALTER TABLE `assets` DISABLE KEYS */;
INSERT INTO `assets` VALUES (1,'DFDFDF','dfdsfds','peralatan','aset',1,1,'unit','baik','dfdsfsd','dfdfdfdf','dfdsfsdf',11111.00,'aktif','',1,'2026-11-13 21:41:41','2026-11-13 21:41:41');
/*!40000 ALTER TABLE `assets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `backup_runs`
--

DROP TABLE IF EXISTS `backup_runs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `backup_runs` (
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `backup_runs`
--

LOCK TABLES `backup_runs` WRITE;
/*!40000 ALTER TABLE `backup_runs` DISABLE KEYS */;
INSERT INTO `backup_runs` VALUES (1,'database','success','db-20261113-222144.sql.gz.enc',23295,'66ca46d5589ec4a4611dc899a6f9abca506f6ea3683cbaca72a0b9b7af6198a9','Terenkripsi AES-256-GCM',0,'2026-11-13 22:21:48','2026-11-13 22:21:44');
/*!40000 ALTER TABLE `backup_runs` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `billing_campaigns`
--

LOCK TABLES `billing_campaigns` WRITE;
/*!40000 ALTER TABLE `billing_campaigns` DISABLE KEYS */;
INSERT INTO `billing_campaigns` VALUES (1,'belanja acara','rutin','Keamanan','2026-11',10000.00,'sdsdsds',0,'2026-11-13 21:15:31'),(2,'testing','rutin','Kebersihan','2026-11',100000.00,'keamanan',0,'2026-11-13 21:16:09'),(3,'kebersihan sekolah','rutin','Operasional','2026-11',50000.00,'sddsdsds',0,'2026-11-13 21:17:07'),(8,'sss','non_rutin','Lain-lain','2026-11',111.00,'ddd',1,'2026-11-14 11:21:54'),(9,'sendiiiii','sosial','Sosial','2026-11',1.00,'ffffff',1,'2026-11-14 11:22:27');
/*!40000 ALTER TABLE `billing_campaigns` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cash_reconciliations`
--

DROP TABLE IF EXISTS `cash_reconciliations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cash_reconciliations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `recon_date` date NOT NULL,
  `bank_balance` decimal(15,2) NOT NULL,
  `system_balance` decimal(15,2) NOT NULL,
  `difference` decimal(15,2) NOT NULL,
  `note` varchar(500) NOT NULL DEFAULT '',
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cash_reconciliations`
--

LOCK TABLES `cash_reconciliations` WRITE;
/*!40000 ALTER TABLE `cash_reconciliations` DISABLE KEYS */;
INSERT INTO `cash_reconciliations` VALUES (1,'2026-11-13',10000.00,160001.00,-150001.00,'ilang',0,'2026-11-13 21:30:51');
/*!40000 ALTER TABLE `cash_reconciliations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chat_conversations`
--

DROP TABLE IF EXISTS `chat_conversations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chat_conversations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `citizen_id` int(10) unsigned NOT NULL,
  `assigned_admin_id` int(10) unsigned DEFAULT NULL,
  `last_message_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `citizen_id` (`citizen_id`),
  KEY `assigned_admin_id` (`assigned_admin_id`),
  KEY `last_message_at` (`last_message_at`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chat_conversations`
--

LOCK TABLES `chat_conversations` WRITE;
/*!40000 ALTER TABLE `chat_conversations` DISABLE KEYS */;
INSERT INTO `chat_conversations` VALUES (2,23,1,'2026-11-08 10:01:46','2026-10-28 21:47:00'),(3,2,NULL,NULL,'2026-10-31 18:19:20'),(4,100,NULL,NULL,'2026-11-08 00:27:42'),(5,22,NULL,NULL,'2026-11-08 08:20:02'),(6,128,NULL,NULL,'2026-11-08 12:54:30'),(7,129,NULL,NULL,'2026-11-13 20:35:25');
/*!40000 ALTER TABLE `chat_conversations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chat_messages`
--

DROP TABLE IF EXISTS `chat_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chat_messages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `conversation_id` int(10) unsigned NOT NULL,
  `sender_type` enum('citizen','admin') NOT NULL,
  `sender_id` int(10) unsigned NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `conversation_messages` (`conversation_id`,`id`),
  KEY `unread_lookup` (`conversation_id`,`sender_type`,`is_read`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chat_messages`
--

LOCK TABLES `chat_messages` WRITE;
/*!40000 ALTER TABLE `chat_messages` DISABLE KEYS */;
INSERT INTO `chat_messages` VALUES (3,2,'citizen',23,'tes',1,'2026-10-28 21:57:15'),(4,2,'citizen',23,'tes',1,'2026-11-08 00:00:59'),(5,2,'citizen',23,'hahaha',1,'2026-11-08 00:01:14'),(6,2,'citizen',23,'blee',1,'2026-11-08 04:58:00'),(7,2,'admin',1,'hola',1,'2026-11-08 10:01:46');
/*!40000 ALTER TABLE `chat_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chat_presence`
--

DROP TABLE IF EXISTS `chat_presence`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chat_presence` (
  `user_type` enum('citizen','admin') NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `last_seen` datetime NOT NULL,
  PRIMARY KEY (`user_type`,`user_id`),
  KEY `last_seen` (`last_seen`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chat_presence`
--

LOCK TABLES `chat_presence` WRITE;
/*!40000 ALTER TABLE `chat_presence` DISABLE KEYS */;
INSERT INTO `chat_presence` VALUES ('admin',12,'2026-11-05 10:56:57'),('admin',14,'2026-11-05 11:05:13'),('admin',15,'2026-11-05 11:05:22'),('admin',16,'2026-11-05 11:07:41'),('admin',17,'2026-11-05 11:30:51'),('citizen',77,'2026-11-05 11:37:12'),('citizen',79,'2026-11-05 11:37:35'),('citizen',80,'2026-11-05 11:37:44'),('citizen',81,'2026-11-05 11:38:12'),('citizen',82,'2026-11-05 11:38:42'),('citizen',85,'2026-11-05 11:45:01'),('citizen',86,'2026-11-05 11:48:10'),('citizen',87,'2026-11-05 11:48:10'),('citizen',88,'2026-11-05 11:55:28'),('citizen',89,'2026-11-05 11:55:28'),('citizen',90,'2026-11-05 12:05:16'),('citizen',91,'2026-11-05 12:05:16'),('citizen',2,'2026-11-08 00:10:50'),('citizen',114,'2026-11-08 07:19:00'),('citizen',115,'2026-11-08 07:45:55'),('citizen',116,'2026-11-08 07:57:13'),('citizen',118,'2026-11-08 07:58:09'),('citizen',119,'2026-11-08 07:58:51'),('citizen',120,'2026-11-08 08:01:09'),('citizen',121,'2026-11-08 08:01:23'),('citizen',122,'2026-11-08 08:04:01'),('citizen',126,'2026-11-08 08:16:38'),('citizen',22,'2026-11-08 08:23:42'),('admin',72,'2026-11-08 09:03:52'),('admin',73,'2026-11-08 11:50:11'),('admin',74,'2026-11-08 11:52:23'),('admin',75,'2026-11-08 11:53:48'),('admin',76,'2026-11-08 11:53:50'),('admin',77,'2026-11-08 12:06:55'),('admin',78,'2026-11-08 12:29:16'),('admin',79,'2026-11-08 13:26:14'),('citizen',129,'2026-11-13 20:35:55'),('admin',81,'2026-11-13 22:39:59'),('admin',84,'2026-11-13 22:54:39'),('admin',85,'2026-11-13 22:58:33'),('admin',86,'2026-11-13 22:59:27'),('admin',87,'2026-11-13 23:08:45'),('admin',88,'2026-11-13 23:09:21'),('admin',89,'2026-11-13 23:09:55'),('admin',90,'2026-11-13 23:16:31'),('admin',91,'2026-11-13 23:17:29'),('admin',92,'2026-11-13 23:17:59'),('admin',93,'2026-11-13 23:18:57'),('admin',94,'2026-11-13 23:19:39'),('admin',95,'2026-11-13 23:25:20'),('admin',96,'2026-11-13 23:26:03'),('admin',97,'2026-11-13 23:26:48'),('admin',98,'2026-11-13 23:32:14'),('admin',99,'2026-11-13 23:35:50'),('admin',100,'2026-11-13 23:51:40'),('admin',101,'2026-11-13 23:51:50'),('admin',102,'2026-11-13 23:52:59'),('admin',103,'2026-11-14 07:17:53'),('admin',104,'2026-11-14 07:19:46'),('admin',105,'2026-11-14 07:19:46'),('admin',106,'2026-11-14 08:51:45'),('admin',107,'2026-11-14 08:51:46'),('admin',108,'2026-11-14 08:59:46'),('admin',109,'2026-11-14 08:59:46'),('citizen',128,'2026-11-14 12:10:47'),('citizen',132,'2026-11-14 18:29:23'),('citizen',133,'2026-11-14 18:39:34'),('citizen',134,'2026-11-14 19:16:59'),('admin',0,'2026-11-14 21:05:16'),('citizen',23,'2026-11-14 21:52:50'),('admin',1,'2026-11-14 21:59:36');
/*!40000 ALTER TABLE `chat_presence` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `citizen_field_values`
--

DROP TABLE IF EXISTS `citizen_field_values`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `citizen_field_values` (
  `citizen_id` int(10) unsigned NOT NULL,
  `field_id` int(10) unsigned NOT NULL,
  `value` varchar(255) NOT NULL DEFAULT '',
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`citizen_id`,`field_id`),
  KEY `idx_cfv_field` (`field_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `citizen_field_values`
--

LOCK TABLES `citizen_field_values` WRITE;
/*!40000 ALTER TABLE `citizen_field_values` DISABLE KEYS */;
/*!40000 ALTER TABLE `citizen_field_values` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `citizen_notice_reads`
--

DROP TABLE IF EXISTS `citizen_notice_reads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `citizen_notice_reads` (
  `citizen_id` int(10) unsigned NOT NULL,
  `kind` varchar(10) NOT NULL,
  `ref_id` int(10) unsigned NOT NULL,
  `status` varchar(30) NOT NULL,
  `seen_at` datetime NOT NULL,
  PRIMARY KEY (`citizen_id`,`kind`,`ref_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `citizen_notice_reads`
--

LOCK TABLES `citizen_notice_reads` WRITE;
/*!40000 ALTER TABLE `citizen_notice_reads` DISABLE KEYS */;
INSERT INTO `citizen_notice_reads` VALUES (23,'bs',12,'disetujui','2026-11-13 23:51:15'),(23,'bs',14,'layak','2026-11-14 00:00:48'),(23,'kk',10,'approved','2026-11-05 11:42:58'),(23,'rm',1,'sent','2026-11-14 11:37:54'),(23,'sr',55,'ditolak','2026-11-13 23:42:35');
/*!40000 ALTER TABLE `citizen_notice_reads` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `citizen_resident_links`
--

DROP TABLE IF EXISTS `citizen_resident_links`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `citizen_resident_links` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `citizen_id` int(10) unsigned NOT NULL,
  `member_id` int(10) unsigned NOT NULL,
  `verified_at` datetime NOT NULL DEFAULT current_timestamp(),
  `verification_source` varchar(30) NOT NULL DEFAULT 'admin',
  `verified_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_link_citizen` (`citizen_id`),
  UNIQUE KEY `uq_link_member` (`member_id`),
  CONSTRAINT `fk_link_citizen` FOREIGN KEY (`citizen_id`) REFERENCES `citizens` (`id`),
  CONSTRAINT `fk_link_member` FOREIGN KEY (`member_id`) REFERENCES `kk_members` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `citizen_resident_links`
--

LOCK TABLES `citizen_resident_links` WRITE;
/*!40000 ALTER TABLE `citizen_resident_links` DISABLE KEYS */;
/*!40000 ALTER TABLE `citizen_resident_links` ENABLE KEYS */;
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
  `password_hash` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `address` varchar(255) DEFAULT NULL,
  `phone` varchar(25) DEFAULT NULL,
  `email` varchar(190) DEFAULT NULL,
  `full_name` varchar(140) DEFAULT NULL,
  `gender` varchar(10) DEFAULT 'female',
  `notes` text DEFAULT NULL,
  `notify_email` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=135 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `citizens`
--

LOCK TABLES `citizens` WRITE;
/*!40000 ALTER TABLE `citizens` DISABLE KEYS */;
INSERT INTO `citizens` VALUES (2,'chendy','Chendy','$2y$10$mhH3DHgOTZdPv.jYmhPxK.JoIsxjmsIySP5cBDabN.QGA93kOI3Ne',0,'2026-10-17 13:29:51',NULL,NULL,NULL,NULL,'female',NULL,0),(22,'chendy182','chendy','$2y$10$2FscB0L1QbS43GjyQoAEIOHV2SqbbsERCVZWt3CYj2Evc2BytTll2',0,'2026-10-24 20:29:24',NULL,NULL,NULL,NULL,'female',NULL,0),(23,'dewi','dewi sinta','$2y$10$vXqidTEG3LNTWfuRjaKFbOUdgly2mvduUG.hT4GOieAcU7WXfe6ha',1,'2026-10-28 21:03:51',NULL,NULL,NULL,NULL,'female',NULL,0),(128,'vita','vita','$2y$10$K0mJQl1xyGJcTYqQ4yPKBeecRdp62V4/pGSW5GcL9LbMi7hdGRQYm',1,'2026-11-08 12:54:30',NULL,NULL,NULL,'vita','female',NULL,0),(129,'andi','andi','$2y$10$zh3QijMwxounmmUaBOWLCeBFd1bomkaNAY42ZxNJgz5/zCeE2XX8S',1,'2026-11-13 20:35:04','sdsdsd','0873772829200','andi@gmail.com','andi','female','sdsd',0);
/*!40000 ALTER TABLE `citizens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `complaint_updates`
--

DROP TABLE IF EXISTS `complaint_updates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `complaint_updates` (
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `complaint_updates`
--

LOCK TABLES `complaint_updates` WRITE;
/*!40000 ALTER TABLE `complaint_updates` DISABLE KEYS */;
INSERT INTO `complaint_updates` VALUES (1,1,'citizen',23,NULL,'submitted','Pengaduan dikirim.',0,'2026-11-13 21:01:22'),(2,1,'admin',0,'submitted','in_progress','Prioritas: Tinggi\nPIC: admin\nERERERR',1,'2026-11-13 21:03:40'),(3,1,'admin',0,NULL,NULL,'44544545',1,'2026-11-13 21:05:09');
/*!40000 ALTER TABLE `complaint_updates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `complaints`
--

DROP TABLE IF EXISTS `complaints`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `complaints` (
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
  `is_anonymous` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_ticket` (`ticket_no`),
  KEY `idx_cmp_status` (`status`),
  KEY `idx_cmp_citizen` (`citizen_id`),
  KEY `idx_cmp_assigned` (`assigned_to`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `complaints`
--

LOCK TABLES `complaints` WRITE;
/*!40000 ALTER TABLE `complaints` DISABLE KEYS */;
INSERT INTO `complaints` VALUES (1,'ADU-2611-00001',23,'keamanan','sdsddffdfdfdfdfdfd','sdsdsd','dsdsdsddsdsdsdsfdsfdsfsd',NULL,'tinggi','in_progress',1,NULL,NULL,NULL,NULL,'2026-11-13 21:01:22','2026-11-13 21:03:40',0);
/*!40000 ALTER TABLE `complaints` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `custom_field_definitions`
--

DROP TABLE IF EXISTS `custom_field_definitions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `custom_field_definitions` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `custom_field_definitions`
--

LOCK TABLES `custom_field_definitions` WRITE;
/*!40000 ALTER TABLE `custom_field_definitions` DISABLE KEYS */;
/*!40000 ALTER TABLE `custom_field_definitions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `custom_roles`
--

DROP TABLE IF EXISTS `custom_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `custom_roles` (
  `role_key` varchar(40) NOT NULL,
  `tenant_id` int(10) unsigned NOT NULL,
  `label` varchar(80) NOT NULL,
  `description` varchar(255) NOT NULL DEFAULT '',
  `base_role` varchar(40) NOT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`role_key`),
  KEY `idx_custom_roles_tenant` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `custom_roles`
--

LOCK TABLES `custom_roles` WRITE;
/*!40000 ALTER TABLE `custom_roles` DISABLE KEYS */;
/*!40000 ALTER TABLE `custom_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `document_versions`
--

DROP TABLE IF EXISTS `document_versions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `document_versions` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `document_versions`
--

LOCK TABLES `document_versions` WRITE;
/*!40000 ALTER TABLE `document_versions` DISABLE KEYS */;
/*!40000 ALTER TABLE `document_versions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `documents`
--

DROP TABLE IF EXISTS `documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `documents` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `documents`
--

LOCK TABLES `documents` WRITE;
/*!40000 ALTER TABLE `documents` DISABLE KEYS */;
/*!40000 ALTER TABLE `documents` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `due_payments`
--

LOCK TABLES `due_payments` WRITE;
/*!40000 ALTER TABLE `due_payments` DISABLE KEYS */;
INSERT INTO `due_payments` VALUES (1,2,2,10000.00,'tunai',NULL,'verified','',2,'2026-10-17 20:28:48','2026-10-17 15:29:09',1),(2,15,23,50000.00,'transfer','f0248c81dcd1b216f1c20789f1a1a031.pdf','verified','nnn',5,'2026-11-13 21:18:41','2026-11-13 21:20:10',0),(3,17,129,50000.00,'tunai','03846d542b802291ed8785d786b40a5b.jpg','verified','fgfgfgf',6,'2026-11-13 21:22:03','2026-11-13 21:22:03',0),(4,16,128,50000.00,'tunai',NULL,'verified','grgfgrf',7,'2026-11-13 21:22:14','2026-11-13 21:22:14',0),(5,7,129,10000.00,'tunai',NULL,'verified','',8,'2026-11-13 21:22:30','2026-11-13 21:22:30',0);
/*!40000 ALTER TABLE `due_payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `due_reminders`
--

DROP TABLE IF EXISTS `due_reminders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `due_reminders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `due_id` int(10) unsigned NOT NULL,
  `citizen_id` int(10) unsigned NOT NULL,
  `message` varchar(500) NOT NULL,
  `sent_by` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_rem_citizen` (`citizen_id`,`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `due_reminders`
--

LOCK TABLES `due_reminders` WRITE;
/*!40000 ALTER TABLE `due_reminders` DISABLE KEYS */;
INSERT INTO `due_reminders` VALUES (1,10,23,'Tagihan Kebersihan periode 2026-11 masih tersisa Rp 100.000. Abaikan bila sudah membayar dan menunggu verifikasi.',1,'2026-11-14 11:34:25');
/*!40000 ALTER TABLE `due_reminders` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dues`
--

LOCK TABLES `dues` WRITE;
/*!40000 ALTER TABLE `dues` DISABLE KEYS */;
INSERT INTO `dues` VALUES (2,2,'2026-10','Iuran lingkungan','rutin',NULL,50000.00,50000.00,'','2026-10-17 20:29:09'),(3,2,'2026-11','Keamanan','rutin',1,10000.00,0.00,'sdsdsds','2026-11-13 21:15:31'),(4,22,'2026-11','Keamanan','rutin',1,10000.00,0.00,'sdsdsds','2026-11-13 21:15:31'),(5,23,'2026-11','Keamanan','rutin',1,10000.00,0.00,'sdsdsds','2026-11-13 21:15:31'),(6,128,'2026-11','Keamanan','rutin',1,10000.00,0.00,'sdsdsds','2026-11-13 21:15:31'),(7,129,'2026-11','Keamanan','rutin',1,10000.00,10000.00,'sdsdsds','2026-11-13 21:22:30'),(8,2,'2026-11','Kebersihan','rutin',2,100000.00,0.00,'keamanan','2026-11-13 21:16:09'),(9,22,'2026-11','Kebersihan','rutin',2,100000.00,0.00,'keamanan','2026-11-13 21:16:09'),(10,23,'2026-11','Kebersihan','rutin',2,100000.00,0.00,'keamanan','2026-11-13 21:16:09'),(11,128,'2026-11','Kebersihan','rutin',2,100000.00,0.00,'keamanan','2026-11-13 21:16:09'),(12,129,'2026-11','Kebersihan','rutin',2,100000.00,0.00,'keamanan','2026-11-13 21:16:09'),(13,2,'2026-11','Operasional','rutin',3,50000.00,0.00,'sddsdsds','2026-11-13 21:17:07'),(14,22,'2026-11','Operasional','rutin',3,50000.00,0.00,'sddsdsds','2026-11-13 21:17:07'),(15,23,'2026-11','Operasional','rutin',3,50000.00,50000.00,'sddsdsds','2026-11-13 21:20:10'),(16,128,'2026-11','Operasional','rutin',3,50000.00,50000.00,'sddsdsds','2026-11-13 21:22:14'),(17,129,'2026-11','Operasional','rutin',3,50000.00,50000.00,'sddsdsds','2026-11-13 21:22:03'),(18,23,'2026-11','Lain-lain','non_rutin',8,111.00,0.00,'ddd','2026-11-14 11:21:54'),(19,128,'2026-11','Lain-lain','non_rutin',8,111.00,0.00,'ddd','2026-11-14 11:21:54'),(20,129,'2026-11','Lain-lain','non_rutin',8,111.00,0.00,'ddd','2026-11-14 11:21:54'),(21,23,'2026-11','Sosial','sosial',9,1.00,0.00,'ffffff','2026-11-14 11:22:27');
/*!40000 ALTER TABLE `dues` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `goods_receipts`
--

DROP TABLE IF EXISTS `goods_receipts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `goods_receipts` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `goods_receipts`
--

LOCK TABLES `goods_receipts` WRITE;
/*!40000 ALTER TABLE `goods_receipts` DISABLE KEYS */;
/*!40000 ALTER TABLE `goods_receipts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `handover_sessions`
--

DROP TABLE IF EXISTS `handover_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `handover_sessions` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `handover_sessions`
--

LOCK TABLES `handover_sessions` WRITE;
/*!40000 ALTER TABLE `handover_sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `handover_sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `handover_signoffs`
--

DROP TABLE IF EXISTS `handover_signoffs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `handover_signoffs` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `handover_signoffs`
--

LOCK TABLES `handover_signoffs` WRITE;
/*!40000 ALTER TABLE `handover_signoffs` DISABLE KEYS */;
/*!40000 ALTER TABLE `handover_signoffs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `important_contacts`
--

DROP TABLE IF EXISTS `important_contacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `important_contacts` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `important_contacts`
--

LOCK TABLES `important_contacts` WRITE;
/*!40000 ALTER TABLE `important_contacts` DISABLE KEYS */;
/*!40000 ALTER TABLE `important_contacts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `incident_reports`
--

DROP TABLE IF EXISTS `incident_reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `incident_reports` (
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `incident_reports`
--

LOCK TABLES `incident_reports` WRITE;
/*!40000 ALTER TABLE `incident_reports` DISABLE KEYS */;
INSERT INTO `incident_reports` VALUES (1,NULL,'2026-11-13 21:39:00','dfd','pencurian','dfdfd','dfdf',0,NULL,'2026-11-13 21:39:22');
/*!40000 ALTER TABLE `incident_reports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kk_families`
--

DROP TABLE IF EXISTS `kk_families`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kk_families` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nomor_kk` varchar(16) NOT NULL,
  `owner_citizen_id` int(10) unsigned DEFAULT NULL,
  `fields_json` text NOT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp(),
  `source` varchar(20) NOT NULL DEFAULT 'pengajuan_warga',
  `verified_by` int(10) unsigned DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nomor_kk` (`nomor_kk`),
  UNIQUE KEY `owner_citizen_id` (`owner_citizen_id`),
  CONSTRAINT `fk_family_owner_citizen` FOREIGN KEY (`owner_citizen_id`) REFERENCES `citizens` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kk_families`
--

LOCK TABLES `kk_families` WRITE;
/*!40000 ALTER TABLE `kk_families` DISABLE KEYS */;
INSERT INTO `kk_families` VALUES (10,'3674041612200024',23,'{\"nomor_kk\":\"3674041612200024\",\"alamat\":\"xccxcx\",\"rt\":\"444\",\"rw\":\"009\",\"kelurahan\":\"serua indah\",\"kecamatan\":\"ade\",\"kota\":\"jakarta\",\"provinsi\":\"serua\",\"kode_pos\":\"15412\",\"fields_need_review\":[]}','2026-11-13 20:43:04','pengajuan_warga',NULL,NULL);
/*!40000 ALTER TABLE `kk_families` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kk_members`
--

DROP TABLE IF EXISTS `kk_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kk_members` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `family_id` int(10) unsigned NOT NULL,
  `nik` varchar(16) NOT NULL,
  `fields_json` text NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'aktif',
  `status_changed_at` datetime DEFAULT NULL,
  `domicile_status` varchar(20) DEFAULT NULL,
  `resident_since` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nik` (`nik`),
  KEY `family_id` (`family_id`),
  CONSTRAINT `kk_members_ibfk_1` FOREIGN KEY (`family_id`) REFERENCES `kk_families` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kk_members`
--

LOCK TABLES `kk_members` WRITE;
/*!40000 ALTER TABLE `kk_members` DISABLE KEYS */;
INSERT INTO `kk_members` VALUES (25,10,'3674062609890006','{\"nama_lengkap\":\"MUHAMMAD CHENDY SETIADI\",\"nik\":\"3674062609890006\",\"jenis_kelamin\":\"L\",\"tempat_lahir\":\"TANGERANG\",\"tanggal_lahir\":\"1989-09-26\",\"agama\":\"ISLAM DIPLOMA\",\"pendidikan\":\"IV\\/STRATA |\",\"pekerjaan\":\"KARYAWAN SWASTA\",\"golongan_darah\":\"TIDAK TAHU\",\"hubungan_keluarga\":\"Kepala Keluarga\",\"fields_need_review\":[]}','aktif',NULL,NULL,NULL),(26,10,'3674046502950009','{\"nama_lengkap\":\"DEWI SINTA SARI\",\"nik\":\"3674046502950009\",\"jenis_kelamin\":\"P\",\"tempat_lahir\":\"TANGERANG\",\"tanggal_lahir\":\"1995-02-25\",\"agama\":\"ISLAM\",\"pendidikan\":\"AKADEMI\\/DIPLOMA III\\/SARJANA MUDA\",\"pekerjaan\":\"KARYAWAN SWASTA\",\"golongan_darah\":\"TIDAK TAHU\",\"hubungan_keluarga\":\"Istri\",\"fields_need_review\":[]}','aktif',NULL,NULL,NULL),(27,10,'3674060410170002','{\"nama_lengkap\":\"EMMERIL KHAIRAN HAMIZAN\",\"nik\":\"3674060410170002\",\"jenis_kelamin\":\"L\",\"tempat_lahir\":\"TANGERANG SELATAN\",\"tanggal_lahir\":\"2017-10-04\",\"agama\":\"ISLAM TIDAK\\/BELUM\",\"pendidikan\":\"SEKOLAH\",\"pekerjaan\":\"BELUM\\/TIDAK BEKERJA\",\"golongan_darah\":\"TIDAK TAHU\",\"hubungan_keluarga\":\"Anak\",\"fields_need_review\":[]}','aktif',NULL,NULL,NULL),(28,10,'3674044611210002','{\"nama_lengkap\":\"shahia\",\"nik\":\"3674044611210002\",\"jenis_kelamin\":\"P\",\"tempat_lahir\":\"tangsel\",\"tanggal_lahir\":\"2026-11-06\",\"agama\":\"ISLAM TIDAK\\/BELUM\",\"pendidikan\":\"SEKOLAH\",\"pekerjaan\":\"BELUM\\/TIDAK BEKERJA\",\"golongan_darah\":\"TIDAK TAHU\",\"hubungan_keluarga\":\"Anak\",\"fields_need_review\":[]}','aktif',NULL,NULL,NULL),(29,10,'3674040407240004','{\"nama_lengkap\":\"MUHAMMAD ARVA KAUTSARRAZKY\",\"nik\":\"3674040407240004\",\"jenis_kelamin\":\"L\",\"tempat_lahir\":\"TANGERANG SELATAN\",\"tanggal_lahir\":\"2024-07-04\",\"agama\":\"ISLAM TIDAK\\/BELUM\",\"pendidikan\":\"SEKOLAH\",\"pekerjaan\":\"BELUM\\/TIDAK BEKERJA\",\"golongan_darah\":\"TIDAK TAHU\",\"hubungan_keluarga\":\"Anak\",\"fields_need_review\":[]}','aktif',NULL,NULL,NULL);
/*!40000 ALTER TABLE `kk_members` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kk_scan_requests`
--

DROP TABLE IF EXISTS `kk_scan_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kk_scan_requests` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `citizen_id` int(10) unsigned DEFAULT NULL,
  `status` enum('pending','approved','revision_requested','rejected','draft') NOT NULL DEFAULT 'pending',
  `proposed_data_json` mediumtext NOT NULL,
  `review_notes` text DEFAULT NULL,
  `reviewed_by` int(10) unsigned DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `source` varchar(20) NOT NULL DEFAULT 'warga',
  `created_by_admin` int(10) unsigned DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `active_citizen_key` int(10) unsigned GENERATED ALWAYS AS (if(`status` = 'pending',`citizen_id`,NULL)) STORED,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_kkreq_one_pending` (`active_citizen_key`),
  KEY `citizen_id` (`citizen_id`,`status`),
  CONSTRAINT `fk_kkreq_citizen` FOREIGN KEY (`citizen_id`) REFERENCES `citizens` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kk_scan_requests`
--

LOCK TABLES `kk_scan_requests` WRITE;
/*!40000 ALTER TABLE `kk_scan_requests` DISABLE KEYS */;
INSERT INTO `kk_scan_requests` VALUES (10,23,'approved','{\"keluarga\":{\"nomor_kk\":\"3674041612200024\",\"alamat\":\"JL SUKA MULYA IV NO. 17\",\"rt\":\"009\",\"rw\":\"008\",\"kelurahan\":\"SERUA INDAH\",\"kecamatan\":\"CIPUTAT\",\"kota\":\"KOTA TANGERANG SELATAN\",\"provinsi\":\"BANTEN\",\"kode_pos\":\"15414\",\"fields_need_review\":[]},\"anggota\":[{\"nama_lengkap\":\"MUHAMMAD CHENDY SETIADI\",\"nik\":\"3674062609890006\",\"jenis_kelamin\":\"L\",\"tempat_lahir\":\"TANGERANG\",\"tanggal_lahir\":\"1989-09-26\",\"agama\":\"ISLAM DIPLOMA\",\"pendidikan\":\"IV\\/STRATA |\",\"pekerjaan\":\"KARYAWAN SWASTA\",\"golongan_darah\":\"TIDAK TAHU\",\"hubungan_keluarga\":\"Kepala Keluarga\",\"fields_need_review\":[]},{\"nama_lengkap\":\"DEWI SINTA SARI\",\"nik\":\"3674046502950009\",\"jenis_kelamin\":\"P\",\"tempat_lahir\":\"TANGERANG\",\"tanggal_lahir\":\"1995-02-25\",\"agama\":\"ISLAM\",\"pendidikan\":\"AKADEMI\\/DIPLOMA III\\/SARJANA MUDA\",\"pekerjaan\":\"KARYAWAN SWASTA\",\"golongan_darah\":\"TIDAK TAHU\",\"hubungan_keluarga\":\"Istri\",\"fields_need_review\":[]},{\"nama_lengkap\":\"EMMERIL KHAIRAN HAMIZAN\",\"nik\":\"3674060410170002\",\"jenis_kelamin\":\"L\",\"tempat_lahir\":\"TANGERANG SELATAN\",\"tanggal_lahir\":\"2017-10-04\",\"agama\":\"ISLAM TIDAK\\/BELUM\",\"pendidikan\":\"SEKOLAH\",\"pekerjaan\":\"BELUM\\/TIDAK BEKERJA\",\"golongan_darah\":\"TIDAK TAHU\",\"hubungan_keluarga\":\"Anak\",\"fields_need_review\":[]},{\"nama_lengkap\":\"SHADLEEN HAZZEMA SHAHIA\",\"nik\":\"3674044611210002\",\"jenis_kelamin\":\"P\",\"tempat_lahir\":\"TANGERANG SELATAN\",\"tanggal_lahir\":\"2021-11-06\",\"agama\":\"ISLAM TIDAK\\/BELUM\",\"pendidikan\":\"SEKOLAH\",\"pekerjaan\":\"BELUM\\/TIDAK BEKERJA\",\"golongan_darah\":\"TIDAK TAHU\",\"hubungan_keluarga\":\"Anak\",\"fields_need_review\":[]},{\"nama_lengkap\":\"MUHAMMAD ARVA KAUTSARRAZKY\",\"nik\":\"3674040407240004\",\"jenis_kelamin\":\"L\",\"tempat_lahir\":\"TANGERANG SELATAN\",\"tanggal_lahir\":\"2024-07-04\",\"agama\":\"ISLAM TIDAK\\/BELUM\",\"pendidikan\":\"SEKOLAH\",\"pekerjaan\":\"BELUM\\/TIDAK BEKERJA\",\"golongan_darah\":\"TIDAK TAHU\",\"hubungan_keluarga\":\"Anak\",\"fields_need_review\":[]}],\"jumlah_anggota\":5}','lanjutkan',1,'2026-11-05 11:33:10','2026-11-05 10:37:42','warga',NULL,NULL,NULL),(30,22,'rejected','{\"keluarga\":{\"nomor_kk\":\"3674041612200024\",\"alamat\":\"JL SUKA MULYA IV NO. 17\",\"rt\":\"009\",\"rw\":\"008\",\"kelurahan\":\"SERUA INDAH\",\"kecamatan\":\"CIPUTAT\",\"kota\":\"KOTA TANGERANG SELATAN\",\"provinsi\":\"BANTEN\",\"kode_pos\":\"15414\",\"fields_need_review\":[]},\"anggota\":[{\"nama_lengkap\":\"MUHAMMAD CHENDY SETIADI\",\"nik\":\"3674062609890006\",\"jenis_kelamin\":\"L\",\"tempat_lahir\":\"TANGERANG\",\"tanggal_lahir\":\"1989-09-26\",\"agama\":\"ISLAM DIPLOMA\",\"pendidikan\":\"IV\\/STRATA |\",\"pekerjaan\":\"KARYAWAN SWASTA\",\"golongan_darah\":\"TIDAK TAHU\",\"hubungan_keluarga\":\"Suami\",\"fields_need_review\":[]},{\"nama_lengkap\":\"DEWI SINTA SARI\",\"nik\":\"3674046502950009\",\"jenis_kelamin\":\"P\",\"tempat_lahir\":\"TANGERANG\",\"tanggal_lahir\":\"1995-02-25\",\"agama\":\"ISLAM\",\"pendidikan\":\"AKADEMI\\/DIPLOMA III\\/SARJANA MUDA\",\"pekerjaan\":\"KARYAWAN SWASTA\",\"golongan_darah\":\"TIDAK TAHU\",\"hubungan_keluarga\":\"Kepala Keluarga\",\"fields_need_review\":[]},{\"nama_lengkap\":\"EMMERIL KHAIRAN HAMIZAN\",\"nik\":\"3674060410170002\",\"jenis_kelamin\":\"L\",\"tempat_lahir\":\"TANGERANG SELATAN\",\"tanggal_lahir\":\"2017-10-04\",\"agama\":\"ISLAM TIDAK\\/BELUM\",\"pendidikan\":\"SEKOLAH\",\"pekerjaan\":\"BELUM\\/TIDAK BEKERJA\",\"golongan_darah\":\"TIDAK TAHU\",\"hubungan_keluarga\":\"Anak\",\"fields_need_review\":[]},{\"nama_lengkap\":\"SHADLEEN HAZZEMA SHAHIA\",\"nik\":\"3674044611210002\",\"jenis_kelamin\":\"P\",\"tempat_lahir\":\"TANGERANG SELATAN\",\"tanggal_lahir\":\"2021-11-06\",\"agama\":\"ISLAM TIDAK\\/BELUM\",\"pendidikan\":\"SEKOLAH\",\"pekerjaan\":\"BELUM\\/TIDAK BEKERJA\",\"golongan_darah\":\"TIDAK TAHU\",\"hubungan_keluarga\":\"Anak\",\"fields_need_review\":[]},{\"nama_lengkap\":\"MUHAMMAD ARVA KAUTSARRAZKY\",\"nik\":\"3674040407240004\",\"jenis_kelamin\":\"L\",\"tempat_lahir\":\"TANGERANG SELATAN\",\"tanggal_lahir\":\"2024-07-04\",\"agama\":\"ISLAM TIDAK\\/BELUM\",\"pendidikan\":\"SEKOLAH\",\"pekerjaan\":\"BELUM\\/TIDAK BEKERJA\",\"golongan_darah\":\"TIDAK TAHU\",\"hubungan_keluarga\":\"Anak\",\"fields_need_review\":[]}],\"jumlah_anggota\":5}','double',1,'2026-11-08 08:32:23','2026-11-08 08:22:19','warga',NULL,NULL,NULL);
/*!40000 ALTER TABLE `kk_scan_requests` ENABLE KEYS */;
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
-- Table structure for table `notification_outbox`
--

DROP TABLE IF EXISTS `notification_outbox`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notification_outbox` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notification_outbox`
--

LOCK TABLES `notification_outbox` WRITE;
/*!40000 ALTER TABLE `notification_outbox` DISABLE KEYS */;
/*!40000 ALTER TABLE `notification_outbox` ENABLE KEYS */;
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
  `icon` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '??',
  `photo` varchar(255) DEFAULT NULL,
  `parent_id` int(10) unsigned DEFAULT NULL,
  `contact` varchar(30) NOT NULL DEFAULT '',
  `term_start` year(4) DEFAULT NULL,
  `term_end` year(4) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_official_parent` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `officials`
--

LOCK TABLES `officials` WRITE;
/*!40000 ALTER TABLE `officials` DISABLE KEYS */;
INSERT INTO `officials` VALUES (1,'Rohmat','Ketua RT','Bertanggung jawab atas pengelolaan dan pelayanan RT secara keseluruhan.','👑','uploads/pengurus/fc3bc310c6ad40695b3934f1017b2edd.png',NULL,'',2020,2026,1,1),(2,'Andi','Sekretaris','Mengelola pengajuan layanan warga dan memberikan informasi.','🪪','uploads/pengurus/1228ad19070a400db2c7615fe6c67d3b.webp',1,'',NULL,NULL,2,1),(3,'Sari','Admin Keuangan','Mengelola administrasi pembayaran dan keuangan.','💰',NULL,1,'',NULL,NULL,3,1),(4,'Budi','Admin Data / Mapping','Mengelola data warga dan pemetaan lokasi.','🗺️',NULL,1,'',NULL,NULL,4,1),(6,'Dani','Support / IT','Memberikan dukungan sistem, data, dan infrastruktur.','💻',NULL,1,'',NULL,NULL,6,1);
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
-- Table structure for table `patrol_schedules`
--

DROP TABLE IF EXISTS `patrol_schedules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `patrol_schedules` (
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `patrol_schedules`
--

LOCK TABLES `patrol_schedules` WRITE;
/*!40000 ALTER TABLE `patrol_schedules` DISABLE KEYS */;
INSERT INTO `patrol_schedules` VALUES (1,'2026-11-13','penuh','Pos Ronda','adib',NULL,'scheduled',NULL,NULL,'','',NULL,0,'2026-11-13 21:39:09',NULL),(2,'2026-11-13','shift_1','Pos Ronda','rrtrtrt',23,'hadir','2026-11-13 21:40:15','2026-11-13 21:40:16','','',NULL,0,'2026-11-13 21:40:06','2026-11-13 21:40:16');
/*!40000 ALTER TABLE `patrol_schedules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `platform_admins`
--

DROP TABLE IF EXISTS `platform_admins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `platform_admins` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `is_active` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `platform_admins`
--

LOCK TABLES `platform_admins` WRITE;
/*!40000 ALTER TABLE `platform_admins` DISABLE KEYS */;
/*!40000 ALTER TABLE `platform_admins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `platform_login_attempts`
--

DROP TABLE IF EXISTS `platform_login_attempts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `platform_login_attempts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ip_hash` char(64) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_attempt` (`ip_hash`,`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `platform_login_attempts`
--

LOCK TABLES `platform_login_attempts` WRITE;
/*!40000 ALTER TABLE `platform_login_attempts` DISABLE KEYS */;
INSERT INTO `platform_login_attempts` VALUES (4,'0bb9f6fad0e76ad9bfc280bcf51caeb239a00cd73172dee54d8f76364494519f','2026-11-13 22:37:42'),(6,'0bb9f6fad0e76ad9bfc280bcf51caeb239a00cd73172dee54d8f76364494519f','2026-11-13 23:15:04'),(8,'0bb9f6fad0e76ad9bfc280bcf51caeb239a00cd73172dee54d8f76364494519f','2026-11-13 23:15:06'),(10,'0bb9f6fad0e76ad9bfc280bcf51caeb239a00cd73172dee54d8f76364494519f','2026-11-13 23:15:28'),(12,'0bb9f6fad0e76ad9bfc280bcf51caeb239a00cd73172dee54d8f76364494519f','2026-11-14 06:35:59'),(16,'0bb9f6fad0e76ad9bfc280bcf51caeb239a00cd73172dee54d8f76364494519f','2026-11-14 20:31:36'),(14,'446f83d6b7f4141e3bbf0fbc7739de72de3acc29ff7c38a224bda442fcf4f190','2026-11-14 15:22:30'),(13,'aee3ef1fcab8f77b5908cb54d91e278267b337da9540b68fda5bcd3483f4274c','2026-11-14 15:22:30'),(2,'db951e644810a5fdc93c77392c4ff61486f7fc385670113a3a597385de42980f','2026-11-13 20:09:53');
/*!40000 ALTER TABLE `platform_login_attempts` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `posts`
--

LOCK TABLES `posts` WRITE;
/*!40000 ALTER TABLE `posts` DISABLE KEYS */;
INSERT INTO `posts` VALUES (2,'pengumuman','LOMBA 17 AGUSTUS – SEMARAK HUT KEMERDEKAAN RI DI SUKA MULYA IV','🇮🇩 **PENGUMUMAN LOMBA 17 AGUSTUS** 🇮🇩\r\n\r\nDalam rangka memeriahkan Hari Ulang Tahun Kemerdekaan Republik Indonesia, pengurus RT 009/RW 008 mengundang seluruh warga Suka Mulya IV untuk berpartisipasi dalam kegiatan **Lomba 17 Agustus**.\r\n\r\n**Lokasi Kegiatan:**\r\nLingkungan Suka Mulya IV, RT 009/RW 008, Kelurahan Serua Indah, Kota Tangerang Selatan.\r\n\r\n**Waktu Kegiatan:**\r\nTanggal: 17 Agustus (tahun menyesuaikan)\r\nPukul: 08.00 WIB – selesai (rencana)\r\n\r\n**Jenis Perlombaan:**\r\n1. Lomba makan kerupuk\r\n2. Lomba balap karung\r\n3. Lomba memasukkan paku ke botol\r\n4. Lomba membawa kelereng dengan sendok\r\n5. Lomba tarik tambang\r\n6. Lomba joget balon\r\n7. Lomba mewarnai untuk anak-anak\r\n8. Lomba keluarga dan pembagian hadiah\r\n\r\nKegiatan ini terbuka untuk anak-anak, remaja, dan orang tua di lingkungan RT 009/RW 008.\r\n\r\nMari kita meriahkan Hari Kemerdekaan dengan semangat kebersamaan, kekompakan, dan gotong royong.\r\n\r\n**Merdeka! Merdeka! Merdeka! 🇮🇩**\r\n\r\nHormat kami,\r\nPanitia HUT RI\r\nPengurus RT 009/RW 008 – Suka Mulya IV\r\n\r\n*Catatan: Jadwal dan daftar perlombaan merupakan contoh dan dapat disesuaikan oleh panitia.*',NULL,NULL,'','uploads/3561b6b57e566b88d5645e3d66db3626.png','published','2026-10-17 17:21:08'),(7,'galeri','Kerja Bakti Lingkungan','Warga bergotong royong membersihkan jalan, taman, dan area sekitar lingkungan RT. Kegiatan rutin ini menjaga lingkungan tetap bersih dan nyaman.',NULL,NULL,'Lingkungan RT 009/008','uploads/gallery-dummy/kerja-bakti-warga.png','published','2026-10-26 08:15:00'),(8,'galeri','Semarak Lomba Kemerdekaan','Anak-anak dan keluarga warga mengikuti perlombaan kemerdekaan dengan penuh semangat dan kebersamaan.',NULL,NULL,'Lapangan Warga RT 009/008','uploads/gallery-dummy/lomba-kemerdekaan.png','published','2026-08-17 10:30:00'),(9,'galeri','Kegiatan Posyandu Balita','Pelayanan kesehatan rutin bagi ibu dan balita bersama kader lingkungan untuk memantau tumbuh kembang anak.',NULL,NULL,'Balai Warga RT 009/008','uploads/gallery-dummy/posyandu-balita.png','published','2026-10-18 09:00:00'),(10,'galeri','Pengajian dan Silaturahmi Warga','Kegiatan pengajian rutin sekaligus mempererat silaturahmi dan kebersamaan antarwarga.',NULL,NULL,'Balai Warga RT 009/008','uploads/gallery-dummy/pengajian-warga.png','published','2026-10-12 19:30:00'),(11,'pengumuman','Rapat Koordinasi Warga Bulanan','Pengurus mengundang seluruh warga untuk mengikuti rapat koordinasi bulanan membahas keamanan, kebersihan, dan program lingkungan.',NULL,NULL,'Balai Warga RT 009/008','uploads/content-dummy/rapat-warga.png','published','2026-10-28 18:00:00'),(12,'pengumuman','Jadwal Fogging Lingkungan','Kegiatan fogging akan dilaksanakan sebagai langkah pencegahan demam berdarah. Warga diminta menjaga keamanan makanan dan hewan peliharaan.',NULL,NULL,'Lingkungan RT 009/008','uploads/content-dummy/fogging-lingkungan.png','published','2026-10-27 09:00:00'),(13,'pengumuman','Senam Sehat Bersama Warga','Ayo ikuti senam sehat bersama untuk menjaga kebugaran sekaligus mempererat kebersamaan warga.',NULL,NULL,'Lapangan Warga RT 009/008','uploads/content-dummy/senam-warga.png','published','2026-10-25 07:00:00'),(14,'agenda','Senam Pagi Bersama','Kegiatan senam pagi rutin untuk seluruh warga.','2026-11-01','07:00:00','Lapangan Warga RT 009/008','uploads/content-dummy/senam-warga.png','published','2026-10-28 10:00:00'),(15,'agenda','Rapat Koordinasi November','Pertemuan warga membahas program kerja, keamanan, kebersihan, dan pelayanan lingkungan.','2026-11-07','19:30:00','Balai Warga RT 009/008','uploads/content-dummy/rapat-warga.png','published','2026-10-28 10:10:00'),(16,'agenda','Bazar UMKM Warga','Bazar produk makanan, minuman, dan kerajinan hasil usaha warga lingkungan.','2026-11-15','08:00:00','Jalan Utama RT 009/008','uploads/content-dummy/bazar-warga.png','published','2026-10-28 10:20:00'),(17,'agenda','Kerja Bakti Akhir Bulan','Gotong royong membersihkan saluran air, taman, dan jalan lingkungan.','2026-11-29','07:00:00','Lingkungan RT 009/008','uploads/gallery-dummy/kerja-bakti-warga.png','published','2026-10-28 10:30:00'),(20,'galeri','andi','sdsdsd',NULL,NULL,'','uploads/d5cefe8a8ba5eace2711d70f25fd6570.jpg','published','2026-11-13 21:13:13');
/*!40000 ALTER TABLE `posts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `program_agendas`
--

DROP TABLE IF EXISTS `program_agendas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `program_agendas` (
  `program_id` int(10) unsigned NOT NULL,
  `post_id` int(10) unsigned NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`program_id`,`post_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `program_agendas`
--

LOCK TABLES `program_agendas` WRITE;
/*!40000 ALTER TABLE `program_agendas` DISABLE KEYS */;
/*!40000 ALTER TABLE `program_agendas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `program_milestones`
--

DROP TABLE IF EXISTS `program_milestones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `program_milestones` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `program_milestones`
--

LOCK TABLES `program_milestones` WRITE;
/*!40000 ALTER TABLE `program_milestones` DISABLE KEYS */;
/*!40000 ALTER TABLE `program_milestones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchase_items`
--

DROP TABLE IF EXISTS `purchase_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchase_items` (
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase_items`
--

LOCK TABLES `purchase_items` WRITE;
/*!40000 ALTER TABLE `purchase_items` DISABLE KEYS */;
INSERT INTO `purchase_items` VALUES (1,1,'dfdfdsf',1,'unit',3333.00,NULL,0,0,NULL);
/*!40000 ALTER TABLE `purchase_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchase_requests`
--

DROP TABLE IF EXISTS `purchase_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchase_requests` (
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase_requests`
--

LOCK TABLES `purchase_requests` WRITE;
/*!40000 ALTER TABLE `purchase_requests` DISABLE KEYS */;
INSERT INTO `purchase_requests` VALUES (1,'dfdfsd','dfdsfsd','fdsfsd',NULL,3333.00,NULL,'',1,'submitted',1,NULL,NULL,NULL,NULL,NULL,'2026-11-13 21:43:25','2026-11-13 21:43:27');
/*!40000 ALTER TABLE `purchase_requests` ENABLE KEYS */;
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
-- Table structure for table `resident_mutations`
--

DROP TABLE IF EXISTS `resident_mutations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `resident_mutations` (
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
  KEY `idx_mut_citizen` (`citizen_id`),
  KEY `fk_mutation_member` (`member_id`),
  CONSTRAINT `fk_mutation_citizen` FOREIGN KEY (`citizen_id`) REFERENCES `citizens` (`id`),
  CONSTRAINT `fk_mutation_family` FOREIGN KEY (`family_id`) REFERENCES `kk_families` (`id`),
  CONSTRAINT `fk_mutation_member` FOREIGN KEY (`member_id`) REFERENCES `kk_members` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `resident_mutations`
--

LOCK TABLES `resident_mutations` WRITE;
/*!40000 ALTER TABLE `resident_mutations` DISABLE KEYS */;
INSERT INTO `resident_mutations` VALUES (1,'ubah_alamat',10,NULL,NULL,0,'2026-11-13','{\"keluarga\":{\"alamat\":\"JL SUKA MULYA IV NO. 17\",\"rt\":\"009\",\"rw\":\"008\",\"kelurahan\":\"SERUA INDAH\",\"kecamatan\":\"CIPUTAT\",\"kota\":\"KOTA TANGERANG SELATAN\",\"provinsi\":\"BANTEN\",\"kode_pos\":\"15414\"}}','{\"alamat\":\"xccxcx\",\"rt\":\"444\",\"rw\":\"009\",\"kelurahan\":\"serua indah\",\"kecamatan\":\"ade\",\"kota\":\"jakarta\",\"provinsi\":\"serua\",\"kode_pos\":\"15412\"}','sdsdsdsdsd','approved',0,NULL,'2026-11-13 20:43:04','2026-11-13 20:43:04','2026-11-13 20:42:11','2026-11-13 20:43:04'),(6,'ubah_data',10,28,NULL,0,'2026-11-13','{\"status\":\"aktif\",\"domicile_status\":null,\"fields\":{\"nama_lengkap\":\"SHADLEEN HAZZEMA SHAHIA\",\"jenis_kelamin\":\"P\",\"tempat_lahir\":\"TANGERANG SELATAN\",\"tanggal_lahir\":\"2021-11-06\",\"agama\":\"ISLAM TIDAK\\/BELUM\",\"pendidikan\":\"SEKOLAH\",\"pekerjaan\":\"BELUM\\/TIDAK BEKERJA\",\"golongan_darah\":\"TIDAK TAHU\",\"hubungan_keluarga\":\"Anak\"}}','{\"nama_lengkap\":\"shahia\",\"tempat_lahir\":\"tangsel\",\"tanggal_lahir\":\"2026-11-06\"}','sdsdssdsd','approved',0,NULL,'2026-11-13 23:00:07','2026-11-13 23:00:07','2026-11-13 22:59:26','2026-11-13 23:00:07');
/*!40000 ALTER TABLE `resident_mutations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_menu_access`
--

DROP TABLE IF EXISTS `role_menu_access`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `role_menu_access` (
  `tenant_id` int(10) unsigned NOT NULL,
  `role` varchar(40) NOT NULL,
  `menu_path` varchar(120) NOT NULL,
  `allowed` tinyint(1) NOT NULL DEFAULT 1,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`tenant_id`,`role`,`menu_path`),
  KEY `idx_role_menu_role` (`tenant_id`,`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_menu_access`
--

LOCK TABLES `role_menu_access` WRITE;
/*!40000 ALTER TABLE `role_menu_access` DISABLE KEYS */;
/*!40000 ALTER TABLE `role_menu_access` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rt_family_residences`
--

DROP TABLE IF EXISTS `rt_family_residences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rt_family_residences` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` int(10) unsigned NOT NULL DEFAULT 1,
  `family_id` int(10) unsigned NOT NULL,
  `house_id` bigint(20) unsigned NOT NULL,
  `occupancy_relation` varchar(20) NOT NULL DEFAULT 'lainnya',
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `verification_status` varchar(20) NOT NULL DEFAULT 'pending',
  `verified_by` int(10) unsigned DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  `active_family_key` int(10) unsigned GENERATED ALWAYS AS (if(`end_date` is null,`family_id`,NULL)) STORED,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_res_one_active` (`tenant_id`,`active_family_key`),
  KEY `idx_res_family_active` (`tenant_id`,`family_id`,`end_date`),
  KEY `idx_res_house_active` (`tenant_id`,`house_id`,`end_date`),
  KEY `idx_res_verify` (`tenant_id`,`verification_status`),
  KEY `fk_residence_house` (`house_id`),
  KEY `fk_residence_family` (`family_id`),
  CONSTRAINT `fk_residence_family` FOREIGN KEY (`family_id`) REFERENCES `kk_families` (`id`),
  CONSTRAINT `fk_residence_house` FOREIGN KEY (`house_id`) REFERENCES `rt_houses` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rt_family_residences`
--

LOCK TABLES `rt_family_residences` WRITE;
/*!40000 ALTER TABLE `rt_family_residences` DISABLE KEYS */;
INSERT INTO `rt_family_residences` VALUES (9,1,10,9,'milik','2026-11-14',NULL,'verified',1,'2026-11-14 09:00:46',1,'2026-11-14 09:00:46',NULL,10);
/*!40000 ALTER TABLE `rt_family_residences` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rt_houses`
--

DROP TABLE IF EXISTS `rt_houses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rt_houses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` int(10) unsigned NOT NULL DEFAULT 1,
  `house_code` varchar(40) NOT NULL,
  `house_number` varchar(30) DEFAULT NULL,
  `block_name` varchar(100) NOT NULL DEFAULT '',
  `address_text` varchar(500) NOT NULL,
  `landmark` varchar(190) NOT NULL DEFAULT '',
  `building_type` varchar(30) NOT NULL DEFAULT 'rumah',
  `occupancy_status` varchar(20) NOT NULL DEFAULT 'dihuni',
  `notes` varchar(500) NOT NULL DEFAULT '',
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_house_code` (`tenant_id`,`house_code`),
  KEY `idx_house_address` (`tenant_id`,`house_number`,`block_name`),
  KEY `idx_house_status` (`tenant_id`,`occupancy_status`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rt_houses`
--

LOCK TABLES `rt_houses` WRITE;
/*!40000 ALTER TABLE `rt_houses` DISABLE KEYS */;
INSERT INTO `rt_houses` VALUES (9,1,'SUKAMULYA IV','17','','serua indah','','rumah','dihuni','',1,'2026-11-14 09:00:43','2026-11-14 09:00:43');
/*!40000 ALTER TABLE `rt_houses` ENABLE KEYS */;
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
INSERT INTO `service_request_details` VALUES (12,'muhammad chendy setiadi','jln sukamulya IV RT 09 RW 08 No 17','Surat Keterangan','{\"applicant_nik\":\"108093000064\",\"applicant_phone\":\"085697578958\",\"applicant_birth_place\":\"tangerang\",\"applicant_birth_date\":\"1989-09-26\",\"applicant_gender\":\"Laki-laki\",\"applicant_religion\":\"islam\",\"applicant_occupation\":\"karyawan swasta\",\"applicant_ktp_address\":\"jln sukamulya IV RT 09 RW 08 No 17\"}',NULL,NULL,NULL,NULL),(41,'muhammad chendy setiadi','jln sukamulya IV RT 09 RW 08 No 17','Surat Keterangan','{\"applicant_nik\":\"1234567891011111\",\"applicant_phone\":\"085697578958\",\"applicant_birth_place\":\"tangerang\",\"applicant_birth_date\":\"\",\"applicant_gender\":\"Laki-laki\",\"applicant_religion\":\"islam\",\"applicant_occupation\":\"karyawan swasta\",\"applicant_ktp_address\":\"jln sukamulya IV RT 09 RW 08 No 17\"}',NULL,'001/RT/Tangsel/2026','2026-10-17 14:32:29',1),(54,'muhammad chendy setiadi','jln sukamulya IV RT 09 RW 08 No 17','untuk sekolah','{\"applicant_nik\":\"1234567891011111\",\"applicant_phone\":\"085697578958\",\"applicant_birth_place\":\"tangerang\",\"applicant_birth_date\":\"1989-10-06\",\"applicant_gender\":\"Laki-laki\",\"applicant_religion\":\"islam\",\"applicant_occupation\":\"karyawan swasta\",\"applicant_ktp_address\":\"jln sukamulya IV RT 09 RW 08 No 17\",\"domicile_since\":\"2026-10-12\",\"residence_status\":\"tetap\",\"household_head\":\"chendy\"}',NULL,'111/rt/0/2026','2026-10-18 04:41:58',1),(55,'muhammad chendy setiadi','bumi pelita','','{\"applicant_nik\":\"1234567891234567\",\"applicant_phone\":\"\",\"applicant_birth_place\":\"tangerang selatan\",\"applicant_birth_date\":\"2026-10-24\",\"applicant_gender\":\"Laki-laki\",\"applicant_religion\":\"Islam\",\"applicant_occupation\":\"karyawan swasta\",\"applicant_ktp_address\":\"bumi pelita\",\"tujuan_instansi\":\"\",\"jenis_urusan\":\"\",\"nomor_kk\":\"\"}',NULL,'111/rt/0/2026',NULL,NULL);
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
) ENGINE=InnoDB AUTO_INCREMENT=78 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `service_requests`
--

LOCK TABLES `service_requests` WRITE;
/*!40000 ALTER TABLE `service_requests` DISABLE KEYS */;
INSERT INTO `service_requests` VALUES (12,23,'domisili','Surat Keterangan Domisili','Surat Keterangan','draft',NULL,'2026-10-17 19:03:04',NULL),(41,23,'domisili','Surat Keterangan Domisili','','disetujui','','2026-10-17 19:15:24','2026-10-17 14:32:29'),(54,23,'domisili','Surat Keterangan Domisili','','disetujui','','2026-10-18 09:41:07','2026-10-18 04:41:58'),(55,23,'surat_pengantar','Surat Pengantar','','ditolak','','2026-10-24 20:31:35','2026-11-13 20:57:00');
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
INSERT INTO `settings` VALUES ('about_image','uploads/79b62b38c1aa3e623dd847f2280bfc05.webp'),('about_text','RT 009/008 Suka Mulya IV adalah bagian dari lingkungan masyarakat yang menjunjung tinggi nilai gotong royong, kebersamaan, dan kepedulian antarwarga.'),('about_title','Tentang RT 009/008'),('address','Suka Mulya IV'),('agenda_icon','assets/mockup/icon-calendar-small.png'),('announcement_icon','uploads/96616f3e6e4839ea07579a6034ff02a3.png'),('brand_label','RT 009/008'),('brand_subtitle','Digital Warga Nusantara'),('citizen_hero_quote','AMAN DAN SEJAHTERA'),('complaint_anonymous','0'),('dashboard_favorites_t1_c128','[]'),('dashboard_favorites_t1_c23','[]'),('email','dwarganusantara@gmail.com'),('emergency_contact',''),('facebook_url',''),('female_residents','0'),('footer_banner_jpg','uploads/ff997dd0eb1fb7c6a92ce1677c6da5df.png'),('footer_text','┬® RT 09 / RW 08 Sukamulya.'),('footer_wave','assets/mockup/footer-wave.png'),('hero_accent','Suka Mulya IV'),('hero_image','assets/mockup/hero-neighborhood.png'),('hero_kicker','SELAMAT DATANG DI WEBSITE'),('hero_text','Bersama Membangun Lingkungan yang Aman, Nyaman, Rukun dan Sejahtera.'),('hero_title','RT 009/008'),('home_banner_jpg','uploads/fcfb10544bfccb7d153892cee6691ace.png'),('households','0'),('instagram_url',''),('letter_address','Kel. Suka Mulya, Kec. Serpong, Kota Tangerang Selatan'),('letter_district','Ciputat'),('letter_environment','Suka Mulya IV'),('letter_logo','11e4f869912d7a009a8dda94307fae74.webp'),('letter_postal','15414'),('letter_region','Tangerang Selatan'),('letter_rt_name','RUKUN TETANGGA 009 / RUKUN WARGA 008'),('letter_signature','1f6a72d73a092d1c04aea8213df7f398.png'),('letter_signer_name','rohmat'),('letter_signer_title','Ketua RT 009 / RW 008'),('letter_stamp','7edc5e9f78b15ffc9770d8137bcfc274.png'),('letter_village','Suka Mulya IV'),('logo','uploads/logo-dwn-transparan.png'),('logo_icon','uploads/logo-dwn-emblem-transparan.png'),('male_residents','0'),('map_url',''),('notify_email','0'),('ops_schema_version','rt16-6-integrated-residence'),('phone','085697578958'),('residents','0'),('site_name','DIGITAL WARGA NUSANTARA'),('slogan','Satu Warga, Satu Keluarga'),('subscription_demo_offers_v1','{\"basic\":1,\"pro\":2,\"enterprise\":3}'),('tagline','Website lingkungan RT Digital Warga Nusantara'),('theme_accent','#f0501a'),('theme_navy','#082654'),('ui_active_version','7'),('value1_icon','assets/mockup/icon-rukun.png'),('value1_text','Menjaga silaturahmi'),('value1_title','Rukun'),('value2_icon','assets/mockup/icon-bersih.png'),('value2_text','Lingkungan yang sehat'),('value2_title','Bersih'),('value3_icon','assets/mockup/icon-aman.png'),('value3_text','Nyaman untuk semua warga'),('value3_title','Aman'),('whatsapp',''),('youtube_url','');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings_history`
--

DROP TABLE IF EXISTS `settings_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings_history` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `snapshot_json` mediumtext NOT NULL,
  `note` varchar(255) NOT NULL DEFAULT '',
  `changed_by` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `changed_by_name` varchar(80) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings_history`
--

LOCK TABLES `settings_history` WRITE;
/*!40000 ALTER TABLE `settings_history` DISABLE KEYS */;
INSERT INTO `settings_history` VALUES (1,'{\"site_name\":\"RT 009\\/008 Suka Mulya IV\",\"tagline\":\"Website lingkungan RT 009\\/008 Suka Mulya IV\",\"theme_accent\":\"\",\"theme_navy\":\"\",\"complaint_anonymous\":\"\",\"notify_email\":\"\"}','Perubahan: site_name, tagline, theme_accent, theme_navy, complaint_anonymous, notify_email',1,'2026-11-07 21:30:52',NULL),(2,'{\"home_banner_jpg\":\"\",\"footer_banner_jpg\":\"\"}','Perubahan: home_banner_jpg, footer_banner_jpg',1,'2026-11-07 22:44:31',NULL),(3,'{\"footer_banner_jpg\":\"uploads\\/8593ddff16d872afa2b69354bfc1f2f7.png\"}','Perubahan: footer_banner_jpg',1,'2026-11-07 23:01:22',NULL),(4,'{\"brand_subtitle\":\"Suka Mulya IV\",\"citizen_hero_quote\":\"\",\"emergency_contact\":\"\"}','Perubahan: brand_subtitle, citizen_hero_quote, emergency_contact',1,'2026-11-13 21:54:44',NULL),(5,'{\"email\":\"\"}','Perubahan: email',1,'2026-11-13 21:56:57',NULL),(6,'{\"phone\":\"\"}','Perubahan: phone',1,'2026-11-13 21:57:25',NULL),(7,'{\"emergency_contact\":\"085697578958\"}','Perubahan: emergency_contact',1,'2026-11-13 21:57:39',NULL),(8,'{\"logo\":\"assets\\/mockup\\/logo.png\"}','Perubahan: logo',1,'2026-11-13 21:58:10',NULL),(9,'{\"logo\":\"uploads\\/c841d706c7aa17a2b64e9ca3f1717c6b.png\"}','Perubahan: logo',1,'2026-11-13 21:58:31',NULL),(10,'{\"site_name\":\"RT DIGITAL WARGA NUSANTARA\"}','Perubahan: site_name',1,'2026-11-13 21:58:50',NULL),(11,'{\"announcement_icon\":\"assets\\/mockup\\/icon-announcement.png\"}','Perubahan: announcement_icon',1,'2026-11-13 22:00:22',NULL),(12,'{\"about_image\":\"assets\\/mockup\\/hero-neighborhood.png\"}','Perubahan: about_image',1,'2026-11-13 22:01:49',NULL),(13,'{\"about_image\":\"uploads\\/05a481eac72729ed7ddc426e7c935f9d.jpeg\"}','Perubahan: about_image',1,'2026-11-13 22:03:00',NULL),(14,'{\"logo\":\"uploads\\/bc56c4ea08fbd99882a60a645acb4cff.jpeg\",\"logo_icon\":\"\"}','Ganti logo Digital Warga Nusantara',NULL,'2026-11-13 22:39:53',NULL),(15,'{\"logo\":\"uploads\\/logo-dwn.png\",\"logo_icon\":\"uploads\\/logo-dwn-emblem.png\"}','Logo DWN latar transparan',NULL,'2026-11-13 22:42:01',NULL);
/*!40000 ALTER TABLE `settings_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_movements`
--

DROP TABLE IF EXISTS `stock_movements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stock_movements` (
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_movements`
--

LOCK TABLES `stock_movements` WRITE;
/*!40000 ALTER TABLE `stock_movements` DISABLE KEYS */;
INSERT INTO `stock_movements` VALUES (1,1,'in',1,'manual',NULL,'Stok awal',1,'2026-11-13 21:41:41');
/*!40000 ALTER TABLE `stock_movements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subscription_audit_logs`
--

DROP TABLE IF EXISTS `subscription_audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subscription_audit_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `actor_id` int(10) unsigned NOT NULL,
  `tenant_id` int(10) unsigned DEFAULT NULL,
  `action` varchar(60) NOT NULL,
  `before_json` mediumtext DEFAULT NULL,
  `after_json` mediumtext DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_audit_tenant` (`tenant_id`,`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subscription_audit_logs`
--

LOCK TABLES `subscription_audit_logs` WRITE;
/*!40000 ALTER TABLE `subscription_audit_logs` DISABLE KEYS */;
INSERT INTO `subscription_audit_logs` VALUES (1,0,NULL,'demo_offers_seed',NULL,'{\"_actor_scope\":\"platform_owner\",\"demo\":true,\"plan_ids\":{\"basic\":1,\"pro\":2,\"enterprise\":3},\"note\":\"Demo package offers only; tenant licences, catalog classifications and data unchanged.\"}','2026-11-08 11:07:02'),(2,0,NULL,'plan_toggle','{\"id\":\"3\",\"name\":\"Enterprise\",\"price\":\"199000.00\",\"duration_days\":\"365\",\"is_active\":\"1\"}','{\"is_active\":0,\"_actor_scope\":\"platform_owner\"}','2026-11-08 11:16:33'),(3,0,1,'catalog','null','{\"feature_code\":\"menu:admin\\/citizens\",\"commercial_type\":\"PREMIUM\",\"description\":\"\",\"_actor_scope\":\"platform_owner\"}','2026-11-08 11:44:43'),(4,0,1,'catalog','{\"feature_code\":\"menu:admin\\/citizens\",\"commercial_type\":\"PREMIUM\",\"description\":\"\"}','{\"feature_code\":\"menu:admin\\/citizens\",\"commercial_type\":\"FREE\",\"description\":\"\",\"_actor_scope\":\"platform_owner\"}','2026-11-08 11:44:46'),(5,0,1,'catalog','{\"feature_code\":\"menu:admin\\/citizens\",\"commercial_type\":\"FREE\",\"description\":\"\"}','{\"feature_code\":\"menu:admin\\/citizens\",\"commercial_type\":\"PREMIUM\",\"description\":\"\",\"_actor_scope\":\"platform_owner\"}','2026-11-08 11:47:58'),(6,0,1,'catalog','{\"feature_code\":\"menu:admin\\/citizens\",\"commercial_type\":\"PREMIUM\",\"description\":\"\"}','{\"feature_code\":\"menu:admin\\/citizens\",\"commercial_type\":\"FREE\",\"description\":\"\",\"_actor_scope\":\"platform_owner\"}','2026-11-08 12:00:46'),(7,0,1,'catalog','null','{\"feature_code\":\"menu:admin\\/pengaturan\\/riwayat\",\"commercial_type\":\"PREMIUM\",\"description\":\"\",\"_actor_scope\":\"platform_owner\"}','2026-11-08 12:01:04'),(8,0,1,'catalog','null','{\"feature_code\":\"menu:admin\\/backup\",\"commercial_type\":\"PREMIUM\",\"description\":\"\",\"_actor_scope\":\"platform_owner\"}','2026-11-08 12:01:06'),(9,0,1,'catalog','null','{\"feature_code\":\"menu:admin\\/audit\",\"commercial_type\":\"PREMIUM\",\"description\":\"\",\"_actor_scope\":\"platform_owner\"}','2026-11-08 12:01:08'),(10,0,1,'catalog','null','{\"feature_code\":\"menu:admin\\/antrean-email\",\"commercial_type\":\"PREMIUM\",\"description\":\"\",\"_actor_scope\":\"platform_owner\"}','2026-11-08 12:01:13'),(11,0,1,'catalog','null','{\"feature_code\":\"menu:admin\\/field-tambahan\",\"commercial_type\":\"PREMIUM\",\"description\":\"\",\"_actor_scope\":\"platform_owner\"}','2026-11-08 12:01:17'),(12,0,1,'catalog','null','{\"feature_code\":\"menu:admin\\/quick_links\",\"commercial_type\":\"PREMIUM\",\"description\":\"\",\"_actor_scope\":\"platform_owner\"}','2026-11-08 12:01:22');
/*!40000 ALTER TABLE `subscription_audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subscription_catalog`
--

DROP TABLE IF EXISTS `subscription_catalog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subscription_catalog` (
  `feature_code` varchar(80) NOT NULL,
  `commercial_type` enum('FREE','PREMIUM','DISABLED') NOT NULL DEFAULT 'FREE',
  `description` varchar(500) NOT NULL DEFAULT '',
  PRIMARY KEY (`feature_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subscription_catalog`
--

LOCK TABLES `subscription_catalog` WRITE;
/*!40000 ALTER TABLE `subscription_catalog` DISABLE KEYS */;
INSERT INTO `subscription_catalog` VALUES ('menu:admin/antrean-email','PREMIUM',''),('menu:admin/audit','PREMIUM',''),('menu:admin/backup','PREMIUM',''),('menu:admin/citizens','FREE',''),('menu:admin/field-tambahan','PREMIUM',''),('menu:admin/pengaturan/riwayat','PREMIUM',''),('menu:admin/quick_links','PREMIUM','');
/*!40000 ALTER TABLE `subscription_catalog` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subscription_overrides`
--

DROP TABLE IF EXISTS `subscription_overrides`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subscription_overrides` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` int(10) unsigned NOT NULL,
  `feature_code` varchar(80) NOT NULL,
  `access_mode` enum('GRANT','DENY') NOT NULL,
  `starts_at` datetime NOT NULL,
  `ends_at` datetime NOT NULL,
  `reason` varchar(500) NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_override` (`tenant_id`,`feature_code`,`starts_at`,`ends_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subscription_overrides`
--

LOCK TABLES `subscription_overrides` WRITE;
/*!40000 ALTER TABLE `subscription_overrides` DISABLE KEYS */;
/*!40000 ALTER TABLE `subscription_overrides` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subscription_plan_details`
--

DROP TABLE IF EXISTS `subscription_plan_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subscription_plan_details` (
  `plan_id` int(10) unsigned NOT NULL,
  `description` varchar(300) NOT NULL DEFAULT '',
  `benefits_json` text NOT NULL,
  `theme` varchar(12) NOT NULL DEFAULT 'orange',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `version` int(10) unsigned NOT NULL DEFAULT 1,
  PRIMARY KEY (`plan_id`),
  CONSTRAINT `subscription_plan_details_ibfk_1` FOREIGN KEY (`plan_id`) REFERENCES `subscription_plans` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subscription_plan_details`
--

LOCK TABLES `subscription_plan_details` WRITE;
/*!40000 ALTER TABLE `subscription_plan_details` DISABLE KEYS */;
INSERT INTO `subscription_plan_details` VALUES (1,'Fitur dasar portal warga. Data contoh paket; akses mengikuti pengaturan dan hak pengguna.','[\"Dashboard\",\"Data Warga\"]','green',0,1),(2,'Semua fitur Basic, ditambah:','[\"Laporan & Ekspor sesuai hak akses\"]','orange',1,1),(3,'Semua fitur Pro, ditambah fitur operasional lingkungan:','[\"Manajemen Pengurus sesuai hak akses\",\"Audit Aktivitas\",\"Backup Data manual\",\"Prioritas Dukungan — contoh manfaat paket\"]','violet',2,1);
/*!40000 ALTER TABLE `subscription_plan_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subscription_plan_features`
--

DROP TABLE IF EXISTS `subscription_plan_features`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subscription_plan_features` (
  `plan_id` int(10) unsigned NOT NULL,
  `feature_code` varchar(80) NOT NULL,
  PRIMARY KEY (`plan_id`,`feature_code`),
  CONSTRAINT `subscription_plan_features_ibfk_1` FOREIGN KEY (`plan_id`) REFERENCES `subscription_plans` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subscription_plan_features`
--

LOCK TABLES `subscription_plan_features` WRITE;
/*!40000 ALTER TABLE `subscription_plan_features` DISABLE KEYS */;
INSERT INTO `subscription_plan_features` VALUES (1,'announcements'),(1,'events'),(1,'letters'),(2,'announcements'),(2,'cash_report'),(2,'events'),(2,'family_card'),(2,'letters'),(2,'resident_mutation'),(2,'resident_profile'),(2,'social_aid'),(3,'activity_notes'),(3,'announcements'),(3,'cash_reconciliation'),(3,'cash_report'),(3,'complaints'),(3,'contacts'),(3,'documents'),(3,'dues'),(3,'dues_arrears'),(3,'events'),(3,'family_card'),(3,'gallery'),(3,'inventory'),(3,'letters'),(3,'live_chat'),(3,'patrol'),(3,'purchasing'),(3,'resident_mutation'),(3,'resident_profile'),(3,'social_aid'),(3,'work_program');
/*!40000 ALTER TABLE `subscription_plan_features` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subscription_plans`
--

DROP TABLE IF EXISTS `subscription_plans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subscription_plans` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `duration_days` int(10) unsigned NOT NULL,
  `is_active` tinyint(4) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subscription_plans`
--

LOCK TABLES `subscription_plans` WRITE;
/*!40000 ALTER TABLE `subscription_plans` DISABLE KEYS */;
INSERT INTO `subscription_plans` VALUES (1,'Gratis (Basic)',0.00,0,1),(2,'Pro',99000.00,365,1),(3,'Enterprise',199000.00,365,0);
/*!40000 ALTER TABLE `subscription_plans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tenant_feature_audit`
--

DROP TABLE IF EXISTS `tenant_feature_audit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tenant_feature_audit` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` int(10) unsigned NOT NULL,
  `feature_id` int(10) unsigned NOT NULL,
  `actor_user_id` int(10) unsigned NOT NULL,
  `old_value` longtext DEFAULT NULL,
  `new_value` longtext NOT NULL,
  `reason` varchar(500) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_tenant_time` (`tenant_id`,`created_at`),
  KEY `idx_tfa_feature` (`feature_id`)
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tenant_feature_audit`
--

LOCK TABLES `tenant_feature_audit` WRITE;
/*!40000 ALTER TABLE `tenant_feature_audit` DISABLE KEYS */;
INSERT INTO `tenant_feature_audit` VALUES (60,1,19,1,'{\"enabled\":1,\"visible_to_residents\":0,\"visible_to_staff\":1}','{\"enabled\":0,\"visible_to_residents\":0,\"visible_to_staff\":1}','Menu disembunyikan atas permintaan pengurus (via Claude Code). Data tidak dihapus.','2026-11-14 12:16:43'),(61,1,20,1,'{\"enabled\":1,\"visible_to_residents\":0,\"visible_to_staff\":1}','{\"enabled\":0,\"visible_to_residents\":0,\"visible_to_staff\":1}','Menu disembunyikan atas permintaan pengurus (via Claude Code). Data tidak dihapus.','2026-11-14 12:16:43');
/*!40000 ALTER TABLE `tenant_feature_audit` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tenant_features`
--

DROP TABLE IF EXISTS `tenant_features`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tenant_features` (
  `tenant_id` int(10) unsigned NOT NULL,
  `feature_id` int(10) unsigned NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT 0,
  `visible_to_residents` tinyint(1) NOT NULL DEFAULT 1,
  `visible_to_staff` tinyint(1) NOT NULL DEFAULT 1,
  `updated_by` int(10) unsigned DEFAULT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`tenant_id`,`feature_id`),
  KEY `idx_tf_feature` (`feature_id`),
  CONSTRAINT `fk_tenant_feature` FOREIGN KEY (`feature_id`) REFERENCES `app_features` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tenant_features`
--

LOCK TABLES `tenant_features` WRITE;
/*!40000 ALTER TABLE `tenant_features` DISABLE KEYS */;
INSERT INTO `tenant_features` VALUES (1,1,1,1,1,NULL,'2026-11-07 22:18:48'),(1,2,1,0,1,NULL,'2026-11-07 22:18:48'),(1,3,1,1,1,NULL,'2026-11-07 22:18:48'),(1,4,1,0,1,NULL,'2026-11-07 22:18:48'),(1,5,1,1,1,NULL,'2026-11-07 22:18:48'),(1,6,1,1,1,NULL,'2026-11-07 22:18:48'),(1,7,1,1,1,NULL,'2026-11-07 22:18:48'),(1,8,1,1,1,NULL,'2026-11-07 22:18:48'),(1,9,1,0,1,NULL,'2026-11-07 22:18:48'),(1,10,1,1,1,NULL,'2026-11-07 22:18:48'),(1,11,1,0,1,NULL,'2026-11-07 22:18:48'),(1,12,1,1,1,NULL,'2026-11-07 22:18:48'),(1,13,1,1,1,NULL,'2026-11-07 22:18:48'),(1,14,1,1,1,NULL,'2026-11-07 22:18:48'),(1,15,1,1,1,NULL,'2026-11-07 22:18:48'),(1,16,1,1,1,NULL,'2026-11-07 22:18:48'),(1,17,1,1,1,NULL,'2026-11-14 12:12:33'),(1,18,1,0,1,NULL,'2026-11-07 22:18:48'),(1,19,0,0,1,1,'2026-11-14 12:16:57'),(1,20,0,0,1,1,'2026-11-14 12:16:57'),(1,21,1,0,1,NULL,'2026-11-07 22:18:48');
/*!40000 ALTER TABLE `tenant_features` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tenant_subscriptions`
--

DROP TABLE IF EXISTS `tenant_subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tenant_subscriptions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` int(10) unsigned NOT NULL,
  `plan_id` int(10) unsigned NOT NULL,
  `status` enum('ACTIVE','TRIAL','EXPIRED','SUSPENDED') NOT NULL,
  `starts_at` datetime NOT NULL,
  `ends_at` datetime NOT NULL,
  `reason` varchar(500) NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`,`id`),
  KEY `plan_id` (`plan_id`),
  CONSTRAINT `tenant_subscriptions_ibfk_1` FOREIGN KEY (`plan_id`) REFERENCES `subscription_plans` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tenant_subscriptions`
--

LOCK TABLES `tenant_subscriptions` WRITE;
/*!40000 ALTER TABLE `tenant_subscriptions` DISABLE KEYS */;
/*!40000 ALTER TABLE `tenant_subscriptions` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transactions`
--

LOCK TABLES `transactions` WRITE;
/*!40000 ALTER TABLE `transactions` DISABLE KEYS */;
INSERT INTO `transactions` VALUES (2,'2026-10-17','masuk','Iuran lingkungan','Iuran rutin Iuran lingkungan 2026-10',10000.00,'2026-10','','','verified',NULL,1,1,'2026-10-17 15:29:09',NULL,'2026-10-17 20:29:09'),(3,'2026-10-31','keluar','Kebersihan','sampah',10000.00,'2026-10','','19966c24877af515ab70a0f1051e0c1d.png','verified',NULL,1,1,'2026-10-31 15:01:41',NULL,'2026-10-31 15:01:41'),(4,'2026-10-31','masuk','Iuran keamanan','fdfd',1.00,'2026-10','','','verified',NULL,1,1,'2026-10-31 09:06:42',NULL,'2026-10-31 15:06:42'),(5,'2026-11-13','masuk','Operasional','Iuran rutin Operasional 2026-11',50000.00,'2026-11','','f0248c81dcd1b216f1c20789f1a1a031.pdf','verified',NULL,0,0,'2026-11-13 21:20:10',NULL,'2026-11-13 21:20:10'),(6,'2026-11-13','masuk','Operasional','Iuran rutin Operasional 2026-11',50000.00,'2026-11','','03846d542b802291ed8785d786b40a5b.jpg','verified',NULL,0,0,'2026-11-13 21:22:03',NULL,'2026-11-13 21:22:03'),(7,'2026-11-13','masuk','Operasional','Iuran rutin Operasional 2026-11',50000.00,'2026-11','','','verified',NULL,0,0,'2026-11-13 21:22:14',NULL,'2026-11-13 21:22:14'),(8,'2026-11-13','masuk','Keamanan','Iuran rutin Keamanan 2026-11',10000.00,'2026-11','','','verified',NULL,0,0,'2026-11-13 21:22:30',NULL,'2026-11-13 21:22:30'),(9,'2026-11-14','masuk','Iuran keamanan','testing',1000000.00,'2026-11','csr','a6c8414984264350ea29b1890de93b0f.webp','verified',NULL,1,1,'2026-11-14 11:46:58',NULL,'2026-11-14 11:46:58'),(10,'2026-11-14','masuk','Iuran keamanan','test',1000000.00,'2026-11','tes','','verified',NULL,0,0,'2026-11-14 21:39:00',NULL,'2026-11-14 21:39:00'),(11,'2026-11-14','keluar','Kebersihan','dfdfd',10000000.00,'2026-11','','','verified',NULL,0,0,'2026-11-14 21:39:32',NULL,'2026-11-14 21:39:32');
/*!40000 ALTER TABLE `transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ui_config_drafts`
--

DROP TABLE IF EXISTS `ui_config_drafts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ui_config_drafts` (
  `owner_key` varchar(100) NOT NULL,
  `config_json` mediumtext NOT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`owner_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ui_config_drafts`
--

LOCK TABLES `ui_config_drafts` WRITE;
/*!40000 ALTER TABLE `ui_config_drafts` DISABLE KEYS */;
/*!40000 ALTER TABLE `ui_config_drafts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ui_config_versions`
--

DROP TABLE IF EXISTS `ui_config_versions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ui_config_versions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `config_json` mediumtext NOT NULL,
  `action` varchar(30) NOT NULL DEFAULT 'apply',
  `note` varchar(255) NOT NULL DEFAULT '',
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_by_name` varchar(80) NOT NULL DEFAULT '',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ui_config_versions`
--

LOCK TABLES `ui_config_versions` WRITE;
/*!40000 ALTER TABLE `ui_config_versions` DISABLE KEYS */;
INSERT INTO `ui_config_versions` VALUES (1,'{\"schemaVersion\":1,\"themeId\":\"navy\",\"sidebar\":{\"variant\":\"standard\"},\"components\":{\"radius\":\"medium\",\"fontSize\":\"medium\",\"density\":\"comfortable\",\"inputHeight\":\"standard\"},\"header\":{\"search\":true,\"notifications\":true,\"banner\":true},\"dashboard\":{\"order\":[\"hero\",\"stats\",\"stats_chart\",\"dues\",\"agenda\",\"letters\",\"operations\"],\"hidden\":[],\"stats_hidden\":[]},\"colors\":{\"primary\":\"#172554\",\"secondary\":\"#334155\",\"accent\":\"#3B82F6\",\"background\":\"#0B1220\",\"surface\":\"#111C33\",\"text\":\"#F8FAFC\"},\"brand\":{\"logo\":\"\"}}','apply','Tema Dark Navy',NULL,'Admin Pusat (superadmin)','2026-11-08 14:33:59'),(2,'{\"schemaVersion\":1,\"themeId\":\"original-orange\",\"sidebar\":{\"variant\":\"standard\"},\"components\":{\"radius\":\"medium\",\"fontSize\":\"medium\",\"density\":\"comfortable\",\"inputHeight\":\"standard\"},\"header\":{\"search\":true,\"notifications\":true,\"banner\":true},\"dashboard\":{\"order\":[\"hero\",\"stats\",\"stats_chart\",\"dues\",\"agenda\",\"letters\",\"operations\"],\"hidden\":[],\"stats_hidden\":[]},\"colors\":{\"primary\":\"#F97316\",\"secondary\":\"#FFEDD5\",\"accent\":\"#F97316\",\"background\":\"#F8FAFC\",\"surface\":\"#FFFFFF\",\"text\":\"#102A56\"},\"brand\":{\"logo\":\"\"}}','reset','Reset ke Default (Oranye Harmoni)',NULL,'Admin Pusat (superadmin)','2026-11-08 14:34:14'),(3,'{\"schemaVersion\":1,\"themeId\":\"teal\",\"sidebar\":{\"variant\":\"standard\"},\"components\":{\"radius\":\"medium\",\"fontSize\":\"medium\",\"density\":\"comfortable\",\"inputHeight\":\"standard\"},\"header\":{\"search\":true,\"notifications\":true,\"banner\":true},\"dashboard\":{\"order\":[\"hero\",\"stats\",\"stats_chart\",\"dues\",\"agenda\",\"letters\",\"operations\"],\"hidden\":[],\"stats_hidden\":[]},\"colors\":{\"primary\":\"#0D9488\",\"secondary\":\"#CCFBF1\",\"accent\":\"#0D9488\",\"background\":\"#F8FAFC\",\"surface\":\"#FFFFFF\",\"text\":\"#102A56\"},\"brand\":{\"logo\":\"\"}}','apply','Tema Teal Fresh',NULL,'Admin Pusat (superadmin)','2026-11-08 14:35:07'),(4,'{\"schemaVersion\":1,\"themeId\":\"original-orange\",\"sidebar\":{\"variant\":\"standard\"},\"components\":{\"radius\":\"medium\",\"fontSize\":\"medium\",\"density\":\"comfortable\",\"inputHeight\":\"standard\"},\"header\":{\"search\":true,\"notifications\":true,\"banner\":true},\"dashboard\":{\"order\":[\"hero\",\"stats\",\"stats_chart\",\"dues\",\"agenda\",\"letters\",\"operations\"],\"hidden\":[],\"stats_hidden\":[]},\"colors\":{\"primary\":\"#F97316\",\"secondary\":\"#FFEDD5\",\"accent\":\"#F97316\",\"background\":\"#F8FAFC\",\"surface\":\"#FFFFFF\",\"text\":\"#102A56\"},\"brand\":{\"logo\":\"\"}}','reset','Reset ke Default (Oranye Harmoni)',NULL,'Admin Pusat (superadmin)','2026-11-08 14:35:34'),(5,'{\"schemaVersion\":1,\"themeId\":\"navy\",\"sidebar\":{\"variant\":\"standard\"},\"components\":{\"radius\":\"medium\",\"fontSize\":\"medium\",\"density\":\"comfortable\",\"inputHeight\":\"standard\"},\"header\":{\"search\":true,\"notifications\":true,\"banner\":true},\"dashboard\":{\"order\":[\"hero\",\"stats\",\"stats_chart\",\"dues\",\"agenda\",\"letters\",\"operations\"],\"hidden\":[],\"stats_hidden\":[]},\"colors\":{\"primary\":\"#172554\",\"secondary\":\"#334155\",\"accent\":\"#3B82F6\",\"background\":\"#0B1220\",\"surface\":\"#111C33\",\"text\":\"#F8FAFC\"},\"brand\":{\"logo\":\"\"}}','apply','Tema Dark Navy',1,'admin','2026-11-13 22:11:06'),(6,'{\"schemaVersion\":1,\"themeId\":\"emerald\",\"sidebar\":{\"variant\":\"standard\"},\"components\":{\"radius\":\"medium\",\"fontSize\":\"medium\",\"density\":\"comfortable\",\"inputHeight\":\"standard\"},\"header\":{\"search\":true,\"notifications\":true,\"banner\":true},\"dashboard\":{\"order\":[\"hero\",\"stats\",\"stats_chart\",\"dues\",\"agenda\",\"letters\",\"operations\"],\"hidden\":[],\"stats_hidden\":[]},\"colors\":{\"primary\":\"#16A34A\",\"secondary\":\"#DCFCE7\",\"accent\":\"#16A34A\",\"background\":\"#F8FAFC\",\"surface\":\"#FFFFFF\",\"text\":\"#102A56\"},\"brand\":{\"logo\":\"\"}}','apply','Tema Hijau Emerald',1,'admin','2026-11-13 22:11:16'),(7,'{\"schemaVersion\":1,\"themeId\":\"original-orange\",\"sidebar\":{\"variant\":\"standard\"},\"components\":{\"radius\":\"medium\",\"fontSize\":\"medium\",\"density\":\"comfortable\",\"inputHeight\":\"standard\"},\"header\":{\"search\":true,\"notifications\":true,\"banner\":true},\"dashboard\":{\"order\":[\"hero\",\"stats\",\"stats_chart\",\"dues\",\"agenda\",\"letters\",\"operations\"],\"hidden\":[],\"stats_hidden\":[]},\"colors\":{\"primary\":\"#F97316\",\"secondary\":\"#FFEDD5\",\"accent\":\"#F97316\",\"background\":\"#F8FAFC\",\"surface\":\"#FFFFFF\",\"text\":\"#102A56\"},\"brand\":{\"logo\":\"\"}}','apply','Tema Oranye Harmoni',1,'admin','2026-11-13 22:11:37');
/*!40000 ALTER TABLE `ui_config_versions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `work_programs`
--

DROP TABLE IF EXISTS `work_programs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `work_programs` (
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `work_programs`
--

LOCK TABLES `work_programs` WRITE;
/*!40000 ALTER TABLE `work_programs` DISABLE KEYS */;
INSERT INTO `work_programs` VALUES (1,'sendi','lingkungan','sdsd','2026-11-13','2026-11-13','bersih','sdsds','sendi',100000.00,50000.00,'selesai',0,'sddss','eeeeeeee',0,'2026-11-13 21:34:17',0,'2026-11-13 21:33:43','2026-11-14 19:48:46',NULL);
/*!40000 ALTER TABLE `work_programs` ENABLE KEYS */;
UNLOCK TABLES;

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

-- Dump completed on 2026-11-14 21:59:38
