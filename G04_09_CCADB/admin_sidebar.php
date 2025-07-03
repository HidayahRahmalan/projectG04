<?php
// This variable will be set on the page that includes this file.
// If it's not set, it defaults to an empty string to avoid errors.
$active_page = $active_page ?? '';
?>
<aside class="admin-sidebar">
    <div class="sidebar-header">
        <h2>Admin Panel</h2>
    </div>
    <nav>
        <ul>
            <li><a href="admin_dashboard.php" class="<?php echo ($active_page == 'dashboard') ? 'active' : ''; ?>">Dashboard</a></li>
            <li><a href="admin_manage_appointments.php" class="<?php echo ($active_page == 'appointments') ? 'active' : ''; ?>">Manage Appointments</a></li>
            <li><a href="admin_manage_doctors.php" class="<?php echo ($active_page == 'doctors') ? 'active' : ''; ?>">Manage Doctors</a></li>
            <!-- This is the corrected line for Manage Patients -->
            <li><a href="admin_manage_patients.php" class="<?php echo ($active_page == 'patients') ? 'active' : ''; ?>">Manage Patients</a></li>
            <li><a href="#" class="<?php echo ($active_page == 'clinics') ? 'active' : ''; ?>">Manage Clinics</a></li>
        </ul>
    </nav>
    <div class="sidebar-footer">
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
</aside>