-- FULL DATABASE MIGRATION FOR ICOGROUP WEBSITE
-- Run this single file to set up the entire database structure and dependencies.
-- Order: admin_users -> remember_tokens -> single_session -> migration -> cms -> permission -> content_blocks

-- ==========================================
-- 1. ADMIN USERS (Core Table)
-- ==========================================
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(255) DEFAULT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'manager', 'user') NOT NULL DEFAULT 'user',
    department VARCHAR(100) DEFAULT NULL,
    manager_id INT DEFAULT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    last_login TIMESTAMP NULL DEFAULT NULL,
    login_attempts INT NOT NULL DEFAULT 0,
    locked_until TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    profile_updated_at TIMESTAMP NULL DEFAULT NULL,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_is_active (is_active),
    INDEX idx_manager_id (manager_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- 2. REMEMBER TOKENS (Depends on admin_users)
-- ==========================================
CREATE TABLE IF NOT EXISTS remember_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token_hash VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES admin_users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_remember_tokens_user_id ON remember_tokens(user_id);
CREATE INDEX idx_remember_tokens_token_hash ON remember_tokens(token_hash);
CREATE INDEX idx_remember_tokens_expires_at ON remember_tokens(expires_at);

-- ==========================================
-- 3. SINGLE SESSION (Alters admin_users)
-- ==========================================
-- Add session_token column if not exists (handling via stored procedure or silent fail in simple scripts)
-- Since SQL scripts don't have easy "IF COL EXISTS", we use a block or just error suppression in manual run.
-- However, for a fresh install, we can just ALTER.
-- If re-running, this might error. We'll use a safer approach if possible, but standard SQL ALTER is usually fine for fresh setup.

-- Attempt to add session_token. If it fails (exists), it's fine in many tools, or we skip check.
-- For standard MySQL import, we can't do conditional ALTER easily without procedures.
-- We will assume fresh install or ignore error.
ALTER TABLE admin_users ADD COLUMN session_token VARCHAR(64) NULL;
ALTER TABLE admin_users ADD INDEX idx_session_token (session_token);

-- ==========================================
-- 4. PERMISSIONS & ROLES (Depends on admin_users)
-- ==========================================
CREATE TABLE IF NOT EXISTS permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    permission_key VARCHAR(100) UNIQUE NOT NULL,
    permission_name VARCHAR(255) NOT NULL,
    description TEXT,
    category VARCHAR(50) DEFAULT 'general',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS role_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role ENUM('admin', 'manager', 'user') NOT NULL,
    permission_key VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_role_permission (role, permission_key),
    FOREIGN KEY (permission_key) REFERENCES permissions(permission_key) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    username VARCHAR(100),
    role VARCHAR(20),
    action VARCHAR(100) NOT NULL,
    target_type VARCHAR(50),
    target_id INT,
    details TEXT,
    ip_address VARCHAR(45),
    user_agent VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at),
    INDEX idx_target (target_type, target_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed Permissions
INSERT INTO permissions (permission_key, permission_name, description, category) VALUES
('users.view_all', 'Xem tất cả users', 'Xem danh sách tất cả tài khoản trong hệ thống', 'users'),
('users.view_team', 'Xem users trong team', 'Xem danh sách users được gán quản lý', 'users'),
('users.create_admin', 'Tạo tài khoản Admin', 'Tạo tài khoản với quyền Admin', 'users'),
('users.create_manager', 'Tạo tài khoản Manager', 'Tạo tài khoản với quyền Manager', 'users'),
('users.create_user', 'Tạo tài khoản User', 'Tạo tài khoản với quyền User', 'users'),
('users.edit_all', 'Sửa tất cả users', 'Chỉnh sửa thông tin mọi tài khoản', 'users'),
('users.edit_team', 'Sửa users trong team', 'Chỉnh sửa thông tin users được gán quản lý', 'users'),
('users.delete', 'Xóa tài khoản', 'Xóa tài khoản khỏi hệ thống', 'users'),
('users.assign_manager', 'Gán Manager', 'Gán manager cho users', 'users'),
('settings.view', 'Xem cấu hình', 'Xem các cấu hình hệ thống', 'settings'),
('settings.modify', 'Thay đổi cấu hình', 'Thay đổi cấu hình hệ thống', 'settings'),
('reports.view_all', 'Xem tất cả báo cáo', 'Xem báo cáo toàn hệ thống', 'reports'),
('reports.view_team', 'Xem báo cáo team', 'Xem báo cáo của team được quản lý', 'reports'),
('reports.view_personal', 'Xem báo cáo cá nhân', 'Xem báo cáo của bản thân', 'reports'),
('reports.export', 'Xuất báo cáo', 'Xuất báo cáo ra file', 'reports'),
('logs.view_all', 'Xem tất cả logs', 'Xem activity logs toàn hệ thống', 'logs'),
('logs.view_team', 'Xem logs team', 'Xem activity logs của team', 'logs'),
('content.manage_all', 'Quản lý tất cả nội dung', 'Quản lý nội dung toàn website', 'content'),
('content.manage_assigned', 'Quản lý nội dung được gán', 'Quản lý nội dung trong phạm vi được gán', 'content'),
('content.view', 'Xem nội dung', 'Xem nội dung website', 'content'),
('news.create', 'Tạo tin tức', 'Tạo bài viết tin tức mới', 'news'),
('news.edit_all', 'Sửa tất cả tin tức', 'Sửa mọi bài viết tin tức', 'news'),
('news.edit_own', 'Sửa tin tức của mình', 'Sửa bài viết do mình tạo', 'news'),
('news.delete', 'Xóa tin tức', 'Xóa bài viết tin tức', 'news'),
('news.publish', 'Đăng tin tức', 'Đăng/gỡ bài viết tin tức', 'news'),
('registrations.view_all', 'Xem tất cả đăng ký', 'Xem tất cả đăng ký tư vấn', 'registrations'),
('registrations.view_assigned', 'Xem đăng ký được gán', 'Xem đăng ký trong phạm vi được gán', 'registrations'),
('registrations.edit', 'Sửa đăng ký', 'Chỉnh sửa thông tin đăng ký', 'registrations'),
('registrations.delete', 'Xóa đăng ký', 'Xóa đăng ký khỏi hệ thống', 'registrations'),
('registrations.export', 'Xuất đăng ký', 'Xuất danh sách đăng ký ra file', 'registrations'),
('cms.manage', 'Quản lý CMS', 'Quản lý nội dung CMS website', 'cms'),
('cms.images', 'Quản lý hình ảnh', 'Upload và quản lý hình ảnh', 'cms'),
('cms.texts', 'Quản lý văn bản', 'Chỉnh sửa văn bản trên website', 'cms'),
('content_blocks.view', 'Xem Content Blocks', 'Xem danh sách content blocks', 'content_blocks'),
('content_blocks.manage', 'Quản lý Content Blocks', 'Tạo, sửa, xóa content blocks', 'content_blocks'),
('profile.edit_own', 'Sửa thông tin cá nhân', 'Chỉnh sửa thông tin cá nhân của mình', 'profile'),
('profile.change_password', 'Đổi mật khẩu', 'Đổi mật khẩu tài khoản của mình', 'profile'),
('database.backup', 'Backup database', 'Tạo bản backup database', 'database'),
('database.restore', 'Restore database', 'Khôi phục database từ backup', 'database')
ON DUPLICATE KEY UPDATE permission_name = VALUES(permission_name);

-- Seed Role Permissions
-- Admin
INSERT INTO role_permissions (role, permission_key)
SELECT 'admin', permission_key FROM permissions
ON DUPLICATE KEY UPDATE role = role;

-- Manager
INSERT INTO role_permissions (role, permission_key) VALUES
('manager', 'users.view_team'),
('manager', 'users.create_user'),
('manager', 'users.edit_team'),
('manager', 'reports.view_team'),
('manager', 'reports.view_personal'),
('manager', 'reports.export'),
('manager', 'logs.view_team'),
('manager', 'content.manage_assigned'),
('manager', 'content.view'),
('manager', 'news.create'),
('manager', 'news.edit_own'),
('manager', 'news.publish'),
('manager', 'registrations.view_assigned'),
('manager', 'registrations.edit'),
('manager', 'registrations.export'),
('manager', 'cms.manage'),
('manager', 'cms.images'),
('manager', 'cms.texts'),
('manager', 'content_blocks.view'),
('manager', 'content_blocks.manage'),
('manager', 'profile.edit_own'),
('manager', 'profile.change_password')
ON DUPLICATE KEY UPDATE role = role;

-- User
INSERT INTO role_permissions (role, permission_key) VALUES
('user', 'reports.view_personal'),
('user', 'content.view'),
('user', 'news.edit_own'),
('user', 'registrations.view_assigned'),
('user', 'profile.edit_own'),
('user', 'profile.change_password')
ON DUPLICATE KEY UPDATE role = role;

-- ==========================================
-- 5. CONTENT TABLES (news, statistics, CMS)
-- ==========================================
CREATE TABLE IF NOT EXISTS news (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE,
    excerpt TEXT,
    content LONGTEXT,
    image_url VARCHAR(500),
    category ENUM('tin-tuc', 'su-kien', 'thong-bao') DEFAULT 'tin-tuc',
    is_featured BOOLEAN DEFAULT FALSE,
    status ENUM('draft', 'published') DEFAULT 'published',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS statistics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    stat_key VARCHAR(100) UNIQUE NOT NULL,
    stat_value INT DEFAULT 0,
    stat_label VARCHAR(255),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO statistics (stat_key, stat_value, stat_label) VALUES
('du_hoc_sinh', 17000, 'Du học sinh'),
('lao_dong', 38000, 'Lao động quốc tế'),
('doi_tac', 600, 'Đối tác doanh nghiệp'),
('truong_lien_ket', 300, 'Trường liên kết')
ON DUPLICATE KEY UPDATE stat_value = VALUES(stat_value);

INSERT INTO news (title, slug, excerpt, image_url, category, is_featured) VALUES
('ICOGroup tổ chức kỷ niệm 43 năm Ngày Nhà giáo Việt Nam', 'icogroup-ky-niem-43-nam-ngay-nha-giao', 'ICOGroup tổ chức lễ kỷ niệm 43 năm Ngày Nhà giáo Việt Nam và khai trương Trung tâm Đào tạo lái xe ICO.', 'https://icogroup.vn/vnt_upload/news/11_2024/43_NAM_NGAY_NHA_GIAO_VN_1.jpg', 'su-kien', TRUE),
('Trường Đại học Pukyong Hàn Quốc làm việc tại ICOGroup', 'truong-pukyong-lam-viec-tai-icogroup', 'Trường Đại học Quốc gia Pukyong (Hàn Quốc) đến thăm và làm việc tại trụ sở ICOGroup.', 'https://icogroup.vn/vnt_upload/news/11_2024/TRUONG_DAI_HOC_PUKYONG.jpg', 'tin-tuc', TRUE),
('ICOGroup tiếp đón Tập đoàn Kaisei Nhật Bản', 'icogroup-tiep-don-kaisei', 'ICOGroup tiếp đón và làm việc với Tập đoàn Giáo dục Kaisei (Nhật Bản).', 'https://icogroup.vn/vnt_upload/news/11_2024/KAISEI_1.jpg', 'tin-tuc', FALSE);

-- CMS Tables
CREATE TABLE IF NOT EXISTS site_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    image_key VARCHAR(100) UNIQUE NOT NULL,
    image_url VARCHAR(500) NOT NULL,
    alt_text VARCHAR(255) DEFAULT '',
    section VARCHAR(50) DEFAULT 'general',
    page VARCHAR(50) DEFAULT 'global',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS site_texts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    text_key VARCHAR(100) UNIQUE NOT NULL,
    text_value TEXT,
    text_type ENUM('title', 'subtitle', 'paragraph', 'button', 'label', 'list') DEFAULT 'paragraph',
    section VARCHAR(50) DEFAULT 'general',
    page VARCHAR(50) DEFAULT 'global',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- CMS Seeds
INSERT INTO site_images (image_key, image_url, alt_text, section, page) VALUES
('logo', 'https://www.icogroup.vn/vnt_upload/company/Logo_icogroup4x.png', 'ICOGroup Logo', 'header', 'global'),
('logo_mobile', 'https://www.icogroup.vn/vnt_upload/company/Logo_icogroup4x.png', 'ICOGroup Logo', 'header', 'global'),
('index_hero_slide_1_img', 'https://icogroup.vn/vnt_upload/weblink/banner_trang_chu_01.jpg', 'ICOGroup - Nơi tạo dựng tương lai', 'hero', 'index'),
('index_hero_slide_2_img', 'https://icogroup.vn/vnt_upload/weblink/banner_chu_04.jpg', 'Du học quốc tế', 'hero', 'index'),
('index_hero_slide_3_img', 'https://www.icogroup.vn/vnt_upload/news/02_2025/ICOGROUP_TUYEN_DUNG_23.jpg', 'Xuất khẩu lao động', 'hero', 'index'),
('index_about_bg', 'https://icogroup.vn/vnt_upload/weblink/banner_trang_chu_01.jpg', 'About Background', 'about', 'index'),
('index_eco_1_img', 'https://icogroup.vn/vnt_upload/service/Linkedin_3.jpg', 'Trung tâm Ngoại ngữ ICO', 'ecosystem', 'index'),
('index_eco_1_logo', 'https://icogroup.vn/vnt_upload/service/Logo_TTNN_ICO_24x_100.jpg', 'Logo TTNN', 'ecosystem', 'index'),
('index_eco_2_img', 'https://icogroup.vn/vnt_upload/service/khai_giang_icoschool.jpg', 'ICOSchool', 'ecosystem', 'index'),
('index_eco_2_logo', 'https://icogroup.vn/vnt_upload/service/mmicon2.jpg', 'Logo ICOSchool', 'ecosystem', 'index'),
('index_eco_3_img', 'https://icogroup.vn/vnt_upload/service/mmimg3.jpg', 'ICOCollege', 'ecosystem', 'index'),
('index_eco_3_logo', 'https://icogroup.vn/vnt_upload/service/mmicon3.jpg', 'Logo ICOCollege', 'ecosystem', 'index'),
('index_eco_4_img', 'https://icogroup.vn/vnt_upload/service/mmimg4.jpg', 'ICOCareer', 'ecosystem', 'index'),
('index_program_1_img', 'https://cdn-images.vtv.vn/562122370168008704/2023/7/26/untitled-1690344019340844974097.png', 'Du học Nhật Bản', 'programs', 'index'),
('index_program_2_img', 'https://icogroup.vn/vnt_upload/weblink/banner_chu_04.jpg', 'Du học Đức', 'programs', 'index'),
('index_program_3_img', 'https://icogroup.vn/vnt_upload/weblink/banner_chu_04.jpg', 'XKLĐ Nhật Bản', 'programs', 'index'),
('form_bg', 'https://www.icogroup.vn/vnt_upload/news/02_2025/ICOGROUP_TUYEN_DUNG_23.jpg', 'Form Background', 'form', 'index')
ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP;

INSERT INTO site_texts (text_key, text_value, text_type, section, page) VALUES
('topbar_phone', '0822.314.555', 'label', 'header', 'global'),
('topbar_email', 'info@icogroup.vn', 'label', 'header', 'global'),
('facebook_url', 'https://facebook.com/icogroup', 'label', 'social', 'global'),
('youtube_url', 'https://youtube.com/icogroup', 'label', 'social', 'global'),
('zalo_url', 'https://zalo.me/icogroup', 'label', 'social', 'global'),
('footer_address', 'Số 360, đường Phan Đình Phùng, tỉnh Thái Nguyên', 'paragraph', 'footer', 'global'),
('footer_phone', '0822.314.555', 'label', 'footer', 'global'),
('footer_email', 'info@icogroup.vn', 'label', 'footer', 'global'),
('index_hero_slide_1_title', 'ICOGroup - Nơi Tạo Dựng Tương Lai', 'title', 'hero', 'index'),
('index_hero_slide_1_subtitle', 'Tập đoàn Giáo dục và Đào tạo nghề hàng đầu Việt Nam với hơn 15 năm kinh nghiệm', 'subtitle', 'hero', 'index'),
('index_hero_slide_2_title', 'Chương Trình Du Học Quốc Tế', 'title', 'hero', 'index'),
('index_hero_slide_2_subtitle', 'Nhật Bản • Đức • Hàn Quốc • Đài Loan', 'subtitle', 'hero', 'index'),
('index_hero_slide_3_title', 'Xuất Khẩu Lao Động Uy Tín', 'title', 'hero', 'index'),
('index_hero_slide_3_subtitle', 'Cơ hội việc làm với thu nhập cao tại nước ngoài', 'subtitle', 'hero', 'index'),
('index_about_title', 'Về ICOGroup', 'title', 'about', 'index'),
('index_about_subtitle', 'Tổ chức Giáo dục và Nhân lực Quốc tế ICO - Hơn 15 năm xây dựng và phát triển', 'subtitle', 'about', 'index'),
('index_about_history_title', 'Lịch Sử Hình Thành & Phát Triển', 'title', 'about', 'index'),
('index_about_history_desc', 'Với tầm nhìn dài hạn và quan điểm phát triển bền vững, ICOGroup đã trở thành một trong những thương hiệu uy tín về du học và xuất khẩu lao động tại Việt Nam.', 'paragraph', 'about', 'index'),
('index_mission', 'Nâng cao chất lượng nguồn nhân lực Việt Nam', 'paragraph', 'about', 'index'),
('index_vision', 'Tập đoàn phát triển nhân lực lớn nhất Việt Nam', 'paragraph', 'about', 'index'),
('index_core_values', 'Trí tuệ, Trung thực, Tận tâm', 'paragraph', 'about', 'index'),
('index_experience_badge', '🏆 Thành lập 2008 - 15+ năm kinh nghiệm', 'label', 'about', 'index'),
('stat_duhoc', '17000', 'label', 'stats', 'index'),
('stat_duhoc_label', 'Du học sinh', 'label', 'stats', 'index'),
('stat_laodong', '38000', 'label', 'stats', 'index'),
('stat_laodong_label', 'Lao động quốc tế', 'label', 'stats', 'index'),
('stat_doitac', '600', 'label', 'stats', 'index'),
('stat_doitac_label', 'Đối tác doanh nghiệp', 'label', 'stats', 'index'),
('stat_truong', '300', 'label', 'stats', 'index'),
('stat_truong_label', 'Trường liên kết', 'label', 'stats', 'index'),
('form_title', '🎯 ĐĂNG KÝ TƯ VẤN MIỄN PHÍ', 'title', 'form', 'index')
ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP;

-- ==========================================
-- 6. CONTENT_BLOCKS
-- ==========================================
CREATE TABLE IF NOT EXISTS content_blocks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    page_key VARCHAR(100) NOT NULL,
    block_order INT DEFAULT 0,
    block_type ENUM('section', 'card', 'info', 'banner') DEFAULT 'section',
    title TEXT DEFAULT NULL,
    image_url TEXT DEFAULT NULL,
    content LONGTEXT DEFAULT NULL,
    updated_by VARCHAR(100) DEFAULT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_active TINYINT(1) DEFAULT 1,
    INDEX idx_page_key (page_key),
    INDEX idx_block_order (block_order),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- 7. MISC ALTERATIONS (User table if exists)
-- ==========================================
-- For registering regular users (frontend)
-- Not to be confused with admin_users

-- Check if table 'user' exists implicitly by trying ALTER on it. 
-- Assuming 'user' table is created by a base script or exists. 
-- In older migration it was just ALTER. Here we can wrap in safety or just run it.
-- We'll assume if it errors, the user can ignore.

-- ALTER TABLE user ADD COLUMN IF NOT EXISTS ngay_nhan TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
-- ALTER TABLE user ADD COLUMN IF NOT EXISTS ghi_chu TEXT;

