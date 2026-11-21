-- WISE School Management System: Phase 2 foundation
-- Safe to import into the existing shared WISE hosting database. All WISE tables use the wise_ prefix; existing application tables are not modified. InnoDB + utf8mb4.
SET NAMES utf8mb4;
SET time_zone = '+00:00';

CREATE TABLE IF NOT EXISTS wise_users (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  google_sub VARCHAR(255) NULL,
  email VARCHAR(254) NOT NULL,
  email_verified TINYINT(1) NOT NULL DEFAULT 0,
  display_name VARCHAR(160) NOT NULL,
  status ENUM('pending','active','suspended','disabled') NOT NULL DEFAULT 'pending',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  last_login_at DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_google_sub (google_sub),
  KEY idx_users_email (email),
  KEY idx_users_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS wise_school_people (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  person_type ENUM('student','staff','parent','alumni') NOT NULL,
  full_name VARCHAR(160) NOT NULL,
  school_reference VARCHAR(80) NOT NULL,
  expected_email VARCHAR(254) NULL,
  status ENUM('pending','active','inactive','suspended') NOT NULL DEFAULT 'pending',
  linked_user_id BIGINT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_school_reference_type (person_type, school_reference),
  UNIQUE KEY uq_school_person_user (linked_user_id),
  KEY idx_school_people_email (expected_email),
  KEY idx_school_people_status (status),
  CONSTRAINT wise_fk_school_people_user FOREIGN KEY (linked_user_id) REFERENCES wise_users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS wise_roles (
  id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  role_key VARCHAR(40) NOT NULL,
  role_name VARCHAR(80) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_roles_key (role_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO wise_roles (role_key, role_name) VALUES
('super_admin','Super Administrator'),
('school_admin','School Administrator'),
('teacher','Teacher'),
('non_teaching_staff','Non-teaching Staff'),
('student','Student'),
('parent','Parent / Guardian'),
('alumni','Alumni');

CREATE TABLE IF NOT EXISTS wise_user_roles (
  user_id BIGINT UNSIGNED NOT NULL,
  role_id SMALLINT UNSIGNED NOT NULL,
  assigned_by_user_id BIGINT UNSIGNED NULL,
  assigned_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id, role_id),
  CONSTRAINT wise_fk_user_roles_user FOREIGN KEY (user_id) REFERENCES wise_users(id) ON DELETE CASCADE,
  CONSTRAINT wise_fk_user_roles_role FOREIGN KEY (role_id) REFERENCES wise_roles(id),
  CONSTRAINT wise_fk_user_roles_assigner FOREIGN KEY (assigned_by_user_id) REFERENCES wise_users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS wise_verification_codes (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  code_hash CHAR(64) NOT NULL,
  category ENUM('student','parent','teacher','non_teaching_staff','administrator','alumni') NOT NULL,
  target_person_id BIGINT UNSIGNED NOT NULL,
  expected_google_email VARCHAR(254) NULL,
  expires_at DATETIME NOT NULL,
  max_attempts TINYINT UNSIGNED NOT NULL DEFAULT 5,
  attempt_count TINYINT UNSIGNED NOT NULL DEFAULT 0,
  issued_by_user_id BIGINT UNSIGNED NOT NULL,
  issued_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  redeemed_at DATETIME NULL,
  revoked_at DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_verification_code_hash (code_hash),
  KEY idx_verification_target (target_person_id, category),
  KEY idx_verification_expiry (expires_at),
  CONSTRAINT wise_fk_verification_target FOREIGN KEY (target_person_id) REFERENCES wise_school_people(id),
  CONSTRAINT wise_fk_verification_issuer FOREIGN KEY (issued_by_user_id) REFERENCES wise_users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS wise_audit_logs (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  actor_user_id BIGINT UNSIGNED NULL,
  event_type VARCHAR(80) NOT NULL,
  target_type VARCHAR(80) NULL,
  target_id BIGINT UNSIGNED NULL,
  details_json JSON NULL,
  ip_address VARCHAR(45) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_audit_actor_time (actor_user_id, created_at),
  KEY idx_audit_event_time (event_type, created_at),
  CONSTRAINT wise_fk_audit_actor FOREIGN KEY (actor_user_id) REFERENCES wise_users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Parent-child links are deliberately approval-based, not inferred from admission number.
CREATE TABLE IF NOT EXISTS wise_parent_student_links (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  parent_person_id BIGINT UNSIGNED NOT NULL,
  student_person_id BIGINT UNSIGNED NOT NULL,
  relationship_type VARCHAR(40) NOT NULL,
  status ENUM('pending','approved','rejected','revoked') NOT NULL DEFAULT 'pending',
  approved_by_user_id BIGINT UNSIGNED NULL,
  approved_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_parent_student_relationship (parent_person_id, student_person_id),
  CONSTRAINT wise_fk_psl_parent FOREIGN KEY (parent_person_id) REFERENCES wise_school_people(id),
  CONSTRAINT wise_fk_psl_student FOREIGN KEY (student_person_id) REFERENCES wise_school_people(id),
  CONSTRAINT wise_fk_psl_approver FOREIGN KEY (approved_by_user_id) REFERENCES wise_users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
