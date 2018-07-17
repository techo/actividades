<?php

namespace App\Http\Controllers\backoffice;

use App\Actividad;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class InscripcionesController extends Controller
{
    public function index(Request $request)
    {
        $datatableConfig = config('datatables.inscripciones');
        $fields = $datatableConfig['fields'];
        $actividad = Actividad::findOrFail($request->id);
        if ($actividad->costo > 0) {
            $checkPago = [[
                'name' => '__component:pago',
                'title' => 'Pago',
                'titleClass' => 'text-center',
                'dataClass' => 'text-center'
            ]];
            array_splice($fields, count($fields) - 2, 0, $checkPago);

        }
        $fields = json_encode($fields);
        $sortOrder = json_encode($datatableConfig['sortOrder']);
        return view('backoffice.inscripciones.index', compact('fields', 'sortOrder', 'actividad'));

    }

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
