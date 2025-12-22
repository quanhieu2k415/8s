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

// Get date range parameters
$dateFrom = isset($_GET['from']) ? $_GET['from'] : null;
$dateTo = isset($_GET['to']) ? $_GET['to'] : null;

// Build SQL query with optional date filter
$sql = "SELECT id, ngay_nhan, ho_ten, sdt, nam_sinh, dia_chi, chuong_trinh, quoc_gia, ghi_chu FROM $table_name";

$whereConditions = [];
$params = [];
$types = '';

if ($dateFrom) {
    $whereConditions[] = "DATE(ngay_nhan) >= ?";
    $params[] = $dateFrom;
    $types .= 's';
}

if ($dateTo) {
    $whereConditions[] = "DATE(ngay_nhan) <= ?";
    $params[] = $dateTo;
    $types .= 's';
}

if (count($whereConditions) > 0) {
    $sql .= " WHERE " . implode(" AND ", $whereConditions);
}

$sql .= " ORDER BY id ASC";

// Prepare and execute query
$stmt = $conn->prepare($sql);

if (count($params) > 0) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    
    // Tạo tên file với date range nếu có
    $dateRangeSuffix = '';
    if ($dateFrom && $dateTo) {
        $dateRangeSuffix = '_' . $dateFrom . '_den_' . $dateTo;
    } elseif ($dateFrom) {
        $dateRangeSuffix = '_tu_' . $dateFrom;
    } elseif ($dateTo) {
        $dateRangeSuffix = '_den_' . $dateTo;
    }
    
    $file_name = 'danh_sach_dang_ky' . $dateRangeSuffix . '_' . date('Ymd_His') . '.csv';
    
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
    $message = "Không có dữ liệu để xuất.";
    if ($dateFrom || $dateTo) {
        $message = "Không có dữ liệu trong khoảng thời gian đã chọn.";
    }
    echo json_encode(array("message" => $message, "status" => true));
}

$stmt->close();
$conn->close();
?>
