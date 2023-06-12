<?php

namespace App\Exports;

use App\Suscribe;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class SuscriptosExport implements FromCollection, WithHeadings, WithColumnFormatting, WithMapping
{
    protected $filter;
    protected $sort;


    public function __construct($filter = null, $sort = 'created_at|desc')
    {
        $this->filter = $filter;
        $this->sort = $sort;
    }

    public function collection()
    {
        $sort = explode('|', $this->sort);
        list($sortField, $sortOrder) = $sort;

        $result = \App\Suscribe::select(
                ['mail', 'idPersona', 'perfil_seleccionado', 'tematica', 'tiempo_disponible', 'created_at']
            )
            ->orderBy($sortField, $sortOrder);


              $result->where('idPais', '=', auth()->user()->idPaisPermitido);

        if ($this->filter) {
            $palabras = explode(' ',$this->filter);
            foreach ($palabras as $palabra)
                $result->whereRaw("mail like '%". $palabra ."%' ");
        }
        $var = $result->get();
        $act = Suscribe::hydrate($var->toArray());
        return $act;
    }

    public function columnFormats(): array
    {
        return [
            'C' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'D' => NumberFormat::FORMAT_DATE_DDMMYYYY,
        ];
    }

    public function map($suscritos): array
    {
        return [
            $suscritos->mail,
            $suscritos->idPersona,
            $suscritos->perfil_seleccionado,
            $suscritos->tematica,
            $suscritos->tiempo_disponible,
            ($suscritos->created_at)?Date::dateTimeToExcel($suscritos->created_at):null

        ];
    }

    public function headings(): array
    {
        return [
            'Mail',
            'IdPersona (si el mail esta asociado a un usuario)',
            'Perfil Seleccionado',
            'tematica',
            'tiempo_disponible',
            'Fecha De Creación'
        ];
    }
}