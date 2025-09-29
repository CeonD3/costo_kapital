<?php 
namespace App\Model;

use App\Utilitarian\{Crypt, FG, MailerFunction, View};
use App\Model\{Report};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as Capsule;

class Design extends Model {

	protected $table = 'designs';

    const KAPITAL = 1;
	const VALORA  = 2;

    public function list($request) {
		$rsp = FG::responseDefault();
        try {
            $designs = Design::where('deleted_at')->where('platform_id', Design::KAPITAL)->get();
            $rsp['success'] = true;
            $rsp['data'] = compact('designs');
            $rsp['message'] = 'List';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function listValora($request) {
		$rsp = FG::responseDefault();
        try {
            $designs = Design::where('deleted_at')->where('platform_id', Design::VALORA)->get();
            $rsp['success'] = true;
            $rsp['data'] = compact('designs');
            $rsp['message'] = 'List';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function edit($request) {
		$rsp = FG::responseDefault();
        try {
            $id = $request->getAttribute('id');
            if (!is_numeric($id)) {
                header('Location: /admin');
            }
            $design = Design::where('deleted_at')->where('id', $id)->first();

            if (!$design) {
                header('Location: /admin');
            }
            $contents = Design::getContentItemsReport();
            $structure = Design::getStructureTemplate($design->platform_id);

            $rsp['success'] = true;
            $rsp['data'] = compact('design', 'structure', 'contents');
            $rsp['message'] = 'List';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function create($request) {
		$rsp = FG::responseDefault();
        try {
            $design = new Design();
            $structure = Design::getStructureTemplate(Design::KAPITAL);
            $contents = Design::getContentItemsReport();
            $rsp['success'] = true;
            $rsp['data'] = compact('design', 'structure', 'contents');
            $rsp['message'] = 'List';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function createValora($request) {
		$rsp = FG::responseDefault();
        try {
            $design = new Design();
            $structure = Design::getStructureTemplate(Design::VALORA);
            $rsp['success'] = true;
            $rsp['data'] = compact('design', 'structure');
            $rsp['message'] = 'List';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function saveData($request) {
		$rsp = FG::responseDefault();
        try {
            $args = $request->getParsedBody();
            extract($args);

            $design = !is_numeric($id) ? new Design() : Design::where('deleted_at')->where('id', $id)->first();
            $design->name = $name;
            $design->body = $body;
            $design->header = $header;
            $design->footer = $footer;
            $design->status = (int)$status;
            $design->type_id = $type_id;
            $design->type_b_a = $type_b_a;
            $design->price = $price;
            $design->link_payment = $link_payment;
            $design->currency = $currency;
            $design->platform_id = $platform_id;
            $design->content_id = $content_id;            

            $upload = $request->getUploadedFiles();
            $baseurl = "upload/portada/";
            $fullpath = __DIR__."/../../public/".$baseurl;
            if (!is_dir($fullpath)) {
                mkdir($fullpath, 0777,true);
            }

            if (isset($upload['image'])) {
                $file = $upload['image'];
                if ($file->getError() == UPLOAD_ERR_OK) {
                    $uniqid = uniqid(time());
                    $fileName = strtolower($file->getClientFilename());
                    $path = $baseurl."$uniqid-$fileName";
                    $file->moveTo($path);
                    unlink(__DIR__."/../../public/".$design->image);
                    $design->image = "/$path";
                }
            }
            $design->save();
            if (Design::VALORA == $design->platform_id) {
                $reload = "/admin/valora/reportes/". $design->id ."/editar";
            } else {
                $reload = "/admin/kapital/reportes/". $design->id ."/editar";
            }

            $rsp['success'] = true;
            $rsp['data'] = compact('design', 'reload');
            $rsp['message'] = 'Se guardo correctamente';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function removeData($request) {
		$rsp = FG::responseDefault();
        try {
            $args = $request->getParsedBody();
            extract($args);

            if (!$id) {
                throw new \Exception('The id is required');
            }

            $design = Design::where('deleted_at')->where('id', $id)->first();
            $design->deleted_at = FG::getDateHour();
            $design->save();

            $rsp['success'] = true;
            $rsp['message'] = 'Se elimino correctamente';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public static function getStructureTemplate($platform_id = 1) {
        $items = Capsule::table('designs_structure')->where('deleted_at')->where('platform_id', $platform_id)->orderBy('order', 'ASC')->get();
        $item_ids = array();
        foreach ($items as $k => $o) {
            array_push($item_ids, $o->id);
        }
        $contents = Capsule::table('designs_structure_contents')->whereIn('structure_id', $item_ids)->get();
        foreach ($items as $k => $o) {
            $detail = [];
            foreach ($contents as $kk => $oo) {
                if ($oo->structure_id == $o->id) {
                    array_push($detail, $oo);
                }
            }
            $items[$k]->contents = $detail;
        }
        return $items;
    }

    public static function getAllActiveDesigns() {
        return Design::where('deleted_at')->where('status', 1)->get();
    }

    public static function getContentItemsReport() {
        /*return [
            ['id' => 1, 'name' => 'Costo de capital del sector'],
            ['id' => 2, 'name' => 'Costo de capital de la empresa'],
            ['id' => 3, 'name' => 'Metodología explicada']
        ];*/
        return Capsule::table('design_contents')->where('deleted_at')->where('edit', 0)->get();
    }
    
}