/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `app_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `app_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'text',
  `group` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'general',
  `label` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `app_settings_key_unique` (`key`),
  KEY `app_settings_group_key_index` (`group`,`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
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
DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `icon` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_path` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `color` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#6B7280',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `events` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `category` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'kamtibmas',
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `color` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#3B82F6',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `events_start_date_end_date_index` (`start_date`,`end_date`),
  KEY `events_is_active_index` (`is_active`),
  KEY `events_category_index` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
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
DROP TABLE IF EXISTS `indas_analysis_results`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `indas_analysis_results` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kabupaten_kota_id` bigint unsigned NOT NULL,
  `month` tinyint NOT NULL,
  `year` year NOT NULL,
  `economic_score` decimal(15,2) NOT NULL DEFAULT '0.00',
  `tourism_score` decimal(15,2) NOT NULL DEFAULT '0.00',
  `social_score` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_score` decimal(15,2) NOT NULL DEFAULT '0.00',
  `economic_trend` decimal(6,2) DEFAULT NULL,
  `tourism_trend` decimal(6,2) DEFAULT NULL,
  `social_trend` decimal(6,2) DEFAULT NULL,
  `total_trend` decimal(6,2) DEFAULT NULL,
  `calculation_details` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `indas_analysis_unique` (`kabupaten_kota_id`,`month`,`year`),
  KEY `indas_analysis_results_month_year_index` (`month`,`year`),
  KEY `indas_analysis_results_total_score_index` (`total_score`),
  CONSTRAINT `indas_analysis_results_kabupaten_kota_id_foreign` FOREIGN KEY (`kabupaten_kota_id`) REFERENCES `kabupaten_kota` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `indas_indicator_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `indas_indicator_types` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` enum('ekonomi','pariwisata','sosial') COLLATE utf8mb4_unicode_ci NOT NULL,
  `unit` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unit',
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `indas_indicator_types_category_is_active_index` (`category`,`is_active`),
  KEY `indas_indicator_types_is_active_index` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `indas_monthly_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `indas_monthly_data` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kabupaten_kota_id` bigint unsigned NOT NULL,
  `indicator_type_id` bigint unsigned NOT NULL,
  `value` decimal(15,2) NOT NULL,
  `month` tinyint NOT NULL,
  `year` year NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `indas_monthly_unique` (`kabupaten_kota_id`,`indicator_type_id`,`month`,`year`),
  KEY `indas_monthly_data_indicator_type_id_foreign` (`indicator_type_id`),
  KEY `indas_monthly_data_user_id_foreign` (`user_id`),
  KEY `indas_monthly_data_month_year_index` (`month`,`year`),
  CONSTRAINT `indas_monthly_data_indicator_type_id_foreign` FOREIGN KEY (`indicator_type_id`) REFERENCES `indas_indicator_types` (`id`) ON DELETE CASCADE,
  CONSTRAINT `indas_monthly_data_kabupaten_kota_id_foreign` FOREIGN KEY (`kabupaten_kota_id`) REFERENCES `kabupaten_kota` (`id`) ON DELETE CASCADE,
  CONSTRAINT `indas_monthly_data_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
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
DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jumlah_suara`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jumlah_suara` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `partai_id` bigint unsigned NOT NULL,
  `tahun_pemilu` year NOT NULL,
  `jumlah_suara` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `jumlah_suara_partai_id_tahun_pemilu_unique` (`partai_id`,`tahun_pemilu`),
  KEY `jumlah_suara_tahun_pemilu_jumlah_suara_index` (`tahun_pemilu`,`jumlah_suara`),
  CONSTRAINT `jumlah_suara_partai_id_foreign` FOREIGN KEY (`partai_id`) REFERENCES `partai_politik` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `kabupaten_kota`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kabupaten_kota` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `provinsi_id` bigint unsigned NOT NULL,
  `nama` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `kabupaten_kota_provinsi_id_foreign` (`provinsi_id`),
  CONSTRAINT `kabupaten_kota_provinsi_id_foreign` FOREIGN KEY (`provinsi_id`) REFERENCES `provinsi` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `kecamatan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kecamatan` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kabupaten_kota_id` bigint unsigned NOT NULL,
  `nama` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `kecamatan_kabupaten_kota_id_foreign` (`kabupaten_kota_id`),
  CONSTRAINT `kecamatan_kabupaten_kota_id_foreign` FOREIGN KEY (`kabupaten_kota_id`) REFERENCES `kabupaten_kota` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `menu_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `menu_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `path` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int NOT NULL DEFAULT '0',
  `parent_id` bigint unsigned DEFAULT NULL,
  `admin_only` tinyint(1) NOT NULL DEFAULT '0',
  `permissions` json DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `menu_items_parent_id_foreign` (`parent_id`),
  KEY `menu_items_is_active_sort_order_index` (`is_active`,`sort_order`),
  CONSTRAINT `menu_items_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `monitoring_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `monitoring_data` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `provinsi_id` bigint unsigned NOT NULL,
  `kabupaten_kota_id` bigint unsigned NOT NULL,
  `kecamatan_id` bigint unsigned DEFAULT NULL,
  `category_id` bigint unsigned NOT NULL,
  `sub_category_id` bigint unsigned NOT NULL,
  `latitude` decimal(10,7) NOT NULL,
  `longitude` decimal(10,7) NOT NULL,
  `title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `sumber_berita` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jumlah_terdampak` int DEFAULT NULL,
  `additional_data` json DEFAULT NULL,
  `severity_level` enum('low','medium','high','critical') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medium',
  `status` enum('active','resolved','monitoring','archived') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `incident_date` timestamp NULL DEFAULT NULL,
  `source` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gallery` json DEFAULT NULL,
  `video_path` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `monitoring_data_kabupaten_kota_id_foreign` (`kabupaten_kota_id`),
  KEY `monitoring_data_kecamatan_id_foreign` (`kecamatan_id`),
  KEY `monitoring_data_sub_category_id_foreign` (`sub_category_id`),
  KEY `monitoring_data_category_id_sub_category_id_index` (`category_id`,`sub_category_id`),
  KEY `monitoring_data_provinsi_id_kabupaten_kota_id_kecamatan_id_index` (`provinsi_id`,`kabupaten_kota_id`,`kecamatan_id`),
  KEY `monitoring_data_incident_date_status_index` (`incident_date`,`status`),
  CONSTRAINT `monitoring_data_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `monitoring_data_kabupaten_kota_id_foreign` FOREIGN KEY (`kabupaten_kota_id`) REFERENCES `kabupaten_kota` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `monitoring_data_kecamatan_id_foreign` FOREIGN KEY (`kecamatan_id`) REFERENCES `kecamatan` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `monitoring_data_provinsi_id_foreign` FOREIGN KEY (`provinsi_id`) REFERENCES `provinsi` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `monitoring_data_sub_category_id_foreign` FOREIGN KEY (`sub_category_id`) REFERENCES `sub_categories` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `partai_politik`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `partai_politik` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nama_partai` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `singkatan` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nomor_urut` int NOT NULL,
  `logo_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `foto_ketua` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama_ketua` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_aktif` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `partai_politik_nomor_urut_unique` (`nomor_urut`),
  KEY `partai_politik_status_aktif_index` (`status_aktif`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `provinsi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `provinsi` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nama` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sembako`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sembako` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nama_komoditas` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `satuan` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `harga` decimal(10,2) NOT NULL,
  `kabupaten_kota_id` bigint unsigned NOT NULL,
  `tanggal_pencatatan` date NOT NULL,
  `keterangan` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sembako_kabupaten_kota_id_tanggal_pencatatan_index` (`kabupaten_kota_id`,`tanggal_pencatatan`),
  KEY `sembako_nama_komoditas_tanggal_pencatatan_index` (`nama_komoditas`,`tanggal_pencatatan`),
  CONSTRAINT `sembako_kabupaten_kota_id_foreign` FOREIGN KEY (`kabupaten_kota_id`) REFERENCES `kabupaten_kota` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
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
DROP TABLE IF EXISTS `sub_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sub_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint unsigned NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `icon` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_path` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `color` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sub_categories_slug_unique` (`slug`),
  KEY `sub_categories_category_id_foreign` (`category_id`),
  CONSTRAINT `sub_categories_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','user') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `provinsi_id` bigint unsigned DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `last_login_at` timestamp NULL DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_provinsi_id_foreign` (`provinsi_id`),
  CONSTRAINT `users_provinsi_id_foreign` FOREIGN KEY (`provinsi_id`) REFERENCES `provinsi` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (1,'0001_01_01_000000_create_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (2,'0001_01_01_000001_create_cache_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (3,'0001_01_01_000002_create_jobs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (4,'0001_01_01_000003_create_provinsi_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (5,'0001_01_01_000004_create_kabupaten_kota_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (6,'0001_01_01_000005_create_kecamatan_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (7,'0001_01_01_000007_create_categories_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (8,'0001_01_01_000008_create_sub_categories_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (9,'0001_01_01_000009_create_monitoring_data_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (10,'2025_08_02_060430_create_app_settings_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (13,'2025_08_09_035228_create_events_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (14,'2025_08_09_040135_add_category_to_events_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (15,'2025_08_09_131610_add_image_fields_to_categories_and_sub_categories_tables',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (16,'2025_08_09_180307_add_role_to_users_table',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (17,'2025_08_10_101238_add_provinsi_id_to_users_table',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (18,'2025_08_10_112327_create_indas_regions_table',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (19,'2025_08_10_112332_create_indas_indicator_types_table',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (20,'2025_08_10_112336_create_indas_monthly_data_table',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (21,'2025_08_10_112340_create_indas_analysis_results_table',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (22,'2025_08_10_115043_update_indas_tables_use_kabupaten_kota',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (23,'2025_08_11_062856_create_partai_politik_table',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (24,'2025_08_11_063730_create_jumlah_suara_table',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (25,'2025_08_11_065406_update_partai_politik_logo_field_to_upload',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (26,'2025_08_11_075214_create_sembako_table',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (27,'2025_08_11_141542_add_chairman_fields_to_partai_politik_table',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (28,'2025_08_12_091838_add_coordinates_to_provinsi_table',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (29,'2025_08_12_101426_add_sumber_berita_to_monitoring_data_table',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (30,'2025_08_12_143212_add_video_path_to_monitoring_data_table',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (31,'2025_08_13_155148_make_kecamatan_id_nullable_in_monitoring_data_table',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (32,'2025_08_13_213405_drop_weight_factor_from_indas_indicator_types',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (33,'2025_08_13_213440_modify_value_column_indas_monthly_data',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (34,'2025_08_13_213533_modify_score_columns_indas_analysis_results',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (35,'2025_08_13_221106_create_menu_items_table',4);
