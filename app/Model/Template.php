<?php

namespace App\Model;

use App\Utilitarian\{Crypt, FG};
use App\Persistence\{utils};
use App\Traits\FinanceExcelTrait;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Eloquent\Model;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\DefaultReadFilter;

// Mover la clase ReadFilter fuera del método
class TemplateReadFilter extends DefaultReadFilter
{

    private $startRow;
    private $endRow;
    private $columns;

    public function __construct($startRow, $endRow, $columns)
    {
        $this->startRow = $startRow;
        $this->endRow = $endRow;
        $this->columns = $columns;
    }

    public function readCell($column, $row, $worksheetName = '')
    {
        if ($this->startRow !== null && $row < $this->startRow) {
            return false;
        }
        if ($this->endRow !== null && $row > $this->endRow) {
            return false;
        }
        return in_array($column, $this->columns);
    }
}

class Template extends Model
{
    use FinanceExcelTrait;
    protected $fillable = ['templates'];

    public function list($request)
    {
        $rsp = FG::responseDefault();
        try {
            $templates = Template::where('deleted_at')->get()->toArray();
            $rsp['success'] = true;
            $rsp['data'] = compact('templates');
            $rsp['message'] = 'successfully';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function manage($request)
    {
        $rsp = FG::responseDefault();
        try {
            extract($request->getParsedBody());
            if (!$name) {
                throw new \Exception('The name is required');
            }
            if (!is_numeric($id)) {
                if (0 < $_FILES['file']['error']) {
                    throw new \Exception('The file is required');
                }
            }

            // Definir valores por defecto para variables que pueden no existir
            $status = isset($status) ? $status : 0;
            $version = isset($version) ? $version : 1;

            $template = is_numeric($id) ? Template::where('id', $id)->first() : new Template();

            if (isset($_FILES['file']) && !(0 < $_FILES['file']['error'])) {
                $filename = $_FILES['file']['name'];
                $fileuri = uniqid(time()) . '-' . $filename;
                move_uploaded_file($_FILES['file']['tmp_name'], FG::getPathMaster($fileuri));

                // Crear archivo de datos
                $this->createDataFile($fileuri);

                if (is_numeric($id)) {
                    unlink(FG::getPathMaster($template->file));
                    // También eliminar el archivo de datos anterior
                    $oldDataFile = $this->getDataFileName($template->file);
                    if (file_exists(FG::getPathMaster($oldDataFile))) {
                        unlink(FG::getPathMaster($oldDataFile));
                    }
                }
                $template->file = $fileuri;
            }

            $myTemplate = Template::where('status', 1)->where('id', '<>', @$template->id)->where('version', $version)->first();
            if ($myTemplate) {
                $myTemplate->status = $status == 1 ? 0 : $status;
                $myTemplate->save();
            }

            $template->name = $name;
            $template->status = $status;
            $template->version = $version;
            $template->save();

            $rsp['success'] = true;
            $rsp['data'] = compact('template');
            $rsp['message'] = 'Se guardo correctamente';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    private function createDataFile($templateFile)
    {
        $masterTemplatePath = FG::getPathMaster($templateFile);
        $dataFileName = $this->getDataFileName($templateFile);
        $dataFilePath = FG::getPathMaster($dataFileName);

        $result = [
            'sectors' => [],
            'instruments' => [],
            'dates' => [],
            'bonos' => [],
            'currencies' => [],
            'countries' => []
        ];


        // 1. Cargar solo las hojas de Industrias, Tablas y Países
        $reader = IOFactory::createReader('Xlsx');
        $sheetsToLoad = [$this->sheetname_industries, $this->sheetname_tablas, $this->sheetname_contries];
        $reader->setLoadSheetsOnly($sheetsToLoad);

        // 2. Definir y aplicar el filtro para leer solo las columnas A y L desde la fila 2 en adelante.
        // Se agrega la columna 'C' para poder leer los bonos solicitados
        $columnsToRead = ['A', 'C', 'L'];
        $filter = new TemplateReadFilter(2, null, $columnsToRead);
        $reader->setReadFilter($filter);

        // 3. Cargar el archivo con todas las optimizaciones aplicadas.
        $spreadsheet = $reader->load($masterTemplatePath);

        // 1. Leer la hoja de Industrias
        $worksheet = $spreadsheet->getSheetByName($this->sheetname_industries);
        if ($worksheet) {
            $lastRow = $worksheet->getHighestRow();
            for ($row = 3; $row <= $lastRow; $row++) {
                $value = $worksheet->getCell('A' . $row)->getValue();
                if (!$value) break;
                $result['sectors'][] = $value;
            }
        }

        // 2. Leer la hoja de Tablas (para fechas y monedas)
        $worksheet = $spreadsheet->getSheetByName($this->sheetname_tablas);
        if ($worksheet) {
            $lastRow = $worksheet->getHighestRow();
            // Leer instrumentos (columna A desde fila 2 hasta vacío)
            for ($row = 2; $row <= $lastRow; $row++) {
                $value = $worksheet->getCell('A' . $row)->getValue();
                if (!$value) break;
                $result['instruments'][] = $value;
            }
            // Leer fechas (columna A desde fila 41)
            for ($row = 41; $row <= $lastRow; $row++) {
                $value = $worksheet->getCell('A' . $row)->getFormattedValue();
                if (!$value) break;
                if ($value) {
                    try {
                        $datetime = new \DateTime($value);
                        $result['dates'][] = $datetime->format('d/m/Y');
                    } catch (\Exception $e) {
                        continue;
                    }
                }
            }
            // Leer bonos (columna C desde fila 2 hasta vacío)
            for ($row = 2; $row <= $lastRow; $row++) {
                $value = $worksheet->getCell('C' . $row)->getValue();
                if (!$value) break;
                $result['bonos'][] = $value;
            }
            // Leer monedas (columna L desde fila 2 hasta vacío)
            for ($row = 2; $row <= $lastRow; $row++) {
                $value = $worksheet->getCell('L' . $row)->getValue();
                if (!$value) break;
                $result['currencies'][] = $value;
            }
        }

        // 3. Leer la hoja de Países
        $worksheet = $spreadsheet->getSheetByName($this->sheetname_contries);
        if ($worksheet) {
            $lastRow = $worksheet->getHighestRow();
            for ($row = 2; $row <= $lastRow; $row++) {
                $value = $worksheet->getCell('A' . $row)->getValue();
                if (!$value) break;
                $result['countries'][] = $value;
            }
        }

        // Guardar los datos en archivo JSON
        file_put_contents($dataFilePath, json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    private function getDataFileName($templateFile)
    {
        $pathInfo = pathinfo($templateFile);
        $dirname = $pathInfo['dirname'] === '.' ? '' : $pathInfo['dirname'] . '/';
        return $dirname . $pathInfo['filename'] . '-data.json';
    }

    public function remove($request)
    {
        $rsp = FG::responseDefault();
        try {
            extract($request->getParsedBody());

            if (!$id) {
                throw new \Exception('The id is required');
            }

            $templates = Template::where('deleted_at')->get()->toArray();
            if (count($templates) == 1) {
                throw new \Exception('Must have at least one stored template');
            }

            $template = Template::where('id', $id)->first();

            if ($template->status == 1) {
                throw new \Exception('Can not delete a default template');
            }
            $template->deleted_at = FG::getDateHour();
            unlink(FG::getPathMaster($template->file));
            $template->save();

            $rsp['success'] = true;
            $rsp['message'] = 'Se elimino correctamente';
        } catch (\Exception $e) {
            $rsp['message'] = $e->getMessage();
        }
        return $rsp;
    }

    public function downloadMaster($request)
    {
        $filename = urldecode(end(explode("/", $request->getUri()->getpath())));
        $file = FG::getPathMaster($filename);
        if (file_exists($file)) {
            $this->downloadFile($file);
        } else {
            header('Location: /');
            exit();
        }
    }

    public function downloadFile($file)
    {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        ob_clean();
        flush();
        readfile($file);
        exit;
    }
}
