/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.20-13.0.2-MariaDB, for Linux (x86_64)
--
-- Host: 127.0.0.1    Database: desa_digital_test
-- ------------------------------------------------------
-- Server version	13.0.2-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `action` varchar(120) NOT NULL,
  `entity_type` varchar(120) NOT NULL,
  `entity_id` bigint(20) unsigned DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `ip_address` varbinary(16) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_audit_entity` (`entity_type`,`entity_id`,`created_at`),
  KEY `idx_audit_user_time` (`user_id`,`created_at`),
  CONSTRAINT `audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=709 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
INSERT INTO `audit_logs` VALUES
(1,NULL,'authentication.login_succeeded','user',3,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 04:17:42'),
(2,NULL,'authentication.logout','user',3,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 04:17:53'),
(3,NULL,'authentication.login_succeeded','user',3,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 04:18:02'),
(4,NULL,'authentication.logout','user',3,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 04:18:11'),
(5,NULL,'authentication.login_succeeded','user',3,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 04:21:22'),
(6,NULL,'authentication.logout','user',3,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 04:21:26'),
(7,NULL,'account.bootstrap_super_admin_created','user',4,'[]',0x7F000001,'Symfony','2026-11-08 04:26:43'),
(8,4,'authentication.login_succeeded','user',4,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 04:27:29'),
(9,NULL,'account.email_verified','user',4,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.134.0 Chrome/148.0.7778.280 Electron/42.8.1 Safari/537.36','2026-11-08 04:30:05'),
(10,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 04:30:25'),
(11,4,'authentication.login_succeeded','user',4,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.134.0 Chrome/148.0.7778.280 Electron/42.8.1 Safari/537.36','2026-11-08 04:31:00'),
(12,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.134.0 Chrome/148.0.7778.280 Electron/42.8.1 Safari/537.36','2026-11-08 04:31:04'),
(13,NULL,'authentication.login_failed','authentication',NULL,'{\"reason\":\"invalid_credentials_or_inactive\"}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 04:31:24'),
(14,4,'authentication.login_succeeded','user',4,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 04:31:36'),
(15,4,'audit.logs_viewed','audit_log_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 04:31:43'),
(16,4,'authorization.region_assignments_viewed','region_officer_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 04:31:52'),
(17,4,'account.pending_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 04:32:02'),
(18,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 04:32:05'),
(19,4,'account.status_changed','user',3,'{\"from\":\"disabled\",\"to\":\"disabled\"}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 04:32:16'),
(20,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 04:32:16'),
(21,4,'authentication.logout','user',4,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 04:32:45'),
(22,4,'authentication.login_succeeded','user',4,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 04:33:09'),
(23,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 04:36:15'),
(24,4,'authorization.region_assignments_viewed','region_officer_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 04:36:30'),
(25,4,'authentication.logout','user',4,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 04:36:34'),
(26,NULL,'account.registration_submitted','user',5,'{\"status\":\"pending\"}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 04:37:17'),
(27,NULL,'authentication.login_failed','authentication',NULL,'{\"reason\":\"invalid_credentials_or_inactive\"}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 04:39:31'),
(28,NULL,'authentication.login_failed','authentication',NULL,'{\"reason\":\"invalid_credentials_or_inactive\"}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 04:39:47'),
(29,NULL,'authentication.login_failed','authentication',NULL,'{\"reason\":\"invalid_credentials_or_inactive\"}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 04:39:58'),
(30,NULL,'account.registration_submitted','user',6,'{\"status\":\"pending\"}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 04:40:44'),
(31,NULL,'authentication.logout','user',6,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 04:42:14'),
(32,NULL,'authentication.login_failed','authentication',NULL,'{\"reason\":\"invalid_credentials_or_inactive\"}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 04:42:24'),
(33,4,'authentication.login_succeeded','user',4,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 04:42:44'),
(34,4,'account.pending_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 04:43:02'),
(35,4,'audit.logs_viewed','audit_log_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 04:44:02'),
(36,4,'account.pending_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 04:44:10'),
(37,4,'account.pending_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 04:45:38'),
(38,4,'account.pending_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 04:45:39'),
(39,4,'account.pending_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 04:45:39'),
(40,4,'account.pending_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.134.0 Chrome/148.0.7778.280 Electron/42.8.1 Safari/537.36','2026-11-08 04:46:46'),
(41,4,'authentication.logout','user',4,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.134.0 Chrome/148.0.7778.280 Electron/42.8.1 Safari/537.36','2026-11-08 04:46:49'),
(42,NULL,'account.email_verified','user',6,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.134.0 Chrome/148.0.7778.280 Electron/42.8.1 Safari/537.36','2026-11-08 04:49:20'),
(43,4,'account.pending_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 04:49:29'),
(44,4,'account.activated','user',6,'{\"status\":\"active\"}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 04:49:31'),
(45,4,'account.pending_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 04:49:31'),
(46,4,'authentication.logout','user',4,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 04:55:13'),
(47,NULL,'authentication.login_failed','authentication',NULL,'{\"reason\":\"invalid_credentials_or_inactive\"}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 04:55:21'),
(48,NULL,'authentication.login_succeeded','user',6,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 04:55:36'),
(49,4,'authentication.login_succeeded','user',4,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 21:10:17'),
(50,4,'authentication.logout','user',4,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 21:10:27'),
(51,4,'authentication.login_succeeded','user',4,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 21:10:40'),
(52,4,'account.pending_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 21:10:50'),
(53,4,'account.pending_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 21:10:58'),
(54,4,'account.pending_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 21:11:10'),
(55,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 21:11:11'),
(56,4,'master_data.region_catalog_viewed','region_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 21:11:12'),
(57,4,'authorization.region_assignments_viewed','region_officer_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 21:11:13'),
(58,4,'audit.logs_viewed','audit_log_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 21:11:14'),
(59,4,'authorization.region_assignments_viewed','region_officer_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 21:12:35'),
(60,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 21:12:49'),
(61,4,'account.status_changed','user',5,'{\"from\":\"pending\",\"to\":\"disabled\"}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 21:13:55'),
(62,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 21:13:56'),
(63,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 21:14:33'),
(64,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 21:15:12'),
(65,4,'account.pending_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 21:15:18'),
(66,4,'master_data.region_catalog_viewed','region_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 21:15:23'),
(67,4,'authorization.region_assignments_viewed','region_officer_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 21:15:24'),
(68,4,'authorization.region_assignments_viewed','region_officer_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 21:17:26'),
(69,4,'account.pending_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 21:35:49'),
(70,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 21:35:49'),
(71,4,'master_data.region_catalog_viewed','region_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 21:35:51'),
(72,4,'authorization.region_assignments_viewed','region_officer_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 21:35:52'),
(73,4,'audit.logs_viewed','audit_log_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 21:35:53'),
(74,4,'audit.logs_viewed','audit_log_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 21:36:24'),
(75,4,'audit.logs_viewed','audit_log_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 21:37:55'),
(76,4,'audit.logs_viewed','audit_log_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 21:38:52'),
(77,4,'audit.logs_viewed','audit_log_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 21:46:12'),
(78,4,'audit.logs_viewed','audit_log_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 21:51:13'),
(79,4,'audit.logs_viewed','audit_log_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 21:53:08'),
(80,4,'audit.logs_viewed','audit_log_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 21:53:23'),
(81,4,'audit.logs_viewed','audit_log_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 21:55:24'),
(82,4,'audit.logs_viewed','audit_log_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 21:57:14'),
(83,4,'audit.logs_viewed','audit_log_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 21:58:03'),
(84,4,'account.pending_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 21:58:05'),
(85,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 21:58:06'),
(86,4,'account.pending_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 21:58:08'),
(87,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 21:58:17'),
(88,4,'master_data.region_catalog_viewed','region_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 21:58:18'),
(89,4,'authorization.region_assignments_viewed','region_officer_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 21:58:19'),
(90,4,'master_data.region_catalog_viewed','region_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 21:58:20'),
(91,4,'authorization.region_assignments_viewed','region_officer_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 21:58:26'),
(92,4,'audit.logs_viewed','audit_log_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 21:58:27'),
(93,4,'authorization.region_assignments_viewed','region_officer_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 21:58:32'),
(94,4,'account.pending_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 22:04:41'),
(95,4,'account.pending_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 22:04:43'),
(96,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 22:04:44'),
(97,4,'account.pending_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 22:04:46'),
(98,4,'authorization.region_assignments_viewed','region_officer_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 22:04:47'),
(99,4,'master_data.region_catalog_viewed','region_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 22:04:48'),
(100,4,'authorization.region_assignments_viewed','region_officer_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 22:04:49'),
(101,4,'master_data.region_catalog_viewed','region_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 22:04:50'),
(102,4,'authentication.logout','user',4,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 22:04:59'),
(103,NULL,'authentication.login_succeeded','user',6,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 22:05:14'),
(104,NULL,'authentication.logout','user',6,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 22:06:14'),
(105,4,'authentication.login_succeeded','user',4,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 22:06:27'),
(106,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 22:06:31'),
(107,4,'account.pending_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 22:06:32'),
(108,4,'master_data.region_catalog_viewed','region_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 22:06:34'),
(109,4,'master_data.village_created','village',1,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 22:06:55'),
(110,4,'master_data.region_catalog_viewed','region_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 22:06:55'),
(111,4,'master_data.region_created','region',1,'{\"region_type\":\"rw\"}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 22:07:50'),
(112,4,'master_data.region_catalog_viewed','region_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 22:07:50'),
(113,4,'master_data.region_created','region',2,'{\"region_type\":\"rt\",\"parent_id\":1}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 22:08:10'),
(114,4,'master_data.region_catalog_viewed','region_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 22:08:10'),
(115,4,'authorization.region_assignments_viewed','region_officer_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 22:08:15'),
(116,4,'authentication.logout','user',4,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 22:08:27'),
(117,NULL,'authentication.login_succeeded','user',6,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 22:08:41'),
(118,NULL,'resident.profile_created','resident',1,'{\"region_verified\":false}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 22:09:25'),
(119,NULL,'resident.profile_viewed','resident',1,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 22:09:25'),
(120,NULL,'resident.profile_viewed','resident',1,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 22:09:39'),
(121,NULL,'authentication.logout','user',6,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 22:09:51'),
(122,NULL,'authentication.login_failed','authentication',NULL,'{\"reason\":\"invalid_credentials_or_inactive\"}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 22:10:03'),
(123,4,'authentication.login_succeeded','user',4,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 22:10:15'),
(124,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 22:10:42'),
(125,4,'master_data.region_catalog_viewed','region_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 22:10:48'),
(126,4,'authorization.region_assignments_viewed','region_officer_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 22:10:53'),
(127,4,'audit.logs_viewed','audit_log_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 22:10:54'),
(128,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 22:10:59'),
(129,4,'account.pending_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 22:11:00'),
(130,4,'authorization.region_assignments_viewed','region_officer_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 22:11:08'),
(131,NULL,'authentication.login_succeeded','user',6,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 22:14:06'),
(132,NULL,'resident.profile_viewed','resident',1,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 22:14:14'),
(133,NULL,'resident.profile_updated','resident',1,'{\"region_verified\":false}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 22:15:03'),
(134,NULL,'resident.profile_viewed','resident',1,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 22:15:03'),
(135,NULL,'resident.profile_viewed','resident',1,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 22:15:12'),
(136,NULL,'resident.profile_viewed','resident',1,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 22:15:17'),
(137,NULL,'resident.profile_viewed','resident',1,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 22:15:20'),
(138,NULL,'resident.profile_viewed','resident',1,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 22:15:21'),
(139,NULL,'resident.profile_viewed','resident',1,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 22:15:21'),
(140,NULL,'resident.profile_viewed','resident',1,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 22:15:22'),
(141,NULL,'resident.profile_viewed','resident',1,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 22:15:24'),
(142,NULL,'resident.profile_updated','resident',1,'{\"region_verified\":false}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 22:15:28'),
(143,NULL,'resident.profile_viewed','resident',1,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 22:15:28'),
(144,NULL,'resident.profile_viewed','resident',1,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 22:15:39'),
(145,NULL,'resident.profile_updated','resident',1,'{\"region_verified\":false}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 22:15:52'),
(146,NULL,'resident.profile_viewed','resident',1,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 22:15:52'),
(147,NULL,'account.registration_submitted','user',7,'{\"status\":\"pending\"}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 22:49:11'),
(148,NULL,'account.email_verified','user',7,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.134.0 Chrome/148.0.7778.280 Electron/42.8.1 Safari/537.36','2026-11-08 22:50:58'),
(149,NULL,'authentication.logout','user',7,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 22:51:13'),
(150,4,'account.pending_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 22:51:21'),
(151,4,'account.activated','user',7,'{\"status\":\"active\"}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 22:51:22'),
(152,4,'account.pending_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 22:51:22'),
(153,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 22:51:24'),
(154,4,'authorization.roles_changed','user',7,'{\"role_ids\":[7,5]}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 22:51:32'),
(155,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 22:51:32'),
(156,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 22:51:54'),
(157,NULL,'authentication.login_succeeded','user',7,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 22:52:18'),
(158,4,'authorization.region_assignments_viewed','region_officer_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 22:53:08'),
(159,4,'authorization.region_assigned','region_officer',1,'{\"target_user_id\":7,\"region_id\":2}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 22:53:17'),
(160,4,'authorization.region_assignments_viewed','region_officer_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 22:53:17'),
(161,4,'authorization.region_assignments_viewed','region_officer_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 22:56:53'),
(162,4,'authorization.region_assignments_viewed','region_officer_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:01:20'),
(163,4,'authorization.region_assignments_viewed','region_officer_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:01:39'),
(164,4,'authorization.region_assignments_viewed','region_officer_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:01:40'),
(165,4,'authorization.region_assignments_viewed','region_officer_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:02:35'),
(166,4,'authorization.region_assignments_viewed','region_officer_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:02:42'),
(167,4,'authorization.region_assignments_viewed','region_officer_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:02:58'),
(168,4,'authorization.region_assignments_viewed','region_officer_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:03:26'),
(169,4,'authorization.region_assignments_viewed','region_officer_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:04:09'),
(170,4,'authorization.region_assignments_viewed','region_officer_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:04:34'),
(171,NULL,'resident.profile_created','resident',2,'{\"region_verified\":false}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:05:28'),
(172,NULL,'resident.profile_viewed','resident',2,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:05:28'),
(173,NULL,'resident.profile_updated','resident',2,'{\"region_verified\":false}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:05:34'),
(174,NULL,'resident.profile_viewed','resident',2,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:05:34'),
(175,NULL,'authentication.logout','user',7,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:05:44'),
(176,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:06:55'),
(177,4,'authentication.logout','user',4,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:07:13'),
(178,4,'authentication.login_succeeded','user',4,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:07:21'),
(179,NULL,'account.registration_submitted','user',8,'{\"status\":\"pending\"}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:08:58'),
(180,NULL,'account.email_verified','user',8,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.134.0 Chrome/148.0.7778.280 Electron/42.8.1 Safari/537.36','2026-11-08 23:09:17'),
(181,NULL,'authentication.logout','user',8,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:09:27'),
(182,NULL,'authentication.login_failed','authentication',NULL,'{\"reason\":\"invalid_credentials_or_inactive\"}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:09:39'),
(183,4,'account.pending_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:09:47'),
(184,4,'account.activated','user',8,'{\"status\":\"active\"}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:09:48'),
(185,4,'account.pending_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:09:48'),
(186,4,'account.pending_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:10:35'),
(187,NULL,'authentication.login_succeeded','user',8,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:10:46'),
(188,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:10:58'),
(189,4,'authorization.roles_changed','user',8,'{\"role_ids\":[7,6]}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:11:04'),
(190,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:11:04'),
(191,4,'account.status_changed','user',8,'{\"from\":\"active\",\"to\":\"active\"}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:11:07'),
(192,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:11:07'),
(193,4,'authorization.roles_changed','user',8,'{\"role_ids\":[7,6]}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:11:08'),
(194,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:11:08'),
(195,4,'account.pending_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:11:18'),
(196,4,'master_data.region_catalog_viewed','region_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:11:19'),
(197,4,'authorization.region_assignments_viewed','region_officer_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:11:23'),
(198,4,'authorization.region_assignments_viewed','region_officer_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:13:28'),
(199,4,'authorization.region_assignments_viewed','region_officer_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:22:50'),
(200,4,'authorization.region_assignments_viewed','region_officer_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:23:27'),
(201,4,'authorization.region_assignments_viewed','region_officer_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:23:58'),
(202,4,'authorization.region_assignments_viewed','region_officer_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:26:21'),
(203,4,'authorization.region_assignments_viewed','region_officer_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:28:31'),
(204,4,'authorization.region_assignments_viewed','region_officer_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:29:18'),
(205,4,'account.pending_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:29:48'),
(206,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:29:50'),
(207,4,'master_data.region_catalog_viewed','region_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:29:54'),
(208,4,'authorization.region_assignments_viewed','region_officer_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:30:33'),
(209,4,'authorization.region_assignments_viewed','region_officer_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:30:53'),
(210,4,'authorization.region_assignments_viewed','region_officer_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:31:25'),
(211,4,'authorization.region_assignments_viewed','region_officer_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:31:31'),
(212,4,'authorization.region_assignments_viewed','region_officer_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:31:31'),
(213,4,'authorization.region_assignments_viewed','region_officer_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:31:31'),
(214,4,'authorization.region_assignments_viewed','region_officer_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:31:31'),
(215,4,'authorization.region_assignments_viewed','region_officer_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:31:31'),
(216,4,'authorization.region_assignments_viewed','region_officer_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:31:32'),
(217,4,'authorization.region_assignments_viewed','region_officer_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:31:32'),
(218,4,'authorization.region_assignments_viewed','region_officer_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:31:32'),
(219,4,'authorization.region_assigned','region_officer',2,'{\"target_user_id\":8,\"region_id\":1}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:32:03'),
(220,4,'authorization.region_assignments_viewed','region_officer_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:32:03'),
(221,4,'account.pending_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:32:09'),
(222,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:32:10'),
(223,4,'authorization.region_assignments_viewed','region_officer_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:33:00'),
(224,4,'master_data.region_catalog_viewed','region_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:33:00'),
(225,4,'authorization.region_assignments_viewed','region_officer_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:33:10'),
(226,4,'master_data.region_catalog_viewed','region_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:33:12'),
(227,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:33:18'),
(228,4,'account.pending_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:33:19'),
(229,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:33:20'),
(230,4,'master_data.region_catalog_viewed','region_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:33:23'),
(231,4,'master_data.region_catalog_viewed','region_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:36:05'),
(232,4,'master_data.region_catalog_viewed','region_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:36:32'),
(233,4,'master_data.region_catalog_viewed','region_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:36:57'),
(234,4,'master_data.region_catalog_viewed','region_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:39:29'),
(235,4,'master_data.region_catalog_viewed','region_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:40:46'),
(236,4,'master_data.region_catalog_viewed','region_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:42:30'),
(237,4,'master_data.region_catalog_viewed','region_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:42:48'),
(238,4,'master_data.region_catalog_viewed','region_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:44:08'),
(239,NULL,'authentication.logout','user',8,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:45:38'),
(240,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:45:52'),
(241,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:45:57'),
(242,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:46:25'),
(243,NULL,'account.registration_submitted','user',9,'{\"status\":\"pending\"}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:46:56'),
(244,NULL,'account.email_verified','user',9,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.134.0 Chrome/148.0.7778.280 Electron/42.8.1 Safari/537.36','2026-11-08 23:47:34'),
(245,4,'account.pending_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:47:49'),
(246,4,'account.activated','user',9,'{\"status\":\"active\"}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:47:50'),
(247,4,'account.pending_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:47:50'),
(248,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:48:09'),
(249,4,'authorization.roles_changed','user',9,'{\"role_ids\":[7,4]}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:48:12'),
(250,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:48:12'),
(251,NULL,'master_data.region_catalog_viewed','region_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:48:21'),
(252,NULL,'resident.region_verification_list_viewed','resident_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:48:26'),
(253,NULL,'resident.region_verified','resident',1,'{\"region_id\":2}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:48:29'),
(254,NULL,'resident.region_verification_list_viewed','resident_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:48:29'),
(255,NULL,'resident.region_verified','resident',2,'{\"region_id\":2}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:48:30'),
(256,NULL,'resident.region_verification_list_viewed','resident_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:48:30'),
(257,NULL,'master_data.region_catalog_viewed','region_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:49:20'),
(258,NULL,'resident.region_verification_list_viewed','resident_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:49:21'),
(259,4,'authorization.roles_changed','user',9,'{\"role_ids\":[4]}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:49:30'),
(260,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:49:30'),
(261,NULL,'resident.region_verification_list_viewed','resident_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:49:39'),
(262,NULL,'master_data.region_catalog_viewed','region_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:49:45'),
(263,NULL,'resident.region_verification_list_viewed','resident_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:49:46'),
(264,NULL,'master_data.region_catalog_viewed','region_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:49:47'),
(265,NULL,'resident.region_verification_list_viewed','resident_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:49:48'),
(266,NULL,'resident.region_verification_list_viewed','resident_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:49:57'),
(267,NULL,'authentication.logout','user',9,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:50:06'),
(268,NULL,'authentication.login_succeeded','user',6,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:50:18'),
(269,NULL,'resident.profile_viewed','resident',1,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:50:28'),
(270,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:50:48'),
(271,4,'master_data.region_catalog_viewed','region_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:50:50'),
(272,4,'master_data.region_catalog_viewed','region_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:51:01'),
(273,4,'master_data.region_catalog_viewed','region_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:53:23'),
(274,4,'master_data.region_catalog_viewed','region_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:54:01'),
(275,4,'master_data.region_catalog_viewed','region_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:54:42'),
(276,4,'master_data.region_catalog_viewed','region_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:55:55'),
(277,4,'master_data.village_settings_viewed','village_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:55:57'),
(278,4,'master_data.village_settings_viewed','village_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:56:04'),
(279,4,'account.pending_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:56:16'),
(280,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:56:17'),
(281,4,'account.pending_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:56:25'),
(282,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:56:27'),
(283,NULL,'resident.profile_viewed','resident',1,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:56:44'),
(284,NULL,'resident.profile_viewed','resident',1,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:56:55'),
(285,4,'account.pending_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:57:03'),
(286,4,'master_data.region_catalog_viewed','region_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:57:04'),
(287,4,'master_data.village_settings_viewed','village_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:57:05'),
(288,4,'master_data.village_settings_viewed','village_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:57:10'),
(289,4,'master_data.region_catalog_viewed','region_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:57:21'),
(290,4,'authorization.region_assignments_viewed','region_officer_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:57:25'),
(291,4,'authorization.region_revoked','region_officer',1,'{\"target_user_id\":7,\"region_id\":2}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:57:30'),
(292,4,'authorization.region_assignments_viewed','region_officer_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:57:30'),
(293,4,'authorization.region_revoked','region_officer',2,'{\"target_user_id\":8,\"region_id\":1}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:57:33'),
(294,4,'authorization.region_assignments_viewed','region_officer_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:57:33'),
(295,4,'master_data.region_catalog_viewed','region_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:57:34'),
(296,4,'master_data.village_settings_viewed','village_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:57:35'),
(297,4,'master_data.village_settings_viewed','village_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:57:36'),
(298,4,'master_data.region_catalog_viewed','region_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:58:07'),
(299,4,'master_data.village_settings_viewed','village_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:58:18'),
(300,NULL,'resident.profile_viewed','resident',1,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:58:28'),
(301,NULL,'resident.profile_viewed','resident',1,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:58:35'),
(302,4,'authorization.region_assignments_viewed','region_officer_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:58:43'),
(303,4,'master_data.village_settings_viewed','village_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:58:56'),
(304,4,'account.pending_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:59:11'),
(305,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-08 23:59:12'),
(306,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:01:09'),
(307,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:01:43'),
(308,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:05:31'),
(309,4,'account.deleted','user',5,'{\"target_user_id\":5}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:05:35'),
(310,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:05:35'),
(311,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:05:37'),
(312,4,'account.deleted','user',3,'{\"target_user_id\":3}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:05:43'),
(313,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:05:43'),
(314,4,'master_data.region_catalog_viewed','region_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:07:12'),
(315,4,'master_data.village_settings_viewed','village_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:07:13'),
(316,4,'authorization.region_assignments_viewed','region_officer_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:07:14'),
(317,4,'master_data.region_catalog_viewed','region_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:07:15'),
(318,4,'master_data.village_settings_viewed','village_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:07:19'),
(319,4,'authorization.region_assignments_viewed','region_officer_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:07:20'),
(320,4,'account.pending_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:07:22'),
(321,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:07:23'),
(322,4,'authorization.roles_changed','user',9,'{\"role_ids\":[7]}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:07:31'),
(323,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:07:31'),
(324,4,'account.deleted','user',9,'{\"target_user_id\":9}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:07:37'),
(325,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:07:37'),
(326,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:07:39'),
(327,4,'authorization.roles_changed','user',8,'{\"role_ids\":[7]}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:07:45'),
(328,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:07:45'),
(329,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:07:48'),
(330,4,'master_data.region_catalog_viewed','region_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:07:50'),
(331,4,'master_data.village_settings_viewed','village_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:07:51'),
(332,4,'authorization.region_assignments_viewed','region_officer_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:07:52'),
(333,4,'authorization.region_assignments_viewed','region_officer_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:07:54'),
(334,4,'master_data.village_settings_viewed','village_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:08:12'),
(335,4,'master_data.region_catalog_viewed','region_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:08:13'),
(336,4,'master_data.village_settings_viewed','village_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:08:19'),
(337,4,'authorization.region_assignments_viewed','region_officer_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:08:21'),
(338,4,'account.pending_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:08:25'),
(339,4,'master_data.region_catalog_viewed','region_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:08:26'),
(340,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:08:27'),
(341,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:08:30'),
(342,4,'account.status_changed','user',8,'{\"from\":\"active\",\"to\":\"disabled\"}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:08:41'),
(343,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:08:41'),
(344,4,'account.status_changed','user',7,'{\"from\":\"active\",\"to\":\"disabled\"}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:08:50'),
(345,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:08:50'),
(346,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:08:53'),
(347,4,'authorization.roles_changed','user',8,'{\"role_ids\":[7]}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:09:00'),
(348,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:09:00'),
(349,4,'account.status_changed','user',8,'{\"from\":\"disabled\",\"to\":\"disabled\"}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:09:03'),
(350,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:09:03'),
(351,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:09:05'),
(352,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:09:09'),
(353,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:09:11'),
(354,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:09:11'),
(355,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:09:12'),
(356,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:09:12'),
(357,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:09:12'),
(358,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:09:22'),
(359,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:12:37'),
(360,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:12:58'),
(361,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:13:02'),
(362,4,'authorization.region_assignments_viewed','region_officer_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:13:26'),
(363,4,'master_data.region_catalog_viewed','region_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:13:29'),
(364,4,'master_data.village_settings_viewed','village_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:13:30'),
(365,4,'account.pending_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:13:31'),
(366,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:13:32'),
(367,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:13:35'),
(368,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:15:16'),
(369,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:15:16'),
(370,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:16:30'),
(371,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:18:01'),
(372,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:18:20'),
(373,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:18:25'),
(374,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:37:13'),
(375,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:37:23'),
(376,4,'account.deleted','user',8,'{\"target_user_id\":8}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:37:25'),
(377,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:37:25'),
(378,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:37:29'),
(379,4,'authorization.region_assignments_viewed','region_officer_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:37:31'),
(380,4,'master_data.region_catalog_viewed','region_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:37:35'),
(381,4,'authorization.region_assignments_viewed','region_officer_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:37:36'),
(382,4,'authorization.region_assignments_viewed','region_officer_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:37:37'),
(383,4,'master_data.region_catalog_viewed','region_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:37:40'),
(384,4,'authorization.region_assignments_viewed','region_officer_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:37:40'),
(385,4,'master_data.region_catalog_viewed','region_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:37:41'),
(386,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:37:43'),
(387,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:37:46'),
(388,4,'account.status_changed','user',7,'{\"from\":\"disabled\",\"to\":\"disabled\"}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:38:01'),
(389,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:38:01'),
(390,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:38:04'),
(391,4,'authorization.roles_changed','user',7,'{\"role_ids\":[7]}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:38:08'),
(392,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:38:08'),
(393,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:38:10'),
(394,4,'authorization.roles_changed','user',7,'{\"role_ids\":[]}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:38:14'),
(395,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:38:14'),
(396,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:38:16'),
(397,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:38:19'),
(398,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:38:22'),
(399,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:38:32'),
(400,4,'account.status_changed','user',6,'{\"from\":\"active\",\"to\":\"disabled\"}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:38:40'),
(401,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:38:40'),
(402,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:38:45'),
(403,4,'account.status_changed','user',7,'{\"from\":\"disabled\",\"to\":\"suspended\"}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:39:19'),
(404,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:39:19'),
(405,4,'account.status_changed','user',7,'{\"from\":\"suspended\",\"to\":\"disabled\"}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:39:27'),
(406,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:39:27'),
(407,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:39:30'),
(408,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:40:40'),
(409,4,'master_data.region_catalog_viewed','region_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:40:40'),
(410,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:46:38'),
(411,4,'master_data.region_catalog_viewed','region_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:46:38'),
(412,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:47:32'),
(413,4,'master_data.region_catalog_viewed','region_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:47:32'),
(414,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:49:09'),
(415,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:49:16'),
(416,4,'account.deleted','user',7,'{\"target_user_id\":7}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:49:20'),
(417,4,'resident.profile_deleted_with_account','resident',2,'{\"target_user_id\":7}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:49:20'),
(418,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:49:20'),
(419,4,'account.deleted','user',6,'{\"target_user_id\":6}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:49:25'),
(420,4,'resident.profile_deleted_with_account','resident',1,'{\"target_user_id\":6}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:49:25'),
(421,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:49:25'),
(422,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:49:27'),
(423,4,'master_data.village_settings_viewed','village_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:52:15'),
(424,4,'master_data.village_settings_viewed','village_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:52:17'),
(425,4,'master_data.village_settings_viewed','village_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:52:39'),
(426,4,'master_data.village_settings_viewed','village_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:52:43'),
(427,4,'authorization.region_assignments_viewed','region_officer_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:52:46'),
(428,4,'master_data.village_settings_viewed','village_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:52:47'),
(429,4,'authorization.region_assignments_viewed','region_officer_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:53:38'),
(430,4,'authorization.region_assignments_viewed','region_officer_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:53:39'),
(431,4,'authorization.region_assignments_viewed','region_officer_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:53:40'),
(432,4,'authorization.region_assignments_viewed','region_officer_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:53:40'),
(433,4,'authorization.region_assignments_viewed','region_officer_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:53:41'),
(434,4,'authorization.region_assignments_viewed','region_officer_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:53:41'),
(435,4,'authorization.region_assignments_viewed','region_officer_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:54:42'),
(436,4,'master_data.region_catalog_viewed','region_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:54:42'),
(437,4,'authorization.region_assignments_viewed','region_officer_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:55:18'),
(438,4,'master_data.region_catalog_viewed','region_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:55:19'),
(439,4,'authorization.region_assignments_viewed','region_officer_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:56:29'),
(440,4,'master_data.village_settings_viewed','village_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:56:30'),
(441,4,'master_data.village_settings_viewed','village_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:56:33'),
(442,4,'master_data.village_settings_viewed','village_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:56:39'),
(443,4,'authorization.region_assignment_history_detached','region_officer',1,'{\"target_user_id\":7,\"former_region_id\":2}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:56:41'),
(444,4,'master_data.region_deleted','region',2,'{\"region_type\":\"rt\",\"village_id\":1,\"parent_id\":1}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:56:41'),
(445,4,'master_data.village_settings_viewed','village_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:56:41'),
(446,4,'authorization.region_assignment_history_detached','region_officer',2,'{\"target_user_id\":8,\"former_region_id\":1}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:56:44'),
(447,4,'master_data.region_deleted','region',1,'{\"region_type\":\"rw\",\"village_id\":1,\"parent_id\":null}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:56:44'),
(448,4,'master_data.village_settings_viewed','village_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:56:44'),
(449,4,'master_data.village_deleted','village',1,'{\"village_code\":\"DESA01\"}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:56:46'),
(450,4,'master_data.village_settings_viewed','village_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:56:46'),
(451,4,'master_data.village_settings_viewed','village_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:56:47'),
(452,4,'master_data.region_catalog_viewed','region_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:57:22'),
(453,4,'master_data.village_created','village',2,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:57:38'),
(454,4,'master_data.region_catalog_viewed','region_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:57:38'),
(455,4,'master_data.region_created','region',3,'{\"region_type\":\"rw\"}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:57:52'),
(456,4,'master_data.region_catalog_viewed','region_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:57:52'),
(457,4,'master_data.region_created','region',4,'{\"region_type\":\"rt\",\"parent_id\":3}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:58:02'),
(458,4,'master_data.region_catalog_viewed','region_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:58:02'),
(459,4,'master_data.region_catalog_viewed','region_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:58:04'),
(460,4,'master_data.village_settings_viewed','village_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:58:06'),
(461,4,'authorization.region_assignments_viewed','region_officer_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:58:08'),
(462,4,'master_data.village_settings_viewed','village_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:58:09'),
(463,4,'master_data.region_catalog_viewed','region_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:58:10'),
(464,4,'account.pending_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:58:14'),
(465,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:58:15'),
(466,NULL,'account.registration_submitted','user',10,'{\"status\":\"pending\"}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:58:59'),
(467,NULL,'account.email_verified','user',10,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.134.0 Chrome/148.0.7778.280 Electron/42.8.1 Safari/537.36','2026-11-09 00:59:35'),
(468,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:59:43'),
(469,4,'account.pending_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:59:47'),
(470,4,'account.activated','user',10,'{\"status\":\"active\"}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:59:48'),
(471,4,'account.pending_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 00:59:49'),
(472,10,'resident.profile_created','resident',3,'{\"region_verified\":false}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 01:00:58'),
(473,10,'resident.profile_viewed','resident',3,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 01:00:58'),
(474,10,'authentication.logout','user',10,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 01:01:06'),
(475,NULL,'account.registration_submitted','user',11,'{\"status\":\"pending\"}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 01:01:28'),
(476,NULL,'account.email_verified','user',11,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.134.0 Chrome/148.0.7778.280 Electron/42.8.1 Safari/537.36','2026-11-09 01:01:40'),
(477,4,'account.pending_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 01:01:47'),
(478,4,'account.activated','user',11,'{\"status\":\"active\"}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 01:01:48'),
(479,4,'account.pending_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 01:01:48'),
(480,11,'resident.profile_created','resident',4,'{\"region_verified\":false}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 01:02:53'),
(481,11,'resident.profile_viewed','resident',4,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 01:02:53'),
(482,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 01:02:59'),
(483,4,'authorization.roles_changed','user',11,'{\"role_ids\":[7,5]}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 01:03:03'),
(484,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 01:03:03'),
(485,4,'master_data.region_catalog_viewed','region_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 01:03:05'),
(486,4,'authorization.region_assignments_viewed','region_officer_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 01:03:06'),
(487,4,'authorization.region_assigned','region_officer',3,'{\"target_user_id\":11,\"region_id\":4}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 01:03:16'),
(488,4,'authorization.region_assignments_viewed','region_officer_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 01:03:16'),
(489,4,'master_data.village_settings_viewed','village_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 01:03:19'),
(490,11,'authentication.logout','user',11,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 01:03:44'),
(491,NULL,'account.registration_submitted','user',12,'{\"status\":\"pending\"}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 01:04:06'),
(492,NULL,'account.email_verified','user',12,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.134.0 Chrome/148.0.7778.280 Electron/42.8.1 Safari/537.36','2026-11-09 01:19:43'),
(493,4,'account.pending_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 01:19:51'),
(494,4,'account.activated','user',12,'{\"status\":\"active\"}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 01:19:52'),
(495,4,'account.pending_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 01:19:52'),
(496,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 01:21:27'),
(497,4,'authorization.roles_changed','user',12,'{\"role_ids\":[7,6]}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 01:21:32'),
(498,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 01:21:32'),
(499,4,'authorization.roles_changed','user',12,'{\"role_ids\":[7,6]}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 01:21:34'),
(500,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 01:21:34'),
(501,4,'authorization.region_assignments_viewed','region_officer_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 01:21:37'),
(502,4,'authorization.region_assigned','region_officer',4,'{\"target_user_id\":12,\"region_id\":3}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 01:21:46'),
(503,4,'authorization.region_assignments_viewed','region_officer_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 01:21:46'),
(504,12,'resident.profile_created','resident',5,'{\"region_verified\":false}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 01:25:16'),
(505,12,'resident.profile_viewed','resident',5,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 01:25:16'),
(506,12,'authentication.logout','user',12,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 01:25:30'),
(507,NULL,'account.registration_submitted','user',13,'{\"status\":\"pending\"}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 01:27:14'),
(508,NULL,'account.email_verified','user',13,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.134.0 Chrome/148.0.7778.280 Electron/42.8.1 Safari/537.36','2026-11-09 01:27:53'),
(509,4,'account.pending_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 01:27:58'),
(510,4,'account.activated','user',13,'{\"status\":\"active\"}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 01:27:59'),
(511,4,'account.pending_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 01:28:00'),
(512,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 01:28:03'),
(513,4,'authorization.roles_changed','user',13,'{\"role_ids\":[4]}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 01:28:15'),
(514,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 01:28:15'),
(515,13,'resident.region_verification_list_viewed','resident_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 01:28:23'),
(516,13,'resident.region_verified','resident',3,'{\"region_id\":4}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 01:28:24'),
(517,13,'resident.region_verification_list_viewed','resident_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 01:28:24'),
(518,13,'resident.region_verified','resident',4,'{\"region_id\":4}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 01:28:25'),
(519,13,'resident.region_verification_list_viewed','resident_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 01:28:25'),
(520,13,'resident.region_verified','resident',5,'{\"region_id\":4}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 01:28:26'),
(521,13,'resident.region_verification_list_viewed','resident_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 01:28:26'),
(522,13,'master_data.village_settings_viewed','village_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 01:28:27'),
(523,13,'master_data.village_settings_viewed','village_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 01:28:51'),
(524,13,'resident.region_verification_list_viewed','resident_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 01:28:53'),
(525,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 01:38:38'),
(526,4,'master_data.region_catalog_viewed','region_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 01:38:38'),
(527,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 01:40:32'),
(528,4,'master_data.region_catalog_viewed','region_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 01:40:32'),
(529,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 01:41:04'),
(530,4,'master_data.region_catalog_viewed','region_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 01:41:04'),
(531,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 01:42:10'),
(532,4,'master_data.region_catalog_viewed','region_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 01:42:10'),
(533,4,'master_data.region_catalog_viewed','region_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 01:43:07'),
(534,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 01:43:07'),
(535,4,'master_data.region_catalog_viewed','region_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 01:44:46'),
(536,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 01:44:46'),
(537,4,'master_data.region_catalog_viewed','region_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 01:58:19'),
(538,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 01:58:20'),
(539,4,'master_data.region_catalog_viewed','region_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 01:59:21'),
(540,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 01:59:21'),
(541,13,'letter.type_created','letter_type',1,'{\"is_active\":true}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 02:16:11'),
(542,13,'authentication.logout','user',13,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 02:16:55'),
(543,10,'authentication.login_succeeded','user',10,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 02:17:07'),
(544,10,'letter.request_created','letter_request',1,'{\"channel\":\"self_service\"}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 02:18:22'),
(545,10,'authentication.logout','user',10,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 02:18:28'),
(546,NULL,'authentication.login_failed','authentication',NULL,'{\"reason\":\"invalid_credentials_or_inactive\"}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 02:18:43'),
(547,11,'authentication.login_succeeded','user',11,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 02:18:57'),
(548,11,'letter.workflow_decided','letter_request',1,'{\"decision\":\"approved\",\"status\":\"rw_review\"}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 02:19:21'),
(549,11,'authentication.logout','user',11,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 02:19:30'),
(550,12,'authentication.login_succeeded','user',12,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 02:24:23'),
(551,12,'letter.workflow_decided','letter_request',1,'{\"decision\":\"approved\",\"status\":\"village_review\"}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 02:25:16'),
(552,12,'authentication.logout','user',12,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 02:33:12'),
(553,13,'authentication.login_succeeded','user',13,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 02:33:31'),
(554,13,'letter.workflow_decided','letter_request',1,'{\"decision\":\"approved\",\"status\":\"signature_pending\"}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 02:33:39'),
(555,13,'authentication.logout','user',13,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 02:37:26'),
(556,NULL,'account.registration_submitted','user',14,'{\"status\":\"pending\"}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 02:40:01'),
(557,NULL,'account.email_verified','user',14,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.134.0 Chrome/148.0.7778.280 Electron/42.8.1 Safari/537.36','2026-11-09 02:40:17'),
(558,4,'account.pending_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 02:40:22'),
(559,4,'account.activated','user',14,'{\"status\":\"active\"}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 02:40:24'),
(560,4,'account.pending_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 02:40:24'),
(561,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 02:40:25'),
(562,4,'account.pending_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 02:43:43'),
(563,14,'authentication.logout','user',14,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 02:44:01'),
(564,NULL,'authentication.login_failed','authentication',NULL,'{\"reason\":\"invalid_credentials_or_inactive\"}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 02:47:44'),
(565,10,'authentication.login_succeeded','user',10,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 02:47:57'),
(566,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 02:48:33'),
(567,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 02:48:36'),
(568,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 02:54:24'),
(569,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 02:54:24'),
(570,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 02:54:59'),
(571,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 02:55:00'),
(572,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 02:56:37'),
(573,4,'authorization.roles_changed','user',14,'{\"role_ids\":[7,9]}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 02:56:39'),
(574,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 02:56:39'),
(575,10,'authentication.logout','user',10,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 02:58:59'),
(576,NULL,'authentication.login_failed','authentication',NULL,'{\"reason\":\"invalid_credentials_or_inactive\"}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 02:59:07'),
(577,14,'authentication.login_succeeded','user',14,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 02:59:16'),
(578,14,'letter.village_head_decision','letter_request',1,'{\"decision\":\"signed\",\"status\":\"completed\"}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 03:00:27'),
(579,14,'authentication.logout','user',14,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 03:02:12'),
(580,10,'authentication.login_succeeded','user',10,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 03:02:25'),
(581,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 03:16:40'),
(582,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 03:16:40'),
(583,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 03:18:22'),
(584,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 03:18:22'),
(585,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 03:18:43'),
(586,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 03:18:43'),
(587,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 03:19:05'),
(588,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 03:19:05'),
(589,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 03:19:26'),
(590,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 03:19:26'),
(591,10,'letter.request_created','letter_request',2,'{\"channel\":\"self_service\"}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 03:33:40'),
(592,10,'authentication.logout','user',10,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 03:35:46'),
(593,11,'authentication.login_succeeded','user',11,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 03:36:03'),
(594,11,'letter.workflow_decided','letter_request',2,'{\"decision\":\"approved\",\"status\":\"rw_review\"}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 03:36:12'),
(595,11,'authentication.logout','user',11,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 03:36:15'),
(596,12,'authentication.login_succeeded','user',12,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 03:37:01'),
(597,12,'letter.workflow_decided','letter_request',2,'{\"decision\":\"approved\",\"status\":\"village_review\"}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 03:37:10'),
(598,12,'authentication.logout','user',12,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 03:37:13'),
(599,13,'authentication.login_succeeded','user',13,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 03:37:39'),
(600,13,'letter.workflow_decided','letter_request',2,'{\"decision\":\"approved\",\"status\":\"signature_pending\"}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 03:38:36'),
(601,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 03:38:36'),
(602,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 03:38:37'),
(603,13,'authentication.logout','user',13,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 03:39:23'),
(604,14,'authentication.login_succeeded','user',14,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 03:39:33'),
(605,14,'letter.village_head_decision','letter_request',2,'{\"decision\":\"signed\",\"status\":\"completed\"}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 03:39:41'),
(606,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 03:43:56'),
(607,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 03:43:56'),
(608,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 03:44:20'),
(609,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 03:44:20'),
(610,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 03:57:50'),
(611,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 03:57:51'),
(612,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 03:58:15'),
(613,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 03:58:15'),
(614,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 03:58:50'),
(615,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 03:58:50'),
(616,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 03:59:34'),
(617,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 03:59:41'),
(618,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 03:59:52'),
(619,4,'account.pending_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 04:00:59'),
(620,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 04:01:00'),
(621,4,'authorization.region_assignments_viewed','region_officer_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 04:01:07'),
(622,4,'master_data.village_settings_viewed','village_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 04:01:10'),
(623,4,'master_data.region_catalog_viewed','region_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 04:01:11'),
(624,4,'master_data.village_settings_viewed','village_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 04:01:12'),
(625,4,'master_data.region_catalog_viewed','region_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 04:01:13'),
(626,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 04:03:10'),
(627,4,'account.pending_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 04:03:47'),
(628,4,'master_data.village_settings_viewed','village_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 04:03:48'),
(629,4,'authorization.region_assignments_viewed','region_officer_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 04:03:49'),
(630,4,'master_data.region_catalog_viewed','region_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 04:03:51'),
(631,4,'account.pending_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 04:03:58'),
(632,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 04:04:00'),
(633,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 04:13:34'),
(634,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 04:14:41'),
(635,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 04:18:47'),
(636,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 04:18:47'),
(637,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 04:35:18'),
(638,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 04:37:10'),
(639,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 04:41:56'),
(640,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 04:42:14'),
(641,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 04:52:36'),
(642,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 04:54:36'),
(643,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 04:55:08'),
(644,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 04:56:19'),
(645,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 04:58:34'),
(646,4,'account.pending_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 04:59:01'),
(647,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 04:59:02'),
(648,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 04:59:24'),
(649,14,'authentication.logout','user',14,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 05:00:26'),
(650,10,'authentication.login_succeeded','user',10,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 05:00:34'),
(651,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 05:58:07'),
(652,10,'authentication.logout','user',10,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 05:58:45'),
(653,NULL,'account.registration_submitted','user',15,'{\"status\":\"pending\"}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 06:00:11'),
(654,NULL,'account.email_verified','user',15,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.134.0 Chrome/148.0.7778.280 Electron/42.8.1 Safari/537.36','2026-11-09 06:00:27'),
(655,4,'account.pending_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 06:00:37'),
(656,4,'account.activated','user',15,'{\"status\":\"active\"}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 06:00:38'),
(657,4,'account.pending_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 06:00:38'),
(658,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 06:00:39'),
(659,4,'authorization.roles_changed','user',15,'{\"role_ids\":[3]}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 06:00:53'),
(660,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 06:00:53'),
(661,15,'authentication.logout','user',15,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 06:01:10'),
(662,NULL,'account.registration_submitted','user',16,'{\"status\":\"pending\"}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 06:02:15'),
(663,NULL,'account.email_verified','user',16,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.134.0 Chrome/148.0.7778.280 Electron/42.8.1 Safari/537.36','2026-11-09 06:02:25'),
(664,4,'account.pending_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 06:02:30'),
(665,4,'account.activated','user',16,'{\"status\":\"active\"}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 06:02:32'),
(666,4,'account.pending_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 06:02:32'),
(667,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 06:02:33'),
(668,4,'authorization.roles_changed','user',16,'{\"role_ids\":[2]}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 06:02:37'),
(669,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 06:02:37'),
(670,16,'bumdes_staff.list_viewed','bumdes_staff_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 06:03:43'),
(671,16,'bumdes_staff.profile_created','bumdes_staff',1,'{\"target_user_id\":15}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 06:04:10'),
(672,16,'bumdes_staff.list_viewed','bumdes_staff_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 06:04:10'),
(673,16,'bumdes_staff.profile_created','bumdes_staff',2,'{\"target_user_id\":16}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 06:04:39'),
(674,16,'bumdes_staff.list_viewed','bumdes_staff_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 06:04:39'),
(675,16,'bumdes_staff.list_viewed','bumdes_staff_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 06:04:41'),
(676,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 06:07:15'),
(677,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 06:09:27'),
(678,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 06:13:46'),
(679,16,'internet_package.created','internet_package',1,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 06:15:18'),
(680,16,'public_wifi.network_created','wifi_network',1,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 06:15:44'),
(681,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 06:16:21'),
(682,16,'bumdes_staff.list_viewed','bumdes_staff_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 06:16:40'),
(683,16,'bumdes_staff.list_viewed','bumdes_staff_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 06:16:42'),
(684,16,'authentication.logout','user',16,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 06:17:27'),
(685,10,'authentication.login_succeeded','user',10,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 06:17:37'),
(686,10,'installation_request.submitted','installation_request',1,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 06:17:54'),
(687,4,'authentication.logout','user',4,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 06:18:09'),
(688,15,'authentication.login_succeeded','user',15,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 06:18:25'),
(689,15,'installation_request.status_changed','installation_request',1,'{\"from\":\"submitted\",\"to\":\"approved\"}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 06:19:38'),
(690,10,'public_wifi.session_requested','wifi_network',1,'{\"result\":\"simulated_success\",\"terms_version\":1}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 06:21:40'),
(691,15,'installation_request.status_changed','installation_request',1,'{\"from\":\"approved\",\"to\":\"scheduled\"}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 06:35:33'),
(692,15,'installation_request.status_changed','installation_request',1,'{\"from\":\"scheduled\",\"to\":\"installed\"}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 06:36:08'),
(693,10,'invoice.payment_submitted','payment',1,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 06:44:07'),
(694,15,'invoice.payment_reviewed','payment',1,'{\"decision\":\"paid\"}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 06:52:04'),
(695,15,'authentication.logout','user',15,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 07:21:56'),
(696,16,'authentication.login_succeeded','user',16,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 07:22:33'),
(697,16,'authentication.logout','user',16,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 07:40:26'),
(698,NULL,'authentication.login_failed','authentication',NULL,'{\"reason\":\"invalid_credentials_or_inactive\"}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 07:40:34'),
(699,NULL,'authentication.login_failed','authentication',NULL,'{\"reason\":\"invalid_credentials_or_inactive\"}',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 07:40:42'),
(700,4,'authentication.login_succeeded','user',4,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 07:40:52'),
(701,4,'authorization.user_list_viewed','account_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 07:41:04'),
(702,4,'authentication.logout','user',4,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 07:45:28'),
(703,16,'authentication.login_succeeded','user',16,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 07:45:42'),
(704,16,'bumdes_staff.list_viewed','bumdes_staff_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 07:50:59'),
(705,16,'bumdes_staff.list_viewed','bumdes_staff_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 08:04:49'),
(706,16,'bumdes_staff.list_viewed','bumdes_staff_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 08:05:14'),
(707,16,'bumdes_staff.list_viewed','bumdes_staff_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 08:06:59'),
(708,16,'bumdes_staff.list_viewed','bumdes_staff_collection',NULL,'[]',0x7F000001,'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','2026-11-09 08:07:15');
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `bumdes_staff`
--

DROP TABLE IF EXISTS `bumdes_staff`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `bumdes_staff` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `department` varchar(100) DEFAULT NULL,
  `position` varchar(100) DEFAULT NULL,
  `employment_status` enum('active','inactive','suspended') NOT NULL DEFAULT 'active',
  `joined_at` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bumdes_staff_user_id_unique` (`user_id`),
  CONSTRAINT `bumdes_staff_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bumdes_staff`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `bumdes_staff` WRITE;
/*!40000 ALTER TABLE `bumdes_staff` DISABLE KEYS */;
INSERT INTO `bumdes_staff` VALUES
(1,15,'operasional','staff','active','2026-11-09','2026-11-09 06:04:10','2026-11-09 06:04:10'),
(2,16,'operasional','manager','active','2026-11-02','2026-11-09 06:04:39','2026-11-09 06:04:39');
/*!40000 ALTER TABLE `bumdes_staff` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `business_expenses`
--

DROP TABLE IF EXISTS `business_expenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `business_expenses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `category` varchar(40) NOT NULL,
  `description` varchar(1000) NOT NULL,
  `amount` decimal(14,2) unsigned NOT NULL,
  `expense_date` date NOT NULL,
  `status` enum('recorded','voided') NOT NULL DEFAULT 'recorded',
  `recorded_by` bigint(20) unsigned DEFAULT NULL,
  `voided_by` bigint(20) unsigned DEFAULT NULL,
  `voided_at` timestamp NULL DEFAULT NULL,
  `void_reason` varchar(500) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `business_expenses_recorded_by_foreign` (`recorded_by`),
  KEY `business_expenses_voided_by_foreign` (`voided_by`),
  KEY `idx_business_expenses_status_date` (`status`,`expense_date`),
  CONSTRAINT `business_expenses_recorded_by_foreign` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `business_expenses_voided_by_foreign` FOREIGN KEY (`voided_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `business_expenses`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `business_expenses` WRITE;
/*!40000 ALTER TABLE `business_expenses` DISABLE KEYS */;
/*!40000 ALTER TABLE `business_expenses` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` bigint(20) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
INSERT INTO `cache` VALUES
('desa-digital-cache-0ade7c2cf97f75d009975f4d720d1fa6c19f4897','i:3;',1794206877),
('desa-digital-cache-0ade7c2cf97f75d009975f4d720d1fa6c19f4897:timer','i:1794206877;',1794206877),
('desa-digital-cache-1574bddb75c78a6fd2251d61e2993b5146201319','i:1;',1794229481),
('desa-digital-cache-1574bddb75c78a6fd2251d61e2993b5146201319:timer','i:1794229481;',1794229481),
('desa-digital-cache-1648701ac52a7a447d5daa6571cae5cb','i:1;',1794220681),
('desa-digital-cache-1648701ac52a7a447d5daa6571cae5cb:timer','i:1794220681;',1794220681),
('desa-digital-cache-17ba0791499db908433b80f37c5fbc89b870084b','i:2;',1794211348),
('desa-digital-cache-17ba0791499db908433b80f37c5fbc89b870084b:timer','i:1794211348;',1794211348),
('desa-digital-cache-1b6453892473a467d07372d45eb05abc2031647a','i:4;',1794138454),
('desa-digital-cache-1b6453892473a467d07372d45eb05abc2031647a:timer','i:1794138454;',1794138454),
('desa-digital-cache-1c31ecdcf43a4c45335e125fdd661c66','i:2;',1794235294),
('desa-digital-cache-1c31ecdcf43a4c45335e125fdd661c66:timer','i:1794235294;',1794235294),
('desa-digital-cache-24326dccbef95198aea70a9a670385b4','i:1;',1794220623),
('desa-digital-cache-24326dccbef95198aea70a9a670385b4:timer','i:1794220623;',1794220623),
('desa-digital-cache-3bd9fd7bde779065820ebe4590138d81','i:1;',1794235602),
('desa-digital-cache-3bd9fd7bde779065820ebe4590138d81:timer','i:1794235602;',1794235602),
('desa-digital-cache-48adbf89cfc0c9cd6e15f2b9a07c4d06','i:1;',1794230560),
('desa-digital-cache-48adbf89cfc0c9cd6e15f2b9a07c4d06:timer','i:1794230560;',1794230560),
('desa-digital-cache-4d393911e9de2930de080759c1b3a1e1','i:1;',1794235312),
('desa-digital-cache-4d393911e9de2930de080759c1b3a1e1:timer','i:1794235312;',1794235312),
('desa-digital-cache-51f29e3b80b39e2bd5a928a018ce41e2','i:1;',1794220719),
('desa-digital-cache-51f29e3b80b39e2bd5a928a018ce41e2:timer','i:1794220719;',1794220719),
('desa-digital-cache-5c785c036466adea360111aa28563bfd556b5fba','i:1;',1794229405),
('desa-digital-cache-5c785c036466adea360111aa28563bfd556b5fba:timer','i:1794229405;',1794229405),
('desa-digital-cache-5e75c57f84f94df3bbf83de20dd974ef','i:1;',1794138057),
('desa-digital-cache-5e75c57f84f94df3bbf83de20dd974ef:timer','i:1794138057;',1794138057),
('desa-digital-cache-6ff45de4beeffafccfd3124f31d95dce','i:2;',1794232811),
('desa-digital-cache-6ff45de4beeffafccfd3124f31d95dce:timer','i:1794232811;',1794232811),
('desa-digital-cache-77de68daecd823babbb58edb1c8e14d7106e83bb','i:1;',1794136942),
('desa-digital-cache-77de68daecd823babbb58edb1c8e14d7106e83bb:timer','i:1794136942;',1794136942),
('desa-digital-cache-7b52009b64fd0a2a49e6d8a939753077792b0554','i:1;',1794212649),
('desa-digital-cache-7b52009b64fd0a2a49e6d8a939753077792b0554:timer','i:1794212649;',1794212649),
('desa-digital-cache-82d0659226e49d3c96c72a2f904126e0','i:1;',1794230317),
('desa-digital-cache-82d0659226e49d3c96c72a2f904126e0:timer','i:1794230317;',1794230317),
('desa-digital-cache-8a753555b220dc0e5b36975c417ed7af','i:1;',1794220832),
('desa-digital-cache-8a753555b220dc0e5b36975c417ed7af:timer','i:1794220832;',1794220832),
('desa-digital-cache-902ba3cda1883801594b6e1b452790cc53948fda','i:1;',1794203530),
('desa-digital-cache-902ba3cda1883801594b6e1b452790cc53948fda:timer','i:1794203530;',1794203530),
('desa-digital-cache-ac3478d69a3c81fa62e60f5c3696165a4e5e6ac4','i:6;',1794137973),
('desa-digital-cache-ac3478d69a3c81fa62e60f5c3696165a4e5e6ac4:timer','i:1794137973;',1794137973),
('desa-digital-cache-b1d5781111d84f7b3fe45a0852e59758cd7a87e5','i:2;',1794211199),
('desa-digital-cache-b1d5781111d84f7b3fe45a0852e59758cd7a87e5:timer','i:1794211199;',1794211199),
('desa-digital-cache-bd307a3ec329e10a2cff8fb87480823da114f8f4','i:1;',1794212959),
('desa-digital-cache-bd307a3ec329e10a2cff8fb87480823da114f8f4:timer','i:1794212959;',1794212959),
('desa-digital-cache-c1dfd96eea8cc2b62785275bca38ac261256e278','i:1;',1794138104),
('desa-digital-cache-c1dfd96eea8cc2b62785275bca38ac261256e278:timer','i:1794138104;',1794138104),
('desa-digital-cache-c604120a6941161245b7a819330bc169','i:2;',1794233874),
('desa-digital-cache-c604120a6941161245b7a819330bc169:timer','i:1794233874;',1794233874),
('desa-digital-cache-cafd5f85583367dfcf86900305f27756','i:1;',1794230364),
('desa-digital-cache-cafd5f85583367dfcf86900305f27756:timer','i:1794230364;',1794230364),
('desa-digital-cache-e75aaec6ac60cb79c0f94bf28b6235e0','i:1;',1794207078),
('desa-digital-cache-e75aaec6ac60cb79c0f94bf28b6235e0:timer','i:1794207078;',1794207078),
('desa-digital-cache-f1abd670358e036c31296e66b3b66c382ac00812','i:2;',1794229272),
('desa-digital-cache-f1abd670358e036c31296e66b3b66c382ac00812:timer','i:1794229272;',1794229272),
('desa-digital-cache-fa35e192121eabf3dabf9f5ea6abdbcbc107ac3b','i:1;',1794217495),
('desa-digital-cache-fa35e192121eabf3dabf9f5ea6abdbcbc107ac3b:timer','i:1794217495;',1794217495),
('desa-digital-cache-fe5dbbcea5ce7e2988b8c69bcfdfde8904aabc1f','i:2;',1794204598),
('desa-digital-cache-fe5dbbcea5ce7e2988b8c69bcfdfde8904aabc1f:timer','i:1794204598;',1794204598);
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` bigint(20) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `cart_items`
--

DROP TABLE IF EXISTS `cart_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cart_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cart_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `quantity` int(10) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_cart_product` (`cart_id`,`product_id`),
  KEY `cart_items_product_id_cart_id_index` (`product_id`,`cart_id`),
  CONSTRAINT `cart_items_cart_id_foreign` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`),
  CONSTRAINT `cart_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `chk_cart_items_positive_quantity` CHECK (`quantity` > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cart_items`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `cart_items` WRITE;
/*!40000 ALTER TABLE `cart_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `cart_items` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `carts`
--

DROP TABLE IF EXISTS `carts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `carts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'open',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `carts_user_id_unique` (`user_id`),
  KEY `carts_status_index` (`status`),
  CONSTRAINT `carts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `carts`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `carts` WRITE;
/*!40000 ALTER TABLE `carts` DISABLE KEYS */;
/*!40000 ALTER TABLE `carts` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `checkout_groups`
--

DROP TABLE IF EXISTS `checkout_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `checkout_groups` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `buyer_id` bigint(20) unsigned NOT NULL,
  `checkout_number` varchar(60) NOT NULL,
  `request_key` varchar(64) NOT NULL,
  `grand_total` decimal(14,2) NOT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_checkout_buyer_request_key` (`buyer_id`,`request_key`),
  UNIQUE KEY `checkout_groups_checkout_number_unique` (`checkout_number`),
  KEY `checkout_groups_buyer_id_created_at_index` (`buyer_id`,`created_at`),
  KEY `checkout_groups_status_index` (`status`),
  CONSTRAINT `checkout_groups_buyer_id_foreign` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `checkout_groups`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `checkout_groups` WRITE;
/*!40000 ALTER TABLE `checkout_groups` DISABLE KEYS */;
/*!40000 ALTER TABLE `checkout_groups` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `deliveries`
--

DROP TABLE IF EXISTS `deliveries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `deliveries` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `delivery_method` varchar(30) NOT NULL,
  `delivery_address` text DEFAULT NULL,
  `recipient_name` text DEFAULT NULL,
  `recipient_phone` text DEFAULT NULL,
  `delivery_reference` varchar(100) DEFAULT NULL,
  `delivery_status` varchar(30) NOT NULL DEFAULT 'preparing',
  `delivered_at` timestamp NULL DEFAULT NULL,
  `buyer_confirmed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `deliveries_order_id_unique` (`order_id`),
  KEY `deliveries_delivery_status_index` (`delivery_status`),
  CONSTRAINT `deliveries_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  CONSTRAINT `chk_deliveries_status` CHECK (`delivery_status` in ('preparing','ready','in_delivery','delivered','failed','confirmed')),
  CONSTRAINT `chk_deliveries_method` CHECK (`delivery_method` in ('delivery','pickup'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `deliveries`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `deliveries` WRITE;
/*!40000 ALTER TABLE `deliveries` DISABLE KEYS */;
/*!40000 ALTER TABLE `deliveries` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `delivery_events`
--

DROP TABLE IF EXISTS `delivery_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `delivery_events` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `delivery_id` bigint(20) unsigned NOT NULL,
  `actor_id` bigint(20) unsigned DEFAULT NULL,
  `from_status` varchar(30) DEFAULT NULL,
  `to_status` varchar(30) NOT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `delivery_events_actor_id_foreign` (`actor_id`),
  KEY `delivery_events_delivery_id_created_at_index` (`delivery_id`,`created_at`),
  CONSTRAINT `delivery_events_actor_id_foreign` FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `delivery_events_delivery_id_foreign` FOREIGN KEY (`delivery_id`) REFERENCES `deliveries` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `delivery_events`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `delivery_events` WRITE;
/*!40000 ALTER TABLE `delivery_events` DISABLE KEYS */;
/*!40000 ALTER TABLE `delivery_events` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` varchar(255) NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `installation_request_events`
--

DROP TABLE IF EXISTS `installation_request_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `installation_request_events` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `installation_request_id` bigint(20) unsigned NOT NULL,
  `actor_id` bigint(20) unsigned DEFAULT NULL,
  `from_status` varchar(30) DEFAULT NULL,
  `to_status` varchar(30) NOT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `installation_request_events_actor_id_foreign` (`actor_id`),
  KEY `idx_installation_events_history` (`installation_request_id`,`created_at`),
  CONSTRAINT `installation_request_events_actor_id_foreign` FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `installation_request_events_installation_request_id_foreign` FOREIGN KEY (`installation_request_id`) REFERENCES `installation_requests` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `installation_request_events`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `installation_request_events` WRITE;
/*!40000 ALTER TABLE `installation_request_events` DISABLE KEYS */;
INSERT INTO `installation_request_events` VALUES
(1,1,10,NULL,'submitted','eyJpdiI6IktLdGZSNHBJbi9BSHQ5YkgxNnViT1E9PSIsInZhbHVlIjoiSHprOFBFK0E2azUrNk1iTjg5dnVKckJmWGJLMlVPazQ0QWd1OHFTeHg3RT0iLCJtYWMiOiI2YmRjYjRmNWJkYzM2YWY5NDVmOGZmMjMxYjkwNDAyMTZkMGM5MWUwZmM4MTZiMWU4YzE3YmMwMDliNzNjYmFmIiwidGFnIjoiIn0=','2026-11-09 13:17:54'),
(2,1,15,'submitted','approved',NULL,'2026-11-09 13:19:38'),
(3,1,15,'approved','scheduled',NULL,'2026-11-09 13:35:33'),
(4,1,15,'scheduled','installed',NULL,'2026-11-09 13:36:08');
/*!40000 ALTER TABLE `installation_request_events` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `installation_requests`
--

DROP TABLE IF EXISTS `installation_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `installation_requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `resident_id` bigint(20) unsigned NOT NULL,
  `package_id` bigint(20) unsigned NOT NULL,
  `installation_address` text NOT NULL,
  `contact_phone` text DEFAULT NULL,
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `scheduled_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `status` enum('submitted','reviewing','approved','rejected','scheduled','installed','cancelled') NOT NULL DEFAULT 'submitted',
  `reviewed_by` bigint(20) unsigned DEFAULT NULL,
  `review_note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `installation_requests_package_id_foreign` (`package_id`),
  KEY `installation_requests_reviewed_by_foreign` (`reviewed_by`),
  KEY `idx_installation_requests_status` (`status`,`requested_at`),
  KEY `idx_installation_resident_status` (`resident_id`,`status`),
  CONSTRAINT `installation_requests_package_id_foreign` FOREIGN KEY (`package_id`) REFERENCES `internet_packages` (`id`),
  CONSTRAINT `installation_requests_resident_id_foreign` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`),
  CONSTRAINT `installation_requests_reviewed_by_foreign` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `installation_requests`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `installation_requests` WRITE;
/*!40000 ALTER TABLE `installation_requests` DISABLE KEYS */;
INSERT INTO `installation_requests` VALUES
(1,3,1,'eyJpdiI6IlpoejRjSm81Vk4veDdFT2FITldtOGc9PSIsInZhbHVlIjoiYmc1dU01bVBQZkYwd1gwYVBNVmJZZz09IiwibWFjIjoiYWFmYzM0YzhlMmQ5ODEyZDlkMmExOTQ1ZWM4Y2ZiMzBjYzkxNWIzMmYwMjA3MjRkYmQ4ZmVlY2FlMGFiYzlkMCIsInRhZyI6IiJ9','eyJpdiI6ImZXQU9oRFB5di8yWm4wQTN4YWgyeVE9PSIsInZhbHVlIjoialNma0NxeGhaLzRQUkxZbzhjK3pEUT09IiwibWFjIjoiNTA1MmZkYzYwZTU4MjI2MGMwZTQ3NGUxN2QxNThhYzk0ODE3OWYyMDZlMWNkZjFkZmI4NDQ4NGFkYjFhYTgzNSIsInRhZyI6IiJ9','2026-11-09 13:17:54','2026-11-09 15:35:00','2026-11-09 06:36:08','installed',15,NULL,'2026-11-09 06:17:54','2026-11-09 06:36:08');
/*!40000 ALTER TABLE `installation_requests` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `internet_packages`
--

DROP TABLE IF EXISTS `internet_packages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `internet_packages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `description` text DEFAULT NULL,
  `monthly_price` decimal(14,2) NOT NULL,
  `bandwidth_download_kbps` int(10) unsigned DEFAULT NULL,
  `bandwidth_upload_kbps` int(10) unsigned DEFAULT NULL,
  `validity_days` smallint(5) unsigned NOT NULL DEFAULT 30,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `internet_packages_is_active_index` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `internet_packages`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `internet_packages` WRITE;
/*!40000 ALTER TABLE `internet_packages` DISABLE KEYS */;
INSERT INTO `internet_packages` VALUES
(1,'Buzzer','COCOK UNTUK BUZZER',100000000.00,1000000,10000000,30,1,'2026-11-09 06:15:18','2026-11-09 06:15:18');
/*!40000 ALTER TABLE `internet_packages` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `internet_subscriptions`
--

DROP TABLE IF EXISTS `internet_subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `internet_subscriptions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `resident_id` bigint(20) unsigned NOT NULL,
  `package_id` bigint(20) unsigned NOT NULL,
  `installation_request_id` bigint(20) unsigned DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('pending','active','suspended','expired','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `internet_subscriptions_installation_request_id_unique` (`installation_request_id`),
  KEY `internet_subscriptions_package_id_foreign` (`package_id`),
  KEY `idx_internet_subscriptions_resident_status` (`resident_id`,`status`),
  CONSTRAINT `internet_subscriptions_installation_request_id_foreign` FOREIGN KEY (`installation_request_id`) REFERENCES `installation_requests` (`id`),
  CONSTRAINT `internet_subscriptions_package_id_foreign` FOREIGN KEY (`package_id`) REFERENCES `internet_packages` (`id`),
  CONSTRAINT `internet_subscriptions_resident_id_foreign` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `internet_subscriptions`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `internet_subscriptions` WRITE;
/*!40000 ALTER TABLE `internet_subscriptions` DISABLE KEYS */;
INSERT INTO `internet_subscriptions` VALUES
(1,3,1,1,'2026-11-09','2026-12-09','active','2026-11-09 06:36:08','2026-11-09 06:36:08');
/*!40000 ALTER TABLE `internet_subscriptions` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` smallint(5) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `letter_approvals`
--

DROP TABLE IF EXISTS `letter_approvals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `letter_approvals` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `letter_request_id` bigint(20) unsigned NOT NULL,
  `reviewer_id` bigint(20) unsigned NOT NULL,
  `actor_role` varchar(40) NOT NULL,
  `approval_level` enum('rt','rw','village_admin','village_head') NOT NULL,
  `decision` enum('approved','rejected','revision_required','signed') NOT NULL,
  `comments` text DEFAULT NULL,
  `resident_visible` tinyint(1) NOT NULL DEFAULT 0,
  `reviewed_at` timestamp NOT NULL,
  PRIMARY KEY (`id`),
  KEY `letter_approvals_reviewer_id_foreign` (`reviewer_id`),
  KEY `idx_letter_approvals_history` (`letter_request_id`,`approval_level`,`reviewed_at`),
  CONSTRAINT `letter_approvals_letter_request_id_foreign` FOREIGN KEY (`letter_request_id`) REFERENCES `letter_requests` (`id`),
  CONSTRAINT `letter_approvals_reviewer_id_foreign` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `letter_approvals`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `letter_approvals` WRITE;
/*!40000 ALTER TABLE `letter_approvals` DISABLE KEYS */;
INSERT INTO `letter_approvals` VALUES
(1,1,11,'RT','rt','approved',NULL,0,'2026-11-09 02:19:21'),
(2,1,12,'RW','rw','approved',NULL,0,'2026-11-09 02:25:16'),
(3,1,13,'Village Admin','village_admin','approved',NULL,0,'2026-11-09 02:33:39'),
(4,1,14,'Village Head','village_head','signed','Prototype signature action; not PSrE-certified.',0,'2026-11-09 03:00:27'),
(5,2,11,'RT','rt','approved',NULL,0,'2026-11-09 03:36:12'),
(6,2,12,'RW','rw','approved',NULL,0,'2026-11-09 03:37:10'),
(9,2,13,'Village Admin','village_admin','approved',NULL,0,'2026-11-09 03:38:36'),
(10,2,14,'Village Head','village_head','signed','Prototype signature action; not PSrE-certified.',0,'2026-11-09 03:39:41');
/*!40000 ALTER TABLE `letter_approvals` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `letter_attachments`
--

DROP TABLE IF EXISTS `letter_attachments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `letter_attachments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `letter_request_id` bigint(20) unsigned NOT NULL,
  `uploaded_by` bigint(20) unsigned NOT NULL,
  `request_version` int(10) unsigned NOT NULL DEFAULT 1,
  `attachment_key` varchar(80) NOT NULL,
  `private_file_path` varchar(500) NOT NULL,
  `original_filename` varchar(255) DEFAULT NULL,
  `mime_type` varchar(120) NOT NULL,
  `file_size` bigint(20) unsigned NOT NULL,
  `file_hash` char(64) NOT NULL,
  `uploaded_at` timestamp NOT NULL,
  PRIMARY KEY (`id`),
  KEY `letter_attachments_uploaded_by_foreign` (`uploaded_by`),
  KEY `idx_letter_attachments_version` (`letter_request_id`,`request_version`),
  CONSTRAINT `letter_attachments_letter_request_id_foreign` FOREIGN KEY (`letter_request_id`) REFERENCES `letter_requests` (`id`),
  CONSTRAINT `letter_attachments_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `letter_attachments`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `letter_attachments` WRITE;
/*!40000 ALTER TABLE `letter_attachments` DISABLE KEYS */;
INSERT INTO `letter_attachments` VALUES
(1,1,10,1,'kartu_keluarga','letters/1/attachments/w7VjedXYFRUwageDMgxsTFelPa1GegBOhQe0kC9tzY1zNj2b.pdf','cv_ahmad_alfarizzi.pdf','application/pdf',152016,'430b53b4dc1f9399d8f59488ee4f00a54335c22aa8a91b060fb720774dabbd3f','2026-11-09 02:18:22'),
(2,1,10,1,'ktp','letters/1/attachments/BMl7DLjG8IyMHwMHhXeSbXLNPjdvPwIqrlkgBfLx3rxO20sZ.pdf','Workshop Basis Data - Pertemuan 5.pdf','application/pdf',648826,'07270943f47b3b080bd37a7a2e6d389184a1c6012618d6f5cf34647c777be371','2026-11-09 02:18:22'),
(3,2,10,1,'kartu_keluarga','letters/2/attachments/lGxa1IzEIGLl0agD4epUdxkEoHgFXgb9008ghT4ZASfbSw2S.pdf','cv_ahmad_alfarizzi.pdf','application/pdf',152016,'430b53b4dc1f9399d8f59488ee4f00a54335c22aa8a91b060fb720774dabbd3f','2026-11-09 03:33:40'),
(4,2,10,1,'ktp','letters/2/attachments/AhXkn17GTvGr5zK5p0CQmDqCtKwQ5KCPxP2D8EQ0zcwfbpNt.pdf','Workshop Basis Data - Pertemuan 5.pdf','application/pdf',648826,'07270943f47b3b080bd37a7a2e6d389184a1c6012618d6f5cf34647c777be371','2026-11-09 03:33:40');
/*!40000 ALTER TABLE `letter_attachments` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `letter_documents`
--

DROP TABLE IF EXISTS `letter_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `letter_documents` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `letter_request_id` bigint(20) unsigned NOT NULL,
  `document_number` varchar(80) DEFAULT NULL,
  `private_file_path` varchar(500) NOT NULL,
  `document_hash` char(64) NOT NULL,
  `version` int(10) unsigned NOT NULL,
  `signed_by` bigint(20) unsigned DEFAULT NULL,
  `signed_at` timestamp NULL DEFAULT NULL,
  `signature_type` enum('prototype','psre') DEFAULT NULL,
  `issued_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_letter_document_version` (`letter_request_id`,`version`),
  KEY `letter_documents_signed_by_foreign` (`signed_by`),
  KEY `idx_letter_documents_hash` (`document_hash`),
  CONSTRAINT `letter_documents_letter_request_id_foreign` FOREIGN KEY (`letter_request_id`) REFERENCES `letter_requests` (`id`),
  CONSTRAINT `letter_documents_signed_by_foreign` FOREIGN KEY (`signed_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `letter_documents`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `letter_documents` WRITE;
/*!40000 ALTER TABLE `letter_documents` DISABLE KEYS */;
INSERT INTO `letter_documents` VALUES
(1,1,'LTR-20261109-QGFREMY2/01','letters/1/documents/version-0001.pdf','2553a9e465a602bd7c399677b3cad355a0b5134cbc45becd2436fab23a6515da',1,NULL,NULL,NULL,NULL,'2026-11-09 02:33:39'),
(2,1,'LTR-20261109-QGFREMY2/02','letters/1/documents/version-0002.pdf','d6d5c3babebcf0720a0bced6795a7435f814e92813211e235b2c02ad68a6e334',2,14,'2026-11-09 10:00:27','prototype','2026-11-09 10:00:27','2026-11-09 03:00:27'),
(3,2,'LTR-20261109-CMUIJLPB/01','letters/2/documents/version-0001.pdf','b024875055522653c23e58dfcfbb2ab2ae6c4854e75677d309aeea2c5205da3d',1,NULL,NULL,NULL,NULL,'2026-11-09 03:38:36'),
(4,2,'LTR-20261109-CMUIJLPB/02','letters/2/documents/version-0002.pdf','8f812cb9df5e3ec14ae599b4af83a36f9b926c39c9493a80f6d1c8df437acc0a',2,14,'2026-11-09 10:39:41','prototype','2026-11-09 10:39:41','2026-11-09 03:39:41');
/*!40000 ALTER TABLE `letter_documents` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `letter_request_versions`
--

DROP TABLE IF EXISTS `letter_request_versions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `letter_request_versions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `letter_request_id` bigint(20) unsigned NOT NULL,
  `changed_by` bigint(20) unsigned NOT NULL,
  `version` int(10) unsigned NOT NULL,
  `encrypted_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`encrypted_data`)),
  `submitted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_letter_request_version` (`letter_request_id`,`version`),
  KEY `letter_request_versions_changed_by_foreign` (`changed_by`),
  KEY `idx_letter_versions_history` (`letter_request_id`,`submitted_at`),
  CONSTRAINT `letter_request_versions_changed_by_foreign` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`),
  CONSTRAINT `letter_request_versions_letter_request_id_foreign` FOREIGN KEY (`letter_request_id`) REFERENCES `letter_requests` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `letter_request_versions`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `letter_request_versions` WRITE;
/*!40000 ALTER TABLE `letter_request_versions` DISABLE KEYS */;
INSERT INTO `letter_request_versions` VALUES
(1,1,10,1,'{\"ciphertext\":\"rfUtB32KD9SEVpNIRtrQ29g67ACeSFzNnmxMtgQrJfOH1zIcriITx3CmQEmCms+RoQk37iPN8gzAgmUne8PP8y83K\\/bzGJSh3LnBPiEQgopS\\/VhwMe2vTOl5RNWQOL2mTvKXHLgRj8IIZJjQi3\\/O287CJTuc8A8fW+kwyi\\/xuRO2pcIFZIpZPMOS0Q60WGTc7g==\",\"nonce\":\"e26feccccec7142c91ea0c35\",\"tag\":\"fdca9a4e538f6e9edd8921535b8822fb\"}','2026-11-09 02:18:22','2026-11-09 02:18:22','2026-11-09 02:18:22'),
(2,2,10,1,'{\"ciphertext\":\"EkzAyULag9XbFmogucrkcTrIAMSMoWcKq4gsTAeNfg1oPCBLVPA9BGZA0DJZEfhNUhVveJoAjlNgz4gGNfvXJbKWhMi+3\\/ehH5MaPY0EOqap+uTbujwFPbuLFR9bv6p4l7U2FCi9Zm\\/RxWrjJmwhy8Gi7qTDLndge\\/AclmowhGFmyQ==\",\"nonce\":\"ae6bdbc94a32c1252bcebd08\",\"tag\":\"3bebee5462d2ffc20d0c89a3851739e7\"}','2026-11-09 03:33:40','2026-11-09 03:33:40','2026-11-09 03:33:40');
/*!40000 ALTER TABLE `letter_request_versions` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `letter_requests`
--

DROP TABLE IF EXISTS `letter_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `letter_requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `resident_id` bigint(20) unsigned NOT NULL,
  `created_by` bigint(20) unsigned NOT NULL,
  `letter_type_id` bigint(20) unsigned NOT NULL,
  `request_number` varchar(60) NOT NULL,
  `submission_channel` enum('self_service','assisted_bumdes') NOT NULL,
  `encrypted_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`encrypted_data`)),
  `letter_type_snapshot` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`letter_type_snapshot`)),
  `status` enum('draft','submitted','rt_review','rw_review','village_review','signature_pending','completed','revision_required','rejected','cancelled') NOT NULL DEFAULT 'draft',
  `resident_confirmed_at` timestamp NULL DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `revision_number` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `letter_requests_request_number_unique` (`request_number`),
  KEY `letter_requests_created_by_foreign` (`created_by`),
  KEY `letter_requests_letter_type_id_foreign` (`letter_type_id`),
  KEY `idx_letter_requests_resident_status` (`resident_id`,`status`),
  KEY `idx_letter_requests_status_created` (`status`,`created_at`),
  CONSTRAINT `letter_requests_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `letter_requests_letter_type_id_foreign` FOREIGN KEY (`letter_type_id`) REFERENCES `letter_types` (`id`),
  CONSTRAINT `letter_requests_resident_id_foreign` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `letter_requests`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `letter_requests` WRITE;
/*!40000 ALTER TABLE `letter_requests` DISABLE KEYS */;
INSERT INTO `letter_requests` VALUES
(1,3,10,1,'LTR-20261109-QGFREMY2','self_service','{\"ciphertext\":\"PzreSmgHoDI1y2DJCLq6xFoB6ODBwFX1+\\/hRCmtditN\\/oWdlln4j0S3qpJVB8fZXDji3ET5DHV2Mlc10JZpA8TcckHfUbMPWJ\\/dG3Sfkp2BBDyRKHNr\\/7MjMTSSQCgow11JyYTpVA3gUPd3yAuqNgVb4nOGYqlrnHGbvoiAjaeDBRDdzE1fp5mFYdPAIWfnF3Q==\",\"nonce\":\"11bfcf9fe09fe71967bc09d7\",\"tag\":\"d9b8bc4d45bb8c428f798e203cbe1272\"}','{\"code\":\"SURAT_DOMISILI\",\"name\":\"Domisili\",\"required_fields\":[{\"key\":\"nama_lengkap\",\"label\":\"Nama Lengkap\",\"type\":\"text\",\"required\":true,\"max\":\"255\"},{\"key\":\"nik\",\"label\":\"nik\",\"type\":\"integer\",\"required\":true},{\"key\":\"tempat_tanggal_lahir\",\"label\":\"Tempat Tanggal Lahir\",\"type\":\"date\",\"required\":true},{\"key\":\"warga_negara\",\"label\":\"Warga Negara\",\"type\":\"text\",\"required\":true,\"max\":\"20\"},{\"key\":\"agama\",\"label\":\"Agama\",\"type\":\"text\",\"required\":true,\"max\":\"10\"},{\"key\":\"pekerjaan\",\"label\":\"Pekerjaan\",\"type\":\"text\",\"required\":true,\"max\":\"50\"},{\"key\":\"alamat\",\"label\":\"Alamat\",\"type\":\"textarea\",\"required\":true,\"max\":\"255\"}],\"required_attachments\":[{\"key\":\"kartu_keluarga\",\"label\":\"Kartu Keluarga\",\"required\":true,\"max_kb\":5120},{\"key\":\"ktp\",\"label\":\"KTP\",\"required\":true,\"max_kb\":5120}],\"template_path\":null}','completed','2026-11-09 02:18:22','2026-11-09 02:18:22','2026-11-09 10:00:27',NULL,0,'2026-11-09 02:18:22','2026-11-09 03:00:27'),
(2,3,10,1,'LTR-20261109-CMUIJLPB','self_service','{\"ciphertext\":\"AnoZ+NuQh9makwadTQrf0ksNRI+uMzeagk\\/JLBBRpkZm5Mikg6KBa1Ul137Gqs0BxSJRpVxvSpDDEfZS8S\\/H1Ea4McWE9iL3\\/yrk7GkBisv7iUn8SogrpSt+OlSLPUmtbdPm37iKBE4bW0ilWLQggPK3ks7CIs2Jt8cew7v4dnyHJA==\",\"nonce\":\"6f38b2b31dcac7a1c5e7285e\",\"tag\":\"a958239b07ffc971881d6e2d81cdf439\"}','{\"code\":\"SURAT_DOMISILI\",\"name\":\"Domisili\",\"required_fields\":[{\"key\":\"nama_lengkap\",\"label\":\"Nama Lengkap\",\"type\":\"text\",\"required\":true,\"max\":\"255\"},{\"key\":\"nik\",\"label\":\"nik\",\"type\":\"integer\",\"required\":true},{\"key\":\"tempat_tanggal_lahir\",\"label\":\"Tempat Tanggal Lahir\",\"type\":\"date\",\"required\":true},{\"key\":\"warga_negara\",\"label\":\"Warga Negara\",\"type\":\"text\",\"required\":true,\"max\":\"20\"},{\"key\":\"agama\",\"label\":\"Agama\",\"type\":\"text\",\"required\":true,\"max\":\"10\"},{\"key\":\"pekerjaan\",\"label\":\"Pekerjaan\",\"type\":\"text\",\"required\":true,\"max\":\"50\"},{\"key\":\"alamat\",\"label\":\"Alamat\",\"type\":\"textarea\",\"required\":true,\"max\":\"255\"}],\"required_attachments\":[{\"key\":\"kartu_keluarga\",\"label\":\"Kartu Keluarga\",\"required\":true,\"max_kb\":5120},{\"key\":\"ktp\",\"label\":\"KTP\",\"required\":true,\"max_kb\":5120}],\"template_path\":null}','completed','2026-11-09 03:33:40','2026-11-09 03:33:40','2026-11-09 10:39:41',NULL,0,'2026-11-09 03:33:40','2026-11-09 03:39:41');
/*!40000 ALTER TABLE `letter_requests` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `letter_types`
--

DROP TABLE IF EXISTS `letter_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `letter_types` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(40) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `required_fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`required_fields`)),
  `required_attachments` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`required_attachments`)),
  `template_path` varchar(500) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `letter_types_code_unique` (`code`),
  KEY `letter_types_is_active_index` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `letter_types`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `letter_types` WRITE;
/*!40000 ALTER TABLE `letter_types` DISABLE KEYS */;
INSERT INTO `letter_types` VALUES
(1,'SURAT_DOMISILI','Domisili','Membuat surat Domisili','[{\"key\":\"nama_lengkap\",\"label\":\"Nama Lengkap\",\"type\":\"text\",\"required\":true,\"max\":\"255\"},{\"key\":\"nik\",\"label\":\"nik\",\"type\":\"integer\",\"required\":true},{\"key\":\"tempat_tanggal_lahir\",\"label\":\"Tempat Tanggal Lahir\",\"type\":\"date\",\"required\":true},{\"key\":\"warga_negara\",\"label\":\"Warga Negara\",\"type\":\"text\",\"required\":true,\"max\":\"20\"},{\"key\":\"agama\",\"label\":\"Agama\",\"type\":\"text\",\"required\":true,\"max\":\"10\"},{\"key\":\"pekerjaan\",\"label\":\"Pekerjaan\",\"type\":\"text\",\"required\":true,\"max\":\"50\"},{\"key\":\"alamat\",\"label\":\"Alamat\",\"type\":\"textarea\",\"required\":true,\"max\":\"255\"}]','[{\"key\":\"kartu_keluarga\",\"label\":\"Kartu Keluarga\",\"required\":true,\"max_kb\":5120},{\"key\":\"ktp\",\"label\":\"KTP\",\"required\":true,\"max_kb\":5120}]',NULL,1,'2026-11-09 02:16:11','2026-11-09 02:16:11');
/*!40000 ALTER TABLE `letter_types` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES
(1,'0001_01_01_000000_create_users_table',1),
(2,'0001_01_01_000001_create_cache_table',1),
(3,'0001_01_01_000002_create_jobs_table',1),
(4,'2026_11_07_181040_create_village_identity_tables',1),
(5,'2026_11_08_000001_create_audit_logs_table',2),
(6,'2026_11_08_000002_add_region_verification_to_residents',2),
(7,'2026_11_08_000003_encrypt_resident_demographics',2),
(8,'2026_11_08_000004_encrypt_user_phone_numbers',2),
(9,'2026_11_08_000005_encrypt_legacy_user_names',2),
(10,'2026_11_09_000001_create_correspondence_tables',3),
(11,'2026_11_09_000002_remove_nik_from_resident_profiles',4),
(12,'2026_11_09_000003_scope_region_codes_to_their_parent',5),
(13,'2026_11_09_000004_preserve_region_assignment_on_user_deletion',5),
(14,'2026_11_09_000005_preserve_region_assignment_on_region_deletion',6),
(15,'2026_11_09_112615_create_internet_and_wifi_service_tables',7),
(16,'2026_11_09_114248_widen_encrypted_installation_contact_phone',8),
(17,'2026_11_09_135540_create_business_expenses_table',9),
(18,'2026_11_09_153631_create_marketplace_tables',10);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `network_devices`
--

DROP TABLE IF EXISTS `network_devices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `network_devices` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `wifi_network_id` bigint(20) unsigned NOT NULL,
  `device_name` varchar(120) NOT NULL,
  `integration_type` enum('simulated','mikrotik','other') NOT NULL DEFAULT 'simulated',
  `device_identifier` varchar(150) DEFAULT NULL,
  `device_status` enum('online','offline','maintenance','unknown') NOT NULL DEFAULT 'unknown',
  `configuration` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`configuration`)),
  `last_seen_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `network_devices_wifi_network_id_foreign` (`wifi_network_id`),
  CONSTRAINT `network_devices_wifi_network_id_foreign` FOREIGN KEY (`wifi_network_id`) REFERENCES `wifi_networks` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `network_devices`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `network_devices` WRITE;
/*!40000 ALTER TABLE `network_devices` DISABLE KEYS */;
INSERT INTO `network_devices` VALUES
(1,1,'Gateway simulasi','simulated',NULL,'unknown','{\"enforces_network_access\":false}',NULL,'2026-11-09 06:15:44','2026-11-09 06:15:44');
/*!40000 ALTER TABLE `network_devices` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `title` varchar(180) NOT NULL,
  `message` text NOT NULL,
  `reference_type` varchar(100) DEFAULT NULL,
  `reference_id` bigint(20) unsigned DEFAULT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_notifications_user_read` (`user_id`,`read_at`,`created_at`),
  CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` VALUES
(1,11,'Pembaruan permohonan surat','Permohonan baru menunggu pemeriksaan RT.','letter_request',1,'2026-11-09 02:19:04','2026-11-09 02:18:22'),
(2,10,'Pembaruan permohonan surat','Permohonan surat Anda telah dikirim untuk pemeriksaan RT.','letter_request',1,'2026-11-09 02:54:19','2026-11-09 02:18:22'),
(3,12,'Pembaruan permohonan surat','Permohonan menunggu pemeriksaan RW.','letter_request',1,'2026-11-09 02:25:06','2026-11-09 02:19:21'),
(4,10,'Pembaruan permohonan surat','RT menyetujui permohonan. Permohonan diteruskan ke RW.','letter_request',1,'2026-11-09 02:54:16','2026-11-09 02:19:21'),
(5,13,'Pembaruan permohonan surat','Permohonan menunggu pemeriksaan administrasi desa.','letter_request',1,'2026-11-09 02:33:35','2026-11-09 02:25:16'),
(6,10,'Pembaruan permohonan surat','RW menyetujui permohonan. Permohonan diteruskan ke administrasi desa.','letter_request',1,'2026-11-09 02:54:10','2026-11-09 02:25:16'),
(7,10,'Pembaruan permohonan surat','Administrasi desa menyetujui permohonan. Permohonan menunggu Kepala Desa.','letter_request',1,'2026-11-09 02:48:06','2026-11-09 02:33:39'),
(8,10,'Pembaruan permohonan surat','Dokumen surat prototipe telah diterbitkan.','letter_request',1,'2026-11-09 03:02:45','2026-11-09 03:00:27'),
(9,11,'Pembaruan permohonan surat','Permohonan baru menunggu pemeriksaan RT.','letter_request',2,'2026-11-09 03:36:07','2026-11-09 03:33:40'),
(10,10,'Pembaruan permohonan surat','Permohonan surat Anda telah dikirim untuk pemeriksaan RT.','letter_request',2,NULL,'2026-11-09 03:33:40'),
(11,12,'Pembaruan permohonan surat','Permohonan menunggu pemeriksaan RW.','letter_request',2,'2026-11-09 03:37:05','2026-11-09 03:36:12'),
(12,10,'Pembaruan permohonan surat','RT menyetujui permohonan. Permohonan diteruskan ke RW.','letter_request',2,NULL,'2026-11-09 03:36:12'),
(13,13,'Pembaruan permohonan surat','Permohonan menunggu pemeriksaan administrasi desa.','letter_request',2,'2026-11-09 03:38:07','2026-11-09 03:37:10'),
(14,10,'Pembaruan permohonan surat','RW menyetujui permohonan. Permohonan diteruskan ke administrasi desa.','letter_request',2,NULL,'2026-11-09 03:37:10'),
(15,14,'Pembaruan permohonan surat','Permohonan menunggu keputusan Kepala Desa.','letter_request',2,'2026-11-09 04:04:07','2026-11-09 03:38:36'),
(16,10,'Pembaruan permohonan surat','Administrasi desa menyetujui permohonan. Permohonan menunggu Kepala Desa.','letter_request',2,NULL,'2026-11-09 03:38:36'),
(17,10,'Pembaruan permohonan surat','Dokumen surat prototipe telah diterbitkan.','letter_request',2,NULL,'2026-11-09 03:39:41');
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `product_name_snapshot` varchar(180) NOT NULL,
  `unit_price` decimal(14,2) NOT NULL,
  `quantity` int(10) unsigned NOT NULL,
  `subtotal` decimal(14,2) NOT NULL,
  `attributes_snapshot` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`attributes_snapshot`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `order_items_product_id_foreign` (`product_id`),
  KEY `order_items_order_id_product_id_index` (`order_id`,`product_id`),
  CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  CONSTRAINT `order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `chk_order_items_positive_quantity` CHECK (`quantity` > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `order_status_events`
--

DROP TABLE IF EXISTS `order_status_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_status_events` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `actor_id` bigint(20) unsigned DEFAULT NULL,
  `from_status` varchar(30) DEFAULT NULL,
  `to_status` varchar(30) NOT NULL,
  `reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `order_status_events_actor_id_foreign` (`actor_id`),
  KEY `order_status_events_order_id_created_at_index` (`order_id`,`created_at`),
  CONSTRAINT `order_status_events_actor_id_foreign` FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `order_status_events_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_status_events`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `order_status_events` WRITE;
/*!40000 ALTER TABLE `order_status_events` DISABLE KEYS */;
/*!40000 ALTER TABLE `order_status_events` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `checkout_group_id` bigint(20) unsigned NOT NULL,
  `buyer_id` bigint(20) unsigned NOT NULL,
  `seller_id` bigint(20) unsigned NOT NULL,
  `order_number` varchar(60) NOT NULL,
  `total_amount` decimal(14,2) NOT NULL,
  `payment_method` varchar(30) NOT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'pending_seller',
  `rejection_reason` text DEFAULT NULL,
  `cancellation_reason` text DEFAULT NULL,
  `accepted_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `orders_order_number_unique` (`order_number`),
  KEY `orders_seller_id_status_created_at_index` (`seller_id`,`status`,`created_at`),
  KEY `orders_buyer_id_created_at_index` (`buyer_id`,`created_at`),
  KEY `orders_checkout_group_id_seller_id_index` (`checkout_group_id`,`seller_id`),
  KEY `orders_status_index` (`status`),
  CONSTRAINT `orders_buyer_id_foreign` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`),
  CONSTRAINT `orders_checkout_group_id_foreign` FOREIGN KEY (`checkout_group_id`) REFERENCES `checkout_groups` (`id`),
  CONSTRAINT `orders_seller_id_foreign` FOREIGN KEY (`seller_id`) REFERENCES `sellers` (`id`),
  CONSTRAINT `chk_orders_status` CHECK (`status` in ('pending_seller','accepted','rejected','processing','ready_for_delivery','in_delivery','delivered','delivery_failed','completed','cancelled','awaiting_payment'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `payment_events`
--

DROP TABLE IF EXISTS `payment_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `payment_events` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `payment_id` bigint(20) unsigned NOT NULL,
  `event_type` varchar(100) NOT NULL,
  `event_reference` varchar(180) DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `signature_verified` tinyint(1) NOT NULL DEFAULT 0,
  `received_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `payment_events_event_reference_unique` (`event_reference`),
  KEY `idx_payment_events_payment_time` (`payment_id`,`received_at`),
  CONSTRAINT `payment_events_payment_id_foreign` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payment_events`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `payment_events` WRITE;
/*!40000 ALTER TABLE `payment_events` DISABLE KEYS */;
INSERT INTO `payment_events` VALUES
(1,1,'payment.submitted',NULL,'{\"source\":\"resident_portal\"}',0,'2026-11-09 13:44:07'),
(2,1,'payment.confirmed',NULL,'{\"note_present\":false}',0,'2026-11-09 13:52:04');
/*!40000 ALTER TABLE `payment_events` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned DEFAULT NULL,
  `invoice_id` bigint(20) unsigned DEFAULT NULL,
  `payment_method` enum('gateway','bank_transfer','cash','other') NOT NULL DEFAULT 'bank_transfer',
  `payment_provider` varchar(80) DEFAULT NULL,
  `provider_reference` varchar(180) DEFAULT NULL,
  `amount` decimal(14,2) NOT NULL,
  `status` enum('pending','authorized','paid','failed','expired','cancelled','refunded') NOT NULL DEFAULT 'pending',
  `submitted_by` bigint(20) unsigned DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payments_invoice_id_foreign` (`invoice_id`),
  KEY `payments_submitted_by_foreign` (`submitted_by`),
  KEY `idx_payments_status_created` (`status`,`created_at`),
  KEY `idx_payments_provider_reference` (`provider_reference`),
  KEY `payments_order_id_foreign` (`order_id`),
  CONSTRAINT `payments_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `subscription_invoices` (`id`),
  CONSTRAINT `payments_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  CONSTRAINT `payments_submitted_by_foreign` FOREIGN KEY (`submitted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `chk_payments_order_or_invoice` CHECK (`order_id` is not null and `invoice_id` is null or `order_id` is null and `invoice_id` is not null),
  CONSTRAINT `chk_payments_positive_amount` CHECK (`amount` > 0)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
INSERT INTO `payments` VALUES
(1,NULL,1,'cash',NULL,NULL,100000000.00,'paid',10,'2026-11-09 06:52:04',NULL,'2026-11-09 06:44:07','2026-11-09 06:52:04');
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `product_categories`
--

DROP TABLE IF EXISTS `product_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(120) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_categories_name_unique` (`name`),
  UNIQUE KEY `product_categories_slug_unique` (`slug`),
  KEY `product_categories_is_active_index` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_categories`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `product_categories` WRITE;
/*!40000 ALTER TABLE `product_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_categories` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `product_images`
--

DROP TABLE IF EXISTS `product_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_images` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) unsigned NOT NULL,
  `private_file_path` varchar(500) NOT NULL,
  `alt_text` varchar(180) NOT NULL,
  `mime_type` varchar(80) NOT NULL,
  `file_size` int(10) unsigned NOT NULL,
  `file_hash` char(64) NOT NULL,
  `sort_order` smallint(5) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_images_product_id_sort_order_index` (`product_id`,`sort_order`),
  CONSTRAINT `product_images_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_images`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `product_images` WRITE;
/*!40000 ALTER TABLE `product_images` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_images` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `product_moderation_events`
--

DROP TABLE IF EXISTS `product_moderation_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_moderation_events` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) unsigned NOT NULL,
  `actor_id` bigint(20) unsigned DEFAULT NULL,
  `from_status` varchar(30) DEFAULT NULL,
  `to_status` varchar(30) NOT NULL,
  `reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `product_moderation_events_actor_id_foreign` (`actor_id`),
  KEY `product_moderation_events_product_id_created_at_index` (`product_id`,`created_at`),
  CONSTRAINT `product_moderation_events_actor_id_foreign` FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `product_moderation_events_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_moderation_events`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `product_moderation_events` WRITE;
/*!40000 ALTER TABLE `product_moderation_events` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_moderation_events` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `products` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `seller_id` bigint(20) unsigned NOT NULL,
  `category_id` bigint(20) unsigned NOT NULL,
  `name` varchar(180) NOT NULL,
  `slug` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `unit` varchar(40) NOT NULL DEFAULT 'item',
  `price` decimal(14,2) NOT NULL,
  `stock` int(10) unsigned NOT NULL DEFAULT 0,
  `status` varchar(30) NOT NULL DEFAULT 'draft',
  `moderation_note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `products_slug_unique` (`slug`),
  KEY `products_seller_id_status_created_at_index` (`seller_id`,`status`,`created_at`),
  KEY `products_category_id_status_price_index` (`category_id`,`status`,`price`),
  KEY `products_status_created_at_index` (`status`,`created_at`),
  KEY `products_status_index` (`status`),
  CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `product_categories` (`id`),
  CONSTRAINT `products_seller_id_foreign` FOREIGN KEY (`seller_id`) REFERENCES `sellers` (`id`),
  CONSTRAINT `chk_products_positive_price` CHECK (`price` > 0),
  CONSTRAINT `chk_products_nonnegative_stock` CHECK (`stock` >= 0),
  CONSTRAINT `chk_products_status` CHECK (`status` in ('draft','pending_review','active','rejected','inactive','out_of_stock','archived'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `region_officers`
--

DROP TABLE IF EXISTS `region_officers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `region_officers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `former_user_id` bigint(20) unsigned DEFAULT NULL,
  `region_id` bigint(20) unsigned DEFAULT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `assigned_by` bigint(20) unsigned DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `ended_at` timestamp NULL DEFAULT NULL,
  `former_region_id` bigint(20) unsigned DEFAULT NULL,
  `former_region_code` varchar(30) DEFAULT NULL,
  `former_region_name` varchar(120) DEFAULT NULL,
  `former_region_type` varchar(10) DEFAULT NULL,
  `former_region_parent_name` varchar(120) DEFAULT NULL,
  `former_village_name` varchar(150) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `region_officers_assigned_by_foreign` (`assigned_by`),
  KEY `idx_region_officers_scope` (`user_id`,`region_id`,`is_active`),
  KEY `idx_region_officers_former_user` (`former_user_id`),
  KEY `idx_region_officers_former_region` (`former_region_id`),
  KEY `region_officers_region_id_foreign` (`region_id`),
  CONSTRAINT `region_officers_assigned_by_foreign` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `region_officers_region_id_foreign` FOREIGN KEY (`region_id`) REFERENCES `regions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `region_officers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `region_officers`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `region_officers` WRITE;
/*!40000 ALTER TABLE `region_officers` DISABLE KEYS */;
INSERT INTO `region_officers` VALUES
(1,NULL,7,NULL,'2026-11-08 22:53:17',4,0,'2026-11-08 23:57:30',2,'RT-04','RT 04','rt','RW 02','yo nda tau'),
(2,NULL,8,NULL,'2026-11-08 23:32:03',4,0,'2026-11-08 23:57:33',1,'RW-02','RW 02','rw',NULL,'yo nda tau'),
(3,11,NULL,4,'2026-11-09 01:03:16',4,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL),
(4,12,NULL,3,'2026-11-09 01:21:46',4,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `region_officers` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `regions`
--

DROP TABLE IF EXISTS `regions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `regions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `village_id` bigint(20) unsigned NOT NULL,
  `parent_id` bigint(20) unsigned DEFAULT NULL,
  `region_type` enum('rw','rt') NOT NULL,
  `region_code` varchar(30) NOT NULL,
  `name` varchar(120) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `code_scope_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_region_village_region_scope_code` (`village_id`,`region_type`,`code_scope_id`,`region_code`),
  KEY `idx_regions_parent` (`parent_id`),
  KEY `idx_regions_village_id` (`village_id`),
  CONSTRAINT `regions_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `regions` (`id`),
  CONSTRAINT `regions_village_id_foreign` FOREIGN KEY (`village_id`) REFERENCES `villages` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `regions`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `regions` WRITE;
/*!40000 ALTER TABLE `regions` DISABLE KEYS */;
INSERT INTO `regions` VALUES
(3,2,NULL,'rw','RW-02','RW 02',NULL,'2026-11-09 00:57:52','2026-11-09 00:57:52',0),
(4,2,3,'rt','RT-04','RW 04',NULL,'2026-11-09 00:58:02','2026-11-09 00:58:02',3);
/*!40000 ALTER TABLE `regions` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `residents`
--

DROP TABLE IF EXISTS `residents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `residents` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `region_id` bigint(20) unsigned NOT NULL,
  `full_name_ciphertext` text NOT NULL,
  `full_name_nonce` varchar(64) DEFAULT NULL,
  `full_name_tag` varchar(64) DEFAULT NULL,
  `phone_ciphertext` text DEFAULT NULL,
  `phone_nonce` varchar(64) DEFAULT NULL,
  `phone_tag` varchar(64) DEFAULT NULL,
  `address_ciphertext` text DEFAULT NULL,
  `address_nonce` varchar(64) DEFAULT NULL,
  `address_tag` varchar(64) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `gender` enum('male','female','other','undisclosed') DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `region_verified_at` timestamp NULL DEFAULT NULL,
  `birth_date_ciphertext` text DEFAULT NULL,
  `birth_date_nonce` varchar(64) DEFAULT NULL,
  `birth_date_tag` varchar(64) DEFAULT NULL,
  `gender_ciphertext` text DEFAULT NULL,
  `gender_nonce` varchar(64) DEFAULT NULL,
  `gender_tag` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `residents_user_id_unique` (`user_id`),
  KEY `idx_residents_region` (`region_id`),
  KEY `idx_residents_verified_region` (`region_id`,`region_verified_at`),
  CONSTRAINT `residents_region_id_foreign` FOREIGN KEY (`region_id`) REFERENCES `regions` (`id`),
  CONSTRAINT `residents_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `residents`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `residents` WRITE;
/*!40000 ALTER TABLE `residents` DISABLE KEYS */;
INSERT INTO `residents` VALUES
(3,10,4,'74Qlol8=','82e4ccb325c9d4dfb7e7d734','1bc0d9dbd7ce5f2cbd15578d7745e2d1','wN4JgaZ5Kz1decTB','aa2d12cd36a85957dfae405c','57d8d6240e63f33b7d245ddcaeb53ff1','KbGxTcVQoilbAg==','371f3f86b6f56fe090ef8d2b','c01044b34b4934704819fdc3e8eb0624',NULL,NULL,'2026-11-09 01:00:58','2026-11-09 01:28:24','2026-11-09 01:28:24','7EjRJXkwMnh5Qw==','01fff50ba5038b3ff156317a','02f12570415ad9bc78ab8c279f3000b5','Ixu9qA==','1a5849662ccf5de7827d7689','a9fa03130665d2704fc9fab06f76583d'),
(4,11,4,'T0O9','abf4fd82d561afaef62235da','e22e03ee4916b330afbbe01156f7dbbc','QqdRlmCUB3jirmr0','07c76c18d286fe48db02c842','a2a8b59920e5393de2008e13daa28fcd','CHEV8E1VQQLGxA==','ef4f3001d0d630c34fe0a030','75d2ece9f810c17eb6381e8bb26c3580',NULL,NULL,'2026-11-09 01:02:53','2026-11-09 01:28:25','2026-11-09 01:28:25','YOrtVOgXgfdkpQ==','51e428746fc6a8efd2b527d6','1b1dc253365b7a41736d7ed422f3dad0','ldXFzWK3','644e07adb866223a49b7731b','1ff4fb130ffe1af1eadb31229203e7ea'),
(5,12,4,'x2bG','4d53a9c1dcbe305a0ce0b2e8','ebac59d1f4acf602520ba018c0d1fb7d','0e3yNPgGeYF/N4fQQyY=','0ab2434ce120f68d4e063bd6','6bafdd49be1cc8f73986b9bb7d9b5d6a','56FhWk+ysXYZ/w==','513df3b3b6f9da0207e16934','08843b95ae0472acbc3ea7493b3ef8a7',NULL,NULL,'2026-11-09 01:25:16','2026-11-09 01:28:26','2026-11-09 01:28:26','JItCFjRmttRk7w==','6b138ee55ca61ccab7784ffd','0787cd2a18f39047338819170e3f747c','c0Oefg==','e6d137e3a299b249160912a8','d1c2b6de867b972306bff519a67e4207');
/*!40000 ALTER TABLE `residents` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(60) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES
(1,'Super Admin','Administrasi teknis platform.','2026-11-08 01:41:02','2026-11-08 01:41:02'),
(2,'BUMDes Manager','Pengelolaan operasional BUMDes.','2026-11-08 01:41:02','2026-11-08 01:41:02'),
(3,'BUMDes Operator','Pendampingan layanan digital BUMDes.','2026-11-08 01:41:02','2026-11-08 01:41:02'),
(4,'Village Admin','Pengelolaan administrasi desa.','2026-11-08 01:41:02','2026-11-08 01:41:02'),
(5,'RT','Petugas wilayah RT yang ditugaskan.','2026-11-08 01:41:02','2026-11-08 01:41:02'),
(6,'RW','Petugas wilayah RW yang ditugaskan.','2026-11-08 01:41:02','2026-11-08 01:41:02'),
(7,'Resident','Warga yang menggunakan layanan.','2026-11-08 01:41:02','2026-11-08 01:41:02'),
(8,'Seller','Penjual yang telah disetujui.','2026-11-08 01:41:02','2026-11-08 01:41:02'),
(9,'Village Head','Pejabat penandatangan kepala desa.','2026-11-08 01:41:02','2026-11-08 01:41:02');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `seller_documents`
--

DROP TABLE IF EXISTS `seller_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `seller_documents` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `seller_id` bigint(20) unsigned NOT NULL,
  `uploaded_by` bigint(20) unsigned NOT NULL,
  `document_type` varchar(60) NOT NULL,
  `private_file_path` varchar(500) NOT NULL,
  `original_filename` varchar(255) DEFAULT NULL,
  `mime_type` varchar(120) NOT NULL,
  `file_size` bigint(20) unsigned NOT NULL,
  `file_hash` char(64) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `seller_documents_uploaded_by_foreign` (`uploaded_by`),
  KEY `seller_documents_seller_id_uploaded_at_index` (`seller_id`,`uploaded_at`),
  CONSTRAINT `seller_documents_seller_id_foreign` FOREIGN KEY (`seller_id`) REFERENCES `sellers` (`id`),
  CONSTRAINT `seller_documents_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `seller_documents`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `seller_documents` WRITE;
/*!40000 ALTER TABLE `seller_documents` DISABLE KEYS */;
/*!40000 ALTER TABLE `seller_documents` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `seller_status_events`
--

DROP TABLE IF EXISTS `seller_status_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `seller_status_events` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `seller_id` bigint(20) unsigned NOT NULL,
  `actor_id` bigint(20) unsigned DEFAULT NULL,
  `from_status` varchar(30) DEFAULT NULL,
  `to_status` varchar(30) NOT NULL,
  `reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `seller_status_events_actor_id_foreign` (`actor_id`),
  KEY `seller_status_events_seller_id_created_at_index` (`seller_id`,`created_at`),
  CONSTRAINT `seller_status_events_actor_id_foreign` FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `seller_status_events_seller_id_foreign` FOREIGN KEY (`seller_id`) REFERENCES `sellers` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `seller_status_events`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `seller_status_events` WRITE;
/*!40000 ALTER TABLE `seller_status_events` DISABLE KEYS */;
/*!40000 ALTER TABLE `seller_status_events` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `sellers`
--

DROP TABLE IF EXISTS `sellers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sellers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `business_name` varchar(160) NOT NULL,
  `business_description` text DEFAULT NULL,
  `business_address` text DEFAULT NULL,
  `contact_phone` text DEFAULT NULL,
  `verification_status` varchar(30) NOT NULL DEFAULT 'pending',
  `reviewed_by` bigint(20) unsigned DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sellers_user_id_unique` (`user_id`),
  KEY `sellers_reviewed_by_foreign` (`reviewed_by`),
  KEY `sellers_verification_status_index` (`verification_status`),
  CONSTRAINT `sellers_reviewed_by_foreign` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`),
  CONSTRAINT `sellers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `chk_sellers_status` CHECK (`verification_status` in ('pending','under_review','approved','rejected','suspended','inactive'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sellers`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `sellers` WRITE;
/*!40000 ALTER TABLE `sellers` DISABLE KEYS */;
/*!40000 ALTER TABLE `sellers` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `service_requests`
--

DROP TABLE IF EXISTS `service_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `service_requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `resident_id` bigint(20) unsigned NOT NULL,
  `service_type` enum('complaint','technical_support','billing_question','wifi_access','other') NOT NULL,
  `subject` varchar(180) NOT NULL,
  `description` text NOT NULL,
  `status` enum('submitted','in_progress','waiting_resident','resolved','rejected','closed') NOT NULL DEFAULT 'submitted',
  `assigned_to` bigint(20) unsigned DEFAULT NULL,
  `resolution_note` text DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `service_requests_assigned_to_foreign` (`assigned_to`),
  KEY `idx_service_requests_status` (`status`,`created_at`),
  KEY `idx_service_requests_resident` (`resident_id`,`created_at`),
  CONSTRAINT `service_requests_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `service_requests_resident_id_foreign` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `service_requests`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `service_requests` WRITE;
/*!40000 ALTER TABLE `service_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `service_requests` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES
('7G5U7HQIMeET51JfMEx7WNZ0m7H9eIQiRdQR3zry',NULL,'127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.134.0 Chrome/148.0.7778.280 Electron/42.8.1 Safari/537.36','ZXlKcGRpSTZJa1JOWTNOTFNuZE1ZbXhVZW5sMFlWQm5ibXBqTTNjOVBTSXNJblpoYkhWbElqb2liRUV5VjFWYWFtOHlOamcyV2xKQlV6VkxlbFoyVm10b1VERlpRWEkyTlhkTVkwVXdiRzlzWjBoUVRXTXdibTVIUkVocVdYSklPQ3R6VVhwaE0wMXdUVWROZDJwV1QxRm5lRmRDUVZOcWVuZEpObXhtUWxCTVpIUlRPV2RNV1dSRlltUnFWMVJ4YmxoWVNHODNSMDVqWlhrNVFXRm1aekZFTml0WlExVk5SbmRRYmxwNlpEaHViWFZYYzNoUVlXSmhTV2R2V1hZeVJ6WXhTRU5uT0c0MFJWcG1PSEZPWTFrek1HRlVjMlZSV2k5M01DOUhjMGRtZUZSTlRrdExTWEp5TUd4dlJXVndjR3BwWmpkTGRFb3hWems1V0ZCdWR6MDlJaXdpYldGaklqb2lZV1U0WlRBMU1ETTRNbUpoTWpabVlUWmhZek15WldZeFpEVXpPR1ExWmpCaVkyVmxNekF4TlRNM1pHVmpZbUU1WmpRM05qQTBZVEkyWmpObE9HVTBZeUlzSW5SaFp5STZJaUo5',1794276381),
('aM5BAgJaSpJ1TuJ1IvpObvkd99r4WCYaHvldC4mp',16,'127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','ZXlKcGRpSTZJazlQVlVNdlZVb3JhVzgyTW1SbmJVcGtUMjFoYjNjOVBTSXNJblpoYkhWbElqb2lSbHBtYURWMU16Wk9VMWxDYm5JNVMyaHNlakJvU2l0a1FYZHBVREJ5YmtGSlZrMU9SMFVyZG5wS2IybDVTSGR2T1c1Q2NuRjFXV3hGWVROS2VqRjBSMU53U2pCNGQzTmtTM0pUVVhsWmEzUmlXbU5CT0V4WGIzUk9VbTFLU1hWQ1VFcFlXbWcwVEUxNlpUTktXSGRsYjJsYVpEWjVNR1IzWTA5VlZVdzVhVkZzU0hReE56VnVia0owU2tSQ2RGQklUbmxPZEhKaVQwdEpMMFYxYUdkbWVqZzFOazlVVmxoSmVUUklNamxtVkVoalEwSmthazFuYVU5b1ZsSmlObU56YkhvNFoyZE9hREZPU2pJMWIweHlTM04xWm5KNVNHUjBlblJxVERWNlNtWlVSbFIzTnpOc2NIWmhSbk5qU2t4UVdDOTVZMG92YjFoME5DODVLMDFQY2xoR05scFlaelJLT1d0eU1XdE9TbThyZEU4d1RsSmFlbGRFY21oeFJ6VkhNV1JrVVdScE9IQTRiMnBqZDJaSFZVMTRRV3QwVDBZMVJVdEtlVTFrTUhWVWNFOTBaMk54WXpsQk1sQlpRamQySzJwRVVEZDRka3RIWTNBNFJIbGhiRXRXUjJKWFR6QXhWVXhOYW5NMmFrbzVNamcwUlM4NE9GVlNUV2Q2TjJWTFMyaHdTbHAzUjFsblNUWjRjRGRVZVVwVmNIUkRSRlIxYUZORFkyZGhWV0lyVTI1elEwWk9MMVJqTkZoR0sweG1ibkUzUzFaUGFHOVhkRk4zZEdwNE5pdGhaM3BDVTNKQlVqazVOUzgyVWt0UWRHcHdWbkZVYm1JMWRuaENjMEZZZDAxMlRtTndiMHRqZW04OUlpd2liV0ZqSWpvaVlUbGlNalk0T1RJeE5URmxNbU0zWVdJd1ltVmxNekF5TVRsaU9UQmxaRFppTldGbE1UWmxNREF4WTJaak1XWmtPR1V5TVRNeU1UTTJaamRsWTJFeU15SXNJblJoWnlJNklpSjk=',1794247335),
('dm1HkZoAC6rcE7UAYAd3ugXR1ujZjkeutFlL3IwE',NULL,'127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.134.0 Chrome/148.0.7778.280 Electron/42.8.1 Safari/537.36','ZXlKcGRpSTZJa0Y1TWs4MVZ6bHJhbTV1ZUN0aFZXVXJUbmMzTTBFOVBTSXNJblpoYkhWbElqb2lURlptU0U0MWNFNWhSa3g2UmxJckszcHFlRU5zWmxSMWVtRlVRVEZ3V2xScFlteFNkRlpMY0dseWN6RjFTV2RoTWpOSFpsSkZka2hpS3pOVFlUbHBZMVZ1ZUVsVVpXbzFibTlwZUVjdmIyWkxXVU5YYW5wSFUybFlSMWxwTlRsdE9YaHpWblpHVEN0b1dHeDVORFJvV0ZOU1YxYzVNRnBVYjNGbFFVbGllR0pRV0UxalZGSlZlRE5UUW5GeldFcENNRzVQZERGdGVqQjBUR1JMUlZCeldYaGxUMDFFZDFZMFozbHlSVXQ0T1RkU2JFUkVibUZ6ZGtGQldrNUNjbEl5Y2xWMU1FZERZbXQ2TDNCeFFrNXpNSFpWZFVzNVVUMDlJaXdpYldGaklqb2lOREF3WWpCak9EY3hNakV4WTJabU5UaGxZelZrWmpVNE1qTTFOamMyTVdZMFltUTBaalU1T1dRelpUZ3lPR0ZrTVRSak1qaGpOV05rTURkak9HSm1PQ0lzSW5SaFp5STZJaUo5',1794247310),
('HAUyysqIyQGiZdxqbqTGr4CUQ1dLA88pV6gFkunT',10,'127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','ZXlKcGRpSTZJbUp3ZDJnd1RqY3JTMHRpTTBVek1ucEpNRlJQUmxFOVBTSXNJblpoYkhWbElqb2ljREVyTVM5b00wTmhZV1l6U0U1RVFpdHJhMk5hVlhwRWNrZHNhMWhSZWpkM1psWmhabGszVFhwQlRtSkxlbVEzY0ZWd01XUllOMDVQTDJsS0szSXdlbEJ6YmpKa1N6ZHpVRnBKZWxZclIyUTVabU5uZFdjdlJrWlNaR0VyY3poelkxaFplVnBIWlVwSFprMVpOa1JRVUhGWVNFTmtOa0YyVFhkb1ZqVTRVakpYTDJkS2JXVjNNMnR0ZWxkaFRrWm1ZVmxXUmpoUVUyeG9WbTVTWjNaQ2VVdFRNRkpqUmpCVmNVTlpWbUp2ZW10cFdFUkZjM1JYS3pWNVYzSnhjalpoZWxCbVdFOVJRaTlGSzFad1UzWmhRbTF3UzBaRk5GQnRUM1kyVVdGUVYxaHRRVEEzVUZGWEwwVnZTVFJNWmtWck9YSXJZVzVFUm10Q2MwbzJaemxyUTNCWk5EaDRXWGhzV2xoTk1uQk5jbTlxZVVSQlRFeElNVUZXVm1WT2MyVllhR1pDU2tNelpWUmFhRWRNVDA0elVXYzNkRU5CVVVOd05uTlJkVVZFTkdkTFlVSnNabE5OVlZsSFJXMVhkRWR2UTFoaGRGZDRjMDR5WlROMFRIVkljRE5YWld0WVdVOW9UekZUUkRsUmRrcG9jR0ZySzNCaFVIUkxOSEl6YUhkRGNYbEdaRkZxV0dOTlZYSkhMMmRGYlhaWE5XeFBhMmxCTkZSd2QzWXZjVWhaV2toU1JXWjVhVTU0YnowaUxDSnRZV01pT2lJeVlXUmhaR1ZpWlRVM1pXSXhPR1E0TlRSa1ptWm1NRFU0WkRrMU5qUXpZekJqWmpoa00yWXlaV0UxTlRVNE9XWmxPRE5qTUdJelpHRTNOemd3WmpFeUlpd2lkR0ZuSWpvaUluMD0=',1794246272),
('xxQtACFIvpuGsImRufC8u3B8b7a3ErnerrUQqpWZ',NULL,'127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36','ZXlKcGRpSTZJbVkyWjIxSWNsaDRWMG8zU21SRlZUTXJkMjVMV1hjOVBTSXNJblpoYkhWbElqb2lkbmhVUTJWRGRrd3laR3BKYUhnNFVqbEJaMmxGU1hCRWJYcGtOa3hzWTNCamJqZENlR2x6Y1VoTlpVaEJjbXcyTldOV1NqSTNjVWhEV2pOYVQwNUxSa3BaZUhSdksyVlpXV0pGY0RVMVp6bHJOWEV6T0ZKMVZYZE5RMDVWT1U1YVQwZFdPVkkwVDI5Uk9WcEdVMnB3U2pGRFRIRnlWV050VG1GTFdTdFlNMkYxWW1kRk5tTlBibFphWkc5TVRqQmhWVkJSVDFGeGVGTlBVVGhLYTB4Q05sbEVhRmsyU0U1SGIxUktiVkJVYUZRNVpuRlJialpxYUdJMlJqZG5XVnBoWmpGNEx6QlFXaXRYUTNwdE1FMXJaekpPVVVwc1p6MDlJaXdpYldGaklqb2laVFkyWlRabFpURmlNR1JrWkRaak5tRXpNalF3TW1VeFlXRmpaakZpTTJZMk16bG1OREpqWkRjMU5UVXpZemcyWlRKbE1UTTVabVExTmpFM056SmlOU0lzSW5SaFp5STZJaUo5',1794277617);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `subscription_invoices`
--

DROP TABLE IF EXISTS `subscription_invoices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `subscription_invoices` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `subscription_id` bigint(20) unsigned NOT NULL,
  `invoice_number` varchar(60) NOT NULL,
  `billing_period_start` date NOT NULL,
  `billing_period_end` date NOT NULL,
  `amount` decimal(14,2) NOT NULL,
  `due_date` date NOT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `status` enum('unpaid','pending','paid','overdue','cancelled') NOT NULL DEFAULT 'unpaid',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_invoice_subscription_period` (`subscription_id`,`billing_period_start`),
  UNIQUE KEY `subscription_invoices_invoice_number_unique` (`invoice_number`),
  KEY `idx_subscription_invoices_due` (`status`,`due_date`),
  CONSTRAINT `subscription_invoices_subscription_id_foreign` FOREIGN KEY (`subscription_id`) REFERENCES `internet_subscriptions` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subscription_invoices`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `subscription_invoices` WRITE;
/*!40000 ALTER TABLE `subscription_invoices` DISABLE KEYS */;
INSERT INTO `subscription_invoices` VALUES
(1,1,'INV-20261109-IU5PHXN9','2026-11-01','2026-11-30',100000000.00,'2026-11-16','2026-11-09 06:52:04','paid','2026-11-09 06:36:08','2026-11-09 06:52:04');
/*!40000 ALTER TABLE `subscription_invoices` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `user_roles`
--

DROP TABLE IF EXISTS `user_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `role_id` bigint(20) unsigned NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `assigned_by` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_user_roles_pair` (`user_id`,`role_id`),
  KEY `user_roles_role_id_foreign` (`role_id`),
  KEY `user_roles_assigned_by_foreign` (`assigned_by`),
  CONSTRAINT `user_roles_assigned_by_foreign` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `user_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  CONSTRAINT `user_roles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_roles`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `user_roles` WRITE;
/*!40000 ALTER TABLE `user_roles` DISABLE KEYS */;
INSERT INTO `user_roles` VALUES
(2,4,1,'2026-11-08 11:26:43',NULL),
(21,10,7,'2026-11-09 07:58:59',NULL),
(23,11,7,'2026-11-09 01:03:03',4),
(24,11,5,'2026-11-09 01:03:03',4),
(28,12,7,'2026-11-09 01:21:34',4),
(29,12,6,'2026-11-09 01:21:34',4),
(31,13,4,'2026-11-09 01:28:15',4),
(33,14,7,'2026-11-09 02:56:39',4),
(34,14,9,'2026-11-09 02:56:39',4),
(36,15,3,'2026-11-09 06:00:53',4),
(38,16,2,'2026-11-09 06:02:37',4);
/*!40000 ALTER TABLE `user_roles` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `username` varchar(60) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `account_status` enum('active','suspended','disabled','pending') NOT NULL DEFAULT 'pending',
  `phone_verified_at` timestamp NULL DEFAULT NULL,
  `phone_ciphertext` text DEFAULT NULL,
  `phone_nonce` varchar(64) DEFAULT NULL,
  `phone_tag` varchar(64) DEFAULT NULL,
  `name_ciphertext` text DEFAULT NULL,
  `name_nonce` varchar(64) DEFAULT NULL,
  `name_tag` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `uq_users_username` (`username`),
  KEY `idx_users_status` (`account_status`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES
(4,'account-admin1','kyzoaja47@gmail.com','2026-11-08 04:30:05','$2y$12$kaHobdHZbDVH/etLcI.s2O49M9epLUkAAAGDVwd8UqG/rCbN48tFK','fXsGbaJjFfXEMOIEhu99Jkn7X2HGkKjcUiry9ejGFCssM816JQBv99RP8QvJ','2026-11-08 04:26:43','2026-11-08 04:30:05','admin1',NULL,'active',NULL,NULL,NULL,NULL,NULL,NULL,NULL),
(10,'account-test1','test1@gmail.com','2026-11-09 00:59:35','$2y$12$QP5DbFJgCh/M2oFd1jPQ9evYOhRjZO5QG90dBkBMsYMkPu6RRkbWO',NULL,'2026-11-09 00:58:59','2026-11-09 00:59:48','test1',NULL,'active',NULL,NULL,NULL,NULL,NULL,NULL,NULL),
(11,'account-testrt','testrt@gmail.com','2026-11-09 01:01:40','$2y$12$44q6Ae2PlJph9CdmaCHwGOm.uYWcGcFtbDDMd6UeKj30X/KPxDoQ.',NULL,'2026-11-09 01:01:28','2026-11-09 01:01:48','testrt',NULL,'active',NULL,NULL,NULL,NULL,NULL,NULL,NULL),
(12,'account-testrw','testrw@gmail.com','2026-11-09 01:19:43','$2y$12$cf8xtOKTiUlI3YEQOj9syuI1H4Qlb311K2HBvbeSdxq5TZgfDiQhO',NULL,'2026-11-09 01:04:06','2026-11-09 01:19:52','testrw',NULL,'active',NULL,NULL,NULL,NULL,NULL,NULL,NULL),
(13,'account-testadmin','testadmin@gmail.com','2026-11-09 01:27:53','$2y$12$58rd/7s3PKDrfr2CAPx1Feb8h73Ak5wZCl.Xa5RTfEWgTrS3UHL..',NULL,'2026-11-09 01:27:14','2026-11-09 01:27:59','testadmin',NULL,'active',NULL,NULL,NULL,NULL,NULL,NULL,NULL),
(14,'account-kades','kades@gmail.com','2026-11-09 02:40:17','$2y$12$3fcXN9e3scaPKFWWtAQqxOMxmWsQGkItiY2cyUbJi02WG5AWP3.Py',NULL,'2026-11-09 02:40:01','2026-11-09 02:40:24','kades',NULL,'active',NULL,NULL,NULL,NULL,NULL,NULL,NULL),
(15,'account-bumdes','bumdes@gmail.com','2026-11-09 06:00:27','$2y$12$Nrccr2SLeEfYQg.I/ZLtMu/1ELxMz0IbM2RcL5sc4FV24jUJUlvRC',NULL,'2026-11-09 06:00:11','2026-11-09 06:00:38','bumdes',NULL,'active',NULL,NULL,NULL,NULL,NULL,NULL,NULL),
(16,'account-bumdesm','bumdesm@gmail.com','2026-11-09 06:02:25','$2y$12$5tmbzVCX2CoAhNEla3Io/upEVsuBw/WIXLvi0JjcuKqZMxBq.kohW',NULL,'2026-11-09 06:02:15','2026-11-09 06:02:32','bumdesm',NULL,'active',NULL,NULL,NULL,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `villages`
--

DROP TABLE IF EXISTS `villages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `villages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(30) NOT NULL,
  `name` varchar(150) NOT NULL,
  `address` text DEFAULT NULL,
  `postal_code` varchar(10) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `villages_code_unique` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `villages`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `villages` WRITE;
/*!40000 ALTER TABLE `villages` DISABLE KEYS */;
INSERT INTO `villages` VALUES
(2,'DESA-01','yo nda tau',NULL,NULL,NULL,NULL,1,'2026-11-09 00:57:38','2026-11-09 00:57:38');
/*!40000 ALTER TABLE `villages` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `wifi_access_logs`
--

DROP TABLE IF EXISTS `wifi_access_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `wifi_access_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `wifi_network_id` bigint(20) unsigned NOT NULL,
  `subscription_id` bigint(20) unsigned DEFAULT NULL,
  `authentication_status` enum('success','failed','blocked','expired') NOT NULL,
  `session_reference` varchar(150) DEFAULT NULL,
  `client_identifier_hash` char(64) DEFAULT NULL,
  `ip_address` varbinary(16) DEFAULT NULL,
  `connected_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `disconnected_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `wifi_access_logs_subscription_id_foreign` (`subscription_id`),
  KEY `idx_wifi_access_user_time` (`user_id`,`connected_at`),
  KEY `idx_wifi_access_network_time` (`wifi_network_id`,`connected_at`),
  CONSTRAINT `wifi_access_logs_subscription_id_foreign` FOREIGN KEY (`subscription_id`) REFERENCES `internet_subscriptions` (`id`),
  CONSTRAINT `wifi_access_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `wifi_access_logs_wifi_network_id_foreign` FOREIGN KEY (`wifi_network_id`) REFERENCES `wifi_networks` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wifi_access_logs`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `wifi_access_logs` WRITE;
/*!40000 ALTER TABLE `wifi_access_logs` DISABLE KEYS */;
INSERT INTO `wifi_access_logs` VALUES
(1,10,1,NULL,'success','43d7dcd8-1ad4-4ee1-8a10-f389fd5d193e',NULL,NULL,'2026-11-09 06:21:40',NULL);
/*!40000 ALTER TABLE `wifi_access_logs` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `wifi_networks`
--

DROP TABLE IF EXISTS `wifi_networks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `wifi_networks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `network_type` enum('public_wifi','residential','office','other') NOT NULL,
  `location` varchar(255) NOT NULL,
  `ssid` varchar(100) NOT NULL,
  `status` enum('active','inactive','maintenance') NOT NULL DEFAULT 'active',
  `access_policy` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`access_policy`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_wifi_networks_type_status` (`network_type`,`status`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wifi_networks`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `wifi_networks` WRITE;
/*!40000 ALTER TABLE `wifi_networks` DISABLE KEYS */;
INSERT INTO `wifi_networks` VALUES
(1,'taman','public_wifi','taman1','taman1wifi','active','{\"eligibility\":\"authenticated_residents\",\"session_minutes\":60,\"terms\":\"Gunakan jaringan dengan bertanggung jawab.\",\"terms_version\":1}','2026-11-09 06:15:44','2026-11-09 06:15:44');
/*!40000 ALTER TABLE `wifi_networks` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2026-11-10  9:32:23
