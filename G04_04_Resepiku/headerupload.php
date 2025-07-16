<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>ResepiKu - Kongsi Resepi</title>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Roboto', sans-serif;
      margin: 0;
      padding: 0;
      background-color: #fff8f0;
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

    nav {
      display: flex;
      gap: 15px;
      align-items: center;
    }

    nav a,
    nav .dropdown {
      color: white;
      text-decoration: none;
      font-weight: bold;
      position: relative;
    }

    .dropdown {
      cursor: pointer;
    }

    .dropdown-content {
      display: none;
      position: absolute;
      background-color: #fff;
      color: black;
      min-width: 160px;
      box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
      z-index: 1;
      border-radius: 5px;
      margin-top: 5px;
    }

    .dropdown-content a {
      color: black;
      padding: 10px 15px;
      text-decoration: none;
      display: block;
    }

    .dropdown-content a:hover {
      background-color: #f1f1f1;
    }

    .dropdown:hover .dropdown-content {
      display: block;
    }

    main {
      flex: 1;
      padding: 40px 20px;
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    .form-container {
      background: white;
      padding: 30px 40px;
      border-radius: 10px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
      width: 100%;
      max-width: 700px;
      margin-bottom: 40px;
    }

    h2 {
      text-align: center;
      color: #ff6347;
      margin-bottom: 30px;
    }

    label {
      font-weight: bold;
      display: block;
      margin-top: 15px;
    }

    input[type="text"],
    input[type="file"],
    textarea,
    select {
      width: 100%;
      padding: 10px;
      margin-top: 5px;
      border: 1px solid #ccc;
      border-radius: 5px;
    }

    textarea {
      resize: vertical;
    }

    .checkbox-group {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      margin-top: 5px;
    }

    .checkbox-group label {
      font-weight: normal;
    }

    button {
      width: 100%;
      padding: 10px;
      background-color: #ff6347;
      color: white;
      border: none;
      border-radius: 5px;
      margin-top: 20px;
      cursor: pointer;
    }

    button:hover {
      background-color: #e5533d;
    }

    footer {
      background-color: #333;
      color: white;
      text-align: center;
      padding: 15px 0;
    }

    .recipe-list {
      width: 100%;
      max-width: 700px;
    }

    .recipe-item {
      background: #fff;
      border: 1px solid #ccc;
      padding: 20px;
      margin-bottom: 15px;
      border-radius: 8px;
    }

    .recipe-item h3 {
      margin: 0 0 10px;
      color: #ff6347;
    }

    .recipe-item button {
      width: auto;
      margin-right: 10px;
    }

    .recipe-media {
      margin-top: 10px;
    }

    .recipe-media img,
    .recipe-media video {
      max-width: 100%;
      border-radius: 8px;
      margin-top: 10px;
    }
  </style>
</head>

<body>
  <header>
    <a href="homepage.php" style="text-decoration: none; color: white;">
      <h1>ResepiKu</h1>
    </a>
    <nav>
      <div class="dropdown">
        <span>Jenis</span>
        <div class="dropdown-content">
          <a href="Local.php">Local Cuisine</a>
          <a href="western.php">Western Cuisine</a>
          <a href="japanese.php">Japanese Cuisine</a>
          <a href="chinese.php">Chinese Cuisine</a>
          <a href="korean.php">Korean Cuisine</a>
          <a href="dessert.php">Dessert</a>
          <a href="beverages.php">Beverages</a>
          <a href="other.php">Other</a>
        </div>
      </div>
      <a href="notification.php">Notifikasi</a>
      <a href="login.php">Log Masuk</a>
      <a href="register.php">Daftar</a>
      <a href="upload.php">Kongsi Resepi</a>
    </nav>
  </header>