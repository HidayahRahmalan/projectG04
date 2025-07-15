<?php
session_name("admin_session");
session_start();
require_once '../connection.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || substr($_SESSION['user_id'], 0, 1) !== 'A') {
    header("Location: ../login.php");
    exit();
}

// Get admin data
$adminId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM user WHERE user_id = ?");
$stmt->execute([$adminId]);
$admin = $stmt->fetch();

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
            $profilePhoto = $admin['profile_pic']; // Keep existing if no new upload

            if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == UPLOAD_ERR_OK) {
                $uploadDir = '../images/admin_profile/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                // Generate unique filename
                $fileExt = pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);
                $filename = $adminId . '_' . time() . '.' . strtolower($fileExt);
                $targetFile = $uploadDir . $filename;

                // Check image type
                $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
                $fileExt = strtolower($fileExt);
                if (in_array($fileExt, $allowedTypes)) {
                    if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $targetFile)) {
                        // Delete old profile pic if exists
                        if (!empty($admin['profile_pic']) && file_exists('../' . $admin['profile_pic'])) {
                            unlink('../' . $admin['profile_pic']);
                        }
                        $profilePhoto = 'images/admin_profile/' . $filename;
                    }
                } else {
                    $_SESSION['profile_error'] = "Only JPG, JPEG, PNG & GIF files are allowed";
                }
            }

            // Update admin data
            if (!empty($password)) {
                if (strlen($password) < 8) {
                    $_SESSION['password_error'] = "Password must be at least 8 characters";
                    header("Location: profileAdmin.php");
                    exit();
                }
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE user SET name = ?, email = ?, phone = ?, password = ?, profile_pic = ? WHERE user_id = ?");
                $stmt->execute([$name, $email, $phone, $hashedPassword, $profilePhoto, $adminId]);
            } else {
                $stmt = $pdo->prepare("UPDATE user SET name = ?, email = ?, phone = ?, profile_pic = ? WHERE user_id = ?");
                $stmt->execute([$name, $email, $phone, $profilePhoto, $adminId]);
            }

            $_SESSION['profile_success'] = "Profile updated successfully";
            $_SESSION['name'] = $name;
            $_SESSION['email'] = $email;
            $_SESSION['profile_pic'] = $profilePhoto;

            // Refresh admin data
            $stmt = $pdo->prepare("SELECT * FROM user WHERE user_id = ?");
            $stmt->execute([$adminId]);
            $admin = $stmt->fetch();

            header("Location: profileAdmin.php");
            exit();
        } catch (PDOException $e) {
            $_SESSION['profile_error'] = "Database error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile | Admin Panel</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../CSS/styleAdmin.css">
    <style>
        .profile-pic-container {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            overflow: hidden;
            border: 3px solid #f0f0f0;
            margin: 0 auto 20px;
            position: relative;
        }

        .profile-pic {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-pic-upload {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background: #0d6efd;
            color: white;
            border-radius: 50%;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border: 2px solid white;
        }

        .profile-pic-upload i {
            font-size: 16px;
        }

        .profile-pic-upload input {
            display: none;
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Admin Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block admin-sidebar p-0">
                <div class="d-flex flex-column p-3">
                    <div class="text-center mb-4">
                        <h4 class="text-white mb-0">
                            <i class="fas fa-tools me-2"></i>FacilityCare
                        </h4>
                        <small class="text-white-50">Admin Panel</small>
                    </div>
                    <hr class="text-white-50">
                    <ul class="nav nav-pills flex-column mb-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="allReports.php">
                                <i class="bi bi-list-check"></i> All Reports
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="archived.php">
                                <i class="bi bi-archive"></i> Archived
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="allTechnician.php">
                                <i class="fas fa-users me-2"></i> Technician Management
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="allStaff.php">
                                <i class="fas fa-user-tie me-2"></i> Staff Management
                            </a>
                        </li>
                    </ul>
                    <hr class="text-white-50">
                    <div class="dropdown">
                        <a href="#" class="d-flex justify-content-center align-items-center text-white text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                            <strong> <?php echo isset($_SESSION['name']) ? htmlspecialchars($_SESSION['name']) : 'Staff User'; ?></strong>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark text-small shadow">
                            <li><a class="dropdown-item" href="adminProfile.php"><i class="bi bi-person me-2"></i>Profile</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="../logout.php"><i class="bi bi-box-arrow-left me-2"></i>Sign out</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="fas fa-user-circle me-2"></i>Admin Profile</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="dashboard.php" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
                        </a>
                    </div>
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
                        <form method="POST" action="adminProfile.php" enctype="multipart/form-data">
                            <div class="row">
                                <!-- Profile Picture and Basic Info -->
                                <div class="col-md-4 text-center">
                                    <div class="profile-pic-container">
                                        <img src="<?= !empty($admin['profile_pic']) ? '../' . $admin['profile_pic'] : 'https://ui-avatars.com/api/?name=' . urlencode($admin['name']) . '&size=150&background=random' ?>"
                                            class="profile-pic" id="profile-pic-preview">
                                        <label class="profile-pic-upload" title="Change profile picture">
                                            <i class="fas fa-camera"></i>
                                            <input type="file" name="profile_pic" id="profile-pic-input" accept="image/*">
                                        </label>
                                    </div>
                                    <h4 class="mb-1"><?= htmlspecialchars($admin['name']) ?></h4>
                                    <p class="text-muted mb-1">
                                        <span class="badge bg-danger">Administrator</span>
                                    </p>
                                    <p class="text-muted small">
                                        ID: <?= htmlspecialchars($admin['user_id']) ?>
                                    </p>
                                </div>

                                <!-- Profile Details -->
                                <div class="col-md-8">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="name" class="form-label"><i class="fas fa-user me-2"></i>Full Name</label>
                                                <input type="text" class="form-control" id="name" name="name"
                                                    value="<?= htmlspecialchars($admin['name']) ?>" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="email" class="form-label"><i class="fas fa-envelope me-2"></i>Email Address</label>
                                                <input type="email" class="form-control" id="email" name="email"
                                                    value="<?= htmlspecialchars($admin['email']) ?>" required>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="phone" class="form-label"><i class="fas fa-phone me-2"></i>Phone Number</label>
                                                <input type="text" class="form-control" id="phone" name="phone"
                                                    value="<?= htmlspecialchars($admin['phone'] ?? '') ?>">
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
                                    
                                    <div class="d-flex justify-content-end mt-4">
                                        <button type="submit" class="btn btn-success px-4">
                                            <i class="fas fa-save me-1"></i> Save Changes
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

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
    </script>
</body>
</html>