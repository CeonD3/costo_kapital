<?php

namespace App\Model;

//use App\Model\{};
use App\Utilitarian\{Crypt, FG};
use App\Persistence\{utils};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as Capsule;

class Industria extends Model
{

    protected $fillable = ['industrias'];

    const TYPE_HOME = 1;
    const TYPE_INFO = 2;
    const TYPE_REPORT = 3;

    public function getIndustrias()
    {
        $rsp = FG::responseDefault();
        try {
            $sql_rsp = Industria::where('deleted_at')->get();

            $rsp['success'] = true;
            $rsp['data'] = $sql_rsp;
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function getIndustriasAllCompanias()
    {
        $rsp = FG::responseDefault();
        try {

            $data_industrias = Industria::where('deleted_at')->get();

            $data_companias = Capsule::table('industrias')
                ->join('companias', 'companias.industria_id', '=', 'industrias.id')
                ->where('industrias.deleted_at', NULL)
                ->select('industrias.id as industria_id', 'industrias.nombre as nombre_industria', 'companias.id as compania_id', 'companias.nombre as compania_nombre')
                ->get();


            $data_total = array();
            for ($i = 0; $i < count($data_industrias); $i++) {


                $data_in = array(
                    "industria_id"     => $data_industrias[$i]->id,
                    "nombre_industria" => $data_industrias[$i]->nombre,
                    "companias"        => []
                );

                array_push($data_total, $data_in);


                for ($c = 0; $c < count($data_companias); $c++) {

                    if ($data_industrias[$i]->id  ==  $data_companias[$c]->industria_id) {
                        $data_com = array(
                            "compania_id"     => $data_companias[$c]->compania_id,
                            "nombre_compania" => $data_companias[$c]->compania_nombre
                        );


                        array_push($data_total[$i]["companias"], $data_com);
                    }
                }
            }

            $rsp['success'] = true;
            $rsp['data'] = $data_total;
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function addIndustriaItem($postData)
    {
        $rsp = FG::responseDefault();
        try {

            $industria_id = $postData["id_i"];

            if ($industria_id >= 1) {

                $data_update = array(
                    "nombre" => $postData["nombre_i"],
                );

                Industria::where('deleted_at')
                    ->where("id", $industria_id)
                    ->update($data_update);

                // $Glosario = Glosario::where('deleted_at')->first();  

            } else {
                $industria = new Industria();
                $industria->nombre = $postData["nombre_i"];
                $industria->save();
            }



            $rsp['success'] = true;
            $rsp['data'] = "";
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function deleteIndustriaItem($postData)
    {
        $rsp = FG::responseDefault();
        try {

            $time_delete = date('Y-m-d H:i:s');

            $id_industria = $postData["id_industria"];

            $data_update = array(
                "deleted_at" => $time_delete
            );

            Industria::where('deleted_at')
                ->where("id", $id_industria)
                ->update($data_update);

            $rsp['success'] = true;
            $rsp['data'] = "";
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }
}
