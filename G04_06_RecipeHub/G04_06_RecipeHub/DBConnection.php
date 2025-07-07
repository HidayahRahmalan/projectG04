<?php
$host = 'localhost';
$db = 'p25_cooking';
$user = 'Group6';
$pass = 'cooking'; // change this if your MySQL has a password

try {
    $conn = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
