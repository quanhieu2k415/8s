<?php
// upload_image.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => false, 'message' => 'Method Not Allowed']);
    exit;
}

if (!isset($_FILES['image'])) {
    http_response_code(400);
    echo json_encode(['status' => false, 'message' => 'Không tìm thấy file']);
    exit;
}

$file = $_FILES['image'];
$targetDir = "uploads/images/";
$fileName = time() . '_' . basename($file['name']);
$targetFilePath = $targetDir . $fileName;
$fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

// Allow certain file formats
$allowTypes = array('jpg', 'png', 'jpeg', 'gif', 'webp');
if (in_array($fileType, $allowTypes)) {
    // Check file size (5MB max)
    if ($file['size'] > 5000000) {
        echo json_encode(['status' => false, 'message' => 'File quá lớn (Max 5MB)']);
        exit;
    }

    if (move_uploaded_file($file['tmp_name'], $targetFilePath)) {
        // Return relative path or full URL depending on need
        // Assuming backend_api is in root/backend_api/
        $publicUrl = '/web8s/backend_api/' . $targetFilePath;
        echo json_encode([
            'status' => true, 
            'message' => 'Upload thành công', 
            'url' => $publicUrl
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['status' => false, 'message' => 'Lỗi khi lưu file']);
    }
} else {
    http_response_code(400);
    echo json_encode(['status' => false, 'message' => 'Chỉ chấp nhận file ảnh (JPG, JPEG, PNG, GIF, WEBP)']);
}
?>
