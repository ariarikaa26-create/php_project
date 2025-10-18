<?php
session_start();
require_once "config.php";

if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== 'consumer') {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: my_orders.php");
    exit();
}

$order_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

// Fetch order details
$stmt = $conn->prepare("SELECT id, status, total, created_at FROM orders WHERE id=? AND user_id=?");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows == 0) {
    die("Invalid order or access denied.");
}
$order = $res->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Track Order #<?= $order['id'] ?></title>
    <style>
        body {
            font-family: Arial;
            background: #f9f9f9;
            padding: 20px;
        }
        h2 {
            text-align: center;
            color: #2e7d32;
        }
        .tracker {
            display: flex;
            justify-content: space-between;
            margin: 50px auto;
            max-width: 600px;
            position: relative;
        }
        .step {
            text-align: center;
            width: 20%;
            position: relative;
        }
        .circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #ccc;
            margin: 0 auto 10px;
            line-height: 40px;
            color: white;
            font-weight: bold;
        }
        .active .circle {
            background: #2e7d32;
        }
        .line {
            position: absolute;
            height: 5px;
            width: 100%;
            background: #ccc;
            top: 18px;
            left: -50%;
            z-index: -1;
        }
        .active .line {
            background: #2e7d32;
        }
        .step:first-child .line {
            display: none;
        }
        p {
            font-weight: bold;
            font-size: 14px;
        }
        .back {
            display: block;
            text-align: center;
            margin-top: 30px;
            color: #2e7d32;
            font-weight: bold;
            text-decoration: none;
        }
    </style>
</head>
<body>
<h2>🚚 Tracking Order #<?= $order['id'] ?></h2>
<p style="text-align:center;">Current Status: <b><?= htmlspecialchars($order['status']); ?></b></p>

<?php
$statuses = ["Placed", "Confirmed", "In Transit", "Out for Delivery", "Delivered"];
$current = array_search($order['status'], $statuses);
?>

<div class="tracker">
    <?php foreach ($statuses as $i => $status): ?>
        <div class="step <?= ($i <= $current) ? 'active' : ''; ?>">
            <div class="circle"><?= $i + 1 ?></div>
            <p><?= $status ?></p>
            <?php if ($i != 0): ?><div class="line"></div><?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>

<a href="my_orders.php" class="back">⬅ Back to My Orders</a>

</body>
</html>
