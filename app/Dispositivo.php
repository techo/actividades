<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Dispositivo extends Model
{
    protected $table      = 'Dispositivo';
    protected $primaryKey = 'idDispositivo';

    protected $fillable = [
        'idPersona',
        'player_id',
        'plataforma',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    /**
     * Persona dueña de este dispositivo.
     */
    public function persona()
    {
        return $this->belongsTo(Persona::class, 'idPersona', 'idPersona');
    }

    /**
     * Scope para obtener solo los dispositivos activos.
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}
