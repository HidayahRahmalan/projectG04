<?php 
include('connect.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];
    
    // Create a shorter but still secure hash
    $hashed_password = substr(hash('sha256', $password . 'y0ur_S@lt_Here'), 0, 16);
    
    // Check if username exists
    $check = $conn->prepare("SELECT * FROM `USER` WHERE username = ?");
    $check->execute([$username]);
    
    if ($check->rowCount() > 0) {
        $error = "Username already exists!";
    } else {
        try {
            $stmt = $conn->prepare("INSERT INTO `USER` (username, password, role) VALUES (?, ?, ?)");
            $stmt->execute([$username, $hashed_password, $role]);
            
            $success = true;
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - CookingApp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php if(isset($success)): ?>
    <meta http-equiv="refresh" content="2;url=login.php">
    <?php endif; ?>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="INDEX.php">CookingApp</a>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-center">Sign Up</h3>
                    </div>
                    <div class="card-body">
                        <?php if(isset($error)): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                        
                        <?php if(isset($success)): ?>
                        <div class="alert alert-success">
                            Registration successful! Redirecting to login page...
                        </div>
                        <?php endif; ?>
                        
                        <form method="POST" <?php if(isset($success)) echo 'style="display:none;"'; ?>>
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Role</label>
                                <div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="role" id="chef" value="CHEF" checked>
                                        <label class="form-check-label" for="chef">Chef</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="role" id="student" value="STUDENT">
                                        <label class="form-check-label" for="student">Student</label>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Sign Up</button>
                        </form>
                        <div class="mt-3 text-center" <?php if(isset($success)) echo 'style="display:none;"'; ?>>
                            Already have an account? <a href="login.php">Login</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
