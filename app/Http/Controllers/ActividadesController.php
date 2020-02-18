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

        $mensaje = __('frontend.error');
        $clase = 'btn-danger';
        $accion = '/inscripciones/actividad/' .  $actividad->idActividad;
        $habilitado = false;
        $payment = '';

        if (auth()->check() && auth()->user()->estadoInscripcion($id)) {
            
            $estado_inscripcion = auth()->user()->estadoInscripcion($id);
            $mensaje = $estado_inscripcion;

            switch ($estado_inscripcion) 
            {
                case 'CONFIRMAR PARTICIPACIÓN':
                    try
                    {
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
                    $mensaje = __('frontend.approval_needed');
                    break;

                case 'FECHA DE CONFIRMACIÓN VENCIDA':
                    $mensaje = __('frontend.confirmation_date_is_closed');
                    $clase = 'btn-danger disabled';
                    $habilitado = false;
                    break;

                case 'ESPERAR CONFIRMACIÓN':
                    $mensaje = __('frontend.waiting_for_confirmation');
                    $clase = 'btn-warning disabled';
                    $habilitado = false;
                    break;

                case 'CONFIRMADO':
                    $mensaje = __('frontend.confirmed');
                    $clase = 'btn-success disabled';
                    $habilitado = false;
                    break;
            }
        }
        else 
        {

            if(!$inscripciones_abiertas) {
                $mensaje = __('frontend.closed_inscriptions');
                $clase = "disabled";
                $habilitado = true;
                return view('actividades.show', compact('actividad', 'mensaje', 'accion' , 'clase', 'habilitado', 'payment'));
            }
            if(!$hay_cupos) {
                $mensaje = __('frontend.activity_full');
                $clase = "disabled";
                $habilitado = false;
                return view('actividades.show', compact('actividad', 'mensaje', 'accion' , 'clase', 'habilitado', 'payment'));
            }

            if($actividad->confirmacion == 1 || $actividad->pago == 1) {
                $mensaje = __('frontend.pre_registration');
                $clase = 'btn-primary';
                $accion = '/inscripciones/actividad/' .  $actividad->idActividad;
                $habilitado = true;
            }
            else {
                $mensaje = __('frontend.apply_now');
                $clase = 'btn-primary';
                $accion = '/inscripciones/actividad/' .  $actividad->idActividad;
                $habilitado = true;
            }

            $fecha_hoy = Carbon::parse(Carbon::now()->format('Y-m-d'));

            if($actividad->pago == 1 && $actividad->fechaLimitePago && $actividad->fechaLimitePago->lessThanOrEqualTo($fecha_hoy) ) {
                $mensaje = __('frontend.approval_needed');
                $clase = 'btn-danger disabled';
                $habilitado = false;
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
