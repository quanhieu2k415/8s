<?php
// Header component - ICOGroup Website
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
</head>
<body>

<!-- TOP BAR -->
<div class="top-bar">
    <span>📞 Hotline: 0822.314.555</span>
    <a href="index.php#dangky">Đăng ký tìm hiểu</a>
</div>

<!-- NAVIGATION -->
<nav>
    <a href="index.php" class="nav-logo">
        <img src="https://www.icogroup.vn/vnt_upload/company/Logo_icogroup4x.png" alt="ICOGroup Logo">
    </a>

    <ul>
        <li><a href="index.php">Trang chủ</a></li>
        
        <li>
            <a href="ve-icogroup.php">Về ICOGroup</a>
        </li>

        <li class="has-submenu">
            <a href="#">Du học <span class="arrow">▼</span></a>
            <ul class="submenu">
                <li><a href="duc.php">Du học Đức</a></li>
                <li><a href="nhat.php">Du học Nhật</a></li>
                <li><a href="han.php">Du học Hàn Quốc</a></li>
            </ul>
        </li>

        <li class="has-submenu">
            <a href="#">Xuất khẩu lao động <span class="arrow">▼</span></a>
            <ul class="submenu">
                <li><a href="xkldjp.php">Nhật Bản</a></li>
                <li><a href="xkldhan.php">Hàn Quốc</a></li>
                <li><a href="xklddailoan.php">Đài Loan</a></li>
                <li><a href="xkldchauau.php">Châu Âu</a></li>
            </ul>
        </li>

        <li><a href="huong-nghiep.php">Hướng nghiệp</a></li>
        <li><a href="hoatdong.php">Hoạt động</a></li>
        <li><a href="lienhe.php">Liên hệ</a></li>
        <li><a href="index.php#dangky" class="btn-register">Đăng ký</a></li>
    </ul>

    <!-- Mobile Menu Button -->
    <button class="mobile-menu-btn" onclick="toggleMobileMenu()">
        <span class="material-symbols-outlined">menu</span>
    </button>
</nav>

<!-- Mobile Menu Overlay -->
<div id="mobileMenu" class="mobile-menu">
    <div class="mobile-menu-header">
        <img src="https://www.icogroup.vn/vnt_upload/company/Logo_icogroup4x.png" alt="ICOGroup">
        <button onclick="toggleMobileMenu()">
            <span class="material-symbols-outlined">close</span>
        </button>
    </div>
    <ul>
        <li><a href="index.php">Trang chủ</a></li>
        <li><a href="ve-icogroup.php">Về ICOGroup</a></li>
        <li><a href="duc.php">Du học Đức</a></li>
        <li><a href="nhat.php">Du học Nhật</a></li>
        <li><a href="han.php">Du học Hàn Quốc</a></li>
        <li><a href="xkldjp.php">XKLĐ Nhật Bản</a></li>
        <li><a href="xkldhan.php">XKLĐ Hàn Quốc</a></li>
        <li><a href="huong-nghiep.php">Hướng nghiệp</a></li>
        <li><a href="hoatdong.php">Hoạt động</a></li>
        <li><a href="lienhe.php">Liên hệ</a></li>
    </ul>
</div>
