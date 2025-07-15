<?php
session_name("technician_session");
session_start();
require_once '../connection.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['technician_id'])) {
    header("Location: ../login.php");
    exit();
}
$technicianId = $_SESSION['technician_id'];

if (!isset($_GET['report_id'])) {
    echo "No report ID provided.";
    exit();
}

$reportId = $_GET['report_id'];

if (!isset($_SESSION['technician_id'])) {
    header("Location: ../login.php");
    exit();
}

// Get report details + reported by
$stmt = $pdo->prepare("
    SELECT r.*, u.name AS reported_by
    FROM report r
    JOIN User u ON r.user_id = u.user_id
    WHERE r.report_id = ?
");
$stmt->execute([$reportId]);
$report = $stmt->fetch();

if (!$report) {
    echo "Report not found.";
    exit();
}

// Get latest status
$stmt = $pdo->prepare("
    SELECT * FROM statuslog 
    WHERE report_id = ?
    ORDER BY timestamp DESC LIMIT 1
");
$stmt->execute([$reportId]);
$statusLog = $stmt->fetch();

// Get media files
$stmt = $pdo->prepare("SELECT file_path, uploaded_by_role FROM media WHERE report_id = ?");
$stmt->execute([$reportId]);
$mediaFiles = $stmt->fetchAll();
$staffMedia = array_filter($mediaFiles, fn($m) => $m['uploaded_by_role'] === 'staff');
$technicianMedia = array_filter($mediaFiles, fn($m) => $m['uploaded_by_role'] === 'technician');

// Get admin notes
$stmt = $pdo->prepare("
    SELECT changed_by, notes, timestamp 
    FROM statuslog 
    WHERE report_id = ? AND notes IS NOT NULL AND notes != ''
    ORDER BY timestamp DESC LIMIT 1
");
$stmt->execute([$reportId]);
$adminNote = $stmt->fetch();

// Get status history
$stmt = $pdo->prepare("
    SELECT sl.status, sl.notes, sl.timestamp, u.name AS changed_by, u.position AS changer_role
    FROM StatusLog sl
    JOIN User u ON u.user_id = sl.changed_by
    WHERE sl.report_id = ?
    ORDER BY sl.timestamp DESC
");
$stmt->execute([$reportId]);
$statusHistory = $stmt->fetchAll();

// Get timeline data
$stmt = $pdo->prepare("
    SELECT
        MIN(CASE WHEN status = 'open' THEN timestamp END) AS open_time,
        MAX(CASE WHEN status = 'resolved' THEN timestamp END) AS resolved_time
    FROM statuslog
    WHERE report_id = ?
");
$stmt->execute([$reportId]);
$times = $stmt->fetch(PDO::FETCH_ASSOC);

$startTime = !empty($times['open_time']) ? strtotime($times['open_time']) : null;
$endTime = !empty($times['resolved_time']) ? strtotime($times['resolved_time']) : time();

if ($startTime) {
    $durationInSeconds = $endTime - $startTime;

    $days = floor($durationInSeconds / 86400);
    $hours = floor(($durationInSeconds % 86400) / 3600);
    $minutes = floor(($durationInSeconds % 3600) / 60);

    $timeSpent = '';
    if ($days > 0) $timeSpent .= "{$days}d ";
    if ($hours > 0 || $days > 0) $timeSpent .= "{$hours}h ";
    $timeSpent .= "{$minutes}m";
} else {
    $timeSpent = 'N/A';
}

$priority = strtolower($report['priority']);

$textFiles = [];

foreach ($staffMedia as $media) {
    $filename = pathinfo($media['file_path'], PATHINFO_FILENAME);
    $transcriptFile = __DIR__ . "/../backend/uploads/{$filename}_transcript.txt";
    $summaryFile = __DIR__ . "/../backend/uploads/{$filename}_summary.txt";

    if (file_exists($transcriptFile)) {
        $textFiles[] = [
            'type' => 'Transcript',
            'filename' => basename($transcriptFile),
            'content' => file_get_contents($transcriptFile),
            'source' => 'staff'
        ];
    }

    if (file_exists($summaryFile)) {
        $textFiles[] = [
            'type' => 'Summary',
            'filename' => basename($summaryFile),
            'content' => file_get_contents($summaryFile),
            'source' => 'staff'
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assignment Details</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../CSS/styleT.css">
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Technician Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block tech-sidebar p-0">
                <div class="d-flex flex-column p-3">
                    <div class="text-center mb-4">
                        <h4 class="text-white mb-0">
                            <i class="fas fa-tools me-2"></i>FacilityCare
                        </h4>
                        <small class="text-white-50">Technician Panel</small>
                    </div>
                    <hr class="text-white-50">
                    <ul class="nav nav-pills flex-column mb-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboardTech.php">
                                <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="allTasks.php">
                                <i class="fas fa-tasks me-2"></i> My Assignments
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="taskhistory.php">
                                <i class="fas fa-history me-2"></i> Work History
                            </a>
                        </li>
                    </ul>
                    <hr class="text-white-50">
                    <div class="dropdown">
                        <a href="#" class="d-flex justify-content-center align-items-center text-white text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                            <strong> <?php echo isset($_SESSION['name']) ? htmlspecialchars($_SESSION['name']) : 'Technician User'; ?></strong>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark text-small shadow">
                            <li><a class="dropdown-item" href="profileTech.php"><i class="bi bi-person me-2"></i>Profile</a></li>
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
                    <h2 class="mb-0">Assignment Details</h2>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button class="btn btn-sm btn-outline-secondary me-2" onclick="history.back()">
                            <i class="fas fa-arrow-left me-1"></i> Back
                        </button>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-8">
                        <div class="card mb-4">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-4">
                                    <div>
                                        <h4 class="mb-1"><?= htmlspecialchars($report['title']) ?></h4>
                                        <div class="d-flex align-items-center mt-2">
                                            <span class="badge bg-primary me-2"><?= htmlspecialchars($report['category']) ?></span>
                                            <span class="badge bg-<?= $report['priority'] == 'high' ? 'danger' : ($report['priority'] == 'medium' ? 'warning' : 'success') ?>">
                                                <?= ucfirst($report['priority']) ?> Priority
                                            </span>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <small class="text-muted">Report ID: #<?= $report['report_id'] ?></small><br>
                                        <small class="text-muted">Assigned: <?= date('F j, Y, g:i A', strtotime($statusLog['timestamp'])) ?></small>
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <h6 class="mb-2"><i class="fas fa-map-marker-alt me-2"></i> Location</h6>
                                        <p><?= htmlspecialchars($report['facilities']) ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="mb-2"><i class="fas fa-user me-2"></i> Reported By</h6>
                                        <p><?= htmlspecialchars($report['reported_by']) ?></p>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <h6 class="mb-2"><i class="fas fa-align-left me-2"></i> Description</h6>
                                    <p><?= nl2br(htmlspecialchars($report['description'])) ?></p>
                                </div>

                                <div class="mb-4">
                                    <h6 class="mb-3"><i class="fas fa-images me-2"></i> Media Evidence</h6>

                                    <!-- MEDIA BY STAFF -->
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

                                    <!-- MEDIA BY TECHNICIAN -->
                                    <h6 class="mb-2">Media Uploaded by Technician</h6>
                                    <?php if (empty($technicianMedia)): ?>
                                        <p class="text-muted">No media uploaded by technician.</p>
                                    <?php else: ?>
                                        <div class="d-flex flex-wrap gap-2">
                                            <?php foreach ($technicianMedia as $media): ?>
                                                <?php
                                                $relativePath = "../backend/" . htmlspecialchars($media['file_path']);
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
                                </div>

                                <!-- Add this new section for text files -->
                                <div class="mt-4">
                                    <h6 class="mb-3"><i class="fas fa-file-alt me-2"></i> Generated Text Files from Staff's Uploaded Media</h6>

                                    <?php if (empty($textFiles)): ?>
                                        <p class="text-muted">No text files available.</p>
                                    <?php else: ?>
                                        <?php foreach ($textFiles as $file): ?>
                                            <div class="card mb-3">
                                                <div class="card-header d-flex justify-content-between">
                                                    <span><?= htmlspecialchars($file['type']) ?></span>
                                                    <small class="text-muted">From staff media</small>
                                                </div>
                                                <div class="card-body">
                                                    <pre class="mb-0" style="white-space: pre-wrap;"><?= htmlspecialchars($file['content']) ?></pre>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>

                                <?php if ($adminNote): ?>
                                    <div class="mb-4">
                                        <h6 class="mb-3"><i class="fas fa-comment me-2"></i>Notes</h6>
                                        <div class="card bg-light mb-3">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between mb-2">
                                                    <strong><?= htmlspecialchars($adminNote['changed_by']) ?></strong>
                                                    <small class="text-muted"><?= date('F j, Y, g:i A', strtotime($adminNote['timestamp'])) ?></small>
                                                </div>
                                                <p class="mb-0"><?= nl2br(htmlspecialchars($adminNote['notes'])) ?></p>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title mb-4"><i class="fas fa-clock me-2"></i> Status History</h5>
                                <div class="status-timeline">
                                    <?php if (empty($statusHistory)): ?>
                                        <p class="text-muted">No status history available.</p>
                                    <?php else: ?>
                                        <?php foreach ($statusHistory as $log): ?>
                                            <div class="timeline-item mb-3">
                                                <div class="d-flex justify-content-between">
                                                    <strong>
                                                        <?php
                                                        if ($log['status'] === 'assigned') {
                                                            echo 'Assigned to you';
                                                        } elseif ($log['status'] === 'in_progress') {
                                                            echo 'Status changed to "In Progress"';
                                                        } elseif ($log['status'] === 'completed') {
                                                            echo 'Marked as Completed';
                                                        } else {
                                                            echo 'Status changed to "' . ucfirst($log['status']) . '"';
                                                        }
                                                        ?>
                                                    </strong>
                                                    <small class="text-muted"><?= date('F j, Y, g:i A', strtotime($log['timestamp'])) ?></small>
                                                </div>
                                                <?php if (!empty($log['notes'])): ?>
                                                    <p class="mb-0 text-muted"><?= htmlspecialchars($log['notes']) ?></p>
                                                <?php endif; ?>
                                                <small class="text-muted">Updated by: <?= htmlspecialchars($log['changed_by']) ?></small>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="col-lg-4">
                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title mb-3"><i class="fas fa-tasks me-2"></i> Task Actions</h5>

                                <form action="../backend/updateTask.php" method="POST" enctype="multipart/form-data">
                                    <input type="hidden" name="report_id" value="<?= $report['report_id'] ?>">

                                    <div class="alert alert-warning mb-4">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-hourglass-half me-2" style="font-size: 1.5rem;"></i>
                                            <div>
                                                <h6 class="alert-heading mb-1"><?= ucwords(str_replace('_', ' ', strtolower($statusLog['status']))) ?></h6>
                                                <p class="mb-0">Started: <?= date('F j, Y, g:i A', strtotime($statusLog['timestamp'])) ?></p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label">Update Status</label>
                                        <select class="form-select mb-3" name="status">
                                            <option value="in_progress" <?= $statusLog['status'] === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                                            <option value="resolved" <?= $statusLog['status'] === 'resolved' ? 'selected' : '' ?>>Resolved</option>
                                        </select>

                                        <label class="form-label">Add Note</label>
                                        <textarea class="form-control mb-3" name="notes" rows="3" placeholder="Describe your progress or any issues..."></textarea>

                                        <label class="form-label">Add Photos</label>
                                        <input type="file" class="form-control mb-3" name="media[]" multiple accept="image/*">

                                        <div class="d-grid gap-2">
                                            <button type="submit" name="action" value="save" class="btn btn-primary action-btn">
                                                <i class="fas fa-save me-1"></i> Save Update
                                            </button>
                                            <button type="submit" name="action" value="complete" class="btn btn-success action-btn">
                                                <i class="fas fa-check-circle me-1"></i> Mark Resolved
                                            </button>
                                            <button type="submit" name="action" value="cancel" class="btn btn-danger action-btn">
                                                <i class="fas fa-times-circle me-1"></i> Cancel Task
                                            </button>
                                        </div>
                                    </div>
                                </form>

                                <hr>

                                <div>
                                    <h6 class="mb-3"><i class="fas fa-info-circle me-2"></i> Task Details</h6>
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item px-0 d-flex justify-content-between">
                                            <span>Priority:</span>
                                            <span class="text-<?= $priority === 'high' ? 'danger' : ($priority === 'medium' ? 'warning' : 'success') ?>">
                                                <?= ucfirst($priority) ?>
                                            </span>
                                        </li>
                                        <li class="list-group-item px-0 d-flex justify-content-between">
                                            <span>Time Spent:</span>
                                            <span><?= $timeSpent ?></span>
                                        </li>
                                        <li class="list-group-item px-0 d-flex justify-content-between">
                                            <span>Created:</span>
                                            <span>
                                                <?= !empty($times['open_time']) ? date('j M Y, g:i A', strtotime($times['open_time'])) : 'N/A' ?>
                                            </span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mb-3"><i class="fas fa-toolbox me-2"></i> Required Tools</h5>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item px-0 d-flex justify-content-between">
                                    <span>Pipe Wrench</span>
                                    <span class="badge bg-success">Available</span>
                                </li>
                                <li class="list-group-item px-0 d-flex justify-content-between">
                                    <span>Teflon Tape</span>
                                    <span class="badge bg-success">Available</span>
                                </li>
                                <li class="list-group-item px-0 d-flex justify-content-between">
                                    <span>Replacement Washers</span>
                                    <span class="badge bg-warning">Low Stock</span>
                                </li>
                            </ul>
                        </div>
                    </div> -->
                    </div>
                </div>
            </main>
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