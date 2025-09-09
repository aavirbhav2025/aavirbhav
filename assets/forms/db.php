<?php
// Database configuration
$servername = "localhost";  // Change if not running locally
$username   = "u223469901_aavirbhav";       // Your MySQL username
$password   = "Ox^j[oix*5";           // Your MySQL password
$dbname     = "u223469901_aavirbhav";    // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
