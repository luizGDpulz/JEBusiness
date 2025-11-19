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
use Controllers\Api\UserController;
use Controllers\Web\ViewController;
use Middlewares\AuthMiddleware;
use Middlewares\ApiAuthMiddleware;

$auth = new AuthController();
$userController = new UserController();
$views = new ViewController();

// Login routes
if ($uri === '/login') {
	if ($method === 'GET') {
		$auth->showLogin();
		exit;
	}
	if ($method === 'POST') {
		$auth->login();
		exit;
	}

}

// Logout route
if ($uri === '/logout') {
	$auth->logout();
	exit;
}

// Home / Dashboard route
if ($uri === '/home' || $uri === '/' || $uri === '' || $uri === '/index.php') {
	$user = AuthMiddleware::check();
	if (!$user) {
		header('Location: /login');
		exit;
	}
	$views->dashboard();
	exit;
}

// Rotas API (JSON)
if (strpos($uri, '/api/') === 0) {
	$user = \Middlewares\ApiAuthMiddleware::check();
	if (!$user) {
		http_response_code(401);
		header('Content-Type: application/json');
		echo json_encode(['error' => 'Token inválido ou ausente']);
		exit;
	}
	// Permissão: apenas admin pode acessar /api/users
	$isAdmin = \Middlewares\ApiAuthMiddleware::checkRole(['admin']);
	if ($uri === '/api/users' && $method === 'GET') {
		if (!$isAdmin) {
			http_response_code(403);
			header('Content-Type: application/json');
			echo json_encode(['error' => 'Acesso negado']);
			exit;
		}
		$userController->index();
		exit;
	}
	if ($uri === '/api/users/create' && $method === 'POST') {
		if (!$isAdmin) {
			http_response_code(403);
			header('Content-Type: application/json');
			echo json_encode(['error' => 'Acesso negado']);
			exit;
		}
		$userController->store();
		exit;
	}
	if (preg_match('#^/api/users/edit/(\d+)$#', $uri, $m) && $method === 'GET') {
		if (!$isAdmin) {
			http_response_code(403);
			header('Content-Type: application/json');
			echo json_encode(['error' => 'Acesso negado']);
			exit;
		}
		$userController->edit($m[1]);
		exit;
	}
	if (preg_match('#^/api/users/update/(\d+)$#', $uri, $m) && $method === 'POST') {
		if (!$isAdmin) {
			http_response_code(403);
			header('Content-Type: application/json');
			echo json_encode(['error' => 'Acesso negado']);
			exit;
		}
		$userController->update($m[1]);
		exit;
	}
	if (preg_match('#^/api/users/delete/(\d+)$#', $uri, $m) && $method === 'POST') {
		if (!$isAdmin) {
			http_response_code(403);
			header('Content-Type: application/json');
			echo json_encode(['error' => 'Acesso negado']);
			exit;
		}
		$userController->delete($m[1]);
		exit;
	}
}
// Rotas web (sessão, CSRF)
if (strpos($uri, '/users') === 0) {
	$user = AuthMiddleware::check();
	if (!$user) {
		header('Location: /login');
		exit;
	}
	if ($user['role_id'] != 99) {
		http_response_code(403);
		echo 'Acesso negado.';
		exit;
	}
	// CRUD via modais na mesma rota
	if ($uri === '/users' && $method === 'GET') {
		$userController->index();
		exit;
	}
	if ($uri === '/users/create' && $method === 'POST') {
		$userController->store();
		exit;
	}
	if (preg_match('#^/users/edit/(\d+)$#', $uri, $m) && $method === 'GET') {
		$userController->edit($m[1]);
		exit;
	}
	if (preg_match('#^/users/update/(\d+)$#', $uri, $m) && $method === 'POST') {
		$userController->update($m[1]);
		exit;
	}
	if (preg_match('#^/users/delete/(\d+)$#', $uri, $m) && $method === 'POST') {
		$userController->delete($m[1]);
		exit;
	}
}

// fallback 404
http_response_code(404);
echo "Not Found - Index.php";