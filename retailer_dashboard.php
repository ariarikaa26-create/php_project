<?php
session_start();
require_once "config.php";

// ✅ Allow only Retailer login
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== 'retailer') {
    header("Location: login.php");
    exit();
}

$retailer_id = (int)$_SESSION['user_id'];
$msg = "";

// ✅ Handle Buy Button Click
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buy'])) {
    $product_id = (int)$_POST['product_id'];
    $qty = max(1, intval($_POST['quantity']));

    $stmt = $conn->prepare("INSERT INTO retailer_orders (retailer_id, product_id, quantity, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iii", $retailer_id, $product_id, $qty);
    if ($stmt->execute()) {
        $msg = "✅ Order placed successfully!";
    } else {
        $msg = "❌ Error placing order: " . $conn->error;
    }
    $stmt->close();
}

// ✅ Fetch all Trader Orders (with product details)
$orders = $conn->query("
    SELECT 
        to_.id,
        p.id AS product_id,
        u.name AS trader_name,
        p.name AS product_name,
        p.category,
        p.price,
        to_.quantity
    FROM trader_orders to_
    JOIN products p ON to_.product_id = p.id
    JOIN users u ON to_.trader_id = u.id
    ORDER BY to_.id DESC
");

// ✅ Fetch Retailer’s own orders (My Purchases)
$purchases = $conn->query("
    SELECT 
        ro.id,
        p.name AS product_name,
        p.price,
        ro.quantity,
        t.name AS trader_name
    FROM retailer_orders ro
    JOIN products p ON ro.product_id = p.id
    JOIN trader_orders to_ ON ro.product_id = to_.product_id
    JOIN users t ON to_.trader_id = t.id
    WHERE ro.retailer_id = $retailer_id
    GROUP BY ro.id
    ORDER BY ro.id DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Retailer Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; background:#f9f9f9; padding:20px; margin:0; }
        table { width:100%; border-collapse:collapse; background:#fff; }
        th, td { border:1px solid #ddd; padding:8px; text-align:center; }
        th { background:#2e7d32; color:white; }
        input[type=number] { width:60px; text-align:center; }
        button { background:#2e7d32; color:white; border:none; padding:5px 10px; border-radius:5px; cursor:pointer; }
        button:hover { background:#1b5e20; }
        .msg { color:green; font-weight:bold; margin:10px 0; text-align:center; }
        .container { display:flex; gap:20px; margin-top:20px; }
        .left, .right { background:white; padding:15px; border-radius:8px; box-shadow:0 2px 6px rgba(0,0,0,0.1); }
        .left { flex:2; }
        .right { flex:1; }
        h3 { color:#1b5e20; margin-bottom:10px; }
        nav {
            background:#2e7d32;
            color:white;
            padding:12px 25px;
            display:flex;
            justify-content:space-between;
            align-items:center;
            border-radius:6px;
        }
        nav a {
            color:white;
            text-decoration:none;
            margin:0 12px;
            font-weight:bold;
        }
        nav a:hover { text-decoration:underline; }
    </style>
</head>
<body>

<!-- ✅ Navigation Bar -->
<nav>
  <div><strong>🏪 Retailer Dashboard</strong></div>
  <div>
    <a href="dashboard.php">🏠 Home</a>
    <a href="products.php">🛒 Browse Products</a>
    <a href="logout.php">🚪 Logout</a>
  </div>
</nav>

<!-- ✅ Success or Error Message -->
<?php if(!empty($msg)) echo "<p class='msg'>$msg</p>"; ?>

<div class="container">
    <!-- 📦 Trader Orders Section -->
    <div class="left">
        <h3>📦 Available Trader Orders</h3>
        <table>
            <tr>
                <th>ID</th>
                <th>Trader</th>
                <th>Product</th>
                <th>Category</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Buy</th>
            </tr>
            <?php while($r = $orders->fetch_assoc()): ?>
            <tr>
                <td><?= $r['id'] ?></td>
                <td><?= htmlspecialchars($r['trader_name']) ?></td>
                <td><?= htmlspecialchars($r['product_name']) ?></td>
                <td><?= htmlspecialchars($r['category']) ?></td>
                <td>₹ <?= number_format($r['price'], 2) ?></td>
                <td><?= (int)$r['quantity'] ?></td>
                <td>
                    <form method="post">
                        <input type="hidden" name="product_id" value="<?= $r['product_id'] ?>">
                        <input type="number" name="quantity" value="1" min="1">
                        <button type="submit" name="buy">Buy</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <!-- 🛒 My Orders Section -->
    <div class="right">
        <h3>🛒 My Orders</h3>
        <table>
            <tr>
                <th>Product</th>
                <th>Trader</th>
                <th>Qty</th>
                <th>Price (₹)</th>
            </tr>
            <?php while($p = $purchases->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($p['product_name']) ?></td>
                <td><?= htmlspecialchars($p['trader_name']) ?></td>
                <td><?= (int)$p['quantity'] ?></td>
                <td>₹ <?= number_format($p['price'], 2) ?></td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>

<script>
// ✅ Auto-hide success message after 3 seconds
setTimeout(() => {
  document.querySelector('.msg')?.remove();
}, 3000);
</script>

</body>
</html>
