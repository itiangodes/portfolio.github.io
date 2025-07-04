<?php
session_start();

// Hardcoded credentials (insecure — only for testing)
$valid_username = "employee";
$valid_password = "employee";

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if ($username === $valid_username && $password === $valid_password) {
        $_SESSION["employee_logged_in"] = true;
        $_SESSION["employee_username"] = $username;
        header("Location: employee_dashboard.php");
        exit;
    } else {
        $error = "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Employee Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-md-4">
      <h3 class="text-center mb-3">Employee Login</h3>
      <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
      <form method="POST">
        <input type="text" name="username" class="form-control mb-2" placeholder="Username" required>
        <input type="password" name="password" class="form-control mb-3" placeholder="Password" required>
        <button type="submit" name="login" class="btn btn-primary w-100">Login</button>
      </form>
    </div>
  </div>
</div>
</body>
</html>
