<?php
session_start();
$conn = new mysqli("localhost", "root", "", "organic_harvest_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = "";

// Login check
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, name, password FROM farmers WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if ($password === $row['password']) { // plain text check for now
            $_SESSION['farmer_id'] = $row['id'];
            $_SESSION['farmer_name'] = $row['name'];
            header("Location: farmer_upload.php");
            exit();
        } else {
            $error = "❌ Invalid password!";
        }
    } else {
        $error = "❌ Farmer not found!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmer Login - Farm2Home</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #2e7d32; /* ✅ Green background */
            display:flex;
            justify-content:center;
            align-items:center;
            height:100vh;
            margin:0;
        }
        .login-box {
            background:white;
            padding:30px;
            border-radius:10px;
            box-shadow:0 4px 15px rgba(0,0,0,0.2);
            width:350px;
            text-align:center;
        }
        .login-box h2 {
            color:#2e7d32;
            margin-bottom:20px;
        }
        .login-box input {
            width:100%;
            padding:10px;
            margin:10px 0;
            border:1px solid #ccc;
            border-radius:5px;
        }
        .login-box button {
            background:#2e7d32;
            color:white;
            padding:10px;
            width:100%;
            border:none;
            border-radius:5px;
            cursor:pointer;
            font-size:16px;
            font-weight:bold;
        }
        .login-box button:hover {
            background:#1b5e20;
        }
        .error {
            color:red;
            margin-bottom:10px;
        }
        .note {
            margin-top:15px;
            font-size:14px;
        }
        .note a {
            color:#2e7d32;
            text-decoration:none;
            font-weight:bold;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>🌾 Farmer Login</h2>
        <?php if ($error) echo "<p class='error'>$error</p>"; ?>
        <form method="post">
            <input type="text" name="email" placeholder="Enter Email" required>
            <input type="password" name="password" placeholder="Enter Password" required>
            <button type="submit" name="login">Login</button>
        </form>
        <p class="note">Not registered? <a href="farmer_register.php">Sign Up</a></p>
    </div>
</body>
</html>
