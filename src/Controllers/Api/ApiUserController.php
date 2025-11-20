<?php
namespace Controllers\Api;

use Models\User;
use Models\Role;

class ApiUserController
{
    public function index()
    {
        header('Content-Type: application/json; charset=utf-8');
        $userModel = new User();
        $users = $userModel->getAll();
        $roleModel = new Role();
        $roles = $roleModel->findAll();
        echo json_encode([
            'users' => $users,
            'roles' => $roles
        ]);
        return;
    }

    public function store()
    {
        $isJson = strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false;
        $data = $isJson ? json_decode(file_get_contents('php://input'), true) : $_POST;
        $userModel = new User();
        $data['password_hash'] = password_hash($data['password'], PASSWORD_ARGON2ID);
        unset($data['password']);
        $user = $userModel->create($data);
        if ($isJson) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['user' => $user]);
            return;
        }
        header('Location: /users');
    }

    public function edit($id)
    {
        $isJson = strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false;
        $userModel = new User();
        $user = $userModel->findById($id);
        $roleModel = new Role();
        $roles = $roleModel->findAll();
        if ($isJson) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['user' => $user, 'roles' => $roles]);
            return;
        }
        include __DIR__ . '/../../../public/views/user_form.html';
    }

    public function update($id)
    {
        $isJson = strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false;
        $data = $isJson ? json_decode(file_get_contents('php://input'), true) : $_POST;
        $userModel = new User();
        if (!empty($data['password'])) {
            $data['password_hash'] = password_hash($data['password'], PASSWORD_ARGON2ID);
        }
        unset($data['password']);
        $user = $userModel->update($id, $data);
        if ($isJson) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['user' => $user]);
            return;
        }
        header('Location: /users');
    }

    public function delete($id)
    {
        $isJson = strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false;
        $userModel = new User();
        $deleted = $userModel->delete($id);
        if ($isJson) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['deleted' => (bool)$deleted, 'id' => $id]);
            return;
        }
        header('Location: /users');
    }
}
