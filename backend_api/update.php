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

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); 
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

$servername = "localhost";
$username = "root";       
$password = "";           
$dbname = "db_nhanluc";   
$table_name = "user";     

$data = json_decode(file_get_contents("php://input"), true);

$id = $data['id'] ?? null;
$ho_ten = $data['ho_ten'] ?? null;
$nam_sinh = $data['nam_sinh'] ?? null;
$dia_chi = $data['dia_chi'] ?? null;
$chuong_trinh = $data['chuong_trinh'] ?? null;
$quoc_gia = $data['quoc_gia'] ?? null;
$sdt = $data['sdt'] ?? null;
$ghi_chu = $data['ghi_chu'] ?? null;

if (empty($id)) {
    http_response_code(400);
    echo json_encode(array("message" => "Thiếu ID người dùng.", "status" => false));
    exit();
}

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    http_response_code(500); 
    die(json_encode(array("message" => "Lỗi kết nối Database.", "status" => false)));
}

$sql = "UPDATE $table_name 
        SET ho_ten=?, nam_sinh=?, dia_chi=?, chuong_trinh=?, quoc_gia=?, sdt=?, ghi_chu=?
        WHERE id=?";
$stmt = $conn->prepare($sql);

$stmt->bind_param("sssssssi", $ho_ten, $nam_sinh, $dia_chi, $chuong_trinh, $quoc_gia, $sdt, $ghi_chu, $id);

if ($stmt->execute()) {
    // Log activity
    ActivityLogger::getInstance()->logUpdate(
        $currentUser['id'],
        $currentUser['username'],
        $currentUser['role'],
        'registration',
        $id,
        "Cập nhật đăng ký: {$ho_ten} - {$chuong_trinh}"
    );
    
    http_response_code(200);
    echo json_encode(array("message" => "Cập nhật dữ liệu thành công!", "status" => true));
} else {
    http_response_code(500);
    echo json_encode(array("message" => "Lỗi SQL: " . $stmt->error, "status" => false));
}

$stmt->close();
$conn->close();
?>  