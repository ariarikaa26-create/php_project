<?php
session_start();
require_once "config.php";

if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== 'retailer') {
    header("Location: login.php");
    exit();
}

$retailer_id = $_SESSION['user_id'];
$res = $conn->query("
    SELECT ro.id, t.name AS trader, ro.product_id, ro.quantity, ro.total, ro.status, ro.created_at
    FROM retailer_orders ro
    JOIN users t ON ro.trader_id = t.id
    WHERE ro.retailer_id = $retailer_id
    ORDER BY ro.id DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Orders - Retailer</title>
    <style>
        body { font-family: Arial; background:#f9f9f9; padding:20px; }
        h2 { color:#2e7d32; text-align:center; }
        table { width:100%; border-collapse:collapse; background:white; }
        th, td { border:1px solid #ddd; padding:10px; text-align:center; }
        th { background:#2e7d32; color:white; }
    </style>
</head>
<body>
<h2>📦 My Orders</h2>
<table>
<tr><th>Order ID</th><th>Trader</th><th>Product ID</th><th>Quantity</th><th>Total (₹)</th><th>Status</th><th>Date</th></tr>
<?php while($row = $res->fetch_assoc()): ?>
<tr>
    <td><?= $row['id'] ?></td>
    <td><?= $row['trader'] ?></td>
    <td><?= $row['product_id'] ?></td>
    <td><?= $row['quantity'] ?></td>
    <td>₹<?= number_format($row['total'], 2) ?></td>
    <td><?= $row['status'] ?></td>
    <td><?= $row['created_at'] ?></td>
</tr>
<?php endwhile; ?>
</table>

<p style="text-align:center;margin-top:20px;">
    <a href="retailer_dashboard.php" style="color:#2e7d32;text-decoration:none;font-weight:bold;">⬅ Back to Dashboard</a>
</p>
</body>
</html>
