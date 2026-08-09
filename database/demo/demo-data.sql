-- Oracle Machine Tech CRM - Demo / Showcase Data
-- Fabricated data only (no real customers or production figures).
-- Demo logins (password Dashboard@123): owner-demo@ / manager-demo@ / account-demo@ / worker-demo@ (all @test.local)
-- Client portal logins (password Client@123): client1@test.local / client2@test.local
-- Import into an EMPTY database:  mysql -u USER -p DBNAME < demo-data.sql
-- Generated 2026-08-09 from a clean migrate (schema matches current migrations).


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
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int DEFAULT NULL,
  `company` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `modal` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `decription` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `update_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `addlaser_product_id_index` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
  `status` enum('present','absent','half_day','leave') COLLATE utf8mb4_unicode_ci NOT NULL,
  `overtime_hours` decimal(5,2) NOT NULL DEFAULT '0.00',
  `marked_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `attendances_employee_id_date_unique` (`employee_id`,`date`),
  KEY `attendances_marked_by_foreign` (`marked_by`),
  CONSTRAINT `attendances_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `attendances_marked_by_foreign` FOREIGN KEY (`marked_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
  `auditable_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `auditable_id` bigint unsigned NOT NULL,
  `action` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `field_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `old_value` text COLLATE utf8mb4_unicode_ci,
  `new_value` text COLLATE utf8mb4_unicode_ci,
  `user_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `audit_logs_user_id_foreign` (`user_id`),
  KEY `audit_logs_auditable_type_auditable_id_index` (`auditable_type`,`auditable_id`),
  CONSTRAINT `audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=144 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
INSERT INTO `audit_logs` VALUES (1,'App\\Models\\ChartAccount',1,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(2,'App\\Models\\ChartAccount',2,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(3,'App\\Models\\ChartAccount',3,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(4,'App\\Models\\ChartAccount',4,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(5,'App\\Models\\ChartAccount',5,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(6,'App\\Models\\ChartAccount',6,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(7,'App\\Models\\ChartAccount',7,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(8,'App\\Models\\ChartAccount',8,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(9,'App\\Models\\ChartAccount',9,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(10,'App\\Models\\ChartAccount',10,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(11,'ticket_problem_type',1,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(12,'ticket_problem_type',2,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(13,'ticket_problem_type',3,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(14,'ticket_problem_type',4,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(15,'ticket_problem_type',5,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(16,'ticket_problem_type',6,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(17,'ticket_problem_type',7,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(18,'ticket_problem_type',8,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(19,'ticket_problem_type',9,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(20,'ticket_problem_type',10,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(21,'ticket_problem_type',11,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(22,'ticket_problem_type',12,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(23,'ticket_problem_type',13,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(24,'ticket_problem_type',14,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(25,'ticket_problem_type',15,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(26,'ticket_problem_type',16,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(27,'user',2,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(28,'user',3,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(29,'user',4,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(30,'user',5,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(31,'user',6,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(32,'inventory',1,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(33,'inventory',2,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(34,'inventory',3,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(35,'inventory',4,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(36,'inventory',5,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(37,'inventory',6,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(38,'machine',1,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(39,'machine',2,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(40,'machine',3,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(41,'machine',4,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(42,'machine',5,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(43,'employee',1,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(44,'employee',2,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(45,'employee',3,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(46,'employee',4,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(47,'salary_payment',1,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(48,'salary_payment',2,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(49,'salary_payment',3,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(50,'salary_payment',4,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(51,'salary_payment',5,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(52,'salary_payment',6,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(53,'salary_payment',7,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(54,'salary_payment',8,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(55,'salary_payment',9,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(56,'salary_payment',10,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(57,'salary_payment',11,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(58,'salary_payment',12,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(59,'salary_payment',13,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(60,'salary_payment',14,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(61,'salary_payment',15,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(62,'salary_payment',16,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(63,'vendor',1,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(64,'vendor_bill',1,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(65,'vendor_payment',1,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(66,'vendor_bill',2,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(67,'vendor_payment',2,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(68,'vendor',2,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(69,'vendor_bill',3,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(70,'vendor_payment',3,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(71,'vendor_bill',4,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(72,'vendor_payment',4,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(73,'vendor',3,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(74,'vendor_bill',5,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(75,'vendor_payment',5,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(76,'vendor_bill',6,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(77,'vendor_payment',6,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(78,'expense_category',1,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(79,'expense_category',2,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(80,'expense_category',3,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(81,'expense_category',4,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(82,'expense_category',5,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(83,'expense_category',6,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(84,'expense_category',7,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(85,'daily_transaction',1,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(86,'daily_transaction',2,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(87,'daily_transaction',3,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(88,'daily_transaction',4,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(89,'daily_transaction',5,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(90,'daily_transaction',6,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(91,'daily_transaction',7,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(92,'daily_transaction',8,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(93,'daily_transaction',9,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(94,'daily_transaction',10,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(95,'daily_transaction',11,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(96,'daily_transaction',12,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(97,'daily_transaction',13,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(98,'daily_transaction',14,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(99,'daily_transaction',15,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(100,'daily_transaction',16,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(101,'daily_transaction',17,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(102,'daily_transaction',18,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(103,'daily_transaction',19,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(104,'daily_transaction',20,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(105,'daily_transaction',21,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(106,'daily_transaction',22,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(107,'daily_transaction',23,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(108,'daily_transaction',24,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(109,'daily_transaction',25,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(110,'daily_transaction',26,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(111,'daily_transaction',27,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(112,'daily_transaction',28,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(113,'daily_transaction',29,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(114,'daily_transaction',30,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(115,'daily_transaction',31,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:48'),(116,'daily_transaction',32,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:49'),(117,'daily_transaction',33,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:49'),(118,'daily_transaction',34,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:49'),(119,'daily_transaction',35,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:49'),(120,'daily_transaction',36,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:49'),(121,'daily_transaction',37,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:49'),(122,'daily_transaction',38,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:49'),(123,'daily_transaction',39,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:49'),(124,'daily_transaction',40,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:49'),(125,'daily_transaction',41,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:49'),(126,'daily_transaction',42,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:49'),(127,'daily_transaction',43,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:49'),(128,'daily_transaction',44,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:49'),(129,'daily_transaction',45,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:49'),(130,'daily_transaction',46,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:49'),(131,'daily_transaction',47,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:49'),(132,'daily_transaction',48,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:49'),(133,'daily_transaction',49,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:49'),(134,'daily_transaction',50,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:49'),(135,'daily_transaction',51,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:49'),(136,'client_account',1,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:49'),(137,'client_machine',1,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:49'),(138,'ticket',1,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:49'),(139,'client_machine',2,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:49'),(140,'client_account',2,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:49'),(141,'client_machine',3,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:49'),(142,'ticket',2,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:49'),(143,'client_machine',4,'created',NULL,NULL,NULL,NULL,'2026-08-09 12:59:49');
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `bank`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bank` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `bankholdername` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bankaccountnumber` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bankifsccode` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bankbranchname` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bankname` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `update_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `bank` WRITE;
/*!40000 ALTER TABLE `bank` DISABLE KEYS */;
/*!40000 ALTER TABLE `bank` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
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
  `key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
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
  `code` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('asset','liability','equity','income','expense') COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `chart_accounts_code_unique` (`code`),
  KEY `chart_accounts_type_index` (`type`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `chart_accounts` WRITE;
/*!40000 ALTER TABLE `chart_accounts` DISABLE KEYS */;
INSERT INTO `chart_accounts` VALUES (1,'1000','Cash & Bank','asset',1,0,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(2,'1100','Accounts Receivable','asset',1,1,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(3,'2000','Accounts Payable','liability',1,2,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(4,'2100','GST Payable','liability',1,3,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(5,'3000','Owner\'s Equity','equity',1,4,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(6,'4000','Sales Income','income',1,5,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(7,'4900','Other Income','income',1,6,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(8,'5000','Salaries & Wages','expense',1,7,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(9,'5100','Vendor Purchases','expense',1,8,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(10,'5900','Operating Expenses','expense',1,9,'2026-08-09 12:59:48','2026-08-09 12:59:48');
/*!40000 ALTER TABLE `chart_accounts` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `client_accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `client_accounts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `client_accounts_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `client_accounts` WRITE;
/*!40000 ALTER TABLE `client_accounts` DISABLE KEYS */;
INSERT INTO `client_accounts` VALUES (1,'Shree Engineering','client1@test.local','9959998313','$2y$10$ZLEG3KB7OFlo//80aaloo.i8LluG1p4XxIVtBMrvQY2Fz4OQ/5Ul2',NULL,1,'2026-08-09 12:59:49','2026-08-09 12:59:49'),(2,'Metro Fabricators','client2@test.local','9957170179','$2y$10$SEE3XyU68I1VdN6x8hgySev/dB2VMGCq8vUBpnH.6BFRRRYLbNS86',NULL,1,'2026-08-09 12:59:49','2026-08-09 12:59:49');
/*!40000 ALTER TABLE `client_accounts` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `client_machines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `client_machines` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `client_account_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `invoice_id` bigint unsigned DEFAULT NULL,
  `serial_number` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `client_machines` WRITE;
/*!40000 ALTER TABLE `client_machines` DISABLE KEYS */;
INSERT INTO `client_machines` VALUES (1,1,3,NULL,'SN-33073','2025-08-09','2026-08-09 12:59:49','2026-08-09 12:59:49'),(2,1,3,NULL,'SN-78848','2025-07-09','2026-08-09 12:59:49','2026-08-09 12:59:49'),(3,2,6,NULL,'SN-48075','2026-03-09','2026-08-09 12:59:49','2026-08-09 12:59:49'),(4,2,2,NULL,'SN-45384','2026-05-09','2026-08-09 12:59:49','2026-08-09 12:59:49');
/*!40000 ALTER TABLE `client_machines` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `cnsthinks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cnsthinks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int DEFAULT NULL,
  `cuttingthinks` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `cnsthinks_product_id_index` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `cnsthinks` WRITE;
/*!40000 ALTER TABLE `cnsthinks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cnsthinks` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `customer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `customer` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `invoice_id` int DEFAULT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `phone` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `billinggst` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `billingpan` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sname` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `saddress` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sphone` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sstate` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shippinggst` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shippingpan` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `customer_invoice_id_index` (`invoice_id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `customer` WRITE;
/*!40000 ALTER TABLE `customer` DISABLE KEYS */;
INSERT INTO `customer` VALUES (1,1,'Precision Cut Co','Plot 12, GIDC, Rajkot','9896164781',NULL,'Gujarat',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-04 12:59:48','2026-05-04 12:59:48'),(2,2,'Sunrise Metals','Plot 12, GIDC, Rajkot','9853718490',NULL,'Gujarat',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-28 12:59:48','2026-06-28 12:59:48'),(3,3,'Gujarat Steel Works','Plot 12, GIDC, Rajkot','9850605305',NULL,'Gujarat',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-31 12:59:48','2026-07-31 12:59:48'),(4,4,'Anand Industries','Plot 12, GIDC, Rajkot','9858598208',NULL,'Gujarat',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-01 12:59:48','2026-05-01 12:59:48'),(5,5,'Sunrise Metals','Plot 12, GIDC, Rajkot','9890202196',NULL,'Gujarat',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-02-16 12:59:48','2026-02-16 12:59:48'),(6,6,'Shree Engineering','Plot 12, GIDC, Rajkot','9898580866',NULL,'Gujarat',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-02-15 12:59:48','2026-02-15 12:59:48'),(7,7,'Anand Industries','Plot 12, GIDC, Rajkot','9855418039',NULL,'Gujarat',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-04 12:59:48','2026-07-04 12:59:48'),(8,8,'Gujarat Steel Works','Plot 12, GIDC, Rajkot','9876219916',NULL,'Gujarat',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-24 12:59:48','2026-07-24 12:59:48'),(9,9,'Sunrise Metals','Plot 12, GIDC, Rajkot','9896839371',NULL,'Gujarat',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-03-08 12:59:48','2026-03-08 12:59:48'),(10,10,'Gujarat Steel Works','Plot 12, GIDC, Rajkot','9872305961',NULL,'Gujarat',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-04-07 12:59:48','2026-04-07 12:59:48'),(11,11,'Shree Engineering','Plot 12, GIDC, Rajkot','9882654490',NULL,'Gujarat',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-23 12:59:48','2026-06-23 12:59:48'),(12,12,'Sunrise Metals','Plot 12, GIDC, Rajkot','9866159606',NULL,'Gujarat',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-05 12:59:48','2026-07-05 12:59:48'),(13,13,'Gujarat Steel Works','Plot 12, GIDC, Rajkot','9825067168',NULL,'Gujarat',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-09 12:59:48','2026-07-09 12:59:48'),(14,14,'Precision Cut Co','Plot 12, GIDC, Rajkot','9890732898',NULL,'Gujarat',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-18 12:59:48','2026-07-18 12:59:48'),(15,15,'Gujarat Steel Works','Plot 12, GIDC, Rajkot','9866345204',NULL,'Gujarat',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-02-21 12:59:48','2026-02-21 12:59:48'),(16,16,'Anand Industries','Plot 12, GIDC, Rajkot','9854794757',NULL,'Gujarat',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-15 12:59:48','2026-05-15 12:59:48'),(17,17,'Gujarat Steel Works','Plot 12, GIDC, Rajkot','9880220150',NULL,'Gujarat',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 12:59:48','2026-05-27 12:59:48'),(18,18,'Shree Engineering','Plot 12, GIDC, Rajkot','9881920919',NULL,'Gujarat',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-02-14 12:59:48','2026-02-14 12:59:48'),(19,19,'Anand Industries','Plot 12, GIDC, Rajkot','9892556643',NULL,'Gujarat',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-20 12:59:48','2026-07-20 12:59:48'),(20,20,'Shree Engineering','Plot 12, GIDC, Rajkot','9828922109',NULL,'Gujarat',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 12:59:48','2026-05-27 12:59:48'),(21,21,'Shree Engineering','Plot 12, GIDC, Rajkot','9899939204',NULL,'Gujarat',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-02-27 12:59:48','2026-02-27 12:59:48'),(22,22,'Gujarat Steel Works','Plot 12, GIDC, Rajkot','9862427992',NULL,'Gujarat',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-04-30 12:59:48','2026-04-30 12:59:48'),(23,23,'Gujarat Steel Works','Plot 12, GIDC, Rajkot','9878619440',NULL,'Gujarat',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-25 12:59:48','2026-07-25 12:59:48'),(24,24,'Metro Fabricators','Plot 12, GIDC, Rajkot','9834495271',NULL,'Gujarat',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-02 12:59:48','2026-07-02 12:59:48'),(25,25,'Precision Cut Co','Plot 12, GIDC, Rajkot','9819632997',NULL,'Gujarat',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-24 12:59:48','2026-06-24 12:59:48'),(26,26,'Gujarat Steel Works','Plot 12, GIDC, Rajkot','9878425954',NULL,'Gujarat',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-06 12:59:48','2026-05-06 12:59:48'),(27,27,'Precision Cut Co','Plot 12, GIDC, Rajkot','9892324459',NULL,'Gujarat',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-14 12:59:48','2026-07-14 12:59:48'),(28,28,'Anand Industries','Plot 12, GIDC, Rajkot','9856624634',NULL,'Gujarat',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-02-12 12:59:48','2026-02-12 12:59:48');
/*!40000 ALTER TABLE `customer` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `cuttingway`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cuttingway` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int DEFAULT NULL,
  `cuttingway` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `cuttingway_product_id_index` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
  `type` enum('payment','receipt') COLLATE utf8mb4_unicode_ci NOT NULL,
  `expense_category_id` bigint unsigned NOT NULL,
  `party_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `party_id` bigint unsigned DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `date` date NOT NULL,
  `payment_mode` enum('cash','bank','upi','cheque') COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `receipt_photo` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `linked_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `linked_id` bigint unsigned DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `daily_transactions_expense_category_id_foreign` (`expense_category_id`),
  KEY `daily_transactions_created_by_foreign` (`created_by`),
  KEY `daily_transactions_type_date_index` (`type`,`date`),
  CONSTRAINT `daily_transactions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `daily_transactions_expense_category_id_foreign` FOREIGN KEY (`expense_category_id`) REFERENCES `expense_categories` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `daily_transactions` WRITE;
/*!40000 ALTER TABLE `daily_transactions` DISABLE KEYS */;
INSERT INTO `daily_transactions` VALUES (1,'payment',2,NULL,NULL,12000.00,'2026-03-30','upi','Diesel / Fuel',NULL,NULL,NULL,2,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(2,'payment',4,NULL,NULL,3500.00,'2026-03-19','upi','Maintenance',NULL,NULL,NULL,2,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(3,'payment',4,NULL,NULL,3000.00,'2026-04-04','upi','Maintenance',NULL,NULL,NULL,2,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(4,'payment',4,NULL,NULL,11000.00,'2026-03-27','upi','Maintenance',NULL,NULL,NULL,2,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(5,'payment',2,NULL,NULL,9500.00,'2026-03-23','upi','Diesel / Fuel',NULL,NULL,NULL,2,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(6,'payment',5,NULL,NULL,5000.00,'2026-03-20','upi','Office Supplies',NULL,NULL,NULL,2,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(7,'payment',1,NULL,NULL,8500.00,'2026-03-22','upi','Electricity',NULL,NULL,NULL,2,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(8,'receipt',7,NULL,NULL,9000.00,'2026-03-09','cash','Scrap Sale',NULL,NULL,NULL,2,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(9,'receipt',6,NULL,NULL,9000.00,'2026-03-23','cash','Other Income',NULL,NULL,NULL,2,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(10,'payment',5,NULL,NULL,7000.00,'2026-04-21','bank','Office Supplies',NULL,NULL,NULL,2,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(11,'payment',4,NULL,NULL,10500.00,'2026-04-23','cash','Maintenance',NULL,NULL,NULL,2,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(12,'payment',5,NULL,NULL,3000.00,'2026-05-03','bank','Office Supplies',NULL,NULL,NULL,2,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(13,'payment',2,NULL,NULL,7000.00,'2026-04-23','bank','Diesel / Fuel',NULL,NULL,NULL,2,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(14,'payment',3,NULL,NULL,9500.00,'2026-04-21','bank','Rent',NULL,NULL,NULL,2,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(15,'payment',2,NULL,NULL,6000.00,'2026-04-25','cash','Diesel / Fuel',NULL,NULL,NULL,2,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(16,'payment',1,NULL,NULL,11000.00,'2026-04-19','cash','Electricity',NULL,NULL,NULL,2,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(17,'receipt',7,NULL,NULL,11000.00,'2026-04-20','cash','Scrap Sale',NULL,NULL,NULL,2,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(18,'receipt',6,NULL,NULL,9000.00,'2026-04-20','cash','Other Income',NULL,NULL,NULL,2,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(19,'receipt',6,NULL,NULL,11000.00,'2026-05-01','bank','Other Income',NULL,NULL,NULL,2,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(20,'payment',2,NULL,NULL,8000.00,'2026-05-10','bank','Diesel / Fuel',NULL,NULL,NULL,2,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(21,'payment',2,NULL,NULL,4500.00,'2026-05-31','upi','Diesel / Fuel',NULL,NULL,NULL,2,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(22,'payment',4,NULL,NULL,12500.00,'2026-05-10','upi','Maintenance',NULL,NULL,NULL,2,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(23,'payment',2,NULL,NULL,1000.00,'2026-05-27','upi','Diesel / Fuel',NULL,NULL,NULL,2,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(24,'payment',4,NULL,NULL,9000.00,'2026-05-18','bank','Maintenance',NULL,NULL,NULL,2,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(25,'payment',5,NULL,NULL,3500.00,'2026-05-31','upi','Office Supplies',NULL,NULL,NULL,2,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(26,'receipt',7,NULL,NULL,4000.00,'2026-05-12','bank','Scrap Sale',NULL,NULL,NULL,2,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(27,'receipt',7,NULL,NULL,12000.00,'2026-05-24','upi','Scrap Sale',NULL,NULL,NULL,2,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(28,'receipt',6,NULL,NULL,12000.00,'2026-05-09','upi','Other Income',NULL,NULL,NULL,2,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(29,'payment',4,NULL,NULL,2000.00,'2026-06-24','bank','Maintenance',NULL,NULL,NULL,2,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(30,'payment',1,NULL,NULL,2000.00,'2026-06-22','upi','Electricity',NULL,NULL,NULL,2,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(31,'payment',2,NULL,NULL,12000.00,'2026-07-05','cash','Diesel / Fuel',NULL,NULL,NULL,2,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(32,'payment',3,NULL,NULL,1500.00,'2026-06-11','cash','Rent',NULL,NULL,NULL,2,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(33,'payment',4,NULL,NULL,10000.00,'2026-06-09','upi','Maintenance',NULL,NULL,NULL,2,'2026-08-09 12:59:49','2026-08-09 12:59:49'),(34,'receipt',6,NULL,NULL,12000.00,'2026-06-16','bank','Other Income',NULL,NULL,NULL,2,'2026-08-09 12:59:49','2026-08-09 12:59:49'),(35,'receipt',6,NULL,NULL,7000.00,'2026-06-18','bank','Other Income',NULL,NULL,NULL,2,'2026-08-09 12:59:49','2026-08-09 12:59:49'),(36,'payment',1,NULL,NULL,10500.00,'2026-07-24','upi','Electricity',NULL,NULL,NULL,2,'2026-08-09 12:59:49','2026-08-09 12:59:49'),(37,'payment',4,NULL,NULL,8000.00,'2026-07-20','upi','Maintenance',NULL,NULL,NULL,2,'2026-08-09 12:59:49','2026-08-09 12:59:49'),(38,'payment',5,NULL,NULL,9000.00,'2026-08-01','cash','Office Supplies',NULL,NULL,NULL,2,'2026-08-09 12:59:49','2026-08-09 12:59:49'),(39,'payment',5,NULL,NULL,5000.00,'2026-07-23','bank','Office Supplies',NULL,NULL,NULL,2,'2026-08-09 12:59:49','2026-08-09 12:59:49'),(40,'payment',3,NULL,NULL,4500.00,'2026-07-24','bank','Rent',NULL,NULL,NULL,2,'2026-08-09 12:59:49','2026-08-09 12:59:49'),(41,'receipt',6,NULL,NULL,11000.00,'2026-07-23','cash','Other Income',NULL,NULL,NULL,2,'2026-08-09 12:59:49','2026-08-09 12:59:49'),(42,'receipt',6,NULL,NULL,6000.00,'2026-07-11','bank','Other Income',NULL,NULL,NULL,2,'2026-08-09 12:59:49','2026-08-09 12:59:49'),(43,'receipt',7,NULL,NULL,10000.00,'2026-07-21','cash','Scrap Sale',NULL,NULL,NULL,2,'2026-08-09 12:59:49','2026-08-09 12:59:49'),(44,'payment',1,NULL,NULL,5000.00,'2026-08-31','upi','Electricity',NULL,NULL,NULL,2,'2026-08-09 12:59:49','2026-08-09 12:59:49'),(45,'payment',5,NULL,NULL,11000.00,'2026-08-15','bank','Office Supplies',NULL,NULL,NULL,2,'2026-08-09 12:59:49','2026-08-09 12:59:49'),(46,'payment',2,NULL,NULL,5000.00,'2026-09-04','bank','Diesel / Fuel',NULL,NULL,NULL,2,'2026-08-09 12:59:49','2026-08-09 12:59:49'),(47,'payment',3,NULL,NULL,11000.00,'2026-09-03','cash','Rent',NULL,NULL,NULL,2,'2026-08-09 12:59:49','2026-08-09 12:59:49'),(48,'payment',3,NULL,NULL,5000.00,'2026-09-03','upi','Rent',NULL,NULL,NULL,2,'2026-08-09 12:59:49','2026-08-09 12:59:49'),(49,'payment',2,NULL,NULL,5500.00,'2026-08-29','upi','Diesel / Fuel',NULL,NULL,NULL,2,'2026-08-09 12:59:49','2026-08-09 12:59:49'),(50,'receipt',6,NULL,NULL,12000.00,'2026-09-04','upi','Other Income',NULL,NULL,NULL,2,'2026-08-09 12:59:49','2026-08-09 12:59:49'),(51,'receipt',6,NULL,NULL,8000.00,'2026-08-14','bank','Other Income',NULL,NULL,NULL,2,'2026-08-09 12:59:49','2026-08-09 12:59:49');
/*!40000 ALTER TABLE `daily_transactions` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `employee_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `employee_documents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint unsigned NOT NULL,
  `document_type` enum('aadhar','pan','driving_license','voter_id','other') COLLATE utf8mb4_unicode_ci NOT NULL,
  `document_number` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `path` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `uploaded_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_documents_employee_id_foreign` (`employee_id`),
  KEY `employee_documents_uploaded_by_foreign` (`uploaded_by`),
  CONSTRAINT `employee_documents_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_documents_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `emergency_contact_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emergency_contact_phone` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `department` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `designation` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `joining_date` date NOT NULL,
  `pay_type` enum('monthly','daily') COLLATE utf8mb4_unicode_ci NOT NULL,
  `pay_rate` decimal(10,2) NOT NULL,
  `overtime_rate_per_hour` decimal(10,2) NOT NULL DEFAULT '0.00',
  `bank_account_holder_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_account_number` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_ifsc` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employees_user_id_foreign` (`user_id`),
  KEY `employees_is_active_index` (`is_active`),
  CONSTRAINT `employees_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `employees` WRITE;
/*!40000 ALTER TABLE `employees` DISABLE KEYS */;
INSERT INTO `employees` VALUES (1,'Suresh Yadav','9797557661',NULL,NULL,NULL,NULL,'Production','Technician','2025-08-09','monthly',22000.00,120.00,NULL,NULL,NULL,NULL,NULL,1,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(2,'Prakash Mehta','9795496699',NULL,NULL,NULL,NULL,'Production','Technician','2025-08-09','monthly',26000.00,120.00,NULL,NULL,NULL,NULL,NULL,1,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(3,'Dinesh Patel','9759289366',NULL,NULL,NULL,NULL,'Production','Technician','2025-08-09','daily',700.00,120.00,NULL,NULL,NULL,NULL,NULL,1,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(4,'Kiran Rao','9713821318',NULL,NULL,NULL,NULL,'Production','Technician','2025-08-09','daily',650.00,120.00,NULL,NULL,NULL,NULL,NULL,1,'2026-08-09 12:59:48','2026-08-09 12:59:48');
/*!40000 ALTER TABLE `employees` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `expense_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `expense_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('payment','receipt') COLLATE utf8mb4_unicode_ci NOT NULL,
  `party_model` enum('employee','vendor','client_account') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `expense_categories` WRITE;
/*!40000 ALTER TABLE `expense_categories` DISABLE KEYS */;
INSERT INTO `expense_categories` VALUES (1,'Electricity','payment',NULL,1,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(2,'Diesel / Fuel','payment',NULL,1,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(3,'Rent','payment',NULL,1,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(4,'Maintenance','payment',NULL,1,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(5,'Office Supplies','payment',NULL,1,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(6,'Other Income','receipt',NULL,1,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(7,'Scrap Sale','receipt',NULL,1,'2026-08-09 12:59:48','2026-08-09 12:59:48');
/*!40000 ALTER TABLE `expense_categories` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `fours`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fours` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int DEFAULT NULL,
  `modal` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `company` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fours_product_id_index` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `fours` WRITE;
/*!40000 ALTER TABLE `fours` DISABLE KEYS */;
/*!40000 ALTER TABLE `fours` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `gear`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `gear` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int DEFAULT NULL,
  `companyname` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `gear_product_id_index` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `gear` WRITE;
/*!40000 ALTER TABLE `gear` DISABLE KEYS */;
/*!40000 ALTER TABLE `gear` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `invetry`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `invetry` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int DEFAULT NULL,
  `quantity` int DEFAULT NULL,
  `low_stock_threshold` int unsigned DEFAULT NULL,
  `low_stock_notified_at` timestamp NULL DEFAULT NULL,
  `vandername` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rate` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `invetry_product_id_index` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `invetry` WRITE;
/*!40000 ALTER TABLE `invetry` DISABLE KEYS */;
INSERT INTO `invetry` VALUES (1,1,40,NULL,NULL,'Demo Supplier','400','2026-08-09 18:29:48','2026-08-09 18:29:48'),(2,2,3,NULL,NULL,'Demo Supplier','400','2026-08-09 18:29:48','2026-08-09 18:29:48'),(3,3,60,NULL,NULL,'Demo Supplier','400','2026-08-09 18:29:48','2026-08-09 18:29:48'),(4,4,5,NULL,NULL,'Demo Supplier','400','2026-08-09 18:29:48','2026-08-09 18:29:48'),(5,5,25,NULL,NULL,'Demo Supplier','400','2026-08-09 18:29:48','2026-08-09 18:29:48'),(6,6,18,NULL,NULL,'Demo Supplier','400','2026-08-09 18:29:48','2026-08-09 18:29:48');
/*!40000 ALTER TABLE `invetry` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `invoice`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `invoice` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `invoice_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date` date DEFAULT NULL,
  `bankname` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `accountholder` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bankaccountnumber` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bankifsccode` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bankbranchname` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `totalamountbeforetax` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amountwithtax` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paycondition` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `duedate` date DEFAULT NULL,
  `placesupply` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `paidamount` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remaining_amount` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `challanno` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ewaybillno` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ewaybilldate` date DEFAULT NULL,
  `despatchthrough` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `TransportVehicleNo` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pono` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `invoice` WRITE;
/*!40000 ALTER TABLE `invoice` DISABLE KEYS */;
INSERT INTO `invoice` VALUES (1,'INV-1000','2026-05-04',NULL,NULL,NULL,NULL,NULL,'Demo invoice','12700','12700','14986',NULL,NULL,'Gujarat','7493','7493',NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-04 18:29:48','2026-05-04 18:29:48'),(2,'INV-1001','2026-06-28',NULL,NULL,NULL,NULL,NULL,'Demo invoice','15400','15400','18172',NULL,NULL,'Gujarat','0','18172',NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-28 18:29:48','2026-06-28 18:29:48'),(3,'INV-1002','2026-07-31',NULL,NULL,NULL,NULL,NULL,'Demo invoice','40050','40050','47259',NULL,NULL,'Gujarat','23629.5','23629.5',NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-31 18:29:48','2026-07-31 18:29:48'),(4,'INV-1003','2026-05-01',NULL,NULL,NULL,NULL,NULL,'Demo invoice','21850','21850','25783',NULL,NULL,'Gujarat','12891.5','12891.5',NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-01 18:29:48','2026-05-01 18:29:48'),(5,'INV-1004','2026-02-16',NULL,NULL,NULL,NULL,NULL,'Demo invoice','12960','12960','15292.8',NULL,NULL,'Gujarat','0','15292.8',NULL,NULL,NULL,NULL,NULL,NULL,'2026-02-16 18:29:48','2026-02-16 18:29:48'),(6,'INV-1005','2026-02-15',NULL,NULL,NULL,NULL,NULL,'Demo invoice','60800','60800','71744',NULL,NULL,'Gujarat','71744','0',NULL,NULL,NULL,NULL,NULL,NULL,'2026-02-15 18:29:48','2026-02-15 18:29:48'),(7,'INV-1006','2026-07-04',NULL,NULL,NULL,NULL,NULL,'Demo invoice','27300','27300','32214',NULL,NULL,'Gujarat','16107','16107',NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-04 18:29:48','2026-07-04 18:29:48'),(8,'INV-1007','2026-07-24',NULL,NULL,NULL,NULL,NULL,'Demo invoice','25950','25950','30621',NULL,NULL,'Gujarat','30621','0',NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-24 18:29:48','2026-07-24 18:29:48'),(9,'INV-1008','2026-03-08',NULL,NULL,NULL,NULL,NULL,'Demo invoice','10200','10200','12036',NULL,NULL,'Gujarat','12036','0',NULL,NULL,NULL,NULL,NULL,NULL,'2026-03-08 18:29:48','2026-03-08 18:29:48'),(10,'INV-1009','2026-04-07',NULL,NULL,NULL,NULL,NULL,'Demo invoice','37060','37060','43730.8',NULL,NULL,'Gujarat','43730.8','0',NULL,NULL,NULL,NULL,NULL,NULL,'2026-04-07 18:29:48','2026-04-07 18:29:48'),(11,'INV-1010','2026-06-23',NULL,NULL,NULL,NULL,NULL,'Demo invoice','6000','6000','7080',NULL,NULL,'Gujarat','3540','3540',NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-23 18:29:48','2026-06-23 18:29:48'),(12,'INV-1011','2026-07-05',NULL,NULL,NULL,NULL,NULL,'Demo invoice','18150','18150','21417',NULL,NULL,'Gujarat','10708.5','10708.5',NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-05 18:29:48','2026-07-05 18:29:48'),(13,'INV-1012','2026-07-09',NULL,NULL,NULL,NULL,NULL,'Demo invoice','10200','10200','12036',NULL,NULL,'Gujarat','12036','0',NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-09 18:29:48','2026-07-09 18:29:48'),(14,'INV-1013','2026-07-18',NULL,NULL,NULL,NULL,NULL,'Demo invoice','26100','26100','30798',NULL,NULL,'Gujarat','0','30798',NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-18 18:29:48','2026-07-18 18:29:48'),(15,'INV-1014','2026-02-21',NULL,NULL,NULL,NULL,NULL,'Demo invoice','3200','3200','3776',NULL,NULL,'Gujarat','0','3776',NULL,NULL,NULL,NULL,NULL,NULL,'2026-02-21 18:29:48','2026-02-21 18:29:48'),(16,'INV-1015','2026-05-15',NULL,NULL,NULL,NULL,NULL,'Demo invoice','9350','9350','11033',NULL,NULL,'Gujarat','0','11033',NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-15 18:29:48','2026-05-15 18:29:48'),(17,'INV-1016','2026-05-27',NULL,NULL,NULL,NULL,NULL,'Demo invoice','24200','24200','28556',NULL,NULL,'Gujarat','28556','0',NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 18:29:48','2026-05-27 18:29:48'),(18,'INV-1017','2026-02-14',NULL,NULL,NULL,NULL,NULL,'Demo invoice','8800','8800','10384',NULL,NULL,'Gujarat','10384','0',NULL,NULL,NULL,NULL,NULL,NULL,'2026-02-14 18:29:48','2026-02-14 18:29:48'),(19,'INV-1018','2026-07-20',NULL,NULL,NULL,NULL,NULL,'Demo invoice','1700','1700','2006',NULL,NULL,'Gujarat','2006','0',NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-20 18:29:48','2026-07-20 18:29:48'),(20,'INV-1019','2026-05-27',NULL,NULL,NULL,NULL,NULL,'Demo invoice','42900','42900','50622',NULL,NULL,'Gujarat','25311','25311',NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 18:29:48','2026-05-27 18:29:48'),(21,'INV-1020','2026-02-27',NULL,NULL,NULL,NULL,NULL,'Demo invoice','50460','50460','59542.8',NULL,NULL,'Gujarat','0','59542.8',NULL,NULL,NULL,NULL,NULL,NULL,'2026-02-27 18:29:48','2026-02-27 18:29:48'),(22,'INV-1021','2026-04-30',NULL,NULL,NULL,NULL,NULL,'Demo invoice','22500','22500','26550',NULL,NULL,'Gujarat','0','26550',NULL,NULL,NULL,NULL,NULL,NULL,'2026-04-30 18:29:48','2026-04-30 18:29:48'),(23,'INV-1022','2026-07-25',NULL,NULL,NULL,NULL,NULL,'Demo invoice','29500','29500','34810',NULL,NULL,'Gujarat','34810','0',NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-25 18:29:48','2026-07-25 18:29:48'),(24,'INV-1023','2026-07-02',NULL,NULL,NULL,NULL,NULL,'Demo invoice','4790','4790','5652.2',NULL,NULL,'Gujarat','0','5652.2',NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-02 18:29:48','2026-07-02 18:29:48'),(25,'INV-1024','2026-06-24',NULL,NULL,NULL,NULL,NULL,'Demo invoice','57300','57300','67614',NULL,NULL,'Gujarat','67614','0',NULL,NULL,NULL,NULL,NULL,NULL,'2026-06-24 18:29:48','2026-06-24 18:29:48'),(26,'INV-1025','2026-05-06',NULL,NULL,NULL,NULL,NULL,'Demo invoice','3400','3400','4012',NULL,NULL,'Gujarat','2006','2006',NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-06 18:29:48','2026-05-06 18:29:48'),(27,'INV-1026','2026-07-14',NULL,NULL,NULL,NULL,NULL,'Demo invoice','12550','12550','14809',NULL,NULL,'Gujarat','0','14809',NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-14 18:29:48','2026-07-14 18:29:48'),(28,'INV-1027','2026-02-12',NULL,NULL,NULL,NULL,NULL,'Demo invoice','32250','32250','38055',NULL,NULL,'Gujarat','0','38055',NULL,NULL,NULL,NULL,NULL,NULL,'2026-02-12 18:29:48','2026-02-12 18:29:48');
/*!40000 ALTER TABLE `invoice` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `invoiceproduct`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `invoiceproduct` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `invoice_id` int DEFAULT NULL,
  `product_name` int DEFAULT NULL,
  `unit` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hsn` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rate` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quantity` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gst` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gstamount` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `totalamount` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `invoiceproduct` WRITE;
/*!40000 ALTER TABLE `invoiceproduct` DISABLE KEYS */;
INSERT INTO `invoiceproduct` VALUES (1,1,3,'pcs','9013','320','15','4800','18','864','5664','2026-05-04 18:29:48','2026-05-04 18:29:48'),(2,1,1,'pcs','9013','450','10','4500','18','810','5310','2026-05-04 18:29:48','2026-05-04 18:29:48'),(3,1,2,'pcs','9013','850','4','3400','18','612','4012','2026-05-04 18:29:48','2026-05-04 18:29:48'),(4,2,4,'pcs','9013','2200','7','15400','18','2772','18172','2026-06-28 18:29:48','2026-06-28 18:29:48'),(5,3,2,'pcs','9013','850','4','3400','18','612','4012','2026-07-31 18:29:48','2026-07-31 18:29:48'),(6,3,1,'pcs','9013','450','13','5850','18','1053','6903','2026-07-31 18:29:48','2026-07-31 18:29:48'),(7,3,4,'pcs','9013','2200','14','30800','18','5544','36344','2026-07-31 18:29:48','2026-07-31 18:29:48'),(8,4,4,'pcs','9013','2200','8','17600','18','3168','20768','2026-05-01 18:29:48','2026-05-01 18:29:48'),(9,4,2,'pcs','9013','850','5','4250','18','765','5015','2026-05-01 18:29:48','2026-05-01 18:29:48'),(10,5,3,'pcs','9013','320','8','2560','18','460.8','3020.8','2026-02-16 18:29:48','2026-02-16 18:29:48'),(11,5,5,'pcs','9013','2600','4','10400','18','1872','12272','2026-02-16 18:29:48','2026-02-16 18:29:48'),(12,6,4,'pcs','9013','2200','12','26400','18','4752','31152','2026-02-15 18:29:48','2026-02-15 18:29:48'),(13,6,3,'pcs','9013','320','10','3200','18','576','3776','2026-02-15 18:29:48','2026-02-15 18:29:48'),(14,6,5,'pcs','9013','2600','12','31200','18','5616','36816','2026-02-15 18:29:48','2026-02-15 18:29:48'),(15,7,3,'pcs','9013','320','15','4800','18','864','5664','2026-07-04 18:29:48','2026-07-04 18:29:48'),(16,7,6,'pcs','9013','1500','15','22500','18','4050','26550','2026-07-04 18:29:48','2026-07-04 18:29:48'),(17,8,6,'pcs','9013','1500','14','21000','18','3780','24780','2026-07-24 18:29:48','2026-07-24 18:29:48'),(18,8,1,'pcs','9013','450','11','4950','18','891','5841','2026-07-24 18:29:48','2026-07-24 18:29:48'),(19,9,3,'pcs','9013','320','15','4800','18','864','5664','2026-03-08 18:29:48','2026-03-08 18:29:48'),(20,9,1,'pcs','9013','450','7','3150','18','567','3717','2026-03-08 18:29:48','2026-03-08 18:29:48'),(21,9,1,'pcs','9013','450','5','2250','18','405','2655','2026-03-08 18:29:48','2026-03-08 18:29:48'),(22,10,6,'pcs','9013','1500','8','12000','18','2160','14160','2026-04-07 18:29:48','2026-04-07 18:29:48'),(23,10,6,'pcs','9013','1500','15','22500','18','4050','26550','2026-04-07 18:29:48','2026-04-07 18:29:48'),(24,10,3,'pcs','9013','320','8','2560','18','460.8','3020.8','2026-04-07 18:29:48','2026-04-07 18:29:48'),(25,11,6,'pcs','9013','1500','4','6000','18','1080','7080','2026-06-23 18:29:48','2026-06-23 18:29:48'),(26,12,6,'pcs','9013','1500','4','6000','18','1080','7080','2026-07-05 18:29:48','2026-07-05 18:29:48'),(27,12,1,'pcs','9013','450','15','6750','18','1215','7965','2026-07-05 18:29:48','2026-07-05 18:29:48'),(28,12,1,'pcs','9013','450','12','5400','18','972','6372','2026-07-05 18:29:48','2026-07-05 18:29:48'),(29,13,2,'pcs','9013','850','12','10200','18','1836','12036','2026-07-09 18:29:48','2026-07-09 18:29:48'),(30,14,4,'pcs','9013','2200','3','6600','18','1188','7788','2026-07-18 18:29:48','2026-07-18 18:29:48'),(31,14,6,'pcs','9013','1500','13','19500','18','3510','23010','2026-07-18 18:29:48','2026-07-18 18:29:48'),(32,15,3,'pcs','9013','320','10','3200','18','576','3776','2026-02-21 18:29:48','2026-02-21 18:29:48'),(33,16,2,'pcs','9013','850','11','9350','18','1683','11033','2026-05-15 18:29:48','2026-05-15 18:29:48'),(34,17,4,'pcs','9013','2200','11','24200','18','4356','28556','2026-05-27 18:29:48','2026-05-27 18:29:48'),(35,18,4,'pcs','9013','2200','4','8800','18','1584','10384','2026-02-14 18:29:48','2026-02-14 18:29:48'),(36,19,2,'pcs','9013','850','2','1700','18','306','2006','2026-07-20 18:29:48','2026-07-20 18:29:48'),(37,20,5,'pcs','9013','2600','10','26000','18','4680','30680','2026-05-27 18:29:48','2026-05-27 18:29:48'),(38,20,6,'pcs','9013','1500','9','13500','18','2430','15930','2026-05-27 18:29:48','2026-05-27 18:29:48'),(39,20,2,'pcs','9013','850','4','3400','18','612','4012','2026-05-27 18:29:48','2026-05-27 18:29:48'),(40,21,3,'pcs','9013','320','3','960','18','172.8','1132.8','2026-02-27 18:29:48','2026-02-27 18:29:48'),(41,21,4,'pcs','9013','2200','15','33000','18','5940','38940','2026-02-27 18:29:48','2026-02-27 18:29:48'),(42,21,6,'pcs','9013','1500','11','16500','18','2970','19470','2026-02-27 18:29:48','2026-02-27 18:29:48'),(43,22,6,'pcs','9013','1500','11','16500','18','2970','19470','2026-04-30 18:29:48','2026-04-30 18:29:48'),(44,22,6,'pcs','9013','1500','4','6000','18','1080','7080','2026-04-30 18:29:48','2026-04-30 18:29:48'),(45,23,1,'pcs','9013','450','2','900','18','162','1062','2026-07-25 18:29:48','2026-07-25 18:29:48'),(46,23,4,'pcs','9013','2200','13','28600','18','5148','33748','2026-07-25 18:29:48','2026-07-25 18:29:48'),(47,24,3,'pcs','9013','320','7','2240','18','403.2','2643.2','2026-07-02 18:29:48','2026-07-02 18:29:48'),(48,24,2,'pcs','9013','850','3','2550','18','459','3009','2026-07-02 18:29:48','2026-07-02 18:29:48'),(49,25,4,'pcs','9013','2200','11','24200','18','4356','28556','2026-06-24 18:29:48','2026-06-24 18:29:48'),(50,25,4,'pcs','9013','2200','13','28600','18','5148','33748','2026-06-24 18:29:48','2026-06-24 18:29:48'),(51,25,6,'pcs','9013','1500','3','4500','18','810','5310','2026-06-24 18:29:48','2026-06-24 18:29:48'),(52,26,2,'pcs','9013','850','2','1700','18','306','2006','2026-05-06 18:29:48','2026-05-06 18:29:48'),(53,26,2,'pcs','9013','850','2','1700','18','306','2006','2026-05-06 18:29:48','2026-05-06 18:29:48'),(54,27,2,'pcs','9013','850','7','5950','18','1071','7021','2026-07-14 18:29:48','2026-07-14 18:29:48'),(55,27,4,'pcs','9013','2200','3','6600','18','1188','7788','2026-07-14 18:29:48','2026-07-14 18:29:48'),(56,28,1,'pcs','9013','450','5','2250','18','405','2655','2026-02-12 18:29:48','2026-02-12 18:29:48'),(57,28,4,'pcs','9013','2200','12','26400','18','4752','31152','2026-02-12 18:29:48','2026-02-12 18:29:48'),(58,28,1,'pcs','9013','450','8','3600','18','648','4248','2026-02-12 18:29:48','2026-02-12 18:29:48');
/*!40000 ALTER TABLE `invoiceproduct` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `job_audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_audit_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `job_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `action` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `job_audit_logs_job_id_foreign` (`job_id`),
  KEY `job_audit_logs_user_id_foreign` (`user_id`),
  CONSTRAINT `job_audit_logs_job_id_foreign` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `job_audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
  `description` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_completed` tinyint(1) NOT NULL DEFAULT '0',
  `completed_by` bigint unsigned DEFAULT NULL,
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
  `uploaded_by` bigint unsigned DEFAULT NULL,
  `path` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content_hash` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stage` enum('before','after','general') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'general',
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `location_captured` tinyint(1) NOT NULL DEFAULT '0',
  `location_flagged` tinyint(1) NOT NULL DEFAULT '0',
  `distance_from_machine_meters` decimal(10,2) DEFAULT NULL,
  `map_link` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
  `title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `machine_id` bigint unsigned DEFAULT NULL,
  `site_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint unsigned NOT NULL,
  `assigned_to` bigint unsigned DEFAULT NULL,
  `priority` enum('low','medium','high','urgent') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medium',
  `due_date` date DEFAULT NULL,
  `overdue_flagged_at` timestamp NULL DEFAULT NULL,
  `status` enum('pending_approval','assigned','in_progress','on_hold','completed','rejected') COLLATE utf8mb4_unicode_ci NOT NULL,
  `decided_by` bigint unsigned DEFAULT NULL,
  `decided_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text COLLATE utf8mb4_unicode_ci,
  `on_hold_reason` text COLLATE utf8mb4_unicode_ci,
  `completion_notes` text COLLATE utf8mb4_unicode_ci,
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
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
INSERT INTO `jobs` VALUES (1,'Head alignment',NULL,4,'Rajkot Plant',2,6,'urgent','2026-07-29',NULL,'completed',NULL,NULL,NULL,NULL,'Work completed and verified.','2026-07-31 12:59:48','2026-07-28 12:59:48','2026-08-09 12:59:48'),(2,'Firmware update',NULL,4,'Rajkot Plant',2,6,'medium','2026-07-22',NULL,'completed',NULL,NULL,NULL,NULL,'Work completed and verified.','2026-07-24 12:59:48','2026-07-21 12:59:48','2026-08-09 12:59:48'),(3,'Head alignment',NULL,5,'Rajkot Plant',2,6,'low','2026-07-28',NULL,'completed',NULL,NULL,NULL,NULL,'Work completed and verified.','2026-07-30 12:59:48','2026-07-27 12:59:48','2026-08-09 12:59:48'),(4,'Firmware update',NULL,4,'Rajkot Plant',2,5,'urgent','2026-08-01',NULL,'completed',NULL,NULL,NULL,NULL,'Work completed and verified.','2026-08-03 12:59:48','2026-07-31 12:59:48','2026-08-09 12:59:48'),(5,'Head alignment',NULL,4,'Rajkot Plant',2,6,'urgent','2026-07-22',NULL,'completed',NULL,NULL,NULL,NULL,'Work completed and verified.','2026-07-24 12:59:48','2026-07-21 12:59:48','2026-08-09 12:59:48'),(6,'Lens cleaning',NULL,3,'Rajkot Plant',2,5,'low','2026-07-26',NULL,'completed',NULL,NULL,NULL,NULL,'Work completed and verified.','2026-07-28 12:59:48','2026-07-25 12:59:48','2026-08-09 12:59:48'),(7,'Nozzle replacement',NULL,2,'Rajkot Plant',2,5,'urgent','2026-07-29',NULL,'completed',NULL,NULL,NULL,NULL,'Work completed and verified.','2026-07-31 12:59:48','2026-07-28 12:59:48','2026-08-09 12:59:48'),(8,'Calibration check',NULL,4,'Rajkot Plant',2,6,'urgent','2026-08-01',NULL,'completed',NULL,NULL,NULL,NULL,'Work completed and verified.','2026-08-03 12:59:48','2026-07-31 12:59:48','2026-08-09 12:59:48'),(9,'Bearing service',NULL,1,'Rajkot Plant',2,5,'medium','2026-08-04',NULL,'completed',NULL,NULL,NULL,NULL,'Work completed and verified.','2026-08-06 12:59:48','2026-08-03 12:59:48','2026-08-09 12:59:48'),(10,'Nozzle replacement',NULL,4,'Rajkot Plant',2,5,'high','2026-07-23',NULL,'completed',NULL,NULL,NULL,NULL,'Work completed and verified.','2026-07-25 12:59:48','2026-07-22 12:59:48','2026-08-09 12:59:48'),(11,'Bearing service',NULL,2,'Rajkot Plant',2,5,'urgent','2026-07-21',NULL,'completed',NULL,NULL,NULL,NULL,'Work completed and verified.','2026-07-23 12:59:48','2026-07-20 12:59:48','2026-08-09 12:59:48'),(12,'Chiller maintenance',NULL,2,'Rajkot Plant',2,5,'medium','2026-07-22',NULL,'completed',NULL,NULL,NULL,NULL,'Work completed and verified.','2026-07-24 12:59:48','2026-07-21 12:59:48','2026-08-09 12:59:48'),(13,'Lens cleaning',NULL,1,'Rajkot Plant',2,6,'urgent','2026-08-09',NULL,'completed',NULL,NULL,NULL,NULL,'Done today.','2026-08-09 11:59:48','2026-08-08 12:59:48','2026-08-09 12:59:48'),(14,'Firmware update',NULL,4,'Rajkot Plant',2,5,'urgent','2026-08-09',NULL,'completed',NULL,NULL,NULL,NULL,'Done today.','2026-08-09 10:59:48','2026-08-08 12:59:48','2026-08-09 12:59:48'),(15,'Head alignment',NULL,4,'Rajkot Plant',2,5,'high','2026-08-09',NULL,'completed',NULL,NULL,NULL,NULL,'Done today.','2026-08-09 09:59:48','2026-08-08 12:59:48','2026-08-09 12:59:48'),(16,'Calibration check',NULL,2,'Rajkot Plant',2,5,'low','2026-08-14',NULL,'assigned',NULL,NULL,NULL,NULL,NULL,NULL,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(17,'Calibration check',NULL,2,'Rajkot Plant',2,5,'low','2026-08-14',NULL,'assigned',NULL,NULL,NULL,NULL,NULL,NULL,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(18,'Nozzle replacement',NULL,4,'Rajkot Plant',2,6,'high','2026-08-16',NULL,'in_progress',NULL,NULL,NULL,NULL,NULL,NULL,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(19,'Lens cleaning',NULL,2,'Rajkot Plant',2,5,'low','2026-08-15',NULL,'in_progress',NULL,NULL,NULL,NULL,NULL,NULL,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(20,'Firmware update',NULL,2,'Rajkot Plant',2,5,'urgent','2026-08-14',NULL,'on_hold',NULL,NULL,NULL,'Awaiting spare part',NULL,NULL,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(21,'Lens cleaning',NULL,5,'Rajkot Plant',2,5,'low','2026-08-14',NULL,'pending_approval',NULL,NULL,NULL,NULL,NULL,NULL,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(22,'Nozzle replacement',NULL,5,'Rajkot Plant',2,5,'urgent','2026-08-14',NULL,'pending_approval',NULL,NULL,NULL,NULL,NULL,NULL,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(23,'Lens cleaning',NULL,4,'Rajkot Plant',2,6,'high','2026-08-05','2026-08-08 12:59:48','in_progress',NULL,NULL,NULL,NULL,NULL,NULL,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(24,'Bearing service',NULL,2,'Rajkot Plant',2,6,'urgent','2026-08-03','2026-08-08 12:59:48','in_progress',NULL,NULL,NULL,NULL,NULL,NULL,'2026-08-09 12:59:48','2026-08-09 12:59:48');
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `machines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `machines` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `machines` WRITE;
/*!40000 ALTER TABLE `machines` DISABLE KEYS */;
INSERT INTO `machines` VALUES (1,'Fiber Cutter FC-3015',1,22.3000000,70.8000000,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(2,'Tube Laser TL-6022',1,22.3100000,70.8100000,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(3,'CO2 Engraver CE-1390',1,22.3200000,70.8200000,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(4,'Bending Press BP-110',1,22.3300000,70.8300000,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(5,'Welding Station WS-04',0,22.3400000,70.8400000,'2026-08-09 12:59:48','2026-08-09 12:59:48');
/*!40000 ALTER TABLE `machines` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=68 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2014_10_12_000000_create_users_table',1),(2,'2014_10_12_100000_create_password_resets_table',1),(3,'2019_08_19_000000_create_failed_jobs_table',1),(4,'2019_12_14_000001_create_personal_access_tokens_table',1),(5,'2026_07_10_000001_create_product_table',1),(6,'2026_07_10_000002_create_invoice_table',1),(7,'2026_07_10_000003_create_customer_table',1),(8,'2026_07_10_000004_create_invoiceproduct_table',1),(9,'2026_07_10_000005_create_paidamount_table',1),(10,'2026_07_10_000006_create_bank_table',1),(11,'2026_07_10_000007_create_softwaredetails_table',1),(12,'2026_07_10_000008_create_softerwere1_table',1),(13,'2026_07_10_000009_create_addlaser_table',1),(14,'2026_07_10_000010_create_fours_table',1),(15,'2026_07_10_000011_create_power_table',1),(16,'2026_07_10_000012_create_motor_table',1),(17,'2026_07_10_000013_create_gear_table',1),(18,'2026_07_10_000014_create_rack_table',1),(19,'2026_07_10_000015_create_cuttingway_table',1),(20,'2026_07_10_000016_create_cnsthinks_table',1),(21,'2026_07_10_000017_create_invetry_table',1),(22,'2026_07_10_000018_create_quationform_table',1),(23,'2026_07_15_024652_add_performance_indexes',1),(24,'2026_07_15_153715_fix_missing_auto_increment_on_rack_and_softerwere1',1),(25,'2026_07_15_165411_create_cache_table',1),(26,'2026_07_15_165411_create_sessions_table',1),(27,'2026_07_15_165539_add_invetry_product_id_index',1),(28,'2026_07_18_000001_create_permission_tables',1),(29,'2026_07_18_000002_add_is_active_to_users_table',1),(30,'2026_07_18_000006_create_machines_table',1),(31,'2026_07_18_000006_create_notifications_table',1),(32,'2026_07_18_000007_create_jobs_table',1),(33,'2026_07_18_000008_create_job_photos_table',1),(34,'2026_07_18_000009_create_job_audit_logs_table',1),(35,'2026_07_18_000010_create_push_subscriptions_table',1),(36,'2026_07_19_000001_create_employees_table',1),(37,'2026_07_19_000002_create_attendances_table',1),(38,'2026_07_19_000003_create_salary_payments_table',1),(39,'2026_07_19_000004_create_employee_documents_table',1),(40,'2026_07_21_000001_create_audit_logs_table',1),(41,'2026_07_21_000002_normalize_push_subscription_morph_type',1),(42,'2026_07_22_000001_change_quationform_phone_to_string',1),(43,'2026_07_25_000001_add_payment_method_and_reference_to_paidamount',1),(44,'2026_07_25_213252_create_quotation_items_table',1),(45,'2026_07_26_111400_create_client_accounts_table',1),(46,'2026_07_26_111401_create_client_machines_table',1),(47,'2026_07_26_111402_create_ticket_problem_types_table',1),(48,'2026_07_26_111403_create_tickets_table',1),(49,'2026_07_26_111404_create_ticket_photos_table',1),(50,'2026_07_26_122658_add_is_spare_part_to_product_table',1),(51,'2026_07_26_122658_create_spare_part_requests_table',1),(52,'2026_08_01_181242_create_vendors_table',1),(53,'2026_08_05_000000_add_location_to_machines_table',1),(54,'2026_08_05_000001_add_overdue_flagged_at_to_jobs_table',1),(55,'2026_08_06_000000_add_low_stock_notified_at_to_invetry_table',1),(56,'2026_08_06_000001_add_low_stock_threshold_to_invetry_table',1),(57,'2026_08_06_000002_add_product_link_to_quotation_items_table',1),(58,'2026_08_07_000000_create_job_checklist_items_table',1),(59,'2026_08_07_000001_add_integrity_fields_to_job_photos_table',1),(60,'2026_08_07_000002_add_stage_to_job_photos_table',1),(61,'2026_08_09_000000_create_expense_categories_table',1),(62,'2026_08_09_000001_create_vendor_bills_table',1),(63,'2026_08_09_000002_create_vendor_payments_table',1),(64,'2026_08_09_000003_create_daily_transactions_table',1),(65,'2026_08_09_000004_merge_ticket_photos_into_job_photos',1),(66,'2026_08_09_100000_create_chart_accounts_table',1),(67,'2026_08_09_120000_add_missing_auth_columns_to_users_table',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `model_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
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
  `model_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `model_has_roles` WRITE;
/*!40000 ALTER TABLE `model_has_roles` DISABLE KEYS */;
INSERT INTO `model_has_roles` VALUES (1,'user',2),(3,'user',3),(4,'user',4),(2,'user',5),(2,'user',6);
/*!40000 ALTER TABLE `model_has_roles` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `motor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `motor` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int DEFAULT NULL,
  `companyname` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `motor_product_id_index` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `motor` WRITE;
/*!40000 ALTER TABLE `motor` DISABLE KEYS */;
/*!40000 ALTER TABLE `motor` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_id` bigint unsigned NOT NULL,
  `data` text COLLATE utf8mb4_unicode_ci NOT NULL,
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
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `invoice_id` int DEFAULT NULL,
  `customer_id` int DEFAULT NULL,
  `paidAmount` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_method` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference_number` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `paidamount_invoice_id_index` (`invoice_id`),
  KEY `paidamount_customer_id_index` (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `paidamount` WRITE;
/*!40000 ALTER TABLE `paidamount` DISABLE KEYS */;
/*!40000 ALTER TABLE `paidamount` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_resets` (
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  KEY `password_resets_email_index` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `password_resets` WRITE;
/*!40000 ALTER TABLE `password_resets` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_resets` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,'dashboard.view','web','2026-08-09 12:59:48','2026-08-09 12:59:48'),(2,'products.view','web','2026-08-09 12:59:48','2026-08-09 12:59:48'),(3,'products.create','web','2026-08-09 12:59:48','2026-08-09 12:59:48'),(4,'products.update','web','2026-08-09 12:59:48','2026-08-09 12:59:48'),(5,'products.delete','web','2026-08-09 12:59:48','2026-08-09 12:59:48'),(6,'products.manage-config','web','2026-08-09 12:59:48','2026-08-09 12:59:48'),(7,'inventory.view','web','2026-08-09 12:59:48','2026-08-09 12:59:48'),(8,'inventory.create','web','2026-08-09 12:59:48','2026-08-09 12:59:48'),(9,'inventory.update','web','2026-08-09 12:59:48','2026-08-09 12:59:48'),(10,'inventory.view-audit','web','2026-08-09 12:59:48','2026-08-09 12:59:48'),(11,'invoices.view','web','2026-08-09 12:59:48','2026-08-09 12:59:48'),(12,'invoices.create','web','2026-08-09 12:59:48','2026-08-09 12:59:48'),(13,'invoices.update','web','2026-08-09 12:59:48','2026-08-09 12:59:48'),(14,'invoices.delete','web','2026-08-09 12:59:48','2026-08-09 12:59:48'),(15,'invoices.view-details','web','2026-08-09 12:59:48','2026-08-09 12:59:48'),(16,'invoices.record-payment','web','2026-08-09 12:59:48','2026-08-09 12:59:48'),(17,'vendors.view','web','2026-08-09 12:59:48','2026-08-09 12:59:48'),(18,'vendors.manage','web','2026-08-09 12:59:48','2026-08-09 12:59:48'),(19,'payment-history.view','web','2026-08-09 12:59:48','2026-08-09 12:59:48'),(20,'quotations.view','web','2026-08-09 12:59:48','2026-08-09 12:59:48'),(21,'quotations.create','web','2026-08-09 12:59:48','2026-08-09 12:59:48'),(22,'quotations.update','web','2026-08-09 12:59:48','2026-08-09 12:59:48'),(23,'quotations.download-pdf','web','2026-08-09 12:59:48','2026-08-09 12:59:48'),(24,'quotations.delete','web','2026-08-09 12:59:48','2026-08-09 12:59:48'),(25,'admin.manage-roles','web','2026-08-09 12:59:48','2026-08-09 12:59:48'),(26,'admin.manage-users','web','2026-08-09 12:59:48','2026-08-09 12:59:48'),(27,'jobs.view-own','web','2026-08-09 12:59:48','2026-08-09 12:59:48'),(28,'jobs.create','web','2026-08-09 12:59:48','2026-08-09 12:59:48'),(29,'jobs.view-all','web','2026-08-09 12:59:48','2026-08-09 12:59:48'),(30,'jobs.approve','web','2026-08-09 12:59:48','2026-08-09 12:59:48'),(31,'jobs.assign','web','2026-08-09 12:59:48','2026-08-09 12:59:48'),(32,'jobs.manage-machines','web','2026-08-09 12:59:48','2026-08-09 12:59:48'),(33,'machines.view-audit','web','2026-08-09 12:59:48','2026-08-09 12:59:48'),(34,'jobs.view-audit','web','2026-08-09 12:59:48','2026-08-09 12:59:48'),(35,'employees.view','web','2026-08-09 12:59:48','2026-08-09 12:59:48'),(36,'employees.manage','web','2026-08-09 12:59:48','2026-08-09 12:59:48'),(37,'attendance.view','web','2026-08-09 12:59:48','2026-08-09 12:59:48'),(38,'attendance.manage','web','2026-08-09 12:59:48','2026-08-09 12:59:48'),(39,'attendance.view-audit','web','2026-08-09 12:59:48','2026-08-09 12:59:48'),(40,'payroll.view','web','2026-08-09 12:59:48','2026-08-09 12:59:48'),(41,'payroll.manage-payments','web','2026-08-09 12:59:48','2026-08-09 12:59:48'),(42,'employees.view-audit','web','2026-08-09 12:59:48','2026-08-09 12:59:48'),(43,'payroll.view-audit','web','2026-08-09 12:59:48','2026-08-09 12:59:48'),(44,'invoices.view-audit','web','2026-08-09 12:59:48','2026-08-09 12:59:48'),(45,'products.view-audit','web','2026-08-09 12:59:48','2026-08-09 12:59:48'),(46,'quotations.view-audit','web','2026-08-09 12:59:48','2026-08-09 12:59:48'),(47,'admin.view-audit','web','2026-08-09 12:59:48','2026-08-09 12:59:48'),(48,'client-machines.view','web','2026-08-09 12:59:48','2026-08-09 12:59:48'),(49,'client-machines.manage','web','2026-08-09 12:59:48','2026-08-09 12:59:48'),(50,'client-machines.view-audit','web','2026-08-09 12:59:48','2026-08-09 12:59:48'),(51,'ticket-problem-types.manage','web','2026-08-09 12:59:48','2026-08-09 12:59:48'),(52,'ticket-problem-types.view-audit','web','2026-08-09 12:59:48','2026-08-09 12:59:48'),(53,'tickets.view','web','2026-08-09 12:59:48','2026-08-09 12:59:48'),(54,'tickets.assign','web','2026-08-09 12:59:48','2026-08-09 12:59:48'),(55,'tickets.view-audit','web','2026-08-09 12:59:48','2026-08-09 12:59:48'),(56,'spare-parts.manage','web','2026-08-09 12:59:48','2026-08-09 12:59:48'),(57,'spare-part-requests.view','web','2026-08-09 12:59:48','2026-08-09 12:59:48'),(58,'spare-part-requests.manage','web','2026-08-09 12:59:48','2026-08-09 12:59:48'),(59,'spare-part-requests.view-audit','web','2026-08-09 12:59:48','2026-08-09 12:59:48'),(60,'reports.view','web','2026-08-09 12:59:48','2026-08-09 12:59:48'),(61,'accounting.view','web','2026-08-09 12:59:48','2026-08-09 12:59:48'),(62,'expenses.view','web','2026-08-09 12:59:48','2026-08-09 12:59:48'),(63,'expenses.manage','web','2026-08-09 12:59:48','2026-08-09 12:59:48'),(64,'expenses.delete','web','2026-08-09 12:59:48','2026-08-09 12:59:48'),(65,'expenses.view-audit','web','2026-08-09 12:59:48','2026-08-09 12:59:48'),(66,'vendor-payments.view','web','2026-08-09 12:59:48','2026-08-09 12:59:48'),(67,'vendor-payments.manage','web','2026-08-09 12:59:48','2026-08-09 12:59:48'),(68,'vendors.view-audit','web','2026-08-09 12:59:48','2026-08-09 12:59:48');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `power`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `power` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int DEFAULT NULL,
  `company` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `modal` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `power_product_id_index` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `power` WRITE;
/*!40000 ALTER TABLE `power` DISABLE KEYS */;
/*!40000 ALTER TABLE `power` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `product`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rate` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `unit` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `make` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_spare_part` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `product` WRITE;
/*!40000 ALTER TABLE `product` DISABLE KEYS */;
INSERT INTO `product` VALUES (1,'Fiber Laser Nozzle 2.0mm','450','pcs','Raytools',0,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(2,'Protective Lens 30x5','850','pcs','WSX',0,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(3,'Ceramic Ring','320','pcs','Precitec',0,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(4,'Focusing Lens D28 F125','2200','pcs','Raytools',0,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(5,'Collimating Lens D30','2600','pcs','WSX',0,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(6,'Nozzle Connector','1500','pcs','Precitec',0,'2026-08-09 12:59:48','2026-08-09 12:59:48');
/*!40000 ALTER TABLE `product` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `push_subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `push_subscriptions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `subscribable_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subscribable_id` bigint unsigned NOT NULL,
  `endpoint` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `public_key` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `auth_token` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content_encoding` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int DEFAULT NULL,
  `clientname` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `companyname` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gstno` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `companyaddress` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank` int DEFAULT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date` date DEFAULT NULL,
  `reminderdate` date DEFAULT NULL,
  `softweredetails` int DEFAULT NULL,
  `lasercutting` int DEFAULT NULL,
  `focus` int DEFAULT NULL,
  `power` int DEFAULT NULL,
  `inputpower` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cuttingway` int DEFAULT NULL,
  `cncspan` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cnslenght` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cuttingrang` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `liftingheight` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `headquantity` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cuttingthickess` int DEFAULT NULL,
  `strokespeed` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cuttingspeed` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `drive` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `motor` int DEFAULT NULL,
  `motortype` int DEFAULT NULL,
  `gearbox` int DEFAULT NULL,
  `rack` int DEFAULT NULL,
  `software` int DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `description1` text COLLATE utf8mb4_unicode_ci,
  `description2` text COLLATE utf8mb4_unicode_ci,
  `amount` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount1` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount2` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `optionparthyscope` text COLLATE utf8mb4_unicode_ci,
  `note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
  `quotation_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned DEFAULT NULL,
  `quantity` int unsigned DEFAULT NULL,
  `description` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `quotation_items_quotation_id_foreign` (`quotation_id`),
  KEY `quotation_items_product_id_foreign` (`product_id`),
  CONSTRAINT `quotation_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE SET NULL,
  CONSTRAINT `quotation_items_quotation_id_foreign` FOREIGN KEY (`quotation_id`) REFERENCES `quationform` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
  `companyname` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `rack_product_id_index` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
INSERT INTO `role_has_permissions` VALUES (1,1),(2,1),(3,1),(4,1),(5,1),(6,1),(7,1),(8,1),(9,1),(10,1),(11,1),(12,1),(13,1),(14,1),(15,1),(16,1),(17,1),(18,1),(19,1),(20,1),(21,1),(22,1),(23,1),(24,1),(25,1),(26,1),(27,1),(28,1),(29,1),(30,1),(31,1),(32,1),(33,1),(34,1),(35,1),(36,1),(37,1),(38,1),(39,1),(40,1),(41,1),(42,1),(43,1),(44,1),(45,1),(46,1),(47,1),(48,1),(49,1),(50,1),(51,1),(52,1),(53,1),(54,1),(55,1),(56,1),(57,1),(58,1),(59,1),(60,1),(61,1),(62,1),(63,1),(64,1),(65,1),(66,1),(67,1),(68,1),(1,2),(27,2),(28,2),(1,3),(7,3),(27,3),(29,3),(30,3),(31,3),(35,3),(37,3),(53,3),(57,3),(60,3),(1,4),(11,4),(15,4),(16,4),(17,4),(19,4),(60,4),(61,4),(62,4),(63,4),(66,4),(67,4);
/*!40000 ALTER TABLE `role_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'Owner','web','2026-08-09 12:59:48','2026-08-09 12:59:48'),(2,'Worker','web','2026-08-09 12:59:48','2026-08-09 12:59:48'),(3,'Manager','web','2026-08-09 12:59:48','2026-08-09 12:59:48'),(4,'Account','web','2026-08-09 12:59:48','2026-08-09 12:59:48');
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
  `note` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paid_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `salary_payments_paid_by_foreign` (`paid_by`),
  KEY `salary_payments_employee_id_date_index` (`employee_id`,`date`),
  CONSTRAINT `salary_payments_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `salary_payments_paid_by_foreign` FOREIGN KEY (`paid_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `salary_payments` WRITE;
/*!40000 ALTER TABLE `salary_payments` DISABLE KEYS */;
INSERT INTO `salary_payments` VALUES (1,1,'2026-05-05',22000.00,'Monthly salary',2,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(2,1,'2026-06-05',22000.00,'Monthly salary',2,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(3,1,'2026-07-05',22000.00,'Monthly salary',2,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(4,1,'2026-08-05',22000.00,'Monthly salary',2,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(5,2,'2026-05-05',26000.00,'Monthly salary',2,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(6,2,'2026-06-05',26000.00,'Monthly salary',2,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(7,2,'2026-07-05',26000.00,'Monthly salary',2,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(8,2,'2026-08-05',26000.00,'Monthly salary',2,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(9,3,'2026-05-05',18200.00,'Monthly salary',2,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(10,3,'2026-06-05',18200.00,'Monthly salary',2,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(11,3,'2026-07-05',18200.00,'Monthly salary',2,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(12,3,'2026-08-05',18200.00,'Monthly salary',2,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(13,4,'2026-05-05',16900.00,'Monthly salary',2,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(14,4,'2026-06-05',16900.00,'Monthly salary',2,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(15,4,'2026-07-05',16900.00,'Monthly salary',2,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(16,4,'2026-08-05',16900.00,'Monthly salary',2,'2026-08-09 12:59:48','2026-08-09 12:59:48');
/*!40000 ALTER TABLE `salary_payments` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
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
  `companyname` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `softerwere1_product_id_index` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `softerwere1` WRITE;
/*!40000 ALTER TABLE `softerwere1` DISABLE KEYS */;
/*!40000 ALTER TABLE `softerwere1` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `softwaredetails`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `softwaredetails` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int DEFAULT NULL,
  `company` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `modal` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo` text COLLATE utf8mb4_unicode_ci,
  `image` text COLLATE utf8mb4_unicode_ci,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `softwaredetails_product_id_index` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
  `product_id` bigint unsigned NOT NULL,
  `quantity` int unsigned NOT NULL DEFAULT '1',
  `note` text COLLATE utf8mb4_unicode_ci,
  `status` enum('pending','approved','fulfilled','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `spare_part_requests_client_machine_id_foreign` (`client_machine_id`),
  KEY `spare_part_requests_client_account_id_foreign` (`client_account_id`),
  KEY `spare_part_requests_product_id_foreign` (`product_id`),
  CONSTRAINT `spare_part_requests_client_account_id_foreign` FOREIGN KEY (`client_account_id`) REFERENCES `client_accounts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `spare_part_requests_client_machine_id_foreign` FOREIGN KEY (`client_machine_id`) REFERENCES `client_machines` (`id`) ON DELETE CASCADE,
  CONSTRAINT `spare_part_requests_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
  `category` enum('electrical','mechanical') COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `ticket_problem_types` WRITE;
/*!40000 ALTER TABLE `ticket_problem_types` DISABLE KEYS */;
INSERT INTO `ticket_problem_types` VALUES (1,'electrical','Power Supply Failure',1,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(2,'electrical','Wiring Fault',1,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(3,'electrical','Motor Not Running',1,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(4,'electrical','Control Panel Error',1,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(5,'electrical','PLC Fault',1,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(6,'electrical','Sensor Fault',1,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(7,'electrical','Short Circuit',1,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(8,'electrical','Cable Damage',1,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(9,'mechanical','Cutting Head Misalignment',1,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(10,'mechanical','Nozzle Damage',1,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(11,'mechanical','Belt / Gear Wear',1,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(12,'mechanical','Rail / Guide Wear',1,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(13,'mechanical','Lubrication Issue',1,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(14,'mechanical','Bearing Failure',1,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(15,'mechanical','Unusual Vibration / Noise',1,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(16,'mechanical','Frame / Structural Issue',1,'2026-08-09 12:59:48','2026-08-09 12:59:48');
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
  `description` text COLLATE utf8mb4_unicode_ci,
  `status` enum('open','assigned','in_progress','resolved') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `tickets` WRITE;
/*!40000 ALTER TABLE `tickets` DISABLE KEYS */;
INSERT INTO `tickets` VALUES (1,1,1,1,'Machine needs a service check.','open',NULL,'2026-08-09 12:59:49','2026-08-09 12:59:49'),(2,3,2,1,'Machine needs a service check.','open',NULL,'2026-08-09 12:59:49','2026-08-09 12:59:49');
/*!40000 ALTER TABLE `tickets` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `avatar` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','admin@themesbrand.com',NULL,'$2y$10$lK5HGvEcMDEQ5Ppe0f86Gep.n6qsOWexlKGCqIk5dAQb0LHKyM0h.',NULL,1,NULL,'2026-08-09 12:59:35','2026-08-09 12:59:35'),(2,'Owner Demo','owner-demo@test.local','2026-08-09 12:59:48','$2y$10$N.HzNr4QjH/sE8heb0OsrOGNXLNOdUwSyf8ZWVtM2BZpXRiXanwpy',NULL,1,NULL,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(3,'Manager Demo','manager-demo@test.local','2026-08-09 12:59:48','$2y$10$mb8BtIcLKEeNSiGlQtHZwel4p//FCnZHmwSwLEACkjsRKZwo25D0y',NULL,1,NULL,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(4,'Account Demo','account-demo@test.local','2026-08-09 12:59:48','$2y$10$Q7UVyLyjPYGTfL1h5TIodeM8Mh1wQual5YNDsa1Rxxr3tWMdTSdJe',NULL,1,NULL,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(5,'Ravi Kumar','worker-demo@test.local','2026-08-09 12:59:48','$2y$10$mhB/k0x9Odc5tWAQ9Ji3ouNDQThtbqLxXzGcRbYSy5faalCl9JAsW',NULL,1,NULL,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(6,'Anil Sharma','worker2-demo@test.local','2026-08-09 12:59:48','$2y$10$27vkkMRHQrhRJnU22bpFQOz1V2MBNx0MF.W1CkK1m5oy84774cgde',NULL,1,NULL,'2026-08-09 12:59:48','2026-08-09 12:59:48');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `vendor_bills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vendor_bills` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `vendor_id` bigint unsigned NOT NULL,
  `bill_number` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `date` date NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `vendor_bills_vendor_id_foreign` (`vendor_id`),
  KEY `vendor_bills_created_by_foreign` (`created_by`),
  CONSTRAINT `vendor_bills_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `vendor_bills_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `vendor_bills` WRITE;
/*!40000 ALTER TABLE `vendor_bills` DISABLE KEYS */;
INSERT INTO `vendor_bills` VALUES (1,1,'BILL-975',41000.00,'2026-08-09','Supplies',2,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(2,1,'BILL-245',33000.00,'2026-07-09','Supplies',2,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(3,2,'BILL-949',58000.00,'2026-05-09','Supplies',2,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(4,2,'BILL-741',60000.00,'2026-08-09','Supplies',2,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(5,3,'BILL-658',22000.00,'2026-07-09','Supplies',2,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(6,3,'BILL-659',19000.00,'2026-04-09','Supplies',2,'2026-08-09 12:59:48','2026-08-09 12:59:48');
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
  `payment_mode` enum('cash','bank','upi','cheque') COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `vendor_payments_vendor_id_foreign` (`vendor_id`),
  KEY `vendor_payments_vendor_bill_id_foreign` (`vendor_bill_id`),
  KEY `vendor_payments_created_by_foreign` (`created_by`),
  CONSTRAINT `vendor_payments_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `vendor_payments_vendor_bill_id_foreign` FOREIGN KEY (`vendor_bill_id`) REFERENCES `vendor_bills` (`id`) ON DELETE SET NULL,
  CONSTRAINT `vendor_payments_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `vendor_payments` WRITE;
/*!40000 ALTER TABLE `vendor_payments` DISABLE KEYS */;
INSERT INTO `vendor_payments` VALUES (1,1,1,24600.00,'2026-08-14','bank','Part payment',2,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(2,1,2,19800.00,'2026-07-14','bank','Part payment',2,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(3,2,3,34800.00,'2026-05-14','bank','Part payment',2,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(4,2,4,36000.00,'2026-08-14','bank','Part payment',2,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(5,3,5,13200.00,'2026-07-14','bank','Part payment',2,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(6,3,6,11400.00,'2026-04-14','bank','Part payment',2,'2026-08-09 12:59:48','2026-08-09 12:59:48');
/*!40000 ALTER TABLE `vendor_payments` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `vendors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vendors` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gstin` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'India',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `vendors` WRITE;
/*!40000 ALTER TABLE `vendors` DISABLE KEYS */;
INSERT INTO `vendors` VALUES (1,'Raytools India Pvt Ltd','Optics',NULL,NULL,NULL,NULL,NULL,'India',NULL,1,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(2,'GIDC Power Supplies','Electrical',NULL,NULL,NULL,NULL,NULL,'India',NULL,1,'2026-08-09 12:59:48','2026-08-09 12:59:48'),(3,'Metro Gases','Consumables',NULL,NULL,NULL,NULL,NULL,'India',NULL,1,'2026-08-09 12:59:48','2026-08-09 12:59:48');
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

