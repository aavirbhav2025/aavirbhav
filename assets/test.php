<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . 'assets/vendor/autoload.php'; // adjust path to autoload.php

$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->isSMTP();
    $mail->SMTPDebug  = 2; // Enable debug output
    $mail->Debugoutput = 'html';
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'aavirbhav2025@gmail.com';  
    $mail->Password   = 'zeza ifuy joqk wlbu';  // Gmail App Password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 
    $mail->Port       = 587;

    // Recipients
    $mail->setFrom('yourgmail@gmail.com', 'Mailer Test');
    $mail->addAddress('anotheremail@example.com', 'Test User'); // receiver

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'SMTP Test';
    $mail->Body    = 'This is a <b>test email</b> sent using Gmail SMTP & PHPMailer!';
    $mail->AltBody = 'This is a test email sent using Gmail SMTP & PHPMailer!';

    $mail->send();
    echo '✅ Test message sent successfully';
} catch (Exception $e) {
    echo "❌ Message could not be sent. Error: {$mail->ErrorInfo}";
}
