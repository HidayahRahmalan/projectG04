<?php
function recordLog(mysqli $conn, int $report_id, int $user_id, string $action, ?string $details = null): int|bool {
    $stmt = $conn->prepare("INSERT INTO logs (Report_ID, User_ID, Action, Details) VALUES (?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("iiss", $report_id, $user_id, $action, $details);
        if ($stmt->execute()) {
            $log_id = $stmt->insert_id;
            $stmt->close();
            return $log_id;
        }
        $stmt->close();
    }
    error_log("Failed to record log: " . $conn->error);
    return false;
}

function getStatusBadge(string $status): string {
    $status_text = ucwords(str_replace('_', ' ', $status));
    $badge_class = 'bg-secondary';
    switch ($status) {
        case 'pending': $badge_class = 'bg-warning text-dark'; break;
        case 'in_progress': $badge_class = 'bg-info text-dark'; break;
        case 'resolved': $badge_class = 'bg-success'; break;
    }
    return "<span class='badge rounded-pill {$badge_class}'>{$status_text}</span>";
}

function getUrgencyClass(string $urgency): string {
    switch ($urgency) {
        case 'high': return 'text-danger';
        case 'medium': return 'text-warning';
        case 'low': return 'text-success';
        default: return 'text-muted';
    }
}
?>