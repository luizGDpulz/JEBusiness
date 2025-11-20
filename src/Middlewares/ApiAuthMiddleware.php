<?php
namespace Middlewares;

use Models\User;

class ApiAuthMiddleware
{
    /**
     * Retorna usuário autenticado via Bearer token ou null
     */
    public static function check(): ?array
    {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['Authorization'] ?? '';
        error_log("[DEBUG] Authorization header recebido: $authHeader");
        if (preg_match('/Bearer\s+(\S+)/', $authHeader, $m)) {
            $token = $m[1];
            error_log("[DEBUG] Token extraído do header: $token");
            $userModel = new User();
            $user = $userModel->findByApiToken($token);
            return $user ?: null;
        } else {
            error_log("[DEBUG] Bearer token não encontrado no header");
        }
        return null;
    }

    /**
     * Verifica se usuário autenticado tem uma das roles
     */
    public static function checkRole(array $roles): bool
    {
        $user = self::check();
        if (!$user) return false;
        $roleId = $user['role_id'] ?? null;
        // 99 = admin, 2 = vendedor, 1 = cliente
        $roleMap = [99 => 'admin', 2 => 'vendedor', 1 => 'cliente'];
        $roleName = $roleMap[$roleId] ?? null;
        return in_array($roleName, $roles);
    }
}
