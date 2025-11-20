<?php
namespace Controllers\Web;

class WebUserController
{
    public function index()
    {
        // Serve apenas HTML para o frontend, injecting CSRF meta tag
        $path = __DIR__ . '/../../../public/views/users.html';
        $html = file_get_contents($path);
        $csrf = \Helpers\Csrf::generate();
        $meta = '<meta name="csrf-token" content="' . htmlspecialchars($csrf, ENT_QUOTES) . '">';
        $html = str_replace('{{csrf_meta}}', $meta, $html);
        echo $html;
    }
    // Se necessário, adicione métodos para formulários web, etc.
}
