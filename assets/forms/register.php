<?php
session_start();
require 'db.php';

// Import PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require __DIR__ . '/../vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $clgname  = trim($_POST['clgname'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $cpass    = $_POST['confirm_password'] ?? '';

    // ✅ Basic required field check
    if (empty($username) || empty($phone) || empty($clgname) || empty($email) || empty($password) || empty($cpass)) {
        echo "<script>alert('All fields are required');window.history.back();</script>";
        exit();
    }

    // ✅ Email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Invalid email format');window.history.back();</script>";
        exit();
    }

    // ✅ Phone validation (10 digits for India, change as needed)
    if (!preg_match('/^[0-9]{10}$/', $phone)) {
        echo "<script>alert('Invalid phone number');window.history.back();</script>";
        exit();
    }

    // ✅ Password length check
    if (strlen($password) < 6) {
        echo "<script>alert('Password must be at least 6 characters long');window.history.back();</script>";
        exit();
    }

    // ✅ Confirm password check
    if ($password !== $cpass) {
        echo "<script>alert('Passwords do not match');window.history.back();</script>";
        exit();
    }

    if ($password !== $cpass) {
        echo "<script>alert('Passwords do not match');window.history.back();</script>";
        exit();
    }

    $check = $conn->prepare("SELECT id FROM users WHERE email=? LIMIT 1");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        echo "<script>alert('Email already registered! Please login or use a different email.');window.history.back();</script>";
        exit();
    }
    $check->close();

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Generate OTP
    $otp = rand(100000, 999999);

    // Insert user with status=0 (unverified)
    $sql = "INSERT INTO users (username, number, clgname, email, password, otp, status) 
            VALUES (?, ?, ?, ?, ?, ?, 0)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", $username, $phone, $clgname, $email, $hashedPassword, $otp);

    if ($stmt->execute()) {
        // ✅ Store email + username in session
        $_SESSION['email'] = $email;
        $_SESSION['username'] = $username;

        // Send OTP email
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = "smtp.gmail.com"; // Hostinger SMTP
            $mail->SMTPAuth   = true;
            $mail->Username   = "aavirbhav2025@gmail.com"; // your webmail
            $mail->Password   = "zeza ifuy joqk wlbu";             // your webmail password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom("aavirbhav2025@gmail.com", "Aavirbhav Event");
            $mail->addAddress($email, $username);

            $mail->isHTML(true);
            $mail->Subject = "Verify Your Email - OTP Code";
            $mail->Body = '
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>OTP Verification</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Cinzel, serif;
            background-color: #1a1a1a;
            color: #fff;
        }

        .card {
            max-width: 600px;
            margin: 30px auto;
            background-color: #222;
            border: 2px solid #D4AF37;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.7);
            padding: 30px;
            text-align: center;
        }

        .logo-wrapper {
            display: inline-block;
        }

        .logo {
            max-width: 120px;
            width: 100%;
            height: auto;
            border-radius: 50%;
            /* makes logo circular */
            display: block;
        }

        h2 {
            color: #D4AF37;
            margin: 15px 0;
            font-size: 24px;
        }

        p {
            color: #ccc;
            font-size: 16px;
            margin: 10px 0;
        }

        .otp {
            display: inline-block;
            font-size: 32px;
            font-weight: bold;
            color: #D4AF37;
            letter-spacing: 4px;
            margin: 20px 0;
        }

        .footer {
            font-size: 12px;
            color: #aaa;
            margin-top: 20px;
            border-top: 1px solid #444;
            padding-top: 10px;
        }
    </style>
</head>

<body>
    <div class="card">
        <div class="logo-wrapper">
            <img src="https://aavirbhav.tech/assets/images/logo.png" alt="Aavirbhav Logo" class="logo">
        </div>
        <h2>Aavirbhav 2025</h2>
        <h2>Verify Your Email</h2>
        <p>Hello <strong>' . htmlspecialchars($username) . '</strong>,</p>
        <p>Your OTP code is:</p>
        <div class="otp">' . htmlspecialchars($otp) . '</div>
        <p>Enter this OTP on the verification page to activate your account. It will expire in 10 minutes.</p>
        <div class="footer">&copy; ' . date('Y') . ' Aavirbhav. All rights reserved.</div>
    </div>
</body>

</html>
';

            $mail->send();
        } catch (Exception $e) {
            error_log("Mailer Error: " . $mail->ErrorInfo);
        }

        // Redirect to verification page
        header("Location: verify.php");
        exit();
    } else {
        echo "<script>alert('Registration failed, please try again');window.history.back();</script>";
    }

    $stmt->close();
    $conn->close();
}
?>
