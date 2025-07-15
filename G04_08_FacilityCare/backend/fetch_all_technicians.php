<?php
require_once '../connection.php';

function getTechnicianSpecialties($pdo, $technicianId) {
    $stmt = $pdo->prepare("
        SELECT s.speciality_name 
        FROM technician_speciality ts 
        JOIN speciality s ON ts.speciality_id = s.speciality_id 
        WHERE ts.technician_id = ?
    ");
    $stmt->execute([$technicianId]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function getInProgressJobCount($pdo, $technicianId) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) AS job_count
        FROM report r
        JOIN (
            SELECT s1.report_id, s1.status
            FROM statuslog s1
            INNER JOIN (
                SELECT report_id, MAX(created_at) AS max_time
                FROM statuslog
                GROUP BY report_id
            ) s2 ON s1.report_id = s2.report_id AND s1.created_at = s2.max_time
            WHERE s1.status = 'in progress'
        ) latest_status ON latest_status.report_id = r.report_id
        WHERE r.technician_id = ?
    ");
    $stmt->execute([$technicianId]);
    return $stmt->fetchColumn();
}

$stmt = $pdo->query("
    SELECT 
        u.user_id,
        u.name,
        u.email,
        t.phone_number,
        t.technician_status,
        t.profile_photo
    FROM user u
    JOIN technician t ON u.user_id = t.technician_id
");
$technicians = $stmt->fetchAll(PDO::FETCH_ASSOC);

$data = [];

foreach ($technicians as $tech) {
    $specialties = getTechnicianSpecialties($pdo, $tech['user_id']);
    $jobCount = getInProgressJobCount($pdo, $tech['user_id']);

    // Dummy rating for now
    $rating = 4.5; // You can query from a rating table if needed

    $data[] = [
        'id'              => $tech['user_id'],
        'name'            => $tech['name'],
        'email'           => $tech['email'],
        'phone'           => $tech['phone_number'],
        'status'          => $tech['technician_status'],
        'profile_photo'   => $tech['profile_photo'],
        'specialties'     => $specialties,
        'assigned_jobs'   => $jobCount,
        'rating'          => $rating
    ];
}

header('Content-Type: application/json');
echo json_encode($data);
