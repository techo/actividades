<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CampaignPregunta extends Model
{
    protected $table = 'campaign_preguntas';
    protected $primaryKey = 'id';

    protected $fillable = [
        'campaign_id',
        'pregunta',
        'descripcion',
        'tipo',
        'opciones',
        'requerida',
        'orden',
    ];

    protected $casts = [
        'opciones'  => 'array',
        'requerida' => 'boolean',
    ];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class, 'campaign_id');
    }
}
