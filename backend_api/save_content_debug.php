<?php
// save_content_debug.php - Temporary debug version
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); 
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, X-CSRF-Token');

// Log all incoming data
error_log("DEBUG save_content: " . print_r($_SERVER, true));

include 'db_config.php';

$data = json_decode(file_get_contents("php://input"), true);
error_log("DEBUG data: " . print_r($data, true));

$section_key = $data['section_key'] ?? null;
$content_value = $data['content_value'] ?? '';

if (empty($section_key)) {
    http_response_code(400); 
    echo json_encode(array("message" => "Thiếu key nội dung", "status" => false, "debug" => "no section_key"));
    exit();
}

try {
    // UPSERT
    $sql = "INSERT INTO $content_table (section_key, content_value, updated_by, updated_at) 
            VALUES (?, ?, 'debug', NOW())
            ON DUPLICATE KEY UPDATE content_value = ?, updated_by = 'debug', updated_at = NOW()";
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        echo json_encode(array(
            "message" => "SQL prepare failed",
            "status" => false,
            "error" => $conn->error,
            "table" => $content_table
        ));
        exit();
    }
    
    $stmt->bind_param("sss", $section_key, $content_value, $content_value);
    
    if ($stmt->execute()) {
        echo json_encode(array(
            "message" => "Cập nhật thành công: $section_key", 
            "status" => true,
            "affected_rows" => $stmt->affected_rows
        ));
    } else {
        echo json_encode(array(
            "message" => "Execute failed",
            "status" => false,
            "error" => $stmt->error
        ));
    }
    
    $stmt->close();
} catch (Exception $e) {
    echo json_encode(array(
        "message" => "Exception",
        "status" => false,
        "error" => $e->getMessage()
    ));
}

$conn->close();
?>
