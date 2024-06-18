<?php

namespace App\Http\Controllers\ajax;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\InstitucionEducativa;

class InstitucionEducativaController extends Controller
{
    public function index(Request $request)
    {
        return InstitucionEducativa::orderBy('nombre')->get();
    }

    public function get(int $InstitucionEducativa)
    {
        return InstitucionEducativa::find($InstitucionEducativa);
    }

    public function porPais(int $idPais) {
    	return InstitucionEducativa::where('idPais', $idPais)->orderBy('nombre')->get();
    }
}
