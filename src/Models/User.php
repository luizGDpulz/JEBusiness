<?php
namespace Models;

use Core\Database;

class User
{
    protected $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    public function getAll()
    {
        $stmt = $this->pdo->query('SELECT * FROM users');
        return $stmt->fetchAll();
    }

    public function findByEmail(string $email)
    {
    $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        return $stmt->fetch() ?: null;
    }

    public function findById($id)
    {
    $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data)
    {
        // MySQL: use NOW() for created_at
        $stmt = $this->pdo->prepare("INSERT INTO users (name,email,password_hash,role_id,api_token_hash,created_at) VALUES (:name,:email,:password_hash,:role_id,:api_token_hash, NOW())");
        $stmt->execute([
            ':name' => $data['name'],
            ':email' => $data['email'],
            ':password_hash' => $data['password_hash'],
            ':role_id' => $data['role_id'] ?? 1,
            ':api_token_hash' => $data['api_token_hash'] ?? null,
        ]);
        return $this->findById($this->pdo->lastInsertId());
    }

    public function verifyPassword($user, $password): bool
    {
        if (!$user) return false;
        return password_verify($password, $user['password_hash']);
    }

    public function setApiToken(int $userId): string
    {
        $token = bin2hex(random_bytes(32));
        $hash = hash('sha256', $token);
        $stmt = $this->pdo->prepare('UPDATE users SET api_token_hash = :h WHERE id = :id');
        $stmt->execute([':h' => $hash, ':id' => $userId]);
        return $token; // return raw token to client once
    }

    public function findByApiToken(string $token)
    {
        $hash = hash('sha256', $token);
        // Debug: log token, hash e valor do banco
        error_log("[DEBUG] Token recebido: $token");
        error_log("[DEBUG] Hash gerado: $hash");
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE api_token_hash = :h LIMIT 1');
        $stmt->execute([':h' => $hash]);
        $user = $stmt->fetch() ?: null;
        if ($user) {
            error_log("[DEBUG] api_token_hash no banco: " . $user['api_token_hash']);
        } else {
            error_log("[DEBUG] Nenhum usuário encontrado para esse hash");
        }
        return $user;
    }
        
    public function update($id, array $data)
    {
        $sql = 'UPDATE users SET name = :name, email = :email, role_id = :role_id';
        $params = [
            ':name' => $data['name'] ?? null,
            ':email' => $data['email'] ?? null,
            ':role_id' => $data['role_id'] ?? 1,
            ':id' => $id
        ];
        if (isset($data['password_hash'])) {
            $sql .= ', password_hash = :password_hash';
            $params[':password_hash'] = $data['password_hash'];
        }
        $sql .= ' WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $this->findById($id);
    }

    public function delete($id)
    {
        $stmt = $this->pdo->prepare('DELETE FROM users WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }
    public function getRole($user)
    {            
        if (!$user || !isset($user['role_id'])) return null;
        $roleModel = new \Models\Role();
        return $roleModel->findById($user['role_id']);
    }
}
