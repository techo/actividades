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
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class InscripcionesExport implements FromCollection, WithHeadings, WithColumnFormatting, WithMapping, WithStrictNullComparison, ShouldAutoSize

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
            ->join('PuntoEncuentro', 'PuntoEncuentro.idPuntoEncuentro', '=', 'Inscripcion.idPuntoEncuentro')
            ->leftJoin('atl_pais', 'PuntoEncuentro.idPais', '=', 'atl_pais.id')
            ->leftJoin('atl_provincias', 'PuntoEncuentro.idProvincia', '=', 'atl_provincias.id')
            ->leftJoin('atl_localidades', 'PuntoEncuentro.idLocalidad', '=', 'atl_localidades.id')
            ->leftJoin('atl_pais as personaPais', 'Persona.idPais', '=', 'personaPais.id')
            ->leftJoin('atl_provincias as personaProvincia', 'Persona.idProvincia', '=', 'personaProvincia.id')
            ->leftJoin('atl_localidades as personaLocalidad', 'Persona.idLocalidad', '=', 'personaLocalidad.id')
            ->select(
                [
                    'Inscripcion.idPersona',
                    'Persona.dni',
                    'Persona.nombres',
                    'Persona.apellidoPaterno',
                    'Persona.telefonoMovil',
                    'Persona.mail',
                    'Persona.fechaNacimiento',
                    'Persona.sexo',
                    'personaPais.nombre as pPais',
                    'personaProvincia.provincia as pProvincia',
                    'personaLocalidad.localidad as pLocalidad',
                    'Inscripcion.fechaInscripcion',
                    'Inscripcion.idInscripcion AS id',
                    'Inscripcion.presente',
                    'Inscripcion.pago',
                    'Inscripcion.estado',
                    'PuntoEncuentro.punto',
                    'atl_pais.nombre as puntoPais',
                    'atl_provincias.provincia as puntoProvincia',
                    'atl_localidades.localidad as puntoLocalidad',
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
            'Provincia del Punto de encuentro'
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
            $query->puntoProvincia
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