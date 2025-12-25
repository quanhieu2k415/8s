<?php
include_once 'includes/content_helper.php';
$pageTitle = "Du học Hàn Quốc";
$pageDescription = "Du học Hàn Quốc với ICOGroup - Chi phí hợp lý, nền giáo dục tiên tiến, cơ hội việc làm sau tốt nghiệp.";
include 'includes/header.php';
?>

<!-- PAGE BANNER -->
<?php
$header_bg = get_image('han_header_bg', '');
$header_style = $header_bg ? "background: url('$header_bg') no-repeat center center/cover;" : "background: url('https://duhochanico.edu.vn/wp-content/uploads/2023/04/Banner-web-Han-1.png'); background-size: cover; background-position: center;";
?>
<section class="page-banner" style="<?php echo $header_style; ?>">
    <h1>🇰🇷 <?php echo get_text('han_title', 'Du Học Hàn Quốc'); ?></h1>
    <p><?php echo get_text('han_subtitle', 'Khám phá xứ sở kim chi - Điểm đến du học hấp dẫn'); ?></p>
    <div class="breadcrumb">
        <a href="index.php">Trang chủ</a>
        <span>/</span>
        <span>Du học Hàn Quốc</span>
    </div>
</section>

<!-- INTRO - Section 1 -->
<?php if (is_section_visible('han', 1)): ?>
<section class="section about-section">
    <div class="container">
        <div class="section-header">
            <h2><?php echo get_text('han_why_title', 'Tại Sao Chọn Du Học Hàn Quốc?'); ?></h2>
        </div>
        
        <div class="about-grid">
            <div class="about-image">
                <img src="<?php echo get_image('han_about_img', 'https://icogroup.vn/vnt_upload/news/11_2024/TRUONG_DAI_HOC_PUKYONG.jpg'); ?>" alt="Du học Hàn Quốc">
            </div>
            
            <div class="about-content">
                <h3><?php echo get_text('han_reason_title', 'Lý Do Du Học Hàn Quốc'); ?></h3>
                <p><?php echo get_text('han_reason_desc', 'Hàn Quốc là quốc gia phát triển với nền giáo dục đẳng cấp, văn hóa K-Pop hấp dẫn và cơ hội việc làm rộng mở.'); ?></p>
                
                <div class="about-values">
                    <div class="value-item"><span>💰</span><span><?php echo get_text('han_benefit_1', 'Chi phí thấp hơn Nhật, Mỹ'); ?></span></div>
                    <div class="value-item"><span>🎓</span><span><?php echo get_text('han_benefit_2', 'Nhiều học bổng hấp dẫn'); ?></span></div>
                    <div class="value-item"><span>💼</span><span><?php echo get_text('han_benefit_3', 'Làm thêm 20h/tuần'); ?></span></div>
                    <div class="value-item"><span>🌸</span><span><?php echo get_text('han_benefit_4', 'Văn hóa K-Pop, K-Drama'); ?></span></div>
                    <div class="value-item"><span>🏢</span><span><?php echo get_text('han_benefit_5', 'Nhiều tập đoàn lớn'); ?></span></div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- PROGRAMS - Section 2 -->
<?php if (is_section_visible('han', 2)): ?>
<section class="section programs-section" style="background: #f4f7fb;">
    <div class="container">
        <div class="section-header">
            <h2><?php echo get_text('han_programs_title', 'Chương Trình Du Học'); ?></h2>
        </div>
        
        <div class="programs-grid">
            <div class="program-card">
                <div class="program-content" style="text-align: center; padding: 35px;">
                    <div style="font-size: 50px; margin-bottom: 15px;">📚</div>
                    <h3><?php echo get_text('han_program_1_title', 'Học Tiếng Hàn'); ?></h3>
                    <p><?php echo get_text('han_program_1_desc', 'Chương trình 6-12 tháng tại các trường đại học, trung tâm ngôn ngữ uy tín.'); ?></p>
                    <p style="margin-top: 15px; color: var(--primary-blue); font-weight: 600;"><?php echo get_text('han_program_1_cost', 'Chi phí: 80-120 triệu/năm'); ?></p>
                </div>
            </div>
            
            <div class="program-card">
                <div class="program-content" style="text-align: center; padding: 35px;">
                    <div style="font-size: 50px; margin-bottom: 15px;">🎓</div>
                    <h3><?php echo get_text('han_program_2_title', 'Cao Đẳng - Đại Học'); ?></h3>
                    <p><?php echo get_text('han_program_2_desc', 'Học tại các trường top Hàn Quốc: Seoul National, Yonsei, Korea University...'); ?></p>
                    <p style="margin-top: 15px; color: var(--primary-blue); font-weight: 600;"><?php echo get_text('han_program_2_scholarship', 'Học bổng: 30-100%'); ?></p>
                </div>
            </div>
            
            <div class="program-card">
                <div class="program-content" style="text-align: center; padding: 35px;">
                    <div style="font-size: 50px; margin-bottom: 15px;">📜</div>
                    <h3><?php echo get_text('han_program_3_title', 'Thạc Sĩ - Tiến Sĩ'); ?></h3>
                    <p><?php echo get_text('han_program_3_desc', 'Chương trình sau đại học với nhiều học bổng toàn phần từ chính phủ Hàn Quốc.'); ?></p>
                    <p style="margin-top: 15px; color: var(--primary-blue); font-weight: 600;"><?php echo get_text('han_program_3_scholarship', 'KGSP, GKS Scholarship'); ?></p>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- DYNAMIC CONTENT BLOCKS -->
<?php
$blocks = get_content_blocks('han');
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

<!-- CTA -->
<section class="form-section">
    <div class="form-container" style="text-align: center;">
        <h3><?php echo get_text('han_cta_title', 'Đăng Ký Tư Vấn Du Học Hàn Quốc'); ?></h3>
        <p style="margin-bottom: 30px; color: #666;">Hotline: <strong><?php echo get_text('header_phone_display', '0822.314.555'); ?></strong> • Địa chỉ: <?php echo get_text('global_footer_address', 'Số 360, Phan Đình Phùng, Thái Nguyên'); ?></p>
        <a href="index.php#dangky" class="hero-btn">Đăng ký ngay</a>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
