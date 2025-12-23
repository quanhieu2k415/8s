<?php
// save_content.php
include 'db_config.php'; 
session_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); 
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

$data = json_decode(file_get_contents("php://input"), true);
$section_key = $data['section_key'] ?? null;
$content_value = $data['content_value'] ?? ''; // Cho phép nội dung rỗng
$username = $_SESSION['username'] ?? 'unknown';

if (empty($section_key)) {
    http_response_code(400); 
    echo json_encode(array("message" => "Thiếu key nội dung để cập nhật.", "status" => false));
    closeConnection($conn);
    exit();
}

// UPSERT với tracking
$sql = "INSERT INTO $content_table (section_key, content_value, updated_by, updated_at) 
        VALUES (?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE content_value = ?, updated_by = ?, updated_at = NOW()";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sssss", $section_key, $content_value, $username, $content_value, $username);

if ($stmt->execute()) {
    http_response_code(200);
    echo json_encode(array(
        "message" => "Cập nhật nội dung Key '$section_key' thành công.", 
        "status" => true,
        "updated_by" => $username,
        "updated_at" => date('Y-m-d H:i:s')
    ));
} else {
    http_response_code(500);
    echo json_encode(array("message" => "Lỗi thực thi SQL: " . $stmt->error, "status" => false));
}

closeConnection($conn, $stmt);
?>