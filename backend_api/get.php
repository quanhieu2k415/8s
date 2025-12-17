<?php
// API Get Users - ICOGroup
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *'); 
header('Access-Control-Allow-Methods: GET');

// Database config
$servername = "localhost";
$username = "root";       
$password = "";           
$dbname = "db_nhanluc";   
$table_name = "user";     

// Connect to database
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(array(
        "message" => "Lỗi kết nối Database. Vui lòng kiểm tra XAMPP MySQL đã chạy chưa.", 
        "status" => false,
        "error" => $conn->connect_error
    ));
    exit;
}

// Set charset
$conn->set_charset("utf8mb4");

// Check if table exists
$table_check = $conn->query("SHOW TABLES LIKE '$table_name'");
if ($table_check->num_rows == 0) {
    // Create table if not exists
    $create_sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ngay_nhan TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        ho_ten VARCHAR(255) NOT NULL,
        nam_sinh VARCHAR(10),
        dia_chi VARCHAR(500),
        chuong_trinh VARCHAR(255),
        quoc_gia VARCHAR(255),
        sdt VARCHAR(20),
        ghi_chu TEXT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if (!$conn->query($create_sql)) {
        http_response_code(500);
        echo json_encode(array(
            "message" => "Không thể tạo bảng user.", 
            "status" => false,
            "error" => $conn->error
        ));
        exit;
    }
}

// Get data
$sql = "SELECT id, ngay_nhan, ho_ten, nam_sinh, dia_chi, chuong_trinh, quoc_gia, sdt, ghi_chu FROM $table_name ORDER BY id DESC";
$result = $conn->query($sql);

if ($result === false) {
    http_response_code(500);
    echo json_encode(array(
        "message" => "Lỗi truy vấn database.", 
        "status" => false,
        "error" => $conn->error
    ));
    exit;
}

$data = array();
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

http_response_code(200);
// Return plain array directly for compatibility with admin JS
echo json_encode($data);

$conn->close();
?>