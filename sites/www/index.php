<?php
$pageTitle = 'Home';
require __DIR__ . '/../../includes/header.php';
?>
<section class="hero">
  <h1>Your car's history.<br>Locked down.</h1>
  <p>Book services, track your vehicles and leave feedback, in a garage that treats your data as seriously as your engine.</p>
  <a class="btn" href="//garage.hashvault.autos/">Open My Garage</a>
</section>

<section>
  <h2>What we do</h2>
  <div class="grid">
    <div class="card"><h3>Full Service</h3><p>Oil, filters, fluids and a complete multi-point inspection.</p></div>
    <div class="card"><h3>Diagnostics</h3><p>Computer diagnostics to find faults before they become repairs.</p></div>
    <div class="card"><h3>Tyres &amp; Brakes</h3><p>Fitting, balancing, alignment and brake replacement.</p></div>
  </div>
</section>

<section>
  <h2>Why HashVault</h2>
  <div class="grid">
    <div class="card"><h3>Passwords never stored</h3><p>We keep only a secure hash, so even we can't read your password.</p></div>
    <div class="card"><h3>Checked inputs</h3><p>Every form is validated in your browser and again on our server.</p></div>
    <div class="card"><h3>See for yourself</h3><p>Read exactly how we protect you on our <a href="//trust.hashvault.autos/">Trust page</a>.</p></div>
  </div>
</section>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
