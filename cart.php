<?php
session_start();
include("config.php");

if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

// Add to Cart
if (isset($_POST['add_to_cart'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $price = $_POST['price'];
    $image = $_POST['image'];

    $found = false;
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['id'] == $id) {
            $item['quantity']++;
            $found = true;
            break;
        }
    }
    if (!$found) {
        $_SESSION['cart'][] = [
            'id' => $id,
            'name' => $name,
            'price' => $price,
            'image' => $image,
            'quantity' => 1
        ];
    }
    header("Location: cart.php");
    exit;
}

// Update Quantities
if (isset($_POST['update'])) {
    foreach ($_POST['qty'] as $i => $q) {
        $_SESSION['cart'][$i]['quantity'] = max(1, intval($q));
    }
    header("Location: cart.php");
    exit;
}

// Remove item
if (isset($_GET['remove'])) {
    $i = intval($_GET['remove']);
    if (isset($_SESSION['cart'][$i])) unset($_SESSION['cart'][$i]);
    $_SESSION['cart'] = array_values($_SESSION['cart']);
    header("Location: cart.php");
    exit;
}

$cart = $_SESSION['cart'];
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Cart - Farm2Home</title>
  <style>
    body { font-family:Arial; background:#f9f9f9; padding:20px; }
    table { width:100%; border-collapse:collapse; background:#fff; }
    th,td { border:1px solid #ddd; padding:8px; text-align:center; }
    th { background:#2e7d32; color:white; }
    button { padding:6px 10px; background:#2e7d32; color:white; border:none; border-radius:4px; }
    button:hover { background:#1b5e20; }
    a { color:red; text-decoration:none; }
  </style>
</head>
<body>
  <h2>Your Cart</h2>
  <?php if (empty($cart)): ?>
    <p>Your cart is empty. <a href="products.php">Shop now</a></p>
  <?php else: ?>
    <form method="post">
      <table>
        <tr><th>Image</th><th>Name</th><th>Price</th><th>Qty</th><th>Total</th><th>Action</th></tr>
        <?php $grand=0; foreach($cart as $i=>$c): $total=$c['price']*$c['quantity']; $grand+=$total; ?>
          <tr>
            <td><img src="<?php echo $c['image']; ?>" width="70"></td>
            <td><?php echo htmlspecialchars($c['name']); ?></td>
            <td>₹ <?php echo number_format($c['price'],2); ?></td>
            <td><input type="number" name="qty[<?php echo $i; ?>]" value="<?php echo $c['quantity']; ?>" min="1" style="width:60px"></td>
            <td>₹ <?php echo number_format($total,2); ?></td>
            <td><a href="cart.php?remove=<?php echo $i; ?>">Remove</a></td>
          </tr>
        <?php endforeach; ?>
        <tr><td colspan="4" style="text-align:right;font-weight:bold">Grand Total</td><td colspan="2">₹ <?php echo number_format($grand,2); ?></td></tr>
      </table>
      <br>
      <button type="submit" name="update">Update Quantities</button>
    </form>
    <br>
    <a href="checkout.php"><button>Proceed to Checkout</button></a>
  <?php endif; ?>
</body>
</html>
