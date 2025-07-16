<?php
session_start();

include(__DIR__ . '/../connect.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  
    $userEmail = htmlspecialchars($_POST['Email']);
    $userPassword = htmlspecialchars($_POST['Password']);

    $query = "SELECT User_ID, User_Name, User_Email, User_Password, User_Role FROM users WHERE User_Email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $userEmail);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        if (password_verify($userPassword, $user['User_Password'])) {
            $_SESSION['UserID'] = $user['User_ID'];
            $_SESSION['UserName'] = $user['User_Name'];
            $_SESSION['UserEmail'] = $user['User_Email'];
            $_SESSION['UserRole'] = $user['User_Role']; 

   
           if ($user['User_Role'] === 'chef') {
    $_SESSION['welcome'] = "Welcome " . htmlspecialchars($_SESSION['UserName']) . "!";
    header("Location: /G04_01_Gastroverse/home.php");
    exit();
} elseif ($user['User_Role'] === 'student') {
    $_SESSION['welcome'] = "Welcome " . htmlspecialchars($_SESSION['UserName']) . "!";
    header("Location: /G04_01_Gastroverse/home.php");
    exit();
} elseif ($user['User_Role'] === 'admin') {
    $_SESSION['welcome'] = "Welcome " . htmlspecialchars($_SESSION['UserName']) . "!";
    header("Location: /G04_01_Gastroverse/admin.php");
    exit();
}
        } else {
            echo "<script>
            sessionStorage.setItem('message', 'Invalid password.');
            window.location.href = '/G04_01_Gastroverse/landingpage.php';
            </script>";
        }
    } else {
        echo "<script>
            sessionStorage.setItem('message', 'Invalid email address.');
            window.location.href = '/G04_01_Gastroverse/landingpage.php';
            </script>";
    }

    $stmt->close();
}

$conn->close();
?>