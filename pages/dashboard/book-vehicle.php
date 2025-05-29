<?php
require_once 'includes/session_helper.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
require_once '../../config/db_connect.php';
require_once 'includes/booking_helper.php';
$page_title = "Book Vehicle";
include 'includes/header.php';
?>
<body>
    <div class="dashboard-container">
        <?php include 'includes/sidebar.php'; ?>
        <div class="main-content">
            <header>
                <h1>Book a Vehicle</h1>
                <div class="user-profile">
                    <i class="fas fa-user-circle"></i>
                    <span><?php echo htmlspecialchars($_SESSION['fullname']); ?></span>
                </div>
            </header>
            <div class="filter-section mb-4">
                <div class="row">
                    <div class="col-md-3">
                        <select id="districtFilter" class="form-select" onchange="filterVehicles()">
                            <option value="">All Districts</option>
                            <option value="Colombo">Colombo</option>
                            <option value="Kandy">Kandy</option>
                            <option value="Galle">Galle</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select id="typeFilter" class="form-select" onchange="filterVehicles()">
                            <option value="">All Types</option>
                            <option value="Car">Car</option>
                            <option value="Van">Van</option>
                            <option value="SUV">SUV</option>
                            <option value="Mini Bus">Mini Bus</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="vehicles-container">
                <?php
                // Get current date for availability check
                $current_date = date('Y-m-d');
                
                // Query to get vehicles and check their availability
                $sql = "SELECT v.*, 
                        CASE WHEN vb.booking_count > 0 THEN 0 ELSE 1 END as is_available 
                        FROM vehicles v 
                        LEFT JOIN (
                            SELECT vehicle_id, COUNT(*) as booking_count 
                            FROM vehicle_bookings 
                            WHERE return_date >= ? 
                            GROUP BY vehicle_id
                        ) vb ON v.vehicle_id = vb.vehicle_id 
                        WHERE v.ac_available = 1 
                        ORDER BY v.daily_rate ASC";
                
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $current_date);
                $stmt->execute();
                $result = $stmt->get_result();
                
                while($vehicle = $result->fetch_assoc()): 
                    $isBookedByUser = isVehicleBookedByUser($conn, $vehicle['vehicle_id'], $_SESSION['user_id']);
                    $userBookingDetails = null;
                    if ($isBookedByUser) {
                        $userBookingDetails = getVehicleBookingDetails($conn, $vehicle['vehicle_id'], $_SESSION['user_id']);
                    }
                ?>
                <div class="card vehicle-card" 
                     data-district="<?php echo htmlspecialchars($vehicle['district']); ?>"
                     data-type="<?php echo htmlspecialchars($vehicle['type']); ?>">
                    <img 
                        src="<?php echo htmlspecialchars($vehicle['imageUrl']); ?>" 
                        class="card-img-top"
                        onerror="handleImageError(this)"
                        alt="<?php echo htmlspecialchars($vehicle['model']); ?>"
                    >
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($vehicle['model']); ?></h5>
                        <p class="card-text">
                            <strong>Type:</strong> <?php echo htmlspecialchars($vehicle['type']); ?><br>
                            <strong>Year:</strong> <?php echo htmlspecialchars($vehicle['year']); ?><br>
                            <strong>District:</strong> <?php echo htmlspecialchars($vehicle['district']); ?><br>
                            <strong>Capacity:</strong> <?php echo htmlspecialchars($vehicle['capacity']); ?> persons<br>
                            <strong>Rate:</strong> Rs. <?php echo number_format($vehicle['daily_rate'], 2); ?> per day<br>
                            <strong>Transmission:</strong> <?php echo htmlspecialchars($vehicle['transmission']); ?>
                            <?php if($vehicle['ac_available']): ?>
                                <br><strong>AC:</strong> Available
                            <?php endif; ?>
                        </p>
                        
                        <?php if ($isBookedByUser): ?>
                            <div class="alert alert-info mb-3">
                                <i class="fas fa-car me-2"></i>
                                <strong>You have booked this vehicle!</strong><br>
                                <small>
                                    Pickup: <?php echo date('M d, Y', strtotime($userBookingDetails['pickup_date'])); ?><br>
                                    Return: <?php echo date('M d, Y', strtotime($userBookingDetails['return_date'])); ?><br>
                                    Location: <?php echo htmlspecialchars($userBookingDetails['pickup_location']); ?><br>
                                    Status: <?php echo ucfirst($userBookingDetails['booking_status']); ?>
                                </small>
                            </div>
                            <button class="btn btn-success" disabled>
                                <i class="fas fa-check-circle me-2"></i>Already Booked by You
                            </button>
                        <?php elseif($vehicle['is_available']): ?>
                            <button class="btn btn-primary" onclick="bookVehicle(<?php echo $vehicle['vehicle_id']; ?>)">
                                <i class="fas fa-car me-2"></i>Book Vehicle
                            </button>
                        <?php else: ?>
                            <button class="btn btn-secondary" disabled>
                                <i class="fas fa-times-circle me-2"></i>Currently Unavailable
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
    
    <style>
        .vehicles-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.5rem;
            padding: 1rem 0;
        }
        
        .vehicle-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: all 0.3s ease;
            margin-bottom: 1.5rem;
            height: 100%;
        }
        
        .vehicle-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        }
        
        .vehicle-card .card-img-top {
            height: 200px;
            object-fit: cover;
            width: 100%;
        }
        
        .vehicle-card .card-body {
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            height: calc(100% - 200px);
        }
        
        .vehicle-card .card-title {
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .vehicle-card .card-text {
            flex-grow: 1;
            margin-bottom: 1rem;
            line-height: 1.6;
        }
        
        .alert-info {
            background-color: #e7f3ff;
            border-color: #0d6efd;
            color: #0a58ca;
            border-radius: 8px;
            font-size: 0.9rem;
            border: 1px solid #0d6efd;
        }
        
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .btn-success:disabled {
            background-color: #198754;
            border-color: #198754;
        }
        
        .filter-section {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .form-select {
            border-radius: 8px;
            border: 1px solid #dee2e6;
            padding: 0.75rem 1rem;
        }
        
        .form-select:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }
    </style>
    
    <script>
        const PLACEHOLDER_IMAGE = 'https://via.placeholder.com/300x200?text=No+Image';

        function handleImageError(img) {
            img.onerror = null;
            img.src = PLACEHOLDER_IMAGE;
        }

        function filterVehicles() {
            const district = document.getElementById('districtFilter').value;
            const type = document.getElementById('typeFilter').value;
            const cards = document.querySelectorAll('.vehicle-card');
            
            cards.forEach(card => {
                const matchDistrict = !district || card.dataset.district === district;
                const matchType = !type || card.dataset.type === type;
                
                if (matchDistrict && matchType) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        function bookVehicle(vehicleId) {
            window.location.href = `rent-vehicle-form.php?vehicle_id=${vehicleId}`;
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>