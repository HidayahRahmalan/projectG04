<?php
session_start();

$successMessage = $_SESSION['success_message'] ?? null;
$loginError = $_SESSION['login_error'] ?? null;

if (isset($_GET['login']) && $_GET['login'] === 'failed') {
  $_SESSION['login_error'] = "Invalid email or password. Please try again.";
} elseif (isset($_GET['register']) && $_GET['register'] === 'success') {
  $_SESSION['success_message'] = "Registration successful! Please login.";
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
  <style>
    /* Validation styles */
    .is-valid {
      border-color: #28a745 !important;
      background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8' viewBox='0 0 8 8'%3e%3cpath fill='%2328a745' d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e");
      background-repeat: no-repeat;
      background-position: right calc(0.375em + 0.1875rem) center;
      background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
    }

    .is-invalid {
      border-color: #dc3545 !important;
      background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='none' stroke='%23dc3545' viewBox='0 0 12 12'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
      background-repeat: no-repeat;
      background-position: right calc(0.375em + 0.1875rem) center;
      background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
    }

    .invalid-feedback {
      display: none;
      width: 100%;
      margin-top: 0.25rem;
      font-size: 0.875em;
      color: #dc3545;
    }

    .is-invalid~.invalid-feedback {
      display: block;
    }
  </style>
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

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      <?php if (isset($_SESSION['login_error'])): ?>
        const errorModalElement = document.getElementById('errorModal');
        const errorModal = new bootstrap.Modal(errorModalElement);
        const modalBody = errorModalElement.querySelector('.modal-body');
        modalBody.textContent = <?= json_encode($_SESSION['login_error']) ?>;
        errorModal.show();
        <?php unset($_SESSION['login_error']); ?>
      <?php endif; ?>
    });
  </script>

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
          <input type="email" name="email" class="form-control input-style" id="email"
            placeholder="Enter your email" required>
          <div class="invalid-feedback">Please enter a valid email address</div>
        </div>

        <div class="mb-3 input-group">
          <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
          <input type="password" name="password" class="form-control input-style" id="password"
            placeholder="Enter password" required>
        </div>

        <button type="submit" class="btn btn-gold2 w-100">Login</button>
        <p class="text-center mt-3">Don't have an account? <a href="register.php">Register</a></p>
      </form>

      <script>
        // Email validation sahaja
        document.getElementById('email').addEventListener('input', function() {
          if (this.value.includes('@') && this.value.includes('.')) {
            this.classList.remove('is-invalid');
            this.classList.add('is-valid');
          } else {
            this.classList.remove('is-valid');
            this.classList.add('is-invalid');
          }
        });
      </script>

      <script>
        // Auto show modal based on session messages
        document.addEventListener('DOMContentLoaded', function() {
          <?php if (isset($_SESSION['login_error'])): ?>
            const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
            document.querySelector('#errorModal .modal-body').textContent = "<?= addslashes($_SESSION['login_error']) ?>";
            errorModal.show();
            <?php unset($_SESSION['login_error']); ?>
          <?php endif; ?>

          <?php if (!empty($successMessage)): ?>
            const successModal = new bootstrap.Modal(document.getElementById('successModal'));
            document.querySelector('#successModal .modal-body').textContent = <?= json_encode($successMessage) ?>;
            successModal.show();
          <?php endif; ?>
        });
      </script>
    </div>
  </div>

  <!-- Error Modal -->
  <div class="modal fade" id="errorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content border-danger">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title">Login Failed</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <!-- Pesan error akan muncul di sini -->
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <?php
  unset($_SESSION['success_message']);
  unset($_SESSION['login_error']);
  ?>

  <!-- Bootstrap -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>
