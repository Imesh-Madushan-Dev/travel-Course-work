<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
require_once '../../config/db_connect.php';
$page_title = "Book Hotel";
include 'includes/header.php';
?>
<body>
    <div class="dashboard-container">
        <?php include 'includes/sidebar.php'; ?>
        <div class="main-content">
            <header>
                <h1>Book a Hotel</h1>
                <div class="user-profile">
                    <i class="fas fa-user-circle"></i>
                    <span><?php echo htmlspecialchars($_SESSION['fullname']); ?></span>
                </div>
            </header>
            <div class="filter-section mb-4">
                <select id="districtFilter" class="form-select" onchange="filterHotels()">
                    <option value="">All Districts</option>
                    <option value="Colombo">Colombo</option>
                    <option value="Kandy">Kandy</option>
                    <option value="Galle">Galle</option>
                </select>
            </div>
            <div class="hotels-container">
                <?php
                $sql = "SELECT * FROM hotels WHERE available_rooms > 0";
                $result = $conn->query($sql);
                while($hotel = $result->fetch_assoc()): 
                ?>
                <div class="card hotel-card" data-district="<?php echo $hotel['district']; ?>">
                    <img 
                        src="<?php echo $hotel['imageUrl']; ?>" 
                        class="card-img-top"
                        onerror="handleImageError(this)"
                        alt="<?php echo $hotel['name']; ?>"
                    >
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $hotel['name']; ?></h5>
                        <div class="hotel-rating">
                            <?php for($i = 0; $i < $hotel['star_rating']; $i++): ?>
                                <i class="fas fa-star text-warning"></i>
                            <?php endfor; ?>
                        </div>
                        <p class="card-text">
                            <strong>Location:</strong> <?php echo $hotel['district']; ?><br>
                            <strong>Price:</strong> Rs. <?php echo number_format($hotel['price_per_night'], 2); ?> per night<br>
                            <strong>Available Rooms:</strong> <?php echo $hotel['available_rooms']; ?><br>
                            <strong>Description:</strong> <?php echo $hotel['description']; ?>
                        </p>
                        <button class="btn btn-primary" onclick="bookHotel(<?php echo $hotel['hotel_id']; ?>)">
                            Book Hotel
                        </button>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>

    <script>
        const PLACEHOLDER_IMAGE = 'https://via.placeholder.com/300x200?text=No+Image';

function handleImageError(img) {
    img.onerror = null;
    img.src = PLACEHOLDER_IMAGE;
}
    function filterHotels() {
        const district = document.getElementById('districtFilter').value;
        const cards = document.querySelectorAll('.hotel-card');
        
        cards.forEach(card => {
            if (!district || card.dataset.district === district) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }

    function bookHotel(hotelId) {
        window.location.href = 'book-hotel-form.php?hotel_id=' + hotelId;
    }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

