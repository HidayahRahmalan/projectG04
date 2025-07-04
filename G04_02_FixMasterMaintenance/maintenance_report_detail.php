<?php
session_start();
include 'config.php';
include 'ai_helper.php'; 
include 'connection.php';

if (!isset($_GET['report_id'])) {
    die("Report ID is required.");
}
$reportID = $_GET['report_id'];

// --- MODIFIED: Added r.AISuggestion to the query ---
$stmt = $conn->prepare("
    SELECT r.ReportID, r.Title, r.Description, r.UrgencyLevel, r.Status, r.CreatedDate, r.LastUpdatedDate, r.AISuggestion,
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

// Fetch associated media files
$stmtMedia = $conn->prepare("SELECT MediaType, FilePath FROM MediaFile WHERE ReportID = ?");
$stmtMedia->bind_param("s", $reportID);
$stmtMedia->execute();
$mediaResult = $stmtMedia->get_result();

// --- NEW AND IMPROVED AI LOGIC BLOCK ---
// First, get the suggestion that might already be saved in the database
$aiSuggestion = $report['AISuggestion'];

// If there is no saved suggestion, then (and only then) do we call the AI
if (empty($aiSuggestion)) {
    $firstImagePath = '';

    // Find the first image associated with this specific report
    if ($mediaResult->num_rows > 0) {
        while ($media = $mediaResult->fetch_assoc()) {
            if ($media['MediaType'] === 'Image') {
                $firstImagePath = $media['FilePath'];
                break; // Stop after finding the first image
            }
        }
        // IMPORTANT: Reset the result pointer so the media display loop below still works
        $mediaResult->data_seek(0);
    }

    // If we found an image path, proceed to call the AI
    if (!empty($firstImagePath)) {
        try {
            // Call the AI helper function to get a new suggestion
            $aiSuggestion = getAiSuggestionForImage($firstImagePath);

            // CRITICAL: If the suggestion was successful, save it to the database
            // We check that it's not empty and doesn't start with "Error:"
            if (!empty($aiSuggestion) && !str_starts_with($aiSuggestion, 'Error:')) {
                $stmtUpdate = $conn->prepare("UPDATE Report SET AISuggestion = ? WHERE ReportID = ?");
                $stmtUpdate->bind_param("ss", $aiSuggestion, $reportID);
                $stmtUpdate->execute();
                $stmtUpdate->close();
            }
            
        } catch (Exception $e) {
            $aiSuggestion = "An error occurred while generating the AI suggestion: " . $e->getMessage();
        }
    }
}
// --- END OF NEW LOGIC BLOCK ---

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Report Details - <?php echo htmlspecialchars($report['ReportID']); ?></title>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap');
  body { font-family: 'Roboto', sans-serif; background: linear-gradient(135deg, #2c3e50, #4b6584); margin: 0; padding: 30px; color: #34495e; }
  .container { max-width: 700px; margin: auto; background: #ecf0f1; border-radius: 10px; padding: 30px; box-shadow: 0 6px 18px rgba(0,0,0,0.3); }
  h1 { color: #f39c12; margin-bottom: 20px; text-align: center; }
  .detail-item { margin-bottom: 15px; }
  .label { font-weight: 700; margin-bottom: 5px; color: #2d3436; }

  body {
    font-family: 'Roboto', sans-serif;
    background: linear-gradient(135deg, #2c3e50, #4b6584);
    margin: 0;
    padding: 30px;
    color: #34495e;
  }

  .container {
    max-width: 700px;
    margin: auto;
    background: #ecf0f1;
    border-radius: 10px;
    padding: 30px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.3);
  }

  h1 {
    color: #f39c12;
    margin-bottom: 20px;
    text-align: center;
  }

  .detail-item {
    margin-bottom: 15px;
  }

  .label {
    font-weight: 700;
    margin-bottom: 5px;
    color: #2d3436;
  }

  form {
    margin-top: 25px;
  }

  select, button {
    font-size: 16px;
    padding: 10px 15px;
    border-radius: 6px;
    border: 1.5px solid #bdc3c7;
    margin-right: 10px;
  }

  button {
    background: #f39c12;
    color: white;
    border: none;
    cursor: pointer;
    font-weight: 700;
  }

  button:hover {
    background: #d88e0a;
  }

  .message {
    font-weight: 600;
    margin: 15px 0;
    text-align: center;
  }
  .success {
    color: #27ae60;
  }
  .error {
    color: #e74c3c;
  }
  .ai-suggestion-box {
    background-color: #fffbe6;
    border-left: 5px solid #f39c12;
    padding: 15px;
    margin-top: 20px;
    border-radius: 5px;
    font-size: 15px;
    line-height: 1.6;
  }
  .ai-suggestion-box .label {
    font-size: 18px;
    color: #c0392b;
    display: flex;
    align-items: center;
  }
  .ai-suggestion-box .label:before {
    content: '✨';
    margin-right: 8px;
    font-size: 20px;
  }
</style>
</head>
<body>
  <div class="container">
    <h1>Report Details - <?php echo htmlspecialchars($report['ReportID']); ?></h1>

    <div class="detail-item">
      <div class="label">Title:</div>
      <div><?php echo htmlspecialchars($report['Title']); ?></div>
    </div>
    <div class="detail-item">
      <div class="label">Description:</div>
      <div><?php echo nl2br(htmlspecialchars($report['Description'])); ?></div>
    </div>
     <div class="detail-item">
      <div class="label">Category:</div>
      <div><?php echo htmlspecialchars($report['CategoryType']); ?></div>
    </div>
    <div class="detail-item">
      <div class="label">Urgency Level:</div>
      <div><?php echo htmlspecialchars($report['UrgencyLevel']); ?></div>
    </div>
    <div class="detail-item">
      <div class="label">Status:</div>
      <div><?php echo htmlspecialchars($report['Status']); ?></div>
    </div>
    <div class="detail-item">
      <div class="label">Reported By:</div>
      <div><?php echo htmlspecialchars($report['StaffName']); ?></div>
    </div>
    <div class="detail-item">
      <div class="label">Created Date:</div>
      <div><?php echo htmlspecialchars($report['CreatedDate']); ?></div>
    </div>
    <div class="detail-item">
      <div class="label">Last Updated:</div>
      <div><?php echo htmlspecialchars($report['LastUpdatedDate']); ?></div>
    </div>

    <!-- Media display loop (no changes needed here) -->
    <?php if ($mediaResult->num_rows > 0): ?>
      <div class="detail-item">
        <div class="label">Media:</div>
        <div class="media-gallery">
          <?php while ($media = $mediaResult->fetch_assoc()): ?>
            <?php if ($media['MediaType'] === 'Image'): ?>
              <img src="<?php echo htmlspecialchars($media['FilePath']); ?>" alt="Report Image" style="max-width: 100%; height: auto; margin-bottom: 10px;"> 
            <?php elseif ($media['MediaType'] === 'Video'): ?>
              <video width="100%" controls>
                <source src="<?php echo htmlspecialchars($media['FilePath']); ?>" type="video/mp4">
              </video>
            <?php endif; ?>
          <?php endwhile; ?>
        </div>
      </div>
    <?php endif; ?>

    <!-- AI suggestion display (no changes needed here) -->
    <?php if (!empty($aiSuggestion)): ?>
      <div class="ai-suggestion-box">
        <div class="label">AI Maintenance Suggestion</div>
        <div>
            <?php echo nl2br(htmlspecialchars($aiSuggestion)); ?>
        </div>
      </div>
    <?php endif; ?>

  </div>
</body>
</html>

<?php
$stmt->close();
$stmtMedia->close();
$conn->close();
?>