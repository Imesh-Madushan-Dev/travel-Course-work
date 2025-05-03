<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['is_admin'])) {
    header("Location: ../../login.php");
    exit();
}

require_once '../../../config/db_connect.php';

// Handle guide deletion if requested
if (isset($_POST['delete_guide']) && isset($_POST['guide_id'])) {
    $guide_id = $_POST['guide_id'];
    
    // Delete guide from database
    $stmt = $conn->prepare("DELETE FROM guides WHERE guide_id = ?");
    $stmt->bind_param("i", $guide_id);
    
    if ($stmt->execute()) {
        $successMessage = "Guide deleted successfully";
    } else {
        $errorMessage = "Error deleting guide: " . $conn->error;
    }
    
    $stmt->close();
}

// Handle guide availability toggle
if (isset($_POST['toggle_availability']) && isset($_POST['guide_id'])) {
    $guide_id = $_POST['guide_id'];
    $current_status = $_POST['current_status'];
    $new_status = $current_status == 1 ? 0 : 1;
    
    // Update guide availability
    $stmt = $conn->prepare("UPDATE guides SET availability = ? WHERE guide_id = ?");
    $stmt->bind_param("ii", $new_status, $guide_id);
    
    if ($stmt->execute()) {
        $successMessage = "Guide availability updated successfully";
    } else {
        $errorMessage = "Error updating guide availability: " . $conn->error;
    }
    
    $stmt->close();
}

// Fetch all guides with booking counts
$guides = [];
$query = "SELECT g.*, COUNT(gb.booking_id) as booking_count, 
          SUM(CASE WHEN gb.tour_date >= CURDATE() THEN 1 ELSE 0 END) as upcoming_bookings
          FROM guides g
          LEFT JOIN guide_bookings gb ON g.guide_id = gb.guide_id
          GROUP BY g.guide_id
          ORDER BY g.guide_id DESC";
$result = $conn->query($query);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $guides[] = $row;
    }
}

$pageTitle = "Manage Tour Guides";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Tour Guides - Travel Ceylon Admin</title>
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

        .search-container {
            margin-bottom: 1.5rem;
        }

        .table-responsive {
            margin-bottom: 1rem;
        }

        .action-btn {
            padding: 0.25rem 0.5rem;
            margin: 0 0.25rem;
            font-size: 0.875rem;
        }

        .guide-card {
            border-radius: 0.75rem;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.2s, box-shadow 0.2s;
            height: 100%;
        }
        
        .guide-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
        }
        
        .guide-img-container {
            height: 200px;
            overflow: hidden;
            position: relative;
        }
        
        .guide-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .guide-availability {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            z-index: 10;
        }
        
        .guide-availability.available {
            background-color: rgba(28, 200, 138, 0.9);
            color: white;
        }
        
        .guide-availability.unavailable {
            background-color: rgba(231, 74, 59, 0.9);
            color: white;
        }
        
        .guide-content {
            padding: 1.25rem;
        }
        
        .guide-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #2c3e50;
        }
        
        .guide-info {
            color: #6c757d;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }
        
        .guide-info i {
            width: 20px;
            text-align: center;
            margin-right: 5px;
        }
        
        .guide-actions {
            padding: 0.75rem 1.25rem;
            background-color: #f8f9fa;
            display: flex;
            justify-content: space-between;
            border-top: 1px solid #e9ecef;
        }
        
        .badge-container {
            margin-top: 0.75rem;
        }
        
        .guide-badge {
            font-size: 0.75rem;
            padding: 0.35rem 0.65rem;
            border-radius: 0.25rem;
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
            display: inline-block;
        }
        
        .district-badge {
            background-color: #e8f4fd;
            color: #4e73df;
        }
        
        .specialization-badge {
            background-color: #e0f8e9;
            color: #1cc88a;
        }
        
        .language-badge {
            background-color: #fff8e1;
            color: #f6c23e;
        }
        
        .rating-stars {
            color: #f6c23e;
            margin-bottom: 0.75rem;
        }
        
        .price-tag {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .filters-container {
            padding: 1rem;
            background: white;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
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
                    <h2 class="section-title">Tour Guides</h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addGuideModal">
                        <i class="fas fa-plus me-2"></i> Add New Guide
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
                
                <div class="filters-container">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Filter by District</label>
                                <select class="form-select" id="districtFilter">
                                    <option value="">All Districts</option>
                                    <?php 
                                    $districts = [];
                                    foreach ($guides as $guide) {
                                        if (!in_array($guide['district'], $districts)) {
                                            $districts[] = $guide['district'];
                                            echo "<option value=\"".htmlspecialchars($guide['district'])."\">".htmlspecialchars($guide['district'])."</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Filter by Specialization</label>
                                <select class="form-select" id="specializationFilter">
                                    <option value="">All Specializations</option>
                                    <?php 
                                    $specializations = [];
                                    foreach ($guides as $guide) {
                                        if (!in_array($guide['specialization'], $specializations)) {
                                            $specializations[] = $guide['specialization'];
                                            echo "<option value=\"".htmlspecialchars($guide['specialization'])."\">".htmlspecialchars($guide['specialization'])."</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Search Guides</label>
                                <input type="text" class="form-control" id="guideSearch" placeholder="Search by name or language">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row" id="guidesContainer">
                    <?php foreach ($guides as $guide): ?>
                    <div class="col-lg-4 col-md-6 mb-4 guide-item" 
                         data-district="<?php echo htmlspecialchars($guide['district']); ?>" 
                         data-specialization="<?php echo htmlspecialchars($guide['specialization']); ?>">
                        <div class="guide-card">
                            <div class="guide-img-container">
                                <img src="../../../<?php echo htmlspecialchars($guide['imageUrl']); ?>" alt="<?php echo htmlspecialchars($guide['name']); ?>" class="guide-img">
                                <div class="guide-availability <?php echo $guide['availability'] ? 'available' : 'unavailable'; ?>">
                                    <?php echo $guide['availability'] ? 'Available' : 'Not Available'; ?>
                                </div>
                            </div>
                            <div class="guide-content">
                                <h3 class="guide-title"><?php echo htmlspecialchars($guide['name']); ?></h3>
                                <div class="rating-stars">
                                    <?php 
                                    $rating = round($guide['rating']);
                                    for ($i = 1; $i <= 5; $i++) {
                                        if ($i <= $rating) {
                                            echo '<i class="fas fa-star"></i>';
                                        } else {
                                            echo '<i class="far fa-star"></i>';
                                        }
                                    }
                                    ?>
                                    <span class="ms-1">(<?php echo $guide['rating']; ?>)</span>
                                </div>
                                <div class="guide-info"><i class="fas fa-phone"></i> <?php echo htmlspecialchars($guide['contact_number']); ?></div>
                                <div class="guide-info"><i class="fas fa-language"></i> <?php echo htmlspecialchars($guide['language']); ?></div>
                                <div class="guide-info"><i class="fas fa-briefcase"></i> <?php echo $guide['experience_years']; ?> years experience</div>
                                <div class="guide-info"><i class="fas fa-calendar-check"></i> <?php echo $guide['booking_count']; ?> total bookings (<?php echo $guide['upcoming_bookings']; ?> upcoming)</div>
                                
                                <div class="badge-container">
                                    <span class="guide-badge district-badge"><?php echo htmlspecialchars($guide['district']); ?></span>
                                    <span class="guide-badge specialization-badge"><?php echo htmlspecialchars($guide['specialization']); ?></span>
                                    <?php 
                                    $languages = explode(',', $guide['language']);
                                    foreach($languages as $language) {
                                        echo '<span class="guide-badge language-badge">'.trim(htmlspecialchars($language)).'</span>';
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="guide-actions">
                                <div class="price-tag"><?php echo number_format($guide['daily_rate']); ?> / day</div>
                                <div>
                                    <form method="POST" action="" style="display:inline;">
                                        <input type="hidden" name="guide_id" value="<?php echo $guide['guide_id']; ?>">
                                        <input type="hidden" name="current_status" value="<?php echo $guide['availability']; ?>">
                                        <input type="hidden" name="toggle_availability" value="1">
                                        <button type="submit" class="btn btn-sm btn-outline-<?php echo $guide['availability'] ? 'warning' : 'success'; ?> action-btn">
                                            <i class="fas fa-toggle-<?php echo $guide['availability'] ? 'on' : 'off'; ?>"></i>
                                        </button>
                                    </form>
                                    <button class="btn btn-sm btn-outline-primary action-btn" onclick="editGuide(<?php echo $guide['guide_id']; ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger action-btn" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#deleteConfirmModal" 
                                            data-id="<?php echo $guide['guide_id']; ?>"
                                            data-name="<?php echo htmlspecialchars($guide['name']); ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if (empty($guides)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-user-tie fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No guides found</p>
                    <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#addGuideModal">
                        <i class="fas fa-plus me-2"></i> Add New Guide
                    </button>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Add Guide Modal -->
    <div class="modal fade" id="addGuideModal" tabindex="-1" aria-labelledby="addGuideModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addGuideModalLabel">Add New Guide</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="process-add-guide.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Full Name</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="language" class="form-label">Languages</label>
                                    <input type="text" class="form-control" id="language" name="language" placeholder="English, Sinhala, Tamil" required>
                                </div>
                                <div class="mb-3">
                                    <label for="experience_years" class="form-label">Years of Experience</label>
                                    <input type="number" class="form-control" id="experience_years" name="experience_years" min="0" required>
                                </div>
                                <div class="mb-3">
                                    <label for="daily_rate" class="form-label">Daily Rate ()</label>
                                    <input type="number" class="form-control" id="daily_rate" name="daily_rate" step="0.01" min="0" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="specialization" class="form-label">Specialization</label>
                                    <input type="text" class="form-control" id="specialization" name="specialization" required>
                                </div>
                                <div class="mb-3">
                                    <label for="district" class="form-label">District</label>
                                    <input type="text" class="form-control" id="district" name="district" required>
                                </div>
                                <div class="mb-3">
                                    <label for="contact_number" class="form-label">Contact Number</label>
                                    <input type="text" class="form-control" id="contact_number" name="contact_number" required>
                                </div>
                                <div class="mb-3">
                                    <label for="guide_image" class="form-label">Guide Image</label>
                                    <input type="file" class="form-control" id="guide_image" name="guide_image" accept="image/*" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Guide</button>
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
                    <p>Are you sure you want to delete guide <strong id="delete-guide-name"></strong>?</p>
                    <p class="text-danger">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <form method="POST" action="">
                        <input type="hidden" name="guide_id" id="delete-guide-id" value="">
                        <input type="hidden" name="delete_guide" value="1">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Function to filter guides
        function filterGuides() {
            const district = document.getElementById('districtFilter').value.toLowerCase();
            const specialization = document.getElementById('specializationFilter').value.toLowerCase();
            const searchText = document.getElementById('guideSearch').value.toLowerCase();
            
            const guideItems = document.querySelectorAll('.guide-item');
            
            guideItems.forEach(item => {
                const guideDistrict = item.getAttribute('data-district').toLowerCase();
                const guideSpecialization = item.getAttribute('data-specialization').toLowerCase();
                const guideName = item.querySelector('.guide-title').textContent.toLowerCase();
                const guideLanguage = item.querySelector('.guide-info:nth-child(4)').textContent.toLowerCase();
                
                const matchesDistrict = district === '' || guideDistrict === district;
                const matchesSpecialization = specialization === '' || guideSpecialization === specialization;
                const matchesSearch = searchText === '' || 
                                     guideName.includes(searchText) || 
                                     guideLanguage.includes(searchText);
                
                if (matchesDistrict && matchesSpecialization && matchesSearch) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        }
        
        // Set up event listeners for filters
        document.getElementById('districtFilter').addEventListener('change', filterGuides);
        document.getElementById('specializationFilter').addEventListener('change', filterGuides);
        document.getElementById('guideSearch').addEventListener('keyup', filterGuides);
        
        // Function to edit guide (placeholder - implement as needed)
        function editGuide(guideId) {
            // Redirect to edit guide page or open modal
            console.log('Edit guide with ID:', guideId);
            // Implement as needed
        }
        
        // Set guide ID in delete modal
        document.getElementById('deleteConfirmModal').addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const guideId = button.getAttribute('data-id');
            const guideName = button.getAttribute('data-name');
            
            document.getElementById('delete-guide-id').value = guideId;
            document.getElementById('delete-guide-name').textContent = guideName;
        });
    </script>
</body>
</html>