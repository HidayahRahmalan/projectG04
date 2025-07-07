<?php
session_start();
header('Content-Type: application/json');
include('dbConnection.php');

if (!isset($_SESSION['UserID'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$userID = $_SESSION['UserID'];
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');

if ($username === '' || $email === '') {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit();
}

try {
    $stmt = $conn->prepare("UPDATE user SET UserName = ?, UserEmail = ? WHERE UserID = ?");
    $success = $stmt->execute([$username, $email, $userID]);

    if ($success) {
        $_SESSION['UserName'] = $username; // update session
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Update failed.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
