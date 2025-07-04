<!DOCTYPE html>
<html>
<head>
  <title>Admin Dashboard - WKND</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <style> td { word-break: break-word; } </style>
</head>
<body class="bg-light">

<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "wknd";

session_start();
if (!isset($_SESSION["admin_logged_in"])) {
    header("Location: admin_login.php");
    exit;
}

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['create'])) {
    $stmt = $conn->prepare("INSERT INTO orders (customer_name, drink_name, size, quantity, price, notes) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssids", $_POST['customer_name'], $_POST['drink_name'], $_POST['size'], $_POST['quantity'], $_POST['price'], $_POST['notes']);
    $stmt->execute();
    header("Location: admin_dashboard.php");
    exit;
}
if (isset($_POST['update'])) {
    $stmt = $conn->prepare("UPDATE orders SET customer_name=?, drink_name=?, size=?, quantity=?, price=?, notes=? WHERE id=?");
    $stmt->bind_param("sssidsi", $_POST['customer_name'], $_POST['drink_name'], $_POST['size'], $_POST['quantity'], $_POST['price'], $_POST['notes'], $_POST['id']);
    $stmt->execute();
    header("Location: admin_dashboard.php");
    exit;
}
if (isset($_POST['delete'])) {
    $stmt = $conn->prepare("DELETE FROM orders WHERE id=?");
    $stmt->bind_param("i", $_POST['id']);
    $stmt->execute();
    header("Location: admin_dashboard.php");
    exit;
}

$totalOrders = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
$totalRevenue = $conn->query("SELECT SUM(price * quantity) as total FROM orders")->fetch_assoc()['total'] ?? 0;
$topDrinks = $conn->query("SELECT drink_name, SUM(quantity) AS total_qty FROM orders GROUP BY drink_name ORDER BY total_qty DESC LIMIT 5");
$recentOrdersRaw = $conn->query("SELECT * FROM orders ORDER BY order_time DESC LIMIT 50");
?>

<div class="container my-5">
  <h2 class="mb-4">📊 WKND Order Dashboard</h2>

  <div class="row g-4 mb-4">
    <div class="col-md-4">
      <div class="card bg-primary text-white text-center">
        <div class="card-body"><h5>Total Orders</h5><h2><?= $totalOrders ?></h2></div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card bg-success text-white text-center">
        <div class="card-body"><h5>Total Revenue</h5><h2>₱<?= number_format($totalRevenue, 2) ?></h2></div>
      </div>
    </div>
  </div>

  <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#createModal">➕ Add Order</button>

  <div class="row">
    <div class="col-md-6">
      <h4>🍹 Top 5 Most Ordered Drinks</h4>
      <ul class="list-group mb-4">
        <?php while ($row = $topDrinks->fetch_assoc()): ?>
          <li class="list-group-item d-flex justify-content-between">
            <strong><?= $row['drink_name'] ?></strong>
            <span><?= $row['total_qty'] ?> cups</span>
          </li>
        <?php endwhile; ?>
      </ul>
    </div>
  </div>

  <h4>🕒 Recent Orders</h4>
  <div class="table-responsive">
    <table class="table table-striped">
      <thead>
        <tr>
          <th>Customer</th><th>Drink</th><th>Size</th><th>Qty</th><th>Notes</th><th>₱ Total</th><th>Time</th><th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $grouped = [];
        while ($row = $recentOrdersRaw->fetch_assoc()) {
            $grouped[$row['customer_name'] . $row['order_time']][] = $row;
        }

        foreach ($grouped as $groupRows):
            $first = true;
            foreach ($groupRows as $row):
        ?>
        <tr>
          <td><?= $first ? htmlspecialchars($row['customer_name']) : '' ?></td>
          <td><?= $row['drink_name'] ?></td>
          <td><?= $row['size'] ?></td>
          <td><?= $row['quantity'] ?></td>
          <td><?= $first ? htmlspecialchars($row['notes']) : '' ?></td>
          <td>₱<?= number_format($row['price'] * $row['quantity'], 2) ?></td>
          <td><?= $first ? $row['order_time'] : '' ?></td>
          <td>
            <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['id'] ?>">Edit</button>
            <form method="POST" style="display:inline;">
              <input type="hidden" name="id" value="<?= $row['id'] ?>">
              <button type="submit" name="delete" onclick="return confirm('Delete this order?');" class="btn btn-danger btn-sm">Delete</button>
            </form>
          </td>
        </tr>
        <?php $first = false; endforeach; endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="modal fade" id="createModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Add New Order</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <input name="customer_name" class="form-control mb-2" placeholder="Customer Name" required>
        <input name="drink_name" class="form-control mb-2" placeholder="Drink Name" required>
        <input name="size" class="form-control mb-2" placeholder="Size" required>
        <input type="number" name="quantity" class="form-control mb-2" placeholder="Quantity" required>
        <input type="number" name="price" class="form-control mb-2" placeholder="Price" step="0.01" required>
        <input name="notes" class="form-control mb-2" placeholder="Notes">
      </div>
      <div class="modal-footer"><button type="submit" name="create" class="btn btn-primary">Save</button></div>
    </form>
  </div>
</div>

<?php
$recentOrdersModal = $conn->query("SELECT * FROM orders ORDER BY order_time DESC LIMIT 50");
while ($row = $recentOrdersModal->fetch_assoc()):
?>
<div class="modal fade" id="editModal<?= $row['id'] ?>" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <input type="hidden" name="id" value="<?= $row['id'] ?>">
      <div class="modal-header"><h5 class="modal-title">Edit Order #<?= $row['id'] ?></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <input name="customer_name" class="form-control mb-2" value="<?= htmlspecialchars($row['customer_name']) ?>" required>
        <input name="drink_name" class="form-control mb-2" value="<?= $row['drink_name'] ?>" required>
        <input name="size" class="form-control mb-2" value="<?= $row['size'] ?>" required>
        <input type="number" name="quantity" class="form-control mb-2" value="<?= $row['quantity'] ?>" required>
        <input type="number" name="price" class="form-control mb-2" value="<?= $row['price'] ?>" step="0.01" required>
        <input name="notes" class="form-control mb-2" value="<?= htmlspecialchars($row['notes']) ?>">
      </div>
      <div class="modal-footer"><button type="submit" name="update" class="btn btn-primary">Update</button></div>
    </form>
  </div>
</div>
<?php endwhile; ?>

<div class="text-center my-4"><a href="admin_logout.php" class="btn btn-danger btn-lg">Logout</a></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>
