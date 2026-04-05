<?php
namespace App\Exports;

use App\EvaluacionPersona;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EvaluacionesPersonasGeneralResumenSheet implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles, WithTitle
{
    protected $request;

    const QUESTION_KEYS = [
        'conexion_equipo',
        'compromiso_colaboracion',
        'actitud_propositiva',
        'potencia_otras',
    ];

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function title(): string
    {
        return 'Resumen por Persona';
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

        $evaluaciones = EvaluacionPersona::whereIn('idActividad', $actividadIds)
            ->with('respuestas')
            ->get();

        $personaIds = $evaluaciones->pluck('idEvaluado')->unique()->toArray();
        $personas = \App\Persona::whereIn('idPersona', $personaIds)
            ->select('idPersona', 'nombres', 'apellidoPaterno', 'dni', 'mail')
            ->get()->keyBy('idPersona');

        $grouped = $evaluaciones->groupBy('idEvaluado');
        $rows = [];

        foreach ($grouped as $idEvaluado => $evals) {
            $persona = $personas->get($idEvaluado);

            $allRespuestas = collect();
            foreach ($evals as $eval) {
                foreach ($eval->respuestas as $r) {
                    $allRespuestas->push($r);
                }
            }

            $row = [
                $persona ? $persona->nombres         : '',
                $persona ? $persona->apellidoPaterno : '',
                $persona ? $persona->dni             : '',
                $persona ? $persona->mail            : '',
            ];

            $keyAvgs = [];
            foreach (self::QUESTION_KEYS as $key) {
                $scores = $allRespuestas->where('question_key', $key)->pluck('score')->filter()->values();
                $avg    = $scores->count() > 0 ? round($scores->avg(), 1) : null;
                $row[]  = $avg;
                if ($avg !== null) {
                    $keyAvgs[] = $avg;
                }
            }

            $row[] = count($keyAvgs) > 0 ? round(array_sum($keyAvgs) / count($keyAvgs), 1) : null;
            $row[] = $evals->count();
            $rows[] = $row;
        }

        return collect($rows);
    }

    public function headings(): array
    {
        return [
            'Nombre', 'Apellido', 'DNI', 'Email',
            'Conexión y Comunidad', 'Compromiso y Colaboración',
            'Actitud Propositiva', 'Potencia a Otros',
            'Promedio Global', 'Cant. Evaluadores',
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
