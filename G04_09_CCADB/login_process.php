<?php
// Original login_process.php
session_start();
require_once 'db_conn.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT UserID, Username, Password, Role FROM User WHERE Username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['Password'])) {
            // Password is correct!
            $_SESSION['user_id'] = $user['UserID'];
            $_SESSION['username'] = $user['Username'];
            $_SESSION['role'] = $user['Role'];

            // Role-based redirection
            switch ($user['Role']) {
                case 'Admin':
                    header("Location: admin_dashboard.php");
                    break;
                case 'Doctor':
                    header("Location: doctor_dashboard.php");
                    break;
                case 'Patient':
                    header("Location: patient_dashboard.php");
                    break;
                default:
                    header("Location: login.php");
                    break;
            }
            exit();

        } else {
            // Password is not correct
            $_SESSION['login_error'] = "Invalid username or password.";
            header("Location: login.php");
            exit();
        }
    } else {
        // No user found
        $_SESSION['login_error'] = "Invalid username or password.";
        header("Location: login.php");
        exit();
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: login.php");
    exit();
}