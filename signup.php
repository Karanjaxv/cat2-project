<?php
include 'config.php';
$msg = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $u = trim($_POST['username']);
    $e = trim($_POST['email']);
    $hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username,email,password) VALUES (?,?,?)");
    $stmt->bind_param("sss", $u, $e, $hash);
    $msg = $stmt->execute()
        ? "<p class='ok'>Account created for " . htmlspecialchars($u) . "</p>"
        : "<p class='err'>Error: username already exists</p>";
}
?>
<!DOCTYPE html><html><head><title>Sign up</title><link rel="stylesheet" href="style.css"></head><body>
<nav><a href="index.php">Home</a><a href="signup.php">Sign up</a><a href="login.php">Login</a><a href="feedback.php">Feedback</a></nav>
<div class="box"><h2>Sign up</h2><?= $msg ?>
<form method="post">
<input name="username" placeholder="Username" required>
<input type="email" name="email" placeholder="Email" required>
<input type="password" name="password" placeholder="Password" required>
<button>Register</button></form></div></body></html>
