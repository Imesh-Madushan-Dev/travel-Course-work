<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['is_admin'])) {
    header("Location: ../../login.php");
    exit();
}

require_once '../../../config/db_connect.php';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get and sanitize form data
    $type = $conn->real_escape_string($_POST['type']);
    $model = $conn->real_escape_string($_POST['model']);
    $year = intval($_POST['year']);
    $daily_rate = floatval($_POST['daily_rate']);
    $capacity = intval($_POST['capacity']);
    $district = $conn->real_escape_string($_POST['district']);
    $transmission = $conn->real_escape_string($_POST['transmission']);
    $license_plate = $conn->real_escape_string($_POST['license_plate']);
    $ac_available = isset($_POST['ac_available']) ? intval($_POST['ac_available']) : 1;
    
    // Check if license plate already exists
    $check_stmt = $conn->prepare("SELECT vehicle_id FROM vehicles WHERE license_plate = ?");
    $check_stmt->bind_param("s", $license_plate);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        $_SESSION['error_message'] = "License plate already exists. Please use a different one.";
        header("Location: manage-vehicles.php");
        exit();
    }
    
    // Handle image upload
    $target_dir = "../../../images/vehicles/";
    
    // Create directory if it doesn't exist
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_extension = strtolower(pathinfo($_FILES["vehicle_image"]["name"], PATHINFO_EXTENSION));
    $new_filename = "vehicle_" . time() . "_" . rand(1000, 9999) . "." . $file_extension;
    $target_file = $target_dir . $new_filename;
    $image_url = "images/vehicles/" . $new_filename;
    
    // Check if image file is a actual image
    $check = getimagesize($_FILES["vehicle_image"]["tmp_name"]);
    if ($check === false) {
        $_SESSION['error_message'] = "File is not an image.";
        header("Location: manage-vehicles.php");
        exit();
    }
    
    // Check file size (2MB max)
    if ($_FILES["vehicle_image"]["size"] > 2000000) {
        $_SESSION['error_message'] = "Sorry, your file is too large. Max size is 2MB.";
        header("Location: manage-vehicles.php");
        exit();
    }
    
    // Allow certain file formats
    if ($file_extension != "jpg" && $file_extension != "png" && $file_extension != "jpeg") {
        $_SESSION['error_message'] = "Sorry, only JPG, JPEG & PNG files are allowed.";
        header("Location: manage-vehicles.php");
        exit();
    }
    
    // Upload the file
    if (!move_uploaded_file($_FILES["vehicle_image"]["tmp_name"], $target_file)) {
        $_SESSION['error_message'] = "Sorry, there was an error uploading your file.";
        header("Location: manage-vehicles.php");
        exit();
    }
    
    // Insert new vehicle into database
    $stmt = $conn->prepare("INSERT INTO vehicles (type, model, year, daily_rate, capacity, district, imageUrl, ac_available, transmission, license_plate) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->bind_param("ssisississ", $type, $model, $year, $daily_rate, $capacity, $district, $image_url, $ac_available, $transmission, $license_plate);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Vehicle added successfully!";
    } else {
        $_SESSION['error_message'] = "Error adding vehicle: " . $conn->error;
    }
    
    $stmt->close();
    
    header("Location: manage-vehicles.php");
    exit();
} else {
    header("Location: manage-vehicles.php");
    exit();
}
?>