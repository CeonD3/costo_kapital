<?php

namespace App\Dows;

use Illuminate\Database\Capsule\Manager as DB;
use App\Utilitarian\FG;
use App\Utilitarian\View;
use App\Libraries\ValoraExcel;
use App\Services\OnedriveService;
use App\Dows\OnedriveDow;

class ValoraDow
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
                // $filename = FG::fullPathUserTemplate($template->file);
                $brand = true;
            } else if (!empty($uid)) {
                $template = DB::table('reports AS R')
                    ->where('R.deleted_at')
                    ->where('R.code', $uid)
                    ->first();
                if (!$template) {
                    throw new \Exception('La plataforma no tiene una plantilla de invitado con el codigo ' . $uid);
                }
                // $filename = FG::fullPathUserTemplate($template->file);
                $brand = true;
            }

            $templateMaster = DB::table('templates')->where('deleted_at')->where('status', 1)->where('version', 2)->first();
            if (!$templateMaster) {
                throw new \Exception('La plataforma no tiene una plantilla de master');
            }
            $filename = FG::getPathMaster($templateMaster->file);
            $valoraExcel = new ValoraExcel();
            $form = $valoraExcel->getFormCloud($filename, $brand, $template);

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

            $input = $request->getParsedBody();
            $userId = $input['userId'];
            $fileUsername = $input['fileUsername'];

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
            $filename = strtolower('valora' . '-' . $uid . '.' . $pathinfo['extension']);

            $fullPathFile = $fullPath . '/' . $filename;

            // copy($fullPathMaster, $fullPathFile);

            $onedriveDow = new OnedriveDow();
            $onedriveService = new OnedriveService();
            $onedriveService = $onedriveDow->handleToken($company->token_onedrive, $onedriveService);
            $folderName = 'PROFINANCE TEMPLATES';
            $folderParentId = null;
            $folderParent = $onedriveService->queryFile($folderName);
            $value = $folderParent['value'];
            FG::debug($value);
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
                'type_id'     => 1,
                'type_b_a'    => 1,
                'file_username' => $fileUsername ?? null,
                'eid'         => $fileUpload['id'],
                'version'     => 2,
                'platform_id' => 2,
                'datetime'    => FG::getDateHour()
            ]);

            $report = DB::table('reports AS R')
                ->where('R.id', $reportId)
                ->select('R.*')
                ->first();

            $valoraExcel = new ValoraExcel();
            $valoraExcel->setFormCloud($report, $input);

            if ($fileUsername) {
                $fileUsernamePath = FG::fullPathUploadTemplateUser($fileUsername);
                $result = $valoraExcel->setFormUsernameCloud($report, $fileUsernamePath);
                DB::table('reports')->where('id', $reportId)->update(['file_username_data' => json_encode($result)]);
            }

            $rsp['success'] = true;
            $rsp['data'] = compact('uid');
            $rsp['message'] = 'Se registro correctamente';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function upload($request)
    {
        $rsp = FG::responseDefault();
        try {

            $input = $request->getParsedBody();
            $upload = $request->getUploadedFiles();
            $uid = $input['uid'];
            $file = $upload['file'];

            $baseurl = '/template/users/';
            $fullpath = __DIR__ . '/../../public' . $baseurl;
            if (!is_dir($fullpath)) {
                mkdir($fullpath, 0777, true);
            }

            if ($file->getError() != UPLOAD_ERR_OK) {
                throw new \Exception('No se encontro el archivo');
            }

            $extension = strtolower(pathinfo($file->getClientFilename(), PATHINFO_EXTENSION));
            $basename = time() . bin2hex(random_bytes(8));
            $filename = sprintf('%s.%0.8s', $basename, $extension);
            $path = $fullpath . $filename;
            $file->moveTo($path);
            $fileurl = $baseurl . $filename;
            $name = $file->getClientFilename();

            if ($uid) {
                $template = DB::table('reports')->where('deleted_at')->where('code', $uid)->first();
                if ($template) {
                    /*DB::table('reports')->where('id', $template->id)->update(['file_username' => $filename]); 
                    $filenameTemplate = FG::fullPathUserTemplate($template->file);
                    if ($filenameTemplate) {
                        $fileUsername = FG::fullPathUploadTemplateUser($filename);
                        $valoraExcel = new ValoraExcel();
                        $valoraExcel->setFormUsername($filenameTemplate, $fileUsername);
                    }*/
                    $valoraExcel = new ValoraExcel();
                    $fileUsernamePath = FG::fullPathUploadTemplateUser($template->file_username);
                    $result = $valoraExcel->setFormUsernameCloud($template, $fileUsernamePath);
                    DB::table('reports')->where('id', $template->id)->update(['file_username_data' => json_encode($result)]);
                }
            }

            $rsp['success'] = true;
            $rsp['data'] = compact('fileurl', 'filename', 'name');
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

            $report = DB::table('reports AS R')
                ->where('R.user_id', $userId)
                ->where('R.code', $uid)
                ->select('R.*')
                ->first();

            if (!$report) {
                throw new \Exception('No se encontro la plantilla');
            }

            // $filename = FG::fullPathUserTemplate($report->file);

            $valoraExcel = new ValoraExcel();
            // $valoraExcel->setForm($filename, $input);
            $valoraExcel->setFormCloud($report, $input);

            $rsp['success'] = true;
            $rsp['data'] = compact('report');
            $rsp['message'] = 'Se guardo correctamente';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function balance($request)
    {
        $rsp = FG::responseDefault();
        try {

            $input  = $request->getParsedBody();
            $userId = $request->getAttribute('userId');
            $uid    = $request->getAttribute('uid');

            $template = DB::table('reports AS R')
                ->where('R.user_id', $userId)
                ->where('R.code', $uid)
                ->select('R.*')
                ->first();

            // $filename = FG::fullPathUserTemplate($template->file);

            $valoraExcel = new ValoraExcel();
            $balance = $valoraExcel->balanceData($template);

            $rsp['success'] = true;
            $rsp['data'] = compact('balance');
            $rsp['message'] = 'Se guardo correctamente';
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

            // $filename = FG::fullPathUserTemplate($template->file);
            $valoraExcel = new ValoraExcel();
            $param = $valoraExcel->getResultCloud($template);
            // $param = $valoraExcel->result($filename);

            $rsp['success'] = true;
            $rsp['data']    = compact('param', 'uid', 'template');
            $rsp['message'] = 'form';
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

            // $filename = FG::fullPathUserTemplate($template->file);
            $valoraExcel = new ValoraExcel();
            $param = $valoraExcel->getAnalysisCloud($template);
            // $param = $valoraExcel->result($filename);

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

            $input  = $request->getParsedBody();
            $userId = $request->getAttribute('userId');
            $uid    = $request->getAttribute('uid');

            $report = DB::table('reports AS R')
                ->where('R.user_id', $userId)
                ->where('R.code', $uid)
                ->select('R.*')
                ->first();

            if (!$report) {
                throw new \Exception('No se encontro la plantilla');
            }

            $valoraExcel = new ValoraExcel();
            $valoraExcel->setCostAnalysisCloud($report, $input);

            $rsp['success'] = true;
            $rsp['data'] = compact('report');
            $rsp['message'] = 'Se guardo correctamente';
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

            $projects = $userId == 0 ? [] : DB::table('reports AS R')
                ->where('deleted_at')
                ->where('R.user_id', $userId)
                ->where('platform_id', 2)
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
                ->where('platform_id', 2)
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
            $structure = View::render('export/structureValora.twig', compact('report', 'design', 'datestring'));
            $contents = DB::table('designs_structure_contents AS DSC')
                ->join('designs_structure AS DS', 'DSC.structure_id', '=', 'DS.id')
                ->where('DSC.deleted_at')
                ->where('DS.platform_id', 2)
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
            $date = null;

            $contents = count($keysCodes) ? DB::table('designs_structure_contents')->where('deleted_at')->whereIn('code', $keysCodes)->get() : [];

            $valoraExcel = new ValoraExcel();
            $keys = $valoraExcel->getReportCloud($report);

            $texts = [];
            foreach ($complements->texts as $k => $o) {
                if (isset($keys[$o->code])) {
                    $value = $keys[$o->code][0][2];
                    if ($o->name == 'Fecha') {
                        $dateObject = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value);
                        $dateObject = json_decode(json_encode($dateObject));
                        $value = FG::getDateFormat($dateObject->date, 'd/m/Y');
                        $date = $value;
                    } else if ($o->name == 'Moneda') {
                    } else {
                        $value = FG::numberformat($value);
                    }
                    $texts[] = [
                        'cid'   => $o->cid,
                        'name'  => $o->name,
                        'value' => $value
                    ];
                }
            }
            $tables = [];
            foreach ($complements->tables as $k => $o) {
                if (isset($keys[$o->code])) {
                    $rrows = $keys[$o->code];
                    $rows = [];
                    foreach ($rrows as $key => $row) {
                        unset($row[0]);

                        $row[2] = is_numeric($row[2]) ? FG::numberformat($row[2]) : $row[2];
                        $row[3] = is_numeric($row[3]) ? FG::numberformat($row[3]) : $row[3];
                        $rows[] = array_values($row);
                    }
                    $tables[] = [
                        'cid'   => $o->cid,
                        'rows'  => $rows
                    ];
                }
            }

            if ($company->token_onedrive) {
                $sheetnames = [];
                foreach ($contents as $k => $c) {
                    if ($c->esheetname) {
                        $sheetnames[$c->esheetname] = $c->esheetname;
                    }
                }
                $sheetnames = array_values($sheetnames);

                $onedriveDow = new OnedriveDow();
                $onedriveService = new OnedriveService();
                $onedriveService = $onedriveDow->handleToken($company->token_onedrive, $onedriveService);
                foreach ($sheetnames as $k => $o) {
                    $charts = $onedriveService->charts($report->eid, $o);
                    foreach ($charts['value'] as $k => $chart) {
                        if (isset($keysImgs[$chart['name']])) {
                            $chart['cid'] = $keysImgs[$chart['name']]->cid;
                            $items[] = $chart;
                        }
                    }
                    foreach ($items as $k => $item) {
                        $result = $onedriveService->imageChart($report->eid, $o, $item['id']);
                        $images[] = [
                            'cid'   => $item['cid'],
                            'name'  => $item['name'],
                            'image' => $result['value']
                        ];
                    }
                }
                $status = true;
            }

            $rsp['success'] = true;
            $rsp['data']    = compact('uid', 'id', 'images', 'texts', 'tables', 'date');
            $rsp['message'] = 'form';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function bvl($request)
    {
        $rsp = FG::responseDefault();
        try {

            $valoraExcel = new ValoraExcel();

            $templateMaster = DB::table('templates')->where('deleted_at')->where('status', 1)->where('version', 2)->first();
            if (!$templateMaster) {
                throw new \Exception('La plataforma no tiene una plantilla de master');
            }
            $filename = FG::getPathMaster($templateMaster->file);

            $result = $valoraExcel->BVL($filename);

            $rsp['success'] = true;
            $rsp['data']    = $result;
            $rsp['message'] = 'bvl';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }
}
