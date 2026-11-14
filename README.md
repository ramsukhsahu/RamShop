# RamShop — PHP + MySQL + AJAX Ecommerce Site

A complete, self-contained ecommerce demo built with plain PHP (**mysqli**), MySQL, and vanilla
JavaScript (fetch-based AJAX — no framework/jQuery required). Cart add/update/remove and
admin actions all happen without page reloads.

## Features

- Product catalog with category filter + search
- Product detail pages
- AJAX shopping cart (add, change quantity, remove) — session based
- Customer registration/login (bcrypt password hashing) and order history
- Checkout flow that creates an order + decrements stock
- **Payment methods at checkout**: Cash on Delivery, Card, and PayPal, with the
  card/PayPal flow confirmed over AJAX before the order is created
- Admin panel: dashboard stats, product CRUD (with image upload), order management
  (with payment method/status shown) with AJAX status updates and AJAX product delete
- Clean, responsive CSS (no build step)
- All prices are shown in Indian Rupees (₹) with Indian digit grouping (e.g. ₹1,23,456.00)
- Demo products ship with illustrated product images in `assets/uploads/`
  (already imported the old DB? run `update_product_images.sql` instead of re-importing)

## Requirements

- PHP 7.4+ (PHP 8 recommended) with the `mysqli` extension
- MySQL or MariaDB
- A local server: XAMPP / MAMP / WAMP / Laragon, or the PHP built-in server

## Setup

1. **Create the database.**
   Import `database.sql` into MySQL — this creates the `ecommerce_db` database,
   all tables, and seeds a few categories/products plus a demo admin account.

   ```bash
   mysql -u root -p < database.sql
   ```

2. **Configure the DB connection.**
   Edit `config/db.php` and set your MySQL host/user/password if they differ from
   the defaults (`localhost` / `root` / empty password).

3. **Set the upload folder permissions** (for admin product image uploads):

   ```bash
   chmod -R 775 assets/uploads
   ```

4. **Run it.**
   - With XAMPP/WAMP/Laragon: place the `ecommerce` folder inside `htdocs`/`www`
     and visit `http://localhost/ecommerce/`.
   - Or with PHP's built-in server, from inside the project folder:

     ```bash
     php -S localhost:8000
     ```

     then open `http://localhost:8000/`.

## Demo logins

- **Admin:** `admin@example.com` / `admin123` (change this after first login — go to
  the `users` table or add a "change password" page)
- **Customer:** register a new account from `register.php`

## Folder structure

```
ecommerce/
├── admin/               # Admin panel (dashboard, products, orders)
│   └── ajax/             # Admin-only AJAX endpoints
├── ajax/                 # Storefront AJAX endpoints (cart)
├── assets/
│   ├── css/style.css
│   ├── js/main.js        # All AJAX logic (fetch API)
│   └── uploads/          # Product images
├── config/db.php         # mysqli connection
├── includes/             # functions.php, header.php, footer.php
├── database.sql          # Schema + seed data
├── index.php             # Home / product listing
├── product.php           # Product detail
├── cart.php               # Cart page
├── checkout.php          # Checkout
├── order_success.php
├── orders.php             # Customer order history
├── login.php / register.php / logout.php
```

## Payment methods

Checkout offers three options:

- **Cash on Delivery** — no online payment step, order is created as `payment_status = pending`.
- **Card** — the form collects name/number/expiry/CVV and sends them to
  `ajax/process_payment.php` over AJAX *before* the order is created. That
  endpoint runs realistic validation (Luhn checksum, expiry check, CVV format)
  and returns a simulated transaction id. **It is a simulated gateway, not a
  real payment processor** — no card network is contacted and, per PCI-DSS
  best practice, full card numbers/CVVs are never written to the database
  (only the last 4 digits are stored, for display only). A card ending in
  `0002` is wired to always simulate a decline, so you can see the failure
  path.
- **PayPal** — simulated the same way (instantly "approved"); wired up as a
  placeholder for the real PayPal Orders API / JS SDK.

### Going live with real payments

Replace the body of `ajax/process_payment.php`'s card branch with a real
call to your provider's **server-side** SDK, and swap the raw `<input>`
card fields in `checkout.php` for the provider's **hosted fields / client-side
tokenization** (e.g. Stripe Elements + `PaymentIntents`, or the PayPal JS
SDK) so raw card numbers never touch your own server — that's what keeps you
out of full PCI-DSS scope. The `orders` table already has the columns
(`payment_method`, `payment_status`, `transaction_id`, `card_last4`) a real
integration needs; you'd typically also add a webhook endpoint so the
provider can push final payment confirmation asynchronously.

## Notes & next steps

- Database access uses `mysqli` throughout, via three small helper functions in
  `includes/functions.php` — `db_query()`, `db_fetch_one()`, `db_fetch_all()` —
  which wrap `prepare()` / `bind_param()` / `get_result()` so each page doesn't
  repeat that boilerplate. Multi-statement operations (checkout) use
  `$conn->begin_transaction()` / `commit()` / `rollback()` directly.
- The cart is stored in the PHP session (`$_SESSION['cart']`), so it persists per
  browser session — no database cart table needed for this demo.
- All SQL uses mysqli prepared statements (`bind_param`) to prevent SQL injection.
- Passwords are hashed with `password_hash()`/`password_verify()` (bcrypt).
- For production: add CSRF tokens to forms, HTTPS, rate limiting on login, a real
  payment gateway integration at checkout (see above), and pagination on the
  product grid.
