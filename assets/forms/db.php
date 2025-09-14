<?php
$servername = "localhost";
$username   = "aavi_aavi_aavirbhav";
$password   = "YeT3!New85f7g7*^";
$dbname     = "aavi_aavi_aavirbhav";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
