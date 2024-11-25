<?php

namespace App\Http\Controllers;

use App\CategoriaActividad;
use App\Persona;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Actividad;
use App\HomeHeader;
use App\Tipo;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PostulacionesController extends Controller
{
    /**
     * Devuelve la vista de actividades
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $idCategoria = 6;
        $categoriaSeleccionada = CategoriaActividad::find($idCategoria);
        $categorias = CategoriaActividad::all();
        if ($request->query('tipo')) {
            $tipoSeleccionada = Tipo::find($request->query('tipo'));
        } else {
            $tipoSeleccionada = null;
        }
        $homeHeader = homeHeader::where('idPais', \Session::get('pais'))->first();
        return view('postulaciones.index')
            ->with(
                [
                    'categoriaSeleccionada' => $categoriaSeleccionada,
                    'categorias' => $categorias,
                    'tipoSeleccionada' => $tipoSeleccionada,
                    'homeHeader' => $homeHeader,
                ]
            );
    }

  
}
