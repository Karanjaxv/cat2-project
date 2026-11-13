<?php
include 'config.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $s = $conn->prepare("INSERT INTO feedback (name,email,message) VALUES (?,?,?)");
    $s->bind_param("sss", $_POST['name'], $_POST['email'], $_POST['message']);
    $s->execute();
}
$rows = $conn->query("SELECT * FROM feedback ORDER BY id DESC");
?>
<!DOCTYPE html><html><head><title>Feedback</title><link rel="stylesheet" href="style.css"></head><body>
<nav><a href="index.php">Home</a><a href="signup.php">Sign up</a><a href="login.php">Login</a><a href="feedback.php">Feedback</a></nav>
<div class="box" style="max-width:700px"><h2>Feedback</h2>
<form method="post">
<input name="name" placeholder="Your name" required>
<input type="email" name="email" placeholder="Email" required>
<textarea name="message" placeholder="Message" required></textarea>
<button>Submit</button></form>
<h3>Submissions</h3>
<table><tr><th>Name</th><th>Email</th><th>Message</th><th>Time</th></tr>
<?php foreach ($rows as $r): ?>
<tr><td><?= htmlspecialchars($r['name']) ?></td><td><?= htmlspecialchars($r['email']) ?></td><td><?= htmlspecialchars($r['message']) ?></td><td><?= $r['submitted_at'] ?></td></tr>
<?php endforeach; ?></table></div></body></html>
