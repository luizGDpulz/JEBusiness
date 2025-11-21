<?php
namespace Controllers\Web;

class WebCategoryController
{
    public function index()
    {
        header('Content-Type: text/html; charset=utf-8');
        $path = __DIR__ . '/../../../public/views/categories.html';
        $html = file_get_contents($path);
        $csrf = \Helpers\Csrf::generate();
        $meta = '<meta name="csrf-token" content="' . htmlspecialchars($csrf, ENT_QUOTES) . '">';
        $html = str_replace('{{csrf_meta}}', $meta, $html);
        echo $html;
    }
}
