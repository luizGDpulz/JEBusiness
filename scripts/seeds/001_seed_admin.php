<?php
// Seed data for admin user
return "INSERT INTO users (name, email, password_hash, role_id, created_at) VALUES ('Admin Root', 'admin@example.com', '" . password_hash('admin123', PASSWORD_ARGON2ID) . "', 99, NOW())";
