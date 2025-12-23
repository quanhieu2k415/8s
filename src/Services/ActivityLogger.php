<?php
/**
 * Activity Logger Service
 * Logs user activities to activity_logs table
 */

namespace App\Services;

use App\Core\Database;

class ActivityLogger
{
    private static ?ActivityLogger $instance = null;
    private Database $db;

    private function __construct()
    {
        $this->db = Database::getInstance();
    }

    public static function getInstance(): ActivityLogger
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Log an activity
     */
    public function log(
        string $action,
        ?int $userId = null,
        ?string $username = null,
        ?string $role = null,
        ?string $details = null,
        ?string $targetType = null,
        ?int $targetId = null
    ): bool {
        try {
            $sql = "INSERT INTO activity_logs 
                    (user_id, username, role, action, target_type, target_id, details, ip_address, user_agent, created_at) 
                    VALUES 
                    (:user_id, :username, :role, :action, :target_type, :target_id, :details, :ip, :user_agent, NOW())";
            
            $this->db->execute($sql, [
                ':user_id' => $userId,
                ':username' => $username,
                ':role' => $role,
                ':action' => $action,
                ':target_type' => $targetType,
                ':target_id' => $targetId,
                ':details' => $details,
                ':ip' => $this->getClientIP(),
                ':user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500)
            ]);
            
            return true;
        } catch (\Exception $e) {
            // Don't fail silently but also don't break the app
            error_log("ActivityLogger error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Log login attempt
     */
    public function logLogin(int $userId, string $username, string $role, bool $success = true): bool
    {
        return $this->log(
            $success ? 'login' : 'login_failed',
            $userId,
            $username,
            $role,
            $success ? 'Đăng nhập thành công' : 'Đăng nhập thất bại'
        );
    }

    /**
     * Log logout
     */
    public function logLogout(int $userId, string $username, string $role): bool
    {
        return $this->log('logout', $userId, $username, $role, 'Đăng xuất');
    }

    /**
     * Log permission change
     */
    public function logPermissionChange(
        int $adminId,
        string $adminUsername,
        string $targetRole,
        string $permissionKey,
        bool $granted
    ): bool {
        $action = $granted ? 'permission_grant' : 'permission_revoke';
        $details = ($granted ? 'Cấp quyền' : 'Thu hồi quyền') . " '{$permissionKey}' cho role '{$targetRole}'";
        
        return $this->log($action, $adminId, $adminUsername, 'admin', $details, 'permission', null);
    }

    /**
     * Log content creation
     */
    public function logCreate(
        int $userId,
        string $username,
        string $role,
        string $targetType,
        ?int $targetId = null,
        ?string $details = null
    ): bool {
        return $this->log('create', $userId, $username, $role, $details ?? "Tạo mới {$targetType}", $targetType, $targetId);
    }

    /**
     * Log content update
     */
    public function logUpdate(
        int $userId,
        string $username,
        string $role,
        string $targetType,
        ?int $targetId = null,
        ?string $details = null
    ): bool {
        return $this->log('update', $userId, $username, $role, $details ?? "Cập nhật {$targetType}", $targetType, $targetId);
    }

    /**
     * Log content deletion
     */
    public function logDelete(
        int $userId,
        string $username,
        string $role,
        string $targetType,
        ?int $targetId = null,
        ?string $details = null
    ): bool {
        return $this->log('delete', $userId, $username, $role, $details ?? "Xóa {$targetType}", $targetType, $targetId);
    }

    /**
     * Log password change
     */
    public function logPasswordChange(int $userId, string $username, string $role): bool
    {
        return $this->log('password_change', $userId, $username, $role, 'Đổi mật khẩu');
    }

    /**
     * Get client IP address
     */
    private function getClientIP(): string
    {
        $headers = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'HTTP_CLIENT_IP',
            'REMOTE_ADDR'
        ];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                // Handle comma-separated IPs (X-Forwarded-For)
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return '127.0.0.1';
    }
}
