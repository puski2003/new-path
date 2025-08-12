-- --------------------------------------------------------
-- Host:                         new-path-pasidurajapaksha202-728e.a.aivencloud.com
-- Server version:               8.0.45 - Source distribution
-- Server OS:                    Linux
-- HeidiSQL Version:             12.7.0.6850
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for new_path
CREATE DATABASE IF NOT EXISTS `new_path` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `new_path`;

-- Dumping structure for table new_path.achievements
CREATE TABLE IF NOT EXISTS `achievements` (
  `achievement_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `achievement_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `badge_icon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `days_required` int DEFAULT NULL,
  `earned_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`achievement_id`),
  KEY `idx_user` (`user_id`),
  CONSTRAINT `fk_achievement_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path.achievements: ~2 rows (approximately)
INSERT INTO `achievements` (`achievement_id`, `user_id`, `achievement_type`, `title`, `description`, `badge_icon`, `days_required`, `earned_at`) VALUES
	(1, 2, '7_days_sober', '7 Days Sober', NULL, NULL, 7, '2025-12-09 15:01:17'),
	(2, 2, '30_days_sober', 'First Month Sober', NULL, NULL, 30, '2026-01-01 15:01:17');

-- Dumping structure for table new_path.admin
CREATE TABLE IF NOT EXISTS `admin` (
  `admin_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `full_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `permissions` json DEFAULT NULL,
  `is_super_admin` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`admin_id`),
  UNIQUE KEY `idx_user` (`user_id`),
  CONSTRAINT `fk_admin_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path.admin: ~2 rows (approximately)
INSERT INTO `admin` (`admin_id`, `user_id`, `full_name`, `permissions`, `is_super_admin`, `created_at`, `updated_at`) VALUES
	(1, 1, 'System Administrator', NULL, 1, '2026-01-02 18:00:38', '2026-01-02 18:00:38'),
	(2, 2, 'System Administrator', NULL, 1, '2026-01-04 17:50:30', '2026-01-04 17:50:39');

-- Dumping structure for table new_path.audit_logs
CREATE TABLE IF NOT EXISTS `audit_logs` (
  `log_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `action` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `entity_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `entity_id` int DEFAULT NULL,
  `old_values` json DEFAULT NULL,
  `new_values` json DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_action` (`action`),
  KEY `idx_entity` (`entity_type`,`entity_id`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path.audit_logs: ~0 rows (approximately)

-- Dumping structure for table new_path.booking_holds
CREATE TABLE IF NOT EXISTS `booking_holds` (
  `hold_id` int NOT NULL AUTO_INCREMENT,
  `counselor_id` int NOT NULL,
  `user_id` int NOT NULL,
  `slot_datetime` datetime NOT NULL,
  `duration_minutes` int DEFAULT '60',
  `status` enum('held','confirmed','released') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'held',
  `held_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` timestamp NOT NULL,
  PRIMARY KEY (`hold_id`),
  KEY `idx_counselor_slot` (`counselor_id`,`slot_datetime`),
  KEY `idx_status` (`status`),
  KEY `fk_hold_user` (`user_id`),
  CONSTRAINT `fk_hold_counselor` FOREIGN KEY (`counselor_id`) REFERENCES `counselors` (`counselor_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_hold_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=76 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path.booking_holds: ~72 rows (approximately)
INSERT INTO `booking_holds` (`hold_id`, `counselor_id`, `user_id`, `slot_datetime`, `duration_minutes`, `status`, `held_at`, `expires_at`) VALUES
	(1, 1, 2, '2026-03-30 15:00:00', 60, 'released', '2026-03-25 08:02:09', '2026-03-25 08:17:09'),
	(2, 1, 2, '2026-03-30 13:00:00', 60, 'released', '2026-03-25 08:23:33', '2026-03-25 08:38:33'),
	(3, 1, 2, '2026-03-30 13:00:00', 60, 'released', '2026-03-25 08:23:56', '2026-03-25 08:38:56'),
	(4, 1, 2, '2026-03-30 09:00:00', 60, 'released', '2026-03-28 10:17:19', '2026-03-28 10:32:19'),
	(5, 1, 2, '2026-03-30 11:00:00', 60, 'released', '2026-03-28 10:21:36', '2026-03-28 10:36:36'),
	(6, 1, 2, '2026-03-30 13:00:00', 60, 'released', '2026-03-28 10:24:32', '2026-03-28 10:39:32'),
	(7, 1, 2, '2026-03-30 12:00:00', 60, 'released', '2026-03-28 10:26:17', '2026-03-28 10:41:17'),
	(8, 1, 2, '2026-03-30 15:00:00', 60, 'released', '2026-03-28 10:26:41', '2026-03-28 10:41:41'),
	(9, 1, 2, '2026-03-30 10:00:00', 60, 'released', '2026-03-28 10:27:34', '2026-03-28 10:42:34'),
	(10, 1, 2, '2026-03-30 09:00:00', 60, 'released', '2026-03-28 10:32:52', '2026-03-28 10:47:52'),
	(11, 1, 2, '2026-03-30 13:00:00', 60, 'released', '2026-03-28 10:47:25', '2026-03-28 11:02:25'),
	(12, 1, 2, '2026-03-30 09:00:00', 60, 'released', '2026-03-28 10:50:17', '2026-03-28 11:05:17'),
	(13, 1, 2, '2026-03-30 11:00:00', 60, 'released', '2026-03-28 10:51:45', '2026-03-28 11:06:45'),
	(14, 1, 2, '2026-03-30 10:00:00', 60, 'released', '2026-03-28 10:52:51', '2026-03-28 11:07:51'),
	(15, 1, 2, '2026-03-30 12:00:00', 60, 'released', '2026-03-28 10:55:12', '2026-03-28 11:10:12'),
	(16, 1, 2, '2026-03-30 14:00:00', 60, 'released', '2026-03-28 10:55:20', '2026-03-28 11:10:20'),
	(17, 1, 2, '2026-03-30 15:00:00', 60, 'released', '2026-03-28 10:55:25', '2026-03-28 11:10:25'),
	(18, 1, 2, '2026-03-30 16:00:00', 60, 'released', '2026-03-28 10:55:32', '2026-03-28 11:10:32'),
	(19, 1, 2, '2026-03-30 09:00:00', 60, 'released', '2026-03-28 11:05:42', '2026-03-28 11:20:42'),
	(20, 1, 2, '2026-03-30 09:00:00', 60, 'released', '2026-03-28 12:25:59', '2026-03-28 12:40:59'),
	(21, 1, 2, '2026-03-30 12:00:00', 60, 'released', '2026-03-28 12:32:37', '2026-03-28 12:47:37'),
	(22, 1, 2, '2026-03-30 13:00:00', 60, 'released', '2026-03-28 12:33:56', '2026-03-28 12:48:56'),
	(23, 1, 2, '2026-03-30 10:00:00', 60, 'released', '2026-03-28 12:34:07', '2026-03-28 12:49:07'),
	(24, 1, 2, '2026-03-30 11:00:00', 60, 'released', '2026-03-28 12:37:43', '2026-03-28 12:52:43'),
	(25, 1, 2, '2026-03-30 11:00:00', 60, 'released', '2026-03-28 12:37:49', '2026-03-28 12:52:49'),
	(26, 1, 2, '2026-03-30 16:00:00', 60, 'released', '2026-03-28 12:37:57', '2026-03-28 12:52:57'),
	(27, 1, 2, '2026-03-30 11:00:00', 60, 'released', '2026-03-28 12:38:31', '2026-03-28 12:53:31'),
	(28, 1, 2, '2026-03-30 11:00:00', 60, 'released', '2026-03-28 12:38:49', '2026-03-28 12:53:49'),
	(29, 1, 2, '2026-03-30 14:00:00', 60, 'released', '2026-03-28 12:42:35', '2026-03-28 12:57:35'),
	(30, 1, 2, '2026-03-30 11:00:00', 60, 'confirmed', '2026-03-28 12:44:08', '2026-03-28 12:59:08'),
	(31, 1, 2, '2026-03-30 09:00:00', 60, 'confirmed', '2026-03-28 12:50:42', '2026-03-28 13:05:42'),
	(32, 1, 2, '2026-03-30 13:00:00', 60, 'released', '2026-03-28 12:52:35', '2026-03-28 13:07:35'),
	(33, 1, 2, '2026-03-30 16:00:00', 60, 'released', '2026-03-28 12:56:44', '2026-03-28 13:11:44'),
	(34, 1, 2, '2026-03-30 10:00:00', 60, 'released', '2026-03-28 13:00:48', '2026-03-28 13:15:48'),
	(35, 1, 2, '2026-03-30 16:00:00', 60, 'released', '2026-03-28 13:03:40', '2026-03-28 13:18:40'),
	(36, 1, 2, '2026-03-30 12:00:00', 60, 'released', '2026-03-28 13:11:18', '2026-03-28 13:26:18'),
	(37, 1, 2, '2026-03-30 13:00:00', 60, 'released', '2026-03-28 14:57:00', '2026-03-28 15:12:00'),
	(38, 1, 2, '2026-03-30 10:00:00', 60, 'released', '2026-03-28 16:17:42', '2026-03-28 16:32:42'),
	(39, 1, 2, '2026-03-30 10:00:00', 60, 'confirmed', '2026-03-28 16:22:29', '2026-03-28 16:37:29'),
	(40, 1, 2, '2026-03-30 12:00:00', 60, 'confirmed', '2026-03-28 16:47:39', '2026-03-28 17:02:39'),
	(41, 1, 2, '2026-03-30 13:00:00', 60, 'confirmed', '2026-03-28 16:51:07', '2026-03-28 17:06:07'),
	(42, 1, 2, '2026-03-30 16:00:00', 60, 'confirmed', '2026-03-28 16:59:28', '2026-03-28 17:14:28'),
	(43, 1, 2, '2026-03-30 14:00:00', 60, 'confirmed', '2026-03-28 17:30:10', '2026-03-28 17:45:10'),
	(44, 1, 2, '2026-03-30 15:00:00', 60, 'confirmed', '2026-03-28 17:58:05', '2026-03-28 18:13:05'),
	(45, 1, 2, '2026-04-06 09:00:00', 60, 'released', '2026-03-30 13:25:56', '2026-03-30 13:40:56'),
	(46, 1, 2, '2026-04-06 10:00:00', 60, 'confirmed', '2026-03-30 13:26:47', '2026-03-30 13:41:47'),
	(47, 1, 2, '2026-04-06 11:00:00', 60, 'confirmed', '2026-03-30 13:30:36', '2026-03-30 13:45:36'),
	(48, 1, 2, '2026-04-06 14:00:00', 60, 'confirmed', '2026-03-30 13:36:42', '2026-03-30 13:51:42'),
	(49, 1, 2, '2026-04-06 13:00:00', 60, 'confirmed', '2026-03-30 17:55:39', '2026-03-30 18:10:39'),
	(50, 1, 2, '2026-04-13 16:00:00', 60, 'confirmed', '2026-03-30 17:58:10', '2026-03-30 18:13:10'),
	(51, 1, 2, '2026-04-06 09:00:00', 60, 'confirmed', '2026-04-02 08:17:46', '2026-04-02 08:32:46'),
	(52, 1, 2, '2026-04-13 09:00:00', 60, 'released', '2026-04-03 19:47:15', '2026-04-03 20:02:15'),
	(53, 1, 2, '2026-04-06 12:00:00', 60, 'confirmed', '2026-04-04 17:43:57', '2026-04-04 17:58:57'),
	(54, 1, 2, '2026-04-06 15:00:00', 60, 'confirmed', '2026-04-04 19:10:44', '2026-04-04 19:25:44'),
	(55, 1, 2, '2026-04-06 11:00:00', 60, 'released', '2026-04-05 18:21:28', '2026-04-05 18:36:28'),
	(56, 1, 2, '2026-04-06 20:00:00', 60, 'confirmed', '2026-04-05 18:23:36', '2026-04-05 18:38:36'),
	(57, 1, 2, '2026-04-06 16:00:00', 60, 'confirmed', '2026-04-05 18:26:38', '2026-04-05 18:41:38'),
	(58, 1, 13, '2026-04-13 11:00:00', 60, 'released', '2026-04-09 03:21:16', '2026-04-09 03:36:16'),
	(59, 1, 15, '2026-04-20 15:00:00', 60, 'confirmed', '2026-04-11 11:01:53', '2026-04-11 11:16:53'),
	(60, 1, 2, '2026-04-13 09:00:00', 60, 'confirmed', '2026-04-11 17:40:13', '2026-04-11 17:55:13'),
	(61, 1, 2, '2026-04-13 19:00:00', 60, 'confirmed', '2026-04-13 09:42:41', '2026-04-13 09:57:41'),
	(62, 1, 2, '2026-04-13 20:00:00', 60, 'confirmed', '2026-04-13 12:46:14', '2026-04-13 13:01:14'),
	(63, 1, 2, '2026-04-20 09:00:00', 60, 'confirmed', '2026-04-13 13:11:40', '2026-04-13 13:26:40'),
	(64, 1, 2, '2026-04-20 14:00:00', 60, 'confirmed', '2026-04-13 13:16:49', '2026-04-13 13:31:49'),
	(65, 1, 2, '2026-04-20 13:00:00', 60, 'confirmed', '2026-04-13 13:22:19', '2026-04-13 13:37:19'),
	(66, 1, 2, '2026-04-20 12:00:00', 60, 'confirmed', '2026-04-13 13:34:14', '2026-04-13 13:49:14'),
	(67, 1, 2, '2026-04-20 10:00:00', 60, 'confirmed', '2026-04-13 13:50:09', '2026-04-13 14:05:09'),
	(68, 1, 2, '2026-04-20 11:00:00', 60, 'confirmed', '2026-04-13 13:52:02', '2026-04-13 14:07:02'),
	(69, 1, 20, '2026-04-20 19:00:00', 60, 'confirmed', '2026-04-13 17:16:44', '2026-04-13 17:31:44'),
	(70, 1, 19, '2026-04-20 16:00:00', 60, 'confirmed', '2026-04-13 17:21:46', '2026-04-13 17:36:46'),
	(71, 1, 20, '2026-04-27 11:00:00', 60, 'confirmed', '2026-04-13 17:26:47', '2026-04-13 17:41:47'),
	(72, 1, 20, '2026-04-20 20:00:00', 60, 'confirmed', '2026-04-13 17:31:35', '2026-04-13 17:46:35'),
	(73, 1, 20, '2026-04-27 15:00:00', 60, 'confirmed', '2026-04-13 17:33:56', '2026-04-13 17:48:56'),
	(74, 2, 2, '2026-04-20 13:00:00', 60, 'released', '2026-04-15 09:36:21', '2026-04-15 09:51:21'),
	(75, 2, 2, '2026-04-20 09:00:00', 60, 'released', '2026-04-15 09:37:39', '2026-04-15 09:52:39');

-- Dumping structure for table new_path.community_posts
CREATE TABLE IF NOT EXISTS `community_posts` (
  `post_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `image_url` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `post_type` enum('general','success_story','question','support_request','resource') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'general',
  `is_anonymous` tinyint(1) DEFAULT '0',
  `is_pinned` tinyint(1) DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `likes_count` int DEFAULT '0',
  `comments_count` int DEFAULT '0',
  `shares_count` int DEFAULT '0',
  `views_count` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`post_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_active` (`is_active`),
  KEY `idx_created` (`created_at`),
  FULLTEXT KEY `idx_content` (`title`,`content`),
  CONSTRAINT `fk_post_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path.community_posts: ~7 rows (approximately)
INSERT INTO `community_posts` (`post_id`, `user_id`, `title`, `content`, `image_url`, `post_type`, `is_anonymous`, `is_pinned`, `is_active`, `likes_count`, `comments_count`, `shares_count`, `views_count`, `created_at`, `updated_at`) VALUES
	(1, 2, 'Pkyd', ';lksdj\';fljsdlf', '/NewPath_war/uploads/posts/d39b9b74-1663-4f7a-8146-5fb5bdde0541.jpg', 'general', 0, 0, 0, 0, 0, 0, 0, '2026-01-05 08:15:06', '2026-01-11 10:03:25'),
	(2, 2, 'liuhwfdlh', 'sdfsdf', '/NewPath_war_exploded/uploads/posts/49b9e1f9-d977-4e0e-9f6f-1939a5f1a324.jpg', 'general', 0, 0, 0, 0, 0, 0, 0, '2026-01-12 07:55:33', '2026-03-30 13:19:24'),
	(3, 2, 'dfgg', 'dfg', '/NewPath_war_exploded/uploads/posts/ba55d07c-b343-4b5e-a0ef-0b8a41cd6438.webp', 'general', 0, 0, 0, 0, 0, 0, 0, '2026-01-19 09:04:20', '2026-03-30 13:19:30'),
	(4, 2, 'jhgjhgkjhg', 'jhgkjhg', '/uploads/posts/1b890dddda4d360e69f5a3f768cdc726.png', 'general', 0, 0, 1, 4, 2, 19, 0, '2026-02-28 11:25:25', '2026-04-13 16:00:58'),
	(5, 13, 'new hope', 'good', '/uploads/posts/6360b1c9aa5960623e9ba628abcf209b.jpeg', 'success_story', 0, 0, 1, 2, 1, 2, 0, '2026-04-10 18:29:19', '2026-04-13 16:00:42'),
	(6, 15, 'happy', 'cool', '/uploads/posts/8441848fc7a203be8926de30034f20b0.jpeg', 'success_story', 0, 0, 1, 0, 0, 0, 0, '2026-04-12 06:36:10', '2026-04-12 06:36:10'),
	(7, 15, 'happy', 'cool', '/uploads/posts/34e6780562ef2097d09c270ed9ac5a68.jpeg', 'success_story', 0, 0, 1, 0, 0, 0, 0, '2026-04-12 06:36:17', '2026-04-12 06:36:17');

-- Dumping structure for table new_path.counselors
CREATE TABLE IF NOT EXISTS `counselors` (
  `counselor_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `title` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `specialty` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `specialty_short` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bio` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `experience_years` int DEFAULT '0',
  `education` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `certifications` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `languages_spoken` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `consultation_fee` decimal(10,2) DEFAULT NULL,
  `availability_schedule` json DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT '0',
  `rating` decimal(3,2) DEFAULT '0.00',
  `total_reviews` int DEFAULT '0',
  `total_clients` int DEFAULT '0',
  `total_sessions` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `google_refresh_token` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`counselor_id`),
  UNIQUE KEY `idx_user` (`user_id`),
  KEY `idx_verified` (`is_verified`),
  KEY `idx_specialty` (`specialty`),
  CONSTRAINT `fk_counselor_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path.counselors: ~0 rows (approximately)
INSERT INTO `counselors` (`counselor_id`, `user_id`, `title`, `specialty`, `specialty_short`, `bio`, `experience_years`, `education`, `certifications`, `languages_spoken`, `consultation_fee`, `availability_schedule`, `is_verified`, `rating`, `total_reviews`, `total_clients`, `total_sessions`, `created_at`, `updated_at`, `google_refresh_token`) VALUES
	(1, 5, 'Doctor', 'Trauma & PTSD', NULL, 'As a dedicated counselor, I provide a compassionate and non-judgmental space where you can explore lifes challenges. I believe in a collaborative approach, working together to develop practical tools and resilience. Whether you are navigating stress, transitions, or personal growth, I am here to support you in finding your path forward', 33, 'dfgzfg', 'dfgdfg', 'dfgdfg', 3500.00, '{"monday": [{"end": "17:00", "start": "09:00"}, {"end": "21:00", "start": "19:00"}]}', 1, 3.00, 1, 0, 0, '2026-01-04 18:10:13', '2026-04-05 09:21:32', NULL),
	(2, 23, 'sdfdsf', 'Mental Health Counseling', '', 'sdfsdf', 3, 'dsfdsf', 'sdfdsf', 'English', 3000.00, '{"monday": [{"end": "17:00", "start": "09:00"}]}', 1, 0.00, 0, 0, 0, '2026-04-15 08:28:36', '2026-04-15 08:28:36', NULL);

-- Dumping structure for table new_path.counselor_applications
CREATE TABLE IF NOT EXISTS `counselor_applications` (
  `application_id` int NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone_number` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `specialty` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `bio` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `experience_years` int DEFAULT NULL,
  `education` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `certifications` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `languages_spoken` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `consultation_fee` decimal(10,2) DEFAULT NULL,
  `availability_schedule` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `documents_url` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pending','approved','rejected') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `admin_notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `reviewed_by` int DEFAULT NULL,
  `review_date` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`application_id`),
  UNIQUE KEY `idx_email` (`email`),
  KEY `idx_status` (`status`),
  KEY `fk_app_admin` (`reviewed_by`),
  CONSTRAINT `fk_app_admin` FOREIGN KEY (`reviewed_by`) REFERENCES `admin` (`admin_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path.counselor_applications: ~7 rows (approximately)
INSERT INTO `counselor_applications` (`application_id`, `full_name`, `email`, `phone_number`, `title`, `specialty`, `bio`, `experience_years`, `education`, `certifications`, `languages_spoken`, `consultation_fee`, `availability_schedule`, `documents_url`, `status`, `admin_notes`, `reviewed_by`, `review_date`, `created_at`, `updated_at`) VALUES
	(1, 'Pasidu Rajapaksha', 'Pasidurajapaksha202@gmail.com', '0773623777', 'sdfdsf', 'Addiction Counseling', 'dfgdfg', 33, 'dfgzfg', 'dfgdfg', 'dfgdfg', 34543.00, 'dfgdfg', 'https://www.youtube.com/watch?v=FBbH2d5A5Tg', 'rejected', 'bullshit', 2, '2026-01-04 18:07:19', '2026-01-04 18:01:27', '2026-01-04 18:07:19'),
	(2, 'Pasidu Rajapaksha', 'Puski200322@gmail.com', '0773623777', 'sdfdsf', 'Addiction Counseling', 'dfgdfg', 33, 'dfgzfg', 'dfgdfg', 'dfgdfg', 34543.00, 'dfgdfg', 'https://www.youtube.com/watch?v=FBbH2d5A5Tg', 'approved', 'Application approved and counselor account created', 2, '2026-01-04 18:10:13', '2026-01-04 18:07:36', '2026-01-04 18:10:13'),
	(3, 'Pasidu Rajapaksha', 'rajapakshagpt@gmail.com', '0714710856', 'vfvf', 'Mental Health Counseling', 'vcbvb', 3, 'vcbb', 'cvbv', 'cvbb', 34333.00, 'cvbcvb', 'cvbcvbhttp://localhost:8080/NewPath_war_exploded/admin/counselor-management', 'rejected', 'dfg', 1, '2026-04-15 08:31:40', '2026-01-19 08:59:09', '2026-04-15 08:31:40'),
	(4, 'Vishwa Dissanayake', 'vndxoxo@gmail.com', '0740006902', 'Therapist', 'Trauma Counseling', 'hiiii', 7, 'bbb', 'bbb', 'English', 999.97, 'bb', '/uploads/applications/2c11fedae4cedadc2fe04daea64b9ca7.jpg', 'rejected', 'niga', 1, '2026-04-15 08:31:20', '2026-04-12 05:51:52', '2026-04-15 08:31:20'),
	(5, 'swairi', 'Swairisg29@gmail.com', '0775567692', 'licensed counselor', 'Mental Health Counseling', 'abcd', 5, 'qbc', 's1dd', 'English, Sinhala', 22000.00, 'monday to friday 8am to 1pm', '', 'rejected', '', 1, '2026-04-15 08:30:57', '2026-04-15 07:28:53', '2026-04-15 08:30:57'),
	(6, 'Sanath', 'sanath@gmail.com', '0773738839', 'Niga', 'Youth Counseling', 'Hello hi how are you', 5, 'ABCD in University of Gedara', '', 'English', 2500.00, 'Monday 10-11', '/uploads/applications/15061cf26aa66d5b57c435a8ed23f7bb.pdf', 'rejected', '', 1, '2026-04-15 08:30:36', '2026-04-15 07:29:29', '2026-04-15 08:30:36'),
	(7, 'samantha', 'samantha@gmail.com', '0774536633', 'sdfdsf', 'Mental Health Counseling', 'sdfsdf', 3, 'dsfdsf', 'sdfdsf', 'English', 3000.00, '{"monday":[{"start":"09:00","end":"17:00"}]}', '/uploads/applications/adb3e750cb2bbfccc074a59970454382.pdf', 'approved', 'Application approved and counselor account created', 1, '2026-04-15 08:29:59', '2026-04-15 08:27:45', '2026-04-15 08:29:59');

-- Dumping structure for table new_path.counselor_payouts
CREATE TABLE IF NOT EXISTS `counselor_payouts` (
  `payout_id` int NOT NULL AUTO_INCREMENT,
  `counselor_id` int NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'LKR',
  `period_start` date NOT NULL,
  `period_end` date NOT NULL,
  `sessions_count` int DEFAULT '0',
  `status` enum('pending','processing','completed','failed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `stripe_payout_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `payhere_reference` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `platform_commission` decimal(10,2) DEFAULT '0.00',
  `commission_rate` decimal(5,2) DEFAULT '0.00',
  PRIMARY KEY (`payout_id`),
  KEY `idx_counselor` (`counselor_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_payout_counselor` FOREIGN KEY (`counselor_id`) REFERENCES `counselors` (`counselor_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path.counselor_payouts: ~0 rows (approximately)
INSERT INTO `counselor_payouts` (`payout_id`, `counselor_id`, `amount`, `currency`, `period_start`, `period_end`, `sessions_count`, `status`, `stripe_payout_id`, `paid_at`, `created_at`, `payhere_reference`, `platform_commission`, `commission_rate`) VALUES
	(1, 1, 119350.00, 'LKR', '2000-01-01', '2026-04-15', 33, 'completed', NULL, '2026-04-15 20:28:05', '2026-04-15 20:28:05', NULL, 0.00, 0.00);

-- Dumping structure for table new_path.daily_checkins
CREATE TABLE IF NOT EXISTS `daily_checkins` (
  `checkin_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `checkin_date` date NOT NULL,
  `is_sober` tinyint(1) DEFAULT '1',
  `mood_rating` int DEFAULT NULL,
  `mood_label` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `energy_level` int DEFAULT NULL,
  `sleep_quality` int DEFAULT NULL,
  `stress_level` int DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`checkin_id`),
  UNIQUE KEY `idx_user_date` (`user_id`,`checkin_date`),
  CONSTRAINT `fk_checkin_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path.daily_checkins: ~8 rows (approximately)
INSERT INTO `daily_checkins` (`checkin_id`, `user_id`, `checkin_date`, `is_sober`, `mood_rating`, `mood_label`, `energy_level`, `sleep_quality`, `stress_level`, `notes`, `created_at`) VALUES
	(1, 2, '2026-01-09', 1, 4, 'Good', 4, NULL, 2, NULL, '2026-01-11 15:01:17'),
	(2, 2, '2026-01-10', 1, 3, 'Okay', 3, NULL, 3, NULL, '2026-01-11 15:01:17'),
	(3, 2, '2026-01-11', 1, 5, 'Great', 5, NULL, 1, NULL, '2026-01-11 15:01:17'),
	(9, 2, '2026-01-18', 1, 5, 'Neutral', NULL, NULL, NULL, 'fghh', '2026-01-18 09:19:16'),
	(10, 2, '2026-04-05', 1, 1, 'Terrible', 3, 3, 2, '', '2026-04-05 18:45:53'),
	(11, 2, '2026-04-13', 1, 3, 'Okay', 4, 4, 4, '', '2026-04-13 11:12:41'),
	(12, 17, '2026-04-13', 1, 4, 'Good', 3, 3, 2, '', '2026-04-13 14:39:48'),
	(13, 2, '2026-04-14', 1, 4, 'Good', 5, 3, 2, '', '2026-04-14 02:17:38');

-- Dumping structure for table new_path.direct_messages
CREATE TABLE IF NOT EXISTS `direct_messages` (
  `message_id` int NOT NULL AUTO_INCREMENT,
  `conversation_id` int NOT NULL,
  `sender_id` int NOT NULL,
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`message_id`),
  KEY `idx_conversation` (`conversation_id`),
  KEY `idx_sender` (`sender_id`),
  KEY `idx_created` (`created_at`),
  CONSTRAINT `fk_dm_conversation` FOREIGN KEY (`conversation_id`) REFERENCES `dm_conversations` (`conversation_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_dm_sender` FOREIGN KEY (`sender_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path.direct_messages: ~15 rows (approximately)
INSERT INTO `direct_messages` (`message_id`, `conversation_id`, `sender_id`, `content`, `is_read`, `created_at`) VALUES
	(1, 10, 2, 'hi', 1, '2026-04-02 19:58:13'),
	(2, 10, 12, 'how are yu', 1, '2026-04-02 19:58:31'),
	(3, 10, 2, 'i am fine thank yu', 1, '2026-04-02 19:58:43'),
	(5, 19, 15, 'hii there', 0, '2026-04-11 11:13:17'),
	(6, 10, 2, 'sudda', 0, '2026-04-15 12:42:41'),
	(7, 19, 15, 'rrr', 0, '2026-04-15 14:38:36'),
	(8, 19, 15, 'rrr', 0, '2026-04-15 14:38:39'),
	(9, 19, 15, 'rrr', 0, '2026-04-15 14:38:41'),
	(10, 19, 15, 'rrr', 0, '2026-04-15 14:38:47'),
	(11, 21, 2, 'abcd', 0, '2026-04-16 08:03:32'),
	(12, 21, 2, 'hiii', 0, '2026-04-16 08:03:44'),
	(13, 16, 2, 'hii', 0, '2026-04-16 08:04:29'),
	(14, 18, 2, 'hii', 0, '2026-04-16 08:05:41'),
	(15, 18, 2, '😄', 0, '2026-04-16 08:07:22'),
	(16, 18, 2, 'ok', 0, '2026-04-16 08:10:45');

-- Dumping structure for table new_path.dm_conversations
CREATE TABLE IF NOT EXISTS `dm_conversations` (
  `conversation_id` int NOT NULL AUTO_INCREMENT,
  `user1_id` int NOT NULL,
  `user2_id` int NOT NULL,
  `last_message_at` timestamp NULL DEFAULT NULL,
  `last_message_preview` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`conversation_id`),
  UNIQUE KEY `idx_user_pair` (`user1_id`,`user2_id`),
  KEY `idx_user1` (`user1_id`),
  KEY `idx_user2` (`user2_id`),
  KEY `idx_last_message` (`last_message_at`),
  CONSTRAINT `fk_conv_user1` FOREIGN KEY (`user1_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_conv_user2` FOREIGN KEY (`user2_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path.dm_conversations: ~6 rows (approximately)
INSERT INTO `dm_conversations` (`conversation_id`, `user1_id`, `user2_id`, `last_message_at`, `last_message_preview`, `created_at`) VALUES
	(10, 2, 12, '2026-04-15 12:42:41', 'sudda', '2026-04-02 19:58:00'),
	(16, 2, 13, '2026-04-16 08:04:29', 'hii', '2026-04-11 10:54:04'),
	(18, 2, 15, '2026-04-16 08:10:45', 'ok', '2026-04-11 11:11:20'),
	(19, 13, 15, '2026-04-15 14:38:48', 'rrr', '2026-04-11 11:11:31'),
	(20, 7, 15, NULL, NULL, '2026-04-11 11:13:00'),
	(21, 2, 7, '2026-04-16 08:03:44', 'hiii', '2026-04-16 06:03:22');

-- Dumping structure for table new_path.help_centers
CREATE TABLE IF NOT EXISTS `help_centers` (
  `help_center_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `organization` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone_number` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `website` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `zip_code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `availability` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `specialties` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) DEFAULT '1',
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`help_center_id`),
  KEY `idx_type` (`type`),
  KEY `idx_category` (`category`),
  KEY `idx_active` (`is_active`),
  KEY `fk_hc_user` (`created_by`),
  CONSTRAINT `fk_hc_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path.help_centers: ~2 rows (approximately)
INSERT INTO `help_centers` (`help_center_id`, `name`, `organization`, `type`, `category`, `phone_number`, `email`, `website`, `address`, `city`, `state`, `zip_code`, `availability`, `description`, `specialties`, `is_active`, `created_by`, `created_at`, `updated_at`) VALUES
	(1, 'UCSC', 'University of Colombo', 'hotline', 'emergency', '077444455', 'puski200322@gmail.com', 'pakaya.com', 'adss', 'sdd', 'dfdf', '122', 'a', 'sdsd', 'sdsd', 1, 1, '2026-04-16 06:45:59', '2026-04-16 06:45:59'),
	(2, 'Swairi', 'homagama', 'appointment', 'technical', '0773623777', 'puski200322@gmail.com', 'sdfsdf', 'sdfsdf', 'sdfsdf', 'sdfdsf', '2323', 'sdfsdf', 'sdfdsf', 'sdfsfsd', 1, 1, '2026-04-16 06:50:53', '2026-04-16 06:50:53');

-- Dumping structure for table new_path.job_posts
CREATE TABLE IF NOT EXISTS `job_posts` (
  `job_id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `company` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `requirements` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `location` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `job_type` enum('full_time','part_time','contract','temporary','internship') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `salary` decimal(10,2) DEFAULT NULL,
  `salary_range` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_phone` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `application_url` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) DEFAULT '1',
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`job_id`),
  KEY `idx_type` (`job_type`),
  KEY `idx_category` (`category`),
  KEY `idx_location` (`location`),
  KEY `idx_active` (`is_active`),
  KEY `fk_job_admin` (`created_by`),
  CONSTRAINT `fk_job_admin` FOREIGN KEY (`created_by`) REFERENCES `admin` (`admin_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path.job_posts: ~0 rows (approximately)

-- Dumping structure for table new_path.journal_categories
CREATE TABLE IF NOT EXISTS `journal_categories` (
  `category_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_default` tinyint(1) DEFAULT '0',
  `color` varchar(7) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`category_id`),
  KEY `idx_user` (`user_id`),
  CONSTRAINT `fk_jc_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path.journal_categories: ~5 rows (approximately)
INSERT INTO `journal_categories` (`category_id`, `user_id`, `name`, `slug`, `is_default`, `color`, `created_at`) VALUES
	(1, NULL, 'Gratitude', 'gratitude', 1, NULL, '2026-01-02 18:00:38'),
	(2, NULL, 'Progress', 'progress', 1, NULL, '2026-01-02 18:00:38'),
	(3, NULL, 'Challenge', 'challenge', 1, NULL, '2026-01-02 18:00:38'),
	(4, NULL, 'Reflection', 'reflection', 1, NULL, '2026-01-02 18:00:38'),
	(5, NULL, 'Other', 'other', 1, NULL, '2026-01-02 18:00:38');

-- Dumping structure for table new_path.journal_entries
CREATE TABLE IF NOT EXISTS `journal_entries` (
  `entry_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `category_id` int DEFAULT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_highlight` tinyint(1) DEFAULT '0',
  `mood` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_private` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`entry_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_category` (`category_id`),
  KEY `idx_created` (`created_at`),
  CONSTRAINT `fk_je_category` FOREIGN KEY (`category_id`) REFERENCES `journal_categories` (`category_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_je_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path.journal_entries: ~2 rows (approximately)
INSERT INTO `journal_entries` (`entry_id`, `user_id`, `category_id`, `title`, `content`, `is_highlight`, `mood`, `is_private`, `created_at`, `updated_at`) VALUES
	(1, 2, NULL, 'Quick Log - Apr 11, 2026', 'fdgdfg', 0, NULL, 1, '2026-04-11 18:16:57', '2026-04-11 18:16:57'),
	(2, 2, NULL, 'Quick Log - Apr 11, 2026', 'fdgdfg', 0, NULL, 1, '2026-04-11 18:19:08', '2026-04-11 18:19:08'),
	(3, 2, 2, '', 'I feel motivated and happy', 0, 'Motivated', 1, '2026-04-14 03:36:46', '2026-04-14 03:36:46'),
	(4, 2, 4, 'Calm today', 'I feel calm', 1, 'Calm', 1, '2026-04-16 08:21:26', '2026-04-16 08:21:26');

-- Dumping structure for table new_path.notifications
CREATE TABLE IF NOT EXISTS `notifications` (
  `notification_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `link` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`notification_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_read` (`is_read`),
  KEY `idx_created` (`created_at`),
  CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path.notifications: ~72 rows (approximately)
INSERT INTO `notifications` (`notification_id`, `user_id`, `type`, `title`, `message`, `link`, `is_read`, `created_at`) VALUES
	(1, 5, 'followup_message', 'New follow-up message', 'Your client sent a follow-up message from your session.', '/counselor/sessions/follow-up?session_id=1', 1, '2026-04-04 17:47:01'),
	(2, 2, 'followup_reply', 'New follow-up reply', 'Your counselor replied to your follow-up thread.', '/user/sessions/follow-up?session_id=1', 1, '2026-04-04 17:49:17'),
	(3, 2, 'followup_reply', 'New follow-up reply', 'Your counselor replied to your follow-up thread.', '/user/sessions/follow-up?session_id=1', 1, '2026-04-04 18:13:22'),
	(4, 5, 'followup_message', 'New follow-up message', 'Your client sent a follow-up message.', '/counselor/sessions/follow-up?session_id=1', 1, '2026-04-04 18:13:50'),
	(5, 2, 'booking_confirmed', 'Session Confirmed', 'Your session with Pasidu Rajapaksha on April 6, 2026 at 3:00 PM is confirmed.', '/user/sessions', 1, '2026-04-04 19:11:22'),
	(6, 5, 'new_booking', 'New Session Booked', 'Anonymous User has booked a session on April 6, 2026 at 3:00 PM.', '/counselor/sessions', 1, '2026-04-04 19:11:22'),
	(7, 5, 'session_cancelled', 'Session Cancelled', 'A client has cancelled their upcoming session.', '/counselor/sessions', 1, '2026-04-04 19:44:19'),
	(8, 5, 'reschedule_request', 'Reschedule Request', 'A client has requested to reschedule their upcoming session.', '/counselor/sessions', 1, '2026-04-04 20:09:00'),
	(9, 2, 'reschedule_approved', 'Reschedule Approved', 'Your reschedule request was approved. Your session has been cancelled — please rebook at a new time.', '/user/counselors', 1, '2026-04-04 20:15:47'),
	(10, 5, 'reschedule_request', 'Reschedule Request', 'A client has requested to reschedule their upcoming session.', '/counselor/sessions', 1, '2026-04-04 20:16:14'),
	(11, 5, 'reschedule_request', 'Reschedule Request', 'A client has requested to reschedule their upcoming session.', '/counselor/sessions', 1, '2026-04-04 20:23:09'),
	(12, 5, 'reschedule_request', 'Reschedule Request', 'A client has requested to reschedule their upcoming session.', '/counselor/sessions', 1, '2026-04-05 07:31:14'),
	(13, 5, 'reschedule_request', 'Reschedule Request', 'A client has requested to reschedule their upcoming session.', '/counselor/sessions', 1, '2026-04-05 07:31:33'),
	(14, 5, 'reschedule_request', 'Reschedule Request', 'A client has requested to reschedule their upcoming session.', '/counselor/sessions', 1, '2026-04-05 07:32:49'),
	(15, 2, 'reschedule_approved', 'Reschedule Approved', 'Your reschedule request was approved. Your session has been cancelled — please rebook at a new time.', '/user/counselors', 1, '2026-04-05 07:35:33'),
	(16, 2, 'booking_confirmed', 'Rescheduled Session Confirmed', 'Your rescheduled session with Pasidu Rajapaksha on April 6, 2026 at 8:00 PM is confirmed. No charge applied.', '/user/sessions', 1, '2026-04-05 18:26:12'),
	(17, 5, 'new_booking', 'Rescheduled Session Booked', 'Anonymous User completed their reschedule booking for April 6, 2026 at 8:00 PM.', '/counselor/sessions', 1, '2026-04-05 18:26:12'),
	(18, 2, 'booking_confirmed', 'Session Confirmed', 'Your session with Pasidu Rajapaksha on April 6, 2026 at 4:00 PM is confirmed.', '/user/sessions', 1, '2026-04-05 18:28:57'),
	(19, 5, 'new_booking', 'New Session Booked', 'Anonymous User has booked a session on April 6, 2026 at 4:00 PM.', '/counselor/sessions', 1, '2026-04-05 18:28:58'),
	(20, 15, 'booking_confirmed', 'Session Confirmed', 'Your session with Pasidu Rajapaksha on April 20, 2026 at 3:00 PM is confirmed.', '/user/sessions', 0, '2026-04-11 11:03:13'),
	(21, 5, 'new_booking', 'New Session Booked', 'misath has booked a session on April 20, 2026 at 3:00 PM.', '/counselor/sessions', 1, '2026-04-11 11:03:13'),
	(22, 2, 'booking_confirmed', 'Session Confirmed', 'Your session with Pasidu Rajapaksha on April 13, 2026 at 9:00 AM is confirmed.', '/user/sessions', 1, '2026-04-11 17:41:06'),
	(23, 5, 'new_booking', 'New Session Booked', 'Anonymous User has booked a session on April 13, 2026 at 9:00 AM.', '/counselor/sessions', 1, '2026-04-11 17:41:06'),
	(24, 5, 'reschedule_request', 'Reschedule Request', 'A client has requested to reschedule their upcoming session.', '/counselor/sessions', 1, '2026-04-12 17:06:47'),
	(25, 2, 'reschedule_approved', 'Reschedule Approved', 'Your reschedule request was approved. Your session has been cancelled — please rebook at a new time.', '/user/counselors', 1, '2026-04-12 17:08:08'),
	(26, 2, 'booking_confirmed', 'Rescheduled Session Confirmed', 'Your rescheduled session with Pasidu Rajapaksha on April 13, 2026 at 7:00 PM is confirmed. No charge applied.', '/user/sessions', 1, '2026-04-13 09:42:59'),
	(27, 5, 'new_booking', 'Rescheduled Session Booked', 'Anonymous User completed their reschedule booking for April 13, 2026 at 7:00 PM.', '/counselor/sessions', 1, '2026-04-13 09:42:59'),
	(28, 2, 'booking_confirmed', 'Session Confirmed', 'Your session with Pasidu Rajapaksha on April 13, 2026 at 8:00 PM is confirmed.', '/user/sessions', 1, '2026-04-13 12:47:10'),
	(29, 5, 'new_booking', 'New Session Booked', 'Anonymous User has booked a session on April 13, 2026 at 8:00 PM.', '/counselor/sessions', 1, '2026-04-13 12:47:10'),
	(30, 2, 'booking_confirmed', 'Session Confirmed', 'Your session with Pasidu Rajapaksha on April 20, 2026 at 9:00 AM is confirmed.', '/user/sessions', 1, '2026-04-13 13:12:32'),
	(31, 5, 'new_booking', 'New Session Booked', 'Anonymous User has booked a session on April 20, 2026 at 9:00 AM.', '/counselor/sessions', 1, '2026-04-13 13:12:33'),
	(32, 2, 'booking_confirmed', 'Session Confirmed', 'Your session with Pasidu Rajapaksha on April 20, 2026 at 2:00 PM is confirmed.', '/user/sessions', 1, '2026-04-13 13:17:32'),
	(33, 5, 'new_booking', 'New Session Booked', 'Anonymous User has booked a session on April 20, 2026 at 2:00 PM.', '/counselor/sessions', 1, '2026-04-13 13:17:32'),
	(34, 2, 'booking_confirmed', 'Session Confirmed', 'Your session with Pasidu Rajapaksha on April 20, 2026 at 1:00 PM is confirmed.', '/user/sessions', 1, '2026-04-13 13:23:01'),
	(35, 5, 'new_booking', 'New Session Booked', 'Anonymous User has booked a session on April 20, 2026 at 1:00 PM.', '/counselor/sessions', 1, '2026-04-13 13:23:01'),
	(36, 2, 'booking_confirmed', 'Session Confirmed', 'Your session with Pasidu Rajapaksha on April 20, 2026 at 12:00 PM is confirmed.', '/user/sessions', 1, '2026-04-13 13:34:54'),
	(37, 5, 'new_booking', 'New Session Booked', 'Anonymous User has booked a session on April 20, 2026 at 12:00 PM.', '/counselor/sessions', 1, '2026-04-13 13:34:54'),
	(38, 2, 'booking_confirmed', 'Session Confirmed', 'Your session with Pasidu Rajapaksha on April 20, 2026 at 10:00 AM is confirmed.', '/user/sessions', 1, '2026-04-13 13:51:01'),
	(39, 5, 'new_booking', 'New Session Booked', 'Anonymous User has booked a session on April 20, 2026 at 10:00 AM.', '/counselor/sessions', 1, '2026-04-13 13:51:02'),
	(40, 2, 'booking_confirmed', 'Session Confirmed', 'Your session with Pasidu Rajapaksha on April 20, 2026 at 11:00 AM is confirmed.', '/user/sessions', 1, '2026-04-13 13:52:39'),
	(41, 5, 'new_booking', 'New Session Booked', 'Anonymous User has booked a session on April 20, 2026 at 11:00 AM.', '/counselor/sessions', 1, '2026-04-13 13:52:40'),
	(42, 20, 'booking_confirmed', 'Session Confirmed', 'Your session with Pasidu Rajapaksha on April 20, 2026 at 7:00 PM is confirmed.', '/user/sessions', 0, '2026-04-13 17:18:40'),
	(43, 5, 'new_booking', 'New Session Booked', 'Pasidu Rajapaksha has booked a session on April 20, 2026 at 7:00 PM.', '/counselor/sessions', 1, '2026-04-13 17:18:40'),
	(44, 19, 'booking_confirmed', 'Session Confirmed', 'Your session with Pasidu Rajapaksha on April 20, 2026 at 4:00 PM is confirmed.', '/user/sessions', 0, '2026-04-13 17:22:28'),
	(45, 5, 'new_booking', 'New Session Booked', 'ponnaya has booked a session on April 20, 2026 at 4:00 PM.', '/counselor/sessions', 1, '2026-04-13 17:22:28'),
	(46, 20, 'booking_confirmed', 'Session Confirmed', 'Your session with Pasidu Rajapaksha on April 27, 2026 at 11:00 AM is confirmed.', '/user/sessions', 0, '2026-04-13 17:27:50'),
	(47, 5, 'new_booking', 'New Session Booked', 'Pasidu Rajapaksha has booked a session on April 27, 2026 at 11:00 AM.', '/counselor/sessions', 1, '2026-04-13 17:27:50'),
	(48, 20, 'booking_confirmed', 'Session Confirmed', 'Your session with Pasidu Rajapaksha on April 20, 2026 at 8:00 PM is confirmed.', '/user/sessions', 0, '2026-04-13 17:32:22'),
	(49, 5, 'new_booking', 'New Session Booked', 'Pasidu Rajapaksha has booked a session on April 20, 2026 at 8:00 PM.', '/counselor/sessions', 1, '2026-04-13 17:32:22'),
	(50, 20, 'booking_confirmed', 'Session Confirmed', 'Your session with Pasidu Rajapaksha on April 27, 2026 at 3:00 PM is confirmed.', '/user/sessions', 0, '2026-04-13 17:34:43'),
	(51, 5, 'new_booking', 'New Session Booked', 'Pasidu Rajapaksha has booked a session on April 27, 2026 at 3:00 PM.', '/counselor/sessions', 1, '2026-04-13 17:34:43'),
	(52, 5, 'followup_message', 'New follow-up message', 'Your client sent a follow-up message.', '/counselor/sessions/follow-up?session_id=2', 1, '2026-04-13 18:02:14'),
	(53, 5, 'reschedule_request', 'Reschedule Request', 'A client has requested to reschedule their upcoming session.', '/counselor/sessions', 1, '2026-04-13 18:10:57'),
	(54, 5, 'reschedule_request', 'Reschedule Request', 'A client has requested to reschedule their upcoming session.', '/counselor/sessions', 1, '2026-04-13 18:11:03'),
	(55, 5, 'task_change_request', 'Task Change Request', 'A client has requested a change to one of their assigned tasks.', '/counselor/recovery-plans/task-changes', 1, '2026-04-14 02:26:36'),
	(56, 2, 'task_change_resolved', 'Task Change Approved', 'Your counselor approved your task change request. The task has been updated.', '/user/recovery/task/change-requests', 1, '2026-04-14 02:28:33'),
	(57, 2, 'reschedule_rejected', 'Reschedule Declined', 'Your reschedule request was declined. Your original session remains scheduled.', '/user/sessions', 1, '2026-04-14 06:44:41'),
	(58, 2, 'followup_reply', 'New follow-up reply', 'Your counselor replied to your follow-up thread.', '/user/sessions/follow-up?session_id=2', 1, '2026-04-14 06:48:04'),
	(59, 2, 'reschedule_approved', 'Reschedule Approved', 'Your reschedule request was approved. Your session has been cancelled — please rebook at a new time.', '/user/counselors', 1, '2026-04-15 07:51:24'),
	(60, 2, 'followup_reply', 'New follow-up reply', 'Your counselor replied to your follow-up thread.', '/user/sessions/follow-up?session_id=2', 1, '2026-04-15 07:56:33'),
	(61, 5, 'reschedule_request', 'Reschedule Request', 'A client has requested to reschedule their upcoming session.', '/counselor/sessions', 1, '2026-04-15 09:38:45'),
	(62, 2, 'reschedule_approved', 'Reschedule Approved', 'Your reschedule request was approved. Your session has been cancelled — please rebook at a new time.', '/user/counselors', 1, '2026-04-15 09:42:58'),
	(63, 5, 'followup_message', 'New follow-up message', 'Your client sent a follow-up message.', '/counselor/sessions/follow-up?session_id=2', 0, '2026-04-15 12:10:13'),
	(64, 5, 'followup_message', 'New follow-up message', 'Your client sent a follow-up message.', '/counselor/sessions/follow-up?session_id=2', 0, '2026-04-15 12:10:30'),
	(65, 2, 'followup_reply', 'New follow-up reply', 'Your counselor replied to your follow-up thread.', '/user/sessions/follow-up?session_id=2', 1, '2026-04-15 12:26:34'),
	(66, 5, 'followup_message', 'New follow-up message', 'Your client sent a follow-up message.', '/counselor/sessions/follow-up?session_id=2', 0, '2026-04-15 12:27:15'),
	(67, 5, 'followup_message', 'New follow-up message', 'Your client sent a follow-up message.', '/counselor/sessions/follow-up?session_id=2', 0, '2026-04-15 12:35:24'),
	(68, 2, 'followup_reply', 'New follow-up reply', 'Your counselor replied to your follow-up thread.', '/user/sessions/follow-up?session_id=2', 1, '2026-04-15 12:35:53'),
	(69, 5, 'followup_message', 'New follow-up message', 'Your client sent a follow-up message.', '/counselor/sessions/follow-up?session_id=2', 0, '2026-04-15 12:36:07'),
	(70, 2, 'followup_reply', 'New follow-up reply', 'Your counselor replied to your follow-up thread.', '/user/sessions/follow-up?session_id=2', 1, '2026-04-15 12:36:29'),
	(71, 5, 'followup_message', 'New follow-up message', 'Your client sent a follow-up message.', '/counselor/sessions/follow-up?session_id=29', 0, '2026-04-16 05:54:38'),
	(72, 2, 'followup_reply', 'New follow-up reply', 'Your counselor replied to your follow-up thread.', '/user/sessions/follow-up?session_id=29', 0, '2026-04-16 05:56:25');

-- Dumping structure for table new_path.payment_methods
CREATE TABLE IF NOT EXISTS `payment_methods` (
  `payment_method_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `method_type` enum('card','paypal','bank_transfer') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `card_last_four` varchar(4) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `card_brand` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expiry_month` int DEFAULT NULL,
  `expiry_year` int DEFAULT NULL,
  `billing_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_default` tinyint(1) DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `stripe_payment_method_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`payment_method_id`),
  KEY `idx_user` (`user_id`),
  CONSTRAINT `fk_pm_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path.payment_methods: ~0 rows (approximately)

-- Dumping structure for table new_path.post_comments
CREATE TABLE IF NOT EXISTS `post_comments` (
  `comment_id` int NOT NULL AUTO_INCREMENT,
  `post_id` int NOT NULL,
  `user_id` int NOT NULL,
  `parent_comment_id` int DEFAULT NULL,
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_anonymous` tinyint(1) DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `likes_count` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`comment_id`),
  KEY `idx_post` (`post_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_parent` (`parent_comment_id`),
  CONSTRAINT `fk_comment_parent` FOREIGN KEY (`parent_comment_id`) REFERENCES `post_comments` (`comment_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_comment_post` FOREIGN KEY (`post_id`) REFERENCES `community_posts` (`post_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_comment_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path.post_comments: ~0 rows (approximately)
INSERT INTO `post_comments` (`comment_id`, `post_id`, `user_id`, `parent_comment_id`, `content`, `is_anonymous`, `is_active`, `likes_count`, `created_at`, `updated_at`) VALUES
	(1, 4, 2, NULL, 'hi', 0, 1, 0, '2026-04-04 19:25:24', '2026-04-04 19:25:24'),
	(2, 5, 13, NULL, 'amaizing', 0, 1, 0, '2026-04-10 18:29:42', '2026-04-10 18:29:42'),
	(3, 4, 2, NULL, 'ew', 0, 1, 0, '2026-04-13 16:00:58', '2026-04-13 16:00:58');

-- Dumping structure for table new_path.post_likes
CREATE TABLE IF NOT EXISTS `post_likes` (
  `like_id` int NOT NULL AUTO_INCREMENT,
  `post_id` int NOT NULL,
  `user_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`like_id`),
  UNIQUE KEY `idx_post_user` (`post_id`,`user_id`),
  KEY `fk_like_user` (`user_id`),
  CONSTRAINT `fk_like_post` FOREIGN KEY (`post_id`) REFERENCES `community_posts` (`post_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_like_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path.post_likes: ~2 rows (approximately)
INSERT INTO `post_likes` (`like_id`, `post_id`, `user_id`, `created_at`) VALUES
	(14, 4, 2, '2026-04-09 17:28:32'),
	(23, 5, 15, '2026-04-11 17:04:53'),
	(25, 5, 2, '2026-04-13 16:00:42');

-- Dumping structure for table new_path.post_reports
CREATE TABLE IF NOT EXISTS `post_reports` (
  `report_id` int NOT NULL AUTO_INCREMENT,
  `post_id` int DEFAULT NULL,
  `comment_id` int DEFAULT NULL,
  `reporter_id` int NOT NULL,
  `reason` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` enum('pending','reviewed','resolved','dismissed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `reviewed_by` int DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `action_taken` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`report_id`),
  KEY `idx_status` (`status`),
  KEY `fk_report_post` (`post_id`),
  KEY `fk_report_comment` (`comment_id`),
  KEY `fk_report_reporter` (`reporter_id`),
  KEY `fk_report_admin` (`reviewed_by`),
  CONSTRAINT `fk_report_admin` FOREIGN KEY (`reviewed_by`) REFERENCES `admin` (`admin_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_report_comment` FOREIGN KEY (`comment_id`) REFERENCES `post_comments` (`comment_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_report_post` FOREIGN KEY (`post_id`) REFERENCES `community_posts` (`post_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_report_reporter` FOREIGN KEY (`reporter_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path.post_reports: ~0 rows (approximately)
INSERT INTO `post_reports` (`report_id`, `post_id`, `comment_id`, `reporter_id`, `reason`, `description`, `status`, `reviewed_by`, `reviewed_at`, `action_taken`, `created_at`) VALUES
	(1, 4, NULL, 2, 'spam', 'gjk', 'pending', NULL, NULL, NULL, '2026-04-04 19:29:27');

-- Dumping structure for table new_path.post_tags
CREATE TABLE IF NOT EXISTS `post_tags` (
  `tag_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_count` int DEFAULT '0',
  PRIMARY KEY (`tag_id`),
  UNIQUE KEY `idx_slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path.post_tags: ~6 rows (approximately)
INSERT INTO `post_tags` (`tag_id`, `name`, `slug`, `post_count`) VALUES
	(1, 'Recovery', 'recovery', 0),
	(2, 'Motivation', 'motivation', 0),
	(3, 'Support', 'support', 0),
	(4, 'Success Story', 'success-story', 0),
	(5, 'Question', 'question', 0),
	(6, 'Resources', 'resources', 0);

-- Dumping structure for table new_path.post_tag_mappings
CREATE TABLE IF NOT EXISTS `post_tag_mappings` (
  `post_id` int NOT NULL,
  `tag_id` int NOT NULL,
  PRIMARY KEY (`post_id`,`tag_id`),
  KEY `fk_ptm_tag` (`tag_id`),
  CONSTRAINT `fk_ptm_post` FOREIGN KEY (`post_id`) REFERENCES `community_posts` (`post_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ptm_tag` FOREIGN KEY (`tag_id`) REFERENCES `post_tags` (`tag_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path.post_tag_mappings: ~0 rows (approximately)

-- Dumping structure for table new_path.recovery_goals
CREATE TABLE IF NOT EXISTS `recovery_goals` (
  `goal_id` int NOT NULL AUTO_INCREMENT,
  `plan_id` int NOT NULL,
  `goal_type` enum('short_term','long_term') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `target_days` int DEFAULT NULL,
  `current_progress` int DEFAULT '0',
  `status` enum('in_progress','achieved','failed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'in_progress',
  `achieved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`goal_id`),
  KEY `idx_plan` (`plan_id`),
  CONSTRAINT `fk_goal_plan` FOREIGN KEY (`plan_id`) REFERENCES `recovery_plans` (`plan_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path.recovery_goals: ~6 rows (approximately)
INSERT INTO `recovery_goals` (`goal_id`, `plan_id`, `goal_type`, `title`, `description`, `target_days`, `current_progress`, `status`, `achieved_at`, `created_at`, `updated_at`) VALUES
	(2, 11, 'short_term', 'Puka nodi seetma', '', 7, 0, 'in_progress', NULL, '2026-01-11 12:10:29', '2026-01-11 12:10:29'),
	(3, 11, 'long_term', 'pakayade', '', 90, 0, 'in_progress', NULL, '2026-01-11 12:10:29', '2026-01-11 12:10:29'),
	(4, 12, 'short_term', 'To get rid of drugs', '', 7, 0, 'in_progress', NULL, '2026-01-19 06:01:32', '2026-01-19 06:01:32'),
	(5, 12, 'long_term', 'To get rid of drugs', '', 90, 0, 'in_progress', NULL, '2026-01-19 06:01:32', '2026-01-19 06:01:32'),
	(6, 13, 'short_term', 'To get rid of drugs', '', 7, 1, 'in_progress', NULL, '2026-01-19 08:46:35', '2026-04-16 08:22:21'),
	(7, 13, 'long_term', 'To get rid of drugs', '', 90, 2, 'in_progress', NULL, '2026-01-19 08:46:35', '2026-04-16 08:22:32');

-- Dumping structure for table new_path.recovery_plans
CREATE TABLE IF NOT EXISTS `recovery_plans` (
  `plan_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `counselor_id` int DEFAULT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `category` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `plan_type` enum('counselor','self') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'self',
  `status` enum('draft','active','paused','completed','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'draft',
  `start_date` date DEFAULT NULL,
  `target_completion_date` date DEFAULT NULL,
  `actual_completion_date` date DEFAULT NULL,
  `progress_percentage` int DEFAULT '0',
  `custom_notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_template` tinyint(1) DEFAULT '0',
  `template_source_id` int DEFAULT NULL,
  `assigned_status` enum('pending','accepted','rejected') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`plan_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_counselor` (`counselor_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_plan_counselor` FOREIGN KEY (`counselor_id`) REFERENCES `counselors` (`counselor_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_plan_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path.recovery_plans: ~7 rows (approximately)
INSERT INTO `recovery_plans` (`plan_id`, `user_id`, `counselor_id`, `title`, `description`, `category`, `plan_type`, `status`, `start_date`, `target_completion_date`, `actual_completion_date`, `progress_percentage`, `custom_notes`, `created_at`, `updated_at`, `is_template`, `template_source_id`, `assigned_status`) VALUES
	(11, 2, 1, 'General Addiction Recovery Plan', 'A flexible recovery plan adaptable to various types of addiction, focusing on individual needs and long-term wellness.', NULL, 'counselor', 'completed', '2026-01-11', '2026-04-11', '2026-03-30', 100, 'Generated plan for substance addiction recovery. Duration: 3 months. Please customize based on client\'s specific needs.', '2026-01-11 12:10:29', '2026-03-30 13:18:21', 0, NULL, 'accepted'),
	(12, 2, 1, 'General Addiction Recovery Plan', 'A flexible recovery plan adaptable to various types of addiction, focusing on individual needs and long-term wellness.', NULL, 'counselor', 'paused', '2026-01-19', '2026-04-19', NULL, 22, 'Generated plan for substance addiction recovery. Duration: 3 months. Please customize based on client\'s specific needs.', '2026-01-19 06:01:32', '2026-01-19 08:46:48', 0, NULL, 'accepted'),
	(13, 2, 1, 'General Addiction Recovery Plan', 'A flexible recovery plan adaptable to various types of addiction, focusing on individual needs and long-term wellness.', NULL, 'counselor', 'active', '2026-01-19', '2026-04-19', NULL, 89, 'Generated plan for substance addiction recovery. Duration: 3 months. Please customize based on client\'s specific needs.', '2026-01-19 08:46:35', '2026-04-04 17:31:17', 0, NULL, 'accepted'),
	(14, 10, NULL, 'Professional Counseling Path', 'Work with verified experts to build a tailored plan and schedule sessions.', NULL, 'counselor', 'active', '2026-02-26', NULL, NULL, 0, NULL, '2026-02-25 19:53:11', '2026-02-25 19:53:11', 0, NULL, NULL),
	(15, 11, NULL, 'Self-Guided Journey', 'Take control at your own pace with system-guided goals and daily tracking.', NULL, 'self', 'active', '2026-04-02', NULL, NULL, 0, NULL, '2026-04-01 20:15:01', '2026-04-01 20:15:01', 0, NULL, NULL),
	(16, 12, NULL, 'Self-Guided Journey', 'Take control at your own pace with system-guided goals and daily tracking.', NULL, 'self', 'active', '2026-04-03', NULL, NULL, 0, NULL, '2026-04-02 19:47:21', '2026-04-02 19:47:21', 0, NULL, NULL),
	(17, 2, 1, 'General Addiction Recovery Plan', 'A flexible recovery plan adaptable to various types of addiction, focusing on individual needs and long-term wellness.', '', 'counselor', 'active', '2026-04-04', '2026-07-04', NULL, 22, 'Generated plan for substance addiction recovery. Duration: 3 months. Please customize based on client\'s specific needs.', '2026-04-04 16:09:37', '2026-04-04 17:30:35', 0, NULL, 'accepted');

-- Dumping structure for table new_path.recovery_tasks
CREATE TABLE IF NOT EXISTS `recovery_tasks` (
  `task_id` int NOT NULL AUTO_INCREMENT,
  `plan_id` int NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `task_type` enum('journal','meditation','session','exercise','custom') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'custom',
  `status` enum('pending','in_progress','completed','skipped') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `priority` enum('low','medium','high') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'medium',
  `due_date` date DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `is_recurring` tinyint(1) DEFAULT '0',
  `recurrence_pattern` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `phase` int DEFAULT '1',
  PRIMARY KEY (`task_id`),
  KEY `idx_plan` (`plan_id`),
  KEY `idx_status` (`status`),
  KEY `idx_due_date` (`due_date`),
  CONSTRAINT `fk_task_plan` FOREIGN KEY (`plan_id`) REFERENCES `recovery_plans` (`plan_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=100 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path.recovery_tasks: ~36 rows (approximately)
INSERT INTO `recovery_tasks` (`task_id`, `plan_id`, `title`, `description`, `task_type`, `status`, `priority`, `due_date`, `completed_at`, `is_recurring`, `recurrence_pattern`, `sort_order`, `created_at`, `updated_at`, `phase`) VALUES
	(64, 11, 'Initial assessment', '', 'custom', 'completed', 'medium', NULL, '2026-01-11 15:33:50', 0, '', 0, '2026-01-11 12:10:29', '2026-01-11 15:33:50', 1),
	(65, 11, 'Goal setting', '', 'custom', 'completed', 'medium', NULL, '2026-01-11 15:33:48', 0, '', 0, '2026-01-11 12:10:29', '2026-01-11 15:33:48', 1),
	(66, 11, 'Build support system', '', 'custom', 'completed', 'medium', NULL, '2026-01-11 15:33:37', 0, '', 0, '2026-01-11 12:10:29', '2026-01-11 15:33:37', 1),
	(67, 11, 'Regular therapy', '', 'custom', 'completed', 'medium', NULL, '2026-01-11 15:33:45', 0, '', 0, '2026-01-11 12:10:29', '2026-01-11 15:33:45', 2),
	(68, 11, 'Develop coping skills', '', 'custom', 'completed', 'medium', NULL, '2026-01-11 15:33:54', 0, '', 0, '2026-01-11 12:10:29', '2026-01-11 15:33:54', 2),
	(69, 11, 'Healthy routines', '', 'custom', 'completed', 'medium', NULL, '2026-01-11 15:33:56', 0, '', 0, '2026-01-11 12:10:29', '2026-01-11 15:33:56', 2),
	(70, 11, 'Relapse prevention', '', 'custom', 'completed', 'medium', NULL, '2026-02-28 12:34:35', 0, '', 0, '2026-01-11 12:10:29', '2026-02-28 12:34:35', 3),
	(71, 11, 'Life skills training', '', 'custom', 'completed', 'medium', NULL, '2026-01-11 15:33:52', 0, '', 0, '2026-01-11 12:10:29', '2026-01-11 15:33:52', 3),
	(72, 11, 'Future planning', '', 'custom', 'completed', 'medium', NULL, '2026-03-30 13:18:21', 0, '', 0, '2026-01-11 12:10:29', '2026-03-30 13:18:21', 3),
	(73, 12, 'Initial assessment', '', 'custom', 'completed', 'medium', NULL, '2026-01-19 08:45:16', 0, '', 0, '2026-01-19 06:01:32', '2026-01-19 08:45:16', 1),
	(74, 12, 'Goal setting', '', 'custom', 'completed', 'medium', NULL, '2026-01-19 08:45:19', 0, '', 0, '2026-01-19 06:01:32', '2026-01-19 08:45:19', 1),
	(75, 12, 'Build support system', '', 'custom', 'pending', 'medium', NULL, NULL, 0, '', 0, '2026-01-19 06:01:32', '2026-01-19 06:01:32', 1),
	(76, 12, 'Regular therapy', '', 'custom', 'pending', 'medium', NULL, NULL, 0, '', 0, '2026-01-19 06:01:32', '2026-01-19 06:01:32', 2),
	(77, 12, 'Develop coping skills', '', 'custom', 'pending', 'medium', NULL, NULL, 0, '', 0, '2026-01-19 06:01:32', '2026-01-19 06:01:32', 2),
	(78, 12, 'Healthy routines', '', 'custom', 'pending', 'medium', NULL, NULL, 0, '', 0, '2026-01-19 06:01:32', '2026-01-19 06:01:32', 2),
	(79, 12, 'Relapse prevention', '', 'custom', 'pending', 'medium', NULL, NULL, 0, '', 0, '2026-01-19 06:01:32', '2026-01-19 06:01:32', 3),
	(80, 12, 'Life skills training', '', 'custom', 'pending', 'medium', NULL, NULL, 0, '', 0, '2026-01-19 06:01:32', '2026-01-19 06:01:32', 3),
	(81, 12, 'Future planning', '', 'custom', 'pending', 'medium', NULL, NULL, 0, '', 0, '2026-01-19 06:01:32', '2026-01-19 06:01:32', 3),
	(82, 13, 'Initial assessment', '', 'custom', 'completed', 'medium', NULL, '2026-04-04 17:30:10', 0, '', 0, '2026-01-19 08:46:35', '2026-04-04 17:30:10', 1),
	(83, 13, 'Goal setting', '', 'custom', 'completed', 'medium', NULL, '2026-04-04 17:30:15', 0, '', 0, '2026-01-19 08:46:35', '2026-04-04 17:30:15', 1),
	(84, 13, 'Build support system', '', 'custom', 'completed', 'medium', NULL, '2026-04-04 17:30:27', 0, '', 0, '2026-01-19 08:46:35', '2026-04-04 17:30:27', 1),
	(85, 13, 'Regular therapy', '', 'custom', 'completed', 'medium', NULL, '2026-04-04 17:30:18', 0, '', 0, '2026-01-19 08:46:35', '2026-04-04 17:30:18', 2),
	(86, 13, 'Develop coping skills', '', 'custom', 'completed', 'medium', NULL, '2026-04-04 17:30:20', 0, '', 0, '2026-01-19 08:46:35', '2026-04-04 17:30:20', 2),
	(87, 13, 'Healthy routines', '', 'custom', 'completed', 'medium', NULL, '2026-04-04 17:30:48', 0, '', 0, '2026-01-19 08:46:35', '2026-04-04 17:30:48', 2),
	(88, 13, 'Relapse prevention', '', 'custom', 'completed', 'medium', NULL, '2026-04-04 17:31:11', 0, '', 0, '2026-01-19 08:46:35', '2026-04-04 17:31:11', 3),
	(89, 13, 'Life skills training', '', 'custom', 'completed', 'medium', NULL, '2026-04-04 17:31:17', 0, '', 0, '2026-01-19 08:46:35', '2026-04-04 17:31:17', 3),
	(90, 13, 'Future planning', '', 'custom', 'pending', 'medium', NULL, NULL, 0, '', 0, '2026-01-19 08:46:35', '2026-01-19 08:46:35', 3),
	(91, 17, 'Initial assessment', '', 'custom', 'completed', 'medium', NULL, '2026-04-04 17:28:37', 0, '', 0, '2026-04-04 16:09:37', '2026-04-04 17:28:37', 1),
	(92, 17, 'Goal setting', '', 'custom', 'pending', 'medium', NULL, NULL, 0, '', 1, '2026-04-04 16:09:37', '2026-04-04 16:09:37', 1),
	(93, 17, 'Build support system', '', 'custom', 'pending', 'medium', NULL, NULL, 0, '', 2, '2026-04-04 16:09:37', '2026-04-04 16:09:37', 1),
	(94, 17, 'Regular therapy', '', 'custom', 'pending', 'medium', NULL, NULL, 0, '', 3, '2026-04-04 16:09:37', '2026-04-04 16:09:37', 2),
	(95, 17, 'Develop coping skills', '', 'custom', 'pending', 'medium', NULL, NULL, 0, '', 4, '2026-04-04 16:09:37', '2026-04-04 16:09:37', 2),
	(96, 17, 'Healthy routines', '', 'custom', 'pending', 'medium', NULL, NULL, 0, '', 5, '2026-04-04 16:09:37', '2026-04-04 16:09:37', 2),
	(97, 17, 'Relapse prevention', '', 'custom', 'pending', 'medium', NULL, NULL, 0, '', 6, '2026-04-04 16:09:37', '2026-04-04 16:09:37', 3),
	(98, 17, 'Life skills training', '', 'custom', 'pending', 'medium', NULL, NULL, 0, '', 7, '2026-04-04 16:09:37', '2026-04-04 16:09:37', 3),
	(99, 17, 'Future planning', '', 'custom', 'completed', 'medium', NULL, '2026-04-04 17:30:35', 0, '', 8, '2026-04-04 16:09:37', '2026-04-04 17:30:35', 3);

-- Dumping structure for table new_path.refund_disputes
CREATE TABLE IF NOT EXISTS `refund_disputes` (
  `dispute_id` int NOT NULL AUTO_INCREMENT,
  `transaction_id` int NOT NULL,
  `user_id` int NOT NULL,
  `issue_type` enum('missed_session','quality_complaint','technical_issue','billing_error','unauthorized_charge','other') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `requested_amount` decimal(10,2) DEFAULT NULL,
  `status` enum('pending','under_review','approved','rejected','resolved') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `admin_notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `resolved_by` int DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `refunded_amount` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`dispute_id`),
  KEY `idx_transaction` (`transaction_id`),
  KEY `idx_status` (`status`),
  KEY `fk_dispute_user` (`user_id`),
  KEY `fk_dispute_admin` (`resolved_by`),
  CONSTRAINT `fk_dispute_admin` FOREIGN KEY (`resolved_by`) REFERENCES `admin` (`admin_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_dispute_txn` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`transaction_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_dispute_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path.refund_disputes: ~0 rows (approximately)

-- Dumping structure for table new_path.relapse_history
CREATE TABLE IF NOT EXISTS `relapse_history` (
  `relapse_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `relapse_date` date NOT NULL,
  `previous_streak_days` int DEFAULT '0',
  `reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`relapse_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_date` (`relapse_date`),
  CONSTRAINT `fk_relapse_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path.relapse_history: ~0 rows (approximately)

-- Dumping structure for table new_path.reschedule_requests
CREATE TABLE IF NOT EXISTS `reschedule_requests` (
  `request_id` int NOT NULL AUTO_INCREMENT,
  `session_id` int NOT NULL,
  `user_id` int NOT NULL,
  `counselor_id` int NOT NULL,
  `reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` enum('pending','approved','rejected') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `counselor_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `requested_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `credit_used` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`request_id`),
  KEY `idx_rr_session` (`session_id`),
  KEY `idx_rr_user` (`user_id`),
  KEY `idx_rr_counselor` (`counselor_id`),
  CONSTRAINT `fk_rr_session` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`session_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_rr_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path.reschedule_requests: ~4 rows (approximately)
INSERT INTO `reschedule_requests` (`request_id`, `session_id`, `user_id`, `counselor_id`, `reason`, `status`, `counselor_note`, `requested_at`, `reviewed_at`, `credit_used`) VALUES
	(6, 11, 2, 1, 'cbc', 'approved', '', '2026-04-05 07:32:49', '2026-04-05 07:35:33', 1),
	(7, 22, 2, 1, 'h,jh,m', 'approved', '', '2026-04-12 17:06:47', '2026-04-12 17:08:08', 1),
	(8, 29, 2, 1, 'gdfg', 'rejected', '', '2026-04-13 18:10:57', '2026-04-14 06:44:41', 0),
	(9, 25, 2, 1, 'abcdef', 'approved', '', '2026-04-13 18:11:03', '2026-04-15 07:51:23', 0),
	(10, 30, 2, 1, 'hello', 'approved', '', '2026-04-15 09:38:45', '2026-04-15 09:42:58', 0);

-- Dumping structure for table new_path.saved_jobs
CREATE TABLE IF NOT EXISTS `saved_jobs` (
  `saved_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `job_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`saved_id`),
  UNIQUE KEY `idx_user_job` (`user_id`,`job_id`),
  KEY `fk_sj_job` (`job_id`),
  CONSTRAINT `fk_sj_job` FOREIGN KEY (`job_id`) REFERENCES `job_posts` (`job_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_sj_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path.saved_jobs: ~0 rows (approximately)

-- Dumping structure for table new_path.saved_posts
CREATE TABLE IF NOT EXISTS `saved_posts` (
  `saved_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `post_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`saved_id`),
  UNIQUE KEY `idx_user_post` (`user_id`,`post_id`),
  KEY `fk_saved_post` (`post_id`),
  CONSTRAINT `fk_saved_post` FOREIGN KEY (`post_id`) REFERENCES `community_posts` (`post_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_saved_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path.saved_posts: ~2 rows (approximately)
INSERT INTO `saved_posts` (`saved_id`, `user_id`, `post_id`, `created_at`) VALUES
	(1, 2, 4, '2026-04-04 19:29:15');

-- Dumping structure for table new_path.sessions
CREATE TABLE IF NOT EXISTS `sessions` (
  `session_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `counselor_id` int NOT NULL,
  `session_datetime` datetime NOT NULL,
  `duration_minutes` int DEFAULT '60',
  `extended_minutes` int DEFAULT '0',
  `extension_fee` decimal(10,2) DEFAULT '0.00',
  `session_type` enum('video','audio','chat','in_person') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'video',
  `status` enum('scheduled','confirmed','in_progress','completed','cancelled','no_show') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'scheduled',
  `location` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meeting_link` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meet_space_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `session_notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `counselor_private_notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `rating` int DEFAULT NULL,
  `review` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `cancelled_by` int DEFAULT NULL,
  `cancellation_reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`session_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_counselor` (`counselor_id`),
  KEY `idx_datetime` (`session_datetime`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_session_counselor` FOREIGN KEY (`counselor_id`) REFERENCES `counselors` (`counselor_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_session_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path.sessions: ~33 rows (approximately)
INSERT INTO `sessions` (`session_id`, `user_id`, `counselor_id`, `session_datetime`, `duration_minutes`, `extended_minutes`, `extension_fee`, `session_type`, `status`, `location`, `meeting_link`, `meet_space_name`, `session_notes`, `counselor_private_notes`, `rating`, `review`, `cancelled_by`, `cancellation_reason`, `created_at`, `updated_at`) VALUES
	(1, 2, 1, '2026-04-04 02:08:55', 60, 0, 0.00, 'video', 'completed', NULL, 'dfsgfdgdfg', NULL, 'lool', NULL, 3, 'sf', NULL, NULL, '2026-01-09 16:38:56', '2026-04-16 05:49:48'),
	(2, 2, 1, '2026-04-12 11:00:00', 60, 0, 0.00, 'video', 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-28 12:44:38', '2026-04-13 18:01:34'),
	(3, 2, 1, '2026-03-30 09:00:00', 60, 0, 0.00, 'video', 'scheduled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-28 12:51:09', '2026-03-28 12:51:09'),
	(4, 2, 1, '2026-03-30 10:00:00', 60, 0, 0.00, 'video', 'scheduled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-28 16:23:03', '2026-03-28 16:23:03'),
	(5, 2, 1, '2026-03-30 12:00:00', 60, 0, 0.00, 'video', 'scheduled', NULL, 'https://meet.google.com/coe-uksq-mov', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-28 16:48:11', '2026-03-28 16:48:11'),
	(6, 2, 1, '2026-03-30 13:00:00', 60, 0, 0.00, 'video', 'scheduled', NULL, 'https://meet.google.com/oow-xsjh-csc', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-28 16:51:44', '2026-03-28 16:51:44'),
	(7, 2, 1, '2026-03-30 16:00:00', 60, 0, 0.00, 'video', 'scheduled', NULL, 'https://meet.google.com/fui-tqzk-pze', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-28 16:59:58', '2026-03-28 16:59:58'),
	(8, 2, 1, '2026-03-30 14:00:00', 60, 0, 0.00, 'video', 'scheduled', NULL, 'https://meet.google.com/hiu-ovsv-fxc', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-28 17:30:50', '2026-03-28 17:30:50'),
	(9, 2, 1, '2026-03-30 15:00:00', 60, 0, 0.00, 'video', 'scheduled', NULL, 'https://meet.google.com/cff-uesv-ekp', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-28 17:58:53', '2026-03-28 17:58:53'),
	(10, 2, 1, '2026-04-06 10:00:00', 60, 0, 0.00, 'video', 'cancelled', NULL, 'https://meet.google.com/zir-nnqp-nws', NULL, NULL, NULL, NULL, NULL, 5, 'Reschedule approved by counselor', '2026-03-30 13:27:07', '2026-04-04 20:15:47'),
	(11, 2, 1, '2026-04-06 11:00:00', 60, 0, 0.00, 'video', 'cancelled', NULL, 'https://meet.google.com/yvu-amax-mfc', NULL, NULL, NULL, NULL, NULL, 5, 'Reschedule approved by counselor', '2026-03-30 13:31:01', '2026-04-05 07:35:33'),
	(12, 2, 1, '2026-04-06 14:00:00', 60, 0, 0.00, 'video', 'scheduled', NULL, 'https://meet.google.com/jzm-xbyr-djb', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-30 13:37:10', '2026-03-30 13:37:10'),
	(13, 2, 1, '2026-04-06 13:00:00', 60, 0, 0.00, 'video', 'scheduled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-30 17:56:07', '2026-03-30 17:56:07'),
	(14, 2, 1, '2026-04-13 16:00:00', 60, 0, 0.00, 'video', 'completed', NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-03-30 17:58:40', '2026-04-15 14:20:59'),
	(15, 2, 1, '2026-04-06 09:00:00', 60, 0, 0.00, 'video', 'cancelled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, 'vxcv', '2026-04-02 08:18:29', '2026-04-04 19:44:19'),
	(16, 2, 1, '2026-04-06 12:00:00', 60, 0, 0.00, 'video', 'scheduled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-04 17:44:45', '2026-04-04 17:44:45'),
	(17, 2, 1, '2026-04-06 15:00:00', 60, 0, 0.00, 'video', 'scheduled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-04 19:11:12', '2026-04-04 19:11:12'),
	(18, 2, 1, '2026-04-06 20:00:00', 60, 0, 0.00, 'video', 'scheduled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-05 18:25:08', '2026-04-05 18:25:08'),
	(19, 2, 1, '2026-04-06 20:00:00', 60, 0, 0.00, 'video', 'scheduled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-05 18:26:00', '2026-04-05 18:26:00'),
	(20, 2, 1, '2026-04-06 16:00:00', 60, 0, 0.00, 'video', 'scheduled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-05 18:28:47', '2026-04-05 18:28:47'),
	(21, 15, 1, '2026-04-20 15:00:00', 60, 0, 0.00, 'video', 'scheduled', NULL, NULL, NULL, NULL, 'hiiii', NULL, NULL, NULL, NULL, '2026-04-11 11:02:53', '2026-04-12 16:13:49'),
	(22, 2, 1, '2026-04-13 09:00:00', 60, 0, 0.00, 'video', 'cancelled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 5, 'Reschedule approved by counselor', '2026-04-11 17:40:56', '2026-04-12 17:08:08'),
	(23, 2, 1, '2026-04-13 19:00:00', 60, 0, 0.00, 'video', 'scheduled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-13 09:42:46', '2026-04-13 09:42:46'),
	(24, 2, 1, '2026-04-13 20:00:00', 60, 0, 0.00, 'video', 'scheduled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-13 12:46:59', '2026-04-13 12:46:59'),
	(25, 2, 1, '2026-04-20 09:00:00', 60, 0, 0.00, 'video', 'cancelled', NULL, NULL, NULL, NULL, 'ddfdf', NULL, NULL, 5, 'Reschedule approved by counselor', '2026-04-13 13:12:23', '2026-04-15 07:51:24'),
	(26, 2, 1, '2026-04-20 14:00:00', 60, 0, 0.00, 'video', 'scheduled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-13 13:17:21', '2026-04-13 13:17:21'),
	(27, 2, 1, '2026-04-20 13:00:00', 60, 0, 0.00, 'video', 'scheduled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-13 13:22:50', '2026-04-13 13:22:50'),
	(28, 2, 1, '2026-04-20 12:00:00', 60, 0, 0.00, 'video', 'scheduled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-13 13:34:43', '2026-04-13 13:34:43'),
	(29, 2, 1, '2026-04-20 10:00:00', 60, 0, 0.00, 'video', 'completed', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-13 13:50:49', '2026-04-15 16:05:55'),
	(30, 2, 1, '2026-04-20 11:00:00', 60, 0, 0.00, 'video', 'cancelled', NULL, 'https://meet.google.com/xop-fpsb-kvy', 'spaces/I40mXr9dLpoB', NULL, NULL, NULL, NULL, 5, 'Reschedule approved by counselor', '2026-04-13 13:52:28', '2026-04-15 09:42:58'),
	(31, 20, 1, '2026-04-20 19:00:00', 60, 0, 0.00, 'video', 'scheduled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-13 17:18:31', '2026-04-13 17:18:31'),
	(32, 19, 1, '2026-04-20 16:00:00', 60, 0, 0.00, 'video', 'scheduled', NULL, 'https://meet.google.com/tsi-cfmm-dii', 'spaces/FBgN7qD-7IwB', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-13 17:22:17', '2026-04-13 17:22:17'),
	(33, 20, 1, '2026-04-27 11:00:00', 60, 0, 0.00, 'video', 'scheduled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-13 17:27:40', '2026-04-13 17:27:40'),
	(34, 20, 1, '2026-04-20 20:00:00', 60, 0, 0.00, 'video', 'scheduled', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-13 17:32:13', '2026-04-13 17:32:13'),
	(35, 20, 1, '2026-04-27 15:00:00', 60, 0, 0.00, 'video', 'scheduled', NULL, 'https://meet.google.com/nbb-kjcn-zfa', 'spaces/CgW0ag2eHhMB', NULL, '', NULL, NULL, NULL, NULL, '2026-04-13 17:34:26', '2026-04-15 12:04:47');

-- Dumping structure for table new_path.session_disputes
CREATE TABLE IF NOT EXISTS `session_disputes` (
  `dispute_id` int NOT NULL AUTO_INCREMENT,
  `session_id` int NOT NULL,
  `reported_by` int NOT NULL,
  `reason` enum('no_show','other') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no_show',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` enum('pending','reviewed','resolved','dismissed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `reviewed_by` int DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `admin_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`dispute_id`),
  KEY `idx_sd_session` (`session_id`),
  KEY `idx_sd_reporter` (`reported_by`),
  CONSTRAINT `fk_sd_reporter` FOREIGN KEY (`reported_by`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_sd_session` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`session_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path.session_disputes: ~0 rows (approximately)
INSERT INTO `session_disputes` (`dispute_id`, `session_id`, `reported_by`, `reason`, `description`, `status`, `reviewed_by`, `reviewed_at`, `admin_note`, `created_at`) VALUES
	(1, 14, 2, 'no_show', 'niga just did not come', 'pending', NULL, NULL, NULL, '2026-04-15 14:22:27');

-- Dumping structure for table new_path.session_messages
CREATE TABLE IF NOT EXISTS `session_messages` (
  `message_id` int NOT NULL AUTO_INCREMENT,
  `session_id` int NOT NULL,
  `sender_id` int NOT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`message_id`),
  KEY `idx_session` (`session_id`),
  KEY `idx_sender` (`sender_id`),
  CONSTRAINT `fk_sm_sender` FOREIGN KEY (`sender_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_sm_session` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`session_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path.session_messages: ~16 rows (approximately)
INSERT INTO `session_messages` (`message_id`, `session_id`, `sender_id`, `message`, `created_at`) VALUES
	(1, 1, 2, 'hello', '2026-04-02 08:23:03'),
	(2, 1, 2, 'hello', '2026-04-04 17:47:01'),
	(3, 1, 5, 'hi', '2026-04-04 17:49:17'),
	(4, 1, 5, 'hi', '2026-04-04 18:13:22'),
	(5, 1, 2, 'fgf', '2026-04-04 18:13:50'),
	(6, 2, 2, 'hello', '2026-04-13 18:02:14'),
	(7, 2, 5, 'hi nigga', '2026-04-14 06:48:04'),
	(8, 2, 5, 'HII\\', '2026-04-15 07:56:33'),
	(9, 2, 2, 'sudda mk', '2026-04-15 12:10:13'),
	(10, 2, 2, 'sapede inne', '2026-04-15 12:10:30'),
	(11, 2, 5, 'kape inne', '2026-04-15 12:26:34'),
	(12, 2, 2, 'mk mk ithin', '2026-04-15 12:27:15'),
	(13, 2, 2, 'sudda', '2026-04-15 12:35:23'),
	(14, 2, 5, 'mko wenne', '2026-04-15 12:35:53'),
	(15, 2, 2, 'sape sape', '2026-04-15 12:36:07'),
	(16, 2, 5, 'elakiri gammak ne', '2026-04-15 12:36:29'),
	(17, 29, 2, 'pakayade', '2026-04-16 05:54:38'),
	(18, 29, 5, 'ooo', '2026-04-16 05:56:25');

-- Dumping structure for table new_path.support_groups
CREATE TABLE IF NOT EXISTS `support_groups` (
  `group_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `category` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meeting_schedule` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meeting_link` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `max_members` int DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`group_id`),
  KEY `idx_active` (`is_active`),
  KEY `fk_sg_admin` (`created_by`),
  CONSTRAINT `fk_sg_admin` FOREIGN KEY (`created_by`) REFERENCES `admin` (`admin_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path.support_groups: ~4 rows (approximately)
INSERT INTO `support_groups` (`group_id`, `name`, `description`, `category`, `meeting_schedule`, `meeting_link`, `max_members`, `is_active`, `created_by`, `created_at`, `updated_at`) VALUES
	(1, 'AA Meeting Support', 'Alcoholics Anonymous peer support group', 'alcohol', NULL, NULL, NULL, 1, 1, '2026-04-02 19:55:16', '2026-04-02 19:55:16'),
	(2, 'Substance Recovery', 'Support for recovering from substance addiction', 'substance', NULL, NULL, NULL, 1, 1, '2026-04-02 19:55:16', '2026-04-02 19:55:16'),
	(3, 'Gambling Recovery', 'Support for gambling addiction recovery', 'gambling', NULL, NULL, NULL, 1, 1, '2026-04-02 19:55:16', '2026-04-02 19:55:16'),
	(4, 'General Recovery', 'General recovery support for all types of addiction', 'general', NULL, NULL, NULL, 1, 1, '2026-04-02 19:55:16', '2026-04-02 19:55:16');

-- Dumping structure for table new_path.support_group_members
CREATE TABLE IF NOT EXISTS `support_group_members` (
  `membership_id` int NOT NULL AUTO_INCREMENT,
  `group_id` int NOT NULL,
  `user_id` int NOT NULL,
  `role` enum('member','moderator','leader') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'member',
  `joined_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`membership_id`),
  UNIQUE KEY `idx_group_user` (`group_id`,`user_id`),
  KEY `fk_sgm_user` (`user_id`),
  CONSTRAINT `fk_sgm_group` FOREIGN KEY (`group_id`) REFERENCES `support_groups` (`group_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_sgm_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path.support_group_members: ~0 rows (approximately)

-- Dumping structure for table new_path.support_group_messages
CREATE TABLE IF NOT EXISTS `support_group_messages` (
  `message_id` int NOT NULL AUTO_INCREMENT,
  `group_id` int NOT NULL,
  `user_id` int NOT NULL,
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_pinned` tinyint(1) DEFAULT '0',
  `is_deleted` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`message_id`),
  KEY `idx_group` (`group_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path.support_group_messages: ~0 rows (approximately)

-- Dumping structure for table new_path.support_group_sessions
CREATE TABLE IF NOT EXISTS `support_group_sessions` (
  `group_session_id` int NOT NULL AUTO_INCREMENT,
  `group_id` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `session_datetime` datetime NOT NULL,
  `duration_minutes` int DEFAULT '60',
  `session_type` enum('video','in_person') COLLATE utf8mb4_unicode_ci DEFAULT 'video',
  `meeting_link` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meeting_location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `max_participants` int DEFAULT NULL,
  `is_recurring` tinyint(1) DEFAULT '0',
  `recurrence_pattern` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('scheduled','completed','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'scheduled',
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`group_session_id`),
  KEY `idx_group_datetime` (`group_id`,`session_datetime`),
  KEY `idx_created_by` (`created_by`),
  CONSTRAINT `fk_sgs_admin` FOREIGN KEY (`created_by`) REFERENCES `admin` (`admin_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_sgs_group` FOREIGN KEY (`group_id`) REFERENCES `support_groups` (`group_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path.support_group_sessions: ~0 rows (approximately)

-- Dumping structure for table new_path.support_group_session_registrations
CREATE TABLE IF NOT EXISTS `support_group_session_registrations` (
  `registration_id` int NOT NULL AUTO_INCREMENT,
  `group_session_id` int NOT NULL,
  `user_id` int NOT NULL,
  `registered_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`registration_id`),
  UNIQUE KEY `idx_session_user` (`group_session_id`,`user_id`),
  KEY `fk_sgsr_session` (`group_session_id`),
  KEY `fk_sgsr_user` (`user_id`),
  CONSTRAINT `fk_sgsr_session` FOREIGN KEY (`group_session_id`) REFERENCES `support_group_sessions` (`group_session_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_sgsr_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path.support_group_session_registrations: ~0 rows (approximately)

-- Dumping structure for table new_path.system_settings
CREATE TABLE IF NOT EXISTS `system_settings` (
  `setting_id` int NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `setting_value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `updated_by` int DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`setting_id`),
  UNIQUE KEY `idx_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path.system_settings: ~0 rows (approximately)

-- Dumping structure for table new_path.task_change_requests
CREATE TABLE IF NOT EXISTS `task_change_requests` (
  `request_id` int NOT NULL AUTO_INCREMENT,
  `task_id` int NOT NULL,
  `plan_id` int NOT NULL,
  `user_id` int NOT NULL,
  `counselor_id` int NOT NULL,
  `reason` text NOT NULL,
  `requested_change` text NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `counselor_note` varchar(500) DEFAULT NULL,
  `resolved_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`request_id`),
  KEY `task_id` (`task_id`),
  KEY `plan_id` (`plan_id`),
  KEY `idx_user_status` (`user_id`,`status`),
  KEY `idx_counselor_status` (`counselor_id`,`status`),
  CONSTRAINT `task_change_requests_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `recovery_tasks` (`task_id`) ON DELETE CASCADE,
  CONSTRAINT `task_change_requests_ibfk_2` FOREIGN KEY (`plan_id`) REFERENCES `recovery_plans` (`plan_id`) ON DELETE CASCADE,
  CONSTRAINT `task_change_requests_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `task_change_requests_ibfk_4` FOREIGN KEY (`counselor_id`) REFERENCES `counselors` (`counselor_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table new_path.task_change_requests: ~1 rows (approximately)

-- Dumping structure for table new_path.transactions
CREATE TABLE IF NOT EXISTS `transactions` (
  `transaction_id` int NOT NULL AUTO_INCREMENT,
  `transaction_uuid` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `session_id` int DEFAULT NULL,
  `user_id` int NOT NULL,
  `counselor_id` int DEFAULT NULL,
  `payment_method_id` int DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'LKR',
  `payment_type` enum('session','subscription','tip','refund','reschedule_credit') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'session',
  `status` enum('pending','completed','failed','refunded','disputed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `payhere_order_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payhere_payment_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payhere_status_code` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stripe_payment_intent_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `failure_reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `processed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`transaction_id`),
  UNIQUE KEY `idx_uuid` (`transaction_uuid`),
  KEY `idx_user` (`user_id`),
  KEY `idx_counselor` (`counselor_id`),
  KEY `idx_status` (`status`),
  KEY `idx_created` (`created_at`),
  KEY `fk_txn_session` (`session_id`),
  KEY `fk_txn_pm` (`payment_method_id`),
  CONSTRAINT `fk_txn_counselor` FOREIGN KEY (`counselor_id`) REFERENCES `counselors` (`counselor_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_txn_pm` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`payment_method_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_txn_session` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`session_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_txn_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path.transactions: ~32 rows (approximately)
INSERT INTO `transactions` (`transaction_id`, `transaction_uuid`, `session_id`, `user_id`, `counselor_id`, `payment_method_id`, `amount`, `currency`, `payment_type`, `status`, `payhere_order_id`, `payhere_payment_id`, `payhere_status_code`, `stripe_payment_intent_id`, `failure_reason`, `processed_at`, `created_at`, `updated_at`) VALUES
	(1, 'd252b7caddb612f322cf50317b309ae7', 2, 2, 1, NULL, 3850.00, 'LKR', 'session', 'completed', 'HOLD-30', '', '2', NULL, NULL, '2026-03-28 12:44:38', '2026-03-28 12:44:38', '2026-03-28 12:44:38'),
	(2, '73197850ad0859f891a37aaa43f799cc', 3, 2, 1, NULL, 3850.00, 'LKR', 'session', 'completed', 'HOLD-31', '', '2', NULL, NULL, '2026-03-28 12:51:09', '2026-03-28 12:51:09', '2026-03-28 12:51:09'),
	(3, '715d86fa2409ee47a28211b76a50d2af', 4, 2, 1, NULL, 3850.00, 'LKR', 'session', 'completed', 'HOLD-39', '', '2', NULL, NULL, '2026-03-28 16:23:03', '2026-03-28 16:23:03', '2026-03-28 16:23:03'),
	(4, '56a47d716f3e98bb1df43819a3e4f874', 5, 2, 1, NULL, 3850.00, 'LKR', 'session', 'completed', 'HOLD-40', '', '2', NULL, NULL, '2026-03-28 16:48:11', '2026-03-28 16:48:11', '2026-03-28 16:48:11'),
	(5, '86c5fad9364f96aeb416d3066367ccfb', 6, 2, 1, NULL, 3850.00, 'LKR', 'session', 'completed', 'HOLD-41', '', '2', NULL, NULL, '2026-03-28 16:51:44', '2026-03-28 16:51:44', '2026-03-28 16:51:44'),
	(6, '8d527a4d8f7e76fd4d77a3075302dcc9', 7, 2, 1, NULL, 3850.00, 'LKR', 'session', 'completed', 'HOLD-42', '', '2', NULL, NULL, '2026-03-28 16:59:58', '2026-03-28 16:59:58', '2026-03-28 16:59:58'),
	(7, '03be04fcf3b7ad88306a6dd0533d1397', 8, 2, 1, NULL, 3850.00, 'LKR', 'session', 'completed', 'HOLD-43', '', '2', NULL, NULL, '2026-03-28 17:30:50', '2026-03-28 17:30:50', '2026-03-28 17:30:50'),
	(8, 'b1424e2fd1f4efa64554f5177dc57ce8', 9, 2, 1, NULL, 3850.00, 'LKR', 'session', 'completed', 'HOLD-44', '', '2', NULL, NULL, '2026-03-28 17:58:53', '2026-03-28 17:58:53', '2026-03-28 17:58:53'),
	(9, 'c6f927abef577311d987cd448776c578', 10, 2, 1, NULL, 3850.00, 'LKR', 'session', 'completed', 'HOLD-46', '', '2', NULL, NULL, '2026-03-30 13:27:07', '2026-03-30 13:27:07', '2026-03-30 13:27:07'),
	(10, '65a9b44a51c6d046fddf0fff435f51f4', 11, 2, 1, NULL, 3850.00, 'LKR', 'session', 'completed', 'HOLD-47', '', '2', NULL, NULL, '2026-03-30 13:31:01', '2026-03-30 13:31:01', '2026-03-30 13:31:01'),
	(11, 'ece74af422bc83a272be53dd4d51a090', 12, 2, 1, NULL, 3850.00, 'LKR', 'session', 'completed', 'HOLD-48', '', '2', NULL, NULL, '2026-03-30 13:37:10', '2026-03-30 13:37:10', '2026-03-30 13:37:10'),
	(12, '436357dc2a41918ef74144d706da6d58', 13, 2, 1, NULL, 3850.00, 'LKR', 'session', 'completed', 'HOLD-49', '', '2', NULL, NULL, '2026-03-30 17:56:07', '2026-03-30 17:56:07', '2026-03-30 17:56:07'),
	(13, '7972cbe391505e49010ad6cce572c5f4', 14, 2, 1, NULL, 3850.00, 'LKR', 'session', 'completed', 'HOLD-50', '', '2', NULL, NULL, '2026-03-30 17:58:40', '2026-03-30 17:58:40', '2026-03-30 17:58:40'),
	(14, '318c6c5eaaf5a2b4a53dc463aaf5d878', 15, 2, 1, NULL, 3850.00, 'LKR', 'session', 'completed', 'HOLD-51', '', '2', NULL, NULL, '2026-04-02 08:18:29', '2026-04-02 08:18:29', '2026-04-02 08:18:29'),
	(15, '1913ac3b87e6c8c2fa20b4a4ba81851a', 16, 2, 1, NULL, 3850.00, 'LKR', 'session', 'completed', 'HOLD-53', '', '2', NULL, NULL, '2026-04-04 17:44:45', '2026-04-04 17:44:45', '2026-04-04 17:44:45'),
	(16, 'd84239bc0abeab9e97d4804ed2f23141', 17, 2, 1, NULL, 3850.00, 'LKR', 'session', 'completed', 'HOLD-54', '', '2', NULL, NULL, '2026-04-04 19:11:12', '2026-04-04 19:11:12', '2026-04-04 19:11:12'),
	(17, 'c9cf5d077e5d4e22f4d8ab378cb6463f', 19, 2, 1, NULL, 0.00, 'LKR', 'session', 'completed', 'CREDIT-6', NULL, NULL, NULL, NULL, '2026-04-05 18:26:01', '2026-04-05 18:26:01', '2026-04-05 18:26:01'),
	(18, '4ae5e7ddae6073ca3352c86c27d994e3', 20, 2, 1, NULL, 3850.00, 'LKR', 'session', 'completed', 'HOLD-57', '', '2', NULL, NULL, '2026-04-05 18:28:47', '2026-04-05 18:28:47', '2026-04-05 18:28:47'),
	(19, 'f805ea8d0052ac366cc5d8b2d7075bb1', 21, 15, 1, NULL, 3850.00, 'LKR', 'session', 'completed', 'HOLD-59', '', '2', NULL, NULL, '2026-04-11 11:02:53', '2026-04-11 11:02:53', '2026-04-11 11:02:53'),
	(20, '2312224e8032fa01e6a8a1d6b99f4c25', 22, 2, 1, NULL, 3850.00, 'LKR', 'session', 'completed', 'HOLD-60', '', '2', NULL, NULL, '2026-04-11 17:40:56', '2026-04-11 17:40:56', '2026-04-11 17:40:56'),
	(21, '7ba2656418b727f491867ab71f6d1303', 23, 2, 1, NULL, 0.00, 'LKR', 'session', 'completed', 'CREDIT-7', NULL, NULL, NULL, NULL, '2026-04-13 09:42:47', '2026-04-13 09:42:47', '2026-04-13 09:42:47'),
	(22, '39d823f0bcb9bbaaae7e594dd11dbba2', 24, 2, 1, NULL, 3850.00, 'LKR', 'session', 'completed', 'HOLD-62', '', '2', NULL, NULL, '2026-04-13 12:46:59', '2026-04-13 12:46:59', '2026-04-13 12:46:59'),
	(23, 'c0aadea0a578d8cbe63c7fef56679331', 25, 2, 1, NULL, 3850.00, 'LKR', 'session', 'completed', 'HOLD-63', '', '2', NULL, NULL, '2026-04-13 13:12:23', '2026-04-13 13:12:23', '2026-04-13 13:12:23'),
	(24, '9dc388380e3ce2909fb5c8dc5940502b', 26, 2, 1, NULL, 3850.00, 'LKR', 'session', 'completed', 'HOLD-64', '', '2', NULL, NULL, '2026-04-13 13:17:21', '2026-04-13 13:17:21', '2026-04-13 13:17:21'),
	(25, 'eae045390f9f954b7dcd94beaefbd21a', 27, 2, 1, NULL, 3850.00, 'LKR', 'session', 'completed', 'HOLD-65', '', '2', NULL, NULL, '2026-04-13 13:22:50', '2026-04-13 13:22:50', '2026-04-13 13:22:50'),
	(26, 'ef42768c03ed0d23e6f77d2cd6247bb4', 28, 2, 1, NULL, 3850.00, 'LKR', 'session', 'completed', 'HOLD-66', '', '2', NULL, NULL, '2026-04-13 13:34:44', '2026-04-13 13:34:44', '2026-04-13 13:34:44'),
	(27, 'b5c2cca649c924a8979b019609ce02e5', 29, 2, 1, NULL, 3850.00, 'LKR', 'session', 'completed', 'HOLD-67', '', '2', NULL, NULL, '2026-04-13 13:50:49', '2026-04-13 13:50:49', '2026-04-13 13:50:49'),
	(28, 'b807576e9aec4a3824394b0da21814cd', 30, 2, 1, NULL, 3850.00, 'LKR', 'session', 'completed', 'HOLD-68', '', '2', NULL, NULL, '2026-04-13 13:52:28', '2026-04-13 13:52:28', '2026-04-13 13:52:28'),
	(29, '86d1e7a4142340c8bb55cef6442cd879', 31, 20, 1, NULL, 3850.00, 'LKR', 'session', 'completed', 'HOLD-69', '', '2', NULL, NULL, '2026-04-13 17:18:31', '2026-04-13 17:18:31', '2026-04-13 17:18:31'),
	(30, '6c45e829d6b028cd523e1ed47935b1b1', 32, 19, 1, NULL, 3850.00, 'LKR', 'session', 'completed', 'HOLD-70', '', '2', NULL, NULL, '2026-04-13 17:22:17', '2026-04-13 17:22:17', '2026-04-13 17:22:17'),
	(31, '4668eb17fddcd8b6e3ec20ce2a59bfb0', 33, 20, 1, NULL, 3850.00, 'LKR', 'session', 'completed', 'HOLD-71', '', '2', NULL, NULL, '2026-04-13 17:27:41', '2026-04-13 17:27:41', '2026-04-13 17:27:41'),
	(32, 'db38e79f361b8e0d0e56b73879e91d8b', 34, 20, 1, NULL, 3850.00, 'LKR', 'session', 'completed', 'HOLD-72', '', '2', NULL, NULL, '2026-04-13 17:32:13', '2026-04-13 17:32:13', '2026-04-13 17:32:13'),
	(33, '3aebaa906b4083636e4c2720e41917e7', 35, 20, 1, NULL, 3850.00, 'LKR', 'session', 'completed', 'HOLD-73', '', '2', NULL, NULL, '2026-04-13 17:34:26', '2026-04-13 17:34:26', '2026-04-13 17:34:26');

-- Dumping structure for table new_path.trigger_categories
CREATE TABLE IF NOT EXISTS `trigger_categories` (
  `category_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_default` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path.trigger_categories: ~5 rows (approximately)
INSERT INTO `trigger_categories` (`category_id`, `name`, `description`, `is_default`) VALUES
	(1, 'Stress', 'Work or life-related stress', 1),
	(2, 'Social', 'Social situations or peer pressure', 1),
	(3, 'Emotional', 'Emotional distress or mood changes', 1),
	(4, 'Environmental', 'Places or situations associated with past use', 1),
	(5, 'Physical', 'Physical discomfort or pain', 1);

-- Dumping structure for table new_path.urge_logs
CREATE TABLE IF NOT EXISTS `urge_logs` (
  `urge_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `logged_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `intensity` int DEFAULT NULL,
  `trigger_category` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `trigger_description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `coping_strategy_used` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `outcome` enum('resisted','relapsed','in_progress') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'resisted',
  `location` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`urge_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_logged_at` (`logged_at`),
  CONSTRAINT `fk_urge_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path.urge_logs: ~15 rows (approximately)
INSERT INTO `urge_logs` (`urge_id`, `user_id`, `logged_at`, `intensity`, `trigger_category`, `trigger_description`, `coping_strategy_used`, `outcome`, `location`, `notes`) VALUES
	(1, 2, '2026-01-06 15:01:17', 3, 'Stress', NULL, NULL, 'resisted', NULL, NULL),
	(2, 2, '2026-01-08 15:01:17', 2, 'Social', NULL, NULL, 'resisted', NULL, NULL),
	(3, 2, '2026-01-10 15:01:17', 1, 'Emotional', NULL, NULL, 'resisted', NULL, NULL),
	(4, 2, '2025-12-17 15:31:02', 3, 'Stress', NULL, NULL, 'resisted', NULL, NULL),
	(5, 2, '2025-12-22 15:31:02', 4, 'Social', NULL, NULL, 'resisted', NULL, NULL),
	(6, 2, '2025-12-27 15:31:02', 2, 'Stress', NULL, NULL, 'resisted', NULL, NULL),
	(7, 2, '2026-01-01 15:31:02', 3, 'Emotional', NULL, NULL, 'resisted', NULL, NULL),
	(8, 2, '2026-01-06 15:31:02', 2, 'Boredom', NULL, NULL, 'resisted', NULL, NULL),
	(9, 2, '2026-01-09 15:31:02', 1, 'Stress', NULL, NULL, 'resisted', NULL, NULL),
	(10, 2, '2025-12-17 15:31:03', 3, 'Stress', NULL, NULL, 'resisted', NULL, NULL),
	(11, 2, '2025-12-22 15:31:03', 4, 'Social', NULL, NULL, 'resisted', NULL, NULL),
	(12, 2, '2025-12-27 15:31:03', 2, 'Stress', NULL, NULL, 'resisted', NULL, NULL),
	(13, 2, '2026-01-01 15:31:03', 3, 'Emotional', NULL, NULL, 'resisted', NULL, NULL),
	(14, 2, '2026-01-06 15:31:03', 2, 'Boredom', NULL, NULL, 'resisted', NULL, NULL),
	(15, 2, '2026-01-09 15:31:03', 1, 'Stress', NULL, NULL, 'resisted', NULL, NULL),
	(16, 13, '2026-04-06 16:08:20', 7, 'Boredom', NULL, 'asref', 'relapsed', NULL, 'astgec'),
	(17, 2, '2026-04-13 18:47:44', 5, 'Emotional', NULL, '', 'in_progress', NULL, ''),
	(18, 2, '2026-04-16 08:21:52', 1, 'Social', NULL, '', 'resisted', NULL, '');

-- Dumping structure for table new_path.users
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `salt` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('user','counselor','admin') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `first_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `display_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `profile_picture` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone_number` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `age` int DEFAULT NULL,
  `gender` enum('male','female','other','prefer_not_to_say') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_email_verified` tinyint(1) DEFAULT '0',
  `email_verification_token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password_reset_token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password_reset_expires` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `last_login` timestamp NULL DEFAULT NULL,
  `onboarding_completed` tinyint(1) DEFAULT '0',
  `current_onboarding_step` int DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `bio` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `idx_email` (`email`),
  UNIQUE KEY `idx_username` (`username`),
  KEY `idx_role` (`role`),
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path.users: ~17 rows (approximately)
INSERT INTO `users` (`user_id`, `email`, `username`, `password_hash`, `salt`, `role`, `first_name`, `last_name`, `display_name`, `profile_picture`, `phone_number`, `age`, `gender`, `is_email_verified`, `email_verification_token`, `password_reset_token`, `password_reset_expires`, `is_active`, `last_login`, `onboarding_completed`, `current_onboarding_step`, `created_at`, `updated_at`, `bio`) VALUES
	(1, 'admin@newpath.com', 'admin', '$2a$10$m3uVRJ8S7NVswiFpooQowuqLGMjeNlPGssXScEPVyqQ8LrM7oQXMe', '$2a$10$92IXUNpkjO0rOQ5byMi.Ye', 'admin', 'System', 'Administrator', 'System Admin', NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, 1, NULL, 0, 1, '2026-01-02 18:00:38', '2026-01-19 08:49:41', NULL),
	(2, 'pasidurajapaksha202@gmail.com', 'pasidu', '$2y$10$.5UJeA9Q/fgAP/.U1ln.B.Meq48YLNDW8lFmBhlENp19Lz2mOCGQ.', '$2a$10$m3uVRJ8S7NVswiFpooQowu', 'user', 'Pasidu', 'Rajapaksha', 'Anonymous User', '/uploads/profiles/profile_2_1775073818.jpg', '0773623777', 17, 'male', 0, NULL, '56d6241af28c78c428a34b521abd59c9c96ccc79e67fba6d4d9bb95243c845a6', '2026-04-14 17:21:47', 1, NULL, 1, 5, '2026-01-04 17:49:51', '2026-04-14 16:21:47', NULL),
	(5, 'Puski200322@gmail.com', 'pasidu_rajapaksha', '$2a$10$m3uVRJ8S7NVswiFpooQowuqLGMjeNlPGssXScEPVyqQ8LrM7oQXMe', '$2a$10$gTLAJVOOJZlDqV9gOYIXve', 'counselor', NULL, NULL, 'Pasidu Rajapaksha', '/uploads/profiles/profile_5_1775154819.png', '0773623777', NULL, NULL, 0, NULL, NULL, NULL, 1, NULL, 0, 1, '2026-01-04 18:10:13', '2026-04-02 18:33:39', NULL),
	(7, 'heloo2@gmail.com', NULL, '$2a$10$XPUcmf1g3EPDOaRoSUrxnO0y/W8nVEe0NAP/17tjo2VJM1joIVhe.', '$2a$10$XPUcmf1g3EPDOaRoSUrxnO', 'user', NULL, NULL, 'hello', NULL, NULL, 23, 'male', 0, NULL, NULL, NULL, 1, NULL, 0, 1, '2026-02-23 07:44:55', '2026-02-23 07:44:55', NULL),
	(8, 'Pasidurajapak66sha202@gmail.com', NULL, '$2y$10$elf8coZ0yGvOVNwSxOA17u0C6mf4OPGdVotJ31hCSSJF/AKLtVmH6', '$2y$10$0cRWY8gsxNuTleg8U9dsd.tUIcjO6Om.FAwI1Nz73s87RjXqjOqti', 'user', NULL, NULL, 'pasidu', NULL, NULL, 54, 'female', 0, NULL, NULL, NULL, 1, NULL, 0, 1, '2026-02-25 19:43:23', '2026-02-25 19:43:23', NULL),
	(9, 'Pasidurffajapaksha202@gmail.com', NULL, '$2y$10$6wgsDcVkfscaNPSk8DElPe5G274TLcgIV1oC48D9QlbNDhMsLOhby', '$2y$10$c9G7uRxpDy3ooHSMkgkNz.vgGmj3UuoZC/z.IBi0G7.XVQTtG/vyC', 'user', NULL, NULL, 'pasidu', NULL, NULL, 23, 'male', 0, NULL, NULL, NULL, 1, NULL, 0, 1, '2026-02-25 19:44:54', '2026-02-25 19:44:54', NULL),
	(10, 'pasidurajapaffksha202@gmail.com', NULL, '$2y$10$g7HD6nA/GuAfeXj7BTlNx.xZYPNFp89Amg5zkguzkPf0AUAdNZx32', '$2y$10$KxcNpQdI7YXc.69hjcS2tOnFMKhzWJIJboCpuNjLMyqAziHu/JEAC', 'user', NULL, NULL, 'hello', NULL, NULL, 23, 'male', 0, NULL, NULL, NULL, 1, NULL, 1, 5, '2026-02-25 19:50:38', '2026-02-25 19:53:11', NULL),
	(11, 'puka@gmail.com', NULL, '$2y$10$/Rkg.0DKIEjfVGzqRvZeKeSvOKeXXIrwQxHEdXqkaDbMBnh26WkQO', '$2y$10$vecMz4.bs/ia/zNX0ANTG.mQ2DP0APjvI89ZB3.vpQHqihFxhPm5C', 'user', 'abc', 'user', 'ABC user', NULL, '', 45, 'male', 0, NULL, NULL, NULL, 1, NULL, 1, 5, '2026-04-01 20:14:41', '2026-04-09 15:27:59', NULL),
	(12, 'machan@gmail.com', NULL, '$2y$10$tPfU40YficVmZ/AJ7Esudeu.uQVfq/gqjjHQ4Px0ZGoUrAmc0PNm.', '$2y$10$z8xDvIW0sdHYTsuqDcLJfO9r6qmh5baw/iOztH4Lj5mznelVsa6MK', 'user', 'Pasidu', 'Rajapaksha', 'machan', NULL, '0714710856', 23, 'male', 0, NULL, NULL, NULL, 1, NULL, 1, 5, '2026-04-02 19:47:10', '2026-04-02 19:55:52', NULL),
	(13, 'tharushi.dilara2003@gmail.com', NULL, '$2y$10$AHYxoGq0bTvSqR4F7p5vBusde0Wq/l4gb6vkGeFGQ02ry6cBBYcU.', '$2y$10$s7vD/Jga4ufvU2SpR/Mg8eQmUFwjZy4z5U29T8J/bYlaIgotnRgHe', 'user', NULL, NULL, 'tharu jayasooriya', NULL, NULL, 38, 'male', 0, NULL, '09e87d8999211800379f8f7d3c96b506018bf7ef206c1e7c9c9211aa58b7824c', '2026-04-10 06:53:24', 1, NULL, 1, 5, '2026-04-06 08:22:59', '2026-04-10 05:53:24', NULL),
	(14, 'pasiddfdfurajapaksha202@gmail.com', NULL, '$2y$10$xtLaC40F9GJM9jrmpbpNdeCh19mK9i7lIal8R/2e6UMO32QwDwndu', '$2y$10$58QdSBtBdf1r/m0u9Kln2e94j9fcRV9.dER/EiNDKT40Xu3Wb2jP2', 'admin', '', '', 'Chethaka', NULL, '', 34, 'male', 0, NULL, NULL, NULL, 1, NULL, 0, 2, '2026-04-06 20:47:30', '2026-04-09 15:45:10', NULL),
	(15, 'misath22@gmail.com', NULL, '$2y$10$qMUCpn7dMzbbcvOAXos8s.ZX2orGZZWOZ4tahibpj.6wO2.qG3eye', '$2y$10$Roerv/pd7wwImoJyt4OmruUeWjiPlycEwBJBSPsrkr.rjk4L2pxou', 'user', 'misath', 'mandira', 'misath', NULL, '', 21, 'male', 0, NULL, NULL, NULL, 1, NULL, 1, 5, '2026-04-11 10:57:37', '2026-04-11 11:00:39', NULL),
	(16, 'Swairisg29@gmail.com', NULL, '$2y$10$zyJ6.Q7ejTfsEXo2lOFuhuFako1VeeIQ/BWLG5f2A3eIwyGYDVzbm', '$2y$10$GdYY14lI39Gvrhnn7WYP2uBuZZK.DB9w2o2imOvsXUCXdePcwQbOa', 'user', NULL, NULL, 'swairi', NULL, NULL, 23, 'female', 0, NULL, NULL, NULL, 1, NULL, 1, 5, '2026-04-13 01:15:42', '2026-04-13 01:17:30', NULL),
	(17, 'nethmiswairi14@gmail.com', NULL, '$2y$10$0NvypPpSlasAErzIa4xQQed./pkZVWzZpkPdPsnP6fpMs7oz7v7lC', '$2y$10$mik1qWbBcif70Ps8OKBBU.CcSDL9njqdvqiVARhZTAznTZB0IKYPG', 'user', NULL, NULL, 'swairi', NULL, NULL, 20, 'female', 0, NULL, NULL, NULL, 1, NULL, 1, 5, '2026-04-13 01:19:34', '2026-04-13 14:39:23', NULL),
	(18, 'nethmiswairi90@gmail.com', NULL, '$2y$10$If4XQsWFVMBqcVKwAquYqeyLadaWu3b5lM2RIli8keupRMLwKnVci', '$2y$10$/561Ng5kAQ5SSOi6UhHgS.TeLbwJ2o3acKLChA.4LxASe7f23mX32', 'user', NULL, NULL, 'Maya Perera', NULL, NULL, 20, 'female', 0, NULL, NULL, NULL, 1, NULL, 1, 5, '2026-04-13 12:03:59', '2026-04-13 12:04:57', NULL),
	(19, 'ponna@gmail.com', NULL, '$2y$10$zzGTR5sJ.ED8DvQhHZxIheuJpUDMRRLXsmokwxLh.xxv410xJODtK', '$2y$10$WobLbifSepQ4apd.F2LiX.8WtJk3RhFO0xFMiRTOWXG2NMeAuO4ga', 'user', NULL, NULL, 'ponnaya', NULL, NULL, 23, 'male', 0, NULL, NULL, NULL, 1, NULL, 1, 5, '2026-04-13 17:06:07', '2026-04-13 17:14:03', NULL),
	(20, 'pasindu@gmail.com', NULL, '$2y$10$mTj4I46Fq2GM2t6MCp5xA.Q3Me1IxhJDn.OVK.SrDpO8DfsovjoqG', '$2y$10$5alFIi6RZ0kK4qp2Ba2tBe8laQA35a6eQFxUVGiSoenWadpo8gHhC', 'user', NULL, NULL, 'Pasidu Rajapaksha', NULL, NULL, 20, 'male', 0, NULL, NULL, NULL, 1, NULL, 1, 5, '2026-04-13 17:06:15', '2026-04-13 17:13:01', NULL),
	(21, 'rajapakshagpt@gmail.com', 'pasidu_rajapaksha_2', '$2y$10$iztoujFl0GG5nQ1sMSSPbeP0lv4biu55k93LlH5r3YieUdzXV65yC', '', 'counselor', NULL, NULL, 'Pasidu Rajapaksha', NULL, '0714710856', NULL, NULL, 0, NULL, NULL, NULL, 1, NULL, 0, 1, '2026-04-14 16:33:51', '2026-04-14 16:33:51', NULL),
	(22, 'sanath@gmail.com', 'sanath', '$2y$10$yrzg47kBARL.4g/afxlX2exwxkyQlHDiOPC53MsRnGmyo6yoLoqXO', '', 'counselor', NULL, NULL, 'Sanath', NULL, '0773738839', NULL, NULL, 0, NULL, NULL, NULL, 1, NULL, 0, 1, '2026-04-15 07:30:34', '2026-04-15 07:30:34', NULL),
	(23, 'samantha@gmail.com', 'samantha', '$2y$10$dhJTaWu0ZVtbt8bcmmTu5uiinsh6ixv.EiuWwcymWVwO4eA1RuWc6', '', 'counselor', NULL, NULL, 'samantha', NULL, '0774536633', NULL, NULL, 0, NULL, NULL, NULL, 1, NULL, 0, 1, '2026-04-15 08:28:36', '2026-04-15 08:28:36', NULL);

-- Dumping structure for table new_path.user_achievements
CREATE TABLE IF NOT EXISTS `user_achievements` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `achievement_key` varchar(50) NOT NULL,
  `awarded_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_user_achievement` (`user_id`,`achievement_key`),
  KEY `idx_user` (`user_id`),
  CONSTRAINT `user_achievements_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table new_path.user_achievements: ~2 rows (approximately)
INSERT INTO `user_achievements` (`id`, `user_id`, `achievement_key`, `awarded_at`) VALUES
	(1, 2, 'first_checkin', '2026-04-13 11:12:42'),
	(2, 2, 'first_journal', '2026-04-13 11:12:42'),
	(3, 2, 'plan_completed', '2026-04-13 11:12:42'),
	(4, 17, 'first_checkin', '2026-04-13 14:39:49'),
	(5, 2, 'sober_1d', '2026-04-14 03:36:47');

-- Dumping structure for table new_path.user_connections
CREATE TABLE IF NOT EXISTS `user_connections` (
  `connection_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `connected_user_id` int NOT NULL,
  `status` enum('pending','accepted','declined','blocked') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`connection_id`),
  UNIQUE KEY `idx_connection_pair` (`user_id`,`connected_user_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_connected_user` (`connected_user_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_conn_connected_user` FOREIGN KEY (`connected_user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_conn_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path.user_connections: ~4 rows (approximately)
INSERT INTO `user_connections` (`connection_id`, `user_id`, `connected_user_id`, `status`, `created_at`, `updated_at`) VALUES
	(10, 2, 12, 'accepted', '2026-04-02 19:58:00', '2026-04-02 19:58:00'),
	(16, 13, 2, 'accepted', '2026-04-11 10:54:03', '2026-04-11 10:54:03'),
	(18, 15, 2, 'accepted', '2026-04-11 11:11:19', '2026-04-11 11:11:19'),
	(19, 15, 13, 'blocked', '2026-04-11 11:11:31', '2026-04-11 11:11:59'),
	(20, 2, 7, 'accepted', '2026-04-16 06:03:22', '2026-04-16 06:03:22');

-- Dumping structure for table new_path.user_profiles
CREATE TABLE IF NOT EXISTS `user_profiles` (
  `profile_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `emergency_contact_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emergency_contact_phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sobriety_start_date` date DEFAULT NULL,
  `last_relapse_date` date DEFAULT NULL,
  `recovery_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notification_preferences` json DEFAULT NULL,
  `privacy_settings` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `substance_frequency` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_used_timeframe` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quit_attempts` int DEFAULT NULL,
  `motivation_level` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `risk_score` int DEFAULT NULL,
  `bio` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_anonymous` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`profile_id`),
  UNIQUE KEY `idx_user` (`user_id`),
  CONSTRAINT `fk_profile_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path.user_profiles: ~12 rows (approximately)
INSERT INTO `user_profiles` (`profile_id`, `user_id`, `emergency_contact_name`, `emergency_contact_phone`, `sobriety_start_date`, `last_relapse_date`, `recovery_type`, `notification_preferences`, `privacy_settings`, `created_at`, `updated_at`, `substance_frequency`, `last_used_timeframe`, `quit_attempts`, `motivation_level`, `risk_score`, `bio`, `is_anonymous`) VALUES
	(1, 2, '', '', '2026-04-13', NULL, 'substance', NULL, NULL, '2026-01-11 15:01:17', '2026-04-13 07:21:03', NULL, NULL, NULL, NULL, NULL, '', 0),
	(11, 7, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-23 07:44:55', '2026-04-03 08:54:38', NULL, NULL, NULL, NULL, NULL, NULL, 0),
	(12, 10, NULL, NULL, NULL, NULL, 'None', NULL, NULL, '2026-02-25 19:52:21', '2026-02-25 19:52:21', NULL, NULL, NULL, NULL, NULL, NULL, 1),
	(16, 11, NULL, NULL, NULL, NULL, 'None', NULL, NULL, '2026-04-01 20:14:47', '2026-04-01 20:14:47', 'None', 'Never', 0, 'exploring', 5, NULL, 1),
	(17, 12, '', '', NULL, NULL, '', NULL, NULL, '2026-04-02 19:47:12', '2026-04-02 19:55:52', 'None', 'Never', 0, 'motivated', 6, NULL, 1),
	(18, 13, NULL, NULL, '2026-04-06', NULL, 'None', NULL, NULL, '2026-04-06 08:23:05', '2026-04-06 08:27:42', 'None', 'Never', 0, 'motivated', 6, NULL, 1),
	(19, 15, '', '', NULL, NULL, '', NULL, NULL, '2026-04-11 10:58:08', '2026-04-11 11:00:40', 'Weekly', 'Past week', 9, 'motivated', 13, NULL, 1),
	(20, 16, NULL, NULL, '2026-04-13', NULL, 'None', NULL, NULL, '2026-04-13 01:15:58', '2026-04-13 07:15:04', 'None', 'Never', 0, 'motivated', 6, NULL, 1),
	(22, 17, NULL, NULL, '2026-04-13', NULL, 'None', NULL, NULL, '2026-04-13 01:27:18', '2026-04-13 15:07:06', 'None', 'Never', 0, 'motivated', 0, NULL, 1),
	(23, 18, NULL, NULL, NULL, NULL, 'None', NULL, NULL, '2026-04-13 12:04:07', '2026-04-13 12:04:07', 'None', 'Never', 0, 'desperate', 7, NULL, 1),
	(24, 19, NULL, NULL, NULL, NULL, 'None', NULL, NULL, '2026-04-13 17:08:53', '2026-04-13 17:09:15', 'None', 'Never', 0, 'exploring', 16, NULL, 1),
	(25, 20, NULL, NULL, NULL, NULL, 'Alcohol', NULL, NULL, '2026-04-13 17:11:16', '2026-04-13 17:12:52', 'Weekly', 'Past week', 4, 'motivated', 14, NULL, 1);

-- Dumping structure for table new_path.user_progress
CREATE TABLE IF NOT EXISTS `user_progress` (
  `progress_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `date` date NOT NULL,
  `days_sober` int DEFAULT '0',
  `is_sober_today` tinyint(1) DEFAULT '1',
  `milestone_progress` int DEFAULT '0',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`progress_id`),
  UNIQUE KEY `idx_user_date` (`user_id`,`date`),
  CONSTRAINT `fk_progress_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table new_path.user_progress: ~9 rows (approximately)
INSERT INTO `user_progress` (`progress_id`, `user_id`, `date`, `days_sober`, `is_sober_today`, `milestone_progress`, `notes`, `created_at`, `updated_at`) VALUES
	(1, 2, '2026-02-28', 0, 1, 0, 'Started sobriety tracking', '2026-02-28 10:55:04', '2026-02-28 10:55:18'),
	(5, 2, '2026-03-31', 0, 1, 0, 'Started sobriety tracking', '2026-03-30 19:42:44', '2026-03-30 19:43:29'),
	(7, 2, '2026-04-05', 0, 1, 0, 'Started sobriety tracking', '2026-04-04 20:25:01', '2026-04-04 20:25:09'),
	(9, 13, '2026-04-06', 0, 1, 0, 'Started sobriety tracking', '2026-04-06 08:27:42', '2026-04-06 08:27:42'),
	(10, 2, '2026-04-06', 0, 1, 0, 'Started sobriety tracking', '2026-04-06 09:19:16', '2026-04-06 16:48:27'),
	(11, 2, '2026-04-07', 0, 1, 0, 'Started sobriety tracking', '2026-04-07 14:03:09', '2026-04-07 14:03:09'),
	(12, 2, '2026-04-10', 0, 1, 0, 'Started sobriety tracking', '2026-04-10 18:28:08', '2026-04-10 18:30:49'),
	(17, 2, '2026-04-13', 0, 1, 0, 'Started sobriety tracking', '2026-04-13 06:00:11', '2026-04-13 11:12:50'),
	(18, 16, '2026-04-13', 0, 1, 0, 'Started sobriety tracking', '2026-04-13 07:15:05', '2026-04-13 07:15:05'),
	(28, 17, '2026-04-13', 0, 1, 0, 'Started sobriety tracking', '2026-04-13 15:07:06', '2026-04-13 15:07:06');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
