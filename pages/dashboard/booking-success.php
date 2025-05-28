<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Process booking after payment
if (isset($_SESSION['booking_details']) && !isset($_SESSION['booking_processed'])) {
    // Include the payment processing file to handle database insertion
    include 'process-booking-payment.php';
    
    $booking_type = $_SESSION['booking_details']['type'];
    
    // Generate a booking reference number if not already set
    if (!isset($_SESSION['booking_reference'])) {
        $booking_ref = strtoupper(substr(md5(time() . $_SESSION['user_id']), 0, 8));
        $_SESSION['booking_reference'] = $booking_ref;
    } else {
        $booking_ref = $_SESSION['booking_reference'];
    }
    
    // Mark booking as processed to prevent duplicate processing
    $_SESSION['booking_processed'] = true;
    
} elseif (isset($_SESSION['booking_details']) && isset($_SESSION['booking_processed'])) {
    // Booking already processed, just get the reference
    $booking_ref = $_SESSION['booking_reference'];
    $booking_type = $_SESSION['booking_details']['type'];
} else {
    // If no booking details, redirect to dashboard
    header("Location: dashboard.php");
    exit();
}

$page_title = "Booking Confirmation";
include 'includes/header.php';
?>

<div class="main-content">
    <div class="container py-5">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card success-card">
                    <div class="card-body text-center p-5">
                        <div class="success-checkmark">
                            <div class="check-icon">
                                <span class="icon-line line-tip"></span>
                                <span class="icon-line line-long"></span>
                                <div class="icon-circle"></div>
                                <div class="icon-fix"></div>
                            </div>
                        </div>
                        
                        <h2 class="mt-4 mb-3">Payment Successful!</h2>
                        <p class="text-muted mb-4">Your booking has been confirmed and your payment was processed successfully.</p>
                        
                        <div class="booking-reference mb-4">
                            <h5>Booking Reference</h5>
                            <div class="reference-number">
                                <?php echo $booking_ref; ?>
                            </div>
                            <p class="small text-muted mt-2">Please save this reference number for your records</p>
                        </div>
                        
                        <div class="confirmation-details mb-4">
                            <div class="alert alert-light p-4 text-start">
                                <h5 class="mb-3">Booking Details</h5>
                                
                                <?php if (isset($_SESSION['booking_details'])): ?>
                                <?php $details = $_SESSION['booking_details']; ?>
                                
                                <?php if ($details['type'] === 'hotel'): ?>
                                <div class="row mb-2">
                                    <div class="col-5">Hotel:</div>
                                    <div class="col-7 fw-bold"><?php echo $details['hotel_name']; ?></div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-5">Check-in Date:</div>
                                    <div class="col-7"><?php echo $details['check_in_date']; ?></div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-5">Check-out Date:</div>
                                    <div class="col-7"><?php echo $details['check_out_date']; ?></div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-5">Rooms:</div>
                                    <div class="col-7"><?php echo $details['room_count']; ?></div>
                                </div>
                                
                                <?php elseif ($details['type'] === 'guide'): ?>
                                <div class="row mb-2">
                                    <div class="col-5">Guide:</div>
                                    <div class="col-7 fw-bold"><?php echo $details['guide_name']; ?></div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-5">Tour Date:</div>
                                    <div class="col-7"><?php echo $details['tour_date']; ?></div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-5">Duration:</div>
                                    <div class="col-7"><?php echo $details['duration_days']; ?> days</div>
                                </div>
                                
                                <?php elseif ($details['type'] === 'vehicle'): ?>
                                <div class="row mb-2">
                                    <div class="col-5">Vehicle:</div>
                                    <div class="col-7 fw-bold"><?php echo $details['vehicle_model']; ?></div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-5">Pickup Date:</div>
                                    <div class="col-7"><?php echo $details['pickup_date']; ?></div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-5">Return Date:</div>
                                    <div class="col-7"><?php echo $details['return_date']; ?></div>
                                </div>
                                <?php endif; ?>
                                
                                <div class="row mt-3 pt-3 border-top">
                                    <div class="col-5 fw-bold">Total Amount:</div>
                                    <div class="col-7 fw-bold fs-5">Rs. <?php echo number_format($details['total_cost'], 2); ?></div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="email-notification mb-4">
                            <div class="d-flex align-items-center justify-content-center text-muted">
                                <i class="fas fa-envelope me-2"></i>
                                <span>A confirmation email has been sent to your registered email address</span>
                            </div>
                        </div>
                        
                        <div class="action-buttons">
                            <a href="booking-history.php" class="btn btn-primary me-3">
                                <i class="fas fa-list me-2"></i>View Your Bookings
                            </a>
                            <a href="dashboard.php" class="btn btn-outline-secondary">
                                <i class="fas fa-home me-2"></i>Back to Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.main-content {
    margin-left: 280px;
    padding: 2rem;
    min-height: 100vh;
    background-color: #f8f9fa;
}

.success-card {
    border-radius: 1rem;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.success-checkmark {
    margin: 0 auto;
    width: 80px;
    height: 80px;
    position: relative;
}

.check-icon {
    width: 80px;
    height: 80px;
    position: relative;
    border-radius: 50%;
    box-sizing: content-box;
    border: 4px solid #4CAF50;
}

.check-icon::before {
    top: 3px;
    left: -2px;
    width: 30px;
    transform-origin: 100% 50%;
    border-radius: 100px 0 0 100px;
}

.check-icon::after {
    top: 0;
    left: 30px;
    width: 60px;
    transform-origin: 0 50%;
    border-radius: 0 100px 100px 0;
    animation: rotate-circle 4.25s ease-in;
}

.check-icon::before, .check-icon::after {
    content: '';
    height: 100px;
    position: absolute;
    background: #f8f9fa;
    transform: rotate(-45deg);
}

.icon-line {
    height: 5px;
    background-color: #4CAF50;
    display: block;
    border-radius: 2px;
    position: absolute;
    z-index: 10;
}

.icon-line.line-tip {
    top: 46px;
    left: 14px;
    width: 25px;
    transform: rotate(45deg);
    animation: icon-line-tip 0.75s;
}

.icon-line.line-long {
    top: 38px;
    right: 8px;
    width: 47px;
    transform: rotate(-45deg);
    animation: icon-line-long 0.75s;
}

.icon-circle {
    top: -4px;
    left: -4px;
    z-index: 10;
    width: 80px;
    height: 80px;
    border-radius: 50%;
    position: absolute;
    box-sizing: content-box;
    border: 4px solid rgba(76, 175, 80, 0.5);
}

.icon-fix {
    top: 8px;
    width: 5px;
    left: 26px;
    z-index: 1;
    height: 85px;
    position: absolute;
    transform: rotate(-45deg);
    background-color: #f8f9fa;
}

.booking-reference {
    margin-top: 2rem;
}

.reference-number {
    background-color: #e8f5e9;
    color: #2e7d32;
    font-size: 1.5rem;
    font-weight: bold;
    letter-spacing: 2px;
    padding: 1rem;
    border-radius: 0.5rem;
    display: inline-block;
    min-width: 200px;
}

@keyframes rotate-circle {
    0% {
        transform: rotate(-45deg);
    }
    5% {
        transform: rotate(-45deg);
    }
    12% {
        transform: rotate(-405deg);
    }
    100% {
        transform: rotate(-405deg);
    }
}

@keyframes icon-line-tip {
    0% {
        width: 0;
        left: 1px;
        top: 19px;
    }
    54% {
        width: 0;
        left: 1px;
        top: 19px;
    }
    70% {
        width: 50px;
        left: -8px;
        top: 37px;
    }
    84% {
        width: 17px;
        left: 21px;
        top: 48px;
    }
    100% {
        width: 25px;
        left: 14px;
        top: 45px;
    }
}

@keyframes icon-line-long {
    0% {
        width: 0;
        right: 46px;
        top: 54px;
    }
    65% {
        width: 0;
        right: 46px;
        top: 54px;
    }
    84% {
        width: 55px;
        right: 0px;
        top: 35px;
    }
    100% {
        width: 47px;
        right: 8px;
        top: 38px;
    }
}
</style>

<script>
// Clear booking session data when user navigates away from success page
window.addEventListener('beforeunload', function() {
    // Send a request to clear the booking session data
    fetch('clear-booking-session.php', {
        method: 'POST',
        keepalive: true
    });
});

// Also clear when clicking on navigation buttons
document.addEventListener('DOMContentLoaded', function() {
    const navButtons = document.querySelectorAll('a[href*="booking-history"], a[href*="dashboard"]');
    navButtons.forEach(button => {
        button.addEventListener('click', function() {
            fetch('clear-booking-session.php', {
                method: 'POST',
                keepalive: true
            });
        });
    });
});
</script>


