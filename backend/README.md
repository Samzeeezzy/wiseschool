# WISE PHP/MySQL backend foundation

This directory contains the first server-side foundation for the WISE School Management System. It is deliberately separate from the static GitHub Pages website. **GitHub Pages cannot execute PHP**; deploy this backend to the school's PHP-enabled cPanel hosting after the school has purchased and configured the hosting/domain.

## Requirements
- PHP 8.1+ (confirm the actual cPanel version before deployment)
- PHP extensions: PDO, pdo_mysql, openssl, mbstring
- MySQL 8+ or a compatible supported MariaDB version
- HTTPS in production

## Deployment layout
Keep the application and configuration outside the public web root where possible. Example:

```
/home/ACCOUNT/
  wise-private/
    config.php                 # created from config.example.php; never commit
    app/                       # copy backend/app here
  public_html/
    wise-api/                  # copy backend/public here
```

Set the server environment variable `WISE_CONFIG_PATH` to the absolute path of `wise-private/config.php`. If the host does not allow environment variables, configure it in a server-level setting or a protected PHP-FPM/Apache configuration; do not place credentials in a public file.

## Setup
1. Use the hosting database and a dedicated least-privilege database user where the host permits it. Confirm permission to add tables first.
2. Back up the existing database, then import `database/schema.sql`. The script creates only `wise_`-prefixed tables and does not drop or alter existing tables.
3. Copy `config.example.php` to a private `config.php` outside `public_html`.
4. Set database credentials and a random 32-byte-or-longer application secret in that private file.
5. Copy `app/` to the private application directory and `public/` to the chosen public API directory.
6. Set the server environment variable `WISE_APP_PATH` to `/home/ACCOUNT/wise-private` (the directory containing `app/`). The API uses this private path to load application code.
7. Enable HTTPS before allowing any sign-in or code redemption.
8. Test in a separate staging database before importing real school records.

## Current milestone
- PDO connection with exception mode and native prepared statements.
- Secure session defaults, CSRF token helper and role guard.
- School person provisioning and role schema.
- Super Administrator-only issue/list/revoke API for single-use verification codes.
- One-time redemption helper that consumes a code transactionally and verifies its category, target record, expiry and attempt limit.
- Audit events that do not store plaintext codes.

## Not live yet
Google OAuth, account activation, email delivery, parent/guardian approval, student/staff imports, and the remaining school-management modules are not enabled by this foundation. The API fails closed unless a valid authenticated PHP session with the `super_admin` role already exists. Do not create a public/shared administrator login or use the shared development code `10987`.

## Verification code policy
The API generates a random 128-bit code for one existing school record and category. It returns the plaintext code only once to the authorised Super Administrator; only an HMAC-SHA-256 hash is stored. Codes expire after a configurable short period (default 48 hours), are single-use, can be revoked, and are bound to a school person record. The shared value `10987` is not embedded in the backend and must never be used as a production credential.

## Before production
Confirm the cPanel PHP/MySQL versions, domain and HTTPS, school-controlled Google Cloud OAuth project, named Super Administrators and MFA/recovery plan, verified school records, parent identity/guardian approval process, privacy/retention policy and approved code delivery channel.


## Shared database compatibility
The schema and PHP queries use the `wise_` prefix for every WISE table so they can coexist with unrelated applications in a shared database. The import uses `CREATE TABLE IF NOT EXISTS` and `INSERT IGNORE`; it does not drop, empty, rename, or alter the existing `bookings`, `currency`, `foradmin`, or `subscribed` tables. Back up the database before importing. Do not use the earlier unprefixed schema file.
