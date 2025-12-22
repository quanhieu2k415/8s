<?php
/**
 * Seed Content Data
 * Run this once to populate initial CMS content
 */

require_once __DIR__ . '/backend_api/db_config.php';

// Content data to seed
$content_data = [
    // Hero Section - Slide 1
    ['index_hero_slide_1_img', 'https://icogroup.vn/vnt_upload/weblink/banner_trang_chu_01.jpg'],
    ['index_hero_slide_1_title', 'ICOGroup - Nơi Tạo Dựng Tương Lai'],
    ['index_hero_slide_1_subtitle', 'Tập đoàn Giáo dục và Đào tạo nghề hàng đầu Việt Nam với hơn 15 năm kinh nghiệm'],
    
    // Hero Section - Slide 2
    ['index_hero_slide_2_img', 'https://icogroup.vn/vnt_upload/weblink/banner_chu_04.jpg'],
    ['index_hero_slide_2_title', 'Chương Trình Du Học Quốc Tế'],
    ['index_hero_slide_2_subtitle', 'Nhật Bản • Đức • Hàn Quốc • Đài Loan'],
    
    // Hero Section - Slide 3
    ['index_hero_slide_3_img', 'https://www.icogroup.vn/vnt_upload/news/02_2025/ICOGROUP_TUYEN_DUNG_23.jpg'],
    ['index_hero_slide_3_title', 'Xuất Khẩu Lao Động Uy Tín'],
    ['index_hero_slide_3_subtitle', 'Cơ hội việc làm với thu nhập cao tại nước ngoài'],
    
    // About Section
    ['index_about_bg', 'https://icogroup.vn/vnt_upload/weblink/banner_trang_chu_01.jpg'],
    ['index_about_history_title', 'Lịch Sử Hình Thành & Phát Triển'],
    ['index_about_history_desc', 'Với tầm nhìn dài hạn và quan điểm phát triển bền vững, ICOGroup đã trở thành một trong những thương hiệu uy tín về du học và xuất khẩu lao động tại Việt Nam.'],
    ['index_about_history_desc_2', 'Hiện ICOGroup đã có mặt ở trên 60 tỉnh thành trong nước với cơ sở vật chất được đầu tư đồng bộ và hiện đại.'],
    
    // Statistics
    ['index_stat_1_number', '15+'],
    ['index_stat_1_label', 'Năm kinh nghiệm'],
    ['index_stat_2_number', '60+'],
    ['index_stat_2_label', 'Tỉnh thành'],
    ['index_stat_3_number', '50000+'],
    ['index_stat_3_label', 'Học viên'],
    ['index_stat_4_number', '200+'],
    ['index_stat_4_label', 'Đối tác'],
    
    // Global Header/Footer
    ['global_header_phone', '1900 599 979'],
    ['global_header_email', 'info@icogroup.vn'],
    ['global_footer_address', 'Số 1 Trần Hữu Dực, Nam Từ Liêm, Hà Nội'],
    ['global_footer_copyright', '© 2024 ICOGroup. All rights reserved.'],
    
    // Programs
    ['index_programs_title', 'Chương Trình Đào Tạo'],
    ['index_programs_subtitle', 'Khám phá các chương trình du học và xuất khẩu lao động chất lượng cao'],
];

echo "<h2>Seeding CMS Content</h2>";
echo "<pre>";

$success = 0;
$skipped = 0;
$table = isset($content_table) ? $content_table : 'content_pages';

// Create table if not exists
$create_table_sql = "CREATE TABLE IF NOT EXISTS $table (
    id INT AUTO_INCREMENT PRIMARY KEY,
    section_key VARCHAR(255) NOT NULL UNIQUE,
    content_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($create_table_sql)) {
    echo "✅ Table '$table' ready\n\n";
} else {
    echo "❌ Error creating table: " . $conn->error . "\n";
}


foreach ($content_data as $item) {
    $key = $item[0];
    $value = $item[1];
    
    // Check if exists
    $check = $conn->prepare("SELECT 1 FROM $table WHERE section_key = ?");
    $check->bind_param("s", $key);
    $check->execute();
    $result = $check->get_result();
    
    if ($result->num_rows > 0) {
        echo "⏭️ SKIP: $key (already exists)\n";
        $skipped++;
        $check->close();
        continue;
    }
    $check->close();
    
    // Insert
    $stmt = $conn->prepare("INSERT INTO $table (section_key, content_value) VALUES (?, ?)");
    $stmt->bind_param("ss", $key, $value);
    
    if ($stmt->execute()) {
        echo "✅ ADD: $key\n";
        $success++;
    } else {
        echo "❌ ERROR: $key - " . $stmt->error . "\n";
    }
    $stmt->close();
}

echo "\n";
echo "=================================\n";
echo "Total: " . count($content_data) . "\n";
echo "Added: $success\n";
echo "Skipped: $skipped\n";
echo "</pre>";

echo "<p><strong>Done!</strong> You can now see content in admin CMS and it will show on frontend.</p>";
echo "<p><a href='admin/dashboard.php'>Go to Admin CMS</a> | <a href='fonend/index.php'>View Homepage</a></p>";

closeConnection($conn);
?>
