<?php
/**
 * Script Mở Khóa Tài khoản Admin
 * Hướng dẫn:
 * 1. Upload file này lên server (thư mục gốc)
 * 2. Truy cập: http://your-domain.com/unlock_admin.php
 * 3. Sau khi thấy thông báo thành công, XÓA NGAY FILE NÀY
 */
require_once 'autoloader.php';
use App\Repositories\AdminUserRepository;
// CẤU HÌNH: Username cần mở khóa
$usernameToUnlock = 'admin';
echo "<h1>Unlock Admin Account</h1>";
try {
    $repo = new AdminUserRepository();
    
    // 1. Tìm user
    $user = $repo->findByUsername($usernameToUnlock);
    
    if (!$user) {
        die("<p style='color:red'>Lỗi: Không tìm thấy user '<b>$usernameToUnlock</b>'</p>");
    }
    
    echo "<p>User ID: " . $user['id'] . "</p>";
    
    // 2. Mở khóa
    // Hàm unlock() sẽ reset số lần thử và xóa thời gian khóa
    if ($repo->unlock($user['id'])) {
        echo "<p style='color:green'>✅ THÀNH CÔNG: Đã mở khóa tài khoản '<b>$usernameToUnlock</b>'.</p>";
        echo "<p>Bây giờ bạn có thể đăng nhập lại bình thường.</p>";
        echo "<hr>";
        echo "<p style='color:red; font-weight:bold'>⚠ QUAN TRỌNG: Hãy xóa file này khỏi server ngay lập tức!</p>";
    } else {
        echo "<p style='color:red'>❌ THẤT BẠI: Không thể cập nhật database.</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color:red'>Lỗi hệ thống: " . $e->getMessage() . "</p>";
}
?>