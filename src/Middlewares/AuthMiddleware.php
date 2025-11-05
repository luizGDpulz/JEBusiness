<?php
namespace Middlewares;

use Models\User;

class AuthMiddleware
{
    /**
     * Handle web routes: returns user array on success, or null on failure.
     */
    public static function check(): ?array
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        // check session
        if (!empty($_SESSION['user_id'])) {
            $userModel = new User();
            $user = $userModel->findById($_SESSION['user_id']);
            if ($user) return $user;
        }

        // check bearer token
        $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['Authorization'] ?? null;
        if ($auth && preg_match('/Bearer\s+(\S+)/', $auth, $m)) {
            $token = $m[1];
            $userModel = new User();
            $user = $userModel->findByApiToken($token);
            if ($user) return $user;
        }

        return null;
    }
}
