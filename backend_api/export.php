<?php
// Export CSV - ICOGroup
date_default_timezone_set('Asia/Ho_Chi_Minh'); 

// Thông tin kết nối Database
$servername = "localhost";
$username = "root";
$password = ""; 
$dbname = "db_nhanluc";
$table_name = "user";

// Kết nối Database
$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8mb4"); 

if ($conn->connect_error) {
    header('Content-Type: application/json');
    http_response_code(500);
    die(json_encode(array("message" => "Lỗi kết nối Database.", "status" => false)));
}

// Thực thi truy vấn SELECT
$sql = "SELECT id, ngay_nhan, ho_ten, sdt, nam_sinh, dia_chi, chuong_trinh, quoc_gia, ghi_chu FROM $table_name ORDER BY id ASC";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    
    // Tạo tên file
    $file_name = 'danh_sach_dang_ky_' . date('Ymd_His') . '.csv';
    
    // Headers để download file
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $file_name . '"');
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');
    
    // Output BOM UTF-8 để Excel đọc được tiếng Việt
    echo "\xEF\xBB\xBF";
    
    // Mở output stream
    $output = fopen('php://output', 'w');
    
    // Header row
    fputcsv($output, ['ID', 'Ngày nhận', 'Họ tên', 'SĐT', 'Năm sinh', 'Địa chỉ', 'Chương trình', 'Quốc gia', 'Ghi chú']);
    
    // Data rows
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['id'],
            $row['ngay_nhan'],
            $row['ho_ten'],
            $row['sdt'],
            $row['nam_sinh'],
            $row['dia_chi'],
            $row['chuong_trinh'],
            $row['quoc_gia'],
            $row['ghi_chu']
        ]);
    }
    
    fclose($output);

} else {
    header('Content-Type: application/json');
    http_response_code(200);
    echo json_encode(array("message" => "Không có dữ liệu để xuất.", "status" => true));
}

$conn->close();
?>