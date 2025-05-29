<?php
require_once 'includes/session_helper.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
require_once '../../config/db_connect.php';
require_once 'includes/booking_helper.php';
$page_title = "Hire Guide";
include 'includes/header.php';
?>
<body>
    <div class="dashboard-container">
        <?php include 'includes/sidebar.php'; ?>
        <div class="main-content">
            <header>
                <h1>Hire a Guide</h1>
                <div class="user-profile">
                    <i class="fas fa-user-circle"></i>
                    <span><?php echo htmlspecialchars($_SESSION['fullname']); ?></span>
                </div>
            </header>
            <div class="filter-section mb-4">
                <select id="districtFilter" class="form-select" onchange="filterGuides()">
                    <option value="">All Districts</option>
                    <option value="Colombo">Colombo</option>
                    <option value="Kandy">Kandy</option>
                    <option value="Galle">Galle</option>
                    <!-- Add more districts -->
                </select>
            </div>
            <div class="guides-container">
                <?php
                $sql = "SELECT * FROM guides";
                $result = $conn->query($sql);
                while($guide = $result->fetch_assoc()): 
                    $isBooked = isGuideBookedByUser($conn, $guide['guide_id'], $_SESSION['user_id']);
                    $bookingDetails = null;
                    if ($isBooked) {
                        $bookingDetails = getGuideBookingDetails($conn, $guide['guide_id'], $_SESSION['user_id']);
                    }
                ?>
                <div class="card guide-card" data-district="<?php echo $guide['district']; ?>">
                    <img 
                        src="<?php echo $guide['imageUrl']; ?>" 
                        class="card-img-top"
                        onerror="handleImageError(this)"
                        alt="<?php echo $guide['name']; ?>"
                    >
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $guide['name']; ?></h5>
                        <div class="card-rating">
                            <i class="fas fa-star"></i>
                            <span><?php echo number_format($guide['rating'], 1); ?></span>
                        </div>
                        <p class="card-text">
                            <strong>Specialization:</strong> <?php echo $guide['specialization']; ?><br>
                            <strong>Languages:</strong> <?php echo $guide['language']; ?><br>
                            <strong>Experience:</strong> <?php echo $guide['experience_years']; ?> years<br>
                            <strong>District:</strong> <?php echo $guide['district']; ?><br>
                            <strong>Rate:</strong> Rs. <?php echo number_format($guide['daily_rate'], 2); ?> per day
                        </p>
                        
                        <?php if ($isBooked): ?>
                            <div class="alert alert-success mb-3">
                                <i class="fas fa-check-circle me-2"></i>
                                <strong>Already Booked!</strong><br>
                                <small>
                                    Tour Date: <?php echo date('M d, Y', strtotime($bookingDetails['tour_date'])); ?><br>
                                    Duration: <?php echo $bookingDetails['duration_days']; ?> day(s)<br>
                                    Status: <?php echo ucfirst($bookingDetails['booking_status']); ?>
                                </small>
                            </div>
                            <button class="btn btn-secondary" disabled>
                                <i class="fas fa-calendar-check me-2"></i>Guide Already Booked
                            </button>
                        <?php else: ?>
                            <button class="btn btn-primary" onclick="window.location.href='book-guide-form.php?guide_id=<?php echo $guide['guide_id']; ?>'">
                                <i class="fas fa-user-plus me-2"></i>Book Guide
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
    
    <style>
        .guides-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            padding: 1rem 0;
        }
        
        .guide-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .guide-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        }
        
        .guide-card .card-img-top {
            height: 200px;
            object-fit: cover;
            width: 100%;
        }
        
        .guide-card .card-body {
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            height: calc(100% - 200px);
        }
        
        .guide-card .card-title {
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .card-rating {
            color: #f39c12;
            margin-bottom: 1rem;
            font-weight: 500;
        }
        
        .card-rating i {
            margin-right: 0.25rem;
        }
        
        .guide-card .card-text {
            flex-grow: 1;
            margin-bottom: 1rem;
            line-height: 1.6;
        }
        
        .alert-success {
            background-color: #d1edff;
            border-color: #0d6efd;
            color: #0a58ca;
            border-radius: 8px;
            font-size: 0.9rem;
            border: 1px solid #0d6efd;
        }
        
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .btn {
            border-radius: 8px;
            font-weight: 500;
            padding: 0.75rem 1.5rem;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #0d6efd 0%, #0099ff 100%);
            border: none;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #0b5ed7 0%, #0077cc 100%);
            transform: translateY(-1px);
        }
        
        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
        }
        
        .filter-section {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .form-select {
            border-radius: 8px;
            border: 1px solid #dee2e6;
            padding: 0.75rem 1rem;
        }
        
        .form-select:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }
    </style>
    
    <script>
        const PLACEHOLDER_IMAGE = 'https://via.placeholder.com/300x200?text=No+Image';

        function handleImageError(img) {
            img.onerror = null;
            img.src = PLACEHOLDER_IMAGE;
        }
       
        function filterGuides() {
            const district = document.getElementById('districtFilter').value;
            const cards = document.querySelectorAll('.guide-card');
            
            cards.forEach(card => {
                if (!district || card.dataset.district === district) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        function bookGuide(guideId) {
            // Add booking logic
            window.location.href = `book-guide.php?guide_id=${guideId}`;
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>