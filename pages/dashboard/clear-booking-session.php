<?php
session_start();

// Clear booking-related session data
if (isset($_SESSION['booking_details'])) {
    unset($_SESSION['booking_details']);
}

if (isset($_SESSION['booking_processed'])) {
    unset($_SESSION['booking_processed']);
}

if (isset($_SESSION['booking_reference'])) {
    unset($_SESSION['booking_reference']);
}

// Return success response
http_response_code(200);
echo json_encode(['status' => 'success']);
?> 