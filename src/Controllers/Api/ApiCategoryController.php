<?php
namespace Controllers\Api;

use Models\Category;

class ApiCategoryController
{
    private $model;
    
    public function __construct()
    {
        $this->model = new Category();
    }

    public function index()
    {
        header('Content-Type: application/json; charset=utf-8');
        $categories = $this->model->findAll();
        echo json_encode(['categories' => $categories]);
    }

    public function show($id)
    {
        header('Content-Type: application/json; charset=utf-8');
        $category = $this->model->findById($id);
        
        if (!$category) {
            http_response_code(404);
            echo json_encode(['error' => 'Categoria não encontrada']);
            return;
        }

        echo json_encode(['category' => $category]);
    }

    public function edit($id)
    {
        header('Content-Type: application/json; charset=utf-8');
        $category = $this->model->findById($id);
        if (!$category) {
            http_response_code(404);
            echo json_encode(['error' => 'Categoria não encontrada']);
            return;
        }
        echo json_encode(['category' => $category]);
    }

    public function store()
    {
        $isJson = strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false
            || (stripos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false);

        $data = $isJson ? json_decode(file_get_contents('php://input'), true) : $_POST;
        $data = $data ?: [];

        if (empty($data['name'])) {
            http_response_code(400);
            if ($isJson) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['error' => 'Nome da categoria é obrigatório']);
            } else {
                echo 'Nome da categoria é obrigatório';
            }
            return;
        }
        
        // validação para nomes de categorias iguais
        $allCategories = $this->model->findAll();
        foreach ($allCategories as $cat) {
            if (isset($cat['name']) && strcasecmp($cat['name'], $data['name']) === 0) {
                http_response_code(409);
                if ($isJson) {
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode(['error' => 'Já existe uma categoria com esse nome']);
                } else {
                    echo 'Já existe uma categoria com esse nome';
                }
                return;
            }
        }

        $category = $this->model->create($data);
        if ($isJson) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(201);
            echo json_encode(['category' => $category]);
            return;
        }

        header('Location: /categories');
    }

    public function update($id)
    {
        $isJson = strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false
            || (stripos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false);

        $data = $isJson ? json_decode(file_get_contents('php://input'), true) : $_POST;
        $data = $data ?: [];

        if (empty($data['name'])) {
            http_response_code(400);
            if ($isJson) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['error' => 'Nome da categoria é obrigatório']);
            } else {
                echo 'Nome da categoria é obrigatório';
            }
            return;
        }

        $category = $this->model->findById($id);
        if (!$category) {
            http_response_code(404);
            if ($isJson) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['error' => 'Categoria não encontrada']);
            } else {
                echo 'Categoria não encontrada';
            }
            return;
        }
        
        // validação para nomes iguais
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
            if ($isJson) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['error' => 'Já existe uma categoria com esse nome']);
            } else {
                echo 'Já existe uma categoria com esse nome';
            }
            return;
        }

        $updated = $this->model->update($id, $data);
        if ($isJson) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['category' => $updated]);
            return;
        }

        header('Location: /categories');
    }

    public function delete($id)
    {
        $isJson = strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false
            || (stripos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false);

        $category = $this->model->findById($id);
        if (!$category) {
            http_response_code(404);
            if ($isJson) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['error' => 'Categoria não encontrada']);
            } else {
                echo 'Categoria não encontrada';
            }
            return;
        }

        try {
            if (!$this->model->transferProductsToDefaultCategory($id)) {
                http_response_code(500);
                if ($isJson) {
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode(['error' => 'Erro ao transferir produtos para categoria padrão.']);
                } else {
                    echo 'Erro ao transferir produtos para categoria padrão.';
                }
                return;
            }
        } catch (\Exception $e) {
            http_response_code(500);
            if ($isJson) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['error' => 'Erro ao transferir produtos para categoria padrão: ' . $e->getMessage()]);
            } else {
                echo 'Erro ao transferir produtos para categoria padrão: ' . $e->getMessage();
            }
            return;
        }

        $deleted = $this->model->delete($id);
        if ($isJson) {
            header('Content-Type: application/json; charset=utf-8');
            if ($deleted) {
                echo json_encode(['message' => 'Categoria excluída com sucesso, produtos transferidos para a categoria padrão.']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Erro ao excluir categoria']);
            }
            return;
        }

        header('Location: /categories');
    }
}