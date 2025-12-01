<?php

namespace App\Libraries;

use Illuminate\Database\Capsule\Manager as DB;
use App\Utilitarian\FG;
use App\Traits\FinanceExcelTrait;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Services\OnedriveService;
use App\Dows\OnedriveDow;
use App\Model\Template;

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
            'ekd'         => '',
            'financial_data' => array(
                'dc_ratio' => '',
                'effective_tax_rate' => '',
                'beta_levered' => ''
            )
        );

        // Cargar listas desde el archivo JSON de datos (sin leer el Excel para estas listas)
        $pathInfo = pathinfo($filename);
        $dataFilePath = $pathInfo['dirname'] . DIRECTORY_SEPARATOR . $pathInfo['filename'] . '-data.json';
        if (file_exists($dataFilePath)) {
            $jsonData = file_get_contents($dataFilePath);
            $templateData = json_decode($jsonData, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($templateData)) {
                $result['sectors']     = $templateData['sectors'] ?? [];
                $result['instruments'] = $templateData['instruments'] ?? [];
                $result['dates']       = $templateData['dates'] ?? [];
                $result['bonos']       = $templateData['bonos'] ?? [];
                $result['currencies']  = $templateData['currencies'] ?? [];
                $result['countries']   = $templateData['countries'] ?? [];
            }
        }

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

            // Leer datos financieros adicionales de celdas específicas
            try {
                $dcRatioCell = $onedriveService->getCell($report->eid, 'WACC', 18, 2);
                $result['financial_data']['dc_ratio'] = FG::formatterx100val($dcRatioCell['value']);
            } catch (\Exception $e) {
                $result['financial_data']['dc_ratio'] = '';
            }

            try {
                $taxRateCell = $onedriveService->getCell($report->eid, 'WACC', 19, 2);
                $result['financial_data']['effective_tax_rate'] = FG::formatterx100val($taxRateCell['value']);
            } catch (\Exception $e) {
                $result['financial_data']['effective_tax_rate'] = '';
            }

            try {
                $betaCell = $onedriveService->getCell($report->eid, 'WACC', 20, 2);
                $result['financial_data']['beta_levered'] = $betaCell['value'];
            } catch (\Exception $e) {
                $result['financial_data']['beta_levered'] = '';
            }
        }
        return $result;
    }

    /**
     * Obtiene la data del formulario usando el archivo WACC optimizado cuando está disponible
     * @param string $filename Archivo de plantilla principal
     * @param string $brand
     * @param boolean $propietary
     * @param object $report
     * @return array
     */
    public function getFormCloudOptimized($filename, $brand, $propietary = false, $report = null)
    {
        $template = new Template();

        // Verificar si existe el archivo WACC optimizado
        $waccFilePath = $template->getWaccFilePath($filename);
        $useWaccFile = $template->hasWaccFile($filename);

        if (!$propietary) {
            // Para obtener solo las listas, usar el archivo JSON es más eficiente
            return $this->getFormCloud($filename, $brand, $propietary, $report);
        } else if ($useWaccFile && $propietary) {
            // ✅ USAR ARCHIVO WACC LOCAL - SIN ONEDRIVE
            error_log("Using local WACC file for form data: " . $waccFilePath);

            // Obtener listas desde JSON (más eficiente)
            $result = $this->getFormCloud($filename, $brand, false, null);

            // Leer datos específicos del reporte desde archivo WACC local
            $result = $this->getFormDataFromWaccFile($waccFilePath, $result);

            return $result;
        } else {
            // Fallback al método original si no hay archivo WACC
            return $this->getFormCloud($filename, $brand, $propietary, $report);
        }
    }

    /**
     * Lee datos del formulario directamente desde el archivo WACC local
     * @param string $waccFilePath Ruta al archivo WACC
     * @param array $result Array de resultado base
     * @return array Datos del formulario
     */
    private function getFormDataFromWaccFile($waccFilePath, $result)
    {
        try {
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            $reader->setLoadSheetsOnly([$this->sheetname_wacc, $this->sheetname_user]);
            $spreadsheet = $reader->load($waccFilePath);

            // Leer datos de la hoja User si existe
            try {
                $userSheet = $spreadsheet->getSheetByName($this->sheetname_user);
                if ($userSheet) {
                    $date = $userSheet->getCell('C2')->getFormattedValue();
                    if ($date) {
                        $datearr = explode('/', $date);
                        $date = FG::addZeroDecimal($datearr[1]) . '/' . FG::addZeroDecimal($datearr[0]) . '/' . $datearr[2];
                    }
                    $result['date'] = $date;
                    $result['country'] = $userSheet->getCell('C3')->getValue();
                    $result['currency'] = $userSheet->getCell('C4')->getValue();
                }
            } catch (\Exception $e) {
                error_log("Error reading User sheet from WACC file: " . $e->getMessage());
            }

            // Leer datos de la hoja WACC
            $worksheet = $spreadsheet->getSheetByName($this->sheetname_wacc);
            if (!$worksheet) {
                throw new \Exception('WACC sheet not found in file');
            }

            // Leer datos específicos del archivo WACC local
            $result['instrument'] = $worksheet->getCell('C3')->getValue();
            $result['sector'] = $worksheet->getCell('C13')->getValue();
            $result['bono'] = $worksheet->getCell('C6')->getValue();

            // Leer datos calculados
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

            $ekd = $worksheet->getCell('I3')->getOldCalculatedValue();
            if (!$ekd) {
                $ekd = FG::formatterx100val($worksheet->getCell('I3')->getValue());
            } else {
                $ekd = FG::formatterx100val($ekd);
            }
            $result['ekd'] = $ekd;

            // Leer datos financieros adicionales directamente del WACC
            try {
                $dcRatioValue = $worksheet->getCell('C19')->getValue();
                $result['financial_data']['dc_ratio'] = FG::formatterx100val($dcRatioValue);
            } catch (\Exception $e) {
                $result['financial_data']['dc_ratio'] = '';
            }

            try {
                $taxRateValue = $worksheet->getCell('C20')->getValue();
                $result['financial_data']['effective_tax_rate'] = FG::formatterx100val($taxRateValue);
            } catch (\Exception $e) {
                $result['financial_data']['effective_tax_rate'] = '';
            }

            try {
                $betaValue = $worksheet->getCell('C21')->getValue();
                $result['financial_data']['beta_levered'] = $betaValue;
            } catch (\Exception $e) {
                $result['financial_data']['beta_levered'] = '';
            }

            error_log("Successfully read form data from local WACC file");
            return $result;
        } catch (\Exception $e) {
            error_log("Error reading WACC file: " . $e->getMessage());
            // En caso de error, devolver el resultado base sin datos específicos
            return $result;
        }
    }



    /**
     * Obtiene la ruta del archivo WACC optimizado si existe, sino devuelve el archivo principal
     * @param string $filename Archivo de plantilla principal
     * @return string Ruta al archivo a usar
     */
    public function getOptimizedFilePath($filename)
    {
        $template = new Template();

        if ($template->hasWaccFile($filename)) {
            return $template->getWaccFilePath($filename);
        }

        return $filename;
    }

    /**
     * Verifica si se puede usar el archivo WACC optimizado para una operación
     * @param string $filename Archivo de plantilla principal
     * @param string $operation Tipo de operación ('analysis', 'copy', 'read')
     * @return boolean
     */
    public function canUseWaccFile($filename, $operation = 'analysis')
    {
        $template = new Template();

        if (!$template->hasWaccFile($filename)) {
            return false;
        }

        // Para análisis y operaciones WACC, siempre podemos usar el archivo optimizado
        if (in_array($operation, ['analysis', 'wacc', 'copy'])) {
            return true;
        }

        return false;
    }

    /**
     * Obtiene información sobre la optimización disponible para un archivo
     * @param string $filename Archivo de plantilla principal
     * @return array Información sobre optimización disponible
     */
    public function getOptimizationInfo($filename)
    {
        $template = new Template();

        return [
            'has_wacc_file' => $template->hasWaccFile($filename),
            'has_data_file' => $template->hasDataFile($filename),
            'wacc_file_path' => $template->hasWaccFile($filename) ? $template->getWaccFilePath($filename) : null,
            'data_file_path' => $template->hasDataFile($filename) ? $template->getDataFilePath($filename) : null,
            'original_file_size' => file_exists($filename) ? filesize($filename) : 0,
            'wacc_file_size' => $template->hasWaccFile($filename) ? filesize($template->getWaccFilePath($filename)) : 0
        ];
    }

    /**
     * Obtiene los datos del formulario desde un archivo Excel local
     * @param string $localFilePath Ruta al archivo Excel local
     * @param object $template Información del template
     * @return array Datos del formulario
     */
    public function getFormDataFromLocalFile($localFilePath, $template)
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
            'ekd'         => '',
            'financial_data' => array(
                'dc_ratio' => '',
                'effective_tax_rate' => '',
                'beta_levered' => ''
            )
        );

        try {
            error_log("Reading form data from local file: " . $localFilePath);

            // Primero obtener las listas desde JSON si existe
            $pathInfo = pathinfo($localFilePath);
            $masterDir = dirname(dirname($localFilePath)); // Subir dos niveles para llegar a template/
            $templateMaster = DB::table('templates')->where('deleted_at')->where('status', 1)->where('version', 2)->first();
            if ($templateMaster) {
                $masterFile = FG::getPathMaster($templateMaster->file);
                $dataFilePath = dirname($masterFile) . DIRECTORY_SEPARATOR . pathinfo($masterFile, PATHINFO_FILENAME) . '-data.json';

                if (file_exists($dataFilePath)) {
                    $jsonData = file_get_contents($dataFilePath);
                    $templateData = json_decode($jsonData, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($templateData)) {
                        $result['sectors']     = $templateData['sectors'] ?? [];
                        $result['instruments'] = $templateData['instruments'] ?? [];
                        $result['dates']       = $templateData['dates'] ?? [];
                        $result['bonos']       = $templateData['bonos'] ?? [];
                        $result['currencies']  = $templateData['currencies'] ?? [];
                        $result['countries']   = $templateData['countries'] ?? [];
                    }
                }
            }

            // Leer datos específicos del archivo local
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            $reader->setLoadSheetsOnly([$this->sheetname_user, $this->sheetname_wacc]);
            $spreadsheet = $reader->load($localFilePath);

            // Leer datos de la hoja User
            try {
                $userSheet = $spreadsheet->getSheetByName($this->sheetname_user);
                if ($userSheet) {
                    $date = $userSheet->getCell('C2')->getFormattedValue();
                    if ($date) {
                        $datearr = explode('/', $date);
                        $date = FG::addZeroDecimal($datearr[1]) . '/' . FG::addZeroDecimal($datearr[0]) . '/' . $datearr[2];
                    }
                    $result['date'] = $date;
                    $result['country'] = $userSheet->getCell('C3')->getValue();
                    $result['currency'] = $userSheet->getCell('C4')->getValue();
                }
            } catch (\Exception $e) {
                error_log("Error reading User sheet: " . $e->getMessage());
            }

            // Leer datos de la hoja WACC
            $waccSheet = $spreadsheet->getSheetByName($this->sheetname_wacc);
            if ($waccSheet) {
                $result['instrument'] = $waccSheet->getCell('C3')->getValue();
                $result['sector'] = $waccSheet->getCell('C13')->getValue();
                $result['bono'] = $waccSheet->getCell('C6')->getValue();

                // Leer valores calculados
                $result['epd'] = $waccSheet->getCell('I17')->getOldCalculatedValue();
                if (!$result['epd']) {
                    $result['epd'] = FG::formatterx100val($waccSheet->getCell('I17')->getValue());
                } else {
                    $result['epd'] = FG::formatterx100val($result['epd']);
                }

                $result['epc'] = $waccSheet->getCell('I18')->getOldCalculatedValue();
                if (!$result['epc']) {
                    $result['epc'] = FG::formatterx100val($waccSheet->getCell('I18')->getValue());
                } else {
                    $result['epc'] = FG::formatterx100val($result['epc']);
                }

                $result['devaluation'] = $waccSheet->getCell('F7')->getOldCalculatedValue();
                if (!$result['devaluation']) {
                    $result['devaluation'] = FG::formatterx100val($waccSheet->getCell('F7')->getValue());
                } else {
                    $result['devaluation'] = FG::formatterx100val($result['devaluation']);
                }

                $result['tax'] = $waccSheet->getCell('F20')->getOldCalculatedValue();
                if (!$result['tax']) {
                    $result['tax'] = FG::formatterx100val($waccSheet->getCell('F20')->getValue());
                } else {
                    $result['tax'] = FG::formatterx100val($result['tax']);
                }

                $ekd = $waccSheet->getCell('I3')->getOldCalculatedValue();
                if (!$ekd) {
                    $ekd = FG::formatterx100val($waccSheet->getCell('I3')->getValue());
                } else {
                    $ekd = FG::formatterx100val($ekd);
                }
                $result['ekd'] = $ekd;

                // Leer datos financieros adicionales directamente del WACC
                try {
                    $dcRatioValue = $waccSheet->getCell('C19')->getValue();
                    $result['financial_data']['dc_ratio'] = FG::formatterx100val($dcRatioValue);
                } catch (\Exception $e) {
                    $result['financial_data']['dc_ratio'] = '';
                }

                try {
                    $taxRateValue = $waccSheet->getCell('C20')->getValue();
                    $result['financial_data']['effective_tax_rate'] = FG::formatterx100val($taxRateValue);
                } catch (\Exception $e) {
                    $result['financial_data']['effective_tax_rate'] = '';
                }

                try {
                    $betaValue = $waccSheet->getCell('C21')->getValue();
                    $result['financial_data']['beta_levered'] = $betaValue;
                } catch (\Exception $e) {
                    $result['financial_data']['beta_levered'] = '';
                }
            }

            error_log("Successfully read form data from local file");
            return $result;
        } catch (\Exception $e) {
            error_log("Error reading form data from local file: " . $e->getMessage());
            throw new \Exception("Error al leer datos del archivo local: " . $e->getMessage());
        }
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

    public function setFormCloud($input, $report = null)
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

        // Función helper para reintentar operaciones con timeout
        $setCellWithRetry = function ($fileId, $worksheet, $row, $column, $value, $type, $maxRetries = 3) use ($onedriveService) {
            $attempt = 0;
            while ($attempt < $maxRetries) {
                try {
                    return $onedriveService->setCell($fileId, $worksheet, $row, $column, $value, $type);
                } catch (\Exception $e) {
                    $attempt++;
                    // Si es un timeout (504) o error de servidor, reintentar
                    if (strpos($e->getMessage(), '504') !== false || strpos($e->getMessage(), 'Gateway Time-out') !== false) {
                        if ($attempt < $maxRetries) {
                            // Esperar un poco antes del reintento (backoff exponencial)
                            sleep($attempt * 2);
                            continue;
                        }
                    }
                    // Si no es un error de timeout o ya agotamos los reintentos, relanzar la excepción
                    throw $e;
                }
            }
        };

        try {
            $cell = $setCellWithRetry($report->eid, $this->sheetname_user, 1, 2, $dateValue, 'Double');
            $cell = $setCellWithRetry($report->eid, $this->sheetname_user, 2, 2, $country, 'String');

            $cell = $setCellWithRetry($report->eid, $this->sheetname_wacc, 2, 2, $instrument, 'String');
            $cell = $setCellWithRetry($report->eid, $this->sheetname_wacc, 5, 2, $bono, 'Double');
            $cell = $setCellWithRetry($report->eid, $this->sheetname_wacc, 12, 2, $sector, 'String');

            $devaluation = ($devaluation / 100);
            $devaluation = $devaluation > 0 ? $devaluation : 0;

            $tax = ($tax / 100);
            $tax = $tax > 0 ? $tax : 0;

            $cell = $setCellWithRetry($report->eid, $this->sheetname_wacc, 6, 5, $devaluation, 'Double');

            // Agregar un pequeño delay antes de actualizar la celda del impuesto que está causando problemas
            usleep(500000); // 0.5 segundos
            $cell = $setCellWithRetry($report->eid, $this->sheetname_wacc, 19, 5, $tax, 'Double');

            if ($typeId == 2) {
                $kd = ($kd / 100);
                $kd = $kd > 0 ? $kd : 0;

                $debt = ($debt / 100);
                $debt = $debt > 0 ? $debt : 0;

                $capital = ($capital / 100);
                $capital = $capital > 0 ? $capital : 0;

                $cell = $setCellWithRetry($report->eid, $this->sheetname_user, 3, 2, $currency, 'String');
                $cell = $setCellWithRetry($report->eid, $this->sheetname_wacc, 2, 8, $kd, 'Double');
                $cell = $setCellWithRetry($report->eid, $this->sheetname_wacc, 16, 8, $debt, 'Double');
                $cell = $setCellWithRetry($report->eid, $this->sheetname_wacc, 17, 8, $capital, 'Double');
            }

            // Manejar datos financieros optimizados si están disponibles
            if (isset($input['useFinancialData']) && $input['useFinancialData'] == '1') {

                // D/C Ratio (Excel C19)
                if (isset($input['dc_ratio_optimized'])) {
                    $cell = $setCellWithRetry($report->eid, $this->sheetname_wacc, 18, 2, $input['dc_ratio_optimized'], 'Double');
                }

                // Tasa Efectiva de Impuesto (Excel C20)
                if (isset($input['effective_tax_rate_optimized'])) {
                    $cell = $setCellWithRetry($report->eid, $this->sheetname_wacc, 19, 2, $input['effective_tax_rate_optimized'], 'Double');
                }

                // Beta Apalancado (Excel C21)
                if (isset($input['beta_levered_optimized'])) {
                    $cell = $setCellWithRetry($report->eid, $this->sheetname_wacc, 20, 2, $input['beta_levered_optimized'], 'Double');
                }
            }
        } catch (\Exception $e) {
            // Log del error para debugging
            error_log("Error en setFormCloud: " . $e->getMessage());
            throw new \Exception("Error al actualizar el archivo Excel: " . $e->getMessage());
        }
    }

    /**
     * Actualiza el formulario trabajando directamente con el archivo Excel local
     * @param array $input Datos del formulario
     * @param object $report Reporte en base de datos
     * @param string $localFilePath Ruta local al archivo Excel
     * @return void
     */
    public function setFormCloudLocal($input, $report, $localFilePath)
    {
        try {
            error_log("Setting form data in local file (FAST MODE): " . $localFilePath);

            $changes = [];

            // Prepare User Sheet Changes
            $userChanges = [];
            $dateValue = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($input['date']);
            $userChanges['C2'] = ['value' => $dateValue, 'type' => 'n'];
            $userChanges['C3'] = ['value' => $input['country'], 'type' => 's'];
            if ($input['typeId'] == 2) {
                $userChanges['C4'] = ['value' => $input['currency'], 'type' => 's'];
            }
            $changes[$this->sheetname_user] = $userChanges;

            // Prepare WACC Sheet Changes
            $waccChanges = [];
            $waccChanges['C3'] = ['value' => $input['instrument'], 'type' => 's'];
            $waccChanges['C6'] = ['value' => $input['bono'], 'type' => 'n'];
            $waccChanges['C13'] = ['value' => $input['sector'], 'type' => 's'];

            $devaluation = $input['devaluation'] / 100;
            $waccChanges['F7'] = ['value' => ($devaluation > 0 ? $devaluation : 0), 'type' => 'n'];

            $tax = $input['tax'] / 100;
            $waccChanges['F20'] = ['value' => ($tax > 0 ? $tax : 0), 'type' => 'n'];

            if ($input['typeId'] == 2) {
                $kd = $input['kd'] / 100;
                $waccChanges['I3'] = ['value' => ($kd > 0 ? $kd : 0), 'type' => 'n'];

                $debt = $input['debt'] / 100;
                $waccChanges['I17'] = ['value' => ($debt > 0 ? $debt : 0), 'type' => 'n'];

                $capital = $input['capital'] / 100;
                $waccChanges['I18'] = ['value' => ($capital > 0 ? $capital : 0), 'type' => 'n'];
            }

            // Financial Data
            if (isset($input['useFinancialData']) && $input['useFinancialData'] == '1') {
                $fields = [
                    'dc_ratio_optimized' => 'C19',
                    'effective_tax_rate_optimized' => 'C20',
                    'beta_levered_optimized' => 'C21'
                ];

                foreach ($fields as $key => $cell) {
                    if (isset($input[$key])) {
                        $waccChanges[$cell] = ['value' => $input[$key], 'type' => 'n'];
                    }
                }
            }
            $changes[$this->sheetname_wacc] = $waccChanges;

            // Execute Fast Update
            $this->updateExcelFast($localFilePath, $changes);

            error_log("Successfully updated local Excel file (FAST MODE): " . $localFilePath);
        } catch (\Exception $e) {
            error_log("Error en setFormCloudLocal: " . $e->getMessage());
            throw new \Exception("Error al actualizar el archivo Excel local: " . $e->getMessage());
        }
    }

    /**
     * Actualiza el archivo Excel directamente manipulando el XML (Mucho más rápido que PhpSpreadsheet)
     */
    private function updateExcelFast($filename, $changes)
    {
        $zip = new \ZipArchive;
        if ($zip->open($filename) === TRUE) {
            // 1. Map sheet names to file paths
            $workbookXml = $zip->getFromName('xl/workbook.xml');
            $relsXml = $zip->getFromName('xl/_rels/workbook.xml.rels');

            if (!$workbookXml || !$relsXml) {
                $zip->close();
                throw new \Exception("Estructura de archivo Excel inválida");
            }

            $sheetMap = []; // Name -> ID
            $xml = simplexml_load_string($workbookXml);
            $namespaces = $xml->getNamespaces(true);

            // Manejar namespace por defecto si existe
            if (isset($namespaces[''])) {
                $xml->registerXPathNamespace('m', $namespaces['']);
                $sheets = $xml->xpath('//m:sheets/m:sheet');
            } else {
                $sheets = $xml->sheets->sheet;
            }

            // Namespace para relaciones (r:id)
            $rNs = $namespaces['r'] ?? 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';

            foreach ($sheets as $sheet) {
                $attributes = $sheet->attributes();
                $rAttributes = $sheet->attributes($rNs);
                $sheetMap[(string)$attributes['name']] = (string)$rAttributes['id'];
            }

            $fileMap = []; // ID -> Target (path)
            $xmlRels = simplexml_load_string($relsXml);
            // Rels namespace usually default
            $relsNs = $xmlRels->getNamespaces(true);
            if (isset($relsNs[''])) {
                $xmlRels->registerXPathNamespace('r', $relsNs['']);
                $relationships = $xmlRels->xpath('//r:Relationship');
            } else {
                $relationships = $xmlRels->Relationship;
            }

            foreach ($relationships as $rel) {
                $attributes = $rel->attributes();
                $fileMap[(string)$attributes['Id']] = (string)$attributes['Target'];
            }

            // 2. Process changes
            foreach ($changes as $sheetName => $cells) {
                if (!isset($sheetMap[$sheetName])) continue;
                $rId = $sheetMap[$sheetName];
                if (!isset($fileMap[$rId])) continue;

                $target = $fileMap[$rId];
                $path = 'xl/' . $target;

                $sheetXmlContent = $zip->getFromName($path);
                if (!$sheetXmlContent) continue;

                $dom = new \DOMDocument();
                $dom->preserveWhiteSpace = false;
                $dom->loadXML($sheetXmlContent);
                $xpath = new \DOMXPath($dom);
                $xpath->registerNamespace('m', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');

                $modified = false;

                foreach ($cells as $cellRef => $valueData) {
                    $value = $valueData['value'];
                    $type = $valueData['type'];

                    preg_match('/([A-Z]+)([0-9]+)/', $cellRef, $matches);
                    $row = $matches[2];

                    // Find Row
                    $rowNode = $xpath->query("//m:sheetData/m:row[@r='$row']")->item(0);
                    if (!$rowNode) continue;

                    // Find Cell
                    $cellNode = $xpath->query("m:c[@r='$cellRef']", $rowNode)->item(0);
                    if (!$cellNode) {
                        $cellNode = $dom->createElement('c');
                        $cellNode->setAttribute('r', $cellRef);
                        $rowNode->appendChild($cellNode);
                    }

                    // Update Value
                    while ($cellNode->hasChildNodes()) {
                        $cellNode->removeChild($cellNode->firstChild);
                    }

                    if ($type == 's') {
                        $cellNode->setAttribute('t', 'inlineStr');
                        $is = $dom->createElement('is');
                        $t = $dom->createElement('t', htmlspecialchars($value));
                        $is->appendChild($t);
                        $cellNode->appendChild($is);
                    } else {
                        // Number
                        $cellNode->removeAttribute('t');
                        $v = $dom->createElement('v', $value);
                        $cellNode->appendChild($v);
                    }
                    $modified = true;
                }

                if ($modified) {
                    $zip->addFromString($path, $dom->saveXML());
                }
            }

            // 3. Force Recalculation on Load
            // Remove calculation chain to force rebuild
            $zip->deleteName('xl/calcChain.xml');

            // Set fullCalcOnLoad in workbook.xml
            $dom = new \DOMDocument();
            $dom->preserveWhiteSpace = false;
            $dom->loadXML($workbookXml);
            $xpath = new \DOMXPath($dom);
            $xpath->registerNamespace('m', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');

            // Find or create calcPr
            $nodes = $xpath->query('//m:calcPr');
            if ($nodes->length > 0) {
                $calcPr = $nodes->item(0);
            } else {
                $calcPr = $dom->createElement('calcPr');
                $workbook = $dom->getElementsByTagName('workbook')->item(0);
                $workbook->appendChild($calcPr);
            }

            if ($calcPr instanceof \DOMElement) {
                $calcPr->setAttribute('fullCalcOnLoad', '1');
                $calcPr->setAttribute('calcId', '999999'); // Force newer calcId
            }

            $zip->addFromString('xl/workbook.xml', $dom->saveXML());

            $zip->close();
            return true;
        }
        return false;
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
        // Deshabilitar Garbage Collection para mejorar rendimiento de PhpSpreadsheet
        gc_disable();

        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();

        // Cargar solo las hojas necesarias indicadas por el usuario
        $reader->setLoadSheetsOnly([
            $this->sheetname_user,
            $this->sheetname_wacc,
            $this->sheetname_tablas,
            $this->sheetname_industries,
            $this->sheetname_contries,
            $this->sheetname_koa_boa,
            $this->sheetname_RfAjustado,
            $this->sheetname_RfBaseDatos,
            $this->sheetname_Embi,
            $this->sheetname_PrimaMarket,
        ]);
        $reader->setReadEmptyCells(false);

        $spreadsheet = $reader->load($filename);
        $worksheet = $spreadsheet->getSheetByName($this->sheetname_wacc);

        $costdebt = $worksheet->getCell('I3')->getCalculatedValue();
        if (!$costdebt) {
            $costdebt = FG::formatterx100val($worksheet->getCell('I3')->getValue());
        } else {
            $costdebt = FG::formatterx100val($costdebt);
        }

        $result = [
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

        // Limpiar memoria y reactivar GC
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
        gc_enable();
        gc_collect_cycles();

        return $result;
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

    /**
     * Obtiene los resultados desde un archivo local
     * @param object $report Reporte con información local
     * @return array Datos de resultados
     */
    public function getResultLocal($report)
    {
        // Construir la ruta del archivo local usando el nombre del archivo
        $fullPath = FG::fullFolderPathUserTemplate();
        $localFilePath = $fullPath . '/' . $report->file;

        if (!file_exists($localFilePath)) {
            throw new \Exception('No se encontró el archivo local del reporte: ' . $localFilePath);
        }

        error_log("Reading results from local file: " . $localFilePath);

        // Usar la función result existente que ya trabaja con archivos locales
        return $this->result($localFilePath);
    }

    /**
     * Obtiene los datos de análisis desde un archivo local
     * @param object $report Reporte con información local
     * @return array Datos del análisis
     */
    public function getAnalysisLocal($report)
    {
        // Para análisis, usar el archivo generado en public/template/
        $filename = strtolower('kapital-analysis-' . $report->code) . '.xlsx';
        $analysisFilePath = __DIR__ . '/../../public/template/' . $filename;

        // Si no existe el archivo de análisis, usar el archivo principal
        if (!file_exists($analysisFilePath)) {
            $fullPath = FG::fullFolderPathUserTemplate();
            $analysisFilePath = $fullPath . '/' . $report->file;
        }

        if (!file_exists($analysisFilePath)) {
            throw new \Exception('No se encontró el archivo de análisis local: ' . $analysisFilePath);
        }

        error_log("Reading analysis from local file: " . $analysisFilePath);

        // Usar la función analysis existente que ya trabaja con archivos locales
        return $this->analysis($analysisFilePath);
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

    /**
     * Configura el análisis de costos trabajando directamente con archivo local
     * @param object $report Reporte con información local
     * @param array $input Datos del análisis
     * @return void
     */
    public function setCostAnalysisLocal($report, $input)
    {
        // Para análisis de costos, usar el archivo generado en public/template/
        $filename = strtolower('kapital-analysis-' . $report->code) . '.xlsx';
        $analysisFilePath = __DIR__ . '/../../public/template/' . $filename;

        // Si no existe el archivo de análisis, usar el archivo principal
        if (!file_exists($analysisFilePath)) {
            $fullPath = FG::fullFolderPathUserTemplate();
            $analysisFilePath = $fullPath . '/' . $report->file;
        }

        if (!file_exists($analysisFilePath)) {
            throw new \Exception('No se encontró el archivo de análisis local: ' . $analysisFilePath);
        }

        error_log("Setting cost analysis in local file: " . $analysisFilePath);

        // Usar la función costAnalysis existente que ya trabaja con archivos locales
        $this->costAnalysis($analysisFilePath, $input);
    }

    public function setCostAnalysisCloud($input, $report = null)
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

        // Intentar método rápido (XML directo)
        try {
            $taxrate = $this->getTaxRateFast($filename, $year, $country);
            if ($taxrate !== null) {
                $taxrate = FG::formatterx100val($taxrate);
                return compact('taxrate');
            }
        } catch (\Exception $e) {
            error_log("Fast taxrate failed, falling back: " . $e->getMessage());
        }

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

    /**
     * Obtiene la tasa de impuestos leyendo directamente el XML (Mucho más rápido)
     */
    private function getTaxRateFast($filename, $year, $country)
    {
        $zip = new \ZipArchive;
        if ($zip->open($filename) !== TRUE) {
            return null;
        }

        try {
            // 1. Map sheet name to file path
            $workbookXml = $zip->getFromName('xl/workbook.xml');
            $relsXml = $zip->getFromName('xl/_rels/workbook.xml.rels');

            if (!$workbookXml || !$relsXml) {
                $zip->close();
                return null;
            }

            $sheetId = null;
            $xml = simplexml_load_string($workbookXml);
            $namespaces = $xml->getNamespaces(true);

            if (isset($namespaces[''])) {
                $xml->registerXPathNamespace('m', $namespaces['']);
                $sheets = $xml->xpath('//m:sheets/m:sheet');
            } else {
                $sheets = $xml->sheets->sheet;
            }

            $rNs = $namespaces['r'] ?? 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';

            foreach ($sheets as $sheet) {
                $attributes = $sheet->attributes();
                if ((string)$attributes['name'] === $this->sheetname_contries) {
                    $rAttributes = $sheet->attributes($rNs);
                    $sheetId = (string)$rAttributes['id'];
                    break;
                }
            }

            if (!$sheetId) {
                $zip->close();
                return null;
            }

            $target = null;
            $xmlRels = simplexml_load_string($relsXml);
            $relsNs = $xmlRels->getNamespaces(true);
            if (isset($relsNs[''])) {
                $xmlRels->registerXPathNamespace('r', $relsNs['']);
                $relationships = $xmlRels->xpath('//r:Relationship');
            } else {
                $relationships = $xmlRels->Relationship;
            }

            foreach ($relationships as $rel) {
                $attributes = $rel->attributes();
                if ((string)$attributes['Id'] === $sheetId) {
                    $target = (string)$attributes['Target'];
                    break;
                }
            }

            if (!$target) {
                $zip->close();
                return null;
            }

            $path = 'xl/' . $target;

            // 2. Load Shared Strings
            $sharedStrings = [];
            if ($zip->locateName('xl/sharedStrings.xml') !== false) {
                $xmlStrings = simplexml_load_string($zip->getFromName('xl/sharedStrings.xml'));
                $namespaces = $xmlStrings->getNamespaces(true);
                if (isset($namespaces[''])) {
                    $xmlStrings->registerXPathNamespace('m', $namespaces['']);
                    $sis = $xmlStrings->xpath('//m:si');
                } else {
                    $sis = $xmlStrings->si;
                }

                foreach ($sis as $si) {
                    $t = (string)$si->t;
                    $sharedStrings[] = $t;
                }
            }

            // 3. Parse Sheet
            $sheetXmlContent = $zip->getFromName($path);
            $zip->close();

            if (!$sheetXmlContent) return null;

            $dom = new \DOMDocument();
            $dom->preserveWhiteSpace = false;
            $dom->loadXML($sheetXmlContent);
            $xpath = new \DOMXPath($dom);
            $xpath->registerNamespace('m', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');

            // Find Year Column (Row 1)
            $yearColumn = null;
            $row1 = $xpath->query('//m:sheetData/m:row[@r="1"]')->item(0);
            if (!$row1) return null;

            $cells = $xpath->query('m:c', $row1);
            foreach ($cells as $cell) {
                $val = $this->getCellValueFast($cell, $sharedStrings);
                if ($val == $year) {
                    if ($cell instanceof \DOMElement) {
                        $ref = $cell->getAttribute('r');
                        $yearColumn = preg_replace('/[0-9]+/', '', $ref);
                        break;
                    }
                }
            }

            if (!$yearColumn) return null;

            // Find Country Row (Column A)
            $countryRow = null;
            $rows = $xpath->query('//m:sheetData/m:row');
            foreach ($rows as $row) {
                if ($row instanceof \DOMElement) {
                    $rIndex = $row->getAttribute('r');
                    if ($rIndex == 1) continue;

                    $aCell = $xpath->query("m:c[@r='A{$rIndex}']", $row)->item(0);
                    if ($aCell) {
                        $val = $this->getCellValueFast($aCell, $sharedStrings);
                        if ($val == $country) {
                            $countryRow = $rIndex;
                            break;
                        }
                    }
                }
            }

            if (!$countryRow) return null;

            // Get Tax Rate
            $targetRef = $yearColumn . $countryRow;
            $targetRow = $xpath->query("//m:sheetData/m:row[@r='$countryRow']")->item(0);
            if ($targetRow) {
                $targetCell = $xpath->query("m:c[@r='$targetRef']", $targetRow)->item(0);
                if ($targetCell) {
                    return $this->getCellValueFast($targetCell, $sharedStrings);
                }
            }

            return null;
        } catch (\Exception $e) {
            if ($zip->status == \ZipArchive::ER_OK) $zip->close();
            throw $e;
        }
    }

    private function getCellValueFast($cellNode, $sharedStrings)
    {
        $t = $cellNode->getAttribute('t');
        $vNode = $cellNode->getElementsByTagName('v')->item(0);
        $v = $vNode ? $vNode->nodeValue : '';

        if ($t == 's') {
            return isset($sharedStrings[$v]) ? $sharedStrings[$v] : $v;
        } elseif ($t == 'inlineStr') {
            $tNode = $cellNode->getElementsByTagName('t')->item(0);
            return $tNode ? $tNode->nodeValue : '';
        }
        return $v;
    }

    public function getReportCloud($company, $sheetname, $tablename, $report = null)
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
