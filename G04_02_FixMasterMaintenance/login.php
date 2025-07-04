<?php
session_start();
include 'connection.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $staffID = trim($_POST['staffid']);
  $password = $_POST['password'];

  // Select StaffName too
  $stmt = $conn->prepare("SELECT StaffID, StaffName, Password, Role FROM Staff WHERE StaffID = ?");
  $stmt->bind_param("s", $staffID);
  $stmt->execute();
  $stmt->store_result();

  if ($stmt->num_rows > 0) {
    // Bind StaffName along with other fields
    $stmt->bind_result($dbStaffID, $dbStaffName, $dbPassword, $role);
    $stmt->fetch();

    if ($password === $dbPassword) {  // plain text password comparison
      $_SESSION['staffID'] = $dbStaffID;
      $_SESSION['staffName'] = $dbStaffName;  // store the staff name here
      $_SESSION['role'] = $role;

      if ($role == 'Admin') {
        header("Location: index.php");
      } else {
        header("Location: maintenance_index.php");
      }
      exit();
    } else {
      $error = "Invalid password.";
    }
  } else {
    $error = "User not found.";
  }
  $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Facilities Maintenance System - Login</title>
  <!-- Bootstrap 5 CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      min-height: 100vh;
      background: linear-gradient(135deg, #2c3e50, #4b6584);
      font-family: 'Roboto', sans-serif;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .login-card {
      border-radius: 16px;
      box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.25);
      background: #ecf0f1;
      padding: 2.5rem 2rem;
      position: relative;
      max-width: 400px;
      margin: 2rem auto;
      width: 100%;
    }

    .login-icon {
      width: 60px;
      height: 60px;
      background: #f39c12;
      color: #fff;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 2.2rem;
      position: absolute;
      left: 50%;
      top: -30px;
      transform: translateX(-50%);
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.12);
    }

    .login-title {
      text-align: center;
      margin-top: 2.5rem;
      margin-bottom: 1.5rem;
      color: #34495e;
      font-weight: 700;
      font-size: 1.6rem;
    }

    .form-label {
      font-weight: 600;
      color: #2d3436;
    }

    .form-control:focus {
      border-color: #f39c12;
      box-shadow: 0 0 0 0.2rem rgba(243, 156, 18, 0.15);
    }

    .btn-login {
      background: #f39c12;
      color: #fff;
      font-weight: 700;
      transition: background 0.3s;
    }

    .btn-login:hover {
      background: #d88e0a;
      color: #fff;
    }

    .error-message {
      color: #e74c3c;
      font-weight: 600;
      margin-top: 10px;
      text-align: center;
    }

    .footer-text {
      text-align: center;
      font-size: 13px;
      color: #7f8c8d;
      margin-top: 18px;
    }

    @media (max-width: 576px) {
      .login-card {
        padding: 2rem 1rem 1.5rem 1rem;
      }

      .login-title {
        font-size: 1.2rem;
      }
    }
  </style>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
</head>

<body>
  <div class="container">
    <div class="row justify-content-center align-items-center min-vh-100">
      <div class="col-12 col-sm-8 col-md-6 col-lg-5">
        <div class="login-card position-relative">
          <div class="login-icon mb-2" title="Facilities Maintenance">
            &#128295; <!-- wrench emoji -->
          </div>
          <div class="login-title">
            Facilities Maintenance Login
          </div>
          <form method="POST" action="" autocomplete="off">
            <div class="mb-3">
              <label for="staffid" class="form-label">Staff ID</label>
              <input type="text" class="form-control" id="staffid" name="staffid" required autocomplete="off" />
            </div>
            <div class="mb-3">
              <label for="password" class="form-label">Password</label>
              <input type="password" class="form-control" id="password" name="password" required autocomplete="off" />
            </div>
            <button type="submit" class="btn btn-login w-100 mb-2">Login</button>
          </form>
          <?php if ($error): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
          <?php endif; ?>
          <div class="footer-text mt-3">&copy; <?php echo date('Y'); ?> Group 2</div>
        </div>
      </div>
    </div>
  </div>
  <!-- Bootstrap 5 JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>