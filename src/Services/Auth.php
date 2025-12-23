<?php
/**
 * Auth Service
 * Authentication and authorization logic
 * 
 * @package ICOGroup
 */

namespace App\Services;

use App\Core\Session;
use App\Core\Logger;
use App\Repositories\AdminUserRepository;

class Auth
{
    private static ?Auth $instance = null;
    private Session $session;
    private AdminUserRepository $userRepo;
    private Logger $logger;

    private function __construct()
    {
        $this->session = Session::getInstance();
        $this->userRepo = new AdminUserRepository();
        $this->logger = Logger::getInstance();
    }

    /**
     * Get Auth singleton instance
     */
    public static function getInstance(): Auth
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Attempt login
     * 
     * @return array ['success' => bool, 'message' => string, 'user' => array|null]
     */
    public function attempt(string $username, string $password, bool $remember = false): array
    {
        $user = $this->userRepo->findByUsername($username);

        if (!$user) {
            $this->logger->info("Login failed: user not found", ['username' => $username]);
            return [
                'success' => false,
                'message' => 'Tên đăng nhập hoặc mật khẩu không đúng',
                'user' => null
            ];
        }

        // Check if account is locked
        if ($this->userRepo->isLocked($user)) {
            $remainingTime = $this->userRepo->getLockRemainingTime($user);
            $minutes = ceil($remainingTime / 60);
            
            $this->logger->warning("Login attempt on locked account", ['username' => $username]);
            
            return [
                'success' => false,
                'message' => "Tài khoản đã bị khóa. Vui lòng thử lại sau {$minutes} phút",
                'user' => null
            ];
        }

        // Check if account is active
        if (!$this->userRepo->isActive($user)) {
            $this->logger->warning("Login attempt on inactive account", ['username' => $username]);
            return [
                'success' => false,
                'message' => 'Tài khoản đã bị vô hiệu hóa',
                'user' => null
            ];
        }

        // Verify password
        if (!$this->userRepo->verifyPassword($user, $password)) {
            $this->userRepo->recordLoginAttempt($user['id'], false);
            
            $updatedUser = $this->userRepo->find($user['id']);
            $remainingAttempts = 5 - ($updatedUser['login_attempts'] ?? 0);
            
            $this->logger->info("Login failed: wrong password", [
                'username' => $username,
                'remaining_attempts' => $remainingAttempts
            ]);
            
            $message = 'Tên đăng nhập hoặc mật khẩu không đúng';
            if ($remainingAttempts > 0 && $remainingAttempts < 3) {
                $message .= ". Còn {$remainingAttempts} lần thử";
            }
            
            return [
                'success' => false,
                'message' => $message,
                'user' => null
            ];
        }

        // Successful login
        $this->userRepo->recordLoginAttempt($user['id'], true);

        // Check if password needs rehash
        if ($this->userRepo->needsRehash($user)) {
            $this->userRepo->updatePassword($user['id'], $password);
        }

        // Set session
        $sessionUser = [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'role' => $user['role']
        ];
        
        $this->session->setUser($sessionUser);

        // Handle remember me
        if ($remember) {
            $this->setRememberToken($user['id']);
        }

        $this->logger->info("Login successful", ['user_id' => $user['id'], 'username' => $username]);
        $this->logger->audit('login', $user['id'], 'admin_user', $user['id']);

        return [
            'success' => true,
            'message' => 'Đăng nhập thành công',
            'user' => $sessionUser
        ];
    }

    /**
     * Set remember me cookie
     */
    private function setRememberToken(int $userId): void
    {
        $token = bin2hex(random_bytes(32));
        $this->userRepo->saveRememberToken($userId, $token);
        
        $cookieValue = base64_encode($userId . ':' . $token);
        $expires = time() + (30 * 24 * 60 * 60); // 30 days
        
        setcookie('remember_token', $cookieValue, [
            'expires' => $expires,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Strict',
            'secure' => isset($_SERVER['HTTPS'])
        ]);
    }

    /**
     * Check remember me cookie and login
     */
    public function checkRememberMe(): bool
    {
        if ($this->session->isAuthenticated()) {
            return true;
        }

        if (!isset($_COOKIE['remember_token'])) {
            return false;
        }

        $decoded = base64_decode($_COOKIE['remember_token']);
        if (!$decoded || strpos($decoded, ':') === false) {
            $this->clearRememberCookie();
            return false;
        }

        [$userId, $token] = explode(':', $decoded, 2);
        $userId = (int) $userId;

        if (!$this->userRepo->verifyRememberToken($userId, $token)) {
            $this->clearRememberCookie();
            return false;
        }

        $user = $this->userRepo->find($userId);
        
        if (!$user || !$this->userRepo->isActive($user)) {
            $this->clearRememberCookie();
            return false;
        }

        // Log in the user
        $sessionUser = [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'role' => $user['role']
        ];
        
        $this->session->setUser($sessionUser);

        // Rotate token for security
        $this->userRepo->deleteRememberToken($userId, $token);
        $this->setRememberToken($userId);

        $this->logger->info("Remember me login", ['user_id' => $userId]);

        return true;
    }

    /**
     * Clear remember cookie
     */
    private function clearRememberCookie(): void
    {
        setcookie('remember_token', '', [
            'expires' => time() - 3600,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
    }

    /**
     * Logout user
     */
    public function logout(): void
    {
        $userId = $this->session->getUserId();
        
        if ($userId) {
            $this->userRepo->deleteRememberToken($userId);
            $this->logger->audit('logout', $userId, 'admin_user', $userId);
        }
        
        $this->clearRememberCookie();
        $this->session->destroy();
    }

    /**
     * Check if user is authenticated
     */
    public function check(): bool
    {
        // First check session
        if ($this->session->isAuthenticated()) {
            return true;
        }
        
        // Try remember me
        return $this->checkRememberMe();
    }

    /**
     * Get authenticated user
     */
    public function user(): ?array
    {
        return $this->session->getUser();
    }

    /**
     * Get authenticated user ID
     */
    public function id(): ?int
    {
        return $this->session->getUserId();
    }

    /**
     * Check if user has role
     */
    public function hasRole(string $role): bool
    {
        $user = $this->user();
        return $user && ($user['role'] ?? '') === $role;
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Check if user is editor (deprecated - use isUser or isManager)
     * @deprecated Use isManager() or isUser() instead
     */
    public function isEditor(): bool
    {
        return $this->hasRole('manager') || $this->hasRole('user');
    }

    /**
     * Check if user is manager
     */
    public function isManager(): bool
    {
        return $this->hasRole('manager');
    }

    /**
     * Check if user is regular user
     */
    public function isUser(): bool
    {
        return $this->hasRole('user');
    }

    /**
     * Check if current user has a specific permission
     */
    public function hasPermission(string $permissionKey): bool
    {
        $user = $this->user();
        if (!$user) {
            return false;
        }

        $permission = Permission::getInstance();
        return $permission->check($user['role'] ?? 'user', $permissionKey);
    }

    /**
     * Check if current user can manage another user
     */
    public function canManage(int $targetUserId): bool
    {
        $currentUser = $this->user();
        if (!$currentUser) {
            return false;
        }

        // Admin can manage everyone
        if ($currentUser['role'] === 'admin') {
            return true;
        }

        // Manager can only manage their assigned users
        if ($currentUser['role'] === 'manager') {
            $targetUser = $this->userRepo->find($targetUserId);
            if ($targetUser && $targetUser['manager_id'] === $currentUser['id']) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get list of users that current user can manage
     */
    public function getSubordinates(): array
    {
        $currentUser = $this->user();
        if (!$currentUser) {
            return [];
        }

        // Admin sees all users
        if ($currentUser['role'] === 'admin') {
            return $this->userRepo->all();
        }

        // Manager sees their team
        if ($currentUser['role'] === 'manager') {
            return $this->userRepo->getSubordinates($currentUser['id']);
        }

        // Regular user sees no one
        return [];
    }

    /**
     * Get current user's role
     */
    public function getRole(): ?string
    {
        $user = $this->user();
        return $user['role'] ?? null;
    }

    /**
     * Check if user has one of the specified roles
     */
    public function hasAnyRole(array $roles): bool
    {
        $user = $this->user();
        if (!$user) {
            return false;
        }
        return in_array($user['role'] ?? '', $roles, true);
    }

    /**
     * Require authentication (redirect if not)
     */
    public function requireAuth(string $redirectUrl = 'index.php'): void
    {
        if (!$this->check()) {
            header("Location: $redirectUrl");
            exit;
        }
    }

    /**
     * Require specific role
     */
    public function requireRole(string $role, string $redirectUrl = 'index.php'): void
    {
        $this->requireAuth($redirectUrl);
        
        if (!$this->hasRole($role)) {
            $this->logger->warning("Unauthorized access attempt", [
                'user_id' => $this->id(),
                'required_role' => $role,
                'current_role' => $this->user()['role'] ?? 'none'
            ]);
            
            http_response_code(403);
            include dirname(__DIR__, 2) . '/admin/403.php';
            exit;
        }
    }

    /**
     * Require one of specified roles
     */
    public function requireAnyRole(array $roles, string $redirectUrl = 'index.php'): void
    {
        $this->requireAuth($redirectUrl);
        
        if (!$this->hasAnyRole($roles)) {
            $this->logger->warning("Unauthorized access attempt", [
                'user_id' => $this->id(),
                'required_roles' => implode(', ', $roles),
                'current_role' => $this->user()['role'] ?? 'none'
            ]);
            
            http_response_code(403);
            include dirname(__DIR__, 2) . '/admin/403.php';
            exit;
        }
    }

    /**
     * Require specific permission
     */
    public function requirePermission(string $permissionKey, string $redirectUrl = 'index.php'): void
    {
        $this->requireAuth($redirectUrl);
        
        if (!$this->hasPermission($permissionKey)) {
            $this->logger->warning("Permission denied", [
                'user_id' => $this->id(),
                'required_permission' => $permissionKey,
                'current_role' => $this->user()['role'] ?? 'none'
            ]);
            
            http_response_code(403);
            include dirname(__DIR__, 2) . '/admin/403.php';
            exit;
        }
    }

    /**
     * Change password
     */
    public function changePassword(int $userId, string $currentPassword, string $newPassword): array
    {
        $user = $this->userRepo->find($userId);
        
        if (!$user) {
            return ['success' => false, 'message' => 'Người dùng không tồn tại'];
        }

        if (!$this->userRepo->verifyPassword($user, $currentPassword)) {
            return ['success' => false, 'message' => 'Mật khẩu hiện tại không đúng'];
        }

        $this->userRepo->updatePassword($userId, $newPassword);
        $this->logger->audit('password_change', $userId, 'admin_user', $userId);

        return ['success' => true, 'message' => 'Đổi mật khẩu thành công'];
    }
}

