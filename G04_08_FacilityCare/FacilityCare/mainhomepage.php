<?php
session_start();
require_once 'connection.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homepage | Maintenance System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="CSS/style.css">
</head>
<body>

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
    <div class="container">
      <a class="navbar-brand fw-bold" href="index.php">
            <i class="fas fa-tools me-2 text-primary"></i>FacilityCare
        </a>
      <div class="ms-auto">
        <a href="login.php" class="btn btn-gold2">Login</a>
        <a href="register.php" class="btn btn-gold2 btn-outline">Sign Up</a>
      </div>
    </div>
  </nav>

  <!-- Hero Section -->
  <section class="py-5 bg-light text-center">
    <div class="container">
      <h1 class="display-5 fw-bold">Effortless Facility Maintenance</h1>
      <p class="lead">Report, Track & Resolve maintenance issues in your organization - fast and efficiently.</p>
      <a href="login.php" class="btn btn-gold2 btn-lg mt-3">
        <i class="fas fa-sign-in-alt me-1"></i> Get Started
      </a>
    </div>
  </section>

  <!-- Features -->
  <section class="py-5">
    <div class="container">
      <div class="row text-center">
        <div class="col-md-4">
          <i class="fas fa-file-alt fa-3x text-primary mb-3"></i>
          <h5>Easy Reporting</h5>
          <p>Submit facility issues with simple forms and image attachments.</p>
        </div>
        <div class="col-md-4">
          <i class="fas fa-tasks fa-3x text-success mb-3"></i>
          <h5>Real-time Tracking</h5>
          <p>Track the progress and status of each report anytime, anywhere.</p>
        </div>
        <div class="col-md-4">
          <i class="fas fa-headset fa-3x text-warning mb-3"></i>
          <h5>Supportive Team</h5>
          <p>Our maintenance team is ready to take action and solve your problems.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="bg-light py-4 mt-5 border-top">
    <div class="container text-center text-muted">
      <p class="mb-0">&copy; 2025 FacilityCare. All rights reserved.</p>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  
</body>
</html>
