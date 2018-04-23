<?php

namespace App\Http\Controllers;

use App\CategoriaActividad;
use Illuminate\Http\Request;
use App\Actividad;

class actividadesController extends Controller
{
    /**
     * Devuelve la vista de actividades
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $default = 1; //Actividades en Asentamientos
        $idCategoria = $request->categoria ?? $default;
        $categoriaSeleccionada = CategoriaActividad::find($idCategoria);
        $categorias = CategoriaActividad::all();

        return view('actividades.index')
            ->with(
                [
                    'categoriaSeleccionada' => $categoriaSeleccionada,
                    'categorias' => $categorias,
                ]
            );
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $actividad = Actividad::with('localidad')->where('estadoConstruccion', 'Abierta')->findOrFail($id);

        $cantInscriptos = $actividad->inscripciones()->inscripto()->count();

        $limiteInscriptos = $actividad->limiteInscripciones;

        $hayCupos = ($limiteInscriptos - $cantInscriptos) > 0;

        $inscripcionAbierta = $actividad->fechaInicioInscripciones->lte(date('Y-m-d')) &&  $actividad->fechaFinInscripciones->gte(date('Y-m-d'));

        return view('actividades.show', compact('actividad', 'hayCupos', 'inscripcionAbierta'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
