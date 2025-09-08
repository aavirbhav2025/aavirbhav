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
    $__selCount = is_array($events) ? count($events) : 0;
if ($type !== "team" && $__selCount > 2) { $type = "team"; }
if ($type === "team") {
        $amount = 1600;
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

<style>
.toast-popup {
    position: fixed;
    z-index: 9999;
    background: #323232;
    color: white;
    padding: 12px 18px;
    border-radius: 8px;
    font-size: 14px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    opacity: 0;
    transform: translateX(100%);
    transition: transform 0.5s ease, opacity 0.5s ease;
    max-width: 250px;
    word-wrap: break-word;
}
.toast-popup.show {
    opacity: 1;
    transform: translateX(0);
}
@media (max-width: 600px) {
    .toast-popup {
        bottom: 20px;
        right: 50%;
        transform: translateX(50%) translateY(100%);
    }
    .toast-popup.show {
        transform: translateX(50%) translateY(0);
    }
}
@media (min-width: 601px) {
    .toast-popup {
        top: 20px;
        right: 20px;
    }
}
</style>
<div id="toastContainer"></div>


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
                        <?php if(isset($_POST["type"]) && $_POST["type"] === "team") { echo htmlspecialchars($event); } else { echo htmlspecialchars($event) . " (₹" . $price . ")"; } ?>
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
document.addEventListener('DOMContentLoaded', function() {

    function showToast(message) {
        const container = document.getElementById('toastContainer');
        const toast = document.createElement('div');
        toast.className = 'toast-popup';
        toast.textContent = message;
        container.appendChild(toast);
        // Force reflow to trigger animation
        void toast.offsetWidth;
        toast.classList.add('show');
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 500);
        }, 4000);
    }

    const typeInputs = document.querySelectorAll('input[name="type"], select[name="type"]');
    const totalPriceEl = document.getElementById('totalPrice');
    let lastTotal = 0;

    function animateTotal(newTotal) {
        const duration = 400;
        const startTime = performance.now();
        const startValue = lastTotal;
        const diff = newTotal - startValue;

        function step(currentTime) {
            const progress = Math.min((currentTime - startTime) / duration, 1);
            const value = Math.floor(startValue + diff * progress);
            if (totalPriceEl) totalPriceEl.textContent = value;
            if (progress < 1) {
                requestAnimationFrame(step);
            } else {
                lastTotal = newTotal;
            }
        }
        requestAnimationFrame(step);
    }

    function setType(value) {
        // Handle radio inputs
        const radios = document.querySelectorAll('input[name="type"]');
        let set = false;
        radios.forEach(r => {
            if (r.value === value) {
                r.checked = true;
                set = true;
            } else if (value === 'team') {
                r.checked = (r.value === 'team');
            }
        });
        // Handle select input
        const select = document.querySelector('select[name="type"]');
        if (select) {
            select.value = value;
            set = true;
        }
        return set;
    }

    function reorderEventsForTeamMode() {
        const eventCheckboxes = document.querySelectorAll('.event-checkbox');
        if (!eventCheckboxes.length) return;

        const specialEvents = [];
        const normalEvents = [];

        eventCheckboxes.forEach(cb => {
            const formCheck = cb.closest('.form-check');
            const label = formCheck.querySelector('.form-check-label');
            const name = (label.textContent || '').replace(/\(₹.*?\)/, '').trim().toLowerCase();
            if (name === 'tug of war' || name === 'corporate walk') {
                specialEvents.push(formCheck);
            } else {
                normalEvents.push(formCheck);
            }
        });

        // Build container safely
        const container = eventCheckboxes[0].closest('div').parentElement;
        // Clear container
        container.innerHTML = '';

        if (specialEvents.length) {
            const heading = document.createElement('h6');
            heading.textContent = 'Special Events (Extra Charges)';
            heading.style.marginTop = '10px';
            heading.style.fontWeight = 'bold';
            container.appendChild(heading);
            specialEvents.forEach(el => container.appendChild(el));
        }

        if (normalEvents.length) {
            const heading = document.createElement('h6');
            heading.textContent = 'Other Events (Included in Base Fee)';
            heading.style.marginTop = '10px';
            heading.style.fontWeight = 'bold';
            container.appendChild(heading);
            normalEvents.forEach(el => container.appendChild(el));
        }
    }

    function updateLabelsAndTotal() {
        const typeEl = document.querySelector('input[name="type"]:checked') || document.querySelector('select[name="type"]');
        const selectedType = typeEl ? (typeEl.value || 'individual') : 'individual';

        const eventCheckboxes = document.querySelectorAll('.event-checkbox');
        let total = 0;

        // Auto-switch to team if > 2 events selected in individual mode
        const selectedCount = Array.from(document.querySelectorAll('.event-checkbox:checked')).length;
        if (selectedType !== 'team' && selectedCount > 2) {
            showToast("You selected more than 2 events, so we switched you to Team pricing.");
    
            setType('team');
            // Re-run with team selected
            updateLabelsAndTotal();
            return;
        }

        if (selectedType === 'team') {
            total = 1600; // Base for team
            reorderEventsForTeamMode();
        }

        eventCheckboxes.forEach(cb => {
            const formCheck = cb.closest('.form-check');
            const label = formCheck.querySelector('.form-check-label');
            const price = parseInt(cb.dataset.price || 0);
            const eventName = (label.textContent || '').replace(/\(₹.*?\)/, '').trim();
            const lowerName = eventName.toLowerCase();

            if (selectedType === 'team') {
                if (lowerName === 'tug of war' || lowerName === 'corporate walk') {
                    label.textContent = eventName + " (₹" + price + ")";
                    if (cb.checked) total += price;
                } else {
                    label.textContent = eventName;
                }
            } else { // individual
                label.textContent = eventName + " (₹" + price + ")";
                if (cb.checked) total += price;
            }
        });

        animateTotal(total);
    }

    typeInputs.forEach(input => {
        input.addEventListener('change', function() {
            location.reload();
        });
    });
    document.querySelectorAll('.event-checkbox').forEach(cb => {
        cb.addEventListener('change', updateLabelsAndTotal);
    });

    // Initial run
    updateLabelsAndTotal();
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {

    function showToast(message) {
        const container = document.getElementById('toastContainer');
        const toast = document.createElement('div');
        toast.className = 'toast-popup';
        toast.textContent = message;
        container.appendChild(toast);
        // Force reflow to trigger animation
        void toast.offsetWidth;
        toast.classList.add('show');
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 500);
        }, 4000);
    }

    const typeInputs = document.querySelectorAll('input[name="type"], select[name="type"]');
    const totalPriceEl = document.getElementById('totalPrice');
    let lastTotal = 0;

    function animateTotal(newTotal) {
        const duration = 400;
        const startTime = performance.now();
        const startValue = lastTotal;
        const diff = newTotal - startValue;

        function step(currentTime) {
            const progress = Math.min((currentTime - startTime) / duration, 1);
            const value = Math.floor(startValue + diff * progress);
            if (totalPriceEl) totalPriceEl.textContent = value;
            if (progress < 1) {
                requestAnimationFrame(step);
            } else {
                lastTotal = newTotal;
            }
        }
        requestAnimationFrame(step);
    }

    function setType(value) {
        // Handle radio inputs
        const radios = document.querySelectorAll('input[name="type"]');
        let set = false;
        radios.forEach(r => {
            if (r.value === value) {
                r.checked = true;
                set = true;
            } else if (value === 'team') {
                r.checked = (r.value === 'team');
            }
        });
        // Handle select input
        const select = document.querySelector('select[name="type"]');
        if (select) {
            select.value = value;
            set = true;
        }
        return set;
    }

    function reorderEventsForTeamMode() {
        const eventCheckboxes = document.querySelectorAll('.event-checkbox');
        if (!eventCheckboxes.length) return;

        const specialEvents = [];
        const normalEvents = [];

        eventCheckboxes.forEach(cb => {
            const formCheck = cb.closest('.form-check');
            const label = formCheck.querySelector('.form-check-label');
            const name = (label.textContent || '').replace(/\(₹.*?\)/, '').trim().toLowerCase();
            if (name === 'tug of war' || name === 'corporate walk') {
                specialEvents.push(formCheck);
            } else {
                normalEvents.push(formCheck);
            }
        });

        // Build container safely
        const container = eventCheckboxes[0].closest('div').parentElement;
        // Clear container
        container.innerHTML = '';

        if (specialEvents.length) {
            const heading = document.createElement('h6');
            heading.textContent = 'Special Events (Extra Charges)';
            heading.style.marginTop = '10px';
            heading.style.fontWeight = 'bold';
            container.appendChild(heading);
            specialEvents.forEach(el => container.appendChild(el));
        }

        if (normalEvents.length) {
            const heading = document.createElement('h6');
            heading.textContent = 'Other Events (Included in Base Fee)';
            heading.style.marginTop = '10px';
            heading.style.fontWeight = 'bold';
            container.appendChild(heading);
            normalEvents.forEach(el => container.appendChild(el));
        }
    }

    function updateLabelsAndTotal() {
        const typeEl = document.querySelector('input[name="type"]:checked') || document.querySelector('select[name="type"]');
        const selectedType = typeEl ? (typeEl.value || 'individual') : 'individual';

        const eventCheckboxes = document.querySelectorAll('.event-checkbox');
        let total = 0;

        // Auto-switch to team if > 2 events selected in individual mode
        const selectedCount = Array.from(document.querySelectorAll('.event-checkbox:checked')).length;
        if (selectedType !== 'team' && selectedCount > 2) {
            showToast("You selected more than 2 events, so we switched you to Team pricing.");
    
            setType('team');
            // Re-run with team selected
            updateLabelsAndTotal();
            return;
        }

        if (selectedType === 'team') {
            total = 1600; // Base for team
            reorderEventsForTeamMode();
        }

        eventCheckboxes.forEach(cb => {
            const formCheck = cb.closest('.form-check');
            const label = formCheck.querySelector('.form-check-label');
            const price = parseInt(cb.dataset.price || 0);
            const eventName = (label.textContent || '').replace(/\(₹.*?\)/, '').trim();
            const lowerName = eventName.toLowerCase();

            if (selectedType === 'team') {
                if (lowerName === 'tug of war' || lowerName === 'corporate walk') {
                    label.textContent = eventName + " (₹" + price + ")";
                    if (cb.checked) total += price;
                } else {
                    label.textContent = eventName;
                }
            } else { // individual
                label.textContent = eventName + " (₹" + price + ")";
                if (cb.checked) total += price;
            }
        });

        animateTotal(total);
    }

    typeInputs.forEach(input => {
        input.addEventListener('change', function() {
            location.reload();
        });
    });
    document.querySelectorAll('.event-checkbox').forEach(cb => {
        cb.addEventListener('change', updateLabelsAndTotal);
    });

    // Initial run
    updateLabelsAndTotal();
});
</script>

</body>
</html>
