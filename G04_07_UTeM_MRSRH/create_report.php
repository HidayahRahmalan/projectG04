<?php
session_start();
// Security Check: Only Admins (Role 5) can access this page.
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 5) {
    header("Location: login.php");
    exit();
}

require 'db.php';
require 'functions.php';

// Fetch locations and roles for the form dropdowns
$locations_result = $conn->query("SELECT Location_ID, House_Name FROM locations ORDER BY House_Name");
// Fetch only staff roles (exclude Admin)
$roles_result = $conn->query("SELECT Role_ID, Role_Name FROM roles WHERE Role_ID != 5 ORDER BY Role_Name");


// --- Handle Form Submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn->begin_transaction();
    try {
        // 1. Sanitize all inputs
        $title = trim(filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING));
        $location_id = filter_input(INPUT_POST, 'location', FILTER_SANITIZE_NUMBER_INT);
        $role_id = filter_input(INPUT_POST, 'role_id', FILTER_SANITIZE_NUMBER_INT);
        $urgency = trim(filter_input(INPUT_POST, 'urgency', FILTER_SANITIZE_STRING));
        $description = trim(filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING));
        $admin_user_id = $_SESSION['user_id'];

        // Also get the category text from the role for storing in the report table
        $category_stmt = $conn->prepare("SELECT Role_Name FROM roles WHERE Role_ID = ?");
        $category_stmt->bind_param("i", $role_id);
        $category_stmt->execute();
        $category = $category_stmt->get_result()->fetch_assoc()['Role_Name'] ?? 'General';
        $category_stmt->close();

        if (empty($title) || empty($location_id) || empty($role_id)) {
            throw new Exception("Title, Location, and Assigned Role are required fields.");
        }

        // 2. Find the least busy staff member with that Role ID
        $assignment_sql = "
            SELECT u.User_ID
            FROM users u
            LEFT JOIN (
                SELECT Assigned_To, COUNT(*) AS active_tasks
                FROM report
                WHERE Status IN ('pending', 'in_progress')
                GROUP BY Assigned_To
            ) r ON u.User_ID = r.Assigned_To
            WHERE u.Role = ?
            ORDER BY COALESCE(r.active_tasks, 0) ASC, RAND() -- Order by tasks, then randomize to distribute load
            LIMIT 1
        ";
        $assign_stmt = $conn->prepare($assignment_sql);
        $assign_stmt->bind_param("i", $role_id);
        $assign_stmt->execute();
        $assign_result = $assign_stmt->get_result();
        if ($assign_result->num_rows === 0) {
            throw new Exception("No available staff found for the role '{$category}'. Please add staff to this role.");
        }
        $assigned_to_id = $assign_result->fetch_assoc()['User_ID'];
        $assign_stmt->close();

        // 3. Insert the new report with the automatically assigned staff member
        $insert_stmt = $conn->prepare("INSERT INTO report (User_ID, Title, Description, Urgency, Location, Category, Assigned_To) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $insert_stmt->bind_param("isssisi", $admin_user_id, $title, $description, $urgency, $location_id, $category, $assigned_to_id);
        $insert_stmt->execute();
        
        $report_id = $insert_stmt->insert_id;
        if ($report_id == 0) {
            throw new Exception("Database error: Failed to create the report record.");
        }
        $insert_stmt->close();

        // 4. Log the automatic assignment action
        $staff_name_query = $conn->prepare("SELECT Name FROM users WHERE User_ID = ?");
        $staff_name_query->bind_param("i", $assigned_to_id);
        $staff_name_query->execute();
        $staff_name = $staff_name_query->get_result()->fetch_assoc()['Name'] ?? "ID " . $assigned_to_id;
        $staff_name_query->close();

        $log_details = "Report automatically assigned to {$staff_name} (least busy in '{$category}' role).";
        recordLog($conn, $report_id, $admin_user_id, 'Created & Auto-Assigned', $log_details);
        
        // If all steps succeeded, commit the transaction
        $conn->commit();
        $_SESSION['message'] = "Report created and automatically assigned to {$staff_name}!";
        $_SESSION['message_type'] = 'success';
        header("Location: admin_all_reports.php");
        exit();

    } catch (Exception $e) {
        // If any step failed, roll back all database changes
        $conn->rollback();
        $error = "Operation failed: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create & Auto-Assign Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">
    <?php include 'component_admin_sidebar.php'; ?>
    <div class="container-fluid p-4">
        <h1 class="h2 mb-4">Create New Report (with Role-Based Auto-Assignment)</h1>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <div class="alert alert-info d-flex align-items-center" role="alert">
            <i class="fas fa-info-circle fa-2x me-3"></i>
            <div>
                This form will automatically assign the report to the least busy staff member matching the **selected role**.
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-4">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Report Title</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Location</label>
                            <select name="location" class="form-select" required>
                                <option value="" disabled selected>Select Location...</option>
                                <?php if ($locations_result->num_rows > 0):
                                    $locations_result->data_seek(0);
                                    while($loc = $locations_result->fetch_assoc()): ?>
                                    <option value="<?= $loc['Location_ID'] ?>"><?= htmlspecialchars($loc['House_Name']) ?></option>
                                <?php endwhile; endif; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Assign to Role (Category)</label>
                            <select name="role_id" class="form-select" required>
                                <option value="" disabled selected>Select Role to Assign...</option>
                                 <?php if ($roles_result->num_rows > 0):
                                    $roles_result->data_seek(0);
                                    while($role = $roles_result->fetch_assoc()): ?>
                                    <option value="<?= $role['Role_ID'] ?>"><?= htmlspecialchars($role['Role_Name']) ?></option>
                                <?php endwhile; endif; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Urgency</label>
                        <select name="urgency" class="form-select" required>
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Description of Issue</label>
                        <textarea name="description" rows="5" class="form-control" required></textarea>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">Create and Auto-Assign Report</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>