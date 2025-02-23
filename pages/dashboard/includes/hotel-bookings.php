<?php
$user_id = $_SESSION['user_id'];
$sql = "SELECT hb.*, h.name, h.district, h.star_rating, h.imageUrl, h.price_per_night, h.contact_number 
        FROM hotel_bookings hb 
        JOIN hotels h ON hb.hotel_id = h.hotel_id 
        WHERE hb.user_id = ? 
        ORDER BY hb.created_at DESC";

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
                    <input type="text" class="form-control" id="searchHotel" 
                           placeholder="Search hotel bookings...">
                    <span class="input-group-text">
                        <i class="fas fa-search" style="font-size: 0.875rem;"></i>
                    </span>
                </div>
            </div>
            <a href="book-hotel.php" class="btn btn-primary">
                <i class="fas fa-plus-circle me-2" style="font-size: 0.875rem;"></i>New Hotel Booking
            </a>
        </div>

        <div class="table-responsive booking-table">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th scope="col" style="width: 25%;">Hotel</th>
                        <th scope="col" style="width: 30%;">Stay Details</th>
                        <th scope="col" style="width: 20%;">Duration & Cost</th>
                        <th scope="col" style="width: 15%;">Status</th>
                        <th scope="col" style="width: 10%;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($booking = $result->fetch_assoc()): 
                        $check_in = new DateTime($booking['check_in_date']);
                        $check_out = new DateTime($booking['check_out_date']);
                        $today = new DateTime();
                        
                        if ($today < $check_in) {
                            $status = "Upcoming";
                            $status_class = "text-primary";
                            $can_modify = true;
                        } elseif ($today > $check_out) {
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
                                    <div class="hotel-image me-3">
                                        <img src="<?php echo htmlspecialchars($booking['imageUrl']); ?>" 
                                             class="rounded shadow-sm"
                                             style="width: 48px; height: 48px; object-fit: cover;"
                                             onerror="this.src='https://via.placeholder.com/48?text=No+Image'"
                                             alt="<?php echo htmlspecialchars($booking['name']); ?>">
                                    </div>
                                    <div>
                                        <h6 class="mb-1 fw-semibold"><?php echo htmlspecialchars($booking['name']); ?></h6>
                                        <div class="text-muted" style="font-size: 0.75rem;">
                                            <span class="d-block">
                                                <i class="fas fa-map-marker-alt me-1" style="font-size: 0.75rem;"></i>
                                                <?php echo htmlspecialchars($booking['district']); ?>
                                            </span>
                                            <span class="d-block">
                                                <?php for($i = 0; $i < $booking['star_rating']; $i++): ?>
                                                    <i class="fas fa-star text-warning" style="font-size: 0.75rem;"></i>
                                                <?php endfor; ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="booking-details">
                                    <div class="mb-2">
                                        <i class="fas fa-calendar-alt text-primary me-2" style="font-size: 0.875rem;"></i>
                                        <span class="fw-medium">Check In:</span>
                                        <span class="ms-1"><?php echo $check_in->format('M d, Y'); ?></span>
                                    </div>
                                    <div class="mb-2">
                                        <i class="fas fa-calendar-check text-primary me-2" style="font-size: 0.875rem;"></i>
                                        <span class="fw-medium">Check Out:</span>
                                        <span class="ms-1"><?php echo $check_out->format('M d, Y'); ?></span>
                                    </div>
                                    <div class="mb-2">
                                        <i class="fas fa-door-closed text-primary me-2" style="font-size: 0.875rem;"></i>
                                        <span class="fw-medium">Rooms:</span>
                                        <span class="ms-1"><?php echo htmlspecialchars($booking['room_count']); ?></span>
                                    </div>
                                    <div>
                                        <i class="fas fa-users text-primary me-2" style="font-size: 0.875rem;"></i>
                                        <span class="fw-medium">Guests:</span>
                                        <span class="ms-1"><?php echo htmlspecialchars($booking['guest_count']); ?></span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="cost-details">
                                    <div class="mb-2">
                                        <i class="fas fa-clock text-primary me-2" style="font-size: 0.875rem;"></i>
                                        <span class="fw-medium">Duration:</span>
                                        <span class="ms-1">
                                            <?php 
                                            $interval = $check_in->diff($check_out);
                                            echo $interval->days; ?> nights
                                        </span>
                                    </div>
                                    <div class="mb-2">
                                        <i class="fas fa-tag text-primary me-2" style="font-size: 0.875rem;"></i>
                                        <span class="fw-medium">Rate/Night:</span>
                                        <span class="ms-1">Rs. <?php echo number_format($booking['price_per_night'], 2); ?></span>
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
                                    <?php if ($booking['booking_status'] == 'pending'): ?>
                                        <span class="badge bg-warning mt-2">
                                            <i class="fas fa-clock me-1" style="font-size: 0.75rem;"></i>
                                            Pending Confirmation
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <?php if ($can_modify): ?>
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-primary action-btn"
                                                onclick="modifyBooking(<?php echo $booking['booking_id']; ?>, 'hotel')"
                                                data-bs-toggle="tooltip"
                                                title="Modify Booking"
                                                style="padding: 0.25rem 0.5rem;">
                                            <i class="fas fa-edit" style="font-size: 0.875rem;"></i>
                                        </button>
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-danger action-btn"
                                                onclick="confirmDelete(<?php echo $booking['booking_id']; ?>, 'hotel')"
                                                data-bs-toggle="tooltip"
                                                title="Cancel Booking"
                                                style="padding: 0.25rem 0.5rem;">
                                            <i class="fas fa-times" style="font-size: 0.875rem;"></i>
                                        </button>
                                    <?php endif; ?>
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-info action-btn"
                                            onclick="window.location.href='booking-details.php?type=hotel&id=<?php echo $booking['booking_id']; ?>'"
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
        <h4>No Hotel Bookings Found</h4>
        <p>You haven't made any hotel bookings yet. Start your journey by booking a hotel now!</p>
        <a href="book-hotel.php" class="btn btn-primary">
            <i class="fas fa-hotel me-2" style="font-size: 0.875rem;"></i>Book a Hotel Now
        </a>
    </div>
<?php endif; ?>

<script>
document.getElementById('searchHotel').addEventListener('keyup', function() {
    const searchText = this.value.toLowerCase();
    const rows = document.querySelectorAll('.booking-table tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchText) ? '' : 'none';
    });
});
</script> 