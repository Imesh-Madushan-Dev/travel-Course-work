<div class="sidebar">
    <div class="logo">
        <img src="includes/logo.png" alt="Travel Ceylon Logo" style="height: 120px;">
    </div>
    <div class="admin-badge">
        <i class="fas fa-user-shield"></i>
        <span>Admin Panel</span>
    </div>
    <nav class="sidebar-menu">
        <ul>
            <li>
                <a href="../admin/dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="../admin/manage-users.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'manage-users.php' ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i>
                    <span>Manage Users</span>
                </a>
            </li>
            <li>
                <a href="../admin/manage-guides.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'manage-guides.php' ? 'active' : ''; ?>">
                    <i class="fas fa-user-tie"></i>
                    <span>Manage Guides</span>
                </a>
            </li>
            <li>
                <a href="../admin/manage-vehicles.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'manage-vehicles.php' ? 'active' : ''; ?>">
                    <i class="fas fa-car"></i>
                    <span>Manage Vehicles</span>
                </a>
            </li>
            <li>
                <a href="../admin/manage-hotels.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'manage-hotels.php' ? 'active' : ''; ?>">
                    <i class="fas fa-hotel"></i>
                    <span>Manage Hotels</span>
                </a>
            </li>
            <li>
                <a href="../admin/booking-reports.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'booking-reports.php' ? 'active' : ''; ?>">
                    <i class="fas fa-chart-bar"></i>
                    <span>Booking Reports</span>
                </a>
            </li>
           
        </ul>
    </nav>
    <div class="logout">
        <a href="../../logout.php">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>
</div>