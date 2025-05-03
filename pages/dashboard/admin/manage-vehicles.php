<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['is_admin'])) {
    header("Location: ../../login.php");
    exit();
}

require_once '../../../config/db_connect.php';

// Handle vehicle deletion if requested
if (isset($_POST['delete_vehicle']) && isset($_POST['vehicle_id'])) {
    $vehicle_id = $_POST['vehicle_id'];
    
    // Delete vehicle from database
    $stmt = $conn->prepare("DELETE FROM vehicles WHERE vehicle_id = ?");
    $stmt->bind_param("i", $vehicle_id);
    
    if ($stmt->execute()) {
        $successMessage = "Vehicle deleted successfully";
    } else {
        $errorMessage = "Error deleting vehicle: " . $conn->error;
    }
    
    $stmt->close();
}

// Handle vehicle availability toggle
if (isset($_POST['toggle_availability']) && isset($_POST['vehicle_id'])) {
    $vehicle_id = $_POST['vehicle_id'];
    $current_status = $_POST['current_status'];
    $new_status = $current_status == 1 ? 0 : 1;
    
    // Update vehicle availability
    $stmt = $conn->prepare("UPDATE vehicles SET ac_available = ? WHERE vehicle_id = ?");
    $stmt->bind_param("ii", $new_status, $vehicle_id);
    
    if ($stmt->execute()) {
        $successMessage = "Vehicle availability updated successfully";
    } else {
        $errorMessage = "Error updating vehicle availability: " . $conn->error;
    }
    
    $stmt->close();
}

// Handle price update
if (isset($_POST['update_price']) && isset($_POST['vehicle_id']) && isset($_POST['daily_rate'])) {
    $vehicle_id = $_POST['vehicle_id'];
    $price = $_POST['daily_rate'];
    
    // Update vehicle price
    $stmt = $conn->prepare("UPDATE vehicles SET daily_rate = ? WHERE vehicle_id = ?");
    $stmt->bind_param("di", $price, $vehicle_id);
    
    if ($stmt->execute()) {
        $successMessage = "Price updated successfully";
    } else {
        $errorMessage = "Error updating price: " . $conn->error;
    }
    
    $stmt->close();
}

// Fetch all vehicles with booking statistics
$vehicles = [];
$query = "SELECT v.*, 
          COUNT(vb.booking_id) as booking_count,
          SUM(CASE WHEN vb.pickup_date >= CURDATE() THEN 1 ELSE 0 END) as upcoming_bookings
          FROM vehicles v
          LEFT JOIN vehicle_bookings vb ON v.vehicle_id = vb.vehicle_id
          GROUP BY v.vehicle_id
          ORDER BY v.type, v.model ASC";
$result = $conn->query($query);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $vehicles[] = $row;
    }
}

// Get districts for filtering
$districts = [];
$query = "SELECT DISTINCT district FROM vehicles ORDER BY district";
$result = $conn->query($query);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $districts[] = $row['district'];
    }
}

// Get vehicle types for filtering
$vehicleTypes = [];
$query = "SELECT DISTINCT type FROM vehicles ORDER BY type";
$result = $conn->query($query);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $vehicleTypes[] = $row['type'];
    }
}

$pageTitle = "Manage Vehicles";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Vehicles - Travel Ceylon Admin</title>
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

        .vehicle-card {
            border-radius: 0.75rem;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.2s, box-shadow 0.2s;
            height: 100%;
            position: relative;
        }
        
        .vehicle-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
        }
        
        .vehicle-img-container {
            height: 200px;
            overflow: hidden;
            position: relative;
        }
        
        .vehicle-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .availability-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            z-index: 10;
        }
        
        .availability-badge.available {
            background-color: rgba(28, 200, 138, 0.9);
            color: white;
        }
        
        .availability-badge.unavailable {
            background-color: rgba(231, 74, 59, 0.9);
            color: white;
        }
        
        .vehicle-content {
            padding: 1.25rem;
        }
        
        .vehicle-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #2c3e50;
        }
        
        .vehicle-info {
            color: #6c757d;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }
        
        .vehicle-info i {
            width: 20px;
            text-align: center;
            margin-right: 5px;
        }
        
        .vehicle-badges {
            margin-top: 0.75rem;
            margin-bottom: 1rem;
        }
        
        .vehicle-badge {
            font-size: 0.75rem;
            padding: 0.35rem 0.65rem;
            border-radius: 0.25rem;
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
            display: inline-block;
        }
        
        .type-badge {
            background-color: #e8f4fd;
            color: #4e73df;
        }
        
        .location-badge {
            background-color: #e0f8e9;
            color: #1cc88a;
        }
        
        .year-badge {
            background-color: #fff8e1;
            color: #f6c23e;
        }
        
        .transmission-badge {
            background-color: #e3f6f9;
            color: #36b9cc;
        }
        
        .vehicle-stats {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 1.25rem;
            background-color: #f8f9fa;
            border-top: 1px solid #e9ecef;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-value {
            font-size: 1rem;
            font-weight: 700;
            color: #2c3e50;
        }
        
        .stat-label {
            font-size: 0.75rem;
            color: #6c757d;
        }
        
        .vehicle-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 1.25rem;
            background-color: #f8f9fa;
            border-top: 1px solid #e9ecef;
        }
        
        .action-btn {
            padding: 0.25rem 0.5rem;
            margin: 0 0.25rem;
            font-size: 0.875rem;
        }

        .action-btn-group {
            display: flex;
        }
        
        .price-edit,
        .license-plate-info {
            display: flex;
            align-items: center;
            margin-top: 0.5rem;
        }
        
        .price-edit button {
            margin-left: 0.5rem;
        }
        
        .quick-edit-form {
            display: flex;
            align-items: center;
        }
        
        .quick-edit-form input {
            width: 80px;
            margin-right: 0.5rem;
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
                    <h2 class="section-title">Vehicles Management</h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addVehicleModal">
                        <i class="fas fa-plus me-2"></i> Add New Vehicle
                    </button>
                </div>
                
                <?php if (isset($successMessage)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $successMessage; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <?php if (isset($errorMessage)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $errorMessage; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <div class="filter-row">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="districtFilter" class="form-label">Filter by District</label>
                                <select id="districtFilter" class="form-select">
                                    <option value="">All Districts</option>
                                    <?php foreach ($districts as $district): ?>
                                    <option value="<?php echo htmlspecialchars($district); ?>"><?php echo htmlspecialchars($district); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="typeFilter" class="form-label">Filter by Type</label>
                                <select id="typeFilter" class="form-select">
                                    <option value="">All Types</option>
                                    <?php foreach ($vehicleTypes as $type): ?>
                                    <option value="<?php echo htmlspecialchars($type); ?>"><?php echo htmlspecialchars($type); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="availabilityFilter" class="form-label">Filter by Availability</label>
                                <select id="availabilityFilter" class="form-select">
                                    <option value="">All Status</option>
                                    <option value="available">Available</option>
                                    <option value="unavailable">Unavailable</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="vehicleSearch" class="form-label">Search Vehicles</label>
                                <input type="text" id="vehicleSearch" class="form-control" placeholder="Search by model or license plate">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row" id="vehiclesContainer">
                    <?php foreach ($vehicles as $vehicle): ?>
                    <div class="col-lg-4 col-md-6 mb-4 vehicle-item"
                         data-district="<?php echo htmlspecialchars($vehicle['district']); ?>"
                         data-type="<?php echo htmlspecialchars($vehicle['type']); ?>"
                         data-availability="<?php echo $vehicle['ac_available'] ? 'available' : 'unavailable'; ?>">
                        <div class="vehicle-card">
                            <div class="vehicle-img-container">
                                <img src="../../../<?php echo htmlspecialchars($vehicle['imageUrl']); ?>" alt="<?php echo htmlspecialchars($vehicle['model']); ?>" class="vehicle-img">
                                <div class="availability-badge <?php echo $vehicle['ac_available'] ? 'available' : 'unavailable'; ?>">
                                    <?php echo $vehicle['ac_available'] ? 'Available' : 'Not Available'; ?>
                                </div>
                            </div>
                            <div class="vehicle-content">
                                <h3 class="vehicle-title"><?php echo htmlspecialchars($vehicle['model']); ?></h3>
                                <div class="vehicle-info"><i class="fas fa-car"></i> <?php echo htmlspecialchars($vehicle['type']); ?></div>
                                <div class="vehicle-info"><i class="fas fa-calendar"></i> <?php echo htmlspecialchars($vehicle['year']); ?> Model</div>
                                <div class="vehicle-info"><i class="fas fa-users"></i> <?php echo $vehicle['capacity']; ?> Passengers</div>
                                <div class="vehicle-info"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($vehicle['district']); ?></div>
                                
                                <div class="license-plate-info">
                                    <span class="fw-bold me-2"><i class="fas fa-id-card me-2"></i> License Plate:</span>
                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($vehicle['license_plate']); ?></span>
                                </div>
                                
                                <div class="vehicle-badges">
                                    <span class="vehicle-badge type-badge"><i class="fas fa-car me-1"></i> <?php echo htmlspecialchars($vehicle['type']); ?></span>
                                    <span class="vehicle-badge location-badge"><i class="fas fa-map-marker-alt me-1"></i> <?php echo htmlspecialchars($vehicle['district']); ?></span>
                                    <span class="vehicle-badge year-badge"><i class="fas fa-calendar me-1"></i> <?php echo $vehicle['year']; ?></span>
                                    <span class="vehicle-badge transmission-badge"><i class="fas fa-cog me-1"></i> <?php echo htmlspecialchars($vehicle['transmission']); ?></span>
                                </div>
                                
                                <div class="price-edit">
                                    <form method="POST" action="" class="quick-edit-form">
                                        <input type="hidden" name="vehicle_id" value="<?php echo $vehicle['vehicle_id']; ?>">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text"></span>
                                            <input type="number" name="daily_rate" class="form-control" value="<?php echo $vehicle['daily_rate']; ?>" step="0.01" min="0">
                                            <button type="submit" name="update_price" class="btn btn-outline-primary btn-sm">Update Rate</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            
                            <div class="vehicle-stats">
                                <div class="stat-item">
                                    <div class="stat-value"><?php echo $vehicle['booking_count']; ?></div>
                                    <div class="stat-label">Total Bookings</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-value"><?php echo $vehicle['upcoming_bookings']; ?></div>
                                    <div class="stat-label">Upcoming</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-value"><?php echo htmlspecialchars($vehicle['transmission']); ?></div>
                                    <div class="stat-label">Transmission</div>
                                </div>
                            </div>
                            
                            <div class="vehicle-actions">
                                <div>
                                    <form method="POST" action="" style="display:inline;">
                                        <input type="hidden" name="vehicle_id" value="<?php echo $vehicle['vehicle_id']; ?>">
                                        <input type="hidden" name="current_status" value="<?php echo $vehicle['ac_available']; ?>">
                                        <input type="hidden" name="toggle_availability" value="1">
                                        <button type="submit" class="btn btn-sm btn-outline-<?php echo $vehicle['ac_available'] ? 'warning' : 'success'; ?> action-btn">
                                            <i class="fas fa-toggle-<?php echo $vehicle['ac_available'] ? 'on' : 'off'; ?>"></i> 
                                            <?php echo $vehicle['ac_available'] ? 'Set Unavailable' : 'Set Available'; ?>
                                        </button>
                                    </form>
                                </div>
                                <div class="action-btn-group">
                                    <button class="btn btn-sm btn-outline-primary action-btn" onclick="editVehicle(<?php echo $vehicle['vehicle_id']; ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger action-btn" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#deleteConfirmModal" 
                                            data-id="<?php echo $vehicle['vehicle_id']; ?>"
                                            data-model="<?php echo htmlspecialchars($vehicle['model']); ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if (empty($vehicles)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-car fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No vehicles found</p>
                    <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#addVehicleModal">
                        <i class="fas fa-plus me-2"></i> Add New Vehicle
                    </button>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Add Vehicle Modal -->
    <div class="modal fade" id="addVehicleModal" tabindex="-1" aria-labelledby="addVehicleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addVehicleModalLabel">Add New Vehicle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="process-add-vehicle.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="type" class="form-label">Vehicle Type</label>
                                    <select class="form-select" id="type" name="type" required>
                                        <option value="">Select Type</option>
                                        <option value="Car">Car</option>
                                        <option value="Van">Van</option>
                                        <option value="SUV">SUV</option>
                                        <option value="Mini Bus">Mini Bus</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="model" class="form-label">Model</label>
                                    <input type="text" class="form-control" id="model" name="model" required>
                                </div>
                                <div class="mb-3">
                                    <label for="year" class="form-label">Year</label>
                                    <input type="number" class="form-control" id="year" name="year" min="2000" max="2025" required>
                                </div>
                                <div class="mb-3">
                                    <label for="district" class="form-label">District</label>
                                    <input type="text" class="form-control" id="district" name="district" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="license_plate" class="form-label">License Plate Number</label>
                                    <input type="text" class="form-control" id="license_plate" name="license_plate" required>
                                </div>
                                <div class="mb-3">
                                    <label for="daily_rate" class="form-label">Daily Rate ()</label>
                                    <input type="number" class="form-control" id="daily_rate" name="daily_rate" step="0.01" min="0" required>
                                </div>
                                <div class="mb-3">
                                    <label for="capacity" class="form-label">Passenger Capacity</label>
                                    <input type="number" class="form-control" id="capacity" name="capacity" min="1" required>
                                </div>
                                <div class="mb-3">
                                    <label for="transmission" class="form-label">Transmission</label>
                                    <select class="form-select" id="transmission" name="transmission" required>
                                        <option value="">Select Transmission</option>
                                        <option value="Automatic">Automatic</option>
                                        <option value="Manual">Manual</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="ac_available" class="form-label">AC Available</label>
                                    <select class="form-select" id="ac_available" name="ac_available">
                                        <option value="1">Yes</option>
                                        <option value="0">No</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="vehicle_image" class="form-label">Vehicle Image</label>
                                    <input type="file" class="form-control" id="vehicle_image" name="vehicle_image" accept="image/*" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Vehicle</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the vehicle <strong id="delete-vehicle-model"></strong>?</p>
                    <p class="text-danger">This action cannot be undone and will remove all associated bookings.</p>
                </div>
                <div class="modal-footer">
                    <form method="POST" action="">
                        <input type="hidden" name="vehicle_id" id="delete-vehicle-id" value="">
                        <input type="hidden" name="delete_vehicle" value="1">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Filter vehicles
        function filterVehicles() {
            const district = document.getElementById('districtFilter').value.toLowerCase();
            const type = document.getElementById('typeFilter').value;
            const availability = document.getElementById('availabilityFilter').value;
            const searchText = document.getElementById('vehicleSearch').value.toLowerCase();
            
            const vehicleItems = document.querySelectorAll('.vehicle-item');
            
            vehicleItems.forEach(item => {
                const vehicleDistrict = item.getAttribute('data-district').toLowerCase();
                const vehicleType = item.getAttribute('data-type');
                const vehicleAvailability = item.getAttribute('data-availability');
                const vehicleModel = item.querySelector('.vehicle-title').textContent.toLowerCase();
                const vehicleLicensePlate = item.querySelector('.license-plate-info .badge').textContent.toLowerCase();
                
                const matchesDistrict = district === '' || vehicleDistrict.includes(district);
                const matchesType = type === '' || vehicleType === type;
                const matchesAvailability = availability === '' || vehicleAvailability === availability;
                const matchesSearch = searchText === '' || 
                                     vehicleModel.includes(searchText) || 
                                     vehicleLicensePlate.includes(searchText);
                
                if (matchesDistrict && matchesType && matchesAvailability && matchesSearch) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        }
        
        // Set up event listeners for filters
        document.getElementById('districtFilter').addEventListener('change', filterVehicles);
        document.getElementById('typeFilter').addEventListener('change', filterVehicles);
        document.getElementById('availabilityFilter').addEventListener('change', filterVehicles);
        document.getElementById('vehicleSearch').addEventListener('keyup', filterVehicles);
        
        // Function to edit vehicle (placeholder - implement as needed)
        function editVehicle(vehicleId) {
            // Redirect to edit vehicle page or open modal
            console.log('Edit vehicle with ID:', vehicleId);
            // Implement as needed
            window.location.href = `edit-vehicle.php?id=${vehicleId}`;
        }
        
        // Set vehicle ID in delete modal
        document.getElementById('deleteConfirmModal').addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const vehicleId = button.getAttribute('data-id');
            const vehicleModel = button.getAttribute('data-model');
            
            document.getElementById('delete-vehicle-id').value = vehicleId;
            document.getElementById('delete-vehicle-model').textContent = vehicleModel;
        });
    </script>
</body>
</html>