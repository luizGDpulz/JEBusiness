<?php
namespace Core;

class Database
{
    /** @var \PDO|null */
    private static $instance = null;

    public static function getInstance(): \PDO
    {
        if (self::$instance === null) {
            $config = require __DIR__ . '/../../config/database.php';

            $dsn = $config['dsn'];
            $user = $config['user'] ?? null;
            $pass = $config['pass'] ?? null;
            $options = $config['options'] ?? [];

            self::$instance = new \PDO($dsn, $user, $pass, $options);
            // No sqlite-specific initialization; app expects MySQL.
        }
        return self::$instance;
    }
}
