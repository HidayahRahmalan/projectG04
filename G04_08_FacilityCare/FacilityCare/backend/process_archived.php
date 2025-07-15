<?php
session_name("admin_session");
session_start();
require_once '../connection.php';

// Pagination setup
$limit  = 5;
$page   = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Filters
$category   = $_GET['category'] ?? '';
$priority   = $_GET['priority'] ?? '';
$dateRange  = $_GET['date'] ?? '';
$startDate  = $_GET['start_date'] ?? '';
$endDate    = $_GET['end_date'] ?? '';
$search     = $_GET['search'] ?? '';

// Base query condition: Only archived reports
$conditions = "WHERE r.archive = TRUE";
$params = [];

// Category filter
if (!empty($category)) {
    $conditions .= " AND r.category = :category";
    $params[':category'] = $category;
}

// Priority filter
if (!empty($priority)) {
    $conditions .= " AND r.priority = :priority";
    $params[':priority'] = $priority;
}

// Date range filter
switch ($dateRange) {
    case 'today':
        $conditions .= " AND DATE(r.created_at) = CURDATE()";
        break;
    case 'this_week':
        $conditions .= " AND YEARWEEK(r.created_at, 1) = YEARWEEK(CURDATE(), 1)";
        break;
    case 'this_month':
        $conditions .= " AND MONTH(r.created_at) = MONTH(CURDATE()) AND YEAR(r.created_at) = YEAR(CURDATE())";
        break;
    case 'custom':
        if (!empty($startDate) && !empty($endDate)) {
            $conditions .= " AND DATE(r.created_at) BETWEEN :startDate AND :endDate";
            $params[':startDate'] = $startDate;
            $params[':endDate']   = $endDate;
        }
        break;
}

// Search filter
if (!empty($search)) {
    $conditions .= " AND (
        r.title LIKE :search OR
        r.category LIKE :search OR
        r.priority LIKE :search OR
        u.name LIKE :search
    )";
    $params[':search'] = "%$search%";
}

// ---------- Count total reports for pagination ----------
$countSql = "
    SELECT COUNT(*) 
    FROM report r
    JOIN user u ON r.user_id = u.user_id
    $conditions
";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalReports = $countStmt->fetchColumn();
$totalPages = ceil($totalReports / $limit);

// ---------- Fetch paginated archived reports ----------
$sql = "
    SELECT 
        r.*, 
        u.name AS reported_by,
        (
            SELECT sl.timestamp 
            FROM statuslog sl 
            WHERE sl.report_id = r.report_id AND sl.status = 'archive'
            ORDER BY sl.timestamp DESC 
            LIMIT 1
        ) AS archived_on
    FROM report r
    JOIN user u ON r.user_id = u.user_id
    $conditions
    ORDER BY archived_on DESC
    LIMIT :limit OFFSET :offset
";

$stmt = $pdo->prepare($sql);

// Bind filter and pagination parameters
foreach ($params as $key => $val) {
    $stmt->bindValue($key, $val);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

$stmt->execute();
$archivedReports = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
