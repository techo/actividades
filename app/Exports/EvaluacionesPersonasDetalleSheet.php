<?php
namespace App\Exports;

use App\EvaluacionPersona;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EvaluacionesPersonasDetalleSheet implements FromCollection, WithHeadings, ShouldAutoSize, WithMapping, WithStyles, WithTitle
{
    protected $actividad;

    public function __construct($actividad)
    {
        $this->actividad = $actividad;
    }

    public function title(): string
    {
        return 'Detalle';
    }

    public function collection()
    {
        return EvaluacionPersona::where('EvaluacionPersona.idActividad', $this->actividad->idActividad)
            ->with('respuestas')
            ->join('Persona as evaluado', 'evaluado.idPersona', '=', 'EvaluacionPersona.idEvaluado')
            ->join('Persona as evaluador', 'evaluador.idPersona', '=', 'EvaluacionPersona.idEvaluador')
            ->select(
                'EvaluacionPersona.idEvaluacionPersona',
                'EvaluacionPersona.comentario',
                'evaluado.nombres as evaluado_nombres',
                'evaluado.apellidoPaterno as evaluado_apellido',
                'evaluado.dni as evaluado_dni',
                'evaluador.nombres as evaluador_nombres',
                'evaluador.apellidoPaterno as evaluador_apellido'
            )
            ->get();
    }

    public function map($row): array
    {
        $respuestas = $row->respuestas->keyBy('question_key');

        return [
            $row->evaluado_nombres,
            $row->evaluado_apellido,
            $row->evaluado_dni,
            $row->evaluador_nombres,
            $row->evaluador_apellido,
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
            'Evaluado - Nombre',
            'Evaluado - Apellido',
            'Evaluado - DNI',
            'Evaluador - Nombre',
            'Evaluador - Apellido',
            'Conexión y Comunidad',
            'Compromiso y Colaboración',
            'Actitud Propositiva',
            'Potencia a Otros',
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
