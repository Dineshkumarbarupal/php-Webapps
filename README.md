# ShopSphere — Modular PHP E-commerce Starter

A modular, Amazon-inspired shopping application built with HTML, CSS, vanilla JavaScript, PHP 8.2+, and SQLite/PDO. It uses original branding and styling rather than copying Amazon assets.

## Features
- Responsive storefront, category navigation, search, sort, price filters, and pagination-ready product listing
- Dynamic product detail pages with image gallery, variants/specifications, stock, ratings, reviews
- Sign-up, sign-in, sign-out; password hashing and session regeneration
- Persistent cart for signed-in users and session cart for guests
- Wishlist, quantity updates, remove item, cart totals
- Checkout flow that creates an order (demo payment only; no real card processing)
- Order history and order detail
- Account page
- Admin product CRUD starter, protected by admin role
- CSRF protection, PDO prepared statements, output escaping, server-side validation
- SQLite auto-migration and sample products
- Modular controllers, services, repositories, views, routes, and assets

## Requirements
- PHP 8.2+ with `pdo_sqlite` enabled (or adapt `app/Database.php` to another PDO driver)
- PHP CLI for the built-in development server

## Run locally
1. Extract this archive.
2. In the project directory, run:
   ```bash
   php -S 127.0.0.1:8000 -t public
   ```
3. Open http://127.0.0.1:8000

The database is created at `storage/shop.sqlite` on first run. Ensure `storage/` is writable by the PHP process.

## Demo admin
For safety, this project does **not** ship with a hard-coded admin password. Create a normal account, then promote it manually in SQLite:
```sql
UPDATE users SET role = 'admin' WHERE email = 'your-email@example.com';
```
Use `sqlite3 storage/shop.sqlite` if installed, or any SQLite database GUI.

## Important production work before launch
- Configure HTTPS, secure cookie settings, SMTP email verification/password reset, rate limiting, audit logging, and monitoring.
- Integrate a payment provider (Razorpay/Stripe etc.) using server-side order verification and webhooks. The current checkout is explicitly a demo order flow and does not charge cards.
- Add shipping/tax rules, inventory reservation, refund/cancellation workflow, image upload validation/storage, and backups.
- Add automated tests, CI, dependency scanning, CSP/security headers, and production error logging.
- For large traffic, move from SQLite to managed PostgreSQL/MySQL, add caching, queues, CDN/object storage, and horizontal scaling.