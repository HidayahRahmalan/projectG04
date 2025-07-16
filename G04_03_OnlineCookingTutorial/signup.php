<?php
include 'db_connect.php'; // Ensure this creates $conn (MySQLi connection)

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $full_name = trim($_POST['full_name']);
    $role = $_POST['role'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $message = "Passwords do not match.";
    } else {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Check if username or email already exists
        $stmt = $conn->prepare("SELECT * FROM user WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->fetch_assoc()) {
            $message = "Username or email already in use.";
        } else {
            $stmt = $conn->prepare("INSERT INTO user (username, password_hash, email, full_name, role, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("sssss", $username, $password_hash, $email, $full_name, $role);
            if ($stmt->execute()) {
                header("Location: login.php?registered=1");
                exit();
            } else {
                $message = "Registration failed. Please try again.";
            }
        }
        $stmt->close();
    }
}
?>

<?php include 'header.php'; ?>

<?php if ($message): ?>
    <p style="color:red; text-align:center;"><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<form method="POST" action="signup.php">
    <label>Username:</label>
    <input type="text" name="username" required>

    <label>Email:</label>
    <input type="email" name="email" required>

    <label>Full Name:</label>
    <input type="text" name="full_name" required>

    <label>Role:</label>
    <select name="role" required>
        <option value="chef">Chef</option>
        <option value="student">Student</option>
    </select>

    <!-- Password field with reveal icon -->
    <div style="position: relative; margin-top: 10px;">
        <label for="password">Password:</label><br>
        <input type="password" name="password" id="password" required style="padding-right: 30px; height: 35px;">
        <span id="togglePassword" style="
            position: absolute;
            right: 10px;
            top: 58%;
            transform: translateY(-50%);
            cursor: pointer;
            line-height: 1;
            font-size: 18px;
        ">👁️</span>
    </div>

    <!-- Confirm Password field with reveal icon -->
    <div style="position: relative; margin-top: 10px;">
        <label for="confirm_password">Confirm Password:</label><br>
        <input type="password" name="confirm_password" id="confirm_password" required style="padding-right: 30px; height: 35px;">
        <span id="toggleConfirmPassword" style="
            position: absolute;
            right: 10px;
            top: 58%;
            transform: translateY(-50%);
            cursor: pointer;
            line-height: 1;
            font-size: 18px;
        ">👁️</span>
    </div>

    <button type="submit">Create Account</button>
</form>

<p style="text-align:center;">Already have an account? <a href="login.php">Login here</a>.</p>

<script>
// Toggle for password
const togglePassword = document.getElementById('togglePassword');
const passwordField = document.getElementById('password');

togglePassword.addEventListener('click', function () {
    const isPassword = passwordField.type === 'password';
    passwordField.type = isPassword ? 'text' : 'password';
    togglePassword.textContent = isPassword ? '🙈' : '👁️';
});

// Toggle for confirm password
const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
const confirmPasswordField = document.getElementById('confirm_password');

toggleConfirmPassword.addEventListener('click', function () {
    const isPassword = confirmPasswordField.type === 'password';
    confirmPasswordField.type = isPassword ? 'text' : 'password';
    toggleConfirmPassword.textContent = isPassword ? '🙈' : '👁️';
});
</script>

<?php include 'footer.php'; ?>
