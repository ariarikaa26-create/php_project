<?php
session_start();
require_once "config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_POST['place_order']) || empty($_SESSION['cart'])) {
    header("Location: checkout.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$customer_name = trim($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? ($_SESSION['email'] ?? ''));
$phone   = trim($_POST['phone'] ?? '');
$address = trim($_POST['address'] ?? '');
$cart    = $_SESSION['cart'];

if ($customer_name === '' || $address === '') {
    die("❌ Name and Address are required.");
}

$grand_total = 0;
foreach ($cart as $c) {
    $grand_total += $c['price'] * $c['quantity'];
}

$conn->begin_transaction();

try {
    // Insert into orders
    $stmt = $conn->prepare("INSERT INTO orders (user_id, customer_name, email, phone, address, total, created_at) 
                            VALUES (?,?,?,?,?,?,NOW())");
    $stmt->bind_param("issssd", $user_id, $customer_name, $email, $phone, $address, $grand_total);
    if (!$stmt->execute()) throw new Exception($stmt->error);
    $order_id = $conn->insert_id;
    $stmt->close();

    // Insert into order_items (use product_name instead of product_id)
    $stmtItem = $conn->prepare("INSERT INTO order_items (order_id, product_name, quantity, price) VALUES (?,?,?,?)");

    foreach ($cart as $it) {
        $pname = $it['name'];  // product_name
        $qty   = (int)$it['quantity'];
        $price = (float)$it['price'];
        $stmtItem->bind_param("isid", $order_id, $pname, $qty, $price);
        if (!$stmtItem->execute()) throw new Exception($stmtItem->error);
    }
    $stmtItem->close();

    $conn->commit();
    unset($_SESSION['cart']);

    header("Location: my_orders.php?placed=1&order_id=" . $order_id);
    exit();

} catch (Exception $e) {
    $conn->rollback();
    die("❌ Order failed: " . $e->getMessage());
}
?>
