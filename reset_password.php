<?php
/**
 * Script Reset Mật khẩu Admin
 * Hướng dẫn:
 * 1. Upload file này lên server (thư mục gốc)
 * 2. Truy cập: http://your-domain.com/reset_password.php
 * 3. Sau khi thấy thông báo thành công, XÓA NGAY FILE NÀY
 */
// Load mã nguồn hệ thống
require_once 'autoloader.php';
use App\Repositories\AdminUserRepository;
// CẤU HÌNH: Điền mật khẩu mới bạn muốn đặt
$newPassword = 'CR7@2025'; 
$usernameToReset = 'admin';
echo "<h1>Reset Admin Password</h1>";
try {
    $repo = new AdminUserRepository();
    
    // 1. Tìm user admin
    $user = $repo->findByUsername($usernameToReset);
    
    if (!$user) {
        die("<p style='color:red'>Lỗi: Không tìm thấy user có tên '<b>$usernameToReset</b>'</p>");
    }
    
    echo "<p>Đã tìm thấy user ID: " . $user['id'] . "</p>";
    
    // 2. Cập nhật mật khẩu
    // Hàm updatePassword của hệ thống sẽ tự động mã hóa (hash) mật khẩu an toàn
    if ($repo->updatePassword($user['id'], $newPassword)) {
        echo "<p style='color:green'>✅ THÀNH CÔNG: Đã đổi mật khẩu cho user '<b>$usernameToReset</b>'.</p>";
        echo "<p>Mật khẩu mới là: <b>$newPassword</b></p>";
        echo "<hr>";
        echo "<p style='color:red; font-weight:bold'>⚠ QUAN TRỌNG: Hãy xóa file này khỏi server ngay lập tức!</p>";
    } else {
        echo "<p style='color:red'>❌ THẤT BẠI: Không thể cập nhật database.</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color:red'>Lỗi hệ thống: " . $e->getMessage() . "</p>";
}
?>