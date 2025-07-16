<?php
session_start();
include 'connection.php';
include 'header.php';

// Optional: Only allow logged-in users
if (!isset($_SESSION['UserID'])) {
    echo "<script>alert('Sila log masuk untuk melihat notifikasi.'); window.location.href='index.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Notifikasi Maklum Balas</title>
  <link rel="stylesheet" href="styles.css">
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #fff8f0;
      margin: 0;
      padding: 0;
    }

    .container {
      max-width: 800px;
      margin: 30px auto;
      padding: 20px;
      background: white;
      border-radius: 10px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }

    h2 {
      text-align: center;
      color: #ff6347;
    }

    .notification {
      border-bottom: 1px solid #ccc;
      padding: 15px 0;
    }

    .notification:last-child {
      border-bottom: none;
    }

    .notification p {
      margin: 5px 0;
    }

    .notification .user {
      font-weight: bold;
      color: #333;
    }

    .notification .time {
      color: #888;
      font-size: 0.9em;
    }
  </style>
</head>
<body>

<div class="container">
  <h2>Senarai Notifikasi Maklum Balas</h2>

  <?php
  $query = "
    SELECT F.Comment, F.ComDateTime, U.FullName
    FROM FEEDBACK F
    JOIN USER U ON F.UserID = U.UserID
    ORDER BY F.ComDateTime DESC
  ";

  $result = $conn->query($query);

  if ($result && $result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
          echo "<div class='notification'>";
          echo "<p class='user'>" . htmlspecialchars($row['FullName']) . " memberi komen:</p>";
          echo "<p>" . nl2br(htmlspecialchars($row['Comment'])) . "</p>";
          echo "<p class='time'>Pada: " . date("d M Y, h:i A", strtotime($row['ComDateTime'])) . "</p>";
          echo "</div>";
      }
  } else {
      echo "<p>Tiada notifikasi buat masa ini.</p>";
  }

  $conn->close();
  ?>
</div>

<?php include 'footer.php'; ?>
</body>
</html>
