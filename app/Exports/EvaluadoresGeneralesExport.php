<?php
namespace App\Exports;

use App\EvaluacionPersona;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class EvaluadoresGeneralesExport implements FromCollection, WithHeadings, ShouldAutoSize
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

        $consulta = \App\Persona::join('Inscripcion', 'Persona.idPersona', '=', 'Inscripcion.idPersona')
            ->join('Actividad', 'Actividad.idActividad', '=', 'Inscripcion.idActividad')
            ->join('atl_oficinas', 'Actividad.idOficina', '=', 'atl_oficinas.id')
            ->select('Persona.nombres', 'Persona.apellidoPaterno', 'Persona.mail', 'Actividad.nombreActividad', 'Inscripcion.rol', 
                DB::raw(' (select count(*) from EvaluacionPersona where Persona.idPersona = EvaluacionPersona.idEvaluador
                            and EvaluacionPersona.idActividad = Actividad.idActividad) as evaluacionPersonas'),
                DB::raw(' (select count(*) from EvaluacionActividad where Persona.idPersona = EvaluacionActividad.idPersona
                            and EvaluacionActividad.idActividad = Actividad.idActividad) as evaluacionActividad'))
            ->whereYear('Inscripcion.created_at', $año)
            ->where('Inscripcion.presente', 1);

        if($pais) $consulta->where('Actividad.idPais', $pais);
        if($oficina) $consulta->where('Actividad.idOficina', $oficina);

        return $consulta->get();
    }

    public function headings(): array
    {
        return [
            'Nombre',
            'Apellido',
            'Email',
            'Nombre actividad',
            'Rol',
            'Evaluacion a Compañerxs',
            'Evaluacion Actividad',
        ];
    }

}