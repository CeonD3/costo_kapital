<?php 
namespace App\Controllers;

use App\Model\{User,Landing, Servicio,Template, Empresa, Contact, Design, Glosario, Team,Industria, Compania };
use App\Utilitarian\{FG};
//use Illuminate\Database\Capsule\Manager as Capsule;

class AdminController extends BaseController
{
	public function InicioAction($request) {
        $user = new User();
        $data_user = $user->Session_validate($request);
        if($data_user["success"] && $data_user["data"]->perfil == 1){
            $landing = new Landing();
            $rsp = $landing->getLanding($request);
            return $this->renderHTML('admin/inicio.twig',$rsp);
        }
        return $this->renderHTML('home/inicio.twig',compact('rsp'));
	}

    public function HomeUpdateAction($request){
        $postData = $request->getParsedBody();
        $postData["file"] = $request->getUploadedFiles();
        $landing = new Landing();
        $rsp = $landing->updateLanding($postData);
        return $rsp;
    }

    public function glosarioUpdateAction($request){
        $postData = $request->getParsedBody();
        $glosario = new Glosario();
        $rsp = $glosario->updateGlosario($postData);
        return $rsp;
    }

    public function HomeDetalleAction($request) {
        $user = new User();
        $data_user = $user->Session_validate($request);
        if($data_user["success"] && $data_user["data"]->perfil == 1){
            $Servicio = new Servicio();
            $rsp = $Servicio->getServicioDetalle($request,1);
            return $this->renderHTML('admin/home-detalle.twig',$rsp);
        }
        return $this->renderHTML('home/inicio.twig',compact('rsp'));
	}

    public function getServicioItemAction($request) {
        $postData = $request->getParsedBody();
        $Servicio = new Servicio();
        $rsp = $Servicio->getServicioItem($postData);
        
        return $rsp;
	}

    public function setServicioItemAction($request) {
        $rsp = FG::responseDefault();
        $user = new User();
        $data_user = $user->Session_validate($request);
        if($data_user["success"] && $data_user["data"]->perfil == 1){
            $postData = $request->getParsedBody();
            $postData["file"] = $request->getUploadedFiles();
            $Servicio = new Servicio();
            $rsp = $Servicio->setServicioItem($postData);
        }
        return $rsp;
	}

    public function addServicioItemAction($request) {
        $rsp = FG::responseDefault();
        $user = new User();
        $data_user = $user->Session_validate($request);
        if($data_user["success"] && $data_user["data"]->perfil == 1){
            $postData = $request->getParsedBody();
            $postData["file"] = $request->getUploadedFiles();
            $Servicio = new Servicio();
            $rsp = $Servicio->addServicioItem($postData,1);
        }
        return $rsp;
	}

    public function addIndustriaItemAction($request) {       
        $rsp = FG::responseDefault();
        $user = new User();
        $data_user = $user->Session_validate($request);
        if($data_user["success"] && $data_user["data"]->perfil == 1){
            $postData = $request->getParsedBody();
            $Industria = new Industria();
            $rsp = $Industria->addIndustriaItem($postData);
        }
        return $rsp;
	}
    
    public function getcompanias($request) {       
        $rsp = FG::responseDefault();
        $user = new User();
        $data_user = $user->Session_validate($request);
        if($data_user["success"] && $data_user["data"]->perfil == 1){
            $postData = $request->getParsedBody();
            $Compania = new Compania();
            $rsp = $Compania->getCompaniasItem($postData);
            
        }
        return $rsp;
	}

    public function addCompaniaItemAction($request) {       
        $rsp = FG::responseDefault();
        $user = new User();
        $data_user = $user->Session_validate($request);
        if($data_user["success"] && $data_user["data"]->perfil == 1){
            $postData = $request->getParsedBody();
            $Compania = new Compania();
            $rsp = $Compania->addCompaniaItem($postData);
        }
        return $rsp;
	}

    public function deleteCompaniaItemAction($request) {       
        $rsp = FG::responseDefault();
        $user = new User();
        $data_user = $user->Session_validate($request);
        if($data_user["success"] && $data_user["data"]->perfil == 1){
            $postData = $request->getParsedBody();
            $Compania = new Compania();
            $rsp = $Compania->deleteCompaniaItem($postData);
        }
        return $rsp;
	}

    public function deleteIndustriaItemAction($request) {       
        $rsp = FG::responseDefault();
        $user = new User();
        $data_user = $user->Session_validate($request);
        if($data_user["success"] && $data_user["data"]->perfil == 1){
            $postData = $request->getParsedBody();
            $Industria = new Industria();
            $rsp = $Industria->deleteIndustriaItem($postData);
        }
        return $rsp;
	}

    public function deleteServicioItemAction($request) {
        $rsp = FG::responseDefault();
        $user = new User();
        $data_user = $user->Session_validate($request);
        if($data_user["success"] && $data_user["data"]->perfil == 1){
            $postData = $request->getParsedBody();
            $postData["file"] = $request->getUploadedFiles();
            $Servicio = new Servicio();
            $rsp = $Servicio->deleteServicioItem($postData);
        }
        return $rsp;
	}
    
    public function costoCapitalAction($request) {
        $user = new User();
        $data_user = $user->Session_validate($request);
        if($data_user["success"] && $data_user["data"]->perfil == 1){
            $Servicio = new Servicio();
            $rsp = $Servicio->getServicioDetalle($request,2);
            return $this->renderHTML('admin/costo-capital.twig',$rsp);
        }
        return $this->renderHTML('home/inicio.twig',compact('rsp'));
	}

    public function costoCapitalReporteAction($request) {
        $user = new User();
        $data_user = $user->Session_validate($request);
        if($data_user["success"] && $data_user["data"]->perfil == 1){
            $Servicio = new Servicio();
            $rsp = $Servicio->getServicioDetalle($request, Servicio::TYPE_REPORT);
            return $this->renderHTML('admin/costo-capital-reporte.twig', $rsp);
        }
        return $this->renderHTML('home/inicio.twig',compact('rsp'));
	}

    public function setServicioCostoItemAction($request) {
        $rsp = FG::responseDefault();
        $user = new User();
        $data_user = $user->Session_validate($request);
        if($data_user["success"] && $data_user["data"]->perfil == 1){
            $postData = $request->getParsedBody();
            $postData["file"] = $request->getUploadedFiles();
            $Servicio = new Servicio();
            $rsp = $Servicio->setServicioItem($postData);
        }
        return $rsp;
	}

    public function addServicioCostoItemAction($request) {
        $rsp = FG::responseDefault();
        $user = new User();
        $data_user = $user->Session_validate($request);
        if($data_user["success"] && $data_user["data"]->perfil == 1){
            $postData = $request->getParsedBody();
            $postData["file"] = $request->getUploadedFiles();
            $Servicio = new Servicio();
            $rsp = $Servicio->addServicioItem($postData,2);
        }
        return $rsp;
	}

    public function contactoAction($request) {
        $user = new User();
        $data_user = $user->Session_validate($request);
        if($data_user["success"] && $data_user["data"]->perfil == 1){
            $empresa = new Empresa();
            $rsp = $empresa->getEmpresaDetalle($request);
            return $this->renderHTML('admin/contacto.twig',$rsp);
        }
        return $this->renderHTML('home/inicio.twig',compact('rsp'));
	}

    public function setContactoAction($request) {
        $rsp = FG::responseDefault();
        $user = new User();
        $data_user = $user->Session_validate($request);
        if($data_user["success"] && $data_user["data"]->perfil == 1){
            $postData = $request->getParsedBody();
            $postData["file"] = $request->getUploadedFiles();
            $empresa = new Empresa();
            $rsp = $empresa->setContacto($postData);
        }
        return $rsp;
	}

    public function information($request) {
        $user = new User();
        if($data_user["success"] && $data_user["data"]->perfil == 1){
            header ("Location: /");
        }
        $landing = new Landing();
        $data = $landing->getLanding($request)['data'];
        return $this->renderHTML('admin/information.twig',compact('data'));
	}

    public function glosario($request) {
        $user = new User();
        $data_user = $user->Session_validate($request);
        if($data_user["success"] && $data_user["data"]->perfil == 1){
            $glosario = new Glosario();
            $rsp = $glosario->getGlosarioOnly($request);

              //echo json_encode($rsp["data"]["data_industria"]);exit;

            return $this->renderHTML('admin/glosario.twig',$rsp);
        }
        return $this->renderHTML('home/inicio.twig',compact('rsp'));
	}

    public function informationSave($request) {
		$landing = new Landing();
		return $landing->informationSave($request);
	}


    public function listTemplate($request) {
		$template = new Template();
		$rsp = $template->list($request);
		return $this->renderHTML('admin/template.twig', compact('rsp'));
	}

    public function listsTemplate($request) {
		$template = new Template();
		return $template->list($request);
	}

    public function removeTemplate($request) {
		$template = new Template();
		return $template->remove($request);
	}

    public function manageTemplate($request) {
		$template = new Template();
		return $template->manage($request);
	}
    
    public function downloadMasterTemplate($request) {
		$template = new Template();
		return $template->downloadMaster($request);
	}

    public function listContacts($request) {
		$contact = new Contact();
		$rsp = $contact->lists($request);
		return $this->renderHTML('admin/contacts.twig', compact('rsp'));
	}

    public function initReportAdmin($request) {
		$design = new Design();
		$rsp = $design->list($request);
		return $this->renderHTML('admin/report/list.twig', compact('rsp'));
	}

    public function listReportKapital($request) {
		$design = new Design();
		return $design->list($request);
	}

    public function initReportValoraAdmin($request) {
		$design = new Design();
		$rsp = $design->listValora($request);
		return $this->renderHTML('admin/report/list-valora.twig', compact('rsp'));
	}

    public function listReportValora($request) {
		$design = new Design();
		return $design->listValora($request);
	}

    public function edit($request) {
		$design = new Design();
		$rsp = $design->edit($request);
		return $this->renderHTML('admin/report/form.twig', compact('rsp'));
	}

    public function showReportKapital($request) {
		$design = new Design();
		return $design->edit($request);
	}

    public function editValora($request) {
		$design = new Design();
		$rsp = $design->edit($request);
		return $this->renderHTML('admin/report/form-valora.twig', compact('rsp'));
	}

    public function showReportValora($request) {
		$design = new Design();
		return $design->edit($request);
	}

    public function create($request) {
		$design = new Design();
		$rsp = $design->create($request);
		return $this->renderHTML('admin/report/form.twig', compact('rsp'));
	}

    public function createReportKapital($request) {
		$design = new Design();
		return $design->create($request);
	}

    public function createValora($request) {
		$design = new Design();
		$rsp = $design->createValora($request);
		return $this->renderHTML('admin/report/form-valora.twig', compact('rsp'));
	}

    public function createReportValora($request) {
		$design = new Design();
		return $design->createValora($request);
	}

    public function saveDesign($request) {
		$design = new Design();
		return $design->saveData($request);
	}

    public function removeDesign($request) {
		$design = new Design();
		return $design->removeData($request);
	}

    public function teamAction($request) {
		$data = Array();
		$equipo = new Team();
        $data = array(
			"team" => $equipo->getTeam()
		);
		//var_dump($data["team"]);exit;
		//return $this->renderHTML('admin/contact-team.twig',$data);
		return $this->renderHTML('admin/credits.twig',$data);
	}

    public function getTeamItem($request) {
        extract($request->getParsedBody());       
		$equipo = new Team();
		return $equipo->getTeamById($id_team);
	}

    public function saveTeamTex($request) {
        extract($request->getParsedBody());       
		$empresa = new Empresa();
		return $empresa->saveTeamTex($team);
	}

    public function addTeam($request) {
        $team = new Team();
		return $team->addTeam($request);
	}

    public function updateTeam($request) {
        $team = new Team();
		return $team->updateTeam($request);
	}

    public function deleteTeam($request) {
        $team = new Team();
		return $team->deleteTeam($request);
	}
}

?>