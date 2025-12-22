<?php
/**
 * Registration Repository
 * Database operations for user registrations
 * 
 * @package ICOGroup
 */

namespace App\Repositories;

class RegistrationRepository extends BaseRepository
{
    protected string $table = 'user';
    protected bool $softDeletes = true;

    /**
     * Get registrations with filters
     */
    public function getFiltered(array $filters = [], int $page = 1, int $limit = 10): array
    {
        $where = [];
        $params = [];
        
        if ($this->softDeletes) {
            $where[] = "deleted_at IS NULL";
        }

        // Search filter
        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $where[] = "(ho_ten LIKE :search OR sdt LIKE :search2 OR dia_chi LIKE :search3)";
            $params[':search'] = $search;
            $params[':search2'] = $search;
            $params[':search3'] = $search;
        }

        // Program filter
        if (!empty($filters['chuong_trinh'])) {
            $where[] = "chuong_trinh = :chuong_trinh";
            $params[':chuong_trinh'] = $filters['chuong_trinh'];
        }

        // Country filter
        if (!empty($filters['quoc_gia'])) {
            $where[] = "quoc_gia = :quoc_gia";
            $params[':quoc_gia'] = $filters['quoc_gia'];
        }

        // Date range filter
        if (!empty($filters['date_from'])) {
            $where[] = "DATE(ngay_nhan) >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where[] = "DATE(ngay_nhan) <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }

        $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

        // Count total
        $countSql = "SELECT COUNT(*) FROM {$this->table} {$whereClause}";
        $total = (int) $this->db->fetchColumn($countSql, $params);

        // Get paginated data
        $offset = ($page - 1) * $limit;
        $sql = "SELECT id, ngay_nhan, ho_ten, nam_sinh, dia_chi, chuong_trinh, quoc_gia, sdt, ghi_chu 
                FROM {$this->table} {$whereClause} 
                ORDER BY id DESC 
                LIMIT :limit OFFSET :offset";
        
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
     * Get statistics
     */
    public function getStats(): array
    {
        $today = date('Y-m-d');
        $weekStart = date('Y-m-d', strtotime('monday this week'));
        $monthStart = date('Y-m-01');

        $deletedCondition = $this->softDeletes ? "AND deleted_at IS NULL" : "";

        // Total count
        $total = (int) $this->db->fetchColumn(
            "SELECT COUNT(*) FROM {$this->table} WHERE 1=1 {$deletedCondition}"
        );

        // Today count
        $todayCount = (int) $this->db->fetchColumn(
            "SELECT COUNT(*) FROM {$this->table} WHERE DATE(ngay_nhan) = :today {$deletedCondition}",
            [':today' => $today]
        );

        // Week count
        $weekCount = (int) $this->db->fetchColumn(
            "SELECT COUNT(*) FROM {$this->table} WHERE DATE(ngay_nhan) >= :week_start {$deletedCondition}",
            [':week_start' => $weekStart]
        );

        // Month count
        $monthCount = (int) $this->db->fetchColumn(
            "SELECT COUNT(*) FROM {$this->table} WHERE DATE(ngay_nhan) >= :month_start {$deletedCondition}",
            [':month_start' => $monthStart]
        );

        return [
            'total' => $total,
            'today' => $todayCount,
            'week' => $weekCount,
            'month' => $monthCount
        ];
    }

    /**
     * Get registrations by program
     */
    public function getByProgram(string $program): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE chuong_trinh = :program";
        
        if ($this->softDeletes) {
            $sql .= " AND deleted_at IS NULL";
        }
        
        $sql .= " ORDER BY id DESC";
        
        return $this->db->fetchAll($sql, [':program' => $program]);
    }

    /**
     * Get registrations by country
     */
    public function getByCountry(string $country): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE quoc_gia = :country";
        
        if ($this->softDeletes) {
            $sql .= " AND deleted_at IS NULL";
        }
        
        $sql .= " ORDER BY id DESC";
        
        return $this->db->fetchAll($sql, [':country' => $country]);
    }

    /**
     * Get registrations by date range
     */
    public function getByDateRange(string $from, string $to): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE DATE(ngay_nhan) BETWEEN :from AND :to";
        
        if ($this->softDeletes) {
            $sql .= " AND deleted_at IS NULL";
        }
        
        $sql .= " ORDER BY ngay_nhan DESC";
        
        return $this->db->fetchAll($sql, [':from' => $from, ':to' => $to]);
    }

    /**
     * Get recent registrations
     */
    public function getRecent(int $limit = 10): array
    {
        $sql = "SELECT id, ngay_nhan, ho_ten, nam_sinh, dia_chi, chuong_trinh, quoc_gia, sdt, ghi_chu 
                FROM {$this->table}";
        
        if ($this->softDeletes) {
            $sql .= " WHERE deleted_at IS NULL";
        }
        
        $sql .= " ORDER BY id DESC LIMIT :limit";
        
        return $this->db->fetchAll($sql, [':limit' => $limit]);
    }

    /**
     * Get distinct programs
     */
    public function getPrograms(): array
    {
        $sql = "SELECT DISTINCT chuong_trinh FROM {$this->table}";
        
        if ($this->softDeletes) {
            $sql .= " WHERE deleted_at IS NULL";
        }
        
        $sql .= " ORDER BY chuong_trinh";
        
        $result = $this->db->fetchAll($sql);
        return array_column($result, 'chuong_trinh');
    }

    /**
     * Get distinct countries
     */
    public function getCountries(): array
    {
        $sql = "SELECT DISTINCT quoc_gia FROM {$this->table}";
        
        if ($this->softDeletes) {
            $sql .= " WHERE deleted_at IS NULL";
        }
        
        $sql .= " ORDER BY quoc_gia";
        
        $result = $this->db->fetchAll($sql);
        return array_column($result, 'quoc_gia');
    }

    /**
     * Check if phone exists
     */
    public function phoneExists(string $phone, ?int $excludeId = null): bool
    {
        $sql = "SELECT 1 FROM {$this->table} WHERE sdt = :phone";
        $params = [':phone' => $phone];
        
        if ($excludeId !== null) {
            $sql .= " AND id != :id";
            $params[':id'] = $excludeId;
        }
        
        if ($this->softDeletes) {
            $sql .= " AND deleted_at IS NULL";
        }
        
        $sql .= " LIMIT 1";
        
        return $this->db->fetchColumn($sql, $params) !== false;
    }

    /**
     * Get export data
     */
    public function getExportData(?string $from = null, ?string $to = null): array
    {
        $where = [];
        $params = [];

        if ($this->softDeletes) {
            $where[] = "deleted_at IS NULL";
        }

        if ($from) {
            $where[] = "DATE(ngay_nhan) >= :from";
            $params[':from'] = $from;
        }

        if ($to) {
            $where[] = "DATE(ngay_nhan) <= :to";
            $params[':to'] = $to;
        }

        $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

        $sql = "SELECT id, ngay_nhan, ho_ten, sdt, nam_sinh, dia_chi, chuong_trinh, quoc_gia, ghi_chu 
                FROM {$this->table} {$whereClause} 
                ORDER BY id ASC";

        return $this->db->fetchAll($sql, $params);
    }
}
