<?php

namespace App\Model;

use App\Utilitarian\{Crypt, FG, View};
use App\Middle\{SystemMiddle};
use App\Model\{SpreadsheetManage, User, Report, Landing, Template, Design, Transfer, BitacoraVenta};
use Illuminate\Database\Capsule\Manager as Capsule;

define('EMPLOYEE', 'employee');

class System
{

    const BONUS_EEUU = 'Bono EE.UU';
    const BONUS_EMPLOYEE = 'Ajustar Rf según la duración del proyecto';

    use SystemMiddle;

    public function initRecord($request)
    {
        $rsp = FG::responseDefault();
        try {
            $user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
            if (!$user) {
                header('Location: /');
                // throw new \Exception('The user must be logged into the platform');
            }

            $landing = Landing::where('deleted_at')->first();
            $reports = Report::where('user_id', $user->id)->where('deleted_at')->orderBy('id', 'desc')->get();
            $template = Template::where('deleted_at')->where('status', 1)->first();

            if (!$template) {
                throw new \Exception('The template master no found');
            }

            $rsp['success'] = true;
            $rsp['data'] = compact('reports', 'template', 'landing');
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function initCalculate($request)
    {
        $rsp = FG::responseDefault();
        try {
            $code = $request->getAttribute('code');

            $filename = $this->verifyReportFile($request);

            $spreadsheetManage = new SpreadsheetManage();
            $result = $spreadsheetManage->initCalculate(compact('filename'));
            if (!$result['success']) {
                throw new \Exception($result['message']);
            }
            $system = $result['data'];

            $landing = Landing::where('deleted_at')->first();

            $rsp['success'] = true;
            $rsp['data'] = compact('code', 'system', 'landing');
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function filterRiskLevel($request)
    {
        $rsp = FG::responseDefault();
        try {
            $args = $request->getParsedBody();
            extract($args);
            $filename = $this->verifyReportFile($request);
            $spreadsheetManage = new SpreadsheetManage();
            if (@$order == 2) {
                $result = $spreadsheetManage->getRiskLevel(compact('filename'));
            } else {
                $result = $spreadsheetManage->initCalculate(compact('filename'));
            }
            if (!$result['success']) {
                throw new \Exception($result['message']);
            }
            $system = $result['data'];

            $rsp['success'] = true;
            $rsp['data'] = compact('code', 'system');
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function initCurvePerformance($request)
    {
        $rsp = FG::responseDefault();
        try {
            $code = $request->getAttribute('code');
            $filename = $this->verifyReportFile($request);

            $spreadsheetManage = new SpreadsheetManage();
            $result = $spreadsheetManage->initCurvePerformance(compact('filename'));
            if (!$result['success']) {
                throw new \Exception($result['message']);
            }
            $system = $result['data'];

            $rsp['success'] = true;
            $rsp['data'] = compact('code', 'system');
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function onCurvePerformance($request)
    {
        $rsp = FG::responseDefault();
        try {
            $args = $request->getParsedBody();
            extract($args);
            $code = $args['code'];
            if (!$code) {
                throw new \Exception('The code required');
            }
            $filename = $this->verifyReportFile($request);
            $spreadsheetManage = new SpreadsheetManage();
            $result = $spreadsheetManage->onCurvePerformance(compact('filename', 'period'));
            if (!$result['success']) {
                throw new \Exception($result['message']);
            }

            $result = $spreadsheetManage->initCurvePerformance(compact('filename'));

            $rsp['success'] = true;
            $rsp['data'] = $result['data'];
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function initStructureDeveloped($request)
    {
        $rsp = FG::responseDefault();
        try {
            $code = $request->getAttribute('code');
            if (!$code) {
                throw new \Exception('The code required');
            }
            $filename = $this->verifyReportFile($request);
            $spreadsheetManage = new SpreadsheetManage();
            $result = $spreadsheetManage->initStructureDeveloped(compact('filename'));
            if (!$result['success']) {
                throw new \Exception($result['message']);
            }
            $system = $result['data'];

            $rsp['success'] = true;
            $rsp['data'] = compact('code', 'system');
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        // echo json_encode($rsp); exit();
        return $rsp;
    }

    public function initParameterDeveloped($request)
    {
        $rsp = FG::responseDefault();
        try {
            $code = $request->getAttribute('code');
            if (!$code) {
                throw new \Exception('The code required');
            }
            $filename = $this->verifyReportFile($request);
            $spreadsheetManage = new SpreadsheetManage();
            $result = $spreadsheetManage->initParameterDeveloped(compact('filename'));
            if (!$result['success']) {
                throw new \Exception($result['message']);
            }
            $system = $result['data'];

            $rsp['success'] = true;
            $rsp['data'] = compact('code', 'system');
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function initAverageDeveloped($request)
    {
        $rsp = FG::responseDefault();
        try {
            $code = $request->getAttribute('code');
            if (!$code) {
                throw new \Exception('The code required');
            }
            $filename = $this->verifyReportFile($request);
            $spreadsheetManage = new SpreadsheetManage();
            $result = $spreadsheetManage->initAverageDeveloped(compact('filename'));
            if (!$result['success']) {
                throw new \Exception($result['message']);
            }
            $system = $result['data'];

            $rsp['success'] = true;
            $rsp['data'] = compact('code', 'system');
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function initStructureEmerging($request)
    {
        $rsp = FG::responseDefault();
        try {
            $code = $request->getAttribute('code');
            if (!$code) {
                throw new \Exception('The code required');
            }
            $filename = $this->verifyReportFile($request);
            $spreadsheetManage = new SpreadsheetManage();
            $result = $spreadsheetManage->initStructureEmerging(compact('filename'));
            if (!$result['success']) {
                throw new \Exception($result['message']);
            }
            $system = $result['data'];

            $rsp['success'] = true;
            $rsp['data'] = compact('code', 'system');
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function initParameterEmerging($request)
    {
        $rsp = FG::responseDefault();
        try {
            $code = $request->getAttribute('code');
            if (!$code) {
                throw new \Exception('The code required');
            }
            $filename = $this->verifyReportFile($request);
            $spreadsheetManage = new SpreadsheetManage();
            $result = $spreadsheetManage->initParameterEmerging(compact('filename'));
            if (!$result['success']) {
                throw new \Exception($result['message']);
            }
            $system = $result['data'];

            $rsp['success'] = true;
            $rsp['data'] = compact('code', 'system');
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function initAverageEmerging($request)
    {
        $rsp = FG::responseDefault();
        try {
            $code = $request->getAttribute('code');
            if (!$code) {
                throw new \Exception('The code required');
            }
            $filename = $this->verifyReportFile($request);
            $spreadsheetManage = new SpreadsheetManage();
            $result = $spreadsheetManage->initAverageEmerging(compact('filename'));
            if (!$result['success']) {
                throw new \Exception($result['message']);
            }
            $system = $result['data'];
            $report = Report::where('code', $code)->first();

            $rsp['success'] = true;
            $rsp['data'] = compact('code', 'system', 'report');
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function initStructureCompany($request)
    {
        $rsp = FG::responseDefault();
        try {
            $code = $request->getAttribute('code');
            if (!$code) {
                throw new \Exception('The code required');
            }
            $filename = $this->verifyReportFile($request);
            $spreadsheetManage = new SpreadsheetManage();
            $result = $spreadsheetManage->initStructureCompany(compact('filename'));
            if (!$result['success']) {
                throw new \Exception($result['message']);
            }
            $system = $result['data'];
            $report = Report::where('code', $code)->first();

            $rsp['success'] = true;
            $rsp['data'] = compact('code', 'system', 'report');
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function initParameterCompany($request)
    {
        $rsp = FG::responseDefault();
        try {
            $code = $request->getAttribute('code');
            if (!$code) {
                throw new \Exception('The code required');
            }
            $filename = $this->verifyReportFile($request);
            $spreadsheetManage = new SpreadsheetManage();
            $result = $spreadsheetManage->initParameterCompany(compact('filename'));
            if (!$result['success']) {
                throw new \Exception($result['message']);
            }
            $system = $result['data'];

            $rsp['success'] = true;
            $rsp['data'] = compact('code', 'system');
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function initAverageDolaresCompany($request)
    {
        $rsp = FG::responseDefault();
        try {
            $code = $request->getAttribute('code');
            if (!$code) {
                throw new \Exception('The code required');
            }
            $filename = $this->verifyReportFile($request);
            $spreadsheetManage = new SpreadsheetManage();
            $result = $spreadsheetManage->initAverageDolaresCompany(compact('filename'));
            if (!$result['success']) {
                throw new \Exception($result['message']);
            }
            $system = $result['data'];

            $rsp['success'] = true;
            $rsp['data'] = compact('code', 'system');
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function initAverageNationalCompany($request)
    {
        $rsp = FG::responseDefault();
        try {
            $code = $request->getAttribute('code');
            if (!$code) {
                throw new \Exception('The code required');
            }
            $filename = $this->verifyReportFile($request);
            $spreadsheetManage = new SpreadsheetManage();
            $result = $spreadsheetManage->initAverageNationalCompany(compact('filename'));
            if (!$result['success']) {
                throw new \Exception($result['message']);
            }
            $system = $result['data'];

            $rsp['success'] = true;
            $rsp['data'] = compact('code', 'system');
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function initReportCompany($request)
    {
        $rsp = FG::responseDefault();
        try {
            $code = $request->getAttribute('code');
            if (!$code) {
                throw new \Exception('The code required');
            }
            $filename = $this->verifyReportFile($request);
            $spreadsheetManage = new SpreadsheetManage();
            $result = $spreadsheetManage->initReportCompany(compact('filename'));
            if (!$result['success']) {
                throw new \Exception($result['message']);
            }
            $system = $result['data'];

            $rsp['success'] = true;
            $rsp['data'] = compact('code', 'system');
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function initComparation($request)
    {
        $rsp = FG::responseDefault();
        try {
            $code = $request->getAttribute('code');
            if (!$code) {
                throw new \Exception('The code required');
            }
            $filename = $this->verifyReportFile($request);
            $spreadsheetManage = new SpreadsheetManage();
            $result = $spreadsheetManage->initComparation(compact('filename'));
            if (!$result['success']) {
                throw new \Exception($result['message']);
            }
            $system = $result['data'];

            $rsp['success'] = true;
            $rsp['data'] = compact('code', 'system');
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function initReportSectorial($request)
    {
        $rsp = FG::responseDefault();
        try {
            $code = $request->getAttribute('code');
            if (!$code) {
                throw new \Exception('The code required');
            }
            $filename = $this->verifyReportFile($request);
            $spreadsheetManage = new SpreadsheetManage();
            $result = $spreadsheetManage->initReportSectorial(compact('filename'));
            if (!$result['success']) {
                throw new \Exception($result['message']);
            }
            $system = $result['data'];
            $report = Report::where('code', $code)->first();

            $rsp['success'] = true;
            $rsp['data'] = compact('code', 'system', 'report');
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function listDocumentsReport($request)
    {
        $rsp = FG::responseDefault();
        try {
            $code = $request->getAttribute('code');
            if (!$code) {
                header("Location: /");
            }
            $user_id = isset($_SESSION['user']) ? $_SESSION['user']->id : 0;
            $report = Report::where('code', $code)->where('user_id', $user_id)->first();
            if (!$report) {
                header("Location: /");
            }
            $designs = Design::where('deleted_at')->where('status', 1)->where('type_id', $report->type_id)->where('type_b_a', $report->type_b_a)->get();

            if (isset($_SESSION['user'])) {
                $user = $_SESSION['user'];
                $bitacorasVentas = $bitacoraVenta = BitacoraVenta::where('email', $user->email)->where('status', 'pagado')->get();
                $keys = array();
                foreach ($bitacorasVentas as $key => $value) {
                    $keys[$value->document_id] = $value->code;
                }
                foreach ($designs as $key => $value) {
                    if (isset($keys[$value->id])) {
                        $designs[$key]->bitacora_code = $keys[$value->id];
                    }
                }
            }

            $rsp['success'] = true;
            $rsp['data'] = compact('code', 'designs', 'report');
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function initPaymentReport($request)
    {
        $rsp = FG::responseDefault();
        try {
            $code = $request->getAttribute('code');
            $id = $request->getAttribute('id');
            if (!$code || !$id) {
                header("Location: /");
            }
            $system = array();
            // $report = null;
            // if (isset($_SESSION['user'])) {
            //     $user = $_SESSION['user'];
            //     $report = Report::where('user_id', $user->id)->where('code', $code)->first();
            // } else {
            $report = Report::where('code', $code)->first();
            // }

            $design = Design::where('id', $id)->first();

            $mobiles = array();
            $banks = array();
            $transfers = Transfer::get();
            foreach ($transfers as $k => $o) {
                if (!$o->deleted_at) {
                    if ($o->type_id == Transfer::TYPE_BANK) {
                        array_push($banks, $o);
                    } else if ($o->type_id == Transfer::TYPE_MOBILE) {
                        array_push($mobiles, $o);
                    }
                }
            }

            $user = isset($_SESSION['user']) ?  $_SESSION['user'] : null;

            $bitacoraVenta = $user ? BitacoraVenta::where('user_id', $user->id)->where('report_id', $report->id)->where('document_id', $design->id)->first() : null;

            $rsp['success'] = true;
            $rsp['data'] = compact('code', 'report', 'banks', 'mobiles', 'design', 'bitacoraVenta');
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function initCalculateSectorBonusReport($request)
    {
        $rsp = FG::responseDefault();
        try {
            $code = end(explode("/", $request->getUri()->getpath()));

            $user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
            if (!$user) {
                header('Location: /');
                // throw new \Exception('The user must be logged into the platform');
            }

            $report = Report::where('user_id', $user->id)->where('code', $code)->first();
            if (!$report) {
                throw new \Exception('The file no found');
            }

            $file_user = FG::fullPathUser($user->folder, $report->file);

            $spreadsheetManage = new SpreadsheetManage();
            $result = $spreadsheetManage->initCalculateSectorBonusReport(compact('file_user'));
            if (!$result['success']) {
                throw new \Exception($result['message']);
            }
            $system = $result['data'];
            $riskFreeRate = @$_POST['riskFreeRate'];
            $rsp['success'] = true;
            $rsp['data'] = compact('system', 'code', 'riskFreeRate');
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function initCalculateSectorEmployeeReport($request)
    {
        $_POST['riskFreeRate'] = EMPLOYEE;
        return $this->initCalculateSectorBonusReport($request);
    }

    public function initCalculateSectorBonusReport2($request)
    {
        $rsp = FG::responseDefault();
        try {
            $code = end(explode("/", $request->getUri()->getpath()));

            $user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
            if (!$user) {
                header('Location: /');
                // throw new \Exception('The user must be logged into the platform');
            }

            $report = Report::where('user_id', $user->id)->where('code', $code)->first();
            if (!$report) {
                throw new \Exception('The file no found');
            }

            $file_user = FG::fullPathUser($user->folder, $report->file);

            $spreadsheetManage = new SpreadsheetManage();
            $result = $spreadsheetManage->initCalculateSectorBonusReport2(compact('file_user'));
            if (!$result['success']) {
                throw new \Exception($result['message']);
            }
            $system = $result['data'];
            $redirect = @$_POST['riskFreeRate'] == EMPLOYEE ? '/empleado-inversiones/' . $code : '/inversiones/' . $code;
            $rsp['success'] = true;
            $rsp['data'] = compact('system', 'code', 'redirect');
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function initCalculateSectorEmployeeReport2($request)
    {
        $_POST['riskFreeRate'] = EMPLOYEE;
        return $this->initCalculateSectorBonusReport2($request);
    }

    public function marketDeveloperBonusReport($request)
    {
        $rsp = FG::responseDefault();
        try {
            $args = $request->getParsedBody();
            extract($args);
            $code = end(explode("/", $request->getUri()->getpath()));

            $user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
            if (!$user) {
                header('Location: /');
                // throw new \Exception('The user must be logged into the platform');
            }

            $report = Report::where('user_id', $user->id)->where('code', $code)->first();
            if (!$report) {
                throw new \Exception('The file no found');
            }

            $file_user = FG::fullPathUser($user->folder, $report->file);

            $spreadsheetManage = new SpreadsheetManage();
            $result = $spreadsheetManage->marketDeveloperBonusReport(compact('file_user'));
            if (!$result['success']) {
                throw new \Exception($result['message']);
            }
            $system = $result['data'];
            $redirect = @$_POST['riskFreeRate'] == EMPLOYEE ? '/empleado-sectores/' . $code : '/sectores/' . $code;
            $riskFreeRate = @$_POST['riskFreeRate'] == EMPLOYEE ? EMPLOYEE : 'bonus';
            $rsp['success'] = true;
            $rsp['data'] = compact('system', 'code', 'redirect', 'riskFreeRate');
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function marketDeveloperEmployeeReport($request)
    {
        $_POST['riskFreeRate'] = 'employee';
        return $this->marketDeveloperBonusReport($request);
    }

    public function initCalculateInvestmentBonusReport($request)
    {
        $rsp = FG::responseDefault();
        try {

            $code = end(explode("/", $request->getUri()->getpath()));

            $user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
            if (!$user) {
                header('Location: /');
                // throw new \Exception('The user must be logged into the platform');
            }

            $report = Report::where('user_id', $user->id)->where('code', $code)->first();
            if (!$report) {
                throw new \Exception('The file no found');
            }

            $file_user = FG::fullPathUser($user->folder, $report->file);

            $spreadsheetManage = new SpreadsheetManage();
            $result = $spreadsheetManage->initCalculateInvestmentBonusReport(compact('file_user'));
            if (!$result['success']) {
                throw new \Exception($result['message']);
            }
            $system = $result['data'];
            $riskFreeRate = @$_POST['riskFreeRate'] == EMPLOYEE ? EMPLOYEE : 'bonus';
            $rsp['success'] = true;
            $rsp['data'] = compact('system', 'code', 'riskFreeRate');
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function initCalculateInvestmentEmployeeReport($request)
    {
        $_POST['riskFreeRate'] = EMPLOYEE;
        return $this->initCalculateInvestmentBonusReport($request);
    }

    public function ratesCostBonusReport($request)
    {
        $rsp = FG::responseDefault();
        try {
            $code = end(explode("/", $request->getUri()->getpath()));

            $user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
            if (!$user) {
                header('Location: /');
                // throw new \Exception('The user must be logged into the platform');
            }

            $report = Report::where('user_id', $user->id)->where('code', $code)->first();
            if (!$report) {
                throw new \Exception('The file no found');
            }

            $file_user = FG::fullPathUser($user->folder, $report->file);

            $spreadsheetManage = new SpreadsheetManage();
            $result = $spreadsheetManage->ratesCostBonusReport(compact('file_user'));
            if (!$result['success']) {
                throw new \Exception($result['message']);
            }
            $system = $result['data'];
            $rsp['success'] = true;
            $rsp['data'] = compact('system', 'code');
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function ratesCostEmployeeReport($request)
    {
        $_POST['riskFreeRate'] = EMPLOYEE;
        return $this->ratesCostBonusReport($request);
    }

    public function onCreateRecord($request)
    {
        $rsp = FG::responseDefault();
        try {

            $args = $request->getParsedBody();
            extract($args);
            /*if (Report::SECTORIAL_TYPE != @$_POST['sectorial']) {
                if (!$name) {
                    throw new \Exception('La inversión es un campo obligatorio');
                }

                if (!$entity) {
                    throw new \Exception('La empresa es un campo obligatorio');
                }
            }*/
            $template = Template::where('deleted_at')->where('status', 1)->where('version', 1)->first();

            if (!$template) {
                throw new \Exception('The platform does not have a master template');
            }

            $user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
            if ($user) {
                $full_path = FG::fullFolderPath($user->folder);
                $user_id = $user->id;
            } else {
                $full_path = FG::fullFolderGuestPath();
                $user_id = 0;
            }

            if (!is_dir($full_path)) {
                mkdir($full_path, 0777, true);
            }

            $full_path_master = FG::getPathMaster($template->file);
            $pathinfo = pathinfo($full_path_master);
            $uniqid = uniqid();
            $code = strtoupper('KAP' . $uniqid);
            $filename = time() . '-' . $code . '.' . $pathinfo['extension'];

            $full_path_file = $full_path . '/' . $filename;

            copy($full_path_master, $full_path_file);

            $report = new Report();
            $report->file = $filename;
            $report->code = $code;
            // $report->name = $name;
            // $report->entity = $entity;
            $report->template_id = $template->id;
            $report->user_id = $user_id;
            $report->type_id = @$_POST['sectorial'] ? Report::SECTORIAL_TYPE : Report::COMPANY_TYPE;
            $report->type_b_a = 1;
            $report->save();

            $rsp['success'] = true;
            $rsp['data'] = compact('report');
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function flowsProject($request)
    {
        $rsp = FG::responseDefault();
        try {

            $code = $request->getAttribute('code');
            if (!$code) {
                throw new \Exception('The code required');
            }
            $filename = $this->verifyReportFile($request);
            $spreadsheetManage = new SpreadsheetManage();
            $result = $spreadsheetManage->flowsProject(compact('filename'));
            if (!$result['success']) {
                throw new \Exception($result['message']);
            }
            $system = $result['data'];

            $rsp['success'] = true;
            $rsp['data'] = compact('code', 'system');
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function onCalculation($request)
    {
        $rsp = FG::responseDefault();
        try {
            $args = $request->getParsedBody();
            extract($args);

            if (!$code) {
                throw new \Exception('The code is required');
            }

            $filename = $this->verifyReportFile($request);
            $spreadsheetManage = new SpreadsheetManage();
            $result = $spreadsheetManage->onCalculation(compact('filename', 'sector', 'instrument'));
            if (!$result['success']) {
                throw new \Exception($result['message']);
            }

            $aux_type_calculation = 1;
            if ($instrument == SYSTEM::BONUS_EMPLOYEE) {
                $aux_type_calculation = 2;
            }

            $data_update = array(
                "type_b_a" => $aux_type_calculation
            );

            Report::where('deleted_at')
                ->where('code', $code)
                ->update($data_update);

            $redirect = $rate == SYSTEM::BONUS_EEUU ? '/rendimiento/' . $code : '/flujos/' . $code;

            $rsp['success'] = true;
            $rsp['data'] = compact('redirect');
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function onCountryEmerging($request)
    {
        $rsp = FG::responseDefault();
        try {
            $args = $request->getParsedBody();
            extract($args);

            if (!$code) {
                throw new \Exception('The code is required');
            }

            $filename = $this->verifyReportFile($request);
            $spreadsheetManage = new SpreadsheetManage();
            $result = $spreadsheetManage->onCountryEmerging(compact('filename', 'country'));
            if (!$result['success']) {
                throw new \Exception($result['message']);
            }
            $system = $result['data'];

            $rsp['success'] = true;
            $rsp['data'] = compact('system');
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function onDevaluationEmerging($request)
    {
        $rsp = FG::responseDefault();
        try {
            $args = $request->getParsedBody();
            extract($args);

            if (!$code) {
                throw new \Exception('The code is required');
            }

            $filename = $this->verifyReportFile($request);
            $spreadsheetManage = new SpreadsheetManage();
            $result = $spreadsheetManage->onDevaluationEmerging(compact('filename', 'devaluation', 'debt'));
            if (!$result['success']) {
                throw new \Exception($result['message']);
            }
            $system = $result['data'];

            $report = Report::where('code', $code)->first();

            $rsp['success'] = true;
            $rsp['data'] = compact('system', 'report');
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function onPercentageCurrencyCompany($request)
    {
        $rsp = FG::responseDefault();
        try {
            $args = $request->getParsedBody();
            extract($args);

            if (!$code) {
                throw new \Exception('The code is required');
            }

            $filename = $this->verifyReportFile($request);
            $spreadsheetManage = new SpreadsheetManage();
            $result = $spreadsheetManage->onPercentageCurrencyCompany(compact('filename', 'percentage', 'currency'));
            if (!$result['success']) {
                throw new \Exception($result['message']);
            }
            $system = $result['data'];

            $rsp['success'] = true;
            $rsp['data'] = compact('system');
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function onPercentageInvestment($request)
    {
        $rsp = FG::responseDefault();
        try {
            $args = $request->getParsedBody();
            extract($args);

            if (!$code) {
                throw new \Exception('The code is required');
            }

            $filename = $this->verifyReportFile($request);
            $spreadsheetManage = new SpreadsheetManage();
            $result = $spreadsheetManage->onPercentageInvestment(compact('filename', 'debt'));
            if (!$result['success']) {
                throw new \Exception($result['message']);
            }
            $system = $result['data'];

            $rsp['success'] = true;
            $rsp['data'] = compact('system');
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function costCalculationSectorUser($request)
    {
        $rsp = FG::responseDefault();
        try {
            $args = $request->getParsedBody();
            extract($args);

            if (!$code) {
                throw new \Exception('The code is required');
            }

            $user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
            if (!$user) {
                header('Location: /');
                // throw new \Exception('The user must be logged into the platform');
            }

            $report = Report::where('user_id', $user->id)->where('code', $code)->first();
            if (!$report) {
                throw new \Exception('The file no found');
            }
            $folder = $user->folder;
            $filename = $report->file;
            $spreadsheetManage = new SpreadsheetManage();
            $result = $spreadsheetManage->costCalculationSectorUser(compact('filename', 'folder', 'num1', 'num2', 'country'));
            if (!$result['success']) {
                throw new \Exception($result['message']);
            }

            $redirect = @$_POST['riskFreeRate'] == EMPLOYEE ? '/empleado-emergencias/' . $code : '/emergencias/' . $code;

            $rsp['success'] = true;
            $rsp['data'] = compact('redirect');
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function costCalculationInvesmentUser($request)
    {
        $rsp = FG::responseDefault();
        try {
            $args = $request->getParsedBody();
            extract($args);

            if (!$code) {
                throw new \Exception('The code is required');
            }

            $user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
            if (!$user) {
                header('Location: /');
                // throw new \Exception('The user must be logged into the platform');
            }

            $report = Report::where('user_id', $user->id)->where('code', $code)->first();
            if (!$report) {
                throw new \Exception('The file no found');
            }
            $folder = $user->folder;
            $filename = $report->file;
            $spreadsheetManage = new SpreadsheetManage();
            $result = $spreadsheetManage->costCalculationInvesmentUser(compact('filename', 'folder', 'percentage'));
            if (!$result['success']) {
                throw new \Exception($result['message']);
            }

            $redirect = @$_POST['riskFreeRate'] == EMPLOYEE ? '/empleado-tasas/' . $code : '/tasas/' . $code;

            $rsp['success'] = true;
            $rsp['data'] = compact('redirect');
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function onCalculationFlow($request)
    {
        $rsp = FG::responseDefault();
        try {
            $args = $request->getParsedBody();
            extract($args);
            /*if (!$code) {
                throw new \Exception('The code is required');
            }

            $user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
            if (!$user) {
                header('Location: /');
                // throw new \Exception('The user must be logged into the platform');
            }

            $report = Report::where('user_id', $user->id)->where('code', $code)->first();
            if (!$report) {
                throw new \Exception('The file no found');
            }

            $folder = $user->folder;
            $filename = $report->file;*/
            $filename = $this->verifyReportFile($request);
            $spreadsheetManage = new SpreadsheetManage();
            $result = $spreadsheetManage->onCalculationFlow(compact('filename', 'folder', 'horizon', 'periodicity'));
            if (!$result['success']) {
                throw new \Exception($result['message']);
            }
            $system = $result['data'];
            $rsp['success'] = true;
            $rsp['data'] = compact('system', 'code');
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function onCalculationDetailFlow($request)
    {
        $rsp = FG::responseDefault();
        try {
            $args = $request->getParsedBody();
            extract($args);
            $code = $request->getAttribute('code');

            $filename = $this->verifyReportFile($request);
            $spreadsheetManage = new SpreadsheetManage();
            $result = $spreadsheetManage->onCalculationDetailFlow(compact('filename', 'horizon', 'periodicity', 'flows'));
            if (!$result['success']) {
                throw new \Exception($result['message']);
            }
            $system = $result['data'];
            $rsp['success'] = true;
            $rsp['data'] = compact('system', 'code');
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function deleteReport($request)
    {
        $rsp = FG::responseDefault();
        try {
            $args = $request->getParsedBody();
            extract($args);

            if (!$code) {
                throw new \Exception('The code is required');
            }

            $user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
            if (!$user) {
                header('Location: /');
                // throw new \Exception('The user must be logged into the platform');
            }

            $report = Report::where('user_id', $user->id)->where('code', $code)->first();
            if (!$report) {
                throw new \Exception('The file no found');
            }

            $folder = $user->folder;
            $filename = $report->file;
            $fullpath = FG::fullPathUser($folder, $filename);
            unlink($fullpath);
            $report->deleted_at = FG::getDateHour();
            $report->save();

            $rsp['success'] = true;
            $rsp['message'] = 'Se elimino correctamente';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function updateNewVersion($request)
    {
        $rsp = FG::responseDefault();
        try {
            $args = $request->getParsedBody();
            extract($args);

            if (!$code) {
                throw new \Exception('The code is required');
            }

            $user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
            if (!$user) {
                header('Location: /');
                // throw new \Exception('The user must be logged into the platform');
            }

            if (!$user->folder) {
                throw new \Exception('The folder not found');
            }

            $report = Report::where('user_id', $user->id)->where('code', $code)->first();
            if (!$report) {
                throw new \Exception('The file no found');
            }

            $folder = $user->folder;
            $filename = $report->file;
            $template = Template::where('deleted_at')->where('status', 1)->where('version', 1)->first();

            if (!$template) {
                throw new \Exception('The template master no found');
            }

            $full_path = FG::fullFolderPath($user->folder);
            if (!is_dir($full_path)) {
                mkdir($full_path, 0777, true);
            }

            $full_path_master = FG::getPathMaster($template->file);
            $pathinfo = pathinfo($full_path_master);
            $filename = time() . '-' . uniqid() . '-' . $code . '.' . $pathinfo['extension'];
            $full_path_file = $full_path . '/' . $filename;
            copy($full_path_master, $full_path_file);
            unlink($full_path . '/' . $report->file);
            $report->file = $filename;
            $report->template_id = $template->id;
            $report->save();

            $rsp['success'] = true;
            $rsp['data'] = compact('report');
            $rsp['message'] = 'Se actualiazo correctamente';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function getAllReport($request)
    {
        $rsp = FG::responseDefault();
        try {
            $args = $request->getParsedBody();
            extract($args);
            $report = new Report();
            if (!$id) {
                throw new \Exception('El parametro Id es obligatorio');
            }
            if (!$code) {
                $template = Template::where('deleted_at')->where('status', 1)->where('version', 1)->first();
                $filename = FG::getPathMaster($template->file);
            } else {
                $user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
                $report = Report::where('code', $code)->first();
                if (!$report) {
                    throw new \Exception('El reporte no existe');
                }
                if ($report->user_id == 0) {
                    $filename = FG::fullPathGuest($report->file);
                } else {
                    $user = User::where('id', $report->user_id)->first();
                    if (!$user) {
                        throw new \Exception('El reporte es un archivo sin usuario destinado');
                    }
                    $filename = FG::fullPathUser($user->folder, $report->file);
                }
            }
            $system = Report::getAllReport(compact('filename'));
            $design = Design::where('deleted_at')->where('id', $id)->first();
            if (!$design) {
                throw new \Exception('No encontramos el reporte desiñado');
            }
            $datestring = FG::getFormatDateString(FG::getDateHour('Y-m-d'));
            $contents = Capsule::table('designs_structure_contents')->where('deleted_at')->get();
            $structure = View::render('export/structure.twig', compact('system', 'report', 'design', 'datestring'));
            $rsp['success'] = true;
            $rsp['data'] = compact('report', 'system', 'design', 'structure', 'contents', 'type');
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function initFile($request)
    {
        $rsp = FG::responseDefault();
        try {
            $code = $request->getAttribute('code');
            if (!$code) {
                header('Location: /');
            }
            $bitacoraVenta = BitacoraVenta::where('code', $code)->first();
            if (!$bitacoraVenta) {
                header('Location: /');
            }
            $report = Report::where('id', $bitacoraVenta->report_id)->first();
            $code = $report->code;
            $document = Design::where('id', $bitacoraVenta->document_id)->first();
            $id = $document->id;
            $rsp['success'] = true;
            $rsp['data'] = compact('code', 'id');
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function downloadDocument($request)
    {
        $rsp = FG::responseDefault();
        try {
            $code = $request->getAttribute('code');
            if (!$code) {
                header('Location: /');
            }
            $id = $request->getAttribute('id');
            if (!$id) {
                header('Location: /');
            }
            $report = Report::where('code', $code)->first();
            if (!$report) {
                header('Location: /');
            }
            $user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
            if ($user->perfil != 1) {
                if ($report->precio > 0) {
                    header('Location: /');
                }
            }
            $document = Design::where('id', $id)->where('type_id', $report->type_id)->first();
            if (!$document) {
                header('Location: /');
            }
            $code = $report->code;
            $id = $document->id;
            $rsp['success'] = true;
            $rsp['data'] = compact('code', 'id');
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function viewDocument($request)
    {
        $rsp = FG::responseDefault();
        try {
            $id = $request->getAttribute('id');
            if (!$id) {
                header('Location: /');
            }
            $user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
            if ($user->perfil != 1) {
                header('Location: /');
            }
            $document = Design::where('id', $id)->first();
            if (!$document) {
                header('Location: /');
            }
            $id = $document->id;
            $rsp['success'] = true;
            $rsp['data'] = compact('code', 'id');
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }


    public function registerReport($request)
    {
        $rsp = FG::responseDefault();
        try {

            $input     = $request->getParsedBody();
            $email     = $input['email'];
            $password  = $input['password'];
            $inversion = $input['inversion'];
            $empresa   = $input['empresa'];
            $code      = $input['code'];

            $report = Report::where('code', $code)->first();
            if (!$report) {
                throw new \Exception('El reporte no existe');
            }

            $user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
            if (!$user) {
                if (!$email) {
                    throw new \Exception('El campo email es obligatorio');
                }
                if (!$password) {
                    throw new \Exception('El campo contraseña es obligatorio');
                }
            }

            if (!$user) {
                $user = User::where('deleted_at')->where('email', $email)->first();
                if ($user) {
                    if ($password != Crypt::decrypt($user->password)) {
                        throw new \Exception('Datos Incorrectos del usuario');
                    }
                } else {
                    $user = new User();
                    $user->email = $email;
                    $user->folder = uniqid(time());
                    $user->password = Crypt::encrypt($password);
                    $user->save();

                    $path = FG::fullFolderPath($user->folder);
                    if (!is_dir($path)) {
                        mkdir($path, 0777, true);
                    }
                }
                $user->registerRecord($report->code, $user);
                $_SESSION['user'] = json_decode(json_encode($user->toArray()));
            }

            $report->user_id = $user->id;
            $report->save();

            if ($report->type_id == Report::COMPANY_TYPE) {
                $report->name   = $inversion;
                $report->entity = $empresa;
                $report->save();
            }

            $rsp['success'] = true;
            $rsp['message'] = 'Se guardo correctamente';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }
}
