<?php 

namespace App\Dows;

use Illuminate\Database\Capsule\Manager as DB;
use App\Utilitarian\FG;

class ConfigurationDow {

    public function show($request) {
		$rsp = FG::responseDefault();
        try {

            $design_contents = DB::table('design_contents')->where('deleted_at')->where('edit', 1)->get();
            
            $rsp['success'] = true;
            $rsp['data']    = compact('design_contents');
            $rsp['message'] = 'show';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
	}

    public function update($request) {
		$rsp = FG::responseDefault();
        try {
            $input = $request->getParsedBody();
            $any   = $input['any'];
            switch ($any) {
                case 'design_contents':
                    $id = $input['id'];
                    $name = $input['name'];
                    DB::table('design_contents')
                        ->where('deleted_at')
                        ->where('id', $id)
                        ->update([
                            'name' => $name
                        ]);
                break;
                
                default:
                    # code...
                break;
            }

            $rsp['success'] = true;
            $rsp['message'] = 'Se guardo correctamente';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
	}

}