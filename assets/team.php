<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['username'])) {
    header("Location: form.html");
    exit();
}

// Pricing rules
$eventPrices = [
    "Tug of War" => 800,
    "Corporate Walk" => 1000,
    "Singing" => 500,
    "Dancing" => 700,
    "Drama" => 600,
    "Painting" => 400,
    "Quiz" => 300,
    "Debate" => 350,
    "Photography" => 450,
    "Cooking" => 550,
    "Coding Challenge" => 900,
    "Startup Pitch" => 1200
];
$defaultPrice = 100;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $type = $_POST['type'] ?? 'individual';
    $events = $_POST['events'] ?? [];
    $names = $_POST['participant_name'] ?? [];
    $contacts = $_POST['participant_contact'] ?? [];

    // Auto-upgrade to team if more than 2 events in individual mode
    if ($type === "individual" && count($events) > 2) {
        $type = "team";
    }

    // Calculate amount
    if ($type === "team") {
        $amount = 1600;
        if (in_array("Treasure Hunt", $events)) { $amount += 800; }
        if (in_array("Corporate Walk", $events)) { $amount += 1000; }
    } else {
        $amount = 0;
        foreach ($events as $event) {
            $amount += $eventPrices[$event] ?? $defaultPrice;
        }
    }

    // Store in session
    $_SESSION['registration'] = [
        'type' => $type,
        'events' => $events,
        'names' => $names,
        'contacts' => $contacts,
        'amount' => $amount
    ];

    header("Location: razorpay/index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Team Registration</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="icon" type="image/png" href="images/favicon.png">
  <link rel="apple-touch-icon" href="images/favicon.png">
</head>
<Style>
body{
    background-image: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url(images/bgimg.jpg);
 background-size: cover;
background-position: center;
}
.container{
    color: white;
}
    </style>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="#">Aavirbhav - Events</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse justify-content-end" id="navbarContent">
      <ul class="navbar-nav align-items-center">
        <li class="nav-item me-3 text-white">
          Hello, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>
        </li>
        <li class="nav-item">
          <a href="forms/logout.php" class="btn btn-outline-light btn-sm">Logout</a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<div class="container mt-4">
    <h2 class="mb-4">Register Your Team</h2>
    <form method="POST" id="eventForm" novalidate>
        <div class="mb-3">
            <label class="form-label">Registration Type</label>
            <select class="form-select" name="type" id="regType" required>
                <option value="individual">Individual</option>
                <option value="team">Team</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Select Events</label>
            <?php foreach ($eventPrices as $event => $price): ?>
                <div class="form-check">
                    <input class="form-check-input event-checkbox" type="checkbox" name="events[]" value="<?php echo htmlspecialchars($event); ?>" data-price="<?php echo $price; ?>" id="<?php echo md5($event); ?>">
                    <label class="form-check-label" for="<?php echo md5($event); ?>">
                        <?php echo htmlspecialchars($event) . " (₹" . $price . ")"; ?>
                    </label>
                </div>
            <?php endforeach; ?>
        </div>

        <div id="participantFields"></div>

        <div class="mt-3">
            <h5>Total Price: ₹<span id="totalPrice">0</span></h5>
        </div>

        <button type="submit" class="btn btn-primary mt-3">Proceed to Payment</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const checkboxes = document.querySelectorAll(".event-checkbox");
    const participantFields = document.getElementById("participantFields");
    const regType = document.getElementById("regType");
    const totalPriceEl = document.getElementById("totalPrice");

    function updateParticipantsAndPrice() {
        participantFields.innerHTML = "";
        let selectedCount = 0;
        let totalPrice = 0;

        checkboxes.forEach(cb => {
            if (cb.checked) {
                selectedCount++;
                const eventName = cb.value;
                const eventPrice = parseInt(cb.getAttribute("data-price")) || 0;
                totalPrice += eventPrice;

                participantFields.innerHTML += `
                    <div class="border rounded p-3 mb-3">
                        <h5>${eventName} - Participants</h5>
                        <div class="row g-2">
                            <div class="col-md-3">
                                <input type="text" class="form-control"
                                    placeholder="Name 1"
                                    name="participant_name[${eventName}][]"
                                    pattern="[A-Za-z\\s]{3,}"
                                    title="At least 3 letters, alphabets only" required>
                            </div>
                            <div class="col-md-3">
                                <input type="tel" class="form-control"
                                    placeholder="Number 1"
                                    name="participant_contact[${eventName}][]"
                                    pattern="\\d{10}"
                                    title="Enter 10 digit number" required>
                            </div>
                            <div class="col-md-3">
                                <input type="text" class="form-control"
                                    placeholder="Name 2"
                                    name="participant_name[${eventName}][]"
                                    pattern="[A-Za-z\\s]{3,}"
                                    title="At least 3 letters, alphabets only" required>
                            </div>
                            <div class="col-md-3">
                                <input type="tel" class="form-control"
                                    placeholder="Number 2"
                                    name="participant_contact[${eventName}][]"
                                    pattern="\\d{10}"
                                    title="Enter 10 digit number" required>
                            </div>
                        </div>
                    </div>
                `;
            }
        });

        // Auto-switch to team if more than 2 events in individual mode
        if (regType.value === "individual" && selectedCount > 2) {
            regType.value = "team";
        }

        // If team, price is fixed
        if (regType.value === "team") {
            totalPrice = 1600;
        }

        if (regType.value === "team") { document.querySelector(".mt-3 h5").style.display = "none"; } else { document.querySelector(".mt-3 h5").style.display = "block"; totalPriceEl.textContent = totalPrice; // Always show total price }
    }

    checkboxes.forEach(cb => {
        cb.addEventListener("change", updateParticipantsAndPrice);
    });

    regType.addEventListener("change", updateParticipantsAndPrice);
});

    document.getElementById("eventForm").addEventListener("submit", function(e) {
        let valid = true;
        document.querySelectorAll("input[name='participant_name[]']").forEach(input => {
            if (!input.value.trim()) {
                valid = false;
            }
        });
        document.querySelectorAll("input[name='participant_contact[]']").forEach(input => {
            if (!input.value.trim()) {
                valid = false;
            }
        });
        if (!valid) {
            e.preventDefault();
            alert("Please fill in all participant names and contact numbers before submitting.");
        }
    });

</script>
</body>
</html>
