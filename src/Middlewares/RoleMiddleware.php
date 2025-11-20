<?php
namespace Middlewares;

use Models\User;

class RoleMiddleware
{
    /**
     * Verifica se o usuário tem uma das roles permitidas
     * @param array $roles nomes das roles permitidas
     * @return bool true se permitido, false se bloqueado
     */
    public static function check(array $roles): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        if (empty($_SESSION['user_id'])) return false;
        $userModel = new User();
        $user = $userModel->findById($_SESSION['user_id']);
        if (!$user) return false;
        $role = $userModel->getRole($user);
        if (!$role) return false;
        return in_array($role['name'], $roles);
    }
}

