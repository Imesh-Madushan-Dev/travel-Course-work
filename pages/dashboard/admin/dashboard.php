<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['is_admin'])) {
    header("Location: ../../login.php");
    exit();
}

require_once '../../../config/db_connect.php';

// Get total counts for dashboard stats
$userCount = 0;
$guideCount = 0;
$vehicleCount = 0;
$hotelCount = 0;
$bookingCount = 0;

// Get user count
$query = "SELECT COUNT(*) as count FROM users";
$result = $conn->query($query);
if($result) {
    $row = $result->fetch_assoc();
    $userCount = $row['count'];
}

// Get guide count
$query = "SELECT COUNT(*) as count FROM guides";
$result = $conn->query($query);
if($result) {
    $row = $result->fetch_assoc();
    $guideCount = $row['count'];
}

// Get vehicle count
$query = "SELECT COUNT(*) as count FROM vehicles";
$result = $conn->query($query);
if($result) {
    $row = $result->fetch_assoc();
    $vehicleCount = $row['count'];
}

// Get hotel count
$query = "SELECT COUNT(*) as count FROM hotels";
$result = $conn->query($query);
if($result) {
    $row = $result->fetch_assoc();
    $hotelCount = $row['count'];
}

// Get total bookings count (combining all booking types)
$query = "SELECT 
    (SELECT COUNT(*) FROM guide_bookings) + 
    (SELECT COUNT(*) FROM vehicle_bookings) + 
    (SELECT COUNT(*) FROM hotel_bookings) as total_bookings";
$result = $conn->query($query);
if($result) {
    $row = $result->fetch_assoc();
    $bookingCount = $row['total_bookings'];
}

// Get recent bookings
$recentBookings = [];
$query = "SELECT 'Hotel' as type, hb.booking_id, u.fullname, h.name as item_name, hb.check_in_date as start_date, 
          hb.total_cost, hb.booking_status, hb.created_at 
          FROM hotel_bookings hb 
          JOIN users u ON hb.user_id = u.id 
          JOIN hotels h ON hb.hotel_id = h.hotel_id
          UNION
          SELECT 'Guide' as type, gb.booking_id, u.fullname, g.name as item_name, gb.tour_date as start_date,
          gb.total_cost, 'confirmed' as booking_status, gb.created_at
          FROM guide_bookings gb
          JOIN users u ON gb.user_id = u.id
          JOIN guides g ON gb.guide_id = g.guide_id
          UNION
          SELECT 'Vehicle' as type, vb.booking_id, u.fullname, CONCAT(v.type, ' ', v.model) as item_name, 
          vb.pickup_date as start_date, vb.total_cost, 'confirmed' as booking_status, vb.created_at
          FROM vehicle_bookings vb
          JOIN users u ON vb.user_id = u.id
          JOIN vehicles v ON vb.vehicle_id = v.vehicle_id
          ORDER BY created_at DESC
          LIMIT 10";

$result = $conn->query($query);
if($result) {
    while($row = $result->fetch_assoc()) {
        $recentBookings[] = $row;
    }
}

$pageTitle = "Admin Dashboard";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Travel Ceylon</title>
    <link rel="stylesheet" href="../../../css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .dashboard-container {
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
        }

        .main-content {
            flex: 1;
            padding: 1.5rem;
        }

        .admin-header {
            background-color: white;
            border-bottom: 1px solid #e0e0e0;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .search-box {
            position: relative;
            display: flex;
            align-items: center;
        }

        .search-box input {
            padding: 0.5rem 0.75rem 0.5rem 2.25rem;
            border: 1px solid #e0e0e0;
            border-radius: 0.5rem;
            width: 250px;
        }

        .search-box i {
            position: absolute;
            left: 0.75rem;
            color: #6c757d;
        }

        .notification-icon {
            position: relative;
            cursor: pointer;
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: #dc3545;
            color: white;
            font-size: 0.7rem;
            width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }

        .admin-profile {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
        }

        .admin-name {
            margin-right: 0.5rem;
        }

        .admin-badge {
            background-color: #4e73df;
            color: white;
            padding: 0.75rem 1rem;
            margin: 1rem 0;
            text-align: center;
            border-radius: 0.25rem;
            font-weight: 500;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .welcome-section {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
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
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s;
            text-align: center;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card i {
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        .stat-card.users i { color: #4e73df; }
        .stat-card.guides i { color: #1cc88a; }
        .stat-card.vehicles i { color: #36b9cc; }
        .stat-card.hotels i { color: #f6c23e; }
        .stat-card.bookings i { color: #e74a3b; }

        .stat-card h3 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: #2c3e50;
        }

        .stat-card p {
            color: #6c757d;
            margin: 0;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .card-section {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #2c3e50;
            margin: 0;
        }

        .view-all {
            color: #4e73df;
            font-weight: 500;
            font-size: 0.9rem;
            text-decoration: none;
        }

        .view-all:hover {
            text-decoration: underline;
        }

        table {
            width: 100%;
        }

        .booking-type {
            display: inline-block;
            padding: 0.35rem 0.65rem;
            font-size: 0.75rem;
            font-weight: 600;
            border-radius: 0.25rem;
            text-align: center;
        }

        .booking-type.hotel {
            background-color: #e8f4fd;
            color: #4e73df;
        }

        .booking-type.guide {
            background-color: #e0f8e9;
            color: #1cc88a;
        }

        .booking-type.vehicle {
            background-color: #e3f6f9;
            color: #36b9cc;
        }

        .status-badge {
            display: inline-block;
            padding: 0.35rem 0.65rem;
            font-size: 0.75rem;
            font-weight: 600;
            border-radius: 0.25rem;
            text-align: center;
        }

        .status-badge.confirmed {
            background-color: #e0f8e9;
            color: #1cc88a;
        }

        .status-badge.pending {
            background-color: #fff8e1;
            color: #f6c23e;
        }

        .status-badge.cancelled {
            background-color: #feeceb;
            color: #e74a3b;
        }

        .chart-container {
            height: 300px;
        }

        @media (max-width: 992px) {
            .quick-stats {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 1rem;
            }

            .welcome-section {
                padding: 1.5rem;
            }

            .header-actions {
                gap: 1rem;
            }

            .search-box input {
                width: 180px;
            }
        }

        @media (max-width: 576px) {
            .quick-stats {
                grid-template-columns: 1fr;
            }
            
            .search-box {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php include 'includes/sidebar.php'; ?>
        <div class="main-content">
            <?php include 'includes/header.php'; ?>
            
            <div class="welcome-section">
                <h1>Welcome to Admin Dashboard</h1>
                <p class="mb-0">Manage and monitor all aspects of Travel Ceylon from this centralized dashboard.</p>
            </div>

            <div class="quick-stats">
                <div class="stat-card users">
                    <i class="fas fa-users"></i>
                    <h3><?php echo $userCount; ?></h3>
                    <p>Total Users</p>
                </div>
                <div class="stat-card guides">
                    <i class="fas fa-user-tie"></i>
                    <h3><?php echo $guideCount; ?></h3>
                    <p>Tour Guides</p>
                </div>
                <div class="stat-card vehicles">
                    <i class="fas fa-car"></i>
                    <h3><?php echo $vehicleCount; ?></h3>
                    <p>Vehicles</p>
                </div>
                <div class="stat-card hotels">
                    <i class="fas fa-hotel"></i>
                    <h3><?php echo $hotelCount; ?></h3>
                    <p>Hotels</p>
                </div>
                <div class="stat-card bookings">
                    <i class="fas fa-calendar-check"></i>
                    <h3><?php echo $bookingCount; ?></h3>
                    <p>Total Bookings</p>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card-section">
                        <div class="section-header">
                            <h2 class="section-title">Recent Bookings</h2>
                            <a href="booking-reports.php" class="view-all">View All</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Customer</th>
                                        <th>Service</th>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentBookings as $booking): ?>
                                    <tr>
                                        <td>
                                            <span class="booking-type <?php echo strtolower($booking['type']); ?>">
                                                <?php echo $booking['type']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($booking['fullname']); ?></td>
                                        <td><?php echo htmlspecialchars($booking['item_name']); ?></td>
                                        <td><?php echo htmlspecialchars($booking['start_date']); ?></td>
                                        <td><?php echo number_format($booking['total_cost']); ?></td>
                                        <td>
                                            <span class="status-badge <?php echo strtolower($booking['booking_status']); ?>">
                                                <?php echo ucfirst($booking['booking_status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card-section">
                        <div class="section-header">
                            <h2 class="section-title">Booking Distribution</h2>
                        </div>
                        <div class="chart-container" id="booking-chart">
                            <!-- Chart will be rendered here -->
                        </div>
                    </div>
                    
                    <div class="card-section">
                        <div class="section-header">
                            <h2 class="section-title">Quick Actions</h2>
                        </div>
                        <div class="d-grid gap-2">
                            <a href="manage-guides.php" class="btn btn-outline-primary">
                                <i class="fas fa-user-tie me-2"></i>Add New Guide
                            </a>
                            <a href="manage-vehicles.php" class="btn btn-outline-info">
                                <i class="fas fa-car me-2"></i>Add New Vehicle
                            </a>
                            <a href="manage-hotels.php" class="btn btn-outline-warning">
                                <i class="fas fa-hotel me-2"></i>Add New Hotel
                            </a>
                            <a href="system-settings.php" class="btn btn-outline-secondary">
                                <i class="fas fa-cog me-2"></i>System Settings
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Simple chart for booking distribution
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.createElement('canvas');
            document.getElementById('booking-chart').appendChild(ctx);
            
            new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: ['Hotels', 'Guides', 'Vehicles'],
                    datasets: [{
                        data: [
                            <?php 
                                $hotelBookings = $conn->query("SELECT COUNT(*) as count FROM hotel_bookings")->fetch_assoc()['count'] ?? 0;
                                $guideBookings = $conn->query("SELECT COUNT(*) as count FROM guide_bookings")->fetch_assoc()['count'] ?? 0;
                                $vehicleBookings = $conn->query("SELECT COUNT(*) as count FROM vehicle_bookings")->fetch_assoc()['count'] ?? 0;
                                echo $hotelBookings . ", " . $guideBookings . ", " . $vehicleBookings;
                            ?>
                        ],
                        backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc'],
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>