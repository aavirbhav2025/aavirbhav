<?php
include 'db.php'; 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $phone = $_POST['phone'];
    $clgname = $_POST['clgname'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Password check
    if ($password !== $confirm_password) {
        echo "<script>alert(' Passwords do not match'); window.location.href='form.html';</script>";
        exit();
    }

    $check = $conn->prepare("SELECT email FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();
    if ($check->num_rows > 0) {
        echo "<script>alert(' Email already registered'); window.location.href='form.html';</script>";
        exit();
    }
    $check->close();

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $otp = rand(100000, 999999);

    // Insert into database
    $sql = "INSERT INTO users (username, number, clgname, email, password, otp, status) 
            VALUES (?, ?, ?, ?, ?, ?, 0)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", $username, $phone, $clgname, $email, $hashedPassword, $otp);

    if ($stmt->execute()) {
        $_SESSION['email'] = $email;

        // Send OTP email
         try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = "smtp.hostinger.com";   // ✅ Use your domain mail server
        $mail->SMTPAuth   = true;
        $mail->Username   = "contact@aavirbhav.tech"; // your Webmail
        $mail->Password   = "T;h^o!oNb4";             // your Webmail password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // or SMTPS if port 465
        $mail->Port       = 465;

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

        // Redirect to verify page
        header("Location: verify.php");
        exit();
    } else {
        echo "<script>alert('Registration failed, please try again');window.history.back();</script>";
    }

    $stmt->close();
    $conn->close();
}
?>