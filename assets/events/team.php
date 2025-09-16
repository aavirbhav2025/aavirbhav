<?php
session_start();

// Redirect to login if not logged in 
if (!isset($_SESSION['username'])) { 
header("Location: ../forms/form.html"); 
exit(); }

// Pricing rules
$eventPrices = [
    "Tug of War" => 800,
    "Corporate Walk" => 1000,
    "Web Design" => 100,
    "It Manager" => 100,
    "IT Quiz" => 100,
    "Treasure Hunt" => 100,
    "Coding" => 100,
    "Photography" => 100,
    "Videography" => 100,
    "Gaming" => 100
];
$defaultPrice = 100;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $type = $_POST['type'] ?? 'individual';
    $events = $_POST['events'] ?? [];
    $participants = $_POST['participants'] ?? [];

    // Save form data
    $_SESSION['team_data'] = json_encode([
        'inputs' => $_POST,
        'selected_events' => $events
    ]);

    // Calculate amount
    if ($type === "team") {
        $amount = 1600; // flat price for all IT events
    } else {
        $amount = 0;
        foreach ($events as $event) {
            $amount += $eventPrices[$event] ?? $defaultPrice;
        }
    }

    // Store registration
    $_SESSION['registration'] = [
        'type' => $type,
        'events' => $events,
        'participants' => $participants,
        'amount' => $amount
    ];

    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Team Registration - Aavirbhav Events</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../images/favicon.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: linear-gradient(rgba(0,0,0,0.7),rgba(0,0,0,0.7)),
                        url('bg.jpg');
            background-size: cover;
            background-position: center;
            min-height: 100vh;
        }
        input[type="checkbox"].custom-checkbox { display: none; }
        .event-card {
            display:flex;align-items:center;justify-content:center;
            padding:1rem;border:2px solid rgba(255,255,255,0.2);
            border-radius:0.75rem;background:rgba(255,255,255,0.05);
            cursor:pointer;transition:.2s;min-height:80px;
        }
        input[type="checkbox"].custom-checkbox:checked + .event-card {
            border-color:#3b82f6;background:rgba(59,130,246,0.2);
            box-shadow:0 0 10px rgba(59,130,246,0.8);
        }
    </style>
</head>
<body class="text-white">

<div class="max-w-4xl mx-auto px-4 py-8">
    <h2 class="text-3xl font-bold mb-6">Register</h2>

    <form id="eventForm" method="POST" class="space-y-6">
        <div class="bg-white/10 p-6 rounded-lg">
            <label class="block text-lg font-medium mb-3">Registration Type</label>
            <select id="regType" name="type" class="w-full p-3 rounded-lg bg-white/20 border border-white/30 text-black">
                <option value="individual">Individual (Open Events)</option>
                <option value="team">Team (IT Events)</option>
            </select>
        </div>

        <p class="text-yellow-300 mb-4 text-sm">
            <strong>Team</strong>: Flat rate ₹1600 (choose IT events only).  
            <strong>Individual</strong>: Only Tug of War or Corporate Walk.
        </p>

        <!-- IT Events -->
        <div id="itEventsWrapper" class="bg-white/10 p-6 rounded-lg">
            <label class="block text-lg font-medium mb-4">IT Events (Team only)</label>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php
                foreach (["Web Design","It Manager","IT Quiz","Treasure Hunt",
                          "Coding","Photography","Videography","Gaming"] as $ev) {
                    echo '<label>
                        <input type="checkbox" name="events[]" value="'.$ev.'" data-price="'.$eventPrices[$ev].'" class="event-checkbox it-event custom-checkbox">
                        <div class="event-card">'.$ev.'</div>
                    </label>';
                }
                ?>
            </div>
        </div>

        <!-- Open Events -->
        <div id="openEventsWrapper" class="bg-white/10 p-6 rounded-lg">
            <label class="block text-lg font-medium mb-4">Open Events (Individual only)</label>
            <select id="openEvents" name="events[]" class="w-full p-3 rounded-lg bg-white/20 border border-white/30 text-white">
                <option value="">-- Select --</option>
                <option value="Tug of War" data-price="800">Tug of War (₹800)</option>
                <option value="Corporate Walk" data-price="1000">Corporate Walk (₹1000)</option>
            </select>
        </div>

        <div id="participantFields" class="space-y-4"></div>

        <div class="bg-white/10 p-6 rounded-lg">
            <h3 class="text-2xl font-bold">Total Price: ₹<span id="totalPrice">0</span></h3>
        </div>

        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 px-6 rounded-lg">
            Proceed to Payment
        </button>
    </form>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const regType = document.getElementById("regType");
    const itEventsWrapper = document.getElementById("itEventsWrapper");
    const openEventsWrapper = document.getElementById("openEventsWrapper");
    const totalPriceEl = document.getElementById("totalPrice");
    const participantFields = document.getElementById("participantFields");
    const openEventsSelect = document.getElementById("openEvents");

    function updateForm() {
        let type = regType.value;
        participantFields.innerHTML = "";
        let total = 0;

        if (type === "team") {
            itEventsWrapper.style.display = "block";
            openEventsWrapper.style.display = "none";
            total = 1600;
            document.querySelectorAll(".it-event:checked").forEach(cb => {
                participantFields.innerHTML += participantBlock(cb.value, 2, "Team (2 members)");
            });
        } else {
            itEventsWrapper.style.display = "none";
            openEventsWrapper.style.display = "block";
            if (openEventsSelect.value) {
                let opt = openEventsSelect.selectedOptions[0];
                total = parseInt(opt.dataset.price);
                participantFields.innerHTML += participantBlock(opt.value, 2, "Individual (2 members)");
            }
        }
        totalPriceEl.textContent = total;
    }

    function participantBlock(eventName, count, typeText) {
        let html = `<div class="bg-white/10 p-6 rounded-lg mb-4">
                        <h3 class="text-xl font-bold mb-4">${eventName} - Participants</h3>
                        <p class="text-gray-300 mb-4">${typeText}</p>`;
        for (let i=1; i<=count; i++) {
            html += `<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-2">
                        <input type="text" name="participants[${eventName}][${i}][name]" 
                               placeholder="Participant ${i} Name" required 
                               class="w-full p-2 rounded bg-white/20 border border-white/30 text-white">
                        <input type="tel" name="participants[${eventName}][${i}][phone]" 
                               placeholder="Phone" required maxlength="10" pattern="[0-9]{10}" 
                               class="w-full p-2 rounded bg-white/20 border border-white/30 text-white">
                     </div>`;
        }
        return html + `</div>`;
    }

    document.querySelectorAll(".it-event, #regType, #openEvents").forEach(el => {
        el.addEventListener("change", updateForm);
    });

    updateForm();
});
</script>
</body>
</html>
