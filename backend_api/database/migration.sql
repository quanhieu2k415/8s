-- ICOGroup Database Migration
-- Run this SQL in phpMyAdmin to create the necessary tables

-- Bảng tin tức
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

-- Bảng thống kê
CREATE TABLE IF NOT EXISTS statistics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    stat_key VARCHAR(100) UNIQUE NOT NULL,
    stat_value INT DEFAULT 0,
    stat_label VARCHAR(255),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default statistics
INSERT INTO statistics (stat_key, stat_value, stat_label) VALUES
('du_hoc_sinh', 17000, 'Du học sinh'),
('lao_dong', 38000, 'Lao động quốc tế'),
('doi_tac', 600, 'Đối tác doanh nghiệp'),
('truong_lien_ket', 300, 'Trường liên kết')
ON DUPLICATE KEY UPDATE stat_value = VALUES(stat_value);

-- Insert sample news
INSERT INTO news (title, slug, excerpt, image_url, category, is_featured) VALUES
('ICOGroup tổ chức kỷ niệm 43 năm Ngày Nhà giáo Việt Nam', 'icogroup-ky-niem-43-nam-ngay-nha-giao', 'ICOGroup tổ chức lễ kỷ niệm 43 năm Ngày Nhà giáo Việt Nam và khai trương Trung tâm Đào tạo lái xe ICO.', 'https://icogroup.vn/vnt_upload/news/11_2024/43_NAM_NGAY_NHA_GIAO_VN_1.jpg', 'su-kien', TRUE),
('Trường Đại học Pukyong Hàn Quốc làm việc tại ICOGroup', 'truong-pukyong-lam-viec-tai-icogroup', 'Trường Đại học Quốc gia Pukyong (Hàn Quốc) đến thăm và làm việc tại trụ sở ICOGroup.', 'https://icogroup.vn/vnt_upload/news/11_2024/TRUONG_DAI_HOC_PUKYONG.jpg', 'tin-tuc', TRUE),
('ICOGroup tiếp đón Tập đoàn Kaisei Nhật Bản', 'icogroup-tiep-don-kaisei', 'ICOGroup tiếp đón và làm việc với Tập đoàn Giáo dục Kaisei (Nhật Bản).', 'https://icogroup.vn/vnt_upload/news/11_2024/KAISEI_1.jpg', 'tin-tuc', FALSE);

-- Thêm cột ngày nhận nếu chưa có trong bảng user
ALTER TABLE user ADD COLUMN IF NOT EXISTS ngay_nhan TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE user ADD COLUMN IF NOT EXISTS ghi_chu TEXT;
