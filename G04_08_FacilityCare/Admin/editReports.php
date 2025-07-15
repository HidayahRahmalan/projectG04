<?php
session_name("admin_session");
session_start();
require_once '../connection.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$reportId = $_GET['id'] ?? null;

if (!$reportId) {
    die("Report ID not provided.");
}

// Fetch report data
$reportId = $_GET['id'];
$stmt = $pdo->prepare("
    SELECT 
        r.*,
        u.name AS reporter_name,
        u.email AS reporter_email,
        u.position AS department,
        t.technician_id,
        t.profile_photo,
        t.phone_number,
        -- Ambil nama technician dari user table kalau technician_id tak NULL
        (SELECT name FROM user WHERE user.user_id = r.technician_id) AS technician_name,
        -- Dapatkan latest status dan nota
        (
            SELECT status FROM statuslog s1
            WHERE s1.report_id = r.report_id
            ORDER BY s1.timestamp DESC
            LIMIT 1
        ) AS latest_status,
        (
            SELECT notes FROM statuslog s1
            WHERE s1.report_id = r.report_id
            ORDER BY s1.timestamp DESC
            LIMIT 1
        ) AS note,
        (
            SELECT timestamp FROM statuslog s1
            WHERE s1.report_id = r.report_id
            ORDER BY s1.timestamp DESC
            LIMIT 1
        ) AS timestamp
    FROM report r
    LEFT JOIN user u ON u.user_id = r.user_id
    LEFT JOIN technician t ON t.technician_id = r.technician_id
    WHERE r.report_id = ?
");
$stmt->execute([$reportId]);
$report = $stmt->fetch();

if (!$report) {
    // If no report found, redirect to all reports page
    header("Location: allReports.php");
    exit();
}

// Ambil semua media berkaitan report
$mediaStmt = $pdo->prepare("SELECT * FROM media WHERE report_id = ?");
$mediaStmt->execute([$report['report_id']]);
$mediaFiles = $mediaStmt->fetchAll();

// Fetch report history log
$logStmt = $pdo->prepare("
    SELECT s.status, s.notes, s.timestamp, u.name AS changed_by_name, u.role
    FROM statuslog s
    LEFT JOIN user u ON s.changed_by = u.user_id
    WHERE s.report_id = ?
    ORDER BY s.timestamp DESC
");
$logStmt->execute([$reportId]);
$statusLogs = $logStmt->fetchAll();

// Normalize latest_status from earlier fetch
if (!empty($report['latest_status'])) {
    $report['latest_status'] = strtolower($report['latest_status']);
} else {
    $report['latest_status'] = 'open';
}

$textFiles = [];

foreach ($mediaFiles as $media) {
    $filename = pathinfo($media['file_path'], PATHINFO_FILENAME);

    $transcriptFile = __DIR__ . "/../backend/uploads/{$filename}_transcript.txt";
    $summaryFile = __DIR__ . "/../backend/uploads/{$filename}_summary.txt";

    if (file_exists($transcriptFile)) {
        $textFiles[] = [
            'type' => 'Transcript',
            'filename' => basename($transcriptFile),
            'content' => file_get_contents($transcriptFile)
        ];
    }

    if (file_exists($summaryFile)) {
        $textFiles[] = [
            'type' => 'Summary',
            'filename' => basename($summaryFile),
            'content' => file_get_contents($summaryFile)
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Report - Admin Panel</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../CSS/styleAdmin.css">
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
                    <h1 class="h2">Edit Maintenance Report</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button class="btn btn-sm btn-outline-secondary me-2">
                            <i class="bi bi-arrow-left me-1"></i> Back
                        </button>
                        <button class="btn btn-sm btn-danger me-2">
                            <i class="bi bi-trash me-1"></i> Delete
                        </button>
                        <button class="btn btn-sm btn-primary">
                            <i class="bi bi-save me-1"></i> Save Changes
                        </button>
                    </div>
                </div>

                <div class="row">
                    <!-- Report Details Column -->
                    <div class="col-lg-8">
                        <div class="card admin-card mb-4">
                            <div class="card-body">
                                <h5 class="card-title mb-4">Report Details</h5>

                                <form action="processEditReport.php" method="POST">
                                    <input type="hidden" name="report_id" value="<?= $report['report_id'] ?>">

                                    <div class="row mb-3">
                                        <div class="col-md-8">
                                            <label for="reportTitle" class="form-label">Title</label>
                                            <input type="text" class="form-control" id="reportTitle" name="title"
                                                value="<?= htmlspecialchars($report['title']) ?>" readonly>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="reportId" class="form-label">Report ID</label>
                                            <input type="text" class="form-control" id="reportId" value="#<?= $report['report_id'] ?>" disabled>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="reportCategory" class="form-label">Category</label>
                                            <select class="form-select" id="reportCategory" name="category">
                                                <?php
                                                $categories = ['Plumbing', 'Electrical', 'HVAC', 'Structural', 'Cleaning', 'Other'];
                                                foreach ($categories as $cat) {
                                                    $selected = $report['category'] === $cat ? 'selected' : '';
                                                    echo "<option value=\"$cat\" $selected>$cat</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="reportUrgency" class="form-label">Priority</label>
                                            <select class="form-select" id="reportUrgency" name="priority">
                                                <?php
                                                $priorities = [
                                                    'low' => 'Low - Routine maintenance',
                                                    'medium' => 'Medium - Needs attention soon',
                                                    'high' => 'High - Immediate attention required'
                                                ];
                                                foreach ($priorities as $key => $label) {
                                                    $selected = $report['priority'] === $key ? 'selected' : '';
                                                    echo "<option value=\"$key\" $selected>$label</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="reportLocation" class="form-label">Location</label>
                                        <input type="text" class="form-control" id="reportLocation" name="facilities"
                                            value="<?= htmlspecialchars($report['facilities']) ?>">
                                    </div>

                                    <div class="mb-3">
                                        <label for="reportDescription" class="form-label">Description</label>
                                        <textarea class="form-control" id="reportDescription" name="description"
                                            rows="5"><?= htmlspecialchars($report['description']) ?></textarea>
                                    </div>

                                    <?php
                                    $staffMedia = [];
                                    $technicianMedia = [];

                                    foreach ($mediaFiles as $file) {
                                        if ($file['uploaded_by_role'] === 'staff') {
                                            $staffMedia[] = $file;
                                        } elseif ($file['uploaded_by_role'] === 'technician') {
                                            $technicianMedia[] = $file;
                                        }
                                    }
                                    ?>
                                    <div class="mb-4">
                                        <h6 class="mb-3"><i class="bi bi-images me-2"></i> Media Evidence</h6>

                                        <!-- STAFF MEDIA -->
                                        <h6 class="mb-2">Media Uploaded by Staff</h6>
                                        <?php if (empty($staffMedia)): ?>
                                            <p class="text-muted">No media uploaded by staff.</p>
                                        <?php else: ?>
                                            <div class="d-flex flex-wrap gap-2 mb-4">
                                                <?php foreach ($staffMedia as $media): ?>
                                                    <?php
                                                    $relativePath = htmlspecialchars("../backend/" . $media['file_path']);
                                                    $ext = pathinfo($media['file_path'], PATHINFO_EXTENSION);
                                                    $mediaType = in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif']) ? 'image' : 'video';
                                                    ?>
                                                    <div style="width: 140px; height: 140px; cursor: pointer; position: relative;">
                                                        <?php if ($mediaType === 'image'): ?>
                                                            <img src="<?= $relativePath ?>" class="img-thumbnail object-fit-cover w-100 h-100"
                                                                onclick="previewMedia('image', '<?= $relativePath ?>')">
                                                        <?php else: ?>
                                                            <video muted class="img-thumbnail object-fit-cover w-100 h-100"
                                                                onclick="previewMedia('video', '<?= $relativePath ?>')">
                                                                <source src="<?= $relativePath ?>" type="video/mp4">
                                                            </video>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>

                                        <!-- TECHNICIAN MEDIA -->
                                        <h6 class="mb-2">Media Uploaded by Technician</h6>
                                        <?php if (empty($technicianMedia)): ?>
                                            <p class="text-muted">No media uploaded by technician.</p>
                                        <?php else: ?>
                                            <div class="d-flex flex-wrap gap-2">
                                                <?php foreach ($technicianMedia as $media): ?>
                                                    <?php
                                                    $relativePath = htmlspecialchars("../backend/" . $media['file_path']);
                                                    $ext = pathinfo($media['file_path'], PATHINFO_EXTENSION);
                                                    $mediaType = in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif']) ? 'image' : 'video';
                                                    ?>
                                                    <div style="width: 140px; height: 140px; cursor: pointer; position: relative;">
                                                        <?php if ($mediaType === 'image'): ?>
                                                            <img src="<?= $relativePath ?>" class="img-thumbnail object-fit-cover w-100 h-100"
                                                                onclick="previewMedia('image', '<?= $relativePath ?>')">
                                                        <?php else: ?>
                                                            <video muted class="img-thumbnail object-fit-cover w-100 h-100"
                                                                onclick="previewMedia('video', '<?= $relativePath ?>')">
                                                                <source src="<?= $relativePath ?>" type="video/mp4">
                                                            </video>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="mt-4">
                                            <h6 class="mb-3"><i class="bi bi-file-earmark-text me-2"></i> Generated Text Files from Staff's Uploaded Media (Transcripts / Summaries)</h6>

                                            <?php if (empty($textFiles)): ?>
                                                <p class="text-muted">No text files available.</p>
                                            <?php else: ?>
                                                <?php foreach ($textFiles as $file): ?>
                                                    <div class="card mb-3">
                                                        <div class="card-header"><?= htmlspecialchars($file['type']) ?></div>
                                                        <div class="card-body">
                                                            <pre class="mb-0" style="white-space: pre-wrap;"><?= htmlspecialchars($file['content']) ?></pre>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>

                                    </div>

                                    <!-- <button class="btn btn-primary">Save Changes</button> -->
                                </form>
                            </div>
                        </div>

                        <!-- Update History -->
                        <div class="card admin-card">
                            <div class="card-body">
                                <h5 class="card-title mb-4">Update History</h5>

                                <?php foreach ($statusLogs as $log): ?>
                                    <div class="history-item">
                                        <div class="d-flex justify-content-between mb-2">
                                            <strong>Status changed to "<?= htmlspecialchars(ucwords(str_replace('_', ' ', $log['status']))) ?>"</strong>
                                            <small class="text-muted"><?= date('d M Y, h:i A', strtotime($log['timestamp'])) ?></small>
                                        </div>
                                        <p class="mb-1">Updated by: <?= htmlspecialchars($log['changed_by_name']) ?> (<?= $log['role'] ?>)</p>
                                        <?php if (!empty($log['notes'])): ?>
                                            <p class="mb-0 text-muted">Admin note: <?= nl2br(htmlspecialchars($log['notes'])) ?></p>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Status & Actions Column -->
                    <div class="col-lg-4">
                        <div class="card admin-card mb-4">
                            <div class="card-body">
                                <h5 class="card-title mb-3">Report Status</h5>
                                <form method="POST" action="../backend/process_update_status.php">
                                    <input type="hidden" name="report_id" value="<?= $report['report_id'] ?>">

                                    <div class="mb-4">
                                        <label class="form-label">Current Status</label>
                                        <select class="form-select mb-3" name="status" required>
                                            <option value="open" <?= ($report['latest_status'] === 'open') ? 'selected' : '' ?>>Open</option>
                                            <option value="in_progress" <?= ($report['latest_status'] === 'in_progress') ? 'selected' : '' ?>>In Progress</option>
                                            <option value="resolved" <?= ($report['latest_status'] === 'resolved') ? 'selected' : '' ?>>Resolved</option>
                                        </select>

                                        <div class="mb-3">
                                            <label class="form-label">Assigned To</label>
                                            <select class="form-select" name="technician_id" required>
                                                <option value="">-- Select Technician --</option>
                                                <?php
                                                // Dapatkan speciality_id dari nama category report
                                                $specStmt = $pdo->prepare("SELECT speciality_id FROM speciality WHERE LOWER(speciality_name) = LOWER(?)");
                                                $specStmt->execute([$report['category']]);
                                                $speciality = $specStmt->fetch();
                                                $specialityId = $speciality['speciality_id'] ?? null;

                                                // Fetch technician ikut speciality
                                                if ($specialityId) {
                                                    $techStmt = $pdo->prepare("
                                                        SELECT u.user_id, u.name, u.role 
                                                        FROM user u
                                                        JOIN technician_speciality ts ON ts.technician_id = u.user_id
                                                        JOIN technician t ON t.technician_id = u.user_id
                                                        WHERE u.position = 'technician'
                                                        AND ts.speciality_id = ?
                                                        AND t.technician_status = 'active'
                                                    ");
                                                    $techStmt->execute([$specialityId]);

                                                    while ($tech = $techStmt->fetch()):
                                                ?>
                                                        <option value="<?= $tech['user_id'] ?>" <?= ($report['technician_id'] === $tech['user_id']) ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($tech['name']) ?> (<?= $tech['role'] ?>)
                                                        </option>
                                                <?php
                                                    endwhile;
                                                }
                                                ?>
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label for="statusNote" class="form-label">Latest Note</label>
                                            <textarea class="form-control" name="notes" id="statusNote" rows="3"><?= $report['note'] ?? '' ?></textarea>
                                        </div>

                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="bi bi-save me-1"></i> Update Status
                                        </button>
                                    </div>
                                </form>


                                <hr>

                                <div class="mb-4">
                                    <h6 class="mb-3">Quick Actions</h6>
                                    <div class="d-grid gap-2">
                                        <button class="btn btn-success">
                                            <i class="bi bi-check-circle me-1"></i> Mark as Resolved
                                        </button>
                                        <button class="btn btn-outline-secondary">
                                            <i class="bi bi-archive me-1"></i> Archive Report
                                        </button>
                                    </div>
                                </div>

                                <hr>

                                <div>
                                    <h6 class="mb-3">Report Information</h6>
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item px-0 d-flex justify-content-between">
                                            <span>Reported By:</span>
                                            <span><?= $report['reporter_name'] ?></span>
                                        </li>
                                        <li class="list-group-item px-0 d-flex justify-content-between">
                                            <span>Role:</span>
                                            <span><?= $report['department'] ?? 'N/A' ?></span>
                                        </li>
                                        <li class="list-group-item px-0 d-flex justify-content-between">
                                            <span>Contact:</span>
                                            <span><?= $report['reporter_email'] ?></span>
                                        </li>
                                        <li class="list-group-item px-0 d-flex justify-content-between">
                                            <span>Created:</span>
                                            <span><?= date('d M Y, h:i A', strtotime($report['created_at'])) ?></span>
                                        </li>
                                        <li class="list-group-item px-0 d-flex justify-content-between">
                                            <span>Last Updated:</span>
                                            <span><?= date('d M Y, h:i A', strtotime($report['timestamp'] ?? $report['created_at'])) ?></span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </main>
        </div>
    </div>

    <!-- Media Modal -->
    <div class="modal fade" id="mediaModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Preview</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body d-flex justify-content-center align-items-center" style="min-height: 300px;">
                    <img id="modalMediaContent"
                        style="max-width: 100%; max-height: 100%; object-fit: contain; display: none;" />

                    <video id="modalVideoContent"
                        controls muted
                        style="max-width: 100%; max-height: 100%; object-fit: contain; display: none;">
                        <source type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                </div>
                <div class="modal-footer">
                    <a id="downloadBtn" href="#" class="btn btn-primary" download>Download</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Media upload functionality
        document.getElementById('mediaUploadArea').addEventListener('click', function() {
            document.getElementById('mediaUploadInput').click();
        });

        // Drag and drop functionality
        const mediaUploadArea = document.getElementById('mediaUploadArea');

        mediaUploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            mediaUploadArea.style.borderColor = '#85A1EF';
            mediaUploadArea.style.backgroundColor = 'rgba(133, 161, 239, 0.1)';
        });

        mediaUploadArea.addEventListener('dragleave', () => {
            mediaUploadArea.style.borderColor = '#dee2e6';
            mediaUploadArea.style.backgroundColor = '';
        });

        mediaUploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            mediaUploadArea.style.borderColor = '#dee2e6';
            mediaUploadArea.style.backgroundColor = '';
            // Handle dropped files here
            console.log('Files dropped:', e.dataTransfer.files);
        });
    </script>

    <script>
        const mediaModal = document.getElementById('mediaModal');

        if (mediaModal) {
            mediaModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const mediaUrl = button.getAttribute('data-media');
                const isVideo = button.classList.contains('video');

                const imgElement = document.getElementById('modalMediaContent');
                const videoElement = document.getElementById('modalVideoContent');

                imgElement.style.display = isVideo ? 'none' : 'block';
                videoElement.style.display = isVideo ? 'block' : 'none';

                if (isVideo) {
                    videoElement.src = mediaUrl;
                    videoElement.load(); // make sure it's refreshed
                } else {
                    imgElement.src = mediaUrl;
                }

                const downloadBtn = document.getElementById('downloadBtn');
                downloadBtn.href = mediaUrl;
            });

            mediaModal.addEventListener('hidden.bs.modal', function() {
                const videoElement = document.getElementById('modalVideoContent');
                videoElement.pause();
                videoElement.src = '';
            });
        }

        function previewMedia(type, src) {
            const modal = new bootstrap.Modal(document.getElementById('mediaModal'));
            modal.show();

            setTimeout(() => {
                const img = document.getElementById('modalMediaContent');
                const video = document.getElementById('modalVideoContent');
                const downloadBtn = document.getElementById('downloadBtn');

                img.style.display = type === 'image' ? 'block' : 'none';
                video.style.display = type === 'video' ? 'block' : 'none';

                if (type === 'image') {
                    img.src = src;
                } else {
                    video.src = src;
                    video.load();
                }

                downloadBtn.href = src;
            }, 200);
        }
    </script>

</body>

</html>