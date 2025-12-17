<?php
// News API - ICOGroup
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db_config.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        getNews();
        break;
    case 'POST':
        createNews();
        break;
    case 'PUT':
        updateNews();
        break;
    case 'DELETE':
        deleteNews();
        break;
    default:
        echo json_encode(['status' => false, 'message' => 'Method not allowed']);
}

function getNews() {
    global $conn;
    
    $id = isset($_GET['id']) ? intval($_GET['id']) : null;
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
    $featured = isset($_GET['featured']) ? true : false;
    
    if ($id) {
        $stmt = $conn->prepare("SELECT * FROM news WHERE id = ?");
        $stmt->bind_param("i", $id);
    } elseif ($featured) {
        $stmt = $conn->prepare("SELECT * FROM news WHERE is_featured = 1 AND status = 'published' ORDER BY created_at DESC LIMIT ?");
        $stmt->bind_param("i", $limit);
    } else {
        $stmt = $conn->prepare("SELECT * FROM news WHERE status = 'published' ORDER BY created_at DESC LIMIT ?");
        $stmt->bind_param("i", $limit);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $news = [];
    
    while ($row = $result->fetch_assoc()) {
        $news[] = $row;
    }
    
    // If fetching single news by ID, return object instead of array
    if ($id && count($news) > 0) {
        echo json_encode($news[0]);
    } else {
        echo json_encode($news);
    }
    $stmt->close();
}

function createNews() {
    global $conn;
    
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (empty($data['title'])) {
        echo json_encode(['status' => false, 'message' => 'Tiêu đề không được để trống']);
        return;
    }
    
    $title = $data['title'];
    $slug = isset($data['slug']) ? $data['slug'] : createSlug($title);
    $excerpt = isset($data['excerpt']) ? $data['excerpt'] : '';
    $content = isset($data['content']) ? $data['content'] : '';
    $image_url = isset($data['image_url']) ? $data['image_url'] : '';
    $category = isset($data['category']) ? $data['category'] : 'tin-tuc';
    $is_featured = isset($data['is_featured']) ? ($data['is_featured'] ? 1 : 0) : 0;
    
    $stmt = $conn->prepare("INSERT INTO news (title, slug, excerpt, content, image_url, category, is_featured) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssi", $title, $slug, $excerpt, $content, $image_url, $category, $is_featured);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => true, 'message' => 'Thêm tin tức thành công', 'id' => $stmt->insert_id]);
    } else {
        echo json_encode(['status' => false, 'message' => 'Lỗi: ' . $stmt->error]);
    }
    
    $stmt->close();
}

function updateNews() {
    global $conn;
    
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (empty($data['id'])) {
        echo json_encode(['status' => false, 'message' => 'ID không được để trống']);
        return;
    }
    
    $id = intval($data['id']);
    $title = $data['title'];
    $excerpt = isset($data['excerpt']) ? $data['excerpt'] : '';
    $content = isset($data['content']) ? $data['content'] : '';
    $image_url = isset($data['image_url']) ? $data['image_url'] : '';
    $category = isset($data['category']) ? $data['category'] : 'tin-tuc';
    $is_featured = isset($data['is_featured']) ? ($data['is_featured'] ? 1 : 0) : 0;
    
    $stmt = $conn->prepare("UPDATE news SET title = ?, excerpt = ?, content = ?, image_url = ?, category = ?, is_featured = ? WHERE id = ?");
    $stmt->bind_param("sssssii", $title, $excerpt, $content, $image_url, $category, $is_featured, $id);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => true, 'message' => 'Cập nhật tin tức thành công']);
    } else {
        echo json_encode(['status' => false, 'message' => 'Lỗi: ' . $stmt->error]);
    }
    
    $stmt->close();
}

function deleteNews() {
    global $conn;
    
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (empty($data['id'])) {
        echo json_encode(['status' => false, 'message' => 'ID không được để trống']);
        return;
    }
    
    $id = intval($data['id']);
    
    $stmt = $conn->prepare("DELETE FROM news WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => true, 'message' => 'Xóa tin tức thành công']);
    } else {
        echo json_encode(['status' => false, 'message' => 'Lỗi: ' . $stmt->error]);
    }
    
    $stmt->close();
}

function createSlug($string) {
    $slug = mb_strtolower($string, 'UTF-8');
    $slug = preg_replace('/[áàảãạăắằẳẵặâấầẩẫậ]/u', 'a', $slug);
    $slug = preg_replace('/[éèẻẽẹêếềểễệ]/u', 'e', $slug);
    $slug = preg_replace('/[íìỉĩị]/u', 'i', $slug);
    $slug = preg_replace('/[óòỏõọôốồổỗộơớờởỡợ]/u', 'o', $slug);
    $slug = preg_replace('/[úùủũụưứừửữự]/u', 'u', $slug);
    $slug = preg_replace('/[ýỳỷỹỵ]/u', 'y', $slug);
    $slug = preg_replace('/[đ]/u', 'd', $slug);
    $slug = preg_replace('/[^a-z0-9\s-]/u', '', $slug);
    $slug = preg_replace('/[\s-]+/', '-', $slug);
    $slug = trim($slug, '-');
    return $slug;
}

closeConnection($conn);
?>
