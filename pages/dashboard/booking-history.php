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
.dashboard-container {
    background-color: #f8f9fa;
}

.main-content {
    padding: 1.5rem;
}

.page-header {
    background: #fff;
    padding: 1rem;
    border-radius: 0.5rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    margin-bottom: 1.5rem;
}

.stats-card {
    border: none;
    border-radius: 0.75rem;
    transition: transform 0.2s;
    margin-bottom: 1rem;
}

.stats-card:hover {
    transform: translateY(-3px);
}

.stats-card .card-body {
    padding: 1.25rem;
}

.stats-card .icon-wrapper {
    width: 45px;
    height: 45px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255,255,255,0.2);
}

.stats-card .icon-wrapper i {
    font-size: 1.25rem;
}

.stats-card h6 {
    font-size: 0.875rem;
    font-weight: 500;
    margin-bottom: 0.5rem;
}

.stats-card h2 {
    font-size: 1.75rem;
    font-weight: 600;
    margin-bottom: 0;
}

.booking-tabs .nav-link {
    color: #6c757d;
    padding: 0.75rem 1.25rem;
    font-weight: 500;
    border: none;
    position: relative;
}

.booking-tabs .nav-link.active {
    color: #0d6efd;
    background: none;
}

.booking-tabs .nav-link.active::after {
    content: '';
    position: absolute;
    bottom: -1px;
    left: 0;
    right: 0;
    height: 2px;
    background: #0d6efd;
}

.booking-tabs .nav-link i {
    font-size: 1rem;
    margin-right: 0.5rem;
}

.search-box {
    max-width: 300px;
    border-radius: 0.5rem;
    overflow: hidden;
}

.search-box .form-control {
    border: 1px solid #e0e0e0;
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
}

.search-box .input-group-text {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-left: none;
}

.search-box .input-group-text i {
    font-size: 0.875rem;
    color: #6c757d;
}

.action-btn {
    padding: 0.5rem;
    font-size: 0.875rem;
}

.action-btn i {
    font-size: 0.875rem;
}

.badge {
    font-weight: 500;
    padding: 0.5rem 0.75rem;
}

.badge i {
    font-size: 0.625rem;
}

.table th {
    font-weight: 600;
    font-size: 0.875rem;
    padding: 1rem;
}

.table td {
    padding: 1rem;
    vertical-align: middle;
}

.empty-state {
    padding: 3rem 1.5rem;
}

.empty-state img {
    width: 120px;
    height: 120px;
    margin-bottom: 1.5rem;
}

.empty-state h4 {
    font-size: 1.25rem;
    color: #495057;
    margin-bottom: 0.75rem;
}

.empty-state p {
    font-size: 0.875rem;
    color: #6c757d;
    margin-bottom: 1.5rem;
}

.modal-content {
    border-radius: 0.75rem;
}

.modal-header {
    padding: 1.25rem;
    border-bottom: 1px solid #e9ecef;
}

.modal-body {
    padding: 1.5rem;
}

.modal-footer {
    padding: 1.25rem;
    border-top: 1px solid #e9ecef;
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
                    <div class="user-profile d-flex align-items-center">
                        <span class="me-2" style="font-size: 0.875rem;">
                            <?php echo htmlspecialchars($_SESSION['fullname']); ?>
                        </span>
                        <i class="fas fa-user-circle" style="font-size: 1.25rem;"></i>
                    </div>
                </div>
            </div>

            <?php if (isset($_SESSION['booking_success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" 
                     style="border-radius: 0.5rem; margin-bottom: 1.5rem;" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php 
                    echo htmlspecialchars($_SESSION['booking_message'] ?? 'Booking completed successfully!');
                    unset($_SESSION['booking_success']);
                    unset($_SESSION['booking_message']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card stats-card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-2">Vehicle Bookings</h6>
                                    <?php
                                    $stmt = $conn->prepare("SELECT COUNT(*) FROM vehicle_bookings WHERE user_id = ?");
                                    $stmt->bind_param("i", $_SESSION['user_id']);
                                    $stmt->execute();
                                    $count = $stmt->get_result()->fetch_row()[0];
                                    ?>
                                    <h2><?php echo $count; ?></h2>
                                </div>
                                <div class="icon-wrapper">
                                    <i class="fas fa-car"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stats-card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-2">Guide Bookings</h6>
                                    <?php
                                    $stmt = $conn->prepare("SELECT COUNT(*) FROM guide_bookings WHERE user_id = ?");
                                    $stmt->bind_param("i", $_SESSION['user_id']);
                                    $stmt->execute();
                                    $count = $stmt->get_result()->fetch_row()[0];
                                    ?>
                                    <h2><?php echo $count; ?></h2>
                                </div>
                                <div class="icon-wrapper">
                                    <i class="fas fa-user-tie"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stats-card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-2">Hotel Bookings</h6>
                                    <?php
                                    $stmt = $conn->prepare("SELECT COUNT(*) FROM hotel_bookings WHERE user_id = ?");
                                    $stmt->bind_param("i", $_SESSION['user_id']);
                                    $stmt->execute();
                                    $count = $stmt->get_result()->fetch_row()[0];
                                    ?>
                                    <h2><?php echo $count; ?></h2>
                                </div>
                                <div class="icon-wrapper">
                                    <i class="fas fa-hotel"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stats-card bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-2">Active Bookings</h6>
                                    <?php
                                    $today = date('Y-m-d');
                                    $stmt = $conn->prepare("SELECT 
                                        (SELECT COUNT(*) FROM vehicle_bookings WHERE user_id = ? AND return_date >= ?) +
                                        (SELECT COUNT(*) FROM guide_bookings WHERE user_id = ? AND tour_date >= ?) +
                                        (SELECT COUNT(*) FROM hotel_bookings WHERE user_id = ? AND check_out_date >= ?)
                                    ");
                                    $stmt->bind_param("isisis", $_SESSION['user_id'], $today, $_SESSION['user_id'], $today, $_SESSION['user_id'], $today);
                                    $stmt->execute();
                                    $count = $stmt->get_result()->fetch_row()[0];
                                    ?>
                                    <h2><?php echo $count; ?></h2>
                                </div>
                                <div class="icon-wrapper">
                                    <i class="fas fa-calendar-check"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <ul class="nav nav-tabs card-header-tabs booking-tabs">
                        <li class="nav-item">
                            <a class="nav-link <?php echo $active_tab == 'vehicles' ? 'active' : ''; ?>" 
                               href="?tab=vehicles">
                               <i class="fas fa-car"></i>Vehicles
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $active_tab == 'guides' ? 'active' : ''; ?>" 
                               href="?tab=guides">
                               <i class="fas fa-user-tie"></i>Guides
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $active_tab == 'hotels' ? 'active' : ''; ?>" 
                               href="?tab=hotels">
                               <i class="fas fa-hotel"></i>Hotels
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-0">
                    <div class="tab-content">
                        <?php if ($active_tab == 'vehicles'): ?>
                            <?php include 'includes/vehicle-bookings.php'; ?>
                        <?php elseif ($active_tab == 'guides'): ?>
                            <?php include 'includes/guide-bookings.php'; ?>
                        <?php elseif ($active_tab == 'hotels'): ?>
                            <?php include 'includes/hotel-bookings.php'; ?>
                        <?php endif; ?>
                    </div>
                </div>
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
    </script>
</body>
</html>