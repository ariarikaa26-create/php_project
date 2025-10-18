<?php
session_start();
require_once "config.php";

// ✅ Correct role check (case-insensitive)
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== 'trader') {
    header("Location: login.php");
    exit();
}

$trader_id = (int)$_SESSION['user_id'];
$msg = "";

// ✅ Handle purchase (Buy button)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buy'])) {
    $pid = (int)$_POST['product_id'];
    $qty = max(1, intval($_POST['quantity']));

    $stmt = $conn->prepare("INSERT INTO trader_orders (trader_id, product_id, quantity, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iii", $trader_id, $pid, $qty);
    if ($stmt->execute()) {
        $msg = "✅ Product purchased successfully!";
    } else {
        $msg = "❌ Error purchasing product: " . $conn->error;
    }
    $stmt->close();
}

// ✅ Fetch all farmer products
$products = $conn->query("
    SELECT p.*, u.name AS farmer 
    FROM products p 
    JOIN users u ON p.farmer_id = u.id 
    ORDER BY p.id DESC
");

// ✅ Fetch trader’s own purchases
$purchases = $conn->query("
    SELECT to_.*, p.name, p.price 
    FROM trader_orders to_
    JOIN products p ON to_.product_id = p.id 
    WHERE to_.trader_id = $trader_id 
    ORDER BY to_.id DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Trader Dashboard</title>
    <style>
        body { font-family: Arial; background:#f9f9f9; padding:20px; margin:0; }
        h2 { color:#2e7d32; }
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
        h3 { color:#1b5e20; }

        /* ✅ Navigation bar */
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
        nav a:hover {
            text-decoration:underline;
        }
    </style>
</head>
<body>

<!-- ✅ Navigation Bar -->
<nav>
  <div><strong>🌱 Trader Dashboard</strong></div>
  <div>
    <a href="dashboard.php">🏠 Home</a>
    <a href="products.php">🛍️ Browse Products</a>
    <a href="logout.php">🚪 Logout</a>
  </div>
</nav>

<?php if(!empty($msg)) echo "<p class='msg'>$msg</p>"; ?>

<div class="container">
    <!-- 🌾 Farmer Products -->
    <div class="left">
        <h3>🌾 Farmer Products</h3>
        <table>
            <tr><th>Name</th><th>Category</th><th>Price</th><th>Farmer</th><th>Buy</th></tr>
            <?php while($r = $products->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($r['name']) ?></td>
                <td><?= htmlspecialchars($r['category']) ?></td>
                <td>₹ <?= number_format($r['price'], 2) ?></td>
                <td><?= htmlspecialchars($r['farmer']) ?></td>
                <td>
                    <form method="post">
                        <input type="hidden" name="product_id" value="<?= $r['id'] ?>">
                        <input type="number" name="quantity" value="10" min="1">
                        <button type="submit" name="buy">Buy</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <!-- 🛒 Purchases -->
    <div class="right">
        <h3>🛒 My Purchases</h3>
        <table>
            <tr><th>Product</th><th>Qty</th><th>Price (₹)</th></tr>
            <?php while($p = $purchases->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($p['name']) ?></td>
                <td><?= (int)$p['quantity'] ?></td>
                <td>₹ <?= number_format($p['price'], 2) ?></td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>
</body>
</html>
