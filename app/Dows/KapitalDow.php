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
            $form = $kapitalExcel->getFormCloudOptimized($filename, $brand, false, $template);

            $rsp['success'] = true;
            $rsp['data']    = compact('form', 'uid', 'template');
            $rsp['message'] = 'form';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function getFormData($request)
    {
        $startTime = microtime(true);
        $rsp = FG::responseDefault();
        try {
            error_log("=== getFormData CALLED ===");

            // ✅ OBTENER UID DESDE ATRIBUTOS DE RUTA (la ruta es GET /users/{userId}/templates/{uid}/form-data)
            $uid = $request->getAttribute('uid');
            $userId = $request->getAttribute('userId');

            error_log("getFormData resolved UID: " . var_export($uid, true));
            error_log("getFormData resolved UserID: " . var_export($userId, true));

            if (!$uid) {
                throw new \Exception('UID parameter is required');
            }

            $template = DB::table('reports AS R')
                ->where('R.deleted_at')
                ->where('R.code', $uid)
                ->select('R.*')
                ->first();

            if (!$template) {
                throw new \Exception('Template not found');
            }

            // ✅ USAR ARCHIVO LOCAL - No necesita eid de OneDrive
            if (!$template->file) {
                throw new \Exception('No file associated with this template');
            }

            // ✅ CONSTRUIR RUTA LOCAL DEL ARCHIVO
            $fullPath = FG::fullFolderPathUserTemplate();
            $localFilePath = $fullPath . '/' . $template->file;

            if (!file_exists($localFilePath)) {
                throw new \Exception('Local template file not found: ' . $localFilePath);
            }

            $kapitalExcel = new KapitalExcel();

            // ✅ USAR MÉTODO LOCAL DIRECTO PARA LEER DATOS DEL FORMULARIO
            // Leer directamente desde el archivo local con los parámetros correctos
            $formData = $kapitalExcel->getFormDataFromLocalFile($localFilePath, $template);

            $rsp['success'] = true;
            $rsp['data'] = $formData;
            $rsp['message'] = 'Form data retrieved successfully';

            error_log("=== getFormData SUCCESS ===");
        } catch (\Exception $e) {
            error_log("=== getFormData ERROR: " . $e->getMessage() . " ===");
            $rsp['message'] = $e->getMessage();
        } finally {
            $duration = microtime(true) - $startTime;
            error_log("KapitalDow::getFormData took " . number_format($duration, 4) . "s");
        }
        return $rsp;
    }

    public function store($request)
    {
        $startTimeAll = microtime(true); // Inicio medición total
        $rsp = FG::responseDefault();
        try {
            $phase = [];
            $t = microtime(true);

            $input  = $request->getParsedBody();
            $userId = $input['userId'];
            $input['typeId'] = (isset($input['typeId']) && $input['typeId'] == 2) ? 2 : 1;
            $phase['parse_input'] = microtime(true) - $t;
            $t = microtime(true);

            // Procesar datos financieros optimizados si están disponibles
            if (isset($input['useFinancialData']) && $input['useFinancialData'] == '1') {
                // D/C Ratio (Excel C19) - mantener como decimal
                if (isset($input['dc_ratio']) && is_numeric($input['dc_ratio'])) {
                    $input['dc_ratio_optimized'] = floatval($input['dc_ratio']);
                }

                // Tasa Efectiva de Impuesto (Excel C20) - convertir % a decimal
                if (isset($input['effective_tax_rate']) && is_numeric($input['effective_tax_rate'])) {
                    $input['effective_tax_rate_optimized'] = floatval($input['effective_tax_rate']) / 100;
                }

                // Beta Apalancado (Excel C21)
                if (isset($input['beta_levered']) && is_numeric($input['beta_levered'])) {
                    $input['beta_levered_optimized'] = floatval($input['beta_levered']);
                }

                // Beta Desapalancado - para cálculos internos
                if (isset($input['beta_unlevered']) && is_numeric($input['beta_unlevered'])) {
                    $input['beta_unlevered_optimized'] = floatval($input['beta_unlevered']);
                }
            }

            $template = DB::table('templates')->where('deleted_at')->where('status', 1)->where('version', 2)->first();
            if (!$template) {
                throw new \Exception('The platform does not have a master template');
            }
            $phase['load_master_template'] = microtime(true) - $t;
            $t = microtime(true);

            $company = DB::table('empresas')->where('deleted_at')->first();
            $phase['load_company'] = microtime(true) - $t;
            $t = microtime(true);

            $fullPath = FG::fullFolderPathUserTemplate();

            if (!is_dir($fullPath)) {
                mkdir($fullPath, 0777, true);
            }
            $phase['prepare_user_folder'] = microtime(true) - $t;
            $t = microtime(true);

            $fullPathMaster = FG::getPathMaster($template->file);
            $phase['get_master_path'] = microtime(true) - $t;
            $t = microtime(true);

            // ✅ MEDICIÓN DE TIEMPO PARA OPTIMIZACIÓN (tiempo de decisión WACC)
            $startDecisionTime = microtime(true);

            // Intentar usar archivo WACC optimizado si está disponible y es apropiado
            $kapitalExcel = new KapitalExcel();
            $useWaccFile = $kapitalExcel->canUseWaccFile($fullPathMaster, 'copy');
            $phase['decision_wacc'] = microtime(true) - $startDecisionTime;
            $t = microtime(true);

            // ✅ FORZAR USO DE ARCHIVO WACC OPTIMIZADO
            // $sourceFile = $kapitalExcel->getOptimizedFilePath($fullPathMaster);
            $sourceFile = $fullPathMaster;

            $pathinfo = pathinfo($sourceFile);
            $uniqid = FG::slugify(uniqid());
            $uid = strtolower($uniqid . FG::randString(5));
            $filename = strtolower('kapital-' . $uid . '.' . $pathinfo['extension']);

            $fullPathFile = $fullPath . '/' . $filename;

            copy($sourceFile, $fullPathFile);
            $phase['copy_master'] = microtime(true) - $t;
            $t = microtime(true);

            // ✅ MANEJO LOCAL - SIN ONEDRIVE
            // Crear un ID único local para reemplazar el eid de OneDrive
            $localFileId = 'local_' . $uid . '_' . time();

            error_log("Creating local report with file: " . $fullPathFile);
            error_log("Local file ID: " . $localFileId);

            $reportId = DB::table('reports')->insertGetId([
                'file'        => $filename,
                'code'        => $uid,
                'template_id' => $template->id,
                'user_id'     => $userId,
                'type_id'     => $input['typeId'],
                'type_b_a'    => $input['instrument'] == 'Bono EE.UU' ? 1 : 2,
                'eid'         => $localFileId, // Usar ID local en lugar de OneDrive
                'version'     => 2,
                'platform_id' => 1,
                'datetime'    => FG::getDateHour()
            ]);
            $phase['insert_report'] = microtime(true) - $t;
            $t = microtime(true);

            $report = DB::table('reports AS R')
                ->where('R.id', $reportId)
                ->select('R.*')
                ->first();
            $phase['load_report'] = microtime(true) - $t;
            $t = microtime(true);

            $kapitalExcel = new KapitalExcel();
            // ✅ USAR ARCHIVO LOCAL - Pasar la ruta local del archivo
            $subStart = microtime(true);
            $kapitalExcel->setFormCloudLocal($input, $report, $fullPathFile);
            $phase['apply_form'] = microtime(true) - $subStart;
            $t = microtime(true);

            $rsp['success'] = true;
            $rsp['data'] = compact('uid');
            $rsp['message'] = 'Se registro correctamente';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        } finally {
            $total = microtime(true) - $startTimeAll;
            // Log detallado de fases
            $ordered = [
                'parse_input',
                'load_master_template',
                'load_company',
                'prepare_user_folder',
                'get_master_path',
                'decision_wacc',
                'copy_master',
                'insert_report',
                'load_report',
                'apply_form'
            ];
            $msgParts = [];
            foreach ($ordered as $key) {
                if (isset($phase[$key])) {
                    $msgParts[] = $key . '=' . number_format($phase[$key], 4) . 's';
                }
            }
            error_log('KapitalDow::store total=' . number_format($total, 4) . 's phases{' . implode(', ', $msgParts) . '}');
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
            $input['typeId'] = (isset($input['typeId']) && $input['typeId'] == 2) ? 2 : 1;

            // Procesar datos financieros optimizados si están disponibles para actualización
            if (isset($input['useFinancialData']) && $input['useFinancialData'] == '1') {
                // D/C Ratio (Excel C19) - mantener como decimal
                if (isset($input['dc_ratio']) && is_numeric($input['dc_ratio'])) {
                    $input['dc_ratio_optimized'] = floatval($input['dc_ratio']);
                }

                // Tasa Efectiva de Impuesto (Excel C20) - convertir % a decimal
                if (isset($input['effective_tax_rate']) && is_numeric($input['effective_tax_rate'])) {
                    $input['effective_tax_rate_optimized'] = floatval($input['effective_tax_rate']) / 100;
                }

                // Beta Apalancado (Excel C21)
                if (isset($input['beta_levered']) && is_numeric($input['beta_levered'])) {
                    $input['beta_levered_optimized'] = floatval($input['beta_levered']);
                }

                // Beta Desapalancado - para cálculos internos
                if (isset($input['beta_unlevered']) && is_numeric($input['beta_unlevered'])) {
                    $input['beta_unlevered_optimized'] = floatval($input['beta_unlevered']);
                }
            }

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

            // ✅ USAR ARCHIVO LOCAL - Construir la ruta local del archivo usando el nombre
            $fullPath = FG::fullFolderPathUserTemplate();
            $localFilePath = $fullPath . '/' . $report->file;

            if ($localFilePath && file_exists($localFilePath)) {
                $kapitalExcel->setFormCloudLocal($input, $report, $localFilePath);
            } else {
                throw new \Exception('No se encontró el archivo local de la plantilla: ' . $localFilePath);
            }

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
        $startTime = microtime(true);
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
            // ✅ USAR RESULTADOS LOCALES
            $param = $kapitalExcel->getResultLocal($template);

            $rsp['success'] = true;
            $rsp['data']    = compact('param', 'uid', 'template');
            $rsp['message'] = 'form';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        } finally {
            $duration = microtime(true) - $startTime;
            error_log("KapitalDow::detailResult took " . number_format($duration, 4) . "s");
        }
        return $rsp;
    }

    public function detailAnalysis($request)
    {
        $startTime = microtime(true);
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
                $filename = strtolower('kapital-analysis-' . $uid) . '.xlsx';
                $fullpath = __DIR__ . '/../../public/template/' . $filename;

                // ✅ CREAR ARCHIVO DE ANÁLISIS LOCAL
                $fullPathUserTemplate = FG::fullFolderPathUserTemplate();
                $localFilePath = $fullPathUserTemplate . '/' . $template->file;

                if ($localFilePath && file_exists($localFilePath)) {
                    // Copiar el archivo local del reporte para crear el análisis
                    if (copy($localFilePath, $fullpath)) {
                        error_log("Created analysis file from local report: " . $localFilePath);

                        // Crear un ID local para el análisis
                        $localAnalysisId = 'local_analysis_' . $uid . '_' . time();

                        DB::table('reports')->where('id', $template->id)->update([
                            'aid' => $localAnalysisId
                        ]);

                        $template = DB::table('reports AS R')
                            ->where('R.user_id', $userId)
                            ->where('R.code', $uid)
                            ->select('R.*')
                            ->first();
                    } else {
                        throw new \Exception('No se pudo crear el archivo de análisis local');
                    }
                } else {
                    throw new \Exception('No se encontró el archivo local del reporte: ' . $localFilePath);
                }
            }
            if (!$template->aid) {
                throw new \Exception('No se pudo generar la plantilla');
            }

            $kapitalExcel = new KapitalExcel();
            // ✅ USAR ANÁLISIS LOCAL
            $param = $kapitalExcel->getAnalysisLocal($template);

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
        } finally {
            $duration = microtime(true) - $startTime;
            error_log("KapitalDow::detailAnalysis took " . number_format($duration, 4) . "s");
        }
        return $rsp;
    }

    public function costAnalysis($request)
    {
        $startTime = microtime(true);
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
            // ✅ USAR ANÁLISIS DE COSTOS LOCAL
            $kapitalExcel->setCostAnalysisLocal($template, $input);

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
        } finally {
            $duration = microtime(true) - $startTime;
            error_log("KapitalDow::costAnalysis took " . number_format($duration, 4) . "s");
        }
        return $rsp;
    }

    public function taxrate($request)
    {
        $startTime = microtime(true);
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
        } finally {
            $duration = microtime(true) - $startTime;
            error_log("KapitalDow::taxrate took " . number_format($duration, 4) . "s");
        }
        return $rsp;
    }

    public function generateReport($request)
    {
        $startTime = microtime(true);
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
        } finally {
            $duration = microtime(true) - $startTime;
            error_log("KapitalDow::generateReport took " . number_format($duration, 4) . "s");
        }
        return $rsp;
    }

    public function indexReport($request)
    {
        $startTime = microtime(true);
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
        } finally {
            $duration = microtime(true) - $startTime;
            error_log("KapitalDow::indexReport took " . number_format($duration, 4) . "s");
        }
        return $rsp;
    }

    public function showReport($request)
    {
        $startTime = microtime(true);
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
        } finally {
            $duration = microtime(true) - $startTime;
            error_log("KapitalDow::showReport took " . number_format($duration, 4) . "s");
        }
        return $rsp;
    }

    public function listReport($request)
    {
        $startTime = microtime(true);
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
        } finally {
            $duration = microtime(true) - $startTime;
            error_log("KapitalDow::listReport took " . number_format($duration, 4) . "s");
        }
        return $rsp;
    }

    public function contentReport($request)
    {
        $startTime = microtime(true);
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
        } finally {
            $duration = microtime(true) - $startTime;
            error_log("KapitalDow::contentReport took " . number_format($duration, 4) . "s");
        }
        return $rsp;
    }

    public function graphReport($request)
    {
        $startTime = microtime(true);
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
        } finally {
            $duration = microtime(true) - $startTime;
            error_log("KapitalDow::graphReport took " . number_format($duration, 4) . "s");
        }
        return $rsp;
    }

    public function projects($request)
    {
        $startTime = microtime(true);
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
        } finally {
            $duration = microtime(true) - $startTime;
            error_log("KapitalDow::projects took " . number_format($duration, 4) . "s");
        }
        return $rsp;
    }
}
