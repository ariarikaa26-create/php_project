<?php
session_start();
require_once "config.php";

// 🧩 Login check
if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $role = trim($_POST['role']);

    // ✅ Validation
    if (empty($email) || empty($password) || empty($role)) {
        header("Location: login.php?error=invalid");
        exit();
    }

    // ✅ Fetch user by email + role
    $stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE email=? AND role=?");
    $stmt->bind_param("ss", $email, $role);
    $stmt->execute();
    $result = $stmt->get_result();

    // ✅ Check user & password validity
    if ($row = $result->fetch_assoc()) {
        $db_password = $row['password'];
        $valid = false;

        // Allow both plain and hashed passwords
        if ($password === $db_password || password_verify($password, $db_password)) {
            $valid = true;
        }

        if ($valid) {
            // ✅ Set session data
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['name'] = $row['name'];
            $_SESSION['role'] = ucfirst(strtolower($row['role']));

            // ✅ Redirect by role
            switch ($row['role']) {
                case 'Farmer':
                    header("Location: farmer_upload.php");
                    break;
                case 'Trader':
                    header("Location: trader_dashboard.php");
                    break;
                case 'Retailer':
                    header("Location: retailer_dashboard.php");
                    break;
                case 'Consumer':
                    header("Location: dashboard.php");
                    break;
                case 'Admin':
                    header("Location: admin_panel.php");
                    break;
                default:
                    header("Location: login.php?error=invalid");
                    break;
            }
            exit();
        } else {
            // ❌ Wrong password
            header("Location: login.php?error=invalid");
            exit();
        }
    } else {
        // ❌ No user found
        header("Location: login.php?error=invalid");
        exit();
    }
} else {
    header("Location: login.php?error=unauthorized");
    exit();
}
?>
