<?php
$pageTitle = "Trang chủ";
$pageDescription = "ICOGroup - Tập đoàn Giáo dục và Đào tạo nghề hàng đầu Việt Nam. Du học Nhật Bản, Đức, Hàn Quốc. Xuất khẩu lao động uy tín.";
include 'includes/header.php';
include_once 'includes/content_helper.php';
?>

<!-- HERO SECTION -->
<section class="hero-section">
    <div class="hero-slider">
        <div class="hero-slide">
            <img src="<?php echo get_image('index_hero_slide_1_img', 'https://icogroup.vn/vnt_upload/weblink/banner_trang_chu_01.jpg'); ?>" alt="ICOGroup">
            <div class="hero-overlay">
                <div class="hero-content">
                    <h1><?php echo get_text('index_hero_slide_1_title', 'ICOGroup - Nơi Tạo Dựng Tương Lai'); ?></h1>
                    <p><?php echo get_text('index_hero_slide_1_subtitle', 'Tập đoàn Giáo dục và Đào tạo nghề hàng đầu Việt Nam với hơn 15 năm kinh nghiệm'); ?></p>
                    <a href="#dangky" class="hero-btn">Đăng ký tư vấn miễn phí</a>
                </div>
            </div>
        </div>
        <div class="hero-slide">
            <img src="<?php echo get_image('index_hero_slide_2_img', 'https://icogroup.vn/vnt_upload/weblink/banner_chu_04.jpg'); ?>" alt="Du học">
            <div class="hero-overlay">
                <div class="hero-content">
                    <h1><?php echo get_text('index_hero_slide_2_title', 'Chương Trình Du Học Quốc Tế'); ?></h1>
                    <p><?php echo get_text('index_hero_slide_2_subtitle', 'Nhật Bản • Đức • Hàn Quốc • Đài Loan'); ?></p>
                    <a href="nhat.php" class="hero-btn">Tìm hiểu ngay</a>
                </div>
            </div>
        </div>
        <div class="hero-slide">
            <img src="<?php echo get_image('index_hero_slide_3_img', 'https://www.icogroup.vn/vnt_upload/news/02_2025/ICOGROUP_TUYEN_DUNG_23.jpg'); ?>" alt="XKLĐ">
            <div class="hero-overlay">
                <div class="hero-content">
                    <h1><?php echo get_text('index_hero_slide_3_title', 'Xuất Khẩu Lao Động Uy Tín'); ?></h1>
                    <p><?php echo get_text('index_hero_slide_3_subtitle', 'Cơ hội việc làm với thu nhập cao tại nước ngoài'); ?></p>
                    <a href="xkldjp.php" class="hero-btn">Xem chi tiết</a>
                </div>
            </div>
        </div>
    </div>
    
    <button class="hero-nav prev">
        <span class="material-symbols-outlined">chevron_left</span>
    </button>
    <button class="hero-nav next">
        <span class="material-symbols-outlined">chevron_right</span>
    </button>
    
    <div class="hero-dots">
        <span class="hero-dot active"></span>
        <span class="hero-dot"></span>
        <span class="hero-dot"></span>
    </div>
</section>

<!-- ABOUT SECTION - Hero Style with Background Image -->
<section class="section about-hero-section" id="about" style="
    background: linear-gradient(135deg, rgba(30, 27, 75, 0.9) 0%, rgba(99, 102, 241, 0.85) 100%), 
                url('<?php echo get_image('index_about_bg', 'https://icogroup.vn/vnt_upload/weblink/banner_trang_chu_01.jpg'); ?>');
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
    color: white;
    padding: 100px 0;
    position: relative;
">
    <div class="container">
        <div class="section-header" style="margin-bottom: 50px;">
            <h2 style="color: white; font-size: 48px; font-weight: 800; text-shadow: 0 4px 20px rgba(0,0,0,0.3);">Về ICOGroup</h2>
            <p style="color: rgba(255,255,255,0.9); font-size: 20px; text-shadow: 0 2px 10px rgba(0,0,0,0.2);">Tổ chức Giáo dục và Nhân lực Quốc tế ICO - Hơn 15 năm xây dựng và phát triển</p>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: center;">
            <!-- Left - History & Content -->
            <div style="background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); padding: 40px; border-radius: 24px; border: 1px solid rgba(255,255,255,0.2);">
                <h3 style="color: white; font-size: 28px; margin-bottom: 20px; font-weight: 700;"><?php echo get_text('index_about_history_title', 'Lịch Sử Hình Thành & Phát Triển'); ?></h3>
                <p style="color: rgba(255,255,255,0.9); line-height: 1.8; margin-bottom: 15px;"><?php echo get_text('index_about_history_desc', 'Với tầm nhìn dài hạn và quan điểm phát triển bền vững, ICOGroup đã trở thành một trong những thương hiệu uy tín về du học và xuất khẩu lao động tại Việt Nam.'); ?></p>
                <p style="color: rgba(255,255,255,0.85); line-height: 1.8;"><?php echo get_text('index_about_history_desc_2', 'Hiện ICOGroup đã có mặt ở trên 60 tỉnh thành trong nước với cơ sở vật chất được đầu tư đồng bộ và hiện đại.'); ?></p>
                
                <a href="ve-icogroup.php" class="hero-btn" style="margin-top: 30px; display: inline-block;">Tìm hiểu thêm</a>
            </div>
            
            <!-- Right - Values -->
            <div style="display: flex; flex-direction: column; gap: 20px;">
                <div style="background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); padding: 25px 30px; border-radius: 16px; border: 1px solid rgba(255,255,255,0.2); display: flex; align-items: center; gap: 20px;">
                    <span style="font-size: 40px; filter: drop-shadow(0 4px 8px rgba(0,0,0,0.2));">🎯</span>
                    <div>
                        <strong style="color: #FCD34D; font-size: 14px; text-transform: uppercase; letter-spacing: 1px;">Sứ mệnh</strong>
                        <p style="color: white; font-size: 16px; margin-top: 5px;">Nâng cao chất lượng nguồn nhân lực Việt Nam</p>
                    </div>
                </div>
                
                <div style="background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); padding: 25px 30px; border-radius: 16px; border: 1px solid rgba(255,255,255,0.2); display: flex; align-items: center; gap: 20px;">
                    <span style="font-size: 40px; filter: drop-shadow(0 4px 8px rgba(0,0,0,0.2));">👁️</span>
                    <div>
                        <strong style="color: #FCD34D; font-size: 14px; text-transform: uppercase; letter-spacing: 1px;">Tầm nhìn</strong>
                        <p style="color: white; font-size: 16px; margin-top: 5px;">Tập đoàn phát triển nhân lực lớn nhất Việt Nam</p>
                    </div>
                </div>
                
                <div style="background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); padding: 25px 30px; border-radius: 16px; border: 1px solid rgba(255,255,255,0.2); display: flex; align-items: center; gap: 20px;">
                    <span style="font-size: 40px; filter: drop-shadow(0 4px 8px rgba(0,0,0,0.2));">💎</span>
                    <div>
                        <strong style="color: #FCD34D; font-size: 14px; text-transform: uppercase; letter-spacing: 1px;">Giá trị cốt lõi</strong>
                        <p style="color: white; font-size: 16px; margin-top: 5px;">Trí tuệ, Trung thực, Tận tâm</p>
                    </div>
                </div>
                
                <!-- Badge -->
                <div style="text-align: center; margin-top: 20px;">
                    <span style="background: linear-gradient(135deg, #F59E0B, #FBBF24); color: #1E293B; padding: 15px 35px; border-radius: 50px; font-weight: 800; font-size: 18px; display: inline-block; box-shadow: 0 8px 25px rgba(245, 158, 11, 0.4);">
                        🏆 Thành lập 2008 - 15+ năm kinh nghiệm
                    </span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ECOSYSTEM SECTION -->
<section class="section ecosystem-section">
    <div class="container">
        <div class="section-header">
            <h2>Hệ Sinh Thái ICOGroup</h2>
            <p>Hệ thống giáo dục và đào tạo toàn diện</p>
        </div>
        
    <div class="ecosystem-grid">
        <div class="ecosystem-card">
            <div class="eco-card-image">
                <img src="<?php echo get_image('index_eco_1_img', 'https://icogroup.vn/vnt_upload/service/Linkedin_3.jpg'); ?>" alt="Trung tâm Ngoại ngữ ICO">
            </div>
            <div class="eco-card-logo">
                <img src="<?php echo get_image('index_eco_1_logo', 'https://icogroup.vn/vnt_upload/service/Logo_TTNN_ICO_24x_100.jpg'); ?>" alt="Logo">
            </div>
            <div class="eco-card-content">
                <h3><?php echo get_text('index_eco_1_name', 'Trung tâm Ngoại ngữ ICO'); ?></h3>
                <p class="eco-slogan"><?php echo get_text('index_eco_1_slogan', 'Học ngoại ngữ để lập nghiệp'); ?></p>
                <p><?php echo get_text('index_eco_1_desc', 'Đào tạo tiếng Nhật, tiếng Đức, tiếng Hàn với đội ngũ giáo viên chất lượng cao và phương pháp hiện đại.'); ?></p>
                <a href="ngoai-ngu-ico.php" class="eco-btn">Xem thêm</a>
            </div>
        </div>
        
        <div class="ecosystem-card">
            <div class="eco-card-image">
                <img src="<?php echo get_image('index_eco_2_img', 'https://icogroup.vn/vnt_upload/service/khai_giang_icoschool.jpg'); ?>" alt="ICOSchool">
            </div>
            <div class="eco-card-logo">
                <img src="<?php echo get_image('index_eco_2_logo', 'https://icogroup.vn/vnt_upload/service/mmicon2.jpg'); ?>" alt="Logo">
            </div>
            <div class="eco-card-content">
                <h3><?php echo get_text('index_eco_2_name', 'ICOSchool'); ?></h3>
                <p class="eco-slogan"><?php echo get_text('index_eco_2_slogan', 'Go Global! - Hãy bước ra thế giới'); ?></p>
                <p><?php echo get_text('index_eco_2_desc', 'Trường THPT chất lượng cao, hoạt động theo mô hình chuyên ngữ với chương trình giáo dục chuẩn quốc tế.'); ?></p>
                <a href="icoschool.php" class="eco-btn">Xem thêm</a>
            </div>
        </div>
        
        <div class="ecosystem-card">
            <div class="eco-card-image">
                <img src="<?php echo get_image('index_eco_3_img', 'https://icogroup.vn/vnt_upload/service/mmimg3.jpg'); ?>" alt="ICOCollege">
            </div>
            <div class="eco-card-logo">
                <img src="<?php echo get_image('index_eco_3_logo', 'https://icogroup.vn/vnt_upload/service/mmicon3.jpg'); ?>" alt="Logo">
            </div>
            <div class="eco-card-content">
                <h3><?php echo get_text('index_eco_3_name', 'ICOCollege'); ?></h3>
                <p class="eco-slogan">Go Global! - Hãy bước ra thế giới</p>
                <p><?php echo get_text('index_eco_3_desc', 'Cao đẳng nghề chất lượng cao với cam kết việc làm sau tốt nghiệp và đào tạo theo đơn đặt hàng.'); ?></p>
                <a href="icocollege.php" class="eco-btn">Xem thêm</a>
            </div>
        </div>
        
        <div class="ecosystem-card">
            <div class="eco-card-image">
                <img src="<?php echo get_image('index_eco_4_img', 'https://icogroup.vn/vnt_upload/service/mmimg4.jpg'); ?>" alt="ICOCareer">
            </div>
            <div class="eco-card-logo">
                <img src="https://icogroup.vn/vnt_upload/service/mmicon3.jpg" alt="Logo">
            </div>
            <div class="eco-card-content">
                <h3><?php echo get_text('index_eco_4_name', 'ICOCareer'); ?></h3>
                <p class="eco-slogan">Định hướng tương lai</p>
                <p><?php echo get_text('index_eco_4_desc', 'Hướng nghiệp, tư vấn nghề nghiệp và kết nối việc làm trong nước và quốc tế cho học viên.'); ?></p>
                <a href="icocareer.php" class="eco-btn">Xem thêm</a>
            </div>
        </div>
    </div>
    </div>
</section>

<!-- STATISTICS SECTION -->
<section class="stats-section">
    <div class="stats-grid">
        <div class="stat-item">
            <span class="stat-number" data-target="<?php echo get_text('stat_duhoc', '17000'); ?>">0</span>
            <span class="stat-label"><?php echo get_text('stat_duhoc_label', 'Du học sinh'); ?></span>
        </div>
        <div class="stat-item">
            <span class="stat-number" data-target="<?php echo get_text('stat_laodong', '38000'); ?>">0</span>
            <span class="stat-label"><?php echo get_text('stat_laodong_label', 'Lao động quốc tế'); ?></span>
        </div>
        <div class="stat-item">
            <span class="stat-number" data-target="<?php echo get_text('stat_doitac', '600'); ?>">0</span>
            <span class="stat-label"><?php echo get_text('stat_doitac_label', 'Đối tác doanh nghiệp'); ?></span>
        </div>
        <div class="stat-item">
            <span class="stat-number" data-target="<?php echo get_text('stat_truong', '300'); ?>">0</span>
            <span class="stat-label"><?php echo get_text('stat_truong_label', 'Trường liên kết'); ?></span>
        </div>
    </div>
</section>

<!-- PROGRAMS SECTION - Dark Theme -->
<?php
$programs_bg_url = get_image('index_programs_bg', '');
$programs_style = $programs_bg_url ? "background: url('$programs_bg_url') no-repeat center center/cover;" : "background: linear-gradient(180deg, #0F172A 0%, #1E293B 100%);";
?>
<section class="section programs-section" style="<?php echo $programs_style; ?> padding: 100px 0;">
    <div class="container">
        <div class="section-header">
            <h2 style="color: white;">Chương Trình Nổi Bật</h2>
            <p style="color: rgba(255,255,255,0.7);">Đa dạng lựa chọn phù hợp với nhu cầu của bạn</p>
        </div>
        
        <div class="programs-grid">
            <div class="program-card" onclick="window.location='nhat.php'" style="cursor:pointer">
                <div class="program-image">
                    <img src="<?php echo get_image('index_program_1_img', 'https://cdn-images.vtv.vn/562122370168008704/2023/7/26/untitled-1690344019340844974097.png'); ?>" alt="Du học Nhật Bản">
                </div>
                <div class="program-content">
                    <span class="program-tag">Du học</span>
                    <h3><?php echo get_text('index_program_1_title', 'Du Học Nhật Bản'); ?></h3>
                    <p><?php echo get_text('index_program_1_desc', 'Chương trình du học Nhật Bản với 100+ trường đối tác. Học bổng hấp dẫn, visa cao.'); ?></p>
                    <a href="nhat.php" class="program-link">
                        Tìm hiểu thêm 
                        <span class="material-symbols-outlined">arrow_forward</span>
                    </a>
                </div>
            </div>
            
            <div class="program-card" onclick="window.location='duc.php'" style="cursor:pointer">
                <div class="program-image">
                    <img src="<?php echo get_image('index_program_2_img', 'https://icogroup.vn/vnt_upload/weblink/banner_chu_04.jpg'); ?>" alt="Du học Đức">
                </div>
                <div class="program-content">
                    <span class="program-tag">Du học</span>
                    <h3><?php echo get_text('index_program_2_title', 'Du Học Đức'); ?></h3>
                    <p><?php echo get_text('index_program_2_desc', 'Du học kép (Ausbildung): Học miễn phí, có lương, việc làm ngay sau tốt nghiệp.'); ?></p>
                    <a href="duc.php" class="program-link">
                        Tìm hiểu thêm 
                        <span class="material-symbols-outlined">arrow_forward</span>
                    </a>
                </div>
            </div>
            
            <div class="program-card" onclick="window.location='xkldjp.php'" style="cursor:pointer">
                <div class="program-image">
                    <img src="<?php echo get_image('index_program_3_img', 'https://icogroup.vn/vnt_upload/weblink/banner_chu_04.jpg'); ?>" alt="Xuất khẩu lao động">
                </div>
                <div class="program-content">
                    <span class="program-tag">XKLĐ</span>
                    <h3><?php echo get_text('index_program_3_title', 'Xuất Khẩu Lao Động Nhật Bản'); ?></h3>
                    <p><?php echo get_text('index_program_3_desc', 'Chương trình thực tập sinh kỹ năng với thu nhập từ 30-40 triệu/tháng.'); ?></p>
                    <a href="xkldjp.php" class="program-link">
                        Tìm hiểu thêm 
                        <span class="material-symbols-outlined">arrow_forward</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- NEWS SECTION - Light Theme with Top Divider -->
<?php
$news_bg_url = get_image('index_news_bg', '');
$news_style = $news_bg_url ? "background: url('$news_bg_url') no-repeat center center/cover;" : "background: linear-gradient(180deg, #FAFAFA 0%, #FFFFFF 100%);";
?>
<section class="section news-section" style="<?php echo $news_style; ?> padding: 100px 0; border-top: 4px solid #6366F1;">
    <div class="container">
        <div class="section-header">
            <h2>Tin Tức & Hoạt Động</h2>
            <p>Cập nhật những thông tin mới nhất từ ICOGroup</p>
        </div>
        
        <div class="news-grid">
            <?php
            // Fetch news from API
            $newsData = @file_get_contents('http://localhost/web8s/backend_api/news_api.php?limit=6');
            if ($newsData) {
                $newsItems = json_decode($newsData, true);
                if (is_array($newsItems) && count($newsItems) > 0) {
                    foreach ($newsItems as $news) {
                        $image = !empty($news['image_url']) ? $news['image_url'] : 'https://via.placeholder.com/400x250?text=No+Image';
                        $title = htmlspecialchars($news['title']);
                        $date = date('d/m/Y', strtotime($news['created_at']));
                        $newsId = $news['id'];
                        ?>
                        <div class="news-card" onclick="window.location='tin-tuc.php?id=<?php echo $newsId; ?>'" style="cursor:pointer">
                            <div class="news-image">
                                <img src="<?php echo $image; ?>" alt="<?php echo $title; ?>">
                            </div>
                            <div class="news-content">
                                <div class="news-date">📅 <?php echo $date; ?></div>
                                <h3><?php echo $title; ?></h3>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    echo '<p style="text-align:center; color:#888; grid-column: 1/-1;">Chưa có tin tức nào. Vui lòng thêm tin tức trong trang quản trị.</p>';
                }
            } else {
                echo '<p style="text-align:center; color:#888; grid-column: 1/-1;">Không thể tải tin tức.</p>';
            }
            ?>
        </div>
        
        <div style="text-align: center; margin-top: 40px;">
            <a href="hoatdong.php" class="hero-btn">Xem tất cả tin tức</a>
        </div>
    </div>
</section>

<!-- REGISTRATION FORM SECTION - With Background -->
<section class="form-section" id="dangky" style="background: linear-gradient(135deg, rgba(30, 27, 75, 0.95) 0%, rgba(99, 102, 241, 0.9) 100%), url('https://icogroup.vn/vnt_upload/news/02_2025/ICOGROUP_TUYEN_DUNG_23.jpg'); background-size: cover; background-position: center; padding: 100px 0;">
    <div class="form-container">
        <h3>🎯 ĐĂNG KÝ TƯ VẤN MIỄN PHÍ</h3>
        
        <form id="userRegistrationForm">
            <div class="form-group">
                <label for="ho_ten">Họ và Tên:</label>
                <input type="text" id="ho_ten" name="ho_ten" required placeholder="Nhập họ tên của bạn...">
            </div>
            
            <div class="form-group">
                <label for="nam_sinh">Năm Sinh:</label>
                <input type="text" id="nam_sinh" name="nam_sinh" required maxlength="4" placeholder="Ví dụ: 2005">
            </div>
            
            <div class="form-group">
                <label for="dia_chi">Địa Chỉ:</label>
                <input type="text" id="dia_chi" name="dia_chi" required placeholder="Tỉnh/Thành phố...">
            </div>
            
            <div class="form-group">
                <label for="chuong_trinh">Chương Trình Quan Tâm:</label>
                <select id="chuong_trinh" name="chuong_trinh" required>
                    <option value="Du học">Du học</option>
                    <option value="Xuất khẩu lao động">Xuất khẩu lao động</option>
                    <option value="Đào tạo ngoại ngữ">Đào tạo ngoại ngữ</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="quoc_gia">Quốc Gia Muốn Đến:</label>
                <select id="quoc_gia" name="quoc_gia">
                    <option value="Nhật Bản">Nhật Bản</option>
                    <option value="Đức">Đức</option>
                    <option value="Hàn Quốc">Hàn Quốc</option>
                    <option value="Đài Loan">Đài Loan</option>
                    <option value="Khác">Khác</option>
                </select>
            </div>
            
            <div class="form-group" id="quoc_gia_khac_box" style="display: none;">
                <label for="quoc_gia_khac">Nhập quốc gia khác:</label>
                <input type="text" id="quoc_gia_khac" name="quoc_gia_khac" placeholder="Tên quốc gia...">
            </div>
            
            <div class="form-group">
                <label for="sdt">Số Điện Thoại:</label>
                <input type="tel" id="sdt" name="sdt" required maxlength="11" pattern="[0-9]{9,11}" placeholder="Nhập số điện thoại...">
            </div>
            
            <button type="submit" class="form-submit">GỬI THÔNG TIN</button>
            <p id="message"></p>
        </form>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
