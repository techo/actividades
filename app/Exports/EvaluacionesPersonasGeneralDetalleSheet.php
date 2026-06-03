<?php
namespace App\Exports;

use App\EvaluacionPersona;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EvaluacionesPersonasGeneralDetalleSheet implements FromCollection, WithHeadings, ShouldAutoSize, WithMapping, WithStyles, WithTitle
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function title(): string
    {
        return 'Detalle';
    }

    public function collection()
    {
        $año     = $this->request->filled('año')     ? $this->request->año     : Carbon::now()->format('Y');
        $pais    = $this->request->filled('pais')    ? $this->request->pais    : null;
        $oficina = $this->request->filled('oficina') ? $this->request->oficina : null;

        $q = \App\Actividad::whereYear('fechaCreacion', $año);
        if ($pais)    $q->where('idPais', $pais);
        if ($oficina) $q->where('idOficina', $oficina);
        $actividadIds = $q->pluck('idActividad');

        return EvaluacionPersona::whereIn('EvaluacionPersona.idActividad', $actividadIds)
            ->with('respuestas')
            ->join('Actividad', 'Actividad.idActividad', '=', 'EvaluacionPersona.idActividad')
            ->join('Persona as evaluado', 'evaluado.idPersona', '=', 'EvaluacionPersona.idEvaluado')
            ->select(
                'EvaluacionPersona.idEvaluacionPersona',
                'EvaluacionPersona.comentario',
                'Actividad.nombreActividad',
                'evaluado.nombres as evaluado_nombres',
                'evaluado.apellidoPaterno as evaluado_apellido',
                'evaluado.dni as evaluado_dni'
            )
            ->get();
    }

    public function map($row): array
    {
        $respuestas = $row->respuestas->keyBy('question_key');

        return [
            $row->nombreActividad,
            $row->evaluado_nombres,
            $row->evaluado_apellido,
            $row->evaluado_dni,
            optional($respuestas->get('conexion_equipo'))->score,
            optional($respuestas->get('compromiso_colaboracion'))->score,
            optional($respuestas->get('actitud_propositiva'))->score,
            optional($respuestas->get('potencia_otras'))->score,
            $row->comentario,
        ];
    }

    public function headings(): array
    {
        return [
            'Actividad',
            'Evaluado - Nombre', 'Evaluado - Apellido', 'Evaluado - DNI',
            'Conexión y Comunidad', 'Compromiso y Colaboración',
            'Actitud Propositiva', 'Potencia a Otros',
            'Comentario',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF2980B9']],
            ],
        ];
    }
}
