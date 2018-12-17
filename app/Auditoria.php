<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

use App\Jobs\LimpiarAuditoriasViejas;

class Auditoria extends Model
{
    protected $table = 'auditorias';

    protected $appends = ['fecha_creacion'];

    const DIAS_A_CONSERVAR = 30;

    public static function crear($modelo)
    {
    	$auditoria = new static;
    	$auditoria->tabla = $modelo->getTable();
    	$auditoria->id_registro = $modelo->getKey();
    	$auditoria->informacion = json_encode($modelo->getOriginal());
    	if($persona = Auth::user()) {
    		$auditoria->idPersona = $persona->idPersona;	
    	}
    	$auditoria->save();

    	LimpiarAuditoriasViejas::dispatch();
    }

    public static function limpiar()
    {
    	static::where('created_at', '<', date('Y-m-d H:i:s', strtotime('-' . static::DIAS_A_CONSERVAR . ' days')))->delete();
    }

    public static function consultar($tabla, $id, $dias = null)
    {
    	if(!$dias) $dias = static::DIAS_A_CONSERVAR;

    	return static::where('tabla', $tabla)
            ->where('id_registro', $id)
            ->where('created_at', '>=', date('Y-m-d H:i:s', strtotime('-' . static::DIAS_A_CONSERVAR . ' days')))
            ->with('persona')
            ->orderBy('created_at', 'desc');
    }

    public function persona()
    {
    	return $this->belongsTo(Persona::class, 'idPersona');
    }

    public function getFechaCreacionAttribute()
    {
        return date('d/m/Y - H:i:s', strtotime($this->created_at));
    }
}
