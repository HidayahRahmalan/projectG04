<?php
$host = 'localhost';
$db = 'p25_cooking_app'; // your DB name
$user = 'cooking_app';   // your username
$pass = 'cooking@app24'; // your password

try {
    $conn = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    //echo "✅ Connection successful!";
} catch (PDOException $e) {
    die("❌ Connection failed: " . $e->getMessage());
}
?>
