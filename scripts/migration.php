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
	// Support migrations that return either a plain SQL string or an array with 'up'/'down'
	if (is_array($sql)) {
		if (isset($sql['up'])) {
			$sql = $sql['up'];
		} else {
			echo "Skipping migration (missing 'up' key): " . basename($file) . PHP_EOL;
			continue;
		}
	}
	if (!$sql) continue;
	try {
		$pdo->exec($sql);
		echo "Executed migration: " . basename($file) . PHP_EOL;
	} catch (Exception $e) {
		echo "Migration failed: " . basename($file) . " - " . $e->getMessage() . PHP_EOL;
	}
}

