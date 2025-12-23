<?php
// Footer component - ICOGroup Website
?>

<!-- FOOTER -->
<footer class="footer-section">
    <div class="footer-container">
        <div class="footer-main">
            <!-- Company Info -->
            <div class="footer-col company-info">
                <img src="https://www.icogroup.vn/vnt_upload/company/Logo_icogroup4x.png" alt="ICOGroup" class="footer-logo">
                <h3>CÔNG TY CỔ PHẦN QUỐC TẾ ICO</h3>
                <p><strong>📌 Địa chỉ:</strong> <?php echo get_text('global_footer_address', 'Số 360, đường Phan Đình Phùng, tỉnh Thái Nguyên'); ?></p>
                <p><strong>📞 Hotline:</strong> <a href="tel:<?php echo get_text('header_phone', '0822314555'); ?>" style="color: #f472b6; text-decoration: none;"><?php echo get_text('header_phone_display', '0822.314.555'); ?></a></p>
                <p><strong>✉️ Email:</strong> <a href="mailto:<?php echo get_text('header_email', 'info@icogroup.vn'); ?>" style="color: #a5b4fc; text-decoration: none;"><?php echo get_text('header_email', 'info@icogroup.vn'); ?></a></p>
                
                <div class="social-links">
                    <?php 
                    $fb_icon = get_image('global_facebook_icon', 'https://cdn-icons-png.flaticon.com/512/733/733547.png');
                    $zalo_icon = get_image('global_zalo_icon', 'https://upload.wikimedia.org/wikipedia/commons/9/91/Icon_of_Zalo.svg');
                    $yt_icon = get_image('global_youtube_icon', 'https://cdn-icons-png.flaticon.com/512/1384/1384060.png');
                    ?>
                    <a href="<?php echo get_text('global_facebook_url', 'https://facebook.com/icogroup'); ?>" target="_blank" title="Facebook">
                        <img src="<?php echo $fb_icon; ?>" alt="Facebook">
                    </a>
                    <a href="<?php echo get_text('global_zalo_url', 'https://zalo.me/0822314555'); ?>" target="_blank" title="Zalo">
                        <img src="<?php echo $zalo_icon; ?>" alt="Zalo">
                    </a>
                    <a href="<?php echo get_text('global_youtube_url', 'https://youtube.com/@icogroup'); ?>" target="_blank" title="YouTube">
                        <img src="<?php echo $yt_icon; ?>" alt="YouTube">
                    </a>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="footer-col">
                <h4>LIÊN KẾT NHANH</h4>
                <ul>
                    <li><a href="ve-icogroup.php">Về ICOGroup</a></li>
                    <li><a href="huong-nghiep.php">Hướng nghiệp</a></li>
                    <li><a href="hoatdong.php">Hoạt động</a></li>
                    <li><a href="lienhe.php">Liên hệ</a></li>
                </ul>
            </div>

            <!-- Study Abroad -->
            <div class="footer-col">
                <h4>DU HỌC</h4>
                <ul>
                    <li><a href="duc.php">Du học Đức</a></li>
                    <li><a href="nhat.php">Du học Nhật Bản</a></li>
                    <li><a href="han.php">Du học Hàn Quốc</a></li>
                </ul>
            </div>

            <!-- Labor Export -->
            <div class="footer-col">
                <h4>XUẤT KHẨU LAO ĐỘNG</h4>
                <ul>
                    <li><a href="xkldjp.php">XKLĐ Nhật Bản</a></li>
                    <li><a href="xkldhan.php">XKLĐ Hàn Quốc</a></li>
                    <li><a href="xklddailoan.php">XKLĐ Đài Loan</a></li>
                    <li><a href="xkldchauau.php">XKLĐ Châu Âu</a></li>
                </ul>
            </div>

            <!-- Map -->
            <div class="footer-col footer-map">
                <h4>BẢN ĐỒ</h4>
                <iframe 
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3710.155702228514!2d105.8340643153326!3d21.57969298570265!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31352738b1bf0b2f%3A0x6d691b0c9c7b9e0!2zMzYwIFBoYW4gxJDDrG5oIFBow7luZywgVFAuIFRow6FpIE5ndXnDqm4!5e0!3m2!1svi!2s!4v1620000000000!5m2!1svi!2s" 
                    width="100%" 
                    height="200" 
                    style="border:0; border-radius:10px;" 
                    loading="lazy" 
                    referrerpolicy="no-referrer-when-downgrade">
                </iframe>
            </div>
        </div>

        <div class="footer-bottom">
            <p>© <?php echo date('Y'); ?> ICOGroup. All rights reserved. | <a href="#">Chính sách bảo mật</a> | <a href="#">Điều khoản sử dụng</a></p>
        </div>
    </div>
</footer>

<!-- Back to Top Button -->
<button id="backToTop" class="back-to-top" onclick="scrollToTop()">
    <span class="material-symbols-outlined">arrow_upward</span>
</button>

<script src="script.js"></script>
</body>
</html>
