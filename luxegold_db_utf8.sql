-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: luxegold_db
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
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `identifiant` varchar(50) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `identifiant` (`identifiant`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admins`
--

LOCK TABLES `admins` WRITE;
/*!40000 ALTER TABLE `admins` DISABLE KEYS */;
INSERT INTO `admins` VALUES (1,'admin','$2y$10$n4VxtxQleFV1xZ2SvVOrPuiwlfo2ldFm/F0n1RSlldEa5BsdOvyma'),(5,'dieylany','$2y$10$Q1K2ck9kB6gu/jULMfDcLurHh.X.SuDLOO1CGUkbhGtpJgn9BpWwm'),(6,'thierno','$2y$10$AFyXZ2VuppQ9tW5O4PBwte5LYO6e0o9PTnFKKZGOWSici8AK9O/JO');
/*!40000 ALTER TABLE `admins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `avis`
--

DROP TABLE IF EXISTS `avis`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `avis` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `produit_id` int(11) NOT NULL,
  `client_nom` varchar(100) NOT NULL,
  `note` int(1) NOT NULL,
  `commentaire` text NOT NULL,
  `statut` enum('en_attente','valide','rejete') DEFAULT 'en_attente',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `avis`
--

LOCK TABLES `avis` WRITE;
/*!40000 ALTER TABLE `avis` DISABLE KEYS */;
INSERT INTO `avis` VALUES (1,3,'dieylany',4,'tres cool','en_attente','2026-02-20 18:03:12'),(2,3,'dielylany',5,'tres joli\r\n\r\n','en_attente','2026-02-20 19:54:06'),(5,48,'Alimatou N',4,'Magnifique ! Le bijou est encore plus beau en vrai que sur les photos. La livraison a ├⌐t├⌐ super rapide et la bo├«te est tr├¿s ├⌐l├⌐gante. Je recommande ├á 100% !','valide','2026-03-02 04:31:21'),(6,46,'Mousa S',5,'J\'ai achet├⌐ cette parure pour l\'anniversaire de ma femme et elle est aux anges ! Excellent rapport qualit├⌐/prix et le service client a ├⌐t├⌐ tr├¿s r├⌐actif sur WhatsApp. Merci l\'├⌐quipe Beug Fallou !','valide','2026-03-02 04:34:02'),(7,46,'Fatima',4,'Tr├¿s belle qualit├⌐, l\'or brille parfaitement et les finitions sont soign├⌐es. J\'ai juste trouv├⌐ que la taille ├⌐tait un tout petit peu plus petite que ce que j\'imaginais, mais ├ºa reste un tr├¿s bel achat.','valide','2026-03-02 04:35:13');
/*!40000 ALTER TABLE `avis` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `video` varchar(255) DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'Bagues',NULL,'1772219010_cat_69a1ea8256822.mp4',0),(2,'Colliers',NULL,'1772221888_cat_69a1f5c02d10b.mp4',0),(3,'Bracelets',NULL,'1772219722_cat_69a1ed4a6f1f0.mp4',0),(4,'Montres',NULL,'1772296595_cat_69a3199330743.mp4',0),(5,'Femme',NULL,NULL,0),(6,'Homme',NULL,NULL,0),(7,'Enfant',NULL,'1772252258_cat_69a26c62c998a.mp4',0),(8,'Couple','','1772222324_cat_69a1f774ba64f.mp4',0),(9,'Cadeaux',NULL,'1772221755_cat_69a1f53b248d6.mp4',0),(15,'sueprise',NULL,'1772272857_cat_69a2bcd95e0cc.mp4',1);
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `codes_promo`
--

DROP TABLE IF EXISTS `codes_promo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `codes_promo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `reduction` int(11) NOT NULL,
  `date_expiration` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `codes_promo`
--

LOCK TABLES `codes_promo` WRITE;
/*!40000 ALTER TABLE `codes_promo` DISABLE KEYS */;
INSERT INTO `codes_promo` VALUES (1,'TOUBA',70,'2026-12-12','2026-02-28 09:33:58');
/*!40000 ALTER TABLE `codes_promo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `commandes`
--

DROP TABLE IF EXISTS `commandes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `commandes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reference` varchar(20) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `devise` varchar(10) NOT NULL DEFAULT 'FCFA',
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp(),
  `statut` varchar(20) DEFAULT 'en_attente',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `id_utilisateur` int(11) DEFAULT NULL,
  `nom_client` varchar(255) DEFAULT NULL,
  `telephone_client` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=107 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `commandes`
--

LOCK TABLES `commandes` WRITE;
/*!40000 ALTER TABLE `commandes` DISABLE KEYS */;
INSERT INTO `commandes` VALUES (1,'CMD-D03E70',15000.00,'FCFA','2026-02-17 12:38:07','Livr├⌐e','2026-02-21 20:49:35',NULL,NULL,NULL),(2,'CMD-12838E',150000.00,'FCFA','2026-02-17 12:38:58','Livr├⌐e','2026-02-21 20:49:35',NULL,NULL,NULL),(3,'CMD-3CDB02',30000.00,'FCFA','2026-02-17 13:19:05','Livr├⌐e','2026-02-21 20:49:35',NULL,NULL,NULL),(4,'CMD-AF7794',15000.00,'FCFA','2026-02-17 13:37:42','Livr├⌐e','2026-02-21 20:49:35',NULL,NULL,NULL),(5,'CMD-DC5B35',120000.00,'FCFA','2026-02-17 13:47:29','Livr├⌐e','2026-02-21 20:49:35',NULL,NULL,NULL),(6,'CMD-A7CDCA',10000.00,'FCFA','2026-02-17 14:59:09','Livr├⌐e','2026-02-21 20:49:35',NULL,NULL,NULL),(7,'',250000.00,'FCFA','2026-02-21 14:38:15','Livr├⌐e','2026-02-21 22:38:15',NULL,NULL,NULL),(8,'',350000.00,'FCFA','2026-02-21 14:39:19','Livr├⌐e','2026-02-21 22:39:19',NULL,NULL,NULL),(9,'',250000.00,'FCFA','2026-02-21 14:43:35','Livr├⌐e','2026-02-21 22:43:35',NULL,NULL,NULL),(10,'',100000.00,'FCFA','2026-02-21 14:44:03','Livr├⌐e','2026-02-21 22:44:03',NULL,NULL,NULL),(11,'',2000.00,'FCFA','2026-02-21 14:54:26','Livr├⌐e','2026-02-21 22:54:26',NULL,NULL,NULL),(12,'',60000.00,'FCFA','2026-02-21 15:08:20','Livr├⌐e','2026-02-21 23:08:20',NULL,NULL,NULL),(13,'',320000.00,'FCFA','2026-02-21 17:04:19','Livr├⌐e','2026-02-22 01:04:19',NULL,NULL,NULL),(14,'',140000.00,'FCFA','2026-02-21 17:05:28','Livr├⌐e','2026-02-22 01:05:28',NULL,NULL,NULL),(15,'',50000.00,'FCFA','2026-02-21 17:09:53','Livr├⌐e','2026-02-22 01:09:53',NULL,NULL,NULL),(16,'',140000.00,'FCFA','2026-02-24 04:23:12','Livr├⌐e','2026-02-24 12:23:12',NULL,NULL,NULL),(17,'',7000000.00,'GMD','2026-02-24 06:02:04','Livr├⌐e','2026-02-24 14:02:04',NULL,NULL,NULL),(18,'',10000.00,'FCFA','2026-02-24 06:06:42','Livr├⌐e','2026-02-24 14:06:42',NULL,NULL,NULL),(19,'',7000.00,'FCFA','2026-02-24 13:51:48','Livr├⌐e','2026-02-24 21:51:48',4,NULL,NULL),(20,'',4000.00,'FCFA','2026-02-24 13:53:21','Livr├⌐e','2026-02-24 21:53:21',4,NULL,NULL),(21,'',63000.00,'FCFA','2026-02-24 13:54:21','Livr├⌐e','2026-02-24 21:54:21',4,NULL,NULL),(22,'',35000.00,'FCFA','2026-02-24 14:13:01','Livr├⌐e','2026-02-24 22:13:01',5,NULL,NULL),(23,'',35000.00,'FCFA','2026-02-24 14:14:23','Livr├⌐e','2026-02-24 22:14:23',5,NULL,NULL),(24,'',7000.00,'FCFA','2026-02-24 14:20:48','Livr├⌐e','2026-02-24 22:20:48',5,NULL,NULL),(25,'',10000.00,'FCFA','2026-02-24 16:26:17','Livr├⌐e','2026-02-25 00:26:17',4,NULL,NULL),(26,'',13350.00,'FCFA','2026-02-25 13:51:29','Livr├⌐e','2026-02-25 21:51:29',6,NULL,NULL),(27,'',520700.00,'FCFA','2026-02-25 14:11:24','Livr├⌐e','2026-02-25 22:11:24',7,NULL,NULL),(28,'',56000.00,'FCFA','2026-02-25 14:29:40','Livr├⌐e','2026-02-25 22:29:40',9,'oumy','771621361'),(29,'',143800.00,'FCFA','2026-02-25 14:34:47','Livr├⌐e','2026-02-25 22:34:47',10,'Binrta Ndiaye','781234567'),(30,'',396400.00,'FCFA','2026-02-25 16:54:11','Livr├⌐e','2026-02-26 00:54:11',11,'Nogaye Diop','781233322'),(31,'',2500000.00,'FCFA','2026-02-25 17:07:17','Livr├⌐e','2026-02-26 01:07:17',12,'fatyma','771621360'),(32,'',200000.00,'FCFA','2026-02-25 17:43:07','Livr├⌐e','2026-02-26 01:43:07',4,'dieylany','780152522'),(33,'',42000.00,'FCFA','2026-02-26 08:54:12','Livr├⌐e','2026-02-26 16:54:12',13,'dieylanya','780152572'),(34,'',2800.00,'FCFA','2026-02-26 09:07:12','Livr├⌐e','2026-02-26 17:07:12',4,'dieylany','780152522'),(35,'',2800.00,'FCFA','2026-02-27 12:53:55','Livr├⌐e','2026-02-27 20:53:55',14,'dieylany','780152511'),(36,'',2800.00,'FCFA','2026-02-27 12:59:06','Livr├⌐e','2026-02-27 20:59:06',12,'fatyma diop','771621367'),(37,'',100000.00,'FCFA','2026-02-27 13:25:21','Livr├⌐e','2026-02-27 21:25:21',4,'fatyma diop','780152522'),(38,'',14000.00,'FCFA','2026-02-27 13:33:40','Livr├⌐e','2026-02-27 21:33:40',4,'fatyma diop','780152522'),(39,'',600000.00,'FCFA','2026-02-27 13:41:17','Livr├⌐e','2026-02-27 21:41:17',4,'fatyma diop','780152522'),(40,'',14000.00,'FCFA','2026-02-27 13:48:42','Livr├⌐e','2026-02-27 21:48:42',15,'nogaye','7777777'),(41,'',100000.00,'FCFA','2026-02-27 13:54:49','Livr├⌐e','2026-02-27 21:54:49',15,'nogaye','7777777'),(42,'',100000.00,'FCFA','2026-02-27 14:19:52','Livr├⌐e','2026-02-27 22:19:52',12,'modou','781112233'),(43,'',20000.00,'FCFA','2026-02-27 14:22:36','Livr├⌐e','2026-02-27 22:22:36',12,'ELhadji Sakho','780987654'),(44,'',8400.00,'FCFA','2026-02-27 14:25:34','Livr├⌐e','2026-02-27 22:25:34',4,'dieylany','780152522'),(45,'',100000.00,'FCFA','2026-02-27 15:08:46','Livr├⌐e','2026-02-27 23:08:46',12,'ndeye','771231212'),(46,'',300000.00,'FCFA','2026-02-27 16:01:27','Livr├⌐e','2026-02-28 00:01:27',12,'aladji','771231212'),(47,'',2800.00,'FCFA','2026-02-27 16:27:18','Livr├⌐e','2026-02-28 00:27:18',4,'nogaye diop','780152522'),(48,'',200000.00,'FCFA','2026-02-27 16:43:46','Livr├⌐e','2026-02-28 00:43:46',12,'Khadime Ndiaye','780000099'),(49,'',610000.00,'FCFA','2026-02-27 20:22:48','en_attente','2026-02-28 04:22:48',19,'Mouhamed','78252536'),(50,'',330000.00,'FCFA','2026-02-28 05:22:36','Livr├⌐e','2026-02-28 13:22:36',20,'alioune badara gaye','778055911'),(51,'',110000.00,'FCFA','2026-02-28 10:39:38','en_attente','2026-02-28 18:39:38',21,'rassoul','789990000'),(52,'',20000.00,'FCFA','2026-02-28 10:49:29','Livr├⌐e','2026-02-28 18:49:29',22,'alimatou','764582813'),(53,'',150000.00,'FCFA','2026-02-28 10:56:23','en_attente','2026-02-28 18:56:23',22,'alimatou','764582813'),(54,'',20000.00,'FCFA','2026-02-28 11:03:56','en_attente','2026-02-28 19:03:56',22,'alimatou','764582813'),(55,'',30000.00,'FCFA','2026-02-28 11:12:20','Livr├⌐e','2026-02-28 19:12:20',22,'alimatou','764582813'),(56,'',20000.00,'FCFA','2026-02-28 11:18:30','en_attente','2026-02-28 19:18:30',22,'alimatou','764582813'),(57,'',220000.00,'FCFA','2026-02-28 12:23:23','en_attente','2026-02-28 20:23:23',22,'alimatou','764582813'),(58,'',1016200.00,'FCFA','2026-02-28 15:05:22','en_attente','2026-02-28 23:05:22',22,'alimatou','764582813'),(59,'',595600.00,'FCFA','2026-02-28 19:11:22','en_attente','2026-03-01 03:11:22',22,NULL,'764582813'),(60,'',595600.00,'FCFA','2026-02-28 19:15:32','en_attente','2026-03-01 03:15:32',22,NULL,'764582813'),(61,'',595600.00,'FCFA','2026-02-28 19:32:29','en_attente','2026-03-01 03:32:29',22,NULL,'764582813'),(62,'',595600.00,'FCFA','2026-02-28 19:34:52','en_attente','2026-03-01 03:34:52',22,NULL,'764582813'),(63,'',595600.00,'FCFA','2026-02-28 19:35:02','en_attente','2026-03-01 03:35:02',22,NULL,'764582813'),(64,'',595600.00,'FCFA','2026-02-28 19:46:07','en_attente','2026-03-01 03:46:07',22,NULL,'764582813'),(65,'',595600.00,'FCFA','2026-02-28 19:46:19','en_attente','2026-03-01 03:46:19',22,NULL,'764582813'),(66,'',595600.00,'FCFA','2026-02-28 19:46:29','en_attente','2026-03-01 03:46:29',22,NULL,'764582813'),(67,'',595600.00,'FCFA','2026-02-28 20:09:20','en_attente','2026-03-01 04:09:20',22,NULL,'764582813'),(68,'',595600.00,'FCFA','2026-02-28 20:10:43','en_attente','2026-03-01 04:10:43',22,NULL,'764582813'),(69,'',595600.00,'FCFA','2026-02-28 20:11:39','en_attente','2026-03-01 04:11:39',22,NULL,'764582813'),(70,'',595600.00,'FCFA','2026-02-28 20:19:56','en_attente','2026-03-01 04:19:56',22,NULL,'764582813'),(71,'',595600.00,'FCFA','2026-02-28 20:50:46','en_attente','2026-03-01 04:50:46',22,NULL,'764582813'),(72,'',595600.00,'FCFA','2026-02-28 20:51:06','en_attente','2026-03-01 04:51:06',22,NULL,'764582813'),(73,'',595600.00,'FCFA','2026-02-28 20:55:49','en_attente','2026-03-01 04:55:49',22,NULL,'764582813'),(74,'',595600.00,'FCFA','2026-02-28 20:58:09','en_attente','2026-03-01 04:58:09',22,NULL,'764582813'),(75,'',595600.00,'FCFA','2026-02-28 21:29:03','Livr├⌐e','2026-03-01 05:29:03',22,NULL,'764582813'),(76,'',595600.00,'FCFA','2026-03-01 09:00:58','en_attente','2026-03-01 17:00:58',22,NULL,'764582813'),(77,'',595600.00,'FCFA','2026-03-01 09:10:25','pay├⌐e','2026-03-01 17:10:25',22,NULL,'764582813'),(78,'',22800.00,'FCFA','2026-03-01 09:16:45','pay├⌐e','2026-03-01 17:16:45',21,NULL,'7771255273'),(79,'',40000.00,'FCFA','2026-03-01 09:26:19','pay├⌐e','2026-03-01 17:26:19',21,NULL,'771621362'),(80,'',100000.00,'FCFA','2026-03-01 09:30:16','Livr├⌐e','2026-03-01 17:30:16',NULL,NULL,'781110011'),(81,'',20000.00,'FCFA','2026-03-01 09:40:40','pay├⌐e','2026-03-01 17:40:40',23,NULL,'7899900088'),(82,'',20000.00,'FCFA','2026-03-01 09:43:08','en_attente','2026-03-01 17:43:08',21,NULL,'567890'),(83,'',20000.00,'FCFA','2026-03-01 09:43:44','pay├⌐e','2026-03-01 17:43:44',21,NULL,'567890'),(84,'',20000.00,'FCFA','2026-03-01 09:55:17','en_attente','2026-03-01 17:55:17',21,NULL,'345678'),(85,'',20000.00,'FCFA','2026-03-01 09:56:32','en_attente','2026-03-01 17:56:32',21,NULL,'345678'),(86,'',20000.00,'FCFA','2026-03-01 10:03:00','pay├⌐e','2026-03-01 18:03:00',21,NULL,'345678'),(87,'',2800.00,'FCFA','2026-03-01 10:04:25','pay├⌐e','2026-03-01 18:04:25',21,NULL,'780152522'),(88,'',100000.00,'FCFA','2026-03-01 10:20:54','pay├⌐e','2026-03-01 18:20:54',21,'rassoul','56789'),(89,'',20000.00,'FCFA','2026-03-01 10:30:35','Livr├⌐e','2026-03-01 18:30:35',24,'alladji','7811221122'),(90,'',100400.00,'FCFA','2026-03-01 10:39:39','pay├⌐e','2026-03-01 18:39:39',4,'dieylany','780152522'),(91,'',30000.00,'FCFA','2026-03-01 10:55:50','Livr├⌐e','2026-03-01 18:55:50',25,'dieylany','78015252'),(92,'',100000.00,'FCFA','2026-03-01 10:58:32','en_attente','2026-03-01 18:58:32',26,'1234','13w'),(93,'',100000.00,'FCFA','2026-03-01 11:08:23','en_attente','2026-03-01 19:08:23',27,'dieylany','+221780152522'),(94,'',100000.00,'FCFA','2026-03-01 11:08:49','en_attente','2026-03-01 19:08:49',28,'fatima','+221771621369'),(95,'',100000.00,'FCFA','2026-03-01 12:09:24','pay├⌐e','2026-03-01 20:09:24',28,'fatima','+221771621369'),(96,'',20000.00,'FCFA','2026-03-01 12:19:36','en_attente','2026-03-01 20:19:36',25,'modou','+221780152522'),(97,'',550000.00,'FCFA','2026-03-01 16:05:49','en_attente','2026-03-02 00:05:49',27,'dieylany','+221780152522'),(98,'',550000.00,'FCFA','2026-03-01 16:06:08','Livr├⌐e','2026-03-02 00:06:08',27,'dieylany','+221780152522'),(99,'',160000.00,'FCFA','2026-03-01 16:12:24','Livr├⌐e','2026-03-02 00:12:24',29,'Tierno','002202657822'),(100,'',20000.00,'FCFA','2026-03-01 16:42:59','en_attente','2026-03-02 00:42:59',29,'Mame ndiaye savon','002202657822'),(101,'',100000.00,'FCFA','2026-03-02 05:44:16','Livr├⌐e','2026-03-02 13:44:16',28,'fatima','+221771621369'),(102,'',40500.00,'FCFA','2026-03-02 05:58:48','en_attente','2026-03-02 13:58:48',27,'dieylany','+221780152522'),(103,'',40500.00,'FCFA','2026-03-02 05:59:18','Livr├⌐e','2026-03-02 13:59:18',27,'dieylany','+221780152522'),(104,'',20000.00,'FCFA','2026-03-06 04:03:23','Livr├⌐e','2026-03-06 12:03:23',30,'Babs diaw','769458738'),(105,'',44500.00,'FCFA','2026-03-06 04:11:52','en_attente','2026-03-06 12:11:52',28,'b','+221771621369'),(106,'',5678.00,'FCFA','2026-03-27 02:30:23','Livr├⌐e','2026-03-27 10:30:23',31,'mouhamed bah','771828980');
/*!40000 ALTER TABLE `commandes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `details_commande`
--

DROP TABLE IF EXISTS `details_commande`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `details_commande` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_commande` int(11) NOT NULL,
  `id_produit` int(11) NOT NULL,
  `quantite` int(11) NOT NULL,
  `prix_unitaire` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_commande` (`id_commande`),
  KEY `id_produit` (`id_produit`),
  CONSTRAINT `details_commande_ibfk_1` FOREIGN KEY (`id_commande`) REFERENCES `commandes` (`id`),
  CONSTRAINT `details_commande_ibfk_2` FOREIGN KEY (`id_produit`) REFERENCES `produits` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=83 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `details_commande`
--

LOCK TABLES `details_commande` WRITE;
/*!40000 ALTER TABLE `details_commande` DISABLE KEYS */;
INSERT INTO `details_commande` VALUES (1,1,2,3,5000.00),(2,2,4,10,15000.00),(3,3,4,2,15000.00),(4,4,4,1,15000.00),(5,5,2,4,5000.00),(6,5,4,6,15000.00),(7,5,3,1,10000.00),(8,6,2,2,5000.00),(9,28,45,4,14000.00),(10,29,46,8,2800.00),(11,29,47,3,20000.00),(12,29,41,1,27000.00),(13,29,38,1,4000.00),(14,29,42,1,30400.00),(15,30,46,13,2800.00),(16,30,44,6,40000.00),(17,30,43,4,30000.00),(18,31,48,25,100000.00),(19,32,48,2,100000.00),(20,33,45,3,14000.00),(21,34,46,1,2800.00),(22,35,46,1,2800.00),(23,36,46,1,2800.00),(24,37,48,1,100000.00),(25,38,46,5,2800.00),(26,39,48,6,100000.00),(27,40,45,1,14000.00),(28,41,48,1,100000.00),(29,42,48,1,100000.00),(30,43,47,1,20000.00),(31,44,46,3,2800.00),(32,45,48,1,100000.00),(33,46,48,3,100000.00),(34,47,46,1,2800.00),(35,48,48,2,100000.00),(36,49,49,2,300000.00),(37,49,37,1,10000.00),(38,50,34,2,50000.00),(39,50,26,2,90000.00),(40,50,24,2,25000.00),(41,51,29,1,50000.00),(42,51,43,2,30000.00),(43,52,47,1,20000.00),(44,53,48,1,100000.00),(45,53,34,1,50000.00),(46,54,47,1,20000.00),(47,55,43,1,30000.00),(48,56,47,1,20000.00),(49,57,48,2,100000.00),(50,57,47,1,20000.00),(51,58,44,2,40000.00),(52,58,45,1,14000.00),(53,58,49,1,300000.00),(54,58,2,3,5000.00),(55,58,46,4,2800.00),(56,58,48,5,100000.00),(57,58,43,3,30000.00),(58,58,30,1,6000.00),(59,89,47,1,20000.00),(60,90,44,1,40000.00),(61,90,43,1,30000.00),(62,90,42,1,30400.00),(63,91,43,1,30000.00),(64,92,48,1,100000.00),(65,93,48,1,100000.00),(66,94,48,1,100000.00),(67,95,48,1,100000.00),(68,96,47,1,20000.00),(69,97,33,3,150000.00),(70,97,29,2,50000.00),(71,98,33,3,150000.00),(72,98,29,2,50000.00),(73,99,9,1,75000.00),(74,99,35,1,35000.00),(75,99,34,1,50000.00),(76,100,22,2,10000.00),(77,101,48,1,100000.00),(78,102,50,1,40500.00),(79,103,50,1,40500.00),(80,104,47,1,20000.00),(81,105,11,1,44500.00),(82,106,60,1,5678.00);
/*!40000 ALTER TABLE `details_commande` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `logs_activite`
--

DROP TABLE IF EXISTS `logs_activite`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `logs_activite` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_admin` int(11) NOT NULL,
  `admin_nom` varchar(100) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `logs_activite`
--

LOCK TABLES `logs_activite` WRITE;
/*!40000 ALTER TABLE `logs_activite` DISABLE KEYS */;
INSERT INTO `logs_activite` VALUES (1,1,'admin','MODIFICATION_PRODUIT','A modifi├⌐ le produit : Full Ice Royal (ID: 48)','::1','2026-02-25 18:20:06'),(2,1,'admin','CONNEXION','L\'administrateur s\'est connect├⌐ au panel.','::1','2026-02-26 07:07:12'),(3,1,'admin','CONNEXION','L\'administrateur s\'est connect├⌐ au panel.','::1','2026-02-26 07:08:23'),(4,6,'thierno','MODIFICATION_PRODUIT','A modifi├⌐ le produit : Full Ice Royal (ID: 48)','::1','2026-02-26 07:11:30'),(5,6,'thierno','MODIF_CLIENT','Mise ├á jour du client : fatima diop (ID: 12)','::1','2026-02-26 07:21:28'),(11,6,'thierno','SUPPRESSION','Produit supprim├⌐ : diaarou (ID: 7)','::1','2026-02-26 09:24:31'),(12,6,'thierno','MODIFICATION_PRODUIT','A modifi├⌐ le produit : fiancaille (ID: 5)','::1','2026-02-26 09:25:42'),(13,6,'thierno','MODIF_CLIENT','Mise ├á jour du client : fatyma diop (ID: 12)','::1','2026-02-27 13:28:02'),(14,6,'thierno','MODIF_CLIENT','Mise ├á jour du client : fatyma diop (ID: 12)','::1','2026-02-27 13:40:36'),(15,6,'thierno','MODIF_CLIENT','Mise ├á jour du client : nogaye (ID: 12)','::1','2026-02-27 13:48:05'),(16,6,'thierno','MODIF_CLIENT','Mise ├á jour du client : ELhadji Sakho (ID: 12)','::1','2026-02-27 14:27:17'),(17,6,'thierno','MODIFICATION_PRODUIT','A modifi├⌐ le produit : Bague en or (ID: 49)','::1','2026-02-28 04:28:33'),(18,6,'thierno','MODIF_CLIENT','Mise ├á jour du client : alimatou (ID: 22)','::1','2026-02-28 11:13:08'),(19,6,'thierno','MODIFICATION_PRODUIT','A modifi├⌐ le produit : Full Ice Royal (ID: 48)','::1','2026-02-28 16:29:19'),(20,6,'thierno','MODIFICATION_PRODUIT','A modifi├⌐ le produit : Alliances Bross├⌐es Forever (ID: 59)','::1','2026-03-01 16:46:32'),(21,6,'thierno','CONNEXION','L\'administrateur s\'est connect├⌐ au panel.','::1','2026-03-01 18:11:20'),(22,6,'thierno','CONNEXION','L\'administrateur s\'est connect├⌐ au panel.','::1','2026-03-01 18:11:31'),(23,6,'thierno','CONNEXION','L\'administrateur s\'est connect├⌐ au panel.','::1','2026-03-01 18:27:36'),(24,6,'thierno','MODIFICATION_PRODUIT','A modifi├⌐ le produit : Duo d\'Alliances Roi &amp; Reine (ID: 55)','::1','2026-03-01 18:33:01'),(25,6,'thierno','MODIFICATION_PRODUIT','A modifi├⌐ le produit : Montre-Bijou ├ëclat Lunaire (ID: 25)','::1','2026-03-01 18:33:19'),(26,6,'thierno','MODIFICATION_PRODUIT','A modifi├⌐ le produit : Chevali├¿re Homme Griffe d\'Onyx (ID: 54)','::1','2026-03-01 18:33:38'),(27,6,'thierno','CONNEXION','L\'administrateur s\'est connect├⌐ au panel.','::1','2026-03-02 03:18:11'),(28,6,'thierno','MODIFICATION_PRODUIT','A modifi├⌐ le produit : montre  (ID: 60)','::1','2026-03-02 08:20:06'),(29,6,'thierno','CONNEXION','L\'administrateur s\'est connect├⌐ au panel.','172.20.10.3','2026-03-02 16:25:51'),(30,6,'thierno','CONNEXION','L\'administrateur s\'est connect├⌐ au panel.','172.20.10.3','2026-03-02 16:25:52'),(31,1,'admin','CONNEXION','L\'administrateur s\'est connect├⌐ au panel.','::1','2026-03-06 04:04:02'),(32,6,'thierno','CONNEXION','L\'administrateur s\'est connect├⌐ au panel.','::1','2026-03-27 03:52:26'),(33,6,'thierno','CONNEXION','L\'administrateur s\'est connect├⌐ au panel.','::1','2026-04-02 09:45:39');
/*!40000 ALTER TABLE `logs_activite` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `paiements`
--

DROP TABLE IF EXISTS `paiements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `paiements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `commande_id` varchar(50) NOT NULL,
  `methode` enum('orange_money','qmoney','wave') NOT NULL,
  `telephone` varchar(20) NOT NULL,
  `montant` decimal(10,2) NOT NULL,
  `devise` varchar(5) NOT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `statut` enum('en_attente','complete','echoue') DEFAULT 'en_attente',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `paiements`
--

LOCK TABLES `paiements` WRITE;
/*!40000 ALTER TABLE `paiements` DISABLE KEYS */;
/*!40000 ALTER TABLE `paiements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `parametres`
--

DROP TABLE IF EXISTS `parametres`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `parametres` (
  `id` int(11) NOT NULL DEFAULT 1,
  `nom_boutique` varchar(100) NOT NULL,
  `telephone_whatsapp` varchar(20) NOT NULL,
  `email_contact` varchar(150) DEFAULT NULL,
  `adresse_boutique` text DEFAULT NULL,
  `logo_filename` varchar(255) DEFAULT 'logo.png',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `parametres`
--

LOCK TABLES `parametres` WRITE;
/*!40000 ALTER TABLE `parametres` DISABLE KEYS */;
INSERT INTO `parametres` VALUES (1,'Jewelry Beug Fallou','780152522','contact@jawelry.com','Serrekunda, The Gambia','logo_1771944762.png');
/*!40000 ALTER TABLE `parametres` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `produits`
--

DROP TABLE IF EXISTS `produits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `produits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `prix` decimal(10,2) NOT NULL,
  `prix_achat` int(11) NOT NULL DEFAULT 0,
  `image` varchar(255) DEFAULT NULL,
  `video` varchar(255) DEFAULT NULL,
  `modele_3d` varchar(255) DEFAULT NULL,
  `id_categorie` int(11) DEFAULT NULL,
  `stock` int(11) DEFAULT 10,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `metal` varchar(50) DEFAULT 'Or',
  `reduction` int(11) DEFAULT 0,
  `deleted` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `fk_categorie` (`id_categorie`),
  CONSTRAINT `fk_categorie` FOREIGN KEY (`id_categorie`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `produits_ibfk_1` FOREIGN KEY (`id_categorie`) REFERENCES `categories` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=63 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `produits`
--

LOCK TABLES `produits` WRITE;
/*!40000 ALTER TABLE `produits` DISABLE KEYS */;
INSERT INTO `produits` VALUES (2,'Bracelette ','bracelette pour homme ',5000.00,3000,'1771326402_bear-06.png',NULL,NULL,1,998,'2026-02-17 11:06:42','Or',0,1),(3,'Collier','',8000.00,3000,'1771908467_collier2.webp',NULL,NULL,2,100,'2026-02-17 11:07:11','Or',10,0),(4,'montre','une montre de qualite pour homme',13350.00,10000,'1771329697_bear-06.png',NULL,NULL,6,987,'2026-02-17 12:01:37','Or',0,1),(5,'fiancaille','',64050.00,50000,'1771675200_bear-06.png',NULL,NULL,5,100,'2026-02-21 12:00:00','Or',90,1),(8,'Collier ','',5000.00,3000,'1771686897_IMG_3589.png',NULL,NULL,2,20,'2026-02-21 15:14:57','Or',0,0),(9,'Rolex','',75000.00,50000,'1771686978_IMG_3595.jpeg',NULL,NULL,4,100,'2026-02-21 15:16:18','Or',20,0),(10,'Collier ','',10000.00,8000,'1771687031_IMG_3593.jpeg',NULL,NULL,2,101,'2026-02-21 15:17:11','Or',0,0),(11,'Montre ','',44500.00,30000,'1771687086_IMG_3596.jpeg',NULL,NULL,6,100,'2026-02-21 15:18:06','Or',0,0),(13,'Cha├«ne en or ','',42000.00,30000,'1771687220_IMG_3591.jpeg',NULL,NULL,5,96,'2026-02-21 15:20:20','Or',1000,0),(14,'Montre ','',70000.00,40000,'1771687327_IMG_3597.jpeg',NULL,NULL,4,96,'2026-02-21 15:22:07','Or',20,0),(15,'Collier en or','',70000.00,50000,'1771908441_CL4.webp',NULL,NULL,2,10000,'2026-02-24 04:47:21','Or',20,0),(16,'collier','',10000.00,5000,'1771909747_CL5.webp',NULL,NULL,2,1100,'2026-02-24 04:49:35','Or',0,0),(18,'Collier','',4000.00,1500,'1771909467_CL6.webp',NULL,NULL,2,100,'2026-02-24 05:04:27','Or',0,0),(19,'Collier','',75000.00,50000,'1771909536_CL8.webp',NULL,NULL,2,100,'2026-02-24 05:05:36','Or',0,0),(20,'Collier','',60000.00,30000,'1771909564_CL9.jpg',NULL,NULL,2,100,'2026-02-24 05:06:04','Or',0,0),(21,'Collier','',10000.00,6000,'1771909591_COL3.webp',NULL,NULL,2,100,'2026-02-24 05:06:31','Or',0,0),(22,'Collier','',10000.00,5000,'1771909616_collier.webp',NULL,NULL,2,100,'2026-02-24 05:06:56','Or',0,0),(23,'Collier','',4000.00,2000,'1771909643_collier2.webp',NULL,NULL,2,100,'2026-02-24 05:07:23','Or',0,0),(24,'Full Ice Royal','Affirmez votre style avec cette montre chronographe de caract├¿re, con├ºue pour l\'homme moderne qui exige pr├⌐cision et distinction. Un design robuste alli├⌐ ├á une finition luxueuse. Cette pi├¿ce ma├«tresse pr├⌐sente :\r\n\r\nBo├«tier &amp;amp; Bracelet : R├⌐alis├⌐e en acier inoxydable poli de haute qualit├⌐, avec un bracelet articul├⌐ aux maillons centraux textur├⌐s pour un ├⌐clat unique au poignet.\r\n\r\nCadran Haute Performance : Un fond noir profond contrast├⌐ par des index argent├⌐s luminescents. ├ëquip├⌐e de trois sous-cadrans fonctionnels et d\'une trotteuse rouge sport pour une lecture pr├⌐cise du temps.\r\n\r\nLunette Tachym├⌐trique : Une lunette grav├⌐e permettant de mesurer la vitesse, ajoutant une touche technique et sophistiqu├⌐e ├á l\'ensemble.\r\n\r\nD├⌐tails de Luxe : Couronne crant├⌐e et boutons poussoirs ergonomiques pour un r├⌐glage fluide et un confort optimal.',25000.00,10000,'1771911296_M1.webp',NULL,NULL,4,50,'2026-02-24 05:34:56','Or',20,0),(25,'Montre-Bijou ├ëclat Lunaire','Plus qu\'une simple montre, une v├⌐ritable pi├¿ce de joaillerie pour habiller votre poignet avec gr├óce. Ce mod├¿le unique combine la pr├⌐cision d\'un mouvement Quartz et le raffinement d\'un bracelet orn├⌐. Elle se distingue par :\r\n\r\nBracelet Ornemental : Un design exclusif compos├⌐ d\'anneaux entrelac├⌐s, finement sertis de cristaux ├⌐tincelants qui capturent la lumi├¿re ├á chaque mouvement.\r\n\r\nCadran &amp;quot;Nuit ├ëtoil├⌐e&amp;quot; : Un fond noir minimaliste avec des chiffres arabes argent├⌐s (3, 6, 9, 12) pour une lecture claire et ├⌐l├⌐gante.\r\n\r\nFinition Argent├⌐e Miroir : Un bo├«tier en alliage de haute qualit├⌐ poli pour un fini brillant durable.\r\n\r\nStyle Versatile : Son allure sophistiqu├⌐e en fait l\'accessoire parfait pour accompagner vos tenues de soir├⌐e ou pour ajouter une touche de luxe ├á un look d├⌐contract├⌐.',40000.00,30000,'1771911357_M2.webp',NULL,NULL,4,1,'2026-02-24 05:35:57','Or',20,0),(26,'Dark Octo Copper','Dominez le temps avec ce garde-temps au design avant-gardiste. Alliant l\'esth├⌐tique brute de l\'aviation et le luxe contemporain, cette montre est destin├⌐e aux hommes qui osent l\'originalit├⌐. Elle se distingue par :\r\n\r\nBo├«tier G├⌐om├⌐trique : Un cadran octogonal unique en acier bross├⌐ noir mat, offrant une pr├⌐sence imposante et moderne au poignet.\r\n\r\nBracelet Cuir Artisanal : Un cuir noir de haute qualit├⌐ avec des surpiq├╗res orange contrast├⌐es, rappelant les int├⌐rieurs des voitures de sport de luxe.\r\n\r\nCadran Complexe Multi-Couches : Un affichage tridimensionnel avec des aiguilles dor├⌐es luminescentes pour une lisibilit├⌐ parfaite, m├¬me dans l\'obscurit├⌐.\r\n\r\nMouvement de Haute Pr├⌐cision : ├ëquip├⌐e de plusieurs cadrans internes pour les fonctions chronom├¿tre, offrant une pr├⌐cision millim├⌐tr├⌐e.',90000.00,65000,'1771911413_M3.webp',NULL,NULL,4,100,'2026-02-24 05:36:53','argent',20,0),(28,'Chronographe Horizon Tech - Silver &amp; Carbon','D├⌐couvrez l\'alliance parfaite entre l\'ing├⌐nierie de pr├⌐cision et le design futuriste. Cette montre se distingue par son cadran r├⌐volutionnaire et son allure technologique. Elle est dot├⌐e de :\r\n\r\nCadran Guilloch├⌐ Lin├⌐aire : Un fond argent├⌐ travaill├⌐ avec des lignes horizontales qui cr├⌐ent des jeux de lumi├¿re fascinants selon l\'inclinaison du poignet.\r\n\r\nAffichage Bi-Compax Asym├⌐trique : Deux compteurs noirs contrast├⌐s pour les fonctions de chronom├⌐trage, offrant une lecture du temps originale et ultra-moderne.\r\n\r\nBracelet &quot;Carbone Structure&quot; : Un bracelet noir ├á la texture gaufr├⌐e haute r├⌐sistance, offrant un confort absolu et un look r├⌐solument sportif.\r\n\r\nBo├«tier Acier Architectural : Des lignes sculpt├⌐es et une lunette polie qui soulignent la robustesse et le prestige de cette pi├¿ce d\'exception.',70000.00,50000,'1771911574_M4.webp',NULL,NULL,4,100,'2026-02-24 05:39:34','Or',20,0),(29,'Montre','',50000.00,30000,'1771911604_M5.webp',NULL,NULL,4,100,'2026-02-24 05:40:04','Or',20,0),(30,'montre','',6000.00,5000,'1771911635_CD6.webp',NULL,NULL,4,100,'2026-02-24 05:40:35','Or',20,0),(31,'Tactical Blue Force','Con├ºue pour l\'endurance, cette montre hybride allie la lisibilit├⌐ de l\'analogique ├á la pr├⌐cision du num├⌐rique. Un outil indispensable pour ceux qui repoussent leurs limites.\r\n\r\nDouble Affichage : Aiguilles luminescentes bleues et ├⌐crans LCD r├⌐tro├⌐clair├⌐s (Heure, Date, Alarme).\r\n\r\nStructure Renforc├⌐e : Bo├«tier antichoc en polym├¿re haute densit├⌐ avec boutons tactiques &quot;Easy-Push&quot;.\r\n\r\nBracelet : R├⌐sine flexible textur├⌐e pour une ├⌐vacuation optimale de la transpiration.\r\n\r\nDesign : Finition noire mat avec accents bleu ├⌐lectrique pour un look r├⌐solument moderne.',2000.00,1500,'1771911659_M7.webp',NULL,NULL,4,100,'2026-02-24 05:40:59','Or',20,0),(33,'Galaxy Watch 4','L\'├⌐l├⌐gance alli├⌐e ├á la technologie de pointe. Suivez votre sant├⌐ et restez connect├⌐ avec style gr├óce ├á cette montre intelligente au design circulaire ├⌐pur├⌐.\r\n\r\n├ëcran : Super AMOLED haute r├⌐solution.\r\n\r\nSant├⌐ : Suivi cardiaque, sommeil et GPS int├⌐gr├⌐.\r\n\r\nConnectivit├⌐ : Notifications smartphone en temps r├⌐el.',150000.00,100000,'1771911744_M8.jpg',NULL,NULL,4,99,'2026-02-24 05:42:24','Or',20,0),(34,'Eclipse Black','Un design avant-gardiste o├╣ le temps s\'affiche de mani├¿re rotative. Cette montre est une v├⌐ritable d├⌐claration de style pour ceux qui cherchent l\'atypique.\r\n\r\nCadran : Affichage par disques rotatifs (Heures/Minutes).\r\n\r\nBo├«tier : Acier noir mat ultra-fin.\r\n\r\nBracelet : Cuir v├⌐ritable textur├⌐ fa├ºon croco.',50000.00,40000,'1771911766_M9.webp',NULL,NULL,4,100,'2026-02-24 05:42:46','Or',20,0),(35,'Tactical Stealth','La version &quot;Full Black&quot; du mod├¿le sport. Un garde-temps hybride ultra-r├⌐sistant con├ºu pour la discr├⌐tion et l\'efficacit├⌐ sur le terrain.\r\n\r\nAffichage : Double fuseau (Aiguilles &amp; LCD).\r\n\r\nFonctions : Chronom├¿tre, alarme et r├⌐tro├⌐clairage vert.\r\n\r\nBracelet : Silicone haute densit├⌐.',35000.00,30000,'1771911788_M10.webp',NULL,NULL,4,100,'2026-02-24 05:43:08','Or',20,0),(36,'Coffret ├ëternit├⌐ ├ëclatante','Offrez un symbole d\'amour imp├⌐rissable avec ce somptueux coffret cadeau multi-pi├¿ces. Ce set d\'exception comprend :\r\n\r\nLa Rose ├ëternelle : Une rose rouge ├⌐clatante pr├⌐serv├⌐e sous un d├┤me de verre, symbole d\'une affection qui ne fl├⌐trit jamais.\r\n\r\nLa Parure de Bijoux : Un collier ├⌐l├⌐gant orn├⌐ d\'un pendentif en double c┼ôur serti de cristaux, accompagn├⌐ d\'une paire de boucles d\'oreilles scintillantes et d\'un bracelet &quot;Infini&quot; assorti.\r\n\r\nL\'├ëcrin ├á Tiroir : Un coffret noir luxueux avec un tiroir secret pour ranger les bijoux, marqu├⌐ &quot;Best Wishes&quot;.\r\n\r\nAccessoires Inclus : Livr├⌐ avec son sac de transport ├⌐l├⌐gant et une carte de v┼ôux pour personnaliser votre message.\r\n\r\nId├⌐al pour : Saint-Valentin, anniversaire de mariage ou une d├⌐claration inoubliable.',28000.00,20000,'1771945835_CD1.webp',NULL,NULL,9,100,'2026-02-24 15:10:35','Or',10,0),(37,'Parure Tri-S├⌐quence','Laissez-vous s├⌐duire par la puret├⌐ et l\'├⌐clat de cette parure au design contemporain. Un ensemble harmonieux qui joue avec la lumi├¿re et le mouvement. Ce coffret comprend :\r\n\r\nLe Collier Trio de Pastilles : Une cha├«ne fine en or jaune orn├⌐e de trois pampilles circulaires polies, cr├⌐ant un mouvement fluide et une brillance subtile ├á chaque geste.\r\n\r\nLes Cr├⌐oles Assorties : Une paire de boucles d\'oreilles cr├⌐oles d├⌐licates, chacune agr├⌐ment├⌐e de trois pastilles mobiles pour un rappel parfait du collier.\r\n\r\n├ëcrin de Luxe &amp;amp;quot;Petite Mode&amp;amp;quot; : Pr├⌐sent├⌐ dans son coffret blanc minimaliste avec un int├⌐rieur en velours noir pour une mise en valeur optimale des bijoux.\r\n\r\nFinition Haute Qualit├⌐ : Un alliage dor├⌐ r├⌐sistant et brillant, con├ºu pour durer et garder tout son ├⌐clat.\r\n\r\nId├⌐al pour : Un cadeau d\'anniversaire, une demoiselle d\'honneur ou simplement pour se faire plaisir avec une touche de luxe discret.',10000.00,7000,'1771946105_CD2.webp',NULL,NULL,9,100,'2026-02-24 15:15:05','Or',0,0),(38,'Cadeau pour femme','',4000.00,1500,'1771946261_CD3.webp',NULL,NULL,9,10,'2026-02-24 15:17:41','Or',0,0),(39,'Cadeau','',6000.00,5000,'1771947560_CD4.webp',NULL,NULL,9,100,'2026-02-24 15:39:02','Or',0,0),(40,'Coffret Tr├¿fle de Lumi├¿re','Invoquez la chance et l\'├⌐l├⌐gance avec cette parure scintillante, un symbole intemporel de bonheur et de r├⌐ussite. Cet ensemble raffin├⌐ comprend :\r\n\r\nLe Collier Tr├¿fle ├á Quatre Feuilles : Un pendentif d├⌐licat en forme de tr├¿fle, enti├¿rement serti de cristaux brillants qui capturent la lumi├¿re ├á chaque mouvement.\r\n\r\nLes Boucles d\'Oreilles Solitaires : Deux puces d\'oreilles ├⌐l├⌐gantes avec cristaux griff├⌐s, offrant une brillance pure pour illuminer le visage.\r\n\r\n├ëcrin Rouge Passion : Pr├⌐sent├⌐ dans un somptueux bo├«tier rouge au fini velout├⌐, cr├⌐ant un contraste saisissant avec l\'├⌐clat de l\'argent.\r\n\r\nFinition Argent├⌐e Protectrice : Con├ºu dans un alliage durable avec un placage argent├⌐ de haute qualit├⌐ pour un ├⌐clat durable et une sensation de luxe.\r\n\r\nId├⌐al pour : Un cadeau de remise de dipl├┤me, un anniversaire ou comme porte-bonheur pour un nouveau d├⌐part.',4000.00,2000,'1771977753_CD3.webp',NULL,NULL,9,100,'2026-02-25 00:02:33','Or',0,0),(41,'Parure Jardin d\'Hiver','Entrez dans un monde de f├⌐erie avec cette parure magistrale inspir├⌐e de la nature. Chaque pi├¿ce est une ┼ôuvre d\'art con├ºue pour sublimer votre port de t├¬te et vos mains. Ce coffret de prestige contient :\r\n\r\nLe Collier Plastron Floral : Une pi├¿ce ma├«tresse spectaculaire compos├⌐e d\'une succession de fleurs et de feuilles finement cisel├⌐es, enti├¿rement pav├⌐es de cristaux clairs pour un ├⌐clat d\'une intensit├⌐ rare.\r\n\r\nLa Bague Solitaire Royale : Une bague assortie arborant un cristal central imposant, entour├⌐ d\'un sertissage d├⌐licat rappelant les motifs floraux du collier.\r\n\r\nLes Boucles d\'Oreilles Assorties : Un rappel ├⌐l├⌐gant du motif floral pour un ensemble parfaitement harmonieux.\r\n\r\n├ëcrin Signature Noir : Pr├⌐sent├⌐ dans un large coffret luxe ├á clapet, dont l\'int├⌐rieur en velours noir profond fait ressortir chaque d├⌐tail de cette parure d\'exception.\r\n\r\nId├⌐al pour : Un mariage, un gala de prestige ou une soir├⌐e o├╣ vous souhaitez ├¬tre la seule ├á briller.',30000.00,15000,'1771977791_CD4.webp',NULL,NULL,9,100,'2026-02-25 00:03:11','Or',10,0),(42,'Collection H├⌐ritage Signature','Plongez dans l\'univers du grand luxe avec cet assortiment complet de joaillerie fine, pr├⌐sent├⌐ dans une architecture de bo├«tiers magn├⌐tiques. Une collection pens├⌐e pour les amateurs de pi├¿ces authentiques et sophistiqu├⌐es. Cet ensemble prestige r├⌐unit :\r\n\r\nLe Duo de Colliers Pendentifs : Deux cha├«nes distinctes arborant des m├⌐daillons finement cisel├⌐s aux motifs baroques et floraux, m├¬lant l\'argent massif et des touches d\'or rose.\r\n\r\nLe Bracelet de Perles de Culture : Un rang de perles nacr├⌐es d\'une rondeur parfaite, dot├⌐ d\'un fermoir anneau moderne pour un style ├á la fois classique et actuel.\r\n\r\nLa Bague &amp;quot;N┼ôud de Vie&amp;quot; : Un anneau travaill├⌐ en entrelacs complexes, pr├⌐sent├⌐ dans son propre ├⌐crin solitaire pour une mise en valeur spectaculaire.\r\n\r\nLe Bracelet Perles de Onyx : Une pi├¿ce plus sombre et myst├⌐rieuse en perles de pierre noire mate, apportant un contraste audacieux ├á l\'ensemble.\r\n\r\nArchitecture de Luxe : Cinq bo├«tiers noirs rigides avec signature argent├⌐e, permettant une organisation parfaite de votre collection.',38000.00,25000,'1771977833_CD5.webp',NULL,NULL,9,100,'2026-02-25 00:03:53','Or',20,0),(43,'Coffret Majest├⌐ Intemporelle','Atteignez le sommet du raffinement avec ce coffret multi-pi├¿ces d\'une ├⌐l├⌐gance absolue. Cette ├⌐dition limit├⌐e a ├⌐t├⌐ con├ºue pour offrir une exp├⌐rience de luxe compl├¿te, m├¬lant joaillerie fine et horlogerie de pr├⌐cision. Ce set magistral comprend :\r\n\r\nLa Montre &amp;quot;├ëclat Argent├⌐&amp;quot; : Une pi├¿ce d\'horlogerie raffin├⌐e avec un cadran noir profond contrastant avec un bracelet en acier inoxydable, alliant fonctionnalit├⌐ et prestige.\r\n\r\nLe Duo de Pendentifs Baroques : Deux colliers aux m├⌐daillons finement grav├⌐s, parfaits pour un port├⌐ en cascade (layering) tr├¿s tendance.\r\n\r\nLe Bracelet de Perles de Nacre : Un rang de perles d\'un blanc pur avec un fermoir mousqueton moderne pour une touche de douceur classique.\r\n\r\nLa Bague &amp;quot;Fleur de Cristal&amp;quot; : Pr├⌐sent├⌐e dans son ├⌐crin d├⌐di├⌐, cette bague en entrelacs sertis de pierres fines est le symbole d\'un savoir-faire artisanal d\'exception.\r\n\r\nLe Bracelet de Pierres Volcaniques : Un bracelet en perles noires mates pour apporter une note de caract├¿re et de modernit├⌐ ├á l\'ensemble.\r\n\r\n├ëcrins Multiples Haute Couture : Chaque pi├¿ce est prot├⌐g├⌐e dans son propre bo├«tier noir rigide, le tout pr├⌐sent├⌐ dans une architecture de bo├«tes magn├⌐tiques.',40000.00,30000,'1771977861_CD6.webp',NULL,NULL,9,100,'2026-02-25 00:04:21','Or',25,0),(44,'Malle Collection Priv├⌐e','Passez ├á la vitesse sup├⌐rieure avec ce coffret monumental, une v├⌐ritable vitrine de luxe transportable. Con├ºue pour celles qui ne veulent jamais avoir ├á choisir, cette malle ├⌐l├⌐gante renferme un tr├⌐sor de diversit├⌐. Ce set hors norme comprend :\r\n\r\nLa Malle de Transport Executive : Un coffret rigide style mallette avec poign├⌐e renforc├⌐e et int├⌐rieur en velours rouge passion, offrant des rangements optimis├⌐s pour chaque type de bijou.\r\n\r\nLΓÇÖAssortiment de Colliers (x8) : Une s├⌐lection vari├⌐e de cha├«nes fines, pendentifs solitaires et colliers ├á th├¿mes pr├⌐sent├⌐s sur leurs supports individuels.\r\n\r\nLa Collection de Boucles dΓÇÖOreilles (x20+) : Un plateau complet incluant des puces scintillantes, des pendants raffin├⌐s et des mod├¿les fantaisies pour s\'adapter ├á toutes vos tenues.\r\n\r\nLe Set de Bracelets et Bagues : Plusieurs pi├¿ces compl├⌐mentaires pour finaliser vos parures avec go├╗t et distinction.\r\n\r\nFinition Professionnelle : Chaque bijou est soigneusement ├⌐tiquet├⌐ et pr├⌐sent├⌐, garantissant une organisation impeccable de votre collection.',40000.00,25000,'1771977889_CD7.webp',NULL,NULL,9,15,'2026-02-25 00:04:49','Or',0,0),(45,'├ëcrins Duo Luminescence','C├⌐l├⌐brez la dualit├⌐ et l\'harmonie avec ce set de coffrets minimalistes. Un design ├⌐pur├⌐ qui laisse toute la place ├á l\'├⌐clat du bijou, parfait pour marquer un moment pr├⌐cieux ├á deux. Cet ensemble comprend :\r\n\r\nLes Coffrets de Pr├⌐sentation (x3) : Un assortiment de deux ├⌐crins blanc cr├¿me et un noir de jais, au fini mat soyeux pour un toucher luxueux.\r\n\r\nLe Collier Pendentif &amp;quot;C┼ôur d\'Argent&amp;quot; : Une cha├«ne ultra-fine supportant un c┼ôur d├⌐licat, symbole de puret├⌐ et d\'attachement.\r\n\r\nLe Set de Puces d\'Oreilles : Des bijoux discrets en argent massif, con├ºus pour apporter une touche de lumi├¿re quotidienne.\r\n\r\nInt├⌐rieur Satin├⌐ : Chaque bo├«te est doubl├⌐e d\'un support en velours protecteur pour maintenir vos bijoux en place avec ├⌐l├⌐gance.\r\n\r\nEsth├⌐tique Moderne : Des lignes rectangulaires et ├⌐pur├⌐es qui s\'int├¿grent parfaitement dans une d├⌐coration d\'int├⌐rieur contemporaine.',14000.00,10000,'1771977915_CD8.webp',NULL,NULL,9,100,'2026-02-25 00:05:15','Or',0,0),(46,'├ëcrin Entrelacs de C┼ôur','C├⌐l├⌐brez l\'affection avec cet ensemble gracieux qui mise sur le d├⌐tail et la symbolique. Un design a├⌐rien et scintillant qui apporte une touche de magie ├á chaque tenue. Ce coffret comprend :\r\n\r\nLe Pendentif Tr├¿fle-C┼ôur : Une pi├¿ce centrale unique compos├⌐e de quatre c┼ôurs entrelac├⌐s formant un tr├¿fle de chance, enti├¿rement pav├⌐ de micro-cristaux pour un ├⌐clat continu.\r\n\r\nLes Puces d\'Oreilles Assorties : Deux c┼ôurs solitaires scintillants qui rappellent d├⌐licatement le motif du collier pour une harmonie parfaite.\r\n\r\nL\'├ëcrin de Pr├⌐sentation &amp;quot;Douceur&amp;quot; : Pr├⌐sent├⌐ sur un support en satin blanc immacul├⌐ dans un bo├«tier noir chic, orn├⌐ d\'un petit n┼ôud bicolore traditionnel.\r\n\r\nCha├«ne Fine Haute Qualit├⌐ : Une maille argent├⌐e fluide et r├⌐sistante, con├ºue pour un confort optimal tout au long de la journ├⌐e.',2800.00,1000,'1771977942_CD9.webp',NULL,NULL,9,100,'2026-02-25 00:05:42','Or',0,0),(47,'Collection ├ëclat Bor├⌐al','D├⌐couvrez une constellation de cadeaux raffin├⌐s avec cet ensemble polyvalent, con├ºu pour c├⌐l├⌐brer chaque facette de la beaut├⌐. Cette collection offre une vari├⌐t├⌐ de textures et de formes pour un cadeau sur mesure. Le set comprend :\r\n\r\nLe Collier Perles de Culture : Un rang classique de perles blanches nacr├⌐es pr├⌐sent├⌐ dans son ├⌐crin longiligne, symbole d\'├⌐l├⌐gance intemporelle.\r\n\r\nLe Bracelet Perles Assorti : Un bracelet ├⌐lastique en perles de nacre pour un port├⌐ confortable et chic au quotidien.\r\n\r\nLe Duo de Pendentifs Figuratifs : Un collier avec un pendentif &amp;amp;quot;C┼ôur ├ëtoil├⌐&amp;amp;quot; et un second avec un motif &amp;amp;quot;Papillon&amp;amp;quot; serti, parfaits pour exprimer la d├⌐licatesse.\r\n\r\nL\'Accessoire Cheveux &amp;amp;quot;├ëtoile Scintillante&amp;amp;quot; : Une barrette ├⌐l├⌐gante en forme d\'├⌐toile pour illuminer toutes les coiffures de soir├⌐e.\r\n\r\nLa Bague Solitaire Fine : Un anneau discret pr├⌐sent├⌐ dans son micro-├⌐crin, id├⌐al pour une attention d├⌐licate.\r\n\r\n├ëcrins &amp;amp;quot;Or Sable&amp;amp;quot; : Chaque pi├¿ce est pr├⌐sent├⌐e dans un bo├«tier cartonn├⌐ haut de gamme couleur sable dor├⌐ avec un int├⌐rieur en mousse noire profonde.',20000.00,15000,'1771977971_CD10.webp',NULL,NULL,9,100,'2026-02-25 00:06:11','Or',0,0),(48,'Full Ice Royal','L\'incarnation ultime du prestige et de l\'├⌐clat. Cette montre automatique est enti├¿rement recouverte de cristaux de haute qualit├⌐, offrant une brillance incomparable sous tous les angles.\r\n\r\nBo├«tier &amp;amp;amp;amp;amp;amp; Bracelet : Acier inoxydable int├⌐gralement pav├⌐ de strass.\r\n\r\nCadran : Noir profond avec index en diamants de synth├¿se et loupe de date ├á 3h.\r\n\r\nStyle : Haute joaillerie, id├⌐al pour les grands ├⌐v├⌐nements.',100000.00,80000,'1771982345_M6.webp',NULL,NULL,4,100,'2026-02-25 01:19:05','Argent',20,0),(49,'Bague en or','',300000.00,250000,'1772215420_69a1dc7c85e89.webp','1772252913_vid_69a26ef1c8d73.mp4','1772215420_3d_69a1dc7c8648f.glb',1,40,'2026-02-27 18:03:40','Or',0,0),(50,'Duo d\'Alliances ├ëclat Diamant','Un magnifique duo de bagues assorties en argent, serties de zircons cubiques scintillants pour c├⌐l├⌐brer votre union.',45000.00,0,'B8.webp',NULL,NULL,1,10,'2026-03-01 15:04:33','Argent 925',10,0),(51,'Parure de Bagues Boh├¿me Dor├⌐e','Un ensemble tendance de 9 bagues fines dor├⌐es, orn├⌐es de petits cristaux. Parfait pour accumuler et cr├⌐er un style boh├¿me chic.',15000.00,0,'B2.webp',NULL,NULL,1,10,'2026-03-01 15:04:33','Plaqu├⌐ Or',0,0),(52,'Bague Minimaliste Vague d\'Argent','Bague ouverte au design moderne et ├⌐pur├⌐. Sa forme asym├⌐trique apporte une touche contemporaine ├á votre main.',25000.00,0,'B5.webp',NULL,NULL,1,10,'2026-03-01 15:04:33','Argent Massif 925',0,0),(53,'Parure Nuptiale Majest├⌐ Or Jaune','Somptueux ensemble de fian├ºailles et alliance en or jaune, avec une pierre centrale brillante entour├⌐e d\'un pavage scintillant.',85000.00,0,'th.webp',NULL,NULL,1,10,'2026-03-01 15:04:33','Or Jaune 18k',15,0),(54,'Chevali├¿re Homme Griffe d\'Onyx','Chevali├¿re imposante pour homme, orn├⌐e d\'une pierre noire profonde style onyx, maintenue par un design audacieux en forme de griffes.',35000.00,0,'B10.webp',NULL,NULL,1,1,'2026-03-01 15:04:33','',0,0),(55,'Duo d\'Alliances Roi &amp; Reine','C├⌐l├⌐brez votre amour avec ce duo de bagues compl├⌐mentaires arborant un motif de couronne. Une d├⌐claration de royaut├⌐ mutuelle.',30000.00,0,'B9.webp',NULL,NULL,1,0,'2026-03-01 15:04:33','',0,0),(56,'Bague For├¬t Enchant├⌐e','Une cr├⌐ation artisanale au design organique rappelant des branches entrelac├⌐es. Pour un look unique et inspir├⌐ de la nature.',40000.00,0,'B3.webp',NULL,NULL,1,10,'2026-03-01 15:04:33','Argent 925',5,0),(57,'Solitaire ├ëternel Taille Coussin','Le grand classique des demandes en mariage. Un solitaire ├⌐l├⌐gant mettant en valeur une magnifique pierre de taille coussin.',75000.00,0,'B4.webp',NULL,NULL,1,10,'2026-03-01 15:04:33','Or Blanc 18k',20,0),(58,'Ensemble Alliance et Solitaire','L\'accord parfait. Un solitaire classique accompagn├⌐ de son alliance demi-tour pav├⌐e, pour un ├⌐clat absolu au doigt.',65000.00,0,'B6.webp',NULL,NULL,1,10,'2026-03-01 15:04:33','Argent 925 Rhodi├⌐',0,0),(59,'Alliances Bross├⌐es Forever','Un duo d\'alliances au fini bross├⌐ moderne, orn├⌐es d\'un d├⌐licat cristal et de gravures romantiques pour sceller votre promesse.',60000.00,0,'B7.jpg',NULL,NULL,1,100,'2026-03-01 15:04:33','Argent',10,0),(60,'montre ','',5678.00,0,'1772389166_69a4832ed3866.webp',NULL,NULL,4,1,'2026-03-01 18:19:35','Argent',20,0),(61,'Bracelet','',5000.00,2000,'1772468826_69a5ba5a76f20.webp',NULL,NULL,3,100,'2026-03-02 16:27:06','',0,0),(62,'Collier ','',10000.00,5000,'1772468878_69a5ba8e5014d.webp',NULL,NULL,2,100,'2026-03-02 16:27:58','',0,0);
/*!40000 ALTER TABLE `produits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `utilisateurs`
--

DROP TABLE IF EXISTS `utilisateurs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `utilisateurs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `telephone` varchar(20) NOT NULL,
  `mot_de_passe` varchar(255) DEFAULT NULL,
  `adresse` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `utilisateurs`
--

LOCK TABLES `utilisateurs` WRITE;
/*!40000 ALTER TABLE `utilisateurs` DISABLE KEYS */;
INSERT INTO `utilisateurs` VALUES (1,'Amina Diop','amina.diop@email.com','77 123 45 67',NULL,'Dakar, Plateau','2026-02-24 13:12:28'),(4,'dieylany',NULL,'780152522',NULL,'','2026-02-24 13:51:48'),(5,'fatima',NULL,'771621369',NULL,'','2026-02-24 14:13:01'),(6,'faty',NULL,'771621366',NULL,'','2026-02-25 13:51:29'),(7,'fallou','fallou@gmail.com','780152529',NULL,'','2026-02-25 14:11:24'),(10,'Binrta Ndiaye',NULL,'781234567',NULL,'Khar Yalla','2026-02-25 14:34:47'),(11,'Nogaye Diop',NULL,'781233322',NULL,'DAkare','2026-02-25 16:54:11'),(12,'Khadime Ndiaye','xecow78357@kaoing.com','777777777',NULL,'','2026-02-25 17:07:17'),(13,'dieylanya','bayedjilykhoumakhouma@gmail.com','780152522',NULL,'','2026-02-26 08:54:12'),(14,'dieylany',NULL,'780152511',NULL,'dakar','2026-02-27 12:53:55'),(15,'nogaye',NULL,'7777777',NULL,'','2026-02-27 13:48:42'),(19,'Mouhamed','momo@gmail.com','78252536',NULL,'','2026-02-27 20:22:48'),(20,'alioune badara gaye','aliounegaye799dd@gmail.com','778055911',NULL,'Dieupeul','2026-02-28 05:22:36'),(21,'rassoul','rassoul@gmail.com','','$2y$10$HV6IX11B31Qaabf3bxSzD.TQLq4CtOg2efi68wf0VgwXYc7iCoYXm','Touba','2026-02-28 10:24:08'),(22,'alimatou','alimatou@gmail.com','764582813','$2y$10$gKnXz1fCXGcvqFqIRfmjwe7pzebFeRG3wh.WpvrOrsyHye.USujSW','HLM','2026-02-28 10:48:00'),(23,'Galase','galasse@gmail.com','7899900088',NULL,'dakr','2026-03-01 09:40:40'),(24,'alladji','aladji@gmail.com','7811221122',NULL,'','2026-03-01 10:30:35'),(25,'modou','modou@gmail.com','78015252',NULL,'','2026-03-01 10:55:50'),(26,'1234','dieylany@gmail.com','13w',NULL,'ee','2026-03-01 10:58:32'),(27,'dieylany','princedieylany@icloud.com','+221780152522',NULL,'linb4','2026-03-01 11:08:23'),(28,'b','fatima@gmail.com','+221771621369',NULL,'','2026-03-01 11:08:49'),(29,'Mame ndiaye savon','bahcherno7@email.com','002202657822',NULL,'','2026-03-01 16:12:24'),(30,'Babs diaw','babacardiawj14@gmail.com','769458738',NULL,'Padoi','2026-03-06 04:03:23'),(31,'mouhamed bah','oumy@gmail.com','771828980',NULL,'Guedieway','2026-03-27 02:30:23'),(32,'fatima','fatiam@gmail.com','','$2y$10$fOucnUnLebFgJgjOQC2YlOyulckbV.jI8ktQ48.U5QkMYpR7jx6zi',NULL,'2026-05-05 10:26:29');
/*!40000 ALTER TABLE `utilisateurs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wishlist`
--

DROP TABLE IF EXISTS `wishlist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` varchar(100) NOT NULL,
  `produit_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=76 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wishlist`
--

LOCK TABLES `wishlist` WRITE;
/*!40000 ALTER TABLE `wishlist` DISABLE KEYS */;
INSERT INTO `wishlist` VALUES (12,'orrq3lp4ll4vcpom1c8qoa41i5',3,'2026-02-20 20:04:26'),(14,'orrq3lp4ll4vcpom1c8qoa41i5',1,'2026-02-20 20:04:30'),(15,'1riod40bg2uvi2ej00tf5bmmdm',5,'2026-02-21 12:20:10'),(16,'1riod40bg2uvi2ej00tf5bmmdm',6,'2026-02-21 12:20:29'),(17,'1riod40bg2uvi2ej00tf5bmmdm',7,'2026-02-21 14:01:14'),(18,'k8a5c6tcs1qaac7pm21eggphic',10,'2026-02-21 16:51:05'),(19,'mufjpofijkpmdjmtff1l7jv4i7',14,'2026-02-24 04:24:15'),(20,'mufjpofijkpmdjmtff1l7jv4i7',11,'2026-02-24 04:27:38'),(22,'j88qhkkm71hakehbv7btkashd6',15,'2026-02-24 06:05:57'),(23,'mufjpofijkpmdjmtff1l7jv4i7',31,'2026-02-24 07:39:29'),(24,'78045qpqqlr9ohmptmach4b679',48,'2026-02-26 09:21:46'),(30,'t52n1faj4sqiodn1taqmuqa14j',49,'2026-02-28 05:36:40'),(69,'egjbr06qp204v1anom5qbnrrum',46,'2026-02-28 17:02:01'),(70,'m2r9b3db5nt7s7hopi4h6cu9ub',20,'2026-03-01 16:39:48'),(72,'m2r9b3db5nt7s7hopi4h6cu9ub',22,'2026-03-01 17:02:52'),(73,'o5ec3avpnqg6litgbqg9m9en2r',61,'2026-03-27 04:07:40'),(75,'iiv05c7nn3ev8tec4v3qkclb3f',60,'2026-05-05 10:28:21');
/*!40000 ALTER TABLE `wishlist` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-12 15:55:27
