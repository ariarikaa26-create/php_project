<?php
session_start();
require_once "config.php";

$order_id = $_GET['order_id'] ?? 0;
$amount = $_GET['amount'] ?? 0;

// ✅ Update payment status to "Paid" in database
if ($order_id) {
    $conn->query("UPDATE orders SET payment_status='Paid' WHERE id=$order_id");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Farm2Home - Online Payment</title>
    <style>
        body {
            background: #f1f8e9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            font-family: Arial, sans-serif;
        }
        .box {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            text-align: center;
            width: 400px;
        }
        h2 {
            color: #2e7d32;
            margin-bottom: 10px;
        }
        p {
            font-size: 16px;
            color: #333;
        }
        .loader {
            border: 6px solid #c8e6c9;
            border-top: 6px solid #2e7d32;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .btn {
            background: #2e7d32;
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            display: inline-block;
            font-weight: bold;
            margin-top: 20px;
        }
        .btn:hover { background: #1b5e20; }
    </style>
    <script>
        // Auto redirect to success page after 4 seconds
        setTimeout(() => {
            window.location.href = "payment_success.php?order_id=<?= $order_id ?>";
        }, 4000);
    </script>
</head>
<body>
    <div class="box">
        <h2>🔐 Processing Payment...</h2>
        <div class="loader"></div>
        <p>Processing online payment of <b>₹<?= number_format($amount, 2) ?></b></p>
        <p>Please wait, do not close this page.</p>
        <a href="payment_success.php?order_id=<?= $order_id ?>" class="btn">Click if not redirected</a>
    </div>
</body>
</html>
