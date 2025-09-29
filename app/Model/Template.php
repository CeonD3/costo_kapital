<?php

namespace App\Model;

use App\Utilitarian\{Crypt, FG};
use App\Persistence\{utils};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as Capsule;

class Template extends Model
{

    protected $fillable = ['templates'];

    public function list($request)
    {
        $rsp = FG::responseDefault();
        try {
            $templates = Template::where('deleted_at')->get()->toArray();
            $rsp['success'] = true;
            $rsp['data'] = compact('templates');
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function manage($request)
    {
        $rsp = FG::responseDefault();
        try {
            extract($request->getParsedBody());
            if (!$name) {
                throw new \Exception('The name is required');
            }
            if (!is_numeric($id)) {
                if (0 < $_FILES['file']['error']) {
                    throw new \Exception('The file is required');
                }
            }

            $template = is_numeric($id) ? Template::where('id', $id)->first() : new Template();

            if (isset($_FILES['file']) && !(0 < $_FILES['file']['error'])) {
                $filename = $_FILES['file']['name'];
                $fileuri = uniqid(time()) . '-' . $filename;
                move_uploaded_file($_FILES['file']['tmp_name'], FG::getPathMaster($fileuri));
                if (is_numeric($id)) {
                    unlink(FG::getPathMaster($template->file));
                }
                $template->file = $fileuri;
            }

            $myTemplate = Template::where('status', 1)->where('id', '<>', @$template->id)->where('version', $version)->first();
            if ($myTemplate) {
                $myTemplate->status = $status == 1 ? 0 : $status;
                $myTemplate->save();
            }
            // Capsule::select('UPDATE templates SET status = 0');

            $template->name = $name;
            $template->status = $status;
            $template->version = $version;
            $template->save();

            $rsp['success'] = true;
            $rsp['data'] = compact('template');
            $rsp['message'] = 'Se guardo correctamente';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function remove($request)
    {
        $rsp = FG::responseDefault();
        try {
            extract($request->getParsedBody());

            if (!$id) {
                throw new \Exception('The id is required');
            }

            $templates = Template::where('deleted_at')->get()->toArray();
            if (count($templates) == 1) {
                throw new \Exception('Must have at least one stored template');
            }

            $template = Template::where('id', $id)->first();

            if ($template->status == 1) {
                throw new \Exception('Can not delete a default template');
            }
            $template->deleted_at = FG::getDateHour();
            unlink(FG::getPathMaster($template->file));
            $template->save();

            $rsp['success'] = true;
            $rsp['message'] = 'Se elimino correctamente';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function downloadMaster($request)
    {
        $filename = urldecode(end(explode("/", $request->getUri()->getpath())));
        $file = FG::getPathMaster($filename);
        if (file_exists($file)) {
            $this->downloadFile($file);
        } else {
            header('Location: /');
            exit();
        }
    }

    public function downloadFile($file)
    {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        ob_clean();
        flush();
        readfile($file);
        exit;
    }
}
