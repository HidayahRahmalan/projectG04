<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>ResepiKu - Log Masuk</title>
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
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 40px 20px;
    }

    .login-container {
      background: white;
      padding: 30px 40px;
      border-radius: 10px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
      width: 100%;
      max-width: 400px;
    }

    h2 {
      text-align: center;
      color: #ff6347;
    }

    label {
      font-weight: bold;
      display: block;
      margin-top: 15px;
    }

    input[type="text"],
    input[type="password"],
    input[type="email"] {
      width: 100%;
      padding: 10px;
      margin-top: 5px;
      border: 1px solid #ccc;
      border-radius: 5px;
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

    .forgot-link {
      text-align: center;
      margin-top: 10px;
    }

    .forgot-link a {
      color: #ff6347;
      text-decoration: none;
      font-size: 14px;
    }

    .reset-password {
      display: none;
      margin-top: 20px;
      border-top: 1px solid #eee;
      padding-top: 20px;
    }

     /* Notification badge styling */
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

    footer {
      background-color: #333;
      color: white;
      text-align: center;
      padding: 15px 0;
    }
  </style>
</head>

<body>
  <header>
    <a href="homepage.php" style="text-decoration: none; color: white;">
    <h1>ResepiKu</h1>
    </a>
    <nav>
      <a href="register.php">Daftar</a>
    </nav>
  </header>