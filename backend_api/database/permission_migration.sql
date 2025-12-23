-- Permission System Migration for ICOGroup
-- Run this SQL in phpMyAdmin to add permission tables

-- ===========================================
-- 1. Bảng permissions - Định nghĩa các quyền
-- ===========================================
CREATE TABLE IF NOT EXISTS permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    permission_key VARCHAR(100) UNIQUE NOT NULL,
    permission_name VARCHAR(255) NOT NULL,
    description TEXT,
    category VARCHAR(50) DEFAULT 'general',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===========================================
-- 2. Bảng role_permissions - Liên kết role với permissions
-- ===========================================
CREATE TABLE IF NOT EXISTS role_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role ENUM('admin', 'manager', 'user') NOT NULL,
    permission_key VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_role_permission (role, permission_key),
    FOREIGN KEY (permission_key) REFERENCES permissions(permission_key) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===========================================
-- 3. Bảng activity_logs - Ghi log hoạt động
-- ===========================================
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

-- ===========================================
-- 4. Cập nhật bảng admin_users
-- ===========================================
-- Thêm cột department nếu chưa có
ALTER TABLE admin_users ADD COLUMN IF NOT EXISTS department VARCHAR(100) DEFAULT NULL;

-- Thêm cột manager_id để liên kết với manager
ALTER TABLE admin_users ADD COLUMN IF NOT EXISTS manager_id INT DEFAULT NULL;

-- Thêm cột profile_updated_at
ALTER TABLE admin_users ADD COLUMN IF NOT EXISTS profile_updated_at TIMESTAMP NULL;

-- Cập nhật ENUM role (sẽ chuyển 'editor' thành 'user')
-- Bước 1: Đổi editor thành user trước
UPDATE admin_users SET role = 'admin' WHERE role = 'editor';

-- Bước 2: Thay đổi ENUM
ALTER TABLE admin_users MODIFY COLUMN role ENUM('admin', 'manager', 'user') NOT NULL DEFAULT 'user';

-- ===========================================
-- 5. Seed permissions mặc định
-- ===========================================
INSERT INTO permissions (permission_key, permission_name, description, category) VALUES
-- Users Management
('users.view_all', 'Xem tất cả users', 'Xem danh sách tất cả tài khoản trong hệ thống', 'users'),
('users.view_team', 'Xem users trong team', 'Xem danh sách users được gán quản lý', 'users'),
('users.create_admin', 'Tạo tài khoản Admin', 'Tạo tài khoản với quyền Admin', 'users'),
('users.create_manager', 'Tạo tài khoản Manager', 'Tạo tài khoản với quyền Manager', 'users'),
('users.create_user', 'Tạo tài khoản User', 'Tạo tài khoản với quyền User', 'users'),
('users.edit_all', 'Sửa tất cả users', 'Chỉnh sửa thông tin mọi tài khoản', 'users'),
('users.edit_team', 'Sửa users trong team', 'Chỉnh sửa thông tin users được gán quản lý', 'users'),
('users.delete', 'Xóa tài khoản', 'Xóa tài khoản khỏi hệ thống', 'users'),
('users.assign_manager', 'Gán Manager', 'Gán manager cho users', 'users'),

-- Settings
('settings.view', 'Xem cấu hình', 'Xem các cấu hình hệ thống', 'settings'),
('settings.modify', 'Thay đổi cấu hình', 'Thay đổi cấu hình hệ thống', 'settings'),

-- Reports
('reports.view_all', 'Xem tất cả báo cáo', 'Xem báo cáo toàn hệ thống', 'reports'),
('reports.view_team', 'Xem báo cáo team', 'Xem báo cáo của team được quản lý', 'reports'),
('reports.view_personal', 'Xem báo cáo cá nhân', 'Xem báo cáo của bản thân', 'reports'),
('reports.export', 'Xuất báo cáo', 'Xuất báo cáo ra file', 'reports'),

-- Logs
('logs.view_all', 'Xem tất cả logs', 'Xem activity logs toàn hệ thống', 'logs'),
('logs.view_team', 'Xem logs team', 'Xem activity logs của team', 'logs'),

-- Content Management
('content.manage_all', 'Quản lý tất cả nội dung', 'Quản lý nội dung toàn website', 'content'),
('content.manage_assigned', 'Quản lý nội dung được gán', 'Quản lý nội dung trong phạm vi được gán', 'content'),
('content.view', 'Xem nội dung', 'Xem nội dung website', 'content'),

-- News
('news.create', 'Tạo tin tức', 'Tạo bài viết tin tức mới', 'news'),
('news.edit_all', 'Sửa tất cả tin tức', 'Sửa mọi bài viết tin tức', 'news'),
('news.edit_own', 'Sửa tin tức của mình', 'Sửa bài viết do mình tạo', 'news'),
('news.delete', 'Xóa tin tức', 'Xóa bài viết tin tức', 'news'),
('news.publish', 'Đăng tin tức', 'Đăng/gỡ bài viết tin tức', 'news'),

-- Registrations
('registrations.view_all', 'Xem tất cả đăng ký', 'Xem tất cả đăng ký tư vấn', 'registrations'),
('registrations.view_assigned', 'Xem đăng ký được gán', 'Xem đăng ký trong phạm vi được gán', 'registrations'),
('registrations.edit', 'Sửa đăng ký', 'Chỉnh sửa thông tin đăng ký', 'registrations'),
('registrations.delete', 'Xóa đăng ký', 'Xóa đăng ký khỏi hệ thống', 'registrations'),
('registrations.export', 'Xuất đăng ký', 'Xuất danh sách đăng ký ra file', 'registrations'),

-- CMS
('cms.manage', 'Quản lý CMS', 'Quản lý nội dung CMS website', 'cms'),
('cms.images', 'Quản lý hình ảnh', 'Upload và quản lý hình ảnh', 'cms'),
('cms.texts', 'Quản lý văn bản', 'Chỉnh sửa văn bản trên website', 'cms'),

-- Content Blocks
('content_blocks.view', 'Xem Content Blocks', 'Xem danh sách content blocks', 'content_blocks'),
('content_blocks.manage', 'Quản lý Content Blocks', 'Tạo, sửa, xóa content blocks', 'content_blocks'),

-- Profile
('profile.edit_own', 'Sửa thông tin cá nhân', 'Chỉnh sửa thông tin cá nhân của mình', 'profile'),
('profile.change_password', 'Đổi mật khẩu', 'Đổi mật khẩu tài khoản của mình', 'profile'),

-- Database
('database.backup', 'Backup database', 'Tạo bản backup database', 'database'),
('database.restore', 'Restore database', 'Khôi phục database từ backup', 'database')
ON DUPLICATE KEY UPDATE permission_name = VALUES(permission_name);

-- ===========================================
-- 6. Gán permissions cho các role
-- ===========================================

-- Admin - Tất cả quyền
INSERT INTO role_permissions (role, permission_key)
SELECT 'admin', permission_key FROM permissions
ON DUPLICATE KEY UPDATE role = role;

-- Manager - Quyền giới hạn
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

-- User - Quyền hạn chế nhất
INSERT INTO role_permissions (role, permission_key) VALUES
('user', 'reports.view_personal'),
('user', 'content.view'),
('user', 'news.edit_own'),
('user', 'registrations.view_assigned'),
('user', 'profile.edit_own'),
('user', 'profile.change_password')
ON DUPLICATE KEY UPDATE role = role;

-- ===========================================
-- 7. Tạo tài khoản mẫu (optional)
-- ===========================================
-- Password: manager123 (hash này cần được generate bởi PHP)
-- INSERT INTO admin_users (username, email, password_hash, role, is_active) VALUES
-- ('manager', 'manager@icogroup.vn', '$argon2id$...', 'manager', 1);

-- Password: user123
-- INSERT INTO admin_users (username, email, password_hash, role, is_active) VALUES
-- ('user1', 'user1@icogroup.vn', '$argon2id$...', 'user', 1);
