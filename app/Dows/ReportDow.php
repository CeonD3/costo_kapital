<?php 

namespace App\Dows;

ini_set('memory_limit', '-1');

use Illuminate\Database\Capsule\Manager as DB;
use App\Utilitarian\FG;
use App\Libraries\FinanceExcel;
use Firebase\JWT\JWT;

class ReportDow {

    public function view($request) {
		$rsp = FG::responseDefault();
        try {

            $key = $request->getAttribute('key');
            $decoded = JWT::decode($key, $_ENV['TOKEN_KEY'], array('HS256'));

            $uid = $decoded->uid;
            $userId  = $decoded->userId;
            $id  = $decoded->id;

            $template = DB::table('reports')
                            ->where('deleted_at')
                            ->where('code', $uid)
                            ->where('user_id', $userId)
                            ->first();

            if (!$template) {
                throw new \Exception('No se encontro la plantilla');
            }
            
            $design = DB::table('designs')
                        ->where('deleted_at')
                        ->where('status', 1)
                        ->where('id', $id)
                        ->first();

            if (!$design) {
                throw new \Exception('No se encontro el diseño de la plantilla');
            }
            $rsp['success'] = true;
            $rsp['data']    = compact('uid', 'id', 'userId');
            $rsp['message'] = 'view';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
	}

}