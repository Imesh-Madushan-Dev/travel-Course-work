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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rent Vehicle - Travel Ceylon</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../css/dashboard.css">
</head>
<style>
    .dashboard-container {
        background-color: #f8f9fa;
    }
    
    .main-content {
        padding: 1.5rem;
    }
    
    .card {
        border: none;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        margin-bottom: 1.5rem;
    }
    
    .card-header {
        background-color: #fff;
        border-bottom: 1px solid #e9ecef;
        padding: 1rem 1.25rem;
    }
    
    .card-header h3 {
        font-size: 1.25rem;
        color: #2c3e50;
        margin: 0;
    }
    
    .vehicle-image-container img {
        border-radius: 0.5rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    
    .vehicle-details i {
        font-size: 0.875rem;
        width: 16px;
        color: #6c757d;
    }
    
    .vehicle-details .mb-2 {
        font-size: 0.9375rem;
    }
    
    .price-tag {
        background-color: #f8f9fa;
        border-radius: 0.5rem;
        padding: 0.75rem 1rem;
    }
    
    .price-tag h5 {
        font-size: 1.125rem;
    }
    
    .price-tag i {
        font-size: 0.875rem;
        margin-right: 0.5rem;
    }
    
    .form-label {
        font-size: 0.875rem;
        font-weight: 500;
        color: #495057;
        margin-bottom: 0.375rem;
    }
    
    .form-label i {
        font-size: 0.75rem;
        margin-right: 0.375rem;
        color: #6c757d;
    }
    
    .form-control {
        font-size: 0.9375rem;
        padding: 0.5rem 0.75rem;
        border-color: #e0e0e0;
    }
    
    .form-control:focus {
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.15);
    }
    
    .total-cost-section {
        background-color: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 0.5rem;
        padding: 1rem 1.25rem;
    }
    
    .total-cost-section h5 {
        font-size: 1rem;
        color: #495057;
    }
    
    .total-cost-section i {
        font-size: 0.875rem;
        margin-right: 0.5rem;
    }
    
    .form-actions {
        margin-top: 1.5rem;
    }
    
    .btn {
        font-size: 0.9375rem;
        padding: 0.5rem 1rem;
    }
    
    .btn i {
        font-size: 0.875rem;
        margin-right: 0.375rem;
    }
    
    @media (max-width: 768px) {
        .main-content {
            padding: 1rem;
        }
        
        .card-body {
            padding: 1rem;
        }
    }
</style>
<body>
    <div class="dashboard-container">
        <?php include 'includes/sidebar.php'; ?>
        <div class="main-content">
            <header class="mb-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h1 class="h3 mb-0">Rent a Vehicle</h1>
                    <div class="user-profile">
                        <i class="fas fa-user-circle"></i>
                        <span class="ms-2"><?php echo htmlspecialchars($_SESSION['fullname']); ?></span>
                    </div>
                </div>
            </header>

            <div class="container-fluid p-0">
                <div class="row">
                    <div class="col-lg-8 mx-auto">
                        <div class="card">
                            <div class="card-header">
                                <h3>Vehicle Details</h3>
                            </div>
                            <div class="card-body">
                                <div class="row g-4 mb-4">
                                    <div class="col-md-5">
                                        <div class="vehicle-image-container">
                                            <img src="<?php echo htmlspecialchars($vehicle['imageUrl']); ?>" 
                                                 class="img-fluid"
                                                 style="width: 100%; height: 200px; object-fit: cover;"
                                                 onerror="this.src='https://via.placeholder.com/400x200?text=No+Image'"
                                                 alt="<?php echo htmlspecialchars($vehicle['model']); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-7">
                                        <h4 class="h5 mb-3"><?php echo htmlspecialchars($vehicle['model']); ?></h4>
                                        <div class="vehicle-details">
                                            <div class="row mb-2">
                                                <div class="col-6">
                                                    <i class="fas fa-car"></i>
                                                    <span class="ms-2"><?php echo htmlspecialchars($vehicle['type']); ?></span>
                                                </div>
                                                <div class="col-6">
                                                    <i class="fas fa-calendar"></i>
                                                    <span class="ms-2"><?php echo htmlspecialchars($vehicle['year']); ?></span>
                                                </div>
                                            </div>
                                            <div class="row mb-2">
                                                <div class="col-6">
                                                    <i class="fas fa-users"></i>
                                                    <span class="ms-2"><?php echo htmlspecialchars($vehicle['capacity']); ?> persons</span>
                                                </div>
                                                <div class="col-6">
                                                    <i class="fas fa-cog"></i>
                                                    <span class="ms-2"><?php echo htmlspecialchars($vehicle['transmission']); ?></span>
                                                </div>
                                            </div>
                                            <div class="price-tag mt-3">
                                                <small class="text-muted d-block mb-1">Daily Rate</small>
                                                <h5 class="mb-0">
                                                    <i class="fas fa-tag"></i>
                                                    Rs. <?php echo number_format($vehicle['daily_rate'], 2); ?>
                                                </h5>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <form action="process-vehicle-booking.php" method="POST" class="booking-form" id="bookingForm">
                                    <input type="hidden" name="vehicle_id" value="<?php echo $vehicle_id; ?>">
                                    
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="pickup_date" class="form-label">
                                                    <i class="fas fa-calendar-plus"></i>
                                                    Pickup Date
                                                </label>
                                                <input type="date" class="form-control" id="pickup_date" 
                                                       name="pickup_date" required min="<?php echo date('Y-m-d'); ?>">
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="return_date" class="form-label">
                                                    <i class="fas fa-calendar-minus"></i>
                                                    Return Date
                                                </label>
                                                <input type="date" class="form-control" id="return_date" 
                                                       name="return_date" required min="<?php echo date('Y-m-d'); ?>">
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="form-group">
                                                <label for="pickup_location" class="form-label">
                                                    <i class="fas fa-map-marker-alt"></i>
                                                    Pickup Location
                                                </label>
                                                <input type="text" class="form-control" id="pickup_location" 
                                                       name="pickup_location" placeholder="Enter pickup location" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="total-cost-section mt-4">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <i class="fas fa-calculator"></i>
                                                <span class="ms-1">Estimated Total:</span>
                                            </div>
                                            <div id="totalCost" class="text-primary fw-bold">Rs. 0.00</div>
                                        </div>
                                    </div>

                                    <div class="form-actions d-flex gap-2">
                                        <button type="submit" class="btn btn-primary flex-grow-1">
                                            <i class="fas fa-check-circle"></i>
                                            Confirm Booking
                                        </button>
                                        <a href="book-vehicle.php" class="btn btn-light">
                                            <i class="fas fa-arrow-left"></i>
                                            Back
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
                const diffTime = Math.abs(return_date - pickup);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                
                if (diffDays > 0) {
                    const daily_rate = <?php echo $vehicle['daily_rate']; ?>;
                    const total = diffDays * daily_rate;
                    document.getElementById('totalCost').textContent = `Rs. ${total.toFixed(2)}`;
                }
            }
        }

        document.getElementById('bookingForm').addEventListener('submit', function(e) {
            const pickup = new Date(document.getElementById('pickup_date').value);
            const return_date = new Date(document.getElementById('return_date').value);
            
            if (return_date <= pickup) {
                e.preventDefault();
                alert('Return date must be after pickup date');
            }
        });
    </script>
</body>
</html>