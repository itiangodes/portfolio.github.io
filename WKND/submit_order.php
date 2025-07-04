<?php
$host = "localhost";     // or 127.0.0.1
$user = "root";          // change if different
$password = "";          // your DB password
$database = "wknd";      // your database name

$conn = new mysqli($host, $user, $password, $database);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Get JSON from JavaScript
$data = json_decode(file_get_contents("php://input"), true);

$customerName = $conn->real_escape_string($data['name']);
$notes = $conn->real_escape_string($data['notes']);
$items = $data['items'];  // Array of order items

foreach ($items as $item) {
  $drink = $conn->real_escape_string($item['name']);
  $size = $conn->real_escape_string($item['size']);
  $price = floatval($item['price']);
  $quantity = intval($item['quantity']);

  $stmt = $conn->prepare("INSERT INTO orders (customer_name, drink_name, size, quantity, price, notes) VALUES (?, ?, ?, ?, ?, ?)");
  $stmt->bind_param("sssids", $customerName, $drink, $size, $quantity, $price, $notes);
  $stmt->execute();
}

echo json_encode(["success" => true, "message" => "Order submitted successfully."]);

$conn->close();
?>
