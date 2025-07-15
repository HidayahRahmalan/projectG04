<?php
session_name("admin_session");
session_start();
require_once '../connection.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Fetch specialties from DB
$specialties = $pdo->query("SELECT speciality_id, speciality_name FROM speciality")->fetchAll(PDO::FETCH_ASSOC);

function getTechnicianSpecialties($pdo, $technicianId)
{
    $stmt = $pdo->prepare("
        SELECT s.speciality_name 
        FROM technician_speciality ts 
        JOIN speciality s ON ts.speciality_id = s.speciality_id 
        WHERE ts.technician_id = ?
    ");
    $stmt->execute([$technicianId]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function getInProgressJobCount($pdo, $technicianId)
{
    $stmt = $pdo->prepare("
        SELECT COUNT(*) AS job_count
        FROM report r
        JOIN (
            SELECT s1.report_id, s1.status
            FROM statuslog s1
            INNER JOIN (
                SELECT report_id, MAX(timestamp) AS max_time
                FROM statuslog
                GROUP BY report_id
            ) s2 ON s1.report_id = s2.report_id AND s1.timestamp = s2.max_time
            WHERE s1.status = 'in_progress'
        ) latest_status ON latest_status.report_id = r.report_id
        WHERE r.technician_id = ? AND r.archive = 0
    ");
    $stmt->execute([$technicianId]);
    return $stmt->fetchColumn();
}

$technicians = $pdo->query("
    SELECT 
        u.user_id,
        u.name,
        u.email,
        t.phone_number,
        t.technician_status,
        t.profile_photo
    FROM user u
    JOIN technician t ON u.user_id = t.technician_id
")->fetchAll(PDO::FETCH_ASSOC);

$statusFilter = $_GET['status'] ?? '';
$specialtyFilter = $_GET['specialty'] ?? '';
$searchQuery = $_GET['search'] ?? '';

$sql = "
    SELECT 
        u.user_id,
        u.name,
        u.email,
        t.phone_number,
        t.technician_status,
        t.profile_photo
    FROM user u
    JOIN technician t ON u.user_id = t.technician_id
";

$conditions = [];
$params = [];

if ($statusFilter !== '') {
    $conditions[] = "t.technician_status = :status";
    $params[':status'] = $statusFilter;
}

if ($searchQuery !== '') {
    $conditions[] = "(u.name LIKE :search OR u.user_id LIKE :search OR u.email LIKE :search)";
    $params[':search'] = '%' . $searchQuery . '%';
}

if (!empty($conditions)) {
    $sql .= ' WHERE ' . implode(' AND ', $conditions);
}

$technicianStmt = $pdo->prepare($sql);
$technicianStmt->execute($params);
$technicians = $technicianStmt->fetchAll(PDO::FETCH_ASSOC);

// Later filter specialty (since it's in another table)
if ($specialtyFilter !== '') {
    $technicians = array_filter($technicians, function ($tech) use ($pdo, $specialtyFilter) {
        $specialties = getTechnicianSpecialties($pdo, $tech['user_id']);
        return in_array($specialtyFilter, $specialties);
    });
}
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
                    <h1 class="h2">Technicians Management</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <!-- <button class="btn btn-sm btn-outline-secondary me-2" data-bs-toggle="modal" data-bs-target="#exportModal">
                            <i class="bi bi-download me-1"></i> Export
                        </button> -->
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addTechModal">
                            <i class="bi bi-plus-lg me-1"></i> Add Technician
                        </button>
                    </div>
                </div>

                <!-- Add Technician Modal -->
                <form action="../backend/add_technician.php" method="POST" enctype="multipart/form-data">
                    <div class="modal fade" id="addTechModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content shadow border-0">
                                <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title">Add New Technician</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>

                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Full Name*</label>
                                            <input type="text" class="form-control" name="name" required>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Email*</label>
                                            <input type="email" class="form-control" name="email" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Phone*</label>
                                            <input type="tel" class="form-control" name="phone" required>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Specialties*</label>
                                        <select class="form-select" name="specialties[]" multiple required>
                                            <?php foreach ($specialties as $spec): ?>
                                                <option value="<?= $spec['speciality_id'] ?>"><?= htmlspecialchars($spec['speciality_name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>

                                        <small class="text-muted">Hold Ctrl/Cmd to select multiple</small>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Status*</label>
                                            <select class="form-select" name="technician_status" required>
                                                <option value="active">Active</option>
                                                <option value="inactive">Inactive</option>
                                            </select>

                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Profile Photo</label>
                                            <input type="file" class="form-control" name="profile_photo" accept="image/*">
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary">Add Technician</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Filters Card -->
                <div class="card filter-card mb-4">
                    <div class="card-body">
                        <form class="row g-3" method="GET" action="">
                            <div class="col-md-4">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status">
                                    <option value="" <?= (!isset($_GET['status']) || $_GET['status'] === '') ? 'selected' : '' ?>>All Statuses</option>
                                    <option value="active" <?= (isset($_GET['status']) && $_GET['status'] === 'active') ? 'selected' : '' ?>>Active</option>
                                    <option value="inactive" <?= (isset($_GET['status']) && $_GET['status'] === 'inactive') ? 'selected' : '' ?>>Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Specialty</label>
                                <select class="form-select" name="specialty">
                                    <option value="" <?= (!isset($_GET['specialty']) || $_GET['specialty'] === '') ? 'selected' : '' ?>>All Specialties</option>
                                    <option value="Plumbing" <?= (isset($_GET['specialty']) && $_GET['specialty'] === 'Plumbing') ? 'selected' : '' ?>>Plumbing</option>
                                    <option value="Electrical" <?= (isset($_GET['specialty']) && $_GET['specialty'] === 'Electrical') ? 'selected' : '' ?>>Electrical</option>
                                    <option value="HVAC" <?= (isset($_GET['specialty']) && $_GET['specialty'] === 'HVAC') ? 'selected' : '' ?>>HVAC</option>
                                    <option value="Structural" <?= (isset($_GET['specialty']) && $_GET['specialty'] === 'Structural') ? 'selected' : '' ?>>Structural</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Search</label>
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control" placeholder="Search technicians..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
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
                                        <th>Technician</th>
                                        <th>Contact</th>
                                        <th>Specialties</th>
                                        <th>Status</th>
                                        <th>Assigned Jobs</th>
                                        <th></th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($technicians as $index => $tech):
                                        $specialties = getTechnicianSpecialties($pdo, $tech['user_id']);
                                        $assignedJobs = getInProgressJobCount($pdo, $tech['user_id']);
                                        echo "<!-- Technician: {$tech['user_id']}, In-Progress Jobs: {$assignedJobs} -->";

                                        $rating = 4.5; // Placeholder
                                        $maxJobs = 8;
                                        $jobPercent = $maxJobs > 0 ? ($assignedJobs / $maxJobs) * 100 : 0;
                                        $progressBarClass = $assignedJobs == 0 ? 'bg-secondary' : ($jobPercent < 50 ? 'bg-success' : 'bg-warning');
                                        $statusClass = strtolower($tech['technician_status']) === 'active' ? 'status-active' : 'status-inactive';
                                        $photoPath = !empty($tech['profile_photo']) ? '../' . $tech['profile_photo'] : '../images/tech_profile/default-avatar.png';
                                    ?>
                                        <tr>
                                            <td>#<?= 1 + $index ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="<?= htmlspecialchars($photoPath) ?>" class="tech-avatar me-2" alt="Avatar" style="width:40px;height:40px;border-radius:50%;">
                                                    <div>
                                                        <h6 class="mb-0"><?= htmlspecialchars($tech['name']) ?></h6>
                                                        <small class="text-muted"><?= htmlspecialchars($tech['user_id']) ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <small class="text-muted">Email:</small>
                                                    <p class="mb-0"><?= htmlspecialchars($tech['email']) ?></p>
                                                </div>
                                                <div class="mt-1">
                                                    <small class="text-muted">Phone:</small>
                                                    <p class="mb-0"><?= htmlspecialchars($tech['phone_number']) ?></p>
                                                </div>
                                            </td>
                                            <td>
                                                <?php foreach ($specialties as $spec): ?>
                                                    <span class="badge bg-primary specialty-badge"><?= htmlspecialchars($spec) ?></span>
                                                <?php endforeach; ?>
                                            </td>
                                            <td>
                                                <span class="badge <?= $statusClass ?>"><?= htmlspecialchars($tech['technician_status']) ?></span>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <small class="text-muted mb-1">Jobs: <?= $assignedJobs ?>/<?= $maxJobs ?></small>
                                                    <div class="progress" style="height: 20px;">
                                                        <div class="progress-bar <?= $progressBarClass ?>" role="progressbar"
                                                            style="width: <?= min($jobPercent, 100) ?>%;"
                                                            aria-valuenow="<?= $assignedJobs ?>" aria-valuemin="0" aria-valuemax="<?= $maxJobs ?>">
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>

                                            <td>
                                                <!-- <div class="rating-star">
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <i class="fas fa-star<?= $i <= floor($rating) ? '' : ($i - $rating < 1 ? '-half-alt' : ' far') ?>"></i>
                                                    <?php endfor; ?>
                                                    <span class="ms-1"><?= number_format($rating, 1) ?></span>
                                                </div> -->
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary me-1" title="View" data-bs-toggle="modal" data-bs-target="#viewTechModal">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-warning me-1"
                                                    title="Edit"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editTechModal"
                                                    data-id="<?= $tech['user_id'] ?>"
                                                    data-name="<?= htmlspecialchars($tech['name']) ?>"
                                                    data-email="<?= htmlspecialchars($tech['email']) ?>"
                                                    data-phone="<?= htmlspecialchars($tech['phone_number']) ?>"
                                                    data-status="<?= $tech['technician_status'] ?>"
                                                    data-photo="<?= htmlspecialchars($photoPath) ?>">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger" title="Delete">
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

    <form action="../backend/edit_technician.php" method="POST" enctype="multipart/form-data">
        <div class="modal fade" id="editTechModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content shadow border-0">
                    <div class="modal-header bg-warning text-white">
                        <h5 class="modal-title">Edit Technician</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <!-- Hidden ID -->
                        <input type="hidden" name="technician_id" id="edit-tech-id">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Full Name*</label>
                                <input type="text" class="form-control" name="name" id="edit-tech-name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email*</label>
                                <input type="email" class="form-control" name="email" id="edit-tech-email" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone*</label>
                                <input type="tel" class="form-control" name="phone" id="edit-tech-phone" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status*</label>
                                <select class="form-select" name="technician_status" id="edit-tech-status" required>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Profile Photo</label>
                            <img src="" id="tech-profile-preview" class="img-thumbnail mb-2" style="max-width: 150px;">
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">Save Changes</button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Export Modal -->
    <div class="modal fade" id="exportModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Export Technicians Data</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Format</label>
                        <select class="form-select">
                            <option>CSV (Excel)</option>
                            <option>PDF</option>
                            <option>Print</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Columns to Include</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="colName" checked>
                            <label class="form-check-label" for="colName">Name</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="colContact" checked>
                            <label class="form-check-label" for="colContact">Contact Info</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="colSpecialty" checked>
                            <label class="form-check-label" for="colSpecialty">Specialties</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="colStatus" checked>
                            <label class="form-check-label" for="colStatus">Status</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary">Export</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'))
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    </script>

    <script>
        const editModal = document.getElementById('editTechModal');
        editModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;

            // Ambil data dari data-*
            const id = button.getAttribute('data-id');
            const name = button.getAttribute('data-name');
            const email = button.getAttribute('data-email');
            const phone = button.getAttribute('data-phone');
            const status = button.getAttribute('data-status');
            const photo = button.getAttribute('data-photo');

            // Isi ke dalam modal
            document.getElementById('edit-tech-id').value = id;
            document.getElementById('edit-tech-name').value = name;
            document.getElementById('edit-tech-email').value = email;
            document.getElementById('edit-tech-phone').value = phone;
            document.getElementById('edit-tech-status').value = status;
            document.getElementById('current-photo-name').textContent = photo ?? 'None';
        });

        document.querySelectorAll('[data-bs-target="#editTechModal"]').forEach(button => {
            button.addEventListener('click', function() {
                const photo = this.getAttribute('data-photo');
                document.getElementById('tech-profile-preview').src = photo;
                // Jangan ubah value input file sebab dia disabled / tiada pun
            });
        });
    </script>

</body>

</html>