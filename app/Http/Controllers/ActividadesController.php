<?php

namespace App\Http\Controllers;

use App\CategoriaActividad;
use Carbon\Carbon;
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

        $limiteInscriptos = (empty($actividad->limiteInscripciones)) ? 0 : $actividad->limiteInscripciones;

        $hayCupos = (($limiteInscriptos - $cantInscriptos) > 0 || $limiteInscriptos == 0);

        $inscripcionAbierta = $actividad->fechaInicioInscripciones->lte(Carbon::now()->format('Y-m-d H:i:00')) &&  $actividad->fechaFinInscripciones->gte(Carbon::now()->format('Y-m-d H:i:00'));
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
