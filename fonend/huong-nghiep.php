<?php
include_once 'includes/content_helper.php';
$pageTitle = "Hướng Nghiệp - ICOCareer";
$pageDescription = "ICOCareer - Chương trình hướng nghiệp của ICOGroup. Tư vấn du học, lao động quốc tế, định hướng nghề nghiệp cho học viên.";
include 'includes/header.php';
?>

<!-- PAGE BANNER -->
<?php
$header_bg = get_image('huongnghiep_header_bg', '');
$header_style = $header_bg ? "background: url('$header_bg') no-repeat center center/cover;" : "";
?>
<section class="page-banner" style="<?php echo $header_style; ?>">
    <h1><?php echo get_text('huongnghiep_title', 'ICOCareer - Hướng Nghiệp'); ?></h1>
    <p><?php echo get_text('huongnghiep_subtitle', 'Định hướng tương lai, khai phá tiềm năng'); ?></p>
    <div class="breadcrumb">
        <a href="index.php">Trang chủ</a>
        <span>/</span>
        <span>Hướng nghiệp</span>
    </div>
</section>

<!-- INTRO SECTION -->
<section class="section about-section">
    <div class="container">
        <div class="about-content" style="max-width: 900px; margin: 0 auto; text-align: center;">
            <h2 style="color: var(--primary-blue); margin-bottom: 20px;">Hoạt Động Hướng Nghiệp ICOGroup</h2>
            <p style="font-size: 18px;">Hoạt động hướng nghiệp là một hoạt động không thể thiếu trong hành trình học tập và phát triển của học viên tại ICOGroup. Hoạt động hướng nghiệp nhằm tư vấn, định hướng cho học viên tham gia các chương trình phù hợp với năng lực và nguyện vọng.</p>
        </div>
    </div>
</section>

<!-- TARGET AUDIENCE -->
<section class="section ecosystem-section">
    <div class="container">
        <div class="section-header">
            <h2>Đối Tượng Hướng Nghiệp</h2>
            <p>Ai có thể tham gia chương trình hướng nghiệp của ICOGroup?</p>
        </div>
        
        <div class="ecosystem-grid">
            <div class="ecosystem-card">
                <div class="ecosystem-icon">🎓</div>
                <h3>Học Sinh THPT</h3>
                <p>Học sinh lớp 10, 11, 12 đang tìm kiếm con đường học tập và nghề nghiệp phù hợp sau khi tốt nghiệp.</p>
            </div>
            
            <div class="ecosystem-card">
                <div class="ecosystem-icon">👨‍🎓</div>
                <h3>Sinh Viên</h3>
                <p>Sinh viên các trường Cao đẳng, Đại học muốn tìm cơ hội thực tập, việc làm hoặc du học nâng cao.</p>
            </div>
            
            <div class="ecosystem-card">
                <div class="ecosystem-icon">👷</div>
                <h3>Người Lao Động</h3>
                <p>Người lao động muốn tìm kiếm cơ hội việc làm với thu nhập cao tại nước ngoài.</p>
            </div>
            
            <div class="ecosystem-card">
                <div class="ecosystem-icon">👨‍👩‍👧</div>
                <h3>Phụ Huynh</h3>
                <p>Phụ huynh muốn tìm hiểu và định hướng tương lai cho con em mình.</p>
            </div>
        </div>
    </div>
</section>

<!-- PROGRAMS -->
<section class="section programs-section">
    <div class="container">
        <div class="section-header">
            <h2><?php echo get_text('huongnghiep_programs_title', 'Chương Trình Hướng Nghiệp'); ?></h2>
            <p><?php echo get_text('huongnghiep_programs_subtitle', 'Ba hướng đi chính dành cho học viên ICOGroup'); ?></p>
        </div>
        
        <div class="programs-grid">
            <div class="program-card">
                <div class="program-image">
                    <img src="<?php echo get_image('huongnghiep_program_1_img', 'https://cdn-images.vtv.vn/562122370168008704/2023/7/26/untitled-1690344019340844974097.png'); ?>" alt="Du học">
                </div>
                <div class="program-content">
                    <span class="program-tag"><?php echo get_text('huongnghiep_program_1_tag', 'Du học'); ?></span>
                    <h3><?php echo get_text('huongnghiep_program_1_title', 'Du Học Quốc Tế'); ?></h3>
                    <p><?php echo get_text('huongnghiep_program_1_desc', 'Chương trình du học tại Nhật Bản, Đức, Hàn Quốc, Đài Loan với học bổng hấp dẫn và hỗ trợ visa toàn diện.'); ?></p>
                    <ul style="margin: 15px 0; color: #666; font-size: 14px;">
                        <li>✓ <?php echo get_text('huongnghiep_program_1_benefit_1', 'Học bổng lên đến 100%'); ?></li>
                        <li>✓ <?php echo get_text('huongnghiep_program_1_benefit_2', 'Hỗ trợ visa, ký túc xá'); ?></li>
                        <li>✓ <?php echo get_text('huongnghiep_program_1_benefit_3', 'Việc làm thêm hợp pháp'); ?></li>
                    </ul>
                    <a href="nhat.php" class="program-link">
                        Xem chi tiết
                        <span class="material-symbols-outlined">arrow_forward</span>
                    </a>
                </div>
            </div>
            
            <div class="program-card">
                <div class="program-image">
                    <img src="<?php echo get_image('huongnghiep_program_2_img', 'https://icogroup.vn/vnt_upload/weblink/banner_chu_04.jpg'); ?>" alt="Lao động quốc tế">
                </div>
                <div class="program-content">
                    <span class="program-tag"><?php echo get_text('huongnghiep_program_2_tag', 'Lao động'); ?></span>
                    <h3><?php echo get_text('huongnghiep_program_2_title', 'Lao Động Quốc Tế'); ?></h3>
                    <p><?php echo get_text('huongnghiep_program_2_desc', 'Chương trình xuất khẩu lao động tại Nhật Bản, Hàn Quốc, Đài Loan, Đức với thu nhập cao và cam kết việc làm.'); ?></p>
                    <ul style="margin: 15px 0; color: #666; font-size: 14px;">
                        <li>✓ <?php echo get_text('huongnghiep_program_2_benefit_1', 'Thu nhập 30-50 triệu/tháng'); ?></li>
                        <li>✓ <?php echo get_text('huongnghiep_program_2_benefit_2', 'Hợp đồng lao động rõ ràng'); ?></li>
                        <li>✓ <?php echo get_text('huongnghiep_program_2_benefit_3', 'Bảo hiểm y tế đầy đủ'); ?></li>
                    </ul>
                    <a href="xkldjp.php" class="program-link">
                        Xem chi tiết
                        <span class="material-symbols-outlined">arrow_forward</span>
                    </a>
                </div>
            </div>
            
            <div class="program-card">
                <div class="program-image">
                    <img src="<?php echo get_image('huongnghiep_program_3_img', 'https://icogroup.vn/vnt_upload/news/11_2024/43_NAM_NGAY_NHA_GIAO_VN_1.jpg'); ?>" alt="Việc làm trong nước">
                </div>
                <div class="program-content">
                    <span class="program-tag"><?php echo get_text('huongnghiep_program_3_tag', 'Việc làm'); ?></span>
                    <h3><?php echo get_text('huongnghiep_program_3_title', 'Lao Động Trong Nước'); ?></h3>
                    <p><?php echo get_text('huongnghiep_program_3_desc', 'Kết nối việc làm tại các doanh nghiệp trong nước, đặc biệt là doanh nghiệp FDI với chế độ đãi ngộ tốt.'); ?></p>
                    <ul style="margin: 15px 0; color: #666; font-size: 14px;">
                        <li>✓ <?php echo get_text('huongnghiep_program_3_benefit_1', 'Doanh nghiệp Nhật, Hàn tại VN'); ?></li>
                        <li>✓ <?php echo get_text('huongnghiep_program_3_benefit_2', 'Mức lương cạnh tranh'); ?></li>
                        <li>✓ <?php echo get_text('huongnghiep_program_3_benefit_3', 'Cơ hội thăng tiến'); ?></li>
                    </ul>
                    <a href="lienhe.php" class="program-link">
                        Liên hệ tư vấn
                        <span class="material-symbols-outlined">arrow_forward</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- PILLARS -->
<section class="section about-section" style="background: #f4f7fb;">
    <div class="container">
        <div class="section-header">
            <h2>Trụ Cột Hướng Nghiệp</h2>
            <p>Quy trình hướng nghiệp toàn diện của ICOGroup</p>
        </div>
        
        <div class="ecosystem-grid" style="grid-template-columns: repeat(4, 1fr);">
            <div style="background: white; padding: 30px; border-radius: 20px; text-align: center; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                <div style="width: 60px; height: 60px; background: #e6f3ff; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px; font-size: 28px;">1</div>
                <h3 style="color: var(--primary-blue); margin-bottom: 10px;">Tư Vấn Tuyển Sinh</h3>
                <p style="color: #666; font-size: 14px;">Tư vấn chi tiết về các chương trình, điều kiện, chi phí và cơ hội.</p>
            </div>
            
            <div style="background: white; padding: 30px; border-radius: 20px; text-align: center; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                <div style="width: 60px; height: 60px; background: #fff3e6; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px; font-size: 28px;">2</div>
                <h3 style="color: var(--accent-orange); margin-bottom: 10px;">Tư Vấn Nghề Nghiệp</h3>
                <p style="color: #666; font-size: 14px;">Định hướng nghề nghiệp phù hợp với năng lực và sở thích cá nhân.</p>
            </div>
            
            <div style="background: white; padding: 30px; border-radius: 20px; text-align: center; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                <div style="width: 60px; height: 60px; background: #e6ffe6; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px; font-size: 28px;">3</div>
                <h3 style="color: #28a745; margin-bottom: 10px;">Thực Tập & Trải Nghiệm</h3>
                <p style="color: #666; font-size: 14px;">Cơ hội thực tập thực tế tại doanh nghiệp trong và ngoài nước.</p>
            </div>
            
            <div style="background: white; padding: 30px; border-radius: 20px; text-align: center; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                <div style="width: 60px; height: 60px; background: #ffe6f0; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px; font-size: 28px;">4</div>
                <h3 style="color: #dc3545; margin-bottom: 10px;">Đào Tạo Kỹ Năng</h3>
                <p style="color: #666; font-size: 14px;">Đào tạo kỹ năng mềm, ngoại ngữ, chuyên môn cần thiết.</p>
            </div>
        </div>
    </div>
</section>

<!-- WHY CHOOSE US -->
<section class="section ecosystem-section">
    <div class="container">
        <div class="section-header">
            <h2>Sự Khác Biệt Của ICOGroup</h2>
            <p>Tại sao chọn ICOGroup cho hành trình hướng nghiệp?</p>
        </div>
        
        <div class="about-grid" style="gap: 50px;">
            <div>
                <h3 style="color: var(--accent-orange); margin-bottom: 20px; font-size: 24px;">🌐 Mạng Lưới Đối Tác Rộng Khắp</h3>
                
                <div style="background: rgba(255,255,255,0.1); padding: 25px; border-radius: 15px; margin-bottom: 20px;">
                    <h4 style="margin-bottom: 10px;">Đối tác trong nước</h4>
                    <p style="opacity: 0.9;">Đối tác của <strong>1.000+ trường THPT, Cao đẳng, Đại học</strong> trên cả nước. ICOGroup có mối quan hệ mật thiết với hệ thống giáo dục, tiếp cận và hỗ trợ hướng nghiệp cho học sinh, sinh viên trên quy mô lớn.</p>
                </div>
                
                <div style="background: rgba(255,255,255,0.1); padding: 25px; border-radius: 15px;">
                    <h4 style="margin-bottom: 10px;">Đối tác quốc tế</h4>
                    <p style="opacity: 0.9;">Đối tác của <strong>300+ trường tiếng, CĐ-ĐH, doanh nghiệp</strong> tại nước ngoài. ICOGroup mở ra cơ hội học tập, làm việc quốc tế cho học viên, đồng thời cập nhật xu hướng nghề nghiệp toàn cầu.</p>
                </div>
            </div>
            
            <div>
                <h3 style="color: var(--accent-orange); margin-bottom: 20px; font-size: 24px;">📚 Chương Trình Thiết Thực</h3>
                
                <div style="background: rgba(255,255,255,0.1); padding: 25px; border-radius: 15px; margin-bottom: 20px;">
                    <h4 style="margin-bottom: 10px;">Hướng nghiệp quy mô lớn</h4>
                    <p style="opacity: 0.9;">Tổ chức các chương trình hướng nghiệp có quy mô lớn tại các trường THPT trên cả nước, giúp học sinh tiếp cận thông tin, định hướng nghề nghiệp bài bản và hiệu quả.</p>
                </div>
                
                <div style="background: rgba(255,255,255,0.1); padding: 25px; border-radius: 15px;">
                    <h4 style="margin-bottom: 10px;">Ngoại khóa nâng cao năng lực</h4>
                    <p style="opacity: 0.9;">Liên tục tổ chức các chương trình ngoại khóa phát triển toàn diện kỹ năng mềm, kiến thức chuyên môn và khả năng thích nghi, giúp học viên tự tin hội nhập.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="form-section">
    <div class="form-container" style="text-align: center;">
        <h3>🎯 Bắt Đầu Hành Trình Của Bạn</h3>
        <p style="margin-bottom: 30px; color: #666;">Đăng ký ngay để được tư vấn hướng nghiệp miễn phí từ chuyên gia ICOGroup</p>
        
        <div style="display: flex; flex-wrap: wrap; gap: 20px; justify-content: center; margin-bottom: 30px;">
            <div class="value-item" style="background: #e6f3ff;">
                <span>📞</span>
                <span>Hotline: 0822.314.555</span>
            </div>
            <div class="value-item" style="background: #e6f3ff;">
                <span>📍</span>
                <span>Số 360, Phan Đình Phùng, Thái Nguyên</span>
            </div>
        </div>
        
        <a href="index.php#dangky" class="hero-btn">Đăng ký tư vấn ngay</a>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
