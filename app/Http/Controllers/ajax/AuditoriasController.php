<?php

namespace App\Http\Controllers\ajax;

use App\Http\Controllers\BaseController;

use Illuminate\Http\Request;

use App\Auditoria;

class AuditoriasController extends BaseController
{

    function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Devuelve el JSON de una auditoria
     *
     * @param  string  $tabla
     * @param  int  $id
     * @return json $auditoria
     */
    public function show($tabla, $id)
    {
        $registros = Auditoria::consultar($tabla, $id)->get();
        return json_encode([
            'tabla' => $tabla,
            'id' => $id,
            'registros' => $registros
        ]);
    }

}
