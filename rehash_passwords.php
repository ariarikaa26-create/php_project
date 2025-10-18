<?php
require_once "config.php";

$result = $conn->query("SELECT id, password FROM users");
while ($row = $result->fetch_assoc()) {
    $id = $row['id'];
    $pass = $row['password'];

    // Skip already-hashed passwords
    if (strpos($pass, '$2y$') !== 0) {
        $hashed = password_hash($pass, PASSWORD_BCRYPT);
        $update = $conn->prepare("UPDATE users SET password=? WHERE id=?");
        $update->bind_param("si", $hashed, $id);
        $update->execute();
        echo "Updated ID $id<br>";
    }
}

echo "✅ All plain passwords converted to secure hashes!";
?>
