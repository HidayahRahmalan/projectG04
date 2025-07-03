<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}
require_once 'db_conn.php';

// Fetch all appointments logic... (no changes here)
$sql = "SELECT a.AppointmentNo, a.AppointmentDate, a.Status, p.Name as PatientName, d.Name as DoctorName, c.ClinicName 
        FROM Appointment a
        JOIN Patient p ON a.PatientID = p.PatientID
        JOIN Doctor d ON a.DoctorID = d.DoctorID
        JOIN Clinic c ON a.ClinicID = c.ClinicID
        ORDER BY CASE a.Status WHEN 'Pending Approval' THEN 1 WHEN 'Scheduled' THEN 2 ELSE 3 END, a.AppointmentDate ASC";
$appointments = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Appointments - Admin</title>
    <link rel="stylesheet" href="css/admin_style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<div class="admin-wrapper">
    <?php 
        $active_page = 'appointments';
        include 'admin_sidebar.php'; 
    ?>
    <main class="admin-main-content">
        <header class="main-header">
            <h1>Manage All Appointments</h1>
            <p>View, approve, and manage all scheduled and pending appointments.</p>
        </header>

        <section class="data-section">
            <div class="data-table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Patient</th>
                            <th>Doctor</th>
                            <th>Clinic</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($appointments->num_rows > 0): ?>
                            <?php while($row = $appointments->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo date('d M Y, g:i A', strtotime($row['AppointmentDate'])); ?></td>
                                    <td><?php echo htmlspecialchars($row['PatientName']); ?></td>
                                    <td>Dr. <?php echo htmlspecialchars($row['DoctorName']); ?></td>
                                    <td><?php echo htmlspecialchars($row['ClinicName']); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $row['Status'])); ?>">
                                            <?php echo htmlspecialchars($row['Status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($row['Status'] == 'Pending Approval'): ?>
                                            <a href="admin_approve_request.php?id=<?php echo $row['AppointmentNo']; ?>&action=approve" class="action-btn">Approve</a>
                                            <a href="admin_approve_request.php?id=<?php echo $row['AppointmentNo']; ?>&action=deny" class="action-btn-delete">Deny</a>
                                        <?php else: ?>
                                            <!-- ============================================= -->
                                            <!-- NEW DROPDOWN BUTTON STRUCTURE                 -->
                                            <!-- ============================================= -->
                                            <div class="action-dropdown">
                                                <button class="action-btn">Actions</button>
                                                <div class="dropdown-content">
                                                    <a href="admin_reschedule_appointment.php?id=<?php echo $row['AppointmentNo']; ?>">Reschedule</a>
                                                    <a href="admin_cancel_appointment.php?id=<?php echo $row['AppointmentNo']; ?>">Cancel Appointment</a>
                                                    <a href="admin_delete_appointment.php?id=<?php echo $row['AppointmentNo']; ?>" 
                                                       onclick="return confirm('WARNING: This will permanently delete the appointment and all associated records. This cannot be undone. Are you sure?');">
                                                       Drop Appointment
                                                    </a>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="6">No appointments found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>
<!-- NOTE: The modal and the script tag have been removed as they are no longer needed. -->
</body>
</html>