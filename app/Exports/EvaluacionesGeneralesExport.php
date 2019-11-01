<?php
namespace App\Exports;

use App\EvaluacionPersona;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class EvaluacionesGeneralesExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $actividad;


    public function __construct($request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $año = ($this->request->filled('año'))?$this->request->año:Carbon::now()->format('Y');
        $pais = ($this->request->filled('pais'))?$this->request->pais:null;
        $oficina = ($this->request->filled('oficina'))?$this->request->oficina:null;

        $consulta = \App\EvaluacionPersona::join('Persona', 'Persona.idPersona', '=', 'EvaluacionPersona.idEvaluado')
            ->join('Actividad', 'Actividad.idActividad', '=', 'EvaluacionPersona.idActividad')
            ->join('Inscripcion', function($join){
                $join->on('Inscripcion.idPersona', '=', 'Persona.idPersona');
                $join->on('Inscripcion.IdActividad', '=', 'Actividad.idActividad');
            })
            ->join('atl_oficinas', 'Actividad.idOficina', '=', 'atl_oficinas.id')
            ->select('Persona.nombres', 'Persona.apellidoPaterno', 'Persona.dni', 'Persona.mail', 'Actividad.nombreActividad', 'Inscripcion.rol', 
                DB::raw(' atl_oficinas.nombre, AVG(EvaluacionPersona.puntajeSocial) as puntajeSocial'), 
                DB::raw('AVG(EvaluacionPersona.puntajeTecnico) as puntajeTecnico'))
            ->groupBy('Persona.nombres', 'Persona.apellidoPaterno', 'Persona.dni', 'Persona.mail', 'Actividad.nombreActividad', 
                'atl_oficinas.nombre', 'Inscripcion.rol')
            ->whereYear('Inscripcion.created_at', $año);


        if($pais) $consulta->where('Actividad.idPais', $pais);
        if($oficina) $consulta->where('Actividad.idOficina', $oficina);

        return $consulta->get();
    }

    public function headings(): array
    {
        return [
            'Nombre',
            'Apellido',
            'DNI',
            'Email',
            'Nombre actividad',
            'Rol',
            'Oficina',
            'Puntaje Social',
            'Puntaje Técnico',
        ];
    }

}