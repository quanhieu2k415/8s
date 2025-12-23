<!-- Users Management Section -->
<style>
#users {
    padding: 0;
    margin: 0;
}
#users .header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    flex-wrap: wrap;
    gap: 16px;
}
#users .header h1 {
    margin: 0;
    font-size: 28px;
    font-weight: 800;
    color: var(--text-primary);
    letter-spacing: -0.02em;
}
#users .stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    margin-bottom: 24px;
}
#users .stat-card {
    background: var(--surface);
    padding: 24px;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-sm);
    border: 1px solid var(--border-light);
    display: flex;
    align-items: center;
    gap: 20px;
    transition: all 0.25s ease;
}
#users .stat-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
    border-color: transparent;
}
#users .stat-icon {
    width: 56px;
    height: 56px;
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
}
#users .stat-icon .material-icons-outlined {
    color: white;
    font-size: 26px;
}
#users .stat-info h3 {
    font-size: 32px;
    font-weight: 800;
    color: var(--text-primary);
    letter-spacing: -0.02em;
    margin: 0;
}
#users .stat-info p {
    color: var(--text-secondary);
    font-size: 14px;
    font-weight: 500;
    margin: 4px 0 0 0;
}
@media (max-width: 1024px) {
    #users .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
@media (max-width: 640px) {
    #users .stats-grid {
        grid-template-columns: 1fr;
    }
    #users .header {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>
<section id="users" class="section-panel">
    <div class="header">
        <h1>👥 Quản lý Tài khoản</h1>
        <div class="header-actions">
            <?php if ($canCreateUser || $canCreateManager || $canCreateAdmin): ?>
            <button class="btn btn-outline" onclick="openImportModal()" title="Import từ file TXT">
                <span class="material-icons-outlined">upload_file</span>
                Import
            </button>
            <button class="btn btn-primary" onclick="openCreateUserModal()">
                <span class="material-icons-outlined">person_add</span>
                Thêm tài khoản
            </button>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($isAdmin): ?>
    <!-- Stats Cards -->
    <div id="usersStatsContainer" class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #EF4444, #F87171);">
                <span class="material-icons-outlined">admin_panel_settings</span>
            </div>
            <div class="stat-info">
                <h3 id="statAdminUsers">0</h3>
                <p>Administrators</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #F59E0B, #FBBF24);">
                <span class="material-icons-outlined">supervisor_account</span>
            </div>
            <div class="stat-info">
                <h3 id="statManagerUsers">0</h3>
                <p>Managers</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #10B981, #34D399);">
                <span class="material-icons-outlined">person</span>
            </div>
            <div class="stat-info">
                <h3 id="statRegularUsers">0</h3>
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
                    <input type="text" id="searchUsersInput" placeholder="Tìm kiếm..." onkeyup="debounceSearchUsers()">
                </div>
                <?php if ($isAdmin): ?>
                <select class="filter-select" id="filterUserRole" onchange="loadUsersList()">
                    <option value="">Tất cả role</option>
                    <option value="admin">Admin</option>
                    <option value="manager">Manager</option>
                    <option value="user">User</option>
                </select>
                <?php endif; ?>
                <button class="btn btn-outline" style="padding: 10px 14px;" onclick="loadUsersList()">
                    <span class="material-icons-outlined">refresh</span>
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
                <tbody id="usersListTable">
                    <tr><td colspan="8" class="loading"><div class="spinner"></div>Đang tải...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create User Modal -->
    <div id="createUserModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Thêm tài khoản mới</h2>
                <button class="modal-close" onclick="closeUserModal('createUserModal')">
                    <span class="material-icons-outlined">close</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="createUserForm">
                    <label>Username *</label>
                    <input type="text" name="username" required placeholder="Nhập username">
                    
                    <label>Email</label>
                    <input type="email" name="email" placeholder="Nhập email">
                    
                    <label>Mật khẩu *</label>
                    <input type="password" name="password" required placeholder="Nhập mật khẩu" minlength="6" autocomplete="new-password">
                    
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
                    <select name="manager_id" id="managerSelectCreate">
                        <option value="">-- Không có --</option>
                    </select>
                    <?php endif; ?>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" onclick="closeUserModal('createUserModal')">Hủy</button>
                <button class="btn btn-primary" onclick="createUserSubmit()">
                    <span class="material-icons-outlined">add</span>
                    Tạo tài khoản
                </button>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div id="editUserModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Chỉnh sửa tài khoản</h2>
                <button class="modal-close" onclick="closeUserModal('editUserModal')">
                    <span class="material-icons-outlined">close</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="editUserForm">
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
                <button class="btn btn-outline" onclick="closeUserModal('editUserModal')">Hủy</button>
                <button class="btn btn-primary" onclick="updateUserSubmit()">
                    <span class="material-icons-outlined">save</span>
                    Lưu thay đổi
                </button>
            </div>
        </div>
    </div>

    <!-- Import Users Modal -->
    <div id="importUsersModal" class="modal">
        <div class="modal-content" style="max-width: 600px;">
            <div class="modal-header">
                <h2><span class="material-icons-outlined">upload_file</span> Import tài khoản từ TXT</h2>
                <button class="modal-close" onclick="closeUserModal('importUsersModal')">
                    <span class="material-icons-outlined">close</span>
                </button>
            </div>
            <div class="modal-body">
                <div style="background: var(--info-light); padding: 16px; border-radius: 8px; margin-bottom: 20px;">
                    <h4 style="color: var(--info); margin-bottom: 8px; display: flex; align-items: center; gap: 8px;">
                        <span class="material-icons-outlined">info</span> Hướng dẫn định dạng
                    </h4>
                    <p style="color: var(--text-secondary); font-size: 13px; line-height: 1.6;">
                        Mỗi dòng là một tài khoản với định dạng:<br>
                        <code style="background: white; padding: 4px 8px; border-radius: 4px; display: inline-block; margin-top: 8px;">username/email/password/role</code><br><br>
                        <strong>Ví dụ:</strong><br>
                        <code style="background: white; padding: 4px 8px; border-radius: 4px; display: inline-block; font-size: 12px;">
                            user1/user1@email.com/123456/user<br>
                            manager1/manager@email.com/pass123/manager
                        </code>
                    </p>
                </div>
                
                <div style="margin-bottom: 16px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600;">Chọn file TXT hoặc dán nội dung:</label>
                    <input type="file" id="importUsersFile" accept=".txt" onchange="handleImportFileSelect(event)" 
                        style="width: 100%; padding: 12px; border: 2px dashed var(--border-medium); border-radius: 8px; background: var(--bg-primary);">
                </div>
                
                <div style="margin-bottom: 16px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600;">Hoặc dán nội dung trực tiếp:</label>
                    <textarea id="importUsersContent" rows="8" placeholder="username/email/password/role&#10;user1/user1@email.com/123456/user&#10;user2/user2@email.com/password/manager"
                        style="width: 100%; padding: 12px; border: 1px solid var(--border-light); border-radius: 8px; font-family: monospace; font-size: 13px; resize: vertical;"></textarea>
                </div>
                
                <div id="importUsersPreview" style="display: none; background: var(--bg-primary); padding: 16px; border-radius: 8px; max-height: 200px; overflow-y: auto;">
                    <h4 style="margin-bottom: 12px;">Preview:</h4>
                    <div id="importUsersPreviewContent"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" onclick="closeUserModal('importUsersModal')">Hủy</button>
                <button class="btn btn-outline" onclick="previewImportUsers()">
                    <span class="material-icons-outlined">visibility</span>
                    Xem trước
                </button>
                <button class="btn btn-primary" onclick="importUsersSubmit()">
                    <span class="material-icons-outlined">upload</span>
                    Import
                </button>
            </div>
        </div>
    </div>
</section>
