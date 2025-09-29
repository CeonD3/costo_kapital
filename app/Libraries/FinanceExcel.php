<?php 

namespace App\Libraries;

use App\Utilitarian\FG;
use App\Traits\FinanceExcelTrait;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

class FinanceExcel {

    use FinanceExcelTrait;

    public function industries($filename) {

        $reader = new Xlsx();
        $reader->setLoadSheetsOnly($this->sheetname_koa_boa); 
        $spreadsheet = $reader->load($filename);
        $worksheet = $spreadsheet->getActiveSheet();
        $lastRow = $worksheet->getHighestRow();
        $lastColumn = $worksheet->getHighestColumn();
        $industries = array();
        $years = array();
        for ($row = 1; $row <= 1; $row++) {
            for ($column = 'A'; $column != $lastColumn; $column++) {
                if ($worksheet->getCell($column . $row)->isInMergeRange()) {
                    $year = $worksheet->getCell($column . $row)->getValue();
                    if ($year) {
                        $years[] = compact('year', 'column');
                    }
                }
            }
        }
        for ($row = 3; $row <= $lastRow; $row++) {
            $label = $worksheet->getCell('A'.$row)->getValue();
            if ($row <= 93) {
                $industries[] = $label;
                foreach ($years as $k => $v) {
                    $value = $worksheet->getCell($v['column'].$row)->getCalculatedValue();
                    $years[$k]['industries'][] = [
                        'label' => $label,
                        'value' => FG::formatterx100val($value)
                    ];
                }
            }
        }
        return compact('years', 'industries');
    }

}