<?php
session_start();
require_once '../connection.php';

// Semak jika technician_id wujud
if (!isset($_POST['technician_id'])) {
    die("Invalid request.");
}

$technicianId = $_POST['technician_id'];
$name = trim($_POST['name']);
$email = trim($_POST['email']);
$phone = trim($_POST['phone']);
$status = trim($_POST['technician_status']);

try {
    // 1. Handle gambar (jika ada upload)
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../images/tech_profile/';
        $ext = pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION);
        $newFileName = $technicianId . '.' . $ext;
        $uploadPath = $uploadDir . $newFileName;

        // Pastikan folder wujud
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Pindahkan fail ke folder
        move_uploaded_file($_FILES['profile_photo']['tmp_name'], $uploadPath);

        // Path simpan dalam DB
        $profilePhotoPath = 'images/tech_profile/' . $newFileName;

        // Update user dengan gambar
        $stmt = $pdo->prepare("
            UPDATE user 
            SET name = ?, email = ?, phone = ?, position = 'technician', profile_pic = ?
            WHERE user_id = ?
        ");
        $stmt->execute([$name, $email, $phone, $profilePhotoPath, $technicianId]);
    } else {
        // Update user tanpa ubah gambar
        $stmt = $pdo->prepare("
            UPDATE user 
            SET name = ?, email = ?, phone = ?, position = 'technician'
            WHERE user_id = ?
        ");
        $stmt->execute([$name, $email, $phone, $technicianId]);
    }

    // 2. Update status dalam technician table
    $stmt = $pdo->prepare("
        UPDATE technician 
        SET phone_number = ?, technician_status = ?
        WHERE technician_id = ?
    ");
    $stmt->execute([$phone, $status, $technicianId]);

    $_SESSION['success'] = "Technician updated successfully.";
    header("Location: ../Admin/allTechnician.php");
    exit();

} catch (PDOException $e) {
    die("Error updating technician: " . $e->getMessage());
}