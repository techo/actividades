<?php
namespace App\Exports;

use App\EvaluacionPersona;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class EvaluacionesUsuarioExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $persona;

    public function __construct($persona)
    {
        $this->persona = $persona;
    }

    public function collection()
    {
        return EvaluacionPersona::where('idEvaluado', '=', $this->persona)
            ->join('Actividad', 'Actividad.idActividad', '=', 'EvaluacionPersona.idActividad')
            ->join('Tipo', 'Actividad.idTipo', '=', 'Tipo.idTipo')
            ->select([
                "Actividad.nombreActividad",
                "Tipo.nombre",
                "Actividad.fechaInicio",
                "puntajeSocial",
                "puntajeTecnico",
                "puntajeGenero",
                "comentario",
            ])
            ->get();
    }

    public function headings(): array
    {
        return [
            'NombreActividad',
            'Tipo',
            'Fecha',
            'Puntaje Social',
            'Puntaje Técnico',
            'Puntaje perspectiva de género',
            'Comentario'
        ];
    }
}