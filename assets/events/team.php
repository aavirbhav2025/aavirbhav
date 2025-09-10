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

    // Save all form data into session as JSON (optional)
    $_SESSION['team_data'] = json_encode([
        'inputs' => $_POST,
        'selected_events' => $events
    ]);

    // Detect IT events selected
    $itEventsSelected = array_intersect($events, [
        "Web Design", "It Manager", "IT Quiz", "Treasure Hunt",
        "Coding", "Photography", "Videography", "Gaming"
    ]);

    // Force team type if more than 2 IT events
    if (count($itEventsSelected) > 2) {
        $type = "team";
    }

    // Calculate amount
    if ($type === "team") {
        $amount = 1600;
        if (in_array("Tug of War", $events)) { $amount += $eventPrices["Tug of War"]; }
        if (in_array("Corporate Walk", $events)) { $amount += $eventPrices["Corporate Walk"]; }
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
        'participants' => $participants,
        'amount' => $amount
    ];

    // Redirect AFTER computing and saving session
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
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), 
                        url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 600"><defs><linearGradient id="bg" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" style="stop-color:%23667eea;stop-opacity:1" /><stop offset="100%" style="stop-color:%23764ba2;stop-opacity:1" /></linearGradient></defs><rect width="1000" height="600" fill="url(%23bg)"/></svg>');
            background-size: cover;
            background-position: center;
            min-height: 100vh;
        }
        input[type="checkbox"].custom-checkbox { display: none; }
        .event-card {
            display: flex; align-items: center; justify-content: center;
            padding: 1rem; border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 0.75rem; background-color: rgba(255, 255, 255, 0.05);
            cursor: pointer; transition: all 0.2s ease-in-out; min-height: 80px;
            text-align: center; font-weight: 500;
        }
        input[type="checkbox"].custom-checkbox:checked + .event-card {
            border-color: #3b82f6; background-color: rgba(59, 130, 246, 0.2);
            box-shadow: 0 0 10px rgba(59, 130, 246, 0.8);
        }
    </style>
</head>
<body class="text-white">

<nav class="bg-gray-900 shadow-lg">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex justify-between items-center py-4">
            <div class="text-xl font-bold text-white">Aavirbhav - Events</div>
            <div class="flex items-center space-x-4">
                <span class="text-gray-300">Hello, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong></span>
                <a href="../forms/logout.php" class="bg-transparent border border-gray-400 text-gray-300 px-4 py-2 rounded hover:bg-gray-700">Logout</a>
            </div>
        </div>
    </div>
</nav>

<div class="max-w-4xl mx-auto px-4 py-8">
    <h2 class="text-3xl font-bold mb-6">Register Your Team</h2>

    <form id="eventForm" method="POST" action="" class="space-y-6">
        <div class="bg-white/10 backdrop-blur-sm rounded-lg p-6">
            <label class="block text-lg font-medium mb-3">Registration Type</label>
            <select id="regType" name="type" class="w-full p-3 rounded-lg bg-white/20 border border-white/30 text-white">

                <!--<option value="individual" class="text-gray-800">Individual</option>-->
                
                <option value="team" class="text-gray-800">Team</option>
            </select>
        </div>
       <!-- <p class="text-yellow-300 mb-4 text-sm">
            Note: If you select more than 2 IT events, your registration will automatically be considered as a <strong>Team</strong>.
            The team pricing (₹1600 base + open event charges) will be applied.
        </p>-->

        <p class="text-yellow-300 mb-4 text-sm">
            Note: For now only <strong>Team</strong> events are available for registration.         
            The team pricing (₹1600 base + open event charges) will be applied.
        </p>

        <!-- IT Events -->
        <div class="bg-white/10 backdrop-blur-sm rounded-lg p-6">
            <label class="block text-lg font-medium mb-4">IT Events</label>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php
                foreach (["Web Design","It Manager","IT Quiz","Treasure Hunt","Coding","Photography","Videography","Gaming"] as $ev) {
                    echo '<label>
                        <input type="checkbox" name="events[]" value="'.$ev.'" data-price="'.$eventPrices[$ev].'" class="event-checkbox it-event custom-checkbox">
                        <div class="event-card">'.$ev.' (₹'.$eventPrices[$ev].')</div>
                    </label>';
                }
                ?>
            </div>
        </div>

        <!-- Open Events -->
        <div class="bg-white/10 backdrop-blur-sm rounded-lg p-6">
            <label class="block text-lg font-medium mb-4">Open Events</label>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <label>
                    <input type="checkbox" name="events[]" value="Tug of War" data-price="800" class="event-checkbox open-event custom-checkbox">
                    <div class="event-card">Tug of War (₹800)</div>
                </label>
                <label>
                    <input type="checkbox" name="events[]" value="Corporate Walk" data-price="1000" class="event-checkbox open-event custom-checkbox">
                    <div class="event-card">Corporate Walk (₹1000)</div>
                </label>
            </div>
        </div>

        <div id="participantFields" class="space-y-4"></div>

        <!-- Total Price -->
        <div class="bg-white/10 backdrop-blur-sm rounded-lg p-6">
            <h3 class="text-2xl font-bold">Total Price: ₹<span id="totalPrice">0</span></h3>
        </div>

        <!-- Reminder Note -->
        <!--<p class="text-yellow-300 text-sm">
            Reminder: Selecting more than 2 IT events automatically switches your registration to <strong>Team</strong> and applies team pricing.
        </p>-->

        <!-- Submit Button -->
        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 px-6 rounded-lg">
            Proceed to Payment
        </button>
    </form>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const participantFields = document.getElementById("participantFields");
    const regType = document.getElementById("regType");
    const totalPriceEl = document.getElementById("totalPrice");
    const itCheckboxes = document.querySelectorAll(".it-event");
    const openCheckboxes = document.querySelectorAll(".open-event");

    function participantInput(eventName, i) {
        let safeName = eventName.replace(/\s+/g, '_').replace(/[^a-zA-Z0-9_]/g, '');
        return `
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label><strong>Participant ${i}</strong><br>Name *</label>
                    <input type="text" name="participants[${safeName}][${i}][name]" required class="w-full p-2 rounded bg-white/20 border border-white/30 text-white">
                </div>
                <div>
                    <label><br>Phone *</label>
                    <input type="tel" name="participants[${safeName}][${i}][phone]" required class="w-full p-2 rounded bg-white/20 border border-white/30 text-white">
                </div>
            </div>`;
    }

    function wrapEvent(name, inputs, typeText) {
        return `<div class="bg-white/10 backdrop-blur-sm rounded-lg p-6">
                    <h3 class="text-xl font-bold mb-4">${name} - Participants</h3>
                    <p class="text-gray-300 mb-4">${typeText}</p>
                    ${inputs}
                </div>`;
    }

    function updateParticipantsAndPrice() {
        participantFields.innerHTML = "";
        let totalPrice = 0;

        const itSelected = Array.from(itCheckboxes).filter(cb => cb.checked);
        const openSelected = Array.from(openCheckboxes).filter(cb => cb.checked);

        // Force team if more than 2 IT events
        let isTeam = regType.value === "team" || itSelected.length > 2;

        if (isTeam) {
            totalPrice += 1600;
            if (openSelected.some(cb => cb.value === "Tug of War")) totalPrice += 800;
            if (openSelected.some(cb => cb.value === "Corporate Walk")) totalPrice += 1000;
        } else {
            itSelected.forEach(cb => totalPrice += parseInt(cb.dataset.price) || 0);
            openSelected.forEach(cb => totalPrice += parseInt(cb.dataset.price) || 0);
        }

        // Always show 2 participant fields for every selected event
        [...itSelected, ...openSelected].forEach(cb => {
            let inputs = "";
            for (let i = 1; i <= 2; i++) { 
                inputs += participantInput(cb.value, i);
            }
            participantFields.innerHTML += wrapEvent(
                cb.value,
                inputs,
                isTeam || cb.classList.contains("open-event") 
                    ? "Team registration (2 members)" 
                    : "Individual registration (2 members)"
            );
        });

        totalPriceEl.textContent = totalPrice;
    }

    document.querySelectorAll(".event-checkbox, #regType").forEach(el => {
        el.addEventListener("change", updateParticipantsAndPrice);
    });

    updateParticipantsAndPrice();
});
</script>

</body>
</html>
