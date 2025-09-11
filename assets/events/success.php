<?php
session_start();
require '../forms/db.php';
require '../vendor/autoload.php'; // Dompdf
use Dompdf\Dompdf;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Redirect if not logged in
if (!isset($_SESSION['username'])) {
    header("Location: form.html");
    exit();
}

$user_email = $_SESSION['email'];
$clgname = $_SESSION['clgname'];

// Fetch latest registration
$reg_sql = "SELECT * FROM registrations WHERE email = ? ORDER BY id DESC LIMIT 1";
$stmt = $conn->prepare($reg_sql);
$stmt->bind_param("s", $user_email);
$stmt->execute();
$reg_result = $stmt->get_result();
$registration = $reg_result->fetch_assoc();
$stmt->close();

if (!$registration) die("No registration found for this email.");

$events = json_decode($registration['events'], true) ?? [];
$participants = json_decode($registration['participants'], true) ?? [];

// --- Generate PDF using Dompdf ---
ob_start();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Receipt</title>
<style>
body { font-family: 'Cinzel', serif; background:#0a0a0a; color:#f8f1e4; }
.invoice-box { background: rgba(20,20,20,0.95); padding:25px; border:3px solid gold; border-radius:15px; max-width:850px; margin:auto; }
h2 { font-weight:bold; color:gold; text-shadow:0 0 5px #000; }
ul { list-style:none; padding:0; }
ul li::before { content:"⚔️ "; }
</style>
</head>
<body>
<div class="invoice-box">
<h2>Aavirbhav 2025 - Registration Receipt</h2>
<p><strong>Name:</strong> <?= htmlspecialchars($registration['name']); ?></p>
<p><strong>Contact:</strong> <?= htmlspecialchars($registration['phone']); ?></p>
<p><strong>Email:</strong> <?= htmlspecialchars($registration['email']); ?></p>
<p><strong>College:</strong> <?= htmlspecialchars($clgname); ?></p>
<p><strong>Type:</strong> <?= htmlspecialchars($registration['type']); ?></p>
<hr>
<p><strong>Registration ID:</strong> <?= htmlspecialchars($registration['id']); ?></p>
<p><strong>Amount Paid:</strong> ₹<?= number_format($registration['amount'],2); ?></p>
<hr>
<h4>Events:</h4>
<ul>
<?php foreach($events as $event) { echo "<li>" . htmlspecialchars($event) . "</li>"; } ?>
</ul>
<h4>Participants:</h4>
<ul>
<?php
if(!empty($participants)){
    foreach($participants as $event=>$members){
        foreach($members as $person){
            echo "<li>".htmlspecialchars($person['name'] ?? 'No Name') . " (" . htmlspecialchars($person['phone'] ?? 'No Contact').")</li>";
        }
    }
}
?>
</ul>
</div>
</body>
</html>
<?php
$html = ob_get_clean();

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4','portrait');
$dompdf->render();

// Save PDF temporarily
$pdfOutput = $dompdf->output();
$pdfFileName = "Aavirbhav_registration_".$registration['id'].".pdf";
file_put_contents($pdfFileName, $pdfOutput);

// --- Send PDF via PHPMailer ---
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
            $mail->Host       = "smtp.hostinger.com"; // Hostinger SMTP
            $mail->SMTPAuth   = true;
            $mail->Username   = "contact@aavirbhav.tech"; // your webmail
            $mail->Password   = "T;h^o!oNb4";             // your webmail password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom("contact@aavirbhav.tech", "Aavirbhav Event Registration");
    $mail->addAddress($registration['email'], $registration['name']);

    $mail->isHTML(true);
    $mail->Subject = "Your Aavirbhav 2025 Registration Receipt";
    $mail->Body = "
        <h2>Hello ".htmlspecialchars($registration['name']).",</h2>
        <p>Thank you for registering the events! Your receipt is attached below.Bring Xerox of receipt on event day. See you there....!</p>
    ";

    // Attach PDF
    $mail->addAttachment($pdfFileName);
    $mail->send();
    $emailStatus = "Email sent successfully to ".$registration['email'];
} catch (Exception $e) {
    $emailStatus = "Mailer Error: " . $mail->ErrorInfo;
}

// Delete temporary PDF
unlink($pdfFileName);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Receipt</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background:#0a0a0a; color:#f8f1e4; font-family:'Cinzel', serif; }
.invoice-box { background: rgba(20,20,20,0.95); padding:25px; border:3px solid gold; border-radius:15px; max-width:850px; margin:auto; }
h2 { font-weight:bold; color:gold; text-shadow:0 0 5px #000; }
</style>
</head>
<body>
<div class="container mt-5">
<div class="invoice-box">
<h2>Receipt Generated & Email Sent</h2>
<p><?= $emailStatus ?></p>

<p><strong>Name:</strong> <?= htmlspecialchars($registration['name']); ?></p>
<p><strong>Contact:</strong> <?= htmlspecialchars($registration['phone']); ?></p>
<p><strong>Email:</strong> <?= htmlspecialchars($registration['email']); ?></p>
<p><strong>College:</strong> <?= htmlspecialchars($clgname); ?></p>
<p><strong>Type:</strong> <?= htmlspecialchars($registration['type']); ?></p>

<hr>
<p><strong>Registration ID:</strong> <?= htmlspecialchars($registration['id']); ?></p>
<p><strong>Amount Paid:</strong> ₹<?= number_format($registration['amount'],2); ?></p>

<h4>Events:</h4>
<ul>
<?php foreach($events as $event) { echo "<li>" . htmlspecialchars($event) . "</li>"; } ?>
</ul>

<h4>Participants:</h4>
<ul>
<?php
if(!empty($participants)){
    foreach($participants as $event=>$members){
        foreach($members as $person){
            echo "<li>".htmlspecialchars($person['name'] ?? 'No Name') . " (" . htmlspecialchars($person['phone'] ?? 'No Contact').")</li>";
        }
    }
}
?>
</ul>
</div>
</div>
</body>
</html>
