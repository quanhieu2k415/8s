# Hướng Dẫn Cài Đặt và Vận Hành Website ICOGroup

Tài liệu này hướng dẫn chi tiết cách cài đặt và chạy source code website 

## 📋 Yêu Cầu Hệ Thống

1.  **XAMPP**: Phiên bản hỗ trợ PHP 8.0 trở lên (Khuyến nghị 8.1 hoặc 8.2).
    *   Tải về tại: [apachefriends.org](https://www.apachefriends.org/download.html)
2.  **Trình duyệt web**: Chrome, Firefox, hoặc Edge mới nhất.
3.  **Git** (Tùy chọn): Để quản lý source code.

---

## 🚀 Các Bước Cài Đặt

### Bước 1: Chuẩn bị Source Code
1.  Tải source code hoặc clone từ git về máy.
2.  Copy thư mục dự án (ví dụ `web8s`) vào thư mục `htdocs` của XAMPP.
    *   Đường dẫn chuẩn: `C:\xampp\htdocs\web8s`

### Bước 2: Khởi động Server
1.  Mở **XAMPP Control Panel**.
2.  Nhấn **Start** ở cả 2 module: **Apache** và **MySQL**.
3.  Đảm bảo biểu tượng của chúng chuyển sang màu xanh lá.

### Bước 3: Cấu hình Database
1.  Truy cập **phpMyAdmin**: [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
2.  Tạo cơ sở dữ liệu mới:
    *   Tên database: `icogroup_db` (hoặc tên khác tùy bạn, nhưng cần khớp với file `.env`).
    *   Collation: `utf8mb4_unicode_ci` (để hỗ trợ tiếng Việt đầy đủ).
3.  **Cách 1: Cài đặt tự động (Khuyên dùng)**
    *   Truy cập đường dẫn: [http://localhost/web8s/install.php](http://localhost/web8s/install.php)
    *   Làm theo hướng dẫn trên màn hình để tạo các bảng chính và tài khoản admin mặc định.
    *   **Lưu ý:** Sau khi cài xong, bạn cần xóa file `install.php` vì lý do bảo mật.
4.  **Cách 2: Import thủ công (Nếu cách 1 lỗi hoặc cần dữ liệu đầy đủ)**
    *   Trong phpMyAdmin, chọn database `icogroup_db`.
    *   Chọn tab **Import** -> Chọn file từ thư mục `backend_api/database/`.
    *   Lần lượt import các file sau (nếu chưa có bảng):
        1.  `full_database_migration.sql` (Chứa toàn bộ cấu trúc và dữ liệu mẫu)

### Bước 4: Cấu hình file môi trường (.env)
1.  Trong thư mục `web8s`, tìm file `.env.example`.
2.  Copy file này và đổi tên thành `.env`.
3.  Mở file `.env` bằng trình soạn thảo code (Notepad, VS Code...) và chỉnh sửa thông tin kết nối database:

```env
DB_HOST=localhost
DB_NAME=icogroup_db  # Tên database bạn vừa tạo
DB_USER=root         # Mặc định của XAMPP là root
DB_PASS=             # Mặc định của XAMPP là để trống
```

### Bước 5: Kiểm tra Website
1.  **Trang chủ (Frontend):**
    *   Truy cập: [http://localhost/web8s/fonend/](http://localhost/web8s/fonend/)
    *   (Lưu ý: Thư mục frontend hiện là `fonend`, nếu bạn muốn đổi tên hãy sửa lại đường dẫn).
2.  **Trang quản trị (Admin Panel):**
    *   Truy cập: [http://localhost/web8s/admin/](http://localhost/web8s/admin/)

---

## 👤 Tài Khoản Quản Trị Mặc Định

Sử dụng tài khoản này để đăng nhập vào trang quản trị:

*   **Username:** `admin`
*   **Password:** `cris123`

> ⚠️ **Quan trọng:** Vui lòng đổi mật khẩu ngay sau khi đăng nhập thành công để bảo mật hệ thống.

---

## 📁 Cấu Trúc Thư Mục Chính

*   `admin/`: Mã nguồn trang quản trị (Dashboard, quản lý tin tức, user...).
*   `backend_api/`: Các API xử lý dữ liệu và file SQL database.
*   `fonend/`: Mã nguồn giao diện người dùng (Trang chủ, Tin tức...).
*   `src/`: Các lớp PHP cốt lõi (Core classes) tự động load.
*   `storage/`: Nơi lưu trữ file upload, logs, cache.
*   `.env`: File cấu hình hệ thống (Database, App Config).

## 🔧 Xử Lý Lỗi Thường Gặp

*   **Lỗi kết nối Database:** Kiểm tra lại tên database và password trong file `.env`.
*   **Lỗi 404 (Không tìm thấy trang):** Kiểm tra lại đường dẫn URL, đảm bảo folder trong `htdocs` tên đúng là `web8s`.
*   **Lỗi phân quyền (Permission denied):** Đảm bảo thư mục `storage` có quyền ghi (Write permission). Trên Windows XAMPP thường mặc định đã có quyền.
*   **Dữ liệu cũ/Không cập nhật:** Thử xóa cache trình duyệt (Ctrl + F5) hoặc xóa file trong `storage/cache` (nếu có).

---
© 2025 ICOGroup. Tài liệu hướng dẫn nội bộ.
