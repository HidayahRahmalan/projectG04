<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}
require_once 'db_conn.php';

// --- Fetch data for the dashboard (This logic is unchanged) ---
$sql_total_users = "SELECT COUNT(*) as total FROM User";
$total_users = $conn->query($sql_total_users)->fetch_assoc()['total'];
$sql_total_doctors = "SELECT COUNT(*) as total FROM Doctor";
$total_doctors = $conn->query($sql_total_doctors)->fetch_assoc()['total'];
$sql_appts_today = "SELECT COUNT(*) as total FROM Appointment WHERE DATE(AppointmentDate) = CURDATE()";
$appts_today = $conn->query($sql_appts_today)->fetch_assoc()['total'];

$sql_appointments = "SELECT a.AppointmentDate, p.Name as PatientName, d.Name as DoctorName, c.ClinicName
                     FROM Appointment a
                     JOIN Patient p ON a.PatientID = p.PatientID
                     JOIN Doctor d ON a.DoctorID = d.DoctorID
                     JOIN Clinic c ON a.ClinicID = c.ClinicID
                     WHERE DATE(a.AppointmentDate) = CURDATE()
                     ORDER BY a.AppointmentDate ASC";
$appointments_result = $conn->query($sql_appointments);

$sql_users = "SELECT UserID, Username, Role, CreatedAt FROM User ORDER BY UserID";
$users_result = $conn->query($sql_users);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Clinic System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/admin_style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

<div class="admin-wrapper">
    
    <!-- ======================================================== -->
    <!-- THE FIX IS HERE: The old sidebar HTML is gone.           -->
    <!-- We set the active page variable, then include the file.  -->
    <!-- ======================================================== -->
    <?php 
        $active_page = 'dashboard'; // Set the active page
        include 'admin_sidebar.php'; 
    ?>

    <!-- Main Content (This part is unchanged) -->
    <main class="admin-main-content">
        <header class="main-header">
            <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
            <p>Here's a summary of the clinic's activity.</p>
        </header>

        <section class="stats-grid">
            <div class="stat-card"><h3>Total Users</h3><p><?php echo $total_users; ?></p></div>
            <div class="stat-card"><h3>Total Doctors</h3><p><?php echo $total_doctors; ?></p></div>
            <div class="stat-card"><h3>Appointments Today</h3><p><?php echo $appts_today; ?></p></div>
        </section>

        <section class="data-section">
            <div class="data-table-container">
                <h2>Today's Appointments</h2>
                <table class="data-table">
                    <thead><tr><th>Time</th><th>Patient</th><th>Doctor</th><th>Clinic</th></tr></thead>
                    <tbody>
                        <?php if ($appointments_result->num_rows > 0): ?>
                            <?php while($row = $appointments_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo date('g:i A', strtotime($row['AppointmentDate'])); ?></td>
                                    <td><?php echo htmlspecialchars($row['PatientName']); ?></td>
                                    <td><?php echo htmlspecialchars($row['DoctorName']); ?></td>
                                    <td><?php echo htmlspecialchars($row['ClinicName']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="4">No appointments scheduled for today.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="data-table-container">
                <h2>User Management</h2>
                <table class="data-table">
                    <thead><tr><th>User ID</th><th>Username</th><th>Role</th><th>Actions</th></tr></thead>
                    <tbody>
                         <?php if ($users_result->num_rows > 0): ?>
                            <?php while($row = $users_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $row['UserID']; ?></td>
                                    <td><?php echo htmlspecialchars($row['Username']); ?></td>
                                    <td><?php echo htmlspecialchars($row['Role']); ?></td>
                                    <td><a href="#" class="action-btn">Edit</a> <a href="#" class="action-btn-delete">Delete</a></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>

</body>
</html>