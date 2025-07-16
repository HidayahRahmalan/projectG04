<?php
// db connection (update with your actual DB credentials)
$host = "localhost";
$user = "root";
$password = "";
$dbname = "mmdb"; //e to your DB name

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT * FROM audit_log ORDER BY LogTime DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>ResepiKu - Admin Logs</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet" />
  <style>
    * {
      box-sizing: border-box;
    }

    html, body {
      font-family: 'Roboto', sans-serif;
      margin: 0;
      padding: 0;
      background-color: #fff8f0;
      height: 100%;
    }

    body {
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }

    header {
      background-color: #ff6347;
      color: white;
      padding: 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
    }

    header h1 {
      margin: 0;
    }

    main {
      flex: 1;
      padding: 30px;
    }

    .card {
      background-color: white;
      padding: 20px;
      border-radius: 12px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    h2 {
      margin-top: 0;
      color: #333;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }

    th, td {
      padding: 12px;
      border-bottom: 1px solid #ccc;
      text-align: left;
    }

    th {
      background-color: #f0f0f0;
      color: #444;
    }

    tr:hover {
      background-color: #fafafa;
    }

    footer {
      background-color: #333;
      color: white;
      text-align: center;
      padding: 15px 0;
    }

    @media (max-width: 768px) {
      th, td {
        font-size: 14px;
      }
    }
  </style>
</head>
<body>
  <header>
    <a href="homepage.html" style="text-decoration: none; color: white;">
      <h1>ResepiKu Admin Panel</h1>
    </a>
  </header>

  <main>
    <div class="card">
      <h2>Audit Logs</h2>

      <table>
        <thead>
          <tr>
            <th>Log ID</th>
            <th>Action</th>
            <th>Status</th>
            <th>IP Address</th>
            <th>Time</th>
            <th>User ID</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
              <tr>
                <td><?= htmlspecialchars($row['LogID']) ?></td>
                <td><?= htmlspecialchars($row['LogAction']) ?></td>
                <td><?= htmlspecialchars($row['LogStatus']) ?></td>
                <td><?= htmlspecialchars($row['LogIP']) ?></td>
                <td><?= htmlspecialchars($row['LogTime']) ?></td>
                <td><?= htmlspecialchars($row['UserID']) ?></td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="6" style="text-align:center;">No audit logs available.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </main>

  <footer>
    &copy; 2025 ResepiKu. All rights reserved.
  </footer>
</body>
</html>

<?php $conn->close(); ?>
