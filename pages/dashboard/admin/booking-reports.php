<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['is_admin'])) {
    header("Location: ../../login.php");
    exit();
}

require_once '../../../config/db_connect.php';

// Set default filters
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
$booking_type = isset($_GET['booking_type']) ? $_GET['booking_type'] : 'all';
$status = isset($_GET['status']) ? $_GET['status'] : 'all';
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'date_desc';

// Prepare base queries for each booking type
$hotel_query_base = "SELECT 'Hotel' as type, hb.booking_id, u.fullname, u.id as user_id,
                      h.name as item_name, h.hotel_id as item_id, hb.check_in_date as start_date,
                      hb.check_out_date as end_date, hb.room_count, hb.guest_count, 
                      hb.total_cost, hb.booking_status, hb.created_at 
                      FROM hotel_bookings hb 
                      JOIN users u ON hb.user_id = u.id 
                      JOIN hotels h ON hb.hotel_id = h.hotel_id
                      WHERE hb.created_at BETWEEN ? AND ?";

$guide_query_base = "SELECT 'Guide' as type, gb.booking_id, u.fullname, u.id as user_id,
                      g.name as item_name, g.guide_id as item_id, gb.tour_date as start_date,
                      DATE_ADD(gb.tour_date, INTERVAL gb.duration_days DAY) as end_date, 
                      NULL as room_count, gb.group_size as guest_count,
                      gb.total_cost, 'confirmed' as booking_status, gb.created_at
                      FROM guide_bookings gb
                      JOIN users u ON gb.user_id = u.id
                      JOIN guides g ON gb.guide_id = g.guide_id
                      WHERE gb.created_at BETWEEN ? AND ?";

$vehicle_query_base = "SELECT 'Vehicle' as type, vb.booking_id, u.fullname, u.id as user_id,
                        CONCAT(v.type, ' ', v.model) as item_name, v.vehicle_id as item_id, 
                        vb.pickup_date as start_date, vb.return_date as end_date,
                        NULL as room_count, NULL as guest_count,
                        vb.total_cost, 'confirmed' as booking_status, vb.created_at
                        FROM vehicle_bookings vb
                        JOIN users u ON vb.user_id = u.id
                        JOIN vehicles v ON vb.vehicle_id = v.vehicle_id
                        WHERE vb.created_at BETWEEN ? AND ?";

// Apply additional filters
if ($booking_type != 'all') {
    if ($booking_type == 'hotel' && $status != 'all') {
        $hotel_query_base .= " AND hb.booking_status = ?";
    } else if ($booking_type == 'hotel') {
        // Only hotel filter, no status
    } else if ($booking_type == 'guide') {
        // Only guide, no status filter needed
    } else if ($booking_type == 'vehicle') {
        // Only vehicle, no status filter needed
    }
}

// Prepare the final query with UNION
$params = [];
$types = "";

if ($booking_type == 'all' || $booking_type == 'hotel') {
    $query_parts[] = $hotel_query_base;
    $params[] = $start_date;
    $params[] = $end_date;
    $types .= "ss";
    
    if ($booking_type == 'hotel' && $status != 'all') {
        $params[] = $status;
        $types .= "s";
    }
}

if ($booking_type == 'all' || $booking_type == 'guide') {
    $query_parts[] = $guide_query_base;
    $params[] = $start_date;
    $params[] = $end_date;
    $types .= "ss";
}

if ($booking_type == 'all' || $booking_type == 'vehicle') {
    $query_parts[] = $vehicle_query_base;
    $params[] = $start_date;
    $params[] = $end_date;
    $types .= "ss";
}

// Apply sorting
$order_by = "";
switch ($sort_by) {
    case 'date_asc':
        $order_by = "ORDER BY created_at ASC";
        break;
    case 'date_desc':
        $order_by = "ORDER BY created_at DESC";
        break;
    case 'price_asc':
        $order_by = "ORDER BY total_cost ASC";
        break;
    case 'price_desc':
        $order_by = "ORDER BY total_cost DESC";
        break;
    case 'name_asc':
        $order_by = "ORDER BY fullname ASC";
        break;
    case 'name_desc':
        $order_by = "ORDER BY fullname DESC";
        break;
    default:
        $order_by = "ORDER BY created_at DESC";
}

$query = implode(" UNION ", $query_parts) . " " . $order_by;

// Fetch bookings
$bookings = [];
$stmt = $conn->prepare($query);
if ($stmt) {
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $bookings[] = $row;
    }
    
    $stmt->close();
}

// Calculate statistics
$total_revenue = 0;
$booking_counts = [
    'total' => count($bookings),
    'hotel' => 0,
    'guide' => 0,
    'vehicle' => 0,
    'pending' => 0,
    'confirmed' => 0,
    'cancelled' => 0
];

$monthly_revenue = [];
$daily_bookings = [];

foreach ($bookings as $booking) {
    $total_revenue += $booking['total_cost'];
    
    // Count by type
    $booking_type_key = strtolower($booking['type']);
    $booking_counts[$booking_type_key]++;
    
    // Count by status
    $status_key = strtolower($booking['booking_status']);
    if (isset($booking_counts[$status_key])) {
        $booking_counts[$status_key]++;
    }
    
    // Group by month for revenue chart
    $month = date('M Y', strtotime($booking['created_at']));
    if (!isset($monthly_revenue[$month])) {
        $monthly_revenue[$month] = 0;
    }
    $monthly_revenue[$month] += $booking['total_cost'];
    
    // Group by day for booking count chart
    $day = date('Y-m-d', strtotime($booking['created_at']));
    if (!isset($daily_bookings[$day])) {
        $daily_bookings[$day] = 0;
    }
    $daily_bookings[$day]++;
}

// Sort monthly revenue by date
uksort($monthly_revenue, function($a, $b) {
    return strtotime($a) - strtotime($b);
});

// Sort daily bookings by date
uksort($daily_bookings, function($a, $b) {
    return strtotime($a) - strtotime($b);
});

// Get last 6 months for chart
$monthly_revenue = array_slice($monthly_revenue, -6, 6, true);

// Format data for charts
$revenue_labels = json_encode(array_keys($monthly_revenue));
$revenue_data = json_encode(array_values($monthly_revenue));

$daily_booking_labels = json_encode(array_keys($daily_bookings));
$daily_booking_data = json_encode(array_values($daily_bookings));

// Type distribution for pie chart
$type_distribution = [
    'Hotel' => $booking_counts['hotel'],
    'Guide' => $booking_counts['guide'],
    'Vehicle' => $booking_counts['vehicle']
];

$type_labels = json_encode(array_keys($type_distribution));
$type_data = json_encode(array_values($type_distribution));

// Status distribution for pie chart
$status_distribution = [
    'Confirmed' => $booking_counts['confirmed'],
    'Pending' => $booking_counts['pending'],
    'Cancelled' => $booking_counts['cancelled']
];

$status_labels = json_encode(array_keys($status_distribution));
$status_data = json_encode(array_values($status_distribution));

$pageTitle = "Booking Reports";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Reports - Travel Ceylon Admin</title>
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
        
        .filter-row {
            background-color: #f8f9fa;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .stat-card {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s;
            text-align: center;
            height: 100%;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card i {
            font-size: 2rem;
            margin-bottom: 1rem;
        }
        
        .stat-card.total i { color: #4e73df; }
        .stat-card.revenue i { color: #1cc88a; }
        .stat-card.hotel i { color: #36b9cc; }
        .stat-card.guide i { color: #f6c23e; }
        .stat-card.vehicle i { color: #e74a3b; }
        .stat-card.pending i { color: #f6c23e; }
        .stat-card.confirmed i { color: #1cc88a; }
        .stat-card.cancelled i { color: #e74a3b; }

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
        
        .chart-container {
            position: relative;
            height: 300px;
            margin-bottom: 2rem;
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
        
        .export-btn {
            margin-left: 1rem;
        }
        
        .pagination {
            justify-content: center;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php include 'includes/sidebar.php'; ?>
        <div class="main-content">
            <?php include 'includes/header.php'; ?>
            
            <div class="card-section">
                <div class="section-header">
                    <h2 class="section-title">Booking Reports & Analytics</h2>
                  
                </div>
                
                <div class="filter-row">
                    <form method="GET" action="" id="filterForm">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="start_date" class="form-label">Start Date</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $start_date; ?>">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="end_date" class="form-label">End Date</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $end_date; ?>">
                            </div>
                            <div class="col-md-2 mb-3">
                                <label for="booking_type" class="form-label">Booking Type</label>
                                <select class="form-select" id="booking_type" name="booking_type">
                                    <option value="all" <?php echo $booking_type == 'all' ? 'selected' : ''; ?>>All Types</option>
                                    <option value="hotel" <?php echo $booking_type == 'hotel' ? 'selected' : ''; ?>>Hotels</option>
                                    <option value="guide" <?php echo $booking_type == 'guide' ? 'selected' : ''; ?>>Guides</option>
                                    <option value="vehicle" <?php echo $booking_type == 'vehicle' ? 'selected' : ''; ?>>Vehicles</option>
                                </select>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="all" <?php echo $status == 'all' ? 'selected' : ''; ?>>All Statuses</option>
                                    <option value="pending" <?php echo $status == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="confirmed" <?php echo $status == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                    <option value="cancelled" <?php echo $status == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label for="sort_by" class="form-label">Sort By</label>
                                <select class="form-select" id="sort_by" name="sort_by">
                                    <option value="date_desc" <?php echo $sort_by == 'date_desc' ? 'selected' : ''; ?>>Date (Newest)</option>
                                    <option value="date_asc" <?php echo $sort_by == 'date_asc' ? 'selected' : ''; ?>>Date (Oldest)</option>
                                    <option value="price_desc" <?php echo $sort_by == 'price_desc' ? 'selected' : ''; ?>>Price (High-Low)</option>
                                    <option value="price_asc" <?php echo $sort_by == 'price_asc' ? 'selected' : ''; ?>>Price (Low-High)</option>
                                    <option value="name_asc" <?php echo $sort_by == 'name_asc' ? 'selected' : ''; ?>>Customer Name (A-Z)</option>
                                    <option value="name_desc" <?php echo $sort_by == 'name_desc' ? 'selected' : ''; ?>>Customer Name (Z-A)</option>
                                </select>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter me-2"></i> Apply Filters
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Statistics Summary -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="stat-card total">
                            <i class="fas fa-calendar-check"></i>
                            <h3><?php echo $booking_counts['total']; ?></h3>
                            <p>Total Bookings</p>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="stat-card revenue">
                            <i class="fas fa-rupee-sign"></i>
                            <h3><?php echo number_format($total_revenue); ?></h3>
                            <p>Total Revenue</p>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="stat-card confirmed">
                            <i class="fas fa-check-circle"></i>
                            <h3><?php echo $booking_counts['confirmed']; ?></h3>
                            <p>Confirmed Bookings</p>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="stat-card pending">
                            <i class="fas fa-clock"></i>
                            <h3><?php echo $booking_counts['pending']; ?></h3>
                            <p>Pending Bookings</p>
                        </div>
                    </div>
                </div>
                
                <!-- Charts -->
                <div class="row mb-4">
                    <div class="col-lg-8 mb-4">
                        <div class="card-section h-100">
                            <h4 class="mb-3">Monthly Revenue</h4>
                            <div class="chart-container">
                                <canvas id="revenueChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 mb-4">
                        <div class="card-section h-100">
                            <h4 class="mb-3">Booking Distribution</h4>
                            <div class="chart-container">
                                <canvas id="bookingTypeChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mb-4">
                    <div class="col-lg-8 mb-4">
                        <div class="card-section h-100">
                            <h4 class="mb-3">Daily Bookings</h4>
                            <div class="chart-container">
                                <canvas id="dailyBookingsChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 mb-4">
                        <div class="card-section h-100">
                            <h4 class="mb-3">Booking Status</h4>
                            <div class="chart-container">
                                <canvas id="statusChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Bookings Table -->
                <div class="card-section">
                    <h4 class="mb-3">Booking Details</h4>
                    <div class="table-responsive">
                        <table class="table table-hover" id="bookingsTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Type</th>
                                    <th>Customer</th>
                                    <th>Service</th>
                                    <th>Date Range</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Booked On</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bookings as $booking): ?>
                                <tr>
                                    <td><?php echo $booking['booking_id']; ?></td>
                                    <td>
                                        <span class="booking-type <?php echo strtolower($booking['type']); ?>">
                                            <?php echo $booking['type']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($booking['fullname']); ?></td>
                                    <td><?php echo htmlspecialchars($booking['item_name']); ?></td>
                                    <td>
                                        <?php echo date('M d, Y', strtotime($booking['start_date'])); ?> -
                                        <?php echo date('M d, Y', strtotime($booking['end_date'])); ?>
                                    </td>
                                    <td><?php echo number_format($booking['total_cost']); ?></td>
                                    <td>
                                        <span class="status-badge <?php echo strtolower($booking['booking_status']); ?>">
                                            <?php echo ucfirst($booking['booking_status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y H:i', strtotime($booking['created_at'])); ?></td>
                                    <td>
                                        <div class="d-flex">
                                            <button class="btn btn-sm btn-outline-primary me-1" 
                                                    onclick="viewBookingDetails(<?php echo $booking['booking_id']; ?>, '<?php echo strtolower($booking['type']); ?>')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <?php if ($booking['booking_status'] == 'pending'): ?>
                                            <button class="btn btn-sm btn-outline-success me-1" 
                                                    onclick="updateBookingStatus(<?php echo $booking['booking_id']; ?>, 'confirmed')">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" 
                                                    onclick="updateBookingStatus(<?php echo $booking['booking_id']; ?>, 'cancelled')">
                                                <i class="fas fa-times"></i>
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if (empty($bookings)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No bookings found for the selected filters</p>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Pagination -->
                    <nav aria-label="Page navigation" class="mt-4">
                        <ul class="pagination">
                            <li class="page-item disabled">
                                <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Previous</a>
                            </li>
                            <li class="page-item active"><a class="page-link" href="#">1</a></li>
                            <li class="page-item"><a class="page-link" href="#">2</a></li>
                            <li class="page-item"><a class="page-link" href="#">3</a></li>
                            <li class="page-item">
                                <a class="page-link" href="#">Next</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Initialize charts
        document.addEventListener('DOMContentLoaded', function () {
            // Revenue chart
            const revenueCtx = document.getElementById('revenueChart').getContext('2d');
            new Chart(revenueCtx, {
                type: 'bar',
                data: {
                    labels: <?php echo $revenue_labels; ?>,
                    datasets: [{
                        label: 'Revenue ()',
                        data: <?php echo $revenue_data; ?>,
                        backgroundColor: '#4e73df',
                        borderColor: '#4e73df',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '' + value.toLocaleString();
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return '' + context.raw.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
            
            // Booking type distribution chart
            const typeCtx = document.getElementById('bookingTypeChart').getContext('2d');
            new Chart(typeCtx, {
                type: 'pie',
                data: {
                    labels: <?php echo $type_labels; ?>,
                    datasets: [{
                        data: <?php echo $type_data; ?>,
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
            
            // Daily bookings chart
            const dailyCtx = document.getElementById('dailyBookingsChart').getContext('2d');
            new Chart(dailyCtx, {
                type: 'line',
                data: {
                    labels: <?php echo $daily_booking_labels; ?>,
                    datasets: [{
                        label: 'Number of Bookings',
                        data: <?php echo $daily_booking_data; ?>,
                        fill: false,
                        borderColor: '#1cc88a',
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
            
            // Status chart
            const statusCtx = document.getElementById('statusChart').getContext('2d');
            new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: <?php echo $status_labels; ?>,
                    datasets: [{
                        data: <?php echo $status_data; ?>,
                        backgroundColor: ['#1cc88a', '#f6c23e', '#e74a3b'],
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
            
            // Update status options based on booking type
            document.getElementById('booking_type').addEventListener('change', function() {
                const bookingType = this.value;
                const statusSelect = document.getElementById('status');
                
                if (bookingType !== 'hotel' && bookingType !== 'all') {
                    statusSelect.value = 'all';
                    statusSelect.disabled = true;
                } else {
                    statusSelect.disabled = false;
                }
            });
            
            // Initialize on page load
            const bookingType = document.getElementById('booking_type').value;
            if (bookingType !== 'hotel' && bookingType !== 'all') {
                document.getElementById('status').disabled = true;
            }
        });
        
        // Function to view booking details
        function viewBookingDetails(bookingId, type) {
            // Redirect to booking details page
            window.location.href = `booking-details.php?id=${bookingId}&type=${type}`;
        }
        
        // Function to update booking status
        function updateBookingStatus(bookingId, status) {
            if (confirm(`Are you sure you want to mark this booking as ${status}?`)) {
                // Send AJAX request to update status
                // For demonstration purposes, we'll just reload the page
                window.location.href = `update-booking-status.php?id=${bookingId}&status=${status}&redirect=booking-reports.php`;
            }
        }
        
        // Export to Excel function
        function exportToExcel() {
            alert("Exporting data to Excel... This functionality would be implemented with a server-side script.");
            // Implement actual Excel export functionality
        }
        
        // Export to PDF function
        function exportToPDF() {
            alert("Exporting data to PDF... This functionality would be implemented with a server-side script.");
            // Implement actual PDF export functionality
        }
    </script>
</body>
</html>