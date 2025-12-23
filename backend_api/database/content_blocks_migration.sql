-- Content Blocks Migration
-- Bảng lưu trữ content blocks động cho các trang

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

-- Thêm cột tracking vào content_pages nếu chưa có
-- Chạy từng lệnh ALTER riêng để tránh lỗi nếu cột đã tồn tại
