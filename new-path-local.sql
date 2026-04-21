-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.0.45 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.17.0.7270
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
CREATE DATABASE IF NOT EXISTS `new_path`  /*!80016 DEFAULT ENCRYPTION='N' */;
USE `new_path`;

-- Dumping structure for table new_path.achievements
CREATE TABLE IF NOT EXISTS `achievements` (
  `achievement_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `achievement_type` varchar(100) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text,
  `badge_icon` varchar(255) DEFAULT NULL,
  `days_required` int DEFAULT NULL,
  `earned_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`achievement_id`),
  KEY `idx_user` (`user_id`),
  CONSTRAINT `fk_achievement_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) AUTO_INCREMENT=3;

-- Dumping data for table new_path.achievements: ~0 rows (approximately)

-- Dumping structure for table new_path.addiction_type_module
CREATE TABLE IF NOT EXISTS `addiction_type_module` (
  `id` tinyint unsigned NOT NULL AUTO_INCREMENT,
  `module_key` varchar(50) NOT NULL,
  `display_name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `module_key` (`module_key`)
) AUTO_INCREMENT=7;

-- Dumping data for table new_path.addiction_type_module: ~0 rows (approximately)
INSERT INTO `addiction_type_module` (`id`, `module_key`, `display_name`) VALUES
	(1, 'MOD_GAMING', 'Gaming'),
	(2, 'MOD_SOCIAL', 'Social Media'),
	(3, 'MOD_PORN', 'Adult Content'),
	(4, 'MOD_SHOPPING', 'Shopping'),
	(5, 'MOD_GAMBLING', 'Gambling'),
	(6, 'MOD_STREAMING', 'Streaming/Entertainment');

-- Dumping structure for table new_path.admin
CREATE TABLE IF NOT EXISTS `admin` (
  `admin_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `permissions` json DEFAULT NULL,
  `is_super_admin` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`admin_id`),
  UNIQUE KEY `idx_user` (`user_id`),
  CONSTRAINT `fk_admin_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) AUTO_INCREMENT=3;

-- Dumping data for table new_path.admin: ~0 rows (approximately)

-- Dumping structure for table new_path.audit_logs
CREATE TABLE IF NOT EXISTS `audit_logs` (
  `log_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `entity_type` varchar(50) DEFAULT NULL,
  `entity_id` int DEFAULT NULL,
  `old_values` json DEFAULT NULL,
  `new_values` json DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_action` (`action`),
  KEY `idx_entity` (`entity_type`,`entity_id`),
  KEY `idx_created` (`created_at`)
);

-- Dumping data for table new_path.audit_logs: ~0 rows (approximately)

-- Dumping structure for table new_path.booking_holds
CREATE TABLE IF NOT EXISTS `booking_holds` (
  `hold_id` int NOT NULL AUTO_INCREMENT,
  `counselor_id` int NOT NULL,
  `user_id` int NOT NULL,
  `slot_datetime` datetime NOT NULL,
  `duration_minutes` int DEFAULT '60',
  `status` enum('held','confirmed','released') DEFAULT 'held',
  `held_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` timestamp NOT NULL,
  PRIMARY KEY (`hold_id`),
  KEY `idx_counselor_slot` (`counselor_id`,`slot_datetime`),
  KEY `idx_status` (`status`),
  KEY `fk_hold_user` (`user_id`),
  CONSTRAINT `fk_hold_counselor` FOREIGN KEY (`counselor_id`) REFERENCES `counselors` (`counselor_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_hold_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) AUTO_INCREMENT=90;

-- Dumping data for table new_path.booking_holds: ~0 rows (approximately)
INSERT INTO `booking_holds` (`hold_id`, `counselor_id`, `user_id`, `slot_datetime`, `duration_minutes`, `status`, `held_at`, `expires_at`) VALUES
	(88, 11, 40, '2026-04-27 09:00:00', 60, 'confirmed', '2026-04-20 21:56:15', '2026-04-20 22:11:15'),
	(89, 11, 40, '2026-04-27 11:00:00', 60, 'confirmed', '2026-04-20 22:05:30', '2026-04-20 22:20:30');

-- Dumping structure for table new_path.community_posts
CREATE TABLE IF NOT EXISTS `community_posts` (
  `post_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `content` text NOT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `post_type` enum('general','success_story','question','support_request','resource') DEFAULT 'general',
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
) AUTO_INCREMENT=13;

-- Dumping data for table new_path.community_posts: ~0 rows (approximately)
INSERT INTO `community_posts` (`post_id`, `user_id`, `title`, `content`, `image_url`, `post_type`, `is_anonymous`, `is_pinned`, `is_active`, `likes_count`, `comments_count`, `shares_count`, `views_count`, `created_at`, `updated_at`) VALUES
	(10, 40, 'The "Self-Care" myth we need to stop believing', 'When we hear "self-care," we often think of luxury—spa days, expensive vacations, or completely unplugging for weeks. But for most of us, that isn\'t realistic. Real self-care is often boring, unglamorous, and absolutely necessary. It’s setting a boundary and saying "no" to an extra commitment. It’s going to bed 30 minutes earlier. It’s drinking a glass of water before your coffee. It’s drinking that cup of coffee without checking your email at the same time. Don\'t wait for a vacation to rescue your mental health. Find the tiny, repetitive, boring acts of care that keep you going today. What is one small thing you’re doing for yourself this week?', '/uploads/posts/f9a8ebfcd3b40fcf8e64e145910c47a6.jpeg', 'general', 0, 0, 1, 0, 0, 0, 0, '2026-04-20 22:08:49', '2026-04-20 22:08:49'),
	(11, 45, 'Normalize the "I\'m not okay" at work', 'We have become experts at "masking" in the workplace. We put on a brave face, hit our KPIs, and answer "Fine, and you?" to every greeting, even when we are struggling with anxiety, burnout, or personal grief. As leaders and colleagues, we need to create environments where it is psychologically safe to be human. Productivity should not come at the cost of our sanity. If you are struggling today, know that your value is not defined solely by your output. To my fellow professionals: check on your people. A simple, genuine "How are you actually doing?" can make all the difference.', '/uploads/posts/95c3052474b5beaa4e27dd52b9fc3bbc.jpeg', 'support_request', 0, 0, 1, 0, 0, 0, 0, '2026-04-20 22:12:35', '2026-04-20 22:12:35'),
	(12, 46, 'You can\'t connect if you are always trying to "correct."', 'Whether it’s with our partners or our children, there is often a strong urge to jump straight into "fix-it" mode when they are upset. When someone we love is venting, we tend to offer solutions, point out where they might be wrong, or try to "correct" their perspective. While well-intentioned, this often feels dismissive to the person who is hurting.\r\nThe next time a loved one opens up, try prioritizing connection over correction. Before you offer advice, offer validated ears. Try saying: "That sounds incredibly frustrating," or "I can see why you feel that way." Healing happens when people feel heard, not when they are told what to do.', '/uploads/posts/3be10a8b5a3bda78783770fe5e6cc632.jpeg', 'general', 0, 0, 1, 0, 0, 0, 0, '2026-04-20 22:15:36', '2026-04-20 22:15:36');

-- Dumping structure for table new_path.counselor_applications
CREATE TABLE IF NOT EXISTS `counselor_applications` (
  `application_id` int NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL,
  `specialty` varchar(255) NOT NULL,
  `bio` text NOT NULL,
  `experience_years` int DEFAULT NULL,
  `education` text NOT NULL,
  `certifications` text,
  `languages_spoken` varchar(255) DEFAULT NULL,
  `consultation_fee` decimal(10,2) DEFAULT NULL,
  `availability_schedule` text,
  `documents_url` varchar(500) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `admin_notes` text,
  `reviewed_by` int DEFAULT NULL,
  `review_date` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`application_id`),
  UNIQUE KEY `idx_email` (`email`),
  KEY `idx_status` (`status`),
  KEY `fk_app_admin` (`reviewed_by`),
  CONSTRAINT `fk_app_admin` FOREIGN KEY (`reviewed_by`) REFERENCES `admin` (`admin_id`) ON DELETE SET NULL
) AUTO_INCREMENT=24;

-- Dumping data for table new_path.counselor_applications: ~0 rows (approximately)
INSERT INTO `counselor_applications` (`application_id`, `full_name`, `email`, `phone_number`, `title`, `specialty`, `bio`, `experience_years`, `education`, `certifications`, `languages_spoken`, `consultation_fee`, `availability_schedule`, `documents_url`, `status`, `admin_notes`, `reviewed_by`, `review_date`, `created_at`, `updated_at`) VALUES
	(21, 'Dr. Eleanor Vance', 'e.vance.therapy@example.com', '+94773455355', 'Licensed Clinical Social Worker (LCSW)', 'Addiction Counseling', 'Dr. Eleanor Vance is a dedicated Licensed Clinical Social Worker with over 12 years of experience helping individuals navigate the complexities of trauma and substance use disorders. She utilizes an integrative approach, combining Cognitive Behavioral Therapy (CBT) with trauma-informed care to create a safe and supportive environment for healing. Eleanor believes in a client-centered perspective, empowering individuals to build resilience and develop healthy coping mechanisms for long-term recovery.', 3, 'Ph.D. in Clinical Psychology, Pacific University (2015)\r\n\r\nMaster of Social Work (MSW), University of California, Berkeley (2011)\r\n\r\nB.A. in Psychology, Stanford University (2009)', 'Licensed Clinical Social Worker (LCSW), State of California, License #LCSW99000\r\n\r\nCertified Clinical Trauma Professional (CCTP)\r\n\r\nCertified Alcohol and Drug Counselor (CADC-II)', 'English', 3000.00, '{"monday":[{"start":"09:00","end":"17:00"}]}', '/uploads/applications/949ac6be8b159114e97b4e699800de9f.pdf', 'approved', 'Application approved and counselor account created', NULL, '2026-04-20 21:38:00', '2026-04-20 21:36:13', '2026-04-20 21:38:00'),
	(22, 'Marcus Thompson', 'm.thompson.lmft@emailprovider.co', '+61714710856', 'Licensed Marriage and Family Therapist (LMFT)', 'Family Therapy', 'Marcus Thompson is a compassionate Licensed Marriage and Family Therapist who believes that individual healing often begins within the family unit. With nearly a decade of experience, he specializes in working with adolescents facing emotional and behavioral challenges, as well as helping families navigate high-conflict transitions, such as divorce or loss. Marcus utilizes a systems theory approach, integrating CBT and group dynamic tools to foster healthier communication, establish firm boundaries, and build lasting resilience for the whole family.', 9, 'M.A. in Marriage and Family Therapy, Syracuse University (2017)\r\n\r\nB.A. in Sociology, State University of New York (SUNY) at Albany (2015)', 'Licensed Marriage and Family Therapist (LMFT), State of New York, License #NY100456\r\n\r\nCertified Child & Adolescent Anxiety Treatment Professional (CCATP)', 'English, French', 4000.00, '{"tuesday":[{"start":"09:00","end":"17:00"}]}', '', 'approved', 'Application approved and counselor account created', NULL, '2026-04-20 21:47:16', '2026-04-20 21:46:35', '2026-04-20 21:47:16'),
	(23, 'Yohan', 'puski200322@gmail.com', '+617147108578', '', 'Family Therapy', 'I am a compassionate Licensed Marriage and Family Therapist who believes that individual healing often begins within the family unit. With nearly a decade of experience, he specializes in working with adolescents facing emotional and behavioral challenges, as well as helping families navigate high-conflict transitions, such as divorce or loss. Marcus utilizes a systems theory approach, integrating CBT and group dynamic tools to foster healthier communication, establish firm boundaries, and build lasting resilience for the whole family.', 32, 'M.A. in Marriage and Family Therapy, Syracuse University (2017)\r\n\r\nB.A. in Sociology, State University of New York (SUNY) at Albany (2015)', '', 'English', NULL, '{"monday":[{"start":"09:00","end":"17:00"}]}', '/uploads/applications/f78c9a3aea37a203e28603a09f007ccc.pdf', 'approved', 'Application approved and counselor account created', NULL, '2026-04-20 21:53:06', '2026-04-20 21:52:06', '2026-04-20 21:53:06');

-- Dumping structure for table new_path.counselor_payouts
CREATE TABLE IF NOT EXISTS `counselor_payouts` (
  `payout_id` int NOT NULL AUTO_INCREMENT,
  `counselor_id` int NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) NOT NULL DEFAULT 'LKR',
  `period_start` date NOT NULL,
  `period_end` date NOT NULL,
  `sessions_count` int DEFAULT '0',
  `status` enum('pending','processing','completed','failed') DEFAULT 'pending',
  `stripe_payout_id` varchar(255) DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `payhere_reference` varchar(255) DEFAULT NULL,
  `platform_commission` decimal(10,2) DEFAULT '0.00',
  `commission_rate` decimal(5,2) DEFAULT '0.00',
  PRIMARY KEY (`payout_id`),
  KEY `idx_counselor` (`counselor_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_payout_counselor` FOREIGN KEY (`counselor_id`) REFERENCES `counselors` (`counselor_id`) ON DELETE CASCADE
) AUTO_INCREMENT=3;

-- Dumping data for table new_path.counselor_payouts: ~0 rows (approximately)

-- Dumping structure for table new_path.counselors
CREATE TABLE IF NOT EXISTS `counselors` (
  `counselor_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `title` varchar(100) DEFAULT NULL,
  `specialty` varchar(255) DEFAULT NULL,
  `specialty_short` varchar(50) DEFAULT NULL,
  `bio` text,
  `experience_years` int DEFAULT '0',
  `education` text,
  `certifications` text,
  `languages_spoken` varchar(255) DEFAULT NULL,
  `consultation_fee` decimal(10,2) DEFAULT NULL,
  `availability_schedule` json DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT '0',
  `rating` decimal(3,2) DEFAULT '0.00',
  `total_reviews` int DEFAULT '0',
  `total_clients` int DEFAULT '0',
  `total_sessions` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `google_refresh_token` text,
  PRIMARY KEY (`counselor_id`),
  UNIQUE KEY `idx_user` (`user_id`),
  KEY `idx_verified` (`is_verified`),
  KEY `idx_specialty` (`specialty`),
  CONSTRAINT `fk_counselor_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) AUTO_INCREMENT=12;

-- Dumping data for table new_path.counselors: ~0 rows (approximately)
INSERT INTO `counselors` (`counselor_id`, `user_id`, `title`, `specialty`, `specialty_short`, `bio`, `experience_years`, `education`, `certifications`, `languages_spoken`, `consultation_fee`, `availability_schedule`, `is_verified`, `rating`, `total_reviews`, `total_clients`, `total_sessions`, `created_at`, `updated_at`, `google_refresh_token`) VALUES
	(9, 42, 'Licensed Clinical Social Worker (LCSW)', '', '', 'Dr. Eleanor Vance is a dedicated Licensed Clinical Social Worker with over 12 years of experience helping individuals navigate the complexities of trauma and substance use disorders. She utilizes an integrative approach, combining Cognitive Behavioral Therapy (CBT) with trauma-informed care to create a safe and supportive environment for healing. Eleanor believes in a client-centered perspective, empowering individuals to build resilience and develop healthy coping mechanisms for long-term recovery.', 3, 'Ph.D. in Clinical Psychology, Pacific University (2015)\r\n\r\nMaster of Social Work (MSW), University of California, Berkeley (2011)\r\n\r\nB.A. in Psychology, Stanford University (2009)', 'Licensed Clinical Social Worker (LCSW), State of California, License #LCSW99000\r\n\r\nCertified Clinical Trauma Professional (CCTP)\r\n\r\nCertified Alcohol and Drug Counselor (CADC-II)', 'English', 3000.00, '{"monday": [{"end": "17:00", "start": "09:00"}]}', 1, 0.00, 0, 0, 0, '2026-04-20 21:38:00', '2026-04-20 21:41:55', NULL),
	(10, 43, 'Licensed Marriage and Family Therapist (LMFT)', 'Family Therapy', '', 'Marcus Thompson is a compassionate Licensed Marriage and Family Therapist who believes that individual healing often begins within the family unit. With nearly a decade of experience, he specializes in working with adolescents facing emotional and behavioral challenges, as well as helping families navigate high-conflict transitions, such as divorce or loss. Marcus utilizes a systems theory approach, integrating CBT and group dynamic tools to foster healthier communication, establish firm boundaries, and build lasting resilience for the whole family.', 9, 'M.A. in Marriage and Family Therapy, Syracuse University (2017)\r\n\r\nB.A. in Sociology, State University of New York (SUNY) at Albany (2015)', 'Licensed Marriage and Family Therapist (LMFT), State of New York, License #NY100456\r\n\r\nCertified Child & Adolescent Anxiety Treatment Professional (CCATP)', 'English, French', 4000.00, '{"tuesday": [{"end": "17:00", "start": "09:00"}]}', 1, 0.00, 0, 0, 0, '2026-04-20 21:47:16', '2026-04-20 21:47:16', NULL),
	(11, 44, '', 'Family Therapy', '', 'I am a compassionate Licensed Marriage and Family Therapist who believes that individual healing often begins within the family unit. With nearly a decade of experience, he specializes in working with adolescents facing emotional and behavioral challenges, as well as helping families navigate high-conflict transitions, such as divorce or loss. Marcus utilizes a systems theory approach, integrating CBT and group dynamic tools to foster healthier communication, establish firm boundaries, and build lasting resilience for the whole family.', 32, 'M.A. in Marriage and Family Therapy, Syracuse University (2017)\r\n\r\nB.A. in Sociology, State University of New York (SUNY) at Albany (2015)', '', 'English', 3500.00, '{"monday": [{"end": "17:00", "start": "09:00"}]}', 1, 5.00, 1, 0, 0, '2026-04-20 21:53:06', '2026-04-20 21:58:52', NULL);

-- Dumping structure for table new_path.daily_checkins
CREATE TABLE IF NOT EXISTS `daily_checkins` (
  `checkin_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `checkin_date` date NOT NULL,
  `is_sober` tinyint(1) DEFAULT '1',
  `mood_rating` int DEFAULT NULL,
  `mood_label` varchar(50) DEFAULT NULL,
  `energy_level` int DEFAULT NULL,
  `sleep_quality` int DEFAULT NULL,
  `stress_level` int DEFAULT NULL,
  `notes` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`checkin_id`),
  UNIQUE KEY `idx_user_date` (`user_id`,`checkin_date`),
  CONSTRAINT `fk_checkin_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) AUTO_INCREMENT=14;

-- Dumping data for table new_path.daily_checkins: ~0 rows (approximately)

-- Dumping structure for table new_path.direct_messages
CREATE TABLE IF NOT EXISTS `direct_messages` (
  `message_id` int NOT NULL AUTO_INCREMENT,
  `conversation_id` int NOT NULL,
  `sender_id` int NOT NULL,
  `content` text NOT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`message_id`),
  KEY `idx_conversation` (`conversation_id`),
  KEY `idx_sender` (`sender_id`),
  KEY `idx_created` (`created_at`),
  CONSTRAINT `fk_dm_conversation` FOREIGN KEY (`conversation_id`) REFERENCES `dm_conversations` (`conversation_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_dm_sender` FOREIGN KEY (`sender_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) AUTO_INCREMENT=21;

-- Dumping data for table new_path.direct_messages: ~0 rows (approximately)
INSERT INTO `direct_messages` (`message_id`, `conversation_id`, `sender_id`, `content`, `is_read`, `created_at`) VALUES
	(17, 22, 46, 'hi', 1, '2026-04-20 22:16:13'),
	(18, 22, 46, 'how are you', 1, '2026-04-20 22:16:22'),
	(19, 22, 46, 'hello', 1, '2026-04-20 22:20:53'),
	(20, 22, 40, 'hi', 0, '2026-04-20 22:33:29');

-- Dumping structure for table new_path.dm_conversations
CREATE TABLE IF NOT EXISTS `dm_conversations` (
  `conversation_id` int NOT NULL AUTO_INCREMENT,
  `user1_id` int NOT NULL,
  `user2_id` int NOT NULL,
  `last_message_at` timestamp NULL DEFAULT NULL,
  `last_message_preview` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`conversation_id`),
  UNIQUE KEY `idx_user_pair` (`user1_id`,`user2_id`),
  KEY `idx_user1` (`user1_id`),
  KEY `idx_user2` (`user2_id`),
  KEY `idx_last_message` (`last_message_at`),
  CONSTRAINT `fk_conv_user1` FOREIGN KEY (`user1_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_conv_user2` FOREIGN KEY (`user2_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) AUTO_INCREMENT=23;

-- Dumping data for table new_path.dm_conversations: ~0 rows (approximately)
INSERT INTO `dm_conversations` (`conversation_id`, `user1_id`, `user2_id`, `last_message_at`, `last_message_preview`, `created_at`) VALUES
	(22, 40, 46, '2026-04-20 22:33:29', 'hi', '2026-04-20 22:15:52');

-- Dumping structure for table new_path.help_centers
CREATE TABLE IF NOT EXISTS `help_centers` (
  `help_center_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `organization` varchar(255) DEFAULT NULL,
  `type` varchar(100) NOT NULL,
  `category` varchar(100) NOT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `website` varchar(500) DEFAULT NULL,
  `address` varchar(500) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `zip_code` varchar(20) DEFAULT NULL,
  `availability` varchar(255) DEFAULT NULL,
  `description` text,
  `specialties` text,
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
) AUTO_INCREMENT=3;

-- Dumping data for table new_path.help_centers: ~0 rows (approximately)

-- Dumping structure for table new_path.job_posts
CREATE TABLE IF NOT EXISTS `job_posts` (
  `job_id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `company` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `requirements` text,
  `location` varchar(255) NOT NULL,
  `job_type` enum('full_time','part_time','contract','temporary','internship') NOT NULL,
  `category` varchar(100) NOT NULL,
  `salary` decimal(10,2) DEFAULT NULL,
  `salary_range` varchar(100) DEFAULT NULL,
  `contact_email` varchar(255) DEFAULT NULL,
  `contact_phone` varchar(50) DEFAULT NULL,
  `application_url` text,
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
);

-- Dumping data for table new_path.job_posts: ~0 rows (approximately)

-- Dumping structure for table new_path.journal_categories
CREATE TABLE IF NOT EXISTS `journal_categories` (
  `category_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `is_default` tinyint(1) DEFAULT '0',
  `color` varchar(7) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`category_id`),
  KEY `idx_user` (`user_id`),
  CONSTRAINT `fk_jc_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) AUTO_INCREMENT=6;

-- Dumping data for table new_path.journal_categories: ~0 rows (approximately)

-- Dumping structure for table new_path.journal_entries
CREATE TABLE IF NOT EXISTS `journal_entries` (
  `entry_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `category_id` int DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `content` text NOT NULL,
  `is_highlight` tinyint(1) DEFAULT '0',
  `mood` varchar(50) DEFAULT NULL,
  `is_private` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`entry_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_category` (`category_id`),
  KEY `idx_created` (`created_at`),
  CONSTRAINT `fk_je_category` FOREIGN KEY (`category_id`) REFERENCES `journal_categories` (`category_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_je_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) AUTO_INCREMENT=9;

-- Dumping data for table new_path.journal_entries: ~0 rows (approximately)

-- Dumping structure for table new_path.notifications
CREATE TABLE IF NOT EXISTS `notifications` (
  `notification_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `type` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text,
  `link` varchar(500) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`notification_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_read` (`is_read`),
  KEY `idx_created` (`created_at`),
  CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) AUTO_INCREMENT=121;

-- Dumping data for table new_path.notifications: ~0 rows (approximately)
INSERT INTO `notifications` (`notification_id`, `user_id`, `type`, `title`, `message`, `link`, `is_read`, `created_at`) VALUES
	(117, 40, 'booking_confirmed', 'Session Confirmed', 'Your session with Yohan on April 27, 2026 at 9:00 AM is confirmed.', '/user/sessions', 0, '2026-04-20 21:56:47'),
	(118, 44, 'new_booking', 'New Session Booked', 'Pasidu has booked a session on April 27, 2026 at 9:00 AM.', '/counselor/sessions', 0, '2026-04-20 21:56:47'),
	(119, 40, 'booking_confirmed', 'Session Confirmed', 'Your session with Yohan on April 27, 2026 at 11:00 AM is confirmed.', '/user/sessions', 0, '2026-04-20 22:06:05'),
	(120, 44, 'new_booking', 'New Session Booked', 'Pasidu has booked a session on April 27, 2026 at 11:00 AM.', '/counselor/sessions', 0, '2026-04-20 22:06:05');

-- Dumping structure for table new_path.onboarding_evaluation
CREATE TABLE IF NOT EXISTS `onboarding_evaluation` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `addictions` varchar(500) DEFAULT NULL,
  `answers` text,
  `final_score` float DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `normalized_score` float DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `onboarding_evaluation_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
) AUTO_INCREMENT=16;

-- Dumping data for table new_path.onboarding_evaluation: ~0 rows (approximately)
INSERT INTO `onboarding_evaluation` (`id`, `user_id`, `addictions`, `answers`, `final_score`, `created_at`, `updated_at`, `normalized_score`) VALUES
	(10, 40, '[]', NULL, NULL, '2026-04-21 02:28:06', '2026-04-21 02:28:06', NULL),
	(11, 40, NULL, NULL, NULL, '2026-04-21 02:28:08', '2026-04-21 02:28:08', NULL),
	(12, 45, '[]', NULL, NULL, '2026-04-21 03:40:10', '2026-04-21 03:40:10', NULL),
	(13, 45, NULL, NULL, NULL, '2026-04-21 03:40:14', '2026-04-21 03:40:14', NULL),
	(14, 46, '[]', NULL, NULL, '2026-04-21 03:44:03', '2026-04-21 03:44:03', NULL),
	(15, 46, NULL, NULL, NULL, '2026-04-21 03:44:05', '2026-04-21 03:44:05', NULL);

-- Dumping structure for table new_path.onboarding_question_scale
CREATE TABLE IF NOT EXISTS `onboarding_question_scale` (
  `id` tinyint unsigned NOT NULL AUTO_INCREMENT,
  `scale_name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `scale_name` (`scale_name`)
) AUTO_INCREMENT=4;

-- Dumping data for table new_path.onboarding_question_scale: ~0 rows (approximately)
INSERT INTO `onboarding_question_scale` (`id`, `scale_name`) VALUES
	(1, 'FREQUENCY'),
	(2, 'INTENSITY'),
	(3, 'IMPACT');

-- Dumping structure for table new_path.onboarding_questions_step_2
CREATE TABLE IF NOT EXISTS `onboarding_questions_step_2` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `question_text` text NOT NULL,
  `weight` decimal(3,1) NOT NULL,
  `scale_id` tinyint unsigned NOT NULL,
  `path` enum('BOTH','LITE','DEEP') NOT NULL DEFAULT 'BOTH',
  `display_order` tinyint unsigned NOT NULL,
  `status` enum('ACTIVE','DISABLED') NOT NULL DEFAULT 'ACTIVE',
  PRIMARY KEY (`id`),
  KEY `idx_scale_id` (`scale_id`),
  KEY `idx_status` (`status`),
  KEY `idx_display_order` (`display_order`),
  CONSTRAINT `fk_scale_step2` FOREIGN KEY (`scale_id`) REFERENCES `onboarding_question_scale` (`id`) ON DELETE RESTRICT
) AUTO_INCREMENT=14;

-- Dumping data for table new_path.onboarding_questions_step_2: ~0 rows (approximately)
INSERT INTO `onboarding_questions_step_2` (`id`, `question_text`, `weight`, `scale_id`, `path`, `display_order`, `status`) VALUES
	(1, 'When you start this activity, how often do you end up doing it for much longer than you planned? ', 1.5, 1, 'BOTH', 1, 'ACTIVE'),
	(2, 'When you decide to cut back or stop this activity, how often do you find yourself unable to follow through?', 1.5, 1, 'BOTH', 2, 'ACTIVE'),
	(3, 'How often do thoughts or urges about this activity occupy your mind when you are doing something else?', 1.0, 1, 'BOTH', 3, 'ACTIVE'),
	(4, 'How often do you feel the need to spend more and more time on this activity to get the same sense of enjoyment or satisfaction you used to get?', 1.0, 1, 'BOTH', 4, 'ACTIVE'),
	(5, 'How intensely do you experience restlessness, irritability, or anxiety when you are unable to engage in this activity?', 1.5, 2, 'BOTH', 5, 'ACTIVE'),
	(6, 'How often does this activity cause you to neglect or delay your responsibilities at work, school, or home?', 1.5, 1, 'BOTH', 6, 'ACTIVE'),
	(7, 'How often do you choose this activity over spending time with friends or family?', 1.0, 1, 'BOTH', 7, 'ACTIVE'),
	(8, 'How often do you continue this activity even when you are aware it is causing problems in your relationships, health, or daily life?', 1.5, 1, 'BOTH', 8, 'ACTIVE'),
	(9, 'How often do you downplay or hide from others how much time or resources you spend on this activity?', 1.0, 1, 'BOTH', 9, 'ACTIVE'),
	(10, 'How often do you turn to this activity specifically to cope with or escape from stress, anxiety, boredom, or emotional pain?', 1.0, 1, 'BOTH', 10, 'ACTIVE'),
	(11, 'This is sample question, don\'t take seriously ', 0.6, 2, 'LITE', 11, 'ACTIVE');

-- Dumping structure for table new_path.onboarding_questions_step_3
CREATE TABLE IF NOT EXISTS `onboarding_questions_step_3` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `module_id` tinyint unsigned NOT NULL,
  `question_text` text NOT NULL,
  `weight` decimal(3,1) NOT NULL,
  `scale_id` tinyint unsigned NOT NULL,
  `display_order` tinyint unsigned NOT NULL,
  `status` enum('ACTIVE','DISABLED') NOT NULL DEFAULT 'ACTIVE',
  PRIMARY KEY (`id`),
  KEY `fk_module` (`module_id`),
  KEY `fk_scale` (`scale_id`),
  CONSTRAINT `fk_module` FOREIGN KEY (`module_id`) REFERENCES `addiction_type_module` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_scale` FOREIGN KEY (`scale_id`) REFERENCES `onboarding_question_scale` (`id`) ON DELETE RESTRICT
) AUTO_INCREMENT=35;

-- Dumping data for table new_path.onboarding_questions_step_3: ~0 rows (approximately)
INSERT INTO `onboarding_questions_step_3` (`id`, `module_id`, `question_text`, `weight`, `scale_id`, `display_order`, `status`) VALUES
	(1, 1, 'How often do you lose track of time while gaming â€” skipping meals, staying up late, or gaming through other obligations?', 1.0, 1, 1, 'ACTIVE'),
	(2, 1, 'How often do you feel compelled to reach the next goal, level, or reward before you can stop?', 0.8, 1, 2, 'ACTIVE'),
	(3, 1, 'How often do you spend money on in-game purchases beyond what you originally planned?', 1.0, 1, 3, 'ACTIVE'),
	(4, 1, 'How strongly does your gaming performance affect how you feel about yourself as a person?', 0.8, 2, 4, 'ACTIVE'),
	(5, 1, 'How often do you prefer gaming alone over spending time with people in person?', 1.0, 1, 5, 'ACTIVE'),
	(6, 2, 'How often is checking social media one of the first or last things you do each day?', 0.8, 1, 1, 'ACTIVE'),
	(7, 2, 'How often do you feel anxious, unsettled, or on edge when you haven\'t checked social media for a few hours?', 1.0, 1, 2, 'ACTIVE'),
	(8, 2, 'How often does viewing others\' content on social media leave you feeling worse about yourself?', 0.8, 1, 3, 'ACTIVE'),
	(9, 2, 'How often do you post or engage online primarily to seek reassurance or validation from others?', 1.0, 1, 4, 'ACTIVE'),
	(10, 2, 'How often do you find yourself scrolling without purpose, wanting to stop but unable to?', 1.5, 1, 5, 'ACTIVE'),
	(11, 3, 'How often do you find that you need more, longer, or different content to feel the same effect as before?', 1.5, 1, 1, 'ACTIVE'),
	(12, 3, 'How significantly has your use affected your interest in or satisfaction with real-life relationships or intimacy?', 1.5, 3, 2, 'ACTIVE'),
	(13, 3, 'How often do you feel guilt or shame after viewing, yet return to it again soon after?', 1.0, 1, 3, 'ACTIVE'),
	(14, 3, 'How often have you viewed content in settings where it was clearly inappropriate â€” at work, school, or in public?', 1.0, 1, 4, 'ACTIVE'),
	(15, 3, 'How often do you use this behavior primarily as a way to cope with loneliness, boredom, or emotional distress?', 0.8, 1, 5, 'ACTIVE'),
	(16, 4, 'How often do you notice a strong rush or excitement when browsing or making an online purchase?', 0.8, 1, 1, 'DISABLED'),
	(17, 4, 'How often do you make purchases you don\'t need or can\'t afford in order to feel better in the moment?', 1.5, 1, 2, 'ACTIVE'),
	(18, 4, 'How often do you hide purchases or feel the need to conceal how much you spend from others?', 1.0, 1, 3, 'ACTIVE'),
	(19, 4, 'How often do you experience regret after shopping, only to find yourself shopping again shortly after?', 1.0, 1, 4, 'ACTIVE'),
	(20, 4, 'How often is shopping your primary strategy for dealing with stress, boredom, or difficult emotions?', 1.0, 1, 5, 'ACTIVE'),
	(21, 5, 'How often do you spend significantly more time or in-game resources on chance-based rewards than you originally intended? Blah blah blah ', 1.5, 1, 1, 'ACTIVE'),
	(22, 5, 'How often do you increase your spending or attempts after a loss, trying to recover what you lost?', 1.5, 1, 2, 'ACTIVE'),
	(23, 5, 'How often do you experience a strong high after winning and a significant crash or low after losing?', 1.0, 1, 3, 'ACTIVE'),
	(24, 5, 'How often do you spend real money to acquire more in-game currency, attempts, or chances?', 1.0, 1, 4, 'ACTIVE'),
	(25, 5, 'How frequently do thoughts about your next chance to play or win occupy your mind throughout the day?', 0.8, 1, 5, 'ACTIVE'),
	(26, 6, 'How often do you continue watching well past when you intended to stop, despite telling yourself you\'ll quit after one more episode?', 1.5, 1, 1, 'ACTIVE'),
	(27, 6, 'How often do you feel genuinely restless, uncomfortable, or unsettled when you are not watching anything?', 1.0, 1, 2, 'ACTIVE'),
	(28, 6, 'How often do you skip sleep, meals, or important responsibilities in order to keep watching?', 1.5, 1, 3, 'ACTIVE'),
	(29, 6, 'How often is streaming your default or primary method of dealing with stress or avoiding problems?', 1.0, 1, 4, 'ACTIVE'),
	(30, 6, 'How often do you experience a noticeable sense of emptiness or loss after finishing a series?', 0.8, 1, 5, 'ACTIVE'),
	(32, 4, 'This is test question don\'t take it seriously ', 1.0, 2, 7, 'DISABLED'),
	(34, 5, 'heloooooooooooo', 1.0, 1, 6, 'ACTIVE');

-- Dumping structure for table new_path.payment_methods
CREATE TABLE IF NOT EXISTS `payment_methods` (
  `payment_method_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `method_type` enum('card','paypal','bank_transfer') NOT NULL,
  `card_last_four` varchar(4) DEFAULT NULL,
  `card_brand` varchar(20) DEFAULT NULL,
  `expiry_month` int DEFAULT NULL,
  `expiry_year` int DEFAULT NULL,
  `billing_name` varchar(100) DEFAULT NULL,
  `is_default` tinyint(1) DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `stripe_payment_method_id` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`payment_method_id`),
  KEY `idx_user` (`user_id`),
  CONSTRAINT `fk_pm_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
);

-- Dumping data for table new_path.payment_methods: ~0 rows (approximately)

-- Dumping structure for table new_path.post_comments
CREATE TABLE IF NOT EXISTS `post_comments` (
  `comment_id` int NOT NULL AUTO_INCREMENT,
  `post_id` int NOT NULL,
  `user_id` int NOT NULL,
  `parent_comment_id` int DEFAULT NULL,
  `content` text NOT NULL,
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
) AUTO_INCREMENT=6;

-- Dumping data for table new_path.post_comments: ~0 rows (approximately)

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
) AUTO_INCREMENT=30;

-- Dumping data for table new_path.post_likes: ~0 rows (approximately)

-- Dumping structure for table new_path.post_reports
CREATE TABLE IF NOT EXISTS `post_reports` (
  `report_id` int NOT NULL AUTO_INCREMENT,
  `post_id` int DEFAULT NULL,
  `comment_id` int DEFAULT NULL,
  `reporter_id` int NOT NULL,
  `reason` varchar(255) NOT NULL,
  `description` text,
  `status` enum('pending','reviewed','resolved','dismissed') DEFAULT 'pending',
  `reviewed_by` int DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `action_taken` varchar(255) DEFAULT NULL,
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
) AUTO_INCREMENT=4;

-- Dumping data for table new_path.post_reports: ~0 rows (approximately)

-- Dumping structure for table new_path.post_tag_mappings
CREATE TABLE IF NOT EXISTS `post_tag_mappings` (
  `post_id` int NOT NULL,
  `tag_id` int NOT NULL,
  PRIMARY KEY (`post_id`,`tag_id`),
  KEY `fk_ptm_tag` (`tag_id`),
  CONSTRAINT `fk_ptm_post` FOREIGN KEY (`post_id`) REFERENCES `community_posts` (`post_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ptm_tag` FOREIGN KEY (`tag_id`) REFERENCES `post_tags` (`tag_id`) ON DELETE CASCADE
);

-- Dumping data for table new_path.post_tag_mappings: ~0 rows (approximately)

-- Dumping structure for table new_path.post_tags
CREATE TABLE IF NOT EXISTS `post_tags` (
  `tag_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `post_count` int DEFAULT '0',
  PRIMARY KEY (`tag_id`),
  UNIQUE KEY `idx_slug` (`slug`)
) AUTO_INCREMENT=7;

-- Dumping data for table new_path.post_tags: ~0 rows (approximately)

-- Dumping structure for table new_path.recovery_goals
CREATE TABLE IF NOT EXISTS `recovery_goals` (
  `goal_id` int NOT NULL AUTO_INCREMENT,
  `plan_id` int NOT NULL,
  `goal_type` enum('short_term','long_term') NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text,
  `target_days` int DEFAULT NULL,
  `current_progress` int DEFAULT '0',
  `status` enum('in_progress','achieved','failed') DEFAULT 'in_progress',
  `achieved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`goal_id`),
  KEY `idx_plan` (`plan_id`),
  CONSTRAINT `fk_goal_plan` FOREIGN KEY (`plan_id`) REFERENCES `recovery_plans` (`plan_id`) ON DELETE CASCADE
) AUTO_INCREMENT=50;

-- Dumping data for table new_path.recovery_goals: ~0 rows (approximately)
INSERT INTO `recovery_goals` (`goal_id`, `plan_id`, `goal_type`, `title`, `description`, `target_days`, `current_progress`, `status`, `achieved_at`, `created_at`, `updated_at`) VALUES
	(44, 35, 'short_term', 'Complete the first 30 days of sobriety with consistent support and minimal cravings', NULL, 30, 0, 'in_progress', NULL, '2026-04-20 21:18:04', '2026-04-20 21:18:04'),
	(45, 35, 'long_term', 'Maintain sobriety for 90 days and develop a robust relapse prevention plan', NULL, 90, 0, 'in_progress', NULL, '2026-04-20 21:18:04', '2026-04-20 21:18:04'),
	(46, 36, 'short_term', 'Complete the first 30 days smoke-free with consistent support', NULL, 30, 0, 'in_progress', NULL, '2026-04-20 22:10:23', '2026-04-20 22:10:23'),
	(47, 36, 'long_term', 'Maintain smoke-free status for 90 days and build a relapse-prevention routine', NULL, 90, 0, 'in_progress', NULL, '2026-04-20 22:10:23', '2026-04-20 22:10:23'),
	(48, 37, 'short_term', 'Complete the first 30 days cannabis-free with consistent support', NULL, 30, 0, 'in_progress', NULL, '2026-04-20 22:14:10', '2026-04-20 22:14:10'),
	(49, 37, 'long_term', 'Maintain cannabis abstinence for 90 days and build a relapse-prevention routine', NULL, 90, 0, 'in_progress', NULL, '2026-04-20 22:14:10', '2026-04-20 22:14:10');

-- Dumping structure for table new_path.recovery_plans
CREATE TABLE IF NOT EXISTS `recovery_plans` (
  `plan_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `counselor_id` int DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text,
  `category` varchar(100) DEFAULT NULL,
  `plan_type` enum('counselor','self') DEFAULT 'self',
  `status` enum('draft','active','paused','completed','cancelled') DEFAULT 'draft',
  `start_date` date DEFAULT NULL,
  `target_completion_date` date DEFAULT NULL,
  `actual_completion_date` date DEFAULT NULL,
  `progress_percentage` int DEFAULT '0',
  `custom_notes` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_template` tinyint(1) DEFAULT '0',
  `template_source_id` int DEFAULT NULL,
  `assigned_status` enum('pending','accepted','rejected') DEFAULT NULL,
  `source_plan_id` int DEFAULT NULL,
  PRIMARY KEY (`plan_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_counselor` (`counselor_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_plan_counselor` FOREIGN KEY (`counselor_id`) REFERENCES `counselors` (`counselor_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_plan_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) AUTO_INCREMENT=38;

-- Dumping data for table new_path.recovery_plans: ~0 rows (approximately)
INSERT INTO `recovery_plans` (`plan_id`, `user_id`, `counselor_id`, `title`, `description`, `category`, `plan_type`, `status`, `start_date`, `target_completion_date`, `actual_completion_date`, `progress_percentage`, `custom_notes`, `created_at`, `updated_at`, `is_template`, `template_source_id`, `assigned_status`, `source_plan_id`) VALUES
	(35, 40, NULL, 'Comprehensive Alcohol Recovery Plan', 'This plan is designed to guide individuals through the recovery process, focusing on stabilization, reduction of alcohol use, and long-term maintenance of sobriety. It incorporates therapy, journaling, coping strategies, and social support.', '', 'self', 'active', '2026-04-21', NULL, NULL, 0, NULL, '2026-04-20 21:18:04', '2026-04-20 21:18:04', 0, NULL, 'accepted', 2),
	(36, 45, NULL, 'Smoking Cessation Support Plan', 'This plan focuses on nicotine reduction, habit change, and long-term maintenance, combining therapy, journaling, and coping practices.', '', 'self', 'active', '2026-04-21', NULL, NULL, 0, NULL, '2026-04-20 22:10:23', '2026-04-20 22:10:23', 0, NULL, 'accepted', 4),
	(37, 46, NULL, 'Cannabis Recovery Support Plan', 'This plan focuses on stabilization, habit change, and long-term maintenance, combining therapy, journaling, coping practice, and recovery structure to overcome cannabis dependence.', '', 'self', 'active', '2026-04-20', NULL, NULL, 0, NULL, '2026-04-20 22:14:10', '2026-04-20 22:14:10', 0, NULL, 'accepted', 3);

-- Dumping structure for table new_path.recovery_tasks
CREATE TABLE IF NOT EXISTS `recovery_tasks` (
  `task_id` int NOT NULL AUTO_INCREMENT,
  `plan_id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text,
  `task_type` enum('journal','meditation','session','exercise','custom') DEFAULT 'custom',
  `status` enum('pending','in_progress','completed','skipped') DEFAULT 'pending',
  `priority` enum('low','medium','high') DEFAULT 'medium',
  `due_date` date DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `is_recurring` tinyint(1) DEFAULT '0',
  `recurrence_pattern` varchar(50) DEFAULT NULL,
  `sort_order` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `phase` int DEFAULT '1',
  PRIMARY KEY (`task_id`),
  KEY `idx_plan` (`plan_id`),
  KEY `idx_status` (`status`),
  KEY `idx_due_date` (`due_date`),
  CONSTRAINT `fk_task_plan` FOREIGN KEY (`plan_id`) REFERENCES `recovery_plans` (`plan_id`) ON DELETE CASCADE
) AUTO_INCREMENT=309;

-- Dumping data for table new_path.recovery_tasks: ~0 rows (approximately)
INSERT INTO `recovery_tasks` (`task_id`, `plan_id`, `title`, `description`, `task_type`, `status`, `priority`, `due_date`, `completed_at`, `is_recurring`, `recurrence_pattern`, `sort_order`, `created_at`, `updated_at`, `phase`) VALUES
	(264, 35, 'Attend weekly therapy sessions for support and guidance', NULL, 'session', 'pending', 'medium', NULL, NULL, 1, 'weekly', 0, '2026-04-20 21:18:04', '2026-04-20 21:18:04', 1),
	(265, 35, 'Keep a daily journal to track progress and emotions', NULL, 'journal', 'pending', 'medium', NULL, NULL, 1, 'daily', 1, '2026-04-20 21:18:04', '2026-04-20 21:18:04', 1),
	(266, 35, 'Practice a daily 10-minute meditation for stress reduction', NULL, 'meditation', 'pending', 'medium', NULL, NULL, 1, 'daily', 2, '2026-04-20 21:18:04', '2026-04-20 21:18:04', 1),
	(267, 35, 'Engage in a 30-minute physical activity to improve mood', NULL, 'exercise', 'pending', 'medium', NULL, NULL, 1, 'daily', 3, '2026-04-20 21:18:04', '2026-04-20 21:18:04', 1),
	(268, 35, 'Reach out to a sponsor or support group for daily check-ins', NULL, 'custom', 'pending', 'medium', NULL, NULL, 1, 'daily', 4, '2026-04-20 21:18:04', '2026-04-20 21:18:04', 1),
	(269, 35, 'Review and discuss craving patterns with a therapist', NULL, 'session', 'pending', 'medium', NULL, NULL, 1, 'weekly', 5, '2026-04-20 21:18:04', '2026-04-20 21:18:04', 2),
	(270, 35, 'Use a craving log to track and manage cravings', NULL, 'journal', 'pending', 'medium', NULL, NULL, 1, 'daily', 6, '2026-04-20 21:18:04', '2026-04-20 21:18:04', 2),
	(271, 35, 'Participate in a weekly support group meeting', NULL, 'custom', 'pending', 'medium', NULL, NULL, 1, 'weekly', 7, '2026-04-20 21:18:04', '2026-04-20 21:18:04', 2),
	(272, 35, 'Engage in a hobby or activity that brings joy and fulfillment', NULL, 'custom', 'pending', 'medium', NULL, NULL, 1, 'bi-weekly', 8, '2026-04-20 21:18:04', '2026-04-20 21:18:04', 2),
	(273, 35, 'Practice a weekly review of relapse prevention strategies', NULL, 'meditation', 'pending', 'medium', NULL, NULL, 1, 'weekly', 9, '2026-04-20 21:18:04', '2026-04-20 21:18:04', 2),
	(274, 35, 'Continue attending weekly therapy sessions', NULL, 'session', 'pending', 'medium', NULL, NULL, 1, 'weekly', 10, '2026-04-20 21:18:04', '2026-04-20 21:18:04', 3),
	(275, 35, 'Maintain a weekly journal reflection on progress and challenges', NULL, 'journal', 'pending', 'medium', NULL, NULL, 1, 'weekly', 11, '2026-04-20 21:18:04', '2026-04-20 21:18:04', 3),
	(276, 35, 'Participate in monthly community service or volunteer work', NULL, 'custom', 'pending', 'medium', NULL, NULL, 0, NULL, 12, '2026-04-20 21:18:04', '2026-04-20 21:18:04', 3),
	(277, 35, 'Schedule regular check-ins with a sponsor or mentor', NULL, 'custom', 'pending', 'medium', NULL, NULL, 1, 'bi-weekly', 13, '2026-04-20 21:18:04', '2026-04-20 21:18:04', 3),
	(278, 35, 'Practice mindfulness and self-care activities daily', NULL, 'meditation', 'pending', 'medium', NULL, NULL, 1, 'daily', 14, '2026-04-20 21:18:04', '2026-04-20 21:18:04', 3),
	(279, 36, 'Attend one weekly therapy session for smoking cessation', NULL, 'session', 'pending', 'medium', NULL, NULL, 1, 'weekly', 0, '2026-04-20 22:10:23', '2026-04-20 22:10:23', 1),
	(280, 36, 'Keep a daily smoking cessation journal', NULL, 'journal', 'pending', 'medium', NULL, NULL, 1, 'daily', 1, '2026-04-20 22:10:23', '2026-04-20 22:10:23', 1),
	(281, 36, 'Practice a 5-minute stress reduction meditation', NULL, 'meditation', 'pending', 'medium', NULL, NULL, 1, 'daily', 2, '2026-04-20 22:10:23', '2026-04-20 22:10:23', 1),
	(282, 36, 'Engage in a 10-minute physical activity to manage cravings', NULL, 'exercise', 'pending', 'medium', NULL, NULL, 1, 'daily', 3, '2026-04-20 22:10:23', '2026-04-20 22:10:23', 1),
	(283, 36, 'Identify and list personal smoking triggers', NULL, 'custom', 'pending', 'medium', NULL, NULL, 0, NULL, 4, '2026-04-20 22:10:23', '2026-04-20 22:10:23', 1),
	(284, 36, 'Review progress with therapist and adjust plan', NULL, 'session', 'pending', 'medium', NULL, NULL, 1, 'weekly', 5, '2026-04-20 22:10:23', '2026-04-20 22:10:23', 2),
	(285, 36, 'Use a craving log to track and manage cravings', NULL, 'journal', 'pending', 'medium', NULL, NULL, 1, 'daily', 6, '2026-04-20 22:10:23', '2026-04-20 22:10:23', 2),
	(286, 36, 'Increase physical activity to 20 minutes to reduce stress', NULL, 'exercise', 'pending', 'medium', NULL, NULL, 1, 'daily', 7, '2026-04-20 22:10:23', '2026-04-20 22:10:23', 2),
	(287, 36, 'Practice a mindfulness exercise to manage emotions', NULL, 'meditation', 'pending', 'medium', NULL, NULL, 1, 'daily', 8, '2026-04-20 22:10:23', '2026-04-20 22:10:23', 2),
	(288, 36, 'Explore healthy alternatives to smoking', NULL, 'custom', 'pending', 'medium', NULL, NULL, 0, NULL, 9, '2026-04-20 22:10:23', '2026-04-20 22:10:23', 2),
	(289, 36, 'Continue weekly recovery support sessions', NULL, 'session', 'pending', 'medium', NULL, NULL, 1, 'weekly', 10, '2026-04-20 22:10:23', '2026-04-20 22:10:23', 3),
	(290, 36, 'Track smoke-free milestones in a journal', NULL, 'journal', 'pending', 'medium', NULL, NULL, 1, 'weekly', 11, '2026-04-20 22:10:23', '2026-04-20 22:10:23', 3),
	(291, 36, 'Practice a relapse-prevention review meditation', NULL, 'meditation', 'pending', 'medium', NULL, NULL, 1, 'weekly', 12, '2026-04-20 22:10:23', '2026-04-20 22:10:23', 3),
	(292, 36, 'Engage in a hobby or activity to maintain smoke-free lifestyle', NULL, 'custom', 'pending', 'medium', NULL, NULL, 1, 'weekly', 13, '2026-04-20 22:10:23', '2026-04-20 22:10:23', 3),
	(293, 36, 'Schedule a follow-up appointment with therapist', NULL, 'session', 'pending', 'medium', NULL, NULL, 0, NULL, 14, '2026-04-20 22:10:23', '2026-04-20 22:10:23', 3),
	(294, 37, 'Attend one weekly therapy session', NULL, 'session', 'pending', 'medium', NULL, NULL, 1, 'weekly', 0, '2026-04-20 22:14:10', '2026-04-20 22:14:10', 1),
	(295, 37, 'Write a daily journal check-in', NULL, 'journal', 'pending', 'medium', NULL, NULL, 1, 'daily', 1, '2026-04-20 22:14:10', '2026-04-20 22:14:10', 1),
	(296, 37, 'Practice a 5-minute mindfulness exercise', NULL, 'meditation', 'pending', 'medium', NULL, NULL, 1, 'daily', 2, '2026-04-20 22:14:10', '2026-04-20 22:14:10', 1),
	(297, 37, 'Engage in a 30-minute physical activity', NULL, 'exercise', 'pending', 'medium', NULL, NULL, 1, 'daily', 3, '2026-04-20 22:14:10', '2026-04-20 22:14:10', 1),
	(298, 37, 'Identify and list personal triggers', NULL, 'custom', 'pending', 'medium', NULL, NULL, 0, NULL, 4, '2026-04-20 22:14:10', '2026-04-20 22:14:10', 1),
	(299, 37, 'Review craving patterns with therapist', NULL, 'session', 'pending', 'medium', NULL, NULL, 1, 'weekly', 5, '2026-04-20 22:14:10', '2026-04-20 22:14:10', 2),
	(300, 37, 'Use a craving log after difficult moments', NULL, 'journal', 'pending', 'medium', NULL, NULL, 1, 'daily', 6, '2026-04-20 22:14:10', '2026-04-20 22:14:10', 2),
	(301, 37, 'Do a 10-minute stress reset routine', NULL, 'exercise', 'pending', 'medium', NULL, NULL, 1, 'daily', 7, '2026-04-20 22:14:10', '2026-04-20 22:14:10', 2),
	(302, 37, 'Practice a relaxation technique', NULL, 'meditation', 'pending', 'medium', NULL, NULL, 1, 'daily', 8, '2026-04-20 22:14:10', '2026-04-20 22:14:10', 2),
	(303, 37, 'Engage in a hobby or creative activity', NULL, 'custom', 'pending', 'medium', NULL, NULL, 1, 'weekly', 9, '2026-04-20 22:14:10', '2026-04-20 22:14:10', 2),
	(304, 37, 'Continue weekly recovery support', NULL, 'session', 'pending', 'medium', NULL, NULL, 1, 'weekly', 10, '2026-04-20 22:14:10', '2026-04-20 22:14:10', 3),
	(305, 37, 'Track cannabis abstinence milestones in a journal', NULL, 'journal', 'pending', 'medium', NULL, NULL, 1, 'weekly', 11, '2026-04-20 22:14:10', '2026-04-20 22:14:10', 3),
	(306, 37, 'Practice a relapse-prevention review', NULL, 'meditation', 'pending', 'medium', NULL, NULL, 1, 'weekly', 12, '2026-04-20 22:14:10', '2026-04-20 22:14:10', 3),
	(307, 37, 'Engage in a social activity with supportive peers', NULL, 'custom', 'pending', 'medium', NULL, NULL, 1, 'bi-weekly', 13, '2026-04-20 22:14:10', '2026-04-20 22:14:10', 3),
	(308, 37, 'Review and adjust recovery plan as needed', NULL, 'custom', 'pending', 'medium', NULL, NULL, 0, NULL, 14, '2026-04-20 22:14:10', '2026-04-20 22:14:10', 3);

-- Dumping structure for table new_path.refund_disputes
CREATE TABLE IF NOT EXISTS `refund_disputes` (
  `dispute_id` int NOT NULL AUTO_INCREMENT,
  `transaction_id` int NOT NULL,
  `user_id` int NOT NULL,
  `issue_type` enum('missed_session','quality_complaint','technical_issue','billing_error','unauthorized_charge','other') NOT NULL,
  `description` text,
  `requested_amount` decimal(10,2) DEFAULT NULL,
  `status` enum('pending','under_review','approved','rejected','resolved') DEFAULT 'pending',
  `admin_notes` text,
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
) AUTO_INCREMENT=2;

-- Dumping data for table new_path.refund_disputes: ~0 rows (approximately)

-- Dumping structure for table new_path.relapse_history
CREATE TABLE IF NOT EXISTS `relapse_history` (
  `relapse_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `relapse_date` date NOT NULL,
  `previous_streak_days` int DEFAULT '0',
  `reason` text,
  `notes` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`relapse_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_date` (`relapse_date`),
  CONSTRAINT `fk_relapse_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
);

-- Dumping data for table new_path.relapse_history: ~0 rows (approximately)

-- Dumping structure for table new_path.reschedule_requests
CREATE TABLE IF NOT EXISTS `reschedule_requests` (
  `request_id` int NOT NULL AUTO_INCREMENT,
  `session_id` int NOT NULL,
  `user_id` int NOT NULL,
  `counselor_id` int NOT NULL,
  `reason` text,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `counselor_note` text,
  `requested_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `credit_used` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`request_id`),
  KEY `idx_rr_session` (`session_id`),
  KEY `idx_rr_user` (`user_id`),
  KEY `idx_rr_counselor` (`counselor_id`),
  CONSTRAINT `fk_rr_session` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`session_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_rr_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) AUTO_INCREMENT=11;

-- Dumping data for table new_path.reschedule_requests: ~0 rows (approximately)

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
);

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
) AUTO_INCREMENT=4;

-- Dumping data for table new_path.saved_posts: ~0 rows (approximately)

-- Dumping structure for table new_path.session_disputes
CREATE TABLE IF NOT EXISTS `session_disputes` (
  `dispute_id` int NOT NULL AUTO_INCREMENT,
  `session_id` int NOT NULL,
  `reported_by` int NOT NULL,
  `reason` enum('no_show','other') NOT NULL DEFAULT 'no_show',
  `description` text,
  `status` enum('pending','reviewed','resolved','dismissed') NOT NULL DEFAULT 'pending',
  `reviewed_by` int DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `admin_note` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`dispute_id`),
  KEY `idx_sd_session` (`session_id`),
  KEY `idx_sd_reporter` (`reported_by`),
  CONSTRAINT `fk_sd_reporter` FOREIGN KEY (`reported_by`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_sd_session` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`session_id`) ON DELETE CASCADE
) AUTO_INCREMENT=13;

-- Dumping data for table new_path.session_disputes: ~0 rows (approximately)

-- Dumping structure for table new_path.session_extension_requests
CREATE TABLE IF NOT EXISTS `session_extension_requests` (
  `extension_id` int NOT NULL AUTO_INCREMENT,
  `session_id` int NOT NULL,
  `counselor_id` int NOT NULL,
  `user_id` int NOT NULL,
  `status` enum('pending','accepted','declined','paid','expired') DEFAULT 'pending',
  `extension_options` json NOT NULL,
  `selected_duration_minutes` int DEFAULT NULL,
  `selected_fee` decimal(10,2) DEFAULT NULL,
  `transaction_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` timestamp NULL DEFAULT NULL,
  `responded_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`extension_id`),
  KEY `session_id` (`session_id`),
  KEY `transaction_id` (`transaction_id`),
  CONSTRAINT `session_extension_requests_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`session_id`),
  CONSTRAINT `session_extension_requests_ibfk_2` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`transaction_id`)
) AUTO_INCREMENT=12;

-- Dumping data for table new_path.session_extension_requests: ~0 rows (approximately)

-- Dumping structure for table new_path.session_messages
CREATE TABLE IF NOT EXISTS `session_messages` (
  `message_id` int NOT NULL AUTO_INCREMENT,
  `session_id` int NOT NULL,
  `sender_id` int NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`message_id`),
  KEY `idx_session` (`session_id`),
  KEY `idx_sender` (`sender_id`),
  CONSTRAINT `fk_sm_sender` FOREIGN KEY (`sender_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_sm_session` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`session_id`) ON DELETE CASCADE
);

-- Dumping data for table new_path.session_messages: ~0 rows (approximately)

-- Dumping structure for table new_path.sessions
CREATE TABLE IF NOT EXISTS `sessions` (
  `session_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `counselor_id` int NOT NULL,
  `session_datetime` datetime NOT NULL,
  `duration_minutes` int DEFAULT '60',
  `extended_minutes` int DEFAULT '0',
  `extension_fee` decimal(10,2) DEFAULT '0.00',
  `session_type` enum('video','audio','chat','in_person') DEFAULT 'video',
  `status` enum('scheduled','confirmed','in_progress','completed','cancelled','no_show') DEFAULT 'scheduled',
  `location` varchar(255) DEFAULT NULL,
  `meeting_link` varchar(500) DEFAULT NULL,
  `meet_space_name` varchar(255) DEFAULT NULL,
  `session_notes` text,
  `counselor_private_notes` text,
  `rating` int DEFAULT NULL,
  `review` text,
  `cancelled_by` int DEFAULT NULL,
  `cancellation_reason` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`session_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_counselor` (`counselor_id`),
  KEY `idx_datetime` (`session_datetime`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_session_counselor` FOREIGN KEY (`counselor_id`) REFERENCES `counselors` (`counselor_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_session_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) AUTO_INCREMENT=10;

-- Dumping data for table new_path.sessions: ~1 rows (approximately)
INSERT INTO `sessions` (`session_id`, `user_id`, `counselor_id`, `session_datetime`, `duration_minutes`, `extended_minutes`, `extension_fee`, `session_type`, `status`, `location`, `meeting_link`, `meet_space_name`, `session_notes`, `counselor_private_notes`, `rating`, `review`, `cancelled_by`, `cancellation_reason`, `created_at`, `updated_at`) VALUES
	(8, 40, 11, '2026-04-20 09:00:00', 60, 0, 0.00, 'video', 'completed', NULL, 'https://meet.google.com/yem-viqw-vdq', 'spaces/g0ZjsDLjeXsB', NULL, NULL, 5, 'He was Realy helpful', NULL, NULL, '2026-04-20 21:56:40', '2026-04-20 21:58:52'),
	(9, 40, 11, '2026-04-27 11:00:00', 60, 0, 0.00, 'video', 'scheduled', NULL, 'https://meet.google.com/txd-acsa-sux', 'spaces/pkGV22rX1r0B', NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-20 22:05:57', '2026-04-20 22:05:57');

-- Dumping structure for table new_path.support_group_members
CREATE TABLE IF NOT EXISTS `support_group_members` (
  `membership_id` int NOT NULL AUTO_INCREMENT,
  `group_id` int NOT NULL,
  `user_id` int NOT NULL,
  `role` enum('member','moderator','leader') DEFAULT 'member',
  `joined_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`membership_id`),
  UNIQUE KEY `idx_group_user` (`group_id`,`user_id`),
  KEY `fk_sgm_user` (`user_id`),
  CONSTRAINT `fk_sgm_group` FOREIGN KEY (`group_id`) REFERENCES `support_groups` (`group_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_sgm_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) AUTO_INCREMENT=12;

-- Dumping data for table new_path.support_group_members: ~0 rows (approximately)
INSERT INTO `support_group_members` (`membership_id`, `group_id`, `user_id`, `role`, `joined_at`) VALUES
	(11, 11, 40, 'member', '2026-04-20 22:33:33');

-- Dumping structure for table new_path.support_group_messages
CREATE TABLE IF NOT EXISTS `support_group_messages` (
  `message_id` int NOT NULL AUTO_INCREMENT,
  `group_id` int NOT NULL,
  `user_id` int NOT NULL,
  `content` text NOT NULL,
  `is_pinned` tinyint(1) DEFAULT '0',
  `is_deleted` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`message_id`),
  KEY `idx_group` (`group_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_created` (`created_at`)
) AUTO_INCREMENT=11;

-- Dumping data for table new_path.support_group_messages: ~0 rows (approximately)
INSERT INTO `support_group_messages` (`message_id`, `group_id`, `user_id`, `content`, `is_pinned`, `is_deleted`, `created_at`) VALUES
	(10, 11, 40, 'hi', 0, 0, '2026-04-20 22:33:44');

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
);

-- Dumping data for table new_path.support_group_session_registrations: ~0 rows (approximately)

-- Dumping structure for table new_path.support_group_sessions
CREATE TABLE IF NOT EXISTS `support_group_sessions` (
  `group_session_id` int NOT NULL AUTO_INCREMENT,
  `group_id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text,
  `session_datetime` datetime NOT NULL,
  `duration_minutes` int DEFAULT '60',
  `session_type` enum('video','in_person') DEFAULT 'video',
  `meeting_link` varchar(500) DEFAULT NULL,
  `meeting_location` varchar(255) DEFAULT NULL,
  `max_participants` int DEFAULT NULL,
  `is_recurring` tinyint(1) DEFAULT '0',
  `recurrence_pattern` varchar(50) DEFAULT NULL,
  `status` enum('scheduled','completed','cancelled') DEFAULT 'scheduled',
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`group_session_id`),
  KEY `idx_group_datetime` (`group_id`,`session_datetime`),
  KEY `idx_created_by` (`created_by`),
  CONSTRAINT `fk_sgs_admin` FOREIGN KEY (`created_by`) REFERENCES `admin` (`admin_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_sgs_group` FOREIGN KEY (`group_id`) REFERENCES `support_groups` (`group_id`) ON DELETE CASCADE
);

-- Dumping data for table new_path.support_group_sessions: ~0 rows (approximately)

-- Dumping structure for table new_path.support_groups
CREATE TABLE IF NOT EXISTS `support_groups` (
  `group_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text,
  `category` varchar(100) DEFAULT NULL,
  `meeting_schedule` varchar(255) DEFAULT NULL,
  `meeting_link` varchar(500) DEFAULT NULL,
  `max_members` int DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`group_id`),
  KEY `idx_active` (`is_active`),
  KEY `fk_sg_admin` (`created_by`),
  CONSTRAINT `fk_sg_admin` FOREIGN KEY (`created_by`) REFERENCES `admin` (`admin_id`) ON DELETE SET NULL
) AUTO_INCREMENT=13;

-- Dumping data for table new_path.support_groups: ~0 rows (approximately)
INSERT INTO `support_groups` (`group_id`, `name`, `description`, `category`, `meeting_schedule`, `meeting_link`, `max_members`, `is_active`, `created_by`, `created_at`, `updated_at`) VALUES
	(11, 'Alcohol Addition Recovery ', '', 'alcohol', '', NULL, NULL, 1, NULL, '2026-04-20 22:31:57', '2026-04-20 22:31:57'),
	(12, 'The Fresh Start Initiative', '', 'general', '', NULL, NULL, 1, NULL, '2026-04-20 22:32:22', '2026-04-20 22:32:22');

-- Dumping structure for table new_path.system_plan_tasks
CREATE TABLE IF NOT EXISTS `system_plan_tasks` (
  `task_id` int NOT NULL AUTO_INCREMENT,
  `plan_id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `task_type` enum('journal','meditation','session','exercise','custom') DEFAULT 'custom',
  `priority` enum('low','medium','high') DEFAULT 'medium',
  `phase` tinyint(1) NOT NULL DEFAULT '1',
  `is_milestone` tinyint(1) NOT NULL DEFAULT '0',
  `is_recurring` tinyint(1) NOT NULL DEFAULT '0',
  `recurrence_pattern` varchar(50) DEFAULT NULL,
  `sort_order` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`task_id`),
  KEY `idx_plan` (`plan_id`),
  CONSTRAINT `system_plan_tasks_ibfk_1` FOREIGN KEY (`plan_id`) REFERENCES `system_plans` (`plan_id`) ON DELETE CASCADE
) AUTO_INCREMENT=88;

-- Dumping data for table new_path.system_plan_tasks: ~0 rows (approximately)
INSERT INTO `system_plan_tasks` (`task_id`, `plan_id`, `title`, `task_type`, `priority`, `phase`, `is_milestone`, `is_recurring`, `recurrence_pattern`, `sort_order`, `created_at`) VALUES
	(25, 2, 'Attend weekly therapy sessions for support and guidance', 'session', 'medium', 1, 0, 1, 'weekly', 0, '2026-04-20 21:11:40'),
	(26, 2, 'Keep a daily journal to track progress and emotions', 'journal', 'medium', 1, 0, 1, 'daily', 1, '2026-04-20 21:11:40'),
	(27, 2, 'Practice a daily 10-minute meditation for stress reduction', 'meditation', 'medium', 1, 0, 1, 'daily', 2, '2026-04-20 21:11:40'),
	(28, 2, 'Engage in a 30-minute physical activity to improve mood', 'exercise', 'medium', 1, 0, 1, 'daily', 3, '2026-04-20 21:11:40'),
	(29, 2, 'Reach out to a sponsor or support group for daily check-ins', 'custom', 'medium', 1, 0, 1, 'daily', 4, '2026-04-20 21:11:40'),
	(30, 2, 'Review and discuss craving patterns with a therapist', 'session', 'medium', 2, 0, 1, 'weekly', 5, '2026-04-20 21:11:40'),
	(31, 2, 'Use a craving log to track and manage cravings', 'journal', 'medium', 2, 0, 1, 'daily', 6, '2026-04-20 21:11:40'),
	(32, 2, 'Participate in a weekly support group meeting', 'custom', 'medium', 2, 0, 1, 'weekly', 7, '2026-04-20 21:11:40'),
	(33, 2, 'Engage in a hobby or activity that brings joy and fulfillment', 'custom', 'medium', 2, 0, 1, 'bi-weekly', 8, '2026-04-20 21:11:40'),
	(34, 2, 'Practice a weekly review of relapse prevention strategies', 'meditation', 'medium', 2, 0, 1, 'weekly', 9, '2026-04-20 21:11:40'),
	(35, 2, 'Continue attending weekly therapy sessions', 'session', 'medium', 3, 0, 1, 'weekly', 10, '2026-04-20 21:11:40'),
	(36, 2, 'Maintain a weekly journal reflection on progress and challenges', 'journal', 'medium', 3, 0, 1, 'weekly', 11, '2026-04-20 21:11:40'),
	(37, 2, 'Participate in monthly community service or volunteer work', 'custom', 'medium', 3, 0, 0, NULL, 12, '2026-04-20 21:11:40'),
	(38, 2, 'Schedule regular check-ins with a sponsor or mentor', 'custom', 'medium', 3, 0, 1, 'bi-weekly', 13, '2026-04-20 21:11:40'),
	(39, 2, 'Practice mindfulness and self-care activities daily', 'meditation', 'medium', 3, 0, 1, 'daily', 14, '2026-04-20 21:11:40'),
	(40, 2, 'Complete the first week without alcohol', 'custom', 'medium', 1, 1, 0, NULL, 0, '2026-04-20 21:11:40'),
	(41, 2, 'Identify and list personal triggers', 'custom', 'medium', 1, 1, 0, NULL, 1, '2026-04-20 21:11:40'),
	(42, 2, 'Reduce cravings by 50%', 'custom', 'medium', 2, 1, 0, NULL, 0, '2026-04-20 21:11:40'),
	(43, 2, 'Develop a crisis management plan', 'custom', 'medium', 2, 1, 0, NULL, 1, '2026-04-20 21:11:40'),
	(44, 2, 'Celebrate 90 days of sobriety', 'custom', 'medium', 3, 1, 0, NULL, 0, '2026-04-20 21:11:40'),
	(45, 2, 'Plan for long-term recovery and personal growth', 'custom', 'medium', 3, 1, 0, NULL, 1, '2026-04-20 21:11:40'),
	(46, 3, 'Attend one weekly therapy session', 'session', 'medium', 1, 0, 1, 'weekly', 0, '2026-04-20 21:15:51'),
	(47, 3, 'Write a daily journal check-in', 'journal', 'medium', 1, 0, 1, 'daily', 1, '2026-04-20 21:15:51'),
	(48, 3, 'Practice a 5-minute mindfulness exercise', 'meditation', 'medium', 1, 0, 1, 'daily', 2, '2026-04-20 21:15:51'),
	(49, 3, 'Engage in a 30-minute physical activity', 'exercise', 'medium', 1, 0, 1, 'daily', 3, '2026-04-20 21:15:51'),
	(50, 3, 'Identify and list personal triggers', 'custom', 'medium', 1, 0, 0, NULL, 4, '2026-04-20 21:15:51'),
	(51, 3, 'Review craving patterns with therapist', 'session', 'medium', 2, 0, 1, 'weekly', 5, '2026-04-20 21:15:51'),
	(52, 3, 'Use a craving log after difficult moments', 'journal', 'medium', 2, 0, 1, 'daily', 6, '2026-04-20 21:15:51'),
	(53, 3, 'Do a 10-minute stress reset routine', 'exercise', 'medium', 2, 0, 1, 'daily', 7, '2026-04-20 21:15:51'),
	(54, 3, 'Practice a relaxation technique', 'meditation', 'medium', 2, 0, 1, 'daily', 8, '2026-04-20 21:15:51'),
	(55, 3, 'Engage in a hobby or creative activity', 'custom', 'medium', 2, 0, 1, 'weekly', 9, '2026-04-20 21:15:51'),
	(56, 3, 'Continue weekly recovery support', 'session', 'medium', 3, 0, 1, 'weekly', 10, '2026-04-20 21:15:51'),
	(57, 3, 'Track cannabis abstinence milestones in a journal', 'journal', 'medium', 3, 0, 1, 'weekly', 11, '2026-04-20 21:15:51'),
	(58, 3, 'Practice a relapse-prevention review', 'meditation', 'medium', 3, 0, 1, 'weekly', 12, '2026-04-20 21:15:51'),
	(59, 3, 'Engage in a social activity with supportive peers', 'custom', 'medium', 3, 0, 1, 'bi-weekly', 13, '2026-04-20 21:15:51'),
	(60, 3, 'Review and adjust recovery plan as needed', 'custom', 'medium', 3, 0, 0, NULL, 14, '2026-04-20 21:15:51'),
	(61, 3, 'Complete the first week cannabis-free', 'custom', 'medium', 1, 1, 0, NULL, 0, '2026-04-20 21:15:51'),
	(62, 3, 'Identify top relapse triggers', 'custom', 'medium', 1, 1, 0, NULL, 1, '2026-04-20 21:15:51'),
	(63, 3, 'Reduce high-risk situations', 'custom', 'medium', 2, 1, 0, NULL, 0, '2026-04-20 21:15:51'),
	(64, 3, 'Use coping skills without prompting', 'custom', 'medium', 2, 1, 0, NULL, 1, '2026-04-20 21:15:51'),
	(65, 3, 'Build a stable cannabis-free routine', 'custom', 'medium', 3, 1, 0, NULL, 0, '2026-04-20 21:15:51'),
	(66, 3, 'Prepare a follow-up plan', 'custom', 'medium', 3, 1, 0, NULL, 1, '2026-04-20 21:15:51'),
	(67, 4, 'Attend one weekly therapy session for smoking cessation', 'session', 'medium', 1, 0, 1, 'weekly', 0, '2026-04-20 21:19:49'),
	(68, 4, 'Keep a daily smoking cessation journal', 'journal', 'medium', 1, 0, 1, 'daily', 1, '2026-04-20 21:19:49'),
	(69, 4, 'Practice a 5-minute stress reduction meditation', 'meditation', 'medium', 1, 0, 1, 'daily', 2, '2026-04-20 21:19:49'),
	(70, 4, 'Engage in a 10-minute physical activity to manage cravings', 'exercise', 'medium', 1, 0, 1, 'daily', 3, '2026-04-20 21:19:49'),
	(71, 4, 'Identify and list personal smoking triggers', 'custom', 'medium', 1, 0, 0, NULL, 4, '2026-04-20 21:19:49'),
	(72, 4, 'Review progress with therapist and adjust plan', 'session', 'medium', 2, 0, 1, 'weekly', 5, '2026-04-20 21:19:49'),
	(73, 4, 'Use a craving log to track and manage cravings', 'journal', 'medium', 2, 0, 1, 'daily', 6, '2026-04-20 21:19:49'),
	(74, 4, 'Increase physical activity to 20 minutes to reduce stress', 'exercise', 'medium', 2, 0, 1, 'daily', 7, '2026-04-20 21:19:49'),
	(75, 4, 'Practice a mindfulness exercise to manage emotions', 'meditation', 'medium', 2, 0, 1, 'daily', 8, '2026-04-20 21:19:49'),
	(76, 4, 'Explore healthy alternatives to smoking', 'custom', 'medium', 2, 0, 0, NULL, 9, '2026-04-20 21:19:49'),
	(77, 4, 'Continue weekly recovery support sessions', 'session', 'medium', 3, 0, 1, 'weekly', 10, '2026-04-20 21:19:49'),
	(78, 4, 'Track smoke-free milestones in a journal', 'journal', 'medium', 3, 0, 1, 'weekly', 11, '2026-04-20 21:19:49'),
	(79, 4, 'Practice a relapse-prevention review meditation', 'meditation', 'medium', 3, 0, 1, 'weekly', 12, '2026-04-20 21:19:49'),
	(80, 4, 'Engage in a hobby or activity to maintain smoke-free lifestyle', 'custom', 'medium', 3, 0, 1, 'weekly', 13, '2026-04-20 21:19:49'),
	(81, 4, 'Schedule a follow-up appointment with therapist', 'session', 'medium', 3, 0, 0, NULL, 14, '2026-04-20 21:19:49'),
	(82, 4, 'Complete the first week smoke-free', 'custom', 'medium', 1, 1, 0, NULL, 0, '2026-04-20 21:19:49'),
	(83, 4, 'Identify top smoking triggers', 'custom', 'medium', 1, 1, 0, NULL, 1, '2026-04-20 21:19:49'),
	(84, 4, 'Reduce high-risk situations', 'custom', 'medium', 2, 1, 0, NULL, 0, '2026-04-20 21:19:49'),
	(85, 4, 'Use coping skills without prompting', 'custom', 'medium', 2, 1, 0, NULL, 1, '2026-04-20 21:19:49'),
	(86, 4, 'Build a stable smoke-free routine', 'custom', 'medium', 3, 1, 0, NULL, 0, '2026-04-20 21:19:49'),
	(87, 4, 'Prepare a long-term maintenance plan', 'custom', 'medium', 3, 1, 0, NULL, 1, '2026-04-20 21:19:49');

-- Dumping structure for table new_path.system_plans
CREATE TABLE IF NOT EXISTS `system_plans` (
  `plan_id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text,
  `category` varchar(100) DEFAULT 'General',
  `image` varchar(255) DEFAULT NULL,
  `goal` text,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `short_term_goal_title` varchar(255) DEFAULT NULL,
  `short_term_goal_days` int DEFAULT '30',
  `long_term_goal_title` varchar(255) DEFAULT NULL,
  `long_term_goal_days` int DEFAULT '90',
  `notes` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`plan_id`)
) AUTO_INCREMENT=5;

-- Dumping data for table new_path.system_plans: ~0 rows (approximately)
INSERT INTO `system_plans` (`plan_id`, `title`, `description`, `category`, `image`, `goal`, `start_date`, `end_date`, `short_term_goal_title`, `short_term_goal_days`, `long_term_goal_title`, `long_term_goal_days`, `notes`, `created_at`, `updated_at`) VALUES
	(2, 'Comprehensive Alcohol Recovery Plan', 'This plan is designed to guide individuals through the recovery process, focusing on stabilization, reduction of alcohol use, and long-term maintenance of sobriety. It incorporates therapy, journaling, coping strategies, and social support.', '', '/uploads/system-plans/plan_e95d89e29e444bb6d3b62e63cd1de06a.png', 'Achieve and maintain sobriety through a structured and supportive approach.', '2026-04-21', '2026-07-31', 'Complete the first 30 days of sobriety with consistent support and minimal cravings', 30, 'Maintain sobriety for 90 days and develop a robust relapse prevention plan', 90, '', '2026-04-20 21:11:40', '2026-04-20 21:11:40'),
	(3, 'Cannabis Recovery Support Plan', 'This plan focuses on stabilization, habit change, and long-term maintenance, combining therapy, journaling, coping practice, and recovery structure to overcome cannabis dependence.', '', '/uploads/system-plans/plan_84f199cbb737551d9fd36b6ddf8260dc.png', 'Support sustained cannabis abstinence and strengthen relapse-prevention skills.', '2026-04-20', '2026-04-25', 'Complete the first 30 days cannabis-free with consistent support', 30, 'Maintain cannabis abstinence for 90 days and build a relapse-prevention routine', 90, '', '2026-04-20 21:15:51', '2026-04-20 21:15:51'),
	(4, 'Smoking Cessation Support Plan', 'This plan focuses on nicotine reduction, habit change, and long-term maintenance, combining therapy, journaling, and coping practices.', '', '/uploads/system-plans/plan_ec21921692b0dfbb45876fe8786e281d.png', 'Achieve and maintain smoking cessation for improved health and wellbeing.', '2026-04-21', '2026-07-21', 'Complete the first 30 days smoke-free with consistent support', 30, 'Maintain smoke-free status for 90 days and build a relapse-prevention routine', 90, '', '2026-04-20 21:19:49', '2026-04-20 21:19:49');

-- Dumping structure for table new_path.system_settings
CREATE TABLE IF NOT EXISTS `system_settings` (
  `setting_id` int NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text,
  `description` text,
  `updated_by` int DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`setting_id`),
  UNIQUE KEY `idx_key` (`setting_key`)
);

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
) AUTO_INCREMENT=5;

-- Dumping data for table new_path.task_change_requests: ~0 rows (approximately)

-- Dumping structure for table new_path.transactions
CREATE TABLE IF NOT EXISTS `transactions` (
  `transaction_id` int NOT NULL AUTO_INCREMENT,
  `transaction_uuid` varchar(100) NOT NULL,
  `session_id` int DEFAULT NULL,
  `user_id` int NOT NULL,
  `counselor_id` int DEFAULT NULL,
  `payment_method_id` int DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) NOT NULL DEFAULT 'LKR',
  `payment_type` enum('session','subscription','tip','refund','reschedule_credit') NOT NULL DEFAULT 'session',
  `status` enum('pending','completed','failed','refunded','disputed') DEFAULT 'pending',
  `payhere_order_id` varchar(255) DEFAULT NULL,
  `payhere_payment_id` varchar(255) DEFAULT NULL,
  `payhere_status_code` varchar(10) DEFAULT NULL,
  `stripe_payment_intent_id` varchar(255) DEFAULT NULL,
  `failure_reason` text,
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
) AUTO_INCREMENT=46;

-- Dumping data for table new_path.transactions: ~0 rows (approximately)
INSERT INTO `transactions` (`transaction_id`, `transaction_uuid`, `session_id`, `user_id`, `counselor_id`, `payment_method_id`, `amount`, `currency`, `payment_type`, `status`, `payhere_order_id`, `payhere_payment_id`, `payhere_status_code`, `stripe_payment_intent_id`, `failure_reason`, `processed_at`, `created_at`, `updated_at`) VALUES
	(44, '7f40855fc88d01b788e2497ceba2f68c', 8, 40, 11, NULL, 3850.00, 'LKR', 'session', 'completed', 'HOLD-88', '', '2', NULL, NULL, '2026-04-20 21:56:40', '2026-04-20 21:56:40', '2026-04-20 21:56:40'),
	(45, '2fff5a1d6709ac3ee04af6a3f10e3faf', 9, 40, 11, NULL, 3850.00, 'LKR', 'session', 'completed', 'HOLD-89', '', '2', NULL, NULL, '2026-04-20 22:05:57', '2026-04-20 22:05:57', '2026-04-20 22:05:57');

-- Dumping structure for table new_path.trigger_categories
CREATE TABLE IF NOT EXISTS `trigger_categories` (
  `category_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  `is_default` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`category_id`)
) AUTO_INCREMENT=6;

-- Dumping data for table new_path.trigger_categories: ~0 rows (approximately)

-- Dumping structure for table new_path.urge_logs
CREATE TABLE IF NOT EXISTS `urge_logs` (
  `urge_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `logged_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `intensity` int DEFAULT NULL,
  `trigger_category` varchar(100) DEFAULT NULL,
  `trigger_description` text,
  `coping_strategy_used` text,
  `outcome` enum('resisted','relapsed','in_progress') DEFAULT 'resisted',
  `location` varchar(255) DEFAULT NULL,
  `notes` text,
  PRIMARY KEY (`urge_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_logged_at` (`logged_at`),
  CONSTRAINT `fk_urge_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) AUTO_INCREMENT=23;

-- Dumping data for table new_path.urge_logs: ~0 rows (approximately)

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
) AUTO_INCREMENT=8;

-- Dumping data for table new_path.user_achievements: ~0 rows (approximately)

-- Dumping structure for table new_path.user_connections
CREATE TABLE IF NOT EXISTS `user_connections` (
  `connection_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `connected_user_id` int NOT NULL,
  `status` enum('pending','accepted','declined','blocked') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`connection_id`),
  UNIQUE KEY `idx_connection_pair` (`user_id`,`connected_user_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_connected_user` (`connected_user_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_conn_connected_user` FOREIGN KEY (`connected_user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_conn_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) AUTO_INCREMENT=22;

-- Dumping data for table new_path.user_connections: ~0 rows (approximately)
INSERT INTO `user_connections` (`connection_id`, `user_id`, `connected_user_id`, `status`, `created_at`, `updated_at`) VALUES
	(21, 46, 40, 'accepted', '2026-04-20 22:15:52', '2026-04-20 22:15:52');

-- Dumping structure for table new_path.user_profiles
CREATE TABLE IF NOT EXISTS `user_profiles` (
  `profile_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `emergency_contact_name` varchar(100) DEFAULT NULL,
  `emergency_contact_phone` varchar(20) DEFAULT NULL,
  `sobriety_start_date` date DEFAULT NULL,
  `last_relapse_date` date DEFAULT NULL,
  `recovery_type` varchar(100) DEFAULT NULL,
  `notification_preferences` json DEFAULT NULL,
  `privacy_settings` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `substance_frequency` varchar(50) DEFAULT NULL,
  `last_used_timeframe` varchar(50) DEFAULT NULL,
  `quit_attempts` int DEFAULT NULL,
  `motivation_level` varchar(50) DEFAULT NULL,
  `risk_score` int DEFAULT NULL,
  `bio` text,
  `is_anonymous` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`profile_id`),
  UNIQUE KEY `idx_user` (`user_id`),
  CONSTRAINT `fk_profile_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) AUTO_INCREMENT=38;

-- Dumping data for table new_path.user_profiles: ~0 rows (approximately)
INSERT INTO `user_profiles` (`profile_id`, `user_id`, `emergency_contact_name`, `emergency_contact_phone`, `sobriety_start_date`, `last_relapse_date`, `recovery_type`, `notification_preferences`, `privacy_settings`, `created_at`, `updated_at`, `substance_frequency`, `last_used_timeframe`, `quit_attempts`, `motivation_level`, `risk_score`, `bio`, `is_anonymous`) VALUES
	(35, 40, '', '', '2026-04-21', NULL, '', NULL, NULL, '2026-04-20 20:58:06', '2026-04-20 22:09:23', 'None', 'Never', 0, 'exploring', 5, NULL, 0),
	(36, 45, '', '', '2026-04-21', NULL, '', NULL, NULL, '2026-04-20 22:10:10', '2026-04-20 22:11:30', 'None', 'Never', 0, 'exploring', 5, NULL, 0),
	(37, 46, '', '', NULL, NULL, '', NULL, NULL, '2026-04-20 22:14:03', '2026-04-20 22:14:57', 'None', 'Never', 0, 'exploring', 5, NULL, 0);

-- Dumping structure for table new_path.user_progress
CREATE TABLE IF NOT EXISTS `user_progress` (
  `progress_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `date` date NOT NULL,
  `days_sober` int DEFAULT '0',
  `is_sober_today` tinyint(1) DEFAULT '1',
  `milestone_progress` int DEFAULT '0',
  `notes` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`progress_id`),
  UNIQUE KEY `idx_user_date` (`user_id`,`date`),
  CONSTRAINT `fk_progress_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) AUTO_INCREMENT=35;

-- Dumping data for table new_path.user_progress: ~0 rows (approximately)
INSERT INTO `user_progress` (`progress_id`, `user_id`, `date`, `days_sober`, `is_sober_today`, `milestone_progress`, `notes`, `created_at`, `updated_at`) VALUES
	(33, 40, '2026-04-21', 0, 1, 0, 'Started sobriety tracking', '2026-04-20 22:06:24', '2026-04-20 22:06:24'),
	(34, 45, '2026-04-21', 0, 1, 0, 'Started sobriety tracking', '2026-04-20 22:10:34', '2026-04-20 22:10:34');

-- Dumping structure for table new_path.users
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `salt` varchar(255) NOT NULL,
  `role` enum('user','counselor','admin') NOT NULL DEFAULT 'user',
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `display_name` varchar(100) DEFAULT NULL,
  `profile_picture` varchar(500) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `age` int DEFAULT NULL,
  `gender` enum('male','female','other','prefer_not_to_say') DEFAULT NULL,
  `is_email_verified` tinyint(1) DEFAULT '0',
  `email_verification_token` varchar(255) DEFAULT NULL,
  `password_reset_token` varchar(255) DEFAULT NULL,
  `password_reset_expires` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `status` enum('active','banned') NOT NULL DEFAULT 'active',
  `must_change_password` tinyint(1) NOT NULL DEFAULT '0',
  `last_login` timestamp NULL DEFAULT NULL,
  `onboarding_completed` tinyint(1) DEFAULT '0',
  `current_onboarding_step` int DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `bio` text,
  `current_goal` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `idx_email` (`email`),
  UNIQUE KEY `idx_username` (`username`),
  KEY `idx_role` (`role`),
  KEY `idx_active` (`is_active`)
) AUTO_INCREMENT=47;

-- Dumping data for table new_path.users: ~1 rows (approximately)
INSERT INTO `users` (`user_id`, `email`, `username`, `password_hash`, `salt`, `role`, `first_name`, `last_name`, `display_name`, `profile_picture`, `phone_number`, `age`, `gender`, `is_email_verified`, `email_verification_token`, `password_reset_token`, `password_reset_expires`, `is_active`, `status`, `must_change_password`, `last_login`, `onboarding_completed`, `current_onboarding_step`, `created_at`, `updated_at`, `bio`, `current_goal`) VALUES
	(1, 'admin@newpath.com', 'admin', '$2a$10$m3uVRJ8S7NVswiFpooQowuqLGMjeNlPGssXScEPVyqQ8LrM7oQXMe', '$2a$10$92IXUNpkjO0rOQ5byMi.Ye', 'admin', 'System', 'Administrator', 'System Admin', NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, 1, 'active', 0, NULL, 0, 1, '2026-01-02 12:30:38', '2026-01-19 03:19:41', NULL, NULL),
	(40, 'Pasidurajapaksha202@gmail.com', NULL, '$2y$10$x8GOb8AehYV4fFwnBdvVA.NCzefQ/sOl/ZQ8BUWxJy/YF1isslDdi', '5654b82d7316b9f2e080a2e0e79f88da', 'user', 'Pasidu', 'Rajapaksha', 'Pasidu', '/uploads/profiles/profile_40_1776720087.jpg', '0773623777', 34, 'male', 0, NULL, '382df1d9e2d2a07667e1112dcc70b5550f34397cf347efda33e9bcb190663e0e', '2026-04-20 22:26:09', 1, 'active', 0, NULL, 1, 5, '2026-04-20 20:57:57', '2026-04-20 21:26:09', NULL, NULL),
	(41, 'Pasindurajapaksha202@gmail.com', 'pasidu_rajapaksha', '$2y$10$4q6AsDGWumA5T0MEaX3ZeeI.lxVavCAsZI2R5m6U2w9bh46qtFOwy', '', 'counselor', NULL, NULL, 'Pasidu Rajapaksha', NULL, '+94773623777', NULL, NULL, 0, NULL, NULL, NULL, 1, 'active', 1, NULL, 0, 1, '2026-04-20 21:32:02', '2026-04-20 21:32:02', NULL, NULL),
	(42, 'e.vance.therapy@example.com', 'dr_eleanor_vance', '$2y$10$d6KNL6XeFxCj1BxGJdaGB.7.lSstfPHxp.jmSSHdePdusxr9g29fC', '', 'counselor', NULL, NULL, 'Dr. Eleanor Vance', '/uploads/profiles/profile_42_1776721315.jpg', '+94773455355', NULL, NULL, 0, NULL, NULL, NULL, 1, 'active', 0, NULL, 0, 1, '2026-04-20 21:38:00', '2026-04-20 21:41:55', NULL, NULL),
	(43, 'm.thompson.lmft@emailprovider.co', 'marcus_thompson', '$2y$10$MsBlYc30V0BWOfDWvhwOWOsgRqxFoYNQ0O9G4ct/jlVeDQI0TdXJG', '', 'counselor', NULL, NULL, 'Marcus Thompson', '/uploads/profiles/profile_43_1776721729.jpg', '+61714710856', NULL, NULL, 0, NULL, NULL, NULL, 1, 'active', 0, NULL, 0, 1, '2026-04-20 21:47:16', '2026-04-20 21:48:49', NULL, NULL),
	(44, 'puski200322@gmail.com', 'yohan', '$2y$10$tv4mJrWPWnKY4dbJM./3.OxEtzK7QNKAtoRYf3ROXG6bTOzuCMIhK', '', 'counselor', NULL, NULL, 'Yohan', '/uploads/profiles/profile_44_1776722058.jpg', '+617147108578', NULL, NULL, 0, NULL, NULL, NULL, 1, 'active', 0, NULL, 0, 1, '2026-04-20 21:53:06', '2026-04-20 21:54:18', NULL, NULL),
	(45, 'lasantha@gmail.com', NULL, '$2y$10$xb/CMZsRIVCz9XtfbstpE.hre4LUSKW.LpWB1dv60WOqkACPtIiDK', '3c253e12766adb0ca24586b8d2301db8', 'user', 'Lasantha', 'Wickramasinghe', 'lasantha', '/uploads/profiles/profile_45_1776723090.jpg', '', 34, 'male', 0, NULL, NULL, NULL, 1, 'active', 0, NULL, 1, 5, '2026-04-20 22:10:04', '2026-04-20 22:11:30', NULL, NULL),
	(46, 'vihangaoshan@gmail.com', NULL, '$2y$10$DhrGu1L7M.BFErzLyhW/DOeDtmpjULR1thhfNNW.4GEQjpnmTfK2e', '789bd87e64602cf28744be15fab30413', 'user', 'Vihanga', 'Oshan', 'Vihanga', '/uploads/profiles/profile_46_1776723297.jpg', '', 45, 'male', 0, NULL, NULL, NULL, 1, 'active', 0, NULL, 1, 5, '2026-04-20 22:13:58', '2026-04-20 22:14:57', NULL, NULL);

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
