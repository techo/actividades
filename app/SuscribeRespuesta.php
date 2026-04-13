<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SuscribeRespuesta extends Model
{
    protected $table = 'suscribe_respuestas';
    protected $primaryKey = 'id';

    protected $fillable = [
        'suscripcion_id',
        'pregunta_id',
        'respuesta',
    ];

    public function suscripcion()
    {
        return $this->belongsTo(Suscribe::class, 'suscripcion_id', 'id');
    }

    public function pregunta()
    {
        return $this->belongsTo(CampaignPregunta::class, 'pregunta_id', 'id');
    }
}
