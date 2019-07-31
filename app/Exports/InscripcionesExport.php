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
            //'ID del Voluntario',
            'dni',
            'nombre',
            'apellido',
            'teléfono Móvil',
            'email',
            'fecha de nacimiento',
            'genero',
            'país',
            'provincia',
            'localidad',
            'fecha de inscripción',
            'cantidad de actividades (según filtro previo)',
            'presente',
            'pago',
            'confirma',
            'estado',
            'punto de encuentro',
            'grupo',
            'rol',
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
            //$query->idPersona,
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
            $query->cantActividades,
            (is_null($query->presente) || $query->presente == 0) ? 'No' : 'Si',
            (is_null($query->pago) || $query->pago == 0) ? 'No' : 'Si',
            (is_null($query->confirma) || $query->confirma == 0) ? 'No' : 'Si',
            $query->estado,
            $query->punto,
            $query->nombreGrupo,
            $query->nombreRol,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT,
            'D' => NumberFormat::FORMAT_TEXT,
            'F' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'K' => NumberFormat::FORMAT_DATE_DDMMYYYY,
        ];
    }


}
