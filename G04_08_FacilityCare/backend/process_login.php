

<?php
// Email & password form submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once '../connection.php';

    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        session_start();
        $_SESSION['login_error'] = "Please fill in both email and password.";
        header("Location: ../login.php");
        exit();
    }

    try {
        $stmt = $pdo->prepare("SELECT user_id, name, email, password FROM User WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            session_start();
            $_SESSION['login_error'] = "No user found with that email address.";
            header("Location: ../login.php");
            exit();
        }

        if (!password_verify($password, $user['password'])) {
            session_start();
            $_SESSION['login_error'] = "Incorrect password. Please try again.";
            header("Location: ../login.php");
            exit();
        }

        // Detect user role from user_id's first letter
        $firstChar = strtoupper(substr($user['user_id'], 0, 1));
        switch ($firstChar) {
            case 'A':
                session_name("admin_session");
                $redirect = "../Admin/dashboard.php";
                break;
            case 'S':
                session_name("staff_session");
                $redirect = "../homepageStaff.php";
                break;
            case 'T':
                session_name("technician_session");
                $redirect = "../technician/dashboardTech.php";
                break;
            default:
                session_start();
                $_SESSION['login_error'] = "Unrecognized user role.";
                header("Location: ../login.php");
                exit();
        }

        // Start session after session_name
        session_start();
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['email'] = $user['email'];

        // Optional: set technician_id if role is technician
        if ($firstChar === 'T') {
            $_SESSION['technician_id'] = $user['user_id'];
        }

        // Redirect to appropriate dashboard
        header("Location: $redirect");
        exit();

    } catch (PDOException $e) {
        session_start();
        $_SESSION['login_error'] = "Database error: " . $e->getMessage();
        header("Location: ../login.php");
        exit();
    }
}
