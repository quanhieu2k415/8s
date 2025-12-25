<?php
include_once 'includes/content_helper.php';
$pageTitle = "XKLĐ Đài Loan";
$pageDescription = "Xuất khẩu lao động Đài Loan - Chi phí thấp, thu nhập ổn định.";
include 'includes/header.php';
?>

<?php
$header_bg = get_image('xklddailoan_header_bg', '');
$header_style = $header_bg ? "background: url('$header_bg') no-repeat center center/cover;" : "background: linear-gradient(135deg, #FE0000, #fff);";
?>
<section class="page-banner" style="<?php echo $header_style; ?>">
    <h1>🇹🇼 <?php echo get_text('xklddailoan_title', 'Xuất Khẩu Lao Động Đài Loan'); ?></h1>
    <p><?php echo get_text('xklddailoan_subtitle', 'Chi phí thấp - Thu nhập ổn định - Cơ hội phát triển'); ?></p>
    <div class="breadcrumb">
        <a href="index.php">Trang chủ</a>
        <span>/</span>
        <span>XKLĐ Đài Loan</span>
    </div>
</section>

<!-- INTRO - Section 1 -->
<?php if (is_section_visible('xklddailoan', 1)): ?>
<section class="section about-section">
    <div class="container">
        <div class="about-grid">
            <div class="about-content">
                <h3><?php echo get_text('xklddailoan_program_title', 'Lao Động Đài Loan'); ?></h3>
                <p><?php echo get_text('xklddailoan_program_desc', 'Đài Loan là thị trường lao động hấp dẫn với chi phí xuất cảnh thấp, ngôn ngữ dễ học và văn hóa gần gũi với Việt Nam.'); ?></p>
                <div class="about-values">
                    <div class="value-item"><span>💰</span><span><?php echo get_text('xklddailoan_benefit_1', 'Thu nhập 20-30 triệu/tháng'); ?></span></div>
                    <div class="value-item"><span>💵</span><span><?php echo get_text('xklddailoan_benefit_2', 'Chi phí xuất cảnh thấp'); ?></span></div>
                    <div class="value-item"><span>🗣️</span><span><?php echo get_text('xklddailoan_benefit_3', 'Ngôn ngữ dễ học'); ?></span></div>
                </div>
            </div>
            <div class="about-image">
                <img src="<?php echo get_image('xklddailoan_main_img', 'https://icogroup.vn/vnt_upload/weblink/banner_chu_04.jpg'); ?>" alt="XKLĐ Đài Loan">
            </div>
        </div>
    </div>
</section>
<?php endif; ?>


<!-- DYNAMIC CONTENT BLOCKS -->
<?php
$blocks = get_content_blocks('xklddailoan');
if (!empty($blocks)):
?>
<section class="section content-blocks-section">
    <div class="container">
        <?php foreach ($blocks as $block): ?>
        <div class="dynamic-content-block block-type-<?php echo htmlspecialchars($block['block_type']); ?>">
            <?php if (!empty($block['title'])): ?>
            <div class="block-title-display">
                <?php echo render_html($block['title']); ?>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($block['image_url'])): ?>
            <div class="block-image-display">
                <img src="<?php echo htmlspecialchars($block['image_url']); ?>" alt="">
            </div>
            <?php endif; ?>
            
            <?php if (!empty($block['content'])): ?>
            <div class="block-content-display">
                <?php echo render_html($block['content']); ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>
<section class="form-section">
    <div class="form-container" style="text-align: center;">
        <h3><?php echo get_text('xklddailoan_cta_title', 'Đăng Ký XKLĐ Đài Loan'); ?></h3>
        <p style="margin-bottom: 30px; color: #666;">Hotline: <strong><?php echo get_text('header_phone_display', '0822.314.555'); ?></strong></p>
        <a href="index.php#dangky" class="hero-btn">Đăng ký ngay</a>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
