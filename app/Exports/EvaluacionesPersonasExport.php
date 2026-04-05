<?php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class EvaluacionesPersonasExport implements WithMultipleSheets
{
    protected $actividad;

    public function __construct($actividad)
    {
        $this->actividad = $actividad;
    }

    public function sheets(): array
    {
        return [
            new EvaluacionesPersonasResumenSheet($this->actividad),
            new EvaluacionesPersonasDetalleSheet($this->actividad),
        ];
    }
}
