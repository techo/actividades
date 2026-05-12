<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    protected $table = 'campaigns';

    protected $fillable = [
        'nombre',
        'descripcion',
        'estado',
        'tipo',
        'imagen',
        'oficina_id',
        'pais_id',
        'whatsapp_link',
        'confirmation_message',
        'fecha_inicio',
        'fecha_fin',
        'activa',
    ];

    protected $casts = [
        'activa'       => 'boolean',
        'fecha_inicio' => 'date',
        'fecha_fin'    => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function (Campaign $campaign) {
            if ($campaign->oficina_id) {
                $oficina = Oficina::find($campaign->oficina_id);
                if ($oficina) {
                    $campaign->pais_id = $oficina->id_pais;
                    return;
                }
            }
            if (auth()->check()) {
                $campaign->pais_id = auth()->user()->idPaisPermitido;
            }
        });
    }

    public function oficina()
    {
        return $this->belongsTo(Oficina::class, 'oficina_id');
    }

    public function suscriptos()
    {
        return $this->hasMany(Suscribe::class, 'campaign_id');
    }

    public function preguntas()
    {
        return $this->hasMany(CampaignPregunta::class, 'campaign_id')->orderBy('orden');
    }
}
