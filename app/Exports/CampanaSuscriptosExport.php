<?php

namespace App\Exports;

use App\Campaign;
use App\Suscribe;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;

class CampanaSuscriptosExport implements FromCollection, WithHeadings, WithMapping, WithColumnFormatting
{
    protected $campana;
    protected $preguntas;

    public function __construct(Campaign $campana)
    {
        $this->campana   = $campana;
        $this->preguntas = $campana->preguntas()->orderBy('orden')->get();
    }

    public function collection()
    {
        return Suscribe::with('respuestas')
            ->where('campaign_id', $this->campana->id)
            ->where('idPais', auth()->user()->idPaisPermitido)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function headings(): array
    {
        $base = [
            'Nombre',
            'Apellido',
            'Mail',
            'DNI',
            'Género',
            'Fecha de Nacimiento',
            'Teléfono',
            'Provincia',
            'Localidad',
            'Ocupación Actual',
            'Canal de Contacto',
            'Experiencia Previa',
            'Convertido',
            'Fecha de Registro',
        ];

        foreach ($this->preguntas as $pregunta) {
            $base[] = $pregunta->pregunta;
        }

        return $base;
    }

    public function map($suscripto): array
    {
        $row = [
            $suscripto->nombre,
            $suscripto->apellido,
            $suscripto->mail,
            $suscripto->dni,
            $suscripto->genero,
            $suscripto->fecha_nacimiento ? Date::dateTimeToExcel($suscripto->fecha_nacimiento) : null,
            $suscripto->telefono,
            $suscripto->idProvincia,
            $suscripto->idLocalidad,
            $suscripto->ocupacion_actual,
            $suscripto->canal_contacto,
            $suscripto->experiencia_previa ? 'Sí' : 'No',
            $suscripto->convertido ? 'Sí' : 'No',
            $suscripto->created_at ? Date::dateTimeToExcel($suscripto->created_at) : null,
        ];

        $respuestasPorPregunta = [];
        foreach ($suscripto->respuestas as $respuesta) {
            $respuestasPorPregunta[$respuesta->pregunta_id] = $respuesta->respuesta;
        }

        foreach ($this->preguntas as $pregunta) {
            $row[] = isset($respuestasPorPregunta[$pregunta->id])
                ? $respuestasPorPregunta[$pregunta->id]
                : null;
        }

        return $row;
    }

    public function columnFormats(): array
    {
        return [
            'F' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'N' => NumberFormat::FORMAT_DATE_DDMMYYYY,
        ];
    }
}
