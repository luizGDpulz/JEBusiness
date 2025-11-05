<?php

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
// Use host-only cookies by omitting the 'domain' option. This avoids problems
// with hosts that include ports (e.g. localhost:8080) and keeps the cookie
// scope limited to the request host.
session_set_cookie_params([
	'lifetime' => 0,
	'path' => '/',
	'secure' => $secure,
	'httponly' => true,
	'samesite' => 'Lax',
]);

// Make sure session starts at the very beginning
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
    if (!isset($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }
}

// Debug session info
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log('Session ID: ' . session_id());
    error_log('Session Data: ' . print_r($_SESSION, true));
    error_log('Cookie Data: ' . print_r($_COOKIE, true));
}

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
echo "Not Found - Index.php";