<?php

namespace App\Exports;

use App\Jornada;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class JornadasExport implements FromCollection, WithHeadings, WithColumnFormatting, WithMapping
{
    protected $filter;
    protected $sort;
    protected $idActividad;


    public function __construct($filter = null, $sort = 'nombre|asc', $idActividad)
    {
        $this->filter = $filter;
        $this->sort = $sort;
        $this->idActividad = $idActividad;
    }

    public function collection()
    {
        $sort = explode('|', $this->sort);
        list($sortField, $sortOrder) = $sort;

        $result = Jornada::select(
                [
                    'idActividad AS idActividad',
                    'nombre',
                    'idJornada',
                    'Persona.idPersona',
                    'Persona.nombres',
                    'Persona.apellidoPaterno',
                    'Persona.mail',
                    'fechaInicio',
                    'fechaFin',
                    'activo',
                ]
            )
            ->join('Persona','Persona.idPersona', '=','Jornada.idPersona')
            ->where('idActividad', '=', $this->idActividad)
            ->orderBy($sortField, $sortOrder);

        $var = $result->get();
        // $act = Actividad::hydrate($var->toArray());
        return $var;
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