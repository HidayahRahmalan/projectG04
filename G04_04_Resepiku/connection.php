<?php
$servername = "localhost";
$username = "root";//default untuk XAMPP
$password = "";           // kosongkan untuk default XAMPP
$database = "mmdb"; // pastikan DB ini telah di-import ke localhost

$conn = new mysqli($servername, $username, $password, $database);

// Semak sambungan
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
