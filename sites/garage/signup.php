<?php
require __DIR__ . '/../../includes/bootstrap.php';
$pageTitle = 'Create Account';
$errors = [];
$old = ['full_name' => '', 'username' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_ok()) {
        $errors[] = 'Your session expired. Please try again.';
    }
    $old['full_name'] = trim($_POST['full_name'] ?? '');
    $old['username']  = trim($_POST['username'] ?? '');
    $old['email']     = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';

    if (!preg_match("/^[A-Za-z][A-Za-z' -]{1,98}$/", $old['full_name'])) {
        $errors[] = 'Enter your full name (letters, spaces, hyphens and apostrophes only).';
    }
    if (!preg_match('/^[A-Za-z0-9_]{3,30}$/', $old['username'])) {
        $errors[] = 'Username must be 3-30 characters: letters, numbers or underscores.';
    }
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL) || strlen($old['email']) > 100) {
        $errors[] = 'Enter a valid email address.';
    }
    if (strlen($password) < 10 || strlen($password) > 72) {
        $errors[] = 'Password must be 10 to 72 characters long.';
    } elseif (!preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $errors[] = 'Password must contain both letters and numbers.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }

    if (!$errors) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        try {
            $stmt = $conn->prepare('INSERT INTO users (full_name, username, email, password_hash) VALUES (?, ?, ?, ?)');
            $stmt->bind_param('ssss', $old['full_name'], $old['username'], $old['email'], $hash);
            $stmt->execute();
            header('Location: /login.php?registered=1');
            exit;
        } catch (mysqli_sql_exception $ex) {
            $errors[] = ($ex->getCode() === 1062)
                ? 'That username or email is already registered.'
                : 'Could not create your account. Please try again.';
        }
    }
}
require __DIR__ . '/../../includes/header.php';
?>
<form method="POST" novalidate="false">
  <h2>Create your account</h2>
  <?php foreach ($errors as $err): ?><div class="error"><?= e($err) ?></div><?php endforeach; ?>
  <?= csrf_field() ?>
  <label>Full name
    <input type="text" name="full_name" required maxlength="100" autocomplete="name" value="<?= e($old['full_name']) ?>">
  </label>
  <label>Username
    <input type="text" name="username" required minlength="3" maxlength="30" pattern="[A-Za-z0-9_]{3,30}" autocomplete="username" value="<?= e($old['username']) ?>">
  </label>
  <label>Email
    <input type="email" name="email" required maxlength="100" autocomplete="email" value="<?= e($old['email']) ?>">
  </label>
  <label>Password (10+ characters, letters and numbers)
    <input type="password" name="password" required minlength="10" maxlength="72" autocomplete="new-password">
  </label>
  <label>Confirm password
    <input type="password" name="confirm" required minlength="10" maxlength="72" autocomplete="new-password">
  </label>
  <br><button class="btn" type="submit">Create account</button>
  <p>Already registered? <a href="/login.php">Log in</a></p>
</form>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
