-- Run this ONLY if you already imported the old database.sql and just want
-- the new product images (no need to re-import / lose existing data).
USE ecommerce_db;
UPDATE products SET image = 'wireless-headphones.jpg'  WHERE slug = 'wireless-bluetooth-headphones';
UPDATE products SET image = 'smart-fitness-watch.jpg'  WHERE slug = 'smart-fitness-watch';
UPDATE products SET image = 'denim-jacket.jpg'         WHERE slug = 'classic-denim-jacket';
UPDATE products SET image = 'running-sneakers.jpg'     WHERE slug = 'running-sneakers';
UPDATE products SET image = 'ceramic-pan-set.jpg'      WHERE slug = 'ceramic-non-stick-pan-set';
UPDATE products SET image = 'electric-kettle.jpg'      WHERE slug = 'electric-kettle-1-7l';
UPDATE products SET image = 'art-of-clean-code.jpg'    WHERE slug = 'the-art-of-clean-code';
UPDATE products SET image = 'mystery-old-house.jpg'    WHERE slug = 'mystery-of-the-old-house';
