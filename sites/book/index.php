<?php
require __DIR__ . '/../../includes/bootstrap.php';
require_login();
$pageTitle = 'Book a Service';
$uid = $_SESSION['user_id'];
$errors = [];
$services = ['Full Service', 'Oil Change', 'Diagnostics', 'Tyres & Brakes', 'Wheel Alignment', 'Inspection'];

$stmt = $conn->prepare('SELECT id, make, model, plate FROM vehicles WHERE user_id = ? ORDER BY id DESC');
$stmt->bind_param('i', $uid);
$stmt->execute();
$vehicles = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$ownedIds = array_map('intval', array_column($vehicles, 'id'));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vid     = filter_var($_POST['vehicle_id'] ?? '', FILTER_VALIDATE_INT);
    $service = $_POST['service'] ?? '';
    $date    = $_POST['date'] ?? '';
    $notes   = trim($_POST['notes'] ?? '');

    if (!csrf_ok()) { $errors[] = 'Your session expired. Please try again.'; }
    if ($vid === false || !in_array($vid, $ownedIds, true)) { $errors[] = 'Choose one of your own vehicles.'; }
    if (!in_array($service, $services, true)) { $errors[] = 'Choose a service from the list.'; }

    $d = DateTime::createFromFormat('!Y-m-d', $date);
    if (!$d || $d->format('Y-m-d') !== $date) {
        $errors[] = 'Choose a valid date.';
    } else {
        $today = new DateTime('today');
        $max   = (new DateTime('today'))->modify('+90 days');
        if ($d < $today || $d > $max) {
            $errors[] = 'Pick a date within the next 90 days.';
        } elseif ($d->format('N') == 7) {
            $errors[] = 'We are closed on Sundays. Please pick another day.';
        }
    }
    if (strlen($notes) > 500) { $errors[] = 'Notes must be 500 characters or fewer.'; }

    if (!$errors) {
        $notesVal = ($notes === '') ? null : $notes;
        $stmt = $conn->prepare('INSERT INTO bookings (user_id, vehicle_id, service_type, preferred_date, notes) VALUES (?, ?, ?, ?, ?)');
        $stmt->bind_param('iisss', $uid, $vid, $service, $date, $notesVal);
        $stmt->execute();
        header('Location: /?booked=1');
        exit;
    }
}

$stmt = $conn->prepare(
    'SELECT b.service_type, b.preferred_date, b.status, v.make, v.model, v.plate
     FROM bookings b JOIN vehicles v ON v.id = b.vehicle_id
     WHERE b.user_id = ? ORDER BY b.id DESC LIMIT 10'
);
$stmt->bind_param('i', $uid);
$stmt->execute();
$bookings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

require __DIR__ . '/../../includes/header.php';
?>
<section>
  <h2>Book a service</h2>
  <?php if (isset($_GET['booked'])): ?><div class="ok">Booking received. We will confirm it shortly.</div><?php endif; ?>

  <?php if (!$vehicles): ?>
    <div class="card"><p>You need to add a vehicle first. <a href="//garage.hashvault.autos/">Go to My Garage</a>.</p></div>
  <?php else: ?>
  <form method="POST">
    <?php foreach ($errors as $err): ?><div class="error"><?= e($err) ?></div><?php endforeach; ?>
    <?= csrf_field() ?>
    <label>Vehicle
      <select name="vehicle_id" required>
        <?php foreach ($vehicles as $v): ?>
          <option value="<?= (int)$v['id'] ?>"><?= e($v['make'] . ' ' . $v['model'] . ' (' . $v['plate'] . ')') ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>Service
      <select name="service" required>
        <?php foreach ($services as $s): ?><option><?= e($s) ?></option><?php endforeach; ?>
      </select>
    </label>
    <label>Preferred date (closed Sundays)
      <input type="date" name="date" required min="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d', strtotime('+90 days')) ?>">
    </label>
    <label>Notes (optional, max 500 characters)
      <textarea name="notes" rows="3" maxlength="500"></textarea>
    </label>
    <br><button class="btn" type="submit">Request booking</button>
  </form>
  <?php endif; ?>

  <?php if ($bookings): ?>
  <h2>Your recent bookings</h2>
  <div class="grid">
    <?php foreach ($bookings as $b): ?>
      <div class="card">
        <h3><?= e($b['service_type']) ?></h3>
        <p><?= e($b['make'] . ' ' . $b['model']) ?> &middot; <?= e($b['plate']) ?><br>
           <?= e($b['preferred_date']) ?> &middot; <strong><?= e(ucfirst($b['status'])) ?></strong></p>
      </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</section>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
