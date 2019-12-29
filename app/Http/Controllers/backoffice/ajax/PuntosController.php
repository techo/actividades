<?php

namespace App\Http\Controllers\backoffice\ajax;

use App\Actividad;
use App\Http\Controllers\Controller;
use App\PuntoEncuentro;
use Illuminate\Http\Request;

class PuntosController extends Controller
{
	public function show(Actividad $id, PuntoEncuentro $punto)
	{
		$r = $punto->responsable;
		$punto->persona = [
			"idPersona" => $r->idPersona,
			"nombre" => $r->nombres . ' ' . $r->apellidoPaterno . ' (' . $r->mail . ')',
		];

		return response()->json($punto);
	}

	public function store(Request $request, $id)
	{
		$actividad = Actividad::findOrFail($id);
		$punto = new PuntoEncuentro;
		$validado = $request->validate([
			'punto' => 'required',
			'horario' => 'required',
			'idProvincia' => 'required',
			'idLocalidad' => 'required',
			'idPersona' => 'required',
			'estado' => 'required',
		]);

		$punto->fill($validado);
		$punto->idPais = $actividad->idPais;
		$actividad->puntosEncuentro()->save($punto);

		return response()->json($punto->fresh());
	}

	public function update(Request $request, $id, PuntoEncuentro $punto)
	{
		$validado = $request->validate([
			'punto' => 'required',
			'horario' => 'required',
			'idProvincia' => 'required',
			'idLocalidad' => 'required',
			'idPersona' => 'required',
			'estado' => 'required',
		]);

		$punto->fill($validado);
		$punto->save();

		return response()->json($punto->fresh());
	}

	public function delete(Actividad $id, PuntoEncuentro $punto)
	{
		if($punto->inscripciones()->count() > 0)
			return response()->json([ 'errors' => [ 'idPuntoEncuentro' => [ 'No se puede eliminar un punto con inscriptos' ] ] ], 422);

		$punto->delete();

		return response()->json('OK', 200);
	}
}
