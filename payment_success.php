<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "config.php";

// ✅ Ensure login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// ✅ Validate order_id
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
if ($order_id <= 0) {
    echo "<h3 style='color:red;text-align:center;'>❌ Invalid Order ID!</h3>";
    exit();
}

// ✅ Fetch order details
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $order_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if (!$order) {
    echo "<h3 style='color:red;text-align:center;'>❌ Order not found or access denied!</h3>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Successful | Farm2Home</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #e8f5e9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .success-box {
            background: white;
            padding: 40px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            max-width: 500px;
            width: 100%;
        }
        h2 {
            color: #2e7d32;
            margin-bottom: 10px;
        }
        p {
            color: #333;
            font-size: 16px;
            margin: 6px 0;
        }
        a {
            display: inline-block;
            margin-top: 15px;
            background: #2e7d32;
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
        }
        a:hover {
            background: #1b5e20;
        }
    </style>
</head>
<body>
    <div class="success-box">
        <h2>✅ Order Placed Successfully!</h2>
        <p>Thank you <b><?= htmlspecialchars($_SESSION['name']) ?></b> for shopping with <b>Farm2Home</b>.</p>
        <p><b>Order ID:</b> #<?= htmlspecialchars($order_id) ?></p>
        <p><b>Name:</b> <?= htmlspecialchars($order['customer_name'] ?? $_SESSION['name']) ?></p>
        <p><b>Payment Method:</b> <?= htmlspecialchars($order['payment_method']) ?></p>
        <p><b>Total:</b> ₹<?= number_format($order['total'], 2) ?></p>

        <!-- ✅ Invoice Button -->
        <a href="invoice.php?order_id=<?= $order_id ?>">🧾 Download Invoice</a>

        <!-- ✅ View Orders Button -->
        <a href="my_orders.php">📦 View My Orders</a>

        <!-- ✅ Back to Dashboard -->
        <a href="dashboard.php">🏠 Go to Dashboard</a>
    </div>
</body>
</html>
