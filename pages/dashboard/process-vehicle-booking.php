<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

require_once '../../config/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $vehicle_id = $_POST['vehicle_id'];
    $user_id = $_SESSION['user_id'];
    $pickup_date = $_POST['pickup_date'];
    $return_date = $_POST['return_date'];
    $pickup_location = $_POST['pickup_location'];
    
    // Validate dates
    $pickup_datetime = new DateTime($pickup_date);
    $return_datetime = new DateTime($return_date);
    $today = new DateTime();
    
    if ($pickup_datetime < $today) {
        header("Location: booking-failed.php?error=invalid_pickup_date");
        exit();
    }
    
    if ($return_datetime < $pickup_datetime) {
        header("Location: booking-failed.php?error=invalid_return_date");
        exit();
    }
    
    // Calculate rental duration and total cost
    $interval = $pickup_datetime->diff($return_datetime);
    $days = $interval->days + 1;
    
    // Get vehicle details and check availability
    $stmt = $conn->prepare("SELECT daily_rate FROM vehicles WHERE vehicle_id = ? AND ac_available = 1");
    $stmt->bind_param("i", $vehicle_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $vehicle = $result->fetch_assoc();
    
    if (!$vehicle) {
        header("Location: booking-failed.php?error=vehicle_unavailable");
        exit();
    }
    
    $total_cost = $vehicle['daily_rate'] * $days;
    
    // Check for overlapping bookings
    $stmt = $conn->prepare("SELECT COUNT(*) as booking_count FROM vehicle_bookings 
                           WHERE vehicle_id = ? 
                           AND ((pickup_date BETWEEN ? AND ?) 
                           OR (return_date BETWEEN ? AND ?))");
    $stmt->bind_param("issss", $vehicle_id, $pickup_date, $return_date, $pickup_date, $return_date);
    $stmt->execute();
    $result = $stmt->get_result();
    $overlap = $result->fetch_assoc();
    
    if ($overlap['booking_count'] > 0) {
        header("Location: booking-failed.php?error=dates_unavailable");
        exit();
    }
    
    try {
        // Start transaction
        $conn->begin_transaction();
        
        // Insert booking
        $stmt = $conn->prepare("INSERT INTO vehicle_bookings (user_id, vehicle_id, pickup_date, return_date, pickup_location, total_cost) 
                               VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iisssd", $user_id, $vehicle_id, $pickup_date, $return_date, $pickup_location, $total_cost);
        $stmt->execute();
        
        if ($stmt->affected_rows > 0) {
            $conn->commit();
            header("Location: booking-history.php?success=1");
            exit();
        } else {
            throw new Exception("Booking insertion failed");
        }
    } catch (Exception $e) {
        $conn->rollback();
        header("Location: booking-failed.php?error=booking_failed");
        exit();
    }
} else {
    header("Location: book-vehicle.php");
    exit();
}
?>