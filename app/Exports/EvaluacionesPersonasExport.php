<?php
namespace App\Exports;

use App\EvaluacionPersona;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class EvaluacionesPersonasExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $actividad;


    public function __construct($actividad)
    {
        $this->actividad = $actividad;
    }

    public function collection()
    {
        return EvaluacionPersona::join('Persona', 'Persona.idPersona', '=', 'EvaluacionPersona.idEvaluado')
            ->select(
                ['Persona.nombres', 'Persona.apellidoPaterno', 'Persona.dni', 'Persona.mail',
                    'EvaluacionPersona.puntajeSocial', 'EvaluacionPersona.puntajeTecnico', 'EvaluacionPersona.comentario']
            )
            ->where('idActividad', $this->actividad->idActividad)
            ->get();
    }

    public function headings(): array
    {
        return [
            'Nombre',
            'Apellido',
            'DNI',
            'Email',
            'Puntaje Social',
            'Puntaje Técnico',
            'Comentario'
        ];
    }

/*    public function map($query): array
    {
        return [
            $query->nombres,
            $query->,
        ];
    }*/
}