<?php

namespace App\Libraries;

use Illuminate\Database\Capsule\Manager as DB;
use App\Utilitarian\FG;
use App\Traits\FinanceExcelTrait;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Services\OnedriveService;
use App\Dows\OnedriveDow;

class KapitalExcel
{

    use FinanceExcelTrait;

    public function getForm($filename, $propietary = false)
    {

        $result = array(
            'dates'       => '',
            'date'        => '',
            'sectors'     => '',
            'instruments' => '',
            'bonos'       => '',
            'countries'   => '',
            'sector'      => '',
            'instrument'  => '',
            'bono'        => '',
            'country'     => '',
            'currencies'  => '',
            'currency'    => '',
            'devaluation' => '',
            'capital'     => '',
            'tax'         => '',
            'epd'         => '',
            'epc'         => '',
            'ekd'         => ''
        );

        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();

        $reader->setLoadSheetsOnly($this->sheetname_industries);
        $spreadsheet = $reader->load($filename);
        $worksheet = $spreadsheet->getActiveSheet();
        $lastRow = $worksheet->getHighestRow();
        $sectors = array();
        for ($row = 3; $row <= $lastRow; $row++) {
            $value = $worksheet->getCell('A' . $row)->getValue();
            if (!$value) {
                break;
            }
            $sectors[] = $value;
        }
        $result['sectors'] = $sectors;

        $reader->setLoadSheetsOnly($this->sheetname_tablas);
        $spreadsheet = $reader->load($filename);
        $worksheet = $spreadsheet->getActiveSheet();
        $lastRow = $worksheet->getHighestRow();
        $instruments = array();
        for ($row = 2; $row <= $lastRow; $row++) {
            $value = $worksheet->getCell('A' . $row)->getValue();
            if (!$value) {
                break;
            }
            $instruments[] = $value;
        }
        $result['instruments'] = $instruments;

        $dates = array();
        for ($row = 41; $row <= $lastRow; $row++) {
            $value = $worksheet->getCell('A' . $row)->getFormattedValue();
            if (!$value) {
                break;
            }
            if ($value) {
                $datetime = new \DateTime($value);
                $value = $datetime->format('d/m/Y');
            }
            $dates[] = $value;
        }
        $result['dates'] = $dates;

        $bonos = array();
        for ($row = 2; $row <= $lastRow; $row++) {
            $value = $worksheet->getCell('C' . $row)->getValue();
            if (!$value) {
                break;
            }
            $bonos[] = $value;
        }
        $result['bonos'] = $bonos;

        $currencies = array();
        for ($row = 2; $row <= $lastRow; $row++) {
            $value = $worksheet->getCell('L' . $row)->getValue();
            if (!$value) {
                break;
            }
            $currencies[] = $value;
        }
        $result['currencies'] = $currencies;

        $reader->setLoadSheetsOnly($this->sheetname_contries);
        $spreadsheet = $reader->load($filename);
        $worksheet = $spreadsheet->getActiveSheet();
        $lastRow = $worksheet->getHighestRow();
        $countries = array();
        for ($row = 2; $row <= $lastRow; $row++) {
            $value = $worksheet->getCell('A' . $row)->getValue();
            if (!$value) {
                break;
            }
            $countries[] = $value;
        }
        $result['countries'] = $countries;

        // $reader->setLoadSheetsOnly($this->sheetname_wacc); 
        // $spreadsheet = $reader->load($filename);
        // $worksheet = $spreadsheet->getActiveSheet();


        $sector = "";
        $instrument = "";
        $bono = "";
        $country = "";
        if ($propietary) {

            $reader->setLoadSheetsOnly($this->sheetname_user);
            $spreadsheet = $reader->load($filename);
            $worksheet = $spreadsheet->getActiveSheet();
            $lastRow = $worksheet->getHighestRow();
            $date = $worksheet->getCell('C2')->getFormattedValue();
            if ($date) {
                $datearr = explode('/', $date);
                $date = FG::addZeroDecimal($datearr[1]) . '/' . FG::addZeroDecimal($datearr[0]) . '/' . $datearr[2];
            }
            $result['date'] = $date;
            $result['country'] = $worksheet->getCell('C3')->getValue();
            $result['currency'] = $worksheet->getCell('C4')->getValue();

            $reader->setLoadSheetsOnly($this->sheetname_wacc);
            $spreadsheet = $reader->load($filename);
            $worksheet = $spreadsheet->getActiveSheet();
            $result['instrument'] = $worksheet->getCell('C3')->getValue();
            $result['sector'] = $worksheet->getCell('C13')->getValue();
            $result['bono'] = $worksheet->getCell('C6')->getValue();

            // $result['epd'] = FG::formatterx100val($worksheet->getCell('I17')->getOldCalculatedValue());
            // $result['epc'] = FG::formatterx100val($worksheet->getCell('I18')->getOldCalculatedValue());

            $result['epd'] = $worksheet->getCell('I17')->getOldCalculatedValue();
            if (!$result['epd']) {
                $result['epd'] = FG::formatterx100val($worksheet->getCell('I17')->getValue());
            } else {
                $result['epd'] = FG::formatterx100val($result['epd']);
            }
            $result['epc'] = $worksheet->getCell('I18')->getOldCalculatedValue();
            if (!$result['epc']) {
                $result['epc'] = FG::formatterx100val($worksheet->getCell('I18')->getValue());
            } else {
                $result['epc'] = FG::formatterx100val($result['epc']);
            }

            $result['devaluation'] = $worksheet->getCell('F7')->getOldCalculatedValue();
            if (!$result['devaluation']) {
                $result['devaluation'] = FG::formatterx100val($worksheet->getCell('F7')->getValue());
            } else {
                $result['devaluation'] = FG::formatterx100val($result['devaluation']);
            }

            $result['tax'] = $worksheet->getCell('F20')->getOldCalculatedValue();
            if (!$result['tax']) {
                $result['tax'] = FG::formatterx100val($worksheet->getCell('F20')->getValue());
            } else {
                $result['tax'] = FG::formatterx100val($result['tax']);
            }
            /*if ($result['currency'] == 'Soles') {
                $result['ekd'] = FG::formatterx100val($worksheet->getCell('J33')->getOldCalculatedValue());
            } else {
                $result['ekd'] = FG::formatterx100val($worksheet->getCell('I33')->getOldCalculatedValue());
            }*/
            $ekd = $worksheet->getCell('I3')->getOldCalculatedValue();
            if (!$ekd) {
                $ekd = FG::formatterx100val($worksheet->getCell('I3')->getValue());
            } else {
                $ekd = FG::formatterx100val($ekd);
            }
            $result['ekd'] = $ekd;
        }
        return $result;
    }

    /**
     * Obtiene la data para el formulario de valoracion
     * @param string $filename
     * @param boolean $propietary
     * @param object $report
     * @return array
     */
    public function getFormCloud($filename, $brand, $propietary = false, $report = null)
    {

        if ($propietary && empty($report)) {
            throw new \InvalidArgumentException('El parámetro $report es obligatorio cuando $propietary es true');
        }

        $result = array(
            'dates'       => '',
            'date'        => '',
            'sectors'     => '',
            'instruments' => '',
            'bonos'       => '',
            'countries'   => '',
            'sector'      => '',
            'instrument'  => '',
            'bono'        => '',
            'country'     => '',
            'currencies'  => '',
            'currency'    => '',
            'devaluation' => '',
            'capital'     => '',
            'tax'         => '',
            'epd'         => '',
            'epc'         => '',
            'ekd'         => ''
        );

        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();

        $reader->setLoadSheetsOnly($this->sheetname_industries);
        $spreadsheet = $reader->load($filename);
        $worksheet = $spreadsheet->getActiveSheet();
        $lastRow = $worksheet->getHighestRow();
        $sectors = array();
        for ($row = 3; $row <= $lastRow; $row++) {
            $value = $worksheet->getCell('A' . $row)->getValue();
            if (!$value) {
                break;
            }
            $sectors[] = $value;
        }
        $result['sectors'] = $sectors;

        $reader->setLoadSheetsOnly($this->sheetname_tablas);
        $spreadsheet = $reader->load($filename);
        $worksheet = $spreadsheet->getActiveSheet();
        $lastRow = $worksheet->getHighestRow();
        $instruments = array();
        for ($row = 2; $row <= $lastRow; $row++) {
            $value = $worksheet->getCell('A' . $row)->getValue();
            if (!$value) {
                break;
            }
            $instruments[] = $value;
        }
        $result['instruments'] = $instruments;

        $dates = array();
        for ($row = 41; $row <= $lastRow; $row++) {
            $value = $worksheet->getCell('A' . $row)->getFormattedValue();
            if (!$value) {
                break;
            }
            if ($value) {
                $datetime = new \DateTime($value);
                $value = $datetime->format('d/m/Y');
            }
            $dates[] = $value;
        }
        $result['dates'] = $dates;

        $bonos = array();
        for ($row = 2; $row <= $lastRow; $row++) {
            $value = $worksheet->getCell('C' . $row)->getValue();
            if (!$value) {
                break;
            }
            $bonos[] = $value;
        }
        $result['bonos'] = $bonos;

        $currencies = array();
        for ($row = 2; $row <= $lastRow; $row++) {
            $value = $worksheet->getCell('L' . $row)->getValue();
            if (!$value) {
                break;
            }
            $currencies[] = $value;
        }
        $result['currencies'] = $currencies;

        $reader->setLoadSheetsOnly($this->sheetname_contries);
        $spreadsheet = $reader->load($filename);
        $worksheet = $spreadsheet->getActiveSheet();
        $lastRow = $worksheet->getHighestRow();
        $countries = array();
        for ($row = 2; $row <= $lastRow; $row++) {
            $value = $worksheet->getCell('A' . $row)->getValue();
            if (!$value) {
                break;
            }
            $countries[] = $value;
        }
        $result['countries'] = $countries;

        $sector = "";
        $instrument = "";
        $bono = "";
        $country = "";
        if ($propietary) {

            $company = DB::table('empresas')->where('deleted_at')->first();

            $onedriveDow = new OnedriveDow();
            $onedriveService = new OnedriveService();
            $onedriveService = $onedriveDow->handleToken($company->token_onedrive, $onedriveService);

            $req = $onedriveService->getTableRows($report->eid, 'WACC', 'Tabla7');
            $rows = $req['value'];
            foreach ($rows as $key => $row) {
                $value = $row['values'][0][1];
                switch ($row['index']) {
                    case 0:
                        $dateObject = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value);
                        $dateObject = json_decode(json_encode($dateObject));
                        $dateXsl = FG::getDateFormat($dateObject->date, 'd/m/Y');
                        $result['date'] = $dateXsl;
                        break;
                    case 1:
                        $result['sector'] = $value;
                        break;
                    case 2:
                        $result['instrument'] = $value;
                        break;
                    case 3:
                        $result['bono'] = $value;
                        break;
                    case 4:
                        $result['country'] = $value;
                        break;
                    case 5:
                        $result['devaluation'] = FG::formatterx100val($value);
                        break;
                    case 6:
                        $result['tax'] = FG::formatterx100val($value);
                        break;
                    case 7:
                        $result['currency'] = $value;
                        break;
                    case 8:
                        $result['ekd'] = FG::formatterx100val($value);
                        break;
                    case 9:
                        $result['epd'] = FG::formatterx100val($value);
                        break;
                    case 10:
                        $result['epc'] = FG::formatterx100val($value);
                        break;
                    default:
                        # code...
                        break;
                }
            }
        }
        return $result;
    }

    public function setForm($filename, $input)
    {

        $date       = $input['date'];
        $sector     = $input['sector'];
        $instrument = $input['instrument'];
        $bono       = $input['bono'];
        $country    = $input['country'];
        $typeId     = $input['typeId'];
        $currency   = $input['currency'];
        $devaluation = $input['devaluation'];
        $tax        = $input['tax'];
        $debt       = $input['debt'];
        $capital    = $input['capital'];
        $kd         = $input['kd'];

        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $spreadsheet = $reader->load($filename);

        $worksheet = $spreadsheet->getSheetByName($this->sheetname_user);

        $worksheet->setCellValue('C3', $country);
        if ($typeId == 2) {
            $worksheet->setCellValue('C4', $currency);
        }

        $dateValue = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($date);
        $worksheet->setCellValue('C2', $dateValue);

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($filename);

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filename);

        $worksheet = $spreadsheet->getSheetByName($this->sheetname_wacc);
        $worksheet->setCellValue('C3', $instrument);
        $worksheet->setCellValue('C13', $sector);

        $devaluation = ($devaluation / 100);
        $devaluation = $devaluation > 0 ? $devaluation : 0;

        $tax = ($tax / 100);
        $tax = $tax > 0 ? $tax : 0;

        $worksheet->setCellValue('F7', $devaluation);
        $worksheet->setCellValue('F20', $tax);

        if ($typeId == 1) {
            $worksheet->setCellValue('C6', $bono);
        } else {
            $kd = ($kd / 100);
            $kd = $kd > 0 ? $kd : 0;

            $debt = ($debt / 100);
            $debt = $debt > 0 ? $debt : 0;

            $capital = ($capital / 100);
            $capital = $capital > 0 ? $capital : 0;

            $worksheet->setCellValue('I3', $kd);
            $worksheet->setCellValue('I17', $debt);
            $worksheet->setCellValue('I18', $capital);

            $worksheet->setCellValue('J3', $currency);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($filename);
    }

    public function setFormCloud($report = null, $input)
    {

        $date        = $input['date'];
        $sector      = $input['sector'];
        $instrument  = $input['instrument'];
        $bono        = $input['bono'];
        $country     = $input['country'];
        $typeId      = $input['typeId'];
        $currency    = $input['currency'];
        $devaluation = $input['devaluation'];
        $tax         = $input['tax'];
        $debt        = $input['debt'];
        $capital     = $input['capital'];
        $kd          = $input['kd'];

        $company = DB::table('empresas')->where('deleted_at')->first();
        $dateValue = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($date);

        $onedriveDow = new OnedriveDow();
        $onedriveService = new OnedriveService();
        $onedriveService = $onedriveDow->handleToken($company->token_onedrive, $onedriveService);

        $cell = $onedriveService->setCell($report->eid, $this->sheetname_user, 1, 2, $dateValue, 'Double');
        $cell = $onedriveService->setCell($report->eid, $this->sheetname_user, 2, 2, $country, 'String');

        $cell = $onedriveService->setCell($report->eid, $this->sheetname_wacc, 2, 2, $instrument, 'String');
        $cell = $onedriveService->setCell($report->eid, $this->sheetname_wacc, 5, 2, $bono, 'Double');
        $cell = $onedriveService->setCell($report->eid, $this->sheetname_wacc, 12, 2, $sector, 'String');

        $devaluation = ($devaluation / 100);
        $devaluation = $devaluation > 0 ? $devaluation : 0;

        $tax = ($tax / 100);
        $tax = $tax > 0 ? $tax : 0;

        $cell = $onedriveService->setCell($report->eid, $this->sheetname_wacc, 6, 5, $devaluation, 'Double');
        $cell = $onedriveService->setCell($report->eid, $this->sheetname_wacc, 19, 5, $tax, 'Double');

        if ($typeId == 2) {
            $kd = ($kd / 100);
            $kd = $kd > 0 ? $kd : 0;

            $debt = ($debt / 100);
            $debt = $debt > 0 ? $debt : 0;

            $capital = ($capital / 100);
            $capital = $capital > 0 ? $capital : 0;

            $cell = $onedriveService->setCell($report->eid, $this->sheetname_user, 3, 2, $currency, 'String');
            $cell = $onedriveService->setCell($report->eid, $this->sheetname_wacc, 2, 8, $kd, 'Double');
            $cell = $onedriveService->setCell($report->eid, $this->sheetname_wacc, 16, 8, $debt, 'Double');
            $cell = $onedriveService->setCell($report->eid, $this->sheetname_wacc, 17, 8, $capital, 'Double');
        }
    }

    public function getResultCloud($report = null)
    {
        $rsp = [];
        $company = DB::table('empresas')->where('deleted_at')->first();

        $onedriveDow = new OnedriveDow();
        $onedriveService = new OnedriveService();
        $onedriveService = $onedriveDow->handleToken($company->token_onedrive, $onedriveService);

        $result = $onedriveService->getTableRows($report->eid, 'WACC', 'Tabla3');
        $rows = $result['value'];
        foreach ($rows as $key => $row) {
            switch ($row['index']) {
                case 1:
                    $rsp['developed']['ke']       = FG::formatterx100val($row['values'][0][1]);
                    $rsp['emergent']['ke']      = FG::formatterx100val($row['values'][0][2]);
                    $rsp['company']['usd']['ke'] = FG::formatterx100val($row['values'][0][3]);
                    $rsp['company']['pen']['ke'] = FG::formatterx100val($row['values'][0][4]);
                    break;
                case 2:
                    $rsp['developed']['koa']       = FG::formatterx100val($row['values'][0][1]);
                    $rsp['emergent']['koa']      = FG::formatterx100val($row['values'][0][2]);
                    $rsp['company']['usd']['koa'] = FG::formatterx100val($row['values'][0][3]);
                    $rsp['company']['pen']['koa'] = FG::formatterx100val($row['values'][0][4]);
                    break;
                case 3:
                    // $rsp['emergent']['kd']       = FG::formatterx100val($row['values'][0][1]);
                    // $rsp['developed']['kd']      = FG::formatterx100val($row['values'][0][2]);
                    $rsp['company']['usd']['kd'] = FG::formatterx100val($row['values'][0][3]);
                    $rsp['company']['pen']['kd'] = FG::formatterx100val($row['values'][0][4]);
                    break;
                case 4:
                    $rsp['developed']['cppc']       = FG::formatterx100val($row['values'][0][1]);
                    $rsp['emergent']['cppc']      = FG::formatterx100val($row['values'][0][2]);
                    $rsp['company']['usd']['cppc'] = FG::formatterx100val($row['values'][0][3]);
                    $rsp['company']['pen']['cppc'] = FG::formatterx100val($row['values'][0][4]);
                    break;
                case 5:
                    $rsp['developed']['kd']       = FG::formatterx100val($row['values'][0][1]);
                    $rsp['emergent']['kd']      = FG::formatterx100val($row['values'][0][2]);
                    if ($report->type_id == 2) {
                        // $rsp['emergent']['kd']       = FG::formatterx100val($row['values'][0][1]);
                        // $rsp['developed']['kd']      = FG::formatterx100val($row['values'][0][2]);
                        $rsp['company']['usd']['kd'] = FG::formatterx100val($row['values'][0][3]);
                        $rsp['company']['pen']['kd'] = FG::formatterx100val($row['values'][0][4]);
                    }
                    break;
                case 12:
                    $rsp['currency'] = $row['values'][0][1];
                    break;
                default:
                    # code...
                    break;
            }
        }
        return $rsp;
    }

    public function result($filename)
    {

        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $spreadsheet = $reader->load($filename);
        $worksheet = $spreadsheet->getSheetByName($this->sheetname_wacc);

        $costdebt = $worksheet->getCell('I3')->getCalculatedValue();
        if (!$costdebt) {
            $costdebt = FG::formatterx100val($worksheet->getCell('I3')->getValue());
        } else {
            $costdebt = FG::formatterx100val($costdebt);
        }
        return [
            'developed' => [
                'cppc' => FG::formatterx100val($worksheet->getCell('C37')->getCalculatedValue()),
                'kd'   => FG::formatterx100val($worksheet->getCell('C34')->getCalculatedValue()),
                'ke'   => FG::formatterx100val($worksheet->getCell('C24')->getCalculatedValue()),
                'koa'  => FG::formatterx100val($worksheet->getCell('C28')->getCalculatedValue())
            ],
            'emergent' => [
                'cppc' => FG::formatterx100val($worksheet->getCell('F37')->getCalculatedValue()),
                'kd'   => FG::formatterx100val($worksheet->getCell('F34')->getCalculatedValue()),
                'ke'   => FG::formatterx100val($worksheet->getCell('F24')->getCalculatedValue()),
                'koa'  => FG::formatterx100val($worksheet->getCell('F28')->getCalculatedValue())
            ],
            'company' => [
                'cppc' => FG::formatterx100val($worksheet->getCell('I44')->getCalculatedValue()),
                'usd' => [
                    'kd'   => FG::formatterx100val($worksheet->getCell('I34')->getCalculatedValue()),
                    'ke'   => FG::formatterx100val($worksheet->getCell('I24')->getCalculatedValue()),
                    'koa'  => FG::formatterx100val($worksheet->getCell('I28')->getCalculatedValue()),
                    'cppc' => FG::formatterx100val($worksheet->getCell('I37')->getCalculatedValue())
                ],
                'pen' => [
                    'kd'   => FG::formatterx100val($worksheet->getCell('J34')->getCalculatedValue()),
                    'ke'   => FG::formatterx100val($worksheet->getCell('J24')->getCalculatedValue()),
                    'koa'  => FG::formatterx100val($worksheet->getCell('J28')->getCalculatedValue()),
                    'cppc' => FG::formatterx100val($worksheet->getCell('J37')->getCalculatedValue())
                ]
            ],
            'relation'     => FG::numberformat($worksheet->getCell('I19')->getCalculatedValue()),
            'costdebt'     => $costdebt // FG::formatterx100val($worksheet->getCell('I3')->getOldCalculatedValue())  
        ];
    }

    public function analysis($filename)
    {

        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();

        $reader->setLoadSheetsOnly($this->sheetname_wacc);
        $spreadsheet = $reader->load($filename);

        $worksheet = $spreadsheet->getActiveSheet();

        $kd = $worksheet->getCell('I3')->getOldCalculatedValue();
        if (!$kd) {
            $kd = FG::formatterx100val($worksheet->getCell('I3')->getValue());
        } else {
            $kd = FG::formatterx100val($kd);
        }

        $dc = $worksheet->getCell('F19')->getOldCalculatedValue();
        if (!$dc) {
            $dc = FG::numberformat($worksheet->getCell('F19')->getValue());
        } else {
            $dc = FG::numberformat($dc);
        }

        $dc2 = $worksheet->getCell('I19')->getOldCalculatedValue();
        if (!$dc2) {
            $dc2 = FG::numberformat($worksheet->getCell('I19')->getValue());
        } else {
            $dc2 = FG::numberformat($dc2);
        }

        $skd = $worksheet->getCell('F33')->getOldCalculatedValue();
        if (!$skd) {
            $skd = FG::formatterx100val($worksheet->getCell('F33')->getValue());
        } else {
            $skd = FG::formatterx100val($skd);
        }

        return [
            'currency' => $worksheet->getCell('J3')->getOldCalculatedValue(),
            'kd'   => $kd,
            'skd'   => $skd,
            'sector' => [
                'cppc' => FG::formatterx100val($worksheet->getCell('F37')->getOldCalculatedValue()),
                'kd'   => FG::formatterx100val($worksheet->getCell('F34')->getOldCalculatedValue()),
                'ke'   => FG::formatterx100val($worksheet->getCell('F24')->getOldCalculatedValue()),
                'koa'  => FG::formatterx100val($worksheet->getCell('F28')->getOldCalculatedValue()),
                'dc'   => $dc
            ],
            'company' => [
                'cppc' => FG::formatterx100val($worksheet->getCell('I44')->getOldCalculatedValue()),
                'dc' => $dc2,
                'usd' => [
                    'kd'   => FG::formatterx100val($worksheet->getCell('I34')->getOldCalculatedValue()),
                    'ke'   => FG::formatterx100val($worksheet->getCell('I24')->getOldCalculatedValue()),
                    'koa'  => FG::formatterx100val($worksheet->getCell('I28')->getOldCalculatedValue()),
                    'cppc' => FG::formatterx100val($worksheet->getCell('I37')->getOldCalculatedValue()),
                    'dc'   => FG::numberformat($worksheet->getCell('I19')->getOldCalculatedValue())
                ],
                'pen' => [
                    'kd'   => FG::formatterx100val($worksheet->getCell('J34')->getOldCalculatedValue()),
                    'ke'   => FG::formatterx100val($worksheet->getCell('J24')->getOldCalculatedValue()),
                    'koa'  => FG::formatterx100val($worksheet->getCell('J28')->getOldCalculatedValue()),
                    'cppc' => FG::formatterx100val($worksheet->getCell('J37')->getOldCalculatedValue()),
                    'dc'   => FG::numberformat($worksheet->getCell('I19')->getOldCalculatedValue())
                ]
            ],
        ];
    }

    public function getAnalysisCloud($report = null)
    {

        $rsp = [];
        $company = DB::table('empresas')->where('deleted_at')->first();

        $onedriveDow = new OnedriveDow();
        $onedriveService = new OnedriveService();
        $onedriveService = $onedriveDow->handleToken($company->token_onedrive, $onedriveService);

        $result = $onedriveService->getTableRows($report->aid, 'WACC', 'Tabla3');
        $rows = $result['value'];
        foreach ($rows as $key => $row) {
            switch ($row['index']) {
                case 1:
                    $rsp['sector']['ke'] = FG::formatterx100val($row['values'][0][2]);
                    $rsp['company']['usd']['ke'] = FG::formatterx100val($row['values'][0][3]);
                    $rsp['company']['pen']['ke'] = FG::formatterx100val($row['values'][0][4]);
                    break;
                case 2:
                    $rsp['sector']['koa']      = FG::formatterx100val($row['values'][0][2]);
                    $rsp['company']['usd']['koa'] = FG::formatterx100val($row['values'][0][3]);
                    $rsp['company']['pen']['koa'] = FG::formatterx100val($row['values'][0][4]);
                    break;
                case 3:
                    $rsp['company']['usd']['kd'] = FG::formatterx100val($row['values'][0][3]);
                    $rsp['company']['pen']['kd'] = FG::formatterx100val($row['values'][0][4]);
                    break;
                case 4:
                    $rsp['sector']['cppc']      = FG::formatterx100val($row['values'][0][2]);
                    $rsp['company']['usd']['cppc'] = FG::formatterx100val($row['values'][0][3]);
                    $rsp['company']['pen']['cppc'] = FG::formatterx100val($row['values'][0][4]);
                    break;
                case 5:
                    $rsp['sector']['kd'] = FG::formatterx100val($row['values'][0][2]);
                    $rsp['skd'] = FG::formatterx100val($row['values'][0][2]);
                    if ($report->type_id == 2) {
                        $rsp['company']['usd']['kd'] = FG::formatterx100val($row['values'][0][3]);
                        $rsp['company']['pen']['kd'] = FG::formatterx100val($row['values'][0][4]);
                    }
                    break;
                case 8:
                    $rsp['sector']['dc'] = FG::numberformat($row['values'][0][1], 4);
                    $rsp['company']['dc'] = FG::numberformat($row['values'][0][2], 4);
                    $rsp['company']['usd']['dc'] = FG::numberformat($row['values'][0][2], 4);
                    $rsp['company']['pen']['dc'] = FG::numberformat($row['values'][0][2], 4);
                    break;
                case 11:
                    $rsp['kd'] = FG::formatterx100val($row['values'][0][1]);
                    break;
                case 12:
                    $rsp['currency'] = $row['values'][0][1];
                    break;
                default:
                    # code...
                    break;
            }
        }
        return $rsp;
    }

    public function costAnalysis($filename, $input)
    {

        $typeId   = $input['typeId'];
        $dc       = $input['dc'];
        $kd       = $input['kd'];
        $currency = !isset($input['currency']) ? 'Dólares' : $input['currency'];

        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $spreadsheet = $reader->load($filename);

        $worksheet = $spreadsheet->getSheetByName($this->sheetname_user);
        $worksheet->setCellValue('C4', $currency);

        $xlsxWriter = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $xlsxWriter->save($filename);

        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $spreadsheet = $reader->load($filename);

        $worksheet = $spreadsheet->getSheetByName($this->sheetname_wacc);

        $kd = ($kd / 100);
        $kd = $kd > 0 ? $kd : 0;

        if ($typeId == 1) {
            $worksheet->setCellValue('F19', $dc);
            $worksheet->setCellValue('F33', $kd);
        } else {
            $worksheet->setCellValue('I19', $dc);
            $worksheet->setCellValue('I3', $kd);
        }

        $xlsxWriter = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $xlsxWriter->save($filename);
    }

    public function setCostAnalysisCloud($report = null, $input)
    {

        $typeId   = $input['typeId'];
        $dc       = $input['dc'];
        $kd       = $input['kd'];
        $currency = !isset($input['currency']) ? 'Dólares' : $input['currency'];

        $kd = ($kd / 100);
        $kd = $kd > 0 ? $kd : 0;

        $company = DB::table('empresas')->where('deleted_at')->first();
        $onedriveDow = new OnedriveDow();
        $onedriveService = new OnedriveService();
        $onedriveService = $onedriveDow->handleToken($company->token_onedrive, $onedriveService);
        $cell = $onedriveService->setCell($report->aid, $this->sheetname_user, 3, 2, $currency, 'String');

        if ($typeId == 1) {
            $cell = $onedriveService->setCell($report->aid, $this->sheetname_wacc, 18, 5, $dc, 'Double');
            $cell = $onedriveService->setCell($report->aid, $this->sheetname_wacc, 32, 5, $kd, 'Double');
        } else {
            $cell = $onedriveService->setCell($report->aid, $this->sheetname_wacc, 18, 8, $dc, 'Double');
            $cell = $onedriveService->setCell($report->aid, $this->sheetname_wacc, 2, 8, $kd, 'Double');
        }
    }

    public function taxrate($filename, $input)
    {

        $year    = $input['year'];
        $country = $input['country'];

        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $reader->setLoadSheetsOnly($this->sheetname_contries);
        $spreadsheet = $reader->load($filename);

        $worksheet = $spreadsheet->getActiveSheet();
        $lastColumn = $worksheet->getHighestColumn();
        $lastRow = $worksheet->getHighestRow();
        $countryColumn = '';
        $countryRow = '';
        $taxrate = '';
        $lastColumn++;
        for ($column = 'B'; $column != $lastColumn; $column++) {
            $value = $worksheet->getCell($column . "1")->getValue();

            if ($value == $year) {
                $countryColumn = $column;
                break;
            }
        }

        for ($row = 2; $row <= $lastRow; $row++) {
            $value = $worksheet->getCell("A" . $row)->getValue();
            if ($value == $country) {
                $countryRow = $row;
                break;
            }
        }

        if ($countryColumn && $countryRow) {
            $taxrate = $worksheet->getCell($countryColumn . $countryRow)->getValue();
            $taxrate = FG::formatterx100val($taxrate);
        }
        return compact('taxrate');
    }

    public function getReportCloud($report = null, $company, $sheetname, $tablename)
    {

        $onedriveDow = new OnedriveDow();
        $onedriveService = new OnedriveService();
        $onedriveService = $onedriveDow->handleToken($company->token_onedrive, $onedriveService);

        $req = $onedriveService->getTableRows($report->eid, $sheetname, $tablename);
        $rows = $req['value'];
        $keys = [];
        foreach ($rows as $key => $row) {
            $value = $row['values'][0][0];
            if ($value) {
                $keys[$value][] = $row['values'][0];
            }
        }
        return $keys;
    }
}
