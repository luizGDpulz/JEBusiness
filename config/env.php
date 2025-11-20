<?php
// Simple env loader returning array of env values used by app.
return [
	'APP_ENV' => getenv('APP_ENV') ?: 'development',
	'DB_DRIVER' => getenv('DB_DRIVER') ?: 'mysql',
	'DB_HOST' => getenv('DB_HOST') ?: 'localhost',
	'DB_NAME' => getenv('DB_NAME') ?: 'jebusiness',
	'DB_USER' => getenv('DB_USER') ?: 'root',
	'DB_PASS' => getenv('DB_PASS') ?: '',
	'APP_URL'  => getenv('APP_URL') ?: 'http://localhost',
];

