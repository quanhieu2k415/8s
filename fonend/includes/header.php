<?php
// Header component - ICOGroup Website
include_once __DIR__ . '/content_helper.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ICOGroup' : 'ICOGroup - Tập đoàn Giáo dục và Đào tạo nghề hàng đầu Việt Nam'; ?></title>
    <meta name="description" content="<?php echo isset($pageDescription) ? $pageDescription : 'ICOGroup - Tổ chức Giáo dục và Nhân lực Quốc tế. Du học Nhật Bản, Đức, Hàn Quốc. Xuất khẩu lao động uy tín.'; ?>">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="animations.css">
    <link rel="icon" type="image/x-icon" href="../logo.ico">
</head>
<body>

<!-- TOP BAR - Modern Design -->
<div class="top-bar">
    <div class="top-bar-container">
        <div class="top-bar-left">
            <a href="tel:<?php echo get_text('header_phone', '0822314555'); ?>" class="top-bar-item">
                <span class="material-symbols-outlined">call</span>
                <span><?php echo get_text('header_phone_display', '0822.314.555'); ?></span>
            </a>
            <a href="mailto:<?php echo get_text('header_email', 'info@icogroup.vn'); ?>" class="top-bar-item">
                <span class="material-symbols-outlined">mail</span>
                <span><?php echo get_text('header_email', 'info@icogroup.vn'); ?></span>
            </a>
        </div>
        <div class="top-bar-right">
            <?php 
            $fb_icon = get_image('global_facebook_icon', '');
            $yt_icon = get_image('global_youtube_icon', '');
            $zalo_icon = get_image('global_zalo_icon', '');
            ?>
            <a href="<?php echo get_text('global_facebook_url', 'https://facebook.com/icogroup'); ?>" target="_blank" class="social-icon" title="Facebook">
                <?php if ($fb_icon): ?>
                    <img src="<?php echo $fb_icon; ?>" alt="Facebook" style="width: 16px; height: 16px; border-radius: 50%; object-fit: cover;">
                <?php else: ?>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                <?php endif; ?>
            </a>
            <a href="<?php echo get_text('global_youtube_url', 'https://youtube.com/icogroup'); ?>" target="_blank" class="social-icon" title="YouTube">
                <?php if ($yt_icon): ?>
                    <img src="<?php echo $yt_icon; ?>" alt="YouTube" style="width: 16px; height: 16px; border-radius: 50%; object-fit: cover;">
                <?php else: ?>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                <?php endif; ?>
            </a>
            <a href="<?php echo get_text('global_zalo_url', 'https://zalo.me/icogroup'); ?>" target="_blank" class="social-icon" title="Zalo">
                <?php if ($zalo_icon): ?>
                    <img src="<?php echo $zalo_icon; ?>" alt="Zalo" style="width: 16px; height: 16px; border-radius: 50%; object-fit: cover;">
                <?php else: ?>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.894 8.221l-1.97 5.28c-.096.288-.444.48-.756.48H9.892c-.312 0-.66-.192-.756-.48l-1.97-5.28c-.168-.456.144-.936.636-.936h.924c.312 0 .588.216.684.504l1.356 4.032h2.468l1.356-4.032c.096-.288.372-.504.684-.504h.924c.492 0 .804.48.636.936z"/></svg>
                <?php endif; ?>
            </a>
            <a href="index.php#dangky" class="top-bar-cta">
                <span class="material-symbols-outlined">app_registration</span>
                <span>Đăng ký tư vấn</span>
            </a>
        </div>
    </div>
</div>

<!-- NAVIGATION - Modern Design -->
<nav>
    <a href="index.php" class="nav-logo">
        <img src="https://www.icogroup.vn/vnt_upload/company/Logo_icogroup4x.png" alt="ICOGroup Logo">
    </a>

    <ul>
        <li><a href="index.php"><?php echo get_text('menu_trangchu', 'Trang chủ'); ?></a></li>
        
        <li>
            <a href="ve-icogroup.php"><?php echo get_text('menu_veicogroup', 'Về ICOGroup'); ?></a>
        </li>

        <li class="has-submenu">
            <a href="#"><?php echo get_text('menu_duhoc', 'Du học'); ?> <span class="material-symbols-outlined arrow-icon">expand_more</span></a>
            <ul class="submenu">
                <?php if(get_text('menu_duhoc_germany_visible', '1') == '1'): ?>
                <li><a href="duc.php"><span class="flag">🇩🇪</span> <?php echo get_text('menu_duhoc_germany', 'Du học Đức'); ?></a></li>
                <?php endif; ?>
                <?php if(get_text('menu_duhoc_japan_visible', '1') == '1'): ?>
                <li><a href="nhat.php"><span class="flag">🇯🇵</span> <?php echo get_text('menu_duhoc_japan', 'Du học Nhật'); ?></a></li>
                <?php endif; ?>
                <?php if(get_text('menu_duhoc_korea_visible', '1') == '1'): ?>
                <li><a href="han.php"><span class="flag">🇰🇷</span> <?php echo get_text('menu_duhoc_korea', 'Du học Hàn Quốc'); ?></a></li>
                <?php endif; ?>
            </ul>
        </li>

        <li class="has-submenu">
            <a href="#"><?php echo get_text('menu_xkld', 'Xuất khẩu lao động'); ?> <span class="material-symbols-outlined arrow-icon">expand_more</span></a>
            <ul class="submenu">
                <?php if(get_text('menu_xkld_japan_visible', '1') == '1'): ?>
                <li><a href="xkldjp.php"><span class="flag">🇯🇵</span> <?php echo get_text('menu_xkld_japan', 'Nhật Bản'); ?></a></li>
                <?php endif; ?>
                <?php if(get_text('menu_xkld_korea_visible', '1') == '1'): ?>
                <li><a href="xkldhan.php"><span class="flag">🇰🇷</span> <?php echo get_text('menu_xkld_korea', 'Hàn Quốc'); ?></a></li>
                <?php endif; ?>
                <?php if(get_text('menu_xkld_taiwan_visible', '1') == '1'): ?>
                <li><a href="xklddailoan.php"><span class="flag">🇹🇼</span> <?php echo get_text('menu_xkld_taiwan', 'Đài Loan'); ?></a></li>
                <?php endif; ?>
                <?php if(get_text('menu_xkld_eu_visible', '1') == '1'): ?>
                <li><a href="xkldchauau.php"><span class="flag">🇪🇺</span> <?php echo get_text('menu_xkld_eu', 'Châu Âu'); ?></a></li>
                <?php endif; ?>
            </ul>
        </li>

        <li><a href="huong-nghiep.php"><?php echo get_text('menu_huongnghiep', 'Hướng nghiệp'); ?></a></li>
        <li><a href="hoatdong.php"><?php echo get_text('menu_hoatdong', 'Hoạt động'); ?></a></li>
        <li><a href="lienhe.php"><?php echo get_text('menu_lienhe', 'Liên hệ'); ?></a></li>
        <li><a href="search.php" title="Tìm kiếm"><span class="material-symbols-outlined">search</span></a></li>
        <li><a href="index.php#dangky" class="btn-register"><?php echo get_text('menu_dangky', 'Đăng ký'); ?></a></li>
    </ul>

    <!-- Mobile Menu Button -->
    <button class="mobile-menu-btn" onclick="toggleMobileMenu()" aria-label="Menu">
        <span class="material-symbols-outlined">menu</span>
    </button>
</nav>

<!-- Mobile Menu Overlay -->
<div id="mobileMenu" class="mobile-menu">
    <div class="mobile-menu-header">
        <img src="https://www.icogroup.vn/vnt_upload/company/Logo_icogroup4x.png" alt="ICOGroup">
        <button onclick="toggleMobileMenu()" aria-label="Đóng menu">
            <span class="material-symbols-outlined">close</span>
        </button>
    </div>
    <ul>
        <li><a href="index.php">🏠 Trang chủ</a></li>
        <li><a href="ve-icogroup.php">ℹ️ Về ICOGroup</a></li>
        <li class="mobile-section-title">Du học</li>
        <li><a href="duc.php">🇩🇪 Du học Đức</a></li>
        <li><a href="nhat.php">🇯🇵 Du học Nhật</a></li>
        <li><a href="han.php">🇰🇷 Du học Hàn Quốc</a></li>
        <li class="mobile-section-title">Xuất khẩu lao động</li>
        <li><a href="xkldjp.php">🇯🇵 XKLĐ Nhật Bản</a></li>
        <li><a href="xkldhan.php">🇰🇷 XKLĐ Hàn Quốc</a></li>
        <li><a href="xklddailoan.php">🇹🇼 XKLĐ Đài Loan</a></li>
        <li><a href="xkldchauau.php">🇪🇺 XKLĐ Châu Âu</a></li>
        <li class="mobile-section-title">Khác</li>
        <li><a href="huong-nghiep.php">🎯 Hướng nghiệp</a></li>
        <li><a href="hoatdong.php">📰 Hoạt động</a></li>
        <li><a href="lienhe.php">📞 Liên hệ</a></li>
        <li><a href="search.php">🔍 Tìm kiếm</a></li>
    </ul>
    <div class="mobile-menu-footer">
        <a href="index.php#dangky" class="mobile-register-btn">
            <span class="material-symbols-outlined">app_registration</span>
            Đăng ký tư vấn miễn phí
        </a>
    </div>
</div>
