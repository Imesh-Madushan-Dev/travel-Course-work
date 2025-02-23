<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$error = isset($_GET['error']) ? $_GET['error'] : 'unknown';
$error_messages = [
    'invalid_check_in_date' => 'Check-in date cannot be in the past.',
    'invalid_check_out_date' => 'Check-out date must be after check-in date.',
    'hotel_not_found' => 'The selected hotel was not found.',
    'insufficient_rooms' => 'Not enough rooms available for your booking.',
    'booking_failed' => 'Failed to process your booking. Please try again.',
    'unknown' => 'An unknown error occurred. Please try again.'
];

$error_message = $error_messages[$error] ?? $error_messages['unknown'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Failed</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .error-container {
            max-width: 600px;
            margin: 100px auto;
            text-align: center;
            padding: 2rem;
        }
        .error-icon {
            font-size: 4rem;
            color: #dc3545;
            margin-bottom: 1rem;
        }
        .error-message {
            margin: 1.5rem 0;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-container">
            <i class="fas fa-exclamation-circle error-icon"></i>
            <h2 class="mb-4">Booking Failed</h2>
            <div class="error-message">
                <p><?php echo htmlspecialchars($error_message); ?></p>
            </div>
            <div class="mt-4">
                <a href="book-hotel.php" class="btn btn-primary me-2">
                    <i class="fas fa-redo me-2"></i>Try Again
                </a>
                <a href="booking-history.php" class="btn btn-secondary">
                    <i class="fas fa-history me-2"></i>View Bookings
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>