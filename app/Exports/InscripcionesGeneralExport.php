<?php

namespace App\Exports;

use App\Search\InscripcionesSearch;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class InscripcionesGeneralExport implements FromCollection, WithHeadings, WithColumnFormatting, WithMapping, WithStrictNullComparison, ShouldAutoSize
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $año = ($this->request->filled('año'))?$this->request->año:Carbon::now()->format('Y');
        $pais = ($this->request->filled('pais'))?$this->request->pais:null;
        $oficina = ($this->request->filled('oficina'))?$this->request->oficina:null;

        $consulta = \App\Inscripcion::join('Persona', 'Persona.idPersona', '=', 'Inscripcion.idPersona')
            ->join('Actividad', 'Actividad.idActividad', '=', 'Inscripcion.idActividad')
            ->join('Tipo', 'Tipo.idTipo', '=', 'Actividad.idTipo')
            ->join('atl_CategoriaActividad', 'atl_CategoriaActividad.id', '=', 'Tipo.idCategoria')
            ->select(DB::raw('dni, nombres, apellidoPaterno, telefonoMovil, mail, fechaNacimiento, sexo, Inscripcion.fechaInscripcion, presente, rol, Actividad.nombreActividad, atl_CategoriaActividad.nombre as categoria, Tipo.nombre as tipo, Actividad.fechaInicio'))
            ->whereYear('Inscripcion.created_at', $año);

        if($pais) $consulta->where('Actividad.idPais', $pais);
        if($oficina) $consulta->where('Actividad.idOficina', $oficina);

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
            'género',
            'fecha de inscripción',
            'presente',
            'rol',
            'actividad',
            'categoría',
            'tipo',
            'fecha de la actividad'
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
            case 'X':
                $genero = 'Otro';
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
            Date::dateTimeToExcel(Carbon::parse($query->fechaInscripcion)),
            (is_null($query->presente) || $query->presente == 0) ? 0 : 1,
            $query->rol,
            $query->nombreActividad,
            $query->categoria,
            $query->tipo,
            Date::dateTimeToExcel(Carbon::parse($query->fechaInicio))
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
