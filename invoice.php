<?php
session_start();
require_once "config.php";

// 🧾 1️⃣ Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// 🧾 2️⃣ Get order ID
if (!isset($_GET['order_id'])) {
    die("<h3 style='color:red;text-align:center;'>❌ Invalid Request!</h3>");
}

$order_id = intval($_GET['order_id']);
$user_id = $_SESSION['user_id'];

// 🧾 3️⃣ Fetch order info
$order = $conn->query("SELECT * FROM orders WHERE id=$order_id AND user_id=$user_id")->fetch_assoc();
if (!$order) {
    die("<h3 style='color:red;text-align:center;'>❌ Order not found!</h3>");
}

// 🧾 4️⃣ Fetch order items
$order_items = $conn->query("SELECT * FROM order_items WHERE order_id=$order_id");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Invoice #<?= $order_id ?> - Farm2Home</title>
<style>
body {
    font-family: 'Arial', sans-serif;
    background: #f9f9f9;
    margin: 40px;
    color: #333;
}
.invoice-box {
    max-width: 850px;
    margin: auto;
    background: #fff;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.header {
    display: flex;
    align-items: center;
    border-bottom: 3px solid #2e7d32;
    padding-bottom: 10px;
    margin-bottom: 20px;
}
.header img {
    width: 80px;
    margin-right: 20px;
}
.header h1 {
    font-size: 26px;
    color: #2e7d32;
    margin: 0;
}
h2 {
    text-align: center;
    color: #2e7d32;
    margin-bottom: 10px;
}
.info {
    line-height: 1.6;
    margin-bottom: 15px;
}
.info strong {
    color: #2e7d32;
}
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
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
.total {
    text-align: right;
    font-weight: bold;
    font-size: 18px;
}
.footer {
    text-align: center;
    font-size: 13px;
    margin-top: 30px;
    color: #555;
}
button {
    background: #2e7d32;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    margin-top: 20px;
}
button:hover {
    background: #1b5e20;
}
</style>
</head>
<body>

<div class="invoice-box">
    <div class="header">
        <img src="images/logo1.png" alt="Farm2Home Logo">
        <div>
            <h1>Farm2Home</h1>
            <p style="margin:0;">
                <strong>Address:</strong> Sion (w) Mumbai-22 Maharashtra,India</sbr>
                <strong>Email:</strong> info@farm2home.com | <strong>Contact:</strong> +91 98765 43210
            </p>
        </div>
    </div>

    <h2>🧾 Invoice #<?= $order_id ?></h2>

    <div class="info">
        <strong>Date:</strong> <?= $order['created_at'] ?><br>
        <strong>Customer:</strong> <?= htmlspecialchars($_SESSION['name']) ?><br>
        <strong>Delivery Address:</strong> <?= htmlspecialchars($order['address']) ?><br>
        <strong>Payment Method:</strong> <?= htmlspecialchars($order['payment_method']) ?>
    </div>

    <table>
        <tr>
            <th>Product</th>
            <th>Qty</th>
            <th>Price (₹)</th>
            <th>Total (₹)</th>
        </tr>
        <?php
        $grand_total = 0;
        while ($item = $order_items->fetch_assoc()):
            $total = $item['price'] * $item['quantity'];
            $grand_total += $total;
        ?>
        <tr>
            <td><?= htmlspecialchars($item['name']) ?></td>
            <td><?= $item['quantity'] ?></td>
            <td><?= number_format($item['price'], 2) ?></td>
            <td><?= number_format($total, 2) ?></td>
        </tr>
        <?php endwhile; ?>
        <tr>
            <td colspan="3" class="total">Grand Total:</td>
            <td><b>₹ <?= number_format($grand_total, 2) ?></b></td>
        </tr>
    </table>

    <div style="text-align:center;">
        <button onclick="window.print()">🖨 Print / 💾 Download PDF</button>
    </div>

    <div class="footer">
        Thank you for shopping with <strong>Farm2Home</strong> 🌾<br>
        This is a computer-generated invoice.
    </div>
</div>

</body>
</html>
