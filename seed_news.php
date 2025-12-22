<?php
/**
 * Seed News Data
 * Run this to create news table and populate sample news
 * 
 * Usage: http://localhost/web8s/seed_news.php
 * 
 * IMPORTANT: Delete this file after running!
 */

require_once __DIR__ . '/backend_api/db_config.php';

echo "<h1>📰 ICOGroup News Setup</h1>";
echo "<pre>";

try {
    // Create news table
    $create_news = "CREATE TABLE IF NOT EXISTS news (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($conn->query($create_news)) {
        echo "✅ Bảng 'news' đã được tạo/kiểm tra\n";
    } else {
        echo "❌ Lỗi tạo bảng: " . $conn->error . "\n";
    }
    
    // Create indexes
    @$conn->query("CREATE INDEX idx_news_category ON news(category)");
    @$conn->query("CREATE INDEX idx_news_status ON news(status)");
    @$conn->query("CREATE INDEX idx_news_created_at ON news(created_at)");
    echo "✅ Đã tạo indexes\n";
    
    // Sample news data
    $sample_news = [
        [
            'title' => 'ICOGroup tổ chức kỷ niệm 43 năm Ngày Nhà giáo Việt Nam',
            'slug' => 'icogroup-ky-niem-43-nam-ngay-nha-giao',
            'excerpt' => 'ICOGroup tổ chức lễ kỷ niệm 43 năm Ngày Nhà giáo Việt Nam và khai trương Trung tâm Đào tạo lái xe ICO. Sự kiện quy tụ đông đảo cán bộ, giáo viên và nhân viên trong toàn hệ thống.',
            'content' => '<p>Ngày 20/11, ICOGroup đã long trọng tổ chức lễ kỷ niệm 43 năm Ngày Nhà giáo Việt Nam (20/11/1982 - 20/11/2025). Đây là dịp để tri ân các thầy cô giáo đã cống hiến cho sự nghiệp đào tạo nguồn nhân lực chất lượng cao.</p><p>Trong khuôn khổ sự kiện, ICOGroup cũng khai trương Trung tâm Đào tạo lái xe ICO - một bước tiến mới trong chiến lược phát triển toàn diện của tập đoàn.</p>',
            'image_url' => 'https://icogroup.vn/vnt_upload/news/11_2024/43_NAM_NGAY_NHA_GIAO_VN_1.jpg',
            'category' => 'su-kien',
            'is_featured' => 1
        ],
        [
            'title' => 'Trường Đại học Pukyong Hàn Quốc làm việc tại ICOGroup',
            'slug' => 'truong-pukyong-lam-viec-tai-icogroup',
            'excerpt' => 'Trường Đại học Quốc gia Pukyong (Hàn Quốc) đến thăm và làm việc tại trụ sở ICOGroup, mở ra cơ hội hợp tác trong lĩnh vực du học và đào tạo.',
            'content' => '<p>Đoàn đại biểu từ Trường Đại học Quốc gia Pukyong (PKNU), Hàn Quốc đã có chuyến thăm và làm việc tại trụ sở ICOGroup. Buổi làm việc tập trung thảo luận về các chương trình hợp tác du học, trao đổi sinh viên và nghiên cứu khoa học.</p><p>PKNU là một trong những trường đại học hàng đầu tại Hàn Quốc, nổi tiếng với chất lượng đào tạo và nghiên cứu trong các lĩnh vực công nghệ, thủy sản và kinh tế biển.</p>',
            'image_url' => 'https://icogroup.vn/vnt_upload/news/11_2024/TRUONG_DAI_HOC_PUKYONG.jpg',
            'category' => 'tin-tuc',
            'is_featured' => 1
        ],
        [
            'title' => 'ICOGroup tiếp đón Tập đoàn Kaisei Nhật Bản',
            'slug' => 'icogroup-tiep-don-kaisei',
            'excerpt' => 'ICOGroup vinh dự tiếp đón và làm việc với Tập đoàn Giáo dục Kaisei - một trong những tập đoàn giáo dục lớn nhất Nhật Bản.',
            'content' => '<p>Lãnh đạo ICOGroup đã có buổi tiếp đón và làm việc với đoàn đại biểu từ Tập đoàn Giáo dục Kaisei (Nhật Bản). Hai bên đã trao đổi về các chương trình hợp tác trong lĩnh vực du học Nhật Bản.</p><p>Tập đoàn Kaisei sở hữu nhiều trường học và trung tâm đào tạo tại Nhật Bản, là đối tác chiến lược trong việc đào tạo nguồn nhân lực chất lượng cao.</p>',
            'image_url' => 'https://icogroup.vn/vnt_upload/news/11_2024/KAISEI_1.jpg',
            'category' => 'tin-tuc',
            'is_featured' => 0
        ],
        [
            'title' => 'Hội thảo Du học Đức 2024 - Cơ hội việc làm và định cư',
            'slug' => 'hoi-thao-du-hoc-duc-2024',
            'excerpt' => 'ICOGroup tổ chức hội thảo chia sẻ cơ hội du học Đức với học bổng hấp dẫn và cơ hội việc làm sau tốt nghiệp.',
            'content' => '<p>Hội thảo "Du học Đức 2024 - Cơ hội việc làm và định cư" được tổ chức nhằm cung cấp thông tin chi tiết về các chương trình du học tại Đức. Tham dự hội thảo, các bạn trẻ sẽ được tư vấn trực tiếp bởi các chuyên gia giáo dục quốc tế.</p><p>Đức là điểm đến du học lý tưởng với chi phí hợp lý, chất lượng giáo dục hàng đầu và cơ hội việc làm cao sau tốt nghiệp.</p>',
            'image_url' => 'https://icogroup.vn/vnt_upload/news/09_2024/du_hoc_duc.jpg',
            'category' => 'su-kien',
            'is_featured' => 1
        ],
        [
            'title' => 'Thông báo tuyển sinh Du học Nhật Bản kỳ tháng 4/2025',
            'slug' => 'tuyen-sinh-du-hoc-nhat-ky-4-2025',
            'excerpt' => 'ICOGroup thông báo tuyển sinh chương trình Du học Nhật Bản kỳ tháng 4/2025 với nhiều ưu đãi hấp dẫn.',
            'content' => '<p>ICOGroup chính thức mở đợt tuyển sinh Du học Nhật Bản kỳ nhập học tháng 4/2025. Đây là kỳ nhập học lớn nhất trong năm tại Nhật Bản với nhiều suất học bổng và ưu đãi đặc biệt.</p><p>Ứng viên đăng ký sớm sẽ được hỗ trợ học phí tiếng Nhật và miễn phí dịch vụ hồ sơ xin visa.</p>',
            'image_url' => 'https://icogroup.vn/vnt_upload/news/10_2024/tuyen_sinh_nhat.jpg',
            'category' => 'thong-bao',
            'is_featured' => 0
        ],
        [
            'title' => 'ICOGroup ký kết hợp tác với 5 trường Đại học Hàn Quốc',
            'slug' => 'icogroup-ky-ket-hop-tac-5-truong-han-quoc',
            'excerpt' => 'Lễ ký kết hợp tác chiến lược giữa ICOGroup và 5 trường Đại học hàng đầu Hàn Quốc đã diễn ra thành công tốt đẹp.',
            'content' => '<p>ICOGroup đã ký kết biên bản hợp tác với 5 trường Đại học danh tiếng tại Hàn Quốc, bao gồm: Đại học Quốc gia Seoul, Đại học Yonsei, Đại học Korea, Đại học Sungkyunkwan và Đại học Hanyang.</p><p>Thỏa thuận hợp tác mở ra nhiều cơ hội học bổng và chương trình trao đổi sinh viên cho các bạn trẻ Việt Nam.</p>',
            'image_url' => 'https://icogroup.vn/vnt_upload/news/08_2024/ky_ket_han_quoc.jpg',
            'category' => 'tin-tuc',
            'is_featured' => 1
        ]
    ];
    
    echo "\n📝 Đang thêm tin tức mẫu...\n";
    
    $added = 0;
    $skipped = 0;
    
    foreach ($sample_news as $news) {
        // Check if slug exists
        $check = $conn->prepare("SELECT id FROM news WHERE slug = ?");
        $check->bind_param("s", $news['slug']);
        $check->execute();
        $result = $check->get_result();
        
        if ($result->num_rows > 0) {
            echo "⏭️  SKIP: {$news['title']} (đã tồn tại)\n";
            $skipped++;
            $check->close();
            continue;
        }
        $check->close();
        
        // Insert news
        $stmt = $conn->prepare("INSERT INTO news (title, slug, excerpt, content, image_url, category, is_featured, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'published')");
        $stmt->bind_param("ssssssi", 
            $news['title'], 
            $news['slug'], 
            $news['excerpt'], 
            $news['content'], 
            $news['image_url'], 
            $news['category'], 
            $news['is_featured']
        );
        
        if ($stmt->execute()) {
            echo "✅ ADD: {$news['title']}\n";
            $added++;
        } else {
            echo "❌ ERROR: {$news['title']} - " . $stmt->error . "\n";
        }
        $stmt->close();
    }
    
    echo "\n=================================\n";
    echo "Tổng cộng: " . count($sample_news) . " tin\n";
    echo "Đã thêm: $added\n";
    echo "Bỏ qua: $skipped\n";
    echo "</pre>";
    
    // Verify
    $count = $conn->query("SELECT COUNT(*) as total FROM news WHERE status = 'published'")->fetch_assoc()['total'];
    echo "<p><strong>✅ Hiện có $count tin tức đã xuất bản trong database.</strong></p>";
    
    echo "<h2>🔗 Kiểm tra</h2>";
    echo "<ul>";
    echo "<li><a href='backend_api/news_api.php' target='_blank'>API Tin tức (JSON)</a></li>";
    echo "<li><a href='fonend/index.php' target='_blank'>Trang chủ</a></li>";
    echo "<li><a href='admin/news.php' target='_blank'>Admin - Quản lý tin tức</a></li>";
    echo "</ul>";
    
    echo "<p style='color: red;'><strong>⚠️ QUAN TRỌNG: Xóa file seed_news.php sau khi hoàn tất!</strong></p>";
    
} catch (Exception $e) {
    echo "❌ Lỗi: " . $e->getMessage() . "\n";
}

closeConnection($conn);
?>
