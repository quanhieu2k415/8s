<?php
include_once 'includes/content_helper.php';
$pageTitle = "Về ICOGroup";
$pageDescription = "Tìm hiểu về ICOGroup - Tổ chức Giáo dục và Nhân lực Quốc tế. Lịch sử hình thành, sứ mệnh, tầm nhìn và giá trị cốt lõi.";
include 'includes/header.php';
?>

<!-- PAGE BANNER -->
<!-- PAGE BANNER -->
<?php
$header_bg = get_image('about_header_bg', '');
$header_style = $header_bg ? "background: url('$header_bg') no-repeat center center/cover;" : "";
?>
<section class="page-banner" style="<?php echo $header_style; ?>">
    <h1>Về ICOGroup</h1>
    <p>Tổ chức Giáo dục và Nhân lực Quốc tế ICO</p>
    <div class="breadcrumb">
        <a href="index.php">Trang chủ</a>
        <span>/</span>
        <span>Về ICOGroup</span>
    </div>
</section>

<!-- HISTORY SECTION -->
<section class="section about-section">
    <div class="container">
        <div class="section-header">
            <h2>Lịch Sử Hình Thành & Phát Triển</h2>
        </div>
        
        <div class="about-grid">
            <div class="about-image">
                <img src="https://icogroup.vn/vnt_upload/weblink/banner_trang_chu_01.jpg" alt="ICOGroup History">
                <div class="about-badge">Thành lập 29/4/2008</div>
            </div>
            
            <div class="about-content">
                <h3>Hành Trình Phát Triển</h3>
                <p>Tổ chức Giáo dục và Nhân lực Quốc tế ICO (gọi tắt là ICOGroup) được thành lập vào ngày <strong>29/4/2008</strong>, hoạt động chuyên nghiệp trong lĩnh vực Du học và Xuất khẩu lao động.</p>
                
                <p>Với tầm nhìn dài hạn và quan điểm phát triển bền vững, ICOGroup đã trở thành một trong những thương hiệu uy tín nhất tại Việt Nam. Tính đến nay, ICOGroup đã đưa gần <strong>17.000 du học sinh</strong> đến học tập tại các nước phát triển và hỗ trợ hơn <strong>38.000 lao động</strong> làm việc ở nước ngoài.</p>
                
                <p>Hiện ICOGroup đã có mặt ở trên <strong>60 tỉnh thành</strong> trong nước trải dài từ Bắc vào Nam với cơ sở vật chất được đầu tư đồng bộ và hiện đại phục vụ công tác tuyển dụng, đào tạo và hoàn thiện visa trước khi xuất cảnh.</p>
                
                <p>Đặc biệt, tập đoàn ICOGroup đã thành lập công ty thành viên <strong>ICO Japan</strong> tại Nhật Bản nhằm tối ưu hóa hoạt động thẩm định đối ngoại, quản lý, hỗ trợ tốt nhất cho du học sinh và người lao động trong suốt quá trình học tập, làm việc tại nước ngoài.</p>
            </div>
        </div>
    </div>
</section>

<!-- VALUES SECTION -->
<section class="section ecosystem-section">
    <div class="container">
        <div class="section-header">
            <h2>Hệ Giá Trị Của ICOGroup</h2>
            <p>Kim chỉ nam định hướng mọi hoạt động của chúng tôi</p>
        </div>
        
        <div class="ecosystem-grid">
            <div class="ecosystem-card">
                <div class="ecosystem-icon">🎯</div>
                <h3>Sứ Mệnh</h3>
                <p>Nâng cao chất lượng nguồn nhân lực Việt Nam, tạo cầu nối giữa người lao động Việt Nam với các cơ hội việc làm và học tập quốc tế.</p>
            </div>
            
            <div class="ecosystem-card">
                <div class="ecosystem-icon">👁️</div>
                <h3>Tầm Nhìn</h3>
                <p>Trở thành tập đoàn phát triển nhân lực lớn nhất Việt Nam, vươn xa ra khu vực và thế giới.</p>
            </div>
            
            <div class="ecosystem-card">
                <div class="ecosystem-icon">💎</div>
                <h3>Giá Trị Cốt Lõi</h3>
                <p><strong>Trí tuệ - Trung thực - Tận tâm</strong><br>Ba giá trị cốt lõi xuyên suốt mọi hoạt động của ICOGroup.</p>
            </div>
            
            <div class="ecosystem-card">
                <div class="ecosystem-icon">💬</div>
                <h3>Slogan</h3>
                <p><strong>"ICOGroup - Nơi tạo dựng tương lai"</strong><br>Khẩu hiệu thể hiện cam kết của chúng tôi với mỗi học viên.</p>
            </div>
        </div>
    </div>
</section>

<!-- STATISTICS -->
<section class="stats-section">
    <div class="stats-grid">
        <div class="stat-item">
            <span class="stat-number" data-target="17000">0</span>
            <span class="stat-label">Du học sinh</span>
        </div>
        <div class="stat-item">
            <span class="stat-number" data-target="38000">0</span>
            <span class="stat-label">Lao động quốc tế</span>
        </div>
        <div class="stat-item">
            <span class="stat-number" data-target="60">0</span>
            <span class="stat-label">Tỉnh thành có mặt</span>
        </div>
        <div class="stat-item">
            <span class="stat-number" data-target="100">0</span>
            <span class="stat-label">Trường đối tác Nhật Bản</span>
        </div>
    </div>
</section>

<!-- PARTNERS SECTION -->
<section class="section programs-section">
    <div class="container">
        <div class="section-header">
            <h2>Đối Tác Chiến Lược</h2>
            <p>Mạng lưới đối tác rộng khắp trong và ngoài nước</p>
        </div>
        
        <div class="about-content" style="max-width: 900px; margin: 0 auto; text-align: center;">
            <p>ICOGroup hiện đã đặt mối quan hệ hợp tác với hơn <strong>100 trường Nhật ngữ, Cao đẳng, Đại học, Học viện</strong> nổi tiếng của Nhật Bản như:</p>
            
            <div style="display: flex; flex-wrap: wrap; gap: 15px; justify-content: center; margin: 30px 0;">
                <span class="value-item">Trường Nhật ngữ UJS</span>
                <span class="value-item">Trường Nhật ngữ Tokyo World</span>
                <span class="value-item">Học viện Shin A</span>
                <span class="value-item">Học viện Aoyama</span>
                <span class="value-item">Trường Human Academy</span>
                <span class="value-item">Trường Nhật ngữ D.B.C</span>
                <span class="value-item">Trường Shinwa</span>
                <span class="value-item">Trường Osaka Minami</span>
            </div>
            
            <p>Ngoài ra, ICOGroup còn là đối tác của <strong>1.000+ trường THPT, Cao đẳng, Đại học</strong> trên cả nước và <strong>300+ trường tiếng, CĐ-ĐH, doanh nghiệp</strong> tại nước ngoài.</p>
        </div>
    </div>
</section>

<!-- ECOSYSTEM SECTION -->
<section class="section about-section" style="background: #f4f7fb;">
    <div class="container">
        <div class="section-header">
            <h2>Hệ Sinh Thái ICOGroup</h2>
        </div>
        
        <div class="programs-grid">
            <div class="program-card">
                <div class="program-content" style="text-align: center; padding: 40px;">
                    <div style="font-size: 60px; margin-bottom: 20px;">🌍</div>
                    <h3>Trung tâm Ngoại ngữ ICO</h3>
                    <p>Đào tạo tiếng Nhật, tiếng Đức, tiếng Hàn với đội ngũ giáo viên chất lượng cao, chương trình chuẩn quốc tế.</p>
                </div>
            </div>
            
            <div class="program-card">
                <div class="program-content" style="text-align: center; padding: 40px;">
                    <div style="font-size: 60px; margin-bottom: 20px;">🏫</div>
                    <h3>ICOSchool</h3>
                    <p>Hệ thống trường học với chương trình giáo dục tiên tiến, chuẩn quốc tế, phát triển toàn diện cho học sinh.</p>
                </div>
            </div>
            
            <div class="program-card">
                <div class="program-content" style="text-align: center; padding: 40px;">
                    <div style="font-size: 60px; margin-bottom: 20px;">🎓</div>
                    <h3>ICOCollege</h3>
                    <p>Cao đẳng nghề chất lượng cao với cam kết việc làm sau tốt nghiệp, đào tạo theo nhu cầu doanh nghiệp.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA SECTION -->
<section class="form-section">
    <div class="form-container" style="text-align: center;">
        <h3>Liên Hệ Với Chúng Tôi</h3>
        <p style="margin-bottom: 30px; color: #666;">Để được tư vấn chi tiết về các chương trình du học và xuất khẩu lao động</p>
        
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
