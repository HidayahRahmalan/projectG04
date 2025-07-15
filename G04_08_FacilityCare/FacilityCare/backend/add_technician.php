<?php
require_once '../connection.php';

function generateUserId($pdo)
{
    $stmt = $pdo->query("SELECT MAX(CAST(SUBSTRING(user_id, 2) AS UNSIGNED)) AS max_id FROM user WHERE position = 'technician'");
    $max = $stmt->fetchColumn();
    $next = $max ? $max + 1 : 1;
    return 'T' . str_pad($next, 3, '0', STR_PAD_LEFT);
}

// Enable errors
ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name         = $_POST['name'];
    $email        = $_POST['email'];
    $phone        = $_POST['phone'];
    $status       = $_POST['technician_status'];
    $specialities = $_POST['specialties'] ?? [];


    // Check email
    $checkEmail = $pdo->prepare("SELECT * FROM user WHERE email = ?");
    $checkEmail->execute([$email]);
    if ($checkEmail->rowCount() > 0) {
        die('Error: Email already exists.');
    }

    $userId = generateUserId($pdo);

    // Photo upload
    $profilePhoto = null;
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION);
        $photoFileName = $userId . '.' . $ext;
        $profilePhoto = 'images/tech_profile/' . $photoFileName;
        $targetDirectory = __DIR__ . '/../images/tech_profile/';
        if (!is_dir($targetDirectory)) {
            mkdir($targetDirectory, 0777, true);
        }
        move_uploaded_file($_FILES['profile_photo']['tmp_name'], $targetDirectory . $photoFileName);
    }

    try {
        $pdo->beginTransaction();

        // Insert into user
        $hashedPassword = password_hash('123456', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO user (user_id, name, email, position, password) VALUES (?, ?, ?, 'technician', ?)");
        $stmt->execute([$userId, $name, $email, $hashedPassword]);

        // Insert into technician
        $stmt = $pdo->prepare("INSERT INTO technician (technician_id, phone_number, technician_status, profile_photo) VALUES (?, ?, ?, ?)");
        $stmt->execute([$userId, $phone, $status, $profilePhoto]);

        // Insert technician specialities
        if (!empty($specialities)) {
            $stmt = $pdo->prepare("INSERT INTO technician_speciality (technician_id, speciality_id) VALUES (?, ?)");
            foreach ($specialities as $specId) {
                $stmt->execute([$userId, $specId]);
            }
        }

        $pdo->commit();
        header("Location: ../Admin/allTechnician.php?success=1");
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        die("Database Error: " . $e->getMessage());
    }
}
?>
