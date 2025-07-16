<?php
session_start();

if (isset($_SESSION['UserName'])) { 
    $_SESSION = array(); 
    session_destroy();
    echo "<script>
          localStorage.removeItem('scrollPosition');
          sessionStorage.setItem('message', 'You have successfully logged out.');
          window.location.href = 'landingpage.php'; 
          </script>";
} else {
    echo "<script>
          sessionStorage.setItem('message', 'Please log-in.');
          window.location.href = 'landingpage.php';
          </script>";
}
exit();
?>