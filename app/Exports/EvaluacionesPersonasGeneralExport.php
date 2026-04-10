<?php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class EvaluacionesPersonasGeneralExport implements WithMultipleSheets
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function sheets(): array
    {
        return [
            new EvaluacionesPersonasGeneralResumenSheet($this->request),
            new EvaluacionesPersonasGeneralDetalleSheet($this->request),
        ];
    }
}
