<?php
session_start();
require_once '../config/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $isAdmin = ($_POST['admin_login'] === "1");

    if ($isAdmin) {
        // Admin login logic
        $sql = "SELECT * FROM admins WHERE email = ? AND password = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $email, $password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $admin = $result->fetch_assoc();
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['name'];
            $_SESSION['is_admin'] = true;
            header("Location: dashboard/admin/dashboard.php");
            exit();
        } else {
            $error = "Invalid admin credentials";
        }
    } else {
        // Regular user login logic
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
    <style>
        .login-toggle {
            display: flex;
            margin-bottom: 20px;
            border-radius: 5px;
            overflow: hidden;
            border: 1px solid #ddd;
        }
        
        .login-toggle button {
            flex: 1;
            padding: 10px;
            border: none;
            background: #f5f5f5;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .login-toggle button.active {
            background: #4e73df;
            color: white;
        }
        
        .login-toggle button:first-child {
            border-right: 1px solid #ddd;
        }
        
        .admin-icon {
            margin-right: 5px;
        }
    </style>
</head>

<body>
    <section class="auth-section">
        <div class="auth-container">
            <h2 style="display: flex; justify-content: space-between; align-items: center;">
                <a href="../index.html" class="back-icon">
                    <i class="fas fa-arrow-left" style="color: black;"></i>
                </a>
                <span id="login-title">Welcome Back</span>
                <span style="width: 24px;"></span>
            </h2>
            <?php if (isset($error)) { ?>
                <div style="color: red; margin-bottom: 10px;"><?php echo $error; ?></div>
            <?php } ?>

            <div class="login-toggle">
                <button type="button" id="user-toggle" class="active">User Login</button>
                <button type="button" id="admin-toggle"><i class="fas fa-user-shield admin-icon"></i>Admin Login</button>
            </div>

            <form class="auth-form" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <input type="hidden" id="admin_login" name="admin_login" value="0">
                
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
                <button type="submit" class="btn primary full-width" id="sign-in-btn">Sign In</button>
                <div class="auth-links" id="register-link">
                    <p>Don't have an account? <a href="register.php">Create Account</a></p>
                </div>
            </form>
        </div>
    </section>

    <script>
        // Toggle between user and admin login
        document.getElementById('user-toggle').addEventListener('click', function() {
            document.getElementById('admin_login').value = '0';
            document.getElementById('login-title').textContent = 'Welcome Back';
            document.getElementById('sign-in-btn').textContent = 'Sign In';
            document.getElementById('register-link').style.display = 'block';
            
            document.getElementById('user-toggle').classList.add('active');
            document.getElementById('admin-toggle').classList.remove('active');
        });
        
        document.getElementById('admin-toggle').addEventListener('click', function() {
            document.getElementById('admin_login').value = '1';
            document.getElementById('login-title').textContent = 'Admin Login';
            document.getElementById('sign-in-btn').textContent = 'Admin Sign In';
            document.getElementById('register-link').style.display = 'none';
            
            document.getElementById('admin-toggle').classList.add('active');
            document.getElementById('user-toggle').classList.remove('active');
        });
    </script>
</body>
</html>