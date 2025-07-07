<?php
session_start();
include 'dbConnection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Redirect if accessed directly without form submission
    header("Location: LOGIN.html");
    exit();
}

$UserEmail = $_POST['UserEmail'] ?? '';
$UserPass = $_POST['UserPass'] ?? '';

if (empty($UserEmail) || empty($UserPass)) {
    echo "<!DOCTYPE html><html><head>
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    </head><body>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Missing Information',
            text: 'Please enter both email and password.'
        }).then(() => window.history.back());
    </script>
    </body></html>";
    exit;
}

try {
    $stmt = $conn->prepare("SELECT * FROM user WHERE UserEmail = :UserEmail LIMIT 1");
    $stmt->execute([':UserEmail' => $UserEmail]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($UserPass, $user['UserPass'])) {
        $_SESSION['UserID'] = $user['UserID'];
        $_SESSION['UserName'] = $user['UserName'];
        $_SESSION['UserRole'] = $user['UserRole'];

        echo "<script>window.location.href = 'INDEXX.php';</script>";
    } else {
        echo "<!DOCTYPE html><html><head>
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        </head><body>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Login Failed',
                text: 'Incorrect email or password!'
            }).then(() => window.history.back());
        </script>
        </body></html>";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
