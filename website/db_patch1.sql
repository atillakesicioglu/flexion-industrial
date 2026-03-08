-- =====================================================
-- Flexion Industrial — DB Patch 1
-- Bu dosyayı PHPMyAdmin'den import edin.
-- Mevcut verilere dokunmaz, sadece eksik yapıları ekler.
-- =====================================================

-- ---- pages tablosuna banner stil kolonları ----
ALTER TABLE `pages`
    ADD COLUMN IF NOT EXISTS `banner_opacity`        TINYINT      NOT NULL DEFAULT 50       AFTER `banner_title`,
    ADD COLUMN IF NOT EXISTS `banner_blur`           TINYINT      NOT NULL DEFAULT 0        AFTER `banner_opacity`,
    ADD COLUMN IF NOT EXISTS `banner_title_color`    VARCHAR(20)  NOT NULL DEFAULT '#ffffff' AFTER `banner_blur`,
    ADD COLUMN IF NOT EXISTS `banner_title_size`     VARCHAR(10)  NOT NULL DEFAULT '2rem'   AFTER `banner_title_color`,
    ADD COLUMN IF NOT EXISTS `banner_title_position` VARCHAR(10)  NOT NULL DEFAULT 'center' AFTER `banner_title_size`;

-- ---- contact_submissions tablosu ----
CREATE TABLE IF NOT EXISTS `contact_submissions` (
    `id`         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `type`       VARCHAR(30)   NOT NULL DEFAULT 'contact',
    `product_id` INT UNSIGNED  NULL     DEFAULT NULL,
    `name`       VARCHAR(200)  NOT NULL DEFAULT '',
    `email`      VARCHAR(200)  NOT NULL DEFAULT '',
    `phone`      VARCHAR(50)   NULL     DEFAULT NULL,
    `company`    VARCHAR(200)  NULL     DEFAULT NULL,
    `country`    VARCHAR(100)  NULL     DEFAULT NULL,
    `message`    TEXT          NOT NULL,
    `is_read`    TINYINT(1)    NOT NULL DEFAULT 0,
    `created_at` DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_type` (`type`),
    KEY `idx_product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---- Yeni ayarlar (news banner + iletişim + header) ----
INSERT IGNORE INTO `settings` (`setting_key`, `setting_value`) VALUES
    ('news_banner_opacity',        '50'),
    ('news_banner_blur',           '0'),
    ('news_banner_title_color',    '#ffffff'),
    ('news_banner_title_size',     '2rem'),
    ('news_banner_title_position', 'center'),
    ('google_maps_embed',          ''),
    ('show_header_title',          '1');
