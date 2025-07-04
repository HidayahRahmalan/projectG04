<?php
session_start();
if (!isset($_SESSION['staffID'])) {
    header("Location: login.php");
    exit();
}

include 'connection.php';
include 'navbar.php';

$error = '';
$success = '';

// Fetch categories for dropdown
$categories = [];
$catResult = $conn->query("SELECT CategoryID, CategoryType FROM Category ORDER BY CategoryType");
if ($catResult) {
    while ($row = $catResult->fetch_assoc()) {
        $categories[] = $row;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $urgency = $_POST['urgency'];
    $categoryId = $_POST['category'];
    $staffId = $_SESSION['staffID'];
    $status = 'Reported';

    // Validate required fields
    if (empty($title) || empty($description) || empty($categoryId)) {
        $error = "Please fill in all required fields.";
    } else {
        // Generate ReportID - example: "R001"
        $lastReport = $conn->query("SELECT ReportID FROM Report ORDER BY ReportID DESC LIMIT 1")->fetch_assoc();
        $lastIdNum = $lastReport ? (int)substr($lastReport['ReportID'], 1) : 0;
        $newReportId = 'R' . str_pad($lastIdNum + 1, 3, '0', STR_PAD_LEFT);

        // Insert into Report table
        $stmt = $conn->prepare("INSERT INTO Report (ReportID, Title, Description, UrgencyLevel, Status, StaffID, CategoryID) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $newReportId, $title, $description, $urgency, $status, $staffId, $categoryId);
        if ($stmt->execute()) {
            // Handle media uploads
            $uploadDir = "uploads/";
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            if (!empty($_FILES['media']['name'][0])) {
                foreach ($_FILES['media']['tmp_name'] as $key => $tmpName) {
                    $fileName = basename($_FILES['media']['name'][$key]);
                    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                    $allowedExt = ['jpg','jpeg','png','gif','mp4','mov','avi','wmv'];

                    if (!in_array($fileExt, $allowedExt)) {
                        $error = "File type not allowed: $fileName";
                        break;
                    }

                    $newFileName = $newReportId . '_' . time() . '_' . $key . '.' . $fileExt;
                    $filePath = $uploadDir . $newFileName;

                    if (move_uploaded_file($tmpName, $filePath)) {
                        $mediaType = in_array($fileExt, ['jpg','jpeg','png','gif']) ? 'Image' : 'Video';

                        // Generate MediaID - example: "M001"
                        $lastMedia = $conn->query("SELECT MediaID FROM MediaFile ORDER BY MediaID DESC LIMIT 1")->fetch_assoc();
                        $lastMediaNum = $lastMedia ? (int)substr($lastMedia['MediaID'], 1) : 0;
                        $newMediaId = 'M' . str_pad($lastMediaNum + 1, 3, '0', STR_PAD_LEFT);

                        $stmtMedia = $conn->prepare("INSERT INTO MediaFile (MediaID, MediaType, FilePath, ReportID) VALUES (?, ?, ?, ?)");
                        $stmtMedia->bind_param("ssss", $newMediaId, $mediaType, $filePath, $newReportId);
                        $stmtMedia->execute();
                        $stmtMedia->close();
                    } else {
                        $error = "Failed to upload file: $fileName";
                        break;
                    }
                }
            }

            if (!$error) {
                $success = "Report submitted successfully!";
                // Clear form variables
                $title = $description = '';
            }
        } else {
            $error = "Failed to submit report. Please try again.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Submit Maintenance Report</title>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap');

body {
  margin: 0;
  min-height: 100vh;
  font-family: 'Roboto', sans-serif;
  background: linear-gradient(135deg, #2c3e50, #4b6584);
  padding: 40px 0;

  /* Remove flex centering */
  display: block;
}

.form-container {
  background: #ecf0f1;
  padding: 35px 40px;
  border-radius: 10px;
  box-shadow: 0 8px 20px rgba(0,0,0,0.3);
  width: 400px;
  position: relative;
  margin: 60px auto 0 auto; 
}

  .form-container::before {
    content: "\1F527"; /* wrench emoji */
    font-size: 50px;
    position: absolute;
    top: -40px;
    left: calc(50% - 25px);
    color: #f39c12;
  }

  h2 {
    margin: 0 0 25px 0;
    text-align: center;
    color: #34495e;
  }

  label {
    display: block;
    font-weight: 600;
    margin-bottom: 6px;
    color: #2d3436;
  }

  input[type="text"], textarea, select {
    width: 100%;
    padding: 12px 15px;
    border: 1.5px solid #bdc3c7;
    border-radius: 6px;
    font-size: 16px;
    margin-bottom: 18px;
    resize: vertical;
    transition: border-color 0.3s ease;
  }

  input[type="text"]:focus, textarea:focus, select:focus {
    border-color: #f39c12;
    outline: none;
  }

  input[type="file"] {
    margin-top: -8px;
    margin-bottom: 20px;
  }

  button {
    width: 100%;
    background: #f39c12;
    border: none;
    padding: 14px 0;
    border-radius: 6px;
    font-size: 16px;
    color: white;
    font-weight: 700;
    cursor: pointer;
    transition: background-color 0.3s ease;
  }

  button:hover {
    background: #d88e0a;
  }

  .message {
    text-align: center;
    font-weight: 600;
    margin-top: 15px;
  }

  .error-message {
    color: #e74c3c;
  }

  .success-message {
    color: #27ae60;
  }
</style>
</head>
<body>
  <div class="form-container">
    <h2>Submit Maintenance Report</h2>
    <form method="POST" enctype="multipart/form-data">
      <label for="title">Title <span style="color:red">*</span></label>
      <input type="text" id="title" name="title" value="<?php echo isset($title) ? htmlspecialchars($title) : ''; ?>" required />

      <label for="description">Description <span style="color:red">*</span></label>
      <textarea id="description" name="description" rows="5" required><?php echo isset($description) ? htmlspecialchars($description) : ''; ?></textarea>

      <label for="urgency">Urgency Level <span style="color:red">*</span></label>
      <select id="urgency" name="urgency" required>
        <option value="Low" <?php if(isset($urgency) && $urgency=='Low') echo 'selected'; ?>>Low</option>
        <option value="Medium" <?php if(isset($urgency) && $urgency=='Medium') echo 'selected'; ?>>Medium</option>
        <option value="High" <?php if(isset($urgency) && $urgency=='High') echo 'selected'; ?>>High</option>
        <option value="Critical" <?php if(isset($urgency) && $urgency=='Critical') echo 'selected'; ?>>Critical</option>
      </select>

      <label for="category">Category <span style="color:red">*</span></label>
      <select id="category" name="category" required>
        <option value="">-- Select Category --</option>
        <?php foreach($categories as $cat): ?>
          <option value="<?php echo htmlspecialchars($cat['CategoryID']); ?>"
            <?php if(isset($categoryId) && $categoryId == $cat['CategoryID']) echo 'selected'; ?>>
            <?php echo htmlspecialchars($cat['CategoryType']); ?>
          </option>
        <?php endforeach; ?>
      </select>

      <label for="media">Upload Images/Videos</label>
      <input type="file" id="media" name="media[]" multiple accept="image/*,video/*" />

      <button type="submit">Submit Report</button>
    </form>

    <?php if ($error): ?>
      <div class="message error-message"><?php echo htmlspecialchars($error); ?></div>
    <?php elseif ($success): ?>
      <div class="message success-message"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
  </div>
</body>
</html>
