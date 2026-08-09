-- Oracle Machine Tech CRM - Demo / Showcase Data
-- Fabricated data only (no real customers or production figures).
-- Demo logins (password Dashboard@123): owner-demo@ / manager-demo@ / account-demo@ / worker-demo@ (all @test.local)
-- Client portal logins (password Client@123): client1@test.local / client2@test.local
-- Import into an EMPTY database:  mysql -u USER -p DBNAME < demo-data.sql
-- Generated 2026-08-09.


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
DROP TABLE IF EXISTS `addlaser`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `addlaser` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int DEFAULT NULL,
  `company` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `modal` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `logo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `decription` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `update_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `addlaser_product_id_index` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `addlaser` WRITE;
/*!40000 ALTER TABLE `addlaser` DISABLE KEYS */;
/*!40000 ALTER TABLE `addlaser` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `attendances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `attendances` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint unsigned NOT NULL,
  `date` date NOT NULL,
  `status` enum('present','absent','half_day','leave') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `overtime_hours` decimal(5,2) NOT NULL DEFAULT '0.00',
  `marked_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `attendances_employee_id_date_unique` (`employee_id`,`date`),
  KEY `attendances_marked_by_foreign` (`marked_by`),
  CONSTRAINT `attendances_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `attendances_marked_by_foreign` FOREIGN KEY (`marked_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `attendances` WRITE;
/*!40000 ALTER TABLE `attendances` DISABLE KEYS */;
/*!40000 ALTER TABLE `attendances` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `auditable_type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `auditable_id` bigint unsigned NOT NULL,
  `action` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `field_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `old_value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `new_value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `user_id` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `audit_logs_user_id_foreign` (`user_id`),
  KEY `audit_logs_auditable_type_auditable_id_index` (`auditable_type`,`auditable_id`),
  CONSTRAINT `audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=491 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
INSERT INTO `audit_logs` VALUES (350,'App\\Models\\ChartAccount',11,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:17'),(351,'App\\Models\\ChartAccount',12,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:17'),(352,'App\\Models\\ChartAccount',13,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:17'),(353,'App\\Models\\ChartAccount',14,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:17'),(354,'App\\Models\\ChartAccount',15,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:17'),(355,'App\\Models\\ChartAccount',16,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:17'),(356,'App\\Models\\ChartAccount',17,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:17'),(357,'App\\Models\\ChartAccount',18,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:17'),(358,'App\\Models\\ChartAccount',19,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:17'),(359,'App\\Models\\ChartAccount',20,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:17'),(360,'ticket_problem_type',18,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:17'),(361,'ticket_problem_type',19,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:17'),(362,'ticket_problem_type',20,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:17'),(363,'ticket_problem_type',21,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:17'),(364,'ticket_problem_type',22,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:17'),(365,'ticket_problem_type',23,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:17'),(366,'ticket_problem_type',24,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:17'),(367,'ticket_problem_type',25,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:17'),(368,'ticket_problem_type',26,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:17'),(369,'ticket_problem_type',27,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:17'),(370,'ticket_problem_type',28,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:17'),(371,'ticket_problem_type',29,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:17'),(372,'ticket_problem_type',30,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:17'),(373,'ticket_problem_type',31,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:17'),(374,'ticket_problem_type',32,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:17'),(375,'ticket_problem_type',33,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:17'),(376,'user',19,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:17'),(377,'user',20,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:17'),(378,'user',21,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:17'),(379,'user',22,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(380,'user',23,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(381,'inventory',14,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(382,'inventory',15,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(383,'inventory',16,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(384,'inventory',17,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(385,'inventory',18,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(386,'inventory',19,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(387,'machine',8,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(388,'machine',9,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(389,'machine',10,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(390,'machine',11,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(391,'machine',12,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(392,'employee',13,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(393,'employee',14,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(394,'employee',15,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(395,'employee',16,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(396,'salary_payment',5,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(397,'salary_payment',6,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(398,'salary_payment',7,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(399,'salary_payment',8,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(400,'salary_payment',9,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(401,'salary_payment',10,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(402,'salary_payment',11,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(403,'salary_payment',12,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(404,'salary_payment',13,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(405,'salary_payment',14,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(406,'salary_payment',15,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(407,'salary_payment',16,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(408,'salary_payment',17,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(409,'salary_payment',18,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(410,'salary_payment',19,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(411,'salary_payment',20,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(412,'vendor',3,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(413,'vendor_bill',2,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(414,'vendor_payment',3,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(415,'vendor_bill',3,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(416,'vendor_payment',4,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(417,'vendor',4,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(418,'vendor_bill',4,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(419,'vendor_payment',5,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(420,'vendor_bill',5,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(421,'vendor_payment',6,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(422,'vendor',5,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(423,'vendor_bill',6,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(424,'vendor_payment',7,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(425,'vendor_bill',7,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(426,'vendor_payment',8,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(427,'expense_category',5,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(428,'expense_category',6,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(429,'expense_category',7,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(430,'expense_category',8,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(431,'expense_category',9,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(432,'expense_category',10,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(433,'expense_category',11,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(434,'daily_transaction',4,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(435,'daily_transaction',5,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(436,'daily_transaction',6,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(437,'daily_transaction',7,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(438,'daily_transaction',8,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(439,'daily_transaction',9,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(440,'daily_transaction',10,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(441,'daily_transaction',11,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(442,'daily_transaction',12,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(443,'daily_transaction',13,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(444,'daily_transaction',14,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(445,'daily_transaction',15,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(446,'daily_transaction',16,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(447,'daily_transaction',17,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(448,'daily_transaction',18,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(449,'daily_transaction',19,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(450,'daily_transaction',20,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(451,'daily_transaction',21,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(452,'daily_transaction',22,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(453,'daily_transaction',23,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(454,'daily_transaction',24,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(455,'daily_transaction',25,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(456,'daily_transaction',26,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(457,'daily_transaction',27,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(458,'daily_transaction',28,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(459,'daily_transaction',29,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(460,'daily_transaction',30,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(461,'daily_transaction',31,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(462,'daily_transaction',32,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(463,'daily_transaction',33,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(464,'daily_transaction',34,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(465,'daily_transaction',35,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(466,'daily_transaction',36,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(467,'daily_transaction',37,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(468,'daily_transaction',38,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(469,'daily_transaction',39,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(470,'daily_transaction',40,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(471,'daily_transaction',41,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(472,'daily_transaction',42,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(473,'daily_transaction',43,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(474,'daily_transaction',44,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(475,'daily_transaction',45,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(476,'daily_transaction',46,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(477,'daily_transaction',47,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(478,'daily_transaction',48,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(479,'daily_transaction',49,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(480,'daily_transaction',50,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(481,'daily_transaction',51,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(482,'daily_transaction',52,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(483,'client_account',10,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(484,'client_machine',10,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(485,'ticket',8,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(486,'client_machine',11,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(487,'client_account',11,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(488,'client_machine',12,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(489,'ticket',9,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18'),(490,'client_machine',13,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:52:18');
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `bank`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bank` (
  `id` int NOT NULL AUTO_INCREMENT,
  `bankholdername` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `bankaccountnumber` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `bankifsccode` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `bankbranchname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `bankname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `update_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `bank` WRITE;
/*!40000 ALTER TABLE `bank` DISABLE KEYS */;
/*!40000 ALTER TABLE `bank` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `chart_accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `chart_accounts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('asset','liability','equity','income','expense') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `chart_accounts_code_unique` (`code`),
  KEY `chart_accounts_type_index` (`type`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `chart_accounts` WRITE;
/*!40000 ALTER TABLE `chart_accounts` DISABLE KEYS */;
INSERT INTO `chart_accounts` VALUES (11,'1000','Cash & Bank','asset',1,0,'2026-08-09 12:52:17','2026-08-09 12:52:17'),(12,'1100','Accounts Receivable','asset',1,1,'2026-08-09 12:52:17','2026-08-09 12:52:17'),(13,'2000','Accounts Payable','liability',1,2,'2026-08-09 12:52:17','2026-08-09 12:52:17'),(14,'2100','GST Payable','liability',1,3,'2026-08-09 12:52:17','2026-08-09 12:52:17'),(15,'3000','Owner\'s Equity','equity',1,4,'2026-08-09 12:52:17','2026-08-09 12:52:17'),(16,'4000','Sales Income','income',1,5,'2026-08-09 12:52:17','2026-08-09 12:52:17'),(17,'4900','Other Income','income',1,6,'2026-08-09 12:52:17','2026-08-09 12:52:17'),(18,'5000','Salaries & Wages','expense',1,7,'2026-08-09 12:52:17','2026-08-09 12:52:17'),(19,'5100','Vendor Purchases','expense',1,8,'2026-08-09 12:52:17','2026-08-09 12:52:17'),(20,'5900','Operating Expenses','expense',1,9,'2026-08-09 12:52:17','2026-08-09 12:52:17');
/*!40000 ALTER TABLE `chart_accounts` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `client_accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `client_accounts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `client_accounts_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `client_accounts` WRITE;
/*!40000 ALTER TABLE `client_accounts` DISABLE KEYS */;
INSERT INTO `client_accounts` VALUES (10,'Shree Engineering','client1@test.local','9984103548','$2y$10$uEhhBIDafhf8mEYlZg3TZ.Y5Ph1xkep/BWPLS1T/C5FNLZs/RWeXS',NULL,1,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(11,'Metro Fabricators','client2@test.local','9923631969','$2y$10$fqd412hf/i.3KWa2vhNwIOMtGhtbOqfIs0fPJNYIku3ir0nklQVdK',NULL,1,'2026-08-09 12:52:18','2026-08-09 12:52:18');
/*!40000 ALTER TABLE `client_accounts` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `client_machines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `client_machines` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `client_account_id` bigint unsigned NOT NULL,
  `product_id` int NOT NULL,
  `invoice_id` int DEFAULT NULL,
  `serial_number` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `installed_at` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `client_machines_client_account_id_foreign` (`client_account_id`),
  KEY `client_machines_product_id_foreign` (`product_id`),
  KEY `client_machines_invoice_id_foreign` (`invoice_id`),
  CONSTRAINT `client_machines_client_account_id_foreign` FOREIGN KEY (`client_account_id`) REFERENCES `client_accounts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `client_machines_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoice` (`id`) ON DELETE SET NULL,
  CONSTRAINT `client_machines_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `client_machines` WRITE;
/*!40000 ALTER TABLE `client_machines` DISABLE KEYS */;
INSERT INTO `client_machines` VALUES (10,10,17,NULL,'SN-99956','2026-04-09','2026-08-09 12:52:18','2026-08-09 12:52:18'),(11,10,19,NULL,'SN-52708','2025-02-09','2026-08-09 12:52:18','2026-08-09 12:52:18'),(12,11,20,NULL,'SN-75328','2025-12-09','2026-08-09 12:52:18','2026-08-09 12:52:18'),(13,11,20,NULL,'SN-96013','2025-04-09','2026-08-09 12:52:18','2026-08-09 12:52:18');
/*!40000 ALTER TABLE `client_machines` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `cnsthinks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cnsthinks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int DEFAULT NULL,
  `cuttingthinks` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `cnsthinks_product_id_index` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `cnsthinks` WRITE;
/*!40000 ALTER TABLE `cnsthinks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cnsthinks` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `customer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `customer` (
  `id` int NOT NULL AUTO_INCREMENT,
  `invoice_id` int DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `phone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `state` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `billinggst` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `billingpan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `sname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `saddress` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `sphone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `sstate` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `shippinggst` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `shippingpan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `customer_invoice_id_index` (`invoice_id`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `customer` WRITE;
/*!40000 ALTER TABLE `customer` DISABLE KEYS */;
INSERT INTO `customer` VALUES (15,15,'Precision Cut Co','Plot 12, GIDC, Rajkot','9842857625',NULL,'Gujarat',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-04-26 12:52:18','2026-04-26 12:52:18'),(16,16,'Shree Engineering','Plot 12, GIDC, Rajkot','9839901230',NULL,'Gujarat',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-08-08 12:52:18','2026-08-08 12:52:18'),(17,17,'Shree Engineering','Plot 12, GIDC, Rajkot','9844581453',NULL,'Gujarat',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-16 12:52:18','2026-05-16 12:52:18'),(18,18,'Anand Industries','Plot 12, GIDC, Rajkot','9816577753',NULL,'Gujarat',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-04-23 12:52:18','2026-04-23 12:52:18'),(19,19,'Gujarat Steel Works','Plot 12, GIDC, Rajkot','9874054140',NULL,'Gujarat',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-04-25 12:52:18','2026-04-25 12:52:18'),(20,20,'Gujarat Steel Works','Plot 12, GIDC, Rajkot','9829038198',NULL,'Gujarat',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-04 12:52:18','2026-07-04 12:52:18'),(21,21,'Shree Engineering','Plot 12, GIDC, Rajkot','9873716911',NULL,'Gujarat',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-08-07 12:52:18','2026-08-07 12:52:18'),(22,22,'Sunrise Metals','Plot 12, GIDC, Rajkot','9826084304',NULL,'Gujarat',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-18 12:52:18','2026-07-18 12:52:18'),(23,23,'Shree Engineering','Plot 12, GIDC, Rajkot','9862457931',NULL,'Gujarat',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-02-13 12:52:18','2026-02-13 12:52:18'),(24,24,'Gujarat Steel Works','Plot 12, GIDC, Rajkot','9881296790',NULL,'Gujarat',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-03-15 12:52:18','2026-03-15 12:52:18'),(25,25,'Shree Engineering','Plot 12, GIDC, Rajkot','9826272841',NULL,'Gujarat',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-04-16 12:52:18','2026-04-16 12:52:18'),(26,26,'Sunrise Metals','Plot 12, GIDC, Rajkot','9863079027',NULL,'Gujarat',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-02 12:52:18','2026-06-02 12:52:18'),(27,27,'Precision Cut Co','Plot 12, GIDC, Rajkot','9880391456',NULL,'Gujarat',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-02-25 12:52:18','2026-02-25 12:52:18'),(28,28,'Precision Cut Co','Plot 12, GIDC, Rajkot','9886569843',NULL,'Gujarat',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-18 12:52:18','2026-05-18 12:52:18'),(29,29,'Metro Fabricators','Plot 12, GIDC, Rajkot','9897036248',NULL,'Gujarat',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-22 12:52:18','2026-07-22 12:52:18'),(30,30,'Sunrise Metals','Plot 12, GIDC, Rajkot','9867196396',NULL,'Gujarat',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-02-12 12:52:18','2026-02-12 12:52:18'),(31,31,'Gujarat Steel Works','Plot 12, GIDC, Rajkot','9863572301',NULL,'Gujarat',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-04-23 12:52:18','2026-04-23 12:52:18'),(32,32,'Precision Cut Co','Plot 12, GIDC, Rajkot','9897876874',NULL,'Gujarat',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-07 12:52:18','2026-06-07 12:52:18'),(33,33,'Anand Industries','Plot 12, GIDC, Rajkot','9893312576',NULL,'Gujarat',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-02-18 12:52:18','2026-02-18 12:52:18'),(34,34,'Gujarat Steel Works','Plot 12, GIDC, Rajkot','9856255455',NULL,'Gujarat',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-04-19 12:52:18','2026-04-19 12:52:18'),(35,35,'Precision Cut Co','Plot 12, GIDC, Rajkot','9825709515',NULL,'Gujarat',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-03 12:52:18','2026-06-03 12:52:18'),(36,36,'Metro Fabricators','Plot 12, GIDC, Rajkot','9896832066',NULL,'Gujarat',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-31 12:52:18','2026-07-31 12:52:18'),(37,37,'Gujarat Steel Works','Plot 12, GIDC, Rajkot','9830797467',NULL,'Gujarat',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-03-27 12:52:18','2026-03-27 12:52:18'),(38,38,'Gujarat Steel Works','Plot 12, GIDC, Rajkot','9818075568',NULL,'Gujarat',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-03 12:52:18','2026-07-03 12:52:18'),(39,39,'Gujarat Steel Works','Plot 12, GIDC, Rajkot','9894746777',NULL,'Gujarat',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-29 12:52:18','2026-07-29 12:52:18'),(40,40,'Sunrise Metals','Plot 12, GIDC, Rajkot','9862804156',NULL,'Gujarat',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-02-17 12:52:18','2026-02-17 12:52:18'),(41,41,'Precision Cut Co','Plot 12, GIDC, Rajkot','9843150225',NULL,'Gujarat',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-06 12:52:18','2026-07-06 12:52:18'),(42,42,'Shree Engineering','Plot 12, GIDC, Rajkot','9870090958',NULL,'Gujarat',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-03-25 12:52:18','2026-03-25 12:52:18');
/*!40000 ALTER TABLE `customer` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `cuttingway`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cuttingway` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int DEFAULT NULL,
  `cuttingway` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `cuttingway_product_id_index` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `cuttingway` WRITE;
/*!40000 ALTER TABLE `cuttingway` DISABLE KEYS */;
/*!40000 ALTER TABLE `cuttingway` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `daily_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `daily_transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `type` enum('payment','receipt') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expense_category_id` bigint unsigned NOT NULL,
  `party_type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `party_id` bigint unsigned DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `date` date NOT NULL,
  `payment_mode` enum('cash','bank','upi','cheque') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `receipt_photo` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `linked_type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `linked_id` bigint unsigned DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `daily_transactions_expense_category_id_foreign` (`expense_category_id`),
  KEY `daily_transactions_created_by_foreign` (`created_by`),
  KEY `daily_transactions_type_date_index` (`type`,`date`),
  CONSTRAINT `daily_transactions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `daily_transactions_expense_category_id_foreign` FOREIGN KEY (`expense_category_id`) REFERENCES `expense_categories` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `daily_transactions` WRITE;
/*!40000 ALTER TABLE `daily_transactions` DISABLE KEYS */;
INSERT INTO `daily_transactions` VALUES (4,'payment',5,NULL,NULL,11500.00,'2026-04-04','upi','Electricity',NULL,NULL,NULL,19,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(5,'payment',8,NULL,NULL,1500.00,'2026-04-02','upi','Maintenance',NULL,NULL,NULL,19,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(6,'payment',5,NULL,NULL,7000.00,'2026-03-28','bank','Electricity',NULL,NULL,NULL,19,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(7,'payment',6,NULL,NULL,11000.00,'2026-03-25','cash','Diesel / Fuel',NULL,NULL,NULL,19,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(8,'payment',7,NULL,NULL,5500.00,'2026-03-18','bank','Rent',NULL,NULL,NULL,19,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(9,'payment',9,NULL,NULL,11000.00,'2026-03-20','cash','Office Supplies',NULL,NULL,NULL,19,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(10,'payment',6,NULL,NULL,4500.00,'2026-03-25','cash','Diesel / Fuel',NULL,NULL,NULL,19,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(11,'receipt',11,NULL,NULL,5000.00,'2026-04-03','upi','Scrap Sale',NULL,NULL,NULL,19,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(12,'payment',8,NULL,NULL,10500.00,'2026-04-24','bank','Maintenance',NULL,NULL,NULL,19,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(13,'payment',6,NULL,NULL,4000.00,'2026-04-14','upi','Diesel / Fuel',NULL,NULL,NULL,19,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(14,'payment',9,NULL,NULL,1500.00,'2026-04-29','cash','Office Supplies',NULL,NULL,NULL,19,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(15,'payment',6,NULL,NULL,4000.00,'2026-04-14','upi','Diesel / Fuel',NULL,NULL,NULL,19,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(16,'payment',5,NULL,NULL,1500.00,'2026-04-27','upi','Electricity',NULL,NULL,NULL,19,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(17,'payment',5,NULL,NULL,9000.00,'2026-04-12','bank','Electricity',NULL,NULL,NULL,19,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(18,'payment',7,NULL,NULL,1500.00,'2026-04-27','cash','Rent',NULL,NULL,NULL,19,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(19,'receipt',11,NULL,NULL,10000.00,'2026-04-19','cash','Scrap Sale',NULL,NULL,NULL,19,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(20,'payment',8,NULL,NULL,1500.00,'2026-06-04','bank','Maintenance',NULL,NULL,NULL,19,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(21,'payment',7,NULL,NULL,5000.00,'2026-05-20','bank','Rent',NULL,NULL,NULL,19,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(22,'payment',7,NULL,NULL,2000.00,'2026-05-27','upi','Rent',NULL,NULL,NULL,19,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(23,'payment',6,NULL,NULL,6000.00,'2026-05-28','bank','Diesel / Fuel',NULL,NULL,NULL,19,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(24,'payment',8,NULL,NULL,8000.00,'2026-05-21','bank','Maintenance',NULL,NULL,NULL,19,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(25,'payment',7,NULL,NULL,11500.00,'2026-05-20','cash','Rent',NULL,NULL,NULL,19,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(26,'payment',8,NULL,NULL,1500.00,'2026-05-10','cash','Maintenance',NULL,NULL,NULL,19,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(27,'receipt',11,NULL,NULL,6000.00,'2026-05-28','upi','Scrap Sale',NULL,NULL,NULL,19,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(28,'receipt',10,NULL,NULL,7000.00,'2026-05-09','bank','Other Income',NULL,NULL,NULL,19,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(29,'receipt',11,NULL,NULL,9000.00,'2026-06-04','bank','Scrap Sale',NULL,NULL,NULL,19,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(30,'payment',7,NULL,NULL,4500.00,'2026-06-22','upi','Rent',NULL,NULL,NULL,19,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(31,'payment',9,NULL,NULL,10500.00,'2026-06-17','upi','Office Supplies',NULL,NULL,NULL,19,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(32,'payment',5,NULL,NULL,10500.00,'2026-07-03','cash','Electricity',NULL,NULL,NULL,19,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(33,'payment',8,NULL,NULL,11500.00,'2026-06-24','upi','Maintenance',NULL,NULL,NULL,19,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(34,'payment',9,NULL,NULL,2500.00,'2026-06-13','upi','Office Supplies',NULL,NULL,NULL,19,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(35,'payment',6,NULL,NULL,11000.00,'2026-06-15','upi','Diesel / Fuel',NULL,NULL,NULL,19,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(36,'payment',9,NULL,NULL,10000.00,'2026-06-10','cash','Office Supplies',NULL,NULL,NULL,19,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(37,'receipt',10,NULL,NULL,9000.00,'2026-07-05','bank','Other Income',NULL,NULL,NULL,19,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(38,'payment',9,NULL,NULL,4000.00,'2026-07-17','upi','Office Supplies',NULL,NULL,NULL,19,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(39,'payment',7,NULL,NULL,10500.00,'2026-07-31','cash','Rent',NULL,NULL,NULL,19,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(40,'payment',7,NULL,NULL,3000.00,'2026-07-31','upi','Rent',NULL,NULL,NULL,19,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(41,'payment',5,NULL,NULL,9500.00,'2026-07-18','cash','Electricity',NULL,NULL,NULL,19,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(42,'payment',5,NULL,NULL,9000.00,'2026-07-12','cash','Electricity',NULL,NULL,NULL,19,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(43,'receipt',11,NULL,NULL,8000.00,'2026-07-15','bank','Scrap Sale',NULL,NULL,NULL,19,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(44,'receipt',10,NULL,NULL,7000.00,'2026-07-12','bank','Other Income',NULL,NULL,NULL,19,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(45,'payment',8,NULL,NULL,11000.00,'2026-08-27','upi','Maintenance',NULL,NULL,NULL,19,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(46,'payment',5,NULL,NULL,11500.00,'2026-08-28','upi','Electricity',NULL,NULL,NULL,19,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(47,'payment',8,NULL,NULL,11500.00,'2026-08-28','cash','Maintenance',NULL,NULL,NULL,19,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(48,'payment',5,NULL,NULL,9000.00,'2026-08-12','cash','Electricity',NULL,NULL,NULL,19,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(49,'payment',6,NULL,NULL,8000.00,'2026-08-09','upi','Diesel / Fuel',NULL,NULL,NULL,19,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(50,'payment',7,NULL,NULL,2000.00,'2026-08-10','cash','Rent',NULL,NULL,NULL,19,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(51,'payment',9,NULL,NULL,1000.00,'2026-08-21','upi','Office Supplies',NULL,NULL,NULL,19,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(52,'receipt',11,NULL,NULL,2000.00,'2026-08-20','upi','Scrap Sale',NULL,NULL,NULL,19,'2026-08-09 12:52:18','2026-08-09 12:52:18');
/*!40000 ALTER TABLE `daily_transactions` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `employee_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `employee_documents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint unsigned NOT NULL,
  `document_type` enum('aadhar','pan','driving_license','voter_id','other') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `document_number` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `path` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `uploaded_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_documents_employee_id_foreign` (`employee_id`),
  KEY `employee_documents_uploaded_by_foreign` (`uploaded_by`),
  CONSTRAINT `employee_documents_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_documents_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `employee_documents` WRITE;
/*!40000 ALTER TABLE `employee_documents` DISABLE KEYS */;
/*!40000 ALTER TABLE `employee_documents` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `employees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `employees` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `emergency_contact_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emergency_contact_phone` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `department` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `designation` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `joining_date` date NOT NULL,
  `pay_type` enum('monthly','daily') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `pay_rate` decimal(10,2) NOT NULL,
  `overtime_rate_per_hour` decimal(10,2) NOT NULL DEFAULT '0.00',
  `bank_account_holder_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_account_number` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_ifsc` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employees_user_id_foreign` (`user_id`),
  KEY `employees_is_active_index` (`is_active`),
  CONSTRAINT `employees_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `employees` WRITE;
/*!40000 ALTER TABLE `employees` DISABLE KEYS */;
INSERT INTO `employees` VALUES (13,'Suresh Yadav','9797703471',NULL,NULL,NULL,NULL,'Production','Technician','2025-08-09','monthly',22000.00,120.00,NULL,NULL,NULL,NULL,NULL,1,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(14,'Prakash Mehta','9794773473',NULL,NULL,NULL,NULL,'Production','Technician','2025-08-09','monthly',26000.00,120.00,NULL,NULL,NULL,NULL,NULL,1,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(15,'Dinesh Patel','9728668000',NULL,NULL,NULL,NULL,'Production','Technician','2025-08-09','daily',700.00,120.00,NULL,NULL,NULL,NULL,NULL,1,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(16,'Kiran Rao','9783456389',NULL,NULL,NULL,NULL,'Production','Technician','2025-08-09','daily',650.00,120.00,NULL,NULL,NULL,NULL,NULL,1,'2026-08-09 12:52:18','2026-08-09 12:52:18');
/*!40000 ALTER TABLE `employees` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `expense_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `expense_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('payment','receipt') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `party_model` enum('employee','vendor','client_account') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `expense_categories` WRITE;
/*!40000 ALTER TABLE `expense_categories` DISABLE KEYS */;
INSERT INTO `expense_categories` VALUES (5,'Electricity','payment',NULL,1,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(6,'Diesel / Fuel','payment',NULL,1,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(7,'Rent','payment',NULL,1,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(8,'Maintenance','payment',NULL,1,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(9,'Office Supplies','payment',NULL,1,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(10,'Other Income','receipt',NULL,1,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(11,'Scrap Sale','receipt',NULL,1,'2026-08-09 12:52:18','2026-08-09 12:52:18');
/*!40000 ALTER TABLE `expense_categories` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `fours`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fours` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int DEFAULT NULL,
  `modal` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `company` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `logo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fours_product_id_index` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `fours` WRITE;
/*!40000 ALTER TABLE `fours` DISABLE KEYS */;
/*!40000 ALTER TABLE `fours` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `gear`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `gear` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int DEFAULT NULL,
  `companyname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `gear_product_id_index` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `gear` WRITE;
/*!40000 ALTER TABLE `gear` DISABLE KEYS */;
/*!40000 ALTER TABLE `gear` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `invetry`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `invetry` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int DEFAULT NULL,
  `quantity` int DEFAULT NULL,
  `low_stock_threshold` int unsigned DEFAULT NULL,
  `low_stock_notified_at` timestamp NULL DEFAULT NULL,
  `vandername` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `rate` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `invetry_product_id_index` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `invetry` WRITE;
/*!40000 ALTER TABLE `invetry` DISABLE KEYS */;
INSERT INTO `invetry` VALUES (14,17,40,NULL,NULL,'Demo Supplier','400','2026-08-09 18:22:18','2026-08-09 18:22:18'),(15,18,3,NULL,NULL,'Demo Supplier','400','2026-08-09 18:22:18','2026-08-09 18:22:18'),(16,19,60,NULL,NULL,'Demo Supplier','400','2026-08-09 18:22:18','2026-08-09 18:22:18'),(17,20,5,NULL,NULL,'Demo Supplier','400','2026-08-09 18:22:18','2026-08-09 18:22:18'),(18,21,25,NULL,NULL,'Demo Supplier','400','2026-08-09 18:22:18','2026-08-09 18:22:18'),(19,22,18,NULL,NULL,'Demo Supplier','400','2026-08-09 18:22:18','2026-08-09 18:22:18');
/*!40000 ALTER TABLE `invetry` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `invoice`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `invoice` (
  `id` int NOT NULL AUTO_INCREMENT,
  `invoice_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `date` date DEFAULT NULL,
  `bankname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `accountholder` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `bankaccountnumber` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `bankifsccode` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `bankbranchname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `totalamountbeforetax` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `amount` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `amountwithtax` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `paycondition` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `duedate` date DEFAULT NULL,
  `placesupply` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `paidamount` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `remaining_amount` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `challanno` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ewaybillno` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ewaybilldate` date DEFAULT NULL,
  `despatchthrough` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `TransportVehicleNo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `pono` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `invoice` WRITE;
/*!40000 ALTER TABLE `invoice` DISABLE KEYS */;
INSERT INTO `invoice` VALUES (15,'INV-1000','2026-04-26',NULL,NULL,NULL,NULL,NULL,'Demo invoice','23900','23900','28202',NULL,NULL,'Gujarat','28202','0',NULL,NULL,NULL,NULL,NULL,NULL,'2026-04-26 18:22:18','2026-04-26 18:22:18'),(16,'INV-1001','2026-08-08',NULL,NULL,NULL,NULL,NULL,'Demo invoice','18200','18200','21476',NULL,NULL,'Gujarat','0','21476',NULL,NULL,NULL,NULL,NULL,NULL,'2026-08-08 18:22:18','2026-08-08 18:22:18'),(17,'INV-1002','2026-05-16',NULL,NULL,NULL,NULL,NULL,'Demo invoice','24900','24900','29382',NULL,NULL,'Gujarat','29382','0',NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-16 18:22:18','2026-05-16 18:22:18'),(18,'INV-1003','2026-04-23',NULL,NULL,NULL,NULL,NULL,'Demo invoice','40550','40550','47849',NULL,NULL,'Gujarat','47849','0',NULL,NULL,NULL,NULL,NULL,NULL,'2026-04-23 18:22:18','2026-04-23 18:22:18'),(19,'INV-1004','2026-04-25',NULL,NULL,NULL,NULL,NULL,'Demo invoice','1920','1920','2265.6',NULL,NULL,'Gujarat','0','2265.6',NULL,NULL,NULL,NULL,NULL,NULL,'2026-04-25 18:22:18','2026-04-25 18:22:18'),(20,'INV-1005','2026-07-04',NULL,NULL,NULL,NULL,NULL,'Demo invoice','4160','4160','4908.8',NULL,NULL,'Gujarat','4908.8','0',NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-04 18:22:18','2026-07-04 18:22:18'),(21,'INV-1006','2026-08-07',NULL,NULL,NULL,NULL,NULL,'Demo invoice','32550','32550','38409',NULL,NULL,'Gujarat','38409','0',NULL,NULL,NULL,NULL,NULL,NULL,'2026-08-07 18:22:18','2026-08-07 18:22:18'),(22,'INV-1007','2026-07-18',NULL,NULL,NULL,NULL,NULL,'Demo invoice','12750','12750','15045',NULL,NULL,'Gujarat','15045','0',NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-18 18:22:18','2026-07-18 18:22:18'),(23,'INV-1008','2026-02-13',NULL,NULL,NULL,NULL,NULL,'Demo invoice','26550','26550','31329',NULL,NULL,'Gujarat','15664.5','15664.5',NULL,NULL,NULL,NULL,NULL,NULL,'2026-02-13 18:22:18','2026-02-13 18:22:18'),(24,'INV-1009','2026-03-15',NULL,NULL,NULL,NULL,NULL,'Demo invoice','23100','23100','27258',NULL,NULL,'Gujarat','27258','0',NULL,NULL,NULL,NULL,NULL,NULL,'2026-03-15 18:22:18','2026-03-15 18:22:18'),(25,'INV-1010','2026-04-16',NULL,NULL,NULL,NULL,NULL,'Demo invoice','64750','64750','76405',NULL,NULL,'Gujarat','0','76405',NULL,NULL,NULL,NULL,NULL,NULL,'2026-04-16 18:22:18','2026-04-16 18:22:18'),(26,'INV-1011','2026-06-02',NULL,NULL,NULL,NULL,NULL,'Demo invoice','22500','22500','26550',NULL,NULL,'Gujarat','13275','13275',NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-02 18:22:18','2026-06-02 18:22:18'),(27,'INV-1012','2026-02-25',NULL,NULL,NULL,NULL,NULL,'Demo invoice','4280','4280','5050.4',NULL,NULL,'Gujarat','2525.2','2525.2',NULL,NULL,NULL,NULL,NULL,NULL,'2026-02-25 18:22:18','2026-02-25 18:22:18'),(28,'INV-1013','2026-05-18',NULL,NULL,NULL,NULL,NULL,'Demo invoice','38750','38750','45725',NULL,NULL,'Gujarat','0','45725',NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-18 18:22:18','2026-05-18 18:22:18'),(29,'INV-1014','2026-07-22',NULL,NULL,NULL,NULL,NULL,'Demo invoice','9350','9350','11033',NULL,NULL,'Gujarat','0','11033',NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-22 18:22:18','2026-07-22 18:22:18'),(30,'INV-1015','2026-02-12',NULL,NULL,NULL,NULL,NULL,'Demo invoice','8500','8500','10030',NULL,NULL,'Gujarat','0','10030',NULL,NULL,NULL,NULL,NULL,NULL,'2026-02-12 18:22:18','2026-02-12 18:22:18'),(31,'INV-1016','2026-04-23',NULL,NULL,NULL,NULL,NULL,'Demo invoice','32700','32700','38586',NULL,NULL,'Gujarat','38586','0',NULL,NULL,NULL,NULL,NULL,NULL,'2026-04-23 18:22:18','2026-04-23 18:22:18'),(32,'INV-1017','2026-06-07',NULL,NULL,NULL,NULL,NULL,'Demo invoice','39000','39000','46020',NULL,NULL,'Gujarat','46020','0',NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-07 18:22:18','2026-06-07 18:22:18'),(33,'INV-1018','2026-02-18',NULL,NULL,NULL,NULL,NULL,'Demo invoice','27950','27950','32981',NULL,NULL,'Gujarat','32981','0',NULL,NULL,NULL,NULL,NULL,NULL,'2026-02-18 18:22:18','2026-02-18 18:22:18'),(34,'INV-1019','2026-04-19',NULL,NULL,NULL,NULL,NULL,'Demo invoice','26100','26100','30798',NULL,NULL,'Gujarat','15399','15399',NULL,NULL,NULL,NULL,NULL,NULL,'2026-04-19 18:22:18','2026-04-19 18:22:18'),(35,'INV-1020','2026-06-03',NULL,NULL,NULL,NULL,NULL,'Demo invoice','11000','11000','12980',NULL,NULL,'Gujarat','0','12980',NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-03 18:22:18','2026-06-03 18:22:18'),(36,'INV-1021','2026-07-31',NULL,NULL,NULL,NULL,NULL,'Demo invoice','63800','63800','75284',NULL,NULL,'Gujarat','37642','37642',NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-31 18:22:18','2026-07-31 18:22:18'),(37,'INV-1022','2026-03-27',NULL,NULL,NULL,NULL,NULL,'Demo invoice','24000','24000','28320',NULL,NULL,'Gujarat','0','28320',NULL,NULL,NULL,NULL,NULL,NULL,'2026-03-27 18:22:18','2026-03-27 18:22:18'),(38,'INV-1023','2026-07-03',NULL,NULL,NULL,NULL,NULL,'Demo invoice','21000','21000','24780',NULL,NULL,'Gujarat','24780','0',NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-03 18:22:18','2026-07-03 18:22:18'),(39,'INV-1024','2026-07-29',NULL,NULL,NULL,NULL,NULL,'Demo invoice','23900','23900','28202',NULL,NULL,'Gujarat','28202','0',NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-29 18:22:18','2026-07-29 18:22:18'),(40,'INV-1025','2026-02-17',NULL,NULL,NULL,NULL,NULL,'Demo invoice','36400','36400','42952',NULL,NULL,'Gujarat','42952','0',NULL,NULL,NULL,NULL,NULL,NULL,'2026-02-17 18:22:18','2026-02-17 18:22:18'),(41,'INV-1026','2026-07-06',NULL,NULL,NULL,NULL,NULL,'Demo invoice','10800','10800','12744',NULL,NULL,'Gujarat','12744','0',NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-06 18:22:18','2026-07-06 18:22:18'),(42,'INV-1027','2026-03-25',NULL,NULL,NULL,NULL,NULL,'Demo invoice','6000','6000','7080',NULL,NULL,'Gujarat','3540','3540',NULL,NULL,NULL,NULL,NULL,NULL,'2026-03-25 18:22:18','2026-03-25 18:22:18');
/*!40000 ALTER TABLE `invoice` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `invoiceproduct`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `invoiceproduct` (
  `id` int NOT NULL AUTO_INCREMENT,
  `invoice_id` int DEFAULT NULL,
  `product_name` int DEFAULT NULL,
  `unit` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `hsn` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `rate` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `quantity` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `total` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `gst` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `gstamount` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `totalamount` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=68 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `invoiceproduct` WRITE;
/*!40000 ALTER TABLE `invoiceproduct` DISABLE KEYS */;
INSERT INTO `invoiceproduct` VALUES (15,15,20,'pcs','9013','2200','2','4400','18','792','5192','2026-04-26 18:22:18','2026-04-26 18:22:18'),(16,15,22,'pcs','9013','1500','11','16500','18','2970','19470','2026-04-26 18:22:18','2026-04-26 18:22:18'),(17,15,22,'pcs','9013','1500','2','3000','18','540','3540','2026-04-26 18:22:18','2026-04-26 18:22:18'),(18,16,21,'pcs','9013','2600','7','18200','18','3276','21476','2026-08-08 18:22:18','2026-08-08 18:22:18'),(19,17,21,'pcs','9013','2600','5','13000','18','2340','15340','2026-05-16 18:22:18','2026-05-16 18:22:18'),(20,17,18,'pcs','9013','850','9','7650','18','1377','9027','2026-05-16 18:22:18','2026-05-16 18:22:18'),(21,17,18,'pcs','9013','850','5','4250','18','765','5015','2026-05-16 18:22:18','2026-05-16 18:22:18'),(22,18,21,'pcs','9013','2600','6','15600','18','2808','18408','2026-04-23 18:22:18','2026-04-23 18:22:18'),(23,18,17,'pcs','9013','450','15','6750','18','1215','7965','2026-04-23 18:22:18','2026-04-23 18:22:18'),(24,18,21,'pcs','9013','2600','7','18200','18','3276','21476','2026-04-23 18:22:18','2026-04-23 18:22:18'),(25,19,19,'pcs','9013','320','2','640','18','115.2','755.2','2026-04-25 18:22:18','2026-04-25 18:22:18'),(26,19,19,'pcs','9013','320','4','1280','18','230.4','1510.4','2026-04-25 18:22:18','2026-04-25 18:22:18'),(27,20,19,'pcs','9013','320','13','4160','18','748.8','4908.8','2026-07-04 18:22:18','2026-07-04 18:22:18'),(28,21,18,'pcs','9013','850','15','12750','18','2295','15045','2026-08-07 18:22:18','2026-08-07 18:22:18'),(29,21,20,'pcs','9013','2200','9','19800','18','3564','23364','2026-08-07 18:22:18','2026-08-07 18:22:18'),(30,22,18,'pcs','9013','850','15','12750','18','2295','15045','2026-07-18 18:22:18','2026-07-18 18:22:18'),(31,23,21,'pcs','9013','2600','9','23400','18','4212','27612','2026-02-13 18:22:18','2026-02-13 18:22:18'),(32,23,17,'pcs','9013','450','7','3150','18','567','3717','2026-02-13 18:22:18','2026-02-13 18:22:18'),(33,24,21,'pcs','9013','2600','6','15600','18','2808','18408','2026-03-15 18:22:18','2026-03-15 18:22:18'),(34,24,22,'pcs','9013','1500','5','7500','18','1350','8850','2026-03-15 18:22:18','2026-03-15 18:22:18'),(35,25,18,'pcs','9013','850','15','12750','18','2295','15045','2026-04-16 18:22:18','2026-04-16 18:22:18'),(36,25,21,'pcs','9013','2600','9','23400','18','4212','27612','2026-04-16 18:22:18','2026-04-16 18:22:18'),(37,25,21,'pcs','9013','2600','11','28600','18','5148','33748','2026-04-16 18:22:18','2026-04-16 18:22:18'),(38,26,22,'pcs','9013','1500','15','22500','18','4050','26550','2026-06-02 18:22:18','2026-06-02 18:22:18'),(39,27,19,'pcs','9013','320','4','1280','18','230.4','1510.4','2026-02-25 18:22:18','2026-02-25 18:22:18'),(40,27,22,'pcs','9013','1500','2','3000','18','540','3540','2026-02-25 18:22:18','2026-02-25 18:22:18'),(41,28,18,'pcs','9013','850','8','6800','18','1224','8024','2026-05-18 18:22:18','2026-05-18 18:22:18'),(42,28,21,'pcs','9013','2600','10','26000','18','4680','30680','2026-05-18 18:22:18','2026-05-18 18:22:18'),(43,28,18,'pcs','9013','850','7','5950','18','1071','7021','2026-05-18 18:22:18','2026-05-18 18:22:18'),(44,29,18,'pcs','9013','850','11','9350','18','1683','11033','2026-07-22 18:22:18','2026-07-22 18:22:18'),(45,30,18,'pcs','9013','850','10','8500','18','1530','10030','2026-02-12 18:22:18','2026-02-12 18:22:18'),(46,31,20,'pcs','9013','2200','6','13200','18','2376','15576','2026-04-23 18:22:18','2026-04-23 18:22:18'),(47,31,22,'pcs','9013','1500','11','16500','18','2970','19470','2026-04-23 18:22:18','2026-04-23 18:22:18'),(48,31,22,'pcs','9013','1500','2','3000','18','540','3540','2026-04-23 18:22:18','2026-04-23 18:22:18'),(49,32,21,'pcs','9013','2600','15','39000','18','7020','46020','2026-06-07 18:22:18','2026-06-07 18:22:18'),(50,33,20,'pcs','9013','2200','10','22000','18','3960','25960','2026-02-18 18:22:18','2026-02-18 18:22:18'),(51,33,18,'pcs','9013','850','7','5950','18','1071','7021','2026-02-18 18:22:18','2026-02-18 18:22:18'),(52,34,22,'pcs','9013','1500','13','19500','18','3510','23010','2026-04-19 18:22:18','2026-04-19 18:22:18'),(53,34,20,'pcs','9013','2200','3','6600','18','1188','7788','2026-04-19 18:22:18','2026-04-19 18:22:18'),(54,35,20,'pcs','9013','2200','5','11000','18','1980','12980','2026-06-03 18:22:18','2026-06-03 18:22:18'),(55,36,20,'pcs','9013','2200','9','19800','18','3564','23364','2026-07-31 18:22:18','2026-07-31 18:22:18'),(56,36,20,'pcs','9013','2200','11','24200','18','4356','28556','2026-07-31 18:22:18','2026-07-31 18:22:18'),(57,36,20,'pcs','9013','2200','9','19800','18','3564','23364','2026-07-31 18:22:18','2026-07-31 18:22:18'),(58,37,22,'pcs','9013','1500','10','15000','18','2700','17700','2026-03-27 18:22:18','2026-03-27 18:22:18'),(59,37,22,'pcs','9013','1500','6','9000','18','1620','10620','2026-03-27 18:22:18','2026-03-27 18:22:18'),(60,38,22,'pcs','9013','1500','14','21000','18','3780','24780','2026-07-03 18:22:18','2026-07-03 18:22:18'),(61,39,20,'pcs','9013','2200','2','4400','18','792','5192','2026-07-29 18:22:18','2026-07-29 18:22:18'),(62,39,22,'pcs','9013','1500','13','19500','18','3510','23010','2026-07-29 18:22:18','2026-07-29 18:22:18'),(63,40,21,'pcs','9013','2600','14','36400','18','6552','42952','2026-02-17 18:22:18','2026-02-17 18:22:18'),(64,41,17,'pcs','9013','450','8','3600','18','648','4248','2026-07-06 18:22:18','2026-07-06 18:22:18'),(65,41,17,'pcs','9013','450','10','4500','18','810','5310','2026-07-06 18:22:18','2026-07-06 18:22:18'),(66,41,17,'pcs','9013','450','6','2700','18','486','3186','2026-07-06 18:22:18','2026-07-06 18:22:18'),(67,42,22,'pcs','9013','1500','4','6000','18','1080','7080','2026-03-25 18:22:18','2026-03-25 18:22:18');
/*!40000 ALTER TABLE `invoiceproduct` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `job_audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_audit_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `job_id` bigint unsigned NOT NULL,
  `user_id` int NOT NULL,
  `action` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `job_audit_logs_job_id_foreign` (`job_id`),
  KEY `job_audit_logs_user_id_foreign` (`user_id`),
  CONSTRAINT `job_audit_logs_job_id_foreign` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `job_audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `job_audit_logs` WRITE;
/*!40000 ALTER TABLE `job_audit_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_audit_logs` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `job_checklist_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_checklist_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `job_id` bigint unsigned NOT NULL,
  `description` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_completed` tinyint(1) NOT NULL DEFAULT '0',
  `completed_by` int DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `job_checklist_items_job_id_foreign` (`job_id`),
  KEY `job_checklist_items_completed_by_foreign` (`completed_by`),
  CONSTRAINT `job_checklist_items_completed_by_foreign` FOREIGN KEY (`completed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `job_checklist_items_job_id_foreign` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `job_checklist_items` WRITE;
/*!40000 ALTER TABLE `job_checklist_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_checklist_items` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `job_photos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_photos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `job_id` bigint unsigned DEFAULT NULL,
  `ticket_id` bigint unsigned DEFAULT NULL,
  `uploaded_by` int DEFAULT NULL,
  `path` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `content_hash` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stage` enum('before','after','general') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'general',
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `location_captured` tinyint(1) NOT NULL DEFAULT '0',
  `location_flagged` tinyint(1) NOT NULL DEFAULT '0',
  `distance_from_machine_meters` decimal(10,2) DEFAULT NULL,
  `map_link` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `captured_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `job_photos_new_job_id_foreign` (`job_id`),
  KEY `job_photos_new_ticket_id_foreign` (`ticket_id`),
  KEY `job_photos_new_uploaded_by_foreign` (`uploaded_by`),
  CONSTRAINT `job_photos_new_job_id_foreign` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `job_photos_new_ticket_id_foreign` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `job_photos_new_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `job_photos` WRITE;
/*!40000 ALTER TABLE `job_photos` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_photos` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `machine_id` bigint unsigned DEFAULT NULL,
  `site_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` int NOT NULL,
  `assigned_to` int DEFAULT NULL,
  `priority` enum('low','medium','high','urgent') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medium',
  `due_date` date DEFAULT NULL,
  `overdue_flagged_at` timestamp NULL DEFAULT NULL,
  `status` enum('pending_approval','assigned','in_progress','on_hold','completed','rejected') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `decided_by` int DEFAULT NULL,
  `decided_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `on_hold_reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `completion_notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_machine_id_foreign` (`machine_id`),
  KEY `jobs_created_by_foreign` (`created_by`),
  KEY `jobs_decided_by_foreign` (`decided_by`),
  KEY `jobs_status_index` (`status`),
  KEY `jobs_assigned_to_status_index` (`assigned_to`,`status`),
  CONSTRAINT `jobs_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `jobs_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `jobs_decided_by_foreign` FOREIGN KEY (`decided_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `jobs_machine_id_foreign` FOREIGN KEY (`machine_id`) REFERENCES `machines` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
INSERT INTO `jobs` VALUES (16,'Lens cleaning',NULL,9,'Rajkot Plant',19,23,'high','2026-07-25',NULL,'completed',NULL,NULL,NULL,NULL,'Work completed and verified.','2026-07-27 12:52:18','2026-07-24 12:52:18','2026-08-09 12:52:18'),(17,'Bearing service',NULL,9,'Rajkot Plant',19,22,'high','2026-07-21',NULL,'completed',NULL,NULL,NULL,NULL,'Work completed and verified.','2026-07-23 12:52:18','2026-07-20 12:52:18','2026-08-09 12:52:18'),(18,'Bearing service',NULL,11,'Rajkot Plant',19,22,'low','2026-08-03',NULL,'completed',NULL,NULL,NULL,NULL,'Work completed and verified.','2026-08-05 12:52:18','2026-08-02 12:52:18','2026-08-09 12:52:18'),(19,'Bearing service',NULL,9,'Rajkot Plant',19,23,'low','2026-07-27',NULL,'completed',NULL,NULL,NULL,NULL,'Work completed and verified.','2026-07-29 12:52:18','2026-07-26 12:52:18','2026-08-09 12:52:18'),(20,'Chiller maintenance',NULL,8,'Rajkot Plant',19,22,'high','2026-08-06',NULL,'completed',NULL,NULL,NULL,NULL,'Work completed and verified.','2026-08-08 12:52:18','2026-08-05 12:52:18','2026-08-09 12:52:18'),(21,'Lens cleaning',NULL,11,'Rajkot Plant',19,22,'high','2026-07-30',NULL,'completed',NULL,NULL,NULL,NULL,'Work completed and verified.','2026-08-01 12:52:18','2026-07-29 12:52:18','2026-08-09 12:52:18'),(22,'Firmware update',NULL,10,'Rajkot Plant',19,23,'medium','2026-07-19',NULL,'completed',NULL,NULL,NULL,NULL,'Work completed and verified.','2026-07-21 12:52:18','2026-07-18 12:52:18','2026-08-09 12:52:18'),(23,'Bearing service',NULL,10,'Rajkot Plant',19,22,'low','2026-07-22',NULL,'completed',NULL,NULL,NULL,NULL,'Work completed and verified.','2026-07-24 12:52:18','2026-07-21 12:52:18','2026-08-09 12:52:18'),(24,'Calibration check',NULL,10,'Rajkot Plant',19,23,'low','2026-07-27',NULL,'completed',NULL,NULL,NULL,NULL,'Work completed and verified.','2026-07-29 12:52:18','2026-07-26 12:52:18','2026-08-09 12:52:18'),(25,'Firmware update',NULL,12,'Rajkot Plant',19,23,'high','2026-07-20',NULL,'completed',NULL,NULL,NULL,NULL,'Work completed and verified.','2026-07-22 12:52:18','2026-07-19 12:52:18','2026-08-09 12:52:18'),(26,'Nozzle replacement',NULL,11,'Rajkot Plant',19,23,'urgent','2026-07-27',NULL,'completed',NULL,NULL,NULL,NULL,'Work completed and verified.','2026-07-29 12:52:18','2026-07-26 12:52:18','2026-08-09 12:52:18'),(27,'Calibration check',NULL,11,'Rajkot Plant',19,22,'urgent','2026-07-31',NULL,'completed',NULL,NULL,NULL,NULL,'Work completed and verified.','2026-08-02 12:52:18','2026-07-30 12:52:18','2026-08-09 12:52:18'),(28,'Nozzle replacement',NULL,11,'Rajkot Plant',19,23,'low','2026-08-09',NULL,'completed',NULL,NULL,NULL,NULL,'Done today.','2026-08-09 11:52:18','2026-08-08 12:52:18','2026-08-09 12:52:18'),(29,'Head alignment',NULL,12,'Rajkot Plant',19,23,'high','2026-08-09',NULL,'completed',NULL,NULL,NULL,NULL,'Done today.','2026-08-09 10:52:18','2026-08-08 12:52:18','2026-08-09 12:52:18'),(30,'Firmware update',NULL,11,'Rajkot Plant',19,22,'medium','2026-08-09',NULL,'completed',NULL,NULL,NULL,NULL,'Done today.','2026-08-09 09:52:18','2026-08-08 12:52:18','2026-08-09 12:52:18'),(31,'Belt replacement',NULL,11,'Rajkot Plant',19,23,'urgent','2026-08-10',NULL,'assigned',NULL,NULL,NULL,NULL,NULL,NULL,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(32,'Firmware update',NULL,9,'Rajkot Plant',19,22,'urgent','2026-08-14',NULL,'assigned',NULL,NULL,NULL,NULL,NULL,NULL,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(33,'Bearing service',NULL,10,'Rajkot Plant',19,22,'medium','2026-08-11',NULL,'in_progress',NULL,NULL,NULL,NULL,NULL,NULL,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(34,'Bearing service',NULL,8,'Rajkot Plant',19,22,'low','2026-08-10',NULL,'in_progress',NULL,NULL,NULL,NULL,NULL,NULL,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(35,'Chiller maintenance',NULL,10,'Rajkot Plant',19,23,'high','2026-08-12',NULL,'on_hold',NULL,NULL,NULL,'Awaiting spare part',NULL,NULL,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(36,'Lens cleaning',NULL,8,'Rajkot Plant',19,22,'medium','2026-08-14',NULL,'pending_approval',NULL,NULL,NULL,NULL,NULL,NULL,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(37,'Calibration check',NULL,10,'Rajkot Plant',19,23,'medium','2026-08-14',NULL,'pending_approval',NULL,NULL,NULL,NULL,NULL,NULL,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(38,'Firmware update',NULL,8,'Rajkot Plant',19,23,'high','2026-08-05','2026-08-08 12:52:18','in_progress',NULL,NULL,NULL,NULL,NULL,NULL,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(39,'Head alignment',NULL,12,'Rajkot Plant',19,23,'medium','2026-08-07','2026-08-08 12:52:18','in_progress',NULL,NULL,NULL,NULL,NULL,NULL,'2026-08-09 12:52:18','2026-08-09 12:52:18');
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `machines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `machines` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `machines` WRITE;
/*!40000 ALTER TABLE `machines` DISABLE KEYS */;
INSERT INTO `machines` VALUES (8,'Fiber Cutter FC-3015',1,22.3000000,70.8000000,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(9,'Tube Laser TL-6022',1,22.3100000,70.8100000,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(10,'CO2 Engraver CE-1390',1,22.3200000,70.8200000,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(11,'Bending Press BP-110',1,22.3300000,70.8300000,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(12,'Welding Station WS-04',0,22.3400000,70.8400000,'2026-08-09 12:52:18','2026-08-09 12:52:18');
/*!40000 ALTER TABLE `machines` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=68 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2014_10_12_000000_create_users_table',1),(2,'2014_10_12_100000_create_password_resets_table',1),(3,'2019_08_19_000000_create_failed_jobs_table',1),(4,'2019_12_14_000001_create_personal_access_tokens_table',1),(5,'2026_07_15_024652_add_performance_indexes',2),(6,'2026_07_15_153715_fix_missing_auto_increment_on_rack_and_softerwere1',3),(7,'2026_07_15_165411_create_cache_table',4),(8,'2026_07_15_165411_create_sessions_table',4),(9,'2026_07_15_165539_add_invetry_product_id_index',4),(10,'2026_07_10_000001_create_product_table',5),(11,'2026_07_10_000002_create_invoice_table',5),(12,'2026_07_10_000003_create_customer_table',5),(13,'2026_07_10_000004_create_invoiceproduct_table',5),(14,'2026_07_10_000005_create_paidamount_table',5),(15,'2026_07_10_000006_create_bank_table',5),(16,'2026_07_10_000007_create_softwaredetails_table',5),(17,'2026_07_10_000008_create_softerwere1_table',5),(18,'2026_07_10_000009_create_addlaser_table',5),(19,'2026_07_10_000010_create_fours_table',5),(20,'2026_07_10_000011_create_power_table',5),(21,'2026_07_10_000012_create_motor_table',5),(22,'2026_07_10_000013_create_gear_table',5),(23,'2026_07_10_000014_create_rack_table',5),(24,'2026_07_10_000015_create_cuttingway_table',5),(25,'2026_07_10_000016_create_cnsthinks_table',5),(26,'2026_07_10_000017_create_invetry_table',5),(27,'2026_07_10_000018_create_quationform_table',5),(28,'2026_07_18_000001_create_permission_tables',6),(29,'2026_07_18_000002_add_is_active_to_users_table',6),(30,'2026_07_18_000006_create_machines_table',7),(31,'2026_07_18_000006_create_notifications_table',7),(32,'2026_07_18_000007_create_jobs_table',8),(33,'2026_07_18_000008_create_job_photos_table',8),(34,'2026_07_18_000009_create_job_audit_logs_table',8),(35,'2026_07_18_000010_create_push_subscriptions_table',8),(36,'2026_07_19_000001_create_employees_table',9),(37,'2026_07_19_000002_create_attendances_table',9),(38,'2026_07_19_000003_create_salary_payments_table',9),(39,'2026_07_19_000004_create_employee_documents_table',9),(40,'2026_07_21_000001_create_audit_logs_table',10),(41,'2026_07_21_000002_normalize_push_subscription_morph_type',10),(42,'2026_07_22_000001_change_quationform_phone_to_string',11),(43,'2026_07_25_000001_add_payment_method_and_reference_to_paidamount',12),(44,'2026_07_25_213252_create_quotation_items_table',13),(45,'2026_07_26_111400_create_client_accounts_table',14),(46,'2026_07_26_111401_create_client_machines_table',14),(47,'2026_07_26_111402_create_ticket_problem_types_table',14),(48,'2026_07_26_111403_create_tickets_table',14),(49,'2026_07_26_111404_create_ticket_photos_table',14),(50,'2026_07_26_122658_add_is_spare_part_to_product_table',15),(51,'2026_07_26_122658_create_spare_part_requests_table',15),(52,'2026_08_01_181242_create_vendors_table',16),(53,'2026_08_05_000000_add_location_to_machines_table',17),(54,'2026_08_05_000001_add_overdue_flagged_at_to_jobs_table',18),(55,'2026_08_06_000000_add_low_stock_notified_at_to_invetry_table',19),(56,'2026_08_06_000001_add_low_stock_threshold_to_invetry_table',20),(57,'2026_08_06_000002_add_product_link_to_quotation_items_table',21),(58,'2026_08_07_000000_create_job_checklist_items_table',22),(59,'2026_08_07_000001_add_integrity_fields_to_job_photos_table',23),(60,'2026_08_07_000002_add_stage_to_job_photos_table',24),(61,'2026_08_09_000000_create_expense_categories_table',25),(62,'2026_08_09_000001_create_vendor_bills_table',26),(63,'2026_08_09_000002_create_vendor_payments_table',26),(64,'2026_08_09_000003_create_daily_transactions_table',27),(65,'2026_08_09_000004_merge_ticket_photos_into_job_photos',28),(66,'2026_08_09_100000_create_chart_accounts_table',29),(67,'2026_08_09_120000_add_missing_auth_columns_to_users_table',30);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `model_type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `model_has_permissions` WRITE;
/*!40000 ALTER TABLE `model_has_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `model_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `model_has_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_roles` (
  `role_id` bigint unsigned NOT NULL,
  `model_type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `model_has_roles` WRITE;
/*!40000 ALTER TABLE `model_has_roles` DISABLE KEYS */;
INSERT INTO `model_has_roles` VALUES (8,'user',19),(10,'user',20),(11,'user',21),(9,'user',22),(9,'user',23);
/*!40000 ALTER TABLE `model_has_roles` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `motor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `motor` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int DEFAULT NULL,
  `companyname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `motor_product_id_index` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `motor` WRITE;
/*!40000 ALTER TABLE `motor` DISABLE KEYS */;
/*!40000 ALTER TABLE `motor` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_id` bigint unsigned NOT NULL,
  `data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `paidamount`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `paidamount` (
  `id` int NOT NULL AUTO_INCREMENT,
  `invoice_id` int DEFAULT NULL,
  `customer_id` int DEFAULT NULL,
  `paidAmount` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `payment_method` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `reference_number` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `paidamount_invoice_id_index` (`invoice_id`),
  KEY `paidamount_customer_id_index` (`customer_id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `paidamount` WRITE;
/*!40000 ALTER TABLE `paidamount` DISABLE KEYS */;
/*!40000 ALTER TABLE `paidamount` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=137 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (69,'dashboard.view','web','2026-08-09 12:52:17','2026-08-09 12:52:17'),(70,'products.view','web','2026-08-09 12:52:17','2026-08-09 12:52:17'),(71,'products.create','web','2026-08-09 12:52:17','2026-08-09 12:52:17'),(72,'products.update','web','2026-08-09 12:52:17','2026-08-09 12:52:17'),(73,'products.delete','web','2026-08-09 12:52:17','2026-08-09 12:52:17'),(74,'products.manage-config','web','2026-08-09 12:52:17','2026-08-09 12:52:17'),(75,'inventory.view','web','2026-08-09 12:52:17','2026-08-09 12:52:17'),(76,'inventory.create','web','2026-08-09 12:52:17','2026-08-09 12:52:17'),(77,'inventory.update','web','2026-08-09 12:52:17','2026-08-09 12:52:17'),(78,'inventory.view-audit','web','2026-08-09 12:52:17','2026-08-09 12:52:17'),(79,'invoices.view','web','2026-08-09 12:52:17','2026-08-09 12:52:17'),(80,'invoices.create','web','2026-08-09 12:52:17','2026-08-09 12:52:17'),(81,'invoices.update','web','2026-08-09 12:52:17','2026-08-09 12:52:17'),(82,'invoices.delete','web','2026-08-09 12:52:17','2026-08-09 12:52:17'),(83,'invoices.view-details','web','2026-08-09 12:52:17','2026-08-09 12:52:17'),(84,'invoices.record-payment','web','2026-08-09 12:52:17','2026-08-09 12:52:17'),(85,'vendors.view','web','2026-08-09 12:52:17','2026-08-09 12:52:17'),(86,'vendors.manage','web','2026-08-09 12:52:17','2026-08-09 12:52:17'),(87,'payment-history.view','web','2026-08-09 12:52:17','2026-08-09 12:52:17'),(88,'quotations.view','web','2026-08-09 12:52:17','2026-08-09 12:52:17'),(89,'quotations.create','web','2026-08-09 12:52:17','2026-08-09 12:52:17'),(90,'quotations.update','web','2026-08-09 12:52:17','2026-08-09 12:52:17'),(91,'quotations.download-pdf','web','2026-08-09 12:52:17','2026-08-09 12:52:17'),(92,'quotations.delete','web','2026-08-09 12:52:17','2026-08-09 12:52:17'),(93,'admin.manage-roles','web','2026-08-09 12:52:17','2026-08-09 12:52:17'),(94,'admin.manage-users','web','2026-08-09 12:52:17','2026-08-09 12:52:17'),(95,'jobs.view-own','web','2026-08-09 12:52:17','2026-08-09 12:52:17'),(96,'jobs.create','web','2026-08-09 12:52:17','2026-08-09 12:52:17'),(97,'jobs.view-all','web','2026-08-09 12:52:17','2026-08-09 12:52:17'),(98,'jobs.approve','web','2026-08-09 12:52:17','2026-08-09 12:52:17'),(99,'jobs.assign','web','2026-08-09 12:52:17','2026-08-09 12:52:17'),(100,'jobs.manage-machines','web','2026-08-09 12:52:17','2026-08-09 12:52:17'),(101,'machines.view-audit','web','2026-08-09 12:52:17','2026-08-09 12:52:17'),(102,'jobs.view-audit','web','2026-08-09 12:52:17','2026-08-09 12:52:17'),(103,'employees.view','web','2026-08-09 12:52:17','2026-08-09 12:52:17'),(104,'employees.manage','web','2026-08-09 12:52:17','2026-08-09 12:52:17'),(105,'attendance.view','web','2026-08-09 12:52:17','2026-08-09 12:52:17'),(106,'attendance.manage','web','2026-08-09 12:52:17','2026-08-09 12:52:17'),(107,'attendance.view-audit','web','2026-08-09 12:52:17','2026-08-09 12:52:17'),(108,'payroll.view','web','2026-08-09 12:52:17','2026-08-09 12:52:17'),(109,'payroll.manage-payments','web','2026-08-09 12:52:17','2026-08-09 12:52:17'),(110,'employees.view-audit','web','2026-08-09 12:52:17','2026-08-09 12:52:17'),(111,'payroll.view-audit','web','2026-08-09 12:52:17','2026-08-09 12:52:17'),(112,'invoices.view-audit','web','2026-08-09 12:52:17','2026-08-09 12:52:17'),(113,'products.view-audit','web','2026-08-09 12:52:17','2026-08-09 12:52:17'),(114,'quotations.view-audit','web','2026-08-09 12:52:17','2026-08-09 12:52:17'),(115,'admin.view-audit','web','2026-08-09 12:52:17','2026-08-09 12:52:17'),(116,'client-machines.view','web','2026-08-09 12:52:17','2026-08-09 12:52:17'),(117,'client-machines.manage','web','2026-08-09 12:52:17','2026-08-09 12:52:17'),(118,'client-machines.view-audit','web','2026-08-09 12:52:17','2026-08-09 12:52:17'),(119,'ticket-problem-types.manage','web','2026-08-09 12:52:17','2026-08-09 12:52:17'),(120,'ticket-problem-types.view-audit','web','2026-08-09 12:52:17','2026-08-09 12:52:17'),(121,'tickets.view','web','2026-08-09 12:52:17','2026-08-09 12:52:17'),(122,'tickets.assign','web','2026-08-09 12:52:17','2026-08-09 12:52:17'),(123,'tickets.view-audit','web','2026-08-09 12:52:17','2026-08-09 12:52:17'),(124,'spare-parts.manage','web','2026-08-09 12:52:17','2026-08-09 12:52:17'),(125,'spare-part-requests.view','web','2026-08-09 12:52:17','2026-08-09 12:52:17'),(126,'spare-part-requests.manage','web','2026-08-09 12:52:17','2026-08-09 12:52:17'),(127,'spare-part-requests.view-audit','web','2026-08-09 12:52:17','2026-08-09 12:52:17'),(128,'reports.view','web','2026-08-09 12:52:17','2026-08-09 12:52:17'),(129,'accounting.view','web','2026-08-09 12:52:17','2026-08-09 12:52:17'),(130,'expenses.view','web','2026-08-09 12:52:17','2026-08-09 12:52:17'),(131,'expenses.manage','web','2026-08-09 12:52:17','2026-08-09 12:52:17'),(132,'expenses.delete','web','2026-08-09 12:52:17','2026-08-09 12:52:17'),(133,'expenses.view-audit','web','2026-08-09 12:52:17','2026-08-09 12:52:17'),(134,'vendor-payments.view','web','2026-08-09 12:52:17','2026-08-09 12:52:17'),(135,'vendor-payments.manage','web','2026-08-09 12:52:17','2026-08-09 12:52:17'),(136,'vendors.view-audit','web','2026-08-09 12:52:17','2026-08-09 12:52:17');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `power`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `power` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int DEFAULT NULL,
  `company` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `modal` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `logo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `power_product_id_index` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `power` WRITE;
/*!40000 ALTER TABLE `power` DISABLE KEYS */;
/*!40000 ALTER TABLE `power` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `product`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `rate` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `unit` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `make` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `is_spare_part` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `product` WRITE;
/*!40000 ALTER TABLE `product` DISABLE KEYS */;
INSERT INTO `product` VALUES (17,'Fiber Laser Nozzle 2.0mm','450','pcs','Raytools',0,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(18,'Protective Lens 30x5','850','pcs','WSX',0,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(19,'Ceramic Ring','320','pcs','Precitec',0,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(20,'Focusing Lens D28 F125','2200','pcs','Raytools',0,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(21,'Collimating Lens D30','2600','pcs','WSX',0,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(22,'Nozzle Connector','1500','pcs','Precitec',0,'2026-08-09 12:52:18','2026-08-09 12:52:18');
/*!40000 ALTER TABLE `product` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `push_subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `push_subscriptions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `subscribable_type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `subscribable_id` bigint unsigned NOT NULL,
  `endpoint` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `public_key` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `auth_token` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content_encoding` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `push_subscriptions_subscribable_type_subscribable_id_index` (`subscribable_type`,`subscribable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `push_subscriptions` WRITE;
/*!40000 ALTER TABLE `push_subscriptions` DISABLE KEYS */;
/*!40000 ALTER TABLE `push_subscriptions` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `quationform`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `quationform` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int DEFAULT NULL,
  `clientname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `companyname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `gstno` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `companyaddress` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `bank` int DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `date` date DEFAULT NULL,
  `reminderdate` date DEFAULT NULL,
  `softweredetails` int DEFAULT NULL,
  `lasercutting` int DEFAULT NULL,
  `focus` int DEFAULT NULL,
  `power` int DEFAULT NULL,
  `inputpower` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cuttingway` int DEFAULT NULL,
  `cncspan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cnslenght` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cuttingrang` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `liftingheight` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `headquantity` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cuttingthickess` int DEFAULT NULL,
  `strokespeed` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cuttingspeed` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `drive` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `motor` int DEFAULT NULL,
  `motortype` int DEFAULT NULL,
  `gearbox` int DEFAULT NULL,
  `rack` int DEFAULT NULL,
  `software` int DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `description1` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `description2` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `amount` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `amount1` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `amount2` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `optionparthyscope` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `quationform` WRITE;
/*!40000 ALTER TABLE `quationform` DISABLE KEYS */;
/*!40000 ALTER TABLE `quationform` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `quotation_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `quotation_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `quotation_id` int NOT NULL,
  `product_id` bigint unsigned DEFAULT NULL,
  `quantity` int unsigned DEFAULT NULL,
  `description` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `quotation_items_quotation_id_foreign` (`quotation_id`),
  CONSTRAINT `quotation_items_quotation_id_foreign` FOREIGN KEY (`quotation_id`) REFERENCES `quationform` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `quotation_items` WRITE;
/*!40000 ALTER TABLE `quotation_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `quotation_items` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `rack`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rack` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int DEFAULT NULL,
  `companyname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `rack_product_id_index` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `rack` WRITE;
/*!40000 ALTER TABLE `rack` DISABLE KEYS */;
/*!40000 ALTER TABLE `rack` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `role_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `role_has_permissions` WRITE;
/*!40000 ALTER TABLE `role_has_permissions` DISABLE KEYS */;
INSERT INTO `role_has_permissions` VALUES (69,8),(70,8),(71,8),(72,8),(73,8),(74,8),(75,8),(76,8),(77,8),(78,8),(79,8),(80,8),(81,8),(82,8),(83,8),(84,8),(85,8),(86,8),(87,8),(88,8),(89,8),(90,8),(91,8),(92,8),(93,8),(94,8),(95,8),(96,8),(97,8),(98,8),(99,8),(100,8),(101,8),(102,8),(103,8),(104,8),(105,8),(106,8),(107,8),(108,8),(109,8),(110,8),(111,8),(112,8),(113,8),(114,8),(115,8),(116,8),(117,8),(118,8),(119,8),(120,8),(121,8),(122,8),(123,8),(124,8),(125,8),(126,8),(127,8),(128,8),(129,8),(130,8),(131,8),(132,8),(133,8),(134,8),(135,8),(136,8),(69,9),(95,9),(96,9),(69,10),(75,10),(95,10),(97,10),(98,10),(99,10),(103,10),(105,10),(121,10),(125,10),(128,10),(69,11),(79,11),(83,11),(84,11),(85,11),(87,11),(128,11),(129,11),(130,11),(131,11),(134,11),(135,11);
/*!40000 ALTER TABLE `role_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (8,'Owner','web','2026-08-09 12:52:17','2026-08-09 12:52:17'),(9,'Worker','web','2026-08-09 12:52:17','2026-08-09 12:52:17'),(10,'Manager','web','2026-08-09 12:52:17','2026-08-09 12:52:17'),(11,'Account','web','2026-08-09 12:52:17','2026-08-09 12:52:17');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `salary_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `salary_payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint unsigned NOT NULL,
  `date` date NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `note` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paid_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `salary_payments_paid_by_foreign` (`paid_by`),
  KEY `salary_payments_employee_id_date_index` (`employee_id`,`date`),
  CONSTRAINT `salary_payments_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `salary_payments_paid_by_foreign` FOREIGN KEY (`paid_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `salary_payments` WRITE;
/*!40000 ALTER TABLE `salary_payments` DISABLE KEYS */;
INSERT INTO `salary_payments` VALUES (5,13,'2026-05-05',22000.00,'Monthly salary',19,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(6,13,'2026-06-05',22000.00,'Monthly salary',19,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(7,13,'2026-07-05',22000.00,'Monthly salary',19,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(8,13,'2026-08-05',22000.00,'Monthly salary',19,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(9,14,'2026-05-05',26000.00,'Monthly salary',19,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(10,14,'2026-06-05',26000.00,'Monthly salary',19,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(11,14,'2026-07-05',26000.00,'Monthly salary',19,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(12,14,'2026-08-05',26000.00,'Monthly salary',19,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(13,15,'2026-05-05',18200.00,'Monthly salary',19,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(14,15,'2026-06-05',18200.00,'Monthly salary',19,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(15,15,'2026-07-05',18200.00,'Monthly salary',19,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(16,15,'2026-08-05',18200.00,'Monthly salary',19,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(17,16,'2026-05-05',16900.00,'Monthly salary',19,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(18,16,'2026-06-05',16900.00,'Monthly salary',19,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(19,16,'2026-07-05',16900.00,'Monthly salary',19,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(20,16,'2026-08-05',16900.00,'Monthly salary',19,'2026-08-09 12:52:18','2026-08-09 12:52:18');
/*!40000 ALTER TABLE `salary_payments` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `softerwere1`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `softerwere1` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int DEFAULT NULL,
  `companyname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `softerwere1_product_id_index` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `softerwere1` WRITE;
/*!40000 ALTER TABLE `softerwere1` DISABLE KEYS */;
/*!40000 ALTER TABLE `softerwere1` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `softwaredetails`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `softwaredetails` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int DEFAULT NULL,
  `company` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `modal` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `logo` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `image` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `softwaredetails_product_id_index` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `softwaredetails` WRITE;
/*!40000 ALTER TABLE `softwaredetails` DISABLE KEYS */;
/*!40000 ALTER TABLE `softwaredetails` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `spare_part_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `spare_part_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `client_machine_id` bigint unsigned NOT NULL,
  `client_account_id` bigint unsigned NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int unsigned NOT NULL DEFAULT '1',
  `note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` enum('pending','approved','fulfilled','rejected') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `spare_part_requests_client_machine_id_foreign` (`client_machine_id`),
  KEY `spare_part_requests_client_account_id_foreign` (`client_account_id`),
  KEY `spare_part_requests_product_id_foreign` (`product_id`),
  CONSTRAINT `spare_part_requests_client_account_id_foreign` FOREIGN KEY (`client_account_id`) REFERENCES `client_accounts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `spare_part_requests_client_machine_id_foreign` FOREIGN KEY (`client_machine_id`) REFERENCES `client_machines` (`id`) ON DELETE CASCADE,
  CONSTRAINT `spare_part_requests_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `spare_part_requests` WRITE;
/*!40000 ALTER TABLE `spare_part_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `spare_part_requests` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `ticket_problem_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ticket_problem_types` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `category` enum('electrical','mechanical') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `ticket_problem_types` WRITE;
/*!40000 ALTER TABLE `ticket_problem_types` DISABLE KEYS */;
INSERT INTO `ticket_problem_types` VALUES (18,'electrical','Power Supply Failure',1,'2026-08-09 12:52:17','2026-08-09 12:52:17'),(19,'electrical','Wiring Fault',1,'2026-08-09 12:52:17','2026-08-09 12:52:17'),(20,'electrical','Motor Not Running',1,'2026-08-09 12:52:17','2026-08-09 12:52:17'),(21,'electrical','Control Panel Error',1,'2026-08-09 12:52:17','2026-08-09 12:52:17'),(22,'electrical','PLC Fault',1,'2026-08-09 12:52:17','2026-08-09 12:52:17'),(23,'electrical','Sensor Fault',1,'2026-08-09 12:52:17','2026-08-09 12:52:17'),(24,'electrical','Short Circuit',1,'2026-08-09 12:52:17','2026-08-09 12:52:17'),(25,'electrical','Cable Damage',1,'2026-08-09 12:52:17','2026-08-09 12:52:17'),(26,'mechanical','Cutting Head Misalignment',1,'2026-08-09 12:52:17','2026-08-09 12:52:17'),(27,'mechanical','Nozzle Damage',1,'2026-08-09 12:52:17','2026-08-09 12:52:17'),(28,'mechanical','Belt / Gear Wear',1,'2026-08-09 12:52:17','2026-08-09 12:52:17'),(29,'mechanical','Rail / Guide Wear',1,'2026-08-09 12:52:17','2026-08-09 12:52:17'),(30,'mechanical','Lubrication Issue',1,'2026-08-09 12:52:17','2026-08-09 12:52:17'),(31,'mechanical','Bearing Failure',1,'2026-08-09 12:52:17','2026-08-09 12:52:17'),(32,'mechanical','Unusual Vibration / Noise',1,'2026-08-09 12:52:17','2026-08-09 12:52:17'),(33,'mechanical','Frame / Structural Issue',1,'2026-08-09 12:52:17','2026-08-09 12:52:17');
/*!40000 ALTER TABLE `ticket_problem_types` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `tickets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tickets` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `client_machine_id` bigint unsigned NOT NULL,
  `client_account_id` bigint unsigned NOT NULL,
  `problem_type_id` bigint unsigned NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` enum('open','assigned','in_progress','resolved') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `job_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tickets_client_machine_id_foreign` (`client_machine_id`),
  KEY `tickets_client_account_id_foreign` (`client_account_id`),
  KEY `tickets_problem_type_id_foreign` (`problem_type_id`),
  KEY `tickets_job_id_foreign` (`job_id`),
  CONSTRAINT `tickets_client_account_id_foreign` FOREIGN KEY (`client_account_id`) REFERENCES `client_accounts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tickets_client_machine_id_foreign` FOREIGN KEY (`client_machine_id`) REFERENCES `client_machines` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tickets_job_id_foreign` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tickets_problem_type_id_foreign` FOREIGN KEY (`problem_type_id`) REFERENCES `ticket_problem_types` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `tickets` WRITE;
/*!40000 ALTER TABLE `tickets` DISABLE KEYS */;
INSERT INTO `tickets` VALUES (8,10,10,18,'Machine needs a service check.','open',NULL,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(9,12,11,18,'Machine needs a service check.','open',NULL,'2026-08-09 12:52:18','2026-08-09 12:52:18');
/*!40000 ALTER TABLE `tickets` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `avatar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (19,'Owner Demo','owner-demo@test.local','2026-08-09 12:52:17','$2y$10$IDemiQon9A3ErR/zlaNFwOiruWQbMqFXLbHULNDYJGlENXr5RpSoy',NULL,1,'2026-08-09 12:52:17','2026-08-09 12:52:17',NULL),(20,'Manager Demo','manager-demo@test.local','2026-08-09 12:52:17','$2y$10$TMcCSI39Mm73ixKjZtb4teUZvjnNkuOaSUpcs2dTI43VM8VsCYGiu',NULL,1,'2026-08-09 12:52:17','2026-08-09 12:52:17',NULL),(21,'Account Demo','account-demo@test.local','2026-08-09 12:52:17','$2y$10$FR43ZMtr511dmDO.EBop2.9i83e/gt1chaWyfeZbCIcdgPI.7T866',NULL,1,'2026-08-09 12:52:17','2026-08-09 12:52:17',NULL),(22,'Ravi Kumar','worker-demo@test.local','2026-08-09 12:52:18','$2y$10$OFaAKcSCLwDTG8HeYkmHNuXo9TkKhmytPDHGMBhL4rspGXJvb4cgC',NULL,1,'2026-08-09 12:52:18','2026-08-09 12:52:18',NULL),(23,'Anil Sharma','worker2-demo@test.local','2026-08-09 12:52:18','$2y$10$SXln.hlwxa/zWbJJcssC4uBDc8Oi84ZRWeOdKRRIZtl.2lKR0u1Lu',NULL,1,'2026-08-09 12:52:18','2026-08-09 12:52:18',NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `vendor_bills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vendor_bills` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` bigint unsigned NOT NULL,
  `bill_number` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `date` date NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `vendor_bills_vendor_id_foreign` (`vendor_id`),
  KEY `vendor_bills_created_by_foreign` (`created_by`),
  CONSTRAINT `vendor_bills_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `vendor_bills_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `vendor_bills` WRITE;
/*!40000 ALTER TABLE `vendor_bills` DISABLE KEYS */;
INSERT INTO `vendor_bills` VALUES (2,3,'BILL-269',24000.00,'2026-04-09','Supplies',19,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(3,3,'BILL-498',15000.00,'2026-05-09','Supplies',19,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(4,4,'BILL-509',31000.00,'2026-04-09','Supplies',19,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(5,4,'BILL-522',55000.00,'2026-06-09','Supplies',19,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(6,5,'BILL-476',19000.00,'2026-06-09','Supplies',19,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(7,5,'BILL-258',55000.00,'2026-06-09','Supplies',19,'2026-08-09 12:52:18','2026-08-09 12:52:18');
/*!40000 ALTER TABLE `vendor_bills` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `vendor_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vendor_payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` bigint unsigned NOT NULL,
  `vendor_bill_id` bigint unsigned DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `date` date NOT NULL,
  `payment_mode` enum('cash','bank','upi','cheque') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `vendor_payments_vendor_id_foreign` (`vendor_id`),
  KEY `vendor_payments_vendor_bill_id_foreign` (`vendor_bill_id`),
  KEY `vendor_payments_created_by_foreign` (`created_by`),
  CONSTRAINT `vendor_payments_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `vendor_payments_vendor_bill_id_foreign` FOREIGN KEY (`vendor_bill_id`) REFERENCES `vendor_bills` (`id`) ON DELETE SET NULL,
  CONSTRAINT `vendor_payments_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `vendor_payments` WRITE;
/*!40000 ALTER TABLE `vendor_payments` DISABLE KEYS */;
INSERT INTO `vendor_payments` VALUES (3,3,2,14400.00,'2026-04-14','bank','Part payment',19,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(4,3,3,9000.00,'2026-05-14','bank','Part payment',19,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(5,4,4,18600.00,'2026-04-14','bank','Part payment',19,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(6,4,5,33000.00,'2026-06-14','bank','Part payment',19,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(7,5,6,11400.00,'2026-06-14','bank','Part payment',19,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(8,5,7,33000.00,'2026-06-14','bank','Part payment',19,'2026-08-09 12:52:18','2026-08-09 12:52:18');
/*!40000 ALTER TABLE `vendor_payments` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `vendors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vendors` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `gstin` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'India',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `vendors` WRITE;
/*!40000 ALTER TABLE `vendors` DISABLE KEYS */;
INSERT INTO `vendors` VALUES (3,'Raytools India Pvt Ltd','Optics',NULL,NULL,NULL,NULL,NULL,'India',NULL,1,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(4,'GIDC Power Supplies','Electrical',NULL,NULL,NULL,NULL,NULL,'India',NULL,1,'2026-08-09 12:52:18','2026-08-09 12:52:18'),(5,'Metro Gases','Consumables',NULL,NULL,NULL,NULL,NULL,'India',NULL,1,'2026-08-09 12:52:18','2026-08-09 12:52:18');
/*!40000 ALTER TABLE `vendors` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

