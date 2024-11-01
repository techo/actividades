<?php

namespace App\Exports;

use App\Search\InscripcionesSearch;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class PersonasInscriptasExport implements FromCollection, WithHeadings, WithColumnFormatting, WithMapping, WithStrictNullComparison, ShouldAutoSize
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $fecha_desde = ($this->request->filled('fecha_desde'))?$this->request->fecha_desde:Carbon::now()->startOfYear()->format('Y-m-d');
        $fecha_hasta = ($this->request->filled('fecha_hasta'))?$this->request->fecha_hasta:Carbon::now()->format('Y-m-d');

        $edad_desde = ($this->request->filled('edad_desde')) ? $this->request->edad_desde : 0;
        $edad_hasta = ($this->request->filled('edad_hasta')) ? $this->request->edad_hasta : null;

        $oficina = ($this->request->filled('oficina'))?$this->request->oficina:null;

        $consulta = \App\Persona::join('Inscripcion', 'Persona.idPersona', '=', 'Inscripcion.idPersona')
            ->join('Actividad', 'Inscripcion.idActividad', '=', 'Actividad.idActividad')
            ->where('Actividad.idPais', auth()->user()->idPaisPermitido)
            ->select(
                [
                    DB::raw('Persona.idPersona as id, nombres, apellidoPaterno, count(*) as inscripciones, sum(if(presente=1,1,0)) as presentes'),
                    'Persona.dni',
                    'Persona.nombres',
                    'Persona.apellidoPaterno',
                    'Persona.genero',
                    'Persona.mail',
                    'Persona.telefonoMovil',
                    'Persona.fechaNacimiento',
                    'oficina.nombre as oficina',
                ]
            )
            ->leftJoin('atl_provincias', 'Persona.idProvincia', '=', 'atl_provincias.id')
            ->leftJoin('atl_oficinas as oficina', 'atl_provincias.idOficina', '=', 'oficina.id')
            ->groupBy(['Persona.idPersona', 'nombres', 'apellidoPaterno']);

        if($fecha_desde && $fecha_hasta)
            $consulta->whereBetween('Inscripcion.created_at', [$fecha_desde, $fecha_hasta]);
        if($edad_hasta)
            $consulta->whereRaw("TIMESTAMPDIFF(YEAR, Persona.fechaNacimiento, CURDATE()) BETWEEN ".$edad_desde." AND ". $edad_hasta);
        if($oficina) $consulta->where('Actividad.idOficina', $oficina);
        
        // Aquí obtenemos la consulta SQL sin bindings
$sql = $consulta->toSql();

// Y aquí obtenemos los bindings (parámetros) que serán usados en la consulta
$bindings = $consulta->getBindings();

Log::info($sql);
Log::info($bindings);

        return $consulta->get();
    }

    public function headings(): array
    {
        return [
            //'ID del Voluntario',
            'dni',
            'nombre',
            'apellido',
            'teléfono',
            'email',
            'fecha de nacimiento',
            'oficina',
            'género',
            'inscripciones totales',
            'presentes totales',
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
            case 'X':
                $genero = 'Otro';
                break;
            default:
                $genero = 'Sin Especificar';
                break;
        }
        return [
            $query->dni ?? '', // Proteger contra valores nulos
            $query->nombres ?? '',
            $query->apellidoPaterno ?? '',
            $query->telefonoMovil ?? '',
            $query->mail ?? '',
            Date::dateTimeToExcel(Carbon::parse($query->fechaNacimiento)),
            $query->oficina ?? '',
            $genero,
            $query->inscripciones ?? 0,
            $query->presentes ?? 0,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'F' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'H' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'N' => NumberFormat::FORMAT_DATE_DDMMYYYY,
        ];
    }


}
