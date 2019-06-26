<?php

namespace App\Exports;

use App\Actividad;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class ActividadesExport implements FromCollection, WithHeadings, WithColumnFormatting, WithMapping
{
    protected $filter;
    protected $sort;


    public function __construct($filter = null, $sort = 'nombreActividad|asc')
    {
        $this->filter = $filter;
        $this->sort = $sort;
    }

    public function collection()
    {
        $sort = explode('|', $this->sort);
        list($sortField, $sortOrder) = $sort;

        $result = DB::table('Actividad')
            ->leftJoin('atl_oficinas', 'Actividad.idOficina', '=', 'atl_oficinas.id')
            ->leftJoin('Tipo', 'Tipo.idTipo', '=', 'Actividad.idTipo')
            ->leftJoin('atl_CategoriaActividad', 'Tipo.idCategoria', '=', 'atl_CategoriaActividad.id')
            ->select(
                [
                    'Actividad.idActividad AS id',
                    'nombreActividad',
                    'fechaInicio',
                    'fechaFin',
                    'estadoConstruccion',
                    'atl_oficinas.nombre AS oficina',
                    'Tipo.nombre AS tipoActividad',
                    'atl_CategoriaActividad.nombre as nombreCategoria',
                ]
            )
            ->whereNull('deleted_at')
            ->orderBy($sortField, $sortOrder);

        if ($this->filter) {
            $filter = $this->filter;
            $result->orWhere(function ($result) use ($filter) {
                $result->orWhere('nombreActividad', 'like', '%' . $filter . '%');
                $result->orWhere('estadoConstruccion', 'like', '%' . $filter . '%');
                $result->orWhere('Tipo.nombre', 'like', '%' . $filter . '%');
                $result->orWhere('atl_oficinas.nombre', 'like', '%' . $filter . '%');
            });
        }
        $var = $result->get();
        $act = Actividad::hydrate($var->toArray());
        return $act;
    }

    public function columnFormats(): array
    {
        return [
            'C' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'D' => NumberFormat::FORMAT_DATE_DDMMYYYY,
        ];
    }

    public function map($actividad): array
    {
        return [
            $actividad->id,
            $actividad->nombreActividad,
            Date::dateTimeToExcel($actividad->fechaInicio),
            Date::dateTimeToExcel($actividad->fechaFin),
            $actividad->estadoConstruccion,
            $actividad->oficina,
            $actividad->tipoActividad,
            $actividad->nombreCategoria

        ];
    }

    public function headings(): array
    {
        return [
            'ID de la Actividad',
            'Nombre de la Actividad',
            'Fecha De Inicio',
            'Fecha de Finalización',
            'Estado',
            'Oficina',
            'Tipo de Actividad',
            'Categoría de la Actividad',
        ];
    }
}