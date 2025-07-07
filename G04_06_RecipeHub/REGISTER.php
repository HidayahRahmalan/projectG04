<?php
session_start();
include 'dbConnection.php';

$UserName = $_POST['UserName'] ?? '';
$UserEmail = $_POST['UserEmail'] ?? '';
$UserPass = $_POST['UserPass'] ?? '';
$UserRole = $_POST['UserRole'] ?? '';

if (empty($UserName) || empty($UserEmail) || empty($UserPass) || empty($UserRole)) {
    die("❌ All fields are required.");
}

$hashedPassword = password_hash($UserPass, PASSWORD_BCRYPT);

$sql = "INSERT INTO user (UserName, UserEmail, UserPass, UserRole)
        VALUES (:UserName, :UserEmail, :UserPass, :UserRole)";
$stmt = $conn->prepare($sql);

try {
    $stmt->execute([
        ':UserName' => $UserName,
        ':UserEmail' => $UserEmail,
        ':UserPass' => $hashedPassword,
        ':UserRole' => $UserRole
    ]);
    echo "<script>alert('✅ Registration successful!'); window.location.href = 'login.html';</script>";
} catch (PDOException $e) {
    echo "❌ Registration failed: " . $e->getMessage();
}
?>
