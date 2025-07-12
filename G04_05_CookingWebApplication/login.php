<?php 
session_start();
include('connect.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        $stmt = $conn->prepare("SELECT * FROM USER WHERE USERNAME = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Hash the entered password (match method used in signup.php)
        $input_hash = substr(hash('sha256', $password . 'y0ur_S@lt_Here'), 0, 16);

        if ($user && isset($user['PASSWORD']) && hash_equals($user['PASSWORD'], $input_hash)) {
            $_SESSION['user_id'] = $user['USER_ID'];
            $_SESSION['username'] = $user['USERNAME'];
            $_SESSION['role'] = $user['ROLE'];
            header("Location: INDEX.php");
            exit();
        } else {
            $error = "Invalid username or password";
        }
    } else {
        $error = "Please enter both username and password";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CookingApp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .card {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .card-header {
            background-color: #343a40;
            color: white;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="INDEX.php">CookingApp</a>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="signup.php">Sign Up</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card">
                    <div class="card-header text-center">
                        <h4>Login to Your Account</h4>
                    </div>
                    <div class="card-body p-4">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <?= htmlspecialchars($error) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" novalidate>
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required 
                                       value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>">
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Login</button>
                            </div>
                        </form>
                        
                        <div class="mt-3 text-center">
                            <p class="mb-0">Don't have an account? <a href="signup.php">Sign up here</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
