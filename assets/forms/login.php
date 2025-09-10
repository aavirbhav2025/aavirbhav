<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Fetch all required details
    $stmt = $conn->prepare("SELECT username, number, clgname, email, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Bind variables in the same order as SELECT
        $stmt->bind_result($username, $phone, $clgname, $user_email, $hashed_password);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            // Set session variables (no password stored)
            $_SESSION['username'] = $username;
            $_SESSION['phone'] = $phone;
            $_SESSION['clgname'] = $clgname;
            $_SESSION['email'] = $user_email;

            echo "<script>alert('Login Successful'); window.location.href='../events/team.php';</script>";
            exit();
        } else {
            echo "<script>alert('Invalid Password'); window.location.href='form.html';</script>";
        }
    } else {
        echo "<script>alert('No User Found, Register Now'); window.location.href='form.html';</script>";
    }

    $stmt->close();
}
?>
