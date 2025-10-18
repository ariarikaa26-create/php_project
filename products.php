<?php
session_start();
require_once "config.php";

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

// Handle Add to Cart
if (isset($_POST['add_to_cart'])) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php?msg=please_login");
        exit();
    }

    $pid   = (int)$_POST['id'];
    $name  = $_POST['name'];
    $price = $_POST['price'];
    $image = $_POST['image'];

    $found = false;
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['id'] == $pid) {
            $item['quantity']++;
            $found = true;
            break;
        }
    }
    if (!$found) {
        $_SESSION['cart'][] = [
            'id' => $pid,
            'name' => $name,
            'price' => $price,
            'image' => $image,
            'quantity' => 1
        ];
    }
    header("Location: products.php");
    exit();
}

// Fetch all products with farmer info
$sql = "SELECT p.*, u.name AS farmer_name 
        FROM products p
        JOIN users u ON p.farmer_id = u.id
        ORDER BY p.id DESC";
$res = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Browse Products</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f9f9f9;
            margin: 0; padding: 20px;
        }
        h2 {
            color: #2e7d32;
            text-align: center;
        }
        .grid {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
            margin-top: 20px;
        }
        .card {
            width: 230px;
            background: #fff;
            border-radius: 12px;
            padding: 15px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.2s;
        }
        .card:hover { transform: scale(1.03); }
        .card img {
            width: 100%;
            height: 160px;
            object-fit: cover;
            border-radius: 8px;
        }
        .card h3 {
            margin: 12px 0 6px;
            font-size: 18px;
            color: #2e7d32;
        }
        .price {
            font-size: 16px;
            font-weight: bold;
            color: #4caf50;
        }
        .info {
            color: #555;
            font-size: 13px;
            margin: 4px 0;
        }
        button {
            background: #2e7d32;
            color: #fff;
            border: none;
            padding: 8px 15px;
            border-radius: 6px;
            cursor: pointer;
            margin-top: 10px;
        }
        button:hover { background: #1b5e20; }
        .disabled {
            background: #ccc;
            color: #666;
            cursor: not-allowed;
        }
        .cart-link {
            text-align: center;
            margin-top: 20px;
        }
        .cart-link a {
            text-decoration: none;
            color: #2e7d32;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h2>🌿 Browse Organic Products</h2>

    <div class="grid">
        <?php if ($res && $res->num_rows > 0): ?>
            <?php while ($row = $res->fetch_assoc()): ?>
                <div class="card">
                    <img src="<?php echo htmlspecialchars($row['image']); ?>" alt="Product">
                    <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                    <p class="price">₹ <?php echo number_format($row['price'], 2); ?></p>

                    <!-- Farmer & Location -->
                    <p class="info">👨‍🌾 Farmer: <?php echo htmlspecialchars($row['farmer_name']); ?></p>
                    
                    <?php if (!empty($row['village']) || !empty($row['city']) || !empty($row['district'])): ?>
                        <p class="info">📍 
                            <?php echo htmlspecialchars($row['village']); ?>,
                            <?php echo htmlspecialchars($row['city']); ?>,
                            <?php echo htmlspecialchars($row['district']); ?>
                        </p>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['user_id'])): ?>
                        <form method="post">
                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                            <input type="hidden" name="name" value="<?php echo htmlspecialchars($row['name']); ?>">
                            <input type="hidden" name="price" value="<?php echo $row['price']; ?>">
                            <input type="hidden" name="image" value="<?php echo htmlspecialchars($row['image']); ?>">
                            <button type="submit" name="add_to_cart">Add to Cart</button>
                        </form>
                    <?php else: ?>
                        <button class="disabled">Login to Add</button>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No products available.</p>
        <?php endif; ?>
    </div>

    <div class="cart-link">
        <p><a href="cart.php">🛒 Go to Cart</a></p>
    </div>
</body>
</html>
