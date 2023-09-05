<?php

namespace App\Http\Controllers\backoffice;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\HomeHeader;
use Illuminate\Support\Facades\Log;

class HomeHeaderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        
        $edicion = true;

        // buscar el Pais del usuario logueado
        Log::info("homeHeader");

        $homeHeader = HomeHeader::where('idPais', auth()->user()->idPaisPermitido)->first();
        Log::info($homeHeader);
        // buscar su header, si no tiene mandarle el default (que es el actual)

        return view(
            'backoffice.configuracion.homeHeader',
            compact(
                'homeHeader',
                'edicion',
            )
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $validados = $request->validate([
            'idTipo' => 'required',
            'nombre' => 'required',
            'idCategoria' => 'required',
            'imagen' => 'nullable|file|image|dimensions:max_width=380,max_height=248',
        ]);
        $homeHeader = HomeHeader::find($validados->idHomeHeader);
        

        $imagen = $request->file('imagen');
        if($imagen){
            $path = $request->file('imagen')->store('public/header');
            $homeHeader->imagen = str_replace('public', 'storage', '/'.$path);
        }
        $homeHeader->header = $validados['header'];
        
        $homeHeader->save();

        return response()->json($validados);

    }

}
