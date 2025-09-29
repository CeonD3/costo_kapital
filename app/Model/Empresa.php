<?php 
namespace App\Model;

//use App\Model\{};
use App\Utilitarian\{Crypt, FG};
use App\Persistence\{utils};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as Capsule;

class Empresa extends Model{

	protected $table = 'empresas';

    public function setContacto($postData) {
		$rsp = FG::responseDefault();
        try {
            $user = new User();
            $data_user = $user->Session_validate($request);
            $data_update = array(
                "correo" => $postData["email"],
                "telefono" => $postData["telefono"],
                "url_uno" => $postData["url"],
                "terminos_condiciones" => $postData["terminos_condiciones"],
                "user_id" => $data_user["data"]->id
            );
           
            Empresa::where('deleted_at')
            ->update($data_update);

            $rsp['success'] = true;
            $rsp['data'] = $data_update;
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
	}

    public function saveTeamTex($team_tex) {
		$rsp = FG::responseDefault();
        try {
            $user = new User();
            $data_user = $user->Session_validate($request);
            $data_update = array(
                "team" => $team_tex
            );
           
            Empresa::where('deleted_at')
            ->update($data_update);

            $rsp['success'] = true;
            $rsp['data'] = $data_update;
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
	}

    public function getEmpresaDetalle($request) {
		$rsp = FG::responseDefault();
        try {
            $sql_rsp = Empresa::where('deleted_at')->first();
            $rsp['success'] = true;
            $rsp['data'] = $sql_rsp;
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
	}
}