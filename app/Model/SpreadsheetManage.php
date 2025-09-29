<?php 
/** 
 * https://phpspreadsheet.readthedocs.io/en/latest/topics/reading-and-writing-to-file/
 * https://github.com/PHPOffice/PhpSpreadsheet/blob/1.3.1/src/PhpSpreadsheet/Cell/Cell.php#L162
 * https://github.com/PHPOffice/PhpSpreadsheet/blob/master/src/PhpSpreadsheet/IOFactory.php
 */

namespace App\Model;

ini_set('memory_limit', '-1');

use App\Utilitarian\{FG};
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;

define('EMPLOYEE', 'employee');

class SpreadsheetManage {

    public $colors = [
        'one'   => '#49FDAC',
        'two'   => '#7DDDFF',
        'three' => '#DA9EDA',
        'four'  => '#9C8BD9',
        'five'  => '#677FCB',
        'six'   => '#2C9DCA',
        'seven' => '#E52320',
        'eight' => '#800080',
        'nine'  => '#02A9DF'
    ];

    public function initCalculateSectorBonusReport($args) {
        $rsp = FG::responseDefault();
        try {
            $file_user = $args['file_user'];
            if(!$file_user) {
                throw new \Exception('File of user no encontred');
            }

            $sheetname = @$_POST['riskFreeRate'] == EMPLOYEE ? 'Koa_emercountry_sec' : 'Koa_emercountry_sec_10';
            $sheetname_countries = 'Embi';
            $countries = array(); $inputs = array();

            $filename = $file_user;
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            $reader->setLoadSheetsOnly($sheetname); 
            $spreadsheet = $reader->load($filename);

            $worksheet = $spreadsheet->getActiveSheet();
            $lastRow = $worksheet->getHighestRow();
            # inputs 
            $inputs = array(); $legend = array();
            for ($row = 6; $row <= 12; $row++) {
                $b = $worksheet->getCell('B'.$row)->getValue();
                $c = $worksheet->getCell('C'.$row)->getValue();
                if ($b && $c) {
                    if ($row == 8) {
                        $c = $worksheet->getCell('C'.$row)->getFormattedValue();
                    }
                    array_push($inputs, ['name'=>$b, 'value'=>$c]);
                }
            }

            # countries
            $reader->setLoadSheetsOnly($sheetname_countries); 
            $spreadsheet = $reader->load($filename);
            $worksheet = $spreadsheet->getActiveSheet();
            $lastRow = $worksheet->getHighestRow();
            for ($row = 2; $row <= $lastRow; $row++) {
                $a = $worksheet->getCell('A'.$row)->getValue();
                if ($a) {
                    array_push($countries, ['name' => $a]);
                }
            }
            
            $rsp['success'] = true;
            $rsp['data'] = compact('inputs', 'countries');
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function initCalculateSectorBonusReport2($args) {
        $rsp = FG::responseDefault();
        try {
            $file_user = $args['file_user'];
            if(!$file_user) {
                throw new \Exception('File of user no encontred');
            }
            $sheetname = @$_POST['riskFreeRate'] == EMPLOYEE ? 'Koa_emercountry_sec' : 'Koa_emercountry_sec_10';
            $report = array(); $legend = array();

            $filename = $file_user;           
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            $reader->setLoadSheetsOnly($sheetname); 
            $spreadsheet = $reader->load($filename);

            $worksheet = $spreadsheet->getActiveSheet();
            $lastRow = $worksheet->getHighestRow();

            # report
            for ($row = 17; $row <= $lastRow; $row++) {
                $b = $worksheet->getCell('B'.$row)->getValue();
                $c = $worksheet->getCell('C'.$row)->getValue();
                if ($c && $b) {
                    $c = FG::formatterx100p($worksheet->getCell('C'.$row)->getOldCalculatedValue());
                    array_push($report, ['name'=>$b, 'value'=>$c]);
                }
            }
            # legend
            for ($row = 7; $row <= $lastRow; $row++) {
                $e = $worksheet->getCell('E'.$row)->getValue();
                $f = $worksheet->getCell('F'.$row)->getValue();
                if ($e && $f) {
                    if ($row >= 9) {
                        $f =  FG::numberformat($worksheet->getCell('F'.$row)->getOldCalculatedValue());
                    } else {
                        $f = FG::formatterx100p($worksheet->getCell('F'.$row)->getOldCalculatedValue());
                    }
                    if ($f && $e) {
                        array_push($legend, ['name'=>$e, 'value'=>$f]);
                    }
                }
            }
            
            $rsp['success'] = true;
            $rsp['data'] = compact('legend', 'report');
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function initCalculate($args) {
        $rsp = FG::responseDefault();
        try {
            extract($args);
            if(!$filename) {
                throw new \Exception('File of user no encontred');
            }
            
            $sheetname_calculate = 'Calculo K';
            $sheetname = 'Tablas';
            $sheetname_industry = 'Industry Averages';

            $sectors = array(); $graph = array('industries' => []);

            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();

            $reader->setLoadSheetsOnly($sheetname_industry); 
            $spreadsheet = $reader->load($filename);

            $worksheet = $spreadsheet->getActiveSheet();
            $lastRow = 112;
            $values = array(); $labels = array(); $industries = array();

            for ($row = 20; $row <= $lastRow; $row++) {
                $a = $worksheet->getCell('A'.$row)->getValue();
                if ($a) {
                    array_push($sectors, $a);
                }
                if ($row <= 110) {
                    $q = $worksheet->getCell('N'.$row)->getOldCalculatedValue();
                    array_push($industries, ['label'=>$a, 'value'=>FG::formatterx100val($q)]);
                }                
            }
            $graph['industries'] = $industries;

            $reader->setLoadSheetsOnly($sheetname_calculate); 
            $spreadsheet = $reader->load($filename);
            $sector = $spreadsheet->getActiveSheet()->getCell('C12')->getValue();
            $instrument = $spreadsheet->getActiveSheet()->getCell('C2')->getValue();

            $reader->setLoadSheetsOnly($sheetname); 
            $spreadsheet = $reader->load($filename);
            $worksheet = $spreadsheet->getActiveSheet();
            $instruments = array();
            for ($row = 2; $row <= 3; $row++) {
                array_push($instruments,  $worksheet->getCell('A'.$row)->getValue());              
            }
            $rsp['success'] = true;
            $rsp['data'] = compact('sectors', 'sector', 'graph', 'instrument', 'instruments');
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function getRiskLevel($args) {
        $rsp = FG::responseDefault();
        try {
            extract($args);
            if(!$filename) {
                throw new \Exception('File of filename no encontred');
            }
            
            $sheetname_calculate = 'Calculo K';
            $sheetname = 'Tablas';
            $sheetname_risk = 'Riesgo-Koa';

            $sectors = array(); $graph = array('industries' => []);

            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();

            $reader->setLoadSheetsOnly($sheetname_risk); 
            $spreadsheet = $reader->load($filename);

            $worksheet = $spreadsheet->getActiveSheet();
            $lastRow = 112;
            $values = array(); $labels = array(); $industries = array();

            for ($row = 20; $row <= $lastRow; $row++) {
                $a = $worksheet->getCell('A'.$row)->getValue();
                if ($a) {
                    array_push($sectors, $a);
                }
                if ($row <= 110) {
                    $q = $worksheet->getCell('N'.$row)->getOldCalculatedValue();
                    array_push($industries, ['label'=>$a, 'value'=>FG::formatterx100val($q)]);
                }                
            }
            $graph['industries'] = $industries;

            $reader->setLoadSheetsOnly($sheetname_calculate); 
            $spreadsheet = $reader->load($filename);
            $sector = $spreadsheet->getActiveSheet()->getCell('C12')->getValue();
            $instrument = $spreadsheet->getActiveSheet()->getCell('C2')->getValue();

            $reader->setLoadSheetsOnly($sheetname); 
            $spreadsheet = $reader->load($filename);
            $worksheet = $spreadsheet->getActiveSheet();
            $instruments = array();
            for ($row = 2; $row <= 3; $row++) {
                array_push($instruments,  $worksheet->getCell('A'.$row)->getValue());              
            }
            $rsp['success'] = true;
            $rsp['data'] = compact('sectors', 'sector', 'graph', 'instrument', 'instruments');
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function initCurvePerformance($args) {
        $rsp = FG::responseDefault();
        try {
            extract($args);
            if(!$filename) {
                throw new \Exception('File of user no encontred');
            }
            $sheetname = 'Tablas';
            $sheetname_rf_adjust = 'Rf Ajustado';
            $sheetname_calculate = 'Calculo K';

            $periods = array(); $graph = array('performance' => []);

            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();

            $reader->setLoadSheetsOnly($sheetname); 
            $spreadsheet = $reader->load($filename);

            $worksheet = $spreadsheet->getActiveSheet();
            $lastRow = $worksheet->getHighestRow();

            for ($row = 2; $row <= $lastRow; $row++) {
                $g = $worksheet->getCell('C'.$row)->getValue();
                if ($g) {
                    array_push($periods, $g);
                }
            }
            
            $reader->setLoadSheetsOnly($sheetname_calculate);
            $spreadsheet = $reader->load($filename);
            $period = $spreadsheet->getActiveSheet()->getCell('C5')->getCalculatedValue();

            $reader->setLoadSheetsOnly($sheetname_rf_adjust); 
            $spreadsheet = $reader->load($filename);
            $worksheet = $spreadsheet->getActiveSheet();
            $lastRow = 25;
            $performance = array();

            $points = array();
            for ($row = 14; $row <= $lastRow; $row++) {
                $a = $worksheet->getCell('A'.$row)->getCalculatedValue();
                $b = $worksheet->getCell('B'.$row)->getValue();
                $points[$a] = ['value'=>($b ? FG::formatterx100($b) : 0), 'label'=>$a];
            }

            foreach ($periods as $k => $o) {
                if (isset($points[$o])){
                    $p = $points[$o]['label'] == $period ? $period : 0;
                    array_push($performance, ['value'=> $points[$o]['value'], 'label'=>$points[$o]['label'], 'index'=>$p]);
                }
            }

            $idx = 0;
            for ($i=0; $i <= 45; $i+=5) {
                if ($i != 0) {
                    $performance[$idx]['interval'] = $i;
                    $idx ++;
                }

            }

           // echo json_encode($performance); exit();
            $graph['performance'] = $performance;

            $rsp['success'] = true;
            $rsp['data'] = compact('periods', 'period', 'graph');
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function onCurvePerformance($args) {
        $rsp = FG::responseDefault();
        try {
            extract($args);
            if(!$filename) {
                throw new \Exception('File of user no encontred');
            }
            if (!$period) {
                throw new \Exception('The period is required');
            }
            $sheetname = 'Calculo K';
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            $spreadsheet = $reader->load($filename);
            $worksheet = $spreadsheet->getSheetByName($sheetname);
            $worksheet->setCellValue('C5', $period);
            $xlsxWriter = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $xlsxWriter->save($filename);

            $rsp['success'] = true;
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function initStructureDeveloped($args) {
        $rsp = FG::responseDefault();
        try {
            extract($args);
            if(!$filename) {
                throw new \Exception('File of user no encontred');
            }
            $sheetname = 'Calculo K';
            $sheetname_market = 'Prima de mercado';
            $graph = array('general' => [], 'structure'=>[]);

            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            $reader->setLoadSheetsOnly($sheetname); 
            $spreadsheet = $reader->load($filename);
            $worksheet = $spreadsheet->getActiveSheet();

            $reader->setLoadSheetsOnly($sheetname_market);
            $spreadsheet = $reader->load($filename);
            $worksheetPM = $spreadsheet->getActiveSheet();

            $instrument = $worksheet->getCell('C2')->getValue();

            $graph['structure'] = array(
                'groups' => [
                    [ 'label' => '%D', 'index' => 'value1', 'color' =>  $this->colors['three'] ],
                    [ 'label' => '%C', 'index' => 'value2', 'color' =>  $this->colors['six'] ]
                ],
                'items' => [
                    [
                        'value1' => FG::formatterx100val($worksheet->getCell('C16')->getOldCalculatedValue()),
                        'value2' => FG::formatterx100val($worksheet->getCell('C17')->getOldCalculatedValue()),
                        'label' => 'MERCADO DESARROLLADO'
                    ]
                ]
            );

            $calculations = array(
                [
                    'label'   => 'Prima de Mercado (Rm -rf)',
                    'value'   => FG::formatterx100p($worksheet->getCell('C8')->getOldCalculatedValue()),
                    'comment' => 'Calculado con la diferencia entre la Tasa de riesgo de Mercado (Rm) y la Tasa libre de riesgo promedio (rf)'
                ],
                [
                    'label'   => 'Tasa de riesgo de Mercado (Rm)',
                    'value'   => FG::formatterx100p($worksheetPM->getCell('F3')->getOldCalculatedValue()),
                    'comment' => 'Calculado con el promedio del índice S&P de los últimos 50 años (1971-2021)'
                ],
                [
                    'label'   => 'Tasa libre de riesgo promedio (rf)',
                    'value'   => FG::formatterx100p($worksheetPM->getCell('H3')->getOldCalculatedValue()),
                    'comment' => 'Calculado con el promedio del rendimiento T-bond a 10 años de los últimos 50 años (1971-2021)'
                ],
                [
                    'label' => 'Tasa libre de riesgo spot (rf)',
                    'value' => FG::formatterx100p($worksheet->getCell('C9')->getOldCalculatedValue())
                ],
                [
                    'label' => 'Porcentaje de deuda (%D)',
                    'value' => FG::formatterx100p($worksheet->getCell('C16')->getOldCalculatedValue())
                ],
                [
                    'label' => 'Porcentaje de capital (%C)',
                    'value' => FG::formatterx100p($worksheet->getCell('C17')->getOldCalculatedValue())
                ],
            );

            $sectors = array();

            $graph['general'] = array(
                [
                    'label' => 'Rm  promedio', 
                    'value' => FG::formatterx100val($worksheetPM->getCell('F3')->getOldCalculatedValue()), 
                    'color' => $this->colors['three'] 
                ],
                [
                    'label' => 'rf  promedio', 
                    'value' => FG::formatterx100val($worksheetPM->getCell('H3')->getOldCalculatedValue()), 
                    'color' =>  $this->colors['one'] 
                ],
                [
                    'label' => 'rf  spot', 
                    'value' => FG::formatterx100val($worksheet->getCell('C9')->getOldCalculatedValue()), 
                    'color' =>  $this->colors['six'] 
                ]
            );

            $rsp['success'] = true;
            $rsp['data'] = compact('graph', 'calculations', 'sectors', 'instrument');
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }
    
    public function initParameterDeveloped($args) {
        $rsp = FG::responseDefault();
        try {
            extract($args);
            if(!$filename) {
                throw new \Exception('File of user no encontred');
            }
            $sheetname = 'Calculo K';
            $graph = array('general' => []);

            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            $reader->setLoadSheetsOnly($sheetname); 
            $spreadsheet = $reader->load($filename);
            $worksheet = $spreadsheet->getActiveSheet();

            $general = array();

            $finance = array(
                'parameters' => array(
                    [
                        'label' => 'Beta apalancado (Be)',
                        'value' => FG::numberformat($worksheet->getCell('C20')->getOldCalculatedValue())
                    ],
                    [
                        'label' => 'Riesgo de negocio financiero (𝝓𝒇)',
                        'value' => FG::formatterx100p($worksheet->getCell('C39')->getOldCalculatedValue())
                    ]
                ),
                'results' => array(
                    [
                        'label' => ' Costo de capital financiero (Ke)',
                        'value' => FG::formatterx100p($worksheet->getCell('C23')->getOldCalculatedValue())    
                    ]
                )
            );

            $economic = array(
                'parameters' => array(
                    [
                        'label' => 'Beta desapalancado (Boa)',
                        'value' => FG::numberformat($worksheet->getCell('C26')->getOldCalculatedValue())
                    ],
                    [
                        'label' => 'Riesgo de negocio económico (𝝓𝒆)',
                        'value' => FG::formatterx100p($worksheet->getCell('C40')->getOldCalculatedValue())
                    ]
                ),
                'results' => array(
                    [
                        'label' => 'Costo de Capital económico (Koa)',
                        'value' => FG::formatterx100p($worksheet->getCell('C27')->getOldCalculatedValue())                
                    ]
                )
            );

            $debt = array(
                'parameters' => array(
                    [
                        'label' => 'Spread de la deuda (CR)',
                        'value' => FG::formatterx100p($worksheet->getCell('C31')->getOldCalculatedValue())
                                    
                    ],
                    [
                        'label' => 'Impuestos del sector (T)',
                        'value' => FG::formatterx100p($worksheet->getCell('C19')->getOldCalculatedValue())
                    ],
                ),
                'results' => array(
                    [
                        'label' => 'Costo de la deuda (Kd)',
                        'value' => FG::formatterx100p($worksheet->getCell('C32')->getOldCalculatedValue())
                    ],
                    [
                        'label' => 'Costo de la deuda después de impuestos Kd(1-T)',
                        'value' => FG::formatterx100p($worksheet->getCell('C33')->getOldCalculatedValue())
                    ]
                )                   
            );

            $graph['general'] = array(
                'groups' => [
                    [ 'label' => 'rf', 'index' => 'value1', 'color' =>  $this->colors['one']  ],
                    [ 'label' => 'CR', 'index' => 'value4', 'color' =>  $this->colors['four'] ],
                    [ 'label' => '𝝓𝒆', 'index' => 'value3', 'color' =>  $this->colors['three'] ],
                    [ 'label' => '𝝓𝒇', 'index' => 'value2', 'color' =>  $this->colors['two'] ],
                ],
                'items' => [
                    [
                        'value1' => FG::formatterx100val($worksheet->getCell('C9')->getOldCalculatedValue()),
                        'value4' => 0,
                        'value3' => 0,
                        'value2' => FG::formatterx100val($worksheet->getCell('C39')->getOldCalculatedValue()),
                        'label' => 'Costo de capital financiero',
                        'title' => 'Ke = ' . FG::formatterx100p($worksheet->getCell('C23')->getOldCalculatedValue()),
                        'value' => FG::formatterx100val($worksheet->getCell('C23')->getOldCalculatedValue())
                    ],
                    [
                        'value1' => FG::formatterx100val($worksheet->getCell('C9')->getOldCalculatedValue()),
                        'value4' => 0,
                        'value3' => FG::formatterx100val($worksheet->getCell('C40')->getOldCalculatedValue()),
                        'value2' => 0,
                        'label' => 'Costo de capital económico',
                        'title' => 'Koa = ' . FG::formatterx100p($worksheet->getCell('C27')->getOldCalculatedValue()),
                        'value' => FG::formatterx100val($worksheet->getCell('C27')->getOldCalculatedValue())
                    ],
                    [
                        'value1' => FG::formatterx100val($worksheet->getCell('C9')->getOldCalculatedValue()),
                        'value4' => FG::formatterx100val($worksheet->getCell('C31')->getOldCalculatedValue()),
                        'value3' => 0,
                        'value2' => 0,
                        'label' => 'Costo de la deuda',
                        'title' => 'Kd = ' . FG::formatterx100p($worksheet->getCell('C32')->getOldCalculatedValue()),
                        'value' => FG::formatterx100val($worksheet->getCell('C32')->getOldCalculatedValue())
                    ],
                ]
            );

            $rsp['success'] = true;
            $rsp['data'] = compact('graph', 'general', 'finance', 'economic', 'debt');
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function initAverageDeveloped($args) {
        $rsp = FG::responseDefault();
        try {
            extract($args);
            if(!$filename) {
                throw new \Exception('File of user no encontred');
            }
            $sheetname = 'Calculo K';
            $graph = array('general' => []);

            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            $reader->setLoadSheetsOnly($sheetname); 
            $spreadsheet = $reader->load($filename);
            $worksheet = $spreadsheet->getActiveSheet();

            $general = array(
                'parameters' => array(
                    [
                        'label'   => 'Porcentaje de deuda (%D)',
                        'value'   => FG::formatterx100p($worksheet->getCell('C16')->getOldCalculatedValue()),
                        'comment' => 'El porcentaje de deuda y capital son un promedio del sector en el NYSE'
                    ],
                    [
                        'label' => 'Porcentaje de capital (%C)',
                        'value' => FG::formatterx100p($worksheet->getCell('C17')->getOldCalculatedValue())    
                    ],
                    [
                        'label' => 'Costo de capital financiero (Ke)',
                        'value' => FG::formatterx100p($worksheet->getCell('C23')->getOldCalculatedValue())    
                    ],
                    [
                        'label' => 'Costo de la deuda después de impuestos Kd(1-T)',
                        'value' => FG::formatterx100p($worksheet->getCell('C33')->getOldCalculatedValue())    
                    ]
                ),
                'results' => array(
                    [
                        'label' => 'CPPC',
                        'value' => FG::formatterx100p($worksheet->getCell('C36')->getOldCalculatedValue())    
                    ]
                ),
                'comparations' => array(
                    [
                        'label' => 'Ratio Deuda/Capital (D/C)',
                        'value' => FG::numberformat($worksheet->getCell('C18')->getOldCalculatedValue())
                    ],
                    [
                        'label' => 'Costo de capital económico (Koa)',
                        'value' => FG::formatterx100p($worksheet->getCell('C27')->getOldCalculatedValue())
                    ]
                )
            );


            $graph['general'] = array(
                [
                    'x' => 0,
                    'y' => FG::formatterx100val($worksheet->getCell('C33')->getOldCalculatedValue()),
                    'label' => 'Kd(1-T)',

                    'x1' => 0,
                    'x2' => FG::numberFormat($worksheet->getCell('C18')->getOldCalculatedValue()),
                    'y1' => FG::formatterx100val($worksheet->getCell('C33')->getOldCalculatedValue()),
                    'y2' => FG::formatterx100val($worksheet->getCell('C33')->getOldCalculatedValue()),
                    'line' => 1
                ],
                [
                    'x' => 0,
                    'y' => FG::formatterx100val($worksheet->getCell('C27')->getOldCalculatedValue()),
                    'label' => 'Koa',

                    'x1' => 0,
                    'x2' => FG::numberFormat($worksheet->getCell('C18')->getOldCalculatedValue()),
                    'y1' => FG::formatterx100val($worksheet->getCell('C27')->getOldCalculatedValue()),
                    'y2' => FG::formatterx100val($worksheet->getCell('C27')->getOldCalculatedValue()),
                    'line' => 2
                ],
                [
                    'x' => FG::numberFormat($worksheet->getCell('C18')->getOldCalculatedValue()),
                    'y' => FG::formatterx100val($worksheet->getCell('C23')->getOldCalculatedValue()),
                    'label' => 'Ke',

                    'x1' => 0,
                    'x2' => FG::numberFormat($worksheet->getCell('C18')->getOldCalculatedValue()),
                    'y1' => FG::formatterx100val($worksheet->getCell('C27')->getOldCalculatedValue()),
                    'y2' => FG::formatterx100val($worksheet->getCell('C23')->getOldCalculatedValue()),
                    'line' => 1
                ],
                [
                    'x' => FG::numberFormat($worksheet->getCell('C18')->getOldCalculatedValue()),
                    'y' => FG::formatterx100val($worksheet->getCell('C36')->getOldCalculatedValue()),
                    'label' => 'CPPC',

                    'x1' => 0,
                    'x2' => FG::numberFormat($worksheet->getCell('C18')->getOldCalculatedValue()),
                    'y1' => FG::formatterx100val($worksheet->getCell('C27')->getOldCalculatedValue()),
                    'y2' => FG::formatterx100val($worksheet->getCell('C36')->getOldCalculatedValue()),
                    'line' => 1
                ]
            );

            $rsp['success'] = true;
            $rsp['data'] = compact('graph', 'general');
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function initStructureEmerging($args) {
        $rsp = FG::responseDefault();
        try {
            extract($args);
            if(!$filename) {
                throw new \Exception('File of user no encontred');
            }
            $sheetname = 'Calculo K';
            $sheetname_embi = 'Embi';
            $graph = array('general' => [], 'riesgo' => []);

            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            $reader->setLoadSheetsOnly($sheetname); 
            $spreadsheet = $reader->load($filename);
            $worksheet = $spreadsheet->getActiveSheet();

            $country = $worksheet->getCell('F5')->getValue();
            $rowCountry = array();

            $general = array(
                [
                    'label' => 'Devaluación Esperada',
                    'value' => FG::formatterx100val($worksheet->getCell('F6')->getOldCalculatedValue()),
                    'input' => 1
                ],
                [
                    'label'   => ' Riesgo país',
                    'value'   => FG::formatterx100p($worksheet->getCell('F8')->getOldCalculatedValue()),
                    'input'   => 0,
                    'comment' => 'Calculado con el Diferencial de Rendimientos del Índice de Bonos de Mercados Emergentes (EMBIG)'
                ],
                [
                    'label' => 'Tasa impositiva',
                    'value' => FG::formatterx100p($worksheet->getCell('F19')->getOldCalculatedValue()),
                    'input' => 0
                ]
            );

            $finance = array(
                'deuda' => [
                    'label' => ' Porcentaje de deuda (%D)',
                    'value' => FG::formatterx100val($worksheet->getCell('F16')->getOldCalculatedValue())
                ],
                'capital' => [
                    'label' => 'Porcentaje de Capital (%C)',
                    'value' => FG::formatterx100val($worksheet->getCell('F17')->getOldCalculatedValue())
                ]
            );

            $graph['general'] = array();

            $reader->setLoadSheetsOnly($sheetname_embi); 
            $spreadsheet = $reader->load($filename);
            $worksheet = $spreadsheet->getActiveSheet();
            $countries = array();
            $points = array();
            $lastRow = $worksheet->getHighestRow();
            for ($row = 2; $row <= $lastRow; $row++) {
                $c = $worksheet->getCell('A'.$row)->getValue();
                if ($c) {
                    array_push($countries, $c);
                }
            }
           
            $lastColumn = $worksheet->getHighestColumn();
            $lastColumn ++;
            $labels = array();
            $values = array();
            for ($row = 1; $row <= 1; $row++) {
                for ($col = 'C'; $col != $lastColumn ; ++$col) {
                    array_push($labels, $worksheet->getCell($col.$row)->getValue());
                } 
            }
            for ($row = 1; $row <= $lastRow; $row++) {
                $c = $worksheet->getCell('A'.$row)->getValue();
                if ($c == $country) {
                    for ($col = 'C'; $col != $lastColumn ; ++$col) {
                        array_push($values, $worksheet->getCell($col.$row)->getValue());
                    }
                    break;
                }
            }
            $filteredLabel = array();
            $filteredValue = array();
            $index = 1;
            foreach ($labels as $key => $value) {
                if (($index%3) == 0 || $index == 1) {
                    array_push($filteredLabel, $value);
                    array_push($filteredValue, $values[$key]);
                }
                $index++;
            }
            
            $labels = $filteredLabel;
            $values = $filteredValue;
            $mydata = array();
            for ($i=0; $i < count($labels); $i++) { 
                array_push($mydata, ['label'=>$labels[$i], 'value'=>$values[$i]]);
            }
            $graph['riesgo'] = ['data'=>$mydata, 'title'=>$country];
            
            $rsp['success'] = true;
            $rsp['data'] = compact('graph', 'general', 'finance', 'economic', 'countries', 'country');
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function initParameterEmerging($args) {
        $rsp = FG::responseDefault();
        try {
            extract($args);
            if(!$filename) {
                throw new \Exception('File of user no encontred');
            }
            $sheetname = 'Calculo K';
            $graph = array('general' => []);

            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            $reader->setLoadSheetsOnly($sheetname); 
            $spreadsheet = $reader->load($filename);
            $worksheet = $spreadsheet->getActiveSheet();

            $general = array();

            $finance = array(
                'parameters' => array(
                    [
                        'label' => 'Beta apalancado (Ke)',
                        'value' => FG::numberformat($worksheet->getCell('F20')->getOldCalculatedValue())
                    ],
                    [
                        'label' => 'Riesgo de negocio financiero (𝝓𝒇)',
                        'value' => FG::formatterx100p($worksheet->getCell('F39')->getOldCalculatedValue())
                    ]
                ),
                'results' => array(
                    [
                        'label' => 'Costo de capital financiero (Ke)',
                        'value' => FG::formatterx100p($worksheet->getCell('F23')->getOldCalculatedValue())    
                    ]
                )
            );

            $economic = array(
                'parameters' => array(
                    [
                        'label' => 'Beta desapalancado (Boa)',
                        'value' => FG::numberformat($worksheet->getCell('F26')->getOldCalculatedValue())
                    ],
                    [
                        'label' => 'Riesgo de negocio económico (𝝓𝒆)',
                        'value' => FG::formatterx100p($worksheet->getCell('F40')->getOldCalculatedValue())
                    ]
                ),
                'results' => array(
                    [
                        'label' => 'Costo de Capital económico (Koa)',
                        'value' => FG::formatterx100p($worksheet->getCell('F27')->getOldCalculatedValue())                
                    ]
                )
            );

            $debt = array(
                'parameters' => array(
                    [
                        'label' => 'Spread de la deuda (CR)',
                        'value' => FG::formatterx100p($worksheet->getCell('C31')->getOldCalculatedValue())
                    ],
                    [
                        'label' => 'Impuestos del sector (T)',
                        'value' => FG::formatterx100p($worksheet->getCell('F19')->getOldCalculatedValue())
                                    
                    ],
                ),
                'results' => array(
                    [
                        'label' => ' Costo de la deuda (Kd)',
                        'value' => FG::formatterx100p($worksheet->getCell('F32')->getOldCalculatedValue())
                    ],
                    [
                        'label' => 'Costo de la deuda después de    impuestos Kd(1-T)',
                        'value' => FG::formatterx100p($worksheet->getCell('F33')->getOldCalculatedValue())
                    ]
                )                
            );

            $graph['general'] = array(
                'groups' => [
                    [ 
                        'label' => 'rf', 
                        'index' => 'value1', 
                        'color' =>  $this->colors['one'] 
                    ],
                    [ 
                        'label' => '𝝓𝒇', 
                        'index' => 'value2',
                        'color' =>  $this->colors['four'] 
                    ],
                    [ 
                        'label' => '𝝓𝒆', 
                        'index' => 'value3', 
                        'color' =>  $this->colors['five'] 
                    ],
                    [ 
                        'label' => 'CR', 
                        'index' => 'value4', 
                        'color' =>  $this->colors['six'] 
                    ],
                    [ 
                        'label' => 'Riesgo país', 
                        'index' => 'value5', 
                        'color' =>  $this->colors['three'] 
                    ]
                ],
                'items' => [
                    [
                        'value1' => FG::formatterx100val($worksheet->getCell('C9')->getOldCalculatedValue()),
                        'value2' => FG::formatterx100p($worksheet->getCell('F39')->getOldCalculatedValue()),
                        'value3' => 0,
                        'value4' => 0,
                        'value5' => FG::formatterx100val($worksheet->getCell('F8')->getOldCalculatedValue()),
                        'label' => 'Costo de capital financiero',
                        'title' => 'Ke = ' . FG::formatterx100p($worksheet->getCell('F23')->getOldCalculatedValue()),
                        'value' => FG::formatterx100val($worksheet->getCell('F23')->getOldCalculatedValue())
                    ],
                    [
                        'value1' => FG::formatterx100val($worksheet->getCell('C9')->getOldCalculatedValue()),
                        'value2' => 0,
                        'value3' => FG::formatterx100p($worksheet->getCell('F40')->getOldCalculatedValue()),
                        'value4' => 0,
                        'value5' => FG::formatterx100val($worksheet->getCell('F8')->getOldCalculatedValue()),
                        'label' => 'Costo de capital económico',
                        'title' => 'Koa = ' . FG::formatterx100p($worksheet->getCell('F27')->getOldCalculatedValue()),
                        'value' => FG::formatterx100val($worksheet->getCell('F23')->getOldCalculatedValue())
                    ],
                    [
                        'value1' => FG::formatterx100val($worksheet->getCell('C9')->getOldCalculatedValue()),
                        'value2' => 0,
                        'value3' => 0,
                        'value4' => FG::formatterx100val($worksheet->getCell('C31')->getOldCalculatedValue()),
                        'value5' => FG::formatterx100val($worksheet->getCell('F8')->getOldCalculatedValue()),
                        'label' => 'Costo de la deuda',
                        'title' => 'Kd = ' . FG::formatterx100p($worksheet->getCell('F32')->getOldCalculatedValue()),
                        'value' => FG::formatterx100val($worksheet->getCell('F23')->getOldCalculatedValue())
                    ],
                ]
            );

            $rsp['success'] = true;
            $rsp['data'] = compact('graph', 'general', 'finance', 'economic', 'debt');
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function initAverageEmerging($args) {
        $rsp = FG::responseDefault();
        try {
            extract($args);
            if(!$filename) {
                throw new \Exception('File of user no encontred');
            }
            $sheetname = 'Calculo K';
            $graph = array('general' => []);

            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            $reader->setLoadSheetsOnly($sheetname); 
            $spreadsheet = $reader->load($filename);
            $worksheet = $spreadsheet->getActiveSheet();

            $general = array(
                'parameters' => array(
                    [
                        'label'   => 'Porcentaje de deuda (%D)',
                        'value' => FG::formatterx100p($worksheet->getCell('F16')->getOldCalculatedValue())
                    ],
                    [
                        'label' => 'Porcentaje de capital (%C)',
                        'value' => FG::formatterx100p($worksheet->getCell('F17')->getOldCalculatedValue())    
                    ],
                    [
                        'label' => 'Costo de capital financiero (Ke)',
                        'value' => FG::formatterx100p($worksheet->getCell('F23')->getOldCalculatedValue())    
                    ],
                    [
                        'label' => 'Costo de la deuda después de impuestos Kd(1-T)',
                        'value' => FG::formatterx100p($worksheet->getCell('F33')->getOldCalculatedValue())    
                    ]
                ),
                'results' => array(
                    [
                        'label' => 'CPPC',
                        'value' => FG::formatterx100p($worksheet->getCell('F36')->getOldCalculatedValue())    
                    ]
                ),
                'comparations' => array(
                    [
                        'label' => 'Ratio Deuda/Capital (D/C)',
                        'value' => FG::numberformat($worksheet->getCell('F18')->getOldCalculatedValue())
                    ],
                    [
                        'label' => 'Costo de capital económico (Koa)',
                        'value' => FG::formatterx100p($worksheet->getCell('F27')->getOldCalculatedValue())
                    ]
                )
            );

            $graph['general'] = array(
                [
                    'x' => 0,
                    'y' => FG::formatterx100val($worksheet->getCell('F33')->getOldCalculatedValue()),
                    'label' => 'Kd(1-T)',

                    'x1' => 0,
                    'x2' => FG::numberFormat($worksheet->getCell('F18')->getOldCalculatedValue()),
                    'y1' => FG::formatterx100val($worksheet->getCell('F33')->getOldCalculatedValue()),
                    'y2' => FG::formatterx100val($worksheet->getCell('F33')->getOldCalculatedValue()),
                    'line' => 1
                ],
                [
                    'x' => 0,
                    'y' => FG::formatterx100val($worksheet->getCell('F27')->getOldCalculatedValue()),
                    'label' => 'Koa',

                    'x1' => 0,
                    'x2' => FG::numberFormat($worksheet->getCell('F18')->getOldCalculatedValue()),
                    'y1' => FG::formatterx100val($worksheet->getCell('F27')->getOldCalculatedValue()),
                    'y2' => FG::formatterx100val($worksheet->getCell('F27')->getOldCalculatedValue()),
                    'line' => 2
                ],
                [
                    'x' => FG::numberFormat($worksheet->getCell('F18')->getOldCalculatedValue()),
                    'y' => FG::formatterx100val($worksheet->getCell('F23')->getOldCalculatedValue()),
                    'label' => 'Ke',

                    'x1' => 0,
                    'x2' => FG::numberFormat($worksheet->getCell('F18')->getOldCalculatedValue()),
                    'y1' => FG::formatterx100val($worksheet->getCell('F27')->getOldCalculatedValue()),
                    'y2' => FG::formatterx100val($worksheet->getCell('F23')->getOldCalculatedValue()),
                    'line' => 1
                ],
                [
                    'x' => FG::numberFormat($worksheet->getCell('F18')->getOldCalculatedValue()),
                    'y' => FG::formatterx100val($worksheet->getCell('F36')->getOldCalculatedValue()),
                    'label' => 'CPPC',

                    'x1' => 0,
                    'x2' => FG::numberFormat($worksheet->getCell('F18')->getOldCalculatedValue()),
                    'y1' => FG::formatterx100val($worksheet->getCell('F27')->getOldCalculatedValue()),
                    'y2' => FG::formatterx100val($worksheet->getCell('F36')->getOldCalculatedValue()),
                    'line' => 1
                ]
            );

            $rsp['success'] = true;
            $rsp['data'] = compact('graph', 'general');
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function initStructureCompany($args) {
        $rsp = FG::responseDefault();
        try {
            extract($args);
            if(!$filename) {
                throw new \Exception('File of user no encontred');
            }
            $sheetname = 'Calculo K';
            $sheetname_embi = 'Embi';
            $graph = array('general' => [], 'riesgo' => []);

            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            $reader->setLoadSheetsOnly($sheetname); 
            $spreadsheet = $reader->load($filename);
            $worksheet = $spreadsheet->getActiveSheet();

            $country = $worksheet->getCell('F5')->getValue();
            $n = (int)$worksheet->getCell('L6')->getValue();
            $d = (int)$worksheet->getCell('L7')->getValue();

            $d = $d == 1 ? 1 : ($n ? 0 : 1);
            $n = $n == 1 ? 1 : 0;

            $general = array(
                'label' => $worksheet->getCell('H7')->getValue(),
                'dolares' => [
                    'label' => $worksheet->getCell('I5')->getValue(),
                    'value' => $d == 1 ? FG::formatterx100val($worksheet->getCell('I7')->getValue()) : FG::formatterx100val($worksheet->getCell('I6')->getOldCalculatedValue()),
                    'currency' => $d
                ],
                'derivative' => [
                    'label' => $worksheet->getCell('J5')->getValue(),
                    'value' => $n == 1 ? FG::formatterx100val($worksheet->getCell('J6')->getOldCalculatedValue()) : FG::formatterx100val($worksheet->getCell('J7')->getOldCalculatedValue())
                ],
                'national' => [
                    'label' => $worksheet->getCell('K5')->getValue(),
                    'value' => $n == 1 ? FG::formatterx100val($worksheet->getCell('K6')->getValue()) : FG::formatterx100val($worksheet->getCell('K7')->getOldCalculatedValue()),
                    'currency' => $n
                ]
            );

            $prima = array(
                'label'   => 'Prima empresa (IRS)',
                'value'   => FG::formatterx100p($worksheet->getCell('I9')->getOldCalculatedValue()),
                'comment' => 'Diferencia entre el Costo de Deuda de Mercado Emergente y el de Mi Empresa (Dólares)'
            );

            $percentage = array(
                'debt' => [
                    'label' => $worksheet->getCell('H16')->getValue(),
                    'value' => FG::formatterx100val($worksheet->getCell('I16')->getValue())
                ],
                'capital' => [
                    'label' => $worksheet->getCell('H17')->getValue(),
                    'value' => FG::formatterx100val($worksheet->getCell('I17')->getOldCalculatedValue())
                ]
            );

            $valuePrima = FG::formatterx100val($worksheet->getCell('F32')->getOldCalculatedValue());
            $valueEmergente = FG::formatterx100val($worksheet->getCell('F32')->getOldCalculatedValue());
            $valueEmpresa = FG::formatterx100val($worksheet->getCell('I32')->getOldCalculatedValue());
            
            if ($valueEmergente > $valueEmpresa) {
                $valuePrima = FG::formatterx100val($worksheet->getCell('I32')->getOldCalculatedValue());
            }

            // $textPrima = FG::formatterx100val($worksheet->getCell('I9')->getOldCalculatedValue());
            // $textPrima = $textPrima > 0 ? $textPrima : ($textPrima * -1);

            $graph['deuda'] = array(
                [                    
                    'label' => 'MERCADO DESARROLLADO',
                    'value' => FG::formatterx100val($worksheet->getCell('C32')->getOldCalculatedValue()),
                    'color' =>  $this->colors['two'],
                    'prima' => 0,
                    'text'  => ''
                ],
                [                    
                    'label' => 'MERCADO EMERGENTE',
                    'value' => FG::formatterx100val($worksheet->getCell('F32')->getOldCalculatedValue()),
                    'color' =>  $this->colors['one'],
                    // 'prima' => FG::formatterx100val($worksheet->getCell('F32')->getOldCalculatedValue()),
                    'prima' => $valuePrima,
                    'text'  => ''
                ],
                [                    
                    'label' => 'EMPRESA',
                    'value' => FG::formatterx100val($worksheet->getCell('I32')->getOldCalculatedValue()),
                    'color' =>  $this->colors['three'],
                    'prima' => $valuePrima,
                    // 'text'  => 'IRS = '.$textPrima
                    // 'prima' => FG::formatterx100val($worksheet->getCell('F32')->getOldCalculatedValue()),
                    'text'  => 'IRS = '.FG::formatterx100val($worksheet->getCell('I9')->getOldCalculatedValue())
                ]
            );

            /*$graph['general'] = array(
                'groups' => [
                    [ 'label' => '%D', 'index' => 'value1', 'color' =>  $this->colors['three'] ],
                    [ 'label' => '%C', 'index' => 'value2', 'color' =>  $this->colors['two'] ]
                ],
                'items' => [
                    [
                        'value1' => FG::formatterx100val($worksheet->getCell('I16')->getValue()),
                        'value2' => FG::formatterx100val($worksheet->getCell('I17')->getOldCalculatedValue()),
                        'label' => 'EMPRESA'
                    ]
                ]
            );
            
            $reader->setLoadSheetsOnly($sheetname_embi); 
            $spreadsheet = $reader->load($filename);
            $worksheet = $spreadsheet->getActiveSheet();
            $countries = array();
            $points = array();
            $lastRow = $worksheet->getHighestRow();
            for ($row = 2; $row <= $lastRow; $row++) {
                $c = $worksheet->getCell('A'.$row)->getValue();
                if ($c) {
                    array_push($countries, $c);
                }
            }

            $lastColumn = $worksheet->getHighestColumn();
            $labels = array();
            $values = array();
            for ($row = 1; $row <= 1; $row++) {
                for ($col = 'C'; $col != $lastColumn ; ++$col) {
                    array_push($labels, $worksheet->getCell($col.$row)->getValue());
                } 
            }
            
            for ($row = 1; $row <= $lastRow; $row++) {
                $c = $worksheet->getCell('A'.$row)->getValue();
                if ($c == $country) {
                    for ($col = 'C'; $col != $lastColumn ; ++$col) {
                        array_push($values, $worksheet->getCell($col.$row)->getValue());
                    }
                    break;
                }
            }
            $mydata = array();
            for ($i=0; $i < count($labels); $i++) { 
                array_push($mydata, ['label'=>$labels[$i], 'value'=>$values[$i]]);
            }
            $graph['riesgo'] = $mydata;*/

            $rsp['success'] = true;
            $rsp['data'] = compact('graph', 'general', 'prima', 'percentage');
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function initParameterCompany($args) {
        $rsp = FG::responseDefault();
        try {
            extract($args);
            if(!$filename) {
                throw new \Exception('File of user no encontred');
            }
            $sheetname = 'Calculo K';
            $graph = array('general' => []);

            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            $reader->setLoadSheetsOnly($sheetname); 
            $spreadsheet = $reader->load($filename);
            $worksheet = $spreadsheet->getActiveSheet();

            $general = array();

            $finance = array(
                'parameters' => array(
                    [
                        'label' => 'Beta apalancado (Be)',
                        'value' => FG::numberformat($worksheet->getCell('I20')->getOldCalculatedValue())
                    ],
                    [
                        'label' => 'Riesgo de negocio financiero (𝝓𝒇)',
                        'value' => FG::formatterx100p($worksheet->getCell('I39')->getOldCalculatedValue())
                    ],
                    [
                        'label' => 'Prima Empresa (IRS)',
                        'value' => FG::formatterx100p($worksheet->getCell('I9')->getOldCalculatedValue())
                    ]
                ),
                'results' => array(
                    [
                        'label' => 'Costo de capital financiero (Ke)',
                        'value' => FG::formatterx100p($worksheet->getCell('I23')->getOldCalculatedValue())    
                    ]
                )
            );

            $economic = array(
                'parameters' => array(
                    [
                        'label' => 'Beta desapalancado (Boa)',
                        'value' => FG::numberformat($worksheet->getCell('F26')->getOldCalculatedValue())
                    ],
                    [
                        'label' => 'Riesgo de negocio económico (𝝓e)',
                        'value' => FG::formatterx100p($worksheet->getCell('I40')->getOldCalculatedValue())
                    ],
                    [
                        'label' => 'Prima Empresa (IRS)',
                        'value' => FG::formatterx100p($worksheet->getCell('I9')->getOldCalculatedValue())
                    ],
                ),
                'results' => array(
                    [
                        'label' => 'Costo de capital económico (Koa)',
                        'value' => FG::formatterx100p($worksheet->getCell('I27')->getOldCalculatedValue())                
                    ]
                )
            );

            $debt = array(
                'parameters' => array(
                    [
                        'label' => 'Spread de la deuda (CR)',                        
                        'value' => FG::formatterx100p($worksheet->getCell('C31')->getOldCalculatedValue())
                    ],
                    [
                        'label' => 'Impuestos del sector (T)',
                        'value' => FG::formatterx100p($worksheet->getCell('I19')->getOldCalculatedValue())                        
                    ],
                    [
                        'label' => 'Prima Empresa (IRS)',
                        'value' => FG::formatterx100p($worksheet->getCell('I9')->getOldCalculatedValue())
                    ],
                ),
                'results' => array(
                    [
                        'label' => 'Costo de la deuda (Kd)',
                        'value' => FG::formatterx100p($worksheet->getCell('I32')->getOldCalculatedValue())
                    ],
                    [
                        'label' => ' Costo de la deuda después de impuestos Kd(1-T)',
                        'value' => FG::formatterx100p($worksheet->getCell('I33')->getOldCalculatedValue())
                    ]
                )                
            );

            $prima = FG::formatterx100val($worksheet->getCell('I9')->getOldCalculatedValue());
            $costoFinanciero = FG::formatterx100val($worksheet->getCell('I23')->getOldCalculatedValue()) ;
            $costoEconomico = FG::formatterx100val($worksheet->getCell('I27')->getOldCalculatedValue()) ;
            $costoDeuda = FG::formatterx100val($worksheet->getCell('I32')->getOldCalculatedValue()) ;

            $graph['general'] = array(
                'groups' => [
                    [ 
                        'label' => 'rf', 
                        'index' => 'value1', 
                        'color' => $this->colors['one'],
                        'prima' => 1,
                        'measure' => 'measure',
                    ],
                    [ 
                        'label' => '𝝓𝒇', 
                        'index' => 'value2',
                        'color' => $this->colors['four'],
                        'prima' => 1,
                        'measure' => 'measure',
                    ],
                    [ 
                        'label' => '𝝓e', 
                        'index' => 'value3', 
                        'color' => $this->colors['five'],
                        'prima' => 1,
                        'measure' => 'measure',
                    ],
                    [ 
                        'label' => 'CR', 
                        'index' => 'value4', 
                        'color' => $this->colors['six'],
                        'prima' => 1,
                        'measure' => 'measure',

                    ],
                    [ 
                        'label' => 'Riesgo país', 
                        'index' => 'value5', 
                        'color' => $this->colors['three'],
                        'prima' => $prima,
                        'measure' => 'measure',
                    ],
                    [ 
                        'label' => 'IRS', 
                        'index' => 'value6', 
                        'color' => $this->colors['two'],
                        'prima' => 1,
                        'measure' => 'measure',
                    ],
                ],
                'items' => [
                    [
                        'value1' => FG::formatterx100val($worksheet->getCell('C9')->getOldCalculatedValue()),
                        'value2' =>  FG::formatterx100val($worksheet->getCell('I39')->getOldCalculatedValue()),
                        'value3' => 0,
                        'value4' => 0,
                        'value5' => FG::formatterx100val($worksheet->getCell('F8')->getOldCalculatedValue()),
                        'value6' => FG::formatterx100val($worksheet->getCell('I9')->getOldCalculatedValue()),
                        'measure'=> $prima >= 0 ? $costoFinanciero : ($costoFinanciero + $prima),
                        'label' => 'Costo de capital financiero',
                        'title' => 'Ke = ' . FG::formatterx100p($worksheet->getCell('I23')->getOldCalculatedValue()),
                        'value' => $costoFinanciero
                    ],
                    [
                        'value1' => FG::formatterx100val($worksheet->getCell('C9')->getOldCalculatedValue()),
                        'value2' => 0,
                        'value3' => FG::formatterx100val($worksheet->getCell('I40')->getOldCalculatedValue()),
                        'value4' => 0,
                        'value5' => FG::formatterx100val($worksheet->getCell('F8')->getOldCalculatedValue()),
                        'value6' => FG::formatterx100val($worksheet->getCell('I9')->getOldCalculatedValue()),
                        'measure'=> $prima >= 0 ? $costoEconomico : ($costoEconomico + $prima),
                        'label' => 'Costo de capital económico',
                        'title' => 'Koa = ' . FG::formatterx100p($worksheet->getCell('I27')->getOldCalculatedValue()),
                        'value' => $costoEconomico
                    ],
                    [
                        'value1' => FG::formatterx100val($worksheet->getCell('C9')->getOldCalculatedValue()),
                        'value2' => 0,
                        'value3' => 0,
                        'value4' => FG::formatterx100val($worksheet->getCell('C31')->getOldCalculatedValue()),
                        'value5' => FG::formatterx100val($worksheet->getCell('F8')->getOldCalculatedValue()),
                        'value6' => FG::formatterx100val($worksheet->getCell('I9')->getOldCalculatedValue()),
                        'measure'=> $prima >= 0 ? $costoDeuda : ($costoDeuda + $prima),
                        'label' => 'Costo de la deuda',
                        'title' => 'Kd = ' . FG::formatterx100p($worksheet->getCell('I32')->getOldCalculatedValue()),
                        'value' => $costoDeuda
                    ],
                ]
            );

            /*$ymax = 0;
            $groups = $graph['general']['groups'];
            $items = $graph['general']['items'];

            if (count($items) > 0) {
                $all = [];
                $except = ['label', 'title', 'value', 'measure'];
                for ($i = 0; $i < count($items); $i++) {
                    $object = $items[i];
                    $suma = 0;
                    foreach ($object as $property) {
                        if (!in_array($property, $except)) {
                            if ($object[$property] > 0) {
                                $suma = $suma + $object[$property];
                            }
                        }
                    }
                    array_push($all, $suma);
                }
                for ($i = 0; $i < count($all); $i++) {
                    $elem = $all[$i];
                    if ($elem > 0 && $elem > $ymax) {
                        $ymax = $elem;
                    }                   
                }
            }*/

            // var_dump($ymax);
            // exit;
//height:170px;

            $rsp['success'] = true;
            $rsp['data'] = compact('graph', 'general', 'finance', 'economic', 'debt');
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function initAverageDolaresCompany($args) {
        $rsp = FG::responseDefault();
        try {
            extract($args);
            if(!$filename) {
                throw new \Exception('File of user no encontred');
            }
            $sheetname = 'Calculo K';
            $graph = array('general' => []);

            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            $reader->setLoadSheetsOnly($sheetname); 
            $spreadsheet = $reader->load($filename);
            $worksheet = $spreadsheet->getActiveSheet();

            $general = array(
                'parameters' => array(
                    [
                        'label' => 'Porcentaje de deuda (%D)',
                        'value' => FG::formatterx100p($worksheet->getCell('I16')->getValue())
                    ],
                    [
                        'label' => 'Porcentaje de capital (%C)',
                        'value' => FG::formatterx100p($worksheet->getCell('I17')->getOldCalculatedValue())    
                    ],
                    [
                        'label' => 'Costo de capital financiero (Ke)',
                        'value' => FG::formatterx100p($worksheet->getCell('I23')->getOldCalculatedValue())    
                    ],
                    [
                        'label' => 'Costo de la deuda después de impuestos Kd(1-T)',
                        'value' => FG::formatterx100p($worksheet->getCell('I33')->getOldCalculatedValue())    
                    ]
                ),
                'results' => array(
                    [
                        'label' => 'CPPC',
                        'value' => FG::formatterx100p($worksheet->getCell('I36')->getOldCalculatedValue())    
                    ]
                ),
                'comparations' => array(
                    [
                        'label' => 'Ratio Deuda/Capital (D/C)',
                        'value' => FG::numberformat($worksheet->getCell('I18')->getOldCalculatedValue())
                    ],
                    [
                        'label' => 'Costo de capital económico (Koa)',
                        'value' => FG::formatterx100p($worksheet->getCell('I27')->getOldCalculatedValue())
                    ]
                )
            );

            $graph['general'] = array(
                [
                    'x' => 0,
                    'y' => FG::formatterx100val($worksheet->getCell('I33')->getOldCalculatedValue()),
                    'label' => 'Kd(1-T)',

                    'x1' => 0,
                    'x2' => FG::numberFormat($worksheet->getCell('I18')->getOldCalculatedValue()),
                    'y1' => FG::formatterx100val($worksheet->getCell('I33')->getOldCalculatedValue()),
                    'y2' => FG::formatterx100val($worksheet->getCell('I33')->getOldCalculatedValue()),
                    'line' => 1
                ],
                [
                    'x' => 0,
                    'y' => FG::formatterx100val($worksheet->getCell('I27')->getOldCalculatedValue()),
                    'label' => 'Koa',

                    'x1' => 0,
                    'x2' => FG::numberFormat($worksheet->getCell('I18')->getOldCalculatedValue()),
                    'y1' => FG::formatterx100val($worksheet->getCell('I27')->getOldCalculatedValue()),
                    'y2' => FG::formatterx100val($worksheet->getCell('I27')->getOldCalculatedValue()),
                    'line' => 2
                ],
                [
                    'x' => FG::numberFormat($worksheet->getCell('I18')->getOldCalculatedValue()),
                    'y' => FG::formatterx100val($worksheet->getCell('I23')->getOldCalculatedValue()),
                    'label' => 'Ke',

                    'x1' => 0,
                    'x2' => FG::numberFormat($worksheet->getCell('I18')->getOldCalculatedValue()),
                    'y1' => FG::formatterx100val($worksheet->getCell('I27')->getOldCalculatedValue()),
                    'y2' =>  FG::formatterx100val($worksheet->getCell('I23')->getOldCalculatedValue()),
                    'line' => 1
                ],
                [
                    'x' => FG::numberFormat($worksheet->getCell('I18')->getOldCalculatedValue()),
                    'y' => FG::formatterx100val($worksheet->getCell('I36')->getOldCalculatedValue()),
                    'label' => 'CPPC',

                    'x1' => 0,
                    'x2' => FG::numberFormat($worksheet->getCell('I18')->getOldCalculatedValue()),
                    'y1' => FG::formatterx100val($worksheet->getCell('I27')->getOldCalculatedValue()),
                    'y2' => FG::formatterx100val($worksheet->getCell('I36')->getOldCalculatedValue()),
                    'line' => 1
                ]
            );

            $rsp['success'] = true;
            $rsp['data'] = compact('graph', 'general');
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function initAverageNationalCompany($args) {
        $rsp = FG::responseDefault();
        try {
            extract($args);
            if(!$filename) {
                throw new \Exception('File of user no encontred');
            }
            $sheetname = 'Calculo K';
            $graph = array('general' => []);

            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            $reader->setLoadSheetsOnly($sheetname); 
            $spreadsheet = $reader->load($filename);
            $worksheet = $spreadsheet->getActiveSheet();

            $general = array(
                'parameters' => array(
                    [
                        'label' => 'Porcentaje de deuda (%D)',
                        'value' => FG::formatterx100p($worksheet->getCell('I16')->getValue())
                    ],
                    [
                        'label' => 'Porcentaje de capital (%C)',
                        'value' => FG::formatterx100p($worksheet->getCell('I17')->getOldCalculatedValue())    
                    ],
                    [
                        'label' => 'Costo de capital financiero (Ke)',
                        'value' => FG::formatterx100p($worksheet->getCell('J23')->getOldCalculatedValue())    
                    ],
                    [
                        'label' => 'Costo de la deuda después de impuestos Kd(1-T)',
                        'value' => FG::formatterx100p($worksheet->getCell('J33')->getOldCalculatedValue())    
                    ],
                ),
                'results' => array(
                    [
                        'label' => 'CPPC',
                        'value' => FG::formatterx100p($worksheet->getCell('J36')->getOldCalculatedValue())    
                    ]
                ),
                'comparations' => array(
                    [
                        'label' => 'Ratio Deuda/Capital (D/C)',
                        'value' => FG::numberformat($worksheet->getCell('I18')->getOldCalculatedValue())
                    ],
                    [
                        'label' => 'Costo de capital económico (Koa)',
                        'value' => FG::formatterx100p($worksheet->getCell('J27')->getOldCalculatedValue())
                    ]
                )
            );

            $graph['general'] = array(
                [
                    'x' => 0,
                    'y' => FG::formatterx100val($worksheet->getCell('J33')->getOldCalculatedValue()),
                    'label' => 'Kd(1-T)',

                    'x1' => 0,
                    'x2' => FG::numberFormat($worksheet->getCell('I18')->getOldCalculatedValue()),
                    'y1' => FG::formatterx100val($worksheet->getCell('J33')->getOldCalculatedValue()),
                    'y2' => FG::formatterx100val($worksheet->getCell('J33')->getOldCalculatedValue()),
                    'line' => 1
                ],
                [
                    'x' => 0,
                    'y' => FG::formatterx100val($worksheet->getCell('J27')->getOldCalculatedValue()),
                    'label' => 'Koa',

                    'x1' => 0,
                    'x2' => FG::numberFormat($worksheet->getCell('I18')->getOldCalculatedValue()),
                    'y1' => FG::formatterx100val($worksheet->getCell('J27')->getOldCalculatedValue()),
                    'y2' => FG::formatterx100val($worksheet->getCell('J27')->getOldCalculatedValue()),
                    'line' => 2
                ],
                [
                    'x' => FG::numberFormat($worksheet->getCell('I18')->getOldCalculatedValue()),
                    'y' => FG::formatterx100val($worksheet->getCell('J23')->getOldCalculatedValue()),
                    'label' => 'Ke',

                    'x1' => 0,
                    'x2' => FG::numberFormat($worksheet->getCell('I18')->getOldCalculatedValue()),
                    'y1' => FG::formatterx100val($worksheet->getCell('J27')->getOldCalculatedValue()),
                    'y2' => FG::formatterx100val($worksheet->getCell('J23')->getOldCalculatedValue()),
                    'line' => 1
                ],
                [
                    'x' => FG::numberFormat($worksheet->getCell('I18')->getOldCalculatedValue()),
                    'y' => FG::formatterx100val($worksheet->getCell('J36')->getOldCalculatedValue()),
                    'label' => 'CPPC',

                    'x1' => 0,
                    'x2' => FG::numberFormat($worksheet->getCell('I18')->getOldCalculatedValue()),
                    'y1' => FG::formatterx100val($worksheet->getCell('J27')->getOldCalculatedValue()),
                    'y2' => FG::formatterx100val($worksheet->getCell('J36')->getOldCalculatedValue()),
                    'line' => 1
                ]
            );

            $rsp['success'] = true;
            $rsp['data'] = compact('graph', 'general');
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function initReportCompany($args) {
        $rsp = FG::responseDefault();
        try {
            extract($args);
            if(!$filename) {
                throw new \Exception('File of user no encontred');
            }
            $sheetname = 'Calculo K';
            $graph = array('general' => []);

            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            $reader->setLoadSheetsOnly($sheetname); 
            $spreadsheet = $reader->load($filename);
            $worksheet = $spreadsheet->getActiveSheet();

            $general = array(
                [
                    'label' => $worksheet->getCell('B8')->getValue(),
                    'value' => FG::formatterx100p($worksheet->getCell('C8')->getOldCalculatedValue())
                ],
                [
                    'label' => $worksheet->getCell('B9')->getValue(),
                    'value' => FG::formatterx100p($worksheet->getCell('C9')->getOldCalculatedValue())
                ],
                [
                    'label' => $worksheet->getCell('E8')->getValue(),
                    'value' => FG::formatterx100p($worksheet->getCell('F8')->getOldCalculatedValue())
                ]
            );

            $parameters = array(
                [
                    'label' => $worksheet->getCell('B9')->getValue(),
                    'value' => FG::formatterx100p($worksheet->getCell('C9')->getOldCalculatedValue())
                ],
                [
                    'label' => $worksheet->getCell('H16')->getValue(),
                    'value' => FG::formatterx100p($worksheet->getCell('I16')->getValue())                                
                ],
                [
                    'label' => $worksheet->getCell('H17')->getValue(),
                    'value' => FG::formatterx100p($worksheet->getCell('I17')->getOldCalculatedValue())                                
                ],
                [
                    'label' => $worksheet->getCell('H20')->getValue(),
                    'value' => FG::numberFormat($worksheet->getCell('I20')->getOldCalculatedValue())
                ],
                [
                    'label' => $worksheet->getCell('B26')->getValue(),
                    'value' => FG::numberFormat($worksheet->getCell('C26')->getOldCalculatedValue())
                ],
                [
                    'label' => $worksheet->getCell('E6')->getValue(),
                    'value' => FG::formatterx100p($worksheet->getCell('F6')->getOldCalculatedValue())
                ]                   
            );

            $percentages = array(
                [
                    'label' => 'Costo de capital económico (Koa)',
                    'dolar' => FG::formatterx100p($worksheet->getCell('I27')->getOldCalculatedValue()),
                    'national' => FG::formatterx100p($worksheet->getCell('J27')->getOldCalculatedValue()),
                ],
                [
                    'label' => ' Costo de capital financiero (Ke)',
                    'dolar' => FG::formatterx100p($worksheet->getCell('I23')->getOldCalculatedValue()),
                    'national' => FG::formatterx100p($worksheet->getCell('J23')->getOldCalculatedValue()),
                ],
                [
                    'label' => 'Costo de la deuda después de impuestos Kd(1-T)',
                    'dolar' => FG::formatterx100p($worksheet->getCell('I33')->getOldCalculatedValue()),
                    'national' => FG::formatterx100p($worksheet->getCell('J33')->getOldCalculatedValue()),
                ],
                [
                    'label' => 'Costo Promedio Ponderado de Capital (CPPC)',
                    'dolar' => FG::formatterx100p($worksheet->getCell('I36')->getOldCalculatedValue()),
                    'national' => FG::formatterx100p($worksheet->getCell('J36')->getOldCalculatedValue()),
                ]
            );

            $graph['general'] = array(
                'groups' => [
                    [ 'label' => 'Ke', 'index' => 'value1', 'color' => $this->colors['six'] ],
                    [ 'label' => 'Koa', 'index' => 'value2', 'color' => $this->colors['two'] ],
                    [ 'label' => 'Kd(1-T)', 'index' => 'value3', 'color' => $this->colors['one'] ],
                    [ 'label' => 'CPPC', 'index' => 'value4', 'color' => $this->colors['four'] ]
                ],
                'items' => [
                    [
                        'value1' => FG::formatterx100val($worksheet->getCell('I23')->getOldCalculatedValue()),
                        'value2' => FG::formatterx100val($worksheet->getCell('I27')->getOldCalculatedValue()),
                        'value3' => FG::formatterx100val($worksheet->getCell('I33')->getOldCalculatedValue()),
                        'value4' => FG::formatterx100val($worksheet->getCell('I36')->getOldCalculatedValue()),
                        'label' => 'Dolares'
                    ],
                    [
                        'value1' => FG::formatterx100val($worksheet->getCell('J23')->getOldCalculatedValue()),
                        'value2' => FG::formatterx100val($worksheet->getCell('J27')->getOldCalculatedValue()),
                        'value3' => FG::formatterx100val($worksheet->getCell('J33')->getOldCalculatedValue()),
                        'value4' => FG::formatterx100val($worksheet->getCell('J36')->getOldCalculatedValue()),
                        'label' => 'Moneda Nacional'
                    ]
                ]
            );
            $rsp['success'] = true;
            $rsp['data'] = compact('graph', 'general', 'parameters', 'percentages');
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function initComparation($args) {
        $rsp = FG::responseDefault();
        try {
            extract($args);
            if(!$filename) {
                throw new \Exception('File of user no encontred');
            }
            $sheetname = 'Calculo K';
            $graph = array('general' => []);

            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            $reader->setLoadSheetsOnly($sheetname); 
            $spreadsheet = $reader->load($filename);
            $worksheet = $spreadsheet->getActiveSheet();

            $general = array();

            $parameters = array();

            $percentages = array(
                [
                    'label'         => 'Costo de Capital Financiero (Ke)',
                    'desarrollado'  => FG::formatterx100p($worksheet->getCell('C23')->getOldCalculatedValue()),
                    'emergente'     => FG::formatterx100p($worksheet->getCell('C27')->getOldCalculatedValue()),
                    'dolar'         => FG::formatterx100p($worksheet->getCell('C33')->getOldCalculatedValue()),
                    'nacional'      => FG::formatterx100p($worksheet->getCell('C36')->getOldCalculatedValue())
                ],
                [
                    'label'         => 'Costo de Capital Económico (Koa)',
                    'desarrollado'  => FG::formatterx100p($worksheet->getCell('C23')->getOldCalculatedValue()),
                    'emergente'     => FG::formatterx100p($worksheet->getCell('F27')->getOldCalculatedValue()),
                    'dolar'         => FG::formatterx100p($worksheet->getCell('F33')->getOldCalculatedValue()),
                    'nacional'      => FG::formatterx100p($worksheet->getCell('F36')->getOldCalculatedValue())

                ],
                [
                    'label'         => 'Costo de deuda despúes de impuestos (Kd(1-T))',
                    'desarrollado'  => FG::formatterx100p($worksheet->getCell('I23')->getOldCalculatedValue()),
                    'emergente'     => FG::formatterx100p($worksheet->getCell('I27')->getOldCalculatedValue()),
                    'dolar'         => FG::formatterx100p($worksheet->getCell('I33')->getOldCalculatedValue()),
                    'nacional'      => FG::formatterx100p($worksheet->getCell('I36')->getOldCalculatedValue())
                ],
                [
                    'label'         => 'Costo Promedio Ponderado de Capital (CPPC)',
                    'desarrollado'  => FG::formatterx100p($worksheet->getCell('J23')->getOldCalculatedValue()),
                    'emergente'     => FG::formatterx100p($worksheet->getCell('J27')->getOldCalculatedValue()),
                    'dolar'         => FG::formatterx100p($worksheet->getCell('J33')->getOldCalculatedValue()),
                    'nacional'      => FG::formatterx100p($worksheet->getCell('J36')->getOldCalculatedValue())
                ]
            );

            $graph['general'] = array(
                'groups' => [
                    [ 
                        'label' => 'Ke', 
                        'index' => 'value1', 
                        'color' => $this->colors['six'] 
                    ],
                    [ 
                        'label' => 'Koa', 
                        'index' => 'value2', 
                        'color' => $this->colors['two'] 
                    ],
                    [ 
                        'label' => 'Kd(1-T)', 
                        'index' => 'value3', 
                        'color' => $this->colors['one'] 
                    ],
                    [ 
                        'label' => 'CPPC', 
                        'index' => 'value4', 
                        'color' => $this->colors['four'] 
                    ]
                ],
                'items' => [
                    [
                        'value1' => FG::formatterx100val($worksheet->getCell('C23')->getOldCalculatedValue()),
                        'value2' => FG::formatterx100val($worksheet->getCell('C27')->getOldCalculatedValue()),
                        'value3' => FG::formatterx100val($worksheet->getCell('C33')->getOldCalculatedValue()),
                        'value4' => FG::formatterx100val($worksheet->getCell('C36')->getOldCalculatedValue()),
                        'label' => 'Mercado Desarrollado'
                    ],
                    [
                        'value1' => FG::formatterx100val($worksheet->getCell('F23')->getOldCalculatedValue()),
                        'value2' => FG::formatterx100val($worksheet->getCell('F27')->getOldCalculatedValue()),
                        'value3' => FG::formatterx100val($worksheet->getCell('F33')->getOldCalculatedValue()),
                        'value4' => FG::formatterx100val($worksheet->getCell('F36')->getOldCalculatedValue()),
                        'label' => 'Mercado Emergente'
                    ],
                    [
                        'value1' => FG::formatterx100val($worksheet->getCell('I23')->getOldCalculatedValue()),
                        'value2' => FG::formatterx100val($worksheet->getCell('I27')->getOldCalculatedValue()),
                        'value3' => FG::formatterx100val($worksheet->getCell('I33')->getOldCalculatedValue()),
                        'value4' => FG::formatterx100val($worksheet->getCell('I36')->getOldCalculatedValue()),
                        'label' => 'Mi Empresa - Dolares'
                    ],
                    [
                        'value1' => FG::formatterx100val($worksheet->getCell('J23')->getOldCalculatedValue()),
                        'value2' => FG::formatterx100val($worksheet->getCell('J27')->getOldCalculatedValue()),
                        'value3' => FG::formatterx100val($worksheet->getCell('J33')->getOldCalculatedValue()),
                        'value4' => FG::formatterx100val($worksheet->getCell('J36')->getOldCalculatedValue()),
                        'label' => 'Mi Empresa - Moneda Nacional'
                    ]
                ]
            );
            $rsp['success'] = true;
            $rsp['data'] = compact('graph', 'general', 'parameters', 'percentages');
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function initReportSectorial($args) {
        $rsp = FG::responseDefault();
        try {
            extract($args);
            if(!$filename) {
                throw new \Exception('File of user no encontred');
            }
            $sheetname = 'Calculo K';
            $graph = array('general' => []);

            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            $reader->setLoadSheetsOnly($sheetname); 
            $spreadsheet = $reader->load($filename);
            $worksheet = $spreadsheet->getActiveSheet();

            $general = array();

            $parameters = array(
                [
                    'label'  => 'Tasa libre de riesgo',
                    'value'  => FG::formatterx100p($worksheet->getCell('C9')->getOldCalculatedValue()),
                    'value2' => FG::formatterx100p($worksheet->getCell('C9')->getOldCalculatedValue())
                ],
                [
                    'label'  => 'Porcentaje de Deuda',
                    'value'  => FG::formatterx100p($worksheet->getCell('C16')->getOldCalculatedValue()),
                    'value2' => FG::formatterx100p($worksheet->getCell('F16')->getOldCalculatedValue())
                ],
                [
                    'label'  => 'Porcentaje de Capital',
                    'value'  => FG::formatterx100p($worksheet->getCell('C17')->getOldCalculatedValue()),
                    'value2' => FG::formatterx100p($worksheet->getCell('F17')->getOldCalculatedValue())
                ],
                [
                    'label'  => 'Beta apalancado',
                    'value'  => FG::numberFormat($worksheet->getCell('C20')->getOldCalculatedValue()),
                    'value2' => FG::formatterx100p($worksheet->getCell('F20')->getOldCalculatedValue())
                ],
                [
                    'label'  => 'Beta desapalancado',
                    'value'  => FG::numberFormat($worksheet->getCell('C26')->getOldCalculatedValue()),
                    'value2' => FG::formatterx100p($worksheet->getCell('F26')->getOldCalculatedValue())
                ],
                [
                    'label'  => 'Devaluación esperada',
                    'value'  => '-',
                    'value2' => FG::formatterx100p($worksheet->getCell('F6')->getOldCalculatedValue())
                ],
                [
                    'label'  => 'Riesgo País',
                    'value'  => '-',
                    'value2' => FG::formatterx100p($worksheet->getCell('F8')->getOldCalculatedValue())
                ]
            );

            $percentages = array(
                [
                    'label' => 'Costo de capital económico (Koa)',
                    'dolar' => FG::formatterx100p($worksheet->getCell('C27')->getOldCalculatedValue()),
                    'national' => FG::formatterx100p($worksheet->getCell('F27')->getOldCalculatedValue()),
                ],
                [
                    'label' => 'Costo de capital financiero (Ke)',
                    'dolar' => FG::formatterx100p($worksheet->getCell('C23')->getOldCalculatedValue()),
                    'national' => FG::formatterx100p($worksheet->getCell('F23')->getOldCalculatedValue()),
                ],
                [
                    'label' => 'Costo de la deuda después de impuestos Kd(1-T)',
                    'dolar' => FG::formatterx100p($worksheet->getCell('C33')->getOldCalculatedValue()),
                    'national' => FG::formatterx100p($worksheet->getCell('F33')->getOldCalculatedValue()),
                ],
                [
                    'label' => 'Costo Promedio Ponderado de Capital (CPPC)',
                    'dolar' => FG::formatterx100p($worksheet->getCell('C36')->getOldCalculatedValue()),
                    'national' => FG::formatterx100p($worksheet->getCell('F36')->getOldCalculatedValue()),
                ]
            );

            $graph['general'] = array(
                'groups' => [
                    [ 
                        'label' => 'CPPC', 
                        'index' => 'value1', 
                        'color' => $this->colors['six'] 
                    ],
                    [ 
                        'label' => 'Kd(1-t)', 
                        'index' => 'value2', 
                        'color' => $this->colors['two'] 
                    ],
                    [ 
                        'label' => 'Koa', 
                        'index' => 'value3', 
                        'color' => $this->colors['one'] 
                    ],
                    [ 
                        'label' => 'Ke', 
                        'index' => 'value4', 
                        'color' => $this->colors['four'] 
                    ]
                ],
                'items' => [
                    [
                        'value1' => FG::formatterx100val($worksheet->getCell('C36')->getOldCalculatedValue()),
                        'value2' => FG::formatterx100val($worksheet->getCell('C33')->getOldCalculatedValue()),
                        'value3' => FG::formatterx100val($worksheet->getCell('C27')->getOldCalculatedValue()),
                        'value4' => FG::formatterx100val($worksheet->getCell('C23')->getOldCalculatedValue()),
                        'label' => 'Mercado Desarrolado'
                    ],
                    [
                        'value1' => FG::formatterx100val($worksheet->getCell('F36')->getOldCalculatedValue()),
                        'value2' => FG::formatterx100val($worksheet->getCell('F33')->getOldCalculatedValue()),
                        'value3' => FG::formatterx100val($worksheet->getCell('F27')->getOldCalculatedValue()),
                        'value4' => FG::formatterx100val($worksheet->getCell('F23')->getOldCalculatedValue()),
                        'label' => 'Mercado Emergente'
                    ]
                ]
            );
            $rsp['success'] = true;
            $rsp['data'] = compact('graph', 'general', 'parameters', 'percentages');
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function marketDeveloperBonusReport($args) {
        $rsp = FG::responseDefault();
        try {
            $file_user = $args['file_user'];
            if(!$file_user) {
                throw new \Exception('File of user no encontred');
            }

            $sheetname = @$_POST['riskFreeRate'] == EMPLOYEE ? 'Reporte Koa Sectorial' : 'Reporte Koa Sectorial_10';
            $report = array(); $legend = array();

            $filename = $file_user;
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            $reader->setLoadSheetsOnly($sheetname); 
            $spreadsheet = $reader->load($filename);

            $worksheet = $spreadsheet->getActiveSheet();
            $lastRow = $worksheet->getHighestRow();

            for ($row = 4; $row <= $lastRow; $row++) {
                $b = $worksheet->getCell('B'.$row)->getValue();
                $c = $worksheet->getCell('C'.$row)->getValue();
                if ($c && $b) {
                    $c = FG::formatterx100p($worksheet->getCell('C'.$row)->getOldCalculatedValue());
                    array_push($report, ['name'=>$b, 'value'=>$c]);
                }
            }

            for ($row = 5; $row <= $lastRow; $row++) {
                $e = $worksheet->getCell('E'.$row)->getValue();
                $f = $worksheet->getCell('F'.$row)->getValue();
                if ($e && $f) {
                    if ($row > 6) {
                        $f =  FG::numberformat($worksheet->getCell('F'.$row)->getOldCalculatedValue());
                    } else {
                        $f = FG::formatterx100p($worksheet->getCell('F'.$row)->getOldCalculatedValue());
                    }
                    if ($f && $e) {
                        array_push($legend, ['name'=>$e, 'value'=>$f]);
                    }
                }
            }

            $rsp['success'] = true;
            $rsp['data'] = compact('report', 'legend');
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function initCalculateInvestmentBonusReport($args) {
        $rsp = FG::responseDefault();
        try {
            $file_user = $args['file_user'];
            if(!$file_user) {
                throw new \Exception('File of user no encontred');
            }

            $sheetname = @$_POST['riskFreeRate'] == EMPLOYEE ? 'Wacc_empresa' : 'Wacc_empresa_10';
            $inputs = array();
            $filename = $file_user;
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            $reader->setLoadSheetsOnly($sheetname); 
            $spreadsheet = $reader->load($filename);

            $worksheet = $spreadsheet->getActiveSheet();
            # inputs 
            $inputs = array(); $graph = array('capital' => 0,'debt' => 0);
            for ($row = 6; $row <= 16; $row++) {
                $b = $worksheet->getCell('B'.$row)->getValue();
                $c = $worksheet->getCell('C'.$row)->getValue();
                $d = $worksheet->getCell('D'.$row)->getValue();
                if ($b && $c) {
                    if ($row == 6 || $row == 8 || $row == 16) {
                        $c = FG::formatterx100p($worksheet->getCell('C'.$row)->getOldCalculatedValue());
                        $d = FG::formatterx100($worksheet->getCell('D'.$row)->getValue());
                    } else {
                        $c = $worksheet->getCell('C'.$row)->getOldCalculatedValue();        
                    }
                    array_push($inputs, ['name'=>$b, 'dolares'=>$c, 'soles'=>$d]);
                }
            }
            $graph['capital'] = $worksheet->getCell('C31')->getOldCalculatedValue();
            $graph['debt'] = $worksheet->getCell('C29')->getOldCalculatedValue();
            $rsp['success'] = true;
            $rsp['data'] = compact('inputs', 'graph');
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function flowsProject($args) {
        $rsp = FG::responseDefault();
        try {
            extract($args);
            if (!$filename) {
                throw new \Exception('The filename is required');
            }
            $table_sheetname = 'Tablas';
            $flow_sheetname = 'Flujos';
            $periodicities = array(); $horizons = array(); 
            $investments = array(); $flows = array();

            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            $reader->setLoadSheetsOnly($table_sheetname); 
            $spreadsheet = $reader->load($filename);

            $worksheet = $spreadsheet->getActiveSheet();
            $lastRow = $worksheet->getHighestRow();

            for ($row = 2; $row <= $lastRow; $row++) {
                $a = $worksheet->getCell('E'.$row)->getValue();
                $c = $worksheet->getCell('G'.$row)->getValue();
                if ($a) {
                    array_push($periodicities, $a);
                }
                if ($c) {
                    array_push($horizons, $c);
                }
            }

            $reader->setLoadSheetsOnly($flow_sheetname); 
            $spreadsheet = $reader->load($filename);
            $worksheet = $spreadsheet->getActiveSheet();
            $lastRow = $worksheet->getHighestRow();

            $horizon = $worksheet->getCell('C4')->getValue(); 
            $periodicity = $worksheet->getCell('C5')->getValue(); 
            $period = $worksheet->getCell('C6')->getOldCalculatedValue(); 

            $html = array(
                'horizon' => array(
                    'text'=> $worksheet->getCell('B4')->getValue(),
                    'value'=> $horizon
                ),
                'periodicity' => array(
                    'text'=> $worksheet->getCell('B5')->getValue(),
                    'value'=> $periodicity
                ),
                'period' => array(
                    'text'=> $worksheet->getCell('B6')->getValue(),
                    'value'=> $period
                )
            );
            
            $last_index = $period + 1;
            $flows = array();
            $lastColumn = $worksheet->getHighestColumn();
            $box = array(); $investments = array();

            $row1 = 11; $row2 = 12;
            $index_col = 0;
            for ($col = 'C'; $col != $lastColumn ; ++$col) {
                if ($index_col >= $last_index) {
                    break;
                }
                $val1 = $worksheet->getCell($col.$row1)->getValue();
                $val2 = $worksheet->getCell($col.$row2)->getValue();
                array_push($flows, array('box'=>$val1 ? $val1 : 0,'investment'=>$val2 ? $val2 : 0));

                $index_col++;
            }
            
            $rsp['success'] = true;
            $rsp['data'] = compact('periodicities', 'horizons', 'flows', 'horizon', 'periodicity', 'period', 'html');
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }
    
    public function ratesCostBonusReport($args) {
        $rsp = FG::responseDefault();
        try {
            $file_user = $args['file_user'];
            if(!$file_user) {
                throw new \Exception('File of user no encontred');
            }

            $sheetname = @$_POST['riskFreeRate'] == EMPLOYEE ? 'Wacc_empresa' : 'Wacc_empresa_10';
            $report = array(); $legend = array(); $graph = array('statistics'=>[]);

            $filename = $file_user;
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            $reader->setLoadSheetsOnly($sheetname); 
            $spreadsheet = $reader->load($filename);

            $worksheet = $spreadsheet->getActiveSheet();
            $lastRow = $worksheet->getHighestRow();

            $values = array(); $labels = array();
            for ($row = 23; $row <= $lastRow; $row++) {
                $b = $worksheet->getCell('B'.$row)->getValue();
                $c = $worksheet->getCell('C'.$row)->getValue();
                $d = $worksheet->getCell('D'.$row)->getValue();
                if ($c && $b) {
                    $val1 = $worksheet->getCell('C'.$row)->getOldCalculatedValue();
                    $val2 = $worksheet->getCell('D'.$row)->getOldCalculatedValue();
                    $c = FG::formatterx100p($val1);
                    $d = FG::formatterx100p($val2);
                    array_push($report, ['name'=>$b, 'dolares'=>$c, 'soles'=>$d]);
                    if ($row != 29 && $row != 31) {
                        array_push($labels, $b);
                        array_push($values, $val1);    
                    }
                }
            }

            $graph['statistics'] = compact('values', 'labels');

            for ($row = 36; $row <= $lastRow; $row++) {
                $e = $worksheet->getCell('G'.$row)->getValue();
                $h = $worksheet->getCell('H'.$row)->getValue();
                if ($e && $h) {
                    if ($row == 38 || $row == 39) {
                        $h = FG::numberformat($worksheet->getCell('H'.$row)->getOldCalculatedValue());
                    } else {
                        $h =  FG::formatterx100p($worksheet->getCell('H'.$row)->getOldCalculatedValue());
                    }
                    if ($h && $e) {
                        array_push($legend, ['name'=>$e, 'value'=>$h]);
                    }
                }
            }

            $rsp['success'] = true;
            $rsp['data'] = compact('report', 'legend', 'graph');
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function onCalculation($args) {
        $rsp = FG::responseDefault();
        try {
            extract($args);
            if (!$sector) {
                throw new \Exception('The sector is required');
            }
            if (!$instrument) {
                throw new \Exception('The instrument is required');
            }
            if (!$filename) {
                throw new \Exception('The filename is required');
            }
            $sheetname = 'Calculo K';
            $sheetname_table = 'Tablas';

            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            $reader->setLoadSheetsOnly($sheetname_table); 
            $spreadsheet = $reader->load($filename);
            $worksheet = $spreadsheet->getActiveSheet();
            $instruments = array();
            for ($row = 2; $row <= 3; $row++) {
                array_push($instruments,  $worksheet->getCell('A'.$row)->getValue());              
            }
            if (!in_array($instrument, $instruments)) {
                throw new \Exception('The instrument no encontred');
            }

            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
           
            $spreadsheet = $reader->load($filename);
        
            $worksheet = $spreadsheet->getSheetByName($sheetname);
            $worksheet->setCellValue('C12', $sector);
            $worksheet->setCellValue('C2', $instrument);

            $xlsxWriter = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $xlsxWriter->save($filename);

            $rsp['success'] = true;
            $rsp['message'] = '';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function onCountryEmerging($args) {
        $rsp = FG::responseDefault();
        try {
            extract($args);
            if (!$country) {
                throw new \Exception('The country is required');
            }
            if (!$filename) {
                throw new \Exception('The filename is required');
            }

            $sheetname = 'Calculo K';
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            $spreadsheet = $reader->load($filename);
            $worksheet = $spreadsheet->getSheetByName($sheetname);
            $worksheet->setCellValue('F5', $country);
            $worksheet->setCellValue('F7', 0);
            $xlsxWriter = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $xlsxWriter->save($filename);

            $result = $this->initStructureEmerging(compact('filename'));

            $rsp['success'] = true;
            $rsp['data'] = $result['data'];
            $rsp['message'] = 'Correct';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function onDevaluationEmerging($args) {
        $rsp = FG::responseDefault();
        try {
            extract($args);
            if (!$devaluation) {
                throw new \Exception('The devaluation is required');
            }
            if (!$debt) {
                throw new \Exception('The debt is required');
            }
            if (!$filename) {
                throw new \Exception('The filename is required');
            }

            $devaluation = ($devaluation/100);
            $devaluation = $devaluation > 0 ? $devaluation : 0;

            $debt = ($debt/100);
            $debt = $debt > 0 ? $debt : 0;

            $sheetname = 'Calculo K';
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            $spreadsheet = $reader->load($filename);
            $worksheet = $spreadsheet->getSheetByName($sheetname);
            $worksheet->setCellValue('F7', $devaluation);
            $worksheet->setCellValue('G16', $debt);
            $xlsxWriter = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $xlsxWriter->save($filename);

            //$result = $this->initStructureEmerging(compact('filename'));

            $rsp['success'] = true;
            //$rsp['data'] = $result['data'];
            $rsp['message'] = 'Correct';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function onPercentageCurrencyCompany($args) {
        $rsp = FG::responseDefault();
        try {
            extract($args);
            if (!$percentage) {
                throw new \Exception('The percentage is required');
            }
            if (!$currency) {
                throw new \Exception('The currency is required');
            }
            if (!$filename) {
                throw new \Exception('The filename is required');
            }

            $percentage = ($percentage/100);
            $percentage = $percentage > 0 ? $percentage : 0;

            $sheetname = 'Calculo K';
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            $spreadsheet = $reader->load($filename);
            $worksheet = $spreadsheet->getSheetByName($sheetname);
            // $worksheet->setCellValue(($currency == 'S' ? 'K6' : 'I7'), $percentage);
            if ($currency == 'S') {
                $worksheet->setCellValue('K6', $percentage);  
                $worksheet->setCellValue('I7', '');  
            } else if($currency == 'D') {
                $worksheet->setCellValue('I7', $percentage);  
                $worksheet->setCellValue('K6', '');  
            }

            $worksheet->setCellValue('L6', ($currency == 'S' ? 1 : 0));
            $worksheet->setCellValue('L7', ($currency == 'D' ? 1 : 0));

            $xlsxWriter = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $xlsxWriter->save($filename);

            $result = $this->initStructureCompany(compact('filename'));

            $rsp['success'] = true;
            $rsp['data'] = $result['data'];
            $rsp['message'] = 'Correct';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function onPercentageInvestment($args) {
        $rsp = FG::responseDefault();
        try {
            extract($args);
            if (!$debt) {
                throw new \Exception('The debt is required');
            }
            if (!$filename) {
                throw new \Exception('The filename is required');
            }

            $debt = ($debt/100);
            $debt = $debt > 0 ? $debt : 0;

            $sheetname = 'Calculo K';
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            $spreadsheet = $reader->load($filename);
            $worksheet = $spreadsheet->getSheetByName($sheetname);
            $worksheet->setCellValue('I16', $debt);
            $xlsxWriter = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $xlsxWriter->save($filename);

            $result = $this->initStructureCompany(compact('filename'));

            $rsp['success'] = true;
            $rsp['data'] = $result['data'];
            $rsp['message'] = 'Correct';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }
    
    public function costCalculationSectorUser($args) {
        $rsp = FG::responseDefault();
        try {
            extract($args);
            if (!$num1) {
                throw new \Exception('The num1 is required');
            }
            if (!$num2) {
                throw new \Exception('The num2 is required');
            }
            if (!$country) {
                throw new \Exception('The country is required');
            }
            if (!$filename) {
                throw new \Exception('The filename is required');
            }
            if (!$folder) {
                throw new \Exception('The folder is required');
            }

            $fullpath = FG::fullPathUser($folder, $filename);
            $sheetname = @$_POST['riskFreeRate'] == EMPLOYEE ? 'Koa_emercountry_sec' : 'Koa_emercountry_sec_10';

            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            $spreadsheet = $reader->load($fullpath);

            $worksheet = $spreadsheet->getSheetByName($sheetname);
            $worksheet->setCellValue('C6', $country);
            $worksheet->setCellValue('C10', $num1);
            $worksheet->setCellValue('C12', $num2);

            $xlsxWriter = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $xlsxWriter->save($fullpath);

            $rsp['success'] = true;
            $rsp['message'] = '';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function costCalculationInvesmentUser($args) {
        $rsp = FG::responseDefault();
        try {
            extract($args);
            if (!is_numeric($percentage)) {
                throw new \Exception('The percentage is required');
            }
            if (!$filename) {
                throw new \Exception('The filename is required');
            }
            if (!$folder) {
                throw new \Exception('The folder is required');
            }

            $fullpath = FG::fullPathUser($folder, $filename);
            $sheetname = @$_POST['riskFreeRate'] == EMPLOYEE ? 'Wacc_empresa' : 'Wacc_empresa_10';

            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            $spreadsheet = $reader->load($fullpath);

            $worksheet = $spreadsheet->getSheetByName($sheetname);
            $percentage = $percentage/100;
            $worksheet->setCellValue('D6', $percentage);

            $xlsxWriter = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $xlsxWriter->save($fullpath);

            $rsp['success'] = true;
            $rsp['message'] = '';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }
    
    public function onCalculationFlow($args) {
        $rsp = FG::responseDefault();
        try {
            extract($args);
            if (!is_numeric($horizon)) {
                throw new \Exception('The horizon is required');
            }
            if (!$periodicity) {
                throw new \Exception('The period is required');
            }
            if (!$filename) {
                throw new \Exception('The user is required');
            }
            /*if (!$folder) {
                throw new \Exception('The folder is required');
            }*/

            // $fullpath = FG::fullPathUser($folder, $filename);
            $sheetname = 'Flujos';

            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            $reader->setLoadSheetsOnly($sheetname); 
            $spreadsheet = $reader->load($filename);
            $worksheet = $spreadsheet->getActiveSheet();
            
            $worksheet->setCellValue('C4', $horizon);
            $worksheet->setCellValue('C5', $periodicity);
            $period = $worksheet->getCell('C6')->getCalculatedValue();

            $rsp['success'] = true;
            $rsp['data'] = compact('period');
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function onCalculationDetailFlow($args) {
        $rsp = FG::responseDefault();
        try {
            extract($args);
            if (!$filename) {
                throw new \Exception('The filename is required');
            }
            if (!$periodicity) {
                throw new \Exception('The periodicity is required');
            }
            if (!$horizon) {
                throw new \Exception('The horizon is required');
            }
            if (!$flows) {
                throw new \Exception('The flows is required');
            }
            $flows = json_decode($flows);
            $sheetname = 'Flujos';
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            $spreadsheet = $reader->load($filename);
            
            $worksheet = $spreadsheet->getSheetByName($sheetname);
            $worksheet->setCellValue('C4', $horizon);
            $worksheet->setCellValue('C5', $periodicity);            
            $period = $worksheet->getCell('C6')->getCalculatedValue();
            $lastColumn = $worksheet->getHighestColumn();

            $last_index = $period + 1; $row1 = 11; $row2 = 12; $index_col = 0;
            for ($col = 'C'; $col != $lastColumn ; ++$col) {
                if ($index_col >= $last_index) {
                    break;
                }
                $nbox = $flows[$index_col]->box;
                $ninvestment = $flows[$index_col]->investment;
                if (!is_numeric($nbox)) {    
                    throw new \Exception('The value '.$nbox.' is not a number.');
                }
                if (!is_numeric($ninvestment)) {                
                    throw new \Exception('The value '.$ninvestment.' is not a number.');
                }
                $worksheet->setCellValue($col.$row1, $nbox);
                $worksheet->setCellValue($col.$row2, $ninvestment);
                $index_col++;
            }

            $xlsxWriter = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $xlsxWriter->save($filename);

            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            $sheetname = 'Tablas';
            $sheetname_rf_adjust = 'Rf Ajustado';
            $sheetname_calculate = 'Calculo K';
            $periods = array(); 
            $graph = array('performance' => []);
            
            $reader->setLoadSheetsOnly($sheetname); 
            $spreadsheet = $reader->load($filename);

            $worksheet = $spreadsheet->getActiveSheet();
            $lastRow = $worksheet->getHighestRow();

            for ($row = 2; $row <= $lastRow; $row++) {
                $g = $worksheet->getCell('C'.$row)->getValue();
                if ($g) {
                    array_push($periods, $g);
                }
            }

            $reader->setLoadSheetsOnly($sheetname_rf_adjust); 
            $spreadsheet = $reader->load($filename);
            $worksheet = $spreadsheet->getActiveSheet();

            $period = $worksheet->getCell('B3')->getOldCalculatedValue();
            $duration = round($worksheet->getCell('B8')->getOldCalculatedValue(),2);
            $instrument = round($worksheet->getCell('B27')->getOldCalculatedValue(),2);
            $lastRow = 25;
            $performances = array();

            $points = array();
            for ($row = 14; $row <= $lastRow; $row++) {
                $a = $worksheet->getCell('A'.$row)->getCalculatedValue();
                $b = $worksheet->getCell('B'.$row)->getValue();
                $points[$a] = ['value'=>($b ? FG::formatterx100($b) : 0), 'label'=>$a];
            }

            foreach ($periods as $k => $o) {
                if (isset($points[$o])){
                    $p = $points[$o]['label'] == $period ? $period : 0;
                    array_push($performances, ['value'=> $points[$o]['value'], 'label'=>$points[$o]['label'], 'index'=>0]);
                }
            }

            $reader->setLoadSheetsOnly($sheetname_calculate); 
            $spreadsheet = $reader->load($filename);
            $worksheet = $spreadsheet->getActiveSheet();
            $performance = FG::formatterx100p($worksheet->getCell('C9')->getOldCalculatedValue());

            array_push($performances, ['value'=> $performance, 'label'=>$duration, 'index'=>$duration]);
            sort($performances);

            $graph['performance'] = $performances;
            $rsp['success'] = true;
            $rsp['data'] = compact('instrument', 'performance', 'duration', 'graph');
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function initCurvePerformanceProject($args) {
        $rsp = FG::responseDefault();
        try {
            extract($args);
            if (!$filename) {
                throw new \Exception('The filename is required');
            }

            $sheetname = 'Tablas';
            $sheetname_rf_adjust = 'Rf Ajustado';
            $sheetname_calculate = 'Calculo K';
            $periods = array(); 
            $graph = array('performance' => []);

            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            
            $reader->setLoadSheetsOnly($sheetname); 
            $spreadsheet = $reader->load($filename);

            $worksheet = $spreadsheet->getActiveSheet();
            $lastRow = $worksheet->getHighestRow();

            for ($row = 2; $row <= $lastRow; $row++) {
                $g = $worksheet->getCell('C'.$row)->getValue();
                if ($g) {
                    array_push($periods, $g);
                }
            }
            $performances = array();

            $reader->setLoadSheetsOnly($sheetname_rf_adjust); 
            $spreadsheet = $reader->load($filename);
            $worksheet = $spreadsheet->getActiveSheet();

            $instrument = round($worksheet->getCell('B27')->getOldCalculatedValue(), 2);
            $period = $worksheet->getCell('B3')->getOldCalculatedValue();
            $duration = round($worksheet->getCell('B8')->getOldCalculatedValue(), 2);
            $lastRow = 25;
            $performances = array();

            $points = array();
            for ($row = 14; $row <= $lastRow; $row++) {
                $a = $worksheet->getCell('A'.$row)->getCalculatedValue();
                $b = $worksheet->getCell('B'.$row)->getValue();
                $points[$a] = ['value'=>($b ? FG::formatterx100($b) : 0), 'label'=>$a];
            }

            foreach ($periods as $k => $o) {
                if (isset($points[$o])){
                    $p = $points[$o]['label'] == $period ? $period : 0;
                    array_push($performances, ['value'=> $points[$o]['value'], 'label'=>$points[$o]['label'], 'index'=> 0]);
                }
            }

            $reader->setLoadSheetsOnly($sheetname_calculate); 
            $spreadsheet = $reader->load($filename);
            $worksheet = $spreadsheet->getActiveSheet();
            $performance = FG::formatterx100p($worksheet->getCell('C9')->getOldCalculatedValue());

            array_push($performances, ['value'=> $performance, 'label'=>$duration, 'index'=>$duration]);
            sort($performances);
            $graph['performance'] = $performances;

            $rsp['success'] = true;
            $rsp['data'] = compact('instrument', 'performance', 'duration', 'graph');
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }
}