<?php
session_start();
require_once "config.php";

// ✅ Only Consumer can access
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== 'consumer') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// ✅ Fetch all orders for this user
$res = $conn->query("SELECT * FROM orders WHERE user_id = $user_id ORDER BY id DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Orders</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f9f9f9;
            padding: 20px;
        }
        h2 {
            color: #2e7d32;
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            margin-top: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: center;
        }
        th {
            background: #2e7d32;
            color: white;
        }
        .cancel-btn, .track-btn {
            padding: 5px 10px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 13px;
        }
        .cancel-btn {
            background: red;
            color: white;
        }
        .track-btn {
            background: #2e7d32;
            color: white;
        }
        .msg {
            background: #e8f5e9;
            border-left: 5px solid #2e7d32;
            padding: 10px;
            margin: 10px 0;
            color: #2e7d32;
            font-weight: bold;
            text-align: center;
        }
    </style>
</head>
<body>

<h2>📦 My Orders</h2>

<!-- ✅ Success/Failure Messages -->
<?php if (isset($_GET['msg'])): ?>
    <?php if ($_GET['msg'] == 'cancel_success'): ?>
        <div class="msg">✅ Order Cancelled Successfully!</div>
    <?php elseif ($_GET['msg'] == 'cancel_failed'): ?>
        <div class="msg" style="background:#ffebee;color:red;border-color:red;">❌ Unable to Cancel Order (Maybe Already Shipped)</div>
    <?php endif; ?>
<?php endif; ?>

<table>
    <tr>
        <th>Order ID</th>
        <th>Total</th>
        <th>Status</th>
        <th>Date</th>
        <th>Action</th>
    </tr>

    <?php if ($res->num_rows > 0): ?>
        <?php while($row = $res->fetch_assoc()): ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td>₹ <?= number_format($row['total'],2) ?></td>
            <td><?= htmlspecialchars($row['status']) ?></td>
            <td><?= $row['created_at'] ?></td>
            <td>
                <?php if ($row['status'] == 'Placed'): ?>
                    <a href="cancel_order.php?id=<?= $row['id'] ?>" class="cancel-btn" onclick="return confirm('Are you sure you want to cancel this order?')">❌ Cancel</a>
                <?php endif; ?>

                <a href="track_order.php?id=<?= $row['id'] ?>" class="track-btn">🚚 Track</a>
            </td>
        </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr><td colspan="5">No orders found.</td></tr>
    <?php endif; ?>
</table>

<p style="text-align:center;margin-top:20px;">
    <a href="products.php" style="text-decoration:none;color:#2e7d32;font-weight:bold;">⬅ Back to Products</a>
</p>

</body>
</html>
