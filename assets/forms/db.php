<?php
// Detect if running locally or live
if ($_SERVER['HTTP_HOST'] === 'localhost') {
    // Localhost (XAMPP/WAMP)
    $servername = "localhost";
    $username   = "root"; // default in XAMPP
    $password   = "";     // default password is empty
    $dbname     = "aavirbhav"; // your local database
} else {
    // Live server credentials
    $servername = "localhost";
    $username   = "u223469901_aavirbhav";
    $password   = "Ox^j[oix*5";
    $dbname     = "u223469901_aavirbhav";
}

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
