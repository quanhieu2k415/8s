<?php
// get_content.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'db_config.php'; 

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); 
header('Access-Control-Allow-Methods: GET');

$sql = "SELECT section_key, content_value FROM $content_table";
$result = $conn->query($sql);

$content = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $content[] = $row;
    }
}

closeConnection($conn);
http_response_code(200);
echo json_encode($content);
?>