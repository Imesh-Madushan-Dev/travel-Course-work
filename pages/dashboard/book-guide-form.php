<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

require_once '../../config/db_connect.php';

if (!isset($_GET['guide_id'])) {
    header("Location: hire-guide.php");
    exit();
}

$guide_id = $_GET['guide_id'];
$sql = "SELECT * FROM guides WHERE guide_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $guide_id);
$stmt->execute();
$result = $stmt->get_result();
$guide = $result->fetch_assoc();

if (!$guide) {
    header("Location: hire-guide.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Guide - Travel Ceylon</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../css/dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <?php include 'includes/sidebar.php'; ?>
        <div class="main-content">
            <header>
                <div class="user-profile">
                    <i class="fas fa-user-circle"></i>
                    <span><?php echo htmlspecialchars($_SESSION['fullname']); ?></span>
                </div>
            </header>

            <div class="booking-container">
                <div class="guide-details">
                    <img 
                        src="<?php echo $guide['imageUrl']; ?>" 
                        onerror="handleImageError(this)"
                        alt="<?php echo $guide['name']; ?>"
                        class="guide-image"
                    >
                    <div class="guide-info">
                        <h2><?php echo $guide['name']; ?></h2>
                        <p><strong>Specialization:</strong> <?php echo $guide['specialization']; ?></p>
                        <p><strong>Languages:</strong> <?php echo $guide['language']; ?></p>
                        <p><strong>Experience:</strong> <?php echo $guide['experience_years']; ?> years</p>
                        <p><strong>Rate:</strong> Rs. <?php echo number_format($guide['daily_rate'], 2); ?> per day</p>
                    </div>
                </div>

                <div class="booking-form">
                    <h3>Book Your Guide</h3>
                    <form id="guideBookingForm" action="process-guide-booking.php" method="POST">
                        <input type="hidden" name="guide_id" value="<?php echo $guide_id; ?>">
                        <input type="hidden" name="daily_rate" value="<?php echo $guide['daily_rate']; ?>">

                        <div class="form-group">
                            <label for="tour_date">Tour Start Date</label>
                            <input type="date" id="tour_date" name="tour_date" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                        </div>

                        <div class="form-group">
                            <label for="duration_days">Duration (Days)</label>
                            <input type="number" id="duration_days" name="duration_days" class="form-control" required min="1" max="30">
                        </div>

                        <div class="form-group">
                            <label for="group_size">Group Size</label>
                            <input type="number" id="group_size" name="group_size" class="form-control" required min="1">
                        </div>

                        <div class="form-group">
                            <label>Total Cost</label>
                            <div class="total-cost">
                                Rs. <span id="totalCost">0.00</span>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Confirm Booking</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
         const PLACEHOLDER_IMAGE = 'https://via.placeholder.com/300x200?text=No+Image';

function handleImageError(img) {
    img.onerror = null;
    img.src = PLACEHOLDER_IMAGE;
}
    document.getElementById('duration_days').addEventListener('input', calculateTotal);
    
    function calculateTotal() {
        const dailyRate = <?php echo $guide['daily_rate']; ?>;
        const days = document.getElementById('duration_days').value || 0;
        const total = dailyRate * days;
        document.getElementById('totalCost').textContent = total.toFixed(2);
    }

    // Check for success parameter in URL
    function getUrlParameter(name) {
        name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
        var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
        var results = regex.exec(location.search);
        return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
    }

    // Show success popup if booking was successful
    if (getUrlParameter('booking') === 'success') {
        document.getElementById('successPopup').style.display = 'flex';
    }

    // Close popup when clicking the close button
    document.querySelector('.close-btn').addEventListener('click', function() {
        document.getElementById('successPopup').style.display = 'none';
    });
    </script>
   
    <style>
    .success-popup {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        justify-content: center;
        align-items: center;
        z-index: 1000;
    }
    
    .popup-content {
        background-color: white;
        padding: 30px;
        border-radius: 10px;
        text-align: center;
        position: relative;
        max-width: 400px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }
    
    .close-btn {
        position: absolute;
        top: 10px;
        right: 15px;
        font-size: 24px;
        cursor: pointer;
        color: #888;
    }
    
    .close-btn:hover {
        color: #333;
    }
    
    .success-popup i {
        font-size: 60px;
        color: #28a745;
        margin-bottom: 15px;
    }
    
    .success-popup h3 {
        margin-bottom: 10px;
        color: #333;
    }
    
    .success-popup p {
        color: #666;
    }
    </style>
</body>
</html>