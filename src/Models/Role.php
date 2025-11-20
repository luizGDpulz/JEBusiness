<?php
namespace Models;

use Core\Database;

class Role
{
    protected $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    public function findAll()
    {
        $stmt = $this->pdo->query('SELECT * FROM roles');
        return $stmt->fetchAll();
    }

    public function findById($id)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM roles WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function findByName($name)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM roles WHERE name = :name LIMIT 1');
        $stmt->execute([':name' => $name]);
        return $stmt->fetch() ?: null;
    }
}
