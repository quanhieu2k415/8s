<?php
// db_config.php

$servername = "localhost";
$username = "root";       
$password = "";           
$dbname = "db_nhanluc";   
// ⭐ Biến tên bảng nội dung cần được định nghĩa ở đây ⭐
$content_table = "content_pages"; 
// Biến tên bảng người dùng (nếu có)
$user_table = "user"; 

// Khởi tạo kết nối
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    // Nếu lỗi kết nối, KHÔNG gán $conn
    // Nếu kết nối thành công, $conn sẽ là đối tượng mysqli.
    http_response_code(500); 
    die(json_encode(array("message" => "Lỗi kết nối Database.", "status" => false)));
}

// Hàm giải phóng kết nối (Không bắt buộc phải đóng ở đây nếu bạn muốn dùng tiếp)
function closeConnection($conn, $stmt = null) {
    if ($stmt) {
        $stmt->close();
    }
    if ($conn) {
        $conn->close();
    }
}
?>