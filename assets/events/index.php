<?php
session_start();
require '../forms/db.php';

if (!isset($_SESSION['username'])) {
    header("Location: form.html");
    exit();
}


$userName    = $_SESSION['username'] ?? '';
$userContact = $_SESSION['phone'] ?? '';
$userEmail   = $_SESSION['email'] ?? '';

$registration = $_SESSION['registration'];
$type         = $registration['type'] ?? 'individual';
$events       = json_encode($registration['events']);        // store as JSON
$participants = json_encode($registration['participants']);  // store as JSON
$amount      = $_SESSION['registration']['amount'] ?? 0;

$qrImages = [
    100  => "100.jpg",
    200  => "200.jpg",
    1600 => "1600.jpg",
    2400 => "2400.jpg",
    2500 => "2500.jpg",
    2600 => "2600.jpg",
    3400 => "3400.jpg"
];

// Pick correct QR code or default
$qrImage = isset($qrImages[$amount]) ? $qrImages[$amount] : "qr_default.png";

// Build the web URL to the image under XAMPP.
// If the site runs at http://localhost/aavirbhav/, use a root-relative URL from that project root:
$qrSrc = "../images/" . htmlspecialchars($qrImage);



if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $sql = "INSERT INTO registrations 
            (name, email, phone, type, events, participants, amount, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
    }

    $stmt->bind_param("ssssssd", $userName, $userEmail, $userContact, $type, $events, $participants, $amount);

    if ($stmt->execute()) {
    // Redirect to success.php after insert
    header("Location: success.php");
    exit(); // Always call exit() after header redirect
} else {
    // Show error if insert fails
    echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
    echo "<pre>";
    var_dump($userName, $userEmail, $userContact, $type, $events, $participants, $amount);
    
}
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Aavirbhav Event Payment</title>
    <link rel="icon" type="image/png" href="/aavirbhav/assets/images/favicon.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body {
            background-image: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url(/aavirbhav/assets/images/bgimg.jpg);
            background-size: cover;
            background-position: center;
            min-height: 100vh;
        }
        .card {
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
        }
        .qr-image {
            max-width: 300px;
            border: 3px solid white;
            border-radius: 10px;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="#">Aavirbhav</a>
    <div class="collapse navbar-collapse justify-content-end">
      <ul class="navbar-nav align-items-center">
        <li class="nav-item me-3 text-white">
          Hello, <strong><?php echo htmlspecialchars($userName); ?></strong>
        </li>
        <li class="nav-item">
          <a href="../forms/logout.php" class="btn btn-outline-light btn-sm">Logout</a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<!-- Main Content -->
<div class="container mt-5">
    <div class="card p-4 text-center">
        <h3 class="mb-3">Confirm Your Details</h3>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($userName); ?></p>
        <p><strong>Contact:</strong> <?php echo htmlspecialchars($userContact); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($userEmail); ?></p>
        <p><strong>Amount:</strong> ₹<?php echo number_format((float)$amount); ?></p>

        <!-- QR Image -->
        <div class="my-4">
            <img src="<?php echo $qrSrc; ?>"
                 alt="Payment QR"
                 class="qr-image"
                 onerror="this.onerror=null;this.src='/aavirbhav/assets/images/qr_default.png';">
        </div>
            <p class="mb-2"><strong>Any Technical issue? Please contact:</strong></p>
            <p><a href="https://wa.me/7012048118" target="_blank"
            class="text-success text-decoration-none fw-bold"><i class="fab fa-whatsapp"></i> Chat on WhatsApp</a><br>or call: <strong>+91 7012048118</strong></p>

        <!-- Payment Confirmation Form -->
        <form method="post" enctype="multipart/form-data" class="text-start">
            <div class="mb-3">
                <label class="form-label">Registrant Email *</label>
                <input type="email" name="registrant_email" class="form-control" placeholder="Enter Email" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Transaction/UTR ID *</label>
                <input type="text" name="transaction_id" class="form-control" placeholder="Enter UPI/Bank Transaction ID" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Upload Payment Screenshot *</label>
                <input type="file" name="payment_screenshot" class="form-control" accept="image/*" required>
            </div>
            <button type="submit" class="btn btn-success w-100">I Have Paid</button>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>