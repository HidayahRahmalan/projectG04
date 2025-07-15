<?php
session_name("staff_session");
session_start();
require_once 'connection.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: homepage.php");
  exit();
}

// Get user data from database
$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM User WHERE user_id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $name = trim($_POST['name']);
  $email = trim($_POST['email']);
  $phone = trim($_POST['phone']);
  $password = trim($_POST['password']);

  // Basic validation
  if (empty($name) || empty($email)) {
    $_SESSION['profile_error'] = "Name and email are required";
  } else {
    try {
      // Handle file upload
      $profilePic = $user['profile_pic']; // Keep existing if no new upload

      if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == UPLOAD_ERR_OK) {
        $uploadDir = 'images/staff_profile/';
        if (!file_exists($uploadDir)) {
          mkdir($uploadDir, 0777, true);
        }

        // Generate unique filename
        $fileExt = pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);
        $filename = 'user_' . $userId . '_' . time() . '.' . strtolower($fileExt);
        $targetFile = $uploadDir . $filename;

        // Check image type
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array(strtolower($fileExt), $allowedTypes)) {
          if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $targetFile)) {
            // Delete old profile pic if exists
            if (!empty($user['profile_pic']) && file_exists($user['profile_pic'])) {
              unlink($user['profile_pic']);
            }
            $profilePic = $targetFile;
          }
        } else {
          $_SESSION['profile_error'] = "Only JPG, JPEG, PNG & GIF files are allowed";
        }
      }

      // Update user data
      if (!empty($password)) {
        if (strlen($password) < 8) {
          $_SESSION['password_error'] = "Password must be at least 8 characters";
          header("Location: profileTech.php");
          exit();
        }
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE User SET name = ?, email = ?, phone = ?, password = ?, profile_pic = ? WHERE user_id = ?");
        $stmt->execute([$name, $email, $phone, $hashedPassword, $profilePic, $userId]);
      } else {
        $stmt = $pdo->prepare("UPDATE User SET name = ?, email = ?, phone = ?, profile_pic = ? WHERE user_id = ?");
        $stmt->execute([$name, $email, $phone, $profilePic, $userId]);
      }

      $_SESSION['profile_success'] = "Profile updated successfully";
      $_SESSION['name'] = $name;
      $_SESSION['email'] = $email;
      $_SESSION['profile_pic'] = $profilePic;

      // Refresh user data
      $stmt = $pdo->prepare("SELECT * FROM User WHERE user_id = ?");
      $stmt->execute([$userId]);
      $user = $stmt->fetch();
    } catch (PDOException $e) {
      $_SESSION['profile_error'] = "Database error: " . $e->getMessage();
    }
  }
}
?>

<!-- profile.html -->
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Report Details | Maintenance System</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="CSS/style.css">
  <style>
    .report-header {
      background-color: #f8f9fa;
      border-bottom: 1px solid #dee2e6;
      padding: 1rem;
      border-radius: 0.375rem 0.375rem 0 0;
    }

    .status-badge {
      font-size: 0.9rem;
      padding: 0.5rem 0.8rem;
    }

    .detail-item {
      margin-bottom: 1rem;
      padding-bottom: 1rem;
      border-bottom: 1px solid #eee;
    }

    .detail-item:last-child {
      border-bottom: none;
      margin-bottom: 0;
      padding-bottom: 0;
    }

    .update-log {
      border-left: 3px solid #95AEF1;
      padding-left: 1rem;
      margin-bottom: 1.5rem;
    }

    .update-log:last-child {
      margin-bottom: 0;
    }

    .img-thumbnail {
      transition: transform 0.2s;
      cursor: pointer;
    }

    .img-thumbnail:hover {
      transform: scale(1.02);
    }

    .media-preview {
      width: 100%;
      height: 250px;
      object-fit: cover;
      cursor: pointer;
    }
  </style>
</head>

<body>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
    <div class="container">
      <a class="navbar-brand fw-bold" href="homepageStaff.php">
        <i class="fas fa-tools me-2 text-primary"></i>FacilityCare
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item">
            <a class="nav-link" href="homepageStaff.php"><i class="fas fa-home me-1"></i> Home</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="newReport.php"><i class="fas fa-plus-circle me-1"></i> New Report</a>
          </li>
          <li class="nav-item">
            <a class="nav-link active" href="reportListings.php"><i class="fas fa-list me-1"></i> My Reports</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="developers.php"><i class="fas fa-user-cog me-1"></i> About Us</a>
          </li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
              <i class="fas fa-user-circle me-1"></i>
              <?php echo isset($_SESSION['name']) ? htmlspecialchars($_SESSION['name']) : 'Staff User'; ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i>My Profile</a></li>
              <li>
                <hr class="dropdown-divider">
              </li>
              <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
            </ul>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <section class="py-5">
    <div class="container">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-user-circle text-primary me-2"></i>My Profile</h2>
        <a href="homepage.php" class="btn btn-outline-secondary">
          <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
        </a>
      </div>

      <!-- Display success/error messages -->
      <?php if (isset($_SESSION['profile_success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <?= $_SESSION['profile_success'] ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['profile_success']); ?>
      <?php endif; ?>

      <?php if (isset($_SESSION['profile_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <?= $_SESSION['profile_error'] ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['profile_error']); ?>
      <?php endif; ?>

      <div class="card shadow-sm">
        <div class="card-body">
          <form method="POST" action="profile.php" enctype="multipart/form-data">
            <div class="row">
              <!-- Profile Picture Sidebar -->
              <div class="col-md-3 text-center profile-sidebar">
                <div class="profile-pic-container">
                  <img src="<?= !empty($user['profile_pic']) ? $user['profile_pic'] : 'https://ui-avatars.com/api/?name=' . urlencode($user['name']) . '&size=150&background=random' ?>"
                    class="profile-pic" id="profile-pic-preview">

                  <label class="profile-pic-upload" title="Change profile picture">
                    <i class="fas fa-camera"></i>
                    <input type="file" name="profile_pic" id="profile-pic-input" accept="image/*">
                  </label>
                </div>

                <h5 class="mb-1"><?= htmlspecialchars($user['name']) ?></h5>
                <p class="text-muted small mb-2">
                  <?= match (strtoupper(substr($user['user_id'], 0, 1))) {
                    'A' => 'Administrator',
                    'S' => 'Staff',
                    'T' => 'Technician',
                    default => 'User'
                  } ?>
                </p>
                <p class="text-muted small">ID: <?= htmlspecialchars($user['user_id']) ?></p>
              </div>

              <!-- Profile Details -->
              <div class="col-md-9">
                <div class="row">
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label for="name" class="form-label"><i class="fas fa-user text-muted me-2"></i>Full Name</label>
                      <input type="text" class="form-control" id="name" name="name"
                        value="<?= htmlspecialchars($user['name']) ?>" required>
                    </div>

                    <div class="mb-3">
                      <label for="email" class="form-label"><i class="fas fa-envelope text-muted me-2"></i>Email Address</label>
                      <input type="email" class="form-control" id="email" name="email"
                        value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div class="mb-3">
                      <label for="phone" class="form-label"><i class="fas fa-phone text-muted me-2"></i>Phone Number</label>
                      <input type="text" class="form-control" id="phone" name="phone"
                        value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                      <label for="password" class="form-label"><i class="fas fa-lock me-2"></i>Change Password</label>
                      <input type="password" class="form-control" id="password" name="password"
                        placeholder="Leave blank to keep current password"
                        minlength="8" pattern=".{8,}">
                      <small class="text-muted">Minimum 8 characters</small>
                      <small class="text-danger d-block">
                        <?= $_SESSION['password_error'] ?? '' ?>
                      </small>
                    </div>
                  </div>
                </div>

                <div class="d-flex justify-content-end mt-3">
                  <button type="submit" class="btn btn-success px-4">
                    <i class="fas fa-save me-1"></i>Save Changes
                  </button>
                </div>
              </div>
            </div>
          </form>
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

  <script>
    // Preview profile picture before upload
    document.getElementById('profile-pic-input').addEventListener('change', function(e) {
      const file = e.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
          document.getElementById('profile-pic-preview').src = event.target.result;
        }
        reader.readAsDataURL(file);
      }
    });

    document.getElementById('password').addEventListener('input', function(e) {
      if (e.target.value.length > 0 && e.target.value.length < 8) {
        e.target.setCustomValidity('Password must be at least 8 characters');
      } else {
        e.target.setCustomValidity('');
      }
    });
  </script>

</body>

</html>