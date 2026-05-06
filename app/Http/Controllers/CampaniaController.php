<?php

namespace App\Http\Controllers;

use App\Campaign;
use App\Pais;
use Illuminate\Http\Request;

class CampaniaController extends Controller
{
    public function show($abreviacion, $id)
    {
        $campaign = Campaign::findOrFail($id);

        if (! $campaign->activa) {
            abort(404);
        }

        $pais = Pais::where('abreviacion', $abreviacion)->firstOrFail();
        app()->setLocale($pais->locale);

        return view('campania.show', compact('pais', 'campaign'));
    }
}
