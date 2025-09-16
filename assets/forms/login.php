<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // ===== Static admin credentials (quick & simple) =====
    // Replace these with your desired admin email/password.
    $ADMIN_EMAIL = 'aavirbhav2025';
    $ADMIN_PASSWORD = 'hakirianap123';

    if ($email === $ADMIN_EMAIL && $password === $ADMIN_PASSWORD) {
        // Admin login successful
        $_SESSION['username'] = 'admin';
        $_SESSION['email'] = $ADMIN_EMAIL;
        $_SESSION['is_admin'] = true;

        echo "<script>alert('Admin login successful'); window.location.href='../admin/admin.php';</script>";
        exit();
    }

    // ===== Normal user authentication (existing code) =====
    $stmt = $conn->prepare("SELECT username, number, clgname, email, password, status FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($username, $phone, $clgname, $user_email, $hashed_password, $status);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            if ($status == 1) {
                $_SESSION['username'] = $username;
                $_SESSION['phone'] = $phone;
                $_SESSION['clgname'] = $clgname;
                $_SESSION['email'] = $user_email;

                echo "<script>alert('Login Successful'); window.location.href='../events/team.php';</script>";
                exit();
            } else {
                $_SESSION['email']    = $user_email;
                $_SESSION['username'] = $username;
                echo "<script>alert('Please verify your email before login. Redirecting...'); window.location.href='verify.php';</script>";
                exit();
            }
        } else {
            echo "<script>alert('Invalid Password'); window.location.href='form.html';</script>";
        }
    } else {
        echo "<script>alert('No User Found, Register Now'); window.location.href='form.html';</script>";
    }

    $stmt->close();
}
?>
