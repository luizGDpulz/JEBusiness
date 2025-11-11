<?php
// Executa seeds simples (arquivos PHP que retornam data arrays com 'type' e dados específicos)
require __DIR__ . '/../src/Core/Database.php';

use Core\Database;

$seedsDir = __DIR__ . '/seeds';
$files = glob($seedsDir . '/*.php');
sort($files);

$pdo = Database::getInstance();

foreach ($files as $file) {
	$data = require $file;
	if (!is_array($data) || !isset($data['type'])) continue;

	$type = $data['type'];

	// Seed de usuário
	if ($type === 'user') {
		$stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email');
		$stmt->execute([':email' => $data['email']]);
		$exists = $stmt->fetch();
		if ($exists) {
			echo "Seed skipped (exists): user " . $data['email'] . PHP_EOL;
			continue;
		}
		$passwordHash = password_hash($data['password'], PASSWORD_ARGON2ID);
		$sql = "INSERT INTO users (name, email, password_hash, role_id, created_at) VALUES (:name, :email, :password_hash, :role_id, NOW())";
		$stmt = $pdo->prepare($sql);
		$stmt->execute([
			':name' => $data['name'] ?? 'User',
			':email' => $data['email'],
			':password_hash' => $passwordHash,
			':role_id' => $data['role_id'] ?? 1,
		]);
		echo "Seed created: user " . $data['email'] . PHP_EOL;
	}

	// Seed de categoria
	if ($type === 'category') {
		$stmt = $pdo->prepare('SELECT id FROM categories WHERE id = :id');
		$stmt->execute([':id' => $data['id']]);
		$exists = $stmt->fetch();
		if ($exists) {
			echo "Seed skipped (exists): category id=" . $data['id'] . PHP_EOL;
			continue;
		}
		$sql = "INSERT INTO categories (id, name, description, created_at) VALUES (:id, :name, :description, NOW())";
		$stmt = $pdo->prepare($sql);
		$stmt->execute([
			':id' => $data['id'],
			':name' => $data['name'],
			':description' => $data['description'] ?? null,
		]);
		echo "Seed created: category id=" . $data['id'] . " - " . $data['name'] . PHP_EOL;
	}
}


