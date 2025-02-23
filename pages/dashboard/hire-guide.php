<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
require_once '../../config/db_connect.php';
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
                        <!-- Replace the existing button with this -->
                        <button class="btn btn-primary" onclick="window.location.href='book-guide-form.php?guide_id=<?php echo $guide['guide_id']; ?>'">
                            Book Guide
                        </button>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
    <script>
       
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