<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}
require_once 'db_conn.php';

// Fetch all doctors from the database
$sql = "SELECT d.DoctorID, d.Name, d.Department, d.Email, d.ContactNumber 
        FROM Doctor d 
        ORDER BY d.Name";
$doctors_result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Doctors - Admin</title>
    <link rel="stylesheet" href="css/admin_style.css">
</head>
<body>
<div class="admin-wrapper">
    <?php include 'admin_sidebar.php'; // Use a separate file for the sidebar for easy maintenance ?>

    <main class="admin-main-content">
        <header class="main-header">
            <h1>Manage Doctors</h1>
            <a href="admin_add_doctor.php" class="action-btn" style="float: right; margin-top: -40px;">+ Add New Doctor</a>
        </header>

        <section class="data-section">
            <div class="data-table-container">
                <h2>All Doctors</h2>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Department</th>
                            <th>Email</th>
                            <th>Contact Number</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($doctors_result->num_rows > 0): ?>
                            <?php while($row = $doctors_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['Name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['Department']); ?></td>
                                    <td><?php echo htmlspecialchars($row['Email']); ?></td>
                                    <td><?php echo htmlspecialchars($row['ContactNumber'] ?? 'N/A'); ?></td>
                                    <td>
                                        <a href="admin_edit_schedule.php?doctor_id=<?php echo $row['DoctorID']; ?>" class="action-btn">Schedule</a>
                                        <a href="admin_edit_doctor.php?doctor_id=<?php echo $row['DoctorID']; ?>" class="action-btn">Edit</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5">No doctors found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>
</body>
</html>