<?php
// Helper functions to check booking availability

function isGuideBookedByUser($conn, $guide_id, $user_id, $tour_date = null) {
    if ($tour_date) {
        // Check for specific date
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM guide_bookings 
                               WHERE guide_id = ? AND user_id = ? AND tour_date = ?");
        $stmt->bind_param("iis", $guide_id, $user_id, $tour_date);
    } else {
        // Check for any active booking
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM guide_bookings 
                               WHERE guide_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $guide_id, $user_id);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['count'] > 0;
}

function isVehicleBookedByUser($conn, $vehicle_id, $user_id, $pickup_date = null) {
    if ($pickup_date) {
        // Check for specific date
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM vehicle_bookings 
                               WHERE vehicle_id = ? AND user_id = ? AND pickup_date = ?");
        $stmt->bind_param("iis", $vehicle_id, $user_id, $pickup_date);
    } else {
        // Check for any active booking
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM vehicle_bookings 
                               WHERE vehicle_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $vehicle_id, $user_id);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['count'] > 0;
}

function getGuideBookingDetails($conn, $guide_id, $user_id) {
    $stmt = $conn->prepare("SELECT tour_date, duration_days, total_cost, 'confirmed' as booking_status 
                           FROM guide_bookings 
                           WHERE guide_id = ? AND user_id = ? 
                           ORDER BY tour_date DESC LIMIT 1");
    $stmt->bind_param("ii", $guide_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function getVehicleBookingDetails($conn, $vehicle_id, $user_id) {
    $stmt = $conn->prepare("SELECT pickup_date, return_date, pickup_location, total_cost, 'confirmed' as booking_status 
                           FROM vehicle_bookings 
                           WHERE vehicle_id = ? AND user_id = ? 
                           ORDER BY pickup_date DESC LIMIT 1");
    $stmt->bind_param("ii", $vehicle_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}
?> 