<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include "config.php";

// ✅ Only admin allowed (case-insensitive)
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== 'admin') {
    header("Location: admin_login.php");
    exit();
}

// ✅ Total Users
$total_users = $conn->query("SELECT COUNT(*) AS c FROM users")->fetch_assoc()['c'];
$total_farmers = $conn->query("SELECT COUNT(*) AS c FROM users WHERE role='farmer'")->fetch_assoc()['c'];

// ✅ Total Orders
$total_orders = $conn->query("SELECT COUNT(*) AS c FROM orders")->fetch_assoc()['c'];

// ✅ Total Revenue
$total_revenue = $conn->query("SELECT SUM(price*quantity) AS total FROM order_items")->fetch_assoc()['total'] ?? 0;

// ✅ Top 5 Products
$top_products = $conn->query("
    SELECT product_name AS name, SUM(quantity) AS total_sold
    FROM order_items
    GROUP BY product_name
    ORDER BY total_sold DESC
    LIMIT 5
");

// ✅ Daily Revenue
$daily_sales = $conn->query("
    SELECT DATE(created_at) AS date, SUM(price*quantity) AS total
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    GROUP BY DATE(created_at)
    ORDER BY DATE(created_at) ASC
");
$dates = []; $totals = [];
while($row = $daily_sales->fetch_assoc()) {
    $dates[] = $row['date'];
    $totals[] = $row['total'];
}

// ✅ User Role Distribution
$roles = $conn->query("SELECT role, COUNT(*) AS count FROM users GROUP BY role");
$role_labels = []; $role_counts = [];
while($r = $roles->fetch_assoc()) {
  $role_labels[] = ucfirst($r['role']);
  $role_counts[] = $r['count'];
}

// ✅ Top 5 Farmers by Total Sales
$top_farmers = $conn->query("
    SELECT u.name AS farmer_name, SUM(oi.price * oi.quantity) AS total_sales
    FROM order_items oi
    JOIN products p ON oi.product_name = p.name
    JOIN users u ON p.farmer_id = u.id
    GROUP BY u.name
    ORDER BY total_sales DESC
    LIMIT 5
");




$farmer_names = [];
$farmer_sales = [];
while($row = $top_farmers->fetch_assoc()) {
    $farmer_names[] = $row['farmer_name'];
    $farmer_sales[] = $row['total_sales'];
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Admin Reports - Organic Harvest</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
body { font-family: 'Segoe UI', sans-serif; background:#f4f6f9; margin:0; padding:0; color:#333; }

/* Navbar */
nav {
  background:#2e7d32; color:white; padding:15px 25px;
  display:flex; justify-content:space-between; align-items:center;
}
nav a {
  color:white; text-decoration:none; margin:0 15px;
  font-weight:bold;
}
nav a:hover { text-decoration:underline; }

/* Stats Cards */
.stats { display:flex; flex-wrap:wrap; gap:20px; margin:30px auto; justify-content:center; }
.card {
  flex:1 1 200px;
  background:white;
  padding:20px;
  border-radius:12px;
  box-shadow:0 4px 12px rgba(0,0,0,0.1);
  text-align:center;
  transition: all 0.3s ease;
}
.card:hover { transform:translateY(-5px); box-shadow:0 6px 15px rgba(0,0,0,0.2); }
.card h3 { color:#1b5e20; margin-bottom:10px; }
.card p { font-size:24px; font-weight:bold; margin:0; }

/* Chart Containers */
.chart-container {
  background:white;
  margin:25px auto;
  padding:25px;
  border-radius:12px;
  box-shadow:0 4px 12px rgba(0,0,0,0.1);
  width:90%;
  max-width:900px;
}
canvas { width:100% !important; }

/* Dark Mode */
.dark-mode { background:#121212; color:white; }
.dark-mode nav { background:#388e3c; }
.dark-mode .card { background:#1e1e1e; color:white; }
.dark-mode .chart-container { background:#1e1e1e; color:white; }
.toggle-btn {
  background:white; color:#2e7d32; border:none;
  padding:6px 12px; border-radius:6px; cursor:pointer;
  font-weight:bold;
}
</style>
</head>
<body>

<!-- Navbar -->
<nav>
  <div><strong>🌱 Organic Harvest Admin Dashboard</strong></div>
  <div>
    <a href="admin_panel.php">🏠 Home</a>
    <a href="manage_users.php">👥 Users</a>
    <a href="manage_products.php">🛒 Products</a>
    <a href="manage_orders.php">📦 Orders</a>
    <a href="logout.php">🚪 Logout</a>
    <button class="toggle-btn" id="toggleTheme">🌙</button>
  </div>
</nav>

<h2 style="text-align:center; margin-top:20px;">📊 Reports & Analytics</h2>

<!-- Stats Cards -->
<div class="stats">
  <div class="card"><h3>Total Users</h3><p><?php echo $total_users; ?></p></div>
  <div class="card"><h3>Total Farmers</h3><p><?php echo $total_farmers; ?></p></div>
  <div class="card"><h3>Total Orders</h3><p><?php echo $total_orders; ?></p></div>
  <div class="card"><h3>Total Revenue</h3><p>₹ <?php echo number_format($total_revenue,2); ?></p></div>
</div>

<!-- Charts Section -->
<div class="chart-container">
  <h3>🔥 Top 5 Best-Selling Products</h3>
  <canvas id="topProductsChart" height="120"></canvas>
</div>

<div class="chart-container">
  <h3>💰 Daily Revenue Trend</h3>
  <canvas id="salesChart" height="120"></canvas>
</div>

<div class="chart-container">
  <h3>👥 User Role Distribution</h3>
  <canvas id="userChart" height="120"></canvas>
</div>

<!-- 🌾 NEW: Top 5 Farmers by Total Sales -->
<div class="chart-container">
  <h3>🌾 Top 5 Farmers by Total Sales</h3>
  <canvas id="farmerChart" height="120"></canvas>
</div>

<script>
// 🌙 Toggle Dark Mode
const btn = document.getElementById('toggleTheme');
btn.onclick = () => {
  document.body.classList.toggle('dark-mode');
  btn.textContent = document.body.classList.contains('dark-mode') ? "☀️" : "🌙";
};

// Top Products
new Chart(document.getElementById('topProductsChart').getContext('2d'), {
    type: 'bar',
    data: {
        labels: [<?php 
            $labels=[]; $values=[];
            $top_products->data_seek(0);
            while($row=$top_products->fetch_assoc()){ 
                $labels[]="'".$row['name']."'";
                $values[]=$row['total_sold'];
            }
            echo implode(",",$labels);
        ?>],
        datasets: [{
            label: 'Units Sold',
            data: [<?php echo implode(",",$values); ?>],
            backgroundColor: '#2e7d32'
        }]
    },
    options: { responsive:true, scales:{ y:{ beginAtZero:true } } }
});

// Daily Revenue
new Chart(document.getElementById('salesChart').getContext('2d'), {
  type: 'line',
  data: {
    labels: <?php echo json_encode($dates); ?>,
    datasets: [{
      label: 'Revenue (₹)',
      data: <?php echo json_encode($totals); ?>,
      borderColor: '#66bb6a',
      backgroundColor: 'rgba(102,187,106,0.2)',
      fill: true,
      tension: 0.4
    }]
  },
  options: { responsive:true, scales:{ y:{ beginAtZero:true } } }
});

// User Roles
new Chart(document.getElementById('userChart'), {
  type: 'pie',
  data: {
    labels: <?php echo json_encode($role_labels); ?>,
    datasets: [{
      data: <?php echo json_encode($role_counts); ?>,
      backgroundColor: ['#4caf50','#81c784','#a5d6a7','#c8e6c9']
    }]
  }
});

// 🌾 Top Farmers by Total Sales
new Chart(document.getElementById('farmerChart').getContext('2d'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($farmer_names); ?>,
        datasets: [{
            label: 'Revenue (₹)',
            data: <?php echo json_encode($farmer_sales); ?>,
            backgroundColor: '#388e3c'
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: { beginAtZero: true }
        }
    }
});
</script>
</body>
</html>
