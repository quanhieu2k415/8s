/**
 * Users Management JavaScript
 * Handles all user management functionality in dashboard
 */

const USERS_API_BASE = '../backend_api/users_api.php';
let usersSearchTimeout;

// Initialize users section when loaded
function initUsersSection() {
    if (typeof isAdmin !== 'undefined' && isAdmin) {
        loadUsersStats();
        loadManagersForDropdown();
    }
    loadUsersList();
}

// Load user statistics
async function loadUsersStats() {
    try {
        const response = await fetch(`${USERS_API_BASE}?action=stats`);
        const data = await response.json();

        if (data.success) {
            document.getElementById('statAdminUsers').textContent = data.data.admin || 0;
            document.getElementById('statManagerUsers').textContent = data.data.manager || 0;
            document.getElementById('statRegularUsers').textContent = data.data.user || 0;
        }
    } catch (error) {
        console.error('Error loading user stats:', error);
    }
}

// Load managers for dropdown
async function loadManagersForDropdown() {
    try {
        const response = await fetch(`${USERS_API_BASE}?action=managers`);
        const data = await response.json();

        if (data.success) {
            const selects = ['managerSelectCreate', 'editManagerSelect'];
            selects.forEach(selectId => {
                const select = document.getElementById(selectId);
                if (select) {
                    select.innerHTML = '<option value="">-- Không có --</option>';
                    data.data.forEach(manager => {
                        const option = document.createElement('option');
                        option.value = manager.id;
                        option.textContent = `${manager.username} (${manager.email || 'No email'})`;
                        select.appendChild(option);
                    });
                }
            });
        }
    } catch (error) {
        console.error('Error loading managers:', error);
    }
}

// Load users list
async function loadUsersList() {
    const tbody = document.getElementById('usersListTable');
    tbody.innerHTML = '<tr><td colspan="8" class="loading"><div class="spinner"></div>Đang tải...</td></tr>';

    try {
        const search = document.getElementById('searchUsersInput')?.value || '';
        const role = document.getElementById('filterUserRole')?.value || '';

        let url = `${USERS_API_BASE}?action=list`;
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

        const isAdminUser = typeof isAdmin !== 'undefined' && isAdmin;
        const canDeleteUser = typeof canDelete !== 'undefined' && canDelete;
        const currentUserId = typeof currentUser !== 'undefined' ? currentUser.id : null;

        tbody.innerHTML = data.data.map(user => `
            <tr>
                <td>${user.id}</td>
                <td><strong>${escapeHtml(user.username)}</strong></td>
                <td>${user.email || '<span style="color: var(--text-muted);">Chưa có</span>'}</td>
                <td><span class="badge badge-${user.role === 'admin' ? 'danger' : user.role === 'manager' ? 'warning' : 'success'}">${user.role.toUpperCase()}</span></td>
                ${isAdminUser ? `<td>${user.manager_username || '<span style="color: var(--text-muted);">-</span>'}</td>` : ''}
                <td>
                    <span class="badge ${user.is_active ? 'badge-success' : 'badge-danger'}">
                        ${user.is_active ? 'Active' : 'Inactive'}
                    </span>
                </td>
                <td>${user.last_login ? formatDate(user.last_login) : '<span style="color: var(--text-muted);">Chưa đăng nhập</span>'}</td>
                <td>
                    <div class="action-btns">
                        <button class="action-btn edit" onclick="openEditUserModal(${user.id})" title="Chỉnh sửa">
                            <span class="material-icons-outlined">edit</span>
                        </button>
                        ${canDeleteUser && user.id !== currentUserId ? `
                        <button class="action-btn delete" onclick="deleteUserAccount(${user.id}, '${escapeHtml(user.username)}')" title="Xóa">
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
function debounceSearchUsers() {
    clearTimeout(usersSearchTimeout);
    usersSearchTimeout = setTimeout(loadUsersList, 300);
}

// Open create user modal
function openCreateUserModal() {
    document.getElementById('createUserForm').reset();
    document.getElementById('createUserModal').style.display = 'block';
}

// Create user submit
async function createUserSubmit() {
    const form = document.getElementById('createUserForm');
    const formData = new FormData(form);
    const data = Object.fromEntries(formData);

    if (!data.username || !data.password) {
        showToast('Vui lòng điền đầy đủ thông tin', 'error');
        return;
    }

    try {
        const response = await fetch(`${USERS_API_BASE}?action=create`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.success) {
            showToast('Tạo tài khoản thành công!', 'success');
            closeUserModal('createUserModal');
            loadUsersList();
            if (typeof isAdmin !== 'undefined' && isAdmin) {
                loadUsersStats();
            }
        } else {
            throw new Error(result.message);
        }
    } catch (error) {
        showToast(error.message, 'error');
    }
}

// Open edit user modal
async function openEditUserModal(id) {
    try {
        const response = await fetch(`${USERS_API_BASE}?action=get&id=${id}`);
        const data = await response.json();

        if (!data.success) {
            throw new Error(data.message);
        }

        const user = data.data;
        document.getElementById('editUserId').value = user.id;
        document.getElementById('editUsername').value = user.username;
        document.getElementById('editEmail').value = user.email || '';

        if (typeof isAdmin !== 'undefined' && isAdmin) {
            document.getElementById('editRole').value = user.role;
            document.getElementById('editDepartment').value = user.department || '';
            document.getElementById('editManagerSelect').value = user.manager_id || '';
        }

        document.getElementById('editUserModal').style.display = 'block';
    } catch (error) {
        showToast(error.message, 'error');
    }
}

// Update user submit
async function updateUserSubmit() {
    const form = document.getElementById('editUserForm');
    const formData = new FormData(form);
    const data = Object.fromEntries(formData);

    try {
        const response = await fetch(`${USERS_API_BASE}?action=update`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.success) {
            showToast('Cập nhật thành công!', 'success');
            closeUserModal('editUserModal');
            loadUsersList();
        } else {
            throw new Error(result.message);
        }
    } catch (error) {
        showToast(error.message, 'error');
    }
}

// Delete user account
async function deleteUserAccount(id, username) {
    if (!confirm(`Bạn có chắc muốn xóa tài khoản "${username}"?\n\nHành động này không thể hoàn tác.`)) {
        return;
    }

    try {
        const response = await fetch(`${USERS_API_BASE}?action=delete&id=${id}`, {
            method: 'DELETE'
        });

        const result = await response.json();

        if (result.success) {
            showToast('Xóa tài khoản thành công!', 'success');
            loadUsersList();
            if (typeof isAdmin !== 'undefined' && isAdmin) {
                loadUsersStats();
            }
        } else {
            throw new Error(result.message);
        }
    } catch (error) {
        showToast(error.message, 'error');
    }
}

// Open import modal
function openImportModal() {
    document.getElementById('importUsersModal').style.display = 'flex';
    document.getElementById('importUsersContent').value = '';
    document.getElementById('importUsersFile').value = '';
    document.getElementById('importUsersPreview').style.display = 'none';
}

// Handle import file select
function handleImportFileSelect(event) {
    const file = event.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = function (e) {
        document.getElementById('importUsersContent').value = e.target.result;
        previewImportUsers();
    };
    reader.readAsText(file);
}

// Parse import data
function parseImportData(content) {
    const lines = content.trim().split('\n').filter(line => line.trim());
    const users = [];
    const errors = [];

    lines.forEach((line, index) => {
        const parts = line.trim().split('/');
        if (parts.length < 3) {
            errors.push(`Dòng ${index + 1}: Thiếu thông tin (cần ít nhất username/email/password)`);
            return;
        }

        const [username, email, password, role = 'user'] = parts.map(p => p.trim());

        if (!username || !email || !password) {
            errors.push(`Dòng ${index + 1}: Username, email hoặc password trống`);
            return;
        }

        if (!['admin', 'manager', 'user'].includes(role.toLowerCase())) {
            errors.push(`Dòng ${index + 1}: Role không hợp lệ (chỉ có: admin, manager, user)`);
            return;
        }

        users.push({
            username,
            email,
            password,
            role: role.toLowerCase()
        });
    });

    return { users, errors };
}

// Preview import users
function previewImportUsers() {
    const content = document.getElementById('importUsersContent').value;
    if (!content.trim()) {
        showToast('Vui lòng nhập nội dung hoặc chọn file', 'error');
        return;
    }

    const { users, errors } = parseImportData(content);
    const previewDiv = document.getElementById('importUsersPreview');
    const previewContent = document.getElementById('importUsersPreviewContent');

    let html = '';

    if (errors.length > 0) {
        html += `<div style="color: var(--danger); margin-bottom: 12px;">
            <strong>⚠️ Lỗi:</strong><br>
            ${errors.map(e => `• ${e}`).join('<br>')}
        </div>`;
    }

    if (users.length > 0) {
        html += `<div style="color: var(--success); margin-bottom: 8px;">
            <strong>✅ ${users.length} tài khoản hợp lệ:</strong>
        </div>`;
        html += '<table style="width: 100%; font-size: 12px; border-collapse: collapse;">';
        html += '<tr style="background: var(--surface);"><th style="padding: 8px; text-align: left;">Username</th><th style="padding: 8px; text-align: left;">Email</th><th style="padding: 8px; text-align: left;">Role</th></tr>';
        users.forEach(u => {
            html += `<tr style="border-top: 1px solid var(--border-light);">
                <td style="padding: 8px;">${escapeHtml(u.username)}</td>
                <td style="padding: 8px;">${escapeHtml(u.email)}</td>
                <td style="padding: 8px;"><span class="badge badge-${u.role}">${u.role.toUpperCase()}</span></td>
            </tr>`;
        });
        html += '</table>';
    }

    previewContent.innerHTML = html;
    previewDiv.style.display = 'block';
}

// Import users submit
async function importUsersSubmit() {
    const content = document.getElementById('importUsersContent').value;
    if (!content.trim()) {
        showToast('Vui lòng nhập nội dung hoặc chọn file', 'error');
        return;
    }

    const { users, errors } = parseImportData(content);

    if (users.length === 0) {
        showToast('Không có tài khoản hợp lệ để import', 'error');
        return;
    }

    if (!confirm(`Bạn có chắc muốn import ${users.length} tài khoản?${errors.length > 0 ? `\n\nCó ${errors.length} dòng lỗi sẽ bị bỏ qua.` : ''}`)) {
        return;
    }

    try {
        const response = await fetch(`${USERS_API_BASE}?action=import`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ users })
        });

        const result = await response.json();

        if (result.success) {
            showToast(`Import thành công ${result.data.created} tài khoản!`, 'success');
            closeUserModal('importUsersModal');
            loadUsersList();
            if (typeof isAdmin !== 'undefined' && isAdmin) {
                loadUsersStats();
            }
        } else {
            throw new Error(result.message);
        }
    } catch (error) {
        showToast(error.message, 'error');
    }
}

// Close user modal
function closeUserModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
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

// Close modal on outside click
window.addEventListener('click', function (event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
});

// Initialize when section becomes active
document.addEventListener('DOMContentLoaded', function () {
    // Init immediately if hash is #users
    if (window.location.hash === '#users') {
        console.log('[Users] Initializing from hash');
        setTimeout(initUsersSection, 100);
    }

    // Also watch for hash changes
    window.addEventListener('hashchange', function () {
        if (window.location.hash === '#users') {
            console.log('[Users] Initializing from hash change');
            initUsersSection();
        }
    });
});

// Also try to init when script loads
if (document.readyState === 'complete' || document.readyState === 'interactive') {
    if (window.location.hash === '#users') {
        console.log('[Users] Initializing on script load');
        setTimeout(initUsersSection, 100);
    }
}
