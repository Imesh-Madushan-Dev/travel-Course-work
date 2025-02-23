<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
require_once '../../config/db_connect.php';

if (!isset($_GET['vehicle_id'])) {
    header("Location: book-vehicle.php");
    exit();
}

$vehicle_id = $_GET['vehicle_id'];
$stmt = $conn->prepare("SELECT * FROM vehicles WHERE vehicle_id = ?");
$stmt->bind_param("i", $vehicle_id);
$stmt->execute();
$result = $stmt->get_result();
$vehicle = $result->fetch_assoc();

if (!$vehicle) {
    header("Location: book-vehicle.php");
    exit();
}

$page_title = "Rent Vehicle";
include 'includes/header.php';
?>
<body>
    <div class="dashboard-container">
        <?php include 'includes/sidebar.php'; ?>
        <div class="main-content">
            <header class="mb-4">
                <div class="d-flex justify-content-between align-items-center">
                    <h1>Rent a Vehicle</h1>
                    <div class="user-profile">
                        <i class="fas fa-user-circle"></i>
                        <span><?php echo htmlspecialchars($_SESSION['fullname']); ?></span>
                    </div>
                </div>
            </header>

            <div class="container-fluid p-0">
                <div class="row">
                    <div class="col-md-8 mx-auto">
                        <div class="card shadow-sm">
                            <div class="card-header bg-primary text-white">
                                <h3 class="card-title mb-0">Vehicle Details</h3>
                            </div>
                            <div class="card-body">
                                <div class="row g-4">
                                    <div class="col-md-5">
                                        <div class="vehicle-image-container">
                                            <img src="<?php echo htmlspecialchars($vehicle['imageUrl']); ?>" 
                                                 class="img-fluid rounded shadow-sm"
                                                 style="width: 100%; height: 250px; object-fit: cover;"
                                                 onerror="this.src='https://via.placeholder.com/400x250?text=No+Image'"
                                                 alt="<?php echo htmlspecialchars($vehicle['model']); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-7">
                                        <h4 class="text-primary mb-3"><?php echo htmlspecialchars($vehicle['model']); ?></h4>
                                        <div class="vehicle-details">
                                            <div class="row mb-2">
                                                <div class="col-6">
                                                    <i class="fas fa-car text-primary"></i>
                                                    <strong>Type:</strong> <?php echo htmlspecialchars($vehicle['type']); ?>
                                                </div>
                                                <div class="col-6">
                                                    <i class="fas fa-calendar text-primary"></i>
                                                    <strong>Year:</strong> <?php echo htmlspecialchars($vehicle['year']); ?>
                                                </div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-6">
                                                    <i class="fas fa-users text-primary"></i>
                                                    <strong>Capacity:</strong> <?php echo htmlspecialchars($vehicle['capacity']); ?> persons
                                                </div>
                                                <div class="col-6">
                                                    <i class="fas fa-cog text-primary"></i>
                                                    <strong>Transmission:</strong> <?php echo htmlspecialchars($vehicle['transmission']); ?>
                                                </div>
                                            </div>
                                            <div class="price-tag mt-3 p-2 bg-light rounded">
                                                <h5 class="mb-0 text-primary">
                                                    <i class="fas fa-tag"></i>
                                                    Daily Rate: Rs. <?php echo number_format($vehicle['daily_rate'], 2); ?>
                                                </h5>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <hr class="my-4">

                                <form action="process-vehicle-booking.php" method="POST" class="booking-form" id="bookingForm">
                                    <input type="hidden" name="vehicle_id" value="<?php echo $vehicle_id; ?>">
                                    
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="pickup_date" class="form-label">
                                                    <i class="fas fa-calendar-plus text-primary"></i>
                                                    Pickup Date
                                                </label>
                                                <input type="date" class="form-control" id="pickup_date" name="pickup_date" required
                                                       min="<?php echo date('Y-m-d'); ?>">
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="return_date" class="form-label">
                                                    <i class="fas fa-calendar-minus text-primary"></i>
                                                    Return Date
                                                </label>
                                                <input type="date" class="form-control" id="return_date" name="return_date" required
                                                       min="<?php echo date('Y-m-d'); ?>">
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="form-group">
                                                <label for="pickup_location" class="form-label">
                                                    <i class="fas fa-map-marker-alt text-primary"></i>
                                                    Pickup Location
                                                </label>
                                                <input type="text" class="form-control" id="pickup_location" name="pickup_location" 
                                                       placeholder="Enter pickup location" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="total-cost-section mt-4 p-3 bg-light rounded">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h5 class="mb-0">
                                                <i class="fas fa-calculator text-primary"></i>
                                                Estimated Total:
                                            </h5>
                                            <h5 class="mb-0 text-primary" id="totalCost">Rs. 0.00</h5>
                                        </div>
                                    </div>

                                    <div class="form-actions mt-4 d-flex gap-2">
                                        <button type="submit" class="btn btn-primary flex-grow-1">
                                            <i class="fas fa-check-circle"></i> Confirm Booking
                                        </button>
                                        <a href="book-vehicle.php" class="btn btn-secondary">
                                            <i class="fas fa-arrow-left"></i> Back
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('pickup_date').addEventListener('change', calculateTotal);
        document.getElementById('return_date').addEventListener('change', calculateTotal);

        function calculateTotal() {
            const pickup = new Date(document.getElementById('pickup_date').value);
            const return_date = new Date(document.getElementById('return_date').value);
            
            if (pickup && return_date) {
                const diff = Math.ceil((return_date - pickup) / (1000 * 60 * 60 * 24)) + 1;
                if (diff > 0) {
                    const daily_rate = <?php echo $vehicle['daily_rate']; ?>;
                    const total = diff * daily_rate;
                    document.getElementById('totalCost').textContent = `Rs. ${total.toFixed(2)}`;
                }
            }
        }

        document.getElementById('bookingForm').addEventListener('submit', function(e) {
            const pickup = new Date(document.getElementById('pickup_date').value);
            const return_date = new Date(document.getElementById('return_date').value);
            
            if (return_date < pickup) {
                e.preventDefault();
                alert('Return date cannot be earlier than pickup date');
            }
        });
    </script>
</body>
</html>