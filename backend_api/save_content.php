<?php
// save_content.php
include 'db_config.php'; 

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); 
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

$data = json_decode(file_get_contents("php://input"), true);
$section_key = $data['section_key'] ?? null;
$content_value = $data['content_value'] ?? ''; // Cho phép nội dung rỗng

if (empty($section_key)) {
    http_response_code(400); 
    echo json_encode(array("message" => "Thiếu key nội dung để cập nhật.", "status" => false));
    closeConnection($conn);
    exit();
}

// UPSERT: Insert nếu chưa tồn tại, Update nếu đã có key đó
$sql = "INSERT INTO $content_table (section_key, content_value) 
        VALUES (?, ?)
        ON DUPLICATE KEY UPDATE content_value = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $section_key, $content_value, $content_value);

if ($stmt->execute()) {
    http_response_code(200);
    echo json_encode(array("message" => "Cập nhật nội dung Key '$section_key' thành công.", "status" => true));
} else {
    http_response_code(500);
    echo json_encode(array("message" => "Lỗi thực thi SQL: " . $stmt->error, "status" => false));
}

closeConnection($conn, $stmt);
?>