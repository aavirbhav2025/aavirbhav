<?php
session_start();
require 'db.php';

// Import PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require __DIR__ . '/../vendor/autoload.php';

$email    = $_SESSION['email'] ?? '';
$username = $_SESSION['username'] ?? 'User';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Handle OTP verification
    if (isset($_POST['verify'])) {
    $otpArray = $_POST['otp'] ?? [];
    $otp = implode('', $otpArray); // combine 6 digits

    $sql = "SELECT * FROM users WHERE email=? AND otp=? AND status=0";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $otp);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Correct OTP: mark verified
        $update = $conn->prepare("UPDATE users SET status=1, otp=NULL WHERE email=?");
        $update->bind_param("s", $email);
        $update->execute();

        unset($_SESSION['email'], $_SESSION['username']);

        echo "<script>alert('Email verified successfully!');window.location='form.html';</script>";
        exit();
    } else {
        // Wrong OTP: redirect with flag to trigger shake
        header("Location: verify.php?wrong=1");
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
            $mail->Host       = "smtp.hostinger.com"; 
            $mail->SMTPAuth   = true;
            $mail->Username   = "contact@aavirbhav.tech"; 
            $mail->Password   = "T;h^o!oNb4";   // your Webmail password
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
    <h2>Aavirbhav - Verify Your Email</h2>
    <p>Hello <strong>' . htmlspecialchars($username) . '</strong>,</p>
    <p>Your OTP code is:</p>
    <div class="otp">' . htmlspecialchars($otp) . '</div>
    <p>Enter this OTP on the verification page to activate your account. It will expire in 10 minutes.</p>
    <div class="footer">&copy; ' . date('Y') . ' ©Aavirbhav All rights reserved.</div>
  </div>
</body>
</html>
';
            $mail->send();
            echo "<script>alert('A new OTP has been sent to your email.');</script>";
        } catch (Exception $e) {
            error_log("Mailer Error: " . $mail->ErrorInfo);
            echo "<script>alert('Failed to resend OTP. Please try again.');</script>";
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
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="../images/favicon.png">
    <link rel="apple-touch-icon" href="../images/favicon.png">
    <style>
        /* Body and background */
        body {
            background: url('../images/bgimg.jpg') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            overflow: hidden;
        }

        /* Main Card */
        .card {
            background: rgba(77, 75, 75, 0.5);
            border: 2px solid #D4AF37;
            border-radius: 20px;
            backdrop-filter: blur(8px);
            box-shadow: 0 0 20px rgba(245, 180, 82, 0.7);
            animation: fadeInUp 1s ease-in-out;
            width: 100%;
            max-width: 420px;
            padding: 2rem;
            text-align: center;
            color: #fff;
        }

        /* Heading */
        h4 {
            font-family: 'Cinzel', serif;
            font-weight: 700;
            color: #D4AF37;
            font-size: 1.8rem;
            margin-bottom: 0.3rem;
        }

        p {
            color: #ccc;
        }

        /* Buttons */
        .btn {
            border-radius: 30px;
            font-weight: bold;
            transition: all 0.3s;
            font-size: 1rem;
            padding: 0.5rem 0;
        }

        .btn-success {
            background: linear-gradient(145deg, #b06f12, #d4af37);
            border: 1px solid #D4AF37;
            color: #111;
        }

        .btn-success:hover:not(:disabled) {
            background: linear-gradient(145deg, #d4af37, #b06f12);
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(212, 175, 55, 0.5);
        }

        .btn-outline-primary {
            border: 1px solid #5DADE2;
            color: #5DADE2;
            background: transparent;
        }

        .btn-outline-primary:hover:not(:disabled) {
            background: #5DADE2;
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(93, 173, 226, 0.5);
        }

        /* OTP Inputs */
        .otp-inputs {
            display: flex;
            gap: 12px;
            justify-content: center;
            margin-top: 15px;
        }

        .otp-inputs input {
            width: 50px;
            height: 60px;
            text-align: center;
            font-size: 1.8rem;
            border-radius: 8px;
            border: 2px solid #D4AF37;
            outline: none;
            background: rgba(255, 255, 255, 0.05);
            color: #D4AF37;
            font-weight: bold;
            transition: all 0.3s;
        }

        .otp-inputs input:focus {
            border-color: #FF5733;
            background: rgba(255, 87, 51, 0.2);
            box-shadow: 0 0 12px rgba(255, 87, 51, 0.6);
            color: #fff;
        }

        /* Timer text */
        #timerText {
            color: #ccc;
            margin-top: 10px;
            font-size: 0.85rem;
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Shake animation for wrong OTP */
        .shake {
            animation: shake 0.5s;
        }

        @keyframes shake {
            0% {
                transform: translateX(0);
            }

            25% {
                transform: translateX(-8px);
            }

            50% {
                transform: translateX(8px);
            }

            75% {
                transform: translateX(-8px);
            }

            100% {
                transform: translateX(0);
            }
        }

        /* Responsive */
       @media (max-width: 780px) {
    .card {
        width: 90% !important;   /* take up most of the screen width */
        max-width: 400px;        /* but don’t stretch too much */
    }

    .otp-inputs input {
        width: 40px;
        height: 50px;
        font-size: 1.4rem;
    }

    h4 {
        font-size: 1.4rem;
    }

    .btn {
        font-size: 0.9rem;
        padding: 0.4rem 0;
    }
}


    </style>
</head>

<body>
    <div class="card shadow-lg" id="otpCard">
        <img src="../images/favicon.png" alt="logo" width="60" class="mb-2 d-block mx-auto">
        <h4>Email Verification</h4>
        <p>Enter the OTP sent to your registered email</p>

        <form method="POST" id="otpForm">
            <label class="form-label d-block mt-2">Enter OTP</label>
            <div class="otp-inputs">
                <input type="text" name="otp[]" maxlength="1" required>
                <input type="text" name="otp[]" maxlength="1" required>
                <input type="text" name="otp[]" maxlength="1" required>
                <input type="text" name="otp[]" maxlength="1" required>
                <input type="text" name="otp[]" maxlength="1" required>
                <input type="text" name="otp[]" maxlength="1" required>
            </div>

            <div class="d-flex gap-2 mt-3">
                <button type="submit" name="verify" class="btn btn-success flex-fill">Verify</button>
                <button type="submit" name="resend" id="resendBtn"
                    class="btn btn-outline-primary flex-fill">Resend</button>
            </div>
            <p class="text-center" id="timerText"></p>
        </form>
    </div>

    <script>
        // Countdown logic for Resend OTP button
const resendBtn = document.getElementById("resendBtn");
const timerText = document.getElementById("timerText");

function startCountdown(duration) {
    let remaining = duration;
    resendBtn.disabled = true;
    timerText.textContent = `Resend available in ${remaining}s`;

    const countdown = setInterval(() => {
        remaining--;
        if (remaining > 0) {
            timerText.textContent = `Resend available in ${remaining}s`;
        } else {
            clearInterval(countdown);
            resendBtn.disabled = false;
            timerText.textContent = "You can resend OTP now.";
        }
    }, 1000);
}

window.onload = () => startCountdown(30);

// Auto-focus next input
const otpInputs = document.querySelectorAll('.otp-inputs input');
otpInputs.forEach((input, index) => {
    input.addEventListener('input', () => {
        if (input.value.length === 1 && index < otpInputs.length - 1) {
            otpInputs[index + 1].focus();
        }
    });

    input.addEventListener('keydown', (e) => {
        if (e.key === "Backspace" && !input.value && index > 0) {
            otpInputs[index - 1].focus();
        }
    });
});

// Shake effect for wrong OTP (triggered by PHP via URL parameter)
window.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('wrong') === '1') {
        const card = document.getElementById('otpCard');
        card.classList.add('shake');
        setTimeout(() => card.classList.remove('shake'), 500);
    }
});

    </script>
</body>

</html>