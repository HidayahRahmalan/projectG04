<?php
session_start();
$successMessage = '';

if (isset($_SESSION['register_success'])) {
    $successMessage = $_SESSION['register_success'];
    unset($_SESSION['register_success']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="CSS/style.css"> <!-- Link to external CSS -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600&display=swap" rel="stylesheet">
</head>
<body>


<?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success text-center" role="alert">
        <?php
            echo $_SESSION['success_message'];
            unset($_SESSION['success_message']);
        ?>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['login_error'])): ?>
    <div class="alert alert-danger text-center" role="alert">
        <?php
            echo $_SESSION['login_error'];
            unset($_SESSION['login_error']);
        ?>
    </div>
<?php endif; ?>

<?php if ($successMessage): ?>
  <div id="successAlert" class="alert alert-success text-center" role="alert">
    <?= htmlspecialchars($successMessage) ?>
  </div>
<?php endif; ?>



<div class="container mt-5">
    <div class="form-container">
        <h2 class="text-center title">Login</h2>
        <p class="text-center subtitle">Sign in to continue</p>
        <form action="backend/process_login.php" method="POST">
            <div class="mb-3 input-group">
                <span class="input-group-text"><i class="fa-solid fa-envelope"></i></span>
                <input type="email" name="email" class="form-control input-style" id="email" placeholder="Enter your email">
            </div>
            <div class="mb-3 input-group">
                <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                <input type="password" name="password" class="form-control input-style" id="password" placeholder="Enter password">
            </div>
            <button type="submit" class="btn btn-gold2 w-100">Login</button>
            <p class="text-center mt-3">Don't have an account? <a href="register.php">Register</a></p>
        </form>
    </div>
</div>

<!-- Error Modal -->
<div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-danger" style="background: #1c1c1c;">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="errorModalLabel">Login Failed</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" style="color: white">
        <!-- Error message would appear here -->
      </div>
    </div>
  </div>
</div>

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-success" style="background: #1c1c1c;">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="successModalLabel">Success</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" style="color: white">
        <!-- Success message would appear here -->
      </div>
    </div>
  </div>
</div>

<!-- Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- You can keep these script blocks if you want to manually trigger the modals for testing -->
<script>
    // To test error modal:
    // var errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
    // errorModal.show();
    
    // To test success modal:
    // var successModal = new bootstrap.Modal(document.getElementById('successModal'));
    // successModal.show();
</script>
</body>
</html>