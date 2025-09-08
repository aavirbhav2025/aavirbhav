<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['username'])) {
    header("Location: form.html");
    exit();
}

require 'config.php'; // assuming you have Razorpay config here

// Fetch values from session
$userName = $_SESSION['username'] ?? '';
$userContact = $_SESSION['phone'] ?? '';
$userEmail = $_SESSION['email'] ?? '';
$amount = $_SESSION['registration']['amount'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Aavirbhav Event Payment</title>
    <link rel="icon" type="image/png" href="../images/favicon.png">
  <link rel="apple-touch-icon" href="../images/favicon.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-image: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url(../images/bgimg.jpg);
 background-size: cover;
background-position: center;
        }
        .card {
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="#">Aavirbhav</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse justify-content-end" id="navbarContent">
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
    <div class="card p-4">
        <h3 class="mb-3">Confirm Your Details</h3>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($userName); ?></p>
        <p><strong>Contact:</strong> <?php echo htmlspecialchars($userContact); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($userEmail); ?></p>
        <p><strong>Amount:</strong> ₹<?php echo number_format($amount); ?></p>

        <button id="payButton" class="btn btn-primary mt-3">Pay Now</button>
    </div>
</div>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
document.getElementById('payButton').onclick = function(e) {
    fetch('create_order.php', { method: 'POST' })
    .then(response => response.json())
    .then(order => {
        const options = {
            key: "<?php echo RAZORPAY_KEY_ID; ?>",
            amount: order.amount, // in paise
            currency: order.currency,
            name: "Aavirbhav Payment",
            image: "../images/aavirbhav.jpg",
            description: "Payment for Registered Events",
            order_id: order.id,
            prefill: {
                name: <?php echo json_encode($userName); ?>,
                contact: <?php echo json_encode($userContact); ?>,
                email: <?php echo json_encode($userEmail); ?>
            },
            theme: {
                color: "#3399cc"
            },
            handler: function (response) {
    // Redirect to verify.php with payment details
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'verify.php';

    const fields = {
        razorpay_payment_id: response.razorpay_payment_id,
        razorpay_order_id: response.razorpay_order_id,
        razorpay_signature: response.razorpay_signature
    };

    for (const key in fields) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = fields[key];
        form.appendChild(input);
    }

    document.body.appendChild(form);
    form.submit();
}

        };
        const rzp = new Razorpay(options);
        rzp.open();
        e.preventDefault();
    });
};
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
