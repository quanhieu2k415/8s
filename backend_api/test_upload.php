<?php
// Simple test to see if upload_image.php is accessible
header('Content-Type: application/json');

echo json_encode([
    'status' => true,
    'message' => 'Backend API is working!',
    'php_version' => phpversion(),
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'post_max_size' => ini_get('post_max_size')
]);
?>
