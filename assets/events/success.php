<?php
session_start();
require '../../forms/db.php';

if (!isset($_SESSION['last_payment_id'])) {
    header("Location: index.php");
    exit();
}

$payment_id = $_SESSION['last_payment_id'];

// Fetch payment details
$payment_sql = "SELECT * FROM payments WHERE payment_id = ?";
$stmt = $conn->prepare($payment_sql);
$stmt->bind_param("s", $payment_id);
$stmt->execute();
$payment_result = $stmt->get_result();
$payment = $payment_result->fetch_assoc();
$stmt->close();

// Fetch registration details
$reg_sql = "SELECT * FROM registrations WHERE payment_id = ?";
$stmt = $conn->prepare($reg_sql);
$stmt->bind_param("s", $payment_id);
$stmt->execute();
$reg_result = $stmt->get_result();
$registration = $reg_result->fetch_assoc();
$stmt->close();

// Decode participants JSON
$participants_data = json_decode($registration['participants_json'], true) ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Receipt - Game of Thrones Theme</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="icon" type="image/png" href="../../images/favicon.png">
  <link rel="apple-touch-icon" href="../../images/favicon.png">
    
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Fantasy Font -->
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;700&display=swap" rel="stylesheet">

    <style>
        body {
            background-color: #0a0a0a;
            color: #f8f1e4;
            font-family: 'Cinzel', serif;
        }
        .invoice-box {
            background: rgba(20, 20, 20, 0.95);
            padding: 25px;
            border: 3px solid gold;
            border-radius: 15px;
            box-shadow: 0 0 25px gold;
            max-width: 850px;
            margin: auto;
            background-image: url('assets/iron-throne.png');
            background-size: cover;
            background-position: center;
            background-blend-mode: overlay;
        }
        h2 {
            font-weight: bold;
            color: gold;
            text-shadow: 0 0 5px #000;
        }
        hr {
            border-top: 2px solid gold;
        }
        .logo {
            max-width: 120px;
            filter: drop-shadow(0 0 10px gold);
        }
        ul {
            list-style-type: none;
            padding: 0;
        }
        ul li::before {
            content: "⚔️ ";
        }
        .btn-gold {
            background-color: gold;
            color: black;
            font-weight: bold;
            border-radius: 8px;
            border: 2px solid #d4af37;
        }
        .btn-gold:hover {
            background-color: #ffdd55;
            border-color: gold;
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <div class="invoice-box" id="receipt">
        <div class="text-center">
            <img src="../../images/logo.png" alt="Event Logo" class="logo">
            <h2 class="mb-4">Aavirbhav 2025</h2>
            <h2 class="mb-4">Event Payment Receipt</h2>
        </div>

        <p><strong>Name:</strong> <?= htmlspecialchars($registration['name']); ?></p>
        <p><strong>Contact:</strong> <?= htmlspecialchars($registration['contact']); ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($registration['email']); ?></p>

        <hr>
        <p><strong>Order ID:</strong> <?= htmlspecialchars($payment['order_id']); ?></p>
        <p><strong>Payment ID:</strong> <?= htmlspecialchars($payment['payment_id']); ?></p>
        <p><strong>Amount Paid:</strong> ₹<?= number_format($registration['amount'], 2); ?></p>
        <p><strong>Payment Date:</strong> <?= htmlspecialchars($payment['created_at']); ?></p>

        <hr>
        <h5>Registered Events & Participants:</h5>
        <ul>
            <?php foreach ($participants_data as $event => $people): ?>
                <li>
                    <strong><?= htmlspecialchars($event); ?>:</strong>
                    <ul>
                        <?php foreach ($people as $person): ?>
                            <li><?= htmlspecialchars($person['name']); ?> - <?= htmlspecialchars($person['contact']); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </li>
            <?php endforeach; ?>
        </ul>

        <hr>
        <p class="text-center" style="color: gold;">In the Game of Thrones, you win or you pay.</p>
    </div>

        <div class="text-center mt-3">
        <button class="btn btn-gold" id="downloadPDF">Download PDF Receipt</button>
        <a href="../../forms/logout.php" class="btn btn-danger ms-2">Return to Home</a>
    </div>
</div>

</div>

<!-- PDF Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>
function generatePDF() {
    const { jsPDF } = window.jspdf;

    html2canvas(document.querySelector("#receipt"), { scale: 2 }).then(canvas => {
        const imgData = canvas.toDataURL("image/png");
        const pdf = new jsPDF("p", "mm", "a4");

        const imgProps = pdf.getImageProperties(imgData);
        const pdfWidth = pdf.internal.pageSize.getWidth();
        const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;

        pdf.addImage(imgData, "PNG", 0, 0, pdfWidth, pdfHeight);
        pdf.save("payment_receipt_<?= $payment_id; ?>.pdf");
    });
}

// Auto-download on page load
window.addEventListener("load", generatePDF);

// Manual download
document.getElementById('downloadPDF').addEventListener('click', generatePDF);
</script>

</body>
</html>
