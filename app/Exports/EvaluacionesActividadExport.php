<?php
namespace App\Exports;

use App\EvaluacionActividad;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EvaluacionesActividadExport implements FromCollection, WithHeadings, ShouldAutoSize, WithMapping, WithStyles
{
    protected $actividad;

    public function __construct($actividad)
    {
        $this->actividad = $actividad;
    }

    public function collection()
    {
        return EvaluacionActividad::where('idActividad', $this->actividad->idActividad)->get();
    }

    public function map($row): array
    {
        $positivos = is_array($row->tags_positivos) ? implode(', ', $row->tags_positivos) : '';
        $negativos = is_array($row->tags_negativos) ? implode(', ', $row->tags_negativos) : '';

        return [
            $row->puntaje,
            $positivos,
            $negativos,
            $row->comentario,
        ];
    }

    public function headings(): array
    {
        return [
            'Puntaje',
            'Atributos Positivos',
            'Puntos de Mejora',
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
