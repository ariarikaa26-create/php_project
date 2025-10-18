<?php
// add_product.php
error_reporting(E_ALL); ini_set('display_errors',1);
include 'config.php'; // must set $conn = new mysqli(...)

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $category = $_POST['category'] ?? '';
    $price = floatval($_POST['price'] ?? 0);

    if (!empty($_FILES['image']['name'])) {
        $targetDir = 'uploads/' . strtolower(str_replace(' & ', '_', $category)) . '/';
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

        $original = basename($_FILES['image']['name']);
        $fileName = time() . '_' . preg_replace('/\s+/', '_', $original);
        $targetFile = $targetDir . $fileName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            $stmt = $conn->prepare("INSERT INTO products (name, category, price, image) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssds", $name, $category, $price, $targetFile);
            if ($stmt->execute()) {
                echo "Product added.";
            } else {
                echo "Insert failed: " . $stmt->error;
            }
        } else {
            echo "File upload failed.";
        }
    } else {
        echo "Please choose an image.";
    }
}
?>
<!-- Simple HTML form -->
<form method="POST" enctype="multipart/form-data">
<input name="name" placeholder="Name" required><br>
<select name="category">
  <option>Fruits & Vegetables</option>
  <option>Grains</option>
  <option>Dairy Products</option>
  <option>Packed Food</option>
</select><br>
<input name="price" type="number" step="0.01" required><br>
<input type="file" name="image" accept="image/*"><br>
<button type="submit">Add Product</button>
</form>
