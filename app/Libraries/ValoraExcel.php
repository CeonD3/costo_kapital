<?php

namespace App\Libraries;

use Illuminate\Database\Capsule\Manager as DB;
use App\Utilitarian\FG;
use App\Utilitarian\ReadFilter;
use App\Traits\FinanceExcelTrait;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use App\Services\OnedriveService;
use App\Dows\OnedriveDow;
use Carbon\Carbon;

class ValoraExcel
{

    use FinanceExcelTrait;

    public function getForm($filename, $propietary = false)
    {

        $result = array(
            'dates'       => '',
            'date'        => '',
            'sectors'     => '',
            'sector'      => '',
            'countries'   => '',
            'country'     => '',
            'currencies'  => '',
            'currency'    => '',
            'action'      => ''
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
            $reader->setLoadSheetsOnly($this->sheetname_user);
            $spreadsheet = $reader->load($filename);
            $worksheet = $spreadsheet->getActiveSheet();
            $lastRow = $worksheet->getHighestRow();
            // $value = $worksheet->getCell('C2')->getValue();
            // $result['date'] = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value);
            /*$date = $worksheet->getCell('C2')->getFormattedValue();
            if ($date) {
                $datetime = new \DateTime($date);
                $date = $datetime->format('d/m/Y');
            }*/
            $result['date'] = $worksheet->getCell('C2')->getValue();
            $result['country'] = $worksheet->getCell('C3')->getValue();
            $result['currency'] = $worksheet->getCell('C4')->getValue();
            $result['action'] = $worksheet->getCell('C5')->getValue();

            $reader->setLoadSheetsOnly($this->sheetname_wacc);
            $spreadsheet = $reader->load($filename);
            $worksheet = $spreadsheet->getActiveSheet();
            $result['instrument'] = $worksheet->getCell('C3')->getValue();
            $result['sector'] = $worksheet->getCell('C13')->getValue();
            $result['bono'] = $worksheet->getCell('C6')->getValue();
        }
        return $result;
    }

    public function getFormCloud($filename, $propietary = false, $report = null)
    {

        // Si se pide comportamiento "propietary" se requiere $report
        if ($propietary && empty($report)) {
            throw new \InvalidArgumentException('El parámetro $report es obligatorio cuando $propietary es true');
        }

        // Obtener el último template activo desde la base de datos
        $latestTemplate = \Illuminate\Database\Capsule\Manager::table('templates')
            ->where('status', 1)
            ->where('version', '2')
            ->whereNull('deleted_at')
            ->orderBy('id', 'desc')
            ->first();

        if (!$latestTemplate) {
            throw new \Exception("No se encontró ningún template activo en la base de datos");
        }

        // Construir las rutas del archivo Excel y su archivo JSON de datos
        $masterTemplatePath = FG::getPathMaster($latestTemplate->file);
        $dataFileName = $this->getDataFileName($latestTemplate->file);
        $dataFilePath = FG::getPathMaster($dataFileName);

        if (!file_exists($masterTemplatePath)) {
            throw new \Exception("El archivo de plantilla no se encuentra en: " . $masterTemplatePath);
        }

        if (!file_exists($dataFilePath)) {
            throw new \Exception("El archivo de datos JSON no se encuentra en: " . $dataFilePath);
        }

        // Leer los datos desde el archivo JSON
        $jsonData = file_get_contents($dataFilePath);
        $templateData = json_decode($jsonData, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Error al decodificar el archivo JSON: " . json_last_error_msg());
        }

        $result = [
            'dates' => $templateData['dates'] ?? [],
            'date' => '',
            'sectors' => $templateData['sectors'] ?? [],
            'sector' => '',
            'countries' => $templateData['countries'] ?? [],
            'country' => '',
            'currencies' => $templateData['currencies'] ?? [],
            'currency' => '',
            'action' => ''
        ];

        if ($propietary) {
            $company = DB::table('empresas')->whereNull('deleted_at')->first();

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
                    case 7:
                        $result['currency'] = $value;
                        break;
                    case 11:
                        $result['action'] = $value;
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
        $currency   = $input['currency'];
        $country    = $input['country'];
        $action     = $input['action'];

        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();

        $spreadsheet = $reader->load($filename);

        $worksheet = $spreadsheet->getSheetByName($this->sheetname_user);
        if ($date) {
            $worksheet->setCellValue('C2', $date);
        }
        if ($country) {
            $worksheet->setCellValue('C3', $country);
        }
        if ($currency) {
            $worksheet->setCellValue('C4', $currency);
        }
        if ($action) {
            $worksheet->setCellValue('C5', $action);
        }

        $xlsxWriter = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $xlsxWriter->save($filename);
    }

    public function setFormCloud($report, $input)
    {

        $date       = $input['date'];
        $sector     = $input['sector'];
        $currency   = $input['currency'];
        $country    = $input['country'];
        $action     = $input['action'];

        $company = DB::table('empresas')->where('deleted_at')->first();
        $onedriveDow = new OnedriveDow();
        $onedriveService = new OnedriveService();
        $onedriveService = $onedriveDow->handleToken($company->token_onedrive, $onedriveService);
        $valore = [];
        if ($date) {
            $dateValue = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($date);
            // $cell = $onedriveService->setCell($report->eid, $this->sheetname_user, 1, 2, $dateValue, 'Double');
        }
        $valores[0] = [$dateValue ?? ""];
        if ($country) {
            // $cell = $onedriveService->setCell($report->eid, $this->sheetname_user, 2, 2, $country, 'String');
        }
        $valores[1] = [$country ?? ""];
        if ($currency) {
            // $cell = $onedriveService->setCell($report->eid, $this->sheetname_user, 3, 2, $currency, 'String');
        }
        $valores[2] = [$currency ?? ""];
        // $cell = $onedriveService->setCell($report->eid, $this->sheetname_user, 4, 2, ($action ?? 0), 'Double');
        $valores[3] = [$action ?? ""];

        $rango = "C2:C5";
        $response = $onedriveService->setRangeCell($report->eid, $this->sheetname_user, $rango, $valores);

        if ($sector) {
            $cell = $onedriveService->setCell($report->eid, $this->sheetname_wacc, 12, 2, $sector, 'String');
        }
    }

    public function setFormUsername($filename, $fileUsername)
    {

        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();

        $spreadsheet = $reader->setLoadSheetsOnly($this->sheetname_user);
        $reader->setReadDataOnly(true);
        $spreadsheet = $spreadsheet->load($fileUsername);
        $worksheet = $spreadsheet->getActiveSheet();
        $result = $this->f_readTableBalance($worksheet);

        $firstAge = $worksheet->getCell('C7')->getValue();
        $lastAge = $worksheet->getCell('C8')->getValue();

        $generales = $result['generales'];
        $resultados = $result['resultados'];

        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $spreadsheet = $reader->load($filename);
        $worksheet = $spreadsheet->getSheetByName($this->sheetname_user);

        $worksheet->setCellValue('C7', $firstAge);
        $worksheet->setCellValue('C8', $lastAge);

        $xlsxWriter = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $xlsxWriter->save($filename);

        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $spreadsheet = $reader->load($filename);
        $worksheet = $spreadsheet->getSheetByName($this->sheetname_user);

        $column = "B";
        $row = 10;

        $lastColumn = $worksheet->getHighestColumn();
        // Cleaner header
        /*for ($row1 = 10; $row1 <= 10; $row1++) {
            for ($column1 = 'B'; $column1 != $lastColumn; $column1++) {
                $worksheet->setCellValue($column1 . $row1, '');
            }
        }*/

        /*foreach ($generales['header'] as $k => $value) {
            $worksheet->setCellValue($column . $row, $value);
            $column ++;
        }*/

        foreach ($generales['body'] as $k => $values) {
            $row++;
            $column = "B";
            // Cleaner body
            for ($column1 = 'B'; $column1 != $lastColumn; $column1++) {
                $worksheet->setCellValue($column1 . $row, '');
            }
            // new data
            foreach ($values as $k2 => $value) {
                $worksheet->setCellValue($column . $row, $value);
                $column++;
            }
        }

        $row = $row + 3;
        $column = "B";

        // Cleaner
        /*for ($row1 = $row; $row1 <= $row; $row1++) {
            for ($column1 = 'B'; $column1 != $lastColumn; $column1++) {
                $worksheet->setCellValue($column1 . $row, '');
            }
        }*/

        /*foreach ($resultados['header'] as $k => $value) {
            $worksheet->setCellValue($column . $row, $value);
            $column ++;
        }*/

        foreach ($resultados['body'] as $k => $values) {
            $row++;
            $column = "B";
            // Cleaner body
            for ($column1 = 'B'; $column1 != $lastColumn; $column1++) {
                $worksheet->setCellValue($column1 . $row, '');
            }
            // new data
            foreach ($values as $k2 => $value) {
                $worksheet->setCellValue($column . $row, $value);
                $column++;
            }
        }

        $xlsxWriter = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $xlsxWriter->save($filename);
    }

    public function setFormUsernameCloud($report, $fileUsername)
    {
        // volver a descargar la plantilla master de kapital
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();

        $spreadsheet = $reader->setLoadSheetsOnly($this->sheetname_user);
        $reader->setReadDataOnly(true);
        $spreadsheet = $spreadsheet->load($fileUsername);
        $worksheet = $spreadsheet->getActiveSheet();
        $result = $this->f_readTableBalance($worksheet);

        $firstYear = $worksheet->getCell('C7')->getValue();
        $lastYear = $worksheet->getCell('C8')->getValue();

        $result['history'] = [
            'first' => $firstYear,
            'last'  => $lastYear
        ];

        $generales = $result['generales'];
        $resultados = $result['resultados'];

        $company = DB::table('empresas')->where('deleted_at')->first();

        $onedriveDow = new OnedriveDow();
        $onedriveService = new OnedriveService();
        $onedriveService = $onedriveDow->handleToken($company->token_onedrive, $onedriveService);

        // $cell = $onedriveService->setCell($report->eid, $this->sheetname_user, 6, 2, $firstYear, 'Double');
        // $cell = $onedriveService->setCell($report->eid, $this->sheetname_user, 7, 2, $lastYear, 'Double');
        $rango = "C7:C8";
        $valore = [];
        $valores[0] = [$firstYear];
        $valores[1] = [$lastYear];
        $response = $onedriveService->setRangeCell($report->eid, $this->sheetname_user, $rango, $valores);


        $firstDate = Carbon::parse("$firstYear-01-01");
        $lastDate = Carbon::parse("$lastYear-01-01");
        $diffInYear = $firstDate->diffInYears($lastDate);
        $keys = [];
        foreach ($generales['body'] as $k => $values) {
            $columnIndex = 0;
            foreach ($values as $k2 => $value) {
                if ($k2 > 0) {
                    if ($diffInYear >= $columnIndex) {
                        $keys[$columnIndex][] = $value;
                        // $cell = $onedriveService->setCell($report->eid, $this->sheetname_user, (10 + $k), (2 + $columnIndex), $value, 'Double');
                    } else {
                        break;
                    }
                    $columnIndex++;
                }
            }
        }

        if (count($keys)) {
            $valores = [];
            foreach ($keys as $k => $values) {
                $valores[0][] = implode(";", $values);
            }
            $keys_columns = FG::obtenerRangoDeLetras("C", count($keys));
            $rango = $keys_columns[0] . "9:" . $keys_columns[count($keys_columns) - 1] . "9";
            $response = $onedriveService->setRangeCell($report->eid, $this->sheetname_user, $rango, $valores);
        }

        $keys = [];
        foreach ($resultados['body'] as $k => $values) {
            $columnIndex = 0;
            foreach ($values as $k2 => $value) {
                if ($k2 > 0) {
                    if ($diffInYear >= $columnIndex) {
                        $keys[$columnIndex][] = $value;
                        // $cell = $onedriveService->setCell($report->eid, $this->sheetname_user, (41 + $k), (2 + $columnIndex), $value, 'Double');
                    } else {
                        break;
                    }
                    $columnIndex++;
                }
            }
        }

        if (count($keys)) {
            $valores = [];
            foreach ($keys as $k => $values) {
                $valores[0][] = implode(";", $values);
            }
            $keys_columns = FG::obtenerRangoDeLetras("C", count($keys));
            $rango = $keys_columns[0] . "40:" . $keys_columns[count($keys_columns) - 1] . "40";
            $response = $onedriveService->setRangeCell($report->eid, $this->sheetname_user, $rango, $valores);
        }

        return $result;
    }

    public function balance($filename)
    {

        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();

        $reader->setLoadSheetsOnly($this->sheetname_user);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($filename);
        $worksheet = $spreadsheet->getActiveSheet();

        $result = $this->f_readTableBalance($worksheet);
        $generales = $result['generales'];
        $resultados = $result['resultados'];

        foreach ($generales['body'] as $k => $values) {
            foreach ($values as $k2 => $value) {
                if ($k2 > 0) {
                    $value = FG::numberFormat($value);
                }
                $title = "";
                if (in_array($k, [21, 12])) {
                    $title = 1;
                }
                $result['generales']['body'][$k][$k2] = [
                    'value' => $value,
                    'title' => $title
                ];
            }
        }

        foreach ($resultados['body'] as $k => $values) {
            foreach ($values as $k2 => $value) {
                if ($k2 > 0) {
                    $value = FG::numberFormat($value);
                }
                $title = "";
                if (in_array($k, [13, 11, 7, 2])) {
                    $title = 1;
                }
                $result['resultados']['body'][$k][$k2] = [
                    'value' => $value,
                    'title' => $title
                ];
            }
        }

        return $result;

        /* $lastRow = $worksheet->getHighestRow();
        $lastColumn = $worksheet->getHighestColumn();

        $generales = array(); $resultados = array();

        $header = array(); $lastColumnTable = "";
        for ($row = 10; $row <= 10; $row++) {
            for ($column = 'B'; $column != $lastColumn; $column++) {
                $value = $worksheet->getCell($column . $row)->getCalculatedValue();
                if (!$value) {
                    break;
                }
                $header[] =  $value;
                $lastColumnTable = $column;
            }            
        }
        $lastColumnTable++;
        $body = array(); $lastRowTable = "";
        for ($row = 11; $row <= $lastRow; $row++) {
            $values = [];
            for ($column = 'B'; $column != $lastColumnTable; $column++) {
                $value = $worksheet->getCell($column . $row)->getCalculatedValue();
                if (!$value && $column == 'B') {
                    break;
                }
                $values[] = $value ?? '';
            }
            if (count($values) == 0) {
                break;
            }
            $body[] = $values;
            $lastRowTable = $row;
        }

        $generales = compact('header', 'body');

        $startRowTable = $lastRowTable + 3;
        $header = array(); $lastColumnTable = "";
        for ($row = $startRowTable; $row <= $startRowTable; $row++) {
            for ($column = 'B'; $column != $lastColumn; $column++) {
                $value = $worksheet->getCell($column . $row)->getCalculatedValue();
                if (!$value) {
                    break;
                }
                $header[] =  $value;
                $lastColumnTable = $column;
            }            
        }
        $lastColumnTable++;
        $body = array(); $lastRowTable = ""; $startRowTable++;
        for ($row = $startRowTable; $row <= $lastRow; $row++) {
            $values = [];
            for ($column = 'B'; $column != $lastColumnTable; $column++) {
                $value = $worksheet->getCell($column . $row)->getCalculatedValue();
                if (!$value && $column == 'B') {
                    break;
                }
                $values[] = $value ?? '';
            }
            if (count($values) == 0) {
                break;
            }
            $body[] = $values;
        }
        $resultados = compact('header', 'body');*/

        /*for ($row = 10; $row <= $lastRow; $row++) {
            
            for ($column = 'B'; $column != $lastColumn; $column++) {
                $value = $worksheet->getCell($column . $row)->getCalculatedValue();
                if (!$value) {
                    if ($column == 'B') {
                        $tabla = 2;
                        $index = 0;    
                    }
                    break;
                }
                $cell = [
                    'value' => $value,
                    'backgroundColor' => $worksheet->getStyle($column . $row)->getFill()->getStartColor()->getARGB(),
                    'font' => $worksheet->getStyle($column . $row)->getFont()->getColor()
                ];

                if ($tabla == 1){
                    $generales[$index][] = $cell;
                } else if ($tabla == 2) {
                    $resultados[$index][] = $cell;
                }
            }
            $index ++;
        }
        return compact('generales', 'resultados');*/
    }

    public function balanceData($report)
    {

        $result = json_decode($report->file_username_data, true);
        $generales = $result['generales'];
        $resultados = $result['resultados'];

        foreach ($generales['body'] as $k => $values) {
            foreach ($values as $k2 => $value) {
                if ($k2 > 0) {
                    $value = FG::numberFormat($value, 0);
                }
                $title = "";
                if (in_array($k, [27, 21, 12])) {
                    $title = 1;
                }
                $result['generales']['body'][$k][$k2] = [
                    'value' => $value,
                    'title' => $title
                ];
            }
        }

        foreach ($resultados['body'] as $k => $values) {
            foreach ($values as $k2 => $value) {
                if ($k2 > 0) {
                    $value = FG::numberFormat($value, 0);
                }
                $title = "";
                if (in_array($k, [13, 11, 7, 2])) {
                    $title = 1;
                }
                $result['resultados']['body'][$k][$k2] = [
                    'value' => $value,
                    'title' => $title
                ];
            }
        }

        return $result;
    }

    public function result($filename)
    {

        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();

        $reader->setLoadSheetsOnly($this->sheetname_user);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($filename);
        $worksheet = $spreadsheet->getActiveSheet();

        $result = $this->f_readTableBalance($worksheet);

        $generales = $result['generales']['body'];

        $balance = [
            'patrimony' => $generales[27][count($generales[27]) - 1],
            'passive'   => $generales[21][count($generales[21]) - 1],
            'active'    => $generales[12][count($generales[12]) - 1],
            'patrimonyText' => FG::numberformat($generales[27][count($generales[27]) - 1]),
            'passiveText'   => FG::numberformat($generales[21][count($generales[21]) - 1]),
            'activeText'    => FG::numberformat($generales[12][count($generales[12]) - 1])
        ];

        $reader->setLoadSheetsOnly($this->sheetname_concept);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($filename);
        $worksheet = $spreadsheet->getActiveSheet();

        $concept = [
            'company'   => FG::numberformat($worksheet->getCell('L195')->getOldCalculatedValue()),
            'patrimony' => FG::numberformat($worksheet->getCell('L197')->getOldCalculatedValue()),
            'action'    => FG::numberformat($worksheet->getCell('L198')->getOldCalculatedValue()),
            'balance'   => $balance
        ];

        $reader->setLoadSheetsOnly($this->sheetname_integrated);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($filename);
        $worksheet = $spreadsheet->getActiveSheet();

        $integrated = [
            'company'   => FG::numberformat($worksheet->getCell('L49')->getOldCalculatedValue()),
            'patrimony' => FG::numberformat($worksheet->getCell('L51')->getOldCalculatedValue()),
            'action'    => FG::numberformat($worksheet->getCell('L52')->getOldCalculatedValue()),
            'balance'   => $balance
        ];

        return compact('concept', 'integrated');
    }

    public function getResultCloud($report)
    {

        $concept = [];
        $integrated = [];
        $balance = [];
        $firstAge = 0;
        $lastAge = 0;

        $company = DB::table('empresas')->where('deleted_at')->first();

        $onedriveDow = new OnedriveDow();
        $onedriveService = new OnedriveService();
        $onedriveService = $onedriveDow->handleToken($company->token_onedrive, $onedriveService);

        $result = $onedriveService->getTableRows($report->eid, $this->sheetname_user, 'Tabla4');
        $rows = $result['value'];

        $cfinancial = 0;
        $ifinancial = 0;
        foreach ($rows as $key => $row) {
            $value = FG::numberformat($row['values'][0][1], 0);
            switch ($row['index']) {
                case 0:
                    $concept['company'] = $value;
                    break;
                case 1:
                    $concept['patrimony'] = $value;
                    $cfinancial = $row['values'][0][1];
                    break;
                case 2:
                    $concept['action'] = FG::numberformat($row['values'][0][1]);
                    break; ///
                case 3:
                    $integrated['company'] = $value;
                    break;
                case 4:
                    $integrated['patrimony'] = $value;
                    $ifinancial = $row['values'][0][1];
                    break;
                case 5:
                    $integrated['action'] = FG::numberformat($row['values'][0][1]);
                    break;
                case 6:
                    $firstAge = $row['values'][0][1];
                    break;
                case 7:
                    $lastAge = $row['values'][0][1];
                    break;
                case 8:
                    // $cfinancial = $row['values'][0][1]; 
                    break;
                case 9:
                    // $ifinancial = $row['values'][0][1]; 
                    break;
                default:
                    # code...
                    break;
            }
        }
        if ($firstAge > 0 && $lastAge > 0) {
            $firstDate = Carbon::parse("$firstAge-01-01");
            $lastDate = Carbon::parse("$lastAge-01-01");
            $diffInYear = $firstDate->diffInYears($lastDate);
            $cell = $onedriveService->getCell($report->eid, $this->sheetname_user, 22, (2 + $diffInYear));
            $active = $cell['values'][0][0];
            $cell = $onedriveService->getCell($report->eid, $this->sheetname_user, 31, (2 + $diffInYear));
            $passive = $cell['values'][0][0];
            $cell = $onedriveService->getCell($report->eid, $this->sheetname_user, 36, (2 + $diffInYear));
            $patrimony = $cell['values'][0][0];

            $balance['active'] = $active;
            $balance['passive'] = $passive;
            $balance['patrimony'] = $patrimony;
            $balance['activeText'] = FG::numberformat($active, 0);
            $balance['passiveText'] = FG::numberformat($passive, 0);
            $balance['patrimonyText'] = FG::numberformat($patrimony, 0);

            $concept['financial'] = $cfinancial;
            $concept['financialText'] = FG::numberformat($cfinancial, 0);
            $integrated['financial'] = $ifinancial;
            $integrated['financialText'] = FG::numberformat($ifinancial, 0);

            $concept['balance'] = $balance;
            $integrated['balance'] = $balance;
        }

        return compact('concept', 'integrated');
    }

    public function getAnalysisCloud($report)
    {

        $concept = [];
        $integrated = [];
        $balance = [];
        $firstAge = 0;
        $lastAge = 0;
        $general = [];

        $company = DB::table('empresas')->where('deleted_at')->first();

        $onedriveDow = new OnedriveDow();
        $onedriveService = new OnedriveService();
        $onedriveService = $onedriveDow->handleToken($company->token_onedrive, $onedriveService);

        $result = $onedriveService->getTableRows($report->eid, $this->sheetname_user, 'Tabla4');
        $rows = $result['value'];
        $csensitized = 0;
        $isensitized = 0;
        $cfinancial = 0;
        $ifinancial = 0;
        foreach ($rows as $key => $row) {
            $value = FG::numberformat($row['values'][0][1], 0);
            switch ($row['index']) {
                case 0:
                    $concept['company'] = $value;
                    break;
                case 15:
                    $concept['patrimony'] = $value;
                    $csensitized = $row['values'][0][1];
                    break;
                case 16:
                    $concept['action'] = FG::numberformat($row['values'][0][1]);
                    break; ///
                case 3:
                    $integrated['company'] = $value;
                    break;
                case 17:
                    $integrated['patrimony'] = $value;
                    $isensitized = $row['values'][0][1];
                    break;
                case 18:
                    $integrated['action'] = FG::numberformat($row['values'][0][1]);
                    break;
                case 6:
                    $firstAge = $row['values'][0][1];
                    break;
                case 7:
                    $lastAge = $row['values'][0][1];
                    break;
                case 8:
                    $cfinancial = $row['values'][0][1];
                    break;
                case 9:
                    $ifinancial = $row['values'][0][1];
                    break;
                case 10:
                    // $csensitized = $row['values'][0][1]; 
                    break;
                case 11:
                    // $isensitized = $row['values'][0][1]; 
                    break;
                case 12:
                    $general['longgrowth'] = FG::formatterx100val($row['values'][0][1]);
                    break;
                case 13:
                    $general['capitalcost'] = FG::formatterx100val($row['values'][0][1]);
                    break;
                case 14:
                    $general['revenuegrowth'] = FG::formatterx100val($row['values'][0][1]);
                    break;
                default:
                    # code...
                    break;
            }
        }
        if ($firstAge > 0 && $lastAge > 0) {
            $firstDate = Carbon::parse("$firstAge-01-01");
            $lastDate = Carbon::parse("$lastAge-01-01");
            $diffInYear = $firstDate->diffInYears($lastDate);
            $cell = $onedriveService->getCell($report->eid, $this->sheetname_user, 22, (2 + $diffInYear));
            $active = $cell['values'][0][0];
            $cell = $onedriveService->getCell($report->eid, $this->sheetname_user, 31, (2 + $diffInYear));
            $passive = $cell['values'][0][0];
            $cell = $onedriveService->getCell($report->eid, $this->sheetname_user, 36, (2 + $diffInYear));
            $patrimony = $cell['values'][0][0];

            $balance['active'] = $active;
            $balance['passive'] = $passive;
            $balance['patrimony'] = $patrimony;
            $balance['activeText'] = FG::numberformat($active, 0);
            $balance['passiveText'] = FG::numberformat($passive, 0);
            $balance['patrimonyText'] = FG::numberformat($patrimony, 0);

            $concept['financial'] = $cfinancial;
            $concept['financialText'] = FG::numberformat($cfinancial, 0);
            $integrated['financial'] = $ifinancial;
            $integrated['financialText'] = FG::numberformat($ifinancial, 0);

            $concept['sensitized'] = $csensitized;
            $concept['sensitizedText'] = FG::numberformat($csensitized, 0);
            $integrated['sensitized'] = $isensitized;
            $integrated['sensitizedText'] = FG::numberformat($isensitized, 0);

            $concept['balance'] = $balance;
            $integrated['balance'] = $balance;
        }

        return compact('concept', 'integrated', 'general');
    }

    public function analysis($filename)
    {

        $reader = new Xlsx();

        $reader->setLoadSheetsOnly($this->sheetname_wacc);
        $spreadsheet = $reader->load($filename);

        $worksheet = $spreadsheet->getActiveSheet();

        return [
            'dc'   => FG::formatterx100val($worksheet->getCell('I19')->getOldCalculatedValue()),
            'sector' => [
                'cppc' => FG::formatterx100val($worksheet->getCell('F37')->getOldCalculatedValue()),
                'kd'   => FG::formatterx100val($worksheet->getCell('F34')->getOldCalculatedValue()),
                'ke'   => FG::formatterx100val($worksheet->getCell('F24')->getOldCalculatedValue()),
                'koa'  => FG::formatterx100val($worksheet->getCell('F28')->getOldCalculatedValue())
            ],
            'company' => [
                'cppc' => FG::formatterx100val($worksheet->getCell('I44')->getOldCalculatedValue()),
                'usd' => [
                    'kd'   => FG::formatterx100val($worksheet->getCell('I34')->getOldCalculatedValue()),
                    'ke'   => FG::formatterx100val($worksheet->getCell('I24')->getOldCalculatedValue()),
                    'koa'  => FG::formatterx100val($worksheet->getCell('I28')->getOldCalculatedValue())
                ],
                'pen' => [
                    'kd'   => FG::formatterx100val($worksheet->getCell('J34')->getOldCalculatedValue()),
                    'ke'   => FG::formatterx100val($worksheet->getCell('J24')->getOldCalculatedValue()),
                    'koa'  => FG::formatterx100val($worksheet->getCell('J28')->getOldCalculatedValue())
                ]
            ],
        ];
    }

    public function setCostAnalysisCloud($report, $input)
    {
        $longgrowth    = $input['longgrowth'];
        $capitalcost   = $input['capitalcost'];
        $revenuegrowth = $input['revenuegrowth'];

        $longgrowth = ($longgrowth / 100);
        $longgrowth = $longgrowth > 0 ? $longgrowth : 0;

        $capitalcost = ($capitalcost / 100);
        $capitalcost = $capitalcost > 0 ? $capitalcost : 0;

        $revenuegrowth = ($revenuegrowth / 100);
        $revenuegrowth = $revenuegrowth > 0 ? $revenuegrowth : 0;

        $company = DB::table('empresas')->where('deleted_at')->first();
        $onedriveDow = new OnedriveDow();
        $onedriveService = new OnedriveService();
        $onedriveService = $onedriveDow->handleToken($company->token_onedrive, $onedriveService);
        $cell = $onedriveService->setCell($report->eid, $this->sheetname_Sensitivity, 13, 6, $longgrowth, 'Double');
        $cell = $onedriveService->setCell($report->eid, $this->sheetname_Sensitivity, 99, 12, $capitalcost, 'Double');
        $cell = $onedriveService->setCell($report->eid, $this->sheetname_Sensitivity, 9, 3, $revenuegrowth, 'Double');
    }

    public function getReportCloud($report)
    {

        $company = DB::table('empresas')->where('deleted_at')->first();

        $onedriveDow = new OnedriveDow();
        $onedriveService = new OnedriveService();
        $onedriveService = $onedriveDow->handleToken($company->token_onedrive, $onedriveService);

        $req = $onedriveService->getTableRows($report->eid, $this->sheetname_user, 'Tabla6');
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

    public function BVL($filename)
    {

        $companies = [];

        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();

        $reader->setLoadSheetsOnly($this->sheetname_BVL);
        $spreadsheet = $reader->load($filename);
        $worksheet = $spreadsheet->getActiveSheet();
        $lastRow = $worksheet->getHighestRow();
        $lastColumn = $worksheet->getHighestColumn();
        $lastColumn++;
        for ($row = 2; $row <= $lastRow; $row++) {
            $company = [];
            for ($column = 'A'; $column != $lastColumn; $column++) {
                $value = $worksheet->getCell($column . $row)->getCalculatedValue();
                $company[] = is_numeric($value) ? FG::numberformat($value) : $value;
            }
            $companies[] = $company;
        }

        return compact('companies');
    }

    private function getDataFileName($templateFile)
    {
        $pathInfo = pathinfo($templateFile);
        $dirname = $pathInfo['dirname'] === '.' ? '' : $pathInfo['dirname'] . '/';
        return $dirname . $pathInfo['filename'] . '-data.json';
    }
}
