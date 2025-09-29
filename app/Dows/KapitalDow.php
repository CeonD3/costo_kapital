<?php

namespace App\Dows;

use Illuminate\Database\Capsule\Manager as DB;
use App\Utilitarian\{FG, View};
use App\Libraries\KapitalExcel;
use App\Services\OnedriveService;
use App\Dows\OnedriveDow;

class KapitalDow
{

    public function form($request)
    {
        $rsp = FG::responseDefault();
        try {
            $input = $request->getParsedBody();
            $uid = $input['uid'];
            $userId = $input['userId'];
            $brand = false;
            $template = [];

            if ($userId > 0 && !empty($uid)) {
                $template = DB::table('reports AS R')
                    ->where('R.deleted_at')
                    ->where('R.user_id', $userId)
                    ->where('R.code', $uid)
                    ->select('R.*')
                    ->first();
                if (!$template) {
                    throw new \Exception('La plataforma no tiene una plantilla de usuario con el codigo ' . $uid);
                }
                //$filename = FG::fullPathUserTemplate($template->file);
                $brand = true;
            } else if (!empty($uid)) {
                $template = DB::table('reports AS R')
                    ->where('R.deleted_at')
                    ->where('R.code', $uid)
                    ->first();
                if (!$template) {
                    throw new \Exception('La plataforma no tiene una plantilla de invitado con el codigo ' . $uid);
                }
                //$filename = FG::fullPathUserTemplate($template->file);
                $brand = true;
            }

            $templateMaster = DB::table('templates')->where('deleted_at')->where('status', 1)->where('version', 2)->first();
            if (!$templateMaster) {
                throw new \Exception('La plataforma no tiene una plantilla de master');
            }
            $filename = FG::getPathMaster($templateMaster->file);


            $kapitalExcel = new KapitalExcel();
            $form = $kapitalExcel->getFormCloud($filename, $brand, $template);

            $rsp['success'] = true;
            $rsp['data']    = compact('form', 'uid', 'template');
            $rsp['message'] = 'form';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function store($request)
    {
        $rsp = FG::responseDefault();
        try {

            $input  = $request->getParsedBody();
            $userId = $input['userId'];
            $input['typeId'] = (isset($input['typeId']) || $input['typeId'] == 2) ? 2 : 1;

            $template = DB::table('templates')->where('deleted_at')->where('status', 1)->where('version', 2)->first();
            if (!$template) {
                throw new \Exception('The platform does not have a master template');
            }

            $company = DB::table('empresas')->where('deleted_at')->first();

            $fullPath = FG::fullFolderPathUserTemplate();

            if (!is_dir($fullPath)) {
                mkdir($fullPath, 0777, true);
            }

            $fullPathMaster = FG::getPathMaster($template->file);
            $pathinfo = pathinfo($fullPathMaster);
            $uniqid = FG::slugify(uniqid());
            $uid = strtolower($uniqid . FG::randString(5));
            $filename = strtolower('kapital-' . $uid . '.' . $pathinfo['extension']);

            $fullPathFile = $fullPath . '/' . $filename;

            copy($fullPathMaster, $fullPathFile);

            $onedriveDow = new OnedriveDow();
            $onedriveService = new OnedriveService();
            $onedriveService = $onedriveDow->handleToken($company->token_onedrive, $onedriveService);
            $folderName = 'PROFINANCE TEMPLATES';
            $folderParentId = null;
            $folderParent = $onedriveService->queryFile($folderName);
            $value = $folderParent['value'];
            if (count($value) == 0) {
                $folderParent = $onedriveService->createFolder($folderName);
                $folderParentId = $folderParent['id'];
            } else {
                $folderParent = array_shift($value);
                $folderParentId = $folderParent['id'];
            }

            $fileUpload = $onedriveService->uploadFilePath($fullPathMaster, $folderParentId, $filename, false);

            $reportId = DB::table('reports')->insertGetId([
                'file'        => $filename,
                'code'        => $uid,
                'template_id' => $template->id,
                'user_id'     => $userId,
                'type_id'     => $input['typeId'],
                'type_b_a'    => $input['instrument'] == 'Bono EE.UU' ? 1 : 2,
                'eid'         => $fileUpload['id'],
                'version'     => 2,
                'platform_id' => 1,
                'datetime'    => FG::getDateHour()
            ]);

            $report = DB::table('reports AS R')
                ->where('R.id', $reportId)
                ->select('R.*')
                ->first();

            $kapitalExcel = new KapitalExcel();
            $kapitalExcel->setFormCloud($report, $input);

            $rsp['success'] = true;
            $rsp['data'] = compact('uid');
            $rsp['message'] = 'Se registro correctamente';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function update($request)
    {
        $rsp = FG::responseDefault();
        try {

            $input  = $request->getParsedBody();
            $userId = $request->getAttribute('userId');
            $uid    = $request->getAttribute('uid');
            $input['typeId'] = (isset($input['typeId']) || $input['typeId'] == 2) ? 2 : 1;

            $report = DB::table('reports AS R')
                ->where('R.user_id', $userId)
                ->where('R.code', $uid)
                ->select('R.*')
                ->first();

            if (!$report) {
                throw new \Exception('No se encontro la plantilla');
            }

            DB::table('reports')->where('id', $report->id)->update([
                'type_id'  => $input['typeId'],
                'type_b_a' => $input['instrument'] == 'Bono EE.UU' ? 1 : 2,
            ]);

            $kapitalExcel = new KapitalExcel();
            $kapitalExcel->setFormCloud($report, $input);

            $rsp['success'] = true;
            $rsp['data'] = compact('report');
            $rsp['message'] = 'Se guardo correctamente';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function analysis($request)
    {
        $rsp = FG::responseDefault();
        try {

            $userId = $request->getAttribute('userId');
            $uid = $request->getAttribute('uid');
            $template = [];

            if ($userId > 0 && !empty($uid)) {
                $template = DB::table('reports AS R')
                    ->where('R.deleted_at')
                    ->where('R.user_id', $userId)
                    ->where('R.code', $uid)
                    ->select('R.*')
                    ->first();
                if (!$template) {
                    throw new \Exception('La plataforma no tiene una plantilla de usuario con el codigo ' . $uid);
                }
            } else if (!empty($uid)) {
                $template = DB::table('reports AS R')
                    ->where('R.deleted_at')
                    ->where('R.code', $uid)
                    ->first();
                if (!$template) {
                    throw new \Exception('La plataforma no tiene una plantilla de invitado con el codigo ' . $uid);
                }
            } else {
                throw new \Exception('No se encontro la plantilla');
            }

            $rsp['success'] = true;
            $rsp['data']    = compact('uid', 'template');
            $rsp['message'] = 'form';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function detailResult($request)
    {
        $rsp = FG::responseDefault();
        try {
            $userId = $request->getAttribute('userId');
            $uid = $request->getAttribute('uid');

            $template = DB::table('reports AS R')
                ->where('R.user_id', $userId)
                ->where('R.code', $uid)
                ->select('R.*')
                ->first();

            $kapitalExcel = new KapitalExcel();
            $param = $kapitalExcel->getResultCloud($template);

            $rsp['success'] = true;
            $rsp['data']    = compact('param', 'uid', 'template');
            $rsp['message'] = 'form';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function detailAnalysis($request)
    {
        $rsp = FG::responseDefault();
        try {
            $userId = $request->getAttribute('userId');
            $uid = $request->getAttribute('uid');

            $template = DB::table('reports AS R')
                ->where('R.user_id', $userId)
                ->where('R.code', $uid)
                ->select('R.*')
                ->first();

            if (!$template) {
                throw new \Exception('La plantilla no existe');
            }

            if (!$template->aid && $template->eid) {
                $company = DB::table('empresas')->where('deleted_at')->first();
                $filename = strtolower('kapital-analysis-' . $uid) . '.xlsx';
                $onedriveDow = new OnedriveDow();
                $onedriveService = new OnedriveService();
                $onedriveService = $onedriveDow->handleToken($company->token_onedrive, $onedriveService);
                $fileDownload = $onedriveService->content($template->eid);
                if (!$fileDownload) {
                    throw new \Exception('No se pudo descargar la plantilla');
                }
                $fullpath = __DIR__ . '/../../public/template/' . $filename;
                $fullPath = $onedriveService->downloadFileAsPath($fileDownload, $fullpath);
                $fileOrigen = $onedriveService->file($template->eid);
                $fileOrigenParentId = $fileOrigen['parentReference']['id'];
                $fileUpload = $onedriveService->uploadFilePath($fullPath, $fileOrigenParentId, $filename);
                // $fileUpload = $onedriveService->copyFile($template->eid, $filename);
                if (isset($fileUpload['id'])) {
                    DB::table('reports')->where('id', $template->id)->update([
                        'aid'  => $fileUpload['id'],
                    ]);
                    $template = DB::table('reports AS R')
                        ->where('R.user_id', $userId)
                        ->where('R.code', $uid)
                        ->select('R.*')
                        ->first();
                }
            }

            if (!$template->aid) {
                throw new \Exception('No se pudo generar la plantilla');
            }

            $kapitalExcel = new KapitalExcel();
            $param = $kapitalExcel->getAnalysisCloud($template);

            /*$fileAnalysis = "analisis-".$template->file;
            $filename = FG::fullPathUserTemplate($fileAnalysis);

            if (!file_exists($filename)) {
                $templateMaster = DB::table('templates')->where('deleted_at')->where('status', 1)->where('version', 2)->first();
                if (!$templateMaster) {
                    throw new \Exception('The platform does not have a master template');
                }
                $fullPathMaster = FG::getPathMaster($templateMaster->file);
                // $fileUser = FG::fullPathUserTemplate($template->file);

                copy($fullPathMaster, $filename);
            }
            
            $kapitalExcel = new KapitalExcel();
            $param = $kapitalExcel->analysis($filename);*/

            $rsp['success'] = true;
            $rsp['data']    = compact('param', 'uid', 'template');
            $rsp['message'] = 'form';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function costAnalysis($request)
    {
        $rsp = FG::responseDefault();
        try {
            $userId = $request->getAttribute('userId');
            $uid = $request->getAttribute('uid');
            $input  = $request->getParsedBody();

            $template = DB::table('reports AS R')
                ->where('R.user_id', $userId)
                ->where('R.code', $uid)
                ->select('R.*')
                ->first();

            $kapitalExcel = new KapitalExcel();
            $kapitalExcel->setCostAnalysisCloud($template, $input);

            /*$fileAnalysis = "analisis-".$template->file;
            $filename = FG::fullPathUserTemplate($fileAnalysis);

            if (!file_exists($filename)) {
                $templateMaster = DB::table('templates')->where('deleted_at')->where('status', 1)->where('version', 2)->first();
                if (!$templateMaster) {
                    throw new \Exception('The platform does not have a master template');
                }
                $fullPathMaster = FG::getPathMaster($templateMaster->file);
                // $fileUser = FG::fullPathUserTemplate($template->file);
                copy($fullPathMaster, $filename);
            }
            
            $kapitalExcel = new KapitalExcel();
            $param = $kapitalExcel->costAnalysis($filename, $input);*/

            $rsp['success'] = true;
            $rsp['data']    = compact('param', 'uid', 'template');
            $rsp['message'] = 'Se guardo correctamente';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function taxrate($request)
    {
        $rsp = FG::responseDefault();
        try {

            $input  = $request->getParsedBody();
            $userId = $input['userId'];
            $uid    = $input['uid'];

            /*if ($userId > 0 && !empty($uid)) {
                $template = DB::table('reports AS R')
                                ->where('R.deleted_at')
                                ->where('R.user_id', $userId)
                                ->where('R.code', $uid)
                                ->select('R.*')
                                ->first();
                if (!$template) {
                    throw new \Exception('La plataforma no tiene una plantilla de usuario con el codigo ' . $uid);
                }
                $filename = FG::fullPathUserTemplate($template->file);
                $brand = true;
            } else if (!empty($uid)) {
                $template = DB::table('reports AS R')
                                ->where('R.deleted_at')
                                ->where('R.code', $uid)
                                ->first();
                if (!$template) {
                    throw new \Exception('La plataforma no tiene una plantilla de invitado con el codigo ' . $uid);
                }
                $filename = FG::fullPathUserTemplate($template->file);
                $brand = true;
            } else {
                $templateMaster = DB::table('templates')->where('deleted_at')->where('status', 1)->where('version', 2)->first();
                if (!$templateMaster) {
                    throw new \Exception('La plataforma no tiene una plantilla de master');
                }
                $filename = FG::getPathMaster($templateMaster->file);
            }*/
            $templateMaster = DB::table('templates')->where('deleted_at')->where('status', 1)->where('version', 2)->first();
            if (!$templateMaster) {
                throw new \Exception('La plataforma no tiene una plantilla de master');
            }
            $filename = FG::getPathMaster($templateMaster->file);

            $kapitalExcel = new KapitalExcel();
            $param = $kapitalExcel->taxrate($filename, $input);

            $rsp['success'] = true;
            $rsp['data'] = compact('param');
            $rsp['message'] = 'Se guardo correctamente';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function generateReport($request)
    {
        $rsp = FG::responseDefault();
        try {
            $userId = $request->getAttribute('userId');
            $uid = $request->getAttribute('uid');
            $input  = $request->getParsedBody();
            $slug = $input['report'];

            $template = DB::table('reports AS R')
                ->where('R.user_id', $userId)
                ->where('R.code', $uid)
                ->select('R.*')
                ->first();

            $rsp['success'] = true;
            $rsp['data']    = compact('param', 'uid', 'template');
            $rsp['message'] = 'generate';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function indexReport($request)
    {
        $rsp = FG::responseDefault();
        try {
            $userId = $request->getAttribute('userId');
            $uid = $request->getAttribute('uid');
            $id = $request->getAttribute('id');

            $rsp['success'] = true;
            $rsp['data']    = compact('uid', 'id');
            $rsp['message'] = 'form';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function showReport($request)
    {
        $rsp = FG::responseDefault();
        try {
            $userId = $request->getAttribute('userId');
            $uid = $request->getAttribute('uid');
            $id = $request->getAttribute('id');


            $rsp['success'] = true;
            $rsp['data']    = compact('uid', 'id');
            $rsp['message'] = 'form';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function listReport($request)
    {
        $rsp = FG::responseDefault();
        try {
            $userId = $request->getAttribute('userId');
            $uid = $request->getAttribute('uid');

            $template = DB::table('reports')
                ->where('deleted_at')
                ->where('code', $uid)
                ->where('user_id', $userId)
                ->first();

            if (!$template) {
                throw new \Exception('No se encontro la plantilla');
            }

            $designs = DB::table('designs')
                ->where('deleted_at')
                ->where('status', 1)
                ->where('type_id', $template->type_id)
                ->where('type_b_a', $template->type_b_a)
                ->where('platform_id', 1)
                ->select('id', 'name', 'price', 'currency', 'content_id')
                ->get();

            $design_contents = DB::table('design_contents')->where('deleted_at')->get();

            $rsp['success'] = true;
            $rsp['data']    = compact('designs', 'design_contents');
            $rsp['message'] = 'list';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function contentReport($request)
    {
        $rsp = FG::responseDefault();
        try {
            $userId = $request->getAttribute('userId');
            $uid = $request->getAttribute('uid');
            $id = $request->getAttribute('id');

            $report = DB::table('reports AS R')
                ->where('R.user_id', $userId)
                ->where('R.code', $uid)
                ->select('R.*')
                ->first();

            if (!$report) {
                throw new \Exception('No se encontro la plantilla');
            }

            $design = DB::table('designs')->where('deleted_at')->where('id', $id)->first();
            if (!$design) {
                throw new \Exception('No encontramos el reporte desiñado');
            }

            $datestring = FG::getFormatDateString(FG::getDateHour('Y-m-d'));
            $structure = View::render('export/structure.twig', compact('report', 'design', 'datestring'));
            $contents = DB::table('designs_structure_contents AS DSC')
                ->join('designs_structure AS DS', 'DSC.structure_id', '=', 'DS.id')
                ->where('DSC.deleted_at')
                ->where('DS.platform_id', 1)
                ->select('DSC.code', 'DSC.id', 'DSC.name', '.DSC.type', 'DSC.structure_id', 'DSC.ename')
                ->get();

            $rsp['success'] = true;
            $rsp['data']    = compact('uid', 'id', 'design', 'structure', 'contents', 'report');
            $rsp['message'] = 'form';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function graphReport($request)
    {
        $rsp = FG::responseDefault();
        try {
            $userId = $request->getAttribute('userId');
            $uid = $request->getAttribute('uid');
            $id = $request->getAttribute('id');
            $input  = $request->getParsedBody();
            $complements = $input['complements'];
            $complements = $complements ? json_decode($complements) : [];

            $keysImgs = [];
            $keysCodes = [];
            foreach ($complements->images as $k => $o) {
                $keysImgs[$o->ename] = $o;
                $keysCodes[] = $o->code;
            }

            foreach ($complements->tables as $k => $o) {
                $keysCodes[] = $o->code;
            }

            $report = DB::table('reports AS R')
                ->where('R.user_id', $userId)
                ->where('R.code', $uid)
                ->select('R.*')
                ->first();

            if (!$report) {
                throw new \Exception('No se encontro la plantilla');
            }

            $design = DB::table('designs')->where('deleted_at')->where('id', $id)->first();
            if (!$design) {
                throw new \Exception('No encontramos el reporte desiñado');
            }

            $company = DB::table('empresas AS EMP')->where('EMP.deleted_at')->first();
            if (!$company) {
                throw new \Exception('No se encontró la empresa');
            }

            $items = [];
            $images = [];
            $tables = [];
            $texts = [];
            $keys = [];
            $contents = count($keysCodes) ? DB::table('designs_structure_contents')->where('deleted_at')->whereIn('code', $keysCodes)->get() : [];

            $kapitalExcel = new KapitalExcel();
            $onedriveDow = new OnedriveDow();
            $onedriveService = new OnedriveService();
            $onedriveService = $onedriveDow->handleToken($company->token_onedrive, $onedriveService);
            $keystable = array();
            foreach ($contents as $k => $o) {
                if ($o->esheetname) {
                    $keystable[$o->esheetname][$o->ename] = [
                        'type' => $o->type,
                        'name' => $o->ename
                    ];
                }
            }

            foreach ($keystable as $k1 => $o1) {
                if (count($o1)) {
                    foreach ($o1 as $k2 => $o2) {
                        if (count($o2)) {
                            if ($o2['type'] == 1) {
                                $req = $onedriveService->getTableRows($report->eid, $k1, $k2);
                                $rows = $req['value'];
                                $keys = [];
                                foreach ($rows as $key => $row) {
                                    $value = $row['values'][0][0];
                                    if ($value) {
                                        $keys[$value][] = $row['values'][0];
                                    }
                                }
                            }
                        }
                    }
                }
            }

            foreach ($complements->tables as $k => $o) {
                if (isset($keys[$o->code])) {
                    $rrows = $keys[$o->code];
                    $rows = [];
                    foreach ($rrows as $key => $rows2) {
                        unset($rows2[0]);
                        $rows3 = [];
                        foreach ($rows2 as $k2 => $row) {
                            if ($k2 > 1 && empty($row)) {
                                continue;
                            }
                            $row = is_numeric($row) ? FG::numberformat($row) : $row;
                            $rows3[] = $row;
                        }
                        $rows[] = array_values($rows3);
                    }
                    $tables[] = [
                        'cid'   => $o->cid,
                        'rows'  => $rows
                    ];
                }
            }

            if ($company->token_onedrive) {
                $onedriveDow = new OnedriveDow();
                $onedriveService = new OnedriveService();
                $onedriveService = $onedriveDow->handleToken($company->token_onedrive, $onedriveService);
                $charts = $onedriveService->charts($report->eid, 'WACC');
                foreach ($charts['value'] as $k => $chart) {
                    if (isset($keysImgs[$chart['name']])) {
                        $chart['cid'] = $keysImgs[$chart['name']]->cid;
                        $items[] = $chart;
                    }
                }
                foreach ($items as $k => $item) {
                    $result = $onedriveService->imageChart($report->eid, 'WACC', $item['id']);
                    $images[] = [
                        'cid'   => $item['cid'],
                        'name'  => $item['name'],
                        'image' => $result['value']
                    ];
                }
                $status = true;
            }

            $rsp['success'] = true;
            $rsp['data']    = compact('uid', 'id', 'images', 'texts', 'tables');
            $rsp['message'] = 'form';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function projects($request)
    {
        $rsp = FG::responseDefault();
        try {
            $userId = $request->getAttribute('userId');

            $projects = $userId == 0 ? [] : DB::table('reports')
                ->where('deleted_at')
                ->where('user_id', $userId)
                ->where('platform_id', 1)
                ->where('version', 2)
                ->orderBy('id', 'desc')
                ->get();

            $rsp['success'] = true;
            $rsp['data']    = compact('projects');
            $rsp['message'] = 'list';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }
}
