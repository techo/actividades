<?php

namespace App\Exports;

use App\Search\InscripcionesSearch;
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
            'oficina',
            'grupo sanguineo',
            'cobertura medica numero',
            'cobertura medica numero',
            'contacto nombre',
            'contacto numero',
            'contacto relacion',
            'alergias',
            'vacunacion_covid',
            'alimentacion',
            'Documento Frente',
            'Documento Dorso',
            'fecha de inscripción',
            'cantidad de actividades (según filtro previo)',
            'presente',
            'pago',
            'voucherUrl',
            'confirma',
            'punto de encuentro',
            'grupo',
            'rol',
            'roles Aplicados',
            'Tipo de Inscripcion',
            'Jornadas'
        ];
    }

    public function map($query): array
    {
        switch ($query->genero) {
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

            // Obtener las jornadas relacionadas con la inscripción
            $jornadas = DB::table('InscripcionJornada')
            ->join('Jornada', 'InscripcionJornada.idJornada', '=', 'Jornada.idJornada')
            ->where('InscripcionJornada.idInscripcion', $query->id)
            ->select('Jornada.nombre', 'Jornada.fechaInicio')
            ->get()
            ->map(function ($jornada) {
                return $jornada->nombre . ' (' . Carbon::parse($jornada->fechaInicio)->format('d/m/Y H:i') . ')';
            })
            ->toArray();

            // Combinar las jornadas en un solo string, separadas por comas
            $jornadasString = implode(' | ', $jornadas);

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
            $query->oficina,
            $query->grupo_sanguineo,
            $query->cobertura_nombre,
            $query->cobertura_numero,
            $query->contacto_nombre,
            $query->contacto_telefono,
            $query->contacto_relacion,
            $query->alergias,
            $query->vacunacion_covid,
            $query->alimentacion,
            env("APP_URL").'/'.$query->documento_frente,
            env("APP_URL").'/'.$query->documento_dorso,
            Date::dateTimeToExcel(Carbon::parse($query->fechaInscripcion)),
            $query->cantActividades,
            (is_null($query->presente) || $query->presente == 0) ? 'No' : 'Si',
            (is_null($query->pago) || $query->pago == 0) ? 'No' : 'Si',
            $query->voucherUrl,
            (is_null($query->confirma) || $query->confirma == 0) ? 'No' : 'Si',
            $query->punto,
            $query->nombreGrupo,
            $query->nombreRol,
            $query->roles_aplicados,
            $query->inscripciones_aplicadas,
            $jornadasString,
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
