<?php
/**
 * Analytics API - ICOGroup
 * Returns detailed statistics with chart data
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../autoloader.php';

use App\Core\Database;

$db = Database::getInstance();
$type = $_GET['type'] ?? 'overview';

try {
    switch ($type) {
        case 'overview':
            echo json_encode(getOverview($db));
            break;
        case 'daily':
            $days = (int)($_GET['days'] ?? 30);
            echo json_encode(getDailyStats($db, $days));
            break;
        case 'monthly':
            $months = (int)($_GET['months'] ?? 12);
            echo json_encode(getMonthlyStats($db, $months));
            break;
        case 'by_program':
            echo json_encode(getByProgram($db));
            break;
        case 'by_country':
            echo json_encode(getByCountry($db));
            break;
        case 'recent':
            $limit = (int)($_GET['limit'] ?? 10);
            echo json_encode(getRecentRegistrations($db, $limit));
            break;
        default:
            echo json_encode(['status' => false, 'message' => 'Invalid type']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => false, 'message' => $e->getMessage()]);
}

/**
 * Get overview statistics
 */
function getOverview($db) {
    $today = date('Y-m-d');
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    $weekStart = date('Y-m-d', strtotime('monday this week'));
    $lastWeekStart = date('Y-m-d', strtotime('monday last week'));
    $lastWeekEnd = date('Y-m-d', strtotime('sunday last week'));
    $monthStart = date('Y-m-01');
    $lastMonthStart = date('Y-m-01', strtotime('-1 month'));
    $lastMonthEnd = date('Y-m-t', strtotime('-1 month'));

    // Registration stats
    $totalRegistrations = (int) $db->fetchColumn("SELECT COUNT(*) FROM user WHERE deleted_at IS NULL");
    $todayRegistrations = (int) $db->fetchColumn("SELECT COUNT(*) FROM user WHERE DATE(ngay_nhan) = ? AND deleted_at IS NULL", [$today]);
    $yesterdayRegistrations = (int) $db->fetchColumn("SELECT COUNT(*) FROM user WHERE DATE(ngay_nhan) = ? AND deleted_at IS NULL", [$yesterday]);
    $weekRegistrations = (int) $db->fetchColumn("SELECT COUNT(*) FROM user WHERE DATE(ngay_nhan) >= ? AND deleted_at IS NULL", [$weekStart]);
    $lastWeekRegistrations = (int) $db->fetchColumn("SELECT COUNT(*) FROM user WHERE DATE(ngay_nhan) BETWEEN ? AND ? AND deleted_at IS NULL", [$lastWeekStart, $lastWeekEnd]);
    $monthRegistrations = (int) $db->fetchColumn("SELECT COUNT(*) FROM user WHERE DATE(ngay_nhan) >= ? AND deleted_at IS NULL", [$monthStart]);
    $lastMonthRegistrations = (int) $db->fetchColumn("SELECT COUNT(*) FROM user WHERE DATE(ngay_nhan) BETWEEN ? AND ? AND deleted_at IS NULL", [$lastMonthStart, $lastMonthEnd]);

    // Contact stats
    $totalContacts = (int) $db->fetchColumn("SELECT COUNT(*) FROM contacts WHERE deleted_at IS NULL");
    $todayContacts = (int) $db->fetchColumn("SELECT COUNT(*) FROM contacts WHERE DATE(created_at) = ? AND deleted_at IS NULL", [$today]);
    $monthContacts = (int) $db->fetchColumn("SELECT COUNT(*) FROM contacts WHERE DATE(created_at) >= ? AND deleted_at IS NULL", [$monthStart]);

    // News stats
    $totalNews = (int) $db->fetchColumn("SELECT COUNT(*) FROM news WHERE deleted_at IS NULL");
    $publishedNews = (int) $db->fetchColumn("SELECT COUNT(*) FROM news WHERE is_published = 1 AND deleted_at IS NULL");

    // Calculate growth percentages
    $dailyGrowth = $yesterdayRegistrations > 0 ? round((($todayRegistrations - $yesterdayRegistrations) / $yesterdayRegistrations) * 100, 1) : 0;
    $weeklyGrowth = $lastWeekRegistrations > 0 ? round((($weekRegistrations - $lastWeekRegistrations) / $lastWeekRegistrations) * 100, 1) : 0;
    $monthlyGrowth = $lastMonthRegistrations > 0 ? round((($monthRegistrations - $lastMonthRegistrations) / $lastMonthRegistrations) * 100, 1) : 0;

    return [
        'status' => true,
        'registrations' => [
            'total' => $totalRegistrations,
            'today' => $todayRegistrations,
            'week' => $weekRegistrations,
            'month' => $monthRegistrations,
            'daily_growth' => $dailyGrowth,
            'weekly_growth' => $weeklyGrowth,
            'monthly_growth' => $monthlyGrowth
        ],
        'contacts' => [
            'total' => $totalContacts,
            'today' => $todayContacts,
            'month' => $monthContacts
        ],
        'news' => [
            'total' => $totalNews,
            'published' => $publishedNews
        ]
    ];
}

/**
 * Get daily registration stats for chart (last N days)
 */
function getDailyStats($db, $days = 30) {
    $data = [];
    
    for ($i = $days - 1; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $count = (int) $db->fetchColumn(
            "SELECT COUNT(*) FROM user WHERE DATE(ngay_nhan) = ? AND deleted_at IS NULL",
            [$date]
        );
        
        $data[] = [
            'date' => $date,
            'label' => date('d/m', strtotime($date)),
            'count' => $count
        ];
    }
    
    return ['status' => true, 'data' => $data];
}

/**
 * Get monthly registration stats for chart (last N months)
 */
function getMonthlyStats($db, $months = 12) {
    $data = [];
    
    for ($i = $months - 1; $i >= 0; $i--) {
        $monthStart = date('Y-m-01', strtotime("-$i months"));
        $monthEnd = date('Y-m-t', strtotime("-$i months"));
        $monthLabel = date('m/Y', strtotime($monthStart));
        
        $count = (int) $db->fetchColumn(
            "SELECT COUNT(*) FROM user WHERE DATE(ngay_nhan) BETWEEN ? AND ? AND deleted_at IS NULL",
            [$monthStart, $monthEnd]
        );
        
        $data[] = [
            'month' => $monthStart,
            'label' => $monthLabel,
            'count' => $count
        ];
    }
    
    return ['status' => true, 'data' => $data];
}

/**
 * Get registrations by program
 */
function getByProgram($db) {
    $sql = "SELECT chuong_trinh as program, COUNT(*) as count 
            FROM user 
            WHERE deleted_at IS NULL AND chuong_trinh IS NOT NULL AND chuong_trinh != ''
            GROUP BY chuong_trinh 
            ORDER BY count DESC";
    
    $data = $db->fetchAll($sql);
    
    return ['status' => true, 'data' => $data];
}

/**
 * Get registrations by country
 */
function getByCountry($db) {
    $sql = "SELECT quoc_gia as country, COUNT(*) as count 
            FROM user 
            WHERE deleted_at IS NULL AND quoc_gia IS NOT NULL AND quoc_gia != ''
            GROUP BY quoc_gia 
            ORDER BY count DESC";
    
    $data = $db->fetchAll($sql);
    
    return ['status' => true, 'data' => $data];
}

/**
 * Get recent registrations
 */
function getRecentRegistrations($db, $limit = 10) {
    $sql = "SELECT id, ho_ten, sdt, chuong_trinh, quoc_gia, ngay_nhan 
            FROM user 
            WHERE deleted_at IS NULL 
            ORDER BY id DESC 
            LIMIT ?";
    
    $data = $db->fetchAll($sql, [$limit]);
    
    return ['status' => true, 'data' => $data];
}
