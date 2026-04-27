CREATE DATABASE IF NOT EXISTS adconnect CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE adconnect;

CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(80) NOT NULL,
    last_name VARCHAR(80) NOT NULL,
    display_name VARCHAR(180) DEFAULT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('client', 'business', 'admin') NOT NULL DEFAULT 'client',
    status ENUM('pending', 'active', 'suspended', 'verified') NOT NULL DEFAULT 'pending',
    phone_number VARCHAR(30) DEFAULT NULL,
    notify_email TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_users_role_status (role, status)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    slug VARCHAR(120) NOT NULL UNIQUE,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS business_profiles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL UNIQUE,
    category_id INT UNSIGNED DEFAULT NULL,
    business_name VARCHAR(180) NOT NULL,
    city VARCHAR(120) DEFAULT NULL,
    budget_tier ENUM('low', 'mid', 'high') NOT NULL DEFAULT 'mid',
    description TEXT,
    contact_email VARCHAR(190) DEFAULT NULL,
    contact_phone VARCHAR(40) DEFAULT NULL,
    rating DECIMAL(3, 2) NOT NULL DEFAULT 0.00,
    approval_status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    is_verified TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_business_profiles_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_business_profiles_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_business_profiles_city (city),
    INDEX idx_business_profiles_status (approval_status, is_verified)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS business_specialties (
    business_id BIGINT UNSIGNED NOT NULL,
    specialty VARCHAR(120) NOT NULL,
    PRIMARY KEY (business_id, specialty),
    CONSTRAINT fk_business_specialties_business FOREIGN KEY (business_id) REFERENCES business_profiles(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS campaigns (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    business_id BIGINT UNSIGNED NOT NULL,
    owner_name VARCHAR(120) DEFAULT NULL,
    name VARCHAR(180) NOT NULL,
    objective ENUM('awareness', 'engagement', 'sales', 'leads') NOT NULL DEFAULT 'awareness',
    status ENUM('planned', 'live', 'paused', 'archived') NOT NULL DEFAULT 'planned',
    budget_amount DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    start_date DATE DEFAULT NULL,
    end_date DATE DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_campaigns_business FOREIGN KEY (business_id) REFERENCES business_profiles(id) ON DELETE CASCADE,
    INDEX idx_campaigns_business_status (business_id, status),
    INDEX idx_campaigns_dates (start_date, end_date)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS ads (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    campaign_id BIGINT UNSIGNED NOT NULL,
    business_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(180) NOT NULL,
    channel ENUM('social', 'search', 'video', 'events') NOT NULL,
    location VARCHAR(120) DEFAULT NULL,
    status ENUM('planned', 'live', 'paused', 'rejected') NOT NULL DEFAULT 'planned',
    objective ENUM('awareness', 'engagement', 'sales', 'leads') NOT NULL DEFAULT 'awareness',
    budget_amount DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    description TEXT,
    moderation_notes TEXT,
    published_at DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_ads_campaign FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE CASCADE,
    CONSTRAINT fk_ads_business FOREIGN KEY (business_id) REFERENCES business_profiles(id) ON DELETE CASCADE,
    INDEX idx_ads_feed (status, channel, location),
    INDEX idx_ads_business (business_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS inquiries (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_user_id BIGINT UNSIGNED NOT NULL,
    business_id BIGINT UNSIGNED NOT NULL,
    campaign_need VARCHAR(200) NOT NULL,
    budget_amount DECIMAL(12, 2) DEFAULT NULL,
    status ENUM('pending', 'replied', 'scheduled', 'closed') NOT NULL DEFAULT 'pending',
    latest_subject VARCHAR(180) DEFAULT NULL,
    latest_message TEXT,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_inquiries_client FOREIGN KEY (client_user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_inquiries_business FOREIGN KEY (business_id) REFERENCES business_profiles(id) ON DELETE CASCADE,
    INDEX idx_inquiries_business_status (business_id, status),
    INDEX idx_inquiries_client_status (client_user_id, status)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS messages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    inquiry_id BIGINT UNSIGNED NOT NULL,
    sender_user_id BIGINT UNSIGNED NOT NULL,
    recipient_user_id BIGINT UNSIGNED NOT NULL,
    subject VARCHAR(180) NOT NULL,
    body TEXT NOT NULL,
    message_status ENUM('open', 'pending', 'reviewed', 'read') NOT NULL DEFAULT 'open',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    read_at DATETIME DEFAULT NULL,
    CONSTRAINT fk_messages_inquiry FOREIGN KEY (inquiry_id) REFERENCES inquiries(id) ON DELETE CASCADE,
    CONSTRAINT fk_messages_sender FOREIGN KEY (sender_user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_messages_recipient FOREIGN KEY (recipient_user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_messages_recipient_created (recipient_user_id, created_at),
    INDEX idx_messages_inquiry (inquiry_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS favorites (
    client_user_id BIGINT UNSIGNED NOT NULL,
    business_id BIGINT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (client_user_id, business_id),
    CONSTRAINT fk_favorites_client FOREIGN KEY (client_user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_favorites_business FOREIGN KEY (business_id) REFERENCES business_profiles(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS reviews (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    business_id BIGINT UNSIGNED NOT NULL,
    client_user_id BIGINT UNSIGNED NOT NULL,
    rating TINYINT UNSIGNED NOT NULL,
    comment TEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_reviews_business FOREIGN KEY (business_id) REFERENCES business_profiles(id) ON DELETE CASCADE,
    CONSTRAINT fk_reviews_client FOREIGN KEY (client_user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT chk_reviews_rating CHECK (rating BETWEEN 1 AND 5),
    INDEX idx_reviews_business_created (business_id, created_at)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS reports (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reference_code VARCHAR(40) NOT NULL UNIQUE,
    issue_type VARCHAR(140) NOT NULL,
    reported_by_user_id BIGINT UNSIGNED DEFAULT NULL,
    status ENUM('open', 'investigating', 'resolved', 'dismissed') NOT NULL DEFAULT 'open',
    notes TEXT,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_reports_reported_by FOREIGN KEY (reported_by_user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_reports_status (status)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED DEFAULT NULL,
    audience_role ENUM('client', 'business', 'admin', 'all') NOT NULL DEFAULT 'all',
    message VARCHAR(255) NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_notifications_user (user_id, is_read, created_at),
    INDEX idx_notifications_role (audience_role, created_at)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS campaign_analytics_daily (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    campaign_id BIGINT UNSIGNED NOT NULL,
    report_date DATE NOT NULL,
    impressions INT UNSIGNED NOT NULL DEFAULT 0,
    clicks INT UNSIGNED NOT NULL DEFAULT 0,
    leads INT UNSIGNED NOT NULL DEFAULT 0,
    spend_amount DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    CONSTRAINT fk_campaign_analytics_campaign FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE CASCADE,
    UNIQUE KEY uq_campaign_report_date (campaign_id, report_date),
    INDEX idx_campaign_analytics_date (report_date)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS support_requests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED DEFAULT NULL,
    name VARCHAR(180) NOT NULL,
    email VARCHAR(190) NOT NULL,
    topic VARCHAR(80) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('open', 'in_progress', 'resolved') NOT NULL DEFAULT 'open',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_support_requests_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_support_requests_status (status)
) ENGINE=InnoDB;

UPDATE campaigns
SET status = 'planned'
WHERE status = 'review';

UPDATE ads
SET status = 'live'
WHERE status = 'review';

ALTER TABLE campaigns
MODIFY status ENUM('planned', 'live', 'paused', 'archived') NOT NULL DEFAULT 'planned';

ALTER TABLE ads
MODIFY status ENUM('planned', 'live', 'paused', 'rejected') NOT NULL DEFAULT 'planned';

INSERT IGNORE INTO users (
    first_name,
    last_name,
    display_name,
    email,
    password_hash,
    role,
    status,
    phone_number,
    notify_email
) VALUES (
    'System',
    'Administrator',
    'Admin',
    'admin@adconnect.local',
    '$2y$10$B8QyeU4Ub9ja0u3JMAH/QuCEBzG2Is20O5D7WmGDQMjb5zbhAlGH.',
    'admin',
    'active',
    '09170000000',
    1
);
