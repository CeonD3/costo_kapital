<?php

namespace App\Model;

//use App\Model\{};
use App\Utilitarian\{Crypt, FG};
use App\Persistence\{utils};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as Capsule;

class Servicio extends Model
{

    protected $fillable = ['servicios'];

    const TYPE_HOME = 1;
    const TYPE_INFO = 2;
    const TYPE_REPORT = 3;

    public function getServicioDetalle($request, $tipo)
    {
        $rsp = FG::responseDefault();
        try {
            $sql_rsp = Servicio::where('deleted_at')->where('tipo', $tipo)->get();
            $rsp['success'] = true;
            $rsp['data'] = $sql_rsp;
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function getServicioItem($postData)
    {
        $rsp = FG::responseDefault();
        try {
            if ($postData["id_servicio"]) {
                $sql_rsp = Servicio::where('deleted_at')->where('id', $postData['id_servicio'])->first();
                $rsp['success'] = true;
                $rsp['data'] = $sql_rsp;
                $rsp['message'] = 'successfully';
            }
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function setServicioItem($postData)
    {
        $rsp = FG::responseDefault();
        try {
            $file = $postData["file"];

            if ($file['imgBanner']) {
                $imagen = $file['imgBanner'];
                $fileName = null;
                $etiq = strtotime("now");
                if ($imagen->getError() == UPLOAD_ERR_OK) {
                    $fileType = explode("/", $imagen->getClientMediaType())[0];
                    $fileName = $imagen->getClientFilename(); //Interfaz del PSR7
                    $fileSize = $imagen->getSize(); //Interfaz del PSR7
                    $ruta = "upload/home/$etiq-$fileName";
                    $imagen->moveTo($ruta);
                }
            }

            $user = new User();
            $data_user = $user->Session_validate($request);
            $data_update = array(
                "titulo" => $postData["titulo"],
                "stitulo" => $postData["stitulo"],
                "user_id" => $data_user["data"]->id
            );

            if ($ruta) {
                $data_update["icono"] = "/" . $ruta;
                $data_update["tipo_file"] = $fileType;
            }

            Servicio::where('deleted_at')
                ->where("id", $postData["id_servicio"])
                ->update($data_update);

            $rsp['success'] = true;
            $rsp['data'] = $data_update;
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function addServicioItem($postData, $tipo)
    {
        $rsp = FG::responseDefault();
        try {
            $file = $postData["file"];

            if ($file['imgBanner']) {
                $imagen = $file['imgBanner'];
                $fileName = null;
                $etiq = strtotime("now");
                if ($imagen->getError() == UPLOAD_ERR_OK) {
                    $fileType = explode("/", $imagen->getClientMediaType())[0];
                    $fileName = $imagen->getClientFilename(); //Interfaz del PSR7
                    $fileSize = $imagen->getSize(); //Interfaz del PSR7
                    $ruta = "upload/home/$etiq-$fileName";
                    $imagen->moveTo($ruta);
                }
            }

            $user = new User();
            $data_user = $user->Session_validate($request);

            $servicio = new Servicio();
            $servicio->titulo = $postData["titulo"];
            $servicio->stitulo = $postData["stitulo"];
            $servicio->user_id = $data_user["data"]->id;
            $servicio->tipo = isset($postData["tipo_id"]) ? $postData["tipo_id"] : $tipo;

            if ($ruta) {
                $servicio->icono = "/" . $ruta;
                $servicio->tipo_file = $fileType;
            }
            $servicio->save();

            $rsp['success'] = true;
            $rsp['data'] = $servicio;
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function deleteServicioItem($postData)
    {
        $rsp = FG::responseDefault();
        try {
            $user = new User();
            $data_user = $user->Session_validate($request);
            $time_delete = date('Y-m-d H:i:s');

            $data_update = array(
                "deleted_at" => $time_delete,
                "user_id" => $data_user["data"]->id
            );

            if ($ruta) {
                $data_update["icono"] = "/" . $ruta;
            }

            Servicio::where('deleted_at')
                ->where("id", $postData["id_servicio"])
                ->update($data_update);

            $rsp['success'] = true;
            $rsp['data'] = $data_update;
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }
}
