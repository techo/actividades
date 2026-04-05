<?php
namespace App\Exports;

use App\EvaluacionActividad;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EvaluacionesActividadGeneralExport implements FromCollection, WithHeadings, ShouldAutoSize, WithMapping, WithStyles
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

        return EvaluacionActividad::whereIn('idActividad', $actividadIds)
            ->join('Actividad', 'Actividad.idActividad', '=', 'EvaluacionActividad.idActividad')
            ->select(
                'Actividad.nombreActividad',
                'EvaluacionActividad.puntaje',
                'EvaluacionActividad.tags_positivos',
                'EvaluacionActividad.tags_negativos',
                'EvaluacionActividad.comentario'
            )
            ->get();
    }

    public function map($row): array
    {
        $positivos = is_array($row->tags_positivos) ? implode(', ', $row->tags_positivos) : '';
        $negativos = is_array($row->tags_negativos) ? implode(', ', $row->tags_negativos) : '';

        return [
            $row->nombreActividad,
            $row->puntaje,
            $positivos,
            $negativos,
            $row->comentario,
        ];
    }

    public function headings(): array
    {
        return ['Actividad', 'Puntaje', 'Atributos Positivos', 'Puntos de Mejora', 'Comentario'];
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
