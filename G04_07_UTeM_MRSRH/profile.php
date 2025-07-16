<?php
$page_title = "My Profile";
include 'templates/header.php';

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT u.Name, u.Email, u.Phone, r.Role_Name 
    FROM users u
    JOIN roles r ON u.Role = r.Role_ID
    WHERE u.User_ID = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>
<h1 class="h2 mb-4">My Profile</h1>
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-7">
        <div class="card shadow-sm">
            <div class="card-header d-flex align-items-center bg-light">
                <i class="fas fa-user-circle fa-2x text-primary me-3"></i>
                <div>
                    <h5 class="mb-0"><?= htmlspecialchars($user['Name']) ?></h5>
                    <small class="text-muted"><?= htmlspecialchars($user['Role_Name']) ?></small>
                </div>
            </div>
            <div class="card-body p-4">
                <dl class="row">
                    <dt class="col-sm-3">User ID:</dt>
                    <dd class="col-sm-9"><?= $user_id ?></dd>

                    <dt class="col-sm-3">Email:</dt>
                    <dd class="col-sm-9"><?= htmlspecialchars($user['Email']) ?></dd>
                    
                    <dt class="col-sm-3">Phone:</dt>
                    <dd class="col-sm-9"><?= htmlspecialchars($user['Phone'] ?? 'Not provided') ?></dd>
                </dl>
            </div>
            <div class="card-footer text-end">
                <button class="btn btn-outline-secondary" disabled>Edit Profile (Coming Soon)</button>
            </div>
        </div>
    </div>
</div>
<?php include 'templates/footer.php'; ?>