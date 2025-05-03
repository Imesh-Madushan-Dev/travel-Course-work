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

$conn->autocommit(FALSE); // Start transaction

if ($type == 'vehicle') {
    $query = "SELECT vb.*, v.model 
              FROM vehicle_bookings vb 
              JOIN vehicles v ON vb.vehicle_id = v.vehicle_id 
              WHERE vb.booking_id = $booking_id AND vb.user_id = $user_id AND vb.pickup_date > '$today'";
    $result = $conn->query($query);
    $booking = $result->fetch_assoc();

    if (!$booking) {
        header("Location: booking-history.php?error=invalid_booking");
        exit();
    }

    $conn->query("DELETE FROM vehicle_bookings WHERE booking_id = $booking_id AND user_id = $user_id");
    $message = "Vehicle booking for " . $booking['model'] . " has been cancelled.";

} elseif ($type == 'guide') {
    $query = "SELECT gb.*, g.name 
              FROM guide_bookings gb 
              JOIN guides g ON gb.guide_id = g.guide_id 
              WHERE gb.booking_id = $booking_id AND gb.user_id = $user_id AND gb.tour_date > '$today'";
    $result = $conn->query($query);
    $booking = $result->fetch_assoc();

    if (!$booking) {
        header("Location: booking-history.php?error=invalid_booking");
        exit();
    }

    $conn->query("DELETE FROM guide_bookings WHERE booking_id = $booking_id AND user_id = $user_id");
    $message = "Guide booking with " . $booking['name'] . " has been cancelled.";

} elseif ($type == 'hotel') {
    $query = "SELECT hb.*, h.name 
              FROM hotel_bookings hb 
              JOIN hotels h ON hb.hotel_id = h.hotel_id 
              WHERE hb.booking_id = $booking_id AND hb.user_id = $user_id AND hb.check_in_date > '$today'";
    $result = $conn->query($query);
    $booking = $result->fetch_assoc();

    if (!$booking) {
        header("Location: booking-history.php?error=invalid_booking");
        exit();
    }

    $conn->query("UPDATE hotels SET available_rooms = available_rooms + " . $booking['room_count'] . " WHERE hotel_id = " . $booking['hotel_id']);
    $conn->query("DELETE FROM hotel_bookings WHERE booking_id = $booking_id AND user_id = $user_id");
    $message = "Hotel booking at " . $booking['name'] . " has been cancelled.";

} else {
    header("Location: booking-history.php?error=invalid_booking_type");
    exit();
}

$conn->commit();
$_SESSION['booking_success'] = true;
$_SESSION['booking_message'] = $message;
header("Location: booking-history.php?tab=" . $type . "s&cancelled=1");
exit();

$conn->rollback();
?> 