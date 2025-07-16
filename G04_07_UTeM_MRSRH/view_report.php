<?php
$report_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($report_id <= 0) { die("Invalid Report ID specified."); }

$page_title = "View Report #{$report_id}";
$css_files = ['view_report.css'];
include 'templates/header.php';

// Fetch main report details
$report_sql = "SELECT r.*, l.House_Name, l.Address, creator.Name as creator_name, assignee.Name as assignee_name 
               FROM report r 
               JOIN locations l ON r.Location = l.Location_ID 
               JOIN users creator ON r.User_ID = creator.User_ID 
               LEFT JOIN users assignee ON r.Assigned_To = assignee.User_ID 
               WHERE r.Report_ID = ?";
$stmt = $conn->prepare($report_sql);
$stmt->bind_param("i", $report_id);
$stmt->execute();
$report = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$report) { die("Report not found."); }

// Fetch associated logs
$logs_sql = "SELECT l.*, u.Name as user_name FROM logs l JOIN users u ON l.User_ID = u.User_ID WHERE l.Report_ID = ? ORDER BY l.Timestamp DESC";
$stmt_logs = $conn->prepare($logs_sql);
$stmt_logs->bind_param("i", $report_id);
$stmt_logs->execute();
$logs = $stmt_logs->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_logs->close();

// Fetch media and group by Log_ID
$media_by_log = [];
$media_sql = $conn->prepare("SELECT Log_ID, File_path FROM media WHERE Report_ID = ? AND Log_ID IS NOT NULL");
$media_sql->bind_param("i", $report_id);
$media_sql->execute();
$media_result = $media_sql->get_result();
while ($media_item = $media_result->fetch_assoc()) {
    $media_by_log[$media_item['Log_ID']][] = $media_item;
}
$media_sql->close();

?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2">Report #<?= $report['Report_ID'] ?>: <?= htmlspecialchars($report['Title']) ?></h1>
    <a href="javascript:history.back()" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Go Back</a>
</div>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card shadow-sm mb-4 sticky-top" style="top: 1rem;">
            <div class="card-header"><h5 class="mb-0">Report Details</h5></div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-4">Status:</dt>
                    <dd class="col-sm-8"><?= getStatusBadge($report['Status']) ?></dd>

                    <dt class="col-sm-4">Urgency:</dt>
                    <dd class="col-sm-8"><span class="fw-bold <?= getUrgencyClass($report['Urgency']) ?>"><?= ucfirst($report['Urgency']) ?></span></dd>
                    
                    <dt class="col-sm-4">Location:</dt>
                    <dd class="col-sm-8"><?= htmlspecialchars($report['House_Name']) ?> <small class="text-muted d-block"><?= htmlspecialchars($report['Address']) ?></small></dd>

                    <dt class="col-sm-4">Assigned To:</dt>
                    <dd class="col-sm-8"><?= htmlspecialchars($report['assignee_name'] ?? 'N/A') ?></dd>

                    <dt class="col-sm-4">Created By:</dt>
                    <dd class="col-sm-8"><?= htmlspecialchars($report['creator_name']) ?></dd>
                    
                    <dt class="col-sm-4">Created On:</dt>
                    <dd class="col-sm-8"><?= date('d M Y, h:i A', strtotime($report['Created_at'])) ?></dd>
                </dl>
                <hr>
                <h6>Description:</h6>
                <p class="text-muted" style="white-space: pre-wrap;"><?= htmlspecialchars($report['Description']) ?></p>
            </div>
        </div>

        <?php if ($_SESSION['user_id'] == $report['Assigned_To'] && $report['Status'] == 'in_progress'): ?>
        <div class="card shadow-sm mb-4">
            <div class="card-header"><h5 class="mb-0">Add Progress Evidence</h5></div>
            <div class="card-body">
                <form action="add_evidence.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="report_id" value="<?= $report['Report_ID'] ?>">
                    <div class="mb-3">
                        <label for="details" class="form-label">Notes / Details (Optional)</label>
                        <textarea name="details" id="details" class="form-control" rows="3" placeholder="e.g., 'Purchased new parts', 'Initial inspection complete'"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="evidence" class="form-label">Upload New Photos/Videos</label>
                        <input type="file" name="evidence[]" id="evidence" class="form-control" accept="image/*,video/*" multiple required>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Evidence</button>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <div class="col-lg-7">
         <div class="card shadow-sm">
            <div class="card-header"><h5 class="mb-0">Activity Log & Evidence</h5></div>
            <div class="card-body">
                <?php if (empty($logs)): ?>
                    <p class="text-muted text-center p-4">No activity has been logged for this report yet.</p>
                <?php else: ?>
                    <ul class="timeline">
                        <?php foreach ($logs as $log): ?>
                        <li class="timeline-item">
                            <div class="timeline-icon bg-primary"><i class="fas fa-info"></i></div>
                            <div class="timeline-body">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><?= htmlspecialchars($log['Action']) ?> by <?= htmlspecialchars($log['user_name']) ?></h6>
                                    <small class="text-muted"><?= date('d M Y, h:i A', strtotime($log['Timestamp'])) ?></small>
                                </div>
                                <?php if(!empty($log['Details'])): ?><p class="mb-2 text-muted"><em>"<?= htmlspecialchars($log['Details']) ?>"</em></p><?php endif; ?>
                                
                                <?php if (isset($media_by_log[$log['Log_ID']])): ?>
                                <div class="d-flex flex-wrap gap-2 mt-2 border-top pt-2">
                                    <?php foreach ($media_by_log[$log['Log_ID']] as $media): ?>
                                    <a href="<?= htmlspecialchars($media['File_path']) ?>" target="_blank" data-bs-toggle="tooltip" title="View full size">
                                        <img src="<?= htmlspecialchars($media['File_path']) ?>" alt="Evidence" class="media-thumbnail">
                                    </a>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'templates/footer.php'; ?>