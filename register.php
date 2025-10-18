<?php
session_start();
require_once "config.php";

$error = "";
$success = "";

// ✅ When user submits form
if (isset($_POST['register'])) {
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);
    $role     = trim($_POST['role']);

    // ✅ Basic validation
    if (empty($name) || empty($email) || empty($password) || empty($role)) {
        $error = "❌ All fields are required!";
    } elseif (strlen($password) < 3) {
        $error = "❌ Password must be at least 3 characters!";
    } else {
        // ✅ Check if email already exists
        $check = $conn->prepare("SELECT id FROM users WHERE email=?");
        $check->bind_param("s", $email);
        $check->execute();
        $res = $check->get_result();

        if ($res->num_rows > 0) {
            $error = "❌ Email already registered!";
        } else {
            // ⚠️ Store password directly (NO HASHING)
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $password, $role);

            if ($stmt->execute()) {
                $success = "✅ Registration successful! You can now login.";
            } else {
                $error = "❌ Database error: " . $conn->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Register - Farm2Home</title>
<style>
body {
    font-family: Arial, sans-serif;
    background: #76b852;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    margin: 0;
}
.container {
    background: #fff;
    padding: 35px;
    border-radius: 10px;
    width: 350px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}
h2 {
    text-align: center;
    color: #2e7d32;
    margin-bottom: 20px;
}
input, select {
    width: 100%;
    padding: 10px;
    margin: 8px 0;
    border: 1px solid #ccc;
    border-radius: 6px;
}
button {
    width: 100%;
    padding: 12px;
    background: #2e7d32;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 16px;
    cursor: pointer;
    margin-top: 10px;
}
button:hover { background: #1b5e20; }
.error { color: red; text-align: center; font-weight: bold; }
.success { color: green; text-align: center; font-weight: bold; }
a { color: #2e7d32; text-decoration: none; font-weight: bold; }
a:hover { text-decoration: underline; }
</style>
</head>
<body>
<div class="container">
    <h2>📝 Register</h2>

    <?php if ($error): ?><p class="error"><?= $error ?></p><?php endif; ?>
    <?php if ($success): ?><p class="success"><?= $success ?></p><?php endif; ?>

    <form method="post">
        <input type="text" name="name" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email Address" required>
        <input type="password" name="password" placeholder="Password" required>
        <select name="role" required>
            <option value="">-- Select Role --</option>
            <option value="Consumer">Consumer</option>
            <option value="Farmer">Farmer</option>
            <option value="Trader">Trader</option>
            <option value="Retailer">Retailer</option>
            <option value="Admin">Admin</option>
        </select>
        <button type="submit" name="register">Register</button>
    </form>

    <p style="text-align:center; margin-top:10px;">
        Already have an account? <a href="login.php">Login here</a>
    </p>
</div>
</body>
</html>
