-- Add CMS keys for XKLD Japan page (intro section)

INSERT INTO site_images (image_key, image_url, alt_text, section, page) VALUES
('xkldjp_intro_img', 'https://icogroup.vn/vnt_upload/weblink/banner_chu_04.jpg', 'XKLĐ Nhật Bản', 'intro', 'xkldjp')
ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP;

INSERT INTO site_texts (text_key, text_value, text_type, section, page) VALUES
('xkldjp_intro_title', 'Chương Trình Thực Tập Sinh Kỹ Năng', 'title', 'intro', 'xkldjp'),
('xkldjp_intro_desc', 'Nhật Bản là điểm đến hàng đầu của lao động Việt Nam với môi trường làm việc chuyên nghiệp, thu nhập cao và nhiều cơ hội phát triển.', 'paragraph', 'intro', 'xkldjp'),
('xkldjp_benefit_1', '💰 Thu nhập 30-40 triệu/tháng', 'label', 'intro', 'xkldjp'),
('xkldjp_benefit_2', '🏠 Hỗ trợ chỗ ở miễn phí', 'label', 'intro', 'xkldjp'),
('xkldjp_benefit_3', '✈️ Bay 0 đồng', 'label', 'intro', 'xkldjp'),
('xkldjp_benefit_4', '📋 Hợp đồng 3 năm', 'label', 'intro', 'xkldjp'),
('xkldjp_benefit_5', '🛡️ Bảo hiểm đầy đủ', 'label', 'intro', 'xkldjp')
ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP;
