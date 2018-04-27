<?php

namespace App\Exports;

use App\Actividad;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class InscripcionesExport implements FromCollection, WithHeadings, WithColumnFormatting, WithMapping, WithStrictNullComparison

{
    protected $idActividad;
    protected $filter;
    protected $sort;

    public function __construct($idActividad, $filter = null, $sort = 'nombreActividad|asc')
    {
        $this->idActividad = (int)$idActividad;
        $this->filter = $filter;
        $this->sort = $sort;
    }

    public function collection()
    {
        $sort = explode('|', $this->sort);
        list($sortField, $sortOrder) = $sort;

        $query = DB::table('Persona')
            ->join('Inscripcion', 'Persona.idPersona', '=', 'Inscripcion.idPersona')
            ->join('Actividad', 'Inscripcion.idActividad', '=', 'Actividad.idActividad')
            ->select(
                [
                    'Inscripcion.idPersona',
                    'Persona.dni',
                    'Persona.nombres',
                    'Persona.apellidoPaterno',
                    'Persona.telefonoMovil',
                    'Persona.mail',
                    'Inscripcion.fechaInscripcion',
                    'Inscripcion.idInscripcion AS id',
                    'Inscripcion.pago',
                    'Inscripcion.presente',
                    'Inscripcion.estado',
                    'Actividad.costo',
                    'Actividad.idActividad'
                ]
            )
            ->where('Inscripcion.idActividad', $this->idActividad)
            ->where('Inscripcion.estado', '<>', 'Desinscripto')
            ->orderBy($sortField, $sortOrder);

        if ($this->filter) {
            $filter = $this->filter;
            $query->where(function ($query) use ($filter) {
                $query->orWhere('Persona.nombres', 'like', '%' . $filter . '%');
                $query->orWhere('Persona.apellidoPaterno', 'like', '%' . $filter . '%');
                $query->orWhere('Persona.dni', 'like', '%' . $filter . '%');
                $query->orWhere('Persona.mail', 'like', '%' . $filter . '%');
                if (strtolower($filter) === 'pagado') {
                    $query->orWhere('Inscripcion.pago', 1);
                }
                if (strtolower($filter) === 'pendiente') {
                    $query->orWhere('Inscripcion.pago', 0);
                    $query->orWhereNull('Inscripcion.pago');
                }
                if (strtolower($filter) === 'presente') {
                    $query->orWhere('Inscripcion.presente', 1);
                }
                if (strtolower($filter) === 'ausente') {
                    $query->orWhere('Inscripcion.presente', 0);
                    $query->orWhereNull('Inscripcion.presente');
                }
            });
        }

        $var = $query->get();
        $act = Actividad::hydrate($var->toArray());
        return $act;
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
            'Fecha De Inscripción',
            'Asistencia A La Actividad',
            'Pago',
            'Costo De La Actividad',
        ];
    }

    public function map($persona): array
    {
        return [
            $persona->idPersona,
            $persona->dni,
            $persona->nombres,
            $persona->apellidoPaterno,
            $persona->telefonoMovil,
            $persona->mail,
            Date::dateTimeToExcel(Carbon::parse($persona->fechaInscripcion)),
            $persona->presente,
            $persona->pago,
            $persona->costo,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'B' => NumberFormat::FORMAT_TEXT,
            'E' => NumberFormat::FORMAT_TEXT,
            'G' => NumberFormat::FORMAT_DATE_DDMMYYYY,
        ];
    }


}