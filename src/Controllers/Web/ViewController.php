<?php
namespace Controllers\Web;


class ViewController
{
    public function dashboard()
    {
        header('Content-Type: text/html; charset=utf-8');
        echo file_get_contents(__DIR__ . '/../../../public/views/dashboard.html');
        // echo "Token na sessão = " . ($_SESSION['_csrf_token'] ?? 'não existe') . "<br>";
        // echo "Session ID = " . session_id() . "<br>";
        // echo "Cookie da sessão existe? " . (isset($_COOKIE[session_name()]) ? 'Sim' : 'Não') . "<br>";
        // echo "Conteúdo da sessão:<br>";
        // var_dump($_SESSION);
    }
}