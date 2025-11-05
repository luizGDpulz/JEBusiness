<?php
namespace Controllers\Web;

class ViewController
{
    public function dashboard()
    {
        header('Content-Type: text/html; charset=utf-8');
        echo file_get_contents(__DIR__ . '/../../../public/views/dashboard.html');
    }
}
