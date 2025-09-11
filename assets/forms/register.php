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

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Generate OTP
    $otp = rand(100000, 999999);

    // Insert user with status=0 (unverified)
    $sql = "INSERT INTO users (username, phone, clgname, email, password, otp, status) 
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
            $mail->Body    = "
                <h2>Hello, {$username}!</h2>
                <p>Thanks for registering for our event.</p>
                <p>Your OTP code is: <b>{$otp}</b></p>
                <p>Please enter this OTP on the verification page to activate your account.</p>
            ";

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
