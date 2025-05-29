<?php
require_once 'includes/session_helper.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Check if there's booking information in the session
if (!isset($_SESSION['booking_details'])) {
    header("Location: dashboard.php");
    exit();
}

$booking_details = $_SESSION['booking_details'];
$page_title = "Payment Gateway";
include 'includes/header.php';
?>

<link rel="stylesheet" href="../../css/payment.css">
<link rel="stylesheet" href="../../css/payment-additions.css">

<div class="main-content">
    <div class="container py-4">
        <div class="row">
            <div class="col-lg-8 mx-auto">                <div class="card payment-card">
                    <div class="card-header bg-white">
                        <h3 class="mb-0 fs-5">
                            <i class="fas fa-credit-card me-2"></i> Secure Payment
                        </h3>
                    </div><div class="card-body">                        <div class="booking-summary mb-3">
                            <h5 class="section-title mb-2"><i class="fas fa-receipt me-2"></i>Booking Summary</h5>
                            <div class="summary-details p-0">
                                <?php if (isset($booking_details['type']) && $booking_details['type'] === 'hotel'): ?>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span>Hotel:</span>
                                    <span class="fw-bold"><?php echo $booking_details['hotel_name']; ?></span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span>Check-in Date:</span>
                                    <span><?php echo $booking_details['check_in_date']; ?></span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span>Check-out Date:</span>
                                    <span><?php echo $booking_details['check_out_date']; ?></span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span>Rooms:</span>
                                    <span><?php echo $booking_details['room_count']; ?></span>
                                </div>
                                <?php elseif (isset($booking_details['type']) && $booking_details['type'] === 'guide'): ?>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span>Guide:</span>
                                    <span class="fw-bold"><?php echo $booking_details['guide_name']; ?></span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span>Tour Date:</span>
                                    <span><?php echo $booking_details['tour_date']; ?></span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span>Duration:</span>
                                    <span><?php echo $booking_details['duration_days']; ?> days</span>
                                </div>
                                <?php elseif (isset($booking_details['type']) && $booking_details['type'] === 'vehicle'): ?>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span>Vehicle:</span>
                                    <span class="fw-bold"><?php echo $booking_details['vehicle_model']; ?></span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span>Pickup Date:</span>
                                    <span><?php echo $booking_details['pickup_date']; ?></span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span>Return Date:</span>
                                    <span><?php echo $booking_details['return_date']; ?></span>
                                </div>
                                <?php endif; ?>
                                <div class="total-amount">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-bold">Total Amount:</span>
                                        <span class="fw-bold fs-5 text-primary">Rs. <?php echo number_format($booking_details['total_cost'], 2); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>                          <div class="payment-options mb-3">
                            <h5 class="section-title mb-2"><i class="fas fa-money-bill-wave me-2"></i>Payment Method</h5>
                            <div class="method-selector mb-2">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="paymentMethod" id="creditCard" checked>
                                    <label class="form-check-label" for="creditCard">Credit/Debit Card</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="paymentMethod" id="paypal">
                                    <label class="form-check-label" for="paypal">PayPal</label>
                                </div>
                            </div>
                            <div class="payment-icons">
                                <div class="payment-icon-placeholder visa-placeholder" data-bs-toggle="tooltip" data-bs-placement="top" title="Visa"></div>
                                <div class="payment-icon-placeholder mastercard-placeholder" data-bs-toggle="tooltip" data-bs-placement="top" title="Mastercard"></div>
                                <div class="payment-icon-placeholder amex-placeholder" data-bs-toggle="tooltip" data-bs-placement="top" title="American Express"></div>
                                <div class="payment-icon-placeholder paypal-placeholder" data-bs-toggle="tooltip" data-bs-placement="top" title="PayPal"></div>
                            </div>
                        </div>                          <form id="payment-form" class="needs-validation" novalidate>
                            <div class="card-details">
                                <h5 class="section-title mb-2"><i class="fas fa-credit-card me-2"></i>Payment Details</h5>
                                <div class="mb-2">
                                    <label for="cardName" class="form-label small"><i class="fas fa-user me-1"></i> Cardholder Name</label>
                                    <input type="text" class="form-control form-control-sm" id="cardName" placeholder="Name on card" required>
                                    <div class="invalid-feedback small">
                                        Please enter the cardholder's name.
                                    </div>
                                </div>
                                
                                <div class="mb-2">
                                    <label for="cardNumber" class="form-label small"><i class="fas fa-credit-card me-1"></i> Card Number</label>
                                    <div class="input-group input-group-sm">
                                        <input type="text" class="form-control" id="cardNumber" placeholder="1234 5678 9012 3456" required>
                                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                        <div class="invalid-feedback small">
                                            Please enter a valid card number.
                                        </div>
                                    </div>
                                </div>                                  <div class="row g-2">
                                    <div class="col-md-6 mb-2">
                                        <label for="expiryDate" class="form-label small"><i class="fas fa-calendar-alt me-1"></i> Expiry Date</label>
                                        <input type="text" class="form-control form-control-sm" id="expiryDate" placeholder="MM/YY" required>
                                        <div class="invalid-feedback small">
                                            Please enter the expiry date.
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label for="cvv" class="form-label small"><i class="fas fa-lock me-1"></i> CVV/CVC</label>
                                        <div class="input-group input-group-sm">
                                            <input type="text" class="form-control" id="cvv" placeholder="123" required>
                                            <span class="input-group-text" data-bs-toggle="tooltip" data-bs-placement="top" title="3-digit code on the back of your card">
                                                <i class="fas fa-question-circle"></i>
                                            </span>
                                            <div class="invalid-feedback small">
                                                Please enter the CVV code.
                                            </div>
                                        </div>
                                    </div>                                </div>
                            </div>
                              <div class="form-check mt-3 mb-2">
                                <input class="form-check-input" type="checkbox" id="saveInfo">
                                <label class="form-check-label small" for="saveInfo">
                                    Save card for future bookings
                                </label>
                            </div><div class="secure-payment-notice mb-3">
                                <div class="d-flex align-items-center justify-content-center">
                                    <i class="fas fa-shield-alt me-1 text-success"></i>
                                    <span class="small">Secure payment - Protected by SSL encryption</span>
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary py-2">
                                    <i class="fas fa-lock me-1"></i> Pay Rs. <?php echo number_format($booking_details['total_cost'], 2); ?>
                                </button>
                                <a href="dashboard.php" class="btn btn-link text-muted small mt-1">Cancel and return to dashboard</a>
                            </div>
                        </form>
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

.payment-card {
    border-radius: 1rem;
    box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.08);
    overflow: hidden;
    border: none;
}

.payment-card .card-header {
    background-color: transparent;
    border-bottom: 1px solid #f0f0f0;
    padding: 1.25rem 1.5rem;
}

.payment-card .card-body {
    padding: 1.5rem;
}

.section-title {
    font-size: 0.9rem;
    font-weight: 600;
    color: #444;
    margin-bottom: 0.75rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #f0f0f0;
    display: flex;
    align-items: center;
}

.booking-summary {
    background-color: #f8f9fa;
    padding: 1.25rem;
    border-radius: 0.75rem;
    margin-bottom: 1.5rem;
    border: 1px solid #f0f0f0;
}

.summary-details {
    font-size: 0.95rem;
}

.summary-details .d-flex {
    padding: 0.5rem 0;
    border-bottom: 1px solid #f8f8f8;
}

.summary-details .d-flex:last-child {
    border-bottom: none;
}

.total-amount {
    margin-top: 0.75rem;
    padding-top: 0.75rem;
    border-top: 1px dashed #dee2e6;
}

.form-control,
.form-control-sm {
    padding: 0.5rem 0.65rem;
    font-size: 0.85rem;
    border-radius: 4px;
    border: 1px solid #e0e0e0;
    background-color: #fcfcfc;
    height: auto;
}

.form-control:focus {
    box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.1);
    border-color: #2563eb;
}

.input-group-text {
    background-color: #f8f9fa;
    border-color: #e0e0e0;
    padding: 0.5rem 0.65rem;
    font-size: 0.85rem;
}

.form-label {
    font-size: 0.85rem;
    font-weight: 500;
    color: #555;
    margin-bottom: 0.3rem;
}

.secure-payment-notice {
    background-color: #f1f8f1;
    border-radius: 0.4rem;
    padding: 0.5rem 0.75rem;
    font-size: 0.75rem;
    color: #2e7d32;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>

<script>
// Form validation
(function() {
    'use strict'
    
    // Fetch all forms we want to apply validation to
    var forms = document.querySelectorAll('.needs-validation')
    
    // Loop over them and prevent submission
    Array.prototype.slice.call(forms)
        .forEach(function(form) {
            form.addEventListener('submit', function(event) {
                event.preventDefault()
                event.stopPropagation()
                
                if (form.checkValidity()) {
                    // Simulate payment processing
                    const paymentBtn = form.querySelector('button[type="submit"]');
                    const originalText = paymentBtn.innerHTML;
                    
                    paymentBtn.disabled = true;
                    paymentBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Processing...';
                    
                    setTimeout(function() {
                        // Redirect to success page after "processing"
                        window.location.href = 'booking-success.php';
                    }, 3000);
                }
                
                form.classList.add('was-validated')
            }, false)
        })
})()

// Format card number input
document.getElementById('cardNumber').addEventListener('input', function (e) {
    let value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
    let formattedValue = '';
    
    for (let i = 0; i < value.length; i++) {
        if (i > 0 && i % 4 === 0) {
            formattedValue += ' ';
        }
        formattedValue += value[i];
    }
    
    e.target.value = formattedValue;
});

// Format expiry date input
document.getElementById('expiryDate').addEventListener('input', function (e) {
    let value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
    
    if (value.length > 2) {
        value = value.substring(0, 2) + '/' + value.substring(2, 4);
    }
    
    e.target.value = value;
});

// Limit CVV to 3 or 4 digits
document.getElementById('cvv').addEventListener('input', function (e) {
    e.target.value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '').substring(0, 4);
});

// Initialize Bootstrap tooltips
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
});

// Add button animation
document.querySelector('.btn-primary').addEventListener('mouseenter', function() {
    this.style.transform = 'translateY(-2px)';
    this.style.boxShadow = '0 4px 8px rgba(0, 0, 0, 0.1)';
});

document.querySelector('.btn-primary').addEventListener('mouseleave', function() {
    this.style.transform = 'translateY(0)';
    this.style.boxShadow = 'none';
});
</script>


