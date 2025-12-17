<?php
$pageTitle = "Chi tiết tin tức";
$pageDescription = "Tin tức chi tiết từ ICOGroup";

// Get news ID from URL
$newsId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch news detail from API
$newsDetail = null;
if ($newsId > 0) {
    $newsData = @file_get_contents('http://localhost/web8s/backend_api/news_api.php?id=' . $newsId);
    if ($newsData) {
        $newsDetail = json_decode($newsData, true);
        if ($newsDetail && isset($newsDetail['title'])) {
            $pageTitle = $newsDetail['title'];
        }
    }
}

include 'includes/header.php';
?>

<!-- PAGE BANNER -->
<section class="page-banner">
    <h1>Chi Tiết Tin Tức</h1>
    <div class="breadcrumb">
        <a href="index.php">Trang chủ</a>
        <span>/</span>
        <a href="hoatdong.php">Hoạt động</a>
        <span>/</span>
        <span>Chi tiết</span>
    </div>
</section>

<!-- NEWS DETAIL -->
<section class="section">
    <div class="container">
        <?php if ($newsDetail): ?>
            <article style="max-width: 900px; margin: 0 auto; background: white; padding: 40px; border-radius: 15px; box-shadow: 0 5px 30px rgba(0,0,0,0.1);">
                
                <!-- News Header -->
                <div style="margin-bottom: 30px;">
                    <span style="background: var(--primary-blue); color: white; padding: 5px 15px; border-radius: 20px; font-size: 14px;">
                        <?php echo htmlspecialchars($newsDetail['category'] ?? 'Tin tức'); ?>
                    </span>
                    <div style="margin-top: 15px; color: #666; font-size: 14px;">
                        📅 <?php echo date('d/m/Y H:i', strtotime($newsDetail['created_at'])); ?>
                    </div>
                </div>
                
                <!-- Title -->
                <h1 style="font-size: 32px; color: var(--primary-blue); margin-bottom: 25px; line-height: 1.4;">
                    <?php echo htmlspecialchars($newsDetail['title']); ?>
                </h1>
                
                <!-- Excerpt -->
                <?php if (!empty($newsDetail['excerpt'])): ?>
                <p style="font-size: 18px; color: #555; font-style: italic; margin-bottom: 25px; padding: 15px; background: #f8f9fa; border-left: 4px solid var(--accent-orange); border-radius: 0 8px 8px 0;">
                    <?php echo htmlspecialchars($newsDetail['excerpt']); ?>
                </p>
                <?php endif; ?>
                
                <!-- Featured Image -->
                <?php if (!empty($newsDetail['image_url'])): ?>
                <div style="margin-bottom: 30px; text-align: center;">
                    <img src="<?php echo htmlspecialchars($newsDetail['image_url']); ?>" 
                         alt="<?php echo htmlspecialchars($newsDetail['title']); ?>"
                         style="max-width: 100%; height: auto; border-radius: 12px; box-shadow: 0 5px 20px rgba(0,0,0,0.15);">
                </div>
                <?php endif; ?>
                
                <!-- Content -->
                <div style="font-size: 16px; line-height: 1.8; color: #333;">
                    <?php echo nl2br(htmlspecialchars($newsDetail['content'] ?? '')); ?>
                </div>
                
                <!-- Back Button -->
                <div style="margin-top: 40px; text-align: center;">
                    <a href="hoatdong.php" class="hero-btn" style="display: inline-block;">
                        ← Quay lại danh sách tin tức
                    </a>
                </div>
                
            </article>
        <?php else: ?>
            <div style="text-align: center; padding: 60px 20px;">
                <h2 style="color: #888;">Không tìm thấy tin tức</h2>
                <p style="color: #aaa; margin: 20px 0;">Tin tức này không tồn tại hoặc đã bị xóa.</p>
                <a href="hoatdong.php" class="hero-btn">Xem tất cả tin tức</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
