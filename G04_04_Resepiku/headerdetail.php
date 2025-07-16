<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>ResepiKu</title>
  <link rel="stylesheet" href="styles.css" />
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet" />
  <style>
    body {
      font-family: 'Roboto', sans-serif;
      margin: 0;
      padding: 0;
      background-color: #fff8f0;
    }

    header {
      background-color: #ff6347;
      color: white;
      padding: 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .logo {
      font-size: 28px;
      font-weight: bold;
      text-decoration: none;
      color: white;
    }

    nav {
      display: flex;
      gap: 15px;
    }

    nav a {
      color: white;
      text-decoration: none;
      font-weight: bold;
    }

    main {
      max-width: 900px;
      margin: 40px auto;
      padding: 20px;
      background-color: white;
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    h1 {
      color: #ff6347;
    }

    .recipe-img {
      width: 100%;
      max-height: 400px;
      object-fit: cover;
      border-radius: 10px;
      margin-bottom: 20px;
    }

    .section {
      margin-bottom: 25px;
    }

    .section h3 {
      color: #333;
      margin-bottom: 10px;
      border-left: 4px solid #ff6347;
      padding-left: 10px;
    }

    ul,
    ol {
      padding-left: 20px;
    }

    .video-container {
      position: relative;
      padding-bottom: 56.25%;
      height: 0;
      overflow: hidden;
      border-radius: 10px;
    }

    .video-container iframe {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
    }

    footer {
      background-color: #333;
      color: white;
      text-align: center;
      padding: 15px 0;
      margin-top: 40px;
    }

    .feedback-section {
      border-top: 2px solid #eee;
      padding-top: 30px;
    }

    .feedback-form textarea {
      width: 100%;
      padding: 10px;
      margin-bottom: 10px;
      resize: vertical;
      border-radius: 5px;
      border: 1px solid #ccc;
      font-size: 14px;
    }

    .feedback-form input[type="file"] {
      display: block;
      margin-bottom: 15px;
    }

    .feedback-form button {
      background-color: #ff6347;
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 5px;
      cursor: pointer;
      font-weight: bold;
    }

    .comment-list {
      margin-top: 30px;
    }

    .comment {
      border-top: 1px solid #eee;
      padding-top: 15px;
      margin-top: 15px;
    }

    .comment img,
    .comment video {
      max-width: 100%;
      display: block;
      margin-top: 10px;
      border-radius: 8px;
    }
  </style>
</head>
<body>
  <header>
    <a href="homepage.php" class="logo">ResepiKu</a>
    <nav>
      <a href="homepage.php">Laman Utama</a>
      <a href="notifications.php">Notifikasi</a>
      <a href="upload.php">Kongsi Resepi</a>
      <a href="logout.php" onclick="return confirm('Anda pasti ingin log keluar?')">Log Keluar</a>
    </nav>
  </header>