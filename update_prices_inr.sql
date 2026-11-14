-- Run this ONLY if you already imported an older database.sql and want the demo
-- products priced in rupees without re-importing (keeps your users and orders).
-- Note: this resets the prices of the 8 demo products only.
USE ecommerce_db;
UPDATE products SET price = 2999.00 WHERE slug = 'wireless-bluetooth-headphones';
UPDATE products SET price = 4499.00 WHERE slug = 'smart-fitness-watch';
UPDATE products SET price = 1999.00 WHERE slug = 'classic-denim-jacket';
UPDATE products SET price = 2499.00 WHERE slug = 'running-sneakers';
UPDATE products SET price = 1799.00 WHERE slug = 'ceramic-non-stick-pan-set';
UPDATE products SET price = 1199.00 WHERE slug = 'electric-kettle-1-7l';
UPDATE products SET price = 599.00  WHERE slug = 'the-art-of-clean-code';
UPDATE products SET price = 399.00  WHERE slug = 'mystery-of-the-old-house';
