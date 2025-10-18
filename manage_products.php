<?php
include "config.php";
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php"); exit();
}

// delete
if (isset($_GET['delete'])) {
    $pid = intval($_GET['delete']);
    // get image path
    $s = $conn->prepare("SELECT image FROM products WHERE id=?");
    $s->bind_param("i",$pid); $s->execute(); $res=$s->get_result();
    if ($r = $res->fetch_assoc()) {
        $img = $r['image'];
        $d = $conn->prepare("DELETE FROM products WHERE id=?");
        $d->bind_param("i",$pid); $d->execute(); $d->close();
        if (!empty($img) && file_exists($img)) @unlink($img);
    }
    $s->close();
    header("Location: manage_products.php");
    exit();
}

$res = $conn->query("SELECT p.*, u.name as farmer_name FROM products p JOIN users u ON p.farmer_id=u.id ORDER BY p.id DESC");
?>
<!DOCTYPE html><html><head><title>Manage Products</title>
<style>body{font-family:Arial;background:#f9f9f9;padding:20px;}table{width:100%;border-collapse:collapse;background:#fff;}th,td{border:1px solid #ddd;padding:8px;text-align:center;}th{background:#2e7d32;color:#fff;}img{width:70px;border-radius:6px;}</style>
</head><body>
<h2>Manage Products</h2>
<table>
<tr><th>ID</th><th>Image</th><th>Name</th><th>Category</th><th>Price</th><th>Farmer</th><th>Action</th></tr>
<?php while($r=$res->fetch_assoc()): ?>
<tr>
  <td><?php echo $r['id']; ?></td>
  <td><img src="<?php echo htmlspecialchars($r['image']); ?>"></td>
  <td><?php echo htmlspecialchars($r['name']); ?></td>
  <td><?php echo htmlspecialchars($r['category']); ?></td>
  <td>₹ <?php echo number_format($r['price'],2); ?></td>
  <td><?php echo htmlspecialchars($r['farmer_name']); ?></td>
  <td><a href="manage_products.php?delete=<?php echo $r['id']; ?>" onclick="return confirm('Delete?')">Delete</a></td>
</tr>
<?php endwhile; ?>
</table>
</body></html>
