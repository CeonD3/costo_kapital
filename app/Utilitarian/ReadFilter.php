<?php
// Puedes crear un archivo nuevo, por ejemplo: app/Libraries/MyReadFilter.php
namespace App\Utilitarian;

class ReadFilter implements \PhpOffice\PhpSpreadsheet\Reader\IReadFilter
{
    private $startRow = 0;
    private $endRow   = 0;
    private $columns  = [];

    /**
     * @param int   $startRow   La primera fila a leer.
     * @param int   $endRow     La última fila a leer (o null para leer hasta el final).
     * @param array $columns    Un array de las letras de las columnas a leer (ej. ['A', 'C', 'F']).
     */
    public function __construct($startRow, $endRow, $columns)
    {
        $this->startRow = $startRow;
        $this->endRow   = $endRow;
        $this->columns  = $columns;
    }

    public function readCell($columnAddress, $row, $worksheetName = '')
    {
        // Si la fila está fuera de nuestro rango, no la leas.
        if ($row >= $this->startRow && ($this->endRow === null || $row <= $this->endRow)) {
            // Si la columna está en nuestra lista de columnas deseadas, léela.
            if (in_array($columnAddress, $this->columns)) {
                return true;
            }
        }
        return false;
    }
}
