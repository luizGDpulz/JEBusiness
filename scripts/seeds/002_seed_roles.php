<?php
// Seed roles padrão
return [
    "INSERT INTO roles (id, name) VALUES (1, 'cliente') ON DUPLICATE KEY UPDATE name = VALUES(name)",
    "INSERT INTO roles (id, name) VALUES (2, 'vendedor') ON DUPLICATE KEY UPDATE name = VALUES(name)",
    "INSERT INTO roles (id, name) VALUES (99, 'admin') ON DUPLICATE KEY UPDATE name = VALUES(name)"
];
