<?php
// ✅ Safe session start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "config.php";

// ✅ Only Consumers can access
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== 'consumer') {
    header("Location: login.php?msg=please_login");
    exit();
}

// ✅ If cart empty → go back to cart
if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$grand_total = 0;
foreach ($_SESSION['cart'] as $item) {
    $grand_total += $item['price'] * $item['quantity'];
}

// ✅ Handle checkout form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $address = $conn->real_escape_string($_POST['address']);
    $payment_method = $conn->real_escape_string($_POST['payment_method']);

    // Insert order into database
    $insert_order = $conn->query("
        INSERT INTO orders (user_id, customer_name, total, status, address, payment_method, created_at)
        VALUES ($user_id, '$name', $grand_total, 'Placed', '$address', '$payment_method', NOW())
    ");

    if ($insert_order) {
        $order_id = $conn->insert_id;

        // Insert each cart item into order_items
        foreach ($_SESSION['cart'] as $item) {
            $pname = $conn->real_escape_string($item['name']);
            $price = $item['price'];
            $qty = $item['quantity'];
            $conn->query("
                INSERT INTO order_items (order_id, name, price, quantity)
                VALUES ($order_id, '$pname', $price, $qty)
            ");
        }

        // Empty cart after successful order
        unset($_SESSION['cart']);

        // Redirect to success page
        header("Location: payment_success.php?order_id=$order_id");
        exit();
    } else {
        echo "<p style='color:red; text-align:center; font-weight:bold;'>⚠️ Order placement failed. Please try again later.</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout | Farm2Home</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f9f9f9;
            padding: 20px;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            max-width: 600px;
            margin: auto;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        h2 {
            color: #2e7d32;
            text-align: center;
            margin-bottom: 20px;
        }
        label {
            font-weight: bold;
            display: block;
            margin-top: 15px;
        }
        input, textarea, select {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 15px;
        }
        button {
            background: #2e7d32;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            margin-top: 20px;
            width: 100%;
            font-size: 16px;
        }
        button:hover {
            background: #1b5e20;
        }
        .total {
            text-align: center;
            font-size: 18px;
            color: #333;
            margin-top: 20px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>🧾 Checkout</h2>
    <form method="POST">
        <label for="name">Full Name</label>
        <input type="text" name="name" id="name" placeholder="Enter your full name" required>

        <label for="address">Delivery Address</label>
        <textarea name="address" id="address" rows="4" placeholder="Enter your delivery address" required></textarea>

        <label for="payment_method">Payment Method</label>
        <select name="payment_method" id="payment_method" required>
            <option value="">-- Select Payment Method --</option>
            <option value="COD">Cash on Delivery</option>
            <option value="Online">Online Payment (Demo)</option>
        </select>

        <p class="total"><b>Total Amount:</b> ₹ <?= number_format($grand_total, 2) ?></p>

        <button type="submit">✅ Confirm & Place Order</button>
    </form>
</div>

</body>
</html>
