<?php
namespace App\Exports;

use App\Inscripcion;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class InscripcionesUsuarioExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $persona;

    public function __construct($persona)
    {
        $this->persona = $persona;
    }

    public function collection()
    {
        return \App\Inscripcion::where('idPersona', '=', $this->persona)
            ->join('Actividad', 'Actividad.idActividad', '=', 'Inscripcion.idActividad')
            ->join('Tipo', 'Actividad.idTipo', '=', 'Tipo.idTipo')
            ->select([
                'Actividad.nombreActividad',
                'Tipo.nombre',
                'fechaInscripcion', 
                'rol',
                'estado',
                'presente',
            ])
            ->get();
    }

    public function headings(): array
    {
        return [
            'NombreActividad',
            'Tipo',
            'Fecha',
            'Rol',
            'Estado',
            'Presente'
        ];
    }
}