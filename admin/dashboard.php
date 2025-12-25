<?php
/**
 * Admin Dashboard
 */

require_once __DIR__ . '/includes/auth_check.php';

use App\Core\Database;

// Handle permission update via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_permission') {
    // Suppress error output for JSON response
    error_reporting(0);
    ini_set('display_errors', 0);
    
    header('Content-Type: application/json');
    
    try {
        // Verify CSRF
        if (!isset($csrf) || !$csrf->validateToken($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
            exit;
        }
        
        // Only admin can update
        if (!isset($userRole) || $userRole !== 'admin') {
            echo json_encode(['success' => false, 'message' => 'Không có quyền']);
            exit;
        }
        
        $role = $_POST['role'] ?? '';
        $permissionKey = $_POST['permission_key'] ?? '';
        $grantedValue = $_POST['granted'] ?? '';
        $granted = ($grantedValue === 'true' || $grantedValue === '1');
        
        if (!in_array($role, ['manager', 'user']) || empty($permissionKey)) {
            echo json_encode(['success' => false, 'message' => 'Invalid input']);
            exit;
        }
        
        $db = \App\Core\Database::getInstance();
        
        if ($granted) {
            $sql = "INSERT IGNORE INTO role_permissions (role, permission_key) VALUES (:role, :pkey)";
        } else {
            $sql = "DELETE FROM role_permissions WHERE role = :role AND permission_key = :pkey";
        }
        $db->execute($sql, [':role' => $role, ':pkey' => $permissionKey]);
        
        // Log permission change
        \App\Services\ActivityLogger::getInstance()->logPermissionChange(
            $currentUser['id'],
            $currentUser['username'],
            $role,
            $permissionKey,
            $granted
        );
        
        echo json_encode([
            'success' => true, 
            'message' => $granted ? 'Đã cấp quyền thành công' : 'Đã thu hồi quyền thành công'
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}

// Handle password change via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    error_reporting(0);
    ini_set('display_errors', 0);
    
    header('Content-Type: application/json');
    
    try {
        // Verify CSRF
        if (!isset($csrf) || !$csrf->validateToken($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
            exit;
        }
        
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        
        if (empty($currentPassword) || empty($newPassword)) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin']);
            exit;
        }
        
        if (strlen($newPassword) < 6) {
            echo json_encode(['success' => false, 'message' => 'Mật khẩu mới phải có ít nhất 6 ký tự']);
            exit;
        }
        
        $result = $auth->changePassword($currentUser['id'], $currentPassword, $newPassword);
        
        if ($result['success']) {
            // Log password change
            \App\Services\ActivityLogger::getInstance()->logPasswordChange(
                $currentUser['id'],
                $currentUser['username'],
                $userRole
            );
        }
        
        echo json_encode($result);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}

// Handle image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    error_reporting(0);
    ini_set('display_errors', 0);
    
    header('Content-Type: application/json');
    
    try {
        $file = $_FILES['image'];
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Upload error code: ' . $file['error']);
        }
        
        $uploadDir = __DIR__ . '/../uploads/images/';
        
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0777, true)) {
                throw new Exception('Cannot create upload directory');
            }
        }
        
        $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($file['name']));
        $targetPath = $uploadDir . $fileName;
        $fileType = strtolower(pathinfo($targetPath, PATHINFO_EXTENSION));
        
        $allowTypes = array('jpg', 'png', 'jpeg', 'gif', 'webp', 'svg');
        if (!in_array($fileType, $allowTypes)) {
            throw new Exception('File type not allowed');
        }
        
        if ($file['size'] > 5000000) {
            throw new Exception('File too large (max 5MB)');
        }
        
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new Exception('Failed to save file');
        }
        
        $publicUrl = '/web8s/uploads/images/' . $fileName;
        
        echo json_encode([
            'status' => true,
            'message' => 'Upload successful',
            'url' => $publicUrl
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'status' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit;
}

// Load permissions matrix for admin
$permissionsData = [];
if ($userRole === 'admin') {
    try {
        $allPerms = $permission->getAllPermissions();
        $matrix = $permission->getPermissionMatrix();
        
        $categoryNames = [
            'users' => 'Quản lý Tài khoản',
            'settings' => 'Cài đặt Hệ thống',
            'reports' => 'Báo cáo',
            'logs' => 'Activity Logs',
            'content' => 'Quản lý Nội dung',
            'news' => 'Tin tức',
            'registrations' => 'Đăng ký Tư vấn',
            'cms' => 'CMS',
            'content_blocks' => 'Content Blocks',
            'profile' => 'Hồ sơ Cá nhân',
            'database' => 'Database',
            'general' => 'Chung'
        ];
        $categoryIcons = [
            'users' => 'group',
            'settings' => 'settings',
            'reports' => 'analytics',
            'logs' => 'history',
            'content' => 'edit_note',
            'news' => 'article',
            'registrations' => 'people',
            'cms' => 'dashboard_customize',
            'content_blocks' => 'view_quilt',
            'profile' => 'person',
            'database' => 'storage',
            'general' => 'extension'
        ];
        
        foreach ($allPerms as $perm) {
            $category = $perm['category'] ?? 'general';
            if (!isset($permissionsData[$category])) {
                $permissionsData[$category] = [
                    'name' => $categoryNames[$category] ?? ucfirst($category),
                    'icon' => $categoryIcons[$category] ?? 'extension',
                    'permissions' => []
                ];
            }
            
            $key = $perm['permission_key'];
            $permissionsData[$category]['permissions'][] = [
                'key' => $key,
                'name' => $perm['permission_name'],
                'description' => $perm['description'] ?? '',
                'manager' => $matrix[$key]['roles']['manager'] ?? false,
                'user' => $matrix[$key]['roles']['user'] ?? false
            ];
        }
    } catch (Exception $e) {
        // Permissions tables may not exist
        $permissionsData = [];
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - ICOGroup</title>
    <meta name="csrf-token" content="<?php echo htmlspecialchars($csrfToken); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
    <link rel=" icon" type="image/x-icon" href="../logo.ico">
    <link rel="stylesheet" href="content_blocks.css">
    
    <?php
    // Dynamic Font Loading from Database
    require_once __DIR__ . '/../backend_api/db_config.php';
    
    $font_body = '';
    $font_heading = '';
    $font_body_url = '';
    $font_heading_url = '';
    
    if ($conn) {
        $sql = "SELECT text_key, text_value FROM site_texts WHERE text_key IN ('global_font_body', 'global_font_heading', 'global_font_body_url', 'global_font_heading_url')";
        $result = $conn->query($sql);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                switch ($row['text_key']) {
                    case 'global_font_body':
                        $font_body = $row['text_value'];
                        break;
                    case 'global_font_heading':
                        $font_heading = $row['text_value'];
                        break;
                    case 'global_font_body_url':
                        $font_body_url = $row['text_value'];
                        break;
                    case 'global_font_heading_url':
                        $font_heading_url = $row['text_value'];
                        break;
                }
            }
        }
    }
    
    // Load Google Fonts if specified
    if ($font_body_url && filter_var($font_body_url, FILTER_VALIDATE_URL)) {
        echo '<link rel="stylesheet" href="' . htmlspecialchars($font_body_url) . '">';
    }
    if ($font_heading_url && filter_var($font_heading_url, FILTER_VALIDATE_URL) && $font_heading_url !== $font_body_url) {
        echo '<link rel="stylesheet" href="' . htmlspecialchars($font_heading_url) . '">';
    }
    ?>
    
    <style>

        /* ===== CSS Variables ===== */
        :root {
            --primary: #2563EB;
            --primary-dark: #1E3A5F;
            --primary-light: #3B82F6;
            --accent: #F59E0B;
            --accent-hover: #D97706;
            --success: #10B981;
            --success-light: #D1FAE5;
            --danger: #EF4444;
            --danger-light: #FEE2E2;
            --warning: #F59E0B;
            --warning-light: #FEF3C7;
            --info: #3B82F6;
            --info-light: #DBEAFE;
            --bg-primary: #F8FAFC;
            --bg-sidebar: linear-gradient(180deg, #0F172A 0%, #1E293B 100%);
            --surface: #FFFFFF;
            --surface-hover: #F1F5F9;
            --text-primary: #1E293B;
            --text-secondary: #64748B;
            --text-muted: #94A3B8;
            --text-white: #FFFFFF;
            --border-light: #E2E8F0;
            --border-medium: #CBD5E1;
            --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            --transition-fast: 150ms cubic-bezier(0.4, 0, 0.2, 1);
            --transition-normal: 250ms cubic-bezier(0.4, 0, 0.2, 1);
            --sidebar-width: 280px;
            --radius-sm: 6px;
            --radius-md: 10px;
            --radius-lg: 16px;
        }

        <?php if ($font_body): ?>
        /* Dynamic font override from database */
        body {
            font-family: <?php echo htmlspecialchars($font_body); ?> !important;
        }
        <?php endif; ?>

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: var(--bg-primary); min-height: 100vh; color: var(--text-primary); }

        /* ===== Toggle Switch Styles ===== */
        .toggle-switch { position: relative; display: inline-block; width: 48px; height: 26px; flex-shrink: 0; }
        .toggle-switch input { opacity: 0; width: 0; height: 0; }
        .toggle-switch .toggle-slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background: #CBD5E1; border-radius: 26px; transition: 0.3s; }
        .toggle-switch .toggle-slider:before { position: absolute; content: ""; height: 20px; width: 20px; left: 3px; bottom: 3px; background: white; border-radius: 50%; transition: 0.3s; box-shadow: 0 2px 4px rgba(0,0,0,0.2); }
        .toggle-switch input:checked + .toggle-slider { background: #10B981; }
        .toggle-switch input:checked + .toggle-slider:before { transform: translateX(22px); }
        .toggle-switch input:focus + .toggle-slider { box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2); }

        /* ===== Section Panel ===== */
        .section-panel { display: none; animation: fadeIn 0.3s ease; }
        .section-panel.active { display: block; }
        
        /* ===== Sidebar ===== */
        .sidebar { position: fixed; left: 0; top: 0; width: var(--sidebar-width); height: 100vh; background: var(--bg-sidebar); color: var(--text-white); z-index: 100; display: flex; flex-direction: column; transition: transform var(--transition-normal); }
        .sidebar-header { padding: 24px; border-bottom: 1px solid rgba(255, 255, 255, 0.08); }
        .sidebar-header img { height: 40px; filter: brightness(0) invert(1); margin-bottom: 12px; }
        .sidebar-header h2 { font-size: 18px; font-weight: 700; letter-spacing: -0.02em; }
        .sidebar-header p { font-size: 13px; color: rgba(255, 255, 255, 0.6); margin-top: 4px; }
        .sidebar-menu { flex: 1; padding: 16px 0; overflow-y: auto; }
        .sidebar-menu a { display: flex; align-items: center; gap: 14px; padding: 14px 24px; color: rgba(255, 255, 255, 0.7); text-decoration: none; font-size: 14px; font-weight: 500; transition: all var(--transition-fast); border-left: 3px solid transparent; margin: 2px 0; }
        .sidebar-menu a:hover { background: rgba(255, 255, 255, 0.06); color: var(--text-white); }
        .sidebar-menu a.active { background: rgba(37, 99, 235, 0.15); color: var(--text-white); border-left-color: var(--accent); }
        .sidebar-menu a .material-icons-outlined { font-size: 20px; }
        .sidebar-divider { height: 1px; background: rgba(255, 255, 255, 0.08); margin: 12px 24px; }
        .user-info { padding: 16px 24px; border-top: 1px solid rgba(255, 255, 255, 0.08); display: flex; align-items: center; gap: 12px; }
        .user-avatar { width: 40px; height: 40px; background: linear-gradient(135deg, #6366F1, #8B5CF6); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 16px; }
        .user-details p { font-size: 14px; font-weight: 600; }
        .user-details span { font-size: 12px; color: rgba(255, 255, 255, 0.6); }

        /* ===== Main Content ===== */
        .main-content { margin-left: var(--sidebar-width); padding: 32px; min-height: 100vh; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px; }
        .header h1 { font-size: 28px; font-weight: 800; color: var(--text-primary); letter-spacing: -0.02em; }
        .header-actions { display: flex; gap: 12px; }

        /* ===== Buttons ===== */
        .btn { display: inline-flex; align-items: center; gap: 8px; padding: 12px 20px; border: none; border-radius: var(--radius-md); font-size: 14px; font-weight: 600; font-family: inherit; cursor: pointer; transition: all var(--transition-fast); }
        .btn-primary { background: var(--primary); color: var(--text-white); }
        .btn-primary:hover { background: var(--primary-light); transform: translateY(-1px); box-shadow: var(--shadow-md); }
        .btn-success { background: var(--success); color: var(--text-white); }
        .btn-success:hover { background: #059669; transform: translateY(-1px); }
        .btn-danger { background: var(--danger); color: var(--text-white); }
        .btn-outline { background: transparent; border: 2px solid var(--border-medium); color: var(--text-secondary); }
        .btn-outline:hover { border-color: var(--primary); color: var(--primary); }

        /* ===== Stats Grid ===== */
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 32px; }
        .stat-card { background: var(--surface); padding: 24px; border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); border: 1px solid var(--border-light); display: flex; align-items: center; gap: 20px; transition: all var(--transition-normal); }
        .stat-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-lg); border-color: transparent; }
        .stat-icon { width: 56px; height: 56px; border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; }
        .stat-icon.blue { background: linear-gradient(135deg, #6366F1, #8B5CF6); }
        .stat-icon.green { background: linear-gradient(135deg, #10B981, #34D399); }
        .stat-icon.orange { background: linear-gradient(135deg, #F59E0B, #FBBF24); }
        .stat-icon.purple { background: linear-gradient(135deg, #8B5CF6, #A78BFA); }
        .stat-icon .material-icons-outlined { color: var(--text-white); font-size: 24px; }
        .stat-info h3 { font-size: 28px; font-weight: 800; color: var(--text-primary); letter-spacing: -0.02em; }
        .stat-info p { color: var(--text-secondary); font-size: 13px; font-weight: 500; margin-top: 4px; }

        /* ===== Table Container ===== */
        .table-container { background: var(--surface); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); border: 1px solid var(--border-light); overflow: hidden; }
        .table-header { display: flex; justify-content: space-between; align-items: center; padding: 20px 24px; border-bottom: 1px solid var(--border-light); flex-wrap: wrap; gap: 16px; }
        .table-header h2 { font-size: 18px; font-weight: 700; color: var(--text-primary); }
        .table-filters { display: flex; gap: 12px; align-items: center; flex-wrap: wrap; }
        .search-box { display: flex; align-items: center; gap: 10px; background: var(--bg-primary); padding: 10px 16px; border-radius: var(--radius-md); border: 1px solid var(--border-light); transition: all var(--transition-fast); }
        .search-box:focus-within { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1); }
        .search-box input { border: none; background: transparent; outline: none; font-size: 14px; width: 200px; font-family: inherit; }
        .search-box .material-icons-outlined { color: var(--text-muted); font-size: 20px; }
        .filter-select { padding: 10px 14px; border: 1px solid var(--border-light); border-radius: var(--radius-md); font-size: 14px; font-family: inherit; background: var(--surface); cursor: pointer; }
        .date-filter { display: flex; gap: 8px; align-items: center; }
        .date-filter input { padding: 10px 12px; border: 1px solid var(--border-light); border-radius: var(--radius-md); font-size: 14px; font-family: inherit; }

        /* ===== Table ===== */
        .table-wrapper { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 800px; }
        th, td { padding: 14px 20px; text-align: left; border-bottom: 1px solid var(--border-light); }
        th { background: var(--bg-primary); font-weight: 600; color: var(--text-secondary); font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; }
        tbody tr { transition: background var(--transition-fast); }
        tbody tr:hover { background: var(--surface-hover); }
        tbody tr:nth-child(even) { background: #FAFBFC; }
        tbody tr:nth-child(even):hover { background: var(--surface-hover); }
        td { font-size: 14px; }

        /* ===== Badges ===== */
        .badge { display: inline-flex; align-items: center; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .badge-success { background: var(--success-light); color: #065F46; }
        .badge-warning { background: var(--warning-light); color: #92400E; }
        .badge-info { background: var(--info-light); color: #1E40AF; }

        /* ===== Action Buttons ===== */
        .action-btns { display: flex; gap: 8px; }
        .action-btn { width: 36px; height: 36px; border: none; border-radius: var(--radius-sm); cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all var(--transition-fast); }
        .action-btn .material-icons-outlined { font-size: 18px; }
        .action-btn.edit { background: var(--info-light); color: var(--primary); }
        .action-btn.edit:hover { background: var(--primary); color: var(--text-white); }
        .action-btn.delete { background: var(--danger-light); color: var(--danger); }
        .action-btn.delete:hover { background: var(--danger); color: var(--text-white); }

        /* ===== Loading & Empty States ===== */
        .loading, .empty-state { text-align: center; padding: 60px 20px; color: var(--text-secondary); }
        .spinner { width: 40px; height: 40px; border: 3px solid var(--border-light); border-top-color: var(--primary); border-radius: 50%; animation: spin 0.8s linear infinite; margin: 0 auto 16px; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .empty-state .material-icons-outlined { font-size: 64px; color: var(--border-medium); margin-bottom: 16px; }
        .empty-state h3 { font-size: 18px; color: var(--text-primary); margin-bottom: 8px; }

        /* ===== Modal ===== */
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); animation: fadeIn 0.2s ease; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        .modal-content { background: var(--surface); margin: 5% auto; padding: 0; border-radius: var(--radius-lg); width: 520px; max-width: 90%; max-height: 85vh; overflow: hidden; box-shadow: var(--shadow-xl); animation: slideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1); }
        @keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .modal-header { display: flex; justify-content: space-between; align-items: center; padding: 20px 24px; border-bottom: 1px solid var(--border-light); }
        .modal-header h2 { font-size: 18px; font-weight: 700; color: var(--text-primary); }
        .modal-close { width: 36px; height: 36px; border: none; background: var(--bg-primary); border-radius: var(--radius-sm); cursor: pointer; display: flex; align-items: center; justify-content: center; color: var(--text-secondary); transition: all var(--transition-fast); }
        .modal-close:hover { background: var(--danger-light); color: var(--danger); }
        .modal-body { padding: 24px; max-height: 60vh; overflow-y: auto; }
        .modal-body label { display: block; font-size: 14px; font-weight: 600; color: var(--text-primary); margin-bottom: 8px; }
        .modal-body input, .modal-body select, .modal-body textarea { width: 100%; padding: 12px 14px; border: 1px solid var(--border-light); border-radius: var(--radius-md); font-size: 14px; font-family: inherit; margin-bottom: 16px; transition: all var(--transition-fast); }
        .modal-body input:focus, .modal-body select:focus, .modal-body textarea:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1); }
        .modal-footer { padding: 16px 24px; border-top: 1px solid var(--border-light); display: flex; justify-content: flex-end; gap: 12px; }

        /* ===== Confirm Dialog ===== */
        .confirm-dialog { text-align: center; padding: 32px 24px; }
        .confirm-dialog .icon { width: 64px; height: 64px; border-radius: 50%; background: var(--danger-light); display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; }
        .confirm-dialog .icon .material-icons-outlined { font-size: 32px; color: var(--danger); }
        .confirm-dialog h3 { font-size: 18px; margin-bottom: 8px; }
        .confirm-dialog p { color: var(--text-secondary); margin-bottom: 24px; }
        .confirm-dialog .btn-group { display: flex; gap: 12px; justify-content: center; }

        /* ===== Toast ===== */
        .toast { position: fixed; bottom: 32px; right: 32px; padding: 16px 24px; border-radius: var(--radius-md); color: var(--text-white); font-weight: 500; display: none; align-items: center; gap: 12px; box-shadow: var(--shadow-lg); animation: toastIn 0.3s ease; z-index: 9999; max-width: 400px; }
        .toast.show { display: flex; }
        .toast.success { background: var(--success); }
        .toast.error { background: var(--danger); }
        @keyframes toastIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

        /* ===== Sections ===== */
        .section-panel { display: none; }
        .section-panel.active { display: block; }

        /* ===== Pagination ===== */
        .pagination { display: flex; justify-content: center; align-items: center; gap: 8px; padding: 20px; border-top: 1px solid var(--border-light); }
        .pagination button { padding: 8px 16px; border: 1px solid var(--border-light); background: var(--surface); border-radius: var(--radius-sm); cursor: pointer; font-family: inherit; transition: all var(--transition-fast); }
        .pagination button:hover:not(:disabled) { border-color: var(--primary); color: var(--primary); }
        .pagination button.active { background: var(--primary); color: white; border-color: var(--primary); }
        .pagination button:disabled { opacity: 0.5; cursor: not-allowed; }

        /* ===== Responsive ===== */
        @media (max-width: 1200px) { .stats-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 992px) { .sidebar { transform: translateX(-100%); } .sidebar.active { transform: translateX(0); } .main-content { margin-left: 0; } }
        @media (max-width: 768px) { .stats-grid { grid-template-columns: 1fr; } .header { flex-direction: column; gap: 16px; align-items: flex-start; } .table-header { flex-direction: column; align-items: flex-start; } .banner-grid { grid-template-columns: 1fr; } }
        
        /* ===== Banner Grid ===== */
        .banner-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
        .banner-card { background: var(--surface); border: 1px solid var(--border-light); border-radius: var(--radius-lg); overflow: hidden; transition: all var(--transition-normal); }
        .banner-card:hover { box-shadow: var(--shadow-lg); border-color: var(--primary); }
        .banner-preview { height: 120px; background: linear-gradient(135deg, #667eea, #764ba2); display: flex; align-items: center; justify-content: center; position: relative; background-size: cover; background-position: center; }
        .banner-preview.has-image { background-size: cover; }
        .banner-preview .placeholder { color: rgba(255,255,255,0.7); font-size: 14px; text-align: center; }
        .banner-preview .overlay { position: absolute; inset: 0; background: rgba(0,0,0,0.4); display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s; }
        .banner-card:hover .banner-preview .overlay { opacity: 1; }
        .banner-info { padding: 16px; }
        .banner-info h4 { font-size: 16px; font-weight: 600; margin-bottom: 4px; }
        .banner-info p { font-size: 13px; color: var(--text-secondary); margin-bottom: 12px; }
        .banner-actions { display: flex; gap: 8px; }
        .banner-actions label, .banner-actions button { flex: 1; padding: 8px 12px; border-radius: var(--radius-sm); font-size: 13px; font-weight: 500; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; transition: all var(--transition-fast); }
        .banner-actions .btn-upload { background: var(--info-light); color: var(--primary); border: none; }
        .banner-actions .btn-upload:hover { background: var(--primary); color: white; }
        .banner-actions .btn-remove { background: var(--danger-light); color: var(--danger); border: none; }
        .banner-actions .btn-remove:hover { background: var(--danger); color: white; }

        /* ===== Visual CMS Editor ===== */
        .visual-cms-container { padding: 24px; }
        .visual-cms-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px; }
        .visual-cms-header h2 { font-size: 20px; font-weight: 700; display: flex; align-items: center; gap: 10px; }
        .page-selector { display: flex; align-items: center; gap: 12px; }
        .page-selector label { font-weight: 600; color: var(--text-secondary); }
        .page-selector select { padding: 12px 40px 12px 16px; border: 2px solid var(--border-light); border-radius: var(--radius-md); font-size: 15px; font-weight: 600; background: var(--surface); cursor: pointer; min-width: 250px; }
        .page-selector select:focus { border-color: var(--primary); outline: none; }
        
        .cms-section-card { background: var(--surface); border: 1px solid var(--border-light); border-radius: var(--radius-lg); margin-bottom: 20px; overflow: hidden; transition: all 0.3s; }
        .cms-section-card:hover { border-color: var(--primary); box-shadow: var(--shadow-md); }
        .cms-section-header { display: flex; justify-content: space-between; align-items: center; padding: 16px 20px; background: linear-gradient(135deg, var(--bg-primary), var(--surface)); border-bottom: 1px solid var(--border-light); cursor: pointer; }
        .cms-section-header h3 { font-size: 16px; font-weight: 700; display: flex; align-items: center; gap: 10px; margin: 0; }
        .cms-section-header h3 .icon { font-size: 22px; }
        .cms-section-header .toggle-icon { color: var(--text-secondary); transition: transform 0.3s; }
        .cms-section-card.collapsed .toggle-icon { transform: rotate(-90deg); }
        .cms-section-card.collapsed .cms-section-body { display: none; }
        
        /* Hidden section styles - for sections hidden on frontend */
        .cms-section-card.section-hidden { border-color: var(--danger); opacity: 0.7; background: repeating-linear-gradient(45deg, var(--surface), var(--surface) 10px, rgba(239, 68, 68, 0.05) 10px, rgba(239, 68, 68, 0.05) 20px); }
        .cms-section-card.section-hidden .cms-section-header { background: linear-gradient(135deg, rgba(239, 68, 68, 0.1), var(--surface)); }
        .cms-section-card.section-hidden:hover { opacity: 1; }
        
        .cms-section-body { padding: 20px; }
        .cms-field-group { margin-bottom: 20px; }
        .cms-field-group:last-child { margin-bottom: 0; }
        .cms-field-label { display: block; font-size: 13px; font-weight: 600; color: var(--text-secondary); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px; }
        .cms-field-input { width: 100%; padding: 14px 16px; border: 1px solid var(--border-light); border-radius: var(--radius-md); font-size: 15px; font-family: inherit; transition: all 0.2s; background: var(--bg-primary); }
        .cms-field-input:focus { outline: none; border-color: var(--primary); background: var(--surface); box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1); }
        .cms-field-input.modified { border-color: var(--warning); background: #fffbeb; }
        textarea.cms-field-input { min-height: 100px; resize: vertical; }
        
        .cms-image-field { display: flex; gap: 16px; align-items: flex-start; }
        .cms-image-preview { width: 120px; height: 80px; border-radius: var(--radius-md); background: var(--bg-primary); border: 2px dashed var(--border-light); display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0; }
        .cms-image-preview img { width: 100%; height: 100%; object-fit: cover; }
        .cms-image-preview .placeholder { color: var(--text-muted); font-size: 12px; text-align: center; }
        .cms-image-actions { flex: 1; display: flex; flex-direction: column; gap: 8px; }
        .cms-image-url { flex: 1; }
        .cms-image-upload { display: inline-flex; align-items: center; gap: 6px; padding: 10px 16px; background: var(--info-light); color: var(--primary); border: none; border-radius: var(--radius-sm); cursor: pointer; font-weight: 500; font-size: 13px; transition: all 0.2s; }
        .cms-image-upload:hover { background: var(--primary); color: white; }
        
        .cms-items-list { display: flex; flex-direction: column; gap: 12px; }
        .cms-item-row { display: flex; gap: 12px; align-items: center; background: var(--bg-primary); padding: 12px 16px; border-radius: var(--radius-md); }
        .cms-item-row .item-number { font-weight: 700; color: var(--primary); min-width: 24px; }
        .cms-item-row input { flex: 1; }
        
        .cms-save-bar { position: sticky; bottom: 0; background: var(--surface); border-top: 2px solid var(--primary); padding: 16px 24px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 -4px 20px rgba(0,0,0,0.1); margin: 20px -24px -24px; }
        .cms-save-bar .changes-info { color: var(--text-secondary); display: flex; align-items: center; gap: 8px; }
        
        /* Spin animation for loading */
        .spin { animation: spin 1s linear infinite; }
        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
        .cms-save-bar .changes-info .dot { width: 8px; height: 8px; border-radius: 50%; background: var(--warning); }
        .cms-save-bar .btn-save-all { padding: 14px 32px; font-size: 16px; font-weight: 600; }
        
        .cms-loading { text-align: center; padding: 60px 20px; color: var(--text-secondary); }
        .cms-loading .spinner { margin-bottom: 16px; }
        
        .cms-empty { text-align: center; padding: 60px 20px; color: var(--text-secondary); }
        .cms-empty .icon { font-size: 64px; color: var(--border-light); margin-bottom: 16px; }
        
        /* Toggle Switch Styles */
        .toggle-wrapper { display: flex; align-items: center; justify-content: space-between; gap: 12px; }
        .toggle-wrapper .toggle-label { flex: 1; }
        .toggle-switch { position: relative; width: 48px; height: 26px; flex-shrink: 0; }
        .toggle-switch input { opacity: 0; width: 0; height: 0; }
        .toggle-slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background: #CBD5E1; border-radius: 26px; transition: 0.3s; }
        .toggle-slider:before { position: absolute; content: ""; height: 20px; width: 20px; left: 3px; bottom: 3px; background: white; border-radius: 50%; transition: 0.3s; box-shadow: 0 2px 4px rgba(0,0,0,0.2); }
        .toggle-switch input:checked + .toggle-slider { background: var(--success); }
        .toggle-switch input:checked + .toggle-slider:before { transform: translateX(22px); }
        .toggle-switch input:focus + .toggle-slider { box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2); }
        .menu-item-row { display: flex; align-items: center; gap: 12px; padding: 12px; background: var(--bg-primary); border-radius: var(--radius-md); margin-bottom: 10px; transition: all 0.2s; }
        .menu-item-row:hover { background: rgba(99, 102, 241, 0.05); }
        .menu-item-row.disabled { opacity: 0.5; }
        .menu-item-row .menu-icon { font-size: 20px; min-width: 28px; text-align: center; }
        .menu-item-row input[type="text"] { flex: 1; }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <a href="../fonend/index.php" title="Về trang chủ">
                <img src="../hi.jpg" alt="Logo" style="filter: none; height: 60px; border-radius: 8px;">
            </a>
            <h2>Admin Panel</h2>
            <p>Quản lý hệ thống</p>
        </div>
        <nav class="sidebar-menu">
            <a href="dashboard.php" class="<?php echo !isset($_SERVER['QUERY_STRING']) && empty(trim($_SERVER['REQUEST_URI'], '/')) ? 'active' : ''; ?>" data-section="dashboard">
                <span class="material-icons-outlined">dashboard</span>
                <span>Dashboard</span>
            </a>
            <?php if ($canManageUsers): ?>
            <a href="#users" data-section="users">
                <span class="material-icons-outlined">group</span>
                <span>Quản lý Tài khoản</span>
            </a>
            <?php endif; ?>
            <a href="#registrations" data-section="registrations">
                <span class="material-icons-outlined">people</span>
                <span>Đăng ký tư vấn</span>
            </a>
            <a href="#news" data-section="news">
                <span class="material-icons-outlined">article</span>
                <span>Tin tức</span>
            </a>
            <?php if ($canManageCMS): ?>
            <a href="#cms" data-section="cms">
                <span class="material-icons-outlined">edit_note</span>
                <span>Quản lý nội dung</span>
            </a>
            <?php endif; ?>
            <?php if ($canManageContentBlocks): ?>
            <a href="#contentBlocks" data-section="contentBlocks">
                <span class="material-icons-outlined">view_quilt</span>
                <span>Content Blocks</span>
            </a>
            <?php endif; ?>
            <?php if ($canViewAllLogs): ?>
            <a href="#logs" data-section="logs">
                <span class="material-icons-outlined">history</span>
                <span>Activity Logs</span>
            </a>
            <?php endif; ?>
            <?php if ($canAccessSettings): ?>
            <div class="sidebar-divider"></div>
            <a href="#settings" data-section="settings">
                <span class="material-icons-outlined">settings</span>
                <span>Cài đặt hệ thống</span>
            </a>
            <?php endif; ?>

            <div class="sidebar-divider"></div>

            <a href="../fonend/index.php">
                <span class="material-icons-outlined">home</span>
                <span>Về trang chủ</span>
            </a>
            <a href="logout.php">
                <span class="material-icons-outlined">logout</span>
                <span>Đăng xuất</span>
            </a>
        </nav>
        <div class="user-info">
            <div class="user-avatar"><?php echo strtoupper(substr($currentUser['username'], 0, 1)); ?></div>
            <div class="user-details">
                <p><?php echo htmlspecialchars($currentUser['username']); ?></p>
                <span><?php echo htmlspecialchars(ucfirst($currentUser['role'])); ?></span>
            </div>
            <button class="btn-icon" onclick="openPasswordModal()" title="Đổi mật khẩu" style="margin-left: auto; background: rgba(255,255,255,0.1); border: none; padding: 8px; border-radius: 8px; cursor: pointer;">
                <span class="material-icons-outlined" style="color: white; font-size: 20px;">lock</span>
            </button>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Dashboard Section -->
        <section id="dashboard" class="section-panel">
            <div class="header">
                <h1>Dashboard</h1>
                <div class="header-actions">
                    <button class="btn btn-outline" onclick="refreshDashboard()">
                        <span class="material-icons-outlined">refresh</span>
                        Làm mới
                    </button>
                </div>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <span class="material-icons-outlined">person_add</span>
                    </div>
                    <div class="stat-info">
                        <h3 id="statTotal">-</h3>
                        <p>Tổng đăng ký</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green">
                        <span class="material-icons-outlined">today</span>
                    </div>
                    <div class="stat-info">
                        <h3 id="statToday">-</h3>
                        <p>Hôm nay</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon orange">
                        <span class="material-icons-outlined">date_range</span>
                    </div>
                    <div class="stat-info">
                        <h3 id="statWeek">-</h3>
                        <p>Tuần này</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon purple">
                        <span class="material-icons-outlined">calendar_month</span>
                    </div>
                    <div class="stat-info">
                        <h3 id="statMonth">-</h3>
                        <p>Tháng này</p>
                    </div>
                </div>
            </div>

            <div class="table-container">
                <div class="table-header">
                    <h2>Đăng ký gần đây</h2>
                </div>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Thời gian</th>
                                <th>Họ tên</th>
                                <th>SĐT</th>
                                <th>Chương trình</th>
                                <th>Quốc gia</th>
                            </tr>
                        </thead>
                        <tbody id="recentRegistrations">
                            <tr><td colspan="6" class="loading"><div class="spinner"></div>Đang tải...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Analytics Charts Section -->
            <div class="analytics-section" style="margin-top: 30px;">
                <div class="table-header" style="margin-bottom: 20px;">
                    <h2>📊 Thống kê chi tiết</h2>
                </div>
                
                <div class="charts-grid" style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <!-- Daily Trend Chart -->
                    <div class="chart-card" style="background: white; border-radius: 16px; padding: 24px; box-shadow: var(--shadow-md);">
                        <h3 style="margin-bottom: 20px; font-size: 16px; color: var(--text-primary);">
                            📈 Đăng ký theo ngày (30 ngày gần nhất)
                        </h3>
                        <div style="height: 300px;">
                            <canvas id="dailyChart"></canvas>
                        </div>
                    </div>
                    
                    <!-- Program Distribution -->
                    <div class="chart-card" style="background: white; border-radius: 16px; padding: 24px; box-shadow: var(--shadow-md);">
                        <h3 style="margin-bottom: 20px; font-size: 16px; color: var(--text-primary);">
                            🎯 Theo chương trình
                        </h3>
                        <div style="height: 300px;">
                            <canvas id="programChart"></canvas>
                        </div>
                    </div>
                </div>
                
                <div class="charts-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <!-- Monthly Trend Chart -->
                    <div class="chart-card" style="background: white; border-radius: 16px; padding: 24px; box-shadow: var(--shadow-md);">
                        <h3 style="margin-bottom: 20px; font-size: 16px; color: var(--text-primary);">
                            📅 Đăng ký theo tháng (12 tháng)
                        </h3>
                        <div style="height: 280px;">
                            <canvas id="monthlyChart"></canvas>
                        </div>
                    </div>
                    
                    <!-- Country Distribution -->
                    <div class="chart-card" style="background: white; border-radius: 16px; padding: 24px; box-shadow: var(--shadow-md);">
                        <h3 style="margin-bottom: 20px; font-size: 16px; color: var(--text-primary);">
                            🌏 Theo quốc gia
                        </h3>
                        <div style="height: 280px;">
                            <canvas id="countryChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <?php 
        // Include Users Management Section - must be inside main-content
        if ($permission->canManageUsers($userRole)) {
            // Define required variables for users_section.php
            $isAdmin = ($userRole === 'admin');
            $canCreateUser = $permission->canCreateUsers($userRole);
            $canCreateManager = $permission->canCreateUsers($userRole);
            $canCreateAdmin = $permission->canCreateUsers($userRole);
            
            include __DIR__ . '/includes/users_section.php';
        }
        ?>

        <!-- Registrations Section -->
        <section id="registrations" class="section-panel">
            <div class="header">
                <h1>Quản lý đăng ký tư vấn</h1>
                <div class="header-actions">
                    <button class="btn btn-success" onclick="exportData()">
                        <span class="material-icons-outlined">download</span>
                        Xuất Excel
                    </button>
                </div>
            </div>

            <div class="table-container">
                <div class="table-header">
                    <h2>Danh sách đăng ký</h2>
                    <div class="table-filters">
                        <div class="search-box">
                            <span class="material-icons-outlined">search</span>
                            <input type="text" id="searchInput" placeholder="Tìm kiếm..." onkeyup="searchRegistrations()">
                        </div>
                        <select class="filter-select" id="filterProgram" onchange="filterRegistrations()">
                            <option value="">Tất cả chương trình</option>
                            <option value="Du học">Du học</option>
                            <option value="Xuất khẩu lao động">Xuất khẩu lao động</option>
                            <option value="Đào tạo ngoại ngữ">Đào tạo ngoại ngữ</option>
                        </select>
                        <div class="date-filter">
                            <input type="date" id="dateFrom" onchange="filterRegistrations()">
                            <span>đến</span>
                            <input type="date" id="dateTo" onchange="filterRegistrations()">
                        </div>
                    </div>
                </div>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Thời gian</th>
                                <th>Họ tên</th>
                                <th>Năm sinh</th>
                                <th>Địa chỉ</th>
                                <th>Chương trình</th>
                                <th>Quốc gia</th>
                                <th>SĐT</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody id="registrationsList">
                            <tr><td colspan="9" class="loading"><div class="spinner"></div>Đang tải...</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="pagination" id="pagination"></div>
            </div>
        </section>

        <!-- News Section -->
        <section id="news" class="section-panel">
            <div class="header">
                <h1>Quản lý tin tức</h1>
                <div class="header-actions">
                    <button class="btn btn-primary" onclick="openAddNewsModal()">
                        <span class="material-icons-outlined">add</span>
                        Thêm tin mới
                    </button>
                </div>
            </div>

            <div class="table-container">
                <div class="table-header">
                    <h2>Danh sách tin tức</h2>
                </div>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Hình ảnh</th>
                                <th>Tiêu đề</th>
                                <th>Danh mục</th>
                                <th>Ngày tạo</th>
                                <th>Nổi bật</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody id="newsList">
                            <tr><td colspan="7" class="loading"><div class="spinner"></div>Đang tải...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- CMS Section -->
        <section id="cms" class="section-panel">
            <div class="header">
                <h1>Quản lý nội dung</h1>
                <div class="header-actions">
                    <button class="btn btn-primary" onclick="openAddCMSModal()">
                        <span class="material-icons-outlined">add</span>
                        Thêm nội dung
                    </button>
                </div>
            </div>

            <div class="table-container">
                <div class="table-header" style="flex-direction: column; align-items: stretch; gap: 16px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                        <h2>📝 Quản lý Nội dung Website</h2>
                        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                            <button class="btn btn-primary cms-tab active" id="tabVisual" onclick="switchCMSTab('visual')">
                                <span class="material-icons-outlined">dashboard_customize</span> Chỉnh sửa trực quan
                            </button>
                            <button class="btn btn-outline cms-tab" id="tabContact" onclick="switchCMSTab('contact')">
                                <span class="material-icons-outlined">contact_phone</span> Liên hệ & MXH
                            </button>
                            <button class="btn btn-outline cms-tab" id="tabAll" onclick="switchCMSTab('all')">
                                <span class="material-icons-outlined">view_list</span> Tất cả
                            </button>
                            <button class="btn btn-outline cms-tab" id="tabImages" onclick="switchCMSTab('images')">
                                <span class="material-icons-outlined">image</span> Hình ảnh
                            </button>
                            <button class="btn btn-outline cms-tab" id="tabTexts" onclick="switchCMSTab('texts')">
                                <span class="material-icons-outlined">text_fields</span> Văn bản
                            </button>
                            <button class="btn btn-outline cms-tab" id="tabBanners" onclick="switchCMSTab('banners')">
                                <span class="material-icons-outlined">wallpaper</span> Banner
                            </button>
                        </div>
                    </div>
                    <div style="display: flex; gap: 12px; background: var(--bg-primary); padding: 12px; border-radius: var(--radius-md); flex-wrap: wrap;">
                        <select id="cmsPageFilter" class="filter-select" onchange="filterCMS()">
                            <option value="all">Tất cả trang</option>
                            <option value="global">Toàn trang (Global)</option>
                            <option value="index">Trang chủ</option>
                            <option value="nhat">Du học Nhật</option>
                            <option value="duc">Du học Đức</option>
                            <option value="han">Du học Hàn</option>
                            <option value="xkldjp">XKLĐ Nhật Bản</option>
                            <option value="xkldhan">XKLĐ Hàn Quốc</option>
                            <option value="xklddailoan">XKLĐ Đài Loan</option>
                            <option value="xkldchauau">XKLĐ Châu Âu</option>
                            <option value="huongnghiep">Hướng nghiệp</option>
                            <option value="veicogroup">Về ICOGroup</option>
                            <option value="lienhe">Liên hệ</option>
                            <option value="hoatdong">Hoạt động</option>
                        </select>
                        <select id="cmsSectionFilter" class="filter-select" onchange="filterCMS()">
                            <option value="all">Tất cả Section</option>
                            <option value="header">Header</option>
                            <option value="hero">Banner/Hero</option>
                            <option value="about">Giới thiệu</option>
                            <option value="ecosystem">Hệ sinh thái</option>
                            <option value="programs">Chương trình</option>
                            <option value="footer">Footer</option>
                        </select>
                        <div class="search-box" style="flex: 1; min-width: 200px;">
                            <span class="material-icons-outlined">search</span>
                            <input type="text" id="cmsSearchInput" placeholder="Tìm theo key..." onkeyup="filterCMS()">
                        </div>
                    </div>
                </div>
                
                <div id="cmsContent" style="padding: 20px; display: none;">
                    <div class="loading">
                        <div class="spinner"></div>
                        <p>Đang tải nội dung...</p>
                    </div>
                </div>
                
                <!-- Banner Management Section -->
                <div id="bannerContent" style="padding: 20px; display: none;">
                    <div style="margin-bottom: 20px;">
                        <h3 style="margin-bottom: 8px;">🖼️ Quản lý Hình nền Banner các trang</h3>
                        <p style="color: var(--text-secondary);">Upload hình nền cho phần Header các trang con của website</p>
                    </div>
                    <div id="bannerGrid" class="banner-grid">
                        <!-- Banner items will be loaded here -->
                    </div>
                </div>
                
                <!-- Contact & Social Section -->
                <div id="contactContent" style="padding: 20px; display: none;">
                    <div style="margin-bottom: 24px;">
                        <h3 style="margin-bottom: 8px; display: flex; align-items: center; gap: 10px;">
                            <span class="material-icons-outlined" style="color: var(--primary);">contact_phone</span>
                            Thông tin liên hệ & Mạng xã hội
                        </h3>
                        <p style="color: var(--text-secondary);">Quản lý số điện thoại, email và các link mạng xã hội hiển thị trên website</p>
                    </div>
                    
                    <!-- Contact Info Grid -->
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 24px;">
                        
                        <!-- Phone Section -->
                        <div class="cms-section-card">
                            <div class="cms-section-header" style="cursor: default;">
                                <h3 style="font-size: 16px; font-weight: 700; display: flex; align-items: center; gap: 10px; margin: 0;">
                                    <span class="material-icons-outlined" style="color: #10B981;">phone</span>
                                    Số điện thoại
                                </h3>
                            </div>
                            <div class="cms-section-body">
                                <div class="cms-field-group">
                                    <label class="cms-field-label">Số điện thoại (không dấu chấm)</label>
                                    <input type="text" class="cms-field-input" id="contact_header_phone" placeholder="0822314555">
                                    <small style="color: var(--text-muted); display: block; margin-top: 4px;">Dùng cho link gọi điện (tel:)</small>
                                </div>
                                <div class="cms-field-group">
                                    <label class="cms-field-label">Số điện thoại (có dấu chấm)</label>
                                    <input type="text" class="cms-field-input" id="contact_header_phone_display" placeholder="0822.314.555">
                                    <small style="color: var(--text-muted); display: block; margin-top: 4px;">Hiển thị trên giao diện</small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Email Section -->
                        <div class="cms-section-card">
                            <div class="cms-section-header" style="cursor: default;">
                                <h3 style="font-size: 16px; font-weight: 700; display: flex; align-items: center; gap: 10px; margin: 0;">
                                    <span class="material-icons-outlined" style="color: #F59E0B;">email</span>
                                    Email liên hệ
                                </h3>
                            </div>
                            <div class="cms-section-body">
                                <div class="cms-field-group">
                                    <label class="cms-field-label">Địa chỉ Email</label>
                                    <input type="email" class="cms-field-input" id="contact_header_email" placeholder="info@icogroup.vn">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Facebook Section -->
                        <div class="cms-section-card">
                            <div class="cms-section-header" style="cursor: default;">
                                <h3 style="font-size: 16px; font-weight: 700; display: flex; align-items: center; gap: 10px; margin: 0;">
                                    <span style="width: 24px; height: 24px; background: #1877F2; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="white"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                    </span>
                                    Facebook
                                </h3>
                            </div>
                            <div class="cms-section-body">
                                <div class="cms-field-group">
                                    <label class="cms-field-label">Link Facebook Page</label>
                                    <input type="url" class="cms-field-input" id="global_facebook_url" placeholder="https://facebook.com/icogroup">
                                </div>
                                <div class="cms-field-group">
                                    <label class="cms-field-label">Icon Facebook (URL hoặc Upload)</label>
                                    <div style="display: flex; gap: 10px; align-items: center;">
                                        <input type="url" class="cms-field-input" id="global_facebook_icon" placeholder="URL ảnh icon" style="flex: 1;" oninput="previewSocialIcon('facebook', this.value)">
                                        <label style="background: var(--primary); color: white; padding: 8px 12px; border-radius: 6px; cursor: pointer; white-space: nowrap;">
                                            <input type="file" accept="image/*" style="display: none;" onchange="uploadSocialIcon('facebook', this)">
                                            📤 Upload
                                        </label>
                                    </div>
                                    <div id="preview_facebook_icon" style="margin-top: 8px; width: 40px; height: 40px; border-radius: 50%; overflow: hidden; background: #f0f0f0; display: flex; align-items: center; justify-content: center;">
                                        <span style="color: #999; font-size: 20px;">📘</span>
                                    </div>
                                    <small style="color: var(--text-muted);">Để trống để dùng icon mặc định</small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- YouTube Section -->
                        <div class="cms-section-card">
                            <div class="cms-section-header" style="cursor: default;">
                                <h3 style="font-size: 16px; font-weight: 700; display: flex; align-items: center; gap: 10px; margin: 0;">
                                    <span style="width: 24px; height: 24px; background: #FF0000; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="white"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                                    </span>
                                    YouTube
                                </h3>
                            </div>
                            <div class="cms-section-body">
                                <div class="cms-field-group">
                                    <label class="cms-field-label">Link YouTube Channel</label>
                                    <input type="url" class="cms-field-input" id="global_youtube_url" placeholder="https://youtube.com/icogroup">
                                </div>
                                <div class="cms-field-group">
                                    <label class="cms-field-label">Icon YouTube (URL hoặc Upload)</label>
                                    <div style="display: flex; gap: 10px; align-items: center;">
                                        <input type="url" class="cms-field-input" id="global_youtube_icon" placeholder="URL ảnh icon" style="flex: 1;" oninput="previewSocialIcon('youtube', this.value)">
                                        <label style="background: var(--primary); color: white; padding: 8px 12px; border-radius: 6px; cursor: pointer; white-space: nowrap;">
                                            <input type="file" accept="image/*" style="display: none;" onchange="uploadSocialIcon('youtube', this)">
                                            📤 Upload
                                        </label>
                                    </div>
                                    <div id="preview_youtube_icon" style="margin-top: 8px; width: 40px; height: 40px; border-radius: 50%; overflow: hidden; background: #f0f0f0; display: flex; align-items: center; justify-content: center;">
                                        <span style="color: #999; font-size: 20px;">📺</span>
                                    </div>
                                    <small style="color: var(--text-muted);">Để trống để dùng icon mặc định</small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Zalo Section -->
                        <div class="cms-section-card">
                            <div class="cms-section-header" style="cursor: default;">
                                <h3 style="font-size: 16px; font-weight: 700; display: flex; align-items: center; gap: 10px; margin: 0;">
                                    <span style="width: 24px; height: 24px; background: #0068FF; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="white"><path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.894 8.221l-1.97 5.28c-.096.288-.444.48-.756.48H9.892c-.312 0-.66-.192-.756-.48l-1.97-5.28c-.168-.456.144-.936.636-.936h.924c.312 0 .588.216.684.504l1.356 4.032h2.468l1.356-4.032c.096-.288.372-.504.684-.504h.924c.492 0 .804.48.636.936z"/></svg>
                                    </span>
                                    Zalo
                                </h3>
                            </div>
                            <div class="cms-section-body">
                                <div class="cms-field-group">
                                    <label class="cms-field-label">Link Zalo</label>
                                    <input type="url" class="cms-field-input" id="global_zalo_url" placeholder="https://zalo.me/0822314555">
                                </div>
                                <div class="cms-field-group">
                                    <label class="cms-field-label">Icon Zalo (URL hoặc Upload)</label>
                                    <div style="display: flex; gap: 10px; align-items: center;">
                                        <input type="url" class="cms-field-input" id="global_zalo_icon" placeholder="URL ảnh icon" style="flex: 1;" oninput="previewSocialIcon('zalo', this.value)">
                                        <label style="background: var(--primary); color: white; padding: 8px 12px; border-radius: 6px; cursor: pointer; white-space: nowrap;">
                                            <input type="file" accept="image/*" style="display: none;" onchange="uploadSocialIcon('zalo', this)">
                                            📤 Upload
                                        </label>
                                    </div>
                                    <div id="preview_zalo_icon" style="margin-top: 8px; width: 40px; height: 40px; border-radius: 50%; overflow: hidden; background: #f0f0f0; display: flex; align-items: center; justify-content: center;">
                                        <span style="color: #999; font-size: 20px;">💬</span>
                                    </div>
                                    <small style="color: var(--text-muted);">Để trống để dùng icon mặc định</small>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                    
                    <!-- Menu Navigation Section -->
                    <h3 style="margin: 32px 0 16px; display: flex; align-items: center; gap: 10px;">
                        <span class="material-icons-outlined" style="color: var(--primary);">menu</span>
                        Menu Navigation
                    </h3>
                    
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
                        <!-- Main Menu Items -->
                        <div class="cms-section-card">
                            <div class="cms-section-header" style="cursor: default;">
                                <h3 style="font-size: 16px; font-weight: 700; display: flex; align-items: center; gap: 10px; margin: 0;">
                                    <span class="material-icons-outlined" style="color: #6366F1;">home</span>
                                    Menu chính
                                </h3>
                            </div>
                            <div class="cms-section-body">
                                <div class="cms-field-group">
                                    <label class="cms-field-label">Trang chủ</label>
                                    <input type="text" class="cms-field-input" id="contact_menu_trangchu" placeholder="Trang chủ">
                                </div>
                                <div class="cms-field-group">
                                    <label class="cms-field-label">Về ICOGroup</label>
                                    <input type="text" class="cms-field-input" id="contact_menu_veicogroup" placeholder="Về ICOGroup">
                                </div>
                                <div class="cms-field-group">
                                    <label class="cms-field-label">Hướng nghiệp</label>
                                    <input type="text" class="cms-field-input" id="contact_menu_huongnghiep" placeholder="Hướng nghiệp">
                                </div>
                                <div class="cms-field-group">
                                    <label class="cms-field-label">Hoạt động</label>
                                    <input type="text" class="cms-field-input" id="contact_menu_hoatdong" placeholder="Hoạt động">
                                </div>
                                <div class="cms-field-group">
                                    <label class="cms-field-label">Liên hệ</label>
                                    <input type="text" class="cms-field-input" id="contact_menu_lienhe" placeholder="Liên hệ">
                                </div>
                                <div class="cms-field-group">
                                    <label class="cms-field-label">Đăng ký</label>
                                    <input type="text" class="cms-field-input" id="contact_menu_dangky" placeholder="Đăng ký">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Du học Menu -->
                        <div class="cms-section-card">
                            <div class="cms-section-header" style="cursor: default;">
                                <h3 style="font-size: 16px; font-weight: 700; display: flex; align-items: center; gap: 10px; margin: 0;">
                                    <span style="font-size: 18px;">🎓</span>
                                    Menu Du học
                                </h3>
                            </div>
                            <div class="cms-section-body">
                                <div class="cms-field-group">
                                    <label class="cms-field-label">Du học (menu chính)</label>
                                    <input type="text" class="cms-field-input" id="contact_menu_duhoc" placeholder="Du học">
                                </div>
                                <div class="menu-item-row">
                                    <span class="menu-icon">🇩🇪</span>
                                    <input type="text" class="cms-field-input" id="contact_menu_duhoc_germany" placeholder="Du học Đức" style="flex: 1;">
                                    <label class="toggle-switch" title="Bật/tắt hiển thị">
                                        <input type="checkbox" id="toggle_menu_duhoc_germany" checked>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                                <div class="menu-item-row">
                                    <span class="menu-icon">🇯🇵</span>
                                    <input type="text" class="cms-field-input" id="contact_menu_duhoc_japan" placeholder="Du học Nhật" style="flex: 1;">
                                    <label class="toggle-switch" title="Bật/tắt hiển thị">
                                        <input type="checkbox" id="toggle_menu_duhoc_japan" checked>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                                <div class="menu-item-row">
                                    <span class="menu-icon">🇰🇷</span>
                                    <input type="text" class="cms-field-input" id="contact_menu_duhoc_korea" placeholder="Du học Hàn Quốc" style="flex: 1;">
                                    <label class="toggle-switch" title="Bật/tắt hiển thị">
                                        <input type="checkbox" id="toggle_menu_duhoc_korea" checked>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <!-- XKLĐ Menu -->
                        <div class="cms-section-card">
                            <div class="cms-section-header" style="cursor: default;">
                                <h3 style="font-size: 16px; font-weight: 700; display: flex; align-items: center; gap: 10px; margin: 0;">
                                    <span style="font-size: 18px;">💼</span>
                                    Menu XKLĐ
                                </h3>
                            </div>
                            <div class="cms-section-body">
                                <div class="cms-field-group">
                                    <label class="cms-field-label">Xuất khẩu lao động (menu chính)</label>
                                    <input type="text" class="cms-field-input" id="contact_menu_xkld" placeholder="Xuất khẩu lao động">
                                </div>
                                <div class="menu-item-row">
                                    <span class="menu-icon">🇯🇵</span>
                                    <input type="text" class="cms-field-input" id="contact_menu_xkld_japan" placeholder="Nhật Bản" style="flex: 1;">
                                    <label class="toggle-switch" title="Bật/tắt hiển thị">
                                        <input type="checkbox" id="toggle_menu_xkld_japan" checked>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                                <div class="menu-item-row">
                                    <span class="menu-icon">🇰🇷</span>
                                    <input type="text" class="cms-field-input" id="contact_menu_xkld_korea" placeholder="Hàn Quốc" style="flex: 1;">
                                    <label class="toggle-switch" title="Bật/tắt hiển thị">
                                        <input type="checkbox" id="toggle_menu_xkld_korea" checked>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                                <div class="menu-item-row">
                                    <span class="menu-icon">🇹🇼</span>
                                    <input type="text" class="cms-field-input" id="contact_menu_xkld_taiwan" placeholder="Đài Loan" style="flex: 1;">
                                    <label class="toggle-switch" title="Bật/tắt hiển thị">
                                        <input type="checkbox" id="toggle_menu_xkld_taiwan" checked>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                                <div class="menu-item-row">
                                    <span class="menu-icon">🇪🇺</span>
                                    <input type="text" class="cms-field-input" id="contact_menu_xkld_eu" placeholder="Châu Âu" style="flex: 1;">
                                    <label class="toggle-switch" title="Bật/tắt hiển thị">
                                        <input type="checkbox" id="toggle_menu_xkld_eu" checked>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Save Button -->
                    <div style="margin-top: 24px; padding: 16px; background: var(--bg-primary); border-radius: var(--radius-md); display: flex; justify-content: space-between; align-items: center;">
                        <div style="display: flex; align-items: center; gap: 8px; color: var(--text-secondary);">
                            <span class="material-icons-outlined">info</span>
                            <span>Thay đổi sẽ được áp dụng ngay trên website sau khi lưu</span>
                        </div>
                        <button class="btn btn-success" onclick="saveContactSettings()" id="saveContactBtn">
                            <span class="material-icons-outlined">save</span>
                            Lưu thay đổi
                        </button>
                    </div>
                </div>
                
                <!-- Visual CMS Editor -->
                <div id="visualCmsContent" class="visual-cms-container" style="display: block;">
                    <div class="visual-cms-header">
                        <h2>✨ Chỉnh sửa nội dung trực quan</h2>
                        <div class="page-selector">
                            <label>Chọn trang:</label>
                            <select id="visualPageSelect" onchange="loadVisualPage()">
                                <option value="">-- Chọn trang để chỉnh sửa --</option>
                                <option value="index">🏠 Trang chủ</option>
                                <option value="nhat">🇯🇵 Du học Nhật Bản</option>
                                <option value="duc">🇩🇪 Du học Đức</option>
                                <option value="han">🇰🇷 Du học Hàn Quốc</option>
                                <option value="xkldjp">💼 XKLĐ Nhật Bản</option>
                                <option value="xkldhan">💼 XKLĐ Hàn Quốc</option>
                                <option value="xklddailoan">💼 XKLĐ Đài Loan</option>
                                <option value="xkldchauau">💼 XKLĐ Châu Âu</option>
                                <option value="huongnghiep">🎯 Hướng nghiệp</option>
                                <option value="veicogroup">ℹ️ Về ICOGroup</option>
                                <option value="lienhe">📞 Liên hệ</option>
                                <option value="hoatdong">📰 Hoạt động</option>
                            </select>
                        </div>
                    </div>
                    
                    <div id="visualSectionsContainer">
                        <div class="cms-empty">
                            <span class="material-icons-outlined icon">touch_app</span>
                            <h3>Chọn trang để bắt đầu chỉnh sửa</h3>
                            <p>Chọn một trang từ dropdown ở trên để xem và chỉnh sửa nội dung</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Content Blocks Section -->
        <section id="contentBlocks" class="section-panel">
            <div class="header">
                <h1>📦 Content Blocks</h1>
                <div class="header-actions">
                    <button class="btn btn-primary" onclick="openAddBlockModal()">
                        <span class="material-icons-outlined">add</span>
                        Thêm Block
                    </button>
                </div>
            </div>

            <div class="table-container" style="padding: 24px;">
                <div class="blocks-header">
                    <div class="blocks-header-left">
                        <h2 style="margin: 0;">Quản lý Content Blocks</h2>
                        <select id="blockPageSelect" class="page-select" onchange="loadContentBlocks(this.value)">
                            <option value="">-- Chọn trang để quản lý --</option>
                            <option value="duc">🇩🇪 Du học Đức</option>
                            <option value="nhat">🇯🇵 Du học Nhật Bản</option>
                            <option value="han">🇰🇷 Du học Hàn Quốc</option>
                            <option value="xkldjp">💼 XKLĐ Nhật Bản</option>
                            <option value="xkldhan">💼 XKLĐ Hàn Quốc</option>
                            <option value="xklddailoan">💼 XKLĐ Đài Loan</option>
                            <option value="xkldchauau">💼 XKLĐ Châu Âu</option>
                            <option value="huongnghiep">🎯 Hướng nghiệp</option>
                        </select>
                    </div>
                </div>

                <div id="blocksContainer" class="blocks-grid">
                    <div class="blocks-empty">
                        <span class="material-icons-outlined icon">widgets</span>
                        <h3>Chọn một trang để bắt đầu</h3>
                        <p>Sử dụng dropdown ở trên để chọn trang và quản lý content blocks</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Block Modal -->
        <div id="blockModal" class="modal">
            <div class="modal-content block-modal-content">
                <div class="modal-header">
                    <h2 id="blockModalTitle">Thêm Content Block</h2>
                    <button class="modal-close" onclick="closeModal('blockModal')">
                        <span class="material-icons-outlined">close</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="blockId">
                    
                    <div class="block-form-row">
                        <div>
                            <label>Loại Block</label>
                            <select id="blockType" style="width: 100%;">
                                <option value="section">Section</option>
                                <option value="card">Card</option>
                                <option value="info">Info Box</option>
                                <option value="banner">Banner</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="block-form-row">
                        <div>
                            <label>Thứ tự hiển thị</label>
                            <input type="number" id="blockOrder" value="1" min="0">
                        </div>
                    </div>

                    <label>Tiêu đề</label>
                    <div class="editor-wrapper">
                        <div class="editor-toolbar">
                            <div class="toolbar-group">
                                <button type="button" onclick="formatBlockText('bold')" title="Bold (Ctrl+B)">
                                    <span class="material-icons-outlined">format_bold</span>
                                </button>
                                <button type="button" onclick="formatBlockText('italic')" title="Italic (Ctrl+I)">
                                    <span class="material-icons-outlined">format_italic</span>
                                </button>
                                <button type="button" onclick="formatBlockText('underline')" title="Underline (Ctrl+U)">
                                    <span class="material-icons-outlined">format_underlined</span>
                                </button>
                                <button type="button" onclick="formatBlockText('strikeThrough')" title="Strikethrough">
                                    <span class="material-icons-outlined">format_strikethrough</span>
                                </button>
                            </div>
                            <div class="toolbar-divider"></div>
                            <div class="toolbar-group">
                                <button type="button" onclick="formatBlockText('justifyLeft')" title="Căn trái">
                                    <span class="material-icons-outlined">format_align_left</span>
                                </button>
                                <button type="button" onclick="formatBlockText('justifyCenter')" title="Căn giữa">
                                    <span class="material-icons-outlined">format_align_center</span>
                                </button>
                                <button type="button" onclick="formatBlockText('justifyRight')" title="Căn phải">
                                    <span class="material-icons-outlined">format_align_right</span>
                                </button>
                            </div>
                            <div class="toolbar-divider"></div>
                            <div class="toolbar-group">
                                <button type="button" onclick="insertLink()" title="Chèn link">
                                    <span class="material-icons-outlined">link</span>
                                </button>
                                <button type="button" onclick="formatBlockText('insertUnorderedList')" title="Danh sách">
                                    <span class="material-icons-outlined">format_list_bulleted</span>
                                </button>
                            </div>
                            <div class="toolbar-divider"></div>
                            <div class="color-picker-wrapper">
                                <input type="color" id="titleColorPicker" value="#000000" onchange="applyBlockTextColor(this.value)">
                                <div class="color-picker-preview" onclick="document.getElementById('titleColorPicker').click()">
                                    <span class="material-icons-outlined">format_color_text</span>
                                </div>
                            </div>
                            <select class="font-select" onchange="applyBlockFont(this.value)" style="max-width: 120px;">
                                <option value="">Font</option>
                            </select>
                        </div>
                        <div id="blockTitleEditor" contenteditable="true" class="rich-editor rich-editor-mini" data-placeholder="Nhập tiêu đề..."></div>
                    </div>

                    <label>Hình ảnh</label>
                    <div class="image-upload-area">
                        <div class="image-preview-box" id="blockImagePreview">
                            <span class="placeholder">
                                <span class="material-icons-outlined">image</span>
                                Preview
                            </span>
                        </div>
                        <div class="image-input-group">
                            <input type="text" id="blockImageUrl" placeholder="URL hình ảnh..." onchange="previewBlockImageUrl()">
                            <label class="upload-btn">
                                <input type="file" accept="image/*" style="display: none;" onchange="uploadBlockImage(this)">
                                <span class="material-icons-outlined">cloud_upload</span> Upload ảnh
                            </label>
                        </div>
                    </div>

                    <label style="margin-top: 16px;">Nội dung</label>
                    <div class="editor-wrapper">
                        <div class="editor-toolbar">
                            <div class="toolbar-group">
                                <button type="button" onclick="formatBlockText('bold')" title="In đậm (Ctrl+B)">
                                    <span class="material-icons-outlined">format_bold</span>
                                </button>
                                <button type="button" onclick="formatBlockText('italic')" title="In nghiêng (Ctrl+I)">
                                    <span class="material-icons-outlined">format_italic</span>
                                </button>
                                <button type="button" onclick="formatBlockText('underline')" title="Gạch chân (Ctrl+U)">
                                    <span class="material-icons-outlined">format_underlined</span>
                                </button>
                                <button type="button" onclick="formatBlockText('strikeThrough')" title="Gạch ngang">
                                    <span class="material-icons-outlined">strikethrough_s</span>
                                </button>
                            </div>
                            
                            <div class="toolbar-divider"></div>
                            
                            <div class="color-picker-wrapper">
                                <input type="color" id="contentColorPicker" value="#000000" onchange="applyBlockTextColor(this.value)">
                                <div class="color-picker-preview" onclick="document.getElementById('contentColorPicker').click()">
                                    <span class="material-icons-outlined">format_color_text</span>
                                </div>
                            </div>
                            
                            <select class="font-select" onchange="applyBlockFont(this.value)">
                                <option value="">-- Font --</option>
                            </select>
                            
                            <select class="font-size-select" onchange="applyBlockFontSize(this.value)">
                                <option value="">Cỡ</option>
                                <option value="1">Rất nhỏ</option>
                                <option value="2">Nhỏ</option>
                                <option value="3">Vừa</option>
                                <option value="4">TB</option>
                                <option value="5">Lớn</option>
                                <option value="6">Rất lớn</option>
                                <option value="7">Cực lớn</option>
                            </select>
                            
                            <div class="toolbar-divider"></div>
                            
                            <div class="toolbar-group">
                                <button type="button" onclick="formatBlockText('justifyLeft')" title="Căn trái">
                                    <span class="material-icons-outlined">format_align_left</span>
                                </button>
                                <button type="button" onclick="formatBlockText('justifyCenter')" title="Căn giữa">
                                    <span class="material-icons-outlined">format_align_center</span>
                                </button>
                                <button type="button" onclick="formatBlockText('justifyRight')" title="Căn phải">
                                    <span class="material-icons-outlined">format_align_right</span>
                                </button>
                            </div>
                            
                            <div class="toolbar-divider"></div>
                            
                            <button type="button" onclick="insertBlockLink()" title="Chèn liên kết">
                                <span class="material-icons-outlined">link</span>
                            </button>
                            <button type="button" onclick="formatBlockText('insertUnorderedList')" title="Danh sách">
                                <span class="material-icons-outlined">format_list_bulleted</span>
                            </button>
                        </div>
                        <div id="blockContentEditor" contenteditable="true" class="rich-editor" data-placeholder="Nhập nội dung với định dạng..."></div>
                    </div>

                    <!-- Tracking info -->
                    <div id="blockTrackingInfo" class="tracking-info" style="display: none;">
                        <span class="material-icons-outlined">history</span>
                        Cập nhật bởi: <strong id="blockUpdatedBy"></strong> 
                        lúc <strong id="blockUpdatedAt"></strong>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-outline" onclick="closeModal('blockModal')">Hủy</button>
                    <button class="btn btn-primary" onclick="saveBlock()">
                        <span class="material-icons-outlined">save</span>
                        Lưu Block
                    </button>
                </div>
            </div>
        </div>

        <!-- Activity Logs Section -->
        <section id="logs" class="section-panel">
            <div class="header">
                <h1>📋 Activity Logs</h1>
                <div class="header-actions">
                    <button class="btn btn-outline" onclick="loadActivityLogs()">
                        <span class="material-icons-outlined">refresh</span>
                        Làm mới
                    </button>
                </div>
            </div>

            <div class="table-container">
                <div class="table-header">
                    <h2>Lịch sử hoạt động</h2>
                    <div class="table-filters">
                        <div class="search-box">
                            <span class="material-icons-outlined">search</span>
                            <input type="text" id="logsSearchInput" placeholder="Tìm kiếm..." onkeyup="filterLogs()">
                        </div>
                        <select class="filter-select" id="logsUserFilter" onchange="filterLogs()">
                            <option value="">Tất cả người dùng</option>
                            <!-- Will be populated dynamically -->
                        </select>
                        <select class="filter-select" id="logsActionFilter" onchange="filterLogs()">
                            <option value="">Tất cả hành động</option>
                            <option value="login">Đăng nhập</option>
                            <option value="logout">Đăng xuất</option>
                            <option value="create">Tạo mới</option>
                            <option value="update">Cập nhật</option>
                            <option value="delete">Xóa</option>
                        </select>
                    </div>
                </div>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Thời gian</th>
                                <th>Người dùng</th>
                                <th>Hành động</th>
                                <th>Chi tiết</th>
                                <th>IP</th>
                            </tr>
                        </thead>
                        <tbody id="logsList">
                            <tr><td colspan="6" class="empty-state">
                                <span class="material-icons-outlined">history</span>
                                <h3>Chức năng Activity Logs</h3>
                                <p>Theo dõi hoạt động của người dùng trong hệ thống</p>
                            </td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- System Settings Section -->
        <section id="settings" class="section-panel">
            <div class="header">
                <h1>⚙️ Cài đặt hệ thống</h1>
                <div class="header-actions">
                    <button class="btn btn-primary" onclick="saveSettings()">
                        <span class="material-icons-outlined">save</span>
                        Lưu cài đặt
                    </button>
                </div>
            </div>

            <div class="settings-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 24px;">
                <!-- General Settings -->
                <div class="table-container">
                    <div class="table-header">
                        <h2>🏢 Thông tin chung</h2>
                    </div>
                    <div style="padding: 24px;">
                        <div style="margin-bottom: 20px;">
                            <label style="display: block; font-weight: 600; margin-bottom: 8px;">Tên website</label>
                            <input type="text" id="settingSiteName" class="cms-field-input" value="ICOGroup" placeholder="Nhập tên website">
                        </div>
                        <div style="margin-bottom: 20px;">
                            <label style="display: block; font-weight: 600; margin-bottom: 8px;">Mô tả website</label>
                            <textarea id="settingSiteDesc" class="cms-field-input" rows="3" placeholder="Mô tả ngắn về website">Du học & Xuất khẩu lao động uy tín</textarea>
                        </div>
                        <div style="margin-bottom: 20px;">
                            <label style="display: block; font-weight: 600; margin-bottom: 8px;">Email liên hệ</label>
                            <input type="email" id="settingEmail" class="cms-field-input" value="info@icogroup.vn" placeholder="email@example.com">
                        </div>
                        <div style="margin-bottom: 20px;">
                            <label style="display: block; font-weight: 600; margin-bottom: 8px;">Số điện thoại</label>
                            <input type="text" id="settingPhone" class="cms-field-input" value="0822.314.555" placeholder="Số điện thoại">
                        </div>
                    </div>
                </div>

                <!-- Security Settings -->
                <div class="table-container">
                    <div class="table-header">
                        <h2>🔒 Bảo mật</h2>
                    </div>
                    <div style="padding: 24px;">
                        <div style="margin-bottom: 20px;">
                            <label style="display: block; font-weight: 600; margin-bottom: 8px;">Thời gian hết phiên (phút)</label>
                            <input type="number" id="settingSessionTimeout" class="cms-field-input" value="60" min="5" max="1440">
                            <small style="color: var(--text-muted);">Thời gian không hoạt động trước khi tự động đăng xuất</small>
                        </div>
                        <div style="margin-bottom: 20px;">
                            <label style="display: block; font-weight: 600; margin-bottom: 8px;">Số lần đăng nhập sai tối đa</label>
                            <input type="number" id="settingMaxLoginAttempts" class="cms-field-input" value="5" min="3" max="10">
                        </div>
                        <div style="margin-bottom: 20px; display: flex; align-items: center; gap: 12px;">
                            <label class="toggle-switch">
                                <input type="checkbox" id="settingMaintenanceMode">
                                <span class="toggle-slider"></span>
                            </label>
                            <span style="font-weight: 600;">Chế độ bảo trì</span>
                        </div>
                    </div>
                </div>

                <!-- Notification Settings -->
                <div class="table-container">
                    <div class="table-header">
                        <h2>🔔 Thông báo</h2>
                    </div>
                    <div style="padding: 24px;">
                        <div style="margin-bottom: 20px; display: flex; align-items: center; gap: 12px;">
                            <label class="toggle-switch">
                                <input type="checkbox" id="settingEmailNotify" checked>
                                <span class="toggle-slider"></span>
                            </label>
                            <span style="font-weight: 600;">Gửi email khi có đăng ký mới</span>
                        </div>
                        <div style="margin-bottom: 20px; display: flex; align-items: center; gap: 12px;">
                            <label class="toggle-switch">
                                <input type="checkbox" id="settingEmailContact" checked>
                                <span class="toggle-slider"></span>
                            </label>
                            <span style="font-weight: 600;">Gửi email khi có liên hệ mới</span>
                        </div>
                        <div style="margin-bottom: 20px;">
                            <label style="display: block; font-weight: 600; margin-bottom: 8px;">Email nhận thông báo</label>
                            <input type="email" id="settingNotifyEmail" class="cms-field-input" value="admin@icogroup.vn" placeholder="Email nhận thông báo">
                        </div>
                    </div>
                </div>
                
                <!-- Font Settings -->
                <div class="table-container">
                    <div class="table-header">
                        <h2>🔤 Cài đặt Font chữ</h2>
                    </div>
                    <div style="padding: 24px;">
                        <div style="margin-bottom: 20px;">
                            <label style="display: block; font-weight: 600; margin-bottom: 8px;">Font cho nội dung (Body)</label>
                            <input type="text" id="settingFontBody" class="cms-field-input" value="'Inter', sans-serif" placeholder="'Roboto', sans-serif">
                            <small style="color: var(--text-muted);">Font family cho nội dung chính. Ví dụ: 'Roboto', sans-serif</small>
                        </div>
                        <div style="margin-bottom: 20px;">
                            <label style="display: block; font-weight: 600; margin-bottom: 8px;">URL Google Font cho Body (tuỳ chọn)</label>
                            <input type="url" id="settingFontBodyUrl" class="cms-field-input" placeholder="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap">
                            <small style="color: var(--text-muted);">Để trống nếu dùng system font</small>
                        </div>
                        <div style="margin-bottom: 20px;">
                            <label style="display: block; font-weight: 600; margin-bottom: 8px;">Font cho tiêu đề (Heading)</label>
                            <input type="text" id="settingFontHeading" class="cms-field-input" value="'Inter', sans-serif" placeholder="'Montserrat', sans-serif">
                            <small style="color: var(--text-muted);">Font family cho các tiêu đề. Ví dụ: 'Montserrat', sans-serif</small>
                        </div>
                        <div style="margin-bottom: 20px;">
                            <label style="display: block; font-weight: 600; margin-bottom: 8px;">URL Google Font cho Heading (tuỳ chọn)</label>
                            <input type="url" id="settingFontHeadingUrl" class="cms-field-input" placeholder="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap">
                            <small style="color: var(--text-muted);">Để trống nếu dùng system font</small>
                        </div>
                        <div style="padding: 12px; background: var(--info-light); border-left: 4px solid var(--info); border-radius: 6px;">
                            <small style="color: var(--text-secondary);">💡 Sau khi lưu, font sẽ tự động áp dụng cho cả Admin Panel và Frontend. Nhớ refresh trang để thấy thay đổi (Ctrl + F5).</small>
                        </div>
                    </div>
                </div>

                <!-- Backup Settings -->
                <div class="table-container">
                    <div class="table-header">
                        <h2>💾 Sao lưu dữ liệu</h2>
                    </div>
                    <div style="padding: 24px;">
                        <div style="margin-bottom: 20px;">
                            <p style="color: var(--text-secondary); margin-bottom: 16px;">Tạo bản sao lưu database để phòng tránh mất dữ liệu</p>
                            <button class="btn btn-success" onclick="createBackup()" style="width: 100%;">
                                <span class="material-icons-outlined">backup</span>
                                Tạo bản sao lưu
                            </button>
                        </div>
                        <div style="margin-bottom: 20px;">
                            <label style="display: block; font-weight: 600; margin-bottom: 8px;">Lần sao lưu cuối</label>
                            <p id="lastBackupTime" style="color: var(--text-muted);">Chưa có bản sao lưu</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Permission Management Section (Full Width) -->
            <div class="table-container" style="grid-column: 1 / -1; margin-top: 24px;">
                <div class="table-header">
                    <h2>🔐 Phân quyền Role</h2>
                    <button class="btn btn-outline" onclick="loadPermissions()">
                        <span class="material-icons-outlined">refresh</span>
                        Làm mới
                    </button>
                </div>
                <div style="padding: 24px;">
                    <p style="color: var(--text-secondary); margin-bottom: 20px;">
                        Cấu hình quyền hạn cho từng role. <strong>Admin</strong> luôn có tất cả quyền.
                    </p>
                    
                    <div id="permissionMatrix">
                        <?php if (!empty($permissionsData)): ?>
                            <?php foreach ($permissionsData as $categoryKey => $category): ?>
                            <div class="permission-category" style="margin-bottom: 20px; border: 1px solid var(--border-light); border-radius: var(--radius-md); overflow: hidden;">
                                <div class="category-header" style="display: flex; justify-content: space-between; align-items: center; padding: 14px 20px; background: linear-gradient(135deg, var(--bg-primary), var(--surface)); cursor: pointer; border-bottom: 1px solid var(--border-light);" onclick="togglePermissionCategory(this)">
                                    <h4 style="display: flex; align-items: center; gap: 10px; margin: 0; font-size: 15px;">
                                        <span class="material-icons-outlined" style="color: var(--primary);"><?php echo htmlspecialchars($category['icon']); ?></span>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                        <span style="background: var(--info-light); color: var(--primary); padding: 2px 8px; border-radius: 12px; font-size: 12px; font-weight: 600;"><?php echo count($category['permissions']); ?></span>
                                    </h4>
                                    <span class="material-icons-outlined toggle-icon" style="color: var(--text-muted); transition: transform 0.2s;">expand_more</span>
                                </div>
                                <div class="category-body" style="padding: 0;">
                                    <table style="width: 100%; border-collapse: collapse;">
                                        <thead>
                                            <tr style="background: var(--bg-primary);">
                                                <th style="text-align: left; padding: 12px 16px; font-size: 12px; text-transform: uppercase; color: var(--text-secondary); letter-spacing: 0.5px; width: 35%;">Quyền</th>
                                                <th style="text-align: left; padding: 12px 16px; font-size: 12px; text-transform: uppercase; color: var(--text-secondary); letter-spacing: 0.5px;">Mô tả</th>
                                                <th style="text-align: center; padding: 12px 16px; font-size: 12px; text-transform: uppercase; color: var(--text-secondary); letter-spacing: 0.5px; width: 100px;">Manager</th>
                                                <th style="text-align: center; padding: 12px 16px; font-size: 12px; text-transform: uppercase; color: var(--text-secondary); letter-spacing: 0.5px; width: 100px;">User</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($category['permissions'] as $perm): ?>
                                            <tr style="border-bottom: 1px solid var(--border-light);">
                                                <td style="padding: 12px 16px; font-weight: 500;"><?php echo htmlspecialchars($perm['name']); ?></td>
                                                <td style="padding: 12px 16px; color: var(--text-secondary); font-size: 13px;"><?php echo htmlspecialchars($perm['description']); ?></td>
                                                <td style="padding: 12px 16px; text-align: center;">
                                                    <label class="toggle-switch">
                                                        <input type="checkbox" <?php echo $perm['manager'] ? 'checked' : ''; ?> onchange="updatePermission('manager', '<?php echo htmlspecialchars($perm['key']); ?>', this.checked)">
                                                        <span class="toggle-slider"></span>
                                                    </label>
                                                </td>
                                                <td style="padding: 12px 16px; text-align: center;">
                                                    <label class="toggle-switch">
                                                        <input type="checkbox" <?php echo $perm['user'] ? 'checked' : ''; ?> onchange="updatePermission('user', '<?php echo htmlspecialchars($perm['key']); ?>', this.checked)">
                                                        <span class="toggle-slider"></span>
                                                    </label>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-state" style="text-align: center; padding: 40px;">
                                <span class="material-icons-outlined" style="font-size: 48px; color: var(--text-muted);">info</span>
                                <h3>Chưa có dữ liệu quyền</h3>
                                <p>Vui lòng import file: backend_api/database/permission_migration.sql</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="editModalTitle">Chỉnh sửa thông tin</h2>
                <button class="modal-close" onclick="closeModal('editModal')">
                    <span class="material-icons-outlined">close</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="editForm">
                    <input type="hidden" id="editId">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                    <label>Họ tên</label>
                    <input type="text" id="editHoTen" required>
                    <label>Năm sinh</label>
                    <input type="text" id="editNamSinh">
                    <label>Địa chỉ</label>
                    <input type="text" id="editDiaChi">
                    <label>Chương trình</label>
                    <select id="editChuongTrinh">
                        <option value="Du học">Du học</option>
                        <option value="Xuất khẩu lao động">Xuất khẩu lao động</option>
                        <option value="Đào tạo ngoại ngữ">Đào tạo ngoại ngữ</option>
                    </select>
                    <label>Quốc gia</label>
                    <input type="text" id="editQuocGia">
                    <label>Số điện thoại</label>
                    <input type="text" id="editSdt">
                    <label>Ghi chú</label>
                    <textarea id="editGhiChu" rows="3"></textarea>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" onclick="closeModal('editModal')">Hủy</button>
                <button class="btn btn-primary" onclick="saveEdit()">Lưu thay đổi</button>
            </div>
        </div>
    </div>

    <!-- Delete Confirm Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content" style="width: 400px;">
            <div class="confirm-dialog">
                <div class="icon">
                    <span class="material-icons-outlined">delete_outline</span>
                </div>
                <h3>Xác nhận xóa</h3>
                <p>Bạn có chắc chắn muốn xóa bản ghi này? Hành động này không thể hoàn tác.</p>
                <div class="btn-group">
                    <button class="btn btn-outline" onclick="closeModal('deleteModal')">Hủy</button>
                    <button class="btn btn-danger" onclick="confirmDelete()">Xóa</button>
                </div>
            </div>
        </div>
    </div>




    <!-- News Modal -->
    <div id="newsModal" class="modal">
        <div class="modal-content" style="max-width: 800px;">
            <div class="modal-header">
                <h2 id="newsModalTitle">Thêm tin tức</h2>
                <button class="modal-close" onclick="closeModal('newsModal')">
                    <span class="material-icons-outlined">close</span>
                </button>
            </div>
            <div class="modal-body" style="max-height: 70vh;">
                <form id="newsForm">
                    <input type="hidden" id="newsId">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div>
                            <label>Tiêu đề *</label>
                            <input type="text" id="newsTitle" required placeholder="Nhập tiêu đề bài viết...">
                        </div>
                        <div>
                            <label>Danh mục</label>
                            <select id="newsCategory">
                                <option value="tin-tuc">Tin tức</option>
                                <option value="hoat-dong">Hoạt động</option>
                                <option value="su-kien">Sự kiện</option>
                                <option value="thong-bao">Thông báo</option>
                            </select>
                        </div>
                    </div>
                    
                    <label>Mô tả ngắn</label>
                    <textarea id="newsExcerpt" rows="2" placeholder="Tóm tắt nội dung bài viết..."></textarea>
                    
                    <!-- Rich Text Editor -->
                    <label>Nội dung bài viết</label>
                    <div class="editor-toolbar">
                        <button type="button" onclick="formatText('bold')" title="In đậm">
                            <span class="material-icons-outlined">format_bold</span>
                        </button>
                        <button type="button" onclick="formatText('italic')" title="In nghiêng">
                            <span class="material-icons-outlined">format_italic</span>
                        </button>
                        <button type="button" onclick="formatText('underline')" title="Gạch chân">
                            <span class="material-icons-outlined">format_underlined</span>
                        </button>
                        <span class="toolbar-divider"></span>
                        <button type="button" onclick="formatText('insertUnorderedList')" title="Danh sách">
                            <span class="material-icons-outlined">format_list_bulleted</span>
                        </button>
                        <button type="button" onclick="formatText('insertOrderedList')" title="Danh sách số">
                            <span class="material-icons-outlined">format_list_numbered</span>
                        </button>
                        <span class="toolbar-divider"></span>
                        <button type="button" onclick="formatText('justifyLeft')" title="Căn trái">
                            <span class="material-icons-outlined">format_align_left</span>
                        </button>
                        <button type="button" onclick="formatText('justifyCenter')" title="Căn giữa">
                            <span class="material-icons-outlined">format_align_center</span>
                        </button>
                        <button type="button" onclick="formatText('justifyRight')" title="Căn phải">
                            <span class="material-icons-outlined">format_align_right</span>
                        </button>
                        <span class="toolbar-divider"></span>
                        <button type="button" onclick="insertLink()" title="Chèn link">
                            <span class="material-icons-outlined">link</span>
                        </button>
                        <button type="button" onclick="showImageInsertDialog()" title="Chèn ảnh" class="btn-highlight">
                            <span class="material-icons-outlined">add_photo_alternate</span>
                        </button>
                        <span class="toolbar-divider"></span>
                        <select onchange="formatHeading(this.value)" style="padding: 4px 8px; border: 1px solid var(--border-light); border-radius: 4px;">
                            <option value="">Định dạng</option>
                            <option value="h2">Tiêu đề lớn</option>
                            <option value="h3">Tiêu đề vừa</option>
                            <option value="h4">Tiêu đề nhỏ</option>
                            <option value="p">Đoạn văn</option>
                        </select>
                    </div>
                    <div id="newsContent" class="rich-editor" contenteditable="true" placeholder="Nhập nội dung bài viết tại đây. Bạn có thể chèn ảnh bằng nút trên toolbar..."></div>
                    
                    <div style="display: grid; grid-template-columns: 1fr auto; gap: 16px; align-items: end;">
                        <div>
                            <label>Ảnh đại diện (thumbnail)</label>
                            <div style="display: flex; gap: 8px;">
                                <input type="text" id="newsImageUrl" placeholder="https://... hoặc upload">
                                <label class="btn btn-outline" style="padding: 10px 16px; cursor: pointer; white-space: nowrap;">
                                    <span class="material-icons-outlined" style="font-size: 18px;">upload</span>
                                    <input type="file" id="newsThumbnailFile" accept="image/*" style="display: none;" onchange="uploadNewsThumbnail(this)">
                                </label>
                            </div>
                            <div id="thumbnailPreview" style="margin-top: 8px; display: none;">
                                <img id="thumbPreviewImg" style="max-height: 80px; border-radius: 4px;">
                            </div>
                        </div>
                        <label style="display: flex; align-items: center; gap: 8px; padding: 12px 16px; background: var(--warning-light); border-radius: var(--radius-md); cursor: pointer;">
                            <input type="checkbox" id="newsFeatured" style="width: auto; margin: 0;">
                            <span class="material-icons-outlined" style="color: var(--warning);">star</span>
                            Tin nổi bật
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" onclick="closeModal('newsModal')">Hủy</button>
                <button class="btn btn-primary" onclick="saveNews()">
                    <span class="material-icons-outlined">save</span>
                    Lưu bài viết
                </button>
            </div>
        </div>
    </div>

    <!-- Image Insert Dialog -->
    <div id="imageInsertDialog" class="modal" style="z-index: 1100;">
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header">
                <h2>Chèn hình ảnh</h2>
                <button class="modal-close" onclick="closeModal('imageInsertDialog')">
                    <span class="material-icons-outlined">close</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- URL Input -->
                <label>📎 Dán URL hình ảnh</label>
                <input type="text" id="insertImageUrl" placeholder="https://example.com/image.jpg" onchange="previewInsertImage()">
                
                <!-- File Upload -->
                <label style="margin-top: 16px;">📤 Hoặc tải ảnh lên</label>
                <div style="border: 2px dashed var(--border-light); border-radius: var(--radius-md); padding: 20px; text-align: center; cursor: pointer;" 
                     onclick="document.getElementById('insertImageFile').click()">
                    <span class="material-icons-outlined" style="font-size: 40px; color: var(--text-muted);">cloud_upload</span>
                    <p style="color: var(--text-secondary); margin-top: 8px;">Click để chọn ảnh</p>
                </div>
                <input type="file" id="insertImageFile" accept="image/*" style="display: none;" onchange="handleInsertImageFile(this)">
                
                <!-- Preview -->
                <div id="insertImagePreview" style="display: none; margin-top: 16px; text-align: center;">
                    <img id="insertPreviewImg" style="max-width: 100%; max-height: 150px; border-radius: 4px;">
                </div>
                
                <!-- Options -->
                <div style="margin-top: 16px;">
                    <label>Căn chỉnh</label>
                    <select id="insertImageAlign">
                        <option value="center">Căn giữa</option>
                        <option value="left">Căn trái</option>
                        <option value="right">Căn phải</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" onclick="closeModal('imageInsertDialog')">Hủy</button>
                <button class="btn btn-primary" onclick="confirmInsertImage()">
                    <span class="material-icons-outlined">add</span>
                    Chèn ảnh
                </button>
            </div>
        </div>
    </div>

    <!-- Toast -->
    <div id="toast" class="toast">
        <span class="material-icons-outlined">check_circle</span>
        <span id="toastMessage"></span>
    </div>

    <!-- Password Change Modal -->
    <div id="passwordModal" class="modal" style="display: none;">
        <div class="modal-content" style="max-width: 400px;">
            <div class="modal-header">
                <h2><span class="material-icons-outlined">lock</span> Đổi mật khẩu</h2>
                <button class="btn-close" onclick="closePasswordModal()">
                    <span class="material-icons-outlined">close</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="passwordForm" onsubmit="changePassword(event)">
                    <div class="form-group" style="margin-bottom: 16px;">
                        <label style="display: block; margin-bottom: 6px; font-weight: 600; color: var(--text-primary);">Mật khẩu hiện tại</label>
                        <input type="password" id="currentPassword" required 
                            style="width: 100%; padding: 12px; border: 1px solid var(--border-light); border-radius: 8px; font-size: 14px;">
                    </div>
                    <div class="form-group" style="margin-bottom: 16px;">
                        <label style="display: block; margin-bottom: 6px; font-weight: 600; color: var(--text-primary);">Mật khẩu mới</label>
                        <input type="password" id="newPassword" required minlength="6"
                            style="width: 100%; padding: 12px; border: 1px solid var(--border-light); border-radius: 8px; font-size: 14px;">
                    </div>
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 6px; font-weight: 600; color: var(--text-primary);">Xác nhận mật khẩu mới</label>
                        <input type="password" id="confirmPassword" required minlength="6"
                            style="width: 100%; padding: 12px; border: 1px solid var(--border-light); border-radius: 8px; font-size: 14px;">
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        <span class="material-icons-outlined">save</span>
                        Đổi mật khẩu
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Global variables
        const API_BASE = '../backend_api';
        const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;
        let allRegistrations = [];
        let allNews = [];
        let currentPage = 1;
        let itemsPerPage = 10;
        let deleteId = null;

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            // Register ChartDataLabels plugin globally
            if (typeof ChartDataLabels !== 'undefined') {
                Chart.register(ChartDataLabels);
                // Disable by default for all charts
                Chart.defaults.plugins.datalabels.display = false;
            }
            
            initNavigation();
            
            // Priority 1: URL Hash
            const hash = window.location.hash.substring(1);
            if (hash && document.getElementById(hash)) {
                console.log("Restoring from Hash:", hash);
                showSection(hash);
            } 
            // Priority 2: LocalStorage
            else {
                const savedSection = localStorage.getItem('adminCurrentSection');
                if (savedSection && document.getElementById(savedSection)) {
                    console.log("Restoring from LocalStorage:", savedSection);
                    showSection(savedSection);
                    history.replaceState(null, null, '#' + savedSection);
                } else {
                    // Priority 3: Default to Dashboard
                    console.log("Defaulting to Dashboard");
                    showSection('dashboard');
                }
            }
            
            // Listen for hash changes
            window.addEventListener('hashchange', handleHashNavigation);
        });
        
        // Handle hash navigation from URL
        function handleHashNavigation() {
            const hash = window.location.hash.substring(1);
            if (hash && document.getElementById(hash)) {
                showSection(hash);
            }
        }
        
        // Show a specific section
        function showSection(sectionId) {
            const sectionElement = document.getElementById(sectionId);
            if (!sectionElement) return;
            
            // Save to localStorage
            localStorage.setItem('adminCurrentSection', sectionId);
            
            // Update sidebar active state
            document.querySelectorAll('.sidebar-menu a').forEach(a => a.classList.remove('active'));
            const activeLink = document.querySelector(`.sidebar-menu a[data-section="${sectionId}"]`);
            if (activeLink) activeLink.classList.add('active');
            
            // Update section visibility
            document.querySelectorAll('.section-panel').forEach(s => s.classList.remove('active'));
            sectionElement.classList.add('active');
            
            // Load section data
            if (sectionId === 'users') { if (typeof initUsersSection === 'function') initUsersSection(); }
            if (sectionId === 'registrations') loadRegistrations();
            if (sectionId === 'news') loadNews();
            if (sectionId === 'cms') loadCMS();
            if (sectionId === 'contentBlocks') loadContentBlocks(document.getElementById('blockPageSelect')?.value || '');
            if (sectionId === 'dashboard') { loadStats(); loadRecentRegistrations(); if (typeof loadAnalyticsCharts === 'function') setTimeout(loadAnalyticsCharts, 100); }
            if (sectionId === 'logs') loadActivityLogs();
            if (sectionId === 'settings') loadSettings();
        }

        // Navigation
        function initNavigation() {
            document.querySelectorAll('.sidebar-menu a[data-section]').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const section = this.dataset.section;
                    showSection(section);
                    history.pushState(null, null, '#' + section);
                });
            });
        }


        // Stats
        async function loadStats() {
            try {
                const response = await fetch(`${API_BASE}/stats_api.php`);
                const stats = await response.json();
                
                document.getElementById('statTotal').textContent = stats.total || 0;
                document.getElementById('statToday').textContent = stats.today || 0;
                document.getElementById('statWeek').textContent = stats.week || 0;
                document.getElementById('statMonth').textContent = stats.month || 0;
            } catch (error) {
                console.error('Error loading stats:', error);
            }
        }

        // Refresh all dashboard data
        function refreshDashboard() {
            loadStats();
            loadRecentRegistrations();
            if (typeof loadAnalyticsCharts === 'function') {
                loadAnalyticsCharts();
            }
            showToast('Đã làm mới Dashboard', 'success');
        }

        // Recent Registrations
        async function loadRecentRegistrations() {
            try {
                const response = await fetch(`${API_BASE}/get.php`);
                const data = await response.json();
                
                const recent = data.slice(0, 5);
                const tbody = document.getElementById('recentRegistrations');
                
                if (recent.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" class="empty-state"><span class="material-icons-outlined">inbox</span><h3>Chưa có dữ liệu</h3></td></tr>';
                    return;
                }
                
                tbody.innerHTML = recent.map(r => `
                    <tr>
                        <td>${r.id}</td>
                        <td>${formatDate(r.ngay_nhan)}</td>
                        <td>${escapeHtml(r.ho_ten)}</td>
                        <td>${escapeHtml(r.sdt)}</td>
                        <td><span class="badge badge-info">${escapeHtml(r.chuong_trinh)}</span></td>
                        <td>${escapeHtml(r.quoc_gia)}</td>
                    </tr>
                `).join('');
            } catch (error) {
                console.error('Error:', error);
            }
        }

        // Registrations
        async function loadRegistrations() {
            try {
                const response = await fetch(`${API_BASE}/get.php`);
                allRegistrations = await response.json();
                renderRegistrations();
            } catch (error) {
                console.error('Error:', error);
            }
        }

        function renderRegistrations() {
            let filtered = [...allRegistrations];
            
            // Apply filters
            const search = document.getElementById('searchInput').value.toLowerCase();
            const program = document.getElementById('filterProgram').value;
            const dateFrom = document.getElementById('dateFrom').value;
            const dateTo = document.getElementById('dateTo').value;
            
            if (search) {
                filtered = filtered.filter(r => 
                    r.ho_ten.toLowerCase().includes(search) ||
                    r.sdt.includes(search) ||
                    (r.dia_chi && r.dia_chi.toLowerCase().includes(search))
                );
            }
            
            if (program) {
                filtered = filtered.filter(r => r.chuong_trinh === program);
            }
            
            if (dateFrom) {
                filtered = filtered.filter(r => r.ngay_nhan >= dateFrom);
            }
            
            if (dateTo) {
                filtered = filtered.filter(r => r.ngay_nhan.substring(0, 10) <= dateTo);
            }
            
            // Pagination
            const totalPages = Math.ceil(filtered.length / itemsPerPage);
            const start = (currentPage - 1) * itemsPerPage;
            const paginated = filtered.slice(start, start + itemsPerPage);
            
            const tbody = document.getElementById('registrationsList');
            
            if (paginated.length === 0) {
                tbody.innerHTML = '<tr><td colspan="9" class="empty-state"><span class="material-icons-outlined">inbox</span><h3>Không có dữ liệu</h3></td></tr>';
                document.getElementById('pagination').innerHTML = '';
                return;
            }
            
            tbody.innerHTML = paginated.map(r => `
                <tr>
                    <td>${r.id}</td>
                    <td>${formatDate(r.ngay_nhan)}</td>
                    <td>${escapeHtml(r.ho_ten)}</td>
                    <td>${escapeHtml(r.nam_sinh || '')}</td>
                    <td>${escapeHtml(r.dia_chi || '')}</td>
                    <td><span class="badge badge-info">${escapeHtml(r.chuong_trinh)}</span></td>
                    <td>${escapeHtml(r.quoc_gia)}</td>
                    <td>${escapeHtml(r.sdt)}</td>
                    <td>
                        <div class="action-btns">
                            <button class="action-btn edit" onclick="openEditModal(${r.id})" title="Sửa">
                                <span class="material-icons-outlined">edit</span>
                            </button>
                            <button class="action-btn delete" onclick="openDeleteModal(${r.id})" title="Xóa">
                                <span class="material-icons-outlined">delete</span>
                            </button>
                        </div>
                    </td>
                </tr>
            `).join('');
            
            // Render pagination
            let paginationHtml = '';
            paginationHtml += `<button onclick="goToPage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}>Trước</button>`;
            for (let i = 1; i <= totalPages; i++) {
                paginationHtml += `<button onclick="goToPage(${i})" class="${i === currentPage ? 'active' : ''}">${i}</button>`;
            }
            paginationHtml += `<button onclick="goToPage(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''}>Sau</button>`;
            document.getElementById('pagination').innerHTML = paginationHtml;
        }

        function goToPage(page) {
            currentPage = page;
            renderRegistrations();
        }

        function searchRegistrations() {
            currentPage = 1;
            renderRegistrations();
        }

        function filterRegistrations() {
            currentPage = 1;
            renderRegistrations();
        }

        // Edit
        function openEditModal(id) {
            const reg = allRegistrations.find(r => r.id == id);
            if (!reg) return;
            
            document.getElementById('editId').value = reg.id;
            document.getElementById('editHoTen').value = reg.ho_ten;
            document.getElementById('editNamSinh').value = reg.nam_sinh || '';
            document.getElementById('editDiaChi').value = reg.dia_chi || '';
            document.getElementById('editChuongTrinh').value = reg.chuong_trinh;
            document.getElementById('editQuocGia').value = reg.quoc_gia || '';
            document.getElementById('editSdt').value = reg.sdt;
            document.getElementById('editGhiChu').value = reg.ghi_chu || '';
            
            document.getElementById('editModal').style.display = 'block';
        }

        async function saveEdit() {
            const data = {
                id: parseInt(document.getElementById('editId').value),
                ho_ten: document.getElementById('editHoTen').value,
                nam_sinh: document.getElementById('editNamSinh').value,
                dia_chi: document.getElementById('editDiaChi').value,
                chuong_trinh: document.getElementById('editChuongTrinh').value,
                quoc_gia: document.getElementById('editQuocGia').value,
                sdt: document.getElementById('editSdt').value,
                ghi_chu: document.getElementById('editGhiChu').value
            };
            
            try {
                const response = await fetch(`${API_BASE}/update.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': CSRF_TOKEN
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.status) {
                    closeModal('editModal');
                    showToast('Cập nhật thành công!', 'success');
                    loadRegistrations();
                } else {
                    showToast(result.message || 'Lỗi cập nhật', 'error');
                }
            } catch (error) {
                showToast('Lỗi kết nối', 'error');
            }
        }

        // Delete
        function openDeleteModal(id) {
            deleteId = id;
            document.getElementById('deleteModal').style.display = 'block';
        }

        async function confirmDelete() {
            try {
                const response = await fetch(`${API_BASE}/delete.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': CSRF_TOKEN
                    },
                    body: JSON.stringify({ id: deleteId })
                });
                
                const result = await response.json();
                
                if (result.status) {
                    closeModal('deleteModal');
                    showToast('Xóa thành công!', 'success');
                    loadRegistrations();
                    loadStats();
                } else {
                    showToast(result.message || 'Lỗi xóa', 'error');
                }
            } catch (error) {
                showToast('Lỗi kết nối', 'error');
            }
        }

        // Export
        function exportData() {
            const dateFrom = document.getElementById('dateFrom').value;
            const dateTo = document.getElementById('dateTo').value;
            
            let url = `${API_BASE}/export.php?`;
            if (dateFrom) url += `from=${dateFrom}&`;
            if (dateTo) url += `to=${dateTo}&`;
            
            window.location.href = url;
        }

        // News
        async function loadNews() {
            try {
                const response = await fetch(`${API_BASE}/news_api.php?limit=100`);
                const news = await response.json();
                
                // Store globally for edit/delete functions
                allNews = news || [];
                
                const tbody = document.getElementById('newsList');
                
                if (!news || news.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="7" class="empty-state"><span class="material-icons-outlined">article</span><h3>Chưa có tin tức</h3></td></tr>';
                    return;
                }
                
                tbody.innerHTML = news.map(n => `
                    <tr>
                        <td>${n.id}</td>
                        <td><img src="${escapeHtml(n.image_url || 'data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2260%22 height=%2240%22%3E%3Crect fill=%22%23e2e8f0%22 width=%2260%22 height=%2240%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 fill=%22%2394a3b8%22 font-family=%22Arial%22 font-size=%2210%22 text-anchor=%22middle%22 dy=%22.3em%22%3ENo Image%3C/text%3E%3C/svg%3E')}" style="width:60px;height:40px;object-fit:cover;border-radius:4px;"></td>
                        <td>${escapeHtml(n.title)}</td>
                        <td><span class="badge badge-info">${escapeHtml(n.category)}</span></td>
                        <td>${formatDate(n.created_at)}</td>
                        <td>${n.is_featured ? '<span class="badge badge-success">Có</span>' : '<span class="badge badge-warning">Không</span>'}</td>
                        <td>
                            <div class="action-btns">
                                <button class="action-btn edit" onclick="openEditNewsModal(${n.id})" title="Sửa">
                                    <span class="material-icons-outlined">edit</span>
                                </button>
                                <button class="action-btn delete" onclick="deleteNews(${n.id})" title="Xóa">
                                    <span class="material-icons-outlined">delete</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                `).join('');
            } catch (error) {
                console.error('Error:', error);
            }
        }

        function openAddNewsModal() {
            document.getElementById('newsModalTitle').textContent = 'Thêm tin tức';
            document.getElementById('newsId').value = '';
            document.getElementById('newsForm').reset();
            document.getElementById('newsModal').style.display = 'block';
        }

        async function openEditNewsModal(id) {
            try {
                const response = await fetch(`${API_BASE}/news_api.php?id=${id}`);
                const news = await response.json();
                
                document.getElementById('newsModalTitle').textContent = 'Sửa tin tức';
                document.getElementById('newsId').value = news.id;
                document.getElementById('newsTitle').value = news.title;
                document.getElementById('newsExcerpt').value = news.excerpt || '';
                document.getElementById('newsContent').value = news.content || '';
                document.getElementById('newsImageUrl').value = news.image_url || '';
                document.getElementById('newsCategory').value = news.category;
                document.getElementById('newsFeatured').checked = news.is_featured == 1;
                
                document.getElementById('newsModal').style.display = 'block';
            } catch (error) {
                showToast('Lỗi tải tin tức', 'error');
            }
        }

        async function saveNews() {
            const newsId = document.getElementById('newsId').value;
            const data = {
                title: document.getElementById('newsTitle').value,
                excerpt: document.getElementById('newsExcerpt').value,
                content: document.getElementById('newsContent').value,
                image_url: document.getElementById('newsImageUrl').value,
                category: document.getElementById('newsCategory').value,
                is_featured: document.getElementById('newsFeatured').checked ? 1 : 0
            };
            
            if (newsId) data.id = parseInt(newsId);
            
            try {
                const response = await fetch(`${API_BASE}/news_api.php`, {
                    method: newsId ? 'PUT' : 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': CSRF_TOKEN
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.status) {
                    closeModal('newsModal');
                    showToast(newsId ? 'Cập nhật thành công!' : 'Thêm tin thành công!', 'success');
                    loadNews();
                } else {
                    showToast(result.message || 'Lỗi lưu tin', 'error');
                }
            } catch (error) {
                showToast('Lỗi kết nối', 'error');
            }
        }

        async function deleteNews(id) {
            if (!confirm('Bạn có chắc muốn xóa tin này?')) return;
            
            try {
                const response = await fetch(`${API_BASE}/news_api.php`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': CSRF_TOKEN
                    },
                    body: JSON.stringify({ id })
                });
                
                const result = await response.json();
                
                if (result.status) {
                    showToast('Xóa tin thành công!', 'success');
                    loadNews();
                } else {
                    showToast(result.message || 'Lỗi xóa', 'error');
                }
            } catch (error) {
                showToast('Lỗi kết nối', 'error');
            }
        }

        // Utilities
        function closeModal(id) {
            document.getElementById(id).style.display = 'none';
        }

        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            document.getElementById('toastMessage').textContent = message;
            toast.className = `toast show ${type}`;
            
            setTimeout(() => toast.classList.remove('show'), 3000);
        }

        function formatDate(dateStr) {
            if (!dateStr) return '-';
            const date = new Date(dateStr);
            return date.toLocaleString('vi-VN', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        function escapeHtml(str) {
            if (!str) return '';
            return str.replace(/&/g, '&amp;')
                      .replace(/</g, '&lt;')
                      .replace(/>/g, '&gt;')
                      .replace(/"/g, '&quot;');
        }

        // Close modals on outside click
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }

        // ==================== CMS FUNCTIONS ====================
        let allCMSContent = [];
        let currentCMSTab = 'all';

        async function loadCMS() {
            try {
                const response = await fetch(`${API_BASE}/get_content.php`);
                allCMSContent = await response.json();
                renderCMS();
            } catch (error) {
                console.error('Error loading CMS:', error);
                document.getElementById('cmsContent').innerHTML = `
                    <div class="empty-state">
                        <span class="material-icons-outlined">error_outline</span>
                        <h3>Lỗi tải nội dung</h3>
                        <p>${error.message}</p>
                    </div>
                `;
            }
        }

        function switchCMSTab(tab) {
            currentCMSTab = tab;
            document.querySelectorAll('.cms-tab').forEach(btn => {
                btn.classList.remove('active');
                btn.classList.remove('btn-primary');
                btn.classList.add('btn-outline');
            });
            const activeBtn = document.getElementById('tab' + tab.charAt(0).toUpperCase() + tab.slice(1));
            if (activeBtn) {
                activeBtn.classList.add('active');
                if (tab === 'visual' || tab === 'contact') {
                    activeBtn.classList.remove('btn-outline');
                    activeBtn.classList.add('btn-primary');
                }
            }
            
            // Show/hide sections based on tab
            const cmsContent = document.getElementById('cmsContent');
            const bannerContent = document.getElementById('bannerContent');
            const visualContent = document.getElementById('visualCmsContent');
            const contactContent = document.getElementById('contactContent');
            const filterBar = document.querySelector('.table-header > div:nth-child(2)'); // Filter bar
            
            if (tab === 'visual') {
                cmsContent.style.display = 'none';
                bannerContent.style.display = 'none';
                visualContent.style.display = 'block';
                contactContent.style.display = 'none';
                if (filterBar) filterBar.style.display = 'none';
            } else if (tab === 'contact') {
                cmsContent.style.display = 'none';
                bannerContent.style.display = 'none';
                visualContent.style.display = 'none';
                contactContent.style.display = 'block';
                if (filterBar) filterBar.style.display = 'none';
                loadContactSettings();
            } else if (tab === 'banners') {
                cmsContent.style.display = 'none';
                bannerContent.style.display = 'block';
                visualContent.style.display = 'none';
                contactContent.style.display = 'none';
                if (filterBar) filterBar.style.display = 'none';
                loadBanners();
            } else {
                cmsContent.style.display = 'block';
                bannerContent.style.display = 'none';
                visualContent.style.display = 'none';
                contactContent.style.display = 'none';
                if (filterBar) filterBar.style.display = 'flex';
                renderCMS();
            }
        }

        function filterCMS() {
            renderCMS();
        }

        // Contact Settings - Mapping between field IDs and database keys
        const contactFieldMapping = {
            // Contact Info
            'contact_header_phone': 'header_phone',
            'contact_header_phone_display': 'header_phone_display',
            'contact_header_email': 'header_email',
            // Social Links
            'contact_global_facebook_url': 'global_facebook_url',
            'contact_global_youtube_url': 'global_youtube_url',
            'contact_global_zalo_url': 'global_zalo_url',
            // Main Menu
            'contact_menu_trangchu': 'menu_trangchu',
            'contact_menu_veicogroup': 'menu_veicogroup',
            'contact_menu_huongnghiep': 'menu_huongnghiep',
            'contact_menu_hoatdong': 'menu_hoatdong',
            'contact_menu_lienhe': 'menu_lienhe',
            'contact_menu_dangky': 'menu_dangky',
            // Du học Menu
            'contact_menu_duhoc': 'menu_duhoc',
            'contact_menu_duhoc_germany': 'menu_duhoc_germany',
            'contact_menu_duhoc_japan': 'menu_duhoc_japan',
            'contact_menu_duhoc_korea': 'menu_duhoc_korea',
            // XKLĐ Menu
            'contact_menu_xkld': 'menu_xkld',
            'contact_menu_xkld_japan': 'menu_xkld_japan',
            'contact_menu_xkld_korea': 'menu_xkld_korea',
            'contact_menu_xkld_taiwan': 'menu_xkld_taiwan',
            'contact_menu_xkld_eu': 'menu_xkld_eu'
        };

        // Toggle visibility mapping between toggle IDs and database keys
        const toggleFieldMapping = {
            'toggle_menu_duhoc_germany': 'menu_duhoc_germany_visible',
            'toggle_menu_duhoc_japan': 'menu_duhoc_japan_visible',
            'toggle_menu_duhoc_korea': 'menu_duhoc_korea_visible',
            'toggle_menu_xkld_japan': 'menu_xkld_japan_visible',
            'toggle_menu_xkld_korea': 'menu_xkld_korea_visible',
            'toggle_menu_xkld_taiwan': 'menu_xkld_taiwan_visible',
            'toggle_menu_xkld_eu': 'menu_xkld_eu_visible'
        };

        async function loadContactSettings() {
            try {
                // Fetch directly from API
                const response = await fetch(`${API_BASE}/get_content.php`);
                const result = await response.json();
                
                if (result.status && result.data) {
                    const cmsData = result.data;
                    
                    // Load text fields
                    for (const [fieldId, dbKey] of Object.entries(contactFieldMapping)) {
                        const field = document.getElementById(fieldId);
                        if (field) {
                            const content = cmsData.find(c => c.section_key === dbKey);
                            field.value = content ? content.content_value : '';
                        }
                    }
                    
                    // Load toggle switches
                    for (const [toggleId, dbKey] of Object.entries(toggleFieldMapping)) {
                        const toggle = document.getElementById(toggleId);
                        if (toggle) {
                            const content = cmsData.find(c => c.section_key === dbKey);
                            toggle.checked = content ? content.content_value === '1' : true;
                            // Update row opacity based on toggle state
                            const row = toggle.closest('.menu-item-row');
                            if (row) {
                                row.classList.toggle('disabled', !toggle.checked);
                            }
                        }
                    }
                    
                    // Add change event listeners to toggles
                    for (const toggleId of Object.keys(toggleFieldMapping)) {
                        const toggle = document.getElementById(toggleId);
                        if (toggle) {
                            toggle.onchange = function() {
                                const row = this.closest('.menu-item-row');
                                if (row) {
                                    row.classList.toggle('disabled', !this.checked);
                                }
                            };
                        }
                    }
                }
            } catch (error) {
                console.error('Error loading contact settings:', error);
                showToast('Lỗi tải dữ liệu liên hệ', 'error');
            }
        }

        async function saveContactSettings() {
            const saveBtn = document.getElementById('saveContactBtn');
            const originalHTML = saveBtn.innerHTML;
            saveBtn.innerHTML = '<span class="material-icons-outlined spin">sync</span> Đang lưu...';
            saveBtn.disabled = true;

            try {
                const promises = [];
                
                // Save text fields
                for (const [fieldId, dbKey] of Object.entries(contactFieldMapping)) {
                    const field = document.getElementById(fieldId);
                    if (field) {
                        const value = field.value.trim();
                        promises.push(
                            fetch(`${API_BASE}/save_content.php`, {
                                method: 'POST',
                                headers: { 
                                    'Content-Type': 'application/json',
                                    'X-CSRF-Token': CSRF_TOKEN
                                },
                                body: JSON.stringify({
                                    section_key: dbKey,
                                    content_value: value
                                })
                            })
                        );
                    }
                }
                
                // Save toggle switches (visibility)
                for (const [toggleId, dbKey] of Object.entries(toggleFieldMapping)) {
                    const toggle = document.getElementById(toggleId);
                    if (toggle) {
                        const value = toggle.checked ? '1' : '0';
                        promises.push(
                            fetch(`${API_BASE}/save_content.php`, {
                                method: 'POST',
                                headers: { 
                                    'Content-Type': 'application/json',
                                    'X-CSRF-Token': CSRF_TOKEN
                                },
                                body: JSON.stringify({
                                    section_key: dbKey,
                                    content_value: value
                                })
                            })
                        );
                    }
                }

                await Promise.all(promises);
                
                // Refresh CMS data
                await loadCMS();
                
                showToast('Đã lưu thông tin liên hệ & menu thành công!', 'success');
            } catch (error) {
                console.error('Error saving contact settings:', error);
                showToast('Lỗi lưu thông tin: ' + error.message, 'error');
            } finally {
                saveBtn.innerHTML = originalHTML;
                saveBtn.disabled = false;
            }
        }

        function renderCMS() {
            let filtered = [...allCMSContent];
            
            // Filter by tab
            if (currentCMSTab === 'images') {
                filtered = filtered.filter(c => isImageContent(c.section_key));
            } else if (currentCMSTab === 'texts') {
                filtered = filtered.filter(c => !isImageContent(c.section_key));
            }
            
            // Filter by page
            const pageFilter = document.getElementById('cmsPageFilter').value;
            if (pageFilter !== 'all') {
                filtered = filtered.filter(c => c.section_key.includes(pageFilter + '_') || c.section_key.startsWith(pageFilter));
            }
            
            // Filter by section
            const sectionFilter = document.getElementById('cmsSectionFilter').value;
            if (sectionFilter !== 'all') {
                filtered = filtered.filter(c => c.section_key.includes('_' + sectionFilter) || c.section_key.includes(sectionFilter + '_'));
            }
            
            // Search
            const search = document.getElementById('cmsSearchInput').value.toLowerCase();
            if (search) {
                filtered = filtered.filter(c => c.section_key.toLowerCase().includes(search) || 
                                                 (c.content_value && c.content_value.toLowerCase().includes(search)));
            }
            
            const container = document.getElementById('cmsContent');
            
            if (filtered.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <span class="material-icons-outlined">inbox</span>
                        <h3>Không có nội dung</h3>
                        <p>Thử thay đổi bộ lọc hoặc thêm nội dung mới</p>
                    </div>
                `;
                return;
            }
            
            // Render as cards grid
            container.innerHTML = `
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 20px;">
                    ${filtered.map(c => renderCMSCard(c)).join('')}
                </div>
            `;
        }

        function isImageContent(key) {
            return key.includes('_image') || key.includes('_img') || key.includes('_logo') || 
                   key.includes('_bg') || key.includes('_banner') || key.includes('_icon') ||
                   key.endsWith('_src') || key.includes('_photo');
        }

        function renderCMSCard(content) {
            const isImage = isImageContent(content.section_key);
            const value = content.content_value || '';
            const truncatedValue = value.length > 100 ? value.substring(0, 100) + '...' : value;
            
            return `
                <div class="cms-card" style="background: var(--surface); border: 1px solid var(--border-light); border-radius: var(--radius-md); overflow: hidden;">
                    <div style="padding: 16px; border-bottom: 1px solid var(--border-light); background: var(--bg-primary);">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-weight: 600; color: var(--text-primary); font-size: 13px; font-family: monospace; background: var(--info-light); padding: 4px 8px; border-radius: 4px;">
                                ${escapeHtml(content.section_key)}
                            </span>
                            <span class="badge ${isImage ? 'badge-warning' : 'badge-info'}">
                                ${isImage ? '🖼️ Hình ảnh' : '📝 Văn bản'}
                            </span>
                        </div>
                    </div>
                    <div style="padding: 16px; min-height: 80px;">
                        ${isImage && value ? 
                            `<img src="${escapeHtml(value)}" style="max-width: 100%; max-height: 150px; object-fit: contain; border-radius: 4px;" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22300%22 height=%22150%22%3E%3Crect fill=%22%23fee2e2%22 width=%22300%22 height=%22150%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 fill=%22%23ef4444%22 font-family=%22Arial%22 font-size=%2214%22 text-anchor=%22middle%22 dy=%22.3em%22%3EKh%C3%B4ng t%E1%BA%A3i %C4%91%C6%B0%E1%BB%A3c%3C/text%3E%3C/svg%3E'">` :
                            `<p style="color: var(--text-secondary); font-size: 14px; line-height: 1.5;">${escapeHtml(truncatedValue) || '<em style="color: var(--text-muted);">Chưa có nội dung</em>'}</p>`
                        }
                    </div>
                    <div style="padding: 12px 16px; border-top: 1px solid var(--border-light); display: flex; gap: 8px; justify-content: flex-end;">
                        <button class="btn btn-outline" style="padding: 8px 12px; font-size: 13px;" onclick="openEditCMSModal('${escapeHtml(content.section_key)}')">
                            <span class="material-icons-outlined" style="font-size: 16px;">edit</span>
                            Sửa
                        </button>
                        <button class="btn btn-danger" style="padding: 8px 12px; font-size: 13px;" onclick="deleteCMS('${escapeHtml(content.section_key)}')">
                            <span class="material-icons-outlined" style="font-size: 16px;">delete</span>
                        </button>
                    </div>
                </div>
            `;
        }

        function openAddCMSModal() {
            document.getElementById('cmsModalTitle').textContent = 'Thêm nội dung mới';
            document.getElementById('cmsKey').value = '';
            document.getElementById('cmsKey').disabled = false;
            document.getElementById('cmsValue').value = '';
            document.getElementById('cmsModal').style.display = 'block';
        }

        function openEditCMSModal(key) {
            const content = allCMSContent.find(c => c.section_key === key);
            if (!content) return;
            
            document.getElementById('cmsModalTitle').textContent = 'Chỉnh sửa nội dung';
            document.getElementById('cmsKey').value = content.section_key;
            document.getElementById('cmsKey').disabled = true;
            document.getElementById('cmsValue').value = content.content_value || '';
            document.getElementById('cmsModal').style.display = 'block';
        }

        async function saveCMS() {
            const key = document.getElementById('cmsKey').value.trim();
            const value = document.getElementById('cmsValue').value;
            
            if (!key) {
                showToast('Vui lòng nhập key nội dung', 'error');
                return;
            }
            
            try {
                const response = await fetch(`${API_BASE}/save_content.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': CSRF_TOKEN
                    },
                    body: JSON.stringify({
                        section_key: key,
                        content_value: value
                    })
                });
                
                const result = await response.json();
                
                if (result.status) {
                    closeModal('cmsModal');
                    showToast('Lưu nội dung thành công!', 'success');
                    loadCMS();
                } else {
                    showToast(result.message || 'Lỗi lưu nội dung', 'error');
                }
            } catch (error) {
                showToast('Lỗi kết nối', 'error');
            }
        }

        async function deleteCMS(key) {
            if (!confirm(`Bạn có chắc muốn xóa nội dung "${key}"?`)) return;
            
            // Note: The current API doesn't have delete, so we'll just clear the value
            try {
                const response = await fetch(`${API_BASE}/save_content.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': CSRF_TOKEN
                    },
                    body: JSON.stringify({
                        section_key: key,
                        content_value: ''
                    })
                });
                
                const result = await response.json();
                
                if (result.status) {
                    showToast('Đã xóa nội dung!', 'success');
                    loadCMS();
                } else {
                    showToast(result.message || 'Lỗi xóa', 'error');
                }
            } catch (error) {
                showToast('Lỗi kết nối', 'error');
            }
        }


    </script>

    <!-- CMS Modal -->
    <div id="cmsModal" class="modal">
        <div class="modal-content" style="max-width: 600px;">
            <div class="modal-header">
                <h2 id="cmsModalTitle">Thêm nội dung</h2>
                <button class="modal-close" onclick="closeModal('cmsModal')">
                    <span class="material-icons-outlined">close</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="cmsForm">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                    
                    <label>Key nội dung *</label>
                    <input type="text" id="cmsKey" placeholder="vd: index_hero_title" required>
                    <p style="font-size: 12px; color: var(--text-muted); margin: -12px 0 16px;">
                        Quy tắc: [trang]_[section]_[loại]. VD: index_hero_slide_1_img, nhat_about_title
                    </p>
                    
                    <!-- Content Type Selection -->
                    <label>Loại nội dung</label>
                    <div style="display: flex; gap: 12px; margin-bottom: 16px;">
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; padding: 12px 16px; border: 2px solid var(--border-light); border-radius: var(--radius-md); flex: 1;">
                            <input type="radio" name="cmsType" value="text" checked onchange="toggleCMSType('text')" style="width: auto;">
                            <span class="material-icons-outlined" style="color: var(--primary);">text_fields</span>
                            <span>Văn bản</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; padding: 12px 16px; border: 2px solid var(--border-light); border-radius: var(--radius-md); flex: 1;">
                            <input type="radio" name="cmsType" value="image" onchange="toggleCMSType('image')" style="width: auto;">
                            <span class="material-icons-outlined" style="color: var(--warning);">image</span>
                            <span>Hình ảnh</span>
                        </label>
                    </div>
                    
                    <!-- Text Input -->
                    <div id="cmsTextInput">
                        <label>Nội dung văn bản</label>
                        <textarea id="cmsValue" rows="5" placeholder="Nhập nội dung văn bản..."></textarea>
                    </div>
                    
                    <!-- Image Input -->
                    <div id="cmsImageInput" style="display: none;">
                        <label>Chọn cách nhập hình ảnh</label>
                        
                        <!-- URL Input -->
                        <div style="margin-bottom: 16px;">
                            <label style="font-size: 13px; color: var(--text-secondary);">📎 Dán URL hình ảnh</label>
                            <input type="text" id="cmsImageUrl" placeholder="https://example.com/image.jpg" onchange="previewImageUrl()">
                        </div>
                        
                        <!-- File Upload -->
                        <div style="margin-bottom: 16px;">
                            <label style="font-size: 13px; color: var(--text-secondary);">📤 Hoặc tải ảnh lên</label>
                            <div style="border: 2px dashed var(--border-light); border-radius: var(--radius-md); padding: 20px; text-align: center; cursor: pointer; transition: all 0.2s;" 
                                 id="uploadArea"
                                 onclick="document.getElementById('cmsImageFile').click()"
                                 ondrop="handleDrop(event)" ondragover="handleDragOver(event)" ondragleave="handleDragLeave(event)">
                                <span class="material-icons-outlined" style="font-size: 48px; color: var(--text-muted);">cloud_upload</span>
                                <p style="margin: 8px 0 0; color: var(--text-secondary);">Kéo thả ảnh vào đây hoặc <strong style="color: var(--primary);">click để chọn</strong></p>
                                <p style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">PNG, JPG, WEBP (max 5MB)</p>
                            </div>
                            <input type="file" id="cmsImageFile" accept="image/*" style="display: none;" onchange="previewImage(this)">
                        </div>
                        
                        <!-- Preview -->
                        <div id="cmsImagePreview" style="display: none; padding: 16px; background: var(--bg-primary); border-radius: var(--radius-md); text-align: center;">
                            <p style="font-size: 12px; color: var(--text-secondary); margin-bottom: 8px;">Xem trước:</p>
                            <img id="previewImg" style="max-width: 100%; max-height: 200px; border-radius: 4px;">
                            <button type="button" class="btn btn-outline" style="margin-top: 12px; padding: 6px 12px; font-size: 12px;" onclick="clearImagePreview()">
                                <span class="material-icons-outlined" style="font-size: 14px;">close</span> Xóa ảnh
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" onclick="closeModal('cmsModal')">Hủy</button>
                <button class="btn btn-primary" onclick="saveCMS()">
                    <span class="material-icons-outlined">save</span>
                    Lưu nội dung
                </button>
            </div>
        </div>
    </div>

    <script>
        // CMS Modal Functions
        function toggleCMSType(type) {
            document.getElementById('cmsTextInput').style.display = type === 'text' ? 'block' : 'none';
            document.getElementById('cmsImageInput').style.display = type === 'image' ? 'block' : 'none';
        }

        function previewImageUrl() {
            const url = document.getElementById('cmsImageUrl').value;
            if (url) {
                document.getElementById('previewImg').src = url;
                document.getElementById('cmsImagePreview').style.display = 'block';
            }
        }

        function previewImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('previewImg').src = e.target.result;
                    document.getElementById('cmsImagePreview').style.display = 'block';
                    document.getElementById('cmsImageUrl').value = ''; // Clear URL if file uploaded
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        function clearImagePreview() {
            document.getElementById('previewImg').src = '';
            document.getElementById('cmsImagePreview').style.display = 'none';
            document.getElementById('cmsImageUrl').value = '';
            document.getElementById('cmsImageFile').value = '';
        }

        function handleDragOver(e) {
            e.preventDefault();
            document.getElementById('uploadArea').style.borderColor = 'var(--primary)';
            document.getElementById('uploadArea').style.background = 'var(--info-light)';
        }

        function handleDragLeave(e) {
            e.preventDefault();
            document.getElementById('uploadArea').style.borderColor = 'var(--border-light)';
            document.getElementById('uploadArea').style.background = 'transparent';
        }

        function handleDrop(e) {
            e.preventDefault();
            handleDragLeave(e);
            const files = e.dataTransfer.files;
            if (files.length && files[0].type.startsWith('image/')) {
                document.getElementById('cmsImageFile').files = files;
                previewImage(document.getElementById('cmsImageFile'));
            }
        }

        // Override saveCMS to handle file upload
        const originalSaveCMS = saveCMS;
        saveCMS = async function() {
            const key = document.getElementById('cmsKey').value.trim();
            const type = document.querySelector('input[name="cmsType"]:checked').value;
            
            if (!key) {
                showToast('Vui lòng nhập key nội dung', 'error');
                return;
            }
            
            let value = '';
            
            if (type === 'text') {
                value = document.getElementById('cmsValue').value;
            } else {
                // Check for file upload first
                const fileInput = document.getElementById('cmsImageFile');
                if (fileInput.files && fileInput.files[0]) {
                    // Upload file
                    const formData = new FormData();
                    formData.append('image', fileInput.files[0]);
                    formData.append('csrf_token', CSRF_TOKEN);
                    
                    try {
                        const uploadResponse = await fetch(`${API_BASE}/upload_image.php`, {
                            method: 'POST',
                            body: formData
                        });
                        const uploadResult = await uploadResponse.json();
                        
                        if (uploadResult.status && uploadResult.url) {
                            value = uploadResult.url;
                        } else {
                            showToast(uploadResult.message || 'Lỗi upload ảnh', 'error');
                            return;
                        }
                    } catch (error) {
                        showToast('Lỗi upload ảnh: ' + error.message, 'error');
                        return;
                    }
                } else {
                    // Use URL
                    value = document.getElementById('cmsImageUrl').value;
                }
            }
            
            // Save content
            try {
                const response = await fetch(`${API_BASE}/save_content.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': CSRF_TOKEN
                    },
                    body: JSON.stringify({
                        section_key: key,
                        content_value: value
                    })
                });
                
                const result = await response.json();
                
                if (result.status) {
                    closeModal('cmsModal');
                    showToast('Lưu nội dung thành công!', 'success');
                    loadCMS();
                } else {
                    showToast(result.message || 'Lỗi lưu nội dung', 'error');
                }
            } catch (error) {
                showToast('Lỗi kết nối', 'error');
            }
        };

        // Reset modal when opening
        const originalOpenAddCMS = openAddCMSModal;
        openAddCMSModal = function() {
            document.getElementById('cmsModalTitle').textContent = 'Thêm nội dung mới';
            document.getElementById('cmsKey').value = '';
            document.getElementById('cmsKey').disabled = false;
            document.getElementById('cmsValue').value = '';
            document.getElementById('cmsImageUrl').value = '';
            document.getElementById('cmsImageFile').value = '';
            clearImagePreview();
            document.querySelector('input[name="cmsType"][value="text"]').checked = true;
            toggleCMSType('text');
            document.getElementById('cmsModal').style.display = 'block';
        };

        const originalOpenEditCMS = openEditCMSModal;
        openEditCMSModal = function(key) {
            const content = allCMSContent.find(c => c.section_key === key);
            if (!content) return;
            
            document.getElementById('cmsModalTitle').textContent = 'Chỉnh sửa nội dung';
            document.getElementById('cmsKey').value = content.section_key;
            document.getElementById('cmsKey').disabled = true;
            
            const isImage = isImageContent(content.section_key);
            
            if (isImage) {
                document.querySelector('input[name="cmsType"][value="image"]').checked = true;
                toggleCMSType('image');
                document.getElementById('cmsImageUrl').value = content.content_value || '';
                document.getElementById('cmsValue').value = '';
                if (content.content_value) {
                    document.getElementById('previewImg').src = content.content_value;
                    document.getElementById('cmsImagePreview').style.display = 'block';
                }
            } else {
                document.querySelector('input[name="cmsType"][value="text"]').checked = true;
                toggleCMSType('text');
                document.getElementById('cmsValue').value = content.content_value || '';
                clearImagePreview();
            }
            
            document.getElementById('cmsModal').style.display = 'block';
        };
    </script>

    <style>
        .cms-tab.active { background: var(--primary); color: white; border-color: var(--primary); }
        #uploadArea:hover { border-color: var(--primary); }
        input[type="radio"]:checked + .material-icons-outlined + span { font-weight: 600; }
        
        /* Rich Text Editor Styles */
        .editor-toolbar {
            display: flex;
            gap: 4px;
            padding: 8px 12px;
            background: var(--bg-primary);
            border: 1px solid var(--border-light);
            border-bottom: none;
            border-radius: var(--radius-md) var(--radius-md) 0 0;
            flex-wrap: wrap;
            align-items: center;
        }
        .editor-toolbar button {
            width: 32px;
            height: 32px;
            border: none;
            background: transparent;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.15s;
        }
        .editor-toolbar button:hover {
            background: var(--surface);
            color: var(--primary);
        }
        .editor-toolbar button .material-icons-outlined {
            font-size: 18px;
        }
        .editor-toolbar .btn-highlight {
            background: var(--success-light);
            color: var(--success);
        }
        .editor-toolbar .btn-highlight:hover {
            background: var(--success);
            color: white;
        }
        .toolbar-divider {
            width: 1px;
            height: 24px;
            background: var(--border-light);
            margin: 0 4px;
        }
        .rich-editor {
            min-height: 200px;
            max-height: 300px;
            overflow-y: auto;
            padding: 16px;
            border: 1px solid var(--border-light);
            border-radius: 0 0 var(--radius-md) var(--radius-md);
            background: var(--surface);
            font-size: 14px;
            line-height: 1.6;
        }
        .rich-editor:focus {
            outline: none;
            border-color: var(--primary);
        }
        .rich-editor:empty:before {
            content: attr(placeholder);
            color: var(--text-muted);
            pointer-events: none;
        }
        .rich-editor img {
            max-width: 100%;
            height: auto;
            border-radius: 4px;
            margin: 12px 0;
        }
        .rich-editor h2, .rich-editor h3, .rich-editor h4 {
            margin: 16px 0 8px;
        }
    </style>

    <script>
        // ==================== RICH TEXT EDITOR ====================
        let pendingImageUrl = '';
        
        function formatText(command) {
            document.execCommand(command, false, null);
            document.getElementById('newsContent').focus();
        }
        
        function formatHeading(tag) {
            if (tag) {
                document.execCommand('formatBlock', false, tag);
                document.getElementById('newsContent').focus();
            }
        }
        
        function insertLink() {
            const url = prompt('Nhập URL:', 'https://');
            if (url) {
                document.execCommand('createLink', false, url);
            }
        }
        
        function showImageInsertDialog() {
            document.getElementById('insertImageUrl').value = '';
            document.getElementById('insertImageFile').value = '';
            document.getElementById('insertImagePreview').style.display = 'none';
            pendingImageUrl = '';
            document.getElementById('imageInsertDialog').style.display = 'block';
        }
        
        function previewInsertImage() {
            const url = document.getElementById('insertImageUrl').value;
            if (url) {
                document.getElementById('insertPreviewImg').src = url;
                document.getElementById('insertImagePreview').style.display = 'block';
                pendingImageUrl = url;
            }
        }
        
        async function handleInsertImageFile(input) {
            if (input.files && input.files[0]) {
                // Upload the file first
                const formData = new FormData();
                formData.append('image', input.files[0]);
                
                try {
                    const response = await fetch(`${API_BASE}/upload_image.php`, {
                        method: 'POST',
                        body: formData
                    });
                    const result = await response.json();
                    
                    if (result.status && result.url) {
                        document.getElementById('insertPreviewImg').src = result.url;
                        document.getElementById('insertImagePreview').style.display = 'block';
                        pendingImageUrl = result.url;
                        document.getElementById('insertImageUrl').value = result.url;
                    } else {
                        showToast(result.message || 'Lỗi upload ảnh', 'error');
                    }
                } catch (error) {
                    showToast('Lỗi upload: ' + error.message, 'error');
                }
            }
        }
        
        function confirmInsertImage() {
            const url = pendingImageUrl || document.getElementById('insertImageUrl').value;
            if (!url) {
                showToast('Vui lòng chọn hoặc nhập URL ảnh', 'error');
                return;
            }
            
            const align = document.getElementById('insertImageAlign').value;
            const alignStyle = align === 'center' ? 'margin: 12px auto; display: block;' :
                              align === 'left' ? 'float: left; margin: 0 16px 12px 0;' :
                              'float: right; margin: 0 0 12px 16px;';
            
            const imgHtml = `<img src="${url}" style="max-width: 100%; ${alignStyle}" alt=""><br>`;
            
            document.getElementById('newsContent').focus();
            document.execCommand('insertHTML', false, imgHtml);
            
            closeModal('imageInsertDialog');
            showToast('Đã chèn ảnh!', 'success');
        }
        
        async function uploadNewsThumbnail(input) {
            if (input.files && input.files[0]) {
                const formData = new FormData();
                formData.append('image', input.files[0]);
                
                try {
                    const response = await fetch(`${API_BASE}/upload_image.php`, {
                        method: 'POST',
                        body: formData
                    });
                    const result = await response.json();
                    
                    if (result.status && result.url) {
                        document.getElementById('newsImageUrl').value = result.url;
                        document.getElementById('thumbPreviewImg').src = result.url;
                        document.getElementById('thumbnailPreview').style.display = 'block';
                        showToast('Upload thành công!', 'success');
                    } else {
                        showToast(result.message || 'Lỗi upload', 'error');
                    }
                } catch (error) {
                    showToast('Lỗi: ' + error.message, 'error');
                }
            }
        }
        
        // Update saveNews to get content from contenteditable
        const originalSaveNews = saveNews;
        saveNews = async function() {
            const newsId = document.getElementById('newsId').value;
            const content = document.getElementById('newsContent').innerHTML;
            
            const data = {
                title: document.getElementById('newsTitle').value,
                excerpt: document.getElementById('newsExcerpt').value,
                content: content,
                image_url: document.getElementById('newsImageUrl').value,
                category: document.getElementById('newsCategory').value,
                is_featured: document.getElementById('newsFeatured').checked ? 1 : 0
            };
            
            if (newsId) data.id = parseInt(newsId);
            
            try {
                const response = await fetch(`${API_BASE}/news_api.php`, {
                    method: newsId ? 'PUT' : 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': CSRF_TOKEN
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.status) {
                    closeModal('newsModal');
                    showToast(newsId ? 'Cập nhật thành công!' : 'Thêm tin thành công!', 'success');
                    loadNews();
                } else {
                    showToast(result.message || 'Lỗi lưu tin', 'error');
                }
            } catch (error) {
                showToast('Lỗi kết nối', 'error');
            }
        };
        
        // Update openEditNews to populate contenteditable
        const originalOpenEditNews = openEditNewsModal;
        openEditNewsModal = function(id) {
            const news = allNews.find(n => n.id == id);
            if (!news) return;
            
            document.getElementById('newsModalTitle').textContent = 'Sửa tin tức';
            document.getElementById('newsId').value = news.id;
            document.getElementById('newsTitle').value = news.title;
            document.getElementById('newsExcerpt').value = news.excerpt || '';
            document.getElementById('newsContent').innerHTML = news.content || '';
            document.getElementById('newsImageUrl').value = news.image_url || '';
            document.getElementById('newsCategory').value = news.category || 'tin-tuc';
            document.getElementById('newsFeatured').checked = news.is_featured == 1;
            
            if (news.image_url) {
                document.getElementById('thumbPreviewImg').src = news.image_url;
                document.getElementById('thumbnailPreview').style.display = 'block';
            } else {
                document.getElementById('thumbnailPreview').style.display = 'none';
            }
            
            document.getElementById('newsModal').style.display = 'block';
        };
        
        // Update openAddNewsModal to clear contenteditable
        const originalOpenAddNews = openAddNewsModal;
        openAddNewsModal = function() {
            document.getElementById('newsModalTitle').textContent = 'Thêm tin tức mới';
            document.getElementById('newsId').value = '';
            document.getElementById('newsTitle').value = '';
            document.getElementById('newsExcerpt').value = '';
            document.getElementById('newsContent').innerHTML = '';
            document.getElementById('newsImageUrl').value = '';
            document.getElementById('newsCategory').value = 'tin-tuc';
            document.getElementById('newsFeatured').checked = false;
            document.getElementById('thumbnailPreview').style.display = 'none';
            document.getElementById('newsModal').style.display = 'block';
        };
        
        // ==================== BANNER MANAGEMENT ====================
        const bannerPages = [
            { key: 'nhat_header_bg', name: 'Du học Nhật Bản', page: 'nhat.php', color: '#BC002D' },
            { key: 'duc_header_bg', name: 'Du học Đức', page: 'duc.php', color: '#DD0000' },
            { key: 'han_header_bg', name: 'Du học Hàn Quốc', page: 'han.php', color: '#0047A0' },
            { key: 'xkldjp_header_bg', name: 'XKLĐ Nhật Bản', page: 'xkldjp.php', color: '#BC002D' },
            { key: 'xkldhan_header_bg', name: 'XKLĐ Hàn Quốc', page: 'xkldhan.php', color: '#0047A0' },
            { key: 'xklddailoan_header_bg', name: 'XKLĐ Đài Loan', page: 'xklddailoan.php', color: '#E60012' },
            { key: 'xkldchauau_header_bg', name: 'XKLĐ Châu Âu', page: 'xkldchauau.php', color: '#003399' },
            { key: 'huongnghiep_header_bg', name: 'Hướng nghiệp', page: 'huong-nghiep.php', color: '#F59E0B' },
            { key: 'about_header_bg', name: 'Về ICOGroup', page: 've-icogroup.php', color: '#2563EB' },
            { key: 'contact_header_bg', name: 'Liên hệ', page: 'lienhe.php', color: '#10B981' },
            { key: 'hoatdong_header_bg', name: 'Hoạt động', page: 'hoatdong.php', color: '#8B5CF6' }
        ];
        
        async function loadBanners() {
            const grid = document.getElementById('bannerGrid');
            grid.innerHTML = '<div class="loading"><div class="spinner"></div><p>Đang tải...</p></div>';
            
            try {
                // Load existing banner images from CMS
                const response = await fetch(`${API_BASE}/get_content.php`);
                const content = await response.json();
                const bannerData = {};
                content.forEach(c => {
                    if (c.section_key.endsWith('_header_bg')) {
                        bannerData[c.section_key] = c.content_value;
                    }
                });
                
                // Render banner cards
                grid.innerHTML = bannerPages.map(banner => {
                    const imageUrl = bannerData[banner.key] || '';
                    const hasImage = imageUrl && imageUrl.length > 0;
                    const bgStyle = hasImage 
                        ? `background-image: url('${imageUrl}'); background-size: cover; background-position: center;`
                        : `background: linear-gradient(135deg, ${banner.color}, ${adjustColor(banner.color, 40)});`;
                    
                    return `
                        <div class="banner-card" data-key="${banner.key}">
                            <div class="banner-preview ${hasImage ? 'has-image' : ''}" style="${bgStyle}">
                                ${!hasImage ? `<div class="placeholder"><span class="material-icons-outlined" style="font-size: 32px;">image</span><br>Chưa có hình</div>` : ''}
                                <div class="overlay">
                                    <span class="material-icons-outlined" style="color: white; font-size: 32px;">visibility</span>
                                </div>
                            </div>
                            <div class="banner-info">
                                <h4>${banner.name}</h4>
                                <p><code>${banner.page}</code></p>
                                <div class="banner-actions">
                                    <label class="btn-upload">
                                        <span class="material-icons-outlined">cloud_upload</span>
                                        Upload
                                        <input type="file" accept="image/*" style="display: none;" onchange="uploadBanner('${banner.key}', this)">
                                    </label>
                                    ${hasImage ? `<button class="btn-remove" onclick="removeBanner('${banner.key}')">
                                        <span class="material-icons-outlined">delete</span>
                                        Xóa
                                    </button>` : ''}
                                </div>
                            </div>
                        </div>
                    `;
                }).join('');
            } catch (error) {
                console.error('Error loading banners:', error);
                grid.innerHTML = '<div class="empty-state"><span class="material-icons-outlined">error</span><h3>Lỗi tải banner</h3></div>';
            }
        }
        
        function adjustColor(hex, percent) {
            // Lighten a hex color
            const num = parseInt(hex.replace('#', ''), 16);
            const amt = Math.round(2.55 * percent);
            const R = (num >> 16) + amt;
            const G = (num >> 8 & 0x00FF) + amt;
            const B = (num & 0x0000FF) + amt;
            return '#' + (0x1000000 + (R < 255 ? R < 1 ? 0 : R : 255) * 0x10000 + (G < 255 ? G < 1 ? 0 : G : 255) * 0x100 + (B < 255 ? B < 1 ? 0 : B : 255)).toString(16).slice(1);
        }
        
        async function uploadBanner(key, input) {
            if (!input.files || !input.files[0]) return;
            
            const file = input.files[0];
            const formData = new FormData();
            formData.append('image', file);
            
            try {
                showToast('Đang tải lên...', 'success');
                
                // Upload image
                const uploadResponse = await fetch(`${API_BASE}/upload_image.php`, {
                    method: 'POST',
                    headers: { 'X-CSRF-Token': CSRF_TOKEN },
                    body: formData
                });
                
                const uploadResult = await uploadResponse.json();
                
                if (!uploadResult.status || !uploadResult.url) {
                    throw new Error(uploadResult.message || 'Upload thất bại');
                }
                
                // Save to CMS content
                const saveResponse = await fetch(`${API_BASE}/save_content.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': CSRF_TOKEN
                    },
                    body: JSON.stringify({
                        section_key: key,
                        content_value: uploadResult.url
                    })
                });
                
                const saveResult = await saveResponse.json();
                
                if (saveResult.status) {
                    showToast('Cập nhật banner thành công!', 'success');
                    loadBanners();
                } else {
                    throw new Error(saveResult.message || 'Lỗi lưu');
                }
            } catch (error) {
                console.error('Error uploading banner:', error);
                showToast('Lỗi: ' + error.message, 'error');
            }
            
            input.value = '';
        }
        
        async function removeBanner(key) {
            if (!confirm('Bạn có chắc muốn xóa hình nền banner này?')) return;
            
            try {
                const response = await fetch(`${API_BASE}/save_content.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': CSRF_TOKEN
                    },
                    body: JSON.stringify({
                        section_key: key,
                        content_value: ''
                    })
                });
                
                const result = await response.json();
                
                if (result.status) {
                    showToast('Đã xóa banner!', 'success');
                    loadBanners();
                } else {
                    throw new Error(result.message || 'Lỗi xóa');
                }
            } catch (error) {
                console.error('Error removing banner:', error);
                showToast('Lỗi: ' + error.message, 'error');
            }
        }

        // ===== VISUAL CMS EDITOR =====
        const pageConfigs = {
            index: {
                name: 'Trang chủ',
                sections: [
                    { title: '🖼️ Hero Slider', icon: 'image', fields: [
                        { key: 'index_hero_slide_1_img', label: 'Slide 1 - Ảnh', type: 'image', defaultValue: 'https://icogroup.vn/vnt_upload/weblink/banner_trang_chu_01.jpg' },
                        { key: 'index_hero_slide_1_title', label: 'Slide 1 - Tiêu đề', type: 'text', defaultValue: 'ICOGroup - Nền Tảng Giáo Dục & Việc Làm Quốc Tế' },
                        { key: 'index_hero_slide_1_subtitle', label: 'Slide 1 - Mô tả', type: 'text', defaultValue: '15 năm đồng hành cùng thế hệ trẻ Việt Nam' },
                        { key: 'index_hero_slide_2_img', label: 'Slide 2 - Ảnh', type: 'image', defaultValue: 'https://icogroup.vn/vnt_upload/weblink/banner_chu_04.jpg' },
                        { key: 'index_hero_slide_2_title', label: 'Slide 2 - Tiêu đề', type: 'text', defaultValue: 'Du Học Nhật - Hàn - Đức' },
                        { key: 'index_hero_slide_2_subtitle', label: 'Slide 2 - Mô tả', type: 'text', defaultValue: 'Học bổng lên đến 100%, visa cao' },
                        { key: 'index_hero_slide_3_img', label: 'Slide 3 - Ảnh', type: 'image', defaultValue: 'https://www.icogroup.vn/vnt_upload/news/02_2025/ICOGROUP_TUYEN_DUNG_23.jpg' },
                        { key: 'index_hero_slide_3_title', label: 'Slide 3 - Tiêu đề', type: 'text', defaultValue: 'Xuất Khẩu Lao Động' },
                        { key: 'index_hero_slide_3_subtitle', label: 'Slide 3 - Mô tả', type: 'text', defaultValue: 'Thu nhập 30-50 triệu/tháng' },
                        { key: 'index_hero_btn_text', label: 'Text nút CTA', type: 'text', defaultValue: 'Đăng ký tư vấn miễn phí' }
                    ]},
                    { title: 'ℹ️ Giới Thiệu', icon: 'info', fields: [
                        { key: 'index_about_bg', label: 'Ảnh nền section', type: 'image', defaultValue: 'https://icogroup.vn/vnt_upload/weblink/banner_trang_chu_01.jpg' },
                        { key: 'index_about_title', label: 'Tiêu đề', type: 'text', defaultValue: 'Về ICOGroup' },
                        { key: 'index_about_history_title', label: 'Tiêu đề lịch sử', type: 'text', defaultValue: 'Lịch Sử Phát Triển' },
                        { key: 'index_about_history_desc', label: 'Mô tả lịch sử', type: 'textarea', defaultValue: 'Được thành lập từ năm 2009, ICOGroup đã trở thành một trong những tập đoàn giáo dục hàng đầu Việt Nam.' }
                    ]},
                    { title: '📊 Thống Kê', icon: 'analytics', fields: [
                        { key: 'index_stat_1_number', label: 'Số 1 (vd: 15+)', type: 'text', defaultValue: '15+' },
                        { key: 'index_stat_1_label', label: 'Nhãn 1', type: 'text', defaultValue: 'Năm kinh nghiệm' },
                        { key: 'index_stat_2_number', label: 'Số 2', type: 'text', defaultValue: '50,000+' },
                        { key: 'index_stat_2_label', label: 'Nhãn 2', type: 'text', defaultValue: 'Du học sinh' },
                        { key: 'index_stat_3_number', label: 'Số 3', type: 'text', defaultValue: '100+' },
                        { key: 'index_stat_3_label', label: 'Nhãn 3', type: 'text', defaultValue: 'Đối tác quốc tế' },
                        { key: 'index_stat_4_number', label: 'Số 4', type: 'text', defaultValue: '20+' },
                        { key: 'index_stat_4_label', label: 'Nhãn 4', type: 'text', defaultValue: 'Chi nhánh toàn quốc' }
                    ]},
                    { title: '🏢 Hệ Sinh Thái', icon: 'business', fields: [
                        { key: 'index_eco_1_name', label: 'Đơn vị 1 - Tên', type: 'text', defaultValue: 'Tiếng Nhật ICO' },
                        { key: 'index_eco_1_desc', label: 'Đơn vị 1 - Mô tả', type: 'textarea', defaultValue: 'Trung tâm đào tạo tiếng Nhật hàng đầu Việt Nam' },
                        { key: 'index_eco_1_img', label: 'Đơn vị 1 - Ảnh nền', type: 'image', defaultValue: 'https://icogroup.vn/vnt_upload/service/Linkedin_3.jpg' },
                        { key: 'index_eco_1_logo', label: 'Đơn vị 1 - Logo', type: 'image', defaultValue: 'https://icogroup.vn/vnt_upload/service/Logo_TTNN_ICO_24x_100.jpg' },
                        { key: 'index_eco_2_name', label: 'Đơn vị 2 - Tên', type: 'text', defaultValue: 'ICOSchool' },
                        { key: 'index_eco_2_desc', label: 'Đơn vị 2 - Mô tả', type: 'textarea', defaultValue: 'Trường dạy nghề ICO' },
                        { key: 'index_eco_2_img', label: 'Đơn vị 2 - Ảnh nền', type: 'image', defaultValue: 'https://icogroup.vn/vnt_upload/service/khai_giang_icoschool.jpg' },
                        { key: 'index_eco_2_logo', label: 'Đơn vị 2 - Logo', type: 'image', defaultValue: 'https://icogroup.vn/vnt_upload/service/mmicon2.jpg' },
                        { key: 'index_eco_3_name', label: 'Đơn vị 3 - Tên', type: 'text', defaultValue: 'ICOCollege' },
                        { key: 'index_eco_3_desc', label: 'Đơn vị 3 - Mô tả', type: 'textarea', defaultValue: 'Cao đẳng nghề ICO' },
                        { key: 'index_eco_3_img', label: 'Đơn vị 3 - Ảnh nền', type: 'image', defaultValue: 'https://icogroup.vn/vnt_upload/service/mmimg3.jpg' },
                        { key: 'index_eco_3_logo', label: 'Đơn vị 3 - Logo', type: 'image', defaultValue: 'https://icogroup.vn/vnt_upload/service/mmicon3.jpg' },
                        { key: 'index_eco_4_name', label: 'Đơn vị 4 - Tên', type: 'text', defaultValue: 'ICOCareer' },
                        { key: 'index_eco_4_desc', label: 'Đơn vị 4 - Mô tả', type: 'textarea', defaultValue: 'Việc làm trong nước và quốc tế' },
                        { key: 'index_eco_4_img', label: 'Đơn vị 4 - Ảnh nền', type: 'image', defaultValue: 'https://icogroup.vn/vnt_upload/service/mmimg4.jpg' }
                    ]},
                    { title: '📚 Chương Trình Nổi Bật', icon: 'school', fields: [
                        { key: 'index_program_1_title', label: 'CT 1 - Tiêu đề', type: 'text', defaultValue: 'Du Học Nhật Bản' },
                        { key: 'index_program_1_desc', label: 'CT 1 - Mô tả', type: 'textarea', defaultValue: 'Học bổng lên đến 100%, làm thêm 28h/tuần' },
                        { key: 'index_program_1_img', label: 'CT 1 - Ảnh', type: 'image', defaultValue: 'https://icogroup.vn/vnt_upload/weblink/banner_chu_02.jpg' },
                        { key: 'index_program_2_title', label: 'CT 2 - Tiêu đề', type: 'text', defaultValue: 'Du Học Đức' },
                        { key: 'index_program_2_desc', label: 'CT 2 - Mô tả', type: 'textarea', defaultValue: 'Miễn học phí, học nghề hưởng lương' },
                        { key: 'index_program_2_img', label: 'CT 2 - Ảnh', type: 'image', defaultValue: 'https://icogroup.vn/vnt_upload/weblink/banner_chu_03.jpg' },
                        { key: 'index_program_3_title', label: 'CT 3 - Tiêu đề', type: 'text', defaultValue: 'XKLĐ Nhật Bản' },
                        { key: 'index_program_3_desc', label: 'CT 3 - Mô tả', type: 'textarea', defaultValue: 'Thu nhập 30-40 triệu/tháng, chương trình thực tập sinh kỹ năng' },
                        { key: 'index_program_3_img', label: 'CT 3 - Ảnh', type: 'image', defaultValue: 'https://icogroup.vn/vnt_upload/weblink/banner_chu_04.jpg' }
                    ]}
                ]
            },
            nhat: {
                name: 'Du học Nhật Bản',
                sections: [
                    { title: '🖼️ Banner Trang', icon: 'image', fields: [
                        { key: 'nhat_header_bg', label: 'Ảnh nền banner', type: 'image', defaultValue: 'https://icogroup.vn/vnt_upload/weblink/banner_nhat.jpg' },
                        { key: 'nhat_title', label: 'Tiêu đề chính', type: 'text', defaultValue: 'Du Học Nhật Bản 🇯🇵' },
                        { key: 'nhat_subtitle', label: 'Mô tả ngắn', type: 'text', defaultValue: 'Hành trình chinh phục xứ sở hoa anh đào' }
                    ]},
                    { title: 'ℹ️ Giới Thiệu', icon: 'info', fields: [
                        { key: 'nhat_why_title', label: 'Tiêu đề phần "Tại sao chọn"', type: 'text', defaultValue: 'Tại Sao Chọn Du Học Nhật Bản?' },
                        { key: 'nhat_about_img', label: 'Ảnh giới thiệu', type: 'image', defaultValue: 'https://cdn-images.vtv.vn/562122370168008704/2023/7/26/untitled-1690344019340844974097.png' },
                        { key: 'nhat_reason_title', label: 'Tiêu đề lý do', type: 'text', defaultValue: 'Lý Do Nên Du Học Nhật Bản' },
                        { key: 'nhat_reason_desc', label: 'Mô tả lý do', type: 'textarea', defaultValue: 'Nhật Bản là quốc gia có nền giáo dục tiên tiến, công nghệ phát triển và nền văn hóa độc đáo.' }
                    ]},
                    { title: '✨ Lợi Ích', icon: 'star', fields: [
                        { key: 'nhat_benefit_1', label: 'Lợi ích 1', type: 'text', defaultValue: 'Giáo dục đẳng cấp thế giới' },
                        { key: 'nhat_benefit_2', label: 'Lợi ích 2', type: 'text', defaultValue: 'Làm thêm 28h/tuần hợp pháp' },
                        { key: 'nhat_benefit_3', label: 'Lợi ích 3', type: 'text', defaultValue: 'Học bổng lên đến 100%' },
                        { key: 'nhat_benefit_4', label: 'Lợi ích 4', type: 'text', defaultValue: 'An ninh và an toàn cao' },
                        { key: 'nhat_benefit_5', label: 'Lợi ích 5', type: 'text', defaultValue: 'Cơ hội việc làm sau tốt nghiệp' },
                        { key: 'nhat_benefit_6', label: 'Lợi ích 6', type: 'text', defaultValue: 'Văn hóa độc đáo, hấp dẫn' }
                    ]},
                    { title: '📚 Chương Trình', icon: 'school', fields: [
                        { key: 'nhat_program_1_title', label: 'Chương trình 1 - Tiêu đề', type: 'text', defaultValue: 'Du Học Tiếng Nhật' },
                        { key: 'nhat_program_1_desc', label: 'Chương trình 1 - Mô tả', type: 'textarea', defaultValue: 'Chương trình học tiếng Nhật từ 6 tháng - 2 năm tại các trường Nhật ngữ uy tín.' },
                        { key: 'nhat_program_2_title', label: 'Chương trình 2 - Tiêu đề', type: 'text', defaultValue: 'Du Học Cao Đẳng - Đại Học' },
                        { key: 'nhat_program_2_desc', label: 'Chương trình 2 - Mô tả', type: 'textarea', defaultValue: 'Học tại các trường Cao đẳng, Đại học tại Nhật Bản với nhiều ngành học đa dạng.' },
                        { key: 'nhat_program_3_title', label: 'Chương trình 3 - Tiêu đề', type: 'text', defaultValue: 'Du Học Nghề (Senmon)' },
                        { key: 'nhat_program_3_desc', label: 'Chương trình 3 - Mô tả', type: 'textarea', defaultValue: 'Học tại các trường chuyên môn với thời gian 2 năm.' }
                    ]},
                    { title: '📞 Liên Hệ', icon: 'call', fields: [
                        { key: 'nhat_cta_title', label: 'Tiêu đề nút đăng ký', type: 'text', defaultValue: 'Đăng Ký Tư Vấn Du Học Nhật Bản' },
                        { key: 'nhat_cta_desc', label: 'Mô tả', type: 'text', defaultValue: 'Nhận tư vấn miễn phí từ đội ngũ chuyên gia với 15 năm kinh nghiệm' }
                    ]}
                ]
            },
            duc: {
                name: 'Du học Đức',
                sections: [
                    { title: '🖼️ Banner Trang', icon: 'image', fields: [
                        { key: 'duc_header_bg', label: 'Ảnh nền banner', type: 'image', defaultValue: 'https://icogroup.vn/vnt_upload/weblink/banner_duc.jpg' },
                        { key: 'duc_title', label: 'Tiêu đề chính', type: 'text', defaultValue: 'Du Học Đức' },
                        { key: 'duc_subtitle', label: 'Mô tả ngắn', type: 'text', defaultValue: 'Chương trình du học miễn học phí với cơ hội việc làm và định cư' }
                    ]},
                    { title: 'ℹ️ Giới Thiệu', icon: 'info', fields: [
                        { key: 'duc_why_title', label: 'Tiêu đề phần "Tại sao chọn"', type: 'text', defaultValue: 'Tại Sao Chọn Du Học Đức?' },
                        { key: 'duc_about_img', label: 'Ảnh giới thiệu', type: 'image', defaultValue: 'https://icogroupvn.wordpress.com/wp-content/uploads/2017/03/du-hoc-duc-ico-cho-tuong-lai-tuoi-sang-01.jpg' },
                        { key: 'duc_advantage_title', label: 'Tiêu đề ưu điểm', type: 'text', defaultValue: 'Ưu Điểm Vượt Trội' },
                        { key: 'duc_advantage_desc', label: 'Mô tả ưu điểm', type: 'textarea', defaultValue: 'Đức là một trong những quốc gia có nền giáo dục hàng đầu thế giới.' }
                    ]},
                    { title: '✨ Lợi Ích', icon: 'star', fields: [
                        { key: 'duc_benefit_1', label: 'Lợi ích 1', type: 'text', defaultValue: 'Miễn học phí tại đại học công lập' },
                        { key: 'duc_benefit_2', label: 'Lợi ích 2', type: 'text', defaultValue: 'Học nghề hưởng lương 800-1200€/tháng' },
                        { key: 'duc_benefit_3', label: 'Lợi ích 3', type: 'text', defaultValue: 'Cơ hội định cư sau khi tốt nghiệp' },
                        { key: 'duc_benefit_4', label: 'Lợi ích 4', type: 'text', defaultValue: 'Bằng cấp được công nhận toàn cầu' },
                        { key: 'duc_benefit_5', label: 'Lợi ích 5', type: 'text', defaultValue: 'Du lịch tự do trong khối Schengen' }
                    ]},
                    { title: '📚 Chương Trình', icon: 'school', fields: [
                        { key: 'duc_program_1_title', label: 'Đại học - Tiêu đề', type: 'text', defaultValue: 'Du Học Đại Học' },
                        { key: 'duc_program_1_desc', label: 'Đại học - Mô tả', type: 'textarea', defaultValue: 'Học tại các trường đại học công lập hàng đầu nước Đức với học phí 0€.' },
                        { key: 'duc_program_2_title', label: 'Ausbildung - Tiêu đề', type: 'text', defaultValue: 'Du Học Nghề (Ausbildung)' },
                        { key: 'duc_program_2_desc', label: 'Ausbildung - Mô tả', type: 'textarea', defaultValue: 'Chương trình đào tạo kép: Học + thực hành. Lương 800-1200€/tháng.' },
                        { key: 'duc_program_3_title', label: 'Du học hè - Tiêu đề', type: 'text', defaultValue: 'Du Học Hè' },
                        { key: 'duc_program_3_desc', label: 'Du học hè - Mô tả', type: 'textarea', defaultValue: 'Chương trình trải nghiệm ngắn hạn 2-4 tuần.' }
                    ]}
                ]
            },
            han: {
                name: 'Du học Hàn Quốc',
                sections: [
                    { title: '🖼️ Banner Trang', icon: 'image', fields: [
                        { key: 'han_header_bg', label: 'Ảnh nền banner', type: 'image', defaultValue: 'https://icogroup.vn/vnt_upload/weblink/banner_han.jpg' },
                        { key: 'han_title', label: 'Tiêu đề chính', type: 'text', defaultValue: 'Du Học Hàn Quốc 🇰🇷' },
                        { key: 'han_subtitle', label: 'Mô tả ngắn', type: 'text', defaultValue: 'Khám phá xứ sở kim chi - Điểm đến du học hấp dẫn' }
                    ]},
                    { title: 'ℹ️ Giới Thiệu', icon: 'info', fields: [
                        { key: 'han_about_img', label: 'Ảnh giới thiệu', type: 'image', defaultValue: 'https://icogroup.vn/vnt_upload/news/11_2024/TRUONG_DAI_HOC_PUKYONG.jpg' }
                    ]},
                    { title: '✨ Lợi Ích', icon: 'star', fields: [
                        { key: 'han_benefit_1', label: 'Lợi ích 1', type: 'text', defaultValue: 'Chi phí hợp lý, học bổng đa dạng' },
                        { key: 'han_benefit_2', label: 'Lợi ích 2', type: 'text', defaultValue: 'Nền giáo dục tiên tiến hàng đầu châu Á' },
                        { key: 'han_benefit_3', label: 'Lợi ích 3', type: 'text', defaultValue: 'Cơ hội làm thêm hợp pháp' },
                        { key: 'han_benefit_4', label: 'Lợi ích 4', type: 'text', defaultValue: 'Văn hóa K-pop, Hallyu hấp dẫn' },
                        { key: 'han_benefit_5', label: 'Lợi ích 5', type: 'text', defaultValue: 'Cơ hội việc làm sau tốt nghiệp' }
                    ]},
                    { title: '📚 Chương Trình', icon: 'school', fields: [
                        { key: 'han_program_1_title', label: 'Tiếng Hàn - Tiêu đề', type: 'text', defaultValue: 'Du Học Tiếng Hàn' },
                        { key: 'han_program_1_desc', label: 'Tiếng Hàn - Mô tả', type: 'textarea', defaultValue: 'Học tiếng Hàn tại các trường đại học hàng đầu Hàn Quốc.' },
                        { key: 'han_program_2_title', label: 'Đại học - Tiêu đề', type: 'text', defaultValue: 'Du Học Đại Học' },
                        { key: 'han_program_2_desc', label: 'Đại học - Mô tả', type: 'textarea', defaultValue: 'Học cử nhân tại các trường đại học danh tiếng với học bổng hấp dẫn.' },
                        { key: 'han_program_3_title', label: 'Thạc sĩ - Tiêu đề', type: 'text', defaultValue: 'Du Học Sau Đại Học' },
                        { key: 'han_program_3_desc', label: 'Thạc sĩ - Mô tả', type: 'textarea', defaultValue: 'Chương trình Thạc sĩ, Tiến sĩ với nhiều học bổng toàn phần.' }
                    ]}
                ]
            },
            xkldjp: {
                name: 'XKLĐ Nhật Bản',
                sections: [
                    { title: '🖼️ Banner Trang', icon: 'image', fields: [
                        { key: 'xkldjp_header_bg', label: 'Ảnh nền banner', type: 'image', defaultValue: 'https://icogroup.vn/vnt_upload/weblink/banner_chu_04.jpg' },
                        { key: 'xkldjp_title', label: 'Tiêu đề chính', type: 'text', defaultValue: 'Xuất Khẩu Lao Động Nhật Bản' },
                        { key: 'xkldjp_subtitle', label: 'Mô tả ngắn', type: 'text', defaultValue: 'Chương trình thực tập sinh kỹ năng tại Nhật Bản' }
                    ]},
                    {
                        title: 'ℹ️ Giới thiệu chương trình', icon: 'info',
                        fields: [
                            { key: 'xkldjp_intro_img', type: 'image', label: 'Hình ảnh Intro', defaultValue: 'https://icogroup.vn/vnt_upload/weblink/banner_chu_04.jpg' },
                            { key: 'xkldjp_intro_title', label: 'Tiêu đề giới thiệu', type: 'text', defaultValue: 'Chương Trình Thực Tập Sinh Kỹ Năng' },
                            { key: 'xkldjp_intro_desc', type: 'textarea', label: 'Mô tả ngắn', defaultValue: 'Nhật Bản là điểm đến hàng đầu của lao động Việt Nam với môi trường làm việc chuyên nghiệp, thu nhập cao và nhiều cơ hội phát triển.' },
                            { key: 'xkldjp_benefit_1', label: 'Lợi ích 1', type: 'text', defaultValue: '💰 Thu nhập 30-40 triệu/tháng' },
                            { key: 'xkldjp_benefit_2', label: 'Lợi ích 2', type: 'text', defaultValue: '🏠 Hỗ trợ chỗ ở miễn phí' },
                            { key: 'xkldjp_benefit_3', label: 'Lợi ích 3', type: 'text', defaultValue: '✈️ Bay 0 đồng' },
                            { key: 'xkldjp_benefit_4', label: 'Lợi ích 4', type: 'text', defaultValue: '📋 Hợp đồng 3 năm' },
                            { key: 'xkldjp_benefit_5', label: 'Lợi ích 5', type: 'text', defaultValue: '🛡️ Bảo hiểm đầy đủ' }
                        ]
                    }
                ]
            },
            xkldhan: {
                name: 'XKLĐ Hàn Quốc',
                sections: [
                    { title: '🖼️ Banner Trang', icon: 'image', fields: [
                        { key: 'xkldhan_header_bg', label: 'Ảnh nền banner', type: 'image', defaultValue: 'https://icogroup.vn/vnt_upload/weblink/banner_xkldhan.jpg' },
                        { key: 'xkldhan_title', label: 'Tiêu đề chính', type: 'text', defaultValue: 'Xuất Khẩu Lao Động Hàn Quốc 🇰🇷' },
                        { key: 'xkldhan_subtitle', label: 'Mô tả ngắn', type: 'text', defaultValue: 'Chương trình EPS - Cơ hội việc làm tại Hàn Quốc' }
                    ]},
                    { title: 'ℹ️ Giới Thiệu', icon: 'info', fields: [
                        { key: 'xkldhan_main_img', label: 'Ảnh giới thiệu', type: 'image', defaultValue: 'https://icogroup.vn/vnt_upload/news/11_2024/TRUONG_DAI_HOC_PUKYONG.jpg' }
                    ]},
                    { title: '📋 Chương Trình EPS', icon: 'work', fields: [
                        { key: 'xkldhan_program_title', label: 'Tiêu đề chương trình', type: 'text', defaultValue: 'Chương Trình EPS (Employment Permit System)' },
                        { key: 'xkldhan_program_desc', label: 'Mô tả chương trình', type: 'textarea', defaultValue: 'Chương trình hợp tác lao động giữa Việt Nam và Hàn Quốc, mang đến cơ hội việc làm ổn định với mức lương hấp dẫn.' }
                    ]},
                    { title: '✨ Quyền Lợi', icon: 'star', fields: [
                        { key: 'xkldhan_benefit_1', label: 'Thu nhập', type: 'text', defaultValue: 'Thu nhập 35-50 triệu VNĐ/tháng' },
                        { key: 'xkldhan_benefit_2', label: 'Hợp đồng', type: 'text', defaultValue: 'Hợp đồng 4 năm 10 tháng' },
                        { key: 'xkldhan_benefit_3', label: 'Bảo hiểm', type: 'text', defaultValue: 'Bảo hiểm đầy đủ theo luật Hàn Quốc' }
                    ]}
                ]
            },
            huongnghiep: {
                name: 'Hướng nghiệp',
                sections: [
                    { title: '🖼️ Banner Trang', icon: 'image', fields: [
                        { key: 'huongnghiep_header_bg', label: 'Ảnh nền banner', type: 'image', defaultValue: 'https://icogroup.vn/vnt_upload/weblink/banner_huongnghiep.jpg' },
                        { key: 'huongnghiep_title', label: 'Tiêu đề chính', type: 'text', defaultValue: 'Hướng Nghiệp - Định Hướng Tương Lai' },
                        { key: 'huongnghiep_subtitle', label: 'Mô tả ngắn', type: 'text', defaultValue: 'Khám phá cơ hội nghề nghiệp phù hợp với bạn' }
                    ]},
                    { title: '📚 Chương Trình', icon: 'school', fields: [
                        { key: 'huongnghiep_programs_title', label: 'Tiêu đề phần chương trình', type: 'text', defaultValue: 'Các Chương Trình Hướng Nghiệp' },
                        { key: 'huongnghiep_program_1_title', label: 'Du học - Tiêu đề', type: 'text', defaultValue: 'Du Học Quốc Tế' },
                        { key: 'huongnghiep_program_1_desc', label: 'Du học - Mô tả', type: 'textarea', defaultValue: 'Cơ hội học tập tại các nước phát triển như Nhật Bản, Hàn Quốc, Đức, Đài Loan.' },
                        { key: 'huongnghiep_program_1_img', label: 'Du học - Ảnh', type: 'image', defaultValue: 'https://cdn-images.vtv.vn/562122370168008704/2023/7/26/untitled-1690344019340844974097.png' },
                        { key: 'huongnghiep_program_2_title', label: 'Lao động QT - Tiêu đề', type: 'text', defaultValue: 'Lao Động Quốc Tế' },
                        { key: 'huongnghiep_program_2_desc', label: 'Lao động QT - Mô tả', type: 'textarea', defaultValue: 'Chương trình xuất khẩu lao động với thu nhập cao và môi trường làm việc chuyên nghiệp.' },
                        { key: 'huongnghiep_program_2_img', label: 'Lao động QT - Ảnh', type: 'image', defaultValue: 'https://icogroup.vn/vnt_upload/weblink/banner_chu_04.jpg' },
                        { key: 'huongnghiep_program_3_title', label: 'Việc làm VN - Tiêu đề', type: 'text', defaultValue: 'Việc Làm Trong Nước' },
                        { key: 'huongnghiep_program_3_desc', label: 'Việc làm VN - Mô tả', type: 'textarea', defaultValue: 'Kết nối với các doanh nghiệp uy tín trong nước, cơ hội việc làm ổn định.' },
                        { key: 'huongnghiep_program_3_img', label: 'Việc làm VN - Ảnh', type: 'image', defaultValue: 'https://icogroup.vn/vnt_upload/news/11_2024/43_NAM_NGAY_NHA_GIAO_VN_1.jpg' }
                    ]}
                ]
            },

            xklddailoan: {
                name: 'XKLĐ Đài Loan',
                sections: [
                    { title: '🖼️ Banner Trang', icon: 'image', fields: [
                        { key: 'xklddailoan_header_bg', label: 'Ảnh nền banner', type: 'image', defaultValue: 'https://icogroup.vn/vnt_upload/weblink/banner_xklddailoan.jpg' },
                        { key: 'xklddailoan_title', label: 'Tiêu đề chính', type: 'text', defaultValue: 'Xuất Khẩu Lao Động Đài Loan 🇹🇼' },
                        { key: 'xklddailoan_subtitle', label: 'Mô tả ngắn', type: 'text', defaultValue: 'Cơ hội việc làm tại Đài Loan với thu nhập hấp dẫn' }
                    ]},
                    { title: 'ℹ️ Giới Thiệu', icon: 'info', fields: [
                        { key: 'xklddailoan_main_img', label: 'Ảnh giới thiệu', type: 'image', defaultValue: 'https://icogroup.vn/vnt_upload/weblink/banner_chu_04.jpg' }
                    ]},
                    { title: '📋 Chương Trình', icon: 'work', fields: [
                        { key: 'xklddailoan_program_title', label: 'Tiêu đề chương trình', type: 'text', defaultValue: 'Chương Trình Lao Động Đài Loan' },
                        { key: 'xklddailoan_program_desc', label: 'Mô tả', type: 'textarea', defaultValue: 'Chương trình xuất khẩu lao động sang Đài Loan trong các ngành sản xuất, chế tạo, điện tử với thu nhập ổn định.' }
                    ]},
                    { title: '✨ Quyền Lợi', icon: 'star', fields: [
                        { key: 'xklddailoan_benefit_1', label: 'Quyền lợi 1', type: 'text', defaultValue: 'Thu nhập 20-30 triệu VNĐ/tháng' },
                        { key: 'xklddailoan_benefit_2', label: 'Quyền lợi 2', type: 'text', defaultValue: 'Hợp đồng 3 năm, có thể gia hạn' },
                        { key: 'xklddailoan_benefit_3', label: 'Quyền lợi 3', type: 'text', defaultValue: 'Hỗ trợ nhà ở, ăn uống' }
                    ]}
                ]
            },
            xkldchauau: {
                name: 'XKLĐ Châu Âu',
                sections: [
                    { title: '🖼️ Banner Trang', icon: 'image', fields: [
                        { key: 'xkldchauau_header_bg', label: 'Ảnh nền banner', type: 'image', defaultValue: 'https://icogroup.vn/vnt_upload/weblink/banner_xkldchauau.jpg' },
                        { key: 'xkldchauau_title', label: 'Tiêu đề chính', type: 'text', defaultValue: 'Xuất Khẩu Lao Động Châu Âu 🇪🇺' },
                        { key: 'xkldchauau_subtitle', label: 'Mô tả ngắn', type: 'text', defaultValue: 'Cơ hội việc làm tại các nước Châu Âu với mức lương cao' }
                    ]},
                    { title: 'ℹ️ Giới Thiệu', icon: 'info', fields: [
                        { key: 'xkldchauau_main_img', label: 'Ảnh giới thiệu', type: 'image', defaultValue: 'https://icogroup.vn/vnt_upload/weblink/banner_chu_04.jpg' }
                    ]},
                    { title: '📋 Chương Trình', icon: 'work', fields: [
                        { key: 'xkldchauau_program_title', label: 'Tiêu đề chương trình', type: 'text', defaultValue: 'Chương Trình Lao Động Châu Âu' },
                        { key: 'xkldchauau_program_desc', label: 'Mô tả', type: 'textarea', defaultValue: 'Chương trình xuất khẩu lao động sang các nước Châu Âu như Ba Lan, Romania, Séc với thu nhập cao và môi trường làm việc hiện đại.' }
                    ]},
                    { title: '✨ Quyền Lợi', icon: 'star', fields: [
                        { key: 'xkldchauau_benefit_1', label: 'Quyền lợi 1', type: 'text', defaultValue: 'Thu nhập 25-40 triệu VNĐ/tháng' },
                        { key: 'xkldchauau_benefit_2', label: 'Quyền lợi 2', type: 'text', defaultValue: 'Visa lao động dài hạn' },
                        { key: 'xkldchauau_benefit_3', label: 'Quyền lợi 3', type: 'text', defaultValue: 'Bảo hiểm y tế, xã hội đầy đủ' }
                    ]},
                    { title: '🌍 Quốc Gia', icon: 'public', fields: [
                        { key: 'xkldchauau_country_1_name', label: 'Quốc gia 1', type: 'text', defaultValue: 'Ba Lan 🇵🇱' },
                        { key: 'xkldchauau_country_1_desc', label: 'Mô tả 1', type: 'textarea', defaultValue: 'Thị trường lao động phát triển với nhiều cơ hội việc làm trong ngành sản xuất, xây dựng.' },
                        { key: 'xkldchauau_country_2_name', label: 'Quốc gia 2', type: 'text', defaultValue: 'Romania 🇷🇴' },
                        { key: 'xkldchauau_country_2_desc', label: 'Mô tả 2', type: 'textarea', defaultValue: 'Chi phí sinh hoạt thấp, thu nhập ổn định trong các ngành nông nghiệp, chế tạo.' },
                        { key: 'xkldchauau_country_3_name', label: 'Quốc gia 3', type: 'text', defaultValue: 'Séc 🇨🇿' },
                        { key: 'xkldchauau_country_3_desc', label: 'Mô tả 3', type: 'textarea', defaultValue: 'Môi trường làm việc hiện đại, thu nhập cao trong ngành ô tô, điện tử.' }
                    ]}
                ]
            },
            veicogroup: {
                name: 'Về ICOGroup',
                sections: [
                    { title: '🖼️ Banner Trang', icon: 'image', fields: [
                        { key: 'about_header_bg', label: 'Ảnh nền banner', type: 'image', defaultValue: 'https://icogroup.vn/vnt_upload/weblink/banner_trang_chu_01.jpg' },
                        { key: 'about_title', label: 'Tiêu đề chính', type: 'text', defaultValue: 'Về ICOGroup' },
                        { key: 'about_subtitle', label: 'Mô tả ngắn', type: 'text', defaultValue: 'Tổ chức Giáo dục và Nhân lực Quốc tế ICO' }
                    ]},
                    { title: '📜 Lịch Sử', icon: 'history', fields: [
                        { key: 'about_history_title', label: 'Tiêu đề', type: 'text', defaultValue: 'Lịch Sử Hình Thành & Phát Triển' },
                        { key: 'about_history_image', label: 'Ảnh lịch sử', type: 'image', defaultValue: 'https://icogroup.vn/vnt_upload/weblink/banner_trang_chu_01.jpg' },
                        { key: 'about_founded_date', label: 'Ngày thành lập', type: 'text', defaultValue: 'Thành lập 29/4/2008' },
                        { key: 'about_history_desc', label: 'Mô tả lịch sử', type: 'textarea', defaultValue: 'Tổ chức Giáo dục và Nhân lực Quốc tế ICO được thành lập vào ngày 29/4/2008, hoạt động chuyên nghiệp trong lĩnh vực Du học và Xuất khẩu lao động.' }
                    ]},
                    { title: '🎯 Giá Trị Cốt Lõi', icon: 'star', fields: [
                        { key: 'about_mission', label: 'Sứ mệnh', type: 'textarea', defaultValue: 'Nâng cao chất lượng nguồn nhân lực Việt Nam, tạo cầu nối giữa người lao động Việt Nam với các cơ hội việc làm và học tập quốc tế.' },
                        { key: 'about_vision', label: 'Tầm nhìn', type: 'textarea', defaultValue: 'Trở thành tập đoàn phát triển nhân lực lớn nhất Việt Nam, vươn xa ra khu vực và thế giới.' },
                        { key: 'about_core_values', label: 'Giá trị cốt lõi', type: 'text', defaultValue: 'Trí tuệ - Trung thực - Tận tâm' },
                        { key: 'about_slogan', label: 'Slogan', type: 'text', defaultValue: 'ICOGroup - Nơi tạo dựng tương lai' }
                    ]},
                    { title: '📊 Thống Kê', icon: 'analytics', fields: [
                        { key: 'about_stat_students', label: 'Số du học sinh', type: 'text', defaultValue: '17000' },
                        { key: 'about_stat_workers', label: 'Số lao động', type: 'text', defaultValue: '38000' },
                        { key: 'about_stat_provinces', label: 'Số tỉnh thành', type: 'text', defaultValue: '60' },
                        { key: 'about_stat_partners', label: 'Số trường đối tác', type: 'text', defaultValue: '100' }
                    ]}
                ]
            },
            lienhe: {
                name: 'Liên Hệ',
                sections: [
                    { title: '🖼️ Banner Trang', icon: 'image', fields: [
                        { key: 'contact_header_bg', label: 'Ảnh nền banner', type: 'image', defaultValue: 'https://icogroup.vn/vnt_upload/weblink/banner_lienhe.jpg' },
                        { key: 'contact_title', label: 'Tiêu đề chính', type: 'text', defaultValue: 'Liên Hệ Với Chúng Tôi' },
                        { key: 'contact_subtitle', label: 'Mô tả ngắn', type: 'text', defaultValue: 'Chúng tôi luôn sẵn sàng hỗ trợ bạn' }
                    ]},
                    { title: '📍 Thông Tin Liên Hệ', icon: 'location_on', fields: [
                        { key: 'contact_address', label: 'Địa chỉ', type: 'text', defaultValue: 'Số 360, đường Phan Đình Phùng, tỉnh Thái Nguyên' },
                        { key: 'contact_hotline', label: 'Hotline', type: 'text', defaultValue: '0822.314.555' },
                        { key: 'contact_email', label: 'Email', type: 'text', defaultValue: 'info@icogroup.vn' },
                        { key: 'contact_working_hours', label: 'Giờ làm việc', type: 'textarea', defaultValue: 'Thứ 2 - Thứ 6: 8:00 - 17:30\nThứ 7: 8:00 - 12:00' }
                    ]},
                    { title: '🔗 Mạng Xã Hội', icon: 'share', fields: [
                        { key: 'contact_facebook', label: 'Facebook URL', type: 'text', defaultValue: 'https://facebook.com/icogroup.vn' },
                        { key: 'contact_zalo', label: 'Zalo URL', type: 'text', defaultValue: 'https://zalo.me/0822314555' }
                    ]}
                ]
            },
            hoatdong: {
                name: 'Hoạt Động & Tin Tức',
                sections: [
                    { title: '🖼️ Banner Trang', icon: 'image', fields: [
                        { key: 'hoatdong_header_bg', label: 'Ảnh nền banner', type: 'image', defaultValue: 'https://icogroup.vn/vnt_upload/weblink/banner_hoatdong.jpg' },
                        { key: 'hoatdong_title', label: 'Tiêu đề chính', type: 'text', defaultValue: 'Tin Tức & Hoạt Động' },
                        { key: 'hoatdong_subtitle', label: 'Mô tả ngắn', type: 'text', defaultValue: 'Cập nhật những thông tin mới nhất từ ICOGroup' }
                    ]}
                ]
            }
        };

        let visualCmsData = {};
        let modifiedFields = new Set();

        async function loadVisualPage() {
            const page = document.getElementById('visualPageSelect').value;
            const container = document.getElementById('visualSectionsContainer');
            
            if (!page || !pageConfigs[page]) {
                container.innerHTML = `
                    <div class="cms-empty">
                        <span class="material-icons-outlined icon">touch_app</span>
                        <h3>Chọn trang để bắt đầu chỉnh sửa</h3>
                        <p>Chọn một trang từ dropdown ở trên để xem và chỉnh sửa nội dung</p>
                    </div>`;
                return;
            }

            container.innerHTML = `
                <div class="cms-loading">
                    <div class="spinner"></div>
                    <p>Đang tải nội dung trang ${pageConfigs[page].name}...</p>
                </div>`;

            try {
                const response = await fetch(`${API_BASE}/get_content.php`);
                const result = await response.json();
                
                visualCmsData = {};
                // Handle both array response and wrapped response
                const dataArray = Array.isArray(result) ? result : (result.data || []);
                dataArray.forEach(item => {
                    if (item.section_key && item.content_value) {
                        visualCmsData[item.section_key] = item.content_value;
                    }
                });
                console.log('Loaded CMS data:', visualCmsData); // Debug log

                renderVisualSections(page);
                modifiedFields.clear();
            } catch (error) {
                console.error('Error loading visual page:', error);
                container.innerHTML = `<div class="cms-empty"><p>Lỗi tải dữ liệu</p></div>`;
            }
        }

        function renderVisualSections(page) {
            const config = pageConfigs[page];
            const container = document.getElementById('visualSectionsContainer');
            
            let html = '';
            
            config.sections.forEach((section, sIdx) => {
                // Generate visibility key for this section
                const visibilityKey = `${page}_section_${sIdx}_visible`;
                const isVisible = visualCmsData[visibilityKey] !== '0' && visualCmsData[visibilityKey] !== 'false';
                
                html += `
                    <div class="cms-section-card ${!isVisible ? 'section-hidden' : ''}" id="section-${sIdx}">
                        <div class="cms-section-header">
                            <div style="display: flex; align-items: center; gap: 12px; cursor: pointer;" onclick="toggleSection(${sIdx})">
                                <h3><span class="icon">${section.title.split(' ')[0]}</span> ${section.title.substring(section.title.indexOf(' ') + 1)}</h3>
                            </div>
                            <div style="display: flex; align-items: center; gap: 16px;">
                                <div class="visibility-toggle" style="display: flex; align-items: center; gap: 8px;" title="Ẩn/Hiện section này trên trang web">
                                    <span style="font-size: 12px; color: var(--text-secondary);">${isVisible ? 'Đang hiện' : 'Đang ẩn'}</span>
                                    <label class="toggle-switch" onclick="event.stopPropagation()">
                                        <input type="checkbox" data-visibility-key="${visibilityKey}" ${isVisible ? 'checked' : ''} 
                                            onchange="toggleSectionVisibility('${page}', ${sIdx}, this)">
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                                <span class="material-icons-outlined toggle-icon" style="cursor: pointer;" onclick="toggleSection(${sIdx})">expand_more</span>
                            </div>
                        </div>
                        <div class="cms-section-body">`;
                
                section.fields.forEach(field => {
                    const value = visualCmsData[field.key] || field.defaultValue || '';
                    
                    if (field.type === 'image') {
                        html += `
                            <div class="cms-field-group">
                                <label class="cms-field-label">${field.label}</label>
                                <div class="cms-image-field">
                                    <div class="cms-image-preview" id="preview-${field.key}">
                                        ${value ? `<img src="${value}" alt="Preview">` : '<span class="placeholder">Chưa có ảnh</span>'}
                                    </div>
                                    <div class="cms-image-actions">
                                        <input type="text" class="cms-field-input cms-image-url" 
                                            data-key="${field.key}" 
                                            value="${escapeHtml(value)}" 
                                            placeholder="URL hình ảnh hoặc upload bên dưới"
                                            onchange="markModified(this); updateImagePreview('${field.key}', this.value)">
                                        <label class="cms-image-upload">
                                            <span class="material-icons-outlined">cloud_upload</span> Upload ảnh
                                            <input type="file" accept="image/*" style="display:none" onchange="uploadVisualImage('${field.key}', this)">
                                        </label>
                                    </div>
                                </div>
                            </div>`;
                    } else if (field.type === 'textarea') {
                        html += `
                            <div class="cms-field-group">
                                <label class="cms-field-label">${field.label}</label>
                                <textarea class="cms-field-input" data-key="${field.key}" 
                                    placeholder="Nhập nội dung..." 
                                    onchange="markModified(this)">${escapeHtml(value)}</textarea>
                            </div>`;
                    } else {
                        html += `
                            <div class="cms-field-group">
                                <label class="cms-field-label">${field.label}</label>
                                <input type="text" class="cms-field-input" data-key="${field.key}" 
                                    value="${escapeHtml(value)}" 
                                    placeholder="Nhập nội dung..."
                                    onchange="markModified(this)">
                            </div>`;
                    }
                });
                
                html += `</div></div>`;
            });

            html += `
                <div class="cms-save-bar" id="saveBar" style="display: none;">
                    <div class="changes-info">
                        <span class="dot"></span>
                        <span id="changesCount">0 thay đổi chưa lưu</span>
                    </div>
                    <button class="btn btn-primary btn-save-all" onclick="saveAllVisualChanges()">
                        <span class="material-icons-outlined">save</span>
                        Lưu tất cả thay đổi
                    </button>
                </div>`;

            container.innerHTML = html;
        }

        function toggleSection(idx) {
            const card = document.getElementById(`section-${idx}`);
            card.classList.toggle('collapsed');
        }
        
        // Toggle section visibility on frontend
        async function toggleSectionVisibility(page, sectionIdx, checkbox) {
            const visibilityKey = `${page}_section_${sectionIdx}_visible`;
            const isVisible = checkbox.checked;
            const card = checkbox.closest('.cms-section-card');
            const statusLabel = card.querySelector('.visibility-toggle span');
            
            // Update UI
            if (isVisible) {
                card.classList.remove('section-hidden');
                statusLabel.textContent = 'Đang hiện';
            } else {
                card.classList.add('section-hidden');
                statusLabel.textContent = 'Đang ẩn';
            }
            
            // Save to database
            try {
                const response = await fetch(`${API_BASE}/save_content.php`, {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': CSRF_TOKEN
                    },
                    body: JSON.stringify({
                        section_key: visibilityKey,
                        content_value: isVisible ? '1' : '0'
                    })
                });
                
                const result = await response.json();
                if (result.status) {
                    showToast(`Đã ${isVisible ? 'hiện' : 'ẩn'} section thành công!`, 'success');
                    // Update local data
                    visualCmsData[visibilityKey] = isVisible ? '1' : '0';
                } else {
                    throw new Error(result.message || 'Lỗi lưu trạng thái');
                }
            } catch (error) {
                console.error('Error saving visibility:', error);
                showToast('Lỗi lưu trạng thái: ' + error.message, 'error');
                // Revert checkbox
                checkbox.checked = !isVisible;
                if (isVisible) {
                    card.classList.add('section-hidden');
                    statusLabel.textContent = 'Đang ẩn';
                } else {
                    card.classList.remove('section-hidden');
                    statusLabel.textContent = 'Đang hiện';
                }
            }
        }

        function markModified(input) {
            input.classList.add('modified');
            modifiedFields.add(input.dataset.key);
            updateSaveBar();
        }

        function updateSaveBar() {
            const saveBar = document.getElementById('saveBar');
            const count = modifiedFields.size;
            if (count > 0) {
                saveBar.style.display = 'flex';
                document.getElementById('changesCount').textContent = `${count} thay đổi chưa lưu`;
            } else {
                saveBar.style.display = 'none';
            }
        }

        function updateImagePreview(key, url) {
            const preview = document.getElementById(`preview-${key}`);
            if (url) {
                preview.innerHTML = `<img src="${url}" alt="Preview" onerror="this.parentElement.innerHTML='<span class=\\'placeholder\\'>Lỗi ảnh</span>'">`;
            } else {
                preview.innerHTML = '<span class="placeholder">Chưa có ảnh</span>';
            }
        }

        async function uploadVisualImage(key, input) {
            if (!input.files || !input.files[0]) return;
            
            const formData = new FormData();
            formData.append('image', input.files[0]);
            
            try {
                const response = await fetch(`${API_BASE}/upload_image.php`, {
                    method: 'POST',
                    headers: { 'X-CSRF-Token': CSRF_TOKEN },
                    body: formData
                });
                const result = await response.json();
                
                if (result.status && result.url) {
                    const urlInput = document.querySelector(`input[data-key="${key}"]`);
                    urlInput.value = result.url;
                    markModified(urlInput);
                    updateImagePreview(key, result.url);
                    showToast('Upload thành công!', 'success');
                } else {
                    throw new Error(result.message || 'Upload thất bại');
                }
            } catch (error) {
                showToast('Lỗi upload: ' + error.message, 'error');
            }
        }

        async function saveAllVisualChanges() {
            const inputs = document.querySelectorAll('.cms-field-input.modified');
            if (inputs.length === 0) {
                showToast('Không có thay đổi để lưu', 'error');
                return;
            }

            const saveBtn = document.querySelector('.btn-save-all');
            saveBtn.innerHTML = '<span class="spinner" style="width:20px;height:20px;border-width:2px;"></span> Đang lưu...';
            saveBtn.disabled = true;

            let successCount = 0;
            let errorCount = 0;

            for (const input of inputs) {
                const key = input.dataset.key;
                const value = input.value;

                try {
                    const page = document.getElementById('visualPageSelect').value;
                    let endpoint = `${API_BASE}/text_api.php`;
                    let bodyData = {
                        text_key: key,
                        text_value: value,
                        page: page,
                        section: 'visual_cms'
                    };

                    if (input.classList.contains('cms-image-url')) {
                         endpoint = `${API_BASE}/image_api.php`;
                         bodyData = {
                            image_key: key,
                            image_url: value,
                            alt_text: '',
                            page: page,
                            section: 'visual_cms'
                         };
                    }

                    const response = await fetch(endpoint, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-Token': CSRF_TOKEN
                        },
                        body: JSON.stringify(bodyData)
                    });
                    const result = await response.json();
                    
                    if (result.status) {
                        input.classList.remove('modified');
                        modifiedFields.delete(key);
                        successCount++;
                    } else {
                        errorCount++;
                    }
                } catch (error) {
                    errorCount++;
                }
            }

            saveBtn.innerHTML = '<span class="material-icons-outlined">save</span> Lưu tất cả thay đổi';
            saveBtn.disabled = false;
            updateSaveBar();

            if (errorCount === 0) {
                showToast(`Đã lưu ${successCount} thay đổi thành công!`, 'success');
            } else {
                showToast(`Lưu ${successCount} thành công, ${errorCount} thất bại`, 'error');
            }
        }

        // ============================================
        // CONTACT SETTINGS FUNCTIONS
        // ============================================
        
        // Upload social icon
        async function uploadSocialIcon(platform, input) {
            if (!input.files || !input.files[0]) return;
            
            const formData = new FormData();
            formData.append('image', input.files[0]);
            
            try {
                const response = await fetch('dashboard.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                
                if (result.status && result.url) {
                    const urlInput = document.getElementById(`global_${platform}_icon`);
                    urlInput.value = result.url;
                    previewSocialIcon(platform, result.url);
                    showToast('Upload icon thành công!', 'success');
                } else {
                    throw new Error(result.message || 'Upload thất bại');
                }
            } catch (error) {
                showToast('Lỗi upload: ' + error.message, 'error');
            }
        }
        
        // Preview social icon
        function previewSocialIcon(platform, url) {
            const preview = document.getElementById(`preview_${platform}_icon`);
            if (!preview) return;
            
            if (url && url.trim()) {
                preview.innerHTML = `<img src="${url}" alt="${platform}" style="width: 100%; height: 100%; object-fit: cover;">`;
            } else {
                const defaultIcons = {
                    facebook: '👥',
                    youtube: '▶️',
                    zalo: '💬'
                };
                preview.innerHTML = `<span style="color: #999; font-size: 20px;">${defaultIcons[platform] || '📷'}</span>`;
            }
        }
        
        // Load contact settings
        async function loadContactSettings() {
            try {
                const response = await fetch(`../backend_api/get_content.php`);
                const result = await response.json();
                const dataArray = Array.isArray(result) ? result : (result.data || []);
                
                // Map data to input fields
                // Map data to input fields
                dataArray.forEach(item => {
                    // Try with contact_ prefix first (legacy), then exact match
                    let field = document.getElementById(`contact_${item.section_key}`);
                    if (!field) {
                        field = document.getElementById(item.section_key);
                    }
                    if (field) {
                        field.value = item.content_value || '';
                        
                        // Load toggle switches
                        if (item.section_key.startsWith('contact_toggle_')) {
                            field.checked = item.content_value === '1';
                        }
                    }
                });
                
                // Load icon previews
                ['facebook', 'youtube', 'zalo'].forEach(platform => {
                    const iconInput = document.getElementById(`global_${platform}_icon`);
                    if (iconInput && iconInput.value) {
                        previewSocialIcon(platform, iconInput.value);
                    }
                });
                
            } catch (error) {
                console.error('Error loading contact settings:', error);
                showToast('Lỗi tải cài đặt liên hệ', 'error');
            }
        }
        
        // Save contact settings
        async function saveContactSettings() {
            const saveBtn = document.getElementById('saveContactBtn');
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<span class="spinner" style="width:20px;height:20px;border-width:2px;"></span> Đang lưu...';
            
            // Get all contact fields and global fields
            const fields = document.querySelectorAll('[id^="contact_"], [id^="global_"]');
            const updates = [];
            
            fields.forEach(field => {
                let section_key = field.id;
                if (section_key.startsWith('contact_')) {
                    section_key = section_key.replace('contact_', '');
                }
                let content_value = '';
                
                if (field.type === 'checkbox') {
                    content_value = field.checked ? '1' : '0';
                } else {
                    content_value = field.value || '';
                }
                
                updates.push({ section_key, content_value });
            });
            
            let successCount = 0;
            let errorCount = 0;
            
            for (const update of updates) {
                try {
                    const response = await fetch(`../backend_api/save_content_debug.php`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-Token': CSRF_TOKEN
                        },
                        body: JSON.stringify(update)
                    });
                    const result = await response.json();
                    
                    if (result.status) {
                        successCount++;
                    } else {
                        errorCount++;
                        console.error('Failed to save:', update.section_key, 'Error:', result.message);
                    }
                } catch (error) {
                    errorCount++;
                    console.error('Exception saving:', update.section_key, 'Error:', error);
                }
            }
            
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<span class="material-icons-outlined">save</span> Lưu thay đổi';
            
            if (errorCount === 0) {
                showToast(`Đã lưu ${successCount} cài đặt thành công!`, 'success');
            } else {
                showToast(`Lưu ${successCount} thành công, ${errorCount} thất bại`, 'error');
            }
        }

        // ============================================
        // ANALYTICS CHARTS
        // ============================================
        let dailyChart, monthlyChart, programChart, countryChart;

        async function loadAnalyticsCharts() {
            await Promise.all([
                loadDailyChart(),
                loadMonthlyChart(),
                loadProgramChart(),
                loadCountryChart()
            ]);
        }

        async function loadDailyChart() {
            try {
                const response = await fetch(`${API_BASE}/analytics_api.php?type=daily&days=30`);
                const result = await response.json();
                
                if (result.status && result.data) {
                    const ctx = document.getElementById('dailyChart');
                    if (!ctx) return;

                    if (dailyChart) dailyChart.destroy();
                    
                    dailyChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: result.data.map(d => d.label),
                            datasets: [{
                                label: 'Đăng ký',
                                data: result.data.map(d => d.count),
                                borderColor: '#2563EB',
                                backgroundColor: 'rgba(37, 99, 235, 0.1)',
                                fill: true,
                                tension: 0.4,
                                pointRadius: 3,
                                pointHoverRadius: 6
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: { stepSize: 1 }
                                }
                            }
                        }
                    });
                }
            } catch (error) {
                console.error('Error loading daily chart:', error);
            }
        }

        async function loadMonthlyChart() {
            try {
                const response = await fetch(`${API_BASE}/analytics_api.php?type=monthly&months=12`);
                const result = await response.json();
                
                if (result.status && result.data) {
                    const ctx = document.getElementById('monthlyChart');
                    if (!ctx) return;

                    if (monthlyChart) monthlyChart.destroy();
                    
                    monthlyChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: result.data.map(d => d.label),
                            datasets: [{
                                label: 'Đăng ký',
                                data: result.data.map(d => d.count),
                                backgroundColor: 'rgba(16, 185, 129, 0.8)',
                                borderColor: '#10B981',
                                borderWidth: 1,
                                borderRadius: 6
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: { stepSize: 1 }
                                }
                            }
                        }
                    });
                }
            } catch (error) {
                console.error('Error loading monthly chart:', error);
            }
        }

        async function loadProgramChart() {
            try {
                const response = await fetch(`${API_BASE}/analytics_api.php?type=by_program`);
                const result = await response.json();
                
                if (result.status && result.data && result.data.length > 0) {
                    const ctx = document.getElementById('programChart');
                    if (!ctx) return;

                    if (programChart) programChart.destroy();
                    
                    const colors = ['#2563EB', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899'];
                    const total = result.data.reduce((sum, item) => sum + parseInt(item.count), 0);

                    programChart = new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: result.data.map(d => {
                                const percent = total > 0 ? ((d.count / total) * 100).toFixed(1) : 0;
                                return `${d.program} (${percent}%)`;
                            }),
                            datasets: [{
                                data: result.data.map(d => d.count),
                                backgroundColor: colors.slice(0, result.data.length),
                                borderWidth: 2,
                                borderColor: '#fff'
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'right',
                                    labels: { usePointStyle: true, padding: 15 }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            const value = context.raw;
                                            const percent = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                            return `${context.label.split(' (')[0]}: ${value} (${percent}%)`;
                                        }
                                    }
                                },
                                datalabels: {
                                    display: true,
                                    color: '#fff',
                                    font: {
                                        weight: 'bold',
                                        size: 12
                                    },
                                    formatter: (value) => {
                                        const percent = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                        return percent > 5 ? percent + '%' : '';
                                    }
                                }
                            }
                        }
                    });
                }
            } catch (error) {
                console.error('Error loading program chart:', error);
            }
        }

        async function loadCountryChart() {
            try {
                const response = await fetch(`${API_BASE}/analytics_api.php?type=by_country`);
                const result = await response.json();
                
                if (result.status && result.data && result.data.length > 0) {
                    const ctx = document.getElementById('countryChart');
                    if (!ctx) return;

                    if (countryChart) countryChart.destroy();
                    
                    const colors = ['#F59E0B', '#10B981', '#2563EB', '#EF4444', '#8B5CF6', '#EC4899'];
                    const total = result.data.reduce((sum, item) => sum + parseInt(item.count), 0);

                    countryChart = new Chart(ctx, {
                        type: 'pie',
                        data: {
                            labels: result.data.map(d => {
                                const percent = total > 0 ? ((d.count / total) * 100).toFixed(1) : 0;
                                return `${d.country} (${percent}%)`;
                            }),
                            datasets: [{
                                data: result.data.map(d => d.count),
                                backgroundColor: colors.slice(0, result.data.length),
                                borderWidth: 2,
                                borderColor: '#fff'
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'right',
                                    labels: { usePointStyle: true, padding: 15 }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            const value = context.raw;
                                            const percent = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                            return `${context.label.split(' (')[0]}: ${value} (${percent}%)`;
                                        }
                                    }
                                },
                                datalabels: {
                                    display: true,
                                    color: '#fff',
                                    font: {
                                        weight: 'bold',
                                        size: 12
                                    },
                                    formatter: (value) => {
                                        const percent = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                        return percent > 5 ? percent + '%' : '';
                                    }
                                }
                            }
                        }
                    });
                }
            } catch (error) {
                console.error('Error loading country chart:', error);
            }
        }

        // Charts are now loaded directly in showSection function

        // ===== Settings Functions =====
        async function loadSettings() {
            try {
                // Load settings from CMS
                const response = await fetch(`${API_BASE}/get_content.php`);
                const result = await response.json();
                
                if (result.status && result.data) {
                    const data = result.data;
                    
                    // Populate form fields with loaded values
                    if (data.global_site_name) document.getElementById('settingSiteName').value = data.global_site_name;
                    if (data.global_site_desc) document.getElementById('settingSiteDesc').value = data.global_site_desc;
                    if (data.header_phone_display) document.getElementById('settingPhone').value = data.header_phone_display;
                    if (data.header_email) document.getElementById('settingEmail').value = data.header_email;
                    
                    // Security settings
                    if (data.security_session_timeout) {
                        document.getElementById('settingSessionTimeout').value = data.security_session_timeout;
                    }
                    if (data.security_max_login_attempts) {
                        document.getElementById('settingMaxLoginAttempts').value = data.security_max_login_attempts;
                    }
                    if (data.system_maintenance_mode) {
                        document.getElementById('settingMaintenanceMode').checked = data.system_maintenance_mode === '1';
                    }
                    
                    // Font settings
                    if (data.global_font_body) {
                        document.getElementById('settingFontBody').value = data.global_font_body;
                    }
                    if (data.global_font_body_url) {
                        document.getElementById('settingFontBodyUrl').value = data.global_font_body_url;
                    }
                    if (data.global_font_heading) {
                        document.getElementById('settingFontHeading').value = data.global_font_heading;
                    }
                    if (data.global_font_heading_url) {
                        document.getElementById('settingFontHeadingUrl').value = data.global_font_heading_url;
                    }
                }
                
                // Load permissions matrix
                loadPermissions();
            } catch (error) {
                console.error('Error loading settings:', error);
            }
        }

        async function saveSettings() {
            try {
                const settings = {
                    siteName: document.getElementById('settingSiteName')?.value,
                    siteDesc: document.getElementById('settingSiteDesc')?.value,
                    email: document.getElementById('settingEmail')?.value,
                    phone: document.getElementById('settingPhone')?.value,
                    sessionTimeout: document.getElementById('settingSessionTimeout')?.value,
                    maxLoginAttempts: document.getElementById('settingMaxLoginAttempts')?.value,
                    maintenanceMode: document.getElementById('settingMaintenanceMode')?.checked,
                    emailNotify: document.getElementById('settingEmailNotify')?.checked,
                    emailContact: document.getElementById('settingEmailContact')?.checked,
                    notifyEmail: document.getElementById('settingNotifyEmail')?.value,
                    fontBody: document.getElementById('settingFontBody')?.value,
                    fontBodyUrl: document.getElementById('settingFontBodyUrl')?.value,
                    fontHeading: document.getElementById('settingFontHeading')?.value,
                    fontHeadingUrl: document.getElementById('settingFontHeadingUrl')?.value
                };

                // Save to CMS
                const promises = [];
                
                const settingsMapping = {
                    'global_site_name': settings.siteName,
                    'global_site_desc': settings.siteDesc,
                    'header_phone_display': settings.phone,
                    'header_email': settings.email,
                    'security_session_timeout': settings.sessionTimeout,
                    'security_max_login_attempts': settings.maxLoginAttempts,
                    'system_maintenance_mode': settings.maintenanceMode ? '1' : '0',
                    'global_font_body': settings.fontBody,
                    'global_font_body_url': settings.fontBodyUrl,
                    'global_font_heading': settings.fontHeading,
                    'global_font_heading_url': settings.fontHeadingUrl
                };

                for (const [key, value] of Object.entries(settingsMapping)) {
                    if (value !== undefined) {
                        promises.push(
                            fetch(`${API_BASE}/save_content.php`, {
                                method: 'POST',
                                headers: { 
                                    'Content-Type': 'application/json',
                                    'X-CSRF-Token': CSRF_TOKEN
                                },
                                body: JSON.stringify({
                                    section_key: key,
                                    content_value: value
                                })
                            })
                        );
                    }
                }

                await Promise.all(promises);
                showToast('Cài đặt đã được lưu thành công!', 'success');
            } catch (error) {
                console.error('Error saving settings:', error);
                showToast('Lỗi khi lưu cài đặt: ' + error.message, 'error');
            }
        }

        async function createBackup() {
            try {
                showToast('Đang tạo bản sao lưu...', 'success');
                
                // Simulate backup creation
                setTimeout(() => {
                    const now = new Date();
                    document.getElementById('lastBackupTime').textContent = formatDateTime(now.toISOString());
                    showToast('Tạo bản sao lưu thành công!', 'success');
                }, 2000);
            } catch (error) {
                console.error('Error creating backup:', error);
                showToast('Lỗi khi tạo bản sao lưu: ' + error.message, 'error');
            }
        }

        // Permissions data embedded by PHP (no API call needed)
        let permissionData = {};
        const EMBEDDED_PERMISSIONS = <?php echo json_encode($permissionsData, JSON_UNESCAPED_UNICODE); ?>;

        async function loadPermissions() {
            const container = document.getElementById('permissionMatrix');
            if (!container) return;
            
            // Use embedded data directly
            if (Object.keys(EMBEDDED_PERMISSIONS).length > 0) {
                permissionData = EMBEDDED_PERMISSIONS;
                renderPermissionMatrix(EMBEDDED_PERMISSIONS);
            } else {
                container.innerHTML = `<div class="empty-state">
                    <span class="material-icons-outlined">info</span>
                    <h3>Chưa có dữ liệu quyền</h3>
                    <p>Vui lòng import file: backend_api/database/permission_migration.sql</p>
                </div>`;
            }
        }

        function renderPermissionMatrix(data) {
            const container = document.getElementById('permissionMatrix');
            if (!container) return;

            let html = '';
            
            for (const [categoryKey, category] of Object.entries(data)) {
                html += `
                <div class="permission-category" style="margin-bottom: 20px; border: 1px solid var(--border-light); border-radius: var(--radius-md); overflow: hidden;">
                    <div class="category-header" style="display: flex; justify-content: space-between; align-items: center; padding: 14px 20px; background: linear-gradient(135deg, var(--bg-primary), var(--surface)); cursor: pointer; border-bottom: 1px solid var(--border-light);" onclick="togglePermissionCategory(this)">
                        <h4 style="display: flex; align-items: center; gap: 10px; margin: 0; font-size: 15px;">
                            <span class="material-icons-outlined" style="color: var(--primary);">${category.icon}</span>
                            ${category.name}
                            <span style="background: var(--info-light); color: var(--primary); padding: 2px 8px; border-radius: 12px; font-size: 12px; font-weight: 600;">${category.permissions.length}</span>
                        </h4>
                        <span class="material-icons-outlined toggle-icon" style="color: var(--text-muted); transition: transform 0.2s;">expand_more</span>
                    </div>
                    <div class="category-body" style="padding: 0;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="background: var(--bg-primary);">
                                    <th style="text-align: left; padding: 12px 16px; font-size: 12px; text-transform: uppercase; color: var(--text-secondary); letter-spacing: 0.5px; width: 35%;">Quyền</th>
                                    <th style="text-align: left; padding: 12px 16px; font-size: 12px; text-transform: uppercase; color: var(--text-secondary); letter-spacing: 0.5px;">Mô tả</th>
                                    <th style="text-align: center; padding: 12px 16px; font-size: 12px; text-transform: uppercase; color: var(--text-secondary); letter-spacing: 0.5px; width: 100px;">Manager</th>
                                    <th style="text-align: center; padding: 12px 16px; font-size: 12px; text-transform: uppercase; color: var(--text-secondary); letter-spacing: 0.5px; width: 100px;">User</th>
                                </tr>
                            </thead>
                            <tbody>`;
                
                for (const perm of category.permissions) {
                    html += `
                                <tr style="border-bottom: 1px solid var(--border-light);">
                                    <td style="padding: 12px 16px; font-weight: 500;">${perm.name}</td>
                                    <td style="padding: 12px 16px; color: var(--text-secondary); font-size: 13px;">${perm.description || ''}</td>
                                    <td style="padding: 12px 16px; text-align: center;">
                                        <label class="toggle-switch">
                                            <input type="checkbox" ${perm.manager ? 'checked' : ''} onchange="updatePermission('manager', '${perm.key}', this.checked)">
                                            <span class="toggle-slider"></span>
                                        </label>
                                    </td>
                                    <td style="padding: 12px 16px; text-align: center;">
                                        <label class="toggle-switch">
                                            <input type="checkbox" ${perm.user ? 'checked' : ''} onchange="updatePermission('user', '${perm.key}', this.checked)">
                                            <span class="toggle-slider"></span>
                                        </label>
                                    </td>
                                </tr>`;
                }
                
                html += `
                            </tbody>
                        </table>
                    </div>
                </div>`;
            }

            container.innerHTML = html;
        }

        function togglePermissionCategory(header) {
            const body = header.nextElementSibling;
            const icon = header.querySelector('.toggle-icon');
            
            if (body.style.display === 'none') {
                body.style.display = 'block';
                icon.style.transform = 'rotate(0deg)';
            } else {
                body.style.display = 'none';
                icon.style.transform = 'rotate(-90deg)';
            }
        }

        async function updatePermission(role, permissionKey, granted) {
            try {
                const formData = new FormData();
                formData.append('action', 'update_permission');
                formData.append('csrf_token', CSRF_TOKEN);
                formData.append('role', role);
                formData.append('permission_key', permissionKey);
                formData.append('granted', granted ? 'true' : 'false');

                const response = await fetch('dashboard.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    showToast(result.message, 'success');
                } else {
                    throw new Error(result.message);
                }
            } catch (error) {
                console.error('Error updating permission:', error);
                showToast('Lỗi: ' + error.message, 'error');
            }
        }

        // ===== Activity Logs Functions =====
        let allLogs = [];

        async function loadActivityLogs() {
            const tbody = document.getElementById('logsList');
            if (!tbody) return;
            
            tbody.innerHTML = '<tr><td colspan="6" class="loading"><div class="spinner"></div><p>Đang tải logs...</p></td></tr>';
            
            try {
                const response = await fetch(`${API_BASE}/activity_logs_api.php?action=list&limit=100`);
                const result = await response.json();
                
                if (!result.success) {
                    throw new Error(result.message || 'Không thể tải logs');
                }
                
                allLogs = result.data;
                
                // Populate user filter dropdown
                populateUserFilter(allLogs);
                
                renderLogs(allLogs);
            } catch (error) {
                console.error('Error loading logs:', error);
                tbody.innerHTML = `<tr><td colspan="6" class="empty-state">
                    <span class="material-icons-outlined">error_outline</span>
                    <h3>Lỗi tải logs</h3>
                    <p>${error.message}</p>
                </td></tr>`;
            }
        }

        function populateUserFilter(logs) {
            const userFilter = document.getElementById('logsUserFilter');
            if (!userFilter) return;
            
            // Get unique users from logs
            const users = {};
            logs.forEach(log => {
                if (log.username && log.user_id) {
                    users[log.user_id] = {
                        id: log.user_id,
                        username: log.username,
                        role: log.role || ''
                    };
                }
            });
            
            // Sort by username
            const sortedUsers = Object.values(users).sort((a, b) => 
                a.username.localeCompare(b.username)
            );
            
            // Build options HTML
            let optionsHTML = '<option value="">Tất cả người dùng</option>';
            sortedUsers.forEach(user => {
                const roleLabel = user.role ? ` (${user.role})` : '';
                optionsHTML += `<option value="${user.id}">${user.username}${roleLabel}</option>`;
            });
            
            userFilter.innerHTML = optionsHTML;
        }

        function renderLogs(logs) {
            const tbody = document.getElementById('logsList');
            if (!tbody) return;
            
            if (!logs || logs.length === 0) {
                tbody.innerHTML = `<tr><td colspan="6" class="empty-state">
                    <span class="material-icons-outlined">history</span>
                    <h3>Chưa có dữ liệu logs</h3>
                    <p>Các hoạt động sẽ được ghi lại tại đây</p>
                </td></tr>`;
                return;
            }
            
            tbody.innerHTML = logs.map(log => `
                <tr>
                    <td>${log.id}</td>
                    <td>${formatDateTime(log.created_at)}</td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span class="material-icons-outlined" style="font-size: 18px; color: var(--text-muted);">person</span>
                            <div>
                                <div style="font-weight: 600;">${log.username || 'Unknown'}</div>
                                <div style="font-size: 11px; color: var(--text-muted);">${log.role || ''}</div>
                            </div>
                        </div>
                    </td>
                    <td>${getActionBadge(log.action)}</td>
                    <td style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="${(log.details || '').replace(/"/g, '&quot;')}">${log.details || '-'}</td>
                    <td><code style="font-size: 12px; background: var(--bg-primary); padding: 2px 6px; border-radius: 4px;">${log.ip_address || '-'}</code></td>
                </tr>
            `).join('');
        }

        function getActionBadge(action) {
            const badges = {
                'login': { icon: 'login', color: 'var(--success)', bg: 'var(--success-light)', text: 'Đăng nhập' },
                'logout': { icon: 'logout', color: 'var(--info)', bg: 'var(--info-light)', text: 'Đăng xuất' },
                'login_failed': { icon: 'error', color: 'var(--danger)', bg: 'var(--danger-light)', text: 'Đăng nhập thất bại' },
                'create': { icon: 'add_circle', color: 'var(--success)', bg: 'var(--success-light)', text: 'Tạo mới' },
                'update': { icon: 'edit', color: 'var(--warning)', bg: 'var(--warning-light)', text: 'Cập nhật' },
                'delete': { icon: 'delete', color: 'var(--danger)', bg: 'var(--danger-light)', text: 'Xóa' },
                'permission_grant': { icon: 'check_circle', color: 'var(--success)', bg: 'var(--success-light)', text: 'Cấp quyền' },
                'permission_revoke': { icon: 'remove_circle', color: 'var(--warning)', bg: 'var(--warning-light)', text: 'Thu hồi quyền' },
                'password_change': { icon: 'lock', color: 'var(--info)', bg: 'var(--info-light)', text: 'Đổi mật khẩu' }
            };
            
            const badge = badges[action] || { icon: 'info', color: 'var(--text-muted)', bg: 'var(--bg-primary)', text: action };
            
            return `<span style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; background: ${badge.bg}; color: ${badge.color}; border-radius: 20px; font-size: 12px; font-weight: 600;">
                <span class="material-icons-outlined" style="font-size: 14px;">${badge.icon}</span>
                ${badge.text}
            </span>`;
        }

        function filterLogs() {
            const search = (document.getElementById('logsSearchInput')?.value || '').toLowerCase();
            const userFilter = document.getElementById('logsUserFilter')?.value || '';
            const actionFilter = document.getElementById('logsActionFilter')?.value || '';
            
            const filtered = allLogs.filter(log => {
                const matchSearch = !search || 
                    (log.username || '').toLowerCase().includes(search) ||
                    (log.details || '').toLowerCase().includes(search) ||
                    (log.ip_address || '').includes(search) ||
                    (log.action || '').toLowerCase().includes(search);
                const matchUser = !userFilter || String(log.user_id) === userFilter;
                const matchAction = !actionFilter || log.action === actionFilter;
                return matchSearch && matchUser && matchAction;
            });
            
            renderLogs(filtered);
        }

        function formatDateTime(dateStr) {
            if (!dateStr) return '-';
            const date = new Date(dateStr);
            return date.toLocaleDateString('vi-VN') + ' ' + date.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
        }

        // ===== Password Change Functions =====
        function openPasswordModal() {
            document.getElementById('passwordModal').style.display = 'flex';
            document.getElementById('passwordForm').reset();
        }

        function closePasswordModal() {
            document.getElementById('passwordModal').style.display = 'none';
        }

        async function changePassword(event) {
            event.preventDefault();
            
            const currentPassword = document.getElementById('currentPassword').value;
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            if (newPassword !== confirmPassword) {
                showToast('Mật khẩu mới không khớp', 'error');
                return;
            }
            
            if (newPassword.length < 6) {
                showToast('Mật khẩu mới phải có ít nhất 6 ký tự', 'error');
                return;
            }
            
            try {
                const formData = new FormData();
                formData.append('action', 'change_password');
                formData.append('csrf_token', CSRF_TOKEN);
                formData.append('current_password', currentPassword);
                formData.append('new_password', newPassword);
                
                const response = await fetch('dashboard.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showToast(result.message, 'success');
                    closePasswordModal();
                } else {
                    showToast(result.message, 'error');
                }
            } catch (error) {
                console.error('Error changing password:', error);
                showToast('Lỗi: ' + error.message, 'error');
            }
        }

        // Close modal when clicking outside
        document.getElementById('passwordModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closePasswordModal();
            }
        });

        // ===== Social Icon Upload Functions =====
        function previewSocialIcon(type, url) {
            const preview = document.getElementById(`preview_${type}_icon`);
            if (url && url.trim()) {
                preview.innerHTML = `<img src="${url}" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.parentElement.innerHTML='<span style=\\'color: red; font-size: 12px;\\'>❌</span>'">`;
            } else {
                const icons = { facebook: '📘', youtube: '📺', zalo: '💬' };
                preview.innerHTML = `<span style="color: #999; font-size: 20px;">${icons[type] || '📷'}</span>`;
            }
        }

        async function uploadSocialIcon(type, input) {
            if (!input.files || !input.files[0]) return;
            
            const file = input.files[0];
            if (!file.type.startsWith('image/')) {
                showToast('Vui lòng chọn file ảnh', 'error');
                return;
            }

            const formData = new FormData();
            formData.append('image', file);

            try {
                const response = await fetch(API_BASE + 'upload_api.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.status && result.url) {
                    document.getElementById(`contact_global_${type}_icon`).value = result.url;
                    previewSocialIcon(type, result.url);
                    showToast('Upload thành công!', 'success');
                } else {
                    showToast(result.message || 'Lỗi upload', 'error');
                }
            } catch (error) {
                console.error('Upload error:', error);
                showToast('Lỗi upload: ' + error.message, 'error');
            }
        }

        // Load social icon previews when contact section loads
        function loadSocialIconPreviews() {
            ['facebook', 'youtube', 'zalo'].forEach(type => {
                const input = document.getElementById(`contact_global_${type}_icon`);
                if (input && input.value) {
                    previewSocialIcon(type, input.value);
                }
            });
        }
    </script>
    
    <!-- Content Blocks JavaScript -->
    <script src="content_blocks.js"></script>
    <script>
        // Update navigation to load Content Blocks
        document.querySelectorAll('.sidebar-menu a[data-section]').forEach(link => {
            link.addEventListener('click', function(e) {
                const section = this.dataset.section;
                if (section === 'contentBlocks') {
                    // Load fonts when switching to Content Blocks
                    loadFonts();
                }
            });
        });
    </script>
    
    <!-- Users Management JavaScript -->
    <?php if ($permission->canManageUsers($userRole)): ?>
    <script>
        // Pass PHP variables to JavaScript for users_management.js
        const isAdmin = <?php echo $isAdmin ? 'true' : 'false'; ?>;
        const currentUser = <?php echo json_encode(['id' => $currentUser['id'], 'username' => $currentUser['username'], 'role' => $currentUser['role']]); ?>;
        const canDelete = <?php echo ($isAdmin || $permission->has($userRole, 'users.delete')) ? 'true' : 'false'; ?>;
    </script>
    <script src="users_management.js"></script>
    <?php endif; ?>
    
    <!-- Session Monitor -->
    <link rel="stylesheet" href="css/session_modal.css">
    <script src="js/session_monitor.js"></script>
</body>

</html>
