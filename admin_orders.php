<?php
session_start();
require_once "config.php";

// Only admin access
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin'){
    header("Location: login.php");
    exit;
}

// Update Status
if (isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE orders SET status=? WHERE id=?");
    $stmt->bind_param("si", $status, $order_id);
    $stmt->execute();
    $stmt->close();

    header("Location: admin_orders.php?updated=1");
    exit;
}

// Fetch Orders
$orders = $conn->query("SELECT o.*, u.name as customer 
                        FROM orders o 
                        JOIN users u ON o.user_id=u.id 
                        ORDER BY o.id DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Orders - Admin</title>
    <style>
        body{font-family:Arial;background:#f9f9f9;padding:20px;}
        table{width:100%;border-collapse:collapse;background:#fff;}
        th,td{border:1px solid #ddd;padding:10px;text-align:center;}
        th{background:#2e7d32;color:white;}
        select,button{padding:5px;}
    </style>
</head>
<body>
<h2>🛒 Manage Orders</h2>
<?php if(isset($_GET['updated'])) echo "<p style='color:green;'>Order status updated!</p>"; ?>
<table>
<tr><th>ID</th><th>Customer</th><th>Address</th><th>Status</th><th>Update</th></tr>
<?php while($o=$orders->fetch_assoc()): ?>
<tr>
  <td><?= $o['id'] ?></td>
  <td><?= htmlspecialchars($o['customer']) ?></td>
  <td><?= htmlspecialchars($o['address']) ?></td>
  <td><?= htmlspecialchars($o['status']) ?></td>
  <td>
    <form method="post">
      <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
      <select name="status">
        <option value="Placed" <?= $o['status']=="Placed"?"selected":"" ?>>Placed</option>
        <option value="Confirmed" <?= $o['status']=="Confirmed"?"selected":"" ?>>Confirmed</option>
        <option value="In Transit" <?= $o['status']=="In Transit"?"selected":"" ?>>In Transit</option>
        <option value="Out for Delivery" <?= $o['status']=="Out for Delivery"?"selected":"" ?>>Out for Delivery</option>
        <option value="Delivered" <?= $o['status']=="Delivered"?"selected":"" ?>>Delivered</option>
      </select>
      <button type="submit" name="update_status">Update</button>
    </form>
  </td>
</tr>
<?php endwhile; ?>
</table>
</body>
</html>
