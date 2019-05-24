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
}
