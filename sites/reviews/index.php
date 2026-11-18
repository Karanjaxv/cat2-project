<?php
require __DIR__ . '/../../includes/bootstrap.php';
$pageTitle = 'Reviews';
$errors = [];
$loggedIn = !empty($_SESSION['user_id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$loggedIn) {
        $errors[] = 'Please log in to leave a review.';
    } else {
        $uid     = $_SESSION['user_id'];
        $rating  = filter_var($_POST['rating'] ?? '', FILTER_VALIDATE_INT);
        $comment = trim($_POST['comment'] ?? '');

        if (!csrf_ok()) { $errors[] = 'Your session expired. Please try again.'; }
        if ($rating === false || $rating < 1 || $rating > 5) { $errors[] = 'Choose a rating from 1 to 5.'; }
        $len = mb_strlen($comment);
        if ($len < 10 || $len > 500) { $errors[] = 'Your review must be 10 to 500 characters.'; }

        if (!$errors) {
            $stmt = $conn->prepare('SELECT COUNT(*) FROM reviews WHERE user_id = ? AND created_at > (NOW() - INTERVAL 1 DAY)');
            $stmt->bind_param('i', $uid);
            $stmt->execute();
            $recent = (int)$stmt->get_result()->fetch_row()[0];
            if ($recent >= 5) {
                $errors[] = 'You have reached the daily review limit. Please try again tomorrow.';
            }
        }
        if (!$errors) {
            $stmt = $conn->prepare('INSERT INTO reviews (user_id, rating, comment) VALUES (?, ?, ?)');
            $stmt->bind_param('iis', $uid, $rating, $comment);
            $stmt->execute();
            header('Location: /?thanks=1');
            exit;
        }
    }
}

$stats = $conn->query('SELECT COUNT(*) AS n, AVG(rating) AS avg FROM reviews')->fetch_assoc();
$list = $conn->query(
    'SELECT r.rating, r.comment, r.created_at, u.username
     FROM reviews r JOIN users u ON u.id = r.user_id
     ORDER BY r.id DESC LIMIT 20'
)->fetch_all(MYSQLI_ASSOC);

require __DIR__ . '/../../includes/header.php';
?>
<section>
  <h2>What our customers say</h2>
  <?php if ((int)$stats['n'] > 0): ?>
    <p><strong><?= number_format((float)$stats['avg'], 1) ?> / 5</strong> from <?= (int)$stats['n'] ?> review<?= (int)$stats['n'] === 1 ? '' : 's' ?></p>
  <?php else: ?>
    <p>No reviews yet. Be the first.</p>
  <?php endif; ?>

  <div class="grid">
    <?php foreach ($list as $r): ?>
      <div class="card">
        <h3><?= str_repeat('&#9733;', (int)$r['rating']) . str_repeat('&#9734;', 5 - (int)$r['rating']) ?></h3>
        <p><?= e($r['comment']) ?></p>
        <p style="color:var(--muted);font-size:.85rem">&mdash; <?= e($r['username']) ?>, <?= e(substr($r['created_at'], 0, 10)) ?></p>
      </div>
    <?php endforeach; ?>
  </div>

  <?php if (isset($_GET['thanks'])): ?><div class="ok">Thank you for your review!</div><?php endif; ?>

  <?php if ($loggedIn): ?>
  <form method="POST">
    <h2>Leave a review</h2>
    <?php foreach ($errors as $err): ?><div class="error"><?= e($err) ?></div><?php endforeach; ?>
    <?= csrf_field() ?>
    <label>Rating
      <select name="rating" required>
        <option value="">Choose...</option>
        <option value="5">5 - Excellent</option>
        <option value="4">4 - Good</option>
        <option value="3">3 - Okay</option>
        <option value="2">2 - Poor</option>
        <option value="1">1 - Bad</option>
      </select>
    </label>
    <label>Your review (10-500 characters)
      <textarea name="comment" rows="4" required minlength="10" maxlength="500"></textarea>
    </label>
    <br><button class="btn" type="submit">Post review</button>
  </form>
  <?php else: ?>
    <p><a class="btn" href="//garage.hashvault.autos/login.php">Log in to leave a review</a></p>
  <?php endif; ?>
</section>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
