
<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Travel Ceylon</title>
    <link rel="stylesheet" href="../../css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
        <?php include 'includes/sidebar.php'; ?>
        <div class="main-content">
            <header>
                <h1>Welcome to Travel Ceylon</h1>
                <div class="user-profile">
                    <i class="fas fa-user-circle"></i>
                    <span><?php echo htmlspecialchars($_SESSION['fullname']); ?></span>
                </div>
            </header>
            <div class="dashboard-cards">
                <div class="card">
                    <i class="fas fa-user-tie"></i>
                    <h3>Hire Guide</h3>
                    <p>Find experienced local guides</p>
                </div>
                <div class="card">
                    <i class="fas fa-car"></i>
                    <h3>Book a Vehicle</h3>
                    <p>Rent vehicles for your journey</p>
                </div>
                <div class="card">
                    <i class="fas fa-hotel"></i>
                    <h3>Book a Hotel</h3>
                    <p>Find comfortable accommodations</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>