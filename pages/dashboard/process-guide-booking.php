<?php
require_once 'includes/session_helper.php';
require_once '../../config/db_connect.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['guide_id'])) {
    header("Location: book-guide-form.php?guide_id=" . $guide_id . "&booking=success");
    exit();
}

try {
    // Convert values to appropriate types
    $user_id = (int)$_SESSION['user_id'];
    $guide_id = (int)$_POST['guide_id'];
    $tour_date = $_POST['tour_date'];
    $duration_days = (int)$_POST['duration_days'];
    $group_size = (int)$_POST['group_size'];
    $daily_rate = (float)$_POST['daily_rate'];
    $total_cost = $daily_rate * $duration_days;

    // First verify if guide exists and get guide name
    $check_guide = "SELECT guide_id, name FROM guides WHERE guide_id = ?";
    $stmt = $conn->prepare($check_guide);
    $stmt->bind_param("i", $guide_id);
    $stmt->execute();
    $guide_result = $stmt->get_result();

    if ($guide_result->num_rows === 0) {
        header("Location: booking-failed.php?error=guide_not_found");
        exit();
    }
    
    $guide_data = $guide_result->fetch_assoc();

    // Verify if user exists
    $check_user = "SELECT id FROM users WHERE id = ?";
    $stmt = $conn->prepare($check_user);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user_result = $stmt->get_result();

    if ($user_result->num_rows === 0) {
        header("Location: booking-failed.php?error=invalid_user");
        exit();
    }

    // Store the booking details in session for payment processing
    $_SESSION['booking_details'] = [
        'type' => 'guide',
        'guide_id' => $guide_id,
        'guide_name' => $guide_data['name'],
        'user_id' => $user_id,
        'tour_date' => $tour_date,
        'duration_days' => $duration_days,
        'group_size' => $group_size,
        'total_cost' => $total_cost
    ];
    
    // Redirect to payment gateway
    header("Location: payment-gateway.php");
    exit();
    
    // Original transaction code moved to process-booking-payment.php
    /*
    // Insert booking
    $sql = "INSERT INTO guide_bookings (user_id, guide_id, tour_date, duration_days, group_size, total_cost) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisiii", $user_id, $guide_id, $tour_date, $duration_days, $group_size, $total_cost);

    if ($stmt->execute()) {
        // Show success message before redirecting
        echo "<script>
            alert('Guide booking successful!');
            window.location.href = 'dashboard.php';
        </script>";
        exit();
    } else {
        header("Location: booking-failed.php?error=booking_failed");
    }
    */
} catch (Exception $e) {
    header("Location: booking-failed.php?error=system_error");
}
exit();
?>