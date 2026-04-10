<?php
namespace App\Exports;

use App\EvaluacionImpactoActividad;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EvaluacionesImpactoExport implements FromCollection, WithHeadings, ShouldAutoSize, WithMapping, WithStyles
{
    protected $actividad;

    public function __construct($actividad)
    {
        $this->actividad = $actividad;
    }

    public function collection()
    {
        return EvaluacionImpactoActividad::where('idActividad', $this->actividad->idActividad)
            ->join('Persona', 'Persona.idPersona', '=', 'EvaluacionImpactoActividad.idPersona')
            ->select(
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
            'Nombre',
            'Apellido',
            'DNI',
            'Email',
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
