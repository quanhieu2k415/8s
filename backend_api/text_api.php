<?php
// text_api.php
include 'db_config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

$method = $_SERVER['REQUEST_METHOD'];

// GET: Lấy danh sách văn bản
if ($method === 'GET') {
    $section = $_GET['section'] ?? null;
    $page = $_GET['page'] ?? null;
    $key = $_GET['key'] ?? null;

    $sql = "SELECT * FROM site_texts WHERE 1=1";
    $params = [];
    $types = "";

    if ($section) {
        $sql .= " AND section = ?";
        $params[] = $section;
        $types .= "s";
    }
    if ($page) {
        $sql .= " AND page = ?";
        $params[] = $page;
        $types .= "s";
    }
    if ($key) {
        $sql .= " AND text_key = ?";
        $params[] = $key;
        $types .= "s";
    }

    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    
    echo json_encode(['status' => true, 'data' => $data]);
    closeConnection($conn, $stmt);
    exit;
}

// POST: Thêm hoặc Cập nhật văn bản (Upsert)
if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $key = $input['text_key'] ?? null;
    $value = $input['text_value'] ?? '';
    $type = $input['text_type'] ?? 'paragraph';
    $section = $input['section'] ?? 'general';
    $page = $input['page'] ?? 'global';

    if (!$key) {
        http_response_code(400);
        echo json_encode(['status' => false, 'message' => 'text_key là bắt buộc']);
        exit;
    }

    $sql = "INSERT INTO site_texts (text_key, text_value, text_type, section, page) 
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
            text_value = VALUES(text_value), 
            text_type = VALUES(text_type),
            section = VALUES(section),
            page = VALUES(page)";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $key, $value, $type, $section, $page);

    if ($stmt->execute()) {
        echo json_encode(['status' => true, 'message' => 'Lưu văn bản thành công']);
    } else {
        http_response_code(500);
        echo json_encode(['status' => false, 'message' => 'Lỗi: ' . $stmt->error]);
    }
    
    closeConnection($conn, $stmt);
    exit;
}

// DELETE: Xóa văn bản
if ($method === 'DELETE') {
    $key = $_GET['key'] ?? null;

    if (!$key) {
        http_response_code(400);
        echo json_encode(['status' => false, 'message' => 'text_key là bắt buộc']);
        exit;
    }

    $sql = "DELETE FROM site_texts WHERE text_key = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $key);

    if ($stmt->execute()) {
        echo json_encode(['status' => true, 'message' => 'Xóa văn bản thành công']);
    } else {
        http_response_code(500);
        echo json_encode(['status' => false, 'message' => 'Lỗi: ' . $stmt->error]);
    }
    
    closeConnection($conn, $stmt);
    exit;
}
?>
