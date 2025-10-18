<?php
session_start();
require_once "config.php";

// ✅ Only consumers can cancel orders
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== 'consumer') {
    header("Location: login.php");
    exit();
}

// ✅ Check if ID is passed
if (isset($_GET['id'])) {
    $order_id = intval($_GET['id']);
    $user_id = $_SESSION['user_id'];

    // Cancel only if order belongs to this user and status is 'Placed'
    $sql = "UPDATE orders SET status = 'Cancelled' WHERE id = ? AND user_id = ? AND status = 'Placed'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        // ✅ Redirect back with success message
        header("Location: my_orders.php?msg=cancel_success");
    } else {
        // ❌ Cancel failed
        header("Location: my_orders.php?msg=cancel_failed");
    }
    $stmt->close();
} else {
    // If no order ID found
    header("Location: my_orders.php");
    exit();
}
?>
