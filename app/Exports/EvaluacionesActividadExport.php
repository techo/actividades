<?php
namespace App\Exports;

use App\EvaluacionActividad;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class EvaluacionesActividadExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $actividad;


    public function __construct($actividad)
    {
        $this->actividad = $actividad;
    }

    public function collection()
    {
        return EvaluacionActividad::select(['puntaje', 'comentario'])
            ->where('idActividad', $this->actividad->idActividad)
            ->get();
    }

    public function headings(): array
    {
        return [
            'Puntaje',
            'Comentario',
        ];
    }
}