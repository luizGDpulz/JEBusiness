<?php
namespace Controllers\Api;

use Models\User;
use Helpers\Csrf;

class AuthController
{
    public function showLogin()
    {
        // serve HTML with CSRF token injection
        header('Content-Type: text/html; charset=utf-8');
        $html = file_get_contents(__DIR__ . '/../../../public/views/login.html');
        $csrfField = \Helpers\Csrf::inputField();
        $html = str_replace('{{csrf_input}}', $csrfField, $html);
        echo $html;
    }

    public function login()
    {
        // accept form or JSON
        $isJson = strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false
            || (stripos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // parse input
            if ($isJson) {
                $data = json_decode(file_get_contents('php://input'), true) ?: [];
                $email = $data['email'] ?? null;
                $password = $data['password'] ?? null;
                $csrf = $data['_csrf'] ?? null;
            } else {
                $email = $_POST['email'] ?? null;
                $password = $_POST['password'] ?? null;
                $csrf = $_POST['_csrf'] ?? null;
            }

            // CSRF for non-API
            if (!$isJson && !Csrf::validate($csrf)) {
                http_response_code(400);
                echo 'CSRF token inválido';
                return;
            }

            if (empty($email) || empty($password)) {
                http_response_code(400);
                echo 'Email e senha são obrigatórios';
                return;
            }

            $userModel = new User();
            $user = $userModel->findByEmail($email);
            if (!$user || !$userModel->verifyPassword($user, $password)) {
                http_response_code(401);
                echo $isJson ? json_encode(['error' => 'Credenciais inválidas']) : 'Credenciais inválidas';
                return;
            }

            // successful login
            if (session_status() !== PHP_SESSION_ACTIVE) session_start();
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];

            // generate api token for API usage
            $token = $userModel->setApiToken((int)$user['id']);

            if ($isJson) {
                header('Content-Type: application/json');
                echo json_encode(['token' => $token, 'user' => ['id' => $user['id'], 'email' => $user['email'], 'name' => $user['name']]]);
                return;
            }

            // redirect to dashboard
            header('Location: /dashboard');
        }
    }

    public function logout()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        // destroy session and cookie
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'] ?? '/',
                $params['domain'] ?? '',
                $params['secure'] ?? false,
                $params['httponly'] ?? true
            );
        }
        session_destroy();
        header('Location: /login');
    }
}
