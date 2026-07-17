<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Valor de una columna personalizada para un registro del listado.
 * record_id es la PK del registro (idInscripcion, idIntegrante...) según
 * el list_key de la columna padre. Valor vacío ⇒ se borra la fila.
 */
class ListadoColumnaValor extends Model
{
    protected $table = 'listado_columna_valores';

    protected $fillable = [
        'columna_id',
        'record_id',
        'valor',
        'updated_by',
    ];

    public function columna()
    {
        return $this->belongsTo(ListadoColumna::class, 'columna_id');
    }
}
