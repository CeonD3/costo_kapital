<?php

namespace App\Model;

use App\Model\{User, Servicio, Empresa};
use App\Utilitarian\{Crypt, FG};
use App\Persistence\{utils};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as Capsule;

class Landing extends Model
{

    protected $fillable = ['landings'];

    public function getLanding($request)
    {
        $rsp = FG::responseDefault();
        try {
            $sql_landing = Landing::where('deleted_at')->first();
            $rsp['success'] = true;
            $rsp['data'] = $sql_landing;
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function updateLanding($postData)
    {
        $rsp = FG::responseDefault();
        try {
            $file = $postData["file"];

            if ($file['imgBanner']) {
                $imagen = $file['imgBanner'];
                $fileName = null;
                $etiq = strtotime("now");
                if ($imagen->getError() == UPLOAD_ERR_OK) {
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
                $data_update["img"] = "/" . $ruta;
            }

            Landing::where('deleted_at')
                ->update($data_update);

            $rsp['success'] = true;
            $rsp['data'] = $data_update;
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function informationSave($request)
    {
        $rsp = FG::responseDefault();
        try {
            $file = $request->getUploadedFiles();
            $param = $request->getParsedBody();

            $landing = Landing::where('deleted_at')->first();

            if ($file['image_ajustado']) {
                $imagen = $file['image_ajustado'];
                $fileName = null;
                $etiq = uniqid(time());
                if ($imagen->getError() == UPLOAD_ERR_OK) {
                    $fileName = $imagen->getClientFilename(); //Interfaz del PSR7
                    $fileSize = $imagen->getSize(); //Interfaz del PSR7
                    $ruta = "upload/home/$etiq-$fileName";
                    $imagen->moveTo($ruta);
                    $landing->image_ajustado = "/" . $ruta;
                }
            }

            if ($file['image_bono']) {
                $imagen = $file['image_bono'];
                $fileName = null;
                $etiq = uniqid(time());
                if ($imagen->getError() == UPLOAD_ERR_OK) {
                    $fileName = $imagen->getClientFilename(); //Interfaz del PSR7
                    $fileSize = $imagen->getSize(); //Interfaz del PSR7
                    $ruta = "upload/home/$etiq-$fileName";
                    $imagen->moveTo($ruta);
                    $landing->image_bono = "/" . $ruta;
                }
            }

            $landing->url_ajustado = $param['url_ajustado'];
            $landing->url_bono = $param['url_bono'];
            $landing->save();

            $data_update = array(
                "link" => $param['link_revista']
            );

            Empresa::where('deleted_at')
                ->update($data_update);

            $rsp['success'] = true;
            $rsp['message'] = 'Se guardo correctamente';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }
}
