# ICOGroup Website

Website giáo dục và đào tạo nghề của ICOGroup - Du học & Xuất khẩu lao động.

## 📋 Yêu cầu hệ thống

- **XAMPP** (PHP 8.0+ & MySQL/MariaDB)
- Trình duyệt web hiện đại

## 🚀 Cài đặt nhanh

### Bước 1: Cài XAMPP
1. Tải XAMPP từ: https://www.apachefriends.org/
2. Cài đặt và chọn Apache + MySQL

### Bước 2: Copy code
```bash
# Copy thư mục web8s vào htdocs
Copy thư mục này vào: C:\xampp\htdocs\web8s\
```

### Bước 3: Tạo Database
1. Mở XAMPP Control Panel → Start **Apache** và **MySQL**
2. Vào phpMyAdmin: http://localhost/phpmyadmin
3. Tạo database mới tên: `icogroup_db`
4. Import file SQL:
   - Click vào database `icogroup_db`
   - Tab **Import** → Chọn file `backend_api/database/migration.sql`
   - Click **Import**

### Bước 4: Cấu hình .env
1. Copy file `.env.example` thành `.env`
2. Sửa thông tin database:
```env
DB_HOST=localhost
DB_NAME=icogroup_db
DB_USER=root
DB_PASS=
```

### Bước 5: Truy cập website
- **Frontend:** http://localhost/web8s/fonend/
- **Admin Panel:** http://localhost/web8s/admin/

## 👤 Tài khoản Admin mặc định
```
Username: admin
Password: cris123
```
> ⚠️ **Quan trọng:** Đổi mật khẩu ngay sau khi đăng nhập!

## 📁 Cấu trúc thư mục

```
web8s/
├── fonend/          # Frontend website
│   ├── index.php    # Trang chủ
│   ├── style.css    # CSS chính
│   └── script.js    # JavaScript
├── admin/           # Admin panel
│   ├── index.php    # Đăng nhập admin
│   └── dashboard.php # Bảng điều khiển
├── backend_api/     # API endpoints
│   ├── insert.php   # API đăng ký
│   ├── news_api.php # API tin tức
│   └── database/    # File SQL
├── src/             # Core PHP classes
└── storage/         # Logs, Cache, Uploads
```

## ✨ Tính năng

### Frontend
- 🏠 Trang chủ với slider
- 📚 Trang du học (Nhật, Đức, Hàn)
- 💼 Trang XKLĐ (Nhật, Hàn, Đài Loan, Châu Âu)
- 📝 Form đăng ký tư vấn
- 🔍 Tìm kiếm tin tức & chương trình
- 📱 Responsive mobile

### Admin Panel
- 📊 Dashboard thống kê
- 📋 Quản lý đăng ký tư vấn
- 📰 Quản lý tin tức
- 🖼️ Visual CMS chỉnh sửa nội dung
- 📈 Biểu đồ analytics

## 🔧 Xử lý lỗi thường gặp

### Lỗi kết nối database
```
Kiểm tra file .env đã đúng thông tin chưa
Đảm bảo MySQL đang chạy trong XAMPP
```

### Lỗi 404 Not Found
```
Kiểm tra đường dẫn htdocs có đúng không
Đảm bảo Apache đang chạy
```

### Session hết hạn (Admin)
```
Tự động logout sau 1 giờ không hoạt động
Đăng nhập lại để tiếp tục
```

## 📞 Hỗ trợ

- Website: https://icogroup.vn
- Hotline: 0822.314.555
- Email: info@icogroup.vn

---
© 2024 ICOGroup. All rights reserved.
