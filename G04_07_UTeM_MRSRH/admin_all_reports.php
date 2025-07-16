<?php
$page_title = "All System Reports";
include 'templates/header.php';

if ($_SESSION['role'] != 5) { header("Location: login.php"); exit(); }

$results_per_page = 15;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$start_from = ($page - 1) * $results_per_page;

// Filter and Search Logic
$filter_status = $_GET['status'] ?? '';
$filter_location = isset($_GET['location']) && is_numeric($_GET['location']) ? (int)$_GET['location'] : 0;
$filter_staff = isset($_GET['staff']) && is_numeric($_GET['staff']) ? (int)$_GET['staff'] : 0;
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

$where_clauses = [];
$params = [];
$param_types = '';

if (!empty($filter_status)) { $where_clauses[] = "r.Status = ?"; $params[] = $filter_status; $param_types .= 's'; }
if (!empty($filter_location)) { $where_clauses[] = "r.Location = ?"; $params[] = $filter_location; $param_types .= 'i'; }
if (!empty($filter_staff)) { $where_clauses[] = "r.Assigned_To = ?"; $params[] = $filter_staff; $param_types .= 'i'; }
if (!empty($search_query)) { $where_clauses[] = "(r.Title LIKE ? OR r.Report_ID = ?)"; $search_like = "%{$search_query}%"; $params[] = $search_like; $params[] = $search_query; $param_types .= 'si';}

$where_sql = count($where_clauses) > 0 ? "WHERE " . implode(" AND ", $where_clauses) : "";

// Get total records for pagination
$count_sql = "SELECT COUNT(*) FROM report r $where_sql";
$count_stmt = $conn->prepare($count_sql);
if (count($params) > 0) { $count_stmt->bind_param($param_types, ...$params); }
$count_stmt->execute();
$total_records = $count_stmt->get_result()->fetch_row()[0];
$count_stmt->close();
$total_pages = ceil($total_records / $results_per_page);

// Get paginated reports
$sql = "SELECT r.Report_ID, r.Title, r.Status, r.Urgency, l.House_Name, assignee.Name as assignee_name, r.Updated_at 
        FROM report r 
        JOIN locations l ON r.Location = l.Location_ID 
        LEFT JOIN users assignee ON r.Assigned_To = assignee.User_ID 
        $where_sql 
        ORDER BY r.Updated_at DESC 
        LIMIT ?, ?";

$limit_params = $params;
$limit_params[] = $start_from;
$limit_params[] = $results_per_page;
$limit_param_types = $param_types . 'ii';

$stmt = $conn->prepare($sql);
$stmt->bind_param($limit_param_types, ...$limit_params);
$stmt->execute();
$reports = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Data for filter dropdowns
$locations_for_filter = $conn->query("SELECT Location_ID, House_Name FROM locations ORDER BY House_Name")->fetch_all(MYSQLI_ASSOC);
$staff_for_filter = $conn->query("SELECT User_ID, Name FROM users WHERE Role != 5 ORDER BY Name")->fetch_all(MYSQLI_ASSOC);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2">All System Reports</h1>
</div>
    
<div class="card shadow-sm mb-4">
    <div class="card-header bg-light">
        <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filter & Search</h5>
    </div>
    <div class="card-body">
        <form class="row g-3 align-items-end" method="GET">
            <div class="col-md-3"><label class="form-label">Search by Title/ID</label><input type="text" name="search" class="form-control" value="<?= htmlspecialchars($search_query) ?>"></div>
            <div class="col-md-2"><label class="form-label">Status</label><select name="status" class="form-select"><option value="">All</option><option value="pending" <?= ($filter_status == 'pending') ? 'selected' : '' ?>>Pending</option><option value="in_progress" <?= ($filter_status == 'in_progress') ? 'selected' : '' ?>>In Progress</option><option value="resolved" <?= ($filter_status == 'resolved') ? 'selected' : '' ?>>Resolved</option></select></div>
            <div class="col-md-2"><label class="form-label">Location</label><select name="location" class="form-select"><option value="">All</option><?php foreach ($locations_for_filter as $loc): ?><option value="<?= $loc['Location_ID'] ?>" <?= ($filter_location == $loc['Location_ID']) ? 'selected' : '' ?>><?= htmlspecialchars($loc['House_Name']) ?></option><?php endforeach; ?></select></div>
            <div class="col-md-2"><label class="form-label">Staff</label><select name="staff" class="form-select"><option value="">All</option><?php foreach ($staff_for_filter as $staff): ?><option value="<?= $staff['User_ID'] ?>" <?= ($filter_staff == $staff['User_ID']) ? 'selected' : '' ?>><?= htmlspecialchars($staff['Name']) ?></option><?php endforeach; ?></select></div>
            <div class="col-md-3 d-flex gap-2"><button type="submit" class="btn btn-primary w-100">Filter</button> <a href="admin_all_reports.php" class="btn btn-secondary w-100">Reset</a></div>
        </form>
    </div>
</div>
<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light"><tr><th>ID</th><th>Title</th><th>Location</th><th>Status</th><th>Urgency</th><th>Staff Assigned</th><th>Last Updated</th><th></th></tr></thead>
                <tbody>
                    <?php if (count($reports) > 0): foreach ($reports as $report): ?>
                    <tr>
                        <td><strong><?= $report['Report_ID'] ?></strong></td>
                        <td><?= htmlspecialchars($report['Title']) ?></td>
                        <td><?= htmlspecialchars($report['House_Name']) ?></td>
                        <td><?= getStatusBadge($report['Status']) ?></td>
                        <td><span class="fw-bold <?= getUrgencyClass($report['Urgency']) ?>"><?= ucfirst($report['Urgency']) ?></span></td>
                        <td><?= htmlspecialchars($report['assignee_name'] ?? 'N/A') ?></td>
                        <td><?= date('d M Y, h:i A', strtotime($report['Updated_at'])) ?></td>
                        <td><a href="view_report.php?id=<?= $report['Report_ID'] ?>" class="btn btn-outline-secondary btn-sm"><i class="fas fa-eye"></i></a></td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr><td colspan="8" class="text-center p-4">No reports found matching your criteria.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if ($total_pages > 1): ?>
        <nav class="mt-4"><ul class="pagination justify-content-end">
            <?php $query_params = http_build_query(array_filter(['status' => $filter_status, 'location' => $filter_location, 'staff' => $filter_staff, 'search' => $search_query])); ?>
            <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>"><a class="page-link" href="?page=<?= $page - 1 ?>&<?= $query_params ?>">Previous</a></li>
            <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>"><a class="page-link" href="?page=<?= $page + 1 ?>&<?= $query_params ?>">Next</a></li>
        </ul></nav>
        <?php endif; ?>
    </div>
</div>

<?php include 'templates/footer.php'; ?>