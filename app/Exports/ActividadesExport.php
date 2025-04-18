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
    protected $idComunidad;


    public function __construct($filter = null, $sort = 'nombreActividad|asc', $idComunidad = null)
    {
        $this->filter = $filter;
        $this->sort = $sort;
        $this->idComunidad = $idComunidad;
    }

    public function collection()
    {
        $sort = explode('|', $this->sort);
        list($sortField, $sortOrder) = $sort;

        $result = \App\Actividad::select(
                [
                    'Actividad.idActividad AS id',
                    'nombreActividad',
                    'fechaInicio',
                    'fechaFin',
                    'estadoConstruccion',
                    'atl_oficinas.nombre AS oficina',
                    'Tipo.nombre AS tipoActividad',
                    'atl_CategoriaActividad.nombre as nombreCategoria',
                    'atl_pais.nombre AS pais',
                    DB::raw('(SELECT GROUP_CONCAT(c.nombre SEPARATOR ";")
                              FROM actividad_comunidad ac
                              JOIN Comunidad c ON c.idComunidad = ac.idComunidad
                              WHERE ac.idActividad = Actividad.idActividad) AS comunidades'),

                    'fechaInicioInscripciones',
                    'fechaFinInscripciones',
                    'fechaInicioEvaluaciones',
                    'fechaFinEvaluaciones',
                    'pago'
                ]
            )
            ->leftJoin('atl_oficinas', 'Actividad.idOficina', '=', 'atl_oficinas.id')
            ->leftJoin('Tipo', 'Tipo.idTipo', '=', 'Actividad.idTipo')
            ->leftJoin('atl_CategoriaActividad', 'Tipo.idCategoria', '=', 'atl_CategoriaActividad.id')
            ->leftJoin('atl_pais', 'Actividad.idPais', '=', 'atl_pais.id')
            ->orderBy($sortField, $sortOrder);

// cambiar esto
              $result->where('Actividad.idPais', '=', auth()->user()->idPaisPermitido);
        
        if ($this->idComunidad) {
            $result->join('actividad_comunidad', 'actividad_comunidad.idActividad', '=', 'Actividad.idActividad')
                    ->where('actividad_comunidad.idComunidad', $this->idComunidad);
        }

        if ($this->filter) {
            $palabras = explode(' ',$this->filter);
            foreach ($palabras as $palabra)
                $result->whereRaw("concat( COALESCE(nombreActividad,''), ' ', COALESCE(Tipo.nombre,''), ' ', COALESCE(atl_oficinas.nombre,'')) like '%". $palabra ."%' ");
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
            ($actividad->fechaInicio)?Date::dateTimeToExcel($actividad->fechaInicio):null,
            ($actividad->fechaFin)?Date::dateTimeToExcel($actividad->fechaFin):null,
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
            'País',
        ];
    }
}