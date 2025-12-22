<?php
/**
 * Search API - ICOGroup
 * Search news and programs
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../autoloader.php';

use App\Core\Database;

$db = Database::getInstance();
$query = trim($_GET['q'] ?? '');
$type = $_GET['type'] ?? 'all'; // all, news, programs
$limit = (int)($_GET['limit'] ?? 20);

if (empty($query) || strlen($query) < 2) {
    echo json_encode(['status' => false, 'message' => 'Từ khóa tìm kiếm phải có ít nhất 2 ký tự']);
    exit;
}

$searchQuery = '%' . $query . '%';
$results = [];

try {
    // Search news
    if ($type === 'all' || $type === 'news') {
        try {
            $sql = "SELECT id, title, content, image_url, created_at, 'news' as result_type 
                    FROM news 
                    WHERE (title LIKE ? OR content LIKE ?) 
                    AND is_published = 1 
                    AND deleted_at IS NULL 
                    ORDER BY created_at DESC 
                    LIMIT ?";
            
            $newsResults = $db->fetchAll($sql, [$searchQuery, $searchQuery, $limit]);
            
            if ($newsResults && is_array($newsResults)) {
                // Extract summary from content
                foreach ($newsResults as &$item) {
                    $item['summary'] = mb_substr(strip_tags($item['content'] ?? ''), 0, 150) . '...';
                    $item['url'] = 'tin-tuc.php?id=' . $item['id'];
                    unset($item['content']);
                }
                
                $results = array_merge($results, $newsResults);
            }
        } catch (Exception $e) {
            // Log error but continue with other searches
            error_log('News search error: ' . $e->getMessage());
        }
    }

    // Search programs (static data based on pages)
    if ($type === 'all' || $type === 'programs') {
        $programs = [
            [
                'id' => 'duhoc-nhat',
                'title' => 'Du học Nhật Bản',
                'summary' => 'Chương trình du học Nhật Bản với học bổng hấp dẫn, hỗ trợ visa và tư vấn miễn phí.',
                'url' => 'nhat.php',
                'image_url' => 'https://icogroup.vn/vnt_upload/weblink/banner_nhat.jpg',
                'keywords' => ['nhật', 'nhat', 'japan', 'du học', 'tiếng nhật', 'học bổng'],
                'result_type' => 'program'
            ],
            [
                'id' => 'duhoc-duc',
                'title' => 'Du học Đức',
                'summary' => 'Du học Đức miễn học phí với các chương trình đào tạo chất lượng cao.',
                'url' => 'duc.php',
                'image_url' => 'https://icogroup.vn/vnt_upload/weblink/banner_duc.jpg',
                'keywords' => ['đức', 'duc', 'germany', 'du học', 'tiếng đức', 'học bổng'],
                'result_type' => 'program'
            ],
            [
                'id' => 'duhoc-han',
                'title' => 'Du học Hàn Quốc',
                'summary' => 'Chương trình du học Hàn Quốc với chi phí hợp lý và cơ hội việc làm tốt.',
                'url' => 'han.php',
                'image_url' => 'https://icogroup.vn/vnt_upload/weblink/banner_han.jpg',
                'keywords' => ['hàn', 'han', 'korea', 'du học', 'tiếng hàn', 'học bổng'],
                'result_type' => 'program'
            ],
            [
                'id' => 'xkld-nhat',
                'title' => 'Xuất khẩu lao động Nhật Bản',
                'summary' => 'Chương trình XKLĐ Nhật Bản với thu nhập cao, đãi ngộ tốt.',
                'url' => 'xkldjp.php',
                'image_url' => 'https://icogroup.vn/vnt_upload/weblink/banner_xkldjp.jpg',
                'keywords' => ['xklđ', 'xkld', 'nhật', 'nhat', 'lao động', 'việc làm', 'thực tập sinh'],
                'result_type' => 'program'
            ],
            [
                'id' => 'xkld-han',
                'title' => 'Xuất khẩu lao động Hàn Quốc',
                'summary' => 'XKLĐ Hàn Quốc với mức lương hấp dẫn và môi trường làm việc tốt.',
                'url' => 'xkldhan.php',
                'image_url' => 'https://icogroup.vn/vnt_upload/weblink/banner_xkldhan.jpg',
                'keywords' => ['xklđ', 'xkld', 'xuất khẩu', 'xuat khau', 'hàn', 'han', 'hàn quốc', 'han quoc', 'korea', 'lao động', 'việc làm', 'eps', 'xuất khẩu lao động'],
                'result_type' => 'program'
            ],
            [
                'id' => 'xkld-dailoan',
                'title' => 'Xuất khẩu lao động Đài Loan',
                'summary' => 'XKLĐ Đài Loan với chi phí thấp, thu nhập ổn định.',
                'url' => 'xklddailoan.php',
                'image_url' => 'https://icogroup.vn/vnt_upload/weblink/banner_xklddailoan.jpg',
                'keywords' => ['xklđ', 'xkld', 'đài loan', 'dai loan', 'taiwan', 'lao động', 'việc làm'],
                'result_type' => 'program'
            ],
            [
                'id' => 'xkld-chauau',
                'title' => 'Xuất khẩu lao động Châu Âu',
                'summary' => 'XKLĐ Châu Âu (Ba Lan, Romania, Séc) với thu nhập cao.',
                'url' => 'xkldchauau.php',
                'image_url' => 'https://icogroup.vn/vnt_upload/weblink/banner_xkldchauau.jpg',
                'keywords' => ['xklđ', 'xkld', 'châu âu', 'chau au', 'ba lan', 'romania', 'séc', 'europe'],
                'result_type' => 'program'
            ],
            [
                'id' => 'huongnghiep',
                'title' => 'Hướng nghiệp',
                'summary' => 'Chương trình hướng nghiệp cho học sinh, sinh viên và người tìm việc.',
                'url' => 'huong-nghiep.php',
                'image_url' => 'https://icogroup.vn/vnt_upload/weblink/banner_huongnghiep.jpg',
                'keywords' => ['hướng nghiệp', 'huong nghiep', 'tư vấn', 'nghề nghiệp', 'định hướng'],
                'result_type' => 'program'
            ]
        ];

        $queryLower = mb_strtolower($query);
        
        foreach ($programs as $program) {
            // Check title match (case insensitive)
            $titleLower = mb_strtolower($program['title']);
            $summaryLower = mb_strtolower($program['summary']);
            
            $matchTitle = mb_strpos($titleLower, $queryLower) !== false;
            $matchSummary = mb_strpos($summaryLower, $queryLower) !== false;
            $matchKeywords = false;
            
            // Check each keyword
            foreach ($program['keywords'] as $keyword) {
                $keywordLower = mb_strtolower($keyword);
                // Check if query contains keyword or keyword contains query
                if (mb_strpos($queryLower, $keywordLower) !== false || mb_strpos($keywordLower, $queryLower) !== false) {
                    $matchKeywords = true;
                    break;
                }
            }
            
            if ($matchTitle || $matchSummary || $matchKeywords) {
                unset($program['keywords']);
                $results[] = $program;
            }
        }
    }

    echo json_encode([
        'status' => true,
        'query' => $query,
        'total' => count($results),
        'data' => $results
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => false, 'message' => $e->getMessage()]);
}
