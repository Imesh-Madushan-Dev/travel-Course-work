<?php
session_start();
require_once '../config/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email = ? AND password = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['fullname'] = $user['fullname'];
        header("Location: dashboard/dashboard.php");
        exit();
    } else {
        $error = "Invalid email or password";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Travel Website</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="css/auth.css">
</head>

<body>
    <section class="auth-section">
        <div class="auth-container">
            <h2 style="display: flex; justify-content: space-between; align-items: center;">
                <a href="../index.html" class="back-icon">
                    <i class="fas fa-arrow-left" style="color: black;"></i>
                </a>
                <span>Welcome Back</span>
                <span style="width: 24px;"></span>
            </h2>
            <?php if (isset($error)) { ?>
                <div style="color: red; margin-bottom: 10px;"><?php echo $error; ?></div>
            <?php } ?>
            <form class="auth-form" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required placeholder="Enter your email">
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required placeholder="Enter your password">
                </div>
                <div class="form-group">
                    <label class="checkbox-container">
                        <input type="checkbox" name="remember">
                        Keep me signed in
                    </label>
                </div>
                <button type="submit" class="btn primary full-width">Sign In</button>
                <div class="auth-links">
                    <p>Don't have an account? <a href="register.php">Create Account</a></p>
                    <p><a href="forgot-password.html">Forgot Password?</a></p>
                </div>
            </form>
        </div>
    </section>
</body>
</html>