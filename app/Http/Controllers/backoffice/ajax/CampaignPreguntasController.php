<?php

namespace App\Http\Controllers\backoffice\ajax;

use App\Campaign;
use App\CampaignPregunta;
use App\Http\Controllers\Concerns\ManagesPreguntas;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CampaignPreguntasController extends Controller
{
    use ManagesPreguntas;

    protected function preguntaClass()
    {
        return CampaignPregunta::class;
    }

    protected function ownerColumn()
    {
        return 'campaign_id';
    }

    public function index(Campaign $campana)
    {
        return $this->respondPreguntas($campana->id);
    }

    public function store(Request $request, Campaign $campana)
    {
        return $this->crearPregunta($request, $campana->id);
    }

    public function update(Request $request, Campaign $campana, $preguntaId)
    {
        return $this->actualizarPregunta($request, $campana->id, $preguntaId);
    }

    public function destroy(Campaign $campana, $preguntaId)
    {
        return $this->eliminarPregunta($campana->id, $preguntaId);
    }

    public function mover(Request $request, Campaign $campana, $preguntaId)
    {
        return $this->moverPregunta($request, $campana->id, $preguntaId);
    }
}
