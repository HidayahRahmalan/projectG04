<?php
// Start the session and check if the user is an admin
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}

// Include the database connection
require_once 'db_conn.php';

// SQL query to fetch all patients and their associated email from the User table
$sql = "SELECT p.PatientID, p.Name, p.ICNumber, p.PhoneNumber, u.Username as Email
        FROM Patient p
        JOIN User u ON p.UserID = u.UserID
        ORDER BY p.Name ASC";
$patients_result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Patients - Admin</title>
    <link rel="stylesheet" href="css/admin_style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<div class="admin-wrapper">
    
    <?php 
        // Set the active page identifier for the sidebar
        $active_page = 'patients'; 
        // Include the reusable sidebar
        include 'admin_sidebar.php'; 
    ?>

    <main class="admin-main-content">
        <header class="main-header">
            <h1>Manage Patients</h1>
            <p>View and manage all registered patient information.</p>
        </header>

        <section class="data-section">
            <div class="data-table-container">
                <h2>All Patients</h2>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email (Username)</th>
                            <th>IC Number</th>
                            <th>Phone Number</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($patients_result && $patients_result->num_rows > 0): ?>
                            <?php while($row = $patients_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['Name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['Email']); ?></td>
                                    <td><?php echo htmlspecialchars($row['ICNumber'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($row['PhoneNumber'] ?? 'N/A'); ?></td>
                                    <td>
                                        <!-- This link correctly points to the edit page with the patient's ID -->
                                        <a href="admin_edit_patient.php?patient_id=<?php echo $row['PatientID']; ?>" class="action-btn">Edit</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5">No patients found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>
</body>
</html>