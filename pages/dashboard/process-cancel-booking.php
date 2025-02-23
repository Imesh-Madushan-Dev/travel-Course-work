<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

require_once '../../config/db_connect.php';

if (!isset($_GET['type']) || !isset($_GET['id'])) {
    header("Location: booking-history.php?error=invalid_request");
    exit();
}

$type = $_GET['type'];
$booking_id = $_GET['id'];
$user_id = $_SESSION['user_id'];
$today = date('Y-m-d');

try {
    $conn->begin_transaction();

    switch ($type) {
        case 'vehicle':
            // Check if booking exists and is upcoming
            $stmt = $conn->prepare("SELECT vb.*, v.model 
                                  FROM vehicle_bookings vb 
                                  JOIN vehicles v ON vb.vehicle_id = v.vehicle_id 
                                  WHERE vb.booking_id = ? AND vb.user_id = ? AND vb.pickup_date > ?");
            $stmt->bind_param("iis", $booking_id, $user_id, $today);
            $stmt->execute();
            $result = $stmt->get_result();
            $booking = $result->fetch_assoc();

            if (!$booking) {
                throw new Exception("Invalid booking or cannot be cancelled");
            }

            // Delete the booking
            $stmt = $conn->prepare("DELETE FROM vehicle_bookings WHERE booking_id = ? AND user_id = ?");
            $stmt->bind_param("ii", $booking_id, $user_id);
            $stmt->execute();

            $message = "Vehicle booking for " . $booking['model'] . " has been cancelled.";
            break;

        case 'guide':
            // Check if booking exists and is upcoming
            $stmt = $conn->prepare("SELECT gb.*, g.name 
                                  FROM guide_bookings gb 
                                  JOIN guides g ON gb.guide_id = g.guide_id 
                                  WHERE gb.booking_id = ? AND gb.user_id = ? AND gb.tour_date > ?");
            $stmt->bind_param("iis", $booking_id, $user_id, $today);
            $stmt->execute();
            $result = $stmt->get_result();
            $booking = $result->fetch_assoc();

            if (!$booking) {
                throw new Exception("Invalid booking or cannot be cancelled");
            }

            // Delete the booking
            $stmt = $conn->prepare("DELETE FROM guide_bookings WHERE booking_id = ? AND user_id = ?");
            $stmt->bind_param("ii", $booking_id, $user_id);
            $stmt->execute();

            $message = "Guide booking with " . $booking['name'] . " has been cancelled.";
            break;

        case 'hotel':
            // Check if booking exists and is upcoming
            $stmt = $conn->prepare("SELECT hb.*, h.name 
                                  FROM hotel_bookings hb 
                                  JOIN hotels h ON hb.hotel_id = h.hotel_id 
                                  WHERE hb.booking_id = ? AND hb.user_id = ? AND hb.check_in_date > ?");
            $stmt->bind_param("iis", $booking_id, $user_id, $today);
            $stmt->execute();
            $result = $stmt->get_result();
            $booking = $result->fetch_assoc();

            if (!$booking) {
                throw new Exception("Invalid booking or cannot be cancelled");
            }

            // Update available rooms
            $stmt = $conn->prepare("UPDATE hotels 
                                  SET available_rooms = available_rooms + ? 
                                  WHERE hotel_id = ?");
            $stmt->bind_param("ii", $booking['room_count'], $booking['hotel_id']);
            $stmt->execute();

            // Delete the booking
            $stmt = $conn->prepare("DELETE FROM hotel_bookings WHERE booking_id = ? AND user_id = ?");
            $stmt->bind_param("ii", $booking_id, $user_id);
            $stmt->execute();

            $message = "Hotel booking at " . $booking['name'] . " has been cancelled.";
            break;

        default:
            throw new Exception("Invalid booking type");
    }

    $conn->commit();
    $_SESSION['booking_success'] = true;
    $_SESSION['booking_message'] = $message;
    header("Location: booking-history.php?tab=" . $type . "s&cancelled=1");
    exit();

} catch (Exception $e) {
    $conn->rollback();
    error_log("Booking Cancellation Error: " . $e->getMessage());
    header("Location: booking-history.php?error=cancellation_failed&message=" . urlencode($e->getMessage()));
    exit();
}
?> 