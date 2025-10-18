<?php
session_start();
require_once "config.php";

// 🛡️ Role & login verification
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$role = strtolower($_SESSION['role']);
if ($role !== 'farmer' && $role !== 'admin') {
    header("Location: login.php");
    exit();
}

// ✅ Initialize message
$msg = "";

// ✅ Get product ID
if (!isset($_GET['id'])) {
    header("Location: farmer_products.php");
    exit();
}
$id = intval($_GET['id']);
$user_id = intval($_SESSION['user_id']);

// ✅ Fetch existing product details
if ($role === 'admin') {
    $stmt = $conn->prepare("SELECT * FROM products WHERE id=?");
    $stmt->bind_param("i", $id);
} else {
    $stmt = $conn->prepare("SELECT * FROM products WHERE id=? AND farmer_id=?");
    $stmt->bind_param("ii", $id, $user_id);
}
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo "<p style='color:red;text-align:center;'>❌ Product not found or unauthorized access!</p>";
    exit();
}

$product = $res->fetch_assoc();

// ✅ Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $price = floatval($_POST['price']);
    $category = trim($_POST['category']);

    // ✅ Handle image upload
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "uploads/";
        $image = $target_dir . time() . "_" . basename($_FILES["image"]["name"]);
        move_uploaded_file($_FILES["image"]["tmp_name"], $image);
    } else {
        $image = $product['image']; // Keep old image
    }

    if ($role === 'admin') {
        $update = $conn->prepare("UPDATE products SET name=?, price=?, category=?, image=? WHERE id=?");
        $update->bind_param("sdssi", $name, $price, $category, $image, $id);
    } else {
        $update = $conn->prepare("UPDATE products SET name=?, price=?, category=?, image=? WHERE id=? AND farmer_id=?");
        $update->bind_param("sdssii", $name, $price, $category, $image, $id, $user_id);
    }

    if ($update->execute()) {
        // ✅ Redirect after update (Prevents resubmit error)
        header("Location: edit_product.php?id=$id&msg=success");
        exit();
    } else {
        $msg = "❌ Failed to update product. Please try again.";
    }
}

// ✅ Show success message if redirected
if (isset($_GET['msg']) && $_GET['msg'] === 'success') {
    $msg = "✅ Product updated successfully!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Product</title>
<style>
body { font-family: Arial; background:#f9f9f9; padding:20px; }
.container { background:white; padding:25px; border-radius:8px; max-width:600px; margin:auto; box-shadow:0 4px 10px rgba(0,0,0,0.1); }
h2 { text-align:center; color:#2e7d32; }
label { display:block; margin-top:10px; font-weight:bold; }
input, select { width:100%; padding:10px; margin-top:5px; border:1px solid #ccc; border-radius:5px; }
button { background:#2e7d32; color:white; border:none; padding:10px 15px; margin-top:15px; border-radius:6px; cursor:pointer; }
button:hover { background:#1b5e20; }
.msg { text-align:center; font-weight:bold; margin-top:10px; color:#2e7d32; }
</style>
</head>
<body>
<div class="container">
    <h2>✏ Edit Product</h2>

    <?php if (!empty($msg)) echo "<p class='msg'>$msg</p>"; ?>

    <form method="POST" enctype="multipart/form-data">
        <label>Product Name</label>
        <input type="text" name="name" value="<?= htmlspecialchars($product['name']); ?>" required>

        <label>Price (₹)</label>
        <input type="number" name="price" step="0.01" value="<?= htmlspecialchars($product['price']); ?>" required>

        <label>Category</label>
        <input type="text" name="category" value="<?= htmlspecialchars($product['category']); ?>" required>

        <label>Product Image</label><br>
        <img src="<?= htmlspecialchars($product['image']); ?>" width="100" style="border-radius:8px;"><br><br>
        <input type="file" name="image">

        <button type="submit">💾 Update Product</button>
    </form>

    <p style="text-align:center;margin-top:10px;">
        <a href="farmer_products.php" style="color:#2e7d32;text-decoration:none;font-weight:bold;">⬅ Back to My Products</a>
    </p>
</div>
</body>
</html>
