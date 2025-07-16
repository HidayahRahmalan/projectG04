<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>ResepiKu - Home</title>
  <link rel="stylesheet" href="styles.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
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

    main {
      flex: 1;
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

    nav a, nav .dropdown {
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
      box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
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

    .filter-bar {
      margin: 20px auto;
      text-align: center;
    }

    .filter-bar select {
      padding: 10px 15px;
      border-radius: 5px;
      border: 1px solid #ccc;
      font-size: 16px;
      margin: 0 10px 10px 10px;
    }

    .search-bar {
      text-align: center;
      margin-bottom: 20px;
    }

    .search-bar input[type="text"] {
      width: 60%;
      padding: 10px;
      font-size: 16px;
      border-radius: 5px;
      border: 1px solid #ccc;
    }

    .recipe-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 20px;
      padding: 20px;
    }

    .recipe-card {
      background-color: white;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      cursor: pointer;
      transition: transform 0.2s;
    }

    .recipe-card:hover {
      transform: scale(1.02);
    }

    .recipe-card img {
      width: 100%;
      height: 180px;
      object-fit: cover;
    }

    .recipe-card .card-content {
      padding: 15px;
    }

    footer {
      background-color: #333;
      color: white;
      text-align: center;
      padding: 15px 0;
    }

    #notifCount {
      background: red;
      color: white;
      border-radius: 50%;
      padding: 2px 6px;
      font-size: 12px;
      position: absolute;
      top: -8px;
      right: -12px;
    }
  </style>
</head>
<body>
  <header>
    <a href="homepage.html" style="text-decoration: none; color: white;">
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
      <a href="#" onclick="requireLogin('notification')" style="position: relative;">
        Notifikasi <span id="notifCount">0</span>
      </a>
      <a href="login.php">Log Masuk</a>
      <a href="register.php">Daftar</a>
      <a href="#" onclick="requireLogin('share')">Kongsi Resepi</a>
    </nav>
  </header>