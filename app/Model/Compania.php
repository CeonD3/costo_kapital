<?php

namespace App\Model;

//use App\Model\{};
use App\Utilitarian\{Crypt, FG};
use App\Persistence\{utils};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as Capsule;

class Compania extends Model
{

    protected $fillable = ['companias'];

    const TYPE_HOME = 1;
    const TYPE_INFO = 2;
    const TYPE_REPORT = 3;

    public function getCompanias()
    {
        $rsp = FG::responseDefault();
        try {
            $sql_rsp = Compania::where('deleted_at')->get();
            $rsp['success'] = true;
            $rsp['data'] = $sql_rsp;
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function getCompaniasItem($postData)
    {
        $rsp = FG::responseDefault();
        try {
            if ($postData["id_industria"]) {
                $sql_rsp = Compania::where('deleted_at')->where('industria_id', $postData['id_industria'])->get();
                $rsp['success'] = true;
                $rsp['data'] = $sql_rsp;
                $rsp['message'] = 'successfully';
            }
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function addCompaniaItem($postData)
    {
        $rsp = FG::responseDefault();
        try {

            $industria_id = $postData["id_in"];
            $compania_id  = $postData["id_c"];


            if ($compania_id >= 1) {

                $data_update = array(
                    "nombre" => $postData["nombre_c"],
                );

                Compania::where('deleted_at')
                    ->where("id", $compania_id)
                    ->update($data_update);

                // $Glosario = Glosario::where('deleted_at')->first();  

            } else {
                $compania = new Compania();
                $compania->nombre       = $postData["nombre_c"];
                $compania->industria_id = $industria_id;
                $compania->save();
            }

            $sql_rsp = Compania::where('deleted_at')->where('industria_id', $industria_id)->get();

            $rsp['success'] = true;
            $rsp['data'] = $sql_rsp;
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function deleteCompaniaItem($postData)
    {
        $rsp = FG::responseDefault();
        try {

            $id_industria = $postData["id_industria"];
            $compania_id = $postData["id_compania"];

            $time_delete = date('Y-m-d H:i:s');

            if ($compania_id >= 1) {

                $data_update = array(
                    "deleted_at" => $time_delete
                );

                Compania::where('deleted_at')
                    ->where("id", $compania_id)
                    ->update($data_update);

                //$Glosario = Glosario::where('deleted_at')->first(); 
            }

            $sql_rsp = Compania::where('deleted_at')->where('industria_id', $id_industria)->get();

            $rsp['success'] = true;
            $rsp['data'] = $sql_rsp;
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }
}
