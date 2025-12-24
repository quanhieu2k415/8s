<?php
require_once __DIR__ . '/../autoloader.php';

use App\Services\Auth;
use App\Services\ActivityLogger;

// Check authentication
$auth = Auth::getInstance();
if (!$auth->check()) {
    http_response_code(401);
    echo json_encode(array("message" => "Unauthorized", "status" => false));
    exit();
}

$currentUser = $auth->user();

// 1. Cấu hình Headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); 
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 2. Thông tin kết nối Database
$servername = "localhost";
$username = "root";       
$password = "";           
$dbname = "db_nhanluc";   
$table_name = "user";     

// 3. Xử lý dữ liệu đầu vào (Nhận JSON từ Frontend)
$data = json_decode(file_get_contents("php://input"), true);

$id = $data['id'] ?? null; // Chỉ cần ID để xóa

if (empty($id)) {
    http_response_code(400); // Bad Request
    echo json_encode(array("message" => "Thiếu ID người dùng để xóa.", "status" => false));
    exit();
}

// 4. Kết nối Database
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    http_response_code(500); 
    die(json_encode(array("message" => "Lỗi kết nối Database.", "status" => false)));
}

// 5. Chuẩn bị và Thực thi truy vấn DELETE
$sql = "DELETE FROM $table_name WHERE id=?";
$stmt = $conn->prepare($sql);

// "i" : tham số integer (id)
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        // Log activity
        ActivityLogger::getInstance()->logDelete(
            $currentUser['id'],
            $currentUser['username'],
            $currentUser['role'],
            'registration',
            $id,
            "Xóa đăng ký ID: {$id}"
        );
        
        http_response_code(200);
        echo json_encode(array("message" => "Xóa người dùng ID $id thành công.", "status" => true));
    } else {
        http_response_code(404);
        echo json_encode(array("message" => "Không tìm thấy người dùng có ID $id.", "status" => false));
    }
} else {
    http_response_code(500);
    echo json_encode(array("message" => "Lỗi thực thi SQL: " . $stmt->error, "status" => false));
}

// 6. Đóng kết nối
$stmt->close();
$conn->close();

?>