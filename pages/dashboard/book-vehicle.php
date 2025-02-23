<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
require_once '../../config/db_connect.php';
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
                        <?php if($vehicle['is_available']): ?>
                            <button class="btn btn-primary" onclick="bookVehicle(<?php echo $vehicle['vehicle_id']; ?>)">
                                Book Vehicle
                            </button>
                        <?php else: ?>
                            <button class="btn btn-secondary" disabled>
                                Currently Unavailable
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
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