<?php
require __DIR__ . '/../../includes/bootstrap.php';
require_login();
$pageTitle = 'My Garage';
$errors = [];
$uid = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $make  = trim($_POST['make'] ?? '');
    $model = trim($_POST['model'] ?? '');
    $year  = filter_var($_POST['year'] ?? '', FILTER_VALIDATE_INT);
    $plate = strtoupper(trim($_POST['plate'] ?? ''));

    if (!csrf_ok()) { $errors[] = 'Your session expired. Please try again.'; }
    if (!preg_match('/^[A-Za-z0-9 .-]{2,50}$/', $make))  { $errors[] = 'Enter a valid make.'; }
    if (!preg_match('/^[A-Za-z0-9 .-]{1,50}$/', $model)) { $errors[] = 'Enter a valid model.'; }
    if ($year === false || $year < 1950 || $year > (int)date('Y') + 1) { $errors[] = 'Enter a valid year.'; }
    if (!preg_match('/^[A-Z0-9 -]{3,15}$/', $plate))     { $errors[] = 'Enter a valid number plate.'; }

    if (!$errors) {
        $stmt = $conn->prepare('INSERT INTO vehicles (user_id, make, model, year, plate) VALUES (?, ?, ?, ?, ?)');
        $stmt->bind_param('issis', $uid, $make, $model, $year, $plate);
        $stmt->execute();
        header('Location: /');
        exit;
    }
}

$stmt = $conn->prepare('SELECT make, model, year, plate FROM vehicles WHERE user_id = ? ORDER BY id DESC');
$stmt->bind_param('i', $uid);
$stmt->execute();
$vehicles = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

require __DIR__ . '/../../includes/header.php';
?>
<section>
  <h2>Welcome back, <?= e($_SESSION['username']) ?></h2>
  <form method="POST" action="/logout.php" style="max-width:none;padding:12px;margin:10px 0">
    <?= csrf_field() ?><button class="btn" type="submit">Log out</button>
  </form>

  <h2>Your vehicles</h2>
  <?php if (!$vehicles): ?>
    <p>No vehicles yet. Add your first one below.</p>
  <?php else: ?>
    <div class="grid">
    <?php foreach ($vehicles as $v): ?>
      <div class="card">
        <h3><?= e($v['make'] . ' ' . $v['model']) ?></h3>
        <p><?= (int)$v['year'] ?> &middot; <?= e($v['plate']) ?></p>
      </div>
    <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <form method="POST">
    <h2>Add a vehicle</h2>
    <?php foreach ($errors as $err): ?><div class="error"><?= e($err) ?></div><?php endforeach; ?>
    <?= csrf_field() ?>
    <label>Make<input type="text" name="make" required maxlength="50"></label>
    <label>Model<input type="text" name="model" required maxlength="50"></label>
    <label>Year<input type="number" name="year" required min="1950" max="<?= (int)date('Y') + 1 ?>"></label>
    <label>Number plate<input type="text" name="plate" required minlength="3" maxlength="15" pattern="[A-Za-z0-9 \-]{3,15}"></label>
    <br><button class="btn" type="submit">Add vehicle</button>
  </form>
</section>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
