<?php
include 'connection.php';
include 'headerdetail.php';
session_start();

// ---------- HANDLE COMMENT SUBMISSION ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment']) && isset($_POST['recipeID'])) {
    $comment = $_POST['comment'];
    $recipeID = $_POST['recipeID'];
    $userID = $_SESSION['UserID'] ?? null;

    if (!$userID) {
        echo json_encode(["status" => "error", "message" => "Sesi pengguna tidak dijumpai."]);
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
        $videoName = basename($_FILES['video']['name']);
        $targetVideo = "uploads/" . $videoName;
        move_uploaded_file($_FILES['video']['tmp_name'], $targetVideo);
        $videoPath = $targetVideo;
        $commentType .= 'Video ';
    }

    if ($commentType === '') $commentType = 'Text';

    $stmt = $conn->prepare("INSERT INTO FEEDBACK (Comment, VideoAttachment, ImageAttachment, UserID, RecipeID) VALUES (  ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $newID, $commentType, $comment, $videoPath, $imageData, $userID, $recipeID);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success"]);
        exit;
    } else {
        echo json_encode(["status" => "error", "message" => $stmt->error]);
        exit;
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

  <div class="feedback-section">
    <h3>Komen / Maklum Balas</h3>
    <form class="feedback-form" id="feedbackForm" enctype="multipart/form-data">
      <textarea id="commentText" name="comment" rows="4" placeholder="Tulis komen anda di sini..." required></textarea>
      <label>Tambah Gambar:</label>
      <input type="file" id="commentImage" name="image" accept="image/*" />
      <label>Tambah Video:</label>
      <input type="file" id="commentVideo" name="video" accept="video/*" />
      <br />
      <button type="submit">Hantar Komen</button>
    </form>

    <div class="comment-list" id="commentList">
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
              echo "<p><strong>" . htmlspecialchars($fb['FullName']) . "</strong> (" . $fb['ComDateTime'] . ")</p>";
              echo "<p>" . nl2br(htmlspecialchars($fb['Comment'])) . "</p>";

              if ($fb['ImageAttachment']) {
                  $img = base64_encode($fb['ImageAttachment']);
                  echo "<img src='data:image/jpeg;base64,$img' style='max-width:200px; display:block; margin-top:5px;' />";
              }

              if ($fb['VideoAttachment']) {
                  echo "<video controls style='max-width:300px; margin-top:5px;'>
                          <source src='" . htmlspecialchars($fb['VideoAttachment']) . "' type='video/mp4'>
                          Your browser does not support the video tag.
                        </video>";
              }

              echo "<hr></div>";
          }
      } else {
          echo "<p>Tiada komen lagi.</p>";
      }
      ?>
    </div>
  </div>
</main>

<?php include 'footer.php'; ?>

<script>
document.getElementById("feedbackForm").addEventListener("submit", async function (event) {
  event.preventDefault();

  const commentText = document.getElementById("commentText").value;
  const image = document.getElementById("commentImage").files[0];
  const video = document.getElementById("commentVideo").files[0];
  const recipeID = new URLSearchParams(window.location.search).get("id");

  const formData = new FormData();
  formData.append("comment", commentText);
  formData.append("recipeID", recipeID);
  if (image) formData.append("image", image);
  if (video) formData.append("video", video);

  try {
    const response = await fetch(window.location.href, {
      method: "POST",
      body: formData
    });

    const result = await response.json();

    if (result.status === "success") {
      alert("Maklum balas berjaya dihantar!");
      location.reload();
    } else {
      alert("Ralat: " + result.message);
    }
  } catch (error) {
    alert("Ralat semasa menghantar maklum balas.");
    console.error(error);
  }
});
</script>

<style>
.comment {
  background: #f9f9f9;
  padding: 10px;
  margin-bottom: 15px;
  border-radius: 8px;
}
</style>
