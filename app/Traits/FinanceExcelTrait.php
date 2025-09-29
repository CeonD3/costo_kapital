<?php

/** 
 * https://phpspreadsheet.readthedocs.io/en/latest/topics/reading-and-writing-to-file/
 * https://github.com/PHPOffice/PhpSpreadsheet/blob/1.3.1/src/PhpSpreadsheet/Cell/Cell.php#L162
 * https://github.com/PHPOffice/PhpSpreadsheet/blob/master/src/PhpSpreadsheet/IOFactory.php
 */

namespace App\Traits;

ini_set('memory_limit', '-1');

trait FinanceExcelTrait
{
    private $sheetname_all_data = 'datos';
    private $sheetname_user = 'Plantilla Usuario';
    private $sheetname_wacc = 'WACC';
    private $sheetname_tablas = 'Tablas';
    private $sheetname_industries = 'Damondaran Industries';
    private $sheetname_contries = 'IR';
    private $sheetname_koa_boa = 'Koa-Boa';
    private $sheetname_concept = 'M. por conceptos';
    private $sheetname_integrated = 'M. Integrado';
    private $sheetname_economic = 'EE.FF Económicos';
    private $sheetname_Sensitivity = 'Sensibilidad';
    private $sheetname_BVL = 'BVL';

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

    public function f_readTableBalance($worksheet)
    {
        $lastRow = $worksheet->getHighestRow();
        $lastColumn = $worksheet->getHighestColumn();
        $generales = array();
        $resultados = array();

        $header = array();
        $lastColumnTable = "";
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
        $body = array();
        $lastRowTable = "";
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
        $header = array();
        $lastColumnTable = "";
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
        $body = array();
        $lastRowTable = "";
        $startRowTable++;
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
        $resultados = compact('header', 'body');

        return compact('generales', 'resultados');
    }
}
