<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['username'])) {
    header("Location: form.html");
    exit();
}

// Fetch values from session
$userName    = $_SESSION['username'] ?? '';
$userContact = $_SESSION['phone'] ?? '';
$userEmail   = $_SESSION['email'] ?? '';
$amount      = $_SESSION['registration']['amount'] ?? 0;

// Map amounts (in rupees) to QR codes
$qrImages = [
    100  => "100.jpg",
    200  => "200.jpg",
    1600 => "1600.jpg",
    2400 => " ",
    2500 => " ",
    2600 => " ",
    3400 => " "
];

// Pick correct QR code or default
$qrImage = isset($qrImages[$amount]) ? $qrImages[$amount] : "qr_default.png";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Aavirbhav Event Payment</title>
    <link rel="icon" type="image/png" href="../assets/images/favicon.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-image: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url(../assets/images/bgimg.jpg);
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
        <p><strong>Amount:</strong> ₹<?php echo number_format($amount); ?></p>

        <!-- QR Image -->
        <div class="my-4">
            <img src="/aavirbhav/assets/images/<?php echo htmlspecialchars($qrImage); ?>" 
                alt="Payment QR" 
                class="qr-image">
            <p class="mt-2 text-warning">Scan this QR code to complete your payment.</p>
        </div>

        <!-- Payment Confirmation Form -->
        <form action="verify.php" method="post" enctype="multipart/form-data" class="text-start">
            <div class="mb-3">
                  <label class="form-label">Registrant Email *</label>
                  <input type="email" name="Registrant Email" class="form-control" placeholder="Enter Email" required>
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