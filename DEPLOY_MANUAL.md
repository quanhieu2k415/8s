# 🚀 Deploy Code Lên Windows Server (KHÔNG DÙNG GIT)

## ⚠️ LƯU Ý
Server không có Git → Deploy bằng cách **upload file** hoặc **copy trực tiếp**

---

## 📋 Phương Pháp 1: Upload qua FTP/SFTP (Khuyên dùng)

### Bước 1: Backup trên Server

```powershell
# Mở PowerShell trên server
# Vào thư mục web
cd C:\xampp\htdocs\web8s

# Backup toàn bộ
$timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
Copy-Item -Path . -Destination ..\backup_$timestamp -Recurse

# Backup database
Copy-Item backend_api\database.db ..\backup_database_$timestamp.db
```

### Bước 2: Chuẩn Bị Files Trên Máy Local

**Trên máy local (nơi bạn code):**

```powershell
# 1. Tạo thư mục deploy
cd C:\xampp\htdocs\web8s
New-Item -Path "..\deploy_package" -ItemType Directory -Force

# 2. Copy ONLY những file đã sửa (không copy git, uploads, database)
$filesToCopy = @(
    "admin\dashboard.php",
    "admin\includes\auth_check.php",
    "backend_api\content_blocks_api.php",
    "backend_api\delete.php",
    "backend_api\insert.php",
    "backend_api\save_content.php",
    "backend_api\update.php",
    "src\Core\Session.php",
    "src\Services\Auth.php"
)

foreach ($file in $filesToCopy) {
    $destPath = "..\deploy_package\$file"
    $destDir = Split-Path $destPath -Parent
    New-Item -Path $destDir -ItemType Directory -Force -ErrorAction SilentlyContinue
    Copy-Item $file $destPath -Force
    Write-Host "✅ Copied: $file"
}

Write-Host "`n📦 Package ready at: ..\deploy_package"
```

### Bước 3: Upload Lên Server

**Dùng FTP Client (FileZilla, WinSCP):**

1. Mở FileZilla hoặc WinSCP
2. Connect tới server
3. Navigate đến `C:\xampp\htdocs\web8s`
4. Upload từng file từ folder `deploy_package`:
   - `admin\dashboard.php` → upload vào `admin\`
   - `backend_api\save_content.php` → upload vào `backend_api\`
   - Etc.

**Hoặc dùng Remote Desktop:**

1. Connect Remote Desktop tới server
2. Copy folder `deploy_package` từ máy local
3. Paste vào server desktop
4. Copy từng file vào đúng vị trí

---

## 📋 Phương Pháp 2: Copy Trực Tiếp (Nếu Remote Desktop)

### Bước 1: Remote Desktop Vào Server

```
Win + R → mstsc → Nhập IP server → Connect
```

### Bước 2: Copy Files

**Cách 1: Map Network Drive**
```powershell
# Trên máy local, share folder web8s
# Rồi trên server:
net use Z: \\YOUR_LOCAL_IP\web8s

# Copy files
Copy-Item Z:\admin\dashboard.php C:\xampp\htdocs\web8s\admin\ -Force
Copy-Item Z:\backend_api\*.php C:\xampp\htdocs\web8s\backend_api\ -Force
# ... copy các file khác
```

**Cách 2: USB hoặc clipboard**
- Copy files vào USB
- Cắm USB vào server
- Copy vào đúng vị trí

---

## 📋 Phương Pháp 3: Tạo Package ZIP

### Trên Máy Local:

```powershell
cd C:\xampp\htdocs\web8s

# Tạo file danh sách files đã sửa
$changedFiles = @(
    "admin\dashboard.php",
    "admin\includes\auth_check.php",
    "backend_api\content_blocks_api.php",
    "backend_api\delete.php",
    "backend_api\insert.php",
    "backend_api\save_content.php",
    "backend_api\update.php",
    "src\Core\Session.php",
    "src\Services\Auth.php"
)

# Tạo ZIP package
$zipPath = "..\deploy_$(Get-Date -Format 'yyyyMMdd_HHmmss').zip"
Compress-Archive -Path $changedFiles -DestinationPath $zipPath -Force

Write-Host "📦 Package created: $zipPath"
```

### Trên Server:

```powershell
# 1. Upload file ZIP lên server (qua FTP, email, USB, v.v.)

# 2. Extract vào temp folder
Expand-Archive -Path "deploy_20251224_162400.zip" -DestinationPath "C:\temp\deploy" -Force

# 3. Backup
cd C:\xampp\htdocs\web8s
$timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
Copy-Item -Path . -Destination ..\backup_$timestamp -Recurse

# 4. Copy files từ temp vào web folder
Copy-Item C:\temp\deploy\* C:\xampp\htdocs\web8s\ -Recurse -Force

# 5. Dọn dẹp
Remove-Item C:\temp\deploy -Recurse -Force
```

---

## 📋 Phương Pháp 4: Script Tự Động Upload (PowerShell PSCP)

**Nếu server có SSH:**

### Cài đặt PSCP (từ PuTTY):

1. Download PuTTY: https://www.putty.org/
2. Lấy file `pscp.exe`
3. Thêm vào PATH hoặc copy vào folder web

### Script Upload Tự Động:

```powershell
# upload.ps1
$serverIP = "192.168.1.100"  # Thay IP server
$serverUser = "Administrator"
$serverPath = "C:/xampp/htdocs/web8s"

$filesToUpload = @(
    "admin/dashboard.php",
    "backend_api/save_content.php",
    # ... thêm các file khác
)

foreach ($file in $filesToUpload) {
    $remotePath = "$serverPath/$file"
    Write-Host "Uploading $file..."
    pscp.exe $file "${serverUser}@${serverIP}:${remotePath}"
}

Write-Host "✅ Upload completed!"
```

---

## 🔄 Sau Khi Upload - Restart Services

### Trên Server (PowerShell hoặc qua Remote Desktop):

```powershell
# Dừng Apache
Stop-Service Apache2.4

# Hoặc qua XAMPP Control Panel
# Click "Stop" button cho Apache

# Đợi 2-3 giây

# Start lại Apache
Start-Service Apache2.4

# Hoặc qua XAMPP Control Panel
# Click "Start" button
```

---

## 📝 Danh Sách Files Cần Upload (Latest Update)

**✅ Files đã sửa trong lần update này:**

```
admin/
  ├── dashboard.php                    ← Activity logs, users stats, settings
  └── includes/
      └── auth_check.php               ← Configurable session timeout

backend_api/
  ├── content_blocks_api.php           ← Activity logging
  ├── save_content.php                 ← Auth + activity logging
  ├── insert.php                       ← Auth + activity logging  
  ├── update.php                       ← Auth + activity logging
  └── delete.php                       ← Auth + activity logging

src/
  ├── Core/
  │   └── Session.php                  ← Session cookie fixes
  └── Services/
      └── Auth.php                     ← Session token fixes
```

**❌ Files KHÔNG cần upload:**

```
backend_api/database.db              ← ĐỪNG ghi đè database!
backend_api/uploads/*                ← ĐỪNG ghi đè uploads!
.git/*                              
node_modules/*
*.log
```

---

## ⚠️ QUAN TRỌNG: Kiểm Tra Sau Deploy

```powershell
# 1. Test website
# Mở browser: http://your-server-ip/web8s

# 2. Test admin login
# http://your-server-ip/web8s/admin

# 3. Check error logs
Get-Content C:\xampp\apache\logs\error.log -Tail 20

# 4. Check PHP logs
Get-Content C:\xampp\php\logs\php_error_log -Tail 20
```

---

## 🆘 Rollback Nếu Lỗi

```powershell
# 1. Stop Apache
Stop-Service Apache2.4

# 2. Xóa files lỗi
cd C:\xampp\htdocs\web8s
Remove-Item admin\dashboard.php -Force
# ... xóa các file khác

# 3. Khôi phục từ backup
$latestBackup = Get-ChildItem ..\backup_* | Sort-Object Name -Descending | Select-Object -First 1
Copy-Item "$latestBackup\admin\dashboard.php" admin\ -Force
# ... copy các file khác

# 4. Start Apache
Start-Service Apache2.4
```

---

## 🎯 Quick Deploy Checklist

**TRƯỚC KHI DEPLOY:**
- [ ] Đã backup code hiện tại trên server
- [ ] Đã backup database
- [ ] Đã test kỹ trên local
- [ ] Đã list đầy đủ files cần upload

**KHI DEPLOY:**
- [ ] Upload đúng vị trí file
- [ ] KHÔNG ghi đè database.db
- [ ] KHÔNG ghi đè uploads folder
- [ ] Restart Apache sau khi upload xong

**SAU KHI DEPLOY:**
- [ ] Test login admin
- [ ] Test các tính năng chính
- [ ] Check error logs
- [ ] Thông báo user nếu cần

---

## 💡 Tips

1. **Deploy từng file một** thay vì toàn bộ (an toàn hơn)
2. **Test sau mỗi file** để biết file nào gây lỗi
3. **Giữ backup ít nhất 7 ngày**
4. **Deploy vào giờ ít người dùng**
5. **Có Remote Desktop sẵn** để xử lý nhanh nếu lỗi

---

## 🔧 Cài Git Cho Server (Khuyến nghị)

Nếu có thể, cài Git cho server để deploy dễ dàng hơn:

1. Download Git for Windows: https://git-scm.com/download/win
2. Cài đặt (Next → Next → Install)
3. Mở PowerShell trên server:

```powershell
cd C:\xampp\htdocs\web8s
git init
git remote add origin https://github.com/quanhieu2k415/8s.git
git pull origin main
```

Từ lần sau deploy dễ như ăn kẹo: `git pull origin main`

---

**Chúc deploy thành công! 🚀**
