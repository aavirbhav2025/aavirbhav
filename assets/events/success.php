<?php
session_start();
require '../forms/db.php';

// Redirect if not logged in
if (!isset($_SESSION['username'])) {
    header("Location: form.html");
    exit();
}
$user_email = ($_SESSION['email']); // remove spaces

$reg_sql = "SELECT * FROM registrations WHERE email = ? ORDER BY id DESC LIMIT 1";
$stmt = $conn->prepare($reg_sql);
$stmt->bind_param("s", $user_email);
$stmt->execute();
$reg_result = $stmt->get_result();
$registration = $reg_result->fetch_assoc();
$stmt->close();

if (!$registration) {
    die("No registration found for this email: " . htmlspecialchars($user_email));
}

$events = json_decode($registration['events'], true) ?? [];
$participants = json_decode($registration['participants'], true) ?? []; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Receipt - Aavirbhav 2025</title>
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
            <img src="../images/logo.png" alt="Event Logo" class="logo">
            <h2 class="mb-4">Aavirbhav 2025</h2>
            <h2 class="mb-4">Event Registration Receipt</h2>
        </div>

        <p><strong>Name:</strong> <?= htmlspecialchars($registration['name']); ?></p>
        <p><strong>Contact:</strong> <?= htmlspecialchars($registration['phone']); ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($registration['email']); ?></p>
        <p><strong>Type:</strong> <?= htmlspecialchars($registration['type']); ?></p>

        <hr>
        <p><strong>Registration ID:</strong> <?= htmlspecialchars($registration['id']); ?></p>
        <p><strong>Amount Paid:</strong> ₹<?= number_format($registration['amount'], 2); ?></p>
        <p><strong>Registered At:</strong> <?= htmlspecialchars($registration['created_at']); ?></p>

        <hr>
        <h5>Registered Events:</h5>
        <ul>
            <?php foreach ($events as $event): ?>
                <li><?= htmlspecialchars($event); ?></li>
            <?php endforeach; ?>
        </ul>

        <h5>Participants:</h5>
        <ul>
<?php 
if (!empty($participants)) {
    // If participants are nested by event (like "Videography" → {1,2,...})
    foreach ($participants as $event => $members) {
        foreach ($members as $person) {
            echo "<li>" 
                . htmlspecialchars($person['name'] ?? 'No Name') 
                . " (" . htmlspecialchars($person['phone'] ?? 'No Contact') . ")</li>";
        }
    }
} else {
    echo "<li>No Name (No Contact)</li>";
}
?>
</ul>


        <hr>
        <p class="text-center" style="color: gold;">In the Game of Thrones, you win or you pay.</p>
    </div>

    <div class="text-center mt-3">
        <button class="btn btn-gold" id="downloadPDF">Download PDF Receipt</button>
        <a href="../forms/logout.php" class="btn btn-danger ms-2">Return to Home</a>
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
        pdf.save("registration_receipt_<?= $registration['id']; ?>.pdf");
    });
}

// Manual download
document.getElementById('downloadPDF').addEventListener('click', generatePDF);
</script>

</body>
</html>
