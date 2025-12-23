<?php
/**
 * Seed ALL Pages Content
 * Creates content keys for every page in the website
 */

require_once __DIR__ . '/backend_api/db_config.php';

$table = isset($content_table) ? $content_table : 'content_pages';

// Create table
$conn->query("CREATE TABLE IF NOT EXISTS $table (
    id INT AUTO_INCREMENT PRIMARY KEY,
    section_key VARCHAR(255) NOT NULL UNIQUE,
    content_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// All content organized by page
$all_content = [
    // ============ GLOBAL (Header/Footer) ============
    'global' => [
        ['global_logo_img', 'https://icogroup.vn/vnt_template/ico_vn/images/logo.svg', 'Logo ICOGroup'],
        ['global_header_phone', '1900 599 979', 'Số điện thoại hotline'],
        ['global_header_email', 'info@icogroup.vn', 'Email liên hệ'],
        ['global_footer_address', 'Số 1 Trần Hữu Dực, Nam Từ Liêm, Hà Nội', 'Địa chỉ công ty'],
        ['global_footer_copyright', '© 2024 ICOGroup. All rights reserved.', 'Bản quyền'],
        ['global_facebook_url', 'https://facebook.com/icogroup', 'Link Facebook'],
        ['global_youtube_url', 'https://youtube.com/icogroup', 'Link YouTube'],
        ['global_zalo_url', 'https://zalo.me/icogroup', 'Link Zalo'],
        // Header Contact
        ['header_phone', '0822314555', 'Số điện thoại header (không có dấu chấm)'],
        ['header_phone_display', '0822.314.555', 'Số điện thoại header (có dấu chấm)'],
        ['header_email', 'info@icogroup.vn', 'Email header'],
        // Navigation Menu
        ['menu_trangchu', 'Trang chủ', 'Menu Trang chủ'],
        ['menu_veicogroup', 'Về ICOGroup', 'Menu Về ICOGroup'],
        ['menu_duhoc', 'Du học', 'Menu Du học'],
        ['menu_duhoc_germany', 'Du học Đức', 'Menu Du học Đức'],
        ['menu_duhoc_japan', 'Du học Nhật', 'Menu Du học Nhật'],
        ['menu_duhoc_korea', 'Du học Hàn Quốc', 'Menu Du học Hàn Quốc'],
        ['menu_xkld', 'Xuất khẩu lao động', 'Menu XKLĐ'],
        ['menu_xkld_japan', 'Nhật Bản', 'Menu XKLĐ Nhật Bản'],
        ['menu_xkld_korea', 'Hàn Quốc', 'Menu XKLĐ Hàn Quốc'],
        ['menu_xkld_taiwan', 'Đài Loan', 'Menu XKLĐ Đài Loan'],
        ['menu_xkld_eu', 'Châu Âu', 'Menu XKLĐ Châu Âu'],
        ['menu_huongnghiep', 'Hướng nghiệp', 'Menu Hướng nghiệp'],
        ['menu_hoatdong', 'Hoạt động', 'Menu Hoạt động'],
        ['menu_lienhe', 'Liên hệ', 'Menu Liên hệ'],
        ['menu_dangky', 'Đăng ký', 'Menu Đăng ký'],
        // Menu Visibility (1 = hiện, 0 = ẩn)
        ['menu_duhoc_germany_visible', '1', 'Hiển thị Du học Đức'],
        ['menu_duhoc_japan_visible', '1', 'Hiển thị Du học Nhật'],
        ['menu_duhoc_korea_visible', '1', 'Hiển thị Du học Hàn'],
        ['menu_xkld_japan_visible', '1', 'Hiển thị XKLĐ Nhật'],
        ['menu_xkld_korea_visible', '1', 'Hiển thị XKLĐ Hàn'],
        ['menu_xkld_taiwan_visible', '1', 'Hiển thị XKLĐ Đài Loan'],
        ['menu_xkld_eu_visible', '1', 'Hiển thị XKLĐ Châu Âu'],
    ],
    
    // ============ TRANG CHỦ (index) ============
    'index' => [
        // Hero Slides
        ['index_hero_slide_1_img', 'https://icogroup.vn/vnt_upload/weblink/banner_trang_chu_01.jpg', 'Banner slide 1'],
        ['index_hero_slide_1_title', 'ICOGroup - Nơi Tạo Dựng Tương Lai', 'Tiêu đề slide 1'],
        ['index_hero_slide_1_subtitle', 'Tập đoàn Giáo dục và Đào tạo nghề hàng đầu Việt Nam', 'Mô tả slide 1'],
        ['index_hero_slide_2_img', 'https://icogroup.vn/vnt_upload/weblink/banner_chu_04.jpg', 'Banner slide 2'],
        ['index_hero_slide_2_title', 'Chương Trình Du Học Quốc Tế', 'Tiêu đề slide 2'],
        ['index_hero_slide_2_subtitle', 'Nhật Bản • Đức • Hàn Quốc • Đài Loan', 'Mô tả slide 2'],
        ['index_hero_slide_3_img', 'https://www.icogroup.vn/vnt_upload/news/02_2025/ICOGROUP_TUYEN_DUNG_23.jpg', 'Banner slide 3'],
        ['index_hero_slide_3_title', 'Xuất Khẩu Lao Động Uy Tín', 'Tiêu đề slide 3'],
        ['index_hero_slide_3_subtitle', 'Cơ hội việc làm với thu nhập cao tại nước ngoài', 'Mô tả slide 3'],
        // About
        ['index_about_bg_img', 'https://icogroup.vn/vnt_upload/weblink/banner_trang_chu_01.jpg', 'Ảnh nền phần giới thiệu'],
        ['index_about_title', 'Về ICOGroup', 'Tiêu đề giới thiệu'],
        ['index_about_history_title', 'Lịch Sử Hình Thành & Phát Triển', 'Tiêu đề lịch sử'],
        ['index_about_history_desc', 'Với tầm nhìn dài hạn và quan điểm phát triển bền vững...', 'Nội dung lịch sử'],
        // Stats
        ['index_stat_1_number', '15+', 'Số năm kinh nghiệm'],
        ['index_stat_1_label', 'Năm kinh nghiệm', 'Nhãn'],
        ['index_stat_2_number', '60+', 'Số tỉnh thành'],
        ['index_stat_2_label', 'Tỉnh thành', 'Nhãn'],
        ['index_stat_3_number', '50000+', 'Số học viên'],
        ['index_stat_3_label', 'Học viên', 'Nhãn'],
        ['index_stat_4_number', '200+', 'Số đối tác'],
        ['index_stat_4_label', 'Đối tác', 'Nhãn'],
        // Ecosystem Section (Hệ sinh thái)
        ['index_eco_1_img', 'https://icogroup.vn/vnt_upload/service/Linkedin_3.jpg', 'Ảnh Trung tâm Ngoại ngữ'],
        ['index_eco_1_logo', 'https://icogroup.vn/vnt_upload/service/Logo_TTNN_ICO_24x_100.jpg', 'Logo Trung tâm Ngoại ngữ'],
        ['index_eco_1_name', 'Trung tâm Ngoại ngữ ICO', 'Tên Trung tâm Ngoại ngữ'],
        ['index_eco_1_slogan', 'Học ngoại ngữ để lập nghiệp', 'Slogan'],
        ['index_eco_1_desc', 'Đào tạo tiếng Nhật, tiếng Đức, tiếng Hàn với đội ngũ giáo viên chất lượng cao và phương pháp hiện đại.', 'Mô tả'],
        ['index_eco_2_img', 'https://icogroup.vn/vnt_upload/service/khai_giang_icoschool.jpg', 'Ảnh ICOSchool'],
        ['index_eco_2_logo', 'https://icogroup.vn/vnt_upload/service/mmicon2.jpg', 'Logo ICOSchool'],
        ['index_eco_2_name', 'ICOSchool', 'Tên ICOSchool'],
        ['index_eco_2_slogan', 'Go Global! - Hãy bước ra thế giới', 'Slogan'],
        ['index_eco_2_desc', 'Trường THPT chất lượng cao, hoạt động theo mô hình chuyên ngữ với chương trình giáo dục chuẩn quốc tế.', 'Mô tả'],
        ['index_eco_3_img', 'https://icogroup.vn/vnt_upload/service/mmimg3.jpg', 'Ảnh ICOCollege'],
        ['index_eco_3_logo', 'https://icogroup.vn/vnt_upload/service/mmicon3.jpg', 'Logo ICOCollege'],
        ['index_eco_3_name', 'ICOCollege', 'Tên ICOCollege'],
        ['index_eco_3_desc', 'Cao đẳng nghề chất lượng cao với cam kết việc làm sau tốt nghiệp và đào tạo theo đơn đặt hàng.', 'Mô tả'],
        ['index_eco_4_img', 'https://icogroup.vn/vnt_upload/service/mmimg4.jpg', 'Ảnh ICOCareer'],
        ['index_eco_4_name', 'ICOCareer', 'Tên ICOCareer'],
        ['index_eco_4_desc', 'Hướng nghiệp, tư vấn nghề nghiệp và kết nối việc làm trong nước và quốc tế cho học viên.', 'Mô tả'],
        // Programs Section (Chương trình nổi bật)
        ['index_programs_bg', '', 'Ảnh nền phần Chương trình nổi bật'],
        ['index_program_1_img', 'https://cdn-images.vtv.vn/562122370168008704/2023/7/26/untitled-1690344019340844974097.png', 'Ảnh Du học Nhật Bản'],
        ['index_program_1_title', 'Du Học Nhật Bản', 'Tiêu đề'],
        ['index_program_1_desc', 'Chương trình du học Nhật Bản với 100+ trường đối tác. Học bổng hấp dẫn, visa cao.', 'Mô tả'],
        ['index_program_2_img', 'https://icogroup.vn/vnt_upload/weblink/banner_chu_04.jpg', 'Ảnh Du học Đức'],
        ['index_program_2_title', 'Du Học Đức', 'Tiêu đề'],
        ['index_program_2_desc', 'Du học kép (Ausbildung): Học miễn phí, có lương, việc làm ngay sau tốt nghiệp.', 'Mô tả'],
        ['index_program_3_img', 'https://icogroup.vn/vnt_upload/weblink/banner_chu_04.jpg', 'Ảnh XKLĐ Nhật Bản'],
        ['index_program_3_title', 'Xuất Khẩu Lao Động Nhật Bản', 'Tiêu đề'],
        ['index_program_3_desc', 'Chương trình thực tập sinh kỹ năng với thu nhập từ 30-40 triệu/tháng.', 'Mô tả'],
    ],
    
    // ============ DU HỌC NHẬT (nhat) ============
    'nhat' => [
        ['nhat_header_bg', '', 'Banner du học Nhật'],
        ['nhat_title', 'Du Học Nhật Bản 🇯🇵', 'Tiêu đề trang'],
        ['nhat_subtitle', 'Hành trình chinh phục xứ sở hoa anh đào', 'Mô tả'],
        ['nhat_why_title', 'Tại Sao Chọn Du Học Nhật Bản?', 'Tiêu đề phần tại sao'],
        ['nhat_why_subtitle', 'Nhật Bản - Điểm đến hàng đầu của du học sinh Việt Nam', 'Mô tả'],
        ['nhat_about_img', 'https://cdn-images.vtv.vn/562122370168008704/2023/7/26/untitled-1690344019340844974097.png', 'Ảnh giới thiệu'],
        ['nhat_badge', '100+ Đối tác', 'Badge'],
        ['nhat_reason_title', 'Lý Do Nên Du Học Nhật Bản', 'Tiêu đề lý do'],
        ['nhat_reason_desc', 'Nhật Bản là quốc gia có nền giáo dục tiên tiến, công nghệ phát triển và nền văn hóa độc đáo.', 'Mô tả'],
        ['nhat_benefit_1', 'Giáo dục đẳng cấp thế giới', 'Lợi ích 1'],
        ['nhat_benefit_2', 'Làm thêm 28h/tuần hợp pháp', 'Lợi ích 2'],
        ['nhat_benefit_3', 'Học bổng lên đến 100%', 'Lợi ích 3'],
        ['nhat_benefit_4', 'An ninh và an toàn cao', 'Lợi ích 4'],
        ['nhat_benefit_5', 'Cơ hội việc làm sau tốt nghiệp', 'Lợi ích 5'],
        ['nhat_benefit_6', 'Văn hóa độc đáo, hấp dẫn', 'Lợi ích 6'],
        ['nhat_partners_title', 'Đối Tác Trường Nhật Ngữ', 'Tiêu đề đối tác'],
        ['nhat_partners_subtitle', 'ICOGroup là đối tác của hơn 100 trường uy tín tại Nhật Bản', 'Mô tả'],
        ['nhat_programs_title', 'Các Chương Trình Du Học', 'Tiêu đề chương trình'],
        ['nhat_program_1_tag', 'Ngắn hạn', 'Tag 1'],
        ['nhat_program_1_title', 'Du Học Tiếng Nhật', 'Tiêu đề 1'],
        ['nhat_program_1_desc', 'Chương trình học tiếng Nhật từ 6 tháng - 2 năm tại các trường Nhật ngữ uy tín.', 'Mô tả'],
        ['nhat_program_1_cost', 'Chi phí: 150 - 200 triệu VNĐ/năm', 'Chi phí'],
        ['nhat_program_2_tag', 'Dài hạn', 'Tag 2'],
        ['nhat_program_2_title', 'Du Học Cao Đẳng - Đại Học', 'Tiêu đề 2'],
        ['nhat_program_2_desc', 'Học tại các trường Cao đẳng, Đại học tại Nhật Bản với nhiều ngành học đa dạng.', 'Mô tả'],
        ['nhat_program_2_scholarship', 'Học bổng lên đến 100%', 'Học bổng'],
        ['nhat_program_3_tag', 'Kỹ năng', 'Tag 3'],
        ['nhat_program_3_title', 'Du Học Nghề (Senmon)', 'Tiêu đề 3'],
        ['nhat_program_3_desc', 'Học tại các trường chuyên môn với thời gian 2 năm.', 'Mô tả'],
        ['nhat_program_3_result', 'Việc làm ngay sau tốt nghiệp', 'Kết quả'],
        ['nhat_process_title', 'Quy Trình Du Học Nhật Bản', 'Tiêu đề quy trình'],
        ['nhat_process_subtitle', '6 bước đơn giản để đến với xứ sở hoa anh đào', 'Mô tả'],
        ['nhat_step_1', 'Đăng ký tư vấn', 'Bước 1'],
        ['nhat_step_2', 'Chọn trường', 'Bước 2'],
        ['nhat_step_3', 'Hoàn thiện hồ sơ', 'Bước 3'],
        ['nhat_step_4', 'Xin COE', 'Bước 4'],
        ['nhat_step_5', 'Xin Visa', 'Bước 5'],
        ['nhat_step_6', 'Bay sang Nhật', 'Bước 6'],
        ['nhat_cta_title', 'Đăng Ký Tư Vấn Du Học Nhật Bản', 'Tiêu đề CTA'],
        ['nhat_cta_desc', 'Nhận tư vấn miễn phí từ đội ngũ chuyên gia với 15 năm kinh nghiệm', 'Mô tả CTA'],
    ],
    
    // ============ DU HỌC ĐỨC (duc) ============
    'duc' => [
        ['duc_header_bg', '', 'Banner du học Đức'],
        ['duc_title', 'Du Học Đức', 'Tiêu đề trang'],
        ['duc_subtitle', 'Chương trình du học miễn học phí với cơ hội việc làm và định cư', 'Mô tả'],
        ['duc_why_title', 'Tại Sao Chọn Du Học Đức?', 'Tiêu đề tại sao'],
        ['duc_why_subtitle', 'Đức - Điểm đến lý tưởng cho du học sinh quốc tế', 'Mô tả'],
        ['duc_about_img', 'https://icogroupvn.wordpress.com/wp-content/uploads/2017/03/du-hoc-duc-ico-cho-tuong-lai-tuoi-sang-01.jpg?w=460&h=345', 'Ảnh giới thiệu'],
        ['duc_advantage_title', 'Ưu Điểm Vượt Trội', 'Tiêu đề ưu điểm'],
        ['duc_advantage_desc', 'Đức là một trong những quốc gia có nền giáo dục hàng đầu thế giới.', 'Mô tả'],
        ['duc_benefit_1', 'Miễn học phí tại đại học công lập', 'Lợi ích 1'],
        ['duc_benefit_2', 'Học nghề hưởng lương 800-1200€/tháng', 'Lợi ích 2'],
        ['duc_benefit_3', 'Cơ hội định cư sau khi tốt nghiệp', 'Lợi ích 3'],
        ['duc_benefit_4', 'Bằng cấp được công nhận toàn cầu', 'Lợi ích 4'],
        ['duc_benefit_5', 'Du lịch tự do trong khối Schengen', 'Lợi ích 5'],
        ['duc_programs_title', 'Các Chương Trình Du Học Đức', 'Tiêu đề chương trình'],
        ['duc_program_1_title', 'Du Học Đại Học', 'Tiêu đề 1'],
        ['duc_program_1_desc', 'Học tại các trường đại học công lập hàng đầu nước Đức với học phí 0€.', 'Mô tả'],
        ['duc_program_2_title', 'Du Học Nghề (Ausbildung)', 'Tiêu đề 2'],
        ['duc_program_2_desc', 'Chương trình đào tạo kép: Học + thực hành. Lương 800-1200€/tháng.', 'Mô tả'],
        ['duc_program_3_title', 'Du Học Hè', 'Tiêu đề 3'],
        ['duc_program_3_desc', 'Chương trình trải nghiệm ngắn hạn 2-4 tuần.', 'Mô tả'],
        ['duc_ausbildung_title', 'Du Học Kép Tại Đức (Ausbildung)', 'Tiêu đề Ausbildung'],
        ['duc_ausbildung_subtitle', 'Học miễn phí, có lương, việc làm ngay sau tốt nghiệp', 'Mô tả'],
        ['duc_requirements_title', 'Điều Kiện & Hồ Sơ Du Học Đức', 'Tiêu đề điều kiện'],
        ['duc_condition_title', 'Điều Kiện', 'Tiêu đề điều kiện'],
        ['duc_documents_title', 'Hồ Sơ Cần Thiết', 'Tiêu đề hồ sơ'],
        ['duc_cost_title', 'Chi Phí', 'Tiêu đề chi phí'],
        ['duc_cta_title', 'Đăng Ký Tư Vấn Du Học Đức', 'Tiêu đề CTA'],
        ['duc_cta_desc', 'Nhận tư vấn miễn phí từ chuyên gia du học Đức của ICOGroup', 'Mô tả CTA'],
    ],
    
    // ============ DU HỌC HÀN (han) ============
    'han' => [
        ['han_header_bg', '', 'Banner du học Hàn'],
        ['han_title', 'Du Học Hàn Quốc 🇰🇷', 'Tiêu đề trang'],
        ['han_subtitle', 'Khám phá xứ sở kim chi - Điểm đến du học hấp dẫn', 'Mô tả'],
        ['han_why_title', 'Tại Sao Chọn Du Học Hàn Quốc?', 'Tiêu đề tại sao'],
        ['han_about_img', 'https://icogroup.vn/vnt_upload/news/11_2024/TRUONG_DAI_HOC_PUKYONG.jpg', 'Ảnh giới thiệu'],
        ['han_reason_title', 'Lý Do Du Học Hàn Quốc', 'Tiêu đề lý do'],
        ['han_reason_desc', 'Hàn Quốc là quốc gia phát triển với nền giáo dục đẳng cấp, văn hóa K-Pop hấp dẫn.', 'Mô tả'],
        ['han_benefit_1', 'Chi phí thấp hơn Nhật, Mỹ', 'Lợi ích 1'],
        ['han_benefit_2', 'Nhiều học bổng hấp dẫn', 'Lợi ích 2'],
        ['han_benefit_3', 'Làm thêm 20h/tuần', 'Lợi ích 3'],
        ['han_benefit_4', 'Văn hóa K-Pop, K-Drama', 'Lợi ích 4'],
        ['han_benefit_5', 'Nhiều tập đoàn lớn', 'Lợi ích 5'],
        ['han_programs_title', 'Chương Trình Du Học', 'Tiêu đề chương trình'],
        ['han_program_1_title', 'Học Tiếng Hàn', 'Tiêu đề 1'],
        ['han_program_1_desc', 'Chương trình 6-12 tháng tại các trường đại học, trung tâm ngôn ngữ uy tín.', 'Mô tả'],
        ['han_program_1_cost', 'Chi phí: 80-120 triệu/năm', 'Chi phí'],
        ['han_program_2_title', 'Cao Đẳng - Đại Học', 'Tiêu đề 2'],
        ['han_program_2_desc', 'Học tại các trường top Hàn Quốc: Seoul National, Yonsei, Korea University...', 'Mô tả'],
        ['han_program_2_scholarship', 'Học bổng: 30-100%', 'Học bổng'],
        ['han_program_3_title', 'Thạc Sĩ - Tiến Sĩ', 'Tiêu đề 3'],
        ['han_program_3_desc', 'Chương trình sau đại học với nhiều học bổng toàn phần từ chính phủ Hàn Quốc.', 'Mô tả'],
        ['han_program_3_scholarship', 'KGSP, GKS Scholarship', 'Học bổng'],
        ['han_cta_title', 'Đăng Ký Tư Vấn Du Học Hàn Quốc', 'Tiêu đề CTA'],
    ],
    
    // ============ XKLĐ NHẬT (xkldjp) ============
    'xkldjp' => [
        ['xkldjp_hero_img', 'https://icogroup.vn/vnt_upload/weblink/banner_xkld_japan.jpg', 'Banner XKLĐ Nhật'],
        ['xkldjp_hero_title', 'Xuất Khẩu Lao Động Nhật Bản', 'Tiêu đề hero'],
        ['xkldjp_hero_subtitle', 'Cơ hội việc làm thu nhập cao tại Nhật Bản', 'Mô tả hero'],
        ['xkldjp_about_img', 'https://icogroup.vn/vnt_upload/weblink/xkld_jp_about.jpg', 'Ảnh giới thiệu'],
        ['xkldjp_about_title', 'Tại Sao Chọn XKLĐ Nhật Bản?', 'Tiêu đề giới thiệu'],
        ['xkldjp_about_desc', 'Thu nhập 25-40 triệu/tháng, môi trường làm việc chuyên nghiệp...', 'Mô tả'],
        ['xkldjp_benefit_1', 'Thu nhập cao: 25-40 triệu/tháng', 'Quyền lợi 1'],
        ['xkldjp_benefit_2', 'Hỗ trợ visa, vé máy bay', 'Quyền lợi 2'],
        ['xkldjp_benefit_3', 'Đào tạo tiếng Nhật miễn phí', 'Quyền lợi 3'],
    ],
    
    // ============ XKLĐ HÀN (xkldhan) ============
    'xkldhan' => [
        ['xkldhan_header_bg', '', 'Banner XKLĐ Hàn Quốc'],
        ['xkldhan_title', 'Xuất Khẩu Lao Động Hàn Quốc 🇰🇷', 'Tiêu đề trang'],
        ['xkldhan_subtitle', 'Chương trình EPS - Cơ hội việc làm tại xứ sở kim chi', 'Mô tả'],
        ['xkldhan_program_title', 'Chương Trình EPS Hàn Quốc', 'Tiêu đề chương trình'],
        ['xkldhan_program_desc', 'Chương trình cấp phép việc làm cho lao động nước ngoài (EPS) là chương trình chính thức của Chính phủ Hàn Quốc.', 'Mô tả'],
        ['xkldhan_benefit_1', 'Thu nhập 25-35 triệu/tháng', 'Quyền lợi 1'],
        ['xkldhan_benefit_2', 'Hợp đồng 4 năm 10 tháng', 'Quyền lợi 2'],
        ['xkldhan_benefit_3', 'Bảo hiểm xã hội đầy đủ', 'Quyền lợi 3'],
        ['xkldhan_main_img', 'https://icogroup.vn/vnt_upload/news/11_2024/TRUONG_DAI_HOC_PUKYONG.jpg', 'Ảnh chính'],
        ['xkldhan_cta_title', 'Đăng Ký XKLĐ Hàn Quốc', 'Tiêu đề CTA'],
    ],
    
    // ============ XKLĐ ĐÀI LOAN (xklddailoan) ============
    'xklddailoan' => [
        ['xklddailoan_header_bg', '', 'Banner XKLĐ Đài Loan'],
        ['xklddailoan_title', 'Xuất Khẩu Lao Động Đài Loan', 'Tiêu đề trang'],
        ['xklddailoan_subtitle', 'Chi phí thấp - Thu nhập ổn định - Cơ hội phát triển', 'Mô tả'],
        ['xklddailoan_program_title', 'Lao Động Đài Loan', 'Tiêu đề chương trình'],
        ['xklddailoan_program_desc', 'Đài Loan là thị trường lao động hấp dẫn với chi phí xuất cảnh thấp, ngôn ngữ dễ học và văn hóa gần gũi với Việt Nam.', 'Mô tả'],
        ['xklddailoan_benefit_1', 'Thu nhập 20-30 triệu/tháng', 'Quyền lợi 1'],
        ['xklddailoan_benefit_2', 'Chi phí xuất cảnh thấp', 'Quyền lợi 2'],
        ['xklddailoan_benefit_3', 'Ngôn ngữ dễ học', 'Quyền lợi 3'],
        ['xklddailoan_main_img', 'https://icogroup.vn/vnt_upload/weblink/banner_chu_04.jpg', 'Ảnh chính'],
        ['xklddailoan_cta_title', 'Đăng Ký XKLĐ Đài Loan', 'Tiêu đề CTA'],
    ],
    
    // ============ XKLĐ CHÂU ÂU (xkldchauau) ============
    'xkldchauau' => [
        ['xkldchauau_header_bg', '', 'Banner XKLĐ Châu Âu'],
        ['xkldchauau_title', 'Xuất Khẩu Lao Động Châu Âu 🇪🇺', 'Tiêu đề trang'],
        ['xkldchauau_subtitle', 'Cơ hội làm việc tại các nước phát triển Châu Âu', 'Mô tả'],
        ['xkldchauau_program_title', 'Lao Động Châu Âu', 'Tiêu đề chương trình'],
        ['xkldchauau_program_desc', 'Châu Âu với các quốc gia phát triển như Đức, Ba Lan, Romania mở ra cơ hội việc làm với thu nhập cao.', 'Mô tả'],
        ['xkldchauau_benefit_1', 'Thu nhập 40-60 triệu/tháng', 'Quyền lợi 1'],
        ['xkldchauau_benefit_2', 'Cơ hội định cư', 'Quyền lợi 2'],
        ['xkldchauau_benefit_3', 'Du lịch Schengen tự do', 'Quyền lợi 3'],
        ['xkldchauau_main_img', 'https://icogroup.vn/vnt_upload/weblink/banner_chu_04.jpg', 'Ảnh chính'],
        ['xkldchauau_countries_title', 'Các Quốc Gia Tuyển Dụng', 'Tiêu đề quốc gia'],
        ['xkldchauau_country_1_name', 'Đức', 'Tên quốc gia 1'],
        ['xkldchauau_country_1_desc', 'Điều dưỡng, cơ khí, nhà hàng khách sạn', 'Ngành nghề 1'],
        ['xkldchauau_country_2_name', 'Ba Lan', 'Tên quốc gia 2'],
        ['xkldchauau_country_2_desc', 'Nông nghiệp, chế biến thực phẩm, xây dựng', 'Ngành nghề 2'],
        ['xkldchauau_country_3_name', 'Romania', 'Tên quốc gia 3'],
        ['xkldchauau_country_3_desc', 'May mặc, điện tử, cơ khí', 'Ngành nghề 3'],
        ['xkldchauau_cta_title', 'Đăng Ký XKLĐ Châu Âu', 'Tiêu đề CTA'],
    ],
    
    // ============ VỀ ICOGROUP (ve-icogroup) ============
    'veicogroup' => [
        ['veicogroup_hero_img', 'https://icogroup.vn/vnt_upload/weblink/banner_about.jpg', 'Banner về ICOGroup'],
        ['veicogroup_hero_title', 'Về ICOGroup', 'Tiêu đề hero'],
        ['veicogroup_intro_title', 'Giới Thiệu Chung', 'Tiêu đề giới thiệu'],
        ['veicogroup_intro_desc', 'ICOGroup được thành lập năm 2008, là tập đoàn giáo dục hàng đầu...', 'Nội dung giới thiệu'],
        ['veicogroup_vision_title', 'Tầm Nhìn', 'Tiêu đề tầm nhìn'],
        ['veicogroup_vision_desc', 'Trở thành tập đoàn phát triển nguồn nhân lực lớn nhất Việt Nam', 'Nội dung tầm nhìn'],
        ['veicogroup_mission_title', 'Sứ Mệnh', 'Tiêu đề sứ mệnh'],
        ['veicogroup_mission_desc', 'Nâng cao chất lượng nguồn nhân lực Việt Nam', 'Nội dung sứ mệnh'],
    ],
    
    // ============ LIÊN HỆ (lienhe) ============
    'lienhe' => [
        ['lienhe_hero_img', 'https://icogroup.vn/vnt_upload/weblink/banner_contact.jpg', 'Banner liên hệ'],
        ['lienhe_hero_title', 'Liên Hệ Với Chúng Tôi', 'Tiêu đề hero'],
        ['lienhe_address', 'Số 1 Trần Hữu Dực, Nam Từ Liêm, Hà Nội', 'Địa chỉ'],
        ['lienhe_phone', '1900 599 979', 'Số điện thoại'],
        ['lienhe_email', 'info@icogroup.vn', 'Email'],
        ['lienhe_map_iframe', 'https://www.google.com/maps/embed?pb=...', 'Embed map'],
    ],
    
    // ============ HƯỚNG NGHIỆP (huong-nghiep) ============
    'huongnghiep' => [
        ['huongnghiep_header_bg', '', 'Banner hướng nghiệp'],
        ['huongnghiep_title', 'ICOCareer - Hướng Nghiệp', 'Tiêu đề trang'],
        ['huongnghiep_subtitle', 'Định hướng tương lai, khai phá tiềm năng', 'Mô tả'],
        ['huongnghiep_intro_title', 'Hoạt Động Hướng Nghiệp ICOGroup', 'Tiêu đề giới thiệu'],
        ['huongnghiep_intro_desc', 'Hoạt động hướng nghiệp là một hoạt động không thể thiếu trong hành trình học tập và phát triển.', 'Mô tả'],
        // Programs Section
        ['huongnghiep_programs_title', 'Chương Trình Hướng Nghiệp', 'Tiêu đề phần chương trình'],
        ['huongnghiep_programs_subtitle', 'Ba hướng đi chính dành cho học viên ICOGroup', 'Mô tả'],
        // Program 1 - Du học
        ['huongnghiep_program_1_img', 'https://cdn-images.vtv.vn/562122370168008704/2023/7/26/untitled-1690344019340844974097.png', 'Ảnh Du học'],
        ['huongnghiep_program_1_tag', 'Du học', 'Tag'],
        ['huongnghiep_program_1_title', 'Du Học Quốc Tế', 'Tiêu đề'],
        ['huongnghiep_program_1_desc', 'Chương trình du học tại Nhật Bản, Đức, Hàn Quốc, Đài Loan với học bổng hấp dẫn và hỗ trợ visa toàn diện.', 'Mô tả'],
        ['huongnghiep_program_1_benefit_1', 'Học bổng lên đến 100%', 'Lợi ích 1'],
        ['huongnghiep_program_1_benefit_2', 'Hỗ trợ visa, ký túc xá', 'Lợi ích 2'],
        ['huongnghiep_program_1_benefit_3', 'Việc làm thêm hợp pháp', 'Lợi ích 3'],
        // Program 2 - Lao động quốc tế
        ['huongnghiep_program_2_img', 'https://icogroup.vn/vnt_upload/weblink/banner_chu_04.jpg', 'Ảnh Lao động'],
        ['huongnghiep_program_2_tag', 'Lao động', 'Tag'],
        ['huongnghiep_program_2_title', 'Lao Động Quốc Tế', 'Tiêu đề'],
        ['huongnghiep_program_2_desc', 'Chương trình xuất khẩu lao động tại Nhật Bản, Hàn Quốc, Đài Loan, Đức với thu nhập cao.', 'Mô tả'],
        ['huongnghiep_program_2_benefit_1', 'Thu nhập 30-50 triệu/tháng', 'Lợi ích 1'],
        ['huongnghiep_program_2_benefit_2', 'Hợp đồng lao động rõ ràng', 'Lợi ích 2'],
        ['huongnghiep_program_2_benefit_3', 'Bảo hiểm y tế đầy đủ', 'Lợi ích 3'],
        // Program 3 - Việc làm trong nước
        ['huongnghiep_program_3_img', 'https://icogroup.vn/vnt_upload/news/11_2024/43_NAM_NGAY_NHA_GIAO_VN_1.jpg', 'Ảnh Việc làm'],
        ['huongnghiep_program_3_tag', 'Việc làm', 'Tag'],
        ['huongnghiep_program_3_title', 'Lao Động Trong Nước', 'Tiêu đề'],
        ['huongnghiep_program_3_desc', 'Kết nối việc làm tại các doanh nghiệp trong nước, đặc biệt là doanh nghiệp FDI.', 'Mô tả'],
        ['huongnghiep_program_3_benefit_1', 'Doanh nghiệp Nhật, Hàn tại VN', 'Lợi ích 1'],
        ['huongnghiep_program_3_benefit_2', 'Mức lương cạnh tranh', 'Lợi ích 2'],
        ['huongnghiep_program_3_benefit_3', 'Cơ hội thăng tiến', 'Lợi ích 3'],
    ],
    
    // ============ HOẠT ĐỘNG (hoatdong) ============
    'hoatdong' => [
        ['hoatdong_header_bg', '', 'Banner hoạt động'],
    ],
    
    // ============ BANNER HEADERS ============
    'banners' => [
        ['nhat_header_bg', '', 'Banner Du học Nhật Bản'],
        ['duc_header_bg', '', 'Banner Du học Đức'],
        ['han_header_bg', '', 'Banner Du học Hàn Quốc'],
        ['xkldjp_header_bg', '', 'Banner XKLĐ Nhật Bản'],
        ['xkldhan_header_bg', '', 'Banner XKLĐ Hàn Quốc'],
        ['xklddailoan_header_bg', '', 'Banner XKLĐ Đài Loan'],
        ['xkldchauau_header_bg', '', 'Banner XKLĐ Châu Âu'],
        ['about_header_bg', '', 'Banner Về ICOGroup'],
        ['contact_header_bg', '', 'Banner Liên hệ'],
    ],
];

// Page labels for display
$page_labels = [
    'global' => 'Toàn trang (Global)',
    'index' => 'Trang chủ',
    'nhat' => 'Du học Nhật Bản',
    'duc' => 'Du học Đức',
    'han' => 'Du học Hàn Quốc',
    'xkldjp' => 'XKLĐ Nhật Bản',
    'xkldhan' => 'XKLĐ Hàn Quốc',
    'xklddailoan' => 'XKLĐ Đài Loan',
    'xkldchauau' => 'XKLĐ Châu Âu',
    'veicogroup' => 'Về ICOGroup',
    'lienhe' => 'Liên hệ',
    'huongnghiep' => 'Hướng nghiệp',
    'hoatdong' => 'Hoạt động',
    'banners' => 'Banner Headers',
];

echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>Seed Content</title>";
echo "<style>body{font-family:Arial;padding:20px;max-width:900px;margin:0 auto}";
echo ".page{background:#f5f5f5;padding:15px;margin:15px 0;border-radius:8px}";
echo ".page h3{margin:0 0 10px;color:#333}.ok{color:green}.skip{color:#888}.err{color:red}";
echo "pre{background:#fff;padding:10px;border-radius:4px;font-size:13px;max-height:200px;overflow:auto}</style></head><body>";

echo "<h1>🚀 Seed CMS Content - All Pages</h1>";

$total_added = 0;
$total_skipped = 0;

foreach ($all_content as $page => $items) {
    $label = $page_labels[$page] ?? $page;
    echo "<div class='page'><h3>📄 $label</h3><pre>";
    
    foreach ($items as $item) {
        $key = $item[0];
        $value = $item[1];
        
        // Check if exists
        $check = $conn->prepare("SELECT 1 FROM $table WHERE section_key = ?");
        $check->bind_param("s", $key);
        $check->execute();
        $result = $check->get_result();
        
        if ($result->num_rows > 0) {
            echo "<span class='skip'>⏭️ $key (đã tồn tại)</span>\n";
            $total_skipped++;
            $check->close();
            continue;
        }
        $check->close();
        
        // Insert
        $stmt = $conn->prepare("INSERT INTO $table (section_key, content_value) VALUES (?, ?)");
        $stmt->bind_param("ss", $key, $value);
        
        if ($stmt->execute()) {
            echo "<span class='ok'>✅ $key</span>\n";
            $total_added++;
        } else {
            echo "<span class='err'>❌ $key - " . $stmt->error . "</span>\n";
        }
        $stmt->close();
    }
    
    echo "</pre></div>";
}

echo "<h2>📊 Kết quả</h2>";
echo "<p><strong>Tổng thêm mới:</strong> $total_added</p>";
echo "<p><strong>Đã tồn tại:</strong> $total_skipped</p>";

echo "<h2>🔗 Tiếp theo</h2>";
echo "<p><a href='admin/dashboard.php' style='padding:10px 20px;background:#2563eb;color:white;text-decoration:none;border-radius:6px;'>Vào Admin CMS →</a></p>";

closeConnection($conn);
echo "</body></html>";
?>
