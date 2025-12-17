<?php
// 1. Cấu hình Headers (quan trọng cho API)
header('Content-Type: application/json');
// Cho phép mọi tên miền (Frontend) truy cập (Cross-Origin Resource Sharing - CORS)
header('Access-Control-Allow-Origin: *'); 
// Cho phép các phương thức POST (để gửi dữ liệu) và các headers cần thiết
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

// 2. Thông tin kết nối Database
$servername = "localhost";
$username = "root";       // Tên người dùng mặc định của XAMPP
$password = "";           // Mật khẩu mặc định của XAMPP
$dbname = "db_nhanluc";   // Tên Database của bạn
$table_name = "user";     // Tên Bảng của bạn

// 3. Xử lý dữ liệu đầu vào (Input) từ Frontend
// Nhận dữ liệu JSON được gửi từ Frontend (Ví dụ: từ Fetch/Axios trong Javascript)
$data = json_decode(file_get_contents("php://input"), true);

// Kiểm tra nếu không có dữ liệu
if (empty($data)) {
    echo json_encode(array("message" => "Không có dữ liệu được gửi.", "status" => false));
    exit();
}

// 4. Lấy các trường dữ liệu
// *Lưu ý: Đảm bảo tên biến $data[...] khớp với tên cột trong bảng 'user' của bạn
$ho_ten = $data['ho_ten'];
$nam_sinh = $data['nam_sinh'];
$dia_chi = $data['dia_chi'];
$chuong_trinh = $data['chuong_trinh'];
$quoc_gia = $data['quoc_gia'];
$sdt = $data['sdt'];

// 5. Kết nối Database
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die(json_encode(array("message" => "Kết nối Database thất bại: " . $conn->connect_error, "status" => false)));
}

// 6. Chuẩn bị và Thực thi truy vấn INSERT
// Sử dụng Prepared Statements để tránh lỗi SQL Injection (Quan trọng về bảo mật)
$stmt = $conn->prepare("INSERT INTO $table_name (ho_ten, nam_sinh, dia_chi, chuong_trinh, quoc_gia, sdt) VALUES (?, ?, ?, ?, ?, ?)");

// Liên kết các biến với các tham số trong truy vấn
// "s" là string (chuỗi), "i" là integer (số nguyên), v.v. (Giả sử tất cả là chuỗi)
$stmt->bind_param("ssssss", $ho_ten, $nam_sinh, $dia_chi, $chuong_trinh, $quoc_gia, $sdt);

if ($stmt->execute()) {
    // 7. Trả về kết quả thành công
    echo json_encode(array("message" => "Thêm người dùng thành công.", "status" => true));
} else {
    // 7. Trả về kết quả thất bại
    echo json_encode(array("message" => "Thêm người dùng thất bại: " . $stmt->error, "status" => false));
}

// 8. Đóng kết nối
$stmt->close();
$conn->close();

?>