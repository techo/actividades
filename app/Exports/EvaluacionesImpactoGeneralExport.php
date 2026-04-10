<?php
namespace App\Exports;

use App\EvaluacionImpactoActividad;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EvaluacionesImpactoGeneralExport implements FromCollection, WithHeadings, ShouldAutoSize, WithMapping, WithStyles
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
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

        return EvaluacionImpactoActividad::whereIn('EvaluacionImpactoActividad.idActividad', $actividadIds)
            ->join('Actividad', 'Actividad.idActividad', '=', 'EvaluacionImpactoActividad.idActividad')
            ->join('Persona',   'Persona.idPersona',     '=', 'EvaluacionImpactoActividad.idPersona')
            ->select(
                'Actividad.nombreActividad',
                'Persona.nombres',
                'Persona.apellidoPaterno',
                'Persona.dni',
                'Persona.mail',
                'EvaluacionImpactoActividad.impacto_habilidades_capacidades',
                'EvaluacionImpactoActividad.impacto_percepcion_realidad',
                'EvaluacionImpactoActividad.impacto_recomendaria_experiencia'
            )
            ->get();
    }

    public function map($row): array
    {
        return [
            $row->nombreActividad,
            $row->nombres,
            $row->apellidoPaterno,
            $row->dni,
            $row->mail,
            $row->impacto_habilidades_capacidades,
            $row->impacto_percepcion_realidad,
            $row->impacto_recomendaria_experiencia,
        ];
    }

    public function headings(): array
    {
        return [
            'Actividad', 'Nombre', 'Apellido', 'DNI', 'Email',
            'Habilidades y Capacidades (0-10)',
            'Percepción de la Realidad (0-10)',
            'Recomendaría la Experiencia (0-10)',
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
