<?php
session_start();
include("config.php");

// ✅ Ensure database connection
if (!$conn) {
    die("Database connection failed!");
}

// ✅ Only admin access (case-insensitive check)
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== 'admin') {
    header("Location: admin_login.php");
    exit;
}

// ✅ Handle Order Status Update
if (isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE orders SET status=? WHERE id=?");
    $stmt->bind_param("si", $status, $order_id);
    $stmt->execute();
    $stmt->close();

    header("Location: admin_panel.php?updated=1#orders");
    exit;
}

// ✅ Fetch Data
$users = $conn->query("SELECT * FROM users");
$products = $conn->query("SELECT * FROM products");
$orders = $conn->query("
    SELECT o.id, o.status, o.address, o.created_at, u.name AS customer
    FROM orders o
    JOIN users u ON o.user_id = u.id
    ORDER BY o.id DESC
");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel - Organic Harvest</title>
    <style>
        body { font-family: Arial, sans-serif; background:#f9f9f9; padding:20px; margin:0; }
        h1 { text-align:center; color:#2e7d32; }
        nav { text-align:center; margin-bottom:20px; background:#2e7d32; padding:12px; border-radius:8px; }
        nav a { margin:0 15px; text-decoration:none; color:white; font-weight:bold; }
        nav a:hover { text-decoration:underline; }
        table { width:100%; border-collapse:collapse; margin-bottom:40px; background:white; }
        th, td { border:1px solid #ddd; padding:10px; text-align:center; }
        th { background:#2e7d32; color:white; }
        img { width:60px; }
        .btn { background:#76b852; color:white; padding:5px 10px; border:none; border-radius:4px; cursor:pointer; }
        .btn:hover { background:#4caf50; }
        select { padding:5px; }
        .logout { text-align:right; margin-bottom:10px; }
        .logout a { color:red; text-decoration:none; font-weight:bold; }
        .logout a:hover { text-decoration:underline; }
    </style>
</head>
<body>

<div class="logout">
    <a href="logout.php">Logout</a>
</div>

<h1>🌱 Admin Panel</h1>

<!-- ✅ Navigation -->
<nav>
    <a href="#users">👥 Users</a>
    <a href="#products">📦 Products</a>
    <a href="#orders">🛒 Orders</a>
    <a href="dashboard.php">🏠 Dashboard</a>
    <a href="reports.php" style="
        background:white;
        color:#2e7d32;
        padding:6px 12px;
        border-radius:6px;
        text-decoration:none;
        font-weight:bold;
    ">📊 View Reports</a>
</nav>

<?php if(isset($_GET['updated'])): ?>
    <p style="color:green;text-align:center;">✅ Order status updated successfully!</p>
<?php endif; ?>

<!-- 👥 Users Section -->
<h2 id="users">👥 Users</h2>
<table>
    <tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Action</th></tr>
    <?php while($u = $users->fetch_assoc()): ?>
    <tr>
        <td><?= htmlspecialchars($u['id']) ?></td>
        <td><?= htmlspecialchars($u['name']) ?></td>
        <td><?= htmlspecialchars($u['email']) ?></td>
        <td><?= htmlspecialchars($u['role']) ?></td>
        <td>
            <form method="post" action="delete_user.php" style="display:inline;">
                <input type="hidden" name="id" value="<?= $u['id'] ?>">
                <button type="submit" class="btn">Delete</button>
            </form>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

<!-- 📦 Products Section -->
<h2 id="products">📦 Products</h2>
<table>
    <tr><th>ID</th><th>Name</th><th>Category</th><th>Price</th><th>Image</th><th>Action</th></tr>
    <?php while($p = $products->fetch_assoc()): ?>
    <tr>
        <td><?= htmlspecialchars($p['id']) ?></td>
        <td><?= htmlspecialchars($p['name']) ?></td>
        <td><?= htmlspecialchars($p['category']) ?></td>
        <td>₹ <?= htmlspecialchars($p['price']) ?></td>
        <td><img src="<?= htmlspecialchars($p['image']) ?>" alt="Product"></td>
        <td>
            <form method="post" action="delete_product.php" style="display:inline;">
                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                <button type="submit" class="btn">Delete</button>
            </form>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

<!-- 🛒 Orders Section -->
<h2 id="orders">🛒 Orders</h2>
<table>
    <tr>
        <th>ID</th>
        <th>Customer</th>
        <th>Address</th>
        <th>Status</th>
        <th>Date</th>
        <th>Action</th>
    </tr>
    <?php while($o = $orders->fetch_assoc()): ?>
    <tr>
        <td><?= htmlspecialchars($o['id']) ?></td>
        <td><?= htmlspecialchars($o['customer']) ?></td>
        <td><?= htmlspecialchars($o['address']) ?></td>
        <td><?= htmlspecialchars($o['status']) ?></td>
        <td><?= htmlspecialchars($o['created_at']) ?></td>
        <td>
            <form method="post" action="update_status.php">
                <input type="hidden" name="id" value="<?= $o['id'] ?>">
                <select name="status">
                    <option value="Placed" <?= $o['status']=="Placed"?"selected":""; ?>>Placed</option>
                    <option value="Confirmed" <?= $o['status']=="Confirmed"?"selected":""; ?>>Confirmed</option>
                    <option value="In Transit" <?= $o['status']=="In Transit"?"selected":""; ?>>In Transit</option>
                    <option value="Out for Delivery" <?= $o['status']=="Out for Delivery"?"selected":""; ?>>Out for Delivery</option>
                    <option value="Delivered" <?= $o['status']=="Delivered"?"selected":""; ?>>Delivered</option>
                </select>
                <button type="submit" class="btn">Update</button>
            </form>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

</body>
</html>
