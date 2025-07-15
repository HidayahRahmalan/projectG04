<?php
session_start();
$register_error = $_SESSION['register_error'] ?? null;
if ($register_error) {
    unset($_SESSION['register_error']);
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="CSS/style.css"> <!-- Link to external CSS -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600&display=swap" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <div class="form-container">
        <h2 class="text-center title">Create an Account</h2><br>
        <form id="registerForm" action="backend/process_register.php" method="POST">
            <div class="mb-3 input-group">
                <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
                <input type="text" name="fullname" class="form-control input-style" id="fullname" placeholder="Enter your full name" required>
            </div>
            <div class="mb-3 input-group">
                <span class="input-group-text"><i class="fa-solid fa-envelope"></i></span>
                <input type="email" name="email" class="form-control input-style" id="email" placeholder="Enter your email" required>
            </div>
            <div class="mb-3 input-group">
                <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                <input type="password" name="password" class="form-control input-style" id="password" placeholder="Enter password" required>
            </div>
            <div class="mb-3 input-group">
                <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                <input type="password" class="form-control input-style" id="confirmPassword" placeholder="Confirm password" required>
            </div>
            <button type="submit" class="btn btn-gold2 w-100">Register</button>
            <p class="text-center mt-3">Already have an account? <a href="login.php">Login</a></p>
        </form>
    </div>
</div>

<!-- Modal for Validation Error -->
<div class="modal fade" id="validationModal" tabindex="-1" aria-labelledby="validationModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-danger" style="background: #1c1c1c;">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="validationModalLabel">Registration Failed</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="modalErrorMsg" style="color: white">
        <!-- Error message will be injected here -->
      </div>
    </div>
  </div>
</div>

<!-- Bootstrap & Validation Script -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('registerForm').addEventListener('submit', function(event) {
  const email = document.getElementById('email').value.trim();
  const password = document.getElementById('password').value;
  const confirm_password = document.getElementById('confirmPassword').value;
  const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  let errorMessage = '';

  // Validate email format
  if (!emailPattern.test(email)) {
    errorMessage = 'Please enter a valid email address.';
  }
  // Check if passwords match
  else if (password !== confirm_password) {
    errorMessage = 'Passwords do not match.';
  }

  // If there's an error, show modal and prevent form submission
  if (errorMessage) {
    event.preventDefault();
    document.getElementById('modalErrorMsg').innerText = errorMessage;
    var validationModal = new bootstrap.Modal(document.getElementById('validationModal'));
    validationModal.show();
  }
});

document.addEventListener('DOMContentLoaded', function () {
    <?php if ($register_error): ?>
        document.getElementById('modalErrorMsg').innerText = <?php echo json_encode($register_error); ?>;
        var validationModal = new bootstrap.Modal(document.getElementById('validationModal'));
        validationModal.show();
    <?php endif; ?>
});
</script>

</body>
</html>