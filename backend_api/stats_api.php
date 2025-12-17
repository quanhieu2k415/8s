<?php
// Statistics API - ICOGroup
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, PUT');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db_config.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        getStats();
        break;
    case 'PUT':
        updateStats();
        break;
    default:
        echo json_encode(['status' => false, 'message' => 'Method not allowed']);
}

function getStats() {
    global $conn;
    
    $result = $conn->query("SELECT * FROM statistics ORDER BY id");
    $stats = [];
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $stats[] = $row;
        }
    }
    
    echo json_encode($stats);
}

function updateStats() {
    global $conn;
    
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (empty($data['stat_key']) || !isset($data['stat_value'])) {
        echo json_encode(['status' => false, 'message' => 'Thiếu thông tin']);
        return;
    }
    
    $key = $data['stat_key'];
    $value = intval($data['stat_value']);
    $label = isset($data['stat_label']) ? $data['stat_label'] : null;
    
    if ($label) {
        $stmt = $conn->prepare("UPDATE statistics SET stat_value = ?, stat_label = ? WHERE stat_key = ?");
        $stmt->bind_param("iss", $value, $label, $key);
    } else {
        $stmt = $conn->prepare("UPDATE statistics SET stat_value = ? WHERE stat_key = ?");
        $stmt->bind_param("is", $value, $key);
    }
    
    if ($stmt->execute()) {
        echo json_encode(['status' => true, 'message' => 'Cập nhật thống kê thành công']);
    } else {
        echo json_encode(['status' => false, 'message' => 'Lỗi: ' . $stmt->error]);
    }
    
    $stmt->close();
}

closeConnection($conn);
?>
