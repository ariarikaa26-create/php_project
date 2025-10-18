<?php
session_start();

// ✅ Categories mapping (URL Key → Actual Category Name)
$categories = [
    "Fruits_Vegetables" => "Fruits & Vegetables",
    "Grains"            => "Grains",
    "Dairy_Products"    => "Dairy Products",
    "Packed_Food"       => "Packed Food",
    "Oils_Spices"       => "Oils & Spices",
    "Beverages"         => "Beverages"
];

// ✅ Database connection
$conn = new mysqli("localhost", "root", "", "organic_harvest_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ✅ Handle Add to Cart
if (isset($_POST['add_to_cart'])) {
    $name  = $_POST['name'];
    $price = $_POST['price'];
    $image = $_POST['image'];

    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

    $found = false;
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['name'] == $name) {
            $item['quantity']++;
            $found = true;
            break;
        }
    }

    if (!$found) {
        $_SESSION['cart'][] = [
            'name' => $name,
            'price' => $price,
            'image' => $image,
            'quantity' => 1
        ];
    }

    header("Location: dashboard.php?show=all");
    exit;
}

// ✅ Check what to display
$show_all = isset($_GET['show']) && $_GET['show'] === "all";
$category_key = $_GET['category'] ?? null;

// Convert key like Fruits_Vegetables → Fruits & Vegetables
$selected_category = $category_key ? ($categories[$category_key] ?? null) : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Farm2Home - Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
<style>
body { margin:0; font-family:'Roboto', sans-serif; background:#f9f9f9; }
/* Header */
.header {
    background: url('images/banner.png') center/cover no-repeat;
    height: 350px;
    display:flex; flex-direction:column;
    justify-content:center; align-items:center;
    color:white; text-align:center; position:relative;
}
.header::after {
    content:""; position:absolute; top:0; left:0;
    width:100%; height:100%; background:rgba(0,0,0,0.4);
}
.header-content { position:relative; z-index:1; }
.header-content h1 { font-size:50px; font-weight:700; margin:0; }
.header-content p { font-size:20px; margin-top:10px; }
/* Navbar */
.navbar {
    background:#76b852;
    padding:15px 20px;
    display:flex;
    justify-content:space-between;
    align-items:center;
}
.navbar-left {
    flex:1;
    display:flex;
    justify-content:center;
    gap:25px;
}
.navbar-left a {
    color:white; text-decoration:none;
    font-weight:700; font-size:18px;
    transition:0.3s;
}
.navbar-left a:hover { color:#c8e6c9; transform:scale(1.05); }
.navbar-right a, .navbar-right span {
    color:white; margin-left:15px; font-weight:bold;
}
/* Sections */
.section { padding:60px 20px; text-align:center; max-width:1000px; margin:0 auto; }
.section h2 { font-size:32px; margin-bottom:20px; color:#2e7d32; font-weight:800; }
.section p { font-size:18px; line-height:1.8; font-weight:600; max-width:800px; margin:0 auto 25px; }
/* Features */
.features {
    display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:25px; margin-top:30px;
}
.feature { background:#f1f8e9; padding:25px; border-radius:10px; font-weight:600; }
.feature h3 { margin-bottom:10px; color:#388e3c; font-weight:700; }
/* Products */
.product-row { margin:40px 0; }
.product-row h2 { text-align:center; margin-bottom:15px; color:#388e3c; }
.grid {
    display:flex; flex-wrap:wrap; gap:20px; justify-content:center;
}
.card {
    width:220px; background:#fff; border-radius:12px;
    padding:15px; box-shadow:0 4px 10px rgba(0,0,0,0.1); 
    text-align:center;
    transition: transform 0.3s, box-shadow 0.3s;
}
.card:hover {
    transform: translateY(-8px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}
.card img {
    width:100%; height:160px; object-fit:cover; border-radius:8px;
}
.card h3 {
    margin:12px 0 6px;
    font-size:18px; font-weight:700; color:#2e7d32;
}
.card p {
    margin:5px 0;
    color:#4caf50; font-weight:bold; font-size:16px;
}
.card form { margin-top:10px; }
button {
    background:#2e7d32;
    color:#fff; border:none;
    padding:8px 15px; border-radius:6px;
    cursor:pointer; font-weight:bold;
    transition:0.3s;
}
button:hover { background:#1b5e20; }
button.added {
    background:white; color:#2e7d32;
    border:2px solid #2e7d32; cursor:default;
}

/* Footer */
footer { background:#2e7d32; color:white; padding:30px 20px; text-align:center; margin-top:40px; font-weight:600; }
</style>
</head>
<body>

<!-- Header -->
<div class="header">
  <div class="header-content">
    <h1>Farm2Home</h1>
    <p>Fresh Organic Products Delivered to Your Doorstep</p>
  </div>
</div>

<!-- Navbar -->
<div class="navbar">
  <div class="navbar-left">
    <a href="dashboard.php">Home</a>
    <a href="dashboard.php?show=all">All Products</a>
    <?php foreach ($categories as $key => $label): ?>
        <a href="dashboard.php?category=<?php echo urlencode($key); ?>"><?php echo $label; ?></a>
    <?php endforeach; ?>
    <a href="cart.php">🛒 Cart</a>
    <a href="my_orders.php">📦 My Orders</a>
    <a href="feedback.php">💬 Feedback</a>

  </div>

  <div class="navbar-right">
    <?php if(isset($_SESSION['user_id'])): ?>
        <span>👤 <?php echo $_SESSION['name']; ?> (<?php echo ucfirst($_SESSION['role']); ?>)</span>
        <a href="logout.php">Logout</a>
    <?php else: ?>
        <a href="login.php">👤 Login</a>
    <?php endif; ?>
  </div>
</div>

<!-- Main Content -->
<?php if (!$show_all && !$selected_category): ?>
    <!-- Home Info -->
    <div class="section">
        <h2>Welcome to Farm2Home</h2>
        <p>
            We bring fresh, organic, and chemical-free products directly from local farms to your home. 
            Our mission is to promote healthy living by ensuring access to natural and sustainable food products.
        </p>
    </div>

    <div class="why-choose section">
        <h2>Why Choose Us?</h2>
        <div class="features">
            <div class="feature"><h3>Always Fresh</h3><p>We deliver only farm-fresh organic products.</p></div>
            <div class="feature"><h3>100% Natural</h3><p>No chemicals, no preservatives – just healthy food.</p></div>
            <div class="feature"><h3>Super Healthy</h3><p>Nutritious and safe products for your family.</p></div>
            <div class="feature"><h3>Premium Quality</h3><p>Handpicked products from trusted farmers.</p></div>
        </div>
    </div>

    <div style="text-align:center; margin-top:40px;">
        <a href="my_orders.php" 
           style="background:#2e7d32;color:white;padding:12px 25px;
                  text-decoration:none;border-radius:6px;font-weight:bold;
                  font-size:16px;box-shadow:0 4px 10px rgba(0,0,0,0.1);">
            🚚 Track My Orders
        </a>
    </div>

<?php elseif ($show_all): ?>
    <!-- ✅ All Products -->
    <h2 style="text-align:center; margin-top:30px;">All Products</h2>
    <?php foreach ($categories as $cat_key => $title): ?>
        <div class="product-row">
            <h2><?php echo $title; ?></h2>
            <div class="grid">
            <?php
                $actual_category = $categories[$cat_key];
                $sql = "SELECT * FROM products WHERE category='$actual_category' ORDER BY id DESC";
                $res = $conn->query($sql);
                if ($res && $res->num_rows > 0) {
                    while ($row = $res->fetch_assoc()):
                        $in_cart = false;
                        if (isset($_SESSION['cart'])) {
                            foreach ($_SESSION['cart'] as $cart_item) {
                                if ($cart_item['name'] == $row['name']) {
                                    $in_cart = true;
                                    break;
                                }
                            }
                        }
            ?>
                <div class="card">
                    <img src="<?php echo $row['image']; ?>" alt="<?php echo $row['name']; ?>">
                    <h3><?php echo $row['name']; ?></h3>
                    <p>₹ <?php echo $row['price']; ?></p>
                    <?php if ($in_cart): ?>
                        <button type="button" class="added">✔ Added</button>
                    <?php else: ?>
                        <form method="POST" action="">
                            <input type="hidden" name="name" value="<?php echo $row['name']; ?>">
                            <input type="hidden" name="price" value="<?php echo $row['price']; ?>">
                            <input type="hidden" name="image" value="<?php echo $row['image']; ?>">
                            <button type="submit" name="add_to_cart">Add to Cart</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endwhile; } else { echo "<p>No products found in this category.</p>"; } ?>
            </div>
        </div>
    <?php endforeach; ?>

<?php elseif ($selected_category): ?>
    <!-- ✅ Single Category -->
    <h2 style="text-align:center; margin-top:30px;"><?php echo $selected_category; ?></h2>
    <div class="grid">
    <?php
        $sql = "SELECT * FROM products WHERE category='$selected_category' ORDER BY id DESC";
        $res = $conn->query($sql);
        if ($res && $res->num_rows > 0) {
            while ($row = $res->fetch_assoc()):
                $in_cart = false;
                if (isset($_SESSION['cart'])) {
                    foreach ($_SESSION['cart'] as $cart_item) {
                        if ($cart_item['name'] == $row['name']) {
                            $in_cart = true;
                            break;
                        }
                    }
                }
    ?>
        <div class="card">
            <img src="<?php echo $row['image']; ?>" alt="<?php echo $row['name']; ?>">
            <h3><?php echo $row['name']; ?></h3>
            <p>₹ <?php echo $row['price']; ?></p>
            <?php if ($in_cart): ?>
                <button type="button" class="added">✔ Added</button>
            <?php else: ?>
                <form method="POST" action="">
                    <input type="hidden" name="name" value="<?php echo $row['name']; ?>">
                    <input type="hidden" name="price" value="<?php echo $row['price']; ?>">
                    <input type="hidden" name="image" value="<?php echo $row['image']; ?>">
                    <button type="submit" name="add_to_cart">Add to Cart</button>
                </form>
            <?php endif; ?>
        </div>
    <?php endwhile; } else { echo "<p>No products found.</p>"; } ?>
    </div>
<?php endif; ?>

<!-- Footer -->
<footer>
  <p>Farm2Home &copy; 2025. All rights reserved.</p>
  <p>Email: info@farm2home.com | Contact: 123-456-7890</p>
</footer>

</body>
</html>


