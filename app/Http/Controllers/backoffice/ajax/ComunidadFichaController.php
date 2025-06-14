<?php

namespace App\Http\Controllers\backoffice\ajax;

use App\Http\Controllers\Controller;
use App\ComunidadFichaInicial;

use App\Http\Requests\Comunidad\EditarComunidadFichaInicial;

class ComunidadFichaController extends Controller
{
    public function store(EditarComunidadFichaInicial $request, $idComunidad)
    {
        $comunidad_ficha_inicial = new ComunidadFichaInicial();
        $validado = $request->validated();
        $comunidad_ficha_inicial->fill($validado);

        $comunidad_ficha_inicial->save();

        return response()->json($comunidad_ficha_inicial->fresh());

    }

    public function update(EditarComunidadFichaInicial $request, $idComunidad, $idFicha)
    {
        $comunidad_ficha_inicial = ComunidadFichaInicial::findOrFail($idFicha);
        $validado = $request->validated();
        $comunidad_ficha_inicial->fill($validado);
        $comunidad_ficha_inicial->save();

        return response()->json($comunidad_ficha_inicial);
    }
}
