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
use Controllers\Api\ProductsController;
use Controllers\Api\CategoryController;
use Middlewares\AuthMiddleware;


$auth = new AuthController();
$views = new ViewController();
$products = new ProductsController();
$categories = new CategoryController();


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

// API de Produtos - listar ou criar
if ($uri === '/api/products') {
	$user = AuthMiddleware::check();
	if (!$user) {
		http_response_code(401);
		header('Content-Type: application/json');
		echo json_encode(['error' => 'Não autorizado']);
		exit;
	}
	if ($method === 'GET') {
		$products->index();
	} elseif ($method === 'POST') {
		$products->create();
	} else {
		http_response_code(405);
		header('Content-Type: application/json');
		echo json_encode(['error' => 'Método não permitido']);
	}
	exit;
}

// API de Produtos - por ID (GET detalhes, PUT atualizar, DELETE deletar)
if (preg_match('#^/api/products/(\d+)$#', $uri, $matches)) {
	$user = AuthMiddleware::check();
	if (!$user) {
		http_response_code(401);
		header('Content-Type: application/json');
		echo json_encode(['error' => 'Não autorizado']);
		exit;
	}
	$id = $matches[1];
	if ($method === 'GET') {
		$products->show($id);
	} elseif ($method === 'PUT') {
		$products->update($id);
	} elseif ($method === 'DELETE') {
		$products->delete($id);
	} else {
		http_response_code(405);
		header('Content-Type: application/json');
		echo json_encode(['error' => 'Método não permitido']);
	}
	exit;
}

// Página de Produtos (SPA que carrega dados via fetch)
if ($uri === '/produtos' && $method === 'GET') {
	$user = AuthMiddleware::check();
	if (!$user) {
		header('Location: /login');
		exit;
	}
	// Serve apenas o HTML; dados vêm via fetch para /api/products
	header('Content-Type: text/html; charset=utf-8');
	echo file_get_contents(__DIR__ . '/../public/views/products_list.html');
	exit;
}

// API de Categorias - listar ou criar
if ($uri === '/api/categories') {
	$user = AuthMiddleware::check();
	if (!$user) {
		http_response_code(401);
		header('Content-Type: application/json');
		echo json_encode(['error' => 'Não autorizado']);
		exit;
	}
	if ($method === 'GET') {
		$categories->index();
	} elseif ($method === 'POST') {
		$categories->create();
	} else {
		http_response_code(405);
		header('Content-Type: application/json');
		echo json_encode(['error' => 'Método não permitido']);
	}
	exit;
}

// API de Categorias - por ID (GET detalhes, PUT atualizar, DELETE deletar)
if (preg_match('#^/api/categories/(\d+)$#', $uri, $matches)) {
	$user = AuthMiddleware::check();
	if (!$user) {
		http_response_code(401);
		header('Content-Type: application/json');
		echo json_encode(['error' => 'Não autorizado']);
		exit;
	}
	$id = $matches[1];
	if ($method === 'GET') {
		$categories->show($id);
	} elseif ($method === 'PUT') {
		$categories->update($id);
	} elseif ($method === 'DELETE') {
		$categories->delete($id);
	} else {
		http_response_code(405);
		header('Content-Type: application/json');
		echo json_encode(['error' => 'Método não permitido']);
	}
	exit;
}

// fallback 404
http_response_code(404);
echo "Not Found - Index.php";