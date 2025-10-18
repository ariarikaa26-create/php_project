<?php
session_start();
require_once "config.php";

// ✅ Only allow Farmers
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== 'farmer') {
    header("Location: login.php");
    exit();
}

$farmer_id = $_SESSION['user_id'];

// ✅ Fetch all products uploaded by this farmer
$stmt = $conn->prepare("SELECT * FROM products WHERE farmer_id = ?");
$stmt->bind_param("i", $farmer_id);
$stmt->execute();
$res = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Products - Farmer</title>
    <style>
        body { font-family: Arial, sans-serif; background:#f9f9f9; padding:20px; }
        h2 { color:#2e7d32; }
        nav a { margin:0 15px; text-decoration:none; font-weight:bold; color:#2e7d32; }
        table { width:100%; border-collapse:collapse; background:#fff; margin-top:20px; }
        th, td { border:1px solid #ddd; padding:10px; text-align:center; }
        th { background:#2e7d32; color:white; }
        img { width:70px; border-radius:6px; }
        button { background:none; border:none; color:red; cursor:pointer; font-size:15px; }
        .msg { color:green; font-weight:bold; margin:10px 0; }
    </style>
</head>
<body>
<h2>📦 My Uploaded Products</h2>

<!-- ✅ Success Message -->
<?php if (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
    <p class="msg">✅ Product deleted successfully!</p>
<?php endif; ?>

<nav>
    <a href="farmer_upload.php">➕ Upload Product</a>
    <a href="farmer_orders.php">📜 My Orders</a>
    <a href="dashboard.php">🏠 Back to Dashboard</a>
</nav>
<table>
<tr>
    <th>Image</th>
    <th>Name</th>
    <th>Price (₹)</th>
    <th>Category</th>
    <th>Action</th>
</tr>

<?php if ($res && $res->num_rows > 0): ?>
    <?php while($row = $res->fetch_assoc()): ?>
    <tr>
        <td><img src="<?= htmlspecialchars($row['image']) ?>" alt="product" width="70" height="70" style="border-radius:6px;"></td>
        <td><?= htmlspecialchars($row['name']) ?></td>
        <td>₹ <?= number_format($row['price'], 2) ?></td>
        <td><?= htmlspecialchars($row['category']) ?></td>
        <td>
            <!-- ✏ Edit Product -->
            <form action="edit_product.php" method="GET" style="display:inline;">
                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                <button type="submit" style="background:#fbc02d;color:white;border:none;padding:6px 10px;border-radius:5px;cursor:pointer;">
                    ✏ Edit
                </button>
            </form>

            <!-- 🗑 Delete Product -->
            <form method="POST" action="delete_product.php" style="display:inline;">
                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                <button type="submit" 
                        onclick="return confirm('Are you sure you want to delete this product?');"
                        style="background:#d32f2f;color:white;border:none;padding:6px 10px;border-radius:5px;cursor:pointer;">
                    🗑 Delete
                </button>
            </form>
        </td>
    </tr>
    <?php endwhile; ?>
<?php else: ?>
    <tr><td colspan="5" style="text-align:center;color:red;">No products found.</td></tr>
<?php endif; ?>
</table>
</body>
</html>