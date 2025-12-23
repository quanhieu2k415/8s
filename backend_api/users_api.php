<?php
/**
 * Users API
 * Endpoints for user management
 * 
 * @package ICOGroup
 */

require_once __DIR__ . '/bootstrap.php';

use App\Core\Response;
use App\Core\CSRF;
use App\Services\Auth;
use App\Services\Permission;
use App\Repositories\AdminUserRepository;
use App\Core\Logger;

Response::setCorsHeaders();

$auth = Auth::getInstance();
$permission = Permission::getInstance();
$userRepo = new AdminUserRepository();
$logger = Logger::getInstance();

// Check authentication
if (!$auth->check()) {
    Response::json(['success' => false, 'message' => 'Chưa đăng nhập'], 401);
}

$currentUser = $auth->user();
$currentRole = $currentUser['role'] ?? 'user';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

switch ($method) {
    case 'GET':
        handleGet($action, $auth, $permission, $userRepo, $currentUser, $currentRole);
        break;
    case 'POST':
        handlePost($action, $auth, $permission, $userRepo, $currentUser, $currentRole, $logger);
        break;
    case 'PUT':
        handlePut($action, $auth, $permission, $userRepo, $currentUser, $currentRole, $logger);
        break;
    case 'DELETE':
        handleDelete($action, $auth, $permission, $userRepo, $currentUser, $currentRole, $logger);
        break;
    default:
        Response::json(['success' => false, 'message' => 'Method not allowed'], 405);
}

/**
 * Handle GET requests
 */
function handleGet($action, $auth, $permission, $userRepo, $currentUser, $currentRole) {
    switch ($action) {
        case 'list':
            // Check permission
            if (!$permission->canManageUsers($currentRole)) {
                Response::json(['success' => false, 'message' => 'Không có quyền xem danh sách người dùng'], 403);
            }

            $role = $_GET['role'] ?? null;
            $search = $_GET['search'] ?? '';

            if ($currentRole === 'admin') {
                // Admin sees all users
                if ($search) {
                    $users = $userRepo->searchUsers($search, $role);
                } elseif ($role) {
                    $users = $userRepo->getByRole($role);
                } else {
                    $users = $userRepo->all();
                }
            } else {
                // Manager sees only their team
                $users = $userRepo->getSubordinates($currentUser['id']);
                if ($search) {
                    $users = array_filter($users, function($u) use ($search) {
                        return stripos($u['username'], $search) !== false || 
                               stripos($u['email'] ?? '', $search) !== false;
                    });
                }
            }

            // Remove sensitive data
            $users = array_map(function($u) {
                unset($u['password_hash'], $u['remember_token']);
                return $u;
            }, $users);

            Response::json([
                'success' => true,
                'data' => array_values($users),
                'total' => count($users)
            ]);
            break;

        case 'get':
            $id = (int) ($_GET['id'] ?? 0);
            if (!$id) {
                Response::json(['success' => false, 'message' => 'ID không hợp lệ'], 400);
            }

            // Check if can view this user
            if (!$auth->canManage($id) && $currentUser['id'] !== $id) {
                Response::json(['success' => false, 'message' => 'Không có quyền xem người dùng này'], 403);
            }

            $user = $userRepo->findWithManager($id);
            if (!$user) {
                Response::json(['success' => false, 'message' => 'Không tìm thấy người dùng'], 404);
            }

            unset($user['password_hash'], $user['remember_token']);
            Response::json(['success' => true, 'data' => $user]);
            break;

        case 'managers':
            // Get list of managers for assignment dropdown
            if ($currentRole !== 'admin') {
                Response::json(['success' => false, 'message' => 'Chỉ Admin mới có quyền'], 403);
            }

            $managers = $userRepo->getManagers();
            $managers = array_map(function($m) {
                return [
                    'id' => $m['id'],
                    'username' => $m['username'],
                    'email' => $m['email']
                ];
            }, $managers);

            Response::json(['success' => true, 'data' => $managers]);
            break;

        case 'stats':
            // Get user statistics
            if ($currentRole !== 'admin') {
                Response::json(['success' => false, 'message' => 'Chỉ Admin mới có quyền xem thống kê'], 403);
            }

            $counts = $userRepo->countByRole();
            Response::json(['success' => true, 'data' => $counts]);
            break;

        case 'permissions':
            // Get current user's permissions
            $permissions = $permission->getRolePermissions($currentRole);
            Response::json([
                'success' => true,
                'role' => $currentRole,
                'permissions' => $permissions
            ]);
            break;

        case 'permission-matrix':
            // Get full permission matrix (admin only)
            if ($currentRole !== 'admin') {
                Response::json(['success' => false, 'message' => 'Chỉ Admin mới có quyền'], 403);
            }

            $matrix = $permission->getPermissionMatrix();
            Response::json(['success' => true, 'data' => $matrix]);
            break;

        default:
            Response::json(['success' => false, 'message' => 'Action không hợp lệ'], 400);
    }
}

/**
 * Handle POST requests (Create)
 */
function handlePost($action, $auth, $permission, $userRepo, $currentUser, $currentRole, $logger) {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

    switch ($action) {
        case 'create':
            $username = trim($input['username'] ?? '');
            $email = trim($input['email'] ?? '');
            $password = $input['password'] ?? '';
            $role = $input['role'] ?? 'user';
            $department = $input['department'] ?? null;
            $managerId = isset($input['manager_id']) ? (int) $input['manager_id'] : null;

            // Validate required fields
            if (!$username || !$password) {
                Response::json(['success' => false, 'message' => 'Username và password không được để trống'], 400);
            }

            // Check permission to create this role
            $creatableRoles = $permission->getCreatableRoles($currentRole);
            if (!in_array($role, $creatableRoles)) {
                Response::json(['success' => false, 'message' => "Bạn không có quyền tạo tài khoản $role"], 403);
            }

            // Check if username exists
            if ($userRepo->usernameExists($username)) {
                Response::json(['success' => false, 'message' => 'Username đã tồn tại'], 400);
            }

            // Manager can only assign themselves as manager
            if ($currentRole === 'manager') {
                $managerId = $currentUser['id'];
            }

            // Create user
            try {
                $userId = $userRepo->createUser($username, $password, $email, $role);
                
                if ($department) {
                    $userRepo->updateDepartment($userId, $department);
                }
                
                if ($managerId) {
                    $userRepo->assignManager($userId, $managerId);
                }

                $logger->audit('user_create', $currentUser['id'], 'admin_users', $userId, [
                    'username' => $username,
                    'role' => $role
                ]);

                Response::json([
                    'success' => true,
                    'message' => 'Tạo tài khoản thành công',
                    'user_id' => $userId
                ]);
            } catch (Exception $e) {
                $logger->error('Failed to create user', ['error' => $e->getMessage()]);
                Response::json(['success' => false, 'message' => 'Lỗi khi tạo tài khoản'], 500);
            }
            break;

        default:
            Response::json(['success' => false, 'message' => 'Action không hợp lệ'], 400);
    }
}

/**
 * Handle PUT requests (Update)
 */
function handlePut($action, $auth, $permission, $userRepo, $currentUser, $currentRole, $logger) {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = (int) ($input['id'] ?? $_GET['id'] ?? 0);

    if (!$id) {
        Response::json(['success' => false, 'message' => 'ID không hợp lệ'], 400);
    }

    // Check permission
    if (!$auth->canManage($id) && $currentUser['id'] !== $id) {
        Response::json(['success' => false, 'message' => 'Không có quyền sửa người dùng này'], 403);
    }

    $targetUser = $userRepo->find($id);
    if (!$targetUser) {
        Response::json(['success' => false, 'message' => 'Không tìm thấy người dùng'], 404);
    }

    switch ($action) {
        case 'update':
            $updates = [];

            // Email update
            if (isset($input['email'])) {
                $updates['email'] = trim($input['email']);
            }

            // Department update (admin only)
            if (isset($input['department']) && $currentRole === 'admin') {
                $updates['department'] = $input['department'];
            }

            // Role update (admin only, and can't change own role)
            if (isset($input['role']) && $currentRole === 'admin' && $id !== $currentUser['id']) {
                $newRole = $input['role'];
                if (in_array($newRole, ['admin', 'manager', 'user'])) {
                    $updates['role'] = $newRole;
                }
            }

            // Manager assignment (admin only)
            if (isset($input['manager_id']) && $currentRole === 'admin') {
                $updates['manager_id'] = $input['manager_id'] ? (int) $input['manager_id'] : null;
            }

            if (!empty($updates)) {
                $userRepo->update($id, $updates);
                
                $logger->audit('user_update', $currentUser['id'], 'admin_users', $id, [
                    'updates' => array_keys($updates)
                ]);
            }

            Response::json(['success' => true, 'message' => 'Cập nhật thành công']);
            break;

        case 'password':
            // Only self or admin can change password
            if ($currentUser['id'] !== $id && $currentRole !== 'admin') {
                Response::json(['success' => false, 'message' => 'Không có quyền đổi mật khẩu'], 403);
            }

            $newPassword = $input['new_password'] ?? '';
            $currentPassword = $input['current_password'] ?? '';

            if (!$newPassword || strlen($newPassword) < 6) {
                Response::json(['success' => false, 'message' => 'Mật khẩu mới phải có ít nhất 6 ký tự'], 400);
            }

            // If not admin, require current password
            if ($currentRole !== 'admin') {
                if (!$currentPassword) {
                    Response::json(['success' => false, 'message' => 'Vui lòng nhập mật khẩu hiện tại'], 400);
                }
                if (!$userRepo->verifyPassword($targetUser, $currentPassword)) {
                    Response::json(['success' => false, 'message' => 'Mật khẩu hiện tại không đúng'], 400);
                }
            }

            $userRepo->updatePassword($id, $newPassword);
            
            $logger->audit('password_change', $currentUser['id'], 'admin_users', $id);

            Response::json(['success' => true, 'message' => 'Đổi mật khẩu thành công']);
            break;

        case 'activate':
            if ($currentRole !== 'admin') {
                Response::json(['success' => false, 'message' => 'Chỉ Admin mới có quyền'], 403);
            }

            $userRepo->activate($id);
            $logger->audit('user_activate', $currentUser['id'], 'admin_users', $id);

            Response::json(['success' => true, 'message' => 'Kích hoạt tài khoản thành công']);
            break;

        case 'deactivate':
            if ($currentRole !== 'admin') {
                Response::json(['success' => false, 'message' => 'Chỉ Admin mới có quyền'], 403);
            }

            if ($id === $currentUser['id']) {
                Response::json(['success' => false, 'message' => 'Không thể vô hiệu hóa tài khoản của chính mình'], 400);
            }

            $userRepo->deactivate($id);
            $logger->audit('user_deactivate', $currentUser['id'], 'admin_users', $id);

            Response::json(['success' => true, 'message' => 'Vô hiệu hóa tài khoản thành công']);
            break;

        case 'assign-manager':
            if ($currentRole !== 'admin') {
                Response::json(['success' => false, 'message' => 'Chỉ Admin mới có quyền gán Manager'], 403);
            }

            $managerId = isset($input['manager_id']) ? (int) $input['manager_id'] : null;
            
            if ($managerId) {
                $manager = $userRepo->find($managerId);
                if (!$manager || $manager['role'] !== 'manager') {
                    Response::json(['success' => false, 'message' => 'Manager không hợp lệ'], 400);
                }
            }

            $userRepo->assignManager($id, $managerId);
            $logger->audit('manager_assign', $currentUser['id'], 'admin_users', $id, [
                'manager_id' => $managerId
            ]);

            Response::json(['success' => true, 'message' => 'Gán Manager thành công']);
            break;

        default:
            Response::json(['success' => false, 'message' => 'Action không hợp lệ'], 400);
    }
}

/**
 * Handle DELETE requests
 */
function handleDelete($action, $auth, $permission, $userRepo, $currentUser, $currentRole, $logger) {
    $id = (int) ($_GET['id'] ?? 0);

    if (!$id) {
        Response::json(['success' => false, 'message' => 'ID không hợp lệ'], 400);
    }

    // Only admin can delete users
    if (!$permission->canDeleteUsers($currentRole)) {
        Response::json(['success' => false, 'message' => 'Chỉ Admin mới có quyền xóa tài khoản'], 403);
    }

    // Can't delete self
    if ($id === $currentUser['id']) {
        Response::json(['success' => false, 'message' => 'Không thể xóa tài khoản của chính mình'], 400);
    }

    $targetUser = $userRepo->find($id);
    if (!$targetUser) {
        Response::json(['success' => false, 'message' => 'Không tìm thấy người dùng'], 404);
    }

    try {
        $userRepo->delete($id);
        
        $logger->audit('user_delete', $currentUser['id'], 'admin_users', $id, [
            'deleted_username' => $targetUser['username'],
            'deleted_role' => $targetUser['role']
        ]);

        Response::json(['success' => true, 'message' => 'Xóa tài khoản thành công']);
    } catch (Exception $e) {
        $logger->error('Failed to delete user', ['error' => $e->getMessage()]);
        Response::json(['success' => false, 'message' => 'Lỗi khi xóa tài khoản'], 500);
    }
}
