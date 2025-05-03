<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['is_admin'])) {
    header("Location: ../../login.php");
    exit();
}

require_once '../../../config/db_connect.php';

// Handle hotel deletion if requested
if (isset($_POST['delete_hotel']) && isset($_POST['hotel_id'])) {
    $hotel_id = $_POST['hotel_id'];
    
    // Delete hotel from database
    $stmt = $conn->prepare("DELETE FROM hotels WHERE hotel_id = ?");
    $stmt->bind_param("i", $hotel_id);
    
    if ($stmt->execute()) {
        $successMessage = "Hotel deleted successfully";
    } else {
        $errorMessage = "Error deleting hotel: " . $conn->error;
    }
    
    $stmt->close();
}

// Handle room availability updates
if (isset($_POST['update_rooms']) && isset($_POST['hotel_id']) && isset($_POST['available_rooms'])) {
    $hotel_id = $_POST['hotel_id'];
    $available_rooms = $_POST['available_rooms'];
    
    // Update hotel room availability
    $stmt = $conn->prepare("UPDATE hotels SET available_rooms = ? WHERE hotel_id = ?");
    $stmt->bind_param("ii", $available_rooms, $hotel_id);
    
    if ($stmt->execute()) {
        $successMessage = "Room availability updated successfully";
    } else {
        $errorMessage = "Error updating room availability: " . $conn->error;
    }
    
    $stmt->close();
}

// Handle price update
if (isset($_POST['update_price']) && isset($_POST['hotel_id']) && isset($_POST['price_per_night'])) {
    $hotel_id = $_POST['hotel_id'];
    $price = $_POST['price_per_night'];
    
    // Update hotel price
    $stmt = $conn->prepare("UPDATE hotels SET price_per_night = ? WHERE hotel_id = ?");
    $stmt->bind_param("di", $price, $hotel_id);
    
    if ($stmt->execute()) {
        $successMessage = "Price updated successfully";
    } else {
        $errorMessage = "Error updating price: " . $conn->error;
    }
    
    $stmt->close();
}

// Fetch all hotels with booking statistics
$hotels = [];
$query = "SELECT h.*, 
          COUNT(hb.booking_id) as booking_count,
          SUM(CASE WHEN hb.check_in_date >= CURDATE() THEN 1 ELSE 0 END) as upcoming_bookings,
          SUM(CASE WHEN hb.booking_status = 'pending' THEN 1 ELSE 0 END) as pending_bookings
          FROM hotels h
          LEFT JOIN hotel_bookings hb ON h.hotel_id = hb.hotel_id
          GROUP BY h.hotel_id
          ORDER BY h.star_rating DESC, h.name ASC";
$result = $conn->query($query);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $hotels[] = $row;
    }
}

// Get districts for filtering
$districts = [];
$query = "SELECT DISTINCT district FROM hotels ORDER BY district";
$result = $conn->query($query);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $districts[] = $row['district'];
    }
}

$pageTitle = "Manage Hotels";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Hotels - Travel Ceylon Admin</title>
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

        .hotel-card {
            border-radius: 0.75rem;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.2s, box-shadow 0.2s;
            height: 100%;
            position: relative;
        }
        
        .hotel-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
        }
        
        .hotel-img-container {
            height: 200px;
            overflow: hidden;
            position: relative;
        }
        
        .hotel-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .star-rating {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            background-color: rgba(0, 0, 0, 0.7);
            color: #f6c23e;
            z-index: 10;
        }
        
        .hotel-content {
            padding: 1.25rem;
        }
        
        .hotel-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #2c3e50;
        }
        
        .hotel-info {
            color: #6c757d;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }
        
        .hotel-info i {
            width: 20px;
            text-align: center;
            margin-right: 5px;
        }
        
        .hotel-description {
            font-size: 0.9rem;
            color: #6c757d;
            margin-bottom: 1rem;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
        }
        
        .hotel-badges {
            margin-top: 0.75rem;
            margin-bottom: 1rem;
        }
        
        .hotel-badge {
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
        
        .rooms-badge {
            background-color: #e0f8e9;
            color: #1cc88a;
        }
        
        .price-badge {
            background-color: #fff8e1;
            color: #f6c23e;
        }
        
        .stars-container {
            color: #f6c23e;
            margin-bottom: 0.5rem;
        }
        
        .hotel-stats {
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
        
        .hotel-actions {
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
        
        .modal-confirm {
            color: #636363;
        }
        
        .modal-confirm .modal-content {
            padding: 20px;
            border-radius: 5px;
            border: none;
        }
        
        .modal-confirm .modal-header {
            border-bottom: none;   
            position: relative;
        }
        
        .modal-confirm h4 {
            text-align: center;
            font-size: 26px;
            margin: 30px 0 -15px;
        }
        
        .modal-confirm .icon-box {
            color: #fff;
            position: absolute;
            margin: 0 auto;
            left: 0;
            right: 0;
            top: -70px;
            width: 95px;
            height: 95px;
            border-radius: 50%;
            z-index: 9;
            background: #ef513a;
            padding: 15px;
            text-align: center;
            box-shadow: 0px 2px 2px rgba(0, 0, 0, 0.1);
        }
        
        .modal-confirm .icon-box i {
            font-size: 56px;
            position: relative;
            top: 4px;
        }
        
        .edit-hotel-img-preview {
            max-width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 0.25rem;
            margin-bottom: 1rem;
        }
        
        .quick-edit-form {
            display: flex;
            align-items: center;
        }
        
        .quick-edit-form input {
            width: 80px;
            margin-right: 0.5rem;
        }
        
        .price-edit,
        .rooms-edit {
            display: flex;
            align-items: center;
            margin-top: 0.5rem;
        }
        
        .price-edit button,
        .rooms-edit button {
            margin-left: 0.5rem;
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
                    <h2 class="section-title">Hotels Management</h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addHotelModal">
                        <i class="fas fa-plus me-2"></i> Add New Hotel
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
                                <label for="starFilter" class="form-label">Filter by Stars</label>
                                <select id="starFilter" class="form-select">
                                    <option value="">All Star Ratings</option>
                                    <option value="5">5 Stars</option>
                                    <option value="4">4 Stars</option>
                                    <option value="3">3 Stars</option>
                                    <option value="2">2 Stars</option>
                                    <option value="1">1 Star</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="priceSort" class="form-label">Sort by Price</label>
                                <select id="priceSort" class="form-select">
                                    <option value="">Default</option>
                                    <option value="asc">Price: Low to High</option>
                                    <option value="desc">Price: High to Low</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="hotelSearch" class="form-label">Search Hotels</label>
                                <input type="text" id="hotelSearch" class="form-control" placeholder="Search by name">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row" id="hotelsContainer">
                    <?php foreach ($hotels as $hotel): ?>
                    <div class="col-lg-4 col-md-6 mb-4 hotel-item"
                         data-district="<?php echo htmlspecialchars($hotel['district']); ?>"
                         data-stars="<?php echo $hotel['star_rating']; ?>"
                         data-price="<?php echo $hotel['price_per_night']; ?>">
                        <div class="hotel-card">
                            <div class="hotel-img-container">
                                <img src="../../../<?php echo htmlspecialchars($hotel['imageUrl']); ?>" alt="<?php echo htmlspecialchars($hotel['name']); ?>" class="hotel-img">
                                <div class="star-rating">
                                    <?php 
                                    for ($i = 0; $i < $hotel['star_rating']; $i++) {
                                        echo '<i class="fas fa-star"></i>';
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="hotel-content">
                                <h3 class="hotel-title"><?php echo htmlspecialchars($hotel['name']); ?></h3>
                                <div class="hotel-info"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($hotel['district']); ?></div>
                                <div class="hotel-info"><i class="fas fa-phone"></i> <?php echo htmlspecialchars($hotel['contact_number']); ?></div>
                                <div class="hotel-info"><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($hotel['email']); ?></div>
                                <p class="hotel-description"><?php echo htmlspecialchars($hotel['description']); ?></p>
                                
                                <div class="hotel-badges">
                                    <span class="hotel-badge district-badge"><i class="fas fa-map-marker-alt me-1"></i> <?php echo htmlspecialchars($hotel['district']); ?></span>
                                    <span class="hotel-badge rooms-badge"><i class="fas fa-door-open me-1"></i> <?php echo $hotel['available_rooms']; ?> rooms available</span>
                                    <span class="hotel-badge price-badge"><i class="fas fa-tag me-1"></i> <?php echo number_format($hotel['price_per_night']); ?> per night</span>
                                </div>
                                
                                <div class="price-edit">
                                    <form method="POST" action="" class="quick-edit-form">
                                        <input type="hidden" name="hotel_id" value="<?php echo $hotel['hotel_id']; ?>">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text"></span>
                                            <input type="number" name="price_per_night" class="form-control" value="<?php echo $hotel['price_per_night']; ?>" step="0.01" min="0">
                                            <button type="submit" name="update_price" class="btn btn-outline-primary btn-sm">Update Price</button>
                                        </div>
                                    </form>
                                </div>
                                
                                <div class="rooms-edit">
                                    <form method="POST" action="" class="quick-edit-form">
                                        <input type="hidden" name="hotel_id" value="<?php echo $hotel['hotel_id']; ?>">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text"><i class="fas fa-door-open"></i></span>
                                            <input type="number" name="available_rooms" class="form-control" value="<?php echo $hotel['available_rooms']; ?>" min="0">
                                            <button type="submit" name="update_rooms" class="btn btn-outline-primary btn-sm">Update Rooms</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            
                            <div class="hotel-stats">
                                <div class="stat-item">
                                    <div class="stat-value"><?php echo $hotel['booking_count']; ?></div>
                                    <div class="stat-label">Total Bookings</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-value"><?php echo $hotel['upcoming_bookings']; ?></div>
                                    <div class="stat-label">Upcoming</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-value"><?php echo $hotel['pending_bookings']; ?></div>
                                    <div class="stat-label">Pending</div>
                                </div>
                            </div>
                            
                            <div class="hotel-actions">
                                <div class="fw-bold">Manage Hotel</div>
                                <div class="action-btn-group">
                                    <button class="btn btn-sm btn-outline-primary action-btn" onclick="editHotel(<?php echo $hotel['hotel_id']; ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-success action-btn" onclick="viewBookings(<?php echo $hotel['hotel_id']; ?>)">
                                        <i class="fas fa-calendar-check"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger action-btn" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#deleteConfirmModal" 
                                            data-id="<?php echo $hotel['hotel_id']; ?>"
                                            data-name="<?php echo htmlspecialchars($hotel['name']); ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if (empty($hotels)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-hotel fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No hotels found</p>
                    <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#addHotelModal">
                        <i class="fas fa-plus me-2"></i> Add New Hotel
                    </button>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Add Hotel Modal -->
    <div class="modal fade" id="addHotelModal" tabindex="-1" aria-labelledby="addHotelModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addHotelModalLabel">Add New Hotel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="process-add-hotel.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Hotel Name</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="district" class="form-label">District</label>
                                    <input type="text" class="form-control" id="district" name="district" required>
                                </div>
                                <div class="mb-3">
                                    <label for="star_rating" class="form-label">Star Rating</label>
                                    <select class="form-select" id="star_rating" name="star_rating" required>
                                        <option value="">Select Star Rating</option>
                                        <option value="1">1 Star</option>
                                        <option value="2">2 Stars</option>
                                        <option value="3">3 Stars</option>
                                        <option value="4">4 Stars</option>
                                        <option value="5">5 Stars</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="price_per_night" class="form-label">Price per Night ()</label>
                                    <input type="number" class="form-control" id="price_per_night" name="price_per_night" step="0.01" min="0" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="contact_number" class="form-label">Contact Number</label>
                                    <input type="text" class="form-control" id="contact_number" name="contact_number" required>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                <div class="mb-3">
                                    <label for="available_rooms" class="form-label">Available Rooms</label>
                                    <input type="number" class="form-control" id="available_rooms" name="available_rooms" min="0" required>
                                </div>
                                <div class="mb-3">
                                    <label for="hotel_image" class="form-label">Hotel Image</label>
                                    <input type="file" class="form-control" id="hotel_image" name="hotel_image" accept="image/*" required>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Hotel</button>
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
                    <p>Are you sure you want to delete the hotel <strong id="delete-hotel-name"></strong>?</p>
                    <p class="text-danger">This action cannot be undone and will remove all associated bookings.</p>
                </div>
                <div class="modal-footer">
                    <form method="POST" action="">
                        <input type="hidden" name="hotel_id" id="delete-hotel-id" value="">
                        <input type="hidden" name="delete_hotel" value="1">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Filter and sort hotels
        function filterHotels() {
            const district = document.getElementById('districtFilter').value.toLowerCase();
            const starRating = document.getElementById('starFilter').value;
            const searchText = document.getElementById('hotelSearch').value.toLowerCase();
            const priceSort = document.getElementById('priceSort').value;
            
            const hotelItems = document.querySelectorAll('.hotel-item');
            let visibleHotels = [];
            
            // Filter hotels
            hotelItems.forEach(item => {
                const hotelDistrict = item.getAttribute('data-district').toLowerCase();
                const hotelStars = item.getAttribute('data-stars');
                const hotelName = item.querySelector('.hotel-title').textContent.toLowerCase();
                const hotelPrice = parseFloat(item.getAttribute('data-price'));
                
                const matchesDistrict = district === '' || hotelDistrict === district;
                const matchesStar = starRating === '' || hotelStars === starRating;
                const matchesSearch = searchText === '' || hotelName.includes(searchText);
                
                if (matchesDistrict && matchesStar && matchesSearch) {
                    item.style.display = '';
                    visibleHotels.push(item);
                } else {
                    item.style.display = 'none';
                }
            });
            
            // Sort hotels if needed
            if (priceSort !== '' && visibleHotels.length > 0) {
                const container = document.getElementById('hotelsContainer');
                const sortedHotels = [...visibleHotels].sort((a, b) => {
                    const priceA = parseFloat(a.getAttribute('data-price'));
                    const priceB = parseFloat(b.getAttribute('data-price'));
                    
                    return priceSort === 'asc' ? priceA - priceB : priceB - priceA;
                });
                
                sortedHotels.forEach(hotel => {
                    container.appendChild(hotel);
                });
            }
        }
        
        // Set up event listeners for filters
        document.getElementById('districtFilter').addEventListener('change', filterHotels);
        document.getElementById('starFilter').addEventListener('change', filterHotels);
        document.getElementById('priceSort').addEventListener('change', filterHotels);
        document.getElementById('hotelSearch').addEventListener('keyup', filterHotels);
        
        // Function to edit hotel (placeholder - implement as needed)
        function editHotel(hotelId) {
            // Redirect to edit hotel page or open modal
            console.log('Edit hotel with ID:', hotelId);
            // Implement as needed
        }
        
        // Function to view hotel bookings (placeholder - implement as needed)
        function viewBookings(hotelId) {
            // Redirect to hotel bookings page
            window.location.href = `hotel-bookings.php?hotel_id=${hotelId}`;
        }
        
        // Set hotel ID in delete modal
        document.getElementById('deleteConfirmModal').addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const hotelId = button.getAttribute('data-id');
            const hotelName = button.getAttribute('data-name');
            
            document.getElementById('delete-hotel-id').value = hotelId;
            document.getElementById('delete-hotel-name').textContent = hotelName;
        });
    </script>
</body>
</html>