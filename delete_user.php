<?php
session_start();
include("config.php");

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin'){
    header("Location: login.php");
    exit;
}

if(isset($_POST['id'])){
    $id = intval($_POST['id']);
    $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}
header("Location: admin_panel.php#users");
exit;
?>
