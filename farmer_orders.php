<?php
session_start();
require_once "config.php";

// ✅ Allow only farmers
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== 'farmer') {
    header("Location: login.php");
    exit();
}

$farmer_id = $_SESSION['user_id'];

// ✅ Fetch orders for this farmer’s products
$sql = "
    SELECT 
        o.id AS order_id,
        i.name AS product,
        i.quantity AS qty,
        u.name AS buyer,
        u.email AS email,
        o.address AS address,
        o.total AS total,
        o.status AS status,
        o.created_at AS created_at
    FROM orders o
    JOIN order_items i ON i.order_id = o.id
    JOIN products p ON i.name = p.name
    JOIN users u ON o.user_id = u.id
    WHERE p.farmer_id = ?
    ORDER BY o.id DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $farmer_id);
$stmt->execute();
$res = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Orders Received | Farm2Home</title>
    <style>
        body { font-family: Arial; background:#f9f9f9; padding:20px; }
        h2 { color:#2e7d32; text-align:center; }
        table { width:100%; border-collapse:collapse; background:white; }
        th, td { border:1px solid #ccc; padding:10px; text-align:center; }
        th { background:#2e7d32; color:white; }
        a { color:#2e7d32; text-decoration:none; font-weight:bold; }
        tr:nth-child(even){ background:#f1f8e9; }
        .status { font-weight:bold; }
        .placed { color:orange; }
        .confirmed { color:green; }
        .intransit { color:blue; }
        .delivered { color:#2e7d32; }
        .cancelled { color:red; }
    </style>
</head>
<body>

<h2>📦 Orders Received for My Products</h2>

<nav style="text-align:center; margin-bottom:20px;">
    <a href="farmer_products.php">⬅ Back to My Products</a>
</nav>

<table>
<tr>
    <th>Order ID</th>
    <th>Product</th>
    <th>Quantity</th>
    <th>Buyer</th>
    <th>Email</th>
    <th>Address</th>
    <th>Total (₹)</th>
    <th>Status</th>
    <th>Date</th>
</tr>

<?php if ($res && $res->num_rows > 0): ?>
    <?php while ($row = $res->fetch_assoc()): ?>
        <tr>
            <td>#<?= $row['order_id'] ?></td>
            <td><?= htmlspecialchars($row['product']) ?></td>
            <td><?= $row['qty'] ?></td>
            <td><?= htmlspecialchars($row['buyer']) ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td><?= htmlspecialchars($row['address']) ?></td>
            <td>₹ <?= number_format($row['total'], 2) ?></td>
            <td class="status <?= strtolower(str_replace(' ', '', $row['status'])) ?>">
                <?= $row['status'] ?>
            </td>
            <td><?= date('d-m-Y', strtotime($row['created_at'])) ?></td>
        </tr>
    <?php endwhile; ?>
<?php else: ?>
    <tr><td colspan="9">No orders received yet.</td></tr>
<?php endif; ?>
</table>

</body>
</html>
