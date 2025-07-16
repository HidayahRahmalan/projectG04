<?php
$page_title = "My Report History";
include 'templates/header.php';

if ($_SESSION['role'] == 5) { header("Location: admin_dashboard.php"); exit(); }
$user_id = $_SESSION['user_id'];

$results_per_page = 15;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$start_from = ($page - 1) * $results_per_page;

$filter_status = $_GET['status'] ?? '';
$filter_location = isset($_GET['location']) && is_numeric($_GET['location']) ? (int)$_GET['location'] : 0;

$where_clauses = ["r.Assigned_To = ?"];
$params = [$user_id];
$param_types = 'i';

if (!empty($filter_status)) { $where_clauses[] = "r.Status = ?"; $params[] = $filter_status; $param_types .= 's'; }
if (!empty($filter_location)) { $where_clauses[] = "r.Location = ?"; $params[] = $filter_location; $param_types .= 'i'; }
$where_sql = "WHERE " . implode(" AND ", $where_clauses);

// Get total records for pagination
$count_sql = "SELECT COUNT(*) FROM report r $where_sql";
$count_stmt = $conn->prepare($count_sql);
$count_stmt->bind_param($param_types, ...$params);
$count_stmt->execute();
$total_records = $count_stmt->get_result()->fetch_row()[0];
$count_stmt->close();
$total_pages = ceil($total_records / $results_per_page);

// Get paginated reports
$sql = "SELECT r.Report_ID, r.Title, r.Status, l.House_Name, r.Updated_at
        FROM report r JOIN locations l ON r.Location = l.Location_ID $where_sql
        ORDER BY r.Updated_at DESC LIMIT ?, ?";
$limit_params = $params;
$limit_params[] = $start_from;
$limit_params[] = $results_per_page;
$limit_param_types = $param_types . 'ii';

$stmt = $conn->prepare($sql);
$stmt->bind_param($limit_param_types, ...$limit_params);
$stmt->execute();
$reports = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$locations_for_filter = $conn->query("SELECT Location_ID, House_Name FROM locations ORDER BY House_Name")->fetch_all(MYSQLI_ASSOC);
?>

<h1 class="h2 mb-4">My Report History</h1>
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form class="row g-3 align-items-end" method="GET">
            <div class="col-md-4">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="pending" <?= ($filter_status == 'pending') ? 'selected' : '' ?>>Pending</option>
                    <option value="in_progress" <?= ($filter_status == 'in_progress') ? 'selected' : '' ?>>In Progress</option>
                    <option value="resolved" <?= ($filter_status == 'resolved') ? 'selected' : '' ?>>Resolved</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Location</label>
                <select name="location" class="form-select">
                    <option value="">All Locations</option>
                    <?php foreach ($locations_for_filter as $loc): ?>
                        <option value="<?= $loc['Location_ID'] ?>" <?= ($filter_location == $loc['Location_ID']) ? 'selected' : '' ?>><?= htmlspecialchars($loc['House_Name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
                <a href="my_all_reports.php" class="btn btn-secondary w-100">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light"><tr><th>ID</th><th>Title</th><th>Location</th><th>Status</th><th>Last Updated</th><th></th></tr></thead>
                <tbody>
                    <?php if (count($reports) > 0): foreach ($reports as $report): ?>
                    <tr style="cursor: pointer;" onclick="window.location='view_report.php?id=<?= $report['Report_ID'] ?>';">
                        <td><strong><?= $report['Report_ID'] ?></strong></td>
                        <td><?= htmlspecialchars($report['Title']) ?></td>
                        <td><?= htmlspecialchars($report['House_Name']) ?></td>
                        <td><?= getStatusBadge($report['Status']) ?></td>
                        <td><?= date('d M Y, h:i A', strtotime($report['Updated_at'])) ?></td>
                        <td><a href="view_report.php?id=<?= $report['Report_ID'] ?>" class="btn btn-outline-secondary btn-sm">View</a></td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr><td colspan="6" class="text-center p-4">No reports found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($total_pages > 1): ?>
        <nav class="mt-4"><ul class="pagination justify-content-end">
            <?php $query_params = http_build_query(array_filter(['status' => $filter_status, 'location' => $filter_location])); ?>
            <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>"><a class="page-link" href="?page=<?= $page - 1 ?>&<?= $query_params ?>">Previous</a></li>
            <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>"><a class="page-link" href="?page=<?= $page + 1 ?>&<?= $query_params ?>">Next</a></li>
        </ul></nav>
        <?php endif; ?>
    </div>
</div>

<?php include 'templates/footer.php'; ?>