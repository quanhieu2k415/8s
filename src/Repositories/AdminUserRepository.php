<?php
/**
 * Admin User Repository
 * Database operations for admin users
 * 
 * @package ICOGroup
 */

namespace App\Repositories;

class AdminUserRepository extends BaseRepository
{
    protected string $table = 'admin_users';

    /**
     * Find user by username
     */
    public function findByUsername(string $username): ?array
    {
        return $this->findBy('username', $username);
    }

    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?array
    {
        return $this->findBy('email', $email);
    }

    /**
     * Create admin user with hashed password
     */
    public function createUser(
        string $username,
        string $password,
        string $email = null,
        string $role = 'admin'
    ): int {
        $passwordHash = password_hash($password, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536,
            'time_cost' => 4,
            'threads' => 3
        ]);

        return $this->create([
            'username' => $username,
            'email' => $email,
            'password_hash' => $passwordHash,
            'role' => $role,
            'is_active' => 1,
            'login_attempts' => 0
        ]);
    }

    /**
     * Verify password
     */
    public function verifyPassword(array $user, string $password): bool
    {
        return password_verify($password, $user['password_hash']);
    }

    /**
     * Check if password needs rehash
     */
    public function needsRehash(array $user): bool
    {
        return password_needs_rehash($user['password_hash'], PASSWORD_ARGON2ID);
    }

    /**
     * Update password
     */
    public function updatePassword(int $userId, string $password): bool
    {
        $passwordHash = password_hash($password, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536,
            'time_cost' => 4,
            'threads' => 3
        ]);

        return $this->update($userId, ['password_hash' => $passwordHash]);
    }

    /**
     * Record login attempt
     */
    public function recordLoginAttempt(int $userId, bool $success): void
    {
        if ($success) {
            $this->update($userId, [
                'login_attempts' => 0,
                'locked_until' => null,
                'last_login' => date('Y-m-d H:i:s')
            ]);
        } else {
            $user = $this->find($userId);
            $attempts = ($user['login_attempts'] ?? 0) + 1;
            
            $data = ['login_attempts' => $attempts];
            
            // Lock account after 5 failed attempts
            if ($attempts >= 5) {
                $data['locked_until'] = date('Y-m-d H:i:s', strtotime('+15 minutes'));
            }
            
            $this->update($userId, $data);
        }
    }

    /**
     * Check if account is locked
     */
    public function isLocked(array $user): bool
    {
        if (empty($user['locked_until'])) {
            return false;
        }
        
        return strtotime($user['locked_until']) > time();
    }

    /**
     * Get remaining lock time in seconds
     */
    public function getLockRemainingTime(array $user): int
    {
        if (empty($user['locked_until'])) {
            return 0;
        }
        
        $remaining = strtotime($user['locked_until']) - time();
        return max(0, $remaining);
    }

    /**
     * Unlock account
     */
    public function unlock(int $userId): bool
    {
        return $this->update($userId, [
            'login_attempts' => 0,
            'locked_until' => null
        ]);
    }

    /**
     * Check if user is active
     */
    public function isActive(array $user): bool
    {
        return (bool) ($user['is_active'] ?? false);
    }

    /**
     * Activate user
     */
    public function activate(int $userId): bool
    {
        return $this->update($userId, ['is_active' => 1]);
    }

    /**
     * Deactivate user
     */
    public function deactivate(int $userId): bool
    {
        return $this->update($userId, ['is_active' => 0]);
    }

    /**
     * Get users by role
     */
    public function getByRole(string $role): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE role = :role ORDER BY username ASC";
        return $this->db->fetchAll($sql, [':role' => $role]);
    }

    /**
     * Check if username exists
     */
    public function usernameExists(string $username, ?int $excludeId = null): bool
    {
        $sql = "SELECT 1 FROM {$this->table} WHERE username = :username";
        $params = [':username' => $username];
        
        if ($excludeId !== null) {
            $sql .= " AND id != :id";
            $params[':id'] = $excludeId;
        }
        
        $sql .= " LIMIT 1";
        
        return $this->db->fetchColumn($sql, $params) !== false;
    }

    /**
     * Save remember token
     */
    public function saveRememberToken(int $userId, string $token): bool
    {
        $tokenHash = hash('sha256', $token);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+30 days'));
        
        $sql = "INSERT INTO remember_tokens (user_id, token_hash, expires_at) VALUES (:user_id, :token_hash, :expires_at)";
        
        try {
            $this->db->query($sql, [
                ':user_id' => $userId,
                ':token_hash' => $tokenHash,
                ':expires_at' => $expiresAt
            ]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Verify remember token
     */
    public function verifyRememberToken(int $userId, string $token): bool
    {
        $tokenHash = hash('sha256', $token);
        
        $sql = "SELECT 1 FROM remember_tokens 
                WHERE user_id = :user_id 
                AND token_hash = :token_hash 
                AND expires_at > NOW() 
                LIMIT 1";
        
        return $this->db->fetchColumn($sql, [
            ':user_id' => $userId,
            ':token_hash' => $tokenHash
        ]) !== false;
    }

    /**
     * Delete remember token
     */
    public function deleteRememberToken(int $userId, string $token = null): bool
    {
        if ($token === null) {
            // Delete all tokens for user
            $sql = "DELETE FROM remember_tokens WHERE user_id = :user_id";
            $this->db->execute($sql, [':user_id' => $userId]);
        } else {
            $tokenHash = hash('sha256', $token);
            $sql = "DELETE FROM remember_tokens WHERE user_id = :user_id AND token_hash = :token_hash";
            $this->db->execute($sql, [
                ':user_id' => $userId,
                ':token_hash' => $tokenHash
            ]);
        }
        
        return true;
    }

    /**
     * Clean expired remember tokens
     */
    public function cleanExpiredTokens(): int
    {
        $sql = "DELETE FROM remember_tokens WHERE expires_at < NOW()";
        return $this->db->execute($sql);
    }

    /**
     * Get all users (for admin) - overrides parent to order by role
     */
    public function all(string $orderBy = 'id', string $order = 'DESC'): array
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY role ASC, username ASC";
        return $this->db->fetchAll($sql);
    }

    /**
     * Get subordinates (users managed by a manager)
     */
    public function getSubordinates(int $managerId): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE manager_id = :manager_id ORDER BY username ASC";
        return $this->db->fetchAll($sql, [':manager_id' => $managerId]);
    }

    /**
     * Assign a manager to a user
     */
    public function assignManager(int $userId, ?int $managerId): bool
    {
        return $this->update($userId, ['manager_id' => $managerId]);
    }

    /**
     * Get users by department
     */
    public function getByDepartment(string $department): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE department = :department ORDER BY role ASC, username ASC";
        return $this->db->fetchAll($sql, [':department' => $department]);
    }

    /**
     * Update user's department
     */
    public function updateDepartment(int $userId, ?string $department): bool
    {
        return $this->update($userId, ['department' => $department]);
    }

    /**
     * Get all managers
     */
    public function getManagers(): array
    {
        return $this->getByRole('manager');
    }

    /**
     * Get users without a manager
     */
    public function getUsersWithoutManager(): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE manager_id IS NULL AND role = 'user' ORDER BY username ASC";
        return $this->db->fetchAll($sql);
    }

    /**
     * Count users by role
     */
    public function countByRole(): array
    {
        $sql = "SELECT role, COUNT(*) as count FROM {$this->table} GROUP BY role";
        $results = $this->db->fetchAll($sql);
        
        $counts = ['admin' => 0, 'manager' => 0, 'user' => 0];
        foreach ($results as $row) {
            $counts[$row['role']] = (int) $row['count'];
        }
        
        return $counts;
    }

    /**
     * Search users by username or email
     */
    public function searchUsers(string $query, ?string $role = null): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE (username LIKE :query OR email LIKE :query)";
        $params = [':query' => "%{$query}%"];
        
        if ($role !== null) {
            $sql .= " AND role = :role";
            $params[':role'] = $role;
        }
        
        $sql .= " ORDER BY username ASC LIMIT 50";
        
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Update user role
     */
    public function updateRole(int $userId, string $role): bool
    {
        if (!in_array($role, ['admin', 'manager', 'user'], true)) {
            return false;
        }
        return $this->update($userId, ['role' => $role]);
    }

    /**
     * Get user with manager info
     */
    public function findWithManager(int $userId): ?array
    {
        $sql = "SELECT u.*, m.username as manager_username, m.email as manager_email 
                FROM {$this->table} u 
                LEFT JOIN {$this->table} m ON u.manager_id = m.id 
                WHERE u.id = :id";
        return $this->db->fetch($sql, [':id' => $userId]);
    }
}

