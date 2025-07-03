<?php
// This variable will be set on each page before including the sidebar.
$active_page = $active_page ?? '';
?>
<aside class="admin-sidebar"> <!-- We can reuse the admin sidebar style -->
    <div class="sidebar-header">
        <h2>Doctor Portal</h2>
    </div>
    <nav>
        <ul>
            <li><a href="doctor_dashboard.php" class="<?php echo ($active_page == 'dashboard') ? 'active' : ''; ?>">Dashboard</a></li>
            <li><a href="doctor_schedule.php" class="<?php echo ($active_page == 'schedule') ? 'active' : ''; ?>">My Schedule</a></li>
            <li><a href="doctor_my_patients.php" class="<?php echo ($active_page == 'patients') ? 'active' : ''; ?>">My Patients</a></li>
            <li><a href="#" class="<?php echo ($active_page == 'profile') ? 'active' : ''; ?>">Profile Settings</a></li>
        </ul>
    </nav>
    <div class="sidebar-footer">
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
</aside>