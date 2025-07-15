<?php
session_name("staff_session");
session_start();
include 'connection.php';

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
  header("Location: homepage.php");
  exit();
}

$statusFilter = $_GET['status'] ?? null;
$statusFilter = strtolower(str_replace('_', '', $statusFilter)); // normalize filter

$query = "
    SELECT r.report_id, r.title, DATE_FORMAT(r.created_at, '%d-%m-%Y') AS formatted_date,
        (SELECT status FROM StatusLog WHERE report_id = r.report_id ORDER BY timestamp DESC LIMIT 1) AS status
    FROM Report r
    WHERE r.user_id = :user_id
";

// Filter "pending" (no status log exists)
if ($statusFilter === 'pending') {
  $query .= " AND NOT EXISTS (
        SELECT 1 FROM StatusLog s WHERE s.report_id = r.report_id
    )";
}
// Filter by specific status
elseif (in_array($statusFilter, ['open', 'inprogress', 'resolved'])) {
  $query .= " AND LOWER(REPLACE((
        SELECT status FROM StatusLog WHERE report_id = r.report_id ORDER BY timestamp DESC LIMIT 1
    ), '_', '')) = :status";
}

$query .= " ORDER BY r.created_at DESC";

$stmt = $pdo->prepare($query);

// Bind values
$params = ['user_id' => $user_id];
if (in_array($statusFilter, ['open', 'inprogress', 'resolved'])) {
  $params['status'] = $statusFilter;
}

$stmt->execute($params);
$reports = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Reports | Maintenance System</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="CSS/style.css">
</head>

<body>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
    <div class="container">
      <a class="navbar-brand fw-bold" href="homepageStaff.php">
        <i class="fas fa-tools me-2 text-primary"></i>FacilityCare
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item">
            <a class="nav-link" href="homepageStaff.php"><i class="fas fa-home me-1"></i> Home</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="newReport.php"><i class="fas fa-plus-circle me-1"></i> New Report</a>
          </li>
          <li class="nav-item">
            <a class="nav-link active" href="reportListings.php"><i class="fas fa-list me-1"></i> My Reports</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="developers.php"><i class="fas fa-user-cog me-1"></i> About Us</a>
          </li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
              <i class="fas fa-user-circle me-1"></i>
              <?php echo isset($_SESSION['name']) ? htmlspecialchars($_SESSION['name']) : 'Staff User'; ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i>My Profile</a></li>
              <li>
                <hr class="dropdown-divider">
              </li>
              <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
            </ul>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Main Content -->
  <div class="container py-5">
    <h3 class="mb-4"><i class="fas fa-list me-2"></i>My Reports</h3>

    <!-- Filter Buttons -->
    <div class="mb-4">
      <a href="reportListings.php?" class="btn btn-outline-primary me-2">All</a>
      <a href="reportListings.php?status=pending" class="btn btn-outline-secondary me-2">Pending</a>
      <a href="reportListings.php?status=open" class="btn btn-outline-info me-2">Open</a>
      <a href="reportListings.php?status=inprogress" class="btn btn-outline-warning me-2">In Progress</a>
      <a href="reportListings.php?status=resolved" class="btn btn-outline-success">Resolved</a>
    </div>

    <!-- Reports Table -->
    <div class="card shadow-sm">
      <div class="card-body table-responsive">
        <table class="table table-bordered align-middle">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Report Title</th>
              <th>Date Submitted</th>
              <th>Status</th>
              <th>Details</th>
            </tr>
          </thead>
          <tbody>
            <?php if (count($reports) > 0): ?>
              <?php foreach ($reports as $index => $report): ?>
                <tr>
                  <td><?= $index + 1 ?></td>
                  <td><?= htmlspecialchars($report['title']) ?></td>
                  <td><?= $report['formatted_date'] ?></td>
                  <td>
                    <?php
                    $status = $report['status'] ?? 'Pending';

                    $badgeClass = match (strtolower($status)) {
                      'inprogress' => 'bg-warning',
                      'resolved'   => 'bg-success',
                      default      => 'bg-primary' // default to pending
                    };

                    echo "<span class='badge $badgeClass'>" . ucwords(str_replace('_', ' ', $status)) . "</span>";

                    ?>
                  </td>
                  <td><a href="reportDetails.php?id=<?= $report['report_id'] ?>" class="btn btn-sm btn-outline-primary">View</a></td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="5" class="text-center text-muted">No reports found.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <footer class="bg-light py-4 mt-5 border-top">
    <div class="container text-center text-muted">
      <p class="mb-0">&copy; 2025 FacilityCare. All rights reserved.</p>
    </div>
  </footer>
</body>

</html>