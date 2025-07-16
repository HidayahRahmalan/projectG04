<?php
$page_title = "My Active Tasks";
include 'templates/header.php';

if ($_SESSION['role'] == 5) { header("Location: admin_dashboard.php"); exit(); }

$user_id = $_SESSION['user_id'];

$tasks_stmt = $conn->prepare("
    SELECT r.Report_ID, r.Title, r.Urgency, r.Status, l.House_Name
    FROM report r JOIN locations l ON r.Location = l.Location_ID
    WHERE r.Assigned_To = ? AND r.Status IN ('pending', 'in_progress')
    ORDER BY CASE r.Urgency WHEN 'high' THEN 1 WHEN 'medium' THEN 2 ELSE 3 END, r.Created_at ASC
");
$tasks_stmt->bind_param("i", $user_id);
$tasks_stmt->execute();
$tasks = $tasks_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$tasks_stmt->close();
?>

<!-- Resolve Task Modal -->
<div class="modal fade" id="resolveModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Resolve Task</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form action="update_status.php" method="POST" enctype="multipart/form-data">
        <div class="modal-body">
            <p>To resolve this task, provide details and upload at least one photo of the completed work.</p>
            <input type="hidden" name="report_id" id="modal_report_id">
            <input type="hidden" name="status" value="resolved">
            <div class="mb-3">
                <label class="form-label">Resolution Details (Mandatory)</label>
                <textarea name="details" class="form-control" rows="4" required placeholder="e.g., Replaced broken pipe section, fixed wiring issue at main switch."></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Evidence of Work (Mandatory)</label>
                <input type="file" name="evidence[]" class="form-control" accept="image/*,video/*" required multiple>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Mark as Resolved</button>
        </div>
      </form>
    </div>
  </div>
</div>

<h1 class="h2 mb-4">My Active Tasks</h1>
    
<?php if (empty($tasks)): ?>
    <div class="card shadow-sm"><div class="card-body text-center p-5">
        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
        <h4>No Active Tasks</h4>
        <p class="text-muted">You have no pending or in-progress tasks assigned. Great job!</p>
    </div></div>
<?php else: foreach ($tasks as $task): ?>
    <div class="card shadow-sm mb-3">
        <div class="card-header d-flex justify-content-between align-items-center bg-white">
            <h5 class="mb-0"><?= htmlspecialchars($task['Title']) ?></h5>
            <?= getStatusBadge($task['Status']) ?>
        </div>
        <div class="card-body">
            <h6 class="card-subtitle mb-2 text-muted"><i class="fas fa-map-marker-alt me-2"></i>Location: <?= htmlspecialchars($task['House_Name']) ?></h6>
            <p class="card-text">Urgency: <span class="fw-bold <?= getUrgencyClass($task['Urgency']) ?>"><?= ucfirst($task['Urgency']) ?></span></p>
        </div>
        <div class="card-footer bg-light d-flex gap-2">
            <a href="view_report.php?id=<?= $task['Report_ID'] ?>" class="btn btn-secondary btn-sm"><i class="fas fa-eye me-1"></i>View Details</a>
            <?php if ($task['Status'] == 'pending'): ?>
            <form action="update_status.php" method="POST" class="d-inline">
                <input type="hidden" name="report_id" value="<?= $task['Report_ID'] ?>">
                <input type="hidden" name="status" value="in_progress">
                <input type="hidden" name="details" value="Work has started on the report.">
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-play me-1"></i>Start Work</button>
            </form>
            <?php elseif ($task['Status'] == 'in_progress'): ?>
            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#resolveModal" data-bs-report-id="<?= $task['Report_ID'] ?>">
              <i class="fas fa-check me-1"></i>Mark as Resolved
            </button>
            <?php endif; ?>
        </div>
    </div>
<?php endforeach; endif; ?>

<?php include 'templates/footer.php'; ?>