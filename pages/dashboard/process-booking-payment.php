<?php
session_start();

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

require_once '../../config/db_connect.php';

$booking_details = $_SESSION['booking_details'];
$booking_type = $booking_details['type'];

try {
    // Start transaction
    $conn->begin_transaction();
    
    if ($booking_type === 'hotel') {
        // Process hotel booking
        $stmt = $conn->prepare("INSERT INTO hotel_bookings (user_id, hotel_id, check_in_date, check_out_date, 
                               room_count, guest_count, total_cost, booking_status) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, 'confirmed')");
        $stmt->bind_param("iissiid", 
            $booking_details['user_id'], 
            $booking_details['hotel_id'], 
            $booking_details['check_in_date'], 
            $booking_details['check_out_date'], 
            $booking_details['room_count'], 
            $booking_details['guest_count'], 
            $booking_details['total_cost']
        );
        $stmt->execute();
        
        if ($stmt->affected_rows > 0) {
            // Update available rooms
            $stmt = $conn->prepare("UPDATE hotels SET available_rooms = available_rooms - ? WHERE hotel_id = ?");
            $stmt->bind_param("ii", $booking_details['room_count'], $booking_details['hotel_id']);
            $stmt->execute();
            
            if ($stmt->affected_rows === 0) {
                throw new Exception("Failed to update room availability");
            }
        } else {
            throw new Exception("Hotel booking insertion failed");
        }
        
    } elseif ($booking_type === 'vehicle') {
        // Process vehicle booking
        $stmt = $conn->prepare("INSERT INTO vehicle_bookings (user_id, vehicle_id, pickup_date, return_date, pickup_location, total_cost) 
                               VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iisssd", 
            $booking_details['user_id'], 
            $booking_details['vehicle_id'], 
            $booking_details['pickup_date'], 
            $booking_details['return_date'], 
            $booking_details['pickup_location'], 
            $booking_details['total_cost']
        );
        $stmt->execute();
        
        if ($stmt->affected_rows === 0) {
            throw new Exception("Vehicle booking insertion failed");
        }
        
    } elseif ($booking_type === 'guide') {
        // Process guide booking
        $stmt = $conn->prepare("INSERT INTO guide_bookings (user_id, guide_id, tour_date, duration_days, group_size, total_cost) 
                               VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iisiii", 
            $booking_details['user_id'], 
            $booking_details['guide_id'], 
            $booking_details['tour_date'], 
            $booking_details['duration_days'], 
            $booking_details['group_size'], 
            $booking_details['total_cost']
        );
        $stmt->execute();
        
        if ($stmt->affected_rows === 0) {
            throw new Exception("Guide booking insertion failed");
        }
    } else {
        throw new Exception("Invalid booking type");
    }
    
    // Commit the transaction
    $conn->commit();
    
    // Generate a booking reference number
    $booking_ref = strtoupper(substr(md5(time() . $booking_details['user_id']), 0, 8));
    $_SESSION['booking_reference'] = $booking_ref;
    
    // Return success response (this file is called via AJAX or redirect)
    return true;
    
} catch (Exception $e) {
    // Rollback the transaction
    $conn->rollback();
    
    // Log the error
    error_log("Booking Payment Processing Error: " . $e->getMessage());
    
    // Redirect to booking failed page
    header("Location: booking-failed.php?error=payment_processing_failed");
    exit();
}
?> 