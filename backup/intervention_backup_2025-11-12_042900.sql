mysqldump: [Warning] Using a password on the command line interface can be insecure.
-- MySQL dump 10.13  Distrib 8.0.43, for Linux (x86_64)
--
-- Host: localhost    Database: intervention_db
-- ------------------------------------------------------
-- Server version	8.0.43-0ubuntu0.22.04.2

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_logs` (
  `log_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `action` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `table_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `record_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `old_values` json DEFAULT NULL,
  `new_values` json DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_id`),
  KEY `idx_user_action` (`user_id`,`action`),
  KEY `idx_table_record` (`table_name`,`record_id`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
INSERT INTO `audit_logs` VALUES (1,1,'login','users','1',NULL,NULL,'41.66.199.137','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0','2025-11-07 20:05:37'),(2,1,'logout','users','1',NULL,NULL,'41.66.199.137','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0','2025-11-07 22:05:41'),(3,1,'login','users','1',NULL,NULL,'41.66.199.137','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0','2025-11-08 03:50:39'),(4,NULL,'CREATE','students','3A1-AO001',NULL,'{\"class\": \"3A1\", \"last_name\": \"Osei\", \"first_name\": \"Ama\"}','','','2025-11-08 04:16:26'),(5,NULL,'CREATE','students','3S1-BY001',NULL,'{\"class\": \"3S1\", \"last_name\": \"Boateng\", \"first_name\": \"Yaw\"}','','','2025-11-08 04:18:55'),(6,NULL,'update','students','3S1-BY001','{\"id\": 11, \"class\": \"3S1\", \"house\": \"3\", \"gender\": \"male\", \"balance\": \"0.00\", \"due_date\": null, \"is_active\": 1, \"last_name\": \"Boateng\", \"amount_due\": \"0.00\", \"created_at\": \"2025-11-08 04:18:55\", \"fee_status\": null, \"first_name\": \"Yaw\", \"student_id\": \"3S1-BY001\", \"updated_at\": \"2025-11-08 04:18:55\", \"amount_paid\": \"0.00\"}','{\"class\": \"3S1\", \"house\": \"4\", \"gender\": \"male\", \"last_name\": \"Boateng\", \"first_name\": \"Yaw\"}','','','2025-11-08 04:18:55'),(7,1,'CREATE','students','3B1-JM001',NULL,'{\"class\": \"3B1\", \"last_name\": \"Mensah\", \"first_name\": \"James\"}','41.66.199.137','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0','2025-11-08 04:22:32'),(8,1,'CREATE','students','3A2-AA001',NULL,'{\"class\": \"3A2\", \"last_name\": \"Adjei\", \"first_name\": \"Akua\"}','','','2025-11-08 04:26:28'),(9,1,'CREATE','student_fees','3A2-AA001',NULL,'{\"due_date\": \"2025-12-08\", \"amount_due\": \"500.00\"}','','','2025-11-08 04:26:28'),(10,1,'create','users','2',NULL,'{\"role\": \"teacher\", \"contact\": \"0242168545\", \"fullname\": \"PETER ASIEDU\", \"password\": \"Knowledge77@\"}','41.66.199.137','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0','2025-11-08 04:36:21'),(11,1,'status_change','users','2','{\"is_active\": 1}','{\"is_active\": 0}','41.66.199.137','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0','2025-11-08 04:36:36'),(12,1,'UPDATE','users','2','\"User deactivated by admin\"',NULL,'41.66.199.137','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0','2025-11-08 04:36:36'),(13,1,'status_change','users','2','{\"is_active\": 0}','{\"is_active\": 1}','41.66.199.137','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0','2025-11-08 04:36:45'),(14,1,'UPDATE','users','2','\"User activated by admin\"',NULL,'41.66.199.137','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0','2025-11-08 04:36:45'),(15,2,'login','users','2',NULL,NULL,'41.66.199.137','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0','2025-11-08 04:37:52'),(16,2,'payment_record','payments','1',NULL,'{\"notes\": \"\", \"student_id\": \"3A2-AA001\", \"amount_paid\": \"200\", \"payment_date\": \"2025-11-08\", \"payment_method\": \"cash\", \"reference_number\": \"\"}','41.66.199.137','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0','2025-11-08 04:39:23'),(17,2,'logout','users','2',NULL,NULL,'41.66.199.137','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0','2025-11-08 04:49:50'),(18,1,'status_change','students','3B1-JM001','{\"is_active\": 1}','{\"is_active\": 0}','41.66.199.137','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0','2025-11-08 04:56:51'),(19,1,'status_change','students','3B1-JM001','{\"is_active\": 0}','{\"is_active\": 1}','41.66.199.137','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0','2025-11-08 04:56:59'),(20,1,'logout','users','1',NULL,NULL,'41.66.199.137','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0','2025-11-08 05:52:02'),(21,2,'login','users','2',NULL,NULL,'41.66.199.137','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0','2025-11-08 08:12:05'),(22,2,'logout','users','2',NULL,NULL,'41.66.199.137','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0','2025-11-08 08:40:31'),(23,2,'login','users','2',NULL,NULL,'41.66.199.137','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0','2025-11-08 08:40:42'),(24,2,'logout','users','2',NULL,NULL,'41.66.199.137','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0','2025-11-08 08:54:12'),(25,1,'login','users','1',NULL,NULL,'41.66.199.137','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0','2025-11-08 08:54:24'),(26,1,'fee_bulk_assign','student_fees','2',NULL,NULL,'41.66.199.137','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0','2025-11-08 08:55:01'),(27,1,'fee_bulk_assign','student_fees','3',NULL,NULL,'41.66.199.137','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0','2025-11-08 08:55:01'),(28,1,'fee_bulk_assign','student_fees','4',NULL,NULL,'41.66.199.137','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0','2025-11-08 08:55:01'),(29,1,'fee_bulk_assign','student_fees','5',NULL,NULL,'41.66.199.137','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0','2025-11-08 08:55:01'),(30,1,'fee_bulk_assign','student_fees','6',NULL,NULL,'41.66.199.137','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0','2025-11-08 08:55:01'),(31,1,'fee_bulk_assign','student_fees','7',NULL,NULL,'41.66.199.137','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0','2025-11-08 08:55:01'),(32,1,'fee_bulk_assign','student_fees','8',NULL,NULL,'41.66.199.137','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0','2025-11-08 08:55:01'),(33,1,'fee_bulk_assign','student_fees','9',NULL,NULL,'41.66.199.137','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0','2025-11-08 08:55:01'),(34,1,'fee_bulk_assign','student_fees','10',NULL,NULL,'41.66.199.137','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0','2025-11-08 08:55:01'),(35,1,'fee_bulk_assign','student_fees','11',NULL,NULL,'41.66.199.137','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0','2025-11-08 08:55:01'),(36,1,'fee_bulk_assign','student_fees','12',NULL,NULL,'41.66.199.137','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0','2025-11-08 08:55:01'),(37,1,'fee_bulk_assign','student_fees','13',NULL,NULL,'41.66.199.137','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0','2025-11-08 08:55:01'),(38,1,'UPDATE','settings','default_fee_due_days','\"Setting default_fee_due_days updated to 30\"',NULL,'41.66.199.137','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0','2025-11-08 08:56:20'),(39,1,'UPDATE','settings','default_student_fee_amount','\"Setting default_student_fee_amount updated to 500.00\"',NULL,'41.66.199.137','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0','2025-11-08 08:56:20'),(40,1,'UPDATE','settings','academic_year','\"Setting academic_year updated to 2024-2025\"',NULL,'41.66.199.137','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0','2025-11-08 08:56:20'),(41,1,'UPDATE','settings','currency','\"Setting currency updated to GHS\"',NULL,'41.66.199.137','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0','2025-11-08 08:56:20'),(42,1,'UPDATE','settings','current_term','\"Setting current_term updated to 1\"',NULL,'41.66.199.137','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0','2025-11-08 08:56:20'),(43,1,'UPDATE','settings','school_name','\"Setting school_name updated to Your School Name\"',NULL,'41.66.199.137','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0','2025-11-08 08:56:20'),(44,1,'UPDATE','settings','sms_enabled','\"Setting sms_enabled updated to false\"',NULL,'41.66.199.137','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0','2025-11-08 08:56:20'),(45,1,'UPDATE','settings','payment_reminder_days','\"Setting payment_reminder_days updated to 7\"',NULL,'41.66.199.137','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0','2025-11-08 08:56:20'),(46,1,'UPDATE','settings','max_login_attempts','\"Setting max_login_attempts updated to 5\"',NULL,'41.66.199.137','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0','2025-11-08 08:56:20'),(47,1,'UPDATE','settings','rate_limit_requests','\"Setting rate_limit_requests updated to 100\"',NULL,'41.66.199.137','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0','2025-11-08 08:56:20'),(48,1,'UPDATE','settings','session_timeout_minutes','\"Setting session_timeout_minutes updated to 120\"',NULL,'41.66.199.137','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0','2025-11-08 08:56:20'),(49,2,'login','users','2',NULL,NULL,'41.66.199.137','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0','2025-11-08 08:57:33'),(50,1,'login','users','1',NULL,NULL,'41.66.199.196','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0','2025-11-08 12:14:57'),(51,2,'login','users','2',NULL,NULL,'41.66.199.196','Mozilla/5.0 (Linux; Android 11; SAMSUNG SM-G973U) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/14.2 Chrome/87.0.4280.141 Mobile Safari/537.36','2025-11-08 14:04:14'),(52,2,'logout','users','2',NULL,NULL,'41.66.199.196','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0','2025-11-08 14:09:43'),(53,2,'login','users','2',NULL,NULL,'41.66.199.196','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36','2025-11-08 14:10:31'),(54,2,'login','users','2',NULL,NULL,'41.66.199.196','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0','2025-11-08 14:11:36'),(55,1,'logout','users','1',NULL,NULL,'41.66.199.196','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0','2025-11-08 14:16:29'),(56,1,'login','users','1',NULL,NULL,'41.66.199.196','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0','2025-11-08 14:16:56'),(57,2,'logout','users','2',NULL,NULL,'41.66.199.117','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0','2025-11-08 16:15:15'),(58,1,'logout','users','1',NULL,NULL,'41.66.199.117','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0','2025-11-08 16:21:22');
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `classes`
--

DROP TABLE IF EXISTS `classes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `classes` (
  `class_id` int NOT NULL AUTO_INCREMENT,
  `program_id` int NOT NULL,
  `level` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `class_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`class_id`),
  UNIQUE KEY `unique_class` (`program_id`,`level`,`class_name`),
  CONSTRAINT `classes_ibfk_1` FOREIGN KEY (`program_id`) REFERENCES `programs` (`program_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=104 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `classes`
--

LOCK TABLES `classes` WRITE;
/*!40000 ALTER TABLE `classes` DISABLE KEYS */;
INSERT INTO `classes` VALUES (7,5,'SHS 1','1AG1','2025-11-08 04:01:55','2025-11-08 04:01:55'),(8,5,'SHS 1','1AG2','2025-11-08 04:01:55','2025-11-08 04:01:55'),(9,5,'SHS 2','2AG','2025-11-08 04:01:55','2025-11-08 04:01:55'),(10,5,'SHS 3','3AG','2025-11-08 04:01:55','2025-11-08 04:01:55'),(11,2,'SHS 1','1A1','2025-11-08 04:01:55','2025-11-08 04:01:55'),(12,2,'SHS 1','1A2','2025-11-08 04:01:55','2025-11-08 04:01:55'),(13,2,'SHS 1','1A3','2025-11-08 04:01:55','2025-11-08 04:01:55'),(14,2,'SHS 1','1A4','2025-11-08 04:01:55','2025-11-08 04:01:55'),(15,2,'SHS 1','1A5','2025-11-08 04:01:55','2025-11-08 04:01:55'),(16,2,'SHS 1','1A6','2025-11-08 04:01:55','2025-11-08 04:01:55'),(17,2,'SHS 1','1A7','2025-11-08 04:01:55','2025-11-08 04:01:55'),(18,2,'SHS 1','1A8','2025-11-08 04:01:55','2025-11-08 04:01:55'),(19,2,'SHS 1','1A56','2025-11-08 04:01:55','2025-11-08 04:01:55'),(20,2,'SHS 1','1A10','2025-11-08 04:01:55','2025-11-08 04:01:55'),(21,2,'SHS 1','1A11','2025-11-08 04:01:55','2025-11-08 04:01:55'),(22,2,'SHS 1','1A12','2025-11-08 04:01:55','2025-11-08 04:01:55'),(23,2,'SHS 2','2A1','2025-11-08 04:01:55','2025-11-08 04:01:55'),(24,2,'SHS 2','2A2','2025-11-08 04:01:55','2025-11-08 04:01:55'),(25,2,'SHS 2','2A3','2025-11-08 04:01:55','2025-11-08 04:01:55'),(26,2,'SHS 2','2A4','2025-11-08 04:01:55','2025-11-08 04:01:55'),(27,2,'SHS 2','2A5','2025-11-08 04:01:55','2025-11-08 04:01:55'),(28,2,'SHS 2','2A6','2025-11-08 04:01:55','2025-11-08 04:01:55'),(29,2,'SHS 2','2A7','2025-11-08 04:01:55','2025-11-08 04:01:55'),(30,2,'SHS 2','2A8','2025-11-08 04:01:55','2025-11-08 04:01:55'),(31,2,'SHS 2','2A9','2025-11-08 04:01:55','2025-11-08 04:01:55'),(32,2,'SHS 2','2A10','2025-11-08 04:01:55','2025-11-08 04:01:55'),(33,2,'SHS 2','2A11','2025-11-08 04:01:55','2025-11-08 04:01:55'),(47,1,'SHS 1','1HE1','2025-11-08 04:01:55','2025-11-08 04:01:55'),(48,1,'SHS 1','1HE2','2025-11-08 04:01:55','2025-11-08 04:01:55'),(49,1,'SHS 1','1HE3','2025-11-08 04:01:55','2025-11-08 04:01:55'),(50,1,'SHS 1','1HE4','2025-11-08 04:01:55','2025-11-08 04:01:55'),(51,1,'SHS 2','2HE1','2025-11-08 04:01:55','2025-11-08 04:01:55'),(52,1,'SHS 2','2HE2','2025-11-08 04:01:55','2025-11-08 04:01:55'),(53,1,'SHS 2','2HE3','2025-11-08 04:01:55','2025-11-08 04:01:55'),(54,1,'SHS 2','2HE4','2025-11-08 04:01:55','2025-11-08 04:01:55'),(55,1,'SHS 3','3HE1','2025-11-08 04:01:55','2025-11-08 04:01:55'),(56,1,'SHS 3','3HE2','2025-11-08 04:01:55','2025-11-08 04:01:55'),(57,1,'SHS 3','3HE3','2025-11-08 04:01:55','2025-11-08 04:01:55'),(58,1,'SHS 3','3HE4','2025-11-08 04:01:55','2025-11-08 04:01:55'),(59,4,'SHS 1','1S1','2025-11-08 04:01:55','2025-11-08 04:01:55'),(60,4,'SHS 1','1S2','2025-11-08 04:01:55','2025-11-08 04:01:55'),(61,4,'SHS 1','1S3','2025-11-08 04:01:55','2025-11-08 04:01:55'),(62,4,'SHS 2','2S1','2025-11-08 04:01:55','2025-11-08 04:01:55'),(63,4,'SHS 2','2S2','2025-11-08 04:01:55','2025-11-08 04:01:55'),(64,4,'SHS 2','2S3','2025-11-08 04:01:55','2025-11-08 04:01:55'),(65,4,'SHS 3','3S1','2025-11-08 04:01:55','2025-11-08 04:01:55'),(66,4,'SHS 3','3S2','2025-11-08 04:01:55','2025-11-08 04:01:55'),(67,4,'SHS 3','3S3','2025-11-08 04:01:55','2025-11-08 04:01:55'),(68,2,'SHS 3','3A1','2025-11-08 04:01:55','2025-11-08 04:01:55'),(69,2,'SHS 3','3A2','2025-11-08 04:01:55','2025-11-08 04:01:55'),(70,2,'SHS 3','3A3','2025-11-08 04:01:55','2025-11-08 04:01:55'),(71,2,'SHS 3','3A4','2025-11-08 04:01:55','2025-11-08 04:01:55'),(72,2,'SHS 3','3A5','2025-11-08 04:01:55','2025-11-08 04:01:55'),(73,2,'SHS 3','3A6','2025-11-08 04:01:55','2025-11-08 04:01:55'),(74,2,'SHS 3','3A7','2025-11-08 04:01:55','2025-11-08 04:01:55'),(75,2,'SHS 3','3A8','2025-11-08 04:01:55','2025-11-08 04:01:55'),(76,2,'SHS 3','3A9','2025-11-08 04:01:55','2025-11-08 04:01:55'),(77,2,'SHS 3','3A10','2025-11-08 04:01:55','2025-11-08 04:01:55'),(78,2,'SHS 3','3A11','2025-11-08 04:01:55','2025-11-08 04:01:55'),(79,2,'SHS 3','3A12','2025-11-08 04:01:55','2025-11-08 04:01:55'),(80,3,'SHS 1','1B1','2025-11-08 04:01:55','2025-11-08 04:01:55'),(81,3,'SHS 1','1B2','2025-11-08 04:01:55','2025-11-08 04:01:55'),(82,3,'SHS 2','2B1','2025-11-08 04:01:55','2025-11-08 04:01:55'),(83,3,'SHS 2','2B2','2025-11-08 04:01:55','2025-11-08 04:01:55'),(84,3,'SHS 3','3B1','2025-11-08 04:01:55','2025-11-08 04:01:55'),(85,3,'SHS 3','3B2','2025-11-08 04:01:55','2025-11-08 04:01:55'),(86,2,'SHS 1','1A9','2025-11-08 04:01:55','2025-11-08 04:01:55'),(89,3,'SHS 1','1TS3','2025-11-08 04:01:55','2025-11-08 04:01:55'),(103,2,'SHS 2','2A3C','2025-11-08 04:01:55','2025-11-08 04:01:55');
/*!40000 ALTER TABLE `classes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `login_attempts`
--

DROP TABLE IF EXISTS `login_attempts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `login_attempts` (
  `attempt_id` int NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attempt_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `success` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`attempt_id`),
  KEY `idx_ip_time` (`ip_address`,`attempt_time`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `login_attempts`
--

LOCK TABLES `login_attempts` WRITE;
/*!40000 ALTER TABLE `login_attempts` DISABLE KEYS */;
INSERT INTO `login_attempts` VALUES (13,'41.66.199.196','0242168545','2025-11-08 14:04:14',1),(14,'41.66.199.196','0242168545','2025-11-08 14:10:31',1),(15,'41.66.199.196','0242168545','2025-11-08 14:11:36',1),(16,'41.66.199.196','0244000000','2025-11-08 14:16:42',1),(17,'41.66.199.196','0244000000','2025-11-08 14:16:56',1);
/*!40000 ALTER TABLE `login_attempts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notification_logs`
--

DROP TABLE IF EXISTS `notification_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notification_logs` (
  `log_id` int NOT NULL AUTO_INCREMENT,
  `notification_id` int NOT NULL,
  `delivery_method` enum('sms') COLLATE utf8mb4_unicode_ci NOT NULL,
  `delivery_status` enum('pending','sent','failed','delivered') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `recipient_contact` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sent_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `error_message` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`log_id`),
  KEY `notification_id` (`notification_id`),
  KEY `idx_status` (`delivery_status`),
  CONSTRAINT `notification_logs_ibfk_1` FOREIGN KEY (`notification_id`) REFERENCES `notifications` (`notification_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notification_logs`
--

LOCK TABLES `notification_logs` WRITE;
/*!40000 ALTER TABLE `notification_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `notification_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `notification_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `parent_id` int DEFAULT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `notification_type` enum('payment_reminder','payment_received','general') COLLATE utf8mb4_unicode_ci NOT NULL,
  `priority` enum('low','medium','high') COLLATE utf8mb4_unicode_ci DEFAULT 'medium',
  `is_read` tinyint(1) DEFAULT '0',
  `sent_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `read_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`notification_id`),
  KEY `idx_user_read` (`user_id`,`is_read`),
  KEY `idx_parent_read` (`parent_id`,`is_read`),
  KEY `idx_sent_at` (`sent_at`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `parents` (`parent_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `parents`
--

DROP TABLE IF EXISTS `parents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `parents` (
  `parent_id` int NOT NULL AUTO_INCREMENT,
  `fullname` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `relationship` enum('father','mother','guardian','other') COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`parent_id`),
  KEY `idx_contact` (`contact`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `parents`
--

LOCK TABLES `parents` WRITE;
/*!40000 ALTER TABLE `parents` DISABLE KEYS */;
/*!40000 ALTER TABLE `parents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payments` (
  `payment_id` int NOT NULL AUTO_INCREMENT,
  `fee_id` int NOT NULL,
  `student_id` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL,
  `payment_method` enum('cash','mobile_money','others') COLLATE utf8mb4_unicode_ci NOT NULL,
  `reference_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_date` date NOT NULL,
  `received_by` int NOT NULL,
  `receipt_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`payment_id`),
  UNIQUE KEY `receipt_number` (`receipt_number`),
  KEY `received_by` (`received_by`),
  KEY `idx_fee_id` (`fee_id`),
  KEY `idx_student` (`student_id`),
  KEY `idx_payment_date` (`payment_date`),
  KEY `idx_receipt` (`receipt_number`),
  CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`fee_id`) REFERENCES `student_fees` (`fee_id`) ON DELETE CASCADE,
  CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE,
  CONSTRAINT `payments_ibfk_3` FOREIGN KEY (`received_by`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
INSERT INTO `payments` VALUES (1,1,'3A2-AA001',200.00,'cash','','2025-11-08',2,'RCP202511081822','','2025-11-08 04:39:23');
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `programs`
--

DROP TABLE IF EXISTS `programs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `programs` (
  `program_id` int NOT NULL AUTO_INCREMENT,
  `program_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`program_id`),
  UNIQUE KEY `unique_program` (`program_name`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `programs`
--

LOCK TABLES `programs` WRITE;
/*!40000 ALTER TABLE `programs` DISABLE KEYS */;
INSERT INTO `programs` VALUES (1,'HOME ECONS','2025-11-08 04:01:41','2025-11-08 04:01:41'),(2,'GENERAL ARTS','2025-11-08 04:01:41','2025-11-08 04:01:41'),(3,'BUSINESS','2025-11-08 04:01:41','2025-11-08 04:01:41'),(4,'SCIENCE','2025-11-08 04:01:41','2025-11-08 04:01:41'),(5,'GENERAL AGRIC','2025-11-08 04:01:41','2025-11-08 04:01:41');
/*!40000 ALTER TABLE `programs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rate_limits`
--

DROP TABLE IF EXISTS `rate_limits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rate_limits` (
  `limit_id` int NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `endpoint` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `request_count` int DEFAULT '1',
  `window_start` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`limit_id`),
  UNIQUE KEY `unique_ip_endpoint` (`ip_address`,`endpoint`),
  KEY `idx_window_start` (`window_start`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rate_limits`
--

LOCK TABLES `rate_limits` WRITE;
/*!40000 ALTER TABLE `rate_limits` DISABLE KEYS */;
/*!40000 ALTER TABLE `rate_limits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `session_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` timestamp NOT NULL,
  `last_activity` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`session_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_expires_at` (`expires_at`),
  CONSTRAINT `sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('2d774d94102381f2256851cfab57e5c33f4173f3ce4bbe0825e064df3b6ae60e',1,'41.66.199.137','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0','2025-11-08 08:54:24','2025-11-08 10:54:24','2025-11-08 08:57:15'),('a85b1f7be7a2ffb46765df00d01dfccfd1f3033ad1fffed82b19c2d5a332947b',2,'41.66.199.196','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Mobile Safari/537.36','2025-11-08 14:10:31','2025-11-08 16:10:31','2025-11-08 14:26:40'),('e0aa7630cc65ce6661720cbb69829b366fb439cda38d4da1399be9d841481156',2,'41.66.199.137','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0','2025-11-08 08:57:33','2025-11-08 10:57:33','2025-11-08 09:28:09');
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `setting_id` int NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `setting_value` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `setting_type` enum('string','number','boolean','json') COLLATE utf8mb4_unicode_ci DEFAULT 'string',
  `description` text COLLATE utf8mb4_unicode_ci,
  `category` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`setting_id`),
  UNIQUE KEY `setting_key` (`setting_key`),
  KEY `updated_by` (`updated_by`),
  KEY `idx_category` (`category`),
  CONSTRAINT `settings_ibfk_1` FOREIGN KEY (`updated_by`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,'school_name','Your School Name','string','Official school name','general',NULL,'2025-11-08 08:56:20'),(2,'academic_year','2024-2025','string','Current academic year','general',NULL,'2025-11-08 08:56:20'),(3,'current_term','1','string','Current academic term','general',NULL,'2025-11-08 08:56:20'),(4,'currency','GHS','string','Currency code','general',NULL,'2025-11-08 08:56:20'),(5,'sms_enabled','false','boolean','Enable SMS notifications','notifications',NULL,'2025-11-08 08:56:20'),(6,'payment_reminder_days','7','number','Days before due date to send reminder','payments',NULL,'2025-11-08 08:56:20'),(7,'max_login_attempts','5','number','Maximum login attempts before lockout','security',NULL,'2025-11-08 08:56:20'),(8,'session_timeout_minutes','120','number','Session timeout in minutes','security',NULL,'2025-11-08 08:56:20'),(9,'rate_limit_requests','100','number','Maximum requests per IP per hour','security',NULL,'2025-11-08 08:56:20'),(10,'default_student_fee_amount','500.00','number','Default fee amount to assign to new students','fees',NULL,'2025-11-08 08:56:20'),(11,'default_fee_due_days','30','number','Number of days from enrollment for fee payment due date','fees',NULL,'2025-11-08 08:56:20');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `student_fees`
--

DROP TABLE IF EXISTS `student_fees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `student_fees` (
  `fee_id` int NOT NULL AUTO_INCREMENT,
  `student_id` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount_due` decimal(10,2) NOT NULL,
  `amount_paid` decimal(10,2) DEFAULT '0.00',
  `balance` decimal(10,2) GENERATED ALWAYS AS ((`amount_due` - `amount_paid`)) STORED,
  `due_date` date NOT NULL,
  `status` enum('pending','partial','paid','overdue') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`fee_id`),
  UNIQUE KEY `unique_student` (`student_id`),
  KEY `created_by` (`created_by`),
  KEY `idx_status` (`status`),
  KEY `idx_due_date` (`due_date`),
  CONSTRAINT `student_fees_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE,
  CONSTRAINT `student_fees_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_fees`
--

LOCK TABLES `student_fees` WRITE;
/*!40000 ALTER TABLE `student_fees` DISABLE KEYS */;
INSERT INTO `student_fees` (`fee_id`, `student_id`, `amount_due`, `amount_paid`, `due_date`, `status`, `created_by`, `created_at`, `updated_at`) VALUES (1,'3A2-AA001',500.00,200.00,'2025-12-08','partial',1,'2025-11-08 04:26:28','2025-11-08 04:39:23'),(2,'1A1-AA001',50.00,0.00,'2025-12-08','pending',1,'2025-11-08 08:55:01','2025-11-08 08:55:01'),(3,'3A1-AO001',50.00,0.00,'2025-12-08','pending',1,'2025-11-08 08:55:01','2025-11-08 08:55:01'),(4,'3AG-JM001',50.00,0.00,'2025-12-08','pending',1,'2025-11-08 08:55:01','2025-11-08 08:55:01'),(5,'3B1-JM001',50.00,0.00,'2025-12-08','pending',1,'2025-11-08 08:55:01','2025-11-08 08:55:01'),(6,'Class 1-DJ001',50.00,0.00,'2025-12-08','pending',1,'2025-11-08 08:55:01','2025-11-08 08:55:01'),(7,'2S1-JS001',50.00,0.00,'2025-12-08','pending',1,'2025-11-08 08:55:01','2025-11-08 08:55:01'),(8,'1B1-KO001',50.00,0.00,'2025-12-08','pending',1,'2025-11-08 08:55:01','2025-11-08 08:55:01'),(9,'1A1-KM001',50.00,0.00,'2025-12-08','pending',1,'2025-11-08 08:55:01','2025-11-08 08:55:01'),(10,'2A1-KM001',50.00,0.00,'2025-12-08','pending',1,'2025-11-08 08:55:01','2025-11-08 08:55:01'),(11,'3HE1-AKM001',50.00,0.00,'2025-12-08','pending',1,'2025-11-08 08:55:01','2025-11-08 08:55:01'),(12,'3B1-GS001',50.00,0.00,'2025-12-08','pending',1,'2025-11-08 08:55:01','2025-11-08 08:55:01'),(13,'3S1-BY001',50.00,0.00,'2025-12-08','pending',1,'2025-11-08 08:55:01','2025-11-08 08:55:01');
/*!40000 ALTER TABLE `student_fees` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `student_parents`
--

DROP TABLE IF EXISTS `student_parents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `student_parents` (
  `student_id` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `parent_id` int NOT NULL,
  `is_primary` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`student_id`,`parent_id`),
  KEY `parent_id` (`parent_id`),
  CONSTRAINT `student_parents_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE,
  CONSTRAINT `student_parents_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `parents` (`parent_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_parents`
--

LOCK TABLES `student_parents` WRITE;
/*!40000 ALTER TABLE `student_parents` DISABLE KEYS */;
/*!40000 ALTER TABLE `student_parents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `students`
--

DROP TABLE IF EXISTS `students`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `students` (
  `id` int NOT NULL AUTO_INCREMENT,
  `student_id` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `first_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gender` enum('male','female') COLLATE utf8mb4_unicode_ci NOT NULL,
  `class` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `house` enum('1','2','3','4') COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_student_id` (`student_id`),
  KEY `idx_class` (`class`),
  KEY `idx_house` (`house`),
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `students`
--

LOCK TABLES `students` WRITE;
/*!40000 ALTER TABLE `students` DISABLE KEYS */;
INSERT INTO `students` VALUES (1,'1A1-AA001','Ama','Asante','female','1A1','2',1,'2025-11-08 04:05:10','2025-11-08 04:05:10'),(2,'1A1-KM001','Kwame','Mensah','male','1A1','1',1,'2025-11-08 04:05:10','2025-11-08 04:05:10'),(3,'1B1-KO001','Kofi','Owusu','male','1B1','1',1,'2025-11-08 04:05:10','2025-11-08 04:05:10'),(4,'2A1-KM001','Kwame','Mensah','male','2A1','2',1,'2025-11-08 04:05:45','2025-11-08 04:05:45'),(5,'2S1-JS001','John','Smith','male','2S1','3',1,'2025-11-08 04:05:10','2025-11-08 04:05:10'),(6,'3A1-AO001','Ama','Osei','female','3A1','2',1,'2025-11-08 04:16:26','2025-11-08 04:16:26'),(7,'3AG-JM001','James','Mensah','male','3AG','1',1,'2025-11-08 04:14:12','2025-11-08 04:14:12'),(8,'3B1-GS001','Sam','Gyan','male','3B1','2',1,'2025-11-08 04:08:37','2025-11-08 04:08:37'),(9,'3HE1-AKM001','Mary Kate','Anderson','female','3HE1','4',1,'2025-11-08 04:05:10','2025-11-08 04:05:10'),(10,'Class 1-DJ001','John','Doe','male','Class 1','1',1,'2025-11-08 03:58:43','2025-11-08 03:58:43'),(11,'3S1-BY001','Yaw','Boateng','male','3S1','4',1,'2025-11-08 04:18:55','2025-11-08 04:18:55'),(12,'3B1-JM001','James','Mensah','male','3B1','1',1,'2025-11-08 04:22:32','2025-11-08 04:56:59'),(13,'3A2-AA001','Akua','Adjei','female','3A2','1',1,'2025-11-08 04:26:28','2025-11-08 04:26:28');
/*!40000 ALTER TABLE `students` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `fullname` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','teacher') COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_login` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `contact` (`contact`),
  KEY `idx_contact` (`contact`),
  KEY `idx_role` (`role`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'System Administrator','0244000000','$2y$10$r8k640XdHAwV3QVNKxSkt.VGCauwRzbQc0bx1rqBCwR48ogb9G.ki','admin',1,'2025-11-07 19:58:32','2025-11-08 14:16:56','2025-11-08 14:16:56'),(2,'PETER ASIEDU','0242168545','$2y$10$JQ3wf/ouZ.8rMHqn9VNt/.WDS6ccSckqqpGMs/r5G9akPJSBZRwqO','teacher',1,'2025-11-08 04:36:21','2025-11-08 14:11:36','2025-11-08 14:11:36');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-11-12  4:32:29
