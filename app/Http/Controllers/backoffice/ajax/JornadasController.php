<?php

namespace App\Http\Controllers\backoffice\ajax;

use App\Actividad;
use App\Http\Controllers\Controller;
use App\Jornada;
use Illuminate\Http\Request;
use App\Http\Requests\CrearJornada;

class JornadasController extends Controller
{
	public function show(Actividad $id, Jornada $jornada)
	{
		$r = $jornada->responsable;
		$jornada->persona = [
			"idPersona" => $r->idPersona,
			"nombre" => $r->nombres . ' ' . $r->apellidoPaterno . ' (' . $r->mail . ')',
		];

		return response()->json($jornada);
	}

	public function store(CrearJornada $request, Actividad $id)
	{
		$actividad = $id;
		$jornada = new Jornada;
		$validado = $request->validated();

		$jornada->fill($validado);
		$actividad->jornadas()->save($jornada);

		return response()->json($jornada->fresh());
	}

	public function update(CrearJornada $request, $id, Jornada $jornada)
	{
		$jornada->fill($request);
		$jornada->save();

		return response()->json($jornada->fresh());
	}

	public function delete(Actividad $id, Jornada $jornada)
	{
		if($jornada->inscripciones()->count() > 0)
			return response()->json([ 'errors' => [ 'idJornada' => [ 'No se puede eliminar un jornada con inscriptos' ] ] ], 422);

		$jornada->delete();

		return response()->json('OK', 200);
	}
}
