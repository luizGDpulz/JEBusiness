<?php

// Migration MySQL: cria tabela 'categories'
return [
    'up' => "CREATE TABLE categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        description TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    'down' => "DROP TABLE IF EXISTS categories"
];