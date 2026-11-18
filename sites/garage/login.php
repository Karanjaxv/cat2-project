<?php
require __DIR__ . '/../../includes/bootstrap.php';
$pageTitle = 'Log In';
$error = '';
$notice = isset($_GET['registered']) ? 'Account created. Please log in.' : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!csrf_ok()) {
        $error = 'Your session expired. Please try again.';
    } else {
        $stmt = $conn->prepare(
            'SELECT id, password_hash, role, failed_attempts,
                    (locked_until IS NOT NULL AND locked_until > NOW()) AS is_locked
             FROM users WHERE username = ?'
        );
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        if ($row && $row['is_locked']) {
            $error = 'Too many failed attempts. Please try again in 15 minutes.';
        } elseif ($row && password_verify($password, $row['password_hash'])) {
            $stmt = $conn->prepare('UPDATE users SET failed_attempts = 0, locked_until = NULL WHERE id = ?');
            $stmt->bind_param('i', $row['id']);
            $stmt->execute();
            session_regenerate_id(true);
            $_SESSION['user_id']  = (int)$row['id'];
            $_SESSION['username'] = $username;
            $_SESSION['role']     = $row['role'];
            header('Location: /');
            exit;
        } else {
            if ($row) {
                $attempts = (int)$row['failed_attempts'] + 1;
                if ($attempts >= 5) {
                    $stmt = $conn->prepare('UPDATE users SET failed_attempts = 0, locked_until = DATE_ADD(NOW(), INTERVAL 15 MINUTE) WHERE id = ?');
                    $stmt->bind_param('i', $row['id']);
                } else {
                    $stmt = $conn->prepare('UPDATE users SET failed_attempts = ? WHERE id = ?');
                    $stmt->bind_param('ii', $attempts, $row['id']);
                }
                $stmt->execute();
            }
            $error = 'Invalid username or password.';
        }
    }
}
require __DIR__ . '/../../includes/header.php';
?>
<form method="POST">
  <h2>Log in to My Garage</h2>
  <?php if ($notice): ?><div class="ok"><?= e($notice) ?></div><?php endif; ?>
  <?php if ($error): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
  <?= csrf_field() ?>
  <label>Username
    <input type="text" name="username" required maxlength="30" autocomplete="username">
  </label>
  <label>Password
    <input type="password" name="password" required maxlength="72" autocomplete="current-password">
  </label>
  <br><button class="btn" type="submit">Log in</button>
  <p>New here? <a href="/signup.php">Create an account</a></p>
</form>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
