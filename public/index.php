<?php
// Bootstrap minimal app for auth demo

// autoload
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
	require __DIR__ . '/../vendor/autoload.php';
} else {
	// Simple autoloader for src/ classes (PSR-4-ish)
	spl_autoload_register(function ($class) {
		$base = __DIR__ . '/../src/';
		$prefix = '';
		$class = ltrim($class, '\\');
		$path = str_replace('\\', '/', $class);
		$file = $base . $path . '.php';
		if (file_exists($file)) require $file;
	});
}

// session cookie params
$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? 80) == 443;
session_set_cookie_params([
	'lifetime' => 0,
	'path' => '/',
	'domain' => $_SERVER['HTTP_HOST'] ?? '',
	'secure' => $secure,
	'httponly' => true,
	'samesite' => 'Lax',
]);
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

// basic routing
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

use Controllers\Api\AuthController;
use Controllers\Web\ViewController;
use Middlewares\AuthMiddleware;

$auth = new AuthController();
$views = new ViewController();

if ($uri === '/login' && $method === 'GET') {
	$auth->showLogin();
	exit;
}

if ($uri === '/login' && $method === 'POST') {
	$auth->login();
	exit;
}

if ($uri === '/logout') {
	$auth->logout();
	exit;
}

if ($uri === '/dashboard') {
	$user = AuthMiddleware::check();
	if (!$user) {
		header('Location: /login');
		exit;
	}
	$views->dashboard();
	exit;
}

// fallback 404
http_response_code(404);
echo "Not Found";

