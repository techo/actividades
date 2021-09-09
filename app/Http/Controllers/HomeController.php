<?php

namespace App\Http\Controllers;

use App\CategoriaActividad;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $showLogin = false;

        if($request->path() == 'login'){
            $showLogin = true;
        }

        $categoriaActividad = CategoriaActividad::with('tipos')->get();

        return view('home', compact('categoriaActividad', 'showLogin'));
    }

    public function seleccionarPais(Request $request, $id)
    {
        $pais = \App\Pais::find($id);
        if($pais) {
            $request->session()->put('pais', $pais->id);
            $request->session()->put('locale',$pais->locale);
        }
        
        return redirect($pais->abreviacion);
    }

    public function seleccionarPaisAbreviacion(Request $request, $abreviacion)
    {
        $pais = \App\Pais::where('abreviacion', '=', $abreviacion)->where('habilitado', '=', 1)->first();
        if($pais) {
            $request->session()->put('pais', $pais->id);
            $request->session()->put('locale',$pais->locale);
        }
        return redirect('/actividades');
    
    }

    public function deseleccionarPais(Request $request)
    {
        $request->session()->forget('pais');
        return redirect('/actividades');
    }
}
