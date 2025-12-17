<?php
$pageTitle = "Hoạt động";
$pageDescription = "Tin tức và hoạt động nổi bật của ICOGroup - Cập nhật các sự kiện, hội thảo, chương trình hướng nghiệp.";
include 'includes/header.php';
?>

<!-- PAGE BANNER -->
<section class="page-banner">
    <h1>Tin Tức & Hoạt Động</h1>
    <p>Cập nhật những thông tin mới nhất từ ICOGroup</p>
    <div class="breadcrumb">
        <a href="index.php">Trang chủ</a>
        <span>/</span>
        <span>Hoạt động</span>
    </div>
</section>

<!-- NEWS GRID -->
<section class="section news-section">
    <div class="container">
        <div class="news-grid">
            <?php
            // Fetch all news from API
            $newsData = @file_get_contents('http://localhost/web8s/backend_api/news_api.php');
            if ($newsData) {
                $newsItems = json_decode($newsData, true);
                if (is_array($newsItems) && count($newsItems) > 0) {
                    foreach ($newsItems as $news) {
                        $image = !empty($news['image_url']) ? $news['image_url'] : 'https://via.placeholder.com/400x250?text=No+Image';
                        $title = htmlspecialchars($news['title']);
                        $date = date('d/m/Y', strtotime($news['created_at']));
                        $newsId = $news['id'];
                        ?>
                        <div class="news-card" onclick="window.location='tin-tuc.php?id=<?php echo $newsId; ?>'" style="cursor:pointer">
                            <div class="news-image">
                                <img src="<?php echo $image; ?>" alt="<?php echo $title; ?>">
                            </div>
                            <div class="news-content">
                                <div class="news-date">📅 <?php echo $date; ?></div>
                                <h3><?php echo $title; ?></h3>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    echo '<p style="text-align:center; color:#888; grid-column: 1/-1;">Chưa có tin tức nào. Vui lòng thêm tin tức trong trang quản trị.</p>';
                }
            } else {
                echo '<p style="text-align:center; color:#888; grid-column: 1/-1;">Không thể tải tin tức.</p>';
            }
            ?>
        </div>
        
        <div style="text-align: center; margin-top: 40px;">
            <button class="hero-btn" style="background: var(--primary-blue);">Xem thêm tin tức</button>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
