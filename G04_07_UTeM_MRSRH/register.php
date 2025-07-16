<?php
session_start();
// This page is primarily for admins. If a non-admin is logged in, redirect them.
if (isset($_SESSION['user_id']) && $_SESSION['role'] != 5) {
    header("Location: staff_dashboard.php");
    exit();
}

require 'db.php';
$errors = [];
$success = '';

// Fetch available roles from the database to populate the dropdown
$roles = [];
$result = $conn->query("SELECT Role_ID, Role_Name FROM roles WHERE Role_ID != 5 ORDER BY Role_Name");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $roles[] = $row;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $role_id = filter_input(INPUT_POST, 'role_id', FILTER_VALIDATE_INT);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // --- Validation ---
    if (empty($name)) { $errors[] = "Full Name is required."; }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = "A valid Email is required."; }
    if (empty($phone)) { $errors[] = "Phone Number is required."; }
    if (empty($role_id)) { $errors[] = "A Role must be selected."; }
    if (empty($password)) { $errors[] = "Password is required."; }
    if (strlen($password) < 8) { $errors[] = "Password must be at least 8 characters long."; }
    if ($password !== $confirm_password) { $errors[] = "Passwords do not match."; }

    // Check if email already exists
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT User_ID FROM users WHERE Email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = "An account with this email address already exists.";
        }
        $stmt->close();
    }

    // --- Process Registration ---
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users (Name, Email, Phone, Role, Password) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssis", $name, $email, $phone, $role_id, $hashed_password);

        if ($stmt->execute()) {
            $success = "User '{$name}' registered successfully! They can now log in.";
            // Clear post data on success
            $_POST = [];
        } else {
            $errors[] = "Registration failed. Please try again. Error: " . $stmt->error;
        }
        $stmt->close();
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register New User - UTeM Maintenance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body class="login-page">
    
    <!-- THE FIX IS HERE: Changed class from "main-container" to "login-container" -->
    <div class="login-container container">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-lg border-0 login-card">
                <div class="card-body p-4 p-sm-5">
                    <div class="text-center mb-4">
                        <img src="assets/img/utem_logo.jpg" alt="UTeM Logo" style="height: 60px;">
                    </div>
                    <h2 class="card-title text-center mb-4">Register New User</h2>
                    
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success">
                            <?php echo htmlspecialchars($success); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="register.php">
                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" name="name" id="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" name="email" id="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" name="phone" id="phone" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="role_id" class="form-label">Assign Role</label>
                            <select name="role_id" id="role_id" class="form-select" required>
                                <option value="" disabled selected>-- Select a Role --</option>
                                <?php foreach ($roles as $role): ?>
                                    <option value="<?= $role['Role_ID'] ?>" <?= (isset($_POST['role_id']) && $_POST['role_id'] == $role['Role_ID']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($role['Role_Name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <hr class="my-4">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" name="password" id="password" required>
                                <div class="form-text">Minimum 8 characters.</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="confirm_password" class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" name="confirm_password" id="confirm_password" required>
                            </div>
                        </div>
                        <div class="d-grid mt-3">
                            <button type="submit" class="btn btn-success btn-lg">Register User</button>
                        </div>
                        <?php if(isset($_SESSION['user_id']) && $_SESSION['role'] == 5): ?>
                            <div class="text-center mt-3">
                                <a href="admin_dashboard.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Back to Dashboard</a>
                            </div>
                        <?php else: ?>
                             <div class="text-center mt-3">
                                <a href="login.php">Already have an account? Login</a>
                            </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>