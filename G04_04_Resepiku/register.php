<?php
include 'connection.php'; // Include the database connection file
include 'register.html'; // Include the registration form HTML
// Only run when POST data exists
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = $_POST['fullname'] ?? '';
    $role = $_POST['role'] ?? '';
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Simple password hashing (optional but recommended)
    $hashed_password = md5($password);

    // SQL to insert user data
    $sql = "INSERT INTO user (FullName, UserRole, Username, Password, UserEmail)
            VALUES ( '$fullname', '$role', '$username', '$hashed_password', '$email')";

    if (mysqli_query($conn, $sql)) {
        echo "<h2>Pendaftaran Berjaya!</h2>";
        echo "<p><a href='login.html'>Klik sini untuk log masuk</a></p>";
    } else {
        echo "Ralat: " . mysqli_error($conn);
    }
}

mysqli_close($conn);
?>
