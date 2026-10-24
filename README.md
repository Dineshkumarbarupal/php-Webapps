# ShopSphere — Modular PHP E-commerce Starter

A modular, Amazon-inspired shopping application built with HTML, CSS, vanilla JavaScript, PHP 8.2+, and SQLite/PDO. It uses original branding and styling rather than copying Amazon assets.

## Features
- Responsive storefront, category navigation, search, sorting, price filters, and product listings
- Dynamic product detail pages with product variants/specifications, stock, ratings, and reviews
- Sign-up, sign-in, sign-out; password hashing and session regeneration
- Persistent cart for signed-in users and session cart for guests
- Wishlist, quantity updates, remove item, and cart totals
- Checkout flow that creates a demo order (no real card processing)
- Order history and order detail
- Account page
- Admin product CRUD starter, protected by admin role
- CSRF protection, PDO prepared statements, output escaping, and server-side validation
- SQLite auto-migration and sample products
- Modular controllers, repositories, views, routes, and assets

## Requirements
- PHP 8.2+ with `pdo_sqlite` enabled (or adapt `app/Database.php` to use another PDO driver)
- Writable `storage/` directory
- Apache `mod_rewrite` and `.htaccess` support for shared hosting

## Directory layout
```text
ShopSphere/
├── index.php          # Web entry point / front controller
├── .htaccess          # URL rewriting and access protection
├── server.php         # Local PHP development-server router
├── assets/
│   ├── css/app.css
│   └── js/app.js
├── app/               # Controllers, database, repositories and views
├── storage/           # SQLite database created on first run
├── .env.example
└── README.md
```

The project is arranged so its repository root can be uploaded directly into a shared-hosting web-root such as `htdocs/`. The `app/` and `storage/` directories remain inside that document root for compatibility with basic shared hosting, but `.htaccess` blocks direct browser requests to them. The host must honour `.htaccess` rules for that protection to work.

## Run locally
From the repository root, run:

```bash
php -S 127.0.0.1:8000 server.php
```

Open http://127.0.0.1:8000. The database is created at `storage/shop.sqlite` on first run.

## Deploy to shared PHP hosting
1. Create a hosting account and a free subdomain.
2. Confirm PHP 8.2+ and the `pdo_sqlite` extension are enabled for the selected PHP version.
3. Upload the contents of the repository root into the hosting web-root (often named `htdocs`), including the hidden `.htaccess` file.
4. Ensure `storage/` is writable by PHP. Do not make it world-writable unless the host specifically requires it; use the narrowest permissions the host supports.
5. Open the hosting URL and test the home page, sign-up, sign-in, products, cart, and checkout demo.
6. If clean routes such as `/products` return 404, confirm Apache rewrite support and that `.htaccess` is enabled.
7. If SQLite fails with “could not find driver” or a permissions error, ask the host whether `pdo_sqlite` is enabled and whether PHP can write to `storage/`. This application will not switch to MySQL automatically.

**Important:** A host may restrict SQLite even if it supports PHP. Verify the extension and storage write access on your chosen hosting account before treating the deployment as successful. Do not leave diagnostic scripts on the public website.

## Demo admin
For safety, this project does **not** ship with a hard-coded admin password. Create a normal account, then promote it manually in SQLite:

```sql
UPDATE users SET role = 'admin' WHERE email = 'your-email@example.com';
```

Use `sqlite3 storage/shop.sqlite` if installed, or any SQLite database GUI.

## Important production work before launch
- Confirm the host enforces HTTPS and that secure session cookies are active.
- Configure email verification and password reset, rate limiting, audit logging, monitoring, and backups.
- Integrate a payment provider using server-side order verification and webhooks. The current checkout is explicitly a demo order flow and does not charge cards.
- Add shipping/tax rules, inventory reservation, refund/cancellation workflow, and safe image storage.
- Add automated tests, CI, dependency scanning, CSP/security headers, and production error logging.
- For substantial traffic, use a managed PostgreSQL/MySQL database, caching, queues, CDN/object storage, and scalable hosting.

This is a starter project, not a production-audited commerce platform.
