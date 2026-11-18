# HashVault Auto

A secure auto-shop web application (ISProject1) hosted on Microsoft Azure.

## Live sites
- https://hashvault.autos - main site
- https://garage.hashvault.autos - customer accounts and vehicles
- https://book.hashvault.autos - service bookings
- https://reviews.hashvault.autos - customer reviews
- https://status.hashvault.autos - live system status
- https://trust.hashvault.autos - how data is protected
- https://admin.hashvault.autos - staff dashboard

## Stack
Ubuntu on Azure VM, Apache, PHP, MariaDB, ZeroSSL certificate (ACME), Spaceship DNS.

## Security features
bcrypt password hashing, prepared statements, server-side validation, CSRF tokens,
login lockout, role-based access, audit log, least-privilege database user,
secrets outside the web root, HTTPS with HSTS and security headers.

## Setup notes
Database settings live in config/db.php, which is not committed.
