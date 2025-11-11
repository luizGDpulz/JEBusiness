<?php
namespace Controllers\Api;

use Models\Category;

class CategoryController
{
    private $model;
    
    public function __construct()
    {
        $this->model = new Category();
    }

    public function index()
    {
        header('Content-Type: application/json');
        $categories = $this->model->findAll();
        echo json_encode(['categories' => $categories]);
    }

    public function show($id)
    {
        header('Content-Type: application/json');
        $category = $this->model->findById($id);
        
        if (!$category) {
            http_response_code(404);
            echo json_encode(['error' => 'Categoria não encontrada']);
            return;
        }

        echo json_encode(['category' => $category]);
    }

    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true) ?: [];
        
        if (empty($data['name'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Nome da categoria é obrigatório']);
            return;
        }
        
        // fazer uma validação para nomes de categorias iguais, para que não seja possível editar para um nome já existente
        $allCategories = $this->model->findAll();
        foreach ($allCategories as $cat) {
            if (isset($cat['name']) && strcasecmp($cat['name'], $data['name']) === 0) {
                http_response_code(409);
                echo json_encode(['error' => 'Já existe uma categoria com esse nome']);
                return;
            }
        }

        header('Content-Type: application/json');
        $category = $this->model->create($data);
        http_response_code(201);
        echo json_encode(['category' => $category]);
    }

    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'PATCH') {
            http_response_code(405);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true) ?: [];
        
        if (empty($data['name'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Nome da categoria é obrigatório']);
            return;
        }
        
        if($id == 1){
            http_response_code(403);
            echo json_encode(['error' => 'A categoria padrão não pode ser alterada']);
            return;
        }

        $category = $this->model->findById($id);
        if (!$category) {
            http_response_code(404);
            echo json_encode(['error' => 'Categoria não encontrada']);
            return;
        }
        
        // fazer uma validação para nomes de categorias iguais, para que não seja possível editar para um nome já existente
        $existingCategory = null;
        $allCategories = $this->model->findAll();
        foreach ($allCategories as $cat) {
            if (isset($cat['name']) && strcasecmp($cat['name'], $data['name']) === 0) {
                $existingCategory = $cat;
                break;
            }
        }
        if ($existingCategory && $existingCategory['id'] != $id) {
            http_response_code(409);
            echo json_encode(['error' => 'Já existe uma categoria com esse nome']);
            return;
        }

        header('Content-Type: application/json');
        $updated = $this->model->update($id, $data);
        echo json_encode(['category' => $updated]);
    }

    public function delete($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            http_response_code(405);
            return;
        }

        if ($id == 1) {
            http_response_code(403);
            echo json_encode(['error' => 'A categoria padrão não pode ser deletada']);
            return;
        }

        $category = $this->model->findById($id);
        if (!$category) {
            http_response_code(404);
            echo json_encode(['error' => 'Categoria não encontrada']);
            return;
        }

        // Transferir produtos para categoria padrão (id 1)
        try {
            if (!$this->model->transferProductsToDefaultCategory($id)) {
                http_response_code(500);
                echo json_encode(['error' => 'Erro ao transferir produtos para categoria padrão.']);
                return;
            }
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao transferir produtos para categoria padrão: ' . $e->getMessage()]);
            return;
        }

        header('Content-Type: application/json');
        if ($this->model->delete($id)) {
            echo json_encode(['message' => 'Categoria excluída com sucesso, produtos transferidos para a categoria padrão.']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao excluir categoria']);
        }
    }
}