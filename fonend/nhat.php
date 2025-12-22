<?php
include_once 'includes/content_helper.php';
$pageTitle = "Du học Nhật Bản";
$pageDescription = "Du học Nhật Bản với ICOGroup - Đối tác của 100+ trường Nhật ngữ uy tín. Học bổng hấp dẫn, visa cao, hỗ trợ toàn diện.";
include 'includes/header.php';
?>

<!-- PAGE BANNER -->
<?php
$header_bg = get_image('nhat_header_bg', '');
$header_style = $header_bg ? "background: url('$header_bg') no-repeat center center/cover;" : "background: linear-gradient(135deg, #BC002D, #FFFFFF);";
?>
<section class="page-banner" style="<?php echo $header_style; ?>">
    <h1><?php echo get_text('nhat_title', 'Du Học Nhật Bản 🇯🇵'); ?></h1>
    <p><?php echo get_text('nhat_subtitle', 'Hành trình chinh phục xứ sở hoa anh đào'); ?></p>
    <div class="breadcrumb">
        <a href="index.php">Trang chủ</a>
        <span>/</span>
        <a href="#">Du học</a>
        <span>/</span>
        <span>Du học Nhật Bản</span>
    </div>
</section>

<!-- INTRO -->
<section class="section about-section">
    <div class="container">
        <div class="section-header">
            <h2><?php echo get_text('nhat_why_title', 'Tại Sao Chọn Du Học Nhật Bản?'); ?></h2>
            <p><?php echo get_text('nhat_why_subtitle', 'Nhật Bản - Điểm đến hàng đầu của du học sinh Việt Nam'); ?></p>
        </div>
        
        <div class="about-grid">
            <div class="about-image">
                <img src="<?php echo get_image('nhat_about_img', 'https://cdn-images.vtv.vn/562122370168008704/2023/7/26/untitled-1690344019340844974097.png'); ?>" alt="Du học Nhật Bản">
                <div class="about-badge"><?php echo get_text('nhat_badge', '100+ Đối tác'); ?></div>
            </div>
            
            <div class="about-content">
                <h3><?php echo get_text('nhat_reason_title', 'Lý Do Nên Du Học Nhật Bản'); ?></h3>
                <p><?php echo get_text('nhat_reason_desc', 'Nhật Bản là quốc gia có nền giáo dục tiên tiến, công nghệ phát triển và nền văn hóa độc đáo. Với chính sách mở cửa đón du học sinh, Nhật Bản đang trở thành điểm đến hấp dẫn nhất Châu Á.'); ?></p>
                
                <div class="about-values">
                    <div class="value-item"><span>🎓</span><span><?php echo get_text('nhat_benefit_1', 'Giáo dục đẳng cấp thế giới'); ?></span></div>
                    <div class="value-item"><span>💴</span><span><?php echo get_text('nhat_benefit_2', 'Làm thêm 28h/tuần hợp pháp'); ?></span></div>
                    <div class="value-item"><span>🏫</span><span><?php echo get_text('nhat_benefit_3', 'Học bổng lên đến 100%'); ?></span></div>
                    <div class="value-item"><span>🛡️</span><span><?php echo get_text('nhat_benefit_4', 'An ninh và an toàn cao'); ?></span></div>
                    <div class="value-item"><span>💼</span><span><?php echo get_text('nhat_benefit_5', 'Cơ hội việc làm sau tốt nghiệp'); ?></span></div>
                    <div class="value-item"><span>🌸</span><span><?php echo get_text('nhat_benefit_6', 'Văn hóa độc đáo, hấp dẫn'); ?></span></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- PARTNERS -->
<section class="section ecosystem-section">
    <div class="container">
        <div class="section-header">
            <h2><?php echo get_text('nhat_partners_title', 'Đối Tác Trường Nhật Ngữ'); ?></h2>
            <p><?php echo get_text('nhat_partners_subtitle', 'ICOGroup là đối tác của hơn 100 trường uy tín tại Nhật Bản'); ?></p>
        </div>
        
        <div style="display: flex; flex-wrap: wrap; gap: 15px; justify-content: center;">
            <span style="background: rgba(255,255,255,0.15); padding: 12px 25px; border-radius: 25px;"><?php echo get_text('nhat_school_1', 'Trường Nhật ngữ UJS'); ?></span>
            <span style="background: rgba(255,255,255,0.15); padding: 12px 25px; border-radius: 25px;"><?php echo get_text('nhat_school_2', 'Tokyo World'); ?></span>
            <span style="background: rgba(255,255,255,0.15); padding: 12px 25px; border-radius: 25px;"><?php echo get_text('nhat_school_3', 'Học viện Shin A'); ?></span>
            <span style="background: rgba(255,255,255,0.15); padding: 12px 25px; border-radius: 25px;"><?php echo get_text('nhat_school_4', 'Aoyama'); ?></span>
            <span style="background: rgba(255,255,255,0.15); padding: 12px 25px; border-radius: 25px;"><?php echo get_text('nhat_school_5', 'Human Academy'); ?></span>
            <span style="background: rgba(255,255,255,0.15); padding: 12px 25px; border-radius: 25px;"><?php echo get_text('nhat_school_6', 'Trường D.B.C'); ?></span>
            <span style="background: rgba(255,255,255,0.15); padding: 12px 25px; border-radius: 25px;"><?php echo get_text('nhat_school_7', 'Shinwa'); ?></span>
            <span style="background: rgba(255,255,255,0.15); padding: 12px 25px; border-radius: 25px;"><?php echo get_text('nhat_school_8', 'Osaka Minami'); ?></span>
            <span style="background: rgba(255,255,255,0.15); padding: 12px 25px; border-radius: 25px;"><?php echo get_text('nhat_school_9', 'Manabi'); ?></span>
            <span style="background: rgba(255,255,255,0.15); padding: 12px 25px; border-radius: 25px;"><?php echo get_text('nhat_school_10', 'IGL'); ?></span>
            <span style="background: var(--accent-orange); padding: 12px 25px; border-radius: 25px; font-weight: 600;"><?php echo get_text('nhat_school_more', '+ 90 trường khác'); ?></span>
        </div>
    </div>
</section>

<!-- PROGRAMS -->
<section class="section programs-section">
    <div class="container">
        <div class="section-header">
            <h2><?php echo get_text('nhat_programs_title', 'Các Chương Trình Du Học'); ?></h2>
        </div>
        
        <div class="programs-grid">
            <div class="program-card">
                <div class="program-content" style="text-align: center; padding: 40px;">
                    <div style="font-size: 60px; margin-bottom: 15px;">📖</div>
                    <span class="program-tag"><?php echo get_text('nhat_program_1_tag', 'Ngắn hạn'); ?></span>
                    <h3><?php echo get_text('nhat_program_1_title', 'Du Học Tiếng Nhật'); ?></h3>
                    <p><?php echo get_text('nhat_program_1_desc', 'Chương trình học tiếng Nhật từ 6 tháng - 2 năm tại các trường Nhật ngữ uy tín. Sau khi tốt nghiệp có thể lên Cao đẳng, Đại học hoặc đi làm.'); ?></p>
                    <div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 10px;">
                        <strong style="color: var(--primary-blue);"><?php echo get_text('nhat_program_1_cost', 'Chi phí: 150 - 200 triệu VNĐ/năm'); ?></strong>
                    </div>
                </div>
            </div>
            
            <div class="program-card">
                <div class="program-content" style="text-align: center; padding: 40px;">
                    <div style="font-size: 60px; margin-bottom: 15px;">🎓</div>
                    <span class="program-tag"><?php echo get_text('nhat_program_2_tag', 'Dài hạn'); ?></span>
                    <h3><?php echo get_text('nhat_program_2_title', 'Du Học Cao Đẳng - Đại Học'); ?></h3>
                    <p><?php echo get_text('nhat_program_2_desc', 'Học tại các trường Cao đẳng, Đại học tại Nhật Bản với nhiều ngành học đa dạng. Học bổng từ 30% - 100% học phí.'); ?></p>
                    <div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 10px;">
                        <strong style="color: var(--primary-blue);"><?php echo get_text('nhat_program_2_scholarship', 'Học bổng lên đến 100%'); ?></strong>
                    </div>
                </div>
            </div>
            
            <div class="program-card">
                <div class="program-content" style="text-align: center; padding: 40px;">
                    <div style="font-size: 60px; margin-bottom: 15px;">🔧</div>
                    <span class="program-tag"><?php echo get_text('nhat_program_3_tag', 'Kỹ năng'); ?></span>
                    <h3><?php echo get_text('nhat_program_3_title', 'Du Học Nghề (Senmon)'); ?></h3>
                    <p><?php echo get_text('nhat_program_3_desc', 'Học tại các trường chuyên môn (Senmon Gakko) với thời gian 2 năm. Tập trung kỹ năng thực hành, dễ xin việc.'); ?></p>
                    <div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 10px;">
                        <strong style="color: var(--primary-blue);"><?php echo get_text('nhat_program_3_result', 'Việc làm ngay sau tốt nghiệp'); ?></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- PROCESS -->
<section class="section about-section" style="background: #f4f7fb;">
    <div class="container">
        <div class="section-header">
            <h2><?php echo get_text('nhat_process_title', 'Quy Trình Du Học Nhật Bản'); ?></h2>
            <p><?php echo get_text('nhat_process_subtitle', '6 bước đơn giản để đến với xứ sở hoa anh đào'); ?></p>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(6, 1fr); gap: 20px;">
            <div style="text-align: center;">
                <div style="width: 60px; height: 60px; background: var(--primary-blue); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px; font-size: 24px; font-weight: bold;">1</div>
                <h4 style="font-size: 14px;"><?php echo get_text('nhat_step_1', 'Đăng ký tư vấn'); ?></h4>
            </div>
            <div style="text-align: center;">
                <div style="width: 60px; height: 60px; background: var(--primary-blue); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px; font-size: 24px; font-weight: bold;">2</div>
                <h4 style="font-size: 14px;"><?php echo get_text('nhat_step_2', 'Chọn trường'); ?></h4>
            </div>
            <div style="text-align: center;">
                <div style="width: 60px; height: 60px; background: var(--primary-blue); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px; font-size: 24px; font-weight: bold;">3</div>
                <h4 style="font-size: 14px;"><?php echo get_text('nhat_step_3', 'Hoàn thiện hồ sơ'); ?></h4>
            </div>
            <div style="text-align: center;">
                <div style="width: 60px; height: 60px; background: var(--primary-blue); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px; font-size: 24px; font-weight: bold;">4</div>
                <h4 style="font-size: 14px;"><?php echo get_text('nhat_step_4', 'Xin COE'); ?></h4>
            </div>
            <div style="text-align: center;">
                <div style="width: 60px; height: 60px; background: var(--primary-blue); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px; font-size: 24px; font-weight: bold;">5</div>
                <h4 style="font-size: 14px;"><?php echo get_text('nhat_step_5', 'Xin Visa'); ?></h4>
            </div>
            <div style="text-align: center;">
                <div style="width: 60px; height: 60px; background: var(--accent-orange); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px; font-size: 24px; font-weight: bold;">6</div>
                <h4 style="font-size: 14px;"><?php echo get_text('nhat_step_6', 'Bay sang Nhật'); ?></h4>
            </div>
        </div>
    </div>
</section>

<!-- STATISTICS -->
<section class="stats-section">
    <div class="stats-grid">
        <div class="stat-item">
            <span class="stat-number" data-target="<?php echo get_text('nhat_stat_1_num', '17000'); ?>">0</span>
            <span class="stat-label"><?php echo get_text('nhat_stat_1_label', 'Du học sinh đã gửi'); ?></span>
        </div>
        <div class="stat-item">
            <span class="stat-number" data-target="<?php echo get_text('nhat_stat_2_num', '100'); ?>">0</span>
            <span class="stat-label"><?php echo get_text('nhat_stat_2_label', 'Trường đối tác'); ?></span>
        </div>
        <div class="stat-item">
            <span class="stat-number" data-target="<?php echo get_text('nhat_stat_3_num', '95'); ?>">0</span>
            <span class="stat-label"><?php echo get_text('nhat_stat_3_label', '% Tỷ lệ đỗ visa'); ?></span>
        </div>
        <div class="stat-item">
            <span class="stat-number" data-target="<?php echo get_text('nhat_stat_4_num', '15'); ?>">0</span>
            <span class="stat-label"><?php echo get_text('nhat_stat_4_label', 'Năm kinh nghiệm'); ?></span>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="form-section">
    <div class="form-container" style="text-align: center;">
        <h3>🇯🇵 <?php echo get_text('nhat_cta_title', 'Đăng Ký Tư Vấn Du Học Nhật Bản'); ?></h3>
        <p style="margin-bottom: 30px; color: #666;"><?php echo get_text('nhat_cta_desc', 'Nhận tư vấn miễn phí từ đội ngũ chuyên gia với 15 năm kinh nghiệm'); ?></p>
        
        <div style="display: flex; flex-wrap: wrap; gap: 20px; justify-content: center; margin-bottom: 30px;">
            <div class="value-item" style="background: #e6f3ff;">
                <span>📞</span>
                <span>Hotline: <?php echo get_text('header_phone_display', '0822.314.555'); ?></span>
            </div>
            <div class="value-item" style="background: #e6f3ff;">
                <span>📍</span>
                <span><?php echo get_text('global_footer_address', 'Số 360, Phan Đình Phùng, Thái Nguyên'); ?></span>
            </div>
        </div>
        
        <a href="index.php#dangky" class="hero-btn">Đăng ký ngay</a>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
