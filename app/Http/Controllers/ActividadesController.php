<?php

namespace App\Http\Controllers;

use App\CategoriaActividad;
use App\Persona;
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
        $idCategoria = $request->categoria ?? null;
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

        $cantInscriptos = $actividad->inscripciones()->count();

        $limiteInscriptos = (empty($actividad->limiteInscripciones)) ? 0 : $actividad->limiteInscripciones;

        $hay_cupos = ($limiteInscriptos - $cantInscriptos) > 0 || $limiteInscriptos == 0;

        $inscripciones_abiertas = $actividad->fechaInicioInscripciones->lte(Carbon::now()) &&  $actividad->fechaFinInscripciones->gte(Carbon::now());

        $mensaje = "ERROR";
        $clase = 'btn-danger';
        $accion = '/inscripciones/actividad/' .  $actividad->idActividad;
        $habilitado = false;

        if (auth()->check()) {
            $estado_inscripcion = auth()->user()->estadoInscripcion($id);
            $mensaje = $estado_inscripcion;
            
            if($estado_inscripcion) //si está inscripto
            {
                switch ($estado_inscripcion) {
                    case 'CONFIRMAR PARTICIPACIÓN':
                        try{
                            $config = json_decode($actividad->pais->config_pago);
                            $paymentClass = 'App\\Payments\\' . $config->payment_class;
                            $persona = Persona::find(auth()->user()->idPersona);
                            $inscripcion = $persona->inscripcionActividad($id);
                            $payment = new $paymentClass($inscripcion);
                        } catch (\Exception $e){
                            return response('La configuración de pagos de '. $actividad->pais->nombre .' no está establecida', 500);
                        }
                        $habilitado = true;
                        $accion = action('InscripcionesController@confirmarDonacion', ['id' => $actividad->idActividad]);
                        $clase = 'btn-primary';
                        break;

                    case 'FECHA DE CONFIRMACIÓN VENCIDA':
                        $clase = 'btn-default disabled';
                        $habilitado = false;
                        break;

                    case 'ESPERAR CONFIRMACIÓN':
                        $clase = 'btn-warning disabled';
                        $habilitado = false;
                        break;

                    case 'CONFIRMADO':
                        $clase = 'btn-success disabled';
                        $habilitado = false;
                        break;
                }
            }
            else {

                if(!$inscripciones_abiertas) {
                    $mensaje = "El período de inscripción está cerrado";
                    $clase = "disabled";
                    $habilitado = true;
                    return view('actividades.show', compact('actividad', 'mensaje', 'accion' , 'clase', 'habilitado', 'payment'));
                }
                if(!$hay_cupos) {
                    $mensaje = "La actividad no tiene más cupos";
                    $clase = "disabled";
                    $habilitado = false;
                    return view('actividades.show', compact('actividad', 'mensaje', 'accion' , 'clase', 'habilitado', 'payment'));
                }

                if($actividad->confirmacion == 1 || $actividad->pago == 1) {
                    $mensaje = "PREINSCRIBIRME";
                    $clase = 'btn-primary';
                    $accion = '/inscripciones/actividad/' .  $actividad->idActividad;
                    $habilitado = true;
                }
                else {
                    $mensaje = "INSCRIBIRME";
                    $clase = 'btn-primary';
                    $accion = '/inscripciones/actividad/' .  $actividad->idActividad;
                    $habilitado = true;
                }
            }
        }

        return view('actividades.show', compact('actividad', 'mensaje', 'accion' , 'clase', 'habilitado', 'payment'));
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
