<?php
include_once 'includes/content_helper.php';
$pageTitle = "XKLĐ Châu Âu";
$pageDescription = "Xuất khẩu lao động Châu Âu - Đức, Ba Lan, Romania với thu nhập cao.";
include 'includes/header.php';
?>

<?php
$header_bg = get_image('xkldchauau_header_bg', '');
$header_style = $header_bg ? "background: url('$header_bg') no-repeat center center/cover;" : "background: linear-gradient(135deg, #003399, #FFCC00);";
?>
<section class="page-banner" style="<?php echo $header_style; ?>">
    <h1>🇪🇺 <?php echo get_text('xkldchauau_title', 'Xuất Khẩu Lao Động Châu Âu'); ?> <span style="font-size: 0.5em; background: rgba(255,255,255,0.2); padding: 5px 15px; border-radius: 20px; vertical-align: middle;">EU</span></h1>
    <p><?php echo get_text('xkldchauau_subtitle', 'Cơ hội làm việc tại các nước phát triển Châu Âu'); ?></p>
    <div class="breadcrumb">
        <a href="index.php">Trang chủ</a>
        <span>/</span>
        <span>XKLĐ Châu Âu</span>
    </div>
</section>

<!-- INTRO - Section 1 -->
<?php if (is_section_visible('xkldchauau', 1)): ?>
<section class="section about-section">
    <div class="container">
        <div class="about-grid">
            <div class="about-content">
                <h3><?php echo get_text('xkldchauau_program_title', 'Lao Động Châu Âu'); ?></h3>
                <p><?php echo get_text('xkldchauau_program_desc', 'Châu Âu với các quốc gia phát triển như Đức, Ba Lan, Romania mở ra cơ hội việc làm với thu nhập cao và môi trường làm việc chuyên nghiệp.'); ?></p>
                <div class="about-values">
                    <div class="value-item"><span>💰</span><span><?php echo get_text('xkldchauau_benefit_1', 'Thu nhập 40-60 triệu/tháng'); ?></span></div>
                    <div class="value-item"><span>🏠</span><span><?php echo get_text('xkldchauau_benefit_2', 'Cơ hội định cư'); ?></span></div>
                    <div class="value-item"><span>🌍</span><span><?php echo get_text('xkldchauau_benefit_3', 'Du lịch Schengen tự do'); ?></span></div>
                </div>
            </div>
            <div class="about-image">
                <img src="<?php echo get_image('xkldchauau_main_img', 'https://icogroup.vn/vnt_upload/weblink/banner_chu_04.jpg'); ?>" alt="XKLĐ Châu Âu">
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- COUNTRIES - Section 2 -->
<?php if (is_section_visible('xkldchauau', 2)): ?>
<section class="section ecosystem-section">
    <div class="container">
        <div class="section-header"><h2><?php echo get_text('xkldchauau_countries_title', 'Các Quốc Gia Tuyển Dụng'); ?></h2></div>
        <div class="ecosystem-grid" style="grid-template-columns: repeat(3, 1fr);">
            <div class="ecosystem-card">
                <div class="ecosystem-icon">🇩🇪</div>
                <h3><?php echo get_text('xkldchauau_country_1_name', 'Đức'); ?></h3>
                <p><?php echo get_text('xkldchauau_country_1_desc', 'Điều dưỡng, cơ khí, nhà hàng khách sạn'); ?></p>
            </div>
            <div class="ecosystem-card">
                <div class="ecosystem-icon">🇵🇱</div>
                <h3><?php echo get_text('xkldchauau_country_2_name', 'Ba Lan'); ?></h3>
                <p><?php echo get_text('xkldchauau_country_2_desc', 'Nông nghiệp, chế biến thực phẩm, xây dựng'); ?></p>
            </div>
            <div class="ecosystem-card">
                <div class="ecosystem-icon">🇷🇴</div>
                <h3><?php echo get_text('xkldchauau_country_3_name', 'Romania'); ?></h3>
                <p><?php echo get_text('xkldchauau_country_3_desc', 'May mặc, điện tử, cơ khí'); ?></p>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="form-section">
    <div class="form-container" style="text-align: center;">
        <h3>🇪🇺 <?php echo get_text('xkldchauau_cta_title', 'Đăng Ký XKLĐ Châu Âu'); ?></h3>
        <p style="margin-bottom: 30px; color: #666;">Hotline: <strong><?php echo get_text('header_phone_display', '0822.314.555'); ?></strong> • Địa chỉ: <?php echo get_text('global_footer_address', 'Số 360, Phan Đình Phùng, Thái Nguyên'); ?></p>
        <a href="index.php#dangky" class="hero-btn">Đăng ký ngay</a>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
