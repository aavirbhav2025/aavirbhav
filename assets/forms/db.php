<?php
// Database configuration
$servername = "localhost";  // Change if not running locally
$username   = "root";       // Your MySQL username
$password   = "";           // Your MySQL password
$dbname     = "aavirbhav";    // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
