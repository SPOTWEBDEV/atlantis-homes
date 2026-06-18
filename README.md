# Atlantis Homes

A full-stack web application for Atlantis Homes — premium construction and
smart real estate investment in Lagos and Abuja. Semantic HTML5 + Tailwind
CSS on the front end, vanilla JavaScript/Fetch for every dynamic
interaction, and modular PHP on the back end with session-based auth and a
real (if swappable) database.

## Quick start (zero configuration)

The app runs on SQLite by default, so there's nothing to install or
configure beyond PHP itself.

```bash
php -S localhost:8000
```

Then open `http://localhost:8000/index.php`. The database file
(`data/atlantis.sqlite`) and all seed data are created automatically on
first request.

### Running on XAMPP / WAMP / MAMP

Drop the project folder into your server's document root (e.g.
`C:\xampp\htdocs\atlantis-homes`) and visit it in the browser, e.g.
`http://localhost/atlantis-homes/index.php`. Every internal link and
asset path is generated through a `base_url()` helper that detects the
subfolder automatically — you don't need to edit anything for this to work
whether the app lives at your domain root or in a subfolder. Make sure the
`php_pdo_sqlite` extension is enabled (it ships enabled by default in
XAMPP/WAMP).

**Permissions:** `data/` and `assets/uploads/` need to be writable by the
web server user, since the database file and any uploaded photos are
written there.

## Default accounts (seeded automatically)

| Role     | Email                       | Password       |
|----------|------------------------------|-----------------|
| Admin    | admin@atlantishomes.ng       | `Admin@123`     |
| Investor | chiamaka@example.com         | `Investor@123`  |

New investors can also register themselves from `register.php` — there's
no public path to create an admin account; that's done directly in the
database.

## What's included

**Public site:** homepage with animated stats and a featured-projects rail,
a filterable property Portfolio with expandable tabbed details, a Smart
Investor Hub (ROI calculator plus two investment boards — a company-wide
fund and standalone investment properties, both separate from the
home-buying Portfolio), a Build Cost Estimator with a live itemised
quote, Reviews with a moderated write-a-review flow, a Book a Session
page, and a Contact page.

**Investor portal** (`dashboard.php`): an investor's active purchase,
construction milestone tracker, and payment ledger, all pulled live from
the database.

**Admin Control Center** (`/admin`): Analytics, Property Management (full
CRUD with image uploads), Investment Opportunities (CRUD for both
boards), the Investor Ledger (record payments, edit total price and
construction milestone — changes show up on the investor's dashboard
immediately), Inquiries (filterable by type/status/date, with one-click
email/call/mark-contacted actions), and Review Moderation.

## Architecture

```
index.php, portfolio.php, investor-hub.php, estimate.php,
reviews.php, contact.php, book-a-session.php,
login.php, register.php, logout.php, dashboard.php
admin/            Admin Control Center pages + admin/actions/*.php (AJAX writes)
api/               Public AJAX endpoints (login, register, properties, reviews, inquiries)
includes/         Shared PHP: db.php, auth.php, functions.php, header/footer templates
assets/css        Tailwind is loaded via CDN; style.css carries only what utilities can't
                  (fonts, the gold "skyline" motif, sliders, masonry, etc.)
assets/js         One file per page/feature, plus shared helpers (main.js, inquiry-form.js)
assets/uploads    Admin-uploaded property/investment photos and site-update photos
data/             SQLite database file (auto-created)
schema.sql        MySQL/MariaDB schema + seed data, for production deployment
```

Every page follows the same shape: require the shared includes, run any
page-specific queries, render the header, the page body, then the footer.
AJAX endpoints (`api/*.php`, `admin/actions/*.php`) return JSON only.

### Database

Ships on **SQLite** for instant local use — `includes/db.php` creates the
schema and seeds demo data the first time it runs, and also carries a
small self-healing migration step (`migrate_database()`) so a database
created by an earlier version of this app picks up new tables/columns
automatically on the next request, with no manual steps.

To run on **MySQL** instead (typical for shared/cPanel hosting):

1. Import `schema.sql` into a MySQL database.
2. In `includes/db.php`, replace the SQLite connection in `get_db()` with:
   ```php
   $pdo = new PDO(
       "mysql:host=localhost;dbname=your_db;charset=utf8mb4",
       'your_user', 'your_password', $options
   );
   ```
3. Remove the `create_schema()` / `seed_database()` calls — `schema.sql`
   already creates the tables and seed rows.

Every other file talks to `$pdo` only through portable PDO/SQL, so that's
the only file that needs touching.

### Authentication & security

Sessions are PHP's native session handling (`includes/auth.php`).
Passwords are hashed with `password_hash()`/`password_verify()`. Every
admin write action (`admin/actions/*.php`) requires both an authenticated
admin session **and** a CSRF token issued per-session and checked on
submit. All user-facing output is escaped via the `h()` helper; all
database queries use prepared statements.

### File uploads

Property/investment cover images, floor plans, and construction site
photos are real uploads (validated for type and size, stored under
`assets/uploads/`) — not just URL fields. `.htaccess` in `data/` blocks
all direct web access to the database folder.

## Known limitations / next steps

- No real payment gateway is wired in — the Investor Ledger records
  payments the admin enters manually, which is realistic for installment
  plans handled by bank transfer, but won't process a card or transfer
  itself.
- Outbound email isn't wired up (the "Email" action in admin/Inquiries
  opens a `mailto:` link in the admin's own mail client rather than
  sending server-side) — add a transactional mail provider if you want
  automated confirmation emails.
- Instagram content on the brand's social profile isn't pulled in
  automatically; this would require Instagram's Graph API and app review.
  The footer/contact page link out to the real profile instead.
