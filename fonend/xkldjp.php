<?php
include_once 'includes/content_helper.php';
$pageTitle = "XKLĐ Nhật Bản";
$pageDescription = "Xuất khẩu lao động Nhật Bản - Chương trình thực tập sinh kỹ năng với thu nhập 30-40 triệu/tháng.";
include 'includes/header.php';
?>

<!-- PAGE BANNER -->
<!-- PAGE BANNER -->
<?php
$header_bg = get_image('xkldjp_header_bg', '');
$header_style = $header_bg ? "background: url('$header_bg') no-repeat center center/cover;" : "background: linear-gradient(135deg, #BC002D, #fff);";
?>
<section class="page-banner" style="<?php echo $header_style; ?>">
    <h1><?php echo_text('xkldjp_title', 'Xuất Khẩu Lao Động Nhật Bản'); ?></h1>
    <p><?php echo_text('xkldjp_subtitle', 'Chương trình thực tập sinh kỹ năng - Thu nhập cao, môi trường làm việc tốt'); ?></p>
    <div class="breadcrumb">
        <a href="index.php">Trang chủ</a>
        <span>/</span>
        <span>XKLĐ Nhật Bản</span>
    </div>
</section>

<!-- INTRO - Section 1 -->
<?php if (is_section_visible('xkldjp', 1)): ?>
<section class="section about-section">
    <div class="container">
        <div class="about-grid">
            <div class="about-image">
                <img src="<?php echo get_image('xkldjp_intro_img', 'https://icogroup.vn/vnt_upload/weblink/banner_chu_04.jpg'); ?>" alt="XKLĐ Nhật Bản">
            </div>
            <div class="about-content">
                <h3><?php echo_text('xkldjp_intro_title', 'Chương Trình Thực Tập Sinh Kỹ Năng'); ?></h3>
                <p><?php echo_text('xkldjp_intro_desc', 'Nhật Bản là điểm đến hàng đầu của lao động Việt Nam với môi trường làm việc chuyên nghiệp, thu nhập cao và nhiều cơ hội phát triển.'); ?></p>
                
                <div class="about-values">
                    <div class="value-item"><span><?php echo_text('xkldjp_benefit_1', '💰 Thu nhập 30-40 triệu/tháng'); ?></span></div>
                    <div class="value-item"><span><?php echo_text('xkldjp_benefit_2', '🏠 Hỗ trợ chỗ ở miễn phí'); ?></span></div>
                    <div class="value-item"><span><?php echo_text('xkldjp_benefit_3', '✈️ Bay 0 đồng'); ?></span></div>
                    <div class="value-item"><span><?php echo_text('xkldjp_benefit_4', '📋 Hợp đồng 3 năm'); ?></span></div>
                    <div class="value-item"><span><?php echo_text('xkldjp_benefit_5', '🛡️ Bảo hiểm đầy đủ'); ?></span></div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- JOBS - Section 2 -->
<?php if (is_section_visible('xkldjp', 2)): ?>
<section class="section ecosystem-section">
    <div class="container">
        <div class="section-header">
            <h2>Ngành Nghề Tuyển Dụng</h2>
        </div>
        <div class="ecosystem-grid">
            <div class="ecosystem-card"><div class="ecosystem-icon">🔧</div><h3>Cơ khí</h3><p>Hàn, tiện, phay, CNC...</p></div>
            <div class="ecosystem-card"><div class="ecosystem-icon">🏗️</div><h3>Xây dựng</h3><p>Xây, trát, cốp pha, giàn giáo...</p></div>
            <div class="ecosystem-card"><div class="ecosystem-icon">🍱</div><h3>Chế biến thực phẩm</h3><p>Đóng gói, chế biến, làm bánh...</p></div>
            <div class="ecosystem-card"><div class="ecosystem-icon">🌾</div><h3>Nông nghiệp</h3><p>Trồng rau, nuôi trồng thủy sản...</p></div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- CTA -->
<section class="form-section">
    <div class="form-container" style="text-align: center;">
        <h3>🇯🇵 Đăng Ký XKLĐ Nhật Bản</h3>
        <p style="margin-bottom: 30px; color: #666;">Hotline: <strong>0822.314.555</strong> • Địa chỉ: Số 360, Phan Đình Phùng, Thái Nguyên</p>
        <a href="index.php#dangky" class="hero-btn">Đăng ký ngay</a>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
