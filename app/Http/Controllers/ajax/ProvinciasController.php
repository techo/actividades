<?php

namespace App\Http\Controllers\ajax;

use App\Provincia;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProvinciasController extends Controller
{
    public function show(Request $request)
    {
        $provincia = Provincia::find($request->id);
        $provincia->load('localidades');
        return $provincia;
    }
}
