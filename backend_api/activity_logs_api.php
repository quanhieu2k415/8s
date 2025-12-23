<?php
/**
 * Activity Logs API
 * Get activity logs from database
 */

require_once __DIR__ . '/bootstrap.php';

use App\Core\Database;
use App\Services\Auth;
use App\Services\Permission;

header('Content-Type: application/json');

$auth = Auth::getInstance();
$permission = Permission::getInstance();

// Check authentication
if (!$auth->check()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$currentUser = $auth->user();
$userRole = $currentUser['role'] ?? 'user';

// Admin always has access, others check permissions
$canViewAllLogs = ($userRole === 'admin') || $permission->canViewAllLogs($userRole);
$canViewTeamLogs = $permission->check($userRole, 'logs.view_team');

// Everyone can view at least their own logs
// So we don't block completely, just filter later

$action = $_GET['action'] ?? 'list';

switch ($action) {
    case 'list':
        getActivityLogs($canViewAllLogs, $currentUser);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

function getActivityLogs($canViewAll, $currentUser) {
    try {
        $db = Database::getInstance();
        
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
        $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
        $search = $_GET['search'] ?? '';
        $actionFilter = $_GET['action_filter'] ?? '';
        
        // Build query
        $params = [];
        $where = [];
        
        // If not admin viewing all, filter by user or team
        if (!$canViewAll) {
            $where[] = "user_id = :user_id";
            $params[':user_id'] = $currentUser['id'];
        }
        
        // Search filter
        if (!empty($search)) {
            $where[] = "(username LIKE :search OR details LIKE :search2 OR ip_address LIKE :search3)";
            $params[':search'] = "%$search%";
            $params[':search2'] = "%$search%";
            $params[':search3'] = "%$search%";
        }
        
        // Action filter
        if (!empty($actionFilter)) {
            $where[] = "action = :action";
            $params[':action'] = $actionFilter;
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM activity_logs $whereClause";
        $countResult = $db->fetchAll($countSql, $params);
        $total = $countResult[0]['total'] ?? 0;
        
        // Get logs
        $sql = "SELECT id, user_id, username, role, action, target_type, target_id, 
                       details, ip_address, created_at 
                FROM activity_logs 
                $whereClause 
                ORDER BY created_at DESC 
                LIMIT :limit OFFSET :offset";
        
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;
        
        $logs = $db->fetchAll($sql, $params);
        
        echo json_encode([
            'success' => true,
            'data' => $logs,
            'total' => (int)$total,
            'limit' => $limit,
            'offset' => $offset
        ], JSON_UNESCAPED_UNICODE);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
}
