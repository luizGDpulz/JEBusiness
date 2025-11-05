<?php
// Executa seeds simples (arquivos PHP que retornam data arrays)
require __DIR__ . '/../src/Core/Database.php';

use Core\Database;

$seedsDir = __DIR__ . '/seeds';
$files = glob($seedsDir . '/*.php');
sort($files);

$pdo = Database::getInstance();

foreach ($files as $file) {
	$data = require $file;
	if (!is_array($data)) continue;

	// Only support admin user seed for now
	if (isset($data['email'])) {
		$stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email');
		$stmt->execute([':email' => $data['email']]);
		$exists = $stmt->fetch();
		if ($exists) {
			echo "Seed skipped (exists): " . $data['email'] . PHP_EOL;
			continue;
		}

		$passwordHash = password_hash($data['password'], PASSWORD_ARGON2ID);

	// MySQL: use NOW() for created_at
	$sql = "INSERT INTO users (name, email, password_hash, role_id, created_at) VALUES (:name, :email, :password_hash, :role_id, NOW())";
	$stmt = $pdo->prepare($sql);
		$stmt->execute([
			':name' => $data['name'] ?? 'Admin',
			':email' => $data['email'],
			':password_hash' => $passwordHash,
			':role_id' => $data['role_id'] ?? 1,
		]);
		echo "Seed created: " . $data['email'] . PHP_EOL;
	}
}

