<?php
session_start();
include 'config.php';
$msg = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $stmt = $conn->prepare("SELECT password FROM users WHERE username=?");
    $stmt->bind_param("s", $_POST['username']);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    if ($row && password_verify($_POST['password'], $row['password'])) {
        $_SESSION['user'] = $_POST['username'];
        $msg = "<p class='ok'>Login successful. Welcome, " . htmlspecialchars($_SESSION['user']) . "!</p>";
    } else {
        $msg = "<p class='err'>Invalid username or password</p>";
    }
}
?>
<!DOCTYPE html><html><head><title>Login</title><link rel="stylesheet" href="style.css"></head><body>
<nav><a href="index.php">Home</a><a href="signup.php">Sign up</a><a href="login.php">Login</a><a href="feedback.php">Feedback</a></nav>
<div class="box"><h2>Login</h2><?= $msg ?>
<form method="post">
<input name="username" placeholder="Username" required>
<input type="password" name="password" placeholder="Password" required>
<button>Login</button></form></div></body></html>
