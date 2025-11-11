<?php
namespace Models;

use Core\Database;

class Category
{
    protected $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    /**
     * Transfere todos os produtos de uma categoria para a categoria padrão (id 1)
     */
    public function transferProductsToDefaultCategory(int $categoryId): bool
    {
        $stmt = $this->pdo->prepare('UPDATE products SET category_id = 1 WHERE category_id = :catid');
        return $stmt->execute([':catid' => $categoryId]);
    }

    public function findAll()
    {
        $stmt = $this->pdo->query('SELECT * FROM categories ORDER BY name');
        return $stmt->fetchAll();
    }

    public function findById($id)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM categories WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data)
    {
        $stmt = $this->pdo->prepare("INSERT INTO categories (name, description) VALUES (:name, :description)");
        $stmt->execute([
            ':name' => $data['name'],
            ':description' => $data['description'] ?? null
        ]);
        return $this->findById($this->pdo->lastInsertId());
    }

    public function update(int $id, array $data)
    {
        $stmt = $this->pdo->prepare("UPDATE categories SET name = :name, description = :description WHERE id = :id");
        $stmt->execute([
            ':id' => $id,
            ':name' => $data['name'],
            ':description' => $data['description'] ?? null
        ]);
        return $this->findById($id);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM categories WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}