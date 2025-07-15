<?php
session_name("staff_session");
session_start();
require_once __DIR__ . '/../connection.php'; // Ensure $pdo is defined
$enumValues = [];

try {
    $query = "SHOW COLUMNS FROM report LIKE 'category'";
    $stmt = $pdo->query($query);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $type = $row['Type'];

        // Extract enum values
        if (preg_match("/^enum\((.*)\)$/", $type, $matches)) {
            $enum = explode(",", $matches[1]);
            foreach ($enum as $value) {
                $enumValues[] = trim($value, "'");
            }
        }
    }
} catch (PDOException $e) {
    // Optional: handle error
    echo "Error: " . $e->getMessage();
}
?>

<?php
$priorityValues = [];

try {
    $query = "SHOW COLUMNS FROM report LIKE 'priority'";
    $stmt = $pdo->query($query);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $type = $row['Type'];
        if (preg_match("/^enum\((.*)\)$/", $type, $matches)) {
            $enum = explode(",", $matches[1]);
            foreach ($enum as $value) {
                $priorityValues[] = trim($value, "'");
            }
        }
    }
} catch (PDOException $e) {
    echo "Error fetching priority values: " . $e->getMessage();
}
?>



<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_SESSION['user_id'])) {
        die("User not logged in.");
    }

    // Collect report data
    $user_id     = $_SESSION['user_id'];
    $title       = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $category    = $_POST['category'] ?? '';
    $priority    = $_POST['priority'] ?? '';
    $facilities  = $_POST['location'] ?? '';

    // Validate report fields
    if (empty($title) || empty($description) || empty($category) || empty($priority) || empty($facilities)) {
        die("All report fields are required.");
    }

    try {
        // Insert report first
        $stmt = $pdo->prepare("
            INSERT INTO Report (user_id, title, description, category, priority, facilities, created_at)
            VALUES (:user_id, :title, :description, :category, :priority, :facilities, NOW())
        ");
        $stmt->execute([
            ':user_id'     => $user_id,
            ':title'       => $title,
            ':description' => $description,
            ':category'    => $category,
            ':priority'    => $priority,
            ':facilities'  => $facilities
        ]);

        $report_id = $pdo->lastInsertId();

        // Handle media files
        if (!empty($_FILES['media']['name'][0])) {
            $uploadDir = 'uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            foreach ($_FILES['media']['tmp_name'] as $index => $tmpName) {
                $originalName = $_FILES['media']['name'][$index];
                $fileType     = $_FILES['media']['type'][$index];
                $fileSize     = $_FILES['media']['size'][$index];

                $ext = pathinfo($originalName, PATHINFO_EXTENSION);
                $newFileName = uniqid('media_', true) . '.' . $ext;
                $filePath = $uploadDir . $newFileName;

                if (move_uploaded_file($tmpName, $filePath)) {
                    $mediaType = str_starts_with($fileType, 'image/') ? 'image' : 'video';

                    // Example metadata
                    $metadata = json_encode([
                        'original_name' => $originalName,
                        'file_type'     => $fileType,
                        'file_size'     => $fileSize . ' bytes',
                        'uploaded_by'   => $user_id,
                        'path'          => $filePath
                    ]);

                    // Insert into Media table
                    $mediaStmt = $pdo->prepare("
                        INSERT INTO Media (report_id, media_type, file_path, metadata_text, uploaded_at, uploaded_by_role)
                        VALUES (:report_id, :media_type, :file_path, :metadata_text, NOW(), :role)
                    ");
                    $mediaStmt->execute([
                        ':report_id'     => $report_id,
                        ':media_type'    => $mediaType,
                        ':file_path'     => $filePath,
                        ':metadata_text' => $metadata,
                        ':role'          => 'staff'  // <-- sebab ni daripada staff
                    ]);

                    $ext = pathinfo($filePath, PATHINFO_EXTENSION);

                    if ($mediaType === 'video' && strtolower($ext) === 'mp4') {
                        $escapedPath = escapeshellarg($filePath);
                        $output = shell_exec("python ../ai_scripts/process_video.py $escapedPath 2>&1");
                        file_put_contents('log.txt', $output); // Simpan output python ke log.txt

                        // Tunggu file siap
                        $transcriptPath = str_replace('.mp4', '_transcript.txt', $filePath);
                        $summaryPath = str_replace('.mp4', '_summary.txt', $filePath);

                        $wait = 0;
                        while (!file_exists($transcriptPath) && $wait < 5) {
                            sleep(1);
                            $wait++;
                        }

                        $wait = 0;
                        while (!file_exists($summaryPath) && $wait < 5) {
                            sleep(1);
                            $wait++;
                        }

                        // Baru baca kalau memang betul2 fail text
                        if (file_exists($transcriptPath) && mime_content_type($transcriptPath) === 'text/plain') {
                            $transcript = file_get_contents($transcriptPath);
                        } else {
                            $transcript = null;
                        }

                        if (file_exists($summaryPath) && mime_content_type($summaryPath) === 'text/plain') {
                            $summary = file_get_contents($summaryPath);
                        } else {
                            $summary = null;
                        }

                        $updateStmt = $pdo->prepare("
                            UPDATE Media 
                            SET transcript = :transcript, summary = :summary 
                            WHERE file_path = :file_path
                        ");
                        $updateStmt->execute([
                            ':transcript' => $transcript,
                            ':summary'    => $summary,
                            ':file_path'  => $filePath
                        ]);
                    }
                }
            }
        }

        // Redirect or show success
        header("Location: ../reportListings.php?status=success");
        exit();
    } catch (PDOException $e) {
        die("Error submitting report: " . $e->getMessage());
    }
}

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate input, process upload, etc.
    if ($error) {
        $error = "Something went wrong!";
    } else {
        $success = "Report submitted successfully!";
    }

    // You can store these in session to show as pop-up later
    $_SESSION['error'] = $error;
    $_SESSION['success'] = $success;
    header('Location: ../newReport.php');
    exit;
}
?>
