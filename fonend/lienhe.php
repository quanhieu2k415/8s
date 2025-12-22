<?php
include_once 'includes/content_helper.php';
$pageTitle = "Liên hệ";
$pageDescription = "Liên hệ với ICOGroup - Hotline: 0822.314.555. Địa chỉ: Số 360, đường Phan Đình Phùng, tỉnh Thái Nguyên.";
include 'includes/header.php';
?>

<!-- PAGE BANNER -->
<!-- PAGE BANNER -->
<?php
$header_bg = get_image('contact_header_bg', '');
$header_style = $header_bg ? "background: url('$header_bg') no-repeat center center/cover;" : "";
?>
<section class="page-banner" style="<?php echo $header_style; ?>">
    <h1>Liên Hệ Với Chúng Tôi</h1>
    <p>Chúng tôi luôn sẵn sàng hỗ trợ bạn</p>
    <div class="breadcrumb">
        <a href="index.php">Trang chủ</a>
        <span>/</span>
        <span>Liên hệ</span>
    </div>
</section>

<!-- CONTACT INFO -->
<section class="section about-section">
    <div class="container">
        <div class="about-grid">
            <div>
                <h2 style="color: var(--primary-blue); margin-bottom: 30px;">Thông Tin Liên Hệ</h2>
                
                <div style="margin-bottom: 25px;">
                    <h4 style="color: var(--accent-orange); margin-bottom: 10px;">📍 Địa Chỉ</h4>
                    <p style="font-size: 18px;">Số 360, đường Phan Đình Phùng, tỉnh Thái Nguyên</p>
                </div>
                
                <div style="margin-bottom: 25px;">
                    <h4 style="color: var(--accent-orange); margin-bottom: 10px;">📞 Hotline</h4>
                    <p style="font-size: 24px; font-weight: bold; color: var(--primary-blue);">0822.314.555</p>
                </div>
                
                <div style="margin-bottom: 25px;">
                    <h4 style="color: var(--accent-orange); margin-bottom: 10px;">✉️ Email</h4>
                    <p style="font-size: 18px;">info@icogroup.vn</p>
                </div>
                
                <div style="margin-bottom: 25px;">
                    <h4 style="color: var(--accent-orange); margin-bottom: 10px;">🕐 Giờ làm việc</h4>
                    <p>Thứ 2 - Thứ 6: 8:00 - 17:30</p>
                    <p>Thứ 7: 8:00 - 12:00</p>
                </div>
                
                <div class="social-links" style="margin-top: 30px;">
                    <a href="https://facebook.com/icogroup.vn" target="_blank" style="background: #1877f2; width: 50px; height: 50px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center;">
                        <img src="https://cdn-icons-png.flaticon.com/512/733/733547.png" alt="Facebook" style="width: 25px; filter: brightness(0) invert(1);">
                    </a>
                    <a href="https://zalo.me/0822314555" target="_blank" style="background: #0068ff; width: 50px; height: 50px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-left: 10px;">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/9/91/Icon_of_Zalo.svg" alt="Zalo" style="width: 25px; filter: brightness(0) invert(1);">
                    </a>
                </div>
            </div>
            
            <div>
                <h4 style="margin-bottom: 20px;">Bản Đồ</h4>
                <iframe 
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3710.155702228514!2d105.8340643153326!3d21.57969298570265!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31352738b1bf0b2f%3A0x6d691b0c9c7b9e0!2zMzYwIFBoYW4gxJDDrG5oIFBow7luZywgVFAuIFRow6FpIE5ndXnDqm4!5e0!3m2!1svi!2s!4v1620000000000!5m2!1svi!2s" 
                    width="100%" 
                    height="400" 
                    style="border:0; border-radius:15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);" 
                    loading="lazy">
                </iframe>
            </div>
        </div>
    </div>
</section>

<!-- CONTACT FORM -->
<section class="form-section" id="dangky">
    <div class="form-container">
        <h3>📩 Gửi Yêu Cầu Liên Hệ</h3>
        
        <form id="userRegistrationForm">
            <div class="form-group">
                <label for="ho_ten">Họ và Tên:</label>
                <input type="text" id="ho_ten" name="ho_ten" required placeholder="Nhập họ tên của bạn...">
            </div>
            
            <div class="form-group">
                <label for="nam_sinh">Năm Sinh:</label>
                <input type="text" id="nam_sinh" name="nam_sinh" required maxlength="4" placeholder="VD: 2000">
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
                <input type="text" id="quoc_gia_khac" name="quoc_gia_khac">
            </div>
            
            <div class="form-group">
                <label for="sdt">Số Điện Thoại:</label>
                <input type="tel" id="sdt" name="sdt" required maxlength="11" placeholder="Nhập số điện thoại...">
            </div>
            
            <button type="submit" class="form-submit">GỬI THÔNG TIN</button>
            <p id="message"></p>
        </form>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
