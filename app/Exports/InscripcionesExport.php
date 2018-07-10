<?php

namespace App\Exports;

use App\Search\InscripcionesSearch;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class InscripcionesExport implements FromCollection, WithHeadings, WithColumnFormatting, WithMapping, WithStrictNullComparison, ShouldAutoSize
{
    protected $filter;

    public function __construct($filter)
    {
        $this->filter = $filter;
    }

    public function collection()
    {
        $inscriptos = InscripcionesSearch::apply($this->filter);
        return $inscriptos;
    }

    public function headings(): array
    {
        return [
            'ID del Voluntario',
            'DNI',
            'Nombre',
            'Apellido',
            'Teléfono Móvil',
            'Email',
            'Fecha de nacimiento',
            'Genero',
            'País',
            'Provincia',
            'Localidad',
            'Fecha De Inscripción',
            'Asistencia A La Actividad',
            'Pago',
            'Costo De La Actividad',
            'Punto de Encuentro',
            'País del punto de encuentro',
            'Localidad del punto de encuentro',
            'Provincia del Punto de encuentro',
            'Cantidad de Actividades (según filtro previo)'
        ];
    }

    public function map($query): array
    {
        switch ($query->sexo) {
            case 'M':
                $genero = 'Masculino';
                break;
            case 'F':
                $genero = 'Femenino';
                break;
            default:
                $genero = 'Sin Especificar';
                break;
        }

        return [
            $query->idPersona,
            $query->dni,
            $query->nombres,
            $query->apellidoPaterno,
            $query->telefonoMovil,
            $query->mail,
            Date::dateTimeToExcel(Carbon::parse($query->fechaNacimiento)),
            $genero,
            $query->pPais,
            $query->pProvincia,
            $query->pLocalidad,
            Date::dateTimeToExcel(Carbon::parse($query->fechaInscripcion)),
            is_null($query->presente) ? 'No' : 'Si',
            is_null($query->pago) ? 'No' : 'Si',
            $query->costo,
            $query->punto,
            $query->puntoPais,
            $query->puntoLocalidad,
            $query->puntoProvincia,
            $query->cantActividades
        ];
    }

    public function columnFormats(): array
    {
        return [
            'B' => NumberFormat::FORMAT_TEXT,
            'E' => NumberFormat::FORMAT_TEXT,
            'G' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'L' => NumberFormat::FORMAT_DATE_DDMMYYYY,
        ];
    }


}