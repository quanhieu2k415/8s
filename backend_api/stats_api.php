<?php
/**
 * Statistics API - ICOGroup
 * Returns registration statistics and custom statistics
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, PUT');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../autoloader.php';

use App\Repositories\RegistrationRepository;
use App\Core\Database;

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
    try {
        $registrationRepo = new RegistrationRepository();
        $stats = $registrationRepo->getStats();
        
        // Return in format expected by dashboard
        echo json_encode($stats);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'status' => false,
            'message' => 'Lỗi lấy thống kê: ' . $e->getMessage(),
            'total' => 0,
            'today' => 0,
            'week' => 0,
            'month' => 0
        ]);
    }
}

function updateStats() {
    try {
        $db = Database::getInstance();
        
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (empty($data['stat_key']) || !isset($data['stat_value'])) {
            echo json_encode(['status' => false, 'message' => 'Thiếu thông tin']);
            return;
        }
        
        $key = $data['stat_key'];
        $value = (int) $data['stat_value'];
        $label = $data['stat_label'] ?? null;
        
        if ($label) {
            $sql = "UPDATE statistics SET stat_value = :value, stat_label = :label WHERE stat_key = :key";
            $db->query($sql, [':value' => $value, ':label' => $label, ':key' => $key]);
        } else {
            $sql = "UPDATE statistics SET stat_value = :value WHERE stat_key = :key";
            $db->query($sql, [':value' => $value, ':key' => $key]);
        }
        
        echo json_encode(['status' => true, 'message' => 'Cập nhật thống kê thành công']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
    }
}

