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
$events       = json_encode($registration['events']);        
$participants = json_encode($registration['participants']);  
$amount      = $_SESSION['registration']['amount'] ?? 0;

$qrImages = [
    800  => "800.jpg",
    1000  => "1000.jpg",
    1600 => "1600.jpg",
    2400 => "2400.jpg",
    2500 => "2500.jpg",
    2600 => "2600.jpg",
    3400 => "3400.jpg"
];
$qrImage = isset($qrImages[$amount]) ? $qrImages[$amount] : "qr_default.png";
$qrSrc = "../images/" . htmlspecialchars($qrImage);
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $utr = $_POST['transaction_id'] ?? '';

    // Handle file upload
    $uploadDir = "../uploads/"; // make sure this folder exists and is writable
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileName = $_FILES['payment_screenshot']['name'];
    $fileTmp  = $_FILES['payment_screenshot']['tmp_name'];
    $fileExt  = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    // Allow only images
    $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array($fileExt, $allowedExt)) {
        die("Invalid file type. Only JPG, PNG, GIF, WEBP allowed.");
    }

    // Create unique file name
    $newFileName = uniqid("payment_", true) . "." . $fileExt;
    $filePath = $uploadDir . $newFileName;

    if (!move_uploaded_file($fileTmp, $filePath)) {
        die("Failed to upload screenshot. Please try again.");
    }

    // Save relative path to DB (so you can retrieve later)
    $screenshotPath = "uploads/" . $newFileName;

    // Insert into DB
    $sql = "INSERT INTO registrations 
            (name, email, phone, type, events, participants, amount, order_id, screenshot, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
    }
    $stmt->bind_param(
        "ssssssdss",
        $userName,     
        $userEmail,    
        $userContact,  
        $type,         
        $events,       
        $participants, 
        $amount,      
        $utr,         
        $screenshotPath  
    );

    if ($stmt->execute()) {
        unset($_SESSION['registration']);
        header("Location: success.php");
        exit();
    } else {
        echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;    
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
                 alt="payment UPI ID harikiranap123@okaxis"
                 class="qr-image"
                 onerror="this.onerror=null;this.src='/aavirbhav/assets/images/qr_default.png';">
        </div>
        <div align="center">      
        <p class="mb-2"><strong>Any Technical issue? Please contact:</strong></p>
        <p><a style="color:green; background-color:white; padding:3px;" href="https://wa.me/7012048118" target="_blank"
        class="text-success text-decoration-none fw-bold">
        <i class="fab fa-whatsapp"></i>
        <strong> Chat on WhatsApp  </strong></a><br>or call: 
        <strong>+91 7012048118</strong></p>
        </div>
        <!-- Payment Confirmation Form -->
        <form method="post" enctype="multipart/form-data" class="text-start">
            <div class="mb-3">
                <label class="form-label">Registrant Email *</label>
                <input type="email" 
       name="registrant_email" 
       class="form-control" 
       value="<?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>" 
       placeholder="Enter Email" 
       readonly>

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