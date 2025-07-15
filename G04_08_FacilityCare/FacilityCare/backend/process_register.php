<?php
session_start();
include '../connection.php'; // Contains $pdo (PDO connection)

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($fullname) || empty($email) || empty($password)) {
        $_SESSION['register_error'] = 'Please fill in all required fields.';
        header('Location: ../register.php');
        exit;
    }

    try {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM user WHERE email = ?");
        $stmt->execute([$email]);
        $emailCount = $stmt->fetchColumn();

        if ($emailCount > 0) {
            $_SESSION['register_error'] = "Email is already registered.";
            header('Location: ../register.php');
            exit;
        }

        // Generate new user_id starting with 'S'
        $prefix = 'S';
        $stmt = $pdo->query("SELECT MAX(CAST(SUBSTRING(user_id, 2) AS UNSIGNED)) AS max_id FROM user WHERE user_id LIKE '{$prefix}%'");
        $row = $stmt->fetch();
        $nextIdNum = $row['max_id'] !== null ? intval($row['max_id']) + 1 : 1;
        $user_id = $prefix . str_pad($nextIdNum, 3, '0', STR_PAD_LEFT);

        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $role = 'staff';

        // Insert new user
        $insert = $pdo->prepare("INSERT INTO user (user_id, name, email, password, role) VALUES (?, ?, ?, ?, ?)");
        $insert->execute([$user_id, $fullname, $email, $hashed_password, $role]);

        // After successful registration, you can set success message in session and redirect to login
        $_SESSION['register_success'] = "Staff registered successfully! Your User ID is: $user_id";
        header('Location: ../login.php');
        exit;

    } catch (Exception $e) {
        $_SESSION['register_error'] = "Error: " . $e->getMessage();
        header('Location: ../register.php');
        exit;
    }
} else {
    echo "Invalid request method.";
}
