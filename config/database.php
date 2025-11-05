<?php
$env = require __DIR__ . '/env.php';

// Build a PDO DSN for MySQL (the app uses MySQL). Do not auto-create sqlite files.
if ($env['DB_DRIVER'] !== 'mysql') {
	// enforce mysql as driver to avoid accidental sqlite creation
	throw new \RuntimeException('Only mysql DB_DRIVER is supported in this deployment. Set DB_DRIVER=mysql and configure DB_HOST/DB_NAME.');
}

$dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $env['DB_HOST'], $env['DB_NAME']);

return [
	'driver' => $env['DB_DRIVER'],
	'dsn' => $dsn,
	'user' => $env['DB_USER'],
	'pass' => $env['DB_PASS'],
	'options' => [
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		PDO::ATTR_EMULATE_PREPARES => false,
	],
];

