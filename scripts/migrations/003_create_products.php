<?php
return [
    'up' => "CREATE TABLE IF NOT EXISTS products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  description TEXT NULL,
  price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  stock_qty INT NOT NULL DEFAULT 0,
  category_id INT NULL,
  image_path VARCHAR(255) NULL,
  thumbnail_path VARCHAR(255) NULL,
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_products_category
    FOREIGN KEY (category_id) REFERENCES categories(id)
    ON DELETE SET NULL
)",
    'down' => "DROP TABLE IF EXISTS products"
];

/*

sku VARCHAR(50) UNIQUE NOT NULL,
unit VARCHAR(10) DEFAULT 'un',
min_stock INT DEFAULT 0,
cost DECIMAL(10,2) NOT NULL DEFAULT 0.00,





Colunas que precisam constar no insert into:
- name
- description
- price
- stock_qty
- category_id
- image_path
- thumbnail_path
- is_active


*/