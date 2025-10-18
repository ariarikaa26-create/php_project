<?php
session_start();
$conn = new mysqli("localhost", "root", "", "organic_harvest_db");

// Fetch all orders
$result = $conn->query("SELECT * FROM orders ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html>
<head>
<title>All Orders - Farm2Home</title>
<style>
body { font-family: Arial, sans-serif; background:#f9f9f9; padding:20px; }
h1 { color:#2e7d32; text-align:center; margin-bottom:20px; }
table { width:100%; border-collapse:collapse; background:white; }
th, td { border:1px solid #ddd; padding:10px; text-align:center; }
th { background:#2e7d32; color:white; }
a { text-decoration:none; color:#2e7d32; font-weight:bold; }
a:hover { color:#76b852; }
</style>
</head>
<body>

<h1>📦 All Orders</h1>

<?php if ($result->num_rows > 0): ?>
<table>
    <tr>
        <th>Order ID</th>
        <th>Customer</th>
        <th>Email</th>
        <th>Phone</th>
        <th>Total</th>
        <th>Date</th>
        <th>Details</th>
    </tr>
    <?php while($row = $result->fetch_assoc()): ?>
    <tr>
        <td>#<?php echo $row['id']; ?></td>
        <td><?php echo $row['customer_name']; ?></td>
        <td><?php echo $row['email']; ?></td>
        <td><?php echo $row['phone']; ?></td>
        <td>₹<?php echo $row['total']; ?></td>
        <td><?php echo $row['created_at']; ?></td>
        <td><a href="order_details.php?id=<?php echo $row['id']; ?>">View</a></td>
    </tr>
    <?php endwhile; ?>
</table>
<?php else: ?>
<p style="text-align:center;">No orders found yet!</p>
<?php endif; ?>

</body>
</html>
