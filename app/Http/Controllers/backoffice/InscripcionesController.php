<?php

namespace App\Http\Controllers\backoffice;

use App\Actividad;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class InscripcionesController extends Controller
{
    public function descargarTemplate()
    {
        $path = resource_path() .'/templates/importar_inscripciones_template.xlsx';
        $file = new \SplFileInfo($path);
        $headers = array(
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => sprintf('attachment; filename="%s"', $file->getFilename()),
            'Content-Length' => $file->getSize(),
        );
        return response()->download($file, 'template.xlsx', $headers);
    }
}
