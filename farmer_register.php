<?php
// register.php
error_reporting(E_ALL); ini_set('display_errors',1);
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $phone = $_POST['phone'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) die('Invalid email');

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (name, email, password_hash, phone) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $hash, $phone);
    if ($stmt->execute()) {
        echo "Registered. User id: " . $stmt->insert_id;
    } else {
        echo "Register failed: " . $stmt->error;
    }
}
?>
<form method="post">
<input name="name" required><br>
<input name="email" type="email" required><br>
<input name="password" type="password" required><br>
<input name="phone"><br>
<button>Register</button>
</form>
