<?php

namespace App;

use App\Concerns\Preguntable;
use Illuminate\Database\Eloquent\Model;

class CampaignPregunta extends Model
{
    use Preguntable;

    protected $table = 'campaign_preguntas';
    protected $primaryKey = 'id';

    protected $fillable = [
        'campaign_id',
        // El resto de los campos comunes los agrega el trait Preguntable.
    ];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class, 'campaign_id');
    }
}
