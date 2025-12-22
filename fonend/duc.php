<?php
include_once 'includes/content_helper.php';
$pageTitle = "Du học Đức";
$pageDescription = "Du học Đức với ICOGroup - Chương trình du học kép Ausbildung, học miễn phí, có lương, việc làm ngay sau tốt nghiệp.";
include 'includes/header.php';
?>

<!-- PAGE BANNER -->
<?php
$header_bg = get_image('duc_header_bg', '');
$header_style = $header_bg ? "background: url('$header_bg') no-repeat center center/cover;" : "background: linear-gradient(135deg, #000000, #DD0000, #FFCC00);";
?>
<section class="page-banner" style="<?php echo $header_style; ?>">
    <h1><?php echo get_text('duc_title', 'Du Học Đức'); ?></h1>
    <p><?php echo get_text('duc_subtitle', 'Chương trình du học miễn học phí với cơ hội việc làm và định cư'); ?></p>
    <div class="breadcrumb">
        <a href="index.php">Trang chủ</a>
        <span>/</span>
        <a href="#">Du học</a>
        <span>/</span>
        <span>Du học Đức</span>
    </div>
</section>

<!-- INTRO -->
<section class="section about-section">
    <div class="container">
        <div class="section-header">
            <h2><?php echo get_text('duc_why_title', 'Tại Sao Chọn Du Học Đức?'); ?></h2>
            <p><?php echo get_text('duc_why_subtitle', 'Đức - Điểm đến lý tưởng cho du học sinh quốc tế'); ?></p>
        </div>
        
        <div class="about-grid">
            <div class="about-image">
                <img src="<?php echo get_image('duc_about_img', 'https://icogroupvn.wordpress.com/wp-content/uploads/2017/03/du-hoc-duc-ico-cho-tuong-lai-tuoi-sang-01.jpg?w=460&h=345'); ?>" alt="Du học Đức">
            </div>
            
            <div class="about-content">
                <h3><?php echo get_text('duc_advantage_title', 'Ưu Điểm Vượt Trội'); ?></h3>
                <p><?php echo get_text('duc_advantage_desc', 'Đức là một trong những quốc gia có nền giáo dục hàng đầu thế giới với nhiều ưu đãi đặc biệt dành cho du học sinh quốc tế.'); ?></p>
                
                <div class="about-values">
                    <div class="value-item"><span>🎓</span><span><?php echo get_text('duc_benefit_1', 'Miễn học phí tại đại học công lập'); ?></span></div>
                    <div class="value-item"><span>💰</span><span><?php echo get_text('duc_benefit_2', 'Học nghề hưởng lương 800-1200€/tháng'); ?></span></div>
                    <div class="value-item"><span>🏠</span><span><?php echo get_text('duc_benefit_3', 'Cơ hội định cư sau khi tốt nghiệp'); ?></span></div>
                    <div class="value-item"><span>🌍</span><span><?php echo get_text('duc_benefit_4', 'Bằng cấp được công nhận toàn cầu'); ?></span></div>
                    <div class="value-item"><span>✈️</span><span><?php echo get_text('duc_benefit_5', 'Du lịch tự do trong khối Schengen'); ?></span></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- PROGRAMS -->
<section class="section ecosystem-section">
    <div class="container">
        <div class="section-header">
            <h2><?php echo get_text('duc_programs_title', 'Các Chương Trình Du Học Đức'); ?></h2>
        </div>
        
        <div class="ecosystem-grid" style="grid-template-columns: repeat(3, 1fr);">
            <div class="ecosystem-card">
                <div class="ecosystem-icon">📚</div>
                <h3><?php echo get_text('duc_program_1_title', 'Du Học Đại Học'); ?></h3>
                <p><?php echo get_text('duc_program_1_desc', 'Học tại các trường đại học công lập hàng đầu nước Đức với học phí 0€. Bằng cử nhân, thạc sĩ được công nhận toàn cầu.'); ?></p>
            </div>
            
            <div class="ecosystem-card">
                <div class="ecosystem-icon">🔧</div>
                <h3><?php echo get_text('duc_program_2_title', 'Du Học Nghề (Ausbildung)'); ?></h3>
                <p><?php echo get_text('duc_program_2_desc', 'Chương trình đào tạo kép: Học lý thuyết + thực hành tại doanh nghiệp. Được trả lương từ 800-1200€/tháng trong quá trình học.'); ?></p>
            </div>
            
            <div class="ecosystem-card">
                <div class="ecosystem-icon">🌞</div>
                <h3><?php echo get_text('duc_program_3_title', 'Du Học Hè'); ?></h3>
                <p><?php echo get_text('duc_program_3_desc', 'Chương trình trải nghiệm ngắn hạn 2-4 tuần. Học tiếng Đức + tham quan du lịch + giao lưu văn hóa.'); ?></p>
            </div>
        </div>
    </div>
</section>

<!-- AUSBILDUNG DETAIL -->
<section class="section programs-section">
    <div class="container">
        <div class="section-header">
            <h2><?php echo get_text('duc_ausbildung_title', 'Du Học Kép Tại Đức (Ausbildung)'); ?></h2>
            <p><?php echo get_text('duc_ausbildung_subtitle', 'Học miễn phí, có lương, việc làm ngay sau tốt nghiệp'); ?></p>
        </div>
        
        <div class="about-grid">
            <div class="about-content">
                <h3><?php echo get_text('duc_ausbildung_what_title', 'Ausbildung Là Gì?'); ?></h3>
                <p><?php echo get_text('duc_ausbildung_what_desc', 'Ausbildung là chương trình đào tạo nghề kép của Đức, kết hợp giữa học lý thuyết tại trường và thực hành tại doanh nghiệp. Đây là con đường ngắn nhất để có việc làm ổn định và định cư tại Đức.'); ?></p>
                
                <h4 style="margin-top: 20px; color: var(--primary-blue);"><?php echo get_text('duc_ausbildung_benefits_title', 'Lợi ích khi học Ausbildung:'); ?></h4>
                <ul style="margin: 15px 0; color: #666;">
                    <li style="margin-bottom: 10px;">✅ <strong><?php echo get_text('duc_aus_benefit_1', 'Miễn học phí hoàn toàn'); ?></strong></li>
                    <li style="margin-bottom: 10px;">✅ <strong><?php echo get_text('duc_aus_benefit_2', 'Lương 800-1.200€/tháng'); ?></strong> trong quá trình học</li>
                    <li style="margin-bottom: 10px;">✅ <strong><?php echo get_text('duc_aus_benefit_3', 'Thời gian đào tạo 2-3 năm'); ?></strong></li>
                    <li style="margin-bottom: 10px;">✅ <strong><?php echo get_text('duc_aus_benefit_4', 'Việc làm ngay sau tốt nghiệp'); ?></strong></li>
                    <li style="margin-bottom: 10px;">✅ <strong><?php echo get_text('duc_aus_benefit_5', 'Cơ hội định cư'); ?></strong> sau 2 năm làm việc</li>
                </ul>
            </div>
            
            <div>
                <h4 style="color: var(--accent-orange); margin-bottom: 20px;"><?php echo get_text('duc_hot_jobs_title', 'Các ngành hot nhất:'); ?></h4>
                <div style="display: flex; flex-direction: column; gap: 15px;">
                    <div style="background: #f8f9fa; padding: 20px; border-radius: 15px; border-left: 4px solid var(--accent-orange);">
                        <strong><?php echo get_text('duc_job_1_title', '🏥 Điều dưỡng - Chăm sóc sức khỏe'); ?></strong>
                        <p style="color: #666; margin-top: 5px; font-size: 14px;"><?php echo get_text('duc_job_1_desc', 'Nhu cầu cao, lương hấp dẫn, dễ định cư'); ?></p>
                    </div>
                    <div style="background: #f8f9fa; padding: 20px; border-radius: 15px; border-left: 4px solid var(--primary-blue);">
                        <strong><?php echo get_text('duc_job_2_title', '⚙️ Cơ khí - Kỹ thuật'); ?></strong>
                        <p style="color: #666; margin-top: 5px; font-size: 14px;"><?php echo get_text('duc_job_2_desc', 'Ngành thế mạnh của Đức, nhiều cơ hội'); ?></p>
                    </div>
                    <div style="background: #f8f9fa; padding: 20px; border-radius: 15px; border-left: 4px solid #28a745;">
                        <strong><?php echo get_text('duc_job_3_title', '🍳 Khách sạn - Nhà hàng'); ?></strong>
                        <p style="color: #666; margin-top: 5px; font-size: 14px;"><?php echo get_text('duc_job_3_desc', 'Yêu cầu đầu vào thấp, cơ hội cao'); ?></p>
                    </div>
                    <div style="background: #f8f9fa; padding: 20px; border-radius: 15px; border-left: 4px solid #dc3545;">
                        <strong><?php echo get_text('duc_job_4_title', '💻 Công nghệ thông tin'); ?></strong>
                        <p style="color: #666; margin-top: 5px; font-size: 14px;"><?php echo get_text('duc_job_4_desc', 'Lương cao nhất, nhiều việc làm'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- REQUIREMENTS -->
<section class="section about-section" style="background: #f4f7fb;">
    <div class="container">
        <div class="section-header">
            <h2><?php echo get_text('duc_requirements_title', 'Điều Kiện & Hồ Sơ Du Học Đức'); ?></h2>
        </div>
        
        <div class="programs-grid">
            <div class="program-card">
                <div class="program-content" style="text-align: center; padding: 35px;">
                    <div style="font-size: 50px; margin-bottom: 15px;">📋</div>
                    <h3><?php echo get_text('duc_condition_title', 'Điều Kiện'); ?></h3>
                    <ul style="text-align: left; color: #666; margin-top: 15px;">
                        <li style="margin-bottom: 8px;">• <?php echo get_text('duc_cond_1', 'Độ tuổi: 18-30 tuổi'); ?></li>
                        <li style="margin-bottom: 8px;">• <?php echo get_text('duc_cond_2', 'Tốt nghiệp THPT trở lên'); ?></li>
                        <li style="margin-bottom: 8px;">• <?php echo get_text('duc_cond_3', 'Tiếng Đức B1/B2 (sẽ được đào tạo)'); ?></li>
                        <li style="margin-bottom: 8px;">• <?php echo get_text('duc_cond_4', 'Sức khỏe tốt'); ?></li>
                        <li>• <?php echo get_text('duc_cond_5', 'Không tiền án tiền sự'); ?></li>
                    </ul>
                </div>
            </div>
            
            <div class="program-card">
                <div class="program-content" style="text-align: center; padding: 35px;">
                    <div style="font-size: 50px; margin-bottom: 15px;">📁</div>
                    <h3><?php echo get_text('duc_documents_title', 'Hồ Sơ Cần Thiết'); ?></h3>
                    <ul style="text-align: left; color: #666; margin-top: 15px;">
                        <li style="margin-bottom: 8px;">• <?php echo get_text('duc_doc_1', 'Bằng tốt nghiệp THPT'); ?></li>
                        <li style="margin-bottom: 8px;">• <?php echo get_text('duc_doc_2', 'Học bạ THPT'); ?></li>
                        <li style="margin-bottom: 8px;">• <?php echo get_text('duc_doc_3', 'Chứng chỉ tiếng Đức B1/B2'); ?></li>
                        <li style="margin-bottom: 8px;">• <?php echo get_text('duc_doc_4', 'Hộ chiếu còn hạn'); ?></li>
                        <li>• <?php echo get_text('duc_doc_5', 'Ảnh visa, giấy tờ cá nhân'); ?></li>
                    </ul>
                </div>
            </div>
            
            <div class="program-card">
                <div class="program-content" style="text-align: center; padding: 35px;">
                    <div style="font-size: 50px; margin-bottom: 15px;">💵</div>
                    <h3><?php echo get_text('duc_cost_title', 'Chi Phí'); ?></h3>
                    <ul style="text-align: left; color: #666; margin-top: 15px;">
                        <li style="margin-bottom: 8px;">• <?php echo get_text('duc_cost_1', 'Học tiếng: 5.000€'); ?></li>
                        <li style="margin-bottom: 8px;">• <?php echo get_text('duc_cost_2', 'Phí dịch vụ: Theo thỏa thuận'); ?></li>
                        <li style="margin-bottom: 8px;">• <?php echo get_text('duc_cost_3', 'Chi phí sinh hoạt: 700-900€/tháng'); ?></li>
                        <li style="margin-bottom: 8px;">• <strong><?php echo get_text('duc_cost_4', 'Hoàn vốn: 6-12 tháng'); ?></strong></li>
                        <li>• <?php echo get_text('duc_cost_5', '(Nhờ lương Ausbildung)'); ?></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="form-section">
    <div class="form-container" style="text-align: center;">
        <h3>🇩🇪 <?php echo get_text('duc_cta_title', 'Đăng Ký Tư Vấn Du Học Đức'); ?></h3>
        <p style="margin-bottom: 30px; color: #666;"><?php echo get_text('duc_cta_desc', 'Nhận tư vấn miễn phí từ chuyên gia du học Đức của ICOGroup'); ?></p>
        
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
