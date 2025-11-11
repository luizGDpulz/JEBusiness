<?php
namespace Controllers\Api;

use Models\Product;

class ProductsController
{
    private $model;
    
    public function __construct()
    {
        $this->model = new Product();
    }

    public function index()
    {
        header('Content-Type: application/json');
        $products = $this->model->findAll();
        echo json_encode(['products' => $products]);
    }

    public function show($id)
    {
        header('Content-Type: application/json');
        $product = $this->model->findById($id);
        
        if (!$product) {
            http_response_code(404);
            echo json_encode(['error' => 'Produto não encontrado']);
            return;
        }

        echo json_encode(['product' => $product]);
    }

    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true) ?: [];
        
        if (empty($data['name']) || !isset($data['price'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Nome e preço do produto são obrigatórios']);
            return;
        }
        
        // validar se o nome do produto já existe
        $allProducts = $this->model->findAll();
        foreach ($allProducts as $prod) {
            if (isset($prod['name']) && strcasecmp($prod['name'], $data['name']) === 0) {
                http_response_code(409);
                echo json_encode(['error' => 'Já existe um produto com esse nome']);
                return;
            }
        }
        
        //verificar se a categoria existe, se foi passada
        if (isset($data['category_id'])) {
            $categoryModel = new \Models\Category();
            $category = $categoryModel->findById((int)$data['category_id']);
            if (!$category) {
                http_response_code(400);
                echo json_encode(['error' => 'Categoria inválida']);
                return;
            }
        }
        
        // verificar se os campos numéricos são válidos e positivos
        if (isset($data['price']) && (!is_numeric($data['price']) || $data['price'] < 0)) {
            http_response_code(400);
            echo json_encode(['error' => 'Preço inválido. Deve ser dado numérico e positivo']);
            return;
        }
        if (isset($data['stock_qty']) && (!is_numeric($data['stock_qty']) || $data['stock_qty'] < 0)) {
            http_response_code(400);
            echo json_encode(['error' => 'Quantidade em estoque inválida']);
            return;
        }

        header('Content-Type: application/json');
        $product = $this->model->create($data);
        http_response_code(201);
        echo json_encode(['product' => $product]);
    }

    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            http_response_code(405);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true) ?: [];
        
        if (empty($data['name']) || !isset($data['price'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Nome e preço do produto são obrigatórios']);
            return;
        }
        
        // verificar se os campos numéricos são válidos e positivos
        if (isset($data['price']) && (!is_numeric($data['price']) || $data['price'] < 0)) {
            http_response_code(400);
            echo json_encode(['error' => 'Preço inválido. Deve ser dado numérico e positivo']);
            return;
        }
        if (isset($data['stock_qty']) && (!is_numeric($data['stock_qty']) || $data['stock_qty'] < 0)) {
            http_response_code(400);
            echo json_encode(['error' => 'Quantidade em estoque inválida']);
            return;
        }
        
        // validar se o nome do produto já existe, para que não seja possível editar para um nome já existente
        $allProducts = $this->model->findAll();
        foreach ($allProducts as $prod) {
            if ($prod['id'] != $id && isset($prod['name']) && strcasecmp($prod['name'], $data['name']) === 0) {
                http_response_code(409);
                echo json_encode(['error' => 'Já existe um produto com esse nome']);
                return;
            }
        }

        header('Content-Type: application/json');
        $product = $this->model->update((int)$id, $data);
        echo json_encode(['product' => $product]);
    }

    public function delete($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            http_response_code(405);
            return;
        }

        header('Content-Type: application/json');
        $success = $this->model->delete((int)$id);
        
        if (!$success) {
            http_response_code(404);
            echo json_encode(['error' => 'Produto não encontrado ou já foi deletado']);
            return;
        }

        http_response_code(200);
        echo json_encode(['message' => 'Produto deletado com sucesso']);
    }
    
}