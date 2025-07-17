<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}
require_once 'db_conn.php';

// ==========================================================
// CHANGE 1: DYNAMIC QUERY BUILDING STARTS HERE
// ==========================================================

// --- Prepare data for the filter dropdowns ---
$sql_clinics = "SELECT ClinicID, ClinicName FROM Clinic ORDER BY ClinicName";
$clinics = $conn->query($sql_clinics);
$sql_doctors = "SELECT DoctorID, Name FROM Doctor ORDER BY Name";
$doctors = $conn->query($sql_doctors);

// --- Process filter parameters from the URL path query ---
$clinic_filter = isset($_GET['clinic_id']) ? intval($_GET['clinic_id']) : '';
$doctor_filter = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : '';
$date_filter = isset($_GET['date']) ? $_GET['date'] : '';

// --- Build the dynamic SQL query ---
$sql = "SELECT a.AppointmentNo, a.AppointmentDate, a.Status, p.Name as PatientName, d.Name as DoctorName, c.ClinicName 
        FROM Appointment a
        JOIN Patient p ON a.PatientID = p.PatientID
        JOIN Doctor d ON a.DoctorID = d.DoctorID
        JOIN Clinic c ON a.ClinicID = c.ClinicID";

// Dynamically add WHERE clauses based on filters
$where_clauses = [];
$params = [];
$types = '';

if (!empty($clinic_filter)) {
    $where_clauses[] = "a.ClinicID = ?";
    $params[] = $clinic_filter;
    $types .= 'i';
}
if (!empty($doctor_filter)) {
    $where_clauses[] = "a.DoctorID = ?";
    $params[] = $doctor_filter;
    $types .= 'i';
}
if (!empty($date_filter)) {
    $where_clauses[] = "DATE(a.AppointmentDate) = ?";
    $params[] = $date_filter;
    $types .= 's';
}

if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(" AND ", $where_clauses);
}

// Add the original ordering
$sql .= " ORDER BY CASE a.Status WHEN 'Pending Approval' THEN 1 WHEN 'Scheduled' THEN 2 ELSE 3 END, a.AppointmentDate ASC";

// Prepare and execute the final query
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    // The '...' is the splat operator, it unpacks the array into arguments
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$appointments = $stmt->get_result();
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

        <!-- ========================================================== -->
        <!-- CHANGE 2: THE NEW FILTER BAR IS ADDED HERE               -->
        <!-- ========================================================== -->
        <section class="filter-bar">
            <form action="admin_manage_appointments.php" method="GET" class="filter-form">
                <div class="filter-group">
                    <label for="clinic_id">Filter by Clinic:</label>
                    <select name="clinic_id" id="clinic_id">
                        <option value="">All Clinics</option>
                        <?php while($clinic = $clinics->fetch_assoc()): ?>
                            <option value="<?php echo $clinic['ClinicID']; ?>" <?php if($clinic_filter == $clinic['ClinicID']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($clinic['ClinicName']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="doctor_id">Filter by Doctor:</label>
                    <select name="doctor_id" id="doctor_id">
                        <option value="">All Doctors</option>
                        <?php while($doctor = $doctors->fetch_assoc()): ?>
                            <option value="<?php echo $doctor['DoctorID']; ?>" <?php if($doctor_filter == $doctor['DoctorID']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($doctor['Name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="date">Filter by Date:</label>
                    <input type="date" name="date" id="date" value="<?php echo htmlspecialchars($date_filter); ?>">
                </div>
                <div class="filter-group">
                    <button type="submit" class="action-btn">Filter</button>
                    <a href="admin_manage_appointments.php" class="back-button" style="margin-left: 10px; text-decoration:none;">Clear</a>
                </div>
            </form>
        </section>

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
                            <tr><td colspan="6">No appointments found matching your criteria.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>
</body>
</html>