<?php
session_start();
require_once "config.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Farmer') {
    header("Location: login.php");
    exit();
}

$msg = "";

if (isset($_POST['upload'])) {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $category = $_POST['category'];
    $farmer_id = $_SESSION['user_id'];
    $district = $_POST['district'];
    $city = $_POST['city'];
    $village = $_POST['village'];

    // ✅ Automatically create folder for category inside uploads/farmer_products/
    $base_dir = "uploads/farmer_products/";
    $target_dir = $base_dir . $category . "/";

    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    // ✅ Generate unique file name
    $image_name = time() . "_" . basename($_FILES["image"]["name"]);
    $image_path = $target_dir . $image_name;

    // ✅ Move uploaded file
    if (move_uploaded_file($_FILES["image"]["tmp_name"], $image_path)) {
        // ✅ Save in DB (including district, city, village)
        $sql = "INSERT INTO products (name, price, category, image, farmer_id, district, city, village)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sdssisss", $name, $price, $category, $image_path, $farmer_id, $district, $city, $village);

        if ($stmt->execute()) {
            $msg = "✅ Product uploaded successfully!";
        } else {
            $msg = "❌ Database Error: " . $conn->error;
        }
    } else {
        $msg = "❌ Failed to upload image file.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Farmer Upload</title>
    <style>
        body { font-family: Arial, sans-serif; background:#f9f9f9; padding:20px; }
        h2 { color:#2e7d32; }
        nav a { margin:0 15px; text-decoration:none; font-weight:bold; color:#2e7d32; }
        form { background:#fff; padding:20px; border-radius:8px; max-width:400px; margin:auto; }
        input, select { width:100%; padding:10px; margin:8px 0; border:1px solid #ccc; border-radius:6px; }
        button { background:#2e7d32; color:#fff; padding:10px; border:none; border-radius:6px; cursor:pointer; }
        button:hover { background:#1b5e20; }
        p { color:green; font-weight:bold; }
    </style>
</head>
<body>
<h2>➕ Upload Product</h2>
<nav>
    <a href="farmer_products.php">📦 My Products</a>
    <a href="farmer_orders.php">📜 My Orders</a>
    <a href="dashboard.php">🏠 Back to Dashboard</a>
</nav>

<p><?php echo $msg; ?></p>
<form method="post" enctype="multipart/form-data">
    <label>Name:</label>
    <input type="text" name="name" required>

    <label>Price:</label>
    <input type="number" name="price" required>

    <label>Category:</label>
    <select name="category" required>
        <option value="Fruits & Vegetables">Fruits & Vegetables</option>
        <option value="Grains">Grains</option>
        <option value="Dairy Products">Dairy Products</option>
        <option value="Packed Food">Packed Food</option>
        <option value="Oils & Spices">Oils & Spices</option>
        <option value="Beverages">Beverages</option>
    </select>

    <label>District:</label>
    <input type="text" name="district" placeholder="Enter district" required>

    <label>City:</label>
    <input type="text" name="city" placeholder="Enter city" required>

    <label>Village:</label>
    <input type="text" name="village" placeholder="Enter village name" required>

    <label>Image:</label>
    <input type="file" name="image" accept="image/*" required>

    <button type="submit" name="upload">Upload</button>
</form>

</body>
</html>
