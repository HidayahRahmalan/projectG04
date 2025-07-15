<?php
session_name("admin_session");
session_start();
require_once '../connection.php';

// Get filters and search inputs
$statusFilter   = $_GET['status'] ?? '';
$categoryFilter = $_GET['category'] ?? '';
$priorityFilter = $_GET['priority'] ?? '';
$dateFilter     = $_GET['date'] ?? '';
$startDate      = $_GET['start_date'] ?? '';
$endDate        = $_GET['end_date'] ?? '';
$searchQuery    = $_GET['search'] ?? '';

$filterConditions = "WHERE 1";
$params = [];

// Pagination setup
$limit  = 5;
$page   = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// ----------------------
// 1. Shared Filter Logic
// ----------------------
$filterConditions = " WHERE 1 = 1 ";
$params = [];

$filterConditions .= " AND r.archive = 0";
$params = [];

// Search (used in both main and count queries)
if (!empty($searchQuery)) {
    $filterConditions .= " AND (
        r.title LIKE :search OR 
        r.category LIKE :search OR 
        r.priority LIKE :search OR 
        u.name LIKE :search
    )";
    $params[':search'] = '%' . $searchQuery . '%';
}

// Status (handle pending manually)
if (!empty($statusFilter)) {
    if ($statusFilter === 'pending') {
        // report yang takde langsung entry dalam statuslog
        $filterConditions .= " AND (
            SELECT COUNT(*) FROM statuslog sl WHERE sl.report_id = r.report_id
        ) = 0";
    } else {
        // report yang ada entry, dan latest status = apa yg ditapis
        $filterConditions .= " AND (
            SELECT sl.status 
            FROM statuslog sl 
            WHERE sl.report_id = r.report_id 
            ORDER BY sl.timestamp DESC 
            LIMIT 1
        ) = :status";
        $params[':status'] = $statusFilter;
    }
}

// Category
if (!empty($categoryFilter)) {
    $filterConditions .= " AND r.category = :category";
    $params[':category'] = $categoryFilter;
}

// Priority
if (!empty($priorityFilter)) {
    $filterConditions .= " AND r.priority = :priority";
    $params[':priority'] = $priorityFilter;
}

// Date Range
switch ($dateFilter) {
    case 'today':
        $filterConditions .= " AND DATE(r.created_at) = CURDATE()";
        break;
    case 'this_week':
        $filterConditions .= " AND YEARWEEK(r.created_at, 1) = YEARWEEK(CURDATE(), 1)";
        break;
    case 'this_month':
        $filterConditions .= " AND MONTH(r.created_at) = MONTH(CURDATE()) AND YEAR(r.created_at) = YEAR(CURDATE())";
        break;
    case 'custom':
        if ($startDate && $endDate) {
            $filterConditions .= " AND DATE(r.created_at) BETWEEN :startDate AND :endDate";
            $params[':startDate'] = $startDate;
            $params[':endDate']   = $endDate;
        }
        break;
}

// ----------------------
// 2. Count total reports
// ----------------------
$countSql = "
    SELECT COUNT(*) 
    FROM report r
    JOIN user u ON r.user_id = u.user_id
    $filterConditions
";

$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalReports = $countStmt->fetchColumn();
$totalPages = ceil($totalReports / $limit);

// ----------------------
// 3. Fetch paginated data
// ----------------------
$sql = "
    SELECT 
        r.report_id,
        r.title,
        r.category,
        r.priority,
        r.created_at,
        u.name AS reported_by,
        COALESCE((
            SELECT sl.status    
            FROM statuslog sl 
            WHERE sl.report_id = r.report_id 
            ORDER BY sl.timestamp DESC 
            LIMIT 1
        ), 'pending') AS status
    FROM report r
    JOIN user u ON r.user_id = u.user_id
    $filterConditions
    ORDER BY r.created_at DESC
    LIMIT :limit OFFSET :offset
";

$stmt = $pdo->prepare($sql);

// Bind normal params
foreach ($params as $key => $val) {
    $stmt->bindValue($key, $val);
}

// Bind pagination params
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

$stmt->execute();
$reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
