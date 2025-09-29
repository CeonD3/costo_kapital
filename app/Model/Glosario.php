<?php

namespace App\Model;

use App\Model\{User, Servicio, Empresa, Industria};
use App\Utilitarian\{Crypt, FG};
use App\Persistence\{utils};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as Capsule;

class Glosario extends Model
{

    protected $fillable = ['glosarios'];

    public function getGlosarioOnly($request)
    {
        $rsp = FG::responseDefault();
        try {
            $sql_glosario = Glosario::where('deleted_at')->first();

            $industria = new Industria();
            //$sql_industrias = $industria->getIndustrias(); 

            $sql_industrias = $industria->getIndustriasAllCompanias();

            //echo json_encode($sql_industrias["data"] );
            //exit;


            $data = array("data_glosario" => $sql_glosario, "data_industria" => $sql_industrias["data"]);

            $rsp['success'] = true;
            $rsp['data'] = $data;
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function getGlosario($request)
    {
        $rsp = FG::responseDefault();
        try {
            $sql_glosario = Glosario::where('deleted_at')->first();

            $industria = new Industria();
            $sql_industrias = $industria->getIndustriasAllCompanias();


            $data = array("data_glosario" => $sql_glosario, "data_industria" => $sql_industrias["data"]);

            $rsp['success'] = true;
            $rsp['data'] = $data;
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function updateGlosario($postData)
    {
        $rsp = FG::responseDefault();
        try {

            $sql_glosario = Glosario::where('deleted_at')->first();

            if ($sql_glosario) {

                $data_update = array(
                    "descripcion" => $postData["g_descripcion"],
                    "titulo_b"    => $postData["g_titulo_b"]
                );

                Glosario::where('deleted_at')
                    ->where("id", $sql_glosario->id)
                    ->update($data_update);

                $Glosario = Glosario::where('deleted_at')->first();
            } else {

                $Glosario = new Glosario();
                $Glosario->descripcion = $postData["g_descripcion"];
                $Glosario->titulo_b    = $postData["g_titulo_b"];
                $Glosario->save();
            }


            $rsp['success'] = true;
            $rsp['data'] = "";
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }
}
