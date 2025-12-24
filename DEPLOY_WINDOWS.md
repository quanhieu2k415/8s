# 🚀 Hướng Dẫn Deploy Code Lên Windows Server

## ⚠️ LƯU Ý QUAN TRỌNG
**LUÔN BACKUP TRƯỚC KHI DEPLOY!**

---

## 📋 Các Bước Deploy Chuẩn (Windows Server)

### Bước 1: Mở PowerShell hoặc CMD với quyền Administrator

```powershell
# Click phải PowerShell → "Run as Administrator"
# Hoặc nhấn Win + X → chọn "Windows PowerShell (Admin)"
```

---

### Bước 2: Backup Code và Database Cũ

```powershell
# Vào thư mục web
cd C:\xampp\htdocs\web8s

# Backup toàn bộ code
$timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
Copy-Item -Path . -Destination ..\backup_$timestamp -Recurse

# Backup database SQLite
Copy-Item backend_api\database.db ..\backup_database_$timestamp.db

# Hoặc nếu dùng MySQL
# mysqldump -u root -p db_nhanluc > ..\backup_db_$timestamp.sql
```

---

### Bước 3: Kiểm Tra Git Status

```powershell
# Xem các file đã thay đổi trên server
git status

# Nếu có file đã sửa trên server (conflicts)
git stash  # Lưu tạm các thay đổi
```

---

### Bước 4: Pull Code Mới Từ GitHub

```powershell
# Pull code mới nhất
git pull origin main

# Nếu có conflicts
git status  # Xem file nào conflict

# Giải quyết conflicts:
# - Mở file conflict và sửa thủ công
# - Hoặc chọn lấy version GitHub:
git checkout --theirs <filename>
# - Hoặc giữ version server:
git checkout --ours <filename>
```

---

### Bước 5: Restart Apache/Services

```powershell
# Cách 1: Dùng XAMPP Control Panel
# - Mở XAMPP Control Panel
# - Click "Stop" Apache
# - Đợi 2-3 giây
# - Click "Start" Apache

# Cách 2: Restart qua PowerShell (nếu cài Apache Service)
Restart-Service -Name Apache2.4

# Cách 3: Restart MySQL (nếu cần)
Restart-Service -Name MySQL
```

---

### Bước 6: Clear PHP Opcache (Nếu Bật)

```powershell
# Tạo file clear_cache.php trong thư mục web
@"
<?php
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo 'Opcache cleared!';
} else {
    echo 'Opcache not enabled';
}
?>
"@ | Out-File -FilePath clear_cache.php -Encoding UTF8

# Truy cập http://localhost/web8s/clear_cache.php
# Sau đó xóa file này
Remove-Item clear_cache.php
```

---

### Bước 7: Test Trang Web

1. Mở trình duyệt
2. Vào `http://your-domain.com` hoặc `http://localhost/web8s`
3. Test các tính năng:
   - ✅ Đăng nhập admin
   - ✅ Activity Logs hiển thị OK
   - ✅ Settings thay đổi session timeout
   - ✅ Users section load stats
   - ✅ CMS save content thành công

---

## 🔥 Deploy Script Tự Động (PowerShell)

Tạo file `deploy.ps1`:

```powershell
# deploy.ps1 - Auto Deploy Script
Write-Host "🚀 Starting deployment..." -ForegroundColor Green

# 1. Backup
$timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
$backupPath = "..\backup_$timestamp"
Write-Host "📦 Creating backup at $backupPath" -ForegroundColor Yellow
Copy-Item -Path . -Destination $backupPath -Recurse -Exclude @('.git', 'node_modules')
Copy-Item backend_api\database.db ..\backup_database_$timestamp.db

# 2. Stash local changes
Write-Host "💾 Stashing local changes..." -ForegroundColor Yellow
git stash

# 3. Pull new code
Write-Host "⬇️ Pulling latest code..." -ForegroundColor Yellow
git pull origin main

# 4. Restart Apache
Write-Host "🔄 Restarting Apache..." -ForegroundColor Yellow
try {
    Restart-Service -Name Apache2.4 -ErrorAction Stop
    Write-Host "✅ Apache restarted" -ForegroundColor Green
} catch {
    Write-Host "⚠️ Could not restart Apache service. Please restart manually via XAMPP Control Panel." -ForegroundColor Red
}

# 5. Clear opcache
Write-Host "🧹 Clearing opcache..." -ForegroundColor Yellow
$clearCacheContent = @"
<?php
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo 'OK';
} else {
    echo 'Not enabled';
}
?>
"@
$clearCacheContent | Out-File -FilePath clear_cache.php -Encoding UTF8
try {
    $result = Invoke-WebRequest -Uri "http://localhost/web8s/clear_cache.php" -UseBasicParsing
    Write-Host "Cache status: $($result.Content)" -ForegroundColor Cyan
} catch {
    Write-Host "⚠️ Could not clear cache via web" -ForegroundColor Yellow
}
Remove-Item clear_cache.php -ErrorAction SilentlyContinue

Write-Host "`n✅ Deployment completed!" -ForegroundColor Green
Write-Host "📝 Backup saved to: $backupPath" -ForegroundColor Cyan
Write-Host "🌐 Please test your website now!" -ForegroundColor Yellow
```

**Chạy script:**
```powershell
# Vào thư mục web
cd C:\xampp\htdocs\web8s

# Chạy deploy script
.\deploy.ps1
```

---

## 🆘 Rollback Nếu Có Lỗi

```powershell
# 1. Vào thư mục web
cd C:\xampp\htdocs\web8s

# 2. Xóa code hiện tại (CẨN THẬN!)
Remove-Item * -Force -Recurse -Exclude .git

# 3. Khôi phục từ backup
$backupFolder = "..\backup_20251224_161900"  # Thay đổi timestamp
Copy-Item -Path "$backupFolder\*" -Destination . -Recurse -Force

# 4. Khôi phục database
Copy-Item ..\backup_database_20251224_161900.db backend_api\database.db -Force

# 5. Restart Apache qua XAMPP Control Panel
```

---

## 📝 Checklist Deploy

- [ ] Đã backup code cũ
- [ ] Đã backup database
- [ ] Đã chạy git pull
- [ ] Đã restart Apache
- [ ] Đã clear cache
- [ ] Đã test login admin
- [ ] Đã test các tính năng chính
- [ ] Website hoạt động bình thường

---

## 🔧 Troubleshooting (Windows)

### Lỗi: git pull bị từ chối (Permission denied)
```powershell
# Đóng XAMPP Control Panel
# Mở PowerShell as Administrator
cd C:\xampp\htdocs\web8s
git pull origin main
```

### Lỗi: Database file is locked
```powershell
# Stop Apache trước
# Via XAMPP Control Panel: Click "Stop" Apache
# Hoặc:
Stop-Service -Name Apache2.4

# Sau đó pull code
git pull origin main

# Start lại Apache
Start-Service -Name Apache2.4
```

### Lỗi: Cannot find Apache2.4 service
```powershell
# Apache chưa cài service, dùng XAMPP Control Panel
# Hoặc cài Apache as service:
# Mở CMD as Administrator
cd C:\xampp\apache\bin
httpd.exe -k install
```

### Lỗi: Session not working after deploy
```powershell
# Clear session folder
Remove-Item C:\xampp\tmp\sess_* -Force

# Restart Apache
# Via XAMPP Control Panel
```

### Check PHP Error Logs
```powershell
# Xem error log
Get-Content C:\xampp\apache\logs\error.log -Tail 50

# Hoặc PHP error log
Get-Content C:\xampp\php\logs\php_error_log -Tail 50
```

---

## 🎯 Best Practices cho Windows Server

1. **Tắt antivirus tạm thời** khi deploy (có thể block git)
2. **Đóng XAMPP Control Panel** trước khi pull
3. **Backup thường xuyên** - ít nhất mỗi tuần 1 lần
4. **Test trên local XAMPP trước** khi deploy lên server
5. **Deploy vào giờ thấp điểm** (đêm khuya)
6. **Monitor logs** sau deploy: `C:\xampp\apache\logs\error.log`

---

## 🌐 URLs Quan Trọng

- **Website:** `http://localhost/web8s` (local) hoặc `http://your-domain.com` (production)
- **Admin:** `http://localhost/web8s/admin`
- **phpMyAdmin:** `http://localhost/phpmyadmin`
- **Error Logs:** `C:\xampp\apache\logs\error.log`

---

## 📞 Quick Commands

```powershell
# Kiểm tra Apache status
Get-Service Apache2.4

# Restart Apache
Restart-Service Apache2.4

# Xem log realtime
Get-Content C:\xampp\apache\logs\error.log -Wait -Tail 20

# Check git status
git status

# View recent commits
git log --oneline -5
```

---

**Chúc deploy thành công trên Windows Server! 🚀**
