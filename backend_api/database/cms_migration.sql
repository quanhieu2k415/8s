-- CMS Migration for ICOGroup Website
-- Quản lý hình ảnh và văn bản từ Admin Panel

-- Bảng quản lý hình ảnh
CREATE TABLE IF NOT EXISTS site_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    image_key VARCHAR(100) UNIQUE NOT NULL,
    image_url VARCHAR(500) NOT NULL,
    alt_text VARCHAR(255) DEFAULT '',
    section VARCHAR(50) DEFAULT 'general',
    page VARCHAR(50) DEFAULT 'global',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bảng quản lý văn bản
CREATE TABLE IF NOT EXISTS site_texts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    text_key VARCHAR(100) UNIQUE NOT NULL,
    text_value TEXT,
    text_type ENUM('title', 'subtitle', 'paragraph', 'button', 'label', 'list') DEFAULT 'paragraph',
    section VARCHAR(50) DEFAULT 'general',
    page VARCHAR(50) DEFAULT 'global',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default global content
INSERT INTO site_images (image_key, image_url, alt_text, section, page) VALUES
('logo', 'https://www.icogroup.vn/vnt_upload/company/Logo_icogroup4x.png', 'ICOGroup Logo', 'header', 'global'),
('logo_mobile', 'https://www.icogroup.vn/vnt_upload/company/Logo_icogroup4x.png', 'ICOGroup Logo', 'header', 'global')
ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP;

INSERT INTO site_texts (text_key, text_value, text_type, section, page) VALUES
('topbar_phone', '0822.314.555', 'label', 'header', 'global'),
('topbar_email', 'info@icogroup.vn', 'label', 'header', 'global'),
('facebook_url', 'https://facebook.com/icogroup', 'label', 'social', 'global'),
('youtube_url', 'https://youtube.com/icogroup', 'label', 'social', 'global'),
('zalo_url', 'https://zalo.me/icogroup', 'label', 'social', 'global'),
('footer_address', 'Số 360, đường Phan Đình Phùng, tỉnh Thái Nguyên', 'paragraph', 'footer', 'global'),
('footer_phone', '0822.314.555', 'label', 'footer', 'global'),
('footer_email', 'info@icogroup.vn', 'label', 'footer', 'global')
ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP;

-- Insert trang chủ content
INSERT INTO site_images (image_key, image_url, alt_text, section, page) VALUES
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
