-- Support Group Sessions tables
-- Run this SQL to add the new tables to the database

CREATE TABLE IF NOT EXISTS `support_group_sessions` (
  `group_session_id` INT NOT NULL AUTO_INCREMENT,
  `group_id` INT NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `session_datetime` DATETIME NOT NULL,
  `duration_minutes` INT DEFAULT 60,
  `session_type` ENUM('video','in_person') DEFAULT 'video',
  `meeting_link` VARCHAR(500) DEFAULT NULL,
  `meeting_location` VARCHAR(255) DEFAULT NULL,
  `max_participants` INT DEFAULT NULL,
  `is_recurring` TINYINT(1) DEFAULT 0,
  `recurrence_pattern` VARCHAR(50) DEFAULT NULL,
  `status` ENUM('scheduled','completed','cancelled') DEFAULT 'scheduled',
  `created_by` INT DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`group_session_id`),
  KEY `idx_group_datetime` (`group_id`,`session_datetime`),
  KEY `idx_created_by` (`created_by`),
  CONSTRAINT `fk_sgs_group` FOREIGN KEY (`group_id`) REFERENCES `support_groups` (`group_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_sgs_admin` FOREIGN KEY (`created_by`) REFERENCES `admin` (`admin_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `support_group_session_registrations` (
  `registration_id` INT NOT NULL AUTO_INCREMENT,
  `group_session_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `registered_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`registration_id`),
  UNIQUE KEY `idx_session_user` (`group_session_id`,`user_id`),
  KEY `fk_sgsr_session` (`group_session_id`),
  KEY `fk_sgsr_user` (`user_id`),
  CONSTRAINT `fk_sgsr_session` FOREIGN KEY (`group_session_id`) REFERENCES `support_group_sessions` (`group_session_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_sgsr_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
