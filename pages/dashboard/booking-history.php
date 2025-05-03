<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
require_once '../../config/db_connect.php';
$page_title = "Booking History";
include 'includes/header.php';

// Get active tab from URL parameter, default to vehicles
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'vehicles';
?>
<style>
/* Reset and Base Styles */
.dashboard-container {
    background-color: #ffffff;
    min-height: 100vh;
    display: flex;
}

.main-content {
    padding: 2rem;
    width: 100%;
}

/* Header Styling */
.page-header {
    background: #fff;
    padding: 1.5rem;
    border-radius: 0.75rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    margin-bottom: 2rem;
}

.page-header h4 {
    font-size: 1.35rem;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 0.3rem;
}

.user-profile {
    display: flex;
    align-items: center;
}

.user-profile i {
    font-size: 1.5rem;
    color: #4a5568;
}

/* Modern Tab Navigation */
.booking-nav {
    background-color: #f8f9fa;
    border-radius: 0.75rem;
    padding: 0.5rem;
    margin-bottom: 2rem;
    display: flex;
    overflow-x: auto;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}

.booking-nav .nav-item {
    flex: 1;
    text-align: center;
}

.booking-nav .nav-link {
    color: #6c757d;
    padding: 0.75rem 1rem;
    border-radius: 0.5rem;
    font-weight: 500;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    border: none;
    background: transparent;
}

.booking-nav .nav-link i {
    margin-right: 0.5rem;
    font-size: 1.1rem;
}

.booking-nav .nav-link.active {
    background-color: #4e73df;
    color: white;
    box-shadow: 0 2px 5px rgba(78, 115, 223, 0.2);
}

.booking-nav .nav-link:hover:not(.active) {
    background-color: rgba(78, 115, 223, 0.1);
    color: #4e73df;
}

/* Search and Action Bar */
.action-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.search-container {
    position: relative;
    flex: 1;
    max-width: 400px;
}

.search-container input {
    width: 100%;
    padding: 0.75rem 1rem 0.75rem 2.5rem;
    border: 1px solid #e2e8f0;
    border-radius: 0.5rem;
    font-size: 0.95rem;
    transition: all 0.2s;
    background-color: #fff;
}

.search-container input:focus {
    border-color: #4e73df;
    box-shadow: 0 0 0 3px rgba(78, 115, 223, 0.15);
    outline: none;
}

.search-container i {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #a0aec0;
}

.new-booking-btn {
    background-color: #4CAF50;
    color: white;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 0.5rem;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.2s;
    box-shadow: 0 2px 5px rgba(76, 175, 80, 0.2);
}

.new-booking-btn:hover {
    background-color: #43a047;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(76, 175, 80, 0.3);
}

.new-booking-btn i {
    font-size: 1rem;
}

/* Booking Card Styling */
.booking-card {
    background-color: #fff;
    border-radius: 0.75rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    margin-bottom: 1.5rem;
    overflow: hidden;
    transition: all 0.2s;
}

.booking-card:hover {
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.booking-header {
    padding: 1.25rem;
    border-bottom: 1px solid #f0f0f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: #f8f9fa;
}

.booking-title {
    font-weight: 600;
    font-size: 1.1rem;
    color: #2d3748;
    margin: 0;
}

.booking-content {
    padding: 0;
}

.booking-details {
    display: flex;
    flex-wrap: wrap;
}

.booking-image {
    width: 120px;
    height: 120px;
    padding: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

.booking-image img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    border-radius: 0.5rem;
}

.booking-info {
    flex: 1;
    padding: 1.25rem;
    min-width: 250px;
}

.booking-item {
    font-weight: 600;
    font-size: 1.1rem;
    margin-bottom: 0.5rem;
    color: #2d3748;
}

.booking-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 1.5rem;
    margin-bottom: 0.75rem;
}

.booking-meta-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #4a5568;
    font-size: 0.9rem;
}

.booking-meta-item i {
    color: #4e73df;
    font-size: 1rem;
}

.booking-cost {
    flex: 0 0 200px;
    padding: 1.25rem;
    border-left: 1px solid #f0f0f0;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.cost-label {
    font-size: 0.9rem;
    color: #718096;
    margin-bottom: 0.25rem;
}

.cost-value {
    font-size: 1.25rem;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 0.5rem;
}

.booking-status {
    flex: 0 0 150px;
    padding: 1.25rem;
    border-left: 1px solid #f0f0f0;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}

.booking-actions {
    flex: 0 0 150px;
    padding: 1.25rem;
    border-left: 1px solid #f0f0f0;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    gap: 0.75rem;
}

/* Status Badges */
.badge {
    font-size: 0.85rem;
    font-weight: 500;
    padding: 0.5rem 1rem;
    border-radius: 2rem;
    letter-spacing: 0.3px;
    display: inline-block;
    text-align: center;
}

.badge-pending {
    background-color: #fff3dc;
    color: #cb8a14;
}

.badge-confirmed {
    background-color: #dcf5e7;
    color: #0d8a47;
}

.badge-cancelled {
    background-color: #fde8e8;
    color: #c81e1e;
}

.badge-completed {
    background-color: #e6effe;
    color: #1a56db;
}

.badge-upcoming {
    background-color: #e6effe;
    color: #1a56db;
}

/* Action Buttons */
.action-btn {
    width: 100%;
    padding: 0.6rem 0.8rem;
    font-size: 0.9rem;
    border-radius: 0.5rem;
    transition: all 0.2s ease;
    border: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    cursor: pointer;
}

.action-btn.btn-primary {
    background: #4e73df;
    color: white;
}

.action-btn.btn-primary:hover {
    background: #2e59d9;
    transform: translateY(-1px);
}

.action-btn.btn-danger {
    background: #e74a3b;
    color: white;
}

.action-btn.btn-danger:hover {
    background: #d52a1a;
    transform: translateY(-1px);
}

.action-btn i {
    font-size: 1rem;
}

/* Empty State Styling */
.empty-state {
    padding: 4rem 2rem;
    text-align: center;
    background: #fff;
    border-radius: 0.75rem;
    margin: 1rem 0;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.empty-state img {
    width: 120px;
    height: 120px;
    margin-bottom: 1.5rem;
    opacity: 0.7;
}

.empty-state h3 {
    color: #2d3748;
    font-size: 1.25rem;
    margin-bottom: 1rem;
}

.empty-state p {
    color: #718096;
    font-size: 1rem;
    max-width: 400px;
    margin: 0 auto;
}

/* Card styling */
.card {
    border: none;
    border-radius: 0.75rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    overflow: hidden;
}

.card-header {
    padding: 1rem 1.5rem;
    background-color: #fff;
    border-bottom: 1px solid #f0f0f0;
}

.card-body {
    padding: 1.5rem;
}

/* Alert styling */
.alert {
    border-radius: 0.5rem;
    padding: 1rem 1.5rem;
    margin-bottom: 1.5rem;
    border: none;
}

.alert-success {
    background-color: #dcf5e7;
    color: #0d8a47;
}

/* Guide specific styles */
.guide-image {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    overflow: hidden;
}

.guide-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.guide-languages {
    display: flex;
    gap: 0.5rem;
    margin-top: 0.5rem;
}

.guide-language {
    background-color: #f0f4f8;
    color: #4a5568;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.8rem;
}

.guide-rating {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    color: #f6ad55;
}

/* Responsive Adjustments */
@media (max-width: 992px) {
    .booking-details {
        flex-direction: column;
    }
    
    .booking-cost, .booking-status, .booking-actions {
        flex: 1 1 100%;
        border-left: none;
        border-top: 1px solid #f0f0f0;
        padding: 1rem 1.25rem;
    }
    
    .booking-cost {
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
    }
    
    .booking-status {
        align-items: flex-start;
    }
    
    .booking-actions {
        flex-direction: row;
        justify-content: flex-start;
    }
    
    .action-btn {
        width: auto;
    }
}

@media (max-width: 768px) {
    .main-content {
        padding: 1rem;
    }
    
    .page-header {
        padding: 1.25rem;
        margin-bottom: 1.5rem;
    }
    
    .booking-nav {
        padding: 0.25rem;
    }
    
    .booking-nav .nav-link {
        padding: 0.5rem 0.75rem;
        font-size: 0.9rem;
    }
    
    .booking-nav .nav-link i {
        margin-right: 0.25rem;
    }
    
    .booking-image {
        width: 80px;
        height: 80px;
    }
    
    .booking-meta {
        gap: 1rem;
    }
    
    .action-bar {
        flex-direction: column;
        align-items: stretch;
    }
    
    .search-container {
        max-width: 100%;
    }
}
</style>

<body>
    <div class="dashboard-container">
        <?php include 'includes/sidebar.php'; ?>
        <div class="main-content">
            <div class="page-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-1">My Bookings</h4>
                        <p class="text-muted mb-0" style="font-size: 0.875rem;">
                            Manage all your travel bookings in one place
                        </p>
                    </div>
                    <div class="user-profile">
                        <span class="me-2" style="font-size: 0.875rem;">
                            <?php echo htmlspecialchars($_SESSION['fullname']); ?>
                        </span>
                        <i class="fas fa-user-circle"></i>
                    </div>
                </div>
            </div>

            <?php if (isset($_GET['success']) || isset($_SESSION['booking_success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php 
                    echo htmlspecialchars($_SESSION['booking_message'] ?? 'Booking completed successfully!');
                    unset($_SESSION['booking_success']);
                    unset($_SESSION['booking_message']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Modern Tab Navigation -->
            <ul class="nav booking-nav">
                <li class="nav-item">
                    <a class="nav-link <?php echo $active_tab == 'vehicles' ? 'active' : ''; ?>" 
                       href="?tab=vehicles">
                       <i class="fas fa-car"></i> Vehicles
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $active_tab == 'guides' ? 'active' : ''; ?>" 
                       href="?tab=guides">
                       <i class="fas fa-user-tie"></i> Guides
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $active_tab == 'hotels' ? 'active' : ''; ?>" 
                       href="?tab=hotels">
                       <i class="fas fa-hotel"></i> Hotels
                    </a>
                </li>
            </ul>

            <!-- Search and Action Bar -->
            <div class="action-bar">
                <div class="search-container">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchBooking" placeholder="Search <?php echo ucfirst($active_tab); ?> bookings..." class="form-control">
                </div>
                <a href="book-<?php echo substr($active_tab, 0, -1); ?>.php" class="new-booking-btn">
                    <i class="fas fa-plus"></i> New <?php echo ucfirst(substr($active_tab, 0, -1)); ?> Booking
                </a>
            </div>

            <!-- Tab Content -->
            <div class="tab-content">
                <?php if ($active_tab == 'vehicles'): ?>
                    <!-- Vehicle Bookings -->
                    <?php
                    $stmt = $conn->prepare("SELECT vb.*, v.model, v.type, v.imageUrl, v.license_plate 
                                           FROM vehicle_bookings vb 
                                           JOIN vehicles v ON vb.vehicle_id = v.vehicle_id 
                                           WHERE vb.user_id = ? 
                                           ORDER BY vb.pickup_date DESC");
                    $stmt->bind_param("i", $_SESSION['user_id']);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows > 0):
                        while ($booking = $result->fetch_assoc()):
                            // Calculate status
                            $today = new DateTime();
                            $pickup_date = new DateTime($booking['pickup_date']);
                            $return_date = new DateTime($booking['return_date']);
                            
                            if (isset($booking['cancelled']) && $booking['cancelled']) {
                                $status = "cancelled";
                                $status_text = "Cancelled";
                            } elseif ($return_date < $today) {
                                $status = "completed";
                                $status_text = "Completed";
                            } elseif ($pickup_date <= $today && $return_date >= $today) {
                                $status = "confirmed";
                                $status_text = "Active";
                            } else {
                                $status = "upcoming";
                                $status_text = "Upcoming";
                            }
                            
                            // Calculate duration
                            $interval = $pickup_date->diff($return_date);
                            $days = $interval->days + 1;
                    ?>
                    <div class="booking-card">
                        <div class="booking-content">
                            <div class="booking-details">
                                <div class="booking-image">
                                    <?php if (!empty($booking['imageUrl'])): ?>
                                        <img src="<?php echo htmlspecialchars($booking['imageUrl']); ?>" alt="<?php echo htmlspecialchars($booking['model']); ?>">
                                    <?php else: ?>
                                        <i class="fas fa-car fa-3x text-muted"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="booking-info">
                                    <div class="booking-item"><?php echo htmlspecialchars($booking['type'] . ' ' . $booking['model']); ?></div>
                                    <div class="booking-meta">
                                        <div class="booking-meta-item">
                                            <i class="fas fa-hashtag"></i>
                                            <span><?php echo htmlspecialchars($booking['license_plate']); ?></span>
                                        </div>
                                        <div class="booking-meta-item">
                                            <i class="fas fa-calendar-alt"></i>
                                            <span>Pickup: <?php echo date('M d, Y', strtotime($booking['pickup_date'])); ?></span>
                                        </div>
                                        <div class="booking-meta-item">
                                            <i class="fas fa-calendar-check"></i>
                                            <span>Return: <?php echo date('M d, Y', strtotime($booking['return_date'])); ?></span>
                                        </div>
                                        <div class="booking-meta-item">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <span><?php echo htmlspecialchars($booking['pickup_location']); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="booking-cost">
                                    <div>
                                        <div class="cost-label">Duration:</div>
                                        <div class="cost-value"><?php echo $days; ?> days</div>
                                    </div>
                                    <div>
                                        <div class="cost-label">Total Cost:</div>
                                        <div class="cost-value">Rs. <?php echo number_format($booking['total_cost'], 2); ?></div>
                                    </div>
                                </div>
                                <div class="booking-status">
                                    <span class="badge badge-<?php echo $status; ?>"><?php echo $status_text; ?></span>
                                </div>
                                <div class="booking-actions">
                                    <?php if ($status != "cancelled" && $status != "completed"): ?>
                                        <button class="action-btn btn-primary" onclick="modifyBooking(<?php echo $booking['booking_id']; ?>, 'vehicle')">
                                            <i class="fas fa-edit"></i> Modify
                                        </button>
                                        <button class="action-btn btn-danger" onclick="confirmDelete(<?php echo $booking['booking_id']; ?>, 'vehicle')">
                                            <i class="fas fa-times"></i> Cancel
                                        </button>
                                    <?php else: ?>
                                        <button class="action-btn btn-primary" onclick="window.location.href='booking-details.php?type=vehicle&id=<?php echo $booking['booking_id']; ?>'">
                                            <i class="fas fa-eye"></i> View Details
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php 
                        endwhile;
                    else:
                    ?>
                    <div class="empty-state">
                        <img src="../../assets/images/empty-bookings.svg" alt="No bookings">
                        <h3>No Vehicle Bookings Found</h3>
                        <p>You haven't made any vehicle bookings yet. Start exploring our vehicle options and plan your journey today!</p>
                        <a href="book-vehicle.php" class="btn btn-primary mt-3">Book a Vehicle</a>
                    </div>
                    <?php endif; ?>
                <?php elseif ($active_tab == 'guides'): ?>
                    <!-- Guide Bookings -->
                    <?php
                    $stmt = $conn->prepare("SELECT gb.*, g.name as fullname, g.imageUrl as profile_image, g.language as languages, 
                                           g.experience_years as experience, g.specialization as tour_type, gb.duration_days as duration
                                           FROM guide_bookings gb 
                                           JOIN guides g ON gb.guide_id = g.guide_id 
                                           WHERE gb.user_id = ? 
                                           ORDER BY gb.tour_date DESC");
                    $stmt->bind_param("i", $_SESSION['user_id']);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows > 0):
                        while ($booking = $result->fetch_assoc()):
                            // Calculate status
                            $today = new DateTime();
                            $tour_date = new DateTime($booking['tour_date']);
                            
                            if (isset($booking['cancelled']) && $booking['cancelled']) {
                                $status = "cancelled";
                                $status_text = "Cancelled";
                            } elseif ($tour_date < $today) {
                                $status = "completed";
                                $status_text = "Completed";
                            } else {
                                $status = "upcoming";
                                $status_text = "Upcoming";
                            }
                            
                            // Parse languages
                            $languages = explode(',', $booking['languages']);
                    ?>
                    <div class="booking-card">
                        <div class="booking-content">
                            <div class="booking-details">
                                <div class="booking-image">
                                    <?php if (!empty($booking['profile_image'])): ?>
                                        <img src="<?php echo htmlspecialchars($booking['profile_image']); ?>" alt="<?php echo htmlspecialchars($booking['fullname']); ?>" class="guide-image">
                                    <?php else: ?>
                                        <i class="fas fa-user-tie fa-3x text-muted"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="booking-info">
                                    <div class="booking-item"><?php echo htmlspecialchars($booking['fullname']); ?></div>
                                    <div class="booking-meta">
                                        <div class="booking-meta-item">
                                            <i class="fas fa-calendar-alt"></i>
                                            <span>Tour Date: <?php echo date('M d, Y', strtotime($booking['tour_date'])); ?></span>
                                        </div>
                                        <div class="booking-meta-item">
                                            <i class="fas fa-users"></i>
                                            <span>Group Size: <?php echo $booking['group_size']; ?> persons</span>
                                        </div>
                                        <div class="booking-meta-item">
                                            <i class="fas fa-map"></i>
                                            <span>Tour Type: <?php echo htmlspecialchars($booking['tour_type']); ?></span>
                                        </div>
                                    </div>
                                    <div class="guide-languages">
                                        <?php foreach ($languages as $language): ?>
                                            <span class="guide-language"><?php echo trim($language); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <div class="booking-cost">
                                    <div>
                                        <div class="cost-label">Duration:</div>
                                        <div class="cost-value"><?php echo $booking['duration']; ?> days</div>
                                    </div>
                                    <div>
                                        <div class="cost-label">Total Cost:</div>
                                        <div class="cost-value">Rs. <?php echo number_format($booking['total_cost'], 2); ?></div>
                                    </div>
                                </div>
                                <div class="booking-status">
                                    <span class="badge badge-<?php echo $status; ?>"><?php echo $status_text; ?></span>
                                </div>
                                <div class="booking-actions">
                                    <?php if ($status != "cancelled" && $status != "completed"): ?>
                                        <button class="action-btn btn-primary" onclick="modifyBooking(<?php echo $booking['booking_id']; ?>, 'guide')">
                                            <i class="fas fa-edit"></i> Modify
                                        </button>
                                        <button class="action-btn btn-danger" onclick="confirmDelete(<?php echo $booking['booking_id']; ?>, 'guide')">
                                            <i class="fas fa-times"></i> Cancel
                                        </button>
                                    <?php else: ?>
                                        <button class="action-btn btn-primary" onclick="window.location.href='booking-details.php?type=guide&id=<?php echo $booking['booking_id']; ?>'">
                                            <i class="fas fa-eye"></i> View Details
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php 
                        endwhile;
                    else:
                    ?>
                    <div class="empty-state">
                        <img src="../../assets/images/empty-bookings.svg" alt="No bookings">
                        <h3>No Guide Bookings Found</h3>
                        <p>You haven't booked any tour guides yet. Enhance your travel experience with our knowledgeable local guides!</p>
                        <a href="book-guide.php" class="btn btn-primary mt-3">Book a Guide</a>
                    </div>
                    <?php endif; ?>
                <?php elseif ($active_tab == 'hotels'): ?>
                    <!-- Hotel Bookings -->
                    <?php
                    $stmt = $conn->prepare("SELECT hb.*, h.name, h.imageUrl as image_url, h.district as location, h.star_rating 
                                           FROM hotel_bookings hb 
                                           JOIN hotels h ON hb.hotel_id = h.hotel_id 
                                           WHERE hb.user_id = ? 
                                           ORDER BY hb.check_in_date DESC");
                    $stmt->bind_param("i", $_SESSION['user_id']);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows > 0):
                        while ($booking = $result->fetch_assoc()):
                            // Calculate status
                            $today = new DateTime();
                            $check_in = new DateTime($booking['check_in_date']);
                            $check_out = new DateTime($booking['check_out_date']);
                            
                            if (isset($booking['cancelled']) && $booking['cancelled']) {
                                $status = "cancelled";
                                $status_text = "Cancelled";
                            } elseif ($check_out < $today) {
                                $status = "completed";
                                $status_text = "Completed";
                            } elseif ($check_in <= $today && $check_out >= $today) {
                                $status = "confirmed";
                                $status_text = "Active";
                            } else {
                                $status = "upcoming";
                                $status_text = "Upcoming";
                            }
                    ?>
                    <div class="booking-card">
                        <div class="booking-content">
                            <div class="booking-details">
                                <div class="booking-image">
                                    <?php if (!empty($booking['image_url'])): ?>
                                        <img src="<?php echo htmlspecialchars($booking['image_url']); ?>" alt="<?php echo htmlspecialchars($booking['name']); ?>">
                                    <?php else: ?>
                                        <i class="fas fa-hotel fa-3x text-muted"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="booking-info">
                                    <div class="booking-item"><?php echo htmlspecialchars($booking['name']); ?></div>
                                    <div class="booking-meta">
                                        <div class="booking-meta-item">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <span><?php echo htmlspecialchars($booking['location']); ?></span>
                                        </div>
                                        <div class="booking-meta-item">
                                            <i class="fas fa-star"></i>
                                            <span><?php echo htmlspecialchars($booking['star_rating']); ?> stars</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="booking-cost">
                                    <div>
                                        <div class="cost-label">Check-in:</div>
                                        <div class="cost-value"><?php echo date('M d, Y', strtotime($booking['check_in_date'])); ?></div>
                                    </div>
                                    <div>
                                        <div class="cost-label">Check-out:</div>
                                        <div class="cost-value"><?php echo date('M d, Y', strtotime($booking['check_out_date'])); ?></div>
                                    </div>
                                </div>
                                <div class="booking-status">
                                    <span class="badge badge-<?php echo $status; ?>"><?php echo $status_text; ?></span>
                                </div>
                                <div class="booking-actions">
                                    <?php if ($status != "cancelled" && $status != "completed"): ?>
                                        <button class="action-btn btn-primary" onclick="modifyBooking(<?php echo $booking['booking_id']; ?>, 'hotel')">
                                            <i class="fas fa-edit"></i> Modify
                                        </button>
                                        <button class="action-btn btn-danger" onclick="confirmDelete(<?php echo $booking['booking_id']; ?>, 'hotel')">
                                            <i class="fas fa-times"></i> Cancel
                                        </button>
                                    <?php else: ?>
                                        <button class="action-btn btn-primary" onclick="window.location.href='booking-details.php?type=hotel&id=<?php echo $booking['booking_id']; ?>'">
                                            <i class="fas fa-eye"></i> View Details
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php 
                        endwhile;
                    else:
                    ?>
                    <div class="empty-state">
                        <img src="../../assets/images/empty-bookings.svg" alt="No bookings">
                        <h3>No Hotel Bookings Found</h3>
                        <p>You haven't booked any hotels yet. Start exploring our hotel options and find the perfect place to stay!</p>
                        <a href="book-hotel.php" class="btn btn-primary mt-3">Book a Hotel</a>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Delete Booking Modal -->
    <div class="modal fade" id="deleteBookingModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                        Cancel Booking
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Are you sure you want to cancel this booking? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">
                        <i class="fas fa-times me-2"></i>Cancel Booking
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modify Booking Modal -->
    <div class="modal fade" id="modifyBookingModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-edit text-primary me-2"></i>
                        Modify Booking
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Form will be loaded dynamically -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Handle booking deletion
        let deleteBookingId = null;
        let deleteBookingType = null;

        function confirmDelete(id, type) {
            deleteBookingId = id;
            deleteBookingType = type;
            new bootstrap.Modal(document.getElementById('deleteBookingModal')).show();
        }

        document.getElementById('confirmDelete').addEventListener('click', function() {
            if (deleteBookingId && deleteBookingType) {
                window.location.href = `process-cancel-booking.php?type=${deleteBookingType}&id=${deleteBookingId}`;
            }
        });

        // Handle booking modification
        function modifyBooking(id, type) {
            fetch(`get-booking-details.php?type=${type}&id=${id}`)
                .then(response => response.text())
                .then(html => {
                    document.querySelector('#modifyBookingModal .modal-body').innerHTML = html;
                    new bootstrap.Modal(document.getElementById('modifyBookingModal')).show();
                });
        }

        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
        
        // Auto-dismiss alerts after 5 seconds
        window.addEventListener('DOMContentLoaded', (event) => {
            setTimeout(function() {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(alert => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 5000);
        });
    </script>
</body>
</html>