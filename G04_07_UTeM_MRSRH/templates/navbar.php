<?php
$current_page = basename($_SERVER['PHP_SELF']);
$user_role = $_SESSION['role'] ?? 0;
$user_name = htmlspecialchars($_SESSION['name'] ?? 'User');

$is_admin = ($user_role == 5);

// Define links for Admin
$admin_links = [
    'admin_dashboard.php' => 'Dashboard',
    'admin_all_reports.php' => 'All Reports',
    'create_report.php' => 'Create Report',
    'register.php' => 'Register User',
];

// Define links for Staff
$staff_links = [
    'staff_dashboard.php' => 'Dashboard',
    'my_tasks.php' => 'My Active Tasks',
    'my_all_reports.php' => 'My Report History',
];

// Select the correct set of links based on role
$nav_links = $is_admin ? $admin_links : $staff_links;
$home_link = $is_admin ? 'admin_dashboard.php' : 'staff_dashboard.php';
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold d-flex align-items-center" href="<?php echo $home_link; ?>">
            <img src="assets/img/utem_logo.jpg" alt="UTeM Logo" style="height: 30px; margin-right: 10px;">
            Maintenance
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <?php foreach ($nav_links as $url => $text): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == $url) ? 'active' : ''; ?>" href="<?php echo $url; ?>"><?php echo $text; ?></a>
                </li>
                <?php endforeach; ?>
            </ul>
            <div class="navbar-nav">
                 <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user-circle me-2 fs-5"></i> <?php echo $user_name; ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="profile.php"><i class="fas fa-id-card fa-fw me-2"></i>Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt fa-fw me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</nav>