<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
require_once '../../config/db_connect.php';

if (!isset($_GET['hotel_id'])) {
    header("Location: book-hotel.php");
    exit();
}

$hotel_id = $_GET['hotel_id'];
$stmt = $conn->prepare("SELECT * FROM hotels WHERE hotel_id = ? AND available_rooms > 0");
$stmt->bind_param("i", $hotel_id);
$stmt->execute();
$result = $stmt->get_result();
$hotel = $result->fetch_assoc();

if (!$hotel) {
    header("Location: book-hotel.php");
    exit();
}

$page_title = "Book Hotel";
include 'includes/header.php';
?>
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
    
    .hotel-image-container img {
        border-radius: 0.5rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    
    .hotel-details i {
        font-size: 0.875rem;
        width: 16px;
        color: #6c757d;
    }
    
    .hotel-details .mb-2 {
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
    
    textarea.form-control {
        min-height: 100px;
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
    
    .star-rating i {
        font-size: 0.875rem;
        color: #ffc107;
        margin-right: 0.125rem;
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
                    <h1 class="h3 mb-0">Book a Hotel</h1>
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
                                <h3>Hotel Details</h3>
                            </div>
                            <div class="card-body">
                                <div class="row g-4 mb-4">
                                    <div class="col-md-5">
                                        <div class="hotel-image-container">
                                            <img src="<?php echo htmlspecialchars($hotel['imageUrl']); ?>" 
                                                 class="img-fluid"
                                                 style="width: 100%; height: 200px; object-fit: cover;"
                                                 onerror="this.src='https://via.placeholder.com/400x200?text=No+Image'"
                                                 alt="<?php echo htmlspecialchars($hotel['name']); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-7">
                                        <h4 class="h5 mb-3"><?php echo htmlspecialchars($hotel['name']); ?></h4>
                                        <div class="hotel-details">
                                            <div class="mb-2">
                                                <i class="fas fa-map-marker-alt"></i>
                                                <span class="ms-2"><?php echo htmlspecialchars($hotel['district']); ?></span>
                                            </div>
                                            <div class="mb-2 star-rating">
                                                <?php for($i = 0; $i < $hotel['star_rating']; $i++): ?>
                                                    <i class="fas fa-star"></i>
                                                <?php endfor; ?>
                                            </div>
                                            <div class="mb-2">
                                                <i class="fas fa-bed"></i>
                                                <span class="ms-2"><?php echo htmlspecialchars($hotel['available_rooms']); ?> rooms available</span>
                                            </div>
                                            <div class="price-tag mt-3">
                                                <small class="text-muted d-block mb-1">Price per night</small>
                                                <h5 class="mb-0">
                                                    <i class="fas fa-tag"></i>
                                                    Rs. <?php echo number_format($hotel['price_per_night'], 2); ?>
                                                </h5>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <form action="process-hotel-booking.php" method="POST" class="booking-form" id="bookingForm">
                                    <input type="hidden" name="hotel_id" value="<?php echo $hotel_id; ?>">
                                    
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="check_in_date" class="form-label">
                                                    <i class="fas fa-calendar-plus"></i>
                                                    Check-in Date
                                                </label>
                                                <input type="date" class="form-control" id="check_in_date" 
                                                       name="check_in_date" required min="<?php echo date('Y-m-d'); ?>">
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="check_out_date" class="form-label">
                                                    <i class="fas fa-calendar-minus"></i>
                                                    Check-out Date
                                                </label>
                                                <input type="date" class="form-control" id="check_out_date" 
                                                       name="check_out_date" required min="<?php echo date('Y-m-d'); ?>">
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="room_count" class="form-label">
                                                    <i class="fas fa-door-closed"></i>
                                                    Number of Rooms
                                                </label>
                                                <input type="number" class="form-control" id="room_count" 
                                                       name="room_count" required min="1" 
                                                       max="<?php echo $hotel['available_rooms']; ?>">
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="guest_count" class="form-label">
                                                    <i class="fas fa-users"></i>
                                                    Number of Guests
                                                </label>
                                                <input type="number" class="form-control" id="guest_count" 
                                                       name="guest_count" required min="1">
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="form-group">
                                                <label for="special_requests" class="form-label">
                                                    <i class="fas fa-comment-alt"></i>
                                                    Special Requests
                                                </label>
                                                <textarea class="form-control" id="special_requests" 
                                                          name="special_requests" 
                                                          placeholder="Any special requests or preferences?"></textarea>
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
                                        <a href="book-hotel.php" class="btn btn-light">
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
        document.getElementById('check_in_date').addEventListener('change', calculateTotal);
        document.getElementById('check_out_date').addEventListener('change', calculateTotal);
        document.getElementById('room_count').addEventListener('change', calculateTotal);

        function calculateTotal() {
            const checkIn = new Date(document.getElementById('check_in_date').value);
            const checkOut = new Date(document.getElementById('check_out_date').value);
            const roomCount = parseInt(document.getElementById('room_count').value) || 0;
            
            if (checkIn && checkOut && roomCount > 0) {
                const diffTime = Math.abs(checkOut - checkIn);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                
                if (diffDays > 0) {
                    const pricePerNight = <?php echo $hotel['price_per_night']; ?>;
                    const total = diffDays * pricePerNight * roomCount;
                    document.getElementById('totalCost').textContent = `Rs. ${total.toFixed(2)}`;
                }
            }
        }

        document.getElementById('bookingForm').addEventListener('submit', function(e) {
            const checkIn = new Date(document.getElementById('check_in_date').value);
            const checkOut = new Date(document.getElementById('check_out_date').value);
            
            if (checkOut <= checkIn) {
                e.preventDefault();
                alert('Check-out date must be after check-in date');
            }
            
            const roomCount = parseInt(document.getElementById('room_count').value);
            const maxRooms = <?php echo $hotel['available_rooms']; ?>;
            
            if (roomCount > maxRooms) {
                e.preventDefault();
                alert(`Maximum available rooms is ${maxRooms}`);
            }
        });
    </script>
</body>
</html> 