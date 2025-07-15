<?php
session_name("staff_session");
session_start();
require __DIR__ . '/../connection.php';

$userId = $_SESSION['user_id'] ?? null;
$pending = 0;
$inProgress = 0;
$recentActivities = [];

if ($userId) {
    // Get all reports by the user
    $stmt = $pdo->prepare("SELECT report_id, title FROM report WHERE user_id = ?");
    $stmt->execute([$userId]);
    $userReports = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($userReports) {
        // FIXED: Use correct key name
        $reportIds = array_column($userReports, 'report_id');

        if (!empty($reportIds)) {
            $reportIdList = implode(',', array_map('intval', $reportIds));

            // Get the latest status per report
            $stmt = $pdo->query("
                SELECT s1.report_id,r.title, s1.status, s1.notes, s1.timestamp, u.name AS technician_name
                FROM statuslog s1
                JOIN (
                    SELECT report_id, MAX(timestamp) AS latest_time
                    FROM statuslog
                    WHERE report_id IN ($reportIdList)
                    GROUP BY report_id
                ) s2 ON s1.report_id = s2.report_id AND s1.timestamp = s2.latest_time
                JOIN report r ON s1.report_id = r.report_id
                LEFT JOIN user u ON s1.changed_by = u.user_id
                ORDER BY s1.timestamp DESC
            ");

            $statusLogs = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $reportsWithStatus = [];

            foreach ($statusLogs as $log) {
                $reportsWithStatus[] = $log['report_id'];

                if (str_replace('_', ' ', strtolower($log['status'])) === 'in progress') {
                    $inProgress++;
                }

                $recentActivities[] = [
                    'report_id' => $log['report_id'],
                    'title' => $log['title'],
                    'status' => $log['status'],
                    'notes' => $log['notes'],
                    'time' => $log['timestamp'],
                    'technician' => $log['technician_name']

                ];
            }

            // Reports without any statuslog entry are considered pending
            $pending = count(array_diff($reportIds, $reportsWithStatus));
        }
    }
}
