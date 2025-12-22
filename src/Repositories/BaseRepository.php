<?php
/**
 * Base Repository Class
 * Common database operations for all repositories
 * 
 * @package ICOGroup
 */

namespace App\Repositories;

use App\Core\Database;
use PDO;

abstract class BaseRepository
{
    protected Database $db;
    protected string $table;
    protected string $primaryKey = 'id';
    protected bool $softDeletes = false;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Find by primary key
     */
    public function find(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id";
        
        if ($this->softDeletes) {
            $sql .= " AND deleted_at IS NULL";
        }
        
        return $this->db->fetch($sql, [':id' => $id]);
    }

    /**
     * Find by column value
     */
    public function findBy(string $column, mixed $value): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$column} = :value";
        
        if ($this->softDeletes) {
            $sql .= " AND deleted_at IS NULL";
        }
        
        $sql .= " LIMIT 1";
        
        return $this->db->fetch($sql, [':value' => $value]);
    }

    /**
     * Get all records
     */
    public function all(string $orderBy = 'id', string $order = 'DESC'): array
    {
        $sql = "SELECT * FROM {$this->table}";
        
        if ($this->softDeletes) {
            $sql .= " WHERE deleted_at IS NULL";
        }
        
        $sql .= " ORDER BY {$orderBy} {$order}";
        
        return $this->db->fetchAll($sql);
    }

    /**
     * Get paginated records
     */
    public function paginate(
        int $page = 1,
        int $limit = 10,
        string $orderBy = 'id',
        string $order = 'DESC',
        array $conditions = []
    ): array {
        $offset = ($page - 1) * $limit;
        
        $where = [];
        $params = [];
        
        if ($this->softDeletes) {
            $where[] = "deleted_at IS NULL";
        }
        
        foreach ($conditions as $column => $value) {
            if ($value !== null && $value !== '') {
                $where[] = "{$column} = :{$column}";
                $params[":{$column}"] = $value;
            }
        }
        
        $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
        
        // Get total count
        $countSql = "SELECT COUNT(*) FROM {$this->table} {$whereClause}";
        $total = (int) $this->db->fetchColumn($countSql, $params);
        
        // Get data
        $sql = "SELECT * FROM {$this->table} {$whereClause} ORDER BY {$orderBy} {$order} LIMIT :limit OFFSET :offset";
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;
        
        $data = $this->db->fetchAll($sql, $params);
        
        return [
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => (int) ceil($total / $limit)
        ];
    }

    /**
     * Create new record
     */
    public function create(array $data): int
    {
        $columns = array_keys($data);
        $placeholders = array_map(fn($col) => ":{$col}", $columns);
        $params = [];
        
        foreach ($data as $key => $value) {
            $params[":{$key}"] = $value;
        }
        
        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            $this->table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );
        
        return $this->db->insert($sql, $params);
    }

    /**
     * Update record
     */
    public function update(int $id, array $data): bool
    {
        $setClauses = [];
        $params = [':id' => $id];
        
        foreach ($data as $key => $value) {
            $setClauses[] = "{$key} = :{$key}";
            $params[":{$key}"] = $value;
        }
        
        $sql = sprintf(
            "UPDATE %s SET %s WHERE %s = :id",
            $this->table,
            implode(', ', $setClauses),
            $this->primaryKey
        );
        
        if ($this->softDeletes) {
            $sql .= " AND deleted_at IS NULL";
        }
        
        return $this->db->execute($sql, $params) > 0;
    }

    /**
     * Delete record (soft delete if enabled)
     */
    public function delete(int $id): bool
    {
        if ($this->softDeletes) {
            return $this->update($id, ['deleted_at' => date('Y-m-d H:i:s')]);
        }
        
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
        return $this->db->execute($sql, [':id' => $id]) > 0;
    }

    /**
     * Force delete (even with soft deletes)
     */
    public function forceDelete(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
        return $this->db->execute($sql, [':id' => $id]) > 0;
    }

    /**
     * Restore soft deleted record
     */
    public function restore(int $id): bool
    {
        if (!$this->softDeletes) {
            return false;
        }
        
        $sql = "UPDATE {$this->table} SET deleted_at = NULL WHERE {$this->primaryKey} = :id";
        return $this->db->execute($sql, [':id' => $id]) > 0;
    }

    /**
     * Count records
     */
    public function count(array $conditions = []): int
    {
        $where = [];
        $params = [];
        
        if ($this->softDeletes) {
            $where[] = "deleted_at IS NULL";
        }
        
        foreach ($conditions as $column => $value) {
            if ($value !== null) {
                $where[] = "{$column} = :{$column}";
                $params[":{$column}"] = $value;
            }
        }
        
        $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
        
        $sql = "SELECT COUNT(*) FROM {$this->table} {$whereClause}";
        return (int) $this->db->fetchColumn($sql, $params);
    }

    /**
     * Check if record exists
     */
    public function exists(int $id): bool
    {
        $sql = "SELECT 1 FROM {$this->table} WHERE {$this->primaryKey} = :id";
        
        if ($this->softDeletes) {
            $sql .= " AND deleted_at IS NULL";
        }
        
        $sql .= " LIMIT 1";
        
        return $this->db->fetchColumn($sql, [':id' => $id]) !== false;
    }

    /**
     * Search records
     */
    public function search(string $query, array $searchColumns, int $limit = 50): array
    {
        $likeConditions = [];
        $params = [':query' => "%{$query}%"];
        
        foreach ($searchColumns as $column) {
            $likeConditions[] = "{$column} LIKE :query";
        }
        
        $sql = "SELECT * FROM {$this->table} WHERE (" . implode(" OR ", $likeConditions) . ")";
        
        if ($this->softDeletes) {
            $sql .= " AND deleted_at IS NULL";
        }
        
        $sql .= " LIMIT :limit";
        $params[':limit'] = $limit;
        
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Begin transaction
     */
    public function beginTransaction(): bool
    {
        return $this->db->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public function commit(): bool
    {
        return $this->db->commit();
    }

    /**
     * Rollback transaction
     */
    public function rollback(): bool
    {
        return $this->db->rollback();
    }
}
