<?php
session_start();
include("config.php");

// ✅ Only Admin or Trader can access this page
if (!isset($_SESSION['user_id']) || !in_array(strtolower($_SESSION['role']), ['admin','trader'])) {
    header("Location: login.php");
    exit();
}

// ✅ Update order status when form submitted
if (isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE orders SET status=? WHERE id=?");
    $stmt->bind_param("si", $status, $order_id);
    $stmt->execute();
    $stmt->close();

    header("Location: manage_orders.php?updated=1");
    exit();
}

// ✅ Fetch all orders with customer details
$orders = $conn->query("
    SELECT o.id, o.customer_name, o.address, o.status, o.created_at, u.name AS customer
    FROM orders o
    JOIN users u ON o.user_id = u.id
    ORDER BY o.id DESC
");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Orders - Organic Harvest</title>
    <style>
        body { font-family: Arial, sans-serif; background:#f9f9f9; padding:20px; }
        h1 { color:#2e7d32; text-align:center; }
        table { width:100%; border-collapse:collapse; background:white; margin-top:20px; }
        th, td { border:1px solid #ddd; padding:10px; text-align:center; }
        th { background:#2e7d32; color:white; }
        select, button { padding:6px; border-radius:4px; border:1px solid #aaa; }
        button { background:#76b852; color:white; cursor:pointer; }
        button:hover { background:#4caf50; }
        .msg { color:green; font-weight:bold; text-align:center; }
        a.back { text-decoration:none; background:#2e7d32; color:white; padding:6px 12px; border-radius:4px; }
        a.back:hover { background:#1b5e20; }
    </style>
</head>
<body>

<h1>📦 Manage Orders</h1>

<p style="text-align:center;"><a href="dashboard.php" class="back">⬅ Back to Dashboard</a></p>

<?php if (isset($_GET['updated'])) echo "<p class='msg'>✅ Order status updated successfully!</p>"; ?>

<table>
    <tr>
        <th>Order ID</th>
        <th>Customer</th>
        <th>Address</th>
        <th>Status</th>
        <th>Order Date</th>
        <th>Action</th>
    </tr>
    <?php while($o = $orders->fetch_assoc()): ?>
    <tr>
        <td><?= $o['id'] ?></td>
        <td><?= htmlspecialchars($o['customer']) ?></td>
        <td><?= htmlspecialchars($o['address']) ?></td>
        <td><?= htmlspecialchars($o['status']) ?></td>
        <td><?= $o['created_at'] ?></td>
        <td>
            <form method="post" style="display:inline-block;">
                <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                <select name="status">
                    <option value="Placed" <?= $o['status']=="Placed"?"selected":"" ?>>Placed</option>
                    <option value="Confirmed" <?= $o['status']=="Confirmed"?"selected":"" ?>>Confirmed</option>
                    <option value="In Transit" <?= $o['status']=="In Transit"?"selected":"" ?>>In Transit</option>
                    <option value="Out for Delivery" <?= $o['status']=="Out for Delivery"?"selected":"" ?>>Out for Delivery</option>
                    <option value="Delivered" <?= $o['status']=="Delivered"?"selected":"" ?>>Delivered</option>
                    <option value="Cancelled" <?= $o['status']=="Cancelled"?"selected":"" ?>>Cancelled</option>
                </select>
                <button type="submit" name="update_status">Update</button>
            </form>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

</body>
</html>
