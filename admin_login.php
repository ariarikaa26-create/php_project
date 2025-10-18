 <?php
session_start();
require_once "config.php";

// Enable error display (for debugging if needed)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// If admin is already logged in, redirect directly to panel
if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'Admin') {
    header("Location: admin_panel.php");
    exit();
}

// Handle any error messages from login_process
$error_message = "";
if (isset($_SESSION['error'])) {
    $error_message = $_SESSION['error'];
    unset($_SESSION['error']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Login | Organic Harvest</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: #f0f4f1;
      font-family: 'Segoe UI', sans-serif;
    }
    .login-container {
      max-width: 420px;
      margin: 100px auto;
      background: white;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    h3 {
      color: #2e7d32;
      text-align: center;
      margin-bottom: 20px;
    }
    .btn-success {
      background: #2e7d32;
      border: none;
    }
    .btn-success:hover {
      background: #256628;
    }
  </style>
</head>
<body>

<div class="login-container">
  <h3>🌱 Admin Login</h3>

  <?php if ($error_message): ?>
    <div class="alert alert-danger text-center py-2">
      <?php echo $error_message; ?>
    </div>
  <?php endif; ?>

  <form action="login_process.php" method="POST">
    <div class="mb-3">
      <label>Email</label>
      <input type="email" name="email" class="form-control" placeholder="Enter admin email" required>
    </div>

    <div class="mb-3">
      <label>Password</label>
      <input type="password" name="password" class="form-control" placeholder="Enter password" required>
    </div>

    <div class="mb-3">
      <label>Role</label>
      <select name="role" class="form-select" required>
        <option value="">Select Role</option>
        <option value="Admin">Admin</option>
      </select>
    </div>

    <div class="d-grid">
      <button type="submit" name="login" class="btn btn-success">Login</button>
    </div>

    <div class="text-center mt-3">
      <a href="login.php" class="text-success text-decoration-none">← Back to User Login</a>
    </div>
  </form>
</div>

</body>
</html>
