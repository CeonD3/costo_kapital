<?php

namespace App\Utilitarian;

class View
{
    public static function render($filename, $data = [])
    {
        $loader = new \Twig\Loader\FilesystemLoader('../views');
        $twig = new \Twig\Environment($loader, [
          'debug' =>  true,
          'cache' => false, 
        ]);
        $twig->addGlobal("APP", [ "SESSION" => $_SESSION, 'URI' => $_SERVER["REQUEST_URI"], 'HOST' => $_SERVER["HTTP_HOST"], 'GET' => $_GET ]);
        return $twig->render($filename, $data);
    }
}