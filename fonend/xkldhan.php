<?php
include_once 'includes/content_helper.php';
$pageTitle = "XKLĐ Hàn Quốc";
$pageDescription = "Xuất khẩu lao động Hàn Quốc - Chương trình EPS với thu nhập hấp dẫn.";
include 'includes/header.php';
?>

<?php
$header_bg = get_image('xkldhan_header_bg', '');
$header_style = $header_bg ? "background: url('$header_bg') no-repeat center center/cover;" : "background: linear-gradient(135deg, #0047A0, #CD2E3A);";
?>
<section class="page-banner" style="<?php echo $header_style; ?>">
    <h1><?php echo get_text('xkldhan_title', 'Xuất Khẩu Lao Động Hàn Quốc 🇰🇷'); ?></h1>
    <p><?php echo get_text('xkldhan_subtitle', 'Chương trình EPS - Cơ hội việc làm tại xứ sở kim chi'); ?></p>
</section>

<section class="section about-section">
    <div class="container">
        <div class="about-grid">
            <div class="about-content">
                <h3><?php echo get_text('xkldhan_program_title', 'Chương Trình EPS Hàn Quốc'); ?></h3>
                <p><?php echo get_text('xkldhan_program_desc', 'Chương trình cấp phép việc làm cho lao động nước ngoài (EPS) là chương trình chính thức của Chính phủ Hàn Quốc.'); ?></p>
                <div class="about-values">
                    <div class="value-item"><span>💰</span><span><?php echo get_text('xkldhan_benefit_1', 'Thu nhập 25-35 triệu/tháng'); ?></span></div>
                    <div class="value-item"><span>📋</span><span><?php echo get_text('xkldhan_benefit_2', 'Hợp đồng 4 năm 10 tháng'); ?></span></div>
                    <div class="value-item"><span>🛡️</span><span><?php echo get_text('xkldhan_benefit_3', 'Bảo hiểm xã hội đầy đủ'); ?></span></div>
                </div>
            </div>
            <div class="about-image">
                <img src="<?php echo get_image('xkldhan_main_img', 'https://icogroup.vn/vnt_upload/news/11_2024/TRUONG_DAI_HOC_PUKYONG.jpg'); ?>" alt="XKLĐ Hàn Quốc">
            </div>
        </div>
    </div>
</section>

<section class="form-section">
    <div class="form-container" style="text-align: center;">
        <h3>🇰🇷 <?php echo get_text('xkldhan_cta_title', 'Đăng Ký XKLĐ Hàn Quốc'); ?></h3>
        <p style="margin-bottom: 30px; color: #666;">Hotline: <strong><?php echo get_text('header_phone_display', '0822.314.555'); ?></strong></p>
        <a href="index.php#dangky" class="hero-btn">Đăng ký ngay</a>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
