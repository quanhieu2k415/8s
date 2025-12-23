<?php
/**
 * Admin Users Management Page
 */

require_once __DIR__ . '/includes/auth_check.php';

use App\Services\Permission;

$permission = Permission::getInstance();
$userRole = $currentUser['role'] ?? 'user';

// Check permission
if (!$permission->canManageUsers($userRole)) {
    header('Location: dashboard.php');
    exit;
}

$canCreateAdmin = $permission->check($userRole, 'users.create_admin');
$canCreateManager = $permission->check($userRole, 'users.create_manager');
$canCreateUser = $permission->check($userRole, 'users.create_user');
$canDelete = $permission->canDeleteUsers($userRole);
$isAdmin = $userRole === 'admin';
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Tài khoản - ICOGroup Admin</title>
    <meta name="csrf-token" content="<?php echo htmlspecialchars($csrfToken); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="../logo.ico">
    <style>
        :root {
            --primary: #2563EB;
            --primary-dark: #1E3A5F;
            --primary-light: #3B82F6;
            --accent: #F59E0B;
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

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: var(--bg-primary); min-height: 100vh; color: var(--text-primary); }

        /* Sidebar */
        .sidebar { position: fixed; left: 0; top: 0; width: var(--sidebar-width); height: 100vh; background: var(--bg-sidebar); color: var(--text-white); z-index: 100; display: flex; flex-direction: column; }
        .sidebar-header { padding: 24px; border-bottom: 1px solid rgba(255, 255, 255, 0.08); }
        .sidebar-header img { height: 40px; filter: brightness(0) invert(1); margin-bottom: 12px; }
        .sidebar-header h2 { font-size: 18px; font-weight: 700; }
        .sidebar-header p { font-size: 13px; color: rgba(255, 255, 255, 0.6); margin-top: 4px; }
        .sidebar-menu { flex: 1; padding: 16px 0; overflow-y: auto; }
        .sidebar-menu a { display: flex; align-items: center; gap: 14px; padding: 14px 24px; color: rgba(255, 255, 255, 0.7); text-decoration: none; font-size: 14px; font-weight: 500; transition: all var(--transition-fast); border-left: 3px solid transparent; }
        .sidebar-menu a:hover { background: rgba(255, 255, 255, 0.06); color: var(--text-white); }
        .sidebar-menu a.active { background: rgba(37, 99, 235, 0.15); color: var(--text-white); border-left-color: var(--accent); }
        .sidebar-menu a .material-icons-outlined { font-size: 20px; }
        .sidebar-divider { height: 1px; background: rgba(255, 255, 255, 0.08); margin: 12px 24px; }
        .user-info { padding: 16px 24px; border-top: 1px solid rgba(255, 255, 255, 0.08); display: flex; align-items: center; gap: 12px; }
        .user-avatar { width: 40px; height: 40px; background: linear-gradient(135deg, #6366F1, #8B5CF6); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 16px; }
        .user-details p { font-size: 14px; font-weight: 600; }
        .user-details span { font-size: 12px; color: rgba(255, 255, 255, 0.6); }

        /* Main Content */
        .main-content { margin-left: var(--sidebar-width); padding: 32px; min-height: 100vh; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px; flex-wrap: wrap; gap: 16px; }
        .header h1 { font-size: 28px; font-weight: 800; color: var(--text-primary); }
        .header-actions { display: flex; gap: 12px; }

        /* Buttons */
        .btn { display: inline-flex; align-items: center; gap: 8px; padding: 12px 20px; border: none; border-radius: var(--radius-md); font-size: 14px; font-weight: 600; font-family: inherit; cursor: pointer; transition: all var(--transition-fast); }
        .btn-primary { background: var(--primary); color: var(--text-white); }
        .btn-primary:hover { background: var(--primary-light); transform: translateY(-1px); box-shadow: var(--shadow-md); }
        .btn-success { background: var(--success); color: var(--text-white); }
        .btn-success:hover { background: #059669; }
        .btn-danger { background: var(--danger); color: var(--text-white); }
        .btn-danger:hover { background: #DC2626; }
        .btn-outline { background: transparent; border: 2px solid var(--border-medium); color: var(--text-secondary); }
        .btn-outline:hover { border-color: var(--primary); color: var(--primary); }
        .btn-sm { padding: 8px 14px; font-size: 13px; }

        /* Stats Grid */
        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 32px; }
        .stat-card { background: var(--surface); padding: 24px; border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); border: 1px solid var(--border-light); display: flex; align-items: center; gap: 20px; transition: all var(--transition-normal); }
        .stat-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-lg); }
        .stat-icon { width: 56px; height: 56px; border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; }
        .stat-icon.admin { background: linear-gradient(135deg, #EF4444, #F87171); }
        .stat-icon.manager { background: linear-gradient(135deg, #F59E0B, #FBBF24); }
        .stat-icon.user { background: linear-gradient(135deg, #10B981, #34D399); }
        .stat-icon .material-icons-outlined { color: var(--text-white); font-size: 24px; }
        .stat-info h3 { font-size: 28px; font-weight: 800; color: var(--text-primary); }
        .stat-info p { color: var(--text-secondary); font-size: 13px; font-weight: 500; margin-top: 4px; }

        /* Table Container */
        .table-container { background: var(--surface); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); border: 1px solid var(--border-light); overflow: hidden; }
        .table-header { display: flex; justify-content: space-between; align-items: center; padding: 20px 24px; border-bottom: 1px solid var(--border-light); flex-wrap: wrap; gap: 16px; }
        .table-header h2 { font-size: 18px; font-weight: 700; color: var(--text-primary); }
        .table-filters { display: flex; gap: 12px; align-items: center; flex-wrap: wrap; }
        .search-box { display: flex; align-items: center; gap: 10px; background: var(--bg-primary); padding: 10px 16px; border-radius: var(--radius-md); border: 1px solid var(--border-light); }
        .search-box:focus-within { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1); }
        .search-box input { border: none; background: transparent; outline: none; font-size: 14px; width: 200px; font-family: inherit; }
        .search-box .material-icons-outlined { color: var(--text-muted); font-size: 20px; }
        .filter-select { padding: 10px 14px; border: 1px solid var(--border-light); border-radius: var(--radius-md); font-size: 14px; font-family: inherit; background: var(--surface); cursor: pointer; }

        /* Table */
        .table-wrapper { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 800px; }
        th, td { padding: 14px 20px; text-align: left; border-bottom: 1px solid var(--border-light); }
        th { background: var(--bg-primary); font-weight: 600; color: var(--text-secondary); font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; }
        tbody tr { transition: background var(--transition-fast); }
        tbody tr:hover { background: var(--surface-hover); }
        td { font-size: 14px; }

        /* Badges */
        .badge { display: inline-flex; align-items: center; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .badge-admin { background: var(--danger-light); color: #991B1B; }
        .badge-manager { background: var(--warning-light); color: #92400E; }
        .badge-user { background: var(--success-light); color: #065F46; }
        .badge-active { background: var(--success-light); color: #065F46; }
        .badge-inactive { background: var(--danger-light); color: #991B1B; }

        /* Action Buttons */
        .action-btns { display: flex; gap: 8px; }
        .action-btn { width: 36px; height: 36px; border: none; border-radius: var(--radius-sm); cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all var(--transition-fast); }
        .action-btn .material-icons-outlined { font-size: 18px; }
        .action-btn.edit { background: var(--info-light); color: var(--primary); }
        .action-btn.edit:hover { background: var(--primary); color: var(--text-white); }
        .action-btn.delete { background: var(--danger-light); color: var(--danger); }
        .action-btn.delete:hover { background: var(--danger); color: var(--text-white); }
        .action-btn.view { background: var(--success-light); color: var(--success); }
        .action-btn.view:hover { background: var(--success); color: var(--text-white); }

        /* Loading & Empty States */
        .loading, .empty-state { text-align: center; padding: 60px 20px; color: var(--text-secondary); }
        .spinner { width: 40px; height: 40px; border: 3px solid var(--border-light); border-top-color: var(--primary); border-radius: 50%; animation: spin 0.8s linear infinite; margin: 0 auto 16px; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .empty-state .material-icons-outlined { font-size: 64px; color: var(--border-medium); margin-bottom: 16px; }
        .empty-state h3 { font-size: 18px; color: var(--text-primary); margin-bottom: 8px; }

        /* Modal */
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
        .modal-body input, .modal-body select { width: 100%; padding: 12px 14px; border: 1px solid var(--border-light); border-radius: var(--radius-md); font-size: 14px; font-family: inherit; margin-bottom: 16px; transition: all var(--transition-fast); }
        .modal-body input:focus, .modal-body select:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1); }
        .modal-footer { padding: 16px 24px; border-top: 1px solid var(--border-light); display: flex; justify-content: flex-end; gap: 12px; }

        /* Toast */
        .toast { position: fixed; bottom: 32px; right: 32px; padding: 16px 24px; border-radius: var(--radius-md); color: var(--text-white); font-weight: 500; display: none; align-items: center; gap: 12px; box-shadow: var(--shadow-lg); animation: toastIn 0.3s ease; z-index: 9999; }
        .toast.show { display: flex; }
        .toast.success { background: var(--success); }
        .toast.error { background: var(--danger); }
        @keyframes toastIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

        /* Responsive */
        @media (max-width: 1200px) { .stats-grid { grid-template-columns: 1fr; } }
        @media (max-width: 992px) { .sidebar { transform: translateX(-100%); } .main-content { margin-left: 0; } }
        @media (max-width: 768px) { .header { flex-direction: column; align-items: flex-start; } .table-header { flex-direction: column; align-items: flex-start; } }
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
            <a href="dashboard.php" data-section="dashboard">
                <span class="material-icons-outlined">dashboard</span>
                <span>Dashboard</span>
            </a>
            <?php if ($permission->canManageUsers($userRole)): ?>
            <a href="users.php" class="active">
                <span class="material-icons-outlined">group</span>
                <span>Quản lý Tài khoản</span>
            </a>
            <?php endif; ?>
            <a href="dashboard.php#registrations">
                <span class="material-icons-outlined">people</span>
                <span>Đăng ký tư vấn</span>
            </a>
            <a href="dashboard.php#news">
                <span class="material-icons-outlined">article</span>
                <span>Tin tức</span>
            </a>
            <?php if ($permission->canManageCMS($userRole)): ?>
            <a href="dashboard.php#cms">
                <span class="material-icons-outlined">edit_note</span>
                <span>Quản lý nội dung</span>
            </a>
            <?php endif; ?>
            <?php if ($permission->canViewAllLogs($userRole)): ?>
            <a href="dashboard.php#logs">
                <span class="material-icons-outlined">history</span>
                <span>Activity Logs</span>
            </a>
            <?php endif; ?>
            <?php if ($permission->canAccessSettings($userRole)): ?>
            <div class="sidebar-divider"></div>
            <a href="dashboard.php#settings">
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
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div class="header">
            <h1>👥 Quản lý Tài khoản</h1>
            <div class="header-actions">
                <?php if ($canCreateUser || $canCreateManager || $canCreateAdmin): ?>
                <button class="btn btn-primary" onclick="openCreateModal()">
                    <span class="material-icons-outlined">person_add</span>
                    Thêm tài khoản
                </button>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($isAdmin): ?>
        <!-- Stats Cards -->
        <div class="stats-grid" id="statsGrid">
            <div class="stat-card">
                <div class="stat-icon admin">
                    <span class="material-icons-outlined">admin_panel_settings</span>
                </div>
                <div class="stat-info">
                    <h3 id="statAdmin">-</h3>
                    <p>Administrators</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon manager">
                    <span class="material-icons-outlined">supervisor_account</span>
                </div>
                <div class="stat-info">
                    <h3 id="statManager">-</h3>
                    <p>Managers</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon user">
                    <span class="material-icons-outlined">person</span>
                </div>
                <div class="stat-info">
                    <h3 id="statUser">-</h3>
                    <p>Users</p>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Users Table -->
        <div class="table-container">
            <div class="table-header">
                <h2>Danh sách tài khoản</h2>
                <div class="table-filters">
                    <div class="search-box">
                        <span class="material-icons-outlined">search</span>
                        <input type="text" id="searchInput" placeholder="Tìm kiếm..." onkeyup="debounceSearch()">
                    </div>
                    <?php if ($isAdmin): ?>
                    <select class="filter-select" id="filterRole" onchange="loadUsers()">
                        <option value="">Tất cả role</option>
                        <option value="admin">Admin</option>
                        <option value="manager">Manager</option>
                        <option value="user">User</option>
                    </select>
                    <?php endif; ?>
                    <button class="btn btn-outline btn-sm" onclick="loadUsers()">
                        <span class="material-icons-outlined">refresh</span>
                        Làm mới
                    </button>
                </div>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <?php if ($isAdmin): ?>
                            <th>Manager</th>
                            <?php endif; ?>
                            <th>Trạng thái</th>
                            <th>Đăng nhập cuối</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody id="usersList">
                        <tr><td colspan="8" class="loading"><div class="spinner"></div>Đang tải...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Create User Modal -->
    <div id="createModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Thêm tài khoản mới</h2>
                <button class="modal-close" onclick="closeModal('createModal')">
                    <span class="material-icons-outlined">close</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="createForm">
                    <label>Username *</label>
                    <input type="text" name="username" required placeholder="Nhập username">
                    
                    <label>Email</label>
                    <input type="email" name="email" placeholder="Nhập email">
                    
                    <label>Mật khẩu *</label>
                    <input type="password" name="password" required placeholder="Nhập mật khẩu" minlength="6">
                    
                    <label>Role *</label>
                    <select name="role" required>
                        <?php if ($canCreateUser): ?>
                        <option value="user">User</option>
                        <?php endif; ?>
                        <?php if ($canCreateManager): ?>
                        <option value="manager">Manager</option>
                        <?php endif; ?>
                        <?php if ($canCreateAdmin): ?>
                        <option value="admin">Admin</option>
                        <?php endif; ?>
                    </select>
                    
                    <?php if ($isAdmin): ?>
                    <label>Phòng ban</label>
                    <input type="text" name="department" placeholder="Nhập tên phòng ban">
                    
                    <label>Manager</label>
                    <select name="manager_id" id="managerSelect">
                        <option value="">-- Không có --</option>
                    </select>
                    <?php endif; ?>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" onclick="closeModal('createModal')">Hủy</button>
                <button class="btn btn-primary" onclick="createUser()">
                    <span class="material-icons-outlined">add</span>
                    Tạo tài khoản
                </button>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Chỉnh sửa tài khoản</h2>
                <button class="modal-close" onclick="closeModal('editModal')">
                    <span class="material-icons-outlined">close</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="editForm">
                    <input type="hidden" name="id" id="editUserId">
                    
                    <label>Username</label>
                    <input type="text" id="editUsername" disabled>
                    
                    <label>Email</label>
                    <input type="email" name="email" id="editEmail" placeholder="Nhập email">
                    
                    <?php if ($isAdmin): ?>
                    <label>Role</label>
                    <select name="role" id="editRole">
                        <option value="user">User</option>
                        <option value="manager">Manager</option>
                        <option value="admin">Admin</option>
                    </select>
                    
                    <label>Phòng ban</label>
                    <input type="text" name="department" id="editDepartment" placeholder="Nhập tên phòng ban">
                    
                    <label>Manager</label>
                    <select name="manager_id" id="editManagerSelect">
                        <option value="">-- Không có --</option>
                    </select>
                    <?php endif; ?>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" onclick="closeModal('editModal')">Hủy</button>
                <button class="btn btn-primary" onclick="updateUser()">
                    <span class="material-icons-outlined">save</span>
                    Lưu thay đổi
                </button>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="toast">
        <span class="material-icons-outlined" id="toastIcon">check_circle</span>
        <span id="toastMessage"></span>
    </div>

    <script>
        const API_BASE = '../backend_api/users_api.php';
        const isAdmin = <?php echo $isAdmin ? 'true' : 'false'; ?>;
        const canDelete = <?php echo $canDelete ? 'true' : 'false'; ?>;
        let searchTimeout;

        // Load data on page load
        document.addEventListener('DOMContentLoaded', () => {
            loadUsers();
            if (isAdmin) {
                loadStats();
                loadManagers();
            }
        });

        // Load user statistics
        async function loadStats() {
            try {
                const response = await fetch(`${API_BASE}?action=stats`);
                const data = await response.json();
                
                if (data.success) {
                    document.getElementById('statAdmin').textContent = data.data.admin || 0;
                    document.getElementById('statManager').textContent = data.data.manager || 0;
                    document.getElementById('statUser').textContent = data.data.user || 0;
                }
            } catch (error) {
                console.error('Error loading stats:', error);
            }
        }

        // Load managers for dropdown
        async function loadManagers() {
            try {
                const response = await fetch(`${API_BASE}?action=managers`);
                const data = await response.json();
                
                if (data.success) {
                    const select = document.getElementById('managerSelect');
                    const editSelect = document.getElementById('editManagerSelect');
                    
                    data.data.forEach(manager => {
                        const option = document.createElement('option');
                        option.value = manager.id;
                        option.textContent = `${manager.username} (${manager.email || 'No email'})`;
                        select.appendChild(option.cloneNode(true));
                        editSelect.appendChild(option);
                    });
                }
            } catch (error) {
                console.error('Error loading managers:', error);
            }
        }

        // Load users list
        async function loadUsers() {
            const tbody = document.getElementById('usersList');
            tbody.innerHTML = '<tr><td colspan="8" class="loading"><div class="spinner"></div>Đang tải...</td></tr>';

            try {
                const search = document.getElementById('searchInput').value;
                const role = isAdmin ? document.getElementById('filterRole').value : '';
                
                let url = `${API_BASE}?action=list`;
                if (search) url += `&search=${encodeURIComponent(search)}`;
                if (role) url += `&role=${role}`;

                const response = await fetch(url);
                const data = await response.json();

                if (!data.success) {
                    throw new Error(data.message);
                }

                if (data.data.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="8" class="empty-state">
                                <span class="material-icons-outlined">people_outline</span>
                                <h3>Không có tài khoản nào</h3>
                                <p>Bấm nút "Thêm tài khoản" để tạo mới</p>
                            </td>
                        </tr>
                    `;
                    return;
                }

                tbody.innerHTML = data.data.map(user => `
                    <tr>
                        <td>${user.id}</td>
                        <td><strong>${escapeHtml(user.username)}</strong></td>
                        <td>${user.email || '<span style="color: var(--text-muted);">Chưa có</span>'}</td>
                        <td><span class="badge badge-${user.role}">${user.role.toUpperCase()}</span></td>
                        ${isAdmin ? `<td>${user.manager_username || '<span style="color: var(--text-muted);">-</span>'}</td>` : ''}
                        <td>
                            <span class="badge ${user.is_active ? 'badge-active' : 'badge-inactive'}">
                                ${user.is_active ? 'Active' : 'Inactive'}
                            </span>
                        </td>
                        <td>${user.last_login ? formatDate(user.last_login) : '<span style="color: var(--text-muted);">Chưa đăng nhập</span>'}</td>
                        <td>
                            <div class="action-btns">
                                <button class="action-btn edit" onclick="openEditModal(${user.id})" title="Chỉnh sửa">
                                    <span class="material-icons-outlined">edit</span>
                                </button>
                                ${canDelete && user.id !== <?php echo $currentUser['id']; ?> ? `
                                <button class="action-btn delete" onclick="deleteUser(${user.id}, '${escapeHtml(user.username)}')" title="Xóa">
                                    <span class="material-icons-outlined">delete</span>
                                </button>
                                ` : ''}
                            </div>
                        </td>
                    </tr>
                `).join('');

            } catch (error) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="8" class="empty-state">
                            <span class="material-icons-outlined">error_outline</span>
                            <h3>Lỗi tải dữ liệu</h3>
                            <p>${error.message}</p>
                        </td>
                    </tr>
                `;
            }
        }

        // Debounce search
        function debounceSearch() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(loadUsers, 300);
        }

        // Open create modal
        function openCreateModal() {
            document.getElementById('createForm').reset();
            document.getElementById('createModal').style.display = 'block';
        }

        // Open edit modal
        async function openEditModal(id) {
            try {
                const response = await fetch(`${API_BASE}?action=get&id=${id}`);
                const data = await response.json();

                if (!data.success) {
                    throw new Error(data.message);
                }

                const user = data.data;
                document.getElementById('editUserId').value = user.id;
                document.getElementById('editUsername').value = user.username;
                document.getElementById('editEmail').value = user.email || '';
                
                if (isAdmin) {
                    document.getElementById('editRole').value = user.role;
                    document.getElementById('editDepartment').value = user.department || '';
                    document.getElementById('editManagerSelect').value = user.manager_id || '';
                }

                document.getElementById('editModal').style.display = 'block';
            } catch (error) {
                showToast(error.message, 'error');
            }
        }

        // Close modal
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // Create user
        async function createUser() {
            const form = document.getElementById('createForm');
            const formData = new FormData(form);
            const data = Object.fromEntries(formData);

            if (!data.username || !data.password) {
                showToast('Vui lòng điền đầy đủ thông tin', 'error');
                return;
            }

            try {
                const response = await fetch(`${API_BASE}?action=create`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    showToast('Tạo tài khoản thành công!', 'success');
                    closeModal('createModal');
                    loadUsers();
                    if (isAdmin) loadStats();
                } else {
                    throw new Error(result.message);
                }
            } catch (error) {
                showToast(error.message, 'error');
            }
        }

        // Update user
        async function updateUser() {
            const form = document.getElementById('editForm');
            const formData = new FormData(form);
            const data = Object.fromEntries(formData);

            try {
                const response = await fetch(`${API_BASE}?action=update`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    showToast('Cập nhật thành công!', 'success');
                    closeModal('editModal');
                    loadUsers();
                } else {
                    throw new Error(result.message);
                }
            } catch (error) {
                showToast(error.message, 'error');
            }
        }

        // Delete user
        async function deleteUser(id, username) {
            if (!confirm(`Bạn có chắc muốn xóa tài khoản "${username}"?\n\nHành động này không thể hoàn tác.`)) {
                return;
            }

            try {
                const response = await fetch(`${API_BASE}?action=delete&id=${id}`, {
                    method: 'DELETE'
                });

                const result = await response.json();

                if (result.success) {
                    showToast('Xóa tài khoản thành công!', 'success');
                    loadUsers();
                    if (isAdmin) loadStats();
                } else {
                    throw new Error(result.message);
                }
            } catch (error) {
                showToast(error.message, 'error');
            }
        }

        // Helper functions
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function formatDate(dateStr) {
            const date = new Date(dateStr);
            return date.toLocaleDateString('vi-VN') + ' ' + date.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
        }

        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            const toastMessage = document.getElementById('toastMessage');
            const toastIcon = document.getElementById('toastIcon');

            toast.className = `toast ${type} show`;
            toastMessage.textContent = message;
            toastIcon.textContent = type === 'success' ? 'check_circle' : 'error';

            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }

        // Close modal on outside click
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }
    </script>
</body>
</html>
