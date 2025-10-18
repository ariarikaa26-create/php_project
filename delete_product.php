<?php
session_start();
require_once "config.php";

// 🛡️ 1️⃣ Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

$user_id = intval($_SESSION['user_id']);
$role = strtolower($_SESSION['role']);

// 🧩 2️⃣ Get Product ID (works for both GET/POST)
if (isset($_POST['id']) || isset($_GET['id'])) {
    $id = isset($_POST['id']) ? intval($_POST['id']) : intval($_GET['id']);
} else {
    header("Location: farmer_products.php?msg=invalid_id");
    exit();
}

// 🧮 3️⃣ Fetch product info (for image delete)
if ($role === 'admin') {
    $stmt = $conn->prepare("SELECT image FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
} elseif ($role === 'farmer') {
    $stmt = $conn->prepare("SELECT image FROM products WHERE id = ? AND farmer_id = ?");
    $stmt->bind_param("ii", $id, $user_id);
} else {
    header("Location: login.php");
    exit();
}

$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $imagePath = $row['image'];

    // ✅ 4️⃣ Delete product from database
    if ($role === 'admin') {
        $delete = $conn->prepare("DELETE FROM products WHERE id = ?");
        $delete->bind_param("i", $id);
    } else {
        $delete = $conn->prepare("DELETE FROM products WHERE id = ? AND farmer_id = ?");
        $delete->bind_param("ii", $id, $user_id);
    }

    if ($delete->execute()) {
        // 🖼️ 5️⃣ Delete the uploaded image (if exists)
        if (!empty($imagePath) && file_exists($imagePath)) {
            unlink($imagePath);
        }

        // 🚀 6️⃣ Redirect with confirmation
        if ($role === 'admin') {
            header("Location: admin_panel.php?msg=product_deleted#products");
        } else {
            header("Location: farmer_products.php?msg=deleted");
        }
        exit();
    } else {
        echo "<p style='color:red; text-align:center;'>❌ Error deleting product: " . $conn->error . "</p>";
    }
} else {
    echo "<p style='color:red; text-align:center;'>❌ Product not found or permission denied.</p>";
}
?>
