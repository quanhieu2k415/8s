<?php
/**
 * Content Blocks API
 * CRUD operations cho content blocks động
 * Requires authentication. Write operations require cms.manage permission.
 */
require_once __DIR__ . '/../autoloader.php';
include 'db_config.php';

use App\Services\Auth;
use App\Services\Permission;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-CSRF-Token');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Check authentication
$auth = Auth::getInstance();
$permission = Permission::getInstance();

if (!$auth->check()) {
    http_response_code(401);
    echo json_encode(['status' => false, 'message' => 'Unauthorized']);
    exit;
}

$currentUser = $auth->user();
$userRole = $currentUser['role'] ?? 'user';

$method = $_SERVER['REQUEST_METHOD'];

// Write operations require content_blocks.manage permission
if (in_array($method, ['POST', 'PUT', 'DELETE']) && !$permission->canManageContentBlocks($userRole)) {
    http_response_code(403);
    echo json_encode(['status' => false, 'message' => 'Không có quyền quản lý content blocks']);
    exit;
}

// GET: Lấy danh sách blocks
if ($method === 'GET') {
    $page = $_GET['page'] ?? null;
    $id = $_GET['id'] ?? null;
    
    if ($id) {
        // Lấy một block cụ thể
        $sql = "SELECT * FROM content_blocks WHERE id = ? AND is_active = 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $block = $result->fetch_assoc();
        
        echo json_encode(['status' => true, 'data' => $block]);
        closeConnection($conn, $stmt);
        exit;
    }
    
    // Lấy danh sách theo page
    $sql = "SELECT * FROM content_blocks WHERE is_active = 1";
    $params = [];
    $types = "";
    
    if ($page) {
        $sql .= " AND page_key = ?";
        $params[] = $page;
        $types .= "s";
    }
    $sql .= " ORDER BY block_order ASC, id ASC";
    
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    $blocks = [];
    while ($row = $result->fetch_assoc()) {
        $blocks[] = $row;
    }
    
    // Lấy danh sách pages có blocks
    $pagesSql = "SELECT DISTINCT page_key FROM content_blocks WHERE is_active = 1 ORDER BY page_key";
    $pagesResult = $conn->query($pagesSql);
    $pages = [];
    while ($pageRow = $pagesResult->fetch_assoc()) {
        $pages[] = $pageRow['page_key'];
    }
    
    echo json_encode([
        'status' => true, 
        'data' => $blocks,
        'pages' => $pages,
        'total' => count($blocks)
    ]);
    closeConnection($conn, $stmt);
    exit;
}

// POST: Tạo block mới
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $username = $currentUser['username'] ?? 'unknown';
    
    if (empty($data['page_key'])) {
        http_response_code(400);
        echo json_encode(['status' => false, 'message' => 'page_key là bắt buộc']);
        exit;
    }
    
    // Lấy max order hiện tại
    $orderSql = "SELECT MAX(block_order) as max_order FROM content_blocks WHERE page_key = ?";
    $orderStmt = $conn->prepare($orderSql);
    $orderStmt->bind_param("s", $data['page_key']);
    $orderStmt->execute();
    $orderResult = $orderStmt->get_result()->fetch_assoc();
    $nextOrder = ($orderResult['max_order'] ?? 0) + 1;
    
    $sql = "INSERT INTO content_blocks (page_key, block_order, block_type, title, image_url, content, updated_by) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    $pageKey = $data['page_key'];
    $blockOrder = $data['block_order'] ?? $nextOrder;
    $blockType = $data['block_type'] ?? 'section';
    $title = $data['title'] ?? '';
    $imageUrl = $data['image_url'] ?? '';
    $content = $data['content'] ?? '';
    
    $stmt->bind_param("sisssss", 
        $pageKey, 
        $blockOrder,
        $blockType,
        $title,
        $imageUrl,
        $content,
        $username
    );
    
    if ($stmt->execute()) {
        $newId = $conn->insert_id;
        echo json_encode([
            'status' => true, 
            'id' => $newId, 
            'message' => 'Tạo block thành công'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['status' => false, 'message' => 'Lỗi: ' . $stmt->error]);
    }
    closeConnection($conn, $stmt);
    exit;
}

// PUT: Cập nhật block
if ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    $username = $currentUser['username'] ?? 'unknown';
    
    if (empty($data['id'])) {
        http_response_code(400);
        echo json_encode(['status' => false, 'message' => 'id là bắt buộc']);
        exit;
    }
    
    $sql = "UPDATE content_blocks SET 
            title = ?, 
            image_url = ?, 
            content = ?, 
            block_order = ?,
            block_type = ?,
            updated_by = ?,
            updated_at = NOW()
            WHERE id = ?";
    $stmt = $conn->prepare($sql);
    
    $title = $data['title'] ?? '';
    $imageUrl = $data['image_url'] ?? '';
    $content = $data['content'] ?? '';
    $blockOrder = $data['block_order'] ?? 0;
    $blockType = $data['block_type'] ?? 'section';
    $id = $data['id'];
    
    $stmt->bind_param("sssisii", 
        $title,
        $imageUrl,
        $content,
        $blockOrder,
        $blockType,
        $username,
        $id
    );
    
    if ($stmt->execute()) {
        echo json_encode(['status' => true, 'message' => 'Cập nhật thành công']);
    } else {
        http_response_code(500);
        echo json_encode(['status' => false, 'message' => 'Lỗi: ' . $stmt->error]);
    }
    closeConnection($conn, $stmt);
    exit;
}

// DELETE: Xóa block (soft delete)
if ($method === 'DELETE') {
    $id = $_GET['id'] ?? null;
    
    if (!$id) {
        http_response_code(400);
        echo json_encode(['status' => false, 'message' => 'id là bắt buộc']);
        exit;
    }
    
    $sql = "UPDATE content_blocks SET is_active = 0 WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => true, 'message' => 'Xóa thành công']);
    } else {
        http_response_code(500);
        echo json_encode(['status' => false, 'message' => 'Lỗi: ' . $stmt->error]);
    }
    closeConnection($conn, $stmt);
    exit;
}

// Method không hợp lệ
http_response_code(405);
echo json_encode(['status' => false, 'message' => 'Method không được hỗ trợ']);
closeConnection($conn);
?>
