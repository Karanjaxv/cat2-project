<?php
require __DIR__ . '/../../includes/bootstrap.php';
$pageTitle = 'How We Protect Your Data';

$row = $conn->query("SELECT COUNT(*) AS total, SUM(password_hash LIKE '\$2y\$%') AS hashed FROM users")->fetch_assoc();
$total  = (int)$row['total'];
$hashed = (int)$row['hashed'];

require __DIR__ . '/../../includes/header.php';
?>
<section class="hero">
  <h1>How we protect your data</h1>
  <p>Your details are only useful to you. Here is exactly what we do to keep them that way.</p>
</section>

<section>
  <h2>Live proof</h2>
  <div class="card">
    <h3><?= $hashed ?> of <?= $total ?> passwords are stored as secure hashes</h3>
    <p>This number is counted from our database right now. We never store your actual password, so even our own team cannot read it.</p>
  </div>
</section>

<section>
  <h2>Our safeguards</h2>
  <div class="grid">
    <div class="card"><h3>Hashed passwords</h3><p>Passwords are scrambled with bcrypt and a unique salt before saving. They cannot be turned back into the original.</p></div>
    <div class="card"><h3>Checked twice</h3><p>Every form is validated in your browser for instant feedback, and again on our server, where it counts.</p></div>
    <div class="card"><h3>Safe database queries</h3><p>We use prepared statements, which stops attackers from sneaking commands into forms (SQL injection).</p></div>
    <div class="card"><h3>Lockout protection</h3><p>Five wrong passwords lock the account for 15 minutes, which slows down guessing attacks.</p></div>
    <div class="card"><h3>Forgery protection</h3><p>Each form carries a secret one-time token, so other websites cannot submit forms on your behalf.</p></div>
    <div class="card"><h3>Safe sessions</h3><p>Login cookies are hidden from scripts and refreshed at login to prevent hijacking.</p></div>
    <div class="card"><h3>Least privilege</h3><p>Our application uses a database account that can read and write records but cannot delete or change the database structure.</p></div>
    <div class="card"><h3>Secrets kept private</h3><p>Database settings and source code sit outside the public web folder, and folder browsing is switched off.</p></div>
    <div class="card"><h3>Encrypted connection</h3><p>All traffic is protected with HTTPS using a ZeroSSL certificate.</p></div>
  </div>
</section>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
