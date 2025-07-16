<?php
session_start();
include 'connection.php'; // make sure this file defines $conn
include 'headerlogin.php'; // Include the header for login page
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($_POST["username"]) && !empty($_POST["password"])) {
        $username = $_POST["username"];
        $password = md5($_POST["password"]); // MD5 password hashing

        $stmt = $conn->prepare("SELECT UserID, Username FROM user WHERE username = ? AND password = ?");
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();

            // Store both username and user ID in session
            $_SESSION["username"] = $row["Username"];
            $_SESSION["UserID"] = $row["UserID"];

            $stmt->close();
            $conn->close();

            header("Location: homepage.php");
            exit();
        } else {
            $stmt->close();
            $conn->close();
            echo "<script>alert('Nama pengguna atau kata laluan salah.'); window.location.href='login.html';</script>";
            exit();
        }
    } else {
        echo "<script>alert('Sila isi nama pengguna dan kata laluan.'); window.location.href='login.html';</script>";
        exit();
    }
} else {
    header("Location: login.html");
    exit();
}
?>
