<?php
date_default_timezone_set('Africa/Nairobi');
header('Cache-Control: no-store');
mysqli_report(MYSQLI_REPORT_OFF);

$t = microtime(true);
$dbOk = false;
try {
    @include __DIR__ . '/../../config/db.php';
    if (isset($conn) && $conn instanceof mysqli && !$conn->connect_errno) {
        $dbOk = (bool)$conn->query('SELECT 1');
    }
} catch (Throwable $e) {
    $dbOk = false;
}
$dbMs = (int)round((microtime(true) - $t) * 1000);

$up = (int)explode(' ', (string)@file_get_contents('/proc/uptime'))[0];
$upText = intdiv($up, 86400) . ' days, ' . intdiv($up % 86400, 3600) . ' hours, ' . intdiv($up % 3600, 60) . ' minutes';

$certFile = '/etc/ssl/hashvault/fullchain.crt';
$daysLeft = null;
if (is_readable($certFile)) {
    $c = openssl_x509_parse((string)file_get_contents($certFile));
    if ($c) {
        $daysLeft = (int)floor(($c['validTo_time_t'] - time()) / 86400);
    }
}
$pageTitle = 'System Status';
require __DIR__ . '/../../includes/header.php';
?>
<section>
  <h2>System status</h2>
  <?php if ($dbOk): ?>
    <div class="ok">All systems operational</div>
  <?php else: ?>
    <div class="error">We are experiencing a problem with our database</div>
  <?php endif; ?>

  <div class="grid">
    <div class="card">
      <h3>Website</h3>
      <p>Operational</p>
    </div>
    <div class="card">
      <h3>Database</h3>
      <p><?= $dbOk ? 'Operational (' . $dbMs . ' ms response)' : 'Unavailable' ?></p>
    </div>
    <div class="card">
      <h3>Server uptime</h3>
      <p><?= htmlspecialchars($upText) ?></p>
    </div>
    <div class="card">
      <h3>HTTPS certificate</h3>
      <p>
        <?php if ($daysLeft === null): ?>
          Not installed yet
        <?php elseif ($daysLeft < 0): ?>
          Expired
        <?php else: ?>
          Valid, <?= (int)$daysLeft ?> days remaining
        <?php endif; ?>
      </p>
    </div>
  </div>
  <p style="color:var(--muted)">Checked at <?= date('Y-m-d H:i:s') ?> (East Africa Time). Refresh the page to check again.</p>
</section>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
