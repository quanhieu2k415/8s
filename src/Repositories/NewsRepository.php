<?php
/**
 * News Repository
 * Database operations for news articles
 * 
 * @package ICOGroup
 */

namespace App\Repositories;

class NewsRepository extends BaseRepository
{
    protected string $table = 'news';
    protected bool $softDeletes = false; // News typically uses status

    /**
     * Get published news
     */
    public function getPublished(int $limit = 10): array
    {
        $sql = "SELECT id, title, slug, excerpt, content, image_url, category, 
                       is_featured, view_count, created_at, updated_at
                FROM {$this->table} 
                WHERE status = 'published'
                ORDER BY created_at DESC 
                LIMIT :limit";
        
        return $this->db->fetchAll($sql, [':limit' => $limit]);
    }

    /**
     * Get featured news
     */
    public function getFeatured(int $limit = 5): array
    {
        $sql = "SELECT id, title, slug, excerpt, image_url, category, created_at
                FROM {$this->table} 
                WHERE is_featured = 1 AND status = 'published'
                ORDER BY created_at DESC 
                LIMIT :limit";
        
        return $this->db->fetchAll($sql, [':limit' => $limit]);
    }

    /**
     * Get news by slug
     */
    public function findBySlug(string $slug): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE slug = :slug LIMIT 1";
        return $this->db->fetch($sql, [':slug' => $slug]);
    }

    /**
     * Get news by category
     */
    public function getByCategory(string $category, int $limit = 20): array
    {
        $sql = "SELECT id, title, slug, excerpt, image_url, category, created_at
                FROM {$this->table} 
                WHERE category = :category AND status = 'published'
                ORDER BY created_at DESC 
                LIMIT :limit";
        
        return $this->db->fetchAll($sql, [':category' => $category, ':limit' => $limit]);
    }

    /**
     * Get paginated news with filters
     */
    public function getPaginated(array $filters = [], int $page = 1, int $limit = 10): array
    {
        $where = [];
        $params = [];

        // Status filter
        if (!empty($filters['status'])) {
            $where[] = "status = :status";
            $params[':status'] = $filters['status'];
        }

        // Category filter
        if (!empty($filters['category'])) {
            $where[] = "category = :category";
            $params[':category'] = $filters['category'];
        }

        // Featured filter
        if (isset($filters['is_featured'])) {
            $where[] = "is_featured = :is_featured";
            $params[':is_featured'] = (int) $filters['is_featured'];
        }

        // Search filter
        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $where[] = "(title LIKE :search OR content LIKE :search2)";
            $params[':search'] = $search;
            $params[':search2'] = $search;
        }

        $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

        // Count total
        $countSql = "SELECT COUNT(*) FROM {$this->table} {$whereClause}";
        $total = (int) $this->db->fetchColumn($countSql, $params);

        // Get data
        $offset = ($page - 1) * $limit;
        $sql = "SELECT id, title, slug, excerpt, image_url, category, 
                       is_featured, status, view_count, created_at, updated_at
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
     * Create news with slug generation
     */
    public function createNews(array $data): int
    {
        // Generate slug if not provided
        if (empty($data['slug']) && !empty($data['title'])) {
            $data['slug'] = $this->generateSlug($data['title']);
        }

        // Set defaults
        $data['status'] = $data['status'] ?? 'draft';
        $data['is_featured'] = $data['is_featured'] ?? 0;
        $data['view_count'] = 0;
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        return $this->create($data);
    }

    /**
     * Update news
     */
    public function updateNews(int $id, array $data): bool
    {
        // Regenerate slug if title changed
        if (!empty($data['title']) && empty($data['slug'])) {
            $existing = $this->find($id);
            if ($existing && $existing['title'] !== $data['title']) {
                $data['slug'] = $this->generateSlug($data['title']);
            }
        }

        $data['updated_at'] = date('Y-m-d H:i:s');

        return $this->update($id, $data);
    }

    /**
     * Increment view count
     */
    public function incrementViews(int $id): void
    {
        $sql = "UPDATE {$this->table} SET view_count = view_count + 1 WHERE id = :id";
        $this->db->execute($sql, [':id' => $id]);
    }

    /**
     * Generate URL-friendly slug
     */
    private function generateSlug(string $title): string
    {
        // Convert to lowercase
        $slug = mb_strtolower($title, 'UTF-8');
        
        // Vietnamese character conversion
        $vietnamese = [
            'à', 'á', 'ạ', 'ả', 'ã', 'â', 'ầ', 'ấ', 'ậ', 'ẩ', 'ẫ', 'ă', 'ằ', 'ắ', 'ặ', 'ẳ', 'ẵ',
            'è', 'é', 'ẹ', 'ẻ', 'ẽ', 'ê', 'ề', 'ế', 'ệ', 'ể', 'ễ',
            'ì', 'í', 'ị', 'ỉ', 'ĩ',
            'ò', 'ó', 'ọ', 'ỏ', 'õ', 'ô', 'ồ', 'ố', 'ộ', 'ổ', 'ỗ', 'ơ', 'ờ', 'ớ', 'ợ', 'ở', 'ỡ',
            'ù', 'ú', 'ụ', 'ủ', 'ũ', 'ư', 'ừ', 'ứ', 'ự', 'ử', 'ữ',
            'ỳ', 'ý', 'ỵ', 'ỷ', 'ỹ',
            'đ'
        ];
        $ascii = [
            'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a',
            'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e',
            'i', 'i', 'i', 'i', 'i',
            'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o',
            'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u',
            'y', 'y', 'y', 'y', 'y',
            'd'
        ];
        
        $slug = str_replace($vietnamese, $ascii, $slug);
        
        // Remove non-alphanumeric characters
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        
        // Replace spaces with dashes
        $slug = preg_replace('/[\s-]+/', '-', $slug);
        
        // Trim dashes
        $slug = trim($slug, '-');
        
        // Make unique by adding timestamp if already exists
        $baseSlug = $slug;
        $counter = 1;
        
        while ($this->slugExists($slug)) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }

    /**
     * Check if slug exists
     */
    public function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $sql = "SELECT 1 FROM {$this->table} WHERE slug = :slug";
        $params = [':slug' => $slug];
        
        if ($excludeId !== null) {
            $sql .= " AND id != :id";
            $params[':id'] = $excludeId;
        }
        
        $sql .= " LIMIT 1";
        
        return $this->db->fetchColumn($sql, $params) !== false;
    }

    /**
     * Get categories with counts
     */
    public function getCategoryCounts(): array
    {
        $sql = "SELECT category, COUNT(*) as count 
                FROM {$this->table} 
                WHERE status = 'published'
                GROUP BY category 
                ORDER BY count DESC";
        
        return $this->db->fetchAll($sql);
    }

    /**
     * Get related news
     */
    public function getRelated(int $id, string $category, int $limit = 4): array
    {
        $sql = "SELECT id, title, slug, excerpt, image_url, created_at
                FROM {$this->table} 
                WHERE category = :category 
                  AND id != :id 
                  AND status = 'published'
                ORDER BY created_at DESC 
                LIMIT :limit";
        
        return $this->db->fetchAll($sql, [
            ':category' => $category,
            ':id' => $id,
            ':limit' => $limit
        ]);
    }
}
