<?php
session_start();
include 'db_connect.php'; // DB connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prepare statement
    $stmt = $conn->prepare("SELECT * FROM user WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();

    // Get result
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Verify password
    if ($user && password_verify($password, $user['password_hash'])) {
        // Save user info to session
        $_SESSION['userID'] = $user['userID'];          // from your DB
        $_SESSION['full_name'] = $user['full_name']; // adjust if your DB column name is different

        // Redirect to homepage
        header('Location: other_people_recipe_list.php');
        exit();
    } else {
        $login_failed = true;
    }

    $stmt->close();
}
?>

<?php include 'header.php'; ?>

<form method="POST">
    <div>
        <label for="username">Username:</label><br>
        <input type="text" name="username" id="username" required>
    </div>

    <div style="position: relative;">
        <label for="password">Password:</label><br>
        <input type="password" name="password" id="password" required style="padding-right: 30px; height: 35px;">
        
        <!-- Eye icon, now centered vertically -->
        <span id="togglePassword" style="
            position: absolute;
            right: 10px;
            top: 58%;
            transform: translateY(-50%);
            cursor: pointer;
            line-height: 1;
            font-size: 18px;
        ">
            👁️
        </span>

    </div> 
    <button type="submit">Login</button>
</form>

<script>
const togglePassword = document.getElementById('togglePassword');
const passwordField = document.getElementById('password');

togglePassword.addEventListener('click', function () {
    const isPassword = passwordField.type === 'password';
    passwordField.type = isPassword ? 'text' : 'password';
    togglePassword.textContent = isPassword ? '🙈' : '👁️';
});
</script>



<?php if (isset($login_failed) && $login_failed): ?>
    <p style="color:red;">Login failed! Please check your credentials.</p>
<?php endif; ?>

<p style="text-align:center;">Don't have an account? <a href="signup.php">Sign up here</a>.</p>

<?php include 'footer.php'; ?>
