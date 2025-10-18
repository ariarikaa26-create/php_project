<?php
include "config.php";
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$order_id = intval($_GET['id'] ?? 0);
if (!$order_id) { die("Order id required"); }

// allow admin or owner
$ok = false;
if ($_SESSION['role'] === 'admin') $ok = true;
else {
    $s = $conn->prepare("SELECT user_id FROM orders WHERE id=?");
    $s->bind_param("i", $order_id);
    $s->execute();
    $r = $s->get_result()->fetch_assoc();
    if ($r && $r['user_id'] == $_SESSION['user_id']) $ok = true;
    $s->close();
}
if (!$ok) die("Not authorized");

$ord = $conn->query("SELECT * FROM orders WHERE id=$order_id")->fetch_assoc();
$items = $conn->query("SELECT oi.*, p.name, p.image FROM order_items oi JOIN products p ON oi.product_id=p.id WHERE oi.order_id=$order_id");
?>
<!DOCTYPE html><html><head><title>Order Details</title>
<style>body{font-family:Arial;background:#f9f9f9;padding:20px;}table{width:100%;border-collapse:collapse;background:#fff;}th,td{border:1px solid #ddd;padding:8px;text-align:center;}th{background:#2e7d32;color:#fff;}img{width:70px;border-radius:6px;}</style>
</head><body>
<h2>Order #<?php echo $ord['id']; ?> Details</h2>
<p><strong>Status:</strong> <?php echo htmlspecialchars($ord['status']); ?> | <strong>Address:</strong> <?php echo htmlspecialchars($ord['address']); ?></p>
<table>
<tr><th>Image</th><th>Product</th><th>Qty</th><th>Price</th><th>Total</th></tr>
<?php $grand = 0; while($it = $items->fetch_assoc()): $total = $it['price']*$it['quantity']; $grand += $total; ?>
<tr>
  <td><img src="<?php echo htmlspecialchars($it['image']); ?>"></td>
  <td><?php echo htmlspecialchars($it['name']); ?></td>
  <td><?php echo intval($it['quantity']); ?></td>
  <td>₹ <?php echo number_format($it['price'],2); ?></td>
  <td>₹ <?php echo number_format($total,2); ?></td>
</tr>
<?php endwhile; ?>
<tr><td colspan="4" style="text-align:right;font-weight:700;">Grand Total</td><td>₹ <?php echo number_format($grand,2); ?></td></tr>
</table>
</body></html>
