<?php
$user_id = $_SESSION['user_id'];
$sql = "SELECT gb.*, g.name, g.language, g.experience_years, g.daily_rate, g.imageUrl, g.specialization 
        FROM guide_bookings gb 
        JOIN guides g ON gb.guide_id = g.guide_id 
        WHERE gb.user_id = ? 
        ORDER BY gb.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0):
?>
    <div class="p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="search-box">
                <div class="input-group">
                    <input type="text" class="form-control" id="searchGuide" 
                           placeholder="Search guide bookings...">
                    <span class="input-group-text">
                        <i class="fas fa-search" style="font-size: 0.875rem;"></i>
                    </span>
                </div>
            </div>
            <a href="hire-guide.php" class="btn btn-primary">
                <i class="fas fa-plus-circle me-2" style="font-size: 0.875rem;"></i>New Guide Booking
            </a>
        </div>

        <div class="table-responsive booking-table">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th scope="col" style="width: 25%;">Guide</th>
                        <th scope="col" style="width: 30%;">Tour Details</th>
                        <th scope="col" style="width: 20%;">Duration & Cost</th>
                        <th scope="col" style="width: 15%;">Status</th>
                        <th scope="col" style="width: 10%;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($booking = $result->fetch_assoc()): 
                        $tour_date = new DateTime($booking['tour_date']);
                        $end_date = clone $tour_date;
                        $end_date->modify('+' . ($booking['duration_days'] - 1) . ' days');
                        $today = new DateTime();
                        
                        if ($today < $tour_date) {
                            $status = "Upcoming";
                            $status_class = "text-primary";
                            $can_modify = true;
                        } elseif ($today > $end_date) {
                            $status = "Completed";
                            $status_class = "text-success";
                            $can_modify = false;
                        } else {
                            $status = "Active";
                            $status_class = "text-warning";
                            $can_modify = false;
                        }
                    ?>
                        <tr class="booking-row">
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="guide-image me-3">
                                        <img src="<?php echo htmlspecialchars($booking['imageUrl']); ?>" 
                                             class="rounded-circle shadow-sm"
                                             style="width: 48px; height: 48px; object-fit: cover;"
                                             onerror="this.src='https://via.placeholder.com/48?text=No+Image'"
                                             alt="<?php echo htmlspecialchars($booking['name']); ?>">
                                    </div>
                                    <div>
                                        <h6 class="mb-1 fw-semibold"><?php echo htmlspecialchars($booking['name']); ?></h6>
                                        <div class="text-muted" style="font-size: 0.75rem;">
                                            <span class="d-block">
                                                <i class="fas fa-language me-1" style="font-size: 0.75rem;"></i>
                                                <?php echo htmlspecialchars($booking['language']); ?>
                                            </span>
                                            <span class="d-block">
                                                <i class="fas fa-star me-1" style="font-size: 0.75rem;"></i>
                                                <?php echo htmlspecialchars($booking['experience_years']); ?> years exp.
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="booking-details">
                                    <div class="mb-2">
                                        <i class="fas fa-calendar-alt text-primary me-2" style="font-size: 0.875rem;"></i>
                                        <span class="fw-medium">Start Date:</span>
                                        <span class="ms-1"><?php echo $tour_date->format('M d, Y'); ?></span>
                                    </div>
                                    <div class="mb-2">
                                        <i class="fas fa-users text-primary me-2" style="font-size: 0.875rem;"></i>
                                        <span class="fw-medium">Group Size:</span>
                                        <span class="ms-1"><?php echo htmlspecialchars($booking['group_size']); ?> persons</span>
                                    </div>
                                    <div>
                                        <i class="fas fa-map-marked-alt text-primary me-2" style="font-size: 0.875rem;"></i>
                                        <span class="fw-medium">Specialization:</span>
                                        <span class="ms-1"><?php echo htmlspecialchars($booking['specialization']); ?></span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="cost-details">
                                    <div class="mb-2">
                                        <i class="fas fa-clock text-primary me-2" style="font-size: 0.875rem;"></i>
                                        <span class="fw-medium">Duration:</span>
                                        <span class="ms-1"><?php echo $booking['duration_days']; ?> days</span>
                                    </div>
                                    <div class="mb-2">
                                        <i class="fas fa-tag text-primary me-2" style="font-size: 0.875rem;"></i>
                                        <span class="fw-medium">Daily Rate:</span>
                                        <span class="ms-1">Rs. <?php echo number_format($booking['daily_rate'], 2); ?></span>
                                    </div>
                                    <div class="fw-semibold">
                                        <i class="fas fa-money-bill-wave text-primary me-2" style="font-size: 0.875rem;"></i>
                                        <span class="fw-medium">Total:</span>
                                        <span class="ms-1">Rs. <?php echo number_format($booking['total_cost'], 2); ?></span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column align-items-start gap-2">
                                    <span class="badge rounded-pill bg-light <?php echo $status_class; ?> px-3 py-2">
                                        <i class="fas fa-circle me-1" style="font-size: 0.5rem; vertical-align: middle;"></i>
                                        <?php echo $status; ?>
                                    </span>
                                </div>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <?php if ($can_modify): ?>
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-primary action-btn"
                                                onclick="modifyBooking(<?php echo $booking['booking_id']; ?>, 'guide')"
                                                data-bs-toggle="tooltip"
                                                title="Modify Booking"
                                                style="padding: 0.25rem 0.5rem;">
                                            <i class="fas fa-edit" style="font-size: 0.875rem;"></i>
                                        </button>
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-danger action-btn"
                                                onclick="confirmDelete(<?php echo $booking['booking_id']; ?>, 'guide')"
                                                data-bs-toggle="tooltip"
                                                title="Cancel Booking"
                                                style="padding: 0.25rem 0.5rem;">
                                            <i class="fas fa-times" style="font-size: 0.875rem;"></i>
                                        </button>
                                    <?php endif; ?>
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-info action-btn"
                                            onclick="window.location.href='booking-details.php?type=guide&id=<?php echo $booking['booking_id']; ?>'"
                                            data-bs-toggle="tooltip"
                                            title="View Details"
                                            style="padding: 0.25rem 0.5rem;">
                                        <i class="fas fa-eye" style="font-size: 0.875rem;"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php else: ?>
    <div class="empty-state">
        <img src="https://via.placeholder.com/150?text=No+Bookings" 
             class="rounded-circle shadow-sm" alt="No bookings">
        <h4>No Guide Bookings Found</h4>
        <p>You haven't made any guide bookings yet. Start your journey by hiring a guide now!</p>
        <a href="hire-guide.php" class="btn btn-primary">
            <i class="fas fa-user-tie me-2" style="font-size: 0.875rem;"></i>Hire a Guide Now
        </a>
    </div>
<?php endif; ?>

<script>
document.getElementById('searchGuide').addEventListener('keyup', function() {
    const searchText = this.value.toLowerCase();
    const rows = document.querySelectorAll('.booking-table tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchText) ? '' : 'none';
    });
});
</script> 