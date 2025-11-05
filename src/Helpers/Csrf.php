<?php
namespace Helpers;

class Csrf
{
    public static function generate(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf_token'];
    }

    public static function validate(?string $token): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        if (empty($token)) return false;
        return hash_equals($_SESSION['_csrf_token'] ?? '', $token);
    }

    public static function inputField(): string
    {
        $t = self::generate();
        return '<input type="hidden" name="_csrf" value="' . htmlspecialchars($t, ENT_QUOTES) . '">';
    }
}
