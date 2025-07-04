<?php
session_start();
include 'connection.php';
include 'm_navbar.php';

if (!isset($_SESSION['staffID'])) {
    header("Location: login.php");
    exit();
}

$staffID = $_SESSION['staffID'];
$reportID = $_GET['report_id'] ?? $_POST['report_id'] ?? null;

if (!$reportID) {
    die("Report ID is required.");
}

$successMsg = '';
$errorMsg = '';
$formSubmitted = $_SERVER['REQUEST_METHOD'] === 'POST';

// Handle form submission
if ($formSubmitted) {
    if (!isset($_FILES['evidence']) || empty($_FILES['evidence']['name'][0])) {
        $errorMsg = "Please upload at least one image or video before submitting.";
    } else {
        $newStatus = 'Pending Approval';

        // Get current status
        $stmt = $conn->prepare("SELECT Status FROM Report WHERE ReportID = ?");
        $stmt->bind_param("s", $reportID);
        $stmt->execute();
        $stmt->bind_result($currentStatus);
        $stmt->fetch();
        $stmt->close();

        // Update Report
        $stmt = $conn->prepare("UPDATE Report SET Status = ?, LastUpdatedDate = NOW() WHERE ReportID = ?");
        $stmt->bind_param("ss", $newStatus, $reportID);
        $stmt->execute();
        $stmt->close();

// Log status change
$lastLog = $conn->query("SELECT LogID FROM MaintenanceStatusLog ORDER BY LogID DESC LIMIT 1")->fetch_assoc();
$lastLogNum = $lastLog ? (int)substr($lastLog['LogID'], 3) : 0;  // Extract number part and increment
$newLogID = 'LOG' . str_pad($lastLogNum + 1, 3, '0', STR_PAD_LEFT);

$stmt = $conn->prepare("INSERT INTO MaintenanceStatusLog (LogID, ReportID, StaffID, PreviousStatus, CurrentStatus, DateTime) VALUES (?, ?, ?, ?, ?, NOW())");
$stmt->bind_param("sssss", $newLogID, $reportID, $staffID, $currentStatus, $newStatus);
        $stmt->execute();
        $stmt->close();

        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $uploadErrors = [];

// Get last media id number (strip 'M'), or 0 if none
$lastMedia = $conn->query("SELECT MediaID FROM MediaFile ORDER BY CAST(SUBSTRING(MediaID, 2) AS UNSIGNED) DESC LIMIT 1")->fetch_assoc();
$lastNum = $lastMedia ? (int)substr($lastMedia['MediaID'], 1) : 0;

foreach ($_FILES['evidence']['name'] as $index => $name) {
    $tmpName = $_FILES['evidence']['tmp_name'][$index];
    $ext = pathinfo($name, PATHINFO_EXTENSION);
    $newFileName = $reportID . "_" . time() . "_$index." . $ext;
    $targetPath = $uploadDir . $newFileName;

    if (move_uploaded_file($tmpName, $targetPath)) {
        $lastNum++; // increment simple
        $mediaID = 'M' . str_pad($lastNum, 3, '0', STR_PAD_LEFT);

        $mediaType = in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif']) ? 'Image' : 'Video';

        $stmt = $conn->prepare("INSERT INTO MediaFile (MediaID, MediaType, FilePath, ReportID) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $mediaID, $mediaType, $targetPath, $reportID);
        $stmt->execute();
        $stmt->close();
    } else {
        $uploadErrors[] = "Failed to upload file: " . htmlspecialchars($name);
    }
}





if (empty($uploadErrors)) {
    header("Location: maintenance_index.php");
    exit();
} else {
    $errorMsg = implode('<br>', $uploadErrors);
}

    }
}

// Fetch report details
$stmt = $conn->prepare("
    SELECT r.ReportID, r.Title, r.Description, r.UrgencyLevel, r.Status, r.CreatedDate, r.LastUpdatedDate,
           s.StaffName, c.CategoryType
    FROM Report r
    JOIN Staff s ON r.StaffID = s.StaffID
    JOIN Category c ON r.CategoryID = c.CategoryID
    WHERE r.ReportID = ?
");
$stmt->bind_param("s", $reportID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Report not found.");
}
$report = $result->fetch_assoc();
$stmt->close();

// Fetch media
$stmtMedia = $conn->prepare("SELECT MediaType, FilePath FROM MediaFile WHERE ReportID = ?");
$stmtMedia->bind_param("s", $reportID);
$stmtMedia->execute();
$mediaResult = $stmtMedia->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Report Details - <?php echo htmlspecialchars($report['ReportID']); ?></title>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap');
    body {
      font-family: 'Roboto', sans-serif;
      background: linear-gradient(135deg, #2c3e50, #4b6584);
      margin: 0;
      padding: 30px;
      color: #34495e;
    }

    .container {
      max-width: 900px;
      background: #ecf0f1;
      border-radius: 10px;
      padding: 30px;
      margin: auto;
      box-shadow: 0 6px 18px rgba(0,0,0,0.2);
    }

    h1 {
      text-align: center;
      color: #f39c12;
      margin-bottom: 20px;
    }

    .detail-item {
      margin-bottom: 15px;
    }

    .label {
      font-weight: bold;
      color: #2d3436;
      margin-bottom: 5px;
    }

    .media-gallery {
      display: flex;
      flex-wrap: wrap;
      gap: 20px;
      margin-top: 10px;
      justify-content: flex-start;
    }

    .media-gallery img,
    .media-gallery video {
      width: 100%;
      max-width: 800px;
      height: auto;
      border-radius: 10px;
      box-shadow: 0 3px 10px rgba(0, 0, 0, 0.3);
    }

    .success { color: green; font-weight: bold; margin-bottom: 10px; }
    .error { color: red; font-weight: bold; margin-bottom: 10px; }

    form {
      margin-top: 30px;
    }

    select, input[type="file"] {
      padding: 10px;
      width: 100%;
      margin-bottom: 15px;
      border-radius: 5px;
      border: 1px solid #ccc;
    }

    button {
      background: #f39c12;
      border: none;
      padding: 12px;
      color: white;
      font-weight: bold;
      border-radius: 6px;
      cursor: pointer;
      width: 100%;
    }

    button:hover {
      background: #d78e0c;
    }
  </style>
</head>
<body>
<div class="container">
  <h1>Report Details - <?php echo htmlspecialchars($report['ReportID']); ?></h1>

  <?php if ($formSubmitted && $errorMsg): ?>
    <div class="error"><?php echo $errorMsg; ?></div>
  <?php endif; ?>

  <div class="detail-item"><div class="label">Title:</div><div><?php echo htmlspecialchars($report['Title']); ?></div></div>
  <div class="detail-item"><div class="label">Description:</div><div><?php echo nl2br(htmlspecialchars($report['Description'])); ?></div></div>
  <div class="detail-item"><div class="label">Category:</div><div><?php echo htmlspecialchars($report['CategoryType']); ?></div></div>
  <div class="detail-item"><div class="label">Urgency:</div><div><?php echo htmlspecialchars($report['UrgencyLevel']); ?></div></div>
  <div class="detail-item"><div class="label">Status:</div><div><?php echo htmlspecialchars($report['Status']); ?></div></div>
  <div class="detail-item"><div class="label">Reported By:</div><div><?php echo htmlspecialchars($report['StaffName']); ?></div></div>
  <div class="detail-item"><div class="label">Created:</div><div><?php echo htmlspecialchars($report['CreatedDate']); ?></div></div>
  <div class="detail-item"><div class="label">Last Updated:</div><div><?php echo htmlspecialchars($report['LastUpdatedDate']); ?></div></div>

  <?php if ($mediaResult && $mediaResult->num_rows > 0): ?>
    <div class="detail-item">
      <div class="label">Media:</div>
      <div class="media-gallery">
        <?php while ($media = $mediaResult->fetch_assoc()): ?>
          <?php if ($media['MediaType'] === 'Image'): ?>
            <img src="<?php echo htmlspecialchars($media['FilePath']); ?>" alt="Image" />
          <?php else: ?>
            <video controls>
              <source src="<?php echo htmlspecialchars($media['FilePath']); ?>" type="video/mp4">
              Your browser does not support video.
            </video>
          <?php endif; ?>
        <?php endwhile; ?>
      </div>
    </div>
  <?php endif; ?>

  <!-- Upload Form -->
  <form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="report_id" value="<?php echo htmlspecialchars($reportID); ?>">
    <label class="label">Upload Image/Video Evidence:</label>
    <input type="file" name="evidence[]" accept="image/*,video/*" multiple required>
    <button type="submit">Done</button>
  </form>
</div>
</body>
</html>

<?php
$stmtMedia->close();
$conn->close();
?>
