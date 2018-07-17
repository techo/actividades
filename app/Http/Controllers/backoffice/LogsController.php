<?php

namespace App\Http\Controllers\backoffice;

use App\Log;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class LogsController extends Controller
{
    public function show($proceso) {
        $logs = Log::where('idPersona', auth()->user()->idPersona)
            ->where('nombreProceso', $proceso)
            ->get();

        $content = \View::make('txt.importar.inscripciones')->with('logs', $logs);

        $filename = 'errores.txt';

        // Headers necesarios para la descarga del txt
        $headers = array(
            'Content-Type' => 'plain/txt',
            'Content-Disposition' => sprintf('attachment; filename="%s"', $filename),
            'Content-Length' => strlen($content),
        );

        return \Response::make($content, 200, $headers);
    }
}
