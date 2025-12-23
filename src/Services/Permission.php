<?php
/**
 * Permission Service
 * Manage role-based permissions
 * 
 * @package ICOGroup
 */

namespace App\Services;

use App\Core\Database;
use App\Core\Logger;

class Permission
{
    private static ?Permission $instance = null;
    private Database $db;
    private Logger $logger;
    private array $permissionsCache = [];
    private array $rolePermissionsCache = [];

    /**
     * Permission hierarchy: admin > manager > user
     */
    private const ROLE_HIERARCHY = [
        'admin' => 3,
        'manager' => 2,
        'user' => 1
    ];

    private function __construct()
    {
        $this->db = Database::getInstance();
        $this->logger = Logger::getInstance();
    }

    /**
     * Get Permission singleton instance
     */
    public static function getInstance(): Permission
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Check if a role has a specific permission
     */
    public function check(string $role, string $permissionKey): bool
    {
        // Admin has all permissions
        if ($role === 'admin') {
            return true;
        }

        $permissions = $this->getRolePermissions($role);
        return in_array($permissionKey, $permissions, true);
    }

    /**
     * Get all permissions for a role
     */
    public function getRolePermissions(string $role): array
    {
        if (isset($this->rolePermissionsCache[$role])) {
            return $this->rolePermissionsCache[$role];
        }

        $sql = "SELECT permission_key FROM role_permissions WHERE role = :role";
        $results = $this->db->fetchAll($sql, [':role' => $role]);
        
        $permissions = array_column($results, 'permission_key');
        $this->rolePermissionsCache[$role] = $permissions;
        
        return $permissions;
    }

    /**
     * Get all available permissions
     */
    public function getAllPermissions(): array
    {
        if (!empty($this->permissionsCache)) {
            return $this->permissionsCache;
        }

        $sql = "SELECT * FROM permissions ORDER BY category, permission_key";
        $this->permissionsCache = $this->db->fetchAll($sql);
        
        return $this->permissionsCache;
    }

    /**
     * Get permissions grouped by category
     */
    public function getPermissionsByCategory(): array
    {
        $permissions = $this->getAllPermissions();
        $grouped = [];
        
        foreach ($permissions as $perm) {
            $category = $perm['category'] ?? 'general';
            if (!isset($grouped[$category])) {
                $grouped[$category] = [];
            }
            $grouped[$category][] = $perm;
        }
        
        return $grouped;
    }

    /**
     * Check if role1 can manage role2 (based on hierarchy)
     */
    public function canManageRole(string $managerRole, string $targetRole): bool
    {
        $managerLevel = self::ROLE_HIERARCHY[$managerRole] ?? 0;
        $targetLevel = self::ROLE_HIERARCHY[$targetRole] ?? 0;
        
        return $managerLevel > $targetLevel;
    }

    /**
     * Get roles that a role can create
     */
    public function getCreatableRoles(string $role): array
    {
        switch ($role) {
            case 'admin':
                return ['admin', 'manager', 'user'];
            case 'manager':
                return ['user'];
            default:
                return [];
        }
    }

    /**
     * Check if role can access admin panel
     */
    public function canAccessAdmin(string $role): bool
    {
        return in_array($role, ['admin', 'manager'], true);
    }

    /**
     * Check if role can view dashboard
     */
    public function canViewDashboard(string $role): bool
    {
        return in_array($role, ['admin', 'manager', 'user'], true);
    }

    // =============================================
    // Helper methods for common permission checks
    // =============================================

    public function canManageUsers(string $role): bool
    {
        return $this->check($role, 'users.view_all') || $this->check($role, 'users.view_team');
    }

    public function canCreateUsers(string $role): bool
    {
        return $this->check($role, 'users.create_user') || 
               $this->check($role, 'users.create_manager') || 
               $this->check($role, 'users.create_admin');
    }

    public function canDeleteUsers(string $role): bool
    {
        return $this->check($role, 'users.delete');
    }

    public function canAccessSettings(string $role): bool
    {
        return $this->check($role, 'settings.view');
    }

    public function canModifySettings(string $role): bool
    {
        return $this->check($role, 'settings.modify');
    }

    public function canViewAllReports(string $role): bool
    {
        return $this->check($role, 'reports.view_all');
    }

    public function canViewTeamReports(string $role): bool
    {
        return $this->check($role, 'reports.view_team');
    }

    public function canViewAllLogs(string $role): bool
    {
        return $this->check($role, 'logs.view_all');
    }

    public function canManageCMS(string $role): bool
    {
        return $this->check($role, 'cms.manage');
    }

    public function canManageNews(string $role): bool
    {
        return $this->check($role, 'news.create') || $this->check($role, 'news.edit_all');
    }

    public function canViewRegistrations(string $role): bool
    {
        return $this->check($role, 'registrations.view_all') || 
               $this->check($role, 'registrations.view_assigned');
    }

    public function canExportData(string $role): bool
    {
        return $this->check($role, 'registrations.export') || 
               $this->check($role, 'reports.export');
    }

    public function canBackupDatabase(string $role): bool
    {
        return $this->check($role, 'database.backup');
    }

    // =============================================
    // Permission management (for admin)
    // =============================================

    /**
     * Grant permission to a role
     */
    public function grantPermission(string $role, string $permissionKey): bool
    {
        try {
            $sql = "INSERT IGNORE INTO role_permissions (role, permission_key) VALUES (:role, :permission_key)";
            $this->db->execute($sql, [
                ':role' => $role,
                ':permission_key' => $permissionKey
            ]);
            
            // Clear cache
            unset($this->rolePermissionsCache[$role]);
            
            $this->logger->audit('permission_grant', null, 'role_permissions', null, [
                'role' => $role,
                'permission' => $permissionKey
            ]);
            
            return true;
        } catch (\Exception $e) {
            $this->logger->error("Failed to grant permission", [
                'role' => $role,
                'permission' => $permissionKey,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Revoke permission from a role
     */
    public function revokePermission(string $role, string $permissionKey): bool
    {
        try {
            $sql = "DELETE FROM role_permissions WHERE role = :role AND permission_key = :permission_key";
            $this->db->execute($sql, [
                ':role' => $role,
                ':permission_key' => $permissionKey
            ]);
            
            // Clear cache
            unset($this->rolePermissionsCache[$role]);
            
            $this->logger->audit('permission_revoke', null, 'role_permissions', null, [
                'role' => $role,
                'permission' => $permissionKey
            ]);
            
            return true;
        } catch (\Exception $e) {
            $this->logger->error("Failed to revoke permission", [
                'role' => $role,
                'permission' => $permissionKey,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get permission matrix for all roles
     */
    public function getPermissionMatrix(): array
    {
        $permissions = $this->getAllPermissions();
        $roles = ['admin', 'manager', 'user'];
        $matrix = [];
        
        foreach ($permissions as $perm) {
            $key = $perm['permission_key'];
            $matrix[$key] = [
                'info' => $perm,
                'roles' => []
            ];
            
            foreach ($roles as $role) {
                $matrix[$key]['roles'][$role] = $this->check($role, $key);
            }
        }
        
        return $matrix;
    }

    /**
     * Clear permission cache
     */
    public function clearCache(): void
    {
        $this->permissionsCache = [];
        $this->rolePermissionsCache = [];
    }
}
