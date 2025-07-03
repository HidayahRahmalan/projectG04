<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - Clinic Portal</title>
    <!-- It uses your main stylesheet -->
    <link rel="stylesheet" href="css/style.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body>
    <div class="main-content" style="padding-top: 60px;">
        <div class="content-card booking-form-container">
            <!-- THE NEW HOME ICON BUTTON IS HERE -->
            <a href="index.php" class="home-icon-link" title="Return to Homepage">⌂</a>

            <h2>Create an Account</h2>
            <p>Register to start your journey with us.</p>
            
            <form action="register_process.php" method="POST" class="booking-form" style="margin-top: 0;">
                <?php
                if (isset($_SESSION['register_error'])) {
                    echo '<p class="error-message">' . htmlspecialchars($_SESSION['register_error']) . '</p>';
                    unset($_SESSION['register_error']);
                }
                ?>
                <div class="form-group">
                    <label for="name">Full Name:</label>
                    <input type="text" id="name" name="name" required />
                </div>
                <div class="form-group">
                    <label for="email">Email Address:</label>
                    <input type="email" id="email" name="email" required />
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required />
                </div>
                <div class="form-group">
                    <button type="submit" class="hero-button">Register</button>
                </div>
            </form>

            <div class="form-divider">
                <span>Already have an account?</span>
            </div>
            <div class="secondary-action">
                <p>Log in to access your existing dashboard and appointments.</p>
                <a href="login.php" class="secondary-button">Login Here</a>
            </div>
        </div>
    </div>
</body>
</html>