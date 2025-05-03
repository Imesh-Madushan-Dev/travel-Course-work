<div class="p-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="search-container">
            <input type="text" id="searchGuide" class="form-control" placeholder="Search guide bookings..." style="padding-left: 40px;">
            <i class="fas fa-search" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #adb5bd;"></i>
        </div>
        <a href="book-guide.php" class="btn btn-success">
            <i class="fas fa-plus me-2"></i>New Guide Booking
        </a>
    </div>

    <?php
    $stmt = $conn->prepare("SELECT gb.*, g.fullname, g.profile_image, g.languages, g.experience 
                           FROM guide_bookings gb 
                           JOIN guides g ON gb.guide_id = g.guide_id 
                           WHERE gb.user_id = ? 
                           ORDER BY gb.tour_date DESC");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0):
    ?>
    <div class="booking-list">
        <?php while ($booking = $result->fetch_assoc()):
            // Calculate status
            $today = new DateTime();
            $tour_date = new DateTime($booking['tour_date']);
            
            if (isset($booking['cancelled']) && $booking['cancelled']) {
                $status = "cancelled";
                $status_text = "Cancelled";
            } elseif ($tour_date < $today) {
                $status = "completed";
                $status_text = "Completed";
            } else {
                $status = "pending";
                $status_text = "Upcoming";
            }
            
            // Parse languages
            $languages = explode(',', $booking['languages']);
        ?>
        <div class="card mb-3 booking-card">
            <div class="row g-0">
                <div class="col-md-2 d-flex align-items-center justify-content-center p-3">
                    <?php if (!empty($booking['profile_image'])): ?>
                        <img src="<?php echo htmlspecialchars($booking['profile_image']); ?>" alt="<?php echo htmlspecialchars($booking['fullname']); ?>" class="img-fluid rounded-circle" style="width: 80px; height: 80px; object-fit: cover;">
                    <?php else: ?>
                        <i class="fas fa-user-tie fa-4x text-muted"></i>
                    <?php endif; ?>
                </div>
                <div class="col-md-4 p-3">
                    <h5 class="card-title"><?php echo htmlspecialchars($booking['fullname']); ?></h5>
                    <div class="d-flex flex-wrap gap-1 mb-2">
                        <?php foreach ($languages as $language): ?>
                            <span class="badge bg-light text-dark"><?php echo trim($language); ?></span>
                        <?php endforeach; ?>
                    </div>
                    <div class="text-warning mb-2">
                        <i class="fas fa-star"></i>
                        <span><?php echo $booking['experience']; ?> years exp.</span>
                    </div>
                    <div class="mt-3">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-calendar-alt text-primary me-2"></i>
                            <span>Start Date: <?php echo date('M d, Y', strtotime($booking['tour_date'])); ?></span>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-users text-success me-2"></i>
                            <span>Group Size: <?php echo $booking['group_size']; ?> persons</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="fas fa-map text-danger me-2"></i>
                            <span><?php echo htmlspecialchars($booking['tour_type']); ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 p-3 border-start">
                    <div class="text-muted mb-2">Duration</div>
                    <div class="h5 mb-3"><?php echo $booking['duration']; ?> days</div>
                    <div class="text-muted mb-2">Total Cost</div>
                    <div class="h5">Rs. <?php echo number_format($booking['total_cost'], 2); ?></div>
                </div>
                <div class="col-md-2 p-3 d-flex flex-column justify-content-center align-items-center border-start">
                    <div class="badge badge-<?php echo $status; ?> mb-2 w-100 text-center"><?php echo $status_text; ?></div>
                </div>
                <div class="col-md-2 p-3 d-flex flex-column justify-content-center align-items-center border-start">
                    <?php if ($status != "cancelled" && $status != "completed"): ?>
                        <button class="btn btn-outline-primary mb-2 w-100" onclick="modifyBooking(<?php echo $booking['booking_id']; ?>, 'guide')">
                            <i class="fas fa-edit me-2"></i>Modify
                        </button>
                        <button class="btn btn-outline-danger w-100" onclick="confirmDelete(<?php echo $booking['booking_id']; ?>, 'guide')">
                            <i class="fas fa-times me-2"></i>Cancel
                        </button>
                    <?php else: ?>
                        <button class="btn btn-outline-secondary w-100" onclick="window.location.href='booking-details.php?type=guide&id=<?php echo $booking['booking_id']; ?>'">
                            <i class="fas fa-eye me-2"></i>View Details
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <img src="../../assets/images/empty-bookings.svg" alt="No bookings" onerror="this.src='../../assets/images/empty-state.png';">
        <h3>No Guide Bookings Found</h3>
        <p>You haven't booked any tour guides yet. Enhance your travel experience with our knowledgeable local guides!</p>
        <a href="book-guide.php" class="btn btn-primary mt-3">Book a Guide</a>
    </div>
    <?php endif; ?>
</div>

<script>
    // Search functionality
    document.getElementById('searchGuide').addEventListener('keyup', function() {
        const searchValue = this.value.toLowerCase();
        const bookingCards = document.querySelectorAll('.booking-card');
        
        bookingCards.forEach(card => {
            const cardText = card.textContent.toLowerCase();
            if (cardText.includes(searchValue)) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    });
</script>