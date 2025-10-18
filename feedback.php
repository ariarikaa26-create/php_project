<?php
session_start();
require_once "config.php";

$msg = "";

// ✅ Insert feedback on submit
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $message = trim($_POST["message"]);
    $rating = intval($_POST["rating"]);
    $user_id = $_SESSION['user_id'] ?? 0;

    if (!empty($message) && $rating > 0) {
        $stmt = $conn->prepare("INSERT INTO feedback (user_id, name, email, message, rating) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("isssi", $user_id, $name, $email, $message, $rating);
        if ($stmt->execute()) {
            $msg = "✅ Thank you for your feedback!";
        } else {
            $msg = "❌ Something went wrong. Try again.";
        }
        $stmt->close();
    } else {
        $msg = "⚠ Please enter a message and select a rating.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Feedback - Farm2Home</title>
<style>
body { font-family: Arial, sans-serif; background:#f9f9f9; padding:30px; text-align:center; }
h2 { color:#2e7d32; }
form { background:#fff; padding:25px; max-width:450px; margin:0 auto; border-radius:10px; box-shadow:0 4px 12px rgba(0,0,0,0.1); }
input, textarea, select { width:90%; padding:10px; margin:10px 0; border:1px solid #ccc; border-radius:6px; font-size:15px; }
button { background:#2e7d32; color:white; padding:10px 20px; border:none; border-radius:6px; cursor:pointer; font-weight:bold; }
button:hover { background:#1b5e20; }
.msg { font-weight:bold; margin-bottom:15px; }
footer { margin-top:40px; color:#666; }
</style>
</head>
<body>
<h2 style="color:#2e7d32; font-size:28px; margin-bottom:20px; display:flex; justify-content:center; align-items:center; gap:10px;">
  <img src="images/logo1.png" alt="Farm2Home Logo" style="width:45px; height:45px; border-radius:8px; vertical-align:middle;">
  What Our Users Say
</h2>

<p>Please share your experience with Farm2Home.</p>

<?php if ($msg): ?>
  <p class="msg"><?= $msg ?></p>
<?php endif; ?>

<form method="POST" action="">
    <input type="text" name="name" placeholder="Your Name" value="<?= $_SESSION['name'] ?? '' ?>"><br>
    <input type="email" name="email" placeholder="Your Email" value=""><br>
    <textarea name="message" rows="4" placeholder="Write your feedback here..." required></textarea><br>

    <label for="rating">Rate Us:</label><br>
    <select name="rating" required>
        <option value="">--Select Rating--</option>
        <option value="5">⭐⭐⭐⭐⭐ Excellent</option>
        <option value="4">⭐⭐⭐⭐ Good</option>
        <option value="3">⭐⭐⭐ Average</option>
        <option value="2">⭐⭐ Poor</option>
        <option value="1">⭐ Very Poor</option>
    </select><br>

    <button type="submit">Submit Feedback</button>
</form>

<footer>
    <p>Farm2Home &copy; 2025 | Thank you for supporting local farmers 🌾</p>
</footer>

</body>
</html>
