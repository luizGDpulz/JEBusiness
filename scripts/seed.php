<?php
// Executa seeds simples (arquivos PHP que retornam data arrays)
require __DIR__ . '/../src/Core/Database.php';

use Core\Database;

$seedsDir = __DIR__ . '/seeds';
$files = glob($seedsDir . '/*.php');
sort($files);

$pdo = Database::getInstance();

foreach ($files as $file) {
	$seed = require $file;
	if (is_array($seed)) {
		foreach ($seed as $sql) {
			try {
				$pdo->exec($sql);
				echo "Seed executed: $sql" . PHP_EOL;
			} catch (Exception $e) {
				echo "Seed failed: $sql - " . $e->getMessage() . PHP_EOL;
			}
		}
	} elseif (is_string($seed)) {
		try {
			$pdo->exec($seed);
			echo "Seed executed: $seed" . PHP_EOL;
		} catch (Exception $e) {
			echo "Seed failed: $seed - " . $e->getMessage() . PHP_EOL;
		}
	}
}

