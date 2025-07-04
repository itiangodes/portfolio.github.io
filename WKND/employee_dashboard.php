<!DOCTYPE html>
<html>
<head>
  <title>Employee Dashboard - WKND</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style> td { word-break: break-word; } </style>
</head>
<body class="bg-light">

<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "wknd";

session_start();
if (!isset($_SESSION["employee_logged_in"])) {
    header("Location: employee_login.php");
    exit;
}

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Mark order as completed
if (isset($_POST['complete'])) {
    $stmt = $conn->prepare("UPDATE orders SET status='completed' WHERE customer_name=? AND order_time=?");
    $stmt->bind_param("ss", $_POST['customer_name'], $_POST['order_time']);
    $stmt->execute();
    header("Location: employee_dashboard.php");
    exit;
}

// Get orders
$incoming = $conn->query("SELECT * FROM orders WHERE status='pending' ORDER BY customer_name, order_time DESC");
$outgoing = $conn->query("SELECT * FROM orders WHERE status='completed' ORDER BY customer_name, order_time DESC");
?>

<div class="container my-5">
  <h2 class="mb-4">👩‍🍳 Kitchen Dashboard</h2>

  <div class="row mb-5">
    <div class="col-md-12">
      <h4 class="text-primary">📥 Incoming Orders</h4>
      <div class="table-responsive">
        <table class="table table-striped">
          <thead>
            <tr>
              <th>Customer</th><th>Drink</th><th>Size</th><th>Qty</th><th>Notes</th><th>₱ Total</th><th>Time</th><th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $lastCustomer = '';
            $lastTime = '';
            while ($row = $incoming->fetch_assoc()):
              $isNewGroup = $row['customer_name'] !== $lastCustomer || $row['order_time'] !== $lastTime;
            ?>
            <tr>
              <td><?= $isNewGroup ? htmlspecialchars($row['customer_name']) : '' ?></td>
              <td><?= $row['drink_name'] ?></td>
              <td><?= $row['size'] ?></td>
              <td><?= $row['quantity'] ?></td>
              <td><?= $isNewGroup ? htmlspecialchars($row['notes']) : '' ?></td>
              <td>₱<?= number_format($row['price'] * $row['quantity'], 2) ?></td>
              <td><?= $isNewGroup ? $row['order_time'] : '' ?></td>
              <td>
                <?php if ($isNewGroup): ?>
                  <form method="POST">
                    <input type="hidden" name="customer_name" value="<?= htmlspecialchars($row['customer_name']) ?>">
                    <input type="hidden" name="order_time" value="<?= $row['order_time'] ?>">
                    <button type="submit" name="complete" class="btn btn-success btn-sm">Mark Completed</button>
                  </form>
                <?php endif; ?>
              </td>
            </tr>
            <?php
              $lastCustomer = $row['customer_name'];
              $lastTime = $row['order_time'];
            endwhile;
            ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-md-12">
      <h4 class="text-secondary">📤 Completed Orders</h4>
      <div class="table-responsive">
        <table class="table table-bordered table-light">
          <thead>
            <tr>
              <th>Customer</th><th>Drink</th><th>Size</th><th>Qty</th><th>Notes</th><th>₱ Total</th><th>Time</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $lastCustomer = '';
            $lastTime = '';
            while ($row = $outgoing->fetch_assoc()):
              $isNewGroup = $row['customer_name'] !== $lastCustomer || $row['order_time'] !== $lastTime;
            ?>
            <tr>
              <td><?= $isNewGroup ? htmlspecialchars($row['customer_name']) : '' ?></td>
              <td><?= $row['drink_name'] ?></td>
              <td><?= $row['size'] ?></td>
              <td><?= $row['quantity'] ?></td>
              <td><?= $isNewGroup ? htmlspecialchars($row['notes']) : '' ?></td>
              <td>₱<?= number_format($row['price'] * $row['quantity'], 2) ?></td>
              <td><?= $isNewGroup ? $row['order_time'] : '' ?></td>
            </tr>
            <?php
              $lastCustomer = $row['customer_name'];
              $lastTime = $row['order_time'];
            endwhile;
            ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="text-center my-4"><a href="employee_logout.php" class="btn btn-danger btn-lg">Logout</a></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>
