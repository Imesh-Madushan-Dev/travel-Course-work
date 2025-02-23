<div class="sidebar">
    <div class="logo">
        <img src="../../images/logo.png" alt="Travel Ceylon Logo">
    </div>
    <nav class="sidebar-menu">
        <ul>
            <li>
                <a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="hire-guide.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'hire-guide.php' ? 'active' : ''; ?>">
                    <i class="fas fa-user-tie"></i>
                    <span>Hire Guide</span>
                </a>
            </li>
            <li>
                <a href="book-vehicle.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'book-vehicle.php' ? 'active' : ''; ?>">
                    <i class="fas fa-car"></i>
                    <span>Book a Vehicle</span>
                </a>
            </li>
            <li>
                <a href="book-hotel.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'book-hotel.php' ? 'active' : ''; ?>">
                    <i class="fas fa-hotel"></i>
                    <span>Book a Hotel</span>
                </a>
            </li>
            <li>
                <a href="booking-history.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'booking-history.php' ? 'active' : ''; ?>">
                    <i class="fas fa-history"></i>
                    <span>History</span>
                </a>
            </li>
        </ul>
    </nav>
    <div class="logout">
        <a href="../logout.php">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>
</div>