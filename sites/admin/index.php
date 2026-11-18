<?php
require __DIR__ . '/../../includes/bootstrap.php';
require_login();

$uid = (int)$_SESSION['user_id'];
$stmt = $conn->prepare('SELECT role FROM users WHERE id = ?');
$stmt->bind_param('i', $uid);
$stmt->execute();
$me = $stmt->get_result()->fetch_assoc();

if (!$me || $me['role'] !== 'staff') {
    http_response_code(403);
    $pageTitle = 'Access denied';
    require __DIR__ . '/../../includes/header.php';
    echo '<section class="hero"><h1>Staff only</h1><p>You do not have permission to view this page.</p></section>';
    require __DIR__ . '/../../includes/footer.php';
    exit;
}

function audit(mysqli $conn, int $uid, string $action, string $detail): void {
    $s = $conn->prepare('INSERT INTO audit_log (user_id, action, detail) VALUES (?, ?, ?)');
    $s->bind_param('iss', $uid, $action, $detail);
    $s->execute();
}

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $do = $_POST['do'] ?? '';
    $id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);
    if (!csrf_ok() || $id === false) {
        $msg = 'Your session expired or the request was invalid. Please try again.';
    } elseif ($do === 'status') {
        $status = $_POST['status'] ?? '';
        if (in_array($status, ['pending', 'confirmed', 'done'], true)) {
            $s = $conn->prepare('UPDATE bookings SET status = ? WHERE id = ?');
            $s->bind_param('si', $status, $id);
            $s->execute();
            audit($conn, $uid, 'booking_status', 'booking ' . $id . ' set to ' . $status);
            header('Location: /?updated=1');
            exit;
        }
        $msg = 'Invalid status.';
    } elseif ($do === 'delete_review') {
        $s = $conn->prepare('DELETE FROM reviews WHERE id = ?');
        $s->bind_param('i', $id);
        $s->execute();
        audit($conn, $uid, 'review_deleted', 'review ' . $id . ' removed');
        header('Location: /?updated=1');
        exit;
    }
}

$stats = $conn->query("SELECT
    (SELECT COUNT(*) FROM users) AS users,
    (SELECT COUNT(*) FROM bookings) AS bookings,
    (SELECT COUNT(*) FROM bookings WHERE status = 'pending') AS pending,
    (SELECT COUNT(*) FROM reviews) AS reviews")->fetch_assoc();

$bookings = $conn->query(
    "SELECT b.id, b.service_type, b.preferred_date, b.status, b.notes,
            u.full_name, v.make, v.model, v.plate
     FROM bookings b
     JOIN users u ON u.id = b.user_id
     JOIN vehicles v ON v.id = b.vehicle_id
     ORDER BY b.preferred_date ASC, b.id DESC LIMIT 50"
)->fetch_all(MYSQLI_ASSOC);

$reviews = $conn->query(
    "SELECT r.id, r.rating, r.comment, u.username
     FROM reviews r JOIN users u ON u.id = r.user_id
     ORDER BY r.id DESC LIMIT 10"
)->fetch_all(MYSQLI_ASSOC);

$logs = $conn->query(
    "SELECT a.action, a.detail, a.created_at, u.username
     FROM audit_log a JOIN users u ON u.id = a.user_id
     ORDER BY a.id DESC LIMIT 8"
)->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Staff Dashboard';
require __DIR__ . '/../../includes/header.php';
?>
<section>
  <h2>Staff dashboard</h2>
  <?php if (isset($_GET['updated'])): ?><div class="ok">Changes saved.</div><?php endif; ?>
  <?php if ($msg): ?><div class="error"><?= e($msg) ?></div><?php endif; ?>

  <div class="grid">
    <div class="card"><h3><?= (int)$stats['users'] ?></h3><p>Registered accounts</p></div>
    <div class="card"><h3><?= (int)$stats['bookings'] ?></h3><p>Total bookings</p></div>
    <div class="card"><h3><?= (int)$stats['pending'] ?></h3><p>Waiting for confirmation</p></div>
    <div class="card"><h3><?= (int)$stats['reviews'] ?></h3><p>Customer reviews</p></div>
  </div>

  <h2>Bookings</h2>
  <?php if (!$bookings): ?>
    <p>No bookings yet.</p>
  <?php else: ?>
  <div class="tablewrap">
    <table>
      <tr><th>Customer</th><th>Vehicle</th><th>Service</th><th>Date</th><th>Notes</th><th>Status</th></tr>
      <?php foreach ($bookings as $b): ?>
      <tr>
        <td><?= e($b['full_name']) ?></td>
        <td><?= e($b['make'] . ' ' . $b['model'] . ' (' . $b['plate'] . ')') ?></td>
        <td><?= e($b['service_type']) ?></td>
        <td><?= e($b['preferred_date']) ?></td>
        <td><?= e((string)($b['notes'] ?? '')) ?></td>
        <td>
          <form class="inline" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="do" value="status">
            <input type="hidden" name="id" value="<?= (int)$b['id'] ?>">
            <select name="status">
              <?php foreach (['pending', 'confirmed', 'done'] as $st): ?>
                <option value="<?= $st ?>"<?= $b['status'] === $st ? ' selected' : '' ?>><?= ucfirst($st) ?></option>
              <?php endforeach; ?>
            </select>
            <button class="btn" type="submit">Save</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
    </table>
  </div>
  <?php endif; ?>

  <h2>Recent reviews</h2>
  <?php if (!$reviews): ?>
    <p>No reviews yet.</p>
  <?php else: ?>
  <div class="tablewrap">
    <table>
      <tr><th>User</th><th>Rating</th><th>Comment</th><th></th></tr>
      <?php foreach ($reviews as $r): ?>
      <tr>
        <td><?= e($r['username']) ?></td>
        <td><?= (int)$r['rating'] ?>/5</td>
        <td><?= e($r['comment']) ?></td>
        <td>
          <form class="inline" method="POST" onsubmit="return confirm('Delete this review?')">
            <?= csrf_field() ?>
            <input type="hidden" name="do" value="delete_review">
            <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
            <button class="btn" type="submit">Delete</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
    </table>
  </div>
  <?php endif; ?>

  <h2>Recent staff activity</h2>
  <?php if (!$logs): ?>
    <p>No activity recorded yet.</p>
  <?php else: ?>
  <div class="tablewrap">
    <table>
      <tr><th>When</th><th>Who</th><th>Action</th><th>Detail</th></tr>
      <?php foreach ($logs as $l): ?>
      <tr>
        <td><?= e($l['created_at']) ?></td>
        <td><?= e($l['username']) ?></td>
        <td><?= e($l['action']) ?></td>
        <td><?= e((string)$l['detail']) ?></td>
      </tr>
      <?php endforeach; ?>
    </table>
  </div>
  <?php endif; ?>
</section>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
