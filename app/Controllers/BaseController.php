<?php

namespace App\Controllers;

use App\Model\{Empresa, Landing, Glosario};
use Curl;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\Diactoros\Response\TextResponse;
use \Firebase\JWT\JWT;
use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * 
 */
class BaseController
{
  protected $templateEngine;

  public function __construct()
  {
    // Buscar rutas posibles para las plantillas
    $candidates = [
        __DIR__ . '/../views',
        __DIR__ . '/../../views',
        __DIR__ . '/../../resources/views',
        __DIR__ . '/../Views',
        __DIR__ . '/../../app/views',
    ];

    $viewsPath = null;
    foreach ($candidates as $p) {
        if (is_dir($p)) {
            $viewsPath = $p;
            break;
        }
    }

    if (!$viewsPath) {
        throw new \RuntimeException('Twig views directory not found. Tried: ' . implode(', ', $candidates));
    }

    $loader = new \Twig\Loader\FilesystemLoader($viewsPath);
    $this->templateEngine = new \Twig\Environment($loader, [
        'debug' => true,
        'cache' => false,
    ]);
    $this->templateEngine->addGlobal("APP", [
      "SESSION" => $_SESSION ?? [],
      'URI' => $_SERVER['REQUEST_URI'] ?? '',
      'HOST' => $_SERVER['HTTP_HOST'] ?? '',
      'GET' => $_GET ?? [],
      'MONEDA' => 'PEN'
    ]);
  }

  public function renderHTML($filename, $data = [], $request = null)
  {
    // sólo iniciar sesión si no hay una activa
    if (session_status() !== PHP_SESSION_ACTIVE) {
      session_start();
    }

    if (!empty($_SESSION['user'])) {
      $data["user_session"] = $_SESSION['user'];
    }

    $empresa = new Empresa();
    $glosario = new Glosario();

    $req = $request ?? $_REQUEST;

    $data["contacto"] = method_exists($empresa, 'getEmpresaDetalle') ? $empresa->getEmpresaDetalle($req) : null;
    $data["interface"] = Landing::whereNull('deleted_at')->first();
    $data['URI'] = $_SERVER["REQUEST_URI"] ?? '';
    $data['glosario'] = method_exists($glosario, 'getGlosario') ? $glosario->getGlosario($req) : [];

    return new HtmlResponse($this->templateEngine->render($filename, $data));
  }
}

class Response
{

  public static function json($data = [], $status = 200)
  {
    // add params []
    return new JsonResponse($data, $status);
  }

  public static function view($filename, $data = [])
  {
    // add params []
    return new HtmlResponse(Twig::render($filename, $data));
  }
}
