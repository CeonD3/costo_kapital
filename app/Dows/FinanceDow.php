<?php 

namespace App\Dows;

use Illuminate\Database\Capsule\Manager as DB;
use App\Utilitarian\FG;
use App\Libraries\FinanceExcel;

class FinanceDow {

    public function industries($request) {
		$rsp = FG::responseDefault();
        try {

            $template = DB::table('templates')->where('deleted_at')->where('status', 1)->where('version', 2)->first();
            if (!$template) {
                throw new \Exception('La plataforma no tiene una plantilla de master');
            }
            $filename = FG::getPathMaster($template->file);

            $financeExcel = new FinanceExcel();
            $param = $financeExcel->industries($filename);

            $rsp['success'] = true;
            $rsp['data']    = $param;
            $rsp['message'] = 'industries';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
	}

    public function removeProject($request) {
		$rsp = FG::responseDefault();
        try {

            $id = $request->getAttribute('id');

            DB::table('reports')->where('id', $id)->update(['deleted_at' => FG::getDateHour()]);

            $rsp['success'] = true;
            $rsp['message'] = 'Se elimino correctamente';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
	}

}