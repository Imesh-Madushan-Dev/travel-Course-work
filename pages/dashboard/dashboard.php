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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .dashboard-container {
            background-color: #f8f9fa;
            min-height: 100vh;
        }

        .main-content {
            padding: 1.5rem;
        }

        .welcome-section {
            background: linear-gradient(135deg, #0d6efd 0%, #0099ff 100%);
            color: white;
            border-radius: 1rem;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .welcome-section h1 {
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        .quick-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card i {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: #4CAF50;
        }

        .stat-card h3 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            color: #2c3e50;
        }

        .stat-card p {
            color: #6c757d;
            margin: 0;
        }

        .service-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .service-card {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .service-card i {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: #0d6efd;
        }

        .service-card h3 {
            font-size: 1.25rem;
            margin-bottom: 0.5rem;
            color: #2c3e50;
        }

        .service-card p {
            color: #6c757d;
            margin-bottom: 1rem;
        }

        .service-card .btn {
            width: 100%;
        }

        .quick-actions {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .quick-actions h2 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: #2c3e50;
        }

        .action-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .weather-widget {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .weather-widget h2 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: #2c3e50;
        }

        .weather-info {
            display: flex;
            align-items: center;
            justify-content: space-around;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 0.5rem;
        }

        .weather-info i {
            font-size: 2.5rem;
            color: #4CAF50;
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 1rem;
            }

            .welcome-section {
                padding: 1.5rem;
            }

            .stat-card, .service-card {
                padding: 1.25rem;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php include 'includes/sidebar.php'; ?>
        <div class="main-content">
            <div class="welcome-section">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1>Welcome back, <?php echo htmlspecialchars($_SESSION['fullname']); ?>!</h1>
                        <p class="mb-0">Plan your next adventure in beautiful Sri Lanka</p>
                    </div>
                    <div class="user-profile">
                        <i class="fas fa-user-circle fa-2x"></i>
                    </div>
                </div>
            </div>

            <div class="quick-stats">
                <div class="stat-card">
                    <i class="fas fa-calendar-check"></i>
                    <h3>Upcoming Trips</h3>
                    <p>View your scheduled adventures</p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-history"></i>
                    <h3>Recent Activities</h3>
                    <p>Track your travel history</p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-star"></i>
                    <h3>Travel Points</h3>
                    <p>Earn rewards for your journeys</p>
                </div>
            </div>

            <div class="service-cards">
                <div class="service-card">
                    <i class="fas fa-user-tie"></i>
                    <h3>Hire Guide</h3>
                    <p>Find experienced local guides for your journey</p>
                    <a href="hire-guide.php" class="btn btn-primary">Find Guides</a>
                </div>
                <div class="service-card">
                    <i class="fas fa-car"></i>
                    <h3>Book a Vehicle</h3>
                    <p>Rent comfortable vehicles for your travel</p>
                    <a href="book-vehicle.php" class="btn btn-primary">View Vehicles</a>
                </div>
                <div class="service-card">
                    <i class="fas fa-hotel"></i>
                    <h3>Book a Hotel</h3>
                    <p>Find the perfect accommodation</p>
                    <a href="book-hotel.php" class="btn btn-primary">Browse Hotels</a>
                </div>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <div class="quick-actions">
                        <h2><i class="fas fa-info-circle me-2"></i>Travel Information</h2>
                        <div class="travel-info">
                            <div class="stat-card mb-3">
                                <i class="fas fa-plane"></i>
                                <h3>Popular Destinations</h3>
                                <ul class="list-unstyled">
                                    <li>Sigiriya Rock Fortress - Cultural Triangle</li>
                                    <li>Yala National Park - Wildlife Safari</li>
                                    <li>Mirissa Beach - Coastal Paradise</li>
                                </ul>
                            </div>
                            
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="weather-widget">
                        <h2><i class="fas fa-cloud-sun me-2"></i>Weather Update</h2>
                        <div class="weather-info">
                            <div>
                                <i class="fas fa-sun"></i>
                            </div>
                            <div>
                                <h4>Colombo</h4>
                                <p class="mb-0">30°C | Sunny</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>