<?php
include_once 'includes/content_helper.php';
$pageTitle = "Hoạt động";
$pageDescription = "Tin tức và hoạt động nổi bật của ICOGroup - Cập nhật các sự kiện, hội thảo, chương trình hướng nghiệp.";
include 'includes/header.php';
?>

<!-- PAGE BANNER -->
<?php
$header_bg = get_image('hoatdong_header_bg', '');
$header_style = $header_bg ? "background: url('$header_bg') no-repeat center center/cover;" : "";
?>
<section class="page-banner" style="<?php echo $header_style; ?>">
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
        <style>
            .news-item {
                display: block; /* Default display */
            }
            .news-item.hidden-news {
                display: none;
            }
            .news-grid {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 30px;
            }
            @media (max-width: 992px) {
                .news-grid {
                    grid-template-columns: repeat(2, 1fr);
                }
            }
            @media (max-width: 576px) {
                .news-grid {
                    grid-template-columns: 1fr;
                }
            }
        </style>
        <div class="news-grid" id="newsGrid">
            <?php
            // Fetch more news from API (limit 100 to support client-side pagination)
            $newsData = @file_get_contents('http://localhost/web8s/backend_api/news_api.php?limit=100');
            $hasItems = false;
            
            if ($newsData) {
                $newsItems = json_decode($newsData, true);
                if (is_array($newsItems) && count($newsItems) > 0) {
                    $hasItems = true;
                    $index = 0;
                    foreach ($newsItems as $news) {
                        $image = !empty($news['image_url']) ? $news['image_url'] : 'https://via.placeholder.com/400x250?text=No+Image';
                        $title = htmlspecialchars($news['title']);
                        $date = date('d/m/Y', strtotime($news['created_at']));
                        $newsId = $news['id'];
                        
                        // Hide items after the 9th one (index 8)
                        $hiddenClass = ($index >= 9) ? 'hidden-news' : '';
                        ?>
                        <div class="news-card news-item <?php echo $hiddenClass; ?>" onclick="window.location='tin-tuc.php?id=<?php echo $newsId; ?>'" style="cursor:pointer">
                            <div class="news-image">
                                <img src="<?php echo $image; ?>" alt="<?php echo $title; ?>">
                            </div>
                            <div class="news-content">
                                <div class="news-date">📅 <?php echo $date; ?></div>
                                <h3><?php echo $title; ?></h3>
                            </div>
                        </div>
                        <?php
                        $index++;
                    }
                } else {
                    echo '<p style="text-align:center; color:#888; grid-column: 1/-1;">Chưa có tin tức nào. Vui lòng thêm tin tức trong trang quản trị.</p>';
                }
            } else {
                echo '<p style="text-align:center; color:#888; grid-column: 1/-1;">Không thể tải tin tức.</p>';
            }
            ?>
        </div>
        
        <?php if ($hasItems && count($newsItems) > 9): ?>
        <div style="text-align: center; margin-top: 40px;" id="loadMoreContainer">
            <button class="hero-btn" style="background: var(--primary-blue);" onclick="loadMoreNews()">Xem thêm tin tức</button>
        </div>
        <?php endif; ?>
    </div>
</section>

<script>
    function loadMoreNews() {
        const hiddenItems = document.querySelectorAll('.news-item.hidden-news');
        const itemsToShow = 6;
        let count = 0;
        
        for (let i = 0; i < hiddenItems.length; i++) {
            if (count < itemsToShow) {
                hiddenItems[i].classList.remove('hidden-news');
                count++;
            } else {
                break;
            }
        }
        
        // Hide button if no more hidden items
        if (document.querySelectorAll('.news-item.hidden-news').length === 0) {
            document.getElementById('loadMoreContainer').style.display = 'none';
        }
    }
</script>

<?php include 'includes/footer.php'; ?>
