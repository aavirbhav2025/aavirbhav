<?php
session_start();
require 'db.php';

// Import PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require __DIR__ . '/../vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'] ?? '';
    $phone    = $_POST['phone'] ?? '';
    $clgname  = $_POST['clgname'] ?? '';
    $email    = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $cpass    = $_POST['confirm_password'] ?? '';

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
            $mail->Host       = "smtp.hostinger.com"; // Hostinger SMTP
            $mail->SMTPAuth   = true;
            $mail->Username   = "contact@aavirbhav.tech"; // your webmail
            $mail->Password   = "T;h^o!oNb4";             // your webmail password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom("contact@aavirbhav.tech", "Aavirbhav Event Registration");
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
    body { margin:0; padding:0; font-family: Cinzel, serif; background-color:#1a1a1a; color:#fff; }
    .card { 
      max-width:600px; 
      margin: 30px auto; 
      background-color:#222; 
      border:2px solid #D4AF37; 
      border-radius:15px; 
      box-shadow:0 0 20px rgba(0,0,0,0.7); 
      padding:30px; 
      text-align:center;
    }
    h2 { color:#D4AF37; margin:0 0 20px; font-size:24px; }
    p { color:#ccc; font-size:16px; margin:10px 0; }
    .otp { 
      display:inline-block; 
      font-size:32px; 
      font-weight:bold; 
      color:#D4AF37; 
      letter-spacing:4px; 
      margin:20px 0; 
    }
    .footer { 
      font-size:12px; 
      color:#aaa; 
      margin-top:20px; 
      border-top:1px solid #444; 
      padding-top:10px;
    }
  </style>
</head>
<body>
  <div class="card">
    <h2>Aavirbhav 2025</h2>
    <h2>Verify Your Email</h2>
    <p>Hello <strong>' . htmlspecialchars($username) . '</strong>,</p>
    <p>Your OTP code is:</p>
    <div class="otp">' . htmlspecialchars($otp) . '</div>
    <p>Enter this OTP on the verification page to activate your account. It will expire in 10 minutes.</p>
    <div class="footer">&copy; ' . date('Y') . 'Aavirbhav All rights reserved.</div>
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
