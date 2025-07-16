<?php
 $host="localhost";
 $user="resepiku";
 $pass="123456"; 
 $dbname="p25_resepiku";

 $conn = new mysqli($host, $user, $pass, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}   

?>
