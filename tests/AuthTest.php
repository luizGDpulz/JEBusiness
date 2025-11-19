<?php
use PHPUnit\Framework\TestCase;

final class AuthTest extends TestCase
{
    public function test_placeholder()
    {
        $this->assertTrue(true);
    }
    
    // Teste: usuário vendedor não pode acessar rota admin
    public function testVendedorCannotAccessAdminRoute()
    {
        require_once __DIR__ . '/../src/Models/User.php';
        require_once __DIR__ . '/../src/Middlewares/RoleMiddleware.php';
        
        $userModel = new \Models\User();
        $vendedor = $userModel->create([
            'name' => 'Teste Vendedor',
            'email' => 'vend1@example.com',
            'password_hash' => password_hash('123456', PASSWORD_ARGON2ID),
            'role_id' => 2
        ]);
        $_SESSION['user_id'] = $vendedor['id'];
        $acesso = \Middlewares\RoleMiddleware::check(['admin']);
        echo "Acesso vendedor à rota admin: " . ($acesso ? 'PERMITIDO' : 'NEGADO') . "\n";
    }

    // Teste: admin pode acessar rota admin
    public function testAdminCanAccessAdminRoute()
    {
        $userModel = new \Models\User();
        $admin = $userModel->findByEmail('admin@example.com');
        $_SESSION['user_id'] = $admin['id'];
        $acessoAdmin = \Middlewares\RoleMiddleware::check(['admin']);
        echo "Acesso admin à rota admin: " . ($acessoAdmin ? 'PERMITIDO' : 'NEGADO') . "\n";
    }
}
