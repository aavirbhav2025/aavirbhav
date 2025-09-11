<?php
session_start();
require 'db.php';

// Import PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require __DIR__ . '/../vendor/autoload.php';

$email = $_SESSION['email'] ?? '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Handle OTP verification
    if (isset($_POST['verify'])) {
        $otp = $_POST['otp'] ?? '';

        if (!$email) {
            echo "<script>alert('Session expired, please register again');window.location='form.html';</script>";
            exit();
        }

        $sql = "SELECT * FROM users WHERE email=? AND otp=? AND status=0";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $email, $otp);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Mark as verified
            $update = $conn->prepare("UPDATE users SET status=1, otp=NULL WHERE email=?");
            $update->bind_param("s", $email);
            $update->execute();

            unset($_SESSION['email']); // clear session

            echo "<script>alert('Email verified successfully! You can now login.');window.location='form.html';</script>";
            exit();
        } else {
            echo "<script>alert('Invalid OTP, please try again');window.history.back();</script>";
            exit();
        }
    }

    // Handle Resend OTP
    if (isset($_POST['resend'])) {
        if (!$email) {
            echo "<script>alert('Session expired, please register again');window.location='form.html';</script>";
            exit();
        }

        $otp = rand(100000, 999999);

        // Update DB with new OTP
        $update = $conn->prepare("UPDATE users SET otp=? WHERE email=? AND status=0");
        $update->bind_param("ss", $otp, $email);
        $update->execute();

        // Send email
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
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Email Verification</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css">
</head>
<body class="d-flex justify-content-center align-items-center vh-100">
  <div class="card p-4 shadow-lg" style="max-width:400px;width:100%;">
    <h4 class="mb-3 text-center">Email Verification</h4>
    <form method="POST">
      <div class="mb-3">
        <label for="otp" class="form-label">Enter OTP</label>
        <input type="text" class="form-control" id="otp" name="otp" placeholder="6-digit OTP" required>
      </div>
      <div class="d-flex gap-2">
        <button type="submit" name="verify" class="btn btn-success flex-fill">Verify</button>
        <button type="submit" name="resend" class="btn btn-secondary flex-fill">Resend OTP</button>
      </div>
    </form>
  </div>
</body>
</html>
