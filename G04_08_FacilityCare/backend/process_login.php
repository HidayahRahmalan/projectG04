<?php
// Start session with default name first
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once '../connection.php';

    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $_SESSION['login_error'] = "Please fill in both email and password.";
        header("Location: ../login.php");
        exit();
    }

    try {
        $stmt = $pdo->prepare("SELECT user_id, name, email, password FROM User WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            $_SESSION['login_error'] = "No user found with that email address.";
            header("Location: ../login.php");
            exit();
        }

        if (!password_verify($password, $user['password'])) {
            $_SESSION['login_error'] = "Incorrect password. Please try again.";
            header("Location: ../login.php");
            exit();
        }

        // Detect user role from user_id's first letter
        $firstChar = strtoupper(substr($user['user_id'], 0, 1));

        // Determine redirect URL and session name
        $sessionName = '';
        $redirect = '';

        switch ($firstChar) {
            case 'A':
                $sessionName = "admin_session";
                $redirect = "../Admin/dashboard.php";
                break;
            case 'S':
                $sessionName = "staff_session";
                $redirect = "../homepagestaff.php";
                break;
            case 'T':
                $sessionName = "technician_session";
                $redirect = "../technician/dashboardTech.php";
                break;
            default:
                $_SESSION['login_error'] = "Unrecognized user role.";
                header("Location: ../login.php");
                exit();
        }

        // Close current session
        session_write_close();

        // Set new session name and restart session
        session_name($sessionName);
        session_start();

        // Set session variables
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['email'] = $user['email'];

        if ($firstChar === 'T') {
            $_SESSION['technician_id'] = $user['user_id'];
        }

        // Success message for modal
        $_SESSION['success_message'] = "Welcome back, " . $user['name'] . "!";

        header("Location: $redirect");
        exit();
    } catch (PDOException $e) {
        $_SESSION['login_error'] = "Database error: " . $e->getMessage();
        header("Location: ../login.php");
        exit();
    }
} else {
    $_SESSION['login_error'] = "Invalid request method.";
    header("Location: ../login.php");
    exit();
}
