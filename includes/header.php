<?php
$pageTitle = $pageTitle ?? 'HashVault Auto';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($pageTitle) ?> | HashVault Auto</title>
<link rel="stylesheet" href="/assets/style.css">
</head>
<body>
<header>
  <div class="wrap">
    <a class="brand" href="//hashvault.autos/">
      <svg width="32" height="32" viewBox="0 0 32 32" aria-hidden="true">
        <path d="M16 2l12 5v8c0 8-5 13-12 15C9 28 4 23 4 15V7z" fill="#f5a623"/>
        <text x="16" y="21" text-anchor="middle" font-size="11" font-weight="700" fill="#0f1b2d" font-family="sans-serif">HV</text>
      </svg>
      HashVault Auto
    </a>
    <nav>
      <a href="//garage.hashvault.autos/">My Garage</a>
      <a href="//book.hashvault.autos/">Book a Service</a>
      <a href="//reviews.hashvault.autos/">Reviews</a>
      <a href="//trust.hashvault.autos/">Trust</a>
      <a href="//status.hashvault.autos/">Status</a>
    </nav>
  </div>
</header>
<main class="wrap">
