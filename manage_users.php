<?php
include "config.php";
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php"); exit();
}

// delete
if (isset($_GET['delete'])) {
    $uid = intval($_GET['delete']);
    $d = $conn->prepare("DELETE FROM users WHERE id=? AND role!='admin'");
    $d->bind_param("i", $uid);
    $d->execute();
    $d->close();
    header("Location: manage_users.php");
    exit();
}

$res = $conn->query("SELECT * FROM users ORDER BY id DESC");
?>
<!DOCTYPE html><html><head><title>Manage Users</title>
<style>body{font-family:Arial;background:#f9f9f9;padding:20px;}table{width:100%;border-collapse:collapse;background:#fff;}th,td{border:1px solid #ddd;padding:8px;text-align:center;}th{background:#2e7d32;color:#fff;}</style>
</head><body>
<h2>Manage Users</h2>
<table><tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Created</th><th>Action</th></tr>
<?php while($r=$res->fetch_assoc()): ?>
<tr>
  <td><?php echo $r['id']; ?></td>
  <td><?php echo htmlspecialchars($r['name']); ?></td>
  <td><?php echo htmlspecialchars($r['email']); ?></td>
  <td><?php echo htmlspecialchars($r['role']); ?></td>
  <td><?php echo $r['created_at']; ?></td>
  <td>
    <?php if($r['role']!=='admin'): ?>
      <a href="manage_users.php?delete=<?php echo $r['id']; ?>" onclick="return confirm('Delete user?')">Delete</a>
    <?php else: echo 'Super Admin'; endif; ?>
  </td>
</tr>
<?php endwhile; ?>
</table>
</body></html>
