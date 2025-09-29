<?php

namespace App\Controllers;

use App\Model;
use App\Model\{User, Landing, Servicio, Empresa, Contact, Team};
use Illuminate\Database\Capsule\Manager as Capsule;

class HomeController extends BaseController
{

    public function kblive()
    {
        $data = array(
            "success" => true,
            "message" => 'successfully state live'
        );
        return $data;
    }


    // Aceptar $request (puede ser null)
    public function inicioAction($request = null)
    {
        $landing = new Landing();
        $servicio = new Servicio();
        $empresa = new Empresa();

        $data = array(
            "landing" => $landing->getLanding($request),
            "servicio" => $servicio->getServicioDetalle($request, 1),
            "contacto" => $empresa->getEmpresaDetalle($request)
        );

        return $this->renderHTML('home/inicio.twig', $data, $request);
    }

    public function costoCapitalAction($request = null)
    {
        $data = array();
        $servicio = new Servicio();
        $data = array(
            "servicio" => $servicio->getServicioDetalle($request, 2)
        );
        return $this->renderHTML('home/costo-capital.twig', $data, $request);
    }

    public function costoCapitalReporteAction($request = null)
    {
        $data = array();
        $servicio = new Servicio();
        $data = array(
            "servicio" => $servicio->getServicioDetalle($request, Servicio::TYPE_REPORT)
        );
        return $this->renderHTML('home/panel.reporte.twig', $data, $request);
    }

    public function equipoAction($request = null)
    {
        $data = array();
        $equipo = new Team();
        $data = array(
            "team" => $equipo->getTeam()
        );
        //var_dump($data["team"]);exit;
        return $this->renderHTML('home/panel.team.twig', $data, $request);
    }

    public function contactoAction($request = null)
    {
        $data = array();
        return $this->renderHTML('home/panel.contact.twig', $data, $request);
    }

    public function loginAction($request = null)
    {
        $data = array();
        return $this->renderHTML('home/login.twig', $data, $request);
    }

    public function postLogin($request)
    {
        $user = new User();
        return $user->postLogin($request);
    }

    public function singoutAction($request)
    {
        $user = new User();
        return $user->outLogin($request);
    }

    public function registerUserAction($request)
    {
        $user = new User();
        return $user->registerUser($request);
    }

    public function sendContact($request)
    {
        $contact = new Contact();
        return $contact->sendContact($request);
    }

    public function forgotPasswordUser($request)
    {
        $user = new User();
        return $user->forgotPasswordUser($request);
    }

    public function recoverPassword($request)
    {
        $user = new User();
        $rsp = $user->recoverPassword($request);
        return $this->renderHTML('home/reset.password.twig', compact('rsp'), $request);
    }

    public function postRecoverPassword($request)
    {
        $user = new User();
        return $user->postRecoverPassword($request);
    }

    public function initProfile($request)
    {
        $user = new User();
        $rsp = $user->getUserSession($request);
        return $this->renderHTML('system/profile.twig', compact('rsp'), $request);
    }

    public function onSavePassword($request)
    {
        $user = new User();
        return $user->onSavePassword($request);
    }

    public function onSaveUserData($request)
    {
        $user = new User();
        return $user->onSaveUserData($request);
    }
}
