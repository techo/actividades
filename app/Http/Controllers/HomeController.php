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
    
    public function multiPais(Request $request)
    {   
        $pais = \App\Pais::where('id', $request->session()->get('pais'))->where('habilitado',1)->first();
        if ($pais){
            return redirect($pais->abreviacion);
        }


        $paises = \App\Pais::where('habilitado',1)->get();
        $showLogin = false;
        $showPaises = 1;

        if($request->path() == 'login'){
            $showLogin = true;
        }


        return view('multiPais', compact('paises', 'showLogin', 'showPaises'));
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

    public function deseleccionarPais(Request $request)
    {
        $request->session()->forget('pais');
        return redirect('/actividades');
    }
}
