# ICOGroup Website

Website du học và xuất khẩu lao động ICOGroup được xây dựng bằng PHP.

## 📋 Yêu Cầu Hệ Thống

- **XAMPP** (hoặc WAMP/LAMP) với:
  - PHP >= 7.4
  - MySQL >= 5.7
  - Apache với mod_rewrite
- **Composer** (không bắt buộc nếu không dùng vendor)

## ⚠️ Vấn Đề Thường Gặp: Dữ Liệu Cũ Trên Máy Mới

### Nguyên Nhân

Khi clone/pull code từ Git sang máy mới, website có thể hiển thị **dữ liệu cũ** hoặc **không có dữ liệu** vì:

1. **File `.env` không được push lên Git** (bị ignore vì lý do bảo mật)
2. **Database MySQL không được đồng bộ** - mỗi máy có database riêng
3. **Chưa chạy script cài đặt** để tạo tables và seed dữ liệu

### Giải Pháp

Làm theo các bước trong phần **Cài Đặt Dự Án** bên dưới để thiết lập môi trường mới.

---

## 🚀 Cài Đặt Dự Án (Trên Máy Mới)

### Bước 1: Clone Repository

```bash
cd C:\xampp\htdocs
git clone <repository-url> web8s
cd web8s
```

### Bước 2: Tạo File Cấu Hình `.env`

```bash
# Copy file mẫu
copy .env.example .env
```

Sau đó mở file `.env` và chỉnh sửa các thông số:

```ini
# Database - Thay đổi theo cấu hình của bạn
DB_HOST=localhost
DB_NAME=db_nhanluc
DB_USER=root
DB_PASS=              # Thêm password nếu có

# Application
APP_URL=http://localhost/web8s
APP_SECRET=CHANGE_THIS_TO_A_RANDOM_32_CHARACTER_STRING
```

### Bước 3: Tạo Database MySQL

Mở phpMyAdmin hoặc MySQL CLI và tạo database:

```sql
CREATE DATABASE IF NOT EXISTS db_nhanluc 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;
```

### Bước 4: Chạy Script Cài Đặt

Truy cập trình duyệt:

```
http://localhost/web8s/install.php
```

Hoặc chạy qua command line:

```bash
php install.php
```

Script này sẽ:
- ✅ Tạo các tables cần thiết (admin_users, remember_tokens, audit_logs, v.v.)
- ✅ Tạo indexes cho các bảng
- ✅ Tạo tài khoản admin mặc định
- ✅ Tạo thư mục storage

### Bước 5: Seed Dữ Liệu CMS

```
http://localhost/web8s/seed_content.php
```

Hoặc seed đầy đủ nội dung:

```
http://localhost/web8s/seed_all_content.php
```

### Bước 6: Xóa File Cài Đặt (Quan Trọng!)

Sau khi cài đặt thành công, **XÓA các file sau** để bảo mật:

```bash
del install.php
del seed_content.php
del seed_all_content.php
```

---

## 🔐 Thông Tin Đăng Nhập Mặc Định

| Loại | Username | Password |
|------|----------|----------|
| Admin Panel | `admin` | `cris123` |

**⚠️ Đổi mật khẩu ngay sau khi đăng nhập!**

---

## 📁 Cấu Trúc Thư Mục

```
web8s/
├── admin/              # Admin Panel
│   ├── index.php       # Trang đăng nhập admin
│   ├── dashboard.php   # Dashboard chính
│   └── ...
├── app/                # Core Application
│   ├── Config/         # Cấu hình
│   ├── Core/           # Database, Router, v.v.
│   └── ...
├── backend_api/        # REST APIs
│   ├── get.php         # API lấy dữ liệu
│   ├── insert.php      # API thêm dữ liệu
│   ├── news_api.php    # API tin tức
│   └── ...
├── fonend/             # Frontend Public Pages
│   ├── index.php       # Trang chủ
│   ├── duhoc.php       # Trang du học
│   └── ...
├── src/                # Services & Repositories
├── storage/            # Logs, Cache, Uploads
├── .env                # Cấu hình môi trường (KHÔNG push lên Git)
├── .env.example        # Mẫu file cấu hình
├── install.php         # Script cài đặt
└── index.php           # Entry point
```

---

## 🔄 Đồng Bộ Dữ Liệu Giữa Các Máy

### Cách 1: Export/Import Database (Khuyến Nghị)

**Trên máy cũ (export):**
```bash
# Sử dụng phpMyAdmin hoặc:
mysqldump -u root -p db_nhanluc > backup.sql
```

**Trên máy mới (import):**
```bash
mysql -u root -p db_nhanluc < backup.sql
```

### Cách 2: Sử Dụng API Export

1. Đăng nhập Admin Panel
2. Vào trang **Registrations** hoặc **Contacts**
3. Click **Xuất CSV** để download dữ liệu
4. Import vào máy mới qua phpMyAdmin

---

## 🛣️ Các URL Quan Trọng

| Mục Đích | URL |
|----------|-----|
| Trang chủ | `http://localhost/web8s/` |
| Admin Panel | `http://localhost/web8s/admin/` |
| API Tin tức | `http://localhost/web8s/backend_api/news_api.php` |
| API Tìm kiếm | `http://localhost/web8s/backend_api/search_api.php` |

---

## 🐛 Xử Lý Lỗi Thường Gặp

### 1. Lỗi "Could not connect to database"

**Nguyên nhân:** File `.env` chưa được tạo hoặc cấu hình sai.

**Giải pháp:**
```bash
copy .env.example .env
# Chỉnh sửa DB_HOST, DB_NAME, DB_USER, DB_PASS
```

### 2. Lỗi "Table doesn't exist"

**Nguyên nhân:** Chưa chạy script cài đặt.

**Giải pháp:**
```
Truy cập: http://localhost/web8s/install.php
```

### 3. Trang trắng không có dữ liệu

**Nguyên nhân:** Chưa seed dữ liệu CMS.

**Giải pháp:**
```
Truy cập: http://localhost/web8s/seed_all_content.php
```

### 4. Lỗi 404 trên các trang con

**Nguyên nhân:** mod_rewrite chưa được bật hoặc thiếu file `.htaccess`.

**Giải pháp (XAMPP):**
1. Mở `C:\xampp\apache\conf\httpd.conf`
2. Tìm và uncomment: `LoadModule rewrite_module modules/mod_rewrite.so`
3. Restart Apache

### 5. Hình ảnh không hiển thị

**Nguyên nhân:** Thiếu thư mục uploads hoặc quyền ghi.

**Giải pháp:**
```bash
mkdir storage\uploads
# Đảm bảo thư mục có quyền ghi
```

---

## 📝 Checklist Cài Đặt Máy Mới

- [ ] Clone repository
- [ ] Copy `.env.example` thành `.env`
- [ ] Cấu hình database trong `.env`
- [ ] Tạo database MySQL
- [ ] Chạy `install.php`
- [ ] Chạy `seed_all_content.php`
- [ ] **XÓA** `install.php`, `seed_content.php`, `seed_all_content.php`
- [ ] Đổi mật khẩu admin mặc định
- [ ] Kiểm tra website hoạt động

---

## 📞 Liên Hệ Hỗ Trợ

Nếu gặp vấn đề, vui lòng liên hệ team phát triển hoặc tạo issue trên repository.

---

**Cập nhật lần cuối:** 2024-12-22
