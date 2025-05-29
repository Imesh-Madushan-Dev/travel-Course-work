<?php
require_once 'includes/session_helper.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

require_once '../../config/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $hotel_id = $_POST['hotel_id'];
    $user_id = $_SESSION['user_id'];
    $check_in_date = $_POST['check_in_date'];
    $check_out_date = $_POST['check_out_date'];
    $room_count = $_POST['room_count'];
    $guest_count = $_POST['guest_count'];
    
    // Validate dates
    $check_in_datetime = new DateTime($check_in_date);
    $check_out_datetime = new DateTime($check_out_date);
    $today = new DateTime();
    
    if ($check_in_datetime < $today) {
        header("Location: booking-failed.php?error=invalid_check_in_date");
        exit();
    }
    
    if ($check_out_datetime <= $check_in_datetime) {
        header("Location: booking-failed.php?error=invalid_check_out_date");
        exit();
    }
    
    // Calculate stay duration and total cost
    $interval = $check_in_datetime->diff($check_out_datetime);
    $nights = $interval->days;
    
    // Get hotel details and check availability
    $stmt = $conn->prepare("SELECT price_per_night, available_rooms FROM hotels WHERE hotel_id = ?");
    $stmt->bind_param("i", $hotel_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $hotel = $result->fetch_assoc();
    
    if (!$hotel) {
        header("Location: booking-failed.php?error=hotel_not_found");
        exit();
    }
    
    if ($hotel['available_rooms'] < $room_count) {
        header("Location: booking-failed.php?error=insufficient_rooms");
        exit();
    }
      $total_cost = $hotel['price_per_night'] * $nights * $room_count;
    
    // Store the booking details in session for payment processing
    $stmt = $conn->prepare("SELECT name FROM hotels WHERE hotel_id = ?");
    $stmt->bind_param("i", $hotel_id);
    $stmt->execute();
    $hotel_result = $stmt->get_result();
    $hotel_name = $hotel_result->fetch_assoc()['name'];
    
    $_SESSION['booking_details'] = [
        'type' => 'hotel',
        'hotel_id' => $hotel_id,
        'hotel_name' => $hotel_name,
        'user_id' => $user_id,
        'check_in_date' => $check_in_date,
        'check_out_date' => $check_out_date,
        'room_count' => $room_count,
        'guest_count' => $guest_count,
        'total_cost' => $total_cost,
        'nights' => $nights
    ];
    
    // Redirect to payment gateway
    header("Location: payment-gateway.php");
    exit();
    
    // Original transaction code moved to after payment
    /*
    try {
        // Start transaction
        $conn->begin_transaction();
        
        // Insert booking - removed special_requests from the query
        $stmt = $conn->prepare("INSERT INTO hotel_bookings (user_id, hotel_id, check_in_date, check_out_date, 
                               room_count, guest_count, total_cost, booking_status) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
        $stmt->bind_param("iissiid", $user_id, $hotel_id, $check_in_date, $check_out_date, 
                         $room_count, $guest_count, $total_cost);
        $stmt->execute();
        
        if ($stmt->affected_rows > 0) {
            // Update available rooms
            $stmt = $conn->prepare("UPDATE hotels SET available_rooms = available_rooms - ? WHERE hotel_id = ?");
            $stmt->bind_param("ii", $room_count, $hotel_id);
            $stmt->execute();
            
            if ($stmt->affected_rows > 0) {
                $conn->commit();
                $_SESSION['booking_success'] = true;
                $_SESSION['booking_message'] = "Hotel booking has been submitted successfully and is pending confirmation.";
                
                // Show success message before redirecting
                echo "<script>
                    alert('Hotel booking successful! Your booking is pending confirmation.');
                    window.location.href = 'booking-history.php?tab=hotels&success=1';
                </script>";
                exit();
            } else {
                throw new Exception("Failed to update room availability");
            }
        } else {
            throw new Exception("Booking insertion failed");
        }
    */
    /* catch (Exception $e) {
        $conn->rollback();
        error_log("Hotel Booking Error: " . $e->getMessage());
        header("Location: booking-failed.php?error=booking_failed");
        exit();
    } */
} else {
    header("Location: book-hotel.php");
    exit();
}
?>