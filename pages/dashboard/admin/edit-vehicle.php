<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['is_admin'])) {
    header("Location: ../../login.php");
    exit();
}

require_once '../../../config/db_connect.php';

// Check if vehicle ID is provided
if (!isset($_GET['id'])) {
    header("Location: manage-vehicles.php?error=no_vehicle_specified");
    exit();
}

$vehicle_id = intval($_GET['id']);

// Get vehicle details
$stmt = $conn->prepare("SELECT * FROM vehicles WHERE vehicle_id = ?");
$stmt->bind_param("i", $vehicle_id);
$stmt->execute();
$result = $stmt->get_result();
$vehicle = $result->fetch_assoc();

// Check if vehicle exists
if (!$vehicle) {
    header("Location: manage-vehicles.php?error=vehicle_not_found");
    exit();
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get and sanitize form data
    $type = $conn->real_escape_string($_POST['type']);
    $model = $conn->real_escape_string($_POST['model']);
    $year = intval($_POST['year']);
    $daily_rate = floatval($_POST['daily_rate']);
    $capacity = intval($_POST['capacity']);
    $district = $conn->real_escape_string($_POST['district']);
    $transmission = $conn->real_escape_string($_POST['transmission']);
    $license_plate = $conn->real_escape_string($_POST['license_plate']);
    $ac_available = isset($_POST['ac_available']) ? intval($_POST['ac_available']) : 1;
    
    // Check if license plate already exists (but not for this vehicle)
    $check_stmt = $conn->prepare("SELECT vehicle_id FROM vehicles WHERE license_plate = ? AND vehicle_id != ?");
    $check_stmt->bind_param("si", $license_plate, $vehicle_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        $error_message = "License plate already exists. Please use a different one.";
    } else {
        $image_url = $vehicle['imageUrl']; // Default to current image
        
        // Handle image upload if a new one is provided
        if (!empty($_FILES['vehicle_image']['name'])) {
            $target_dir = "../../../images/vehicles/";
            
            // Create directory if it doesn't exist
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            $file_extension = strtolower(pathinfo($_FILES["vehicle_image"]["name"], PATHINFO_EXTENSION));
            $new_filename = "vehicle_" . time() . "_" . rand(1000, 9999) . "." . $file_extension;
            $target_file = $target_dir . $new_filename;
            $new_image_url = "images/vehicles/" . $new_filename;
            
            // Check if image file is a actual image
            $check = getimagesize($_FILES["vehicle_image"]["tmp_name"]);
            if ($check !== false) {
                // Check file size (2MB max)
                if ($_FILES["vehicle_image"]["size"] <= 2000000) {
                    // Allow certain file formats
                    if ($file_extension == "jpg" || $file_extension == "png" || $file_extension == "jpeg") {
                        // Upload the file
                        if (move_uploaded_file($_FILES["vehicle_image"]["tmp_name"], $target_file)) {
                            // Delete old image if it exists
                            if (!empty($vehicle['imageUrl']) && file_exists("../../../" . $vehicle['imageUrl'])) {
                                unlink("../../../" . $vehicle['imageUrl']);
                            }
                            $image_url = $new_image_url;
                        } else {
                            $error_message = "Sorry, there was an error uploading your file.";
                        }
                    } else {
                        $error_message = "Sorry, only JPG, JPEG & PNG files are allowed.";
                    }
                } else {
                    $error_message = "Sorry, your file is too large. Max size is 2MB.";
                }
            } else {
                $error_message = "File is not an image.";
            }
        }
        
        if (!isset($error_message)) {
            // Update vehicle in database
            $update_stmt = $conn->prepare("UPDATE vehicles SET 
                                         type = ?, 
                                         model = ?, 
                                         year = ?, 
                                         daily_rate = ?, 
                                         capacity = ?, 
                                         district = ?, 
                                         imageUrl = ?, 
                                         ac_available = ?, 
                                         transmission = ?, 
                                         license_plate = ? 
                                         WHERE vehicle_id = ?");
            
            $update_stmt->bind_param("ssisissisii", $type, $model, $year, $daily_rate, $capacity, $district, $image_url, $ac_available, $transmission, $license_plate, $vehicle_id);
            
            if ($update_stmt->execute()) {
                $success_message = "Vehicle updated successfully!";
                
                // Refresh vehicle data
                $stmt->execute();
                $result = $stmt->get_result();
                $vehicle = $result->fetch_assoc();
            } else {
                $error_message = "Error updating vehicle: " . $conn->error;
            }
        }
    }
}

$pageTitle = "Edit Vehicle";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Vehicle - Travel Ceylon Admin</title>
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

        .image-preview {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }

        .vehicle-info {
            padding: 1rem;
            background-color: #f8f9fa;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .vehicle-stats {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 1.25rem;
            background-color: #f8f9fa;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-value {
            font-size: 1.25rem;
            font-weight: 700;
            color: #2c3e50;
        }
        
        .stat-label {
            font-size: 0.875rem;
            color: #6c757d;
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
                    <h2 class="section-title">Edit Vehicle</h2>
                    <a href="manage-vehicles.php" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left me-2"></i> Back to Vehicles
                    </a>
                </div>
                
                <?php if (isset($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $success_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <?php if (isset($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $error_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <div class="row">
                    <div class="col-lg-4">
                        <div class="text-center">
                            <img 
                                src="../../../<?php echo htmlspecialchars($vehicle['imageUrl']); ?>" 
                                alt="<?php echo htmlspecialchars($vehicle['model']); ?>"
                                class="image-preview"
                                id="imagePreview"
                                onerror="this.src='https://via.placeholder.com/400x200?text=No+Image'"
                            >
                        </div>
                        
                        <div class="vehicle-info">
                            <h4 class="mb-3"><?php echo htmlspecialchars($vehicle['model']); ?></h4>
                            <div class="mb-2">
                                <i class="fas fa-car text-primary me-2"></i>
                                <span class="fw-bold">Type:</span>
                                <span class="ms-1"><?php echo htmlspecialchars($vehicle['type']); ?></span>
                            </div>
                            <div class="mb-2">
                                <i class="fas fa-id-card text-primary me-2"></i>
                                <span class="fw-bold">License:</span>
                                <span class="ms-1"><?php echo htmlspecialchars($vehicle['license_plate']); ?></span>
                            </div>
                            <div class="mb-2">
                                <i class="fas fa-calendar text-primary me-2"></i>
                                <span class="fw-bold">Year:</span>
                                <span class="ms-1"><?php echo $vehicle['year']; ?></span>
                            </div>
                            <div class="mb-2">
                                <i class="fas fa-map-marker-alt text-primary me-2"></i>
                                <span class="fw-bold">District:</span>
                                <span class="ms-1"><?php echo htmlspecialchars($vehicle['district']); ?></span>
                            </div>
                            <div>
                                <i class="fas fa-tag text-primary me-2"></i>
                                <span class="fw-bold">Current Rate:</span>
                                <span class="ms-1">Rs. <?php echo number_format($vehicle['daily_rate'], 2); ?>/day</span>
                            </div>
                        </div>
                        
                        <?php
                        // Get booking statistics for this vehicle
                        $stats_query = "SELECT 
                                        COUNT(*) as total_bookings,
                                        SUM(CASE WHEN pickup_date >= CURDATE() THEN 1 ELSE 0 END) as upcoming_bookings,
                                        SUM(total_cost) as total_revenue
                                        FROM vehicle_bookings 
                                        WHERE vehicle_id = ?";
                        $stats_stmt = $conn->prepare($stats_query);
                        $stats_stmt->bind_param("i", $vehicle_id);
                        $stats_stmt->execute();
                        $stats_result = $stats_stmt->get_result();
                        $stats = $stats_result->fetch_assoc();
                        ?>
                        
                        <div class="vehicle-stats">
                            <div class="stat-item">
                                <div class="stat-value"><?php echo $stats['total_bookings']; ?></div>
                                <div class="stat-label">Total Bookings</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value"><?php echo $stats['upcoming_bookings']; ?></div>
                                <div class="stat-label">Upcoming</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value">Rs. <?php echo number_format($stats['total_revenue'] ?: 0, 0); ?></div>
                                <div class="stat-label">Revenue</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-8">
                        <form method="POST" action="" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="type" class="form-label">Vehicle Type</label>
                                        <select class="form-select" id="type" name="type" required>
                                            <option value="Car" <?php echo $vehicle['type'] == 'Car' ? 'selected' : ''; ?>>Car</option>
                                            <option value="Van" <?php echo $vehicle['type'] == 'Van' ? 'selected' : ''; ?>>Van</option>
                                            <option value="SUV" <?php echo $vehicle['type'] == 'SUV' ? 'selected' : ''; ?>>SUV</option>
                                            <option value="Mini Bus" <?php echo $vehicle['type'] == 'Mini Bus' ? 'selected' : ''; ?>>Mini Bus</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="model" class="form-label">Model</label>
                                        <input type="text" class="form-control" id="model" name="model" value="<?php echo htmlspecialchars($vehicle['model']); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="year" class="form-label">Year</label>
                                        <input type="number" class="form-control" id="year" name="year" min="2000" max="2025" value="<?php echo $vehicle['year']; ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="district" class="form-label">District</label>
                                        <input type="text" class="form-control" id="district" name="district" value="<?php echo htmlspecialchars($vehicle['district']); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="license_plate" class="form-label">License Plate Number</label>
                                        <input type="text" class="form-control" id="license_plate" name="license_plate" value="<?php echo htmlspecialchars($vehicle['license_plate']); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="daily_rate" class="form-label">Daily Rate (Rs.)</label>
                                        <input type="number" class="form-control" id="daily_rate" name="daily_rate" step="0.01" min="0" value="<?php echo $vehicle['daily_rate']; ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="capacity" class="form-label">Passenger Capacity</label>
                                        <input type="number" class="form-control" id="capacity" name="capacity" min="1" value="<?php echo $vehicle['capacity']; ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="transmission" class="form-label">Transmission</label>
                                        <select class="form-select" id="transmission" name="transmission" required>
                                            <option value="Automatic" <?php echo $vehicle['transmission'] == 'Automatic' ? 'selected' : ''; ?>>Automatic</option>
                                            <option value="Manual" <?php echo $vehicle['transmission'] == 'Manual' ? 'selected' : ''; ?>>Manual</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="ac_available" class="form-label">AC Available</label>
                                        <select class="form-select" id="ac_available" name="ac_available">
                                            <option value="1" <?php echo $vehicle['ac_available'] ? 'selected' : ''; ?>>Yes</option>
                                            <option value="0" <?php echo !$vehicle['ac_available'] ? 'selected' : ''; ?>>No</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="vehicle_image" class="form-label">Vehicle Image</label>
                                        <input type="file" class="form-control" id="vehicle_image" name="vehicle_image" accept="image/*">
                                        <small class="form-text text-muted">Leave empty to keep current image</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i> Save Changes
                                </button>
                                <a href="manage-vehicles.php" class="btn btn-light ms-2">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Preview image before upload
        document.getElementById('vehicle_image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('imagePreview').src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>