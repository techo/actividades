<?php

namespace App\Http\Controllers;

use App\Pais;
use App\Suscribe;
use Illuminate\Http\Request;

class SuscribeController extends Controller
{
    public function get($abreviacion)
    {
		$pais = Pais::where('abreviacion', $abreviacion)->firstOrFail();
		app()->setLocale($pais->locale);
		return view('perfil.suscribe', compact('pais'));
    }

    public function create(Request $request)
    {
        $data = $request->all();

        $suscripcion = Suscribe::create($data);

        return response()->json([
            'success' => true,
            'message' => __('suscribe.success')
        ]);
    }
}