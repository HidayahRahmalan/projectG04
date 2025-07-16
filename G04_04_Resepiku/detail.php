<?php
session_start();
include 'connection.php';
include 'headerdetail.php';

// ---------- HANDLE COMMENT SUBMISSION ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment']) && isset($_POST['recipeID'])) {
    $comment = $_POST['comment'];
    $recipeID = $_POST['recipeID'];
    $userID = $_SESSION['UserID'] ?? null;

    if (!$userID) {
        echo "<script>alert('Sila log masuk terlebih dahulu.'); window.location.href='login.html';</script>";
        exit;
    }

    // Generate FeedbackID
    $checkID = $conn->query("SELECT FeedbackID FROM FEEDBACK ORDER BY FeedbackID DESC LIMIT 1");
    if ($checkID->num_rows > 0) {
        $lastID = $checkID->fetch_assoc()['FeedbackID'];
        $num = intval(substr($lastID, 2)) + 1;
        $newID = 'FB' . str_pad($num, 4, '0', STR_PAD_LEFT);
    } else {
        $newID = 'FB0001';
    }

    $imageData = null;
    $videoPath = null;
    $commentType = '';

    // Handle image
    if (isset($_FILES['image']) && $_FILES['image']['size'] > 0) {
        $imageData = file_get_contents($_FILES['image']['tmp_name']);
        $commentType .= 'Image ';
    }

    // Handle video
    if (isset($_FILES['video']) && $_FILES['video']['size'] > 0) {
        $videoName = uniqid('vid_') . "_" . basename($_FILES['video']['name']);
        $targetVideo = "uploads/" . $videoName;
        move_uploaded_file($_FILES['video']['tmp_name'], $targetVideo);
        $videoPath = $targetVideo;
        $commentType .= 'Video ';
    }

    if ($commentType === '') $commentType = 'Text';

    $stmt = $conn->prepare("INSERT INTO FEEDBACK (FeedbackID, CommentType, Comment, VideoAttachment, ImageAttachment, UserID, RecipeID) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $newID, $commentType, $comment, $videoPath, $imageData, $userID, $recipeID);

    if ($stmt->execute()) {
        echo "<script>alert('Maklum balas berjaya dihantar!'); window.location.href='detail.php?id=$recipeID';</script>";
        exit;
    } else {
        echo "<script>alert('Ralat semasa menghantar maklum balas: " . $stmt->error . "');</script>";
    }
}

// ---------- DISPLAY RECIPE DETAILS ----------
$recipeID = $_GET['id'] ?? null;

if ($recipeID) {
    $sql = "
        SELECT 
            FOOD.FoodTitle, FOOD.FoodCategory, FOOD.FoodDesc, FOOD.FoodType, FOOD.FoodImage,
            RECIPE.RecInstructions, RECIPE.RecIngredients, RECIPE.RecLevel, RECIPE.CookVideo,
            USER.FullName
        FROM RECIPE
        JOIN FOOD ON RECIPE.FoodID = FOOD.FoodID
        JOIN USER ON RECIPE.UserID = USER.UserID
        WHERE RECIPE.RecipeID = ?
        LIMIT 1
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $recipeID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $data = $result->fetch_assoc();
    } else {
        echo "<p>Resepi tidak dijumpai.</p>";
        include 'footer.php';
        exit;
    }
} else {
    echo "<p>Tiada ID disediakan.</p>";
    include 'footer.php';
    exit;
}
?>

<main>
  <h1><?= htmlspecialchars($data['FoodTitle']) ?></h1>

  <?php if (!empty($data['FoodImage'])): ?>
    <img src="data:image/jpeg;base64,<?= base64_encode($data['FoodImage']) ?>" alt="<?= htmlspecialchars($data['FoodTitle']) ?>" class="recipe-img">
  <?php endif; ?>

  <div class="section"><h3>Kategori</h3><p><?= htmlspecialchars($data['FoodCategory']) ?></p></div>
  <div class="section"><h3>Jenis Hidangan</h3><p><?= htmlspecialchars($data['FoodType']) ?></p></div>
  <div class="section"><h3>Deskripsi Ringkas</h3><p><?= htmlspecialchars($data['FoodDesc']) ?></p></div>

  <div class="section">
    <h3>Langkah Penyediaan</h3>
    <ol>
      <?php foreach (explode("\n", $data['RecInstructions']) as $step) {
          if (trim($step)) echo "<li>" . htmlspecialchars($step) . "</li>";
      } ?>
    </ol>
  </div>

  <div class="section">
    <h3>Bahan-bahan</h3>
    <ul>
      <?php foreach (explode("\n", $data['RecIngredients']) as $item) {
          if (trim($item)) echo "<li>" . htmlspecialchars($item) . "</li>";
      } ?>
    </ul>
  </div>

  <?php if (!empty($data['CookVideo'])): ?>
  <div class="section">
    <h3>Video Penyediaan</h3>
    <div class="video-container">
      <iframe src="<?= htmlspecialchars($data['CookVideo']) ?>" frameborder="0" allowfullscreen></iframe>
    </div>
  </div>
  <?php endif; ?>

  <div class="section">
    <h3>Tahap Kesukaran</h3>
    <p><?= htmlspecialchars($data['RecLevel']) ?></p>
  </div>

  <div class="section">
    <h3>Nama Pengguna Yang Kongsi Resepi</h3>
    <p><strong><?= htmlspecialchars($data['FullName']) ?></strong></p>
  </div>

  <!-- Feedback Section -->
  <div class="feedback-section" style="margin-top: 30px;">
    <h3>Komen / Maklum Balas</h3>

    <?php if (isset($_SESSION['UserID'])): ?>
    <form method="POST" action="" enctype="multipart/form-data" class="comment-form" style="margin-bottom: 30px;">
      <textarea name="comment" rows="4" placeholder="Tulis komen anda di sini..." required style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #ccc;"></textarea>
      <input type="hidden" name="recipeID" value="<?= htmlspecialchars($_GET['id']) ?>" />
      
      <div style="margin-top: 10px;">
        <label>Tambah Gambar:</label><br>
        <input type="file" name="image" accept="image/*">
      </div>

      <div style="margin-top: 10px;">
        <label>Tambah Video:</label><br>
        <input type="file" name="video" accept="video/*">
      </div>

      <button type="submit" style="margin-top: 15px; padding: 10px 20px; background-color: #0066cc; color: white; border: none; border-radius: 5px;">Hantar Komen</button>
    </form>
    <?php else: ?>
      <p><strong>Sila log masuk untuk memberi komen.</strong></p>
    <?php endif; ?>

    <div class="comment-list">
      <h4>Senarai Komen</h4>
      <?php
      $feedbackQuery = $conn->prepare("
        SELECT F.Comment, F.ComDateTime, F.CommentType, F.VideoAttachment, F.ImageAttachment, U.FullName
        FROM FEEDBACK F
        JOIN USER U ON F.UserID = U.UserID
        WHERE F.RecipeID = ?
        ORDER BY F.ComDateTime DESC
      ");
      $feedbackQuery->bind_param("s", $recipeID);
      $feedbackQuery->execute();
      $feedbackResult = $feedbackQuery->get_result();

      if ($feedbackResult && $feedbackResult->num_rows > 0) {
          while ($fb = $feedbackResult->fetch_assoc()) {
              echo "<div class='comment'>";
              echo "<p><strong>" . htmlspecialchars($fb['FullName']) . "</strong> <small>(" . date('d-m-Y H:i', strtotime($fb['ComDateTime'])) . ")</small></p>";
              echo "<p>" . nl2br(htmlspecialchars($fb['Comment'])) . "</p>";

              if ($fb['ImageAttachment']) {
                  $img = base64_encode($fb['ImageAttachment']);
                  echo "<img src='data:image/jpeg;base64,$img' alt='Comment Image' style='max-width:250px; display:block; margin-top:10px; border-radius:5px;' />";
              }

              if ($fb['VideoAttachment']) {
                  echo "<video controls style='max-width:300px; display:block; margin-top:10px;'>
                          <source src='" . htmlspecialchars($fb['VideoAttachment']) . "' type='video/mp4'>
                          Video tidak disokong pada pelayar anda.
                        </video>";
              }

              echo "</div>";
          }
      } else {
          echo "<p>Tiada komen lagi. Jadilah yang pertama!</p>";
      }
      ?>
    </div>
  </div>
</main>

<?php include 'footer.php'; ?>

<style>
.comment {
  background-color: #f4f4f4;
  border-left: 4px solid #0077cc;
  padding: 15px;
  margin-bottom: 20px;
  border-radius: 6px;
}
.comment-list h4 {
  margin-bottom: 15px;
}
</style>
