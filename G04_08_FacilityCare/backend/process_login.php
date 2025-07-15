<?php
// Move session_name() before any session_start()
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
        
        // Set session name based on role BEFORE starting session
        switch ($firstChar) {
            case 'A':
                session_name("admin_session");
                $redirect = "../Admin/dashboard.php";
                break;
            case 'S':
                session_name("staff_session");
                $redirect = "../homepagestaff.php";
                break;
            case 'T':
                session_name("technician_session");
                $redirect = "../technician/dashboardTech.php";
                break;
            default:
                $_SESSION['login_error'] = "Unrecognized user role.";
                header("Location: ../login.php");
                exit();
        }

        // Now start the session with the correct name
        session_start();
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['email'] = $user['email'];

        if ($firstChar === 'T') {
            $_SESSION['technician_id'] = $user['user_id'];
        }

        header("Location: $redirect");
        exit();

    } catch (PDOException $e) {
        $_SESSION['login_error'] = "Database error: " . $e->getMessage();
        header("Location: ../login.php");
        exit();
    }
}