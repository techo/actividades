<?php

namespace App\Http\Controllers\backoffice;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\HomeHeader;

class HomeHeaderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {      
        $edicion = false;
        $homeHeader = HomeHeader::where('idPais', auth()->user()->idPaisPermitido)->first();

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
            'idHomeHeader' => 'required',
            'header' => 'required',
            'subHeader' => 'required',
            'imagen' => 'nullable|file|image|dimensions:max_width=1366,max_height=210,min_width=1366,min_height=210',
        ]);
        $homeHeader = HomeHeader::find($request->idHomeHeader);
        

        $imagen = $request->file('imagen');
        if($imagen){
            $path = $request->file('imagen')->store('public/header');
            $homeHeader->imagen = str_replace('public', 'storage', '/'.$path);
        }
        $homeHeader->header = $validados['header'];
        $homeHeader->subHeader = $validados['subHeader'];
        
        $homeHeader->save();

        return response()->json($validados);

    }
}
