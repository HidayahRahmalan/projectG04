<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: " . ($_SESSION['role'] == 5 ? 'admin_dashboard.php' : 'staff_dashboard.php'));
    exit();
}

require 'db.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (!empty($email) && !empty($password)) {
        $stmt = $conn->prepare("SELECT User_ID, Name, Password, Role FROM users WHERE Email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $name, $hashed_password, $role_id);
            $stmt->fetch();
            if (password_verify($password, $hashed_password)) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $id;
                $_SESSION['name'] = $name;
                $_SESSION['role'] = $role_id;
                header("Location: " . ($role_id == 5 ? 'admin_dashboard.php' : 'staff_dashboard.php'));
                exit();
            }
        }
    }
    $error = "Invalid email or password.";
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - UTeM Maintenance System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body class="login-page">
    <div class="login-container container">
        <div class="col-md-6 col-lg-5 col-xl-4">
            <div class="card shadow-lg border-0 login-card">
                <div class="card-body p-4 p-sm-5">
                    <div class="text-center mb-4">
                        <img src="assets/img/utem_logo.jpg" alt="UTeM Logo" style="height: 70px;">
                    </div>
                    <h2 class="card-title text-center mb-4">Maintenance Portal</h2>
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    <form method="POST" action="login.php">
                        <div class="form-floating mb-3">
                            <input type="email" class="form-control" name="email" id="email" placeholder="name@example.com" required>
                            <label for="email">Email address</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="password" class="form-control" name="password" id="password" placeholder="Password" required>
                            <label for="password">Password</label>
                        </div>
                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">Login</button>
                        </div>
                    </form>
                    <div class="text-center mt-4">
                        <a href="index.php" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-home me-1"></i> Back to Home Page
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>