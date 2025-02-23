<?php
$user_id = $_SESSION['user_id'];
$sql = "SELECT vb.*, v.model, v.type, v.imageUrl, v.daily_rate, v.license_plate 
        FROM vehicle_bookings vb 
        JOIN vehicles v ON vb.vehicle_id = v.vehicle_id 
        WHERE vb.user_id = ? 
        ORDER BY vb.created_at DESC";

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
                    <input type="text" class="form-control" id="searchVehicle" 
                           placeholder="Search vehicle bookings...">
                    <span class="input-group-text">
                        <i class="fas fa-search"></i>
                    </span>
                </div>
            </div>
            <a href="book-vehicle.php" class="btn btn-primary">
                <i class="fas fa-plus-circle me-2"></i>New Vehicle Booking
            </a>
        </div>

        <div class="table-responsive booking-table">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th scope="col" style="width: 25%;">Vehicle</th>
                        <th scope="col" style="width: 30%;">Booking Details</th>
                        <th scope="col" style="width: 20%;">Duration & Cost</th>
                        <th scope="col" style="width: 15%;">Status</th>
                        <th scope="col" style="width: 10%;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($booking = $result->fetch_assoc()): 
                        $pickup_date = new DateTime($booking['pickup_date']);
                        $return_date = new DateTime($booking['return_date']);
                        $today = new DateTime();
                        
                        if ($today < $pickup_date) {
                            $status = "Upcoming";
                            $status_class = "text-primary";
                            $can_modify = true;
                        } elseif ($today > $return_date) {
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
                                    <div class="vehicle-image me-3">
                                        <img src="<?php echo htmlspecialchars($booking['imageUrl']); ?>" 
                                             class="rounded shadow-sm"
                                             style="width: 48px; height: 48px; object-fit: cover;"
                                             onerror="this.src='https://via.placeholder.com/48?text=No+Image'"
                                             alt="<?php echo htmlspecialchars($booking['model']); ?>">
                                    </div>
                                    <div>
                                        <h6 class="mb-1 fw-semibold"><?php echo htmlspecialchars($booking['model']); ?></h6>
                                        <div class="text-muted" style="font-size: 0.75rem;">
                                            <span class="d-block">
                                                <i class="fas fa-car-side me-1" style="font-size: 0.75rem;"></i>
                                                <?php echo htmlspecialchars($booking['type']); ?>
                                            </span>
                                            <span class="d-block">
                                                <i class="fas fa-hashtag me-1" style="font-size: 0.75rem;"></i>
                                                <?php echo htmlspecialchars($booking['license_plate']); ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="booking-details">
                                    <div class="mb-2">
                                        <i class="fas fa-calendar-alt text-primary me-2" style="font-size: 0.875rem;"></i>
                                        <span class="fw-medium">Pickup:</span>
                                        <span class="ms-1"><?php echo $pickup_date->format('M d, Y'); ?></span>
                                    </div>
                                    <div class="mb-2">
                                        <i class="fas fa-calendar-check text-primary me-2" style="font-size: 0.875rem;"></i>
                                        <span class="fw-medium">Return:</span>
                                        <span class="ms-1"><?php echo $return_date->format('M d, Y'); ?></span>
                                    </div>
                                    <div>
                                        <i class="fas fa-map-marker-alt text-primary me-2" style="font-size: 0.875rem;"></i>
                                        <span class="fw-medium">Location:</span>
                                        <span class="ms-1"><?php echo htmlspecialchars($booking['pickup_location']); ?></span>
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
                                            $interval = $pickup_date->diff($return_date);
                                            echo $interval->days + 1; ?> days
                                        </span>
                                    </div>
                                    <div class="mb-2">
                                        <i class="fas fa-tag text-primary me-2" style="font-size: 0.875rem;"></i>
                                        <span class="fw-medium">Rate:</span>
                                        <span class="ms-1">Rs. <?php echo number_format($booking['daily_rate'], 2); ?>/day</span>
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
                                                onclick="modifyBooking(<?php echo $booking['booking_id']; ?>, 'vehicle')"
                                                data-bs-toggle="tooltip"
                                                title="Modify Booking"
                                                style="padding: 0.25rem 0.5rem;">
                                            <i class="fas fa-edit" style="font-size: 0.875rem;"></i>
                                        </button>
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-danger action-btn"
                                                onclick="confirmDelete(<?php echo $booking['booking_id']; ?>, 'vehicle')"
                                                data-bs-toggle="tooltip"
                                                title="Cancel Booking"
                                                style="padding: 0.25rem 0.5rem;">
                                            <i class="fas fa-times" style="font-size: 0.875rem;"></i>
                                        </button>
                                    <?php endif; ?>
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-info action-btn"
                                            onclick="window.location.href='booking-details.php?type=vehicle&id=<?php echo $booking['booking_id']; ?>'"
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
        <h4>No Vehicle Bookings Found</h4>
        <p>You haven't made any vehicle bookings yet. Start your journey by booking a vehicle now!</p>
        <a href="book-vehicle.php" class="btn btn-primary">
            <i class="fas fa-car me-2"></i>Book a Vehicle Now
        </a>
    </div>
<?php endif; ?>

<script>
document.getElementById('searchVehicle').addEventListener('keyup', function() {
    const searchText = this.value.toLowerCase();
    const rows = document.querySelectorAll('.booking-table tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchText) ? '' : 'none';
    });
});
</script> 