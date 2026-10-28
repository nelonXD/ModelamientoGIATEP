-- MySQL dump 10.13  Distrib 8.4.3, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: giatep_db
-- ------------------------------------------------------
-- Server version	8.4.3

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
-- Table structure for table `ai_analysis_runs`
--

DROP TABLE IF EXISTS `ai_analysis_runs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ai_analysis_runs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `investigation_id` bigint unsigned DEFAULT NULL,
  `ai_prompt_id` bigint unsigned DEFAULT NULL,
  `requested_by` bigint unsigned NOT NULL,
  `modelo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entrada` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `salida` json DEFAULT NULL,
  `estado` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `error` text COLLATE utf8mb4_unicode_ci,
  `duracion_ms` int unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ai_analysis_runs_investigation_id_foreign` (`investigation_id`),
  KEY `ai_analysis_runs_ai_prompt_id_foreign` (`ai_prompt_id`),
  KEY `ai_analysis_runs_requested_by_foreign` (`requested_by`),
  CONSTRAINT `ai_analysis_runs_ai_prompt_id_foreign` FOREIGN KEY (`ai_prompt_id`) REFERENCES `ai_prompts` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ai_analysis_runs_investigation_id_foreign` FOREIGN KEY (`investigation_id`) REFERENCES `investigations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ai_analysis_runs_requested_by_foreign` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ai_analysis_runs`
--

LOCK TABLES `ai_analysis_runs` WRITE;
/*!40000 ALTER TABLE `ai_analysis_runs` DISABLE KEYS */;
/*!40000 ALTER TABLE `ai_analysis_runs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ai_prompts`
--

DROP TABLE IF EXISTS `ai_prompts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ai_prompts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `version` smallint unsigned NOT NULL DEFAULT '1',
  `prompt_base` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `modelo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ai_prompts_nombre_unique` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ai_prompts`
--

LOCK TABLES `ai_prompts` WRITE;
/*!40000 ALTER TABLE `ai_prompts` DISABLE KEYS */;
INSERT INTO `ai_prompts` VALUES (1,'analisis-investigacion',3,'Aplica la metodología del Árbol de Causas con trazabilidad por fuente. Distingue eventos puntuales, condiciones permanentes, declaraciones, hechos corroborados y datos pendientes; declarado no significa verificado, y una cita no equivale por sí sola a corroboración. Produce por separado hechos cronológicos, vacíos de información y árbol retrospectivo desde la lesión. No incluyas falta de recuerdo o de inspección como hechos: regístralas como verificaciones pendientes. El árbol usa solo hechos revisados, vincula antecedente→consecuencia y clasifica cadena, conjunción (antecedentes independientes conjuntamente necesarios) o disyunción; valida cada vínculo con la prueba contrafactual. No confundas cronología con causalidad, no fuerces conectividad ni inventes hechos, evidencia, relaciones, diagnósticos, normas infringidas ni responsabilidades. Usa exclusivamente códigos de la matriz local entregada y solo si están respaldados por hechos. Propón medidas concretas y verificables ligadas a nodos causales, priorizando controles de mayor jerarquía. Explicita fuentes, contradicciones y vacíos; la salida es una propuesta editable que requiere revisión humana.','qwen2.5-7b-instruct',1,'2026-10-26 15:25:00','2026-10-26 15:25:00');
/*!40000 ALTER TABLE `ai_prompts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `arbol_causa_matriz`
--

DROP TABLE IF EXISTS `arbol_causa_matriz`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `arbol_causa_matriz` (
  `Id_Arbol_Causa` int NOT NULL,
  `Cod_Matriz` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`Id_Arbol_Causa`,`Cod_Matriz`),
  KEY `Cod_Matriz` (`Cod_Matriz`),
  CONSTRAINT `arbol_causa_matriz_ibfk_1` FOREIGN KEY (`Id_Arbol_Causa`) REFERENCES `arbol_causas` (`Id_Arbol_Causa`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `arbol_causa_matriz_ibfk_2` FOREIGN KEY (`Cod_Matriz`) REFERENCES `matriz_factores_causas` (`Cod_Matriz`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `arbol_causa_matriz`
--

LOCK TABLES `arbol_causa_matriz` WRITE;
/*!40000 ALTER TABLE `arbol_causa_matriz` DISABLE KEYS */;
/*!40000 ALTER TABLE `arbol_causa_matriz` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `arbol_causas`
--

DROP TABLE IF EXISTS `arbol_causas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `arbol_causas` (
  `Id_Arbol_Causa` int NOT NULL AUTO_INCREMENT,
  `Listado_Hechos` mediumtext COLLATE utf8mb4_unicode_ci,
  `Medidas_De_Mitigacion` mediumtext COLLATE utf8mb4_unicode_ci,
  `Texto_Final` mediumtext COLLATE utf8mb4_unicode_ci,
  `Diagrama_Arbol_Causa` json DEFAULT NULL,
  `Id_Prompt` int DEFAULT NULL,
  `Codigo_Investigacion` int NOT NULL,
  PRIMARY KEY (`Id_Arbol_Causa`),
  KEY `Id_Prompt` (`Id_Prompt`),
  KEY `Codigo_Investigacion` (`Codigo_Investigacion`),
  CONSTRAINT `arbol_causas_ibfk_1` FOREIGN KEY (`Id_Prompt`) REFERENCES `ia` (`Id_Prompt`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `arbol_causas_ibfk_2` FOREIGN KEY (`Codigo_Investigacion`) REFERENCES `investigacion` (`Codigo_Investigacion`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `arbol_causas`
--

LOCK TABLES `arbol_causas` WRITE;
/*!40000 ALTER TABLE `arbol_causas` DISABLE KEYS */;
/*!40000 ALTER TABLE `arbol_causas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `case_evidences`
--

DROP TABLE IF EXISTS `case_evidences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `case_evidences` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `occupational_case_id` bigint unsigned NOT NULL,
  `uploaded_by` bigint unsigned NOT NULL,
  `original_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `size` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `case_evidences_occupational_case_id_foreign` (`occupational_case_id`),
  KEY `case_evidences_uploaded_by_foreign` (`uploaded_by`),
  CONSTRAINT `case_evidences_occupational_case_id_foreign` FOREIGN KEY (`occupational_case_id`) REFERENCES `occupational_cases` (`id`) ON DELETE CASCADE,
  CONSTRAINT `case_evidences_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `case_evidences`
--

LOCK TABLES `case_evidences` WRITE;
/*!40000 ALTER TABLE `case_evidences` DISABLE KEYS */;
/*!40000 ALTER TABLE `case_evidences` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `caso`
--

DROP TABLE IF EXISTS `caso`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `caso` (
  `Id_Caso` int NOT NULL AUTO_INCREMENT,
  `Estado` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `RUT_CI_Trabajador` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Id_Dependencia` int NOT NULL,
  `Id_Clasificacion_Caso` int NOT NULL,
  `Clasificacion_Caso` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `RUT_Usuario` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Id_Empresa` int NOT NULL,
  PRIMARY KEY (`Id_Caso`),
  KEY `RUT_CI_Trabajador` (`RUT_CI_Trabajador`),
  KEY `Id_Dependencia` (`Id_Dependencia`),
  KEY `Id_Clasificacion_Caso` (`Id_Clasificacion_Caso`),
  KEY `RUT_Usuario` (`RUT_Usuario`),
  KEY `Id_Empresa` (`Id_Empresa`),
  CONSTRAINT `caso_ibfk_1` FOREIGN KEY (`RUT_CI_Trabajador`) REFERENCES `trabajador_accidentado_enfermo` (`RUT_CI_Trabajador`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `caso_ibfk_2` FOREIGN KEY (`Id_Dependencia`) REFERENCES `dependencia` (`Id_Dependencia`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `caso_ibfk_3` FOREIGN KEY (`Id_Clasificacion_Caso`) REFERENCES `tipo_caso` (`Id_Clasificacion_Caso`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `caso_ibfk_4` FOREIGN KEY (`RUT_Usuario`) REFERENCES `usuario` (`Rut_Usuario`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `caso_ibfk_5` FOREIGN KEY (`Id_Empresa`) REFERENCES `empresa_empleador` (`Id_Empresa`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `caso`
--

LOCK TABLES `caso` WRITE;
/*!40000 ALTER TABLE `caso` DISABLE KEYS */;
/*!40000 ALTER TABLE `caso` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `caso_noley`
--

DROP TABLE IF EXISTS `caso_noley`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `caso_noley` (
  `Id_Caso` int NOT NULL,
  `Fecha_No_Ley` datetime NOT NULL,
  `Motivo_No_Ley` text COLLATE utf8mb4_unicode_ci,
  `Rut_Usuario` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`Id_Caso`),
  KEY `Rut_Usuario` (`Rut_Usuario`),
  CONSTRAINT `caso_noley_ibfk_1` FOREIGN KEY (`Id_Caso`) REFERENCES `caso` (`Id_Caso`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `caso_noley_ibfk_2` FOREIGN KEY (`Rut_Usuario`) REFERENCES `usuario` (`Rut_Usuario`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `caso_noley`
--

LOCK TABLES `caso_noley` WRITE;
/*!40000 ALTER TABLE `caso_noley` DISABLE KEYS */;
/*!40000 ALTER TABLE `caso_noley` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `catalog_options`
--

DROP TABLE IF EXISTS `catalog_options`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `catalog_options` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `category` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort_order` smallint unsigned NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `catalog_options_category_value_unique` (`category`,`value`),
  KEY `catalog_options_category_is_active_sort_order_index` (`category`,`is_active`,`sort_order`),
  KEY `catalog_options_is_active_index` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=111 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `catalog_options`
--

LOCK TABLES `catalog_options` WRITE;
/*!40000 ALTER TABLE `catalog_options` DISABLE KEYS */;
INSERT INTO `catalog_options` VALUES (1,'case_type','Accidente de trabajo','Accidente de trabajo',1,1,'2026-10-26 15:24:59','2026-10-26 15:24:59'),(2,'case_type','Accidente de trayecto','Accidente de trayecto',2,1,'2026-10-26 15:24:59','2026-10-26 15:24:59'),(3,'case_type','Enfermedad profesional','Enfermedad profesional',3,1,'2026-10-26 15:24:59','2026-10-26 15:24:59'),(4,'case_type','Accidente grave','Accidente grave',4,1,'2026-10-26 15:24:59','2026-10-26 15:24:59'),(5,'case_type','Accidente fatal','Accidente fatal',5,1,'2026-10-26 15:24:59','2026-10-26 15:24:59'),(6,'case_type','Accidente de trabajo sin días perdidos','Accidente de trabajo sin días perdidos',6,1,'2026-10-26 15:24:59','2026-10-26 15:24:59'),(7,'case_type','Incidente o suceso peligroso','Incidente o suceso peligroso',7,1,'2026-10-26 15:24:59','2026-10-26 15:24:59'),(8,'case_type','Accidente o enfermedad común','Accidente o enfermedad común',8,1,'2026-10-26 15:24:59','2026-10-26 15:24:59'),(9,'case_status','Borrador','Borrador',1,1,'2026-10-26 15:24:59','2026-10-26 15:24:59'),(10,'case_status','Registrado','Registrado',2,1,'2026-10-26 15:24:59','2026-10-26 15:24:59'),(11,'case_status','En investigación','En investigación',3,1,'2026-10-26 15:24:59','2026-10-26 15:24:59'),(12,'case_status','Con observaciones','Con observaciones',4,1,'2026-10-26 15:24:59','2026-10-26 15:24:59'),(13,'case_status','Seguimiento de medidas','Seguimiento de medidas',5,1,'2026-10-26 15:24:59','2026-10-26 15:24:59'),(14,'case_status','Cerrado','Cerrado',6,1,'2026-10-26 15:24:59','2026-10-26 15:24:59'),(15,'commune','Alto Biobío','Alto Biobío',1,1,'2026-10-26 15:24:59','2026-10-26 15:24:59'),(16,'commune','Antuco','Antuco',2,1,'2026-10-26 15:24:59','2026-10-26 15:24:59'),(17,'commune','Arauco','Arauco',3,1,'2026-10-26 15:24:59','2026-10-26 15:24:59'),(18,'commune','Cabrero','Cabrero',4,1,'2026-10-26 15:24:59','2026-10-26 15:24:59'),(19,'commune','Cañete','Cañete',5,1,'2026-10-26 15:24:59','2026-10-26 15:24:59'),(20,'commune','Chiguayante','Chiguayante',6,1,'2026-10-26 15:24:59','2026-10-26 15:24:59'),(21,'commune','Concepción','Concepción',7,1,'2026-10-26 15:24:59','2026-10-26 15:24:59'),(22,'commune','Contulmo','Contulmo',8,1,'2026-10-26 15:24:59','2026-10-26 15:24:59'),(23,'commune','Coronel','Coronel',9,1,'2026-10-26 15:24:59','2026-10-26 15:24:59'),(24,'commune','Curanilahue','Curanilahue',10,1,'2026-10-26 15:24:59','2026-10-26 15:24:59'),(25,'commune','Florida','Florida',11,1,'2026-10-26 15:24:59','2026-10-26 15:24:59'),(26,'commune','Hualpén','Hualpén',12,1,'2026-10-26 15:24:59','2026-10-26 15:24:59'),(27,'commune','Hualqui','Hualqui',13,1,'2026-10-26 15:24:59','2026-10-26 15:24:59'),(28,'commune','Laja','Laja',14,1,'2026-10-26 15:24:59','2026-10-26 15:24:59'),(29,'commune','Lebu','Lebu',15,1,'2026-10-26 15:24:59','2026-10-26 15:24:59'),(30,'commune','Los Álamos','Los Álamos',16,1,'2026-10-26 15:24:59','2026-10-26 15:24:59'),(31,'commune','Los Ángeles','Los Ángeles',17,1,'2026-10-26 15:24:59','2026-10-26 15:24:59'),(32,'commune','Lota','Lota',18,1,'2026-10-26 15:24:59','2026-10-26 15:24:59'),(33,'commune','Mulchén','Mulchén',19,1,'2026-10-26 15:24:59','2026-10-26 15:24:59'),(34,'commune','Nacimiento','Nacimiento',20,1,'2026-10-26 15:24:59','2026-10-26 15:24:59'),(35,'commune','Negrete','Negrete',21,1,'2026-10-26 15:24:59','2026-10-26 15:24:59'),(36,'commune','Penco','Penco',22,1,'2026-10-26 15:24:59','2026-10-26 15:24:59'),(37,'commune','Quilaco','Quilaco',23,1,'2026-10-26 15:24:59','2026-10-26 15:24:59'),(38,'commune','Quilleco','Quilleco',24,1,'2026-10-26 15:24:59','2026-10-26 15:24:59'),(39,'commune','San Pedro de la Paz','San Pedro de la Paz',25,1,'2026-10-26 15:24:59','2026-10-26 15:24:59'),(40,'commune','San Rosendo','San Rosendo',26,1,'2026-10-26 15:24:59','2026-10-26 15:24:59'),(41,'commune','Santa Bárbara','Santa Bárbara',27,1,'2026-10-26 15:24:59','2026-10-26 15:24:59'),(42,'commune','Santa Juana','Santa Juana',28,1,'2026-10-26 15:24:59','2026-10-26 15:24:59'),(43,'commune','Talcahuano','Talcahuano',29,1,'2026-10-26 15:24:59','2026-10-26 15:24:59'),(44,'commune','Tirúa','Tirúa',30,1,'2026-10-26 15:24:59','2026-10-26 15:24:59'),(45,'commune','Tomé','Tomé',31,1,'2026-10-26 15:24:59','2026-10-26 15:24:59'),(46,'commune','Tucapel','Tucapel',32,1,'2026-10-26 15:24:59','2026-10-26 15:24:59'),(47,'company_type','Principal','Principal',1,1,'2026-10-26 15:24:59','2026-10-26 15:24:59'),(48,'company_type','Contratista','Contratista',2,1,'2026-10-26 15:24:59','2026-10-26 15:24:59'),(49,'company_type','Subcontratista','Subcontratista',3,1,'2026-10-26 15:24:59','2026-10-26 15:24:59'),(50,'company_type','Servicios transitorios','Servicios transitorios',4,1,'2026-10-26 15:24:59','2026-10-26 15:24:59'),(51,'administrator','ACHS','ACHS',1,1,'2026-10-26 15:24:59','2026-10-26 15:24:59'),(52,'administrator','IST','IST',2,1,'2026-10-26 15:24:59','2026-10-26 15:24:59'),(53,'administrator','Mutual de Seguridad','Mutual de Seguridad',3,1,'2026-10-26 15:24:59','2026-10-26 15:24:59'),(54,'administrator','ISL','ISL',4,1,'2026-10-26 15:24:59','2026-10-26 15:24:59'),(55,'worker_sex','Mujer','Mujer',1,1,'2026-10-26 15:24:59','2026-10-26 15:24:59'),(56,'worker_sex','Hombre','Hombre',2,1,'2026-10-26 15:24:59','2026-10-26 15:24:59'),(57,'worker_sex','Otro','Otro',3,1,'2026-10-26 15:24:59','2026-10-26 15:24:59'),(58,'yes_no','Sí','Sí',1,1,'2026-10-26 15:24:59','2026-10-26 15:24:59'),(59,'yes_no','No','No',2,1,'2026-10-26 15:24:59','2026-10-26 15:24:59'),(60,'nationality','Chilena','Chilena',1,1,'2026-10-26 15:24:59','2026-10-26 15:24:59'),(61,'nationality','Argentina','Argentina',2,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(62,'nationality','Boliviana','Boliviana',3,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(63,'nationality','Brasileña','Brasileña',4,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(64,'nationality','Colombiana','Colombiana',5,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(65,'nationality','Ecuatoriana','Ecuatoriana',6,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(66,'nationality','Paraguaya','Paraguaya',7,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(67,'nationality','Peruana','Peruana',8,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(68,'nationality','Uruguaya','Uruguaya',9,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(69,'nationality','Venezolana','Venezolana',10,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(70,'nationality','Haitiana','Haitiana',11,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(71,'nationality','Dominicana','Dominicana',12,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(72,'nationality','Cubana','Cubana',13,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(73,'nationality','Mexicana','Mexicana',14,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(74,'nationality','Otra nacionalidad','Otra nacionalidad',15,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(75,'indigenous_people','Ninguno','Ninguno',1,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(76,'indigenous_people','Mapuche','Mapuche',2,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(77,'indigenous_people','Aimara','Aimara',3,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(78,'indigenous_people','Rapanui','Rapanui',4,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(79,'indigenous_people','Atacameño','Atacameño',5,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(80,'indigenous_people','Quechua','Quechua',6,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(81,'indigenous_people','Colla','Colla',7,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(82,'indigenous_people','Diaguita','Diaguita',8,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(83,'indigenous_people','Alacalufe','Alacalufe',9,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(84,'indigenous_people','Yamana (Yagán)','Yamana (Yagán)',10,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(85,'indigenous_people','Otro','Otro',11,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(86,'contract_type','Indefinido','Indefinido',1,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(87,'contract_type','Plazo fijo','Plazo fijo',2,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(88,'contract_type','Honorarios','Honorarios',3,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(89,'contract_type','Contrata','Contrata',4,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(90,'contract_type','Obra o faena','Obra o faena',5,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(91,'contract_type','Reemplazo','Reemplazo',6,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(92,'income_type','Remuneración fija','Remuneración fija',1,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(93,'income_type','Remuneración variable','Remuneración variable',2,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(94,'income_type','Honorarios','Honorarios',3,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(95,'occupational_category','Trabajador dependiente','Trabajador dependiente',1,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(96,'occupational_category','Trabajador independiente','Trabajador independiente',2,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(97,'occupational_category','Familiar no remunerado','Familiar no remunerado',3,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(98,'occupational_category','Trabajador voluntario','Trabajador voluntario',4,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(99,'work_schedule','Diurna','Diurna',1,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(100,'work_schedule','Turnos rotativos','Turnos rotativos',2,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(101,'work_schedule','Nocturna','Nocturna',3,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(102,'work_schedule','Otra','Otra',4,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(103,'route_type','Domicilio - Trabajo','Domicilio - Trabajo',1,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(104,'route_type','Trabajo - Domicilio','Trabajo - Domicilio',2,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(105,'route_type','Entre dos trabajos','Entre dos trabajos',3,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(106,'proof_type','Parte de Carabineros','Parte de Carabineros',1,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(107,'proof_type','Declaración','Declaración',2,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(108,'proof_type','Testigos','Testigos',3,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(109,'proof_type','Fotografías','Fotografías',4,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(110,'proof_type','Otro','Otro',5,1,'2026-10-26 15:25:00','2026-10-26 15:25:00');
/*!40000 ALTER TABLE `catalog_options` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `causal_nodes`
--

DROP TABLE IF EXISTS `causal_nodes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `causal_nodes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `investigation_id` bigint unsigned NOT NULL,
  `node_type` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `position` smallint unsigned NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `ai_generated` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `causal_nodes_investigation_id_foreign` (`investigation_id`),
  CONSTRAINT `causal_nodes_investigation_id_foreign` FOREIGN KEY (`investigation_id`) REFERENCES `investigations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `causal_nodes`
--

LOCK TABLES `causal_nodes` WRITE;
/*!40000 ALTER TABLE `causal_nodes` DISABLE KEYS */;
/*!40000 ALTER TABLE `causal_nodes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cause_factors`
--

DROP TABLE IF EXISTS `cause_factors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cause_factors` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `categoria` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subcategoria` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `codigo` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subcodigo` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `codigo_efectivo` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `glosa` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `explicacion_glosa` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `pagina_pdf` smallint unsigned DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cause_factors_codigo_efectivo_unique` (`codigo_efectivo`),
  KEY `cause_factors_codigo_index` (`codigo`),
  KEY `cause_factors_subcodigo_index` (`subcodigo`),
  KEY `cause_factors_activo_index` (`activo`),
  FULLTEXT KEY `cause_factors_glosa_explicacion_glosa_fulltext` (`glosa`,`explicacion_glosa`)
) ENGINE=InnoDB AUTO_INCREMENT=223 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cause_factors`
--

LOCK TABLES `cause_factors` WRITE;
/*!40000 ALTER TABLE `cause_factors` DISABLE KEYS */;
INSERT INTO `cause_factors` VALUES (1,'1. Gestión preventiva de la empresa','11. Gestión de la prevención (que influyeron en el accidente)','1101',NULL,'1101','Inexistencia de un programa de prevención de riesgos laborales en la empresa o incongruencia de este con los riesgos presentes.','La empresa no evidencia un programa de prevención de riesgos laborales. O bien el programa no considera los riesgos presentes en la empresa.',73,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(2,'1. Gestión preventiva de la empresa','11. Gestión de la prevención (que influyeron en el accidente)','1102',NULL,'1102','No se han identificado los peligros y los riesgos no están evaluados.','La empresa no ha identificado los peligros y evaluado los riesgos porque no dispone de procedimientos formalizados en un documento que pueda ser auditado o fiscalizado.',73,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(3,'1. Gestión preventiva de la empresa','11. Gestión de la prevención (que influyeron en el accidente)','1103',NULL,'1103','Deficiencias en la organización preventiva de la empresa respecto a los instrumentos de prevención establecidos en la normativa.','Según corresponda: No haber constituido o estar sin funcionamiento el Departamentos de Prevención de Riesgos Laborales y los Comités Paritarios. No disponer de: Reglamentos Internos, Sistema de Gestión de SST, Reglamento Especial de Empresas Contratistas, y Registro de Antecedentes. No cumplimiento de las obligaciones de los reglamentos de instrumentos preventivos (DS 40, DS 54, DS 76).',73,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(4,'1. Gestión preventiva de la empresa','11. Gestión de la prevención (que influyeron en el accidente)','1104',NULL,'1104','Inexistencia de procedimientos de evaluación y auditoría en el Sistema de Gestión de Riesgos Laborales.','La empresa no cuenta con sistema de gestión desarrollado en documentos comprobables o bien no tiene procedimientos de auditoría interna para evaluar su gestión respecto a riesgos laborales.',73,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(5,'1. Gestión preventiva de la empresa','11. Gestión de la prevención (que influyeron en el accidente)','1105',NULL,'1105','Falta de coordinación entre empresas sobre procedimientos de trabajo seguro (PTS).','Cuando entre 2 o más empresas no han acordado un PTS y existen diversas formas de proceder /No hay evidencia de la coordinación.',74,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(6,'1. Gestión preventiva de la empresa','11. Gestión de la prevención (que influyeron en el accidente)','1106',NULL,'1106','Inexistencia o deficiencia en la coordinación entre trabajadores para la realización del trabajo. Deficiencias del trabajo en equipo.','El informe indica en forma explícita que hay mala coordinación entre los trabajadores, que se detectan ausencia o fallas en el trabajo de equipo.',74,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(7,'1. Gestión preventiva de la empresa','11. Gestión de la prevención (que influyeron en el accidente)','1107',NULL,'1107','Inexistencia o deficiencias de Procedimiento de Trabajo Seguro (PTS) en la o las empresa (s) del accidente.','La o las empresas involucradas no cuenta(n) con PTS, o este presenta características que lo hacen insuficiente, incorrecto o poco claro.',74,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(8,'1. Gestión preventiva de la empresa','11. Gestión de la prevención (que influyeron en el accidente)','1108',NULL,'1108','Sistema inexistente, inadecuado o mal aplicado de asignación de tareas.','Las tareas se asignan sin una planificación, sin asignación de recursos materiales, equipos, herramientas, sin explicar al trabajador el método de trabajo correcto.',74,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(9,'1. Gestión preventiva de la empresa','11. Gestión de la prevención (que influyeron en el accidente)','1109',NULL,'1109','Los trabajadores no participan en la gestión preventiva de la empresa.','Los reportes de investigación no evidencian la participación de los trabajadores ya sea en consulta con los trabajadores o no se considera la opinión respecto a temas de seguridad.',74,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(10,'1. Gestión preventiva de la empresa','11. Gestión de la prevención (que influyeron en el accidente)','1110',NULL,'1110','No considerar las características de los trabajadores para la realización de la tarea o en función de los riesgos.','El empleador no considera las capacidades físicas y psicológicas de los trabajadores para la asignación de tareas. No hay evidencias de realización de exámenes preocupacionales y/o ocupacionales que avalen que el trabajador puede realizar una tarea que requiere evaluación de características físicas, dado el riesgo existente. Ejemplos. Exámenes para exposición a altitud, exámenes a conductores, requisitos de visión, etc.',74,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(11,'1. Gestión preventiva de la empresa','11. Gestión de la prevención (que influyeron en el accidente)','1111',NULL,'1111','Política de compras inexistente o inadecuada desde el punto de vista de la prevención para la Seguridad.','No existe una política establecida que contemple las implicancias de las adquisiciones respecto a temas de seguridad, o la política de adquisiciones existe, pero no incorpora los aspectos relacionados con la salud y seguridad en el trabajo.',75,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(12,'1. Gestión preventiva de la empresa','11. Gestión de la prevención (que influyeron en el accidente)','1112',NULL,'1112','No existe plan de emergencia o el plan es inadecuado.','La empresa no cuenta con un plan de emergencias ante situaciones de riesgo graves ya sea por situaciones derivadas del trabajo, catástrofes naturales, o cualquier otra condición que ponga en riesgo inminente la vida y salud de los trabajadores. Incluye inexistencia o defectos de plan de emergencias en tránsito de vehículos.',75,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(13,'1. Gestión preventiva de la empresa','11. Gestión de la prevención (que influyeron en el accidente)','1113',NULL,'1113','Inexistencia de procedimiento de investigación de incidentes.','No se evidencia un procedimiento establecido por la empresa para encontrar las causas y evitar la repetición de los incidentes.',75,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(14,'1. Gestión preventiva de la empresa','11. Gestión de la prevención (que influyeron en el accidente)','1199',NULL,'1199','Otras causas relativas a la gestión de la prevención. Especificar:','Otras causas relacionadas con sistema de gestión en SST no incluidas en ítems anteriores.',75,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(15,'1. Gestión preventiva de la empresa','12. Actividades preventivas','1201',NULL,'1201','Falta de control del cumplimiento del Plan de seguridad y salud en el trabajo para evitar accidentes.','La empresa no controla el cumplimiento de su Plan de SST, o no se revisa periódicamente o no se modifica en relación a los cambios que sufre el proceso productivo.',75,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(16,'1. Gestión preventiva de la empresa','12. Actividades preventivas','1202',NULL,'1202','No existe programa de mantenimiento preventivo de espacios de trabajo y máquinas, o este es inadecuado.','Incluye mantenimiento programado y revisión diaria o aleatoria de todas las partes relacionadas con seguridad de máquinas, vehículos, equipos y herramientas.',75,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(17,'1. Gestión preventiva de la empresa','12. Actividades preventivas',NULL,'1202a','1202a','Mantenimiento preventivo inexistente o inadecuado del lugar de trabajo, vehículos, máquinas y herramientas.','No se evidencia con documento la realización de mantención. Incluye mantenimiento programado y revisión diaria o aleatoria de todas las partes relacionadas con seguridad de máquinas, vehículos, equipos y herramientas.',76,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(18,'1. Gestión preventiva de la empresa','12. Actividades preventivas',NULL,'1202b','1202b','Mantenimiento preventivo inexistente o inadecuado de vías interiores.','Incluye mantenimiento programado y revisión diaria o aleatoria de todas las vías peatonales o vehiculares al interior de la empresa.',76,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(19,'1. Gestión preventiva de la empresa','12. Actividades preventivas','1203',NULL,'1203','No identificación del/ los peligros específicos y evaluación de los riesgos que han materializado el accidente.','La empresa no evidencia Análisis Seguro del Trabajo (AST) previo a ejecutar las tareas en la que ocurre el accidente.',76,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(20,'1. Gestión preventiva de la empresa','12. Actividades preventivas','1204',NULL,'1204','Falta o deficiencias en los controles de salud.','No hay registro que evidencie el ingreso de trabajadores a Vigilancia de Salud.',76,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(21,'1. Gestión preventiva de la empresa','12. Actividades preventivas','1205',NULL,'1205','Ausencia/deficiencias de permisos y/o procedimientos de trabajo en intervenciones peligrosas.','Los permisos o procedimientos no existen o no cumplen con su propósito en tareas de alto riesgo tales como: trabajo en altura, tareas con exposición a circuitos eléctricos, excavaciones, conducción en condiciones extremas, transporte de cargas peligrosas, etc.',76,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(22,'1. Gestión preventiva de la empresa','12. Actividades preventivas','1206',NULL,'1206','Deficiente gestión en la selección y control en la entrega y uso de Elementos de Protección Personal, EPP.','La empresa no selecciona adecuadamente los EPP respecto al riesgo a proteger o bien no controla que los trabajadores sepan usarlos y lo usen correctamente. Considerar Elementos de Protección personal tales como: cascos, zapatos de seguridad dieléctricos, arneses, chalecos reflectantes, chalecos salvavidas, paracaídas, equipos protección incendios, sistemas de bloqueos, etc. que correspondan al riesgo.',76,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(23,'1. Gestión preventiva de la empresa','12. Actividades preventivas',NULL,'1206a','1206a','Falta de EPP.','No se ha suministrado EPP.',77,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(24,'1. Gestión preventiva de la empresa','12. Actividades preventivas',NULL,'1206b','1206b','EPP no adecuados al riesgo que materializó el accidente.','Los EPP suministrados no son adecuados al riesgo del accidente producido.',77,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(25,'1. Gestión preventiva de la empresa','12. Actividades preventivas',NULL,'1206c','1206c','EPP en malas condiciones por falta de mantención o renovación..','Los EPP suministrados se encuentran en malas condi- ciones y no protegen bien a los trabajadores debido a que no están sometidos a un plan de control, mantenimiento y renovación.',77,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(26,'1. Gestión preventiva de la empresa','12. Actividades preventivas',NULL,'1206d','1206d','No hay supervisión para el uso de elementos de Protección personal.','La empresa no dispone de un sistema de supervisión de uso de elementos de protec- ción personal.',77,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(27,'1. Gestión preventiva de la empresa','12. Actividades preventivas','1207',NULL,'1207','Ausencia o falla en procedimientos de control y supervisión en el puesto de trabajo.','Informe constata que no existen procedimientos para asegurar que controles operacionales estén en funcionamiento o que estos existen pero las personas responsables no los aplicaron o los aplicaron de manera incorrecta.',77,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(28,'1. Gestión preventiva de la empresa','12. Actividades preventivas','1299',NULL,'1299','Otras causas relativas a las actividades preventivas Especificar:','Otras causas relacionadas con actividades preventivas no incluidas en los ítems anteriores.',77,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(29,'2. Factores de la organización del trabajo','21. Jornadas de trabajo y descansos','2101',NULL,'2101','Exceder la jornada máxima legal diaria y/o semanal.','En el informe de accidente se constata que el trabajador afectado había excedido el límite de jornada diaria o semanal, legalmente establecida en el último período. (semanal o último período de turno de jornada especial).',77,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(30,'2. Factores de la organización del trabajo','21. Jornadas de trabajo y descansos','2102',NULL,'2102','Exceder el máximo de horas de conducción continuas.','En el informe de accidente se constata que el conductor del vehículo había excedido el tiempo máximo de conducción. Especificar si inmediatamente antes del accidente o en días previos.',77,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(31,'2. Factores de la organización del trabajo','21. Jornadas de trabajo y descansos','2103',NULL,'2103','El descanso, dentro de la jornada y al término de la jornada, es inferior al legal pactado entre las partes.','En el informe se constata que el trabajador afectado no tenía pausas dentro de su jornada laboral, en el período previo al accidente. Se otorga un descanso diario y/o semanal, inferior al legal.',78,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(32,'2. Factores de la organización del trabajo','21. Jornadas de trabajo y descansos','2104',NULL,'2104','Ausencia de pausas programadas en el caso de trabajo repetitivo de alta frecuencia.','Aplicables a trabajo repetitivo, a trabajo monótono o por sobrecarga.',78,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(33,'2. Factores de la organización del trabajo','21. Jornadas de trabajo y descansos','2199',NULL,'2199','Otras causas asociadas a jornadas y sistemas de turno Especificar:','Usar esta opción si se indica otra causa asociada a jornada laboral o a sistema de turnos. Especificar según como esté escrito en el informe.',78,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(34,'2. Factores de la organización del trabajo','22. Carga mental y física en el trabajo','2201',NULL,'2201','Ritmo de trabajo elevado por sobre las capacidades normales.','El informe indica en forma explícita que se trabajaba a alta velocidad o que se trabajaba bajo exigencia de cumplir tiempos.',78,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(35,'2. Factores de la organización del trabajo','22. Carga mental y física en el trabajo','2202',NULL,'2202','Trabajo monótono o rutinario sin aplicar medidas para evitar efecto nocivo.','El informe indica que el trabajo realizado por el trabajador afectado era monótono, falto de variedad.',78,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(36,'2. Factores de la organización del trabajo','22. Carga mental y física en el trabajo','2203',NULL,'2203','Trabajo solitario sin medidas de asistencia.','El informe indica que el trabajador se encontraba en un puesto de trabajo regularmente sin compañeros de trabajo en el mismo espacio.',78,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(37,'2. Factores de la organización del trabajo','22. Carga mental y física en el trabajo','2204',NULL,'2204','Sobrecarga mental en tareas de supervisión de personas o equipos.','Sobre carga mental en la supervisión de personas o equipos más allá de sus capacidades. Ejemplo supervisión de tareas en varias secciones, control de equipos automatizados en tiempo escaso, controles de entrada y salida de bodegas, etc.',78,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(38,'2. Factores de la organización del trabajo','22. Carga mental y física en el trabajo','2205',NULL,'2205','Organizar el trabajo sin tomar en cuenta condiciones meteorológicas adversas.','El informe indica que no se han considerado las condiciones meteorológicas en la asignación de tareas, de los turnos y horarios, en la conformación de los equipos u otros aspectos de la organización del trabajo, lo que ha implicado mayor carga física o mental para la ejecución de la tarea por exceso de calor, frío, lluvia, etc.',79,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(39,'2. Factores de la organización del trabajo','22. Carga mental y física en el trabajo','2206',NULL,'2206','Ganancia de un incentivo económico por hacer el trabajo más rápido.','El informe indica que el trabajador afectado percibía un pago de incentivo por mayor cantidad de piezas, productos, resultados x unidad de tiempo.',79,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(40,'2. Factores de la organización del trabajo','22. Carga mental y física en el trabajo','2207',NULL,'2207','Ganancia de un beneficio económico por no tener accidentes.','El informe indica que el trabajador afectado tenía estipulado en su contrato un pago de incentivo por no accidentarse o por no ausentarse por razones de salud.',79,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(41,'2. Factores de la organización del trabajo','22. Carga mental y física en el trabajo','2212',NULL,'2212','Ritmo de trabajo impuesto.','El trabajo se realiza a una velocidad impuesta que ha significado aumentar el riesgo de accidente.',79,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(42,'2. Factores de la organización del trabajo','22. Carga mental y física en el trabajo','2213',NULL,'2213','Elevado nivel de atención.','Se refiere a trabajos que requieren altos niveles de atención personal y no se han tomado las medidas adecuadas para las pausas necesarias o los resguardos ante la pérdida de atención que materializa el accidente.',79,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(43,'2. Factores de la organización del trabajo','22. Carga mental y física en el trabajo','2214',NULL,'2214','Falta de autonomía en la toma de decisiones respecto de la tarea.','El trabajador no participa de la decisión de cómo ejecutar su tarea aumentando el riesgo.',79,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(44,'2. Factores de la organización del trabajo','22. Carga mental y física en el trabajo','2299',NULL,'2299','Otras causas asociadas a métodos de trabajo (Tales como utilizar métodos, técnicas o materiales inadecuados). Especificar:','Informe constata otra causa relacionada con los procedimientos de trabajo (no con los procedimientos de seguridad).',79,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(45,'2. Factores de la organización del trabajo','23. Formación, capacitación e información','2301',NULL,'2301','Trabajador no cuenta con capacitación o no ha sido informado sobre los riesgos a los que se encuentra expuesto, las medidas preventivas y los métodos de trabajo correcto.','Informe constata que la empresa o el departamento de prevención o el comité paritario no habían entregado capacitación al trabajador afectado, en las materias de seguridad y prevención específicas a la tarea que realizaba. No existen procedimientos para informar y capacitar a los trabajadores en temas de seguridad, o los procedimientos no contemplan alguno de los temas mencionados y/o la formación entregada no permite asegurar que el trabajador conoce sus riesgos, medidas preventivas y métodos correcto de trabajo. Art. 21 DS 40.',80,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(46,'2. Factores de la organización del trabajo','23. Formación, capacitación e información',NULL,'2301a','2301a','Conductor(a) sin capacitación y/o adiestramiento insuficiente para conducir el vehículo del accidente.','Para el caso de conductores de vehículos solamente, dado que es una causa importante de accidentes respecto a maquinaria pesada.',80,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(47,'2. Factores de la organización del trabajo','23. Formación, capacitación e información','2302',NULL,'2302','Operador de máquinas, equipos o herramientas sin la capacitación o adiestramiento suficiente para usar la máquina, equipo o herramienta implicada.','Informe constata que el trabajador no tenía la capacitación o adiestramiento suficientes requeridos para usar la máquina, equipo o herramienta.',80,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(48,'2. Factores de la organización del trabajo','23. Formación, capacitación e información','2303',NULL,'2303','Inexistencia o deficiencias en información sobre cómo actuar en condiciones críticas del trabajo.','Actuaciones en emergencias, respuesta a agresiones o conflictos sociales, respuestas seguras ante peligros manifiestos. Ejemplos no hay procedimientos escritos, el trabajador no estaba capacitado en respuesta a emergencias, no hay señalética de actuación en condiciones críticas tales como: ?en caso de incendio presione botón de alarma?.',80,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(49,'2. Factores de la organización del trabajo','23. Formación, capacitación e información','2304',NULL,'2304','El trabajador no es capacitado en uso de equipos y elementos de protección personal.','No se ha entrenado a los trabajadores sobre el correcto uso, mantenimiento y almacenamiento de EPP.',81,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(50,'2. Factores de la organización del trabajo','23. Formación, capacitación e información','2309',NULL,'2309','Otras causas relativas a capacitación, formación e información Especificar:','Otras causas relacionadas con la capacitación, formación o información no comprendidas en ítems anteriores.',81,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(51,'2. Factores de la organización del trabajo','24. Clima organizacional','2401',NULL,'2401','Abuso o maltrato.','Informe constata que existen situaciones de abuso o maltrato en el lugar de trabajo o empresa donde ocurrió el accidente (denunciadas, informadas por testigos u observadas por el investigador del accidente).',81,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(52,'2. Factores de la organización del trabajo','24. Clima organizacional',NULL,'2401a','2401a','De jefaturas.','El maltrato que se constata es ejercido por autoridades, directivos, mandos superiores.',81,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(53,'2. Factores de la organización del trabajo','24. Clima organizacional',NULL,'2401b','2401b','De compañeros(as) de trabajo.','El maltrato que se constata se ejerce entre colegas o pares de trabajo.',81,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(54,'2. Factores de la organización del trabajo','24. Clima organizacional','2402',NULL,'2402','Excesiva verticalidad del mando, no hay mecanismos para recoger sugerencias, quejas o propuestas.','Informe constata que la estructura jerárquica vertical no tiene mecanismos para recoger sugerencias, quejas o propuestas.',81,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(55,'2. Factores de la organización del trabajo','24. Clima organizacional','2403',NULL,'2403','Falta de participación en la toma de decisiones sobre su tarea.','Informe constata que los trabajadores no participan en la toma de decisiones sobre la ejecución de sus tareas.',81,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(56,'2. Factores de la organización del trabajo','24. Clima organizacional','2404',NULL,'2404','Comunicaciones inexistentes, insuficientes o inadecuadas desde los mandos.','Informe constata que no ha existido comunicación entre mando superior y trabajador afectado en período previo al accidente, o que esta fue insuficiente o inadecuada. Informe constata que el trabajador pidió instrucciones y no las recibió.',81,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(57,'2. Factores de la organización del trabajo','24. Clima organizacional','2499',NULL,'2499','Otras causas relativas a clima laboral Especificar:','Otras causas relacionadas con clima laboral no incluidas en ítems anteriores.',81,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(58,'3. Factores individuales','31. Condiciones de salud','3101',NULL,'3101','Fatiga del (de la) trabajador(a).','Se evidencia estado de disminución de la alerta, atención y del rendimiento físico y mental ya sea por examen objetivo o por declaración de testigos.',82,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(59,'3. Factores individuales','31. Condiciones de salud','3102',NULL,'3102','Enfermedad.','Alteración de salud física, mental o social. Contempla patologías agudas, crónicas descompensadas, emergencias/urgencias médicas. Evidenciadas en la investigación.',82,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(60,'3. Factores individuales','31. Condiciones de salud','3103',NULL,'3103','Trastornos del sueño.','Conjunto de patologías relacionadas a dificultades para dormir, especialmente para iniciar y mantener el sueño. Evidenciada mediante exámenes o declaraciones.',82,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(61,'3. Factores individuales','31. Condiciones de salud','3104',NULL,'3104','Consumo de fármacos.','Referido al consumo de medicamentos indicados por profesional de salud. Contempla la automedicación de fármacos no recetados.',82,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(62,'3. Factores individuales','31. Condiciones de salud','3105',NULL,'3105','Sensibilidad o alergias a sustancias.','Reacción alérgica de tipo inmediata asociada a alimentos, aditivos y colorantes, medicamentos, inhalantes, insectos, u otros.',82,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(63,'3. Factores individuales','31. Condiciones de salud','3106',NULL,'3106','Deficiencia o limitación de los sentidos (audición/equilibrio, visión).','Alteración de los órganos de los sentidos, que limitan la capacidad del individuo.',82,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(64,'3. Factores individuales','31. Condiciones de salud','3107',NULL,'3107','Incapacidad para realizar la tarea.','Insuficiencia o inhabilidad para realizar la tarea.',82,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(65,'3. Factores individuales','31. Condiciones de salud',NULL,'3107a','3107a','Incapacidad física.','Insuficiencia o inhabilidad física para realizar la tarea.',82,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(66,'3. Factores individuales','31. Condiciones de salud',NULL,'3107b','3107b','Incapacidad mental.','Insuficiencia o inhabilidad mental para realizar la tarea.',82,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(67,'3. Factores individuales','31. Condiciones de salud','3199',NULL,'3199','Otras condiciones relativas a salud. Especificar:','Otras condiciones relativas a salud no especificadas anteriormente.',82,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(68,'3. Factores individuales','32. Factores del comportamiento','3201',NULL,'3201','Consumo de alcohol.','Contempla consumo de alcohol detectado por algún medio de detección de consumo o presencia de alcohol en la sangre durante la ejecución de la tarea.',83,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(69,'3. Factores individuales','32. Factores del comportamiento',NULL,'3201a','3201a','Conducción de vehículo o realización de la tarea bajo la influencia del alcohol o estado de ebriedad.','Contempla conducción de vehículos o ejecución de tareas estando bajo efectos agudos del consumo de alcohol.',83,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(70,'3. Factores individuales','32. Factores del comportamiento',NULL,'3201b','3201b','Peatón bajo la influencia del alcohol.','Contempla la circulación de peatones estando bajo efectos agudos del consumo de alcohol.',83,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(71,'3. Factores individuales','32. Factores del comportamiento',NULL,'3201c','3201c','Pasajero u ocupante bajo la influencia del alcohol.','Contempla consumo de alcohol o presencia de alcohol en la sangre en pasajeros u ocupantes de maquinarias/vehículos.',83,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(72,'3. Factores individuales','32. Factores del comportamiento',NULL,'3201d','3201d','Trabajar bajo la influencia del alcohol.','Contempla el desarrollo de las actividades laborales estando bajo la influencia del alcohol.',83,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(73,'3. Factores individuales','32. Factores del comportamiento','3202',NULL,'3202','Consumo drogas ilícitas.','Contempla consumo o presencia de drogas ilícitas durante la ejecución de la tarea, detectadas en fluidos o tejidos corporales.',83,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(74,'3. Factores individuales','32. Factores del comportamiento',NULL,'3202a','3202a','Conducción de vehículo o realización de la tarea bajo la influencia de drogas ilícitas.','Implica la conducción de vehículos y la operación de maquinaria bajo los efectos agudos del consumo de drogas ilícitas.',83,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(75,'3. Factores individuales','32. Factores del comportamiento',NULL,'3202b','3202b','Peatón bajo la influencia de drogas ilícitas.','Implica la circulación de peatones bajo los efectos agudos del consumo de drogas ilícitas.',83,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(76,'3. Factores individuales','32. Factores del comportamiento',NULL,'3202c','3202c','Pasajero u ocupante bajo la influencia de drogas ilícitas.','Implica la realización de tareas por parte de pasajeros u ocupantes, bajo los efectos agudos del consumo de drogas ilícitas.',83,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(77,'3. Factores individuales','32. Factores del comportamiento',NULL,'3202d','3202d','Trabajar bajo la influencia de las drogas.','Contempla el desarrollo de las actividades laborales estando bajo la influencia de las drogas.',84,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(78,'4. Factores asociados al medio','41. Condiciones estructurales y deficiencias de lugares de trabajo','4101',NULL,'4101','Camino en mal estado.','Pavimento mal estado, baches, camino resbaladizo por acción antropogénica o cualquier condición que pueda provocar accidentes en desplazamiento de vehículos, máquinas o personas.',84,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(79,'4. Factores asociados al medio','41. Condiciones estructurales y deficiencias de lugares de trabajo','4102',NULL,'4102','Diseño de la vía o camino inadecuado.','Inconsistencia del trazado, existencia de rampas y pendientes prolongadas, insuficiencia de peralte en curvas, taludes mal calculados, etc.',84,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(80,'4. Factores asociados al medio','41. Condiciones estructurales y deficiencias de lugares de trabajo','4103',NULL,'4103','Iluminación deficiente de la vía.','Condición constatada en la investigación que intervino en el accidente.',84,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(81,'4. Factores asociados al medio','41. Condiciones estructurales y deficiencias de lugares de trabajo','4104',NULL,'4104','Inexistencia de barreras (o en mal estado) para contención vehículos, paso de animales o peatones.','El camino de circulación no dispone de barreras que pudieran evitar accidentes o están en mal estado.',84,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(82,'4. Factores asociados al medio','41. Condiciones estructurales y deficiencias de lugares de trabajo','4105',NULL,'4105','Presencia de animales sueltos en la vía.','Presencia de animales debido a descuidos de un tercero o porque el diseño de la vía no evita el paso de animales de envergadura que pueden provocar accidentes.',84,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(83,'4. Factores asociados al medio','41. Condiciones estructurales y deficiencias de lugares de trabajo','4106',NULL,'4106','Condiciones naturales peligrosas del terreno.','Ejemplo: caminos con excesiva pendiente, riesgos de derrumbes, asociada a una condición geográfica.',84,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(84,'4. Factores asociados al medio','41. Condiciones estructurales y deficiencias de lugares de trabajo','4107',NULL,'4107','Inexistencia de berma.','Aplicable a vías de circulación de vehículos tales como carreteras o caminos.',84,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(85,'4. Factores asociados al medio','41. Condiciones estructurales y deficiencias de lugares de trabajo','4110',NULL,'4110','Vías de circulación de personas en lugar de trabajo, estrechas o poco iluminadas.','Se refiere a lugares de desplazamiento interno en el lugar de trabajo por donde transitan personas.',85,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(86,'4. Factores asociados al medio','41. Condiciones estructurales y deficiencias de lugares de trabajo','4111',NULL,'4111','Obstrucción de vías de circulación de personas.','Objetos presentes en la vía, materiales acumulados por derrumbes o caída de cargas.',85,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(87,'4. Factores asociados al medio','41. Condiciones estructurales y deficiencias de lugares de trabajo','4112',NULL,'4112','Vías de circulación de personas con baches o peligro de colapso.','Vías con perforaciones o con posibilidad de desplome.',85,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(88,'4. Factores asociados al medio','41. Condiciones estructurales y deficiencias de lugares de trabajo','4113',NULL,'4113','Falta de protección colectiva contra caídas en vías de circulación.','Ausencia de barandas, pasamanos, antideslizantes, y otros.',85,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(89,'4. Factores asociados al medio','41. Condiciones estructurales y deficiencias de lugares de trabajo','4114',NULL,'4114','Vías con desniveles peligrosos.','Desniveles que pueden provocar caídas peligrosas, volcamientos de vehículos, caída de carga etc.',85,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(90,'4. Factores asociados al medio','41. Condiciones estructurales y deficiencias de lugares de trabajo','4115',NULL,'4115','Estructuras del lugar de trabajo mal construidas.','Construcciones que no se ajustan a las exigencias de seguridad establecidas en la normativa o no cumplen criterios técnicos para la realización del trabajo.',85,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(91,'4. Factores asociados al medio','41. Condiciones estructurales y deficiencias de lugares de trabajo','4116',NULL,'4116','Estructuras mal mantenidas con probabilidad de colapso.','Condiciones riesgosas de las estructuras debido a falta de mantenimiento.',85,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(92,'4. Factores asociados al medio','41. Condiciones estructurales y deficiencias de lugares de trabajo','4199',NULL,'4199','Otras causas relativas a condiciones estructurales de la vía. Especificar:','Otras condiciones estructurales de la vía no consideradas en este grupo.',85,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(93,'4. Factores asociados al medio','42. Condiciones climáticas y geográficas adversas','4201',NULL,'4201','Circulación por camino resbaladizo provocado por condiciones climáticas.','Condiciones climáticas que afectan la seguridad en el desplazamiento de personas o vehículos.',85,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(94,'4. Factores asociados al medio','42. Condiciones climáticas y geográficas adversas',NULL,'4201a','4201a','Camino resbaladizo por llovizna/lluvia.','Riesgo de pérdida de control del vehículo por lluvia que afecta adherencia al camino.',85,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(95,'4. Factores asociados al medio','42. Condiciones climáticas y geográficas adversas',NULL,'4201b','4201b','Camino resbaladizo por nieve.','Riesgo de pérdida de control del vehículo por nieve que afecta adherencia al camino.',85,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(96,'4. Factores asociados al medio','42. Condiciones climáticas y geográficas adversas',NULL,'4201c','4201c','Camino resbaladizo por formación de hielo.','Riesgo de pérdida de control de vehículo por formación de hielo que afecta adherencia al camino.',86,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(97,'4. Factores asociados al medio','42. Condiciones climáticas y geográficas adversas','4202',NULL,'4202','Baja visibilidad por neblina, lluvia, nieve, oscuridad, baja luminosidad diurna, u otras relativas a la geografía.','Riesgo de pérdida de control del vehículo o colisión debido a problemas de visibilidad por condiciones climáticas.',86,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(98,'4. Factores asociados al medio','42. Condiciones climáticas y geográficas adversas','4203',NULL,'4203','Conducción de vehículos en condiciones de altura geográfica.','Sólo aplicable a conducción de vehículo en condiciones de altura geográfica que pueda afectar su condición física (sobre 3.000msnm).',86,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(99,'4. Factores asociados al medio','42. Condiciones climáticas y geográficas adversas','4204',NULL,'4204','Condiciones de alta velocidad de viento.','Viento a velocidad que puede mover objetos o personas y puede provocar accidentes por desplazamiento de vehículo o golpes por objetos volando.',86,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(100,'4. Factores asociados al medio','42. Condiciones climáticas y geográficas adversas','4205',NULL,'4205','Condiciones de turbulencia extrema navegación en aire o agua.','Turbulencia de agua o aire en navegación aérea o marítima.',86,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(101,'4. Factores asociados al medio','42. Condiciones climáticas y geográficas adversas','4206',NULL,'4206','Exposición a temperaturas extremas por razones climáticas.','Temperaturas del medio natural que pueden causar congelamiento en poco tiempo o golpe de calor.',86,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(102,'4. Factores asociados al medio','42. Condiciones climáticas y geográficas adversas','4207',NULL,'4207','Falta de protección anti rayos en zonas expuestas a caídas de rayos.','Aplicable a aquellas zonas de recurrencia de tempestades con caída de rayos.',86,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(103,'4. Factores asociados al medio','42. Condiciones climáticas y geográficas adversas','4208',NULL,'4208','Atrapamiento en aluviones.','Riesgo de atrapamiento de personas por aluviones sin haber tomado medidas de precaución.',86,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(104,'4. Factores asociados al medio','42. Condiciones climáticas y geográficas adversas','4299',NULL,'4299','Otras causas relativas a condiciones climáticas o geográficas. Especificar:','Causas relativas a condiciones climáticas o geográficas adversas no consideradas.',86,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(105,'4. Factores asociados al medio','43. Señalización','4301',NULL,'4301','Señalización vial inexistente.','Señales necesarias no instaladas o retiradas.',86,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(106,'4. Factores asociados al medio','43. Señalización','4302',NULL,'4302','Señalización vial en mal estado.','Señalización deteriorada que no permite percatarse del peligro.',87,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(107,'4. Factores asociados al medio','43. Señalización','4303',NULL,'4303','Señalización vial confusa.','Señalización no normalizada que puede confundir al conductor o peatón sobre la conducta a seguir.',87,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(108,'4. Factores asociados al medio','43. Señalización','4304',NULL,'4304','Señalización vial no visible.','Señalización obstruida por objetos, árboles o cualquier medio que impida verla con claridad.',87,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(109,'4. Factores asociados al medio','43. Señalización','4305',NULL,'4305','Líneas poco visibles en calzada.','Líneas de demarcación de la calzada inexistentes o poco visibles por deterioro.',87,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(110,'4. Factores asociados al medio','43. Señalización','4306',NULL,'4306','Inexistencia de paso peatonal.','Inexistencia de paso peatonal donde debiera existir por el paso habitual de peatones.',87,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(111,'4. Factores asociados al medio','43. Señalización','4307',NULL,'4307','Baches o desperfectos viales temporales no señalizados.','Baches o desperfectos de la vía producidos por cualquier situación (natural o antropogénica) sin barreras o señalización.',87,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(112,'4. Factores asociados al medio','43. Señalización','4308',NULL,'4308','Señalización en vías internas, estacionamientos o lugares de la faena, inexistente, inadecuada o en mal estado.','Se refiere a vías internas y espacios de circulación de vehículos controladas por la empresa.',87,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(113,'4. Factores asociados al medio','43. Señalización','4309',NULL,'4309','Señales de peligro inexistentes o no visibles en el lugar de trabajo.','Contempla aquella señalética que es necesaria para advertir a los trabajadores de peligros (máquinas, energía, caídas, golpes).',87,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(114,'4. Factores asociados al medio','43. Señalización','4310',NULL,'4310','Señalización inexistente en zonas delimitadas por razones de seguridad.','Espacios delimitados, pero no señalizados en el lugar de trabajo.',87,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(115,'4. Factores asociados al medio','43. Señalización','4311',NULL,'4311','Vías de evacuación no señalizada.','Ausencia de señalética que indique lugares donde se encuentran las vías de evacuación.',87,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(116,'4. Factores asociados al medio','43. Señalización','4312',NULL,'4312','Señales inexistentes o inadecuadas sobre uso obligatorio de EPP.','Aplicable a aquellos lugares donde el uso de determinado EPP es obligatorio.',87,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(117,'4. Factores asociados al medio','43. Señalización','4313',NULL,'4313','Señales inadecuadas de cualquier tipo','Incluye confusión idiomática, imagen, características de visibilidad.',87,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(118,'4. Factores asociados al medio','43. Señalización','4399',NULL,'4399','Otras causas relativas a señalización. Especificar:','Otras causas de señalización (vial o de lugar de trabajo) no consideradas en los ítems anteriores.',88,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(119,'4. Factores asociados al medio','44. Condiciones del medio ambiente natural','4401',NULL,'4401','Exposición a hiperbaría.','Trabajos en condiciones de presiones mayores a la atmosférica medido como presión atmosférica absoluta (ATA). Ej. Buzos profesionales en todas sus categorías, trabajadores/as de cámaras hiperbáricas, instructores/ as profesionales de buceo deportivo u otros.',88,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(120,'4. Factores asociados al medio','44. Condiciones del medio ambiente natural','4402',NULL,'4402','Exposición a hipobaría.','Trabajos realizados en condiciones de presión atmosférica menores a 1 ATM por sobre los 3000 msnm.',88,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(121,'4. Factores asociados al medio','44. Condiciones del medio ambiente natural','4403',NULL,'4403','Deslumbramiento por luz natural.','Deslumbramiento que puede causar accidentes debido a iluminación directa o indirecta del sol.',88,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(122,'4. Factores asociados al medio','44. Condiciones del medio ambiente natural','4404',NULL,'4404','Exposición a frío o calor del medio ambiente natural.','Condiciones de temperaturas extrema, humedad y velocidad del viento en el medio natural que pueden provocar hipotermia o golpe de calor.',88,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(123,'4. Factores asociados al medio','44. Condiciones del medio ambiente natural','4499',NULL,'4499','Otras causas relativas a alteraciones del medio ambiente. Especificar:','Causas relativas a otras condiciones del medio natural que puedan afectar la salud de forma aguda.',88,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(124,'4. Factores asociados al medio','45. Condiciones de riesgo en el espacio de trabajo','4501',NULL,'4501','Exposición a calor extremo generado por fuentes antropogénicas del lugar de trabajo.','Condiciones que pueden provocar golpe de calor (alta radiación infrarroja de hornos o superficies muy caliente, aire sobrecalentado).',88,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(125,'4. Factores asociados al medio','45. Condiciones de riesgo en el espacio de trabajo','4502',NULL,'4502','Ruido excesivo que afecta la realización de la tarea o detección de peligros.','Ruidos que puedan alterar las respuestas ante peligros (ruidos que enmascaran alertas, distraen foco de atención, ruidos de impacto por sobre lo permisible u otros).',88,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(126,'4. Factores asociados al medio','45. Condiciones de riesgo en el espacio de trabajo','4503',NULL,'4503','Superficies de trabajo inestables, frágiles o resbaladizas.','Trabajos realizados en plataformas y superficies móviles (barcos, plataformas en movimiento, etc.) o bien estructuras frágiles que se puedan romper (ej. techos o plataformas con debilidad estructural). O superficies resbaladizas.',89,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(127,'4. Factores asociados al medio','45. Condiciones de riesgo en el espacio de trabajo','4504',NULL,'4504','Ausencia o deficiencia de protecciones colectivas frente a caída de personas.','Por ejemplo, barreras, barandas, mallas anti caídas, etc. Especialmente caídas de altura.',89,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(128,'4. Factores asociados al medio','45. Condiciones de riesgo en el espacio de trabajo','4505',NULL,'4505','Inexistencia de barreras ante excavaciones o huecos en pisos.','Barreras en pisos para evitar que personas caigan a huecos perforados o naturales.',89,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(129,'4. Factores asociados al medio','45. Condiciones de riesgo en el espacio de trabajo','4506',NULL,'4506','Taludes mal construídos o inestables por cualquier condición. Ausencia o deficiencias en entibaciones.','Mal diseño o deficiencia estructural de taludes que pueda provocar derrumbes. Incluye ausencia o deficiencia de entibaciones en las excavaciones.',89,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(130,'4. Factores asociados al medio','45. Condiciones de riesgo en el espacio de trabajo','4507',NULL,'4507','Mecanismos y espacios de subida de personas inseguros (escaleras, escalas y ramplas).','Escaleras no seguras por anchura, tipos y espaciados de peldaños, sistemas mecánicos de elevación de personas.',89,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(131,'4. Factores asociados al medio','45. Condiciones de riesgo en el espacio de trabajo','4508',NULL,'4508','Inexistencia u obstrucción de vías de evacuación.','Inexistencia de vías de evacuación por error de diseño, obstrucción o problemas de acceso a las mismas (puertas cerradas con llave).',89,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(132,'4. Factores asociados al medio','45. Condiciones de riesgo en el espacio de trabajo','4509',NULL,'4509','Inexistencia, insuficiencia o ineficacia de aislamiento y protecciones en espacios altamente peligrosos.','Espacios de trabajo con peligros inherentes tales como atmósferas explosivas, riesgos de incendio por vapores combustibles, espacios confinados con déficit de oxígeno etc.',89,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(133,'4. Factores asociados al medio','45. Condiciones de riesgo en el espacio de trabajo','4510',NULL,'4510','Ausencia de sistemas de seguridad para trabajos en espacios confinados.','Falta de sistema de detección de atmósferas peligrosas y sistemas de rescate, instalados.',89,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(134,'4. Factores asociados al medio','45. Condiciones de riesgo en el espacio de trabajo','4511',NULL,'4511','No delimitación de zonas de trabajo peligrosas.','No existencia de delimitación de circulación de personas cerca de vehículos, maniobras o trabajo cerca de máquinas peligrosas.',90,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(135,'4. Factores asociados al medio','45. Condiciones de riesgo en el espacio de trabajo','4512',NULL,'4512','Pisos resbaladizos por acción humana tales como derrame de líquidos o sólidos.','Condición de resbaladizo de carácter permanente o circunstancial por derrame de líquidos o sólidos.',90,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(136,'4. Factores asociados al medio','45. Condiciones de riesgo en el espacio de trabajo','4513',NULL,'4513','Diseño ergonómico inadecuado del puesto de trabajo que lo tornan peligroso.','Exigencia de posturas inadecuadas o con peligros de caída, atrapamiento u otro.',90,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(137,'4. Factores asociados al medio','45. Condiciones de riesgo en el espacio de trabajo','4514',NULL,'4514','Deficiencia en orden y limpieza.','Factores del orden y limpieza que determina accidentes laborales como golpes con o contra, caídas, cortes, etc.',90,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(138,'4. Factores asociados al medio','45. Condiciones de riesgo en el espacio de trabajo','4515',NULL,'4515','Sistema de ventilación deficiente o en mal estado.','Aplicable a lugares en que se suministra aire por ventilación natural o forzada.',90,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(139,'4. Factores asociados al medio','45. Condiciones de riesgo en el espacio de trabajo','4516',NULL,'4516','Vía de circulación habitual ubicada en zona peligrosa.','Vía ubicada en zona de derrumbe, caída de objetos, circulación de vehículos.',90,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(140,'4. Factores asociados al medio','45. Condiciones de riesgo en el espacio de trabajo','4517',NULL,'4517','Deficiencia en la iluminación en el puesto de trabajo.','Iluminación deficiente por debajo de los niveles recomendados para la realización de las tareas.',90,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(141,'4. Factores asociados al medio','45. Condiciones de riesgo en el espacio de trabajo','4518',NULL,'4518','Estructuras interiores inestables con riesgos de desplomes y aplastamientos.','Incluye socavones con riesgo de desprendimientos de planchones, murallas interiores con riesgo de caídas.',90,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(142,'4. Factores asociados al medio','45. Condiciones de riesgo en el espacio de trabajo','4519',NULL,'4519','Equipos de izaje inadecuados o en mal estado.','Aplica a lugares de trabajo con izaje mecánico de carga.',90,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(143,'4. Factores asociados al medio','45. Condiciones de riesgo en el espacio de trabajo','4520',NULL,'4520','Apilamiento de carga inseguro.','Sistema de apilamiento inseguro ya sea por diseño del apilamiento o por condiciones circunstanciales.',90,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(144,'4. Factores asociados al medio','45. Condiciones de riesgo en el espacio de trabajo','4521',NULL,'4521','Exceso de carga en el sistema de izaje o transporte.','Carga de mayor tonelaje que lo que permite el equipo o el procedimiento seguro.',90,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(145,'4. Factores asociados al medio','45. Condiciones de riesgo en el espacio de trabajo','4522',NULL,'4522','Manejo manual de carga en condiciones inseguras.','Carga con mayor peso que el recomendado, estabilidad de la carga manual, entre otras. Incluye levantar carga en forma manual por sobre los límites permisibles.',91,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(146,'4. Factores asociados al medio','45. Condiciones de riesgo en el espacio de trabajo','4523',NULL,'4523','Vehículo con sobrecarga o mal estibado.','Se evidencia que en la actividad preventiva no hubo control de sobrecarga o disposición de la carga en el vehículo de transporte.',91,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(147,'4. Factores asociados al medio','45. Condiciones de riesgo en el espacio de trabajo','4524',NULL,'4524','Sistemas eléctricos de la instalación no protegidos o mal manejados.','Puntos vivos no protegidos, ausencia de dispositivos de seguridad como interruptores térmicos y diferenciales, no puesta a tierra.',91,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(148,'4. Factores asociados al medio','45. Condiciones de riesgo en el espacio de trabajo','4525',NULL,'4525','Sistemas que utilizan grandes presiones no bien protegidos o deficientemente manejados.','Sistemas de gases a presión con exceso de presión o con fallas tanto en el sistema de control como de materiales.',91,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(149,'4. Factores asociados al medio','45. Condiciones de riesgo en el espacio de trabajo','4526',NULL,'4526','Fuentes radiactivas no bien protegidas.','Problemas en el diseño de los bunkers de almacenamiento, o en los blindajes de las fuentes.',91,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(150,'4. Factores asociados al medio','45. Condiciones de riesgo en el espacio de trabajo','4527',NULL,'4527','Calderas y generadores de vapor sin mantención preventiva o con ausencia de sistema de protección.','Incumplimiento de las normas sobre seguridad de los sistemas de generación de vapor.',91,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(151,'4. Factores asociados al medio','45. Condiciones de riesgo en el espacio de trabajo','4528',NULL,'4528','No disponer de sistemas de bloqueo de energía cuando sea necesario.','No se dispone de sistemas de bloqueo para mantenimiento, reparaciones, cortes de producción y otros que requieran asegurar el corte de energía de cualquier tipo. Ej., ausencia de sistema de bloqueo con candados especiales.',91,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(152,'4. Factores asociados al medio','45. Condiciones de riesgo en el espacio de trabajo','4529',NULL,'4529','Sistemas de detección y extinción de incendios insuficientes o ineficaces.','Sistemas de detección y extinción inexistente o deficiente cuando es obligatorio.',91,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(153,'4. Factores asociados al medio','45. Condiciones de riesgo en el espacio de trabajo','4530',NULL,'4530','Almacenamiento de sustancias peligrosas no reglamentario.','Cualquier incumplimiento al reglamento de almacenamiento que involucre riesgos de accidentes del trabajo.',92,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(154,'4. Factores asociados al medio','45. Condiciones de riesgo en el espacio de trabajo','4531',NULL,'4531','Inexistencia de instalaciones anti-explosivas en atmósferas potencialmente explosivas.','Se refiere a los dispositivos de seguridad anti-chispas para evitar explosiones.',92,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(155,'4. Factores asociados al medio','45. Condiciones de riesgo en el espacio de trabajo','4532',NULL,'4532','Sistemas de alarma inexistente o deficiente.','Sistemas de alarmas inexistente o deficiente cuando es obligatorio.',92,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(156,'4. Factores asociados al medio','45. Condiciones de riesgo en el espacio de trabajo','4533',NULL,'4533','Ausencia de duchas de seguridad.','No disposición de duchas de seguridad en instalaciones que lo requieren.',92,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(157,'4. Factores asociados al medio','45. Condiciones de riesgo en el espacio de trabajo','4534',NULL,'4534','Ausencia o deficiencia de aislación de superficies calientes.','Incendios provocados por falta de aislación de superficies calientes.',92,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(158,'4. Factores asociados al medio','45. Condiciones de riesgo en el espacio de trabajo','4599',NULL,'4599','Otras causas relativas a condiciones de riesgo en el espacio de trabajo. Especificar:','Factores del medio ambiente de trabajo no considerados en los ítems anteriores.',92,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(159,'4. Factores asociados al medio','46. Factores de riesgos químicos y biológicos','4601',NULL,'4601','Ataque de animales.','Producido en el lugar de trabajo o con ocasión del trabajo.',92,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(160,'4. Factores asociados al medio','46. Factores de riesgos químicos y biológicos','4602',NULL,'4602','Picaduras de insectos que cause reacción alérgica o intoxicación.','Picaduras o mordeduras de insectos que puedan causar una reacción aguda grave o la muerte.',92,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(161,'4. Factores asociados al medio','46. Factores de riesgos químicos y biológicos','4603',NULL,'4603','Contaminación con material biológico altamente peligroso.','Manipulación o contacto con sustancias biológicas (virus, hongos, bacterias, parásitos).',92,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(162,'4. Factores asociados al medio','46. Factores de riesgos químicos y biológicos','4604',NULL,'4604','Manipulación o presencia de sustancias químicas peligrosas en el ambiente de trabajo.','Se refiere a contaminantes químicos presentes en ambientes de trabajo propio o ambiente en que los trabajadores se exponen por ocasión del trabajo. Incluye situaciones críticas que pueden ocasionar un efecto agudo debido a manipulación de sustancias o contacto por cualquier razón. Se incluye deficiencias en el control de derrames de sustancias químicas peligrosas.',92,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(163,'4. Factores asociados al medio','46. Factores de riesgos químicos y biológicos','4605',NULL,'4605','Manejo de sustancias radiactivas.','Factor asociado a la manipulación o contacto con sustancias radiactivas que pueden generar contaminación o irradiación. Considerar en este código solo fuentes radiactivas abiertas.',93,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(164,'4. Factores asociados al medio','46. Factores de riesgos químicos y biológicos','4606',NULL,'4606','Programa de control de plagas y vectores inexistente o caducado.','No existe programa de control de plagas indicado por autoridad sanitaria o dicho programa ha perdido la vigencia.',93,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(165,'4. Factores asociados al medio','46. Factores de riesgos químicos y biológicos','4699',NULL,'4699','Otras causas relativas a factores químicos y biológicos. Especificar:','Factores químicos y biológicos no considerados en el listado anterior.',93,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(166,'5. Factores tecnológicos','51. Características de los vehículos','5101',NULL,'5101','Vehículo inadecuado para el transporte de carga común.','Vehículo no diseñado para el transporte de carga y utilizado como tal.',93,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(167,'5. Factores tecnológicos','51. Características de los vehículos','5102',NULL,'5102','Vehículo inadecuado para el transporte de sustancias peligrosas.','Vehículo que transporta cargas peligrosas que no cumple la normativa de este tipo de transporte.',93,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(168,'5. Factores tecnológicos','51. Características de los vehículos','5103',NULL,'5103','Buses urbanos o interurbanos que no disponen de condiciones de seguridad según exigencias legales.','Buses de transporte de pasajeros que no cumplen con la normativa vigente en materia de seguridad de conductor y pasajeros. Ejemplo Exigencias del DS 175 /2006 MTT modificado por DS 158/2013.',93,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(169,'5. Factores tecnológicos','51. Características de los vehículos','5104',NULL,'5104','Vehículo no destinado al transporte de pasajeros.','Transporte de pasajeros en vehículo no destinado para ello (Ej. Tractores, grúas horquilla, acoplados, etc.).',93,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(170,'5. Factores tecnológicos','51. Características de los vehículos','5105',NULL,'5105','Embarcación no apta para navegar en esas aguas.','Embarcación no autorizada por autoridad competente para navegar en esas aguas.',93,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(171,'5. Factores tecnológicos','51. Características de los vehículos','5106',NULL,'5106','Carencia de elementos de protección ante emergencias (triángulos, extintores, otros sistemas).','No dispone de elementos para avisar, señalar o enfrentar emergencias según corresponda al tipo de trabajo.',93,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(172,'5. Factores tecnológicos','51. Características de los vehículos','5107',NULL,'5107','Ausencia o deficiencia de alarmas de retroceso.','Vehículos de carga que no disponen de alarma de retroceso o bien está no operativa.',94,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(173,'5. Factores tecnológicos','51. Características de los vehículos','5199',NULL,'5199','Otras causas relacionadas con características de los vehículos (terrestres, acuáticos o aéreos). Especificar:','Otras características de los vehículos que provocan riesgos para la seguridad de las personas no contempladas en ítems anteriores.',94,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(174,'5. Factores tecnológicos','52. Sistemas de protección activa de vehículos.','5201',NULL,'5201','Sistema de frenado inseguro (sin circuito independiente, sin ABS).','Elementos que se incorporan al vehículo para evitar la ocurrencia de accidentes.',94,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(175,'5. Factores tecnológicos','52. Sistemas de protección activa de vehículos.','5202',NULL,'5202','Sistema de dirección inseguro.','No cuenta con sistema de dirección asistida o se encuentra defectuosa.',94,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(176,'5. Factores tecnológicos','52. Sistemas de protección activa de vehículos.','5203',NULL,'5203','Sistema de suspensión sin estabilización.','Sistema de suspensión en mal estado o deficiente.',94,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(177,'5. Factores tecnológicos','52. Sistemas de protección activa de vehículos.','5204',NULL,'5204','Neumáticos inadecuados para la vía de circulación.','Los neumáticos no cumplen con la normativa vigente (revisión técnica).',94,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(178,'5. Factores tecnológicos','52. Sistemas de protección activa de vehículos.','5205',NULL,'5205','Iluminación delantera inadecuada.','Iluminación delantera no cumple con estándar normativo.',94,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(179,'5. Factores tecnológicos','52. Sistemas de protección activa de vehículos.','5206',NULL,'5206','Iluminación trasera inadecuada','Iluminación trasera no cumple con estándar normativo.',94,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(180,'5. Factores tecnológicos','52. Sistemas de protección activa de vehículos.','5207',NULL,'5207','Sin sistema de control de estabilidad virajes.','Sistema electrónico de estabilización del vehículo principalmente para curvas o emergencias.',94,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(181,'5. Factores tecnológicos','52. Sistemas de protección activa de vehículos.','5299',NULL,'5299','Otras causas relacionadas con protección activa de vehículos. Especificar:','Otras causas relativas a elementos de protección activa no especificados.',94,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(182,'5. Factores tecnológicos','53. Sistema de protección pasiva de vehículos','5301',NULL,'5301','Ausencia de cinturón seguridad.','No disponer de cinturón de seguridad o que esté en mal estado.',94,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(183,'5. Factores tecnológicos','53. Sistema de protección pasiva de vehículos','5302',NULL,'5302','Chasis y carrocería inadecuados para transporte de personas.','Estructura inadecuada del vehículo para prevenir mayor daño a las personas cuando ocurren accidentes.',94,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(184,'5. Factores tecnológicos','53. Sistema de protección pasiva de vehículos','5303',NULL,'5303','Ausencia de sistema de airbags.','No dispone de sistema de airbags para contención de pasajeros ante choques.',94,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(185,'5. Factores tecnológicos','53. Sistema de protección pasiva de vehículos','5304',NULL,'5304','Ausencia de apoya-cabezas.','Butacas de conductor y acompañantes sin apoya cabeza.',95,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(186,'5. Factores tecnológicos','53. Sistema de protección pasiva de vehículos','5305',NULL,'5305','Ausencia de barra antivuelco normalizada en vehículo (cuando corresponda).','Barra antivuelco en vehículos de carga liviana inexistente o no construida bajo estándares de calidad.',95,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(187,'5. Factores tecnológicos','53. Sistema de protección pasiva de vehículos','5306',NULL,'5306','Cabina no protegida.','Cabina del vehículo no protegida ante golpes ni proyección de objetos.',95,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(188,'5. Factores tecnológicos','53. Sistema de protección pasiva de vehículos','5307',NULL,'5307','Cristal parabrisas inadecuado.','Se ha cambiado el cristal de parabrisas por uno no recomendado por el fabricante.',95,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(189,'5. Factores tecnológicos','53. Sistema de protección pasiva de vehículos','5399',NULL,'5399','Otras causas relacionadas con protección pasiva de vehículos. Especificar:','Otros elementos de seguridad pasiva no considerados.',95,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(190,'5. Factores tecnológicos','54. Fallas en vehículos','5401',NULL,'5401','Fallas mecánicas de carrocería.','Todas las fallas atribuibles a la carrocería.',95,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(191,'5. Factores tecnológicos','54. Fallas en vehículos','5402',NULL,'5402','Fallas mecánicas de dirección.','Fallas del sistema de dirección o piezas de este en mal estado.',95,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(192,'5. Factores tecnológicos','54. Fallas en vehículos','5403',NULL,'5403','Fallas mecánicas eléctricas.','Fallas de cualquier parte del sistema eléctrico que influyeron en el accidente (luces, encendido, etc.).',95,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(193,'5. Factores tecnológicos','54. Fallas en vehículos','5404',NULL,'5404','Fallas mecánicas del sistema de frenos.','Fallas en sistemas de frenado cualquiera sea el tipo (con o sin sistema de ABS).',95,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(194,'5. Factores tecnológicos','54. Fallas en vehículos','5405',NULL,'5405','Fallas mecánicas del motor.','Fallas del sistema de propulsión (motor de cualquier tipo).',95,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(195,'5. Factores tecnológicos','54. Fallas en vehículos','5406',NULL,'5406','Fallas de neumáticos.','Fallos de neumáticos por fabricación, mantenimiento o desgaste.',95,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(196,'5. Factores tecnológicos','54. Fallas en vehículos','5407',NULL,'5407','Fallas mecánicas de suspensión.','Fallas en el sistema de suspensión del vehículo que intervinieron en el accidente.',95,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(197,'5. Factores tecnológicos','54. Fallas en vehículos','5408',NULL,'5408','Vehículo en panne sin señalización o deficiente.','Vehículo no cumple con exigencias de señalización ante detención en la vía por fallas.',95,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(198,'5. Factores tecnológicos','54. Fallas en vehículos','5409',NULL,'5409','Falla en sistema de vuelo (vehículos aéreos).','Aplicable a vehículos de transporte aéreo. Cualquier falla del vehículo por cualquier razón. Fabricación, mantenimiento, desgaste, sobre esfuerzo, etc.',96,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(199,'5. Factores tecnológicos','54. Fallas en vehículos','5410',NULL,'5410','Falla en sistema de navegación de la embarcación (vehículos acuáticos).','Aplicable a vehículos de transporte marítimo. Cualquier falla del vehículo por cualquier razón. Fabricación, mantenimiento, desgaste, sobre esfuerzo, etc.',96,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(200,'5. Factores tecnológicos','54. Fallas en vehículos','5499',NULL,'5499','Otras fallas del vehículo. Especificar:','Otras fallas no consideradas en los ítems anteriores.',96,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(201,'5. Factores tecnológicos','55. Máquinas y herramientas','5501',NULL,'5501','Diseño riesgoso de máquinas y/o herramientas o utilización de ellas para fines no concebidos.','Se refiera a máquinas que no cumplen estándares de seguridad en su diseño. Incluye máquinas y herramientas hechizas, máquinas diseñadas sin guardas de partes móviles, escudos protectores etc. Se incorporan aquí problemas de diseño no ergonómico de la máquina que puede provocar accidentes.',96,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(202,'5. Factores tecnológicos','55. Máquinas y herramientas','5502',NULL,'5502','Modificaciones realizadas en la máquina que dan lugar a situaciones de riesgo no previstas por el fabricante.','Por ejemplo, adiciones de partes hechizas que no cumplen estándares de seguridad, modificaciones en los sistemas de protección, modificación en sistemas de alarmas.',96,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(203,'5. Factores tecnológicos','55. Máquinas y herramientas','5503',NULL,'5503','Acceso inadecuado a la máquina o deficiente ubicación.','Aplicable a todos aquellos equipos en el que los trabajadores deban permanecer en el por un tiempo. El acceso y permanencia en la máquina debe ser seguro sin riesgos de accidentes.',96,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(204,'5. Factores tecnológicos','55. Máquinas y herramientas','5504',NULL,'5504','Máquinas sin enclavamiento o estibación segura para la realización de la tarea.','Máquinas o sus componentes que pueden volcarse por mal enclavamiento. Se presentan defectos de estabilidad que ponen en riesgo a los trabajadores. Dispositivos de enclavamiento violados o intervenidos.',97,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(205,'5. Factores tecnológicos','55. Máquinas y herramientas','5505',NULL,'5505','Falta o falla de elementos de protección y aislación de máquinas.','Se incorporan todas aquellas fallas por ausencia de protección de partes móviles, de presión, corte, proyección de partícula aislación de energía eléctrica o calórica, debido al retiro o falta de mantenimiento de la protección. Incorpora defensas y guardas que impidan que el trabajador tome contacto con la parte peligrosa de la máquina. Incorpora las fallas en la aislación de energía calórica en los materiales que puede provocar quemaduras en las personas.',97,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(206,'5. Factores tecnológicos','55. Máquinas y herramientas','5506',NULL,'5506','Materiales defectuosos o de mala calidad en máquinas que involucran altos cambios de energía (eléctrica, calor, flujo de masa, energía química, hidráulica.','Deficiencia en calidad de los materiales que toman riesgosa a la máquina. Ej. Acero o soldadura de mala calidad en estanques o recipientes de presión, engranajes que provocan saltos, contactos eléctricos inadecuados para la cantidad de energía, etc.',97,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(207,'5. Factores tecnológicos','55. Máquinas y herramientas','5507',NULL,'5507','Fallas en el sistema eléctrico, neumático o hidráulico.','Riesgo de shock eléctrico, quemaduras, golpes o atrapamiento debido a fallas del sistema de energía motriz.',97,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(208,'5. Factores tecnológicos','55. Máquinas y herramientas','5508',NULL,'5508','Ausencia de dispositivos de control de emanaciones de las máquinas.','Incluye emanaciones de material particulado, gases, vapores y fluidos líquidos peligrosos.',97,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(209,'5. Factores tecnológicos','55. Máquinas y herramientas','5509',NULL,'5509','Fallas en el sistema de control y comando de la máquina.','Controles y comandos que no funcionan (o lo hacen con deficiencias) para la puesta en marcha, funcionamiento o paro de la máquina.',98,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(210,'5. Factores tecnológicos','55. Máquinas y herramientas','5510',NULL,'5510','Visibilidad insuficiente Sistema de iluminación de máquina inexistente, inadecuado, en mal estado o deficiente.','Se refiere a iluminación localizada de la máquina que genera riesgo de accidente.',98,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(211,'5. Factores tecnológicos','55. Máquinas y herramientas','5511',NULL,'5511','Inexistencia de manual de operación o instrucciones en la máquina. O ilegibles para el operador.','No existe manual de operación, no se entiende por estar en otro idioma está muy deteriorado. Las señales de modos de operación no existen o están borradas.',98,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(212,'5. Factores tecnológicos','55. Máquinas y herramientas','5512',NULL,'5512','Partes estructurales de máquinas, vehículos, equipos y/o herramientas en mal estado.','Poleas, protecciones, motores, neumáticos, sistema de frenos, carrocería, etc.',98,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(213,'5. Factores tecnológicos','55. Máquinas y herramientas','5513',NULL,'5513','Ausencia o deficiencias de sistemas de bloqueo automático de funcionamiento.','Máquinas o sistemas sin dispositivos de seguridad que bloquean el funcionamiento o alejan automáticamente al trabajador ante el peligro.',98,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(214,'5. Factores tecnológicos','55. Máquinas y herramientas','5514',NULL,'5514','Sistemas de puesta en marcha o paro de la máquina fuera de diseño o sin sistema de parada de emergencia.','Involucra a aquellas máquinas que se le ha acondicionado un sistema de marcha o paro distinto al diseño y que incorpora peligros.',98,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(215,'5. Factores tecnológicos','55. Máquinas y herramientas','5515',NULL,'5515','Máquinas sin dispositivos anti explosión en atmósferas explosivas.','Máquinas que tienen riesgos de chispas por encendido o por fricción sin elementos apropiados en atmósferas con riesgo de explosión.',98,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(216,'5. Factores tecnológicos','55. Máquinas y herramientas','5516',NULL,'5516','Ausencia de alarmas de puesta en marcha y funcionamiento.','Aplicable a máquinas peligrosas que requieren dar cuenta de puesta en marcha y funcionamiento.',98,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(217,'5. Factores tecnológicos','55. Máquinas y herramientas','5599',NULL,'5599','Otras causas relativas a máquinas y herramientas. Especificar','Causas relativas a máquinas y herramientas no especificadas en los ítems anteriores.',98,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(218,'6. Factores externos','61. Fuerza mayor','6101',NULL,'6101','Desastres naturales: Inundaciones terremotos, maremotos, erupciones volcánicas imprevistas, aluviones, incendios forestales.','Perturbaciones naturales de carácter mayor que afectan poblaciones. Incluye a los trabajadores de respuestas a emergencias tales como brigadas de incendios forestales, rescatistas.',99,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(219,'6. Factores externos','61. Fuerza mayor','6102',NULL,'6102','Desastres tecnológicos: Explosiones, incendio, fugas radiactivas reactor, colapsos de grandes estructuras.','Accidentes antropogénicos mayores que afectan a lugares en gran escala.',99,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(220,'6. Factores externos','61. Fuerza mayor','6103',NULL,'6103','Acciones delictuales de personas: Delitos de terceros, actos de sabotaje, actos vandálicos, atentados, suicidios.','Actos delictuales de terceros no aislados a la víctima ni la empresa.',99,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(221,'6. Factores externos','61. Fuerza mayor','6104',NULL,'6104','Accidentes involuntarios provocados por terceros no relacionados con la empresa de la víctima.','(Ejemplo choque por imprudencia de un conductor de vehículo ajeno en carretera, atropellos, accidentes de un bus en que la víctima va de pasajero etc.).',99,1,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(222,'7. Otros factores','','7999',NULL,'7999','Otros factores no considerados en ninguno de los grupos principales anteriores. Especificar:','Cualquier otro factor que no cabe en los grupos principales definidos.',99,1,'2026-10-26 15:25:00','2026-10-26 15:25:00');
/*!40000 ALTER TABLE `cause_factors` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `comite_paritario`
--

DROP TABLE IF EXISTS `comite_paritario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `comite_paritario` (
  `Id_Comite` int NOT NULL AUTO_INCREMENT,
  `Fecha_Inicio` date NOT NULL,
  `Fecha_Termino` date DEFAULT NULL,
  `Id_Dependencia` int NOT NULL,
  `Rut_Usuario` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`Id_Comite`),
  KEY `Id_Dependencia` (`Id_Dependencia`),
  KEY `Rut_Usuario` (`Rut_Usuario`),
  CONSTRAINT `comite_paritario_ibfk_1` FOREIGN KEY (`Id_Dependencia`) REFERENCES `dependencia` (`Id_Dependencia`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `comite_paritario_ibfk_2` FOREIGN KEY (`Rut_Usuario`) REFERENCES `usuario` (`Rut_Usuario`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comite_paritario`
--

LOCK TABLES `comite_paritario` WRITE;
/*!40000 ALTER TABLE `comite_paritario` DISABLE KEYS */;
/*!40000 ALTER TABLE `comite_paritario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `comuna`
--

DROP TABLE IF EXISTS `comuna`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `comuna` (
  `Id_Comuna` int NOT NULL AUTO_INCREMENT,
  `Id_Region` int NOT NULL,
  `Nombre_Comuna` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`Id_Comuna`),
  KEY `Id_Region` (`Id_Region`),
  CONSTRAINT `comuna_ibfk_1` FOREIGN KEY (`Id_Region`) REFERENCES `region` (`Id_Region`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comuna`
--

LOCK TABLES `comuna` WRITE;
/*!40000 ALTER TABLE `comuna` DISABLE KEYS */;
/*!40000 ALTER TABLE `comuna` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `condicion_pregunta`
--

DROP TABLE IF EXISTS `condicion_pregunta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `condicion_pregunta` (
  `Id_Condicion` int NOT NULL AUTO_INCREMENT,
  `Id_Pregunta_Origen` int NOT NULL,
  `Id_Pregunta_Destino` int NOT NULL,
  `Operador` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Id_Opcion_Esperada` int DEFAULT NULL,
  PRIMARY KEY (`Id_Condicion`),
  KEY `Id_Pregunta_Origen` (`Id_Pregunta_Origen`),
  KEY `Id_Pregunta_Destino` (`Id_Pregunta_Destino`),
  KEY `Id_Opcion_Esperada` (`Id_Opcion_Esperada`),
  CONSTRAINT `condicion_pregunta_ibfk_1` FOREIGN KEY (`Id_Pregunta_Origen`) REFERENCES `pregunta_declaracion` (`Id_Pregunta`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `condicion_pregunta_ibfk_2` FOREIGN KEY (`Id_Pregunta_Destino`) REFERENCES `pregunta_declaracion` (`Id_Pregunta`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `condicion_pregunta_ibfk_3` FOREIGN KEY (`Id_Opcion_Esperada`) REFERENCES `opcion_pregunta` (`Id_Opcion`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `condicion_pregunta`
--

LOCK TABLES `condicion_pregunta` WRITE;
/*!40000 ALTER TABLE `condicion_pregunta` DISABLE KEYS */;
/*!40000 ALTER TABLE `condicion_pregunta` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `corrective_measures`
--

DROP TABLE IF EXISTS `corrective_measures`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `corrective_measures` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `investigation_id` bigint unsigned NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `responsible` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `due_date` date DEFAULT NULL,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pendiente',
  `progress` tinyint unsigned NOT NULL DEFAULT '0',
  `ai_generated` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `corrective_measures_investigation_id_foreign` (`investigation_id`),
  KEY `corrective_measures_status_index` (`status`),
  CONSTRAINT `corrective_measures_investigation_id_foreign` FOREIGN KEY (`investigation_id`) REFERENCES `investigations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `corrective_measures`
--

LOCK TABLES `corrective_measures` WRITE;
/*!40000 ALTER TABLE `corrective_measures` DISABLE KEYS */;
/*!40000 ALTER TABLE `corrective_measures` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `declaracion`
--

DROP TABLE IF EXISTS `declaracion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `declaracion` (
  `Id_Declaracion` int NOT NULL AUTO_INCREMENT,
  `Id_Formulario` int NOT NULL,
  `Codigo_Investigacion` int NOT NULL,
  `Id_Testigo_Caso` int NOT NULL,
  `Id_Tipo_Entrevistado` int NOT NULL,
  `Rut_Usuario` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Fecha_Entrevista` date NOT NULL,
  `Hora_Inicio` time DEFAULT NULL,
  `Lugar_Entrevista` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Modalidad` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Cargo_Al_Declarar` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Empresa_Al_Declarar` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Estado` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Firma` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Fecha_Validacion` datetime DEFAULT NULL,
  PRIMARY KEY (`Id_Declaracion`),
  KEY `Id_Formulario` (`Id_Formulario`),
  KEY `Codigo_Investigacion` (`Codigo_Investigacion`),
  KEY `Id_Testigo_Caso` (`Id_Testigo_Caso`),
  KEY `Id_Tipo_Entrevistado` (`Id_Tipo_Entrevistado`),
  KEY `Rut_Usuario` (`Rut_Usuario`),
  CONSTRAINT `declaracion_ibfk_1` FOREIGN KEY (`Id_Formulario`) REFERENCES `formulario_declaracion` (`Id_Formulario`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `declaracion_ibfk_2` FOREIGN KEY (`Codigo_Investigacion`) REFERENCES `investigacion` (`Codigo_Investigacion`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `declaracion_ibfk_3` FOREIGN KEY (`Id_Testigo_Caso`) REFERENCES `testigo_caso` (`Id_Testigo_Caso`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `declaracion_ibfk_4` FOREIGN KEY (`Id_Tipo_Entrevistado`) REFERENCES `tipo_entrevistado` (`Id_Tipo_Entrevistado`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `declaracion_ibfk_5` FOREIGN KEY (`Rut_Usuario`) REFERENCES `usuario` (`Rut_Usuario`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `declaracion`
--

LOCK TABLES `declaracion` WRITE;
/*!40000 ALTER TABLE `declaracion` DISABLE KEYS */;
/*!40000 ALTER TABLE `declaracion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `delegado_seguridad`
--

DROP TABLE IF EXISTS `delegado_seguridad`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `delegado_seguridad` (
  `Id_Delegado` int NOT NULL AUTO_INCREMENT,
  `Integrante_Delegado` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Fecha_Inicio` date NOT NULL,
  `Fecha_Termino` date DEFAULT NULL,
  `Id_Dependencia` int NOT NULL,
  `Rut_Usuario` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`Id_Delegado`),
  KEY `Id_Dependencia` (`Id_Dependencia`),
  KEY `Rut_Usuario` (`Rut_Usuario`),
  CONSTRAINT `delegado_seguridad_ibfk_1` FOREIGN KEY (`Id_Dependencia`) REFERENCES `dependencia` (`Id_Dependencia`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `delegado_seguridad_ibfk_2` FOREIGN KEY (`Rut_Usuario`) REFERENCES `usuario` (`Rut_Usuario`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `delegado_seguridad`
--

LOCK TABLES `delegado_seguridad` WRITE;
/*!40000 ALTER TABLE `delegado_seguridad` DISABLE KEYS */;
/*!40000 ALTER TABLE `delegado_seguridad` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dependencia`
--

DROP TABLE IF EXISTS `dependencia`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dependencia` (
  `Id_Dependencia` int NOT NULL AUTO_INCREMENT,
  `Nombre_Dependencia` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`Id_Dependencia`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dependencia`
--

LOCK TABLES `dependencia` WRITE;
/*!40000 ALTER TABLE `dependencia` DISABLE KEYS */;
/*!40000 ALTER TABLE `dependencia` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detalle_accidente`
--

DROP TABLE IF EXISTS `detalle_accidente`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `detalle_accidente` (
  `Id_Caso` int NOT NULL,
  `Fecha_Accidente` date NOT NULL,
  `Hora_Accidente` time DEFAULT NULL,
  `Hora_Ingreso_Trabajo` time DEFAULT NULL,
  `Hora_Salida_Trabajo` time DEFAULT NULL,
  `Direccion_Accidente` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Lugar_Accidente` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Actividad_Previa_Accidente` text COLLATE utf8mb4_unicode_ci,
  `Descripcion_Accidente` text COLLATE utf8mb4_unicode_ci,
  `Trabajo_Habitual` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Realizaba_Trabajo_Habitual` tinyint(1) DEFAULT NULL,
  `Clasificacion_Gravedad` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Tipo_Accidente` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Id_Evento_Accidente` int DEFAULT NULL,
  PRIMARY KEY (`Id_Caso`),
  KEY `Id_Evento_Accidente` (`Id_Evento_Accidente`),
  CONSTRAINT `detalle_accidente_ibfk_1` FOREIGN KEY (`Id_Caso`) REFERENCES `caso` (`Id_Caso`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `detalle_accidente_ibfk_2` FOREIGN KEY (`Id_Evento_Accidente`) REFERENCES `evento_que_provoco_el_accidente` (`Id_Evento_Accidente`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_accidente`
--

LOCK TABLES `detalle_accidente` WRITE;
/*!40000 ALTER TABLE `detalle_accidente` DISABLE KEYS */;
/*!40000 ALTER TABLE `detalle_accidente` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detalle_enfermedad`
--

DROP TABLE IF EXISTS `detalle_enfermedad`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `detalle_enfermedad` (
  `Id_Caso` int NOT NULL,
  `Descripcion_Sintomas` text COLLATE utf8mb4_unicode_ci,
  `Duracion_Sintomas_Valor` decimal(8,2) DEFAULT NULL,
  `Duracion_Sintomas_Unidad` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Molestias_Previas` tinyint(1) DEFAULT NULL,
  `Actividad_Inicio_Sintomas` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Puesto_Inicio_Sintomas` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Companero_Mismos_Sintomas` tinyint(1) DEFAULT NULL,
  `Agentes_Sospechados` text COLLATE utf8mb4_unicode_ci,
  `Tiempo_Exposicion_Valor` decimal(8,2) DEFAULT NULL,
  `Tiempo_Exposicion_Unidad` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Fecha_Inicio_Sintomas` date DEFAULT NULL,
  PRIMARY KEY (`Id_Caso`),
  CONSTRAINT `detalle_enfermedad_ibfk_1` FOREIGN KEY (`Id_Caso`) REFERENCES `caso` (`Id_Caso`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_enfermedad`
--

LOCK TABLES `detalle_enfermedad` WRITE;
/*!40000 ALTER TABLE `detalle_enfermedad` DISABLE KEYS */;
/*!40000 ALTER TABLE `detalle_enfermedad` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `empresa_empleador`
--

DROP TABLE IF EXISTS `empresa_empleador`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `empresa_empleador` (
  `Id_Empresa` int NOT NULL AUTO_INCREMENT,
  `Nombre_Empresa` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Centro_Trabajo` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Organismo_Administrador` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Direccion_Sucursal` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Propiedad_Empresa` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Promedio_Anual_Trabajadores` decimal(8,2) DEFAULT NULL,
  `Trabajadores` int DEFAULT NULL,
  `Telefono` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Correo` varchar(254) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Id_Comuna` int NOT NULL,
  `Tipo_Empresa` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`Id_Empresa`),
  KEY `Id_Comuna` (`Id_Comuna`),
  CONSTRAINT `empresa_empleador_ibfk_1` FOREIGN KEY (`Id_Comuna`) REFERENCES `comuna` (`Id_Comuna`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `empresa_empleador`
--

LOCK TABLES `empresa_empleador` WRITE;
/*!40000 ALTER TABLE `empresa_empleador` DISABLE KEYS */;
/*!40000 ALTER TABLE `empresa_empleador` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `establishment_user`
--

DROP TABLE IF EXISTS `establishment_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `establishment_user` (
  `establishment_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`establishment_id`,`user_id`),
  KEY `establishment_user_user_id_foreign` (`user_id`),
  CONSTRAINT `establishment_user_establishment_id_foreign` FOREIGN KEY (`establishment_id`) REFERENCES `establishments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `establishment_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `establishment_user`
--

LOCK TABLES `establishment_user` WRITE;
/*!40000 ALTER TABLE `establishment_user` DISABLE KEYS */;
INSERT INTO `establishment_user` VALUES (3,1),(3,2),(3,3),(3,4),(3,5);
/*!40000 ALTER TABLE `establishment_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `establishments`
--

DROP TABLE IF EXISTS `establishments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `establishments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `establishments`
--

LOCK TABLES `establishments` WRITE;
/*!40000 ALTER TABLE `establishments` DISABLE KEYS */;
INSERT INTO `establishments` VALUES (1,'CESFAM','Centro Demostración Norte',1,'2026-10-26 15:24:58','2026-10-26 15:24:58'),(2,'CECOSF','Centro Demostración Sur',1,'2026-10-26 15:24:58','2026-10-26 15:24:58'),(3,'Oficina administrativa','Dependencia Demostración Central',1,'2026-10-26 15:24:58','2026-10-26 15:24:58');
/*!40000 ALTER TABLE `establishments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `evento_que_provoco_el_accidente`
--

DROP TABLE IF EXISTS `evento_que_provoco_el_accidente`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evento_que_provoco_el_accidente` (
  `Id_Evento_Accidente` int NOT NULL AUTO_INCREMENT,
  `Nombre_Evento` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`Id_Evento_Accidente`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `evento_que_provoco_el_accidente`
--

LOCK TABLES `evento_que_provoco_el_accidente` WRITE;
/*!40000 ALTER TABLE `evento_que_provoco_el_accidente` DISABLE KEYS */;
/*!40000 ALTER TABLE `evento_que_provoco_el_accidente` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `evidencia`
--

DROP TABLE IF EXISTS `evidencia`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evidencia` (
  `Id_Evidencia` int NOT NULL AUTO_INCREMENT,
  `Id_Caso` int NOT NULL,
  `Nombre_Archivo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Tipo_MIME` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Archivo` longblob NOT NULL,
  `Fecha_Carga` datetime NOT NULL,
  PRIMARY KEY (`Id_Evidencia`),
  KEY `Id_Caso` (`Id_Caso`),
  CONSTRAINT `evidencia_ibfk_1` FOREIGN KEY (`Id_Caso`) REFERENCES `caso` (`Id_Caso`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `evidencia`
--

LOCK TABLES `evidencia` WRITE;
/*!40000 ALTER TABLE `evidencia` DISABLE KEYS */;
/*!40000 ALTER TABLE `evidencia` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `evidencia_medida`
--

DROP TABLE IF EXISTS `evidencia_medida`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evidencia_medida` (
  `Id_Evidencia_Medida` int NOT NULL AUTO_INCREMENT,
  `Id_Monitoreo` int NOT NULL,
  `Nombre_Archivo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Tipo_MIME` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Archivo` longblob NOT NULL,
  `Fecha_Carga` datetime NOT NULL,
  PRIMARY KEY (`Id_Evidencia_Medida`),
  KEY `Id_Monitoreo` (`Id_Monitoreo`),
  CONSTRAINT `evidencia_medida_ibfk_1` FOREIGN KEY (`Id_Monitoreo`) REFERENCES `monitoreo_medida_control` (`Id_Monitoreo`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `evidencia_medida`
--

LOCK TABLES `evidencia_medida` WRITE;
/*!40000 ALTER TABLE `evidencia_medida` DISABLE KEYS */;
/*!40000 ALTER TABLE `evidencia_medida` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `formulario_declaracion`
--

DROP TABLE IF EXISTS `formulario_declaracion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `formulario_declaracion` (
  `Id_Formulario` int NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Version` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Estado` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`Id_Formulario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `formulario_declaracion`
--

LOCK TABLES `formulario_declaracion` WRITE;
/*!40000 ALTER TABLE `formulario_declaracion` DISABLE KEYS */;
/*!40000 ALTER TABLE `formulario_declaracion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ia`
--

DROP TABLE IF EXISTS `ia`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ia` (
  `Id_Prompt` int NOT NULL AUTO_INCREMENT,
  `Descripcion_Prompt` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Texto_Prompt_Base` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`Id_Prompt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ia`
--

LOCK TABLES `ia` WRITE;
/*!40000 ALTER TABLE `ia` DISABLE KEYS */;
/*!40000 ALTER TABLE `ia` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `interview_answer_options`
--

DROP TABLE IF EXISTS `interview_answer_options`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `interview_answer_options` (
  `interview_answer_id` bigint unsigned NOT NULL,
  `questionnaire_option_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`interview_answer_id`,`questionnaire_option_id`),
  KEY `interview_answer_options_questionnaire_option_id_foreign` (`questionnaire_option_id`),
  CONSTRAINT `interview_answer_options_interview_answer_id_foreign` FOREIGN KEY (`interview_answer_id`) REFERENCES `interview_answers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `interview_answer_options_questionnaire_option_id_foreign` FOREIGN KEY (`questionnaire_option_id`) REFERENCES `questionnaire_options` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `interview_answer_options`
--

LOCK TABLES `interview_answer_options` WRITE;
/*!40000 ALTER TABLE `interview_answer_options` DISABLE KEYS */;
/*!40000 ALTER TABLE `interview_answer_options` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `interview_answers`
--

DROP TABLE IF EXISTS `interview_answers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `interview_answers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `investigation_interview_id` bigint unsigned NOT NULL,
  `questionnaire_question_id` bigint unsigned DEFAULT NULL,
  `question_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `answer_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'text',
  `value` json NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `estado` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'respondida',
  PRIMARY KEY (`id`),
  UNIQUE KEY `interview_answers_investigation_interview_id_question_key_unique` (`investigation_interview_id`,`question_key`),
  KEY `interview_answers_questionnaire_question_id_foreign` (`questionnaire_question_id`),
  CONSTRAINT `interview_answers_investigation_interview_id_foreign` FOREIGN KEY (`investigation_interview_id`) REFERENCES `investigation_interviews` (`id`) ON DELETE CASCADE,
  CONSTRAINT `interview_answers_questionnaire_question_id_foreign` FOREIGN KEY (`questionnaire_question_id`) REFERENCES `questionnaire_questions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `interview_answers`
--

LOCK TABLES `interview_answers` WRITE;
/*!40000 ALTER TABLE `interview_answers` DISABLE KEYS */;
/*!40000 ALTER TABLE `interview_answers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `investigacion`
--

DROP TABLE IF EXISTS `investigacion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `investigacion` (
  `Codigo_Investigacion` int NOT NULL AUTO_INCREMENT,
  `Fecha_Creacion` datetime NOT NULL,
  `Revision` int NOT NULL DEFAULT '1',
  `Rut_Usuario` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Id_Caso` int NOT NULL,
  PRIMARY KEY (`Codigo_Investigacion`),
  UNIQUE KEY `Id_Caso` (`Id_Caso`),
  KEY `Rut_Usuario` (`Rut_Usuario`),
  CONSTRAINT `investigacion_ibfk_1` FOREIGN KEY (`Rut_Usuario`) REFERENCES `usuario` (`Rut_Usuario`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `investigacion_ibfk_2` FOREIGN KEY (`Id_Caso`) REFERENCES `caso` (`Id_Caso`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `investigacion`
--

LOCK TABLES `investigacion` WRITE;
/*!40000 ALTER TABLE `investigacion` DISABLE KEYS */;
/*!40000 ALTER TABLE `investigacion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `investigation_cause_factor`
--

DROP TABLE IF EXISTS `investigation_cause_factor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `investigation_cause_factor` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `investigation_id` bigint unsigned NOT NULL,
  `cause_factor_id` bigint unsigned NOT NULL,
  `origen` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'manual',
  `fundamento` text COLLATE utf8mb4_unicode_ci,
  `validated_by` bigint unsigned DEFAULT NULL,
  `validated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `inv_cause_factor_unique` (`investigation_id`,`cause_factor_id`),
  KEY `investigation_cause_factor_cause_factor_id_foreign` (`cause_factor_id`),
  KEY `investigation_cause_factor_validated_by_foreign` (`validated_by`),
  CONSTRAINT `investigation_cause_factor_cause_factor_id_foreign` FOREIGN KEY (`cause_factor_id`) REFERENCES `cause_factors` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `investigation_cause_factor_investigation_id_foreign` FOREIGN KEY (`investigation_id`) REFERENCES `investigations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `investigation_cause_factor_validated_by_foreign` FOREIGN KEY (`validated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `investigation_cause_factor`
--

LOCK TABLES `investigation_cause_factor` WRITE;
/*!40000 ALTER TABLE `investigation_cause_factor` DISABLE KEYS */;
/*!40000 ALTER TABLE `investigation_cause_factor` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `investigation_facts`
--

DROP TABLE IF EXISTS `investigation_facts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `investigation_facts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `investigation_id` bigint unsigned NOT NULL,
  `position` smallint unsigned NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `ai_generated` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `investigation_facts_investigation_id_foreign` (`investigation_id`),
  CONSTRAINT `investigation_facts_investigation_id_foreign` FOREIGN KEY (`investigation_id`) REFERENCES `investigations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `investigation_facts`
--

LOCK TABLES `investigation_facts` WRITE;
/*!40000 ALTER TABLE `investigation_facts` DISABLE KEYS */;
/*!40000 ALTER TABLE `investigation_facts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `investigation_interviews`
--

DROP TABLE IF EXISTS `investigation_interviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `investigation_interviews` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `investigation_id` bigint unsigned NOT NULL,
  `questionnaire_form_id` bigint unsigned DEFAULT NULL,
  `created_by` bigint unsigned NOT NULL,
  `questionnaire_type` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'adapted',
  `token` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `interviewee_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `interviewee_role` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `witness_type` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `interview_date` date DEFAULT NULL,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Completada',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `interview_start_time` time DEFAULT NULL,
  `interview_location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `interviewee_rut` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `interviewee_company` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `validated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `investigation_interviews_token_unique` (`token`),
  KEY `investigation_interviews_investigation_id_foreign` (`investigation_id`),
  KEY `investigation_interviews_created_by_foreign` (`created_by`),
  KEY `investigation_interviews_questionnaire_form_id_foreign` (`questionnaire_form_id`),
  CONSTRAINT `investigation_interviews_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `investigation_interviews_investigation_id_foreign` FOREIGN KEY (`investigation_id`) REFERENCES `investigations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `investigation_interviews_questionnaire_form_id_foreign` FOREIGN KEY (`questionnaire_form_id`) REFERENCES `questionnaire_forms` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `investigation_interviews`
--

LOCK TABLES `investigation_interviews` WRITE;
/*!40000 ALTER TABLE `investigation_interviews` DISABLE KEYS */;
/*!40000 ALTER TABLE `investigation_interviews` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `investigations`
--

DROP TABLE IF EXISTS `investigations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `investigations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `folio` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `occupational_case_id` bigint unsigned NOT NULL,
  `owner_id` bigint unsigned NOT NULL,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Borrador',
  `final_story` longtext COLLATE utf8mb4_unicode_ci,
  `ai_model` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ai_analyzed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `investigations_folio_unique` (`folio`),
  UNIQUE KEY `investigations_occupational_case_id_unique` (`occupational_case_id`),
  KEY `investigations_owner_id_foreign` (`owner_id`),
  KEY `investigations_status_index` (`status`),
  CONSTRAINT `investigations_occupational_case_id_foreign` FOREIGN KEY (`occupational_case_id`) REFERENCES `occupational_cases` (`id`) ON DELETE CASCADE,
  CONSTRAINT `investigations_owner_id_foreign` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `investigations`
--

LOCK TABLES `investigations` WRITE;
/*!40000 ALTER TABLE `investigations` DISABLE KEYS */;
/*!40000 ALTER TABLE `investigations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` smallint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `matriz_factores_causas`
--

DROP TABLE IF EXISTS `matriz_factores_causas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `matriz_factores_causas` (
  `Cod_Matriz` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Subcodigo` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Explicacion_Glosa` text COLLATE utf8mb4_unicode_ci,
  `Glosa` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`Cod_Matriz`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `matriz_factores_causas`
--

LOCK TABLES `matriz_factores_causas` WRITE;
/*!40000 ALTER TABLE `matriz_factores_causas` DISABLE KEYS */;
/*!40000 ALTER TABLE `matriz_factores_causas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `medida_mitigacion`
--

DROP TABLE IF EXISTS `medida_mitigacion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `medida_mitigacion` (
  `Id_Medida_Mitigacion` int NOT NULL AUTO_INCREMENT,
  `Id_Arbol_Causa` int NOT NULL,
  `Descripcion` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `Estado_Revision` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Fecha_Registro` datetime NOT NULL,
  PRIMARY KEY (`Id_Medida_Mitigacion`),
  KEY `Id_Arbol_Causa` (`Id_Arbol_Causa`),
  CONSTRAINT `medida_mitigacion_ibfk_1` FOREIGN KEY (`Id_Arbol_Causa`) REFERENCES `arbol_causas` (`Id_Arbol_Causa`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `medida_mitigacion`
--

LOCK TABLES `medida_mitigacion` WRITE;
/*!40000 ALTER TABLE `medida_mitigacion` DISABLE KEYS */;
/*!40000 ALTER TABLE `medida_mitigacion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_09_30_193521_create_establishments_table',1),(5,'2026_09_30_193523_create_roles_table',1),(6,'2026_09_30_193525_create_registration_requests_table',1),(7,'2026_09_30_193528_add_giatep_fields_to_users_table',1),(8,'2026_09_30_193530_create_role_user_table',1),(9,'2026_09_30_193532_create_establishment_user_table',1),(10,'2026_10_01_023043_create_personal_access_tokens_table',1),(11,'2026_10_13_190000_create_case_management_tables',1),(12,'2026_10_13_210000_create_investigation_catalog_tables',1),(13,'2026_10_15_120000_create_catalog_options_table',1),(14,'2026_10_15_170000_renombrar_esquema_negocio_a_espanol',1),(15,'2026_10_19_120000_update_investigation_ai_prompt',1),(16,'2026_10_21_120000_update_investigation_ai_prompt_methodology',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `monitoreo_medida_control`
--

DROP TABLE IF EXISTS `monitoreo_medida_control`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `monitoreo_medida_control` (
  `Id_Monitoreo` int NOT NULL AUTO_INCREMENT,
  `Id_Medida_Mitigacion` int NOT NULL,
  `Fecha_Limite` date DEFAULT NULL,
  `Fecha_Ejecucion` date DEFAULT NULL,
  `Estado` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Nombre_Responsable` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Comite_Delegado_A_Cargo` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Id_Plan` int NOT NULL,
  PRIMARY KEY (`Id_Monitoreo`),
  KEY `Id_Medida_Mitigacion` (`Id_Medida_Mitigacion`),
  KEY `Id_Plan` (`Id_Plan`),
  CONSTRAINT `monitoreo_medida_control_ibfk_1` FOREIGN KEY (`Id_Medida_Mitigacion`) REFERENCES `medida_mitigacion` (`Id_Medida_Mitigacion`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `monitoreo_medida_control_ibfk_2` FOREIGN KEY (`Id_Plan`) REFERENCES `plan_accion` (`Id_Plan`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `monitoreo_medida_control`
--

LOCK TABLES `monitoreo_medida_control` WRITE;
/*!40000 ALTER TABLE `monitoreo_medida_control` DISABLE KEYS */;
/*!40000 ALTER TABLE `monitoreo_medida_control` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `observacion_caso`
--

DROP TABLE IF EXISTS `observacion_caso`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `observacion_caso` (
  `Id_Observacion` int NOT NULL AUTO_INCREMENT,
  `Observacion` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `FechaObs` datetime NOT NULL,
  `Rut_Usuario` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Id_Caso` int NOT NULL,
  PRIMARY KEY (`Id_Observacion`),
  KEY `Rut_Usuario` (`Rut_Usuario`),
  KEY `Id_Caso` (`Id_Caso`),
  CONSTRAINT `observacion_caso_ibfk_1` FOREIGN KEY (`Rut_Usuario`) REFERENCES `usuario` (`Rut_Usuario`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `observacion_caso_ibfk_2` FOREIGN KEY (`Id_Caso`) REFERENCES `caso` (`Id_Caso`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `observacion_caso`
--

LOCK TABLES `observacion_caso` WRITE;
/*!40000 ALTER TABLE `observacion_caso` DISABLE KEYS */;
/*!40000 ALTER TABLE `observacion_caso` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `observacion_investigacion`
--

DROP TABLE IF EXISTS `observacion_investigacion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `observacion_investigacion` (
  `Id_Observacion` int NOT NULL AUTO_INCREMENT,
  `Observacion` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `Fecha_Observacion` datetime NOT NULL,
  `Rut_Usuario` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Codigo_Investigacion` int NOT NULL,
  PRIMARY KEY (`Id_Observacion`),
  KEY `Rut_Usuario` (`Rut_Usuario`),
  KEY `Codigo_Investigacion` (`Codigo_Investigacion`),
  CONSTRAINT `observacion_investigacion_ibfk_1` FOREIGN KEY (`Rut_Usuario`) REFERENCES `usuario` (`Rut_Usuario`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `observacion_investigacion_ibfk_2` FOREIGN KEY (`Codigo_Investigacion`) REFERENCES `investigacion` (`Codigo_Investigacion`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `observacion_investigacion`
--

LOCK TABLES `observacion_investigacion` WRITE;
/*!40000 ALTER TABLE `observacion_investigacion` DISABLE KEYS */;
/*!40000 ALTER TABLE `observacion_investigacion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `observacion_planaccion`
--

DROP TABLE IF EXISTS `observacion_planaccion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `observacion_planaccion` (
  `Id_Observacion` int NOT NULL AUTO_INCREMENT,
  `Observacion` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `FechaObs` datetime NOT NULL,
  `Id_Plan` int NOT NULL,
  `Rut_Usuario` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`Id_Observacion`),
  KEY `Id_Plan` (`Id_Plan`),
  KEY `Rut_Usuario` (`Rut_Usuario`),
  CONSTRAINT `observacion_planaccion_ibfk_1` FOREIGN KEY (`Id_Plan`) REFERENCES `plan_accion` (`Id_Plan`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `observacion_planaccion_ibfk_2` FOREIGN KEY (`Rut_Usuario`) REFERENCES `usuario` (`Rut_Usuario`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `observacion_planaccion`
--

LOCK TABLES `observacion_planaccion` WRITE;
/*!40000 ALTER TABLE `observacion_planaccion` DISABLE KEYS */;
/*!40000 ALTER TABLE `observacion_planaccion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `occupational_cases`
--

DROP TABLE IF EXISTS `occupational_cases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `occupational_cases` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `folio` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` bigint unsigned NOT NULL,
  `establishment_id` bigint unsigned DEFAULT NULL,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Borrador',
  `case_type` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `employer_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `employer_rut` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `employer_address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `employer_commune` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `employer_phone` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `economic_activity` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `company_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `administrator` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `worker_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `worker_rut` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `worker_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `worker_commune` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `worker_phone` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `worker_sex` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `nationality` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `job_title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `workplace` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contract_type` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `occupational_category` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hire_date` date DEFAULT NULL,
  `event_date` date NOT NULL,
  `event_time` time DEFAULT NULL,
  `event_location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `narrative` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `witnesses` text COLLATE utf8mb4_unicode_ci,
  `details` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `occupational_cases_folio_unique` (`folio`),
  KEY `occupational_cases_created_by_foreign` (`created_by`),
  KEY `occupational_cases_establishment_id_foreign` (`establishment_id`),
  KEY `occupational_cases_status_index` (`status`),
  KEY `occupational_cases_case_type_index` (`case_type`),
  KEY `occupational_cases_worker_rut_index` (`worker_rut`),
  CONSTRAINT `occupational_cases_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `occupational_cases_establishment_id_foreign` FOREIGN KEY (`establishment_id`) REFERENCES `establishments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `occupational_cases`
--

LOCK TABLES `occupational_cases` WRITE;
/*!40000 ALTER TABLE `occupational_cases` DISABLE KEYS */;
INSERT INTO `occupational_cases` VALUES (1,'GIATEP-2026-001',3,1,'Borrador','Accidente de trabajo','Empresa de Prueba GIATEP','TEST-EMP-001','Dirección ficticia de prueba','Los Ángeles','000000000','Actividad de prueba','Principal','ACHS','Trabajador de Prueba','TEST-WRK-001','Domicilio ficticio','Los Ángeles','000000000','Otro',NULL,'Chilena','Cargo de prueba','Unidad de prueba',NULL,'Trabajador dependiente',NULL,'2026-10-01','10:30:00','Centro de trabajo de prueba','Relato ficticio para comprobar el almacenamiento del caso en el entorno de pruebas.',NULL,'{\"income_type\": null, \"proof_detail\": null, \"work_end_time\": null, \"work_schedule\": \"Diurna\", \"company_tenure\": null, \"work_start_time\": null, \"indigenous_people\": null, \"activity_before_event\": \"Actividad ficticia de prueba\", \"usual_job_description\": \"Función habitual de prueba\"}','2026-10-26 15:26:38','2026-10-26 15:26:38');
/*!40000 ALTER TABLE `occupational_cases` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `opcion_pregunta`
--

DROP TABLE IF EXISTS `opcion_pregunta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `opcion_pregunta` (
  `Id_Opcion` int NOT NULL AUTO_INCREMENT,
  `Id_Pregunta` int NOT NULL,
  `Texto_Opcion` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Orden` smallint NOT NULL,
  `Requiere_Detalle` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`Id_Opcion`),
  KEY `Id_Pregunta` (`Id_Pregunta`),
  CONSTRAINT `opcion_pregunta_ibfk_1` FOREIGN KEY (`Id_Pregunta`) REFERENCES `pregunta_declaracion` (`Id_Pregunta`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `opcion_pregunta`
--

LOCK TABLES `opcion_pregunta` WRITE;
/*!40000 ALTER TABLE `opcion_pregunta` DISABLE KEYS */;
/*!40000 ALTER TABLE `opcion_pregunta` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `parte_cuerpo_caso`
--

DROP TABLE IF EXISTS `parte_cuerpo_caso`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `parte_cuerpo_caso` (
  `Id_Parte_Cuerpo_Caso` int NOT NULL AUTO_INCREMENT,
  `Id_Caso` int NOT NULL,
  `Id_Parte_Del_Cuerpo` int NOT NULL,
  PRIMARY KEY (`Id_Parte_Cuerpo_Caso`),
  UNIQUE KEY `Id_Caso` (`Id_Caso`,`Id_Parte_Del_Cuerpo`),
  KEY `Id_Parte_Del_Cuerpo` (`Id_Parte_Del_Cuerpo`),
  CONSTRAINT `parte_cuerpo_caso_ibfk_1` FOREIGN KEY (`Id_Caso`) REFERENCES `caso` (`Id_Caso`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `parte_cuerpo_caso_ibfk_2` FOREIGN KEY (`Id_Parte_Del_Cuerpo`) REFERENCES `parte_del_cuerpo` (`Id_Parte_Del_Cuerpo`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `parte_cuerpo_caso`
--

LOCK TABLES `parte_cuerpo_caso` WRITE;
/*!40000 ALTER TABLE `parte_cuerpo_caso` DISABLE KEYS */;
/*!40000 ALTER TABLE `parte_cuerpo_caso` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `parte_del_cuerpo`
--

DROP TABLE IF EXISTS `parte_del_cuerpo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `parte_del_cuerpo` (
  `Id_Parte_Del_Cuerpo` int NOT NULL AUTO_INCREMENT,
  `Nombre_Parte_Del_Cuerpo` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`Id_Parte_Del_Cuerpo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `parte_del_cuerpo`
--

LOCK TABLES `parte_del_cuerpo` WRITE;
/*!40000 ALTER TABLE `parte_del_cuerpo` DISABLE KEYS */;
/*!40000 ALTER TABLE `parte_del_cuerpo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `participante_investigacion`
--

DROP TABLE IF EXISTS `participante_investigacion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `participante_investigacion` (
  `Id_Participante_Inv` int NOT NULL AUTO_INCREMENT,
  `Codigo_Investigacion` int NOT NULL,
  `Nombre_Apellido` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Cargo_Puesto` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Establecimiento` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Firma` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`Id_Participante_Inv`),
  KEY `Codigo_Investigacion` (`Codigo_Investigacion`),
  CONSTRAINT `participante_investigacion_ibfk_1` FOREIGN KEY (`Codigo_Investigacion`) REFERENCES `investigacion` (`Codigo_Investigacion`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `participante_investigacion`
--

LOCK TABLES `participante_investigacion` WRITE;
/*!40000 ALTER TABLE `participante_investigacion` DISABLE KEYS */;
/*!40000 ALTER TABLE `participante_investigacion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permisos`
--

DROP TABLE IF EXISTS `permisos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permisos` (
  `Id_Permiso` int NOT NULL AUTO_INCREMENT,
  `Nombre_Permiso` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`Id_Permiso`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permisos`
--

LOCK TABLES `permisos` WRITE;
/*!40000 ALTER TABLE `permisos` DISABLE KEYS */;
/*!40000 ALTER TABLE `permisos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `plan_accion`
--

DROP TABLE IF EXISTS `plan_accion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `plan_accion` (
  `Id_Plan` int NOT NULL AUTO_INCREMENT,
  `Fecha_Creacion` datetime NOT NULL,
  `Fecha_Inicio` date DEFAULT NULL,
  `Fecha_Termino` date DEFAULT NULL,
  `Estado` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Codigo_Investigacion` int NOT NULL,
  PRIMARY KEY (`Id_Plan`),
  KEY `Codigo_Investigacion` (`Codigo_Investigacion`),
  CONSTRAINT `plan_accion_ibfk_1` FOREIGN KEY (`Codigo_Investigacion`) REFERENCES `investigacion` (`Codigo_Investigacion`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `plan_accion`
--

LOCK TABLES `plan_accion` WRITE;
/*!40000 ALTER TABLE `plan_accion` DISABLE KEYS */;
/*!40000 ALTER TABLE `plan_accion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pregunta_declaracion`
--

DROP TABLE IF EXISTS `pregunta_declaracion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pregunta_declaracion` (
  `Id_Pregunta` int NOT NULL AUTO_INCREMENT,
  `Id_Formulario` int NOT NULL,
  `Seccion` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Texto_Pregunta` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `Tipo_Respuesta` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Orden` smallint NOT NULL,
  `Obligatorio_Si_Aplica` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`Id_Pregunta`),
  KEY `Id_Formulario` (`Id_Formulario`),
  CONSTRAINT `pregunta_declaracion_ibfk_1` FOREIGN KEY (`Id_Formulario`) REFERENCES `formulario_declaracion` (`Id_Formulario`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pregunta_declaracion`
--

LOCK TABLES `pregunta_declaracion` WRITE;
/*!40000 ALTER TABLE `pregunta_declaracion` DISABLE KEYS */;
/*!40000 ALTER TABLE `pregunta_declaracion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pueblos_originarios`
--

DROP TABLE IF EXISTS `pueblos_originarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pueblos_originarios` (
  `Id_Pueblo_Originario` int NOT NULL AUTO_INCREMENT,
  `Nombre_Pueblo` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`Id_Pueblo_Originario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pueblos_originarios`
--

LOCK TABLES `pueblos_originarios` WRITE;
/*!40000 ALTER TABLE `pueblos_originarios` DISABLE KEYS */;
/*!40000 ALTER TABLE `pueblos_originarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `questionnaire_conditions`
--

DROP TABLE IF EXISTS `questionnaire_conditions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `questionnaire_conditions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `questionnaire_question_id` bigint unsigned NOT NULL,
  `depends_on_question_id` bigint unsigned NOT NULL,
  `operador` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'equals',
  `valor_esperado` json NOT NULL,
  `accion` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'show',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `questionnaire_conditions_questionnaire_question_id_foreign` (`questionnaire_question_id`),
  KEY `questionnaire_conditions_depends_on_question_id_foreign` (`depends_on_question_id`),
  CONSTRAINT `questionnaire_conditions_depends_on_question_id_foreign` FOREIGN KEY (`depends_on_question_id`) REFERENCES `questionnaire_questions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `questionnaire_conditions_questionnaire_question_id_foreign` FOREIGN KEY (`questionnaire_question_id`) REFERENCES `questionnaire_questions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `questionnaire_conditions`
--

LOCK TABLES `questionnaire_conditions` WRITE;
/*!40000 ALTER TABLE `questionnaire_conditions` DISABLE KEYS */;
/*!40000 ALTER TABLE `questionnaire_conditions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `questionnaire_forms`
--

DROP TABLE IF EXISTS `questionnaire_forms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `questionnaire_forms` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `version` smallint unsigned NOT NULL DEFAULT '1',
  `estado` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'activo',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `questionnaire_forms_tipo_version_unique` (`tipo`,`version`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `questionnaire_forms`
--

LOCK TABLES `questionnaire_forms` WRITE;
/*!40000 ALTER TABLE `questionnaire_forms` DISABLE KEYS */;
INSERT INTO `questionnaire_forms` VALUES (1,'Cuestionario adaptado de investigación','adapted',1,'activo','2026-10-26 15:25:00','2026-10-26 15:25:00'),(2,'Cuestionario anterior de investigación','legacy',1,'activo','2026-10-26 15:25:00','2026-10-26 15:25:00');
/*!40000 ALTER TABLE `questionnaire_forms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `questionnaire_options`
--

DROP TABLE IF EXISTS `questionnaire_options`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `questionnaire_options` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `questionnaire_question_id` bigint unsigned NOT NULL,
  `valor` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `etiqueta` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `orden` smallint unsigned NOT NULL DEFAULT '0',
  `activa` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `questionnaire_options_questionnaire_question_id_foreign` (`questionnaire_question_id`),
  CONSTRAINT `questionnaire_options_questionnaire_question_id_foreign` FOREIGN KEY (`questionnaire_question_id`) REFERENCES `questionnaire_questions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `questionnaire_options`
--

LOCK TABLES `questionnaire_options` WRITE;
/*!40000 ALTER TABLE `questionnaire_options` DISABLE KEYS */;
/*!40000 ALTER TABLE `questionnaire_options` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `questionnaire_questions`
--

DROP TABLE IF EXISTS `questionnaire_questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `questionnaire_questions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `questionnaire_form_id` bigint unsigned NOT NULL,
  `variable` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pregunta` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_respuesta` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'text',
  `seccion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `orden` smallint unsigned NOT NULL DEFAULT '0',
  `obligatoria` tinyint(1) NOT NULL DEFAULT '0',
  `activa` tinyint(1) NOT NULL DEFAULT '1',
  `configuracion` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `questionnaire_questions_questionnaire_form_id_variable_unique` (`questionnaire_form_id`,`variable`),
  CONSTRAINT `questionnaire_questions_questionnaire_form_id_foreign` FOREIGN KEY (`questionnaire_form_id`) REFERENCES `questionnaire_forms` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `questionnaire_questions`
--

LOCK TABLES `questionnaire_questions` WRITE;
/*!40000 ALTER TABLE `questionnaire_questions` DISABLE KEYS */;
INSERT INTO `questionnaire_questions` VALUES (1,1,'interviewee_name','Nombre de la persona entrevistada','text','identificacion',1,1,1,NULL,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(2,1,'interviewee_rut','RUT de la persona entrevistada','text','identificacion',2,0,1,NULL,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(3,1,'witness_type','Tipo de testigo','text','identificacion',3,1,1,NULL,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(4,1,'interviewee_role','Cargo o función','text','identificacion',4,0,1,NULL,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(5,1,'interviewee_company','Empresa o establecimiento','text','identificacion',5,0,1,NULL,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(6,1,'interview_date','Fecha de entrevista','text','identificacion',6,0,1,NULL,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(7,1,'interview_start_time','Hora de inicio','text','identificacion',7,0,1,NULL,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(8,1,'interview_location','Lugar o modalidad de la entrevista','text','identificacion',8,0,1,NULL,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(9,1,'statement','Relato libre o información adicional','text','cuestionario',9,0,1,NULL,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(10,1,'interview_observations','Observaciones de la entrevista','text','cuestionario',10,0,1,NULL,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(11,1,'chronology_detail','Detalle cronológico','multiple','cuestionario',11,0,1,NULL,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(12,1,'chronology_not_applicable','Hecho cronológico no aplicable','multiple','cuestionario',12,0,1,NULL,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(13,1,'task_own_job','La tarea correspondía a su trabajo','text','cuestionario',13,0,1,NULL,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(14,1,'task_habitual','La tarea era habitual','text','cuestionario',14,0,1,NULL,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(15,1,'task_frequency','Frecuencia de la tarea','text','cuestionario',15,0,1,NULL,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(16,1,'task_same_way','La tarea se realizaba de la forma habitual','text','cuestionario',16,0,1,NULL,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(17,1,'task_change_reasons','Razones del cambio en la tarea','multiple','cuestionario',17,0,1,NULL,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(18,1,'task_change_other','Otra razón del cambio','text','cuestionario',18,0,1,NULL,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(19,1,'task_usual_accident_possible','El accidente podía ocurrir ejecutando la tarea habitual','text','cuestionario',19,0,1,NULL,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(20,1,'instructions_received','Recibió instrucciones','text','cuestionario',20,0,1,NULL,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(21,1,'instruction_type','Tipo de instrucción','multiple','cuestionario',21,0,1,NULL,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(22,1,'instruction_source','Origen de la instrucción','multiple','cuestionario',22,0,1,NULL,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(23,1,'followed_instructions','Siguió las instrucciones','text','cuestionario',23,0,1,NULL,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(24,1,'ppe_habitual','Uso habitual de elementos de protección personal','text','cuestionario',24,0,1,NULL,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(25,1,'ppe_description','Descripción de EPP','text','cuestionario',25,0,1,NULL,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(26,1,'usual_place','Lugar habitual de la tarea','text','cuestionario',26,0,1,NULL,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(27,1,'different_place_reason','Motivo de cambio de lugar','text','cuestionario',27,0,1,NULL,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(28,1,'usual_time','Horario habitual de la tarea','text','cuestionario',28,0,1,NULL,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(29,1,'different_time_reason','Motivo de cambio de horario','text','cuestionario',29,0,1,NULL,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(30,1,'equipment_used','Uso de equipos o herramientas','text','cuestionario',30,0,1,NULL,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(31,1,'equipment_description','Descripción de equipos','text','cuestionario',31,0,1,NULL,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(32,1,'equipment_conditions','Condiciones de equipos','multiple','cuestionario',32,0,1,NULL,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(33,1,'equipment_other','Otra condición del equipo','text','cuestionario',33,0,1,NULL,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(34,1,'substance_involved','Participación de sustancias o materiales','text','cuestionario',34,0,1,NULL,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(35,1,'substance_types','Tipos de sustancias','multiple','cuestionario',35,0,1,NULL,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(36,1,'substance_habitual','Uso habitual de la sustancia','text','cuestionario',36,0,1,NULL,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(37,1,'substance_reason','Motivo del uso no habitual','text','cuestionario',37,0,1,NULL,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(38,1,'material_conditions','Condiciones de materiales','multiple','cuestionario',38,0,1,NULL,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(39,1,'facility_conditions','Condiciones de instalaciones','multiple','cuestionario',39,0,1,NULL,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(40,1,'time_conditions','Condiciones temporales','multiple','cuestionario',40,0,1,NULL,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(41,2,'interviewee_name','Nombre de la persona entrevistada','text','identificacion',1,1,1,NULL,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(42,2,'interviewee_rut','RUT de la persona entrevistada','text','identificacion',2,0,1,NULL,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(43,2,'witness_type','Tipo de testigo','text','identificacion',3,1,1,NULL,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(44,2,'interviewee_role','Cargo o función','text','identificacion',4,0,1,NULL,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(45,2,'interviewee_company','Empresa o establecimiento','text','identificacion',5,0,1,NULL,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(46,2,'interview_date','Fecha de entrevista','text','identificacion',6,0,1,NULL,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(47,2,'interview_start_time','Hora de inicio','text','identificacion',7,0,1,NULL,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(48,2,'interview_location','Lugar o modalidad de la entrevista','text','identificacion',8,0,1,NULL,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(49,2,'statement','Relato libre o información adicional','text','cuestionario',9,0,1,NULL,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(50,2,'interview_observations','Observaciones de la entrevista','text','cuestionario',10,0,1,NULL,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(51,2,'initial_story','Relato inicial','text','cuestionario',11,0,1,NULL,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(52,2,'environment_conditions','Condiciones del entorno','multiple','cuestionario',12,0,1,NULL,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(53,2,'other_environment_condition','Otra condición del entorno','text','cuestionario',13,0,1,NULL,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(54,2,'ppe_required','La tarea requería EPP','text','cuestionario',14,0,1,NULL,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(55,2,'ppe_used','EPP utilizados','multiple','cuestionario',15,0,1,NULL,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(56,2,'other_ppe','Otro EPP','text','cuestionario',16,0,1,NULL,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(57,2,'instruction_received','Recibió instrucción para realizar la tarea','text','cuestionario',17,0,1,NULL,'2026-10-26 15:25:00','2026-10-26 15:25:00'),(58,2,'instruction_types','Tipos de instrucción recibida','multiple','cuestionario',18,0,1,NULL,'2026-10-26 15:25:00','2026-10-26 15:25:00');
/*!40000 ALTER TABLE `questionnaire_questions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `region`
--

DROP TABLE IF EXISTS `region`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `region` (
  `Id_Region` int NOT NULL AUTO_INCREMENT,
  `Nombre_Region` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`Id_Region`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `region`
--

LOCK TABLES `region` WRITE;
/*!40000 ALTER TABLE `region` DISABLE KEYS */;
/*!40000 ALTER TABLE `region` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `registration_requests`
--

DROP TABLE IF EXISTS `registration_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `registration_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `rut` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pending_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `job_title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `establishment_id` bigint unsigned NOT NULL,
  `requested_role` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `rejection_reason` text COLLATE utf8mb4_unicode_ci,
  `resolved_by` bigint unsigned DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `registration_requests_pending_key_unique` (`pending_key`),
  KEY `registration_requests_establishment_id_foreign` (`establishment_id`),
  KEY `registration_requests_resolved_by_foreign` (`resolved_by`),
  KEY `registration_requests_rut_index` (`rut`),
  KEY `registration_requests_status_index` (`status`),
  CONSTRAINT `registration_requests_establishment_id_foreign` FOREIGN KEY (`establishment_id`) REFERENCES `establishments` (`id`),
  CONSTRAINT `registration_requests_resolved_by_foreign` FOREIGN KEY (`resolved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `registration_requests`
--

LOCK TABLES `registration_requests` WRITE;
/*!40000 ALTER TABLE `registration_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `registration_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `relato_cuestionario`
--

DROP TABLE IF EXISTS `relato_cuestionario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `relato_cuestionario` (
  `Id_Relato` int NOT NULL AUTO_INCREMENT,
  `Id_Caso` int NOT NULL,
  `Relato` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`Id_Relato`),
  KEY `Id_Caso` (`Id_Caso`),
  CONSTRAINT `relato_cuestionario_ibfk_1` FOREIGN KEY (`Id_Caso`) REFERENCES `caso` (`Id_Caso`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `relato_cuestionario`
--

LOCK TABLES `relato_cuestionario` WRITE;
/*!40000 ALTER TABLE `relato_cuestionario` DISABLE KEYS */;
/*!40000 ALTER TABLE `relato_cuestionario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reporte_cumplimiento`
--

DROP TABLE IF EXISTS `reporte_cumplimiento`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reporte_cumplimiento` (
  `Id_Reporte` int NOT NULL AUTO_INCREMENT,
  `Observaciones` text COLLATE utf8mb4_unicode_ci,
  `Fecha_Carga` datetime NOT NULL,
  `Id_Plan` int NOT NULL,
  PRIMARY KEY (`Id_Reporte`),
  KEY `Id_Plan` (`Id_Plan`),
  CONSTRAINT `reporte_cumplimiento_ibfk_1` FOREIGN KEY (`Id_Plan`) REFERENCES `plan_accion` (`Id_Plan`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reporte_cumplimiento`
--

LOCK TABLES `reporte_cumplimiento` WRITE;
/*!40000 ALTER TABLE `reporte_cumplimiento` DISABLE KEYS */;
/*!40000 ALTER TABLE `reporte_cumplimiento` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `respuesta_declaracion`
--

DROP TABLE IF EXISTS `respuesta_declaracion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `respuesta_declaracion` (
  `Id_Respuesta` int NOT NULL AUTO_INCREMENT,
  `Id_Declaracion` int NOT NULL,
  `Id_Pregunta` int NOT NULL,
  `Estado_Respuesta` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Texto_Respuesta` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`Id_Respuesta`),
  UNIQUE KEY `Id_Declaracion` (`Id_Declaracion`,`Id_Pregunta`),
  KEY `Id_Pregunta` (`Id_Pregunta`),
  CONSTRAINT `respuesta_declaracion_ibfk_1` FOREIGN KEY (`Id_Declaracion`) REFERENCES `declaracion` (`Id_Declaracion`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `respuesta_declaracion_ibfk_2` FOREIGN KEY (`Id_Pregunta`) REFERENCES `pregunta_declaracion` (`Id_Pregunta`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `respuesta_declaracion`
--

LOCK TABLES `respuesta_declaracion` WRITE;
/*!40000 ALTER TABLE `respuesta_declaracion` DISABLE KEYS */;
/*!40000 ALTER TABLE `respuesta_declaracion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `respuesta_opcion`
--

DROP TABLE IF EXISTS `respuesta_opcion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `respuesta_opcion` (
  `Id_Respuesta` int NOT NULL,
  `Id_Opcion` int NOT NULL,
  `Detalle` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`Id_Respuesta`,`Id_Opcion`),
  KEY `Id_Opcion` (`Id_Opcion`),
  CONSTRAINT `respuesta_opcion_ibfk_1` FOREIGN KEY (`Id_Respuesta`) REFERENCES `respuesta_declaracion` (`Id_Respuesta`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `respuesta_opcion_ibfk_2` FOREIGN KEY (`Id_Opcion`) REFERENCES `opcion_pregunta` (`Id_Opcion`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `respuesta_opcion`
--

LOCK TABLES `respuesta_opcion` WRITE;
/*!40000 ALTER TABLE `respuesta_opcion` DISABLE KEYS */;
/*!40000 ALTER TABLE `respuesta_opcion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rol`
--

DROP TABLE IF EXISTS `rol`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rol` (
  `Id_Rol` int NOT NULL AUTO_INCREMENT,
  `Nombre_Rol` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`Id_Rol`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rol`
--

LOCK TABLES `rol` WRITE;
/*!40000 ALTER TABLE `rol` DISABLE KEYS */;
/*!40000 ALTER TABLE `rol` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rol_permisos`
--

DROP TABLE IF EXISTS `rol_permisos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rol_permisos` (
  `Id_Rol_Per` int NOT NULL AUTO_INCREMENT,
  `Id_Rol` int NOT NULL,
  `Id_Permiso` int NOT NULL,
  PRIMARY KEY (`Id_Rol_Per`),
  UNIQUE KEY `Id_Rol` (`Id_Rol`,`Id_Permiso`),
  KEY `Id_Permiso` (`Id_Permiso`),
  CONSTRAINT `rol_permisos_ibfk_1` FOREIGN KEY (`Id_Rol`) REFERENCES `rol` (`Id_Rol`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `rol_permisos_ibfk_2` FOREIGN KEY (`Id_Permiso`) REFERENCES `permisos` (`Id_Permiso`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rol_permisos`
--

LOCK TABLES `rol_permisos` WRITE;
/*!40000 ALTER TABLE `rol_permisos` DISABLE KEYS */;
/*!40000 ALTER TABLE `rol_permisos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rol_usuario`
--

DROP TABLE IF EXISTS `rol_usuario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rol_usuario` (
  `Id_Rol_Us` int NOT NULL AUTO_INCREMENT,
  `Id_Rol` int NOT NULL,
  `Rut_Usuario` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`Id_Rol_Us`),
  UNIQUE KEY `Id_Rol` (`Id_Rol`,`Rut_Usuario`),
  KEY `Rut_Usuario` (`Rut_Usuario`),
  CONSTRAINT `rol_usuario_ibfk_1` FOREIGN KEY (`Id_Rol`) REFERENCES `rol` (`Id_Rol`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `rol_usuario_ibfk_2` FOREIGN KEY (`Rut_Usuario`) REFERENCES `usuario` (`Rut_Usuario`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rol_usuario`
--

LOCK TABLES `rol_usuario` WRITE;
/*!40000 ALTER TABLE `rol_usuario` DISABLE KEYS */;
/*!40000 ALTER TABLE `rol_usuario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_user`
--

DROP TABLE IF EXISTS `role_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_user` (
  `role_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`user_id`),
  KEY `role_user_user_id_foreign` (`user_id`),
  CONSTRAINT `role_user_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_user`
--

LOCK TABLES `role_user` WRITE;
/*!40000 ALTER TABLE `role_user` DISABLE KEYS */;
INSERT INTO `role_user` VALUES (2,1),(1,2),(3,3),(4,4),(5,5),(6,6);
/*!40000 ALTER TABLE `role_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'delegado','Delegado de Seguridad','2026-10-26 15:24:58','2026-10-26 15:24:58'),(2,'cphs','Comité Paritario','2026-10-26 15:24:58','2026-10-26 15:24:58'),(3,'prevencion','Prevencionista','2026-10-26 15:24:58','2026-10-26 15:24:58'),(4,'jefatura','Jefatura','2026-10-26 15:24:58','2026-10-26 15:24:58'),(5,'alta-direccion','Alta Dirección','2026-10-26 15:24:58','2026-10-26 15:24:58'),(6,'administrador','Administrador','2026-10-26 15:24:58','2026-10-26 15:24:58');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
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

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `solicitud_acceso`
--

DROP TABLE IF EXISTS `solicitud_acceso`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `solicitud_acceso` (
  `Id_Solicitud` int NOT NULL AUTO_INCREMENT,
  `Rut_Solicitante` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Nombre_Solicitante` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Correo_Solicitante` varchar(254) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Id_Rol_Solicitado` int NOT NULL,
  `Motivo_Solicitud` text COLLATE utf8mb4_unicode_ci,
  `Fecha_Solicitud` datetime NOT NULL,
  `Estado_Solicitud` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Fecha_Resolucion` datetime DEFAULT NULL,
  `Observacion_Resolucion` text COLLATE utf8mb4_unicode_ci,
  `Rut_Usuario_Validador` varchar(12) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Rut_Usuario_Beneficiario` varchar(12) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`Id_Solicitud`),
  KEY `Id_Rol_Solicitado` (`Id_Rol_Solicitado`),
  KEY `Rut_Usuario_Validador` (`Rut_Usuario_Validador`),
  KEY `Rut_Usuario_Beneficiario` (`Rut_Usuario_Beneficiario`),
  CONSTRAINT `solicitud_acceso_ibfk_1` FOREIGN KEY (`Id_Rol_Solicitado`) REFERENCES `rol` (`Id_Rol`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `solicitud_acceso_ibfk_2` FOREIGN KEY (`Rut_Usuario_Validador`) REFERENCES `usuario` (`Rut_Usuario`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `solicitud_acceso_ibfk_3` FOREIGN KEY (`Rut_Usuario_Beneficiario`) REFERENCES `usuario` (`Rut_Usuario`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `solicitud_acceso`
--

LOCK TABLES `solicitud_acceso` WRITE;
/*!40000 ALTER TABLE `solicitud_acceso` DISABLE KEYS */;
/*!40000 ALTER TABLE `solicitud_acceso` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `testigo`
--

DROP TABLE IF EXISTS `testigo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `testigo` (
  `Rut_Testigo` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Nombre_Testigo` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Establecimiento` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Cargo` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`Rut_Testigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `testigo`
--

LOCK TABLES `testigo` WRITE;
/*!40000 ALTER TABLE `testigo` DISABLE KEYS */;
/*!40000 ALTER TABLE `testigo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `testigo_caso`
--

DROP TABLE IF EXISTS `testigo_caso`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `testigo_caso` (
  `Id_Testigo_Caso` int NOT NULL AUTO_INCREMENT,
  `Id_Caso` int NOT NULL,
  `Rut_Testigo` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Fecha_Registro` datetime NOT NULL,
  PRIMARY KEY (`Id_Testigo_Caso`),
  UNIQUE KEY `Id_Caso` (`Id_Caso`,`Rut_Testigo`),
  KEY `Rut_Testigo` (`Rut_Testigo`),
  CONSTRAINT `testigo_caso_ibfk_1` FOREIGN KEY (`Id_Caso`) REFERENCES `caso` (`Id_Caso`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `testigo_caso_ibfk_2` FOREIGN KEY (`Rut_Testigo`) REFERENCES `testigo` (`Rut_Testigo`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `testigo_caso`
--

LOCK TABLES `testigo_caso` WRITE;
/*!40000 ALTER TABLE `testigo_caso` DISABLE KEYS */;
/*!40000 ALTER TABLE `testigo_caso` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tipo_caso`
--

DROP TABLE IF EXISTS `tipo_caso`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tipo_caso` (
  `Id_Clasificacion_Caso` int NOT NULL AUTO_INCREMENT,
  `Nombre_Tipo_Caso` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`Id_Clasificacion_Caso`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tipo_caso`
--

LOCK TABLES `tipo_caso` WRITE;
/*!40000 ALTER TABLE `tipo_caso` DISABLE KEYS */;
/*!40000 ALTER TABLE `tipo_caso` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tipo_entrevistado`
--

DROP TABLE IF EXISTS `tipo_entrevistado`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tipo_entrevistado` (
  `Id_Tipo_Entrevistado` int NOT NULL AUTO_INCREMENT,
  `Nombre_Tipo` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`Id_Tipo_Entrevistado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tipo_entrevistado`
--

LOCK TABLES `tipo_entrevistado` WRITE;
/*!40000 ALTER TABLE `tipo_entrevistado` DISABLE KEYS */;
/*!40000 ALTER TABLE `tipo_entrevistado` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `trabajador_accidentado_enfermo`
--

DROP TABLE IF EXISTS `trabajador_accidentado_enfermo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `trabajador_accidentado_enfermo` (
  `RUT_CI_Trabajador` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Apellido` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Fecha_Nacimiento` date DEFAULT NULL,
  `Sexo` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Lugar_Nacimiento` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Nacionalidad` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Profesion_U_Oficio` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Fecha_Ingreso` date DEFAULT NULL,
  `Antiguedad_En_El_Puesto` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Fecha_Contrato` date DEFAULT NULL,
  `Categoria_Ocupacional` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Direccion_Domicilio` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Telefono_Personal` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Persona_De_Contacto` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Telefono_De_Contacto` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Id_Comuna` int NOT NULL,
  `Id_Pueblo_Originario` int DEFAULT NULL,
  PRIMARY KEY (`RUT_CI_Trabajador`),
  KEY `Id_Comuna` (`Id_Comuna`),
  KEY `Id_Pueblo_Originario` (`Id_Pueblo_Originario`),
  CONSTRAINT `trabajador_accidentado_enfermo_ibfk_1` FOREIGN KEY (`Id_Comuna`) REFERENCES `comuna` (`Id_Comuna`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `trabajador_accidentado_enfermo_ibfk_2` FOREIGN KEY (`Id_Pueblo_Originario`) REFERENCES `pueblos_originarios` (`Id_Pueblo_Originario`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `trabajador_accidentado_enfermo`
--

LOCK TABLES `trabajador_accidentado_enfermo` WRITE;
/*!40000 ALTER TABLE `trabajador_accidentado_enfermo` DISABLE KEYS */;
/*!40000 ALTER TABLE `trabajador_accidentado_enfermo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `rut` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `job_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_rut_unique` (`rut`),
  KEY `users_status_index` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'44444444-4','Comité Paritario de Demostración','comite.demo@giatep.local','Representante del Comité Paritario',NULL,'active',NULL,'$2y$12$zU3QeQZbncwMgTkUw5Bp/.HlUGLnxNc0YdI0regv2p4bra1BWlds2',NULL,'2026-10-26 15:24:58','2026-10-26 15:24:58'),(2,'55555555-5','Delegado de Seguridad de Demostración','delegado.demo@giatep.local','Delegado de Seguridad',NULL,'active',NULL,'$2y$12$/qrCUYz.pqi4AEKM7D0O7O32NqvFzGLK962c8Kd9q4cn87VVvsGAS',NULL,'2026-10-26 15:24:59','2026-10-26 15:24:59'),(3,'11111111-1','Prevencionista de Demostración','prevencionista.demo@giatep.local','Prevencionista',NULL,'active',NULL,'$2y$12$hclwWVn45eEBNJ6.FD0IEuhB.Hluv8g1SZ9HD.lkxpJKclwNaliS.',NULL,'2026-10-26 15:24:59','2026-10-26 15:24:59'),(4,'22222222-2','Jefatura de Demostración','jefatura.demo@giatep.local','Jefatura',NULL,'active',NULL,'$2y$12$bLiAmdnOZEz2dRBvfF5UZe5vNUv.1kbvMNMCnAAA.7rX4iEWeyCq.',NULL,'2026-10-26 15:24:59','2026-10-26 15:24:59'),(5,'33333333-3','Alta Dirección de Demostración','alta.direccion.demo@giatep.local','Alta Dirección',NULL,'active',NULL,'$2y$12$4moMpqQeYCFrptVfsz95SOWPZRsny8cApCQV1TCNxCcRdTUuOs4cS',NULL,'2026-10-26 15:24:59','2026-10-26 15:24:59'),(6,'21200314-K','Administrador de Demostración','admin.demo@giatep.local','Administración de plataforma',NULL,'active',NULL,'$2y$12$KZumJsDTmNAbOjTkbrnmjOfGqJ9Uun5VTTkCMhstMPu2oNVU4I4Li',NULL,'2026-10-26 15:24:59','2026-10-26 15:24:59');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuario`
--

DROP TABLE IF EXISTS `usuario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuario` (
  `Rut_Usuario` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Contrasena_Hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Correo` varchar(254) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Id_Dependencia` int NOT NULL,
  PRIMARY KEY (`Rut_Usuario`),
  KEY `Id_Dependencia` (`Id_Dependencia`),
  CONSTRAINT `usuario_ibfk_1` FOREIGN KEY (`Id_Dependencia`) REFERENCES `dependencia` (`Id_Dependencia`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuario`
--

LOCK TABLES `usuario` WRITE;
/*!40000 ALTER TABLE `usuario` DISABLE KEYS */;
/*!40000 ALTER TABLE `usuario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'giatep_db'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-26  9:31:09
