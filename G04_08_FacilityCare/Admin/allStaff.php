<?php
session_name("admin_session");
session_start();
require_once '../connection.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

function getStaffReportCount($pdo, $userId)
{
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM report WHERE user_id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetchColumn();
}

$reportCountFilter = $_GET['report_count'] ?? '';
$search = $_GET['search'] ?? '';

$query = "
    SELECT u.user_id, u.name, u.email, u.phone, u.profile_pic,
           COUNT(r.report_id) AS report_count
    FROM user u
    LEFT JOIN report r ON u.user_id = r.user_id
    WHERE u.position = 'staff'
";

// Tambah filter untuk search
if (!empty($search)) {
    $query .= " AND (u.name LIKE :search OR u.email LIKE :search)";
}

// Group by supaya COUNT boleh kira
$query .= " GROUP BY u.user_id";

// Tambah filter untuk report count range
if ($reportCountFilter === '0') {
    $query .= " HAVING report_count = 0";
} elseif ($reportCountFilter === '1-5') {
    $query .= " HAVING report_count BETWEEN 1 AND 5";
} elseif ($reportCountFilter === '6+') {
    $query .= " HAVING report_count >= 6";
}

// Prepare & bind
$stmt = $pdo->prepare($query);

if (!empty($search)) {
    $stmt->bindValue(':search', "%$search%");
}

$stmt->execute();
$staffList = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Guna result yang dah filtered
$staffUsers = $staffList;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Technicians - Admin Panel</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../CSS/styleAdmin.css">
    <style>
        .tech-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }

        .specialty-badge {
            font-size: 0.75rem;
            margin-right: 5px;
        }

        .status-active {
            background-color: #d3f9d8;
            color: #0a5200;
        }

        .status-inactive {
            background-color: #ffebee;
            color: #c62828;
        }

        .rating-star {
            color: #ffc107;
        }

        .pagination .page-item.active .page-link {
            background-color: var(--admin-primary);
            border-color: var(--admin-primary);
        }
    </style>

</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Admin Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block admin-sidebar p-0">
                <div class="d-flex flex-column p-3">
                    <div class="text-center mb-4">
                        <h4 class="text-white mb-0">
                            <i class="fas fa-tools me-2"></i>FacilityCare
                        </h4>
                        <small class="text-white-50">Admin Panel</small>
                    </div>
                    <hr class="text-white-50">
                    <ul class="nav nav-pills flex-column mb-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="allReports.php">
                                <i class="bi bi-list-check"></i> All Reports
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="archived.php">
                                <i class="bi bi-archive"></i> Archived
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="allTechnician.php">
                                <i class="fas fa-users me-2"></i> Technician Management
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="allStaff.php">
                                <i class="fas fa-user-tie me-2"></i> Staff Management
                            </a>
                        </li>
                    </ul>
                    <hr class="text-white-50">
                    <div class="dropdown">
                        <a href="#" class="d-flex justify-content-center align-items-center text-white text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                            <strong> <?php echo isset($_SESSION['name']) ? htmlspecialchars($_SESSION['name']) : 'Staff User'; ?></strong>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark text-small shadow">
                            <li><a class="dropdown-item" href="adminProfile.php"><i class="bi bi-person me-2"></i>Profile</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="../logout.php"><i class="bi bi-box-arrow-left me-2"></i>Sign out</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Staff Management</h1>
                </div>

                <!-- Filters Card -->
                <div class="card filter-card mb-3">
                    <div class="card-body">
                        <form class="row g-3" method="GET" action="">
                            <div class="col-md-6">
                                <label class="form-label">Report Count</label>
                                <select class="form-select" name="report_count">
                                    <option value="" <?= (!isset($_GET['report_count']) || $_GET['report_count'] === '') ? 'selected' : '' ?>>All</option>
                                    <option value="0" <?= ($_GET['report_count'] ?? '') === '0' ? 'selected' : '' ?>>0 Reports</option>
                                    <option value="1-5" <?= ($_GET['report_count'] ?? '') === '1-5' ? 'selected' : '' ?>>1–5 Reports</option>
                                    <option value="6+" <?= ($_GET['report_count'] ?? '') === '6+' ? 'selected' : '' ?>>>6 Reports</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Search</label>
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control" placeholder="Search staff..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                                    <button class="btn btn-outline-secondary" type="submit">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Technicians Table -->
                <div class="card admin-card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Staff</th>
                                        <th>Contact</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($staffUsers as $index => $staff):
                                        $photoPath = !empty($staff['profile_pic']) ? '../' . $staff['profile_pic'] : '../images/staff_profile/default-avatar.png';
                                        $reportCount = getStaffReportCount($pdo, $staff['user_id']);
                                    ?>
                                        <tr>
                                            <td>#<?= 1 + $index ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="<?= htmlspecialchars($photoPath) ?>" class="tech-avatar me-2" alt="Avatar">
                                                    <div>
                                                        <h6 class="mb-0"><?= htmlspecialchars($staff['name']) ?></h6>
                                                        <small class="text-muted"><?= htmlspecialchars($staff['user_id']) ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <small class="text-muted">Email:</small>
                                                    <p class="mb-0"><?= htmlspecialchars($staff['email']) ?></p>
                                                </div>
                                                <div class="mt-1">
                                                    <small class="text-muted">Phone:</small>
                                                    <p class="mb-0"><?= htmlspecialchars($staff['phone']) ?: '-' ?></p>
                                                </div>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary me-1 viewBtn" title="View"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#viewStaffModal"
                                                    data-id="<?= $staff['user_id'] ?>"
                                                    data-name="<?= htmlspecialchars($staff['name']) ?>"
                                                    data-email="<?= htmlspecialchars($staff['email']) ?>"
                                                    data-phone="<?= htmlspecialchars($staff['phone']) ?>"
                                                    data-photo="<?= htmlspecialchars($photoPath) ?>"
                                                    data-reports="<?= $reportCount ?>">
                                                    <i class="bi bi-eye"></i>
                                                </button>

                                                <button class="btn btn-sm btn-outline-danger" title="Delete"
                                                    data-id="<?= $staff['user_id'] ?>">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>

                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <!-- Pagination -->
                        <nav aria-label="Technicians pagination" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <li class="page-item disabled">
                                    <a class="page-link" href="#" tabindex="-1">Previous</a>
                                </li>
                                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                <li class="page-item"><a class="page-link" href="#">2</a></li>
                                <li class="page-item"><a class="page-link" href="#">3</a></li>
                                <li class="page-item">
                                    <a class="page-link" href="#">Next</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- View Staff Modal -->
    <div class="modal fade" id="viewStaffModal" tabindex="-1" aria-labelledby="viewStaffModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewStaffModalLabel">Staff Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <img id="staffPhoto" src="" class="rounded-circle" style="width: 130px; height: 130px; object-fit: cover;" alt="Staff Photo">
                    </div>
                    <p><strong>Name:</strong> <span id="staffName"></span></p>
                    <p><strong>User ID:</strong> <span id="staffId"></span></p>
                    <p><strong>Email:</strong> <span id="staffEmail"></span></p>
                    <p><strong>Phone:</strong> <span id="staffPhone"></span></p>
                    <p><strong>Total Reports Submitted:</strong> <span id="staffReports"></span></p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.querySelectorAll('.viewBtn').forEach(button => {
            button.addEventListener('click', () => {
                document.getElementById('staffPhoto').src = button.dataset.photo;
                document.getElementById('staffName').textContent = button.dataset.name;
                document.getElementById('staffId').textContent = button.dataset.id;
                document.getElementById('staffEmail').textContent = button.dataset.email;
                document.getElementById('staffPhone').textContent = button.dataset.phone || '-';
                document.getElementById('staffReports').textContent = button.dataset.reports;
            });
        });
    </script>

</body>

</html>