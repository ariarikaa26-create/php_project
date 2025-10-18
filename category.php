<?php
session_start();

// Get selected category (folder name from URL)
$category = $_GET['category'] ?? '';
$folder   = "uploads/" . $category;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo htmlspecialchars(str_replace("_", " ", $category)); ?> - Farm2Home</title>
<style>
body { font-family: Arial, sans-serif; background: #f9f9f9; margin:0; padding:20px; }
h1 { text-align:center; color:#2e7d32; margin-bottom:20px; }
.grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:20px; }
.card { background:#fff; border-radius:8px; padding:10px; box-shadow:0 2px 5px rgba(0,0,0,0.1); text-align:center; }
.card img { width:100%; height:150px; object-fit:cover; border-radius:6px; }
.card h3 { margin:10px 0; font-size:16px; text-transform:capitalize; }
.card p { color:#4caf50; font-weight:bold; }
input[type=number] { width:60px; padding:5px; margin-top:5px; }
button { background:#2e7d32; color:#fff; border:none; padding:6px 12px; border-radius:5px; cursor:pointer; margin-top:8px; }
button:hover { background:#1b5e20; }
</style>
</head>
<body>

<h1><?php echo htmlspecialchars(str_replace("_", " ", $category)); ?></h1>

<div class="grid">
<?php
if ($category && is_dir($folder)) {
    // get all image files
    $files = glob($folder . "/*.{jpg,jpeg,png,gif,JPG,JPEG,PNG,GIF}", GLOB_BRACE);

    if (!empty($files)) {
        foreach ($files as $file) {
            $name = pathinfo($file, PATHINFO_FILENAME);
            ?>
            <div class="card">
                <img src="<?php echo $file; ?>" alt="<?php echo $name; ?>">
                <h3><?php echo ucfirst($name); ?></h3>
                <p>₹ 100</p>
                <form method="POST" action="cart.php">
                <input type="hidden" name="name" value="<?php echo ucfirst($name); ?>">
                <input type="hidden" name="price" value="100">
                <input type="hidden" name="image" value="<?php echo $file; ?>">
                <button type="submit" name="add_to_cart">Add to Cart</button>
                </form>
            </div>
            <?php
        }
    } else {
        echo "<p style='text-align:center;color:gray;'>No products found in this category.</p>";
    }
} else {
    echo "<p style='text-align:center;color:red;'>Invalid category selected or folder missing.</p>";
}
?>
</div>

</body>
</html>
