-- ============================================================
-- PHP AJAX Ecommerce - Database Schema
-- Import this file into MySQL/MariaDB before running the site.
-- ============================================================

CREATE DATABASE IF NOT EXISTS ecommerce_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ecommerce_db;

-- ----------------------------
-- Users (customers + admin)
-- ----------------------------
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('customer','admin') NOT NULL DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ----------------------------
-- Categories
-- ----------------------------
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- ----------------------------
-- Products
-- ----------------------------
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    name VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    description TEXT,
    price DECIMAL(10,2) NOT NULL DEFAULT 0,
    stock INT NOT NULL DEFAULT 0,
    image VARCHAR(255) DEFAULT 'no-image.png',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ----------------------------
-- Orders
-- ----------------------------
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    phone VARCHAR(30),
    address TEXT NOT NULL,
    total DECIMAL(10,2) NOT NULL DEFAULT 0,
    status ENUM('pending','processing','shipped','completed','cancelled') NOT NULL DEFAULT 'pending',
    payment_method ENUM('cod','card','paypal') NOT NULL DEFAULT 'cod',
    payment_status ENUM('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending',
    transaction_id VARCHAR(64) DEFAULT NULL,
    card_last4 VARCHAR(4) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ----------------------------
-- Order items
-- ----------------------------
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT,
    product_name VARCHAR(200) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ----------------------------
-- Upgrading an existing database?
-- If you already ran an earlier version of this file, run these two lines
-- instead of re-importing everything (they add the new payment columns):
--
-- ALTER TABLE orders
--   ADD COLUMN payment_method ENUM('cod','card','paypal') NOT NULL DEFAULT 'cod' AFTER status,
--   ADD COLUMN payment_status ENUM('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending' AFTER payment_method,
--   ADD COLUMN transaction_id VARCHAR(64) DEFAULT NULL AFTER payment_status,
--   ADD COLUMN card_last4 VARCHAR(4) DEFAULT NULL AFTER transaction_id;
-- ----------------------------

-- ----------------------------
-- Seed data
-- ----------------------------

-- Admin login: admin@example.com / admin123
INSERT INTO users (name, email, password, role) VALUES
('Site Admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
-- NOTE: hash above corresponds to "admin123" (bcrypt). Change it after first login.

INSERT INTO categories (name, slug) VALUES
('Electronics', 'electronics'),
('Fashion', 'fashion'),
('Home & Kitchen', 'home-kitchen'),
('Books', 'books');

INSERT INTO products (category_id, name, slug, description, price, stock, image) VALUES
(1, 'Wireless Bluetooth Headphones', 'wireless-bluetooth-headphones', 'Over-ear wireless headphones with noise cancellation and 30-hour battery life.', 2999.00, 25, 'wireless-headphones.jpg'),
(1, 'Smart Fitness Watch', 'smart-fitness-watch', 'Track your steps, heart rate, and sleep with this waterproof smart watch.', 4499.00, 15, 'smart-fitness-watch.jpg'),
(2, 'Classic Denim Jacket', 'classic-denim-jacket', 'Unisex denim jacket, durable and stylish for all seasons.', 1999.00, 30, 'denim-jacket.jpg'),
(2, 'Running Sneakers', 'running-sneakers', 'Lightweight breathable sneakers designed for daily runs.', 2499.00, 40, 'running-sneakers.jpg'),
(3, 'Ceramic Non-Stick Pan Set', 'ceramic-non-stick-pan-set', '3-piece ceramic-coated non-stick cookware set.', 1799.00, 20, 'ceramic-pan-set.jpg'),
(3, 'Electric Kettle 1.7L', 'electric-kettle-1-7l', 'Fast-boil electric kettle with auto shut-off.', 1199.00, 35, 'electric-kettle.jpg'),
(4, 'The Art of Clean Code', 'the-art-of-clean-code', 'A practical guide to writing maintainable software.', 599.00, 50, 'art-of-clean-code.jpg'),
(4, 'Mystery of the Old House', 'mystery-of-the-old-house', 'A gripping mystery novel that keeps you guessing till the end.', 399.00, 60, 'mystery-old-house.jpg');
