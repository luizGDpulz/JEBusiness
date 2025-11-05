<?php
// Executa migrations simples (arquivos PHP que retornam SQL string)
require __DIR__ . '/../src/Core/Database.php';

use Core\Database;

$migrationsDir = __DIR__ . '/migrations';
$files = glob($migrationsDir . '/*.php');
sort($files);

$pdo = Database::getInstance();

foreach ($files as $file) {
	$sql = require $file;
	if (!$sql) continue;
	try {
		$pdo->exec($sql);
		echo "Executed migration: " . basename($file) . PHP_EOL;
	} catch (Exception $e) {
		echo "Migration failed: " . basename($file) . " - " . $e->getMessage() . PHP_EOL;
	}
}

