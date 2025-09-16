<?php
session_start();
require '../forms/db.php';

// Protect admin
if (empty($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: ../forms/form.html');
    exit();
}

// Handle verify/unverify action
if (isset($_POST['verify_id'])) {
    $id = intval($_POST['verify_id']);
    $status = intval($_POST['status']); // 1 or 0
    $stmt = $conn->prepare("UPDATE registrations SET verified=? WHERE id=?");
    $stmt->bind_param("ii", $status, $id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin.php?search=" . urlencode($_GET['search'] ?? ''));
    exit();
}

// Search logic
$search = $_GET['search'] ?? '';
$query = "SELECT * FROM registrations";
if (!empty($search)) {
    $query .= " WHERE name LIKE ? OR email LIKE ?";
    $stmt = $conn->prepare($query);
    $like = "%$search%";
    $stmt->bind_param("ss", $like, $like);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($query);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Panel - Aavirbhav</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background: #1c1c1c; color: #f8f9fa; font-family: 'Cinzel', serif; }
    .navbar { background: #111; border-bottom: 2px solid #444; }
    .navbar-brand { font-weight: bold; font-size: 1.5rem; color: #d4af37 !important; }
    .receipt-card {
      background: #2a2a2a; border: 2px solid #444; border-radius: 12px;
      padding: 20px; margin-bottom: 20px;
      box-shadow: 0 0 15px rgba(212,175,55,0.5);
    }
    .receipt-card h5 { color: #d4af37; border-bottom: 1px solid #555; padding-bottom: 5px; margin-bottom: 15px; }
    .receipt-label { font-weight: bold; color: #d4af37; }
    .search-bar { max-width: 400px; margin: 20px auto; }
    table.participants-table { width: 100%; background: #333; border-radius: 6px; }
    table.participants-table th, table.participants-table td {
      border: 1px solid #555; padding: 8px; color: #fff;
    }
  </style>
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600&display=swap" rel="stylesheet">
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar navbar-dark px-3">
    <a class="navbar-brand" href="#">Aavirbhav</a>
    <div class="d-flex">
      <span class="me-3">Hello Admin</span>
      <a href="../forms/logout.php" class="btn btn-danger btn-sm">Logout</a>
    </div>
  </nav>

  <!-- Search -->
  <div class="container">
    <form method="get" class="search-bar d-flex">
      <input type="text" class="form-control me-2" name="search" placeholder="Search by Name or Email" value="<?= htmlspecialchars($search) ?>">
      <button class="btn btn-warning">Search</button>
    </form>

    <!-- Results -->
    <div class="mt-4">
      <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
          <div class="receipt-card">
            <h5>Registration Receipt</h5>
            <p><span class="receipt-label">ID:</span> <?= $row['id'] ?></p>
            <p><span class="receipt-label">Name:</span> <?= htmlspecialchars($row['name']) ?></p>
            <p><span class="receipt-label">Email:</span> <?= htmlspecialchars($row['email']) ?></p>
            <p><span class="receipt-label">Phone:</span> <?= htmlspecialchars($row['phone']) ?></p>
            <p><span class="receipt-label">Type:</span> <?= htmlspecialchars($row['type']) ?></p>
            <p><span class="receipt-label">Events:</span> <?= htmlspecialchars($row['events']) ?></p>

            <!-- Decode participants JSON -->
            <?php
            $participantsData = json_decode($row['participants'], true);
            if ($participantsData && is_array($participantsData)): ?>
              <p><span class="receipt-label">Participants:</span></p>
              <table class="participants-table">
                <thead>
                  <tr><th>#</th><th>Name</th><th>Phone</th></tr>
                </thead>
                <tbody>
                <?php 
                $i = 1;
                foreach ($participantsData as $event => $plist) {
                    foreach ($plist as $p) {
                        echo "<tr><td>$i</td><td>".htmlspecialchars($p['name'])."</td><td>".htmlspecialchars($p['phone'])."</td></tr>";
                        $i++;
                    }
                }
                ?>
                </tbody>
              </table>
            <?php else: ?>
              <p><span class="receipt-label">Participants:</span> None</p>
            <?php endif; ?>

            <p><span class="receipt-label">Amount:</span> ₹<?= $row['amount'] ?></p>
            <p><span class="receipt-label">Order ID:</span> <?= $row['order_id'] ?></p>
            <p><span class="receipt-label">Screenshot:</span><br>
              <img src="../<?= $row['screenshot'] ?>" alt="Payment Screenshot" class="img-fluid rounded" style="max-height:200px;">
            </p>
            <p><span class="receipt-label">Created At:</span> <?= $row['created_at'] ?></p>

            <!-- Verify Button -->
            <form method="post" class="mt-3">
              <input type="hidden" name="verify_id" value="<?= $row['id'] ?>">
              <?php if ($row['verified'] == 1): ?>
                <button type="submit" name="status" value="0" class="btn btn-success">Verified ✅ (Click to Unverify)</button>
              <?php else: ?>
                <button type="submit" name="status" value="1" class="btn btn-secondary">Not Verified ❌ (Click to Verify)</button>
              <?php endif; ?>
            </form>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <div class="alert alert-warning text-center">No records found</div>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
