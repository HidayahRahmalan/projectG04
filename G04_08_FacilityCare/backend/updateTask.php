<?php
echo '<pre>';
print_r($_FILES);
echo '</pre>';

session_start();
require_once '../connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reportId = $_POST['report_id'];
    $status = $_POST['status'];
    $notes = trim($_POST['notes']) ?? null;
    $changedBy = $_SESSION['technician_id'];
    $action = $_POST['action'];

    if ($action === 'complete') {
        $status = 'resolved';
    } elseif ($action === 'cancel') {
        $status = 'resolved'; // atau boleh abaikan kalau taknak log cancel
    }

    if (empty($status)) {
        $status = 'in_progress'; // fallback
    }

    // Insert into statuslog
    $stmt = $pdo->prepare("
        INSERT INTO statuslog (report_id, status, notes, changed_by, timestamp)
        VALUES (?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$reportId, $status, $notes, $changedBy]);

    if (!empty($_FILES['media']['name'][0])) {
        $uploadDir = 'uploads/technician/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        foreach ($_FILES['media']['tmp_name'] as $index => $tmpName) {
            $originalName = $_FILES['media']['name'][$index];
            $fileName = uniqid('photo_') . '_' . basename($originalName);
            $filePath = $uploadDir . $fileName;

            echo "Trying to upload: $originalName to $filePath<br>";

            if (move_uploaded_file($tmpName, $filePath)) {
                echo "✅ Upload success<br>";

                // Optional: detect type
                $fileType = mime_content_type($filePath);
                $mediaType = str_starts_with($fileType, 'image') ? 'image' : 'video';

                $uploaderRole = 'technician'; // sebab ni datang dari form update technician

                $stmt = $pdo->prepare("
                    INSERT INTO media (report_id, file_path, media_type, uploaded_at, uploaded_by_role)
                    VALUES (?, ?, ?, NOW(), ?)
                ");
                $stmt->execute([
                    $reportId,
                    'uploads/technician/' . $fileName,
                    $mediaType,
                    $uploaderRole
                ]);

                echo "✅ Inserted into media<br>";
            } else {
                echo "❌ Upload failed<br>";
                echo "Temp name: $tmpName<br>";
                echo "Is uploaded file: " . (is_uploaded_file($tmpName) ? 'yes' : 'no') . "<br>";
                echo "Upload dir writable: " . (is_writable($uploadDir) ? 'yes' : 'no') . "<br>";
            }
        }
    }


    // Redirect back to task detail
    header("Location: ../technician/taskDetail.php?report_id=" . $reportId);
    exit();
}
