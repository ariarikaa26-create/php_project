<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Farm2Home - Login</title>
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
.login-container {
    background: white;
    padding: 40px;
    border-radius: 12px;
    box-shadow: 0px 4px 12px rgba(0,0,0,0.2);
    width: 350px;
    text-align: center;
}
h2 {
    margin-bottom: 20px;
    color: #2e7d32;
}
input, select {
    width: 100%;
    padding: 12px;
    margin: 8px 0;
    border-radius: 8px;
    border: 1px solid #ccc;
}
button {
    width: 100%;
    padding: 12px;
    background: #2e7d32;
    color: white;
    font-size: 16px;
    font-weight: bold;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    margin-top: 10px;
}
button:hover { background: #1b5e20; }
.error {
    color: red;
    margin-top: 10px;
    font-weight: bold;
}
.warning {
    color: orange;
    margin-top: 10px;
    font-weight: bold;
}
p.register {
    margin-top: 20px;
}
p.register a {
    color: #2e7d32;
    font-weight: bold;
    text-decoration: none;
}
p.register a:hover {
    text-decoration: underline;
}
.logo {
    width: 100px;
    height: 100px;
    margin-bottom: 10px;
}
</style>
<script>
// ✅ JavaScript Validation Function
function validateLoginForm() {
    let email = document.forms["loginForm"]["email"].value.trim();
    let password = document.forms["loginForm"]["password"].value.trim();
    let role = document.forms["loginForm"]["role"].value;

    // Email validation pattern
    const emailPattern = /^[a-zA-Z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$/;

    // Validation checks
    if (email === "" || password === "" || role === "") {
        alert("⚠️ Please fill all fields before logging in.");
        return false;
    }
    if (!emailPattern.test(email)) {
        alert("❌ Please enter a valid email address.");
        return false;
    }
    if (password.length < 6) {
        alert("⚠️ Password must be at least 6 characters long.");
        return false;
    }
    return true;
}
</script>
</head>
<body>
<div class="login-container">

    <!-- ✅ Logo added here -->
    <img src="images/logo1.png" alt="Farm2Home Logo" class="logo">

    <h2>Login</h2>

    <!-- ✅ Display dynamic error messages -->
   <?php
if (isset($_GET['error'])) {
    if ($_GET['error'] === 'invalid') {
        echo "<p class='error'>❌ Invalid Email or Password!</p>";
    } elseif ($_GET['error'] === 'unauthorized') {
        echo "<p class='error'>⚠️ Unauthorized access. Please login again!</p>";
    }
}

if (isset($_GET['msg']) && $_GET['msg'] === 'please_login') {
    echo "<p class='warning'>⚠️ Please login to continue.</p>";
}

if (isset($_SESSION['success_msg'])) {
    echo "<p style='color:green;font-weight:bold;'>" . $_SESSION['success_msg'] . "</p>";
    unset($_SESSION['success_msg']);
}
?>

    <!-- ✅ Login Form -->
    <form method="post" action="login_process.php">
        <input type="email" name="email" placeholder="Enter Email" required>
        <input type="password" name="password" placeholder="Enter Password" required>

        <select name="role" required>
            <option value="">-- Select Role --</option>
            <option value="Farmer">Farmer</option>
            <option value="Consumer">Consumer</option>
            <option value="Trader">Trader</option>
            <option value="Retailer">Retailer</option>
            <option value="Admin">Admin</option>
        </select>

        <button type="submit" name="login">Login</button>
    </form>

    <p class="register">New user? <a href="register.php">Register here</a></p>
</div>
</body>
</html>
