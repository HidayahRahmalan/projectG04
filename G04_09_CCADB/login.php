<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Clinic Portal</title>
    <link rel="stylesheet" href="css/style.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body>
    <div class="main-content" style="padding-top: 60px;">
        <div class="content-card booking-form-container">
            <a href="index.php" class="home-icon-link" title="Return to Homepage">⌂</a>
            
            <h2>Portal Login</h2>
            <p>Please enter your credentials to access your dashboard.</p>

            <!-- 
              The form no longer needs an action/method attribute.
              We give it an ID to target it with JavaScript. 
            -->
            <form id="loginForm" class="booking-form" style="margin-top: 0;">
                
                <!-- This div will display our error messages dynamically -->
                <div id="errorMessageContainer"></div>

                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <button type="submit" id="loginButton" class="hero-button">Login</button>
                </div>
            </form>
            
            <div class="form-divider">
                <span>New to our portal?</span>
            </div>
            <div class="secondary-action">
                <p>Create an account to manage your appointments and records online.</p>
                <a href="register.php" class="secondary-button">Create Account</a>
            </div>
        </div>
    </div>

    <!-- The JavaScript that mimics the React example's behavior -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = document.getElementById('loginForm');
            const loginButton = document.getElementById('loginButton');
            const errorMessageContainer = document.getElementById('errorMessageContainer');

            loginForm.addEventListener('submit', async function(e) {
                // Prevent the default form submission (which causes a page reload)
                e.preventDefault();

                // Show a "loading" state on the button
                loginButton.textContent = 'Logging in...';
                loginButton.disabled = true;
                errorMessageContainer.innerHTML = ''; // Clear previous errors

                // Create a FormData object from the form
                const formData = new FormData(loginForm);

                try {
                    // Use fetch to send the form data to our new API script
                    const response = await fetch('login_ajax_process.php', {
                        method: 'POST',
                        body: formData
                    });

                    // Parse the JSON response from the server
                    const data = await response.json();

                    if (data.success) {
                        // SUCCESS! The server confirmed the login.
                        console.log('Login successful:', data.user);

                        // As in the React example, we can store user info if needed by other scripts
                        localStorage.setItem('userId', data.user.user_id);
                        localStorage.setItem('username', data.user.username);
                        localStorage.setItem('role', data.user.role);

                        // Redirect to the correct dashboard based on the role from the response
                        switch (data.user.role) {
                            case 'Admin':
                                window.location.href = 'admin_dashboard.php';
                                break;
                            case 'Doctor':
                                window.location.href = 'doctor_dashboard.php';
                                break;
                            case 'Patient':
                                window.location.href = 'patient_dashboard.php';
                                break;
                            default:
                                window.location.href = 'index.php'; // Fallback
                                break;
                        }
                    } else {
                        // FAILURE: The server said the login was invalid.
                        // Display the error message dynamically without reloading the page.
                        errorMessageContainer.innerHTML = `<p class="error-message">${data.message}</p>`;
                    }

                } catch (error) {
                    // Handle network errors or if the server returns non-JSON text
                    console.error('Login error:', error);
                    errorMessageContainer.innerHTML = `<p class="error-message">A server error occurred. Please try again later.</p>`;
                } finally {
                    // Re-enable the button regardless of success or failure
                    loginButton.textContent = 'Login';
                    loginButton.disabled = false;
                }
            });
        });
    </script>

</body>
</html>