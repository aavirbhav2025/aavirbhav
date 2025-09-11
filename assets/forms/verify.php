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
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700&display=swap" rel="stylesheet">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    /* Background */
    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background: url('../images/bgimg.jpg') no-repeat center center fixed;
      background-size: cover;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
    }

    /* Card */
    .otp-card {
      background: rgba(77, 75, 75, 0.55);
      border: 2px solid #D4AF37;
      border-radius: 20px;
      padding: 2rem;
      text-align: center;
      color: #fff;
      width: 90%;
      max-width: 400px;
      box-shadow: 0 0 20px rgba(0,0,0,0.7);
      backdrop-filter: blur(8px);
      animation: fadeIn 1s ease;
    }

    .otp-card img {
      width: 60px;
      margin-bottom: 10px;
    }

    .otp-card h4 {
      font-family: 'Cinzel', serif;
      font-size: 1.8rem;
      color: #D4AF37;
      margin: 0.5rem 0;
    }

    .otp-card p {
      color: #ccc;
      font-size: 0.95rem;
    }

    /* OTP input boxes */
    .otp-inputs {
      display: flex;
      justify-content: center;
      gap: 10px;
      margin: 1rem 0;
    }
    .otp-inputs input {
      width: 48px;
      height: 55px;
      font-size: 1.5rem;
      text-align: center;
      border-radius: 8px;
      border: 2px solid #D4AF37;
      background: rgba(255,255,255,0.05);
      color: #D4AF37;
      font-weight: bold;
      outline: none;
      transition: all 0.3s;
    }
    .otp-inputs input:focus {
      border-color: #FF5733;
      background: rgba(255,87,51,0.2);
      box-shadow: 0 0 10px rgba(255,87,51,0.5);
      color: #fff;
    }

    /* Buttons */
    .btn {
      width: 48%;
      border-radius: 25px;
      padding: 0.6rem;
      font-weight: bold;
      border: none;
      cursor: pointer;
      transition: all 0.3s;
      font-size: 1rem;
    }
    .btn-verify {
      background: linear-gradient(145deg, #b06f12, #d4af37);
      color: #111;
    }
    .btn-verify:hover {
      background: linear-gradient(145deg, #d4af37, #b06f12);
      transform: translateY(-2px);
      box-shadow: 0 6px 18px rgba(212,175,55,0.5);
    }
    .btn-resend {
      background: transparent;
      border: 2px solid #5DADE2;
      color: #5DADE2;
    }
    .btn-resend:hover:not(:disabled) {
      background: #5DADE2;
      color: #fff;
      transform: translateY(-2px);
      box-shadow: 0 6px 18px rgba(93,173,226,0.5);
    }
    .btn-resend:disabled {
      opacity: 0.6;
      cursor: not-allowed;
    }

    /* Timer text */
    #timerText {
      font-size: 0.85rem;
      color: #ccc;
      margin-top: 10px;
    }

    /* Animations */
    @keyframes fadeIn {
      from { opacity:0; transform: translateY(30px); }
      to { opacity:1; transform: translateY(0); }
    }

    .shake { animation: shake 0.4s; }
    @keyframes shake {
      25% { transform: translateX(-6px); }
      50% { transform: translateX(6px); }
      75% { transform: translateX(-6px); }
    }

    /* Responsive */
    @media (max-width: 480px) {
      .otp-inputs input {
        width: 38px;
        height: 46px;
        font-size: 1.2rem;
      }
      .btn { font-size: 0.85rem; padding: 0.45rem; }
      .otp-card h4 { font-size: 1.4rem; }
    }
    @media (max-width: 360px) {
      .otp-inputs { gap: 6px; }
      .otp-inputs input {
        width: 32px;
        height: 42px;
        font-size: 1rem;
      }
    }
  </style>
</head>
<body>
  <div class="otp-card" id="otpCard">
    <img src="../images/favicon.png" alt="logo">
    <h4>Email Verification</h4>
    <p>Enter the OTP sent to your registered email</p>

    <form id="otpForm" method="POST">
      <div class="otp-inputs">
        <div class="otp-inputs">
  <input type="tel" name="otp[]" inputmode="numeric" pattern="[0-9]*" maxlength="1" required>
  <input type="tel" name="otp[]" inputmode="numeric" pattern="[0-9]*" maxlength="1" required>
  <input type="tel" name="otp[]" inputmode="numeric" pattern="[0-9]*" maxlength="1" required>
  <input type="tel" name="otp[]" inputmode="numeric" pattern="[0-9]*" maxlength="1" required>
  <input type="tel" name="otp[]" inputmode="numeric" pattern="[0-9]*" maxlength="1" required>
  <input type="tel" name="otp[]" inputmode="numeric" pattern="[0-9]*" maxlength="1" required>
</div>

      </div>

      <div style="display:flex; justify-content: space-between; gap:10px;">
        <button type="submit" class="btn btn-verify">Verify</button>
        <button type="button" id="resendBtn" class="btn btn-resend">Resend</button>
      </div>
      <p id="timerText"></p>
    </form>
  </div>

  <script>
    // OTP auto-focus
    const inputs = document.querySelectorAll('.otp-inputs input');
    inputs.forEach((input, i) => {
      input.addEventListener('input', () => {
        if (input.value && i < inputs.length-1) inputs[i+1].focus();
      });
      input.addEventListener('keydown', e => {
        if (e.key === "Backspace" && !input.value && i > 0) inputs[i-1].focus();
      });
    });

    // Countdown logic for resend
    const resendBtn = document.getElementById('resendBtn');
    const timerText = document.getElementById('timerText');
    function startCountdown(sec) {
      resendBtn.disabled = true;
      timerText.textContent = `Resend available in ${sec}s`;
      let timer = setInterval(() => {
        sec--;
        if (sec > 0) {
          timerText.textContent = `Resend available in ${sec}s`;
        } else {
          clearInterval(timer);
          resendBtn.disabled = false;
          timerText.textContent = "You can resend OTP now.";
        }
      }, 1000);
    }
    startCountdown(30);

    // Shake effect if wrong OTP (simulate)
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('wrong') === '1') {
      const card = document.getElementById('otpCard');
      card.classList.add('shake');
      setTimeout(()=>card.classList.remove('shake'), 400);
    }
  </script>
</body>
</html>
